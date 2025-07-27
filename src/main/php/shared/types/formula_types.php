<?php

/*

    shared/types/formula_types.php - db based ENUM of the formula types
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

    Copyright (c) 1995-2023 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

namespace shared\types;

class formula_types
{

    // list of the formula types that have a coded functionality
    const CALC = "default";    // a normal calculation formula
    const NEXT = "time_next";  // time jump forward: replaces a time term with the next time term based on the verb follower. E.g. "2017" "next" would lead to use "2018"
    const THIS = "time_this";  // selects the assumed time term
    const PREV = "time_prior"; // time jump backward: replaces a time term with the previous time term based on the verb follower. E.g. "2017" "next" would lead to use "2016"
    const REV = "reversible";  // used to define a const value that is not supposed to be changed like pi

}
