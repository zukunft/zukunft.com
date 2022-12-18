<?php

/*

  user_sandbox_description.php - adding the description field to the user_sandbox superclass
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


class user_sandbox_named_with_type extends user_sandbox_named
{

    // database id of the type used for named user sandbox objects with predefined functionality
    // such as words, formulas, values, terms and view component links
    // because all types are preloaded with the database id the name and code id can fast be received
    // the id of the source type, view type, view component type or word type
    // e.g. to classify measure words
    // the id of the source type, view type, view component type or word type e.g. to classify measure words
    // or the formula type to link special behavior to special formulas like "this" or "next"
    public ?int $type_id = null;

    /*
     * set and get
     */

    /**
     * set the database id of the type
     *
     * @param int|null $type_id the database id of the type
     * @return void
     */
    function set_type_id(?int $type_id): void
    {
        $this->type_id = $type_id;
    }

    /**
     * @return int|null the database id of the type
     */
    function type_id(): ?int
    {
        return $this->type_id;
    }

    /**
     * dummy function that should be overwritten by the child object
     * @return string the name of the object type
     */
    public function type_name(): string
    {
        $msg = 'ERROR: the type name function should have been overwritten by the child object';
        return log_err($msg);
    }

    /*
     * casting objects
     */

    /**
     * @param object $api_obj frontend API objects that should be filled with unique object name
     */
    function fill_api_obj(object $api_obj): void
    {
        parent::fill_api_obj($api_obj);

        $api_obj->set_type_id($this->type_id());
    }

    /**
     * @param object $dsp_obj frontend API objects that should be filled with unique object name
     */
    function fill_dsp_obj(object $dsp_obj): void
    {
        parent::fill_api_obj($dsp_obj);

        $dsp_obj->set_type_id($this->type_id());
    }

    /*
     * write to database
     */

    /**
     * set the update parameters for the word, triple or formula type
     * TODO: log the ref
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @param user_sandbox_named_with_type $db_rec the database record before the saving
     * @param user_sandbox_named_with_type $std_rec the database record defined as standard because it is used by most users
     * @return string if not empty the message that should be shown to the user
     */
    function save_field_type(
        sql_db $db_con,
        user_sandbox_named_with_type $db_rec,
        user_sandbox_named_with_type $std_rec
    ): string
    {
        $result = '';
        if ($db_rec->type_id <> $this->type_id) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->type_name();
            $log->old_id = $db_rec->type_id;
            $log->new_value = $this->type_name();
            $log->new_id = $this->type_id;
            $log->std_value = $std_rec->type_name();
            $log->std_id = $std_rec->type_id;
            $log->row_id = $this->id;
            $log->field = $this->obj_name . sql_db::FLD_EXT_TYPE_ID;
            $result .= $this->save_field_do($db_con, $log);
            log_debug('changed type to "' . $log->new_value . '" (from ' . $log->new_id . ')');
        }
        return $result;
    }

    /**
     * save all updated source fields excluding the name, because already done when adding a source
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @param source $db_rec the database record before the saving
     * @param source $std_rec the database record defined as standard because it is used by most users
     * @return string if not empty the message that should be shown to the user
     */
    function save_fields_typed(sql_db $db_con, user_sandbox_named_with_type $db_rec, user_sandbox_named_with_type $std_rec): string
    {
        $result = parent::save_fields_named($db_con, $db_rec, $std_rec);
        $result .= $this->save_field_type($db_con, $db_rec, $std_rec);
        return $result;
    }

}