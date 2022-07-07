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
  along with zukunft.com. If not, see <http://www.gnu.org/licenses/agpl.html>.
  
  To contact the authors write to:
  Timon Zielonka <timon@zukunft.com>
  
  Copyright (c) 1995-2022 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

global $word_types;

class word_type_list extends user_type_list
{
    // list of the word types that have a coded functionality
    // TODO add the missing functionality and unit tests
    const DBL_NORMAL = "default";
    const DBL_MATH_CONST = "constant"; // TODO add usage sample
    const DBL_TIME = "time";
    const DBL_TIME_JUMP = "time_jump";
    const DBL_LATEST = "latest"; // TODO add usage sample
    const DBL_PERCENT = "percent";
    const DBL_MEASURE = "measure";
    const DBL_SCALING = "scaling";
    const DBL_SCALING_HIDDEN = "scaling_hidden";
    const DBL_SCALING_PCT = "scaling_percent"; // TODO used to define the scaling formula word to scale percentage values ?
    const DBL_SCALED_MEASURE = "scaled_measure"; // TODO add usage sample
    const DBL_FORMULA_LINK = "formula_link";
    const DBL_CALC = "calc"; // TODO add usage sample
    const DBL_LAYER = "view"; // TODO add usage sample
    const DBL_OTHER = "type_other";

    const TYPES = array(
        self::DBL_NORMAL,
        self::DBL_MATH_CONST,
        self::DBL_TIME,
        self::DBL_TIME_JUMP,
        self::DBL_LATEST,
        self::DBL_PERCENT,
        self::DBL_MEASURE,
        self::DBL_SCALING,
        self::DBL_SCALING_HIDDEN,
        self::DBL_SCALING_PCT,
        self::DBL_SCALED_MEASURE,
        self::DBL_FORMULA_LINK,
        self::DBL_CALC,
        self::DBL_LAYER,
        self::DBL_OTHER
    );

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
    function load_dummy()
    {
        parent::load_dummy();
        $i = 2;
        foreach (self::TYPES as $type_name)
        {
            $type = new user_type();
            $type->name = $type_name;
            $type->code_id = $type_name;
            $this->lst[$i] = $type;
            $this->hash[$type_name] = $i;
            $i++;
        }
    }

    /**
     * return the database id of the default word type
     */
    function default_id(): int
    {
        return parent::id(word_type_list::DBL_NORMAL);
    }

}
