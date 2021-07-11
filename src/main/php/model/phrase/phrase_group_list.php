<?php

/*

  phrase_group_list.php - a list of word and triple groups
  ---------------------
  
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
  along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.
  
  To contact the authors write to:
  Timon Zielonka <timon@zukunft.com>
  
  Copyright (c) 1995-2021 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

class phrase_group_list
{

    public ?array $lst = null;          // the list of the phrase group objects
    public ?array $time_lst = null;     // the list of the time phrase (the add function)
    public ?array $grp_ids = null;      // the list of the phrase group ids
    public ?array $grp_time_ids = null; // the list of the phrase group and time ids
    public ?user $usr = null;           // the person for whom the word group list has been created

    public ?array $phr_lst_lst = null;  // list of a list of phrases

    /*
    add functions
    */

    // combine the group id and the time id to a unique index
    private function grp_time_id($grp, $time)
    {
        $id = '';
        if (isset($grp)) {
            $grp_id = $grp->id;
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

    // add a phrase group and a time word based on the id
    private function add_grp_time_id($grp_id, $time_id)
    {
        log_debug('phrase_group_list->add_grp_time_id ' . $grp_id . '@' . $time_id);
        $result = false;

        $grp = new phrase_group;
        if ($grp_id > 0) {
            $grp->id = $grp_id;
            $grp->usr = $this->usr;
            $grp->load();
            $grp->load_lst();
            log_debug('phrase_group_list->add_grp_time_id -> found ' . $grp->name());
        }
        $time = new word_dsp;
        if ($time_id > 0) {
            $time->id = $time_id;
            $time->usr = $this->usr;
            $time->load();
            log_debug('phrase_group_list->add_grp_time_id -> found time ' . $time->dsp_id());
        }
        return $this->add_with_time($grp, $time);
    }

    // add a phrase group if the group/time combination is not yet part of the list
    private function add_with_time($grp, $time)
    {
        $result = false;

        $id = $this->grp_time_id($grp, $time);
        log_debug('phrase_group_list->add_with_time ' . $id);
        if ($id <> '') {
            log_debug('phrase_group_list->add_with_time is id ' . $id . ' in ' . implode(",", $this->grp_time_ids));
            if (!in_array($id, $this->grp_time_ids)) {
                log_debug('phrase_group_list->add_with_time id ' . $id . ' add');
                $this->grp_time_ids[] = $id;
                if (isset($grp)) {
                    $this->lst[] = $grp;
                    $this->grp_ids[] = $grp->id;
                    $phr_lst = clone $grp->phr_lst;
                } else {
                    $phr_lst = new phrase_list;
                    $phr_lst->usr = $this->usr;
                    $this->lst[] = null;
                    $this->grp_ids[] = 0;
                }
                if (isset($time)) {
                    $this->time_lst[] = $time;
                    $phr_time = $time->phrase();
                    $phr_lst->add($phr_time);
                } else {
                    $this->time_lst[] = null;
                }
                $this->phr_lst_lst[] = $phr_lst;
                $result = true;
                log_debug($grp->dsp_id() . ' added to list ' . $this->dsp_id());
            } else {
                log_debug($grp->dsp_id() . ' skipped, because is already in list ' . $this->dsp_id());
            }
        }
        return $result;
    }

    // add a phrase group if it is not yet part of the list
    function add($grp)
    {
        log_debug('phrase_group_list->add ' . $grp->id);
        if ($grp->id > 0) {
            if (!in_array($grp->id, $this->grp_ids)) {
                $this->lst[] = $grp;
                $this->grp_ids[] = $grp->id;
                $this->time_lst[] = null;
                log_debug($grp->dsp_id() . ' added to list ' . $this->dsp_id());
            } else {
                log_debug($grp->dsp_id() . ' skipped, because is already in list ' . $this->dsp_id());
            }
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
         so the result would be to add 4 formula values to the list:
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

    todo: check if a value is used in the formula
          exclude the time word and if needed loop over the time words
          if the value has been update, create a calculation request
    */

    // query to get the value or formula result phrase groups and time words that contains at least one phrase of two lists based on the user sandbox
    // e.g. which value that have "Sales" and "2016"?
    private function get_grp_by_phr($type, $phr_linked, $phr_used)
    {
        log_debug('get values because formula is assigned to phrases ' . $phr_linked->name() . ' and phrases ' . $phr_used->name() . ' are used in the formula');

        global $db_con;
        $result = null;

        // separate the time words from the phrases
        $time_linked = $phr_linked->time_lst();
        log_debug('phr_grp_lst->get_grp_by_phr -> time words linked ' . $time_linked->name());
        $time_used = $phr_used->time_lst();
        log_debug('phr_grp_lst->get_grp_by_phr -> time words used ' . $time_used->name());
        $phr_linked_ex = clone $phr_linked;
        $phr_linked_ex->ex_time();
        log_debug('phr_grp_lst->get_grp_by_phr -> linked ex time ' . $phr_linked_ex->name());
        $phr_used_ex = clone $phr_used;
        $phr_used_ex->ex_time();
        log_debug('phr_grp_lst->get_grp_by_phr -> used ex time ' . $phr_used_ex->name());

        // create the group selection
        $sql_group = '';
        if (count($phr_linked->ids) > 0 and count($phr_used->ids) > 0) {
            $sql_group = 'SELECT l1.phrase_group_id
                      FROM phrase_group_phrase_links l1
                 LEFT JOIN user_phrase_group_phrase_links u1 ON u1.phrase_group_phrase_link_id = l1.phrase_group_phrase_link_id 
                                                            AND u1.user_id = ' . $this->usr->id . ',
                           phrase_group_phrase_links l2
                 LEFT JOIN user_phrase_group_phrase_links u2 ON u2.phrase_group_phrase_link_id = l2.phrase_group_phrase_link_id 
                                                            AND u2.user_id = ' . $this->usr->id . '
                     WHERE l1.phrase_id IN (' . $phr_linked_ex->ids_txt() . ')  
                       AND l2.phrase_id IN (' . $phr_used_ex->ids_txt() . ')
                       AND l1.phrase_group_id = l2.phrase_group_id
                       AND COALESCE(u1.excluded, 0) <> 1
                       AND COALESCE(u2.excluded, 0) <> 1
                  GROUP BY l1.phrase_group_id';
        } else {
            // e.g. if "Sales" is assigned, but never  used in the formula no value needs to be calculated, so no group should be used
            //   or if "Sales" is used, but never  assigned to the formula no value needs to be calculated, so no group should be used
            //   or if "Sales" is not used and not assigned to the formula no value needs to be calculated, so no group should be used
            // in all these cases only value selected by time needs to be updated
        }

        // create the time selection
        $sql_time = '';
        if (count($time_linked->ids) > 0 and count($time_used->ids) > 0) {
            $sql_time = 'v.time_word_id IN (' . implode(',', $time_linked->ids) . ')
               AND v.time_word_id IN (' . implode(',', $time_used->ids) . ')';
        } else {
            // dito group
        }

        // create the value or result selection
        if ($type == 'value') {
            $sql_select = 'SELECT v.value_id,
                            v.phrase_group_id,
                            v.time_word_id
                       FROM `values` v';
        } else {
            $sql_select = 'SELECT v.formula_value_id AS value_id,
                            v.phrase_group_id,
                            v.time_word_id
                       FROM formula_values v';
        }

        // combine the selections
        $sql = '';
        $sql_group_by = ' GROUP BY value_id, phrase_group_id, time_word_id LIMIT 500'; // limit is only set for testing: remove for release!
        if ($sql_group <> '') {
            if ($sql_time <> '') {
                // select only values that match both: group and time
                $sql = $sql_select . ', ( ' . $sql_group . ') AS g WHERE v.phrase_group_id = g.phrase_group_id OR ' . $sql_time . $sql_group_by . ';';
            } else {
                // select values only by the group
                $sql = $sql_select . ', ( ' . $sql_group . ') AS g WHERE v.phrase_group_id = g.phrase_group_id' . $sql_group_by . ';';
            }
        } else {
            // select values only by the time
            if ($sql_time <> '') {
                $sql = $sql_select . ' WHERE ' . $sql_time . $sql_group_by . ';';
            } else {
                // dummy selection for an empty result
                $sql = '';
            }
        }

        log_debug('phr_grp_lst->get_grp_by_phr -> sql "' . $sql . '"');
        //$db_con = New mysql;
        $db_con->usr_id = $this->usr->id;
        $result = $db_con->get($sql);
        return $result;
    }

    // combined code add add values assigned by a word or a predefined formula like "this", "prior" or "next"
    private function add_grp_by_phr($type, $frm_linked, $frm_used, $phr_frm, $phr_lst_fv)
    {
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

        log_debug('phr_grp_lst->add_grp_by_phr -> ' . $frm_linked->name() . ' related ' . $type . 's found for ' . $frm_used->name() . ' and user ' . $this->usr->name);
        $added = 0;
        $changed = 0;

        // select is using the phrase groups because this is faster than checking all values or formula result
        // and there cannot be any value or formula result without phrase group
        if (!empty($frm_linked->ids)) {
            $val_rows = $this->get_grp_by_phr($type, $frm_linked, $frm_used);
            foreach ($val_rows as $val_row) {
                // add the phrase group of the value or formula result add the time using a combined index
                // because a time word should never be part of a phrase group to have a useful number of groups
                log_debug('phr_grp_lst->add_grp_by_phr -> add id ' . $val_row['phrase_group_id']);
                log_debug('phr_grp_lst->add_grp_by_phr -> add time id ' . $val_row['time_word_id']);
                // remove the formula name phrase and the result phrases from the value phrases to avoid potentials loops and
                $val_grp = new phrase_group;
                $val_grp->usr = $this->usr;
                $val_grp->id = $val_row['phrase_group_id'];
                $val_grp->load();
                $val_grp->load_lst();
                $used_phr_lst = clone $val_grp->phr_lst;
                log_debug('phr_grp_lst->add_grp_by_phr -> used_phr_lst ' . $used_phr_lst->dsp_id());
                // exclude the formula name
                $used_phr_lst->del($phr_frm);
                log_debug('phr_grp_lst->add_grp_by_phr -> removed formula phrase ' . $phr_frm->dsp_id() . ' from used_phr_lst ' . $used_phr_lst->dsp_id());
                // exclude the result phrases
                $phr_lst_fv_name = '';
                if (isset($phr_lst_fv)) {
                    $used_phr_lst->diff($phr_lst_fv);
                    log_debug('phr_grp_lst->add_grp_by_phr -> removed result phrases ' . $phr_lst_fv->dsp_id() . ' from used_phr_lst ' . $used_phr_lst->dsp_id());
                    $phr_lst_fv_name = $phr_lst_fv->dsp_id();
                }
                // add the group to the calculation list if the group is not yet in the list
                $grp_to_add = $used_phr_lst->get_grp();
                if ($grp_to_add->id <> $val_grp->id) {
                    log_debug('phr_grp_lst->add_grp_by_phr -> group ' . $grp_to_add->dsp_id() . ' used instead of ' . $val_grp->dsp_id() . ' because ' . $phr_frm->dsp_id() . ' and  ' . $phr_lst_fv_name . ' are part of the formula and have been remove from the phrase group selection');
                    $changed++;
                }
                if ($this->add_grp_time_id($grp_to_add->id, $val_row['time_word_id'])) {
                    $added++;
                    $changed++;
                    log_debug('phr_grp_lst->add_grp_by_phr -> added ' . $added . ' in ' . count($this->grp_time_ids));
                }
            }
        }

        log_debug($added . ' ' . $type . 's selected for update because the formula is assigned ' . $frm_linked->name() . ' and uses ' . $frm_used->name() . ' (adding up to "' . $this->name() . '")');
        return $added;
    }

    function get_by_val_with_one_phr_each($frm_linked, $frm_used, $phr_frm, $phr_lst_fv)
    {
        return $this->add_grp_by_phr('value', $frm_linked, $frm_used, $phr_frm, $phr_lst_fv);
    }

    function get_by_val_special($frm_linked, $frm_used_fixed, $phr_frm, $phr_lst_fv)
    {
        return $this->add_grp_by_phr('value', $frm_linked, $frm_used_fixed, $phr_frm, $phr_lst_fv);
    }

    function get_by_fv_with_one_phr_each($frm_linked, $frm_used, $phr_frm, $phr_lst_fv)
    {
        return $this->add_grp_by_phr('formula result', $frm_linked, $frm_used, $phr_frm, $phr_lst_fv);
    }

    //
    function get_by_fv_special($frm_linked, $frm_used_fixed, $phr_frm, $phr_lst_fv)
    {
        return $this->add_grp_by_phr('formula result', $frm_linked, $frm_used_fixed, $phr_frm, $phr_lst_fv);
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
      foreach (array_keys($this->lst) AS $pos) {
        zu_debug('remove "'.implode(",",$del_wrd_lst->names()).'" from ('.implode(",",$this->lst[$pos]->names()).')');
        $this->lst[$pos]->diff_by_ids($del_wrd_lst->ids);
      }
    }
    */

    /*

    get functions

    */

    // return all phrases that are part of each phrase group of the list
    function common_phrases()
    {
        log_debug('phrase_group_list->common_phrases');
        $result = new phrase_list;
        $result->usr = $this->usr;
        $pos = 0;
        foreach ($this->lst as $grp) {
            $grp->load_lst();
            if ($pos == 0) {
                if (isset($grp->phr_lst)) {
                    $result = clone $grp->phr_lst;
                }
            } else {
                if (isset($grp->phr_lst)) {
                    //$result = $result->concat_unique($grp->phr_lst);
                    $result->common($grp->phr_lst);
                }
            }
            log_debug('phrase_group_list->common_phrases ' . $result->name());
            $pos++;
        }
        log_debug('phrase_group_list->common_phrases (' . count($result->lst) . ')');
        return $result;
    }

    /*

      display functions
      -----------------

    */

    // display the unique id fields
    function dsp_id()
    {
        global $debug;
        $result = '';
        // check the object setup
        if (count($this->lst) <> count($this->time_lst)) {
            $result .= 'The number of groups (' . count($this->lst) . ') are not equal the number of times (' . count($this->time_lst) . ') of this phrase group list';
        } else {

            $pos = 0;
            foreach ($this->lst as $phr_lst) {
                if ($debug > $pos) {
                    if ($result <> '') {
                        $result .= ' / ';
                    }
                    $result .= $phr_lst->name();
                    $phr_time = $this->time_lst[$pos];
                    if (is_null($phr_time)) {
                        $result .= '@' . $phr_time->name();
                    }
                    $pos++;
                }
            }
            if (count($this->lst) > $pos) {
                $result .= ' ... total ' . count($this->lst);
            }

        }
        return $result;
    }

    // create a useful (but not unique!) name of the phrase group list mainly used for debugging
    function name(): string
    {
        global $debug;
        $result = '';
        $names = $this->names();
        if ($debug > 10 or count($names) > 3) {
            $main_names = array_slice($names, 0, 3);
            $result .= implode(" and ", $main_names);
            $result .= " ... total " . count($names);
        } else {
            $result .= implode(" and ", $names);
        }
        return $result;
    }

    // return a list of the word names
    function names(): string
    {
        $result = array();
        foreach ($this->lst as $phr_lst) {
            $result[] = $phr_lst->name();
        }
        log_debug('phrase_group_list->names ' . implode(" / ", $result));
        return $result;
    }

    // correct all word groups e.g. that still have a time word
    function check()
    {
    }

}