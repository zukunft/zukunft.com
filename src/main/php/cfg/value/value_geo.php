<?php

/*

    model/value/value_geo.php - the main geolocation value object using the prime, norm and big value keys
    -------------------------


    The main sections of this object are
    - db const:          const for the database link
    - object vars:       the variables of this word object
    - construct and map: including the mapping of the db row to this word object
    - set and get:       to capsule the vars from unexpected changes
    - api:               create an api array for the frontend and set the vars based on a frontend api message
    - im- and export:    create an export object and set the vars from an import object
    - log:               write the changes to the log
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

namespace cfg\value;

use cfg\const\paths;

include_once paths::MODEL_VALUE . 'value_base.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_field_default.php';
include_once paths::DB . 'sql_field_type.php';
include_once paths::MODEL_GROUP . 'group.php';
include_once paths::MODEL_LOG . 'change_value_geo.php';
include_once paths::MODEL_LOG . 'change_values_geo_prime.php';
include_once paths::MODEL_LOG . 'change_values_geo_norm.php';
include_once paths::MODEL_LOG . 'change_values_geo_big.php';
include_once paths::MODEL_REF . 'source.php';
include_once paths::MODEL_REF . 'source_db.php';
include_once paths::MODEL_SANDBOX . 'sandbox.php';
include_once paths::MODEL_SANDBOX . 'sandbox_multi.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';
include_once paths::SHARED . 'json_fields.php';

use cfg\db\sql_db;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
use cfg\group\group;
use cfg\log\change_value_geo;
use cfg\log\change_values_geo_prime;
use cfg\log\change_values_geo_norm;
use cfg\log\change_values_geo_big;
use cfg\sandbox\sandbox;
use cfg\ref\source_db;
use cfg\sandbox\sandbox_multi;
use cfg\user\user;
use DateTime;
use shared\json_fields;
use shared\types\api_type_list;

class value_geo extends value_base
{

    /*
     * db const
     */

    // object specific database and JSON object field names
    const FLD_VALUE = 'geo_value';
    const FLD_COM = 'the geolocation given by the user';
    const FLD_USER_COM = 'the user specific geolocation change';

    // database field with the sql type specification
    const FLD_ALL_VALUE = array(
        [self::FLD_VALUE, sql_field_type::GEO, sql_field_default::NOT_NULL, '', '', self::FLD_COM],
    );
    const FLD_ALL_VALUE_USER = array(
        [self::FLD_VALUE, sql_field_type::GEO, sql_field_default::NULL, '', '', self::FLD_USER_COM],
    );

    const FLD_NAMES_STD = array(
        self::FLD_VALUE,
        source_db::FLD_ID,
    );

    // list of the user specific database field names for geo values
    const FLD_NAMES_USR = array(
        self::FLD_VALUE,
    );
    // list of the user specific numeric database field names
    const FLD_NAMES_NUM_USR = array(
        source_db::FLD_ID,
        sandbox_multi::FLD_LAST_UPDATE,
        sql_db::FLD_EXCLUDED,
        sandbox::FLD_SHARE,
        sandbox::FLD_PROTECT
    );
    // all database field names excluding the id used to identify if there are some user specific changes
    const ALL_SANDBOX_FLD_NAMES = array(
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
    private ?string $geo_val = null;


    /*
     * construct and map
     */

    /**
     * set the user, which is needed in all cases and the main vars with the object creation
     * @param user $usr the user who requested to see this value
     * @param string|null $val the geolocation value that should be set on creation
     * @param group|null $grp the phrases for unique identification of this value
     */
    function __construct(
        user        $usr,
        string|null $val = null,
        ?group      $grp = null
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
        $this->set_geo_value($val);
    }

    /**
     * overwrite the sandbox_value value() function to return the geolocation value
     * @return string|null the geolocation string
     */
    function value(): string|null
    {
        return $this->geo_val;
    }

    /**
     * accept only geolocation strings as value
     * @param string|null $geo_val a geolocation string
     * @return void
     */
    protected function set_geo_value(?string $geo_val): void
    {
        $this->geo_val = $geo_val;
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

        // add the geolocation string itself
        $vars[json_fields::GEO_VALUE] = $this->value();

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
     * @param bool $do_load to switch off the database load for unit tests
     * @return array the filled array used to create the user export json
     */
    function export_json(bool $do_load = true): array
    {
        $vars = parent::export_json($do_load);

        // add the geolocation value itself
        $vars[json_fields::GEO_VALUE] = $this->value();

        return $vars;
    }


    /*
     * log
     */

    /**
     * @return change_value_geo the object that is used to log the user changes
     */
    function log_object(): change_value_geo
    {
        if ($this->is_prime()) {
            return new change_values_geo_prime($this->user());
        } elseif ($this->is_big()) {
            return new change_values_geo_big($this->user());
        } else {
            return new change_values_geo_norm($this->user());
        }
    }


    /*
     * sql helper
     */

    public function sql_field_type(): sql_field_type
    {
        return sql_field_type::POINT;
    }

}