<?php

/*

    api/phrase/phrase.php - the minimal phrase object for the frontend API
    ---------------------

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
use html\phrase_dsp;
use html\triple_dsp;
use html\verb_dsp;
use html\word_dsp;

class phrase_api extends user_sandbox_named_api
{

    // phrase names for stand-alone unit tests that are added with the system initial data load
    // TN_* is the name of the phrase used for testing
    // TD_* is the tooltip/description of the phrase
    const TN_ZH_CITY_NAME = 'City of Zurich';
    const TN_ZH_CITY = 'Zurich (City)';
    const TN_ZH_CANTON_NAME = 'Canton Zurich';
    const TN_ZH_CANTON = 'Zurich (Canton)';
    const TN_ZH_COMPANY = "System Test Phrase: Zurich Insurance";

    const RESERVED_PHRASES = array(
        self::TN_ZH_CANTON,
        self::TN_ZH_CITY,
        self::TN_ZH_COMPANY
    );
    const TEST_TRIPLE_STANDARD = array(
        self::TN_ZH_CANTON,
        self::TN_ZH_CITY
    );

    // used only if the phrase is a triple
    private ?triple_api $triple;

    // the type of this phrase
    private ?int $type_id;

    /*
     * construct and map
     */

    function __construct(
        int    $id = 0,
        string $name = '',
        string $from = '',
        string $verb = '',
        string $to = '')
    {
        global $phrase_types;

        parent::__construct($id, $name);
        if ($from != '' and $to != '') {
            $this->triple = new triple_api($id, $name, $from, $verb, $to);
        }
        $this->set_type($phrase_types->default_id());
    }

    /**
     * reset the in memory fields used e.g. if some ids are updated
     */
    function reset(): void
    {
        $this->description = null;
        $this->triple = null;
        $this->set_type(null);
    }


    /*
     * set and get
     */

    function set_description(?string $description)
    {
        $this->description = $description;
    }

    function description(): ?string
    {
        return $this->description;
    }

    function set_type(?int $type_id)
    {
        $this->type_id = $type_id;
    }

    function type(): ?int
    {
        return $this->type_id;
    }


    /*
     * cast
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
        $wrd = new word_dsp($this->id, $this->name);
        $wrd->set_type_id($this->type());
        return $wrd;
    }

    protected function trp_dsp(): triple_dsp
    {
        $trp = new triple_dsp($this->id, $this->name);
        $trp->set_type_id($this->type());
        return $trp;
    }


    /*
     * classifications
     */

    /**
     * @return bool true if this phrase is a word or supposed to be a word
     */
    function is_word(): bool
    {
        if ($this->id > 0) {
            return true;
        } else {
            return false;
        }
    }


    /*
     * info
     */

    /**
     * @return bool true if one of the phrases that classify this value is of type percent
     */
    function is_percent(): bool
    {
        if ($this->is_word()) {
            return $this->wrd_dsp()->is_percent();
        } else {
            return $this->trp_dsp()->is_percent();
        }
    }

}
