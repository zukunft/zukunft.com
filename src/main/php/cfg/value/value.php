<?php

/*

    model/value/value.php - the main number object
    ---------------------

    TODO: always use the phrase group as master and update the value phrase links as slave

    TODO: move the time word to the phrase group because otherwise a geo tag or an area also needs to be seperated

    TODO: what happens if a user (not the value owner) is adding a word to the value
    TODO: split the object to a time term value and a time stamp value for memory saving
    TODO: create an extreme reduced base object for effective handling of mass data with just phrase group (incl. time if needed) and value with can be used for key value noSQL databases

    Common object for the tables values, user_values,
    in the database the object is save in two tables
    because it is expected that there will be much less user values than standard values

    A value is usually assigned to exact one phrase group, exceptions are time-series, geo-series or other phrase series values


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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/agpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2023 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace cfg;

include_once MODEL_SANDBOX_PATH . 'sandbox_value.php';
include_once MODEL_SANDBOX_PATH . 'sandbox.php';
include_once MODEL_GROUP_PATH . 'group.php';
include_once MODEL_FORMULA_PATH . 'figure.php';
include_once MODEL_VALUE_PATH . 'value_phrase_link_list.php';
include_once SERVICE_EXPORT_PATH . 'source_exp.php';
include_once SERVICE_EXPORT_PATH . 'value_exp.php';
include_once SERVICE_EXPORT_PATH . 'json.php';

use api\api;
use api\value_api;
use cfg\db\sql_creator;
use cfg\group\group;
use DateTime;
use Exception;
use html\value\value as value_dsp;
use im_export\export;
use math;
use model\export\exp_obj;
use model\export\source_exp;
use model\export\value_exp;

class value extends sandbox_value
{

    /*
     * database link
     */

    // object specific database and JSON object field names
    const FLD_ID = 'group_id';
    const FLD_VALUE = 'numeric_value';
    const FLD_LAST_UPDATE = 'last_update';

    // all database field names excluding the id and excluding the user specific fields
    const FLD_NAMES = array(
        group::FLD_ID
    );
    // list of the user specific numeric database field names
    const FLD_NAMES_NUM_USR = array(
        self::FLD_VALUE,
        source::FLD_ID,
        self::FLD_LAST_UPDATE,
        sandbox::FLD_EXCLUDED,
        sandbox::FLD_PROTECT
    );
    // all database field names excluding the id used to identify if there are some user specific changes
    const ALL_SANDBOX_FLD_NAMES = array(
        self::FLD_VALUE,
        source::FLD_ID,
        self::FLD_LAST_UPDATE,
        sandbox::FLD_EXCLUDED,
        sandbox::FLD_PROTECT
    );
    // list of field names that are only on the user sandbox row
    // e.g. the standard value does not need the share type, because it is by definition public (even if share types within a group of users needs to be defined, the value for the user group are also user sandbox table)
    const FLD_NAMES_USR_ONLY = array(
        sandbox::FLD_SHARE
    );


    /*
     * object vars
     */

    // related database objects
    public group $grp;  // phrases (word or triple) group object for this value
    public ?source $source;    // the source object
    private string $symbol = '';               // the symbol of the related formula element

    // deprecated fields
    public ?DateTime $time_stamp = null;  // the time stamp for this value (if this is set, the time wrd is supposed to be empty and the value is saved in the time_series table)

    // deprecated derived database fields
    public ?DateTime $update_time = null; // time of the last update, which could also be taken from the change log

    // field for user interaction
    public ?string $usr_value = null;     // the raw value as the user has entered it including formatting chars such as the thousand separator


    /*
     * construct and map
     */

    /**
     * set the user sandbox type for a value object and set the user, which is needed in all cases
     * @param user $usr the user who requested to see this value
     */
    function __construct(user $usr, int $id = 0, ?float $num_val = null, ?group $phr_grp = null)
    {
        parent::__construct($usr);
        $this->obj_type = sandbox::TYPE_VALUE;
        $this->obj_name = sql_db::TBL_VALUE;

        $this->rename_can_switch = UI_CAN_CHANGE_VALUE;

        $this->reset();

        if ($id != null) {
            $this->set_id($id);
        }
        if ($num_val != null) {
            $this->set_number($num_val);
        }
        if ($phr_grp != null) {
            $this->set_grp($phr_grp);
        }
        $this->set_last_update(new DateTime());
    }

    function reset(): void
    {
        parent::reset();

        $this->grp = new group($this->user());
        $this->source = null;

        $this->last_update = null;

        // deprecated fields
        $this->time_stamp = null;

        $this->update_time = null;
        $this->share_id = null;
        $this->protection_id = null;

        $this->usr_value = '';
    }

    /**
     * map the database fields to the object fields
     *
     * @param array|null $db_row with the data directly from the database
     * @param bool $load_std true if only the standard user sandbox object ist loaded
     * @param bool $allow_usr_protect false for using the standard protection settings for the default object used for all users
     * @param string $id_fld the name of the id field as defined in this child and given to the parent
     * @return bool true if the value is loaded and valid
     */
    function row_mapper_sandbox(
        ?array $db_row,
        bool   $load_std = false,
        bool   $allow_usr_protect = true,
        string $id_fld = self::FLD_ID
    ): bool
    {
        $lib = new library();
        $result = parent::row_mapper_sandbox($db_row, $load_std, $allow_usr_protect, self::FLD_ID);
        if ($result) {
            $this->number = $db_row[self::FLD_VALUE];
            // TODO check if phrase_group_id and time_word_id are user specific or time series specific
            $this->grp->set_id($db_row[group::FLD_ID]);
            $this->set_source_id($db_row[source::FLD_ID]);
            $this->last_update = $lib->get_datetime($db_row[self::FLD_LAST_UPDATE]);
        }
        return $result;
    }


    /*
     * cast
     */

    /**
     * @return value_api the value frontend api object
     */
    function api_obj(): object
    {
        $api_obj = new value_api();
        $this->fill_api_obj($api_obj);
        $api_obj->set_number($this->number());
        $api_obj->set_grp($this->grp->api_obj());
        $api_obj->set_is_std($this->is_std());
        return $api_obj;
    }

    /**
     * @returns string the api json message for the object as a string
     */
    function api_json(): string
    {
        return $this->api_obj()->get_json();
    }

    /**
     * just to shorten the code
     * @return value_dsp the value frontend object
     */
    function dsp_obj(): value_dsp
    {
        $api_json = $this->api_obj()->get_json();
        return new value_dsp($api_json);
    }


    /*
     * set and get
     */

    function set_symbol(string $symbol): void
    {
        $this->symbol = $symbol;
    }

    function symbol(): string
    {
        return $this->symbol;
    }

    function set_grp(group $grp): void
    {
        $this->grp = $grp;
    }

    function grp(): group
    {
        return $this->grp;
    }

    /**
     * map a value api json to this model value object
     * @param array $api_json the api array with the values that should be mapped
     */
    function set_by_api_json(array $api_json): user_message
    {
        global $share_types;
        global $protection_types;

        $msg = new user_message();
        $lib = new library();

        // make sure that there are no unexpected leftovers but keep the user
        $usr = $this->user();
        $this->reset();
        $this->set_user($usr);

        foreach ($api_json as $key => $value) {

            if ($key == api::FLD_ID) {
                $this->set_id($value);
            }

            if ($key == api::FLD_PHRASES) {
                $phr_lst = new phrase_list($this->user());
                $msg->add($phr_lst->set_by_api_json($value));
                if ($msg->is_ok()) {
                    $this->grp->set_phrase_list($phr_lst);
                }
            }

            if ($key == exp_obj::FLD_TIMESTAMP) {
                if (strtotime($value)) {
                    $this->time_stamp = $lib->get_datetime($value, $this->dsp_id(), 'JSON import');
                } else {
                    $msg->add_message('Cannot add timestamp "' . $value . '" when importing ' . $this->dsp_id());
                }
            }

            if ($key == exp_obj::FLD_NUMBER) {
                if (is_numeric($value)) {
                    $this->number = $value;
                } else {
                    $msg->add_message('Import value: "' . $value . '" is expected to be a number (' . $this->grp->dsp_id() . ')');
                }
            }

            if ($key == share_type::JSON_FLD) {
                $this->share_id = $share_types->id($value);
            }

            if ($key == protection_type::JSON_FLD) {
                $this->protection_id = $protection_types->id($value);
                if ($value <> protection_type::NO_PROTECT) {
                    $get_ownership = true;
                }
            }

            if ($key == source_exp::FLD_REF) {
                $src = new source($this->user());
                $src->set_name($value);
                $this->source = $src;
            }

        }

        return $msg;
    }

    function wrd_lst(): word_list
    {
        return $this->grp->phrase_list()->wrd_lst();
    }

    function trp_lst(): triple_list
    {
        return $this->grp->phrase_list()->trp_lst();
    }

    /**
     * @return array with the ids of the phrases
     */
    function ids(): array
    {
        return $this->grp->phrase_list()->ids();
    }


    /*
     * database load functions that reads the object from the database
     */

    /**
     * create the SQL to load the single default value always by the id
     * @param sql_creator $sc with the target db_type set
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_standard_sql(sql_creator $sc, string $class = self::class): sql_par
    {
        $sc->set_type(self::class);
        // overwrite the standard id field name (value_id) with the main database id field for values "group_id"
        $sc->set_id_field($this->id_field());
        $sc->set_fields(array_merge(self::FLD_NAMES, self::FLD_NAMES_NUM_USR, array(user::FLD_ID)));

        return parent::load_standard_sql($sc, $class);
    }

    /**
     * load the standard value use by most users for the given phrase group and time
     * @param sql_par|null $qp placeholder to align the function parameters with the parent
     * @param string $class the name of this class to be delivered to the parent function
     * @return bool true if the standard value has been loaded
     */
    function load_standard(?sql_par $qp = null, string $class = self::class): bool
    {
        global $db_con;
        $qp = $this->load_standard_sql($db_con->sql_creator());
        return parent::load_standard($qp, $class);
    }

    /**
     * create the common part of an SQL statement to retrieve the parameters of a value from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name extension to make the query name unique
     * @param string $class the name of the child class from where the call has been triggered
     * @param string $ext the table name extension e.g. to switch between standard and prime values
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(
        sql_creator $sc,
        string $query_name,
        string $class = self::class,
        string $ext = ''
    ): sql_par
    {
        $qp = parent::load_sql_multi($sc, $query_name, $class, $ext);

        // overwrite the standard id field name (value_id) with the main database id field for values "group_id"
        $sc->set_id_field($this->id_field());
        $sc->set_name($qp->name);
        $sc->set_usr($this->user()->id());
        $sc->set_fields(self::FLD_NAMES);
        $sc->set_usr_num_fields(self::FLD_NAMES_NUM_USR);
        $sc->set_usr_only_fields(self::FLD_NAMES_USR_ONLY);

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a value by id from the database
     * added to value just to assign the class for the user sandbox object
     *
     * @param sql_creator $sc with the target db_type set
     * @param int $id the id of the value
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_id(sql_creator $sc, int $id, string $class = self::class): sql_par
    {
        return parent::load_sql_by_id($sc, $id, $class);
    }

    /**
     * create an SQL statement to retrieve a value by phrase group from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param group $grp the id of the phrase group
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_grp(sql_creator $sc, group $grp, string $class = self::class): sql_par
    {
        $ext = $grp->table_extension();
        $qp = $this->load_sql($sc, 'group_id', $class, $ext);
        $sc->set_name($qp->name);
        $sc->set_usr($this->user()->id());
        $sc->set_fields(self::FLD_NAMES);
        $sc->set_usr_num_fields(self::FLD_NAMES_NUM_USR);
        $sc->set_usr_only_fields(self::FLD_NAMES_USR_ONLY);
        if ($grp->is_prime()) {
            $sc->add_where(group::FLD_ID, $grp->id());
        } else {
            $sc->add_where(group::FLD_ID, $grp->id());
        }
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * create the SQL to load a single user specific value
     *
     * @param sql_creator $sc with the target db_type set
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_obj_vars(sql_creator $sc, string $class = self::class): sql_par
    {
        $qp = parent::load_sql_obj_vars($sc, $class);
        $sql_where = '';
        $sql_grp = '';

        $sc->set_type(self::class);
        if ($this->id() > 0) {
            $qp->name .= sql_db::FLD_ID;
        } elseif ($this->grp->id() > 0) {
            $qp->name .= 'group_id';
        } elseif ($this->grp->phrase_list() != null) {
            $phr_lst = clone $this->grp->phrase_list();
            $pos = 1;
            foreach ($phr_lst->lst() as $phr) {
                $pos++;
            }
            if ($pos > 1) {
                $qp->name .= $pos;
            }
            $qp->name .= phrase::FLD_ID;
        }
        $sc->set_name($qp->name);
        $sc->set_usr($this->user()->id());
        $sc->set_fields(self::FLD_NAMES);
        $sc->set_usr_num_fields(self::FLD_NAMES_NUM_USR);
        $sc->set_usr_only_fields(self::FLD_NAMES_USR_ONLY);

        if ($this->id() > 0) {
            $sql_where = $sc->where_id(self::FLD_ID, $this->id, true);
        } elseif ($this->grp->id() > 0) {
            $sql_where = $sc->where_par(array(group::FLD_ID), array($this->grp->id()), true);
        } elseif ($this->grp->phrase_list() != null) {
            // create the SQL to select a phrase group which needs to inside load_sql for correct parameter counting
            $phr_lst = clone $this->grp->phrase_list();

            // the phrase groups with the least number of additional words that have at least one result
            $sql_grp_from = '';
            $sql_grp_where = '';
            $pos = 1;
            foreach ($phr_lst->lst() as $phr) {
                if ($sql_grp_from <> '') {
                    $sql_grp_from .= ',';
                }
                $sql_grp_from .= 'group_word_links l' . $pos;
                $pos_prior = $pos - 1;
                if ($sql_grp_where <> '') {
                    $sql_grp_where .= ' AND l' . $pos_prior . '.' . group::FLD_ID . ' = l' . $pos . '.' . group::FLD_ID . ' AND ';
                }
                $sc->add_where(self::FLD_ID, $phr->id());
                $sql_grp_where .= ' l' . $pos . '.word_id = ' . $sc->par_name();
                $pos++;
            }
            $sql_avoid_code_check_prefix = "SELECT";
            $sql_grp = 's.group_id IN (' . $sql_avoid_code_check_prefix . ' l1.' . group::FLD_ID . ' 
                          FROM ' . $sql_grp_from . ' 
                         WHERE ' . $sql_grp_where . ')';
            $sql_where .= $sql_grp;

        } else {
            log_err('At least the id, phrase group or phrase list must be set to load a value', 'value->load');
        }

        if ($sql_where != '') {

            $sc->set_where_text($sql_where);
            $qp->sql = $sc->select_by_set_id();
            $qp->par = $sc->get_par();

        }

        return $qp;
    }

    /**
     * load a value by the phrase group
     * @param group $grp the id of the phrase group
     * @param string $class the name of the child class from where the call has been triggered
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_grp(group $grp, string $class = self::class): int
    {
        global $db_con;

        log_debug($grp->dsp_id());
        $qp = $this->load_sql_by_grp($db_con->sql_creator(), $grp, $class);
        $id = $this->load_non_int_db_key($qp);

        // use the given phrase list
        if ($this->phr_lst()->is_empty() and !$grp->phrase_list()->is_empty()) {
            $this->grp = $grp;
            /*
        } else {
            // ... or reload the phrase list
            if ($this->phr_lst()->is_empty()) {
                $this->phr_lst()->load_by_phr();
            }
            */
        }

        return $id;
    }

    /**
     * load one database row e.g. value or result from the database
     * where the prime key is not nessesarry and integer
     * @param sql_par $qp the query parameters created by the calling function
     * @return int|string the id of the object found and zero if nothing is found
     */
    protected function load_non_int_db_key(sql_par $qp): int|string
    {
        global $db_con;

        $db_row = $db_con->get1($qp);
        $this->row_mapper_sandbox($db_row);
        return $this->id();
    }

    /**
     * load a value by the phrase ids
     * @param array $phr_ids with the phrase ids
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_phr_ids(array $phr_ids): int
    {
        $phr_lst = new phrase_list($this->user());
        $phr_lst->load_names_by_ids((new phr_ids($phr_ids)));
        return $this->load_by_grp($phr_lst->get_grp_id());
    }

    /**
     * load the missing value parameters from the database
     */
    function load_obj_vars(): bool
    {

        global $db_con;
        global $debug;
        $result = true;

        // check the all minimal input parameters
        if ($this->user() == null) {
            log_err('The user id must be set to load a result.', 'value->load');
        } else {
            log_debug($this->dsp_id(), $debug - 9);

            $qp = $this->load_sql_obj_vars($db_con->sql_creator());
            $db_val = $db_con->get1($qp);
            $result = $this->row_mapper_sandbox($db_val);

            // if not direct value is found try to get a more specific value
            // similar to result
            /* TODO review with a concrete test
            if ($this->id() <= 0 and isset($this->phr_lst)) {
                if (count($this->phr_lst->lst) > 0) {

                    $qp_val = $this->load_sql($db_con);
                    log_debug('value->load sql val "' . $qp_val->name . '"');
                    $db_con->usr_id = $this->user()->id();
                    $val_ids_rows = $db_con->get($qp_val);
                    if ($val_ids_rows != null) {
                        if (count($val_ids_rows) > 0) {
                            $val_id_row = $val_ids_rows[0];
                            $this->id() = $val_id_row[self::FLD_ID];
                            if ($this->id() > 0) {
                                $sql_where = "s.group_id = " . $this->id();
                                $qp = $this->load_sql($db_con);
                                $db_val = $db_con->get1($qp);
                                $this->row_mapper($db_val, true);
                                log_debug('value->loaded best guess id (' . $this->id() . ')');
                            }
                        }
                    }
                }
            }
            */
        }
        log_debug('got ' . $this->number() . ' with id ' . $this->id, $debug - 1);
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
        $this->load_by_grp($this->grp);
        // if not found try without scaling
        if ($this->id() <= 0) {
            $this->load_phrases();
            if (!$this->grp->phrase_list()->is_empty()) {
                log_err('No phrases found for ' . $this->dsp_id() . '.', 'value->load_best');
            } else {
                // try to get a value with another scaling
                $phr_lst_unscaled = clone $this->grp->phrase_list();
                $phr_lst_unscaled->ex_scaling();
                log_debug('try unscaled with ' . $phr_lst_unscaled->dsp_id());
                $grp_unscale = $phr_lst_unscaled->get_grp_id();
                $this->load_by_grp($grp_unscale);
                // if not found try with converted measure
                if ($this->id() <= 0) {
                    // try to get a value with another measure
                    $phr_lst_converted = clone $phr_lst_unscaled;
                    $phr_lst_converted->ex_measure();
                    log_debug('try converted with ' . $phr_lst_converted->dsp_id());
                    $grp_unscale = $phr_lst_converted->get_grp_id();
                    $this->grp->set_id($grp_unscale->id());
                    $this->load_by_grp($grp_unscale);
                    // TODO:
                    // check if there are any matching values at all
                    // if yes, get the most often used phrase
                    // repeat adding a phrase utils a number is found
                }
            }
        }
        log_debug('got ' . $this->number . ' for ' . $this->dsp_id());
    }

    function all_sandbox_fields(): array
    {
        return self::ALL_SANDBOX_FLD_NAMES;
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
        log_debug();
        // loading via word group is the most used case, because to save database space and reading time the value is saved with the word group id
        if ($this->grp->id() > 0) {
            $this->load_grp_by_id();
        }
        log_debug('done');
    }

    /**
     * load the source object
     * what happens if a source is updated
     */
    function load_source(): ?source
    {
        $src = null;
        log_debug('for ' . $this->dsp_id());

        if ($this->get_source_id() > 0) {
            $this->source->set_user($this->user());
            $this->source->load_by_id($this->get_source_id());
            $src = $this->source;
        } else {
            $this->source = null;
        }

        if (isset($src)) {
            log_debug($src->dsp_id());
        } else {
            log_debug('done');
        }
        return $src;
    }

    /**
     * rebuild the word and triple list based on the group id
     */
    function load_grp_by_id(): void
    {
        // if the group object is missing
        if ($this->grp->id() > 0) {
            // ... load the group related objects means the word and triple list
            $grp = new group($this->user()); // in case the word names and word links can be user specific maybe the owner should be used here
            $grp->load_by_id($this->grp->id()); // to make sure that the word and triple object lists are loaded
            if ($grp->id() > 0) {
                $this->grp = $grp;
            }
        }

        /*
        // if a list object is missing
        if (!isset($this->wrd_lst) or !isset($this->lnk_lst)) {
            if (isset($this->grp)) {
                $this->set_lst_by_grp();

                // these if's are only needed for debugging to avoid accessing an unset object, which would cause a crash
                if (isset($this->grp->phr_lst)) {
                    log_debug('got ' . $this->grp->phr_lst->dsp_name() . ' from group ' . $this->grp->id() . ' for "' . $this->user()->name . '"');
                }
                $wrd_lst = $this->wrd_lst();
                $trp_lst = $this->trp_lst();
                if (!$wrd_lst->is_empty()) {
                    if (!$trp_lst->is_empty()) {
                        log_debug('with words ' . $wrd_lst->name() . ' and triples ' . $trp_lst->dsp_id() . ' by group ' . $this->grp->id() . ' for "' . $this->user()->name . '"');
                    } else {
                        log_debug('with words ' . $wrd_lst->name() . ' by group ' . $this->grp->id() . ' for "' . $this->user()->name . '"');
                    }
                } else {
                    log_debug($this->grp->id() . ' for "' . $this->user()->name . '"');
                }
            }
        }
        */
        log_debug('done');
    }


    /*
     * information
     */

    /**
     * overwrites the standard db_object function because
     * the main id field of value is not value_id, but group_id
     * @return string the field name of the prime database index of the object
     */
    function id_field(): string
    {
        $lib = new library();
        return $lib->class_to_name(group::class) . sql_db::FLD_EXT_ID;
    }


    /*
     * Interface functions
     */

    /**
     * @return int the id of the source or zero if no source is defined
     */
    function get_source_id(): int
    {
        $result = 0;
        if ($this->source != null) {
            $result = $this->source->id();
        }
        return $result;
    }

    /**
     * create the source object if needed and set the id
     * @param int $id the id of the source
     */
    function set_source_id(?int $id): void
    {
        if ($id != null) {
            if ($id <> 0) {
                if ($this->source == null) {
                    $this->source = new source($this->user());
                }
                $this->source->set_id($id);
            }
        }
    }

    /**
     * load the source and return the source name
     * TODO avoid unneeded loading of sources
     */
    function source_name(): string
    {
        $result = '';
        log_debug($this->dsp_id());

        if ($this->get_source_id() > 0) {
            $this->load_source();
            if (isset($this->source)) {
                $result = $this->source->name();
            }
        }
        return $result;
    }


    /*
     * reduce code line length
     */

    /**
     * @return phrase_list the phrase list of this value from the phrase group
     */
    function phr_lst(): phrase_list
    {
        return $this->grp->phrase_list();
    }

    /**
     * @return array with the phrase names of this value from the phrase group
     */
    function phr_names(): array
    {
        return $this->grp->phrase_list()->names();
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
        log_debug('value->check id ' . $this->id() . ', for user ' . $this->user()->name);
        $this->load_by_id($this->id());
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
    function scale($target_wrd_lst): ?float
    {
        log_debug('value->scale ' . $this->number);
        // fallback value
        $result = $this->number;

        $lib = new library();

        $this->load_phrases();

        // check input parameters
        if (is_null($this->number)) {
            // this test should be done in the calling function if needed
            log_debug("To scale a value the number should not be empty.");
        } elseif (is_null($this->user()->id())) {
            log_warning("To scale a value the user must be defined.", "value->scale");
        } elseif ($this->grp->phrase_list()->is_empty()) {
            log_warning("To scale a value the word list should be loaded by the calling method.", "value->scale");
        } else {
            log_debug($this->number . ' for ' . $this->grp->dsp_id() . ' (user ' . $this->user()->id() . ')');

            // if it has a scaling word, scale it to one
            if ($this->grp->phrase_list()->has_scaling()) {
                log_debug('value words have a scaling words');
                // get any scaling words related to the value
                $scale_wrd_lst = $this->grp->phrase_list()->scaling_lst();
                if (count($scale_wrd_lst->lst()) > 1) {
                    log_warning('Only one scale word can be taken into account in the current version, but not a list like ' . $scale_wrd_lst->name() . '.', "value->scale");
                } else {
                    if (count($scale_wrd_lst->lst()) == 1) {
                        $scale_wrd = $scale_wrd_lst->lst()[0];
                        log_debug('word (' . $scale_wrd->name() . ')');
                        if ($scale_wrd->id() > 0) {
                            $frm = $scale_wrd->formula();
                            $frm->usr = $this->user(); // temp solution utils the bug of not setting is found
                            if (!isset($frm)) {
                                log_warning('No scaling formula defined for "' . $scale_wrd->name() . '".', "value->scale");
                            } else {
                                $formula_text = $frm->ref_text;
                                log_debug('scaling formula "' . $frm->name() . '" (' . $frm->id() . '): ' . $formula_text);
                                if ($formula_text <> "") {
                                    $l_part = $lib->str_left_of($formula_text, expression::CHAR_CALC);
                                    $r_part = $lib->str_right_of($formula_text, expression::CHAR_CALC);
                                    $exp = new expression($this->user());
                                    $exp->set_ref_text($frm->ref_text);
                                    $res_phr_lst = $exp->res_phr_lst();
                                    $phr_lst = $exp->phr_lst();
                                    if (!$res_phr_lst->is_empty()) {
                                        $res_wrd_lst = $res_phr_lst->wrd_lst_all();
                                        $wrd_lst = $phr_lst->wrd_lst_all();
                                        if (count($res_wrd_lst->lst()) == 1 and count($wrd_lst->lst()) == 1) {
                                            $res_wrd = $res_wrd_lst->lst()[0];
                                            $r_wrd = $wrd_lst->lst()[0];

                                            // test if it is a valid scale formula
                                            if ($res_wrd->is_type(phrase_type::SCALING_HIDDEN)
                                                and $r_wrd->is_type(phrase_type::SCALING)) {
                                                $wrd_symbol = expression::WORD_START . $r_wrd->id() . expression::WORD_END;
                                                log_debug('replace (' . $wrd_symbol . ' in ' . $r_part . ' with ' . $this->number . ')');
                                                $r_part = str_replace($wrd_symbol, $this->number, $r_part);
                                                log_debug('replace done (' . $r_part . ')');
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


    /*
     * im- and export
     */

    /**
     * import a value from an external object
     *
     * @param array $in_ex_json an array with the data of the json object
     * @param object|null $test_obj if not null the unit test object to get a dummy seq id
     * @return user_message the status of the import and if needed the error messages that should be shown to the user
     */
    function import_obj(array $in_ex_json, object $test_obj = null): user_message
    {
        log_debug();
        $result = parent::import_obj($in_ex_json, $test_obj);

        if ($test_obj) {
            $do_save = false;
        } else {
            $do_save = true;
        }

        $get_ownership = false;
        foreach ($in_ex_json as $key => $value) {

            if ($key == export::WORDS) {
                $phr_lst = new phrase_list($this->user());
                $result->add($phr_lst->import_lst($value, $test_obj));
                if ($result->is_ok()) {
                    $phr_grp = $phr_lst->get_grp_id($do_save);
                    $this->grp = $phr_grp;
                }
            }

            $result->add($this->set_fields_from_json($key, $value, $result, $do_save));

        }

        // save the value in the database
        if (!$test_obj) {
            if ($result->is_ok()) {
                $result->add_message($this->save());
            }
        }

        // try to get the ownership if requested
        if ($get_ownership) {
            $this->take_ownership();
        }

        return $result;
    }

    /**
     * import a simple value with just one related phrase
     *
     * @param string $phr_name the phrase name of the number value to add
     * @param float $value the numeric value that should be linked to the phrase
     * @param object|null $test_obj if not null the unit test object to get a dummy seq id
     * @return user_message the status of the import and if needed the error messages that should be shown to the user
     */
    function import_phrase_value(string $phr_name, float $value, object $test_obj = null): user_message
    {
        $msg = new user_message();
        log_debug();

        if ($test_obj) {
            $do_save = false;
        } else {
            $do_save = true;
        }

        $get_ownership = false;
        $phr_lst = new phrase_list($this->user());
        $phr = new phrase($this->user());
        if ($do_save) {
            $msg = $phr->get_or_add($phr_name);
        } else {
            $phr->set_name($phr_name);
        }

        if ($msg->is_ok()) {
            $phr_lst->add($phr);
            $phr_grp = $phr_lst->get_grp_id($do_save);
            $this->grp = $phr_grp;
            $this->number = $value;

            // save the value in the database
            if ($do_save) {
                $msg->add_message($this->save());
            }
        }

        return $msg;
    }

    /**
     * create an object for the export
     */
    function export_obj(bool $do_load = true): exp_obj
    {
        global $share_types;
        global $protection_types;
        log_debug();
        $result = new value_exp();

        // reload the value parameters
        if ($do_load) {
            $this->load_by_id($this->id());
            log_debug('load phrases');
            $this->load_phrases();
        }

        // add the words
        log_debug('get words');
        $wrd_lst = array();
        // TODO use the triple export_obj function
        if (!$this->grp->phrase_list()->is_empty()) {
            if (!$this->wrd_lst()->is_empty()) {
                foreach ($this->wrd_lst()->lst() as $wrd) {
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
        if (!$this->grp->phrase_list()->is_empty()) {
            if (!$this->trp_lst()->is_empty()) {
                foreach ($this->trp_lst()->lst as $lnk) {
                    $triples_lst[] = $lnk->name();
                }
                if (count($triples_lst) > 0) {
                    $result->triples = $triples_lst;
                }
            }
        }

        // add the value itself
        $result->number = $this->number;

        // add the share type
        if ($this->share_id > 0 and $this->share_id <> $share_types->id(share_type::PUBLIC)) {
            $result->share = $this->share_type_code_id();
        }

        // add the protection type
        if ($this->protection_id > 0 and $this->protection_id <> $protection_types->id(protection_type::NO_PROTECT)) {
            $result->protection = $this->protection_type_code_id();
        }

        // add the source
        if ($this->source != null) {
            $result->source = $this->source->name();
        }

        log_debug(json_encode($result));
        return $result;
    }

    /**
     * set the value object vars based on an api json array
     * similar to import_obj but using the database id instead of the names
     * the other side of the api_obj function
     *
     * @param array $api_json the api array
     * @return user_message false if a value could not be set
     */
    function save_from_api_msg(array $api_json, bool $do_save = true): user_message
    {
        log_debug();
        $result = new user_message();

        foreach ($api_json as $key => $value) {

            if ($key == export::WORDS) {
                $grp = new group($this->user());
                $result->add($grp->save_from_api_msg($value, $do_save));
                if ($result->is_ok()) {
                    $this->grp = $value;
                }
            }

            $result->add($this->set_fields_from_json($key, $value, $result, $do_save));

        }

        if ($result->is_ok() and $do_save) {
            $result->add_message($this->save());
        }

        return $result;
    }

    /**
     * set the all model vars based on a json key value pair that are not a database id
     * used for the import_obj and save_from_api_msg function
     *
     * @param string $key the json key
     * @param string|array $value the value from the json message or an array of sub json
     * @param user_message $msg the user message object to remember the message that should be shown to the user
     * @param bool $do_save false only for unit tests
     * @return user_message the enriched user message
     */
    private function set_fields_from_json(
        string       $key,
        string|array $value,
        user_message $msg,
        bool         $do_save = true): user_message
    {
        global $share_types;
        global $protection_types;
        $lib = new library();

        if ($key == exp_obj::FLD_TIMESTAMP) {
            if (strtotime($value)) {
                $this->time_stamp = $lib->get_datetime($value, $this->dsp_id(), 'JSON import');
            } else {
                $msg->add_message('Cannot add timestamp "' . $value . '" when importing ' . $this->dsp_id());
            }
        }

        if ($key == exp_obj::FLD_NUMBER) {
            if (is_numeric($value)) {
                $this->number = $value;
            } else {
                $msg->add_message('Import value: "' . $value . '" is expected to be a number (' . $this->grp->dsp_id() . ')');
            }
        }

        if ($key == share_type::JSON_FLD) {
            $this->share_id = $share_types->id($value);
        }

        if ($key == protection_type::JSON_FLD) {
            $this->protection_id = $protection_types->id($value);
            if ($value <> protection_type::NO_PROTECT) {
                $get_ownership = true;
            }
        }

        if ($key == source_exp::FLD_REF) {
            $src = new source($this->user());
            $src->set_name($value);
            if ($msg->is_ok() and $do_save) {
                $src->load_by_name($value);
                if ($src->id() == 0) {
                    $src->save();
                }
            }
            $this->source = $src;
        }

        return $msg;
    }


    /*
     *  display functions
     */

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

        return $result;
    }

    /*
     *  get functions that return other linked objects
     */

    /**
     * create and return the figure object for the value
     */
    function figure(): figure
    {
        return new figure($this);
    }

    /**
     * convert a user entry for a value to a useful database number
     * e.g. remove leading spaces and tabulators
     * if the value contains a single quote "'" the function asks once if to use it as a comma or a thousand operator
     * once the user has given an answer it saves the answer in the database and uses it for the next values
     * if the type of the value differs the user should be asked again
     */
    function convert(): string
    {
        log_debug('value->convert (' . $this->usr_value . ',u' . $this->user()->id() . ')');
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
    function res_lst_depending(): result_list
    {
        log_debug('value->res_lst_depending group id "' . $this->grp->id() . '" for user ' . $this->user()->name . '');
        $res_lst = new result_list($this->user());
        $res_lst->load_by_obj($this->grp, true);

        log_debug('done');
        return $res_lst;
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
        log_debug('value->not_used (' . $this->id() . ')');
        $result = true;

        // to review: maybe replace by a database foreign key check
        return $this->not_changed();
    }

    /**
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     *                 to check if the value has been changed
     */
    function not_changed_sql(sql_db $db_con): sql_par
    {
        $db_con->set_type(sql_db::TBL_VALUE);
        return $db_con->load_sql_not_changed($this->id, $this->owner_id, $this->id_field());
    }

    /**
     * true if no other user has modified the value
     */
    function not_changed(): bool
    {
        log_debug('value->not_changed id ' . $this->id() . ' by someone else than the owner (' . $this->owner_id . ')');

        global $db_con;
        $result = true;

        if ($this->id() == 0) {
            log_err('The id must be set to check if the formula has been changed');
        } else {
            $qp = $this->not_changed_sql($db_con);
            $db_row = $db_con->get1($qp);
            if ($db_row[user::FLD_ID] > 0) {
                $result = false;
            }
        }
        log_debug('value->not_changed for ' . $this->id() . ' is ' . zu_dsp_bool($result));
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
        if ($this->owner_id == $this->user()->id() or $this->owner_id <= 0) {
            $result = true;
        }

        log_debug(zu_dsp_bool($result));
        return $result;
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
    protected function add_usr_cfg(string $class = self::class): bool
    {
        global $db_con;
        $result = true;

        if (!$this->has_usr_cfg()) {
            log_debug('value->add_usr_cfg for "' . $this->id() . ' und user ' . $this->user()->name);

            // check again if there ist not yet a record
            $qp = $this->load_sql_user_changes($db_con->sql_creator());
            $db_con->usr_id = $this->user()->id();
            $db_row = $db_con->get1($qp);
            if ($db_row != null) {
                $this->usr_cfg_id = $this->user()->id();
            }
            if (!$this->has_usr_cfg()) {
                // create an entry in the user sandbox
                $db_con->set_type(sql_db::TBL_USER_PREFIX . sql_db::TBL_VALUE);
                $log_id = $db_con->insert(array(self::FLD_ID, user::FLD_ID), array($this->id, $this->user()->id()));
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
     * create an SQL statement to retrieve the user changes of the current value
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_user_changes(sql_creator $sc, string $class = self::class): sql_par
    {
        $sc->set_type(self::class, true);
        // overwrite the standard id field name (value_id) with the main database id field for values "group_id"
        $sc->set_id_field($this->id_field());
        return parent::load_sql_user_changes($sc, $class);
    }

    /**
     * set the log entry parameters for a value update
     */
    function log_upd(): change_log_named
    {
        log_debug('value->log_upd "' . $this->number . '" for user ' . $this->user()->id());
        $log = new change_log_named($this->user());
        $log->action = change_log_action::UPDATE;
        if ($this->can_change()) {
            $log->set_table(change_log_table::VALUE);
        } else {
            $log->set_table(change_log_table::VALUE_USR);
        }

        return $log;
    }

    /*
    // set the log entry parameter to delete a value
    function log_del($db_type) {
    zu_debug('value->log_del "'.$this->id.'" for user '.$this->user()->name);
    $log = New user_log_named;
    $log->usr       = $this->user();
    $log->action    = user_log::ACTION_DELETE;
    $log->table     = $db_type;
    $log->field     = 'numeric_value';
    $log->old_value = $this->number;
    $log->new_value = '';
    $log->row_id    = $this->id();
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
        $lib = new library();

        // get the list of phrases assigned to this value based on the phrase group
        // this list is the master
        $this->grp->load_by_obj_vars();
        $phr_lst = $this->grp->phrase_list();
        if ($phr_lst == null) {
            log_err('Cannot load phrases for value "' . $this->dsp_id() . '" and group "' . $this->grp->dsp_id() . '".', "value->upd_phr_links");
        } else {
            // TODO check if the phrases are already loaded
            // $phr_lst->load();
            $grp_ids = $phr_lst->id_lst();

            // read all existing phrase to value links for this value
            $lst = new value_phrase_link_list($this->user());
            $lst->load_by_value($this->user(), $this);
            $db_ids = $lst->phr_ids();

            // get what needs to be added or removed
            log_debug('should have phrase ids ' . implode(",", $grp_ids));
            $add_ids = array_diff($grp_ids, $db_ids);
            $del_ids = array_diff($db_ids, $grp_ids);
            log_debug('add ids ' . implode(",", $add_ids));
            log_debug('del ids ' . implode(",", $del_ids));


            // create the db link object for all actions
            $db_con->usr_id = $this->user()->id();

            $table_name = $db_con->get_table_name(sql_db::TBL_VALUE_PHRASE_LINK);
            $field_name = phrase::FLD_ID;

            // add the missing links
            if (count($add_ids) > 0) {
                $add_nbr = 0;
                $sql = '';
                foreach ($add_ids as $add_id) {
                    if ($add_id <> '') {
                        if ($sql == '') {
                            $sql = 'INSERT INTO ' . $table_name . ' (group_id, ' . $field_name . ') VALUES ';
                        }
                        $sql .= " (" . $this->id() . "," . $add_id . ") ";
                        $add_nbr++;
                        if ($add_nbr < count($add_ids)) {
                            $sql .= ",";
                        } else {
                            $sql .= ";";
                        }
                    }
                }
                $lib = new library();
                log_debug('add sql');
                if ($sql <> '') {
                    //$sql_result = $db_con->exe($sql, "value->upd_phr_links", array());
                    try {
                        $sql_result = $db_con->exe($sql);
                        if ($sql_result) {
                            $sql_error = pg_result_error($sql_result);
                            if ($sql_error != '') {
                                log_err('Error adding new group links "' . $lib->dsp_array($add_ids) . '" for ' . $this->id() . ' using ' . $sql . ' failed due to ' . $sql_error);
                            }
                        }
                    } catch (Exception $e) {
                        $trace_link = log_err('Cannot remove phrase group links with "' . $sql . '" because: ' . $e->getMessage());
                        $result = 'Removing of the phrase group links' . log::MSG_ERR_INTERNAL . $trace_link;
                    }
                }
            }
            $lib = new library();
            log_debug('added links "' . $lib->dsp_array($add_ids) . '" lead to ' . implode(",", $db_ids));

            // remove the links not needed any more
            if (count($del_ids) > 0) {
                log_debug('del ' . implode(",", $del_ids) . '');
                $del_nbr = 0;
                $sql_ids = $lib->sql_array($del_ids,
                    ' AND ' . $field_name . ' IN (', ')');
                $sql = 'DELETE FROM ' . $table_name . ' 
               WHERE group_id = ' . $this->id() . $sql_ids;
                //$sql_result = $db_con->exe($sql, "value->upd_phr_links_delete", array());
                try {
                    $sql_result = $db_con->exe($sql);
                    if ($sql_result != '') {
                        $msg = 'Removing the phrase group links "' . $lib->dsp_array($del_ids) . '" from ' . $this->id() . ' failed because: ' . $sql_result;
                        log_warning($msg);
                        $result = $msg;
                    }
                } catch (Exception $e) {
                    $trace_link = log_err('Cannot remove phrase group links with "' . $sql . '" because: ' . $e->getMessage());
                    $result = 'Removing of the phrase group links' . log::MSG_ERR_INTERNAL . $trace_link;
                }
            }

            log_debug('done');
        }
        return $result;
    }

    /*
    // set the parameter for the log entry to link a word to value
    function log_add_link($wrd_id) {
    zu_debug('value->log_add_link word "'.$wrd_id.'" to value '.$this->id());
    $log = New user_log_link;
    $log->usr       = $this->user();
    $log->action    = user_log::ACTION_ADD;
    $log->table     = 'value_phrase_links';
    $log->new_from  = $this->id();
    $log->new_to    = $wrd_id;
    $log->row_id    = $this->id();
    $log->link_text = 'word';
    $log->add_link_ref();

    return $log;
    }

    // set the parameter for the log entry to unlink a word to value
    function log_del_link($wrd_id) {
    zu_debug('value->log_del_link word "'.$wrd_id.'" from value '.$this->id());
    $log = New user_log_link;
    $log->usr       = $this->user();
    $log->action    = user_log::ACTION_DELETE;
    $log->table     = 'value_phrase_links';
    $log->old_from  = $this->id();
    $log->old_to    = $wrd_id;
    $log->row_id    = $this->id();
    $log->link_text = 'word';
    $log->add_link_ref();

    return $log;
    }

    // link an additional phrase the value
    function add_wrd($phr_id) {
    zu_debug("value->add_wrd add ".$phr_id." to ".$this->name().",t for user ".$this->user()->name.".");
    $result = false;

    if ($this->can_change()) {
      // log the insert attempt first
      $log = $this->log_add_link($phr_id);
      if ($log->id() > 0) {
        // insert the link
        $db_con = new mysql;
        $db_con->usr_id = $this->user()->id();
        $db_con->set_type(sql_db::TBL_VALUE_PHRASE_LINK);
        $val_wrd_id = $db_con->insert(array("group_id","phrase_id"), array($this->id,$phr_id));
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
    zu_debug('value->del_wrd from id '.$this->id.' the phrase "'.$wrd->name.'" by user '.$this->user()->name);
    $result = '';

    if ($this->can_change()) {
      // log the delete attempt first
      $log = $this->log_del_link($wrd->id());
      if ($log->id() > 0) {
        // remove the link
        $db_con = new mysql;
        $db_con->usr_id = $this->user()->id();
        $db_con->set_type(sql_db::TBL_VALUE_PHRASE_LINK);
        $result = $db_con->delete(array("group_id","phrase_id"), array($this->id,$wrd->id()));
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
        global $job_types;

        $result = '';

        $this->last_update = new DateTime();
        $db_con->set_type(sql_db::TBL_VALUE);
        if (!$db_con->update($this->id, value::FLD_LAST_UPDATE, 'Now()')) {
            $result = 'setting of value update trigger failed';
        }
        log_debug('value->save_field_trigger_update timestamp of ' . $this->id() . ' updated to "' . $this->last_update->format('Y-m-d H:i:s') . '"');

        // trigger the batch job
        // save the pending update to the database for the batch calculation
        log_debug('value->save_field_trigger_update group id "' . $this->grp->id() . '" for user ' . $this->user()->name . '');
        if ($this->id() > 0) {
            $job = new batch_job($this->user());
            $job->set_type(batch_job_type_list::VALUE_UPDATE);
            $job->obj = $this;
            $job->add();
        } else {
            $result = 'initiating of value update job failed';
        }
        log_debug('done');
        return $result;
    }

    /**
     * set the update parameters for the number
     */
    function save_field_number(sql_db $db_con, value $db_rec, value $std_rec): string
    {
        $result = '';
        if ($db_rec->number() <> $this->number()) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->number();
            $log->new_value = $this->number();
            $log->std_value = $std_rec->number();
            $log->row_id = $this->id();
            $log->set_field(self::FLD_VALUE);
            $result .= $this->save_field_user($db_con, $log);
            // updating the number is definitely relevant for calculation, so force to update the timestamp
            log_debug('trigger update');
            $result .= $this->save_field_trigger_update($db_con);
        }
        return $result;
    }


    /**
     * set the update parameters for the source link
     */
    function save_field_source(sql_db $db_con, value $db_rec, value $std_rec): string
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
            $log->row_id = $this->id();
            $log->set_field(source::FLD_ID);
            $result = $this->save_field_user($db_con, $log);
        }
        return $result;
    }

    /**
     * save the value number and the source
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @param value|sandbox $db_rec the database record before the saving
     * @param value|sandbox $std_rec the database record defined as standard because it is used by most users
     * @return string if not empty the message that should be shown to the user
     */
    function save_fields(sql_db $db_con, value|sandbox $db_rec, value|sandbox $std_rec): string
    {
        $result = $this->save_field_number($db_con, $db_rec, $std_rec);
        $result .= $this->save_field_source($db_con, $db_rec, $std_rec);
        $result .= $this->save_field_share($db_con, $db_rec, $std_rec);
        $result .= $this->save_field_protection($db_con, $db_rec, $std_rec);
        $result .= $this->save_field_excluded($db_con, $db_rec, $std_rec);
        log_debug('value->save_fields all fields for "' . $this->id() . '" has been saved');
        return $result;
    }

    /**
     * updated the view component name (which is the id field)
     * should only be called if the user is the owner and nobody has used the display component link
     */
    function save_id_fields(sql_db $db_con, sandbox $db_rec, sandbox $std_rec): string
    {
        log_debug('value->save_id_fields');
        $result = '';

        // to load any missing objects
        $db_rec->load_phrases();
        $this->load_phrases();
        $std_rec->load_phrases();

        if ($db_rec->grp->id() <> $this->grp->id()) {
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
            $log->old_id = $db_rec->grp->id();
            $log->new_id = $this->grp->id();
            $log->std_id = $std_rec->grp->id();
            $log->row_id = $this->id();
            $log->set_field(change_log_field::FLD_VALUE_GROUP);
            if ($log->add()) {
                $db_con->set_type(sql_db::TBL_VALUE);
                $result = $db_con->update($this->id,
                    array(group::FLD_ID),
                    array($this->grp->id()));
            }
        }
        log_debug('value->save_id_fields group updated for ' . $this->dsp_id());

        // not yet active
        /*
        if ($db_rec->time_stamp <> $this->time_stamp) {
          zu_debug('value->save_id_fields to '.$this->dsp_id().' from "'.$db_rec->dsp_id().'" (standard '.$std_rec->dsp_id().')');
          $log = $this->log_upd();
          $log->old_value = $db_rec->time_stamp;
          $log->new_value = $this->time_stamp;
          $log->std_value = $std_rec->time_stamp;
          $log->row_id    = $this->id();
          $log->field     = 'time_stamp';
          if ($log->add()) {
            $result .= $db_con->update($this->id, array("time_stamp"),
                                                  array($this->time_stamp));
          }
        }
        */

        return $result;
    }

    /**
     * check if the id parameters are supposed to be changed
     */
    function save_id_if_updated($db_con, sandbox $db_rec, sandbox $std_rec): string
    {
        log_debug('value->save_id_if_updated has name changed from "' . $db_rec->dsp_id() . '" to "' . $this->dsp_id() . '"');
        $result = '';

        // if the phrases or time has changed, check if value with the same phrases/time already exists
        if ($db_rec->grp->id() <> $this->grp->id() or $db_rec->time_stamp <> $this->time_stamp) {
            // check if a value with the same phrases/time is already in the database
            $chk_val = new value($this->user());
            //$chk_val->time_phr = $this->time_phr;
            //$chk_val->time_stamp = $this->time_stamp;
            $chk_val->load_by_grp($this->grp);
            log_debug('value->save_id_if_updated check value loaded');
            if ($chk_val->id() > 0) {
                // TODO if the target value is already in the database combine the user changes with this values
                // $this->id() = $chk_val->id();
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
                    $this->set_id(0);
                    $this->owner_id = $this->user()->id();
                    $result .= $this->add($db_con)->get_last_message();
                    log_debug('value->save_id_if_updated recreate the value "' . $db_rec->dsp_id() . '" as ' . $this->dsp_id() . ' (standard "' . $std_rec->dsp_id() . '")');
                }
            }
        } else {
            log_debug('value->save_id_if_updated no id field updated (group ' . $db_rec->grp->id() . '=' . $this->grp->id() . ')');
        }

        log_debug('value->save_id_if_updated for ' . $this->dsp_id() . ' has been done');
        return $result;
    }

    /**
     * add a new value
     * @return user_message with status ok
     *                      or if something went wrong
     *                      the message that should be shown to the user
     *                      including suggested solutions
     */
    function add(): user_message
    {
        log_debug('value->add the value ' . $this->dsp_id());

        global $db_con;
        $result = new user_message();

        // log the insert attempt first
        $log = $this->log_add();
        if ($log->id() > 0) {
            // insert the value
            $db_con->set_type(sql_db::TBL_VALUE);
            $this->set_id($db_con->insert(
                array(group::FLD_ID, user::FLD_ID, self::FLD_VALUE, self::FLD_LAST_UPDATE),
                array($this->grp->id(), $this->user()->id, $this->number, "Now()")));
            if ($this->id() > 0) {
                // update the reference in the log
                if (!$log->add_ref($this->id())) {
                    $result->add_message('adding the value reference in the system log failed');
                }

                // update the phrase links for fast searching
                $upd_result = $this->upd_phr_links();
                if ($upd_result != '') {
                    $result->add_message('Adding the phrase links of the value failed because ' . $upd_result);
                    $this->set_id(0);
                }

                if ($this->id() > 0) {
                    // create an empty db_rec element to force saving of all set fields
                    $db_val = new value($this->user());
                    $db_val->set_id($this->id());
                    $db_val->number = $this->number; // ... but not the field saved already with the insert
                    $std_val = clone $db_val;
                    // save the value fields
                    $result->add_message($this->save_fields($db_con, $db_val, $std_val));
                }

            } else {
                $result->add_message("Adding value " . $this->id() . " failed.");
            }
        }

        return $result;
    }

    /**
     * insert or update a number in the database or save a user specific number
     */
    function save(): string
    {
        log_debug('->save');

        global $db_con;
        $result = '';

        // build the database object because the is anyway needed
        $db_con->set_type(sql_db::TBL_VALUE);
        $db_con->set_usr($this->user()->id());

        // check if a new value is supposed to be added
        if ($this->id() <= 0) {
            log_debug('value->save check if a value for "' . $this->name() . '" and user ' . $this->user()->name . ' is already in the database');
            // check if a value for this words is already in the database
            $db_chk = new value($this->user());
            //$db_chk->time_phr = $this->time_phr;
            //$db_chk->time_stamp = $this->time_stamp;
            $db_chk->load_by_grp($this->grp);
            if ($db_chk->id() > 0) {
                if ($this->grp->id() != 0 and $this->user() == null) {
                    log_debug('value for "' . $this->grp->name() . '" and user ' . $this->user()->name . ' is already in the database and will be updated');
                } else {
                    log_debug('value is empty');
                }
                $this->set_id($db_chk->id());
            }
        }

        if ($this->id() <= 0) {
            log_debug('value->save "' . $this->name() . '": ' . $this->number . ' for user ' . $this->user()->name . ' as a new value');

            $result .= $this->add($db_con)->get_last_message();
        } else {
            log_debug('update id ' . $this->id() . ' to save "' . $this->number . '" for user ' . $this->user()->id());
            // update a value
            // TODO: if no one else has ever changed the value, change to default value, else create a user overwrite

            // read the database value to be able to check if something has been changed
            // done first, because it needs to be done for user and general values
            $db_rec = new value($this->user());
            $db_rec->load_by_id($this->id());
            log_debug("old database value loaded (" . $db_rec->number . ") with group " . $db_rec->grp->id() . ".");
            $std_rec = new value($this->user()); // user must also be set to allow to take the ownership
            $std_rec->set_id($this->id());
            $std_rec->load_standard();
            log_debug("standard value settings loaded (" . $std_rec->number . ")");

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