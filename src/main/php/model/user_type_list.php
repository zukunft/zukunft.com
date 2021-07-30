<?php

/*

  user_type_list.php - the superclass for word, formula and view type lists
  ------------------


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

class user_type_list
{

    private array $type_hash = [];

    /**
     * force the types to reload the names and translations from the database
     */
    function load_types(string $db_type, sql_db $db_con): array
    {
        $type_list = [];
        $db_con->set_type($db_type);
        $db_con->set_fields(array(sql_db::FLD_DESCRIPTION, sql_db::FLD_CODE_ID));
        $sql = $db_con->select();
        $db_lst = $db_con->get($sql);
        if ($db_lst != null) {
            foreach ($db_lst as $db_entry) {
                $type_obj = new user_type();
                $type_obj->name = $db_entry[sql_db::FLD_TYPE_NAME];
                $type_obj->comment = $db_entry[sql_db::FLD_DESCRIPTION];
                $type_obj->code_id = $db_entry[sql_db::FLD_CODE_ID];
                $type_list[$db_entry[$db_con->get_id_field_name($db_type)]] = $type_obj;
            }
        }
        return $type_list;
    }

    function get_hash(array $type_list): array
    {
        $type_hash = [];
        if ($type_list != null) {
            foreach ($type_list as $key => $type) {
                $type_hash[$type->code_id] = $key;
            }
        }
        return $type_hash;
    }
}