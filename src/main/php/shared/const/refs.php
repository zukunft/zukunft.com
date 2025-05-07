<?php

/*

    shared/const/refs.php - references used by the system for testing
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

    Copyright (c) 1995-2025 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace shared\const;

class refs
{

    // references used by the system for testing
    // persevered reference names for unit and integration tests
    // *_TYPE is the code_id of the reference type
    // *_KEY is the unique key/id in the external system
    // *_COM is the tooltip/description of the link to the external reference
    // *_ID the fixed database due to the initial setup
    // *_URL is the url overwrite for this reference
    const WIKIDATA_TYPE = 'wikidata';
    const PI_COM = 'pi - ratio of the circumference of a circle to its diameter';
    const PI_KEY = 'Q167';
    const PI_ID = 21;
    const PI_URL = 'https://www.wikidata.org/wiki/Special:EntityData/Q167.json';

    // to test changing the external key of a reference
    const CHANGE_OLD_KEY = 'Q901028';
    const CHANGE_COM = 'global warming potential - estimate of how an atmospheric gas affects global climate change';
    const CHANGE_NEW_KEY = 'Q999999999';

    // to test adding a new reference type
    const SYSTEM_TEST_ADD_COM = 'System Test Reference Type';

    // must be the same as in /resource/api/source/source_put.json
    const SYSTEM_TEST_API_ADD_KEY = 'System Test Reference API added';
    const SYSTEM_TEST_API_ADD_COM = 'System Test Reference Description API';
    const SYSTEM_TEST_API_ADD_URL = 'https://api.zukunft.com/';

    // reference group for testing
    // TODO activate Prio 3
    const RESERVED_REFERENCES_TYPES = array(
        self::SYSTEM_TEST_ADD_COM
    );

}
