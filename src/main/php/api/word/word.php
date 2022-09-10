<?php

/*

    api\word\word.php - the minimal word object for the backend to frontend API transfer
    -----------------


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
use html\word_dsp;
use word_type;

class word_api extends user_sandbox_named_api
{
    // word names for stand-alone unit tests
    // for database based test words see model/word/word.php
    const TN_ZH = 'Zurich';
    const TN_CITY = 'City';
    const TN_CANTON = 'Canton';
    const TN_CH = 'Switzerland';
    const TN_INHABITANT = 'inhabitant';
    const TN_2019 = '2019';
    const TN_ONE = 'one';
    const TN_MIO = 'mio';
    const TN_PCT = 'percent';
    const TN_CONST = 'Pi';

    // the mouse over tooltip for the word
    protected ?string $description = null;

    // the language specific forms
    private ?string $plural = null;

    // the main parent phrase
    private ?phrase_api $parent;

    // repeat the type in the frontend object for faster selection
    private ?\word_type $type;

    /*
     * construct and map
     */

    function __construct(int $id = 0, string $name = '', string $description = '')
    {
        parent::__construct($id, $name);
        $this->description = $description;
        $this->parent = null;
        $this->type = null;
    }

    /*
     * get and set
     */

    public function set_description(string $description): void
    {
        $this->description = $description;
    }

    function description(): string
    {
        return $this->description;
    }

    public function set_plural(string $plural): void
    {
        $this->plural = $plural;
    }

    function plural(): ?string
    {
        return $this->plural;
    }

    public function set_parent(?phrase_api $parent): void
    {
        $this->parent = $parent;
    }

    function parent(): ?phrase_api
    {
        return $this->parent;
    }

    /**
     * TODO use ENUM instead of string in php version 8.1
     * @param string $type
     * @return void
     */
    public function set_type(string $type): void
    {
        $this->type = new word_type($type);
    }

    function type(): string
    {
        if ($this->type == null) {
            return '';
        } else {
            return $this->type->code_id();
        }
    }

    /*
     * casting objects
     */

    /**
     * @returns word_dsp the cast object with the HTML code generating functions
     */
    function dsp_obj(): word_dsp
    {
        $wrd_dsp = new word_dsp($this->id, $this->name, $this->description);
        $wrd_dsp->plural = $this->plural;
        $wrd_dsp->type = $this->type;
        if ($this->parent != null) {
            $wrd_dsp->parent = $this->parent->dsp_obj();
        }
        return $wrd_dsp;
    }

    function phrase(): phrase_api
    {
        return new phrase_api($this->id, $this->name);
    }

    /*
     * type functions
     */

    /**
     * repeating of the backend functions in the frontend to enable filtering in the frontend and reduce the traffic
     * repeated in triple, because a triple can have it's own type
     * kind of repeated in phrase to use hierarchies
     *
     * @param string $type the ENUM string of the fixed type
     * @returns bool true if the word has the given type
     * TODO Switch to php 8.1 and real ENUM
     */
    function is_type(string $type): bool
    {
        $result = false;
        if ($this->type != Null) {
            if ($this->type->code_id == $type) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * @return bool true if the word has the type "time" e.g. "2022 (year)"
     */
    function is_time(): bool
    {
        return $this->is_type(phrase_type::TIME);
    }

    /**
     * @return bool true if the word has the type "time" e.g. "monthly"
     */
    function is_time_jump(): bool
    {
        return $this->is_type(phrase_type::TIME_JUMP);
    }

    /**
     * @return bool true if the word has the type "measure" (e.g. "meter" or "CHF")
     * in case of a division, these words are excluded from the result
     * in case of add, it is checked that the added value does not have a different measure
     */
    function is_measure(): bool
    {
        return $this->is_type(phrase_type::MEASURE);
    }

    /**
     * @return bool true if the word has the type "scaling" (e.g. "million", "million" or "one"; "one" is a hidden scaling type)
     */
    function is_scaling(): bool
    {
        $result = false;
        if ($this->is_type(phrase_type::SCALING)
            or $this->is_type(phrase_type::SCALING_HIDDEN)) {
            $result = true;
        }
        return $result;
    }

    /**
     * @return bool true if the word has the type "scaling_percent" (e.g. "percent")
     */
    function is_percent(): bool
    {
        return $this->is_type(phrase_type::PERCENT);
    }

    /**
     * @return bool true if the word is normally not shown to the user e.g. scaling of one is assumed
     */
    function is_hidden(): bool
    {
        $result = false;
        if ($this->is_type(phrase_type::SCALING_HIDDEN)) {
            $result = true;
        }
        return $result;
    }

}
