<?php

/*

    model/log/change_log.php - read the changes from the database and forward them to the API
    ------------------------

    for writing the user change to the database the classes model/user/user_log* are used

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

class change_log_list extends base_list
{


    // TODO setup global field list
    // TODO add unit test for SQL
    // TODO add db read test
    // TODO add cast
    // TODO add JSON export test
    // TODO add API controller
    // TODO add API test
    // TODO add table view
    // TODO add table view unit test
    // TODO add table view db read test
    public function load_sql_dsp_of_phr(sql_db $db_con, phrase $phr): sql_par
    {
        global $usr;
        global $change_log_tables;
        global $change_log_fields;

        $table_id = $change_log_tables->id(change_log_table::WORD);
        $table_field_name = $table_id . change_log_field::FLD_WORD_NAME;
        if (!$phr->is_word()) {
            $table_id = $change_log_tables->id(change_log_table::TRIPLE);
            $table_field_name = $table_id . change_log_field::FLD_TRIPLE_NAME;
        }
        // get the phrase view field
        $field_id = $change_log_fields->id($table_field_name);
        $log_named = new user_log_named();
        $log_named->usr = $usr;
        $qp = $log_named->load_sql($db_con, 'dsp_of_phr');
        $db_con->set_page();
        $db_con->add_par(sql_db::PAR_INT, $field_id);
        $qp->sql = $db_con->select_by_field(user_log_named::FLD_FIELD_ID);
        $qp->par = $db_con->get_par();
        return $qp;
    }

}