<?php

/*

    value.php - the main number object
    ---------

    Common object for the tables values, user_values,
    in the database the object is save in two tables
    because it is expected that there will be much less user values than standard values

    A value is usually assigned to exact one phrase group, exceptions are time-series, geo-series or other phrase series values

    TODO: always use the phrase group as master and update the value phrase links as slave

    TODO: move the time word to the phrase group because otherwise a geo tag or an area also needs to be seperated

    TODO: what happens if a user (not the value owner) is adding a word to the value
    TODO: split the object to a time term value and a time stamp value for memory saving
    TODO: create an extreme reduced base object for effective handling of mass data with just phrase group (incl. time if needed) and value with can be used for key value noSQL databases


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
    // a list of dummy values that are used for system tests
    const TEST_VALUE = 123456;
    const TEST_FLOAT = 123.456;
    const TEST_BIG = 123456789;
    const TEST_BIGGER = 234567890;
    const TEST_USER_HIGH_QUOTE = "123'456";
    const TEST_USER_SPACE = "123 456";
    const TEST_PCT = 0.182642816772838; // to test the percentage calculation by the percent of Swiss inhabitants living in Canton Zurich
    const TEST_INCREASE = 0.007871833296164; // to test the increase calculation by the increase of inhabitants in Switzerland from 2019 to 2020
    const TV_CANTON_ZH_INHABITANTS_2020_IN_MIO = 1.553423;
    const TV_CITY_ZH_INHABITANTS_2019 = 415367;
    const TV_CH_INHABITANTS_2019_IN_MIO = 8.438822;
    const TV_CH_INHABITANTS_2020_IN_MIO = 8.505251;
    const TV_SHARE_PRICE = 17.08;
    const TV_EARNINGS_PER_SHARE = 1.22;

    // object specific database and JSON object field names
    const FLD_ID = 'value_id';
    const FLD_VALUE = 'word_value';
    const FLD_TIME_WORD = 'time_word_id';
    const FLD_LAST_UPDATE = 'last_update';

    // all database field names excluding the id and excluding the user specific fields
    const FLD_NAMES = array(
        phrase_group::FLD_ID,
        self::FLD_TIME_WORD
    );
    // list of the user specific numeric database field names
    const FLD_NAMES_NUM_USR = array(
        self::FLD_VALUE,
        source::FLD_ID,
        self::FLD_LAST_UPDATE,
        self::FLD_EXCLUDED,
        sql_db::FLD_PROTECT
    );
    // list of field names that are only on the user sandbox row
    // e.g. the standard value does not need the share type, because it is by definition public (even if share types within a group of users needs to be defined, the value for the user group are also user sandbox table)
    const FLD_NAMES_USR_ONLY = array(
        sql_db::FLD_SHARE
    );

    // related database objects
    public phrase_group $grp;  // phrases (word or triple) group object for this value
    public ?source $source;    // the source object

    // simple database fields
    public ?DateTime $last_update = null; // the time of the last update of fields that may influence the calculated results

    // fields to deprecate
    public ?phrase $time_phr;             // deprecation reason: the time phrase should no longer be seperated; has been the time (period) word object for this value

    // deprecated fields
    public ?DateTime $time_stamp = null;  // the time stamp for this value (if this is set, the time wrd is supposed to be empty and the value is saved in the time_series table)

    // deprecated derived database fields
    public ?array $ids = null;            // list of the word or triple ids (if > 0 id of a word if < 0 id of a triple)
    public ?phrase_list $phr_lst = null;  // the phrase object list for this value
    public ?word_list $wrd_lst = null;    // the word object list for this value
    public ?word_link_list $lnk_lst = null;        // the triple object list  for this value
    public ?DateTime $update_time = null; // time of the last update, which could also be taken from the change log

    // field for user interaction
    public ?string $usr_value = null;     // the raw value as the user has entered it including formatting chars such as the thousand separator

    /**
     * set the user sandbox type for a value object and set the user, which is needed in all cases
     * @param user $usr the user who requested to see this value
     */
    function __construct(user $usr)
    {
        parent::__construct();
        $this->obj_type = user_sandbox::TYPE_VALUE;
        $this->obj_name = DB_TYPE_VALUE;

        $this->rename_can_switch = UI_CAN_CHANGE_VALUE;

        $this->reset($usr);
    }

    function reset(?user $usr = null)
    {
        parent::reset();

        $this->grp = new phrase_group();
        $this->source = null;

        if ($usr != null) {
            $this->usr = $usr;
        }

        $this->last_update = null;

        // deprecated fields
        $this->time_stamp = null;

        $this->ids = null;
        $this->phr_lst = null;
        $this->wrd_lst = null;
        $this->lnk_lst = null;
        $this->time_phr = null;
        $this->update_time = null;
        $this->share_id = null;
        $this->protection_id = null;

        $this->usr_value = '';

    }

    /**
     * @return value_dsp the value object with the display interface functions
     */
    function dsp_obj(): object
    {
        $dsp_obj = new value_dsp($this->usr);

        $dsp_obj = parent::fill_dsp_obj($dsp_obj);

        $dsp_obj->number = $this->number;
        $dsp_obj->source = $this->source;
        $dsp_obj->time_stamp = $this->time_stamp;
        $dsp_obj->last_update = $this->last_update;

        $dsp_obj->ids = $this->ids;
        $dsp_obj->phr_lst = $this->phr_lst;
        $dsp_obj->wrd_lst = $this->wrd_lst;
        $dsp_obj->lnk_lst = $this->lnk_lst;
        $dsp_obj->grp = $this->grp;
        $dsp_obj->time_phr = $this->time_phr;
        $dsp_obj->update_time = $this->update_time;
        $dsp_obj->share_id = $this->share_id;
        $dsp_obj->protection_id = $this->protection_id;

        $dsp_obj->usr_value = $this->usr_value;

        return $dsp_obj;
    }

    function row_mapper(array $db_row, bool $map_usr_fields = false): bool
    {
        $result = false;
        if ($db_row != null) {
            if ($db_row[self::FLD_ID] > 0) {
                $this->id = $db_row[self::FLD_ID];
                $this->number = $db_row[self::FLD_VALUE];
                // check if phrase_group_id and time_word_id are user specific or time series specific
                $this->grp->id = $db_row[phrase_group::FLD_ID];
                $this->set_source_id($db_row[source::FLD_ID]);
                $this->set_time_id($db_row[self::FLD_TIME_WORD]);
                $this->owner_id = $db_row[self::FLD_USER];
                $this->last_update = $this->get_datetime($db_row[self::FLD_LAST_UPDATE]);
                $this->excluded = $db_row[self::FLD_EXCLUDED];
                if ($map_usr_fields) {
                    parent::row_mapper_usr($db_row, self::FLD_ID);
                } else {
                    parent::row_mapper_std();
                }
                $result = true;
            } else {
                $this->id = 0;
            }
        } else {
            $this->id = 0;
        }
        return $result;
    }


    /*
     * database load functions that reads the object from the database
     */

    /**
     * create the SQL to load the single default value always by the id
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_standard_sql(sql_db $db_con, string $class = ''): sql_par
    {
        $db_con->set_type(DB_TYPE_VALUE);
        $db_con->set_fields(array_merge(self::FLD_NAMES, self::FLD_NAMES_NUM_USR, array(sql_db::FLD_USER_ID)));

        return parent::load_standard_sql($db_con, self::class);
    }

    /**
     * load the standard value use by most users for the given phrase group and time
     */
    function load_standard(): bool
    {
        global $db_con;
        $result = false;

        if ($this->id <= 0) {
            log_err('The value id must be set to load ' . self::class, self::class . '->load_standard');
        } else {
            $qp = $this->load_standard_sql($db_con);
            $db_val = $db_con->get1($qp);
            if ($this->row_mapper($db_val)) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * create the SQL to load a single user specific value
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql_db $db_con): sql_par
    {
        $qp = new sql_par();
        $qp->name = self::class . '_by_';
        $sql_where = '';
        $sql_grp = '';

        $db_con->set_type(DB_TYPE_VALUE);

        if ($this->id > 0) {
            $qp->name .= 'id';
            $sql_where = $db_con->where_id(self::FLD_ID, $this->id, true);
        } elseif ($this->grp->id > 0) {
            if ($this->get_time_id() <> 0) {
                $qp->name .= 'phrase_group_and_time_id';
                $sql_where = $db_con->where_par(array(phrase_group::FLD_ID, self::FLD_TIME_WORD), array($this->grp->id, $this->get_time_id()), true);
            } else {
                $qp->name .= 'phrase_group_id';
                $sql_where = $db_con->where_par(array(phrase_group::FLD_ID), array($this->grp->id), true);
            }
        } elseif ($this->phr_lst != null) {
            // create the SQL to select a phrase group which needs to inside load_sql for correct parameter counting
            $phr_lst = clone $this->phr_lst;
            if ($this->get_time_id() == 0) {
                $time_phr = $this->phr_lst->time_useful();
                if ($time_phr != null) {
                    $this->time_phr = $time_phr;
                }
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
                    $sql_grp_where .= ' AND l' . $pos_prior . '.' . phrase_group::FLD_ID . ' = l' . $pos . '.' . phrase_group::FLD_ID . ' AND ';
                }
                $db_con->add_par(sql_db::PAR_INT, $phr->id);
                $sql_grp_where .= ' l' . $pos . '.word_id = ' . $db_con->par_name();
                $pos++;
            }
            if ($pos > 1) {
                $qp->name .= $pos;
            }
            $sql_avoid_code_check_prefix = "SELECT";
            $sql_grp = 's.phrase_group_id IN (' . $sql_avoid_code_check_prefix . ' l1.' . phrase_group::FLD_ID . ' 
                          FROM ' . $sql_grp_from . ' 
                         WHERE ' . $sql_grp_where . ')';
            $sql_where .= $sql_grp;

            $sql_name_time = '';
            if ($this->get_time_id() <> 0) {
                $sql_name_time = '_and_' . self::FLD_TIME_WORD;
                $db_con->add_par(sql_db::PAR_INT, $this->get_time_id());
                $sql_where .= ' AND ' . self::FLD_TIME_WORD . ' = ' . $db_con->par_name() . ' ';
            }
            $qp->name .= phrase::FLD_ID . $sql_name_time;

        } else {
            log_err('At least the id, phrase group or phrase list must be set to load a value', 'value->load');
        }

        if ($sql_where != '') {

            $db_con->set_name($qp->name);
            $db_con->set_usr($this->usr->id);
            $db_con->set_fields(self::FLD_NAMES);
            $db_con->set_usr_num_fields(self::FLD_NAMES_NUM_USR);
            $db_con->set_usr_only_fields(self::FLD_NAMES_USR_ONLY);
            $db_con->set_where_text($sql_where);
            $qp->sql = $db_con->select();
            $qp->par = $db_con->get_par();

        }

        return $qp;
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

            $qp = $this->load_sql($db_con);
            $db_val = $db_con->get1($qp);
            $result = $this->row_mapper($db_val, true);

            // if not direct value is found try to get a more specific value
            // similar to formula_value
            /* TODO review with a concrete test
            if ($this->id <= 0 and isset($this->phr_lst)) {
                if (count($this->phr_lst->lst) > 0) {

                    $qp_val = $this->load_sql($db_con);
                    log_debug('value->load sql val "' . $qp_val->name . '"');
                    $db_con->usr_id = $this->usr->id;
                    $val_ids_rows = $db_con->get($qp_val);
                    if ($val_ids_rows != null) {
                        if (count($val_ids_rows) > 0) {
                            $val_id_row = $val_ids_rows[0];
                            $this->id = $val_id_row[self::FLD_ID];
                            if ($this->id > 0) {
                                $sql_where = "s.value_id = " . $this->id;
                                $qp = $this->load_sql($db_con);
                                $db_val = $db_con->get1($qp);
                                $this->row_mapper($db_val, true);
                                log_debug('value->loaded best guess id (' . $this->id . ')');
                            }
                        }
                    }
                }
            }
            */
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
     */
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
                $this->grp->id = $grp_unscale->id;
                $this->load();
                // if not found try with converted measure
                if ($this->id <= 0) {
                    // try to get a value with another measure
                    $phr_lst_converted = clone $phr_lst_unscaled;
                    $phr_lst_converted->ex_measure();
                    log_debug('value->load_best try converted with ' . $phr_lst_converted->dsp_id());
                    $grp_unscale = $phr_lst_converted->get_grp();
                    $this->grp->id = $grp_unscale->id;
                    $this->load();
                    // TODO:
                    // check if there are any matching values at all
                    // if yes, get the most often used phrase
                    // repeat adding a phrase utils a number is found
                }
            }
        }
        log_debug('value->load_best got ' . $this->number . ' for ' . $this->dsp_id());
    }

    /*
     * load object functions that extend the database load functions
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
        if ($this->grp->id > 0) {
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

        if ($this->get_source_id() > 0) {
            $this->source->usr = $this->usr;
            $this->source->load();
        } else {
            $this->source = null;
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
            if ($this->grp->id > 0) {
                // ... load the group related objects means the word and triple list
                $grp = new phrase_group;
                $grp->id = $this->grp->id;
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
                    log_debug('value->load_grp_by_id got ' . $this->phr_lst->name() . ' from group ' . $this->grp->id . ' for "' . $this->usr->name . '"');
                }
                if (isset($this->wrd_lst)) {
                    if (isset($this->lnk_lst)) {
                        log_debug('value->load_grp_by_id with words ' . $this->wrd_lst->name() . ' and triples ' . $this->lnk_lst->dsp_id() . ' by group ' . $this->grp->id . ' for "' . $this->usr->name . '"');
                    } else {
                        log_debug('value->load_grp_by_id with words ' . $this->wrd_lst->name() . ' by group ' . $this->grp->id . ' for "' . $this->usr->name . '"');
                    }
                } else {
                    log_debug('value->load_grp_by_id ' . $this->grp->id . ' for "' . $this->usr->name . '"');
                }
            }
        }
        log_debug('value->load_grp_by_id -> done');
    }

    /*
     * Interface functions
     */

    /**
     * @return int the id of the time phrase or zero if no time phrase is defined
     */
    function get_time_id(): int
    {
        $result = 0;
        if ($this->time_phr != null) {
            $result = $this->time_phr->id;
        }
        return $result;
    }

    /**
     * create the time phrase if needed and set the id
     * @param int|null $id the id of the time phrase
     */
    function set_time_id(?int $id)
    {
        if ($id != null) {
            if ($id <> 0) {
                if ($this->time_phr == null) {
                    $this->time_phr = new phrase();
                    $this->usr = $this->usr;
                }
                $this->time_phr->id = $id;
            }
        }
    }

    /**
     * @return int the id of the source or zero if no source is defined
     */
    function get_source_id(): int
    {
        $result = 0;
        if ($this->source != null) {
            $result = $this->source->id;
        }
        return $result;
    }

    /**
     * create the source object if needed and set the id
     * @param int|null $id the id of the source
     */
    function set_source_id(?int $id)
    {
        if ($id != null) {
            if ($id <> 0) {
                if ($this->source == null) {
                    $this->source = new source();
                    $this->source->usr = $this->usr;
                }
                $this->source->id = $id;
            }
        }
    }

    /**
     * set the list objects based on the loaded phrase group
     * function to set depending objects based on loaded objects
     */
    function set_lst_by_grp()
    {
        if (isset($this->grp)) {
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
        if ($this->get_time_id() <> 0) {
            $this->time_phr->load();
        }
    }

    /**
     * load the source and return the source name
     */
    function source_name(): string
    {
        $result = '';
        log_debug('value->source_name');
        log_debug('value->source_name for ' . $this->dsp_id());

        if ($this->get_source_id() > 0) {
            $this->load_source();
            if (isset($this->source)) {
                $result = $this->source->name;
            }
        }
        return $result;
    }

    /*
     *  load object functions that extend the frontend functions
     */

    //
    function set_grp_and_time_by_ids(?array $ids): string
    {
        $result = '';
        if ($ids != null) {
            // 1. load the phrases parameters based on the ids
            $result = $this->set_phr_lst_by_ids($ids);
            // 2. extract the time from the phrase list
            $result .= $this->set_time_by_phr_lst();
            // 3. get the group based on the phrase list
            $result .= $this->set_grp_by_ids();
            if ($this->ids == null) {
                log_debug('value->set_grp_and_time_by_ids ids are null');
            } else {
                log_debug('value->set_grp_and_time_by_ids "' . implode(",", $ids) . '" to "' . $this->grp->id . '" and ' . $this->get_time_id());
            }
        }
        return $result;
    }

    /**
     * rebuild the phrase list based on the phrase ids
     */
    function set_phr_lst_by_ids(array $ids): string
    {
        $result = '';

        // check the parameters
        if (empty($this->usr)) {
            $result = 'User must be set to load ' . $this->dsp_id() . ' to load the phrase list';
        } else {
            if (empty($this->phr_lst)) {
                if (!empty($this->ids)) {
                    log_debug('value->set_phr_lst_by_ids for "' . implode(",", $ids) . '" and "' . $this->usr->name . '"');
                    $phr_lst = new phrase_list($this->usr);
                    $phr_lst->ids = $ids;
                    if (!$phr_lst->load()) {
                        $result = 'Cannot load phrases by id';
                    }
                    $this->phr_lst = $phr_lst;
                }
            }
        }
        return $result;
    }

    /**
     * get the time based on the phrase id list
     */
    function set_time_by_phr_lst(): string
    {
        $result = '';
        if (isset($this->phr_lst)) {
            log_debug('value->set_time_by_phr_lst from ' . $this->phr_lst->name());
            if ($this->get_time_id() == 0) {
                $wrd_lst = $this->phr_lst->wrd_lst_all();
                $this->time_phr = $wrd_lst->assume_time();
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
                    /* actually not needed
                    $this->set_lst_by_grp();
                    if (isset($this->wrd_lst)) {
                        zu_debug('value->set_grp_by_ids -> got '.$this->wrd_lst->name().' for '.dsp_array($this->ids).'');
                    }
                    */
                }
            }
        }
        log_debug('value->set_grp_by_ids -> group set to id ' . $this->grp->id);
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
        if ($this->upd_phr_links() != '') {
            $result = false;
        }

        log_debug('value->check done');
        return $result;
    }

    /**
     * scale a value for the target words
     * e.g. if the target words contains "millions" "2'100'000" is converted to "2.1"
     *      if the target words are empty convert "2.1 mio" to "2'100'000"
     * once this is working switch on the call in word_list->value_scaled
     */
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
                                    if (isset($fv_phr_lst)) {
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
                                                // TODO separate time from value words
                                                $calc = new math();
                                                $result = $calc->parse($r_part);
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

            // TODO: scale the number to the target scaling
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
    function import_obj(array $json_obj, bool $do_save = true): string
    {
        global $share_types;
        global $protection_types;

        log_debug('value->import_obj');
        $result = '';

        $get_ownership = false;
        foreach ($json_obj as $key => $value) {

            if ($key == 'words') {
                $phr_lst = new phrase_list($this->usr);
                $result .= $phr_lst->import_lst($value, $do_save);
                if ($do_save) {
                    $phr_grp = $phr_lst->get_grp();
                    log_debug('value->import_obj got word group ' . $phr_grp->dsp_id());
                    $this->grp = $phr_grp;
                    log_debug('value->import_obj set grp id to ' . $this->grp->id);
                }
                $this->phr_lst = $phr_lst;
            }

            if ($key == 'timestamp') {
                if (strtotime($value)) {
                    $this->time_stamp = get_datetime($value, $this->dsp_id(), 'JSON import');
                } else {
                    log_err('Cannot add timestamp "' . $value . '" when importing ' . $this->dsp_id(), 'value->import_obj');
                }
            }

            if ($key == 'time') {
                $phr = new phrase;
                $phr->usr = $this->usr;
                if (!$phr->import_obj($value, $do_save)) {
                    $result = 'Failed to import time ' . $value;
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

    /**
     * create an object for the export
     */
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

    /**
     * create and return the description for this value for debugging
     */
    function dsp_id(): string
    {
        $result = '';

        if ($this->phr_lst != null) {
            $result .= $this->phr_lst->dsp_id();
        }
        $result .= $this->usr_value;

        return $result;
    }

    /**
     * create and return the description for this value
     * TODO check if $this->load_phrases() needs to be called before calling this function
     */
    function name(): string
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
     *  get functions that returns other linked objects
     */

    /**
     * create and return the figure object for the value
     */
    function figure(): figure
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

    /**
     * convert a user entry for a value to a useful database number
     * e.g. remove leading spaces and tabulators
     * if the value contains a single quote "'" the function asks once if to use it as a comma or a thousand operator
     * once the user has given an answer it saves the answer in the database and uses it for the next values
     * if the type of the value differs the user should be asked again
     */
    function convert()
    {
        log_debug('value->convert (' . $this->usr_value . ',u' . $this->usr->id . ')');
        $result = $this->usr_value;
        $result = str_replace(" ", "", $result);
        $result = str_replace("'", "", $result);
        //$result = str_replace(".", "", $result);
        $this->number = floatval($result);
        return $result;
    }


    /*
     * Select functions
     */

    /**
     * get a list of all formula results that are depending on this value
     * TODO: add a loop over the calculation if the are more formula results needs to be updated than defined with SQL_ROW_MAX
     */
    function fv_lst_depending()
    {
        log_debug('value->fv_lst_depending group id "' . $this->grp->id . '" for user ' . $this->usr->name . '');
        $fv_lst = new formula_value_list;
        $fv_lst->usr = $this->usr;
        $fv_lst->grp_id = $this->grp->id;
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
    add_usr_cfg  - to create a record for user specific settings
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

    function used(): bool
    {
        return !$this->not_used();
    }

    /**
     * true if no one has used this value
     */
    function not_used(): bool
    {
        log_debug('value->not_used (' . $this->id . ')');
        $result = true;

        // to review: maybe replace by a database foreign key check
        return $this->not_changed();
    }

    /**
     * true if no other user has modified the value
     */
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
        $db_row = $db_con->get1_old($sql);
        $change_user_id = $db_row[self::FLD_USER];
        if ($change_user_id > 0) {
            $result = false;
        }
        log_debug('value->not_changed for ' . $this->id . ' is ' . zu_dsp_bool($result));
        return $result;
    }

    /**
     * search for the median (not average) value
     */
    function get_std()
    {
    }

    /**
     * this value object is defined as the standard value
     */
    function set_std()
    {
        // if a user has been using the standard value utils now, just create a message, that the standard value has been changes and offer him to use the old standard value also in the future
        // delete all user values that are matching the new standard
        // save the new standard value in the database
    }

    /**
     * true if the loaded value is not user specific
     * TODO: check the difference between is_std and can_change
     */
    function is_std(): bool
    {
        $result = false;
        if ($this->owner_id == $this->usr->id or $this->owner_id <= 0) {
            $result = true;
        }

        log_debug('value->is_std -> (' . zu_dsp_bool($result) . ')');
        return $result;
    }

    /**
     * true if the user is the owner and no one else has changed the value
     */
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

    /**
     * true if a record for a user specific configuration already exists in the database
     */
    function has_usr_cfg(): bool
    {
        $has_cfg = false;
        if ($this->usr_cfg_id > 0) {
            $has_cfg = true;
        }
        return $has_cfg;
    }

    /**
     * create a database record to save a user specific value
     */
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
            $db_row = $db_con->get1_old($sql);
            if ($db_row != null) {
                $this->usr_cfg_id = $db_row[self::FLD_USER];
            }
            if (!$this->has_usr_cfg()) {
                // create an entry in the user sandbox
                $db_con->set_type(DB_TYPE_USER_PREFIX . DB_TYPE_VALUE);
                $log_id = $db_con->insert(array(self::FLD_ID, user_sandbox::FLD_USER), array($this->id, $this->usr->id));
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

    /**
     * check if the database record for the user specific settings can be removed
     * exposed at the moment to user_display.php for consistency check, but this should not be needed
     */
    function del_usr_cfg_if_not_needed(): bool
    {
        log_debug('value->del_usr_cfg_if_not_needed pre check for "' . $this->id . ' und user ' . $this->usr->name);

        global $db_con;
        $result = true;

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
        $usr_cfg = $db_con->get1_old($sql);
        log_debug('value->del_usr_cfg_if_not_needed check for "' . $this->id . ' und user ' . $this->usr->name . ' with (' . $sql . ')');
        if ($usr_cfg != false) {
            if ($usr_cfg[self::FLD_ID] > 0) {
                if ($usr_cfg['word_value'] == Null
                    and $usr_cfg[source::FLD_ID] == Null
                    and $usr_cfg[self::FLD_EXCLUDED] == Null) {
                    // delete the entry in the user sandbox
                    log_debug('value->del_usr_cfg_if_not_needed any more for "' . $this->id . ' und user ' . $this->usr->name);
                    $result = $this->del_usr_cfg_exe($db_con);
                }
            }
        }

        return $result;
    }

    /**
     * set the log entry parameters for a value update
     */
    function log_upd(): user_log_named
    {
        log_debug('value->log_upd "' . $this->number . '" for user ' . $this->usr->id);
        $log = new user_log_named;
        $log->usr = $this->usr;
        $log->action = user_log::ACTION_UPDATE;
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
    $log = New user_log_named;
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

    /**
     * update the phrase links to the value based on the group and time for faster searching
     * e.g. if the value "46'000" is linked to the group "2116 (ABB, SALES, CHF, MIO)" it is checked that lines to all phrases to the value are in the database
     *      to be able to search the value by a single phrase
     * TODO: REVIEW and make it user specific!
     */
    function upd_phr_links(): string
    {
        log_debug('value->upd_phr_links');

        global $db_con;
        $result = '';

        // get the list of phrases assigned to this value based on the phrase group
        // this list is the master
        $this->grp->load();
        $phr_lst = $this->grp->phr_lst;
        if ($phr_lst == null) {
            log_err('Cannot load phrases for value "' . $this->dsp_id() . '" and group "' . $this->grp->dsp_id() . '".', "value->upd_phr_links");
        } else {
            $phr_lst->load();
            $grp_ids = $phr_lst->ids();

            // add the time phrase id if needed
            // TODO remove or replace with the series phrase id
            if ($this->get_time_id() <> 0) {
                if (!in_array($this->get_time_id(), $grp_ids)) {
                    $grp_ids[] = $this->get_time_id();
                }
            }

            // read all existing phrase to value links for this value
            $lst = new value_phrase_link_list($this->usr);
            $lst->load_by_value($this->usr, $this);
            $db_ids = $lst->phr_ids();

            // get what needs to be added or removed
            log_debug('value->upd_phr_links -> should have phrase ids ' . implode(",", $grp_ids));
            $add_ids = array_diff($grp_ids, $db_ids);
            $del_ids = array_diff($db_ids, $grp_ids);
            log_debug('value->upd_phr_links -> add ids ' . implode(",", $add_ids));
            log_debug('value->upd_phr_links -> del ids ' . implode(",", $del_ids));


            // create the db link object for all actions
            $db_con->usr_id = $this->usr->id;

            $table_name = $db_con->get_table_name(DB_TYPE_VALUE_PHRASE_LINK);
            $field_name = phrase::FLD_ID;

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
                    try {
                        $sql_result = $db_con->exe($sql);
                        if ($sql_result) {
                            $sql_error = pg_result_error($sql_result);
                            if ($sql_error != '') {
                                log_err('Error adding new group links "' . dsp_array($add_ids) . '" for ' . $this->id . ' using ' . $sql . ' failed due to ' . $sql_error);
                            }
                        }
                    } catch (Exception $e) {
                        $trace_link = log_err('Cannot remove phrase group links with "' . $sql . '" because: ' . $e->getMessage());
                        $result = 'Removing of the phrase group links' . log::MSG_ERR_INTERNAL . $trace_link;
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
                try {
                    $sql_result = $db_con->exe($sql);
                    if ($sql_result != '') {
                        $msg = 'Removing the phrase group links "' . dsp_array($del_ids) . '" from ' . $this->id . ' failed because: ' . $sql_result;
                        log_warning($msg);
                        $result = $msg;
                    }
                } catch (Exception $e) {
                    $trace_link = log_err('Cannot remove phrase group links with "' . $sql . '" because: ' . $e->getMessage());
                    $result = 'Removing of the phrase group links' . log::MSG_ERR_INTERNAL . $trace_link;
                }
            }

            log_debug('value->upd_phr_links -> done');
        }
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
          // TODO: call the word group creation
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

    /**
     * update the time stamp to trigger an update of the depending on results
     */
    function save_field_trigger_update($db_con): string
    {
        $result = '';

        $this->last_update = new DateTime();
        $db_con->set_type(DB_TYPE_VALUE);
        if (!$db_con->update($this->id, 'last_update', 'Now()')) {
            $result = 'setting of value update trigger failed';
        }
        log_debug('value->save_field_trigger_update timestamp of ' . $this->id . ' updated to "' . $this->last_update->format('Y-m-d H:i:s') . '"');

        // trigger the batch job
        // save the pending update to the database for the batch calculation
        log_debug('value->save_field_trigger_update group id "' . $this->grp->id . '" for user ' . $this->usr->name . '');
        if ($this->id > 0) {
            $job = new batch_job;
            $job->type = cl(db_cl::JOB_TYPE, job_type_list::VALUE_UPDATE);
            //$job->usr  = $this->usr;
            $job->obj = $this;
            $job->add();
        } else {
            $result = 'initiating of value update job failed';
        }
        log_debug('value->save_field_trigger_update -> done');
        return $result;
    }

// set the update parameters for the number
    function save_field_number($db_con, $db_rec, $std_rec): string
    {
        $result = '';
        if ($db_rec->number <> $this->number) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->number;
            $log->new_value = $this->number;
            $log->std_value = $std_rec->number;
            $log->row_id = $this->id;
            $log->field = self::FLD_VALUE;
            $result .= $this->save_field_do($db_con, $log);
            // updating the number is definitely relevant for calculation, so force to update the timestamp
            log_debug('value->save_field_number -> trigger update');
            $result .= $this->save_field_trigger_update($db_con);
        }
        return $result;
    }

// set the update parameters for the source link
    function save_field_source($db_con, $db_rec, $std_rec): string
    {
        $result = '';
        if ($db_rec->get_source_id() <> $this->get_source_id()) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->source_name();
            $log->old_id = $db_rec->get_source_id();
            $log->new_value = $this->source_name();
            $log->new_id = $this->get_source_id();
            $log->std_value = $std_rec->source_name();
            $log->std_id = $std_rec->get_source_id();
            $log->row_id = $this->id;
            $log->field = source::FLD_ID;
            $result = $this->save_field_do($db_con, $log);
        }
        return $result;
    }

// save the value number and the source
    function save_fields($db_con, $db_rec, $std_rec): string
    {
        $result = $this->save_field_number($db_con, $db_rec, $std_rec);
        $result .= $this->save_field_source($db_con, $db_rec, $std_rec);
        $result .= $this->save_field_share($db_con, $db_rec, $std_rec);
        $result .= $this->save_field_protection($db_con, $db_rec, $std_rec);
        $result .= $this->save_field_excluded($db_con, $db_rec, $std_rec);
        log_debug('value->save_fields all fields for "' . $this->id . '" has been saved');
        return $result;
    }

    /**
     * updated the view component name (which is the id field)
     * should only be called if the user is the owner and nobody has used the display component link
     */
    function save_id_fields($db_con, $db_rec, $std_rec): string
    {
        log_debug('value->save_id_fields');
        $result = '';

        // to load any missing objects
        $db_rec->load_phrases();
        $this->load_phrases();
        $std_rec->load_phrases();

        if ($db_rec->grp_id <> $this->grp->id) {
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
            $log->new_id = $this->grp->id;
            $log->std_id = $std_rec->grp_id;
            $log->row_id = $this->id;
            $log->field = 'phrase_group_id';
            if ($log->add()) {
                $db_con->set_type(DB_TYPE_VALUE);
                $result = $db_con->update($this->id,
                    array(phrase_group::FLD_ID),
                    array($this->grp->id));
            }
        }
        log_debug('value->save_id_fields group updated for ' . $this->dsp_id());

        if ($result == '') {
            if ($db_rec->get_time_id() <> $this->get_time_id()) {
                log_debug('value->save_id_fields to ' . $this->dsp_id() . ' from "' . $db_rec->dsp_id() . '" (standard ' . $std_rec->dsp_id() . ')');
                $log = $this->log_upd();
                $log->old_value = $db_rec->time_phr->name();
                $log->old_id = $db_rec->get_time_id();
                $log->new_value = $this->time_phr->name();
                $log->new_id = $this->get_time_id();
                $log->std_value = $std_rec->time_phr->name();
                $log->std_id = $std_rec->get_time_id();
                $log->row_id = $this->id;
                $log->field = 'time_word_id';
                if ($log->add()) {
                    $db_con->set_type(DB_TYPE_VALUE);
                    if (!$db_con->update(
                        $this->id,
                        array("time_word_id"),
                        array($this->get_time_id()))) {
                        $result .= 'updating time word of value failed';
                    }
                }
            }
            log_debug('value->save_id_fields time updated for ' . $this->dsp_id());

            // update the phrase links for fast searching
            $result .= $this->upd_phr_links();
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

    /**
     * check if the id parameters are supposed to be changed
     */
    function save_id_if_updated($db_con, user_sandbox $db_rec, user_sandbox $std_rec): string
    {
        log_debug('value->save_id_if_updated has name changed from "' . $db_rec->dsp_id() . '" to "' . $this->dsp_id() . '"');
        $result = '';

        // if the phrases or time has changed, check if value with the same phrases/time already exists
        if ($db_rec->grp->id <> $this->grp->id or $db_rec->get_time_id() <> $this->get_time_id() or $db_rec->time_stamp <> $this->time_stamp) {
            // check if a value with the same phrases/time is already in the database
            $chk_val = new value($this->usr);
            $chk_val->grp->id = $this->grp->id;
            $chk_val->time_phr = $this->time_phr;
            $chk_val->time_stamp = $this->time_stamp;
            $chk_val->load();
            log_debug('value->save_id_if_updated check value loaded');
            if ($chk_val->id > 0) {
                // TODO if the target value is already in the database combine the user changes with this values
                // $this->id = $chk_val->id;
                // $result .= $this->save();
                log_debug('value->save_id_if_updated update the existing ' . $chk_val->dsp_id());
            } else {

                log_debug('value->save_id_if_updated target value name does not yet exists for ' . $this->dsp_id());
                if ($this->can_change() and $this->not_used()) {
                    // in this case change is allowed and done
                    log_debug('value->save_id_if_updated change the existing display component link ' . $this->dsp_id() . ' (db "' . $db_rec->dsp_id() . '", standard "' . $std_rec->dsp_id() . '")');
                    //$this->load_objects();
                    $result .= $this->save_field_excluded($db_con, $db_rec, $std_rec);
                    if ($result != '') {
                        log_err('Excluding value has failed');
                    }
                } else {
                    // if the target link has not yet been created
                    // ... request to delete the old
                    $to_del = clone $db_rec;
                    $msg = $to_del->del();
                    $result .= $msg->get_last_message();
                    // .. and create a deletion request for all users ???

                    // ... and create a new display component link
                    $this->id = 0;
                    $this->owner_id = $this->usr->id;
                    $result .= $this->add($db_con);
                    log_debug('value->save_id_if_updated recreate the value "' . $db_rec->dsp_id() . '" as ' . $this->dsp_id() . ' (standard "' . $std_rec->dsp_id() . '")');
                }
            }
        } else {
            log_debug('value->save_id_if_updated no id field updated (group ' . $db_rec->grp->id . '=' . $this->grp->id . ', time ' . $db_rec->get_time_id() . '=' . $this->get_time_id() . ')');
        }

        log_debug('value->save_id_if_updated for ' . $this->dsp_id() . ' has been done');
        return $result;
    }

    /**
     * add a new value
     */
    function add(): string
    {
        log_debug('value->add the value ' . $this->dsp_id());

        global $db_con;
        $result = '';

        // log the insert attempt first
        $log = $this->log_add();
        if ($log->id > 0) {
            // insert the value
            $db_con->set_type(DB_TYPE_VALUE);
            $this->id = $db_con->insert(
                array(phrase_group::FLD_ID, "time_word_id", "user_id", self::FLD_VALUE, self::FLD_LAST_UPDATE),
                array($this->grp->id, $this->get_time_id(), $this->usr->id, $this->number, "Now()"));
            if ($this->id > 0) {
                // update the reference in the log
                if (!$log->add_ref($this->id)) {
                    $result = 'adding the value reference in the system log failed';
                }

                // update the phrase links for fast searching
                $upd_result = $this->upd_phr_links();
                if ($upd_result != '') {
                    $result = 'Adding the phrase links of the value failed because ' . $upd_result;
                    $this->id = 0;
                }

                if ($this->id > 0) {
                    // create an empty db_rec element to force saving of all set fields
                    $db_val = new value($this->usr);
                    $db_val->id = $this->id;
                    $db_val->number = $this->number; // ... but not the field saved already with the insert
                    $std_val = clone $db_val;
                    // save the value fields
                    $result .= $this->save_fields($db_con, $db_val, $std_val);
                }

            } else {
                $result = "Adding value " . $this->id . " failed.";
            }
        }

        return $result;
    }

    /**
     * insert or update a number in the database or save a user specific number
     */
    function save(): string
    {
        log_debug(self::class . '->save');

        global $db_con;
        $result = '';

        // build the database object because the is anyway needed
        $db_con->set_type(DB_TYPE_VALUE);
        $db_con->set_usr($this->usr->id);

        // rebuild the value ids if needed e.g. if the front end function has just set a list of phrase ids get the responding group
        $result .= $this->set_grp_and_time_by_ids($this->ids);

        // check if a new value is supposed to be added
        if ($this->id <= 0) {
            log_debug('value->save check if a value for "' . $this->name() . '" and user ' . $this->usr->name . ' is already in the database');
            // check if a value for this words is already in the database
            $db_chk = new value($this->usr);
            $db_chk->grp = $this->grp;
            $db_chk->time_phr = $this->time_phr;
            $db_chk->time_stamp = $this->time_stamp;
            $db_chk->load();
            if ($db_chk->id > 0) {
                if ($this->grp != null and $this->time_phr != null and $this->usr != null) {
                    log_debug('value->save value for "' . $this->grp->name() . '"@"' . $this->time_phr->name . '" and user ' . $this->usr->name . ' is already in the database and will be updated');
                } else {
                    log_debug('value->save value is empty');
                }
                $this->id = $db_chk->id;
            }
        }

        if ($this->id <= 0) {
            log_debug('value->save "' . $this->name() . '": ' . $this->number . ' for user ' . $this->usr->name . ' as a new value');

            $result .= $this->add($db_con);
        } else {
            log_debug('value->save update id ' . $this->id . ' to save "' . $this->number . '" for user ' . $this->usr->id);
            // update a value
            // TODO: if no one else has ever changed the value, change to default value, else create a user overwrite

            // read the database value to be able to check if something has been changed
            // done first, because it needs to be done for user and general values
            $db_rec = new value($this->usr);
            $db_rec->id = $this->id;
            $db_rec->load();
            log_debug("value->save -> old database value loaded (" . $db_rec->number . ") with group " . $db_rec->grp->id . ".");
            $std_rec = new value($this->usr); // user must also be set to allow to take the ownership
            $std_rec->id = $this->id;
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
                $result = $this->save_fields($db_con, $db_rec, $std_rec);
            }

        }

        if ($result != '') {
            log_err($result);
        }

        return $result;
    }

}