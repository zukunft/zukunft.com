<?php

/*

    api/ref/ref.php - the reference object for the frontend API
    ---------------


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

namespace api\ref;

use api\sandbox\sandbox as sandbox_api;

class ref extends sandbox_api
{

    /*
     * const for the api
     */

    const API_NAME = 'reference';


    /*
     * const for system testing
     */

    // persevered reference names for unit and integration tests
    const TN_READ = 'wikidata';
    const TN_ADD = 'System Test Reference Name';
    const TK_READ = 'Q167';
    const TU_READ = 'https://www.wikidata.org/wiki/';
    const TD_READ = 'ratio of the circumference of a circle to its diameter';

    // must be the same as in /resource/api/source/source_put.json
    const TK_ADD_API = 'System Test Reference API added';
    const TD_ADD_API = 'System Test Reference Description API';
    const TU_ADD_API = 'https://api.zukunft.com/';

    // reference group for testing
    // TODO activate Prio 3
    const RESERVED_REFERENCES = array(
        self::TN_READ
    );


    /*
     * object vars
     */

    public ?int $phrase_id;
    public ?string $external_key;
    public ?int $type_id;
    public ?int $source_id;
    public ?string $url;
    public ?string $description;


    /*
     * set and get
     */

    function set_type_id(?int $type_id): void
    {
        $this->type_id = $type_id;
    }

    /**
     * @return string the name of the reference type e.g. wikidata
     */
    function type_name(): string
    {
        global $ref_types;
        return $ref_types->name($this->type_id);
    }
}
