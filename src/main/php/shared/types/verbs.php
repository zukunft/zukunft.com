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

namespace Zukunft\ZukunftCom\main\php\shared\types;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::SHARED_ENUM . 'foaf_direction.php';

use Zukunft\ZukunftCom\main\php\shared\enum\foaf_direction;

class verbs
{

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
    const string NOT_SET = "not_set";
    const string NOT_SET_NAME = "not set";
    const int NOT_SET_ID = 1;
    const string NOT_SET_COM = 'no verb / predicate selected';
    const string IS = "is";
    const string IS_NAME = "is a";
    const int IS_ID = 2;
    const string IS_COM = "the main child to parent relation e.g. Zurich is a Canton. The reverse is valid and usually plural is used e.g. Cantons are Zurich, Bern, ...";
    const string IS_PLURAL = "are";
    const string IS_REVERSE = "are";
    const string IS_REV_PLURAL = "are";
    const string IS_NAME_FORMULA = "of all";
    const string PART = "contains";
    const string PART_NAME = "is part of";
    const int PART_ID = 3;
    const string OF = "of";
    const string OF_NAME = "of";
    const int OF_ID = 5;
    const string ON = "on";
    const string ON_NAME = "on";
    const int ON_ID = 31;
    const string WITH = "with";
    const string WITH_NAME = "with";
    const int WITH_ID = 6;
    const string HAS = "has";
    const string HAS_NAME = "has a";
    const int HAS_ID = 7;
    const string CAN_BE_PART_OF = "can_be_part_of";
    const string CAN_BE_PART_OF_NAME = "can be part of";
    const int CAN_BE_PART_OF_ID = 4;
    const string FOLLOW = "follow";
    const string FOLLOW_NAME = "is follower of";
    const int FOLLOW_ID = 11;
    const string MEASURE = "measure_type";
    const string MEASURE_NAME = "is measure type for";
    const int MEASURE_ID = 14;
    const string MEASURE_COM = "is the default measure type for";
    const string MEASURE_PLURAL = 'are measure type for';
    const string MEASURE_REVERSE = 'has the measure type';
    const string MEASURE_REV_PLURAL = 'have the measure type';
    const string MEASURE_NAME_FORMULA = 'measure type';
    const string ALIAS = "alias";
    const string ALIAS_NAME = "is alias of";
    const int ALIAS_ID = 18;
    const string CAN_CONTAIN = "can_contain";
    const string CAN_CONTAIN_NAME = "can be used as a differentiator for";
    const string CAN_CONTAIN_NAME_REVERSE = "of";
    const int CAN_CONTAIN_ID = 16;
    const string CAN = "can";
    const string CAN_NAME = "can";
    const int CAN_ID = 19;
    const string CAN_BE = "can_be";
    const string CAN_BE_NAME = "can be";
    const int CAN_BE_ID = 20;
    const string CAN_GET = "can_get";
    const string CAN_GET_NAME = "can get";
    const int CAN_GET_ID = 21;
    const string CAN_CAUSE = "can_cause";
    const string CAN_CAUSE_NAME = "can cause";
    const int CAN_CAUSE_ID = 22;
    const string CAN_HAVE = "can_have";
    const string CAN_HAVE_NAME = "can have";
    const int CAN_HAVE_ID = 23;
    const string CAN_USE = "can_use";
    const string CAN_USE_NAME = "can use";
    const int CAN_USE_ID = 24;
    const string SCALED = "scaled";
    const string SCALED_NAME = "scaled by";
    const int SCALED_ID = 25;
    const string PER = "per";
    const string PER_NAME = "per";
    const int PER_ID = 26;
    const string TIMES = "times";
    const string TIMES_NAME = "times";
    const int TIMES_ID = 27;
    const string IN = "in";
    const string IN_NAME = "in";
    const int IN_ID = 32;
    const string TO = "to";  // to define a time period e.g. "12:00 to 13:00" or "1. March 2024 to 3. March 2024"
    const string TO_NAME = "to";
    const int TO_ID = 33;
    const string SYMBOL = "symbol";
    const string SYMBOL_NAME = "is symbol for";
    const int SYMBOL_ID = 29;
    const string AND = "and";
    const string AND_NAME = "and";
    const int AND_ID = 30;
    const string RANK = "rank";
    const string RANK_NAME = "is ranked by";
    const int RANK_ID = 34;
    const string SELECTOR = "selector"; // the from_phrase of a selector can be used more than once so the description of the to_phrase should be shown to the user
    const string SELECTOR_NAME = "is selector for";
    const int SELECTOR_ID = 28;
    const string BETWEEN = "between"; // to define a range e.g. "12:00 to 13:00" can also be expressed as "12:00 between 13:00"
    const string BETWEEN_NAME = "between";
    const int BETWEEN_ID = 35;
    const string BETWEEN_COM = "to define a range e.g. a value between a lower and an upper bound";
    const string KIND_OF = "kind_of"; // to assign a sub kind to a parent category e.g. the quadratic formula is a kind of formula
    const string KIND_OF_NAME = "kind of";
    const int KIND_OF_ID = 36;
    const string KIND_OF_COM = "to assign a sub kind to a parent category e.g. the quadratic formula is a kind of formula";
    const string KIND_OF_PLURAL = "are kinds of";
    const string KIND_OF_REVERSE = "has the kind";
    const string KIND_OF_REV_PLURAL = "have the kinds";
    const string NAME_OF = "name_of"; // to assign a proper name to a category e.g. the Pythagorean theorem is named after Pythagoras
    const string NAME_OF_NAME = "name of";
    const int NAME_OF_ID = 37;
    const string NAME_OF_COM = "to assign a proper name to a category e.g. the Pythagorean theorem is named after Pythagoras";
    const string NAME_OF_PLURAL = "are names of";
    const string NAME_OF_REVERSE = "is named";
    const string NAME_OF_REV_PLURAL = "are named";
    const string BY_PARTS = "by_parts"; // to describe a method that operates on the parts of an expression e.g. integration by parts
    const string BY_PARTS_NAME = "by parts";
    const int BY_PARTS_ID = 38;
    const string BY_PARTS_COM = "to describe a method that operates on the parts of an expression e.g. integration by parts";
    const string BY_PARTS_PLURAL = "by parts";
    const string BY_PARTS_REVERSE = "";
    const string BY_PARTS_REV_PLURAL = "";
    const string MUST_BE_ONE_OF = "must_be_one_of"; // to disambiguate a word with several meanings by pinning each meaning to its own qualifier triple e.g. "second (time unit)" and "second (ranking number)"
    const string MUST_BE_ONE_OF_NAME = "must be one of";
    const int MUST_BE_ONE_OF_ID = 39;
    const string MUST_BE_ONE_OF_COM = "to disambiguate a word that has several meanings by pinning each meaning to its own triple e.g. the word 'second' must be one of 'time unit' or 'ranking number'; the qualifier triple is referenced instead of the ambiguous word and only the original word is shown on a page while the qualifier appears in the tooltip";

