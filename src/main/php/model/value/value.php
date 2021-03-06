<?php

/*

  value.php - the main number object
  ---------
  
  Common object for the tables values, user_values,
  in the database the object is save in two tables 
  because it is expected that there will be much less user values than standard values
  
  TODO: what happens if a user (not the value owner) is adding a word to the value
  
  if the value is not used at all the adding of the new word is logged and the group change is updated without logging
  if the value is used, adding, changing or deleting a word creates a new value or updates an existing value 
     and the logging is done according new value (add all words) or existing value (value modified by the user)
  
  
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

class value extends user_sandbox_display
{

    // database fields additional to the user sandbox fields for the value object
    public ?int $source_id = null;        // the id of source where the value is coming from
    public ?int $grp_id = null;           // id of the group of phrases that are linked to this value for fast selections
    public ?int $time_id = null;          // id of the main time period word for fast time seres creation selections
    public ?DateTime $time_stamp = null;  // the time stamp for this value (if this is set, the time wrd is supposed to be empty and the value is saved in the time_series table)
    public ?DateTime $last_update = null; // the time of the last update of fields that may influence the calculated results

    // derived database fields for fast selection (needs to be verified from time to time to check the database consistency and detect program errors)
    // field set by the front end scripts such as value_add.php or value_edit.php
    public ?array $ids = null;            // list of the word or triple ids (if > 0 id of a word if < 0 id of a triple)
    public ?array $phr_lst = null;        // the phrase object list for this value
    //public $phr_ids       = null;       // the phrase id list for this value loaded directly from the group
    public ?array $wrd_lst = null;        // the word object list for this value
    public ?array $wrd_ids = null;        // the word id list for this value loaded directly from the group
    public ?array $lnk_lst = null;        // the triple object list  for this value
    public ?array $lnk_ids = null;        // the triple id list  for this value loaded directly from the group
    // public $phr_all_lst  = null;       // $phr_lst including the time wrd
    // public $phr_all_ids  = null;       // $phr_ids including the time id
    public ?phrase $grp = null;           // phrases (word or triple) group object for this value
    public ?phrase $time_phr = null;      // the time (period) word object for this value
    public ?DateTime $update_time = null; // time of the last update, which could also be taken from the change log
    public ?source $source = null;        // the source object

    // field for user interaction
    public ?string $usr_value = null;     // the raw value as the user has entered it including formatting chars such as the thousand separator


    function __construct()
    {
        $this->obj_type = user_sandbox::TYPE_VALUE;
        $this->obj_name = DB_TYPE_VALUE;

        $this->rename_can_switch = UI_CAN_CHANGE_VALUE;
    }

    function reset()
    {
        $this->id = null;
        $this->usr_cfg_id = null;
        $this->usr = null;
        $this->owner_id = null;
        $this->excluded = null;

        $this->number = null;
        $this->source_id = null;
        $this->grp_id = null;
        $this->time_id = null;
        $this->time_stamp = null;
        $this->last_update = null;

        $this->ids = null;
        $this->phr_lst = null;
        $this->wrd_lst = null;
        $this->wrd_ids = null;
        $this->lnk_lst = null;
        $this->lnk_ids = null;
        $this->grp = null;
        $this->time_phr = null;
        $this->update_time = null;
        $this->source = null;
        $this->share_id = null;
        $this->protection_id = null;

        $this->usr_value = '';
    }

    private function row_mapper($db_row, $map_usr_fields = false)
    {
        if ($db_row != null) {
            if ($db_row['value_id'] > 0) {
                $this->id = $db_row['value_id'];
                $this->number = $db_row['word_value'];
                // check if phrase_group_id and time_word_id are user specific or time series specific
                $this->grp_id = $db_row['phrase_group_id'];
                $this->time_id = $db_row['time_word_id'];
                $this->source_id = $db_row['source_id'];
                $this->last_update = new DateTime($db_row['last_update']);
                $this->excluded = $db_row['excluded'];
                if ($map_usr_fields) {
                    $this->usr_cfg_id = $db_row['user_value_id'];
                    $this->owner_id = $db_row['user_id'];
                    $this->share_id = $db_row['share_type_id'];
                    $this->protection_id = $db_row['protection_type_id'];
                } else {
                    $this->share_id = cl(DBL_SHARE_PUBLIC);
                    $this->protection_id = cl(DBL_PROTECT_NO);
                }
            } else {
                $this->id = 0;
            }
        } else {
            $this->id = 0;
        }
    }


    /*

    database load functions that reads the object from the database

    */

    // load the standard value use by most users
    function load_standard(): bool
    {
        global $db_con;
        $result = false;

        if ($this->id > 0) {
            $db_con->set_type(DB_TYPE_VALUE);
            $db_con->set_usr($this->usr->id);
            $db_con->set_fields(array('value_id', 'user_id', 'word_value', 'source_id', 'last_update', 'excluded', 'protection_type_id'));
            $db_con->where(array('value_id'), array($this->id));
            $sql = $db_con->select();

            if ($db_con->get_where() <> '') {
                $db_val = $db_con->get1($sql);
                $this->row_mapper($db_val);
                $result = $this->load_owner();
            }
        }
        return $result;
    }

    // load the record from the database
    // in a separate function, because this can be called twice from the load function
    function load_rec($sql_where)
    {
        global $db_con;

        $db_con->set_type(DB_TYPE_VALUE);
        $db_con->set_usr($this->usr->id);
        $db_con->set_fields(array('phrase_group_id', 'time_word_id'));
        $db_con->set_usr_fields(array('word_value', 'source_id', 'last_update', 'protection_type_id', 'excluded'));
        $db_con->set_usr_only_fields(array('share_type_id'));
        $db_con->set_where_text($sql_where);
        $sql = $db_con->select();

        $db_val = $db_con->get1($sql);
        $this->row_mapper($db_val, true);
        if ($this->id > 0) {
            log_debug('value->load_rec -> got ' . $this->number . ' with id ' . $this->id);
        }
    }

    // load the missing value parameters from the database
    function load(): bool
    {

        global $db_con;
        $result = true;

        // check the all minimal input parameters
        if (!isset($this->usr)) {
            log_err('The user id must be set to load a result.', 'value->load');
        } else {
            log_debug('value->load');

            $sql_where = '';
            if ($this->id > 0) {
                $sql_where = 's.value_id = ' . $this->id;
            } elseif ($this->grp_id > 0) {
                $sql_where = 's.phrase_group_id = ' . $this->grp_id;
                if ($this->time_id > 0) {
                    $sql_where .= ' AND s.time_word_id = ' . $this->time_id . ' ';
                }
            } elseif (!empty($this->ids)) {
                $this->set_grp_and_time_by_ids();
                if ($this->grp_id > 0) {
                    $sql_where = 's.phrase_group_id = ' . $this->grp_id;
                    if ($this->time_id > 0) {
                        $sql_where .= ' AND s.time_word_id = ' . $this->time_id . ' ';
                    }
                }
            } else {
                // if no value for a word group is found it is not an error, this is why here the error message is not at the same point as in other load methods
                if (!empty($this->ids)) {
                    log_err('Either the database ID (' . $this->id . '), the word group (' . $this->grp_id . ') or the word list (' . implode(",", $this->ids) . ') and the user (' . $this->usr->id . ') must be set to load a value.', 'value->load');
                } else {
                    log_err('Either the database ID (' . $this->id . '), the word group (' . $this->grp_id . ')  and the user (' . $this->usr->id . ') must be set to load a value.', 'value->load');
                }
            }

            // check if a valid identification is given and load the result
            if ($sql_where <> '') {
                log_debug('value->load -> by "' . $sql_where . '"');
                $this->load_rec($sql_where);

                // if not direct value is found try to get a more specific value
                // similar to formula_value
                if ($this->id <= 0 and isset($this->phr_lst)) {
                    log_debug('value->load try best guess');
                    $phr_lst = clone $this->phr_lst;
                    if ($this->time_id <= 0) {
                        $time_phr = $this->phr_lst->time_useful();
                        $this->time_id = $time_phr->id;
                    }
                    $phr_lst->ex_time();
                    if (count($phr_lst->lst) > 0) {
                        // the phrase groups with the least number of additional words that have at least one formula value
                        $sql_grp_from = '';
                        $sql_grp_where = '';
                        $pos = 1;
                        foreach ($phr_lst->lst as $phr) {
                            if ($sql_grp_from <> '') {
                                $sql_grp_from .= ',';
                            }
                            $sql_grp_from .= 'phrase_group_word_links l' . $pos;
                            $pos_prior = $pos - 1;
                            if ($sql_grp_where <> '') {
                                $sql_grp_where .= ' AND l' . $pos_prior . '.phrase_group_id = l' . $pos . '.phrase_group_id AND ';
                            }
                            $sql_grp_where .= ' l' . $pos . '.word_id = ' . $phr->id;
                            $pos++;
                        }
                        $sql_grp = 'SELECT l1.phrase_group_id 
                          FROM ' . $sql_grp_from . ' 
                         WHERE ' . $sql_grp_where;
                        // todo:
                        // count the number of phrases per group
                        // and add the user specific phrase links
                        // select also the time
                        $sql_time = '';
                        if (isset($this->time_stamp)) {
                            $sql_val = "SELECT value_time_series_id 
                            FROM `value_time_series`
                          WHERE phrase_group_id IN (" . $sql_grp . ");";
                        } else {
                            if ($this->time_id > 0) {
                                $sql_time = ' AND time_word_id = ' . $this->time_id . ' ';
                            }
                            $sql_val = "SELECT value_id 
                            FROM `values`
                          WHERE phrase_group_id IN (" . $sql_grp . ") " . $sql_time . ";";
                        }
                        log_debug('value->load sql val "' . $sql_val . '"');
                        //$db_con = new mysql;
                        $db_con->usr_id = $this->usr->id;
                        $val_ids_rows = $db_con->get($sql_val);
                        if (count($val_ids_rows) > 0) {
                            $val_id_row = $val_ids_rows[0];
                            $this->id = $val_id_row['value_id'];
                            if ($this->id > 0) {
                                $sql_where = "s.value_id = " . $this->id;
                                $this->load_rec($sql_where);
                                log_debug('value->loaded best guess id (' . $this->id . ')');
                            }
                        }
                    }
                }
            }
        }
        log_debug('value->load -> got ' . $this->number . ' with id ' . $this->id);
        return $result;
    }

    // get the best matching value
    // 1. try to find a value with simply a different scaling e.g. if the number of share are requested, but this is in millions in the database use and scale it
    // 2. check if another measure type can be converted      e.g. if the share price in USD is requested, but only in EUR is in the database convert it
    // e.g. for "ABB","Sales","2014" the value for "ABB","Sales","2014","million","CHF" will be loaded,
    //      because most values for "ABB", "Sales" are in ,"million","CHF"
    function load_best()
    {
        log_debug('value->load_best for ' . $this->dsp_id());
        $this->load();
        // if not found try without scaling
        if ($this->id <= 0) {
            $this->load_phrases();
            if (!isset($this->phr_lst)) {
                log_err('No phrases found for ' . $this->dsp_id() . '.', 'value->load_best');
            } else {
                // try to get a value with another scaling
                $phr_lst_unscaled = clone $this->phr_lst;
                $phr_lst_unscaled->ex_scaling();
                log_debug('value->load_best try unscaled with ' . $phr_lst_unscaled->dsp_id());
                $grp_unscale = $phr_lst_unscaled->get_grp();
                $this->grp_id = $grp_unscale->id;
                $this->load();
                // if not found try with converted measure
                if ($this->id <= 0) {
                    // try to get a value with another measure
                    $phr_lst_converted = clone $phr_lst_unscaled;
                    $phr_lst_converted->ex_measure();
                    log_debug('value->load_best try converted with ' . $phr_lst_converted->dsp_id());
                    $grp_unscale = $phr_lst_converted->get_grp();
                    $this->grp_id = $grp_unscale->id;
                    $this->load();
                    // todo:
                    // check if there are any matching values at all
                    // if yes, get the most often used phrase
                    // repeat adding a phrase until a number is found
                }
            }
        }
        log_debug('value->load_best got ' . $this->number . ' for ' . $this->dsp_id());
    }

    /*

  load object functions that extends the database load functions

  */


    // called from the user sandbox
    function load_objects(): bool
    {
        $this->load_phrases();
    }

    // load the phrase objects for this value if needed
    // not included in load, because sometimes loading of the word objects is not needed
    // maybe rename to load_objects
    // NEVER call the dsp_id function from this function or any called function, because this would lead to an endless loop
    function load_phrases()
    {
        log_debug('value->load_phrases');
        // loading via word group is the most used case, because to save database space and reading time the value is saved with the word group id
        if ($this->grp_id > 0) {
            $this->load_grp_by_id();
        }
        log_debug('value->load_phrases load time');
        $this->load_time_phrase();
        log_debug('value->load_phrases -> done (' . (new Exception)->getTraceAsString() . ')');
    }

    // load the source object
    // what happens if a source is updated
    function load_source()
    {
        $src = null;
        log_debug('value->load_source for ' . $this->dsp_id());

        $do_load = false;
        if (isset($this->source)) {
            if ($this->source_id == $this->source->id) {
                $src = $this->source;
            } else {
                $do_load = true;
            }
        } else {
            $do_load = true;
        }
        if ($do_load) {
            if ($this->source_id > 0) {
                $src = new source;
                $src->id = $this->source_id;
                $src->usr = $this->usr;
                $src->load();
                $this->source = $src;
            } else {
                $this->source = null;
            }
        }

        if (isset($src)) {
            log_debug('value->load_source -> ' . $src->dsp_id());
        } else {
            log_debug('value->load_source done');
        }
        return $src;
    }

    // rebuild the word and triple list based on the group id
    function load_grp_by_id()
    {
        // if the group object is missing
        if (!isset($this->grp)) {
            if ($this->grp_id > 0) {
                // ... load the group related objects means the word and triple list
                $grp = new phrase_group;
                $grp->id = $this->grp_id;
                $grp->usr = $this->usr; // in case the word names and word links can be user specific maybe the owner should be used here
                $grp->get();
                $grp->load_lst(); // to make sure that the word and triple object lists are loaded
                if ($grp->id > 0) {
                    $this->grp = $grp;
                }
            }
        }

        // if a list object is missing
        if (!isset($this->wrd_lst) or !isset($this->lnk_lst)) {
            if (isset($this->grp)) {
                $this->set_lst_by_grp();

                // these if's are only needed for debugging to avoid accessing an unset object, which would cause a crash
                if (isset($this->phr_lst)) {
                    log_debug('value->load_grp_by_id got ' . $this->phr_lst->name() . ' from group ' . $this->grp_id . ' for "' . $this->usr->name . '"');
                }
                if (isset($this->wrd_lst)) {
                    if (isset($this->lnk_lst)) {
                        log_debug('value->load_grp_by_id -> both');
                        log_debug('value->load_grp_by_id with words ' . $this->wrd_lst->name() . ' ');
                        log_debug('value->load_grp_by_id with words ' . $this->wrd_lst->name() . ' and triples ' . $this->lnk_lst->name() . ' ');
                        log_debug('value->load_grp_by_id with words ' . $this->wrd_lst->name() . ' and triples ' . $this->lnk_lst->name() . ' by group ' . $this->grp_id . ' for "' . $this->usr->name . '"');
                    } else {
                        log_debug('value->load_grp_by_id with words ' . $this->wrd_lst->name() . ' by group ' . $this->grp_id . ' for "' . $this->usr->name . '"');
                    }
                } else {
                    log_debug('value->load_grp_by_id ' . $this->grp_id . ' for "' . $this->usr->name . '"');
                }
            }
        }
        log_debug('value->load_grp_by_id -> done');
    }

    // set the list objects based on the loaded phrase group
    // function to set depending objects based on loaded objects
    function set_lst_by_grp()
    {
        if (isset($this->grp)) {
            $this->grp_id = $this->grp->id;
            if (!isset($this->phr_lst)) {
                $this->phr_lst = $this->grp->phr_lst;
            }
            if (!isset($this->wrd_lst)) {
                $this->wrd_lst = $this->grp->wrd_lst;
            }
            if (!isset($this->lnk_lst)) {
                $this->lnk_lst = $this->grp->lnk_lst;
            }
            $this->ids = $this->grp->ids;
        }
    }

    // just load the time word object based on the id loaded from the database
    function load_time_phrase()
    {
        log_debug('value->load_time_phrase');
        $do_load = false;

        if (isset($this->time_phr)) {
            if ($this->time_phr->id <> $this->time_id) {
                $do_load = true;
            }
        } else {
            $do_load = true;
        }
        if ($do_load) {
            if ($this->time_id <> 0) {
                log_debug('value->load_time_phrase -> load');
                $time_phr = new phrase;
                $time_phr->id = $this->time_id;
                $time_phr->usr = $this->usr;
                $time_phr->load();
                $this->time_phr = $time_phr;
                log_debug('value->load_time_phrase -> got ' . $time_phr->dsp_id());
            } else {
                $this->time_phr = null;
            }
        }
        log_debug('value->load_time_phrase done');
    }

    // load the source and return the source name
    function source_name()
    {
        $result = '';
        log_debug('value->source_name');
        log_debug('value->source_name for ' . $this->dsp_id());

        if ($this->source_id > 0) {
            $this->load_source();
            if (isset($this->source)) {
                $result = $this->source->name;
            }
        }
        return $result;
    }

    /*

  load object functions that extends the frontend functions

  */

    //
    function set_grp_and_time_by_ids()
    {
        log_debug('value->set_grp_and_time_by_ids');
        // 1. load the phrases parameters based on the ids
        $result = $this->set_phr_lst_by_ids();
        // 2. extract the time from the phrase list
        $result .= $this->set_time_by_phr_lst();
        // 3. get the group based on the phrase list
        $result .= $this->set_grp_by_ids();
        if ($this->ids == null) {
            log_debug('value->set_grp_and_time_by_ids ids are null');
        } else {
            log_debug('value->set_grp_and_time_by_ids "' . implode(",", $this->ids) . '" to "' . $this->grp_id . '" and ' . $this->time_id);
        }
        return $result;
    }

    // rebuild the phrase list based on the phrase ids
    function set_phr_lst_by_ids()
    {
        $result = '';

        // check the parameters
        if (empty($this->usr)) {
            log_err('User must be set to load ' . $this->dsp_id(), 'phrase_list->load');
        } else {
            if (empty($this->phr_lst)) {
                if (!empty($this->ids)) {
                    log_debug('value->set_phr_lst_by_ids for "' . implode(",", $this->ids) . '" and "' . $this->usr->name . '"');
                    $phr_lst = new phrase_list;
                    $phr_lst->ids = $this->ids;
                    $phr_lst->usr = $this->usr;
                    $phr_lst->load();
                    $this->phr_lst = $phr_lst;
                }
            }
        }
        return $result;
    }

    // get the time based on the phrase id list
    function set_time_by_phr_lst()
    {
        $result = '';
        if (isset($this->phr_lst)) {
            log_debug('value->set_time_by_phr_lst from ' . $this->phr_lst->name());
            if (!isset($this->time_id)) {
                if (isset($this->time_phr)) {
                    $this->time_id = $this->time_phr->id;
                } else {
                    $wrd_lst = $this->phr_lst->wrd_lst_all();
                    $this->time_phr = $wrd_lst->assume_time();
                    $this->time_id = $this->time_phr->id;
                    log_debug('value->set_time_by_phr_lst got ' . $this->time_phr->name . ' for user ' . $this->time_phr->usr->name);
                }
            }
        }
        return $result;
    }

    // rebuild the word and triple list based on the word and triple ids
    // add set the time_id if needed
    function set_grp_by_ids()
    {
        $result = '';
        if (!isset($this->grp)) {
            if (!empty($this->ids)) {
                log_debug('value->set_grp_by_ids for ids "' . implode(",", $this->ids) . '" for "' . $this->usr->name . '"');
                $grp = new phrase_group;
                $grp->ids = $this->ids;
                $grp->usr = $this->usr; // in case the word names and word links can be user specific maybe the owner should be used here
                $grp->get();
                if ($grp->id > 0) {
                    $this->grp = $grp;
                    $this->grp_id = $grp->id;
                    /* actually not needed
                    $this->set_lst_by_grp();
                    if (isset($this->wrd_lst)) {
                        zu_debug('value->set_grp_by_ids -> got '.$this->wrd_lst->name().' for '.implode(',',$this->ids).'');
                    }
                    */
                }
            }
        }
        log_debug('value->set_grp_by_ids -> group set to id ' . $this->grp_id);
        return $result;
    }

    // exclude the time period word from the phrase list
    function set_phr_lst_ex_time()
    {
        log_debug('value->set_phr_lst_ex_time for "' . $this->phr_lst->name() . '" for "' . $this->usr->name . '"');
        $result = '';
        $this->phr_lst->ex_time();
        return $result;
    }

    /*


  */

    // to be dismissed
    // set the word list object for this value if needed
    // to be dismissed, but used by value_list->html at the moment
    function load_wrd_lst()
    {
        log_debug('value->load_wrd_lst');

        global $db_con;

        if ($this->wrd_lst == NUll) {
            // loading via word group is the most used case, because to save database space and reading time the value is saved with the word group id
            if ($this->grp_id > 0) {
                log_debug('value->load_wrd_lst by group id');
                $this->load_grp_by_id();
                log_debug('value->load_wrd_lst by group id done');
            } else {
                // when adding new values only the word IDs are known
                log_debug('value->load_wrd_lst by ids');
                if (isset($this->ids)) {
                    log_debug('value->load_wrd_lst by ids do');
                    $this->set_grp_by_ids();
                    log_debug('value->load_wrd_lst ' . $this->wrd_lst->name() . '" by ids ' . $this->ids . ' for "' . $this->usr->name . '"');
                } else {
                    if ($this->id > 0) {
                        // rebuild word ids based on the link table
                        $sql = "SELECT phrase_id FROM value_phrase_links WHERE value_id = " . $this->id . " GROUP BY phrase_id;";
                        //$db_con = new mysql;
                        $db_con->usr_id = $this->usr->id;
                        $wrd_lnk_lst = $db_con->get($sql);
                        $wrd_ids = array();
                        foreach ($wrd_lnk_lst as $wrd_lnk) {
                            $wrd_ids[] = $wrd_lnk['phrase_id'];
                        }
                        // todo: add the triple links
                        $this->ids = $wrd_ids;
                        $this->set_grp_by_ids();
                    } else {
                        log_err("Missing value id", "value->load_wrd_lst");
                    }
                }
            }
        }
        log_debug('value->load_wrd_lst -> done (trace ' . (new Exception)->getTraceAsString() . ')');
    }

    // to be dismissed
    // a list of all word links related to a given value with the id of the linked word
    // used by value_edit.php
    function wrd_link_lst()
    {
        log_debug("value->wrd_link_lst (" . $this->id . " and user " . $this->usr->name . ")");

        global $db_con;
        $result = array();

        if ($this->id > 0) {
            $sql = "SELECT l.value_phrase_link_id,
                    t.word_id
                FROM value_phrase_links l
          LEFT JOIN words t      ON l.phrase_id = t.word_id  
          LEFT JOIN user_words u ON t.phrase_id = u.word_id AND u.user_id  = " . $this->usr->id . "  
              WHERE l.value_id = " . $this->id . " 
            GROUP BY t.word_id, t.values, t.word_name
            ORDER BY t.values, t.word_name;";
            //$db_con = new mysql;
            $db_con->usr_id = $this->usr->id;
            $db_lst = $db_con->get($sql);
            foreach ($db_lst as $db_row) {
                $id = $db_row['value_phrase_link_id'];
                $result[$id] = $db_row['word_id'];
            }
        } else {
            log_err("Missing value id", "value->wrd_link_lst");
        }

        return $result;
    }

    /*

  consistency check functions

  */

    // check the data consistency of this user value
    // e.g. update the value_phrase_links database table based on the group id
    function check(): bool
    {
        $result = true;

        // reload the value to include all changes
        log_debug('value->check id ' . $this->id . ', for user ' . $this->usr->name);
        $this->load();
        log_debug('value->check load phrases');
        $this->load_phrases();
        log_debug('value->check phrases loaded');

        // remove duplicate entries in value phrase link table
        if (!$this->upd_phr_links()) {
            $result = false;
        }

        log_debug('value->check done');
        return $result;
    }

    // scale a value for the target words
    // e.g. if the target words contains "millions" "2'100'000" is converted to "2.1"
    //      if the target words are empty convert "2.1 mio" to "2'100'000"
    // once this is working switch on the call in word_list->value_scaled
    function scale($target_wrd_lst)
    {
        log_debug('value->scale ' . $this->number);
        // fallback value
        $result = $this->number;

        $this->load_phrases();

        // check input parameters
        if (is_null($this->number)) {
            // this test should be done in the calling function if needed
            // zu_info("To scale a value the number should not be empty.", "value->scale");
        } elseif (is_null($this->usr->id)) {
            log_warning("To scale a value the user must be defined.", "value->scale");
        } elseif (is_null($this->wrd_lst)) {
            log_warning("To scale a value the word list should be loaded by the calling method.", "value->scale");
        } else {
            log_debug('value->scale ' . $this->number . ' for ' . $this->wrd_lst->name() . ' (user ' . $this->usr->id . ')');

            // if it has a scaling word, scale it to one
            if ($this->wrd_lst->has_scaling()) {
                log_debug('value->scale value words have a scaling words');
                // get any scaling words related to the value
                $scale_wrd_lst = $this->wrd_lst->scaling_lst();
                if (count($scale_wrd_lst->lst) > 1) {
                    log_warning('Only one scale word can be taken into account in the current version, but not a list like ' . $scale_wrd_lst->name() . '.', "value->scale");
                } else {
                    if (count($scale_wrd_lst->lst) == 1) {
                        $scale_wrd = $scale_wrd_lst->lst[0];
                        log_debug('value->scale -> word (' . $scale_wrd->name . ')');
                        if ($scale_wrd->id > 0) {
                            $frm = $scale_wrd->formula();
                            $frm->usr = $this->usr; // temp solution until the bug of not setting is found
                            if (!isset($frm)) {
                                log_warning('No scaling formula defined for "' . $scale_wrd->name . '".', "value->scale");
                            } else {
                                $formula_text = $frm->ref_text;
                                log_debug('value->scale -> scaling formula "' . $frm->name . '" (' . $frm->id . '): ' . $formula_text);
                                if ($formula_text <> "") {
                                    $l_part = zu_str_left_of($formula_text, ZUP_CHAR_CALC);
                                    $r_part = zu_str_right_of($formula_text, ZUP_CHAR_CALC);
                                    $exp = new expression;
                                    $exp->ref_text = $frm->ref_text;
                                    $exp->usr = $this->usr;
                                    $fv_phr_lst = $exp->fv_phr_lst();
                                    $phr_lst = $exp->phr_lst();
                                    if (isset($fv_phr_lst) and isset($phr_lst)) {
                                        $fv_wrd_lst = $fv_phr_lst->wrd_lst_all();
                                        $wrd_lst = $phr_lst->wrd_lst_all();
                                        if (count($fv_wrd_lst->lst) == 1 and count($wrd_lst->lst) == 1) {
                                            $fv_wrd = $fv_wrd_lst->lst[0];
                                            $r_wrd = $wrd_lst->lst[0];

                                            // test if it is a valid scale formula
                                            if ($fv_wrd->is_type(DBL_WORD_TYPE_SCALING_HIDDEN)
                                                and $r_wrd->is_type(DBL_WORD_TYPE_SCALING)) {
                                                $wrd_symbol = ZUP_CHAR_WORD_START . $r_wrd->id . ZUP_CHAR_WORD_END;
                                                log_debug('value->scale -> replace (' . $wrd_symbol . ' in ' . $r_part . ' with ' . $this->number . ')');
                                                $r_part = str_replace($wrd_symbol, $this->number, $r_part);
                                                log_debug('value->scale -> replace done (' . $r_part . ')');
                                                // todo separet time from value words
                                                $result = zuc_math_parse($r_part, $wrd_lst, Null);
                                            } else {
                                                log_err('Formula "' . $formula_text . '" seems to be not a valid scaling formula, because the words are not defined as scaling words.', 'scale');
                                            }
                                        } else {
                                            log_err('Formula "' . $formula_text . '" seems to be not a valid scaling formula, because only one word should be on both sides of the equation.', 'scale');
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // todo: scale the number to the target scaling
            // if no target scaling is defined leave the scaling at one
            //if ($target_wrd_lst->has_scaling()) {
            //}

        }
        return $result;
    }

    // create an object for the export
    function export_obj()
    {
        log_debug('value->export_obj');
        $result = new value();

        // reload the value parameters
        $this->load();
        log_debug('value->export_obj load phrases');
        $this->load_phrases();

        // add the words
        log_debug('value->export_obj get words');
        $wrd_lst = array();
        foreach ($this->wrd_lst->lst as $wrd) {
            $wrd_lst[] = $wrd->name();
        }
        if (count($wrd_lst) > 0) {
            $result->words = $wrd_lst;
        }

        // add the triples
        $triples_lst = array();
        foreach ($this->lnk_lst->lst as $lnk) {
            $triples_lst[] = $lnk->name();
        }
        if (count($triples_lst) > 0) {
            $result->triples = $triples_lst;
        }

        // add the time
        if (isset($this->time_phr)) {
            $phr = new phrase;
            $phr->usr = $this->usr;
            $phr->id = $this->time_id;
            $phr->load();
            $result->time = $phr->name;
            log_debug('value->export_obj got time ' . $this->time_phr->dsp_id());
        }

        // add the value itself
        $result->number = $this->number;

        // add the share type
        log_debug('value->export_obj get share');
        if ($this->share_id > 0 and $this->share_id <> cl(DBL_SHARE_PUBLIC)) {
            $result->share = $this->share_type_code_id();
        }

        // add the protection type
        log_debug('value->export_obj get protection');
        if ($this->protection_id > 0 and $this->protection_id <> cl(DBL_PROTECT_NO)) {
            $result->protection = $this->protection_type_code_id();
        }

        // add the source
        log_debug('value->export_obj get source');
        if ($this->source_id > 0) {
            $result->source = $this->source_name();
        }

        log_debug('value->export_obj -> ' . json_encode($result));
        return $result;
    }

    // import a value from an external object
    function import_obj($json_obj)
    {
        log_debug('value->import_obj');
        $result = '';

        $get_ownership = false;
        foreach ($json_obj as $key => $value) {

            if ($key == 'words') {
                $phr_lst = new phrase_list;
                $phr_lst->usr = $this->usr;
                foreach ($value as $phr_name) {
                    $phr = new phrase;
                    $phr->name = $phr_name;
                    $phr->usr = $this->usr;
                    $phr->load();
                    if ($phr->id == 0) {
                        $wrd = new word;
                        $wrd->name = $phr_name;
                        $wrd->usr = $this->usr;
                        $wrd->load();
                        if ($wrd->id == 0) {
                            $wrd->name = $phr_name;
                            $wrd->type_id = cl(DBL_WORD_TYPE_NORMAL);
                            $wrd->save();
                        }
                        if ($wrd->id == 0) {
                            log_err('Cannot add word "' . $phr_name . '" when importing ' . $this->dsp_id(), 'value->import_obj');
                        } else {
                            $phr_lst->add($wrd);
                        }
                    } else {
                        $phr_lst->add($phr);
                    }
                }
                log_debug('value->import_obj got words ' . $phr_lst->dsp_id());
                $phr_grp = $phr_lst->get_grp();
                log_debug('value->import_obj got word group ' . $phr_grp->dsp_id());
                $this->grp = $phr_grp;
                $this->grp_id = $phr_grp->id;
                $this->phr_lst = $phr_lst;
                log_debug('value->import_obj set grp id to ' . $this->grp_id);
            }

            if ($key == 'timestamp') {
                if (strtotime($value)) {
                    $this->time_stamp = strtotime($value);
                } else {
                    log_err('Cannot add timestamp "' . $value . '" when importing ' . $this->dsp_id(), 'value->import_obj');
                }
            }

            if ($key == 'time') {
                $phr = new phrase;
                $phr->name = $value;
                $phr->usr = $this->usr;
                $phr->load();
                if ($phr->id == 0) {
                    $wrd = new word;
                    $wrd->name = $value;
                    $wrd->usr = $this->usr;
                    $wrd->load();
                    if ($wrd->id == 0) {
                        $wrd->name = $value;
                        $wrd->type_id = cl(DBL_WORD_TYPE_TIME);
                        $wrd->save();
                    }
                    if ($wrd->id == 0) {
                        log_err('Cannot add time word "' . $value . '" when importing ' . $this->dsp_id(), 'value->import_obj');
                    } else {
                        $this->time_phr = $wrd->phrase();
                        $this->time_id = $wrd->id;
                    }
                } else {
                    $this->time_phr = $phr;
                    $this->time_id = $phr->id;
                }
            }

            if ($key == 'number') {
                $this->number = $value;
            }

            if ($key == 'share') {
                $this->share_id = cl($value);
            }

            if ($key == 'protection') {
                $this->protection_id = cl($value);
                if ($value <> DBL_PROTECT_NO) {
                    $get_ownership = true;
                }
            }
        }

        if ($result == '') {
            $this->save();
            log_debug('value->import_obj -> ' . $this->dsp_id());
        } else {
            log_debug('value->import_obj -> ' . $result);
        }

        // try to get the ownership if requested
        if ($get_ownership) {
            $this->take_ownership();
        }

        return $result;
    }

    /*

  display functions

  */

    // create and return the description for this value for debugging
    function dsp_id(): string
    {
        $result = '';

        //$this->load_phrases();

        return $result;
    }

    // create and return the description for this value
    // TODO check if $this->load_phrases() needs to be called before calling this function
    function name()
    {
        $result = '';
        if (isset($this->grp)) {
            $result .= $this->grp->name();
        }
        if (isset($this->time_phr)) {
            if ($result <> '') {
                $result .= ',';
            }
            $result .= $this->time_phr->name;
        }

        return $result;
    }

    // return the html code to display a value
    // this is the opposite of the convert function
    function display($back): string
    {
        $result = '';
        if (!is_null($this->number)) {
            $this->load_phrases();
            $num_text = $this->val_formatted();
            if (!$this->is_std()) {
                $result = '<font class="user_specific">' . $num_text . '</font>';
                //$result = $num_text;
            } else {
                $result = $num_text;
            }
        }
        return $result;
    }

    // html code to show the value with the possibility to click for the result explanation
    function display_linked($back)
    {
        $result = '';

        log_debug('value->display_linked (' . $this->id . ',u' . $this->usr->id . ')');
        if (!is_null($this->number)) {
            $num_text = $this->val_formatted();
            $link_format = '';
            if (isset($this->usr)) {
                if (!$this->is_std()) {
                    $link_format = ' class="user_specific"';
                }
            }
            // to review
            $result .= '<a href="/http/value_edit.php?id=' . $this->id . '&back=' . $back . '"' . $link_format . '>' . $num_text . '</a>';
        }
        log_debug('value->display_linked -> done');
        return $result;
    }

    // offer the user to add a new value similar to this value
    function btn_add($back)
    {
        $result = '';

        $val_btn_title = '';
        $url_phr = '';
        $this->load_phrases();
        if (isset($this->phr_lst)) {
            if (!empty($this->phr_lst->lst)) {
                $val_btn_title = "add new value similar to " . htmlentities($this->phr_lst->name());
            } else {
                $val_btn_title = "add new value";
            }
            $url_phr = $this->phr_lst->id_url_long();
        }

        $val_btn_call = '/http/value_add.php?back=' . $back . $url_phr;
        $result .= btn_add($val_btn_title, $val_btn_call);

        return $result;
    }

    /*

  get functions that returns other linked objects

  */

    // create and return the figure object for the value
    function figure()
    {
        log_debug('value->figure');
        $fig = new figure;
        $fig->id = $this->id;
        $fig->usr = $this->usr;
        $fig->type = 'value';
        $fig->number = $this->number;
        $fig->last_update = $this->last_update;
        $fig->obj = $this;
        log_debug('value->figure -> done');

        return $fig;
    }

    // convert a user entry for a value to a useful database number
    // e.g. remove leading spaces and tabulators
    // if the value contains a single quote "'" the function asks once if to use it as a comma or a thousand operator
    // once the user has given an answer it saves the answer in the database and uses it for the next values
    // if the type of the value differs the user should be asked again
    function convert()
    {
        log_debug('value->convert (' . $this->usr_value . ',u' . $this->usr->id . ')');
        $result = $this->usr_value;
        $result = str_replace(" ", "", $result);
        $result = str_replace("'", "", $result);
        //$result = str_replace(".", "", $result);
        $this->number = $result;
        return $result;
    }

    /*
    display functions
    -------
  */

    // depending on the word list format the numeric value
    // format the value for on screen display
    // similar to the corresponding function in the "formula_value" class
    function val_formatted()
    {
        $result = '';

        $this->load_phrases();

        if (!is_null($this->number)) {
            if (is_null($this->wrd_lst)) {
                $this->load();
            }
            if ($this->wrd_lst->has_percent()) {
                $result = round($this->number * 100, 2) . "%";
            } else {
                if ($this->number >= 1000 or $this->number <= -1000) {
                    $result .= number_format($this->number, 0, $this->usr->dec_point, $this->usr->thousand_sep);
                } else {
                    $result = round($this->number, 2);
                }
            }
        }
        return $result;
    }

    // the same as btn_del_value, but with another icon
    function btn_undo_add_value($back)
    {
        $result = btn_undo('delete this value', '/http/value_del.php?id=' . $this->id . '&back=' . $back . '');
        return $result;
    }

    // display a value, means create the HTML code that allows to edit the value
    function dsp_tbl_std($back)
    {
        log_debug('value->dsp_tbl_std ');
        $result = '';
        $result .= '    <td>' . "\n";
        $result .= '      <div class="right_ref"><a href="/http/value_edit.php?id=' . $this->id . '&back=' . $back . '">' . $this->val_formatted() . '</a></div>' . "\n";
        $result .= '    </td>' . "\n";
        return $result;
    }

    // same as dsp_tbl_std, but in the user specific color
    function dsp_tbl_usr($back)
    {
        log_debug('value->dsp_tbl_usr');
        $result = '';
        $result .= '    <td>' . "\n";
        $result .= '      <div class="right_ref"><a href="/http/value_edit.php?id=' . $this->id . '&back=' . $back . '" class="user_specific">' . $this->val_formatted() . '</a></div>' . "\n";
        $result .= '    </td>' . "\n";
        return $result;
    }

    function dsp_tbl($back)
    {
        log_debug('value->dsp_tbl_std ');
        $result = '';

        if ($this->is_std()) {
            $result .= $this->dsp_tbl_std($back);
        } else {
            $result .= $this->dsp_tbl_usr($back);
        }
        return $result;
    }

    // display the history of a value
    function dsp_hist($page, $size, $call, $back)
    {
        log_debug("value->dsp_hist for id " . $this->id . " page " . $size . ", size " . $size . ", call " . $call . ", back " . $back . ".");
        $result = ''; // reset the html code var

        $log_dsp = new user_log_display;
        $log_dsp->id = $this->id;
        $log_dsp->obj = $this;
        $log_dsp->usr = $this->usr;
        $log_dsp->type = 'value';
        $log_dsp->page = $page;
        $log_dsp->size = $size;
        $log_dsp->call = $call;
        $log_dsp->back = $back;
        $result .= $log_dsp->dsp_hist();

        log_debug("value->dsp_hist -> done");
        return $result;
    }

    // display the history of a value
    function dsp_hist_links($page, $size, $call, $back)
    {
        log_debug("value->dsp_hist_links (" . $this->id . ",size" . $size . ",b" . $size . ")");
        $result = ''; // reset the html code var

        $log_dsp = new user_log_display;
        $log_dsp->id = $this->id;
        $log_dsp->usr = $this->usr;
        $log_dsp->type = 'value';
        $log_dsp->page = $page;
        $log_dsp->size = $size;
        $log_dsp->call = $call;
        $log_dsp->back = $back;
        $result .= $log_dsp->dsp_hist_links();

        log_debug("value->dsp_hist_links -> done");
        return $result;
    }

    // display some value samples related to the wrd_id
    // with a preference of the start_word_ids
    function dsp_samples($wrd_id, $start_wrd_ids, $size, $back)
    {
        log_debug("value->dsp_samples (" . $wrd_id . ",rt" . implode(",", $start_wrd_ids) . ",size" . $size . ")");

        global $db_con;
        $result = ''; // reset the html code var

        // get value changes by the user that are not standard
        $sql = "SELECT v.value_id,
                    " . $db_con->get_usr_field('word_value', 'v', 'u', sql_db::FLD_FORMAT_VAL) . ",
                   t.word_id,
                   t.word_name
              FROM value_phrase_links l,
                   value_phrase_links lt,
                   words t,
                   " . $db_con->get_table_name(DB_TYPE_VALUE) . " v
         LEFT JOIN user_values u ON v.value_id = u.value_id AND u.user_id = " . $this->usr->id . " 
             WHERE l.phrase_id = " . $wrd_id . "
               AND l.value_id = v.value_id
               AND v.value_id = lt.value_id
               AND lt.phrase_id <> " . $wrd_id . "
               AND lt.phrase_id = t.word_id
               AND (u.excluded IS NULL OR u.excluded = 0) 
             LIMIT " . $size . ";";
        //$db_con = New mysql;
        $db_con->usr_id = $this->usr->id;
        $db_lst = $db_con->get($sql);

        // prepare to show where the user uses different value than a normal viewer
        $row_nbr = 0;
        $value_id = 0;
        $word_names = "";
        $result .= dsp_tbl_start_hist();
        foreach ($db_lst as $db_row) {
            // display the headline first if there is at least on entry
            if ($row_nbr == 0) {
                $result .= '<tr>';
                $result .= '<th>samples</th>';
                $result .= '<th>for</th>';
                $result .= '</tr>';
                $row_nbr++;
            }

            $new_value_id = $db_row["value_id"];
            $wrd = new word_dsp;
            $wrd->usr = $this->usr;
            $wrd->id = $db_row["word_id"];
            $wrd->name = $db_row["word_name"];
            if ($value_id <> $new_value_id) {
                if ($word_names <> "") {
                    // display a row if the value has changed and
                    $result .= '<tr>';
                    $result .= '<td><a href="/http/value_edit.php?id=' . $value_id . '&back=' . $back . '" class="grey">' . $row_value . '</a></td>';
                    $result .= '<td>' . $word_names . '</td>';
                    $result .= '</tr>';
                    $row_nbr++;
                }
                // prepare a new value display
                $row_value = $db_row["word_value"];
                $word_names = $wrd->dsp_link_style("grey");
                $value_id = $new_value_id;
            } else {
                $word_names .= ", " . $wrd->dsp_link_style("grey");
            }
        }
        // display the last row if there has been at least one word
        if ($word_names <> "") {
            $result .= '<tr>';
            $result .= '<td><a href="/http/value_edit.php?id=' . $value_id . '&back=' . $back . '" class="grey">' . $row_value . '</a></td>';
            $result .= '<td>' . $word_names . '</td>';
            $result .= '</tr>';
        }
        $result .= dsp_tbl_end();

        log_debug("value->dsp_samples -> done.");
        return $result;
    }

    // simple modal box to add a value
    function dsp_add_fast($back)
    {
        $result = '';

        $result .= '  <h2>Modal Example</h2>';
        $result .= '  <!-- Button to Open the Modal -->';
        //$result .= '  <a href="/http/value_add.php?back=2" title="add"><img src="'.$icon.'" alt="'.$this->title.'"></a>';
        $result .= '';

        return $result;
    }

    // lists all phrases related to a given value except the given phrase
    // and offer to add a formula to the value as an alternative
    // $wrd_add is only optional to display the last added phrase at the end
    // todo: take user unlink of phrases into account
    // save data to the database only if "save" is pressed add and remove the phrase links "on the fly", which means that after the first call the edit view is more or less the same as the add view
    function dsp_edit($type_ids, $back): string
    {
        $result = ''; // reset the html code var

        // set main display parameters for the add or edit view
        if ($this->id <= 0) {
            $script = "value_add";
            $result .= dsp_form_start($script);
            $result .= dsp_text_h3("Add value for");
            log_debug("value->dsp_edit new for phrase ids " . implode(",", $this->ids) . " and user " . $this->usr->id . ".");
        } else {
            $script = "value_edit";
            $result .= dsp_form_start($script);
            $result .= dsp_text_h3("Change value for");
            if (count($this->ids) <= 0) {
                $this->load_phrases();
                log_debug('value->dsp_edit id ' . $this->id . ' with "' . $this->grp->name() . '"@"' . $this->time_phr->name . '"and user ' . $this->usr->id);
            } else {
                $this->load_time_phrase();
                log_debug('value->dsp_edit id ' . $this->id . ' with phrase ids ' . implode(',', $this->ids) . ' and user ' . $this->usr->id);
            }
        }
        $this_url = '/http/' . $script . '.php?id=' . $this->id . '&back=' . $back; // url to call this display again to display the user changes

        // display the words and triples
        $result .= dsp_tbl_start_select();
        if (count($this->ids) > 0) {
            $url_pos = 1; // the phrase position (combined number for fixed, type and free phrases)
            // if the form is confirmed, save the value or the other way round: if with the plus sign only a new phrase is added, do not yet save the value
            $result .= '  <input type="hidden" name="id" value="' . $this->id . '">';
            $result .= '  <input type="hidden" name="confirm" value="1">';

            // reset the phrase sample settings
            $main_wrd = null;
            log_debug("value->dsp_edit main wrd");

            // rebuild the value ids if needed
            // 1. load the phrases parameters based on the ids
            $result .= $this->set_phr_lst_by_ids();
            // 2. extract the time from the phrase list
            $result .= $this->set_time_by_phr_lst();
            log_debug("value->dsp_edit phrase list incl. time " . $this->phr_lst->name());
            $result .= $this->set_phr_lst_ex_time();
            log_debug("value->dsp_edit phrase list excl. time " . $this->phr_lst->name());
            $phr_lst = $this->phr_lst;

            /*
      // load the phrase list
      $phr_lst = New phrase_list;
      $phr_lst->ids = $this->ids;
      $phr_lst->usr = $this->usr;
      $phr_lst->load();

      // separate the time if needed
      if ($this->time_id <= 0) {
        $this->time_phr = $phr_lst->time_useful();
        $phr_lst->del($this->time_phr);
        $this->time_id = $this->time_phr->id; // not really needed ...
      }
      */

            // assign the type to the phrases
            foreach ($phr_lst->lst as $phr) {
                $phr->usr = $this->usr;
                foreach (array_keys($this->ids) as $pos) {
                    if ($phr->id == $this->ids[$pos]) {
                        $phr->is_wrd_id = $type_ids[$pos];
                        $is_wrd = new word_dsp;
                        $is_wrd->id = $phr->is_wrd_id;
                        $is_wrd->usr = $this->usr;
                        $phr->is_wrd = $is_wrd;
                        $phr->dsp_pos = $pos;
                    }
                }
                // guess the missing phrase types
                if ($phr->is_wrd_id == 0) {
                    log_debug('value->dsp_edit -> guess type for "' . $phr->name . '"');
                    $phr->is_wrd = $phr->is_mainly();
                    if ($phr->is_wrd->id > 0) {
                        $phr->is_wrd_id = $phr->is_wrd->id;
                        log_debug('value->dsp_edit -> guessed type for ' . $phr->name . ': ' . $phr->is_wrd->name);
                    }
                }
            }

            // show first the phrases, that are not supposed to be changed
            //foreach (array_keys($this->ids) AS $pos) {
            log_debug('value->dsp_edit -> show fixed phrases');
            foreach ($phr_lst->lst as $phr) {
                //if ($type_ids[$pos] < 0) {
                if ($phr->is_wrd_id < 0) {
                    log_debug('value->dsp_edit -> show fixed phrase "' . $phr->name . '"');
                    // allow the user to change also the fixed phrases
                    $type_ids_adj = $type_ids;
                    $type_ids_adj[$phr->dsp_pos] = 0;
                    $used_url = $this_url . zu_ids_to_url($this->ids, "phrase") .
                        zu_ids_to_url($type_ids_adj, "type");
                    $result .= $phr->dsp_name_del($used_url);
                    $result .= '  <input type="hidden" name="phrase' . $url_pos . '" value="' . $phr->id . '">';
                    $url_pos++;
                }
            }

            // show the phrases that the user can change: first the non specific ones, that the phrases of a selective type and new phrases at the end
            log_debug('value->dsp_edit -> show phrases');
            for ($dsp_type = 0; $dsp_type <= 1; $dsp_type++) {
                foreach ($phr_lst->lst as $phr) {
                    /*
          // build a list of suggested phrases
          $phr_lst_sel_old = array();
          if ($phr->is_wrd_id > 0) {
            // prepare the selector for the type phrase
            $phr->is_wrd->usr = $this->usr;
            $phr_lst_sel = $phr->is_wrd->children();
            zu_debug("value->dsp_edit -> suggested phrases for ".$phr->name.": ".$phr_lst_sel->name().".");
          } else {
            // if no phrase group is found, use the phrase type time if the phrase is a time phrase
            if ($phr->is_time()) {
              $phr_lst_sel = New phrase_list;
              $phr_lst_sel->usr = $this->usr;
              $phr_lst_sel->phrase_type_id = cl(SQL_WORD_TYPE_TIME);
              $phr_lst_sel->load();
            }
          } */

                    // build the url for the case that this phrase should be removed
                    log_debug('value->dsp_edit -> build url');
                    $phr_ids_adj = $this->ids;
                    $type_ids_adj = $type_ids;
                    array_splice($phr_ids_adj, $phr->dsp_pos, 1);
                    array_splice($type_ids_adj, $phr->dsp_pos, 1);
                    $used_url = $this_url . zu_ids_to_url($phr_ids_adj, "phrase") .
                        zu_ids_to_url($type_ids_adj, "type") .
                        '&confirm=1';
                    // url for the case that this phrase should be renamed
                    if ($phr->id > 0) {
                        $phrase_url = '/http/word_edit.php?id=' . $phr->id . '&back=' . $back;
                    } else {
                        $lnk_id = $phr->id * -1;
                        $phrase_url = '/http/link_edit.php?id=' . $lnk_id . '&back=' . $back;
                    }

                    // show the phrase selector
                    $result .= '  <tr>';

                    // show the phrases that have a type
                    if ($dsp_type == 0) {
                        if ($phr->is_wrd->id > 0) {
                            log_debug('value->dsp_edit -> id ' . $phr->id . ' has a type');
                            $result .= '    <td>';
                            $result .= $phr->is_wrd->name . ':';
                            $result .= '    </td>';
                            //$result .= '    <input type="hidden" name="db'.$url_pos.'" value="'.$phr->dsp_lnk_id.'">';
                            $result .= '    <td>';
                            /*if (!empty($phr_lst_sel->lst)) {
                $result .= '      '.$phr_lst_sel->dsp_selector("phrase".$url_pos, $script, $phr->id);
              } else {  */
                            $result .= '      ' . $phr->dsp_selector($phr->is_wrd, $script, $url_pos, '', $back);
                            //}
                            $url_pos++;

                            $result .= '    </td>';
                            $result .= '    <td>' . btn_del("Remove " . $phr->name, $used_url) . '</td>';
                            $result .= '    <td>' . btn_edit("Rename " . $phr->name, $phrase_url) . '</td>';
                        }
                    }

                    // show the phrases that don't have a type
                    if ($dsp_type == 1) {
                        if ($phr->is_wrd->id == 0 and $phr->id > 0) {
                            log_debug('value->dsp_edit -> id ' . $phr->id . ' has no type');
                            if (!isset($main_wrd)) {
                                $main_wrd = $phr;
                            }
                            //$result .= '    <input type="hidden" name="db'.$url_pos.'" value="'.$phr->dsp_lnk_id.'">';
                            $result .= '    <td colspan="2">';
                            $result .= '      ' . $phr->dsp_selector(0, $script, $url_pos, '', $back);
                            $url_pos++;

                            $result .= '    </td>';
                            $result .= '    <td>' . btn_del("Remove " . $phr->name, $used_url) . '</td>';
                            $result .= '    <td>' . btn_edit("Rename " . $phr->name, $phrase_url) . '</td>';
                        }
                    }


                    $result .= '  </tr>';
                }
            }

            // show the time word
            log_debug('value->dsp_edit -> show time');
            if ($this->time_id > 0) {
                if (isset($this->time_phr)) {
                    $result .= '  <tr>';
                    if ($this->time_phr->id == 0) {
                        $result .= '    <td colspan="2">';

                        log_debug('value->dsp_edit -> show time selector');
                        $result .= $this->time_phr->dsp_time_selector(0, $script, $url_pos, $back);
                        $url_pos++;

                        $result .= '    </td>';
                        $result .= '    <td>' . btn_del("Remove " . $this->time_phr->name, $used_url) . '</td>';
                    }
                    $result .= '  </tr>';
                }
            }

            // show the new phrases
            log_debug('value->dsp_edit -> show new phrases');
            foreach ($this->ids as $phr_id) {
                $result .= '  <tr>';
                if ($phr_id == 0) {
                    $result .= '    <td colspan="2">';

                    $phr_new = new phrase;
                    $phr_new->usr = $this->usr;
                    $result .= $phr_new->dsp_selector(0, $script, $url_pos, '', $back);
                    $url_pos++;

                    $result .= '    </td>';
                    $result .= '    <td>' . btn_del("Remove new", $used_url) . '</td>';
                }
                $result .= '  </tr>';
            }
        }

        $result .= dsp_tbl_end();

        log_debug('value->dsp_edit -> table ended');
        $phr_ids_new = $this->ids;
        //$phr_ids_new[]  = $new_phrase_default;
        $phr_ids_new[] = 0;
        $type_ids_new = $type_ids;
        $type_ids_new[] = 0;
        $used_url = $this_url . zu_ids_to_url($phr_ids_new, "phrase") .
            zu_ids_to_url($type_ids_new, "type");
        $result .= '  ' . btn_add("Add another phrase", $used_url);
        $result .= '  <br><br>';
        $result .= '  <input type="hidden" name="back" value="' . $back . '">';
        if ($this->id > 0) {
            $result .= '  to <input type="text" name="value" value="' . $this->number . '">';
        } else {
            $result .= '  is <input type="text" name="value">';
        }
        $result .= dsp_form_end("Save", $back);
        $result .= '<br><br>';
        log_debug('value->dsp_edit -> load source');
        $src = $this->load_source();
        if (isset($src)) {
            $result .= $src->dsp_select($script, $back);
            $result .= '<br><br>';
        }

        // display the share type
        $result .= $this->dsp_share($script, $back);

        // display the protection type
        $result .= $this->dsp_protection($script, $back);

        $result .= '<br>';
        $result .= btn_back($back);

        // display the user changes
        log_debug('value->dsp_edit -> user changes');
        if ($this->id > 0) {
            $changes = $this->dsp_hist(0, SQL_ROW_LIMIT, '', $back);
            if (trim($changes) <> "") {
                $result .= dsp_text_h3("Latest changes related to this value", "change_hist");
                $result .= $changes;
            }
            $changes = $this->dsp_hist_links(0, SQL_ROW_LIMIT, '', $back);
            if (trim($changes) <> "") {
                $result .= dsp_text_h3("Latest link changes related to this value", "change_hist");
                $result .= $changes;
            }
        } else {
            // display similar values as a sample for the user to force a consistent type of entry e.g. cost should always be a negative number
            if (isset($main_wrd)) {
                $main_wrd->load();
                $samples = $this->dsp_samples($main_wrd->id, $this->ids, 10, $back);
                log_debug("value->dsp_edit samples.");
                if (trim($samples) <> "") {
                    $result .= dsp_text_h3('Please have a look at these other "' . $main_wrd->dsp_link_style("grey") . '" values as an indication', 'change_hist');
                    $result .= $samples;
                }
            }
        }

        log_debug("value->dsp_edit -> done");
        return $result;
    }


    /*

    Select functions

  */

    // get a list of all formula results that are depending on this value
    // todo: add a loop over the calculation if the are more formula results needs to be updated than defined with SQL_ROW_MAX
    function fv_lst_depending()
    {
        log_debug('value->fv_lst_depending group id "' . $this->grp_id . '" for user ' . $this->usr->name . '');
        $fv_lst = new formula_value_list;
        $fv_lst->usr = $this->usr;
        $fv_lst->grp_id = $this->grp_id;
        $fv_lst->load(SQL_ROW_MAX);

        log_debug('value->fv_lst_depending -> done');
        return $fv_lst;
    }


    /*

    Save functions

    changer      - true if another user is using this record (value in this case)
    can_change   - true if the actual user is allowed to change the record
    log_add      - set the log object for adding a new record
    log_upd      - set the log object for changing this record
    log_del      - set the log object for excluding this record
    need_usr_cfg - true if at least one field differs between the standard record and the user specific record
    has_usr_cfg  - true if a record for user specific setting exists
    add_usr_cfg  - to created a record for user specific settings
    del_usr_cfg  - to delete the record for user specific settings, because it is not needed any more

    Default steps to save a value
    1. if the id is not set
    2. get the word and triple ids
    3. get or create a word group for the word and triple combination
    4. get the time (period) or time stamp
    5. check if a value for the word group and time already exist
    6. if not, create the value
    7.

    cases for user
    1) user a creates a value -> he can change it
    2) user b changes the value -> the change is saved only for this user
    3a) user a changes the original value -> the change is save in the original record -> user a is still the owner
    3b) user a changes the original value to the same value as b -> the user specific record is removed -> user a is still the owner
    3c) user b changes the value -> the user specific record is updated
    3d) user b changes the value to the same value as a -> the user specific record is removed
    3e) user a excludes the value -> b gets the owner and a user specific exclusion for a is created

  */

    // true if no one has used this value
    function not_used(): bool
    {
        log_debug('value->not_used (' . $this->id . ')');
        $result = true;

        // to review: maybe replace by a database foreign key check
        return $this->not_changed();
    }

    // true if no other user has modified the value
    function not_changed(): bool
    {
        log_debug('value->not_changed id ' . $this->id . ' by someone else than the owner (' . $this->owner_id . ')');

        global $db_con;
        $result = true;

        $change_user_id = 0;
        if ($this->owner_id > 0) {
            $sql = "SELECT user_id 
                FROM user_values 
               WHERE value_id = " . $this->id . "
                 AND user_id <> " . $this->owner_id . "
                 AND (excluded <> 1 OR excluded is NULL)";
        } else {
            $sql = "SELECT user_id 
                FROM user_values 
               WHERE value_id = " . $this->id . "
                 AND (excluded <> 1 OR excluded is NULL)";
        }
        //$db_con = new mysql;
        $db_con->usr_id = $this->usr->id;
        $db_row = $db_con->get1($sql);
        $change_user_id = $db_row['user_id'];
        if ($change_user_id > 0) {
            $result = false;
        }
        log_debug('value->not_changed for ' . $this->id . ' is ' . zu_dsp_bool($result));
        return $result;
    }

    // search for the median (not average) value
    function get_std()
    {
    }

    // this value object is defined as the standard value
    function set_std()
    {
        // if a user has been using the standard value until now, just create a message, that the standard value has been changes and offer him to use the old standard value also in the future
        // delete all user values that are matching the new standard
        // save the new standard value in the database
    }

    // true if the loaded value is not user specific
    // todo: check the difference between is_std and can_change
    function is_std(): bool
    {
        $result = false;
        if ($this->owner_id == $this->usr->id or $this->owner_id <= 0) {
            $result = true;
        }

        log_debug('value->is_std -> (' . zu_dsp_bool($result) . ')');
        return $result;
    }

    // true if the user is the owner and no one else has changed the value
    function can_change(): bool
    {
        log_debug('value->can_change id ' . $this->id . ' by user ' . $this->usr->name);
        $can_change = false;
        log_debug('value->can_change id ' . $this->id . ' owner ' . $this->owner_id . ' = ' . $this->usr->id . '?');
        if ($this->owner_id == $this->usr->id or $this->owner_id <= 0) {
            $can_change = true;
        }

        log_debug('value->can_change -> (' . zu_dsp_bool($can_change) . ')');
        return $can_change;
    }

    // true if a record for a user specific configuration already exists in the database
    function has_usr_cfg(): bool
    {
        $has_cfg = false;
        if ($this->usr_cfg_id > 0) {
            $has_cfg = true;
        }
        return $has_cfg;
    }

    // create a database record to save a user specific value
    function add_usr_cfg(): bool
    {
        global $db_con;
        $result = true;

        if (!$this->has_usr_cfg()) {
            log_debug('value->add_usr_cfg for "' . $this->id . ' und user ' . $this->usr->name);

            // check again if there ist not yet a record
            $sql = 'SELECT user_id 
                FROM user_values
               WHERE value_id = ' . $this->id . ' 
                 AND user_id = ' . $this->usr->id . ';';
            //$db_con = New mysql;
            $db_con->usr_id = $this->usr->id;
            $db_row = $db_con->get1($sql);
            if ($db_row != null) {
                $this->usr_cfg_id = $db_row['user_id'];
            }
            if (!$this->has_usr_cfg()) {
                // create an entry in the user sandbox
                $db_con->set_type(DB_TYPE_USER_PREFIX . DB_TYPE_VALUE);
                $log_id = $db_con->insert(array('value_id', 'user_id'), array($this->id, $this->usr->id));
                if ($log_id <= 0) {
                    log_err('Insert of user_value failed.');
                    $result = false;
                } else {
                    $result = true;
                }
            }
        }
        return $result;
    }

    // check if the database record for the user specific settings can be removed
    // exposed at the moment to user_display.php for consistency check, but this should not be needed
    function del_usr_cfg_if_not_needed(): bool
    {
        log_debug('value->del_usr_cfg_if_not_needed pre check for "' . $this->id . ' und user ' . $this->usr->name);

        global $db_con;
        $result = false;

        // check again if the user config is still needed (don't use $this->has_usr_cfg to include all updated)
        $sql = "SELECT value_id,
                   word_value,
                   source_id,
                   excluded
              FROM user_values
             WHERE value_id = " . $this->id . " 
               AND user_id = " . $this->usr->id . ";";
        //$db_con = New mysql;
        $db_con->usr_id = $this->usr->id;
        $usr_cfg = $db_con->get1($sql);
        log_debug('value->del_usr_cfg_if_not_needed check for "' . $this->id . ' und user ' . $this->usr->name . ' with (' . $sql . ')');
        if ($usr_cfg['value_id'] > 0) {
            if ($usr_cfg['word_value'] == Null
                and $usr_cfg['source_id'] == Null
                and $usr_cfg['excluded'] == Null) {
                // delete the entry in the user sandbox
                log_debug('value->del_usr_cfg_if_not_needed any more for "' . $this->id . ' und user ' . $this->usr->name);
                $result = $this->del_usr_cfg_exe($db_con);
            }
        }

        return $result;
    }

    // set the log entry parameters for a value update
    function log_upd()
    {
        log_debug('value->log_upd "' . $this->number . '" for user ' . $this->usr->id);
        $log = new user_log;
        $log->usr = $this->usr;
        $log->action = 'update';
        if ($this->can_change()) {
            $log->table = 'values';
        } else {
            $log->table = 'user_values';
        }

        return $log;
    }

    /*
  // set the log entry parameter to delete a value
  function log_del($db_type) {
    zu_debug('value->log_del "'.$this->id.'" for user '.$this->usr->name);
    $log = New user_log;
    $log->usr       = $this->usr;
    $log->action    = 'del';
    $log->table     = $db_type;
    $log->field     = 'word_value';
    $log->old_value = $this->number;
    $log->new_value = '';
    $log->row_id    = $this->id;
    $log->add();

    return $log;
  }
  */

    // update the phrase links to the value based on the group and time for faster searching
    // e.g. if the value "46'000" is linked to the group "2116 (ABB, SALES, CHF, MIO)" it is checked that lines to all phrases to the value are in the database
    //      to be able to search the value by a single phrase
    // to do: make it user specific!
    function upd_phr_links(): bool
    {
        log_debug('value->upd_phr_links');

        global $db_con;
        $result = true;

        // create the db link object for all actions
        //$db_con = New mysql;
        $db_con->usr_id = $this->usr->id;

        $table_name = 'value_phrase_links';
        $field_name = 'phrase_id';

        // read all existing phrase to value links
        $sql = 'SELECT ' . $field_name . '
              FROM ' . $table_name . '
             WHERE value_id = ' . $this->id . ';';
        $grp_lnk_rows = $db_con->get($sql);
        $db_ids = array();
        if ($grp_lnk_rows != null) {
            foreach ($grp_lnk_rows as $grp_lnk_row) {
                $db_ids[] = $grp_lnk_row[$field_name];
            }
        }

        log_debug('value->upd_phr_links -> links found in database ' . implode(",", $db_ids));

        // add the time phrase to the target link list
        if (!isset($this->phr_lst)) {
            $this->load_phrases();
        }
        if (!isset($this->phr_lst)) {
            // zu_err('Cannot load phrases for group "'.$this->phr_grp_id.'".', "value->upd_phr_links");
        } else {
            $phr_ids_used = $this->phr_lst->ids();
            if ($this->time_id <> 0) {
                if (!in_array($this->time_id, $phr_ids_used)) {
                    $phr_ids_used[] = $this->time_id;
                }
            }
        }
        log_debug('value->upd_phr_links -> phrases loaded based on value ' . implode(",", $phr_ids_used));

        // get what needs added or removed
        log_debug('value->upd_phr_links -> should have phrase ids ' . implode(",", $phr_ids_used));
        $add_ids = array_diff($phr_ids_used, $db_ids);
        $del_ids = array_diff($db_ids, $phr_ids_used);
        log_debug('value->upd_phr_links -> add ids ' . implode(",", $add_ids));
        log_debug('value->upd_phr_links -> del ids ' . implode(",", $del_ids));

        // add the missing links
        if (count($add_ids) > 0) {
            $add_nbr = 0;
            $sql = '';
            foreach ($add_ids as $add_id) {
                if ($add_id <> '') {
                    if ($sql == '') {
                        $sql = 'INSERT INTO ' . $table_name . ' (value_id, ' . $field_name . ') VALUES ';
                    }
                    $sql .= " (" . $this->id . "," . $add_id . ") ";
                    $add_nbr++;
                    if ($add_nbr < count($add_ids)) {
                        $sql .= ",";
                    } else {
                        $sql .= ";";
                    }
                }
            }
            log_debug('value->upd_phr_links -> add sql');
            if ($sql <> '') {
                //$sql_result = $db_con->exe($sql, "value->upd_phr_links", array());
                $sql_result = $db_con->exe($sql);
                if ($sql_result === False) {
                    log_err('Error adding new group links "' . implode(',', $add_ids) . '" for ' . $this->id);
                    $result = false;
                }
            }
        }
        log_debug('value->upd_phr_links -> added links "' . implode(',', $add_ids) . '" lead to ' . implode(",", $db_ids));

        // remove the links not needed any more
        if (count($del_ids) > 0) {
            log_debug('value->upd_phr_links -> del ' . implode(",", $del_ids) . '');
            $del_nbr = 0;
            $sql = 'DELETE FROM ' . $table_name . ' 
               WHERE value_id = ' . $this->id . '
                 AND ' . $field_name . ' IN (' . implode(',', $del_ids) . ');';
            //$sql_result = $db_con->exe($sql, "value->upd_phr_links_delete", array());
            $sql_result = $db_con->exe($sql);
            if ($sql_result === False) {
                og_err('Error removing group links "' . implode(',', $del_ids) . '" from ' . $this->id);
                $result = false;
            }
        }
        log_debug('value->upd_phr_links -> deleted links "' . implode(',', $del_ids) . '" lead to ' . implode(",", $db_ids));

        log_debug('value->upd_phr_links -> done');
        return $result;
    }

    /*
  // set the parameter for the log entry to link a word to value
  function log_add_link($wrd_id) {
    zu_debug('value->log_add_link word "'.$wrd_id.'" to value '.$this->id);
    $log = New user_log_link;
    $log->usr       = $this->usr;
    $log->action    = 'add';
    $log->table     = 'value_phrase_links';
    $log->new_from  = $this->id;
    $log->new_to    = $wrd_id;
    $log->row_id    = $this->id;
    $log->link_text = 'word';
    $log->add_link_ref();

    return $log;
  }

  // set the parameter for the log entry to unlink a word to value
  function log_del_link($wrd_id) {
    zu_debug('value->log_del_link word "'.$wrd_id.'" from value '.$this->id);
    $log = New user_log_link;
    $log->usr       = $this->usr;
    $log->action    = 'del';
    $log->table     = 'value_phrase_links';
    $log->old_from  = $this->id;
    $log->old_to    = $wrd_id;
    $log->row_id    = $this->id;
    $log->link_text = 'word';
    $log->add_link_ref();

    return $log;
  }

  // link an additional phrase the value
  function add_wrd($phr_id) {
    zu_debug("value->add_wrd add ".$phr_id." to ".$this->name().",t for user ".$this->usr->name.".");
    $result = false;

    if ($this->can_change()) {
      // log the insert attempt first
      $log = $this->log_add_link($phr_id);
      if ($log->id > 0) {
        // insert the link
        $db_con = new mysql;
        $db_con->usr_id = $this->usr->id;
        $db_con->set_type(DB_TYPE_VALUE_PHRASE_LINK);
        $val_wrd_id = $db_con->insert(array("value_id","phrase_id"), array($this->id,$phr_id));
        if ($val_wrd_id > 0) {
          // get the link id, but updating the reference in the log should not be done, because the row id should be the ref to the original value
          // todo: call the word group creation
        }
      }
    } else {
      // add the link only for this user
    }
    return $result;
  }

  // unlink a phrase from the value
  function del_wrd($wrd) {
    zu_debug('value->del_wrd from id '.$this->id.' the phrase "'.$wrd->name.'" by user '.$this->usr->name);
    $result = '';

    if ($this->can_change()) {
      // log the delete attempt first
      $log = $this->log_del_link($wrd->id);
      if ($log->id > 0) {
        // remove the link
        $db_con = new mysql;
        $db_con->usr_id = $this->usr->id;
        $db_con->set_type(DB_TYPE_VALUE_PHRASE_LINK);
        $result = $db_con->delete(array("value_id","phrase_id"), array($this->id,$wrd->id));
        //$result = str_replace ('1','',$result);
      }
    } else {
      // add the link only for this user
    }
    return $result;
  }
  */

    // update the time stamp to trigger an update of the depending results
    function save_field_trigger_update($db_con): bool
    {
        $result = false;

        $this->last_update = new DateTime();
        $db_con->set_type(DB_TYPE_VALUE);
        $result .= $db_con->update($this->id, 'last_update', 'Now()');
        log_debug('value->save_field_trigger_update timestamp of ' . $this->id . ' updated to "' . $this->last_update->format('Y-m-d H:i:s') . '"');

        // trigger the batch job
        // save the pending update to the database for the batch calculation
        log_debug('value->save_field_trigger_update group id "' . $this->grp_id . '" for user ' . $this->usr->name . '');
        if ($this->id > 0) {
            $job = new batch_job;
            $job->type = cl(DBL_JOB_VALUE_UPDATE);
            //$job->usr  = $this->usr;
            $job->obj = $this;
            $job->add();
            $result = true;
        }
        log_debug('value->save_field_trigger_update -> done');
        return $result;
    }

    // set the update parameters for the number
    function save_field_number($db_con, $db_rec, $std_rec): bool
    {
        $result = true;
        if ($db_rec->number <> $this->number) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->number;
            $log->new_value = $this->number;
            $log->std_value = $std_rec->number;
            $log->row_id = $this->id;
            $log->field = 'word_value';
            $result .= $this->save_field_do($db_con, $log);
            // updating the number is definitely relevant for calculation, so force to update the timestamp
            log_debug('value->save_field_number -> trigger update');
            $result = $this->save_field_trigger_update($db_con);
        }
        return $result;
    }

    // set the update parameters for the source link
    function save_field_source($db_con, $db_rec, $std_rec): bool
    {
        $result = true;
        if ($db_rec->source_id <> $this->source_id) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->source_name();
            $log->old_id = $db_rec->source_id;
            $log->new_value = $this->source_name();
            $log->new_id = $this->source_id;
            $log->std_value = $std_rec->source_name();
            $log->std_id = $std_rec->source_id;
            $log->row_id = $this->id;
            $log->field = 'source_id';
            $result = $this->save_field_do($db_con, $log);
        }
        return $result;
    }

    // save the value number and the source
    function save_fields($db_con, $db_rec, $std_rec): bool
    {
        $result = $this->save_field_number($db_con, $db_rec, $std_rec);
        if ($result) {
            $result = $this->save_field_source($db_con, $db_rec, $std_rec);
        }
        if ($result) {
            $result = $this->save_field_share($db_con, $db_rec, $std_rec);
        }
        if ($result) {
            $result = $this->save_field_protection($db_con, $db_rec, $std_rec);
        }
        if ($result) {
            $result = $this->save_field_excluded($db_con, $db_rec, $std_rec);
        }
        log_debug('value->save_fields all fields for "' . $this->id . '" has been saved');
        return $result;
    }

    // updated the view component name (which is the id field)
    // should only be called if the user is the owner and nobody has used the display component link
    function save_id_fields($db_con, $db_rec, $std_rec): bool
    {
        log_debug('value->save_id_fields');
        $result = true;

        // to load any missing objects
        $db_rec->load_phrases();
        $this->load_phrases();
        $std_rec->load_phrases();

        if ($db_rec->grp_id <> $this->grp_id) {
            log_debug('value->save_id_fields to ' . $this->dsp_id() . ' from "' . $db_rec->dsp_id() . '" (standard ' . $std_rec->dsp_id() . ')');

            $log = $this->log_upd();
            if (isset($db_rec->grp)) {
                $log->old_value = $db_rec->grp->name();
            }
            if (isset($this->grp)) {
                $log->new_value = $this->grp->name();
            }
            if (isset($std_rec->grp)) {
                $log->std_value = $std_rec->grp->name();
            }
            $log->old_id = $db_rec->grp_id;
            $log->new_id = $this->grp_id;
            $log->std_id = $std_rec->grp_id;
            $log->row_id = $this->id;
            $log->field = 'phrase_group_id';
            if ($log->add()) {
                $db_con->set_type(DB_TYPE_VALUE);
                $result = $db_con->update($this->id,
                    array("phrase_group_id"),
                    array($this->grp_id));
            }
        }
        log_debug('value->save_id_fields group updated for ' . $this->dsp_id());

        if ($result) {
            if ($db_rec->time_id <> $this->time_id) {
                log_debug('value->save_id_fields to ' . $this->dsp_id() . ' from "' . $db_rec->dsp_id() . '" (standard ' . $std_rec->dsp_id() . ')');
                $log = $this->log_upd();
                $log->old_value = $db_rec->time_phr->name();
                $log->old_id = $db_rec->time_id;
                $log->new_value = $this->time_phr->name();
                $log->new_id = $this->time_id;
                $log->std_value = $std_rec->time_phr->name();
                $log->std_id = $std_rec->time_id;
                $log->row_id = $this->id;
                $log->field = 'time_word_id';
                if ($log->add()) {
                    $db_con->set_type(DB_TYPE_VALUE);
                    $result .= $db_con->update($this->id, array("time_word_id"),
                        array($this->time_id));
                }
            }
            log_debug('value->save_id_fields time updated for ' . $this->dsp_id());

            // update the phrase links for fast searching
            $result = $this->upd_phr_links();
        }

        // not yet active
        /*
        if ($db_rec->time_stamp <> $this->time_stamp) {
          zu_debug('value->save_id_fields to '.$this->dsp_id().' from "'.$db_rec->dsp_id().'" (standard '.$std_rec->dsp_id().')');
          $log = $this->log_upd();
          $log->old_value = $db_rec->time_stamp;
          $log->new_value = $this->time_stamp;
          $log->std_value = $std_rec->time_stamp;
          $log->row_id    = $this->id;
          $log->field     = 'time_stamp';
          if ($log->add()) {
            $result .= $db_con->update($this->id, array("time_word_id"),
                                                  array($this->time_stamp));
          }
        }
        */
        log_debug('value->save_id_fields time updated for ' . $this->dsp_id());

        return $result;
    }

    // check if the id parameters are supposed to be changed
    function save_id_if_updated($db_con, $db_rec, $std_rec): string
    {
        log_debug('value->save_id_if_updated has name changed from "' . $db_rec->dsp_id() . '" to "' . $this->dsp_id() . '"');
        $result = '';

        // if the phrases or time has changed, check if value with the same phrases/time already exists
        if ($db_rec->grp_id <> $this->grp_id or $db_rec->time_id <> $this->time_id or $db_rec->time_stamp <> $this->time_stamp) {
            // check if a value with the same phrases/time is already in the database
            $chk_val = new value;
            $chk_val->grp_id = $this->grp_id;
            $chk_val->time_id = $this->time_id;
            $chk_val->time_stamp = $this->time_stamp;
            $chk_val->usr = $this->usr;
            $chk_val->load();
            log_debug('value->save_id_if_updated check value loaded');
            if ($chk_val->id > 0) {
                // if the target value is already in the database combine the user changes with this values
                $this->id = $chk_val->id;
                $result .= $this->save();
                log_debug('value->save_id_if_updated update the existing ' . $chk_val->dsp_id());
            } else {

                log_debug('value->save_id_if_updated target value name does not yet exists for ' . $this->dsp_id());
                if ($this->can_change() and $this->not_used()) {
                    // in this case change is allowed and done
                    log_debug('value->save_id_if_updated change the existing display component link ' . $this->dsp_id() . ' (db "' . $db_rec->dsp_id() . '", standard "' . $std_rec->dsp_id() . '")');
                    //$this->load_objects();
                    if (!$this->save_field_excluded($db_con, $db_rec, $std_rec)) {
                        log_err('Excluding value has failed');
                    }
                } else {
                    // if the target link has not yet been created
                    // ... request to delete the old
                    $to_del = clone $db_rec;
                    $result .= $to_del->del();
                    // .. and create a deletion request for all users ???

                    // ... and create a new display component link
                    $this->id = 0;
                    $this->owner_id = $this->usr->id;
                    $result .= $this->add($db_con);
                    log_debug('value->save_id_if_updated recreate the value "' . $db_rec->dsp_id() . '" as ' . $this->dsp_id() . ' (standard "' . $std_rec->dsp_id() . '")');
                }
            }
        } else {
            log_debug('value->save_id_if_updated no id field updated (group ' . $db_rec->grp_id . '=' . $this->grp_id . ', time ' . $db_rec->time_id . '=' . $this->time_id . ')');
        }

        log_debug('value->save_id_if_updated for ' . $this->dsp_id() . ' has been done');
        return $result;
    }

    // create a new value
    function add(): int
    {
        log_debug('value->add the value ' . $this->dsp_id());

        global $db_con;
        $result = 0;

        // log the insert attempt first
        $log = $this->log_add();
        if ($log->id > 0) {
            // insert the value
            if ($this->is_time_series()) {
                $db_con->set_type(DB_TYPE_VALUE_TIME_SERIES);
                $this->id = $db_con->insert(
                    array("phrase_group_id", "user_id", "last_update"),
                    array($this->grp_id, $this->usr->id, "Now()"));
                if ($this->id > 0) {
                    // update the reference in the log
                    if ($log->add_ref($this->id)) {
                        $result = $this->id;
                    }

                    // update the phrase links for fast searching
                    if (!$this->upd_phr_links()) {
                        $result = 0;
                    }

                    // create an empty db_rec element to force saving of all set fields
                    $db_val = new value;
                    $db_val->id = $this->id;
                    $db_val->usr = $this->usr;
                    $db_val->number = $this->number; // ... but not the field saved already with the insert
                    $std_val = clone $db_val;
                    // save the value fields
                    //$result .= $this->save_fields($db_con, $db_val, $std_val);
                    // save the value
                    $db_con_ts = clone $db_con;
                    // TODO type is missing!!!
                    //$db_con_ts->type = 'value_t';
                    $db_con_ts->insert(array("value_time_series_id", "val_time", "number"),
                        array($this->id, $this->time_stamp, $this->number));
                }
            } else {
                $db_con->set_type(DB_TYPE_VALUE);
                $this->id = $db_con->insert(array("phrase_group_id", "time_word_id", "user_id", "word_value", "last_update"),
                    array($this->grp_id, $this->time_id, $this->usr->id, $this->number, "Now()"));
                if ($this->id > 0) {
                    // update the reference in the log
                    if ($log->add_ref($this->id)) {
                        $result = $this->id;
                    }

                    // update the phrase links for fast searching
                    if (!$this->upd_phr_links()) {
                        $result = 0;
                    }

                    if ($this->id > 0) {
                        // create an empty db_rec element to force saving of all set fields
                        $db_val = new value;
                        $db_val->id = $this->id;
                        $db_val->usr = $this->usr;
                        $db_val->number = $this->number; // ... but not the field saved already with the insert
                        $std_val = clone $db_val;
                        // save the value fields
                        $result .= $this->save_fields($db_con, $db_val, $std_val);
                    }

                } else {
                    log_err("Adding value " . $this->id . " failed.", "value->save");
                }
            }
        }

        return $result;
    }

    // insert or update a number in the database or save a user specific number
    function save(): string
    {
        log_debug('value->save "' . $this->number . '" for user ' . $this->usr->name);

        global $db_con;
        $result = '';

        // build the database object because the is anyway needed
        if ($this->is_time_series()) {
            $db_con->set_type(DB_TYPE_VALUE_TIME_SERIES);
        } else {
            $db_con->set_type(DB_TYPE_VALUE);
        }
        $db_con->set_usr($this->usr->id);

        // rebuild the value ids if needed e.g. if the front end function has just set a list of phrase ids get the responding group
        $result .= $this->set_grp_and_time_by_ids();

        // check if a new value is supposed to be added
        if ($this->id <= 0) {
            log_debug('value->save check if a value for "' . $this->name() . '" and user ' . $this->usr->name . ' is already in the database');
            // check if a value for this words is already in the database
            $db_chk = new value;
            $db_chk->grp_id = $this->grp_id;
            $db_chk->time_id = $this->time_id;
            $db_chk->time_stamp = $this->time_stamp;
            $db_chk->usr = $this->usr;
            $db_chk->load();
            if ($db_chk->id > 0) {
                log_debug('value->save value for "' . $this->grp->name() . '"@"' . $this->time_phr->name . '" and user ' . $this->usr->name . ' is already in the database and will be updated');
                $this->id = $db_chk->id;
            }
        }

        if ($this->id <= 0) {
            log_debug('value->save "' . $this->name() . '": ' . $this->number . ' for user ' . $this->usr->name . ' as a new value');

            $result .= $this->add($db_con);
        } else {
            log_debug('value->save update id ' . $this->id . ' to save "' . $this->number . '" for user ' . $this->usr->id);
            // update a value
            // todo: if no one else has ever changed the value, change to default value, else create a user overwrite

            // read the database value to be able to check if something has been changed
            // done first, because it needs to be done for user and general values
            $db_rec = new value;
            $db_rec->id = $this->id;
            $db_rec->usr = $this->usr;
            $db_rec->load();
            log_debug("value->save -> old database value loaded (" . $db_rec->number . ") with group " . $db_rec->grp_id . ".");
            $std_rec = new value;
            $std_rec->id = $this->id;
            $std_rec->usr = $this->usr; // must also be set to allow to take the ownership
            $std_rec->load_standard();
            log_debug("value->save -> standard value settings loaded (" . $std_rec->number . ")");

            // for a correct user value detection (function can_change) set the owner even if the value has not been loaded before the save
            if ($this->owner_id <= 0) {
                $this->owner_id = $std_rec->owner_id;
            }

            // check if the id parameters are supposed to be changed
            if ($result == '') {
                $result = $this->save_id_if_updated($db_con, $db_rec, $std_rec);
            }

            // if a problem has appeared up to here, don't try to save the values
            // the problem is shown to the user by the calling interactive script
            if ($result == '') {
                // if the user is the owner and no other user has adjusted the value, really delete the value in the database
                if (!$this->save_fields($db_con, $db_rec, $std_rec)) {
                    $result = 'Saving of fields for a value failed';
                    log_err($result);
                }
            }

        }
        return $result;
    }

    // true if the value (or value list) is saved as a time series
    private function is_time_series(): bool
    {
        return isset($this->time_stamp);
    }

}