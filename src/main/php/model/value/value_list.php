<?php

/*

  value_list.php - to show or modify a list of values
  --------------
  
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

class value_list
{

    public ?array $lst = null;               // the list of values
    public ?user $usr = null;                // the person who wants to see ths value list

    // fields to select the values
    public ?phrase $phr = null;              // show the values related to this phrase
    public ?phrase_list $phr_lst = null;     // show the values related to these phrases

    // display and select fields to increase the response time
    public ?int $page_size = SQL_ROW_LIMIT;  // if not defined, use the default page size
    public ?int $page = 0;                   // start to display with this page

    // the general load function (either by word, word list, formula or group)
    function load()
    {

        global $db_con;

        // the id and the user must be set
        if ($this->phr->id > 0 and !is_null($this->usr->id)) {
            log_debug('value_list->load for "' . $this->phr->name . '"');
            $limit = $this->page_size;
            if ($limit <= 0) {
                $limit = SQL_ROW_LIMIT;
            }
            $sql = "SELECT v.value_id,
                     u.value_id AS user_value_id,
                     v.user_id,
                    " . $db_con->get_usr_field('word_value', 'v', 'u') . ",
                    " . $db_con->get_usr_field('excluded', 'v', 'u') . ",
                    " . $db_con->get_usr_field('last_update', 'v', 'u') . ",
                    " . $db_con->get_usr_field('source_id', 'v', 'u') . ",
                     v.phrase_group_id,
                     v.time_word_id,
                     g.word_ids,
                     g.triple_ids
                FROM phrase_groups g, " . $db_con->get_table_name(DB_TYPE_VALUE) . " v 
           LEFT JOIN user_values u ON u.value_id = v.value_id 
                                  AND u.user_id = " . $this->usr->id . " 
               WHERE g.phrase_group_id = v.phrase_group_id 
                 AND v.value_id IN ( SELECT value_id 
                                       FROM value_phrase_links 
                                      WHERE phrase_id = " . $this->phr->id . " 
                                   GROUP BY value_id )
            ORDER BY v.phrase_group_id, v.time_word_id
               LIMIT " . $limit . ";";
            //$db_con = New mysql;
            $db_con->usr_id = $this->usr->id;
            $db_val_lst = $db_con->get($sql);
            foreach ($db_val_lst as $db_val) {
                if (is_null($db_val['excluded']) or $db_val['excluded'] == 0) {
                    $val = new value;
                    $val->id = $db_val['value_id'];
                    $val->usr_cfg_id = $db_val['user_value_id'];
                    $val->owner_id = $db_val['user_id'];
                    $val->usr = $this->usr;
                    $val->owner_id = $db_val['user_id'];
                    $val->number = $db_val['word_value'];
                    $val->source = $db_val['source_id'];
                    $val->last_update = new DateTime($db_val['last_update']);
                    $val->grp_id = $db_val['phrase_group_id'];
                    $val->time_id = $db_val['time_word_id'];
                    $val->wrd_ids = explode(",", $db_val['word_ids']);
                    $val->lnk_ids = explode(",", $db_val['triple_ids']);
                    $this->lst[] = $val;
                }
            }
            log_debug('value_list->load (' . count($this->lst) . ')');
        }
    }

    // load a list of values that are related to a phrase or a list of phrases
    function load_by_phr($limit = 0)
    {

        global $db_con;

        // the id and the user must be set
        if ($this->phr->id > 0 and !is_null($this->usr->id)) {
            log_debug('value_list->load for "' . $this->phr->name . '"');
            if ($limit <= 0) {
                $limit = SQL_ROW_LIMIT;
            }
            $sql = "SELECT v.value_id,
                     u.value_id AS user_value_id,
                     v.user_id,
                    " . $db_con->get_usr_field('word_value', 'v', 'u', sql_db::FLD_FORMAT_VAL) . ",
                    " . $db_con->get_usr_field('excluded', 'v', 'u', sql_db::FLD_FORMAT_VAL) . ",
                    " . $db_con->get_usr_field('last_update', 'v', 'u', sql_db::FLD_FORMAT_VAL) . ",
                    " . $db_con->get_usr_field('source_id', 'v', 'u', sql_db::FLD_FORMAT_VAL) . ",
                     v.phrase_group_id,
                     v.time_word_id
                FROM " . $db_con->get_table_name(DB_TYPE_VALUE) . " v 
           LEFT JOIN user_values u ON u.value_id = v.value_id 
                                  AND u.user_id = " . $this->usr->id . " 
               WHERE v.value_id IN ( SELECT value_id 
                                       FROM value_phrase_links 
                                      WHERE phrase_id = " . $this->phr->id . " 
                                   GROUP BY value_id )
            ORDER BY v.phrase_group_id, v.time_word_id
               LIMIT " . $limit . ";";
            //$db_con = New mysql;
            $db_con->usr_id = $this->usr->id;
            $db_val_lst = $db_con->get($sql);
            foreach ($db_val_lst as $db_val) {
                if (is_null($db_val['excluded']) or $db_val['excluded'] == 0) {
                    $val = new value;
                    $val->id = $db_val['value_id'];
                    $val->usr_cfg_id = $db_val['user_value_id'];
                    $val->owner_id = $db_val['user_id'];
                    $val->usr = $this->usr;
                    $val->owner_id = $db_val['user_id'];
                    $val->number = $db_val['word_value'];
                    $val->source = $db_val['source_id'];
                    $val->last_update = new DateTime($db_val['last_update']);
                    $val->grp_id = $db_val['phrase_group_id'];
                    $val->time_phr = $db_val['time_word_id'];
                    $this->lst[] = $val;
                }
            }
            log_debug('value_list->load_by_phr (' . count($this->lst) . ')');
        }
    }

    // load a list of values that are related to one
    function load_all()
    {

        global $db_con;

        // the id and the user must be set
        if (isset($this->phr_lst)) {
            if (count($this->phr_lst->ids) > 0 and !is_null($this->usr->id)) {
                log_debug('value_list->load_all for ' . $this->phr_lst->dsp_id());
                $sql = "SELECT v.value_id,
                      u.value_id AS user_value_id,
                      v.user_id,
                    " . $db_con->get_usr_field('word_value', 'v', 'u', sql_db::FLD_FORMAT_VAL) . ",
                    " . $db_con->get_usr_field('excluded', 'v', 'u', sql_db::FLD_FORMAT_VAL) . ",
                    " . $db_con->get_usr_field('last_update', 'v', 'u', sql_db::FLD_FORMAT_VAL) . ",
                    " . $db_con->get_usr_field('source_id', 'v', 'u', sql_db::FLD_FORMAT_VAL) . ",
                      v.phrase_group_id,
                      v.time_word_id
                  FROM " . $db_con->get_table_name(DB_TYPE_VALUE) . " v 
            LEFT JOIN user_values u ON u.value_id = v.value_id 
                                    AND u.user_id = " . $this->usr->id . " 
                WHERE v.value_id IN ( SELECT value_id 
                                        FROM value_phrase_links 
                                        WHERE phrase_id IN (" . implode(",", $this->phr_lst->ids()) . ")
                                    GROUP BY value_id )
              ORDER BY v.phrase_group_id, v.time_word_id;";
                //$db_con = New mysql;
                $db_con->usr_id = $this->usr->id;
                $db_val_lst = $db_con->get($sql);
                foreach ($db_val_lst as $db_val) {
                    if (is_null($db_val['excluded']) or $db_val['excluded'] == 0) {
                        $val = new value;
                        $val->id = $db_val['value_id'];
                        $val->usr_cfg_id = $db_val['user_value_id'];
                        $val->owner_id = $db_val['user_id'];
                        $val->usr = $this->usr;
                        $val->owner = $db_val['user_id'];
                        $val->number = $db_val['word_value'];
                        $val->source = $db_val['source_id'];
                        $val->last_update = new DateTime($db_val['last_update']);
                        $val->grp_id = $db_val['phrase_group_id'];
                        $val->time_phr = $db_val['time_word_id'];
                        $this->lst[] = $val;
                    }
                }
                log_debug('value_list->load_all (' . count($this->lst) . ')');
            }
        }
        log_debug('value_list->load_all -> done');
    }

    // load a list of values that are related to all words of the list
    function load_by_phr_lst()
    {

        global $db_con;

        // the word list and the user must be set
        if (count($this->phr_lst->ids) > 0 and !is_null($this->usr->id)) {
            // build the sql statement based in the number of words
            $sql_where = '';
            $sql_from = '';
            $sql_pos = 0;
            foreach ($this->phr_lst->ids as $phr_id) {
                if ($phr_id > 0) {
                    $sql_pos = $sql_pos + 1;
                    $sql_from = $sql_from . " `value_phrase_links` l" . $sql_pos . ", ";
                    if ($sql_pos == 1) {
                        $sql_where = $sql_where . " WHERE l" . $sql_pos . ".`phrase_id` = " . $phr_id . " AND l" . $sql_pos . ".`value_id` = v.`value_id` ";
                    } else {
                        $sql_where = $sql_where . "   AND l" . $sql_pos . ".`phrase_id` = " . $phr_id . " AND l" . $sql_pos . ".`value_id` = v.`value_id` ";
                    }
                }
            }

            if ($sql_where <> '') {
                $sql = "SELECT DISTINCT v.value_id,
                    " . $db_con->get_usr_field('word_value', 'v', 'u', sql_db::FLD_FORMAT_VAL) . ",
                    " . $db_con->get_usr_field('excluded', 'v', 'u', sql_db::FLD_FORMAT_VAL) . ",
                    " . $db_con->get_usr_field('last_update', 'v', 'u', sql_db::FLD_FORMAT_VAL) . ",
                    " . $db_con->get_usr_field('source_id', 'v', 'u', sql_db::FLD_FORMAT_VAL) . ",
                       v.user_id,
                       v.phrase_group_id,
                       v.time_word_id
                  FROM " . $db_con->get_table_name(DB_TYPE_VALUE) . " v 
             LEFT JOIN user_values u ON u.value_id = v.value_id 
                                    AND u.user_id = " . $this->usr->id . " 
                 WHERE v.value_id IN ( SELECT DISTINCT v.value_id 
                                         FROM " . $sql_from . "
                                              " . $db_con->get_table_name(DB_TYPE_VALUE) . " v
                                              " . $sql_where . " )
              ORDER BY v.phrase_group_id, v.time_word_id;";
                log_debug('value_list->load_by_phr_lst sql (' . $sql . ')');
                //$db_con = New mysql;
                $db_con->usr_id = $this->usr->id;
                $db_val_lst = $db_con->get($sql);
                foreach ($db_val_lst as $db_val) {
                    if (is_null($db_val['excluded']) or $db_val['excluded'] == 0) {
                        $val = new value;
                        $val->id = $db_val['value_id'];
                        $val->usr = $this->usr;
                        $val->owner = $db_val['user_id'];
                        $val->number = $db_val['word_value'];
                        $val->source = $db_val['source_id'];
                        $val->last_update = new DateTime($db_val['last_update']);
                        $val->grp_id = $db_val['phrase_group_id'];
                        $val->time_id = $db_val['time_word_id'];
                        $this->lst[] = $val;
                    }
                }
            }
            log_debug('value_list->load_by_phr_lst (' . count($this->lst) . ')');
        }
    }

    // set the word objects for all value in the list if needed
    // not included in load, because sometimes loading of the word objects is not needed
    function load_phrases()
    {
        // loading via word group is the most used case, because to save database space and reading time the value is saved with the word group id
        foreach ($this->lst as $val) {
            $val->load_phrases();
        }
    }

    /*

    data retrival functions

    */

    // get a list with all time phrase used in the complete value list
    function time_lst()
    {
        $all_ids = array();
        foreach ($this->lst as $val) {
            $all_ids = array_unique(array_merge($all_ids, array($val->time_id)));
        }
        $phr_lst = new word_list;
        $phr_lst->usr = $this->usr;
        if (count($all_ids) > 0) {
            $phr_lst->ids = $all_ids;
            $phr_lst->load();
        }
        log_debug('value_list->time_lst (' . count($phr_lst->lst) . ')');
        return $phr_lst;
    }

    // get a list with all unique phrase used in the complete value list
    function phr_lst()
    {
        log_debug('value_list->phr_lst by ids (needs review)');
        $phr_lst = new phrase_list;
        $phr_lst->usr = $this->usr;

        foreach ($this->lst as $val) {
            if (!isset($val->phr_lst)) {
                $val->load();
                $val->load_phrases();
            }
            $phr_lst->merge($val->phr_lst);
        }

        log_debug('value_list->phr_lst (' . count($phr_lst->lst) . ')');
        return $phr_lst;
    }

    // get a list with all unique phrase including the time phrase
    function phr_lst_all()
    {
        log_debug('value_list->phr_lst_all');

        $phr_lst = $this->phr_lst();
        $phr_lst->merge($this->time_lst());

        log_debug('value_list->phr_lst_all -> done');
        return $phr_lst;
    }

    // get a list of all words used for the value list
    function wrd_lst()
    {
        log_debug('value_list->wrd_lst');

        $phr_lst = $this->phr_lst_all();
        $wrd_lst = $phr_lst->wrd_lst_all();

        log_debug('value_list->wrd_lst -> done');
        return $wrd_lst;
    }

    // get a list of all words used for the value list
    function source_lst()
    {
        log_debug('value_list->source_lst');
        $result = array();
        $src_ids = array();

        foreach ($this->lst as $val) {
            if ($val->source_id > 0) {
                log_debug('value_list->source_lst test id ' . $val->source_id);
                if (!in_array($val->source_id, $src_ids)) {
                    log_debug('value_list->source_lst add id ' . $val->source_id);
                    if (!isset($val->source)) {
                        log_debug('value_list->source_lst load id ' . $val->source_id);
                        $val->load_source();
                        log_debug('value_list->source_lst loaded ' . $val->source->name);
                    } else {
                        if ($val->source_id <> $val->source->id) {
                            log_debug('value_list->source_lst load id ' . $val->source_id);
                            $val->load_source();
                            log_debug('value_list->source_lst loaded ' . $val->source->name);
                        }
                    }
                    $result[] = $val->source;
                    $src_ids[] = $val->source_id;
                    log_debug('value_list->source_lst added ' . $val->source->name);
                }
            }
        }

        log_debug('value_list->source_lst -> done');
        return $result;
    }

    /*

    filter and select functions

    */

    // return a value list object that contains only values that match the time word list
    function filter_by_time($time_lst)
    {
        log_debug('value_list->filter_by_time');
        $val_lst = array();
        foreach ($this->lst as $val) {
            // only include time specific value
            if ($val->time_id > 0) {
                // only include values within the specific time periods
                if (in_array($val->time_id, $time_lst->ids)) {
                    $val_lst[] = $val;
                    log_debug('value_list->filter_by_time include ' . $val->name());
                } else {
                    log_debug('value_list->filter_by_time excluded ' . $val->name() . ' because outside the specifid time periods');
                }
            } else {
                log_debug('value_list->filter_by_time excluded ' . $val->name() . ' because this is not time specific');
            }
        }
        $result = clone $this;
        $result->lst = $val_lst;

        log_debug('value_list->filter_by_time (' . count($result->lst) . ')');
        return $result;
    }

    // return a value list object that contains only values that match at least one phrase from the phrase list
    function filter_by_phrase_lst($phr_lst)
    {
        log_debug('value_list->filter_by_phrase_lst ' . count($this->lst) . ' values by ' . $phr_lst->name());
        $result = array();
        foreach ($this->lst as $val) {
            //$val->load_phrases();
            $val_phr_lst = $val->phr_lst;
            if (isset($val_phr_lst)) {
                log_debug('value_list->filter_by_phrase_lst val phrase list ' . $val_phr_lst->name());
            } else {
                log_debug('value_list->filter_by_phrase_lst val no value phrase list');
            }
            $found = false;
            foreach ($val_phr_lst->lst as $phr) {
                //zu_debug('value_list->filter_by_phrase_lst val is '.$phr->name.' in '.$phr_lst->name());
                if (in_array($phr->name, $phr_lst->names())) {
                    if (isset($val_phr_lst)) {
                        log_debug('value_list->filter_by_phrase_lst val phrase list ' . $val_phr_lst->name() . ' is found in ' . $phr_lst->name());
                    } else {
                        log_debug('value_list->filter_by_phrase_lst val found, but no value phrase list');
                    }
                    $found = true; // to make sure that each value is only added once; an improval could be to stop searching after a phrase is found
                }
            }
            if ($found) {
                $result[] = $val;
            }
        }
        $this->lst = $result;

        log_debug('value_list->filter_by_phrase_lst (' . count($this->lst) . ')');
        return $this;
    }

    // selects from a val_lst_phr the best matching value
    // best matching means that all words from word_ids must be matching and the least additional words, because this would be a more specific value
    // used by value_list_dsp->dsp_table
    function get_from_lst($word_ids)
    {
        asort($word_ids);
        log_debug("value_list->get_from_lst ids " . implode(",", $word_ids) . ".");

        $found = false;
        $result = null;
        foreach ($this->lst as $val) {
            if (!$found) {
                log_debug("value_list->get_from_lst -> check " . implode(",", $word_ids) . " with (" . implode(",", $val->ids) . ")");
                $wrd_missing = zu_lst_not_in_no_key($word_ids, $val->ids);
                if (empty($wrd_missing)) {
                    // potential result candidate, because the value has all needed words
                    log_debug("value_list->get_from_lst -> can (" . $val->number . ")");
                    $wrd_extra = zu_lst_not_in_no_key($val->ids, $word_ids);
                    if (empty($wrd_extra)) {
                        // if there is no extra word, it is the correct value
                        log_debug("value_list->get_from_lst -> is (" . $val->number . ")");
                        $found = true;
                        $result = $val;
                    } else {
                        log_debug("value_list->get_from_lst -> is not, because (" . implode(",", $wrd_extra) . ")");
                    }
                }
            }
        }

        log_debug("value_list->get_from_lst -> done (" . $result->number . ")");
        return $result;
    }

    // selects from a val_lst_wrd the best matching value
    // best matching means that all words from word_ids must be matching and the least additional words, because this would be a more specific value
    // used by value_list_dsp->dsp_table
    function get_by_grp($grp, $time)
    {
        log_debug("value_list->get_by_grp " . $grp->auto_name . ".");

        $found = false;
        $result = null;
        $row = 0;
        foreach ($this->lst as $val) {
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
                        log_debug("value_list->get_by_grp -> load group");
                        $val->load_phrases();
                    }
                    if (isset($val->grp)) {
                        if ($row < 6) {
                            log_debug('value_list->get_by_grp -> check if all of ' . $grp->name() . ' are in ' . $val->grp->name() . ' and value should be used');
                        }
                        if ($val->grp->has_all_phrases_of($grp)
                            and $val->time_id == $time->id) {
                            log_debug('value_list->get_by_grp -> all of ' . $grp->name() . ' are in ' . $val->grp->name() . ' so value is used');
                            $found = true;
                            $result = $val;
                        }
                    }
                }
            }
            $row++;
        }

        log_debug("value_list->get_by_grp -> done (" . $result->number . ")");
        return $result;
    }


    /*
      convert functions
      -----------------
    */

    // return a list of phrase groups for all values of this list
    function phrase_groups()
    {
        log_debug('value_list->phrase_groups');
        $grp_lst = new phrase_group_list;
        $grp_lst->usr = $this->usr;
        foreach ($this->lst as $val) {
            if (!isset($val->grp)) {
                $this->load_grp_by_id();
            }
            if (isset($val->grp)) {
                $grp_lst->lst[] = $val->grp;
            } else {
                log_err("The phrase group for value " . $val->id . " cannot be loaded.", "value_list->phrase_groups");
            }
        }

        log_debug('value_list->phrase_groups (' . count($grp_lst->lst) . ')');
        return $grp_lst;
    }


    // return a list of phrases used for each value
    function common_phrases()
    {
        $grp_lst = $this->phrase_groups();
        $phr_lst = $grp_lst->common_phrases();
        log_debug('value_list->common_phrases (' . count($phr_lst->lst) . ')');
        return $phr_lst;
    }

    /*

    check / database consistency functions

    */

    // check the consistency for all values
    // so get the words and triples linked from the word group
    //    and update the slave table value_phrase_links (which should be renamed to value_phrase_links)
    // TODO split into smaller sections by adding LIMIT to the query and start a loop
    function check_all(): bool
    {

        global $db_con;
        $result = true;

        // the id and the user must be set
        $db_con->set_type(DB_TYPE_VALUE);
        $db_con->set_usr($this->usr->id);
        $sql = $db_con->select();
        $db_val_lst = $db_con->get($sql);
        foreach ($db_val_lst as $db_val) {
            $val = new value;
            $val->id = $db_val['value_id'];
            $val->usr = $this->usr;
            $val->load();
            if (!$val->check()) {
                $result = false;
            }
            log_debug('value_list->load_by_phr (' . count($this->lst) . ')');
        }
        log_debug('value_list->check_all (' . count($this->lst) . ')');
        return $result;
    }

    // to be integrated into load
    // list of values related to a formula
    // described by the word to which the formula is assigned
    // and the words used in the formula
    function load_frm_related($phr_id, $phr_ids, $user_id)
    {
        log_debug("value_list->load_frm_related (" . $phr_id . ",ft" . implode(",", $phr_ids) . ",u" . $user_id . ")");

        global $db_con;
        $result = array();

        if ($phr_id > 0 and !empty($phr_ids)) {
            $sql = "SELECT l1.value_id
                FROM value_phrase_links l1,
                    value_phrase_links l2
              WHERE l1.value_id = l2.value_id
                AND l1.phrase_id = " . $phr_id . "
                AND l2.phrase_id IN (" . implode(",", $phr_ids) . ");";
            //$db_con = New mysql;
            $db_con->usr_id = $this->usr->id;
            $db_lst = $db_con->get($sql);
            foreach ($db_lst as $db_val) {
                $result = $db_val['value_id'];
            }
        }

        log_debug("value_list->load_frm_related -> (" . implode(",", $result) . ")");
        return $result;
    }

    // group words
    // kind of similar to zu_sql_val_lst_wrd
    function load_frm_related_grp_phrs_part($val_ids, $phr_id, $phr_ids, $user_id)
    {
        log_debug("value_list->load_frm_related_grp_phrs_part (v" . implode(",", $val_ids) . ",t" . $phr_id . ",ft" . implode(",", $phr_ids) . ",u" . $user_id . ")");

        global $db_con;
        $result = array();

        if ($phr_id > 0 and !empty($phr_ids) and !empty($val_ids)) {
            $phr_ids[] = $phr_id; // add the main word to the exclude words
            $sql = "SELECT l.value_id,
                    " . $db_con->get_usr_field('word_value', 'v', 'u', sql_db::FLD_FORMAT_VAL) . ",
                    l.phrase_id, 
                    v.excluded, 
                    u.excluded AS user_excluded 
                FROM value_phrase_links l,
                    " . $db_con->get_table_name(DB_TYPE_VALUE) . " v 
          LEFT JOIN user_values u ON v.value_id = u.value_id AND u.user_id = " . $user_id . " 
              WHERE l.value_id = v.value_id
                AND l.phrase_id NOT IN (" . implode(",", $phr_ids) . ")
                AND l.value_id IN (" . implode(",", $val_ids) . ")
                AND (u.excluded IS NULL OR u.excluded = 0) 
            GROUP BY l.value_id, l.phrase_id;";
            //$db_con = New mysql;
            $db_con->usr_id = $this->usr->id;
            $db_lst = $db_con->get($sql);
            $value_id = -1; // set to an id that is never used to force the creation of a new entry at start
            foreach ($db_lst as $db_val) {
                if ($value_id == $db_val['value_id']) {
                    $phr_result[] = $db_val['phrase_id'];
                } else {
                    if ($value_id >= 0) {
                        // remember the previous values
                        $row_result[] = $phr_result;
                        $result[$value_id] = $row_result;
                    }
                    // remember the values for a new result row
                    $value_id = $db_val['value_id'];
                    $val_num = $db_val['word_value'];
                    $row_result = array();
                    $row_result[] = $val_num;
                    $phr_result = array();
                    $phr_result[] = $db_val['phrase_id'];
                }
            }
            if ($value_id >= 0) {
                // remember the last values
                $row_result[] = $phr_result;
                $result[$value_id] = $row_result;
            }
        }

        log_debug("value_list->load_frm_related_grp_phrs_part -> (" . zu_lst_dsp($result) . ")");
        return $result;
    }

    // to be integrated into load
    function load_frm_related_grp_phrs($phr_id, $phr_ids, $user_id)
    {
        log_debug("value_list->load_frm_related_grp_phrs (" . $phr_id . ",ft" . implode(",", $phr_ids) . ",u" . $user_id . ")");
        $result = array();

        if ($phr_id > 0 and !empty($phr_ids)) {
            // get the relevant values
            $val_ids = $this->load_frm_related($phr_id, $phr_ids, $user_id);

            // get the word groups for which a formula result is expected
            // maybe exclude word groups already here where not all needed values for the formula are in the database
            $result = $this->load_frm_related_grp_phrs_part($val_ids, $phr_id, $phr_ids, $user_id);
        }

        log_debug("value_list->load_frm_related_grp_phrs -> (" . zu_lst_dsp($result) . ")");
        return $result;
    }

    // return the html code to display all values related to a given word
    // $phr->id is the related word that shoud not be included in the display
    // $this->usr->id is a parameter, because the viewer must not be the owner of the value
    // to do: add back
    function html($back)
    {
        log_debug('value_list->html (' . count($this->lst) . ')');
        $result = '';

        // get common words
        $common_phr_ids = array();
        foreach ($this->lst as $val) {
            if ($val->check() > 0) {
                log_warning('The group id for value ' . $val->id . ' has not been updated, but should now be correct.', "value_list->html");
            }
            $val->load_phrases();
            log_debug('value_list->html loaded');
            $val_phr_lst = $val->phr_lst;
            if (count($val_phr_lst) > 0) {
                log_debug('value_list->html -> get words ' . $val->phr_lst->dsp_id() . ' for "' . $val->number . '" (' . $val->id . ')');
                if (empty($common_phr_ids)) {
                    $common_phr_ids = $val_phr_lst->ids;
                } else {
                    $common_phr_ids = array_intersect($common_phr_ids, $val_phr_lst->ids);
                }
            }
        }

        log_debug('value_list->html common ');
        $common_phr_ids = array_diff($common_phr_ids, array($this->phr->id));  // exclude the list word
        $common_phr_ids = array_values($common_phr_ids);            // cleanup the array

        // display the common words
        log_debug('value_list->html common dsp');
        if (!empty($common_phr_ids)) {
            $commen_phr_lst = new word_list;
            $commen_phr_lst->ids = $common_phr_ids;
            $commen_phr_lst->usr = $this->usr;
            $commen_phr_lst->load();
            $result .= ' in (' . implode(",", $commen_phr_lst->names_linked()) . ')<br>';
        }

        // instead of the saved result maybe display the calculated result based on formulas that matches the word pattern
        log_debug('value_list->html tbl_start');
        $result .= dsp_tbl_start();

        // the reused button object
        $btn = new button;

        // to avoid repeating the same words in each line and to offer a useful "add new value"
        $last_phr_lst = array();

        log_debug('value_list->html add new button');
        if (isset($this->lst)) {
            log_debug('value_list->html add new button loop');
            foreach ($this->lst as $val) {
                //$this->usr->id  = $val->usr->id;

                // get the words
                $val->load_phrases();
                if (isset($val->phr_lst)) {
                    $val_phr_lst = $val->phr_lst;

                    // remove the main word from the list, because it should not be shown on each line
                    log_debug('value_list->html -> remove main ' . $val->id);
                    $dsp_phr_lst = clone $val_phr_lst;
                    log_debug('value_list->html -> cloned ' . $val->id);
                    if (isset($this->phr)) {
                        if (isset($this->phr->id)) {
                            $dsp_phr_lst->diff_by_ids(array($this->phr->id));
                        }
                    }
                    log_debug('value_list->html -> removed ' . $this->phr->id);
                    $dsp_phr_lst->diff_by_ids($common_phr_ids);
                    log_debug('value_list->html -> removed ' . implode(',', $this->phr->id));
                    // remove the words of the privious row, because it should not be shown on each line
                    if (isset($last_phr_lst->ids)) {
                        $dsp_phr_lst->diff_by_ids($last_phr_lst->ids);
                    }
                    log_debug('value_list->html -> removed ' . implode(',', $this->phr->id));

                    //if (isset($val->time_phr)) {
                    log_debug('value_list->html -> add time ' . $val->id);
                    if ($val->time_id > 0) {
                        $time_phr = new phrase;
                        $time_phr->id = $val->time_id;
                        $time_phr->usr = $val->usr;
                        $time_phr->load();
                        $val->time_phr = $time_phr;
                        $dsp_phr_lst->add($time_phr);
                        log_debug('value_list->html -> add time word ' . $val->time_phr->name);
                    }

                    $result .= '  <tr>';
                    $result .= '    <td>';
                    log_debug('value_list->html -> linked words ' . $val->id);
                    $result .= '      ' . $dsp_phr_lst->name_linked() . ' <a href="/http/value_edit.php?id=' . $val->id . '&back=' . $this->phr->id . '">' . $val->val_formatted() . '</a>';
                    log_debug('value_list->html -> linked words ' . $val->id . ' done');
                    // to review
                    // list the related formula values
                    $fv_lst = new formula_value_list;
                    $fv_lst->usr = $this->usr;
                    $result .= $fv_lst->val_phr_lst($val, $this->phr->id, $val_phr_lst, $val->time_id);
                    $result .= '    </td>';
                    log_debug('value_list->html -> formula results ' . $val->id . ' loaded');

                    if ($last_phr_lst != $val_phr_lst) {
                        $last_phr_lst = $val_phr_lst;
                        $result .= '    <td>';
                        $result .= btn_add_value($val_phr_lst, Null, $this->phr->id);

                        $result .= '    </td>';
                    }
                    $result .= '    <td>';
                    $result .= '      ' . $btn->edit_value($val_phr_lst, $val->id, $this->phr->id);
                    $result .= '    </td>';
                    $result .= '    <td>';
                    $result .= '      ' . $btn->del_value($val_phr_lst, $val->id, $this->phr->id);
                    $result .= '    </td>';
                    $result .= '  </tr>';
                }
            }
        }
        log_debug('value_list->html add new button done');

        $result .= dsp_tbl_end();

        // allow the user to add a completely new value
        log_debug('value_list->html new');
        if (empty($common_phr_ids)) {
            $commen_phr_lst = new word_list;
            $common_phr_ids[] = $this->phr->id;
            $commen_phr_lst->ids = $common_phr_ids;
            $commen_phr_lst->usr = $this->usr;
            $commen_phr_lst->load();
        }

        $commen_phr_lst = $commen_phr_lst->phrase_lst();

        // to review probably wrong call from /var/www/default/src/main/php/model/view/view.php(267): view_component_dsp->all(Object(word_dsp), 291, 17
        if (get_class($this->phr) == 'word' or get_class($this->phr) == 'word_dsp') {
            $this->phr = $this->phr->phrase();
        }
        if (isset($commen_phr_lst)) {
            if (!empty($commen_phr_lst->lst)) {
                $commen_phr_lst->add($this->phr);
                $result .= $commen_phr_lst->btn_add_value($back);
            }
        }

        log_debug("value_list->html ... done");

        return $result;
    }


}

?>
