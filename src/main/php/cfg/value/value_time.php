<?php

/*

    model/value/value_time.php - the main text value object using the prime, norm and big value keys
    --------------------------


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
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_field_default.php';
include_once paths::DB . 'sql_field_type.php';
include_once paths::EXPORT . 'export_type_list.php';
include_once paths::MODEL_GROUP . 'group.php';
include_once paths::MODEL_LOG . 'change_value_time.php';
include_once paths::MODEL_LOG . 'change_values_time_prime.php';
include_once paths::MODEL_LOG . 'change_values_time_norm.php';
include_once paths::MODEL_LOG . 'change_values_time_big.php';
include_once paths::MODEL_REF . 'source_db.php';
include_once paths::MODEL_SANDBOX . 'sandbox.php';
include_once paths::MODEL_SANDBOX . 'sandbox_multi.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';
include_once paths::SHARED . 'json_fields.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_default;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_type;
use Zukunft\ZukunftCom\main\php\cfg\export\export_type_list;
use Zukunft\ZukunftCom\main\php\cfg\group\group;
use Zukunft\ZukunftCom\main\php\cfg\log\change_value_time;
use Zukunft\ZukunftCom\main\php\cfg\log\change_values_time_prime;
use Zukunft\ZukunftCom\main\php\cfg\log\change_values_time_norm;
use Zukunft\ZukunftCom\main\php\cfg\log\change_values_time_big;
use Zukunft\ZukunftCom\main\php\cfg\ref\source_db;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_multi;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use DateTime;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;

class value_time extends value_base
{

    /*
     * db const
     */

    // object specific database and JSON object field names
    const string FLD_VALUE = 'time_value';
    const string FLD_COM = 'the time given by the user';
    const string FLD_USER_COM = 'the user-specific time change';

    // database field with the sql type specification
    const array FLD_ALL_VALUE = array(
        [self::FLD_VALUE, sql_field_type::TIME, sql_field_default::NOT_NULL, '', '', self::FLD_COM],
    );
    const array FLD_ALL_VALUE_USER = array(
        [self::FLD_VALUE, sql_field_type::TIME, sql_field_default::NULL, '', '', self::FLD_USER_COM],
    );

    const array FLD_NAMES_STD = array(
        self::FLD_VALUE,
        source_db::FLD_ID,
    );

    // list of the user-specific database field names for time values
    const array FLD_NAMES_USR = array(
        self::FLD_VALUE,
    );
    // list of the user-specific database field names for time values
    const array FLD_NAMES_NUM_USR = array(
        self::FLD_VALUE,
        source_db::FLD_ID,
        sandbox_multi::FLD_LAST_UPDATE,
        sql_db::FLD_EXCLUDED,
        sandbox::FLD_PROTECT
    );
    // all database field names excluding the id used to identify if there are some user-specific changes
    const array ALL_SANDBOX_FLD_NAMES = array(
        self::FLD_VALUE,
        source_db::FLD_ID,
        sandbox_multi::FLD_LAST_UPDATE,
        sql_db::FLD_EXCLUDED,
        sandbox::FLD_PROTECT
    );



    /*
     * object vars
     */

    // database related variables
    private ?Datetime $time_val = null;


    /*
     * construct and map
     */

    /**
     * set the user, which is needed in all cases and the main vars with the object creation
     * @param user $usr the user who requested to see this value
     * @param Datetime|null $val the time value that should be set on creation
     * @param group|null $grp the phrases for unique identification of this value
     */
    function __construct(
        user          $usr,
        Datetime|null $val = null,
        ?group        $grp = null
    )
    {
        parent::__construct($usr, $val, $grp);
    }


    /*
     * set and get
     */

    /**
     * overwrite the sandbox_value set_value() function to set the geolocation value
     * @param float|DateTime|string|null $val
     * @return void
     */
    function set_value(float|DateTime|string|null $val): void
    {
        $this->set_time_value($val);
    }

    /**
     * overwrite the sandbox_value value() function to return the DateTime value
     * @return DateTime|null the DateTime value of this object
     */
    function get_value(): DateTime|null
    {
        return $this->time_val;
    }

    /**
     * except only DateTime values
     * @param DateTime|null $time_val a DateTime value
     * @return void
     */
    protected function set_time_value(?DateTime $time_val): void
    {
        $this->time_val = $time_val;
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

        // add the datetime value itself
        $vars[json_fields::TIME_VALUE] = $this->get_value();

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

        // add the datetime value itself
        $vars[json_fields::TIME_VALUE] = $this->get_value();

        return $vars;
    }


    /*
     * log
     */

    /**
     * @return change_value_time the object that is used to log the user changes
     */
    function log_object(): change_value_time
    {
        if ($this->is_prime()) {
            return new change_values_time_prime($this->get_user());
        } elseif ($this->is_big()) {
            return new change_values_time_big($this->get_user());
        } else {
            return new change_values_time_norm($this->get_user());
        }
    }


    /*
     * sql helper
     */

    public function sql_field_type(): sql_field_type
    {
        return sql_field_type::TIME;
    }

}