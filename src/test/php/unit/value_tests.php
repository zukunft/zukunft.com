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

namespace unit;

include_once MODEL_VALUE_PATH . 'value_time_series.php';

use api\phrase\group as group_api;
use api\value\value as value_api;
use api\word\word as word_api;
use cfg\db\sql;
use cfg\db\sql_type;
use cfg\group\group;
use cfg\db\sql_db;
use cfg\value\value;
use cfg\value\value_dsp_old;
use cfg\value\value_time_series;
use html\value\value as value_dsp;
use test\test_cleanup;

class value_tests
{

    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $db_con = new sql_db();
        $sc = new sql();
        $t->name = 'value->';
        $t->resource_path = 'db/value/';
        $json_file = 'unit/value/speed_of_light.json';
        $usr->set_id(1);


        $t->header('value unit tests');

        $t->subheader('value sql setup');
        $val = $t->value(); // one value object creates all tables (e.g. prime, big, time, text and geo)
        $t->assert_sql_table_create($val);
        $t->assert_sql_index_create($val);
        $t->assert_sql_foreign_key_create($val);

        $t->subheader('value sql read');
        $val = $t->value();
        $this->assert_sql_by_grp($t, $db_con, $val, $t->group_prime_3());
        $this->assert_sql_by_grp($t, $db_con, $val, $t->group_16());
        $this->assert_sql_by_grp($t, $db_con, $val, $t->group_17_plus());

        $t->subheader('value sql read default and user changes');
        $val = $t->value_prime_3();
        $t->assert_sql_not_changed($sc, $val);
        $t->assert_sql_user_changes($sc, $val);
        $t->assert_sql_changer($sc, $val);
        $t->assert_sql_median_user($sc, $val);
        $val = $t->value_16();
        $t->assert_sql_median_user($sc, $val);
        $val = $t->value_17_plus();
        $t->assert_sql_not_changed($sc, $val);
        $t->assert_sql_user_changes($sc, $val);
        $t->assert_sql_changer($sc, $val);

        // ... and to check if any user has uses another than the default value

        // ... and the related default value
        $val = $t->value();
        $t->assert_sql_standard($sc, $val);

        // TODO sort the test by query type and not value type
        // TODO add tests with log
        // TODO add sql insert and update tests to all db objects
        $t->subheader('SQL statements - for often used (prime) values');
        $t->assert_sql_insert($sc, $t->value());
        $t->assert_sql_insert($sc, $t->value_zero(), [sql_type::USER]);
        $t->assert_sql_insert($sc, $t->value_prime_3());
        $t->assert_sql_insert($sc, $t->value_prime_3(), [sql_type::USER]);
        $t->assert_sql_insert($sc, $t->value_prime_max());
        $t->assert_sql_insert($sc, $t->value_prime_max(), [sql_type::USER]);
        // TODO for 1 given phrase fill the others with 0 because usually only one value is expected to be changed
        // TODO for update fill the missing phrase id with zeros because only one row should be updated
        // TODO add test to change owner of the normal (not user specific) value
        $val = $t->value();
        $db_val = $val->cloned(value_api::TV_FLOAT);
        $t->assert_sql_update($sc, $val, $db_val);
        $t->assert_sql_update($sc, $val, $db_val, [sql_type::USER]);
        $val_prime = $t->value_prime_3();
        $db_val_prime = $val_prime->cloned(value_api::TV_FLOAT);
        $t->assert_sql_update($sc, $val_prime, $db_val_prime);
        $t->assert_sql_update($sc, $val_prime, $db_val_prime, [sql_type::USER]);
        // update only the last_update date to trigger recalc
        $val = $t->value();
        $val_upd = $val->updated();
        $this->assert_sql_update_trigger($t, $db_con, $val_upd, $val);
        $t->assert_sql_delete($sc, $val);
        $t->assert_sql_delete($sc, $val, [sql_type::USER]);
        $t->assert_sql_delete($sc, $val, [sql_type::USER, sql_type::EXCLUDE]);




        $t->subheader('SQL statements - for values related to up to 16 phrases');
        $val = $t->value_16();
        // TODO insert value does not need to return the id because this is given by the group id
        $t->assert_sql_insert($sc, $val);
        $t->assert_sql_insert($sc, $val, [sql_type::USER]);
        $db_val = $val->cloned(value_api::TV_FLOAT);
        $t->assert_sql_update($sc, $val, $db_val);
        $t->assert_sql_delete($sc, $val);
        $t->assert_sql_delete($sc, $val, [sql_type::USER]);
        $t->assert_sql_by_id($sc, $val);
        // TODO activate Prio 2
        //$this->assert_sql_by_grp($t, $db_con, $val);

