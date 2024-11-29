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

namespace api\word;

include_once SHARED_TYPES_PATH . 'phrase_type.php';
include_once SHARED_PATH . 'json_fields.php';

use shared\api;
use api\word\word as word_api;
use api\phrase\phrase as phrase_api;
use api\phrase\term as term_api;
use api\sandbox\sandbox_typed as sandbox_typed_api;
use api\verb\verb as verb_api;
use cfg\phrase_type;
use cfg\word as word_cfg;
use shared\json_fields;
use shared\types\phrase_type AS phrase_type_shared;

class triple extends sandbox_typed_api
{

    /*
     * const for system testing
     */

    // triple names for stand-alone unit tests that are added with the system initial data load
    // TN_* is the name of the triple used for testing
    // TD_* is the tooltip/description of the triple
    const TN_READ = 'Mathematical constant';
    const TI_READ = 1;
    const TD_READ = 'A mathematical constant that never changes e.g. Pi';
    const TN_PI = 'Pi';
    const TN_CUBIC_METER = 'm3';
    const TN_PI_NAME = 'Pi (math)';
    const TI_PI = 2;
    const TD_PI = 'ratio of the circumference of a circle to its diameter';
    const TN_E = '𝑒 (math)';
    const TI_E = 3;
    const TD_E = 'Is the limit of (1 + 1/n)^n as n approaches infinity';
    const TN_ADD = 'System Test Triple';
    const TN_ADD_AUTO = 'System Test Triple';
    const TN_EXCLUDED = 'System Test Excluded Zurich Insurance is not part of the City of Zurich';
    const TN_ADD_VIA_FUNC = 'System Test Triple added via sql function';
    const TN_ADD_VIA_SQL = 'System Test Triple added via prepared sql insert';

    const TN_ZH_CITY = 'Zurich (City)';
    const TI_ZH_CITY = 38;
    const TN_ZH_CITY_NAME = 'City of Zurich';
    const TN_BE_CITY = 'Bern (City)';
    const TI_BE_CITY = 39;
    const TN_GE_CITY = 'Geneva (City)';
    const TI_GE_CITY = 40;
    const TN_ZH_CANTON = 'Zurich (Canton)';
    const TN_ZH_CANTON_NAME = 'Canton Zurich';
    const TN_ZH_COMPANY = "Zurich Insurance";
    const TN_VESTAS_COMPANY = "Vestas SA";
    const TN_ABB_COMPANY = "ABB (Company)";
    const TN_2014_FOLLOW = "2014 is follower of 2013";
    const TN_TAXES_OF_CF = "Income taxes is part of cash flow statement";

    // list of predefined triple used for system testing that are expected to be never renamed
    const RESERVED_NAMES = array(
        word_cfg::SYSTEM_CONFIG,
        self::TN_ADD,
        self::TN_EXCLUDED
    );

    // array of triple names that used for db read testing and that should not be renamed
    const FIXED_NAMES = array(
        self::TN_READ
    );

    // list of triples that are used for system testing that should be removed are the system test has been completed
    // and that are never expected to be used by a user
    const TEST_TRIPLES = array(
        self::TN_ADD,
        self::TN_ADD_VIA_FUNC,
        self::TN_ADD_VIA_SQL
    );

    const TEST_TRIPLE_STANDARD = array(
        self::TN_ADD,
        self::TN_EXCLUDED
    );


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
        $this->set($from, $verb, $to);
    }


    /*
     * set and get
     */

    function set(string $from, string $verb, string $to): void
    {
        $this->set_from(new phrase_api(new word_api(0, $from)));
        $this->set_verb(new verb_api(0, $verb));
        $this->set_to(new phrase_api(new word_api(0, $to)));
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

    function set_predicate_id(?int $predicate_id): void
    {
        $this->verb->id = $predicate_id;
    }

    function predicate_id(): ?int
    {
        return $this->verb()->id();
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
        global $phr_typ_cac;
        $result = false;
        if ($this->type_id() != Null) {
            if ($this->type_id() == $phr_typ_cac->id($type)) {
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
        return $this->is_type(phrase_type_shared::TIME);
    }

    /**
     * @return bool true if the word has the type "time" e.g. "monthly"
     */
    function is_time_jump(): bool
    {
        return $this->is_type(phrase_type_shared::TIME_JUMP);
    }

    /**
     * @return bool true if the word has the type "measure" (e.g. "meter" or "CHF")
     * in case of a division, these words are excluded from the result
     * in case of add, it is checked that the added value does not have a different measure
     */
    function is_measure(): bool
    {
        return $this->is_type(phrase_type_shared::MEASURE);
    }

    /**
     * @return bool true if the word has the type "scaling" (e.g. "a million", "a million" or "one"; "one" is a hidden scaling type)
     */
    function is_scaling(): bool
    {
        $result = false;
        if ($this->is_type(phrase_type_shared::SCALING)
            or $this->is_type(phrase_type_shared::SCALING_HIDDEN)) {
            $result = true;
        }
        return $result;
    }

    /**
     * @return bool true if the word has the type "scaling_percent" (e.g. "percent")
     */
    function is_percent(): bool
    {
        return $this->is_type(phrase_type_shared::PERCENT);
    }

    /**
     * @return bool true if the word is normally not shown to the user e.g. scaling of one is assumed
     */
    function is_hidden(): bool
    {
        $result = false;
        if ($this->is_type(phrase_type_shared::SCALING_HIDDEN)) {
            $result = true;
        }
        return $result;
    }


    /*
     * interface
     */

    /**
     * @return array with the triple vars without empty values that are not needed
     * the message from the backend to the frontend does not need to include empty fields
     * the message from the frontend to the backend on the other side must include empty fields
     * to be able to unset fields in the backend
     */
    function jsonSerialize(): array
    {
        $vars = parent::jsonSerialize();
        if ($this->from()->id() != 0) {
            $vars[json_fields::FROM] = $this->from()->id();
        }
        if ($this->verb()->id() != 0) {
            $vars[json_fields::VERB] = $this->verb()->id();
        }
        if ($this->to()->id() != 0) {
            $vars[json_fields::TO] = $this->to()->id();
        }
        return array_filter($vars, fn($value) => !is_null($value) && $value !== '');
    }

}
