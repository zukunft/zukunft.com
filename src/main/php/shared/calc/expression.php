<?php

/*

    shared/calc/expression.php - common parts of the formula expressing handling used in front- and backend
    --------------------------

    repeating some backend functions in the frontend, but based on the frontend cache and frontend object



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

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace shared\calc;

include_once MODEL_FORMULA_PATH . 'formula.php';
include_once MODEL_PHRASE_PATH . 'term.php';
include_once MODEL_PHRASE_PATH . 'term_list.php';
include_once MODEL_WORD_PATH . 'triple.php';
include_once MODEL_VERB_PATH . 'verb.php';
include_once MODEL_WORD_PATH . 'word.php';
include_once WEB_FORMULA_PATH . 'formula.php';
include_once WEB_PHRASE_PATH . 'term.php';
include_once WEB_PHRASE_PATH . 'term_list.php';
include_once WEB_WORD_PATH . 'triple.php';
include_once WEB_VERB_PATH . 'verb.php';
include_once WEB_WORD_PATH . 'word.php';
include_once SHARED_CONST_PATH . 'chars.php';
include_once SHARED_ENUM_PATH . 'messages.php';
include_once SHARED_PATH . 'library.php';

use cfg\formula\formula;
use cfg\phrase\term;
use cfg\phrase\term_list;
use cfg\user\user_message;
use cfg\word\triple;
use cfg\verb\verb;
use cfg\word\word;
use html\formula\formula as formula_dsp;
use html\phrase\term as term_dsp;
use html\phrase\term_list as term_list_dsp;
use html\word\triple as triple_dsp;
use html\verb\verb as verb_dsp;
use html\word\word as word_dsp;
use shared\const\chars;
use shared\enum\messages;
use shared\enum\messages as msg_id;
use shared\library;

class expression
{

    /*
     * object vars
     */

    // the formula expression in the human-readable format
    private ?string $usr_text = null;
    // true if the reference text has been updated and not yet converted
    private bool $usr_text_dirty = false;
    // the formula expression with the database references
    private ?string $ref_text = null;
    // true if the human-readable text has been updated and not yet converted
    private bool $ref_text_dirty = false;
    // the formula name only for better user messages
    private string $frm_name = '';
    // description of the problems that appeared during the conversion from the human-readable to the database reference format
    public ?string $err_text = null;


    /*
     * construct and map
     */

    function reset(): void
    {
        $this->usr_text = null;
        $this->usr_text_dirty = false;
        $this->ref_text = null;
        $this->ref_text_dirty = false;
        $this->frm_name = '';
        $this->err_text = null;
    }


    /*
     * set and get
     */

    /**
     * update the expression by setting the human-readable format and try to update the database reference format
     * @param string|null $usr_txt the formula expression in the human-readable format
     * @param term_list|term_list_dsp|null $trm_lst a list of preloaded terms that should be used for the transformation
     * @return void
     */
    function set_user_text(?string $usr_txt, term_list|term_list_dsp|null $trm_lst = null): void
    {
        if ($usr_txt != null) {
            $this->usr_text = $usr_txt;
            $this->usr_text_dirty = false;
            $this->ref_text_dirty = true;
            $this->ref_text($trm_lst);
        }
    }

    /**
     * update the expression by setting the database reference format and try to update the human-readable format
     * @param string|null $ref_txt the formula expression in the database reference format
     * @param term_list|term_list_dsp|null $trm_lst a list of preloaded terms that should be used for the transformation
     * @return void
     */
    function set_ref_text(?string $ref_txt, term_list|term_list_dsp|null $trm_lst = null): void
    {
        if ($ref_txt != null) {
            $this->ref_text = $ref_txt;
            $this->ref_text_dirty = false;
            $this->usr_text_dirty = true;
            $this->user_text($trm_lst);
        }
    }

    /**
     * @param term_list|term_list_dsp|null $trm_lst a list of preloaded terms that should be used for the transformation
     * @return string|null the recreated expression in the human-readable format or null if an error has occurred
     */
    function user_text(term_list|term_list_dsp|null $trm_lst = null): ?string
    {
        if ($this->usr_text_dirty) {
            $this->usr_text = $this->get_usr_text($trm_lst);
        }
        if (!$this->usr_text_dirty) {
            return $this->usr_text;
        } else {
            return '';
        }
    }

    /**
     * get and set the reference text based on the user formula expression
     * TODO Prio 2 do not call it from the frontend
     * @param term_list|term_list_dsp|null $trm_lst a list of preloaded terms that should be used for the transformation
     * @param user_message $usr_msg to enrich with problems and suggested solution
     * @return string|null the recreated expression in the database reference format or null if an error has occurred
     */
    function ref_text(
        term_list|term_list_dsp|null $trm_lst = null,
        user_message                 $usr_msg = new user_message()
    ): ?string
    {
        if ($this->ref_text_dirty) {
            $this->ref_text = $this->get_ref_text($trm_lst, $usr_msg);
            if ($usr_msg->is_ok()) {
                $this->ref_text_dirty = false;
            }
        }
        if (!$this->ref_text_dirty) {
            return $this->ref_text;
        } else {
            return '';
        }
    }


    /*
     * convert
     * internal function to convert from reference text to user text and back
     */

    /**
     * convert the user text to the database reference format
     * @param term_list|term_list_dsp|null $trm_lst a list of preloaded terms that should be used for the transformation
     * @param user_message $usr_msg to enrich with problems and suggested solution
     * @return string the expression in the formula reference format
     */
    protected function get_ref_text(
        term_list|term_list_dsp|null $trm_lst = null,
        user_message                 $usr_msg = new user_message()
    ): string
    {
        $result = '';

        // check the formula indicator "=" and convert the left and right part separately
        $pos = strpos($this->user_text($trm_lst), chars::CHAR_CALC);
        if ($pos >= 0) {
            $left_part = $this->res_part_usr();
            $right_part = $this->r_part_usr();
            $left_part = $this->get_ref_part($left_part, $trm_lst, $usr_msg);
            // continue with the right part of the expression only if the left part has been fine
            if (!$this->usr_text_dirty) {
                $right_part = $this->get_ref_part($right_part, $trm_lst, $usr_msg);
            }
            $result = $left_part . chars::CHAR_CALC . $right_part;
        }

        // remove all spaces because they are not relevant for calculation and to avoid too much recalculation
        return str_replace(" ", "", $result);
    }

    /**
     * @param term_list|term_list_dsp|null $trm_lst a list of preloaded terms that should be used for the transformation
     * @return string the formula expression converted to the user text from the database reference format
     * e.g. converts "{w5}={w6}{l12}/{f19}" to "'percent' = 'sales' 'differentiator'/'Total sales'"
     */
    protected function get_usr_text(term_list|term_list_dsp|null $trm_lst = null): string
    {
        log_debug($this->ref_text());
        $result = '';

        // check the formula indicator "=" and convert the left and right part separately
        $pos = strpos($this->ref_text($trm_lst), chars::CHAR_CALC);
        if ($pos > 0) {
            $left_part = $this->res_part();
            $right_part = $this->r_part();
            $left_part = $this->get_usr_part($left_part, $trm_lst);
            // continue with the right part of the expression only if the left part has been fine
            if (!$this->usr_text_dirty) {
                $right_part = $this->get_usr_part($right_part, $trm_lst);
            }
            $result = $left_part . chars::CHAR_CALC . $right_part;
        }

        log_debug('done "' . $result . '"');
        return $result;
    }

    /*
     * internal
     * functions public just for testing
     */

    /**
     * find the position of the formula indicator "="
     * use the part left of it to add the words to the result
     */
    function res_part(): string
    {
        $lib = new library();
        $result = $lib->str_left_of($this->ref_text(), chars::CHAR_CALC);
        return trim($result);
    }

    function res_part_usr(): string
    {
        $lib = new library();
        $result = $lib->str_left_of($this->user_text(), chars::CHAR_CALC);
        return trim($result);
    }

    function r_part(): string
    {
        $lib = new library();
        $result = $lib->str_right_of($this->ref_text(), chars::CHAR_CALC);
        return trim($result);
    }

    function r_part_usr(): string
    {
        $lib = new library();
        $result = $lib->str_right_of($this->user_text(), chars::CHAR_CALC);
        return trim($result);
    }

    /*
     * part loading
     */

    /**
     * converts a formula from the user text format to the database reference format
     * e.g. converts "='sales' 'differentiator'/'Total sales'" to "={w6}{l12}/{f19}"
     *
     * @param string $frm_part_text the expression text in user format that should be converted
     * @param term_list|term_list_dsp|null $trm_lst a list of preloaded terms that should be preferred used for the conversion
     * @param user_message $usr_msg to enrich with problems and suggested solution
     * @return string the expression text in the database ref format
     */
    private function get_ref_part(
        string                  $frm_part_text,
        term_list|term_list_dsp $trm_lst = null,
        user_message            $usr_msg = new user_message()
    ): string
    {
        $result = $frm_part_text;

        // if everything works fine the user text is not dirty anymore
        $this->ref_text_dirty = false;

        if ($frm_part_text != '') {
            // find the first word
            $start = 0;
            $pos = strpos($result, chars::TERM_DELIMITER, $start);
            $end = strpos($result, chars::TERM_DELIMITER, $pos + 1);
            while ($end !== False) {
                // for 12'45'78: pos = 2, end = 5, name = 45, left = 12. right = 78
                $name = substr($result, $pos + 1, $end - $pos - 1);
                $left = substr($result, 0, $pos);
                $right = substr($result, $end + 1);
                log_debug('name "' . $name . '" (' . $end . ') left "' . $left . '" (' . $pos . ') right "' . $right . '"');

                $db_sym = '';

                // check if the preloaded terms can be used for the conversion
                if ($trm_lst != null) {
                    $trm = $trm_lst->get_by_name($name);
                    if ($trm != null) {
                        if ($trm->obj_id() > 0) {
                            $db_sym = $this->get_db_sym($trm);
                        }
                    } else {
                        $usr_msg->add_id_with_vars(msg_id::FORMULA_TERM_MISSING, [
                            msg_id::VAR_NAME => $name,
                            msg_id::VAR_FORMULA => $this->frm_name,
                            msg_id::VAR_EXPRESSION => $frm_part_text
                        ]);
                    }
                }

                if ($db_sym == '') {
                    $db_sym = $this->get_term_symbol($name);
                }

                $result = $left . $db_sym . $right;
                log_debug('exchanged to "' . $result . '"');

                // find the next word
                $start = strlen($left) + strlen($db_sym);
                $end = false;
                if ($start < strlen($result)) {
                    $pos = strpos($result, chars::TERM_DELIMITER, $start);
                    if ($pos !== false) {
                        $end = strpos($result, chars::TERM_DELIMITER, $pos + 1);
                    }
                }
            }

            log_debug('done "' . $result . '"');
        }
        return $result;
    }


    /*
     * internal functions
     */

    /**
     * converts a formula from the database reference format to the human-readable format
     * e.g. converts "{w6}{l12}/{f19}" to "'sales' 'differentiator'/'Total sales'"
     * @param string $frm_part_text the expression text in user format that should be converted
     * @param term_list|null $trm_lst a list of preloaded terms that should be preferred used for the conversion
     * @return string the expression text in the database ref format
     */
    private function get_usr_part(string $frm_part_text, ?term_list $trm_lst = null): string
    {
        $result = $frm_part_text;

        // if everything works fine the user text is not dirty anymore
        $this->usr_text_dirty = false;

        // replace the database references with the names
        $trm = $this->get_next_term_from_ref($result, $trm_lst);
        while ($trm != null) {
            $db_sym = $this->get_db_sym($trm);
            $result = str_replace($db_sym, chars::TERM_DELIMITER . $trm->name() . chars::TERM_DELIMITER, $result);
            $trm = $this->get_next_term_from_ref($result, $trm_lst);
        }

        log_debug($result);
        return $result;
    }

    /**
     * get the next term from the expression part in the database reference format
     *
     * @param string $frm_part_ref_text
     * @param term_list|null $trm_lst
     * @return term|null
     */
    private function get_next_term_from_ref(string $frm_part_ref_text, term_list $trm_lst = null): ?term
    {
        $trm = null;

        $lib = new library();

        /*
        if ($trm_lst == null) {
            $trm_lst = new term_list_dsp();
        }
        */

        // get a word
        $id = $lib->str_between($frm_part_ref_text, chars::WORD_START, chars::WORD_END);
        if ($id > 0) {
            $wrd = $trm_lst?->word_by_id($id);
            if ($wrd == null) {
                $wrd = $this->load_word($id);
            }
            if ($wrd == null) {
                $this->usr_text_dirty = true;
                log_warning('Word with id ' . $id . ' not found');
            } else {
                $trm = $wrd->term();
                // TODO remember the word to avoid double load
                //$trm_lst->add($trm);
            }
        }

        // get a triple
        if ($id == "") {
            $id = $lib->str_between($frm_part_ref_text, chars::TRIPLE_START, chars::TRIPLE_END);
            if ($id > 0) {
                $trp = $trm_lst?->triple_by_id($id);
                if ($trp == null) {
                    $trp = $this->load_triple($id);
                }
                if ($trp == null) {
                    $this->usr_text_dirty = true;
                    log_warning('Triple with id ' . $id . ' not found');
                } else {
                    $trm = $trp->term();
                    //$trm_lst->add($trm);
                }
            }
        }

        // get a formulas
        if ($id == "") {
            $id = $lib->str_between($frm_part_ref_text, chars::FORMULA_START, chars::FORMULA_END);
            if ($id > 0) {
                $frm = $trm_lst?->formula_by_id($id);
                if ($frm == null) {
                    $frm = $this->load_formula($id);
                }
                if ($frm == null) {
                    $this->usr_text_dirty = true;
                    log_warning('Formula with id ' . $id . ' not found');
                } else {
                    $trm = $frm->term();
                    //$trm_lst->add($trm);
                }
            }
        }

        // get a verbs
        if ($id == "") {
            $id = $lib->str_between($frm_part_ref_text, chars::VERB_START, chars::VERB_END);
            if ($id > 0) {
                $vrb = $trm_lst?->verb_by_id($id);
                if ($vrb == null) {
                    $vrb = $this->load_verb($id);
                }
                if ($vrb == null) {
                    $this->usr_text_dirty = true;
                    log_warning('Verb with id ' . $id . ' not found');
                } else {
                    $trm = $vrb->term();
                    //$trm_lst->add($trm);
                }
            }
        }

        return $trm;
    }

    /**
     * create the symbol for a term e.g. {w1} for the word with id 1
     * @param term|term_dsp $trm the term that should be used to create the database reference symbol
     * @return string the database reference symbol e.g. {w1} for word with the id 1
     */
    protected function get_db_sym(term|term_dsp $trm): string
    {
        $db_sym = '';
        if ($trm->is_word()) {
            $db_sym = chars::WORD_START . $trm->obj_id() . chars::WORD_END;
        } elseif ($trm->is_triple()) {
            $db_sym = chars::TRIPLE_START . $trm->obj_id() . chars::TRIPLE_END;
        } elseif ($trm->is_formula()) {
            $db_sym = chars::FORMULA_START . $trm->obj_id() . chars::FORMULA_END;
        } elseif ($trm->is_verb()) {
            $db_sym = chars::VERB_START . $trm->obj_id() . chars::VERB_END;
        }
        return $db_sym;
    }

    protected function get_term_symbol(string $name): string
    {
        // check for formulas first, because for every formula a word is also existing
        // similar to a part in get_usr_part, maybe combine
        $db_sym = $this->get_formula_symbol($name);

        // check for words
        if ($db_sym == '') {
            $db_sym = $this->get_word_symbol($name);
        }

        // check for triple
        if ($db_sym == '') {
            $db_sym = $this->get_triple_symbol($name);
        }

        // check for verbs
        if ($db_sym == '') {
            $db_sym = $this->get_verb_symbol($name);
        }

        // if still not found report the missing link
        if ($db_sym == '' and $name <> '') {
            $this->ref_text_dirty = true;
            $this->err_text .= 'No word, triple, formula or verb found for "' . $name . '". ';
        }

        return $db_sym;

    }

    /*
     * overwrite
     */

    protected function get_formula_symbol(string $name): string
    {
        return 'Error: function get_formula_symbol() is expected to be overwritten by a frontend or backend class function';
    }

    protected function get_word_symbol(string $name): string
    {
        return 'Error: function get_word_symbol() is expected to be overwritten by a frontend or backend class function';
    }

    protected function get_triple_symbol(string $name): string
    {
        return 'Error: function get_triple_symbol() is expected to be overwritten by a frontend or backend class function';
    }

    protected function get_verb_symbol(string $name): string
    {
        return 'Error: function get_verb_symbol() is expected to be overwritten by a frontend or backend class function';
    }

    protected function load_word(int $id): word|word_dsp|null
    {
        log_err('Error: function load_word() is expected to be overwritten');
        return new word_dsp();
    }

    protected function load_triple(int $id): triple|triple_dsp|null
    {
        log_err('Error: function load_triple() is expected to be overwritten');
        return new triple_dsp();
    }

    protected function load_formula(int $id): formula|formula_dsp|null
    {
        log_err('Error: function load_formula() is expected to be overwritten');
        return new formula_dsp();
    }

    protected function load_verb(int $id): verb|verb_dsp|null
    {
        log_err('Error: function load_verb() is expected to be overwritten');
        return new verb_dsp();
    }

}
