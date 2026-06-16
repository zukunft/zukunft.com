<?php

/*

    model/value/value.php - the main numeric value object using the prime, norm and big value keys
    ---------------------

    $val is the suggested var name

    The main sections of this object are
    - db const:          const for the database link
    - object vars:       the variables of this word object
    - construct and map: including the mapping of the db row to this word object
    - set and get:       to capsule the vars from unexpected changes
    - api:               create an api array for the frontend and set the vars based on a frontend api message
    - im- and export:    create an export object and set the vars from an import object
    - sql helper:        object specific parameters for creating the sql statements


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

namespace Zukunft\ZukunftCom\main\php\cfg\value;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_VALUE . 'value_base.php';
include_once paths::DB . 'sql_field_default.php';
include_once paths::DB . 'sql_field_type.php';
include_once paths::EXPORT . 'export_type_list.php';
include_once paths::MODEL_GROUP . 'group.php';
include_once paths::MODEL_HELPER . 'db_object_multi.php';
include_once paths::MODEL_SANDBOX . 'sandbox_multi.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';
include_once paths::SHARED . 'json_fields.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_default;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_type;
use Zukunft\ZukunftCom\main\php\cfg\export\export_type_list;
use Zukunft\ZukunftCom\main\php\cfg\group\group;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_object_multi;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_multi;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use DateTime;

class value extends value_base
{

    /*
     * db const
     */

    // object specific database and JSON object field names
    const string FLD_VALUE = 'numeric_value';
    const string FLD_COM = 'the numeric given by the user';
    const string FLD_USER_COM = 'the user-specific numeric value change';

    // database field with the sql type specification
    const array FLD_NAMES_STD = value_db::FLD_NAMES_STD;
    const array FLD_ALL_VALUE = array(
        [self::FLD_VALUE, sql_field_type::NUMERIC_FLOAT, sql_field_default::NOT_NULL, '', '', self::FLD_COM],
    );
    const array FLD_ALL_VALUE_USER = array(
        [self::FLD_VALUE, sql_field_type::NUMERIC_FLOAT, sql_field_default::NULL, '', '', self::FLD_USER_COM],
    );


    /*
     * object vars
     */

    // database related variables
    private ?float $number = null;


    /*
     * construct and map
     */

    /**
     * set the user, which is needed in all cases and the main vars with the object creation
     * @param user $usr the user who requested to see this value
     * @param float|null $val the numeric value that should be set on creation
     * @param group|null $grp the phrases for unique identification of this value
     */
    function __construct(
        user       $usr,
        float|null $val = null,
        ?group     $grp = null
    )
    {
        parent::__construct($usr, $val, $grp);
    }

    /**
     * map a numeric value api json to this model value object
     * @param array $api_json the api array with the values that should be mapped
     * @param user_message $msg if the mapping is incomplete, the human-readable message what happened and how to solve it
     * @return bool true if the mapping has been completed successfully
     */
    function api_mapper(array $api_json, user_message $msg): bool
    {
        parent::api_mapper($api_json, $msg);

        if (array_key_exists(json_fields::NUMBER, $api_json)) {
            $value = $api_json[json_fields::NUMBER];
            if (is_numeric($value)) {
                $this->set_value($value);
            } else {
                $msg->add(msg_id::IMPORT_VALUE_NOT_NUMERIC, [
                    msg_id::VAR_VALUE => $value,
                    msg_id::VAR_GROUP => $this->grp()->dsp_id()
                ]);
            }
        }

        return $msg->is_ok();
    }


    /*
     * set and get
     */

    /**
     * overwrite the sandbox_value set_value() function to set the numeric value
     * @param float|DateTime|string|null $val
     * @return void
     */
    function set_value(float|DateTime|string|null $val): void
    {
        $this->set_num_value($val);
    }

    /**
     * overwrite the sandbox_value value() function to return the numeric value
     * @return float|null the numeric value
     */
    function get_value(): float|null
    {
        return $this->number;
    }

    /**
     * accept only numeric values
     * @param float|null $num_val a numeric value
     * @return void
     */
    private function set_num_value(?float $num_val): void
    {
        $this->number = $num_val;
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

        // add the numeric string itself
        $vars[json_fields::NUMBER] = $this->get_value();

        return $vars;
    }

    // TODO test set_by_api_json

    /*
     * im- and export
     */

    /**
     * create an array with the export json fields
     * differs from the api array by NOT using the internal id
     * instead of the names for a complete independent recreation
     * @param export_type_list|array $exp_typ define the export format
     * @param bool $do_load to switch off the database load for unit tests
     * @return array the filled array used to create the user export json
     */
    function export_json(export_type_list|array $exp_typ = [], bool $do_load = true): array
    {
        $vars = parent::export_json($exp_typ, $do_load);

        // add the numeric value itself
        $vars[json_fields::NUMBER] = $this->get_value();

        return $vars;
    }


    /*
     * info
     */

    /**
     * Create an object where only the vars are set
     * where the var of this object differs from the var of the given object.
     *
     * @param value|sandbox_multi|db_object_multi $std_obj the norm object as saved in the database
     * @param value|sandbox_multi|db_object_multi $result empty clone of the target user object
     * @return value|sandbox_multi|db_object_multi the object where only the vars are set that are changed compared to the given $obj
     */
    function delta(
        value|sandbox_multi|db_object_multi $std_obj,
        value|sandbox_multi|db_object_multi $result
    ): value|sandbox_multi|db_object_multi
    {
        parent::delta($std_obj, $result);
        if ($std_obj->number !== $this->number) {
            $result->number = $this->number;
        }
        return $result;
    }


    /*
     * modify
     */

    /**
     * fill this sandbox object based on the given object
     * if the given description is not set (null) the description is not removed
     * if the given description is an empty string (not null), the description is removed
     *
     * @param value|sandbox_multi|db_object_multi $obj sandbox object with the values that should be updated e.g. based on the import
     * @param user $usr_req the user who has requested the fill
     * @return user_message a warning in case of a conflict e.g. due to a missing change time
     */
    function fill(value|sandbox_multi|db_object_multi $obj, user $usr_req): user_message
    {
        $usr_msg = parent::fill($obj, $usr_req);
        if ($this->number === null and $obj->number != null) {
            $this->number = $obj->number;
        }
        return $usr_msg;
    }


    /*
     * sql helper
     */

    public function sql_field_type(): sql_field_type
    {
        return sql_field_type::NUMERIC_FLOAT;
    }

}