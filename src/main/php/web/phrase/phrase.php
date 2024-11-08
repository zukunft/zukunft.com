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

include_once SANDBOX_PATH . 'combine_named.php';
include_once API_SANDBOX_PATH . 'combine_object.php';
include_once API_PHRASE_PATH . 'phrase.php';
include_once WORD_PATH . 'word.php';
include_once WORD_PATH . 'triple.php';

use shared\api;
use api\phrase\phrase as phrase_api;
use api\sandbox\combine_object as combine_object_api;
use cfg\verb_list;
use html\button;
use html\html_base;
use html\phrase\phrase_list as phrase_list_dsp;
use html\rest_ctrl as api_dsp;
use html\sandbox\combine_named as combine_named_dsp;
use html\system\messages;
use html\user\user_message;
use html\word\triple as triple_dsp;
use html\word\word as word_dsp;
use shared\enum\foaf_direction;

class phrase extends combine_named_dsp
{

    /*
     * set and get
     */

    /**
     * set the vars of this phrase html display object bases on the api message
     * @param string $json_api_msg an api json message as a string
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function set_from_json(string $json_api_msg): user_message
    {
        return $this->set_from_json_array(json_decode($json_api_msg, true));
    }

    /**
     * set the vars of this phrase frontend object bases on the api json array
     * @param array $json_array an api json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function set_from_json_array(array $json_array): user_message
    {
        $usr_msg = new user_message();
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
                $usr_msg->add_err('Json class ' . $json_array[combine_object_api::FLD_CLASS] . ' not expected for a phrase');
            }
        } else {
            $usr_msg->add_err('Json class missing, but expected for a phrase');
        }
        return $usr_msg;
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
     * @return int|string|null the id of the word or triple
     * e.g 1 for a word with id 1, 1 for a triple with id 1
     */
    function obj_id(): int|string|null
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
            $trp = $this->obj();
            $vars[api::FLD_FROM] = $trp->from()->id();
            $vars[api::FLD_VERB] = $trp->verb()->id();
            $vars[api::FLD_TO] = $trp->to()->id();
        }
        $vars[api::FLD_ID] = $this->obj_id();
        $vars[api::FLD_NAME] = $this->name();
        $vars[api::FLD_DESCRIPTION] = $this->description();
        $vars[api::FLD_TYPE] = $this->type_id();
        $vars[api::FLD_PLURAL] = $this->plural();
        // TODO add exclude field and move to a parent object?
        if ($this->obj()?->share_id != null) {
            $vars[api::FLD_SHARE] = $this->obj()?->share_id;
        }
        if ($this->obj()?->protection_id != null) {
            $vars[api::FLD_PROTECTION] = $this->obj()?->protection_id;
        }
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
            $ui_msg_id = messages::WORD_DEL;
        } else {
            $obj_name = api_dsp::TRIPLE;
            $ui_msg_id = messages::TRIPLE_DEL;
        }
        $url = (new html_base())->url($obj_name . api_dsp::REMOVE, $this->id(), $this->id());
        return (new button($url))->del($ui_msg_id);
    }

    /*
     * to review
     */

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
        // TODO include pattern in the call
        $pattern = '';
        $phr_lst = new phrase_list();
        $phr_lst->load_like($pattern);

        if ($pos > 0) {
            $field_name = "phrase" . $pos;
        } else {
            $field_name = "phrase";
        }
        $label = "";
        if ($form_name != "value_add" and $form_name != "value_edit") {
            if ($pos == 1) {
                $label = "From:";
            } elseif ($pos == 2) {
                $label = "To:";
            } else {
                $label = "Word:";
            }
        }
        // TODO activate Prio 3
        // $sel->bs_class = $class;

        return $phr_lst->selector($field_name, $form_name, $label, '', $this->id());
    }

    function dsp_graph(foaf_direction $direction, ?verb_list $link_types = null, string $back = ''): string
    {
        $phr_lst = new phrase_list_dsp();
        if ($phr_lst->load_related($this, $direction, $link_types)) {
            return $phr_lst->dsp_graph($this, $back);
        } else {
            return '';
        }
    }

    /**
     * html code for a button to add a new phrase similar to this phrase
     **/
    function btn_add($back): string
    {
        $wrd = $this->main_word();
        return $wrd->btn_add($back);
    }

}
