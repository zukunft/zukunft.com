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

namespace shared;

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
    const PAR = '?';
    const ADD = '&';
    const EQ = '=';
    const API_PATH = 'api/';
    const ID = 'id'; // the internal database id of the main view object


    /*
     * url type
     */

    // the url entry to select the url format
    // if the next two are not set at least MASK is expected to be set and if the short technical url is used
    const MASK = 'm'; // the internal database id of the view used to format the object
    const MASK_HUMAN = 'mask_id'; // if *_LONG is given the human-readable url format is used
    const MASK_POD = 'mask'; // if *_EXCHANGE is given the url that is interchangeable between pods is used thet does not contain pod specific database ids


    /*
     * short url
     */

    // the var names for the short technical url (in alphabetic order to detect duplicates)
    const ACTION = 'a'; // the curl action
    const VERB = 'b';
    const COMPONENT = 'c';
    const COMPONENT_LINK = 'cl'; // to link a component to a view
    const TERM = 'e';
    const FORMULA = 'f';
    const FORMULA_LINK = 'fl'; // to link a formula to a phrase
    const GROUP = 'g';
    const LOG = 'h'; // h for history of the object
    const FIGURE = 'i';
    const JOB = 'j'; // for system batch jobs
    // const MASK = 'm'; // just the placeholder to remember that the char m is used
    const RESULT = 'r';
    const SOURCE = 's';
    const VIEW = 'v';
    const WORD = 'w';
    const TRIPLE = 't';
    const PHRASE = 'p'; // the id or name of one phrase
    const VALUE = 'v';
    const VIEW_TERM_LINK = 'vl'; // to link a view to a term
    const CONTEXT = 'x'; // list of terms to describe the context used for the view
    const STEP = 'z'; // the user process (proZess) step (e.g. show, to_confirm, confirmed)
    const VALUE_TIME_SERIES = 'ts';
    const LOG_LINK = 'hl'; // history of a link object
    const SYS_LOG = 'hs'; // history of a system event
    const IP = 'ip'; // for ip ranges (for admin only)


    /*
     * standard and human readable url
     */

    // the var names for the easy human-readable url (in content related order)
    const ACTION_LONG = 'action'; // the curl action for the long url
    const STEP_LONG = 'step_id';  // the action status for the long url

    // enum for next step the action for ACTION
    const CURL_CREATE = 'add'; // the curl action code to add an object
    const CURL_UPDATE = 'edit'; // the curl action to change an object
    const CURL_REMOVE = 'del'; // the curl action to delete an object
    const CURL_LOAD = 'show'; // the curl action to show object with the most relevant fields
    const CURL_FULL = 'full'; // to show object with all fields
    const CURL_POPUP = 'popup'; // to show object with only a few fields as a popup window
    const CURL_CELL = 'cell'; // to show object with only the name or key as table cell

    const WORD_LONG = 'word_id';
    const WORD_POS_LONG = 'word_id_pos_';
    const VERB_LONG = 'verb_id';
    const TRIPLE_LONG = 'triple_id';
    const PHRASE_LONG = 'phrase_id';
    // used for a list of phrases where the list position is added to the name
    const PHRASE_POS_LONG = 'phrase_id_pos_';
    const PHRASE_LINK_FORMULA_LONG = 'phrase_id';
    const PHRASE_LIST_LONG = 'phrase_ids';
    const TERM_LONG = 'term_id';
    // used for a list of terms where the list position is added to the name
    const TERM_POS_LONG = 'term_id_pos_';
    const SOURCE_LONG = 'source_id';
    const REF = 'l'; // l for data link to external
    const NUMERIC_VALUE_LONG = 'number';
    const FORMULA_LONG = 'formula_id';
    const VIEW_LONG = 'view_id';
    const COMPONENT_LONG = 'component_id';
    const ID_LST = 'ids'; // a comma separated list of internal database ids
    const NAME = 'name'; // the unique name of a term, view, component, user, source, language or type
    const PATTERN = 'pattern'; // part of a name to select a named object such as word, triple, ...

    // to select the configuration part that should be updated in the frontend e.g. all, frontend or user
    const CONFIG_PART = 'part';
    const WITH_PHRASES = 'incl_phrases';
    const TRUE = '1';
    const COMMENT = 'comment';
    const DESCRIPTION = 'description';
    const DEBUG = 'debug'; // to force the output of debug messages
    const CODE_ID = 'code_id';
    const WORDS = 'words'; // to select the words that should be displayed
    const VERBS = 'verbs';  // to select the verbs that should be displayed
    const TRIPLES = 'triples'; // to select the triples that should be displayed
    const FORMULAS = 'formulas';  // to select the formulas that should be displayed
    const DIRECTION = 'dir'; // 'up' to get the parents and 'down' for the children
    const LEVELS = 'levels'; // the number of search levels'
    const MSG = 'message';
    const EMAIL = 'email';
    const VIEW_ID = 'view_id'; //
    const CMP_ID = 'component_id';
    const CHILDREN = 'levels'; // number of component levels that should be included
    const USER = 'user';
    const BACK = 'back';

    // to be sorted
    const NEED_ALL = 'need_all_val';
    const USER_EXPRESSION = 'formula_text';
    const LINK_VIEW = 'link_view';
    const FORMULA_TYPE = 'formula_type';
    const VIEW_TYPE = 'view_type';
    const VIEW_LINK_TYPE = 'view_link_type';
    const COMPONENT_TYPE = 'component_type';
    const COMPONENT_LINK_TYPE = 'component_link_type';
    const POSITION_TYPE = 'position_type';
    const SOURCE_TYPE = 'source_type';
    const REF_TYPE = 'ref_type';
    const USER_PROFILE = 'user_profile';
    const UNLINK_VIEW = 'unlink_view';
    const PHRASE_FROM = 'phrase_row';
    const PHRASE_TO = 'phrase_row';
    const PHRASE_ROW = 'phrase_row';
    const PHRASE_COL = 'phrase_col';
    const PHRASE_COL_SUB = 'phrase_col_sub';
    const TYPE = 'type';
    const STYLE = 'style';
    const SHARE = 'share';
    const PROTECTION = 'protection';
    const REFERENCE = 'reference';
    const PHRASE_TYPE = 'phrase_type';
    const PLURAL = 'plural';
    const REVERSE = 'reverse';
    const REVERSE_PLURAL = 'plural_reverse';
    const URL = 'url';
    const EXTERNAL_KEY = 'external_key';

    // for triple link selections
    const FROM_ID_LONG = 'from_id';
    const TO_ID_LONG = 'to_id';

    // used for the change log
    // the short name of the object class name e.g. word instead of cfg/word
    const CLASS_NAME = 'class';
    // the name of the field to filter the changes which might be more than one database field
    const FIELD = 'field';
    const WORD_ID = 'word_id';
    const WORD_FLD = 'word_field';
    const LINK_PHRASE = 'link_phrase';
    const UNLINK_PHRASE = 'unlink_phrase';

    // used for languages
    const LANGUAGE = 'language';
    const LANGUAGE_FORM = 'languageForm';


    /*
     * pod exchangeable url
     */

    // the var names for urls that work for more than one pod (in content related order)
    const STEP_POD = 'step';  // the action status for the long url

}
