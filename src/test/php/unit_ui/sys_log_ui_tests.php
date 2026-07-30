<?php

/*

    test/unit/html/sys_log.php - testing of the system log display functions
    --------------------------
  

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

namespace Zukunft\ZukunftCom\test\php\unit_ui;

use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\web\user\user as user_ui;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\const\icons;
use Zukunft\ZukunftCom\main\php\web\log\change_log_list as change_log_list_ui;
use Zukunft\ZukunftCom\main\php\web\log\change_log_named;
use Zukunft\ZukunftCom\main\php\web\system\sys_log_list as sys_log_list_ui;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\shared\types\phrase_types;
use Zukunft\ZukunftCom\main\php\shared\types\protection_types;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_log;
use Zukunft\ZukunftCom\test\php\create\test_sys_log;
use Zukunft\ZukunftCom\test\php\create\test_users;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class sys_log_ui_tests
{
    // a sample char limit for the change log table pure so that a long change (the description) is
    // shortened in the snapshot; matches the production default (config.yaml 'what limit') and stays
    // below the length of the test description word_names::MATH_COM so exactly that row is truncated
    const int WHAT_LIMIT_SAMPLE = 40;

    // a sample row limit for the change log table pure so that a change log longer than the limit is
    // shortened to the most recent rows in the snapshot (config.yaml 'change log > row limit')
    const int ROW_LIMIT_SAMPLE = 5;

    function run(test_cleanup $t): void
    {
        $html = new html_base();
        $t_sys = new test_sys_log($t);

        $sys_usr = new user;
        $sys_usr->load_by_id(users::SYSTEM_ID);
        $sys_usr_ui = new user_ui($sys_usr->api_json());

        // start the test section (ts)
        $ts = 'unit ui html system log ';
        $t->header($ts);

        // test the system log html display functions
        $test_page = $html->text_h2('system log display test');
        $log_lst = new sys_log_list_ui($t_sys->sys_log_list()->api_json());
        $test_page .= 'user view of a table with system log entries<br>';
        $test_page .= $log_lst->display() . '<br>';
        $test_page .= 'admin view of a table with system log entries<br>';
        $test_page .= $log_lst->display_admin($sys_usr_ui) . '<br>';

        // the invisible (borderless) change log table with the three columns when, who and what;
        // the what column is limited to the config char count (config.yaml, read like the renderer);
        // rendered in test mode so the change time stays deterministic in the snapshot
        $t_log = new test_log($t);
        $chg_lst_ui = new change_log_list_ui(
            $t_log->log_list_word_changes()->api_json(new api_type_list([api_types::TEST_MODE])));
        global $ui_sys;
        $what_max = 0;
        if ($ui_sys?->cfg !== null) {
            $what_max = (int)$ui_sys->cfg->get_by(
                [triples::WHAT_LIMIT, triples::CHANGE_LOG, words::FRONTEND, words::USER], 0);
        }
        // sort like the page renderer (ui_log::change_log_table_pure) so that the row order of the
        // changes with the same time never depends on the api row order
        $chg_lst_ui->sort_by_time_and_what();
        $chg_tbl = $chg_lst_ui->tbl_when_who_what($what_max, 0, true);
        $test_page .= 'change log table pure (borderless when / who / what)<br>';
        $test_page .= $chg_tbl . '<br>';

        // the same table rendered with a char limit so a long change (the description) triggers the
        // limit: the what column is shortened with '...' and the full change text is kept as a
        // mouseover popup (see change_log_named::what / tr_when_who_what)
        $chg_tbl_short = $chg_lst_ui->tbl_when_who_what(self::WHAT_LIMIT_SAMPLE, 0, true);
        $test_page .= 'change log table pure with char limit (shortened long what)<br>';
        $test_page .= $chg_tbl_short . '<br>';

        // a change log longer than the row limit: only the most recent rows up to the limit are shown,
        // so the change log table pure stays compact on the word and triple page (config.yaml row limit)
        $chg_lst_lng_ui = new change_log_list_ui(
            $t_log->log_list_named()->api_json(new api_type_list([api_types::TEST_MODE])));
        $chg_lst_lng_ui->sort_by_time_and_what();
        $chg_tbl_limited = $chg_lst_lng_ui->tbl_when_who_what(self::WHAT_LIMIT_SAMPLE, self::ROW_LIMIT_SAMPLE, true);
        $test_page .= 'change log table pure limited to ' . self::ROW_LIMIT_SAMPLE . ' rows (longer than the limit)<br>';
        $test_page .= $chg_tbl_limited . '<br>';

        // the same word changes as seen by a normal user: the rows of the admin-only fields
        // (the cached impact and usage numbers) are hidden (see fields::LOG_ADMIN_ONLY);
        // $t->usr_normal is used because earlier tests (e.g. system_view_ui_tests) replace
        // $t->usr1 with a test profile user, and the test profile counts as a system user
        $usr_ui = new user_ui($t->usr_normal->api_json());
        $chg_tbl_usr = $chg_lst_ui->filter_admin_fields($usr_ui)->tbl_when_who_what($what_max, 0, true);
        $test_page .= 'change log table pure for a normal user (admin-only rows hidden)<br>';
        $test_page .= $chg_tbl_usr . '<br>';

        $t->subheader($ts . 'change log table pure');

        // the prime field (the word name) is shown without a field name prefix,
        // because the log row already represents that object
        $test_name = 'name change shows no field name prefix';
        $t->assert_text_contains($test_name, $chg_tbl, 'added "' . word_names::MATH . '"');

        // any other field is prefixed with its translated name before the changed value,
        // and a type field shows the type name (resolved from the id) instead of the type number
        $test_name = 'phrase type change shows the field name and the type name';
        $t->assert_text_contains($test_name, $chg_tbl,
            'phrase type to "' . phrase_types::TIME_NAME . '" from "' . phrase_types::MEASURE_NAME . '"');

        $test_name = 'description change shows the translated field name';
        $t->assert_text_contains($test_name, $chg_tbl, 'description "' . word_names::MATH_COM . '"');

        // the cached impact number is logged like any other field and shown with the field name
        $test_name = 'an impact change shows the field name and the value';
        $t->assert_text_contains($test_name, $chg_tbl, 'added impact "0"');

        // the cached impact and usage numbers are system internals, so their change rows are
        // shown to an admin but hidden from a normal user (see fields::LOG_ADMIN_ONLY)
        $adm_usr_ui = new user_ui($t->usr_admin->api_json());
        $chg_tbl_admin = $chg_lst_ui->filter_admin_fields($adm_usr_ui)->tbl_when_who_what($what_max, 0, true);
        $test_name = 'an admin sees the impact change row';
        $t->assert_text_contains($test_name, $chg_tbl_admin, 'added impact "0"');

        // the system user covers the is_system branch of the filter
        $t_usr = new test_users();
        $sys_usr_prof_ui = new user_ui($t_usr->system_user()->api_json());
        $chg_tbl_sys_usr = $chg_lst_ui->filter_admin_fields($sys_usr_prof_ui)->tbl_when_who_what($what_max, 0, true);
        $test_name = 'a system user sees the impact change row';
        $t->assert_text_contains($test_name, $chg_tbl_sys_usr, 'added impact "0"');

        $test_name = 'a normal user does not see the impact change row';
        $t->assert_text_not_contains($test_name, $chg_tbl_usr, 'added impact');

        $test_name = 'a normal user does not see the usage change row';
        $t->assert_text_not_contains($test_name, $chg_tbl_usr, 'added usage');

        $test_name = 'a normal user still sees the other change rows';
        $t->assert_text_contains($test_name, $chg_tbl_usr, 'added "' . word_names::MATH . '"');

        // the protection is logged with the numeric type id, so the table must show the type name, not the number
        $test_name = 'protection type change shows the type name instead of the type number';
        $t->assert_text_contains($test_name, $chg_tbl,
            'protection to "' . protection_types::ADMIN_NAME . '" from "' . protection_types::NO_PROTECT_NAME . '"');

        $test_name = 'protection type change does not show the raw type number';
        $t->assert_text_not_contains($test_name, $chg_tbl, 'protection to "' . protection_types::ADMIN_ID . '"');

        // an owner (user_id) change set to the change author shows 'set owner to' and the resolved
        // author name (no quotes) instead of the raw user id
        $test_name = 'an owner change shows set owner to and the resolved owner name';
        $t->assert_text_contains($test_name, $chg_tbl, 'set owner to ' . users::SYSTEM_NAME);

        // a reference (id) field shows the referenced object's name when the change log carries it
        // (add_link_field stores the name); a change in the user sandbox is prefixed with 'user'
        $test_name = 'a user sandbox view change shows the user prefix and the view name';
        $t->assert_text_contains($test_name, $chg_tbl, 'user added view id "' . views::WORD_NAME . '"');

        // adding an empty value in the user sandbox removes the user's overwrite for that field, so it
        // is shown as 'remove user overwrite for view' instead of 'user added view id ""'
        $test_name = 'an empty user sandbox change shows the remove user overwrite text';
        $t->assert_text_contains($test_name, $chg_tbl, 'remove user overwrite for view');

        // a view change that only carries the view id (like the backend save that only knows the id)
        // resolves the view name from the cache instead of showing an empty value
        $test_name = 'a view change logged with only the id shows the view name';
        $t->assert_text_contains($test_name, $chg_tbl, 'added view id "' . views::START_NAME . '"');
        $test_name = 'no change row shows an empty view value';
        $t->assert_text_not_contains($test_name, $chg_tbl, 'view id ""');

        // the long description change is shortened with '...' and the full change text is kept as a
        // mouseover popup (title) so the user can still read the whole change
        $test_name = 'long change is shortened with a more indicator';
        $t->assert_text_contains($test_name, $chg_tbl_short, change_log_named::MORE_INDICATOR);

        $test_name = 'shortened change keeps the full text as a mouseover popup';
        $t->assert_text_contains($test_name, $chg_tbl_short, word_names::MATH_COM);

        // the who column links the user name to the user default page so a click shows that user
        $test_name = 'the who column links the user name to the user default page';
        $t->assert_text_contains($test_name, $chg_tbl, 'm=' . views::USER_ID);

        $test_name = 'the linked user name is shown as the link text';
        $t->assert_text_contains($test_name, $chg_tbl, users::SYSTEM_NAME . '</a>');

        // the change log is longer than the row limit, so the table shows only the configured rows;
        // each shown row has exactly one linked user name, so counting them counts the shown rows
        $test_name = 'the sample change log is longer than the row limit';
        $t->assert_greater($test_name, self::ROW_LIMIT_SAMPLE, $chg_lst_lng_ui->count());

        $test_name = 'the change log table pure shows only the configured number of rows';
        $t->assert($test_name, substr_count($chg_tbl_limited, users::SYSTEM_NAME . '</a>'), self::ROW_LIMIT_SAMPLE);

        // when the row limit is reached a forward button (fontawesome icon) is shown at the end
        $test_name = 'a forward button is shown when the row limit is reached';
        $t->assert_text_contains($test_name, $chg_tbl_limited, icons::PAGE_FORWARD);

        // the back button is only prepared (paging is not implemented yet), so the first page shows none
        $test_name = 'no back button is shown on the first page';
        $t->assert_text_not_contains($test_name, $chg_tbl_limited, icons::PAGE_BACK);

        // a table that shows all changes (no row limit reached) has no forward button
        $test_name = 'no forward button is shown when all changes fit';
        $t->assert_text_not_contains($test_name, $chg_tbl, icons::PAGE_FORWARD);

        $t->html_page_test($test_page, 'sys_log', 'sys_log');
    }

}