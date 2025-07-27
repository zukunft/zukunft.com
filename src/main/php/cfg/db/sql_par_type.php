<?php

/*

    model/db/sql_par_type.php - enum of the sql where parameter types
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

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace cfg\db;

enum sql_par_type: string
{

    // the parameter types for prepared queries independent of the SQL dialect
    case FLOAT = 'numeric'; // a normal format for numbers
    case INT = 'int'; // a normal integer e.g. the unique database row id / prime index
    case INT_SMALL = 'int_small'; // a small integer e.g. the unique database row id of the most often used phrases
    case INT_HIGHER = 'int_higher'; // the result includes the given int value an all rows with a higher value
    case INT_LOWER = 'int_lower'; //
    case INT_OR = 'int_or'; //
    case INT_NOT = 'int_not';
    case INT_NOT_OR_NULL = 'int_not_or_null';
    case INT_LIST = 'int_list';
    case INT_LIST_OR = 'int_list_or';
    case INT_SUB = 'int_sub'; // a sub query is using an int parameter
    case INT_SUB_IN = 'int_sub_in'; // a sub query is using an int parameter and the IN SQL condition
    case INT_SAME = 'int_same'; // repeat the previous integer with and
    case INT_SAME_OR = 'int_same_or'; // repeat the previous integer with or
    case LIMIT = 'limit'; // the query limit as an integer that is not used in the where statement
    case OFFSET = 'offset'; // the query offset as an integer that is not used in the where statement
    case TEXT = 'text';
    case TEXT_LIST = 'text_list';
    case TEXT_OR = 'text_or';
    case KEY_512 = 'key512';
    case TEXT_USR = 'text_usr'; // a name that can be user specific e.g. the word or triple name
    case LIKE_R = 'like_start_with'; // add a wildcard to the right to fine the values that start with the given text
    case LIKE = 'like';
    case LIKE_OR = 'like_or'; // connect with the previous condition with OR and like
    case CONST = 'const';
    case CONST_NOT = 'const_not';
    case CONST_OR_NULL = 'const_or_null';
    case CONST_NOT_IN = 'const_not_in';
    case IS_NULL = 'is_null';
    case NOT_NULL = 'not_null';
    case TIME = 'timestamp'; // e.g. for now()
    case POINT = 'point'; // e.g. for geolocations

    // parameter types to calculate the result for a group
    case MIN = 'min';
    case MAX = 'max';
    case COUNT = 'count';

    function is_function(): bool
    {
        return match($this) {
            self::MIN, self::MAX, self::COUNT, self::LIMIT, self::OFFSET => true,
            default => false,
        };
    }

    function is_or(): bool
    {
        return match($this) {
            self::TEXT_OR, self::INT_OR, self::INT_LIST_OR, self::LIKE_OR, self::INT_SAME_OR => true,
            default => false,
        };
    }

    /**
     * @return bool true if the selection is based on a list e.g. "IN (1,2,3)"
     */
    function is_list(): bool
    {
        return match($this) {
            self::INT_LIST, self::INT_LIST_OR, self::TEXT_LIST => true,
            default => false,
        };
    }

    /**
     * @return bool true if the selection is based on a list e.g. "IN (1,2,3)"
     */
    function is_text(): bool
    {
        return match($this) {
            self::TEXT, self::TEXT_USR, self::TEXT_OR, self::INT_SUB, self::INT_SUB_IN, self::KEY_512 => true,
            default => false,
        };
    }

}