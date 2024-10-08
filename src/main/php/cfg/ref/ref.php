<?php

/*

    model/ref/ref.php - a link between a phrase and another system such as wikidata
    -----------------

    The reference is a concrete link between one phrase and an object in an external system
    the external system is defined by the reference type

    a reference type is potentially a bidirectional interface to another system
    that includes specific coding for the external system
    a user can never add a reference type but can rename it or change the description

    reference types are preloaded in the frontend whereas source are loaded on demand

    a source is always unidirectional and based on standard data format

    ref types can be bidirectional

    The main sections of this object are
    - db const:          const for the database link
    - object vars:       the variables of this word object
    - construct and map: including the mapping of the db row to this word object
    - set and get:       to capsule the vars from unexpected changes
    - cast:              create an api object and set the vars from an api json
    - preloaded:         select e.g. types from cache
    - load:              database access object (DAO) functions
    - im- and export:    create an export object and set the vars from an import object
    - log:               write the changes to the log
    - save:              manage to update the database
    - del:               manage to remove from the database
    - debug:             internal support functions for debugging

    TODO add to UI; add unit tests


    This file is part of zukunft.com - calc with words

    zukunft.com is free software: you can redistribute it and/or modify it
    under the terms of the GNU General Public License as
    published by the Free Software Foundation, either version 3 of
    the License, or (at your option) any later version.
    zukunft.com is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/agpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
   
*/

namespace cfg;

include_once SERVICE_EXPORT_PATH . 'sandbox_exp.php';
include_once SERVICE_EXPORT_PATH . 'ref_exp.php';
include_once MODEL_REF_PATH . 'ref_type.php';
include_once MODEL_REF_PATH . 'ref_type_list.php';
include_once API_REF_PATH . 'ref.php';
include_once WEB_REF_PATH . 'ref.php';
include_once MODEL_REF_PATH . 'source.php';
include_once MODEL_PHRASE_PATH . 'phrase.php';
include_once MODEL_SANDBOX_PATH . 'sandbox_named.php';
include_once MODEL_SANDBOX_PATH . 'sandbox_link_with_type.php';

use api\api;
use api\ref\ref as ref_api;
use cfg\db\sql;
use cfg\db\sql_db;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
use cfg\db\sql_par;
use cfg\db\sql_par_field_list;
use cfg\db\sql_type;
use cfg\db\sql_type_list;
use cfg\export\sandbox_exp;
use cfg\export\ref_exp;
use cfg\log\change;
use cfg\log\change_action;
use cfg\log\change_link;
use cfg\log\change_table_list;

class ref extends sandbox_link_with_type
{

    /*
     * db const
     */

    // object specific database and JSON object field names
    // *_COM: the description of the field
    // *_SQLTYP is the sql data type used for the field
    const TBL_COMMENT = 'to link external data to internal for syncronisation';
    const FLD_ID = 'ref_id';
    const FLD_USER_COM = 'the user who has created or adjusted the reference';
    const FLD_EX_KEY_COM = 'the unique external key used in the other system';
    const FLD_EX_KEY = 'external_key';
    const FLD_EX_KEY_SQLTYP = sql_field_type::NAME;
    const FLD_TYPE = 'ref_type_id';
    const FLD_URL_COM = 'the concrete url for the entry inluding the item id';
    const FLD_URL = 'url';
    const FLD_URL_SQLTYP = sql_field_type::TEXT;
    const FLD_SOURCE_COM = 'if the reference does not allow a full automatic bidirectional update use the source to define an as good as possible import or at least a check if the reference is still valid';
    const FLD_SOURCE = 'source_id';
    const FLD_PHRASE_COM = 'the phrase for which the external data should be syncronised';

