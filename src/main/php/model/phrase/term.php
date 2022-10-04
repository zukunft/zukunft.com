<?php

/*

  term.php - either a word, verb or formula
  --------
  
  mainly to check the term consistency of all objects
  a term must be unique for word, verb and triple e.g. "Company" is a word "is a" is a verb and "Kanton Zurich" is a triple
  all terms are the same for each user
  if a user changes a term and it has been used already
  a new term is created and the deletion of the existing term is requested
  if all user have confirmed the deletion, the term is finally deleted
  each user can have its own language translation which must be unique only for one user
  so one user may use "Zurich" in US English for "Kanton Zurich"
  and another user may use "Zurich" in US English for "Zurich AG"
  
  
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
  
  TODO: load formula word
        check triple

*/

use api\term_api;
use cfg\phrase_type;
use html\term_dsp;
use html\word_dsp;

class term
{

    public ?int $id = null;      // the database id of the word, verb or formula
    public ?user $usr = null;    // the person who wants to add a term (word, verb or formula)
    public ?string $type = null; // either "word", "verb" or "formula"
    public ?string $name = null; // the name used (must be unique for words, verbs and formulas)
    public ?object $obj = null;  // the word, verb or formula object

    /**
     * always set the user because a term is always user specific
     * @param user $usr the user who requested to see this term
     */
    function __construct(user $usr)
    {
        $this->usr = $usr;
    }

    /*
     * get, set and debug functions
     */

    /**
     * display the unique id fields
     */
    function dsp_id(): string
    {
        $result = '';

        if ($this->name <> '') {
            $result .= '"' . $this->name . '"';
            if ($this->id > 0) {
                $result .= ' (' . $this->id . ')';
            }
        } else {
            $result .= $this->id;
        }
        if (isset($this->usr)) {
            $result .= ' for user ' . $this->usr->id . ' (' . $this->usr->name . ')';
        }
        return $result;
    }

    /*
     * classification
     */

