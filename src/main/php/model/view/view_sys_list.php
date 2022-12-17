<?php

/*

    view_list.php - list of predefined system views
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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/agpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

global $system_views;

class view_sys_list extends user_type_list
{

    public user $usr;   // the user object of the person for whom the verb list is loaded, so to say the viewer

    function __construct(user $usr)
    {
        $this->set_user($usr);
    }

    /*
     * get and set
     */

    /**
     * set the user of the phrase list
     *
     * @param user $usr the person who wants to access the phrases
     * @return void
     */
    function set_user(user $usr): void
    {
        $this->usr = $usr;
    }

    /**
     * @return user the person who wants to see the phrases
     */
    function user(): user
    {
        return $this->usr;
    }

    /*
     * loading functions
     */

    /**
     * force to reload the list of views from the database that have a used code id
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @param string $db_type the database name in this case view just for compatibility reasons
     * @return array the list of views used by the system
     */
    private function load_list(sql_db $db_con, string $db_type): array
    {
        $this->lst = [];
        $db_con->set_type($db_type);
        $db_con->set_usr($this->user()->id);
        $db_con->set_fields(view::FLD_NAMES);
        $db_con->set_usr_fields(view::FLD_NAMES_USR);
        $db_con->set_usr_num_fields(view::FLD_NAMES_NUM_USR);
        $db_con->set_where_text('code_id IS NOT NULL');
        $sql = $db_con->select_by_set_id();
        $db_lst = $db_con->get_old($sql);
        if ($db_lst != null) {
            foreach ($db_lst as $db_row) {
                $vrb = new view($this->usr);
                $vrb->row_mapper($db_row);
                $this->lst[$db_row[$db_con->get_id_field_name($db_type)]] = $vrb;
            }
        }
        return $this->lst;
    }

    /**
     * overwrite the general user sys list load function to keep the link to the table sys capsuled
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @return bool true if load was successful
     */
    function load(sql_db $db_con, string $db_type = sql_db::TBL_VIEW): bool
    {
        $result = false;
        $this->lst = $this->load_list($db_con, $db_type);
        $this->hash = $this->get_hash($this->lst);
        if (count($this->hash) > 0) {
            $result = true;
        }
        return $result;
    }

    /**
     * adding the system views used for unit tests to the dummy list
     */
    function load_dummy(): void {
        parent::load_dummy();
        $dsp = new view($this->usr);
        $dsp->set_name(view::WORD);
        $dsp->code_id = view::WORD;
        $this->lst[2] = $dsp;
        $this->hash[view::WORD] = 2;
    }

    /**
     * return the database id of the default view sys
     */
    function default_id(): int
    {
        return parent::id(view::WORD);
    }

}
