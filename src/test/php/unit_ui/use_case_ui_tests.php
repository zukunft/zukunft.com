<?php

/*

    unit_ui/use_case_ui_tests.php - testing of the components of a use case page e.g. the PV lifetime balance
    -----------------------------


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

use Zukunft\ZukunftCom\main\php\web\component\component_exe as component_ui;
use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\ref\source as source_ui;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\view\view as view_ui;
use Zukunft\ZukunftCom\main\php\web\word\word as word_ui;
use Zukunft\ZukunftCom\main\php\shared\const\components;
use Zukunft\ZukunftCom\main\php\shared\const\sources;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\create\test_components;
use Zukunft\ZukunftCom\test\php\create\test_sources;
use Zukunft\ZukunftCom\test\php\create\test_values;
use Zukunft\ZukunftCom\test\php\create\test_views;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class use_case_ui_tests
{

    /**
     * the components of a use case page e.g. the PV lifetime balance: the view title, the section
     * titles, the notes, the collapsed blocks, the input fields and the sources of the page phrase
     */
    function run(test_cleanup $t): void
    {
        global $ui_sys;
        $html = new html_base();
        $t_cmp = new test_components($t);
        $t_msk = new test_views($t);
        $t_wrd = new test_words($t);
        $t_val = new test_values($t);
        $t_src = new test_sources($t);
        $msg = new user_message();

        // start the test section (ts)
        $ts = 'unit ui html use case ';
        $t->header($ts);

        // the page phrase with its values and the request cache with the view of the page
        $wrd = $t_wrd->zh_ui();
        $msk = new view_ui($t_msk->view()->api_json());
        $dto = new data_object();
        $dto->online = false;
        $dto->typ_lst_cache = $ui_sys->typ_lst_cache;
        $dto->val_lst = $t_val->value_list_zh_impact_ui();
        $dto->msk_lst->add($msk, $msg);

        $title_html = $this->render($t_cmp->component_view_title(), $wrd, $msk, $dto, $msg);
        $sub_title_html = $this->render($t_cmp->component_name_sub_title(), $wrd, $msk, $dto, $msg);
        $note_html = $this->render($t_cmp->component_description_text(), $wrd, $msk, $dto, $msg);
        $start_html = $this->render($t_cmp->component_details_start(), $wrd, $msk, $dto, $msg);
        $end_html = $this->render($t_cmp->component_details_end(), $wrd, $msk, $dto, $msg);
        $fields_html = $this->render($t_cmp->component_value_input_fields(), $wrd, $msk, $dto, $msg);
        $test_page = $html->text_h2('Use case page components');
        $test_page .= 'view title: ' . $title_html . '<br>';
        $test_page .= 'section title: ' . $sub_title_html . '<br>';
        $test_page .= 'note: ' . $note_html . '<br>';
        $test_page .= 'collapsed block: ' . $start_html . $note_html . $end_html . '<br>';
        $test_page .= 'input fields: ' . $fields_html . '<br>';
        $t->html_page_test($test_page, 'use case html components', 'use_case', $msg);

        $t->subheader($ts . 'view title');
        $test_name = 'the view title shows the name of the view of the page';
        $t->assert_text_contains($test_name, $title_html, $msk->name());
        // negative: a component without a page e.g. in a preview has no view to show
        $test_name = '... and nothing for a component without a view';
        $t->assert($test_name, $this->render($t_cmp->component_view_title(), $wrd, new view_ui(), $dto, $msg), '');

        $t->subheader($ts . 'section title and note');
        $test_name = 'the section title is the name of the component as a heading';
        $t->assert_text_contains($test_name, $sub_title_html, '>' . components::TEST_SUB_TITLE_NAME . '<');
        $test_name = 'the note is the description of the component as a paragraph';
        $t->assert_text_contains($test_name, $note_html, '<p>' . components::TEST_ADD_COM . '</p>');

        $t->subheader($ts . 'collapsed block');
        $test_name = 'the block starts with the name of the component as the summary line';
        $t->assert_text_contains($test_name, $start_html, '<summary>' . components::TEST_DETAILS_START_NAME . '</summary>');
        $test_name = '... and the end component closes it';
        $t->assert_text_contains($test_name, $end_html, '</details>');

        $t->subheader($ts . 'input fields');
        $test_name = 'each value of the page phrase is an input field named by its other phrase';
        $t->assert_text_contains($test_name, $fields_html, triple_names::COMPANY_ZURICH);
        $t->assert_text_contains($test_name, $fields_html, triple_names::CITY_ZH_NAME);
        $test_name = '... with the number as the field value';
        $t->assert_text_contains($test_name, $fields_html, 'value="');
        // negative: a page without values e.g. a new word has no input to show
        $test_name = 'a page phrase without values shows no field';
        $dto_empty = new data_object();
        $dto_empty->online = false;
        $dto_empty->typ_lst_cache = $ui_sys->typ_lst_cache;
        $t->assert($test_name, $this->render($t_cmp->component_value_input_fields(), $wrd, $msk, $dto_empty, $msg), '');

        $t->subheader($ts . 'sources');
        // negative first: the values of the test word have no source, so the list is empty
        $test_name = 'values without a source show no source list';
        $t->assert($test_name, $this->render($t_cmp->component_sources_used(), $wrd, $msk, $dto, $msg), '');
        $test_name = 'the source of a value is listed with a link to its url';
        $dto->val_lst->lst()[0]->src = new source_ui($t_src->source_reserved()->api_json());
        $sources_html = $this->render($t_cmp->component_sources_used(), $wrd, $msk, $dto, $msg);
        $t->assert_text_contains($test_name, $sources_html, sources::SIB);
        $t->assert_text_contains($test_name, $sources_html, sources::SIB_URL);
    }

    /**
     * @param \Zukunft\ZukunftCom\main\php\cfg\component\component $cmp the backend component fixture
     * @param word_ui $wrd the phrase of the page
     * @param view_ui $msk the view of the page
     * @param data_object $dto the request cache of the page
     * @param user_message $msg to collect the messages of the render
     * @return string the html code of the component as the page would show it
     */
    private function render($cmp, word_ui $wrd, view_ui $msk, data_object $dto, user_message $msg): string
    {
        $cmp_ui = new component_ui($cmp->api_json());
        return $cmp_ui->dsp_entries($wrd, $msg, '', $msk->id(), $dto, null, '', true);
    }

}
