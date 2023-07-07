<?php

/*

    web/phrase.php - to create the html code to display a word or triple
    --------------


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

    Copyright (c) 1995-2023 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace html\phrase;

include_once WEB_SANDBOX_PATH . 'combine_named.php';
include_once API_SANDBOX_PATH . 'combine_object.php';
include_once API_PHRASE_PATH . 'phrase.php';
include_once WEB_WORD_PATH . 'word.php';
include_once WEB_WORD_PATH . 'triple.php';

use api\combine_object_api;
use api\phrase_api;
use api\word_api;
use api\api;
use html\api as api_dsp;
use html\button;
use html\combine_named_dsp;
use html\html_base;
use html\html_selector;
use html\msg;
use html\word\word as word_dsp;
use html\word\triple as triple_dsp;
use controller\controller;

class phrase extends combine_named_dsp
{

    /*
     * set and get
     */

    /**
     * set the vars of this phrase html display object bases on the api message
     * @param string $json_api_msg an api json message as a string
     * @return void
     */
    function set_from_json(string $json_api_msg): void
    {
        $this->set_from_json_array(json_decode($json_api_msg, true));
    }

    /**
     * set the vars of this phrase frontend object bases on the api json array
     * @param array $json_array an api json message
     * @return void
     */
    function set_from_json_array(array $json_array): void
    {
        if (array_key_exists(combine_object_api::FLD_CLASS, $json_array)) {
            if ($json_array[combine_object_api::FLD_CLASS] == phrase_api::CLASS_WORD) {
                $wrd_dsp = new word_dsp();
                $wrd_dsp->set_from_json_array($json_array);
                $this->set_obj($wrd_dsp);
            } elseif ($json_array[combine_object_api::FLD_CLASS] == phrase_api::CLASS_TRIPLE) {
                $trp_dsp = new triple_dsp();
                $trp_dsp->set_from_json_array($json_array);
                $this->set_obj($trp_dsp);
                // switch the phrase id to the object id
                $this->set_id($trp_dsp->id());
            } else {
                log_err('Json class ' . $json_array[combine_object_api::FLD_CLASS] . ' not expected for a phrase');
            }
        } else {
            log_err('Json class missing, but expected for a phrase');
        }
    }

    function set_phrase_obj(word_dsp|triple_dsp|null $obj = null): void
    {
        $this->obj = $obj;
    }

    /**
     * set the object id based on the given phrase id
     * must have the same logic as the database view and the api
     * @param int $id the phrase id that is converted to the object id
     * @return void
     */
    function set_id(int $id): void
    {
        $this->set_obj_id(abs($id));
    }

    /**
     * @return int the id of the phrase generated from the object id
     * e.g 1 for a word with id 1, -1 for a triple with id 1
     */
    function id(): int
    {
        if ($this->is_word()) {
            return $this->obj_id();
        } else {
            return $this->obj_id() * -1;
        }
    }

    /**
     * @return int the id of the word or triple
     * e.g 1 for a word with id 1, 1 for a triple with id 1
     */
    function obj_id(): int
    {
        return $this->obj()?->id();
    }


    /*
     * interface
     */

    /**
     * @return array the json message array to send the updated data to the backend
     */
    function api_array(): array
    {
        $vars = array();
        if ($this->is_word()) {
            $vars[combine_object_api::FLD_CLASS] = phrase_api::CLASS_WORD;
        } else {
            $vars[combine_object_api::FLD_CLASS] = phrase_api::CLASS_TRIPLE;
        }
        $vars[api::FLD_ID] = $this->obj_id();
        $vars[api::FLD_NAME] = $this->name();
        $vars[api::FLD_DESCRIPTION] = $this->description();
        $vars[api::FLD_TYPE] = $this->type_id();
        return array_filter($vars, fn($value) => !is_null($value) && $value !== '');
    }


    /*
     * classifications
     */

    /**
     * @return bool true if this phrase is a word or supposed to be a word
     */
    function is_word(): bool
    {
        if ($this->obj() != null) {
            if ($this->obj()::class == word_dsp::class) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }


    /*
     * info
     */

    /**
     * @return bool true if this phrase is of type percent
     */
    function is_percent(): bool
    {
        return $this->obj()->is_percent();
    }


    /*
     * display
     */

    /**
     * @returns string the html code to display with mouse over that shows the description
     */
    function display(): string
    {
        return $this->obj()->display();
    }

    /**
     * @returns string the html code to display the phrase with reference links
     */
    function display_linked(): string
    {
        return $this->obj()->name();
    }

    /**
     * simply to display a single word in a table cell
     */
    function dsp_tbl_cell(int $intent): string
    {
        $result = '';
        if ($this->is_word()) {
            $wrd = $this->obj();
            $result .= $wrd->td('', '', $intent);
        }
        return $result;
    }

    /**
     * @returns string the html code that allows the user to unlink this phrase
     */
    function dsp_unlink(int $link_id): string
    {
        $result = '    <td>' . "\n";
        $result .= $this->btn_del();
        $result .= '    </td>' . "\n";

        return $result;
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
        if ($this->is_word()) {
            $obj_name = api_dsp::WORD;
            $ui_msg_id = msg::WORD_DEL;
        } else {
            $obj_name = api_dsp::TRIPLE;
            $ui_msg_id = msg::TRIPLE_DEL;
        }
        $url = (new html_base())->url($obj_name . api_dsp::REMOVE, $this->id(), $this->id());
        return (new button($url))->del($ui_msg_id);
    }

    //
    //
    // $type
    /**
     * create a selector that contains the words and triples
     * if one form contains more than one selector, $pos is used for identification
     *
     * @param phrase_api $type is a word to preselect the list to only those phrases matching this type
     * @param string $form_name
     * @param int $pos
     * @param string $class
     * @param string $back
     * @return string
     */
    function dsp_selector(phrase_api $type, string $form_name, int $pos, string $class, string $back = ''): string
    {
        $result = '';

        if ($pos > 0) {
            $field_name = "phrase" . $pos;
        } else {
            $field_name = "phrase";
        }
        $sel = new html_selector;
        $sel->form = $form_name;
        $sel->name = $field_name;
        if ($form_name == "value_add" or $form_name == "value_edit") {
            $sel->label = "";
        } else {
            if ($pos == 1) {
                $sel->label = "From:";
            } elseif ($pos == 2) {
                $sel->label = "To:";
            } else {
                $sel->label = "Word:";
            }
        }
        $sel->bs_class = $class;
        $sel->sql = $this->sql_list($type);
        $sel->selected = $this->id();
        $sel->dummy_text = '... please select';
        $result .= $sel->display_old();

        return $result;
    }

}