    /**
     * @return bool true if this term is a word or supposed to be a word
     */
    function is_word(): bool
    {
        $result = false;
        if (isset($this->obj)) {
            if (get_class($this->obj) == word::class or get_class($this->obj) == word_dsp::class) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * @return bool true if this term is a triple or supposed to be a triple
     */
    private function is_triple(): bool
    {
        $result = false;
        if (isset($this->obj)) {
            if (get_class($this->obj) == word_link::class) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * @return bool true if this term is a formula or supposed to be a triple
     */
    private function is_formula(): bool
    {
        $result = false;
        if (isset($this->obj)) {
            if (get_class($this->obj) == formula::class) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * @return bool true if this term is a verb or supposed to be a triple
     */
    private function is_verb(): bool
    {
        $result = false;
        if (isset($this->obj)) {
            if (get_class($this->obj) == formula::class) {
                $result = true;
            }
        }
        return $result;
    }

    /*
     * conversion
     */

    private function get_word(): word
    {
        $wrd = new word($this->usr);
        if (get_class($this->obj) == word::class) {
            $wrd = $this->obj;
        }
        return $wrd;
    }

    private function get_triple(): word_link
    {
        $lnk = new word_link($this->usr);
        if (get_class($this->obj) == word_link::class) {
            $lnk = $this->obj;
        }
        return $lnk;
    }

    private function get_formula(): formula
    {
        $frm = new formula($this->usr);
        if (get_class($this->obj) == formula::class) {
            $frm = $this->obj;
        }
        return $frm;
    }

    private function get_verb(): verb
    {
        $vrb = new verb();
        if (get_class($this->obj) == verb::class) {
            $vrb = $this->obj;
        }
        return $vrb;
    }

    /*
     * casting objects
     */

    /**
     * @return term_api the term frontend api object
     */
    function api_obj(): term_api
    {
        if ($this->is_word()) {
            return $this->get_word()->api_obj()->term();
        } elseif ($this->is_triple()) {
            return $this->get_triple()->api_obj()->term();
        } elseif ($this->is_formula()) {
            return $this->get_formula()->api_obj()->term();
        } elseif ($this->is_verb()) {
            return $this->get_verb()->api_obj()->term();
        } else {
            log_warning('Term ' . $this->dsp_id() . ' is of unknown type');
            return (new term_api());
        }
    }

    /**
     * @return term_dsp the phrase object with the display interface functions
     */
    function dsp_obj(): term_dsp
    {
        if ($this->is_word()) {
            return $this->get_word()->dsp_obj()->term();
        } elseif ($this->is_triple()) {
            return $this->get_triple()->dsp_obj()->term();
        } elseif ($this->is_formula()) {
            return $this->get_formula()->dsp_obj()->term();
        } elseif ($this->is_verb()) {
            return $this->get_verb()->dsp_obj()->term();
        } else {
            log_warning('Term ' . $this->dsp_id() . ' is of unknown type');
            return (new term_dsp());
        }
    }

    /*
     * load functions
     */

    /**
     * test if the name is used already and load the object
     * @param bool $including_word_links
     * @return int the id of the object found and zero if nothing is found
     */
    function load(bool $including_word_links = true): int
    {
        log_debug('term->load (' . $this->name . ')');
        $result = 0;

        if ($this->load_word()) {
            $result = $this->obj->id;
        } elseif ($this->load_triple($including_word_links)) {
            $result = $this->obj->id;
        } elseif ($this->load_formula()) {
            $result = $this->obj->id;
        } elseif ($this->load_verb()) {
            $result = $this->obj->id;
        }
        log_debug('term->load loaded id "' . $this->id . '" for ' . $this->name);

        return $result;
    }

    /**
     * simply load a word
     * (separate functions for loading  for a better overview)
     */
    private function load_word(): bool
    {
        $result = false;
        $wrd = new word($this->usr);
        $wrd->name = $this->name;
        if ($wrd->load()) {
            log_debug('term->load word type is "' . $wrd->type_id . '" and the formula type is ' . cl(db_cl::WORD_TYPE, phrase_type::FORMULA_LINK));
            if ($wrd->type_id == cl(db_cl::WORD_TYPE, phrase_type::FORMULA_LINK)) {
                $result = $this->load_formula();
            } else {
                $this->id = $wrd->id;
                $this->type = word::class;
                $this->obj = $wrd;
                $result = true;
            }
        }
        return $result;
    }

    /**
     * simply load a triple
     */
    private function load_triple(bool $including_word_links): bool
    {
        $result = false;
        if ($including_word_links) {
            $lnk = new word_link($this->usr);
            $lnk->name = $this->name;
            if ($lnk->load()) {
                $this->id = $lnk->id;
                //$this->type = word_link::class;
                $this->type = 'triple';
                $this->obj = $lnk;
                $result = true;
            }
        }
        return $result;
    }

    /**
     * simply load a formula
     * without fixing any missing related word issues
     */
    private function load_formula(): bool
    {
        $result = false;
        $frm = new formula($this->usr);
        $frm->name = $this->name;
        if ($frm->load(false)) {
            $this->id = $frm->id;
            $this->type = formula::class;
            $this->obj = $frm;
            $result = true;
        }
        return $result;
    }

    /**
     * simply load a verb
     */
    private function load_verb(): bool
    {
        $result = false;
        $vrb = new verb;
        $vrb->name = $this->name;
        $vrb->usr = $this->usr;
        if ($vrb->load()) {
            $this->id = $vrb->id;
            $this->type = verb::class;
            $this->obj = $vrb;
            $result = true;
        }
        return $result;
    }

    /*
    * user interface language specific functions
    */

    /**
     * create a message text that the name is already used
     */
    function id_used_msg(): string
    {
        $result = "";

        if ($this->id > 0) {
            $result = dsp_err('A ' . $this->type . ' with the name "' . $this->name . '" already exists. Please use another name.');
        }

        return $result;
    }

}