<?php

/*

    model/word/phrase_types.php - to link coded functionality to a word or a triple, which means to every phrase
    -----------------------------

    TODO rename to phrase type

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

namespace cfg;

use cfg\db\sql_db;

include_once DB_PATH . 'sql_db.php';
include_once MODEL_HELPER_PATH . 'type_list.php';
include_once MODEL_PHRASE_PATH . 'phrase_type.php';

class phrase_types extends type_list
{

    // the phrase types used for unit testing
    // TODO sync this list with the csv list and write a update process for the prod database
    const TYPES = array(
        phrase_type::NORMAL,
        phrase_type::TIME,
        phrase_type::MEASURE,
        phrase_type::TIME_JUMP,
        phrase_type::CALC,
        phrase_type::PERCENT,
        phrase_type::SCALING,
        phrase_type::SCALING_HIDDEN,
        phrase_type::LAYER,
        phrase_type::FORMULA_LINK,
        phrase_type::OTHER,
        phrase_type::THIS,
        phrase_type::NEXT,
        phrase_type::PRIOR,
        phrase_type::SCALING_PCT,
        phrase_type::SCALED_MEASURE,
        phrase_type::MATH_CONST,
        phrase_type::MEASURE_DIVISOR,
        phrase_type::LATEST,
        phrase_type::KEY,
        phrase_type::INFO,
        phrase_type::TRIPLE_HIDDEN,
        phrase_type::SYSTEM_HIDDEN,
        phrase_type::GROUP,
        phrase_type::SYMBOL,
        phrase_type::RANK,
        phrase_type::IGNORE
    );

    /*
     * construct and map
     */

    /**
     * @param bool $usr_can_add true by default to allow seariching by name for new added phrase types
     */
    function __construct(bool $usr_can_add = true)
    {
        parent::__construct($usr_can_add);
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
     * @return int the database id of the default word type
     */
    function default_id(): int
    {
        return parent::id(phrase_type::NORMAL);
    }

}
