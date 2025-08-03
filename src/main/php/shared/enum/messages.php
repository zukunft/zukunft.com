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

namespace shared\enum;

enum messages: string
{

    // start and end maker for message id within a text to allow changing the order of vars within a message
    const VAR_START = 'z$';
    const VAR_END = '$z';

    // to use the var makers without
    const VAR_ESC_START = '\z$';
    const VAR_ESC_END = '\$z';
    const VAR_TEMP_START = '\zTemp$';
    const VAR_TEMP_END = '\Temp$z';
    const VAR_TEMP_VAR = 'VarPrefix';

    // var names
    // the id of a sandbox object
    const VAR_ID = 'VarObjId';
    // the id of the compare sandbox object
    const VAR_ID_CHK = 'VarObjIdCheck';
    // the name of a sandbox object
    const VAR_NAME = 'VarObjName';
    // the list of names e.g. the reserved names that should not be used
    const VAR_NAME_LIST = 'VarObjNameList';
    // the name of the compare sandbox object
    const VAR_NAME_CHK = 'VarObjNameCheck';
    // the description of a sandbox object using dsp_id()
    const VAR_SANDBOX_NAME = 'VarSandboxName';
    // the name and if of a word
    const VAR_WORD_NAME = 'VarWordName';
    // the name and if of a triple
    const VAR_TRIPLE_NAME = 'VarTripleName';
    // the name and if of a phrase
    const VAR_PHRASE_NAME = 'VarPhraseName';
    // the name and if of a term
    const VAR_TERM_NAME = 'VarTermName';
    // the name and if of a view
    const VAR_VIEW_NAME = 'VarViewName';
    // the user/owner of an object
    const VAR_USER = 'VarUser';
    // the user/owner of a compare object
    const VAR_USER_CHK = 'VarUserCheck';
    // the name of a user
    const VAR_USER_NAME = 'VarUserName';
    // the name, profile and permissions of a user
    const VAR_USER_PROFILE = 'VarUserProfile';
    // the name of a user of a list
    const VAR_USER_LIST_NAME = 'VarUserListName';
    // the name of a sandbox object
    const VAR_TYPE = 'VarObjType';
    // the name of the compare sandbox object
    const VAR_TYPE_CHK = 'VarObjTypeCheck';
    // the id of a value object
    const VAR_VAL_ID = 'VarValueId';
    // the numeric, time, text or geo value of a value
    const VAR_VALUE = 'VarValue';
    // the numeric, time, text or geo value of a compare value
    const VAR_VALUE_CHK = 'VarValueCheck';
    // the real number of values
    const VAR_VALUE_COUNT = 'VarValueCount';
    // the expected number of values
    const VAR_VALUE_COUNT_CHK = 'VarValueCountCheck';
    // a list of values
    const VAR_VALUE_LIST = 'VarValueList';
    // the phrase group naming of a value
    const VAR_GROUP = 'VarGroup';
    // the phrase group naming of a compare value
    const VAR_GROUP_CHK = 'VarGroupCheck';
    // the source of a value
    const VAR_SOURCE = 'VarSource';
    // the source of a compare value
    const VAR_SOURCE_CHK = 'VarSourceCheck';
    const VAR_FORMULA_CHK = 'VarFormulaCheck';
    // the name of a class
    const VAR_CLASS_NAME = 'VarClassName';
    // the share permission of a sandbox object
    const VAR_SHARE = 'VarShare';
    // the share permission of the compare sandbox object
    const VAR_SHARE_CHK = 'VarShareCheck';
    // the change protection of a sandbox object
    const VAR_PROTECT = 'VarProtect';
    // the change protection of the compare sandbox object
    const VAR_PROTECT_CHK = 'VarProtectCheck';
    // the exclusion status of a sandbox object
    const VAR_EXCLUDE = 'VarExclude';
    // the exclusion status of the compare sandbox object
    const VAR_EXCLUDE_CHK = 'VarExcludeCheck';

    const VAR_JSON_TEXT = 'VarJsonText';
    const VAR_SOURCE_NAME = 'VarSourceName';
    const VAR_COMPONENT_NAME = 'VarComponentName';
    const VAR_FILE_TYPE = 'VarFileType';
    const VAR_FILE_NAME = 'VarFileName';
    const VAR_IP_RANGE = 'VarIpRange';
    const VAR_SUMMARY = 'VarSummary';
    const VAR_PART = 'VarPart';
    const VAR_ERROR_TEXT = 'VarErrorText';

