<?php

/*

    shared/api.php - constants used for the backend to frontend api of zukunft.com
    --------------


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

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace shared;

class api
{

    /*
     * URL
     */

    // the parameter names used in the url or in the result json
    const URL_API_PATH = 'api/';


    /*
     * JSON
     */

    // json field names of the api json messages
    const JSON_BODY = 'body';
    const JSON_BODY_SYS_LOG = 'sys_log';

    // to include the objects that should be displayed in one api message
    const JSON_WORD = 'word';
    const JSON_TRIPLE = 'triple';

    //
    const JSON_TYPE_LISTS = 'type_lists';
    const JSON_LIST_USER_PROFILES = 'user_profiles';
    const JSON_LIST_PHRASE_TYPES = 'phrase_types';
    const JSON_LIST_FORMULA_TYPES = 'formula_types';
    const JSON_LIST_FORMULA_LINK_TYPES = 'formula_link_types';
    const JSON_LIST_ELEMENT_TYPES = 'element_types';
    const JSON_LIST_VIEW_TYPES = 'view_types';
    const JSON_LIST_COMPONENT_TYPES = 'component_types';
    // const JSON_LIST_COMPONENT_LINK_TYPES = 'component_link_types';
    const JSON_LIST_COMPONENT_POSITION_TYPES = 'position_types';
    const JSON_LIST_REF_TYPES = 'ref_types';
    const JSON_LIST_SOURCE_TYPES = 'source_types';
    const JSON_LIST_SHARE_TYPES = 'share_types';
    const JSON_LIST_PROTECTION_TYPES = 'protection_types';
    const JSON_LIST_LANGUAGES = 'languages';
    const JSON_LIST_LANGUAGE_FORMS = 'language_forms';
    const JSON_LIST_SYS_LOG_STATI = 'sys_log_stati';
    const JSON_LIST_JOB_TYPES = 'job_types';
    const JSON_LIST_CHANGE_LOG_ACTIONS = 'change_action_list';
    const JSON_LIST_CHANGE_LOG_TABLES = 'change_table_list';
    const JSON_LIST_CHANGE_LOG_FIELDS = 'change_field_list';
    const JSON_LIST_VERBS = 'verbs';
    const JSON_LIST_SYSTEM_VIEWS = 'system_views';

    /*
     * fields
     */

    // json field names of the api json messages
    // which is supposed to be the same as the corresponding var of the api object
    // so that no additional mapping is needed
    const FLD_ID = 'id'; // the unique database id used to save the changes
    const FLD_NAME = 'name'; // the unique name of the object which is also a database index
    const FLD_DESCRIPTION = 'description';

    // the json field name in the api json message which is supposed to contain
    // the database id (or in some cases still the code id) of an object type
    // e.g. for the word api message it contains the id of the phrase type
    const FLD_TYPE = 'type_id';

    // the json field name for code id to select a single object
    // e.g. to select a system view
    const FLD_CODE_ID = 'code_id';

    // reference fields e.g. to link a phrase to an external reference
    const FLD_PHRASE = 'phrase_id';
    const FLD_SOURCE = 'source_id';

    // object list
    const FLD_PHRASES = 'phrases';
    const FLD_COMPONENTS = 'components';
    const FLD_POSITION = 'position';
    const FLD_LINK_ID = 'link_id';

    // object fields
    const FLD_NUMBER = 'number'; // a float number used for values and results
    const FLD_IS_STD = 'is_std'; // flag if a value or result is user specific or the default value for all users
    const FLD_USER_TEXT = 'user_text'; // the formula expression in a human-readable format
    const FLD_REF_TEXT = 'ref_text'; // the formula expression in a database reference format
    const FLD_NEED_ALL_VAL = 'need_all_val'; // calculate and save the result only if all used values are not null
    const FLD_FORMULA_NAME_PHRASE = 'name_phrase'; // the phrase object for the formula name
    const FLD_URL = 'url'; // the external link of a source or a reference
    const FLD_EXTERNAL_KEY = 'external_key'; // the unique key of the reference
    const FLD_PHRASE_ROW = 'word_row'; // the phrase to select the row name of a view component
    const FLD_PHRASE_COL = 'word_col'; // the phrase to select the column name of a view component

    // batch job fields
    const FLD_TIME_REQUEST = 'request_time'; // e.g. the timestamp when a batch job has been requested
    const FLD_PRIORITY = 'priority'; // of the batch job
    const FLD_TIME_START = 'start_time'; // e.g. the timestamp of a log entry
    const FLD_TIME_END = 'end_time'; // e.g. the timestamp of a log entry
    const FLD_STATUS = 'status'; // of the job and also used for the sys log

    // change log fields
    const FLD_TIME = 'time'; // e.g. the timestamp of a log entry
    const FLD_TEXT = 'text'; // the description of the change as a fixed text

    // system log fields
    const FLD_TRACE = 'trace'; // what has lead to the issue
    const FLD_PRG_PART = 'prg_part'; // which part has caused the issue
    const FLD_OWNER = 'owner'; // the developer which wants to fix the problem

    const FLD_USER_ID = 'user_id';

    // phrase api specific fields
    const FLD_PHRASE_CLASS = 'class';


    /**
     * check if an api message is fine
     * @param array $api_msg the complete api message including the header and in some cases several body parts
     * @param string $body_key to select a body part of the api message
     * @return array the message body if everything has been fine or an empty array
     */
    function check_api_msg(array $api_msg, string $body_key = api::JSON_BODY): array
    {
        $msg_ok = true;
        $body = array();
        // TODO check transfer time
        // TODO check if version matches
        if ($msg_ok) {
            if (array_key_exists($body_key, $api_msg)) {
                $body = $api_msg[$body_key];
            } else {
                // TODO activate Prio 3 next line and avoid these cases
                // $msg_ok = false;
                $body = $api_msg;
                log_warning('message header missing in api message');
            }
        }
        if ($msg_ok) {
            return $body;
        } else {
            return array();
        }
    }

}
