<?php

/*

    model/log/change_table_list.php - to link coded functionality to a log log table
    -------------------------------


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

    Copyright (c) 1995-2025 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

namespace cfg\log;

include_once MODEL_HELPER_PATH . 'type_list.php';
include_once MODEL_HELPER_PATH . 'type_object.php';
include_once MODEL_LOG_PATH . 'change_table.php';
include_once MODEL_LOG_PATH . 'change_table_list.php';
include_once SHARED_ENUM_PATH . 'change_tables.php';

use cfg\helper\type_list;
use shared\enum\change_tables;

class change_table_list extends type_list
{

    // list of all log tables allowed in this program version
    const TABLE_LIST = array(
        change_tables::USER,
        change_tables::WORD,
        change_tables::WORD_USR,
        change_tables::VERB,
        change_tables::TRIPLE,
        change_tables::TRIPLE_USR,
        change_tables::VALUE,
        change_tables::VALUE_USR,
        change_tables::VALUE_LINK,
        change_tables::FORMULA,
        change_tables::FORMULA_USR,
        change_tables::FORMULA_LINK,
        change_tables::FORMULA_LINK_USR,
        change_tables::VIEW,
        change_tables::VIEW_USR,
        change_tables::VIEW_LINK,
        change_tables::VIEW_LINK_USR,
        change_tables::VIEW_COMPONENT,
        change_tables::VIEW_COMPONENT_USR,
        change_tables::REF,
        change_tables::REF_USR,
        change_tables::SOURCE,
        change_tables::SOURCE_USR,
    );

    /**
     * adding the system log stati used for unit tests to the dummy list
     */
    function load_dummy(): void
    {
        parent::load_dummy();
        // read the corresponding names and description from the internal config csv files
        $this->read_from_config_csv($this);
    }

    /**
     * return the database id of the default log type
     */
    function default_id(): int
    {
        return parent::id(change_tables::VALUE);
    }

}