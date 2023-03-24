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

namespace html;

include_once API_SANDBOX_PATH . 'combine_object.php';
include_once API_PHRASE_PATH . 'phrase.php';
include_once WEB_WORD_PATH . 'word.php';
include_once WEB_WORD_PATH . 'triple.php';

use api\combine_object_api;
use api\phrase_api;
use controller\controller;

class phrase_dsp
{

    /*
     * object vars
     */

    // the word or triple object
    private word_dsp|triple_dsp $obj;


    /*
     * construct and map
     */

    function __construct(word_dsp|triple_dsp $phr_obj)
    {
        $this->set_obj($phr_obj);
    }


    /*
     * set and get
     */

    function set_from_json(string $json_api_msg): void
    {
        $json_array = json_decode($json_api_msg);
        if ($json_array[combine_object_api::FLD_CLASS] == phrase_api::CLASS_WORD) {
            $fv_dsp = new word_dsp();
            $fv_dsp->set_from_json_array($json_array);
            $this->set_obj($fv_dsp);
        } elseif ($json_array[combine_object_api::FLD_CLASS] == phrase_api::CLASS_TRIPLE) {
            $fv_dsp = new triple_dsp();
            $fv_dsp->set_from_json_array($json_array);
            $this->set_obj($fv_dsp);
        } else {
            log_err('Json class ' . $json_array[combine_object_api::FLD_CLASS] . ' not expected for a phrase');
        }
    }

    function set_obj(word_dsp|triple_dsp $obj): void
    {
        $this->obj = $obj;
    }

    function obj(): word_dsp|triple_dsp
    {
        return $this->obj;
    }

    /**
     * return the phrase id based on the word or triple id
     * must have the same logic as the database view and the backend
     */
    function id(): int
    {
        if ($this->is_word()) {
            return $this->obj_id();
        } else {
            return $this->obj_id() * -1;
        }
    }

    function obj_id(): int
    {
        return $this->obj()->id();
    }

    function set_name(?string $name): void
    {
        $this->obj()->set_name($name);
    }

    function name(): ?string
    {
        return $this->obj()->name();
    }

    function set_description(?string $description): void
    {
        $this->obj()->description = $description;
    }

    function description(): ?string
    {
        return $this->obj()->description;
    }

    function set_type(?int $type_id): void
    {
        $this->obj()->set_type_id($type_id);
    }

    function type(): ?int
    {
        return $this->obj()->type_id();
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
        $vars[controller::API_FLD_ID] = $this->id();
        $vars[controller::API_FLD_NAME] = $this->name();
        $vars[controller::API_FLD_DESCRIPTION] = $this->description();
        return $vars;
    }


    /*
     * classifications
     */

    /**
     * @return bool true if this phrase is a word or supposed to be a word
     */
    function is_word(): bool
    {
        if ($this->obj::class == word_dsp::class) {
            return true;
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
    function dsp(): string
    {
        return $this->obj()->dsp();
    }

    /**
     * @returns string the html code to display the phrase with reference links
     */
    function dsp_link(): string
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
        $result .= btn_del("unlink word", "/http/link_del.php?id=" . $link_id . "&back=" . $this->id());
        $result .= '    </td>' . "\n";

        return $result;
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
        $result .= $sel->display();

        return $result;
    }

}
