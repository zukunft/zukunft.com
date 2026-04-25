<?php

/*

    model/phrase/phrase_types.php - to link coded functionality to a word or a triple, which means to every phrase
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

namespace Zukunft\ZukunftCom\main\php\cfg\phrase;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_HELPER . 'type_object.php';
include_once paths::DB . 'sql_db.php';
include_once paths::MODEL_HELPER . 'type_list.php';
include_once paths::MODEL_PHRASE . 'phrase_type.php';
include_once paths::SHARED_TYPES . 'phrase_types.php';

use Zukunft\ZukunftCom\main\php\cfg\helper\type_list;
use Zukunft\ZukunftCom\main\php\cfg\helper\type_object;
use Zukunft\ZukunftCom\main\php\shared\types\phrase_types as phrase_type_shared;

class phrase_types extends type_list
{

    // the phrase types used for unit testing
    // TODO sync this list with the csv list and write a update process for the prod database
    const array TYPES = array(
        phrase_type_shared::NORMAL_NAME => phrase_type_shared::NORMAL,
        phrase_type_shared::TIME_NAME => phrase_type_shared::TIME,
        phrase_type_shared::MEASURE_NAME => phrase_type_shared::MEASURE,
        phrase_type_shared::TIME_JUMP_NAME => phrase_type_shared::TIME_JUMP,
        phrase_type_shared::CALC_NAME => phrase_type_shared::CALC,
        phrase_type_shared::PERCENT_NAME => phrase_type_shared::PERCENT,
        phrase_type_shared::SCALING_NAME => phrase_type_shared::SCALING,
        phrase_type_shared::SCALING_HIDDEN_NAME => phrase_type_shared::SCALING_HIDDEN,
        phrase_type_shared::LAYER_NAME => phrase_type_shared::LAYER,
        phrase_type_shared::FORMULA_LINK_NAME => phrase_type_shared::FORMULA_LINK,
        phrase_type_shared::OTHER_NAME => phrase_type_shared::OTHER,
        phrase_type_shared::THIS_NAME => phrase_type_shared::THIS,
        phrase_type_shared::NEXT_NAME => phrase_type_shared::NEXT,
        phrase_type_shared::PRIOR_NAME => phrase_type_shared::PRIOR,
        phrase_type_shared::SCALING_PCT_NAME => phrase_type_shared::SCALING_PCT,
        phrase_type_shared::SCALED_MEASURE_NAME => phrase_type_shared::SCALED_MEASURE,
        phrase_type_shared::MATH_CONST_NAME => phrase_type_shared::MATH_CONST,
        phrase_type_shared::MEASURE_DIVISOR_NAME => phrase_type_shared::MEASURE_DIVISOR,
        phrase_type_shared::LATEST_NAME => phrase_type_shared::LATEST,
        phrase_type_shared::KEY_NAME => phrase_type_shared::KEY,
        phrase_type_shared::INFO_NAME => phrase_type_shared::INFO,
        phrase_type_shared::TRIPLE_HIDDEN_NAME => phrase_type_shared::TRIPLE_HIDDEN,
        phrase_type_shared::SYSTEM_HIDDEN_NAME => phrase_type_shared::SYSTEM_HIDDEN,
        phrase_type_shared::GROUP_NAME => phrase_type_shared::GROUP,
        phrase_type_shared::SYMBOL_NAME => phrase_type_shared::SYMBOL,
        phrase_type_shared::RANK_NAME => phrase_type_shared::RANK,
        phrase_type_shared::IGNORE_NAME => phrase_type_shared::IGNORE,
        phrase_type_shared::TIME_NUMBER_NAME => phrase_type_shared::TIME_NUMBER,
    );

    /*
     * construct and map
     */

    /**
     * @param bool $usr_can_add true by default to allow searching by name for new added phrase types
     */
    function __construct(bool $usr_can_add = true)
    {
        parent::__construct($usr_can_add);
    }

    /**
     * adding the word types used for unit tests to the dummy list
     * TODO Prio 3: load from csv but make sure that for all csv entry an code const exists
     */
    function load_dummy(): void
    {
        $i = 1;
        foreach (self::TYPES as $type_name => $type_code_id) {
            $type = new type_object($type_code_id, $type_name, '', $i);
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
        return parent::id(phrase_type_shared::NORMAL);
    }

}
