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

namespace Zukunft\ZukunftCom\test\php\unit;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::DB . 'sql.php';
include_once paths::MODEL_VALUE . 'value_time_series.php';
include_once paths::MODEL_VALUE . 'value_obj.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type;
use Zukunft\ZukunftCom\main\php\cfg\group\group;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_multi;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_value;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\value\value;
use Zukunft\ZukunftCom\main\php\cfg\value\value_geo;
use Zukunft\ZukunftCom\main\php\cfg\value\value_obj;
use Zukunft\ZukunftCom\main\php\cfg\value\value_text;
use Zukunft\ZukunftCom\main\php\cfg\value\value_time;
use Zukunft\ZukunftCom\main\php\cfg\value\value_time_series;
use DateTime;
use Zukunft\ZukunftCom\main\php\shared\api;
use Zukunft\ZukunftCom\main\php\shared\const\groups;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\value\value as value_ui;
use Zukunft\ZukunftCom\main\php\shared\const\values;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\test\php\create\test_groups;
use Zukunft\ZukunftCom\test\php\create\test_phrases;
use Zukunft\ZukunftCom\test\php\create\test_terms;
use Zukunft\ZukunftCom\test\php\create\test_values;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;
use Zukunft\ZukunftCom\test\php\utils\test_lib;

class value_tests
{

