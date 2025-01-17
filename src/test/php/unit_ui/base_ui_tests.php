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
include_once SHARED_PATH . 'views.php';
include_once WEB_HTML_PATH . 'html_selector.php';
include_once WEB_HTML_PATH . 'button.php';
include_once WEB_RESULT_PATH . 'result_list.php';
include_once SHARED_TYPES_PATH . 'verbs.php';

use api\component\component as component_api;
use api\phrase\group as group_api;
use api\phrase\phrase as phrase_api;
use api\phrase\phrase_list as phrase_list_api;
use api\result\result as result_api;
use api\value\value as value_api;
use api\view\view as view_api;
use api\word\word as word_api;
use cfg\component\component;
use cfg\verb\verb;
use cfg\verb\verb_list;
use cfg\view\view;
use html\button;
use html\component\component as component_dsp;
use html\html_base;
use html\result\result as result_dsp;
use html\result\result_list as result_list_dsp;
use html\system\messages;
use shared\library;
use shared\types\component_type as comp_type_shared;
use shared\types\verbs;
use shared\views as view_shared;
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
        $url = $html->url(view_shared::MC_WORD_ADD);
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

        // create a test set of phrase
        $phr_id = 1;
        $phr_zh = $this->phrase_api_word( $phr_id, word_api::TN_ZH); $phr_id++;
        $phr_city = $this->phrase_api_word($phr_id, word_api::TN_CITY); $phr_id++;
        $phr_canton = $this->phrase_api_word($phr_id, word_api::TN_CANTON); $phr_id++;
        $phr_ch = $this->phrase_api_word($phr_id, word_api::TN_CH); $phr_id++;
        $phr_inhabitant = $this->phrase_api_word($phr_id, word_api::TN_INHABITANT); $phr_id++;
        $phr_2019 = $this->phrase_api_word($phr_id, word_api::TN_2019); $phr_id++;
        $phr_mio = $this->phrase_api_word($phr_id, word_api::TN_MIO_SHORT);
        $phr_pct = $this->phrase_api_word($phr_id, word_api::TN_PCT);

        // create a test set of phrase groups
        $grp_id = 1;
        $phr_grp_city = new group_api($grp_id); $grp_id++;
        $phr_grp_city->add($phr_zh);
        $phr_grp_city->add($phr_city);
        $phr_grp_city->add($phr_inhabitant);
        $phr_grp_city->add($phr_2019);
        $phr_grp_canton = new group_api($grp_id); $grp_id++;
        $phr_grp_canton->add($phr_zh);
        $phr_grp_canton->add($phr_canton);
        $phr_grp_canton->add($phr_inhabitant);
        $phr_grp_canton->add($phr_mio);
        $phr_grp_canton->add($phr_2019);
        $phr_grp_ch = new group_api($grp_id);
        $phr_grp_ch->add($phr_ch);
        $phr_grp_ch->add($phr_mio);
        $phr_grp_ch->add($phr_inhabitant);
        $phr_grp_ch->add($phr_2019);
        $phr_grp_city_pct = new group_api($grp_id); $grp_id++;
        $phr_grp_city_pct->add($phr_zh);
        $phr_grp_city_pct->add($phr_city);
        $phr_grp_city_pct->add($phr_inhabitant);
        $phr_grp_city_pct->add($phr_2019);
        $phr_grp_city_pct->add($phr_pct);
        $phr_grp_canton_pct = new group_api($grp_id); $grp_id++;
        $phr_grp_canton_pct->add($phr_zh);
        $phr_grp_canton_pct->add($phr_canton);
        $phr_grp_canton_pct->add($phr_inhabitant);
        $phr_grp_canton_pct->add($phr_2019);
        $phr_grp_canton_pct->add($phr_pct);
        $phr_lst_context = new phrase_list_api();
        $phr_lst_context->add($phr_inhabitant);

        // create the value for the inhabitants of the city of zurich
        $val_id = 1;
        $val_city = new value_api($val_id); $val_id++;
        $val_city->set_grp($phr_grp_city);
        $val_city->set_number(value_api::TV_CITY_ZH_INHABITANTS_2019);

        // create the value for the inhabitants of the city of zurich
        $val_canton = new value_api($val_id); $val_id++;
        $val_canton->set_grp($phr_grp_canton);
        $val_canton->set_number(value_api::TV_CANTON_ZH_INHABITANTS_2020_IN_MIO);

        // create the value for the inhabitants of Switzerland
        $val_ch = new value_api($val_id);
        $val_ch->set_grp($phr_grp_ch);
        $val_ch->set_number(value_api::TV_CH_INHABITANTS_2019_IN_MIO);

        // create the formula result for the inhabitants of the city of zurich
        $res_id = 1;
        $res_city = new result_api($res_id); $res_id++;
        $res_city->set_grp($phr_grp_city_pct);
        $ch_val_scaled = value_api::TV_CH_INHABITANTS_2019_IN_MIO * 1000000;
        $res_city->set_number(value_api::TV_CITY_ZH_INHABITANTS_2019 / $ch_val_scaled);

        // create the formula result for the inhabitants of the city of zurich
        $res_canton = new result_api($res_id); $res_id++;
        $res_canton->set_grp($phr_grp_canton_pct);
        $res_canton->set_number(value_api::TV_CANTON_ZH_INHABITANTS_2020_IN_MIO / value_api::TV_CH_INHABITANTS_2019_IN_MIO);

        // create the formula result list and the table to display the results
        $res_lst = new result_list_dsp();
        $res_lst->add(new result_dsp($res_city->get_json()));
        $res_lst->add(new result_dsp($res_canton->get_json()));
        $t->html_test($res_lst->table(), '', 'table_result', $t);

        // create the same table as above, but within a context
        $t->html_test($res_lst->table($phr_lst_context->dsp_obj()), '', 'table_result_context', $t);


        $t->subheader('View component tests');

        $cmp = new component($usr);
        $cmp->set(1, component_api::TN_ADD, comp_type_shared::TEXT);
        $cmp_dsp = new component_dsp($cmp->api_json());
        $t->html_test($cmp_dsp->html(), '', 'component_text', $t);

        $wrd = new word_api();
        $wrd->set_name(word_api::TN_ADD);
        $cmp->obj = $wrd;


        // TODO review

        global $usr;
        global $cmp_typ_cac;
        $html = new html_base();

        $is_connected = true; // assumes that the test is done with an internet connection, but if not connected, just show the warning once

        $t->header('Test the view_display class (classes/view_display.php)');

        // test the usage of a view to create the HTML code
        /*
        $wrd = $t->load_word(word_api::TN_READ);
        $msk = new view($usr);
        $msk->load_by_name(view_api::TN_READ_RATIO);
        //$result = $msk->display($wrd, $back);
        $target = true;
        //$t->dsp_contains(', view_dsp->display is "'.$result.'" which should contain '.$wrd_abb->name.'', $target, $result);
        */


        $t->header('Test the view component display class (classes/component.php)');

        // test if a simple text component can be created
        $cmp = new component($usr);
        $cmp->type_id = $cmp_typ_cac->id(comp_type_shared::TEXT);
        $cmp->set_id(1);
        $cmp->set_name(view_api::TN_READ_NESN_2016);
        $cmp_dsp = new component_dsp($cmp->api_json());
        $result = $cmp_dsp->html();
        $target = view_api::TN_READ_NESN_2016;
        $t->display('component_dsp->text', $target, $result);


        $t->header('Test the display button class (src/main/php/web/html/button.php )');

        $url = $html->url(view_shared::MC_WORD_ADD);
        $back = '1';
        $target = '<a href="/http/word_add.php" title="Add test"><img src="/images/button_add.svg" alt="Add test"></a>';
        $target = '<a href="/http/word_add.php" title="add new word">';
        $result = (new button($url, $back))->add(messages::WORD_ADD);
        $t->dsp_contains(", btn_add", $target, $result);

        // TODO move e.g. because the edit word button is tested already in the unit tests of the object

        $url = $html->url(view_shared::MC_WORD_DEL);
        $target = '<a href="/http/view.php" title="Del test"><img src="/images/button_del.svg" alt="Del test"></a>';
        $target = '<a href="/http/word_del.php" title="delete word"><i class="far fa-times-circle"></i></a>';
        $result = (new button($url, $back))->del(messages::WORD_DEL);
        $t->dsp_contains(", btn_del", $target, $result);

        $url = $html->url(view_shared::MC_WORD);
        $target = '<a href="/http/view.php" title="Undo test"><img src="/images/button_undo.svg" alt="Undo test"></a>';
        $target = '<a href="/http/word.php" title="undo"><img src="/images/button_undo.svg" alt="undo"></a>';
        $result = (new button($url, $back))->undo(messages::UNDO);
        //$t->display(", btn_undo", $target, $result);

        $url = $html->url(view_shared::MC_WORD_ADD);
        $target = '<a href="/http/view.php" title="Find test"><img src="/images/button_find.svg" alt="Find test"></a>';
        $target = '<a href="/http/word_add.php" title=""><img src="/images/button_find.svg" alt=""></a>';
        $result = (new button($url, $back))->find();
        //$t->display(", btn_find", $target, $result);

        $url = $html->url(view_shared::MC_WORD_ADD);
        $target = '<a href="/http/view.php" title="Show all test"><img src="/images/button_filter_off.svg" alt="Show all test"></a>';
        $target = '<a href="/http/word_add.php" title=""><img src="/images/button_filter_off.svg" alt=""></a>';
        $result = (new button($url, $back))->un_filter();
        //$t->display(", btn_unfilter", $target, $result);

        $url = $html->url(view_shared::MC_WORD_ADD);
        $target = '<h6>YesNo test</h6><a href="/http/view.php&confirm=1" title="Yes">Yes</a>/<a href="/http/view.php&confirm=-1" title="No">No</a>';
        $target = '<h6></h6><a href="/http/word_add.php&confirm=1" title="Yes">Yes</a>/<a href="/http/word_add.php&confirm=-1" title="No">No</a>';
        $result = (new button($url, $back))->yes_no();
        $t->display(", btn_yesno", $target, $result);

        $url = $html->url(view_shared::MC_WORD_ADD);
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
            'http/find.php?pattern=' . word_api::TN_ABB,
            word_api::TN_ABB,
            ', frontend find.php contains at least', $is_connected);



    }

    function phrase_api_word($id, $name): phrase_api
    {
        $wrd = new word_api($id, $name);
        return new phrase_api($wrd);
    }

}
