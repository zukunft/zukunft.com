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

include_once API_SANDBOX_PATH . 'combine_named.php';
include_once API_WORD_PATH . 'word.php';
include_once API_WORD_PATH . 'triple.php';
include_once WEB_WORD_PATH . 'word.php';
include_once WEB_WORD_PATH . 'triple.php';
include_once WEB_PHRASE_PATH . 'phrase.php';

use html\word_dsp;
use html\triple_dsp;
use html\phrase_dsp;
use JsonSerializable;

class phrase_api extends combine_named_api implements JsonSerializable
{

    // the json field name in the api json message to identify if the figure is a value or result
    const CLASS_WORD = 'word';
    const CLASS_TRIPLE = 'triple';

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
        if ($id < 0) {
            $this->set_obj(new triple_api($id * -1, $name, $from, $verb, $to));
        } else {
            $this->set_obj(new word_api($id, $name));
        }
        $this->set_type_id($phrase_types->default_id());
        parent::__construct($id, $name);
    }

    /**
     * reset the in memory fields used e.g. if some ids are updated
     */
    function reset(): void
    {
        $this->set_id(0);
        $this->set_name(null);
        $this->set_description(null);
        $this->set_type_id(null);
    }


    /*
     * set and get
     */

    function set_id(int $id): void
    {
        if ($this->is_word()) {
            $this->obj()?->set_id($id);
        } else {
            $this->obj()?->set_id($id * -1);
        }
    }

    function id(): int
    {
        return $this->obj()?->id();
    }


    /*
     * cast
     */

    /**
     * @returns phrase_dsp the cast object with the HTML code generating functions
     */
    function dsp_obj(): phrase_dsp
    {
        if ($this->is_word()) {
            $dsp_obj = $this->wrd_dsp()->phrase();
        } else {
            $dsp_obj = $this->trp_dsp()->phrase();
        }
        return $dsp_obj;
    }

    protected function wrd_dsp(): word_dsp
    {
        $wrd = new word_dsp($this->id(), $this->name());
        $wrd->set_type_id($this->type_id());
        return $wrd;
    }

    protected function trp_dsp(): triple_dsp
    {
        $trp = new triple_dsp($this->id() * -1, $this->name());
        $trp->set_type_id($this->type_id());
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
        if ($this->obj()::class == word_api::class) {
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
        } else {
            $vars[combine_object_api::FLD_CLASS] = self::CLASS_TRIPLE;
        }
        return $vars;
    }

}
