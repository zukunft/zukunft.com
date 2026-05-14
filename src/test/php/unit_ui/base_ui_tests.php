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
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once paths::SHARED_TYPES . 'component_types.php';
include_once paths::SHARED_CONST . 'views.php';
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
use Zukunft\ZukunftCom\main\php\cfg\value\value;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb_list;
use Zukunft\ZukunftCom\main\php\web\formula\formula;
use Zukunft\ZukunftCom\main\php\web\helper\url_mapper;
use Zukunft\ZukunftCom\main\php\web\html\button;
use Zukunft\ZukunftCom\main\php\web\ref\source;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\verb\verb_list as verb_list_ui;
use Zukunft\ZukunftCom\main\php\web\component\component_exe as component_ui;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase_list as phrase_list_ui;
use Zukunft\ZukunftCom\main\php\web\result\result as result_ui;
use Zukunft\ZukunftCom\main\php\web\result\result_list as result_list_ui;
use Zukunft\ZukunftCom\main\php\web\value\value as value_ui;
use Zukunft\ZukunftCom\main\php\web\verb\verb as verb_ui;
use Zukunft\ZukunftCom\main\php\web\word\word;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\const\components;
use Zukunft\ZukunftCom\main\php\shared\const\values;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\shared\types\component_types as comp_type_shared;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
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

        global $usr;
        $lib = new library();
        $html = new html_base();
        $t_wrd = new test_words($t);
        $t_phr = new test_phrases($t);
        $t_src = new test_sources($t);
        $t_frm = new test_formulas($t);

        // start the test section (ts)
        $ts = 'unit ui html base ';
        $t->header($ts);

        $t->subheader($ts . 'login');

        $created_html = $html->about_page();
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
        //$t->assert_sql_name_unique($log_dsp->dsp_hist_links_sql($db_con, true));

        // button add
        $url = $html->url(views::WORD_ADD);
        $t->html_page_test(new button($url)->add(msg_id::WORD_ADD), '', 'button_add', $t);

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
        $val_city_dsp = new value_ui($val_city->api_json([api_types::INCL_PHRASES]));
        $val_city_html = $val_city_dsp->name_link();
        $t->assert_text_contains('', $val_city_html, words::CITY);

        // create the value for the inhabitants of the city of zurich
        $val_canton = new value($t->usr1);
        $val_canton->set_grp($grp_canton);
        $val_canton->set_number(values::CANTON_ZH_INHABITANTS_2020_IN_MIO);
        $val_canton_dsp = new value_ui($val_canton->api_json([api_types::INCL_PHRASES]));
        $val_canton_html = $val_canton_dsp->name_link();
        $t->assert_text_contains('', $val_canton_html, words::CANTON);

        // create the value for the inhabitants of Switzerland
        $val_ch = new value($t->usr1);
        $val_ch->set_grp($grp_ch);
        $val_ch->set_number(values::CH_INHABITANTS_2019_IN_MIO);
        $val_ch_dsp = new value_ui($val_ch->api_json([api_types::INCL_PHRASES]));
        $val_ch_html = $val_ch_dsp->name_link();
        $t->assert_text_contains('', $val_ch_html, round(values::CH_INHABITANTS_2019_IN_MIO, 2));

        // create the formula result for the inhabitants of the city of zurich
        $res_city = new result($t->usr1);
        $res_city->set_grp($grp_city_pct);
        $ch_val_scaled = values::CH_INHABITANTS_2019_IN_MIO * 1000000;
        $res_city->set_number(values::CITY_ZH_INHABITANTS_2019 / $ch_val_scaled);
        $res_city_dsp = new value_ui($res_city->api_json([api_types::INCL_PHRASES]));
        $res_city_html = $res_city_dsp->name_link();
        $t->assert_text_contains('', $res_city_html, words::CITY);

        // create the formula result for the inhabitants of the canton of zurich
        $res_canton = new result($t->usr1);
        $res_canton->set_grp($grp_canton_pct);
        $res_canton->set_number(values::CANTON_ZH_INHABITANTS_2020_IN_MIO / values::CH_INHABITANTS_2019_IN_MIO);
        $res_canton_dsp = new value_ui($res_canton->api_json([api_types::INCL_PHRASES]));
        $res_canton_html = $res_canton_dsp->value_edit('');
        $res_canton_number = round((values::CANTON_ZH_INHABITANTS_2020_IN_MIO / values::CH_INHABITANTS_2019_IN_MIO) * 100, 2) . '%';
        $t->assert_text_contains('', $res_canton_html, $res_canton_number);

        // create the formula result list and the table to display the results
        $res_lst = new result_list_ui();
        $res_lst->add_result(new result_ui($res_city->api_json([api_types::INCL_PHRASES])));
        $res_lst->add_result(new result_ui($res_canton->api_json([api_types::INCL_PHRASES])));
        $t->html_page_test($res_lst->table(), '', 'table_result', $t);

        // create the same table as above, but within a context
        $phr_lst_context_dsp = new phrase_list_ui($phr_lst_context->api_json([api_types::INCL_PHRASES]));
        $t->html_page_test($res_lst->table($phr_lst_context_dsp), '', 'table_result_context', $t);


        $t->subheader($ts . 'unit html view component tests');

        $cmp = new component($t->usr1);
        $cmp->set(components::WORD_ID, components::TEST_ADD_NAME);
        $cmp->set_type(comp_type_shared::TEXT, $t->usr1);
        $cmp_dsp = new component_ui($cmp->api_json());
        $t->html_page_test($cmp_dsp->html(), '', 'component_text', $t);


        $t->subheader($ts . 'list');

        // TODO create and set the model objects and
        //      create the api object using the api_obj() function
        //      create and set the dsp object based on the api json

        $lst = new verb_list($t->usr1);
        $lst->add_verb(new verb(1, verbs::IS));
        $lst->add_verb(new verb(2, verbs::PART_NAME));
        // TODO use set_from_json to set the display object
        $vrb_lst_dsp = new verb_list_ui();
        $vrb_lst_dsp->set_from_json_array($lst->api_json_array());
        $t->html_page_test($vrb_lst_dsp->list(verb_ui::class, 'Verbs'), '', 'list_verbs', $t);

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

        global $usr;
        global $sys;
        $html = new html_base();

        $is_connected = true; // assumes that the test is done with an internet connection, but if not connected, just show the warning once

        $t->subheader($ts . 'unit html view tests');

        // test the usage of a view to create the HTML code
        /*
        $wrd = $t->load_word(words::TN_READ);
        $msk = new view($usr);
        $msk->load_by_name(views::TN_READ_RATIO);
        //$result = $msk->display($wrd, $back);
        $target = true;
        //$t->dsp_contains(', view_dsp->display is "'.$result.'" which should contain '.$wrd_abb->name.'', $target, $result);
        */


        $t->subheader($ts . 'component');

        // test if a simple text component can be created
        $cmp = new component($t->usr1);
        $usr_msg = new user_message();
        $cmp->type_id = $sys->typ_lst->cmp_typ->id(comp_type_shared::TEXT);
        $cmp->id = 1;
        $cmp->set_name(views::NESN_2016_FS_NAME);
        $cmp_dsp = new component_ui($cmp->api_json());
        $result = $cmp_dsp->html();
        $target = views::NESN_2016_FS_NAME;
        $t->assert('component_dsp->text', $result, $target);


        $t->subheader($ts . 'button tests');
        $test_name = 'a sandbox object e.g. word add button html code';
        $target = '<a href="' . api::MAIN_SCRIPT . '?' . url_var::MASK . '=word_add&back=1" title="add new word"><i class="far fa-plus-square"></i></a>';
        $wrd = new word();
        $t->assert($test_name, $wrd->btn_add('1'), $target);

        $test_name = 'a sandbox object e.g. source change button html code';
        $target = '<a href="' . api::MAIN_SCRIPT . '?' . url_var::MASK . '=source_edit&id=1&back=1" title="source_edit"><i class="far fa-edit"></i></a>';
        $src = new source();
        $src->set_from_json($t_src->source_reserved()->api_json(), $usr_msg);
        $t->assert($test_name, $src->btn_edit('1'), $target);

        $test_name = 'a sandbox object e.g. formula delete button html code';
        $target = '<a href="' . api::MAIN_SCRIPT . '?' . url_var::MASK . '=formula_del&id=1&back=1" title="delete this formula of scale minute to sec"><i class="far fa-times-circle"></i></a>';
        $frm = new formula();
        $frm->set_from_json($t_frm->formula()->api_json(), $usr_msg);
        $t->assert($test_name, $frm->btn_del('1'), $target);


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

        $url = $html->url(views::WORD_NAME);
        $target = '<a href="/http/view.php" title="Undo test"><img src="/images/button_undo.svg" alt="Undo test"></a>';
        $target = '<a href="/http/word.php" title="undo"><img src="/images/button_undo.svg" alt="undo"></a>';
        $result = (new button($url, $back))->undo(msg_id::UNDO);
        //$t->assert(", btn_undo", $result, $target);

        $url = $html->url(views::WORD_ADD);
        $target = '<a href="/http/view.php" title="Find test"><img src="/images/button_find.svg" alt="Find test"></a>';
        $target = '<a href="/http/word_add.php" title=""><img src="/images/button_find.svg" alt=""></a>';
        $result = (new button($url, $back))->find(msg_id::FIND);
        //$t->assert(", btn_find", $result, $target);

        $url = $html->url(views::WORD_ADD);
        $target = '<a href="/http/view.php" title="Show all test"><img src="/images/button_filter_off.svg" alt="Show all test"></a>';
        $target = '<a href="/http/word_add.php" title=""><img src="/images/button_filter_off.svg" alt=""></a>';
        $result = (new button($url, $back))->un_filter(msg_id::REMOVE_FILTER);
        //$t->assert(", btn_unfilter", $result, $target);

        $url = $html->url(views::WORD_ADD);
        $target = '<h6>YesNo test</h6><a href="/http/view.php&confirm=1" title="Yes">Yes</a>/<a href="/http/view.php&confirm=-1" title="No">No</a>';
        $target = '<h6></h6><a href="/http/word_add.php&confirm=1">yes</a>/<a href="/http/word_add.php&confirm=-1">no</a>';
        $result = (new button($url, $back))->yes_no();
        $t->assert(", btn_yesno", $result, $target);

        $url = $html->url(views::WORD_ADD);
        $target = '<a href="' . api::MAIN_SCRIPT . '?words=1" title="back"><img src="/images/button_back.svg" alt="back"></a>';
        $result = (new button($url, $back))->back();
        //$t->assert(", btn_back", $result, $target);

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
        $t->assert($test_name, $result, '/http/view.php?m=61&9m=3&9id=123');

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
        $t->assert($test_name, $result, '/http/view.php?m=3&id=123&9m=1');

        $lib = new library();
        $usr_msg = new user_message();
        $url_test = new test_mappers($t);

        $t->subheader($ts . 'url mapper');
        $url_map = new url_mapper();
        $test_name = 'add default value of view';
        $url = 'http://localhost' . api::MAIN_SCRIPT . '?id=1';
        $url_array = $url_map->url_to_standard($lib->url_array($url), $usr_msg);
        $view = $url_array[url_var::MASK];
        $t->assert($test_name, $view, views::START_ID);
        $test_name = 'add default value of step';
        $url = 'http://localhost' . api::MAIN_SCRIPT . '?' . url_var::MASK . '=3&id=1&debug=-1';
        $url_array = $url_map->url_to_standard($lib->url_array($url), $usr_msg);
        $step = $url_array[url_var::STEP];
        $t->assert($test_name, $step, 0);
        $test_name = 'add default value of view for human-readable url';
        $url = 'http://localhost' . api::MAIN_SCRIPT . '?mask_id=&verb_id=3';
        $url_array = $url_map->url_to_standard($lib->url_array($url), $usr_msg);
        $view = $url_array[url_var::MASK];
        $t->assert($test_name, $view, views::START_ID);
        $test_name = 'convert the standard url to human-readable url';
        $url = 'http://localhost' . api::MAIN_SCRIPT . '?' . url_var::MASK . '=2&id=1&debug=-1';
        $url_human = $url_test->test_url($url_map->standard_url_to_human($lib->url_array_with($url), $usr_msg));
        $url_array = $lib->url_array($url_human);
        $view = $url_array[url_var::MASK_HUMAN];
        $t->assert($test_name, $view, views::WORD_ADD_ID);
        $test_name = 'convert the standard url to pod interchangeable url';
        $url = 'http://localhost' . api::MAIN_SCRIPT . '?' . url_var::MASK . '=2&id=1&debug=-1';
        $url_pod = $url_test->test_url($url_map->standard_url_to_pod($lib->url_array_with($url), $usr_msg));
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
        $url_map->url_to_standard($lib->url_array($url), $usr_msg);
        $err_msg = $usr_msg->var_message_text();
        $t->assert($test_name, $err_msg, 'url mapper for "debug" is missing, url mapper for "id" is missing, url mapper for "mapping_missing" is missing');

    }

}