    // directional forms of verbs (maybe move to verb_api or test if only used for testing)
    const string FOLLOWED_BY = "is followed by";
    const string FOLLOWER_OF = "is follower of";
    const string TIME_STEP = "time_jump";
    const string TIME_STEP_NAME_FORMULA = "time jump";
    const string TIME_STEP_NAME = "is time jump for";
    const int TIME_STEP_ID = 8;
    const string TERM_STEP = "term_jump";
    const string TERM_STEP_NAME = "is term jump for";
    const int TERM_STEP_ID = 9;
    const string TERM_NEED_STEP = "term_needed";
    const string TERM_NEED_STEP_NAME = "term type needed";
    const int TERM_NEED_STEP_ID = 10;
    const string USES = "uses";
    const string USES_NAME = "uses";
    const int USES_ID = 12;
    const string ISSUE = "issue";
    const string ISSUE_NAME = "issue";
    const int ISSUE_ID = 13;
    const string ACRONYM = "acronym";
    const string ACRONYM_NAME = "is an acronym for";
    const int ACRONYM_ID = 15;
    const string INFLUENCE = "influence";
    const string INFLUENCE_NAME = "influence";
    const int INFLUENCE_ID = 17;
    const string USED_BY = "used_by"; // passive form of 'uses' when the dependent phrase is the subject e.g. cent is used by Euro
    const string USED_BY_NAME = "is used by";
    const int USED_BY_ID = 40;
    const string USED_BY_COM = "passive form of 'uses' when the dependent phrase is the subject e.g. cent is used by Euro";
    const string CAN_BE_MADE_OF = "can_be_made_of"; // to specify possible materials or composition options e.g. a porringer can be made of plastic
    const string CAN_BE_MADE_OF_NAME = "can be made of";
    const int CAN_BE_MADE_OF_ID = 41;
    const string CAN_BE_MADE_OF_COM = "to specify possible materials or composition options e.g. a porringer can be made of plastic";
    const string CAN_BE_PACKED_IN = "can_be_packed_in"; // to specify possible packaging options e.g. a blueberry can be packed in a plastic porringer
    const string CAN_BE_PACKED_IN_NAME = "can be packed in";
    const int CAN_BE_PACKED_IN_ID = 42;
    const string CAN_BE_PACKED_IN_COM = "to specify possible packaging options e.g. a blueberry can be packed in a plastic porringer";
    const string USED_FOR = "used_for"; // to specify the intended purpose of a phrase e.g. fuel used for a jet (jet fuel)
    const string USED_FOR_NAME = "used for";
    const int USED_FOR_ID = 43;
    const string USED_FOR_COM = "to specify the intended purpose of a phrase e.g. fuel used for a jet (jet fuel)";

