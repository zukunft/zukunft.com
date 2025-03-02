<?php

/*

    web/word/word.php - create HTML code to display a words based on the api json message
    -----------------

    The main sections of this object are
    - object vars:       the variables of this word object
    - set and get:       to capsule the vars from unexpected changes
    - api:               set the object vars based on the api json message and create a json for the backend
    - cast:              create related frontend objects e.g. the phrase of a triple
    - load:              get an api json from the backend and
    - related:           load related objects from the backend
    - base:              html code for the single object vars
    - buttons:           html code for the buttons e.g. to add, edit, del, link or unlink
    - select:            html code to select parameter like the type
    - table:             html code for object vars within a table
    - actions:           send change messages to the backend
    - log:               show the hist changes related to this object
    - type:              link to code actions
    - views:             system view to add, change or delete the word (to be deprecated)


    This file is part of the frontend of zukunft.com - calc with words

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

    Copyright (c) 1995-2025 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

namespace html\word;

include_once WEB_SANDBOX_PATH . 'sandbox_typed.php';
include_once API_OBJECT_PATH . 'api_message.php';
include_once WEB_HTML_PATH . 'button.php';
include_once WEB_HTML_PATH . 'html_base.php';
include_once WEB_HTML_PATH . 'html_selector.php';
include_once WEB_HTML_PATH . 'rest_ctrl.php';
include_once WEB_HTML_PATH . 'styles.php';
include_once SHARED_ENUM_PATH . 'foaf_direction.php';
//include_once WEB_FORMULA_PATH . 'formula.php';
//include_once WEB_HELPER_PATH . 'config.php';
include_once WEB_LOG_PATH . 'change_log_named.php';
//include_once WEB_LOG_PATH . 'user_log_display.php';
include_once WEB_PHRASE_PATH . 'phrase.php';
include_once WEB_PHRASE_PATH . 'phrase_list.php';
//include_once WEB_PHRASE_PATH . 'term.php';
include_once WEB_SANDBOX_PATH . 'sandbox_typed.php';
include_once WEB_SYSTEM_PATH . 'back_trace.php';
include_once WEB_SYSTEM_PATH . 'messages.php';
include_once WEB_USER_PATH . 'user_message.php';
include_once WEB_VERB_PATH . 'verb_list.php';
//include_once WEB_VIEW_PATH . 'view.php';
include_once SHARED_TYPES_PATH . 'phrase_type.php';
include_once SHARED_TYPES_PATH . 'view_styles.php';
include_once SHARED_PATH . 'api.php';
include_once SHARED_PATH . 'json_fields.php';
include_once SHARED_CONST_PATH . 'views.php';
include_once SHARED_CONST_PATH . 'words.php';
include_once SHARED_PATH . 'library.php';

use controller\api_message;
use html\button;
use html\formula\formula;
use html\helper\config;
use html\html_base;
use html\html_selector;
use html\log\change_log_named;
use html\log\user_log_display;
use html\phrase\phrase;
use html\phrase\phrase_list;
use html\phrase\term;
use html\rest_ctrl;
use html\sandbox\sandbox_typed;
use html\styles;
use html\system\back_trace;
use html\system\messages;
use html\user\user_message;
use html\verb\verb_list;
use html\view\view;
use shared\api;
use shared\enum\foaf_direction;
use shared\json_fields;
use shared\const\views;
use shared\const\words;
use shared\types\phrase_type;
use shared\types\view_styles;

class word extends sandbox_typed
{

    /*
     * object vars
     */

    // the language specific forms
    private ?string $plural = null;

    // the main parent phrase
    private ?phrase $parent = null;

    // the default view
    private ?view $msk = null;


    /*
     * construct and map
     */

    /**
     * set the vars of this word frontend object bases on the url array
     * public because it is reused e.g. by the phrase group display object
     * @param array $url_array an array based on $_GET from a form submit
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function url_mapper(array $url_array): user_message
    {
        $usr_msg = parent::url_mapper($url_array);
        if ($usr_msg->is_ok()) {
            if (array_key_exists(api::URL_VAR_PLURAL, $url_array)) {
                $this->set_plural($url_array[api::URL_VAR_PLURAL]);
            } else {
                $this->set_plural(null);
            }
            if (array_key_exists(api::URL_VAR_VIEW, $url_array)) {
                if ($url_array[api::URL_VAR_VIEW] != null) {
                    $this->set_view_id($url_array[api::URL_VAR_VIEW]);
                }
            }
        }
        return $usr_msg;
    }

    /**
     * set the vars of this object bases on the api json array
     * public because it is reused e.g. by the phrase group display object
     * @param array $json_array an api json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function api_mapper(array $json_array): user_message
    {
        // get body from message
        $api_msg = new api_message();
        $json_array = $api_msg->validate($json_array);

        $usr_msg = parent::api_mapper($json_array);
        if (array_key_exists(json_fields::PLURAL, $json_array)) {
            $this->set_plural($json_array[json_fields::PLURAL]);
        } else {
            $this->set_plural(null);
        }
        if (array_key_exists(json_fields::PARENT, $json_array)) {
            $this->set_parent($json_array[json_fields::PARENT]);
        } else {
            $this->set_parent(null);
        }
        return $usr_msg;
    }


    /*
     * api
     */

    /**
     * create an api json array for the backend based on this frontend object
     * @return array the json message array to send the updated data to the backend
     * an array is used (instead of a string) to enable combinations of api_array() calls
     */
    function api_array(): array
    {
        $vars = parent::api_array();

        $vars[json_fields::PLURAL] = $this->plural();
        if ($this->has_parent()) {
            $vars[json_fields::PARENT] = $this->parent()->api_array();
        }
        return array_filter($vars, fn($value) => !is_null($value) && $value !== '');
    }


    /*
     * set and get
     */

    function set_plural(?string $plural): void
    {
        $this->plural = $plural;
    }

    function plural(): ?string
    {
        return $this->plural;
    }

    function set_parent(?phrase $parent): void
    {
        $this->parent = $parent;
    }

    function parent(): ?phrase
    {
        return $this->parent;
    }

    function set_view_id(?int $view_id): void
    {
        $msk = new view();
        $msk->load_by_id($view_id);
        $this->set_view($msk);
    }

    function set_view(?view $view): void
    {
        $this->msk = $view;
    }

    function view(): ?view
    {
        return $this->msk;
    }

    /**
     * @param string|null $code_id the code id of the phrase type
     */
    function set_type(?string $code_id): void
    {
        global $phr_typ_cac;
        if ($code_id == null) {
            $this->set_type_id();
        } else {
            $this->set_type_id($phr_typ_cac->id($code_id));
        }
    }


    /*
     * cast
     */

    /**
     * to have a similar cast function in word that in triple for the phrase
     * @return $this
     */
    function word(): word
    {
        return $this;
    }

    /**
     * @returns phrase the phrase display object base on this word object
     */
    function phrase(): phrase
    {
        $phr = new phrase();
        $phr->set_obj($this);
        return $phr;
    }

    /**
     * @returns term the word object cast into a term object
     */
    function term(): term
    {
        $trm = new term();
        $trm->set_obj($this);
        return $trm;
    }


    /*
     * load
     */

    /*
     * related
     */

    /**
     * get the parent phrases of the given phrase
     * if a phrase list is given get only the parent phrases within the list
     * if no phrase list is given get the phrases from the api
     * e.g. for Zurich the list is City and Canton based on a phrase list with City, Canton and Country
     * but  for Zurich the list is City, Canton and Company based on a phrase list with Company, City, Canton and Country
     * @param phrase_list|null $phr_lst
     * @param int $levels the number of parent levels
     * @return phrase_list
     */
    function parents(?phrase_list $phr_lst = null, int $levels = 1): phrase_list
    {
        $lst = new phrase_list();
        $lst->load_related($this->phrase(), foaf_direction::UP);
        return $lst;
    }

    function children(): phrase_list
    {
        $lst = new phrase_list();
        $lst->load_related($this->phrase(), foaf_direction::DOWN);
        return $lst;
    }


    /*
     * base
     */

    /**
     * display a word with a link to the main page for the word
     * @param string|null $back the back trace url for the undo functionality
     * @param string $style the CSS style that should be used
     * @param int $msk_id database id of the view that should be shown
     * @returns string the html code
     */
    function name_link(?string $back = '', string $style = '', int $msk_id = views::WORD_ID): string
    {
        return parent::name_link($back, $style, $msk_id);
    }


    /*
     * buttons
     */

    /**
     * @return string the html code for a bottom
     * to create a new word for the current user
     */
    function btn_add(string $back = ''): string
    {
        return parent::btn_add_sbx(
            views::WORD_ADD,
            messages::WORD_ADD,
            $back);
    }

    /**
     * @return string the html code for a bottom
     * to change a word e.g. the name or the type
     */
    function btn_edit(string $back = ''): string
    {
        return parent::btn_edit_sbx(
            views::WORD_EDIT,
            messages::WORD_EDIT,
            $back);
    }

    /**
     * @return string the html code for a bottom
     * to exclude the word for the current user
     * or if no one uses the word delete the complete word
     */
    function btn_del(string $back = ''): string
    {
        return parent::btn_del_sbx(
            views::WORD_DEL,
            messages::WORD_DEL,
            $back);
    }

    /**
     * @returns string the html code to display a bottom to edit the word link in a table cell
     */
    function btn_unlink(int $link_id, string $back = ''): string
    {
        $url = (new html_base())->url(rest_ctrl::LINK . rest_ctrl::REMOVE, $link_id, $this->id());
        return (new button($url, $back))->del(messages::WORD_UNLINK);
    }


    /*
     * select
     */

    /**
     * wrapper for the word type selector
     * to prevent type changes of internal formula words
     * as a second line of defence
     * @param string $form the name of the html form
     * @return string the html code to select the phrase type
     */
    function dsp_type_selector(string $form): string
    {
        global $phr_typ_cac;
        $result = '';
        if ($phr_typ_cac->code_id($this->type_id()) == phrase_type::FORMULA_LINK) {
            $result .= ' type: ' . $phr_typ_cac->name($this->type_id());
        } else {
            $result .= $this->phrase_type_selector($form);
        }
        return $result;
    }

    /**
     * create the HTML code to select a phrase type
     * and select the phrase type of this word
     * @param string $form the name of the html form
     * @return string the html code to select the phrase type
     */
    public function phrase_type_selector(string $form): string
    {
        global $html_phrase_types;
        $used_phrase_id = $this->type_id();
        if ($used_phrase_id == null) {
            $used_phrase_id = $html_phrase_types->default_id();
        }
        return $html_phrase_types->selector($form, $used_phrase_id);
    }


    /*
     * table
     */

    /**
     * @param string $back the back trace url for the undo functionality
     * @param string $style the CSS style that should be used
     * @returns string the word as a table cell
     */
    function th(string $back = '', string $style = ''): string
    {
        return (new html_base)->th($this->name_link($back, $style));
    }

    /**
     * @return string the html code for a table row with the word
     */
    function tr(): string
    {
        return (new html_base())->tr($this->td());
    }

    /**
     * @param string $back the back trace url for the undo functionality
     * @param string $style the CSS style that should be used
     * @returns string the word as a table cell
     */
    function td(string $back = '', string $style = '', int $intent = 0): string
    {
        $cell_text = $this->name_link($back, $style);
        return (new html_base)->td($cell_text, '', $intent);
    }


    /*
     * change action
     */

    /**
     * @returns string html code to display a single word in a column and allow to delete it
     */
    function dsp_del(): string
    {
        $html = new html_base();
        $name = $this->td();
        $btn = $html->td($this->btn_del());
        return $html->tr($name . $btn);
    }

    /**
     * allow the user to unlink a word
     */
    function dsp_unlink(int $link_id): string
    {
        $html = new html_base();
        $name = $this->td();
        $btn = $html->td($this->btn_unlink($link_id));
        return $html->tr($name . $btn);
    }


    /*
     * log
     */

    /**
     * @param back_trace $back the last changes to allow undo actions by the user
     * @return string with the HTML code to show the last changes of the view of this word
     */
    function log_view(back_trace $back): string
    {
        $log_dsp = new change_log_named();
        return '';
    }


    /*
     * type
     */

    /**
     * repeating of the backend functions in the frontend to enable filtering in the frontend and reduce the traffic
     * repeated in triple, because a triple can have it's own type
     * kind of repeated in phrase to use hierarchies
     *
     * @param string $type the ENUM string of the fixed type
     * @returns bool true if the word has the given type
     * TODO Switch to php 8.1 and real ENUM
     */
    function is_type(string $type): bool
    {
        global $phr_typ_cac;
        $result = false;
        if ($this->type_id() != Null) {
            if ($this->type_id() == $phr_typ_cac->id($type)) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * @return bool true if the word has the type "time" e.g. "2022 (year)"
     */
    function is_time(): bool
    {
        return $this->is_type(phrase_type::TIME);
    }

    /**
     * @return bool true if the word has the type "time" e.g. "monthly"
     */
    function is_time_jump(): bool
    {
        return $this->is_type(phrase_type::TIME_JUMP);
    }

    /**
     * @return bool true if the word has the type "measure" (e.g. "meter" or "CHF")
     * in case of a division, these words are excluded from the result
     * in case of add, it is checked that the added value does not have a different measure
     */
    function is_measure(): bool
    {
        return $this->is_type(phrase_type::MEASURE);
    }

    /**
     * @return bool true if the word has the type "scaling" (e.g. "million", "million" or "one"; "one" is a hidden scaling type)
     */
    function is_scaling(): bool
    {
        $result = false;
        if ($this->is_type(phrase_type::SCALING)
            or $this->is_type(phrase_type::SCALING_HIDDEN)) {
            $result = true;
        }
        return $result;
    }

    /**
     * @return bool true if the word has the type "scaling_percent" (e.g. "percent")
     */
    function is_percent(): bool
    {
        return $this->is_type(phrase_type::PERCENT);
    }

    /**
     * @return bool true if the word is normally not shown to the user e.g. scaling of one is assumed
     */
    function is_hidden(): bool
    {
        $result = false;
        if ($this->is_type(phrase_type::SCALING_HIDDEN)) {
            $result = true;
        }
        return $result;
    }

    /*
     * TODO deprecate the following functions
     */


    /*
     * views
     */

    /**
     * display a word as the view header
     * @param phrase|null $is_part_of the word group as a hint to the user
     *        e.g. City Zurich because in many cases if just the word Zurich is given the assumption is,
     *             that the Zurich (City) is the phrase to select
     * @returns string the HTML code to display a word
     */
    function header(?phrase $is_part_of = null): string
    {
        $html = new html_base();

        $result = '';

        if ($this->id() <= 0) {
            $result .= 'no word selected';
        } else {
            // load the word parameters if not yet done
            if ($this->name == "") {
                log_err('Name for word with id ' . $this->id() . ' is empty', 'word_dsp->dsp_header');
            }

            //$default_view_id = cl(DBL_VIEW_WORD);
            $title = '';
            if ($is_part_of != null) {
                if ($is_part_of->name() <> '' and $is_part_of->name() <> 'not set') {
                    $url = $html->url(rest_ctrl::VIEW, $is_part_of->id(), '', api::URL_VAR_WORDS);
                    $title .= ' (' . $html->ref($url, $is_part_of->name()) . ')';
                }
            }
            $url = $html->url(rest_ctrl::WORD . rest_ctrl::UPDATE, $this->id(), $this->id());
            $title .= $html->ref($url, $html->span($this->name(), styles::STYLE_GLYPH), 'Rename word');
            $result .= $html->dsp_text_h1($title);
        }

        return $result;
    }

    /*
     * TODO to be replaced by a system view
     */

    /**
     * HTML code to edit all word fields
     * @param string $dsp_graph the html code of the related phrases
     * @param string $dsp_log the html code of the change log
     * @param string $dsp_frm the html code of the linked formulas
     * @param string $dsp_type the html code of the type selector formulas
     * @param string $back the html code to be opened in case of a back action
     * @return string the html code to display the edit page
     */
    function form_edit(string $dsp_graph, string $dsp_log, string $dsp_frm, string $dsp_type, string $back = ''): string
    {
        $html = new html_base();
        $result = '';

        if ($this->id() > 0) {
            $header = $html->text_h2('Change "' . $this->name . '"');
            $hidden_fields = $html->form_hidden("id", $this->id());
            $hidden_fields .= $html->form_hidden("back", $back);
            $hidden_fields .= $html->form_hidden("confirm", '1');
            $detail_fields = $dsp_frm;
            $detail_fields .= $html->form_text("plural", $this->plural());
            $detail_fields .= $html->form_text("description", $this->description());
            $detail_fields .= $dsp_type;
            $detail_row = $html->fr($detail_fields) . '<br>';
            $result = $header
                . $html->form(views::WORD_EDIT, $hidden_fields . $detail_row)
                . '<br>' . $dsp_graph;
        }

        $result .= $dsp_log;

        return $result;
    }


    /**
     * @return string HTML code to edit all word fields
     */
    function dsp_add(int $wrd_id, int $wrd_to, int $vrb_id, $back): string
    {
        log_debug('word_dsp->dsp_add ' . $this->dsp_id() . ' or link the existing word with id ' . $wrd_id . ' to ' . $wrd_to . ' by verb ' . $vrb_id . ' (called by ' . $back . ')');
        $result = '';
        $html = new html_base();

        $form = "word_add";
        $result .= $html->dsp_text_h2('Add a new word');
        $result .= $html->dsp_form_start($form);
        $result .= $html->dsp_form_hidden("back", $back);
        $result .= $html->dsp_form_hidden("confirm", '1');
        $result .= '<div class="form-row">';
        $result .= $html->dsp_form_text("word_name", $this->name, "Name:", view_styles::COL_SM_4);
        $result .= $this->dsp_type_selector($form, view_styles::COL_SM_4);
        $result .= $this->selector_add($wrd_id, $form, "form-row") . ' ';
        $result .= '</div>';
        $result .= 'which ';
        $result .= '<div class="form-row">';
        $result .= $this->selector_link($vrb_id, $form, $back);
        $result .= $this->selector_word($wrd_to, 0, $form);
        $result .= '</div>';
        $result .= $html->dsp_form_end('', $back);

        log_debug('word_dsp->dsp_add ... done');
        return $result;
    }

    /**
     * HTML code to edit all word fields
     */
    function dsp_edit(string $back = ''): string
    {
        $cfg = new config();
        $row_limit = $cfg->get_by_names([words::ROW, words::LIMIT]);
        $html = new html_base();
        $phr_lst_up = $this->parents();
        $phr_lst_down = $this->children();
        $dsp_graph = $phr_lst_up->dsp_graph($this->phrase(), $back);
        $dsp_graph .= $phr_lst_down->dsp_graph($this->phrase(), $back);
        $wrd_dsp = $this;
        // collect the display code for the user changes
        $dsp_log = '';
        $changes = $this->dsp_hist(1, $row_limit, '', $back);
        if (trim($changes) <> "") {
            $dsp_log .= $html->dsp_text_h3("Latest changes related to this word", "change_hist");
            $dsp_log .= $changes;
        }
        $changes = $this->dsp_hist_links(0, $row_limit, '', $back);
        if (trim($changes) <> "") {
            $dsp_log .= $html->dsp_text_h3("Latest link changes related to this word", "change_hist");
            $dsp_log .= $changes;
        }
        return $wrd_dsp->form_edit(
            $dsp_graph,
            $dsp_log,
            $this->dsp_formula($back),
            $this->dsp_type_selector(views::WORD_EDIT, $back),
            $back);
    }

    /*
     * to review
     */

    function dsp_graph(foaf_direction $direction, verb_list $link_types, string $back = ''): string
    {
        return $this->phrase()->dsp_graph($direction, $link_types, $back);
    }

    /**
     * returns the html code to select a word link type
     * database link must be open
     * TODO: similar to verb->dsp_selector maybe combine???
     */
    function selector_link($id, $form, $back): string
    {
        /*
        log_debug('verb id ' . $id);
        global $db_con;

        $result = '';

        $sql_name = "";
        if ($db_con->get_type() == sql_db::POSTGRES) {
            $sql_name = "CASE WHEN (name_reverse  <> '' IS NOT TRUE AND name_reverse <> verb_name) THEN CONCAT(verb_name, ' (', name_reverse, ')') ELSE verb_name END AS name";
        } elseif ($db_con->get_type() == sql_db::MYSQL) {
            $sql_name = "IF (name_reverse <> '' AND name_reverse <> verb_name, CONCAT(verb_name, ' (', name_reverse, ')'), verb_name) AS name";
        } else {
            log_err('Unknown db type ' . $db_con->get_type());
        }
        $sql_avoid_code_check_prefix = "SELECT";
        $sql = $sql_avoid_code_check_prefix . " * FROM (
            SELECT verb_id AS id, 
                   " . $sql_name . ",
                   words
              FROM verbs 
      UNION SELECT verb_id * -1 AS id, 
                   CONCAT(name_reverse, ' (', verb_name, ')') AS name,
                   words
              FROM verbs 
             WHERE name_reverse <> '' 
               AND name_reverse <> verb_name) AS links
          ORDER BY words DESC, name;";
        $sel = new html_selector;
        $sel->form = $form;
        $sel->name = 'verb';
        $sel->sql = $sql;
        $sel->selected = $id;
        $sel->dummy_text = '';
        */
        global $usr;
        global $html_verbs;
        // TODO add $id to the parameters
        $result = $html_verbs->selector($form);

        if ($usr->is_admin()) {
            // admin users should always have the possibility to create a new link type
            $result .= \html\btn_add('add new link type', '/http/verb_add.php?back=' . $back);
        }

        return $result;
    }

    /**
     * to select an existing word to be added
     */
    private function selector_add($id, $form, $bs_class): string
    {
        $pattern = '';
        $phr_lst = new word_list();
        $phr_lst->load_like($pattern);
        $field_name = 'add';
        $label = "Word:";
        //$sel->bs_class = $bs_class;
        //$sel->dummy_text = '... or select an existing word to link it';
        return $phr_lst->selector($form, $id, $field_name, $label, '');
    }

    /*
     * select
     */

    /**
     * TODO review
     *
     * select a phrase based on a given context
     *
     * @param string $name the unique name inside the form for this selector
     * @param string $form the name of the html form
     * @param string $label the text show to the user
     * @param string $col_class the formatting code to adjust the formatting
     * @param int $selected the id of the preselected phrase
     * @param string $pattern the pattern to filter the phrases
     * @param phrase|null $phr the context to select the phrases, which is until now just the phrase
     * @return string the html code to select a phrase
     */
    public function phrase_selector_old(
        string      $name,
        string      $form,
        string      $label = '',
        string      $col_class = '',
        int         $selected = 0,
        string      $pattern = '',
        ?phrase $phr = null
    ): string
    {
        $result = '';
        $phr_lst = new phrase_list();
        if ($pattern != '') {
            $phr_lst->load_like($pattern);
            $result = $phr_lst->selector($form, $selected, $name, $label, view_styles::COL_SM_4, html_selector::TYPE_DATALIST);
        } else {
            $result = $this->name();
        }
        return $result;
    }

    /**
     * @returns string the html code to select a word
     */
    function selector_word(int $id, int $pos, string $form_name): string
    {
        $pattern = '';
        $phr_lst = new word_list();
        $phr_lst->load_like($pattern);

        if ($pos > 0) {
            $field_name = "word" . $pos;
        } else {
            $field_name = "word";
        }
        return $phr_lst->selector($form_name, $id, $field_name, '');
    }

    /**
     * display the history of a word
     * maybe move this to a new object user_log_display
     * because this is very similar to a value linked function
     */
    function dsp_hist(int $page = 1, int $size = 20, string $call = '', string $back = ''): string
    {
        $log_dsp = new user_log_display();
        return $log_dsp->dsp_hist(word::class, $this->id(), $size, $page, '', null);
    }

    /**
     * display the history of a word
     */
    function dsp_hist_links($page, $size, $call, $back): string
    {
        log_debug($this->id() . ",size" . $size . ",b" . $size);
        $result = ''; // reset the html code var

        $log_dsp = new user_log_display();
        $log_dsp->id = $this->id();
        $log_dsp->type = word::class;
        $log_dsp->page = $page;
        $log_dsp->size = $size;
        $log_dsp->call = $call;
        $log_dsp->back = $back;
        $result .= $log_dsp->dsp_hist_links();

        log_debug('done');
        return $result;
    }

    function dsp_formula(string $back = ''): string
    {
        global $phr_typ_cac;
        $html = new html_base();

        $result = '';
        if ($this->type_id() == $phr_typ_cac->id(phrase_type::FORMULA_LINK)) {
            $result .= $html->dsp_form_hidden("name", $this->name);
            $result .= '  to change the name of "' . $this->name . '" rename the ';
            $frm = new formula();
            $frm->load_by_name($this->name());
            $result .= $frm->name_link($back);
            $result .= '.<br> ';
        } else {
            $result .= $html->dsp_form_text("name", $this->name, "Name:", view_styles::COL_SM_4);
        }
        return $result;
    }

    /*
     * internal
     */

    private function has_parent(): bool
    {
        if ($this->parent() == null) {
            return false;
        } else {
            return true;
        }
    }

}
