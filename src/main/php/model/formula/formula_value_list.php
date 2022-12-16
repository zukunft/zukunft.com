<?php

/*

  formula_value_list.php - a list of formula results
  ----------------------
  
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

use html\word_dsp;

class formula_value_list
{

    /*
     * object vars
     */

    public array $lst;   // list of the formula results
    public user $usr;    // the person who wants to see the results

    /*
     * construct and map
     */

    function __construct(user $usr)
    {
        $this->lst = array();
        $this->set_user($usr);
    }

    /*
     * get and set
     */

    /**
     * set the user of the value list
     *
     * @param user|null $usr the person who wants to access the values
     * @return void
     */
    function set_user(?user $usr): void
    {
        $this->usr = $usr;
    }

    /**
     * @return user|null the person who wants to see the values
     */
    function user(): ?user
    {
        return $this->usr;
    }

    /*
     *  load functions
     */

    function load_by_phr_lst_sql(sql_db $db_con, phrase_list $phr_lst): sql_par
    {
        $qp = new sql_par(self::class);

        $qp->par = $db_con->get_par();
        return $qp;
    }

    function load_by_phr_lst(sql_db $db_con, phrase_list $phr_lst): sql_par
    {
        $qp = $this->load_by_phr_lst_sql($db_con, $phr_lst);

        $qp->par = $db_con->get_par();
        return $qp;
    }


    /**
     * create the SQL to load a list of formula values link to
     * a formula
     * a phrase group
     *   either of the source or the result
     *   and with or without time selection
     * a word or a triple
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param object $obj a named object used for selection e.g. a formula
     * @param object|null $obj2 a second named object used for selection e.g. a time phrase
     * @param bool $by_source set to true to force the selection e.g. by source phrase group id
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql_db $db_con, object $obj, ?object $obj2 = null, bool $by_source = false): sql_par
    {
        $qp = new sql_par(self::class);
        $sql_by = '';
        if ($obj->id() > 0) {
            if (get_class($obj) == formula::class or get_class($obj) == formula_dsp_old::class) {
                $sql_by .= formula::FLD_ID;
            } elseif (get_class($obj) == phrase_group::class) {
                if ($by_source) {
                    $sql_by .= formula_value::FLD_SOURCE_GRP;
                    if ($obj2 != null) {
                        if (get_class($obj2) == phrase::class or get_class($obj2) == phrase_dsp_old::class) {
                            $sql_by .= '_' . formula_value::FLD_SOURCE_TIME;
                        }
                    }
                } else {
                    $sql_by .= phrase_group::FLD_ID;
                    if ($obj2 != null) {
                        if (get_class($obj2) == phrase::class or get_class($obj2) == phrase_dsp_old::class) {
                            $sql_by .= '_' . formula_value::FLD_TIME;
                        }
                    }
                }
            } elseif (get_class($obj) == word::class or get_class($obj) == word_dsp::class) {
                $sql_by .= word::FLD_ID;
            } elseif (get_class($obj) == triple::class) {
                $sql_by .= triple::FLD_ID;
            }
        }
        if ($sql_by == '') {
            log_err('Either the formula id or the phrase group id and the user (' . $this->user()->id .
                ') must be set to load a ' . self::class, self::class . '->load_sql');
            $qp->name = '';
        } else {
            $db_con->set_type(sql_db::TBL_FORMULA_VALUE);
            $qp->name .= $sql_by;
            $db_con->set_name(substr($qp->name, 0, 62));
            $db_con->set_fields(formula_value::FLD_NAMES);
            $db_con->set_usr($this->user()->id);
            if ($obj->id() > 0) {
                if (get_class($obj) == formula::class or get_class($obj) == formula_dsp_old::class) {
                    $db_con->add_par(sql_db::PAR_INT, $obj->id());
                    $qp->sql = $db_con->select_by_field_list(array(formula::FLD_ID));
                } elseif (get_class($obj) == phrase_group::class) {
                    $db_con->add_par(sql_db::PAR_INT, $obj->id());
                    $link_fields = array();
                    if ($by_source) {
                        $link_fields[] = formula_value::FLD_SOURCE_GRP;
                        if ($obj2 != null) {
                            if (get_class($obj2) == phrase::class or get_class($obj2) == phrase_dsp_old::class) {
                                $db_con->add_par(sql_db::PAR_INT, $obj2->id());
                                $link_fields[] = formula_value::FLD_SOURCE_TIME;
                            }
                        }
                    } else {
                        $link_fields[] = phrase_group::FLD_ID;
                        if ($obj2 != null) {
                            if (get_class($obj2) == phrase::class or get_class($obj2) == phrase_dsp_old::class) {
                                $db_con->add_par(sql_db::PAR_INT, $obj2->id());
                                $link_fields[] = formula_value::FLD_TIME;
                            }
                        }
                    }
                    $qp->sql = $db_con->select_by_field_list($link_fields);
                } elseif (get_class($obj) == word::class or get_class($obj) == word_dsp::class) {
                    // TODO check if the results are still correct if the user has excluded the word
                    $db_con->add_par(sql_db::PAR_INT, $obj->id(), false, true);
                    $db_con->set_join_fields(
                        array(formula_value::FLD_GRP),
                        sql_db::TBL_PHRASE_GROUP_WORD_LINK,
                        formula_value::FLD_GRP,
                        formula_value::FLD_GRP);
                    $qp->sql = $db_con->select_by_field_list(array(word::FLD_ID));
                } elseif (get_class($obj) == triple::class) {
                    // TODO check if the results are still correct if the user has excluded the triple
                    $db_con->add_par(sql_db::PAR_INT, $obj->id(), false, true);
                    $db_con->set_join_fields(
                        array(formula_value::FLD_GRP),
                        sql_db::TBL_PHRASE_GROUP_TRIPLE_LINK,
                        formula_value::FLD_GRP,
                        formula_value::FLD_GRP);
                    $qp->sql = $db_con->select_by_field_list(array(triple::FLD_ID));
                }
            }
            $qp->par = $db_con->get_par();
        }

        return $qp;
    }

    /**
     * create the SQL to load a list of formula values link to a formula
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param formula $frm a named object used for selection e.g. a formula
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_by_frm_sql(sql_db $db_con, formula $frm): sql_par
    {
        return $this->load_sql($db_con, $frm);
    }

    /**
     * load a list of formula values linked to a formula
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param formula $frm a named object used for selection e.g. a formula
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_by_frm(formula $frm): bool
    {
        global $db_con;
        $result = false;

        $qp = $this->load_by_frm_sql($db_con, $frm);
        if ($qp->name != '') {
            $db_rows = $db_con->get($qp);
            if ($db_rows != null) {
                foreach ($db_rows as $db_row) {
                    $fv = new formula_value($this->usr);
                    $fv->row_mapper($db_row);
                    $this->lst[] = $fv;
                    $result = true;
                }
            }
        }

        return $result;
    }

    /**
     * load a list of formula values linked to
     * a formula
     * a phrase group
     *   either of the source or the result
     *   and with or without time selection
     * a word or a triple
     *
     * @param object $obj a named object used for selection e.g. a formula
     * @param object|null $obj2 a second named object used for selection e.g. a time phrase
     * @param bool $by_source set to true to force the selection e.g. by source phrase group id
     * @return bool true if value or phrases are found
     */
    function load(object $obj, ?object $obj2 = null, bool $by_source = false): bool
    {
        global $db_con;
        $result = false;

        $qp = $this->load_sql($db_con, $obj, $obj2, $by_source);
        if ($qp->name != '') {
            $db_rows = $db_con->get($qp);
            if ($db_rows != null) {
                foreach ($db_rows as $db_row) {
                    $fv = new formula_value($this->usr);
                    $fv->row_mapper($db_row);
                    $this->lst[] = $fv;
                    $result = true;
                }
            }
        }

        return $result;
    }


    /*
     * display functions
     */

    // return best possible id for this element mainly used for debugging
    function dsp_id(): string
    {
        global $debug;
        $result = '';

        if ($debug > 10) {
            if (isset($this->lst)) {
                foreach ($this->lst as $fv) {
                    $result .= $fv->dsp_id();
                    $result .= ' (' . $fv->id() . ') - ';
                }
            }
        } else {
            $nbr = 1;
            if (isset($this->lst)) {
                foreach ($this->lst as $fv) {
                    if ($nbr <= 5) {
                        $result .= $fv->dsp_id();
                        $result .= ' (' . $fv->id() . ') - ';
                    }
                    $nbr++;
                }
            }
            if ($nbr > 5) {
                $result .= ' ... total ' . dsp_count($this->lst);
            }
        }
        /*
        if ($this->user()->is_set()) {
          $result .= ' for user '.$this->user()->name;
        }
        */
        return $result;
    }

    /**
     * return one string with all names of the list
     */
    function name(): string
    {
        global $debug;

        $name_lst = array();
        if (isset($this->lst)) {
            foreach ($this->lst as $fv) {
                $name_lst[] = $fv->name();
            }
        }

        if ($debug > 10) {
            $result = '"' . implode('","', $name_lst) . '"';
        } else {
            $result = '"' . implode('","', array_slice($name_lst, 0, 7));
            if (count($name_lst) > 8) {
                $result .= ' ... total ' . dsp_count($this->lst);
            }
            $result .= '"';
        }
        return $result;
    }

    /**
     * return a list of the formula result ids
     */
    function ids(): array
    {
        $result = array();
        if (isset($this->lst)) {
            foreach ($this->lst as $fv) {
                // use only valid ids
                if ($fv->id() <> 0) {
                    $result[] = $fv->id();
                }
            }
        }
        return $result;
    }

    /**
     * return a list of the formula result names
     */
    function names(): array
    {
        $result = array();
        if (isset($this->lst)) {
            foreach ($this->lst as $fv) {
                $result[] = $fv->name();

                // check user consistency (can be switched off once the program ist stable)
                if (!isset($fv->usr)) {
                    log_err('The user of a formula result list element differs from the list user.', 'fv_lst->names', 'The user of "' . $fv->name() . '" is missing, but the list user is "' . $this->user()->name . '".', (new Exception)->getTraceAsString(), $this->usr);
                } elseif ($fv->usr <> $this->usr) {
                    log_err('The user of a formula result list element differs from the list user.', 'fv_lst->names', 'The user "' . $fv->usr->name . '" of "' . $fv->name() . '" does not match the list user "' . $this->user()->name . '".', (new Exception)->getTraceAsString(), $this->usr);
                }
            }
        }
        log_debug('fv_lst->names (' . dsp_array($result) . ')');
        return $result;
    }

    /**
     * create the html code to show the formula results to the user
     * TODO move to formula_value_list_min_display
     */
    function display(string $back = ''): string
    {
        log_debug("fv_lst->display (" . dsp_count($this->lst) . ")");
        $result = ''; // reset the html code var

        // prepare to show where the user uses different word than a normal viewer
        //$row_nbr = 0;
        $result .= dsp_tbl_start_half();
        if ($this->lst != null) {
            foreach ($this->lst as $fv) {
                //$row_nbr++;
                $result .= '<tr>';
                /*if ($row_nbr == 1) {
                  $result .= '<th>words</th>';
                  $result .= '<th>value</th>';
                } */
                $fv->load_phrases(); // load any missing objects if needed
                $phr_lst = clone $fv->phr_lst;
                if (isset($fv->time_phr)) {
                    log_debug("add time " . $fv->time_phr->name() . ".");
                    $phr_lst->add($fv->time_phr);
                }
                $phr_lst_dsp = $phr_lst->dsp_obj();
                $result .= '</tr><tr>';
                $result .= '<td>' . $phr_lst_dsp->name_linked() . '</td>';
                $result .= '<td>' . $fv->display_linked($back) . '</td>';
                $result .= '</tr>';
            }
        }
        $result .= dsp_tbl_end();

        log_debug("done");
        return $result;
    }

    /*
     * create functions - build new formula values
     */

    /**
     * add all formula results to the list for ONE formula based on
     * - the word assigned to the formula ($phr_id)
     * - the word that are used in the formula ($frm_phr_ids)
     * - the formula ($frm_row) to provide parameters, but not for selection
     * - the user ($this->user()->id) to filter the results
     * and request on formula result for each word group
     * e.g. the formula is assigned to Company ($phr_id) and the "operating income" formula result should be calculated
     *      so Sales and Cost are words of the formula
     *      if Sales and Cost for 2016 and 2017 and EUR and CHF are in the database for one company (e.g. ABB)
     *      the "ABB" "operating income" for "2016" and "2017" should be calculated in "EUR" and "CHF"
     *      so the result would be to add 4 formula values to the list:
     *      1. calculate "operating income" for "ABB", "EUR" and "2016"
     *      2. calculate "operating income" for "ABB", "CHF" and "2016"
     *      3. calculate "operating income" for "ABB", "EUR" and "2017"
     *      4. calculate "operating income" for "ABB", "CHF" and "2017"
     * TODO: check if a value is used in the formula
     *       exclude the time word and if needed loop over the time words
     *       if the value has been update, create a calculation request
     * ex zuc_upd_lst_val
     */
    function add_frm_val($phr_id, $frm_phr_ids, $frm_row, $usr_id)
    {
        log_debug('fv_lst->add_frm_val(t' . $phr_id . ',' . dsp_array($frm_phr_ids) . ',u' . $this->user()->id . ')');

        global $debug;

        $result = array();

        // temp utils the call is reviewed
        $wrd = new word($this->usr);
        $wrd->set_id($phr_id);
        $wrd->load_obj_vars();

        $val_lst = new value_list($this->usr);
        $value_lst = $val_lst->load_frm_related_grp_phrs($phr_id, $frm_phr_ids, $this->user()->id);

        foreach (array_keys($value_lst) as $val_id) {
            /* maybe use for debugging */
            if ($debug > 0) {
                $debug_txt = "";
                $debug_phr_ids = $value_lst[$val_id][1];
                foreach ($debug_phr_ids as $debug_phr_id) {
                    $debug_wrd = new word($this->usr);
                    $debug_wrd->set_id($debug_phr_id);
                    $debug_wrd->load_obj_vars();
                    $debug_txt .= ", " . $debug_wrd->name();
                }
            }
            log_debug('calc ' . $frm_row['formula_name'] . ' for ' . $wrd->name() . ' (' . $phr_id . ')' . $debug_txt);

            // get the group words
            $phr_ids = $value_lst[$val_id][1];
            // add the formula assigned word if needed
            if (!in_array($phr_id, $phr_ids)) {
                $phr_ids[] = $phr_id;
            }

            // build the single calculation request
            $calc_row = array();
            $calc_row['usr_id'] = $this->user()->id;
            $calc_row['frm_id'] = $frm_row[formula::FLD_ID];
            $calc_row['frm_name'] = $frm_row['formula_name'];
            $calc_row['frm_text'] = $frm_row['formula_text'];
            $calc_row['trm_ids'] = $phr_ids;
            $result[] = $calc_row;
        }

        log_debug('number of values added (' . dsp_count($result) . ')');
        return $result;
    }

    /**
     * add all formula results to the list that may needs to be updated if a formula is updated for one user
     * TODO: only request the user specific calculation if needed
     */
    function frm_upd_lst_usr(
        formula $frm,
                $phr_lst_frm_assigned, $phr_lst_frm_used, $phr_grp_lst_used, $usr, $last_msg_time, $collect_pos)
    {
        log_debug('fv_lst->frm_upd_lst_usr(' . $frm->name() . ',fat' . $phr_lst_frm_assigned->name() . ',ft' . $phr_lst_frm_used->name() . ',' . $usr->name . ')');
        $result = new batch_job_list($usr);
        $added = 0;

        // TODO: check if the assigned words are different for the user

        // TODO: check if the formula words are different for the user

        // TODO: check if the assigned words, formula words OR the user has different values or formula values

        // TODO: filter the words if just a value has been updated
        /*    if (!empty($val_wrd_lst)) {
              zu_debug('fv_lst->frm_upd_lst_usr -> update related words ('.implode(",",$val_wrd_lst).')');
              $used_word_ids = array_intersect($is_word_ids, array_keys($val_wrd_lst));
              zu_debug('fv_lst->frm_upd_lst_usr -> needed words ('.implode(",",$used_word_ids).' instead of '.implode(",",$is_word_ids).')');
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

            if (!empty($phr_lst->lst)) {
                $calc_request = new batch_job($usr);
                $calc_request->frm = $frm;
                $calc_request->phr_lst = $phr_lst;
                $result->add($calc_request);
                log_debug('request "' . $frm->name() . '" for "' . $phr_lst->name() . '"');
                $added++;
            }
        }

        // loop over the word categories assigned to the formulas
        // get the words where the formula is used including the based on the assigned word e.g. Company or year
        //$sql_result = zuf_wrd_lst ($frm_lst->ids, $this->user()->id);
        //zu_debug('fv_lst->frm_upd_lst_usr -> number of formula assigned words '. mysqli_num_rows ($sql_result));
        //while ($frm_row = mysqli_fetch_array($sql_result, MySQLi_ASSOC)) {
        //zu_debug('fv_lst->frm_upd_lst_usr -> formula '.$frm_row['formula_name'].' ('.$frm_row['resolved_text'].') linked to '.zut_name($frm_row['word_id'], $this->user()->id));

        // also use the formula for all related words e.g. if the formula should be used for "Company" use it also for "ABB"
        //$is_word_ids = zut_ids_are($frm_row['word_id'], $this->user()->id); // should later be taken from the original array to increase speed

        // include also the main word in the testing
        //$is_word_ids[] = $frm_row['word_id'];

        /*
        $used_word_lst = New word_list;
        $used_word_lst->ids    = $used_word_ids;
        $used_word_lst->usr_id = $this->user()->id;
        $used_word_lst->load ();

        // loop over the words assigned to the formulas
        zu_debug('the formula "'.$frm_row['formula_name'].'" is assigned to "'.zut_name($frm_row['word_id'], $this->user()->id).'", which are '.implode(",",$used_word_lst->names_linked()));
        foreach ($used_word_ids AS $phr_id) {
          $special_frm_phr_ids = array();

          if (zuf_has_verb($frm_row['formula_text'], $this->user()->id)) {
            // special case
            zu_debug('fv_lst->frm_upd_lst_usr -> formula has verb ('.$frm_row['formula_text'].')');
          } else {

            // include all results of the underlying formulas
            $all_frm_ids = zuf_frm_ids ($frm_row['formula_text'], $this->user()->id);

            // get fixed / special formulas
            $frm_ids = array();
            foreach ($all_frm_ids as $chk_frm_id) {
              if (zuf_is_special ($chk_frm_id, $this->user()->id)) {
                $special_frm_phr_ids = $frm_upd_lst_frm_special ($chk_frm_id, $frm_row['formula_text'], $this->user()->id, $phr_id);

                //get all values related to the words
              } else {
                $frm_ids[] = $chk_frm_id;
              }
            }

            // include the results of the underlying formulas, but only the once related to one of the words assigned to the formula
            $result_fv = zuc_upd_lst_fv($val_wrd_lst, $phr_id, $frm_ids, $frm_row, $this->user()->id);
            $result = array_merge($result, $result_fv);

            // get all values related to assigned word and to the formula words
            // and based on this value get the unique word list
            // e.g. if the formula text contains the word "Sales" all values that are related to Sales should be taken into account
            //      $frm_phr_ids is the list of words for the value selection, so in this case it would contain "Sales"
            $frm_phr_ids = zuf_phr_ids ($frm_row['formula_text'], $this->user()->id);
            zu_debug('fv_lst->frm_upd_lst_usr -> frm_phr_ids1 ('.implode(",",$frm_phr_ids).')');

            // add word words for the special formulas
            // e.g. if the formula text contains the special word "prior" and the formula is linked to "Year" and "2016" is a "Year"
            //      than the "prior" of "2016" is "2015", so the word "2015" should be included in the value selection
            $frm_phr_ids = array_unique (array_merge ($frm_phr_ids, $special_frm_phr_ids));
            $frm_phr_ids = array_filter($frm_phr_ids);
            zu_debug('fv_lst->frm_upd_lst_usr -> frm_phr_ids2 ('.implode(",",$frm_phr_ids).')');

            $result_val = $this->add_frm_val($phr_id, $frm_phr_ids, $frm_row, $this->user()->id);
            // $result_val = zuc_upd_lst_val($phr_id, $frm_phr_ids, $frm_row, $this->user()->id);
            $result = array_merge($result, $result_val);

            // show the user the progress every two seconds
            $last_msg_time = zuc_upd_lst_msg($last_msg_time, $collect_pos, mysqli_num_rows($sql_result));
            $collect_pos++;

            Sample:
            update "Sales" "water" "annual growth rate"
            -> get the formulas where any of the value words is used (zuv_frm_lst )
            -> formula "yearly forecast "estimate" "next" = "this" * (1 + "annual growth rate")" because "water" OR "annual growth rate" used
            -> get the list of words of the updated value not used in the formula e.g. "Sales" "Water" ($val_wrd_ids_ex_frm_wrd)
            -> get all values linked to the word list e.g. "Sales" AND "Water" (zuv_lst_of_wrd_ids -> $val_lst_of_wrd_ids)
            -> get the word list for each value excluding the word used in the formula e.g. "Nestlé" "Sales" "Water" "2016" and  "Nestlé" "Sales" "Water" "2017" ($val_wrd_lst_ex_frm_wrd)
            -> calculate the formula result for each word list (zuc_frm)
            -> return the list of formula results e.g. "Nestlé" "Sales" "Water" "2018" "estimate" that have been updated or created ($frm_result_upd_lst)
            -> r) check in which formula the formula results are used
            -> formula "yearly forecast "estimate" "next" = "this" * (1 + "annual growth rate"), because the formula is linked to year and 2018 is a Year
            -> calculate the formula result for each word list of the formula result
            -> return the list of formula results e.g. "Nestlé" "Sales" "Water" "2019" "estimate"
            -> repeat at r)

          }
        }  */
        //}

        //print_r($result);
        log_debug(dsp_count($result->lst));
        return $result;
    }

    /**
     * get the formula value that needs to be recalculated if one formula has been updated
     * TODO should returns a batch_job_list with all formula results that may need to be updated if a formula is updated
     * @param formula $frm - the formula that has been updated
     * $usr - to define which user view should be updated
     */
    function frm_upd_lst(formula $frm, $back)
    {
        log_debug('add ' . $frm->dsp_id() . ' to queue ...');

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
        // e.g. for the formula "net profit" the word "Sales" & "cost of sales" is used
        // for formulas the formula word is used
        $exp = $frm->expression();
        $phr_lst_frm_used = $exp->phr_verb_lst($back);
        log_debug('formula "' . $frm->name() . '" uses ' . $phr_lst_frm_used->name_linked() . ' (taken from ' . $frm->usr_text . ')');

        // get the list of predefined "following" phrases/formulas like "prior" or "next"
        $phr_lst_preset_following = $exp->element_special_following($back);
        $frm_lst_preset_following = $exp->element_special_following_frm($back);

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
        // e.g. to calculate the "increase" of "ABB,Sales" the formula results for "ABB,Sales,increase" should not be used
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
        $phr_lst_fv = $exp->fv_phr_lst();
        if (isset($phr_lst_fv)) {
            log_debug('For ' . $frm->usr_text . ' formula results with the result phrases ' . $phr_lst_fv->dsp_name() . ' should not be used for calculation to avoid loops');
        }

        // depending on the formula setting (all words or at least one word)
        // create a formula value list with all needed word combinations
        // TODO this get all values that
        // 1. have at least one assigned word and one formula word (one of each)
        // 2. remove all assigned words and formula words from the value word list
        // 3. aggregate the word list for all values
        // this is a kind of word group list, where for each word group list several results are possible,
        // because there may be one value and several formula values for the same word group
        log_debug('get all values used in the formula ' . $frm->usr_text . ' that are related to one of the phrases assigned ' . $phr_lst_frm_assigned->dsp_name());
        $phr_grp_lst_val = new phrase_group_list($this->usr); // by default the calling user is used, but if needed the value for other users also needs to be updated
        $phr_grp_lst_val->get_by_val_with_one_phr_each($phr_lst_frm_assigned, $phr_lst_frm_used, $phr_frm, $phr_lst_fv);
        $phr_grp_lst_val->get_by_fv_with_one_phr_each($phr_lst_frm_assigned, $phr_lst_frm_used, $phr_frm, $phr_lst_fv);
        $phr_grp_lst_val->get_by_val_special($phr_lst_frm_assigned, $phr_lst_preset, $phr_frm, $phr_lst_fv); // for predefined formulas ...
        $phr_grp_lst_val->get_by_fv_special($phr_lst_frm_assigned, $phr_lst_preset, $phr_frm, $phr_lst_fv); // ... such as "this"
        $phr_grp_lst_used = clone $phr_grp_lst_val;

        // first calculate the standard values for all user and then the user specific values
        // than loop over the users and check if the user has changed any value, formula or formula assignment
        $usr_lst = new user_list;
        $usr_lst->load_active();

        log_debug('active users (' . dsp_array($usr_lst->names()) . ')');
        foreach ($usr_lst->lst as $usr) {
            // check
            $usr_calc_needed = False;
            if ($usr->id == $this->user()->id) {
                $usr_calc_needed = true;
            }
            if ($this->user()->id == 0 or $usr_calc_needed) {
                log_debug('update values for user: ' . $usr->name . ' and formula ' . $frm->name());

                $result = $this->frm_upd_lst_usr($frm, $phr_lst_frm_assigned, $phr_lst_frm_used, $phr_grp_lst_used, $usr, $last_msg_time, $collect_pos);
            }
        }

        //flush();
        log_debug(dsp_count($result->lst));
        return $result;
    }

    function get_first(): formula_value
    {
        $result = new formula_value($this->usr);
        if (count($this->lst) > 0) {
            $result = $this->lst[0];
        }
        return $result;
    }

    /**
     * create a list of all formula results that needs to be updated if a value is updated
     */
    function val_upd_lst($val, $usr)
    {
        // check if the default value has been updated and if yes, update the default value
        // get all formula values
    }

    /**
     * load all formula values related to one value
     * TODO review: the table value_formula_links is not yet filled
     *              split the backend and frontend part
     *              target is: if a value is changed, what needs to be updated?
     */
    function load_by_val(value $val)
    {
        global $db_con;

        $phr_lst = $val->phr_lst;

        log_debug("fv_lst->val_phr_lst ... for value " . $val->id());
        $result = '';

        // list all related formula results
        $formula_links = '';
        $sql = "SELECT l.formula_id, f.formula_text FROM value_formula_links l, formulas f WHERE l.value_id = " . $val->id() . " AND l.formula_id = f.formula_id;";
        //$db_con = New mysql;
        $db_con->usr_id = $this->user()->id;
        $db_lst = $db_con->get_old($sql);
        if ($db_lst != null) {
            foreach ($db_lst as $db_fv) {
                $frm_id = $db_fv[formula::FLD_ID];
                $formula_text = $db_fv['formula_text'];
                $phr_lst_used = clone $phr_lst;
                if ($val->time_phr != null) {
                    $phr_lst_used->add($val->time_phr);
                }
                $frm = new formula($this->usr);
                $frm->set_id($frm_id);
                $frm->load_obj_vars();
                $back = '';
                $fv_list = $frm->to_num($phr_lst_used);
                $formula_value = $fv_list->get_first();
                // if the formula value is empty use the id to be able to select the formula
                if ($formula_value == '') {
                    $formula_value = $db_fv[formula::FLD_ID];
                }
                $formula_links .= ' <a href="/http/formula_edit.php?id=' . $db_fv[formula::FLD_ID] . '">' . $formula_value . '</a> ';
            }
        }

        if ($formula_links <> '') {
            $result .= ' (or ' . $formula_links . ')';
        }

        log_debug("fv_lst->val_phr_lst ... done.");
        return $result;
    }

    /**
     * create the pure html (5) code for all formula links related to this value list
     * @param back_trace|null $back list of past url calls of the session user
     * @return string the html code part with the formula links
     */
    function frm_links_html(?back_trace $back = null): string
    {
        $result = '';
        $formula_links = '';
        foreach ($this->lst as $fv) {
            $formula_links .= ' <a href="/http/formula_edit.php?id=' . $fv->frm->id . '&back=' . $back->url_encode() . '">' . $fv->number . '</a> ';
        }
        if ($formula_links <> '') {
            $result .= ' (or ' . $formula_links . ')';
        }
        return $result;
    }

    /**
     * add one formula value to the formula value list, but only if it is not yet part of the phrase list
     * @param formula_value $fv_to_add the calculation result that should be added to the list
     */
    function add(formula_value $fv_to_add): void
    {
        log_debug($fv_to_add->dsp_id());
        if (!in_array($fv_to_add->id(), $this->ids())) {
            if ($fv_to_add->id() <> 0) {
                $this->lst[] = $fv_to_add;
            }
        } else {
            log_debug($fv_to_add->dsp_id() . ' not added, because it is already in the list');
        }
    }

    /**
     * combine two calculation queues
     */
    function merge(formula_value_list $lst_to_merge): formula_value_list
    {
        log_debug($lst_to_merge->dsp_id() . ' to ' . $this->dsp_id());
        if (isset($lst_to_merge->lst)) {
            foreach ($lst_to_merge->lst as $new_fv) {
                log_debug('add ' . $new_fv->dsp_id());
                $this->add($new_fv);
            }
        }
        log_debug('to ' . $this->dsp_id());
        return $this;
    }

    /**
     * @return bool true if the list is empty
     */
    function is_empty(): bool
    {
        if (count($this->lst) <= 0) {
            return true;
        } else {
            return false;
        }
    }

}