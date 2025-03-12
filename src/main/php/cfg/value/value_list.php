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

include_once MODEL_SANDBOX_PATH . 'sandbox_value_list.php';
include_once DB_PATH . 'sql_creator.php';
include_once DB_PATH . 'sql_db.php';
include_once DB_PATH . 'sql_field_list.php';
include_once DB_PATH . 'sql_par.php';
include_once DB_PATH . 'sql_par_type.php';
include_once DB_PATH . 'sql_type.php';
include_once DB_PATH . 'sql_type_list.php';
include_once MODEL_GROUP_PATH . 'group.php';
include_once MODEL_GROUP_PATH . 'group_id.php';
include_once MODEL_GROUP_PATH . 'group_list.php';
include_once MODEL_PHRASE_PATH . 'phrase.php';
include_once MODEL_PHRASE_PATH . 'phrase_list.php';
include_once MODEL_REF_PATH . 'source.php';
include_once MODEL_RESULT_PATH . 'result.php';
include_once MODEL_SANDBOX_PATH . 'sandbox.php';
include_once MODEL_USER_PATH . 'user.php';
include_once MODEL_USER_PATH . 'user_message.php';
include_once MODEL_WORD_PATH . 'word.php';
include_once MODEL_WORD_PATH . 'word_list.php';
include_once SHARED_TYPES_PATH . 'api_type_list.php';
include_once SHARED_TYPES_PATH . 'protection_type.php';
include_once SHARED_TYPES_PATH . 'share_type.php';
include_once SHARED_PATH . 'json_fields.php';
include_once SHARED_PATH . 'library.php';

use cfg\db\sql_field_list;
use cfg\db\sql_type_list;
use cfg\sandbox\sandbox;
use cfg\sandbox\sandbox_value_list;
use cfg\user\user;
use shared\json_fields;
use shared\types\protection_type as protect_type_shared;
use shared\types\share_type as share_type_shared;
use cfg\db\sql_creator;
use cfg\db\sql_db;
use cfg\db\sql_par;
use cfg\db\sql_par_type;
use cfg\db\sql_type;
use cfg\group\group;
use cfg\group\group_id;
use cfg\group\group_list;
use cfg\phrase\phrase;
use cfg\phrase\phrase_list;
use cfg\ref\source;
use cfg\user\user_message;
use cfg\word\word;
use cfg\word\word_list;
use shared\library;

class value_list extends sandbox_value_list
{

    /*
     * construct and map
     */

    /**
     * fill the value list based on a database records
     * TODO replace $ext with sql_tbl_typ
     *
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
     * set and get
     */

    function set_grp_ids(): user_message
    {
        $usr_msg = new user_message();

        foreach ($this->lst() as $val) {
            $phr_lst = $val->phrase_list();
            $val->set_grp($phr_lst->get_grp_id(false));
            //$usr_msg->add_message('');
        }
        return $usr_msg;

    }

    function grp_ids(): group_list
    {
        $grp_lst = new group_list($this->user());

        foreach ($this->lst() as $val) {
            $grp_lst->add($val->grp());
        }
        return $grp_lst;

    }


    /*
     * load
     */

    /**
     * if $or is false or null
     * load a list of values that are linked to each phrase of the given list
     * e.g. for "city" and "inhabitants" the city inhabitants for all years are returned
     *      to get the inhabitants of the cities itself first get a phrase list of all cities
     *
     * if $or is true
     * load a list of values that are related to at least one phrase of the given list
     *  e.g. for "Zurich (city)" and "Geneva (city)" all values related to the two cities are returned
     *
     *  TODO use order by in query
     *  TODO use limit and page in query
     *
     * @param phrase_list $phr_lst phrase list to which all related values should be loaded
     * @param bool $or if true all values are returned that are linked to any phrase of the list
     * @param int $limit the number of values that should be loaded at once
     * @param int $page the offset for the limit
     * @return bool true if at least one value found
     */
    function load_by_phr_lst(
        phrase_list $phr_lst,
        bool        $or = false,
        int         $limit = sql_db::ROW_LIMIT,
        int         $page = 0
    ): bool
    {
        return parent::load_by_phr_lst_multi($phr_lst, value::class, $or, $limit, $page);
    }

