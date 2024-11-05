<?php

/*

    model/word/triple.php - the object that links two words (an RDF triple)
    ---------------------

    A link can also be used in replacement for a word
    e.g. "Zurich (Company)" where the link "Zurich is a company" is used

    The main sections of this object are
    - db const:          const for the database link
    - im/export const:   const for the im and export link
    - object vars:       the variables of this word object
    - construct and map: including the mapping of the db row to this word object
    - set and get:       to capsule the vars from unexpected changes
    - modify:            change potentially all variables of this word object
    - preloaded:         select e.g. types from cache
    - fields:            the field names of this object as overwrite functions
    - cast:              create an api object and set the vars from an api json
    - load:              database access object (DAO) functions
    - im- and export:    create an export object and set the vars from an import object
    - information:       functions to make code easier to read
    - internal:          e.g. to generate the name based on the link
    - save:              manage to update the database
    - sql write:         sql statement creation to write to the database
    - sql write fields:  field list for writing to the database
    - debug:             internal support functions for debugging
    - display:           to be moved to the frontend object


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

    Copyright (c) 1995-2024 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace cfg;

include_once SHARED_TYPES_PATH . 'protection_type.php';
include_once SHARED_TYPES_PATH . 'share_type.php';
include_once DB_PATH . 'sql_par_type.php';
include_once MODEL_SANDBOX_PATH . 'sandbox_link_named.php';
include_once SERVICE_EXPORT_PATH . 'triple_exp.php';
include_once SHARED_TYPES_PATH . 'phrase_type.php';

use api\system\messeges as msg_enum;
use cfg\db\sql_par_field_list;
use cfg\db\sql_type_list;
use shared\types\protection_type as protect_type_shared;
use shared\types\share_type as share_type_shared;
use api\api;
use api\word\triple as triple_api;
use cfg\db\sql;
use cfg\db\sql_db;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
use cfg\db\sql_par;
use cfg\db\sql_par_type;
use cfg\db\sql_type;
use cfg\export\sandbox_exp;
use cfg\export\triple_exp;
use cfg\log\change;
use cfg\log\change_action;
use cfg\log\change_link;
use cfg\log\change_table_list;
use cfg\value\value_list;
use html\html_base;
use JsonSerializable;
use shared\library;
use shared\types\phrase_type AS phrase_type_shared;


class triple extends sandbox_link_named implements JsonSerializable
{

    /*
     * db const
     */

    // comment used for the database creation
    const TBL_COMMENT = 'to link one word or triple with a verb to another word or triple';

    // object specific database and JSON object field names
    const FLD_ID = 'triple_id';
    const FLD_FROM_COM = 'the phrase_id that is linked';
    const FLD_FROM = 'from_phrase_id';
    const FLD_VERB_COM = 'the verb_id that defines how the phrases are linked';
    const FLD_TO_COM = 'the phrase_id to which the first phrase is linked';
    const FLD_TO = 'to_phrase_id';
    const FLD_NAME_COM = 'the name used which must be unique within the terms of the user';
    const FLD_NAME = 'triple_name';
    const FLD_NAME_GIVEN_COM = 'the unique name manually set by the user, which can be null if the generated name should be used';
    const FLD_NAME_GIVEN = 'name_given';
    const FLD_NAME_GIVEN_SQL_TYP = sql_field_type::NAME;
    const FLD_NAME_AUTO_COM = 'the generated name is saved in the database for database base unique check based on the phrases and verb, which can be overwritten by the given name';
    const FLD_NAME_AUTO = 'name_generated';
    const FLD_NAME_AUTO_SQL_TYP = sql_field_type::NAME;
    const FLD_DESCRIPTION_COM = 'text that should be shown to the user in case of mouseover on the triple name';
    const FLD_DESCRIPTION_SQL_TYP = sql_field_type::TEXT;
    const FLD_VIEW_COM = 'the default mask for this triple';
    const FLD_VIEW = 'view_id';
    const FLD_VIEW_SQL_TYP = sql_field_type::INT;
    const FLD_VALUES_COM = 'number of values linked to the word, which gives an indication of the importance';
    const FLD_VALUES = 'values';
    const FLD_VALUES_SQL_TYP = sql_field_type::INT;
    const FLD_INACTIVE_COM = 'true if the word is not yet active e.g. because it is moved to the prime words with a 16 bit id';
    const FLD_INACTIVE = 'inactive';
    const FLD_CODE_ID_COM = 'to link coded functionality to a specific triple e.g. to get the values of the system configuration';
    const FLD_COND_ID_COM = 'formula_id of a formula with a boolean result; the term is only added if formula result is true';
    const FLD_COND_ID = 'triple_condition_id';
    const FLD_REFS = 'refs';

    // list of fields that MUST be set by one user
    const FLD_LST_LINK = array(
        [self::FLD_FROM, sql_field_type::INT_UNIQUE_PART, sql_field_default::NOT_NULL, sql::INDEX, '', self::FLD_FROM_COM],
        [verb::FLD_ID, sql_field_type::INT_UNIQUE_PART, sql_field_default::NOT_NULL, sql::INDEX, verb::class, self::FLD_VERB_COM],
        [self::FLD_TO, sql_field_type::INT_UNIQUE_PART, sql_field_default::NOT_NULL, sql::INDEX, '', self::FLD_TO_COM],
    );
    // list of must fields that CAN be changed by the user
    const FLD_LST_MUST_BUT_USER_CAN_CHANGE = array(
        [language::FLD_ID, sql_field_type::KEY_PART_INT, sql_field_default::ONE, sql::INDEX, language::class, self::FLD_NAME_COM],
    );
    // list of fields that CAN be changed by the user
    const FLD_LST_USER_CAN_CHANGE = array(
        [self::FLD_NAME, sql_field_type::NAME, sql_field_default::NULL, sql::INDEX, '', self::FLD_NAME_COM],
        [self::FLD_NAME_GIVEN, self::FLD_NAME_GIVEN_SQL_TYP, sql_field_default::NULL, sql::INDEX, '', self::FLD_NAME_GIVEN_COM],
        [self::FLD_NAME_AUTO, self::FLD_NAME_AUTO_SQL_TYP, sql_field_default::NULL, sql::INDEX, '', self::FLD_NAME_AUTO_COM],
        [sandbox_named::FLD_DESCRIPTION, self::FLD_DESCRIPTION_SQL_TYP, sql_field_default::NULL, '', '', self::FLD_DESCRIPTION_COM],
        [self::FLD_COND_ID, sql_field_type::INT, sql_field_default::NULL, '', '', self::FLD_COND_ID_COM],
        [phrase::FLD_TYPE, phrase::FLD_TYPE_SQL_TYP, sql_field_default::NULL, sql::INDEX, phrase_type::class, word::FLD_TYPE_COM],
        [self::FLD_VIEW, sql_field_type::INT, sql_field_default::NULL, sql::INDEX, view::class, self::FLD_VIEW_COM],
        [self::FLD_VALUES, sql_field_type::INT, sql_field_default::NULL, '', '', self::FLD_VALUES_COM],
    );
    // list of fields that CANNOT be changed by the user
    const FLD_LST_NON_CHANGEABLE = array(
        [self::FLD_INACTIVE, sql_field_type::INT_SMALL, sql_field_default::NULL, '', '', self::FLD_INACTIVE_COM],
        [sql::FLD_CODE_ID, sql_field_type::NAME_UNIQUE, sql_field_default::NULL, '', '', self::FLD_CODE_ID_COM],
    );

    // all database field names excluding the id and excluding the user specific fields
    const FLD_NAMES = array(
        phrase::FLD_TYPE,
        self::FLD_COND_ID
    );
    // list of the link database field names
    // TODO use this name for all links
    const FLD_NAMES_LINK = array(
        self::FLD_FROM,
        verb::FLD_ID,
        self::FLD_TO
    );
    // list of the user specific database field names
    const FLD_NAMES_USR = array(
        self::FLD_NAME,
        self::FLD_NAME_GIVEN,
        self::FLD_NAME_AUTO,
        sandbox_named::FLD_DESCRIPTION
    );
    // list of the user specific numeric database field names
    const FLD_NAMES_NUM_USR = array(
        self::FLD_VALUES,
        sandbox::FLD_EXCLUDED,
        sandbox::FLD_SHARE,
        sandbox::FLD_PROTECT
    );
    // all database field names excluding the id used to identify if there are some user specific changes
    const ALL_SANDBOX_FLD_NAMES = array(
        self::FLD_NAME,
        self::FLD_NAME_GIVEN,
        self::FLD_NAME_AUTO,
        sandbox_named::FLD_DESCRIPTION,
        phrase::FLD_TYPE,
        self::FLD_VALUES,
        sandbox::FLD_EXCLUDED,
        sandbox::FLD_SHARE,
        sandbox::FLD_PROTECT
    );


    /*
     * im- and export const
     */

    // the field names used for the im- and export in the json or yaml format
    const FLD_EX_FROM = 'from';
    const FLD_EX_TO = 'to';
    const FLD_EX_VERB = 'verb';


    /*
     * object vars
     */

    // triple vars additional to the name and link vars of the parent user sandbox object
    private ?string $name_given;     // the name manually set by the user, which can be empty
    private string $name_generated; // the generated name based on the linked objects and saved in the database for faster searching
    public ?int $values;            // the total number of values linked to this triple as an indication how common the triple is and to sort the triples

    // only used for the export object
    private ?view $view; // name of the default view for this word
    private ?array $ref_lst = [];


    /*
     * construct and map
     */

    /**
     * define the settings for this triple object
     * @param user $usr the user who requested to see this triple
     */
    function __construct(user $usr)
    {
        $this->set_id(0);

        parent::__construct($usr);

        $this->rename_can_switch = UI_CAN_CHANGE_triple_NAME;

        $this->reset();
        $this->name_given = null;
        $this->name_generated = '';

        // also create the link objects because there is now case where they are supposed to be null
        $this->create_objects();
    }

    /**
     * reset the in memory fields used e.g. if some ids are updated
     */
    function reset(): void
    {
        parent::reset();
        $this->set_name('');
        $this->name_given = null;
        $this->name_generated = '';
        $this->values = null;

        $this->view = null;
        $this->ref_lst = [];

        $this->create_objects();
    }

    private function create_objects(
        string $from = '',
        string $verb = '',
        string $to = ''
    ): void
    {
        $this->set_fob(new phrase($this->user()));
        $this->fob()->set_name($from);
        $this->set_tob(new phrase($this->user()));
        $this->tob()->set_name($to);
    }

    /**
     * map the database fields to the object fields
     *
     * @param array|null $db_row with the data directly from the database
     * @param bool $load_std true if only the standard user sandbox object is loaded
     * @param bool $allow_usr_protect false for using the standard protection settings for the default object used for all users
     * @param string $id_fld the name of the id field as defined in this child and given to the parent
     * @return bool true if the triple is loaded and valid
     */
    function row_mapper_sandbox(
        ?array $db_row,
        bool   $load_std = false,
        bool   $allow_usr_protect = true,
        string $id_fld = self::FLD_ID,
        string $name_fld = self::FLD_NAME,
        string $type_fld = phrase::FLD_TYPE): bool
    {
        $result = parent::row_mapper_sandbox($db_row, $load_std, $allow_usr_protect, $id_fld, $name_fld);
        if ($result) {
            if (array_key_exists(self::FLD_FROM, $db_row)) {
                $phr_id = $db_row[self::FLD_FROM];
                if ($phr_id != null) {
                    $this->from()->set_obj_from_id($phr_id);
                }
            }
            if (array_key_exists(self::FLD_TO, $db_row)) {
                $phr_id = $db_row[self::FLD_TO];
                if ($phr_id != null) {
                    $this->to()->set_obj_from_id($phr_id);
                }
            }
            if (array_key_exists(verb::FLD_ID, $db_row)) {
                if ($db_row[verb::FLD_ID] != null) {
                    $this->set_verb_id($db_row[verb::FLD_ID]);
                }
            }
            if (array_key_exists(self::FLD_NAME_GIVEN, $db_row)) {
                $this->set_name_given($db_row[self::FLD_NAME_GIVEN]);
            }
            if (array_key_exists(self::FLD_NAME_AUTO, $db_row)) {
                $this->set_name_generated($db_row[self::FLD_NAME_AUTO]);
            }
            if (array_key_exists($type_fld, $db_row)) {
                $this->type_id = $db_row[$type_fld];
            }
            if (array_key_exists(self::FLD_VALUES, $db_row)) {
                $this->values = $db_row[self::FLD_VALUES];
            }
        }
        return $result;
    }


    /*
     * set and get
     */

    /**
     * set the most used object vars with one set statement
     * @param int $id mainly for test creation the database id of the word
     * @param string $name mainly for test creation the name of the word
     */
    function set(
        int    $id = 0,
        string $name = '',
        string $from = '',
        string $verb = '',
        string $to = ''
    ): void
    {
        parent::set_id($id);
        if ($name != '') {
            $this->set_name($name);
        }
        $this->create_objects($from, $verb, $to);
    }

    /**
     * set the "from" phrase of this triple
     * e.g. "Zurich" for "Zurich (city)" based on "Zurich" (from) "is a" (verb) "city" (to)
     *
     * @param phrase $from_phr the "from" phrase
     * @return void
     */
    function set_from(phrase $from_phr): void
    {
        $this->set_fob($from_phr);
    }

    function from(): phrase|sandbox_named|combine_named|null
    {
        return $this->fob();
    }

    /**
     * set the "from" phrase of this triple
     * e.g. "Zurich" for "Zurich (city)" based on "Zurich" (from) "is a" (verb) "city" (to)
     *
     * @param verb $vrb the verb
     * @return void
     */
    function set_verb(verb $vrb): void
    {
        if ($vrb->id() != 0) {
            $this->set_predicate_id($vrb->id());
        } else {
            if ($vrb->name() != '') {
                global $verbs;
                $vrb_selected = $verbs->get_by_name($vrb->name());
                $this->set_predicate_id($vrb_selected->id());
            }
        }
    }

    /**
     * set the id of the link predicate
     * in case of triple objects the id can be negative, which means that the object is used to test the reverse case
     * @param int $id if > 0 the id of the verb, if < 0 the reverse case and if 0 the verb is not yet set
     * @return void
     */
    function set_verb_id(int $id): void
    {
        $this->set_predicate_id($id);
    }

    function verb(): verb|null
    {
        global $verbs;
        $id = $this->predicate_id();
        if ($id == 0) {
            return null;
        } else {
            return $verbs->get($id);
        }
    }

    /**
     * @return bool true if the verb of the triple is set
     */
    function has_verb(): bool
    {
        if ($this->predicate_id() != 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return int|null the id of predicate which is in case of the triple the verb id
     */
    function verb_id(): int|null
    {
        return $this->predicate_id();
    }

    /**
     * @return string the name of the verb
     */
    function verb_name(): string
    {
        global $verbs;
        $id = $this->predicate_id();
        if ($id > 0) {
            $vrb = $verbs->get($this->predicate_id());
            return $vrb->name();
        } elseif ($id < 0) {
            $vrb = $verbs->get($this->predicate_id() * -1);
            return $vrb->reverse();
        } else {
            return '';
        }
    }

    /**
     * overwrite the link type function
     * @return string|null the name of the verb
     */
    function predicate_name(): ?string
    {
        return $this->verb_name();
    }

    /**
     * set the "from" phrase of this triple
     * e.g. "city" for "Zurich (city)" based on "Zurich" (from) "is a" (verb) "city" (to)
     *
     * @param phrase $to_phr the code id that should be added to this triple
     * @return void
     */
    function set_to(phrase $to_phr): void
    {
        $this->set_tob($to_phr);
    }

    function to(): phrase|sandbox_named|combine_named|null
    {
        return $this->tob();
    }

    /**
     * set the phrase type of this triple
     * if the type id is null or 0 the phrase type from the "to" phrase is returned
     *
     * @param string $type_code_id the code id that should be added to this triple
     * @return void
     */
    function set_type(string $type_code_id): void
    {
        global $phrase_types;
        $this->type_id = $phrase_types->id($type_code_id);
    }

    /**
     * set the name used object
     * @param string $name
     * @return void
     */
    function set_name(string $name): void
    {
        $this->name = $name;
    }

    /**
     * set the name manually set by the user and set the used name if needed
     * @param string|null $name_given
     * @return void
     */
    function set_name_given(?string $name_given): void
    {
        $this->name_given = $name_given;
    }

    /**
     *
     * @param string|null $name_generated the generated name as saved in the database
     * @return void
     */
    function set_name_generated(?string $name_generated): void
    {
        if ($name_generated != null) {
            // use the updated generated name or the generated name loaded from the database
            $this->name_generated = $name_generated;
        } else {
            // worst case use an empty string
            $this->name_generated = '';
            log_warning('No name found for triple ' . $this->id());
        }
    }

    /**
     * set the used name, update the generated name if needed
     * @return void
     */
    function set_names(): void
    {
        // update the generated name if needed
        if ($this->generate_name() != '' and $this->generate_name() != ' ()') {
            $this->name_generated = $this->generate_name();
        }
        // remove the given name if not needed
        if ($this->name_given == $this->name_generated) {
            $this->name_given = null;
        } else {
            // or set the given name if needed e.g. when called be json import
            if ($this->name != '' and $this->name != $this->name_generated) {
                $this->name_given = $this->name;
            }
        }
        // use the generated name as fallback
        if ($this->name == '') {
            if ($this->name_given != null and $this->name_given != '') {
                $this->name = $this->name_given;
            } else {
                $this->name = $this->name_generated;
            }
        }
    }

    /**
     * set the value to rank the triple by usage
     *
     * @param int|null $usage a higher value moves the triple to the top of the selection list
     * @return void
     */
    function set_usage(?int $usage): void
    {
        $this->values = $usage;
    }

    /**
     * @return int|null a higher number indicates a higher usage
     */
    function usage(): ?int
    {
        return $this->values;
    }

    /**
     * @return string|null the name manually set by the user or null if the generated name should be used
     */
    function name_given(): ?string
    {
        return $this->name_given;
    }

    /**
     * TODO check where the function or the db value should be used
     */
    function name_generated(): ?string
    {
        return $this->name_generated;
    }

    /**
     * @return string|null the description of the link which should be shown to the user as mouseover
     */
    function description(): ?string
    {
        return $this->description();
    }

    /**
     * get the database id of the phrase type
     * @return int|null the id of the word type
     */
    function type_id(): ?int
    {
        return $this->type_id;
    }

    /**
     * @return int the id of the default view for this triple or null if no view is preferred
     */
    function view_id(): int
    {
        if ($this->view == null) {
            return 0;
        } else {
            return $this->view->id();
        }
    }

    /**
     * create a clone and keep additional the verb as it is a unique db id for the triple
     *
     * @param string $name the target name
     * @return $this a clone with the name changed
     */
    function cloned_named(string $name): sandbox_link_named
    {

        $obj_cpy = parent::cloned_named($name);
        $obj_cpy->set_verb($this->verb());
        return $obj_cpy;
    }

    /**
     * copy the link objects from this object to the given triple
     * used to unset any changes in the link to detect only the changes fields that the user is allowed to change
     *
     * @param sandbox_link|triple $lnk
     * @return triple
     */
    function set_link_objects(sandbox_link|triple $lnk): triple
    {
        $lnk->set_fob($this->fob());
        $lnk->set_verb($this->verb());
        $lnk->set_tob($this->tob());
        return $lnk;
    }


    /*
     * modify
     */

    /**
     * fill this triple based on the given triple
     * if the id is set in the given word loaded from the database but this import word does not yet have the db id, set the id
     * if the given name is not set (null) the given name is not remove
     * if the given name is an empty string the given name is removed
     *
     * @param triple|db_object_seq_id $sbx word with the values that sould been updated e.g. based on the import
     * @return user_message a warning in case of a conflict e.g. due to a missing change time
     */
    function fill(triple|db_object_seq_id $sbx): user_message
    {
        $usr_msg = parent::fill($sbx);
        if ($sbx->name_given != null) {
            $this->name_given = $sbx->name_given;
        }
        if ($sbx->name_generated != null) {
            $this->name_generated = $sbx->name_generated;
        }
        if ($sbx->values != null) {
            $this->values = $sbx->values;
        }
        return $usr_msg;
    }


    /*
     * preloaded
     */

    /**
     * get the name of the triple type
     * @return string the name of the triple type
     */
    function type_name(): string
    {
        global $phrase_types;
        return $phrase_types->name($this->type_id);
    }

    /**
     * get the code_id of the word type
     * @return string the code_id of the word type
     */
    function type_code_id(): string
    {
        global $phrase_types;
        if ($this->type_id == null) {
            return '';
        } else {
            return $phrase_types->code_id($this->type_id);
        }
    }

    // TODO add a function for each type and streamline the call

    /**
     * @return bool
     */
    function is_time(): bool
    {
        if ($this->type_code_id() == phrase_type_shared::TIME) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool true if the triple is of the given type
     */
    function is_type(string $typ): bool
    {
        global $phrase_types;

        $result = false;
        if ($this->type_id == $phrase_types->id($typ)) {
            $result = true;
        }
        return $result;
    }


    /*
     * fields
     */

    function from_field(): string
    {
        return self::FLD_FROM;
    }

    function type_field(): string
    {
        return verb::FLD_ID;
    }

    function type_name_field(): string
    {
        return verb::FLD_NAME;
    }

    function to_field(): string
    {
        return self::FLD_TO;
    }


    /*
     * cast
     */

    /**
     * @return triple_api the filled triple frontend api object
     */
    function api_obj(): triple_api
    {
        $api_obj = new triple_api();
        if ($this->is_excluded()) {
            $api_obj->set_id($this->id());
            $api_obj->excluded = true;
        } else {
            parent::fill_api_obj($api_obj);
            if ($this->from()->obj() != null) {
                if ($this->from()->obj()->id() <> 0 or $this->from()->obj()->name() != '') {
                    $api_obj->set_from($this->from()->obj()->api_obj()->phrase());
                }
            }
            if ($this->verb() != null) {
                $api_obj->set_verb($this->verb()->api_verb_obj());
            }
            if ($this->to()->obj() != null) {
                if ($this->to()->obj()->id() <> 0 or $this->to()->obj()->name() != '') {
                    $api_obj->set_to($this->to()->obj()?->api_obj()->phrase());
                }
            }
        }
        return $api_obj;
    }

    /**
     * map a triple api json to this model triple object
     * similar to the import_obj function but using the database id instead of names as the unique key
     * @param array $api_json the api array with the triple values that should be mapped
     * @return user_message the message for the user why the action has failed and a suggested solution
     */
    function set_by_api_json(array $api_json): user_message
    {
        $msg = parent::set_by_api_json($api_json);

        foreach ($api_json as $key => $value) {

            if ($key == api::FLD_FROM) {
                if ($value != 0) {
                    // TODO use phrase cache
                    $phr = new phrase($this->user());
                    $phr->set_id($value);
                    $this->set_from($phr);
                }
            }
            if ($key == api::FLD_TO) {
                if ($value != 0) {
                    // TODO use phrase cache
                    $phr = new phrase($this->user());
                    $phr->set_id($value);
                    $this->set_to($phr);
                }
            }
            if ($key == api::FLD_VERB) {
                if ($value != 0) {
                    global $verbs;
                    $vrb = $verbs->get($value);
                    $this->set_verb($vrb);
                }
            }

            /* TODO
            if ($key == self::FLD_PLURAL) {
                if ($value <> '') {
                    $this->plural = $value;
                }
            }
            if ($key == share_type_shared::JSON_FLD) {
                $this->share_id = $share_types->id($value);
            }
            if ($key == protect_type_shared::JSON_FLD) {
                $this->protection_id = $protection_types->id($value);
            }
            if ($key == exp_obj::FLD_VIEW) {
                $wrd_view = new view($this->user());
                if ($do_save) {
                    $wrd_view->load_by_name($value);
                    if ($wrd_view->id() == 0) {
                        $result->add_message('Cannot find view "' . $value . '" when importing ' . $this->dsp_id());
                    } else {
                        $this->view_id = $wrd_view->id();
                    }
                } else {
                    $wrd_view->set_name($value);
                }
                $this->view = $wrd_view;
            }

            if ($key == api::FLD_PHRASES) {
                $phr_lst = new phrase_list($this->user());
                $msg->add($phr_lst->db_obj($value));
                if ($msg->is_ok()) {
                    $this->grp->phr_lst = $phr_lst;
                }
            }
            */

        }

        return $msg;
    }

    /**
     * convert the word object into a phrase object
     */
    function phrase(): phrase
    {
        $phr = new phrase($this->user());
        // the triple has positive id, but the phrase uses a negative id
        $phr->set_name($this->name, triple::class);
        $phr->set_obj($this);
        log_debug('triple->phrase of ' . $this->dsp_id());
        return $phr;
    }

    /**
     * @returns term the triple object cast into a term object
     * TODO remove lines not needed any more
     */
    function term(): term
    {
        $trm = new term($this->user());
        $trm->set_id_from_obj($this->id(), self::class);
        $trm->set_name($this->name(), triple::class);
        $trm->set_obj($this);
        log_debug($this->dsp_id());
        return $trm;
    }


    /*
     * load
     */

    /**
     * load a triple by name
     * @param string $name the name of the word, triple, formula, verb, view or view component
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_name(string $name): int
    {
        global $db_con;

        log_debug($name);
        $qp = $this->load_sql_by_name($db_con->sql_creator(), $name);
        return $this->load($qp);
    }

    /**
     * load a triple by the generated name (the name that the triple would have if the user has done not overwrite)
     * @param string $name the generated name of the triple
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_name_generated(string $name): int
    {
        global $db_con;

        log_debug($name);
        $qp = $this->load_sql_by_name_generated($db_con->sql_creator(), $name, $this::class);
        return $this->load($qp);
    }

    /**
     * load a triple by the ids of the linked objects
     * @param int $from the id of the phrase that is linked
     * @param int $predicate_id the type id of the link
     * @param int|string $to the id of the phrase to which is the link directed
     * @param string $class the name of the child class from where the call has been triggered
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_link_id(int $from, int $predicate_id = 0, int|string $to = 0, string $class = self::class): int
    {
        global $db_con;

        log_debug($from . ' ' . $predicate_id . ' ' . $to);
        $qp = $this->load_sql_by_link($db_con->sql_creator(), $from, $predicate_id, $to, $class);
        return $this->load($qp);
    }

    /**
     * load the triple parameters for all users
     *
     * @param sql_par|null $qp placeholder to align the function parameters with the parent
     * @return bool true if the standard triple has been loaded
     */
    function load_standard(?sql_par $qp = null): bool
    {
        global $db_con;

        // after every load call from outside the class the order should be checked and reversed if needed
        $this->check_order();

        $qp = $this->load_standard_sql($db_con->sql_creator());

        $db_lnk = $db_con->get1($qp);
        $result = $this->row_mapper_sandbox($db_lnk, true);
        if ($result) {
            $result = $this->load_owner();

            // automatically update the generic name
            if ($result) {
                $this->load_objects();
                $new_name = $this->name();
                log_debug('triple->load_standard check if name ' . $this->dsp_id() . ' needs to be updated to "' . $new_name . '"');
                if ($new_name <> $this->name) {
                    $db_con->set_class(triple::class);
                    $result = $db_con->update_old($this->id(), self::FLD_NAME_GIVEN, $new_name);
                    $this->name = $new_name;
                }
            }
            log_debug('triple->load_standard ... done (' . $this->description . ')');
        }

        return $result;
    }

    /**
     * create the SQL to load the default triple always by the id
     *
     * @param sql $sc with the target db_type set
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_standard_sql(sql $sc): sql_par
    {
        $sc->set_class($this::class);
        $qp = new sql_par($this::class, new sql_type_list([sql_type::NORM]));
        $qp->name .= $this->load_sql_name_ext();
        $sc->set_name($qp->name);
        $sc->set_usr($this->user()->id());
        $sc->set_fields(array_merge(
            self::FLD_NAMES_LINK,
            self::FLD_NAMES,
            self::FLD_NAMES_USR,
            self::FLD_NAMES_NUM_USR,
            array(user::FLD_ID)
        ));

        return $this->load_sql_select_qp($sc, $qp);
    }

    /**
     * create the common part of an SQL statement to retrieve the parameters of a triple from the database
     *
     * @param sql $sc with the target db_type set
     * @param string $query_name the name extension to make the query name unique
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql $sc, string $query_name, string $class = self::class): sql_par
    {
        $qp = new sql_par($class);
        $qp->name .= $query_name;

        $sc->set_class($class);
        $sc->set_name($qp->name);
        $sc->set_usr($this->user()->id());
        $sc->set_fields(array_merge(self::FLD_NAMES_LINK, self::FLD_NAMES));
        $sc->set_usr_fields(self::FLD_NAMES_USR);
        $sc->set_usr_num_fields(self::FLD_NAMES_NUM_USR);

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a triple by name from the database
     *
     * @param sql $sc with the target db_type set
     * @param string $name the name of the triple and the related word, triple, formula or verb
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_name(sql $sc, string $name): sql_par
    {
        $qp = $this->load_sql($sc, sql_db::FLD_NAME, $this::class);
        $sc->add_where($this->name_field(), $name, sql_par_type::TEXT_USR);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a triple by the generated name from the database
     *
     * @param sql $sc with the target db_type set
     * @param string $name the generated name of the triple and the related word, triple, formula or verb
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_name_generated(sql $sc, string $name, string $class): sql_par
    {
        $qp = $this->load_sql($sc, 'name_generated', $class);
        $sc->add_where(self::FLD_NAME_AUTO, $name, sql_par_type::TEXT_USR);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a triple by name from the database
     *
     * @param sql $sc with the target db_type set
     * @param int $from the id of the phrase that is linked
     * @param int $predicate_id the type id of the link
     * @param int|string $to the id of the phrase to which is the link directed
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_link(sql $sc, int $from, int $predicate_id, int|string $to, string $class): sql_par
    {
        $qp = $this->load_sql($sc, 'link_ids', $class);
        $sc->add_where(self::FLD_FROM, $from);
        $sc->add_where(self::FLD_TO, $to);
        $sc->add_where(verb::FLD_ID, $predicate_id);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * set the generated triple name base on the view
     */
    private function reload_generated_name(): void
    {
        global $db_con;

        if ($this->id() > 0) {
            // automatically update the generic name
            $this->load_objects();
            $new_name = $this->name_generated();
            log_debug('triple->load check if name ' . $this->dsp_id() . ' needs to be updated to "' . $new_name . '"');
            if ($new_name <> $this->name_generated) {
                $db_con->set_class(triple::class);
                $db_con->update_old($this->id(), self::FLD_NAME_AUTO, $new_name);
                $this->set_name_generated($new_name);
            }
        }
    }

    /**
     * load a named user sandbox object e.g. word, triple, formula, verb or view from the database
     * @param sql_par $qp the query parameters created by the calling function
     * @return int the id of the object found and zero if nothing is found
     */
    protected function load(sql_par $qp): int
    {
        global $db_con;

        $db_row = $db_con->get1($qp);
        $this->row_mapper_sandbox($db_row);
        $this->reload_generated_name();
        return $this->id();
    }

    function name_field(): string
    {
        return self::FLD_NAME;
    }

    function all_sandbox_fields(): array
    {
        return self::ALL_SANDBOX_FLD_NAMES;
    }

    /**
     * if needed reverse the order if the user has entered it the other way round
     * e.g. "Cask Flow Statement" "contains" "Taxes" instead of "Taxes" "is part of" "Cask Flow Statement"
     */
    private function check_order(): void
    {
        if ($this->verb_id() < 0) {
            $to = $this->to();
            $this->set_to($this->from());
            $this->set_verb_id($this->verb_id() * -1);
            /*
             * TODO remove
            if ($this->has_verb()) {
                $this->verb->set_name($this->verb->reverse);
            }
            */
            $this->set_from($to);
            log_debug('reversed');
        }
    }

    /**
     * load the triple without the linked objects, because in many cases the object are already loaded by the caller
     * similar to term->load, but with a different use of verbs
     */
    function load_objects(): bool
    {
        log_debug($this->dsp_id());
        $result = true;

        // after every load call from outside the class the order should be checked and reversed if needed
        $this->check_order();

        // load the "from" phrase
        if ($this->from() == null) {
            log_err("The word (" . $this->from_id() . ") must be set before it can be loaded.", "triple->load_objects");
        } else {
            if ($this->from_id() <> 0 and !is_null($this->user()->id())) {
                if ($this->from_id() > 0) {
                    $wrd = new word($this->user());
                    $wrd->load_by_id($this->from_id());
                    if ($wrd->name() <> '') {
                        $this->set_from($wrd->phrase());
                        $this->from()->set_name($wrd->name());
                    } else {
                        log_err('Failed to load first word of phrase ' . $this->dsp_id());
                        $result = false;
                    }
                } elseif ($this->from_id() < 0) {
                    $lnk = new triple($this->user());
                    $lnk->load_by_id($this->from()->obj_id());
                    if ($lnk->id() > 0) {
                        $this->set_from($lnk->phrase());
                        $this->from()->set_name($lnk->name());
                    } else {
                        log_err('Failed to load first phrase of phrase ' . $this->dsp_id());
                        $result = false;
                    }
                } else {
                    // if type is not (yet) set, create a dummy object to enable the selection
                    $phr = new phrase($this->user());
                    $this->set_from($phr);
                }
                log_debug('from ' . $this->from()->name());
            }
        }

        // test verb
        if (!$this->has_verb()) {
            log_err("The verb must be set before it can be loaded.", "triple->load_objects");
        }

        // load the "to" phrase
        if ($this->to() == null) {
            if ($this->to_id() == 0) {
                // set a dummy word
                $wrd_to = new word($this->user());
                $this->set_to($wrd_to->phrase());
            }
        } else {
            if ($this->to_id() <> 0 and !is_null($this->user()->id())) {
                if ($this->to_id() > 0) {
                    $wrd_to = new word($this->user());
                    $wrd_to->load_by_id($this->to_id());
                    if ($wrd_to->name() <> '') {
                        $this->set_to($wrd_to->phrase());
                        $this->to()->set_name($wrd_to->name());
                    } else {
                        log_err('Failed to load second word of phrase ' . $this->dsp_id());
                        $result = false;
                    }
                } elseif ($this->to_id() < 0) {
                    $lnk = new triple($this->user());
                    $lnk->load_by_id($this->to()->obj_id());
                    if ($lnk->id() > 0) {
                        $this->set_to($lnk->phrase());
                        $this->to()->set_name($lnk->name());
                    } else {
                        log_err('Failed to load second phrase of phrase ' . $this->dsp_id());
                        $result = false;
                    }
                } else {
                    // if type is not (yet) set, create a dummy object to enable the selection
                    $phr_to = new phrase($this->user());
                    $this->set_to($phr_to);
                }
                log_debug('to ' . $this->to()->name());
            }
        }
        return $result;
    }

    /**
     * @return string the name of the SQL statement name extension based on the filled fields
     */
    private function load_sql_name_ext(): string
    {
        if ($this->id() != 0) {
            return sql_db::FLD_ID;
        } elseif ($this->name != '') {
            return sql_db::FLD_NAME;
        } elseif ($this->has_objects()) {
            return 'link_ids';
        } else {
            log_err('Either the database ID (' . $this->id() . ') or the ' .
                self::class . ' link objects (' . $this->dsp_id() . ') and the user (' . $this->user()->id() . ') must be set to load a ' .
                self::class, self::class . '->load');
            return '';
        }
    }

    /**
     * add the select parameters to the query parameters
     *
     * @param sql $sc with the target db_type set
     * @param sql_par $qp the query parameters with the name already set
     * @return sql_par the query parameters with the select parameters added
     */
    private function load_sql_select_qp(sql $sc, sql_par $qp): sql_par
    {
        if ($this->id() != 0) {
            $sc->add_where($this->id_field(), $this->id());
        } elseif ($this->name != '') {
            $sc->add_where($this->name_field(), $this->name());
        } elseif ($this->has_objects()) {
            $sc->add_where(self::FLD_FROM, $this->from_id());
            $sc->add_where(self::FLD_TO, $this->to_id());
            $sc->add_where(verb::FLD_ID, $this->verb_id());
        } elseif ($this->name_generated() != '') {
            $sc->add_where(self::FLD_NAME_AUTO, $this->name_generated());
        } elseif ($this->name_given() != '') {
            $sc->add_where(self::FLD_NAME_GIVEN, $this->name_given());
        } else {
            log_err('Cannot load default triple because no unique field is set');
        }
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();
        return $qp;
    }

    /**
     * create an SQL statement to retrieve the user changes of the current triple
     *
     * @param sql $sc with the target db_type set
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation e.g. standard for values and results
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_user_changes(
        sql           $sc,
        sql_type_list $sc_par_lst = new sql_type_list([])
    ): sql_par
    {
        $sc->set_class($this::class, new sql_type_list([sql_type::USER]));
        $sc->set_fields(array_merge(
            self::FLD_NAMES_USR,
            self::FLD_NAMES_NUM_USR
        ));
        return parent::load_sql_user_changes($sc, $sc_par_lst);
    }

    /**
     * @return true if no link objects is missing
     */
    private function has_objects(): bool
    {
        $result = true;
        if ($this->from_id() == 0) {
            $result = false;
        }
        if ($this->verb_id() == 0) {
            $result = false;
        }
        if ($this->to_id() == 0) {
            $result = false;
        }
        return $result;
    }

    /**
     * recursive function to include the foaf words for this triple
     */
    function wrd_lst(): word_list
    {
        log_debug('triple->wrd_lst ' . $this->dsp_id());
        $wrd_lst = new word_list($this->user());

        // add the "from" side
        if ($this->from() != null) {
            if ($this->from_id() > 0) {
                $wrd_lst->add($this->from()->obj());
            } elseif ($this->from_id() < 0) {
                $sub_wrd_lst = $this->from()->wrd_lst();
                foreach ($sub_wrd_lst->lst() as $wrd) {
                    $wrd_lst->add($wrd);
                }
            } else {
                log_err('The from phrase ' . $this->from()->dsp_id() . ' should not have the id 0', 'triple->wrd_lst');
            }
        }

        // add the "to" side
        if ($this->to() != null) {
            if ($this->to_id() > 0) {
                $wrd_lst->add($this->to()->obj());
            } elseif ($this->to_id() < 0) {
                $sub_wrd_lst = $this->to()->wrd_lst();
                foreach ($sub_wrd_lst->lst() as $wrd) {
                    $wrd_lst->add($wrd);
                }
            } else {
                log_err('The to phrase ' . $this->to()->dsp_id() . ' should not have the id 0', 'triple->wrd_lst');
            }
        }

        log_debug($wrd_lst->name());
        return $wrd_lst;
    }


    /*
     * im- and export
     */

    /**
     * an array of the value vars including the private vars
     */
    function jsonSerialize(): array
    {
        $vars = parent::jsonSerialize();
        $vars = array_merge($vars, get_object_vars($this));
        if ($this->from()->obj() != null) {
            $vars[self::FLD_EX_FROM] = $this->from()->obj()->name_dsp();
        }
        if ($this->to()->obj() != null) {
            $vars[self::FLD_EX_TO] = $this->to()->obj()->name_dsp();
        }
        return $vars;
    }

    /**
     * get a phrase based on the name (and save it if needed and requested)
     *
     * @param string $name the name of the phrase
     * @param object|null $test_obj if not null the unit test object to get a dummy seq id
     * @return phrase the created phrase object
     */
    private function import_phrase(string $name, object $test_obj = null): phrase
    {
        $result = new phrase($this->user());
        if (!$test_obj) {
            $result->load_by_name($name);
            if ($result->id() == 0) {
                // if there is no word or triple with the name yet, automatically create a word
                $wrd = new word($this->user());
                $wrd->set_name($name);
                $wrd->save();
                if ($wrd->id() == 0) {
                    log_err('Cannot add from word "' . $name . '" when importing ' . $this->dsp_id(), 'triple->import_obj');
                } else {
                    $result = $wrd->phrase();
                }
            }
        } else {
            $result->set_name($name, word::class);
        }
        return $result;
    }

    /**
     * import a triple from a json object
     *
     * @param array $in_ex_json an array with the data of the json object
     * @param object|null $test_obj if not null the unit test object to get a dummy seq id
     * @return user_message the status of the import and if needed the error messages that should be shown to the user
     */
    function import_obj(array $in_ex_json, object $test_obj = null): user_message
    {
        global $phrase_types;

        log_debug();

        // set the object vars based on the json
        $result = $this->import_obj_fill($in_ex_json, $test_obj);

        // save the triple in the database
        if (!$test_obj) {
            if ($result->is_ok()) {
                // remove unneeded given names
                $this->set_names();
                $result->add($this->save());
            }
        }

        // add related parameters to the word object
        if ($result->is_ok()) {
            log_debug('saved ' . $this->dsp_id());

            if (!$test_obj) {
                if ($this->id() == 0) {
                    $result->add_message('Triple ' . $this->dsp_id() . ' cannot be saved');
                } else {
                    foreach ($in_ex_json as $key => $value) {
                        if ($result->is_ok()) {
                            if ($key == self::FLD_REFS) {
                                foreach ($value as $ref_data) {
                                    $ref_obj = new ref($this->user());
                                    $ref_obj->set_phrase($this->phrase());
                                    $result->add($ref_obj->import_obj($ref_data, $test_obj));
                                    $this->ref_lst[] = $ref_obj;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * set the vars of this triple object based on the given json without writing to the database
     *
     * @param array $in_ex_json an array with the data of the json object
     * @param object|null $test_obj if not null the unit test object to get a dummy seq id
     * @return user_message
     */
    function import_obj_fill(array $in_ex_json, object $test_obj = null): user_message
    {
        global $phrase_types;

        $result = parent::import_obj($in_ex_json, $test_obj);

        foreach ($in_ex_json as $key => $value) {
            if ($key == sandbox_exp::FLD_TYPE) {
                $this->type_id = $phrase_types->id($value);
            }
            if ($key == self::FLD_EX_FROM) {
                if ($value == "") {
                    $lib = new library();
                    $result->add_message('from name should not be empty at ' . $lib->dsp_array($in_ex_json));
                } else {
                    if (is_string($value)) {
                        $this->set_from($this->import_phrase($value, $test_obj));
                    } else {
                        log_err($value . ' is expected to be a string');
                    }
                }
            }
            if ($key == self::FLD_EX_TO) {
                if ($value == "") {
                    $lib = new library();
                    $result->add_message('to name should not be empty at ' . $lib->dsp_array($in_ex_json));
                } else {
                    $this->set_to($this->import_phrase($value, $test_obj));
                }
            }
            if ($key == self::FLD_EX_VERB) {
                $vrb = new verb;
                $vrb->set_user($this->user());
                if (!$test_obj) {
                    if ($result->is_ok()) {
                        $vrb->load_by_name($value);
                        if ($vrb->id() <= 0) {
                            $result->add_message('verb "' . $value . '" not found');
                            if ($this->name <> '') {
                                $result->add_message('for triple "' . $this->name . '"');
                            }
                        }
                    }
                } else {
                    $vrb->set_name($value);
                }
                $this->set_verb($vrb);
            }
            if ($key == sandbox_exp::FLD_VIEW) {
                $trp_view = new view($this->user());
                if (!$test_obj) {
                    $trp_view->load_by_name($value);
                    if ($trp_view->id() == 0) {
                        $result->add_message('Cannot find view "' . $value . '" when importing ' . $this->dsp_id());
                    }
                } else {
                    $trp_view->set_name($value);
                }
                $this->view = $trp_view;
            }
        }

        return $result;
    }

    /**
     * create a triple object for the export
     * @return triple_exp a reduced triple object that can be used to create a JSON message
     */
    function export_obj(bool $do_load = true): sandbox_exp
    {
        global $phrase_types;
        global $share_types;
        global $protection_types;

        log_debug();
        $result = new triple_exp();

        if ($this->name <> '') {
            $result->name = $this->name;
        }
        if ($this->description <> '') {
            $result->description = $this->description;
        }
        if ($this->type_id > 0) {
            if ($this->type_id <> $phrase_types->default_id()) {
                $result->type = $this->type_code_id();
            }
        }
        if ($this->from()->name() <> '') {
            $result->from = $this->from()->name();
        }
        if ($this->verb_name() <> '') {
            $result->verb = $this->verb_name();
        }
        if ($this->to()->name() <> '') {
            $result->to = $this->to()->name();
        }

        // add the share type
        if ($this->share_id > 0 and $this->share_id <> $share_types->id(share_type_shared::PUBLIC)) {
            $result->share = $this->share_type_code_id();
        }

        // add the protection type
        if ($this->protection_id > 0 and $this->protection_id <> $protection_types->id(protect_type_shared::NO_PROTECT)) {
            $result->protection = $this->protection_type_code_id();
        }

        if (isset($this->view)) {
            $result->view = $this->view->name();
        }
        if (isset($this->ref_lst)) {
            foreach ($this->ref_lst as $ref) {
                $result->refs[] = $ref->export_obj();
            }
        }

        log_debug(json_encode($result));
        return $result;
    }


    /*
     * information
     */

    /**
     * check if the word in the database needs to be updated
     * e.g. for import  if this word has only the name set, the protection should not be updated in the database
     *
     * @param triple $db_trp the word as saved in the database
     * @return bool true if this word has infos that should be saved in the database
     */
    function needs_db_update(triple $db_trp): bool
    {
        $result = parent::needs_db_update_named($db_trp);
        if ($this->verb_id() > 0) {
            if ($this->verb_id() != $db_trp->verb_id()) {
                $result = true;
            }
        }
        if ($this->name_given != null) {
            if ($this->name_given != $db_trp->name_given) {
                $result = true;
            }
        }
        if ($this->values != null) {
            if ($this->values != $db_trp->values) {
                $result = true;
            }
        }
        return $result;
    }


    /*
     * internal
     */

    /**
     * @return string the generated name based on the linked phrases
     */
    function generate_name(): string
    {
        global $verbs;
        if ($this->verb_id() == $verbs->id(verb::IS) and $this->from()->name() != '' and $this->to()->name() != '') {
            // use the user defined description
            return $this->from()->name() . ' (' . $this->to()->name() . ')';
        } elseif ($this->from()->name() != '' and $this->verb_name() != '' and $this->to()->name() != '') {
            // or use the standard generic description
            return $this->from()->name() . ' ' . $this->verb_name() . ' ' . $this->to()->name();
        } elseif ($this->from()->name() != '' and $this->to()->name() != '') {
            // or use the short generic description
            return $this->from()->name() . ' ' . $this->to()->name();
        } else {
            // or use the name as fallback
            if ($this->name_given() == null) {
                return '';
            } else {
                return $this->name_given();
            }
        }
    }


    /*
     * save
     */

    /**
     * true if no one has used this triple
     */
    function not_used(): bool
    {
        log_debug('triple->not_used (' . $this->id() . ')');

        // TODO review: maybe replace by a database foreign key check
        return $this->not_changed();
    }

    /**
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     *                 to check if the triple has been changed
     */
    function not_changed_sql(sql $sc): sql_par
    {
        $sc->set_class(triple::class);
        return $sc->load_sql_not_changed($this->id(), $this->owner_id);
    }

    /**
     * @returns bool true if no other user has modified the triple
     */
    function not_changed(): bool
    {
        log_debug('triple->not_changed (' . $this->id() . ') by someone else than the owner (' . $this->owner_id . ')');

        global $db_con;
        $result = true;

        if ($this->id() == 0) {
            log_err('The id must be set to check if the triple has been changed');
        } else {
            $qp = $this->not_changed_sql($db_con->sql_creator());
            $db_row = $db_con->get1($qp);
            if ($db_row[user::FLD_ID] > 0) {
                $result = false;
            }
        }
        log_debug('triple->not_changed for ' . $this->id() . ' is ' . zu_dsp_bool($result));
        return $result;
    }

    /**
     * set the log entry parameter for a new value
     * e.g. that the user can see "added ABB is a Company"
     */
    function log_link_add(): change_link
    {
        log_debug('triple->log_link_add for ' . $this->dsp_id() . ' by user "' . $this->user()->name . '"');
        $log = new change_link($this->user());
        $log->set_action(change_action::ADD);
        $log->set_table(change_table_list::TRIPLE);
        $log->new_from = $this->from();
        $log->new_link = $this->verb();
        $log->new_to = $this->to();
        $log->row_id = 0;
        $log->add();

        return $log;
    }

    /**
     * set the main log entry parameters for updating the triple itself
     */
    function log_upd(): change_link
    {
        $log = new change_link($this->user());
        $log->set_action(change_action::UPDATE);
        if ($this->can_change()) {
            $log->set_table(change_table_list::TRIPLE);
        } else {
            $log->set_table(change_table_list::TRIPLE_USR);
        }

        return $log;
    }

    /**
     * set the log entry parameter to delete a triple
     * e.g. that the user can see "ABB is a Company not anymore"
     */
    function log_del_link(): change_link
    {
        log_debug('triple->log_link_del for ' . $this->dsp_id() . ' by user "' . $this->user()->name . '"');
        $log = new change_link($this->user());
        $log->set_action(change_action::DELETE);
        $log->set_table(change_table_list::TRIPLE);
        $log->old_from = $this->from();
        $log->old_link = $this->verb();
        $log->old_to = $this->to();
        $log->row_id = $this->id();
        $log->add();

        return $log;
    }

    /**
     * set the main log entry parameters for updating one display triple field
     */
    function log_upd_field(): change
    {
        $log = new change($this->user());
        $log->set_action(change_action::UPDATE);
        if ($this->can_change()) {
            $log->set_table(change_table_list::TRIPLE);
        } else {
            $log->set_table(change_table_list::TRIPLE_USR);
        }

        return $log;
    }


    /**
     * check if a term with the unique name already exists
     * returns null if no similar object is found
     * or returns the term with the same unique key that is not the actual object
     * similar to sandbox named get_similar but
     * @return term|null a filled object that has the same name
     *                or a sandbox object with id() = 0 if nothing similar has been found
     */
    function get_similar_named(): ?term
    {
        $trm = new term($this->user());
        if ($this->name() != '') {
            $trm->load_by_name($this->name());
            if ($trm->id_obj() == 0) {
                $similar_trp = new triple($this->user());
                $similar_trp->load_by_name_generated($this->name());
                if ($similar_trp->id() != 0) {
                    $trm = $similar_trp->term();
                }
            }
        }
        if ($trm->id_obj() == 0 or $trm->id_obj() == $this->id()) {
            $trm = null;
        }

        return $trm;
    }

    /**
     * check if the given name can be used for this triple
     * @return user_message the message that should be shown to the user in case something went wrong
     */
    private function is_name_used_msg(): user_message
    {
        $usr_msg = new user_message();
        // check if the name is used
        $similar = $this->get_similar_named();
        // if the similar object is not the same as $this object, suggest renaming $this object
        if ($similar != null) {
            $usr_msg->add_message($similar->id_used_msg($this));
        }
        return $usr_msg;
    }

    /**
     * set the update parameters for the triple name
     * @return user_message the message that should be shown to the user in case something went wrong
     */
    function save_field_name(sql_db $db_con, sandbox $db_rec, sandbox $std_rec): user_message
    {
        $usr_msg = new user_message();

        // the name field is a generic created field, so update it before saving
        // the generic name of $this is saved to the database for faster uniqueness check (TODO to be checked if this is really faster)
        $this->set_names();

        if ($db_rec->name() <> $this->name() and !$this->is_excluded()) {
            $usr_msg->add($this->is_name_used_msg());
            if ($usr_msg->is_ok()) {
                $log = $this->log_upd_field();
                // TODO review
                if ($db_rec->name() != '') {
                    $log->old_value = $db_rec->name();
                } else {
                    $log->old_value = null;
                }
                // ignore excluded to not overwrite an existing name
                $log->new_value = $this->name(true);
                $log->std_value = $std_rec->name();
                $log->row_id = $this->id();
                $log->set_field(self::FLD_NAME);
                $usr_msg->add($this->save_field_user($db_con, $log));
            }
        }
        return $usr_msg;
    }

    /**
     * set the update parameters for the triple given name
     * @return user_message the message that should be shown to the user in case something went wrong
     */
    private
    function save_field_name_given(sql_db $db_con, triple $db_rec, triple $std_rec): user_message
    {
        $usr_msg = new user_message();
        if ($db_rec->name_given() <> $this->name_given()) {
            if ($this->name_given() != null) {
                $result = $this->is_name_used_msg($this->name_given());
            }
            if ($usr_msg->is_ok()) {
                $log = $this->log_upd_field();
                $log->old_value = $db_rec->name_given();
                $log->new_value = $this->name_given();
                $log->std_value = $std_rec->name_given();
                $log->row_id = $this->id();
                $log->set_field(self::FLD_NAME_GIVEN);
                $usr_msg->add($this->save_field_user($db_con, $log));
            }
        }
        return $usr_msg;
    }

    /**
     * set the update parameters for the triple generated name
     * @return user_message the message that should be shown to the user in case something went wrong
     */
    private
    function save_field_name_generated(sql_db $db_con, triple $db_rec, triple $std_rec): user_message
    {
        $usr_msg = new user_message();
        if ($db_rec->name_generated <> $this->name_generated()) {
            $usr_msg->add($this->is_name_used_msg($this->name_generated()));
            if ($usr_msg->is_ok()) {
                $log = $this->log_upd_field();
                if ($db_rec->name_generated == '') {
                    $log->old_value = null;
                } else {
                    $log->old_value = $db_rec->name_generated;
                }
                $log->new_value = $this->name_generated();
                $log->std_value = $std_rec->name_generated;
                $log->row_id = $this->id();
                $log->set_field(self::FLD_NAME_AUTO);
                $usr_msg->add($this->save_field_user($db_con, $log));
            }
        }
        return $usr_msg;
    }

    /**
     * set the update parameters for the triple description
     * @return user_message the message that should be shown to the user in case something went wrong
     */
    function save_field_triple_description(sql_db $db_con, triple $db_rec, triple $std_rec): user_message
    {
        $usr_msg = new user_message();
        if ($db_rec->description <> $this->description) {
            $log = $this->log_upd_field();
            $log->old_value = $db_rec->description;
            $log->new_value = $this->description;
            $log->std_value = $std_rec->description;
            $log->row_id = $this->id();
            $log->set_field(sandbox_named::FLD_DESCRIPTION);
            $usr_msg->add($this->save_field_user($db_con, $log));
        }
        return $usr_msg;
    }

    /**
     * save all updated triple fields excluding id fields (from, verb and to), because already done when adding a triple
     * @return user_message the message that should be shown to the user in case something went wrong
     */
    function save_triple_fields(sql_db $db_con, triple $db_rec, triple $std_rec): user_message
    {
        $usr_msg = $this->save_field_triple_description($db_con, $db_rec, $std_rec);
        $usr_msg->add($this->save_field_excluded($db_con, $db_rec, $std_rec));
        $usr_msg->add($this->save_field_type($db_con, $db_rec, $std_rec));
        log_debug('triple->save_fields all fields for ' . $this->dsp_id() . ' has been saved');
        return $usr_msg;
    }

    /**
     * save all updated triple fields excluding id fields (from, verb and to), because already done when adding a triple
     * @return user_message the message that should be shown to the user in case something went wrong
     */
    function save_name_fields(sql_db $db_con, triple $db_rec, triple $std_rec): user_message
    {
        $usr_msg = $this->save_field_name($db_con, $db_rec, $std_rec);
        if ($usr_msg->is_ok()) {
            $usr_msg->add($this->save_field_name_given($db_con, $db_rec, $std_rec));
        }
        if ($usr_msg->is_ok()) {
            $usr_msg->add($this->save_field_name_generated($db_con, $db_rec, $std_rec));
        }
        return $usr_msg;
    }

    /**
     * save updated the triple id fields (from, verb and to)
     * should only be called if the user is the owner and nobody has used the triple
     */
    function save_id_fields(sql_db $db_con, sandbox|triple $db_rec, sandbox|triple $std_rec): string
    {
        $result = '';
        if ($db_rec->from_id() <> $this->from_id()
            or $db_rec->verb_id() <> $this->verb_id()
            or $db_rec->to_id() <> $this->to_id()) {
            log_debug('triple->save_id_fields to "' . $this->to()->name() . '" (' . $this->to_id() . ') from "' . $db_rec->to()->name() . '" (' . $db_rec->to_id() . ') standard ' . $std_rec->to()->name() . '" (' . $std_rec->to_id() . ')');
            $log = $this->log_upd();
            $log->old_from = $db_rec->from();
            $log->new_from = $this->from();
            $log->std_from = $std_rec->from();
            $log->old_link = $db_rec->verb();
            $log->new_link = $this->verb();
            $log->std_link = $std_rec->verb();
            $log->old_to = $db_rec->to();
            $log->new_to = $this->to();
            $log->std_to = $std_rec->to();
            $log->row_id = $this->id();
            //$log->set_field(self::FLD_FROM);
            if ($log->add()) {
                $db_con->set_class(triple::class);
                if (!$db_con->update_old($this->id(),
                    array(triple::FLD_FROM, verb::FLD_ID, triple::FLD_TO),
                    array($this->from_id(), $this->verb_id(), $this->to_id()))) {
                    $result = 'Update of work link name failed';
                }
            }
        }
        log_debug('triple->save_id_fields for ' . $this->dsp_id() . ' has been done');
        return $result;
    }

    /**
     * check if the id parameters are supposed to be changed
     * TODO try to move to sandbox or sandbox link object
     *
     * @param sql_db $db_con the active database connection
     * @param triple|sandbox $db_rec the database record before the saving
     * @param triple|sandbox $std_rec the database record defined as standard because it is used by most users
     * @param bool $use_func if true a predefined function is used that also creates the log entries
     * @returns user_message a messages for the user what should be changed if something failed
     */
    function save_id_if_updated(
        sql_db         $db_con,
        triple|sandbox $db_rec,
        triple|sandbox $std_rec,
        bool           $use_func
    ): user_message
    {
        $usr_msg = new user_message();

        if ($db_rec->from_id() <> $this->from_id()
            or $db_rec->verb_id() <> $this->verb_id()
            or $db_rec->to_id() <> $this->to_id()) {
            // check if target link already exists
            log_debug('triple->save_id_if_updated check if target link already exists ' . $this->dsp_id() . ' (has been "' . $db_rec->dsp_id() . '")');
            $db_chk = clone $this;
            $db_chk->set_id(0); // to force the load by the id fields
            $db_chk->load_standard();
            if ($db_chk->id() > 0) {
                // ... if yes request to delete or exclude the record with the id parameters before the change
                $to_del = clone $db_rec;
                $msg = $to_del->del();
                $usr_msg->add($msg);
                if (!$msg->is_ok()) {
                    $usr_msg->add_message('Failed to delete the unused work link');
                }
                if ($usr_msg->is_ok()) {
                    // ... and use it for the update
                    $this->set_id($db_chk->id());
                    $this->owner_id = $db_chk->owner_id;
                    // force including again
                    $this->include();
                    $db_rec->exclude();
                    if ($this->save_field_excluded($db_con, $db_rec, $std_rec)) {
                        log_debug('triple->save_id_if_updated found a triple with target ids "' . $db_chk->dsp_id() . '", so del "' . $db_rec->dsp_id() . '" and add ' . $this->dsp_id());
                    }
                }
            } else {
                if ($this->can_change() and $this->not_used()) {
                    // in this case change is allowed and done
                    log_debug('triple->save_id_if_updated change the existing triple ' . $this->dsp_id() . ' (db "' . $db_rec->dsp_id() . '", standard "' . $std_rec->dsp_id() . '")');
                    $this->load_objects();
                    $usr_msg->add_message($this->save_id_fields($db_con, $db_rec, $std_rec));
                } else {
                    // if the target link has not yet been created
                    // ... request to delete the old
                    $to_del = clone $db_rec;
                    $msg = $to_del->del();
                    $usr_msg->add($msg);
                    if (!$msg->is_ok()) {
                        $usr_msg->add_message('Failed to delete the unused work link');
                    }
                    // ... and create a deletion request for all users ???

                    // ... and create a new triple
                    $this->set_id(0);
                    $this->owner_id = $this->user()->id();
                    $usr_msg->add($this->add());
                    log_debug('triple->save_id_if_updated recreate the triple del "' . $db_rec->dsp_id() . '" add ' . $this->dsp_id() . ' (standard "' . $std_rec->dsp_id() . '")');
                }
            }
        }

        log_debug('triple->save_id_if_updated for ' . $this->dsp_id() . ' has been done');
        return $usr_msg;
    }

    /**
     * add a new triple to the database
     * @param bool $use_func if true a predefined function is used that also creates the log entries
     * @return user_message with status ok
     *                      or if something went wrong
     *                      the message that should be shown to the user
     *                      including suggested solutions
     */
    function add(bool $use_func = false): user_message
    {
        log_debug('triple->add new triple for "' . $this->from()->name() . '" ' . $this->verb_name() . ' "' . $this->to()->name() . '"');

        global $db_con;
        $usr_msg = new user_message();

        if ($use_func) {
            // TODO review: do not set the generated name if it matches the name
            $this->set_names();
            $sc = $db_con->sql_creator();
            $qp = $this->sql_insert($sc, new sql_type_list([sql_type::LOG]));
            $ins_msg = $db_con->insert($qp, 'add and log ' . $this->dsp_id());
            if ($ins_msg->is_ok()) {
                $this->set_id($ins_msg->get_row_id());
            }
            $usr_msg->add($ins_msg);
        } else {

            // log the insert attempt first
            $log = $this->log_link_add();
            if ($log->id() > 0) {
                // insert the new triple
                if ($this->sql_write_prepared()) {
                    $sc = $db_con->sql_creator();
                    $qp = $this->sql_insert($sc);
                    $ins_msg = $db_con->insert($qp, 'add ' . $this->dsp_id());
                    if ($ins_msg->is_ok()) {
                        $this->set_id($ins_msg->get_row_id());
                    }
                } else {
                    $db_con->set_class(triple::class);
                    $this->set_id($db_con->insert_old(array(triple::FLD_FROM, verb::FLD_ID, triple::FLD_TO, user::FLD_ID),
                        array($this->from_id(), $this->verb_id(), $this->to_id(), $this->user()->id())));
                }
                // TODO make sure on all add functions that the database object is always set
                //array($this->from_id(), $this->verb_id() , $this->to_id(), $this->user()->id()));
                if ($this->id() > 0) {
                    // update the id in the log
                    if (!$log->add_ref($this->id())) {
                        $usr_msg->add_message('Updating the reference in the log failed');
                        // TODO do rollback or retry?
                    } else {

                        // create an empty db_rec element to force saving of all set fields
                        $db_rec = new triple($this->user());
                        $db_rec->set_from($this->from());
                        $db_rec->set_verb($this->verb());
                        $db_rec->set_to($this->to());
                        $std_rec = clone $db_rec;
                        // save the triple fields
                        $usr_msg->add($this->save_name_fields($db_con, $db_rec, $std_rec));
                        $usr_msg->add($this->save_triple_fields($db_con, $db_rec, $std_rec));
                    }

                } else {
                    $usr_msg->add_message("Adding triple " . $this->name . " failed");
                }
            }
        }

        return $usr_msg;
    }

    /**
     * add or update a triple in the database or create a user triple
     * overwrite the sandbox save becuse for triple the reverse order should be checked
     *
     * @param bool|null $use_func if true a predefined function is used that also creates the log entries
     * @return user_message the message that should be shown to the user in case something went wrong
     */
    function save(?bool $use_func = null): user_message
    {
        log_debug($this->dsp_id());

        global $db_con;

        // init
        $mtr = new message_translator();
        $msg_reload = $mtr->txt(msg_enum::RELOAD);
        $msg_fail = $mtr->txt(msg_enum::FAILED);
        $lib = new library();
        $class_name = $lib->class_to_name($this::class);

        // decide which db write method should be used
        if ($use_func === null) {
            $use_func = $this->sql_default_script_usage();
        }

        // check the preserved names
        $usr_msg = $this->check_save();

        if ($usr_msg->is_ok()) {

            // load the objects if needed
            $this->load_objects();

            // build the database object because the is anyway needed
            $db_con->set_usr($this->user()->id());
            $db_con->set_class(triple::class);

            // check if a triple with the same link exists and if yes, update this triple
            if ($this->id() == 0) {
                $db_chk = new triple($this->user());
                $db_chk->load_by_link_id($this->from()->id(), $this->verb()->id(), $this->to()->id());
                if ($db_chk->id() != 0) {
                    $this->set_id($db_chk->id());
                }
            }

            // check if the opposite triple already exists and if yes, ask for confirmation
            if ($this->id() == 0) {
                log_debug('check if a new triple for "' . $this->from()->name() . '" and "' . $this->to()->name() . '" needs to be created');
                // check if the reverse triple is already in the database
                $db_chk_rev = clone $this;
                $db_chk_rev->set_from($this->to());
                $db_chk_rev->from()->set_id($this->to_id());
                $db_chk_rev->set_to($this->from());
                $db_chk_rev->to()->set_id($this->from_id());
                // remove the name in the object to prevent loading by name
                $db_chk_rev->name = '';
                $db_chk_rev->load_standard();
                if ($db_chk_rev->id() > 0) {
                    $this->set_id($db_chk_rev->id());
                    $usr_msg->add_message('The reverse of "' . $this->from()->name() . ' ' . $this->verb_name() . ' ' . $this->to()->name() . '" already exists. Do you really want to create both sides?');
                }
            }

            // check if the triple already exists as standard and if yes, update it if needed
            if ($this->id() == 0 and $usr_msg->is_ok()) {
                log_debug('check if a new triple for "' . $this->from()->name() . '" and "' . $this->to()->name() . '" needs to be created');
                // check if the same triple is already in the database
                $db_chk = clone $this;
                $db_chk->load_standard();
                if ($db_chk->id() > 0) {
                    $this->set_id($db_chk->id());
                }
            }
        }

        // try to save the link only if no question has been raised utils now
        if ($usr_msg->is_ok()) {
            // check if a new value is supposed to be added
            if ($this->id() == 0) {
                $usr_msg->add($this->is_name_used_msg($this->name()));
                if ($usr_msg->is_ok()) {
                    $usr_msg->add($this->add($use_func));
                    if (!$usr_msg->is_ok()) {
                        log_info($usr_msg->get_last_message());
                    }
                }
            } else {
                log_debug('update ' . $this->dsp_id());
                // read the database values to be able to check if something has been changed;
                // done first, because it needs to be done for user and general phrases
                $db_rec = new triple($this->user());
                if (!$db_rec->load_by_id($this->id())) {
                    $usr_msg->add_message($msg_reload . ' ' . $class_name . ' ' . $msg_fail);
                    if (!$usr_msg->is_ok()) {
                        log_err($usr_msg->get_last_message());
                    }
                }
                log_debug('database triple "' . $db_rec->name() . '" (' . $db_rec->id() . ') loaded');
                $std_rec = new triple($this->user()); // the user must also be set to allow to take the ownership
                $std_rec->set_id($this->id());
                if (!$std_rec->load_standard()) {
                    $usr_msg->add_message($msg_reload . ' ' . $class_name . ' ' . $msg_fail);
                    if (!$usr_msg->is_ok()) {
                        log_err($usr_msg->get_last_message());
                    }
                }
                log_debug('standard triple settings for "' . $std_rec->name() . '" (' . $std_rec->id() . ') loaded');

                // for a correct user triple detection (function can_change) set the owner even if the triple has not been loaded before the save
                if ($this->owner_id <= 0) {
                    $this->owner_id = $std_rec->owner_id;
                }

                // check if the id parameters are supposed to be changed
                if ($usr_msg->is_ok()) {
                    $usr_msg->add($this->save_id_if_updated($db_con, $db_rec, $std_rec, $use_func));
                    if (!$usr_msg->is_ok()) {
                        log_err($usr_msg->get_last_message());
                    }
                }

                if ($usr_msg->is_ok()) {
                    $usr_msg->add($this->save_name_fields($db_con, $db_rec, $std_rec));
                }

                // if a problem has appeared up to here, don't try to save the values
                // the problem is shown to the user by the calling interactive script
                if ($usr_msg->is_ok()) {
                    $usr_msg->add($this->save_triple_fields($db_con, $db_rec, $std_rec));
                    if (!$usr_msg->is_ok()) {
                        log_err($usr_msg->get_last_message());
                    }
                }
            }
        }

        return $usr_msg;
    }

    /*
     * save helper
     */

    /**
     * @return array with the reserved triple names
     */
    protected function reserved_names(): array
    {
        return triple_api::RESERVED_NAMES;
    }

    /**
     * @return array with the fixed triple names for db read testing
     */
    protected function fixed_names(): array
    {
        return triple_api::FIXED_NAMES;
    }

    /**
     * delete the phrase groups which where this triple is used
     *
     * @return user_message the message for the user why the action has failed and a suggested solution
     */
    function del_links(): user_message
    {
        $usr_msg = new user_message();

        // collect all phrase groups where this triple is used
        // TODO activate
        //$grp_lst = new group_list($this->user());
        //$grp_lst->load_by_phr($this->phrase());

        // collect all values related to this triple
        $val_lst = new value_list($this->user());
        $val_lst->load_by_phr($this->phrase());

        // if there are still values, ask if they really should be deleted
        if ($val_lst->has_values()) {
            $usr_msg->add($val_lst->del());
        }

        // if the user confirms the deletion, the removal process is started with a retry of the triple deletion at the end
        // TODO activate
        //$usr_msg->add($grp_lst->del());

        return $usr_msg;
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
            parent::db_fields_all($sc_par_lst),
            [
                phrase::FLD_TYPE,
                verb::FLD_ID,
                self::FLD_NAME_GIVEN,
                self::FLD_NAME_AUTO,
                self::FLD_VALUES,
                self::FLD_VIEW
            ],
            parent::db_fields_all_sandbox()
        );
    }

    /**
     * get a list of database field names, values and types that have been updated
     *
     * @param sandbox|triple $sbx the compare value to detect the changed fields
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par_field_list list 3 entry arrays with the database field name, the value and the sql type that have been updated
     */
    function db_fields_changed(
        sandbox|triple $sbx,
        sql_type_list  $sc_par_lst = new sql_type_list([])
    ): sql_par_field_list
    {
        global $change_field_list;

        $sc = new sql();
        $do_log = $sc_par_lst->incl_log();
        $is_insert = $sc_par_lst->is_insert();
        $usr_tbl = $sc_par_lst->is_usr_tbl();
        $table_id = $sc->table_id($this::class);

        // should be corresponding with the list of triple object vars
        $lst = parent::db_fields_changed($sbx, $sc_par_lst);

        // for triple the type is the phrase type
        // the type is object specific that why it is not part of snadbox_link_types
        if ($sbx->type_id() <> $this->type_id()) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . phrase::FLD_TYPE,
                    $change_field_list->id($table_id . phrase::FLD_TYPE),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            global $phrase_types;
            $lst->add_type_field(
                phrase::FLD_TYPE,
                phrase::FLD_TYPE_NAME,
                $this->type_id(),
                $sbx->type_id(),
                $phrase_types
            );
        }

        // the link type cannot be changed by the user, because this would be another link
        if (!$usr_tbl) {
            if ($sbx->verb_id() <> $this->verb_id()) {
                if ($do_log) {
                    $lst->add_field(
                        sql::FLD_LOG_FIELD_PREFIX . verb::FLD_ID,
                        $change_field_list->id($table_id . verb::FLD_ID),
                        change::FLD_FIELD_ID_SQL_TYP
                    );
                }
                global $verbs;
                $lst->add_type_field(
                    verb::FLD_ID,
                    verb::FLD_NAME,
                    $this->verb_id(),
                    $sbx->verb_id(),
                    $verbs
                );
            }
        } else {
            // add the from and to fields even if the objects are the same in case of an insert exclude to identify the rows
            if ($is_insert) {
                // TODO check how to handle if the standard
                // $sbx can in this case be e.g. the standard object and $this is the object updated by the user
                if ($this->is_excluded() and !$sbx->is_excluded()) {
                    // the verb field is added for triple exclude insert statements
                    if ($do_log) {
                        $lst->add_field(
                            sql::FLD_LOG_FIELD_PREFIX . verb::FLD_ID,
                            $change_field_list->id($table_id . verb::FLD_ID),
                            change::FLD_FIELD_ID_SQL_TYP
                        );
                    }
                    global $verbs;
                    $lst->add_type_field(
                        verb::FLD_ID,
                        verb::FLD_NAME,
                        null,
                        $sbx->verb_id(),
                        $verbs
                    );
                    // TODO check if the excluded field is not already added by the sandbox function
                    if ($do_log) {
                        $lst->add_field(
                            sql::FLD_LOG_FIELD_PREFIX . sandbox::FLD_EXCLUDED,
                            $change_field_list->id($table_id . sandbox::FLD_EXCLUDED),
                            change::FLD_FIELD_ID_SQL_TYP
                        );
                    }
                    $lst->add_field(
                        sandbox::FLD_EXCLUDED,
                        1,
                        sandbox::FLD_EXCLUDED_SQL_TYP
                    );
                } elseif (!$this->is_excluded() and $sbx->is_excluded()) {
                    if ($do_log) {
                        $lst->add_field(
                            sql::FLD_LOG_FIELD_PREFIX . verb::FLD_ID,
                            $change_field_list->id($table_id . verb::FLD_ID),
                            change::FLD_FIELD_ID_SQL_TYP
                        );
                    }
                    global $verbs;
                    $lst->add_type_field(
                        verb::FLD_ID,
                        verb::FLD_NAME,
                        $this->verb_id(),
                        null,
                        $verbs
                    );
                }
            }
        }
        // TODO check if the excluded field is not already added by the sandbox function
        if ($sbx->is_excluded() <> $this->is_excluded()) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . sandbox::FLD_EXCLUDED,
                    $change_field_list->id($table_id . sandbox::FLD_EXCLUDED),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            if ($this->is_excluded()) {
                $new_excl = 1;
            } else {
                $new_excl = 0;
            }
            $lst->add_field(
                sandbox::FLD_EXCLUDED,
                $new_excl,
                sandbox::FLD_EXCLUDED_SQL_TYP
            );
        }
        if ($sbx->name_given() <> $this->name_given()) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . self::FLD_NAME_GIVEN,
                    $change_field_list->id($table_id . self::FLD_NAME_GIVEN),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            if ($sbx->name_given() == '') {
                $old_name = null;
            } else {
                $old_name = $sbx->name_given();
            }
            $lst->add_field(
                self::FLD_NAME_GIVEN,
                $this->name_given(),
                self::FLD_NAME_GIVEN_SQL_TYP,
                $old_name
            );
        }
        if ($sbx->name_generated() <> $this->name_generated()) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . self::FLD_NAME_AUTO,
                    $change_field_list->id($table_id . self::FLD_NAME_AUTO),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            if ($sbx->name_generated() == '') {
                $old_name = null;
            } else {
                $old_name = $sbx->name_generated();
            }
            $lst->add_field(
                self::FLD_NAME_AUTO,
                $this->name_generated(),
                self::FLD_NAME_AUTO_SQL_TYP,
                $old_name
            );
        }
        // TODO rename to usage
        if ($sbx->values <> $this->values) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . self::FLD_VALUES,
                    $change_field_list->id($table_id . self::FLD_VALUES),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                self::FLD_VALUES,
                $this->values,
                self::FLD_VALUES_SQL_TYP,
                $sbx->values
            );
        }
        if ($sbx->view_id() <> $this->view_id()) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . self::FLD_VIEW,
                    $change_field_list->id($table_id . self::FLD_VIEW),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_link_field(
                self::FLD_VIEW,
                view::FLD_NAME,
                $this->view,
                $sbx->view
            );
        }
        // TODO add ref list
        return $lst->merge($this->db_changed_sandbox_list($sbx, $sc_par_lst));
    }



    /*
     * debug
     */

    /**
     * @return string with the unique id fields
     * TODO check if $this->load_objects(); needs to be called from the calling function upfront
     */
    function dsp_id(): string
    {
        $result = '';

        if ($this->from()->name() <> '' and $this->verb_name() <> '' and $this->to()->name() <> '') {
            $result .= '"' . $this->from()->name() . '" "'; // e.g. Australia
            $result .= $this->verb_name() . '" "'; // e.g. is a
            $result .= $this->to()->name() . '"';       // e.g. Country
        }
        $result .= ' (' . $this->from_id() . ',' . $this->verb_id() . ',' . $this->to_id();
        if ($this->id() > 0) {
            $result .= ' -> triple_id ' . $this->id() . ')';
        }
        $result .= $this->dsp_id_user();
        return $result;
    }

    /**
     * either the user edited description
     * or the generic name e.g. Australia is a Country
     * or for the verb is 'is' the category in brackets e.g. Zurich (Canton) or Zurich (City)
     */
    function name(bool $ignore_excluded = false): string
    {
        $result = '';

        if (!$this->is_excluded() or $ignore_excluded) {
            if ($this->name <> '') {
                // use the object
                $result = $this->name;
            } elseif ($this->name_given() <> '') {
                // use the user defined description
                $result = $this->name_given();
            } else {
                // or use the standard generic description
                $result = $this->name_generated();
            }
        }

        return $result;
    }


    /*
     * display
     */

    /**
     * display one link to the user by returning the HTML code for the link to the calling function
     * TODO include the user sandbox in the selection
     */
    private
    function display(): string
    {
        log_debug("triple->dsp " . $this->id() . ".");

        $result = ''; // reset the html code var

        // get the link from the database
        $this->load_objects();

        // prepare to show the triple
        $result .= $this->from()->name() . ' '; // e.g. Australia
        $result .= $this->verb_name() . ' '; // e.g. is a
        $result .= $this->to()->name();       // e.g. Country

        return $result;
    }

    /**
     * similar to dsp, but display the reverse expression
     */
    private
    function dsp_r(): string
    {
        log_debug("triple->dsp_r " . $this->id() . ".");

        $result = ''; // reset the html code var

        // get the link from the database
        $this->load_objects();

        // prepare to show the triple
        $result .= $this->to()->name() . ' ';   // e.g. Countries
        $result .= $this->verb_name() . ' '; // e.g. are
        $result .= $this->from()->name();     // e.g. Australia (and others)

        return $result;
    }

    /**
     * display a form to create a triple
     */
    function dsp_add(string $back = ''): string
    {
        log_debug();

        $html = new html_base();
        $result = ''; // reset the html code var

        // at least to create the dummy objects to display the selectors
        $this->load_objects();

        // for creating a new triple the first word / triple is fixed
        $form_name = 'link_add';
        //$result .= 'Create a combined word (semantic triple):<br>';
        $result .= '<br>Define a new relation for <br><br>';
        $result .= '<b>' . $this->from()->name() . '</b> ';
        $result .= $html->dsp_form_start($form_name);
        $result .= $html->input("back", $back);
        $result .= $html->input("confirm", '1');
        $result .= $html->input("from", $this->from_id());
        $result .= '<div class="form-row">';
        if ($this->has_verb()) {
            $result .= $this->verb()->dsp_selector('both', $form_name, html_base::COL_SM_6, $back);
        }
        if ($this->to() != null) {
            $type_phr = new phrase($this->user());
            $type_phr->load_by_id(0);
            $result .= $this->to()->dsp_selector($type_phr->dsp_obj(), $form_name, 0, html_base::COL_SM_6, $back);
        }
        $result .= '</div>';
        $result .= '<br>';
        $result .= $html->dsp_form_end('', $back);

        return $result;
    }

    /**
     * display a form to adjust the link between too words or triples
     */
    function dsp_del(string $back = ''): string
    {
        log_debug("triple->dsp_del " . $this->id() . ".");
        $result = ''; // reset the html code var

        $result .= \html\btn_yesno('Is "' . $this->display() . '" wrong?', '/http/link_del.php?id=' . $this->id() . '&back=' . $back);
        $result .= '<br><br>... and "' . $this->dsp_r() . '" is also wrong.<br><br>If you press Yes, both rules will be removed.';

        return $result;
    }

    /**
     * simply to display a single triple in a table
     */
    function display_linked(): string
    {
        return '<a href="/http/view.php?link=' . $this->id() . '" title="' . $this->name() . '">' . $this->name() . '</a>';
    }

    /**
     * simply to display a single triple in a table
     */
    function dsp_tbl($intent): string
    {
        log_debug('triple->dsp_tbl');
        $result = '    <td>' . "\n";
        while ($intent > 0) {
            $result .= '&nbsp;';
            $intent = $intent - 1;
        }
        $result .= '      ' . $this->display_linked() . "\n";
        $result .= '    </td>' . "\n";
        return $result;
    }

    function dsp_tbl_row(): string
    {
        $result = '  <tr>' . "\n";
        $result .= $this->dsp_tbl(0);
        $result .= '  </tr>' . "\n";
        return $result;
    }

}