    // for the object main parameters created by the dsp_id function
    const VAR_TRIPLE = 'VarObjTriple';
    const VAR_FORMULA = 'VarObjFormula';
    const VAR_EXPRESSION = 'VarObjExpression';
    const VAR_JSON_PART = 'VarJsonPart';
    const VAR_VERB_NAME = 'VarVerbName';
    const IMPORT_SUCCESS = 'finished successful';

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
    case ERROR = 'error';
    case NONE = '';

    // messages with vars
    case MISSING_OVERWRITE = 'internal function overwrite of "'
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '" is missing';
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
    case PHRASE_MISSING_MSG = 'phrase "'
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . '" is missing';
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
    case FAILED_ADD_LOGGING_ERROR = 'Adding "'
        . self::VAR_START . self::VAR_CLASS_NAME . self::VAR_END
        . '" "'
        . self::VAR_START . self::VAR_ID . self::VAR_END
        . '" failed due to logging error';
    case USED_OBJECT_ID_AND_NAME_MISSING =
        self::VAR_START . self::VAR_CLASS_NAME . self::VAR_END
        . ' "'
        . self::VAR_START . self::VAR_WORD_NAME . self::VAR_END
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

    case SOURCE_MISSING_IMPORT = 'source "'
        . self::VAR_START . self::VAR_JSON_TEXT . self::VAR_END
        . '" is missing in the import message '
        . self::VAR_START . self::VAR_SOURCE_NAME . self::VAR_END;
    case FORMULA_MISSING_IMPORT = 'formula "'
        . self::VAR_START . self::VAR_JSON_TEXT . self::VAR_END
        . '" is missing in the import message '
        . self::VAR_START . self::VAR_FORMULA . self::VAR_END;
    case FORMULA_EXPRESSION_MISSING = 'formula expression is missing in '
        . self::VAR_START . self::VAR_FORMULA . self::VAR_END;
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
    case API_MESSAGE = self::VAR_START . self::VAR_JSON_TEXT . self::VAR_END;
    case MANDATORY_FIELD_NAME_MISSING = 'Mandatory field name missing in API JSON '
        . self::VAR_START . self::VAR_JSON_TEXT . self::VAR_END;
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

    case CONFLICT_DB_ID = 'Unexpected conflict of the database id. '
        . self::VAR_START . self::VAR_ID . self::VAR_END
        . ' != '
        . self::VAR_START . self::VAR_ID . self::VAR_END;

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

    case VERB_UPDATE_FAILED = 'updating '
        . self::VAR_START . self::VAR_NAME . self::VAR_END
        . ' to '
        . self::VAR_START . self::VAR_VALUE . self::VAR_END
        . ' for verb '
        . self::VAR_START . self::VAR_ID . self::VAR_END
        . ' failed';

    case VERB_ADD_FAILED = 'Adding verb '
        .self::VAR_START . self::VAR_NAME . self::VAR_END
        . ' failed.';


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

    // e.g. if an import formula does not contain all needed parameters
    case FORMULA_NOT_VALID = 'formula is not valid';
    case TRIPLE_NOT_VALID = 'triple is not valid';

    // config
    case CONFIG_NOT_LOADED = 'cannot load config';
    case CONFIG_EMPTY = 'config is empty';
    case IP_LIST_EMPTY = 'ip range list is empty on import';
    case CONFIG_API_MESSAGE_EMPTY = 'config api message is empty';