    /**
     * load a list of values that are related to the given phrase
     * e.g. for "city" all values directly related to the phrase city are returned
     *
     * TODO use order by in query
     * TODO use limit and page in query
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

    // interface load

    /**
     * load a list of values by the given value ids
     *
     * @param string|int $id the value / group id
     * @return bool true if at least one value found
     */
    function load_by_id(string|int $id): bool
    {
        return $this->load_by_ids([$id]);
    }

    // internal load

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
     * TODO add ORDER BY (relevance of value)
     * TODO use LIMIT and PAGE
     *
     * @param sql_creator $sc with the target db_type set
     * @param phrase_list $phr_lst phrase list to which all related values should be loaded
     * @param bool $usr_tbl true if only the user overwrites should be loaded
     * @param bool $or true if all values related to any phrase of the list should be loaded
     * @param int $limit the number of values that should be loaded at once
     * @param int $page the offset for the limit
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_phr_lst(
        sql_creator $sc,
        phrase_list $phr_lst,
        bool        $usr_tbl = false,
        bool        $or = false,
        int         $limit = 0,
        int         $page = 0
    ): sql_par
    {
        return parent::load_sql_by_phr_lst_multi($sc, $phr_lst, value::class, $usr_tbl, $or, $limit, $page);
    }

    /**
     * create an SQL statement to retrieve a list of values linked to the given phrase from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param phrase $phr if set to get all values for this phrase
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_phr(
        sql_creator $sc,
        phrase      $phr,
        int         $limit = 0,
        int         $page = 0
    ): sql_par
    {
        $lib = new library();
        $qp = new sql_par(value::class);
        $qp->name = $lib->class_to_name(value_list::class) . '_by_phr';
        $par_types = array();

        // prepare adding the parameters in order of expected usage
        $par_pos = $sc->par_count();

        // add the single phrase parameter
        $pos_phr = $par_pos;
        $par_pos++;
        $par_name = $sc->par_name($par_pos);
        $sc->add_where_par(phrase::FLD_ID, $phr->id(), sql_par_type::INT_SAME_OR, '', $par_name);

        // add the phrase group parameter
        $pos_grp = $par_pos;
        $par_pos++;
        $par_name = $sc->par_name($par_pos);
        $grp_id = new group_id();
        $sc->add_where_par(group::FLD_ID, $grp_id->int2alpha_num($phr->id()), sql_par_type::LIKE, '', $par_name);

        // add the user parameter
        $pos_usr = $par_pos;
        $par_pos++;
        $par_name = $sc->par_name($par_pos);
        $sc->add_where_par(user::FLD_ID, $this->user()->id(), sql_par_type::INT, '', $par_name);

        // remember the parameters
        $par_lst = clone $sc->par_list();

        // loop over the possible tables where the value might be stored in this pod
        foreach (value_base::TBL_LIST as $tbl_typ) {
            // reset but keep the parameter list
            $sc->reset();
            $qp_tbl = $this->load_sql_by_phr_single($sc, $pos_phr, $pos_grp, $pos_usr, $tbl_typ, $par_lst);
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
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name extension to make the query name unique
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_multi(
        sql_creator   $sc,
        string        $query_name,
        sql_type_list $sc_par_lst
    ): sql_par
    {
        $qp = new sql_par(value::class, new sql_type_list(), $sc_par_lst->ext_ex_user());
        $qp->name .= $query_name;

        $sc->set_class(value::class, $sc_par_lst);
        // overwrite the standard id field name (value_id) with the main database id field for values "group_id"
        $val = new value($this->user());
        $sc->set_id_field($val->id_field());
        $sc->set_name($qp->name);

        $sc->set_usr($this->user()->id());
        $sc->set_fields(value_base::FLD_NAMES);
        //$sc->set_usr_only_fields(value::FLD_NAMES_USR_ONLY);
        //$sc->set_usr_num_fields(value::FLD_NAMES_NUM_USR);
        //$db_con->set_order_text(sql_db::STD_TBL . '.' . $db_con->name_sql_esc(word_db::FLD_VALUES) . ' DESC, ' . word_db::FLD_NAME);
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
        $sc_par_lst = array();
        foreach ($ids as $id) {
            $sc_par_lst[] = $grp_id->table_type($id);
        }

        return $this->array_unique_type($sc_par_lst);
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
     * @param sql_creator $sc with the target db_type set
     * @param array $ids value ids that should be loaded
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_ids(sql_creator $sc, array $ids, bool $usr_tbl = false): sql_par
    {
        /*
         * 1. collect the potential source tables (maybe all)
         * 2. set the names based on the tables and
         */

