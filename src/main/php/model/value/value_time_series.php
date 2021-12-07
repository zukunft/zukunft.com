<?php

/*

  value_time_series.php - the header object for time series values
  --------------------
  
  To save time and space values that have a timestamp are saved in a separate table

  TODO add function that decides if the user values should saved in a complete new time series or if overwrites should be saved
  
  
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

class value_time_series extends user_sandbox_display
{

    // database fields additional to the user sandbox fields for the value object
    public ?int $source_id = null;        // the id of source where the value is coming from
    public ?int $grp_id = null;           // id of the group of phrases that are linked to this value for fast selections
    public ?DateTime $last_update = null; // the time of the last update of fields that may influence the calculated results

    // in memory only fields

    function __construct()
    {
        $this->obj_type = user_sandbox::TYPE_VALUE;
        $this->obj_name = 'value_time_series';

        $this->rename_can_switch = UI_CAN_CHANGE_VALUE;
    }

    /*
    database load functions that reads the object from the database
    */

    private function row_mapper(array $db_row, bool $map_usr_fields = false)
    {
        if ($db_row != null) {
            if ($db_row['value_time_series_id'] > 0) {
                $this->id = $db_row['value_time_series_id'];
                $this->source_id = $db_row['source_id'];
                $this->grp_id = $db_row[phrase_group::FLD_ID];
                $this->owner_id = $db_row[self::FLD_USER];
                $this->last_update = $this->get_datetime($db_row['last_update'], $this->dsp_id());
                $this->excluded = $db_row[self::FLD_EXCLUDED];
                if ($map_usr_fields) {
                    $this->usr_cfg_id = $db_row['user_value_time_series_id'];
                    $this->share_id = $db_row[sql_db::FLD_SHARE];
                    $this->protection_id = $db_row[sql_db::FLD_PROTECT];
                } else {
                    $this->share_id = cl(db_cl::SHARE_TYPE, share_type_list::DBL_PUBLIC);
                    $this->protection_id = cl(db_cl::PROTECTION_TYPE, protection_type_list::DBL_NO);
                }
            } else {
                $this->id = 0;
            }
        } else {
            $this->id = 0;
        }
    }

    // load the standard value use by most users
    function load_standard(): bool
    {

        global $db_con;
        $result = false;

        if ($this->id > 0) {
            //$db_con = new mysql;
            $db_con->usr_id = $this->usr->id;
            $db_con->set_type(DB_TYPE_VALUE_TIME_SERIES);
            $sql = 'SELECT v.value_time_series_id,
                     v.user_id,
                     v.source_id,
                     v.phrase_group_id,
                     v.last_update,
                     v.excluded,
                     v.share_type_id,
                     v.protection_type_id
                FROM value_time_series v 
               WHERE v.value_time_series_id = ' . $this->id . ';';
            $db_val = $db_con->get1($sql);
            $this->row_mapper($db_val);
            $result = $this->load_owner();
        }
        return $result;
    }

    // load the record from the database
    // in a separate function, because this can be called twice from the load function
    function load_rec($sql_where)
    {
        global $db_con;
        //$db_con = new mysql;
        $db_con->usr_id = $this->usr->id;
        // TODO not value_time_series ???
        $db_con->set_type(DB_TYPE_VALUE_TIME_SERIES);
        if (SQL_DB_TYPE == sql_db::POSTGRES) {
            $sql = "SELECT 
                v.value_time_series_id,
                u.value_time_series_id AS user_value_time_series_id,
                v.user_id,
                v.phrase_group_id,
                u.share_type_id,
                CASE WHEN (u.source_id          <> '' IS NOT TRUE) THEN v.source_id          ELSE u.source_id          END AS source_id,
                CASE WHEN (u.last_update        <> '' IS NOT TRUE) THEN v.last_update        ELSE u.last_update        END AS last_update,
                CASE WHEN (u.excluded           <> '' IS NOT TRUE) THEN v.excluded           ELSE u.excluded           END AS excluded,
                CASE WHEN (u.protection_type_id <> '' IS NOT TRUE) THEN v.protection_type_id ELSE u.protection_type_id END AS protection_type_id
           FROM value_time_series v 
      LEFT JOIN user_values u ON u.value_time_series_id = v.value_time_series_id 
                             AND u.user_id = " . $this->usr->id . " 
          WHERE " . $sql_where . ";";
        } else {
            $sql = 'SELECT 
                v.value_time_series_id,
                u.value_time_series_id AS user_value_time_series_id,
                v.user_id,
                v.phrase_group_id,
                v.time_word_id,
                u.share_type_id,
                IF(u.source_id          IS NULL, v.source_id,          u.source_id)          AS source_id,
                IF(u.last_update        IS NULL, v.last_update,        u.last_update)        AS last_update,
                IF(u.excluded           IS NULL, v.excluded,           u.excluded)           AS excluded,
                IF(u.protection_type_id IS NULL, v.protection_type_id, u.protection_type_id) AS protection_type_id
           FROM `value_time_series` v 
      LEFT JOIN user_values u ON u.value_time_series_id = v.value_time_series_id 
                             AND u.user_id = ' . $this->usr->id . ' 
          WHERE ' . $sql_where . ';';
        }
        log_debug('value_time_series->load_rec -> sql "' . $sql . '"');
        $db_val = $db_con->get1($sql);
        $this->row_mapper($db_val, true);
        if ($this->id > 0) {
            log_debug('value_time_series->load_rec -> got ' . $this->number . ' with id ' . $this->id);
        }
    }

    // insert or update a number in the database or save a user specific number
    function save(): string
    {
        log_debug('value->save "' . $this->number . '" for user ' . $this->usr->name);

        global $db_con;
        $result = '';

        // build the database object because the is anyway needed
        $db_con->set_usr($this->usr->id);
        $db_con->set_type(DB_TYPE_VALUE_TIME_SERIES);

        // rebuild the value ids if needed e.g. if the front end function has just set a list of phrase ids get the responding group
        $result .= $this->set_grp_and_time_by_ids();

        // check if a new value is supposed to be added
        if ($this->id <= 0) {
            log_debug('value->save check if a value for "' . $this->name() . '" and user ' . $this->usr->name . ' is already in the database');
            // check if a value for this words is already in the database
            $db_chk = new value;
            $db_chk->grp_id = $this->grp_id;
            $db_chk->time_id = $this->time_id;
            $db_chk->time_stamp = $this->time_stamp;
            $db_chk->usr = $this->usr;
            $db_chk->load();
            if ($db_chk->id > 0) {
                log_debug('value->save value for "' . $this->grp->name() . '"@"' . $this->time_phr->name . '" and user ' . $this->usr->name . ' is already in the database and will be updated');
                $this->id = $db_chk->id;
            }
        }

        if ($this->id <= 0) {
            log_debug('value->save "' . $this->name() . '": ' . $this->number . ' for user ' . $this->usr->name . ' as a new value');

            $result .= $this->add($db_con);
        } else {
            log_debug('value->save update id ' . $this->id . ' to save "' . $this->number . '" for user ' . $this->usr->id);
            // update a value
            // TODO: if no one else has ever changed the value, change to default value, else create a user overwrite

            // read the database value to be able to check if something has been changed
            // done first, because it needs to be done for user and general values
            $db_rec = new value;
            $db_rec->id = $this->id;
            $db_rec->usr = $this->usr;
            $db_rec->load();
            log_debug("value->save -> old database value loaded (" . $db_rec->number . ") with group " . $db_rec->grp_id . ".");
            $std_rec = new value;
            $std_rec->id = $this->id;
            $std_rec->usr = $this->usr; // must also be set to allow to take the ownership
            $std_rec->load_standard();
            log_debug("value->save -> standard value settings loaded (" . $std_rec->number . ")");

            // for a correct user value detection (function can_change) set the owner even if the value has not been loaded before the save
            if ($this->owner_id <= 0) {
                $this->owner_id = $std_rec->owner_id;
            }

            // check if the id parameters are supposed to be changed
            if ($result == '') {
                $result = $this->save_id_if_updated($db_con, $db_rec, $std_rec);
            }

            // if a problem has appeared up to here, don't try to save the values
            // the problem is shown to the user by the calling interactive script
            if ($result == '') {
                // if the user is the owner and no other user has adjusted the value, really delete the value in the database
                $result = $this->save_fields($db_con, $db_rec, $std_rec);
            }

        }

        if ($result != '') {
            log_err($result);
        }

        return $result;
    }

}

?>
