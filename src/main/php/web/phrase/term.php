<?php

/*

    web/phrase/term.php - to create the html code to display a word, triple, verb or formula
    --------------------

    $trm is the suggested var name

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

namespace Zukunft\ZukunftCom\main\php\web\phrase;

use Zukunft\ZukunftCom\main\php\shared\enum\messages;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::SANDBOX . 'combine_named.php';
include_once html_paths::SHARED . 'api.php';
include_once html_paths::SHARED . 'url_var.php';
include_once html_paths::FORMULA . 'formula.php';
include_once html_paths::HTML . 'button.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::SANDBOX . 'combine_named.php';
include_once html_paths::TYPES . 'type_lists.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::WORD . 'triple.php';
include_once html_paths::WORD . 'word.php';
include_once html_paths::VERB . 'verb.php';
include_once html_paths::SHARED_ENUM . 'messages.php';
include_once html_paths::SHARED_TYPES . 'api_type_list.php';
include_once html_paths::SHARED_TYPES . 'phrase_types.php';
include_once html_paths::SHARED . 'json_fields.php';
include_once html_paths::SHARED . 'library.php';
include_once html_paths::SHARED_CONST . 'views.php';

use Zukunft\ZukunftCom\main\php\web\formula\formula;
use Zukunft\ZukunftCom\main\php\web\html\button;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\verb\verb;
use Zukunft\ZukunftCom\main\php\web\word\triple;
use Zukunft\ZukunftCom\main\php\web\word\word;
use Zukunft\ZukunftCom\main\php\web\sandbox\combine_named as combine_named;
use Zukunft\ZukunftCom\main\php\web\types\type_lists;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\types\phrase_types;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\url_var;

class term extends combine_named
{

    /*
     * set and get
     */

    /**
     * set the vars of this term html display object bases on the api message
     * @param array $json_array an api json message as a string
     * @param user_message $msg ok or a warning e.g. if the server version does not match
     * @return bool true if the mapping has been completed successfully
     */
    function api_mapper(array $json_array, user_message $msg): bool
    {
        if ($json_array[json_fields::OBJECT_CLASS] == json_fields::CLASS_WORD) {
            $wrd = new word();
            $wrd->api_mapper($json_array, $msg);
            $this->set_obj($wrd);
            // unlike the cases below the switch of the term id to the object id not needed for words
        } elseif ($json_array[json_fields::OBJECT_CLASS] == json_fields::CLASS_TRIPLE) {
            $trp = new triple();
            $trp->api_mapper($json_array, $msg);
            $this->set_obj($trp);
            // TODO check if needed
            //$this->set_id($trp->id());
        } elseif ($json_array[json_fields::OBJECT_CLASS] == json_fields::CLASS_VERB) {
            $vrb = new verb();
            $vrb->api_mapper($json_array, $msg);
            $this->set_obj($vrb);
            //$this->set_id($vrb->id());
        } elseif ($json_array[json_fields::OBJECT_CLASS] == json_fields::CLASS_FORMULA) {
            $frm = new formula();
            $frm->api_mapper($json_array, $msg);
            $this->set_obj($frm);
            //$this->set_id($frm->id());
        } else {
            $msg->add_error_text('Json class ' . $json_array[json_fields::OBJECT_CLASS] . ' not expected for a term');
        }
        return $msg->is_ok();
    }

    function set_term_obj(word|triple|verb|formula|null $obj): void
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
     * set the object class and the object id based on the given term id
     * must have the same logic as the database view and the api
     * all cases are covered by the "term id" block of unit_ui/term_ui_tests.php
     * @param int $id the term id that is converted to the object class and id
     * @return void
     */
    function set_id(int $id): void
    {
        // the term id encodes the class of the term object: an odd id is a phrase (positive a
        // word, negative a triple) and an even id is a formula (positive) or a verb (negative),
        // so without setting the class every term of a bare id would be read as a word (see id())
        if ($id % 2 == 0) {
            $class = $id > 0 ? formula::class : verb::class;
            $obj_id = abs($id) / 2;
        } else {
            $class = $id > 0 ? word::class : triple::class;
            $obj_id = (abs($id) + 1) / 2;
        }
        // an already loaded object of the same class is kept, so that its name is not lost
        if ($this->obj() == null or $this->obj()::class != $class) {
            $this->set_obj_from_class($class);
        }
        $this->set_obj_id($obj_id);
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
    function load_by_obj_id(int $id, string $class, user_message $msg, bool $including_triples = true): int
    {
        log_debug($this->name());
        $result = 0;

        if ($class == word::class) {
            if ($this->load_word_by_id($id, $msg)) {
                $result = $this->obj_id();
            }
        } elseif ($class == triple::class) {
            if ($this->load_triple_by_id($id, $msg, $including_triples)) {
                $result = $this->obj_id();
            }
        } elseif ($class == formula::class) {
            if ($this->load_formula_by_id($id, $msg)) {
                $result = $this->obj_id();
            }
        } elseif ($class == verb::class) {
            if ($this->load_verb_by_id($id, $msg)) {
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
    function load_word_by_id(int $id, user_message $msg): bool
    {
        $result = false;
        $wrd = new word();
        if ($wrd->load_by_id($id, $msg)) {
            $phr_typ = type_lists::phrase_types($msg);
            if ($wrd->type_id($msg) == $phr_typ?->id(phrase_types::FORMULA_LINK)) {
                $result = $this->load_formula_by_id($id, $msg);
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
    function load_triple_by_id(int $id, user_message $msg, bool $including_triples): bool
    {
        $result = false;
        if ($including_triples) {
            $trp = new triple();
            if ($trp->load_by_id($id, $msg)) {
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
    private function load_formula_by_id(int $id, user_message $msg): bool
    {
        $result = false;
        $frm = new formula();
        if ($frm->load_by_id($id, $msg)) {
            $this->set_id_from_obj($frm->id(), formula::class);
            $this->obj = $frm;
            $result = true;
        }
        return $result;
    }

    /**
     * simply load a verb
     */
    private function load_verb_by_id(int $id, user_message $msg): bool
    {
        $result = false;
        $vrb = new verb;
        $vrb->set_name($this->name());
        if ($vrb->load_by_id($id, $msg)) {
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
                }
            } elseif ($class == triple::class) {
                if ($this->obj == null) {
                    $this->obj = new triple();
                }
            } elseif ($class == formula::class) {
                if ($this->obj == null) {
                    $this->obj = new formula();
                }
            } elseif ($class == verb::class) {
                if ($this->obj == null) {
                    $this->obj = new verb();
                }
            } else {
                if ($this->obj == null) {
                    $this->obj = new word();
                }
            }
            $this->obj->id = $id;
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
    function api_array(api_type_list|array $typ_lst, user_message $msg): array
    {
        $lib = new library();
        $vars = array();
        if ($this->is_verb()) {
            $vars = $this->obj()?->api_array($typ_lst, $msg);
            $class = $lib->class_to_name($this->obj()::class);
            $vars[json_fields::OBJECT_CLASS] = $class;
        } else {
            if ($this->is_word()) {
                $vars[json_fields::OBJECT_CLASS] = json_fields::CLASS_WORD;
            } elseif ($this->is_triple()) {
                $vars[json_fields::OBJECT_CLASS] = json_fields::CLASS_TRIPLE;
                $trp = $this->obj();
                $vars[json_fields::FROM] = $trp->get_from()->id();
                $vars[json_fields::VERB] = $trp->get_verb()->id();
                $vars[json_fields::TO] = $trp->get_to()->id();
                // like the backend emit the fields are only sent if the triple uses them
                if ($trp->weight != null) {
                    $vars[json_fields::WEIGHT] = $trp->weight;
                }
                if ($trp->condition_id != null) {
                    $vars[json_fields::CONDITION_ID] = $trp->condition_id;
                }
            } elseif ($this->is_formula()) {
                $vars[json_fields::OBJECT_CLASS] = json_fields::CLASS_FORMULA;
            } elseif ($this->is_verb()) {
                $vars[json_fields::OBJECT_CLASS] = json_fields::CLASS_VERB;
            } else {
                log_err('cannot create api message for term ' . $this->dsp_id() . ' because class is unknown');
            }
            $vars[json_fields::ID] = $this->obj_id();
            $vars[json_fields::NAME] = $this->name();
            $vars[json_fields::DESCRIPTION] = $this->get_description();
            $vars[json_fields::TYPE] = $this->type_id($msg);
            if ($this->is_formula()) {
                $vars[json_fields::USER_TEXT] = $this->obj()->get_usr_text();
                $vars[json_fields::LATEX] = $this->obj()->get_latex();
            }
            // TODO add exclude field and move to a parent object?
            if ($this->obj()?->share_id() != null) {
                $vars[json_fields::SHARE] = $this->obj()?->share_id();
            }
            if ($this->obj()?->protection_id() != null) {
                $vars[json_fields::PROTECTION] = $this->obj()?->protection_id();
            }
            if ($this->obj()?->impact != null) {
                $vars[json_fields::IMPACT] = $this->obj()?->impact;
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
        if ($this->obj() === null) {
            return false;
        } else {
            if ($this->obj()::class == word::class) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * @return bool true if this term is a triple
     */
    function is_triple(): bool
    {
        if ($this->obj() === null) {
            return false;
        } else {
            if ($this->obj()::class == triple::class) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * @return bool true if this term is a verb
     */
    function is_verb(): bool
    {
        if ($this->obj() === null) {
            return false;
        } else {
            if ($this->obj()::class == verb::class) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * @return bool true if this term is a formula
     */
    function is_formula(): bool
    {
        if ($this->obj() === null) {
            return false;
        } else {
            if ($this->obj()::class == formula::class) {
                return true;
            } else {
                return false;
            }
        }
    }


    /*
     * conversion
     */

    function get_word(): word
    {
        $wrd = new word();
        if (get_class($this->obj) == word::class) {
            $wrd = $this->obj;
        }
        return $wrd;
    }

    function get_triple(): triple
    {
        $lnk = new triple();
        if (get_class($this->obj) == triple::class) {
            $lnk = $this->obj;
        }
        return $lnk;
    }

    function get_formula(): formula
    {
        $frm = new formula();
        if (get_class($this->obj) == formula::class) {
            $frm = $this->obj;
        }
        return $frm;
    }

    function get_verb(): verb
    {
        $vrb = new verb();
        if (get_class($this->obj) == verb::class) {
            $vrb = $this->obj;
        }
        return $vrb;
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
     * @return float the system calculated impact of the wrapped word, triple, formula or verb;
     *               used to sort a term list so that the most relevant term is shown first
     */
    function get_impact(): float
    {
        return $this->obj()->impact;
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
     * @param array $url_array the url params of the calling page; the page-identifying params are
     *                         added with the url_var::BACK ('9') prefix so the edit mask can return
     *                         to the calling page
     * @return string the html code of a "fas fa-edit" icon that links to the edit page of the
     *                wrapped object e.g. /http/view.php?m=10&id=206&9m=67 for triple 206
     */
    function edit_link(array $url_array = []): string
    {
        global $mtr;

        $html = new html_base();
        $url = $html->url_with_back(
            $html->url_back($this->edit_view_id(), $this->obj_id()),
            html_base::page_url_array($url_array)
        );
        $icon = '<' . html_base::I . ' ' . html_base::CLASS_HTML . '="fas fa-edit"></' . html_base::I . '>';
        return $html->ref($url, $icon, $mtr->txt($this->obj()::MSG_EDIT), '', true);
    }

    /**
     * @return int the database id of the edit view that matches the wrapped object type
     *             so that the edit link points to the right edit page (word, triple, formula or verb)
     */
    private function edit_view_id(): int
    {
        if ($this->is_triple()) {
            $view_id = views::TRIPLE_EDIT_ID;
        } elseif ($this->is_formula()) {
            $view_id = views::FORMULA_EDIT_ID;
        } elseif ($this->is_verb()) {
            $view_id = views::VERB_EDIT_ID;
        } else {
            $view_id = views::WORD_EDIT_ID;
        }
        return $view_id;
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
        $btn = new button();
        $html = new html_base();
        $del_call = $html->url_back(views::TRIPLE_DEL_ID, $link_id, '', (string)$this->id());
        $result = '    <td>' . "\n";
        $result .= $btn->del(msg_id::WORD_UNLINK, $del_call);
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
            $name = url_var::TERM_POS . $pos;
        } else {
            $name = url_var::TERM;
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
        // TODO Prio 3 activate
        // $sel->bs_class = $class;

        return $trm_lst->selector($form, $this->id(), $name);
    }

    /**
     * @return string that best describes this object
     */
    function display(): string
    {
        return $this->name();
    }

}
