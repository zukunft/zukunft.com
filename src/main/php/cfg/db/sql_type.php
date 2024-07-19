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
    case NULL = 'null'; // an empty type as a placeholder
    case INSERT = 'insert';
    case UPDATE = 'update';
    case DELETE = 'delete';
    case REF = 'ref'; // to change the log query name if a reference has been changed
    case LOAD = 'load';
    case NORM = 'norm'; // the data used be most users should be loaded
    case NORM_EXT = 'norm_ext'; // force to use the norm extesion for the table name e.g. for the change log
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
            self::INSERT => sql::NAME_SEP . self::INSERT->value,
            self::UPDATE => sql::NAME_SEP . self::UPDATE->value,
            self::DELETE => sql::NAME_SEP . self::DELETE->value,
            self::EXCLUDE => sql::NAME_SEP . 'excluded',
            self::NORM, self::NORM_EXT => sql::NAME_SEP . self::NORM->value,
            self::PRIME => sql::NAME_SEP . self::PRIME->value,
            self::MAIN => sql::NAME_SEP . self::MAIN->value,
            self::BIG => sql::NAME_SEP . self::BIG->value,
            self::INDEX => sql::NAME_SEP . self::INDEX->value,
            self::LARGE => sql::NAME_SEP . self::LARGE->value,
            self::STANDARD => sql::NAME_SEP . self::STANDARD->value,
            self::USER => sql::NAME_SEP . self::USER->value,
            self::SUB => sql::NAME_SEP . self::SUB->value,
            self::LIST => sql::NAME_SEP . self::LIST->value,
            self::LOG => sql::NAME_SEP . self::LOG->value,
            self::REF => sql::NAME_SEP . self::REF->value,
            default => '',
        };
    }

    /**
     * @return string the name prefix for the query name
     */
    function prefix(): string
    {
        return match($this) {
            self::PRIME => self::PRIME->value . sql::NAME_SEP,
            self::USER => self::USER->value . sql::NAME_SEP,
            default => '',
        };
    }

    /**
     * @return bool true if the sql type changes the database e.g. an update query
     */
    function is_sql_change(): bool
    {
        return match($this) {
            self::INSERT, self::UPDATE, self::DELETE => true,
            default => false,
        };
    }

}