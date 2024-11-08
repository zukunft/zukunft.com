<?php

/*

    web/word/word.php - create HTML code to display a words based on the api json message
    -----------------

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

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

namespace html\word;

include_once SANDBOX_PATH . 'sandbox_typed.php';
include_once PHRASE_PATH . 'phrase.php';
include_once HTML_PATH . 'html_base.php';
include_once API_PHRASE_PATH . 'phrase.php';
include_once SHARED_TYPES_PATH . 'phrase_type.php';
include_once SHARED_PATH . 'views.php';

use cfg\verb_list;
use html\button;
use html\formula\formula as formula_dsp;
use html\html_base;
use html\html_selector;
use html\log\change_log_named as change_log_named_dsp;
use html\log\user_log_display;
use html\phrase\phrase as phrase_dsp;
use html\phrase\phrase_list as phrase_list_dsp;
use html\phrase\term as term_dsp;
use html\rest_ctrl;
use html\sandbox\config;
use html\sandbox\sandbox_typed;
use html\system\back_trace;
use html\system\messages;
use html\user\user_message;
use shared\api;
use shared\enum\foaf_direction;
use shared\words;
use shared\types\phrase_type AS phrase_type_shared;
use shared\views as view_shared;

class word extends sandbox_typed
{

    // the json field names in the api json message which is supposed to be the same as the var $id
    const FLD_PLURAL = 'plural';
    const FLD_PARENT = 'parent';


    /*
     * object vars
     */

    // the language specific forms
    private ?string $plural = null;

    // the main parent phrase
    private ?phrase_dsp $parent;


    /*
     * api
     */

    /**
     * set the vars of this object bases on the api json array
     * public because it is reused e.g. by the phrase group display object
     * @param array $json_array an api json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function set_from_json_array(array $json_array): user_message
    {
        $usr_msg = parent::set_from_json_array($json_array);
        if (array_key_exists(self::FLD_PLURAL, $json_array)) {
            $this->set_plural($json_array[self::FLD_PLURAL]);
        } else {
            $this->set_plural(null);
        }
        if (array_key_exists(self::FLD_PARENT, $json_array)) {
            $this->set_parent($json_array[self::FLD_PARENT]);
        } else {
            $this->set_parent(null);
        }
        return $usr_msg;
    }

    /**
     * create an api json array for the backend based on this frontend object
     * @return array the json message array to send the updated data to the backend
     * an array is used (instead of a string) to enable combinations of api_array() calls
     */
    function api_array(): array
    {
        $vars = parent::api_array();

        $vars[self::FLD_PLURAL] = $this->plural();
        if ($this->has_parent()) {
            $vars[self::FLD_PARENT] = $this->parent()->api_array();
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

    function set_parent(?phrase_dsp $parent): void
    {
        $this->parent = $parent;
    }

    function parent(): ?phrase_dsp
    {
        return $this->parent;
    }

    /**
     * @param string|null $code_id the code id of the phrase type
     */
    function set_type(?string $code_id): void
    {
        global $phrase_types;
        if ($code_id == null) {
            $this->set_type_id();
        } else {
            $this->set_type_id($phrase_types->id($code_id));
        }
    }


    /*
     * names
     */

    /**
     * @returns string simply the word name, but later with mouse over that shows the description
     */
    function display(): string
    {
        return $this->name;
    }

    /**
     * display a word with a link to the main page for the word
     * @param string|null $back the back trace url for the undo functionality
     * @param string $style the CSS style that should be used
     * @returns string the html code
     */
    function display_linked(?string $back = '', string $style = ''): string
    {
        $html = new html_base();
        $url = $html->url_new(view_shared::MI_WORD, $this->id(), '', $back);
        return $html->ref($url, $this->name(), $this->description(), $style);
    }


    /*
     * buttons
     */

    /**
     * @returns string the html code to display a bottom to create anew word for the current user
     */
    function btn_add(string $back = ''): string
    {
        $html = new html_base();
        $url = $html->url_new(view_shared::MI_WORD_ADD, $this->id(), '', $back);
        $btn = new button($url, $back);
        return $btn->add(messages::WORD_ADD);
    }

    /**
     * @returns string the html code to display a bottom to create anew word for the current user
     */
    function btn_edit(string $back = ''): string
    {
        $html = new html_base();
        $url = $html->url_new(view_shared::MI_WORD_EDIT, $this->id(), '', $back);
        $btn = new button($url, $back);
        return $btn->edit(messages::WORD_EDIT);
    }

    /**
     * @returns string the html code to display a bottom to exclude the word for the current user
     *                 or if no one uses the word delete the complete word
     */
    function btn_del(string $back = ''): string
    {
        $html = new html_base();
        $url = $html->url_new(view_shared::MI_WORD_DEL, $this->id(), '', $back);
        $btn = new button($url, $back);
        return $btn->del(messages::WORD_DEL);
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

    function dsp_type_selector(string $script, string $back = ''): string
    {
        global $phrase_types;
        $result = '';
        if ($phrase_types->code_id($this->type_id()) == phrase_type_shared::FORMULA_LINK) {
            $result .= ' type: ' . $phrase_types->name($this->type_id());
        } else {
            $result .= $this->type_selector($script, html_base::COL_SM_4);
        }
        return $result;
    }

    /**
     * @param string $form_name the name of the html form
     * @param string $bs_class e.g. to define the size of the select field
     * @return string the html code to select the phrase type
     */
    private function type_selector(string $form_name, string $bs_class): string
    {
        global $html_phrase_types;
        return $html_phrase_types->selector($form_name);
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
        return (new html_base)->th($this->display_linked($back, $style));
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
        $cell_text = $this->display_linked($back, $style);
        return (new html_base)->td($cell_text, $intent);
    }


    /*
     * views
     */

    /**
     * display a word as the view header
     * @param phrase_dsp|null $is_part_of the word group as a hint to the user
     *        e.g. City Zurich because in many cases if just the word Zurich is given the assumption is,
     *             that the Zurich (City) is the phrase to select
     * @returns string the HTML code to display a word
     */
    function header(?phrase_dsp $is_part_of = null): string
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
            $title .= $html->ref($url, $html->span($this->name(), rest_ctrl::STYLE_GLYPH), 'Rename word');
            $result .= $html->dsp_text_h1($title);
        }

        return $result;
    }

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
                . $html->form(view_shared::MC_WORD_EDIT, $hidden_fields . $detail_row)
                . '<br>' . $dsp_graph;
        }

        $result .= $dsp_log;

        return $result;
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
     * change log
     */

    /**
     * @param back_trace $back the last changes to allow undo actions by the user
     * @return string with the HTML code to show the last changes of the view of this word
     */
    function log_view(back_trace $back): string
    {
        $log_dsp = new change_log_named_dsp();
        return '';
    }


    /*
     * cast
     */

    /**
     * @returns phrase_dsp the phrase display object base on this word object
     */
    function phrase(): phrase_dsp
    {
        $phr = new phrase_dsp();
        $phr->set_obj($this);
        return $phr;
    }

    /**
     * @returns term_dsp the word object cast into a term object
     */
    function term(): term_dsp
    {
        $trm = new term_dsp();
        $trm->set_obj($this);
        return $trm;
    }


    /*
     * load
     */

    function parents(): phrase_list_dsp
    {
        $lst = new phrase_list_dsp();
        // TODO get the json from the backend
        return $lst;
    }

    function children(): phrase_list_dsp
    {
        $lst = new phrase_list_dsp();
        // TODO get the json from the backend
        return $lst;
    }


    /*
     * type functions
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
        global $phrase_types;
        $result = false;
        if ($this->type_id() != Null) {
            if ($this->type_id() == $phrase_types->id($type)) {
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
        return $this->is_type(phrase_type_shared::TIME);
    }

    /**
     * @return bool true if the word has the type "time" e.g. "monthly"
     */
    function is_time_jump(): bool
    {
        return $this->is_type(phrase_type_shared::TIME_JUMP);
    }

    /**
     * @return bool true if the word has the type "measure" (e.g. "meter" or "CHF")
     * in case of a division, these words are excluded from the result
     * in case of add, it is checked that the added value does not have a different measure
     */
    function is_measure(): bool
    {
        return $this->is_type(phrase_type_shared::MEASURE);
    }

    /**
     * @return bool true if the word has the type "scaling" (e.g. "million", "million" or "one"; "one" is a hidden scaling type)
     */
    function is_scaling(): bool
    {
        $result = false;
        if ($this->is_type(phrase_type_shared::SCALING)
            or $this->is_type(phrase_type_shared::SCALING_HIDDEN)) {
            $result = true;
        }
        return $result;
    }

    /**
     * @return bool true if the word has the type "scaling_percent" (e.g. "percent")
     */
    function is_percent(): bool
    {
        return $this->is_type(phrase_type_shared::PERCENT);
    }

    /**
     * @return bool true if the word is normally not shown to the user e.g. scaling of one is assumed
     */
    function is_hidden(): bool
    {
        $result = false;
        if ($this->is_type(phrase_type_shared::SCALING_HIDDEN)) {
            $result = true;
        }
        return $result;
    }

    /*
     * to be replaced by a system view
     */

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
        $result .= $html->dsp_form_text("word_name", $this->name, "Name:", html_base::COL_SM_4);
        $result .= $this->dsp_type_selector($form, html_base::COL_SM_4);
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
        $row_limit = $cfg->get([words::ROW, words::LIMIT]);
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
            $this->dsp_type_selector(view_shared::MC_WORD_EDIT, $back),
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
        return $phr_lst->selector($field_name, $form, $label, '', $id);
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
     * @param string $form_name the name of the html form
     * @param string $label the text show to the user
     * @param string $col_class the formatting code to adjust the formatting
     * @param int $selected the id of the preselected phrase
     * @param string $pattern the pattern to filter the phrases
     * @param phrase_dsp|null $phr the context to select the phrases, which is until now just the phrase
     * @return string the html code to select a phrase
     */
    protected function phrase_selector(
        string $name,
        string $form_name,
        string $label = '',
        string $col_class = '',
        int $selected = 0,
        string $pattern = '',
        ?phrase_dsp $phr = null
    ): string
    {
        $result = '';
        $phr_lst = new phrase_list_dsp();
        if ($pattern != '') {
            $phr_lst->load_like($pattern);
            $result = $phr_lst->selector($name, $form_name, $label, html_base::COL_SM_4, $selected, html_selector::TYPE_DATALIST);
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
        return $phr_lst->selector($field_name, $form_name, '', $id);
    }

    /**
     * display the history of a word
     * maybe move this to a new object user_log_display
     * because this is very similar to a value linked function
     */
    function dsp_hist(int $page = 1, int $size = 20, string $call = '', string $back = ''): string
    {
        log_debug("word_dsp->dsp_hist for id " . $this->id() . " page " . $size . ", size " . $size . ", call " . $call . ", back " . $back . ".");
        $result = ''; // reset the html code var

        $log_dsp = new user_log_display();
        $log_dsp->id = $this->id();
        $log_dsp->type = word::class;
        $log_dsp->page = $page;
        $log_dsp->size = $size;
        $log_dsp->call = $call;
        $log_dsp->back = $back;
        $result .= $log_dsp->dsp_hist();

        log_debug('done');
        return $result;
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
        global $phrase_types;
        $html = new html_base();

        $result = '';
        if ($this->type_id() == $phrase_types->id(phrase_type_shared::FORMULA_LINK)) {
            $result .= $html->dsp_form_hidden("name", $this->name);
            $result .= '  to change the name of "' . $this->name . '" rename the ';
            $frm = new formula_dsp();
            $frm->load_by_name($this->name());
            $result .= $frm->display_linked($back);
            $result .= '.<br> ';
        } else {
            $result .= $html->dsp_form_text("name", $this->name, "Name:", html_base::COL_SM_4);
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
