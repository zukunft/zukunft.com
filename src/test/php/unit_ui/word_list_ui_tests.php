<?php

/*

    test/unit/html/word_list.php - testing of the word list html frontend functions
    ----------------------------
  

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

use Zukunft\ZukunftCom\main\php\shared\enum\languages;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::WORD . 'word_list.php';

use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\word\word_list as word_list_ui;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class word_list_ui_tests
{
    function run(test_cleanup $t): void
    {
        $html = new html_base();
        $t_wrd = new test_words($t);
        $msg = new user_message();

        $base_url = THIS_URL;
        $lan = languages::DEFAULT;
        $url_arr = [url_var::MASK => views::WORD_ID, url_var::ID => word_names::ZH_ID];

        // start the test section (ts)
        $ts = 'unit ui html word list ';
        $t->header($ts);

        // create the word list test set
        $lst = new word_list_ui($t_wrd->word_list_short()->api_json());
        $lst_long = new word_list_ui($t_wrd->word_list_all_types()->api_json());

        // test the word list display functions
        $test_page = $html->text_h1('Word list display test');
        $test_page .= 'names with links:<br>' . $lst->name_link($msg) . '<br><br>';
        $test_page .= 'table cells<br>';
        $test_page .= $lst->tbl();
        $test_page .= 'all word types: ' . '<br>' . $lst_long->name_link($msg) . '<br><br>';
        $test_page .= 'ex measure and time: ' . '<br>' . $lst_long->ex_measure_and_time_lst($msg)->name_link($msg) . '<br><br>';
        $test_page .= 'measure and scaling: ' . '<br>' . $lst_long->measure_scale_lst($msg)->name_link($msg) . '<br><br>';

        $test_page .= '<br>' . $html->text_h2('Selector tests');
        $from_rows = $lst_long->selector(views::WORD_LIST, 0, 'nothing_selected') . '<br>';
        $from_rows .= $lst_long->selector(views::WORD_LIST, 3, '2_selected') . '<br>';
        $test_page .= $html->form(views::WORD_LIST, $from_rows);

        $t->html_page_test($test_page, '', 'word_list', $msg, $base_url, $lan);

        $t->subheader($ts . 'api mapper');

        // each word of the api json is added to the list
        $test_name = 'the api mapper adds the words of the api json';
        $wrd_lst_map = new word_list_ui();
        $wrd_lst_map->api_mapper(json_decode($t_wrd->word_list_short()->api_json(), true));
        $t->assert($test_name, $wrd_lst_map->count(), $t_wrd->word_list_short()->count());

        // the id is mandatory, so a word of the api json without the id is reported
        $test_name = 'the api mapper reports a word without the id';
        $wrd_lst_map = new word_list_ui();
        $api_json = [[json_fields::NAME => word_names::MATH]];
        $t->assert_false($test_name, $wrd_lst_map->api_mapper($api_json)->is_ok());
    }

}