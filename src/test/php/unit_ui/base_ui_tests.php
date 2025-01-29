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

namespace unit_ui;

include_once SHARED_TYPES_PATH . 'component_type.php';
include_once SHARED_CONST_PATH . 'views.php';
include_once WEB_HTML_PATH . 'html_selector.php';
include_once WEB_HTML_PATH . 'button.php';
include_once WEB_RESULT_PATH . 'result_list.php';
include_once SHARED_TYPES_PATH . 'verbs.php';

use cfg\component\component;
use cfg\group\group;
use cfg\phrase\phrase_list;
use cfg\result\result;
use cfg\value\value;
use cfg\verb\verb;
use cfg\verb\verb_list;
use html\button;
use html\component\component as component_dsp;
use html\html_base;
use html\phrase\phrase_list as phrase_list_dsp;
use html\result\result as result_dsp;
use html\result\result_list as result_list_dsp;
use html\system\messages;
use html\value\value as value_dsp;
use shared\library;
use shared\const\components;
use shared\const\values;
use shared\const\views;
use shared\const\views as view_shared;
use shared\const\words;
use shared\types\api_type;
use shared\types\component_type as comp_type_shared;
use shared\types\verbs;
use test\test_cleanup;

class base_ui_tests
{
    function run(test_cleanup $t): void
    {

        global $usr;
        $lib = new library();
        $html = new html_base();

        $t->header('Unit tests of the html classes (src/main/php/web/html/*)');


        $t->subheader('Login pages');

        $created_html = $html->about();
        $expected_html = $t->file('web/html/about.html');
        $t->display('about', $lib->trim_html($expected_html), $lib->trim_html($created_html));


        $t->subheader('Selector tests');

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
        //$t->assert_sql_name_unique($log_dsp->dsp_hist_links_sql($db_con, true));

        // button add
        $url = $html->url(view_shared::WORD_ADD);
        $t->html_test((new button($url))->add(messages::WORD_ADD), '', 'button_add', $t);


        $t->subheader('HTML list tests');

        // TODO create and set the model objects and
        //      create the api object using the api_obj() function
        //      create and set the dsp object based on the api json

        $lst = new verb_list($usr);
        $lst->add_verb(new verb(1, verbs::IS));
        $lst->add_verb(new verb(2, verbs::IS_PART_OF));
        // TODO use set_from_json to set the display object
        $t->html_test($lst->dsp_obj()->list(verb::class, 'Verbs'), '', 'list_verbs', $t);


        $t->subheader('HTML table tests');

        // create a test set of phrase groups
        $t->phrase_list_zh_mio();
        $grp_city = new group($t->usr1);
        $grp_city->set_phrase_list($t->phrase_list_zh_city());
        $grp_canton = new group($t->usr1);
        $grp_canton->set_phrase_list($t->phrase_list_canton_mio());
        $grp_ch = new group($t->usr1);
        $grp_ch->set_phrase_list($t->phrase_list_ch_mio());
        $grp_city_pct = new group($t->usr1);
        $grp_city_pct->set_phrase_list($t->phrase_list_zh_city_pct());
        $grp_canton_pct = new group($t->usr1);
        $grp_canton_pct->set_phrase_list($t->phrase_list_canton_pct());
        $phr_lst_context = new phrase_list($t->usr1);
        $phr_lst_context->add($t->word_inhabitant()->phrase());

        // create the value for the inhabitants of the city of zurich
        $val_city = new value($t->usr1);
        $val_city->set_grp($grp_city);
        $val_city->set_number(values::CITY_ZH_INHABITANTS_2019);
        $val_city_dsp = new value_dsp($val_city->api_json([api_type::INCL_PHRASES]));
        $val_city_html = $val_city_dsp->name_and_value();
        $t->assert_text_contains('', $val_city_html, words::CITY);

        // create the value for the inhabitants of the city of zurich
        $val_canton = new value($t->usr1);
        $val_canton->set_grp($grp_canton);
        $val_canton->set_number(values::CANTON_ZH_INHABITANTS_2020_IN_MIO);
        $val_canton_dsp = new value_dsp($val_canton->api_json([api_type::INCL_PHRASES]));
        $val_canton_html = $val_canton_dsp->name_and_value();
        $t->assert_text_contains('', $val_canton_html, words::CANTON);

        // create the value for the inhabitants of Switzerland
        $val_ch = new value($t->usr1);
        $val_ch->set_grp($grp_ch);
        $val_ch->set_number(values::CH_INHABITANTS_2019_IN_MIO);
        $val_ch_dsp = new value_dsp($val_ch->api_json([api_type::INCL_PHRASES]));
        $val_ch_html = $val_ch_dsp->name_and_value();
        $t->assert_text_contains('', $val_ch_html, round(values::CH_INHABITANTS_2019_IN_MIO,2));

        // create the formula result for the inhabitants of the city of zurich
        $res_city = new result($t->usr1);
        $res_city->set_grp($grp_city_pct);
        $ch_val_scaled = values::CH_INHABITANTS_2019_IN_MIO * 1000000;
        $res_city->set_number(values::CITY_ZH_INHABITANTS_2019 / $ch_val_scaled);
        $res_city_dsp = new value_dsp($res_city->api_json([api_type::INCL_PHRASES]));
        $res_city_html = $res_city_dsp->name_and_value();
        $t->assert_text_contains('', $res_city_html, words::CITY);

        // create the formula result for the inhabitants of the canton of zurich
        $res_canton = new result($t->usr1);
        $res_canton->set_grp($grp_canton_pct);
        $res_canton->set_number(values::CANTON_ZH_INHABITANTS_2020_IN_MIO / values::CH_INHABITANTS_2019_IN_MIO);
        $res_canton_dsp = new value_dsp($res_canton->api_json([api_type::INCL_PHRASES]));
        $res_canton_html = $res_canton_dsp->display_value_linked('');
        $res_canton_number = round((values::CANTON_ZH_INHABITANTS_2020_IN_MIO / values::CH_INHABITANTS_2019_IN_MIO) * 100,2) . '%';
        $t->assert_text_contains('', $res_canton_html, $res_canton_number);

        // create the formula result list and the table to display the results
        $res_lst = new result_list_dsp();
        $res_lst->add(new result_dsp($res_city->api_json([api_type::INCL_PHRASES])));
        $res_lst->add(new result_dsp($res_canton->api_json([api_type::INCL_PHRASES])));
        $t->html_test($res_lst->table(), '', 'table_result', $t);

        // create the same table as above, but within a context
        $phr_lst_context_dsp = new phrase_list_dsp($phr_lst_context->api_json([api_type::INCL_PHRASES]));
        $t->html_test($res_lst->table($phr_lst_context_dsp), '', 'table_result_context', $t);


        $t->subheader('View component tests');

        $cmp = new component($usr);
        $cmp->set(1, components::TEST_ADD_NAME, comp_type_shared::TEXT);
        $cmp_dsp = new component_dsp($cmp->api_json());
        $t->html_test($cmp_dsp->html(), '', 'component_text', $t);


        // TODO review

        global $usr;
        global $cmp_typ_cac;
        $html = new html_base();

        $is_connected = true; // assumes that the test is done with an internet connection, but if not connected, just show the warning once

        $t->header('Test the view_display class (classes/view_display.php)');

        // test the usage of a view to create the HTML code
        /*
        $wrd = $t->load_word(words::TN_READ);
        $msk = new view($usr);
        $msk->load_by_name(views::TN_READ_RATIO);
        //$result = $msk->display($wrd, $back);
        $target = true;
        //$t->dsp_contains(', view_dsp->display is "'.$result.'" which should contain '.$wrd_abb->name.'', $target, $result);
        */


        $t->header('view component unit test');

        // test if a simple text component can be created
        $cmp = new component($usr);
        $cmp->type_id = $cmp_typ_cac->id(comp_type_shared::TEXT);
        $cmp->set_id(1);
        $cmp->set_name(views::NESN_2016_FS_NAME);
        $cmp_dsp = new component_dsp($cmp->api_json());
        $result = $cmp_dsp->html();
        $target = views::NESN_2016_FS_NAME;
        $t->display('component_dsp->text', $target, $result);


        $t->header('Test the display button class (src/main/php/web/html/button.php )');

        $url = $html->url(view_shared::WORD_ADD);
        $back = '1';
        $target = '<a href="/http/word_add.php" title="Add test"><img src="/images/button_add.svg" alt="Add test"></a>';
        $target = '<a href="/http/word_add.php" title="add new word">';
        $result = (new button($url, $back))->add(messages::WORD_ADD);
        $t->dsp_contains(", btn_add", $target, $result);

        // TODO move e.g. because the edit word button is tested already in the unit tests of the object

        $url = $html->url(view_shared::WORD_DEL);
        $target = '<a href="/http/view.php" title="Del test"><img src="/images/button_del.svg" alt="Del test"></a>';
        $target = '<a href="/http/word_del.php" title="delete word"><i class="far fa-times-circle"></i></a>';
        $result = (new button($url, $back))->del(messages::WORD_DEL);
        $t->dsp_contains(", btn_del", $target, $result);

        $url = $html->url(view_shared::WORD);
        $target = '<a href="/http/view.php" title="Undo test"><img src="/images/button_undo.svg" alt="Undo test"></a>';
        $target = '<a href="/http/word.php" title="undo"><img src="/images/button_undo.svg" alt="undo"></a>';
        $result = (new button($url, $back))->undo(messages::UNDO);
        //$t->display(", btn_undo", $target, $result);

        $url = $html->url(view_shared::WORD_ADD);
        $target = '<a href="/http/view.php" title="Find test"><img src="/images/button_find.svg" alt="Find test"></a>';
        $target = '<a href="/http/word_add.php" title=""><img src="/images/button_find.svg" alt=""></a>';
        $result = (new button($url, $back))->find();
        //$t->display(", btn_find", $target, $result);

        $url = $html->url(view_shared::WORD_ADD);
        $target = '<a href="/http/view.php" title="Show all test"><img src="/images/button_filter_off.svg" alt="Show all test"></a>';
        $target = '<a href="/http/word_add.php" title=""><img src="/images/button_filter_off.svg" alt=""></a>';
        $result = (new button($url, $back))->un_filter();
        //$t->display(", btn_unfilter", $target, $result);

        $url = $html->url(view_shared::WORD_ADD);
        $target = '<h6>YesNo test</h6><a href="/http/view.php&confirm=1" title="Yes">Yes</a>/<a href="/http/view.php&confirm=-1" title="No">No</a>';
        $target = '<h6></h6><a href="/http/word_add.php&confirm=1" title="Yes">Yes</a>/<a href="/http/word_add.php&confirm=-1" title="No">No</a>';
        $result = (new button($url, $back))->yes_no();
        $t->display(", btn_yesno", $target, $result);

        $url = $html->url(view_shared::WORD_ADD);
        $target = '<a href="/http/view.php?words=1" title="back"><img src="/images/button_back.svg" alt="back"></a>';
        $result = (new button($url, $back))->back();
        //$t->display(", btn_back", $target, $result);


        $t->header('Test the display HTML class');

        $target = htmlspecialchars(trim('<html> <head> <title>Header test (zukunft.com)</title> <link rel="stylesheet" type="text/css" href="../../../main/resources/style/style.css" /> </head> <body class="center_form">'));
        $target = htmlspecialchars(trim('<title>Header test (zukunft.com)</title>'));
        $result = htmlspecialchars(trim($html->header('Header test', 'center_form')));
        $t->dsp_contains(", dsp_header", $target, $result);


        $t->header('Test general frontend scripts (e.g. /about.php)');

        // check if the about page contains at least some basic keywords
        // TODO activate Prio 3: $result = file_get_contents('https://www.zukunft.com/http/about.php?id=1');
        $target = 'zukunft.com AG';
        if (strpos($result, $target) > 0) {
            $result = $target;
        } else {
            $result = '';
        }
        // about does not return a page for unknown reasons at the moment
        // $t->dsp_contains(', frontend about.php '.$result.' contains at least ' . $target, $target, $result, $t::TIMEOUT_LIMIT_PAGE);

        $is_connected = $t->dsp_web_test(
            'http/privacy_policy.html',
            'Swiss purpose of data protection',
            ', frontend privacy_policy.php contains at least');
        $is_connected = $t->dsp_web_test(
            'http/error_update.php?id=1',
            'not permitted',
            ', frontend error_update.php contains at least', $is_connected);
        $t->dsp_web_test(
            'http/find.php?pattern=' . words::TN_ABB,
            words::TN_ABB,
            ', frontend find.php contains at least', $is_connected);



    }

}
