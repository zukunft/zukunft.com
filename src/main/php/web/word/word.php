<?php

/*

    web/word/word.php - the extension of the word API objects to create word base html code
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

include_once WEB_SANDBOX_PATH . 'sandbox_typed.php';
include_once WEB_PHRASE_PATH . 'phrase.php';
include_once WEB_HTML_PATH . 'html_base.php';
include_once API_PHRASE_PATH . 'phrase.php';

use cfg\phrase_type;
use html\api;
use html\button;
use html\log\change_log_named as change_log_named_dsp;
use html\html_base;
use html\html_selector;
use html\msg;
use html\phrase\phrase as phrase_dsp;
use html\phrase\term as term_dsp;
use html\sandbox_typed_dsp;
use html\system\back_trace;

class word extends sandbox_typed_dsp
{

    // default view settings
    const TIME_MIN_COLS = 3; // minimum number of same time type word to display in a table e.g. if at least 3 years exist use a table to display
    const TIME_MAX_COLS = 10; // maximum number of same time type word to display in a table e.g. if more the 10 years exist, by default show only the lst 10 years
    const TIME_FUT_PCT = 20; // the default number of future outlook e.g. if there are 10 years of hist and 3 years of outlook display 8 years of hist and 2 years outlook

    // the form names to change the word
    const FORM_ADD = 'word_add';
    const FORM_EDIT = 'word_edit';
    const FORM_DEL = 'word_del';

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
     * set and get
     */

    /**
     * create the word object and fill it base on the json message
     * @param array $json_array an api single object json message
     * @return void
     */
    function set_obj_from_json_array(array $json_array): void
    {
        $wrd = new word();
        $wrd->set_from_json_array($json_array);
    }

    /**
     * set the vars of this object bases on the api json array
     * public because it is reused e.g. by the phrase group display object
     * @param array $json_array an api json message
     * @return void
     */
    function set_from_json_array(array $json_array): void
    {
        parent::set_from_json_array($json_array);
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
    }

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
     * interface
     */

    /**
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
        return array_filter($vars, fn($value) => !is_null($value));
    }


    /*
     * info
     */

    function has_parent(): bool
    {
        if ($this->parent() == null) {
            return false;
        } else {
            return true;
        }
    }


    /*
     * base elements
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
        $url = $html->url(api::VIEW, $this->id, $back, api::PAR_VIEW_WORDS);
        return $html->ref($url, $this->name(), $this->description(), $style);
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

        if ($this->id <= 0) {
            $result .= 'no word selected';
        } else {
            // load the word parameters if not yet done
            if ($this->name == "") {
                log_err('Name for word with id ' . $this->id . ' is empty', 'word_dsp->dsp_header');
            }

            //$default_view_id = cl(DBL_VIEW_WORD);
            $title = '';
            if ($is_part_of != null) {
                if ($is_part_of->name() <> '' and $is_part_of->name() <> 'not set') {
                    $url = $html->url(api::VIEW, $is_part_of->id(), '', api::PAR_VIEW_WORDS);
                    $title .= ' (' . $html->ref($url, $is_part_of->name()) . ')';
                }
            }
            $url = $html->url(api::WORD . api::UPDATE, $this->id, $this->id);
            $title .= $html->ref($url, $html->span($this->name(), api::STYLE_GLYPH), 'Rename word');
            $result .= $html->dsp_text_h1($title);
        }

        return $result;
    }


    /*
     * select
     */

    /**
     * @param string $script
     * @param string $bs_class
     * @return string
     */
    private function type_selector(string $script, string $bs_class): string
    {
        $result = '';
        $sel = new html_selector;
        $sel->form = $script;
        $sel->name = 'type';
        $sel->label = "Word type:";
        $sel->bs_class = $bs_class;
        $sel->sql = sql_lst("word_type");
        $sel->selected = $this->type_id();
        $sel->dummy_text = '';
        $result .= $sel->display_old();
        return $result;
    }

    function dsp_type_selector(string $script, string $back = ''): string
    {
        global $phrase_types;
        $result = '';
        if ($phrase_types->code_id($this->type_id()) == phrase_type::FORMULA_LINK) {
            $result .= ' type: ' . $phrase_types->name($this->type_id());
        } else {
            $result .= $this->type_selector($script, "col-sm-4");
        }
        return $result;
    }


    /*
     * change forms
     */

    /**
     * HTML code to add a word with all fields
     * @param string $back the html code to be opened in case of a back action
     * @return string the html code to display the add page
     */
    function form_add(string $back = ''): string
    {
        $html = new html_base();
        $ui_msg = new msg();

        $header = $html->text_h2($ui_msg->txt(msg::FORM_WORD_ADD_TITLE));
        $hidden_fields = $html->form_hidden("back", $back);
        $hidden_fields .= $html->form_hidden("confirm", '1');
        $detail_fields = $html->form_text("word_name", $this->plural(), $ui_msg->txt(msg::FORM_WORD_FLD_NAME));
        $detail_row = $html->fr($detail_fields) . '<br>';

        // TODO complete

        return $header . $html->form(self::FORM_ADD, $hidden_fields . $detail_row);
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

        if ($this->id > 0) {
            $header = $html->text_h2('Change "' . $this->name . '"');
            $hidden_fields = $html->form_hidden("id", $this->id);
            $hidden_fields .= $html->form_hidden("back", $back);
            $hidden_fields .= $html->form_hidden("confirm", '1');
            $detail_fields = $dsp_frm;
            $detail_fields .= $html->form_text("plural", $this->plural());
            $detail_fields .= $html->form_text("description", $this->description());
            $detail_fields .= $dsp_type;
            $detail_row = $html->fr($detail_fields) . '<br>';
            $result = $header
                . $html->form(self::FORM_EDIT, $hidden_fields . $detail_row)
                . '<br>' . $dsp_graph;
        }

        $result .= $dsp_log;

        return $result;
    }

    /**
     * HTML code to delete or exclude a word
     * @param string $back the html code to be opened in case of a back action
     * @return string the html code to display the delete page
     */
    function form_del(string $back = ''): string
    {
        $html = new html_base();

        $header = $html->text_h2('Delete "' . $this->name . '"');
        $hidden_fields = $html->form_hidden("id", $this->id);
        $hidden_fields .= $html->form_hidden("back", $back);
        $hidden_fields .= $html->form_hidden("confirm", '1');
        $detail_row = $this->btn_del() . '<br>';

        // TODO complete

        return $header . $html->form(self::FORM_DEL, $hidden_fields . $detail_row);
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
     * buttons
     */

    /**
     * @returns string the html code to display a bottom to exclude the word for the current user
     *                 or if no one uses the word delete the complete word
     */
    function btn_del(): string
    {
        $url = (new html_base())->url(api::WORD . api::REMOVE, $this->id, $this->id);
        return (new button((new msg())->txt(msg::WORD_DEL), $url))->del();
    }

    /**
     * @returns string the html code to display a bottom to edit the word link in a table cell
     */
    function btn_unlink(int $link_id): string
    {
        $url = (new html_base())->url(api::LINK . api::REMOVE, $link_id, $this->id);
        return (new button((new msg())->txt(msg::WORD_UNLINK), $url))->del();
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

}
