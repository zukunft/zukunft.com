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

    // simply load a formula (separate function, because used twice)
    private function load_frm(): bool
    {
        log_debug('term->load_frm for "' . $this->name . '"');
        $result = false;
        $frm = new formula;
        $frm->name = $this->name;
        $frm->usr = $this->usr;
        $frm->load();
        if ($frm->id > 0) {
            $this->id = $frm->id;
            $this->type = 'formula';
            $this->obj = $frm;
            $result = true;
        }
        log_debug('term->load_frm loaded id "' . $this->id . '"');
        return $result;
    }

    // test if the name is used already
    // returns the id of the object found
    function load(): int
    {
        log_debug('term->load (' . $this->name . ')');
        $result = 0;

        // test the word
        $wrd = new word_dsp;
        $wrd->name = $this->name;
        $wrd->usr = $this->usr;
        $wrd->load();
        if ($wrd->id > 0) {
            log_debug('term->load word type is "' . $wrd->type_id . '" and the formula type is ' . cl(DBL_WORD_TYPE_FORMULA_LINK));
            if ($wrd->type_id == cl(DBL_WORD_TYPE_FORMULA_LINK)) {
                if ($this->load_frm()) {
                    $result = $this->obj->id;
                }
            } else {
                $this->id = $wrd->id;
                $this->type = 'word';
                $this->obj = $wrd;
                $result = $wrd->id;
            }
        } else {
            $lnk = new word_link;
            $lnk->name = $this->name;
            $lnk->usr = $this->usr;
            $lnk->load();
            if ($lnk->id > 0) {
                $this->id = $lnk->id;
                $this->type = 'triple';
                $this->obj = $lnk;
                $result = $lnk->id;
            } else {
                $vrb = new verb;
                $vrb->name = $this->name;
                $vrb->usr = $this->usr;
                $vrb->load();
                if ($vrb->id > 0) {
                    $this->id = $vrb->id;
                    $this->type = 'verb';
                    $this->obj = $vrb;
                    $result = $vrb->id;
                } else {
                    if ($this->load_frm()) {
                        $result = $this->obj->id;
                    }
                }
            }
        }
        log_debug('term->load loaded id "' . $this->id . '" for ' . $this->name);

        return $result;
    }

    // create a message text that the name is already used
    function id_used_msg(): string
    {
        $result = "";

        if ($this->id > 0) {
            $result = dsp_err('A ' . $this->type . ' with the name "' . $this->name . '" already exists. Please use another name.');
        }

        return $result;
    }

}