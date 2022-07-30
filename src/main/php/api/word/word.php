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

use html\word_dsp;

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
    const TN_MIO = 'mio';
    const TN_PCT = 'percent';

    // the mouse over tooltip for the word
    protected ?string $description = null;

    // the language specific forms
    private ?string $plural = null;

    // the main parent phrase
    private ?phrase_api $parent;

    /*
     * construct and map
     */

    function __construct(int $id = 0, string $name = '', string $description = '')
    {
        parent::__construct($id, $name);
        $this->description = $description;
        $this->parent = null;
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
        if ($this->parent != null) {
            $wrd_dsp->parent = $this->parent->dsp_obj();
        }
        return $wrd_dsp;
    }

    function phrase(): phrase_api
    {
        return new phrase_api($this->id, $this->name);
    }

}
