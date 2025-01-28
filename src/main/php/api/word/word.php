<?php

/*

    api/word/word.php - the minimal word object for the backend to frontend API transfer
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

namespace api\word;

include_once API_SANDBOX_PATH . 'sandbox_typed.php';
include_once SHARED_PATH . 'triples.php';
include_once SHARED_PATH . 'words.php';
include_once SHARED_PATH . 'json_fields.php';

use api\phrase\phrase as phrase_api;
use api\phrase\term as term_api;
use api\sandbox\sandbox_typed as sandbox_typed_api;
use cfg\phrase\phrase_type;
use cfg\word\word as word_cfg;
use JsonSerializable;
use shared\json_fields;
use shared\triples;
use shared\words;

class word extends sandbox_typed_api implements JsonSerializable
{

    /*
     * object vars
     */

    // the mouse over tooltip for the word
    // a null value is needed to detect if nothing has been changed by the user
    public ?string $description = null;

    // the language specific forms
    // TODO switch to public to avoid jsonSerialize usage ?
    private ?string $plural = null;

    // the main parent phrase
    private ?phrase_api $parent;


    /*
     * construct and map
     */

    function __construct(int $id = 0, string $name = '', ?string $description = null)
    {
        parent::__construct($id, $name);
        $this->description = $description;
        $this->parent = null;
        $this->type_id = null;
    }



}
