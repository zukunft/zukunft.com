<?php

/*

    /model/dp/sql_par_type.php - enum of the sql where parameter types
    ---------------------------

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
    case INT = 'int'; // a normal integer e.g. the unique database row id / prime index
    case INT_HIGHER = 'int_higher'; // the result includes the given int value an all rows with a higher value
    case INT_LOWER = 'int_lower'; //
    case INT_OR = 'int_or'; //
    case INT_NOT = 'int_not';
    case INT_NOT_OR_NULL = 'int_not_or_null';
    case INT_LIST = 'int_list';
    case INT_LIST_OR = 'int_list_or';
    case INT_SUB = 'int_sub'; // a sub query is using an int parameter
    case LIMIT = 'limit'; // the query limit as an integer that is not used in the where statement
    case OFFSET = 'offset'; // the query offset as an integer that is not used in the where statement
    case TEXT = 'text';
    case TEXT_LIST = 'text_list';
    case TEXT_OR = 'text_or';
    case TEXT_USR = 'text_usr'; // a name that can be user specific e.g. the word or triple name
    case LIKE = 'like';
    case CONST = 'const';
    case CONST_NOT = 'const_not';

}