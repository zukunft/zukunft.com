<?php

/*

    shared/json_fields.php - list of json field names used for the api and im- and export
    ----------------------

    the json or yaml fields for the api, im- and export messages are in the shared api object


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

    /*
     * shared - fields used for api and im- and export json messages
     */

    // message header
    const POD = 'pod';
    const VERSION = 'version';
    const TIMESTAMP = 'timestamp';
    const SELECTION = 'selection';
    const BODY = 'body';

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
    // the json field name in the im- and export json message which is supposed to contain
    // the code id of an object type
    const TYPE_CODE_ID = 'type_code_id';
    // the json field name in the im- and export json message which is supposed to contain
    // the name of an object type which is used if the object type has not yet a code id
    // or the type name has been changed by the user
    const TYPE_NAME = 'type';

    // object lists
    const PHRASES = 'phrases';
    const COMPONENTS = 'components';

    // single objects
    const REFERENCE = 'reference';

    // object specific fields
    const NUMBER = 'number'; // a float number used for values and results
    const TIME_VALUE = 'time_value'; // a date and time value or result
    const TEXT_VALUE = 'text_value'; // a text value that should not be used for searching
    const GEO_VALUE = 'geo_value'; // a geolocation value or result

    const LAST_UPDATE = 'last_update';

    // the code id of the view style of a view, component or component_link
    const STYLE = 'style';

    // the field names used for the im- and export in the json or yaml format
    const EX_FROM = 'from';
    const EX_TO = 'to';
    const EX_VERB = 'verb';

    // the external link of a source or a reference
    const URL = 'url';

    // the order number e.g. of the component within the view
    const POSITION = 'position';

    // language forms
    const PLURAL = 'plural';
    const NAME_PLURAL = 'name_plural';
    const NAME_REVERSE = 'name_reverse';
    const NAME_PLURAL_REVERSE = 'name_plural_reverse';

    // verbs
    const REVERSE = 'reverse';
    const REV_PLURAL = 'rev_plural';
    const FRM_NAME = 'frm_name';
    const USAGE = 'usage';


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
    //const GROUP_ID = 'group_id';
    const FORMULA_ID = 'formula_id';

    // for link api objects the id of the preloaded link type
    const PREDICATE = 'predicate_id';
    const FROM = 'from_id';
    const FROM_PHRASE = 'from_phrase';
    const TO = 'to_id';
    const TO_PHRASE = 'to_phrase';
    const VERB = 'verb_id';
    // the json field names in the api json message which is supposed to be the same as the var $id
    const PARENT = 'parent';

    const USR_TEXT = 'user_text'; // the formula expression in the user readable format
    const SHARE = 'share'; // the field name used for the JSON im- and export
    const PROTECTION = 'protection'; // the field name used for the JSON im- and export
    const EXCLUDED = 'excluded'; // true if the object has been excluded by the user

    // fields for external ref
    const EXTERNAL_KEY = 'external_key'; // the unique key of the reference

    // object specific fields
    const IS_STD = 'is_std'; // flag if a value or result is user specific or the default value for all users
    const USER_TEXT = 'user_text'; // the formula expression in a human-readable format
    const REF_TEXT = 'ref_text'; // the formula expression in a database reference format
    const NEED_ALL_VAL = 'need_all_val'; // calculate and save the result only if all used values are not null
    const FORMULA_NAME_PHRASE = 'name_phrase'; // the phrase object for the formula name
    const FORMULA_NAME = 'formula'; // the name of the formula for im- and export

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

    // the json field name in the api json message to identify if the
    // phrase, term or figure is a word, triple, verb, formula, value or result
    // to allow renaming the class in backend and frontend without changing the api
    const CLASS_WORD = 'word';
    const CLASS_TRIPLE = 'triple';
    const CLASS_VERB = 'verb';
    const CLASS_FORMULA = 'formula';
    const CLASS_VALUE = 'value';
    const CLASS_RESULT = 'result';

    // activate to handle differences between the api class name and the code class name
    //const CLASS_PHRASE_TYPE = 'phrase_type';
    //const CLASS_LOG_STATUS = 'sys_log_status';

    // view to component link
    //const POS_TYPE_CMP = 'pos_type';

    // change log
    const USR = 'usr';
    const ACTION_ID = 'action_id';
    const TABLE_ID = 'table_id';
    const FIELD_ID = 'field_id';
    const ROW_ID = 'row_id';
    const CHANGE_TIME = 'change_time';
    const OLD_VALUE = 'old_value';
    const OLD_ID = 'old_id';
    const NEW_VALUE = 'new_value';
    const NEW_ID = 'new_id';
    const STD_VALUE = 'std_value';
    const STD_ID = 'std_id';

    // to review
    const USER_NAME = 'user';


    /*
     * api type list
     */

    //const TYPE_LISTS = 'type_lists';
    const LIST_USER_PROFILES = 'user_profiles';
    const LIST_PHRASE_TYPES = 'phrase_types';
    const LIST_FORMULA_TYPES = 'formula_types';
    const LIST_FORMULA_LINK_TYPES = 'formula_link_types';
    const LIST_ELEMENT_TYPES = 'element_types';
    const LIST_VIEW_TYPES = 'view_types';
    const LIST_VIEW_STYLES = 'view_styles';
    const LIST_VIEW_LINK_TYPES = 'view_link_types';
    const LIST_COMPONENT_TYPES = 'component_types';
    const LIST_COMPONENT_LINK_TYPES = 'component_link_types';
    const LIST_COMPONENT_POSITION_TYPES = 'position_types';
    const LIST_REF_TYPES = 'ref_types';
    const LIST_SOURCE_TYPES = 'source_types';
    const LIST_SHARE_TYPES = 'share_types';
    const LIST_PROTECTION_TYPES = 'protection_types';
    const LIST_LANGUAGES = 'languages';
    const LIST_LANGUAGE_FORMS = 'language_forms';
    const LIST_SYS_LOG_STATUUS = 'sys_log_statuus';
    const LIST_JOB_TYPES = 'job_types';
    const LIST_CHANGE_LOG_ACTIONS = 'change_action_list';
    const LIST_CHANGE_LOG_TABLES = 'change_table_list';
    const LIST_CHANGE_LOG_FIELDS = 'change_field_list';
    const LIST_VERBS = 'verbs';
    const LIST_SYSTEM_VIEWS = 'system_views';

    /*
     * im- and export - fields used only for the im- and export json messages
     */


    // name of the view to show a word, triple or formula
    const VIEW = 'view';
    // list of views
    const VIEWS = 'views';

    // a list of users
    const USERS = 'users';

    const VALUE_LIST = 'value-list';
    const CALC_VALIDATION = 'calc-validation';
    const VIEW_VALIDATION = 'view-validation';
    const IP_BLACKLIST = 'ip-blacklist';


    // the name of the value source
    const SOURCE_NAME = 'source';

    // to assign e.g. words, triples or formulas to a view
    const ASSIGNED = 'assigned';

    // for the user object
    const IP_ADDR = 'ip_address';
    const EMAIL = 'email';
    const FIRST_NAME = 'first_name';
    const LAST_NAME = 'last_name';
    const PROFILE = 'profile';
    const PROFILE_ID = 'profile_id';

    const PHRASE_VALUES = 'phrase-values';
    const SOURCES = 'sources';
    const REFERENCES = 'references';
    // the alpha key to select the internal phrase (or later the term)
    const REF_INTERN = 'phrase';
    // the external unique key for this reference
    const REF_EXTERN = 'name';


    // the phrase to select the row name of a view component
    const ROW = 'row';
    const COLUMN = 'column';
    const COLUMN2 = 'column2';

    // list of references e.g. of words
    const REFS = 'refs';

    // for value lists
    const VALUES = 'values';

    // a list of the word names without further parameters
    const WORD_LIST = 'word-list';


    // for formulas
    const EXPRESSION = 'expression';
    const ASSIGNED_WORD = 'assigned_word';
    const FORMULAS = 'formulas';


    // for results
    const WORDS = 'words';
    const VERBS = 'verbs';
    const TRIPLES = 'triples';

    // for ip ranges
    const IP_FROM = 'ip_from';
    const IP_TO = 'ip_to';
    const REASON = 'reason';
    const IS_ACTIVE = 'is_active';

    // list of phrases
    // for results to select the input values
    // and for value lists to reduce the number of phrase for each value
    // also used to select the phrases used to filter the values for calculating this result
    const CONTEXT = 'context';
}
