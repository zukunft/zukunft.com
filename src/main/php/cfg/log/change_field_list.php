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

use cfg\const\paths;

include_once paths::MODEL_HELPER . 'type_object.php';
include_once paths::MODEL_HELPER . 'type_list.php';
include_once paths::DB . 'sql_db.php';
include_once paths::MODEL_LOG . 'change_table.php';
include_once paths::MODEL_LOG . 'change_table_list.php';
include_once paths::MODEL_LOG . 'change_field.php';
include_once paths::MODEL_LOG . 'change_field_list.php';
include_once paths::SHARED_ENUM . 'change_tables.php';
include_once paths::SHARED_ENUM . 'change_fields.php';

use cfg\helper\type_list;
use cfg\helper\type_object;
use shared\enum\change_tables;
use shared\enum\change_fields;

class change_field_list extends type_list
{

    const TI_WORD_NAME = 10;


    /**
     * adding the system log statuus used for unit tests to the dummy list
     * the field name starts always with the table id to make the field name unique
     * the table id is remove as one of the last steps if the real table field name is requested
     */
    function load_dummy(): void
    {
        global $cng_tbl_cac;

        parent::load_dummy();

        // read the corresponding names and description from the internal config csv files
        $this->read_from_config_csv($this);
        // TODO Prio 3 load from csv
        $table_id = $cng_tbl_cac->id(change_tables::WORD);
        $table_field_name = $table_id . change_fields::FLD_WORD_NAME;
        $type = new type_object(
            $table_field_name,
            change_fields::FLD_WORD_NAME,
            change_fields::FLD_WORD_NAME_DSP,
            change_field_list::TI_WORD_NAME);
        $this->add($type);

    }

    /**
     * return the database id of the default log type
     */
    function default_id(): int
    {
        global $cng_tbl_cac;
        $table_id = $cng_tbl_cac->id(change_tables::WORD);
        $table_field_name = $table_id . change_fields::FLD_WORD_NAME;
        return parent::id($table_field_name);
    }

}