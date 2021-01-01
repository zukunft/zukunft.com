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

class user_list {

  public $usr_lst = array();  // the list of users
    
  // return a list of all users that have done at least one modification compared to the standard
  function load_active ($debug) {
    zu_debug('user_list->load_active', $debug-10);
    
    // add a dummy user to calculate the standard results within the same loop
    $usr = New user;
    $usr->dummy_all($debug-1);
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
    $db_con = New mysql;
    $db_con->usr_id = $usr->id;         
    $db_usr_lst = $db_con->get($sql, $debug-5);  

    foreach ($db_usr_lst AS $db_usr) {
      $usr = New user;
      $usr->id          = $db_usr['user_id'];
      $usr->name        = $db_usr['user_name'];
      $usr->code_id     = $db_usr['code_id'];
      $this->usr_lst[] = $usr;
    }

    zu_debug('user_list->load_active -> ('.count($this->usr_lst).')', $debug-5);
    return $this->usr_lst;
  }

  function name ($debug) {
    $result = implode(",",$this->names($debug-1));
    return $result;
  }
  
  function names ($debug) {
    $result = array();
    foreach ($this->usr_lst AS $usr) {
      $result[] = $usr->name;
    }
    return $result;
  }
  
}

?>
