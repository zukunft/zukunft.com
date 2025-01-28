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

include_once API_SANDBOX_PATH . 'sandbox_typed.php';
include_once SHARED_TYPES_PATH . 'phrase_type.php';
include_once SHARED_PATH . 'words.php';
include_once SHARED_PATH . 'json_fields.php';

use api\word\word as word_api;
use api\phrase\phrase as phrase_api;
use api\phrase\term as term_api;
use api\sandbox\sandbox_typed as sandbox_typed_api;
use api\verb\verb as verb_api;
use cfg\word\word as word_cfg;
use shared\json_fields;
use shared\triples;
use shared\types\phrase_type as phrase_type_shared;
use shared\words;

class triple extends sandbox_typed_api
{

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

}
