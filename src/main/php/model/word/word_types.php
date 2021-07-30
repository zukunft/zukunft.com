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

$word_types = [];
$word_types_hash = [];

class word_types extends user_type
{
    // persevered view name for unit and integration tests
    const TEST_NAME = 'System Test word Type';
    const TEST_TYPE = DBL_WORD_TYPE_NORMAL;
}

/**
 * reload the global word_types array from the database e.g. because a translation has changed
 */
function init_word_types($db_con)
{
    global $word_types;
    global $word_types_hash;

    $result = false;
    $typ_lst = new user_type_list();
    $word_types = $typ_lst->load_types(DB_TYPE_WORD_TYPE, $db_con);
    $word_types_hash = $typ_lst->get_hash($word_types);
    if (count($word_types_hash) > 0) {
        $result = true;
    }
    return $result;

}

/**
 * create word type array for the unit tests without database connection
 */
function unit_text_init_word_types()
{
    global $word_types;
    global $word_types_hash;

    $word_types = array();
    $word_types_hash = array();
    $wrd_type = new word_types();
    $wrd_type->name = word_types::TEST_NAME;
    $wrd_type->code_id = word_types::TEST_TYPE;
    $word_types[1] = $wrd_type;
    $word_types_hash[word_types::TEST_TYPE] = 1;

}
