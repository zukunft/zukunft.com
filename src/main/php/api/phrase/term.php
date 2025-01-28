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

namespace api\phrase;

include_once API_SANDBOX_PATH . 'combine_named.php';
include_once API_WORD_PATH . 'word.php';
include_once API_WORD_PATH . 'triple.php';
include_once API_VERB_PATH . 'verb.php';
include_once WEB_WORD_PATH . 'word.php';
include_once WEB_WORD_PATH . 'triple.php';
include_once WEB_FORMULA_PATH . 'formula.php';
include_once WEB_VERB_PATH . 'verb.php';
include_once WEB_PHRASE_PATH . 'term.php';
include_once SHARED_PATH . 'json_fields.php';

use api\sandbox\combine_named as combine_named_api;
use html\phrase\phrase as phrase_dsp;
use html\word\word as word_dsp;
use JsonSerializable;

class term extends combine_named_api implements JsonSerializable
{

    /*
     * set and get
     */

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
            $this->set_obj_id(abs($id) / 2);
        } else {
            $this->set_obj_id((abs($id) + 1) / 2);
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

}
