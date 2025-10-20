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
    const string ID = 'id'; // the internal database id of the main view object


    /*
     * url type
     */

    // the url entry to select the url format
    // if the next two are not set at least MASK is expected to be set and if the short technical url is used
    const string MASK = 'm'; // the internal database id of the view used to format the object
    const string MASK_HUMAN = 'mask_id'; // if *_LONG is given the human-readable url format is used
    const string MASK_POD = 'mask'; // if *_EXCHANGE is given the url that is interchangeable between pods is used thet does not contain pod specific database ids


    /*
     * short url
     */

    // the var names for the short technical url (in alphabetic order to detect duplicates)
    const string ACTION = 'a'; // the crud action
    const string VERB = 'b';
    const string COMPONENT = 'c';
    const string COMPONENT_LINK = 'cl'; // to link a component to a view
    const string TERM = 'e';
    const string FORMULA = 'f';
    const string FORMULA_LINK = 'fl'; // to link a formula to a phrase
    const string FORMULA_LINK_PRIO = 'fp';
    const string GROUP = 'g';
    const string GROUP_NAME = 'gn';
    const string LOG = 'h'; // h for history of the object
    const string FIGURE = 'i';
    const string JOB = 'j'; // for system batch jobs
    // const string MASK = 'm'; // just the placeholder to remember that the char m is used
    const string RESULT = 'r';
    const string SOURCE = 's';
    const string VIEW = 'v';
    const string WORD = 'w';
    const string TRIPLE = 't';
    const string PHRASE = 'p'; // the id or name of one phrase
    const string VALUE = 'v';
    const string VIEW_TERM_LINK = 'vl'; // to link a view to a term
    const string VIEW_TERM_LINK_PRIO = 'vp'; //
    const string CONTEXT = 'x'; // list of terms to describe the context used for the view
    const string STEP = 'z'; // the user process (proZess) step (e.g. show, to_confirm, confirmed)
    const string VALUE_TIME_SERIES = 'ts';
    const string LOG_LINK = 'hl'; // history of a link object
    const string SYS_LOG = 'hs'; // history of a system event
    const string IP = 'ip'; // for ip ranges (for admin only)


    /*
     * standard and human readable url
     */

    // the var names for the easy human-readable url (in content related order)
    const string ACTION_LONG = 'action'; // the CRUD action for the long url
    const string STEP_LONG = 'step_id';  // the action status for the long url

    // enum for next step the action for ACTION
    const string CRUD_CREATE = 'add'; // the CRUD action code to add an object
    const string CRUD_UPDATE = 'edit'; // the CRUD action to change an object
    const string CRUD_REMOVE = 'del'; // the CRUD action to delete an object
    const string CRUD_READ = 'show'; // the CRUD action to show object with the most relevant fields
    const string CRUD_FULL = 'full'; // to show object with all fields
    const string CRUD_POPUP = 'popup'; // to show object with only a few fields as a popup window
    const string CRUD_CELL = 'cell'; // to show object with only the name or key as table cell

    const string WORD_LONG = 'word_id';
    const string WORD_POS_LONG = 'word_id_pos_';
    const string VERB_LONG = 'verb_id';
    const string TRIPLE_LONG = 'triple_id';
    const string PHRASE_LONG = 'phrase_id';
    // used for a list of phrases where the list position is added to the name
    const string PHRASE_POS_LONG = 'phrase_id_pos_';
    const string PHRASE_LINK_FORMULA_LONG = 'phrase_id';
    const string PHRASE_LIST_LONG = 'phrase_ids';
    const string TERM_LONG = 'term_id';
    // used for a list of terms where the list position is added to the name
    const string TERM_POS_LONG = 'term_id_pos_';
    const string SOURCE_LONG = 'source_id';
    const string REF = 'l'; // l for data link to external
    const string NUMERIC_VALUE_LONG = 'number';
    const string FORMULA_LONG = 'formula_id';
    const string VIEW_LONG = 'view_id';
    const string COMPONENT_LONG = 'component_id';
    const string ID_LST = 'ids'; // a comma separated list of internal database ids
    const string NAME = 'name'; // the unique name of a term, view, component, user, source, language or type
    const string PATTERN = 'pattern'; // part of a name to select a named object such as word, triple, ...

    // to select the configuration part that should be updated in the frontend e.g. all, frontend or user
    const string CONFIG_PART = 'part';
    const string WITH_PHRASES = 'incl_phrases';
    const string TRUE = '1';
    const string COMMENT = 'comment';
    const string DESCRIPTION = 'description';
    const string DEBUG = 'debug'; // to force the output of debug messages
    const string CODE_ID = 'code_id';
    const string WORDS = 'words'; // to select the words that should be displayed
    const string VERBS = 'verbs';  // to select the verbs that should be displayed
    const string TRIPLES = 'triples'; // to select the triples that should be displayed
    const string FORMULAS = 'formulas';  // to select the formulas that should be displayed
    const string DIRECTION = 'dir'; // 'up' to get the parents and 'down' for the children
    const string LEVELS = 'levels'; // the number of search levels'
    const string MSG = 'message';
    const string GROUP_NAME_LONG = 'group_name';
    const string EMAIL = 'email';
    const string VIEW_ID = 'view_id'; //
    const string CMP_ID = 'component_id';
    const string CHILDREN = 'levels'; // number of component levels that should be included
    const string USER = 'user';
    const string BACK = 'back';

    // to be sorted
    const string NEED_ALL = 'need_all_val';
    const string USER_EXPRESSION = 'formula_text';
    const string LINK_VIEW = 'link_view';
    const string FORMULA_TYPE = 'formula_type';
    const string VIEW_TYPE = 'view_type';
    const string VIEW_LINK_TYPE = 'view_link_type';
    const string COMPONENT_TYPE = 'component_type';
    const string COMPONENT_LINK_TYPE = 'component_link_type';
    const string POSITION_TYPE = 'position_type';
    const string SOURCE_TYPE = 'source_type';
    const string REF_TYPE = 'ref_type';
    const string USER_PROFILE = 'user_profile';
    const string UNLINK_VIEW = 'unlink_view';
    const string PHRASE_FROM = 'phrase_row';
    const string PHRASE_TO = 'phrase_row';
    const string PHRASE_ROW = 'phrase_row';
    const string PHRASE_COL = 'phrase_col';
    const string PHRASE_COL_SUB = 'phrase_col_sub';
    const string TYPE = 'type';
    const string STYLE = 'style';
    const string SHARE = 'share';
    const string PROTECTION = 'protection';
    const string EXCLUDED = 'excluded';
    const string REFERENCE = 'reference';
    const string PHRASE_TYPE = 'phrase_type';
    const string PLURAL = 'plural';
    const string REVERSE = 'reverse';
    const string REVERSE_PLURAL = 'plural_reverse';
    const string NAME_IN_FORMULA = 'name_in_formula';
    const string URL = 'url';
    const string WEIGHT = 'weight';

    const string USAGE = 'usage';
    const string IMPACT = 'impact';
    const string EXTERNAL_KEY = 'external_key';

    // for triple link selections
    const string FROM_ID_LONG = 'from_id';
    const string TO_ID_LONG = 'to_id';

    // used for the change log
    // the short name of the object class name e.g. word instead of cfg/word
    const string CLASS_NAME = 'class';
    // the name of the field to filter the changes which might be more than one database field
    const string FIELD = 'field';
    const string WORD_ID = 'word_id';
    const string WORD_FLD = 'word_field';
    const string LINK_PHRASE = 'link_phrase';
    const string UNLINK_PHRASE = 'unlink_phrase';

    // used for languages
    const string LANGUAGE = 'language';
    const string LANGUAGE_FORM = 'languageForm';


    /*
     * pod exchangeable url
     */

    // the var names for urls that work for more than one pod (in content related order)
    const string STEP_POD = 'step';  // the action status for the long url

}
