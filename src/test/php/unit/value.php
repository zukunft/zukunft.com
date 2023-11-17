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

namespace test;

include_once MODEL_VALUE_PATH . 'value_time_series.php';

use api\phrase\group as group_api;
use api\value\value as value_api;
use cfg\group\group;
use cfg\db\sql_db;
use cfg\value\value;
use cfg\value\value_time_series;
use html\value\value as value_dsp;

class value_unit_tests
{

    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $db_con = new sql_db();
        $t->name = 'value->';
        $t->resource_path = 'db/value/';
        $json_file = 'unit/value/speed_of_light.json';
        $usr->set_id(1);


        $t->header('Unit tests of the value class (src/main/php/model/value/value.php)');

        $t->subheader('SQL statements - setup');
        $val = $t->dummy_value();
        $t->assert_sql_table_create($db_con, $val);
        $t->assert_sql_index_create($db_con, $val);
        $t->assert_sql_foreign_key_create($db_con, $val);

        // TODO add sql insert and update tests to all db objects
        $t->subheader('SQL statements - read - for often used (prime) values');
        $val = $t->dummy_value();
        $t->assert_sql_insert($db_con, $val);
        $t->assert_sql_insert($db_con, $val, true);
        $t->assert_sql_update($db_con, $val);
        $t->assert_sql_update($db_con, $val, true);
        $this->assert_sql_by_grp($t, $db_con, $val);

        // ... and the related default value
        $t->assert_sql_standard($db_con, $val);

        // ... and to check if any user has uses another than the default value
        $t->assert_sql_not_changed($db_con, $val);
        $t->assert_sql_user_changes($db_con, $val);

        $t->subheader('for values related to up to 16 phrases');
        $val = $t->dummy_value_16();
        $t->assert_sql_insert($db_con, $val);
        $t->assert_sql_insert($db_con, $val, true);
        $t->assert_sql_update($db_con, $val);
        $t->assert_sql_by_id($db_con, $val);
        // TODO activate
        //$this->assert_sql_by_grp($t, $db_con, $val);

        // ... and the related default value
        $t->assert_sql_standard($db_con, $val);

        $t->subheader('for values related to more than 16 phrases');
        $val = $t->dummy_value_17_plus();
        $t->assert_sql_insert($db_con, $val);
        $t->assert_sql_update($db_con, $val);
        // TODO activate
        //$this->assert_sql_by_grp($t, $db_con, $val);

        // ... and the related default value
        $t->assert_sql_standard($db_con, $val);


        $t->subheader('Database query creation tests');

        // sql to load a user specific value by phrase group id
        $val->reset($usr);
        $val->grp->set_id(2);
        //$t->assert_load_sql_obj_vars($db_con, $val);

        $t->subheader('Im- and Export tests');

        $t->assert_json_file(new value($usr), $json_file);


        $t->subheader('HTML frontend unit tests');

        $val = $t->dummy_value();
        // TODO add class field to api message
        $t->assert_api_to_dsp($val, new value_dsp());


        $t->subheader('Convert and API unit tests');

        // casting API
        $grp = new group($usr, 1,  array(group_api::TN_READ));
        $val = new value($usr, round(value_api::TV_READ, 13), $grp);
        $t->assert_api($val);

        // casting figure
        $val = new value($usr);
        $val->set_number(value_api::TV_PCT);
        $fig = $val->figure();
        $t->assert($t->name . ' get figure',$fig->number(), $val->number());


        $t->header('Unit tests of the value time series class (src/main/php/model/value/value_time_series.php)');

        $t->subheader('Database query creation tests');

        // sql to load a user specific time series by id
        $vts = new value_time_series($usr);
        $t->assert_sql_by_id($db_con, $vts);

        // ... and the related default time series
        $t->assert_sql_standard($db_con, $vts);

        // sql to load a user specific time series by phrase group id
        $vts->reset($usr);
        $vts->grp->set_id(2);
        $this->assert_sql_by_grp($t, $db_con, $vts);

    }

    /**
     * similar to assert_load_sql of the testing class but select a value by the phrase group
     *
     * @param test_cleanup $t the testing object with the error counter
     * @param sql_db $db_con does not need to be connected to a real database
     * @param object $usr_obj the user sandbox object e.g. a verb
     */
    private function assert_sql_by_grp(test_cleanup $t, sql_db $db_con, object $usr_obj): void
    {
        global $usr;

        $phr_grp = new group($usr);
        $phr_grp->set_id(1);

        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $usr_obj->load_sql_by_grp($db_con->sql_creator(), $phr_grp, $usr_obj::class);
        $result = $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $usr_obj->load_sql_by_grp($db_con->sql_creator(), $phr_grp, $usr_obj::class);
            $t->assert_qp($qp, $db_con->db_type);
        }
    }

}