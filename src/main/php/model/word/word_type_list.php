<?php

/*

    word_type_list.php - to link coded functionality to a word or a triple, which means to every phrase
    ------------------

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

global $phrase_types;

use cfg\phrase_type;
use cfg\type_list;
use cfg\type_object;

class word_type_list extends type_list
{

    const TYPES = array(
        phrase_type::NORMAL,
        phrase_type::MATH_CONST,
        phrase_type::TIME,
        phrase_type::TIME_JUMP,
        phrase_type::LATEST,
        phrase_type::PERCENT,
        phrase_type::MEASURE,
        phrase_type::SCALING,
        phrase_type::SCALING_HIDDEN,
        phrase_type::SCALING_PCT,
        phrase_type::SCALED_MEASURE,
        phrase_type::FORMULA_LINK,
        phrase_type::CALC,
        phrase_type::LAYER,
        phrase_type::OTHER
    );

    /**
     * overwrite the general user type list load function to keep the link to the table type capsuled
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @return bool true if load was successful
     */
    function load(sql_db $db_con, string $db_type = sql_db::TBL_WORD_TYPE): bool
    {
        return parent::load($db_con, $db_type);
    }

    /**
     * adding the word types used for unit tests to the dummy list
     */
    function load_dummy(): void
    {
        $i = 1;
        foreach (self::TYPES as $type_name)
        {
            $type = new type_object($type_name, $type_name, '', $i);
            $this->add($type);
            $i++;
        }
        //parent::load_dummy();
    }

    /**
     * return the database id of the default word type
     */
    function default_id(): int
    {
        return parent::id(phrase_type::NORMAL);
    }

}
