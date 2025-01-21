<?php

/*

    api/formula/formula.php - the minimal formula object for the frontend API
    -----------------------


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

namespace api\formula;

include_once API_SANDBOX_PATH . 'sandbox_typed.php';
include_once API_PHRASE_PATH . 'term.php';
include_once API_WORD_PATH . 'word.php';
include_once API_VERB_PATH . 'verb.php';
include_once SHARED_PATH . 'words.php';

use api\phrase\term as term_api;
use api\sandbox\sandbox_typed as sandbox_typed_api;
use api\word\word as word_api;
use api\verb\verb as verb_api;
use shared\words;

class formula extends sandbox_typed_api
{

    /*
     * object vars
     */

    // the formula expression as shown to the user
    private string $user_text;


    /*
     * construct and map
     */

    function __construct(int $id = 0, string $name = '')
    {
        parent::__construct($id, $name);
        $this->user_text = '';
    }

    /*
     * set and get
     */

    function set_usr_text(?string $usr_text): void
    {
        if ($usr_text != null) {
            $this->user_text = $usr_text;
        } else {
            $this->user_text = '';
        }
    }

    function usr_text(): string
    {
        return $this->user_text;
    }


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
     * @return array with the formula vars without empty values that are not needed
     */
    function jsonSerialize(): array
    {
        $vars = parent::jsonSerialize();
        $vars = array_merge($vars, get_object_vars($this));
        return array_filter($vars, fn($value) => !is_null($value) && $value !== '');
    }

}