        // get the matrix of the potential tables, the number of phrases of the table and the phrase id list
        $tbl_id_matrix = $this->extension_id_matrix($ids);
        $qp = $this->load_sql_init_query_par($ids, 'ids', false);

        $par_offset = 0;
        $par_types = array();
        foreach ($tbl_id_matrix as $matrix_row) {
            $sc_par_lst = new sql_type_list();
            $tbl_typ = array_shift($matrix_row);
            $sc_par_lst->add($tbl_typ);
            // TODO add the union query creation for the other table types
            // combine the select statements with and instead of union if possible
            if ($tbl_typ == sql_type::PRIME) {
                $max_row_ids = array_shift($matrix_row);
                $phr_id_lst = $matrix_row;

                // TODO move to the calling function
                if ($usr_tbl) {
                    $sc_par_lst->add(sql_type::USER);
                }
                $qp_tbl = $this->load_sql_multi($sc, '', $sc_par_lst);
                if ($par_offset == 0) {
                    $sc->set_usr_num_fields(value_base::FLD_NAMES_NUM_USR);
                } else {
                    $sc->set_usr_num_fields(value_base::FLD_NAMES_NUM_USR, false);
                }
                $sc->set_usr_only_fields(value_base::FLD_NAMES_USR_ONLY);
                for ($pos = 1; $pos <= $max_row_ids; $pos++) {
                    // the array of the phrase ids starts with 0 whereas the phrase id fields start with 1
                    $id_pos = $pos - 1;
                    if (array_key_exists($id_pos, $phr_id_lst)) {
                        if ($phr_id_lst[$id_pos] == '') {
                            $sc->add_where(phrase::FLD_ID . '_' . $pos, '0', sql_par_type::INT, null, '', $par_offset);
                        } else {
                            $sc->add_where(phrase::FLD_ID . '_' . $pos, $phr_id_lst[$id_pos], sql_par_type::INT, null, '', $par_offset);
                        }
                    } else {
                        $sc->add_where(phrase::FLD_ID . '_' . $pos, '0', sql_par_type::INT, null, '', $par_offset);
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
     * @param sql_creator $sc with the target db_type set
     * @param phrase_list $phr_lst phrase list to which all related values should be loaded
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_grp_lst(sql_creator $sc, phrase_list $phr_lst, bool $usr_tbl = false): sql_par
    {
        // get the matrix of the potential tables, the number of phrases of the table and the phrase id list
        $tbl_id_matrix = $this->extension_id_matrix($phr_lst->ids());
        $qp = $this->load_sql_init_query_par($phr_lst->ids(), 'grp_lst');

        // loop over the tables where the value might be stored
        $par_offset = 0;
        $par_types = array();
        foreach ($tbl_id_matrix as $matrix_row) {
            $sc_par_lst = new sql_type_list();
            $tbl_typ = array_shift($matrix_row);
            $sc_par_lst->add($tbl_typ);
            if ($tbl_typ == sql_type::PRIME) {
                $max_row_ids = array_shift($matrix_row);
                $phr_id_lst = $matrix_row;

                // TODO move to the calling function
                if ($usr_tbl) {
                    $sc_par_lst->add(sql_type::USER);
                }
                $qp_tbl = $this->load_sql_multi($sc, 'grp_lst', $sc_par_lst);
                if ($par_offset == 0) {
                    $sc->set_usr_num_fields(value_base::FLD_NAMES_NUM_USR);
                } else {
                    $sc->set_usr_num_fields(value_base::FLD_NAMES_NUM_USR, false);
                }
                $sc->set_usr_only_fields(value_base::FLD_NAMES_USR_ONLY);
                for ($pos = 1; $pos <= $max_row_ids; $pos++) {
                    // the array of the phrase ids starts with o whereas the phrase id fields start with 1
                    $id_pos = $pos - 1;
                    if (array_key_exists($id_pos, $phr_id_lst)) {
                        $sc->add_where(phrase::FLD_ID . '_' . $pos, $phr_id_lst[$id_pos], sql_par_type::INT, null, '', $par_offset);
                    } else {
                        $sc->add_where(phrase::FLD_ID . '_' . $pos, '', sql_par_type::INT, null, '', $par_offset);
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
     * @param sql_creator $sc with the target db_type set
     * @param int $phr_pos the array key of the query parameter for the phrase id
     * @param int $grp_pos the array key of the query parameter for the phrase id as group id
     * @param int $usr_pos the array key of the query parameter for the user id
     * @param array $sc_par_lst the parameters for the sql statement creation
     * @param sql_field_list $par_lst list of parameters use for all table types
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_phr_single(
        sql_creator    $sc,
        int            $phr_pos,
        int            $grp_pos,
        int            $usr_pos,
        array          $sc_par_lst,
        sql_field_list $par_lst
    ): sql_par
    {
        $qp = $this->load_sql_init(
            $sc, value::class, 'phr',
            $sc_par_lst, $par_lst, $usr_pos);
        if ($this->is_prime($sc_par_lst)) {
            for ($i = 1; $i <= group_id::PRIME_PHRASES_STD; $i++) {
                $sc->add_where_no_par('',
                    phrase::FLD_ID . '_' . $i, sql_par_type::INT_SAME_OR, $phr_pos);
            }
        } else {
            $sc->add_where_no_par('', group::FLD_ID, sql_par_type::LIKE, $grp_pos);
        }
        $qp->sql = $sc->sql(0, true, false);
        $qp->par = $sc->get_par();

        return $qp;
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
     * modify
     */

    /**
     * add one value to the value list, but only if it is not yet part of the list
     * @param value|null $val_to_add the value object to be added to the list
     * @param bool $allow_duplicates true if e.g. the group id is not yet set but the value should nevertheless be added
     * @returns bool true the value has been added
     */
    function add(?value $val_to_add, bool $allow_duplicates = false): bool
    {
        $result = false;
        // check parameters
        if ($val_to_add != null) {
            if ($allow_duplicates) {
                parent::add_obj($val_to_add, $allow_duplicates);
            } else {
                if ($val_to_add->is_id_set() or $val_to_add->grp()->name() != '') {
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

    /**
     * remove a value from the list
     * @param value|null $val the value that should be removed from this list
     * @return bool true if the value has been removed
     */
    function remove(?value $val): bool
    {
        $result = false;
        if ($this->get($val->id() != null)) {
            $this->unset($val->id());
            $result = true;
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
        global $shr_typ_cac;
        global $ptc_typ_cac;

        log_debug();
        $usr_msg = new user_message();
        $lib = new library();

        $val = new value($this->user());
        $phr_lst = new phrase_list($this->user());

        if ($test_obj) {
            $do_save = false;
        } else {
            $do_save = true;
        }

        foreach ($json_obj as $key => $value) {

            if ($key == json_fields::CONTEXT) {
                $phr_lst = new phrase_list($this->user());
                $usr_msg->add($phr_lst->import_lst($value, $test_obj));
                $val->set_grp($phr_lst->get_grp_id($do_save));
            }

            if ($key == json_fields::TIMESTAMP) {
                if (strtotime($value)) {
                    $val->time_stamp = $lib->get_datetime($value, $val->dsp_id(), 'JSON import');
                } else {
                    $usr_msg->add_message('Cannot add timestamp "' . $value . '" when importing ' . $val->dsp_id());
                }
            }

            if ($key == json_fields::SHARE) {
                $val->share_id = $shr_typ_cac->id($value);
            }

            if ($key == json_fields::PROTECTION) {
                $val->protection_id = $ptc_typ_cac->id($value);
            }

            if ($key == json_fields::SOURCE_NAME) {
                $src = new source($this->user());
                $src->set_name($value);
                if ($test_obj) {
                    $src->set_id($test_obj->seq_id());
                } else {
                    if ($usr_msg->is_ok()) {
                        $src->load_by_name($value);
                        if ($src->id() == 0) {
                            $usr_msg->add($src->save());
                        }
                    }
                }
                $val->source = $src;
            }

            if ($key == json_fields::VALUES) {
                foreach ($value as $val_entry_key => $val_entry) {
                    if (is_array($val_entry)) {
                        foreach ($val_entry as $val_key => $val_number) {
                            $usr_msg = $this->add_value(
                                $val_key,
                                $val_number,
                                $val,
                                $phr_lst,
                                $do_save,
                                $usr_msg,
                                $test_obj);
                        }
                    } else {
                        $usr_msg = $this->add_value(
                            $val_entry_key,
                            $val_entry,
                            $val,
                            $phr_lst,
                            $do_save,
                            $usr_msg,
                            $test_obj);
                    }
                }
            }
        }

        return $usr_msg;
    }

    private function add_value(
        $val_key,
        $val_number,
        value $val,
        phrase_list $phr_lst,
        bool $do_save,
        user_message $usr_msg,
        object $test_obj = null
    ): user_message
    {
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
        $val_to_add->set_grp($phr_lst_to_add->get_grp_id($do_save));
        if ($test_obj) {
            $val_to_add->set_id($test_obj->seq_id());
        } else {
            $usr_msg->add($val_to_add->save());
        }
        $this->add_obj($val_to_add);
        return $usr_msg;
    }

    /**
     * create an array with the export json fields
     * @param bool $do_load to switch off the database load for unit tests
     * @return array the filled array used to create the user export json
     */
    function export_json(bool $do_load = true): array
    {
        global $shr_typ_cac;
        global $ptc_typ_cac;

        $vars = [];

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
                ksort($phr_lst);
                $vars[json_fields::CONTEXT] = $phr_lst;
            }

            // add the share type
            if ($val0->share_id > 0 and $val0->share_id <> $shr_typ_cac->id(share_type_shared::PUBLIC)) {
                $vars[json_fields::SHARE] = $val0->share_type_code_id();
            }

            // add the protection type
            if ($val0->protection_id > 0 and $val0->protection_id <> $ptc_typ_cac->id(protect_type_shared::NO_PROTECT)) {
                $vars[json_fields::PROTECTION] = $val0->protection_type_code_id();
            }

            // add the source
            if ($val0->source() != null) {
                $vars[json_fields::SOURCE_NAME] = $val0->source()->name();
            }

            if (count($this->lst()) > 0) {
                $val_lst = [];
                foreach ($this->lst() as $val) {
                    $phr_name = array_diff($val->phr_names(), $phr_lst);
                    if (count($phr_name) > 0) {
                        $val_entry = array();
                        $key_name = array_values($phr_name)[0];
                        $val_entry[$key_name] = $val->number();
                        $val_lst[] = $val_entry;
                    }
                }
                $vars[json_fields::VALUES] = $val_lst;
            }
        }

        return $vars;
    }


    /*
     * data retrieval functions
     */

    /**
     * get a list with all time phrase used in the complete value list
     * @return phrase_list with the time phrases of this value list
     */
    function time_list(): phrase_list
    {
        log_debug();
        $lst = new phrase_list($this->user());
        foreach ($this->lst() as $val) {
            $lst->merge($val->phrase_list()->time_list());
        }
        return $lst;
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
                $val->load_phrases();
            }
            $phr_lst->merge($val->phr_lst());
        }

        log_debug($lib->dsp_count($phr_lst->lst()));
        return $phr_lst;
    }

    /**
     * @return phrase_list list with all unique phrase including the time phrase
     */
    function phr_lst_all(): phrase_list
    {
        log_debug();

        $phr_lst = $this->phr_lst();
        $phr_lst->merge($this->time_list());

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
            if ($val->source != null) {
                if ($val->source->id() > 0) {
                    log_debug('test id ' . $val->source->id());
                    if (!in_array($val->source->id(), $src_ids)) {
                        log_debug('add id ' . $val->source->id());
                        if (!isset($val->source)) {
                            log_debug('load id ' . $val->source->id());
                            $val->load_source();
                            log_debug('loaded ' . $val->source->name());
                        } else {
                            if ($val->source->id() <> $val->source->id()) {
                                log_debug('load id ' . $val->source->id());
                                $val->load_source();
                                log_debug('loaded ' . $val->source->name());
                            }
                        }
                        $result[] = $val->source;
                        $src_ids[] = $val->source->id();
                        log_debug('added ' . $val->source->name());
                    }
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
     * @param phrase_list|null $time_lst list of time phrases to filter only by these times
     * @returns value_list that contains only values that match the time word list
     */
    function filter_by_time(?phrase_list $time_lst): value_list
    {
        log_debug();
        $lib = new library();
        $val_lst = array();
        foreach ($this->lst() as $val) {
            // only include time specific value
            $time_list = $val->phrase_list()->time_list();
            if ($time_list->count() > 0) {
                foreach ($time_list->lst() as $phr) {
                    // only include values within the specific time periods
                    if (in_array($phr->id(), $time_lst->ids())) {
                        $val_lst[] = $val;
                        log_debug('include ' . $val->name());
                    } else {
                        log_debug('excluded ' . $val->name() . ' because outside the specified time periods');
                    }
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
     * @returns value_list that contains only values that match at least one phrase from the phrase list
     */
    function filter_by_phrase_lst(phrase_list $phr_lst): value_list
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
     * select only the values
     *
     * @param phrase $phr
     * @return $this
     */
    function filter_by_phrase(phrase $phr): value_list
    {
        $phr_lst = new phrase_list($this->user());
        $phr_lst->add($phr);
        return $this->filter_by_phrase_lst($phr_lst);
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
    function get_by_grp(group $grp)
    {

        $found = false;
        $result = null;
        foreach ($this->lst() as $val) {
            if (!$found) {
                if ($val->grp()->id() == $grp->id()) {
                    $found = true;
                    $result = $val;
                }
            }
        }

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

    /**
     * get the values missing in the given list
     * @param value_list $val_lst
     * @return value_list
     */
    function diff(value_list $val_lst): value_list
    {
        $result = new value_list($this->user());
        foreach ($this->lst() as $val) {
            if ($val_lst->get_by_grp($val->grp()) == null) {
                $result->add($val);
            }
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
                log_err("The phrase group for value " . $val->id() . " cannot be loaded.", "value_list->phrase_groups");
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
            $val->load_by_id($db_val[value::FLD_ID]);
            if (!$val->check()) {
                $result = false;
            }
            log_debug($lib->dsp_count($this->lst()));
        }
        log_debug($lib->dsp_count($this->lst()));
        return $result;
    }

    /*
    private function common_phrases(): phrase_list
    {

    }
    */

    /*
     * save
     */

    function save(): user_message
    {
        $usr_msg = new user_message();

        if ($this->is_empty()) {
            log_info('no values to save');
        } else {
            // if the group id is not yet set
            $this->set_grp_ids();

            // load the values already in the database
            $grp_lst = $this->grp_ids();
            $db_lst = new value_list($this->user());
            $db_lst->load_by_ids($grp_lst->ids());

            // insert the new values
            foreach ($this->lst() as $val) {
                if ($val->value() != null) {
                    $usr_msg->add($val->save());
                }
            }

            // update the existing values

            // TODO loop over the values and check if all needed functions exist
            //      create the missing functions
            //      create blocks of update function calls
        }

        return $usr_msg;
    }


    /**
     * delete all loaded values e.g. to delete all the values linked to a phrase
     * @return user_message
     */
    function del(): user_message
    {
        $usr_msg = new user_message();

        foreach ($this->lst() as $val) {
            $usr_msg->add($val->del());
        }
        return new user_message();
    }

}
