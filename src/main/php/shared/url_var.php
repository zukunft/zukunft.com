<?php

/*

    shared/url_var.php - all names used for the url and the form field names
    ------------------

    for the names used in the api see shared/api


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

namespace Zukunft\ZukunftCom\main\php\shared;

use Zukunft\ZukunftCom\main\php\shared\const\views;

class url_var
{

    /*
     * base url
     */

    // TODO Prio 1 use be default the *_LONG url_var
    // TODO Prio 2 based on the config setting switch the the short or pod exchangeable url
    // TODO always use these const instead e.g. of the controller const
    // TODO allow to use the object names instead of the id for human readable urls
    // the parameter names used in the url or in the result json
    const string PAR = '?';
    const string ADD = '&';
    const string EQ = '=';
    const string API_PATH = 'api/';
    const string ADD_ID = self::ADD . self::ID . self::EQ;

    // exception for the reference api path
    const string REF_API = 'reference';


    /*
     * url type
     */

    // the url entry to select the url format
    // if the next two are not set at least MASK is expected to be set and if the short technical url is used
    const string MASK = 'm'; // the internal database id of the view used to format the object
    const string MASK_HUMAN = 'mask_id'; // if *_LONG is given the human-readable url format is used
    const string MASK_POD = 'mask'; // if *_EXCHANGE is given the url that is interchangeable between pods is used thet does not contain pod specific database ids


    /*
     * short and standard url
     */

    // the var names for the short technical url (in alphabetic order to detect duplicates)
    const string EXCLUDED = '0';
    const string CONFIG_PART = '1';
    const string MSG = '2';
    const string BACK = '9'; // list of url targets for the back action
    const string ACTION = 'a'; // the crud action
    const string VERB = 'b'; // the verB id
    const string VERBS = 'bl';  // to select the verbs that should be displayed
    const string NAME_IN_FORMULA = 'bn'; // name of the verb used in formulas
    const string COMPONENT = 'c';
    const string PHRASE_ROW = 'c1'; // id of the phrase used for e.g. the table rows
    const string PHRASE_COL = 'c2';
    const string PHRASE_COL_SUB = 'c3';
    const string COMPONENT_LINK = 'cl'; // to link a component to a view
    const string POSITION_TYPE = 'cp';
    const string STYLE = 'cs';
    const string COMPONENT_TYPE = 'ct';
    const string COMPONENT_LINK_TYPE = 'cy';
    // data fields used for system forms
    const string VIEW = 'd'; // the Display / view id form field value of the object not the view that should be used to show the form to the user
    const string VIEW_LINK = 'dc'; // display connector to link a view to another view
    const string VIEW_PARENT = 'df'; // the "from" display view that should be modified
    const string VIEW_TERM_LINK = 'dl'; // to link a view to a term
    const string VIEW_CHILD = 'dm'; // the display view that modifies the parent view
    const string VIEW_TERM_LINK_PRIO = 'dp'; // to define the order of the view components
    const string VIEW_TYPE = 'dt';
    const string VIEW_RELATION_TYPE = 'dr'; // the type of the view to view link
    const string UNLINK_VIEW = 'dx'; // exclude the view link
    const string VIEW_LINK_TYPE = 'dy'; // the type of the term to view link
    const string TERM = 'e';
    const string TERM_POS = 'ep'; // used for a list of terms where the list position is added to the name
    const string EXTERNAL_KEY = 'ek'; // the external key of a form field
    const string FORMULA = 'f';
    const string NEED_ALL = 'fa';
    const string USER_EXPRESSION = 'fe';
    const string FORMULA_LINK = 'fl'; // to link a formula to a phrase
    const string FORMULA_LINK_PRIO = 'fp';
    const string FORMULAS = 'fs';  // to select the formulas that should be displayed
    const string FORMULA_LINK_TYPE = 'ft';
    const string FORMULA_TYPE = 'fy';
    const string GROUP = 'g';
    const string GROUP_NAME = 'gn'; // TODO maybe it is possible to use NAME
    const string LOG = 'h'; // h for history of the object
    const string LOG_STATUS = 'ha'; // the stAtus of a system log entry
    const string LOG_CLASS = 'hc'; // the object / class name used to filter the change log
    const string LOG_LINK = 'hl'; // history of a link object
    const string LOG_FIELD = 'hf'; // the field of the change log entry used to filter the log
    const string LOG_LEVEL = 'hg'; // the grade / level of the log entry used to filter the system log
    const string LOG_FUNCTION = 'hp'; // the process / function that has cause the log even
    const string SYS_TRACE = 'hr'; // the record / trace of a system log entry
    const string SYS_LOG = 'hs'; // history of a system event
    const string LOG_TIME = 'ht'; // the time of the creation of the log event
    const string FIGURE = 'i';
    const string ID = 'id'; // the internal database id of the main view object
    const string ID_LST = 'il'; // a comma separated list of internal database ids
    const string IP = 'ip'; // for ip ranges (for admin only)
    const string WITH_PHRASES = 'iw'; // include the phrases in the values or result messages
    const string JOB = 'j'; // for system batch jobs
    const string JOB_PARAMETER = 'ja'; // pArameter passed to a job e.g. the id of the phrase set
    const string JOB_START_TIME = 'jb'; // Begin time of the job execution
    const string JOB_CHANGE_FIELD = 'jc'; // the Changed field id for undo jobs
    const string JOB_END_TIME = 'je'; // End time of the job execution
    const string JOB_PRIORITY = 'jp'; // the Priority of the batch job
    const string JOB_REQUEST_TIME = 'jq'; // the time when the job was reQuested
    const string JOB_ROW_ID = 'jr'; // the Row id of the related object touched by the job
    const string JOB_STATUS = 'js'; // the Status of the batch job e.g. new, running, done
    const string JOB_TYPE = 'jt'; // the job type
    const string NAME = 'k'; // the name of a word, verb, triple, ... of a form field (Kennung)
    const string CODE_ID = 'ki'; // the code id
    const string PATTERN = 'kp'; // the wildcard pattern to select a list of objects by name
    const string REF = 'l'; // l for data link to external
    const string REF_TYPE = 'lt';
    const string PLURAL = 'lp'; // the name of the word, triple or verb if there are many
    const string REVERSE = 'lr'; // the name of the verb if used the other way round
    const string REVERSE_PLURAL = 'lx'; // the name of the verb if used the other way round and there are many phrases
    // const string MASK = 'm'; // just the placeholder to remember that the char m is used for the url type selection
    const string NUMERIC_VALUE = 'n';
    const string DESCRIPTION = 'o'; // the description of a word, verb, triple, ... of a form field
    const string PHRASE = 'p'; // the id or name of one phrase
    const string PHRASE_CLASS = 'pc'; // word or triple class indicator of the phrase
    const string DIRECTION = 'pd'; // 'up' to get the parents and 'down' for the children
    const string PHRASE_FROM = 'pf';
    const string PHRASE_LIST = 'pl'; // a list of phrase ids
    const string POSITION = 'po'; // a order number or a position
    const string PHRASE_TO = 'pt';
    const string LEVELS = 'px'; // the number of search levels'
    const string PHRASE_TYPE = 'py';
    const string PHRASE_POS = 'p_'; // used for a list of phrases where the list position is added to the name
    const string LINK_PHRASE = 'p+';
    const string UNLINK_PHRASE = 'p-';
    const string LANGUAGE = 'q'; // the id of a language (q like question)
    const string LANGUAGE_FORM = 'qf';
    const string LANGUAGE_SYMBOL = 'qs';
    const string RESULT = 'r';
    const string SHARE = 's'; // the share id of the sandbox object
    const string SOURCE = 'so';
    const string PROTECTION = 'sp'; // the protection id of the sandbox object
    const string SOURCE_TYPE = 'st';
    const string TRIPLE = 't';
    const string WEIGHT = 'tw';
    const string TRIPLES = 'tl'; // to select the triples that should be displayed
    const string USER = 'u';
    const string USER_FIRST_NAME = 'uf';
    const string IMPACT = 'ui'; // the impact value a form field
    const string USER_LAST_NAME = 'ul';
    const string EMAIL = 'um'; // the user email form field
    const string USERNAME = 'un';
    const string USER_PROFILE = 'up';
    const string URL = 'ur';
    const string USER_TYPE = 'ut';
    const string USAGE = 'uu'; // the usage value a form field
    const string USER_PASSWORD = 'uw';
    const string USER_PASSWORD_RETYPE = 'uwr';
    const string VALUE = 'v';
    const string VALUE_TIME_SERIES = 'vs';
    const string WORD = 'w';
    const string WORD_POS = 'wp'; // with a number the word id of the x word e.g. of the form field of a group
    const string WORDS = 'wl'; // array of ids to select or add the words
    const string CONTEXT = 'x'; // list of terms to describe the context used for the view
    const string TYPE = 'y'; // the type id or predicate id of a form field
    const string STEP = 'z'; // the user process (proZess) step (e.g. show, to_confirm, confirmed)


    /*
     * short and standard url actions
     */

    const string UP = 'up'; // move this item in the list one step up
    const string DOWN = 'down'; // move this item in the list one step down


    /*
     * short and standard url values
     */

    // action values
    const string CRUD_CREATE = 'a'; // the crud add / create action
    const string CRUD_READ = 'r'; // the crud read action
    const string CRUD_UPDATE = 'u'; // the crud update action
    const string CRUD_DELETE = 'd'; // the crud delete action
    const string SHOW_FULL = 'f'; // to show object with all fields
    const string SHOW_POPUP = 'p'; // to show object with only a few fields as a popup window
    const string SHOW_CREATE = 's'; // to show object with only the name or key as table cell

    // to select the configuration part that should be updated in the frontend e.g. all, frontend or user
    const string TRUE = '1';

    // enum for self::STEP and the next step of the action
    const string STEP_BASE = '0'; // no action process has been started
    const string STEP_CONFIRM = '1'; // the change must be confirmed by the user
    const string STEP_CONFIRMED = '2'; // the change has been confirmed by the user
    const string STEP_DONE = '3'; // the change has been processed successful
    const string STEP_CANCEL = '-1'; // the user has requested to stop the process
    const string STEP_CANCELED = '-2'; // the process has been canceled
    const string STEP_FAILED = '-3'; // the process cannot be processed


    /*
     * human-readable url
     */

    // init
    const string CONFIG_PART_HUMAN = 'part';
    const string DEBUG = 'debug'; // to force the output of debug messages
    const int DEBUG_EXE_TIME_REPORT = -1; // show the execution time report in the frontend


    // the var names for the easy human-readable url (in content related order)
    const string ACTION_HUMAN = 'action'; // the CRUD action for the long url
    const string STEP_HUMAN = 'step';  // the action status for the long url
    const string BACK_HUMAN = 'back';
    const string MSG_HUMAN = 'message';

    // enum for self::ACTION and the database change process that should be stared
    const string CRUD_CREATE_HUMAN = 'add'; // the CRUD action code to add an object
    const string CRUD_UPDATE_HUMAN = 'edit'; // the CRUD action to change an object
    const string CRUD_REMOVE_HUMAN = 'del'; // the CRUD action to delete an object
    const string CRUD_READ_HUMAN = 'show'; // the CRUD action to show object with the most relevant fields
    const string CRUD_FULL_HUMAN = 'full'; // to show object with all fields
    const string CRUD_POPUP_HUMAN = 'popup'; // to show object with only a few fields as a popup window
    const string CRUD_CELL_HUMAN = 'cell'; // to show object with only the name or key as table cell

    // enum for self::STEP and the next step of the action
    const string STEP_BASE_HUMAN = 'no'; // no action process has been started
    const string STEP_CONFIRM_HUMAN = 'confirm'; // the change must be confirmed by the user
    const string STEP_CONFIRMED_HUMAN = 'confirmed'; // the change has been confirmed by the user
    const string STEP_DONE_HUMAN = 'done'; // the change has been processed successful
    const string STEP_CANCEL_HUMAN = 'cancel'; // the user has requested to stop the process
    const string STEP_CANCELED_HUMAN = 'canceled'; // the process has been canceled
    const string STEP_FAILED_HUMAN = 'failed'; // the process cannot be processed

    // user
    const string USER_HUMAN = 'user';
    const string USERNAME_HUMAN = 'username';
    const string EMAIL_HUMAN = 'email';
    const string USER_PASSWORD_HUMAN = 'password';
    const string USER_PASSWORD_RETYPE_HUMAN = 're_password';
    const string USER_PROFILE_HUMAN = 'user_profile';
    const string USER_FIRST_NAME_HUMAN = 'user_first_name';
    const string USER_LAST_NAME_HUMAN = 'user_last_name';
    const string USER_TYPE_HUMAN = 'user_type';

    // id & name
    //const string ID = 'id'; // repeated as comment from standard list above just to remember that it is the same as standard
    const string ID_LST_HUMAN = 'ids'; // a comma separated list of internal database ids
    const string NAME_HUMAN = 'name'; // the unique name of a term, view, component, user, source, language or type
    const string PATTERN_HUMAN = 'pattern'; // part of a name to select a named object such as word, triple, ...
    const string DESCRIPTION_HUMAN = 'description';
    const string CODE_ID_HUMAN = 'code_id';
    const string EXCLUDED_HUMAN = 'excluded';

    // language
    const string LANGUAGE_HUMAN = 'language';
    const string LANGUAGE_SYMBOL_HUMAN = 'languageSymbol';
    const string LANGUAGE_FORM_HUMAN = 'languageForm';
    const string PLURAL_HUMAN = 'plural';

    // type
    const string TYPE_HUMAN = 'type';

    // word
    const string WORD_HUMAN = 'word_id';
    const string WORD_POS_HUMAN = 'word_id_pos_';
    const string WORDS_HUMAN = 'words'; // array of id to select the words that should be displayed

    // verb
    const string VERB_HUMAN = 'verb_id';
    const string REVERSE_HUMAN = 'reverse';
    const string REVERSE_PLURAL_HUMAN = 'plural_reverse';
    const string NAME_IN_FORMULA_HUMAN = 'name_in_formula';
    const string VERBS_HUMAN = 'verbs';  // to select the verbs that should be displayed

    // triple
    const string TRIPLE_HUMAN = 'triple_id';
    const string PHRASE_FROM_HUMAN = 'from_id';
    const string PHRASE_TO_HUMAN = 'to_id';
    const string WEIGHT_HUMAN = 'weight';
    const string TRIPLES_HUMAN = 'triples'; // to select the triples that should be displayed

    // phrase
    const string PHRASE_HUMAN = 'phrase_id';
    const string PHRASE_CLASS_HUMAN = 'phrase_class'; // word or triple class indicator of the phrase
    const string PHRASE_TYPE_HUMAN = 'phrase_type';
    const string PHRASE_LIST_HUMAN = 'phrase_ids';
    const string PHRASE_POS_HUMAN = 'phrase_id_pos_'; // used for a list of phrases where the list position is added to the name
    const string LINK_PHRASE_HUMAN = 'link_phrase';
    const string UNLINK_PHRASE_HUMAN = 'unlink_phrase';

    // graph
    const string DIRECTION_HUMAN = 'dir'; // 'up' to get the parents and 'down' for the children
    const string LEVELS_HUMAN = 'levels'; // the number of search levels'

    // source
    const string SOURCE_HUMAN = 'source_id';
    const string SOURCE_TYPE_HUMAN = 'source_type';
    const string URL_HUMAN = 'url';

    // ref
    const string REF_HUMAN = 'ref_id';
    const string REF_TYPE_HUMAN = 'ref_type';
    const string EX_KEY_HUMAN = 'external_key';

    // group
    const string GROUP_HUMAN = 'group_ig';
    const string GROUP_NAME_HUMAN = 'group_name';

    // value
    const string VALUE_HUMAN = 'value_id';
    const string VALUE_TIME_SERIES_HUMAN = 'time_series_id';
    const string NUMERIC_VALUE_HUMAN = 'number';

    // formula
    const string FORMULA_HUMAN = 'formula_id';
    const string FORMULA_TYPE_HUMAN = 'formula_type';
    const string NEED_ALL_HUMAN = 'need_all_val';
    const string USER_EXPRESSION_HUMAN = 'formula_text';
    const string FORMULA_LINK_HUMAN = 'formula_link_id'; // to link a formula to a phrase
    const string FORMULA_LINK_PRIO_HUMAN = 'formula_link_prio';
    const string FORMULA_LINK_TYPE_HUMAN = 'formula_link_type';
    const string FORMULAS_HUMAN = 'formulas';  // to select the formulas that should be displayed

    // term
    const string TERM_HUMAN = 'term_id';
    // used for a list of terms where the list position is added to the name
    const string TERM_POS_HUMAN = 'term_id_pos_';

    // result
    const string RESULT_HUMAN = 'result_id'; // not group id to select the result via group id and not the group name it self

    // figure
    const string FIGURE_HUMAN = 'figure_id';
    const string WITH_PHRASES_HUMAN = 'incl_phrases';

    // view
    const string VIEW_HUMAN = 'view_id';
    const string VIEW_TYPE_HUMAN = 'view_type';
    const string VIEW_TERM_LINK_HUMAN = 'view_term_link_id'; // to link a view to a term
    const string VIEW_TERM_LINK_PRIO_HUMAN = 'view_term_link_prio'; // to define the order of the view components
    const string VIEW_LINK_HUMAN = 'link_view'; // id of the link that connects a view to another view
    const string VIEW_LINK_TYPE_HUMAN = 'view_link_type';
    const string UNLINK_VIEW_HUMAN = 'unlink_view'; //
    const string VIEW_PARENT_HUMAN = 'parent_view';
    const string VIEW_CHILD_HUMAN = 'child_view';
    const string POSITION_HUMAN = 'position';

    // component
    const string COMPONENT_HUMAN = 'component_id';
    const string COMPONENT_TYPE_HUMAN = 'component_type';
    const string COMPONENT_LINK_HUMAN = 'component_link_id'; // link a component to a view
    const string COMPONENT_LINK_TYPE_HUMAN = 'component_link_type';
    const string POSITION_TYPE_HUMAN = 'position_type';
    const string STYLE_HUMAN = 'style';
    const string PHRASE_ROW_HUMAN = 'phrase_row';
    const string PHRASE_COL_HUMAN = 'phrase_col';
    const string PHRASE_COL_SUB_HUMAN = 'phrase_col_sub';

    // im- and export
    const string CONTEXT_HUMAN = 'context'; // list of terms to describe the context used for the view

    // log
    const string LOG_HUMAN = 'log_id'; // the id of a change log entry
    const string LOG_STATUS_HUMAN = 'log_status'; // the stAtus of a system log entry
    const string LOG_CLASS_HUMAN = 'class'; // the short name of the object class name e.g. word instead of cfg/word
    const string LOG_FIELD_HUMAN = 'log_field'; // the name of the field to filter the changes which might be more than one database field
    const string LOG_LINK_HUMAN = 'log_link_id'; // the id of a log entry of a link change
    const string LOG_LEVEL_HUMAN = 'log_level'; // the grade / level of the log entry used to filter the system log
    const string LOG_FUNCTION_HUMAN = 'log_function'; // the process / function that has cause the log even
    const string SYS_TRACE_HUMAN = 'log_trace'; // the record / trace of a system log entry
    const string LOG_TIME_HUMAN = 'log_time'; // the time of the creation of the log event

    // system
    const string SYS_LOG_HUMAN = 'sys_log_id'; // the id of a system log entry e.g. of internal program errors
    const string IP_HUMAN = 'ip_addr_id'; // the id of an ip range to set rules
    const string JOB_HUMAN = 'job_id'; // the id of a concrete job with start, status and end
    const string JOB_TYPE_HUMAN = 'job_type'; // the id of the job type to link the functionality to the concrete job
    const string JOB_STATUS_HUMAN = 'job_status'; // the status of the batch job e.g. new, running, done
    const string JOB_PRIORITY_HUMAN = 'job_priority'; // the priority of the batch job
    const string JOB_PARAMETER_HUMAN = 'job_parameter'; // parameter passed to a job e.g. the id of the phrase set
    const string JOB_CHANGE_FIELD_HUMAN = 'job_change_field'; // the changed field id for undo jobs
    const string JOB_ROW_ID_HUMAN = 'job_row_id'; // the row id of the related object touched by the job
    const string JOB_REQUEST_TIME_HUMAN = 'job_request_time'; // the time when the job was requested
    const string JOB_START_TIME_HUMAN = 'job_start_time'; // begin time of the job execution
    const string JOB_END_TIME_HUMAN = 'job_end_time'; // end time of the job execution

    // access
    const string SHARE_HUMAN = 'share';
    const string PROTECTION_HUMAN = 'protection';

    // impact
    const string USAGE_HUMAN = 'usage';
    const string IMPACT_HUMAN = 'impact';


    /*
     * session
     */

    const string SESSION_LOGGED = 'logged';
    const string SESSION_TOKEN = 'token';
    const string SESSION_USER_ID = 'usr_id';


    /*
     * curl
     */

    const string POST_SUBMIT = 'submit';
    const string POST_KEY = 'key';


    /*
     * pod exchangeable url
     */

    // the var names for urls that work for more than one pod (in content related order)
    const string STEP_POD = 'step';  // the action status for the long url


    /*
     * map
     */

    // TODO Prio 1 complete all url vars mappings for $pod_url
    // TODO use the mapping table also to create the human url
    // mapping of a human-readable url to the standard url
    // first entry is the from array key,
    // second is the target array key
    // third is the default value
    // third is the default value
    // fourth is true if the url key a mandatory
    // views::START_ID is the database id of the view to display
    // views::START_CODE is the code id of the view to display
    const array HUMAN_TO_STD = [
        // init
        [self::CONFIG_PART_HUMAN, self::CONFIG_PART],

        // control
        [self::MASK_HUMAN, self::MASK, views::START_ID, true],
        [self::ACTION_HUMAN, self::ACTION],
        [self::STEP_HUMAN, self::STEP],
        [self::BACK_HUMAN, self::BACK],
        [self::MSG_HUMAN, self::MSG],

        // user
        [self::USER_HUMAN, self::USER],
        [self::USERNAME_HUMAN, self::USERNAME],
        [self::EMAIL_HUMAN, self::EMAIL],
        [self::USER_PASSWORD_HUMAN, self::USER_PASSWORD],
        [self::USER_PASSWORD_RETYPE_HUMAN, self::USER_PASSWORD_RETYPE],
        [self::USER_FIRST_NAME_HUMAN, self::USER_FIRST_NAME],
        [self::USER_LAST_NAME_HUMAN, self::USER_LAST_NAME],
        [self::USER_PROFILE_HUMAN, self::USER_PROFILE],
        [self::USER_TYPE_HUMAN, self::USER_TYPE],

        // id & name
        [self::ID, self::ID],
        [self::ID_LST_HUMAN, self::ID_LST],
        [self::NAME_HUMAN, self::NAME],
        [self::PATTERN_HUMAN, self::PATTERN],
        [self::DESCRIPTION_HUMAN, self::DESCRIPTION],
        [self::CODE_ID_HUMAN, self::CODE_ID],
        [self::EXCLUDED_HUMAN, self::EXCLUDED],

        // language
        [self::LANGUAGE_HUMAN, self::LANGUAGE],
        [self::LANGUAGE_FORM_HUMAN, self::LANGUAGE_FORM],
        [self::LANGUAGE_SYMBOL_HUMAN, self::LANGUAGE_SYMBOL],
        [self::PLURAL_HUMAN, self::PLURAL],

        // type
        [self::TYPE_HUMAN, self::TYPE],

        // word
        [self::WORD_HUMAN, self::WORD],
        [self::WORD_POS_HUMAN, self::WORD_POS],
        [self::WORDS_HUMAN, self::WORDS],

        // verb
        [self::VERB_HUMAN, self::VERB],
        [self::REVERSE_HUMAN, self::REVERSE],
        [self::REVERSE_PLURAL_HUMAN, self::REVERSE_PLURAL],
        [self::NAME_IN_FORMULA_HUMAN, self::NAME_IN_FORMULA],
        [self::VERBS_HUMAN, self::VERBS],

        // triple
        [self::TRIPLE_HUMAN, self::TRIPLE],
        [self::PHRASE_FROM_HUMAN, self::PHRASE_FROM],
        [self::PHRASE_TO_HUMAN, self::PHRASE_TO],
        [self::WEIGHT_HUMAN, self::WEIGHT],
        [self::TRIPLES_HUMAN, self::TRIPLES],

        // phrase
        [self::PHRASE_HUMAN, self::PHRASE],
        [self::PHRASE_CLASS_HUMAN, self::PHRASE_CLASS],
        [self::PHRASE_LIST_HUMAN, self::PHRASE_LIST],
        [self::PHRASE_POS_HUMAN, self::PHRASE_POS],

        // graph
        [self::DIRECTION_HUMAN, self::DIRECTION],
        [self::LEVELS_HUMAN, self::LEVELS],

        // source
        [self::SOURCE_HUMAN, self::SOURCE],
        [self::SOURCE_TYPE_HUMAN, self::SOURCE_TYPE],
        [self::URL_HUMAN, self::URL],

        // ref
        [self::REF_HUMAN, self::REF],
        [self::REF_TYPE_HUMAN, self::REF_TYPE],
        [self::EX_KEY_HUMAN, self::EXTERNAL_KEY],

        // group
        [self::GROUP_HUMAN, self::GROUP],
        [self::GROUP_NAME_HUMAN, self::GROUP_NAME],

        // value
        [self::VALUE_HUMAN, self::VALUE],
        [self::VALUE_TIME_SERIES_HUMAN, self::VALUE_TIME_SERIES],
        [self::NUMERIC_VALUE_HUMAN, self::NUMERIC_VALUE],

        // formula
        [self::FORMULA_HUMAN, self::FORMULA],
        [self::FORMULA_TYPE_HUMAN, self::FORMULA_TYPE],
        [self::USER_EXPRESSION_HUMAN, self::USER_EXPRESSION],
        [self::NEED_ALL_HUMAN, self::NEED_ALL],
        [self::FORMULA_LINK_HUMAN, self::FORMULA_LINK],
        [self::FORMULA_LINK_PRIO_HUMAN, self::FORMULA_LINK_PRIO],
        [self::FORMULA_LINK_TYPE_HUMAN, self::FORMULA_LINK_TYPE],
        [self::FORMULAS_HUMAN, self::FORMULAS],

        // term
        [self::TERM_HUMAN, self::TERM],
        [self::TERM_POS_HUMAN, self::TERM_POS],

        // result
        [self::RESULT_HUMAN, self::RESULT],

        // figure
        [self::FIGURE_HUMAN, self::FIGURE],
        [self::WITH_PHRASES_HUMAN, self::WITH_PHRASES],

        // view
        [self::VIEW_HUMAN, self::VIEW],
        [self::VIEW_TYPE_HUMAN, self::VIEW_TYPE],
        [self::VIEW_TERM_LINK_HUMAN, self::VIEW_TERM_LINK],
        [self::VIEW_TERM_LINK_PRIO_HUMAN, self::VIEW_TERM_LINK_PRIO],
        [self::VIEW_LINK_HUMAN, self::VIEW_LINK],
        [self::VIEW_LINK_TYPE_HUMAN, self::VIEW_LINK_TYPE],
        [self::UNLINK_VIEW_HUMAN, self::UNLINK_VIEW],
        [self::VIEW_PARENT_HUMAN, self::VIEW_PARENT],
        [self::VIEW_CHILD_HUMAN, self::VIEW_CHILD],
        [self::POSITION_HUMAN, self::POSITION],

        // component
        [self::COMPONENT_HUMAN, self::COMPONENT],
        [self::COMPONENT_TYPE_HUMAN, self::COMPONENT_TYPE],
        [self::COMPONENT_LINK_HUMAN, self::COMPONENT_LINK],
        [self::COMPONENT_LINK_TYPE_HUMAN, self::COMPONENT_LINK_TYPE],
        [self::POSITION_TYPE_HUMAN, self::POSITION_TYPE],
        [self::STYLE_HUMAN, self::STYLE],
        [self::PHRASE_ROW_HUMAN, self::PHRASE_ROW],
        [self::PHRASE_COL_HUMAN, self::PHRASE_COL],
        [self::PHRASE_COL_SUB_HUMAN, self::PHRASE_COL_SUB],
        [self::LINK_PHRASE_HUMAN, self::LINK_PHRASE],
        [self::UNLINK_PHRASE_HUMAN, self::UNLINK_PHRASE],

        // im- and export
        [self::CONTEXT_HUMAN, self::CONTEXT],

        // log
        [self::LOG_HUMAN, self::LOG],
        [self::LOG_STATUS_HUMAN, self::LOG_STATUS],
        [self::LOG_CLASS_HUMAN, self::LOG_CLASS],
        [self::LOG_FIELD_HUMAN, self::LOG_FIELD],
        [self::LOG_LINK_HUMAN, self::LOG_LINK],
        [self::LOG_LEVEL_HUMAN, self::LOG_LEVEL],
        [self::LOG_FUNCTION_HUMAN, self::LOG_FUNCTION],
        [self::LOG_TIME_HUMAN, self::LOG_TIME],

        // system
        [self::SYS_LOG_HUMAN, self::SYS_LOG],
        [self::SYS_TRACE_HUMAN, self::SYS_TRACE],
        [self::IP_HUMAN, self::IP],
        [self::JOB_HUMAN, self::JOB],
        [self::JOB_TYPE_HUMAN, self::JOB_TYPE],
        [self::JOB_STATUS_HUMAN, self::JOB_STATUS],
        [self::JOB_PRIORITY_HUMAN, self::JOB_PRIORITY],
        [self::JOB_PARAMETER_HUMAN, self::JOB_PARAMETER],
        [self::JOB_CHANGE_FIELD_HUMAN, self::JOB_CHANGE_FIELD],
        [self::JOB_ROW_ID_HUMAN, self::JOB_ROW_ID],
        [self::JOB_REQUEST_TIME_HUMAN, self::JOB_REQUEST_TIME],
        [self::JOB_START_TIME_HUMAN, self::JOB_START_TIME],
        [self::JOB_END_TIME_HUMAN, self::JOB_END_TIME],

        // access
        [self::SHARE_HUMAN, self::SHARE],
        [self::PROTECTION_HUMAN, self::PROTECTION],

        // impact
        [self::USAGE_HUMAN, self::USAGE],
        [self::IMPACT_HUMAN, self::IMPACT],
    ];

    // map human-readable url values to standard url values
    const array HUMAN_TO_STD_ACTIONS_VAL = [
        self::CRUD_CREATE => self::CRUD_CREATE_HUMAN,
        self::CRUD_UPDATE => self::CRUD_READ_HUMAN,
        self::CRUD_DELETE => self::CRUD_REMOVE_HUMAN,
        self::CRUD_READ => self::CRUD_READ_HUMAN,
        self::SHOW_FULL => self::CRUD_FULL_HUMAN,
        self::SHOW_POPUP => self::CRUD_POPUP_HUMAN,
        self::SHOW_CREATE => self::CRUD_CELL_HUMAN,
    ];

    // map human-readable url values to standard url values
    const array HUMAN_TO_STD_STEP_VAL = [
        self::STEP_BASE => self::STEP_BASE_HUMAN,
        self::STEP_CONFIRM => self::STEP_CONFIRM_HUMAN,
        self::STEP_CONFIRMED => self::STEP_CONFIRMED_HUMAN,
        self::STEP_DONE => self::STEP_DONE_HUMAN,
        self::STEP_CANCEL => self::STEP_CANCEL_HUMAN,
        self::STEP_CANCELED => self::STEP_CANCELED_HUMAN,
        self::STEP_FAILED => self::STEP_FAILED_HUMAN,
    ];

    const array POD_TO_STD = [
        [self::MASK_HUMAN, self::MASK, views::START_CODE, true],
        [self::STEP_HUMAN, self::STEP, 0],
    ];

    // first entry is the from array key,
    // second is the default value
    const array STD_DEFAULT = [
        [self::MASK, views::START_ID, true],
        [self::STEP, 0]
    ];


    /*
     * functions
     */

    /**
     * return only the back-navigation entries from a url array
     * i.e. entries whose key starts with the BACK prefix character '9'
     *
     * @param array $url_array the full url parameter array
     * @return array the subset of $url_array whose keys are prefixed with BACK
     */
    static function back_par(array $url_array): array
    {
        return array_filter($url_array, fn($k) => str_starts_with($k, self::BACK), ARRAY_FILTER_USE_KEY);
    }

}