    // persevered verb names for unit and integration tests based on the database
    const string TEST_ADD_NAME = "System Test Verb";
    const string TEST_ADD_CODE_ID = "System Test Verb code id";
    const string TEST_ADD_COM = "test description if it can be added to the verb via import";
    const string TEST_ADD_RENAMED = "System Test Verb Renamed";

    // search directions to get related words (phrases)
    const string DIRECTION_NO = '';
    const string DIRECTION_DOWN = 'down';    // or forward  to get a list of 'to' phrases
    const string DIRECTION_UP = 'up';        // or backward to get a list of 'from' phrases based on a given to phrase



    // word groups for creating the test words and remove them after the test
    const array RESERVED_WORDS = array(
        self::NOT_SET_NAME,
        self::IS_NAME,
        self::PART_NAME,
        self::TEST_ADD_NAME,
        self::TEST_ADD_RENAMED,
    );

    // list of verb names only used for system testing that should always be removed after testing
    const array TEST_VERBS = array(
        self::TEST_ADD_NAME,
        self::TEST_ADD_RENAMED,
    );

    // list of verbs that does not need a from phrase e.g. "per day" oder "m/s is alias of meter per second"
    const array WITHOUT_FROM = array(
        self::ALIAS,
        self::SYMBOL,
        self::PER,
        self::IN
    );

    // list of verbs used by the back- or frontend for internal processes e.g. to sort objects
    const array SYSTEM_VERBS = array(
        self::RANK,
    );

