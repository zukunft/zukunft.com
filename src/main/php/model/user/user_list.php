<?php

/*

    model/user/user_list.php - a list of users
    ------------------------

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
include_once DB_PATH . 'sql_par.php';

global $system_users;

class user_list
{

    public ?array $lst = null;  // the list of users
    public array $code_id_hash = [];

    // fill the user objects of the list based on a sql
    // TODO review
    private function load_sql($sql, sql_db $db_con): void
    {

        $db_usr_lst = $db_con->get_old($sql);

        if ($db_usr_lst != null) {
            foreach ($db_usr_lst as $db_usr) {
                $usr = new user;
                $usr->set_id($db_usr[user::FLD_ID]);
                $usr->name = $db_usr[user::FLD_NAME];
                $usr->code_id = $db_usr[sql_db::FLD_CODE_ID];
                $this->lst[] = $usr;
            }
        }
    }

    // return a list of all users that have done at least one modification compared to the standard
    function load_active(): array
    {
        log_debug('user_list->load_active');
        $lib = new library();

        global $db_con;

        // add a dummy user to calculate the standard results within the same loop
        $usr = new user;
        $usr->dummy_all();
        $this->lst[] = $usr;

        $sql = "SELECT u.user_id, u.user_name, u.code_id 
              FROM users u,
                  ( SELECT user_id 
                      FROM ( SELECT user_id 
                               FROM user_formulas
                           GROUP BY user_id 
                       UNION SELECT user_id 
                               FROM user_words
                           GROUP BY user_id 
                       UNION SELECT user_id 
                               FROM user_values
                           GROUP BY user_id ) AS cp
                  GROUP BY user_id ) AS c
            WHERE u.user_id = c.user_id
         ORDER BY u.user_id;";
        // TODO check if the user needs to be set to the original value again
        $db_con->usr_id = $usr->id();
        $this->load_sql($sql, $db_con);

        log_debug($lib->dsp_count($this->lst));
        return $this->lst;
    }

    /**
     * load all system users that have a code id
     */
    function load_system(sql_db $db_con): void
    {
        global $system_users;

        $sql = "SELECT u.user_id, u.user_name, u.code_id 
              FROM users u
            WHERE u.code_id IS NOT NULL
         ORDER BY u.user_id;";
        $this->load_sql($sql, $db_con);
        $this->set_hash();
        $system_users = clone $this;
    }

    /**
     * add an usr with just the id for later mass load
     */
    function add_by_id($usr_id): void
    {
        $usr = new user;
        $usr->set_id($usr_id);
        $this->lst[] = $usr;
    }

    // fill the user objects of the list based on the id
    function load_by_id(): void
    {

        global $db_con;
        $lib = new library();

        $sql_in = $lib->sql_array($this->lst, 'user_id IN(', ')');
        $sql = "SELECT user_id, user_name, code_id 
              FROM users
            WHERE " . $sql_in . "
         ORDER BY user_id;";
        $this->load_sql($sql, $db_con);
    }

    function name_lst(): string
    {
        $lib = new library();
        return $lib->dsp_array($this->names());
    }

    function names(): array
    {
        $result = array();
        foreach ($this->lst as $usr) {
            $result[] = $usr->name;
        }
        return $result;
    }

    /**
     * fill the hash based on the code id
     */
    function set_hash(): void
    {
        $this->code_id_hash = [];
        if ($this->lst != null) {
            foreach ($this->lst as $key => $usr) {
                $this->code_id_hash[$usr->code_id] = $key;
            }
        }
    }

    /**
     * return the database row id based on the code_id
     *
     * @param string $code_id
     * @return int
     */
    function id(string $code_id): int
    {
        $result = 0;
        if ($code_id != '' and $code_id != null) {
            if (array_key_exists($code_id, $this->code_id_hash)) {
                $result = $this->code_id_hash[$code_id];
            } else {
                $lib = new library();
                log_err('User id not found for ' . $code_id . ' in ' . $lib->dsp_array($this->code_id_hash));
            }
        } else {
            log_debug('Type code id not not set');
        }
        return $result;
    }

    /**
     * create dummy system user list for the unit tests without database connection
     */
    function load_dummy(): void
    {
        $this->lst = array();
        $this->code_id_hash = array();
        $type = new user();
        $type->name = user::SYSTEM_NAME;
        $type->code_id = user::SYSTEM_CODE_ID;
        $this->lst[1] = $type;
        $this->code_id_hash[user::SYSTEM_NAME] = 1;
        $type = new user();
        $type->name = user::SYSTEM_TEST_NAME;
        $type->code_id = user::SYSTEM_TEST_PROFILE_CODE_ID;
        $this->lst[2] = $type;
        $this->code_id_hash[user::SYSTEM_TEST_PROFILE_CODE_ID] = 2;

    }

}