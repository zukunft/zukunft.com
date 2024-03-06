<?php

/*

    model/phrase/group_list.php - a list of word and triple groups
    ----------------------------------

    TODO base on sandbox_list

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

namespace cfg\group;

include_once DB_PATH . 'sql_par_type.php';

use cfg\db\sql;
use cfg\db\sql_par;
use cfg\db\sql_par_type;
use cfg\library;
use cfg\phrase;
use cfg\phrase_list;
use cfg\sandbox_list;
use cfg\db\sql_db;
use cfg\term_list;
use cfg\triple;
use cfg\user_message;
use cfg\value\value;
use cfg\word;

class group_list extends sandbox_list
{

    public ?array $time_lst = null;     // the list of the time phrase (the add function)
    public ?array $grp_ids = null;      // the list of the phrase group ids

    // search fields
    public ?phrase $phr; //


    /*
     * load
     */

    /**
     * load all phrase groups that contain the given phrase
     * @param phrase $phr
     * @return bool true if at least one phrase group has been found
     */
    function load_by_phr(phrase $phr): bool
    {
        global $db_con;
        $result = false;

        $qp = $this->load_sql_by_phr_old($db_con->sql_creator(), $phr);

        // similar statement used in triple_list->load, check if changes should be repeated in triple_list.php
        $db_rows = $db_con->get($qp);
        if ($db_rows != null) {
            foreach ($db_rows as $db_row) {
                $phr_grp = new group($this->user());
                $phr_grp->row_mapper($db_row);
                $this->add_obj($phr_grp);
                $result = true;
            }
        }

        return $result;
    }

    /**
     * create an SQL statement to retrieve a list of groups linked to the given phrase from the database
     * TODO review to load the prime group by id
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
        $qp = new sql_par(group::class);
        $qp->name = $lib->class_to_name(group_list::class) . '_by_phr';
        $par_types = array();
        // loop over the possible tables where the group name overwrite might be stored in this pod
        foreach (group::TBL_LIST as $tbl_typ) {
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
     * create an SQL statement to retrieve a list of values linked to a phrase from the database
     * from a single table
     *
     * @param sql $sc with the target db_type set
     * @param phrase $phr if set to get all values for this phrase
     * @param array $tbl_typ_lst the table types for this table
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_phr_single(sql $sc, phrase $phr, array $tbl_typ_lst): sql_par
    {
        $qp = $this->load_sql_init($sc, group::class, 'phr', $tbl_typ_lst);
        $grp_id = new group_id();
        $sc->add_where(group::FLD_ID, $grp_id->int2alpha_num($phr->id()), sql_par_type::LIKE, '$3');
        $qp->sql = $sc->sql(0, true, false);
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * set the SQL query parameters to load a list of groups
     * set the fields for a union select of all possible tables
     *
     * @param sql $sc with the target db_type set
     * @param string $class the value or result class name
     * @param string $query_name the name extension to make the query name unique
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_init(
        sql    $sc,
        string $class,
        string $query_name,
        array  $tbl_types = []
    ): sql_par
    {
        $is_prime = $this->is_prime($tbl_types);
        $is_main = $this->is_main($tbl_types);

        $tbl_ext = $this->table_extension($tbl_types);
        $qp = new sql_par(group_list::class, false, false, $tbl_ext);
        $qp->name .= $query_name;

        $sc->set_class($class, false, $tbl_ext);
        // TODO add pattern filter for the prime group id
        $grp = new group($this->user());
        $sc->set_id_field($grp->id_field());
        $sc->set_name($qp->name);

        $sc->set_usr($this->user()->id());
        $sc->set_fields(group::FLD_NAMES);
        return $qp;
    }

    /**
     * create the common part of an SQL statement to get a list of phrase groups names from the database
     *
     * @param sql $sc with the target db_type set
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    protected function load_names_sql(sql $sc, string $query_name, string $class = self::class): sql_par
    {
        $grp = new group($this->user());
        return $grp->load_sql($sc, $query_name, $class);
    }

    /**
     * set the SQL query parameters to load a list of phrase groups names by the ids
     * @param sql $sc with the target db_type set
     * @param array $grp_ids a list of int values with the group ids
     * @param int $limit the number of rows to return
     * @param int $offset jump over these number of pages
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_names_sql_by_ids(sql $sc, array $grp_ids, int $limit = 0, int $offset = 0): sql_par
    {
        $qp = $this->load_names_sql($sc, 'ids_fast');

        // change query name from group to group_list
        $lib = new library();
        $class = $lib->class_to_name(self::class);
        $qp->name = $class . '_by_ids_fast';
        $sc->set_name($qp->name);

        $sc->add_where(group::FLD_ID, $grp_ids);
        $sc->set_order(group::FLD_ID);
        $sc->set_page($limit, $offset);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();
        return $qp;
    }

    /**
     * create the common part of an SQL statement to get a list of phrase groups from the database
     * TODO combine standard with prime and big
     *
     * @param sql $sc with the target db_type set
     * @param string $query_name the name extension to make the query name unique
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    protected function load_sql(sql $sc, string $query_name, string $class = self::class): sql_par
    {
        $grp = new group($this->user());
        $qp = $grp->load_sql($sc, $query_name);

        // change query name from group to group_list
        $lib = new library();
        $class = $lib->class_to_name(self::class);
        $qp->name = $class . '_by_' . $query_name;
        $sc->set_name($qp->name);

        return $qp;
    }

    /**
     * set the SQL query parameters to load a list of phrase groups by the ids
     * TODO combine standard with prime and big
     * TODO add load test to compare like matching with link table matching
     * TODO for prime use binary key like matching
     *
     * @param sql $sc with the target db_type set
     * @param array $grp_ids a list of int values with the group ids
     * @param int $limit the number of rows to return
     * @param int $offset jump over these number of pages
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_ids(sql $sc, array $grp_ids, int $limit = 0, int $offset = 0): sql_par
    {
        $qp = $this->load_sql($sc, 'ids');
        $sc->add_where(group::FLD_ID, $grp_ids);
        $sc->set_order(group::FLD_ID);
        $sc->set_page($limit, $offset);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();
        return $qp;
    }

    /**
     * set the SQL query parameters to load a list of groups by a phrase id
     * TODO add pattern matching for 64-bit, 512-bit and text group_id
     *
     * @param sql $sc with the target db_type set
     * @param phrase $phr the phrase to which all linked groups should be returned
     * @param int $limit the number of rows to return
     * @param int $offset jump over these number of pages
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_phr_old(sql $sc, phrase $phr, int $limit = 0, int $offset = 0): sql_par
    {

        $qp = $this->load_sql($sc, 'phr');
        // overwrite the query name
        $lib = new library();
        $class = $lib->class_to_name($this::class);
        $qp->name = $class . '_by_phr';
        $sc->set_name($qp->name);
        $sc->set_join_fields(
            array(phrase::FLD_ID),
            sql_db::TBL_GROUP_LINK,
            group::FLD_ID,
            group::FLD_ID);
        $sc->add_where(sql_db::LNK_TBL . '.' . phrase::FLD_ID, $phr->obj_id());
        $sc->set_order(group::FLD_ID);
        $sc->set_page($limit, $offset);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();
        return $qp;
    }

    /**
     * delete all loaded phrase groups e.g. to delete al the phrase groups linked to a phrase
     * @return user_message
     */
    function del(): user_message
    {
        $result = new user_message();

        foreach ($this->lst() as $phr_grp) {
            $result->add($phr_grp->del());
        }
        return new user_message();
    }


    /*
     * add
     */

    /**
     * combine the group id and the time id to a unique index
     */
    private function grp_time_id(group $grp, $time)
    {
        $id = '';
        if (isset($grp)) {
            $grp_id = $grp->id();
            if ($grp_id > 0) {
                $id = $grp_id;
            }
        }
        if (isset($time)) {
            $time_id = $time->id;
            if ($time_id > 0) {
                if ($id <> '') {
                    $id = $id . '@' . $time_id;
                } else {
                    $id = $time_id;
                }
            }
        }
        return $id;
    }

    /**
     * add a phrase group if it is not yet part of the list
     * @param group $grp
     */
    function add(group $grp): void
    {
        log_debug($grp->id());
        $do_add = false;
        if ($grp->is_id_set()) {
            if ($this->grp_ids == null) {
                $do_add = true;
            } else {
                if (!in_array($grp->id(), $this->grp_ids)) {
                    $do_add = true;
                }
            }
        }
        if ($do_add) {
            $this->add_obj($grp);
            $this->grp_ids[] = $grp->id();
            $this->time_lst[] = null;
            log_debug($grp->dsp_id() . ' added to list ' . $this->dsp_id());
        } else {
            log_debug($grp->dsp_id() . ' skipped, because is already in list ' . $this->dsp_id());
        }
    }

    /*

    add groups that have a value linked to one of the phrases
    add all phrase groups to the list that have a value with at least one word in each word list

    add all formula results to the list for ONE formula based on
    - $frm_linked: the words and triples assigned to the formula e.g. "Year" for "increase"
    - $frm_used:   the words and triples that are used in the formula e.g. "this" and "next" for "increase"

    the function is assuming that the "view" table "value_phrase_links" is up to date
    including the user specific exceptions based on the formula expression

    used to request an update for a formula result for each phrase group
    e.g. the formula is assigned to "Company" ($frm_linked) and the "operating income" formula result should be calculated
         so "Sales" and "Cost" are words of the formula
         if "Sales" and "Cost" for 2016 and 2017 and EUR and CHF are in the database for one company (e.g. "ABB")
         the "ABB" "operating income" for "2016" and "2017" should be calculated in "EUR" and "CHF"
         so the result would be to add 4 results to the list:
         1. calculate "operating income" for "ABB", "EUR" and "2016"
         2. calculate "operating income" for "ABB", "CHF" and "2016"
         3. calculate "operating income" for "ABB", "EUR" and "2017"
         4. calculate "operating income" for "ABB", "CHF" and "2017"

    time words and normal words and triples should be treated the same way,
    but to reduce the number of phrase groups for value and formula result saving they are separated
    this implies that in the query the selection needs to be separated by time and normal words and triples

    cases:
    - if a formula is only uses time   words e.g. "increase"             only the time  selection should be used and all groups         should be included
    - if a formula is only uses normal words e.g. "Net profit"           only the group selection should be used and all times          should be included
    - if a formula is only uses both         e.g. "Net profit next year" only the group selection should be used and the time selection should be used

    - if a formula is assigned to "Year" and "2018" all value and result that have "Year" OR "2018" should be updated
    - if a formula is assigned to the triple "2018 (Year)" only the value and result for the "Year" "2018" should be updated

    - if a normal phrase is assigned but not used no value should be selected
    - if a   time word   is assigned but not used no value should be selected

    TODO: check if a value is used in the formula
          exclude the time word and if needed loop over the time words
          if the value has been update, create a calculation request
    */

    /**
     * query to get the value or formula result phrase groups and time words that contains at least one phrase of two lists based on the user sandbox
     * e.g. which value that have "Sales" and "2016"?
     */
    private function get_grp_by_phr($type, $phr_linked, $phr_used): array
    {
        log_debug('get values because formula is assigned to phrases ' . $phr_linked->name() . ' and phrases ' . $phr_used->name() . ' are used in the formula');

        global $db_con;

        // separate the time words from the phrases
        $time_linked = $phr_linked->time_lst();
        log_debug('time words linked ' . $time_linked->name());
        $time_used = $phr_used->time_lst();
        log_debug('time words used ' . $time_used->name());
        $phr_linked_ex = clone $phr_linked;
        $phr_linked_ex->ex_time();
        log_debug('linked ex time ' . $phr_linked_ex->name());
        $phr_used_ex = clone $phr_used;
        $phr_used_ex->ex_time();
        log_debug('used ex time ' . $phr_used_ex->name());

        // create the group selection
        $sql_group = '';
        if (count($phr_linked->ids) > 0 and count($phr_used->ids) > 0) {
            $sql_group = 'SELECT l1.group_id
                      FROM group_phrase_links l1
                 LEFT JOIN user_group_phrase_links u1 ON u1.group_phrase_link_id = l1.group_phrase_link_id 
                                                            AND u1.user_id = ' . $this->user()->id() . ',
                           group_phrase_links l2
                 LEFT JOIN user_group_phrase_links u2 ON u2.group_phrase_link_id = l2.group_phrase_link_id 
                                                            AND u2.user_id = ' . $this->user()->id() . '
                     WHERE l1.phrase_id IN (' . $phr_linked_ex->ids_txt() . ')  
                       AND l2.phrase_id IN (' . $phr_used_ex->ids_txt() . ')
                       AND l1.group_id = l2.group_id
                       AND COALESCE(u1.excluded, 0) <> 1
                       AND COALESCE(u2.excluded, 0) <> 1
                  GROUP BY l1.group_id';
        } else {
            log_warning('Phrases missing while loading the phrase groups');
            // e.g. if "Sales" is assigned, but never  used in the formula no value needs to be calculated, so no group should be used
            //   or if "Sales" is used, but never  assigned to the formula no value needs to be calculated, so no group should be used
            //   or if "Sales" is not used and not assigned to the formula no value needs to be calculated, so no group should be used
            // in all these cases only value selected by time needs to be updated
        }

        // create the time selection
        /*
        $sql_time = '';
        if (count($time_linked->ids) > 0 and count($time_used->ids) > 0) {
            $sql_time = 'v.time_word_id IN (' . sql_array($time_linked->ids) . ')
               AND v.time_word_id IN (' . sql_array($time_used->ids) . ')';
        } else {
            // dito group
            log_warning('Phrases missing while loading the phrase groups');
        }
        */

        // create the value or result selection
        // TODO use sql builder
        if ($type == 'value') {
            $sql_select = 'SELECT v.group_id,
                            v.group_id
                       FROM values v';
        } else {
            $sql_select = 'SELECT v.group_id AS group_id,
                            v.group_id
                       FROM results v';
        }

        // combine the selections
        $sql = '';
        $sql_group_by = ' GROUP BY group_id, group_id LIMIT 500'; // limit is only set for testing: remove for release!
        if ($sql_group <> '') {
            // select values only by the group
            $sql = $sql_select . ', ( ' . $sql_group . ') AS g WHERE v.group_id = g.group_id' . $sql_group_by . ';';
        }

        log_debug('sql "' . $sql . '"');
        //$db_con = New mysql;
        $db_con->usr_id = $this->user()->id();
        return $db_con->get_old($sql);
    }

    // combined code to add values assigned by a word or a predefined formula like "this", "prior" or "next"
    private function add_grp_by_phr($type, $frm_linked, $frm_used, $phr_frm, $phr_lst_res): int
    {
        $lib = new library();

        // check the parameters
        if ($type == '') {
            log_err('Type is missing.', 'phr_grp_lst->add_grp_by_phr');
        }
        if (!isset($frm_linked)) {
            log_err('Linked formula is missing.', 'phr_grp_lst->add_grp_by_phr');
        }
        if (!isset($frm_used)) {
            log_err('Used formula is missing.', 'phr_grp_lst->add_grp_by_phr');
        }
        if (!isset($phr_frm)) {
            log_err('Formula phrase is missing.', 'phr_grp_lst->add_grp_by_phr');
        }

        log_debug($frm_linked->name() . ' related ' . $type . 's found for ' . $frm_used->name() . ' and user ' . $this->user()->name);
        $added = 0;
        $changed = 0;

        // select is using the phrase groups because this is faster than checking all values or formula result
        // and there cannot be any value or formula result without phrase group
        if (!empty($frm_linked->ids)) {
            $val_rows = $this->get_grp_by_phr($type, $frm_linked, $frm_used);
            foreach ($val_rows as $val_row) {
                // add the phrase group of the value or formula result add the time using a combined index
                // because a time word should never be part of a phrase group to have a useful number of groups
                log_debug('add id ' . $val_row[group::FLD_ID]);
                // log_debug('add time id ' . $val_row[value::FLD_TIME_WORD]);
                // remove the formula name phrase and the result phrases from the value phrases to avoid potentials loops and
                $val_grp = new group($this->user());
                $val_grp->load_by_id($val_row[group::FLD_ID]);
                $used_phr_lst = clone $val_grp->phrase_list();
                log_debug('used_phr_lst ' . $used_phr_lst->dsp_id());
                // exclude the formula name
                $used_phr_lst->del($phr_frm);
                log_debug('removed formula phrase ' . $phr_frm->dsp_id() . ' from used_phr_lst ' . $used_phr_lst->dsp_id());
                // exclude the result phrases
                $phr_lst_res_name = '';
                if (isset($phr_lst_res)) {
                    $used_phr_lst->diff($phr_lst_res);
                    log_debug('removed result phrases ' . $phr_lst_res->dsp_id() . ' from used_phr_lst ' . $used_phr_lst->dsp_id());
                    $phr_lst_res_name = $phr_lst_res->dsp_id();
                }
                // add the group to the calculation list if the group is not yet in the list
                $grp_to_add = $used_phr_lst->get_grp_id();
                if ($grp_to_add->id() <> $val_grp->id()) {
                    log_debug('group ' . $grp_to_add->dsp_id() . ' used instead of ' . $val_grp->dsp_id() . ' because ' . $phr_frm->dsp_id() . ' and  ' . $phr_lst_res_name . ' are part of the formula and have been remove from the phrase group selection');
                    $changed++;
                }
                /* TODO deprecate now
                if ($this->add_grp_time_id($grp_to_add->id(), $val_row[value::FLD_TIME_WORD])) {
                    $added++;
                    $changed++;
                    log_debug('added ' . $added . ' in ' . $lib->dsp_count($this->grp_time_ids));
                }
                */
            }
        }

        log_debug($added . ' ' . $type . 's selected for update because the formula is assigned ' . $frm_linked->name() . ' and uses ' . $frm_used->name() . ' (adding up to "' . $this->name() . '")');
        return $added;
    }

    function get_by_val_with_one_phr_each($frm_linked, $frm_used, $phr_frm, $phr_lst_res): int
    {
        return $this->add_grp_by_phr('value', $frm_linked, $frm_used, $phr_frm, $phr_lst_res);
    }

    function get_by_val_special($frm_linked, $frm_used_fixed, $phr_frm, $phr_lst_res): int
    {
        return $this->add_grp_by_phr('value', $frm_linked, $frm_used_fixed, $phr_frm, $phr_lst_res);
    }

    function get_by_res_with_one_phr_each($frm_linked, $frm_used, $phr_frm, $phr_lst_res): int
    {
        return $this->add_grp_by_phr('formula result', $frm_linked, $frm_used, $phr_frm, $phr_lst_res);
    }

    //
    function get_by_res_special($frm_linked, $frm_used_fixed, $phr_frm, $phr_lst_res): int
    {
        return $this->add_grp_by_phr('formula result', $frm_linked, $frm_used_fixed, $phr_frm, $phr_lst_res);
    }

    /*
    change functions
    */

    // remove all words of del word list from each word list
    // e.g. if the word group list contains "2016,Nestlé,Number of shares" and "2016,Danone,Number of shares"
    //      and "Number of shares" should be removed
    //      the result would be "2016,Nestlé" and "2016,Danone"
    /* to review
    function remove_wrd_lst($del_wrd_lst) {
      foreach (array_keys($this->lst()) AS $pos) {
        zu_debug('remove "'.implode(",",$del_wrd_lst->names()).'" from ('.implode(",",$this->lst[$pos]->names()).')');
        $this->lst[$pos]->diff_by_ids($del_wrd_lst->ids);
      }
    }
    */


    /*
     * information
     */

    /**
     * get the common phrases of a groups
     * assumes that each group is fully loaded
     * TODO add a check if a group the phrase list of a group is incomplete
     *
     * @return phrase_list|null with all phrases that are part of each phrase group of the list
     */
    function common_phrases(): ?phrase_list
    {
        log_debug();
        $lib = new library();
        $result = new phrase_list($this->user());
        $pos = 0;
        foreach ($this->lst() as $grp) {
            //$grp->load_by_obj_vars();
            if ($pos == 0) {
                if ($grp->has_phrase_list()) {
                    $result = clone $grp->phrase_list();
                }
            } else {
                if ($grp->has_phrase_list()) {
                    //$result = $result->concat_unique($grp->phrase_list());
                    $result->common($grp->phrase_list());
                }
            }
            log_debug($result->dsp_name());
            $pos++;
        }
        log_debug($lib->dsp_count($result->lst()));
        return $result;
    }

    /**
     * @return array with the database ids of all objects of this list
     */
    function ids(int $limit = null): array
    {
        $result = array();
        foreach ($this->lst() as $sbx_obj) {
            // use only valid ids
            if ($sbx_obj->id() <> 0) {
                $result[] = $sbx_obj->id();
            }
        }
        return $result;
    }


    /*
     * debug
     */

    /**
     * @param term_list|null $trm_lst a cached list of terms
     * @return string with the unique id fields
     */
    function dsp_id(?term_list $trm_lst = null): string
    {
        global $debug;
        $lib = new library();
        $result = '';
        // check the object setup
        if (count($this->lst()) <> count($this->time_lst)) {
            $result .= 'The number of groups (' . $lib->dsp_count($this->lst()) . ') are not equal the number of times (' . $lib->dsp_count($this->time_lst) . ') of this phrase group list';
        } else {

            $pos = 0;
            foreach ($this->lst() as $phr_lst) {
                if ($debug > $pos) {
                    if ($result <> '') {
                        $result .= ' / ';
                    }
                    $result .= $phr_lst->name();
                    $phr_time = $this->time_lst[$pos];
                    if (!is_null($phr_time)) {
                        $result .= '@' . $phr_time->name();
                    }
                    $pos++;
                }
            }
            if (count($this->lst()) > $pos) {
                $result .= ' ... total ' . $lib->dsp_count($this->lst());
            }

        }
        return $result;
    }

    /**
     * create a useful (but not unique!) name of the phrase group list mainly used for debugging
     */
    function name(int $limit = null): string
    {
        global $debug;
        $lib = new library();
        $result = '';
        $names = $this->names();
        if ($debug > 10 or count($names) > 3) {
            $main_names = array_slice($names, 0, 3);
            $result .= implode(" and ", $main_names);
            $result .= " ... total " . $lib->dsp_count($names);
        } else {
            $result .= implode(" and ", $names);
        }
        return $result;
    }

    /**
     * return a list of the word names
     */
    function names(int $limit = null): array
    {
        $result = array();
        foreach ($this->lst() as $phr_lst) {
            $result[] = $phr_lst->name();
        }
        log_debug('group_list->names ' . implode(" / ", $result));
        return $result;
    }

    // correct all word groups e.g. that still has a time word
    function check()
    {
    }

}