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

namespace Zukunft\ZukunftCom\main\php\shared;

class json_fields
{

    /*
     * shared - fields used for api and im- and export json messages
     */

    // message header
    const string POD = 'pod';
    const string VERSION = 'version';
    const string TIMESTAMP = 'timestamp';
    const string SELECTION = 'selection';
    const string BODY = 'body';
    // the messages that should be shown to the user e.g. name already used. please use another name
    // TODO Prio 0 add unit test case
    const string MSG = 'message';


    // the unique name of the object which is also a database index
    const string NAME = 'name';

    // a short description of concrete object used e.g. for the tooltip in the frontend
    const string DESCRIPTION = 'description';

    // the json field name for code id to select a single object
    // to link predefined functionality to a row e.g. to select a system view
    // e.g. to select a system view
    const string CODE_ID = 'code_id';

    // the json field name in the api json message which is supposed to contain
    // the database id (or in some cases still the code id) of an object type
    // e.g. for the word api message it contains the id of the phrase type
    // for link types use PREDICATE
    const string TYPE = 'type_id';
    // the json field name in the im- and export json message which is supposed to contain
    // the code id of an object type
    const string TYPE_CODE_ID = 'type_code_id';
    // the json field name in the im- and export json message which is supposed to contain
    // the name of an object type which is used if the object type has not yet a code id
    // or the type name has been changed by the user
    const string TYPE_NAME = 'type';

    // object lists
    const string PHRASES = 'phrases';

    // single objects
    const string REFERENCE = 'reference';

    // object specific fields
    const string NUMBER = 'number'; // a float number used for values and results
    const string TIME_VALUE = 'time_value'; // a date and time value or result
    const string TEXT_VALUE = 'text_value'; // a text value that should not be used for searching
    const string GEO_VALUE = 'geo_value'; // a geolocation value or result

    const string LAST_UPDATE = 'last_update';

    // the code id of the view style of a view, component or component_link
    const string STYLE = 'style';

    // the field names used for the im- and export in the json or yaml format
    const string EX_FROM = 'from';
    const string EX_TO = 'to';
    const string EX_VERB = 'verb';

    // the external link of a source or a reference
    const string URL = 'url';

    // the order number e.g. of the component within the view
    // is needed in the json because the json format does not support ordered lists by definition
    const string POSITION = 'position';

    // language
    const string WIKI_CODE = 'wikimedia_code';
    const string LOCAL_NAME = 'local_name';

    // language forms
    const string LANGUAGE = 'language_id';
    const string PLURAL = 'plural';
    const string NAME_PLURAL = 'name_plural';
    const string NAME_REVERSE = 'name_reverse';
    const string NAME_PLURAL_REVERSE = 'name_plural_reverse';

    // verbs
    const string REVERSE = 'reverse';
    const string REV_PLURAL = 'rev_plural';
    const string FRM_NAME = 'frm_name';
    const string USAGE = 'usage';
    const string IMPACT = 'impact';

    // user profiles
    const string RIGHT_LEVEL = 'right_level';

    // phrase types
    const string SCALE = 'scale';
    const string SYMBOL = 'symbol';


    /*
     * api - fields used only for the api json messages
     */

    // json field names of the api json messages
    // which is supposed to be the same as the corresponding var of the api object
    // so that no additional mapping is needed
    // TODO check if api objects can be deprecated
    // and used in the backend to create the json for the frontend
    // and used in the frontend for the field selection
    const string ID = 'id'; // the unique database id used to save the changes

    // reference fields e.g. to link a phrase to an external reference
    const string PHRASE_ID = 'phrase_id';
    const string PHRASE = 'phrase'; // the phrase object as a sub array
    const string TERM_ID = 'term_id';
    const string TERM = 'term'; // the term object as a sub array
    const string SOURCE_ID = 'source_id';
    const string SOURCE = 'source'; // the source object as a sub array
    const string USER_ID = 'user_id';
    //const string GROUP_ID = 'group_id';
    const string FORMULA_ID = 'formula_id';
    const string FORMULA = 'formula'; // the formula object as a sub array

