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
     * const for system testing
     */

    // references for stand-alone unit tests that are added with the system initial data load
    // TN_* is the name of the word used for testing created with the initial setup (see also TWN_*)
    // TI_* is the database id based on the initial load
    // TD_* is the tooltip/description of the word
    const TI_PI = 5;


    /*
     * const for the api
     */

    const API_NAME = 'reference';


    /*
     * const for system testing
     */

    // persevered reference names for unit and integration tests
    // TT_* is the code_id of the reference type
    // TD_* is the description of the link to the external reference
    // TK_* is the unique key/id in the external system
    // TU_* is the url overwrite for this reference
    const TT_READ = 'wikidata';
    const TD_READ = 'pi - ratio of the circumference of a circle to its diameter';
    const TK_READ = 'Q167';
    const TU_READ = 'https://www.wikidata.org/wiki/Special:EntityData/Q167.json';
    // to test changing the external key of a reference
    const TT_CHANGE = 'wikidata';
    const TD_CHANGE = 'Q901028';
    const TK_CHANGE = 'global warming potential - estimate of how an atmospheric gas affects global climate change';
    const TK_CHANGED = 'Q999999999';

    // to test adding a new reference type
    const TT_ADD = 'System Test Reference Type';

    // must be the same as in /resource/api/source/source_put.json
    const TK_ADD_API = 'System Test Reference API added';
    const TD_ADD_API = 'System Test Reference Description API';
    const TU_ADD_API = 'https://api.zukunft.com/';
    const TK_ADD = 'Q168';

    // reference group for testing
    // TODO activate Prio 3
    const RESERVED_REFERENCES_TYPES = array(
        self::TT_ADD
    );


    /*
     * object vars
     */

    public ?int $phrase_id;
    public ?string $external_key;
    public ?int $predicate_id;
    public ?int $source_id;
    public ?string $url;
    public ?string $description;


    /*
     * set and get
     */

    function set_predicate_id(?int $predicate_id): void
    {
        $this->predicate_id = $predicate_id;
    }


    /*
     * preloaded
     */

    /**
     * @return string the name of the reference type e.g. wikidata
     */
    function predicate_name(): string
    {
        global $ref_types;
        return $ref_types->name($this->predicate_id);
    }

}
