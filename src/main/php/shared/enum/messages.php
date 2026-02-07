<?php

/*

    shared/enum/messages.php - enum of the user message ids and the text in the default language
    ------------------------


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

namespace Zukunft\ZukunftCom\main\php\shared\enum;

use ValueError;

enum messages: string
{

    /*
     * GENERAL TARGET:
     * use the most specific const name so that the translation can be specific
     *
     * the const names start with
     * INFO_* for information only messages without a variable
     * WARN_* for warning messages without a variable
     * ERR_* for error messages without a variable
     * VAR_* for variable names or makers
     * SYSTEM_* for parts of fixed system pages
     * FORM_* for form parts used by the system
     * FORM_TITLE_* for the form title that is translated
     * FORM_SUB_TITLE_* for the form sub titles that is translated
     * FORM_FIELD_* for the test input form field label of that is translated
     * FORM_SELECT_* (ex LABEL_*) for the form field label of that is translated
     *
     * to be renamed / deprecated
     * MISSING_* to WARN_MISSING_*
     *
     *
     * the prefix naming convention for the translations is
     * page_title_* for the main titles of fixed system pages
     * form_title_* for the main titles of the system forms
     * system_title_* for subtitles of system forms that might be used also in user views
     * form_field_* for normal input fields of system forms
     * form_select_* for select input fields of system forms
     *
     * TODO Prio 2
     * - rename system_form_* to form_field_*
     *
     * the const name may have a different name order for easier code reading
     * e.g. URL_FORM_FIELD for 'form_field_url'
     * use LABEL_* const only for fallback
    */

    // the message types that defines what needs to be done next
    const int FATAL = -4;
    const int ERROR = -3;
    const int NOK = -2;
    const int WARNING = -1;
    const int INFO = 0;
    const int OK = 1;
    const int YES_NO = 2;
    const int CONFIRM_CANCEL = 3;

    // start and end maker for message id within a text to allow changing the order of vars within a message
    const string VAR_START = 'z$';
    const string VAR_END = '$z';

    // to use the var makers without
    const string VAR_ESC_START = '\z$';
    const string VAR_ESC_END = '\$z';
    const string VAR_TEMP_START = '\zTemp$';
    const string VAR_TEMP_END = '\Temp$z';
    const string VAR_TEMP_VAR = 'VarPrefix';

    // var names
    // the id of a sandbox object
    const string VAR_ID = 'VarObjId';
    // the id of the compare sandbox object
    const string VAR_ID_CHK = 'VarObjIdCheck';
    // the name of a sandbox object
    const string VAR_NAME = 'VarObjName';
    // the list of names e.g. the reserved names that should not be used
    const string VAR_NAME_LIST = 'VarObjNameList';
    // the name of the compare sandbox object
    const string VAR_NAME_CHK = 'VarObjNameCheck';
    // the description of a sandbox object using dsp_id()
    const string VAR_SANDBOX_NAME = 'VarSandboxName';
    // the name of a word
    const string VAR_WORD_NAME = 'VarWordName';
    // the name of a triple
    const string VAR_TRIPLE_NAME = 'VarTripleName';
    // the name of the source object of a link
    const string VAR_NAME_FROM = 'VarFromName';
    // the name of the destination object of a link
    const string VAR_NAME_TO = 'VarToName';
    // the name of a phrase
    const string VAR_PHRASE_NAME = 'VarPhraseName';
    // the name of a term
    const string VAR_TERM_NAME = 'VarTermName';
    // the name of a view
    const string VAR_VIEW_NAME = 'VarViewName';
    // the user/owner of an object
    const string VAR_USER = 'VarUser';
    // the user/owner of a compare object
    const string VAR_USER_CHK = 'VarUserCheck';
    // the name of a user
    const string VAR_USER_NAME = 'VarUserName';
    // the name, profile and permissions of a user
    const string VAR_USER_PROFILE = 'VarUserProfile';
    // the name of a user of a list
    const string VAR_USER_LIST_NAME = 'VarUserListName';
    // the name of a sandbox object
    const string VAR_TYPE = 'VarObjType';
    // the name of the compare sandbox object
    const string VAR_TYPE_CHK = 'VarObjTypeCheck';
    // the id of a value object
    const string VAR_VAL_ID = 'VarValueId';
    // the numeric, time, text or geo value of a value
    const string VAR_VALUE = 'VarValue';
    // the numeric, time, text or geo value of a compare value
    const string VAR_VALUE_CHK = 'VarValueCheck';
    // the real number of values
    const string VAR_VALUE_COUNT = 'VarValueCount';
    // the expected number of values
    const string VAR_VALUE_COUNT_CHK = 'VarValueCountCheck';
    // a list of values
    const string VAR_VALUE_LIST = 'VarValueList';
    // the phrase group naming of a value
    const string VAR_GROUP = 'VarGroup';
    // the phrase group naming of a compare value
    const string VAR_GROUP_CHK = 'VarGroupCheck';
    // the source of a value
    const string VAR_SOURCE = 'VarSource';
    // the source of a compare value
    const string VAR_SOURCE_CHK = 'VarSourceCheck';
    const string VAR_SYMBOL = 'VarSymbol';
    // the symbol of a compare value
    const string VAR_SYMBOL_CHK = 'VarSymbolCheck';
    const string VAR_FORMULA_CHK = 'VarFormulaCheck';
    // the name of a class
    const string VAR_CLASS_NAME = 'VarClassName';
    // the name of a function
    const string VAR_FUNCTION_NAME = 'VarFunctionName';
    // value how many times the object is referenced
    const string VAR_USAGE = 'VarUsage';
    // the share permission of a sandbox object
    const string VAR_SHARE = 'VarShare';
    // the share permission of the compare sandbox object
    const string VAR_SHARE_CHK = 'VarShareCheck';
    // the change protection of a sandbox object
    const string VAR_PROTECT = 'VarProtect';
    // the change protection of the compare sandbox object
    const string VAR_PROTECT_CHK = 'VarProtectCheck';
    // the exclusion status of a sandbox object
    const string VAR_EXCLUDE = 'VarExclude';
    // the exclusion status of the compare sandbox object
    const string VAR_EXCLUDE_CHK = 'VarExcludeCheck';

    const string VAR_JSON_TEXT = 'VarJsonText';
    const string VAR_SOURCE_NAME = 'VarSourceName';
    const string VAR_FORMULA_NAME = 'VarFormulaName';
    const string VAR_COMPONENT_NAME = 'VarComponentName';
    const string VAR_FILE_TYPE = 'VarFileType';
    const string VAR_FILE_NAME = 'VarFileName';
    const string VAR_IP_RANGE = 'VarIpRange';
    const string VAR_SUMMARY = 'VarSummary';
    const string VAR_PART = 'VarPart';
    const string VAR_REQUEST = 'VarRequest';
    const string VAR_ERROR_TEXT = 'VarErrorText';
    const string VAR_MESSAGE_ID = 'VarMsgId';
    const string VAR_LANGUAGE = 'VarLanguage';
    // the key of a url
    const string VAR_URL_KEY = 'VarUrlKey';

    // for the object main parameters created by the dsp_id function
    const string VAR_PHRASE = 'VarObjPhrase';
    const string VAR_FORMULA = 'VarObjFormula';
    const string VAR_TERM = 'VarObjTerm';
    const string VAR_VIEW = 'VarObjView';
    const string VAR_COMPONENT = 'VarObjComponent';
    const string VAR_EXPRESSION = 'VarObjExpression';
    const string VAR_JSON_PART = 'VarJsonPart';
    const string VAR_VERB_NAME = 'VarVerbName';
    const string VAR_COUNTER = 'VarCounter';
    const string IMPORT_SUCCESS = 'finished successful';

    // technical database vars
    const string VAR_SQL = 'VarObjSQL';
    const string VAR_TRACE_LINK = 'VarObjTraceLink';
    const string VAR_SQL_REASON = 'VarObjSqlReason';

    // unique message keys
    // *_txt sample translation to test the English mapping
    case IS_RESERVED = 'is_reserved';
    case IS_RESERVED_TXT = 'is a reserved';
    case RESERVED_NAME = 'reserved_name';
    case NOT_SIMILAR = 'not_similar';
    case RELOAD = 'reload';
    case OF_DEFAULT = 'of_default';
    case FAILED = 'failed';
    case READ = 'read';
    case LOADED = 'loaded';
    case DONE = 'done';
    case TOTAL = 'total';
    case EXAMPLE_SHORT = 'e.g.';

    // special message id placeholders
    case ERROR_TEXT = 'error';
    case NONE = '';

    // messages with vars

    case MISSING_TRANSLATION = 'translation of "'
        . self::VAR_START . self::VAR_MESSAGE_ID . self::VAR_END
        . '" to language "'
        . self::VAR_START . self::VAR_LANGUAGE . self::VAR_END
        . '" missing ('
        . self::VAR_START . self::VAR_ERROR_TEXT . self::VAR_END
        . ')';
    case MISSING_OVERWRITE = 'internal function overwrite of "'
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '" is missing in class '
        . self::VAR_START . self::VAR_CLASS_NAME . self::VAR_END
        . '.';
    case DIFF_ID = 'id is "'
        . self::VAR_START . self::VAR_ID . self::VAR_END
        . '" instead of "'
        . self::VAR_START . self::VAR_ID_CHK . self::VAR_END
        . '" for '
        . self::VAR_START . self::VAR_CLASS_NAME . self::VAR_END
        . ' "'
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '"';
    case DIFF_NAME = 'name is "'
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '" instead of "'
        . self::VAR_START . self::VAR_NAME_CHK . self::VAR_END
        . '" for '
        . self::VAR_START . self::VAR_CLASS_NAME . self::VAR_END
        . ' "'
        . self::VAR_START . self::VAR_SANDBOX_NAME . self::VAR_END
        . '"';
    case DIFF_USER = 'user is "'
        . self::VAR_START . self::VAR_USER . self::VAR_END
        . '" instead of "'
        . self::VAR_START . self::VAR_USER_CHK . self::VAR_END
        . '" for '
        . self::VAR_START . self::VAR_CLASS_NAME . self::VAR_END
        . ' "'
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '"';
    case DIFF_OWNER = 'owner is "'
        . self::VAR_START . self::VAR_USER . self::VAR_END
        . '" instead of "'
        . self::VAR_START . self::VAR_USER_CHK . self::VAR_END
        . '" for '
        . self::VAR_START . self::VAR_CLASS_NAME . self::VAR_END
        . ' "'
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '"';
    case DIFF_TYPE = 'type is "'
        . self::VAR_START . self::VAR_TYPE . self::VAR_END
        . '" instead of "'
        . self::VAR_START . self::VAR_TYPE_CHK . self::VAR_END
        . '" for '
        . self::VAR_START . self::VAR_CLASS_NAME . self::VAR_END
        . ' "'
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '"';
    case DIFF_SHARE = 'share permission is "'
        . self::VAR_START . self::VAR_SHARE . self::VAR_END
        . '" instead of "'
        . self::VAR_START . self::VAR_SHARE_CHK . self::VAR_END
        . '" for '
        . self::VAR_START . self::VAR_CLASS_NAME . self::VAR_END
        . ' "'
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '"';
    case DIFF_PROTECTION = 'modify protect is "'
        . self::VAR_START . self::VAR_PROTECT . self::VAR_END
        . '" instead of "'
        . self::VAR_START . self::VAR_PROTECT_CHK . self::VAR_END
        . '" for '
        . self::VAR_START . self::VAR_CLASS_NAME . self::VAR_END
        . ' "'
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '"';
    case DIFF_EXCLUSION = 'exclusion is "'
        . self::VAR_START . self::VAR_EXCLUDE . self::VAR_END
        . '" instead of "'
        . self::VAR_START . self::VAR_EXCLUDE_CHK . self::VAR_END
        . '" for '
        . self::VAR_START . self::VAR_CLASS_NAME . self::VAR_END
        . ' "'
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '"';
    case DIFF_VALUE = 'value is "'
        . self::VAR_START . self::VAR_VALUE . self::VAR_END
        . '" instead of "'
        . self::VAR_START . self::VAR_VALUE_CHK . self::VAR_END
        . '" for "'
        . self::VAR_START . self::VAR_VAL_ID . self::VAR_END
        . '"';
    case DIFF_VALUE_TYPE = 'value type is "'
        . self::VAR_START . self::VAR_TYPE . self::VAR_END
        . '" instead of "'
        . self::VAR_START . self::VAR_TYPE_CHK . self::VAR_END
        . '" for "'
        . self::VAR_START . self::VAR_VAL_ID . self::VAR_END
        . '"';
    case DIFF_GROUP = 'group name is "'
        . self::VAR_START . self::VAR_GROUP . self::VAR_END
        . '" instead of "'
        . self::VAR_START . self::VAR_GROUP_CHK . self::VAR_END
        . '" for "'
        . self::VAR_START . self::VAR_VAL_ID . self::VAR_END
        . '"';
    case DIFF_SOURCE = 'source is "'
        . self::VAR_START . self::VAR_SOURCE . self::VAR_END
        . '" instead of "'
        . self::VAR_START . self::VAR_SOURCE_CHK . self::VAR_END
        . '" for '
        . self::VAR_START . self::VAR_CLASS_NAME . self::VAR_END
        . ' "'
        . self::VAR_START . self::VAR_VAL_ID . self::VAR_END
        . '"';
    case DIFF_SYMBOL = 'symbol is "'
        . self::VAR_START . self::VAR_SYMBOL . self::VAR_END
        . '" instead of "'
        . self::VAR_START . self::VAR_SYMBOL_CHK . self::VAR_END
        . '" for '
        . self::VAR_START . self::VAR_CLASS_NAME . self::VAR_END
        . ' "'
        . self::VAR_START . self::VAR_VAL_ID . self::VAR_END
        . '"';
    case DIFF_FORMULA = 'formula is "'
        . self::VAR_START . self::VAR_FORMULA . self::VAR_END
        . '" instead of "'
        . self::VAR_START . self::VAR_FORMULA_CHK . self::VAR_END
        . '" for '
        . self::VAR_START . self::VAR_CLASS_NAME . self::VAR_END
        . ' "'
        . self::VAR_START . self::VAR_SANDBOX_NAME . self::VAR_END
        . '"';
    case DIFF_CODE_ID = 'code_id is "'
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '" instead of "'
        . self::VAR_START . self::VAR_NAME_CHK . self::VAR_END
        . '" for '
        . self::VAR_START . self::VAR_CLASS_NAME . self::VAR_END
        . ' "'
        . self::VAR_START . self::VAR_SANDBOX_NAME . self::VAR_END
        . '"';

    case LOAD_FORMULA_ID = 'unexpected formula id '
        . self::VAR_START . self::VAR_FORMULA . self::VAR_END
        . ' in database for '
        . self::VAR_START . self::VAR_CLASS_NAME . self::VAR_END
        . ' "'
        . self::VAR_START . self::VAR_SANDBOX_NAME . self::VAR_END
        . '"';

    case TRIM_NAME = 'trim "'
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '"';
    case WORD_MISSING = 'word "'
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '" is missing';
    case WORD_ADDITIONAL = 'word "'
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '" is extra';
    case WORD_ID_MISSING = 'word id missing of "'
        . self::VAR_START . self::VAR_ID . self::VAR_END
        . '"';
    case WORD_NAME_MISSING = 'required word name missing';
    case WORD_ID_ADDITIONAL = 'word id additional of "'
        . self::VAR_START . self::VAR_ID . self::VAR_END
        . '"';
    case WORD_NOT_SAVED = 'word "'
        . self::VAR_START . self::VAR_ID . self::VAR_END
        . '" cannot be saved';
    case TRIPLE_MISSING = 'triple "'
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '" is missing';
    case TRIPLE_ADDITIONAL = 'triple "'
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '" is extra';
    case TRIPLE_ID_MISSING = 'triple id missing of "'
        . self::VAR_START . self::VAR_ID . self::VAR_END
        . '"';
    case TRIPLE_ID_ADDITIONAL = 'triple id additional of "'
        . self::VAR_START . self::VAR_ID . self::VAR_END
        . '"';
    case IMPORT_NOT_SAVED = 'import of '
        . self::VAR_START . self::VAR_CLASS_NAME . self::VAR_END
        . ' '
        . self::VAR_START . self::VAR_ID . self::VAR_END
        . '" cannot be saved';
    case URL_KEY_MISSING = 'url key "'
        . self::VAR_START . self::VAR_URL_KEY . self::VAR_END
        . '" is missing';
    case URL_MAP_MISSING = 'url mapper for "'
        . self::VAR_START . self::VAR_URL_KEY . self::VAR_END
        . '" is missing';
    case URL_MAP_VALUE_MISSING = 'url value mapper for "'
        . self::VAR_START . self::VAR_URL_KEY . self::VAR_END
        . '" is missing in '
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '.';
    case PHRASE_MISSING_MSG = 'phrase "'
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '" is missing';
    case VALUE_ID_MISSING = 'to add a value to the database at least one word must specify the value "'
        . self::VAR_START . self::VAR_VALUE . self::VAR_END;
    case USER_MISSING = 'user in "'
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '" missing';
    case FROM_MISSING = 'from of link "'
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '" missing';
    case TO_MISSING = 'to of link "'
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '" missing';
    case FROM_ZERO_ID = 'id of from "'
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '" is missing';
    case TO_ZERO_ID = 'id of to "'
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '" is missing';
    case ID_AND_NAME_MISSING = 'id and name missing';
    case VALUE_MISSING = 'value "'
        . self::VAR_START . self::VAR_VAL_ID . self::VAR_END
        . '" is missing';
    case VALUE_ADDITIONAL = 'value "'
        . self::VAR_START . self::VAR_VAL_ID . self::VAR_END
        . '" is extra';
    case RESULT_MISSING = 'result "'
        . self::VAR_START . self::VAR_VAL_ID . self::VAR_END
        . '" is missing';
    case RESULT_ADDITIONAL = 'result "'
        . self::VAR_START . self::VAR_VAL_ID . self::VAR_END
        . '" is extra';
    case LIST_DOUBLE_ENTRY = 'trying to add "'
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '" which is already part of the '
        . self::VAR_START . self::VAR_CLASS_NAME . self::VAR_END
        . ' list';
    case LIST_USER_NO_MATCH = 'trying to add "'
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '" of user '
        . self::VAR_START . self::VAR_USER_NAME . self::VAR_END
        . ' to list of user '
        . self::VAR_START . self::VAR_USER_LIST_NAME . self::VAR_END
        . ' list';
    case LIST_USER_INVALID = 'trying to add an invalid user '
        . self::VAR_START . self::VAR_USER_NAME . self::VAR_END
        . ' to list of user '
        . self::VAR_START . self::VAR_USER_LIST_NAME . self::VAR_END
        . ' list';
    case FILL_WORD_WITH_OTHER = 'word "'
        . self::VAR_START . self::VAR_WORD_NAME . self::VAR_END
        . '" cannot be filled with '
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '"';
    case FILL_TRIPLE_WITH_OTHER = 'triple "'
        . self::VAR_START . self::VAR_TRIPLE_NAME . self::VAR_END
        . '" cannot be filled with '
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '"';

    case JSON_DECODE = 'error trying to decode json "'
        . self::VAR_START . self::VAR_JSON_TEXT . self::VAR_END
        . '"';

    case YAML_DECODE_FAILED = 'YAML decode failed of "'
        . self::VAR_START . self::VAR_JSON_TEXT . self::VAR_END
        . '"';
    case YAML_STRING_EMPTY = 'YAML string is empty';
    case JSON_DECODE_FAILED = 'JSON decode failed of "'
        . self::VAR_START . self::VAR_JSON_TEXT . self::VAR_END
        . '"';
    case JSON_STRING_EMPTY = 'JSON string is empty';
    case JSON_ORDER_POS_COMPONENT = 'Unexpected position '
        . self::VAR_START . self::VAR_VALUE . self::VAR_END
        . ' instead of '
        . self::VAR_START . self::VAR_VALUE_CHK . self::VAR_END
        . ' for component "'
        . self::VAR_START . self::VAR_COMPONENT_NAME . self::VAR_END
        . '" in view "'
        . self::VAR_START . self::VAR_VIEW_NAME . self::VAR_END
        . '"';
    case IMPORT_VERSION_NEWER = 'Import file has been created with version "'
        . self::VAR_START . self::VAR_VALUE . self::VAR_END
        . '", which is newer than this, which is "'
        . self::VAR_START . self::VAR_VALUE_CHK . self::VAR_END
        . '"';
    case IMPORT_UNKNOWN_ELEMENT = 'Unknown element "'
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '"';
    case IMPORT_SUMMARY = self::VAR_START . self::VAR_SUMMARY . self::VAR_END;
    case PHRASE_NAME_EMPTY = self::VAR_START . self::VAR_VALUE_LIST . self::VAR_END
        . ' contains an empty phrase name';

    case YAML_TOOLTIP_COMMENT_UNEXPECTED = 'yaml is not expected to start with a tooltip-comment';
    case SOURCE_DESCRIPTION_WITHOUT_NAME = 'source-description is given without source-name';
    case IMPORT_RESULT_NOT_NUMERIC = 'Import result: "'
        . self::VAR_START . self::VAR_VALUE . self::VAR_END
        . '" is expected to be a number ('
        . self::VAR_START . self::VAR_GROUP . self::VAR_END
        . ')';
    case IMPORT_VALUE_NOT_NUMERIC = 'Import value: "'
        . self::VAR_START . self::VAR_VALUE . self::VAR_END
        . '" is expected to be a number ('
        . self::VAR_START . self::VAR_GROUP . self::VAR_END
        . ')';
    case IMPORT_VALUE_NOT_DATETIME = 'Import value: "'
        . self::VAR_START . self::VAR_VALUE . self::VAR_END
        . '" is expected to be a a datetime ('
        . self::VAR_START . self::VAR_GROUP . self::VAR_END
        . ')';
    case IMPORT_VALUE_FORMAT_NOT_KNOWN = 'Import value has an unexpected json value name "'
        . self::VAR_START . self::VAR_JSON_TEXT . self::VAR_END
        . '"';
    case FAILED_ADD_LOGGING_ERROR = 'Adding "'
        . self::VAR_START . self::VAR_CLASS_NAME . self::VAR_END
        . '" "'
        . self::VAR_START . self::VAR_ID . self::VAR_END
        . '" failed due to logging error';
    case USED_OBJECT_ID_AND_NAME_MISSING =
        self::VAR_START . self::VAR_CLASS_NAME . self::VAR_END
        . ' "'
        . self::VAR_START . self::VAR_PHRASE_NAME . self::VAR_END
        . '" missing but it is used "'
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '"';
    case FILL_OBJECT_ID_MISSING =
        self::VAR_START . self::VAR_CLASS_NAME . self::VAR_END
        . ' "'
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '" has no id but is used in fill_by_id';
    case ADDED_OBJECT_ID_MISSING =
        self::VAR_START . self::VAR_CLASS_NAME . self::VAR_END
        . ' "'
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '" has no id after expected to be added to the database';
    case ADDED_OBJECT_NOT_FOUND =
        self::VAR_START . self::VAR_CLASS_NAME . self::VAR_END
        . ' "'
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '" is not found any more after expected to be added to the database';

    case USER_IP_ADDR_MISSING = 'ip addr for user "'
        . self::VAR_START . self::VAR_USER_NAME . self::VAR_END
        . '" is missing';
    case SOURCE_MISSING_IMPORT = 'source "'
        . self::VAR_START . self::VAR_SOURCE_NAME . self::VAR_END
        . '" is missing in the import message '
        . self::VAR_START . self::VAR_JSON_TEXT . self::VAR_END;
    case PHRASE_MISSING_IMPORT = 'phrase "'
        . self::VAR_START . self::VAR_PHRASE . self::VAR_END
        . '" is missing in the import message '
        . self::VAR_START . self::VAR_JSON_TEXT . self::VAR_END;
    case PHRASE_CREATED = 'phrase with name "'
        . self::VAR_START . self::VAR_PHRASE_NAME . self::VAR_END
        . '" created';
    case FORMULA_MISSING_IMPORT = 'formula "'
        . self::VAR_START . self::VAR_FORMULA . self::VAR_END
        . '" is missing in the import message '
        . self::VAR_START . self::VAR_JSON_TEXT . self::VAR_END;
    case FORMULA_ID_MISSING = 'formula id is missing in the import message '
        . self::VAR_START . self::VAR_JSON_TEXT . self::VAR_END;
    case FORMULA_JSON_MISSING = 'formula JSON is missing in the import message '
        . self::VAR_START . self::VAR_JSON_TEXT . self::VAR_END;
    case FORMULA_EXPRESSION_MISSING = 'formula expression is missing in '
        . self::VAR_START . self::VAR_FORMULA . self::VAR_END;
    case FORMULA_TERM_NAME_MISSING = 'no word, triple, formula or verb found for "'
        . self::VAR_START . self::VAR_NAME . self::VAR_END . '"';
    case FORMULA_CREATED = 'formula with name "'
        . self::VAR_START . self::VAR_FORMULA_NAME . self::VAR_END
        . '" created';

    case EXPRESSION_EMPTY = 'the expression of formula "'
        . self::VAR_START . self::VAR_FORMULA_NAME . self::VAR_END
        . '" is empty';
    case EXPRESSION_SYMBOL_TOO_SHORT = 'the formula expression symbol "'
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '" is too short';
    case EXPRESSION_ID_NOT_A_NUMBER = 'the formula expression id '
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . ' is no a valid integer number';
    case EXPRESSION_EQUAL_SIGN_MISSING = 'the equal sign (""=) is missing formula expression "'
        . self::VAR_START . self::VAR_FORMULA . self::VAR_END
        . '"';
    case EXPRESSION_ID_NOT_VALID = 'the formula expression id '
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . ' is not valid number.';
    case EXPRESSION_SYMBOL_NOT_VALID = 'the formula expression symbol "'
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '" is not valid. only word, triple, verb and formula are expected.';
    case EXPRESSION_TERM_MISSING = 'the term "'
        . self::VAR_START . self::VAR_TERM . self::VAR_END
        . '" of formula "'
        . self::VAR_START . self::VAR_FORMULA . self::VAR_END
        . '" is missing';
    case EXPRESSION_REF_IS_NULL = 'the database format of the formula expression of "'
        . self::VAR_START . self::VAR_FORMULA . self::VAR_END
        . '" is null';
    case EXPRESSION_REF_IS_EMPTY = 'the database format of the formula expression of "'
        . self::VAR_START . self::VAR_FORMULA . self::VAR_END
        . '" is empty';
    case EXPRESSION_REF_IS_TOO_SHORT = 'the database format of the formula expression "'
        . self::VAR_START . self::VAR_EXPRESSION . self::VAR_END
        . '" of "'
        . self::VAR_START . self::VAR_FORMULA . self::VAR_END
        . '" is too short to be a valid expression';
    case EXPRESSION_HAS_MORE_REFS_THAN_USER_TERMS = 'the database format of the formula expression "'
        . self::VAR_START . self::VAR_EXPRESSION . self::VAR_END
        . '" has more references than the  "'
        . self::VAR_START . self::VAR_FORMULA . self::VAR_END
        . '" is too short to be a valid expression';
    case EXPRESSION_USER_IS_NULL = 'the formula expression of "'
        . self::VAR_START . self::VAR_FORMULA . self::VAR_END
        . '" is null';
    case EXPRESSION_USER_IS_EMPTY = 'the formula expression of "'
        . self::VAR_START . self::VAR_FORMULA . self::VAR_END
        . '" is empty';
    case EXPRESSION_USER_IS_TOO_SHORT = 'the database format of formula expression "'
        . self::VAR_START . self::VAR_EXPRESSION . self::VAR_END
        . '" of formula "'
        . self::VAR_START . self::VAR_FORMULA . self::VAR_END
        . '" does probably not contain all terms of the formula expression "'
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '". Probably at least one term is not in the database any more';

    case PHRASE_TYPE_UNEXPECTED = 'it is not yet defined how "'
        . self::VAR_START . self::VAR_PHRASE_NAME . self::VAR_END
        . '" should be handled to '
        . self::VAR_START . self::VAR_FUNCTION_NAME . self::VAR_END;

    case TERM_MISSING_IMPORT = 'term "'
        . self::VAR_START . self::VAR_TERM . self::VAR_END
        . '" is missing in the import message '
        . self::VAR_START . self::VAR_JSON_TEXT . self::VAR_END;
    case TERM_CREATED = 'term with name "'
        . self::VAR_START . self::VAR_TERM_NAME . self::VAR_END
        . '" created';

    case VIEW_MISSING_IMPORT = 'view "'
        . self::VAR_START . self::VAR_VIEW . self::VAR_END
        . '" is missing in the import message '
        . self::VAR_START . self::VAR_JSON_TEXT . self::VAR_END;
    case VIEW_CREATED = 'view with name "'
        . self::VAR_START . self::VAR_VIEW_NAME . self::VAR_END
        . '" created';

    case COMPONENT_MISSING_IMPORT = 'component "'
        . self::VAR_START . self::VAR_COMPONENT . self::VAR_END
        . '" is missing in the import message '
        . self::VAR_START . self::VAR_JSON_TEXT . self::VAR_END;
    case COMPONENT_MISSING = 'component with name "'
        . self::VAR_START . self::VAR_COMPONENT_NAME . self::VAR_END
        . '" missing when importing json part '
        . self::VAR_START . self::VAR_JSON_TEXT . self::VAR_END;
    case COMPONENT_CREATED = 'component with name "'
        . self::VAR_START . self::VAR_COMPONENT_NAME . self::VAR_END
        . '" created';
    case COMPONENT_ALREADY_EXISTS = 'A view component with the name "'
        . self::VAR_START . self::VAR_COMPONENT_NAME . self::VAR_END
        . '" already exists. Please use another name.';

    // messages with vars for import
    case IMPORT_READ_ERROR = 'error reading to decode json '
        . self::VAR_START . self::VAR_FILE_TYPE . self::VAR_END
        . ' file "'
        . self::VAR_START . self::VAR_FILE_NAME . self::VAR_END
        . '"';
    case IMPORT_EMPTY = 'import file "'
        . self::VAR_START . self::VAR_FILE_NAME . self::VAR_END
        . '" is empty';
    case IMPORT_DONE = self::IMPORT_SUCCESS . ' ('
        . self::VAR_START . self::VAR_SUMMARY . self::VAR_END
        . ' imported)';
    case IMPORT_FAILED = 'failed because '
        . self::VAR_START . self::VAR_SUMMARY . self::VAR_END
        . '.';
    case CONFIG_PART = 'configuration part '
        . self::VAR_START . self::VAR_PART . self::VAR_END
        . ' cannot yet be selected';
    case API_MESSAGE_EMPTY = 'request '
        . self::VAR_START . self::VAR_REQUEST . self::VAR_END
        . ' has not returned any response';
    case API_MESSAGE = self::VAR_START . self::VAR_JSON_TEXT . self::VAR_END;
    case MANDATORY_FIELD_MISSING = 'Mandatory field '
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . ' missing in "'
        . self::VAR_START . self::VAR_NAME_LIST . self::VAR_END . '"';
    case MANDATORY_FIELD_NAME_MISSING = 'Mandatory field name missing in API JSON '
        . self::VAR_START . self::VAR_JSON_TEXT . self::VAR_END;
    case MANDATORY_GROUP_ID_MISSING = 'at least one word or triple must be given to save the value '
        . self::VAR_START . self::VAR_VALUE . self::VAR_END;
    case MANDATORY_EXPRESSION_MISSING = 'the formula '
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . ' must have an valid expression';
    case MANDATORY_FROM_OBJECT_INVALID = 'object '
        . self::VAR_START . self::VAR_NAME_FROM . self::VAR_END
        . ' is not valid and cannot be linked to '
        . self::VAR_START . self::VAR_NAME . self::VAR_END . '"';
    case MANDATORY_TO_OBJECT_INVALID = 'object '
        . self::VAR_START . self::VAR_NAME_TO . self::VAR_END
        . ' is not valid and cannot be linked from '
        . self::VAR_START . self::VAR_NAME . self::VAR_END . '"';
    case MANDATORY_PHRASE_IN_LINK_INVALID = 'word or triple '
        . self::VAR_START . self::VAR_PHRASE_NAME . self::VAR_END
        . ' is not valid and cannot be linked to '
        . self::VAR_START . self::VAR_NAME . self::VAR_END . '"';
    case MANDATORY_FORMULA_IN_LINK_INVALID = 'formula '
        . self::VAR_START . self::VAR_FORMULA_NAME . self::VAR_END
        . ' is not valid and cannot be linked to '
        . self::VAR_START . self::VAR_NAME . self::VAR_END . '"';
    case MANDATORY_TERM_IN_LINK_INVALID = 'word, verb, triple or formula '
        . self::VAR_START . self::VAR_TERM_NAME . self::VAR_END
        . ' is not valid and cannot be linked to '
        . self::VAR_START . self::VAR_NAME . self::VAR_END . '"';
    case MANDATORY_VIEW_IN_LINK_INVALID = 'view '
        . self::VAR_START . self::VAR_VIEW_NAME . self::VAR_END
        . ' is not valid and cannot be linked to '
        . self::VAR_START . self::VAR_NAME . self::VAR_END . '"';
    case MANDATORY_COMPONENT_IN_LINK_INVALID = 'component '
        . self::VAR_START . self::VAR_COMPONENT_NAME . self::VAR_END
        . ' is not valid and cannot be linked to '
        . self::VAR_START . self::VAR_NAME . self::VAR_END . '"';

    case DB_SQL_TYPE_UNKNOWN = 'database type "'
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '" is not expected';
    case DB_SQL_EXE_PREPARE_ERROR = 'creating the prepared SQL statement "'
        . self::VAR_START . self::VAR_SQL . self::VAR_END
        . '" failed due to '
        . self::VAR_START . self::VAR_SQL_REASON . self::VAR_END
        . ' is not expected. this error can be traced with this link'
        . self::VAR_START . self::VAR_TRACE_LINK . self::VAR_END
        . '.';

    case PHRASE_TYPE_NOT_FOUND = 'word/triple type "'
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '" not found';
    case SOURCE_TYPE_NOT_FOUND = 'source type "'
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '" not found';
    case FORMULA_TYPE_NOT_FOUND = 'formula type "'
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '" not found';
    case VIEW_TYPE_NOT_FOUND = 'view type "'
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '" not found';
    case COMPONENT_TYPE_NOT_FOUND = 'component type "'
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '" not found';
    case VIEW_STYLE_NOT_FOUND = 'view style "'
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '" not found';
    case VIEW_IMPORT_ERROR = ' when importing '
        . self::VAR_START . self::VAR_JSON_TEXT . self::VAR_END;
    case VIEW_NAME_MISSING = 'name in view missing';
    case NOT_YET_IMPLEMENTED = 'not yet implemented';
    case CANNOT_ADD_TIMESTAMP = 'Cannot add timestamp "'
        . self::VAR_START . self::VAR_VALUE . self::VAR_END
        . '" when importing '
        . self::VAR_START . self::VAR_ID . self::VAR_END;
    case NULL_VALUE_NOT_SAVED = 'null value for '
        . self::VAR_START . self::VAR_ID . self::VAR_END
        . ' not saved';
    case CANNOT_SAVE_ZERO_ID = 'cannot save '
        . self::VAR_START . self::VAR_ID . self::VAR_END
        . ' because id is zero';
    case VALUE_TIME_SERIES_LOG_REF_FAILED = 'adding the value time series reference in the system log failed';
    case VALUE_REFERENCE_LOG_REF_FAILED = 'adding the value reference in the system log failed';
    case SHARE_TYPE_NOT_EXPECTED = 'share type "'
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '" is not expected when importing '
        . self::VAR_START . self::VAR_JSON_TEXT . self::VAR_END;
    case PROTECTION_TYPE_NOT_EXPECTED = 'protection type "'
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '" is not expected when importing '
        . self::VAR_START . self::VAR_JSON_TEXT . self::VAR_END;
    case USER_SANDBOX_CREATION_FAILED = 'creation of user sandbox for '
        . self::VAR_START . self::VAR_ID . self::VAR_END
        . ' failed';
    case REMOVE_FIELD_FAILED = 'remove of '
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . ' failed';
    case DATABASE_UPDATE_FIELD_TO_VALUE_FAILED = 'update of '
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . ' to '
        . self::VAR_START . self::VAR_VALUE . self::VAR_END
        . ' failed';
    case EXCLUDING_FAILED = 'excluding of '
        . self::VAR_START . self::VAR_CLASS_NAME . self::VAR_END
        . ' failed';
    case USER_SANDBOX_TO_EXCLUDE_FAILED = 'creation of user sandbox to exclude failed';
    case INCLUDE_FOR_USER_FAILED = 'include of '
        . self::VAR_START . self::VAR_CLASS_NAME . self::VAR_END
        . ' for user failed';
    case EXCLUDING_FOR_USER_FAILED = 'excluding of '
        . self::VAR_START . self::VAR_CLASS_NAME . self::VAR_END
        . ' for user failed';
    case USER_SANDBOX_DELETE_IF_NOT_NEEDED_FAILED = 'remove of user sandbox if not needed for '
        . self::VAR_START . self::VAR_CLASS_NAME . self::VAR_END
        . ' failed';
    case USER_SANDBOX_CANNOT_BE_CLEANED = ' and user sandbox cannot be cleaned';
    case FAILED_TO_DELETE_UNUSED = 'Failed to delete the unused '
        . self::VAR_START . self::VAR_CLASS_NAME . self::VAR_END;
    case IMPORT_COUNT_DIFF = 'import of "'
        . self::VAR_START . self::VAR_FILE_NAME . self::VAR_END
        . '" failed because only '
        . self::VAR_START . self::VAR_VALUE_COUNT . self::VAR_END
        . ' are in the database instead of '
        . self::VAR_START . self::VAR_VALUE_COUNT_CHK . self::VAR_END
        . '.';
    case IMPORT_VALUES_MISSING = 'import of "'
        . self::VAR_START . self::VAR_FILE_NAME . self::VAR_END
        . '" failed because these values are missing '
        . self::VAR_START . self::VAR_VALUE_LIST . self::VAR_END
        . '.';
    case IMPORT_TRIPLE_NOT_READY = 'import of "'
        . self::VAR_START . self::VAR_FILE_NAME . self::VAR_END
        . '" failed because the triple "'
        . self::VAR_START . self::VAR_TRIPLE_NAME . self::VAR_END
        . '" is incomplete.';
    case IMPORT_COMPONENT_NOT_READY = 'import of "'
        . self::VAR_START . self::VAR_FILE_NAME . self::VAR_END
        . '" failed because the component "'
        . self::VAR_START . self::VAR_FORMULA . self::VAR_END
        . '" is incomplete.';
    case IMPORT_FORMULA_NOT_READY = 'import of "'
        . self::VAR_START . self::VAR_FILE_NAME . self::VAR_END
        . '" failed because the formula "'
        . self::VAR_START . self::VAR_FORMULA . self::VAR_END
        . '" is incomplete.';
    case IMPORT_FORMULA_WORD_NOT_READY = 'Word with the formula name "'
        . self::VAR_START . self::VAR_WORD_NAME . self::VAR_END
        . '" missing for id '
        . self::VAR_START . self::VAR_FORMULA . self::VAR_END
        . '.';
    case IMPORT_FORMULA_ASSIGN_PHRASE_MISSING = 'import of "'
        . self::VAR_START . self::VAR_FILE_NAME . self::VAR_END
        . '" failed because "'
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '" should be assigned to formula "'
        . self::VAR_START . self::VAR_FORMULA . self::VAR_END
        . '" but it is not defined.';

    case IMPORT_FORMULA_FAILED = 'import of formula "'
        . self::VAR_START . self::VAR_FORMULA . self::VAR_END
        . '" failed when importing "'
        . self::VAR_START . self::VAR_FILE_NAME . self::VAR_END
        . '"';

    case IMPORT_VALUE_COUNT_VALIDATED = 'import from "'
        . self::VAR_START . self::VAR_FILE_NAME . self::VAR_END
        . '" validated by counting '
        . self::VAR_START . self::VAR_VALUE_COUNT . self::VAR_END
        . ' values';
    case IMPORT_FAIL_BECAUSE = 'import of "'
        . self::VAR_START . self::VAR_FILE_NAME . self::VAR_END
        . '" failed because '
        . self::VAR_START . self::VAR_VALUE_LIST . self::VAR_END
        . '.';
    case IMPORT_IP_MISSING = 'mandatory '
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . ' ip of range missing in import json part "'
        . self::VAR_START . self::VAR_IP_RANGE . self::VAR_END
        . '".';
    case IMPORT_TERM_VIEW_DOUBLE = 'the term '
        . self::VAR_START . self::VAR_TERM_NAME . self::VAR_END
        . ' is probable assigned more than once to the view "'
        . self::VAR_START . self::VAR_VIEW_NAME . self::VAR_END
        . ' in the import json part "'
        . self::VAR_START . self::VAR_JSON_PART . self::VAR_END
        . '".';
    case IMPORT_NOT_FIND_VIEW = 'Cannot find view "'
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '" when importing '
        . self::VAR_START . self::VAR_ID . self::VAR_END;
    case FROM_NAME_NOT_EMPTY = 'from name should not be empty at "'
        . self::VAR_START . self::VAR_JSON_TEXT . self::VAR_END
        . '"';
    case TO_NAME_NOT_EMPTY = 'to name should not be empty at "'
        . self::VAR_START . self::VAR_JSON_TEXT . self::VAR_END
        . '"';
    case TRIPLE_VERB_CREATED = 'verb "'
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '" for triple "'
        . self::VAR_START . self::VAR_ID . self::VAR_END
        . '" created';
    case TRIPLE_VERB_MISSING = 'verb for triple "'
        . self::VAR_START . self::VAR_ID . self::VAR_END
        . '" missing';
    case TRIPLE_VERB_NOT_FOUND = 'verb "'
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '" not found';
    case FOR_TRIPLE = 'for triple "'
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '"';
    case FAILED_ADD_TRIPLE = 'Adding triple "'
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '" failed';
    case REVERSE_ALREADY_EXISTS = 'The reverse of "'
        . self::VAR_START . self::VAR_SOURCE_NAME . self::VAR_END
        . ' '
        . self::VAR_START . self::VAR_VERB_NAME . self::VAR_END
        . ' '
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '" already exists. Do you really want to create both sides?';
    case FAILED_RELOAD_CLASS = 'Reload "'
        . self::VAR_START . self::VAR_CLASS_NAME . self::VAR_END
        . '" failed';
    case FAILED_RELOAD_VALUE = 'Reload '
        . self::VAR_START . self::VAR_CLASS_NAME . self::VAR_END
        . ' '
        . self::VAR_START . self::VAR_VAL_ID . self::VAR_END
        . '" failed';
    case FAILED_RELOAD_OBJECT = 'Reload '
        . self::VAR_START . self::VAR_CLASS_NAME . self::VAR_END
        . ' '
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '" failed';
    case TRIPLE_VERB_SET = 'verb for triple '
        . self::VAR_START . self::VAR_ID . self::VAR_END
        . ' set to '
        . self::VAR_START . self::VAR_VALUE . self::VAR_END;
    case FAILED_SAVE_FORMULA_TRIGGER = 'saving the update trigger for formula '
        . self::VAR_START . self::VAR_ID . self::VAR_END
        . ' failed';
    case FAILED_ADD_FORMULA = 'Adding formula '
        . self::VAR_START . self::VAR_ID . self::VAR_END
        . ' failed.';
    case FORMULA_NOT_SIMILAR = 'Adding formula '
        . self::VAR_START . self::VAR_ID . self::VAR_END
        . ' '
        . self::VAR_START . self::VAR_VALUE . self::VAR_END
        . ' '
        . self::VAR_START . self::VAR_VAL_ID . self::VAR_END;
    case FORMULA_TERM_MISSING = '"'
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '" is missing in formula '
        . self::VAR_START . self::VAR_FORMULA . self::VAR_END
        . ' with the expression '
        . self::VAR_START . self::VAR_EXPRESSION . self::VAR_END;
    case FORMULA_REF_EXPRESSION_MISSING = 'the reference is missing in formula '
        . self::VAR_START . self::VAR_FORMULA . self::VAR_END;

    case CANNOT_DELETE_TYPE_WITH_CODE_IS = 'the type  '
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . ' with a link to the program code can only be delete by an administrator user';
    case ID_MISSING_FOR_DEL = 'Deleting of '
        . self::VAR_START . self::VAR_CLASS_NAME . self::VAR_END
        . ' not possible because database id is missing in '
        . self::VAR_START . self::VAR_NAME . self::VAR_END;

    case FAILED_ADD_GROUP = 'Adding group '
        . self::VAR_START . self::VAR_ID . self::VAR_END
        . ' failed (missing save maker).';
    case GROUP_IS_RESERVED = '"'
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '" '
        . self::VAR_START . self::VAR_JSON_TEXT . self::VAR_END;
    case USER_PROFILE_MISSING = 'user profile '
        . self::VAR_START . self::VAR_TYPE . self::VAR_END
        . ' for '
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . ' not found';
    case PHRASE_TYPE_MISSING = 'phrase type '
        . self::VAR_START . self::VAR_TYPE . self::VAR_END
        . ' for '
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . ' not found';
    case VERB_MISSING = 'verb '
        . self::VAR_START . self::VAR_TYPE . self::VAR_END
        . ' for '
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . ' not found';
    case REFERENCE_TYPE_MISSING = 'reference type '
        . self::VAR_START . self::VAR_TYPE . self::VAR_END
        . ' for '
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . ' not found';
    case VIEW_TYPE_MISSING = 'view type '
        . self::VAR_START . self::VAR_TYPE . self::VAR_END
        . ' for '
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . ' not found';
    case VIEW_STYLE_MISSING = 'view style '
        . self::VAR_START . self::VAR_TYPE . self::VAR_END
        . ' for '
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . ' not found';
    case COMPONENT_TYPE_MISSING = 'component type '
        . self::VAR_START . self::VAR_TYPE . self::VAR_END
        . ' for '
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . ' not found';
    case COMPONENT_STYLE_MISSING = 'component style '
        . self::VAR_START . self::VAR_TYPE . self::VAR_END
        . ' for '
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . ' not found';
    case FORMULA_LINK_TYPE_MISSING = 'formula link type '
        . self::VAR_START . self::VAR_TYPE . self::VAR_END
        . ' for '
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . ' not found';
    case VIEW_LINK_TYPE_MISSING = 'view link type '
        . self::VAR_START . self::VAR_TYPE . self::VAR_END
        . ' for '
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . ' not found';
    case COMPONENT_LINK_TYPE_MISSING = 'component link type '
        . self::VAR_START . self::VAR_TYPE . self::VAR_END
        . ' for '
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . ' not found';
    case COMPONENT_POS_TYPE_MISSING = 'component position type '
        . self::VAR_START . self::VAR_TYPE . self::VAR_END
        . ' for '
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . ' not found';
    case COMPONENT_LINK_STYLE_MISSING = 'component link style '
        . self::VAR_START . self::VAR_TYPE . self::VAR_END
        . ' for '
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . ' not found';
    case JOB_STATUS_MISSING = 'job status '
        . self::VAR_START . self::VAR_TYPE . self::VAR_END
        . ' for '
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . ' not found';
    case JOB_TYPE_MISSING = 'job type '
        . self::VAR_START . self::VAR_TYPE . self::VAR_END
        . ' for '
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . ' not found';

    case NO_UPDATE_PRIVILEGES =
        self::VAR_START . self::VAR_CLASS_NAME . self::VAR_END
        . '  '
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . ' cannot be changed by user '
        . self::VAR_START . self::VAR_USER_NAME . self::VAR_END;

    case USER_NO_IMPORT_PRIVILEGES = 'user "'
        . self::VAR_START . self::VAR_USER_NAME . self::VAR_END
        . '" cannot be imported due to missing privileges of the requesting user '
        . self::VAR_START . self::VAR_USER_PROFILE . self::VAR_END;
    case USER_NO_ADD_PRIVILEGES = 'user "'
        . self::VAR_START . self::VAR_USER_NAME . self::VAR_END
        . '" cannot be added due to missing privileges of the requesting user '
        . self::VAR_START . self::VAR_USER_PROFILE . self::VAR_END;
    case USER_NO_UPDATE_PRIVILEGES = 'user "'
        . self::VAR_START . self::VAR_USER_NAME . self::VAR_END
        . '" cannot be updated due to missing privileges of the requesting user '
        . self::VAR_START . self::VAR_USER_PROFILE . self::VAR_END;
    case NOT_ALLOWED_TO = 'user "'
        . self::VAR_START . self::VAR_USER_NAME . self::VAR_END
        . '" with profile '
        . self::VAR_START . self::VAR_USER_PROFILE . self::VAR_END
        . ' is not permitted to update '
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . ' of '
        . self::VAR_START . self::VAR_CLASS_NAME . self::VAR_END;
    case USER_IS_RESERVED = 'user name "'
        . self::VAR_START . self::VAR_USER_NAME . self::VAR_END
        . '" is used by the system. Please use another name, which should not be one of these '
        . self::VAR_START . self::VAR_NAME_LIST . self::VAR_END;
    case USER_CANNOT_DEL = 'user "'
        . self::VAR_START . self::VAR_USER_NAME . self::VAR_END
        . '" cannot be deleted because otherwise log entries would be lost';
    case DEFAULT_VALUES_RELOADING_FAILED = 'Reloading of the default values for '
        . self::VAR_START . self::VAR_VALUE . self::VAR_END
        . ' failed';

    case DB_PHRASE_MISSING = 'phrase '
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . ' is unexpected missing in database during import';
    case DB_TERM_MISSING = 'term '
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . ' is unexpected missing in database during import';


    case CONFLICT_DB_ID = 'Unexpected conflict of the database id. '
        . self::VAR_START . self::VAR_ID . self::VAR_END
        . ' != '
        . self::VAR_START . self::VAR_ID . self::VAR_END;

    case DB_CLEANUP_ERROR = 'There are '
        . self::VAR_START . self::VAR_COUNTER . self::VAR_END
        . ' unexpected system test rows detected by '
        . self::VAR_START . self::VAR_FILE_NAME . self::VAR_END;

    case IMPORT_PHRASE_NOT_FOUND = 'Cannot find word or triple "'
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '" when importing '
        . self::VAR_START . self::VAR_ID . self::VAR_END;
    case IMPORT_TERM_NOT_FOUND = 'Cannot find word, verb, triple or formula "'
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '" when importing '
        . self::VAR_START . self::VAR_ID . self::VAR_END;

    case IMPORT_SOURCE_NOT_FOUND = 'Cannot find source "'
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '" when importing '
        . self::VAR_START . self::VAR_ID . self::VAR_END;

    case CLASS_ALREADY_EXISTS = 'A '
        . self::VAR_START . self::VAR_CLASS_NAME . self::VAR_END
        . ' with the name "'
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '" already exists. Please use another '
        . self::VAR_START . self::VAR_VALUE . self::VAR_END
        . ' name.';

    case JOB_TYPE_INVALID = 'the job type for job '
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . ' is not valid';

    case JOB_ROW_MISSING = 'the database id for job '
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . ' is missing';

    case IP_RANGE_FROM_MISSING = 'the from value of the ip range '
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . ' is not valid';

    case IP_RANGE_TO_MISSING = 'the to value of the ip range '
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . ' is not valid';

    case VERB_UPDATE_FAILED = 'updating '
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . ' to '
        . self::VAR_START . self::VAR_VALUE . self::VAR_END
        . ' for verb '
        . self::VAR_START . self::VAR_ID . self::VAR_END
        . ' failed';

    case VERB_ADD_FAILED = 'Adding verb '
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . ' failed.';

    case SYS_MSG_USAGE = 'Used '
        . self::VAR_START . self::VAR_USAGE . self::VAR_END
        . ' times';


    case JOB_FORMULA_MISSING = 'Job '
        . self::VAR_START . self::VAR_ID . self::VAR_END
        . ' cannot be added, because formula is missing.';
    case JOB_WORD_MISSING = 'Job '
        . self::VAR_START . self::VAR_ID . self::VAR_END
        . ' cannot be added, because no words or triples are defined.';
    case JOB_ALREADY_ACTIVE = 'Job for phrases '
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . ' is already in the list of active jobs';

    case UPDATE_FAILED = 'updating '
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . ' to '
        . self::VAR_START . self::VAR_VALUE . self::VAR_END
        . ' for '
        . self::VAR_START . self::VAR_CLASS_NAME . self::VAR_END
        . ' '
        . self::VAR_START . self::VAR_ID . self::VAR_END
        . ' failed';

    case SANDBOX_NOT_SIMILAR = self::VAR_START . self::VAR_ID . self::VAR_END
        . ' seems to be not similar to '
        . self::VAR_START . self::VAR_ID_CHK . self::VAR_END;

    // for the change log
    case LOG_ADD = 'added';
    case LOG_UPDATE = 'changed';
    case LOG_DEL = 'deleted';
    case LOG_LINK = 'linked';
    case LOG_TO = 'to';

    // import
    case IMPORT_JSON = 'import';
    case COUNT = 'count';
    case LOAD = 'load';
    case DECODED = 'decoded';
    case CHECK = 'check';
    case PREPARE = 'prepare';
    case SAVE = 'save';
    case SAVE_SINGLE = 'save single';
    case SAVE_LIST = 'save list';
    case VALIDATE = 'validate';
    case PHRASE_MISSING = 'phrase missing';
    case PHRASE_MISSING_FROM = 'phrase missing from';
    case PHRASE_MISSING_TO = 'phrase missing to';
    case PHRASE_MISSING_ID = 'phrase id is zero';
    case PHRASE_ID_NOT_FOUND = 'phrase id not found';
    case TERM_ID_NOT_FOUND = 'term id not found';
    case NOT_USED_FOR_VERB = 'not used for verb';
    case NOT_USED_FOR_TRIPLES = 'not used for triples';
    case INFO_NOT_USED_FOR_FORMULAS = 'info_not_used_for_formulas';

    // e.g. if an import formula does not contain all needed parameters
    case FORMULA_NOT_VALID = 'formula is not valid';
    case TRIPLE_NOT_VALID = 'triple is not valid';

    // config
    case CONFIG_NOT_LOADED = 'cannot load config';
    case CONFIG_EMPTY = 'config is empty';
    case IP_LIST_EMPTY = 'ip range list is empty on import';
    case CONFIG_API_MESSAGE_EMPTY = 'config api message is empty';

    case ADD_USER_CONFIG_FAILED = 'adding of user configuration failed';

    // text to be shown in frontend
    // TODO add translation
    case AND_MORE_BEFORE = 'and';
    case AND_MORE_AFTER = 'more';
    case THREE_POINTS = '...';

    // text to be shown in buttons
    case ADD = 'add';
    case EDIT = 'edit';
    case DEL = 'del';
    case SEARCH_MAIN = 'search_main';
    case WORD_ADD = 'word_add';
    case WORD_EDIT = 'word_edit';
    case WORD_DEL = 'word_del';
    case WORD_UNLINK = 'unlink_word';
    case VERB_ADD = 'verb_add';
    case VERB_EDIT = 'verb_edit';
    case VERB_DEL = 'verb_del';
    case TRIPLE_ADD = 'triple_add';
    case TRIPLE_EDIT = 'triple_edit';
    case TRIPLE_DEL = 'triple_del';
    case SOURCE_ADD = 'source_add';
    case SOURCE_EDIT = 'source_edit';
    case SOURCE_DEL = 'source_del';
    case REF_ADD = 'ref_add';
    case REF_EDIT = 'ref_edit';
    case REF_DEL = 'ref_del';
    case VALUE_ADD = 'value_add';
    case VALUE_ADD_SIMILAR = 'value_add_similar';
    case VALUE_EDIT = 'value_edit';
    case VALUE_DEL = 'value_del';
    case FORMULA_ADD = 'formula_add';
    case FORMULA_EDIT = 'formula_edit';
    case FORMULA_DEL = 'formula_del';
    case FORMULA_LINK = 'formula_link';
    case FORMULA_UNLINK = 'formula_unlink';
    case RESULT_EDIT = 'result_edit';
    case RESULT_DEL = 'result_del';
    case VIEW_ADD = 'view_add';
    case VIEW_EDIT = 'view_edit';
    case VIEW_DEL = 'view_del';
    case COMPONENT_ADD = 'component_add';
    case COMPONENT_EDIT = 'component_edit';
    case COMPONENT_DEL = 'component_del';
    case COMPONENT_LINK = 'component_link';
    case COMPONENT_UNLINK = 'component_unlink';
    case VIEW_LINK_ADD = 'view_link_add';
    case VIEW_LINK_EDIT = 'view_link_edit';
    case VIEW_LINK_DEL = 'view_link_del';
    case COMPONENT_LINK_ADD = 'component_link_add';
    case COMPONENT_LINK_EDIT = 'component_link_edit';
    case COMPONENT_LINK_DEL = 'component_link_del';
    case FORMULA_LINK_ADD = 'formula_link_add';
    case FORMULA_LINK_EDIT = 'formula_link_edit';
    case FORMULA_LINK_DEL = 'formula_link_del';
    case VIEW_RELATION_ADD = 'view_relation_add';
    case VIEW_RELATION_EDIT = 'view_relation_edit';
    case VIEW_RELATION_DEL = 'view_relation_del';
    case USER_ADD = 'user_add';
    case USER_EDIT = 'user_edit';
    case USER_DEL = 'user_del';
    case PLEASE_SELECT = 'please_select';

    /*
     * view
     */

    // messages used to translate the fixed text of views

    // view titles: form_title_*
    case FORM_TITLE_WORD_ADD = 'form_title_word_add';
    case FORM_TITLE_WORD_EDIT = 'form_title_word_edit';
    case FORM_TITLE_WORD_DEL = 'form_title_word_del';
    case FORM_TITLE_VERB_ADD = 'form_title_verb_add';
    case FORM_TITLE_VERB_EDIT = 'form_title_verb_edit';
    case FORM_TITLE_VERB_DEL = 'form_title_verb_del';
    case FORM_TRIPLE_ADD_TITLE = 'form_title_triple_add';
    case FORM_TITLE_TRIPLE_EDIT = 'form_title_triple_edit';
    case FORM_TITLE_TRIPLE_DEL = 'form_title_triple_del';
    case FORM_TITLE_SOURCE_ADD = 'form_title_source_add';
    case FORM_TITLE_SOURCE_EDIT = 'form_title_source_edit';
    case FORM_TITLE_SOURCE_DEL = 'form_title_source_del';
    case FORM_TITLE_REF_ADD = 'form_title_ref_add';
    case FORM_TITLE_REF_EDIT = 'form_title_ref_edit';
    case FORM_TITLE_REF_DEL = 'form_title_ref_del';
    case FORM_TITLE_GROUP_ADD = 'form_title_group_add';
    case FORM_TITLE_GROUP_EDIT = 'form_title_group_edit';
    case FORM_TITLE_GROUP_DEL = 'form_title_group_del';
    case FORM_TITLE_VALUE_ADD = 'form_title_value_add';
    case FORM_TITLE_VALUE_EDIT = 'form_title_value_edit';
    case FORM_TITLE_VALUE_DEL = 'form_title_value_del';
    case FORM_TITLE_FORMULA_ADD = 'form_title_formula_add';
    case FORM_TITLE_FORMULA_EDIT = 'form_title_formula_edit';
    case FORM_TITLE_FORMULA_DEL = 'form_title_formula_del';
    case FORM_TITLE_RESULT_ADD = 'form_title_result_add';
    case FORM_TITLE_RESULT_EDIT = 'form_title_result_edit';
    case FORM_TITLE_RESULT_DEL = 'form_title_result_del';
    case FORM_TITLE_VIEW_ADD = 'form_title_view_add';
    case FORM_TITLE_VIEW_EDIT = 'form_title_view_edit';
    case FORM_TITLE_VIEW_DEL = 'form_title_view_del';
    case FORM_TITLE_COMPONENT_ADD = 'form_title_component_add';
    case FORM_TITLE_COMPONENT_EDIT = 'form_title_component_edit';
    case FORM_TITLE_COMPONENT_DEL = 'form_title_component_del';
    case FORM_TITLE_VIEW_LINK_ADD = 'form_title_view_link_add';
    case FORM_TITLE_VIEW_LINK_EDIT = 'form_title_view_link_edit';
    case FORM_TITLE_VIEW_LINK_DEL = 'form_title_view_link_del';
    case FORM_TITLE_COMPONENT_LINK_ADD = 'form_title_component_link_add';
    case FORM_TITLE_COMPONENT_LINK_EDIT = 'form_title_component_link_edit';
    case FORM_TITLE_COMPONENT_LINK_DEL = 'form_title_component_link_del';
    case FORM_TITLE_VIEW_RELATION_ADD = 'form_title_view_relation_add';
    case FORM_TITLE_VIEW_RELATION_EDIT = 'form_title_view_relation_edit';
    case FORM_TITLE_VIEW_RELATION_DEL = 'form_title_view_relation_del';
    case FORM_TITLE_FORMULA_LINK_ADD = 'form_title_formula_link_add';
    case FORM_TITLE_FORMULA_LINK_EDIT = 'form_title_formula_link_edit';
    case FORM_TITLE_FORMULA_LINK_DEL = 'form_title_formula_link_del';
    case FORM_TITLE_USER_ADD_BY_ADMIN = 'form_title_admin_user_add';
    case FORM_TITLE_USER_EDIT_BY_ADMIN = 'form_title_admin_user_edit';
    case FORM_TITLE_USER_DEL_BY_ADMIN = 'form_title_admin_user_del';
    case FORM_TITLE_LANGUAGE_ADD_BY_ADMIN = 'form_title_admin_add_language';
    case FORM_TITLE_LANGUAGE_EDIT_BY_ADMIN = 'form_title_admin_edit_language';
    case FORM_TITLE_LANGUAGE_DEL_BY_ADMIN = 'form_title_admin_del_language';
    case FORM_TITLE_CONFIRM_ADD = 'form_title_confirm_add';
    case FORM_TITLE_CONFIRM_EDIT = 'form_title_confirm_edit';
    case FORM_TITLE_CONFIRM_DEL = 'form_title_confirm_del';

    // sub titles
    case FORM_SUB_TITLE_USAGE = 'system_sub_title_usage';
    case FORM_SUB_TITLE_VAR_USAGE = 'system_sub_title_var_usage';
    case FORM_SUB_TITLE_NO_USAGE = 'system_sub_title_no_usage';
    case FORM_SUB_TITLE_TRIPLES = 'system_sub_title_triples';
    case FORM_SUB_TITLE_REF = 'system_sub_title_references';
    case FORM_SUB_TITLE_VALUES = 'system_sub_title_values';
    case FORM_SUB_TITLE_FORMULAS = 'system_sub_title_formulas';
    case FORM_SUB_TITLE_ASSIGNED_PHRASES = 'system_sub_title_assigned_phrases';
    case FORM_SUB_TITLE_RESULTS = 'system_sub_title_results';
    case FORM_SUB_TITLE_LOG = 'system_sub_title_log';

    // log, im- and export titles
    case FORM_TITLE_ERROR_LOG = 'system_title_error_log';
    case FORM_TITLE_ERROR_UPDATE = 'system_title_error_update';
    case FORM_TITLE_SEARCH = 'system_title_search';
    case FORM_TITLE_SEARCH_FULL = 'system_title_search_full';
    case FORM_TITLE_SANDBOX = 'system_title_sandbox';
    case FORM_TITLE_UNDO = 'system_title_undo';
    case FORM_PASTE_TABLE = 'system_paste_table';
    case FORM_TITLE_IMPORT = 'system_title_import';
    case FORM_TITLE_EXPORT = 'system_title_export';
    case FORM_TITLE_EXPORT_JSON = 'system_title_export_json';
    case FORM_TITLE_EXPORT_XML = 'system_title_export_xml';
    case FORM_TITLE_EXPORT_CSV = 'system_title_export_csv';
    case FORM_TITLE_EXPORT_ODS = 'system_title_export_ods';
    case FORM_TITLE_PROCESS_ASYNC = 'system_title_process_async';
    case FORM_TITLE_PROCESS_LIST = 'system_title_process_list';
    case FORM_TITLE_PROCESS = 'system_title_process';

    // fixed system page titles
    case SYSTEM_TITLE_ABOUT = 'system_title_about';
    case SYSTEM_TITLE_SETUP = 'system_title_setup';
    case SYSTEM_TITLE_SIGNUP = 'system_title_signup';
    case SYSTEM_TITLE_LOGIN = 'system_title_login';
    case SYSTEM_TITLE_LOGIN_ACTIVATE = 'system_title_login_activate';
    case SYSTEM_TITLE_LOGIN_RESET = 'system_title_login_reset';
    case SYSTEM_TITLE_LOGOUT = 'system_title_logout';
    case SYSTEM_TITLE_VALUE_DETAIL = 'system_title_value_detail';
    case SYSTEM_TITLE_RESULT_EXPLAIN = 'system_title_result_explain';
    case SYSTEM_TITLE_FORMULA_TEST = 'system_title_formula_test';
    case SYSTEM_TITLE_USER_SETTINGS = 'system_title_user_settings';


    /*
     * form fields
     */

    // internal and hidden form fields
    case FORM_FIELD_STEP = 'form_field_step';
    case FORM_FIELD_CONFIRM = 'form_field_confirm';
    case FORM_FIELD_MASK = 'form_field_mask';
    case FORM_FIELD_ID = 'form_field_id';
    case FORM_FIELD_BACK = 'form_field_back';
    case FORM_FIELD_PATTERN = 'form_field_pattern';
    case FORM_FIELD_LANGUAGE_SYMBOL = 'form_field_language_symbol';
    case FIELD_LANGUAGE_SYMBOL = 'field_language_symbol';

    // text input form fields
    // general fields used in more than one view
    case FORM_FIELD_NAME = 'form_field_name';
    case FORM_FIELD_NAME_FORMULA = 'form_field_name_formula';
    case FORM_FIELD_NAME_COMPONENT = 'form_field_name_component';
    case FORM_FIELD_DESCRIPTION = 'form_field_description';
    case FORM_FIELD_TYPE = 'form_field_type';

    // word, triple and phrase fields
    case FORM_FIELD_WEIGHT = 'form_field_weight';
    case FORM_FIELD_PHRASE_LIST = 'form_field_phrase_list';

    // value and result fields
    case FORM_FIELD_VALUE = 'form_field_value';
    case FORM_FIELD_GROUP = 'form_field_group';
    case FORM_FIELD_GROUP_OR_PHRASE_LIST = 'form_field_group_or_phrase_list';
    case FORM_FIELD_SOURCE_GROUP_OR_PHRASE_LIST = 'form_field_source_group_or_phrase_list';

    // source and reference fields
    case FORM_FIELD_URL = 'form_field_url';
    case FORM_FIELD_EXTERNAL_KEY = 'form_field_external_key';

    // formula fields
    case FORM_FIELD_FORMULA_LINK_PRIO = 'form_field_formula_link_prio';
    case FORM_FIELD_FORMULA_EXPRESSION = 'form_field_formula_expression';
    case FORM_FIELD_FORMULA_ALL_VARS = 'form_field_formula_all_vars';

    // view fields
    case FORM_FIELD_VIEW_TERM_LINK_PRIO = 'form_field_view_term_link_prio';
    case FORM_FIELD_COMPONENT_LINK = 'form_field_component_link';

    // export fields
    case FORM_FIELD_SELECTION_NAME = 'system_form_selection_name';
    case FORM_FIELD_SELECTION_DESCRIPTION = 'system_form_selection_description';
    case FORM_FIELD_SELECTION_TEXT = 'system_form_selection_text';

    // language form fields
    case FORM_FIELD_PLURAL = 'form_field_plural';
    case FORM_FIELD_REVERSE = 'form_field_reverse';
    case FORM_FIELD_PLURAL_REVERSE = 'form_field_plural_reverse';
    case FORM_FIELD_NAME_IN_FORMULAS = 'form_field_name_in_formulas';


    // select input form fields
    case FORM_SELECT = 'form_select'; // dummy label as fallback value for selections

    // word, verb and triple select fields
    case FORM_SELECT_WORD = 'form_select_word';
    case FORM_SELECT_VERB = 'form_select_verb';
    case FORM_SELECT_MULTI_VERBS = 'form_select_multi_verbs';

    // phrase select fields
    case FORM_SELECT_PHRASE = 'form_select_phrase';
    case FORM_SELECT_PHRASE_FROM = 'form_select_phrase_from';
    case FORM_SELECT_PHRASE_TO = 'form_select_phrase_to';
    case FORM_SELECT_MULTI_PHRASES = 'form_select_multi_phrases';
    case FORM_SELECT_PHRASE_REF = 'form_select_phrase_ref';
    case FORM_SELECT_PHRASE_ROW = 'form_select_phrase_row';
    case FORM_SELECT_PHRASE_COL = 'form_select_phrase_col';
    case FORM_SELECT_PHRASE_COL_SUB = 'form_select_phrase_col_sub';
    case FORM_SELECT_PHRASE_TYPE = 'form_select_phrase_type';

    // source and ref select fields
    case FORM_SELECT_SOURCE = 'form_select_source';
    case FORM_SELECT_MULTI_SOURCES = 'form_select_multi_sources';
    case FORM_SELECT_SOURCE_TYPE = 'form_select_source_type';
    case FORM_SELECT_REF = 'form_select_ref';
    case FORM_SELECT_MULTI_REFS = 'form_select_multi_refs';
    case FORM_SELECT_REF_TYPE = 'form_select_ref_type';

    // value and result select fields
    case FORM_SELECT_VALUE = 'form_select_value';
    case FORM_SELECT_MULTI_VALUES = 'form_select_multi_values';
    case FORM_SELECT_RESULT = 'form_select_result';
    case FORM_SELECT_MULTI_RESULTS = 'form_select_multi_results';

    // formula select fields
    case FORM_SELECT_FORMULA = 'form_select_formula';
    case FORM_SELECT_MULTI_FORMULAS = 'form_select_multi_formulas';
    case FORM_SELECT_FORMULA_TYPE = 'form_select_formula_type';
    case FORM_SELECT_FORMULA_LINK_TYPE = 'form_select_formula_link_type';
    case FORM_SELECT_FORMULA_LINK_PRIORITY = 'form_select_formula_link_priority';

    // term select fields
    case FORM_SELECT_TERM = 'form_select_term';
    case FORM_SELECT_MULTI_TERMS = 'form_select_multi_terms';

    // view select fields
    case FORM_SELECT_VIEW = 'form_select_view';
    case FORM_SELECT_PARENT_VIEW = 'form_select_parent_view';
    case FORM_SELECT_CHILD_VIEW = 'form_select_child_view';
    case FORM_SELECT_MULTI_VIEWS = 'form_select_multi_views';
    case FORM_SELECT_VIEW_TYPE = 'form_select_view_type';
    case FORM_SELECT_VIEW_STYLE = 'form_select_view_style';
    case FORM_SELECT_VIEW_LINK_TYPE = 'form_select_view_link_type';
    case FORM_SELECT_VIEW_LINK_PRIORITY = 'form_select_view_link_priority';
    case FORM_SELECT_VIEW_RELATION_TYPE = 'form_select_view_relation_type';
    case FORM_FIELD_VIEW_RELATION_START_POS = 'form_field_view_relation_start_pos';

    // view component select fields
    case FORM_SELECT_COMPONENT = 'form_select_component';
    case FORM_SELECT_MULTI_COMPONENTS = 'form_select_multi_components';
    case FORM_SELECT_COMPONENT_TYPE = 'form_select_component_type';
    case FORM_SELECT_COMPONENT_STYLE = 'form_select_component_style';
    case FORM_SELECT_COMPONENT_POS_TYPE = 'form_select_component_pos_type';
    case FORM_SELECT_COMPONENT_LINK_TYPE = 'form_select_component_link_type';
    case FORM_SELECT_COMPONENT_LINK_ORDER_NUMBER = 'form_select_component_link_order_number';

    // im- and export select fields
    case FORM_SELECT_FILE = 'form_select_file';
    case FORM_SELECT_EXPORT_FORMAT = 'form_select_export_format';

    // im- and export select fields
    case FORM_SELECT_LANGUAGE = 'form_select_language';
    case FORM_SELECT_LANGUAGE_FORM = 'form_select_language_form';

    // user select fields
    case FORM_FIELD_USERNAME = 'form_field_username';
    case FORM_FIELD_USER_EMAIL = 'form_field_user_email';
    case FORM_FIELD_USER_PASSWORD = 'form_field_user_password';
    case FORM_FIELD_USER_FIRST_NAME = 'form_field_first_name';
    case FORM_FIELD_USER_LAST_NAME = 'form_field_last_name';
    case FORM_SELECT_USER_PROFILE = 'user profile';

    // job select fields
    case FORM_SELECT_JOB_TYPE = 'form_label_job_type';

    // access select fields
    case FORM_SELECT_SHARE_TYPE = 'form_select_share';
    case FORM_SELECT_PROTECTION_TYPE = 'form_select_protection';

    // TODO review
    case FORM_FIELD_PREVIEW_CHANGE_COMPONENTS = 'system_form_preview_change_component';
    case FORM_LINK_TABLE_VIEW = 'form_link_table_view';
    case FORM_PHRASE_TYPE_FROM = 'form_phrase_type_from';
    case FORM_PHRASE_TYPE_TO = 'form_phrase_type_to';
    case FORM_SELECT_VIEW_DEFAULT = 'form_select_view_default';
    case FORM_SELECT_VALUE_TYPE = 'form_select_value_type';
    case SYSTEM_PASTE_TABLE_CONTEXT = 'system_paste_table_context';
    case SYSTEM_PASTE_TABLE_BODY = 'system_paste_table_body';
    case SYSTEM_SELECTION_TEXT = 'system_selection_text';
    case SYSTEM_POPUP_TITLE_UPDATE = 'system_popup_title_update';
    case SYSTEM_POPUP_TITLE_DELETE = 'system_popup_title_delete';
    case SELECT_VIEW = 'select_view';

    case FORM_BUTTON_CANCEL = 'form_button_cancel';
    case FORM_BUTTON_SAVE = 'form_button_save';
    case FORM_BUTTON_DEL = 'form_button_del';
    case SYSTEM_BUTTON_IMPORT = 'system_button_import';
    case SYSTEM_BUTTON_EXPORT = 'system_button_export';
    case FORM_WORD_FLD_NAME = 'form_word_fld_name';


    case UNDO = 'undo';
    case FIND = 'find';
    case REMOVE_FILTER = 'remove filter';
    case YES_NO_TEXT = 'yes or no';
    case UNDO_ADD = 'undo_add';
    case UNDO_EDIT = 'undo_edit';
    case UNDO_DEL = 'undo_del';

    // IP filter
    case IP_BLOCK_PRE_ADDR = 'ip_block_pre_addr';
    case IP_BLOCK_POST_ADDR = 'ip_block_post_addr';
    case IP_BLOCK_SOLUTION = 'ip_block_solution';

    // language elements to create a text
    case FOR = ' for '; // e.g. to indicate which phrases a value is assigned to
    case OF = ' of ';   // e.g. to indicate which word would be deleted

    case TRIPLE_FROM_PHRASE_MISSING = 'triple from phrase is missing';
    case TRIPLE_PHRASE_FROM_NAME_MISSING = 'triple phrase from name is missing and id is 0';
    case TRIPLE_PHRASE_WITHOUT_DB_ID = 'triple phrase from id is 0';
    case TRIPLE_TO_PHRASE_MISSING = 'triple to phrase is missing';
    case TRIPLE_PHRASE_TO_NAME_MISSING = 'triple phrase to name is missing and id is 0';
    case FAILED_TO_DELETE_UNUSED_WORK_LINK = 'Failed to delete the unused work link';
    case FAILED_UPDATE_REF = 'Updating the reference in the log failed';
    case FAILED_UPDATE_WORK_LINK_NAME = 'Update of work link name failed';

    case FAILED_MESSAGE_EMPTY = ' failed because message file is empty of not found.';
    case FAILED_REFRESH_FORMULA = 'Refresh of the formula elements failed';

    case OBJECT_NAME_ALREADY_EXISTS = 'A '
        . self::VAR_START . self::VAR_CLASS_NAME . self::VAR_END
        . ' with the name "'
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '" already exists. Please use another name or merge with this '
        . self::VAR_START . self::VAR_CLASS_NAME . self::VAR_END
        . '.';

    case MISSING_KEY =
        self::VAR_START . self::VAR_NAME . self::VAR_END
        . ' is missing in '
        . self::VAR_START . self::VAR_CLASS_NAME . self::VAR_END;

    /*
     * internal code errors
     */

    case MISSING_FUNCTION_OVERWRITE =
        self::VAR_START . self::VAR_FUNCTION_NAME . self::VAR_END
        . ' function is not overwritten by '
        . self::VAR_START . self::VAR_CLASS_NAME . self::VAR_END;

    case NOT_SIMILAR_OBJECTS =
        self::VAR_START . self::VAR_NAME . self::VAR_END
        . ' not similar '
        . self::VAR_START . self::VAR_NAME_CHK . self::VAR_END;
    case FAILED_RELOAD_DEFAULT_VALUES = 'Reloading of the default values for '
        . self::VAR_START . self::VAR_CLASS_NAME . self::VAR_END
        . ' failed';

    case NAME_IS_RESERVED_FOR_CLASS = '"'
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '" is a reserved '
        . self::VAR_START . self::VAR_CLASS_NAME . self::VAR_END
        . ' name';

    case FAILED_ADD_REFERENCE = 'Adding reference '
        . self::VAR_START . self::VAR_ID . self::VAR_END
        . ' failed.';
    case FAILED_ADD_VALUE = 'Adding value '
        . self::VAR_START . self::VAR_ID . self::VAR_END
        . ' failed.';

    case FAILED_ADD_REFERENCE_LOG = 'Adding reference for '
        . self::VAR_START . self::VAR_ID . self::VAR_END
        . ' in the log failed.';

    case PHRASE_NOT_FOUND = 'word or triple "'
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '" not found';
    case REFERENCE_TYPE_NOT_FOUND = 'Reference type for {VAR_TYPE_NAME} not found';
    case VAR_TYPE_NAME = 'TYPE_NAME';

    public const array FORM_TYPE_SELECTOR_LABELS_SORT_BY_ALPHA_WITH_DEFAULT = [
        self::FORM_SELECT_PHRASE_TYPE,
        self::FORM_SELECT_FORMULA_TYPE,
        self::FORM_SELECT_FORMULA_LINK_TYPE,
        self::FORM_SELECT_VIEW,
        self::FORM_SELECT_VIEW_TYPE,
        self::FORM_SELECT_VIEW_LINK_TYPE,
        self::FORM_SELECT_VIEW_RELATION_TYPE,
        self::FORM_SELECT_COMPONENT_TYPE,
        self::FORM_SELECT_COMPONENT_LINK_TYPE,
    ];

    public const array FORM_TYPE_SELECTOR_LABELS_SORT_BY_ALPHA = [
        self::FORM_FIELD_NAME,
        self::FORM_SELECT_VERB,
        self::FORM_SELECT_SOURCE,
        self::FORM_SELECT_PHRASE,
        self::FORM_SELECT_PHRASE_FROM,
        self::FORM_SELECT_PHRASE_TO,
        self::FORM_SELECT_PHRASE_ROW,
        self::FORM_SELECT_PHRASE_COL,
        self::FORM_SELECT_PHRASE_COL_SUB,
        self::FORM_SELECT_FORMULA,
        self::FORM_SELECT_TERM,
        self::FORM_SELECT_VIEW,
        self::FORM_SELECT_COMPONENT,
    ];

    /**
     * @return string with the text for the user in the default language
     */
    public function text(string $lan = ''): string
    {
        global $mtr;
        if ($lan == language_codes::SYS) {
            if ($mtr->has($this)) {
                return $mtr->txt($this);
            } else {
                return $this->value;
            }
        } else {
            return $mtr->txt($this);
        }

    }

    public static function get(string $name): messages
    {
        foreach (self::cases() as $msg_id) {
            if ($name === $msg_id->value) {
                return $msg_id;
            }
        }
        throw new ValueError("$name is not a valid backing value for enum " . self::class);
    }
}