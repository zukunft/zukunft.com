<?php

/*

    test/unit/html.php - unit testing of the html code generating functions
    ------------------


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
use Zukunft\ZukunftCom\main\php\shared\api;
use Zukunft\ZukunftCom\main\php\shared\const\rest_ctrl;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once paths::SHARED_TYPES . 'component_types.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_CONST . 'rest_ctrl.php';
include_once html_paths::COMPONENT . 'component_exe.php';
include_once html_paths::HTML . 'html_selector.php';
include_once html_paths::HTML . 'button.php';
include_once html_paths::RESULT . 'result_list.php';
include_once html_paths::VERB . 'verb_list.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_TYPES . 'verbs.php';

use Zukunft\ZukunftCom\main\php\cfg\component\component;
use Zukunft\ZukunftCom\main\php\cfg\group\group;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\cfg\result\result;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\value\value;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb_list;
use Zukunft\ZukunftCom\main\php\web\formula\formula;
use Zukunft\ZukunftCom\main\php\web\frontend;
use Zukunft\ZukunftCom\main\php\web\helper\url_mapper;
use Zukunft\ZukunftCom\main\php\web\html\button;
use Zukunft\ZukunftCom\main\php\web\ref\source;
use Zukunft\ZukunftCom\main\php\web\user\user_message as user_message_ui;
use Zukunft\ZukunftCom\main\php\web\verb\verb_list as verb_list_ui;
use Zukunft\ZukunftCom\main\php\web\component\component_exe as component_ui;
use Zukunft\ZukunftCom\main\php\web\component\execute\system_form;
use Zukunft\ZukunftCom\main\php\web\component\execute\ui_base;
use Zukunft\ZukunftCom\main\php\web\const\icons;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\html\styles;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase_list as phrase_list_ui;
use Zukunft\ZukunftCom\main\php\web\result\result as result_ui;
use Zukunft\ZukunftCom\main\php\web\result\result_list as result_list_ui;
use Zukunft\ZukunftCom\main\php\web\value\value as value_ui;
use Zukunft\ZukunftCom\main\php\web\verb\verb as verb_ui;
use Zukunft\ZukunftCom\main\php\web\word\word;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\const\components;
use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\main\php\shared\const\values;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\shared\types\component_types as comp_type_shared;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_formulas;
use Zukunft\ZukunftCom\test\php\create\test_mappers;
use Zukunft\ZukunftCom\test\php\create\test_phrases;
use Zukunft\ZukunftCom\test\php\create\test_sources;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

class base_ui_tests
{
    function run(test_cleanup $t): void
    {

        $lib = new library();
        $html = new html_base();
        $t_wrd = new test_words($t);
        $t_phr = new test_phrases($t);
        $t_src = new test_sources($t);
        $t_frm = new test_formulas($t);
        $msg = new user_message();
        $msg_ui = new user_message_ui();

        // start the test section (ts)
        $ts = 'unit ui html base ';
        $t->header($ts);

        $t->subheader($ts . 'cached page routing');

        // a view-only request is cached by the canonical view and object key
        $ui = new frontend();
        $test_name = 'a view-only request is cached by the view and object key';
        $url_array = [url_var::MASK => views::WORD_ID, url_var::ID => 2];
        $t->assert($test_name, $ui->url_cache_key($url_array), 'm=' . views::WORD_ID . '&id=2');

        // the language is part of the cache key if set
        $test_name = 'the language is part of the cache key';
        $url_array = [url_var::MASK => views::WORD_ID, url_var::ID => 2, url_var::LANGUAGE => 'de'];
        $t->assert($test_name, $ui->url_cache_key($url_array), 'm=' . views::WORD_ID . '&id=2&' . url_var::LANGUAGE . '=de');

        // a request of a view that changes data is never cached
        $test_name = 'a change mask request is not cached';
        $url_array = [url_var::MASK => views::WORD_ADD_ID, url_var::ID => 2];
        $t->assert($test_name, $ui->url_cache_key($url_array), '');

        // a request with a form submission is never cached
        $test_name = 'a post request is not cached';
        $url_array = [url_var::MASK => views::WORD_ID, url_var::ID => 2, url_var::POST_SUBMIT => 'Save'];
        $t->assert($test_name, $ui->url_cache_key($url_array), '');

        // a process step of 0 (no action started) does not change a view-only page, so it is ignored
        // and the request still hits the same cache key as the bare view (e.g. view.php?m=1&z=0)
        $test_name = 'a show step (z=0) is ignored for the cache key';
        $url_array = [url_var::MASK => views::WORD_ID, url_var::ID => 2, url_var::STEP => url_var::STEP_BASE];
        $t->assert($test_name, $ui->url_cache_key($url_array), 'm=' . views::WORD_ID . '&id=2');

        // the anti-csrf token is per session and does not change a view-only page, so it too is
        // ignored and the request still hits the same cache key (e.g. view.php?m=1&z=0&token=...)
        $test_name = 'the anti-csrf token is ignored for the cache key';
        $url_array = [
            url_var::MASK => views::WORD_ID, url_var::ID => 2,
            url_var::STEP => url_var::STEP_BASE, url_var::SESSION_TOKEN => 'abc'];
        $t->assert($test_name, $ui->url_cache_key($url_array), 'm=' . views::WORD_ID . '&id=2');

        // a non-zero process step is an action step, so the request is rendered live and not cached
        $test_name = 'a non-zero step request is not cached';
        $url_array = [url_var::MASK => views::WORD_ID, url_var::ID => 2, url_var::STEP => url_var::STEP_CONFIRM];
        $t->assert($test_name, $ui->url_cache_key($url_array), '');

        // a logged in (non-ip) user gets a personalised page (e.g. the dark blue person icon,
        // the logout link and the my tab), so it is never served from the shared page cache;
        // the login state comes from the session, because the cache fast path runs before
        // the type cache needed for a profile based check is loaded
        $test_name = 'a logged in user never gets the shared cached page';
        $_SESSION[url_var::SESSION_LOGGED] = true;
        $url_array = [url_var::MASK => views::WORD_ID, url_var::ID => 2];
        $t->assert_true($test_name, $ui->cached_page_or_null($url_array, new user_message_ui()) === null);
        unset($_SESSION[url_var::SESSION_LOGGED]);

        // the debug level only controls out-of-band debug output, not the cached html, so it is
        // ignored and ?m=2&debug=6 takes the same cached path as ?m=2 (same cache key)
        $test_name = 'the debug level is ignored for the cache key';
        $url_array = [url_var::MASK => views::WORD_ID, url_var::ID => 2,
            url_var::DEBUG => url_var::DEBUG_LEVEL_DB_READ];
        $t->assert($test_name, $ui->url_cache_key($url_array), 'm=' . views::WORD_ID . '&id=2');

        // 'nc=1' switches the html page cache off, so the page is rendered live and the
        // result is not written to the cache (e.g. view.php?m=1&id=2&nc=1)
        $test_name = 'nc=1 bypasses the cache read and write';
        $url_array = [url_var::MASK => views::WORD_ID, url_var::ID => 2, url_var::NO_CACHE => url_var::NO_CACHE_ON];
        $t->assert($test_name, $ui->url_cache_key($url_array), '');

        // any other value keeps the cache on, so the request hits the same cache key as the bare url
        $test_name = 'nc=0 keeps the cache on';
        $url_array = [url_var::MASK => views::WORD_ID, url_var::ID => 2, url_var::NO_CACHE => '0'];
        $t->assert($test_name, $ui->url_cache_key($url_array), 'm=' . views::WORD_ID . '&id=2');

        // the human-readable 'nocache' is mapped to the short 'nc' before the cache key is created,
        // so view.php?mask_id=word&id=2&nocache=1 bypasses the cache just like the short url
        $test_name = 'nocache is mapped to the short nc';
        $url_map = new url_mapper();
        $url_msg = new user_message_ui();
        $url_array = [url_var::MASK_HUMAN => views::WORD, url_var::ID => 2,
            url_var::NO_CACHE_HUMAN => url_var::NO_CACHE_ON];
        $url_std = $url_map->url_to_standard($url_array, $url_msg);
        $t->assert($test_name, $url_std[url_var::NO_CACHE] ?? '', url_var::NO_CACHE_ON);
        $t->assert($test_name . ' and bypasses the cache', $ui->url_cache_key($url_std), '');

        // the login and signup form pages are served from the page cache even though they are process
        // masks, because the plain form is static (the per-session token is restored per request)
        $test_name = 'the login form is cached';
        $url_array = [url_var::MASK => views::LOGIN_ID];
        $t->assert($test_name, $ui->url_cache_key($url_array), 'm=' . views::LOGIN_ID . '&id=0');

        $test_name = 'the signup form is cached';
        $url_array = [url_var::MASK => views::SIGNUP_ID];
        $t->assert($test_name, $ui->url_cache_key($url_array), 'm=' . views::SIGNUP_ID . '&id=0');

        // the login submit is a post action, so only the form GET is cached, never the submission
        $test_name = 'the login submit is not cached';
        $url_array = [url_var::MASK => views::LOGIN_ID, url_var::POST_SUBMIT => 'Login'];
        $t->assert($test_name, $ui->url_cache_key($url_array), '');

        // another process step mask that is not a login or signup form stays excluded from the cache
        $test_name = 'a non-login process step view is still not cached';
        $url_array = [url_var::MASK => views::EXPORT_ID];
        $t->assert($test_name, $ui->url_cache_key($url_array), '');

        // the start page is cached; the bare landing page (no view) and an explicit start request
        // share the same start view cache key, because a request without a view shows the start view
        $test_name = 'the explicit start view is cached';
        $url_array = [url_var::MASK => views::START_ID];
        $t->assert($test_name, $ui->url_cache_key($url_array), 'm=' . views::START_ID . '&id=0');

        $test_name = 'the bare landing page is cached under the start view key';
        $url_array = [];
        $t->assert($test_name, $ui->url_cache_key($url_array), 'm=' . views::START_ID . '&id=0');

        $t->subheader($ts . 'tab box');

        // the tab box switches via the url fragment with pure css (:target) and no javascript: the
        // 'Changes' label gives the pane id 'changes' and the nav link href '#changes'
        $test_name = 'tab_box switches tabs via the url fragment';
        $two_tabs = $html->tab_box(['View' => 'view content', 'Changes' => 'changes content']);
        $t->assert_text_contains($test_name, $two_tabs, html_base::HREF . '="#changes"');
        $t->assert_text_contains($test_name, $two_tabs, html_base::ID . '="changes"');

        // the target is a pure html frontend, so the tab box must not contain javascript
        $test_name = 'tab_box contains no javascript';
        $t->assert_text_not_contains($test_name, $two_tabs, '<script');

        $t->subheader($ts . 'navbar');

        // the person icon in the top right corner is shown in dark blue (styles::USER_LOGGED)
        // if a non-ip user is logged in, i.e. if the navbar gets a user name
        $test_name = 'the person icon shows the logged in state in dark blue';
        $navbar_logged = $html->navbar(0, [], 'test user');
        $t->assert_text_contains($test_name, $navbar_logged, icons::USER_CIRCLE . ' ' . styles::USER_LOGGED);

        $test_name = 'without a logged in user the person icon keeps the default color';
        $navbar_anon = $html->navbar(0, []);
        $t->assert_text_not_contains($test_name, $navbar_anon, styles::USER_LOGGED);

        // the login link forwards a '9'-prefixed back target of the current page (the logout
        // page carries the original page as back target, see frontend::action_logout), so after
        // the login the original page is shown again and not the logout page
        $test_name = 'the login link forwards the back target of the logout page';
        $logout_page_url = [
            url_var::MASK => (string)views::LOGOUT_ID,
            url_var::BACK . url_var::MASK => (string)views::WORD_ID,
            url_var::BACK . url_var::ID => '347',
        ];
        $navbar_logout_page = $html->navbar(views::LOGOUT_ID, $logout_page_url);
        $t->assert_text_contains($test_name, $navbar_logout_page,
            api::LOGIN_SCRIPT . '&amp;' . url_var::BACK . url_var::MASK . '=' . views::WORD_ID);
        $t->assert_text_contains($test_name, $navbar_logout_page, url_var::BACK . url_var::ID . '=347');

        $test_name = '... and never the logout page as its own back target';
        $t->assert_text_not_contains($test_name, $navbar_logout_page,
            api::LOGIN_SCRIPT . '&amp;' . url_var::BACK . url_var::MASK . '=' . views::LOGOUT_ID);

        $test_name = 'on a normal page the login link uses the page as the back target';
        $navbar_normal_page = $html->navbar(views::WORD_ID, [
            url_var::MASK => (string)views::WORD_ID,
            url_var::ID => '347',
        ]);
        $t->assert_text_contains($test_name, $navbar_normal_page,
            api::LOGIN_SCRIPT . '&amp;' . url_var::BACK . url_var::MASK . '=' . views::WORD_ID);

        $t->subheader($ts . 'login');

        $created_html = $html->about_page($msg_ui);
        $expected_html = $t->file(test_paths::HTML . test_paths::VIEW_FUNCTIONS . 'about.html');
        $t->assert('about', $lib->trim_html($created_html), $lib->trim_html($expected_html));


        $t->subheader($ts . 'selector');

        // TODO test the creation of a phrase list API JSON
        // TODO create a selector using a list an with a simple test page header an footer
        /*
        $sel = new html_selector();
        $sel->label = 'Test:';
        $sel->name = 'test_selector';
        $sel->form = 'test_form';
        $sel_lst = array();
        $sel_lst[1] = 'First';
        $sel_lst[2] = 'Second';
        $sel_lst[3] = 'Third (selected)';
        $sel_lst[4] = 'Fourth';
        $sel->lst = $sel_lst;
        $sel->selected = 3;
        $body = $html->form_start($sel->form);
        $body .= $sel->display_old();
        $body .= $html->form_end_with_submit($sel->name, '');
        $t->html_test($body, '', 'selector', $t);
        */

        // ... and check if the prepared sql name is unique
        //$t->assert_sql_name_unique($log_ui->dsp_hist_links_sql($db_con, true));

        // button add
        $url = $html->url_new(views::WORD_ADD_ID);
        $t->html_page_test(new button($url)->add(msg_id::WORD_ADD), '', 'button_add', $msg_ui);

        $t->subheader($ts . 'form field name and id');

        // the field name is the url var (the submitted key), the id is the user-readable
        // label; the label text must never become the submitted name (see field_id / url_mapper)
        global $mtr;
        $name_label = $mtr->txt(msg_id::FORM_FIELD_NAME);
        $name_id = strtolower($name_label);

        $test_name = 'text field submits the url var as name';
        $field = $html->input(url_var::NAME, msg_id::FORM_FIELD_NAME, 'math', html_base::INPUT_TEXT);
        $t->assert_text_contains($test_name, $field, 'name="' . url_var::NAME . '"');

        $test_name = '... and uses the readable label as the id';
        $t->assert_text_contains($test_name, $field, 'id="' . $name_id . '"');

        $test_name = '... never the label as the name nor the url var as the id';
        $t->assert_text_not_contains($test_name, $field, 'name="' . $name_label . '"');
        $t->assert_text_not_contains($test_name, $field, 'id="' . url_var::NAME . '"');

        // a second form on the same page suffixes the url var (e.g. '_add'); the id must
        // carry the same suffix so it stays unique next to the un-suffixed edit form field
        $test_name = 'suffixed url var keeps the id unique';
        $field_add = $html->input(url_var::NAME . '_add', msg_id::FORM_FIELD_NAME, '', html_base::INPUT_TEXT);
        $t->assert_text_contains($test_name, $field_add, 'name="' . url_var::NAME . '_add"');
        $t->assert_text_contains($test_name, $field_add, 'id="' . $name_id . '_add"');

        $test_name = 'add and edit field ids differ on the same page';
        $t->assert_text_not_contains($test_name, $field_add, 'id="' . $name_id . '"');

        // form_field pairs a <label for> with the input id; both must use the same field_id
        $test_name = 'form_field links label for to the input id';
        $labelled = $html->form_field(url_var::NAME, msg_id::FORM_FIELD_NAME, 'math');
        $t->assert_text_contains($test_name, $labelled, 'for="' . $name_id . '"');
        $t->assert_text_contains($test_name, $labelled, 'id="' . $name_id . '"');

        $test_name = '... and keeps the pair unique for a suffixed field';
        $labelled_add = $html->form_field(url_var::NAME . '_add', msg_id::FORM_FIELD_NAME, '');
        $t->assert_text_contains($test_name, $labelled_add, 'for="' . $name_id . '_add"');
        $t->assert_text_contains($test_name, $labelled_add, 'id="' . $name_id . '_add"');


        $t->subheader($ts . 'unit html table tests');

        // create a test set of phrase groups
        $t_phr->phrase_list_zh_mio();
        $grp_city = new group($t->usr1);
        $grp_city->set_phrase_list($t_phr->phrase_list_zh_city_2019());
        $grp_canton = new group($t->usr1);
        $grp_canton->set_phrase_list($t_phr->phrase_list_canton_mio());
        $grp_ch = new group($t->usr1);
        $grp_ch->set_phrase_list($t_phr->ch_inhabitants_in_mio_2019());
        $grp_city_pct = new group($t->usr1);
        $grp_city_pct->set_phrase_list($t_phr->phrase_list_zh_city_pct());
        $grp_canton_pct = new group($t->usr1);
        $grp_canton_pct->set_phrase_list($t_phr->phrase_list_canton_pct());
        $phr_lst_context = new phrase_list($t->usr1);
        $phr_lst_context->add($t_wrd->word_inhabitant()->phrase());

        // create the value for the inhabitants of the city of zurich
        $val_city = new value($t->usr1);
        $val_city->set_grp($grp_city);
        $val_city->set_number(values::CITY_ZH_INHABITANTS_2019);
        $val_city_ui = new value_ui($val_city->api_json([api_types::INCL_PHRASES]));
        $val_city_html = $val_city_ui->name_link($msg_ui);
        $t->assert_text_contains('', $val_city_html, word_names::CITY);

        // create the value for the inhabitants of the city of zurich
        $val_canton = new value($t->usr1);
        $val_canton->set_grp($grp_canton);
        $val_canton->set_number(values::CANTON_ZH_INHABITANTS_2020_IN_MIO);
        $val_canton_ui = new value_ui($val_canton->api_json([api_types::INCL_PHRASES]));
        $val_canton_html = $val_canton_ui->name_link($msg_ui);
        $t->assert_text_contains('', $val_canton_html, word_names::CANTON);

        // create the value for the inhabitants of Switzerland
        $val_ch = new value($t->usr1);
        $val_ch->set_grp($grp_ch);
        $val_ch->set_number(values::CH_INHABITANTS_2019_IN_MIO);
        $val_ch_ui = new value_ui($val_ch->api_json([api_types::INCL_PHRASES]));
        $val_ch_html = $val_ch_ui->name_link($msg_ui);
        $t->assert_text_contains('', $val_ch_html, round(values::CH_INHABITANTS_2019_IN_MIO, 2));

        // create the formula result for the inhabitants of the city of zurich
        $res_city = new result($t->usr1);
        $res_city->set_grp($grp_city_pct);
        $ch_val_scaled = values::CH_INHABITANTS_2019_IN_MIO * 1000000;
        $res_city->set_number(values::CITY_ZH_INHABITANTS_2019 / $ch_val_scaled);
        $res_city_ui = new value_ui($res_city->api_json([api_types::INCL_PHRASES]));
        $res_city_html = $res_city_ui->name_link($msg_ui);
        $t->assert_text_contains('', $res_city_html, word_names::CITY);

        // create the formula result for the inhabitants of the canton of zurich
        $res_canton = new result($t->usr1);
        $res_canton->set_grp($grp_canton_pct);
        $res_canton->set_number(values::CANTON_ZH_INHABITANTS_2020_IN_MIO / values::CH_INHABITANTS_2019_IN_MIO);
        $res_canton_ui = new value_ui($res_canton->api_json([api_types::INCL_PHRASES]));
        $res_canton_html = $res_canton_ui->value_edit($msg_ui, '');
        $res_canton_number = round((values::CANTON_ZH_INHABITANTS_2020_IN_MIO / values::CH_INHABITANTS_2019_IN_MIO) * 100, 2) . '%';
        $t->assert_text_contains('', $res_canton_html, $res_canton_number);

        // create the formula result list and the table to display the results
        $res_lst = new result_list_ui();
        $res_lst->add_result(new result_ui($res_city->api_json([api_types::INCL_PHRASES])));
        $res_lst->add_result(new result_ui($res_canton->api_json([api_types::INCL_PHRASES])));
        $t->html_page_test($res_lst->table(), '', 'table_result', $msg_ui);

        // create the same table as above, but within a context
        $phr_lst_context_ui = new phrase_list_ui($phr_lst_context->api_json([api_types::INCL_PHRASES]));
        $t->html_page_test($res_lst->table($phr_lst_context_ui), '', 'table_result_context', $msg_ui);


        $t->subheader($ts . 'unit html view component tests');

        $cmp = new component($t->usr1);
        $cmp->set(components::WORD_ID, components::TEST_ADD_NAME);
        $cmp->set_type(comp_type_shared::TEXT, new user_message($t->usr1));
        $cmp_ui = new component_ui($cmp->api_json());
        $t->html_page_test($cmp_ui->html($msg_ui), '', 'component_text', $msg_ui);


        $t->subheader($ts . 'list');

        // TODO create and set the model objects and
        //      create the api object using the api_obj() function
        //      create and set the dsp object based on the api json

        $lst = new verb_list($t->usr1);
        $lst->add_verb(new verb(1, verbs::IS));
        $lst->add_verb(new verb(2, verbs::PART_NAME));
        // TODO use set_from_json to set the display object
        $vrb_lst_ui = new verb_list_ui();
        $vrb_lst_ui->set_from_json_array($lst->api_json_array([], $msg), $msg_ui);
        $t->html_page_test($vrb_lst_ui->list(verb_ui::class, 'Verbs'), '', 'list_verbs', $msg_ui);

        $test_name = 'sort a named list by the name';
        $lst = $t_phr->phrase_list_zh_mio();
        $names_unsorted = $lst->names();
        $lst->sort_by_name();
        $names = $lst->names();
        $names_sorted = $names;
        natcasesort($names_sorted);
        $t->assert($test_name, implode(',', $names), implode(',', $names_sorted));
        $test_name = 'unsorted named list';
        $t->assert_not($test_name, implode(',', $names), implode(',', $names_unsorted));




        // TODO review

        global $sys;
        $html = new html_base();

        $is_connected = true; // assumes that the test is done with an internet connection, but if not connected, just show the warning once

        $t->subheader($ts . 'unit html view tests');

        // test the usage of a view to create the HTML code
        /*
        $wrd = $t->load_word(words::TN_READ);
        $msk = new view($t->usr1);
        $msk->load_by_name(views::TN_READ_RATIO);
        //$result = $msk->display($wrd, $back);
        $target = true;
        //$t->dsp_contains(', view_dsp->display is "'.$result.'" which should contain '.$wrd_abb->name.'', $target, $result);
        */


        $t->subheader($ts . 'component');

        // test if a simple text component can be created
        $cmp = new component($t->usr1);
        $msg = new user_message_ui();
        $cmp->type_id = $sys->typ_lst->cmp_typ->id(comp_type_shared::TEXT);
        $cmp->id = 1;
        $cmp->set_name(views::NESN_2016_FS_NAME);
        $cmp_ui = new component_ui($cmp->api_json());
        $result = $cmp_ui->html($msg);
        $target = views::NESN_2016_FS_NAME;
        $t->assert('component_dsp->text', $result, $target);


        $t->subheader($ts . 'button tests');
        $test_name = 'a sandbox object e.g. word add button html code';
        $target = '<a href="' . api::MAIN_SCRIPT . '?' . url_var::MASK . '=word_add&amp;back=1" title="add new word"><i class="far fa-plus-square"></i></a>';
        $wrd = new word();
        $t->assert($test_name, $wrd->btn_add('1'), $target);

        $test_name = 'a sandbox object e.g. source change button html code';
        $target = '<a href="' . api::MAIN_SCRIPT . '?' . url_var::MASK . '=source_edit&amp;id=1&amp;back=1" title="source_edit"><i class="far fa-edit"></i></a>';
        $src = new source();
        $src->set_from_json($t_src->source_reserved()->api_json(), $msg);
        $t->assert($test_name, $src->btn_edit('1'), $target);

        $test_name = 'a sandbox object e.g. formula delete button html code';
        $target = '<a href="' . api::MAIN_SCRIPT . '?' . url_var::MASK . '=formula_del&amp;id=1&amp;back=1" title="delete this formula of scale minute to sec"><i class="far fa-times-circle"></i></a>';
        $frm = new formula();
        $frm->set_from_json($t_frm->formula()->api_json(), $msg);
        $t->assert($test_name, $frm->btn_del('1'), $target);


        $url = $html->url_new(views::WORD_ADD_ID);
        $back = '1';
        $target = '<a href="' . api::MAIN_SCRIPT . '?' . url_var::MASK . '=' . views::WORD_ADD_ID . '" title="add new word">';
        $result = (new button($url, $back))->add(msg_id::WORD_ADD);
        $t->dsp_contains(", btn_add", $target, $result);

        // TODO move e.g. because the edit word button is tested already in the unit tests of the object

        $url = $html->url_new(views::WORD_DEL_ID);
        $target = '<a href="/http/view.php" title="Del test"><img src="/images/button_del.svg" alt="Del test"></a>';
        $target = '<a href="' . api::MAIN_SCRIPT . '?' . url_var::MASK . '=' . views::WORD_DEL_ID . '" title="delete word"><i class="far fa-times-circle"></i></a>';
        $result = (new button($url, $back))->del(msg_id::WORD_DEL);
        $t->dsp_contains(", btn_del", $target, $result);

        $url = $html->url_new(views::WORD_NAME);
        $target = '<a href="/http/view.php" title="Undo test"><img src="/images/button_undo.svg" alt="Undo test"></a>';
        $target = '<a href="/http/word.php" title="undo"><img src="/images/button_undo.svg" alt="undo"></a>';
        $result = (new button($url, $back))->undo(msg_id::UNDO);
        //$t->assert(", btn_undo", $result, $target);

        $url = $html->url_new(views::WORD_ADD_ID);
        $target = '<a href="/http/view.php" title="Find test"><img src="/images/button_find.svg" alt="Find test"></a>';
        $target = '<a href="' . api::MAIN_SCRIPT . '?' . url_var::MASK . '=' . views::WORD_ADD_ID . '" title=""><img src="/images/button_find.svg" alt=""></a>';
        $result = (new button($url, $back))->find(msg_id::FIND);
        //$t->assert(", btn_find", $result, $target);

        $url = $html->url_new(views::WORD_ADD_ID);
        $target = '<a href="/http/view.php" title="Show all test"><img src="/images/button_filter_off.svg" alt="Show all test"></a>';
        $target = '<a href="' . api::MAIN_SCRIPT . '?' . url_var::MASK . '=' . views::WORD_ADD_ID . '" title=""><img src="/images/button_filter_off.svg" alt=""></a>';
        $result = (new button($url, $back))->un_filter(msg_id::REMOVE_FILTER);
        //$t->assert(", btn_unfilter", $result, $target);

        $url = $html->url_new(views::WORD_ADD_ID);
        $target = '<h6>YesNo test</h6><a href="/http/view.php&confirm=1" title="Yes">Yes</a>/<a href="/http/view.php&confirm=-1" title="No">No</a>';
        $target = '<h6></h6><a href="' . api::MAIN_SCRIPT . '?' . url_var::MASK . '=' . views::WORD_ADD_ID . '&amp;confirm=1">yes</a>/<a href="' . api::MAIN_SCRIPT . '?' . url_var::MASK . '=' . views::WORD_ADD_ID . '&amp;confirm=-1">no</a>';
        $result = (new button($url, $back))->yes_no();
        $t->assert(", btn_yesno", $result, $target);

        $url = $html->url_new(views::WORD_ADD_ID);
        $target = '<a href="' . api::MAIN_SCRIPT . '?words=1" title="back"><img src="/images/button_back.svg" alt="back"></a>';
        $result = (new button($url, $back))->back();
        //$t->assert(", btn_back", $result, $target);

        $t->subheader($ts . 'xss escaping');
        // a user-settable name is escaped at the display sink so a crafted name cannot inject script
        // into the page shown to other users incl. an admin (stored xss); ui_base and system_form
        // route the display names / descriptions through html_base::esc (element-text context)
        $xss = '<script>alert(1)</script>';
        $xss_esc = htmlspecialchars($xss, ENT_NOQUOTES);
        $wrd_xss = new word();
        $wrd_xss->name = $xss;
        $base = new ui_base();
        $form = new system_form();
        $test_name = 'ui_base->phrase_name escapes an injected script tag';
        $t->assert_text_contains($test_name, $base->phrase_name($wrd_xss), $xss_esc);
        $test_name = 'ui_base->phrase_name does not echo the raw script tag';
        $t->assert_text_not_contains($test_name, $base->phrase_name($wrd_xss), $xss);
        $test_name = 'system_form->show_name escapes an injected script tag';
        $t->assert_text_contains($test_name, $form->show_name($wrd_xss), $xss_esc);
        $test_name = 'system_form->show_name does not echo the raw script tag';
        $t->assert_text_not_contains($test_name, $form->show_name($wrd_xss), $xss);

        $t->subheader($ts . 'back url');

        $test_name = 'back url part while editing word 123';
        $url_part = parse_url('?m=3&id=123');
        parse_str($url_part["query"], $url_array);
        $result = $html->back_url_part($url_array);
        $t->assert($test_name, $result, '9m=3&9id=123');

        $test_name = 'back url part is empty if there is no query string';
        $url_array = [];
        $result = $html->back_url_part($url_array);
        $t->assert($test_name, $result, '');

        $test_name = 'login url with back part while editing word 123';
        $url_part = parse_url('?m=3&id=123');
        parse_str($url_part["query"], $url_array);
        $result = $html->url_with_back(api::LOGIN_SCRIPT, $url_array);
        $t->assert($test_name, $result, rest_ctrl::PATH_FIXED .'view.php?m=61&9m=3&9id=123');

        $test_name = 'url from back part while editing word 123';
        $url_part = parse_url('?m=2&9m=3&9id=123');
        parse_str($url_part["query"], $url_array);
        $result = $html->url_par_from_back_part($url_array);
        $t->assert($test_name, $result, ['m' => '3', 'id' => '123']);

        $test_name = 'url from back part if array is empty';
        $url_array = [];
        $result = $html->url_par_from_back_part($url_array);
        $t->assert($test_name, $result, []);

        $test_name = 'add word url with back part from main page';
        $url_part = parse_url('?m=1');
        parse_str($url_part["query"], $url_array);
        $result = $html->url_with_back(api::MAIN_SCRIPT . '?m=3&id=123', $url_array);
        $t->assert($test_name, $result, rest_ctrl::PATH_FIXED .'view.php?m=3&id=123&9m=1');

        $lib = new library();
        $msg = new user_message_ui();
        $url_test = new test_mappers($t);

        $t->subheader($ts . 'url mapper');
        $url_map = new url_mapper();
        $test_name = 'add default value of view';
        $url = 'http://localhost' . api::MAIN_SCRIPT . '?id=1';
        $url_array = $url_map->url_to_standard($lib->url_array($url), $msg);
        $view = $url_array[url_var::MASK];
        $t->assert($test_name, $view, views::START_ID);
        $test_name = 'add default value of step';
        $url = 'http://localhost' . api::MAIN_SCRIPT . '?' . url_var::MASK . '=3&id=1&debug=-1';
        $url_array = $url_map->url_to_standard($lib->url_array($url), $msg);
        $step = $url_array[url_var::STEP];
        $t->assert($test_name, $step, 0);
        $test_name = 'add default value of view for human-readable url';
        $url = 'http://localhost' . api::MAIN_SCRIPT . '?mask_id=&verb_id=3';
        $url_array = $url_map->url_to_standard($lib->url_array($url), $msg);
        $view = $url_array[url_var::MASK];
        $t->assert($test_name, $view, views::START_ID);
        // the human url uses the view code id (the name) for the mask, not the numeric view id, for
        // every view that is in the loaded cache (url_mapper::map_std_mask_to)
        $test_name = 'convert the standard url to human-readable url';
        $url = 'http://localhost' . api::MAIN_SCRIPT . '?' . url_var::MASK . '=2&id=1&debug=-1';
        $url_human = $url_test->test_url($url_map->standard_url_to_human($lib->url_array_with($url), $msg));
        $url_array = $lib->url_array($url_human);
        $view = $url_array[url_var::MASK_HUMAN];
        $t->assert($test_name, $view, views::WORD_ADD);

        // TODO Prio 1 review
        // url_mapper::to_row_format: the flat standard url array (as produced by url_to_standard) is
        // accepted directly now, not only the [key, value] row format produced by url_array_with
        $test_name = 'convert a flat standard url to human-readable url';
        $url = 'http://localhost' . api::MAIN_SCRIPT . '?' . url_var::MASK . '=2&id=1&debug=-1';
        $url_human = $url_test->test_url($url_map->standard_url_to_human($lib->url_array($url), $msg));
        $url_array = $lib->url_array($url_human);
        $view = $url_array[url_var::MASK_HUMAN];
        $t->assert($test_name, $view, views::WORD_ADD);
        // an '8'-prefixed pre value (and '9'-prefixed back target) is mapped to its human key with the
        // prefix kept (e.g. 8name), so it is not reported as missing
        $test_name = 'human url conversion maps an 8-prefixed pre value';
        $ok_msg = new user_message_ui();
        $url = 'http://localhost' . api::MAIN_SCRIPT . '?' . url_var::MASK . '=2&' . url_var::PRE . url_var::NAME . '=x';
        $url_human = $url_test->test_url($url_map->standard_url_to_human($lib->url_array($url), $ok_msg));
        $t->assert_false($test_name, $ok_msg->has_msg_id(msg_id::URL_MAP_MISSING));
        $t->assert_text_contains($test_name, $url_human, url_var::PRE . url_var::NAME_HUMAN);
        // negative: a url key without any human mapping is still reported as missing
        $test_name = 'human url conversion reports a url key without a human mapping';
        $err_msg = new user_message_ui();
        $url = 'http://localhost' . api::MAIN_SCRIPT . '?' . url_var::MASK . '=2&zzz=x';
        $url_map->standard_url_to_human($lib->url_array($url), $err_msg);
        $t->assert_true($test_name, $err_msg->has_msg_id(msg_id::URL_MAP_MISSING));

        // frontend::url_to_back_url returns the previous page from the '9'-prefixed back targets
        $test_name = 'url_to_back_url returns the back target view and id';
        $ui = new frontend();
        $back_url = $ui->url_to_back_url([
            url_var::BACK . url_var::MASK => views::WORD_ID,
            url_var::BACK . url_var::ID => 1
        ]);
        $t->assert($test_name, $back_url[url_var::MASK], views::WORD_ID);
        // negative: a url without a back target falls back to the start view
        $test_name = 'url_to_back_url without a back target returns the start view';
        $back_url = $ui->url_to_back_url([url_var::MASK => views::WORD_ID]);
        $t->assert($test_name, $back_url[url_var::MASK], views::START_ID);

        // url_mapper::human_url_to_json groups the 8-prefixed vars into 'original_data' and the
        // 9-prefixed vars into 'back', and converts the view id to the code id
        $test_name = 'human_url_to_json groups the pre values and back targets into subarrays';
        $json = $url_map->human_url_to_json([
            url_var::MASK => views::WORD_EDIT_ID,
            url_var::NAME => 'x',
            url_var::PRE . url_var::NAME => 'old',
            url_var::BACK . url_var::MASK => views::WORD_ID
        ], $msg);
        $t->assert_text_contains($test_name, $json, json_fields::URL_ORIGINAL_DATA);
        $t->assert_text_contains($test_name, $json, json_fields::URL_PART_BACK);
        $t->assert_text_contains($test_name, $json, views::WORD_EDIT);
        // negative: a top-level url key without a human mapping is reported as missing
        $test_name = 'human_url_to_json reports a url key without a human mapping';
        $err_msg = new user_message_ui();
        $url_map->human_url_to_json([url_var::MASK => views::WORD_EDIT_ID, 'zzz' => '1'], $err_msg);
        $t->assert_true($test_name, $err_msg->has_msg_id(msg_id::URL_MAP_MISSING));

        // url_var::action_step maps a confirmed action to the confirmed process step (which triggers the
        // db write), and a plain navigation action to the base step
        $test_name = 'action_step maps update_confirmed to the confirmed step';
        $t->assert($test_name, url_var::action_step(url_var::ACTION_CONFIRMED), url_var::STEP_CONFIRMED);
        // negative: a navigation action does not advance the process step
        $test_name = 'action_step maps a navigation action to the base step';
        $t->assert($test_name, url_var::action_step(url_var::ACTION_SHOW), url_var::STEP_BASE);

        // without_secrets masks the unhashed password of a login post so it is never logged (http/view.php);
        // a non-secret field like the username is kept unchanged so the log stays useful
        $dummy_pw = 'dummy unhashed password for the redaction unit test';
        $post = [url_var::USERNAME_HUMAN => users::TEST_USER_NAME, url_var::USER_PASSWORD_HUMAN => $dummy_pw];
        $redacted = url_var::without_secrets($post);
        $test_name = 'without_secrets masks the unhashed password';
        $t->assert($test_name, $redacted[url_var::USER_PASSWORD_HUMAN], url_var::SECRET_MASK);
        $test_name = 'without_secrets keeps a non-secret field unchanged';
        $t->assert($test_name, $redacted[url_var::USERNAME_HUMAN], users::TEST_USER_NAME);

        // session_recovery_url: when the session token is not valid any more a logged-in (non-ip)
        // user is sent to the login page with the requested page kept as the '9'-prefixed back
        // target; a valid token or an anonymous / ip user needs no recovery (null)
        $req_url = [url_var::MASK => views::WORD_ID, url_var::ID => 2];
        $recovery = frontend::session_recovery_url(false, true, $req_url);
        $test_name = 'an expired token of a logged-in user shows the login page';
        $t->assert($test_name, $recovery[url_var::MASK], views::LOGIN_ID);
        $test_name = 'the login page keeps the requested page as the back target';
        $t->assert($test_name, $recovery[url_var::BACK . url_var::MASK], views::WORD_ID);
        $test_name = 'a valid token needs no session recovery';
        $t->assert_true($test_name, frontend::session_recovery_url(true, true, $req_url) === null);
        $test_name = 'an anonymous or ip user with an expired token just gets the page again';
        $t->assert_true($test_name, frontend::session_recovery_url(false, false, $req_url) === null);

        $test_name = 'convert the standard url to pod interchangeable url';
        $url = 'http://localhost' . api::MAIN_SCRIPT . '?' . url_var::MASK . '=2&id=1&debug=-1';
        $url_pod = $url_test->test_url($url_map->standard_url_to_pod($lib->url_array_with($url), $msg));
        $url_array = $lib->url_array($url_pod);
        // TODO Prio 2 activate
        //$view = $url_array[url_var::MASK_POD];
        //$t->assert($test_name, $view, views::WORD_ADD_ID);
        //$test_name = 'convert human-readable url keys to standard url keys';
        //$verb = $url_array[url_var::VERB];
        //$t->assert($test_name, $verb, 3);
        //$test_name = 'add default value of view for pod independent url';
        //$url = 'http://localhost' . api::MAIN_SCRIPT_REL . '?mask=';
        //$url_array = $url_map->url_to_standard($lib->url_array($url), $usr_msg);
        //$view = $url_array[url_var::MASK];
        //$t->assert($test_name, $view, views::START_CODE);
        $test_name = 'error message if mapping is missing';
        $url = 'http://localhost' . api::MAIN_SCRIPT . '?mask_id=&mapping_missing=3';
        $url_map->url_to_standard($lib->url_array($url), $msg);
        $err_msg = $msg->var_message_text();
        $t->assert($test_name, $err_msg, 'url mapper for "debug" is missing, url mapper for "id" is missing, url mapper for "mapping_missing" is missing');

    }

}
