<?php

/*

  value.php - the main number object
  ---------
  
  Common object for the tables values, user_values,
  in the database the object is save in two tables 
  because it is expected that there will be much less user values than standard values
  
  TODO: what happens if a user (not the value owner) is adding a word to the value
  TODO: split the object to a time term value and a time stamp value for memory saving
  
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
    public ?phrase_group $grp = null;     // phrases (word or triple) group object for this value
    public ?phrase $time_phr = null;      // the time (period) word object for this value
    public ?source $source = null;        // the source object

    // legacy derived database fields
    public ?array $ids = null;            // list of the word or triple ids (if > 0 id of a word if < 0 id of a triple)
    public ?phrase_list $phr_lst = null;  // the phrase object list for this value
    //public $phr_ids       = null;       // the phrase id list for this value loaded directly from the group
    public ?word_list $wrd_lst = null;    // the word object list for this value
    public ?array $wrd_ids = null;        // the word id list for this value loaded directly from the group
    public ?word_link_list $lnk_lst = null;        // the triple object list  for this value
    public ?array $lnk_ids = null;        // the triple id list  for this value loaded directly from the group
    // public $phr_all_lst  = null;       // $phr_lst including the time wrd
    // public $phr_all_ids  = null;       // $phr_ids including the time id
    public ?DateTime $update_time = null; // time of the last update, which could also be taken from the change log

    // field for user interaction
    public ?string $usr_value = null;     // the raw value as the user has entered it including formatting chars such as the thousand separator


    function __construct()
    {
        parent::__construct();
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
                    $this->share_id = cl(db_cl::SHARE_TYPE, share_type_list::DBL_PUBLIC);
                    $this->protection_id = cl(db_cl::PROTECTION_TYPE, protection_type_list::DBL_NO);
                }
            } else {
                $this->id = 0;
            }
        } else {
            $this->id = 0;
        }
    }


    /*
     * database load functions that reads the object from the database
     */

    /**
     * load the standard value use by most users for the given phrase group and time
     */
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

    /**
     * load the record from the database
     * in a separate function, because this can be called twice from the load function
     */
    function load_rec($sql_where)
    {
        global $db_con;

        $db_con->set_type(DB_TYPE_VALUE);
        $db_con->set_usr($this->usr->id);
        $db_con->set_fields(array('phrase_group_id', 'time_word_id'));
        $db_con->set_usr_num_fields(array('word_value', 'source_id', 'last_update', 'protection_type_id', 'excluded'));
        $db_con->set_usr_only_fields(array('share_type_id'));
        $db_con->set_where_text($sql_where);
        $sql = $db_con->select();

        $db_val = $db_con->get1($sql);
        $this->row_mapper($db_val, true);
        if ($this->id > 0) {
            log_debug('value->load_rec -> got ' . $this->number . ' with id ' . $this->id);
        }
    }

    /**
     * create the SQL to select a phrase group
     * @param bool $get_name set to true to get the query name
     * @return string the SQL statement to get the phrase group
     */
    function load_sql_group(bool $get_name = false): string
    {
        log_debug('value->load try best guess');
        $sql_name = 'value_phrase_group_by_';

        $sql_grp = '';
        if ($this->phr_lst != null) {
            $phr_lst = clone $this->phr_lst;
            if ($this->time_id <= 0) {
                $time_phr = $this->phr_lst->time_useful();
                $this->time_id = $time_phr->id;
            }
            $phr_lst->ex_time();

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
                // TODO add the number of words??
                $sql_name .= 'word_id';
                $pos++;
            }
            $sql_avoid_code_check_prefix = "SELECT";
            $sql_grp = $sql_avoid_code_check_prefix . ' l1.phrase_group_id 
                          FROM ' . $sql_grp_from . ' 
                         WHERE ' . $sql_grp_where;
        }
        if ($get_name) {
            $result = $sql_name;
        } else {
            $result = $sql_grp;
        }
        return $result;
    }

    /**
     * create the SQL to load a single value
     * @return string the SQL statement or the name of the SQL statement
     */
    function load_sql(bool $get_name = false): string
    {
        global $db_con;

        $sql_name = '';

        $sql_grp = $this->load_sql_group();

        // todo:
        // count the number of phrases per group
        // and add the user specific phrase links
        // select also the time
        $sql_time = '';
        $sql_avoid_code_check_prefix = "SELECT";

        if (isset($this->time_stamp)) {
            $sql_val = "SELECT value_time_series_id 
                            FROM value_time_series
                          WHERE phrase_group_id IN (" . $sql_grp . ");";
        } else {
            if ($this->time_id > 0) {
                $sql_time = ' AND time_word_id = ' . $this->time_id . ' ';
            }
            $sql_val = $sql_avoid_code_check_prefix . " value_id 
                            FROM " . $db_con->get_table_name(DB_TYPE_VALUE) . "
                          WHERE phrase_group_id IN (" . $sql_grp . ") " . $sql_time . ";";
        }
        if ($get_name) {
            $result = $sql_name;
        } else {
            $result = $sql_val;
        }
        return $result;
    }

    /**
     * load the missing value parameters from the database
     */
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
                    if (count($this->phr_lst->lst) > 0) {

                        $sql_val = $this->load_sql();
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

    /**
     * get the best matching value
     * 1. try to find a value with simply a different scaling e.g. if the number of share are requested, but this is in millions in the database use and scale it
     * 2. check if another measure type can be converted      e.g. if the share price in USD is requested, but only in EUR is in the database convert it
     *    e.g. for "ABB","Sales","2014" the value for "ABB","Sales","2014","million","CHF" will be loaded,
     *    because most values for "ABB", "Sales" are in ,"million","CHF"
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
                    // repeat adding a phrase utils a number is found
                }
            }
        }
        log_debug('value->load_best got ' . $this->number . ' for ' . $this->dsp_id());
    }

    /*
     * load object functions that extends the database load functions
     */

    /**
     * called from the user sandbox
     */
    function load_objects(): bool
    {
        $this->load_phrases();
        return true;
    }

    /**
     * load the phrase objects for this value if needed
     * not included in load, because sometimes loading of the word objects is not needed
     * maybe rename to load_objects
     * NEVER call the dsp_id function from this function or any called function, because this would lead to an endless loop
     */
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

    /**
     * load the source object
     * what happens if a source is updated
     */
    function load_source(): source
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

    /**
     * rebuild the word and triple list based on the group id
     */
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
                        log_debug('value->load_grp_by_id with words ' . $this->wrd_lst->name() . ' and triples ' . $this->lnk_lst->dsp_id() . ' by group ' . $this->grp_id . ' for "' . $this->usr->name . '"');
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

    /**
     * set the list objects based on the loaded phrase group
     * function to set depending objects based on loaded objects
     */
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

    /**
     * just load the time word object based on the id loaded from the database
     */
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

    /**
     * load the source and return the source name
     */
    function source_name(): string
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
     *  load object functions that extends the frontend functions
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

    /**
     * rebuild the phrase list based on the phrase ids
     */
    function set_phr_lst_by_ids(): bool
    {
        $result = false;

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
                    $result = $phr_lst->load();
                    $this->phr_lst = $phr_lst;
                }
            }
        }
        return $result;
    }

    /**
     * get the time based on the phrase id list
     */
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

    /**
     * rebuild the word and triple list based on the word and triple ids
     * add set the time_id if needed
     */
    function set_grp_by_ids(): string
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
                        zu_debug('value->set_grp_by_ids -> got '.$this->wrd_lst->name().' for '.dsp_array($this->ids).'');
                    }
                    */
                }
            }
        }
        log_debug('value->set_grp_by_ids -> group set to id ' . $this->grp_id);
        return $result;
    }

    /**
     * exclude the time period word from the phrase list
     */
    function set_phr_lst_ex_time()
    {
        log_debug('value->set_phr_lst_ex_time for "' . $this->phr_lst->name() . '" for "' . $this->usr->name . '"');
        $result = '';
        $this->phr_lst->ex_time();
        return $result;
    }


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

    /*
     * consistency check functions
     */

    /**
     * check the data consistency of this user value
     * e.g. update the value_phrase_links database table based on the group id
     */
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
            log_debug("To scale a value the number should not be empty.");
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
                            $frm->usr = $this->usr; // temp solution utils the bug of not setting is found
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
                                            if ($fv_wrd->is_type(word_type_list::DBL_SCALING_HIDDEN)
                                                and $r_wrd->is_type(word_type_list::DBL_SCALING)) {
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

    /**
     * import a value from an external object
     *
     * @param array $json_obj an array with the data of the json object
     * @param bool $do_save can be set to false for unit testing
     * @return bool true if the import has been successfully saved to the database
     */
    function import_obj(array $json_obj, bool $do_save = true): bool
    {
        global $share_types;
        global $protection_types;

        log_debug('value->import_obj');
        $result = false;

        $get_ownership = false;
        foreach ($json_obj as $key => $value) {

            if ($key == 'words') {
                $phr_lst = new phrase_list;
                $phr_lst->usr = $this->usr;
                $result = $phr_lst->import_lst($value, $do_save);
                if ($do_save) {
                    $phr_grp = $phr_lst->get_grp();
                    log_debug('value->import_obj got word group ' . $phr_grp->dsp_id());
                    $this->grp = $phr_grp;
                    $this->grp_id = $phr_grp->id;
                    log_debug('value->import_obj set grp id to ' . $this->grp_id);
                }
                $this->phr_lst = $phr_lst;
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
                $phr->usr = $this->usr;
                if (!$phr->import_obj($value, $do_save)) {
                    $result = false;
                } else {
                    $this->time_id = $phr->id;
                }
                $this->time_phr = $phr;
            }

            if ($key == 'number') {
                $this->number = $value;
            }

            if ($key == 'share') {
                $this->share_id = $share_types->id($value);
            }

            if ($key == 'protection') {
                $this->protection_id = $protection_types->id($value);
                if ($value <> protection_type_list::DBL_NO) {
                    $get_ownership = true;
                }
            }

            if ($key == 'source') {
                $src = new source;
                $src->usr = $this->usr;
                $src->name = $value;
                if ($do_save) {
                    $src->load();
                    if ($src->id == 0) {
                        $src->save();
                    }
                        $this->source_id = $src->id;
                }
                $this->source = $src;
            }


        }

        if ($result == true and $do_save) {
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

    // create an object for the export
    function export_obj(bool $do_load = true): value_exp
    {
        log_debug('value->export_obj');
        $result = new value_exp();

        // reload the value parameters
        if ($do_load) {
            $this->load();
            log_debug('value->export_obj load phrases');
            $this->load_phrases();
        }

        // add the phrases
        log_debug('value->export_obj get phrases');
        $phr_lst = array();
        // TODO use either word and triple export_obj function or phrase
        if ($this->phr_lst != null) {
            if (count($this->phr_lst->lst) > 0) {
                foreach ($this->phr_lst->lst as $phr) {
                    $phr_lst[] = $phr->name;
                }
                if (count($phr_lst) > 0) {
                    $result->words = $phr_lst;
                }
            }
        }

        // add the words
        log_debug('value->export_obj get words');
        $wrd_lst = array();
        // TODO use the triple export_obj function
        if ($this->wrd_lst != null) {
            if (count($this->wrd_lst->lst) > 0) {
                foreach ($this->wrd_lst->lst as $wrd) {
                    $wrd_lst[] = $wrd->name();
                }
                if (count($wrd_lst) > 0) {
                    $result->words = $wrd_lst;
                }
            }
        }

        // add the triples
        $triples_lst = array();
        // TODO use the triple export_obj function
        if ($this->lnk_lst != null) {
            if (count($this->lnk_lst->lst) > 0) {
                foreach ($this->lnk_lst->lst as $lnk) {
                    $triples_lst[] = $lnk->name();
                }
                if (count($triples_lst) > 0) {
                    $result->triples = $triples_lst;
                }
            }
        }

        // add the time
        if (isset($this->time_phr)) {
            /*
            $phr = new phrase;
            $phr->usr = $this->usr;
            $phr->id = $this->time_id;
            if ($do_load) {
                $phr->load();
            }
            */
            $result->time = $this->time_phr->name;
            log_debug('value->export_obj got time ' . $this->time_phr->dsp_id());
        }

        // add the value itself
        $result->number = $this->number;

        // add the share type
        log_debug('value->export_obj get share');
        if ($this->share_id > 0 and $this->share_id <> cl(db_cl::SHARE_TYPE, share_type_list::DBL_PUBLIC)) {
            $result->share = $this->share_type_code_id();
        }

        // add the protection type
        log_debug('value->export_obj get protection');
        if ($this->protection_id > 0 and $this->protection_id <> cl(db_cl::PROTECTION_TYPE, protection_type_list::DBL_NO)) {
            $result->protection = $this->protection_type_code_id();
        }

        // add the source
        log_debug('value->export_obj get source');
        if ($this->source != null) {
            $result->source = $this->source->name;
        }

        log_debug('value->export_obj -> ' . json_encode($result));
        return $result;
    }

    /*
     *  display functions
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
     * Select functions
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
        // if a user has been using the standard value utils now, just create a message, that the standard value has been changes and offer him to use the old standard value also in the future
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
            log_err('Cannot load phrases for group "'.$this->dsp_id().'".', "value->upd_phr_links");
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
                    log_err('Error adding new group links "' . dsp_array($add_ids) . '" for ' . $this->id);
                    $result = false;
                }
            }
        }
        log_debug('value->upd_phr_links -> added links "' . dsp_array($add_ids) . '" lead to ' . implode(",", $db_ids));

        // remove the links not needed any more
        if (count($del_ids) > 0) {
            log_debug('value->upd_phr_links -> del ' . implode(",", $del_ids) . '');
            $del_nbr = 0;
            $sql = 'DELETE FROM ' . $table_name . ' 
               WHERE value_id = ' . $this->id . '
                 AND ' . $field_name . ' IN (' . sql_array($del_ids) . ');';
            //$sql_result = $db_con->exe($sql, "value->upd_phr_links_delete", array());
            $sql_result = $db_con->exe($sql);
            if ($sql_result === False) {
                log_err('Error removing group links "' . dsp_array($del_ids) . '" from ' . $this->id);
                $result = false;
            }
        }
        log_debug('value->upd_phr_links -> deleted links "' . dsp_array($del_ids) . '" lead to ' . implode(",", $db_ids));

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
            $job->type = clo(DBL_JOB_VALUE_UPDATE);
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