<?php

/*

    test/unit/html/term_list.php - testing of the html frontend functions for term lists
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

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::EXECUTE . 'system_page.php';

use Zukunft\ZukunftCom\main\php\web\component\execute\system_page;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\phrase\term_list;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\word\word;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_terms;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class term_list_ui_tests
{
    function run(test_cleanup $t): void
    {
        global $mtr;

        $html = new html_base();
        $lib = new library();
        $t_trm = new test_terms($t);
        $msg = new user_message();

        // start the test section (ts)
        $ts = 'unit ui html term list ';
        $t->header($ts);

        // test the term list display functions
        $form = 'term_list_ui_test';
        $lst = new term_list($t_trm->term_list()->api_json());
        $test_page = $html->text_h2('term list display test');
        $test_page .= 'term list with tooltip: ' . $lst->name_tip() . '<br>';
        $test_page .= 'term list with link: ' . $lst->name_link() . '<br>';

        $from_rows = '<br>' . $html->text_h2('Selector tests');
        $from_rows .= $lst->selector($form, 0, url_var::TERM, msg_id::FORM_SELECT_TERM) . '<br>';
        $test_page .= $html->form($form, $from_rows);

        // test the SYSTEM_BODY_SEARCH component-type renderer (system_page::body_search)
        // which shows the terms matching the search pattern with the highest impact first;
        // the term list is injected so no backend call is needed
        $page = new system_page();
        $wrd_low = new word();
        $wrd_low->set_id(1);
        $wrd_low->set_name('low impact term');
        $wrd_low->impact = 1.0;
        $wrd_high = new word();
        $wrd_high->set_id(2);
        $wrd_high->set_name('high impact term');
        $wrd_high->impact = 9.0;
        $search_lst = new term_list();
        $search_lst->add($wrd_low->term(), $msg);
        $search_lst->add($wrd_high->term(), $msg);
        $test_page .= $html->text_h2('body_search test');
        $test_page .= 'empty pattern shows no terms<br>';
        $test_page .= $page->body_search() . '<br>';
        $test_page .= 'terms matching the pattern with the highest impact first<br>';
        $test_page .= $page->body_search([url_var::PATTERN => 'impact'], $search_lst) . '<br>';
        $test_page .= 'a pattern without a match offers to add the word<br>';
        $test_page .= $page->body_search([url_var::PATTERN => word_names::TEST_ADD], new term_list()) . '<br>';

        // a search that finds nothing says so and offers the pattern as the name of a new word,
        // so that the user can create it in one click via the confirm page of the add workflow
        $test_name = 'a search without a result suggests the pattern as a new word';
        $no_hit_html = $page->body_search([url_var::PATTERN => word_names::TEST_ADD], new term_list());
        $t->assert_text_contains($test_name, $no_hit_html, word_names::TEST_ADD);
        $test_name = 'a search without a result says that nothing has been found';
        $no_hit_msg = $lib->msg_var_replace(
            $mtr->txt(msg_id::INFO_NO_SEARCH_RESULT), msg_id::VAR_PATTERN, word_names::TEST_ADD);
        $t->assert_text_contains($test_name, $no_hit_html, $no_hit_msg);
        $test_name = 'the no result message is shown above the add word button';
        $t->assert_true($test_name,
            strpos($no_hit_html, $no_hit_msg) < strpos($no_hit_html, html_base::BS_BTN_SUCCESS));
        $test_name = 'a search with a result says nothing about a missing result';
        $hit_msg = $lib->msg_var_replace(
            $mtr->txt(msg_id::INFO_NO_SEARCH_RESULT), msg_id::VAR_PATTERN, 'impact');
        $t->assert_text_not_contains($test_name,
            $page->body_search([url_var::PATTERN => 'impact'], $search_lst), $hit_msg);
        $test_name = 'the add word button opens the add word mask';
        $t->assert_text_contains($test_name, $no_hit_html, url_var::MASK . url_var::EQ . views::WORD_ADD_ID);
        $test_name = 'the add word button asks to confirm the new word';
        $t->assert_text_contains($test_name, $no_hit_html,
            url_var::STEP . url_var::EQ . url_var::STEP_CONFIRM);
        $test_name = 'the add word button suggests the pattern as the word name';
        $t->assert_text_contains($test_name, $no_hit_html,
            url_var::NAME . url_var::EQ . urlencode(word_names::TEST_ADD));
        $test_name = 'a search with a result offers no add word button';
        $hit_html = $page->body_search([url_var::PATTERN => 'impact'], $search_lst);
        $t->assert_text_not_contains($test_name, $hit_html, html_base::BS_BTN_SUCCESS);
        // the pattern is user input that the button reflects into the page
        $test_name = 'the add word button escapes a script tag in the pattern';
        $xss_html = $page->body_search([url_var::PATTERN => '<script>alert(1)</script>'], new term_list());
        $t->assert_false($test_name, str_contains($xss_html, '<script>'));

        // the search pattern is user input reflected into the search result title
        // (system_page::search_title), so it must be html-escaped to prevent a reflected xss
        $search_title = $page->system_tile(msg_id::FORM_TITLE_SEARCH, [url_var::PATTERN => 'impact']);
        $test_name = 'the search result title shows the pattern';
        $t->assert_text_contains($test_name, $search_title, 'impact');
        $xss_title = $page->system_tile(msg_id::FORM_TITLE_SEARCH, [url_var::PATTERN => '<script>alert(1)</script>']);
        $test_name = 'the search result title escapes a script tag in the pattern';
        $t->assert_text_contains($test_name, $xss_title, '&lt;script&gt;');
        $test_name = 'the search result title does not contain a raw script tag';
        $t->assert_false($test_name, str_contains($xss_title, '<script>'));

        $t->html_page_test($test_page, 'term_list', 'term_list', $msg);
    }

}