    function run(test_cleanup $t): void
    {

        global $usr;
        global $usr_sys;

        // init
        $usr_msg = new user_message();
        $db_con = new sql_db();
        $sc = new sql_creator();
        $tl = new test_lib();
        $t_val = new test_values($t);
        $t_grp = new test_groups($t);
        $t_phr = new test_phrases($t);
        $t_trm = new test_terms($t);
        $trm_lst = $t_trm->term_list_all();
        $t->name = 'value->';
        $t->resource_path = 'db/value/';

        // start the test section (ts)
        $ts = 'unit value ';
        $t->header($ts);

        $t->subheader($ts . 'value object selection');
        $test_name = 'create a numeric value object';
        $val = new value_obj()->get($usr, values::PI_LONG);
        $t->assert($test_name, $val::class, value::class);
        $test_name = 'create a time value object';
        $val = new value_obj()->get($usr, new DateTime(values::TIME));
        $t->assert($test_name, $val::class, value_time::class);
        $test_name = 'create a text value object';
        $val = new value_obj()->get($usr, values::TEXT);
        $t->assert($test_name, $val::class, value_text::class);
        $test_name = 'create a geolocation value object';
        $val = new value_obj()->get($usr, values::GEO);
        $t->assert($test_name, $val::class, value_geo::class);

        $t->subheader($ts . 'sql setup');
        $val = $t_val->value(); // one value object creates all tables (e.g. prime, big, time, text and geo)
        $t->assert_sql_table_create($val);
        $t->assert_sql_index_create($val);
        $t->assert_sql_foreign_key_create($val);

        $t->subheader($ts . 'sql read');
        $val = $t_val->value();
        $val_16 = $t_val->value_16();
        $val_txt = $t_val->text_value();
        $this->assert_sql_by_grp($t, $db_con, $val, $t_grp->group_prime_3());
        $this->assert_sql_by_grp($t, $db_con, $val, $t_grp->group_16());
        $this->assert_sql_by_grp($t, $db_con, $val, $t_grp->group_17_plus());
        $this->assert_sql_by_grp($t, $db_con, $val_txt, $t_grp->group_pod_url());
        $t->assert_sql_by_id($sc, $val_16);

        $t->subheader($ts . 'sql read default and user changes');
        $val = $t_val->value();
        $val_3 = $t_val->value_prime_3();
        $val_16 = $t_val->value_16();
        $val_17 = $t_val->value_17_plus();
        $val_txt = $t_val->text_value();
        $val_txt_4 = $t_val->text_value();
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

        // TODO Prio 0 activate db write
        $t->subheader($ts . 'sql write insert');
        $val = $t_val->value();
        $db_val = $val->cloned(values::SAMPLE_FLOAT);
        $val_upd = $val->updated();
        $val_0 = $t_val->value_zero();
        $val_3 = $t_val->value_prime_3();
        $db_val_3 = $val_3->cloned(values::SAMPLE_FLOAT);
        $db_val_3_share = $t_val->value_shared($val_3);
        $val_4 = $t_val->value_prime_max();
        $val_main = $t_val->value_main();
        $db_val_main_share = $t_val->value_shared($val_3);
        $val_16 = $t_val->value_16();
        $db_val_16 = $val_16->cloned(values::SAMPLE_FLOAT);
        $val_fill = $t_val->value_16_filled();
        $val_17 = $t_val->value_17_plus();
        $db_val_17 = $val_17->cloned(values::SAMPLE_FLOAT);
        $val_txt = $t_val->text_value();
        $db_val_txt = $val_txt->cloned(values::DB_TEXT);
        $t->assert_sql_insert($sc, $val_0, [sql_type::USER]);
        $t->assert_sql_insert($sc, $val);
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
        $val = $t_val->value_incomplete();
        $t->assert_sql_insert_fail($sc, $val, [sql_type::LOG]);

        // TODO for 1 given phrase fill the others with 0 because usually only one value is expected to be changed
        // TODO for update fill the missing phrase id with zeros because only one row should be updated
        // TODO add test to change owner of the normal (not user-specific) value
        // TODO add tests for time, text and geo values
        $t->subheader($ts . 'sql write update');
        $val = $t_val->value();
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
        $t->assert_sql_update_owner($sc, $t->usr2, $val_3, [sql_type::LOG]);
        $t->assert_sql_update_owner($sc, $t->usr2, $val_16, [sql_type::LOG]);
        $t->assert_sql_update_owner($sc, $t->usr2, $val_17, [sql_type::LOG]);
        // update only the last_update date to trigger calculation
        $this->assert_sql_update_trigger($t, $db_con, $val_upd, $val);

        $t->subheader($ts . 'sql write delete');
        $t->assert_sql_delete($sc, $val);
        $t->assert_sql_delete($sc, $val, [sql_type::USER]);
        // is covered already by the horizontal tests
        //$t->assert_sql_delete($sc, $val, [sql_type::LOG]);
        $t->assert_sql_delete($sc, $val, [sql_type::LOG, sql_type::USER]);
        $t->assert_sql_delete($sc, $val, [sql_type::USER, sql_type::EXCLUDE]);
        $t->assert_sql_delete($sc, $val_16);
        $t->assert_sql_delete($sc, $val_16, [sql_type::USER]);
        $t->assert_sql_delete($sc, $val_txt, [sql_type::LOG]);


        $t->subheader($ts . 'database query creation');

        // sql to load a user-specific value by phrase group id
        $val->reset(true);
        $val->grp()->set_id(2);
        //$t->assert_load_sql_obj_vars($db_con, $val);

        $t->subheader($ts . 'value base object handling');
        $val = $t_val->value_16_filled();
        $t->assert_reset($val);

        $t->subheader($ts . 'value im- and export');
        $t->assert_ex_and_import($t_val->value(), $usr_sys);
        $t->assert_ex_and_import($t_val->value_16_filled(), $usr_sys);
        $json_file = 'unit/value/speed_of_light.json';
        $t->assert_json_file(new value($usr), $json_file);


        $t->subheader($ts . 'ui formatting');

        $test_case = 'show the unit after the value';
        $val = $tl->ui_value($t_val->light_speed());
        $result = $tl->text_from_html($val->with_unit_and_info());
        $target = groups::LENGTH_DEFINITION . ' ' . values::SPEED_OF_LIGHT_TXT . ' ' . triples::M_PER_S;
        $t->assert($test_case, $result, $target);

        $t->subheader($ts . 'ui validation');

        $test_case = 'check the warning message if a value has more than one unit phrase';
        $val = $tl->ui_value($t_val->light_speed_with_two_units());
        $result = $val->warning_text();
        // TODO add warning
        $target = '';
        $t->assert($test_case, $result, $target);

        $t->subheader($ts . 'html frontend');

        $val = $t_val->value();
        // TODO add class field to api message
        $t->assert_api_to_ui($val, new value_ui());

        // TODO move to ui tests
        $val_dsp = new value_ui($val->api_json([api_types::INCL_PHRASES]));
        $t->assert('value edit link', $val_dsp->value_edit(), '<a href="' . api::MAIN_SCRIPT . '?' . url_var::MASK . '=value_edit&id=32770">3.14</a>');

        $t->subheader($ts . 'convert and api');

        // casting API
        $grp = $t_grp->group();
        $val = new value($usr, round(values::PI_LONG, 13), $grp);
        $t->assert_api($val, 'value_without_phrases');
        $t->assert_api($val, 'value_with_phrases', [api_types::INCL_PHRASES]);
        $val = $t_val->time_value();
        $t->assert_api($val);
        $t->assert_api($val, 'value_with_phrases', [api_types::INCL_PHRASES]);
        $val = $t_val->text_value();
        $t->assert_api($val);
        $t->assert_api($val, 'value_with_phrases', [api_types::INCL_PHRASES]);
        $val = $t_val->geo_value();
        $t->assert_api($val);
        $t->assert_api($val, 'value_with_phrases', [api_types::INCL_PHRASES]);

        // casting figure
        $val = new value($usr);
        $val->set_number(values::SAMPLE_PCT);
        $fig = $val->figure();
        $t->assert($t->name . ' get figure', $fig->number(), $val->number());

        // start the test section (ts)
        $ts = 'unit value time series ';
        $t->header($ts);

        $t->subheader($ts . 'database query creation');

        // sql to load a user-specific time series by id
        $vts = new value_time_series($usr);
        $vts->set_grp($t_grp->group_16());
        $t->assert_sql_by_id($sc, $vts);

        // ... and the related default time series
        // TODO Prio 2 activate
        //$t->assert_sql_standard($sc, $vts);

        // sql to load a user-specific time series by phrase group id
        $vts->reset(true);
        $vts->grp()->set_id(2);
        $this->assert_sql_by_grp($t, $db_con, $vts, $vts->grp());

        $t->subheader($ts . 'data sql setup');
        $tsn = $t_val->value_ts_data();
        $t->assert_sql_table_create($tsn);
        $t->assert_sql_index_create($tsn);
        // TODO Prio 2 activate
        //$t->assert_sql_foreign_key_create($tsn);


        $t->subheader($ts . 'scaling');

        $test_name = 'scale the number of Swiss inhabitants from million to single inhabitants';
        $trm_lst = $t_phr->ch_inhabitants_in_mio_2019()->term_list();
        $res_phr_lst = $t_phr->phrase_list_one();
        $mio_val = $t_val->value_ch();
        $result = $mio_val->scale_new($res_phr_lst, $usr_msg, $trm_lst);
        $target = values::CH_INHABITANTS_2020_IN_MIO * 1000000;
        //$t->assert($test_name, $result, $target);

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
    function assert_sql_update_trigger(
        test_cleanup $t,
        sql_db $db_con, value $val,
        value $db_val
    ): bool
    {
        $sc = $db_con->sql_creator();
        $usr_msg = new user_message();
        $fields = array(sandbox_multi::FLD_LAST_UPDATE);
        $values = array(sql::NOW);
        // check the Postgres query syntax
        $sc->reset(sql_db::POSTGRES);
        $qp = $val->sql_update($sc, $db_val, $usr_msg);
        $result = $t->assert_qp($qp, $sc->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $sc->reset(sql_db::MYSQL);
            $qp = $val->sql_update($sc, $db_val, $usr_msg);
            $result = $t->assert_qp($qp, $sc->db_type);
        }
        return $result;
    }

}