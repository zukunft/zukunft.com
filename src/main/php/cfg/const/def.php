<?php

/*

    model/const/def.php - general system definitions
    -------------------


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

namespace Zukunft\ZukunftCom\main\php\cfg\const;

//include_once paths::MODEL_COMPONENT . 'component.php';
//include_once paths::MODEL_COMPONENT . 'component_link.php';
//include_once paths::MODEL_COMPONENT . 'component_list.php';
//include_once paths::MODEL_COMPONENT . 'component_type.php';
//include_once paths::MODEL_COMPONENT . 'component_link_type.php';
//include_once paths::MODEL_COMPONENT . 'position_type.php';
//include_once paths::MODEL_COMPONENT . 'view_style.php';
//include_once paths::MODEL_ELEMENT . 'element.php';
//include_once paths::MODEL_ELEMENT . 'element_type.php';
//include_once paths::MODEL_GROUP . 'group.php';
//include_once paths::MODEL_FORMULA . 'formula.php';
//include_once paths::MODEL_FORMULA . 'formula_db.php';
//include_once paths::MODEL_FORMULA . 'formula_map.php';
//include_once paths::MODEL_FORMULA . 'formula_type.php';
//include_once paths::MODEL_FORMULA . 'formula_link.php';
//include_once paths::MODEL_FORMULA . 'formula_link_type.php';
//include_once paths::MODEL_LANGUAGE . 'language.php';
//include_once paths::MODEL_LANGUAGE . 'language_form.php';
//include_once paths::MODEL_LOG . 'change.php';
//include_once paths::MODEL_LOG . 'change_action.php';
//include_once paths::MODEL_LOG . 'change_table.php';
//include_once paths::MODEL_LOG . 'change_field.php';
//include_once paths::MODEL_LOG . 'change_link.php';
//include_once paths::MODEL_LOG . 'change_log.php';
//include_once paths::MODEL_LOG . 'change_value.php';
//include_once paths::MODEL_LOG . 'change_values_big.php';
//include_once paths::MODEL_LOG . 'change_values_norm.php';
//include_once paths::MODEL_LOG . 'change_values_prime.php';
//include_once paths::MODEL_LOG . 'change_values_time_big.php';
//include_once paths::MODEL_LOG . 'change_values_time_norm.php';
//include_once paths::MODEL_LOG . 'change_values_text_norm.php';
//include_once paths::MODEL_LOG . 'change_values_time_prime.php';
//include_once paths::MODEL_LOG . 'change_values_text_big.php';
//include_once paths::MODEL_LOG . 'change_values_text_prime.php';
//include_once paths::MODEL_LOG . 'change_values_geo_big.php';
//include_once paths::MODEL_LOG . 'change_values_geo_norm.php';
//include_once paths::MODEL_LOG . 'change_values_geo_prime.php';
//include_once paths::MODEL_LOG . 'changes_big.php';
//include_once paths::MODEL_LOG . 'changes_norm.php';
//include_once paths::MODEL_PHRASE . 'phrase_types.php';
//include_once paths::MODEL_REF . 'ref.php';
//include_once paths::MODEL_REF . 'ref_type.php';
//include_once paths::MODEL_REF . 'source.php';
//include_once paths::MODEL_REF . 'source_list.php';
//include_once paths::MODEL_REF . 'source_type.php';
//include_once paths::MODEL_RESULT . 'result.php';
//include_once paths::MODEL_RESULT . 'result_db.php';
//include_once paths::MODEL_SANDBOX . 'sandbox_multi.php';
//include_once paths::MODEL_SYSTEM . 'job.php';
//include_once paths::MODEL_SYSTEM . 'job_status.php';
//include_once paths::MODEL_SYSTEM . 'job_type.php';
//include_once paths::MODEL_SYSTEM . 'pod.php';
//include_once paths::MODEL_SYSTEM . 'session.php';
//include_once paths::MODEL_SYSTEM . 'sys_log.php';
//include_once paths::MODEL_SYSTEM . 'sys_log_function.php';
//include_once paths::MODEL_SYSTEM . 'sys_log_status.php';
//include_once paths::MODEL_SYSTEM . 'sys_log_level.php';
//include_once paths::MODEL_SYSTEM . 'system_time.php';
//include_once paths::MODEL_USER . 'user.php';
//include_once paths::MODEL_USER . 'user_db.php';
//include_once paths::MODEL_USER . 'user_list.php';
//include_once paths::MODEL_USER . 'user_profile.php';
//include_once paths::MODEL_USER . 'user_status.php';
//include_once paths::MODEL_USER . 'user_type.php';
//include_once paths::MODEL_USER . 'user_official_type.php';
//include_once paths::MODEL_VALUE . 'value.php';
//include_once paths::MODEL_VALUE . 'value_geo.php';
//include_once paths::MODEL_VALUE . 'value_text.php';
//include_once paths::MODEL_VALUE . 'value_time.php';
//include_once paths::MODEL_VALUE . 'value_time_series.php';
//include_once paths::MODEL_VERB . 'verb.php';
//include_once paths::MODEL_VERB . 'verb_list.php';
//include_once paths::MODEL_VIEW . 'term_view.php';
//include_once paths::MODEL_VIEW . 'view.php';
//include_once paths::MODEL_VIEW . 'view_list.php';
//include_once paths::MODEL_VIEW . 'view_type.php';
//include_once paths::MODEL_VIEW . 'view_link_type.php';
//include_once paths::MODEL_VIEW . 'view_relation.php';
//include_once paths::MODEL_WORD . 'triple.php';
//include_once paths::MODEL_WORD . 'triple_list.php';
//include_once paths::MODEL_WORD . 'word.php';
//include_once paths::MODEL_WORD . 'word_list.php';
//include_once paths::SHARED_ENUM . 'sys_log_statuum.php';
//include_once paths::SHARED_ENUM . 'user_statuum.php';
//include_once paths::SHARED_TYPES . 'system_time_type.php';
//include_once paths::SHARED_TYPES . 'protection_types.php';
//include_once paths::SHARED_TYPES . 'share_types.php';
//include_once paths::SHARED_TYPES . 'view_relation_types.php';

use Zukunft\ZukunftCom\main\php\cfg\component\component;
use Zukunft\ZukunftCom\main\php\cfg\component\component_link;
use Zukunft\ZukunftCom\main\php\cfg\component\component_list;
use Zukunft\ZukunftCom\main\php\cfg\component\component_type;
use Zukunft\ZukunftCom\main\php\cfg\component\component_link_type;
use Zukunft\ZukunftCom\main\php\cfg\component\position_type;
use Zukunft\ZukunftCom\main\php\cfg\component\view_style;
use Zukunft\ZukunftCom\main\php\cfg\element\element;
use Zukunft\ZukunftCom\main\php\cfg\element\element_type;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_db;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_link;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_link_type;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_map;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_type;
use Zukunft\ZukunftCom\main\php\cfg\group\group;
use Zukunft\ZukunftCom\main\php\cfg\log\change;
use Zukunft\ZukunftCom\main\php\cfg\log\change_link;
use Zukunft\ZukunftCom\main\php\cfg\log\change_log;
use Zukunft\ZukunftCom\main\php\cfg\log\change_value;
use Zukunft\ZukunftCom\main\php\cfg\log\change_values_big;
use Zukunft\ZukunftCom\main\php\cfg\log\change_values_norm;
use Zukunft\ZukunftCom\main\php\cfg\log\change_values_prime;
use Zukunft\ZukunftCom\main\php\cfg\log\change_values_geo_big;
use Zukunft\ZukunftCom\main\php\cfg\log\change_values_geo_norm;
use Zukunft\ZukunftCom\main\php\cfg\log\change_values_geo_prime;
use Zukunft\ZukunftCom\main\php\cfg\log\change_values_text_big;
use Zukunft\ZukunftCom\main\php\cfg\log\change_values_text_norm;
use Zukunft\ZukunftCom\main\php\cfg\log\change_values_text_prime;
use Zukunft\ZukunftCom\main\php\cfg\log\change_values_time_big;
use Zukunft\ZukunftCom\main\php\cfg\log\change_values_time_norm;
use Zukunft\ZukunftCom\main\php\cfg\log\change_values_time_prime;
use Zukunft\ZukunftCom\main\php\cfg\log\changes_big;
use Zukunft\ZukunftCom\main\php\cfg\log\changes_norm;
use Zukunft\ZukunftCom\main\php\cfg\ref\ref;
use Zukunft\ZukunftCom\main\php\cfg\ref\ref_type;
use Zukunft\ZukunftCom\main\php\cfg\ref\source;
use Zukunft\ZukunftCom\main\php\cfg\ref\source_list;
use Zukunft\ZukunftCom\main\php\cfg\ref\source_type;
use Zukunft\ZukunftCom\main\php\cfg\result\result;
use Zukunft\ZukunftCom\main\php\cfg\language\language;
use Zukunft\ZukunftCom\main\php\cfg\language\language_form;
use Zukunft\ZukunftCom\main\php\cfg\log\change_action;
use Zukunft\ZukunftCom\main\php\cfg\log\change_table;
use Zukunft\ZukunftCom\main\php\cfg\log\change_field;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_types;
use Zukunft\ZukunftCom\main\php\cfg\result\result_db;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_multi;
use Zukunft\ZukunftCom\main\php\cfg\system\job;
use Zukunft\ZukunftCom\main\php\cfg\system\job_status;
use Zukunft\ZukunftCom\main\php\cfg\system\job_type;
use Zukunft\ZukunftCom\main\php\cfg\system\pod;
use Zukunft\ZukunftCom\main\php\cfg\system\session;
use Zukunft\ZukunftCom\main\php\cfg\system\sys_log;
use Zukunft\ZukunftCom\main\php\cfg\system\sys_log_function;
use Zukunft\ZukunftCom\main\php\cfg\system\sys_log_level;
use Zukunft\ZukunftCom\main\php\cfg\system\sys_log_status;
use Zukunft\ZukunftCom\main\php\cfg\system\system_time;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_db;
use Zukunft\ZukunftCom\main\php\cfg\user\user_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user_profile;
use Zukunft\ZukunftCom\main\php\cfg\user\user_status;
use Zukunft\ZukunftCom\main\php\cfg\user\user_type;
use Zukunft\ZukunftCom\main\php\cfg\user\user_official_type;
use Zukunft\ZukunftCom\main\php\cfg\value\value;
use Zukunft\ZukunftCom\main\php\cfg\value\value_geo;
use Zukunft\ZukunftCom\main\php\cfg\value\value_text;
use Zukunft\ZukunftCom\main\php\cfg\value\value_time;
use Zukunft\ZukunftCom\main\php\cfg\value\value_time_series;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb_list;
use Zukunft\ZukunftCom\main\php\cfg\view\term_view;
use Zukunft\ZukunftCom\main\php\cfg\view\view;
use Zukunft\ZukunftCom\main\php\cfg\view\view_link_type;
use Zukunft\ZukunftCom\main\php\cfg\view\view_list;
use Zukunft\ZukunftCom\main\php\cfg\view\view_relation;
use Zukunft\ZukunftCom\main\php\cfg\view\view_type;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\cfg\word\triple_list;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\cfg\word\word_list;
use Zukunft\ZukunftCom\main\php\shared\enum\sys_log_statuum;
use Zukunft\ZukunftCom\main\php\shared\enum\user_statuum;
use Zukunft\ZukunftCom\main\php\shared\types\protection_types;
use Zukunft\ZukunftCom\main\php\shared\types\share_types;
use Zukunft\ZukunftCom\main\php\shared\types\system_time_type;
use Zukunft\ZukunftCom\main\php\shared\types\view_relation_types;

class def
{

    /*
     * main code const
     */

    const string POD_NAME = "zukunft.com"; // the default pod name if not defined
    const string PRG_VERSION = "0.0.3"; // to detect the correct update script and to mark the data export
    const string NEXT_VERSION = "0.0.4"; // to prevent importing incompatible data
    const string FIRST_VERSION = "0.0.2"; // the last program version which has not a basic upgrade process

    // parameters for internal testing and debugging
    const int LIST_MIN_NAMES = 4; // number of object names that should at least be shown
    const int LIST_MIN_NUM = 20; // number of object ids that should at least be shown
    const int DEBUG_SHOW_USER = 10; // starting from this debug level the user should be shown in the debug text
    const int DEBUG_SQL_LENGTH = 200; // the max number of chars of an SQL statement shown in the debug text
    const int DEBUG_SQL_LIST_TEXT = 500; // the max number of chars of a list of SQL statements shown in the debug text


    /*
     * calc
     */

    // TODO Prio 2 allow overwrite by the config value
    const int MAX_LOOP = 10000; // maximal number of loops to avoid hanging while loops; used for example for the number of formula elements
    const int MAX_RECURSIVE = 10; // max number of recursive call to avoid endless looping in case of a program error


    /*
     * fallback
     */

    // TODO Prio 1 collect all fallback values here
    // configuration values used as fallback if the value is missing in the system configuration
    const int FALLBACK_IMPORT_PER_SEC = 100; // expected number of objects that could be imported per second
    const int FALLBACK_IMPORT_BYTE_PER_SEC = 10000; // expected number of bytes per second that could be processed in import
    const int FALLBACK_PERCENT_STEP = 1; // the percent step size for the progress bar and of process parts
    const int FALLBACK_RETRY = 10; // the default number of retries for a failed process
    const int FALLBACK_RECURSIVE_MAX = 99; // the maximal number of recursive calls of a function
    const int FALLBACK_DB_PAGE_ROWS = 20; // the number of database rows that should be loaded at once
    const float FALLBACK_RESPONSE_TIME = 1.0; // the response time to update the frontend in seconds


    /*
     * user sandbox
     */

    /*
    if UI_CAN_CHANGE_... setting is true renaming an object may switch to an object with the new name
    if false the user gets an error message that the object with the new name exists already

    e.g. if this setting is true
         user 1 creates     "Nestle" with id 1
         and user 2 creates "Nestlé" with id 2
         now the user 1 changes "Nestle" to "Nestlé"
         1. "Nestle" will be deleted, because it is not used anymore
         2. "Nestlé" with id 2 will not be excluded anymore

    */
    const bool UI_CAN_CHANGE_VALUE = TRUE;
    const bool UI_CAN_CHANGE_TIME_SERIES_VALUE = TRUE;
    const bool UI_CAN_CHANGE_VIEW_NAME = TRUE;
    const bool UI_CAN_CHANGE_VIEW_COMPONENT_NAME = TRUE; // dito for view components
    const bool UI_CAN_CHANGE_VIEW_COMPONENT_LINK = TRUE; // dito for view component links
    const bool UI_CAN_CHANGE_WORD_NAME = TRUE; // dito for words
    const bool UI_CAN_CHANGE_triple_NAME = TRUE; // dito for phrases
    const bool UI_CAN_CHANGE_FORMULA_NAME = TRUE; // dito for formulas
    const bool UI_CAN_CHANGE_VERB_NAME = TRUE; // dito for verbs
    const bool UI_CAN_CHANGE_SOURCE_NAME = TRUE; // dito for sources


    /*
     * classes
     */

    // the main classes that have a
    // corresponding frontend object,
    // a database table and
    // can be im- and exported
    // TODO add group, term_view_links, formula_link, component_links, styles, view_types,
    //          time_series, geo and text values, ip ranges, language, pod,
    //          add types (phrase_type, formula_type, formula_link_types, source_types,
    //                     ref_types, position_types, view_types, view_link_types,
    //                     component_types, component_link_types, pod_types, pod_statu
    const array MAIN_CLASSES = [
        user::class,
        word::class,
        verb::class,
        triple::class,
        source::class,
        ref::class,
        group::class,
        value::class,
        formula::class,
        formula_link::class,
        result::class,
        view::class,
        view_relation::class,
        term_view::class,
        component::class,
        component_link::class,
    ];

    // classes that should not be delete (the only exception is that system users can delete test rows)
    const array NO_DELETE_CLASSES = [
        user::class,
    ];

    // classes where database rows should never be updated (the only exception is that system users can delete test rows)
    const array NO_UPDATE_CLASSES = [
        change_log::class,
    ];

    // classes that should not be delete (the only exception is that system users can delete test rows)
    const array ONLY_ADMIN_CAN_DELETE_CLASSES = [
        language::class,
    ];

    // classes that should not be delete (the only exception is that system users can delete test rows)
    const array ONLY_ADMIN_CAN_UPDATE_CLASSES = [
        language::class,
    ];

    // classes that are directly linked to the main classes and that should be included in the same code function docs part
    const array MAIN_SUB_CLASSES = [
        formula_map::class,
    ];

    // list of classes that have a unique name
    const array NAME_CLASSES = [
        word::class,
        verb::class,
        triple::class,
        source::class,
        formula::class,
        view::class,
        component::class,
    ];

    // list of classes that have a unique name
    const array ONLY_ADMIN_CAN_RENAME_CLASSES = [
        verb::class,
    ];

    // list of value classes
    const array VALUE_CLASSES = [
        value::class,
        value_time::class,
        value_text::class,
        value_geo::class,
        value_time_series::class,
    ];

    // list of classes where the tables have no auto increase id instead the id is based on a phrase list based database id
    const array DB_TYPES_NO_SEQ = [
        value::class,
        value_time::class,
        value_text::class,
        value_geo::class,
        value_time_series::class,
        result::class,
        group::class,
    ];

    // list of classes that can be the object of a phrase
    // TODO Prio 1 use this for all phrase checks
    const array PHRASE_CLASSES = [
        word::class,
        triple::class,
    ];

    // list of classes that can be the object of a phrase
    const array TERM_CLASSES = [
        word::class,
        verb::class,
        triple::class,
        formula::class,
    ];

    // list of classes where the link of two objects is the main unique key beside the database id
    const array LINK_CLASSES = [
        element::class,
        ref::class,
        component_link::class,
    ];

    // list of classes where the link of two objects and the predicate/type is the main unique key beside the database id
    const array LINK_TYPE_CLASSES = [
        triple::class,
        ref::class,
        component_link::class,
    ];

    // classes that have a frontend and backend object but are not user-specific
    const array SYSTEM_UI_CLASSES = [
        language::class,
        pod::class,
        job::class,
        change_log::class,
        sys_log::class,
    ];

    // classes that have a code id
    // to select single database rows from the code with a unique key
    // or a type which cannot be changed by IP users
    // so the requesting user needs to be included in the mapping request
    const array CODE_ID_CLASSES = [
        word::class,
        word_list::class,
        verb::class,
        verb_list::class,
        triple::class,
        triple_list::class,
        source::class,
        source_list::class,
        user::class,
        user_list::class,
        view::class,
        view_list::class,
        component::class,
        component_list::class,
    ];

    // classes that have a user interface message code id
    const array UI_MSG_CODE_ID_CLASSES = [
        component::class,
    ];

    // classes that does not need a foreign db key
    const array NO_FOREIGN_DB_KEY_CLASSES = [
        verb::class,
    ];

    // type classes that have a csv file for the initial load
    const array BASE_CODE_LINK_FILES = [
        sys_log_function::class,
        sys_log_level::class,
        sys_log_statuum::class,
        job_status::class,
        job_type::class,
        change_action::class,
        change_table::class,
        change_field::class,
        element_type::class,
        formula_link_type::class,
        formula_type::class,
        language::class,
        language_form::class,
        protection_types::class,
        ref_type::class,
        share_types::class,
        source_type::class,
        system_time_type::class,
        user_official_type::class,
        user_profile::class,
        user_type::class,
        user_statuum::class,
        position_type::class,
        component_link_type::class,
        component_type::class,
        view_link_type::class,
        view_type::class,
        view_style::class,
        view_relation_types::class,
        phrase_types::class
    ];

    // log type classes that have a csv file for the initial load
    const array LOG_CODE_LINK_FILES = [
        change_action::class,
        change_table::class,
        change_field::class,
    ];

    // list of classes that are used in the api e.g. to receive the user changes
    const array API_CLASSES = [
        word::class,
        verb::class,
        triple::class,
        source::class,
        ref::class,
        value::class,
        formula::class,
        result::class,
        view::class,
        component::class,
        view_relation::class
    ];

    // list of classes that have a csv with the code id for the initial user profile and type setup
    const array CLASS_WITH_USER_CODE_LINK_CSV = [
        user_profile::class,
        user_type::class,
        user_status::class,
    ];

    // list of classes that use the user sandbox
    const array SANDBOX_CLASSES = [
        word::class,
        triple::class,
        source::class,
        ref::class,
        value::class,
        formula::class,
        result::class,
        view::class,
        component::class,
        view_relation::class
    ];

    // list of classes that have n:m object links e.g. view has components linked
    const array CLASSES_WITH_LINKS = [
        view::class,
    ];

    // list of log classes that does not need to fill up usr_msg object when creating the sql statements
    const array CLASSES_CHANGE_LOG = [
        change::class,
        change_log::class,
        changes_norm::class,
        changes_big::class,
        change_values_prime::class,
        change_values_norm::class,
        change_values_big::class,
        change_values_time_prime::class,
        change_values_time_norm::class,
        change_values_time_big::class,
        change_values_text_norm::class,
        change_values_text_prime::class,
        change_values_text_big::class,
        change_values_geo_prime::class,
        change_values_geo_norm::class,
        change_values_geo_big::class,
        change_link::class,
    ];

    // TODO Prion 1 review and combine with CLASSES_NO_CHANGE_LOG
    // list of classes that use a database table but where the changes never needs to be added to the change log
    const array CLASSES_NO_LOG = [
        job::class,
    ];

    // list of classes that use a database table but where the changes do not need to be logged
    const array CLASSES_NO_CHANGE_LOG = [
        sys_log_function::class,
        sys_log_status::class,
        sys_log_level::class,
        system_time_type::class,
        system_time::class,
        change_action::class,
        change_table::class,
        change_field::class,
        change_link::class,
        change_value::class,
        'change*',
        session::class,
        job::class,
        element::class,
        'phrase*',
        'user_phrase*',
        'prime_phrase*',
        'user_prime_phrase*',
        'term*',
        'user_term*',
        'prime_term*',
        'user_prime_term*',
        'result*',
        'user_result*',
    ];

    // similar to self::CLASSES_NO_CHANGE_LOG but without wildcards and only for self::MAIN_CLASSES
    const array MAIN_CLASSES_NO_CHANGE_LOG = [
        result::class,
    ];

    // TODO Prio 2 base it on the class names
    // list of all ab tables in order of dependencies
    const array DB_TABLE_LIST = [
        'config',
        'sys_log_functions',
        'sys_log_levels',
        'sys_log_statuum',
        'sys_log',
        'system_times',
        'system_time_types',
        'job_times',
        'jobs',
        'job_statuum',
        'job_types',
        'user_official_types',
        'ip_ranges',
        'sessions',
        'changes',
        'changes_norm',
        'changes_big',
        'change_values_norm',
        'change_values_prime',
        'change_values_big',
        'change_values_time_norm',
        'change_values_time_prime',
        'change_values_time_big',
        'change_values_text_prime',
        'change_values_text_norm',
        'change_values_text_big',
        'change_values_geo_norm',
        'change_values_geo_prime',
        'change_values_geo_big',
        'change_fields',
        'change_links',
        'change_actions',
        'change_tables',
        'protection_types',
        'share_types',
        'language_forms',
        'user_words',
        'words',
        'user_triples',
        'phrase_tables',
        'pods',
        'pod_types',
        'pod_status',
        'triples',
        'phrase_types',
        'verbs',
        'phrase_table_status',
        'groups',
        'user_groups',
        'groups_prime',
        'user_groups_prime',
        'groups_big',
        'user_groups_big',
        'user_sources',
        'user_refs',
        'refs',
        'ref_types',
        'values_standard_prime',
        'values_standard',
        'values',
        'user_values',
        'values_prime',
        'user_values_prime',
        'values_big',
        'user_values_big',
        'values_text_standard_prime',
        'values_text_standard',
        'values_text',
        'user_values_text',
        'values_text_prime',
        'user_values_text_prime',
        'values_text_big',
        'user_values_text_big',
        'values_time_standard_prime',
        'values_time_standard',
        'values_time',
        'user_values_time',
        'values_time_prime',
        'user_values_time_prime',
        'values_time_big',
        'user_values_time_big',
        'values_geo_standard_prime',
        'values_geo_standard',
        'values_geo',
        'user_values_geo',
        'values_geo_prime',
        'user_values_geo_prime',
        'values_geo_big',
        'user_values_geo_big',
        'sources',
        'source_types',
        'user_values_time_series',
        'value_time_series_prime',
        'user_value_time_series_prime',
        'value_ts_data',
        'values_time_series',
        'elements',
        'element_types',
        'user_formulas',
        'user_formula_links',
        'formula_link_types',
        'formula_links',
        'results_standard_prime',
        'results_standard_main',
        'results_standard',
        'results',
        'user_results',
        'results_prime',
        'user_results_prime',
        'results_main',
        'user_results_main',
        'results_big',
        'user_results_big',
        'results_text_standard_prime',
        'results_text_standard_main',
        'results_text_standard',
        'results_text',
        'user_results_text',
        'results_text_prime',
        'user_results_text_prime',
        'results_text_main',
        'user_results_text_main',
        'results_text_big',
        'user_results_text_big',
        'results_time_standard_prime',
        'results_time_standard_main',
        'results_time_standard',
        'results_time',
        'user_results_time',
        'results_time_prime',
        'user_results_time_prime',
        'results_time_main',
        'user_results_time_main',
        'results_time_big',
        'user_results_time_big',
        'results_geo_standard_prime',
        'results_geo_standard_main',
        'results_geo_standard',
        'results_geo',
        'user_results_geo',
        'results_geo_prime',
        'user_results_geo_prime',
        'results_geo_main',
        'user_results_geo_main',
        'results_geo_big',
        'user_results_geo_big',
        'user_views',
        'languages',
        'component_link_types',
        'user_components',
        'user_component_links',
        'component_links',
        'user_view_relations',
        'view_relations',
        'position_types',
        'components',
        'formulas',
        'formula_types',
        'views',
        'users',
        'user_types',
        'user_profiles',
        'user_statuum',
        'view_types',
        'view_styles',
        'component_types',
        'view_link_types',
        'view_relation_types',
        'term_views',
        'user_term_views',
        'value_formula_links',
        'value_time_series',
        'user_value_time_series',
        'values_time_series_prime',
        'user_values_time_series_prime',
        'values_time_series_big',
        'user_values_time_series_big',
        'results_time_series',
        'user_results_time_series',
        'results_time_series_prime',
        'user_results_time_series_prime',
        'results_time_series_big',
        'user_results_time_series_big'
    ];

    // list of all sequences used in the database
    // TODO base the list on the class list const and a sequence name function
    const array DB_SEQ_LIST = [
        'sys_log_status_sys_log_status_id_seq',
        'sys_log_sys_log_id_seq',
        'elements_element_id_seq',
        'element_types_element_type_id_seq',
        'formula_links_formula_link_id_seq',
        'formulas_formula_id_seq',
        'formula_types_formula_type_id_seq',
        'component_links_component_link_id_seq',
        'component_link_types_component_link_type_id_seq',
        'components_component_id_seq',
        'component_types_component_type_id_seq',
        'views_view_id_seq',
        'view_types_view_type_id_seq',
        'verbs_verb_id_seq',
        'triples_triple_id_seq',
        'words_word_id_seq',
        'phrase_types_phrase_type_id_seq',
        'sources_source_id_seq',
        'source_types_source_type_id_seq',
        'refs_ref_id_seq',
        'ref_types_ref_type_id_seq',
        'change_links_change_link_id_seq',
        'changes_change_id_seq',
        'change_actions_change_action_id_seq',
        'change_fields_change_field_id_seq',
        'change_tables_change_table_id_seq',
        'config_config_id_seq',
        'job_statuum_job_status_id_seq',
        'job_types_job_type_id_seq',
        'jobs_job_id_seq',
        'sys_log_status_sys_log_status_id_seq',
        'sys_log_functions_sys_log_function_id_seq',
        'share_types_share_type_id_seq',
        'protection_types_protection_type_id_seq',
        'users_user_id_seq',
        'user_profiles_user_profile_id_seq'
    ];

    // id field names that can be either int or text e.g. the group_id
    const array MIXED_ID_FIELDS = [
        result_db::FLD_SOURCE_GRP,
    ];


    // list of database fields that are also in test volatile
    // and that should be ignored in unit tests
    const array VOLATILE_DB_FIELDS = [
        [value::class, sandbox_multi::FLD_LAST_UPDATE],
        [formula::class, formula_db::FLD_LAST_UPDATE],
        [user::class, user_db::FLD_CREATED],
        [user::class, user_db::FLD_LAST_LOGIN],
        [user::class, user_db::FLD_LAST_LOGOUT],
    ];


}
