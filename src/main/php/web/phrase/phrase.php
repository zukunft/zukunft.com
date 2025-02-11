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
include_once WEB_HTML_PATH . 'button.php';
include_once WEB_HTML_PATH . 'html_base.php';
include_once WEB_HTML_PATH . 'rest_ctrl.php';
include_once WEB_PHRASE_PATH . 'phrase_list.php';
include_once WEB_SYSTEM_PATH . 'messages.php';
include_once WEB_USER_PATH . 'user_message.php';
include_once WEB_VERB_PATH . 'verb_list.php';
include_once WEB_WORD_PATH . 'word.php';
//include_once WEB_WORD_PATH . 'word_list.php';
include_once WEB_WORD_PATH . 'triple.php';
include_once SHARED_ENUM_PATH . 'foaf_direction.php';
include_once SHARED_PATH . 'json_fields.php';

use html\button;
use html\html_base;
use html\rest_ctrl as api_dsp;
use html\sandbox\combine_named;
use html\system\messages;
use html\user\user_message;
use html\verb\verb_list;
use html\word\triple;
use html\word\word;
use html\word\word_list;
use shared\enum\foaf_direction;
use shared\json_fields;

class phrase extends combine_named
{

    /*
     * set and get
     */

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
     * api
     */

    /**
     * @return array the json message array to send the updated data to the backend
     */
    function api_array(): array
    {
        $vars = array();
        if ($this->is_word()) {
            $vars[json_fields::OBJECT_CLASS] = json_fields::CLASS_WORD;
        } else {
            $trp = $this->obj();
            if ($trp != null) {
                $vars[json_fields::OBJECT_CLASS] = json_fields::CLASS_TRIPLE;
                $vars[json_fields::FROM] = $trp->from()->id();
                $vars[json_fields::VERB] = $trp->verb()->id();
                $vars[json_fields::TO] = $trp->to()->id();
            }
        }
        $vars[json_fields::ID] = $this->obj_id();
        $vars[json_fields::NAME] = $this->name();
        $vars[json_fields::DESCRIPTION] = $this->description();
        $vars[json_fields::TYPE] = $this->type_id();
        $vars[json_fields::PLURAL] = $this->plural();
        // TODO add exclude field and move to a parent object?
        if ($this->obj()?->share_id != null) {
            $vars[json_fields::SHARE] = $this->obj()?->share_id;
        }
        if ($this->obj()?->protection_id != null) {
            $vars[json_fields::PROTECTION] = $this->obj()?->protection_id;
        }
        return array_filter($vars, fn($value) => !is_null($value) && $value !== '');
    }

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
        if (array_key_exists(json_fields::OBJECT_CLASS, $json_array)) {
            if ($json_array[json_fields::OBJECT_CLASS] == json_fields::CLASS_WORD) {
                $wrd_dsp = new word();
                $wrd_dsp->set_from_json_array($json_array);
                $this->set_obj($wrd_dsp);
            } elseif ($json_array[json_fields::OBJECT_CLASS] == json_fields::CLASS_TRIPLE) {
                $trp_dsp = new triple();
                $trp_dsp->set_from_json_array($json_array);
                $this->set_obj($trp_dsp);
                // switch the phrase id to the object id
                $this->set_id($trp_dsp->id());
            } else {
                $usr_msg->add_err('Json class ' . $json_array[json_fields::OBJECT_CLASS] . ' not expected for a phrase');
            }
        } else {
            $usr_msg->add_err('Json class missing, but expected for a phrase');
        }
        return $usr_msg;
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
            if ($this->obj()::class == word::class) {
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
     * base
     */

    /**
     * @returns string the html code to display with mouse over that shows the description
     */
    function name_tip(): string
    {
        return $this->obj()->name_tip();
    }

    /**
     * @returns string the html code to display the phrase with reference links
     */
    function name_link(): string
    {
        return $this->obj()->name_link();
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
     * @param phrase $type is a word to preselect the list to only those phrases matching this type
     * @param string $form_name
     * @param int $pos
     * @param string $class
     * @param string $back
     * @return string
     */
    function dsp_selector(phrase $type, string $form_name, int $pos, string $class, string $back = ''): string
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

        return $phr_lst->selector($form_name, $this->id(), $field_name, $label, '');
    }

    function dsp_graph(foaf_direction $direction, ?verb_list $link_types = null, string $back = ''): string
    {
        $phr_lst = new phrase_list();
        if ($phr_lst->load_related($this, $direction, $link_types)) {
            return $phr_lst->dsp_graph($this, $back);
        } else {
            return '';
        }
    }

    /**
     * @return word the most relevant
     */
    function main_word(): word
    {
        if ($this->is_word()) {
            return $this->obj()->word();
        } else {
            return $this->obj()->main_word();
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

    /**
     * to enable the recursive function in work_link
     * TODO add a list of triple already split to detect endless loops
     */
    function wrd_lst(): word_list
    {
        $wrd_lst = new word_list();
        if (!$this->is_word()) {
            $trp = $this->obj();
            $sub_wrd_lst = $trp->wrd_lst();
            foreach ($sub_wrd_lst->lst() as $wrd) {
                $wrd_lst->add($wrd);
            }
        } else {
            $wrd = $this->obj();
            $wrd_lst->add($wrd);
        }
        return $wrd_lst;
    }

    function dsp_tbl(int $intent = 0): string
    {
        $result = '';
        if ($this != null) {
            if ($this->obj != null) {
                // the function dsp_tbl should exist for words and triples
                $dsp_obj = $this->obj();
                if ($this->is_word() == word::class) {
                    $result = $dsp_obj->td('', '', $intent);
                } else {
                    $result = $dsp_obj->tr('', '', $intent);
                }
            }
        }
        log_debug('for ' . $this->dsp_id());
        return $result;
    }
}
