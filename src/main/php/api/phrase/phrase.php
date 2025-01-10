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

namespace api\phrase;

include_once API_SANDBOX_PATH . 'combine_named.php';
include_once API_WORD_PATH . 'word.php';
include_once API_WORD_PATH . 'triple.php';
include_once WEB_WORD_PATH . 'word.php';
include_once WEB_WORD_PATH . 'triple.php';
include_once WEB_PHRASE_PATH . 'phrase.php';
include_once SHARED_PATH . 'json_fields.php';

use api\sandbox\combine_named as combine_named_api;
use api\sandbox\combine_object as combine_object_api;
use api\word\triple as triple_api;
use api\word\word as word_api;
use html\word\word as word_dsp;
use html\word\triple as triple_dsp;
use html\phrase\phrase as phrase_dsp;
use JsonSerializable;
use shared\json_fields;

class phrase extends combine_named_api implements JsonSerializable
{

    // the json field name in the api json message to identify if the figure is a value or result
    const CLASS_WORD = 'word';
    const CLASS_TRIPLE = 'triple';

    // TODO move to triple API
    // phrase names used for system testing
    const RESERVED_PHRASES = array(
        triple_api::TN_ADD,
        triple_api::TN_EXCLUDED
    );
    const TEST_TRIPLE_STANDARD = array(
        triple_api::TN_ADD,
        triple_api::TN_EXCLUDED
    );


    /*
     * construct and map
     */

    function __construct(word_api|triple_api $obj)
    {
        $this->set_obj($obj);
    }


    /*
     * set and get
     */

    function set_phrase_obj(word_api|triple_api $obj): void
    {
        $this->obj = $obj;
    }

    /**
     * TODO remove this logic from the API and keep it only in the model, the database view and the frontend
     *
     * set the object id based on the given phrase id
     * must have the same logic as the database view and the frontend
     * @param int $id the phrase id that is converted to the object id
     * @return void
     */
    function set_id(int $id): void
    {
        $this->set_obj_id(abs($id));
    }

    function id(): int
    {
        if ($this->is_word()) {
            return $this->obj_id();
        } else {
            return $this->obj_id() * -1;
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
        if ($this->is_word()) {
            $dsp_obj = $this->wrd_dsp()->phrase();
        } else {
            $dsp_obj = $this->trp_dsp()->phrase();
        }
        return $dsp_obj;
    }

    protected function wrd_dsp(): word_dsp
    {
        $api_json = $this->get_json();
        return new word_dsp($api_json);
    }

    protected function trp_dsp(): triple_dsp
    {
        $trp = new triple_dsp($this->get_json());
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
        $id = $this->obj_id();
        $vars[json_fields::ID] = $this->obj_id();
        if ($id != 0) {
            if ($this->is_word()) {
                $vars[json_fields::OBJECT_CLASS] = self::CLASS_WORD;
            } else {
                $vars[json_fields::OBJECT_CLASS] = self::CLASS_TRIPLE;
            }
        }
        return $vars;
    }

}
