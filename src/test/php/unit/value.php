<?php

/*

  test/unit/value.php - unit testing of the VALUE functions
  -------------------
  

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

use api\phrase_group_api;
use api\value_api;

class value_unit_tests
{

    function run(testing $t): void
    {

        global $usr;

        // init
        $db_con = new sql_db();
        $t->name = 'value->';
        $t->resource_path = 'db/value/';
        $json_file = 'unit/value/speed_of_light.json';
        $usr->set_id(1);

        $t->header('Unit tests of the value class (src/main/php/model/value/value.php)');


        $t->subheader('SQL user sandbox statement tests');

        $val = new value($usr);
        $t->assert_load_sql_id($db_con, $val);
        $this->assert_load_sql_grp($t, $db_con, $val);
        $this->assert_load_sql_grp_and_time($t, $db_con, $val);


        $t->subheader('Database query creation tests');

        // sql to load a user specific value by phrase group id
        $val->reset($usr);
        $val->grp->set_id(2);
        //$t->assert_load_sql_obj_vars($db_con, $val);

        // sql to load a user specific value by phrase list
        $val->reset($usr);
        $val->phr_lst = (new phrase_list_unit_tests)->get_phrase_list();
        $t->assert_load_sql_obj_vars($db_con, $val);

        // ... and the related default value
        $t->assert_load_standard_sql($db_con, $val);

        // ... and to check if any user has uses another than the default value
        $val->set_id(1);
        $t->assert_not_changed_sql($db_con, $val);
        $t->assert_user_config_sql($db_con, $val);


        $t->subheader('Im- and Export tests');

        $t->assert_json(new value($usr), $json_file);


        $t->subheader('Convert and API unit tests');

        // casting API
        $grp = new phrase_group($usr, 1,  array(phrase_group_api::TN_READ));
        $val = new value($usr, 1, round(value_api::TV_READ, 13), $grp);
        $t->assert_api($val);

        // casting figure
        $val = new value($usr);
        $val->set_number(value_api::TV_PCT);
        $fig = $val->figure();
        $t->assert($t->name . ' get figure',$fig->number, $val->number());


        $t->header('Unit tests of the value time series class (src/main/php/model/value/value_time_series.php)');

        $t->subheader('Database query creation tests');

        // sql to load a user specific time series by id
        $vts = new value_time_series($usr);
        $vts->set_id(1);
        $t->assert_load_sql_obj_vars($db_con, $vts);

        // ... and the related default time series
        $t->assert_load_standard_sql($db_con, $vts);

        // sql to load a user specific time series by phrase group id
        $vts->reset($usr);
        $vts->grp->set_id(2);
        $t->assert_load_sql_obj_vars($db_con, $vts);

    }

    /**
     * similar to assert_load_sql of the testing class but select a value by the phrase group
     *
     * @param testing $t the testing object with the error counter
     * @param sql_db $db_con does not need to be connected to a real database
     * @param object $usr_obj the user sandbox object e.g. a verb
     * @return bool true if all tests are fine
     */
    function assert_load_sql_grp(testing $t, sql_db $db_con, object $usr_obj): bool
    {
        global $usr;

        $phr_grp = new phrase_group($usr);
        $phr_grp->set_id(1);

        // check the PostgreSQL query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $usr_obj->load_sql_by_grp($db_con, $phr_grp, $usr_obj::class);
        $result = $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $usr_obj->load_sql_by_grp($db_con, $phr_grp, $usr_obj::class);
            $result = $t->assert_qp($qp, $db_con->db_type);
        }
        return $result;
    }

    /**
     * similar to assert_load_sql_grp but select a value by the phrase group and time phrase
     *
     * @param testing $t the testing object with the error counter
     * @param sql_db $db_con does not need to be connected to a real database
     * @param object $usr_obj the user sandbox object e.g. a verb
     * @return bool true if all tests are fine
     */
    function assert_load_sql_grp_and_time(testing $t, sql_db $db_con, object $usr_obj): bool
    {
        global $usr;

        $phr_grp = new phrase_group($usr);
        $phr_grp->set_id(1);
        $time_phr = new phrase($usr);
        $time_phr->set_id(2);

        // check the PostgreSQL query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $usr_obj->load_sql_by_grp_and_time($db_con, $phr_grp, $time_phr, $usr_obj::class);
        $result = $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $usr_obj->load_sql_by_grp_and_time($db_con, $phr_grp, $time_phr, $usr_obj::class);
            $result = $t->assert_qp($qp, $db_con->db_type);
        }
        return $result;
    }

}