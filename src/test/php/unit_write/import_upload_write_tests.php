<?php

/*

    test/php/unit_write/import_upload_write_tests.php - the upload of a json file on the import view
    -------------------------------------------------


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

include_once paths::MODEL_IMPORT . 'import.php';
include_once paths::SHARED_CONST . 'rest_ctrl.php';
include_once paths::SHARED_ENUM . 'change_tables.php';

use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\shared\const\rest_ctrl;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\change_tables;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\frontend;
use Zukunft\ZukunftCom\main\php\web\user\user as user_ui;
use Zukunft\ZukunftCom\main\php\web\user\user_message as user_message_ui;
use Zukunft\ZukunftCom\test\php\const\files as test_files;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class import_upload_write_tests
{

    // the extension of a file that is no json, to check that only a json file is imported
    const string NO_JSON_EXT = '.txt';
    // one byte more than the fallback limit, which is the upload limit of the default config.yaml
    const int SIZE_ABOVE_LIMIT = def::FALLBACK_IMPORT_FILE_MB * 1000000 + 1;

    /**
     * the upload of the import view imports a small json file of test objects and shows the number of the imported
     * objects per type; a refused file is not imported and the message says why; the test rows are removed again
     * through the logged delete
     *
     * @param test_cleanup $t the test environment with the test users
     * @return void
     */
    function run(test_cleanup $t): void
    {
        global $mtr;

        // init
        $lib = new library();
        $msg = new user_message($t->usr1);
        $file = test_files::IMPORT_UPLOAD . test_files::JSON;
        $name = basename($file);
        $upload = [
            rest_ctrl::UPLOAD_NAME => $name,
            rest_ctrl::UPLOAD_TMP_NAME => $file,
            rest_ctrl::UPLOAD_SIZE => filesize($file)
        ];

        // start the test section (ts)
        $ts = 'db write import upload ';
        $t->header($ts);

        $t->subheader($ts . 'refused');
        $test_name = 'a user who may not import gets the message';
        $vars = [msg_id::VAR_FILE_NAME => $name];
        $target = $lib->msg_var_text([[msg_id::IMPORT_UPLOAD_NOT_PERMITTED, $vars]], $mtr);
        $this->assert_upload_refused($t, $test_name, $t->usr_normal, $upload, true, $target);

        $test_name = 'a file that has not been uploaded with the request';
        $target = $lib->msg_var_text([[msg_id::IMPORT_UPLOAD_MISSING, $vars]], $mtr);
        $this->assert_upload_refused($t, $test_name, $t->usr1, $upload, false, $target);

        $test_name = 'a file that is no json';
        $no_json = $upload;
        $no_json[rest_ctrl::UPLOAD_NAME] = $name . self::NO_JSON_EXT;
        $no_json_vars = [msg_id::VAR_FILE_NAME => $no_json[rest_ctrl::UPLOAD_NAME]];
        $target = $lib->msg_var_text([[msg_id::IMPORT_UPLOAD_NO_JSON, $no_json_vars]], $mtr);
        $this->assert_upload_refused($t, $test_name, $t->usr1, $no_json, true, $target);

        $test_name = 'an empty file';
        $empty = $upload;
        $empty[rest_ctrl::UPLOAD_SIZE] = 0;
        $target = $lib->msg_var_text([[msg_id::IMPORT_UPLOAD_EMPTY, $vars]], $mtr);
        $this->assert_upload_refused($t, $test_name, $t->usr1, $empty, true, $target);

        $test_name = 'a file above the size limit';
        $too_big = $upload;
        $too_big[rest_ctrl::UPLOAD_SIZE] = self::SIZE_ABOVE_LIMIT;
        $too_big_vars = $vars;
        $too_big_vars[msg_id::VAR_VALUE] = self::SIZE_ABOVE_LIMIT;
        $too_big_vars[msg_id::VAR_VALUE_CHK] = def::FALLBACK_IMPORT_FILE_MB;
        $target = $lib->msg_var_text([[msg_id::IMPORT_UPLOAD_TOO_BIG, $too_big_vars]], $mtr);
        $this->assert_upload_refused($t, $test_name, $t->usr1, $too_big, true, $target);

        $t->subheader($ts . 'imported');
        $test_name = 'the upload of a json file shows the import result view';
        $ui = new frontend('');
        $msg_ui = new user_message_ui();
        $msg_ui->usr = new user_ui($t->usr1->api_json());
        $url = [url_var::MASK => views::IMPORT_ID, url_var::POST_SUBMIT => url_var::TRUE];
        $next = $ui->url_to_action($url, $msg_ui, $ui->dto, true, $upload, true);
        $next_mask = $next[url_var::MASK] ?? 0;
        $t->assert($test_name, $next_mask, views::IMPORT_RESULT_ID, $t::TIMEOUT_LIMIT_IMPORT);

        $test_name = '... with the number of the imported objects per type';
        $words_txt = $mtr->text_db_table(change_tables::WORD) . ': 2';
        $triples_txt = $mtr->text_db_table(change_tables::TRIPLE) . ': 1';
        $summary = $words_txt . ', ' . $triples_txt;
        $target = $lib->msg_var_text([[msg_id::IMPORT_UPLOAD_DONE, [
            msg_id::VAR_FILE_NAME => $name,
            msg_id::VAR_SUMMARY => $summary
        ]]], $mtr);
        $t->assert($test_name, $msg_ui->text(), $target);
        $msg_ui->reset();

        $test_name = '... and the triple of the file is in the database';
        $trp = new triple($t->usr1);
        $trp->load_by_name(triple_names::SYSTEM_TEST_ADD, $msg);
        $t->assert_greater_zero($test_name, $trp->id(), $t::TIMEOUT_LIMIT_DB);

        // cleanup through the logged delete; the change log rows go with the cleanup of the reserved test names
        $test_name = 'the imported test triple and test words are removed again';
        $trp->del($msg);
        foreach ([word_names::TEST_ADD, word_names::TEST_ADD_TO] as $wrd_name) {
            $wrd = new word($t->usr1);
            $wrd->load_by_name($wrd_name, $msg);
            $wrd->del($msg);
        }
        $t->assert_msg($test_name, $msg, $t::TIMEOUT_LIMIT_DB_MULTI);
        $msg->reset();
    }

    /**
     * check that the upload of the import view refuses the file with the given message, shows the import view again
     * and has not imported the test triple of the file
     *
     * @param test_cleanup $t the test environment with the test users
     * @param string $test_name the description of the refused upload
     * @param user $usr the user who uploads the file
     * @param array $upload the entry of the uploaded file as php gives it in $_FILES
     * @param bool $is_uploaded true if php would have received the file with the request
     * @param string $target the expected message in the user language
     * @return bool true if the upload has been refused as expected
     */
    private function assert_upload_refused(
        test_cleanup $t,
        string       $test_name,
        user         $usr,
        array        $upload,
        bool         $is_uploaded,
        string       $target
    ): bool
    {
        $ui = new frontend('');
        $msg_ui = new user_message_ui();
        $msg_ui->usr = new user_ui($usr->api_json());
        $msg = new user_message($t->usr1);
        $url = [url_var::MASK => views::IMPORT_ID, url_var::POST_SUBMIT => url_var::TRUE];
        $next = $ui->url_to_action($url, $msg_ui, $ui->dto, true, $upload, $is_uploaded);
        $next_mask = $next[url_var::MASK] ?? 0;
        $result = $t->assert($test_name . ' shows the import view again', $next_mask, views::IMPORT_ID, $t::TIMEOUT_LIMIT_DB);
        $msg_txt = $msg_ui->text();
        $result = ($t->assert_text_contains($test_name . ' with the message', $msg_txt, $target) and $result);
        $trp = new triple($t->usr1);
        $trp->load_by_name(triple_names::SYSTEM_TEST_ADD, $msg);
        $trp_id = $trp->id();
        return ($t->assert($test_name . ' and imports nothing', $trp_id, 0, $t::TIMEOUT_LIMIT_DB) and $result);
    }

}
