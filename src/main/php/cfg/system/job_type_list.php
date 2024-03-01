<?php

/*

    model/system/job_type_list.php - list of predefined system batch jobs
    ------------------------------------

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

global $job_types;

class job_type_list extends type_list
{
    // list of the predefined system batch jobs
    const VALUE_UPDATE = "value_update";
    const VALUE_ADD = "value_add";
    const VALUE_DEL = "value_del";
    const FORMULA_UPDATE = "formula_update";
    const FORMULA_ADD = "formula_add";
    const FORMULA_DEL = "formula_del";
    const FORMULA_LINK = "formula_link";
    const FORMULA_UNLINK = "formula_unlink";
    const TRIPLE = "triple";
    const WORD_UNLINK = "word_unlink";
    const BASE_IMPORT = "base_import"; // import the base configuration by a system user on initial setup

    /**
     * overwrite the general user type list load function to keep the link to the table type capsuled
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @return bool true if load was successful
     */
    function load(sql_db $db_con, string $db_type = sql_db::TBL_TASK_TYPE): bool
    {
        return parent::load($db_con, $db_type);
    }

    /**
     * adding the job type used for unit tests to a dummy list
     */
    function load_dummy(): void
    {
        parent::load_dummy();
        $type = new type_object(job_type_list::VALUE_UPDATE, job_type_list::VALUE_UPDATE, '', 2);
        $this->add($type);
        $type = new type_object(job_type_list::BASE_IMPORT, job_type_list::BASE_IMPORT, '', 11);
        $this->add($type);
    }

    /**
     * return the database id of the default job type
     */
    function default_id(): int
    {
        return parent::id(job_type_list::VALUE_UPDATE);
    }

}