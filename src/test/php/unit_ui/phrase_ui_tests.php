<?php

/*

    test/unit/html/phrase.php - testing of the phrase display functions
    -------------------------
  

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

use Zukunft\ZukunftCom\main\php\web\component\execute\ui_list;
use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\word\triple as triple_ui;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_formulas;
use Zukunft\ZukunftCom\test\php\create\test_triples;
use Zukunft\ZukunftCom\test\php\create\test_values;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class phrase_ui_tests
{
    function run(test_cleanup $t): void
    {
        $html = new html_base();
        $t_wrd = new test_words($t);
        $t_trp = new test_triples($t);
        $t_val = new test_values($t);
        $t_frm = new test_formulas($t);
        $list = new ui_list();
        $msg = new user_message();

        // start the test section (ts)
        $ts = 'unit ui html phrase ';
        $t->header($ts);

        $wrd = new phrase($t_wrd->word()->phrase()->api_json());
        $trp = new phrase($t_trp->triple_pi()->phrase()->api_json());
        $test_page = $html->text_h2('Phrase display test');
        $test_page .= 'word phrase with tooltip: ' . $wrd->name_tip() . '<br>';
        $test_page .= 'word phrase with link: ' . $wrd->name_link() . '<br>';
        $test_page .= 'triple phrase with tooltip: ' . $trp->name_tip() . '<br>';
        $test_page .= 'triple phrase with link: ' . $trp->name_link() . '<br>';

        // the table view renders the values of a phrase with one column per related phrase;
        // word.html shows the word case, so here the triple case, because the component type
        // is used by one view for both phrase classes
        $dto = new data_object();
        $dto->val_lst = $t_val->value_list_zh_impact_ui();
        $trp_zh_city = new triple_ui($t_trp->zh_city()->api_json());
        $test_page .= $html->text_h2('values of ' . $trp_zh_city->name() . ' as a table');
        $test_page .= 'as table: ' . $list->table_with_related_columns($trp_zh_city, $msg, $dto) . '<br>';
        $t->html_page_test($test_page, 'phrase', 'phrase', $msg);

        $t->subheader($ts . 'table with related columns');
        $tbl_html = $list->table_with_related_columns($trp_zh_city, $msg, $dto);
        $test_name = 'the values of a triple are shown as a table';
        $t->assert_text_contains($test_name, $tbl_html, '<table');
        // the page phrase is the context of every row, so the row is named by the remaining
        // phrase of the value, e.g. "Zurich" for the value of "Zurich" and "Zurich (city)"
        $test_name = 'the other phrase of the value is shown as the row name';
        $t->assert_text_contains($test_name, $tbl_html, '>' . word_names::ZH . '</a>');
        $test_name = 'the phrase of the page is not repeated as a row name';
        $t->assert_text_not_contains($test_name, $tbl_html, triple_names::CITY_ZH);

        // negative: the component type can be assigned to any object by a view, so a class
        // that is not a phrase must say so instead of silently showing nothing
        // this block stays last, because the frontend user_message has no reset() to clear
        // the expected warning for a following test
        $test_name = 'a class that is not a phrase shows no table';
        $frm = $t_frm->formula_increase_ui();
        $t->assert($test_name, $list->table_with_related_columns($frm, $msg, $dto), '');
        // the report is a warning, which informs the user but keeps the message ok,
        // so check for the message id instead of the ok status
        $test_name = 'a class that is not a phrase reports that the table is not implemented';
        $t->assert_true($test_name, $msg->has_msg_id(msg_id::TABLE_COLUMNS_NOT_IMPLEMENTED));
    }

}