<?php

/*

    test/php/unit_write/update_put_write_tests.php - db write tests of the update put messages
    -----------------------------------------------

    put each update put message of the calculator editor save, read the result back from the
    database and compare it, then put the undo message


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

include_once paths::EXPORT . 'phrase_data.php';
include_once paths::MODEL_HELPER . 'db_cache_page.php';
include_once paths::MODEL_IMPORT . 'update_put.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::MODEL_VALUE . 'value.php';
include_once paths::MODEL_WORD . 'triple.php';
include_once paths::MODEL_WORD . 'word.php';
include_once paths::SHARED_CONST . 'values.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'url_var.php';
include_once test_paths::CONST . 'files.php';
include_once test_paths::CONST . 'triple_names.php';
include_once test_paths::CONST . 'word_names.php';
include_once test_paths::UTILS . 'test_cleanup.php';

use Zukunft\ZukunftCom\main\php\cfg\export\phrase_data;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_cache_page;
use Zukunft\ZukunftCom\main\php\cfg\import\update_put;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\value\value;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\shared\const\values;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\test\php\const\files as test_files;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\utils\test_base;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;
use DateTime;

class update_put_write_tests
{

    // a cached answer of the phrase data, which the put must remove
    const string TV_API_JSON = '{"update_put_test":[1]}';

    function run(test_cleanup $t): void
    {

        // init
        $upd = new update_put();
        $chk_msg = new user_message($t->usr1);

        // start the test section (ts)
        $ts = 'db write update put ';
        $t->header($ts);

        $t->subheader($ts . 'add');

        $test_name = 'a triple whose words are neither in the message nor in the database is refused';
        $msg = new user_message($t->usr1);
        $upd->put($this->message(test_files::UPDATE_PUT_WITHOUT_WORD), $msg);
        $t->assert_false($test_name, $msg->is_ok());
        $test_name = '... and not added';
        $trp = new triple($t->usr1);
        $trp->load_by_name(triple_names::SYSTEM_TEST_ADD, $chk_msg);
        $t->assert($test_name, $trp->id(), 0, test_base::TIMEOUT_LIMIT_DB);

        $cac_page = new db_cache_page();
        $cac_key = db_cache_page::api_key(phrase_data::class, [url_var::ID => word_names::TEST_ADD_ID], $t->usr1->id());
        $cac_page->save_json($cac_key, self::TV_API_JSON, new DateTime(), $chk_msg);
        $test_name = 'the phrase data answer is cached before the put';
        $t->assert($test_name, $cac_page->json_by_key($cac_key, $chk_msg), self::TV_API_JSON, test_base::TIMEOUT_LIMIT_DB);

        $test_name = 'put the add message of the words, the triple and the value';
        $msg = new user_message($t->usr1);
        $add = $upd->put($this->message(test_files::UPDATE_PUT_ADD), $msg);
        $t->assert_true($test_name . ' ' . $msg->all_message_text(), $msg->is_ok(), test_base::TIMEOUT_LIMIT_DB_MULTI);
        $test_name = 'the triple of the add message is in the database';
        $trp = new triple($t->usr1);
        $trp->load_by_name(triple_names::SYSTEM_TEST_ADD, $chk_msg);
        $t->assert_greater_zero($test_name, $trp->id(), test_base::TIMEOUT_LIMIT_DB);
        $test_name = 'the answer of the add message has the database id of the triple';
        $t->assert($test_name, $add[json_fields::TRIPLES][0][json_fields::ID], $trp->id());
        $test_name = 'the value of the add message is in the database';
        $val_id = $add[json_fields::VALUES][0][json_fields::ID];
        $val = new value($t->usr1);
        $val->load_by_id($val_id, $chk_msg);
        $t->assert($test_name, $val->number(), values::SAMPLE_INT, test_base::TIMEOUT_LIMIT_DB);
        $test_name = 'the put removes the cached phrase data';
        $t->assert_null($test_name, $cac_page->json_by_key($cac_key, $chk_msg));

        $t->subheader($ts . 'update');

        // the database ids differ by database, so the test adds them to the message
        $wrd_id = $add[json_fields::WORDS][0][json_fields::ID];
        $wrd_to_id = $add[json_fields::WORDS][1][json_fields::ID];
        $json = $this->message(test_files::UPDATE_PUT_UPDATE);
        $json[json_fields::BODY][json_fields::TRIPLES][0][json_fields::ID] = $trp->id();
        $json[json_fields::BODY][json_fields::TRIPLES][0][json_fields::FROM] = $wrd_id;
        $json[json_fields::BODY][json_fields::TRIPLES][0][json_fields::TO] = $wrd_to_id;
        // as text like the editor sends a group id beyond the safe integer of javascript
        $json[json_fields::BODY][json_fields::VALUES][0][json_fields::ID] = (string)$val_id;
        $test_name = 'put the update message with the database ids';
        $msg = new user_message($t->usr1);
        $upd->put($json, $msg);
        $t->assert_true($test_name . ' ' . $msg->all_message_text(), $msg->is_ok(), test_base::TIMEOUT_LIMIT_DB_MULTI);
        $test_name = 'the triple is renamed, because the id is the prime key';
        $trp_upd = new triple($t->usr1);
        $trp_upd->load_by_id($trp->id(), $chk_msg);
        $t->assert($test_name, $trp_upd->name(), triple_names::SYSTEM_TEST_RENAMED, test_base::TIMEOUT_LIMIT_DB);
        $test_name = '... and has the description of the message';
        $t->assert($test_name, $trp_upd->description, triple_names::SYSTEM_TEST_ADD_COM);
        $test_name = 'the value is changed by its id';
        $val = new value($t->usr1);
        $val->load_by_id($val_id, $chk_msg);
        $t->assert($test_name, $val->number(), values::SAMPLE_FLOAT, test_base::TIMEOUT_LIMIT_DB);

        $t->subheader($ts . 'undo');

        $json = $this->undo_message($val_id, $trp->id(), $wrd_id, $wrd_to_id);
        $test_name = 'put the undo message that deletes the value, the triple and the words';
        $msg = new user_message($t->usr1);
        $upd->put($json, $msg);
        $t->assert_true($test_name . ' ' . $msg->all_message_text(), $msg->is_ok(), test_base::TIMEOUT_LIMIT_DB_MULTI);
        $test_name = 'the triple is deleted';
        $trp_del = new triple($t->usr1);
        $trp_del->load_by_id($trp->id(), $chk_msg);
        $t->assert($test_name, $trp_del->id(), 0, test_base::TIMEOUT_LIMIT_DB);
        $test_name = 'the word is deleted';
        $wrd = new word($t->usr1);
        $wrd->load_by_id($wrd_id, $chk_msg);
        $t->assert($test_name, $wrd->id(), 0, test_base::TIMEOUT_LIMIT_DB);
        $test_name = 'the undo message again is refused, because the objects are deleted';
        $msg = new user_message($t->usr1);
        $upd->put($json, $msg);
        $t->assert_false($test_name, $msg->is_ok());
    }

    /**
     * @param string $filename the path of the update put message e.g. test_files::UPDATE_PUT_ADD
     * @return array the update put message
     */
    private function message(string $filename): array
    {
        return json_decode(file_get_contents($filename), true);
    }

    /**
     * @param int|string $val_id the database id of the value, which is the id of its group
     * @param int $trp_id the database id of the triple
     * @param int $wrd_id the database id of the from word
     * @param int $wrd_to_id the database id of the to word
     * @return array the undo message with the database ids in the order of its delete list
     */
    private function undo_message(int|string $val_id, int $trp_id, int $wrd_id, int $wrd_to_id): array
    {
        $json = $this->message(test_files::UPDATE_PUT_UNDO);
        $ids = [$val_id, $trp_id, $wrd_id, $wrd_to_id];
        foreach ($ids as $pos => $id) {
            $json[json_fields::BODY][json_fields::DELETE][$pos][json_fields::ID] = $id;
        }
        return $json;
    }

}
