<?php

/*

    shared/types/api_types.php - options of the api messages
    --------------------------


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

    Copyright (c) 1995-2024 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

namespace Zukunft\ZukunftCom\main\php\shared\types;

enum api_types: string
{

    // include the phrases in the value list api message
    case INCL_PHRASES = 'incl_phrases';

    // include the most often used related objects in the api message e.g. for a symbol like 'CHF' the related 'Swiss Franc'
    case INCL_RELATED = 'incl_related';

    // include only the phrase names for a short list that is at least somehow user human-readable
    case PHRASE_NAMES = 'phrase_names';

    // include the term details in the api message e.g. for the last term used by the user
    case INCL_TERMS = 'incl_terms';

    // include the view details with the components in the api message
    case INCL_VIEWS = 'incl_views';

    // include the source details in the api message e.g. for the last source used by the user
    case INCL_SOURCES = 'incl_sources';

    // include the components details with the view or component_link api message
    case INCL_COMPONENTS = 'incl_components';

    // if set create the json by simple combining the json arrays
    // which may lead to some repeating values
    // if not set the simplified linked object json is used
    // by using the link_id json field and move the subarray one layer higher
    // e.g. instead of "components":[{"link_id":1,                         "id":1,"name":"Word","description":"simply show the word or triple name","type_id":8 ,"position":1,"position_type":1},{"link_id":2,                         "id":2,"name":"spreadsheet","description":"changeable sheet with words, number and formulas","type_id":35 ,"position":2,"position_type":1}]
    //             use "components":[{     "id":1,"view_id":1,"component":{"id":1,"name":"Word","description":"simply show the word or triple name","type_id":8},"position":1,"position_type":1},     {"id":2,"view_id":1,"component":{"id":2,"name":"spreadsheet","description":"changeable sheet with words, number and formulas","type_id":35},"position":2,"position_type":1}]
    case LINK_DETAILS = 'link_details';

    // include objects that have been excluded by the user e.g. so that the user can include the objects again
    // by default excluded objects are not send to the frontend
    case WITH_EXCLUDED = 'with_excluded';

    // include object id and the impact of excluded objects for warning messages in the frontend
    case WITH_EXCLUDED_ID = 'with_excluded_id';

    // do not fill up the group id to the full key length
    case NO_KEY_FILL = 'no_key_fill';

    // internal parameter for unit testing to switch off the database loading of missing objects
    // and ignore the excluded flag so include all fields also for excluded
    case TEST_MODE = 'test_mode';

    // include the message header
    case HEADER = 'header';

}