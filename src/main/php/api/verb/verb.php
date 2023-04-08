<?php

/*

    api/verb/verb.php - the verb object for the frontend API
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

include_once API_SANDBOX_PATH . 'sandbox_named.php';
include_once MODEL_VERB_PATH . 'verb.php';
include_once WEB_PHRASE_PATH . 'term.php';

use model\verb;
use html\term_dsp;

class verb_api extends sandbox_named_api
{

    /*
     * const for system testing
     */

    // already coded verb names or persevered verbs names for unit and integration tests
    const TN_READ = "not set";
    const TC_READ = "not_set";
    const TN_IS_A = "is a";
    const TC_IS_A = "is";


    /*
     * cast
     */

    function term(): term_api
    {
        return new term_api($this);
    }


    /*
     * interface
     */

    /**
     * @return string the json api message as a text string
     */
    function get_json(): string
    {
        return json_encode($this->jsonSerialize());
    }

    /**
     * an array of the verb vars without empty values that are not needed
     */
    function jsonSerialize(): array
    {
        $vars = json_decode(json_encode($this), true);
        return array_filter($vars, fn($value) => !is_null($value) && $value !== '');
    }

}