    // field names that cannot be user specific
    const FLD_NAMES = array(
        phrase::FLD_ID,
        self::FLD_TYPE
    );
    // list of user specific text field names
    const FLD_NAMES_USR = array(
        self::FLD_EX_KEY,
        self::FLD_URL,
        sandbox_named::FLD_DESCRIPTION
    );
    // list of user specific numeric field names
    const FLD_NAMES_NUM_USR = array(
        source::FLD_ID,
        sandbox::FLD_EXCLUDED,
        sandbox::FLD_SHARE,
        sandbox::FLD_PROTECT
    );
    // all database field names excluding the id used to identify if there are some user specific changes
    const ALL_SANDBOX_FLD_NAMES = array(
        phrase::FLD_ID,
        self::FLD_EX_KEY,
        self::FLD_TYPE,
        self::FLD_URL,
        sandbox_named::FLD_DESCRIPTION,
        sandbox::FLD_EXCLUDED
    );
    // list of fields that must be set
    const FLD_LST_MUST_BUT_STD_ONLY = array(
        [self::FLD_EX_KEY, self::FLD_EX_KEY_SQLTYP, sql_field_default::NOT_NULL, sql::INDEX, '', self::FLD_EX_KEY_COM],
    );
    // list of fields that must be set, but CAN be changed by the user
    const FLD_LST_MUST_BUT_USER_CAN_CHANGE = array(
        [self::FLD_EX_KEY, self::FLD_EX_KEY_SQLTYP, sql_field_default::NULL, sql::INDEX, '', self::FLD_EX_KEY_COM],
    );
    // list of fields that CAN be changed by the user
    const FLD_LST_USER_CAN_CHANGE = array(
        [self::FLD_URL, self::FLD_URL_SQLTYP, sql_field_default::NULL, '', '', self::FLD_URL_COM],
        [source::FLD_ID, sql_field_type::INT, sql_field_default::NULL, sql::INDEX, source::class, self::FLD_SOURCE_COM],
        [sandbox_named::FLD_DESCRIPTION, sandbox_named::FLD_DESCRIPTION_SQLTYP, sql_field_default::NULL, '', '', ''],
    );
    // list of fields that CANNOT be changed by the user
    const FLD_LST_NON_CHANGEABLE = array(
        [phrase::FLD_ID, sql_field_type::INT, sql_field_default::NULL, sql::INDEX, '', self::FLD_PHRASE_COM],
        [ref_type::FLD_ID, sql_field_type::INT, sql_field_default::NOT_NULL, sql::INDEX, ref_type::class, ref_type::TBL_COMMENT],
    );

    // persevered reference names for unit and integration tests
    const TEST_REF_NAME = 'System Test Reference Name';


    /*
     * object vars
     */

    // database fields
    public ?string $external_key = null;  // the unique key in the external system
    public ?source $source = null;        // if the reference does not allow a full automatic bidirectional update
    //                                       use the source to define an as good as possible import
    //                                       or at least a check if the reference is still valid
    public ?string $url;
    public ?string $code_id = null;
    public ?string $description = null;

    // TODO deprecate
    public ?string $name = null;


    /*
     * construct and map
     */

    function __construct(user $usr)
    {
        parent::__construct($usr);
        $this->reset();
    }

    function reset(): void
    {
        parent::reset();
        $this->create_objects($this->user());
        $this->set_type_id(0);
        $this->external_key = '';
        $this->source = null;
        $this->url = null;
        $this->description = null;
    }

    private function create_objects(user $usr): void
    {
        global $ref_types;
        $this->set_phrase(new phrase($usr));
    }

    /**
     * set the class vars based on a database record
     *
     * @param array|null $db_row is an array with the database values
     * @param bool $load_std true if only the standard user sandbox object ist loaded
     * @param bool $allow_usr_protect false for using the standard protection settings for the default object used for all users
     * @param string $id_fld the name of the id field as defined in this child and given to the parent
     * @return bool true if the reference is loaded and valid
     */
    function row_mapper_sandbox(
        ?array $db_row,
        bool   $load_std = false,
        bool   $allow_usr_protect = false,
        string $id_fld = ''
    ): bool
    {
        global $ref_types;
        $result = parent::row_mapper_sandbox($db_row, $load_std, $allow_usr_protect, $id_fld);
        if ($result) {
            $this->set_phrase_by_id($db_row[phrase::FLD_ID]);
            $this->external_key = $db_row[self::FLD_EX_KEY];
            $this->set_type_id($db_row[self::FLD_TYPE]);
            $this->url = $db_row[self::FLD_URL];
            $this->description = $db_row[sandbox_named::FLD_DESCRIPTION];
            $this->set_source_by_id($db_row[source::FLD_ID]);
            if ($this->load_objects()) {
                $result = true;
                log_debug('done ' . $this->dsp_id());
            }
        }
        return $result;
    }


    /*
     * set and get
     */

    /**
     * set the most often used reference vars with one set statement
     * @param int $id mainly for test creation the database id of the reference
     */
    function set(int $id = 0): void
    {
        $this->set_id($id);
    }

    /**
     * @param int $id the database id of the verb
     */
    function set_id(int $id): void
    {
        $this->id = $id;
    }

    /**
     * set the phrase and the fob at once because it is not yet known how to cast to a custom type like phrase
     * @param phrase $phr the phrase that should be linked to an external source for data exchange
     * @return void
     */
    function set_phrase(phrase $phr): void
    {
        $this->set_fob($phr);
    }

    function set_phrase_by_id(?int $id): void
    {
        if ($id != null) {
            if ($id != 0) {
                $phr = new phrase($this->user());
                $phr->load_by_id($id);
                $this->set_phrase($phr);
            }
        }
    }

