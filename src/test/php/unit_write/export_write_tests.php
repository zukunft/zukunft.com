<?php

/*

    test/php/unit_write/export_write_tests.php - the export of a phrase selection with test objects of the database
    ------------------------------------------


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

include_once paths::EXPORT . 'export.php';
include_once paths::SHARED_TYPES . 'share_types.php';

use Zukunft\ZukunftCom\main\php\cfg\export\export;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\types\share_types;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\test\php\const\formula_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_db_load;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class export_write_tests
{

    /**
     * the export of a private word is only for its owner and the csv of a formula without results is the head only;
     * both need own test rows, which are removed again through the logged delete
     *
     * @param test_cleanup $t the test environment with the test users
     * @return void
     */
    function run(test_cleanup $t): void
    {
        // init
        $t_db = new test_db_load($t);
        $exp = new export();
        $msg = new user_message($t->usr1);
        // a user without admin or test profile, because these may read every object (see sandbox::is_readable_by)
        $msg_normal = new user_message($t->usr_normal);

        // start the test section (ts)
        $ts = 'db write export ';
        $t->header($ts);

        $t->subheader($ts . 'private phrase');
        $test_name = 'the owner exports the own private word';
        $wrd = $t_db->add_word($msg, word_names::TEST_ADD);
        $wrd->set_share(share_types::PRIVATE);
        $wrd->save($msg);
        $result = $exp->file_text([word_names::TEST_ADD], [], url_var::EXPORT_FORMAT_JSON, $msg);
        $t->assert_text_contains($test_name, $result, word_names::TEST_ADD, $t::TIMEOUT_LIMIT_DB_MULTI);
        $msg->reset();

        $test_name = '... but another user gets no export of it';
        $result = $exp->file_text([word_names::TEST_ADD], [], url_var::EXPORT_FORMAT_JSON, $msg_normal);
        $t->assert($test_name, $result, '', $t::TIMEOUT_LIMIT_DB_MULTI);

        $test_name = '... and the message that nothing is selected';
        $t->assert_text_contains($test_name, $msg_normal->text(), msg_id::EXPORT_PHRASE_MISSING->text());
        $msg_normal->reset();

        $t->subheader($ts . 'formula results');
        $test_name = 'a formula without results gives the csv head only';
        $wrd_to = $t_db->add_word($msg, word_names::TEST_ADD_TO);
        $frm = $t_db->add_formula(formula_names::SYSTEM_TEST_NO_RESULT, formula_names::SYSTEM_TEST_NO_RESULT_EXP, $msg);
        $result = $exp->formula_result_csv([formula_names::SYSTEM_TEST_NO_RESULT], $msg);
        $quoted_name = library::CSV_ENCLOSURE . formula_names::SYSTEM_TEST_NO_RESULT . library::CSV_ENCLOSURE;
        $target = library::CSV_DELIMITER . $quoted_name . "\n";
        $t->assert($test_name, $result, $target, $t::TIMEOUT_LIMIT_DB_MULTI);
        $msg->reset();

        $test_name = '... but a word is no formula';
        $result = $exp->formula_result_csv([word_names::TEST_ADD_TO], $msg);
        $t->assert($test_name, $result, '');

        $test_name = '... which the message names';
        $t->assert_text_contains($test_name, $msg->text(), word_names::TEST_ADD_TO);
        $msg->reset();

        // cleanup through the logged delete; the change log rows go with the cleanup of the reserved test names
        $test_name = 'the test formula and the test words are removed again';
        $frm->del($msg);
        $wrd_to->del($msg);
        $wrd->del($msg);
        $t->assert_msg($test_name, $msg, $t::TIMEOUT_LIMIT_DB_MULTI);
        $msg->reset();
    }

}