    // for link api objects the id of the preloaded link type
    // for the type of one object that is not a link use TYPE
    const string PREDICATE_ID = 'predicate_id';
    const string PREDICATE = 'predicate'; // the link type code id
    const string FROM = 'from_id';
    const string FROM_PHRASE = 'from_phrase';
    const string TO = 'to_id';
    const string TO_PHRASE = 'to_phrase';
    const string VERB = 'verb_id';
    const string WEIGHT = 'weight';

    // the parent object with detail fields used e.g. for the parent view of view relations
    const string PARENT = 'parent';
    // the parent id used e.g. for the parent view of view relations
    const string PARENT_ID = 'parent_id';

    // the child object with detail fields used e.g. for the child view of view relations
    const string CHILD = 'child';

    // the child id used e.g. for the child view of view relations
    const string CHILD_ID = 'child_id';


    const string USR_TEXT = 'user_text'; // the formula expression in the user readable format
    const string SHARE = 'share'; // the field name used for the JSON im- and export
    const string PROTECTION = 'protection'; // the field name used for the JSON im- and export
    const string EXCLUDED = 'excluded'; // true if the object has been excluded by the user

    // fields for external ref
    const string EXTERNAL_KEY = 'external_key'; // the unique key of the reference

    // object specific fields
    const string IS_STD = 'is_std'; // flag if a value or result is user-specific or the default value for all users
    const string USER_TEXT = 'user_text'; // the formula expression in a human-readable format
    const string REF_TEXT = 'ref_text'; // the formula expression in a database reference format
    const string LATEX = 'latex'; // the formula in latex format
    const string NEED_ALL_VAL = 'need_all_val'; // calculate and save the result only if all used values are not null
    const string FORMULA_NAME_PHRASE = 'name_phrase'; // the phrase object for the formula name
    const string FORMULA_NAME = 'formula'; // the name of the formula for im- and export
    const string NAME_IN_FORMULA = 'name_in_formula'; // the name of the verb if used in formulas

    // batch job fields
    const string TIME_REQUEST = 'request_time'; // e.g. the timestamp when a batch job has been requested
    const string PRIORITY = 'priority'; // of the batch job
    const string TIME_START = 'start_time'; // e.g. the timestamp of a log entry
    const string TIME_UPDATE = 'update_time'; // e.g. the timestamp of the last system error update
    const string TIME_END = 'end_time'; // e.g. the timestamp of a log entry
    const string STATUS = 'status'; // name or code id of the user or job status and also used for the sys log
    const string STATUS_ID = 'status_id'; // database id of the user or job status and also used for the sys log

    // database cache job fields
    const string CACHE_DATA = 'data'; // the json text of the cache with all the cached values

    // change log fields
    const string TIME = 'time'; // e.g. the timestamp of a log entry
    const string TEXT = 'text'; // the description of the change as a fixed text

    // system log fields
    const string TRACE = 'trace'; // what has lead to the issue
    const string FUNCTION_ID = 'function_id'; // id of a code part that has caused an issue
    const string SOLVER = 'solver'; // the developer which wants to fix the problem

    // the database id e.g. of a component_link
    const string LINK_ID = 'link_id';

    // the phrase to select the row name of a view component
    const string PHRASE_ROW = 'word_row';
    // the phrase to select the column name of a view component
    const string PHRASE_COL = 'word_col';

    // the position rules for a component relative to the previous component
    const string POS_TYPE = 'position_type';

    // id the select a predefined text for the user that is translated into the user interface language
    const string UI_MSG_CODE_ID = 'ui_msg_code_id';
    // id the select a second predefined text for the user that is translated into the user interface language e.g. after the number that is shown
    const string UI_MSG_CODE_ID_VARS = 'ui_msg_code_id_vars';
    // id the select a predefined text for the user in case of a special value that is translated into the user interface language e.g. if the number is zero
    const string UI_MSG_CODE_ID_EXCEPTION = 'ui_msg_code_id_exception';
    // the value of the system var the select the exception message; the value is selected by the placeholder in the ui_msg_code_id_vars text
    const string UI_MSG_CODE_VAL_EXCEPTION = 'ui_msg_value_exception';

