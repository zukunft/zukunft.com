<?php

/*

    model/user/user_profile.php - a database based enum for the user profiles
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

    Copyright (c) 1995-2023 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

namespace Zukunft\ZukunftCom\main\php\cfg\user;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_HELPER . 'type_object.php';
include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_field_default.php';
include_once paths::DB . 'sql_field_type.php';
include_once paths::DB . 'sql_par_field_list.php';
include_once paths::DB . 'sql_type_list.php';
include_once paths::EXPORT . 'export_type_list.php';
include_once paths::MODEL_HELPER . 'data_object.php';
include_once paths::MODEL_HELPER . 'db_object_seq_id.php';
include_once paths::MODEL_HELPER . 'type_object.php';
include_once paths::MODEL_LOG . 'change.php';
include_once paths::SHARED_ENUM . 'user_profiles.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';
include_once paths::SHARED . 'json_fields.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_default;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par_field_list;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type_list;
use Zukunft\ZukunftCom\main\php\cfg\export\export_type_list;
use Zukunft\ZukunftCom\main\php\cfg\helper\data_object;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_object_seq_id;
use Zukunft\ZukunftCom\main\php\cfg\helper\type_object;
use Zukunft\ZukunftCom\main\php\cfg\log\change;
use Zukunft\ZukunftCom\main\php\shared\enum\user_profiles;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\json_fields;

class user_profile extends type_object
{

    /*
     * database link
     */

    // comments used for the database creation
    const string TBL_COMMENT = 'to define the user roles and read and write rights';

    // database fields additional to the standard field names
    const string FLD_ID = 'user_profile_id';
    const string FLD_LEVEL_COM = 'the access right level to prevent not permitted right gaining';
    const string FLD_LEVEL = 'right_level';

    // additional fields for the table creation of user profiles
    const array FLD_LST_EXTRA = array(
        [self::FLD_LEVEL, sql_field_type::INT_SMALL, sql_field_default::NULL, '', '', self::FLD_LEVEL_COM],
    );


    /*
     * object vars
     */

    // the access right level to prevent not permitted right gaining
    public ?int $right_level = null;


    /*
     * construct and map
     */

    /**
     * set the vars of this user profile object to the default values
     * @param bool $keep_user set to true to keep the original user
     * @return void
     */
    function reset(bool $keep_user = false): void
    {
        parent::reset();
        $this->right_level = null;
    }

    /**
     * map a user profile api json to this model user profile object
     * @param array $api_json the api array with the word values that should be mapped
     * @param user_message $usr_msg the message for the user why the action has failed and a suggested solution
     * @return bool true if the mapping has been completed successfully
     */
    function api_mapper(array $api_json, user_message $usr_msg): bool
    {
        parent::api_mapper($api_json, $usr_msg);

        if (array_key_exists(json_fields::RIGHT_LEVEL, $api_json)) {
            if ($api_json[json_fields::RIGHT_LEVEL] <> '') {
                $this->right_level = $api_json[json_fields::RIGHT_LEVEL];
            }
        }

        return $usr_msg->is_ok();
    }

    /**
     * function to import the core user profile values from a json string
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

        if ($msg->usr->is_admin() or $msg->usr->is_system()) {
            if (key_exists(json_fields::RIGHT_LEVEL, $in_ex_json)) {
                $this->right_level = $in_ex_json[json_fields::RIGHT_LEVEL];
            }
        }

        return $msg->is_ok();
    }

    /**
     * set the user profile vars based on a database record
     *
     * @param array|null $db_row is an array with the database values
     * @param string $id_fld the name of the id field
     * @return bool true if the verb is loaded and valid
     */
    function row_mapper(
        ?array $db_row,
        string $id_fld = self::FLD_ID): bool
    {
        $result = parent::row_mapper($db_row, $id_fld);
        if ($result) {
            if (array_key_exists(self::FLD_LEVEL, $db_row)) {
                if ($db_row[self::FLD_LEVEL] != null) {
                    $this->right_level = $db_row[self::FLD_LEVEL];
                }
            }
        }
        return $result;
    }


    /*
     * api
     */

    /**
     * create an array for the api json creation
     * differs from the export array by using the internal id instead of the names
     * @param api_type_list|array $typ_lst configuration for the api message e.g. if phrases should be included
     * @param user|null $usr the user for whom the api message should be created which can differ from the session user
     * @return array the filled array used to create the api json message to the frontend
     */
    function api_json_array(api_type_list|array $typ_lst = [], user|null $usr = null): array
    {
        $vars = parent::api_json_array($typ_lst, $usr);
        $vars[json_fields::RIGHT_LEVEL] = $this->right_level;
        return $vars;
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
        if ($this->right_level !== null) {
            $vars[json_fields::RIGHT_LEVEL] = $this->right_level;
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
                self::FLD_LEVEL
            ]
        );
    }

    /**
     * get a list of database field names, values and types that have been updated
     *
     * @param user_profile|db_object_seq_id $obj the compare value to detect the changed fields
     * @param user_message $msg the user message object that collects any issues during the sql creation
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par_field_list list 3 entry arrays with the database field name, the value and the sql type that have been updated
     */
    function db_fields_changed(
        user_profile|db_object_seq_id $obj,
        user_message                  $msg,
        sql_type_list                 $sc_par_lst = new sql_type_list()
    ): sql_par_field_list
    {
        global $sys;

        $sc = new sql_creator();
        $do_log = $sc_par_lst->incl_log();
        $table_id = $sc->table_id($this::class);

        $lst = parent::db_fields_changed($obj, $msg, $sc_par_lst);
        if ($obj->right_level !== $this->right_level) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . self::FLD_LEVEL,
                    $sys->typ_lst->cng_fld->id($table_id . self::FLD_LEVEL),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                self::FLD_LEVEL,
                $this->right_level,
                sql_field_type::INT_SMALL,
                $obj->right_level
            );
        }
        return $lst;
    }


    /*
     * info
     */

    function is_system(): bool
    {
        return $this->is_type(user_profiles::SYSTEM);
    }

}