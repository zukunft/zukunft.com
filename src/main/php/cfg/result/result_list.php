<?php

/*

    model/formula/result_list.php - a list of formula results
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

    Copyright (c) 1995-2023 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace cfg\result;

include_once MODEL_SANDBOX_PATH . 'sandbox_value_list.php';
include_once DB_PATH . 'sql_creator.php';
include_once DB_PATH . 'sql_db.php';
include_once DB_PATH . 'sql_field_list.php';
include_once DB_PATH . 'sql_par.php';
include_once DB_PATH . 'sql_par_type.php';
include_once DB_PATH . 'sql_type.php';
include_once DB_PATH . 'sql_type_list.php';
include_once MODEL_FORMULA_PATH . 'formula.php';
include_once MODEL_FORMULA_PATH . 'formula_db.php';
include_once MODEL_GROUP_PATH . 'group.php';
include_once MODEL_GROUP_PATH . 'group_id.php';
include_once MODEL_GROUP_PATH . 'group_list.php';
include_once MODEL_HELPER_PATH . 'data_object.php';
include_once MODEL_PHRASE_PATH . 'phrase.php';
include_once MODEL_PHRASE_PATH . 'phrase_list.php';
include_once MODEL_PHRASE_PATH . 'term.php';
include_once MODEL_PHRASE_PATH . 'term_list.php';
include_once MODEL_SYSTEM_PATH . 'job.php';
include_once MODEL_SYSTEM_PATH . 'job_list.php';
include_once MODEL_WORD_PATH . 'triple.php';
include_once MODEL_WORD_PATH . 'triple_db.php';
include_once MODEL_USER_PATH . 'user.php';
include_once MODEL_USER_PATH . 'user_list.php';
include_once MODEL_USER_PATH . 'user_message.php';
include_once MODEL_VALUE_PATH . 'value_base.php';
include_once MODEL_WORD_PATH . 'word.php';
include_once MODEL_WORD_PATH . 'word_db.php';
include_once SHARED_ENUM_PATH . 'messages.php';
include_once SHARED_TYPES_PATH . 'api_type_list.php';
include_once SHARED_PATH . 'library.php';

use cfg\formula\formula_db;
use cfg\helper\data_object;
use cfg\sandbox\sandbox_value_list;
use cfg\db\sql_creator;
use cfg\db\sql_db;
use cfg\db\sql_field_list;
use cfg\db\sql_par;
use cfg\db\sql_par_type;
use cfg\db\sql_type;
use cfg\db\sql_type_list;
use cfg\formula\formula;
use cfg\group\group;
use cfg\group\group_id;
use cfg\group\group_list;
use cfg\phrase\phrase;
use cfg\phrase\phrase_list;
use cfg\phrase\term;
use cfg\phrase\term_list;
use cfg\system\job;
use cfg\system\job_list;
use cfg\word\triple;
use cfg\user\user;
use cfg\user\user_list;
use cfg\user\user_message;
use cfg\value\value_base;
use cfg\word\triple_db;
use cfg\word\word;
use cfg\word\word_db;
use shared\enum\messages as msg_id;
use shared\library;
use Exception;

class result_list extends sandbox_value_list
{

    /*
     * load
     */

    /**
     * if $or is false or null
     * load a list of result that are linked to each phrase of the given list
     * e.g. for "city", "inhabitants" and "increase" the yearly increase of the city inhabitants are returned
     *      to get the inhabitants of the cities itself first get a phrase list of all cities
     *
     * if $or is true
     * load a list of result that are related to at least one phrase of the given list
     *  e.g. for "Zurich (city)" and "Geneva (city)" all calculated values related to the two cities are returned
     *
     *  TODO use order by in query
     *  TODO use limit and page in query
     *
     * @param phrase_list $phr_lst phrase list to which all related results should be loaded
     * @param bool $or if true all results are returned that are linked to any phrase of the list
     * @param int $limit the number of results that should be loaded at once
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
        return parent::load_by_phr_lst_multi($phr_lst, result::class, $or, $limit, $page);
    }

    /**
     * load a list of results linked to a formula
     * used to detect which results needs to be updated in case of a formula change
     * TODO check if needed that the standard results can also be searched by formula
     *      advantage is the higher speed in case of a formula update
     *      disadvantage is that it is not possible to use a pure key value table
     *      -> measure it based on real life data
     *      -> a solution could be to include the source group and the formula in the result group id
     *
     * @param formula $frm a named object used for selection e.g. a formula
     * @return bool true if loading has been successful
     */
    function load_by_frm(formula $frm): bool
    {
        global $db_con;

        $sc = $db_con->sql_creator();
        $qp = $this->load_sql_by_frm($sc, $frm);
        return $this->load($qp);
    }

    /**
     * load a list of results based on the given phrases
     *
     * @param phrase_list $phr_lst to select the results that are based on any or all of these phase values
     * @param bool $or if true all results are returned that are based on any phrase of the list
     * @param int $limit the number of results that should be loaded at once
     * @param int $page the offset for the limit
     * @return bool true if loading has been successful
     */
    function load_by_src(
        phrase_list $phr_lst,
        bool        $or = false,
        int         $limit = sql_db::ROW_LIMIT,
        int         $page = 0
    ): bool
    {
        global $db_con;

        if ($phr_lst->is_empty()) {
            log_warning("At lease one phrase should be given to load a value list");
        }
        $sc = $db_con->sql_creator();
        $qp = $this->load_sql_by_src($sc, $phr_lst, $or, $limit, $page);
        return $this->load($qp);
    }

    /**
     * load the result objects by the given complete id list from the database
     *
     * @param array $ids result ids that should be loaded
     * @return bool true if at least one phrase has been loaded
     */
    function load_by_ids(array $ids): bool
    {
        global $db_con;
        $qp = $this->load_sql_by_ids($db_con->sql_creator(), $ids);
        return $this->load($qp);
    }

    // internal load

    /**
     * load a result list base on the given query parameters
     *
     * @param sql_par $qp the query parameters that should be used to get the data from the database
     * @param bool $load_all
     * @return bool
     */
    function load(sql_par $qp, bool $load_all = false): bool
    {
        global $db_con;
        $result = false;
        if ($qp->name != '') {
            $db_rows = $db_con->get($qp);
            if ($db_rows != null) {
                foreach ($db_rows as $db_row) {
                    $res = new result($this->user());
                    $res->row_mapper($db_row);
                    $this->add_obj($res);
                    $result = true;
                }
            }
        }
        return $result;
    }

    /**
     * load a list of results that are linked to each phrase of the given list
     * e.g. for "city", "inhabitants" and "increase" all yearly increases of city inhabitants are returned
     *      to get the inhabitants of the cities itself first get a phrase list of all cities
     *
     * if $or is true
     * load a list of results that are related to at least one phrase of the given list
     *  e.g. for "Zurich (city)" and "Geneva (city)" all results related to the two cities are returned
     *
     *  TODO use order by in query
     *  TODO use limit and page in query
     *
     * @param sql_creator $sc with the target db_type set
     * @param phrase_list $phr_lst phrase list to which all related results should be loaded
     * @param bool $usr_tbl true if only the user overwrites should be loaded
     * @param bool $or if true all results are returned that are linked to any phrase of the list
     * @param int $limit the number of results that should be loaded at once
     * @param int $page the offset for the limit
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_phr_lst(
        sql_creator $sc,
        phrase_list $phr_lst,
        bool        $usr_tbl = false,
        bool        $or = false,
        int         $limit = sql_db::ROW_LIMIT,
        int         $page = 0
    ): sql_par
    {
        return parent::load_sql_by_phr_lst_multi($sc, $phr_lst, result::class, $usr_tbl, $or, $limit, $page);
    }

    /**
     * create an SQL statement to retrieve a list of results linked to a formula from the database
     * @param sql_creator $sc
     * @param formula $frm
     * @return sql_par
     */
    function load_sql_by_frm(sql_creator $sc, formula $frm): sql_par
    {
        $qp = new sql_par(result_list::class);
        $qp->name .= 'frm';
        $par_types = array();
        foreach (result_db::TBL_LIST_EX_STD as $tbl_typ) {
            $qp_tbl = $this->load_sql_by_frm_single($sc, $frm, $tbl_typ);

            $qp->merge($qp_tbl);
        }
        $qp->sql = $sc->prepare_sql($qp->sql, $qp->name, $par_types);
        return $qp;
    }

    /**
     * create an SQL statement to retrieve a list of results based one of the given phrases from the database
     * similar to load_sql_by_phr_lst_multi of sandbox_value_list
     * TODO use a mixed id with a formula id, 2 or 6 phrase ids for the source and result value and 2 for the result only
     * TODO add ORDER BY (relevance of value)
     * TODO use LIMIT and PAGE
     *
     * @param sql_creator $sc with the target db_type set
     * @param phrase_list $phr_lst phrase list to which all related results should be loaded
     * @param bool $usr_tbl true if only the user overwrites should be loaded
     * @param bool $or true if all results related to any phrase of the list should be loaded
     * @param int $limit the number of results that should be loaded at once
     * @param int $page the offset for the limit
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_src(
        sql_creator $sc,
        phrase_list $phr_lst,
        bool        $usr_tbl = false,
        bool        $or = false,
        int         $limit = sql_db::ROW_LIMIT,
        int         $page = 0
    ): sql_par
    {
        $lib = new library();
        $qp = new sql_par(result::class);
        $name_ext = 'src_phr_lst';
        if ($usr_tbl) {
            $name_ext .= '_usr';
        }
        if ($or) {
            $name_ext .= '_all';
        }
        $name_count = '_p' . $phr_lst->count();
        $qp->name = $lib->class_to_name(result_list::class) . '_by_' . $name_ext . $name_count;
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
        foreach (result_db::TBL_LIST as $tbl_typ) {
            $sc->reset();
            $sc->set_par_list($par_lst);
            $qp_tbl = $this->load_sql_by_phr_lst_single(
                $sc, result::class, $or, $tbl_typ,
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
     * create an SQL statement to retrieve a list of results linked to a phrase from the database
     * from a single table
     *     *
     * @param sql_creator $sc with the target db_type set
     * @param formula $frm if set to get all results for this phrase
     * @param array $sc_par_lst the parameters for the sql statement creation
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_frm_single(sql_creator $sc, formula $frm, array $sc_par_lst): sql_par
    {
        $qp = $this->load_sql_init(
            $sc,
            result::class,
            'frm',
            $sc_par_lst,
            new sql_field_list(),
            new sql_type_list()
        );
        $sc->add_where(formula_db::FLD_ID, $frm->id());
        $qp->sql = $sc->sql(0, true, false);
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a list of result objects by the complete id from the database
     * TODO Prio 1 load also from prime and big tables
     *
     * @param sql_creator $sc with the target db_type set
     * @param array $ids result ids that should be loaded
     * @param int $limit the number of rows to return
     * @param int $offset jump over these number of pages
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_ids(
        sql_creator $sc,
        array       $ids,
        int         $limit = 0,
        int         $offset = 0
    ): sql_par
    {
        $qp = $this->load_sql($sc, 'ids');
        $sc->add_where(result_db::FLD_ID, $ids);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }


    /*
     * to review
     */

    /**
     * the common query parameter to get a list of results
     *
     * @param sql_creator $sc the sql creator instance with the target db_type already set
     * @param string $query_name the name extension to make the query name unique
     * @param sql_type $tbl_typ the table extension to force the sub table selection
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    private function load_sql(sql_creator $sc, string $query_name, sql_type $tbl_typ = sql_type::MOST): sql_par
    {
        $qp = new sql_par(self::class, new sql_type_list([$tbl_typ]));
        $qp->name .= $query_name;

        $sc->set_class(result::class, new sql_type_list(), $tbl_typ->extension());
        // overwrite the standard id field name (result_id) with the main database id field for results "group_id"
        $res = new result($this->user());
        $sc->set_id_field($res->id_field_list($tbl_typ));
        $sc->set_name($qp->name);

        $sc->set_usr($this->user()->id());
        $sc->set_fields(result_db::FLD_NAMES);
        return $qp;
    }

    /**
     * load a list of results by the phrase group e.g. the results of other users
     *
     * @param sql_creator $sc the sql creator instance with the target db_type already set
     * @param group $grp the group of phrases to select the results
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_grp(sql_creator $sc, group $grp): sql_par
    {
        $ext = $grp->table_type();
        $qp = $this->load_sql($sc, 'grp', $ext);
        if ($grp->is_prime()) {
            $fields = $grp->id_names();
            $values = $grp->id_lst();
            $pos = 0;
            foreach ($fields as $field) {
                $sc->add_where($field, $values[$pos], sql_par_type::INT_SMALL);
                $pos++;
            }
        } elseif ($grp->is_big()) {
            $sc->add_where(group::FLD_ID, $grp->id(), sql_par_type::TEXT);
        } else {
            $sc->add_where(group::FLD_ID, $grp->id());
        }
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();
        return $qp;
    }

    /**
     * load a list of results by the source group e.g. to get the dependent results
     *
     * @param sql_creator $sc the sql creator instance with the target db_type already set
     * @param group $grp the group of phrases to select the results
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_src_grp(sql_creator $sc, group $grp): sql_par
    {
        $qp = $this->load_sql($sc, 'src_grp');
        if ($grp->is_prime()) {
            $sc->add_where(result_db::FLD_SOURCE_GRP, $grp->id(), sql_par_type::TEXT);
        } elseif ($grp->is_big()) {
            $sc->add_where(result_db::FLD_SOURCE_GRP, $grp->id(), sql_par_type::TEXT);
        } else {
            $sc->add_where(result_db::FLD_SOURCE_GRP, $grp->id());
        }
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();
        return $qp;
    }

    /**
     * create the SQL statement to load the results created by the given formula
     *
     * @param string $query_name symbol by what the results are selected e.g. frm
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */

    private function load_sql_init_query_par(string $query_name): sql_par
    {
        $qp = new sql_par(result::class);
        $lib = new library();
        // TODO shorten the code
        $ext_lst = array();
        foreach (result_db::TBL_EXT_LST as $ext) {
            $ext_lst[] = $ext->extension();
        }
        $qp->name =
            $lib->class_to_name(self::class) .
            implode("", $ext_lst) .
            '_by_' . $query_name;
        return $qp;
    }

    /**
     * create the SQL to load a list of results link to
     * a formula
     * a phrase group
     *   either of the source or the result
     *   and with or without time selection
     * a word or a triple
     *
     * TODO: split to single functions and deprecate
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param object $obj a named object used for selection e.g. a formula
     * @param bool $by_source set to true to force the selection e.g. by source phrase group id
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_obj_old(sql_db $db_con, object $obj, bool $by_source = false): sql_par
    {
        $qp = new sql_par(self::class);
        $sql_by = '';
        if ($obj->id() > 0) {
            if (get_class($obj) == formula::class) {
                $sql_by .= formula_db::FLD_ID;
            } elseif (get_class($obj) == group::class) {
                if ($by_source) {
                    $sql_by .= result_db::FLD_SOURCE_GRP;
                } else {
                    $sql_by .= group::FLD_ID;
                }
            } elseif (get_class($obj) == word::class) {
                $sql_by .= word_db::FLD_ID;
            } elseif (get_class($obj) == triple::class) {
                $sql_by .= triple_db::FLD_ID;
            }
        }
        if ($sql_by == '') {
            log_err('Either the formula id or the phrase group id and the user (' . $this->user()->id() .
                ') must be set to load a ' . self::class, self::class . '->load_sql');
            $qp->name = '';
        } else {
            $db_con->set_class(result::class);
            // overwrite the standard id field name (result_id) with the main database id field for results "group_id"
            $res = new result($this->user());
            $db_con->set_id_field($res->id_field());
            $qp->name .= $sql_by;
            $db_con->set_name($qp->name);
            $db_con->set_fields(result_db::FLD_NAMES);
            $db_con->set_usr($this->user()->id());
            if ($obj->id() > 0) {
                if (get_class($obj) == formula::class) {
                    $db_con->add_par(sql_par_type::INT, $obj->id());
                    $qp->sql = $db_con->select_by_field_list(array(formula_db::FLD_ID));
                } elseif (get_class($obj) == group::class) {
                    $db_con->add_par(sql_par_type::INT, $obj->id());
                    $link_fields = array();
                    if ($by_source) {
                        $link_fields[] = result_db::FLD_SOURCE_GRP;
                    } else {
                        $link_fields[] = group::FLD_ID;
                    }
                    $qp->sql = $db_con->select_by_field_list($link_fields);
                } elseif (get_class($obj) == word::class) {
                    // TODO check if the results are still correct if the user has excluded the word
                    $db_con->add_par(sql_par_type::INT, $obj->id(), false, true);
                    // $db_con->set_join_fields(                        array(result_db::FLD_GRP),                        sql_db::TBL_GROUP_LINK,                        result_db::FLD_GRP,                        result_db::FLD_GRP);
                    $qp->sql = $db_con->select_by_field_list(array(word_db::FLD_ID));
                } elseif (get_class($obj) == triple::class) {
                    // TODO check if the results are still correct if the user has excluded the triple
                    $db_con->add_par(sql_par_type::INT, $obj->id(), false, true);
                    //$db_con->set_join_fields( array(result_db::FLD_GRP),sql_db::TBL_PHRASE_GROUP_TRIPLE_LINK,   result_db::FLD_GRP,         result_db::FLD_GRP);
                    $qp->sql = $db_con->select_by_field_list(array(triple_db::FLD_ID));
                }
            }
            $qp->par = $db_con->get_par();
        }

        return $qp;
    }

    /**
     * list of potential table extensions where a result may be saved
     *
     * @param array $ids value ids that should be selected
     * @return array with the unique table extension where the results of the given id list may be found of the group ids
     */
    private function table_extension_list(array $ids): array
    {
        $grp_id = new group_id();
        $tbl_ext_lst = array();
        foreach ($ids as $id) {
            $tbl_ext_lst[] = $grp_id->table_extension($id, true);
        }

        return array_unique($tbl_ext_lst);
    }

    /**
     * load a list of results linked to a phrase group
     *   either of the source or the result
     *   and with or without time selection
     *
     * @param group $grp the phrase group to select the results
     * @param bool $by_source set to true to force the selection e.g. by source phrase group id
     * @return bool true if value or phrases are found
     */
    function load_by_grp(group $grp, bool $by_source = false): bool
    {
        global $db_con;

        if (!$by_source) {
            $qp = $this->load_sql_by_grp($db_con->sql_creator(), $grp);
        } else {
            $qp = $this->load_sql_by_src_grp($db_con->sql_creator(), $grp);
        }
        return $this->load($qp);
    }

    /**
     * load a list of results linked to
     * a formula
     * a phrase group
     *   either of the source or the result
     *   and with or without time selection
     * a word or a triple
     *
     * @param object $obj a named object used for selection e.g. a formula
     * @param bool $by_source set to true to force the selection e.g. by source phrase group id
     * @return bool true if value or phrases are found
     */
    function load_by_obj(object $obj, bool $by_source = false): bool
    {
        global $db_con;

        $qp = $this->load_sql_by_obj_old($db_con, $obj, $by_source);
        return $this->load($qp);
    }


    /*
     * im- and export
     */

    /**
     * import a list of results from a JSON array object
     *
     * @param array $json_obj an array with the data of the json object
     * @param user $usr_req the user how has initiated the import mainly used to prevent any user to gain additional rights
     * @param data_object|null $dto cache of the objects imported until now for the primary references
     * @param object|null $test_obj if not null the unit test object to get a dummy seq id
     * @return user_message the status of the import and if needed the error messages that should be shown to the user
     */
    function import_obj(
        array        $json_obj,
        user         $usr_req,
        ?data_object $dto = null,
        object       $test_obj = null
    ): user_message
    {
        $usr_msg = new user_message();
        foreach ($json_obj as $res_json) {
            $res = new result($this->user());
            $usr_msg->add($res->import_obj($res_json, $usr_req, $dto, $test_obj));
            $this->add($res);
        }

        return $usr_msg;
    }


    /*
     * information
     */

    /**
     * reports the difference to the given result list as a human-readable messages
     * @param sandbox_value_list $val_lst the list of the object to compare with
     * @param msg_id $msg_missing the message id for a missing result
     * @param msg_id $msg_additional the message id for an additional result
     * @return user_message
     */
    function diff_msg(
        sandbox_value_list $val_lst,
        msg_id             $msg_missing = msg_id::RESULT_MISSING,
        msg_id             $msg_additional = msg_id::RESULT_ADDITIONAL,
    ): user_message
    {
        return parent::diff_msg($val_lst,
            msg_id::RESULT_MISSING,
            msg_id::RESULT_ADDITIONAL);
    }


    /*
     * display
     */

    /**
     * @param ?int $limit the max number of ids to show
     * @return string one string with all names of the list
     */
    function name(int $limit = null): string
    {
        global $debug;
        $lib = new library();

        $name_lst = array();
        if (!$this->is_empty()) {
            foreach ($this->lst() as $res) {
                $name_lst[] = $res->name();
            }
        }

        if ($debug > 10) {
            $result = '"' . implode('","', $name_lst) . '"';
        } else {
            $result = '"' . implode('","', array_slice($name_lst, 0, 7));
            if (count($name_lst) > 8) {
                $result .= ' ... total ' . $lib->dsp_count($this->lst());
            }
            $result .= '"';
        }
        return $result;
    }

    /**
     * return a list of the formula result names
     */
    function names(int $limit = null): array
    {
        $result = array();
        $lib = new library();
        if (!$this->is_empty()) {
            foreach ($this->lst() as $res) {
                $result[] = $res->name();

                // check user consistency (can be switched off once the program ist stable)
                if (!isset($res->usr)) {
                    log_err('The user of a formula result list element differs from the list user.', 'res_lst->names', 'The user of "' . $res->name() . '" is missing, but the list user is "' . $this->user()->name . '".', (new Exception)->getTraceAsString(), $this->user());
                } elseif ($res->usr <> $this->user()) {
                    log_err('The user of a formula result list element differs from the list user.', 'res_lst->names', 'The user "' . $res->usr->name . '" of "' . $res->name() . '" does not match the list user "' . $this->user()->name . '".', (new Exception)->getTraceAsString(), $this->user());
                }
            }
        }
        log_debug('res_lst->names (' . $lib->dsp_array($result) . ')');
        return $result;
    }


    /*
     * create functions - build new results
     */

    /*
     * TODO check
     * add all formula results to the list for ONE formula based on
     * - the word assigned to the formula ($phr_id)
     * - the word that are used in the formula ($frm_phr_ids)
     * - the formula ($frm_row) to provide parameters, but not for selection
     * - the user ($this->user()->id) to filter the results
     * and request on formula result for each word group
     * e.g. the formula is assigned to Company ($phr_id) and the "operating income" formula result should be calculated
     *      so sales and Cost are words of the formula
     *      if sales and Cost for 2016 and 2017 and EUR and CHF are in the database for one company (e.g. ABB)
     *      the "ABB" "operating income" for "2016" and "2017" should be calculated in "EUR" and "CHF"
     *      so the result would be to add 4 results to the list:
     *      1. calculate "operating income" for "ABB", "EUR" and "2016"
     *      2. calculate "operating income" for "ABB", "CHF" and "2016"
     *      3. calculate "operating income" for "ABB", "EUR" and "2017"
     *      4. calculate "operating income" for "ABB", "CHF" and "2017"
     * TODO: check if a value is used in the formula
     *       exclude the time word and if needed loop over the time words
     *       if the value has been update, create a calculation request
     * ex zuc_upd_lst_val
    function add_frm_val(int $phr_id, $frm_phr_ids, $frm_row, $usr_id)
    {
        $lib = new library();
        log_debug($phr_id . ',' . $lib->dsp_array($frm_phr_ids) . ',u' . $this->user()->id() . ')');

        global $debug;

        $result = array();

        // temp utils the call is reviewed
        $phr = new phrase($this->usr);
        $phr->load_by_id($phr_id);

        $val_lst = new value_list($this->usr);
        $value_lst = $val_lst->load_frm_related_grp_phrs($phr_id, $frm_phr_ids, $this->user()->id());

        foreach (array_keys($value_lst) as $val_id) {
            // maybe use for debugging
            if ($debug > 0) {
                $debug_txt = "";
                $debug_phr_ids = $value_lst[$val_id][1];
                foreach ($debug_phr_ids as $debug_phr_id) {
                    $debug_phr = new phrase($this->usr);
                    $debug_phr->load_by_id($debug_phr_id);
                    $debug_txt .= ", " . $debug_phr->name();
                }
            }
            log_debug('calc ' . $frm_row['formula_name'] . ' for ' . $phr->name() . ' (' . $phr_id . ')' . $debug_txt);

            // get the group words
            $phr_ids = $value_lst[$val_id][1];
            // add the formula assigned word if needed
            if (!in_array($phr_id, $phr_ids)) {
                $phr_ids[] = $phr_id;
            }

            // build the single calculation request
            $calc_row = array();
            $calc_row['usr_id'] = $this->user()->id();
            $calc_row['frm_id'] = $frm_row[formula_db::FLD_ID];
            $calc_row['frm_name'] = $frm_row[formula_db::FLD_NAME];
            $calc_row['frm_text'] = $frm_row[formula_db::FLD_FORMULA_TEXT];
            $calc_row['trm_ids'] = $phr_ids;
            $result[] = $calc_row;
        }

        log_debug('number of results added (' . $lib->dsp_count($result) . ')');
        return $result;
    }
*/

    /**
     * add all formula results to the list that may needs to be updated if a formula is updated for one user
     * TODO: only request the user specific calculation if needed
     */
    function frm_upd_lst_usr(
        formula $frm,
                $phr_lst_frm_assigned,
                $phr_lst_frm_used,
                $phr_grp_lst_used,
                $usr,
                $last_msg_time,
                $collect_pos): job_list
    {
        $lib = new library();
        log_debug('res_lst->frm_upd_lst_usr(' . $frm->name() . ',fat' . $phr_lst_frm_assigned->name() . ',ft' . $phr_lst_frm_used->name() . ',' . $usr->name . ')');
        $result = new job_list($usr);
        $added = 0;

        // TODO: check if the assigned words are different for the user

        // TODO: check if the formula words are different for the user

        // TODO: check if the assigned words, formula words OR the user has different values or results

        // TODO: filter the words if just a value has been updated
        /*    if (!empty($val_wrd_lst)) {
              zu_debug('res_lst->frm_upd_lst_usr -> update related words ('.implode(",",$val_wrd_lst).')');
              $used_word_ids = array_intersect($is_word_ids, array_keys($val_wrd_lst));
              zu_debug('res_lst->frm_upd_lst_usr -> needed words ('.implode(",",$used_word_ids).' instead of '.implode(",",$is_word_ids).')');
            } else {
              $used_word_ids = $is_word_ids;
            } */

        // create the calc request
        foreach ($phr_grp_lst_used->phr_lst_lst as $phr_lst) {
            // remove the formula words from the word group list
            log_debug('remove the formula words "' . $phr_lst->name() . '" from the request word list ' . $phr_lst->name());
            //$phr_lst->remove_wrd_lst($phr_lst_frm_used);
            $phr_lst->diff($phr_lst_frm_used);
            log_debug('removed ' . $phr_lst->name() . ')');

            // remove double requests

            if (!$phr_lst->is_empty()) {
                $calc_request = new job($usr);
                $calc_request->frm = $frm;
                $calc_request->phr_lst = $phr_lst;
                $result->add($calc_request);
                log_debug('request "' . $frm->name() . '" for "' . $phr_lst->name() . '"');
                $added++;
            }
        }

        // loop over the word categories assigned to the formulas
        // get the words where the formula is used including the based on the assigned word e.g. Company or year
        //$sql_result = zuf_wrd_lst ($frm_lst->ids, $this->user()->id());
        //zu_debug('res_lst->frm_upd_lst_usr -> number of formula assigned words '. mysqli_num_rows ($sql_result));
        //while ($frm_row = mysqli_fetch_array($sql_result, MySQLi_ASSOC)) {
        //zu_debug('res_lst->frm_upd_lst_usr -> formula '.$frm_row['formula_name'].' ('.$frm_row['resolved_text'].') linked to '.zut_name($frm_row['word_id'], $this->user()->id()));

        // also use the formula for all related words e.g. if the formula should be used for "Company" use it also for "ABB"
        //$is_word_ids = zut_ids_are($frm_row['word_id'], $this->user()->id()); // should later be taken from the original array to increase speed

        // include also the main word in the testing
        //$is_word_ids[] = $frm_row['word_id'];

        /*
        $used_word_lst = New word_list;
        $used_word_lst->ids    = $used_word_ids;
        $used_word_lst->usr_id = $this->user()->id();
        $used_word_lst->load ();

        // loop over the words assigned to the formulas
        zu_debug('the formula "'.$frm_row['formula_name'].'" is assigned to "'.zut_name($frm_row['word_id'], $this->user()->id).'", which are '.implode(",",$used_word_lst->names_linked()));
        foreach ($used_word_ids AS $phr_id) {
          $special_frm_phr_ids = array();

          if (zuf_has_verb($frm_row[api::URL_VAR_USER_EXPRESSION], $this->user()->id)) {
            // special case
            zu_debug('res_lst->frm_upd_lst_usr -> formula has verb ('.$frm_row[api::URL_VAR_USER_EXPRESSION].')');
          } else {

            // include all results of the underlying formulas
            $all_frm_ids = zuf_frm_ids ($frm_row[api::URL_VAR_USER_EXPRESSION], $this->user()->id());

            // get fixed / special formulas
            $frm_ids = array();
            foreach ($all_frm_ids as $chk_frm_id) {
              if (zuf_is_special ($chk_frm_id, $this->user()->id)) {
                $special_frm_phr_ids = $frm_upd_lst_frm_special ($chk_frm_id, $frm_row[api::URL_VAR_USER_EXPRESSION], $this->user()->id, $phr_id);

                //get all values related to the words
              } else {
                $frm_ids[] = $chk_frm_id;
              }
            }

            // include the results of the underlying formulas, but only the once related to one of the words assigned to the formula
            $result_res = zuc_upd_lst_res($val_wrd_lst, $phr_id, $frm_ids, $frm_row, $this->user()->id());
            $result = array_merge($result, $result_res);

            // get all values related to assigned word and to the formula words
            // and based on this value get the unique word list
            // e.g. if the formula text contains the word "sales" all values that are related to sales should be taken into account
            //      $frm_phr_ids is the list of words for the value selection, so in this case it would contain "sales"
            $frm_phr_ids = zuf_phr_ids ($frm_row[api::URL_VAR_USER_EXPRESSION], $this->user()->id());
            zu_debug('res_lst->frm_upd_lst_usr -> frm_phr_ids1 ('.implode(",",$frm_phr_ids).')');

            // add word words for the special formulas
            // e.g. if the formula text contains the special word "prior" and the formula is linked to "Year" and "2016" is a "Year"
            //      than the "prior" of "2016" is "2015", so the word "2015" should be included in the value selection
            $frm_phr_ids = array_unique (array_merge ($frm_phr_ids, $special_frm_phr_ids));
            $frm_phr_ids = array_filter($frm_phr_ids);
            zu_debug('res_lst->frm_upd_lst_usr -> frm_phr_ids2 ('.implode(",",$frm_phr_ids).')');

            $result_val = $this->add_frm_val($phr_id, $frm_phr_ids, $frm_row, $this->user()->id());
            // $result_val = zuc_upd_lst_val($phr_id, $frm_phr_ids, $frm_row, $this->user()->id());
            $result = array_merge($result, $result_val);

            // show the user the progress every two seconds
            $last_msg_time = zuc_upd_lst_msg($last_msg_time, $collect_pos, mysqli_num_rows($sql_result));
            $collect_pos++;

            Sample:
            update "sales" "water" "annual growth rate"
            -> get the formulas where any of the value words is used (zuv_frm_lst )
            -> formula "yearly forecast "estimate" "next" = "this" * (1 + "annual growth rate")" because "water" OR "annual growth rate" used
            -> get the list of words of the updated value not used in the formula e.g. "sales" "Water" ($val_wrd_ids_ex_frm_wrd)
            -> get all values linked to the word list e.g. "sales" AND "Water" (zuv_lst_of_wrd_ids -> $val_lst_of_wrd_ids)
            -> get the word list for each value excluding the word used in the formula e.g. "Nestlé" "sales" "Water" "2016" and  "Nestlé" "sales" "Water" "2017" ($val_wrd_lst_ex_frm_wrd)
            -> calculate the formula result for each word list (zuc_frm)
            -> return the list of formula results e.g. "Nestlé" "sales" "Water" "2018" "estimate" that have been updated or created ($frm_result_upd_lst)
            -> r) check in which formula the formula results are used
            -> formula "yearly forecast "estimate" "next" = "this" * (1 + "annual growth rate"), because the formula is linked to year and 2018 is a Year
            -> calculate the formula result for each word list of the formula result
            -> return the list of formula results e.g. "Nestlé" "sales" "Water" "2019" "estimate"
            -> repeat at r)

          }
        }  */
        //}

        //print_r($result);
        log_debug($lib->dsp_count($result->lst()));
        return $result;
    }

    /**
     * get the result that needs to be recalculated if one formula has been updated
     * TODO should returns a job_list with all formula results that may need to be updated if a formula is updated
     * @param formula $frm - the formula that has been updated
     * $usr - to define which user view should be updated
     */
    function frm_upd_lst(formula $frm, string $back): job_list
    {
        log_debug('add ' . $frm->dsp_id() . ' to queue ...');
        $lib = new library();

        // to inform the user about the progress
        $last_msg_time = time(); // the start time
        $collect_pos = 0;        // to calculate the progress in percent

        $result = null;

        // get a list of all words and triples where the formula should be used (assigned words)
        // including all child phrases that should also be included in the assignment e.g. for "Year" include "2018"
        // e.g. if the formula is assigned to "Company" and "ABB is a Company" include ABB in the phrase list
        // check in frm_upd_lst_usr only if the user has done any modifications that may influence the word list
        $phr_lst_frm_assigned = $frm->assign_phr_lst();
        log_debug('formula "' . $frm->name() . '" is assigned to ' . $phr_lst_frm_assigned->dsp_name() . ' for user ' . $phr_lst_frm_assigned->user()->name . '');

        // get a list of all words, triples, formulas and verbs used in the formula
        // e.g. for the formula "net profit" the word "sales" & "cost of sales" is used
        // for formulas the formula word is used
        $exp = $frm->expression();
        $phr_lst_frm_used = $exp->phr_verb_lst($back);
        log_debug('formula "' . $frm->name() . '" uses ' . $phr_lst_frm_used->name_linked() . ' (taken from ' . $frm->usr_text . ')');

        // get the list of predefined "following" phrases/formulas like "prior" or "next"
        $trm_lst_back = new term_list($this->user());
        $trm_back = new term($this->user());
        $trm_back->load_by_id($back);
        $trm_lst_back->add($trm_back);
        $phr_lst_preset_following = $exp->element_special_following($trm_lst_back);
        $frm_lst_preset_following = $exp->element_special_following_frm($trm_lst_back);

        // combine all used predefined phrases/formulas
        $phr_lst_preset = $phr_lst_preset_following;
        $frm_lst_preset = $frm_lst_preset_following;
        if (!empty($phr_lst_preset->lst())) {
            log_debug('predefined are ' . $phr_lst_preset->dsp_name());
        }

        // exclude the special elements from the phrase list to avoid double usage
        $phr_lst_frm_used->diff($phr_lst_preset);
        if ($phr_lst_preset->dsp_name() <> '""') {
            log_debug('Excluding the predefined phrases ' . $phr_lst_preset->dsp_name() . ' the formula uses ' . $phr_lst_frm_used->dsp_name());
        }

        // convert the special formulas to normal phrases e.g. use "2018" instead of "this" if the formula is assigned to "Year"
        foreach ($frm_lst_preset_following->lst() as $frm_special) {
            $frm_special->load();
            log_debug('get preset phrases for formula ' . $frm_special->dsp_id() . ' and phrases ' . $phr_lst_frm_assigned->dsp_name());
            $phr_lst_preset = $frm_special->special_phr_lst($phr_lst_frm_assigned);
            log_debug('got phrases ' . $phr_lst_preset->dsp_id());
        }
        log_debug('the used ' . $phr_lst_frm_used->name_linked() . ' are taken from ' . $frm->usr_text);
        if ($phr_lst_preset->dsp_name() <> '""') {
            log_debug('the used predefined formulas ' . $frm_lst_preset->name() . ' leading to ' . $phr_lst_preset->dsp_name());
        }

        // get the formula phrase name and the formula result phrases to exclude them already in the result phrase selection to avoid loops
        // e.g. to calculate the "increase" of "ABB,sales" the formula results for "ABB,sales,increase" should not be used
        //      because the "increase" of an "increase" is a gradient not an "increase"

        /*
        // get the phrase name of the formula e.g. "increase"
        if (!isset($frm->name_wrd)) {
            $frm->load_wrd();
        }
        */
        $phr_frm = $frm->name_wrd;
        log_debug('For ' . $frm->usr_text . ' formula results with the name ' . $phr_frm->name_dsp() . ' should not be used for calculation to avoid loops');

        // get the phrase name of the formula e.g. "percent"
        $exp = $frm->expression();
        $phr_lst_res = $exp->result_phrases();
        if (isset($phr_lst_res)) {
            log_debug('For ' . $frm->usr_text . ' formula results with the result phrases ' . $phr_lst_res->dsp_name() . ' should not be used for calculation to avoid loops');
        }

        // depending on the formula setting (all words or at least one word)
        // create a result list with all needed word combinations
        // TODO this get all results that
        // 1. have at least one assigned word and one formula word (one of each)
        // 2. remove all assigned words and formula words from the value word list
        // 3. aggregate the word list for all results
        // this is a kind of word group list, where for each word group list several results are possible,
        // because there may be one value and several results for the same word group
        log_debug('get all results used in the formula ' . $frm->usr_text . ' that are related to one of the phrases assigned ' . $phr_lst_frm_assigned->dsp_name());
        $phr_grp_lst_val = new group_list($this->user()); // by default the calling user is used, but if needed the value for other users also needs to be updated
        $phr_grp_lst_val->get_by_val_with_one_phr_each($phr_lst_frm_assigned, $phr_lst_frm_used, $phr_frm, $phr_lst_res);
        $phr_grp_lst_val->get_by_res_with_one_phr_each($phr_lst_frm_assigned, $phr_lst_frm_used, $phr_frm, $phr_lst_res);
        $phr_grp_lst_val->get_by_val_special($phr_lst_frm_assigned, $phr_lst_preset, $phr_frm, $phr_lst_res); // for predefined formulas ...
        $phr_grp_lst_val->get_by_res_special($phr_lst_frm_assigned, $phr_lst_preset, $phr_frm, $phr_lst_res); // ... such as "this"
        $phr_grp_lst_used = clone $phr_grp_lst_val;

        // first calculate the standard results for all user and then the user specific results
        // than loop over the users and check if the user has changed any value, formula or formula assignment
        $usr_lst = new user_list($this->user());
        $usr_lst->load_active();

        $lib = new library();
        log_debug('active users (' . $lib->dsp_array($usr_lst->names()) . ')');
        foreach ($usr_lst->lst() as $usr) {
            // check
            $usr_calc_needed = False;
            if ($usr->id() == $this->user()->id()) {
                $usr_calc_needed = true;
            }
            if ($this->user()->id() == 0 or $usr_calc_needed) {
                log_debug('update results for user: ' . $usr->name . ' and formula ' . $frm->name());

                $result = $this->frm_upd_lst_usr($frm, $phr_lst_frm_assigned, $phr_lst_frm_used, $phr_grp_lst_used, $usr, $last_msg_time, $collect_pos);
            }
        }

        //flush();
        log_debug($lib->dsp_count($result->lst()));
        return $result;
    }

    function get_first(): result
    {
        $result = new result($this->user());
        if (!$this->is_empty()) {
            $result = $this->get(0);
        }
        return $result;
    }

    /**
     * create a list of all formula results that needs to be updated if a value is updated
     */
    function val_upd_lst($val, $usr)
    {
        // check if the default value has been updated and if yes, update the default value
        // get all results
    }

    /**
     * load all results related to one value
     * TODO check if this is needed
     * TODO review: split the backend and frontend part
     *              target is: if a value is changed, what needs to be updated?
     */
    function load_by_val(value_base $val): string
    {
        $phr_lst = $val->phr_lst();
        return $this->load_by_phr_lst($phr_lst);
    }

    /**
     * add one result to the result list, but only if it is not yet part of the phrase list
     * @param result|value_base|null $val_to_add the calculation result that should be added to the list
     * @param bool $allow_duplicates true if e.g. the group id is not yet set but the value should nevertheless be added
     * @returns bool true the result has been added
     */
    function add(result|value_base|null $val_to_add, bool $allow_duplicates = false): bool
    {
        $result = false;
        log_debug($val_to_add->dsp_id());
        if (!in_array($val_to_add->id(), $this->ids())) {
            if ($val_to_add->id() <> 0) {
                $this->add_obj($val_to_add);
                $result = true;
            }
        } else {
            log_debug($val_to_add->dsp_id() . ' not added, because it is already in the list');
        }
        return $result;
    }

    /**
     * combine two calculation queues
     */
    function merge(result_list $lst_to_merge): result_list
    {
        log_debug($lst_to_merge->dsp_id() . ' to ' . $this->dsp_id());
        if (!$lst_to_merge->is_empty()) {
            foreach ($lst_to_merge->lst() as $new_res) {
                log_debug('add ' . $new_res->dsp_id());
                $this->add($new_res);
            }
        }
        log_debug('to ' . $this->dsp_id());
        return $this;
    }

}