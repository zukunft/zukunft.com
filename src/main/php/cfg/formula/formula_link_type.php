<?php

/*

    model/formula/formula_link_type.php - the formula link type object with the ENUM values for hardcoded formulas
    ---------------------------------

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

namespace Zukunft\ZukunftCom\main\php\cfg\formula;

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
include_once paths::MODEL_PHRASE . 'phrase.php';
include_once paths::MODEL_PHRASE . 'phrase_type.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_ENUM . 'messages.php';
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
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_type;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\json_fields;

class formula_link_type extends type_object
{

    /*
     * code links
     */

    // the database and JSON object field names used only for formula links
    const string FLD_ID = 'formula_link_type_id';


    /*
     * database link
     */

    // comments used for the database creation
    const string TBL_COMMENT = 'to assign predefined behaviour to a formula link';

    // field lists for the table creation of phrase type
    const array FLD_LST_EXTRA = array(
        [formula_db::FLD_ID, sql_field_type::INT, sql_field_default::NULL, '', formula::class, ''],
        [phrase::FLD_TYPE, phrase::FLD_TYPE_SQL_TYP, sql_field_default::NULL, '', phrase_type::class, ''],
    );


    /*
     * object vars
     */

    // this formula link type only applies to this formula (id only because all statuus are always in the $sys object)
    public ?int $frm_id = null;
    // this formula link type only applies to phrases of this type (id only because all statuus are always in the $sys object)
    public ?int $phr_id = null;


    /*
     * construct and map
     */

    /**
     * set the additional vars of this formula link type object to the default values
     * @param bool $keep_user set to true to keep the original user
     * @return void
     */
    function reset(bool $keep_user = false): void
    {
        parent::reset();
        $this->frm_id = null;
        $this->phr_id = null;
    }

    /**
     * set the additional vars of this formula link type object
     * based on an array of fields from the database
     * @param array $db_row with the data from the database
     * @param string $class the type class name that should be filled
     * @return bool true if all expected object vars have been set
     */
    function row_mapper_typ_obj(array $db_row, string $class): bool
    {
        $result = parent::row_mapper_typ_obj($db_row, $class);
        if ($result) {
            if (array_key_exists(formula_db::FLD_ID, $db_row)) {
                if ($db_row[formula_db::FLD_ID] != '') {
                    $this->frm_id = $db_row[formula_db::FLD_ID];
                }
            }
            if (array_key_exists(phrase::FLD_TYPE, $db_row)) {
                if ($db_row[phrase::FLD_TYPE] != '') {
                    $this->phr_id = $db_row[phrase::FLD_TYPE];
                }
            }
        }
        return $result;
    }

    /**
     * map the additional vars of a formula link type api json
     * to this formula link type object
     * @param array $api_json the api array with the word values that should be mapped
     * @param user_message $usr_msg the message for the user why the action has failed and a suggested solution
     * @return bool true if the mapping has been completed successfully
     */
    function api_mapper(array $api_json, user_message $usr_msg): bool
    {
        parent::api_mapper($api_json, $usr_msg);

        if (array_key_exists(json_fields::FORMULA_ID, $api_json)) {
            if ($api_json[json_fields::FORMULA_ID] <> '') {
                $this->frm_id = $api_json[json_fields::FORMULA_ID];
            }
            if ($api_json[json_fields::PHRASE_TYPE_ID] <> '') {
                $this->phr_id = $api_json[json_fields::PHRASE_TYPE_ID];
            }
        }

        return $usr_msg->is_ok();
    }

    /**
     * function to import the core formula link type values from a json string
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
            if (key_exists(json_fields::FORMULA_NAME, $in_ex_json)) {
                // TODO Prio 0 convert the name to the id based on the data cache
                // TODO Prio 2 check that for import always the name or code id is converted to the id
                $this->frm_id = $in_ex_json[json_fields::FORMULA_NAME];
            }
            if (key_exists(json_fields::PHRASE_TYPE, $in_ex_json)) {
                // TODO Prio 2 check that for import always the name or code id is converted to the id
                global $sys;
                $this->phr_id = $sys->typ_lst->phr_typ->id($in_ex_json[json_fields::PHRASE_TYPE]);
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
     * @param api_type_list|array $typ_lst configuration for the api message e.g. if phrases should be included
     * @param user|null $usr the user for whom the api message should be created which can differ from the session user
     * @return array the filled array used to create the api json message to the frontend
     */
    function api_json_array(api_type_list|array $typ_lst = [], user|null $usr = null): array
    {
        $vars = parent::api_json_array($typ_lst, $usr);
        $vars[json_fields::FORMULA_ID] = $this->frm_id;
        $vars[json_fields::PHRASE_TYPE_ID] = $this->phr_id;
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
        if ($this->frm_id !== null) {
            // TODO Prio 0 get formula name based on the data cache
            $vars[json_fields::FORMULA] = $this->frm_id;
            $vars[json_fields::PHRASE_TYPE] = $this->phr_id;
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
                formula_db::FLD_ID,
                phrase::FLD_TYPE,
            ]
        );
    }

    /**
     * get a list of database field names, values and types that have been updated
     *
     * @param formula_link_type|db_object_seq_id $obj the compare value to detect the changed fields
     * @param user_message $msg the user message object that collects any issues during the sql creation
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par_field_list list 3 entry arrays with the database field name, the value and the sql type that have been updated
     */
    function db_fields_changed(
        formula_link_type|db_object_seq_id $obj,
        user_message                    $msg,
        sql_type_list                   $sc_par_lst = new sql_type_list()
    ): sql_par_field_list
    {
        global $sys;

        $sc = new sql_creator();
        $do_log = $sc_par_lst->incl_log();
        $table_id = $sc->table_id($this::class);

        $lst = parent::db_fields_changed($obj, $msg, $sc_par_lst);
        if ($obj->frm_id !== $this->frm_id) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . formula_db::FLD_ID,
                    $sys->typ_lst->cng_fld->id($table_id . formula_db::FLD_ID),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                formula_db::FLD_ID,
                $this->frm_id,
                sql_field_type::INT_SMALL,
                $obj->frm_id
            );
        }
        if ($obj->phr_id !== $this->phr_id) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . phrase::FLD_TYPE,
                    $sys->typ_lst->cng_fld->id($table_id . phrase::FLD_TYPE),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            global $sys;
            // TODO Prio 2 use this check for all type field
            if ($sys->typ_lst->phr_typ->has($this->phr_id)) {
                $msg->add(msg_id::PHRASE_TYPE_MISSING, [
                    msg_id::VAR_TYPE => $this->phr_id,
                    msg_id::VAR_NAME => $this->dsp_id()
                ]);
            }
            $lst->add_type_field(
                phrase::FLD_TYPE,
                type_object::FLD_NAME,
                $this->phr_id,
                $obj->phr_id,
                $sys->typ_lst->phr_typ);
        }
        return $lst;
    }

}
