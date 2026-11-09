<?php

/*

    test/unit/html/reference.php - testing of the html frontend functions for references
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

use Zukunft\ZukunftCom\main\php\shared\const\refs;
use Zukunft\ZukunftCom\main\php\shared\const\sources;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\types\ref_types;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\component\execute\system_form;
use Zukunft\ZukunftCom\main\php\web\component\execute\ui_list;
use Zukunft\ZukunftCom\main\php\web\component\execute\ui_preview;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase as phrase_ui;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\html\styles;
use Zukunft\ZukunftCom\main\php\web\ref\ref;
use Zukunft\ZukunftCom\main\php\web\ref\ref_list as ref_list_ui;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\word\word as word_ui;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_refs;
use Zukunft\ZukunftCom\test\php\create\test_sources;
use Zukunft\ZukunftCom\test\php\create\test_triples;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class reference_ui_tests
{

    // the description that the backend has taken from the wikipedia page of mathematics
    const string MATH_WIKIPEDIA_COM = 'Mathematics is a field of study that discovers and organizes methods, theories and theorems.';
    // the description that the backend has taken from the wikipedia page of geneva
    const string GENEVA_WIKIPEDIA_COM = 'Geneva is the second-most populous city in Switzerland.';

    function run(test_cleanup $t): void
    {
        global $mtr;

        $html = new html_base();
        $t_ref = new test_refs($t);
        $msg = new user_message();

        // start the test section (ts)
        $ts = 'unit ui html reference ';
        $t->header($ts);

        $ref = new ref($t_ref->reference()->api_json());
        $test_page = $html->text_h2('reference display test');
        $test_page .= 'with tooltip: ' . $ref->name_tip() . '<br>';
        $test_page .= 'with link: ' . $ref->name_link() . '<br>';
        $test_page .= $t->dsp_title_named_edit($ref, $msg);
        // the filled reference has a non default share and protection, so its title shows all
        // three parts of the subtitle, which the reference of the page above does not
        $ref_filled = new ref($t_ref->ref_filled()->api_json());
        $test_page .= $t->dsp_title_named_edit($ref_filled, $msg);
        $t->html_page_test($test_page, 'reference', 'reference', $msg);

        $t->subheader($ts . 'title');

        // the reference default view shows the reference name as the page title and the predicate,
        // the share and the protection in the subtitle like the source default view (see
        // base_views.json ref_default), so the api message must carry these three fields
        $test_name = 'the predicate of a reference is sent to the frontend';
        $t->assert_true($test_name, $ref_filled->type_id($msg) > 0);
        $test_name = 'the share type of a reference is sent to the frontend';
        $t->assert_true($test_name, $ref_filled->share_id() > 0);
        $test_name = 'the protection type of a reference is sent to the frontend';
        $t->assert_true($test_name, $ref_filled->protection_id() > 0);

        // the url of a reference carries the fields the edit form shows, e.g. for the change workflow
        $test_name = 'the url of a filled reference carries the phrase and the external key';
        $url_arr = $ref_filled->to_url_array($msg);
        $t->assert($test_name, $url_arr[url_var::EXTERNAL_KEY], $ref_filled->external_key());
        $t->assert($test_name, $url_arr[url_var::PHRASE], $ref_filled->phrase()->id());
        $test_name = 'the url of an empty reference carries no external key';
        $ref_empty = new ref();
        $url_arr = $ref_empty->to_url_array($msg);
        $t->assert_key_not_exists($test_name, url_var::EXTERNAL_KEY, $url_arr);

        $sfm = new system_form();
        $ttl_html = $sfm->title_named($ref_filled, $msg);
        $test_name = 'the reference title names the reference';
        $t->assert_text_contains($test_name, $ttl_html, $ref_filled->name());
        $test_name = 'the reference title links to the reference edit view';
        $t->assert_text_contains($test_name, $ttl_html, url_var::MASK . '=' . views::REF_EDIT_ID);
        $test_name = 'the reference title has a subtitle for the predicate, share and protection';
        $t->assert_text_contains($test_name, $ttl_html, styles::SUBTITLE);

        // a reference always provides a phrase for the rendering like the other db objects:
        // the linked phrase, or an empty phrase e.g. for a new reference of an add form
        $test_name = 'the phrase of a new reference is empty but never null';
        $ref_new = new ref();
        $t->assert($test_name, $ref_new->phrase()->id(), 0);
        // a back link carries only the id, so a test render has a reference with an id but no phrase
        $test_name = 'the phrase of a reference known only by its id is empty';
        $ref_new->set_id(refs::PI_ID);
        $t->assert($test_name, $ref_new->phrase()->id(), 0);
        $test_name = 'the phrase of a linked reference is the linked phrase';
        $t->assert_true($test_name, $ref->phrase()->id() != 0);

        $t->subheader($ts . 'api mapper');

        // the frontend reference takes the external key of the api message
        $test_name = 'the api mapper sets the external key of a reference';
        $ref_map = new ref();
        $ref_map->api_mapper(json_decode($t_ref->reference()->api_json(), true), $msg);
        $t->assert($test_name, $ref_map->external_key(), refs::PI_KEY);
        $msg->reset();

        // the id is mandatory, because without it a save would add a new reference
        $test_name = 'a reference json without an id is refused';
        $t->assert_false($test_name, $ref_map->api_mapper([json_fields::EXTERNAL_KEY => refs::PI_KEY], $msg));
        $msg->reset();

        $t->subheader($ts . 'debug id');

        // a reference has no own debug text, so the db object debug text shows its id
        $test_name = 'shows the id of the reference';
        $ref_dsp = new ref($t_ref->reference()->api_json());
        $id_txt = (string)refs::PI_ID;
        $dsp = $ref_dsp->dsp_id();
        $t->assert_text_contains($test_name, $dsp, $id_txt);

        // negative: a new reference has no database id to show
        $test_name = 'shows no id for a new reference';
        $ref_dsp = new ref();
        $id_txt = (string)refs::PI_ID;
        $dsp = $ref_dsp->dsp_id();
        $t->assert_text_not_contains($test_name, $dsp, $id_txt);

        $t->subheader($ts . 'source select');

        // the reference add and edit form offers the sources of the request cache and preselects
        // the source of the reference - never the reference itself - so that saving the form does
        // not silently remove the source; the same id is sent as the opening value, so that the
        // confirm view can show the source before the change (see db_object::add_pre_value)
        // the selector loads the list, so each call gets its own copy of the cached sources
        $t_src = new test_sources($t);

        $test_name = 'the source selector preselects the source of the reference';
        $sel_html = $ref_filled->source_selector(
            views::REF_EDIT, $msg, '', $t_src->source_list_ui(), true);
        $t->assert_text_contains($test_name, $sel_html,
            '<option value="' . sources::SIB_ID . '"  selected >');
        $msg->reset();
        $test_name = '... and sends the opening source as the pre value';
        $t->assert_text_contains($test_name, $sel_html,
            $html->form_hidden(url_var::PRE . url_var::SOURCE, (string)sources::SIB_ID));

        // negative: a reference without a source preselects none, so the user sees that it is unset
        $test_name = 'a reference without a source preselects no source';
        $sel_no_src = $ref->source_selector(
            views::REF_EDIT, $msg, '', $t_src->source_list_ui(), true);
        $t->assert_text_contains($test_name, $sel_no_src, '<option value="0" selected>');
        $msg->reset();
        $test_name = '... and sends no source as the pre value';
        $t->assert_text_contains($test_name, $sel_no_src,
            $html->form_hidden(url_var::PRE . url_var::SOURCE, '0'));

        // negative: a reference whose api message names no source has no source object
        $test_name = 'a reference without a source has no source';
        $no_src = $ref->source();
        $t->assert_null($test_name, $no_src?->name());

        $t->subheader($ts . 'view tab box');

        // the third column of the reference page shows the tab box like the source page
        // (see the 'view tab box' of base_views.json ref_default); a reference is a sandbox
        // object, so the same renderer builds the change log and the overwrite tabs for it
        $list = new ui_list();
        $log_tab_ref = 'href="#' . strtolower($mtr->txt(msg_id::FORM_SUB_TITLE_LOG)) . '"';
        $views_tab_ref = 'href="#' . strtolower($mtr->txt(msg_id::FORM_SUB_TITLE_VIEWS)) . '"';
        // test mode so the change log table is built without loading it from the database
        $tab_html = $list->view_tab_box($ref_filled, $msg, true);

        $test_name = 'the reference page shows the changes tab';
        $t->assert_text_contains($test_name, $tab_html, $log_tab_ref);

        // the views of an object are offered by the edit menu of the navbar only
        $test_name = 'the reference page shows no views tab';
        $t->assert_text_not_contains($test_name, $tab_html, $views_tab_ref);

        $t->subheader($ts . 'wikipedia refresh');

        // the stored word and the word as the backend answers the wikipedia refresh
        $t_wrd = new test_words($t);
        $refresh_par = url_var::REFRESH . '=' . url_var::REFRESH_WIKIPEDIA;
        $edit_url = [url_var::MASK => views::WORD_EDIT_ID, url_var::ID => word_names::MATH_ID];
        $old = new word_ui($t_wrd->word_math()->api_json());
        $old->description = word_names::MATH_COM;
        $old->ref_lst = $t_ref->ref_list_math_wikipedia_ui();
        $new = new word_ui($t_wrd->word_math()->api_json());
        $new->description = self::MATH_WIKIPEDIA_COM;
        $new->ref_lst = $t_ref->ref_list_math_wikipedia_ui();
        $new_wd_ref = $new->ref_lst->get_by_type_code_id(ref_types::WIKIDATA);
        $new_wd_ref->set_external_key(refs::CHANGE_NEW_KEY);
        $phr = $old->phrase();

        $test_name = 'a wikipedia reference links to the refresh of the phrase description';
        $wiki_ref = $old->ref_lst->get_by_type_code_id(ref_types::WIKIPEDIA);
        $lnk_html = $wiki_ref->name_link();
        $t->assert_text_contains($test_name, $lnk_html, $refresh_par);

        $test_name = 'a wikidata reference has no wikipedia refresh';
        $lnk_html = $ref->name_link();
        $t->assert_text_not_contains($test_name, $lnk_html, $refresh_par);

        $test_name = 'an empty reference list has no wikipedia reference';
        $no_ref = new ref_list_ui()->get_by_type_code_id(ref_types::WIKIPEDIA);
        $t->assert_null($test_name, $no_ref?->external_key());

        $test_name = 'the wikipedia refresh shows the confirm page of the edit';
        $cnf_url = $phr->confirm_url_of_refresh($edit_url, $old, $new, $msg);
        $mask = $cnf_url[url_var::MASK];
        $t->assert($test_name, $mask, views::CONFIRM_EDIT_ID);

        $test_name = '... with the wikipedia text as the new description';
        $description = $cnf_url[url_var::DESCRIPTION];
        $t->assert($test_name, $description, self::MATH_WIKIPEDIA_COM);

        $test_name = '... with the stored description as the from value';
        $pre_description = $cnf_url[url_var::PRE . url_var::DESCRIPTION];
        $t->assert($test_name, $pre_description, word_names::MATH_COM);

        $wd_row = phrase_ui::REFRESH_WIKIDATA_ROW;
        $test_name = '... and with the changed wikidata id as a reference row of the same confirm';
        $cnf_rows = url_var::ref_rows($cnf_url);
        $t->assert($test_name, $cnf_rows[$wd_row][url_var::EXTERNAL_KEY] ?? '', refs::CHANGE_NEW_KEY);
        $test_name = '... that changes the stored wikidata reference';
        $t->assert($test_name, $cnf_rows[$wd_row][url_var::ACTION] ?? '', url_var::CRUD_UPDATE);
        $test_name = '... with the stored wikidata id as the from value';
        $cnf_pre = url_var::ref_row_pre_values($cnf_url);
        $t->assert($test_name, $cnf_pre[$wd_row][url_var::EXTERNAL_KEY] ?? '', refs::MATH_KEY);

        $test_name = 'a phrase without wikidata reference gets its add with the same confirm';
        $no_wd = clone $old;
        $no_wd->ref_lst = $t_ref->ref_list_math_without_wikidata_ui();
        $add_url = $phr->confirm_url_of_refresh($edit_url, $no_wd, $new, $msg);
        $add_rows = url_var::ref_rows($add_url);
        $t->assert($test_name, $add_rows[$wd_row][url_var::ACTION] ?? '', url_var::CRUD_CREATE);
        $test_name = '... still on the confirm page of the description';
        $t->assert($test_name, $add_url[url_var::MASK], views::CONFIRM_EDIT_ID);
        $msg->reset();

        $test_name = 'an unchanged wikidata id is not confirmed again';
        $same_url = $phr->confirm_url_of_refresh($edit_url, $old, $old, $msg);
        $t->assert($test_name, url_var::ref_rows($same_url), []);
        $msg->reset();

        // negative: without any wikidata item the refresh changes nothing
        $test_name = 'a wikipedia page without wikidata item shows the page of the phrase again';
        $none_url = $phr->confirm_url_of_refresh($edit_url, $no_wd, $no_wd, $msg);
        $t->assert($test_name, $none_url[url_var::MASK], views::WORD_ID);
        $test_name = '... with the message that no wikidata link has been found';
        $t->assert_text_contains($test_name, $msg->text(), word_names::MATH);
        $msg->reset();

        $test_name = 'the confirm page of a triple keeps its to phrase';
        $t_trp = new test_triples($t);
        $ge_old = $t_trp->triple_ge_ui();
        $ge_old->ref_lst = $t_ref->ref_list_geneva_ui();
        $ge_new = $t_trp->triple_ge_ui();
        $ge_new->ref_lst = $t_ref->ref_list_geneva_ui();
        $ge_new->description = self::GENEVA_WIKIPEDIA_COM;
        $ge_edit_url = [url_var::MASK => views::TRIPLE_EDIT_ID, url_var::ID => triple_names::CITY_GE_ID];
        $ge_url = $ge_old->phrase()->confirm_url_of_refresh($ge_edit_url, $ge_old, $ge_new, $msg);
        $to_id = $ge_url[url_var::PHRASE_TO] ?? 0;
        $t->assert($test_name, $to_id, word_names::CITY_ID);

        $test_name = '... and its stored to phrase, so that only the description is written';
        $pre_to_id = $ge_url[url_var::PRE . url_var::PHRASE_TO] ?? 0;
        $t->assert($test_name, $pre_to_id, word_names::CITY_ID);

        $test_name = '... with the wikipedia text as the new description';
        $description = $ge_url[url_var::DESCRIPTION];
        $t->assert($test_name, $description, self::GENEVA_WIKIPEDIA_COM);
        $msg->reset();

        $preview = new ui_preview();
        $refs_title = $mtr->txt(msg_id::INFO_CONFIRM_REFS);
        $test_name = 'the confirm page lists the reference changes of the same confirm';
        $cnf_html = $preview->popup_changes($msg, $cnf_url, $old, true);
        $t->assert_text_contains($test_name, $cnf_html, $refs_title);
        // the cells of the reference table, because the hidden '86' input carries the stored id too
        $test_name = '... with the new wikidata id as to';
        $new_cell = $html->td('<span class="' . styles::STYLE_CHANGED . '">' . refs::CHANGE_NEW_KEY . '</span>');
        $t->assert_text_contains($test_name, $cnf_html, $new_cell);
        $test_name = '... and the stored one as from';
        $old_cell = $html->td('<span class="' . styles::STYLE_GREY . '">' . refs::MATH_KEY . '</span>');
        $t->assert_text_contains($test_name, $cnf_html, $old_cell);
        $msg->reset();

        $test_name = 'a confirm page without reference change lists no reference';
        $same_html = $preview->popup_changes($msg, $same_url, $old, true);
        $t->assert_text_not_contains($test_name, $same_html, $refs_title);
        $msg->reset();

        $test_name = 'a confirm that changes only a reference still shows the impact of the change';
        $only_ref_url = $phr->confirm_url_of_refresh($edit_url, $old, $old, $msg);
        $only_ref_url = array_merge($only_ref_url, url_var::ref_row_url_array(url_var::ref_rows($cnf_url)));
        $t->assert_text_contains($test_name, $preview->popup_impact($only_ref_url), $mtr->txt(msg_id::POPUP_IMPACT));

        $test_name = 'the confirm form carries the reference rows to the confirmed write';
        $wd_key_var = url_var::REF_ROW . url_var::EXTERNAL_KEY . $wd_row;
        $back_html = new system_form()->form_back(views::CONFIRM_EDIT_ID, word_names::MATH_ID, $cnf_url);
        $t->assert_text_contains($test_name, $back_html, $html->form_hidden($wd_key_var, refs::CHANGE_NEW_KEY));
        $msg->reset();

        $t->subheader($ts . 'reference rows');

        $test_name = 'the reference rows written to the url are read back unchanged';
        $rows = [$wd_row => [url_var::ACTION => url_var::CRUD_DELETE, url_var::REF => refs::MATH_ID]];
        $t->assert($test_name, url_var::ref_rows(url_var::ref_row_url_array($rows)), $rows);
        $test_name = '... and their stored values as from values';
        $pre_rows = [$wd_row => [url_var::EXTERNAL_KEY => refs::MATH_KEY]];
        $rows_url = url_var::ref_row_url_array($rows, $pre_rows);
        $t->assert($test_name, url_var::ref_row_pre_values($rows_url), $pre_rows);

        $test_name = 'a delete row lists the stored external key of the reference as from';
        $del_html = $preview->popup_changes($msg, array_merge($same_url, $rows_url), $old, true);
        $from_cell = $html->td('<span class="' . styles::STYLE_GREY . '">' . refs::MATH_KEY . '</span>');
        $t->assert_text_contains($test_name, $del_html, $from_cell);
        $test_name = '... and nothing as to';
        $to_cell = $html->td('<span class="' . styles::STYLE_CHANGED . '"></span>');
        $t->assert_text_contains($test_name, $del_html, $to_cell);
        $msg->reset();
        // negative: a typed url cannot set the phrase of a row nor a row without action
        $test_name = 'a row var that is not a reference field is ignored';
        $phr_var = url_var::REF_ROW . url_var::PHRASE . $wd_row;
        $typed_url = array_merge(url_var::ref_row_url_array($rows), [$phr_var => word_names::MATH_ID]);
        $t->assert($test_name, url_var::ref_rows($typed_url), $rows);
        $test_name = '... and a row without crud action is no change';
        $key_var = url_var::REF_ROW . url_var::EXTERNAL_KEY . $wd_row;
        $t->assert($test_name, url_var::ref_rows([$key_var => refs::MATH_KEY]), []);
        $test_name = 'a written row is removed from the url together with its stored values';
        $t->assert($test_name, url_var::without_ref_row($rows_url, $wd_row), []);
    }

}