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

include_once DB_PATH . 'sql.php';
include_once MODEL_VALUE_PATH . 'value_time_series.php';
include_once MODEL_VALUE_PATH . 'value_obj.php';

use cfg\db\sql;
use cfg\db\sql_creator;
use cfg\db\sql_db;
use cfg\db\sql_type;
use cfg\group\group;
use cfg\sandbox\sandbox_multi;
use cfg\sandbox\sandbox_value;
use cfg\value\value;
use cfg\value\value_geo;
use cfg\value\value_obj;
use cfg\value\value_text;
use cfg\value\value_time;
use cfg\value\value_time_series;
use DateTime;
use html\value\value as value_dsp;
use shared\const\values;
use shared\const\views;
use shared\types\api_type;
use test\test_cleanup;

class value_tests
{

    function run(test_cleanup $t): void
    {

        global $usr;
        global $usr_sys;

        // init
        $db_con = new sql_db();
        $sc = new sql_creator();
        $t->name = 'value->';
        $t->resource_path = 'db/value/';

        // start the test section (ts)
        $ts = 'unit value ';
        $t->header($ts);

        $t->subheader($ts . 'value object selection');
        $test_name = 'create a numeric value object';
        $val = (new value_obj())->get($usr, values::PI_LONG);
        $t->assert($test_name, $val::class, value::class);
        $test_name = 'create a time value object';
        $val = (new value_obj())->get($usr, (new DateTime(values::TIME)));
        $t->assert($test_name, $val::class, value_time::class);
        $test_name = 'create a text value object';
        $val = (new value_obj())->get($usr, values::TEXT);
        $t->assert($test_name, $val::class, value_text::class);
        $test_name = 'create a geolocation value object';
        $val = (new value_obj())->get($usr, values::GEO);
        $t->assert($test_name, $val::class, value_geo::class);

        $t->subheader($ts . 'sql setup');
        $val = $t->value(); // one value object creates all tables (e.g. prime, big, time, text and geo)
        $t->assert_sql_table_create($val);
        $t->assert_sql_index_create($val);
        $t->assert_sql_foreign_key_create($val);

        $t->subheader($ts . 'sql read');
        $val = $t->value();
        $val_16 = $t->value_16();
        $val_txt = $t->text_value();
        $this->assert_sql_by_grp($t, $db_con, $val, $t->group_prime_3());
        $this->assert_sql_by_grp($t, $db_con, $val, $t->group_16());
        $this->assert_sql_by_grp($t, $db_con, $val, $t->group_17_plus());
        $this->assert_sql_by_grp($t, $db_con, $val_txt, $t->group_pod_url());
        $t->assert_sql_by_id($sc, $val_16);

        $t->subheader($ts . 'sql read default and user changes');
        $val = $t->value();
        $val_3 = $t->value_prime_3();
        $val_16 = $t->value_16();
        $val_17 = $t->value_17_plus();
        $val_txt = $t->text_value();
        $val_txt_4 = $t->text_value();
        $t->assert_sql_not_changed($sc, $val_3);
        $t->assert_sql_not_changed($sc, $val_17);
        $t->assert_sql_user_changes($sc, $val_3);
        $t->assert_sql_user_changes($sc, $val_17);
        $t->assert_sql_user_changes($sc, $val_txt_4);
        $t->assert_sql_changer($sc, $val_3);
        $t->assert_sql_changer($sc, $val_17);
        $t->assert_sql_median_user($sc, $val_3);
        $t->assert_sql_median_user($sc, $val_16);
        $t->assert_sql_standard($sc, $val);
        $t->assert_sql_standard($sc, $val_16);
        $t->assert_sql_standard($sc, $val_17);
        $t->assert_sql_standard($sc, $val_txt);

        // TODO activate db write
        $t->subheader($ts . 'sql write insert');
        $val = $t->value();
        $db_val = $val->cloned(values::SAMPLE_FLOAT);
        $val_upd = $val->updated();
        $val_0 = $t->value_zero();
        $val_3 = $t->value_prime_3();
        $db_val_3 = $val_3->cloned(values::SAMPLE_FLOAT);
        $db_val_3_share = $t->value_shared($val_3);
        $val_4 = $t->value_prime_max();
        $val_main = $t->value_main();
        $db_val_main_share = $t->value_shared($val_3);
        $val_16 = $t->value_16();
        $db_val_16 = $val_16->cloned(values::SAMPLE_FLOAT);
        $val_fill = $t->value_16_filled();
        $val_17 = $t->value_17_plus();
        $db_val_17 = $val_17->cloned(values::SAMPLE_FLOAT);
        $val_txt = $t->text_value();
        $db_val_txt = $val_txt->cloned(values::DB_TEXT);
        $t->assert_sql_insert($sc, $val_0, [sql_type::USER]);
        $t->assert_sql_insert($sc, $val);
        $t->assert_sql_insert($sc, $val, [sql_type::LOG]);
        $t->assert_sql_insert($sc, $val, [sql_type::LOG, sql_type::USER]);
        $t->assert_sql_insert($sc, $val, [sql_type::LOG, sql_type::STANDARD]);
        $t->assert_sql_insert($sc, $val_3);
        $t->assert_sql_insert($sc, $val_3, [sql_type::USER]);
        $t->assert_sql_insert($sc, $val_3, [sql_type::LOG, sql_type::USER]);
        $t->assert_sql_insert($sc, $val_4);
        $t->assert_sql_insert($sc, $val_4, [sql_type::USER]);
        $t->assert_sql_insert($sc, $val_16);
        $t->assert_sql_insert($sc, $val_16, [sql_type::LOG]);
        $t->assert_sql_insert($sc, $val_16, [sql_type::USER]);
        $t->assert_sql_insert($sc, $val_fill);
        $t->assert_sql_insert($sc, $val_fill, [sql_type::LOG]);
        $t->assert_sql_insert($sc, $val_17);
        $t->assert_sql_insert($sc, $val_17, [sql_type::USER]);
        $t->assert_sql_insert($sc, $val_txt);
        $t->assert_sql_insert($sc, $val_txt, [sql_type::USER]);
        $t->assert_sql_insert($sc, $val_txt, [sql_type::LOG, sql_type::USER]);

        // TODO for 1 given phrase fill the others with 0 because usually only one value is expected to be changed
        // TODO for update fill the missing phrase id with zeros because only one row should be updated
        // TODO add test to change owner of the normal (not user specific) value
        // TODO add tests for time, text and geo values
        $t->subheader($ts . 'sql write update');
        $t->assert_sql_update($sc, $val, $db_val);
        $t->assert_sql_update($sc, $val, $db_val, [sql_type::USER]);
        $t->assert_sql_update($sc, $val, $db_val, [sql_type::LOG]);
        $t->assert_sql_update($sc, $val, $db_val, [sql_type::LOG, sql_type::USER]);
        $t->assert_sql_update($sc, $val_3, $db_val_3);
        $t->assert_sql_update($sc, $val_3, $db_val_3, [sql_type::USER]);
        $t->assert_sql_update($sc, $val_3, $db_val_3_share, [sql_type::LOG]);
        $t->assert_sql_update($sc, $val_main, $db_val_main_share, [sql_type::LOG]);
        $t->assert_sql_update($sc, $val_16, $db_val_16);
        $t->assert_sql_update($sc, $val_16, $db_val_16, [sql_type::LOG]);
        $t->assert_sql_update($sc, $val_16, $db_val_16, [sql_type::LOG, sql_type::USER]);
        $t->assert_sql_update($sc, $val_16, $val_fill, [sql_type::LOG]);
        $t->assert_sql_update($sc, $val_16, $val_fill, [sql_type::LOG, sql_type::USER]);
        $t->assert_sql_update($sc, $val_17, $db_val_17);
        $t->assert_sql_update($sc, $val_txt, $db_val_txt);
        $t->assert_sql_update($sc, $val_txt, $db_val_txt, [sql_type::LOG]);
        // update only the last_update date to trigger calculation
        $this->assert_sql_update_trigger($t, $db_con, $val_upd, $val);

        $t->subheader($ts . 'sql write delete');
        $t->assert_sql_delete($sc, $val);
        $t->assert_sql_delete($sc, $val, [sql_type::USER]);
        $t->assert_sql_delete($sc, $val, [sql_type::LOG]);
        $t->assert_sql_delete($sc, $val, [sql_type::LOG, sql_type::USER]);
        $t->assert_sql_delete($sc, $val, [sql_type::USER, sql_type::EXCLUDE]);
        $t->assert_sql_delete($sc, $val_16);
        $t->assert_sql_delete($sc, $val_16, [sql_type::USER]);
        $t->assert_sql_delete($sc, $val_txt, [sql_type::LOG]);


        $t->subheader($ts . 'database query creation');

        // sql to load a user specific value by phrase group id
        $val->reset($usr);
        $val->grp()->set_id(2);
        //$t->assert_load_sql_obj_vars($db_con, $val);

        $t->subheader($ts . 'value base object handling');
        $val = $t->value_16_filled();
        $t->assert_reset($val);

        $t->subheader($ts . 'value im- and export');
        $t->assert_ex_and_import($t->value(), $usr_sys);
        $t->assert_ex_and_import($t->value_16_filled(), $usr_sys);
        $json_file = 'unit/value/speed_of_light.json';
        $t->assert_json_file(new value($usr), $json_file);


        $t->subheader($ts . 'html frontend');

        $val = $t->value();
        // TODO add class field to api message
        $t->assert_api_to_dsp($val, new value_dsp());

        // TODO move to ui tests
        $val_dsp = new value_dsp($val->api_json([api_type::INCL_PHRASES]));
        $t->assert('value edit link', $val_dsp->value_edit(), '<a href="/http/view.php?m=value_edit&id=32819" title="3.14">3.14</a>');

        $t->subheader($ts . 'convert and api');

        // casting API
        $grp = $t->group();
        $val = new value($usr, round(values::PI_LONG, 13), $grp);
        $t->assert_api($val, 'value_without_phrases');
        $t->assert_api($val, 'value_with_phrases', [api_type::INCL_PHRASES]);
        $val = $t->time_value();
        $t->assert_api($val);
        $t->assert_api($val, 'value_with_phrases', [api_type::INCL_PHRASES]);
        $val = $t->text_value();
        $t->assert_api($val);
        $t->assert_api($val, 'value_with_phrases', [api_type::INCL_PHRASES]);
        $val = $t->geo_value();
        $t->assert_api($val);
        $t->assert_api($val, 'value_with_phrases', [api_type::INCL_PHRASES]);

        // casting figure
        $val = new value($usr);
        $val->set_number(values::SAMPLE_PCT);
        $fig = $val->figure();
        $t->assert($t->name . ' get figure', $fig->number(), $val->number());

        // start the test section (ts)
        $ts = 'unit value time series ';
        $t->header($ts);

        $t->subheader($ts . 'database query creation');

        // sql to load a user specific time series by id
        $vts = new value_time_series($usr);
        $vts->set_grp($t->group_16());
        $t->assert_sql_by_id($sc, $vts);

        // ... and the related default time series
        // TODO Prio 2 activate
        //$t->assert_sql_standard($sc, $vts);

        // sql to load a user specific time series by phrase group id
        $vts->reset($usr);
        $vts->grp()->set_id(2);
        $this->assert_sql_by_grp($t, $db_con, $vts, $vts->grp());

        $t->subheader($ts . 'data sql setup');
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
        $sc->reset(sql_db::POSTGRES);
        $qp = $usr_obj->load_sql_by_grp($sc, $grp, $usr_obj::class);
        $result = $t->assert_qp($qp, $sc->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $sc->reset(sql_db::MYSQL);
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
        $fields = array(sandbox_multi::FLD_LAST_UPDATE);
        $values = array(sql::NOW);
        // check the Postgres query syntax
        $sc->reset(sql_db::POSTGRES);
        $qp = $val->sql_update($sc, $db_val);
        $result = $t->assert_qp($qp, $sc->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $sc->reset(sql_db::MYSQL);
            $qp = $val->sql_update($sc, $db_val);
            $result = $t->assert_qp($qp, $sc->db_type);
        }
        return $result;
    }

}