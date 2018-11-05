<?php

/*

  zu_lib_config.php - functions to handle the database based system configuration
  __________

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
  
  Copyright (c) 1995-2018 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com

*/

// get a config value from the database table
function cfg_get($code_id, $usr, $debug) {
  zu_debug('cfg_get for "'.$code_id.'".', $debug-12);
  $result = '';
  
  $sql = "SELECT `value` 
            FROM `config` 
           WHERE `code_id` = ".sf($code_id).";";
  $db_con = new mysql;         
  $db_con->usr_id = $usr->id;         
  $db_row = $db_con->get1($sql, $debug-5);  
  $result = $db_row['value'];

  return $result;
}


?>
