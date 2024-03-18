<?php

/*

    model/formula/element_type.php - enum to link the class string to a db id for a formula element
    --------------------------------------

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

namespace cfg;

include_once MODEL_SYSTEM_PATH . 'BasicEnum.php';

class parameter_type extends BasicEnum
{
    // the database id for a formula element (or parameter) type
    const WORD_ID = 1;
    const VERB_ID = 2;
    const FORMULA_ID = 3;
    const TRIPLE_ID = 4;

    // the allowed objects types for a formula element
    // use the class name for the formula element object
    const WORD_CLASS = word::class;        // a word is used for an AND selection of values
    const TRIPLE_CLASS = triple::class;    // a triple is used for an AND selection of values
    const VERB_CLASS = verb::class;        // a verb is used for dynamic usage of linked words for an AND selection
    const FORMULA_CLASS = formula::class;  // a formula is used to include formula results of another formula

    protected static function get_description($value): string
    {
        $result = 'formula element type "' . $value . '" not yet defined';

        switch ($value) {

            // system log
            case parameter_type::WORD_ID:
                $result = 'a reference to a simple word';
                break;
            case parameter_type::VERB_ID:
                $result = 'a reference to predicate';
                break;
            case parameter_type::FORMULA_ID:
                $result = 'a reference to another formula';
                break;
            case parameter_type::TRIPLE_ID:
                $result = 'a reference to word link';
                break;
        }

        return $result;
    }

    function db_id(string $class): int
    {
        $result = 0;
        return match ($class) {
            self::WORD_CLASS => self::WORD_ID,
            self::TRIPLE_CLASS => self::TRIPLE_ID,
            self::FORMULA_CLASS => self::FORMULA_ID,
            self::VERB_CLASS => self::VERB_ID,
        };
    }

    function class_name(int $id): string
    {
        $result = '';
        return match ($id) {
            self::WORD_ID => self::WORD_CLASS,
            self::TRIPLE_ID => self::TRIPLE_CLASS,
            self::FORMULA_ID => self::FORMULA_CLASS,
            self::VERB_ID => self::VERB_CLASS,
        };
    }
}