<?php

/*

    model/log/change_field_list.php - the const for the change log field table
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

namespace cfg\log;

use cfg\db\sql_db;
use cfg\type_list;
use cfg\type_object;

include_once DB_PATH . 'sql_db.php';
include_once MODEL_LOG_PATH . 'change_table.php';
include_once MODEL_LOG_PATH . 'change_table_list.php';
include_once MODEL_LOG_PATH . 'change_field.php';
include_once MODEL_LOG_PATH . 'change_field_list.php';

class change_field_list extends type_list
{

    const TN_WORD_VIEW = "values";
    const TI_WORD_USER_ID = 9;
    const TI_WORD_NAME = 10;
    const TI_WORD_DESCRIPTION = 11;

    /*
     * database link
     */

    // the field names in the log are for the current version the same as the field names of the single objects
    // the field names are listed here again, so that the log can include all changes even if the field name has changed
    // *_NAME is the name as used in the program or as it has been used in a previous program version
    // *_NAME_DSP is the description that should be shown to the user
    const FLD_TABLE = 'table_id';
    // TODO add the user_id to the field list because the owner can change and this should be included in the log
    const FLD_USER_ID = 'user_id';
    const FLD_USER_ID_DSP = 'user';
    const FLD_WORD_NAME = 'word_name';
    const FLD_WORD_NAME_DSP = 'name';
    const FLD_WORD_DESCRIPTION = 'description';
    const FLD_WORD_VIEW = 'view_id';
    const FLD_WORD_PLURAL = 'plural';
    const FLD_PHRASE_TYPE = 'phrase_type_id';
    const FLD_VERB_NAME = 'verb_name';
    const FLD_TRIPLE_NAME = 'triple_name';
    const FLD_GIVEN_NAME = 'name_given';
    const FLD_TRIPLE_VIEW = 'view_id';
    const FLD_NUMERIC_VALUE = 'numeric_value';
    const FLD_VALUE_GROUP = 'group_id';
    const FLD_FORMULA_NAME = 'formula_name';
    const FLD_FORMULA_USR_TEXT = 'resolved_text';
    const FLD_FORMULA_REF_TEXT = 'formula_text';
    const FLD_FORMULA_TYPE = 'formula_type_id';
    const FLD_FORMULA_ALL = 'all_values_needed';
    const FLD_SOURCE_NAME = 'source_name';
    const FLD_SOURCE_URL = 'url';
    const FLD_VIEW_NAME = 'view_name';
    const FLD_COMPONENT_NAME = 'component_name';
    const FLD_COMPONENT_TYPE = 'component_type_id';
    const FLD_TABLE_FIELD = 'table_field_name';


    /**
     * adding the system log stati used for unit tests to the dummy list
     */
    function load_dummy(): void
    {
        global $change_table_list;

        parent::load_dummy();
        $table_id = $change_table_list->id(change_table_list::WORD);
        $table_field_name = $table_id . change_field_list::FLD_USER_ID;
        $type = new type_object(
            $table_field_name,
            change_field_list::FLD_USER_ID,
            change_field_list::FLD_USER_ID_DSP,
            change_field_list::TI_WORD_USER_ID);
        $this->add($type);
        $table_field_name = $table_id . change_field_list::FLD_WORD_NAME;
        $type = new type_object(
            $table_field_name,
            change_field_list::FLD_WORD_NAME,
            change_field_list::FLD_WORD_NAME_DSP,
            change_field_list::TI_WORD_NAME);
        $this->add($type);
        $table_field_name = $table_id . change_field_list::FLD_WORD_DESCRIPTION;
        $type = new type_object(
            $table_field_name,
            change_field_list::FLD_WORD_DESCRIPTION,
            change_field_list::FLD_WORD_DESCRIPTION,
            change_field_list::TI_WORD_DESCRIPTION);
        $this->add($type);
    }

    /**
     * return the database id of the default log type
     */
    function default_id(): int
    {
        global $change_table_list;
        $table_id = $change_table_list->id(change_table_list::WORD);
        $table_field_name = $table_id . change_field_list::FLD_WORD_NAME;
        return parent::id($table_field_name);
    }

}