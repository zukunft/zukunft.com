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
  along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.
  
  To contact the authors write to:
  Timon Zielonka <timon@zukunft.com>
  
  Copyright (c) 1995-2021 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

global $system_views;

class view_list extends user_type_list
{

    public ?user $usr = null;   // the user object of the person for whom the verb list is loaded, so to say the viewer

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
        $db_con->set_usr($this->usr->id);
        $db_con->set_fields(array('code_id'));
        $db_con->set_usr_fields(array('comment'));
        $db_con->set_usr_num_fields(array('view_type_id', user_sandbox::FLD_EXCLUDED));
        $db_con->set_where_text('code_id IS NOT NULL');
        $sql = $db_con->select();
        $db_lst = $db_con->get($sql);
        if ($db_lst != null) {
            foreach ($db_lst as $db_row) {
                $vrb = new view();
                $vrb->row_mapper($db_row, true);
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
    function load(sql_db $db_con, string $db_type = DB_TYPE_VIEW): bool
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
    function load_dummy() {
        parent::load_dummy();
        $dsp = new view();
        $dsp->name = view::WORD;
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

