<?php

/*

    shared/json_fields.php - list of json field names used for the api and im- and export
    ----------------------

    the json or yaml fields for the api messages are in the shared api object


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

namespace shared;

class json_fields
{

    // TODO easy move all fields used for the json im- and export messages to this object

    /*
     * shared - fields used for api and im- and export json messages
     */

    // the unique name of the object which is also a database index
    const NAME = 'name';

    // a short description of concrete object used e.g. for the tooltip in the frontend
    const DESCRIPTION = 'description';

    // the json field name for code id to select a single object
    // to link predefined functionality to a row e.g. to select a system view
    // e.g. to select a system view
    const CODE_ID = 'code_id';

    // the json field name in the api json message which is supposed to contain
    // the database id (or in some cases still the code id) of an object type
    // e.g. for the word api message it contains the id of the phrase type
    const TYPE = 'type_id';
    const TYPE_NAME = 'type';

    // the code id of the view style of a view, component or component_link
    const STYLE = 'style';


    /*
     * api - fields used only for the api json messages
     */

    // json field names of the api json messages
    // which is supposed to be the same as the corresponding var of the api object
    // so that no additional mapping is needed
    // TODO check if api objects can be deprecated
    // and used in the backend to create the json for the frontend
    // and used in the frontend for the field selection
    const ID = 'id'; // the unique database id used to save the changes

    // reference fields e.g. to link a phrase to an external reference
    const PHRASE = 'phrase_id';
    const SOURCE = 'source_id';
    const USER_ID = 'user_id';

    // for link api objects the id of the preloaded link type
    const PREDICATE = 'predicate_id';
    const FROM = 'from_id';
    const TO = 'to_id';
    const VERB = 'verb_id';
    // the field names used for the im- and export in the json or yaml format
    const EX_FROM = 'from';
    const EX_TO = 'to';
    const EX_VERB = 'verb';
    const PLURAL = 'plural';
    // the json field names in the api json message which is supposed to be the same as the var $id
    const PARENT = 'parent';

    const USR_TEXT = 'user_text'; // the formula expression in the user readable format
    const SHARE = 'share'; // the field name used for the JSON im- and export
    const PROTECTION = 'protection'; // the field name used for the JSON im- and export

    // object list
    const PHRASES = 'phrases';
    const COMPONENTS = 'components';

    // fields for external ref
    const URL = 'url'; // the external link of a source or a reference
    const EXTERNAL_KEY = 'external_key'; // the unique key of the reference

    // object specific fields
    const NUMBER = 'number'; // a float number used for values and results
    const IS_STD = 'is_std'; // flag if a value or result is user specific or the default value for all users
    const USER_TEXT = 'user_text'; // the formula expression in a human-readable format
    const REF_TEXT = 'ref_text'; // the formula expression in a database reference format
    const NEED_ALL_VAL = 'need_all_val'; // calculate and save the result only if all used values are not null
    const FORMULA_NAME_PHRASE = 'name_phrase'; // the phrase object for the formula name

    // batch job fields
    const TIME_REQUEST = 'request_time'; // e.g. the timestamp when a batch job has been requested
    const PRIORITY = 'priority'; // of the batch job
    const TIME_START = 'start_time'; // e.g. the timestamp of a log entry
    const TIME_END = 'end_time'; // e.g. the timestamp of a log entry
    const STATUS = 'status'; // of the job and also used for the sys log

    // change log fields
    const TIME = 'time'; // e.g. the timestamp of a log entry
    const TEXT = 'text'; // the description of the change as a fixed text

    // system log fields
    const TRACE = 'trace'; // what has lead to the issue
    const PRG_PART = 'prg_part'; // which part has caused the issue
    const OWNER = 'owner'; // the developer which wants to fix the problem

    // the order number e.g. of the component within the view
    const POSITION = 'position';

    // the database id e.g. of a component_link
    const LINK_ID = 'link_id';

    // e.g. the order of the components within a view
    const POS = 'position';

    // the phrase to select the row name of a view component
    const PHRASE_ROW = 'word_row';
    // the phrase to select the column name of a view component
    const PHRASE_COL = 'word_col';

    // the position rules for a component relative to the previous component
    const POS_TYPE = 'position_type';

    // to link predefined functionality to a row e.g. to select a system view
    const UI_MSG_CODE_ID = 'ui_msg_code_id';

    // phrase api specific fields
    // the json field name in the api json message to identify if the term is a word, triple, verb or formula
    const OBJECT_CLASS = 'class';


    /*
     * im- and export - fields used only for the im- and export json messages
     */

    const TIMESTAMP = 'timestamp';

    // name of the view to show a word, triple or formula
    const VIEW = 'view';

    // the name of the value source
    const SOURCE_NAME = 'source';

    // to assign e.g. words, triples or formulas to a view
    const ASSIGNED = 'assigned';

}
