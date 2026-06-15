<?php

/*

    test/unit_ui/word.php - testing of the html frontend functions for words
    ---------------------
  

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

use Zukunft\ZukunftCom\main\php\web\component\execute\system_form;
use Zukunft\ZukunftCom\main\php\web\component\execute\ui_list;
use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\html\styles;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\web\types\type_lists;
use Zukunft\ZukunftCom\main\php\web\word\word;
use Zukunft\ZukunftCom\main\php\shared\const\formulas;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\shared\types\phrase_types;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_formulas;
use Zukunft\ZukunftCom\test\php\create\test_phrases;
use Zukunft\ZukunftCom\test\php\create\test_values;
use Zukunft\ZukunftCom\test\php\create\test_views;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class word_ui_tests
{
    function run(test_cleanup $t, type_lists $cfg): void
    {
        global $mtr;

        // init
        $html = new html_base();
        $t_wrd = new test_words($t);
        $t_msk = new test_views($t);
        $t_phr = new test_phrases($t);

        // start the test section (ts)
        $ts = 'unit ui html word ';
        $t->header($ts);

        // TODO add a list of differences between the user word and the standard word
        //      with an undo button to change back to the standard
        // TODO add this ui test for all main sandbox objects

        $wrd = new word($t_wrd->word()->api_json());
        $wrd_pi = new word($t_wrd->word_pi()->api_json());
        $wrd_zh = new word($t_wrd->word_zh()->api_json());
        $wrd_city = new word($t_wrd->word_city()->api_json());
        $wrd_chf = $t_wrd->swiss_franc_ui();
        $test_page = $html->text_h1('Word display test');
        $test_page .= $html->text_h2('names');
        $test_page .= 'with tooltip: ' . $wrd->name_tip() . '<br>';
        $test_page .= 'with link: ' . $wrd->name_link() . '<br>';
        $test_page .= $html->text_h2('buttons');
        $test_page .= 'add button: ' . $wrd->btn_add() . '<br>';
        $test_page .= 'edit button: ' . $wrd->btn_edit() . '<br>';
        $test_page .= 'del button: ' . $wrd->btn_del() . '<br>';
        $test_page .= 'unlink button: ' . $wrd->btn_unlink(1) . '<br>';
        $test_page .= $html->text_h2('select');
        $from_rows = $wrd->dsp_type_selector(views::WORD_EDIT, '', $cfg) . '<br>';
        $from_rows .= $wrd->view_selector(views::WORD_EDIT, $t_msk->view_list_ui()) . '<br>';
        $from_rows .= $wrd->view_selector(views::WORD_EDIT, $t_msk->view_list_long_dsp(), 'view_long') . '<br>';
        $test_page .= $html->form(views::WORD_EDIT, $from_rows);
        $test_page .= $html->text_h2('table');
        $test_page .= $html->tbl($html->tr($wrd->th()) . $wrd_pi->tr());
        $test_page .= 'del in columns: ' . $html->tbl($wrd->dsp_del()) . '<br>';
        $test_page .= 'unlink in columns: ' . $html->tbl($wrd_pi->dsp_unlink($wrd->id)) . '<br>';
        $test_page .= $html->text_h2('view header');
        $test_page .= $wrd->header() . '<br>';
        $test_page .= $html->text_h2('parents of ' . $wrd_zh->name());
        $test_page .= 'all: ' . $wrd_zh->parents()->name_link_list() . '<br>';
        $test_page .= 'filtered by a phrase list: '
            . $wrd_zh->parents($t_phr->list_zh_ui())->name_link_list() . '<br>';
        $test_page .= 'two levels up: '
            . $wrd_zh->parents(null, 2)->name_link_list() . '<br>';
        $test_page .= $html->text_h2('children of ' . $wrd_city->name());
        $test_page .= $wrd_city->children($t_phr->list_zh_ui())->name_link_list() . '<br>';
        $test_page .= $html->text_h2('similar to ' . $wrd_chf->name());
        $test_page .= $wrd_chf->similar($t_phr->list_currency_ui())->name_link_list() . '<br>';
        $test_page .= $t->dsp_title_named_edit($wrd);

        // show the phrases related to a word as on the default word page
        $list = new ui_list();
        $wrd_chf_rel = $t_wrd->swiss_franc_related_ui();
        $test_page .= $html->text_h2('phrases related to ' . $wrd_chf_rel->name());
        $test_page .= 'symbols and aliases: ' . $list->parents_of_word($wrd_chf_rel) . '<br>';
        $test_page .= 'categories: ' . $list->children_of_word($wrd_chf_rel) . '<br>';

        // show the alias and symbol phrases as on the default word page
        $wrd_usd_rel = $t_wrd->us_dollar_related_ui();
        $test_page .= $html->text_h2('aliases and symbols of ' . $wrd_usd_rel->name());
        $test_page .= $list->phrase_aliases($wrd_usd_rel) . '<br>';
        $test_page .= $list->phrase_symbols($wrd_usd_rel) . '<br>';
        $test_page .= 'other related phrases: ' . $list->phrases_related_ex_symbols($wrd_usd_rel) . '<br>';

        // show the related stocks sorted by the market capitalisation as on the default company page
        $wrd_company_rel = $t_wrd->company_related_ui();
        $test_page .= $html->text_h2('stocks related to ' . $wrd_company_rel->name());
        $test_page .= 'stocks by impact: ' . $list->phrases_related_ex_symbols($wrd_company_rel) . '<br>';

        // show the phrase type as read only text e.g. for a word detail view
        $form = new system_form();
        $wrd_measure = new word($t_wrd->hz()->api_json());
        $test_page .= $html->text_h2('phrase type of ' . $wrd_measure->name());
        $test_page .= 'phrase type: ' . $form->show_phrase_type($wrd_measure) . '<br>';

        // show the formulas assigned to a word as on the default word page
        $t_frm = new test_formulas($t);
        $dto = new data_object();
        $dto->frm_lnk_lst = $t_frm->formula_link_list_ui();
        $dto->frm_lst = $t_frm->formula_list_ui();
        $wrd_minute = new word($t_wrd->word_minute()->api_json());
        $test_page .= $html->text_h2('formulas assigned to ' . $wrd_minute->name());
        $test_page .= 'formulas: ' . $list->formulas($wrd_minute, $dto) . '<br>';
        $test_page .= $html->text_h2('formulas assigned to ' . $wrd->name());
        $test_page .= 'formulas: ' . $list->formulas($wrd, $dto) . '<br>';

        // show the values related to a word sorted by impact as on the default word page
        $t_val = new test_values($t);
        $dto->val_lst = $t_val->value_list_zh_impact_ui();
        $test_page .= $html->text_h2('values related to ' . $wrd_zh->name());
        $test_page .= 'values by impact: ' . $list->values_by_word($wrd_zh, $dto) . '<br>';
        $t->html_page_test($test_page, 'word html components', 'word', $t);

        $t->subheader($ts . 'related phrases');
        $test_name = 'the symbol triple of the word is shown';
        $t->assert_text_contains($test_name, $list->parents_of_word($wrd_chf_rel), words::CHF);
        $test_name = 'the category triple of the word is shown';
        $t->assert_text_contains($test_name, $list->children_of_word($wrd_chf_rel), word_names::CURRENCY);
        $test_name = 'without related phrases the section stays empty';
        $t->assert($test_name, $list->parents_of_word($wrd_chf, new phrase_list()), '');

        $t->subheader($ts . 'aliases and symbols');
        $alias_html = $list->phrase_aliases($wrd_usd_rel);
        $test_name = 'two aliases are shown with the plural text';
        $t->assert_text_contains($test_name, $alias_html, $mtr->txt(msg_id::PHRASE_ALIASES));
        $test_name = 'the alias line is not broken across lines';
        $t->assert_text_contains($test_name, $alias_html, styles::TEXT_NOWRAP);
        $test_name = 'the dollar sign is linked as alias';
        $t->assert_text_contains($test_name, $alias_html, word_names::DOLLAR);
        $symbol_html = $list->phrase_symbols($wrd_usd_rel);
        $test_name = 'one symbol is shown with the singular text';
        $t->assert_text_not_contains($test_name, $symbol_html, $mtr->txt(msg_id::PHRASE_SYMBOLS));
        $test_name = 'the currency code is linked as symbol';
        $t->assert_text_contains($test_name, $symbol_html, word_names::USD);
        $ex_html = $list->phrases_related_ex_symbols($wrd_usd_rel);
        $test_name = 'the other related phrases are listed';
        $t->assert_text_contains($test_name, $ex_html, triples::IN_USD);
        $test_name = 'the alias triples are excluded from the related phrases';
        $t->assert_text_not_contains($test_name, $ex_html, triples::DOLLAR_ALIAS);
        $test_name = 'without an alias nothing is shown';
        $t->assert($test_name, $list->phrase_aliases($wrd_chf_rel), '');

        $t->subheader($ts . 'phrase type');
        $test_name = 'the phrase type name is shown';
        $t->assert($test_name, $form->show_phrase_type($wrd_measure), phrase_types::MEASURE_NAME);
        $test_name = 'a word without a type shows an empty text';
        $t->assert($test_name, $form->show_phrase_type($wrd_zh), '');

        $t->subheader($ts . 'assigned formulas');
        $test_name = 'the formula assigned to the word is listed';
        $t->assert_text_contains($test_name, $list->formulas($wrd_minute, $dto), formulas::SCALE_TO_SEC);
        $test_name = 'the sample formula of the default test word is listed';
        $t->assert_text_contains($test_name, $list->formulas($wrd, $dto), formulas::INCREASE);
        $test_name = 'a word without assigned formulas shows an empty list';
        $t->assert($test_name, $list->formulas($wrd_zh, $dto), '');

        $t->subheader($ts . 'related sorted by impact');
        $stock_html = $list->phrases_related_ex_symbols($wrd_company_rel);
        $test_name = 'the stock with the highest market capitalisation is first';
        $t->assert_text_order($test_name, $stock_html, triples::COMPANY_ABB, triples::COMPANY_ZURICH);
        $test_name = 'the stock with the lowest market capitalisation is last';
        $t->assert_text_order($test_name, $stock_html, triples::COMPANY_ZURICH, triples::COMPANY_VESTAS);

        $t->subheader($ts . 'related values sorted by impact');
        $val_html = $list->values_by_word($wrd_zh, $dto);
        $test_name = 'the value of the phrase with the highest impact is shown first';
        $t->assert_text_order($test_name, $val_html, triples::COMPANY_ZURICH, triples::CITY_ZH_NAME);
        $test_name = 'a word without related values shows an empty value list';
        $t->assert($test_name, $list->values_by_word($wrd, $dto), '');

        // a word loaded with its related values carries them through the api to the
        // default word page, so the value list is shown without a separate cache
        $test_name = 'the related values of a word are shown from the word api';
        $wrd_zh_be = $t_wrd->word_zh();
        $wrd_zh_be->values_related = $t_val->value_list_zh_impact();
        $wrd_zh_rel = new word($wrd_zh_be->api_json(
            [api_types::INCL_RELATED, api_types::INCL_PHRASES, api_types::TEST_MODE]));
        $t->assert_text_order($test_name, $list->values_by_word($wrd_zh_rel),
            triples::COMPANY_ZURICH, triples::CITY_ZH_NAME);

        // the similar words of a word are the other words linked to the same parent via the 'is a' verb
        // e.g. "Swiss franc" is a "currency" and the other currencies are "Euro" and "US Dollar" (USD)
        $test_name = 'word->similar for ' . word_names::SWISS_FRANC;
        $similar = $t_wrd->swiss_franc_ui()->similar($t_phr->list_currency_ui());
        $names = $similar->names();
        sort($names);
        $result = implode(',', $names);
        $target = word_names::EURO . ',' . word_names::US_DOLLAR;
        $t->assert($test_name, $result, $target);

    }

}