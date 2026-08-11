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

use Zukunft\ZukunftCom\main\php\web\const\icons;
use Zukunft\ZukunftCom\main\php\web\component\execute\system_form;
use Zukunft\ZukunftCom\main\php\web\component\execute\ui_list;
use Zukunft\ZukunftCom\main\php\web\component\execute\ui_preview;
use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\html\styles;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\web\types\type_lists;
use Zukunft\ZukunftCom\main\php\web\word\word;
use Zukunft\ZukunftCom\main\php\web\user\user as user_ui;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\shared\const\fields\fields;
use Zukunft\ZukunftCom\main\php\shared\const\fields\word_fields;
use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\shared\types\phrase_types;
use Zukunft\ZukunftCom\test\php\const\formula_names;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_formulas;
use Zukunft\ZukunftCom\test\php\create\test_phrases;
use Zukunft\ZukunftCom\test\php\create\test_users;
use Zukunft\ZukunftCom\test\php\create\test_values;
use Zukunft\ZukunftCom\test\php\create\test_views;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class word_ui_tests
{
    function run(test_cleanup $t, type_lists $cfg): void
    {
        global $mtr;
        $msg = new user_message();

        // init
        $html = new html_base();
        $t_wrd = new test_words($t);
        $t_msk = new test_views($t);
        $t_phr = new test_phrases($t);

        // start the test section (ts)
        $ts = 'unit ui html word ';
        $t->header($ts);

        // a database change is only executed for a known requesting user on the message
        // (docs/llm/state-and-messages.md); the positive twins that save with a message user
        // are the rename and delete tests in unit_write_workflow/*_write_url_tests.php
        $t->subheader($ts . 'crud guard');
        $test_name = 'add without a message user reports the missing user';
        $no_usr_msg = new user_message();
        $wrd_crud = new word($t_wrd->word()->api_json());
        $result = $wrd_crud->add_via_api($no_usr_msg);
        $t->assert_true($test_name, $result->has_msg_id(msg_id::USER_MISSING));
        $test_name = 'update without a message user reports the missing user';
        $result = $wrd_crud->update($no_usr_msg);
        $t->assert_true($test_name, $result->has_msg_id(msg_id::USER_MISSING));
        $test_name = 'del without a message user reports the missing user';
        $result = $wrd_crud->del($no_usr_msg);
        $t->assert_true($test_name, $result->has_msg_id(msg_id::USER_MISSING));

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
        $from_rows = $wrd->dsp_type_selector(views::WORD_EDIT, $msg, '', $cfg) . '<br>';
        $from_rows .= $wrd->view_selector(views::WORD_EDIT, $t_msk->view_list_ui(), $msg) . '<br>';
        $from_rows .= $wrd->view_selector(views::WORD_EDIT, $t_msk->view_list_long_dsp(), $msg, 'view_long') . '<br>';
        $test_page .= $html->form(views::WORD_EDIT, $from_rows);
        $test_page .= $html->text_h2('table');
        $test_page .= $html->tbl($html->tr($wrd->th()) . $wrd_pi->tr());
        $test_page .= 'del in columns: ' . $html->tbl($wrd->dsp_del()) . '<br>';
        $test_page .= 'unlink in columns: ' . $html->tbl($wrd_pi->dsp_unlink($wrd->id)) . '<br>';
        $test_page .= $html->text_h2('view header');
        $test_page .= $wrd->header($msg) . '<br>';
        $test_page .= $html->text_h2('parents of ' . $wrd_zh->name());
        $test_page .= 'all: ' . $wrd_zh->parents($msg)->name_link_list() . '<br>';
        $test_page .= 'filtered by a phrase list: '
            . $wrd_zh->parents($msg, $t_phr->list_zh_ui())->name_link_list() . '<br>';
        $test_page .= 'two levels up: '
            . $wrd_zh->parents($msg, null, 2)->name_link_list() . '<br>';
        $test_page .= $html->text_h2('children of ' . $wrd_city->name());
        $test_page .= $wrd_city->children($msg, $t_phr->list_zh_ui())->name_link_list() . '<br>';
        $test_page .= $html->text_h2('similar to ' . $wrd_chf->name());
        $test_page .= $wrd_chf->similar($t_phr->list_currency_ui())->name_link_list() . '<br>';
        $test_page .= $t->dsp_title_named_edit($wrd, $msg);

        // show the phrases related to a word as on the default word page
        $list = new ui_list();
        $wrd_chf_rel = $t_wrd->swiss_franc_related_ui();
        $test_page .= $html->text_h2('phrases related to ' . $wrd_chf_rel->name());
        $test_page .= 'symbols and aliases: ' . $list->parents_of_word($wrd_chf_rel) . '<br>';
        $test_page .= 'children without categories: ' . $list->children_of_word($wrd_chf_rel) . '<br>';

        // the children of a word are its subclasses; with several children the component starts
        // with a header of the word plural and the verb plural, e.g. "currencies are", followed
        // by the child phrases "Euro", "Swiss franc" and "US Dollar"
        $wrd_currency_rel = $t_wrd->currency_related_ui();
        $test_page .= $html->text_h2('children of ' . $wrd_currency_rel->name());
        $test_page .= 'children: ' . $list->children_of_word($wrd_currency_rel) . '<br>';

        // with a single child the component reads as the full statement "Euro is a currency"
        $wrd_currency_single = $t_wrd->single_currency_related_ui();
        $test_page .= $html->text_h2('single child of ' . $wrd_currency_single->name());
        $test_page .= 'child: ' . $list->children_of_word($wrd_currency_single) . '<br>';

        // show the alias and symbol phrases as on the default word page
        $wrd_eur_rel = $t_wrd->euro_related_ui();
        $test_page .= $html->text_h2('aliases and symbols of ' . $wrd_eur_rel->name());
        $test_page .= $list->phrase_aliases($wrd_eur_rel) . '<br>';
        $test_page .= $list->phrase_symbols($wrd_eur_rel) . '<br>';
        $test_page .= 'other related phrases: ' . $list->phrases_related_ex_symbols($wrd_eur_rel) . '<br>';
        // the "related phrases without subtitles" component groups the related phrases by verb
        $test_page .= 'related phrases without subtitles: ' . $list->phrases_related_ex_subtitle($wrd_eur_rel) . '<br>';

        // show the related stocks sorted by the market capitalisation as on the default company page
        $wrd_company_rel = $t_wrd->company_related_ui();
        $test_page .= $html->text_h2('stocks related to ' . $wrd_company_rel->name());
        $test_page .= 'stocks by impact: ' . $list->phrases_related_ex_symbols($wrd_company_rel) . '<br>';

        // show the phrase type as read only text e.g. for a word detail view
        $form = new system_form();
        $wrd_measure = new word($t_wrd->hz()->api_json());
        $test_page .= $html->text_h2('phrase type of ' . $wrd_measure->name());
        $test_page .= 'phrase type: ' . $form->show_phrase_type($wrd_measure, $msg) . '<br>';

        // show the formulas assigned to a word as on the default word page
        $t_frm = new test_formulas($t);
        $dto = new data_object();
        $dto->frm_lnk_lst = $t_frm->formula_link_list_ui();
        $dto->frm_lst = $t_frm->formula_list_ui();
        $wrd_minute = new word($t_wrd->word_minute()->api_json());
        $test_page .= $html->text_h2('formulas assigned to ' . $wrd_minute->name());
        $test_page .= 'formulas: ' . $list->formulas($wrd_minute, $msg, $dto) . '<br>';
        $test_page .= $html->text_h2('formulas assigned to ' . $wrd->name());
        $test_page .= 'formulas: ' . $list->formulas($wrd, $msg, $dto) . '<br>';

        // show the values related to a word sorted by impact as on the default word page
        $t_val = new test_values($t);
        $dto->val_lst = $t_val->value_list_zh_impact_ui();
        $test_page .= $html->text_h2('values related to ' . $wrd_zh->name());
        $test_page .= 'values by impact: ' . $list->values_by_word($wrd_zh, $msg, $dto) . '<br>';
        $test_page .= 'most relevant: ' . $list->values_most_relevant($wrd_zh, $msg, $dto) . '<br>';
        $t->html_page_test($test_page, 'word html components', 'word', $msg);

        $t->subheader($ts . 'related phrases');
        $test_name = 'the symbol triple of the word is shown';
        $t->assert_text_contains($test_name, $list->parents_of_word($wrd_chf_rel), words::CHF);
        $test_name = 'a word category is not shown among its children, which are its subclasses';
        $t->assert_text_not_contains($test_name, $list->children_of_word($wrd_chf_rel), word_names::CURRENCY);
        // the children of a word are its subclasses (the phrases that "are a" the word)
        $currency_children = $list->children_of_word($wrd_currency_rel);
        $test_name = 'the subclasses of currency include Euro';
        $t->assert_text_contains($test_name, $currency_children, word_names::EURO);
        $test_name = 'the subclasses of currency include Swiss franc';
        $t->assert_text_contains($test_name, $currency_children, word_names::SWISS_FRANC);
        $test_name = 'with several children the header uses the word plural "currencies"';
        $t->assert_text_contains($test_name, $currency_children, word_names::CURRENCIES);
        // a single child is shown as the full statement, e.g. "Euro is a currency"
        $currency_single = $list->children_of_word($wrd_currency_single);
        $test_name = 'a single child statement names the child Euro';
        $t->assert_text_contains($test_name, $currency_single, word_names::EURO);
        $test_name = 'a single child statement names the parent currency';
        $t->assert_text_contains($test_name, $currency_single, word_names::CURRENCY);
        $test_name = 'without related phrases the section stays empty';
        $t->assert($test_name, $list->parents_of_word($wrd_chf, new phrase_list()), '');

        $t->subheader($ts . 'aliases and symbols');
        $alias_html = $list->phrase_aliases($wrd_eur_rel);
        $test_name = 'one alias is shown with the singular text';
        $t->assert_text_contains($test_name, $alias_html, $mtr->txt(msg_id::PHRASE_ALIAS));
        $test_name = 'the alias line is not broken across lines';
        $t->assert_text_contains($test_name, $alias_html, styles::TEXT_NOWRAP);
        $test_name = 'the euro sign is linked as alias';
        $t->assert_text_contains($test_name, $alias_html, word_names::EURO_SIGN);
        $symbol_html = $list->phrase_symbols($wrd_eur_rel);
        $test_name = 'one symbol is shown with the singular text';
        $t->assert_text_not_contains($test_name, $symbol_html, $mtr->txt(msg_id::PHRASE_SYMBOLS));
        $test_name = 'the currency code is linked as symbol';
        $t->assert_text_contains($test_name, $symbol_html, word_names::EUR);
        $ex_html = $list->phrases_related_ex_symbols($wrd_eur_rel);
        $test_name = 'the other related phrases are listed';
        $t->assert_text_contains($test_name, $ex_html, triple_names::IN_EUR);
        $test_name = 'the alias triples are excluded from the related phrases';
        $t->assert_text_not_contains($test_name, $ex_html, triple_names::EURO_SIGN_ALIAS);
        // the "related phrases without subtitles" component groups the related triples by verb,
        // showing the verb (linked to its page) followed by the linked phrases instead of the
        // full triple name (e.g. the "in" group with "EUR" instead of "in EUR")
        $sub_html = $list->phrases_related_ex_subtitle($wrd_eur_rel);
        $test_name = 'the linked phrase is shown in its verb group';
        $t->assert_text_contains($test_name, $sub_html, word_names::EUR);
        $test_name = 'the full triple name is replaced by the verb group';
        $t->assert_text_not_contains($test_name, $sub_html, triple_names::IN_EUR);
        $test_name = 'without an alias nothing is shown';
        $t->assert($test_name, $list->phrase_aliases($wrd_chf_rel), '');

        $t->subheader($ts . 'phrase type');
        $test_name = 'the phrase type name is shown';
        $t->assert($test_name, $form->show_phrase_type($wrd_measure, $msg), phrase_types::MEASURE_NAME);
        $test_name = 'a word without a type shows an empty text';
        $t->assert($test_name, $form->show_phrase_type($wrd_zh, $msg), '');

        $t->subheader($ts . 'assigned formulas');
        $test_name = 'the formula assigned to the word is listed';
        $t->assert_text_contains($test_name, $list->formulas($wrd_minute, $msg, $dto), formula_names::SCALE_TO_SEC);
        $test_name = 'the sample formula of the default test word is listed';
        $t->assert_text_contains($test_name, $list->formulas($wrd, $msg, $dto), formula_names::INCREASE);
        $test_name = 'a word without assigned formulas shows an empty list';
        $t->assert($test_name, $list->formulas($wrd_zh, $msg, $dto), '');

        $t->subheader($ts . 'related sorted by impact');
        $stock_html = $list->phrases_related($wrd_company_rel);
        $test_name = 'the stock with the highest market capitalisation is first';
        $t->assert_text_order($test_name, $stock_html, triple_names::COMPANY_ABB, triple_names::COMPANY_ZURICH);
        $test_name = 'the stock with the lowest market capitalisation is last';
        $t->assert_text_order($test_name, $stock_html, triple_names::COMPANY_ZURICH, triple_names::COMPANY_VESTAS);

        $t->subheader($ts . 'related values sorted by impact');
        $val_html = $list->values_by_word($wrd_zh, $msg, $dto);
        $test_name = 'the value of the phrase with the highest impact is shown first';
        $t->assert_text_order($test_name, $val_html, triple_names::COMPANY_ZURICH, triple_names::CITY_ZH_NAME);
        $test_name = 'a word without related values shows an empty value list';
        $t->assert($test_name, $list->values_by_word($wrd, $msg, $dto), '');

        // a word loaded with its related values carries them through the api to the
        // default word page, so the value list is shown without a separate cache
        $test_name = 'the related values of a word are shown from the word api';
        $wrd_zh_be = $t_wrd->word_zh();
        $wrd_zh_be->values_related = $t_val->value_list_zh_impact();
        $wrd_zh_rel = new word($wrd_zh_be->api_json(
            [api_types::INCL_RELATED, api_types::INCL_PHRASES, api_types::TEST_MODE]));
        $t->assert_text_order($test_name, $list->values_by_word($wrd_zh_rel, $msg),
            triple_names::COMPANY_ZURICH, triple_names::CITY_ZH_NAME);

        // the similar words of a word are the other words linked to the same parent via the 'is a' verb
        // e.g. "Swiss franc" is a "currency" and the other currencies are "Euro" and "US Dollar" (USD)
        $test_name = 'word->similar for ' . word_names::SWISS_FRANC;
        $similar = $t_wrd->swiss_franc_ui()->similar($t_phr->list_currency_ui());
        $names = $similar->names();
        sort($names);
        $result = implode(',', $names);
        $target = word_names::EURO . ',' . word_names::US_DOLLAR;
        $t->assert($test_name, $result, $target);

        // the entered data is checked before the confirm view is shown: a word with a name can be
        // confirmed, but an empty name reports an orange warning that the user must fix first
        $test_name = 'word->input_valid for a word with a name';
        $t->assert_true($test_name, $wrd->input_valid($msg));

        $test_name = 'word->input_valid for a word with an empty name';
        $wrd_empty = new word($t_wrd->word()->api_json());
        $wrd_empty->set_name('');
        $msg = new user_message();
        $t->assert_false($test_name, $wrd_empty->input_valid($msg));

        $test_name = 'word->input_valid reports the empty name';
        $t->assert_true($test_name, $msg->has_msg_id(msg_id::NAME_EMPTY));

        $test_name = 'word->input_valid allows an empty name when the word is deleted';
        $t->assert_true($test_name, $wrd_empty->input_valid($msg, url_var::CRUD_DELETE));

        $test_name = 'a used word cannot be deleted';
        $wrd_empty->load_by_id_with_related($wrd_empty->id(), $msg);
        $t->assert_false($test_name, $wrd_empty->input_valid($msg, url_var::CRUD_DELETE));

        $test_name = 'word->input_valid allows an empty name when the word is excluded';
        $wrd_excluded = new word($t_wrd->word()->api_json());
        $wrd_excluded->set_name('');
        $wrd_excluded->excluded = true;
        $t->assert_true($test_name, $wrd_excluded->input_valid($msg));

        // the phrase type may only be changed by a user that is allowed to set the type: a permitted
        // user can confirm the change, a not permitted user (e.g. ip only) gets an orange warning
        $t_usr = new test_users($t);
        $type_changed = [
            url_var::PHRASE_TYPE => '2',
            url_var::PRE . url_var::PHRASE_TYPE => '1'
        ];

        $test_name = 'word->input_valid allows a phrase type change for a permitted user';
        $usr_ok = new user_message(new user_ui($t_usr->user_sys_test()->api_json()));
        $t->assert_true($test_name, $wrd->input_valid($usr_ok, '', $type_changed));

        $test_name = 'word->input_valid blocks a phrase type change for a not permitted user';
        $usr_no = new user_message(new user_ui($t_usr->user_ip()->api_json()));
        $t->assert_false($test_name, $wrd->input_valid($usr_no, '', $type_changed));

        $test_name = 'word->input_valid reports the missing phrase type permission';
        $t->assert_true($test_name, $usr_no->has_msg_id(msg_id::TYPE_CHANGE_NOT_ALLOWED));

        $test_name = 'word->input_valid allows an unchanged phrase type for a not permitted user';
        $type_same = [
            url_var::PHRASE_TYPE => '1',
            url_var::PRE . url_var::PHRASE_TYPE => '1'
        ];
        $usr_no_2 = new user_message(new user_ui($t_usr->user_ip()->api_json()));
        $t->assert_true($test_name, $wrd->input_valid($usr_no_2, '', $type_same));


        $t->subheader($ts . 'confirm change preview');

        // the confirm change preview shows the changes of the admin-only fields (the cached
        // impact and usage numbers, see fields::LOG_ADMIN_ONLY) only to users with admin,
        // developer or system rights, like the change log (change_log_list::filter_admin_fields)
        global $ui_sys;
        $preview = new ui_preview();
        $wrd_chg = new word($t_wrd->word()->api_json());
        $chg_url = [
            url_var::DESCRIPTION => word_names::TEST_CHANGE_COM,
            url_var::PRE . url_var::DESCRIPTION => '',
            url_var::IMPACT => '5',
            url_var::PRE . url_var::IMPACT => ''
        ];
        $impact_lbl = $mtr->text_db_field(fields::FLD_IMPACT);
        // remember the session user so the changed global can be restored after the checks
        $usr_keep = $ui_sys->usr ?? null;

        $test_name = 'an admin sees the impact change in the confirm preview';
        $ui_sys->usr = new user_ui($t->usr_admin->api_json());
        $t->assert_text_contains($test_name, $preview->popup_changes($msg, $chg_url, $wrd_chg), $impact_lbl);

        $test_name = 'a developer sees the impact change in the confirm preview';
        $ui_sys->usr = new user_ui($t->usr_dev->api_json());
        $t->assert_text_contains($test_name, $preview->popup_changes($msg, $chg_url, $wrd_chg), $impact_lbl);

        $test_name = 'a normal user does not see the impact change in the confirm preview';
        $ui_sys->usr = new user_ui($t->usr_normal->api_json());
        $chg_html = $preview->popup_changes($msg, $chg_url, $wrd_chg);
        $t->assert_text_not_contains($test_name, $chg_html, $impact_lbl);

        $test_name = '... but still sees the description change';
        $t->assert_text_contains($test_name, $chg_html, word_names::TEST_CHANGE_COM);

        $test_name = '... and the impact value is still carried forward as a hidden input';
        $t->assert_text_contains($test_name, $chg_html, 'name="' . url_var::IMPACT . '"');

        // restore the session user for the following tests
        if ($usr_keep == null) {
            unset($ui_sys->usr);
        } else {
            $ui_sys->usr = $usr_keep;
        }

        // below the changes the impact of the pending change is shown in the impact unit
        // ('happy time points' unless another unit is set); the impact number cannot be
        // calculated yet, so 'unknown' is shown with an update link to retry the calculation
        $test_name = 'the impact line shows the unit and the unknown impact with an update link';
        $impact_html = $preview->popup_impact($chg_url);
        $t->assert_text_contains($test_name, $impact_html,
            $mtr->txt(msg_id::POPUP_IMPACT) . ' ' . $mtr->txt(msg_id::POPUP_IMPACT_UNIT_FALLBACK));
        $t->assert_text_contains($test_name, $impact_html, $mtr->txt(msg_id::POPUP_IMPACT_UNKNOWN));
        $t->assert_text_contains($test_name, $impact_html, '>' . $mtr->txt(msg_id::POPUP_IMPACT_UPDATE) . '</a>');

        $test_name = 'without a change no impact line is shown';
        $t->assert($test_name, $preview->popup_impact([]), '');


        $t->subheader($ts . 'url mapper');

        // an id-identified url carries only the changed fields (e.g. the my tab undo link), so
        // the mapper must keep the already known name - a partial url must never overwrite
        // fields it does not carry - and only clear it for a url without an id
        $test_name = 'a partial url with an id keeps the object name';
        $wrd_map = new word($t_wrd->word()->api_json());
        $map_msg = new user_message();
        $wrd_map->url_mapper([
            url_var::ID => (string)$wrd_map->id(),
            url_var::DESCRIPTION => 'partial url description'
        ], $map_msg);
        $t->assert($test_name, $wrd_map->name(), word_names::MATH);

        $test_name = '... and takes the description of the partial url';
        $t->assert($test_name, $wrd_map->get_description() ?? '', 'partial url description');

        $test_name = 'a url without an id and without a name clears the name';
        $wrd_map->url_mapper([url_var::DESCRIPTION => 'partial url description'], $map_msg);
        $t->assert($test_name, $wrd_map->name(), '');


        $t->subheader($ts . 'view tab box');

        // the 'my' tab shows the fields the session user has overwritten in user_words as a
        // your / field / instead table (the user value, the translated field name and the
        // standard value) and is only shown if the user is logged in and the api has
        // delivered overwrites for the requesting user
        $wrd_json = json_decode($t_wrd->word()->api_json(), true);
        $wrd_json[json_fields::USER_OVERWRITES] = [
            [
                json_fields::FIELD => word_fields::FLD_PLURAL,
                json_fields::USR_VALUE => word_names::TEST_ADD_PLURAL,
                json_fields::STD_VALUE => word_names::MATH_PLURAL,
            ],
            [
                json_fields::FIELD => fields::FLD_IMPACT,
                json_fields::USR_VALUE => '5',
                json_fields::STD_VALUE => '3',
            ],
            // a null and a zero view id both resolve to 'not set', so this row must be skipped
            [
                json_fields::FIELD => fields::FLD_VIEW,
                json_fields::USR_VALUE => '0',
                json_fields::STD_VALUE => '',
            ],
        ];
        $wrd_json[json_fields::OTHER_OVERWRITES] = [
            [
                json_fields::FIELD => word_fields::FLD_PLURAL,
                json_fields::USER_NAME => users::SYSTEM_TEST_PARTNER_NAME,
                json_fields::USR_VALUE => word_names::TEST_ADD_PLURAL . '2',
                json_fields::STD_VALUE => word_names::MATH_PLURAL,
            ],
        ];
        $wrd_tab = new word(json_encode($wrd_json));
        $my_tab_ref = 'href="#' . strtolower($mtr->txt(msg_id::FORM_SUB_TITLE_MY)) . '"';
        $usr_tab_keep = $ui_sys->usr ?? null;

        $test_name = 'the user with overwrites sees the my tab';
        $ui_sys->usr = new user_ui($t->usr_normal->api_json());
        // the current page url with another entry that the undo link must keep and a stale
        // value of the field to undo that the undo link must replace by the standard value
        $tab_url = [
            url_var::DESCRIPTION => 'undo context',
            url_var::PLURAL => 'stale plural',
        ];
        $tab_html = $list->view_tab_box($wrd_tab, $msg, true, $tab_url);
        $t->assert_text_contains($test_name, $tab_html, $my_tab_ref);

        $test_name = '... with the your and instead columns';
        $t->assert_text_contains($test_name, $tab_html, $mtr->txt(msg_id::MY_TBL_YOUR));
        $t->assert_text_contains($test_name, $tab_html, $mtr->txt(msg_id::MY_TBL_INSTEAD));

        $test_name = '... the user value and the standard value of the overwritten field';
        $t->assert_text_contains($test_name, $tab_html, word_names::TEST_ADD_PLURAL);
        $t->assert_text_contains($test_name, $tab_html, word_names::MATH_PLURAL);

        // the undo icon links to the confirm page of the word edit view that sets the plural
        // back to the standard value, with the user value as the '8'-prefixed opening value;
        // the values are url-encoded in the link (http_build_query), so e.g. a space is a '+'
        $test_name = '... and an undo link to the confirm page for the overwritten field';
        $t->assert_text_contains($test_name, $tab_html, icons::UNDO);
        $t->assert_text_contains($test_name, $tab_html, url_var::PLURAL . '=' . urlencode(word_names::MATH_PLURAL));
        $t->assert_text_contains($test_name, $tab_html, url_var::PRE . url_var::PLURAL . '=' . urlencode(word_names::TEST_ADD_PLURAL));
        $t->assert_text_contains($test_name, $tab_html, url_var::STEP . '=' . url_var::STEP_CONFIRM);

        $test_name = '... the undo link keeps the other entries of the current url';
        $t->assert_text_contains($test_name, $tab_html, url_var::DESCRIPTION . '=' . urlencode('undo context'));

        $test_name = '... but never the current url entry of the field to undo';
        $t->assert_text_not_contains($test_name, $tab_html, urlencode('stale plural'));

        $test_name = '... but without the admin-only impact overwrite for a normal user';
        $t->assert_text_not_contains($test_name, $tab_html, $mtr->text_db_field(fields::FLD_IMPACT));

        $test_name = '... and without the row where both values resolve to the same text';
        $t->assert_text_not_contains($test_name, $tab_html, $mtr->text_db_field(fields::FLD_VIEW));

        // the 'others' tab lists the shared overwrites of the other users with the
        // overwriting user name and the value of that user
        $test_name = 'the others tab shows the overwrite of the other user';
        $others_tab_ref = 'href="#' . strtolower($mtr->txt(msg_id::FORM_SUB_TITLE_OTHERS)) . '"';
        $t->assert_text_contains($test_name, $tab_html, $others_tab_ref);
        $t->assert_text_contains($test_name, $tab_html, users::SYSTEM_TEST_PARTNER_NAME);
        $t->assert_text_contains($test_name, $tab_html, word_names::TEST_ADD_PLURAL . '2');

        // the apply icon links to the confirm page that sets the plural to the other user's value
        $test_name = '... and an apply link to the confirm page for the other user value';
        $t->assert_text_contains($test_name, $tab_html, icons::APPLY);
        $t->assert_text_contains($test_name, $tab_html,
            url_var::PLURAL . '=' . urlencode(word_names::TEST_ADD_PLURAL . '2'));

        $test_name = 'an admin also sees the admin-only impact overwrite';
        $ui_sys->usr = new user_ui($t->usr_admin->api_json());
        $t->assert_text_contains($test_name, $list->view_tab_box($wrd_tab, $msg, true), $mtr->text_db_field(fields::FLD_IMPACT));

        $test_name = 'without overwrites no my and no others tab is shown';
        $ui_sys->usr = new user_ui($t->usr_normal->api_json());
        $wrd_plain = new word($t_wrd->word()->api_json());
        $plain_html = $list->view_tab_box($wrd_plain, $msg, true);
        $t->assert_text_not_contains($test_name, $plain_html, $my_tab_ref);
        $t->assert_text_not_contains($test_name, $plain_html, $others_tab_ref);

        $test_name = 'without a logged in user no my and no others tab is shown';
        unset($ui_sys->usr);
        $anon_html = $list->view_tab_box($wrd_tab, $msg, true);
        $t->assert_text_not_contains($test_name, $anon_html, $my_tab_ref);
        $t->assert_text_not_contains($test_name, $anon_html, $others_tab_ref);

        // restore the session user for the following tests
        if ($usr_tab_keep == null) {
            unset($ui_sys->usr);
        } else {
            $ui_sys->usr = $usr_tab_keep;
        }

    }

}