        // ... and the related default value
        $t->assert_sql_standard($sc, $val);

        $t->subheader('SQL statements - for values related to more than 16 phrases');
        $val = $t->value_17_plus();
        $db_val = $val->cloned(value_api::TV_FLOAT);
        $t->assert_sql_insert($sc, $val);
        $t->assert_sql_update($sc, $val, $db_val);
        // TODO activate Prio 2
        //$this->assert_sql_by_grp($t, $db_con, $val);

        // ... and the related default value
        $t->assert_sql_standard($sc, $val);

        // ... and to check if any user has uses another than the default value
        // TODO prio 1 activate
        //$t->assert_sql_not_changed($db_con, $val);
        //$t->assert_sql_user_changes($sc, $val);
        //$t->assert_sql_changer($sc, $val);


        $t->subheader('Database query creation tests');

        // sql to load a user specific value by phrase group id
        $val->reset($usr);
        $val->grp->set_id(2);
        //$t->assert_load_sql_obj_vars($db_con, $val);

        $t->subheader('Im- and Export tests');

        $t->assert_json_file(new value($usr), $json_file);


        $t->subheader('HTML frontend unit tests');

        $val = $t->value();
        // TODO add class field to api message
        $t->assert_api_to_dsp($val, new value_dsp());


        $t->subheader('Convert and API unit tests');

        // casting API
        $grp = new group($usr, 1, array(group_api::TN_READ));
        $val = new value($usr, round(value_api::TV_READ, 13), $grp);
        $t->assert_api($val);

        // casting figure
        $val = new value($usr);
        $val->set_number(value_api::TV_PCT);
        $fig = $val->figure();
        $t->assert($t->name . ' get figure', $fig->number(), $val->number());


        $t->header('Unit tests of the value time series class (src/main/php/model/value/value_time_series.php)');

        $t->subheader('Database query creation tests');

        // sql to load a user specific time series by id
        $vts = new value_time_series($usr);
        $vts->set_grp($t->group_16());
        $t->assert_sql_by_id($sc, $vts);

        // ... and the related default time series
        // TODO Prio 2 activate
        //$t->assert_sql_standard($sc, $vts);

        // sql to load a user specific time series by phrase group id
        $vts->reset($usr);
        $vts->grp->set_id(2);
        $this->assert_sql_by_grp($t, $db_con, $vts, $vts->grp);

        $t->subheader('Value time series data SQL setup statements');
        $tsn = $t->value_ts_data();
        $t->assert_sql_table_create($tsn);
        $t->assert_sql_index_create($tsn);
        // TODO activate
        //$t->assert_sql_foreign_key_create($tsn);

    }

    /**
     * similar to assert_load_sql of the testing class but select a value by the phrase group
     *
     * @param test_cleanup $t the testing object with the error counter
     * @param sql_db $db_con does not need to be connected to a real database
     * @param object $usr_obj the user sandbox object e.g. a verb
     */
    private function assert_sql_by_grp(test_cleanup $t, sql_db $db_con, object $usr_obj, group $grp): void
    {
        global $usr;

        $sc = $db_con->sql_creator();

        // check the Postgres query syntax
        $sc->db_type = sql_db::POSTGRES;
        $qp = $usr_obj->load_sql_by_grp($sc, $grp, $usr_obj::class);
        $result = $t->assert_qp($qp, $sc->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $sc->db_type = sql_db::MYSQL;
            $qp = $usr_obj->load_sql_by_grp($sc, $grp, $usr_obj::class);
            $t->assert_qp($qp, $sc->db_type);
        }
    }

    /**
     * check the SQL statement to set the update trigger for a value
     * for all allowed SQL database dialects
     *
     * @param test_cleanup $t the testing object with the error counter
     * @param sql_db $db_con does not need to be connected to a real database
     * @param value $val the value without a last update timestamp
     * @param value $db_val the value as it is expected to be in the database
     * @return bool true if all tests are fine
     */
    function assert_sql_update_trigger(test_cleanup $t, sql_db $db_con, value $val, value $db_val): bool
    {
        $sc = $db_con->sql_creator();
        $fields = array(value::FLD_LAST_UPDATE);
        $values = array(sql::NOW);
        // check the Postgres query syntax
        $sc->db_type = sql_db::POSTGRES;
        $qp = $val->sql_update($sc, $db_val);
        $result = $t->assert_qp($qp, $sc->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $sc->db_type = sql_db::MYSQL;
            $qp = $val->sql_update($sc, $db_val);
            $result = $t->assert_qp($qp, $sc->db_type);
        }
        return $result;
    }

}