    function phrase(): phrase|sandbox_named|combine_named|null
    {
        return $this->fob();
    }

    /**
     * @return int the phrase id and null if the phrase is not set
     */
    function phrase_id(): int
    {
        $result = 0;
        $phr = $this->phrase();
        if ($phr != null) {
            $id = $phr->id();
            if ($id != 0) {
                $result = $id;
            }
        }
        return $result;
    }

    /**
     * @param string|null $name the name of the reference
     */
    function set_name(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return int the database id which is not 0 if the object has been saved
     */
    function id(): int
    {
        return $this->id;
    }

    /**
     * @return int the id of the linked object with is in this case the phrase id (or maybe later the group_id))
     */
    function from_id(): int
    {
        if ($this->fob() == null) {
            return 0;
        } else {
            return $this->phrase()->id();
        }
    }

    /**
     * @return int|string the id of the linked object with is in this case the phrase id (or maybe later the group_id))
     */
    function to_id(): int|string
    {
        return $this->external_key;
    }

    function has_type(): bool
    {
        if ($this->type_id() >= 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * TODO use always a function like this to set an object
     * TODO use a cache to reducte database access because an id will necer change and due to that no database refresh is needed
     * @param int|null $id
     * @return void
     */
    function set_source_by_id(?int $id): void
    {
        if ($id != null) {
            if ($id != 0) {
                $src = new source($this->user());
                $src->load_by_id($id);
                $this->source = $src;
            }
        }
    }

    /**
     * @return int the id of the source or zero if no source is defined
     */
    function source_id(): int
    {
        $result = 0;
        if ($this->source != null) {
            $result = $this->source->id();
        }
        return $result;
    }

    /**
     * @return string the name of the source or an empty string if no source is defined
     */
    function source_name(): string
    {
        $result = '';
        if ($this->source != null) {
            $result = $this->source->name();
        }
        return $result;
    }

    /**
     * create a clone but change the external link
     *
     * @param string $external_key the target name
     * @return $this a clone with the name changed
     */
    function cloned_linked(string $external_key): ref
    {

        $obj_cpy = parent::cloned();
        $obj_cpy->set_type_id($this->type_id());
        $obj_cpy->external_key = $external_key;
        return $obj_cpy;
    }


    /*
     * preloaded
     */

    /**
     * get the name of the reference type
     * @return string the name of the reference type
     */
    function type_name(): string
    {
        global $ref_types;
        return $ref_types->name($this->type_id);
    }

    /**
     * get the code_id of the reference type
     * @return string the code_id of the reference type
     */
    function type_code_id(): string
    {
        global $ref_types;
        return $ref_types->code_id($this->type_id);
    }

    /**
     * @return type_object|null the reference type
     */
    function type(): ?type_object
    {
        global $ref_types;
        return $ref_types->get($this->type_id);
    }


    /*
     * cast
     */

    /**
     * @return ref_api the ref frontend api object
     */
    function api_obj(): ref_api
    {
        $api_obj = new ref_api();
        if ($this->is_excluded()) {
            $api_obj->set_id($this->id());
            $api_obj->excluded = true;
        } else {
            parent::fill_api_obj($api_obj);
            if ($this->phrase_id() != 0) {
                $api_obj->phrase_id = $this->phrase_id();
            }
            $api_obj->external_key = $this->external_key;
            if ($this->source != null) {
                $api_obj->source_id = $this->source->id();
            }
            $api_obj->url = $this->url;
            $api_obj->description = $this->description;
        }
        return $api_obj;
    }

    /**
     * map a ref api json to this model ref object
     * similar to the import_obj function but using the database id instead of names as the unique key
     * @param array $api_json the api array with the triple values that should be mapped
     * @return user_message the message for the user why the action has failed and a suggested solution
     */
    function set_by_api_json(array $api_json): user_message
    {
        $msg = parent::set_by_api_json($api_json);

        foreach ($api_json as $key => $value) {

            if ($key == api::FLD_PHRASE) {
                if ($value != '' and $value != 0) {
                    $phr = new phrase($this->user());
                    $phr->set_id($value);
                    $this->set_phrase($phr);
                }
            }
            if ($key == api::FLD_EXTERNAL_KEY) {
                if ($value <> '') {
                    $this->external_key = $value;
                }
            }
            if ($key == api::FLD_URL) {
                if ($value <> '') {
                    $this->url = $value;
                }
            }
            if ($key == api::FLD_DESCRIPTION) {
                if ($value <> '') {
                    $this->description = $value;
                }
            }

        }

        return $msg;
    }


    /*
     * load
     */

    /**
     * load a verb by the verb name
     * @param string $external_key_name the name of the external key for the reference
     * @return int the id of the verb found and zero if nothing is found
     */
    function load_by_ex_key(string $external_key_name): int
    {
        global $db_con;

        log_debug($external_key_name);
        $qp = $this->load_sql_by_id($db_con, $external_key_name);
        return $this->load($qp);
    }

    /**
     * just set the class name for the user sandbox function
     * load a reference object by database id
     * @param int $phr_id the id of the phrase that is referenced
     * @param int $type_id the id of the reference type
     * @param string $class the reference class name
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_link_ids(int $phr_id, int $type_id, string $class = self::class): int
    {
        global $db_con;

        log_debug();
        $sc = $db_con->sql_creator();
        $qp = $this->load_sql_by_link_ids($sc, $phr_id, $type_id);
        return $this->load($qp);
    }

    /**
     * create the SQL to load the default ref always by the id
     *
     * @param sql $sc with the target db_type set
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_standard_sql(sql $sc): sql_par
    {
        $sc->set_class($this::class);
        $sc->set_fields(array_merge(
            self::FLD_NAMES,
            self::FLD_NAMES_USR,
            self::FLD_NAMES_NUM_USR,
            array(user::FLD_ID)
        ));

        return parent::load_standard_sql($sc);
    }

    /**
     * load the ref parameters for all users
     * @param sql_par|null $qp placeholder to align the function parameters with the parent
     * @return bool true if the standard ref has been loaded
     */
    function load_standard(?sql_par $qp = null): bool
    {
        global $db_con;
        $qp = $this->load_standard_sql($db_con->sql_creator());
        $result = parent::load_standard($qp);

        if ($result) {
            $result = $this->load_owner();
        }
        return $result;
    }

    /**
     * create the common part of an SQL statement to retrieve the parameters of a ref from the database
     *
     * @param sql $sc with the target db_type set
     * @param string $query_name the name extension to make the query name unique
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql $sc, string $query_name, string $class = self::class): sql_par
    {
        return parent::load_sql_usr_num($sc, $this, $query_name);
    }

    /**
     * create an SQL statement to retrieve a ref by id from the database
     *
     * @param sql $sc with the target db_type set
     * @param int $phr_id the id of the phrase that is referenced
     * @param int $type_id the id of the reference type
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_link_ids(sql $sc, int $phr_id, int $type_id): sql_par
    {
        $qp = $this->load_sql($sc, 'link_ids');
        $sc->add_where(phrase::FLD_ID, $phr_id);
        $sc->add_where(self::FLD_TYPE, $type_id);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * load a verb from the database
     * @param sql_par $qp the query parameters created by the calling function
     * @return int the id of the object found and zero if nothing is found
     */
    protected function load(sql_par $qp): int
    {
        global $db_con;

        $db_row = $db_con->get1($qp);
        $this->row_mapper_sandbox($db_row);
        return $this->id();
    }

    function all_sandbox_fields(): array
    {
        return self::ALL_SANDBOX_FLD_NAMES;
    }

    /**
     * TODO add the missing objects like the source
     * @return bool true if all the related objects has been loaded
     */
    function load_objects(): bool
    {
        $result = true;

        if ($this->phrase()?->name() == null or $this->phrase()?->name() == '') {
            if ($this->phrase_id() <> 0) {
                $phr = new phrase($this->user());
                if ($phr->load_by_id($this->phrase_id())) {
                    $this->set_phrase($phr);
                    log_debug('phrase ' . $phr->dsp_id() . ' loaded');
                } else {
                    $result = false;
                }
            }
        }

        log_debug('done');
        return $result;
    }

    /**
     * TODO check if it should be changed to group_id
     * @return string with the field name of the internal object that is linked
     */
    function from_field(): string
    {
        return phrase::FLD_ID;
    }

    /**
     * @return string with the field name of the external object that is linked
     * together with the ref_type and from_field this is the unique link for references
     */
    function to_field(): string
    {
        return self::FLD_EX_KEY;
    }

    function to_value(): string
    {
        return $this->external_key;
    }

    function type_field(): string
    {
        return ref_type::FLD_ID;
    }


    /*
     * im- and export
     */

    /**
     * import a link to external database from an imported json object
     *
     * @param array $in_ex_json an array with the data of the json object
     * @param object|null $test_obj if not null the unit test object to get a dummy seq id
     * @return user_message the status of the import and if needed the error messages that should be shown to the user
     */
    function import_obj(array $in_ex_json, object $test_obj = null): user_message
    {
        $result = parent::import_obj($in_ex_json, $test_obj);

        global $ref_types;
        // reset of object not needed, because the calling function has just created the object
        foreach ($in_ex_json as $key => $value) {
            if ($key == sandbox_exp::FLD_SOURCE) {
                $src = new source($this->user());
                if (!$test_obj) {
                    $src->load_by_name($value);
                    if ($src->id == 0) {
                        $result->add_message('Cannot find source "' . $value . '" when importing ' . $this->dsp_id());
                    }
                } else {
                    $src->set_name($value);
                }
                $this->source = $src;
            }
            if ($key == sandbox_exp::FLD_TYPE) {
                $this->set_type_id($ref_types->id($value));

                if ($this->type_id() == null or $this->type_id() <= 0) {
                    $result->add_message('Reference type for ' . $value . ' not found');
                }
            }
            if ($key == sandbox_exp::FLD_NAME) {
                $this->external_key = $value;
            }
            if ($key == sandbox_exp::FLD_DESCRIPTION) {
                $this->description = $value;
            }
            if ($key == self::FLD_URL) {
                $this->url = $value;
            }
        }
        // to be able to log the object names
        if (!$test_obj) {
            if ($this->load_objects()) {
                if ($result->is_ok()) {
                    $result->add_message($this->save());
                }
            }
        }

        return $result;
    }

    /**
     * create a reference object for export (so excluding e.g. the database id)
     * @return ref_exp a reduced reference object for the JSON message creation
     */
    function export_obj(bool $do_load = true): sandbox_exp
    {
        global $ref_types;
        $result = new ref_exp();

        if ($this->source != null) {
            $result->source = $this->source->name();
        }
        if ($this->type_id > 0) {
            $result->type = $this->type_code_id();
        }
        if ($this->external_key <> '') {
            $result->name = $this->external_key;
        }
        if ($this->description <> '') {
            $result->description = $this->description;
        }
        if ($this->url <> '') {
            $result->url = $this->url;
        }

        return $result;
    }


    /*
     * log
     */

    /**
     * set the log entry parameter for a new reference
     */
    function log_link_add(): change_link
    {
        log_debug('ref->log_add ' . $this->dsp_id());

        // check that the minimal parameters are set
        if ($this->phrase() == null) {
            log_err('The phrase object must be set to log adding an external reference.', 'ref->log_add');
        }
        if ($this->type_id() <= 0) {
            log_err('The reference type object must be set to log adding an external reference.', 'ref->log_add');
        }

        $log = new change_link($this->user());
        $log->set_action(change_action::ADD);
        $log->set_table(change_table_list::REF);
        // TODO review in log_link
        // TODO object must be loaded before it can be logged
        $log->new_from = $this->phrase();
        $log->new_link = $this->type();
        $log->new_to = $this;
        $log->row_id = 0;
        $log->add();

        return $log;
    }

    /**
     * set the main log entry parameters for updating one reference field
     */
    function log_link_upd($db_rec): change_link
    {
        log_debug('ref->log_upd ' . $this->dsp_id());
        $log = new change_link($this->user());
        $log->set_action(change_action::UPDATE);
        $log->set_table(change_table_list::REF);
        $log->old_from = $db_rec->phrase();
        $log->old_link = $db_rec->type();
        $log->old_to = $db_rec;
        $log->new_from = $this->phrase();
        $log->new_link = $this->type();
        $log->new_to = $this;
        $log->row_id = $this->id;
        $log->add();

        return $log;
    }

    /**
     * set the log entry parameter to delete a reference
     */
    function log_link_del(): change_link
    {
        log_debug('ref->log_del ' . $this->dsp_id());

        // check that the minimal parameters are set
        if ($this->phrase() == null) {
            log_err('The phrase object must be set to log deletion of an external reference.', 'ref->log_del');
        }
        if ($this->type_id() <= 0) {
            log_err('The reference type object must be set to log deletion of an external reference.', 'ref->log_del');
        }

        $log = new change_link($this->user());
        $log->set_action(change_action::DELETE);
        $log->set_table(change_table_list::REF);
        $log->old_from = $this->phrase();
        $log->old_link = $this->type();
        $log->old_to = $this;
        $log->row_id = $this->id;
        $log->add();

        return $log;
    }


    /*
     * save
     */

    /**
     * set the update parameters for the description
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @param ref|sandbox $db_rec the database record before the saving
     * @param ref|sandbox $std_rec the database record defined as standard because it is used by most users
     * @return string the message shown to the user why the action has failed or an empty string if everything is fine
     */
    private function save_field_description(sql_db $db_con, ref|sandbox $db_rec, ref|sandbox $std_rec): string
    {
        $result = '';
        // if the plural is not set, don't overwrite any db entry
        if ($this->description <> Null) {
            if ($this->description <> $db_rec->description) {
                $log = $this->log_upd_field();
                $log->old_value = $db_rec->description;
                $log->new_value = $this->description;
                $log->std_value = $std_rec->description;
                $log->row_id = $this->id;
                $log->set_field(sandbox_named::FLD_DESCRIPTION);
                $result = $this->save_field_user($db_con, $log);
            }
        }
        return $result;
    }

    /**
     * set the update parameters for the reference url
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @param ref|sandbox $db_rec the database record before the saving
     * @param ref|sandbox $std_rec the database record defined as standard because it is used by most users
     * @return string the message shown to the user why the action has failed or an empty string if everything is fine
     */
    private function save_field_url(sql_db $db_con, ref|sandbox $db_rec, ref|sandbox $std_rec): string
    {
        $result = '';
        // if the plural is not set, don't overwrite any db entry
        if ($this->url <> Null) {
            if ($this->url <> $db_rec->url) {
                $log = $this->log_upd_field();
                $log->old_value = $db_rec->url;
                $log->new_value = $this->url;
                $log->std_value = $std_rec->url;
                $log->row_id = $this->id;
                $log->set_field(self::FLD_URL);
                $result = $this->save_field_user($db_con, $log);
            }
        }
        return $result;
    }

    /**
     * set the update parameters for the reference url
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @param ref|sandbox $db_rec the database record before the saving
     * @param ref|sandbox $std_rec the database record defined as standard because it is used by most users
     * @return string the message shown to the user why the action has failed or an empty string if everything is fine
     */
    private function save_field_source(sql_db $db_con, ref|sandbox $db_rec, ref|sandbox $std_rec): string
    {
        $result = '';
        if ($db_rec->source_id() <> $this->source_id()) {
            $log = $this->log_upd_field();
            $log->old_value = $db_rec->source_name();
            $log->old_id = $db_rec->source_id();
            $log->new_value = $this->source_name();
            $log->new_id = $this->source_id();
            $log->std_value = $std_rec->source_name();
            $log->std_id = $std_rec->source_id();
            $log->row_id = $this->id();
            $log->set_field(self::FLD_SOURCE);
            $result = $this->save_field_user($db_con, $log);
        }
        return $result;
    }

    /**
     * save all updated reference fields
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @param ref|sandbox $db_rec the database record before the saving
     * @param ref|sandbox $std_rec the database record defined as standard because it is used by most users
     * @return string the message shown to the user why the action has failed or an empty string if everything is fine
     */
    function save_fields(sql_db $db_con, ref|sandbox $db_rec, ref|sandbox $std_rec): string
    {
        $result = parent::save_fields($db_con, $db_rec, $std_rec);
        $result .= $this->save_field_description($db_con, $db_rec, $std_rec);
        $result .= $this->save_field_url($db_con, $db_rec, $std_rec);
        $result .= $this->save_field_source($db_con, $db_rec, $std_rec);
        log_debug('all fields for "' . $this->dsp_id() . '" has been saved');
        return $result;
    }

    /**
     * update a ref in the database or update the existing
     * @param bool $use_func if true a predefined function is used that also creates the log entries
     * @return user_message the database id of the created reference or 0 if not successful
     */
    function add(bool $use_func = false): user_message
    {
        log_debug('ref->add ' . $this->dsp_id());

        global $db_con;
        $result = new user_message();

        if ($use_func) {
            $sc = $db_con->sql_creator();
            $qp = $this->sql_insert($sc, new sql_type_list([sql_type::LOG]));
            $usr_msg = $db_con->insert($qp, 'add and log ' . $this->dsp_id());
            if ($usr_msg->is_ok()) {
                $this->id = $usr_msg->get_row_id();
            }
            $result->add($usr_msg);
        } else {
            // log the insert attempt first
            $log = $this->log_link_add();
            if ($log->id() > 0) {
                // insert the new reference
                $db_con->set_class(ref::class);
                $db_con->set_usr($this->user()->id());

                $this->id = $db_con->insert_old(
                    array(phrase::FLD_ID, self::FLD_EX_KEY, self::FLD_TYPE),
                    array($this->phrase_id(), $this->external_key, $this->type_id));
                if ($this->id > 0) {
                    // update the id in the log for the correct reference
                    if (!$log->add_ref($this->id)) {
                        $result->add_message('Adding reference ' . $this->dsp_id() . ' in the log failed.');
                        log_err($result->get_message(), 'ref->add');
                    } else {
                        // create an empty db_rec element to force saving of all set fields
                        $db_rec = clone $this;
                        $db_rec->reset();
                        $db_rec->set_fob($this->fob());
                        $db_rec->set_tob($this->tob());
                        $db_rec->set_user($this->user());
                        $std_rec = clone $db_rec;
                        // save the object fields
                        $result->add_message($this->save_fields($db_con, $db_rec, $std_rec));
                    }
                } else {
                    $result->add_message('Adding reference ' . $this->dsp_id() . ' failed.');
                    log_err($result->get_message(), 'ref->add');
                }
            }
        }

        return $result;
    }

    /**
     * get a similar reference
     */
    function get_similar(): ref
    {
        $result = new ref($this->user());
        log_debug('ref->get_similar ' . $this->dsp_id());

        $db_chk = clone $this;
        $db_chk->reset();
        $db_chk->load_by_link_ids($this->phrase_id(), $this->type_id());
        if ($db_chk->id > 0) {
            log_debug('ref->get_similar an external reference for ' . $this->dsp_id() . ' already exists');
            $result = $db_chk;
        }

        return $result;
    }

    /**
     * update a ref in the database or update the existing
     * @param bool $use_func if true a predefined function is used that also creates the log entries
     * @return string the id of the updated or created reference
     */
    function save(?bool $use_func = null): string
    {
        log_debug();

        global $db_con;
        $result = '';

        // decide which db write method should be used
        if ($use_func === null) {
            $use_func = $this->sql_default_script_usage();
        }

        // build the database object because the is anyway needed
        if ($this->user() != null) {
            $db_con->set_usr($this->user()->id());
        }
        $db_con->set_class(ref::class);

        // check if the external reference is supposed to be added
        if ($this->id <= 0) {
            // check possible duplicates before adding
            log_debug('ref->save check possible duplicates before adding ' . $this->dsp_id());
            $similar = $this->get_similar();
            if (isset($similar)) {
                if ($similar->id <> 0) {
                    $this->id = $similar->id;
                }
            }
        }

        // create a new object or update an existing
        if ($this->id <= 0) {
            log_debug('add ' . $this->dsp_id());
            $result .= $this->add($use_func)->get_message();
        } else {
            log_debug('update ' . $this->dsp_id());

            // read the database values to be able to check if something has been changed;
            // done first, because it needs to be done for user and general object values
            $db_rec = clone $this;
            $db_rec->reset();
            $db_rec->load_by_id($this->id);
            log_debug('ref->save reloaded from db');
            $std_rec = new ref($this->user()); // must also be set to allow to take the ownership
            $std_rec->id = $this->id;
            $std_rec->load_standard();
            log_debug("standard reference settings loaded (" . $std_rec->id . ")");

            // if needed log the change and update the database
            if ($this->external_key <> $db_rec->external_key) {
                $log = $this->log_link_upd($db_rec);
                if ($log->id() > 0) {
                    $db_con->set_class(ref::class);
                    if ($db_con->update_old($this->id, self::FLD_EX_KEY, $this->external_key)) {
                        log_debug('ref->save update ... done.');
                    }
                }
            }

            // if everything has been fine until here
            // update the
            if ($result == '') {
                $result = $this->save_fields($db_con, $db_rec, $std_rec);
            }
        }

        return $result;
    }


    /*
     * del
     */

    /**
     * delete a reference of return false if it fails
     * @param bool|null $use_func if true a predefined function is used that also creates the log entries
     * @return user_message in case of an issue with the message to the user and a suggested solution
     */
    function del(?bool $use_func = null): user_message
    {
        global $db_con;
        $result = new user_message();

        $reloaded = false;
        $reloaded_id = $this->load_by_id($this->id());
        if ($reloaded_id != 0) {
            $reloaded = true;
        }
        if (!$reloaded) {
            log_warning('Reload of ref ' . $this->dsp_id() . ' for deletion failed', 'ref->del');
        } else {
            if ($this->id <= 0) {
                log_warning('Delete failed, because it seems that the ref ' . $this->dsp_id() . ' has been deleted in the meantime.', 'ref->del');
            } else {
                $log = $this->log_link_del();
                if ($log->id() > 0) {
                    $db_con->set_class(ref::class);
                    $del_result = $db_con->delete_old(self::FLD_ID, $this->id);
                    if ($del_result == '') {
                        log_debug('done.');
                    } else {
                        $result->add_message($del_result);
                    }
                }
            }
        }
        return $result;
    }


    /*
     * sql write fields
     */

    /**
     * get a list of all database fields that might be changed
     * excluding the internal fields e.g. the database id
     * field list must be corresponding to the db_fields_changed fields
     *
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return array list of all database field names that have been updated
     */
    function db_fields_all(sql_type_list $sc_par_lst = new sql_type_list([])): array
    {
        return array_merge(
            parent::db_all_fields_link($sc_par_lst),
            [
                self::FLD_TYPE,
                phrase::FLD_ID,
                self::FLD_EX_KEY,
                self::FLD_URL,
                source::FLD_ID,
                sandbox_named::FLD_DESCRIPTION,
            ],
            parent::db_fields_all_sandbox()
        );
    }

    /**
     * get a list of database field names, values and types that have been updated
     *
     * @param sandbox|ref $sbx the compare value to detect the changed fields
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par_field_list list 3 entry arrays with the database field name, the value and the sql type that have been updated
     */
    function db_fields_changed(
        sandbox|ref   $sbx,
        sql_type_list $sc_par_lst = new sql_type_list([])
    ): sql_par_field_list
    {
        global $change_field_list;

        $sc = new sql();
        $do_log = $sc_par_lst->incl_log();
        $usr_tbl = $sc_par_lst->is_usr_tbl();
        $table_id = $sc->table_id($this::class);

        $lst = parent::db_fields_changed($sbx, $sc_par_lst);
        // the link type cannot be changed by the user, because this would be another link
        if (!$usr_tbl) {
            // for insert into the standard table the type field should always be included
            // because it is part of the prime indey
            if ($sbx->type_id() <> $this->type_id() or $sc_par_lst->is_insert()) {
                if ($do_log) {
                    $lst->add_field(
                        sql::FLD_LOG_FIELD_PREFIX . ref_type::FLD_ID,
                        $change_field_list->id($table_id . ref_type::FLD_ID),
                        change::FLD_FIELD_ID_SQLTYP
                    );
                }
                global $ref_types;
                $lst->add_type_field(
                    ref_type::FLD_ID,
                    type_object::FLD_NAME,
                    $this->type_id(),
                    $sbx->type_id(),
                    $ref_types
                );
            }
        }
        if ($sc_par_lst->is_insert()) {
            if ($sbx->phrase_id() <> $this->phrase_id()) {
                if ($do_log) {
                    $lst->add_field(
                        sql::FLD_LOG_FIELD_PREFIX . phrase::FLD_ID,
                        $change_field_list->id($table_id . phrase::FLD_ID),
                        change::FLD_FIELD_ID_SQLTYP
                    );
                }
                $lst->add_link_field(
                    phrase::FLD_ID,
                    phrase::FLD_NAME,
                    $this->phrase(),
                    $sbx->phrase()
                );
            }
        }
        if ($sbx->external_key <> $this->external_key) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . self::FLD_EX_KEY,
                    $change_field_list->id($table_id . self::FLD_EX_KEY),
                    change::FLD_FIELD_ID_SQLTYP
                );
            }
            $old_key = $sbx->external_key;
            if ($sc_par_lst->is_insert() and $old_key == '') {
                $old_key = null;
            }
            $lst->add_field(
                self::FLD_EX_KEY,
                $this->external_key,
                self::FLD_EX_KEY_SQLTYP,
                $old_key
            );
        }
        if ($sbx->url <> $this->url) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . self::FLD_URL,
                    $change_field_list->id($table_id . self::FLD_URL),
                    change::FLD_FIELD_ID_SQLTYP
                );
            }
            $lst->add_field(
                self::FLD_URL,
                $this->url,
                self::FLD_URL_SQLTYP,
                $sbx->url
            );
        }
        if ($sbx->source?->id() <> $this->source?->id()) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . source::FLD_ID,
                    $change_field_list->id($table_id . source::FLD_ID),
                    change::FLD_FIELD_ID_SQLTYP
                );
            }
            $lst->add_link_field(
                source::FLD_ID,
                source::FLD_NAME,
                $this->source,
                $sbx->source
            );
        }
        if ($sbx->description <> $this->description) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . sandbox_named::FLD_DESCRIPTION,
                    $change_field_list->id($table_id . sandbox_named::FLD_DESCRIPTION),
                    change::FLD_FIELD_ID_SQLTYP
                );
            }
            $lst->add_field(
                sandbox_named::FLD_DESCRIPTION,
                $this->description,
                sandbox_named::FLD_DESCRIPTION_SQLTYP,
                $sbx->description
            );
        }
        return $lst->merge($this->db_changed_sandbox_list($sbx, $sc_par_lst));
    }

    /*
      * debug
      */

    /**
     * @return string with the unique id fields
     */
    function dsp_id(): string
    {
        $result = $this->name();
        if ($result <> '') {
            if ($this->id > 0) {
                $result .= ' (' . $this->id . ')';
            }
        } else {
            $result .= $this->id;
        }
        return $result;
    }

    /**
     * @return string with the unique name
     */
    function name(): string
    {
        $result = '';

        if ($this->phrase() != null) {
            $result .= 'ref of "' . $this->phrase()->name() . '"';
        } else {
            if ($this->phrase_id() != 0) {
                $result .= 'ref of phrase id ' . $this->phrase_id() . ' ';
            }
        }
        if ($this->has_type()) {
            $result .= ' to "' . $this->type_name() . '"';
        } else {
            if ($this->type_id != null) {
                if ($this->type_id > 0) {
                    $result .= 'to type id ' . $this->type_id() . ' ';
                }
            }
        }
        return $result;
    }

}