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

use cfg\db\sql_db;
use cfg\export\sandbox_exp;

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

}