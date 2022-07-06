<?php

/*

  user_sandbox_description.php - adding the description and type field to the user_sandbox superclass
  ----------------------------

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


class user_sandbox_link_description extends user_sandbox_link
{
    // the word description that is shown as a mouseover explain to the user
    // if description is NULL the database value should not be updated
    // or for triples the description that may differ from the generic created text
    // e.g. Zurich AG instead of Zurich (Company)
    // if the description is empty the generic created name is used
    public ?string $description = null;

    function reset()
    {
        parent::reset();
        $this->description = null;
    }

    /**
     * set the update parameters for the word description
     */
    function save_field_description(sql_db $db_con, user_sandbox $db_rec, user_sandbox $std_rec): string
    {
        $result = '';
        // if the description is not set, don't overwrite any db entry
        if ($this->description <> Null) {
            if ($this->description <> $db_rec->description) {
                $log = $this->log_upd();
                $log->old_value = $db_rec->description;
                $log->new_value = $this->description;
                $log->std_value = $std_rec->description;
                $log->row_id = $this->id;
                $log->field = sql_db::FLD_DESCRIPTION;
                $result = $this->save_field_do($db_con, $log);
            }
        }
        return $result;
    }

}