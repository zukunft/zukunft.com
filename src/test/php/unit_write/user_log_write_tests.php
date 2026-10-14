<?php

/*

    test/php/unit_write/user_log_write_tests.php - write test for the user log sessions
    --------------------------------------------

    the removal of the sessions of a user and the archive of the old sessions as csv file


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

include_once paths::MODEL_CONST . 'files.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_log.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_CONST . 'users.php';
include_once test_paths::CREATE . 'test_users.php';
include_once test_paths::UTILS . 'test_cleanup.php';

use Zukunft\ZukunftCom\main\php\cfg\const\files;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_log;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\test\php\create\test_users;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;
use DateTime;

class user_log_write_tests
{

    function run(test_cleanup $t): void
    {

        // init
        $msg = new user_message($t->usr1);
        $t->name = 'user log db write->';
        $before = new DateTime(test_users::TV_USER_LOG_ARCHIVE_BEFORE);
        $dir = rtrim(test_paths::USER_LOG_ARCHIVE, DIRECTORY_SEPARATOR);

        // start the test section (ts)
        $ts = 'db write user log ';
        $t->header($ts);

        $t->subheader($ts . 'delete by user');
        $test_name = 'the sessions of a user are removed before the user is deleted';
        $this->add_old_session($t->usr1->id(), $msg);
        $usr_log = new user_log();
        $usr_log->del_by_user($t->usr1->id(), $msg);
        $t->assert($test_name, $this->old_ids($before, $msg), []);
        $msg->reset();

        $t->subheader($ts . 'delete user');
        // remove the test user left over from a previous run, because the add would update it
        $t->cleanup_test_user(users::TEST_USER_NAME);
        $msg_sys = new user_message($t->usr_system); // the system user adds and removes the reserved test user
        $usr = new test_users($t)->user_add();
        $usr->save_user($msg_sys, $t->usr_system);
        $usr_id = $usr->id();
        $test_name = 'the test user with a session is added';
        $t->assert_true($test_name, $usr_id > 0);
        $this->add_old_session($usr_id, $msg_sys);
        $test_name = '... and its session is written';
        $t->assert($test_name, count($this->old_ids($before, $msg_sys)), 1);
        $test_name = 'a user with a session can be deleted';
        $t->assert_true($test_name, $usr->del($msg_sys));
        $test_name = '... and the session of the user is removed with the user';
        $t->assert($test_name, $this->old_ids($before, $msg_sys), []);
        $test_name = '... and the user is removed';
        $t->assert($test_name, new user()->load_by_id($usr_id, $msg_sys), 0);
        $t->cleanup_test_user(users::TEST_USER_NAME);

        $t->subheader($ts . 'archive');
        // archive files left over from a previous run would be counted as written by this run
        $this->remove_archive($dir);
        $test_name = 'a failed archive file write is reported';
        $this->add_old_session($t->usr1->id(), $msg);
        // an existing file cannot be used as the archive directory
        $usr_log->archive($msg, $before, __FILE__);
        // the message of msg_id::FILE_WRITE_FAILED names the file in the given directory
        $t->assert_text_contains($test_name, $msg->text(), __FILE__);
        $msg->reset();
        $test_name = '... and the session is kept in the database';
        $t->assert($test_name, count($this->old_ids($before, $msg)), 1);
        $msg->reset();
        $test_name = 'the old session is moved to the archive';
        $t->assert($test_name, $usr_log->archive($msg, $before, $dir), 1);
        $msg->reset();
        $test_name = '... and removed from the database';
        $t->assert($test_name, $this->old_ids($before, $msg), []);
        $msg->reset();
        $test_name = '... and written with the header line to one csv file';
        $files = glob($dir . DIRECTORY_SEPARATOR . files::USER_LOG_ARCHIVE . '*' . files::CSV);
        $lines = file($files[0] ?? __FILE__, FILE_IGNORE_NEW_LINES);
        $t->assert($test_name, count($files) . ' file with ' . count($lines) . ' lines', '1 file with 2 lines');
        $test_name = '... that starts with the field names';
        $t->assert($test_name, $lines[0], implode(',', [user_log::FLD_ID, ...user_log::FLD_NAMES]));

        // cleanup
        $this->remove_archive($dir);
    }

    /**
     * write a session of a test user that has ended before any real session
     * @param int $usr_id the test user whose session is written
     * @param user_message $msg to report why the session could not be written
     */
    private function add_old_session(int $usr_id, user_message $msg): void
    {
        $usr_log = new user_log();
        $start = (float)strtotime(test_users::TV_USER_LOG_OLD_START);
        $usr_log->log_request($usr_id, $start, $start + test_users::TV_USER_LOG_SEC, false, $msg);
    }

    /**
     * remove the archive files of the test and the test archive directory if they exist
     * @param string $dir the test archive directory without the closing slash
     */
    private function remove_archive(string $dir): void
    {
        if (is_dir($dir)) {
            array_map('unlink', glob($dir . DIRECTORY_SEPARATOR . files::USER_LOG_ARCHIVE . '*' . files::CSV));
            rmdir($dir);
        }
    }

    /**
     * @param DateTime $before the sessions that have ended before this time are selected
     * @param user_message $msg to report a failed database read
     * @return array the ids of the sessions that would be archived
     */
    private function old_ids(DateTime $before, user_message $msg): array
    {
        global $db_con;
        $usr_log = new user_log();
        $qp = $usr_log->load_sql_old($db_con->sql_creator(), $before, user_log::ARCHIVE_LIMIT);
        $rows = $db_con->get($qp, $msg);
        if ($rows === false) {
            $rows = [];
        }
        return array_column($rows, user_log::FLD_ID);
    }

}
