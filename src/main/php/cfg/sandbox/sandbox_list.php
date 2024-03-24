<?php

/*

    model/sandbox/sandbox_list.php - a base object for a list of user sandbox objects
    ------------------------------


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

use cfg\db\sql;
use cfg\db\sql_db;
use cfg\db\sql_par;
use cfg\db\sql_par_type;
use cfg\db\sql_type;
use cfg\result\result_list;
use cfg\value\value_list;

include_once MODEL_SYSTEM_PATH . 'base_list.php';

class sandbox_list extends base_list
{

    /*
     *  object vars
     */

    private user $usr; // the person for whom the list has been created


    /*
     * construct and map
     */

    /**
     * always set the user because a link list is always user specific
     * @param user $usr the user who requested to see e.g. the formula links
     */
    function __construct(user $usr, array $lst = array())
    {
        parent::__construct($lst);
        $this->set_user($usr);
    }

    /**
     * dummy function to be overwritten by the child class
     * @param array $db_rows is an array of an array with the database values
     * @param bool $load_all force to include also the excluded phrases e.g. for admins
     * @return bool true if at least one object has been loaded
     */
    protected function rows_mapper(array $db_rows, bool $load_all = false): bool
    {
        log_err('Unexpected call of the parent rows_mapper function');
        return false;
    }

    /**
     * add the user sandbox object to the list
     *
     * @param object $sdb_obj the user sandbox object that should be added to the list
     * @param array|null $db_rows is an array of an array with the database values
     * @param bool $load_all force to include also the excluded phrases e.g. for admins
     * @return bool true if at least one object has been loaded
     */
    protected function rows_mapper_obj(object $sdb_obj, ?array $db_rows, bool $load_all = false): bool
    {
        $result = false;
        if ($db_rows != null) {
            foreach ($db_rows as $db_row) {
                $excluded = null;
                if (array_key_exists(sandbox::FLD_EXCLUDED, $db_row)) {
                    $excluded = $db_row[sandbox::FLD_EXCLUDED];
                }
                if (is_null($excluded) or $excluded == 0 or $load_all) {
                    $obj_to_add = clone $sdb_obj;
                    $obj_to_add->row_mapper_sandbox($db_row);
                    $this->add_obj($obj_to_add);
                    $result = true;
                }
            }
        }
        return $result;
    }


    /*
     * set and get
     */

    /**
     * set the user of the phrase list
     *
     * @param user $usr the person who wants to access the phrases
     * @return void
     */
    function set_user(user $usr): void
    {
        $this->usr = $usr;
    }

    /**
     * @return user the person who wants to see the phrases
     */
    function user(): user
    {
        return $this->usr;
    }


    /*
     * load
     */

    /**
     * set the SQL query parameters to load only the id and name to save time and memory
     * without the final building of the sql statement to allow adding a filter
     * e.g. for words to exclude formula words
     *   or for view to exclude system views
     *
     * @param sql $sc with the target db_type set
     * @param sandbox_named|sandbox_link_named|combine_named $sbx the single child object
     * @param string $pattern the pattern to filter the words
     * @param int $limit the number of rows to return
     * @param int $offset jump over these number of pages
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    protected function load_sql_names_pre(
        sql                                            $sc,
        sandbox_named|sandbox_link_named|combine_named $sbx,
        string                                         $pattern = '',
        int                                            $limit = 0,
        int                                            $offset = 0
    ): sql_par
    {
        $lib = new library();

        $qp = new sql_par($sbx::class, [sql_type::COMPLETE]);
        $qp->name .= 'names';

        //$sc->set_class($lib->class_to_name($sbx::class));
        $sc->set_class($sbx::class);
        $sc->set_name($qp->name);  // assign incomplete name to force the usage of the user as a parameter
        $sc->set_usr($this->user()->id());
        $sc->set_fields(array($sbx->id_field()));
        $sc->set_usr_query();
        if ($pattern != '') {
            $qp->name .= '_like';
            $sc->set_name($qp->name);
            $sc->add_where($sbx->name_field(), $pattern, sql_par_type::LIKE_R);
        }
        $sc->set_page($limit, $offset);
        $sc->set_order($sbx->name_field());

        return $qp;
    }

    /**
     * build the SQL statement to load only the id and name to save time and memory
     * without further filter
     *
     * @param sql $sc with the target db_type set
     * @param sandbox_named|sandbox_link_named|combine_named $sbx the single child object
     * @param string $pattern the pattern to filter the words
     * @param int $limit the number of rows to return
     * @param int $offset jump over these number of pages
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_names(
        sql                                            $sc,
        sandbox_named|sandbox_link_named|combine_named $sbx,
        string                                         $pattern = '',
        int                                            $limit = 0,
        int                                            $offset = 0
    ): sql_par
    {
        $qp = $this->load_sql_names_pre($sc, $sbx, $pattern, $limit, $offset);

        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * load only the id and name of sandbox objects (e.g. phrases or values) based on the given query parameters
     *
     * @param sandbox_named|sandbox_link_named|combine_named $sbx the single child object
     * @param string $pattern the pattern to filter the words
     * @param int $limit the number of rows to return
     * @param int $offset jump over these number of pages
     * @return bool true if at least one object has been loaded
     */
    function load_sbx_names(
        sandbox_named|sandbox_link_named|combine_named $sbx,
        string                                         $pattern = '',
        int                                            $limit = 0,
        int                                            $offset = 0
    ): bool
    {

        global $db_con;
        $result = false;

        // check the all minimal input parameters are set
        if ($this->user()->id() <= 0) {
            log_err('The user must be set to load ' . self::class, self::class . '->load');
        } else {
            $qp = $this->load_sql_names($db_con->sql_creator(), $sbx, $pattern, $limit, $offset);
            $db_lst = $db_con->get($qp);
            $result = $this->rows_mapper($db_lst);
        }
        return $result;
    }

    /**
     * load a list of sandbox objects (e.g. phrases or values) based on the given query parameters
     * @param sql_par $qp the SQL statement, the unique name of the SQL statement and the parameter list
     * @param bool $load_all force to include also the excluded phrases e.g. for admins
     * @return bool true if at least one object has been loaded
     */
    protected function load(sql_par $qp, bool $load_all = false): bool
    {
        return $this->load_sys($qp, $load_all);
    }

    /**
     * load a list of sandbox objects (e.g. phrases or values) based on the given query parameters
     * @param sql_par $qp the SQL statement, the unique name of the SQL statement and the parameter list
     * @param bool $load_all force to include also the excluded phrases e.g. for admins
     * @param sql_db|null $db_con_given the database connection as a parameter for the initial load of the system views
     * @return bool true if at least one object has been loaded
     */
    protected function load_sys(sql_par $qp, bool $load_all = false, ?sql_db $db_con_given = null): bool
    {

        global $db_con;
        $result = false;

        $db_con_used = $db_con_given;
        if ($db_con_used == null) {
            $db_con_used = $db_con;
        }

        // check the all minimal input parameters are set
        if ($this->user()->id() <= 0) {
            log_err('The user must be set to load ' . self::class, self::class . '->load');
        } elseif ($qp->name == '') {
            log_err('The query name cannot be created to load a ' . self::class, self::class . '->load');
        } else {
            $db_lst = $db_con_used->get($qp);
            $result = $this->rows_mapper($db_lst, $load_all);
        }
        return $result;
    }


    /*
     * modification
     */

    /**
     * add one object to the list of user sandbox objects, but only if it is not yet part of the list
     * @param object $obj_to_add the backend object that should be added
     * @param bool $allow_duplicates true if the list can contain the same entry twice e.g. for the components
     * @returns bool true the formula has been added
     */
    function add_obj(object $obj_to_add, bool $allow_duplicates = false): bool
    {
        $result = false;

        // check parameters
        if ($obj_to_add->user() == null) {
            $obj_to_add->set_user($this->user());
        }
        if ($obj_to_add->user() != $this->user()) {
            if (!$this->user()->is_admin() and !$this->user()->is_system()) {
                log_warning('Trying to add ' . $obj_to_add->dsp_id()
                    . ' of user ' . $obj_to_add->user()->name()
                    . ' to list of ' . $this->user()->name()
                );
            }
        }
        if ($obj_to_add->id() <> 0 or $obj_to_add->name() != '') {
            if ($allow_duplicates) {
                $result = parent::add_obj($obj_to_add, $allow_duplicates);
            } else {
                $obj_id = $obj_to_add->id();
                $ids = $this->ids();
                if (!in_array($obj_to_add->id(), $this->ids())) {
                    $result = parent::add_obj($obj_to_add);
                } else {
                    log_warning('Trying to add ' . $obj_to_add->dsp_id()
                        . ' which is already part of the ' . $obj_to_add::class . 'list');
                }
            }
        }
        return $result;
    }


    /*
     * debug
     */

    /**
     * create a text that describes the list for unique identification
     *
     * @param term_list|null $trm_lst a cached list of terms
     * @return string with a unique description of at least some entries of this list for debugging
     */
    function dsp_id(?term_list $trm_lst = null): string
    {
        global $debug;

        $result = '';

        // show at least 4 elements by name
        $min_names = $debug;
        if ($min_names < LIST_MIN_NAMES) {
            $min_names = LIST_MIN_NAMES;
        }

        $id = $this->ids_txt($min_names);
        if ($this->lst() != null) {
            $id_field = $this::class . '_id';
            if ($this->count() > 0) {
                $first_obj = $this->lst()[array_key_first($this->lst())];
                $id_field = $first_obj->id_field();
            }
            if ($this::class == value_list::class or $this::class == result_list::class) {
                foreach ($this->lst() as $val) {
                    if ($result != '') {
                        $result .= ' / ';
                    }
                    $result .= $val->dsp_id_entry();
                }
                if (is_array($id_field)) {
                    $fld_dsp = ' (' . implode(', ', $id_field);
                    $fld_dsp .= ' = ' . $id . ')';
                    $result .= $fld_dsp;
                } else {
                    $result .= ' (' . $id_field . ' ' . $id . ')';
                }
            } else {
                $name = $this->name($min_names);
                if ($name <> '""') {
                    $result .= $name . ' (' . $id_field . ' ' . $id . ')';
                } else {
                    $result .= $id_field . ' ' . $id;
                }
            }
            $pos = max($this->count(), $min_names);
            $result .= $this->dsp_id_remaining($pos);
        }
        return $result;
    }

    /**
     * @param int $pos the first list id that has not yet been shown
     * @return string a short summary of the remaining ids
     */
    protected function dsp_id_remaining(int $pos,): string
    {
        global $debug;
        $lib = new library();
        $result = '';

        if (count($this->lst()) > $pos) {
            $result .= ' ... total ' . $lib->dsp_count($this->lst());
        }
        if ($debug > DEBUG_SHOW_USER or $debug == 0) {
            if ($this->user() != null) {
                $result .= ' for user ' . $this->user()->id() . ' (' . $this->user()->name . ')';
            }
        }
        return $result;
    }


    /**
     * to show the list name to the user in the most simple form (without any ids)
     * this function is called from dsp_id, so no other call is allowed
     *
     * @param ?int $limit the max number of ids to show
     * @return string a simple name of the list
     */
    function name(int $limit = null): string
    {
        return '"' . implode('","', $this->names($limit)) . '"';
    }

    /**
     * @param ?int $limit the max number of ids to show
     * @return array with all names of the list
     */
    function names(int $limit = null): array
    {
        $result = [];
        $pos = 0;
        foreach ($this->lst() as $sbx_obj) {
            if ($pos <= $limit or $limit == null) {
                $result[] = $sbx_obj->name();
                $pos++;
            }
        }
        return $result;
    }

    /**
     * @param ?int $limit the max number of ids to show
     * @return string with the list of the sandbox object ids as a SQL compatible text,
     * but actually used onl< for debugging?
     */
    private function ids_txt(int $limit = null): string
    {
        $lib = new library();
        if ($this::class == value_list::class or $this::class == result_list::class) {
            $result = '';
            foreach ($this->lst() as $val) {
                if ($result != '') {
                    $result .= ' / ';
                }
                $result .= $val->grp()->dsp_id_short();
            }
            return $result;
        } else {
            return $lib->sql_array($this->ids($limit));
        }
    }

    /*
     * sql_type_list
     */

    /**
     * @param array $tbl_types list of sql table types that specifies the current case
     * @return bool true if the list of types specifies that the value or result has e.g. no protection and is public
     */
    protected function is_std(array $tbl_types): bool
    {
        if (in_array(sql_type::STANDARD, $tbl_types)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param array $tbl_types list of sql table types that specifies the current case
     * @return bool true if the list of types specifies that the value or result has max 4 prime phrases
     */
    protected function is_prime(array $tbl_types): bool
    {
        if (in_array(sql_type::PRIME, $tbl_types)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param array $tbl_types list of sql table types that specifies the current case
     * @return bool true if the list of types specifies that the result has max 7 prime phrases plus the formula id
     *              or 8 prime phrases if the table is not standard
     */
    protected function is_main(array $tbl_types): bool
    {
        if (in_array(sql_type::MAIN, $tbl_types)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param array $tbl_types list of sql table types that specifies the current case
     * @return bool true if the list of types specifies that the value has max 4 prime phrases
     */
    protected function is_big(array $tbl_types): bool
    {
        if (in_array(sql_type::BIG, $tbl_types)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param array $tbl_types list of sql table types that specifies the current case
     * @return string with the table extension in the defined order
     */
    function table_extension(array $tbl_types): string
    {
        $result = '';
        if ($this->is_std($tbl_types)) {
            $result .= sql_type::STANDARD->extension();
        }
        if ($this->is_prime($tbl_types)) {
            $result .= sql_type::PRIME->extension();
        }
        if ($this->is_main($tbl_types)) {
            $result .= sql_type::MAIN->extension();
        }
        if ($this->is_big($tbl_types)) {
            $result .= sql_type::BIG->extension();
        }
        return $result;
    }

    /**
     * @param array $tbl_types list of sql table types that specifies the current case
     * @return bool true if the list of types specifies that the value has no user overwrites
     */
    protected function is_user(array $tbl_types): bool
    {
        if (in_array(sql_type::STANDARD, $tbl_types)) {
            return true;
        } else {
            return false;
        }
    }

}
