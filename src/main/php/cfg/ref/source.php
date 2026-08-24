<?php

/*

    model/ref/source.php - the source object to define a source for values
    ------------------

    $src is the suggested var name

    a source is always unidirectional
    in many cases a source is just a user base data source without any import
    the automatic import can be based on standard data format e.g. json, XML or HTML

    reference types are preloaded in the frontend whereas source are loaded on demand

    if the import gets more complex or the interface is bidirectional use a reference type
    references are concrete links between a phrase and an external object

    The main sections of this object are
    - db const:          const for the database link
    - object vars:       the variables of this word object
    - construct and map: including the mapping of the db row to this word object
    - api:               create an api array for the frontend and set the vars based on a frontend api message
    - im- and export:    create an export object and set the vars from an import object
    - set and get:       to capsule the vars from unexpected changes
    - preloaded:         select e.g. types from cache
    - cast:              create an api object and set the vars from an api json
    - convert:           convert this word e.g. phrase or term
    - load:              database access object (DAO) functions
    - sql fields:        field names for sql
    - sandbox:           manage the user sandbox
    - save:              manage to update the database
    - sql write:         sql statement creation to write to the database
    - sql write fields:  field list for writing to the database


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

namespace Zukunft\ZukunftCom\main\php\cfg\ref;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_SANDBOX . 'sandbox_typed.php';
include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_field_default.php';
include_once paths::DB . 'sql_par.php';
include_once paths::DB . 'sql_par_field_list.php';
include_once paths::DB . 'sql_type.php';
include_once paths::DB . 'sql_type_list.php';
include_once paths::MODEL_CONST . 'def.php';
include_once paths::EXPORT . 'export_type_list.php';
include_once paths::MODEL_HELPER . 'data_object.php';
include_once paths::MODEL_HELPER . 'db_object_seq_id.php';
include_once paths::MODEL_HELPER . 'type_object.php';
include_once paths::MODEL_LOG . 'change.php';
include_once paths::MODEL_REF . 'source_db.php';
include_once paths::MODEL_SANDBOX . 'sandbox.php';
include_once paths::MODEL_SANDBOX . 'sandbox_named.php';
include_once paths::MODEL_SANDBOX . 'sandbox_code_id.php';
include_once paths::MODEL_SANDBOX . 'sandbox_related.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_db.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::MODEL_VALUE . 'value_list.php';
include_once paths::MODEL_VIEW . 'view_list.php';
include_once paths::SHARED_CONST . 'sources.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_HELPER . 'CombineObject.php';
include_once paths::SHARED_HELPER . 'IdObject.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';
include_once paths::SHARED_TYPES . 'api_types.php';
include_once paths::SHARED_TYPES . 'view_types.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'library.php';
include_once paths::SHARED_CONST_FIELDS . 'fields.php';
include_once paths::SHARED_CONST_FIELDS . 'source_fields.php';

use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\main\php\cfg\db\sql;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par_field_list;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type_list;
use Zukunft\ZukunftCom\main\php\cfg\export\export_type_list;
use Zukunft\ZukunftCom\main\php\cfg\helper\data_object;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_object_seq_id;
use Zukunft\ZukunftCom\main\php\cfg\helper\type_object;
use Zukunft\ZukunftCom\main\php\cfg\log\change;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_code_id;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_related;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_typed;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_db;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\value\value_list;
use Zukunft\ZukunftCom\main\php\cfg\view\view_list;
use Zukunft\ZukunftCom\main\php\shared\const\sources;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\helper\CombineObject;
use Zukunft\ZukunftCom\main\php\shared\helper\IdObject;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\shared\types\view_types;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\const\fields\fields;
use Zukunft\ZukunftCom\main\php\shared\const\fields\source_fields;

class source extends sandbox_code_id
{

    /*
     * db const
     */

    // comments used for the database creation
    const string TBL_COMMENT = 'for the original sources for the numeric, time and geo values';

    // forward the const string to enable usage of $this::CONST_NAME
    const string FLD_ID = source_fields::FLD_ID;
    const array FLD_NAMES = source_db::FLD_NAMES;
    const array FLD_NAMES_USR = source_db::FLD_NAMES_USR;
    const array FLD_NAMES_NUM_USR = source_db::FLD_NAMES_NUM_USR;
    const array FLD_LST_MUST_BE_IN_STD = source_db::FLD_LST_MUST_BE_IN_STD;
    const array FLD_LST_MUST_BUT_USER_CAN_CHANGE = source_db::FLD_LST_MUST_BUT_USER_CAN_CHANGE;
    const array FLD_LST_USER_CAN_CHANGE = source_db::FLD_LST_USER_CAN_CHANGE;


    /*
     * object vars
     */

    // database fields additional to the user sandbox fields
    // the internet link to the source
    public ?string $url = null;
    // the digital object identifier of the source e.g. 10.5281/zenodo.19443909 used to create the url to doi.org
    public ?string $doi = null;

    // the views that can show this source; populated lazily by load_views_related() and only
    // emitted via api_json_array() under the INCL_RELATED flag, so the views tab of the default
    // source view can offer the views to switch to
    public ?view_list $views_related = null;

    // the values that name this source; filled and emitted like views_related above, so that the
    // 'source values' component of the source page lists them
    public ?value_list $values_related = null;


    /*
     * construct and map
     */

    /**
     * set the user and fix the setting of this source object
     * @param user $usr the user who requested to see the source
     */
    function __construct(user $usr)
    {
        $this->reset();
        parent::__construct($usr);

        $this->rename_can_switch = def::UI_CAN_CHANGE_SOURCE_NAME;
    }

    /**
     * set the vars of this source object to the default values
     * @param bool $keep_user set to true to keep the original user
     * @return void
     */
    function reset(bool $keep_user = false): void
    {
        parent::reset($keep_user);
        $this->url = null;
        $this->doi = null;
    }

    /**
     * map the database object to this source class fields
     *
     * @param array|null $db_row with the data directly from the database
     * @param bool $load_std true if only the standard user sandbox object is loaded
     * @param bool $allow_usr_protect false for using the standard protection settings for the default object used for all users
     * @param string $id_fld the name of the id field as defined in this child and given to the parent
     * @param string $name_fld the name of the name field as defined in this child class
     * @param string $type_fld the name of the type field as defined in this child class
     * @return bool true if the source is loaded and valid
     */
    function row_mapper_sandbox(
        ?array       $db_row,
        user_message $msg,
        bool         $load_std = false,
        bool         $allow_usr_protect = true,
        string       $id_fld = source_fields::FLD_ID,
        string       $name_fld = source_fields::FLD_NAME,
        string       $type_fld = source_fields::FLD_TYPE
    ): bool
    {
        $result = parent::row_mapper_sandbox($db_row, $msg, $load_std, $allow_usr_protect, $id_fld, $name_fld, $type_fld);
        if ($result) {
            $this->url = $db_row[fields::FLD_URL];
            $this->doi = $db_row[fields::FLD_DOI];
        }
        return $msg->is_ok();
    }

    /**
     * map a source api json to this model source object
     * similar to the import_obj function but using the database id instead of names as the unique key
     * @param array $api_json the api array with the triple values that should be mapped
     * @param user_message $msg the message for the user why the action has failed and a suggested solution
     * @return bool true if the mapping has been completed successfully
     */
    function api_mapper(array $api_json, user_message $msg): bool
    {
        parent::api_mapper($api_json, $msg);

        if (array_key_exists(json_fields::URL, $api_json)) {
            $this->url = $api_json[json_fields::URL];
        }
        if (array_key_exists(json_fields::DOI, $api_json)) {
            $this->doi = $api_json[json_fields::DOI];
        }

        return $msg->is_ok();
    }

    /**
     * set the object vars of this source object based on the import json array
     *
     * @param array $in_ex_json an array with the data of the json object
     * @param user_message $msg to enrich with warnings, problems and solutions including the user who has initiated the import mainly used to add tge code id to the database
     * @param data_object|null $dto cache of the objects imported until now for the primary references
     * @return bool true if everything was fine
     */
    function import_mapper(
        array        $in_ex_json,
        user_message $msg,
        ?data_object $dto = null
    ): bool
    {
        parent::import_mapper($in_ex_json, $msg, $dto);

        if (key_exists(json_fields::URL, $in_ex_json)) {
            $this->url = $in_ex_json[json_fields::URL];
        }
        if (key_exists(json_fields::DOI, $in_ex_json)) {
            $this->doi = $in_ex_json[json_fields::DOI];
        }
        // json_fields::AUTHOR, PUBLISHER and PUBLISH_DATE are allowed in the import json, but
        // the source object has no field for them, see the TODO Prio 2 of json_fields::AUTHOR

        return $msg->is_ok();
    }


    /*
     * api
     */

    /**
     * create an array for the api json creation
     * differs from the export array by using the internal id instead of the names
     * @param api_type_list|array $typ_lst configuration for the api message e.g. if phrases should be included
     * @param user_message $msg to collect the mapping problems for the requesting user
     * @param user|null $usr the user for whom the api message should be created which can differ from the session user
     * @return array the filled array used to create the api json message to the frontend
     */
    function api_json_array(api_type_list|array $typ_lst, user_message $msg, user|null $usr = null): array
    {
        $vars = [];
        if (is_array($typ_lst)) {
            $typ_lst = new api_type_list($typ_lst);
        }
        if (!$this->is_excluded() or $typ_lst->test_mode() or $typ_lst->with_excluded()) {
            $vars = parent::api_json_array($typ_lst, $msg, $usr);
            $vars[json_fields::URL] = $this->url;
            $vars[json_fields::DOI] = $this->doi;
            // the views, changes and overwrites tabs of the source default page
            if ($typ_lst->incl_related()) {
                if ($this->views_related == null and !$typ_lst->test_mode()) {
                    $this->load_views_related($msg);
                }
                $vars = array_merge($vars,
                    new sandbox_related()->views_array($this->views_related, $msg, $usr));
                // a value names the source by its id, so a fresh source (id 0, e.g. the add
                // form) has none, whereas the views above are the same for every source
                if ($this->values_related == null and !$typ_lst->test_mode() and $this->id() != 0) {
                    $this->load_values_related($msg);
                }
                if ($this->values_related != null and !$this->values_related->is_empty()) {
                    // drop the values the requester may not read, so the list cannot disclose
                    // another user's private value of this source (idor), the same gate that
                    // word::api_json_array uses for the values of a word
                    $this->values_related->filter_readable_by($usr);
                    // INCL_PHRASES so each value carries its group phrases, which the frontend
                    // needs for the value name
                    $vars[json_fields::VALUES] = $this->values_related->api_json_array(
                        new api_type_list([api_types::INCL_PHRASES]), $msg, $usr);
                }
                $vars = array_merge($vars, $this->api_changes_array($typ_lst, $msg, $usr));
                $vars = array_merge($vars, $this->api_overwrites_array($typ_lst, $msg, $usr));
            }
        } elseif ($this->is_excluded() and $typ_lst->with_excluded_id()) {
            $vars[json_fields::ID] = $this->id();
            $vars[json_fields::EXCLUDED] = true;
        }
        return $vars;
    }

    /**
     * load the views that can show this source into the in-memory views_related list so that
     * api_json_array() can emit them under the INCL_RELATED flag; a source has no view of its
     * own (the sources table has no view id), so the related views are all views of the source
     * view type, which is what the views tab offers the user to switch to
     *
     * @param user_message $msg to collect any problem while loading the views
     * @return void
     */
    function load_views_related(user_message $msg): void
    {
        global $sys;

        $msk_lst = new view_list($this->get_user());
        $msk_lst->load_by_type($sys->typ_lst->msk_typ->id(view_types::SOURCE), $msg);
        $this->views_related = $msk_lst;
    }

    /**
     * load the values that name this source into the in-memory values_related list so that
     * api_json_array() can emit them under the INCL_RELATED flag, which the 'source values'
     * component of the source default page shows (see web/component/execute/ui_list::values_by_source)
     *
     * @param user_message $msg to collect any problem while loading the values
     * @return void
     */
    function load_values_related(user_message $msg): void
    {
        $val_lst = new value_list($this->get_user());
        $val_lst->load_by_source($this, $msg);
        $val_lst->load_names_related($msg);
        $this->values_related = $val_lst;
    }


    /*
     * im- and export
     */

    /**
     * create an array with the export json fields
     * @param user_message $msg to collect the export errors
     * @param export_type_list|array $exp_typ define the export format
     * @param bool $do_load true if any missing data should be loaded while creating the array
     * @return array with the json fields
     */
    function export_json(user_message $msg, export_type_list|array $exp_typ = [], bool $do_load = true): array
    {
        $vars = parent::export_json($msg, $exp_typ, $do_load);

        if ($this->url <> '') {
            $vars[json_fields::URL] = $this->url;
        }
        if ($this->doi <> '') {
            $vars[json_fields::DOI] = $this->doi;
        }

        return $vars;
    }


    /*
     * set and get
     */

    /**
     * set the predefined source type by the given code id or name
     *
     * @param string $code_id_or_name the code id or name of the source type that should be added to this source
     * @param user_message $msg with the requesting user; enriched with a missing-type or permission warning
     * @return bool true if the source type has been set, false if unknown or not permitted
     */
    function set_type(string $code_id_or_name, user_message $msg): bool
    {
        global $sys;
        if ($sys->typ_lst->src_typ->has_code_id($code_id_or_name)) {
            return parent::set_type_by_code_id(
                $code_id_or_name, $sys->typ_lst->src_typ, msg_id::SOURCE_TYPE_NOT_FOUND, $msg);
        } else {
            return parent::set_type_by_name(
                $code_id_or_name, $sys->typ_lst->src_typ, msg_id::SOURCE_TYPE_NOT_FOUND, $msg);
        }
    }


    /*
     * preloaded
     */

    /**
     * @return string|null the code_id of the source type
     */
    function type_code_id(): string|null
    {
        global $sys;
        return $sys->typ_lst->src_typ->code_id($this->type_id);
    }

    /**
     * @return string the source type name from the array preloaded from the database
     */
    function type_name(): string
    {
        global $sys;

        $type_name = '';
        if ($this->type_id > 0) {
            $type_name = $sys->typ_lst->src_typ->name($this->type_id);
        }
        return $type_name;
    }


    /*
     * load sql
     */

    /**
     * create the common part of an SQL statement to retrieve the parameters of a source from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name extension to make the query name unique
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql(sql_creator $sc, string $query_name): sql_par
    {
        $sc->set_class($this::class);
        return parent::load_sql_fields(
            $sc, $query_name,
            source_db::FLD_NAMES,
            source_db::FLD_NAMES_USR,
            source_db::FLD_NAMES_NUM_USR
        );
    }

    /**
     * create an SQL statement to retrieve the user changes of the current source
     *
     * @param sql_creator $sc with the target db_type set
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation e.g. standard for values and results
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql_user_changes(
        sql_creator   $sc,
        sql_type_list $sc_par_lst = new sql_type_list()
    ): sql_par
    {
        $sc->set_class($this::class, new sql_type_list([sql_type::USER]));
        return parent::load_sql_user_changes($sc, $sc_par_lst);
    }


    /*
     * info
     */

    /**
     * create human-readable messages of the differences between the source objects
     * is expected to be similar to the needs_db_update function
     *
     * @param source|CombineObject|db_object_seq_id $obj which might be different to this source
     * @param bool $ex_def if true excluding differences in fields with a default value like the type
     * @return user_message the human-readable messages of the differences between the source objects
     */
    function diff_msg(source|CombineObject|db_object_seq_id $obj, bool $ex_def = false): user_message
    {
        $msg = parent::diff_msg($obj, $ex_def);
        $this->diff_field_msg($msg, fields::FLD_URL, $this->url, $obj->url);
        $this->diff_field_msg($msg, fields::FLD_DOI, $this->doi, $obj->doi);
        return $msg;
    }

    /**
     * check if the source in the database needs to be updated
     * e.g. for import if this source has only the name set, the protection should not be updated in the database
     *
     * @param source|CombineObject|IdObject $db_obj the source as saved in the database
     * @param user_message $msg to collect the messages
     * @return bool true if this source has infos that should be saved in the database
     */
    function needs_db_update(source|CombineObject|IdObject $db_obj, user_message $msg): bool
    {
        $result = parent::needs_db_update($db_obj, $msg);
        if ($this->url != null) {
            if ($this->url != $db_obj->url) {
                $result = true;
            }
        }
        if ($this->doi != null) {
            if ($this->doi != $db_obj->doi) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * Create an object where only the vars are set
     * where the var of this object differs from the var of the given object.
     *
     * @param source|CombineObject|db_object_seq_id $std_obj the norm object as saved in the database
     * @param source|CombineObject|db_object_seq_id $result empty clone of the target user object
     * @return source|CombineObject|db_object_seq_id the object where only the vars are set that are changed compared to the given $obj
     */
    function delta(
        source|CombineObject|db_object_seq_id $std_obj,
        source|CombineObject|db_object_seq_id $result
    ): source|CombineObject|db_object_seq_id
    {
        parent::delta($std_obj, $result);
        if ($std_obj->url !== $this->url) {
            $result->url = $this->url;
        }
        if ($std_obj->doi !== $this->doi) {
            $result->doi = $this->doi;
        }
        return $result;
    }


    /*
     * modify
     */

    /**
     * fill this source object based on the given object
     *
     * @param source|CombineObject|db_object_seq_id $obj sandbox object with the values that should be updated e.g. based on the import
     * @param user $usr_req the user who has requested the fill
     * @return user_message a warning in case of a conflict e.g. due to a missing change time
     */
    function fill(source|CombineObject|db_object_seq_id $obj, user $usr_req): user_message
    {
        $msg = parent::fill($obj, $usr_req);
        if ($this->url === null and $obj->url != null) {
            $this->url = $obj->url;
        }
        if ($this->doi === null and $obj->doi != null) {
            $this->doi = $obj->doi;
        }
        return $msg;
    }


    /*
     * sql fields
     */

    function name_field(): string
    {
        return source_fields::FLD_NAME;
    }

    /**
     * @return array with all fields names of this source object
     */
    protected function all_fields(): array
    {
        return array_merge(
            source_db::FLD_NAMES,
            source_db::FLD_NAMES_USR,
            source_db::FLD_NAMES_NUM_USR,
            array(user_db::FLD_ID));
    }

    function all_sandbox_fields(): array
    {
        return source_fields::ALL_NAMES;
    }


    /*
     * sandbox
     */

    /**
     * @param user_message $msg to enrich with problems and suggested solutions
     * @return bool true if no one has used this source
     */
    function not_used(user_message $msg): bool
    {
        log_debug($this->id());

        // to review: maybe replace by a database foreign key check
        return $this->not_changed($msg);
    }

    /**
     * @param user_message $msg to enrich with problems and suggested solutions
     * @return bool true if no other user has modified the source
     */
    function not_changed(user_message $msg): bool
    {
        log_debug($this->dsp_id() . ' by someone else than the owner (' . $this->owner_id() . ')');

        global $db_con;
        $result = true;

        $lib = new library();
        if ($this->id() == 0) {
            log_err('The id must be set to detect if the link has been changed');
        } else {
            $qp = $this->not_changed_sql($db_con->sql_creator());
            $db_row = $db_con->get1($qp, $msg);
            $change_user_id = $db_row[user_db::FLD_ID];
            if ($change_user_id > 0) {
                $result = false;
            }
        }
        log_debug('for ' . $this->dsp_id() . ' is ' . $lib->dsp_bool($result));
        return $result;
    }

    /**
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     *                 to check if the source has been changed
     */
    function not_changed_sql(sql_creator $sc): sql_par
    {
        $sc->set_class(source::class);
        return $sc->load_sql_not_changed($this->id(), $this->owner_id());
    }


    /*
     * save helper
     */

    /**
     * @return array with the reserved source names
     */
    protected function reserved_names(): array
    {
        return sources::RESERVED_NAMES;
    }

    /**
     * @return array with the fixed source names for db read testing
     */
    protected function fixed_names(): array
    {
        return sources::FIXED_NAMES;
    }


    /*
     * del
     */

    /**
     * delete the references to this source
     *
     * @param user_message $msg the message for the user why deleting the word links has failed and a suggested solution
     * @return bool true if the word links has been deleted
     */
    function del_links(user_message $msg): bool
    {
        // collect all phrase groups where this word is used
        // TODO Prio 2 activate
        //$grp_lst = new group_list($this->get_user());
        //$grp_lst->load_by_phr($this->phrase());

        // collect all references where this source is used
        $ref_lst = new ref_list($this->get_user());
        // TODO Prio 1 activate
        $ref_lst->load_sql_by_source($this);

        // if there are still triples, ask if they really should be deleted
        if (!$ref_lst->is_empty()) {
            // TODO Prio 1 activate
            $ref_lst->del($msg);
        }

        return $msg->is_ok();
    }


    /*
     * sql write fields
     */

    /**
     * get a list of all database fields that might be changed
     * excluding the internal fields e.g. the database id
     * field list must be corresponding to the db_fields_changed fields
     *
     * @param sql_type_list $sc_par_lst only used for link objects
     * @return array list of all database field names that have been updated
     */
    function db_fields_all(sql_type_list $sc_par_lst = new sql_type_list()): array
    {
        return array_merge(
            parent::db_fields_all(),
            [
                source_fields::FLD_TYPE,
                fields::FLD_URL,
                fields::FLD_DOI,
            ],
            parent::db_fields_all_sandbox()
        );
    }

    /**
     * get a list of database field names, values and types that have been updated
     *
     * @param source|db_object_seq_id $obj the compare value to detect the changed fields
     * @param user_message $msg the user message object that collects any issues during the sql creation
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par_field_list list of the database field names that have been updated
     */
    function db_fields_changed(
        source|db_object_seq_id $obj,
        user_message            $msg,
        sql_type_list           $sc_par_lst = new sql_type_list()
    ): sql_par_field_list
    {
        global $sys;

        $sc = new sql_creator();
        $do_log = $sc_par_lst->incl_log();
        $table_id = $sc->table_id($this::class, $sc_par_lst);

        $lst = parent::db_fields_changed($obj, $msg, $sc_par_lst);
        if ($obj->type_id($msg) !== $this->type_id($msg)) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . source_fields::FLD_TYPE,
                    $sys->typ_lst->cng_fld->id($table_id . source_fields::FLD_TYPE),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                source_fields::FLD_TYPE,
                $this->type_id($msg),
                type_object::FLD_ID_SQL_TYP,
                $obj->type_id($msg)
            );
        }
        if ($obj->url !== $this->url) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . fields::FLD_URL,
                    $sys->typ_lst->cng_fld->id($table_id . fields::FLD_URL),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                fields::FLD_URL,
                $this->url,
                source_db::FLD_URL_SQL_TYP,
                $obj->url
            );
        }
        if ($obj->doi !== $this->doi) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . fields::FLD_DOI,
                    $sys->typ_lst->cng_fld->id($table_id . fields::FLD_DOI),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                fields::FLD_DOI,
                $this->doi,
                source_db::FLD_DOI_SQL_TYP,
                $obj->doi
            );
        }
        return $lst->merge($this->db_changed_sandbox_list($obj, $sc_par_lst));
    }

}
