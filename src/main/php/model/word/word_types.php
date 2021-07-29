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
    const TEST_TYPE = DBL_VIEW_TYPE_DEFAULT;
}

/**
 * reload the global word_types array from the database e.g. because a translation has changed
 */
function init_word_types($db_con)
{
    global $word_types;
    global $word_types_hash;

    $db_con->set_type(DB_TYPE_VIEW_TYPE);
    $db_con->set_fields(array('description', 'code_id'));
    $sql = $db_con->select();
    $db_lst = $db_con->get($sql);
    $word_types = array();
    $word_types_hash = array();
    foreach ($db_lst as $db_entry) {
        $wrd_type = new view_types();
        $wrd_type->name = $db_entry['word_type_name'];
        $wrd_type->comment = $db_entry['description'];
        $wrd_type->code_id = $db_entry['code_id'];
        $word_types[$db_entry['word_type_id']] = $wrd_type;
        $word_types_hash[$db_entry['code_id']] = $db_entry['word_type_id'];
    }

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
    $wrd_type = new view_types();
    $wrd_type->name = view_types::TEST_NAME;
    $wrd_type->code_id = view_types::TEST_TYPE;
    $word_types[1] = $wrd_type;
    $word_types_hash[view_types::TEST_TYPE] = 1;

}
