<?php

/*

  word_types.php - to link coded functionality to a word or a word link, which means to every phrase
  -----------------
  
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
  along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.
  
  To contact the authors write to:
  Timon Zielonka <timon@zukunft.com>
  
  Copyright (c) 1995-2021 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

global $word_types;

class word_type_list extends user_type_list
{
    // list of the word types that have a coded functionality
    const DBL_NORMAL = "default";
    const DBL_MATH_CONST = "constant";
    const DBL_TIME = "time";
    const DBL_TIME_JUMP = "time_jump";
    const DBL_LATEST = "latest";
    const DBL_PERCENT = "percent";
    const DBL_MEASURE = "measure";
    const DBL_SCALING = "scaling";
    const DBL_SCALING_HIDDEN = "scaling_hidden";
    const DBL_SCALING_PCT = "scaling_percent"; // TODO used to define the scaling formula word to scale percentage values ?
    const DBL_SCALED_MEASURE = "scaled_measure";
    const DBL_FORMULA_LINK = "formula_link";
    const DBL_CALC = "calc";
    const DBL_LAYER = "view";
    const DBL_OTHER = "type_other";

    /**
     * overwrite the general user type list load function to keep the link to the table type capsuled
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @return bool true if load was successful
     */
    function load(sql_db $db_con, string $db_type = DB_TYPE_WORD_TYPE): bool
    {
        return parent::load($db_con, $db_type);
    }

    /**
     * adding the word types used for unit tests to the dummy list
     */
    function load_dummy() {
        parent::load_dummy();
        $type = new user_type();
        $type->name = word_type_list::DBL_NORMAL;
        $type->code_id = word_type_list::DBL_NORMAL;
        $this->lst[2] = $type;
        $this->hash[word_type_list::DBL_NORMAL] = 2;
        $type = new user_type();
        $type->name = word_type_list::DBL_TIME;
        $type->code_id = word_type_list::DBL_TIME;
        $this->lst[3] = $type;
        $this->hash[word_type_list::DBL_TIME] = 3;
        $type = new user_type();
        $type->name = word_type_list::DBL_MEASURE;
        $type->code_id = word_type_list::DBL_MEASURE;
        $this->lst[4] = $type;
        $this->hash[word_type_list::DBL_MEASURE] = 4;
    }

    /**
     * return the database id of the default word type
     */
    function default_id(): int {
        return parent::id(word_type_list::DBL_NORMAL);
    }

}
