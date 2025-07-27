<?php

/*

    model/formula/formula_type.php - the formula type object with the ENUM values for hardcoded formulas
    ------------------------------

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

namespace cfg\formula;

use cfg\const\paths;

include_once paths::MODEL_HELPER . 'type_object.php';

use cfg\helper\type_object;

class formula_type extends type_object
{

    /*
     * code links
     */

    // list of the formula types that have a coded functionality
    const CALC = "default";    // a normal calculation formula
    const NEXT = "time_next";  // time jump forward: replaces a time term with the next time term based on the verb follower. E.g. "2017" "next" would lead to use "2018"
    const THIS = "time_this";  // selects the assumed time term
    const PREV = "time_prior"; // time jump backward: replaces a time term with the previous time term based on the verb follower. E.g. "2017" "next" would lead to use "2016"
    const REV = "reversible";  // used to define a const value that is not supposed to be changed like pi
    const DEFAULT = self::CALC;


    /*
     * database link
     */

    // comments used for the database creation
    const TBL_COMMENT = 'to assign predefined behaviour to formulas';

}
