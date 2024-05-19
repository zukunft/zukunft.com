<?php

/*

    model/sandbox/sandbox_description.php - adding the description and type field to the _sandbox superclass
    -------------------------------------

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

    Copyright (c) 1995-2023 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace cfg;

use cfg\db\sql;
use cfg\db\sql_db;
use cfg\db\sql_par;
use cfg\db\sql_par_field_list;
use cfg\db\sql_type;
use cfg\db\sql_type_list;
use cfg\export\sandbox_exp;
use cfg\log\change;

include_once MODEL_SANDBOX_PATH . 'sandbox_link.php';

class sandbox_link_named extends sandbox_link
{
    // the word, triple, verb oder formula description that is shown as a mouseover explain to the user
    // if description is NULL the database value should not be updated
    // or for triples the description that may differ from the generic created text
    // e.g. Zurich AG instead of Zurich (Company)
    // if the description is empty the generic created name is used
    protected ?string $name = '';   // simply the object name, which cannot be empty if it is a named object
    public ?string $description = null;

    function reset(): void
    {
        parent::reset();
        $this->description = null;
    }

    /**
     * map the database fields to the object fields
     * to be extended by the child object
     *
     * @param array|null $db_row with the data directly from the database
     * @param bool $load_std true if only the standard user sandbox object ist loaded
     * @param bool $allow_usr_protect false for using the standard protection settings for the default object used for all users
     * @param string $id_fld the name of the id field as set in the child class
     * @param string $name_fld the name of the name field as set in the child class
     * @return bool true if the word is loaded and valid
     */
    function row_mapper_sandbox(
        ?array $db_row,
        bool   $load_std = false,
        bool   $allow_usr_protect = true,
        string $id_fld = '',
        string $name_fld = ''
    ): bool
    {
        $result = parent::row_mapper_sandbox($db_row, $load_std, $allow_usr_protect, $id_fld);
        if ($result) {
            if (array_key_exists($name_fld, $db_row)) {
                if ($db_row[$name_fld] != null) {
                    $this->set_name($db_row[$name_fld]);
                }
            }
            if (array_key_exists(sandbox_named::FLD_DESCRIPTION, $db_row)) {
                $this->description = $db_row[sandbox_named::FLD_DESCRIPTION];
            }
        }
        return $result;
    }


    /*
     * set and get
     */

    /**
     * set the name of this named user sandbox link object
     * set and get of the name is needed to use the same function for phrase or term
     *
     * @param string $name the name of this named user sandbox object e.g. word set in the related object
     * @return void
     */
    function set_name(string $name): void
    {
        $this->name = $name;
    }

    /**
     * get the name of the word object
     *
     * @return string the name from the object e.g. word using the same function as the phrase and term
     */
    function name(): string
    {
        return $this->name;
    }

    /**
     * dummy function that should always be overwritten by the child object
     * @return string
     */
    function name_field(): string
    {
        log_err('function name_field() missing in class ' . $this::class);
        return '';
    }

    /**
     * create a clone and update the name (mainly used for unit testing)
     * but keep the is a unique db id
     *
     * @param string $name the target name
     * @return $this a clone with the name changed
     */
    function cloned(string $name): sandbox_link_named
    {
        $obj_cpy = $this->clone_reset();
        $obj_cpy->set_id($this->id());
        $obj_cpy->set_fob($this->fob());
        $obj_cpy->set_tob($this->tob());
        $obj_cpy->set_name($name);
        return $obj_cpy;
    }


    /*
     * im- and export
     */

    /**
     * import the name and description of a sandbox link object
     *
     * @param array $in_ex_json an array with the data of the json object
     * @param object|null $test_obj if not null the unit test object to get a dummy seq id
     * @return user_message the status of the import and if needed the error messages that should be shown to the user
     */
    function import_obj(array $in_ex_json, object $test_obj = null): user_message
    {
        $result = parent::import_obj($in_ex_json, $test_obj);

        // reset of object not needed, because the calling function has just created the object
        foreach ($in_ex_json as $key => $value) {
            if ($key == sandbox_exp::FLD_NAME) {
                $this->set_name($value);
            }
            if ($key == sandbox_exp::FLD_DESCRIPTION) {
                $this->description = $value;
            }
        }

        return $result;
    }


    /*
     * information
     */

    /**
     * check if the named object in the database needs to be updated
     *
     * @param sandbox_link_named $db_obj the word as saved in the database
     * @return bool true if this word has infos that should be saved in the datanase
     */
    function needs_db_update_named(sandbox_link_named $db_obj): bool
    {
        $result = parent::needs_db_update_linked($db_obj);
        if ($this->name != null) {
            if ($this->name != $db_obj->name) {
                $result = true;
            }
        }
        if ($this->description != null) {
            if ($this->description != $db_obj->description) {
                $result = true;
            }
        }
        return $result;
    }


    /*
     * save function
     */

    /**
     * set the update parameters for the link object description
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @param sandbox_link_named $db_rec the database record before the saving
     * @param sandbox_link_named $std_rec the database record defined as standard because it is used by most users
     * @return string if not empty the message that should be shown to the user
     */
    function save_field_description(sql_db $db_con, sandbox_link_named $db_rec, sandbox_link_named $std_rec): string
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
                $log->set_field(sandbox_named::FLD_DESCRIPTION);
                $result = $this->save_field_user($db_con, $log);
            }
        }
        return $result;
    }


    /*
     * sql write
     */

    /**
     * create the sql statement to add a new named sandbox object e.g. word to the database
     * TODO add qp merge
     *
     * @param sql $sc with the target db_type set
     * @param sql_par $qp
     * @param sql_par_field_list $fvt_lst list of field names, values and sql types additional to the standard id and name fields
     * @param string $id_fld_new
     * @param sql_type_list $sc_par_lst_sub the parameters for the sql statement creation
     * @return sql_par the SQL insert statement, the name of the SQL statement and the parameter list
     */
    function sql_insert_key_field(
        sql                $sc,
        sql_par            $qp,
        sql_par_field_list $fvt_lst,
        string             $id_fld_new,
        sql_type_list      $sc_par_lst_sub = new sql_type_list([])
    ): sql_par
    {
        // set some var names to shorten the code lines
        $usr_tbl = $sc_par_lst_sub->is_usr_tbl();
        $ext = sql::file_sep . sql::file_insert;

        // list of parameters actually used in order of the function usage
        $sql = '';

        $qp_lnk = parent::sql_insert_key_field($sc, $qp, $fvt_lst, $id_fld_new, $sc_par_lst_sub);

        // create the sql to insert the row
        $fvt_insert = $fvt_lst->get($this->name_field());
        $fvt_insert_list = new sql_par_field_list();
        $fvt_insert_list->add($fvt_insert);
        $sc_insert = clone $sc;
        $qp_insert = $this->sql_common($sc_insert, $sc_par_lst_sub, $ext);;
        $sc_par_lst_sub->add(sql_type::SELECT_FOR_INSERT);
        if ($sc->db_type == sql_db::MYSQL) {
            $sc_par_lst_sub->add(sql_type::NO_ID_RETURN);
        }
        $qp_insert->sql = $sc_insert->create_sql_insert(
            $fvt_insert_list, $sc_par_lst_sub, true, '', '', '', $id_fld_new);
        $qp_insert->par = [$fvt_insert->value];

        // add the insert row to the function body
        //$sql .= ' ' . $qp_insert->sql . '; ';

        // get the new row id for MySQL db
        if ($sc->db_type == sql_db::MYSQL and !$usr_tbl) {
            $sql .= ' ' . sql::LAST_ID_MYSQL . $sc->var_name_row_id($sc_par_lst_sub) . '; ';
        }

        $qp->sql = $qp_lnk->sql . ' ' . $sql;
        $qp->par_fld_lst = $qp_lnk->par_fld_lst;
        $qp->par_fld = $fvt_insert;

        return $qp;
    }


    /*
     * sql write fields
     */

    /**
     * get a list of all database fields that might be changed of the named link object
     * excluding the internal fields e.g. the database id
     * field list must be corresponding to the db_fields_changed fields
     *
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return array list of all database field names that have been updated
     */
    function db_fields_all(sql_type_list $sc_par_lst = new sql_type_list([])): array
    {
        return array_merge(
            parent::db_all_fields_link($sc_par_lst),
            [$this->name_field(),
                sandbox_named::FLD_DESCRIPTION
            ]);
    }

    /**
     * get a list of database field names, values and types that have been updated
     * of the object to combine the list with the list of the child object e.g. word
     *
     * @param sandbox|sandbox_link_named $sbx the same named sandbox as this to compare which fields have been changed
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par_field_list with the field names of the object and any child object
     */
    function db_fields_changed(
        sandbox|sandbox_link_named $sbx,
        sql_type_list $sc_par_lst = new sql_type_list([])
    ): sql_par_field_list
    {
        global $change_field_list;

        $sc = new sql();
        $do_log = $sc_par_lst->and_log();
        $table_id = $sc->table_id($this::class);

        $lst = parent::db_fields_changed($sbx, $sc_par_lst);
        // for insert statements of user sandbox rows user id fields always needs to be included
        $lst->add_name_and_description($this, $sbx, $do_log, $table_id);
        return $lst;
    }

}