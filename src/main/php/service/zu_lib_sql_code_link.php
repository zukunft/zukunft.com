<?php

/*

  zu_lib_sql_code_link.php - SQL code link 
  ________________________

  to link a database row with the program code with the field "code_id"
  e.g. the verb "alias" is used to combine "mio" with "millions"
  this module contains all const and the related functions where a row in the database is link to a special behaviour
  the const is the code_id that is also shown to to user if he/she wants to change the name

  functions
  ---------
  
  sql_code_link - get the id for a predefined item
  cl            - shortcut for sql_Code_Link
  
  Const
  -----
  
  DBL - DataBase Link: link to the database over the special field code_id

  TODO load all code links once at program start, because the ID will never change within the same instance

  TODO use cases:
    these the optimal tax rates are
        from -10% needed to fulfill the basic needed
        to 99% for everything more than the community is able to invest to save one live
        reason: this is the optimal combination between safety and prestige

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

// TODO check automatically that the code links are unique
/**
 * returns the pk / row_id for a given code_id
 * if the code_id does not exist the missing record is created
 * the code_id is always saved in the 20 char long field code_id
 * the short form is the cl function without description and db_con as parameter
 *
 * @param $code_id
 * @param $description
 * @param $db_con
 * @return int|mixed
 */
function sql_code_link($code_id, $description, $db_con)
{
    log_debug("sql_code_link (" . $code_id . "," . $description . ")");

    global $word_types_hash;

    $row_id = 0;

    // set the table name and the id field
    $table_name = '';
    $db_type = '';

    if ($code_id == "dummy") {
        $table_name = "user";
        $db_type = "user";
    }

    /*  if ($code_id == EVENT_TYPE_TRADE_MISSING
     OR $code_id == EVENT_TYPE_SQL_ERROR_ID
     OR $code_id == EVENT_TYPE_SYSTEM_EVENT
     OR $code_id == EVENT_TYPE_USER_DAILY
     OR $code_id == EVENT_TYPE_EXPOSURE_LIMIT) {
      $table_name = "event_types";
      $id_field   = "event_type_id";
    } */

    if ($table_name == '' and $db_type == '') {
        log_debug('table name for code_id ' . $code_id . ' (' . $db_type . ') not found <br>');
    } else {
        // get the preloaded types directly from the hash
        if ($db_type == DB_TYPE_WORD_TYPE) {
            $row_id = $word_types_hash[$code_id];
        } else {
            //$db_con = new mysql;
            // remember the db_type
            $db_value_type = $db_con->get_type();
            $db_con->usr_id = SYSTEM_USER_ID;
            $db_con->set_type($db_type);

            // get the row_id
            $row_id = $db_con->get_id_from_code($code_id);

            // insert the missing row if needed
            if ($row_id <= 0) {
                if ($db_type == 'view') {
                    $db_con->insert(array(sql_db::FLD_CODE_ID, user::FLD_ID), array($code_id, SYSTEM_USER_ID));
                } else {
                    // TODO for sys_log_type include the name db field
                    $db_con->insert(sql_db::FLD_CODE_ID, $code_id);
                }
                log_debug('inserted ' . $code_id . '<br>');
                // get the id of the inserted row
                $row_id = $db_con->get_id_from_code($code_id);
                log_debug('inserted ' . $code_id . ' as ' . $row_id . '<br>');
            } else {
                log_debug('found ' . $code_id . ' as ' . $row_id . '<br>');
            }

            // set the name as default
            if ($row_id > 0 and $description <> '') {
                $row_name = $db_con->get_name($row_id);
                if ($row_name == '') {
                    log_debug('add ' . $description . '<br>');
                    $db_con->update_name($row_id, $description);
                }
            }
            // restore the db_type
            $db_con->set_type($db_value_type);
        }
    }

    log_debug("sql_code_link ... done");

    return $row_id;
}
