<?php

/*

    api\term.php - the minimal term object for the frontend API
    ------------

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

use cfg\phrase_type;
use formula;
use html\formula_dsp;
use html\phrase_dsp;
use html\triple_dsp;
use html\verb_dsp;
use html\word_dsp;
use verb;
use word;
use triple;

class term_api extends user_sandbox_named_api
{

    // the mouse over tooltip for the word
    private ?string $description = null;

    // the word, triple, verb or formula object
    private ?user_sandbox_api $obj = null;

    // the type of this phrase
    private phrase_type $type;

    /*
     * construct and map
     */

    function __construct(
        int    $id = 0,
        string $name = '',
        string $obj = null)
    {
        parent::__construct($id, $name);
        $this->set_obj_id($id, $obj);
        $this->name = $name;
        // TODO set type
        // $this->type = phrase_type::NORMAL;
    }

    /**
     * reset the in memory fields used e.g. if some ids are updated
     */
    function reset(): void
    {
        $this->description = null;
    }

    /*
     * set and get
     */

    public function set_description(?string $description)
    {
        $this->description = $description;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    /**
     * @param int $id the object id that is converted to the term id
     * @return void
     */
    function set_obj_id(int $id, string $class): void
    {
        if ($class == word::class) {
            $this->id = ($id * 2) - 1;
        } elseif ($class == triple::class) {
            $this->id = ($id * -2) + 1;
        } elseif ($class == formula::class) {
            $this->id = ($id * 2);
        } elseif ($class == verb::class) {
            $this->id = ($id * -2);
        }
    }

    /**
     * @return int the id of the containing object witch is (corresponding to id())
     * e.g 1 for a word, 1 for a phrase, 1 for a formula and 1 for a verb
     */
    function id_obj(): int
    {
        if ($this->id % 2 == 0) {
            return abs($this->id / 2);
        } else {
            return abs(($this->id + 1) / 2);
        }
    }

    /*
     * casting objects
     */

    /**
     * @returns phrase_dsp the cast object with the HTML code generating functions
     */
    function dsp_obj(): phrase_dsp
    {
        $dsp_obj = new phrase_dsp($this->id, $this->name);
        $dsp_obj->set_description($this->description());
        return $dsp_obj;
    }

    protected function wrd_dsp(): word_dsp
    {
        return new word_dsp($this->id_obj(), $this->name);
    }

    protected function trp_dsp(): triple_dsp
    {
        return new triple_dsp($this->id_obj(), $this->name);
    }

    protected function frm_dsp(): formula_dsp
    {
        return new formula_dsp($this->id_obj(), $this->name);
    }

    protected function vrb_dsp(): verb_dsp
    {
        return new verb_dsp($this->id_obj(), $this->name);
    }

    /*
     * classifications
     */

    /**
     * @return bool true if this term is a word or supposed to be a word
     */
    function is_word(): bool
    {
        if ($this->class_from_id() == word::class) {
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
        if ($this->class_from_id() == triple::class) {
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
        if ($this->class_from_id() == formula::class) {
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
        if ($this->class_from_id() == verb::class) {
            return true;
        } else {
            return false;
        }
    }

    private function class_from_id(): string
    {
        if ($this->id % 2 != 0) {
            if ($this->id > 0) {
                return word::class;
            } else {
                return triple::class;
            }
        } else {
            if ($this->id > 0) {
                return formula::class;
            } else {
                return verb::class;
            }
        }
    }

}
