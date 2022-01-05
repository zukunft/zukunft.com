<?php

/*

  value_time_series.php - the header object for time series values
  --------------------
  
  To save values that have a timestamp more efficient in a separate table

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

    /*
     * database link
     */

    // object specific database and JSON object field names
    const FLD_ID = 'value_time_series_id';
    const FLD_LAST_UPDATE = 'last_update';

    // all database field names excluding the id and excluding the user specific fields
    const FLD_NAMES = array(
        phrase_group::FLD_ID
    );

    // list of the user specific numeric database field names
    const FLD_NAMES_NUM_USR = array(
        source::FLD_ID,
        self::FLD_EXCLUDED,
        user_sandbox::FLD_PROTECT
    );

    // list of field names that are only on the user sandbox row
    // e.g. the standard value does not need the share type, because it is by definition public (even if share types within a group of users needs to be defined, the value for the user group are also user sandbox table)
    const FLD_NAMES_USR_ONLY = array(
        user_sandbox::FLD_SHARE
    );

    /*
     * object vars
     */

    // related objects used also for database mapping
    public phrase_group $grp;  // phrases (word or triple) group object for this value
    public ?source $source;    // the source object

    // database fields additional to the user sandbox fields for the value object
    public DateTime $last_update; // the time of the last update of fields that may influence the calculated results

    /*
     * construct and map
     */

    /**
     * set the user sandbox type for a value time series object and set the user, which is needed in all cases
     * @param user $usr the user who requested to see this value
     */
    function __construct(user $usr)
    {
        parent::__construct($usr);
        $this->obj_type = user_sandbox::TYPE_VALUE;
        $this->obj_name = DB_TYPE_VALUE_TIME_SERIES;

        $this->rename_can_switch = UI_CAN_CHANGE_VALUE;

        $this->reset($usr);
    }

    function reset()
    {
        parent::reset();

        $this->grp = new phrase_group($this->usr);
        $this->source = null;

        $this->last_update = new DateTime();
    }

    /*
     * database load functions that reads the object from the database
     */

    /**
     * map the database fields to the object fields
     *
     * @param array $db_row with the data directly from the database
     * @param bool $map_usr_fields false for using the standard protection settings for the default value time series used for all users
     * @param string $id_fld the name of the id field as defined in this child and given to the parent
     * @return bool true if the value time series is loaded and valid
     */
    function row_mapper(array $db_row, bool $map_usr_fields = true, string $id_fld = self::FLD_ID): bool
    {
        $result = parent::row_mapper($db_row, $map_usr_fields, self::FLD_ID);
        if ($result) {
            $this->grp->id = $db_row[phrase_group::FLD_ID];
            if ($db_row[source::FLD_ID] > 0) {
                $this->source = new source($this->usr);
                $this->source->id = $db_row[source::FLD_ID];
            }
            $this->last_update = $this->get_datetime($db_row[self::FLD_LAST_UPDATE], $this->dsp_id());
        }
        return $result;
    }

    /**
     * create the SQL to load the default time series always by the id
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_standard_sql(sql_db $db_con, string $class = ''): sql_par
    {
        $db_con->set_type(DB_TYPE_VALUE_TIME_SERIES);
        $db_con->set_fields(array_merge(self::FLD_NAMES, self::FLD_NAMES_NUM_USR, array(sql_db::FLD_USER_ID)));

        return parent::load_standard_sql($db_con, self::class);
    }

    /**
     * load the standard value use by most users
     * @param sql_par|null $qp placeholder to align the function parameters with the parent
     * @param string $class the name of this class to be delivered to the parent function
     * @return bool true if a time series has been loaded
     */
    function load_standard(?sql_par $qp = null, string $class = self::class): bool
    {
        global $db_con;
        $qp = $this->load_standard_sql($db_con);
        return parent::load_standard($qp, self::class);
    }

    /**
     * create the SQL to load user specific time series values
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql_db $db_con, string $class = ''): sql_par
    {
        $qp = new sql_par(self::class);
        $sql_where = '';

        $db_con->set_type(DB_TYPE_VALUE_TIME_SERIES);

        if ($this->id > 0) {
            $qp->name .= 'id';
            $sql_where = $db_con->where_id(self::FLD_ID, $this->id, true);
        } elseif ($this->grp->id > 0) {
            $qp->name .= 'phrase_group_id';
            $sql_where = $db_con->where_par(array(phrase_group::FLD_ID), array($this->grp->id), true);
        } else {
            log_err('At least the id or phrase group must be set to load a time series values', self::class . '->load_sql');
        }

        if ($sql_where != '') {
            $db_con->set_name($qp->name);
            $db_con->set_usr($this->usr->id);
            $db_con->set_fields(self::FLD_NAMES);
            $db_con->set_usr_num_fields(self::FLD_NAMES_NUM_USR);
            $db_con->set_usr_only_fields(self::FLD_NAMES_USR_ONLY);
            $db_con->set_where_text($sql_where);
            $qp->sql = $db_con->select_by_id();
            $qp->par = $db_con->get_par();
        }

        return $qp;
    }

    /**
     * load the record from the database
     * in a separate function, because this can be called twice from the load function
     *
     * TODO load the related time series data
     */
    function load(): bool
    {
        global $db_con;
        $result = true;

        // check the all minimal input parameters
        if (!isset($this->usr)) {
            log_err('The user must be set to load a time series for a user', self::class . '->load');
        } else {
            log_debug(self::class . '->load');

            $qp = $this->load_sql($db_con);
            $db_val = $db_con->get1($qp);
            $result = $this->row_mapper($db_val);
        }

        return $result;
    }

    /**
     * add a new time series
     * @return user_message with status ok
     *                      or if something went wrong
     *                      the message that should be shown to the user
     *                      including suggested solutions
     */
    function add(): user_message
    {
        log_debug(self::class . '->add');

        global $db_con;
        $result = new user_message();

        // log the insert attempt first
        $log = $this->log_add();
        if ($log->id > 0) {
            $db_con->set_type(DB_TYPE_VALUE_TIME_SERIES);
            $this->id = $db_con->insert(
                array(phrase_group::FLD_ID, self::FLD_USER, self::FLD_LAST_UPDATE),
                array($this->grp->id, $this->usr->id, "Now()"));
            if ($this->id > 0) {
                // update the reference in the log
                if (!$log->add_ref($this->id)) {
                    $result->add_message('adding the value time series reference in the system log failed');
                }

                // update the phrase links for fast searching
                /*
                $upd_result = $this->upd_phr_links();
                if ($upd_result != '') {
                    $result->add_message('Adding the phrase links of the value time series failed because ' . $upd_result);
                    $this->id = 0;
                }
                */

                // create an empty db_rec element to force saving of all set fields
                //$db_vts = new value_time_series($this->usr);
                //$db_vts->id = $this->id;
                // TODO add the data list saving
            }
        }

        return $result;
    }

    /**
     * insert or update a time series in the database or save user specific time series numbers
     */
    function save(): string
    {
        log_debug(self::class . '->save');

        global $db_con;
        $result = '';

        // build the database object because the is anyway needed
        $db_con->set_type(DB_TYPE_VALUE_TIME_SERIES);
        $db_con->set_usr($this->usr->id);

        // check if a new time series is supposed to be added
        if ($this->id <= 0) {
            // check if a time series for the phrase group is already in the database
            $db_chk = new value_time_series($this->usr);
            $db_chk->grp = $this->grp;
            $db_chk->load();
            if ($db_chk->id > 0) {
                $this->id = $db_chk->id;
            }
        }

        if ($this->id <= 0) {
            $result .= $this->add()->get_last_message();
        } else {
            // update a value
            // TODO: if no one else has ever changed the value, change to default value, else create a user overwrite

            // read the database value to be able to check if something has been changed
            // done first, because it needs to be done for user and general values
            $db_rec = new value_time_series($this->usr);
            $db_rec->id = $this->id;
            $db_rec->load();
            $std_rec = new value_time_series($this->usr); // user must also be set to allow to take the ownership
            $std_rec->id = $this->id;
            $std_rec->load_standard();

            // for a correct user value detection (function can_change) set the owner even if the value has not been loaded before the save
            if ($this->owner_id <= 0) {
                $this->owner_id = $std_rec->owner_id;
            }

            // check if the id parameters are supposed to be changed
            $result = $this->save_id_if_updated($db_con, $db_rec, $std_rec);

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