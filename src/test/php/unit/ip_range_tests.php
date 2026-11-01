<?php

/*

    test/unit/ip_range_tests.php - unit testing of the batch task functions
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

    Copyright (c) 1995-2026 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\test\php\unit;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type;
use Zukunft\ZukunftCom\main\php\cfg\helper\data_object;
use Zukunft\ZukunftCom\main\php\cfg\system\ip_range_list;
use Zukunft\ZukunftCom\main\php\shared\const\ip_ranges;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::DB . 'sql_creator.php';
include_once paths::MODEL_SYSTEM . 'ip_range.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED . 'library.php';
include_once test_paths::CONST . 'files.php';
include_once test_paths::CREATE . 'test_ip_ranges.php';
include_once test_paths::UTILS . 'test_cleanup.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\system\ip_range;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\test\php\create\test_ip_ranges;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;
use Zukunft\ZukunftCom\test\php\const\files as test_files;

class ip_range_tests
{
    function run(test_cleanup $t): void
    {

        // init
        $lib = new library();
        $db_con = new sql_db();
        $sc = new sql_creator();
        $t_ip_range = new test_ip_ranges($t);
        $msg = new user_message();
        $t->name = 'ip_range->';
        $t->resource_path = 'db/system/';

        $ts = 'unit ip_range ';
        $t->header($ts);

        $t->subheader($ts . 'sql setup');
        $ip_range = new ip_range();
        $t->assert_sql_table_create($ip_range);
        $t->assert_sql_index_create($ip_range);


        $t->subheader($ts . 'sql read');

        // sql to load one batch ip_range
        $ip_range = new ip_range();
        $t->assert_sql_by_id($sc, $ip_range);
        $this->assert_sql_ip_addresses($t, $sc, $ip_range);

        $t->subheader($ts . 'sql write');
        $ip_range = $t_ip_range->ip_range();
        // for ip_range a log is not needed because the table rows are never expected to be deleted
        $t->assert_sql_insert($sc, $ip_range, [sql_type::LOG]);
        $ip_range = $t_ip_range->ip_range_filled();
        $ip_range_db = $ip_range->clone_reset();
        $t->assert_sql_update($sc, $ip_range, $ip_range_db, [sql_type::LOG]);
        $t->assert_sql_delete($sc, $ip_range, [sql_type::LOG]);

        $t->subheader($ts . 'api');

        $t_ip_range = new test_ip_ranges($t);
        $ip_range = $t_ip_range->ip_range();
        $t->assert_api($ip_range);

        $ip_range_lst = $t_ip_range->ip_range_list($msg);
        $t->assert_api($ip_range_lst);


        /*
         * im- and export tests
         */

        $t->subheader($ts . 'im- and export');

        $msg = new user_message($t->usr1);

        $json_in = json_decode(file_get_contents(test_files::IP_BLACKLIST), true);
        $ip_range = new ip_range();
        $ip_range->set_user($t->usr1);
        // use the system user for the import
        $ip_range->import_obj($json_in, $msg, new data_object($t->usr_system), $t);
        $json_ex = $ip_range->export_json($msg);
        $result = $lib->json_is_similar($json_in, $json_ex);
        $t->assert_true('ip_range->import check', $result);

        // ip_range: the fields are mapped to the export json
        $test_name = 'export the reason';
        $ip_range = $t_ip_range->ip_range_filled();
        $exp = $ip_range->export_json($msg, [], false);
        $t->assert($test_name, $exp[json_fields::REASON], ip_ranges::TEST_REASON);
        $msg->reset();

        // negative: a range without reason exports an empty reason
        $test_name = 'export no reason';
        $ip_range = $t_ip_range->ip_range();
        $exp = $ip_range->export_json($msg, [], false);
        $t->assert_null($test_name, $exp[json_fields::REASON]);
        $msg->reset();


        $t->subheader($ts . 'ip list sql');

        $ip_lst = new ip_range_list();
        $t->assert_sql_by_obj_vars($db_con, $ip_lst);


        $t->subheader($ts . 'list add');

        // a new ip range is added to the list
        $test_name = 'add a range';
        $ip_lst = new ip_range_list();
        $added = $ip_lst->add($t_ip_range->ip_range(), $msg);
        $t->assert_true($test_name, $added);
        $msg->reset();

        // negative: the same range again is refused as a double entry
        $test_name = 'add a double range';
        $ip_lst = $t_ip_range->ip_range_list($msg);
        $added = $ip_lst->add($t_ip_range->ip_range(), $msg);
        $t->assert_false($test_name, $added);
        $msg->reset();


        $t->subheader($ts . 'debug id');

        // the debug id of a saved range shows the database id
        $test_name = 'range with id';
        $ip_range = $t_ip_range->ip_range_loaded();
        $dsp = $ip_range->dsp_id();
        $id_txt = '(' . test_ip_ranges::TV_ID . ')';
        $t->assert_text_contains($test_name, $dsp, $id_txt);

        // negative: a range that is not yet saved has no database id
        $test_name = 'range without id';
        $ip_range = $t_ip_range->ip_range();
        $dsp = $ip_range->dsp_id();
        $id_txt = '(' . test_ip_ranges::TV_ID . ')';
        $t->assert_text_not_contains($test_name, $dsp, $id_txt);

    }

    /**
     * test the SQL statement creation to get an ip range by the ip addresses
     * and check if the statement name is unique
     *
     * @param test_cleanup $t the test environment
     * @param sql_creator $sc the test database connection
     * @param ip_range $ipr the ip range object for which the load-by-address sql statement creation should be tested
     * @return void
     */
    private function assert_sql_ip_addresses(
        test_cleanup $t,
        sql_creator  $sc,
        ip_range     $ipr): void
    {
        // check the Postgres query syntax
        $sc->reset(sql_db::POSTGRES);
        $qp = $ipr->load_sql_by_ip_addresses($sc, ip_ranges::TEST_START, ip_ranges::TEST_END);
        $result = $t->assert_qp($qp, $sc->db_type);

        // check the MySQL query syntax
        if ($result) {
            $sc->reset(sql_db::MYSQL);
            $qp = $ipr->load_sql_by_ip_addresses($sc, ip_ranges::TEST_START, ip_ranges::TEST_END);
            $t->assert_qp($qp, $sc->db_type);
        }
    }

}
