<?php

/*

    model/sandbox/sandbox_value_list.php - the superclass for the value and result lists
    ------------------------------------


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

    Copyright (c) 1995-2024 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace cfg\sandbox;

include_once MODEL_SANDBOX_PATH . 'sandbox_list.php';

include_once DB_PATH . 'sql_creator.php';
include_once DB_PATH . 'sql_db.php';
include_once DB_PATH . 'sql_field_list.php';
include_once DB_PATH . 'sql_par.php';
include_once DB_PATH . 'sql_par_type.php';
include_once DB_PATH . 'sql_type_list.php';
include_once MODEL_FORMULA_PATH . 'formula.php';
//include_once MODEL_GROUP_PATH . 'group.php';
//include_once MODEL_GROUP_PATH . 'group_id.php';
//include_once MODEL_GROUP_PATH . 'result_id.php';
//include_once MODEL_PHRASE_PATH . 'phrase.php';
//include_once MODEL_PHRASE_PATH . 'phrase_list.php';
//include_once MODEL_RESULT_PATH . 'result.php';
//include_once MODEL_RESULT_PATH . 'result_list.php';
include_once MODEL_USER_PATH . 'user.php';
//include_once MODEL_VALUE_PATH . 'value.php';
//include_once MODEL_VALUE_PATH . 'value_base.php';
//include_once MODEL_VALUE_PATH . 'value_list.php';
include_once SHARED_PATH . 'library.php';

use cfg\db\sql_creator;
use cfg\db\sql_db;
use cfg\db\sql_field_list;
use cfg\db\sql_par;
use cfg\db\sql_par_type;
use cfg\db\sql_type_list;
use cfg\formula\formula;
use cfg\group\group;
use cfg\group\group_id;
use cfg\group\result_id;
use cfg\phrase\phrase;
use cfg\phrase\phrase_list;
use cfg\result\result;
use cfg\result\result_list;
use cfg\user\user;
use cfg\value\value;
use cfg\value\value_base;
use cfg\value\value_list;
use shared\library;

class sandbox_value_list extends sandbox_list
{

    /*
     * load
     */

    /**
     * if $or is false or null
     * load a list of values or results that are linked to each phrase of the given list
     * e.g. for "city", "inhabitants" and "increase" all yearly increases of city inhabitants are returned
     *      to get the inhabitants of the cities itself first get a phrase list of all cities
     *
     * if $or is true
     * load a list of values that are related to at least one phrase of the given list
     *  e.g. for "Zurich (city)" and "Geneva (city)" all values related to the two cities are returned
     *
     *  TODO use order by in query
     *  TODO use limit and page in query
     *  TODO replace return bool with user message e.g. to be able to return to the user
     *       "no values found, click here to search for values linked to any word of the list"
     *       or "... click here for related words"
     *
     * @param phrase_list $phr_lst phrase list to which all related values should be loaded
     * @param string $class the value or result class name
     * @param bool $or if true all values are returned that are linked to any phrase of the list
     * @param int $limit the number of values that should be loaded at once
     * @param int $page the offset for the limit
     * @return bool true if at least one result have been loaded
     */
    function load_by_phr_lst_multi(
        phrase_list $phr_lst,
        string      $class = value::class,
        bool        $or = false,
        int         $limit = sql_db::ROW_LIMIT,
        int         $page = 0
    ): bool
    {
        global $db_con;

        if ($phr_lst->is_empty()) {
            log_warning("At lease one phrase should be given to load a value list");
        } else {
            log_debug($phr_lst->dsp_id());
        }
        $sc = $db_con->sql_creator();
        $qp = $this->load_sql_by_phr_lst_multi($sc, $phr_lst, $class, false, $or, $limit, $page);
        return $this->load($qp);
    }

    /**
     * create an SQL statement to retrieve a list of values by a list of phrases from the database
     * return all values that match at least one phrase of the list
     * TODO add ORDER BY (relevance of value)
     * TODO use LIMIT and PAGE
     *
     * @param sql_creator $sc with the target db_type set
     * @param phrase_list $phr_lst phrase list to which all related values should be loaded
     * @param string $class the value or result class name
     * @param bool $usr_tbl true if only the user overwrites should be loaded
     * @param bool $or true if all values related to any phrase of the list should be loaded
     * @param int $limit the number of values that should be loaded at once
     * @param int $page the offset for the limit
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_phr_lst_multi(
        sql_creator $sc,
        phrase_list $phr_lst,
        string      $class = value::class,
        bool        $usr_tbl = false,
        bool        $or = false,
        int         $limit = sql_db::ROW_LIMIT,
        int         $page = 0
    ): sql_par
    {
        // differences between value and result list
        $list_class = value_list::class;
        $tbl_lst = value_base::TBL_LIST;
        if ($class !== value::class) {
            $list_class = result_list::class;
            $tbl_lst = result::TBL_LIST;
        }

        $lib = new library();
        $qp = new sql_par($class);
        $name_ext = 'phr_lst';
        if ($usr_tbl) {
            $name_ext .= '_usr';
        }
        if ($or) {
            $name_ext .= '_all';
        }
        $name_count = '_p' . $phr_lst->count();
        $qp->name = $lib->class_to_name($list_class) . '_by_' . $name_ext . $name_count;
        $par_types = array();

        // prepare adding the parameters in order of expected usage
        $par_pos = $sc->par_count();
        $pos_phr_lst = [];
        $pos_grp_lst = [];

        // add the single phrase parameter
        foreach ($phr_lst->lst() as $phr) {
            $pos_phr_lst[] = $par_pos;
            $par_pos++;
            $par_name = $sc->par_name($par_pos);
            $spt = sql_par_type::INT_SAME;
            if ($or) {
                $spt = sql_par_type::INT_SAME_OR;
            }
            $sc->add_where_par(phrase::FLD_ID, $phr->id(), $spt, '', $par_name);
        }

        // add the phrase group parameter
        foreach ($phr_lst->lst() as $phr) {
            $pos_grp_lst[] = $par_pos;
            $par_pos++;
            $par_name = $sc->par_name($par_pos);
            $spt = sql_par_type::LIKE;
            if ($or) {
                $spt = sql_par_type::LIKE_OR;
            }
            $grp_id = new group_id();
            $sc->add_where_par(group::FLD_ID, $grp_id->int2alpha_num($phr->id()), $spt, '', $par_name);
        }

        // add the user parameter
        $pos_usr = $par_pos;
        $par_pos++;
        $par_name = $sc->par_name($par_pos);
        $sc->add_where_par(user::FLD_ID, $this->user()->id(), sql_par_type::INT, '', $par_name);

        // remember the parameters
        $par_lst = clone $sc->par_list();

        // loop over the possible tables where the value might be stored in this pod
        foreach ($tbl_lst as $tbl_typ) {
            $sc->reset();
            $sc->set_par_list($par_lst);
            $qp_tbl = $this->load_sql_by_phr_lst_single(
                $sc, $class, $or, $tbl_typ,
                $pos_phr_lst, $pos_grp_lst, $pos_usr);
            $qp->merge($qp_tbl);
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
     * @param sql_creator $sc with the target db_type set
     * @param string $class the value or result class name
     * @param bool $or true if all values related to any phrase of the list should be loaded
     * @param array $sc_par_lst the parameters for the sql statement creation
     * @param array $phr_pos_lst list of array keys of the query parameter for the phrase id
     * @param array $grp_pos_lst list of array keys of the query parameter for the phrase id as group id
     * @param int $usr_pos the array key of the query parameter for the user id
     * @param int $frm_pos the array key of the query parameter for the user id
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_phr_lst_single(
        sql_creator $sc,
        string      $class,
        bool        $or,
        array       $sc_par_lst,
        array       $phr_pos_lst,
        array       $grp_pos_lst,
        int         $usr_pos,
        int         $frm_pos = 0
    ): sql_par
    {
        $qp = $this->load_sql_init(
            $sc, $class, 'phr',
            $sc_par_lst, $sc->par_list(), $usr_pos);
        if ($this->is_prime($sc_par_lst)) {
            $max_phr = group_id::PRIME_PHRASES_STD;
            if (($class == result::class
                    or $class == result_list::class) and $this->is_std($sc_par_lst)) {
                $max_phr = result_id::PRIME_PHRASES_STD;
                if ($frm_pos != 0) {
                    $sc->add_where_no_par(
                        '', formula::FLD_ID, sql_par_type::INT_SAME, $frm_pos);
                }
            }
            $this->load_sql_set_phrase_fields($sc, $phr_pos_lst, $or, $max_phr);
        } elseif ($this->is_main($sc_par_lst)) {
            // only for results
            $max_phr = result_id::MAIN_SOURCE_PHRASES + result_id::MAIN_RESULT_PHRASES;
            if ($this->is_std($sc_par_lst)) {
                $max_phr += result_id::MAIN_PHRASES_STD;
            } else {
                $max_phr += result_id::MAIN_PHRASES;
            }
            if ($frm_pos != 0) {
                $sc->add_where_no_par(
                    '', formula::FLD_ID, sql_par_type::INT_SAME, $frm_pos);
            }
            $this->load_sql_set_phrase_fields($sc, $phr_pos_lst, $or, $max_phr);
        } else {
            foreach ($grp_pos_lst as $grp_pos) {
                $spt = sql_par_type::LIKE;
                if ($or) {
                    $spt = sql_par_type::LIKE_OR;
                }
                $sc->add_where_no_par('', group::FLD_ID, $spt, $grp_pos);
            }
        }
        $qp->sql = $sc->sql(0, true, false);
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * add the single phrase fields to the sql creator object
     * TODO make $par_pos unnecessary
     *
     * @param sql_creator $sc with the target db_type set
     * @param array $phr_pos_lst to set a fixed name for the parameter
     * @param bool $or true if all values related to any phrase of the list should be loaded
     * @param int $max_phr the maximal number of phrases allowed for this table
     */
    private function load_sql_set_phrase_fields(
        sql_creator $sc,
        array       $phr_pos_lst,
        bool        $or,
        int         $max_phr
    ): void
    {
        $used_or = true; // the first phrase is always or to force staring with brakest
        foreach ($phr_pos_lst as $phr_pos) {
            $spt = sql_par_type::INT_SAME;
            if ($used_or) {
                $spt = sql_par_type::INT_SAME_OR;
            }
            for ($i = 1; $i <= $max_phr; $i++) {
                $sc->add_where_no_par('', phrase::FLD_ID . '_' . $i,
                    $spt, $phr_pos);
                $spt = sql_par_type::INT_SAME_OR;
            }
            $used_or = $or; // the following where connection should use the user selected logical connection
        }
    }

    /**
     * set the SQL query parameters to load a list of values or results
     * set the fields for a union select of all possible tables
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $class the value or result class name
     * @param string $query_name the name extension to make the query name unique
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_init(
        sql_creator    $sc,
        string         $class,
        string         $query_name,
        array          $tbl_types,
        sql_field_list $par_lst,
        ?int           $usr_pos = null
    ): sql_par
    {
        $is_std = $this->is_std($tbl_types);
        $is_prime = $this->is_prime($tbl_types);
        $is_main = $this->is_main($tbl_types);

        // differences between value and result list
        $list_class = value_list::class;
        $fld_lst = value_base::FLD_NAMES;
        $fld_lst_std = value_base::FLD_NAMES_STD;
        $fld_lst_dummy = value_base::FLD_NAMES_STD_DUMMY;
        $fld_lst_usr_ex_std = value_base::FLD_NAMES_DATE_USR_EX_STD;
        $fld_lst_usr_num_ex_std = value_base::FLD_NAMES_NUM_USR_EX_STD;
        $fld_lst_usr_num = value_base::FLD_NAMES_NUM_USR;
        $fld_lst_usr_only = value_base::FLD_NAMES_USR_ONLY;
        if ($class != value::class) {
            $list_class = result_list::class;
            $fld_lst_std = result::FLD_NAMES_STD;
            if ($is_std) {
                $fld_lst = result::FLD_NAMES_ALL;
                if ($is_prime or $is_main) {
                    $fld_lst_dummy = result::FLD_NAMES_STD_DUMMY;
                } else {
                    $fld_lst_dummy = result::FLD_NAMES_DUMMY;
                }
            } else {
                $fld_lst = result::FLD_NAMES_NON_STD;
                $fld_lst_dummy = result::FLD_NAMES_DUMMY;
            }
            $fld_lst_usr_ex_std = result::FLD_NAMES_DATE_USR_EX_STD;
            $fld_lst_usr_num_ex_std = result::FLD_NAMES_NUM_USR_EX_STD;
            $fld_lst_usr_num = result::FLD_NAMES_NUM_USR;
            $fld_lst_usr_only = result::FLD_NAMES_USR_ONLY;
        }

        $tbl_ext = $this->table_extension($tbl_types);
        $qp = new sql_par($list_class, new sql_type_list($tbl_types), $tbl_ext);
        $qp->name .= $query_name;

        $sc->set_class($class, new sql_type_list(), $tbl_ext);
        if ($par_lst->count() > 0) {
            $sc->set_par_list($par_lst);
        }
        // overwrite the standard id field name (value_id) with the main database id field for values "group_id"
        $val = new value($this->user());
        if ($is_prime) {
            $sc->set_id_field_dummy($val->id_field_group(true));
            if ($class == value::class) {
                $sc->set_id_field($val->id_fields_prime());
            } else {
                $num_of_main_phrases = result_id::PRIME_SOURCE_PHRASES + result_id::PRIME_RESULT_PHRASES;
                if ($this->is_std($tbl_types)) {
                    $num_of_main_phrases += result_id::PRIME_PHRASES_STD;
                } else {
                    $num_of_main_phrases += result_id::PRIME_PHRASES;
                }
                $sc->set_id_field($val->id_fields_prime(1, $num_of_main_phrases));
                $sc->set_id_field_num_dummy($val->id_fields_prime($num_of_main_phrases + 1, result_id::MAIN_PHRASES_ALL));
            }
        } elseif ($is_main) {
            $sc->set_id_field_dummy($val->id_field_group(true));
            $num_of_main_phrases = result_id::MAIN_SOURCE_PHRASES + result_id::MAIN_RESULT_PHRASES;
            if ($this->is_std($tbl_types)) {
                $num_of_main_phrases += result_id::MAIN_PHRASES_STD;
            } else {
                $num_of_main_phrases += result_id::MAIN_PHRASES;
            }
            $sc->set_id_field($val->id_fields_prime(1, $num_of_main_phrases));
            $sc->set_id_field_num_dummy($val->id_fields_prime($num_of_main_phrases + 1, result_id::MAIN_PHRASES_ALL));
        } else {
            $sc->set_id_field($val->id_field_group());
            if ($is_std) {
                $sc->set_id_field_usr_dummy($val->id_field_group(false, true));
            }
            if ($class == value::class) {
                $sc->set_id_field_num_dummy($val->id_fields_prime());
            } else {
                $sc->set_id_field_num_dummy($val->id_fields_prime(1, result_id::MAIN_PHRASES_ALL));
            }
        }
        $sc->set_name($qp->name);

        $sc->set_usr($this->user()->id());
        if ($is_std) {
            // TODO replace next line with union select field name synchronisation
            $sc->set_fields_num_dummy($fld_lst_dummy);
            if ($class == value::class) {
                $sc->set_fields($fld_lst_std);
            } else {
                if ($is_prime or $is_main) {
                    $sc->set_fields(array_merge(result::FLD_NAMES_STD_NON_DUMMY, $fld_lst_std));
                } else {
                    $sc->set_fields($fld_lst_std);
                }
            }
            $sc->set_fields_date_dummy($fld_lst_usr_ex_std);
            $sc->set_fields_dummy(array_merge($fld_lst_usr_num_ex_std, $fld_lst_usr_only));
        } else {
            $sc->set_fields($fld_lst);
            $usr_par_name = $sc->par_name(1);
            if ($usr_pos != null) {
                $usr_par = $par_lst->get($usr_pos);
                $usr_par_name = $usr_par->name;
            }
            $sc->set_usr_num_fields($fld_lst_usr_num, true, $usr_par_name);
            $sc->set_usr_only_fields($fld_lst_usr_only);
        }
        return $qp;
    }

}


