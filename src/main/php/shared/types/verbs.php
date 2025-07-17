<?php

/*

    shared/types/verbs.php - to use the same verb code_id in frontend and backend
    ----------------------

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
    const OF_NAME = "of";
    const OF_ID = 4;
    const ON = "on";
    const ON_NAME = "on";
    const ON_ID = 4;
    const WITH = "with";
    const WITH_NAME = "with";
    const WITH_ID = 5;
    const FOLLOW = "follow";
    const FOLLOW_NAME = "is follower of";
    const FOLLOW_ID = 10;
    const MEASURE = "measure_type";
    const MEASURE_NAME = "is measure type for";
    const MEASURE_ID = 14;
    const ALIAS = "alias";
    const ALIAS_NAME = "is alias of";
    const ALIAS_ID = 18;
    const CAN_CONTAIN = "can_contain";
    const CAN_CONTAIN_NAME = "differentiator";
    const CAN_CONTAIN_NAME_REVERSE = "of";
    const CAN_BE = "can_be";
    const CAN_BE_NAME = "can be";
    const CAN_BE_ID = 18;
    const CAN_GET = "can_get";
    const CAN_GET_NAME = "can get";
    const CAN_GET_ID = 19;
    const CAN_CAUSE = "can_cause";
    const CAN_CAUSE_NAME = "can cause";
    const CAN_CAUSE_ID = 22;
    const CAN_USE = "can_use";
    const CAN_USE_NAME = "can use";
    const CAN_USE_ID = 24;
    const SYMBOL = "symbol";
    const SYMBOL_NAME = "is symbol for";
    const SYMBOL_ID = 29;
    const AND = "and";
    const AND_NAME = "and";
    const AND_ID = 30;
    const SELECTOR = "selector"; // the from_phrase of a selector can be used more than once so the description of the to_phrase should be shown to the user
    const TO = 'to'; // to define a time period e.g. "12:00 to 13:00" or "1. March 2024 to 3. March 2024"

    // directional forms of verbs (maybe move to verb_api or test if only used for testing)
    const FOLLOWED_BY = "is followed by";
    const FOLLOWER_OF = "is follower of";
    const TIME_STEP = "time jump";

    // persevered  verb names for unit and integration tests based on the database
    const TEST_ADD_NAME = "System Test Verb";
    const TEST_ADD_COM = "test description if it can be added to the verb via import";

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