    // phrase api specific fields
    // the json field name in the api json message to identify if the term is a word, triple, verb or formula
    const string OBJECT_CLASS = 'class';

    // the json field name in the api json message to identify if the
    // phrase, term or figure is a word, triple, verb, formula, value or result
    // to allow renaming the class in backend and frontend without changing the api
    const string CLASS_WORD = 'word';
    const string CLASS_TRIPLE = 'triple';
    const string CLASS_VERB = 'verb';
    const string CLASS_FORMULA = 'formula';
    const string CLASS_VALUE = 'value';
    const string CLASS_RESULT = 'result';

    // activate to handle differences between the api class name and the code class name
    //const string CLASS_PHRASE_TYPE = 'phrase_type';
    //const string CLASS_LOG_STATUS = 'sys_log_status';

    // view to component link
    //const string POS_TYPE_CMP = 'pos_type';

    // change log
    const string USR = 'usr';
    const string ACTION_ID = 'action_id';
    const string TABLE_ID = 'table_id';
    const string FIELD_ID = 'field_id';
    const string ROW_ID = 'row_id';
    const string CHANGE_TIME = 'change_time';
    const string OLD_VALUE = 'old_value';
    const string OLD_ID = 'old_id';
    const string NEW_VALUE = 'new_value';
    const string NEW_ID = 'new_id';
    const string STD_VALUE = 'std_value';
    const string STD_ID = 'std_id';

    // to review
    const string USER_NAME = 'user';
    const string JOB_PARAMETER = 'job_parameter';


    /*
     * api type list
     */

    //const string TYPE_LISTS = 'type_lists';
    const string LIST_USER_PROFILES = 'user_profiles';
    const string LIST_USER_TYPES = 'user_types';
    const string LIST_USER_STATUUS = 'user_statuum';
    const string LIST_PHRASE_TYPES = 'phrase_types';
    const string LIST_FORMULA_TYPES = 'formula_types';
    const string LIST_FORMULA_LINK_TYPES = 'formula_link_types';
    const string LIST_ELEMENTS = 'elements';
    const string LIST_ELEMENT_TYPES = 'element_types';
    const string LIST_VIEW_TYPES = 'view_types';
    const string LIST_VIEW_STYLES = 'view_styles';
    const string LIST_VIEW_LINK_TYPES = 'view_link_types';
    const string LIST_VIEW_RELATION_TYPES = 'view_relation_types';
    const string LIST_COMPONENT_TYPES = 'component_types';
    const string LIST_COMPONENT_LINK_TYPES = 'component_link_types';
    const string LIST_COMPONENT_POSITION_TYPES = 'position_types';
    const string LIST_REF_TYPES = 'ref_types';
    const string LIST_SOURCE_TYPES = 'source_types';
    const string LIST_SHARE_TYPES = 'share_types';
    const string LIST_PROTECTION_TYPES = 'protection_types';
    const string LIST_LANGUAGES = 'languages';
    const string LIST_LANGUAGE_FORMS = 'language_forms';
    const string LIST_SYS_LOG_FUNCTIONS = 'sys_log_functions';
    const string LIST_SYS_LOG_LEVELS = 'sys_log_levels';
    const string LIST_SYS_LOG_STATUUS = 'sys_log_statuum';
    const string LIST_JOB_STATUUS = 'job_statuum';
    const string LIST_JOB_TYPES = 'job_types';
    const string LIST_DB_CACHE_STATUUS = 'db_cache_statuum';
    const string LIST_DB_CACHE_TYPES = 'db_cache_types';
    const string LIST_CHANGE_LOG_ACTIONS = 'change_action_list';
    const string LIST_CHANGE_LOG_TABLES = 'change_table_list';
    const string LIST_CHANGE_LOG_FIELDS = 'change_field_list';
    const string LIST_VERBS = 'verbs';
    const string LIST_SYSTEM_VIEWS = 'system_views';

