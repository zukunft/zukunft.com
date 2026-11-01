<?php

/*

    test/php/unit_write/value_by_id_write_tests.php - db write tests of a value given only by its id
    -----------------------------------------------

    change and delete a value that is given only by its id, as the update put does it, once for
    the value of a word and once for the value of a triple, to find out if the write of a value
    without its phrase list reaches the database row


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

namespace Zukunft\ZukunftCom\test\php\unit_write;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::MODEL_USER . 'user_message.php';
include_once paths::MODEL_VALUE . 'value.php';
include_once paths::SHARED_CONST . 'values.php';
include_once paths::SHARED_TYPES . 'verbs.php';
include_once test_paths::CONST . 'triple_names.php';
include_once test_paths::CONST . 'word_names.php';
include_once test_paths::CREATE . 'test_db_load.php';
include_once test_paths::UTILS . 'test_cleanup.php';

use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\value\value;
use Zukunft\ZukunftCom\main\php\shared\const\values;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_db_load;
use Zukunft\ZukunftCom\test\php\utils\test_base;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class value_by_id_write_tests
{

    // the number of the value after the change of the loaded value
    const float NUMBER_LOADED = 234.5;

    function run(test_cleanup $t): void
    {
        // init
        $t_db = new test_db_load($t);
        $msg = new user_message($t->usr1);

        // start the test section (ts)
        $ts = 'db write value by id ';
        $t->header($ts);

        $t->subheader($ts . 'prepare');
        $wrd = $t_db->test_word($msg, word_names::TEST_ADD);
        $wrd_to = $t_db->test_word($msg, word_names::TEST_ADD_TO);
        $trp = $t_db->test_triple($msg, word_names::TEST_ADD, verbs::IS, word_names::TEST_ADD_TO, '', triple_names::SYSTEM_TEST_ADD);

        // the value of a word first, to see if a problem is only the one of the value of a triple
        $this->run_value($t, $ts . 'of a word ', [word_names::TEST_ADD]);
        $this->run_value($t, $ts . 'of a triple ', [triple_names::SYSTEM_TEST_ADD]);

        // a change log entry must never point to a deleted test row
        $trp->del($msg);
        $t->cleanup_change_log($trp, [triple_names::SYSTEM_TEST_ADD]);
        $wrd->del($msg);
        $wrd_to->del($msg);
        $t->cleanup_change_log($wrd, [word_names::TEST_ADD, word_names::TEST_ADD_TO]);
    }

    /**
     * change the value after the load by its id, change it given only by its id and the number
     * like the update put, and delete it after the load by its id like the undo of the update put
     *
     * @param test_cleanup $t the test environment with the test user
     * @param string $ts the name of the test section e.g. "db write value by id of a triple "
     * @param array $phr_names the names of the phrases of the value
     * @return void
     */
    private function run_value(test_cleanup $t, string $ts, array $phr_names): void
    {
        $t_db = new test_db_load($t);
        $msg = new user_message($t->usr1);
        $chk_msg = new user_message($t->usr1);
        $t->subheader($ts);

        $test_name = 'the value is added with its phrases';
        $val = $t_db->add_value($phr_names, values::SAMPLE_INT);
        $val_id = $val->id();
        // read back, because the database sets the last update of the added value
        $val_chk = new value($t->usr1);
        $val_chk->load_by_id($val_id, $chk_msg);
        $t->assert($test_name, $val_chk->number(), values::SAMPLE_INT, test_base::TIMEOUT_LIMIT_DB);
        // the phrases are kept to remove the change log of the value after its delete
        $val_grp = $val->grp();

        $test_name = 'the value loaded by its id is changed';
        $val_upd = new value($t->usr1);
        $val_upd->load_by_id($val_id, $chk_msg);
        $val_upd->set_number(self::NUMBER_LOADED);
        $val_upd->save($msg);
        $t->assert_msg($test_name, $msg, test_base::TIMEOUT_LIMIT_DB);
        $msg->reset();
        $test_name = '... and the database has the changed number';
        $val_chk = new value($t->usr1);
        $val_chk->load_by_id($val_id, $chk_msg);
        $t->assert($test_name, $val_chk->number(), self::NUMBER_LOADED, test_base::TIMEOUT_LIMIT_DB);

        $test_name = 'the value given only by its id and the number is changed like by the update put';
        $val_req = new value($t->usr1);
        $val_req->set_id($val_id);
        $val_req->set_number(values::SAMPLE_FLOAT);
        $val_db = new value($t->usr1);
        $val_db->load_by_id($val_id, $chk_msg);
        $msg->merge($val_req->fill($val_db, $t->usr1));
        $val_req->save($msg);
        $t->assert_msg($test_name, $msg, test_base::TIMEOUT_LIMIT_DB);
        $msg->reset();
        $test_name = '... and the database has the changed number';
        $val_chk = new value($t->usr1);
        $val_chk->load_by_id($val_id, $chk_msg);
        $t->assert($test_name, $val_chk->number(), values::SAMPLE_FLOAT, test_base::TIMEOUT_LIMIT_DB);

        $test_name = 'the value loaded by its id is deleted like by the undo of the update put';
        $val_del = new value($t->usr1);
        $val_del->load_by_id($val_id, $chk_msg);
        $val_del->del($msg);
        $t->assert_msg($test_name, $msg, test_base::TIMEOUT_LIMIT_DB);
        $msg->reset();
        $test_name = '... and the database has no value with the id any more';
        $val_chk = new value($t->usr1);
        $found_id = $val_chk->load_by_id($val_id, $chk_msg);
        $t->assert($test_name, $found_id, 0, test_base::TIMEOUT_LIMIT_DB);

        $t->cleanup_change_log_value($val_grp, $val_grp->phrase_list());
    }

}
