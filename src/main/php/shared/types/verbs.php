<?php

/*

    model/sandbox/verbs.php - to use the same verb code_id in frontend and backend
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

    Copyright (c) 1995-2023 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace shared\types;

class verbs
{
    // the field name used for the JSON im- and export
    const JSON_FLD = 'verb';

    /*
     * code links
     */

    // the unique id of predicates or verbs
    // to link a db row to predefined program code
    // * tne unique code id to select the verb from the program code
    // *_NAME the name of the verb that is shown to the user
    // *_ID the fixed database id of the verb due to the initial database load
    // *_COM the tooltip description for the verb
    // TODO add a check if all verbs have a const und linked functionalities
    const NOT_SET = "not_set";
    const NOT_SET_NAME = "not set";
    const NOT_SET_ID = 1;
    const NOT_SET_COM = 'no verb / predicate selected';
    const IS = "is";
    const IS_NAME = "is a";
    const IS_ID = 2;
    const PART = "contains";
    const PART_NAME = "is part of";
    const PART_ID = 3;
    const OF = "of";
    const OF_ID = 4;
    const IS_WITH = "with";
    const FOLLOW = "follow";
    const CAN_CONTAIN = "can_contain";
    const CAN_CONTAIN_NAME = "differentiator";
    const CAN_CONTAIN_NAME_REVERSE = "of";
    const CAN_BE = "can_be";
    const CAN_USE = "can_use";
    const SELECTOR = "selector"; // the from_phrase of a selector can be used more than once so the description of the to_phrase should be shown to the user
    const TO = 'to'; // to define a time period e.g. "12:00 to 13:00" or "1. March 2024 to 3. March 2024"

    // directional forms of verbs (maybe move to verb_api or test if only used for testing)
    const FOLLOWED_BY = "is followed by";
    const FOLLOWER_OF = "is follower of";
    const SYMBOL = "symbol";
    const TIME_STEP = "time jump";
    const SYMBOL_NAME = 'is symbol for';
    const TEST_ADD_NAME = "System Test Verb";

    // search directions to get related words (phrases)
    const DIRECTION_NO = '';
    const DIRECTION_DOWN = 'down';    // or forward  to get a list of 'to' phrases
    const DIRECTION_UP = 'up';        // or backward to get a list of 'from' phrases based on a given to phrase


    // word groups for creating the test words and remove them after the test
    const RESERVED_WORDS = array(
        self::NOT_SET_NAME,
        self::IS_NAME,
        self::PART_NAME,
        self::TEST_ADD_NAME,
    );
    const TEST_WORDS = array(
        self::TEST_ADD_NAME
    );

}