    /*
     * im- and export - fields used only for the im- and export json messages
     */


    // name of the view to show a word, triple or formula
    const string VIEW_ID = 'view_id';
    const string VIEW = 'view'; // the view as a sub array
    // list of views
    const string VIEWS = 'views';

    // name of the component that is part of a view
    const string COMPONENT_ID = 'component_id';
    const string COMPONENT = 'component'; // the component as a sub array
    const string COMPONENTS = 'components';

    // a list of users
    const string USERS = 'users';

    const string VALUE_LIST = 'value-list';
    const string CALC_VALIDATION = 'calc-validation';
    const string VIEW_VALIDATION = 'view-validation';
    const string IP_BLACKLIST = 'ip-blacklist';


    // the name of the value source
    const string SOURCE_NAME = 'source';

    // to assign e.g. words, triples or formulas to a view
    const string ASSIGNED = 'assigned';

    // for the user object
    const string IP_ADDR = 'ip_address';
    const string EMAIL = 'email';
    const string FIRST_NAME = 'first_name';
    const string LAST_NAME = 'last_name';
    const string PROFILE = 'profile';
    const string PROFILE_ID = 'profile_id';
    const string ACTIVATION_KEY = 'activation_key';
    const string ACTIVATION_TIMEOUT = 'activation_timeout';
    const string DB_NOW = 'db_now';
    const string LAST_LOGIN = 'last_login';
    const string LAST_LOGOFF = 'last_logoff';
    const string CREATED = 'created';

    const string PHRASE_VALUES = 'phrase-values';
    const string SOURCES = 'sources';
    const string REFERENCES = 'references';
    // the alpha key to select the internal phrase (or later the term)
    const string REF_INTERN = 'phrase';
    // the external unique key for this reference
    const string REF_EXTERN = 'name';


    // the phrase to select the row name of a view component
    const string ROW = 'row';
    const string COLUMN = 'column';
    const string COLUMN2 = 'column2';

    // list of references e.g. of words
    const string REFS = 'refs';

    // for value lists
    const string VALUES = 'values';

    // a list of the word names without further parameters
    const string WORD_LIST = 'word-list';


    // for formulas
    const string EXPRESSION = 'expression';
    const string ASSIGNED_WORD = 'assigned_word';
    const string FORMULAS = 'formulas';
    const string FORMULA_LINKS = 'formula_links';
    // TODO Prio 2 cleanup and use fields with *_id only for API messages and move const to this section
    //             use phrase_type (without id) for im- and export where the name is used
    //             if e.g. phrase_type (without id) is used in an API message all type vars are included
    const string PHRASE_TYPE = 'phrase_type';
    const string PHRASE_TYPE_ID = 'phrase_type_id';


    // for results
    const string WORDS = 'words';
    const string VERBS = 'verbs';
    const string TRIPLES = 'triples';

    // for ip ranges
    const string IP_FROM = 'ip_from';
    const string IP_TO = 'ip_to';
    const string REASON = 'reason';
    const string IS_ACTIVE = 'is_active';

    // list of phrases
    // for results to select the input values
    // and for value lists to reduce the number of phrase for each value
    // also used to select the phrases used to filter the values for calculating this result
    const string CONTEXT = 'context';

    // for user messages
    const string USER_MESSAGES = 'message_id_list';
    const string USER_MESSAGES_WITH_VARS = 'message_id_list_with_vars';
    const string USER_MESSAGES_STATUS = 'message_status';
    const string USER = 'user';

    // list of json fields that are used for the api message to the frontend
    // but that are never used for the api message to the backend
    const array UNIDIRECTIONAL = [
        self::REF_TEXT,
        self::USAGE,
        self::IMPACT
    ];

}
