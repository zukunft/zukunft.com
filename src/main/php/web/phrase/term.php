<?php

/*

    web/phrase/term.php - to create the html code to display a word, triple, verb or formula
    --------------------


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
include_once API_PHRASE_PATH . 'term.php';
include_once WEB_WORD_PATH . 'word.php';
include_once WEB_WORD_PATH . 'triple.php';
include_once WEB_FORMULA_PATH . 'formula.php';
include_once WEB_VERB_PATH . 'verb.php';
include_once SHARED_PATH . 'json_fields.php';

use shared\api;
use api\phrase\term as term_api;
use api\sandbox\combine_object as combine_object_api;
use html\sandbox\combine_named as combine_named_dsp;
use html\formula\formula as formula_dsp;
use html\user\user_message;
use html\verb\verb as verb_dsp;
use html\word\word as word_dsp;
use html\word\triple as triple_dsp;
use shared\json_fields;

class term extends combine_named_dsp
{


    /*
     * set and get
     */

    /**
     * set the vars of this term html display object bases on the api message
     * @param array $json_array an api json message as a string
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function set_from_json_array(array $json_array): user_message
    {
        $usr_msg = new user_message();
        if ($json_array[json_fields::OBJECT_CLASS] == term_api::CLASS_WORD) {
            $wrd = new word_dsp();
            $wrd->set_from_json_array($json_array);
            $this->set_obj($wrd);
            // unlike the cases below the switch of the term id to the object id not needed for words
        } elseif ($json_array[json_fields::OBJECT_CLASS] == term_api::CLASS_TRIPLE) {
            $trp = new triple_dsp();
            $trp->set_from_json_array($json_array);
            $this->set_obj($trp);
            // TODO check if needed
            //$this->set_id($trp->id());
        } elseif ($json_array[json_fields::OBJECT_CLASS] == term_api::CLASS_VERB) {
            $vrb = new verb_dsp();
            $vrb->set_from_json_array($json_array);
            $this->set_obj($vrb);
            //$this->set_id($vrb->id());
        } elseif ($json_array[json_fields::OBJECT_CLASS] == term_api::CLASS_FORMULA) {
            $frm = new formula_dsp();
            $frm->set_from_json_array($json_array);
            $this->set_obj($frm);
            //$this->set_id($frm->id());
        } else {
            $usr_msg->add_err('Json class ' . $json_array[json_fields::OBJECT_CLASS] . ' not expected for a term');
        }
        return $usr_msg;
    }

    function set_term_obj(word_dsp|triple_dsp|verb_dsp|formula_dsp|null $obj): void
    {
        $this->obj = $obj;
    }

    /**
     * set the object id based on the given term id
     * must have the same logic as the database view and the api
     * @param int $id the term id that is converted to the object id
     * @return void
     */
    function set_id(int $id): void
    {
        if ($id % 2 == 0) {
            $this->set_obj_id(abs($id) / 2);
        } else {
            $this->set_obj_id((abs($id) + 1) / 2);
        }
    }

    /**
     * @return int the id of the term generated from the object id
     * e.g 1 for a word 1, -1 for a triple 1, 2 for a formula 1 and -2 for a verb 1
     */
    function id(): int
    {
        if ($this->is_word()) {
            return ($this->obj_id() * 2) - 1;
        } elseif ($this->is_triple()) {
            return ($this->obj_id() * -2) + 1;
        } elseif ($this->is_formula()) {
            return $this->obj_id() * 2;
        } elseif ($this->is_verb()) {
            return $this->obj_id() * -2;
        } else {
            return 0;
        }
    }

    /**
     * @return int|string|null the id of the object
     * e.g 1 for a word 1, 1 for a triple 1, 1 for a formula 1 and 1 for a verb 1
     */
    function obj_id(): int|string|null
    {
        return $this->obj()->id();
    }


    /*
     * interface
     */

    /**
     * @return array the json message array to send the updated data to the backend
     * corresponding to the api jsonSerialize function:
     * use the object id not the term id because the class is included
     * maybe to reduce traffic remove the class but than the term id needs to be used
     */
    function api_array(): array
    {
        $vars = array();
        if ($this->is_word()) {
            $vars[json_fields::OBJECT_CLASS] = term_api::CLASS_WORD;
        } elseif ($this->is_triple()) {
            $vars[json_fields::OBJECT_CLASS] = term_api::CLASS_TRIPLE;
            $trp = $this->obj();
            $vars[json_fields::FROM] = $trp->from()->id();
            $vars[json_fields::VERB] = $trp->verb()->id();
            $vars[json_fields::TO] = $trp->to()->id();
        } elseif ($this->is_formula()) {
            $vars[json_fields::OBJECT_CLASS] = term_api::CLASS_FORMULA;
        } elseif ($this->is_verb()) {
            $vars[json_fields::OBJECT_CLASS] = term_api::CLASS_VERB;
        } else {
            log_err('cannot create api message for term ' . $this->dsp_id() . ' because class is unknown');
        }
        $vars[json_fields::ID] = $this->obj_id();
        $vars[json_fields::NAME] = $this->name();
        $vars[json_fields::DESCRIPTION] = $this->description();
        if (!$this->is_verb()) {
            $vars[json_fields::TYPE] = $this->type_id();
        }
        if ($this->is_formula()) {
            $vars[json_fields::USER_TEXT] = $this->obj()->usr_text();
        }
        // TODO add exclude field and move to a parent object?
        if ($this->obj()?->share_id != null) {
            $vars[json_fields::SHARE] = $this->obj()?->share_id;
        }
        if ($this->obj()?->protection_id != null) {
            $vars[json_fields::PROTECTION] = $this->obj()?->protection_id;
        }
        return array_filter($vars, fn($value) => !is_null($value) && $value !== '');
    }


    /*
     * classifications
     */

    /**
     * @return bool true if this term is a word or supposed to be a word
     */
    function is_word(): bool
    {
        if ($this->obj()::class == word_dsp::class) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool true if this term is a triple
     */
    function is_triple(): bool
    {
        if ($this->obj()::class == triple_dsp::class) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool true if this term is a verb
     */
    function is_verb(): bool
    {
        if ($this->obj()::class == verb_dsp::class) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool true if this term is a formula
     */
    function is_formula(): bool
    {
        if ($this->obj()::class == formula_dsp::class) {
            return true;
        } else {
            return false;
        }
    }


    /*
     * display
     */

    /**
     * @return string best possible id for this term mainly used for debugging
     */
    function dsp_id(): string
    {
        return $this->obj()->dsp_id();
    }

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
        if ($this->is_word()) {
            return $this->obj()->display_linked();
        } elseif ($this->is_triple()) {
            return $this->obj()->display_linked();
        } elseif ($this->is_formula()) {
            return $this->obj()->display_linked();
        } elseif ($this->is_verb()) {
            return $this->obj()->display_linked();
        } else {
            $msg = 'Unexpected term type ' . $this->dsp_id();
            log_err($msg);
            return $msg;
        }
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
        $result .= \html\btn_del("unlink word", "/http/link_del.php?id=" . $link_id . "&back=" . $this->id());
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
     * @param term_api $type is a word to preselect the list to only those phrases matching this type
     * @param string $form_name
     * @param int $pos
     * @param string $class
     * @param string $back
     * @return string
     */
    function dsp_selector(term_api $type, string $form_name, int $pos, string $class, string $back = ''): string
    {
        // TODO include pattern in the call
        $pattern = '';
        $trm_lst = new term_list();
        $trm_lst->load_like($pattern);

        if ($pos > 0) {
            $field_name = "term" . $pos;
        } else {
            $field_name = "term";
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

        return $trm_lst->selector($form_name, $this->id(), $field_name, $label, '');
    }

}
