<?php

/*

    model/phrase/phrase_type.php - the phrase type object for the frontend API
    ----------------------------


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

namespace Zukunft\ZukunftCom\main\php\cfg\phrase;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

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
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'library.php';

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
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;

class phrase_type extends type_object
{

    /*
     * database link
     */

    // comments used for the database creation
    const string TBL_COMMENT = 'for the phrase type to set the predefined behaviour of a word or triple';

    // database and JSON object field names additional to the type field only for phrase types
    const string FLD_SCALE_COM = 'e.g. for percent the scaling factor is 100';
    const string FLD_SCALE = 'scaling_factor';
    const string FLD_SYMBOL_COM = 'e.g. for percent the symbol is %';
    const string FLD_SYMBOL = 'symbol';

    // field lists for the table creation of phrase type
    const array FLD_LST_EXTRA = array(
        [self::FLD_SCALE, sql_field_type::INT, sql_field_default::NULL, '', '', self::FLD_SCALE_COM],
        [self::FLD_SYMBOL, sql_field_type::NAME, sql_field_default::NULL, '', '', self::FLD_SYMBOL_COM],
    );


    /*
     * object vars
     */

    // the access right level to prevent not permitted right gaining
    public ?int $scale = null;
    public ?string $symbol = null;


    /*
     * construct and map
     */

    function __construct(string $code_id, int $id = 0, string $name = '')
    {
        parent::__construct($code_id, $name, $id);
        $this->code_id = $code_id;
        $this->id = $id;
        $this->name = $name;
    }

    /**
     * set the vars of this phrase type object to the default values
     * @param bool $keep_user set to true to keep the original user
     * @return void
     */
    function reset(bool $keep_user = false): void
    {
        parent::reset();
        $this->scale = null;
        $this->symbol = null;
    }

    function get_code_id(): string
    {
        return $this->code_id;
    }

    /**
     * fill the user phrase type vars based on an array of fields from the database
     * @param array $db_row with the data from the database
     * @param string $class the type class name that should be filled
     * @return bool true if all expected object vars have been set
     */
    function row_mapper_typ_obj(array $db_row, string $class): bool
    {
        $result = parent::row_mapper_typ_obj($db_row, $class);
        if ($result) {
            if (array_key_exists(self::FLD_SCALE, $db_row)) {
                // TODO Prio 0 use a more general conversion for all fields
                //      e.g. $lib->convert($in_value, sql_type::INT, $msg)
                if ($db_row[self::FLD_SCALE] = 'NULL') {
                    $this->scale = null;
                } else {
                    if (is_integer($db_row[self::FLD_SCALE])) {
                        $this->scale = $db_row[self::FLD_SCALE];
                    } else {
                        log_err('scale csv value ' . $db_row[self::FLD_SCALE] . ' seems to be not an expected integer value');
                    }
                }
            }
            if (array_key_exists(self::FLD_SYMBOL, $db_row)) {
                $this->symbol = $db_row[self::FLD_SYMBOL];
            }
        }
        return $result;
    }

    /**
     * map a phrase type api json to this model phrase type object
     * @param array $api_json the api array with the word values that should be mapped
     * @param user_message $usr_msg the message for the user why the action has failed and a suggested solution
     * @return bool true if the mapping has been completed successfully
     */
    function api_mapper(array $api_json, user_message $usr_msg): bool
    {
        parent::api_mapper($api_json, $usr_msg);

        if (array_key_exists(json_fields::SCALE, $api_json)) {
            if ($api_json[json_fields::SCALE] <> '') {
                $this->scale = $api_json[json_fields::SCALE];
            }
        }
        if (array_key_exists(json_fields::SYMBOL, $api_json)) {
            if ($api_json[json_fields::SYMBOL] <> '') {
                $this->symbol = $api_json[json_fields::SYMBOL];
            }
        }

        return $usr_msg->is_ok();
    }

    /**
     * function to import the core phrase type values from a json string
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

        if (key_exists(json_fields::SCALE, $in_ex_json)) {
            $this->scale = $in_ex_json[json_fields::SCALE];
        }
        if (key_exists(json_fields::SYMBOL, $in_ex_json)) {
            $this->symbol = $in_ex_json[json_fields::SYMBOL];
        }

        return $msg->is_ok();
    }


    /*
     * load
     */

    /**
     * to load a phrase type object by the database id,
     * set the class name for the type object function
     *
     * @param int $id the id of the phrase type
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_id(int $id): int
    {
        global $db_con;

        $lib = new library();
        log_debug($id);
        $qp = $this->load_sql_by_id($db_con->sql_creator(), $id, $this::class);
        return $this->load_typ_obj($qp, $this::class);
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
        $vars[json_fields::SCALE] = $this->scale;
        $vars[json_fields::SYMBOL] = $this->symbol;
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
        if ($this->scale !== null) {
            $vars[json_fields::SCALE] = $this->scale;
        }
        if ($this->scale !== null) {
            $vars[json_fields::SYMBOL] = $this->symbol;
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
                self::FLD_SCALE,
                self::FLD_SYMBOL,
            ]
        );
    }

    /**
     * get a list of database field names, values and types that have been updated
     *
     * @param phrase_type|db_object_seq_id $obj the compare value to detect the changed fields
     * @param user_message $msg the user message object that collects any issues during the sql creation
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par_field_list list 3 entry arrays with the database field name, the value and the sql type that have been updated
     */
    function db_fields_changed(
        phrase_type|db_object_seq_id $obj,
        user_message                 $msg,
        sql_type_list                $sc_par_lst = new sql_type_list()
    ): sql_par_field_list
    {
        global $sys;

        $sc = new sql_creator();
        $do_log = $sc_par_lst->incl_log();
        $table_id = $sc->table_id($this::class);

        $lst = parent::db_fields_changed($obj, $msg, $sc_par_lst);
        if ($obj->scale !== $this->scale) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . self::FLD_SCALE,
                    $sys->typ_lst->cng_fld->id($table_id . self::FLD_SCALE),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                self::FLD_SCALE,
                $this->scale,
                sql_field_type::INT,
                $obj->scale
            );
        }
        if ($obj->symbol !== $this->symbol) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . self::FLD_SYMBOL,
                    $sys->typ_lst->cng_fld->id($table_id . self::FLD_SYMBOL),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                self::FLD_SYMBOL,
                $this->symbol,
                sql_field_type::NAME,
                $obj->symbol
            );
        }
        return $lst;
    }

}
