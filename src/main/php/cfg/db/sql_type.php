<?php

/*

    /model/dp/sql_type.php - enum of the sql statement creation parameters
    ----------------------

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

namespace cfg\db;

enum sql_type: string
{

    // curl sql statement types
    case INSERT = 'insert';
    case UPDATE = 'update';
    case DELETE = 'delete';
    case LOAD = 'load';
    case NORM = 'norm'; // the data used be most users should be loaded
    case COMPLETE = 'complete'; // force to load all rows from the database because the number of rows are expected to be limited and all rows should be e.g. load to the chance at once

    // the fixed table types for a value or result
    case PRIME = 'prime'; // up to four 16-bit phrase ids or one formula id and three phrase ids
    case MAIN = 'main'; // for result only one formula id and up to seven 64-bit phrase ids
    case MOST = 'most'; // up to 16 64-bit phrase ids
    case BIG = 'big'; // more than 16 64-bit phrase ids
    case INDEX = 'index'; // one 32-bit and two 16-bit phrase ids
    case LARGE = 'large'; // one 48-bit and one 16-bit phrase ids

    // the fixed table subtypes
    case STANDARD = 'standard'; // value or result that is public and unprotected
    case USER = 'user'; // for user specific values and results

    // sql builder parameters
    case FUNCTION = 'function'; // create a function that combines a list of sql statements
    case SUB = 'sub'; // the created sql statement should be used as part of another prepared sql statement
    case LIST = 'list'; // the created sql statement should be used as part of with sql statement
    case LOG = 'log'; // the created sql statement should include statements for logging the changes
    case NO_ID_RETURN = 'no_id_return'; // the created sql statement does not need to return the id
    case NAMED_PAR = 'named_par'; // to use named parameters in the prepared query e.g. _user_id instead od $1
    case VALUE_SELECT = 'value_select'; // use a select statement for the insert values
    case INSERT_PART = 'insert_part'; // the sql statement is part of an insert action which implies that a new db row id is added
    case UPDATE_PART = 'update_part'; // the sql statement is part of an update action which implies that no new db row id is added
    case DELETE_PART = 'delete_part'; // the sql statement is part of a delete function which implies that only old fields are used
    case EXCLUDE = 'exclude'; // instead of delete create a sql to exclude one row
    case SANDBOX = 'sandbox'; // to include the standard sandbox fields in the sql statement
    case KEY_SMALL_INT = 'key_small_int'; // use a smallint as the prime db key e.g. for types
    case SELECT_FOR_INSERT = 'select_for_insert'; // use a select statement for the insert values

    /**
     * @return string the name extension for the query name
     */
    public function extension(): string
    {
        return match($this) {
            self::INSERT => sql::file_sep . 'insert',
            self::UPDATE => sql::file_sep . 'update',
            self::DELETE => sql::file_sep . 'delete',
            self::NORM => sql::file_sep . 'norm',
            self::PRIME => sql::file_sep . 'prime',
            self::MAIN => sql::file_sep . 'main',
            self::BIG => sql::file_sep . 'big',
            self::INDEX => sql::file_sep . 'index',
            self::LARGE => sql::file_sep . 'large',
            self::STANDARD => sql::file_sep . 'standard',
            self::USER => sql::file_sep . 'user',
            self::SUB => sql::file_sep . 'sub',
            self::LIST => sql::file_sep . 'list',
            self::LOG => sql::file_sep . 'log',
            default => '',
        };
    }

    /**
     * @return string the name prefix for the query name
     */
    function prefix(): string
    {
        return match($this) {
            self::PRIME => 'prime' . sql::file_sep,
            self::USER => 'user' . sql::file_sep,
            default => '',
        };
    }
}