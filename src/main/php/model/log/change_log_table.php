<?php

/*

  model/log/change_log_table.php - to link coded functionality to a log log table
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
  
  Copyright (c) 1995-2022 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

namespace model;

include_once DB_PATH . 'sql_db.php';
include_once MODEL_HELPER_PATH . 'type_list.php';
include_once MODEL_HELPER_PATH . 'type_object.php';
include_once MODEL_LOG_PATH . 'change_log_table.php';

use cfg\type_list;
use cfg\type_object;

global $change_log_tables;

class change_log_table extends type_list
{
    // list of the log table with linked functionalities
    // unlike the table const in sql_db this contains also table names of previous versions
    // and an assignment of the deprecated tables names to the table names of this version
    // this list contains only tables where a direct change by the user is possible
    // so the phrase, term and figure tables are not included here, but in the sql_db list
    const USER = 'users';
    const WORD = 'words';
    const WORD_USR = 'user_words';
    const VERB = 'verbs';
    const TRIPLE = 'triples';
    const TRIPLE_USR = 'user_triples';
    const VALUE = 'values';
    const VALUE_USR = 'user_values';
    const VALUE_LINK = 'value_links';
    const VALUE_PHRASE_LINK = 'value_phrase_links';
    const FORMULA = 'formulas';
    const FORMULA_USR = 'user_formulas';
    const FORMULA_LINK = 'formula_links';
    const FORMULA_LINK_USR = 'user_formula_links';
    const VIEW = 'views';
    const VIEW_USR = 'user_views';
    const VIEW_TERM_LINK = 'view_term_links';
    //const VIEW_TERM_LINK_USR = 'user_view_term_links';
    const VIEW_COMPONENT = 'components';
    const VIEW_COMPONENT_USR = 'user_components';
    const VIEW_LINK = 'component_links';
    const VIEW_LINK_USR = 'user_component_links';
    const REF = 'refs';
    const REF_USR = 'user_refs';
    const SOURCE = 'sources';
    const SOURCE_USR = 'user_sources';

    // list of all log tables allowed in this program version
    const TABLE_LIST = array(
        self::USER,
        self::WORD,
        self::WORD_USR,
        self::VERB,
        self::TRIPLE,
        self::TRIPLE_USR,
        self::VALUE,
        self::VALUE_USR,
        self::VALUE_LINK,
        self::FORMULA,
        self::FORMULA_USR,
        self::FORMULA_LINK,
        self::FORMULA_LINK_USR,
        self::VIEW,
        self::VIEW_USR,
        self::VIEW_LINK,
        self::VIEW_LINK_USR,
        self::VIEW_COMPONENT,
        self::VIEW_COMPONENT_USR,
        self::REF,
        self::REF_USR,
        self::SOURCE,
        self::SOURCE_USR,
    );

    /**
     * overwrite the general user type list load function to keep the link to the table type capsuled
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @return bool true if load was successful
     */
    function load(sql_db $db_con, string $db_type = sql_db::TBL_CHANGE_TABLE): bool
    {
        return parent::load($db_con, $db_type);
    }

    /**
     * adding the system log stati used for unit tests to the dummy list
     */
    function load_dummy(): void
    {
        parent::load_dummy();
        $type = new type_object(change_log_table::VALUE, change_log_table::VALUE, '', 2);
        $this->add($type);
        $type = new type_object(change_log_table::USER, change_log_table::USER, '', 3);
        $this->add($type);
        $type = new type_object(change_log_table::WORD, change_log_table::WORD, '', 5);
        $this->add($type);
    }

    /**
     * return the database id of the default log type
     */
    function default_id(): int
    {
        return parent::id(change_log_table::VALUE);
    }

}