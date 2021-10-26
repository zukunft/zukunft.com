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
  along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.
  
  To contact the authors write to:
  Timon Zielonka <timon@zukunft.com>
  
  Copyright (c) 1995-2021 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
  TODO: load formula word
        check triple

*/

class term
{

    public ?int $id = null;      // the database id of the word, verb or formula
    public ?user $usr = null;    // the person who wants to add a term (word, verb or formula)
    public ?string $type = null; // either "word", "verb" or "formula"
    public ?string $name = null; // the name used (must be unique for words, verbs and formulas)
    public ?object $obj = null;  // the word, verb or formula object

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
        $wrd = new word_dsp;
        $wrd->name = $this->name;
        $wrd->usr = $this->usr;
        if ($wrd->load()) {
            log_debug('term->load word type is "' . $wrd->type_id . '" and the formula type is ' . cl(db_cl::WORD_TYPE, word_type_list::DBL_FORMULA_LINK));
            if ($wrd->type_id == cl(db_cl::WORD_TYPE, word_type_list::DBL_FORMULA_LINK)) {
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
            $lnk = new word_link;
            $lnk->name = $this->name;
            $lnk->usr = $this->usr;
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
     */
    private function load_formula(): bool
    {
        $result = false;
        $frm = new formula;
        $frm->name = $this->name;
        $frm->usr = $this->usr;
        if ($frm->load()) {
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