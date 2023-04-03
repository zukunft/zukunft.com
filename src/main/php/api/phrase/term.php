<?php

/*

    api/phrase/term.php - the minimal term object for the frontend API
    -------------------

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

namespace api;

include_once API_SANDBOX_PATH . 'combine_named.php';
include_once API_WORD_PATH . 'word.php';
include_once API_WORD_PATH . 'triple.php';
include_once API_FORMULA_PATH . 'formula.php';
include_once API_VERB_PATH . 'verb.php';
include_once WEB_WORD_PATH . 'word.php';
include_once WEB_WORD_PATH . 'triple.php';
include_once WEB_FORMULA_PATH . 'formula.php';
include_once WEB_VERB_PATH . 'verb.php';
include_once WEB_PHRASE_PATH . 'term.php';

use html\phrase_dsp;
use html\word_dsp;
use html\triple_dsp;
use html\formula_dsp;
use html\verb_dsp;
use html\term_dsp;
use cfg\phrase_type;
use model\word;
use model\triple;
use model\formula;
use model\verb;
use JsonSerializable;

class term_api extends combine_named_api implements JsonSerializable
{

    // the json field name in the api json message to identify if the term is a word, triple, verb or formula
    const CLASS_WORD = 'word';
    const CLASS_TRIPLE = 'triple';
    const CLASS_VERB = 'verb';
    const CLASS_FORMULA = 'formula';


    /*
     * construct and map
     */

    function __construct(
        int    $id = 0,
        string $name = '',
        string $class = '')
    {
        if ($class == word::class) {
            $this->set_term_obj(new word_api());
        } elseif ($class == triple::class) {
            $this->set_term_obj(new triple_api());
        } elseif ($class == formula::class) {
            $this->set_term_obj(new formula_api());
        } elseif ($class == verb::class) {
            $this->set_term_obj(new verb_api());
        } else {
            $this->set_term_obj(new word_api());
        }
        parent::__construct($id, $name);
    }


    /*
     * set and get
     */

    function set_term_obj(word_api|triple_api|verb_api|formula_api $obj): void
    {
        $this->obj = $obj;
    }


    /**
     * TODO remove this logic from the API and keep it only in the model, the database view and the frontend
     *
     * set the object id based on the given term id
     * must have the same logic as the database view and the frontend
     * @param int $id the term id that is converted to the object id
     * @return void
     */
    function set_id(int $id): void
    {
        if ($id % 2 == 0) {
            $this->set_obj_id(abs($id / 2));
        } else {
            $this->set_obj_id(abs(($id + 1) / 2));
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
            return ($this->obj_id() * 2);
        } elseif ($this->is_verb()) {
            return ($this->obj_id() * -2);
        } else {
            return 0;
        }
    }


    /*
     * cast
     */

    /**
     * @returns phrase_dsp the cast object with the HTML code generating functions
     */
    function dsp_obj(): phrase_dsp
    {
        $dsp_obj = new phrase_dsp($this->obj()->dsp_obj());
        $dsp_obj->set_name($this->description());
        $dsp_obj->set_description($this->description());
        return $dsp_obj;
    }

    protected function wrd_dsp(): word_dsp
    {
        return new word_dsp($this->obj_id(), $this->name());
    }

    protected function trp_dsp(): triple_dsp
    {
        return new triple_dsp($this->obj_id(), $this->name());
    }

    protected function frm_dsp(): formula_dsp
    {
        return new formula_dsp($this->obj_id(), $this->name());
    }

    protected function vrb_dsp(): verb_dsp
    {
        return new verb_dsp($this->obj_id(), $this->name());
    }


    /*
     * classifications
     */

    /**
     * @return bool true if this term is a word or supposed to be a word
     */
    function is_word(): bool
    {
        if ($this->obj()::class == word_api::class) {
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
        if ($this->obj()::class == triple_api::class) {
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
        if ($this->obj()::class == formula_api::class) {
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
        if ($this->obj()::class == verb_api::class) {
            return true;
        } else {
            return false;
        }
    }


    /*
     * interface
     */

    /**
     * @return array with the value vars including the private vars
     */
    function jsonSerialize(): array
    {
        $vars = parent::jsonSerialize();
        if ($this->is_word()) {
            $vars[combine_object_api::FLD_CLASS] = self::CLASS_WORD;
        } elseif ($this->is_triple()) {
            $vars[combine_object_api::FLD_CLASS] = self::CLASS_TRIPLE;
        } elseif ($this->is_formula()) {
            $vars[combine_object_api::FLD_CLASS] = self::CLASS_FORMULA;
        } elseif ($this->is_verb()) {
            $vars[combine_object_api::FLD_CLASS] = self::CLASS_VERB;
        } else {
            log_err('class ' . $this->obj()::class . ' of term ' . $this->name() . ' not expected');
        }
        return $vars;
    }

}
