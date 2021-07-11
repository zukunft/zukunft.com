<?php

/*

  user.php - a person who uses zukunft.com
  --------
  
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

class user_list
{

    public ?array $usr_lst = null;  // the list of users

    // fill the user objects of the list based on an sql
    private function load_sql($sql)
    {

        global $db_con;

        $db_usr_lst = $db_con->get($sql);

        if ($db_usr_lst != null) {
            foreach ($db_usr_lst as $db_usr) {
                $usr = new user;
                $usr->id = $db_usr['user_id'];
                $usr->name = $db_usr['user_name'];
                $usr->code_id = $db_usr['code_id'];
                $this->usr_lst[] = $usr;
            }
        }
    }

    // return a list of all users that have done at least one modification compared to the standard
    function load_active(): array
    {
        log_debug('user_list->load_active');

        global $db_con;

        // add a dummy user to calculate the standard results within the same loop
        $usr = new user;
        $usr->dummy_all();
        $this->usr_lst[] = $usr;

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
        // todo check if the user needs to be set to the original value again
        $db_con->usr_id = $usr->id;
        $this->load_sql($sql);

        log_debug('user_list->load_active -> (' . count($this->usr_lst) . ')');
        return $this->usr_lst;
    }

    // add a usr with just the id for later mass load
    function add_by_id($usr_id)
    {
        $usr = new user;
        $usr->id = $usr_id;
    }

    // fill the user objects of the list based on the id
    function load_by_id()
    {

        $sql = "SELECT user_id, user_name, code_id 
              FROM users
            WHERE user_id IN(" . implode(",", $this->usr_lst) . ")
         ORDER BY user_id;";
        $this->load_sql($sql);
    }

    function name(): string
    {
        return implode(",", $this->names());
    }

    function names(): array
    {
        $result = array();
        foreach ($this->usr_lst as $usr) {
            $result[] = $usr->name;
        }
        return $result;
    }

}