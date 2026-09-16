<?php

/*

    web/component/execute/ui_select.php - html interface components to select an object
    -----------------------------------


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

namespace Zukunft\ZukunftCom\main\php\web\component\execute;

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::CONST . 'icons.php';
include_once html_paths::HELPER . 'config.php';
include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::HTML . 'html_selector.php';
include_once html_paths::HTML . 'styles.php';
include_once html_paths::PHRASE . 'phrase.php';
include_once html_paths::PHRASE . 'phrase_list.php';
include_once html_paths::SANDBOX . 'db_object.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::VIEW . 'view_list.php';
include_once html_paths::TYPES . 'type_list.php';
include_once html_paths::TYPES . 'type_object.php';
include_once html_paths::SHARED_CONST . 'triples.php';
include_once html_paths::SHARED_CONST . 'views.php';
include_once html_paths::SHARED_CONST . 'words.php';
include_once html_paths::SHARED_ENUM . 'messages.php';
include_once html_paths::SHARED_TYPES . 'view_styles.php';
include_once html_paths::SHARED . 'library.php';
include_once html_paths::SHARED . 'url_var.php';

use Zukunft\ZukunftCom\main\php\web\const\icons;
use Zukunft\ZukunftCom\main\php\web\helper\config;
use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\html\styles;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\web\sandbox\db_object;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\view\view_list;
use Zukunft\ZukunftCom\main\php\web\types\type_list;
use Zukunft\ZukunftCom\main\php\web\types\type_object;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\types\view_styles;
use Zukunft\ZukunftCom\main\php\shared\url_var;

class ui_select
{

    // the suffix of the form name of the value form that follows the phrase selection (see value_add_simple)
    const string VALUE_FORM_SUFFIX = '_value';

    /**
     * @return string the name of a phrase and give the user the possibility to change the phrase name
     */
    function phrase_select(
        db_object $phr,
        string $form_name,
        phrase_list $phr_lst
    ): string
    {
        return $phr->phrase_selector($phr_lst, url_var::PHRASE, $form_name, $phr->id());
    }

    /**
     * the html code to select the view for the given object
     * which can also be the component itself
     * so view_select (for the $obj) can call view_selector of this class if $obj is of class component
     * @param db_object $dbo the word, triple or formula object that should be shown to the user
     * @param string $form the name of the view which is also used for the html form name
     * @param data_object|null $cfg the context used to create the view
     * @return string with the html code to select a view
     */
    function view_select(db_object $dbo, string $form, user_message $msg, ?data_object $cfg = null): string
    {
        return $dbo->view_selector($form, $this->view_list_for($dbo, $msg, $cfg), $msg);
    }

    /**
     * the views to offer in a view selector: the request cache list if it is already filled, otherwise
     * the object's own views loaded via the api (view_list::load_by_pattern), so an edit form whose
     * context has no preloaded view list still shows the views that can be assigned (docs/llm/frontend.md)
     *
     * @param db_object $dbo the object whose assignable views are loaded when the cache has none
     * @param data_object|null $cfg the request context that may already hold the loaded view list
     * @return view_list the views to show in the selector (the caller filters them per object type)
     */
    function view_list_for(db_object $dbo, user_message $msg, ?data_object $cfg = null): view_list
    {
        $msk_lst = null;
        if ($cfg != null and $cfg->has_view_list()) {
            $msk_lst = $cfg->view_list();
        }
        if ($msk_lst == null) {
            $msk_lst = $dbo->view_list($msg);
        }
        return $msk_lst;
    }

    /**
     * show a selection list (e.g. of languages) and let the user pick one entry by name
     * each list entry has a database id, a display name, a description and an alternative
     * unique code id; only the name is shown, the database id is submitted as the form value
     * @param db_object|type_object|null $dbo the currently selected backend object; its id() pre-selects the matching list entry
     * @param type_list $lst the list whose entries the user can choose from
     * @param string $form_name the name of the surrounding html form
     * @param string $field_name the form field name carrying the chosen database id on submit
     * @param msg_id $label_id the message id of the label shown above the selector
     * @return string the html code to render the selection list
     */
    function list_select(
        db_object|type_object|null $dbo,
        type_list                  $lst,
        string                     $form_name = '',
        string                     $field_name = url_var::ID,
        msg_id                     $label_id = msg_id::FORM_SELECT
    ): string
    {
        $selected = $dbo?->id();
        return $lst->type_selector($form_name, $selected, $field_name, $label_id);
    }

    /**
     * the pure html selection of the phrases of a new value one after the other followed by the number field:
     * the chosen phrases are carried as the phrase list of the url and the selector offers the phrases that
     * the user uses most often with the phrases that match the typed chars on top; a typed name that names
     * one phrase is added and a selector for the next phrase is shown; the refresh icon of the pattern field
     * is the first submit of the form, so that the enter key only updates the phrase selection and never
     * changes any data, while the add button of the number form writes the value and the more details link
     * opens the detailed value add form with the phrases chosen so far
     *
     * @param string $form_name the name of the html form of the view
     * @param array $url_arr the url of the shown form with the chosen phrases, the typed chars and the selection
     * @param user_message $msg to report a problem of the phrase load
     * @param data_object|null $cfg the request cache with the phrases that the test mode uses
     * @param bool $test_mode true to find the phrases in the request cache without a backend call
     * @return string the html code of the phrase selection
     */
    function phrase_steps(
        string       $form_name,
        array        $url_arr,
        user_message $msg,
        ?data_object $cfg,
        bool         $test_mode
    ): string
    {
        [$chosen, $matches, $pattern] = $this->phrase_step_selection($url_arr, $msg, $cfg, $test_mode);
        $chosen_ids = implode(',', $chosen->id_lst());
        $result = $this->phrase_step_fields($form_name, $url_arr, $chosen, $matches, $pattern, $msg, $cfg, $test_mode);
        // as soon as a phrase is chosen the number can be entered, so that the value can be added
        // without the detailed form
        if ($chosen_ids != '') {
            $result .= $this->value_number_form($form_name, $chosen_ids);
        }
        $result .= $this->value_detail_link($url_arr, $chosen_ids);
        return $result;
    }

    /**
     * the pure html form that adds a value just by its phrases and the number: the phrases are selected step by
     * step like in phrase_steps and as soon as a phrase is chosen a second form with the chosen phrases, the
     * number field and the add button follows; the hidden value add mask with the confirmed step lets
     * url_to_action write the value directly and after the write the new value is shown with its default view
     *
     * @param string $form_name the name of the html form of the view
     * @param array $url_arr the url of the shown form with the chosen phrases, the typed chars and the selection
     * @param user_message $msg to report a problem of the phrase load
     * @param data_object|null $cfg the request cache with the phrases that the test mode uses
     * @param bool $test_mode true to find the phrases in the request cache without a backend call
     * @return string the html code of the phrase selection followed by the value form
     */
    function value_add_simple(
        string       $form_name,
        array        $url_arr,
        user_message $msg,
        ?data_object $cfg,
        bool         $test_mode
    ): string
    {
        [$chosen, $matches, $pattern] = $this->phrase_step_selection($url_arr, $msg, $cfg, $test_mode);
        $chosen_ids = implode(',', $chosen->id_lst());
        $result = $this->phrase_step_fields($form_name, $url_arr, $chosen, $matches, $pattern, $msg, $cfg, $test_mode);
        if ($chosen_ids != '') {
            $result .= $this->value_number_form($form_name, $chosen_ids);
        }
        return $result;
    }

    /**
     * the phrases chosen so far including a phrase that the typed chars name
     *
     * @param array $url_arr the url of the shown form with the chosen phrases, the typed chars and the selection
     * @param user_message $msg to report a problem of the phrase load
     * @param data_object|null $cfg the request cache with the phrases that the test mode uses
     * @param bool $test_mode true to find the phrases in the request cache without a backend call
     * @return array the chosen phrase list, the phrases that start with the typed chars and the typed chars
     *               that are left to show because they name no single phrase
     */
    private function phrase_step_selection(
        array        $url_arr,
        user_message $msg,
        ?data_object $cfg,
        bool         $test_mode
    ): array
    {
        $ids = $this->phrase_step_ids($url_arr);
        $pattern = trim((string)($url_arr[url_var::PATTERN] ?? ''));
        // a phrase selected from the matches of the last check replaces the typed chars
        if ((int)($url_arr[url_var::PHRASE] ?? 0) != 0) {
            $pattern = '';
        }
        $matches = new phrase_list();
        if ($pattern != '') {
            $matches = $this->phrase_matches($pattern, $msg, $cfg, $test_mode);
            $match_ids = $matches->id_lst();
            // an exact name or a single match names the phrase, so it is added without a selection
            $exact = $matches->get_by_name($pattern, $msg);
            $found_id = 0;
            if ($exact != null) {
                $found_id = $exact->id();
            } elseif (count($match_ids) == 1) {
                $found_id = $match_ids[0];
            }
            if ($found_id != 0) {
                $ids[] = $found_id;
                $pattern = '';
            }
        }
        $chosen = $this->phrases_by_ids(array_unique($ids), $msg, $cfg, $test_mode);
        return [$chosen, $matches, $pattern];
    }

    /**
     * the chosen phrases, the selector of the next phrase, the info if no phrase matches the typed chars
     * and the field for the typed chars with the refresh icon
     *
     * the selector is always shown, so that a user who has chosen a phrase directly gets the selector for
     * the next phrase and a user who has not yet typed anything can pick one of the preloaded phrases
     *
     * @param string $form_name the name of the html form of the view
     * @param array $url_arr the url of the shown form used for the links of the chosen phrases
     * @param phrase_list $chosen the phrases chosen so far
     * @param phrase_list $matches the phrases that start with the typed chars
     * @param string $pattern the typed chars that name no single phrase
     * @param user_message $msg to report a problem of the phrase load
     * @param data_object|null $cfg the request cache with the phrases that the test mode uses
     * @param bool $test_mode true to take the offered phrases from the request cache without a backend call
     * @return string the html code of the phrase selection fields
     */
    private function phrase_step_fields(
        string       $form_name,
        array        $url_arr,
        phrase_list  $chosen,
        phrase_list  $matches,
        string       $pattern,
        user_message $msg,
        ?data_object $cfg,
        bool         $test_mode
    ): string
    {
        global $mtr;
        $html = new html_base();
        $lib = new library();

        // the ids before the names, because the chosen phrases keep the order that the user has chosen
        $chosen_ids = implode(',', $chosen->id_lst());
        $result = $this->chosen_phrase_fields($form_name, $chosen, $url_arr);
        $result .= $html->form_hidden(url_var::PHRASE_LIST, $chosen_ids);
        $result .= $this->phrase_selector($form_name, $this->phrase_offer($chosen, $matches, $msg, $cfg, $test_mode));
        // the typed chars name no phrase at all, so the user is told instead of getting an empty selection
        if ($pattern != '' and $matches->is_empty()) {
            $no_match = $mtr->txt(msg_id::INFO_NO_PHRASE_FOR_PATTERN);
            $result .= $lib->msg_var_replace($no_match, msg_id::VAR_PATTERN, $html->esc($pattern));
        }
        // the refresh icon adds the matching phrases to the selector and is the first submit of the form,
        // so that the enter key triggers it and never saves anything
        $result .= $html->form_field(url_var::PATTERN, msg_id::FORM_FIELD_PHRASE_PATTERN, $pattern,
            html_base::INPUT_TEXT, '', view_styles::COL_SM_4, url_var::REFRESH_PHRASES);
        return $result;
    }

    /**
     * the phrases chosen so far, each followed by the icon that removes it from the new value
     *
     * the remove icon keeps the view that shows the form (the form name is the code id of the view), so
     * that the pure and the simple value add view each keep their own phrase selection; the link carries
     * the complete phrase list and the phrase to remove, so that it does not depend on the hidden field
     * of the form, which a link never submits
     *
     * @param string $form_name the name of the html form which is also the code id of the shown view
     * @param phrase_list $chosen the phrases chosen so far
     * @param array $url_arr the url of the shown form used for the back part of the remove link
     * @return string the html code of the chosen phrases with their remove icons
     */
    private function chosen_phrase_fields(string $form_name, phrase_list $chosen, array $url_arr): string
    {
        global $mtr;
        $html = new html_base();

        $chosen_ids = implode(',', $chosen->id_lst());
        $icon = $html->icon(icons::DEL);
        $tip = $mtr->txt(msg_id::PHRASE_REMOVE);
        $names = [];
        foreach ($chosen->lst() as $phr) {
            $par = url_var::PHRASE_LIST . url_var::EQ . $chosen_ids
                . url_var::ADD . url_var::UNLINK_PHRASE . url_var::EQ . $phr->id();
            $url = $html->url_back($form_name, 0, $url_arr, $par);
            $names[] = $phr->name_link() . ' '
                . $html->ref($url, $icon, $tip, styles::HEADING_ICON_INLINE, true);
        }
        return implode(', ', $names);
    }

    /**
     * the second form of a phrase selection view with the chosen phrases, the number and the add button
     *
     * the refresh of the phrase selection must keep the selection view, so the vars that write the value
     * are in a form of their own and the hidden value add mask with the confirmed step lets url_to_action
     * write the value directly and show it afterwards with its default view
     *
     * @param string $form_name the name of the html form of the view
     * @param string $chosen_ids the ids of the chosen phrases in the order that the user has chosen them
     * @return string the html code of the number form
     */
    private function value_number_form(string $form_name, string $chosen_ids): string
    {
        global $mtr;
        $html = new html_base();

        $result = $html->form_end();
        $result .= $html->form_start($form_name . self::VALUE_FORM_SUFFIX);
        $result .= $html->form_hidden(url_var::MASK, (string)views::VALUE_ADD_DETAIL_ID);
        $result .= $html->form_hidden(url_var::STEP, url_var::STEP_CONFIRMED);
        $result .= $html->form_hidden(url_var::BACK . url_var::MASK, (string)views::VALUE_DEFAULT_ID);
        $result .= $html->form_hidden(url_var::PHRASE_LIST, $chosen_ids);
        $result .= $html->form_field(url_var::NUMERIC_VALUE, msg_id::FORM_FIELD_VALUE, '', html_base::INPUT_NUMBER);
        $result .= $html->button_bs($mtr->txt(msg_id::SYSTEM_BUTTON_ADD), '', '', url_var::POST_SUBMIT);
        return $result;
    }

    /**
     * the link at the bottom left of a simple add form that opens the detailed form with the same parameters,
     * so that the user can add the fields that the simple form does not offer e.g. the source of a value
     *
     * @param array $url_arr the url of the shown form as back target
     * @param string $chosen_ids the ids of the chosen phrases, empty if the user has not yet chosen one
     * @return string the html code of the more details link
     */
    private function value_detail_link(array $url_arr, string $chosen_ids): string
    {
        global $mtr;
        $html = new html_base();

        $preset = '';
        if ($chosen_ids != '') {
            $preset = url_var::PHRASE_LIST . url_var::EQ . $chosen_ids;
        }
        $url = $html->url_back(views::VALUE_ADD_DETAIL_ID, 0, $url_arr, $preset);
        $link = $html->ref($url, $mtr->txt(msg_id::SYSTEM_BUTTON_MORE_DETAILS));
        return $html->div($link, view_styles::TEXT_LEFT);
    }

    /**
     * the phrases offered in the selector of the next phrase: the phrases that match the typed chars sorted
     * by impact first, filled up with the phrases that the user uses most often, without the phrases that
     * are already chosen
     *
     * @param phrase_list $chosen the phrases chosen so far
     * @param phrase_list $matches the phrases that start with the typed chars
     * @param user_message $msg to report a problem of the phrase load
     * @param data_object|null $cfg the request cache with the phrases that the test mode uses
     * @param bool $test_mode true to take the offered phrases from the request cache without a backend call
     * @return phrase_list the phrases to offer in the order that they should be shown
     */
    private function phrase_offer(
        phrase_list  $chosen,
        phrase_list  $matches,
        user_message $msg,
        ?data_object $cfg,
        bool         $test_mode
    ): phrase_list
    {
        $result = new phrase_list();
        $used_ids = $chosen->id_lst();
        // the best matching phrases are on top, so that the typed chars decide what the user sees first
        $best = clone $matches;
        $best->sort_by_impact();
        foreach ($best->lst() as $phr) {
            if (!in_array($phr->id(), $used_ids)) {
                $used_ids[] = $phr->id();
                $result->add($phr, $msg);
            }
        }
        // the preloaded phrases follow, so that the selector is never empty
        $limit = $this->phrase_preload_limit($msg);
        $added = 0;
        foreach ($this->phrase_preload($msg, $cfg, $test_mode)->lst() as $phr) {
            if ($added < $limit and !in_array($phr->id(), $used_ids)) {
                $used_ids[] = $phr->id();
                $result->add($phr, $msg);
                $added++;
            }
        }
        return $result;
    }

    /**
     * the selector of the next phrase
     *
     * the label of a phrase selector would sort the entries by name, but the offered phrases are already
     * sorted by relevance, so the list of the selector is set after the selector has been created
     *
     * @param string $form_name the name of the html form of the view
     * @param phrase_list $offer the phrases to offer in the order that they should be shown
     * @return string the html code of the selector or '' if no phrase can be offered
     */
    private function phrase_selector(string $form_name, phrase_list $offer): string
    {
        if ($offer->is_empty()) {
            return '';
        }
        $sel = $offer->selector_ui($form_name, 0, url_var::PHRASE, msg_id::FORM_SELECT_PHRASE);
        $sel->lst = $offer->lst_key();
        return $sel->display();
    }

    /**
     * the phrases that the user uses most often to preload the phrase selector
     *
     * @param user_message $msg to report a problem of the phrase load
     * @param data_object|null $cfg the request cache with the phrases that the test mode uses
     * @param bool $test_mode true to take the phrases from the request cache without a backend call
     * @return phrase_list the phrases to offer before the user has typed anything
     */
    private function phrase_preload(user_message $msg, ?data_object $cfg, bool $test_mode): phrase_list
    {
        $result = new phrase_list();
        if ($test_mode) {
            $result = $cfg?->phrase_list() ?? new phrase_list();
        } else {
            // TODO Prio 2 replace the fallback list with the phrases that this user uses most often
            $result->load_fallback($msg);
        }
        return $result;
    }

    /**
     * @param user_message $msg to report a problem of reading the config
     * @return int the number of preloaded phrases from the frontend config
     *             (config.yaml "user > frontend > lists > preload > phrase list")
     */
    private function phrase_preload_limit(user_message $msg): int
    {
        global $ui_sys;
        $result = config::LIMIT_PHRASE_PRELOAD;
        if ($ui_sys?->cfg !== null) {
            $result = (int)$ui_sys->cfg->get_by(
                [triples::PHRASE_LIST, words::PRELOAD, words::LISTS, words::FRONTEND, words::USER],
                $msg, config::LIMIT_PHRASE_PRELOAD);
        }
        return $result;
    }

    /**
     * @param array $url_arr the url of the phrase selection form
     * @return array the phrase ids of the url: the chosen phrase list followed by a phrase selected from
     *               the matches and without the phrase that the user has removed
     */
    private function phrase_step_ids(array $url_arr): array
    {
        $ids = [];
        foreach (explode(',', (string)($url_arr[url_var::PHRASE_LIST] ?? '')) as $id) {
            if ((int)$id != 0) {
                $ids[] = (int)$id;
            }
        }
        $selected = (int)($url_arr[url_var::PHRASE] ?? 0);
        if ($selected != 0) {
            $ids[] = $selected;
        }
        // the user has pressed the remove icon of one phrase, so it is dropped from the selection
        $removed = (int)($url_arr[url_var::UNLINK_PHRASE] ?? 0);
        if ($removed != 0) {
            $ids = array_values(array_diff($ids, [$removed]));
        }
        return $ids;
    }

    /**
     * the phrases whose name starts with the typed chars, from the request cache in the test mode and else
     * from the backend
     *
     * @param string $pattern the typed name or the first chars of a phrase name
     * @param user_message $msg to report a problem of the phrase list
     * @param data_object|null $cfg the request cache with the phrases that the test mode uses
     * @param bool $test_mode true to search the request cache without a backend call
     * @return phrase_list the phrases that start with the typed chars
     */
    private function phrase_matches(string $pattern, user_message $msg, ?data_object $cfg, bool $test_mode): phrase_list
    {
        $matches = new phrase_list();
        if ($test_mode) {
            $cache = $cfg?->phrase_list() ?? new phrase_list();
            $matches = $cache->filter_by_name_start($pattern, $msg);
        } else {
            $matches->get_by_pattern($pattern);
        }
        return $matches;
    }

    /**
     * the phrases with the given ids in the given order, so an id of the url that names no phrase is dropped
     *
     * @param array $ids the phrase ids in the order that the user has chosen them
     * @param user_message $msg to report a problem of the phrase load
     * @param data_object|null $cfg the request cache with the phrases that the test mode uses
     * @param bool $test_mode true to take the phrases from the request cache without a backend call
     * @return phrase_list the existing phrases of the given ids
     */
    private function phrases_by_ids(array $ids, user_message $msg, ?data_object $cfg, bool $test_mode): phrase_list
    {
        $found = new phrase_list();
        if ($test_mode) {
            $found = $cfg?->phrase_list() ?? new phrase_list();
        } elseif ($ids != []) {
            $found->load_by_ids($ids, $msg);
        }
        $by_id = [];
        foreach ($found->lst() as $phr) {
            $by_id[$phr->id()] = $phr;
        }
        $result = new phrase_list();
        foreach ($ids as $id) {
            if (array_key_exists($id, $by_id)) {
                $result->add($by_id[$id], $msg);
            }
        }
        return $result;
    }

}
