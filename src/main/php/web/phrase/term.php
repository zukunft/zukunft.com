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

use cfg\const\paths;
use html\const\paths as html_paths;
include_once html_paths::SANDBOX . 'combine_named.php';
include_once paths::SHARED . 'api.php';
include_once paths::SHARED . 'url_var.php';
include_once html_paths::FORMULA . 'formula.php';
include_once html_paths::SANDBOX . 'combine_named.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::WORD . 'triple.php';
include_once html_paths::WORD . 'word.php';
include_once html_paths::VERB . 'verb.php';
include_once paths::SHARED_TYPES . 'phrase_type.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'library.php';

use html\formula\formula;
use html\verb\verb;
use html\word\triple;
use html\word\word;
use html\sandbox\combine_named as combine_named_dsp;
use html\formula\formula as formula_dsp;
use html\user\user_message;
use html\verb\verb as verb_dsp;
use html\word\word as word_dsp;
use html\word\triple as triple_dsp;
use shared\types\phrase_type;
use shared\json_fields;
use shared\library;
use shared\url_var;

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
    function api_mapper(array $json_array): user_message
    {
        $usr_msg = new user_message();
        if ($json_array[json_fields::OBJECT_CLASS] == json_fields::CLASS_WORD) {
            $wrd = new word_dsp();
            $wrd->api_mapper($json_array);
            $this->set_obj($wrd);
            // unlike the cases below the switch of the term id to the object id not needed for words
        } elseif ($json_array[json_fields::OBJECT_CLASS] == json_fields::CLASS_TRIPLE) {
            $trp = new triple_dsp();
            $trp->api_mapper($json_array);
            $this->set_obj($trp);
            // TODO check if needed
            //$this->set_id($trp->id());
        } elseif ($json_array[json_fields::OBJECT_CLASS] == json_fields::CLASS_VERB) {
            $vrb = new verb_dsp();
            $vrb->api_mapper($json_array);
            $this->set_obj($vrb);
            //$this->set_id($vrb->id());
        } elseif ($json_array[json_fields::OBJECT_CLASS] == json_fields::CLASS_FORMULA) {
            $frm = new formula_dsp();
            $frm->api_mapper($json_array);
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
     * create the expected object based on the class name
     * must have the same logic as the database view and the frontend
     * @param string $class the term id as received e.g. from the database view
     * @return void
     */
    function set_obj_from_class(string $class): void
    {
        if ($class == triple::class) {
            $this->obj = new triple();
        } elseif ($class == formula::class) {
            $this->obj = new formula();
        } elseif ($class == verb::class) {
            $this->obj = new verb();
        } else {
            $this->obj = new word();
        }
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
     * load
     */

    /**
     * load the term object by the word or triple id (not the phrase id)
     * @param int $id the id of the term object e.g. for a triple "-1"
     * @param string $class not used for this term object just to be compatible with the db base object
     * @param bool $including_triples to include the words or triple of a triple (not recursive)
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_obj_id(int $id, string $class, bool $including_triples = true): int
    {
        log_debug($this->name());
        $result = 0;

        if ($class == word::class) {
            if ($this->load_word_by_id($id)) {
                $result = $this->obj_id();
            }
        } elseif ($class == triple::class) {
            if ($this->load_triple_by_id($id, $including_triples)) {
                $result = $this->obj_id();
            }
        } elseif ($class == formula::class) {
            if ($this->load_formula_by_id($id)) {
                $result = $this->obj_id();
            }
        } elseif ($class == verb::class) {
            if ($this->load_verb_by_id($id)) {
                $result = $this->obj_id();
            }
        } else {
            log_err('Unexpected class ' . $class . ' when creating term ' . $this->dsp_id());
        }

        log_debug('term->load loaded id "' . $this->id() . '" for ' . $this->name());

        return $result;
    }

    /**
     * simply load a word
     * (separate functions for loading  for a better overview)
     */
    private
    function load_word_by_id(int $id): bool
    {
        global $phr_typ_cac;

        $result = false;
        $wrd = new word();
        if ($wrd->load_by_id($id)) {
            if ($wrd->type_id() == $phr_typ_cac->id(phrase_type::FORMULA_LINK)) {
                $result = $this->load_formula_by_id($id);
            } else {
                $this->set_id_from_obj($wrd->id(), word::class);
                $this->obj = $wrd;
                $result = true;
            }
        }
        return $result;
    }

    /**
     * simply load a triple
     */
    private
    function load_triple_by_id(int $id, bool $including_triples): bool
    {
        $result = false;
        if ($including_triples) {
            $trp = new triple();
            if ($trp->load_by_id($id)) {
                $this->set_id_from_obj($trp->id(), triple::class);
                $this->obj = $trp;
                $result = true;
            }
        }
        return $result;
    }

    /**
     * simply load a formula
     * without fixing any missing related word issues
     */
    private function load_formula_by_id(int $id): bool
    {
        $result = false;
        $frm = new formula();
        if ($frm->load_by_id($id)) {
            $this->set_id_from_obj($frm->id(), formula::class);
            $this->obj = $frm;
            $result = true;
        }
        return $result;
    }

    /**
     * simply load a verb
     */
    private function load_verb_by_id(int $id): bool
    {
        $result = false;
        $vrb = new verb;
        $vrb->set_name($this->name());
        if ($vrb->load_by_id($id)) {
            $this->set_id_from_obj($vrb->id(), verb::class);
            $this->obj = $vrb;
            $result = true;
        }
        return $result;
    }

    /**
     * set the term id based id the word, triple, verb or formula id
     * must have the same logic as the database view and the frontend
     * TODO deprecate?
     *
     * @param int $id the object id that is converted to the term id
     * @param string $class the class of the term object
     * @return void
     */
    function set_id_from_obj(int $id, string $class): void
    {
        if ($id != null) {
            if ($class == word::class) {
                if ($this->obj == null) {
                    $this->obj = new word();
                    $this->obj->set_id($id);
                }
            } elseif ($class == triple::class) {
                if ($this->obj == null) {
                    $this->obj = new triple();
                    $this->obj->set_id($id);
                }
            } elseif ($class == formula::class) {
                if ($this->obj == null) {
                    $this->obj = new formula();
                    $this->obj->set_id($id);
                }
            } elseif ($class == verb::class) {
                if ($this->obj == null) {
                    $this->obj = new verb();
                    $this->obj->set_id($id);
                }
            }
            $this->obj->set_id($id);
        }
    }


    /*
     * interface
     */

    /**
     * TODO review and use the api_array function of the objects
     * @return array the json message array to send the updated data to the backend
     * corresponding to the api jsonSerialize function:
     * use the object id not the term id because the class is included
     * maybe to reduce traffic remove the class but than the term id needs to be used
     */
    function api_array(): array
    {
        $lib = new library();
        $vars = array();
        if ($this->is_verb()) {
            $vars = $this->obj()?->api_array();
            $class = $lib->class_to_name($this->obj()::class);
            $vars[json_fields::OBJECT_CLASS] = $class;
        } else {
            if ($this->is_word()) {
                $vars[json_fields::OBJECT_CLASS] = json_fields::CLASS_WORD;
            } elseif ($this->is_triple()) {
                $vars[json_fields::OBJECT_CLASS] = json_fields::CLASS_TRIPLE;
                $trp = $this->obj();
                $vars[json_fields::FROM] = $trp->from()->id();
                $vars[json_fields::VERB] = $trp->verb()->id();
                $vars[json_fields::TO] = $trp->to()->id();
            } elseif ($this->is_formula()) {
                $vars[json_fields::OBJECT_CLASS] = json_fields::CLASS_FORMULA;
            } elseif ($this->is_verb()) {
                $vars[json_fields::OBJECT_CLASS] = json_fields::CLASS_VERB;
            } else {
                log_err('cannot create api message for term ' . $this->dsp_id() . ' because class is unknown');
            }
            $vars[json_fields::ID] = $this->obj_id();
            $vars[json_fields::NAME] = $this->name();
            $vars[json_fields::DESCRIPTION] = $this->description();
            $vars[json_fields::TYPE] = $this->type_id();
            if ($this->is_formula()) {
                $vars[json_fields::USER_TEXT] = $this->obj()->usr_text();
            }
            // TODO add exclude field and move to a parent object?
            if ($this->obj()?->share_id() != null) {
                $vars[json_fields::SHARE] = $this->obj()?->share_id();
            }
            if ($this->obj()?->protection_id() != null) {
                $vars[json_fields::PROTECTION] = $this->obj()?->protection_id();
            }
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
    function name_tip(): string
    {
        return $this->obj()->name_tip();
    }

    /**
     * @returns string the html code to display the phrase with reference links
     */
    function name_link(): string
    {
        if ($this->is_word()) {
            return $this->obj()->name_link();
        } elseif ($this->is_triple()) {
            return $this->obj()->name_link();
        } elseif ($this->is_formula()) {
            return $this->obj()->name_link();
        } elseif ($this->is_verb()) {
            return $this->obj()->name_link();
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
     * @param term $type is a word to preselect the list to only those phrases matching this type
     * @param string $form
     * @param int $pos
     * @param string $class
     * @param string $back
     * @return string
     */
    function dsp_selector(term $type, string $form, int $pos, string $class, string $back = ''): string
    {
        // TODO include pattern in the call
        $pattern = '';
        $trm_lst = new term_list();
        $trm_lst->load_like($pattern);

        if ($pos > 0) {
            $name = url_var::TERM_POS_LONG . $pos;
        } else {
            $name = url_var::TERM_LONG;
        }
        $label = "";
        if ($form != "value_add" and $form != "value_edit") {
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

        return $trm_lst->selector($form, $this->id(), $name);
    }

}
