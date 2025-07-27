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

use cfg\const\paths;
use html\const\paths as html_paths;

include_once paths::SHARED_TYPES . 'component_type.php';
include_once paths::SHARED_CONST . 'views.php';
include_once html_paths::COMPONENT . 'component_exe.php';
include_once html_paths::HTML . 'html_selector.php';
include_once html_paths::HTML . 'button.php';
include_once html_paths::RESULT . 'result_list.php';
include_once html_paths::VERB . 'verb_list.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_TYPES . 'verbs.php';

use cfg\component\component;
use cfg\group\group;
use cfg\phrase\phrase_list;
use cfg\result\result;
use cfg\value\value;
use cfg\verb\verb;
use cfg\verb\verb_list;
use html\verb\verb_list as verb_list_dsp;
use html\button;
use html\component\component_exe as component_dsp;
use html\html_base;
use html\phrase\phrase_list as phrase_list_dsp;
use html\result\result as result_dsp;
use html\result\result_list as result_list_dsp;
use html\value\value as value_dsp;
use html\verb\verb as verb_dsp;
use shared\library;
use shared\const\components;
use shared\const\values;
use shared\const\views;
use shared\const\words;
use shared\enum\messages as msg_id;
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

        // start the test section (ts)
        $ts = 'unit ui html base ';
        $t->header($ts);

        $t->subheader($ts . 'login');

        $created_html = $html->about();
        $expected_html = $t->file('web/html/about.html');
        $t->display('about', $lib->trim_html($expected_html), $lib->trim_html($created_html));


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
        //$t->assert_sql_name_unique($log_dsp->dsp_hist_links_sql($db_con, true));

        // button add
        $url = $html->url(views::WORD_ADD);
        $t->html_test((new button($url))->add(msg_id::WORD_ADD), '', 'button_add', $t);

        $t->subheader('unit html table tests');

        // create a test set of phrase groups
        $t->phrase_list_zh_mio();
        $grp_city = new group($t->usr1);
        $grp_city->set_phrase_list($t->phrase_list_zh_city_2019());
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
        $val_city_html = $val_city_dsp->name_link();
        $t->assert_text_contains('', $val_city_html, words::CITY);

        // create the value for the inhabitants of the city of zurich
        $val_canton = new value($t->usr1);
        $val_canton->set_grp($grp_canton);
        $val_canton->set_number(values::CANTON_ZH_INHABITANTS_2020_IN_MIO);
        $val_canton_dsp = new value_dsp($val_canton->api_json([api_type::INCL_PHRASES]));
        $val_canton_html = $val_canton_dsp->name_link();
        $t->assert_text_contains('', $val_canton_html, words::CANTON);

        // create the value for the inhabitants of Switzerland
        $val_ch = new value($t->usr1);
        $val_ch->set_grp($grp_ch);
        $val_ch->set_number(values::CH_INHABITANTS_2019_IN_MIO);
        $val_ch_dsp = new value_dsp($val_ch->api_json([api_type::INCL_PHRASES]));
        $val_ch_html = $val_ch_dsp->name_link();
        $t->assert_text_contains('', $val_ch_html, round(values::CH_INHABITANTS_2019_IN_MIO, 2));

        // create the formula result for the inhabitants of the city of zurich
        $res_city = new result($t->usr1);
        $res_city->set_grp($grp_city_pct);
        $ch_val_scaled = values::CH_INHABITANTS_2019_IN_MIO * 1000000;
        $res_city->set_number(values::CITY_ZH_INHABITANTS_2019 / $ch_val_scaled);
        $res_city_dsp = new value_dsp($res_city->api_json([api_type::INCL_PHRASES]));
        $res_city_html = $res_city_dsp->name_link();
        $t->assert_text_contains('', $res_city_html, words::CITY);

        // create the formula result for the inhabitants of the canton of zurich
        $res_canton = new result($t->usr1);
        $res_canton->set_grp($grp_canton_pct);
        $res_canton->set_number(values::CANTON_ZH_INHABITANTS_2020_IN_MIO / values::CH_INHABITANTS_2019_IN_MIO);
        $res_canton_dsp = new value_dsp($res_canton->api_json([api_type::INCL_PHRASES]));
        $res_canton_html = $res_canton_dsp->value_edit('');
        $res_canton_number = round((values::CANTON_ZH_INHABITANTS_2020_IN_MIO / values::CH_INHABITANTS_2019_IN_MIO) * 100, 2) . '%';
        $t->assert_text_contains('', $res_canton_html, $res_canton_number);

        // create the formula result list and the table to display the results
        $res_lst = new result_list_dsp();
        $res_lst->add(new result_dsp($res_city->api_json([api_type::INCL_PHRASES])));
        $res_lst->add(new result_dsp($res_canton->api_json([api_type::INCL_PHRASES])));
        $t->html_test($res_lst->table(), '', 'table_result', $t);

        // create the same table as above, but within a context
        $phr_lst_context_dsp = new phrase_list_dsp($phr_lst_context->api_json([api_type::INCL_PHRASES]));
        $t->html_test($res_lst->table($phr_lst_context_dsp), '', 'table_result_context', $t);


        $t->subheader('unit html view component tests');

        $cmp = new component($usr);
        $cmp->set(components::WORD_ID, components::TEST_ADD_NAME);
        $cmp->set_type(comp_type_shared::TEXT, $usr);
        $cmp_dsp = new component_dsp($cmp->api_json());
        $t->html_test($cmp_dsp->html(), '', 'component_text', $t);


        $t->header('unit html list tests');

        // TODO create and set the model objects and
        //      create the api object using the api_obj() function
        //      create and set the dsp object based on the api json

        $lst = new verb_list($usr);
        $lst->add_verb(new verb(1, verbs::IS));
        $lst->add_verb(new verb(2, verbs::PART_NAME));
        // TODO use set_from_json to set the display object
        $vrb_lst_dsp = new verb_list_dsp();
        $vrb_lst_dsp->set_from_json_array($lst->api_json_array());
        $t->html_test($vrb_lst_dsp->list(verb_dsp::class, 'Verbs'), '', 'list_verbs', $t);

        $test_name = 'sort a named list by the name';
        $lst = $t->phrase_list_zh_mio();
        $names_unsorted = $lst->names();
        $lst->sort_by_name();
        $names = $lst->names();
        $names_sorted = $names;
        natcasesort($names_sorted);
        $t->assert($test_name, implode(',', $names), implode(',', $names_sorted));
        $test_name = 'unsorted named list';
        $t->assert_not($test_name, implode(',', $names), implode(',', $names_unsorted));




        // TODO review

        global $usr;
        global $cmp_typ_cac;
        $html = new html_base();

        $is_connected = true; // assumes that the test is done with an internet connection, but if not connected, just show the warning once

        $t->header('unit html view tests');

        // test the usage of a view to create the HTML code
        /*
        $wrd = $t->load_word(words::TN_READ);
        $msk = new view($usr);
        $msk->load_by_name(views::TN_READ_RATIO);
        //$result = $msk->display($wrd, $back);
        $target = true;
        //$t->dsp_contains(', view_dsp->display is "'.$result.'" which should contain '.$wrd_abb->name.'', $target, $result);
        */


        $t->header('unit view component unit test');

        // test if a simple text component can be created
        $cmp = new component($usr);
        $cmp->type_id = $cmp_typ_cac->id(comp_type_shared::TEXT);
        $cmp->set_id(1);
        $cmp->set_name(views::NESN_2016_FS_NAME);
        $cmp_dsp = new component_dsp($cmp->api_json());
        $result = $cmp_dsp->html();
        $target = views::NESN_2016_FS_NAME;
        $t->display('component_dsp->text', $target, $result);


        $t->header('unit html button tests');

        $url = $html->url(views::WORD_ADD);
        $back = '1';
        $target = '<a href="/http/word_add.php" title="Add test"><img src="/images/button_add.svg" alt="Add test"></a>';
        $target = '<a href="/http/word_add.php" title="add new word">';
        $result = (new button($url, $back))->add(msg_id::WORD_ADD);
        $t->dsp_contains(", btn_add", $target, $result);

        // TODO move e.g. because the edit word button is tested already in the unit tests of the object

        $url = $html->url(views::WORD_DEL);
        $target = '<a href="/http/view.php" title="Del test"><img src="/images/button_del.svg" alt="Del test"></a>';
        $target = '<a href="/http/word_del.php" title="delete word"><i class="far fa-times-circle"></i></a>';
        $result = (new button($url, $back))->del(msg_id::WORD_DEL);
        $t->dsp_contains(", btn_del", $target, $result);

        $url = $html->url(views::WORD);
        $target = '<a href="/http/view.php" title="Undo test"><img src="/images/button_undo.svg" alt="Undo test"></a>';
        $target = '<a href="/http/word.php" title="undo"><img src="/images/button_undo.svg" alt="undo"></a>';
        $result = (new button($url, $back))->undo(msg_id::UNDO);
        //$t->display(", btn_undo", $target, $result);

        $url = $html->url(views::WORD_ADD);
        $target = '<a href="/http/view.php" title="Find test"><img src="/images/button_find.svg" alt="Find test"></a>';
        $target = '<a href="/http/word_add.php" title=""><img src="/images/button_find.svg" alt=""></a>';
        $result = (new button($url, $back))->find(msg_id::FIND);
        //$t->display(", btn_find", $target, $result);

        $url = $html->url(views::WORD_ADD);
        $target = '<a href="/http/view.php" title="Show all test"><img src="/images/button_filter_off.svg" alt="Show all test"></a>';
        $target = '<a href="/http/word_add.php" title=""><img src="/images/button_filter_off.svg" alt=""></a>';
        $result = (new button($url, $back))->un_filter(msg_id::REMOVE_FILTER);
        //$t->display(", btn_unfilter", $target, $result);

        $url = $html->url(views::WORD_ADD);
        $target = '<h6>YesNo test</h6><a href="/http/view.php&confirm=1" title="Yes">Yes</a>/<a href="/http/view.php&confirm=-1" title="No">No</a>';
        $target = '<h6></h6><a href="/http/word_add.php&confirm=1" title="Yes">Yes</a>/<a href="/http/word_add.php&confirm=-1" title="No">No</a>';
        $result = (new button($url, $back))->yes_no();
        $t->display(", btn_yesno", $target, $result);

        $url = $html->url(views::WORD_ADD);
        $target = '<a href="/http/view.php?words=1" title="back"><img src="/images/button_back.svg" alt="back"></a>';
        $result = (new button($url, $back))->back();
        //$t->display(", btn_back", $target, $result);




    }

}
