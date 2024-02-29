<?php

/*

    api/api.php - constants used for the backend to frontend api of zukunft.com
    -----------


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

namespace api;

class api
{

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

    // job job fields
    const FLD_TIME_REQUEST = 'request_time'; // e.g. the timestamp when a job job has been requested
    const FLD_PRIORITY = 'priority'; // of the job job
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
}
