<?php

/*

    model/sandbox/sandbox_value.php - the superclass for handling user specific link objects including the database saving
    -------------------------------

    This superclass should be used by the class word links, formula links and view link


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

namespace cfg;

include_once MODEL_SANDBOX_PATH . 'sandbox.php';
include_once MODEL_GROUP_PATH . 'group.php';

use cfg\db\sql_creator;
use cfg\group\group;
use DateTime;
use Exception;

class sandbox_value extends sandbox
{

    /*
     * object vars
     */

    // database fields only used for the value object
    public group $grp;  // phrases (word or triple) group object for this value
    protected ?float $number; // simply the numeric value
    private ?DateTime $last_update = null; // the time of the last update of fields that may influence the calculated results; also used to detect if the value has been saved


    /*
     * construct and map
     */

    /**
     * all value user specific, that's why the user is always set
     */
    function __construct(user $usr)
    {
        parent::__construct($usr);
        $this->reset();
    }

    function reset(): void
    {
        parent::reset();
        $this->set_number(null);
        $this->set_last_update(null);
    }


    /*
     * set and get
     */

    function set_grp(group $grp): void
    {
        $this->grp = $grp;
    }

    function grp(): group
    {
        return $this->grp;
    }

    /**
     * set the numeric value of the user sandbox object
     *
     * @param float|null $number the numeric value that should be saved in the database
     * @return void
     */
    function set_number(?float $number): void
    {
        $this->number = $number;
    }

    /**
     * @return float|null the numeric value
     */
    function number(): ?float
    {
        return $this->number;
    }

    /**
     * set the timestamp of the last update of this value
     *
     * @param DateTime|null $last_update the timestamp when this value has been updated eiter by the user or a calculatio job
     * @return void
     */
    function set_last_update(?DateTime $last_update): void
    {
        $this->last_update = $last_update;
    }

    /**
     * @return DateTime|null the timestamp when the user has last updated the value
     */
    function last_update(): ?DateTime
    {
        return $this->last_update;
    }


    /*
     * load
     */

    /**
     * load the value parameters for all users
     * @param sql_par|null $qp the query parameter created by the function of the child object e.g. word->load_standard
     * @param string $class the name of the child class from where the call has been triggered
     * @return bool true if the standard object has been loaded
     */
    function load_standard(?sql_par $qp = null, string $class = ''): bool
    {
        global $db_con;

        $db_row = $db_con->get1($qp);
        return $this->row_mapper_sandbox($db_row, true, false);
    }


    /*
     * information
     */

    /**
     * @return bool true if the value has been at least once saved to the database
     */
    function is_saved(): bool
    {
        if ($this->last_update() == null) {
            return false;
        } else {
            return true;
        }
    }


    /*
     * cast
     */

    /**
     * @param object $api_obj frontend API object filled with the database id
     */
    function fill_api_obj(object $api_obj): void
    {
        parent::fill_api_obj($api_obj);

        $api_obj->set_number($this->number);
    }


    /*
     * save
     */

    /**
     * set the log entry parameter for a new value object
     * for all not named objects like links, this function is overwritten
     * e.g. that the user can see "added formula 'scale millions' to word 'mio'"
     */
    function log_add(): change_log_named
    {
        log_debug($this->dsp_id());

        $log = new change_log_named($this->user());
        $log->action = change_log_action::ADD;
        $log->set_table($this->obj_name . sql_db::TABLE_EXTENSION);
        $log->set_field(change_log_field::FLD_NUMERIC_VALUE);
        $log->old_value = '';
        $log->new_value = $this->number;

        $log->row_id = 0;
        $log->add();

        return $log;
    }

    /**
     * set the log entry parameter to delete a object
     * @returns change_log_link with the object presets e.g. th object name
     */
    function log_del(): change_log_named
    {
        log_debug($this->dsp_id());

        $log = new change_log_named($this->user());
        $log->action = change_log_action::DELETE;
        $log->set_table($this->obj_name . sql_db::TABLE_EXTENSION);
        $log->set_field(change_log_field::FLD_NUMERIC_VALUE);
        $log->old_value = $this->number;
        $log->new_value = '';

        $log->row_id = $this->id;
        $log->add();

        return $log;
    }

    /**
     * updated the object id fields (e.g. for a word or formula the name, and for a link the linked ids)
     * should only be called if the user is the owner and nobody has used the display component link
     * @param sql_db $db_con the active database connection
     * @param sandbox $db_rec the database record before the saving
     * @param sandbox $std_rec the database record defined as standard because it is used by most users
     * @returns string either the id of the updated or created source or a message to the user with the reason, why it has failed
     * @throws Exception
     */
    function save_id_fields(sql_db $db_con, sandbox $db_rec, sandbox $std_rec): string
    {

        return 'The user sandbox save_id_fields does not support ' . $this->obj_type . ' for ' . $this->obj_name;
    }


    /**
     * @param sql_creator $sc with the target db_type set
     * @return sql_par the common part for insert and update sql statements
     */
    protected function sql_common(sql_creator $sc): sql_par
    {
        $lib = new library();
        $class = $lib->class_to_name($this::class);
        $ext = $this->grp->table_extension();
        $qp = new sql_par($class . $ext);
        $qp->name = $class . $ext;
        $sc->set_type($class, false, $ext);
        return $qp;
    }

    /**
     * create the sql statement to update a value in the database
     * @param sql_creator $sc with the target db_type set
     * @return sql_par the SQL insert statement, the name of the SQL statement and the parameter list
     */
    function sql_update(
        sql_creator $sc,
        array $fields = [],
        array $values = []
    ): sql_par
    {
        $qp = $this->sql_common($sc);
        $qp->name .= '_update';
        $sc->set_name($qp->name);
        $qp->sql = $sc->sql_update($this->id_field(),  $this->id(), $fields, $values);
        $qp->par = $values;
        return $qp;
    }


    /**
     * actually update a field in the main database record or the user sandbox
     * the usr id is taken into account in sql_db->update (maybe move outside)
     *
     * for values the log should show to the user just which value has been changed
     * but the technical log needs to remember in which actual table the change has been saved
     *
     * @param sql_db $db_con the active database connection that should be used
     * @param change_log_named|change_log_link $log the log object to track the change and allow a rollback
     * @return string an empty string if everything is fine or the message that should be shown to the user
     */
    function save_field_user(
        sql_db $db_con,
        change_log_named|change_log_link $log
    ): string
    {
        $result = '';

        if ($log->new_id > 0) {
            $new_value = $log->new_id;
            $std_value = $log->std_id;
        } else {
            $new_value = $log->new_value;
            $std_value = $log->std_value;
        }
        $ext = $this->grp()->table_extension();
        if ($log->add()) {
            if ($this->can_change()) {
                if ($new_value == $std_value) {
                    if ($this->has_usr_cfg()) {
                        log_debug('remove user change');
                        $db_con->set_type(sql_db::TBL_USER_PREFIX . $this->obj_name . $ext);
                        $db_con->set_usr($this->user()->id);
                        $qp = $this->sql_update($db_con->sql_creator(), array($log->field()), array(null));
                        try {
                            $db_con->exe_par($qp);
                        } catch (Exception $e) {
                            $result = 'remove of ' . $log->field() . ' failed';
                            $trace_link = log_err($result . log::MSG_ERR_USING . $qp->sql . log::MSG_ERR_BECAUSE . $e->getMessage());
                        }
                    }
                    $this->del_usr_cfg_if_not_needed(); // don't care what the result is, because in most cases it is fine to keep the user sandbox row
                } else {
                    $db_con->set_type($this->obj_name . $ext);
                    $db_con->set_usr($this->user()->id);
                    $qp = $this->sql_update($db_con->sql_creator(), array($log->field()), array($new_value));
                    try {
                        $db_con->exe_par($qp);
                    } catch (Exception $e) {
                        $result = 'update of ' . $log->field() . ' to ' . $new_value . ' failed';
                        $trace_link = log_err($result . log::MSG_ERR_USING . $qp->sql . log::MSG_ERR_BECAUSE . $e->getMessage());
                    }
                }
            } else {
                if (!$this->has_usr_cfg()) {
                    if (!$this->add_usr_cfg()) {
                        $result = 'creation of user sandbox for ' . $log->field() . ' failed';
                    }
                }
                if ($result == '') {
                    $db_con->set_type(sql_db::TBL_USER_PREFIX . $this->obj_name . $ext);
                    $db_con->set_usr($this->user()->id);
                    if ($new_value == $std_value) {
                        log_debug('remove user change');
                        $qp = $this->sql_update($db_con->sql_creator(), array($log->field()), array(Null));
                        try {
                            $db_con->exe_par($qp);
                        } catch (Exception $e) {
                            $result = 'remove of user value for ' . $log->field() . ' failed';
                            $trace_link = log_err($result . log::MSG_ERR_USING . $qp->sql . log::MSG_ERR_BECAUSE . $e->getMessage());
                        }
                    } else {
                        $qp = $this->sql_update($db_con->sql_creator(), array($log->field()), array($new_value));
                        try {
                            $db_con->exe_par($qp);
                        } catch (Exception $e) {
                            $result = 'update of user value for ' . $log->field() . ' to ' . $new_value . ' failed';
                            $trace_link = log_err($result . log::MSG_ERR_USING . $qp->sql . log::MSG_ERR_BECAUSE . $e->getMessage());
                        }
                    }
                    $this->del_usr_cfg_if_not_needed(); // don't care what the result is, because in most cases it is fine to keep the user sandbox row
                }
            }
        }
        return $result;
    }


    /*
     * debug
     */

    /**
     * @return string with the best possible identification for this value mainly used for debugging
     */
    function dsp_id(): string
    {
        $result = $this->dsp_id_short();
        $result .= $this->dsp_id_user();
        return $result;
    }

    /**
     * @return string with the short identification for links
     */
    function dsp_id_short(): string
    {
        $result = $this->dsp_id_entry();
        $result .= parent::dsp_id();
        return $result;
    }

    /**
     * @return string with the short identification for lists
     */
    function dsp_id_entry(): string
    {
        $result = '';
        if (isset($this->grp)) {
            $result .= '"' . $this->grp->name() . '" ';
        }
        if ($this->number() != null) {
            $result .= $this->number();
        } else {
            $result .= 'null';
        }
        return $result;
    }

}


