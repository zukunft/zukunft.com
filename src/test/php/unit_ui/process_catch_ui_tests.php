<?php

/*

    test/php/unit_ui/process_catch_ui_tests.php - testing of the html frontend functions of the process catch pages
    -------------------------------------------


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

    Copyright (c) 1995-2026 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\test\php\unit_ui;

use Zukunft\ZukunftCom\main\php\shared\const\components;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\component\execute\system_page;
use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class process_catch_ui_tests
{
    /**
     * the four process catch pages guide the user of a question phrase from one easy question to
     * the result; every link keeps the question phrase, so the pages need no javascript
     */
    function run(test_cleanup $t): void
    {
        global $mtr;
        global $ui_sys;

        $page = new system_page();
        $t_wrd = new test_words($t);
        $question = $t_wrd->zh_ui();

        // start the test section (ts)
        $ts = 'unit ui html process catch ';
        $t->header($ts);

        $t->subheader($ts . 'page code id');
        $test_name = 'the tabs and buttons get the code id of the page from the view cache';
        $dto = new data_object();
        $dto->typ_lst_cache = $ui_sys->typ_lst_cache;
        $t->assert($test_name, $dto->view_code_id(views::WORD_ID), views::WORD);
        // negative: a component without a page e.g. in a preview gets no code id, so it shows no button
        $test_name = '... and a component without a page gets none';
        $t->assert($test_name, $dto->view_code_id(0), '');

        $t->subheader($ts . 'explain text');
        $test_name = 'the explain text of a process catch page is the translated text';
        $explain = $page->text_explain(msg_id::PROCESS_CATCH_EXPLAIN_DETAIL);
        $t->assert_text_contains($test_name, $explain, $mtr->txt(msg_id::PROCESS_CATCH_EXPLAIN_DETAIL));
        $test_name = '... and a component without a text shows nothing';
        $t->assert($test_name, $page->text_explain(null), '');

        $t->subheader($ts . 'fields');
        $fields_page = 'process catch main input fields';
        $fields_tab = 'process catch detail tab two fields';
        $test_name = 'the fields of a page without tabs are always shown';
        $t->assert_text_contains($test_name, $page->process_catch_fields($fields_page, null, []), $fields_page);
        $test_name = 'the fields of the selected tab are shown';
        $tab_two = [url_var::TAB => 2];
        $t->assert_text_contains($test_name, $page->process_catch_fields(
            $fields_tab, components::PROCESS_CATCH_DETAIL_TAB_TWO_FIELDS, $tab_two), $fields_tab);
        // negative: without a tab in the url the first tab is selected, so the second tab hides its fields
        $test_name = '... but not the fields of a tab that is not selected';
        $t->assert($test_name, $page->process_catch_fields(
            $fields_tab, components::PROCESS_CATCH_DETAIL_TAB_TWO_FIELDS, []), '');
        // an unknown tab e.g. of an edited link selects the first tab, so the page never hides every field
        $test_name = 'an unknown tab shows the fields of the first tab';
        $tab_unknown = [url_var::TAB => count(system_page::PROCESS_CATCH_TABS) + 1];
        $t->assert_text_contains($test_name, $page->process_catch_fields(
            $fields_tab, components::PROCESS_CATCH_DETAIL_TAB_ONE_FIELDS, $tab_unknown), $fields_tab);
        $test_name = '... and not the fields of another tab';
        $t->assert($test_name, $page->process_catch_fields(
            $fields_tab, components::PROCESS_CATCH_DETAIL_TAB_TWO_FIELDS, $tab_unknown), '');

        $t->subheader($ts . 'tabs');
        $test_name = 'a tab that is not selected links to the same page with the tab selected';
        $tab_html = $page->process_catch_tab(components::PROCESS_CATCH_DETAIL_TAB_TWO, views::PROCESS_CATCH_DETAIL, $question, []);
        $t->assert_text_contains($test_name, $tab_html, url_var::MASK . url_var::EQ . views::PROCESS_CATCH_DETAIL);
        $t->assert_text_contains($test_name, $tab_html, url_var::TAB . url_var::EQ . 2);
        $test_name = '... for the same question phrase';
        $t->assert_text_contains($test_name, $tab_html, url_var::ID . url_var::EQ . word_names::ZH_ID);
        // negative: the selected tab is shown as text, so it has no link that selects it again
        $test_name = 'the selected tab is no link';
        $tab_html = $page->process_catch_tab(components::PROCESS_CATCH_DETAIL_TAB_ONE, views::PROCESS_CATCH_DETAIL, $question, []);
        $t->assert_text_contains($test_name, $tab_html, $mtr->txt(msg_id::PROCESS_CATCH_TAB_ONE));
        $t->assert_text_not_contains($test_name, $tab_html, url_var::TAB . url_var::EQ);
        $test_name = 'with an unknown tab in the url the first tab is selected';
        $tab_html = $page->process_catch_tab(components::PROCESS_CATCH_DETAIL_TAB_ONE, views::PROCESS_CATCH_DETAIL, $question, $tab_unknown);
        $t->assert_text_not_contains($test_name, $tab_html, url_var::TAB . url_var::EQ);
        // negative: a component that names no tab e.g. a misconfigured code id shows no tab
        $test_name = 'a component without a tab code id shows no tab';
        $t->assert($test_name, $page->process_catch_tab(null, views::PROCESS_CATCH_DETAIL, $question, []), '');

        $t->subheader($ts . 'buttons');
        $test_name = 'the next button of the start page opens the main page';
        $next_html = $page->process_catch_button_next(views::PROCESS_CATCH_START, $question);
        $t->assert_text_contains($test_name, $next_html, url_var::MASK . url_var::EQ . views::PROCESS_CATCH_MAIN);
        $t->assert_text_contains($test_name, $next_html, $mtr->txt(msg_id::SYSTEM_BUTTON_NEXT));
        $test_name = '... for the same question phrase';
        $t->assert_text_contains($test_name, $next_html, url_var::ID . url_var::EQ . word_names::ZH_ID);
        // negative: the result is the last page, so a next button there would lead nowhere
        $test_name = 'the result page has no next button';
        $t->assert($test_name, $page->process_catch_button_next(views::PROCESS_CATCH_RESULT, $question), '');
        $test_name = 'the back button of the result page opens the detail page';
        $back_html = $page->process_catch_button_back(views::PROCESS_CATCH_RESULT, $question);
        $t->assert_text_contains($test_name, $back_html, url_var::MASK . url_var::EQ . views::PROCESS_CATCH_DETAIL);
        $t->assert_text_contains($test_name, $back_html, $mtr->txt(msg_id::SYSTEM_BUTTON_BACK));
        // negative: the start is the first page, so there is no page to go back to
        $test_name = 'the start page has no back button';
        $t->assert($test_name, $page->process_catch_button_back(views::PROCESS_CATCH_START, $question), '');

        $t->subheader($ts . 'refresh');
        $test_name = 'the refresh button submits the form of the page to show it again';
        $refresh_html = $page->process_catch_button_refresh(msg_id::PROCESS_CATCH_BUTTON_REFRESH,
            views::PROCESS_CATCH_DETAIL, views::PROCESS_CATCH_DETAIL, $question, $tab_two);
        $t->assert_text_contains($test_name, $refresh_html, 'id="' . views::PROCESS_CATCH_DETAIL . '"');
        $t->assert_text_contains($test_name, $refresh_html, $mtr->txt(msg_id::PROCESS_CATCH_BUTTON_REFRESH));
        $test_name = '... for the same page, question phrase and tab';
        $t->assert_text_contains($test_name, $refresh_html, 'name="' . url_var::MASK . '" value="' . views::PROCESS_CATCH_DETAIL . '"');
        $t->assert_text_contains($test_name, $refresh_html, 'name="' . url_var::ID . '" value="' . word_names::ZH_ID . '"');
        $t->assert_text_contains($test_name, $refresh_html, 'name="' . url_var::TAB . '" value="2"');
        // negative: the refresh never changes data, so it posts no submit key that would start a save
        $test_name = '... without a data change submit';
        $t->assert_text_not_contains($test_name, $refresh_html, 'name="' . url_var::POST_SUBMIT . '"');
        // negative: a component without a page e.g. a preview has no page to show again
        $test_name = 'a refresh button without a page shows nothing';
        $t->assert($test_name, $page->process_catch_button_refresh(msg_id::PROCESS_CATCH_BUTTON_REFRESH,
            views::PROCESS_CATCH_DETAIL, '', $question, []), '');

        $t->subheader($ts . 'geolocator');
        $test_name = 'the geolocator shows the translated label';
        $geo_html = $page->geolocator(msg_id::GEOLOCATOR_LABEL, views::PROCESS_CATCH_DETAIL, []);
        $t->assert_text_contains($test_name, $geo_html, $mtr->txt(msg_id::GEOLOCATOR_LABEL));
        $test_name = '... with the place url var as field name of the form of the refresh button';
        $t->assert_text_contains($test_name, $geo_html, 'name="' . url_var::PLACE . '"');
        $t->assert_text_contains($test_name, $geo_html, 'form="' . views::PROCESS_CATCH_DETAIL . '"');
        $test_name = 'the place entered before is shown again after the refresh';
        $place = word_names::ZH;
        $geo_html = $page->geolocator(msg_id::GEOLOCATOR_LABEL, views::PROCESS_CATCH_DETAIL, [url_var::PLACE => $place]);
        $t->assert_text_contains($test_name, $geo_html, 'value="' . $place . '"');
        // negative: without a place in the url the input is empty and does not show an old place
        $test_name = 'a new page shows an empty place';
        $geo_html = $page->geolocator(msg_id::GEOLOCATOR_LABEL, views::PROCESS_CATCH_DETAIL, []);
        $t->assert_text_not_contains($test_name, $geo_html, 'value="');

        // the detail page with the second tab selected as one snapshot of all process catch renderers
        $detail_html = $page->text_explain(msg_id::PROCESS_CATCH_EXPLAIN_DETAIL);
        $detail_html .= $page->geolocator(msg_id::GEOLOCATOR_LABEL, views::PROCESS_CATCH_DETAIL, [url_var::PLACE => $place]);
        $detail_tabs = [
            components::PROCESS_CATCH_DETAIL_TAB_ONE,
            components::PROCESS_CATCH_DETAIL_TAB_TWO,
            components::PROCESS_CATCH_DETAIL_TAB_THREE,
            components::PROCESS_CATCH_DETAIL_TAB_FOUR,
            components::PROCESS_CATCH_DETAIL_TAB_FIVE,
        ];
        foreach ($detail_tabs as $tab_code_id) {
            $detail_html .= $page->process_catch_tab($tab_code_id, views::PROCESS_CATCH_DETAIL, $question, $tab_two);
        }
        $detail_fields = [
            components::PROCESS_CATCH_DETAIL_TAB_ONE_FIELDS,
            components::PROCESS_CATCH_DETAIL_TAB_TWO_FIELDS,
            components::PROCESS_CATCH_DETAIL_TAB_THREE_FIELDS,
            components::PROCESS_CATCH_DETAIL_TAB_FOUR_FIELDS,
            components::PROCESS_CATCH_DETAIL_TAB_FIVE_FIELDS,
        ];
        foreach ($detail_fields as $fields_code_id) {
            $detail_html .= $page->process_catch_fields($fields_tab, $fields_code_id, $tab_two);
        }
        $detail_html .= $page->process_catch_button_refresh(msg_id::PROCESS_CATCH_BUTTON_REFRESH,
            views::PROCESS_CATCH_DETAIL, views::PROCESS_CATCH_DETAIL, $question, $tab_two);
        $detail_html .= $page->process_catch_button_next(views::PROCESS_CATCH_DETAIL, $question);
        $t->html_page_test($detail_html, 'process catch detail', 'process_catch_detail');
    }

}
