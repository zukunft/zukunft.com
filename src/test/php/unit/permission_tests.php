<?php

/*

    test/unit/permission_tests.php - TESTing of the permission level functions
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
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::MODEL_WORD . 'word.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once test_paths::CREATE . 'test_users.php';
include_once test_paths::CREATE . 'test_values.php';
include_once test_paths::CREATE . 'test_words.php';
include_once test_paths::UTILS . 'test_cleanup.php';

use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\test\php\create\test_users;
use Zukunft\ZukunftCom\test\php\create\test_values;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class permission_tests
{
    function run(test_cleanup $t): void
    {

        // init
        global $mtr;
        $t_usr = new test_users($t);
        $t_wrd = new test_words($t);
        $t_val = new test_values($t);
        $t->name = 'permission->';

        // start the test section (ts)
        $ts = 'unit permission ';
        $t->header($ts);


        $t->subheader($ts . 'ip user blocked');

        // by default config.yaml does not permit the database changes of a user without login
        $usr_ip = $t_usr->user_ip_loaded();
        $wrd = $t_wrd->word();
        $blocked_txt = $mtr->txt(msg_id::CHANGE_BLOCKED_FOR_IP_USER);

        $test_name = 'a word cannot be added by an ip user';
        $usr_msg = new user_message($usr_ip);
        $t->assert_false($test_name, $wrd->can_be_added_by($usr_msg));
        $test_name = 'the ip user is told why the adding has been rejected';
        $t->assert_text_contains($test_name, $usr_msg->all_message_text(), $blocked_txt);

        $test_name = 'a word cannot be changed by an ip user';
        $usr_msg = new user_message($usr_ip);
        $t->assert_false($test_name, $wrd->can_be_changed_by($usr_msg));
        $test_name = 'the ip user is told why the change has been rejected';
        $t->assert_text_contains($test_name, $usr_msg->all_message_text(), $blocked_txt);

        $test_name = 'a word cannot be deleted by an ip user';
        $usr_msg = new user_message($usr_ip);
        $t->assert_false($test_name, $wrd->can_be_deleted_by($usr_msg));
        $test_name = 'the ip user is told why the deletion has been rejected';
        $t->assert_text_contains($test_name, $usr_msg->all_message_text(), $blocked_txt);

        // save and del are the single entry points of all user data changes, so the block is checked there
        // that a permitted user can actually save and delete is tested by the write tests
        $test_name = 'the save of a word requested by an ip user is blocked';
        $usr_msg = new user_message($usr_ip);
        $t->assert_false($test_name, $wrd->save($usr_msg));
        $test_name = 'the ip user is told why the save has been rejected';
        $t->assert_text_contains($test_name, $usr_msg->all_message_text(), $blocked_txt);

        $test_name = 'the del of a word requested by an ip user is blocked';
        $usr_msg = new user_message($usr_ip);
        $t->assert_false($test_name, $wrd->del($usr_msg));
        $test_name = 'the ip user is told why the del has been rejected';
        $t->assert_text_contains($test_name, $usr_msg->all_message_text(), $blocked_txt);

        // a value is a multi-user object (sandbox_multi branch), so verify the block is enforced
        // there as well and not only in the sandbox branch that the word above covers
        $val = $t_val->value();
        $test_name = 'the save of a value requested by an ip user is blocked';
        $usr_msg = new user_message($usr_ip);
        $t->assert_false($test_name, $val->save($usr_msg));
        $test_name = 'the ip user is told why the value save has been rejected';
        $t->assert_text_contains($test_name, $usr_msg->all_message_text(), $blocked_txt);

        $test_name = 'the del of a value requested by an ip user is blocked';
        $usr_msg = new user_message($usr_ip);
        $t->assert_false($test_name, $val->del($usr_msg));
        $test_name = 'the ip user is told why the value del has been rejected';
        $t->assert_text_contains($test_name, $usr_msg->all_message_text(), $blocked_txt);


        $t->subheader($ts . 'ownerless object');

        // an object whose owner was never set (shared or seed data) is the standard row seen by
        // all users; an ip user must not change that shared row, so can_change is false
        $wrd_ip = new word($usr_ip);
        $wrd_ip->set_owner_id(0);
        $test_name = 'an ip user cannot change an ownerless object';
        $usr_msg = new user_message($usr_ip);
        $t->assert_false($test_name, $wrd_ip->can_change($usr_msg));

        // a logged-in user may take over an ownerless object and change the standard row
        $wrd_admin = new word($t->usr_admin);
        $wrd_admin->set_owner_id(0);
        $test_name = 'a logged-in user can change an ownerless object';
        $usr_msg = new user_message($t->usr_admin);
        $t->assert_true($test_name, $wrd_admin->can_change($usr_msg));


        $t->subheader($ts . 'user with login');

        // a user with a login is never blocked by the ip user permission
        $test_name = 'a word can be added by an admin user';
        $usr_msg = new user_message($t->usr_admin);
        $t->assert_true($test_name, $wrd->can_be_added_by($usr_msg));
        $test_name = 'a word can be changed by an admin user';
        $t->assert_true($test_name, $wrd->can_be_changed_by($usr_msg));
        $test_name = 'a word can be deleted by an admin user';
        $t->assert_true($test_name, $wrd->can_be_deleted_by($usr_msg));


        $t->subheader($ts . 'ip user permitted');

        // simulate a pod that permits the database changes of a user without login
        global $cfg;
        $cfg_pod = $cfg;
        $cfg = $t_val->config_ip_user_change(true);

        $test_name = 'a word can be added by an ip user if this pod permits it';
        $usr_msg = new user_message($usr_ip);
        $t->assert_true($test_name, $wrd->can_be_added_by($usr_msg));
        $test_name = 'a word can be changed by an ip user if this pod permits it';
        $t->assert_true($test_name, $wrd->can_be_changed_by($usr_msg));
        $test_name = 'a word can be deleted by an ip user if this pod permits it';
        $t->assert_true($test_name, $wrd->can_be_deleted_by($usr_msg));

        // restore the configuration of this pod
        $cfg = $cfg_pod;


        // if a user has done 3 value edits he can add new values (adding a word to a value also creates a new value)
        // if a user has added 3 values and at least one is accepted by another user, he can add words and formula and he must have a valid email
        // if a user has added 2 formula and both are accepted by at least one other user and no one has complained, he can change formulas and words, including linking of words
        // if a user has linked 10 words and all got accepted by one other user and no one has complained, he can request new verbs and he must have an validated address

        // if a user got 10 pending word or formula discussion, he can no longer add words or formula utils the open discussions are less than 10
        // if a user got 5 pending word or formula discussion, he can no longer change words or formula utils the open discussions are less than 5
        // if a user got 2 pending verb discussion, he can no longer add verbs utils the open discussions are less than 2

        // the same ip can max 10 add 10 values and max 5 user a day, upon request the number of max user creation can be increased for an ip range

    }
}