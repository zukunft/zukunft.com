<?php

/*

    test/unit/html/user.php - testing of the user html frontend functions
    -----------------------
  

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

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\shared\enum\languages;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once html_paths::CONST . 'icons.php';
include_once html_paths::EXECUTE . 'ui_log.php';
include_once html_paths::EXECUTE . 'system_form.php';
include_once html_paths::EXECUTE . 'ui_preview.php';
include_once html_paths::HELPER . 'mail_sender.php';
include_once html_paths::HELPER . 'url_mapper.php';
include_once html_paths::HTML . 'rest_call.php';
include_once html_paths::LOG . 'change_log_list.php';
include_once html_paths::USER . 'user.php';
include_once html_paths::WEB . 'frontend.php';
include_once paths::SHARED . 'api.php';
include_once paths::SHARED_CONST . 'components.php';
include_once paths::SHARED_CONST . 'sources.php';
include_once paths::SHARED_CONST . 'users.php';
include_once paths::SHARED_CONST . 'values.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_ENUM . 'change_log_actions.php';
include_once paths::SHARED_ENUM . 'change_tables.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once test_paths::CONST . 'formula_names.php';
include_once test_paths::CONST . 'triple_names.php';
include_once test_paths::CONST . 'word_names.php';
include_once test_paths::CREATE . 'test_groups.php';
include_once test_paths::CREATE . 'test_log.php';
include_once test_paths::CREATE . 'test_sys_log.php';
include_once test_paths::UNIT . 'sys_log_tests.php';

use Zukunft\ZukunftCom\main\php\web\component\execute\system_form;
use Zukunft\ZukunftCom\main\php\web\component\execute\ui_log;
use Zukunft\ZukunftCom\main\php\web\component\execute\ui_preview;
use Zukunft\ZukunftCom\main\php\web\const\icons;
use Zukunft\ZukunftCom\main\php\shared\api;
use Zukunft\ZukunftCom\main\php\web\frontend;
use Zukunft\ZukunftCom\main\php\web\helper\mail_sender;
use Zukunft\ZukunftCom\main\php\web\helper\url_mapper;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\html\rest_call;
use Zukunft\ZukunftCom\main\php\web\log\change_log_list as change_log_list_ui;
use Zukunft\ZukunftCom\main\php\web\log\change_log_named as change_log_named_ui;
use Zukunft\ZukunftCom\main\php\web\user\user as user_ui;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\components;
use Zukunft\ZukunftCom\main\php\shared\const\sources;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\main\php\shared\const\values;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\enum\change_log_actions;
use Zukunft\ZukunftCom\main\php\shared\enum\change_tables;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\helper\Config;
use Zukunft\ZukunftCom\test\php\const\formula_names;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_formulas;
use Zukunft\ZukunftCom\test\php\create\test_groups;
use Zukunft\ZukunftCom\test\php\create\test_log;
use Zukunft\ZukunftCom\test\php\create\test_sys_log;
use Zukunft\ZukunftCom\test\php\create\test_users;
use Zukunft\ZukunftCom\test\php\unit\sys_log_tests;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class user_ui_tests
{
    function run(test_cleanup $t): void
    {
        global $mtr;

        $t_sys = new test_sys_log($t);
        $t_log = new test_log($t);
        $t_frm = new test_formulas($t);
        $t_grp = new test_groups($t);
        $t_usr = new test_users();
        $log = new ui_log();
        $msg = new user_message();

        $base_url = THIS_URL;
        $lan = languages::DEFAULT;
        $url_arr = [url_var::MASK => views::WORD_ID, url_var::ID => word_names::ZH_ID];

        // start the test section (ts)
        $ts = 'unit ui html user ';
        $t->header($ts);

        $usr_ui = new user_ui($t_usr->user_sys_test()->api_json());
        $test_page = $usr_ui->form_edit([url_var::MASK => views::PHRASE_ID, url_var::ID => 1]) . '<br>';

        $t->subheader($ts . 'system errors');

        $test_name = 'the open system errors related to the user are listed';
        $err_html = $log->user_system_errors($t_sys->list_for_user_ui($msg), $msg, msg_id::USER_SYSTEM_ERRORS);
        $t->assert_text_contains($test_name, $err_html, sys_log_tests::TV_LOG_TEXT);
        $test_page .= $err_html . '<br>';

        $test_name = 'the error list is limited to the most relevant entries';
        $t->assert_text_not_contains($test_name, $t_sys->list_for_user_ui($msg)->head(1)->get_html($msg), sys_log_tests::T2_LOG_TEXT);

        $test_name = 'without an open system error the user gets the no-error message';
        $err_html = $log->user_system_errors($t_sys->list_for_user_empty_ui(), $msg, msg_id::USER_SYSTEM_ERRORS);
        $t->assert_text_contains($test_name, $err_html, $mtr->txt(msg_id::USER_SYSTEM_ERRORS_NONE));

        $t->subheader($ts . 'all user overwrites');

        // a user loaded for its page carries the changes of the user like a word carries its
        // change log, so the fixed column can list the sandbox overwrites of the shown user
        $test_name = 'the sandbox view overwrite of the shown user is listed';
        $usr_sys_ui = new user_ui($t->usr_system->api_json());
        $usr_sys_ui->chg_log = $t_log->log_list_word_changes_ui();
        $chg_html = $log->all_user_overwrites($usr_sys_ui, new change_log_list_ui(), $msg, true, msg_id::ALL_USER_OVERWRITES);
        $t->assert_text_contains($test_name, $chg_html, views::WORD_NAME);
        $test_page .= $chg_html . '<br>';

        $test_name = 'the normal table changes of other objects are not listed as overwrites';
        $t->assert_text_not_contains($test_name, $chg_html, word_names::MATH);

        // the column lists the overwrites of every object type, not only the word overwrites, so
        // that a user sees all changes on one page (the same filter by the user sandbox tables,
        // see change_log_list::filter_user_overwrites and change_tables::USER_TABLES)
        $usr_sys_ui->chg_log = $t_log->log_list_user_overwrites_ui();
        $all_html = $log->all_user_overwrites(
            $usr_sys_ui, new change_log_list_ui(), $msg, true, msg_id::ALL_USER_OVERWRITES, $url_arr);
        $test_name = 'the word overwrite of the shown user is listed';
        $t->assert_text_contains($test_name, $all_html, views::WORD_NAME);
        $test_name = 'the triple overwrite of the shown user is listed';
        $t->assert_text_contains($test_name, $all_html, triple_names::MATH_CONST_COM);
        $test_name = 'the formula overwrite of the shown user is listed';
        $t->assert_text_contains($test_name, $all_html, test_log::FORMULA_OVERWRITE_COM);
        $test_name = 'the formula link overwrite of the shown user is listed';
        $t->assert_text_contains($test_name, $all_html,
            (string)test_log::FORMULA_LINK_OVERWRITE_ORDER_NBR);
        $test_name = 'the value overwrite of the shown user is listed';
        $t->assert_text_contains($test_name, $all_html, (string)values::SAMPLE_INT);
        $test_name = 'the component overwrite of the shown user is listed';
        $t->assert_text_contains($test_name, $all_html, test_log::COMPONENT_OVERWRITE_COM);
        $test_name = 'the view overwrite of the shown user is listed';
        $t->assert_text_contains($test_name, $all_html, test_log::VIEW_OVERWRITE_COM);
        $test_name = 'the source overwrite of the shown user is listed';
        $t->assert_text_contains($test_name, $all_html, test_log::SOURCE_OVERWRITE_COM);
        $test_name = 'the ref overwrite of the shown user is listed';
        $t->assert_text_contains($test_name, $all_html, test_log::REF_OVERWRITE_KEY);
        $test_name = 'the standard table change is not listed beside the overwrites';
        $t->assert_text_not_contains($test_name, $all_html, word_names::TEST_RENAMED);

        // the column lists the changes of more than one object, so the change text alone does not
        // tell the user which object has been changed and the what column names the object first;
        // the name comes first, so that it survives the shortening of the what column
        // the name is a link to the default page of the object, so the name and the separator are
        // not adjacent in the html but separated by the end of the link (see object_link)
        $name_end = '>';
        $link_end = '</a>' . change_log_named_ui::OBJECT_SEPARATOR;
        $test_name = 'the what column names the changed triple';
        $t->assert_text_contains($test_name, $all_html,
            $name_end . triple_names::MATH_CONST . $link_end);
        $test_name = 'the what column names the changed word';
        $t->assert_text_contains($test_name, $all_html,
            $name_end . word_names::MATH . $link_end);
        $test_name = 'the what column names the changed formula';
        $t->assert_text_contains($test_name, $all_html,
            $name_end . formula_names::INCREASE . $link_end);
        // a link has no name column, so its name is built from both linked objects; the formula
        // name is asserted separately, because it was dropped by the link name before
        $test_name = 'the what column names the changed formula link';
        $t->assert_text_contains($test_name, $all_html,
            $name_end . $t_frm->formula_link()->name() . $link_end);
        $test_name = '... including the linked formula';
        $t->assert_text_contains($test_name, $all_html, formula_names::SCALE_TO_SEC);
        // a value has no name column either, so the what column names it by the group of phrases
        $test_name = 'the what column names the changed value';
        $t->assert_text_contains($test_name, $all_html,
            $name_end . $t_grp->group()->name() . $link_end);
        $test_name = 'the what column names the changed component';
        $t->assert_text_contains($test_name, $all_html,
            $name_end . components::MATRIX_NAME . $link_end);
        $test_name = 'the what column names the changed view';
        $t->assert_text_contains($test_name, $all_html,
            $name_end . views::START_NAME . $link_end);
        $test_name = 'the what column names the changed source';
        $t->assert_text_contains($test_name, $all_html,
            $name_end . sources::SIB . $link_end);
        // a ref has no name column either, so it is named by the phrase and the type that it links;
        // esc() keeps the quotes of the name, because it escapes with ENT_NOQUOTES
        $test_name = 'the what column names the changed ref';
        $t->assert_text_contains($test_name, $all_html,
            $name_end . test_log::REF_OVERWRITE_NAME . $link_end);

        $test_name = 'the object name is not cut off by the what column limit';
        $t->assert_text_contains($test_name, $all_html,
            $name_end . triple_names::MATH_CONST . $link_end . $mtr->txt(msg_id::LOG_ADD));

        // the column lists the changes of every object type, so it names the type of each changed
        // object, which the old user page showed as one table per type (see docs/llm/pending.md)
        $test_name = 'the column has a type header';
        $t->assert_text_contains($test_name, $all_html, $mtr->txt(msg_id::CHANGE_LOG_TBL_TYPE));
        $test_name = 'the type of a changed word is named';
        $t->assert_text_contains($test_name, $all_html, $mtr->text_db_table(change_tables::WORD));
        $test_name = 'the type of a changed value is named';
        $t->assert_text_contains($test_name, $all_html, $mtr->text_db_table(change_tables::VALUE));
        $test_name = 'the type of a changed view is named';
        $t->assert_text_contains($test_name, $all_html, $mtr->text_db_table(change_tables::VIEW));
        $test_name = 'the type of a changed ref is named';
        $t->assert_text_contains($test_name, $all_html, $mtr->text_db_table(change_tables::REF));
        // the user sandbox change of a word is a change of a word and not of a 'user word'
        $test_name = 'the type names the object and not the user sandbox table';
        $t->assert_text_not_contains($test_name, $all_html, $mtr->text_db_table(change_tables::WORD_USR));

        // an object page shows the changes of one object, so there the type is already known
        $test_name = 'the changes tab of an object page has no type column';
        $one_obj_html = $log->change_log_table_pure(
            $usr_sys_ui, $t_log->log_list_word_changes_ui(), $msg, true);
        $t->assert_text_not_contains($test_name, $one_obj_html, $mtr->txt(msg_id::CHANGE_LOG_TBL_TYPE));

        // the name of the changed object links to the default page of the object, so that the user
        // can open it from the user page instead of having to search it by name
        $test_name = 'the name of the changed word links to the word default page';
        $t->assert_text_contains($test_name, $all_html,
            url_var::MASK . '=' . views::WORD_ID . '&amp;' . url_var::ID . '=' . word_names::MATH_ID
            . '"' . $name_end . word_names::MATH . $link_end);
        $test_name = 'the name of the changed value links to the value default page';
        $t->assert_text_contains($test_name, $all_html,
            url_var::MASK . '=' . views::VALUE_DEFAULT_ID . '&amp;' . url_var::ID . '=' . values::PI_ID);

        // like the two columns of the 'my' tab of an object page the column shows the user value
        // beside the value of the shared standard object, so that the user sees what the overwrite
        // changes (the standard value is added by change_log_list::load_changed_objects)
        // esc() escapes with ENT_NOQUOTES, so the quotes around a value stay plain in the html
        $test_name = 'the what column shows the standard value beside the user value';
        $t->assert_text_contains($test_name, $all_html,
            $mtr->txt(msg_id::LOG_INSTEAD_OF) . ' "' . components::WORD_COM . '"');
        $test_name = 'a change without a standard value shows no comparison';
        $t->assert_text_not_contains($test_name, $all_html,
            test_log::VIEW_OVERWRITE_COM . '" ' . $mtr->txt(msg_id::LOG_INSTEAD_OF));

        // each row that can be undone gets an undo icon, so that the user can reset an overwrite
        // from the user page instead of having to open the 'my' tab of each object; the link is
        // the same view.php confirm url as the undo icon of the 'my' tab (see url_to_action)
        $test_name = 'the overwrite rows have an undo icon';
        $t->assert_text_contains($test_name, $all_html, icons::UNDO);
        $test_name = 'the undo icon of the word overwrite opens the word edit view';
        $t->assert_text_contains($test_name, $all_html,
            url_var::MASK . '=' . views::WORD_EDIT_ID . '&amp;' . url_var::ID . '=' . word_names::MATH_ID);
        $test_name = 'the undo icon sets the overwritten field back to the value before the change';
        $t->assert_text_contains($test_name, $all_html,
            url_var::VIEW . '=&amp;' . url_var::PRE . url_var::VIEW . '=' . views::WORD_ID);
        $test_name = 'the undo icon asks the user to confirm the change';
        $t->assert_text_contains($test_name, $all_html,
            url_var::STEP . '=' . url_var::STEP_CONFIRM);
        // a value is keyed by the group id, so its undo link uses the group id and not a row id
        $test_name = 'the undo icon of the value overwrite opens the value edit view';
        $t->assert_text_contains($test_name, $all_html,
            url_var::MASK . '=' . views::VALUE_EDIT_ID . '&amp;' . url_var::ID . '=' . values::PI_ID);
        $t->assert_text_contains($test_name, $all_html,
            url_var::NUMERIC_VALUE . '=&amp;' . url_var::PRE . url_var::NUMERIC_VALUE
            . '=' . values::SAMPLE_INT);

        // beside the undo icon each row has an icon that opens the 'others' tab of the changed
        // object, which lists what the other users have set for the same object; the fragment is
        // the tab id that html_base::tab_box gives the tab, so both must use tab_id
        $others_tab = new html_base()->tab_id($mtr->txt(msg_id::FORM_SUB_TITLE_OTHERS));
        $test_name = 'the overwrite rows have an icon to the overwrites of the other users';
        $t->assert_text_contains($test_name, $all_html, icons::OTHERS);
        $test_name = 'the others icon of the word overwrite opens the others tab of the word page';
        $t->assert_text_contains($test_name, $all_html,
            url_var::MASK . '=' . views::WORD_ID . '&amp;' . url_var::ID . '=' . word_names::MATH_ID
            . '#' . $others_tab);
        $test_page .= $all_html . '<br>';

        // the column can add the undo icon, the icon to the values of the other users, these values
        // inline or nothing at all, so each case gets an own html snapshot to show the difference
        // (see change_log_actions and change_log_list::tbl_when_who_what)
        $usr_sys_ui->chg_log = $t_log->log_list_user_overwrites_ui();
        $plain_page = $log->all_user_overwrites(
            $usr_sys_ui, new change_log_list_ui(), $msg, true, msg_id::ALL_USER_OVERWRITES,
            $url_arr, []);
        $test_name = 'without an action the column shows no undo icon';
        $t->assert_text_not_contains($test_name, $plain_page, icons::UNDO);
        $test_name = '... and no icon to the values of the other users';
        $t->assert_text_not_contains($test_name, $plain_page, icons::OTHERS);
        $t->html_page_test($plain_page, 'user', 'user_log_plain', $msg, $base_url, $lan);

        $undo_page = $log->all_user_overwrites(
            $usr_sys_ui, new change_log_list_ui(), $msg, true, msg_id::ALL_USER_OVERWRITES,
            $url_arr, [change_log_actions::UNDO]);
        $test_name = 'with the undo action the column shows the undo icon';
        $t->assert_text_contains($test_name, $undo_page, icons::UNDO);
        $test_name = '... but no icon to the values of the other users';
        $t->assert_text_not_contains($test_name, $undo_page, icons::OTHERS);
        $t->html_page_test($undo_page, 'user', 'user_log_undo', $msg, $base_url, $lan);

        $others_page = $log->all_user_overwrites(
            $usr_sys_ui, new change_log_list_ui(), $msg, true, msg_id::ALL_USER_OVERWRITES,
            $url_arr, [change_log_actions::OTHERS_LINK]);
        $test_name = 'with the others link action the column shows the icon of the other users';
        $t->assert_text_contains($test_name, $others_page, icons::OTHERS);
        $test_name = '... but no undo icon';
        $t->assert_text_not_contains($test_name, $others_page, icons::UNDO);
        $t->html_page_test($others_page, 'user', 'user_log_others_link', $msg, $base_url, $lan);

        // only the word overwrite carries the values of the other users, so the inline column shows
        // them on that row and stays empty on the others; more users than OTHERS_MAX_INLINE are set
        // (see test_log::OTHER_VALUES), so that the snapshot also shows how the column indicates
        // the users that it does not name
        $inline_page = $log->all_user_overwrites(
            $usr_sys_ui, new change_log_list_ui(), $msg, true, msg_id::ALL_USER_OVERWRITES,
            $url_arr, [change_log_actions::OTHERS_INLINE]);
        $test_name = 'with the others inline action the column names the other users and the values';
        $t->assert_text_contains($test_name, $inline_page, array_key_first(test_log::OTHER_VALUES));
        $t->assert_text_contains($test_name, $inline_page, test_log::OTHER_VALUES[array_key_first(test_log::OTHER_VALUES)]);
        $test_name = '... and indicates the users above the inline limit';
        $t->assert_text_contains($test_name, $inline_page, change_log_named_ui::MORE_INDICATOR);
        $t->html_page_test($inline_page, 'user', 'user_log_others_inline', $msg, $base_url, $lan);

        // with the grouping the column shows one table per object type with the type as its header,
        // the type of the newest change first, like the retired user_display_old page did
        $grouped_page = $log->all_user_overwrites(
            $usr_sys_ui, new change_log_list_ui(), $msg, true, msg_id::ALL_USER_OVERWRITES,
            $url_arr, [change_log_actions::GROUP_BY_TYPE, change_log_actions::UNDO]);
        // the column sorts the changes newest first before it splits them by type, so the expected
        // groups are taken from the sorted list; without the sort the groups would be in the order
        // in which the test builder added them, which is the oldest change first
        $grouped_lst = $t_log->log_list_user_overwrites_ui()->filter_user_overwrites($usr_sys_ui);
        $grouped_lst->sort_by_time_and_what(true);
        $groups = $grouped_lst->split_by_object_type();
        // the heading tag depends on the bootstrap setting, so the expected html is built with the
        // same function that the renderer uses
        $head_html = new html_base();
        $test_name = 'the grouped column has a table header per object type';
        foreach (array_keys($groups) as $type_name) {
            $t->assert_text_contains($test_name, $grouped_page, $head_html->text_h4($type_name));
        }
        // all test changes have the same change time, so the newest is the one with the highest
        // change log id, which is the reference overwrite added last by log_list_user_overwrites
        $test_name = 'the type of the newest change is the first group';
        $newest_type = $grouped_lst->lst()[0]->object_type();
        $first_pos = strpos($grouped_page, $head_html->text_h4($newest_type));
        foreach (array_keys($groups) as $type_name) {
            $t->assert_true($test_name,
                $first_pos <= strpos($grouped_page, $head_html->text_h4($type_name)));
        }
        // the header of each table names the type, so a type column would only repeat it
        $test_name = 'the grouped column has no type column';
        $t->assert_text_not_contains($test_name, $grouped_page, $mtr->txt(msg_id::CHANGE_LOG_TBL_TYPE));
        $test_name = '... but still the requested undo icon';
        $t->assert_text_contains($test_name, $grouped_page, icons::UNDO);
        $t->html_page_test($grouped_page, 'user', 'user_log_grouped', $msg, $base_url, $lan);

        // a user can have far more overwrites than a page should show (over 15'000 for the system
        // user), so the list is cut to the configured number of rows before the rows are prepared
        // and the newest overwrites are the ones that are shown
        $usr_sys_ui->chg_log = $t_log->log_list_many_user_overwrites_ui();
        $many_html = $log->all_user_overwrites($usr_sys_ui, new change_log_list_ui(), $msg, true, msg_id::ALL_USER_OVERWRITES);
        // the same limit and the same fallback as ui_log::configured_row_limit, because the unit
        // tests use an empty frontend config, so that here the fallback is the effective limit
        global $ui_sys;
        $row_limit = config::ROW_LIMIT;
        if ($ui_sys?->cfg !== null) {
            $row_limit = (int)$ui_sys->cfg->get_by(
                [triples::ROW_LIMIT, triples::CHANGE_LOG, words::FRONTEND, words::USER],
                $msg, config::ROW_LIMIT);
        }
        $test_name = 'the newest overwrite is shown';
        $t->assert_text_contains($test_name, $many_html,
            test_log::OVERWRITE_VALUE . test_log::MANY_OVERWRITES);
        $test_name = 'the oldest overwrite above the configured row limit is not shown';
        $t->assert_text_not_contains($test_name, $many_html, test_log::OVERWRITE_VALUE . '01');
        // one table row per shown change plus the header row and the paging row that tells the
        // user that more changes exist
        $test_name = 'the change log table shows the configured number of rows';
        $t->assert($test_name, substr_count($many_html, '<' . html_base::TR . '>'), $row_limit + 2);

        // on an object page the object is named by the page itself, so the change log there must
        // not repeat the object name in every row; the object is only named where it is requested
        $test_name = 'by default a change does not name the changed object';
        $plain_what = '';
        foreach ($t_log->log_list_user_overwrites_ui()->lst() as $chg_ui) {
            $plain_what .= $chg_ui->what_text();
        }
        $t->assert_text_not_contains($test_name, $plain_what, change_log_named_ui::OBJECT_SEPARATOR);

        $test_name = 'a user without changes gets the no-changes message';
        $none_html = $log->all_user_overwrites($usr_ui, new change_log_list_ui(), $msg, true, msg_id::ALL_USER_OVERWRITES);
        $t->assert_text_contains($test_name, $none_html, $mtr->txt(msg_id::ALL_USER_OVERWRITES_NONE));
        $test_page .= $none_html . '<br>';

        $t->subheader($ts . 'profile rights');

        // the admin-only fields (the cached usage and impact numbers) are shown to a developer
        // but never to a user without an elevated profile (see user::sees_admin_fields)
        $test_name = 'a developer user has developer rights';
        $dev_ui = new user_ui($t->usr_dev->api_json());
        $t->assert_true($test_name, $dev_ui->is_developer());

        $test_name = 'a developer sees the admin-only fields';
        $t->assert_true($test_name, $dev_ui->sees_admin_fields());

        $test_name = 'an ip only user has no developer rights';
        $ip_ui = new user_ui($t_usr->user_ip()->api_json());
        $t->assert_false($test_name, $ip_ui->is_developer());

        $test_name = 'an ip only user does not see the admin-only fields';
        $t->assert_false($test_name, $ip_ui->sees_admin_fields());

        // the two normal test users carry the test profile only for the backend write privileges;
        // for the frontend display they act like a normal user (see user::is_system), so most test
        // pages render without the admin-only fields
        $test_name = 'the normal test user is not a system user for the display';
        $usr1_ui = new user_ui($t->usr1->api_json());
        $t->assert_false($test_name, $usr1_ui->is_system());

        $test_name = 'the normal test user does not see the admin-only fields';
        $t->assert_false($test_name, $usr1_ui->sees_admin_fields());

        // the test profile keeps the admin mask access (frontend::admin_mask_denied), because the
        // system view tests render the admin masks as the test user; only the display acts normal;
        // the user comes from the factory and not from $t->usr1, because $t->usr1 carries the test
        // profile only when it has been loaded from the database and the email profile when it is
        // the dummy of the unit tests (see docs/llm/testing.md)
        $test_name = 'a user with the test profile uses the admin masks like a system user';
        $sys_test_ui = new user_ui($t_usr->user_sys_test()->api_json());
        $t->assert_true($test_name, $sys_test_ui->is_system_test());

        $test_name = 'an ip only user is not a system test user';
        $t->assert_false($test_name, $ip_ui->is_system_test());

        $test_name = 'the system user itself keeps the system rights';
        $sys_ui = new user_ui($t_usr->system_user()->api_json());
        $t->assert_true($test_name, $sys_ui->is_system());

        $test_name = 'the system user sees the admin-only fields';
        $t->assert_true($test_name, $sys_ui->sees_admin_fields());

        $t->subheader($ts . 'api url');

        // the frontend asks the backend for the sandbox of the logged-in user (see user::data_user)
        $rest = new rest_call();
        $test_name = 'the api url carries the id of the logged-in user';
        $dev_data = $rest->data_with_user([], new user_message($dev_ui));
        $t->assert($test_name, $dev_data[url_var::USER] ?? 0, users::DEV_ID);

        $test_name = 'a data user named by the caller is kept';
        $adm_data = $rest->data_with_user([url_var::USER => users::SYSTEM_ADMIN_ID], new user_message($dev_ui));
        $t->assert($test_name, $adm_data[url_var::USER], users::SYSTEM_ADMIN_ID);

        $test_name = 'the api url of an ip only user carries no user id';
        $ip_data = $rest->data_with_user([], new user_message($ip_ui));
        $t->assert_false($test_name, array_key_exists(url_var::USER, $ip_data));

        $test_name = 'the api url without a user carries no user id';
        $t->assert_false($test_name, array_key_exists(url_var::USER, $rest->data_with_user([], $msg)));

        $t->subheader($ts . 'page url');

        // the url builders read the session user from the request cache (see html_base::url_with_user)
        global $ui_sys;
        $html = new html_base();
        $page_url = api::MAIN_SCRIPT . url_var::PAR . url_var::MASK . url_var::EQ . views::WORD_ID;
        $usr_url = url_var::USER . url_var::EQ . users::DEV_ID;
        $usr_keep = $ui_sys->usr ?? null;
        $ui_sys->usr = $dev_ui;
        $test_name = 'the page url carries the id of the logged-in user';
        $t->assert($test_name, $html->url_with_user($page_url), $page_url . url_var::ADD . $usr_url);
        $test_name = '... also as the only parameter';
        $t->assert($test_name, $html->url_with_user(api::MAIN_SCRIPT), api::MAIN_SCRIPT . url_var::PAR . $usr_url);
        $test_name = '... which replaces the user id of another user';
        $other_url = $page_url . url_var::ADD . url_var::USER . url_var::EQ . users::SYSTEM_ADMIN_ID;
        $t->assert($test_name, $html->url_with_user($other_url), $page_url . url_var::ADD . $usr_url);
        $test_name = '... in front of the in-page target';
        $anchor = url_var::ANCHOR . 'tab';
        $t->assert($test_name, $html->url_with_user($page_url . $anchor), $page_url . url_var::ADD . $usr_url . $anchor);
        $test_name = '... and in the link';
        $t->assert_text_contains($test_name, $html->ref($page_url, word_names::MATH), $usr_url);
        $test_name = '... and in the form';
        $t->assert_text_contains($test_name, $html->form_start(views::WORD_EDIT), $html->form_hidden(url_var::USER, (string)users::DEV_ID));
        $test_name = 'the back key and a value u are not taken as the user id';
        $back_url = $page_url . url_var::ADD . url_var::BACK . $usr_url . url_var::ADD . url_var::ACTION . url_var::EQ . url_var::CRUD_UPDATE;
        $t->assert($test_name, $html->url_with_user($back_url), $back_url . url_var::ADD . $usr_url);
        $test_name = 'an in-page anchor gets no user id';
        $t->assert($test_name, $html->url_with_user($anchor), $anchor);
        $test_name = 'the url of another site gets no user id';
        $ext_url = 'https://www.wikidata.org' . api::MAIN_SCRIPT;
        $t->assert($test_name, $html->url_with_user($ext_url), $ext_url);
        $test_name = 'the url of another script gets no user id';
        $t->assert($test_name, $html->url_with_user(api::ABOUT_SCRIPT), api::ABOUT_SCRIPT);
        $ui_sys->usr = $ip_ui;
        $test_name = 'the page url of an ip only user carries no user id';
        $t->assert($test_name, $html->url_with_user($page_url), $page_url);
        $test_name = '... and the form of an ip only user has no user field';
        $t->assert($test_name, $html->form_user(), '');
        // the system users see the standard data by default, so their urls name no user
        $ui_sys->usr = $sys_ui;
        $test_name = 'the page url of the system user carries no user id';
        $t->assert($test_name, $html->url_with_user($page_url), $page_url);
        $ui_sys->usr = $sys_test_ui;
        $test_name = 'the page url of the system test user carries no user id, so the test pages show none';
        $t->assert($test_name, $html->url_with_user($page_url), $page_url);
        $ui_sys->usr = $usr_keep;
        $test_name = 'a developer does not see only the standard data';
        $t->assert_false($test_name, $dev_ui->uses_standard_data());
        $test_name = 'an ip user sees the standard data, because it is not unique enough for a sandbox';
        $t->assert_true($test_name, $ip_ui->uses_standard_data());
        $test_name = 'the system user sees the standard data';
        $t->assert_true($test_name, $sys_ui->uses_standard_data());
        $test_name = 'the api url of the system user carries no user id';
        $t->assert_false($test_name, array_key_exists(url_var::USER, $rest->data_with_user([], new user_message($sys_ui))));

        // the page of a logged in user shows the user name, the logout link and the black add and
        // edit icons, so it is never stored as or taken from the shared page cache; the requesting
        // user decides, because a render without a php session (a workflow test or the page refresh
        // job) has no session flag and would else share the page of one user with everybody else
        $test_name = 'the page of a logged in user is personal';
        $t->assert_true($test_name, frontend::shows_personal_page(new user_message($dev_ui)));
        $test_name = 'the page of an ip user is the shared page';
        $t->assert_false($test_name, frontend::shows_personal_page(new user_message($ip_ui)));
        $test_name = 'the page of a request without a user is the shared page';
        $t->assert_false($test_name, frontend::shows_personal_page(new user_message()));

        // after an action the next page is created for the user of the session after the action
        $act_url = [url_var::MASK => views::WORD_ID, url_var::USER => users::SYSTEM_ADMIN_ID];
        $test_name = 'the redirect after an action carries the logged-in user';
        $t->assert_text_contains($test_name, frontend::redirect_url($act_url, new user_message($dev_ui)), $usr_url);
        $test_name = '... and not the posted user';
        $t->assert_text_not_contains($test_name,
            frontend::redirect_url($act_url, new user_message($dev_ui)), url_var::USER . url_var::EQ . users::SYSTEM_ADMIN_ID);
        $test_name = 'the redirect after a logout carries no user';
        $t->assert_text_not_contains($test_name, frontend::redirect_url($act_url, $msg), url_var::ADD . url_var::USER . url_var::EQ);

        // the page is always created for the session user, so a link of another user only gets a notice
        $test_name = 'a url with the id of the session user needs no notice';
        $t->assert_true($test_name, frontend::url_user_matches([url_var::USER => users::DEV_ID], new user_message($dev_ui)));
        $test_name = 'a url without a user needs no notice';
        $t->assert_true($test_name, frontend::url_user_matches([url_var::MASK => views::WORD_ID], new user_message($dev_ui)));
        $test_name = 'a url with the id of another user gets the notice';
        $other_msg = new user_message($dev_ui);
        $t->assert_false($test_name, frontend::url_user_matches([url_var::USER => users::SYSTEM_ADMIN_ID], $other_msg));
        $test_name = '... which names the user of the url';
        $t->assert_text_contains($test_name, $other_msg->text(), (string)users::SYSTEM_ADMIN_ID);
        $test_name = '... and does not stop the request';
        $t->assert_true($test_name, $other_msg->is_ok());

        // the user to show or change is not the logged-in user, so it has its own url var
        $test_name = 'the user edit form names the user to change by user_to_edit';
        $edit_html = $usr_ui->form_edit();
        $t->assert_text_contains($test_name, $edit_html, $html->form_hidden(url_var::USER_TO_EDIT, (string)$usr_ui->id()));
        $test_name = '... and posts the name as the username';
        $t->assert_text_contains($test_name, $edit_html, html_base::NAME . '="' . url_var::USERNAME . '"');
        $test_name = 'the user to edit selects the user';
        $edit_usr = new user_ui();
        $edit_usr->url_mapper([url_var::USER_TO_EDIT => users::DEV_ID], $msg);
        $t->assert($test_name, $edit_usr->id(), users::DEV_ID);
        $msg->reset();
        $test_name = '... but the logged-in user of the url does not';
        $edit_usr = new user_ui();
        $edit_usr->url_mapper([url_var::USER => users::DEV_ID, url_var::ID => users::SYSTEM_ADMIN_ID], $msg);
        $t->assert($test_name, $edit_usr->id(), users::SYSTEM_ADMIN_ID);
        $msg->reset();

        // the signup and the reset mail open the activation page for the user of the mail
        $test_name = 'the activation link names the user by user_to_edit';
        $act_url = frontend::activation_url(users::DEV_ID, users::TEST_USER_ACTIVATION_KEY);
        $t->assert_text_contains($test_name, $act_url, url_var::PAR . url_var::USER_TO_EDIT . url_var::EQ . users::DEV_ID);
        $test_name = '... and carries the key';
        $t->assert_text_contains($test_name, $act_url, url_var::ADD . url_var::POST_KEY . url_var::EQ . users::TEST_USER_ACTIVATION_KEY);
        $test_name = '... and opens the activation page';
        $t->assert_text_contains($test_name, $act_url, api::LOGIN_ACTIVATE_FORWARD . url_var::PAR);
        $test_name = '... but not by the id';
        $t->assert_text_not_contains($test_name, $act_url, url_var::PAR . url_var::ID . url_var::EQ);

        $t->subheader($ts . 'user to edit url');

        // the views that show or change a user select it by user_to_edit, all other views by the id
        $test_name = 'the admin user edit view selects the user by user_to_edit';
        $t->assert($test_name, url_var::id_var(views::USER_ADMIN_EDIT_ID), url_var::USER_TO_EDIT);
        $test_name = '... also if the view is given by its code id';
        $t->assert($test_name, url_var::id_var(views::USER_ADMIN_EDIT), url_var::USER_TO_EDIT);
        $test_name = 'the user page selects the user by user_to_edit';
        $t->assert($test_name, url_var::id_var(views::USER_ID), url_var::USER_TO_EDIT);
        $test_name = 'the admin user add view selects the user by user_to_edit';
        $t->assert($test_name, url_var::id_var(views::USER_ADMIN_ADD_ID), url_var::USER_TO_EDIT);
        $test_name = 'the admin user delete view selects the user by user_to_edit';
        $t->assert($test_name, url_var::id_var(views::USER_ADMIN_DEL_ID), url_var::USER_TO_EDIT);
        $test_name = 'the jobs of the logged-in user select no user to edit';
        $t->assert($test_name, url_var::id_var(views::USER_JOBS_ID), url_var::ID);
        $test_name = 'another view selects its object by the id';
        $t->assert($test_name, url_var::id_var(views::WORD_ID), url_var::ID);
        $test_name = 'a view list in the url selects by the id';
        $t->assert($test_name, url_var::id_var([views::USER_ADMIN_EDIT_ID]), url_var::ID);
        $test_name = 'the user page url names its user by user_to_edit';
        $t->assert($test_name, url_var::object_id([url_var::MASK => views::USER_ID, url_var::USER_TO_EDIT => users::DEV_ID]), users::DEV_ID);
        $test_name = '... or by the id of an old link';
        $t->assert($test_name, url_var::object_id([url_var::MASK => views::USER_ID, url_var::ID => users::DEV_ID]), users::DEV_ID);
        $test_name = '... but another view is not selected by the user to edit';
        $t->assert($test_name, url_var::object_id([url_var::MASK => views::WORD_ID, url_var::USER_TO_EDIT => users::DEV_ID]), 0);
        $test_name = 'a url without an object names no object';
        $t->assert($test_name, url_var::object_id([url_var::MASK => views::USER_ID]), 0);

        $adm_url = api::MAIN_SCRIPT . url_var::PAR . url_var::MASK . url_var::EQ . views::USER_ADMIN_EDIT_ID;
        $test_name = 'the link to the admin user edit names the user by user_to_edit';
        $adm_id_url = $adm_url . url_var::ADD . url_var::ID . url_var::EQ . users::DEV_ID;
        $adm_ue_url = $adm_url . url_var::ADD . url_var::USER_TO_EDIT . url_var::EQ . users::DEV_ID;
        $t->assert($test_name, $html->url_with_id_var($adm_id_url), $adm_ue_url);
        $test_name = 'the link to the user page names the user by user_to_edit';
        $usr_link = $html->ref_view(views::USER_ID, users::DEV_ID, users::DEV_NAME);
        $t->assert_text_contains($test_name, $usr_link, url_var::USER_TO_EDIT . url_var::EQ . users::DEV_ID);
        $test_name = '... and not by the id';
        $t->assert_text_not_contains($test_name, $usr_link, url_var::ADD . url_var::ID . url_var::EQ);
        $test_name = '... but the link to another view keeps the id';
        $wrd_id_url = $page_url . url_var::ADD . url_var::ID . url_var::EQ . word_names::MATH_ID;
        $t->assert($test_name, $html->url_with_id_var($wrd_id_url), $wrd_id_url);
        $test_name = '... and the back id to the admin user edit stays the back id';
        $back_id_url = $page_url . url_var::ADD . url_var::BACK . url_var::MASK . url_var::EQ . views::USER_ADMIN_EDIT_ID
            . url_var::ADD . url_var::BACK . url_var::ID . url_var::EQ . users::DEV_ID;
        $t->assert($test_name, $html->url_with_id_var($back_id_url), $back_id_url);
        $test_name = 'the redirect to the admin user edit names the user by user_to_edit';
        $adm_arr = [url_var::MASK => views::USER_ADMIN_EDIT_ID, url_var::ID => users::DEV_ID];
        $t->assert_text_contains($test_name, frontend::redirect_url($adm_arr, $msg), url_var::USER_TO_EDIT . url_var::EQ . users::DEV_ID);
        $test_name = 'the admin user edit form posts the user as user_to_edit';
        $form = new system_form();
        $t->assert_text_contains($test_name, $form->form_back(views::USER_ADMIN_EDIT_ID, users::DEV_ID),
            html_base::NAME . '="' . url_var::USER_TO_EDIT . '"');

        $url_map = new url_mapper();
        $test_name = 'the user_to_edit of the admin user edit url loads the user by the id';
        $std_arr = $url_map->url_to_standard([url_var::MASK => views::USER_ADMIN_EDIT_ID, url_var::USER_TO_EDIT => users::DEV_ID], $msg);
        $t->assert($test_name, $std_arr[url_var::ID] ?? 0, users::DEV_ID);
        $test_name = '... and is not kept beside the id';
        $t->assert_false($test_name, array_key_exists(url_var::USER_TO_EDIT, $std_arr));
        $test_name = 'the user_to_edit of another view does not replace the id';
        $std_arr = $url_map->url_to_standard(
            [url_var::MASK => views::WORD_ID, url_var::ID => word_names::MATH_ID, url_var::USER_TO_EDIT => users::DEV_ID], $msg);
        $t->assert($test_name, $std_arr[url_var::ID], word_names::MATH_ID);
        $msg->reset();

        $t->subheader($ts . 'signup mail');

        // the signup and the reset mail are sent via the smtp account of the .env (see mail_sender)
        $smtp_host = 'smtp.' . POD_NAME;
        $test_name = 'the smtp account on the starttls port is called via smtp';
        $t->assert($test_name, mail_sender::smtp_url($smtp_host, SIGNUP_MAIL_PORT_FALLBACK), 'smtp://' . $smtp_host . ':' . SIGNUP_MAIL_PORT_FALLBACK);
        $test_name = '... and on the port with tls from the start via smtps';
        $tls_port = mail_sender::SMTP_PORT_IMPLICIT_TLS;
        $t->assert($test_name, mail_sender::smtp_url($smtp_host, $tls_port), 'smtps://' . $smtp_host . ':' . $tls_port);
        $test_name = 'the mail is sent from the signup address of the .env';
        $t->assert($test_name, mail_sender::sender(users::SYSTEM_SIGNUP_EMAIL), users::SYSTEM_SIGNUP_EMAIL);
        $test_name = '... or from the admin address if the .env has none';
        $t->assert($test_name, mail_sender::sender(''), users::SYSTEM_ADMIN_EMAIL);
        $test_name = 'an address without a line break can be used in the mail header';
        $t->assert_true($test_name, mail_sender::header_safe(users::TEST_SIGNUP_EMAIL));
        $test_name = '... but a line break that would add a header is refused';
        $t->assert_false($test_name, mail_sender::header_safe(users::TEST_SIGNUP_EMAIL . "\r\nBcc: " . users::SYSTEM_ADMIN_EMAIL));
        $mail_txt = mail_sender::smtp_message(users::SYSTEM_SIGNUP_EMAIL, users::TEST_SIGNUP_EMAIL,
            $mtr->txt(msg_id::SIGNUP_MAIL_SUBJECT), $mtr->txt(msg_id::RESET_MAIL_HELLO) . "\n\n" . $mtr->txt(msg_id::SIGNUP_MAIL_KEY_INTRO));
        $test_name = 'the mail names the sender';
        $t->assert_text_contains($test_name, $mail_txt, 'From: ' . users::SYSTEM_SIGNUP_EMAIL . mail_sender::CRLF);
        $test_name = '... and the receiver';
        $t->assert_text_contains($test_name, $mail_txt, 'To: ' . users::TEST_SIGNUP_EMAIL . mail_sender::CRLF);
        $test_name = '... and says that the text is utf-8, e.g. for the german umlauts';
        $t->assert_text_contains($test_name, $mail_txt, 'charset=UTF-8');
        $test_name = '... and separates the header from the text by an empty line';
        $t->assert_text_contains($test_name, $mail_txt, mail_sender::CRLF . mail_sender::CRLF . $mtr->txt(msg_id::RESET_MAIL_HELLO));
        $test_name = '... and ends every text line with crlf as the smtp server expects';
        $t->assert_text_contains($test_name, $mail_txt,
            $mtr->txt(msg_id::RESET_MAIL_HELLO) . mail_sender::CRLF . mail_sender::CRLF . $mtr->txt(msg_id::SIGNUP_MAIL_KEY_INTRO));
        $test_name = '... without a single line feed';
        $t->assert_false($test_name, preg_match('/[^\r]\n/', $mail_txt) === 1);

        $t->subheader($ts . 'popup form');

        // the popup form class must also accept a user (e.g. of the user settings form),
        // which is a db object but not a sandbox object
        $preview = new ui_preview();
        $test_name = 'the popup form class of a user form is the user class name';
        $t->assert_true($test_name, $preview->popup_class($usr_ui) != '');
        $test_name = 'without an object the popup form class is empty';
        $t->assert($test_name, $preview->popup_class(), '');

        $t->html_page_test($test_page, 'user', 'user', $msg, $base_url, $lan);
    }

}