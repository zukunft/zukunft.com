<?php

/*

    model/log/change_log_field.php - the const for the change log field table
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
include_once MODEL_LOG_PATH . 'change_log_table.php';
include_once MODEL_LOG_PATH . 'change_log_field.php';

use model\sql_db;
use cfg\type_list;
use cfg\type_object;

class change_log_field extends type_list
{

    const TN_WORD_VIEW = "values";

    /*
     * database link
     */

    // the field names in the log are for the current version the same as the field names of the single objects
    // the field names are listed here again, so that the log can include all changes even if the field name has changed
    // *_NAME is the name as used in the program or as it has been used in a previous program version
    // *_NAME_DSP is the description that should be shown to the user
    const FLD_TABLE = 'table_id';
    const FLD_WORD_NAME = 'word_name';
    const FLD_WORD_NAME_DSP = 'name';
    const FLD_WORD_VIEW = 'view_id';
    const FLD_WORD_PLURAL = 'plural';
    const FLD_WORD_TYPE = 'word_type_id';
    const FLD_VERB_NAME = 'verb_name';
    const FLD_TRIPLE_NAME = 'triple_name';
    const FLD_GIVEN_NAME = 'name_given';
    const FLD_TRIPLE_VIEW = 'view_id';
    const FLD_VALUE_NUMBER = 'word_value';
    const FLD_VALUE_GROUP = 'phrase_group_id';
    const FLD_FORMULA_NAME = 'formula_name';
    const FLD_FORMULA_USR_TEXT = 'resolved_text';
    const FLD_FORMULA_REF_TEXT = 'formula_text';
    const FLD_FORMULA_TYPE = 'formula_type_id';
    const FLD_FORMULA_ALL = 'all_values_needed';
    const FLD_SOURCE_NAME = 'source_name';
    const FLD_SOURCE_URL = 'url';
    const FLD_VIEW_NAME = 'view_name';
    const FLD_VIEW_CMP_NAME = 'component_name';
    const FLD_VIEW_CMP_TYPE = 'component_type_id';
    const FLD_TABLE_FIELD = 'table_field_name';

    /**
     * overwrite the general user type list load function to keep the link to the field type capsuled
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @return bool true if load was successful
     */
    function load(sql_db $db_con, string $db_type = sql_db::VT_TABLE_FIELD): bool
    {
        return parent::load($db_con, $db_type);
    }

    /**
     * adding the system log stati used for unit tests to the dummy list
     */
    function load_dummy(): void
    {
        global $change_log_tables;

        parent::load_dummy();
        $table_id = $change_log_tables->id(change_log_table::WORD);
        $table_field_name = $table_id . change_log_field::FLD_WORD_NAME;
        $type = new type_object(
            $table_field_name,
            change_log_field::FLD_WORD_NAME,
            change_log_field::FLD_WORD_NAME_DSP,
            9);
        $this->add($type);
    }

    /**
     * return the database id of the default log type
     */
    function default_id(): int
    {
        global $change_log_tables;
        $table_id = $change_log_tables->id(change_log_table::WORD);
        $table_field_name = $table_id . change_log_field::FLD_WORD_NAME;
        return parent::id($table_field_name);
    }

}