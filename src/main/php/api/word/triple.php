<?php

/*

    api/word/triple.php - the minimal triple (triple) object
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

use cfg\phrase_type;
use html\phrase\term as term_dsp;
use model\triple;

class triple_api extends sandbox_typed_api
{

    /*
     * const for system testing
     */

    // triple names for stand-alone unit tests that are added with the system initial data load
    // TN_* is the name of the triple used for testing
    // TD_* is the tooltip/description of the triple
    const TN_READ = 'Pi';
    const TN_READ_NAME = 'Pi (math)';
    const TN_EXCLUDED = 'System Test Excluded Zurich Insurance is not part of the City of Zurich';

    const TN_ZH_CITY = 'Zurich (City)';
    const TN_ZH_CITY_NAME = 'City of Zurich';
    const TN_ZH_CANTON = 'Zurich (Canton)';
    const TN_ZH_CANTON_NAME = 'Canton Zurich';
    const TN_ZH_COMPANY = "Zurich Insurance";
    const TN_VESTAS_COMPANY = "Vestas SA";


    /*
     * object vars
     */

    // the triple components
    private phrase_api $from;
    private verb_api $verb;
    private phrase_api $to;


    /*
     * construct and map
     */

    function __construct(
        int    $id = 0,
        string $name = '',
        string $from = '',
        string $verb = '',
        string $to = ''
    )
    {
        parent::__construct($id, $name);
        if ($from != '' or $verb != '' or $to != '') {
            $this->set($from, $verb, $to);
        }
    }


    /*
     * set and get
     */

    function set(string $from, string $verb, string $to): void
    {
        if ($from != '') {
            $this->set_from(new phrase_api(new word_api(0, $from)));
        }
        if ($verb != '') {
            $this->set_verb(new verb_api(0, $verb));
        }
        if ($to != '') {
            $this->set_to(new phrase_api(new word_api(0, $to)));
        }
    }

    function set_from(phrase_api $from): void
    {
        $this->from = $from;
    }

    function set_verb(verb_api $vrb): void
    {
        $this->verb = $vrb;
    }

    function set_to(phrase_api $to): void
    {
        $this->to = $to;
    }

    function from(): phrase_api
    {
        return $this->from;
    }

    function verb(): verb_api
    {
        return $this->verb;
    }

    function to(): phrase_api
    {
        return $this->to;
    }


    /*
     * cast
     */

    /**
     * @return phrase_api the related phrase api or display object with the basic values filled
     */
    function phrase(): phrase_api
    {
        return new phrase_api($this);
    }

    function term(): term_api
    {
        return new term_api($this);
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
        global $phrase_types;
        $result = false;
        if ($this->type_id() != Null) {
            if ($this->type_id() == $phrase_types->id($type)) {
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
