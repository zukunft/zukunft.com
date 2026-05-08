<?php

/*

    model/language/language.php - to define a language for the user interface
    ---------------------------

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

    Copyright (c) 1995-2026 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\cfg\language;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_HELPER . 'type_object.php';
include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_field_default.php';
include_once paths::DB . 'sql_field_type.php';
include_once paths::DB . 'sql_par_field_list.php';
include_once paths::DB . 'sql_type_list.php';
include_once paths::EXPORT . 'export_type_list.php';
include_once paths::MODEL_HELPER . 'data_object.php';
include_once paths::MODEL_HELPER . 'db_object_seq_id.php';
include_once paths::MODEL_HELPER . 'type_object.php';
include_once paths::MODEL_LOG . 'change.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'library.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_default;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par_field_list;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type_list;
use Zukunft\ZukunftCom\main\php\cfg\export\export_type_list;
use Zukunft\ZukunftCom\main\php\cfg\helper\data_object;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_object_seq_id;
use Zukunft\ZukunftCom\main\php\cfg\helper\type_object;
use Zukunft\ZukunftCom\main\php\cfg\log\change;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;

class language extends type_object
{

    /*
     * db const
     */

    // database and JSON object field names
    const string TBL_COMMENT = 'for table languages';
    const string FLD_ID = 'language_id';
    const string FLD_NAME = 'language_name';
    const string FLD_NAME_COM = 'the name of the language in the system language, which is English';
    const string FLD_CODE_ID_COM = 'the ISO 639-1 language code plus BCP 47 plus additional language codes requested by zukunft.com users';
    const string FLD_WIKI_CODE = 'wikimedia_code';
    const string FLD_WIKI_CODE_COM = 'wikimedia language code e.g. no instead of nb (Norwegian Bokmål in ISO) for a full link to wikipedia';
    const string FLD_LOCAL_NAME = 'local_name';
    const string FLD_LOCAL_NAME_COM = 'the name of the language in the language';
    const string FLD_USAGE_COM = 'the number of speakers worldwide';

    // field lists for the table creation
    const array FLD_LST_NAME = array(
        [self::FLD_NAME, sql_field_type::NAME_UNIQUE, sql_field_default::NOT_NULL, sql::INDEX, '', self::FLD_NAME_COM],
    );
    const array FLD_LST_ALL = array(
        [sql_db::FLD_CODE_ID, sql_field_type::CODE_ID, sql_field_default::NULL, '', '', self::FLD_CODE_ID_COM],
        [sql_db::FLD_DESCRIPTION, sql_db::FLD_DESCRIPTION_SQL_TYP, sql_field_default::NULL, '', '', ''],
        [self::FLD_WIKI_CODE, sql_field_type::CODE_ID, sql_field_default::NULL, '', '', self::FLD_WIKI_CODE_COM],
        [self::FLD_LOCAL_NAME, sql_field_type::NAME_UNIQUE, sql_field_default::NULL, sql::INDEX, '', self::FLD_LOCAL_NAME_COM],
        [sql_db::FLD_USAGE, sql_db::FLD_USAGE_SQL_TYP, sql_field_default::NULL, '', '', self::FLD_USAGE_COM],
    );


    /*
     * object vars
     */

    public ?string $wiki_code = null; // the language code from Wikimedia for synchronisation
    public ?string $local_name = null; // the name in the language
    public ?int $usage = null; // estimation how many users the language has for sorting


    /*
     * construct and map
     */

    /**
     * set the additional vars of this language object to the default values
     * @param bool $keep_user set to true to keep the original user
     * @return void
     */
    function reset(bool $keep_user = false): void
    {
        parent::reset();
        $this->wiki_code = null;
        $this->local_name = null;
        $this->usage = null;
    }

    /**
     * set the additional vars of this language object
     * based on an array of fields from the database
     * @param array $db_row with the data from the database
     * @param string $class the type class name that should be filled
     * @return bool true if all expected object vars have been set
     */
    function row_mapper_typ_obj(array $db_row, string $class): bool
    {
        $result = parent::row_mapper_typ_obj($db_row, $class);
        if ($result) {
            if (array_key_exists(self::FLD_WIKI_CODE, $db_row)) {
                $this->wiki_code = ($db_row[self::FLD_WIKI_CODE]);
            }
            if (array_key_exists(self::FLD_LOCAL_NAME, $db_row)) {
                $this->local_name = ($db_row[self::FLD_LOCAL_NAME]);
            }
            if (array_key_exists(sql_db::FLD_USAGE, $db_row)) {
                $this->usage = ($db_row[sql_db::FLD_USAGE]);
            }
        }
        return $result;
    }

    /**
     * map the additional vars of a language api json
     * to this language object
     * @param array $api_json the api array with the word values that should be mapped
     * @param user_message $usr_msg the message for the user why the action has failed and a suggested solution
     * @return bool true if the mapping has been completed successfully
     */
    function api_mapper(array $api_json, user_message $usr_msg): bool
    {
        parent::api_mapper($api_json, $usr_msg);

        if (array_key_exists(json_fields::WIKI_CODE, $api_json)) {
            if ($api_json[json_fields::WIKI_CODE] <> '') {
                $this->wiki_code = $api_json[json_fields::WIKI_CODE];
            }
            if ($api_json[json_fields::LOCAL_NAME] <> '') {
                $this->local_name = $api_json[json_fields::LOCAL_NAME];
            }
            if ($api_json[json_fields::USAGE] <> '') {
                $this->usage = $api_json[json_fields::USAGE];
            }
        }

        return $usr_msg->is_ok();
    }

    /**
     * function to import the core language values from a json string
     *
     * @param array $in_ex_json an array with the data of the json object
     * @param user_message $msg to enrich with warnings, problems and solutions
     *                          including the user who has initiated the import
     *                          mainly used to add the code id to the database
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

        if ($msg->usr->is_admin() or $msg->usr->is_system()) {
            if (key_exists(json_fields::WIKI_CODE, $in_ex_json)) {
                $this->wiki_code = $in_ex_json[json_fields::WIKI_CODE];
            }
            if (key_exists(json_fields::LOCAL_NAME, $in_ex_json)) {
                $this->local_name = $in_ex_json[json_fields::LOCAL_NAME];
            }
            if (key_exists(json_fields::USAGE, $in_ex_json)) {
                $this->usage = $in_ex_json[json_fields::USAGE];
            }
        }

        return $msg->is_ok();
    }


    /*
     * api
     */

    /**
     * create an array for the api json creation
     * differs from the export array by using the internal id instead of the names
     * @param api_type_list $typ_lst configuration for the api message e.g. if phrases should be included
     * @param user|null $usr the user for whom the api message should be created which can differ from the session user
     * @return array the filled array used to create the api json message to the frontend
     */
    function api_json_array(api_type_list $typ_lst, user|null $usr = null): array
    {
        $vars = parent::api_json_array($typ_lst, $usr);
        $vars = array_merge($vars, get_object_vars($this));
        $vars[json_fields::ID] = $this->id();
        $vars[json_fields::WIKI_CODE] = $this->wiki_code;
        $vars[json_fields::LOCAL_NAME] = $this->local_name;
        $vars[json_fields::USAGE] = $this->usage;
        return $vars;
    }


    /*
     * load
     */

    /**
     * load a language object by database id
     * mainly set the class name for the type object function
     *
     * @param int $id the id of the language
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_id(int $id): int
    {
        global $db_con;

        log_debug($id);
        $qp = $this->load_sql_by_id($db_con->sql_creator(), $id, $this::class);
        return $this->load_typ_obj($qp, $this::class);
    }

    /**
     * load a language object by database id
     * mainly set the class name for the type object function
     *
     * @param string $name the name of the language
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_name(string $name): int
    {
        global $db_con;

        log_debug($name);
        $lib = new library();
        $dp_type = $lib->class_to_name($this::class);
        $qp = $this->load_sql_by_name($db_con->sql_creator(), $name, $dp_type);
        return $this->load_typ_obj($qp, $this::class);
    }


    /*
     * im- and export
     */

    /**
     * create an array with the export json fields
     * @param export_type_list|array $exp_typ define the export format
     * @param bool $do_load to switch off the database load for unit tests
     * @return array the filled array used to create the user export json
     */
    function export_json(export_type_list|array $exp_typ = [], bool $do_load = true): array
    {
        $vars = parent::export_json($exp_typ, $do_load);
        if ($this->wiki_code !== null) {
            $vars[json_fields::WIKI_CODE] = $this->wiki_code;
        }
        if ($this->local_name !== null) {
            $vars[json_fields::LOCAL_NAME] = $this->local_name;
        }
        if ($this->usage !== null) {
            $vars[json_fields::USAGE] = $this->usage;
        }
        return $vars;
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
                self::FLD_WIKI_CODE,
                self::FLD_LOCAL_NAME,
                sql_db::FLD_USAGE,
            ]
        );
    }

    /**
     * get a list of database field names, values and types that have been updated
     *
     * @param language|db_object_seq_id $obj the compare value to detect the changed fields
     * @param user_message $msg the user message object that collects any issues during the sql creation
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par_field_list list 3 entry arrays with the database field name, the value and the sql type that have been updated
     */
    function db_fields_changed(
        language|db_object_seq_id $obj,
        user_message                    $msg,
        sql_type_list                   $sc_par_lst = new sql_type_list()
    ): sql_par_field_list
    {
        global $sys;

        $sc = new sql_creator();
        $do_log = $sc_par_lst->incl_log();
        $table_id = $sc->table_id($this::class);

        $lst = parent::db_fields_changed($obj, $msg, $sc_par_lst);
        if ($obj->wiki_code !== $this->wiki_code) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . self::FLD_WIKI_CODE,
                    $sys->typ_lst->cng_fld->id($table_id . self::FLD_WIKI_CODE),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                self::FLD_WIKI_CODE,
                $this->wiki_code,
                sql_field_type::NAME,
                $obj->wiki_code
            );
        }
        if ($obj->local_name !== $this->local_name) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . self::FLD_LOCAL_NAME,
                    $sys->typ_lst->cng_fld->id($table_id . self::FLD_LOCAL_NAME),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                self::FLD_LOCAL_NAME,
                $this->local_name,
                sql_field_type::NAME,
                $obj->local_name
            );
        }
        if ($obj->usage !== $this->usage) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . sql_db::FLD_USAGE,
                    $sys->typ_lst->cng_fld->id($table_id . sql_db::FLD_USAGE),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                sql_db::FLD_USAGE,
                $this->usage,
                sql_db::FLD_USAGE_SQL_TYP,
                $obj->usage
            );
        }
        return $lst;
    }


    /*
     * sql fields
     */

    function name_field(): string
    {
        return self::FLD_NAME;
    }

}
