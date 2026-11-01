<?php

/*

    test/unit/html/phrase_list.php - testing of the html frontend functions for phrase lists
    ------------------------------
  

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

include_once paths::SHARED_TYPES . 'verbs.php';

use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\test\php\create\test_phrases;
use Zukunft\ZukunftCom\test\php\create\test_triples;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class phrase_list_ui_tests
{
    function run(test_cleanup $t): void
    {

        $html = new html_base();
        $t_wrd = new test_words($t);
        $t_trp = new test_triples($t);
        $t_phr = new test_phrases($t);
        $msg = new user_message();

        // start the test section (ts)
        $ts = 'unit ui html phrase list ';
        $t->header($ts);

        // fill the phrase list based on the api message
        $db_lst = $t_phr->phrase_list();
        $lst = new phrase_list($db_lst->api_json());
        $t->assert('HTML phrase list names match backend names', $lst->names(), $db_lst->names());

        $t->subheader($ts . 'api mapper');

        // each phrase of the api json is added to the list
        $test_name = 'the api mapper adds the phrases of the api json';
        $phr_lst_map = new phrase_list();
        $phr_lst_map->api_mapper(json_decode($db_lst->api_json(), true));
        $t->assert($test_name, $phr_lst_map->names(), $db_lst->names());

        // without the class the mapper cannot know if a phrase is a word or a triple, so it reports it
        $test_name = 'the api mapper reports a phrase without the class';
        $phr_lst_map = new phrase_list();
        $api_json = [[json_fields::ID => word_names::MATH_ID, json_fields::NAME => word_names::MATH]];
        $t->assert_false($test_name, $phr_lst_map->api_mapper($api_json)->is_ok());

        // create the phrase list test set
        $form = 'phrase_list_ui_test';
        $lst = new phrase_list();
        $phr_city = $t_trp->zh_city()->phrase();
        $phr_canton = $t_trp->zh_canton()->phrase();
        $phr_ch = $t_wrd->word_ch()->phrase();
        $phr_city_ui = new phrase($phr_city->api_json());
        $phr_canton_ui = new phrase($phr_canton->api_json());
        $phr_ch_ui = new phrase($phr_ch->api_json());
        $lst->add_phrase($phr_city_ui);
        $lst->add_phrase($phr_canton_ui);
        $lst->add_phrase($phr_ch_ui);

        // test the phrase list display functions
        $test_page = $html->text_h2('phrase list display test');
        /*
        $test_page .= 'names with links: ' . $lst->display() . '<br>';
        $test_page .= 'table cells<br>';
        $test_page .= $lst->tbl();
        */

        $from_rows = 'selector: ' . '<br>';
        $from_rows .= $lst->selector($form, 0, url_var::PHRASE, msg_id::FORM_SELECT_PHRASE) . '<br>';
        $test_page .= $html->form($form, $from_rows);

        $t->html_page_test($test_page, 'phrase_list', 'phrase_list', $msg);

        // the phrase list uses the add function of sandbox_list_named
        $t->subheader($ts . 'add');

        // a phrase that is not yet in the list is added
        $test_name = 'a new phrase is added';
        $add_lst = new phrase_list();
        $added = $add_lst->add($phr_city_ui, $msg);
        $t->assert_true($test_name, $added);
        $msg->reset();

        // negative: a phrase that is already in the list is not added again
        $test_name = 'a phrase that is already in the list is not added again';
        $add_lst = new phrase_list();
        $add_lst->add($phr_city_ui, $msg);
        $added = $add_lst->add($phr_city_ui, $msg);
        $t->assert_false($test_name, $added);
        $msg->reset();

        // the related words and triples of a page split a phrase list by the class of the phrase
        $t->subheader($ts . 'filter');
        $test_name = 'the words of the currency list include the word Euro';
        $wrd_names = $t_phr->list_currency_ui()->only_words($msg)->names();
        $t->assert_contains($test_name, $wrd_names, word_names::EURO);
        $test_name = '... but not the triple Swiss franc';
        $wrd_names = $t_phr->list_currency_ui()->only_words($msg)->names();
        $t->assert_contains_not($test_name, $wrd_names, triple_names::SWISS_FRANC);
        $test_name = 'the triples of the currency list include the triple Swiss franc';
        $trp_names = $t_phr->list_currency_ui()->only_triples($msg)->names();
        $t->assert_contains($test_name, $trp_names, triple_names::SWISS_FRANC);
        $test_name = '... but not the word Euro';
        $trp_names = $t_phr->list_currency_ui()->only_triples($msg)->names();
        $t->assert_contains_not($test_name, $trp_names, word_names::EURO);
        $msg->reset();

        /*
         * TODO Prio 3 add a phrase selector if the phrase list is short and add an test
        // test the phrase selector
        $form_name = 'test_phrase_selector';
        $pos = 1;
        $url_arr = [url_var::MASK => views::PHRASE_ID, url_var::ID => $company_id];
        $phr = new phrase($usr);
        $phr->load_by_id($zh_company_id);
        $result = $phr->dsp_selector(Null, $form_name, $pos, '', $url_arr);
        $target = triple_names::COMPANY_ZURICH;
        $t->dsp_contains(', phrase->dsp_selector ' . $result . ' with ' .
            triple_names::COMPANY_ZURICH . ' selected contains ' .
            triple_names::COMPANY_ZURICH, $target, $result, $t::TIMEOUT_LIMIT_PAGE);

        // test the phrase selector for the word company
        $wrd = new word($usr);
        $wrd->load_by_name(word_names::COMPANY, word::class);
        $trp_ins = new triple($usr);
        $trp_ins->load_by_name(triple_names::COMPANY_ZURICH, triple::class);
        $phr = $wrd->phrase();
        $phr_ui = new phrase_dsp($phr->api_json());
        $result = $phr->dsp_selector($phr_ui, $form_name, $pos, '', $url_arr);
        $target = $trp_ins->name();
        $t->dsp_contains(', phrase->dsp_selector of type ' . word_names::COMPANY . ' is : ' .
            $result . ' which contains ' . triple_names::COMPANY_ZURICH,
            $target, $result, $t::TIMEOUT_LIMIT_PAGE_SEMI);
        */

    }

}