    case ADD_USER_CONFIG_FAILED = 'adding of user configuration failed';

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
    case USER_ADD = 'user_add';
    case USER_EDIT = 'user_edit';
    case USER_DEL = 'user_del';
    case PLEASE_SELECT = 'please_select';
    case FORM_WORD_ADD_TITLE = 'form_title_word_add';
    case FORM_WORD_EDIT_TITLE = 'form_title_word_edit';
    case FORM_WORD_DEL_TITLE = 'form_title_word_del';
    case FORM_VERB_ADD_TITLE = 'form_title_verb_add';
    case FORM_VERB_EDIT_TITLE = 'form_title_verb_edit';
    case FORM_VERB_DEL_TITLE = 'form_title_verb_del';
    case FORM_TRIPLE_ADD_TITLE = 'form_title_triple_add';
    case FORM_TRIPLE_EDIT_TITLE = 'form_title_triple_edit';
    case FORM_TRIPLE_DEL_TITLE = 'form_title_triple_del';
    case FORM_SOURCE_ADD_TITLE = 'form_title_source_add';
    case FORM_SOURCE_EDIT_TITLE = 'form_title_source_edit';
    case FORM_SOURCE_DEL_TITLE = 'form_title_source_del';
    case FORM_REF_ADD_TITLE = 'form_title_ref_add';
    case FORM_REF_EDIT_TITLE = 'form_title_ref_edit';
    case FORM_REF_DEL_TITLE = 'form_title_ref_del';
    case FORM_GROUP_ADD_TITLE = 'form_title_group_add';
    case FORM_GROUP_EDIT_TITLE = 'form_title_group_edit';
    case FORM_GROUP_DEL_TITLE = 'form_title_group_del';
    case FORM_VALUE_ADD_TITLE = 'form_title_value_add';
    case FORM_VALUE_EDIT_TITLE = 'form_title_value_edit';
    case FORM_VALUE_DEL_TITLE = 'form_title_value_del';
    case FORM_FORMULA_ADD_TITLE = 'form_title_formula_add';
    case FORM_FORMULA_EDIT_TITLE = 'form_title_formula_edit';
    case FORM_FORMULA_DEL_TITLE = 'form_title_formula_del';
    case FORM_RESULT_ADD_TITLE = 'form_title_result_add';
    case FORM_RESULT_EDIT_TITLE = 'form_title_result_edit';
    case FORM_RESULT_DEL_TITLE = 'form_title_result_del';
    case FORM_VIEW_ADD_TITLE = 'form_title_view_add';
    case FORM_VIEW_EDIT_TITLE = 'form_title_view_edit';
    case FORM_VIEW_DEL_TITLE = 'form_title_view_del';
    case FORM_COMPONENT_ADD_TITLE = 'form_title_component_add';
    case FORM_COMPONENT_EDIT_TITLE = 'form_title_component_edit';
    case FORM_COMPONENT_DEL_TITLE = 'form_title_component_del';
    case FORM_FIELD_NAME = 'form_field_name';
    case FORM_FIELD_DESCRIPTION = 'form_field_description';
    case FORM_FIELD_PLURAL = 'form_field_plural';
    case FORM_FIELD_FORMULA_EXPRESSION = 'form_field_formula_expression';
    case FORM_FIELD_FORMULA_ALL_VARS = 'form_field_formula_all_vars';
    case FORM_TRIPLE_PHRASE_FROM = 'form_triple_phrase_from';
    case FORM_TRIPLE_PHRASE_TO = 'form_triple_phrase_to';
    case FORM_TRIPLE_VERB = 'form_triple_verb';
    case FORM_PHRASE_TYPE_FROM = 'form_phrase_type_from';
    case FORM_PHRASE_TYPE_TO = 'form_phrase_type_to';
    case FORM_SELECT_PHRASE_TYPE = 'form_select_phrase_type';
    case FORM_SELECT_SOURCE_TYPE = 'form_select_source_type';
    case FORM_SELECT_REF_TYPE = 'form_select_ref_type';
    case FORM_SELECT_FORMULA_TYPE = 'form_select_formula_type';
    case FORM_SELECT_VIEW_TYPE = 'form_select_view_type';
    case FORM_SELECT_COMPONENT_TYPE = 'form_select_component_type';
    case SELECT_VIEW = 'select_view';
    case FORM_SELECT_SHARE = 'form_select_share';
    case FORM_SELECT_PROTECTION = 'form_select_protection';
    case FORM_BUTTON_CANCEL = 'form_button_cancel';
    case FORM_BUTTON_SAVE = 'form_button_save';
    case FORM_BUTTON_DEL = 'form_button_del';
    case FORM_WORD_FLD_NAME = 'form_word_fld_name';
    case UNDO = 'undo';
    case FIND = 'find';
    case REMOVE_FILTER = 'remove filter';
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
    case DUMMY_PARENT_ADD_FUNCTION_CALLED = 'The dummy parent add function has been called, which should never happen';
    case NOT_SIMILAR_OBJECTS = ''
        . self::VAR_START . self::VAR_NAME . self::VAR_END
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
        throw new \ValueError("$name is not a valid backing value for enum " . self::class);
    }
}