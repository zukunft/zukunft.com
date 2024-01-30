<?php

/*

    model/value/value_list.php - to show or modify a list of values
    --------------------------

    For the value selection use a list of phrases with the
    pods that can be in memory an all devices
    to select the pod from where the values should be loaded

    for the table selection use a list of phrases with the
    table that can be in memory for the pod
    to select the tables where the value might be stored


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

namespace cfg\value;

include_once DB_PATH . 'sql_par_type.php';
include_once API_VALUE_PATH . 'value_list.php';
include_once SERVICE_EXPORT_PATH . 'value_list_exp.php';
include_once MODEL_GROUP_PATH . 'group_id_list.php';

use api\value\value_list as value_list_api;
use cfg\db\sql;
use cfg\db\sql_db;
use cfg\db\sql_table_type;
use cfg\db\sql_par;
use cfg\db\sql_par_type;
use cfg\group\group;
use cfg\group\group_id;
use cfg\group\group_id_list;
use cfg\group\group_list;
use cfg\library;
use cfg\phr_ids;
use cfg\phrase;
use cfg\phrase_list;
use cfg\protection_type;
use cfg\result\result_list;
use cfg\sandbox;
use cfg\sandbox_list;
use cfg\share_type;
use cfg\source;
use cfg\triple;
use cfg\user;
use cfg\user_message;
use cfg\word;
use cfg\word_list;
use controller\controller;
use html\button;
use html\html_base;
use html\phrase\phrase_list as phrase_list_dsp;
use cfg\export\sandbox_exp;
use cfg\export\source_exp;
use cfg\export\value_list_exp;

class value_list extends sandbox_list
{

    // TODO deprecate
    // fields to select the values
    public ?phrase_list $phr_lst = null;     // show the values related to these phrases

    /*
     * im- and export link
     */

    // the field names used for the im- and export in the json or yaml format
    const FLD_EX_CONTEXT = 'context';
    const FLD_EX_VALUES = 'values';

    /*
     * construct and map
     */

    /**
     * always set the user because a value list is always user specific
     * @param user $usr the user who requested to see this value list
     */
    function __construct(user $usr)
    {
        parent::__construct($usr);
    }

    /**
     * fill the value list based on a database records
     * @param array $db_rows is an array of an array with the database values
     * @param bool $load_all force to include also the excluded values e.g. for admins
     * @return bool true if at least one value has been loaded
     */
    protected function rows_mapper_multi(array $db_rows, string $ext, bool $load_all = false): bool
    {
        $result = false;
        if ($db_rows != null) {
            foreach ($db_rows as $db_row) {
                $excluded = null;
                if (array_key_exists(sandbox::FLD_EXCLUDED, $db_row)) {
                    $excluded = $db_row[sandbox::FLD_EXCLUDED];
                }
                if (is_null($excluded) or $excluded == 0 or $load_all) {
                    $obj_to_add = new value($this->user());
                    $obj_to_add->row_mapper_sandbox_multi($db_row, $ext);
                    $this->add_obj($obj_to_add);
                    $result = true;
                }
            }
        }
        return $result;
    }

    /*
     * cast
     */

    /**
     * @return value_list_api frontend API object filled with the relevant data of this object
     */
    function api_obj(): value_list_api
    {
        $api_obj = new value_list_api();
        $api_obj->set_lst($this->api_lst());
        return $api_obj;
    }

    /**
     * @returns string the api json message for the object as a string
     */
    function api_json(): string
    {
        return $this->api_obj()->get_json();
    }


    /*
     * load
     */

    /**
     * load a list of values that are linked to each phrase of the given list
     * e.g. for "city" and "inhabitants" all yearly increases of city inhabitants are returned
     *      to get the inhabitants of the cities itself first get a phrase list of all cities
     *
     * if $or is true
     * load a list of values that are related to at least one phrase of the given list
     *  e.g. for "Zurich (city)" and "Geneva (city)" all values related to the two cities are returned
     *
     * @param phrase_list $phr_lst phrase list to which all related values should be loaded
     * @param bool $or if true all values are returned that are linked to any phrase of the list
     * @return bool true if at least one value found
     */
    function load_by_phr_lst(phrase_list $phr_lst, bool $or = false, int $limit = sql_db::ROW_LIMIT): bool
    {
        global $db_con;
        $sc = $db_con->sql_creator();
        $qp = $this->load_sql_by_phr_lst($sc, $phr_lst, false, $or, $limit);
        return $this->load($qp);
    }

    /**
     * load a list of values that are related to the given phrase
     * e.g. for "city" all values directly related to the phrase city are returned
     * TODO use limit in query
     *
     * @param phrase $phr phrase list to which all related values should be loaded
     * @return bool true if at least one value has been loaded
     */
    function load_by_phr(phrase $phr, int $limit = sql_db::ROW_LIMIT, int $page = 0): bool
    {
        global $db_con;
        $sc = $db_con->sql_creator();
        $qp = $this->load_sql_by_phr($sc, $phr, $limit, $page);
        return $this->load($qp);
    }

    /**
     * load a list of values by the given value ids
     *
     * @param array $val_ids an array of value ids which should be loaded
     * @return bool true if at least one value found
     */
    function load_by_ids(array $val_ids): bool
    {
        global $db_con;
        $qp = $this->load_sql_by_ids($db_con->sql_creator(), $val_ids);
        return $this->load($qp);
    }


    /**
     * load a list of sandbox objects (e.g. phrases or values) based on the given query parameters
     * @param sql_par $qp the SQL statement, the unique name of the SQL statement and the parameter list
     * @param bool $load_all force to include also the excluded phrases e.g. for admins
     * @param sql_db|null $db_con_given the database connection as a parameter for the initial load of the system views
     * @return bool true if at least one object has been loaded
     */
    protected function load(sql_par $qp, bool $load_all = false, ?sql_db $db_con_given = null): bool
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
            $result = $this->rows_mapper_multi($db_lst, $qp->ext, $load_all);
        }
        return $result;
    }

    /**
     * create an SQL statement to retrieve a list of values by a list of phrases from the database
     * return all values that match at least one phrase of the list
     * TODO change where to ANY
     * TODO links and select all phrase ids
     * TODO add ORDER BY (relevance of value)
     * TODO use LIMIT and PAGE
     *
     * @param sql $sc with the target db_type set
     * @param phrase_list $phr_lst phrase list to which all related values should be loaded
     * @param bool $usr_tbl true if only the user overwrites should be loaded
     * @param bool $or true if all values related to any phrase of the list should be loaded
     * @param int $limit the number of values that should be loaded at once
     * @param int $page the offset for the limit
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_phr_lst(
        sql         $sc,
        phrase_list $phr_lst,
        bool        $usr_tbl = false,
        bool        $or = false,
        int         $limit = 0,
        int         $page = 0
    ): sql_par
    {
        $lib = new library();
        $qp = new sql_par(value::class);
        $name_ext = 'phr_lst';
        if ($usr_tbl) {
            $name_ext .= '_usr';
        }
        if ($or) {
            $name_ext .= '_all';
        }
        $name_count = '_p' . $phr_lst->count();
        $qp->name = $lib->class_to_name(value_list::class) . '_by_' . $name_ext . $name_count;
        $par_types = array();
        // loop over the possible tables where the value might be stored in this pod
        $par_pos = 2;
        foreach (value::TBL_LIST as $tbl_typ) {
            $sc->reset();
            $qp_tbl = $this->load_sql_by_phr_lst_single($sc, $phr_lst, $or, $tbl_typ, $par_pos);
            if ($sc->db_type() != sql_db::MYSQL) {
                $qp->merge($qp_tbl, true);
            } else {
                $qp->merge($qp_tbl);
            }
            $phr_pos = $par_pos + 2;
        }
        // sort the parameters if the parameters are part of the union
        if ($sc->db_type() != sql_db::MYSQL) {
            $lib = new library();
            $qp->par = $lib->key_num_sort($qp->par);
        }

        foreach ($qp->par as $par) {
            if (is_numeric($par)) {
                $par_types[] = sql_par_type::INT;
            } else {
                $par_types[] = sql_par_type::TEXT;
            }
        }
        $qp->sql = $sc->prepare_sql($qp->sql, $qp->name, $par_types);

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a list of values linked to a phrase from the database
     * from a single table
     *     *
     * @param sql $sc with the target db_type set
     * @param phrase_list $phr_lst if set to get all values for this phrase
     * @param array $tbl_typ_lst the table types for this table
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_phr_lst_single(
        sql         $sc,
        phrase_list $phr_lst,
        bool        $or,
        array       $tbl_typ_lst,
        int         $par_pos
    ): sql_par
    {
        $qp = $this->load_sql_init($sc, 'phr', $tbl_typ_lst);
        if ($this->is_prime($tbl_typ_lst)) {
            foreach ($phr_lst->lst() as $phr) {
                $spt = sql_par_type::INT_SAME;
                if ($or) {
                    $spt = sql_par_type::INT_SAME_OR;
                }
                for ($i = 1; $i <= group_id::PRIME_PHRASE; $i++) {
                    $sc->add_where(phrase::FLD_ID . '_' . $i,
                        $phr->id(), $spt, '$' . $par_pos);
                    $spt = sql_par_type::INT_SAME_OR;
                }
                $par_pos = $par_pos + 2;
            }
        } else {
            foreach ($phr_lst->lst() as $phr) {
                $grp_id = new group_id();
                $spt = sql_par_type::LIKE;
                if ($or) {
                    $spt = sql_par_type::LIKE_OR;
                }
                $sc->add_where(group::FLD_ID,
                    $grp_id->int2alpha_num($phr->id()), $spt, '$' . $par_pos + 1);
                $par_pos = $par_pos + 2;
            }
        }
        $qp->sql = $sc->sql(0, true, false);
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a list of values linked to the given phrase from the database
     *
     * @param sql $sc with the target db_type set
     * @param phrase $phr if set to get all values for this phrase
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_phr(
        sql    $sc,
        phrase $phr,
        int    $limit = 0,
        int    $page = 0
    ): sql_par
    {
        $lib = new library();
        $qp = new sql_par(value::class);
        $qp->name = $lib->class_to_name(value_list::class) . '_by_phr';
        $par_types = array();
        // loop over the possible tables where the value might be stored in this pod
        foreach (value::TBL_LIST as $tbl_typ) {
            $sc->reset();
            $qp_tbl = $this->load_sql_by_phr_single($sc, $phr, $tbl_typ);
            if ($sc->db_type() != sql_db::MYSQL) {
                $qp->merge($qp_tbl, true);
            } else {
                $qp->merge($qp_tbl);
            }
        }
        // sort the parameters if the parameters are part of the union
        if ($sc->db_type() != sql_db::MYSQL) {
            $lib = new library();
            $qp->par = $lib->key_num_sort($qp->par);
        }

        foreach ($qp->par as $par) {
            if (is_numeric($par)) {
                $par_types[] = sql_par_type::INT;
            } else {
                $par_types[] = sql_par_type::TEXT;
            }
        }
        $qp->sql = $sc->prepare_sql($qp->sql, $qp->name, $par_types);

        return $qp;
    }

    /**
     * set the SQL query parameters to load a list of values
     * @param sql $sc with the target db_type set
     * @param string $query_name the name extension to make the query name unique
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_multi(
        sql            $sc,
        string         $query_name,
        string         $ext = '',
        sql_table_type $tbl_typ = sql_table_type::MOST,
        bool           $usr_tbl = false
    ): sql_par
    {
        $qp = new sql_par(value::class, false, false, $ext);
        $qp->name .= $query_name;

        $sc->set_class(value::class, $usr_tbl, $ext);
        // overwrite the standard id field name (value_id) with the main database id field for values "group_id"
        $val = new value($this->user());
        $sc->set_id_field($val->id_field());
        $sc->set_name($qp->name);

        $sc->set_usr($this->user()->id());
        $sc->set_fields(value::FLD_NAMES);
        //$sc->set_usr_only_fields(value::FLD_NAMES_USR_ONLY);
        //$sc->set_usr_num_fields(value::FLD_NAMES_NUM_USR);
        //$db_con->set_order_text(sql_db::STD_TBL . '.' . $db_con->name_sql_esc(word::FLD_VALUES) . ' DESC, ' . word::FLD_NAME);
        return $qp;
    }

    /**
     * TODO to deprecate
     * set the SQL query parameters to load a list of values
     * @param sql $sc with the target db_type set
     * @param string $query_name the name extension to make the query name unique
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_init_old(
        sql    $sc,
        string $query_name,
        string $ext = '',
        string $tbl_ext = ''
    ): sql_par
    {
        // TODO make the name unique for all combinations of the value list
        $qp = new sql_par(self::class);
        $qp->name .= $query_name;

        $sc->set_class(value::class, false, $tbl_ext);
        // overwrite the standard id field name (value_id) with the main database id field for values "group_id"
        $val = new value($this->user());
        $sc->set_id_field($val->id_field());
        $sc->set_name($qp->name);

        $sc->set_usr($this->user()->id());
        $sc->set_fields(value::FLD_NAMES);
        $sc->set_usr_num_fields(value::FLD_NAMES_NUM_USR);
        $sc->set_usr_only_fields(value::FLD_NAMES_USR_ONLY);
        //$db_con->set_order_text(sql_db::STD_TBL . '.' . $db_con->name_sql_esc(word::FLD_VALUES) . ' DESC, ' . word::FLD_NAME);
        return $qp;
    }

    /**
     * set the SQL query parameters to load a list of values
     * set the fields for a union select of all possible tables
     *
     * @param sql $sc with the target db_type set
     * @param string $query_name the name extension to make the query name unique
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_init(
        sql    $sc,
        string $query_name,
        array  $tbl_types = []
    ): sql_par
    {
        $tbl_ext = $this->table_extension($tbl_types);
        $is_std = $this->is_std($tbl_types);
        $qp = new sql_par(self::class, $is_std, false, $tbl_ext);
        $qp->name .= $query_name;

        $sc->set_class(value::class, false, $tbl_ext);
        // overwrite the standard id field name (value_id) with the main database id field for values "group_id"
        $val = new value($this->user());
        if ($this->is_prime($tbl_types)) {
            $sc->set_id_field_dummy($val->id_field_group(true));
            $sc->set_id_field($val->id_fields_prime());
        } else {
            $sc->set_id_field($val->id_field_group());
            if ($is_std) {
                $sc->set_id_field_usr_dummy($val->id_field_group(false, true));
            }
            $sc->set_id_field_num_dummy($val->id_fields_prime());
        }
        $sc->set_name($qp->name);

        $sc->set_usr($this->user()->id());
        $sc->set_fields(value::FLD_NAMES);
        if ($is_std) {
            // TODO replace next line with union select field name synchronisation
            $sc->set_fields_num_dummy(array(user::FLD_ID));
            $sc->set_fields(value::FLD_NAMES_STD);
            $sc->set_fields_date_dummy(value::FLD_NAMES_DATE_USR_EX_STD);
            $sc->set_fields_dummy(array_merge(value::FLD_NAMES_NUM_USR_EX_STD, value::FLD_NAMES_USR_ONLY));
        } else {
            $sc->set_fields(value::FLD_NAMES);
            $sc->set_usr_num_fields(value::FLD_NAMES_NUM_USR);
            $sc->set_usr_only_fields(value::FLD_NAMES_USR_ONLY);
        }
        return $qp;
    }

    /**
     * collect the potential source tables
     * selected by the given group ids
     * all tables where the selected values might be found
     * to fix the rows of the matrix
     *
     * @param array $ids value ids that should be selected
     * @return array with the unique table extension where the values of the given id list may be found of the group ids
     */
    private function table_type_list_unique(array $ids): array
    {
        $grp_id = new group_id();
        $tbl_typ_lst = array();
        foreach ($ids as $id) {
            $tbl_typ_lst[] = $grp_id->table_type($id);
        }

        return $this->array_unique_type($tbl_typ_lst);
    }

    private function array_unique_type(array $typ_lst): array
    {
        $result = array();
        $val_lst = array();
        foreach ($typ_lst as $typ) {
            if (!in_array($typ->value, $val_lst)) {
                $result[] = $typ;
                $val_lst[] = $typ->value;
            }
        }
        return $result;
    }

    /**
     * collect all phrase ids that are needed for the selection
     * to fix the columns of the matrix
     *
     * @param array $ids value ids that should be selected
     * @return array with the unique phrase ids of the group ids
     */
    private function phrase_id_list_unique(array $ids): array
    {
        $grp_id = new group_id();
        $phr_id_lst = array();
        foreach ($ids as $id) {
            $phr_id_lst = array_merge($phr_id_lst, $grp_id->get_array($id));
        }

        return array_unique($phr_id_lst);
    }

    /**
     * TODO the second row should be 4, 0 not 0,4
     * @param array $ids
     * @return array
     */
    private function extension_id_matrix(array $ids): array
    {
        $grp_id = new group_id();
        $tbl_typ_uni = $this->table_type_list_unique($ids);
        $tbl_id_matrix = array();
        // loop over the tables where the values might be
        foreach ($tbl_typ_uni as $tbl_typ) {
            // loop over the given ids to select each value
            foreach ($ids as $id) {
                // fill the matrix row with the ids of corresponding table type
                $id_tbl_typ = $grp_id->table_type($id);
                if ($id_tbl_typ == $tbl_typ) {
                    $matrix_row = array();
                    $matrix_row[] = $tbl_typ;
                    $matrix_row[] = $grp_id->max_number_of_phrase($id);
                    $row_id_lst = $grp_id->get_array($id);
                    foreach ($row_id_lst as $phr_id) {
                        $matrix_row[] = $phr_id;
                    }
                    $tbl_id_matrix[] = $matrix_row;
                }
            }
        }
        return $tbl_id_matrix;
    }

    /**
     * create a query parameter object with a unique name
     *
     * @param array $ids array with the ids used to select the result
     * @param string $query_name the "by" name extension to make the query name unique e.g. phr_lst
     * @param bool $count_phrases true if the number of phrases are relevant for the query name
     * @return sql_par
     */
    private function load_sql_init_query_par(array $ids, string $query_name, bool $count_phrases = true): sql_par
    {
        $qp = new sql_par(value::class);
        $lib = new library();
        $tbl_typ_uni = $this->table_type_list_unique($ids);
        $tbl_ext_uni = array();
        foreach ($tbl_typ_uni as $tbl_typ) {
            $tbl_ext_uni[] = $tbl_typ->extension();
        }
        $phr_id_uni = $this->phrase_id_list_unique($ids);
        if ($count_phrases) {
            $count_ext = '_r' . count($phr_id_uni);
        } else {
            $count_ext = '_r' . count($ids);
        }
        $qp->name = $lib->class_to_name(value_list::class) .
            '_by_' . $query_name . implode("", $tbl_ext_uni) . $count_ext;
        return $qp;
    }

    /**
     * create an SQL statement to retrieve a list of values by the id from the database
     * TODO links and select all phrase ids
     *
     * @param sql $sc with the target db_type set
     * @param array $ids value ids that should be loaded
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_ids(sql $sc, array $ids, bool $usr_tbl = false): sql_par
    {
        /*
         * 1. collect the potential source tables (maybe all
         * 2. set the names based on the tables and
         */

        // get the matrix of the potential tables, the number of phrases of the table and the phrase id list
        $tbl_id_matrix = $this->extension_id_matrix($ids);
        $qp = $this->load_sql_init_query_par($ids, 'ids', false);

        $par_offset = 0;
        $par_types = array();
        foreach ($tbl_id_matrix as $matrix_row) {
            $tbl_typ = array_shift($matrix_row);
            // TODO add the union query creation for the other table types
            // combine the select statements with and instead of union if possible
            if ($tbl_typ == sql_table_type::PRIME) {
                $max_row_ids = array_shift($matrix_row);
                $phr_id_lst = $matrix_row;

                $qp_tbl = $this->load_sql_multi($sc, '', $tbl_typ->extension(), $tbl_typ, $usr_tbl);
                if ($par_offset == 0) {
                    $sc->set_usr_num_fields(value::FLD_NAMES_NUM_USR);
                } else {
                    $sc->set_usr_num_fields(value::FLD_NAMES_NUM_USR, false);
                }
                $sc->set_usr_only_fields(value::FLD_NAMES_USR_ONLY);
                for ($pos = 1; $pos <= $max_row_ids; $pos++) {
                    // the array of the phrase ids starts with 0 whereas the phrase id fields start with 1
                    $id_pos = $pos - 1;
                    if (array_key_exists($id_pos, $phr_id_lst)) {
                        if ($phr_id_lst[$id_pos] == '') {
                            $sc->add_where(phrase::FLD_ID . '_' . $pos, '0', sql_par_type::INT);
                        } else {
                            $sc->add_where(phrase::FLD_ID . '_' . $pos, $phr_id_lst[$id_pos], sql_par_type::INT);
                        }
                    } else {
                        $sc->add_where(phrase::FLD_ID . '_' . $pos, '0', sql_par_type::INT);
                    }
                }

                $qp_tbl->sql = $sc->sql($par_offset, true, false);
                $qp_tbl->par = $sc->get_par();
                $par_offset = $par_offset + count($qp_tbl->par);
                $par_types = array_merge($par_types, $sc->get_par_types());

                $qp->merge($qp_tbl);
            }
        }

        $qp->sql = $sc->prepare_sql($qp->sql, $qp->name, $par_types);

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a list of values by a list of phrases from the database
     * return all values that match at least one phrase of the list
     * TODO change where to ANY
     * TODO links and select all phrase ids
     *
     * @param sql $sc with the target db_type set
     * @param phrase_list $phr_lst phrase list to which all related values should be loaded
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_phr_lst_old(sql $sc, phrase_list $phr_lst, bool $usr_tbl = false): sql_par
    {
        // get the matrix of the potential tables, the number of phrases of the table and the phrase id list
        $tbl_id_matrix = $this->extension_id_matrix($phr_lst->ids());
        $qp = $this->load_sql_init_query_par($phr_lst->ids(), 'phr_lst_old');

        // loop over the tables where the value might be stored
        $par_offset = 0;
        $par_types = array();
        foreach ($tbl_id_matrix as $matrix_row) {
            $tbl_typ = array_shift($matrix_row);
            if ($tbl_typ == sql_table_type::PRIME) {
                $max_row_ids = array_shift($matrix_row);
                $phr_id_lst = $matrix_row;
                $qp_tbl = $this->load_sql_multi($sc, 'phr_lst_old', $tbl_typ->extension(), $tbl_typ, $usr_tbl);
                if ($par_offset == 0) {
                    $sc->set_usr_num_fields(value::FLD_NAMES_NUM_USR);
                } else {
                    $sc->set_usr_num_fields(value::FLD_NAMES_NUM_USR, false);
                }
                $sc->set_usr_only_fields(value::FLD_NAMES_USR_ONLY);
                for ($pos = 1; $pos <= $max_row_ids; $pos++) {
                    // the array of the phrase ids starts with o whereas the phrase id fields start with 1
                    $id_pos = $pos - 1;
                    if (array_key_exists($id_pos, $phr_id_lst)) {
                        $sc->add_where(phrase::FLD_ID . '_' . $pos, $phr_id_lst[$id_pos]);
                    } else {
                        $sc->add_where(phrase::FLD_ID . '_' . $pos, sql::ID_NULL);
                    }
                }

                $qp_tbl->sql = $sc->sql($par_offset, true, false);
                $qp_tbl->par = $sc->get_par();
                $par_offset = $par_offset + count($qp_tbl->par);
                $par_types = array_merge($par_types, $sc->get_par_types());

                $qp->merge($qp_tbl);
            }
        }

        $qp->sql = $sc->prepare_sql($qp->sql, $qp->name, $par_types);

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a list of values by a list of group ids from the database
     * TODO check if this not the same as the load_sql_by_ids function
     *
     * @param sql $sc with the target db_type set
     * @param phrase_list $phr_lst phrase list to which all related values should be loaded
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_grp_lst(sql $sc, phrase_list $phr_lst, bool $usr_tbl = false): sql_par
    {
        // get the matrix of the potential tables, the number of phrases of the table and the phrase id list
        $tbl_id_matrix = $this->extension_id_matrix($phr_lst->ids());
        $qp = $this->load_sql_init_query_par($phr_lst->ids(), 'grp_lst');

        // loop over the tables where the value might be stored
        $par_offset = 0;
        $par_types = array();
        foreach ($tbl_id_matrix as $matrix_row) {
            $tbl_typ = array_shift($matrix_row);
            if ($tbl_typ == sql_table_type::PRIME) {
                $max_row_ids = array_shift($matrix_row);
                $phr_id_lst = $matrix_row;
                $qp_tbl = $this->load_sql_multi($sc, 'grp_lst', $tbl_typ->extension(), $tbl_typ, $usr_tbl);
                if ($par_offset == 0) {
                    $sc->set_usr_num_fields(value::FLD_NAMES_NUM_USR);
                } else {
                    $sc->set_usr_num_fields(value::FLD_NAMES_NUM_USR, false);
                }
                $sc->set_usr_only_fields(value::FLD_NAMES_USR_ONLY);
                for ($pos = 1; $pos <= $max_row_ids; $pos++) {
                    // the array of the phrase ids starts with o whereas the phrase id fields start with 1
                    $id_pos = $pos - 1;
                    if (array_key_exists($id_pos, $phr_id_lst)) {
                        $sc->add_where(phrase::FLD_ID . '_' . $pos, $phr_id_lst[$id_pos]);
                    } else {
                        $sc->add_where(phrase::FLD_ID . '_' . $pos, '');
                    }
                }

                $qp_tbl->sql = $sc->sql($par_offset, true, false);
                $qp_tbl->par = $sc->get_par();
                $par_offset = $par_offset + count($qp_tbl->par);
                $par_types = array_merge($par_types, $sc->get_par_types());

                $qp->merge($qp_tbl);
            }
        }

        $qp->sql = $sc->prepare_sql($qp->sql, $qp->name, $par_types);

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a list of values linked to a phrase from the database
     * from a single table
     *     *
     * @param sql $sc with the target db_type set
     * @param phrase $phr if set to get all values for this phrase
     * @param array $tbl_typ_lst the table types for this table
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_phr_single(sql $sc, phrase $phr, array $tbl_typ_lst): sql_par
    {
        $qp = $this->load_sql_init($sc, 'phr', $tbl_typ_lst);
        if ($this->is_prime($tbl_typ_lst)) {
            for ($i = 1; $i <= group_id::PRIME_PHRASE; $i++) {
                $sc->add_where(phrase::FLD_ID . '_' . $i, $phr->id(), sql_par_type::INT_SAME_OR, '$2');
            }
        } else {
            $grp_id = new group_id();
            $sc->add_where(group::FLD_ID, $grp_id->int2alpha_num($phr->id()), sql_par_type::LIKE, '$3');
        }
        $qp->sql = $sc->sql(0, true, false);
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * build the sql statement based in the number of words
     * create an SQL statement to retrieve the parameters of a list of phrases from the database
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param bool $get_name to create the SQL statement name for the predefined SQL within the same function to avoid duplicating if in case of more than on where type
     * @return string the SQL statement base on the parameters set in $this
     */
    function load_by_phr_lst_sql_old(sql_db $db_con, bool $get_name = false): string
    {

        $sql_name = 'phr_lst_by_';
        $phr_ids = $this->phr_lst->id_lst();
        if (count($phr_ids) > 0) {
            $sql_name .= count($phr_ids) . 'ids';
        } else {
            log_err("At lease on phrase ID must be set to load a value list.", "value_list->load_by_phr_lst_sql");
        }

        $sql = '';
        $sql_where = '';
        $sql_from = '';
        $sql_pos = 0;
        foreach ($phr_ids as $phr_id) {
            if ($phr_id > 0) {
                $sql_pos = $sql_pos + 1;
                $sql_from = $sql_from . " value_phrase_links l" . $sql_pos . ", ";
                if ($sql_pos == 1) {
                    $sql_where = $sql_where . " WHERE l" . $sql_pos . ".phrase_id = " . $phr_id . " AND l" . $sql_pos . ".group_id = v.group_id ";
                } else {
                    $sql_where = $sql_where . "   AND l" . $sql_pos . ".phrase_id = " . $phr_id . " AND l" . $sql_pos . ".group_id = v.group_id ";
                }
            }
        }

        if ($sql_where <> '') {
            $sql = "SELECT DISTINCT v.group_id,
                    " . $db_con->get_usr_field(value::FLD_VALUE, 'v', 'u', sql_db::FLD_FORMAT_VAL) . ",
                    " . $db_con->get_usr_field(sandbox::FLD_EXCLUDED, 'v', 'u', sql_db::FLD_FORMAT_VAL) . ",
                    " . $db_con->get_usr_field(value::FLD_LAST_UPDATE, 'v', 'u', sql_db::FLD_FORMAT_VAL) . ",
                    " . $db_con->get_usr_field(source::FLD_ID, 'v', 'u', sql_db::FLD_FORMAT_VAL) . ",
                       v.user_id,
                       v.group_id
                  FROM " . $db_con->get_table_name_esc(value::class) . " v 
             LEFT JOIN user_values u ON u.group_id = v.group_id 
                                    AND u.user_id = " . $this->user()->id() . " 
                 WHERE v.group_id IN ( SELECT DISTINCT v.group_id 
                                         FROM " . $sql_from . "
                                              " . $db_con->get_table_name_esc(value::class) . " v
                                              " . $sql_where . " )
              ORDER BY v.group_id;";
        }

        if ($get_name) {
            $result = $sql_name;
        } else {
            $result = $sql;
        }
        return $result;
    }

    /**
     * set the word objects for all value in the list if needed
     * not included in load, because sometimes loading of the word objects is not needed
     */
    function load_phrases(): void
    {
        // loading via word group is the most used case, because to save database space and reading time the value is saved with the word group id
        foreach ($this->lst() as $val) {
            $val->load_phrases();
        }
    }


    /*
     * modification
     */

    /**
     * add one value to the value list, but only if it is not yet part of the list
     * @param value|null $val_to_add the value object to be added to the list
     * @returns bool true the value has been added
     */
    function add(?value $val_to_add): bool
    {
        $result = false;
        // check parameters
        if ($val_to_add != null) {
            if (get_class($val_to_add) <> value::class) {
                log_err("Object to add must be of type value, but it is " . get_class($val_to_add), "value_list->add");
            } else {
                if ($val_to_add->is_id_set() or $val_to_add->grp->name() != '') {
                    if (count($this->id_lst()) > 0) {
                        if (!in_array($val_to_add->id(), $this->id_lst())) {
                            parent::add_obj($val_to_add);
                            $result = true;
                        }
                    } else {
                        parent::add_obj($val_to_add);
                        $result = true;
                    }
                }
            }
        }
        return $result;
    }


    /*
     * im- and export
     */

    /**
     * import a value from an external object
     *
     * @param array $json_obj an array with the data of the json object
     * @param object|null $test_obj if not null the unit test object to get a dummy seq id
     * @return user_message the status of the import and if needed the error messages that should be shown to the user
     */
    function import_obj(array $json_obj, object $test_obj = null): user_message
    {
        global $share_types;
        global $protection_types;

        log_debug();
        $result = new user_message();
        $lib = new library();

        $val = new value($this->user());
        $phr_lst = new phrase_list($this->user());

        if ($test_obj) {
            $do_save = false;
        } else {
            $do_save = true;
        }

        foreach ($json_obj as $key => $value) {

            if ($key == self::FLD_EX_CONTEXT) {
                $phr_lst = new phrase_list($this->user());
                $result->add($phr_lst->import_lst($value, $test_obj));
                $val->grp = $phr_lst->get_grp_id($do_save);
            }

            if ($key == sandbox_exp::FLD_TIMESTAMP) {
                if (strtotime($value)) {
                    $val->time_stamp = $lib->get_datetime($value, $val->dsp_id(), 'JSON import');
                } else {
                    $result->add_message('Cannot add timestamp "' . $value . '" when importing ' . $val->dsp_id());
                }
            }

            if ($key == share_type::JSON_FLD) {
                $val->share_id = $share_types->id($value);
            }

            if ($key == protection_type::JSON_FLD) {
                $val->protection_id = $protection_types->id($value);
            }

            if ($key == source_exp::FLD_REF) {
                $src = new source($this->user());
                $src->set_name($value);
                if ($test_obj) {
                    $src->set_id($test_obj->seq_id());
                } else {
                    if ($result->is_ok()) {
                        $src->load_by_name($value);
                        if ($src->id() == 0) {
                            $result->add_message($src->save());
                        }
                    }
                }
                $val->source = $src;
            }

            if ($key == self::FLD_EX_VALUES) {
                foreach ($value as $val_entry) {
                    foreach ($val_entry as $val_key => $val_number) {
                        $val_to_add = clone $val;
                        $phr_lst_to_add = clone $phr_lst;
                        $val_phr = new phrase($this->user());
                        if ($test_obj) {
                            $val_phr->set_name($val_key, word::class);
                            $val_phr->set_id($test_obj->seq_id());
                        } else {
                            $val_phr->load_by_name($val_key);
                        }
                        $phr_lst_to_add->add($val_phr);
                        $val_to_add->set_number($val_number);
                        $val_to_add->grp = $phr_lst_to_add->get_grp_id($do_save);
                        if ($test_obj) {
                            $val_to_add->set_id($test_obj->seq_id());
                        } else {
                            $result->add_message($val_to_add->save());
                        }
                        $this->add_obj($val_to_add);
                    }
                }
            }

        }

        return $result;
    }

    /**
     * create a value list object for the JSON export
     */
    function export_obj(bool $do_load = true): sandbox_exp
    {
        log_debug();
        $result = new value_list_exp();
        global $share_types;
        global $protection_types;

        // reload the value parameters
        if ($do_load) {
            log_debug();
            $this->load_by_ids($this->id_lst());
        }

        if ($this->count() > 1) {

            // use the first value to get the context parameter
            $val0 = $this->get(0);
            // use the second value to detect the context phrases
            $val1 = $this->get(1);

            // get phrase names of the first value
            $phr_lst1 = $val0->phr_names();
            // get phrase names of the second value
            $phr_lst2 = $val1->phr_names();
            // add common phrase of the first and second value
            $phr_lst = array();
            if (count($phr_lst1) > 0 and count($phr_lst2) > 0) {
                $phr_lst = array_intersect($phr_lst1, $phr_lst2);
                $result->context = $phr_lst;
            }

            // order the context to make the string result reproducible
            ksort($result->context);


            // add the share type
            log_debug('get share');
            if ($val0->share_id > 0 and $val0->share_id <> $share_types->id(share_type::PUBLIC)) {
                $result->share = $val0->share_type_code_id();
            }

            // add the protection type
            log_debug('get protection');
            if ($val0->protection_id > 0 and $val0->protection_id <> $protection_types->id(protection_type::NO_PROTECT)) {
                $result->protection = $val0->protection_type_code_id();
            }

            // add the source
            if ($val0->source != null) {
                $result->source = $val0->source->name();
            }

            foreach ($this->lst() as $val) {
                $phr_name = array_diff($val->phr_names(), $phr_lst);
                if (count($phr_name) > 0) {
                    $val_entry = array();
                    $key_name = array_values($phr_name)[0];
                    $val_entry[$key_name] = $val->number();
                    $result->values[] = $val_entry;
                }
            }
        }

        log_debug(json_encode($result));
        return $result;
    }


    /*
     * data retrieval functions
     */

    /**
     * get a list with all time phrase used in the complete value list
     */
    function time_lst(): phrase_list
    {
        $lib = new library();
        $all_ids = array();
        foreach ($this->lst() as $val) {
            $all_ids = array_unique(array_merge($all_ids, array($val->time_id)));
        }
        $phr_lst = new phrase_list($this->user());
        if (count($all_ids) > 0) {
            $phr_lst->load_names_by_ids(new phr_ids($all_ids));
        }
        log_debug($lib->dsp_count($phr_lst->lst()));
        return $phr_lst;
    }

    /**
     * @return phrase_list list with all unique phrase used in the complete value list
     */
    function phr_lst(): phrase_list
    {
        log_debug('by ids (needs review)');
        $phr_lst = new phrase_list($this->user());
        $lib = new library();

        foreach ($this->lst() as $val) {
            if (!isset($val->phr_lst)) {
                $val->load();
                $val->load_phrases();
            }
            $phr_lst->merge($val->phr_lst);
        }

        log_debug($lib->dsp_count($phr_lst->lst()));
        return $phr_lst;
    }

    /**
     * @return phrase_list  list with all unique phrase including the time phrase
     */
    function phr_lst_all(): phrase_list
    {
        log_debug();

        $phr_lst = $this->phr_lst();
        $phr_lst->merge($this->time_lst());

        log_debug('done');
        return $phr_lst;
    }

    /**
     * @return word_list list of all words used for the value list
     */
    function wrd_lst(): word_list
    {
        log_debug();

        $phr_lst = $this->phr_lst_all();
        $wrd_lst = $phr_lst->wrd_lst_all();

        log_debug('done');
        return $wrd_lst;
    }

    /**
     * get a list of all words used for the value list
     */
    function source_lst(): array
    {
        log_debug();
        $result = array();
        $src_ids = array();

        foreach ($this->lst() as $val) {
            if ($val->source_id > 0) {
                log_debug('test id ' . $val->source_id);
                if (!in_array($val->source_id, $src_ids)) {
                    log_debug('add id ' . $val->source_id);
                    if (!isset($val->source)) {
                        log_debug('load id ' . $val->source_id);
                        $val->load_source();
                        log_debug('loaded ' . $val->source->name);
                    } else {
                        if ($val->source_id <> $val->source->id) {
                            log_debug('load id ' . $val->source_id);
                            $val->load_source();
                            log_debug('loaded ' . $val->source->name);
                        }
                    }
                    $result[] = $val->source;
                    $src_ids[] = $val->source_id;
                    log_debug('added ' . $val->source->name);
                }
            }
        }

        log_debug('done');
        return $result;
    }

    /**
     * @return array a list of all numbers of this value list
     */
    function numbers(): array
    {
        $result = array();
        foreach ($this->lst() as $val) {
            $result[] = $val->number();
        }
        return $result;
    }

    /**
     * @return array with the sorted value ids
     */
    function id_lst(): array
    {
        $lst = array();
        if ($this->count() > 0) {
            foreach ($this->lst() as $val) {
                // use only valid ids
                if ($val->id() <> 0) {
                    $lst[] = $val->id();
                }
            }
        }
        asort($lst);
        return $lst;
    }


    /*
     * filter and select functions
    */

    /**
     * @returns value_list that contains only values that match the time word list
     */
    function filter_by_time($time_lst): value_list
    {
        log_debug();
        $lib = new library();
        $val_lst = array();
        foreach ($this->lst() as $val) {
            // only include time specific value
            if ($val->time_id > 0) {
                // only include values within the specific time periods
                if (in_array($val->time_id, $time_lst->ids)) {
                    $val_lst[] = $val;
                    log_debug('include ' . $val->name());
                } else {
                    log_debug('excluded ' . $val->name() . ' because outside the specified time periods');
                }
            } else {
                log_debug('excluded ' . $val->name() . ' because this is not time specific');
            }
        }
        $result = clone $this;
        $result->set_lst($val_lst);

        log_debug($lib->dsp_count($result->lst()));
        return $result;
    }

    /**
     * return a value list object that contains only values that match at least one phrase from the phrase list
     */
    function filter_by_phrase_lst($phr_lst): value_list
    {
        $lib = new library();
        log_debug($lib->dsp_count($this->lst()) . ' values by ' . $phr_lst->name());
        $result = array();
        foreach ($this->lst() as $val) {
            //$val->load_phrases();
            $val_phr_lst = $val->phr_lst;
            if (isset($val_phr_lst)) {
                log_debug('val phrase list ' . $val_phr_lst->name());
            } else {
                log_debug('val no value phrase list');
            }
            $found = false;
            foreach ($val_phr_lst->lst() as $phr) {
                //zu_debug('value_list->filter_by_phrase_lst val is '.$phr->name().' in '.$phr_lst->name());
                if (in_array($phr->name(), $phr_lst->names())) {
                    if (isset($val_phr_lst)) {
                        log_debug('val phrase list ' . $val_phr_lst->name() . ' is found in ' . $phr_lst->name());
                    } else {
                        log_debug('val found, but no value phrase list');
                    }
                    $found = true; // to make sure that each value is only added once; an improvement could be to stop searching after a phrase is found
                }
            }
            if ($found) {
                $result[] = $val;
            }
        }
        $this->set_lst($result);

        log_debug($lib->dsp_count($this->lst()));
        return $this;
    }

    /**
     * selects from a val_lst_phr the best matching value
     * best matching means that all words from word_ids must be matching and the least additional words, because this would be a more specific value
     * used by value_list_dsp->dsp_table
     */
    function get_from_lst($word_ids)
    {
        asort($word_ids);
        log_debug("ids " . implode(",", $word_ids) . ".");
        $lib = new library();

        $found = false;
        $result = null;
        foreach ($this->lst() as $val) {
            if (!$found) {
                log_debug("check " . implode(",", $word_ids) . " with (" . implode(",", $val->ids) . ")");
                $wrd_missing = $lib->lst_not_in($word_ids, $val->ids);
                if (empty($wrd_missing)) {
                    // potential result candidate, because the value has all needed words
                    log_debug("can (" . $val->number() . ")");
                    $wrd_extra = $lib->lst_not_in($val->ids, $word_ids);
                    if (empty($wrd_extra)) {
                        // if there is no extra word, it is the correct value
                        log_debug("is (" . $val->number() . ")");
                        $found = true;
                        $result = $val;
                    } else {
                        log_debug("is not, because (" . implode(",", $wrd_extra) . ")");
                    }
                }
            }
        }

        log_debug("done " . $result->number);
        return $result;
    }

    /**
     * selects from a val_lst_wrd the best matching value
     * best matching means that all words from word_ids must be matching and the least additional words, because this would be a more specific value
     * used by value_list_dsp->dsp_table
     */
    function get_by_grp($grp, $time)
    {
        log_debug("value_list->get_by_grp " . $grp->auto_name . ".");

        $found = false;
        $result = null;
        $row = 0;
        foreach ($this->lst() as $val) {
            if (!$found) {
                // show only a few debug messages for a useful result
                if ($row < 6) {
                    log_debug("value_list->get_by_grp check if " . $val->grp_id . " = " . $grp->id . " and " . $val->time_id . " = " . $time->id . ".");
                }
                if ($val->grp_id == $grp->id
                    and $val->time_id == $time->id) {
                    $found = true;
                    $result = $val;
                } else {
                    if (!isset($val->grp)) {
                        log_debug("load group");
                        $val->load_phrases();
                    }
                    if (isset($val->grp)) {
                        if ($row < 6) {
                            log_debug('check if all of ' . $grp->name() . ' are in ' . $val->grp->name() . ' and value should be used');
                        }
                        if ($val->grp->has_all_phrases_of($grp)
                            and $val->time_id == $time->id) {
                            log_debug('all of ' . $grp->name() . ' are in ' . $val->grp->name() . ' so value is used');
                            $found = true;
                            $result = $val;
                        }
                    }
                }
            }
            $row++;
        }

        log_debug("done " . $result->number);
        return $result;
    }

    /**
     * @return bool true if the list contains at least one value
     */
    function has_values(): bool
    {
        $result = false;
        if ($this->count() > 0) {
            $result = true;
        }
        return $result;
    }


    /*
     * convert
     */

    /**
     * return a list of phrase groups for all values of this list
     */
    function phrase_groups(): group_list
    {
        log_debug();
        $lib = new library();
        $grp_lst = new group_list($this->user());
        foreach ($this->lst() as $val) {
            if (!isset($val->grp)) {
                $val->load_grp_by_id();
            }
            if (isset($val->grp)) {
                $grp_lst->add_obj($val->grp);
            } else {
                log_err("The phrase group for value " . $val->id . " cannot be loaded.", "value_list->phrase_groups");
            }
        }

        log_debug($lib->dsp_count($grp_lst->lst()));
        return $grp_lst;
    }


    /**
     * return a list of phrases used for each value
     */
    function common_phrases(): phrase_list
    {
        $lib = new library();
        $grp_lst = $this->phrase_groups();
        $phr_lst = $grp_lst->common_phrases();
        log_debug($lib->dsp_count($phr_lst->lst()));
        return $phr_lst;
    }

    /*
     * check / database consistency functions
     */

    /**
     * check the consistency for all values
     * so get the words and triples linked from the word group
     *    and update the slave table value_phrase_links (which should be renamed to value_phrase_links)
     * TODO split into smaller sections by adding LIMIT to the query and start a loop
     */
    function check_all(): bool
    {

        global $db_con;
        $lib = new library();
        $result = true;

        // the id and the user must be set
        $db_con->set_class(value::class);
        $db_con->set_usr($this->user()->id());
        $sql = $db_con->select_by_set_id();
        $db_val_lst = $db_con->get_old($sql);
        foreach ($db_val_lst as $db_val) {
            $val = new value($this->user());
            $val->load_by_id($db_val[value::FLD_ID], value::class);
            if (!$val->check()) {
                $result = false;
            }
            log_debug($lib->dsp_count($this->lst()));
        }
        log_debug($lib->dsp_count($this->lst()));
        return $result;
    }

    /**
     * to be integrated into load
     * list of values related to a formula
     * described by the word to which the formula is assigned
     * and the words used in the formula
     */
    function load_frm_related($phr_id, $phr_ids, $user_id)
    {
        log_debug("value_list->load_frm_related (" . $phr_id . ",ft" . implode(",", $phr_ids) . ",u" . $user_id . ")");

        global $db_con;
        $result = array();

        if ($phr_id > 0 and !empty($phr_ids)) {
            $sql = "SELECT l1.group_id
                FROM value_phrase_links l1,
                    value_phrase_links l2
              WHERE l1.group_id = l2.group_id
                AND l1.phrase_id = " . $phr_id . "
                AND l2.phrase_id IN (" . implode(",", $phr_ids) . ");";
            //$db_con = New mysql;
            $db_con->usr_id = $this->user()->id();
            $db_lst = $db_con->get_old($sql);
            foreach ($db_lst as $db_val) {
                $result = $db_val[value::FLD_ID];
            }
        }

        log_debug(implode(",", $result));
        return $result;
    }

    /*
     * group words
     * kind of similar to zu_sql_val_lst_wrd
    function load_frm_related_grp_phrs_part($val_ids, $phr_id, $phr_ids, $user_id): array
    {
        log_debug("(v" . implode(",", $val_ids) . ",t" . $phr_id . ",ft" . implode(",", $phr_ids) . ",u" . $user_id . ")");

        global $db_con;
        $result = array();

        if ($phr_id > 0 and !empty($phr_ids) and !empty($val_ids)) {
            $phr_ids[] = $phr_id; // add the main word to the exclude words
            $sql = "SELECT l.group_id,
                    " . $db_con->get_usr_field(value::FLD_VALUE, 'v', 'u', sql_db::FLD_FORMAT_VAL) . ",
                    l.phrase_id, 
                    v.excluded, 
                    u.excluded AS user_excluded 
                FROM value_phrase_links l,
                    " . $db_con->get_table_name_esc(value::class) . " v
          LEFT JOIN user_values u ON v.group_id = u.group_id AND u.user_id = " . $user_id . " 
              WHERE l.group_id = v.group_id
                AND l.phrase_id NOT IN (" . implode(",", $phr_ids) . ")
                AND l.group_id IN (" . implode(",", $val_ids) . ")
                AND (u.excluded IS NULL OR u.excluded = 0) 
            GROUP BY l.group_id, l.phrase_id;";
            //$db_con = New mysql;
            $db_con->usr_id = $this->user()->id();
            $db_lst = $db_con->get_old($sql);
            $group_id = -1; // set to an id that is never used to force the creation of a new entry at start
            foreach ($db_lst as $db_val) {
                if ($group_id == $db_val[value::FLD_ID]) {
                    $phr_result[] = $db_val[phrase::FLD_ID];
                } else {
                    if ($group_id >= 0) {
                        // remember the previous values
                        $row_result[] = $phr_result;
                        $result[$group_id] = $row_result;
                    }
                    // remember the values for a new result row
                    $group_id = $db_val[value::FLD_ID];
                    $val_num = $db_val[value::FLD_VALUE];
                    $row_result = array();
                    $row_result[] = $val_num;
                    $phr_result = array();
                    $phr_result[] = $db_val[phrase::FLD_ID];
                }
            }
            if ($group_id >= 0) {
                // remember the last values
                $row_result[] = $phr_result;
                $result[$group_id] = $row_result;
            }
        }

        log_debug(zu_lst_dsp($result));
        return $result;
    }
     */


    /*
    private function common_phrases(): phrase_list
    {

    }
    */


    /**
     * delete all loaded values e.g. to delete all the values linked to a phrase
     * @return user_message
     */
    function del(): user_message
    {
        $result = new user_message();

        foreach ($this->lst() as $val) {
            $result->add($val->del());
        }
        return new user_message();
    }

}
