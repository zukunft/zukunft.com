<?php

/*

  test_system.php - TESTing of the basic system functions like ip blocking
  ---------------
  

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

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once paths::SHARED_CONST . 'users.php';
include_once paths::SERVICE . 'config.php';
include_once paths::WEB . 'frontend.php';

use Zukunft\ZukunftCom\main\php\web\frontend;
use Zukunft\ZukunftCom\main\php\web\user\user as user_ui;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_list;
use Zukunft\ZukunftCom\main\php\service\config;
use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_db_load;
use Zukunft\ZukunftCom\test\php\utils\all_tests;

function run_system_test(all_tests $t): void
{

    global $usr;
    global $db_con;

    $t_db = new test_db_load($t);

    // start the test section (ts)
    $ts = 'db read code consistency ';
    $t->header($ts);

    // load the main test word
    $wrd_company = $t_db->test_word(word_names::COMPANY);

    if ($t::TEST_EMAIL) {
        $t->subheader($ts . 'est mail sending');
        $mail_to = 'timon@zukunft.com';
        $mail_subject = 'Test mailto';
        $mail_body = 'Hello';
        $mail_header = 'From: heang@zukunft.com' . "\r\n" .
            'Reply-To: heang@zukunft.com' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();

        mail($mail_to, $mail_subject, $mail_body, $mail_header);
    }


    // check if the owner is always setting
    //$sbx = New _sandbox;
    //$chk_txt = $sbx->chk_owner(sql_db::TBL_TRIPLE, False); if ($chk_txt <> '') { echo $chk_txt."<br>"; }

    $t->subheader($ts . 'blocked IP addresses');

    // check the first predefined word "company"
    // load by id
    $usr_test = new user;
    $usr_test->ip_addr = users::TEST_IP;
    $target = 'Your IP ' . $usr_test->ip_addr . ' is blocked at the moment because too much damage from this IP. If you think, this should not be the case, please request the unblocking with an email to admin@zukunft.com.';
    $result = $usr_test->get();
    if ($usr_test->id() > 0) {
        $result = 'permitted!';
    }
    $t->assert('IP blocking for ' . $usr_test->ip_addr, $result, $target);

    // TODO combine with the other user unit tests
    $t->subheader($ts . 'user unit tests');

    // load by name
    $usr_by_id = new user;
    $usr_by_id->load_by_id(users::SYSTEM_TEST_ID);
    $usr_test = new user;
    $usr_test->load_by_name(users::SYSTEM_TEST_NAME);
    $usr_ui = new user_ui($usr_by_id->api_json());
    $target = '<a href="/http/view.php?m=74&id=' . $usr_test->id() . '">zukunft.com system test</a>';
    $result = $usr_ui->display();
    $t->assert('user->load for id ' . $wrd_company->id(), $result, $target);


    $t->subheader('user list');

    $usr_lst = new user_list($usr);
    $usr_lst->load_active();
    $result = $usr_lst->name_lst();
    $target = users::TEST_NAME;
    $t->dsp_contains(', user_list->load_active', $target, $result);


    // regression test for the config->check_cfg bug that stored a hardcoded site
    // name instead of the passed code id, so the database version was never saved
    $t->subheader('config check_cfg');
    $cfg = new config();
    // the average calculation time is a runtime metric that is safe to overwrite;
    // remember the current value to restore it at the end of the test
    $cfg_orig = $cfg->get_db(config::AVG_CALC_TIME, $db_con);
    $cfg->set(config::AVG_CALC_TIME, '111', $db_con);
    $test_name = 'check_cfg stores the passed value under the passed code id when it differs';
    $set_done = $cfg->check_cfg(config::AVG_CALC_TIME, '222', $db_con);
    // TODO Prio 0 activate
    // $t->assert($test_name, $cfg->get_db(config::AVG_CALC_TIME, $db_con), '222');
    $test_name = 'check_cfg reports that it has stored the differing value';
    // TODO Prio 0 activate
    // $t->assert_true($test_name, $set_done);
    $test_name = 'check_cfg does not store again when the value already matches the target';
    $t->assert_false($test_name, $cfg->check_cfg(config::AVG_CALC_TIME, '222', $db_con));
    // restore the original value
    $cfg->set(config::AVG_CALC_TIME, $cfg_orig, $db_con);


    // regression test for the site wide csrf gap: a data change (a crud mask submit) must carry the
    // session token, so an attacker cannot csrf a victim into creating or changing an object
    $t->subheader('csrf session token');
    $token = 'a-valid-session-token';
    $change_submit = [
        url_var::MASK => views::WORD_ADD_ID,
        url_var::POST_SUBMIT => '',
        url_var::SESSION_TOKEN => $token
    ];
    $test_name = 'a crud submit with the matching token is permitted';
    $t->assert_true($test_name, frontend::request_token_valid($change_submit, $token));
    $missing_token = [
        url_var::MASK => views::WORD_ADD_ID,
        url_var::POST_SUBMIT => ''
    ];
    $test_name = 'a crud submit without a token is rejected';
    $t->assert_false($test_name, frontend::request_token_valid($missing_token, $token));
    $wrong_token = [
        url_var::MASK => views::WORD_ADD_ID,
        url_var::POST_SUBMIT => '',
        url_var::SESSION_TOKEN => 'forged'
    ];
    $test_name = 'a crud submit with a wrong token is rejected';
    $t->assert_false($test_name, frontend::request_token_valid($wrong_token, $token));
    $form_display = [url_var::MASK => views::WORD_ADD_ID];
    $test_name = 'opening a crud form without a submit needs no token';
    $t->assert_true($test_name, frontend::request_token_valid($form_display, $token));
    $login_forged = [
        url_var::MASK => views::LOGIN_ID,
        url_var::POST_SUBMIT => '',
        url_var::SESSION_TOKEN => 'forged'
    ];
    $test_name = 'any submit that sends a wrong token is rejected';
    $t->assert_false($test_name, frontend::request_token_valid($login_forged, $token));

}