    // ordered list of verbs to create the subtitle phrase category description;
    // ordering goes from most specific naming/measuring relations to the broader
    // taxonomy ones so the subtitle renderer prefers the tightest category label
    // (e.g. a symbol-for or measure-type entry wins over a plain "is a" entry).
    //
    // each entry is a [code_id, direction] pair: the direction tells the load
    // step which side the current phrase must be on for the connecting triple
    // to count as a category. All category verbs run FROM the categorised phrase
    // TO its category, so the direction is foaf_direction::DOWN throughout —
    // when loading the category subtitle for CHF, the triple "CHF is symbol for
    // Swiss Franc" matches because CHF is the FROM (i.e. looking DOWN at the
    // TO), but when loading it for Swiss Franc the same triple does NOT match
    // (Swiss Franc is the TO, so looking DOWN would yield nothing — CHF is just
    // its symbol, not its category)
    const array CATEGORY_VERBS = array(
        [self::SYMBOL,         foaf_direction::DOWN], // "CHF is symbol for Swiss Franc"          — specific naming/representation
        [self::MEASURE,        foaf_direction::DOWN], // "meter is measure type for length"       — specific measure category
        [self::NAME_OF,        foaf_direction::DOWN], // "Newton is name of law"                  — specific naming of a category
        [self::IS,             foaf_direction::DOWN], // "Zurich is a Canton"                     — main child-to-parent relation
        [self::KIND_OF,        foaf_direction::DOWN], // "quadratic formula is a kind of formula" — sub-kind of a parent category
        [self::MUST_BE_ONE_OF, foaf_direction::DOWN], // "second must be one of time unit"        — disambiguation as category pin
        [self::PART,           foaf_direction::DOWN], // "Zurich is part of Switzerland"          — structural membership
        [self::CAN_BE_PART_OF, foaf_direction::DOWN], // "income tax can be part of cash flow"    — potential structural membership
    );

    // ordered list of verbs that defines the behavior of a phrase;
    // ordering starts with the strongest (definite) "has / uses" relations and
    // moves toward potential/conditional capabilities so behavior renderers can
    // surface the most concrete property the phrase carries first.
    //
    // each entry is a [code_id, direction] pair (same shape as CATEGORY_VERBS):
    // every property verb runs FROM the owner/actor TO the property/effect, so the
    // direction is foaf_direction::DOWN throughout — "Zurich has a population" is
    // a property of Zurich, not of population; "global warming can cause flooding"
    // is a property of global warming, not of flooding
    const array PROPERTY_VERBS = array(
        [self::HAS,         foaf_direction::DOWN], // "Zurich has a population"                       — definite possession
        [self::CAN_USE,     foaf_direction::DOWN], // "Zurich can use the Swiss Franc"                — definite capability
        [self::CAN_HAVE,    foaf_direction::DOWN], // "a city can have inhabitants"                   — potential possession
        [self::CAN_GET,     foaf_direction::DOWN], // "a stock can get a price"                       — potential acquisition
        [self::CAN_CAUSE,   foaf_direction::DOWN], // "global warming can cause flooding"             — potential effect
        [self::CAN_CONTAIN, foaf_direction::DOWN], // "year can be used as a differentiator for population" — potential differentiator
        [self::CAN_BE,      foaf_direction::DOWN], // "a city can be a canton"                        — potential state (Zurich is both)
        [self::CAN,         foaf_direction::DOWN], // "a stock can rise"                              — general capability
        [self::INFLUENCE,   foaf_direction::DOWN], // "interest rate influence stock prices"          — affects without ownership
    );

    // ordered list of verbs that defines the synonymy of a phrase; aliases come
    // before acronyms because an alias is an explicit equivalence whereas an
    // acronym is the narrower abbreviation form of one.
    //
    // each entry is a [code_id, direction] pair (same shape as CATEGORY_VERBS):
    // - ALIAS is symmetric — "m/s is alias of meter per second" reads naturally
    //   in both directions, so the subtitle should render whichever side the user
    //   is viewing → foaf_direction::BOTH
    // - ACRONYM is one-way — the short form IS an acronym for the long form, but
    //   the long form is NOT an acronym for anything, so the subtitle only renders
    //   when the user views the acronym (FROM side) → foaf_direction::DOWN
    const array SYNONYM_VERBS = array(
        [self::ALIAS,   foaf_direction::BOTH], // "m/s is alias of meter per second"                — explicit equivalence (symmetric)
        [self::ACRONYM, foaf_direction::DOWN], // "CHF is an acronym for Confoederatio Helvetica Franc" — abbreviated form of a name (asymmetric)
    );

}
