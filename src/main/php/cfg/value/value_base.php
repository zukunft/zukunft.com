<?php

/*

    model/value/value.php - the main number object
    ---------------------

    TODO: split the group_id key into single phrase keys for faster db index based selection
    TODO: always use the phrase group as master and update the value phrase links as slave

    TODO: to read a value
          first check the fastest and least used cache pod for the given phrase (not phrase list or group!)
          second check inside the pod for the expected value table oder OLAP cube
          e.g. a classic table e.g. for swiss addresses
          or an OLAP cube for XBRL values
          or a key-value table for a group of 1, 2, 4, 8, 16 and more phrases (or s standard table if public)


    TODO: move the time word to the phrase group because otherwise a geo tag or an area also needs to be separated

    TODO: what happens if a user (not the value owner) is adding a word to the value
    TODO: split the object to a time term value and a time stamp value for memory saving
    TODO: create an extreme reduced base object for effective handling of mass data with just phrase group (incl. time if needed) and value with can be used for key value noSQL databases
    TODO: remove PRIMARY KEY creation for prime tables aor allow null column in primary key
    TODO: split the number, text and geo value object
    TODO: split and move the dto_read and dto_write parts to separate objects

    Common object for the tables values, user_values,
    in the database the object is save in two tables
    because it is expected that there will be much less user values than standard values

    A value is usually assigned to exact one phrase group, exceptions are time-series, geo-series or other phrase series values


    if the value is not used at all the adding of the new word is logged and the group change is updated without logging
    if the value is used, adding, changing or deleting a word creates a new value or updates an existing value
     and the logging is done according new value (add all words) or existing value (value modified by the user)

    The main sections of this object are
    - db const:          const for the database link
    - object vars:       the variables of this word object
    - construct and map: including the mapping of the db row to this word object
    - set and get:       to capsule the vars from unexpected changes
    - cast:              create an api object and set the vars from an api json
    - load:              database access object (DAO) functions
    - sql:               to create sql statements e.g. for load
    - sql fields:        field names for sql
    - information:       functions to make code easier to read
    - check:             functions to check the consistency
    - im- and export:    create an export object and set the vars from an import object
    - save:              manage to update the database
    - sql write fields:  field list for writing to the database


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

namespace cfg\value;

include_once MODEL_SANDBOX_PATH . 'sandbox_value.php';
include_once API_VALUE_PATH . 'value.php';
include_once DB_PATH . 'sql.php';
include_once DB_PATH . 'sql_creator.php';
include_once DB_PATH . 'sql_db.php';
include_once DB_PATH . 'sql_field_default.php';
include_once DB_PATH . 'sql_field_type.php';
include_once DB_PATH . 'sql_par.php';
include_once DB_PATH . 'sql_par_field_list.php';
include_once DB_PATH . 'sql_type.php';
include_once DB_PATH . 'sql_type_list.php';
include_once EXPORT_PATH . 'export.php';
include_once MODEL_FORMULA_PATH . 'expression.php';
include_once MODEL_FORMULA_PATH . 'figure.php';
include_once MODEL_GROUP_PATH . 'group.php';
include_once MODEL_GROUP_PATH . 'group_id.php';
include_once MODEL_LOG_PATH . 'change.php';
include_once MODEL_LOG_PATH . 'change_action.php';
include_once MODEL_LOG_PATH . 'change_field_list.php';
include_once MODEL_LOG_PATH . 'change_log.php';
include_once MODEL_LOG_PATH . 'change_table_list.php';
include_once MODEL_LOG_PATH . 'change_value.php';
include_once MODEL_LOG_PATH . 'change_values_big.php';
include_once MODEL_LOG_PATH . 'change_values_norm.php';
include_once MODEL_LOG_PATH . 'change_values_prime.php';
include_once MODEL_LOG_PATH . 'changes_big.php';
include_once MODEL_LOG_PATH . 'changes_norm.php';
include_once MODEL_PHRASE_PATH . 'phr_ids.php';
include_once MODEL_PHRASE_PATH . 'phrase.php';
include_once MODEL_PHRASE_PATH . 'phrase_list.php';
include_once MODEL_REF_PATH . 'source.php';
include_once MODEL_RESULT_PATH . 'result_list.php';
include_once MODEL_SANDBOX_PATH . 'sandbox.php';
include_once MODEL_SANDBOX_PATH . 'sandbox_multi.php';
include_once MODEL_SANDBOX_PATH . 'sandbox_value.php';
include_once MODEL_SYSTEM_PATH . 'job.php';
include_once MODEL_SYSTEM_PATH . 'job_type_list.php';
include_once MODEL_SYSTEM_PATH . 'log.php';
include_once MODEL_USER_PATH . 'user.php';
include_once MODEL_USER_PATH . 'user_message.php';
include_once WEB_VALUE_PATH . 'value.php';
include_once SHARED_TYPES_PATH . 'api_type_list.php';
include_once SHARED_TYPES_PATH . 'phrase_type.php';
include_once SHARED_TYPES_PATH . 'protection_type.php';
include_once SHARED_PATH . 'json_fields.php';
include_once SHARED_PATH . 'library.php';

use cfg\db\sql;
use cfg\db\sql_par_field_list;
use cfg\db\sql_type_list;
use cfg\formula\figure;
use cfg\log\change;
use cfg\log\change_values_big;
use cfg\log\change_values_norm;
use cfg\log\change_values_prime;
use cfg\log\changes_big;
use cfg\log\changes_norm;
use cfg\ref\source;
use cfg\sandbox\sandbox;
use cfg\sandbox\sandbox_multi;
use cfg\system\log;
use shared\json_fields;
use shared\types\api_type_list;
use shared\types\protection_type as protect_type_shared;
use api\value\value as value_api;
use cfg\db\sql_creator;
use cfg\db\sql_db;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
use cfg\db\sql_par;
use cfg\db\sql_type;
use cfg\export\export;
use cfg\formula\expression;
use cfg\group\group;
use cfg\group\group_id;
use cfg\system\job;
use cfg\system\job_type_list;
use cfg\log\change_action;
use cfg\log\change_field_list;
use cfg\log\change_log;
use cfg\log\change_table_list;
use cfg\log\change_value;
use cfg\phrase\phr_ids;
use cfg\phrase\phrase;
use cfg\phrase\phrase_list;
use cfg\result\result_list;
use cfg\sandbox\sandbox_value;
use cfg\user\user;
use cfg\user\user_message;
use html\value\value as value_dsp;
use shared\library;
use shared\types\phrase_type as phrase_type_shared;
use DateTime;
use Exception;
use math;

class value_base extends sandbox_value
{

    /*
     * db const
     */

    // object specific database and JSON object field names
    const FLD_ID = 'group_id';
    // TODO move the sandbox value object
    const FLD_VALUE_TEXT = 'text_value';
    const FLD_VALUE_TIME = 'time_value';
    const FLD_VALUE_GEO = 'geo_value';
    const FLD_TS_ID_COM = 'the id of the time series as a 64 bit integer value because the number of time series is not expected to be too high';
    const FLD_TS_ID_COM_USER = 'the 64 bit integer which is unique for the standard and the user series';
    const FLD_VALUE_TS_ID = 'value_time_series_id';
    const FLD_ALL_TIME_SERIES = array(
        [self::FLD_VALUE_TS_ID, sql_field_type::INT, sql_field_default::NOT_NULL, sql::INDEX, '', self::FLD_TS_ID_COM],
    );
    const FLD_ALL_TIME_SERIES_USER = array(
        [self::FLD_VALUE_TS_ID, sql_field_type::INT, sql_field_default::NOT_NULL, sql::INDEX, '', self::FLD_TS_ID_COM_USER],
    );

    // all database field names excluding the id and excluding the user specific fields
    const FLD_NAMES = array();
    const FLD_NAMES_STD = array(
        self::FLD_VALUE,
        source::FLD_ID,
    );
    // fields that are not part of the standard result table, but that needs to be included for a correct union field match
    const FLD_NAMES_STD_DUMMY = array(
        user::FLD_ID,
    );
    // list of the user specific numeric database field names
    const FLD_NAMES_NUM_USR_EX_STD = array(
        sandbox::FLD_EXCLUDED,
        sandbox::FLD_PROTECT
    );
    // list of the user specific datetime database field names
    const FLD_NAMES_DATE_USR_EX_STD = array(
        self::FLD_LAST_UPDATE
    );
    // list of the user specific numeric database field names
    const FLD_NAMES_NUM_USR = array(
        self::FLD_VALUE,
        source::FLD_ID,
        self::FLD_LAST_UPDATE,
        sandbox::FLD_EXCLUDED,
        sandbox::FLD_PROTECT
    );
    // list of the user specific database field names for time values
    const FLD_NAMES_NUM_USR_TIME = array(
        self::FLD_VALUE_TIME,
        source::FLD_ID,
        self::FLD_LAST_UPDATE,
        sandbox::FLD_EXCLUDED,
        sandbox::FLD_PROTECT
    );
    // list of the user specific database field names for text values
    const FLD_NAMES_USR_TEXT = array(
        self::FLD_VALUE_TEXT,
    );
    const FLD_NAMES_NUM_USR_TEXT = array(
        source::FLD_ID,
        self::FLD_LAST_UPDATE,
        sandbox::FLD_EXCLUDED,
        sandbox::FLD_PROTECT
    );
    // list of the user specific database field names for geo values
    const FLD_NAMES_USR_GEO = array(
        self::FLD_VALUE_GEO,
    );
    const FLD_NAMES_NUM_USR_GEO = array(
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
        sandbox::FLD_CHANGE_USER,
        sandbox::FLD_SHARE
    );
    // list of fixed tables where a value might be stored
    const TBL_LIST = array(
        [sql_type::PRIME, sql_type::STANDARD],
        [sql_type::MOST, sql_type::STANDARD],
        [sql_type::MOST],
        [sql_type::PRIME],
        [sql_type::BIG]
    );


    /*
     * object vars
     */

    // related database objects
    public ?source $source;    // the source object
    private string $symbol = '';               // the symbol of the related formula element

    // deprecated fields
    public ?DateTime $time_stamp = null;  // the time stamp for this value (if this is set, the time wrd is supposed to be empty and the value is saved in the time_series table)

    // field for user interaction
    public ?string $usr_value = null;     // the raw value as the user has entered it including formatting chars such as the thousand separator


    /*
     * construct and map
     */

    /**
     * set the user sandbox type for a value object and set the user, which is needed in all cases
     * @param user $usr the user who requested to see this value
     * @param float|DateTime|string|null $val the numeric or text value that should be set on creation
     * @param group|null $grp the phrases for unique identification of this value
     */
    function __construct(
        user                       $usr,
        float|DateTime|string|null $val = null,
        ?group                     $grp = null
    )
    {
        parent::__construct($usr);

        $this->rename_can_switch = UI_CAN_CHANGE_VALUE;

        $this->reset();

        if ($val !== null) {
            $this->set_value($val);
        }
        if ($grp != null) {
            $this->set_grp($grp);
        }
    }

    function reset(): void
    {
        parent::reset();

        $this->set_grp(new group($this->user()));
        $this->source = null;

        // deprecated fields
        $this->time_stamp = null;

        $this->set_last_update(null);
        $this->share_id = null;
        $this->protection_id = null;

        $this->usr_value = '';
    }

    /**
     * map the database fields to the object fields
     * for distributed tables where the data may be saved in more than one table
     *
     * @param array|null $db_row with the data directly from the database
     * @param string $ext the table type e.g. to indicate if the id is int
     * @param bool $load_std true if only the standard user sandbox object is loaded
     * @param bool $allow_usr_protect false for using the standard protection settings for the default object used for all users
     * @param string $id_fld the name of the id field as defined in this child and given to the parent
     * @param bool $one_id_fld false if the unique database id is based on more than one field
     * @return bool true if the value is loaded and valid
     */
    function row_mapper_sandbox_multi(
        ?array $db_row,
        string $ext,
        bool   $load_std = false,
        bool   $allow_usr_protect = true,
        string $id_fld = self::FLD_ID,
        bool   $one_id_fld = true
    ): bool
    {
        $lib = new library();

        // check if text group id field is given and filled
        $one_id_fld = false;
        if (array_key_exists($id_fld, $db_row)) {
            if ($db_row[$id_fld] != '' and $db_row[$id_fld] != 'null' and $db_row[$id_fld] != null) {
                $one_id_fld = true;
            }
        }

        if ($one_id_fld) {
            // if the value is not of prime or main type, use the text group id
            $id = $db_row[$id_fld];
            $grp = new group($this->user());
            $grp->set_phrase_list_by_id($id);
            $grp->set_id($id);
            $this->set_grp($grp);
        } else {
            // for prime and main values an array with the prime id fields is used
            $id_fld = $this->id_field();
            if (is_array($id_fld)) {
                $grp_id = new group_id();
                $phr_lst = new phrase_list($this->user());
                foreach ($id_fld as $fld_name) {
                    if (array_key_exists($fld_name, $db_row)) {
                        $id = $db_row[$fld_name];
                        if ($id != 0) {
                            $phr = new phrase($this->user(), $id);
                            $phr_lst->add($phr);
                        }
                    }
                }
                $grp = new group($this->user());
                $grp->set_id($grp_id->get_id($phr_lst));
                $grp->set_phrase_list($phr_lst);
                $this->set_grp($grp);
                $id_fld = $id_fld[0];
            }
        }
        $result = parent::row_mapper_sandbox_multi($db_row, $ext, $load_std, $allow_usr_protect, $id_fld, $one_id_fld);
        if ($result) {
            $this->set_value($db_row[self::FLD_VALUE]);
            // TODO check if phrase_group_id and time_word_id are user specific or time series specific
            $this->set_source_id($db_row[source::FLD_ID]);
            $this->set_last_update($lib->get_datetime($db_row[self::FLD_LAST_UPDATE]));
        }
        return $result;
    }


    /*
     * set and get
     */

    /**
     * set the unique database id of a database object
     * @param int|string $id used in the row mapper and to set a dummy database id for unit tests
     */
    function set_id(int|string $id): void
    {
        $this->id = $id;
        $this->grp()->set_id($id);
    }

    function id(): int|string
    {
        return $this->grp()->id();
    }

    function source(): source
    {
        return $this->source;
    }

    function source_id(): int
    {
        if ($this->source == null) {
            return 0;
        } else {
            return $this->source->id();
        }
    }

    function set_symbol(string $symbol): void
    {
        $this->symbol = $symbol;
    }

    function symbol(): string
    {
        return $this->symbol;
    }

    /**
     * map a value api json to this model value object
     * @param array $api_json the api array with the values that should be mapped
     */
    function set_by_api_json(array $api_json): user_message
    {
        global $shr_typ_cac;
        global $ptc_typ_cac;

        $usr_msg = new user_message();
        $lib = new library();

        // make sure that there are no unexpected leftovers but keep the user
        // TODO check that it is always moved to sandbox object
        // TODO use sand
        $usr = $this->user();
        $this->reset();
        $this->set_user($usr);

        foreach ($api_json as $key => $value) {

            if ($key == json_fields::ID) {
                $this->set_id($value);
            }

            if ($key == json_fields::PHRASES) {
                $phr_lst = new phrase_list($this->user());
                $usr_msg->add($phr_lst->set_by_api_json($value));
                if ($usr_msg->is_ok()) {
                    $this->grp()->set_phrase_list($phr_lst);
                }
            }

            if ($key == json_fields::TIMESTAMP) {
                if (strtotime($value)) {
                    $this->time_stamp = $lib->get_datetime($value, $this->dsp_id(), 'JSON import');
                } else {
                    $usr_msg->add_message('Cannot add timestamp "' . $value . '" when importing ' . $this->dsp_id());
                }
            }

            if ($key == json_fields::NUMBER) {
                if (is_numeric($value)) {
                    $this->set_value($value);
                } else {
                    $usr_msg->add_message('Import value: "' . $value . '" is expected to be a number (' . $this->grp()->dsp_id() . ')');
                }
            }

            if ($key == json_fields::SHARE) {
                $this->share_id = $shr_typ_cac->id($value);
            }

            if ($key == json_fields::PROTECTION) {
                $this->protection_id = $ptc_typ_cac->id($value);
                if ($value <> protect_type_shared::NO_PROTECT) {
                    $get_ownership = true;
                }
            }

            if ($key == json_fields::SOURCE_NAME) {
                $src = new source($this->user());
                $src->set_name($value);
                $this->source = $src;
            }

        }

        return $usr_msg;
    }

    /**
     * @return phrase_list the phrase list of the value
     */
    function phrase_list(): phrase_list
    {
        return $this->grp()->phrase_list();
    }

    /**
     * @return array with the ids of the phrases
     */
    function ids(): array
    {
        return $this->phrase_list()->ids();
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
        $api_obj->set_grp($this->grp()->api_obj());
        $api_obj->set_is_std($this->is_std());
        return $api_obj;
    }

    /**
     * @param api_type_list|array $typ_lst configuration for the api message e.g. if phrases should be included
     * @returns string the api json message for the object as a string
     */
    function api_json(api_type_list|array $typ_lst = []): string
    {
        if (is_array($typ_lst)) {
            $typ_lst = new api_type_list($typ_lst);
        }

        // null values are not needed in the api message to the frontend (but in the api message to the backend!)
        $vars = $this->api_json_array($typ_lst);
        $vars = array_filter($vars, fn($value) => !is_null($value) && $value !== '');

        return json_encode($vars);
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
     * load
     */

    /**
     * load a value by the phrase group
     * @param group $grp the id of the phrase group
     * @param bool $by_source set to true to force the selection e.g. by source phrase group id for results only
     * @return bool true if value has been found
     */
    function load_by_grp(group $grp, bool $by_source = false): bool
    {
        global $db_con;

        log_debug($grp->dsp_id());
        $qp = $this->load_sql_by_grp($db_con->sql_creator(), $grp);
        $id = $this->load_non_int_db_key($qp);

        // use the given phrase list
        if ($this->phr_lst()->is_empty() and !$grp->phrase_list()->is_empty()) {
            $this->set_grp($grp);
        } else {
            // ... or fill up the missing vars
            if ($this->phr_lst()->names() != $grp->phrase_list()->names()) {
                $this->phr_lst()->fill_by_id($grp->phrase_list());
            }
        }

        if ($id != 0) {
            return true;
        } else {
            return false;
        }
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
     * load one database row e.g. value or result from the database
     * where the prime key is not necessary and integer
     * @param sql_par $qp the query parameters created by the calling function
     * @return int|string the id of the object found and zero if nothing is found
     */
    protected function load_non_int_db_key(sql_par $qp): int|string
    {
        global $db_con;

        $db_row = $db_con->get1($qp);
        $this->row_mapper_sandbox_multi($db_row, $qp->ext);
        return $this->id();
    }

    /**
     * get the best matching value
     * 1. try to find a value with simply a different scaling e.g. if the number of share are requested, but this is in millions in the database use and scale it
     * 2. check if another measure type can be converted      e.g. if the share price in USD is requested, but only in EUR is in the database convert it
     *    e.g. for "ABB","Sales","2014" the value for "ABB","Sales","2014","million","CHF" will be loaded,
     *    because most values for "ABB", "Sales" are in ,"million","CHF"
     *
     * @param phrase_list $phr_lst with the phrases used for the selection
     */
    function load_best(phrase_list $phr_lst): void
    {
        log_debug('value->load_best for ' . $this->dsp_id());
        $grp = $phr_lst->get_grp_id();
        $this->load_by_grp($grp);
        // if not found try without scaling
        if (!$this->is_id_set()) {
            if (!$phr_lst->is_empty()) {
                log_err('No phrases found for ' . $this->dsp_id() . '.', 'value->load_best');
            } else {
                // try to get a value with another scaling
                $phr_lst_unscaled = clone $phr_lst;
                $phr_lst_unscaled->ex_scaling();
                log_debug('try unscaled with ' . $phr_lst_unscaled->dsp_id());
                $grp_unscale = $phr_lst_unscaled->get_grp_id();
                $this->load_by_grp($grp_unscale);
                // if not found try with converted measure
                if (!$this->is_id_set()) {
                    // try to get a value with another measure
                    $phr_lst_converted = clone $phr_lst_unscaled;
                    $phr_lst_converted->ex_measure();
                    log_debug('try converted with ' . $phr_lst_converted->dsp_id());
                    $grp_unscale = $phr_lst_converted->get_grp_id();
                    $this->grp()->set_id($grp_unscale->id());
                    $this->load_by_grp($grp_unscale);
                    // TODO:
                    // check if there are any matching values at all
                    // if yes, get the most often used phrase
                    // repeat adding a phrase utils a number is found
                }
            }
        }
        log_debug('got ' . $this->value() . ' for ' . $this->dsp_id());
    }

    /**
     * load the standard value use by most users for the given phrase group and time
     * @param sql_par|null $qp placeholder to align the function parameters with the parent
     * @return bool true if the standard value has been loaded
     */
    function load_standard(?sql_par $qp = null): bool
    {
        global $db_con;
        $qp = $this->load_standard_sql($db_con->sql_creator());
        return parent::load_standard($qp);
    }


    /*
     * sql
     */

    /**
     * create the common part of an SQL statement to retrieve the parameters of a value from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name extension to make the query name unique
     * @param string $class the name of the child class from where the call has been triggered
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @param string $ext the query name extension e.g. to differentiate queries based on 1,2, or more phrases
     * @param string $id_ext the query name extension that indicated how many id fields are used e.g. "_p1"
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_multi(
        sql_creator   $sc,
        string        $query_name,
        string        $class = self::class,
        sql_type_list $sc_par_lst = new sql_type_list(),
        string        $ext = '',
        string        $id_ext = ''
    ): sql_par
    {
        $qp = parent::load_sql_multi($sc, $query_name, $class, $sc_par_lst, $ext, $id_ext);

        // overwrite the standard id field name (value_id) with the main database id field for values "group_id"
        $sc->set_id_field($this->id_field($sc_par_lst));

        $sc->set_usr($this->user()->id());
        $sc->set_fields(self::FLD_NAMES);
        if ($this->is_numeric()) {
            $sc->set_usr_num_fields(self::FLD_NAMES_NUM_USR);
        } elseif ($this->is_time_value()) {
            $sc->set_usr_num_fields(self::FLD_NAMES_NUM_USR_TIME);
        } elseif ($this->is_text_value()) {
            $sc->set_usr_fields(self::FLD_NAMES_USR_TEXT);
            $sc->set_usr_num_fields(self::FLD_NAMES_NUM_USR_TEXT);
        } elseif ($this->is_geo_value()) {
            $sc->set_usr_fields(self::FLD_NAMES_USR_GEO);
            $sc->set_usr_num_fields(self::FLD_NAMES_NUM_USR_GEO);
        } else {
            // fallback option
            $sc->set_usr_num_fields(self::FLD_NAMES_NUM_USR);
        }
        $sc->set_usr_only_fields(self::FLD_NAMES_USR_ONLY);

        return $qp;
    }

    /**
     * create the SQL to load the single default value always by the id
     * @param sql_creator $sc with the target db_type set
     * @param array $fld_lst list of fields either for the value or the result
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_standard_sql(sql_creator $sc, array $fld_lst = []): sql_par
    {
        if ($this->is_numeric()) {
            $fld_lst = array_merge(
                self::FLD_NAMES,
                self::FLD_NAMES_NUM_USR,
                array(user::FLD_ID)
            );
        } elseif ($this->is_time_value()) {
            $fld_lst = array_merge(
                self::FLD_NAMES,
                self::FLD_NAMES_NUM_USR_TIME,
                array(user::FLD_ID)
            );
        } elseif ($this->is_text_value()) {
            $fld_lst = array_merge(
                self::FLD_NAMES,
                self::FLD_NAMES_USR_TEXT,
                self::FLD_NAMES_NUM_USR_TEXT,
                array(user::FLD_ID)
            );
        } elseif ($this->is_geo_value()) {
            $fld_lst = array_merge(
                self::FLD_NAMES,
                self::FLD_NAMES_USR_GEO,
                self::FLD_NAMES_NUM_USR_GEO,
                array(user::FLD_ID)
            );
        } else {
            // fallback option
            $fld_lst = array_merge(
                self::FLD_NAMES,
                self::FLD_NAMES_NUM_USR,
                array(user::FLD_ID)
            );
        }
        return parent::load_standard_sql($sc, $fld_lst);
    }


    /*
     * sql fields
     */

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
    function load_phrases(): void
    {
        log_debug();
        // loading via word group is the most used case, because to save database space and reading time the value is saved with the word group id
        if ($this->grp()->is_id_set()) {
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
        if ($this->grp()->is_id_set()) {
            // ... load the group related objects means the word and triple list
            $grp = new group($this->user()); // in case the word names and word links can be user specific maybe the owner should be used here
            $grp->load_by_id($this->grp()->id()); // to make sure that the word and triple object lists are loaded
            if ($grp->is_id_set()) {
                $this->set_grp($grp);
            }
        }

        // if a list object is missing
        if ($this->grp() != null) {
            $this->grp()->load_phrase_list();
        }

        log_debug('done');
    }



    /*
     * information
     */

    /**
     * create and return the description for this value
     * TODO check if $this->load_phrases() needs to be called before calling this function
     */
    function name(): string
    {
        $result = '';
        if ($this->grp() != null) {
            $result .= $this->grp()->name();
        }

        return $result;
    }

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
     * @param int|null $id the id of the source
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

    /**
     * @return phrase_list the phrase list of this value from the phrase group
     */
    function phr_lst(): phrase_list
    {
        return $this->phrase_list();
    }

    /**
     * @return array with the phrase names of this value from the phrase group
     */
    function phr_names(): array
    {
        return $this->phrase_list()->names();
    }


    /*
     * check
     */

    /**
     * check the data consistency of this user value
     * TODO move to test?
     * @return bool true if everything is fine
     */
    function check(): bool
    {
        $result = true;

        // reload the value by id
        $val_id = new value($this->user());
        $val_id->load_by_id($this->id());
        if (!$this->is_same_val($val_id)) {
            $result = false;
        }

        // reload the value by group
        log_debug('value->check id ' . $this->id() . ', for user ' . $this->user()->name);
        $val_grp = new value($this->user());
        $val_grp->load_by_grp($this->grp());
        if (!$this->is_same_val($val_grp)) {
            $result = false;
        }

        // reload the value by group
        /*
        log_debug('value->check load phrases');
        $val_phr = new value($this->user());
        $val_phr->load_by_phr_ids($this->phr_lst()->ids());
        if (!$this->is_same_val($val_phr)) {
            $result = false;
        }
        */

        log_debug('value->check done');
        return $result;
    }

    /**
     * check if the given value matches this value
     * TODO join with sandbox is_same and review
     *
     * @param value_base $val the value that should be checked
     * @return bool true if all parameters are the same
     */
    function is_same_val(value_base $val): bool
    {
        $result = true;
        if ($this->id() != $val->id()) {
            $result = false;
        }
        if ($this->user()->id() != $val->user()->id()) {
            $result = false;
        }
        if ($this->number() != $val->number()) {
            $result = false;
        }
        /*
         * TODO activate
        if ($this->phr_lst()->id() != $val->phr_lst()->id()) {
            $result = false;
        }
        */
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
        log_debug('value->scale ' . $this->value());
        // fallback value
        $result = $this->value();

        $lib = new library();

        $this->load_phrases();

        // check input parameters
        if (is_null($this->value())) {
            // this test should be done in the calling function if needed
            log_debug("To scale a value the number should not be empty.");
        } elseif (is_null($this->user()->id())) {
            log_warning("To scale a value the user must be defined.", "value->scale");
        } elseif ($this->phrase_list()->is_empty()) {
            log_warning("To scale a value the word list should be loaded by the calling method.", "value->scale");
        } else {
            log_debug($this->value() . ' for ' . $this->grp()->dsp_id() . ' (user ' . $this->user()->id() . ')');

            // if it has a scaling word, scale it to one
            if ($this->phrase_list()->has_scaling()) {
                log_debug('value words have a scaling words');
                // get any scaling words related to the value
                $scale_wrd_lst = $this->phrase_list()->scaling_lst();
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
                                            if ($res_wrd->is_type(phrase_type_shared::SCALING_HIDDEN)
                                                and $r_wrd->is_type(phrase_type_shared::SCALING)) {
                                                $wrd_symbol = expression::WORD_START . $r_wrd->id() . expression::WORD_END;
                                                log_debug('replace (' . $wrd_symbol . ' in ' . $r_part . ' with ' . $this->value() . ')');
                                                $r_part = str_replace($wrd_symbol, $this->value(), $r_part);
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
     * api
     */


    /**
     * create an array for the api json creation
     * differs from the export array by using the internal id instead of the names
     * @param api_type_list $typ_lst configuration for the api message e.g. if phrases should be included
     * @return array the filled array used to create the api json message to the frontend
     */
    function api_json_array(api_type_list $typ_lst): array
    {
        $vars = parent::api_json_array($typ_lst);

        // add the source
        if ($this->source != null) {
            $vars[json_fields::SOURCE] = $this->source->id();
        }

        return $vars;
    }

    /*
     * im- and export
     */

    /**
     * import a value from an external object
     * TODO import the description and save it in the group description
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
                    $this->set_grp($phr_grp);
                }
            }

            $result->add($this->set_fields_from_json($key, $value, $result, $do_save));

        }

        // save the value in the database
        if (!$test_obj) {
            if ($result->is_ok()) {
                $result->add($this->save());
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
        $usr_msg = new user_message();
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
            $usr_msg = $phr->get_or_add($phr_name);
        } else {
            $phr->set_name($phr_name);
        }

        if ($usr_msg->is_ok()) {
            $phr_lst->add($phr);
            $phr_grp = $phr_lst->get_grp_id($do_save);
            $this->set_grp($phr_grp);
            $this->set_value($value);

            // save the value in the database
            if ($do_save) {
                $usr_msg->add($this->save());
            }
        }

        return $usr_msg;
    }

    /**
     * create an array with the export json fields
     * @param bool $do_load to switch off the database load for unit tests
     * @return array the filled array used to create the user export json
     */
    function export_json(bool $do_load = true): array
    {
        $vars = parent::export_json($do_load);

        // add the source
        if ($this->source != null) {
            $vars[json_fields::SOURCE_NAME] = $this->source->name();
        }

        return $vars;
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
        $usr_msg = new user_message();

        foreach ($api_json as $key => $value) {

            if ($key == export::WORDS) {
                $grp = new group($this->user());
                $usr_msg->add($grp->save_from_api_msg($value, $do_save));
                if ($usr_msg->is_ok()) {
                    $this->set_grp($grp);
                }
            }

            $usr_msg->add($this->set_fields_from_json($key, $value, $usr_msg, $do_save));

        }

        if ($usr_msg->is_ok() and $do_save) {
            $usr_msg->add($this->save());
        }

        return $usr_msg;
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
        global $shr_typ_cac;
        global $ptc_typ_cac;
        $lib = new library();

        if ($key == json_fields::TIMESTAMP) {
            if (strtotime($value)) {
                $this->time_stamp = $lib->get_datetime($value, $this->dsp_id(), 'JSON import');
            } else {
                $msg->add_message('Cannot add timestamp "' . $value . '" when importing ' . $this->dsp_id());
            }
        }

        if ($key == json_fields::NUMBER) {
            if (is_numeric($value)) {
                $this->set_value($value);
            } else {
                $msg->add_message('Import value: "' . $value . '" is expected to be a number (' . $this->grp()->dsp_id() . ')');
            }
        }

        if ($key == json_fields::SHARE) {
            $this->share_id = $shr_typ_cac->id($value);
        }

        if ($key == json_fields::PROTECTION) {
            $this->protection_id = $ptc_typ_cac->id($value);
            if ($value <> protect_type_shared::NO_PROTECT) {
                $get_ownership = true;
            }
        }

        if ($key == json_fields::SOURCE_NAME) {
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
        $this->set_value(floatval($result));
        return $result;
    }


    /*
     * Select functions
     */

    /**
     * get a list of all formula results that are depending on this value
     * TODO: add a loop over the calculation if the are more formula results needs to be updated than defined with sql_db::ROW_MAX
     */
    function res_lst_depending(): result_list
    {
        log_debug('value->res_lst_depending group id "' . $this->grp()->id() . '" for user ' . $this->user()->name . '');
        $res_lst = new result_list($this->user());
        $res_lst->load_by_grp($this->grp(), true);

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
    3a) user a changes the original value -> the change is save in the original record -> user is still the owner
    3b) user a changes the original value to the same value as b -> the user specific record is removed -> user is still the owner
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
     * TODO switch to sql creator
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     *                 to check if the value has been changed
     */
    function not_changed_sql(sql_creator $sc): sql_par
    {
        $sc_par_lst = new sql_type_list();
        $sc_par_lst->add($this->table_type());
        $sc_par_lst->add($this->value_type());
        $sc->set_class($this::class, $sc_par_lst);
        $ext = $this->table_extension();
        return $sc->load_sql_not_changed_multi($this->id(), $this->owner_id, $this->id_field(), $ext, $sc_par_lst);
    }

    /**
     * true if no other user has modified the value
     */
    function not_changed(): bool
    {
        log_debug('value->not_changed id ' . $this->id() . ' by someone else than the owner (' . $this->owner_id . ')');

        global $db_con;
        $result = true;

        if (!$this->is_id_set()) {
            log_err('The id must be set to check if the formula has been changed');
        } else {
            $qp = $this->not_changed_sql($db_con->sql_creator());
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
     * TODO for the user config it is relevant which user has the user config
     *      so the target user id must be added (or not?)
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
                $ext = $this->table_extension();
                $db_con->set_class($class, true, $ext);
                $qp = $this->sql_insert($db_con->sql_creator(), new sql_type_list([sql_type::USER]));
                $usr_msg = $db_con->insert($qp, 'add user specific value');
                $result = $usr_msg->is_ok();
            }
        }
        return $result;
    }

    /**
     * set the log entry parameters for a value update
     * @return change_value actually a child object (prime, norm or big) with the parameters for this change
     */
    function log_upd(): change_value
    {
        log_debug('value->log_upd "' . $this->dsp_id());
        if ($this->is_prime()) {
            $log = new change_values_prime($this->user());
        } elseif ($this->is_big()) {
            $log = new change_values_big($this->user());
        } else {
            $log = new change_values_norm($this->user());
        }
        $log->set_action(change_action::UPDATE);
        if ($this->can_change()) {
            $log->set_table(change_table_list::VALUE);
        } else {
            $log->set_table(change_table_list::VALUE_USR);
        }
        $log->group_id = $this->grp_id();

        return $log;
    }

    /**
     * set the log entry parameters for value parameter updates
     * @return change actually a child object (prime, norm or big) with the parameters for this change
     */
    function log_update_parameter(): change
    {
        log_debug();
        if ($this->is_prime()) {
            $log = new change($this->user());
        } elseif ($this->is_big()) {
            $log = new changes_big($this->user());
        } else {
            $log = new changes_norm($this->user());
        }
        $log->set_action(change_action::UPDATE);
        if ($this->can_change()) {
            $log->set_table(change_table_list::VALUE);
        } else {
            $log->set_table(change_table_list::VALUE_USR);
        }
        $log->row_id = $this->grp_id();

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
    $log->new_value = null;
    $log->row_id    = $this->id();
    $log->add();

    return $log;
    }
    */

    /*
    // set the parameter for the log entry to link a word to value
    function log_add_link($wrd_id) {
    zu_debug('value->log_add_link word "'.$wrd_id.'" to value '.$this->id());
    $log = New user_log_link;
    $log->usr       = $this->user();
    $log->action    = user_log::ACTION_ADD;
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
        $val_wrd_id = $db_con->insert(array("group_id","phrase_id"), array($this->id(),$phr_id));
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
        $result = $db_con->delete(array("group_id","phrase_id"), array($this->id(),$wrd->id()));
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
        global $job_typ_cac;

        $result = '';

        $this->set_last_update(new DateTime());
        $ext = $this->grp()->table_extension();
        $db_con->set_class(self::class, false, $ext);
        $fvt_lst = new sql_par_field_list();
        $fvt_lst->add_field(value_base::FLD_LAST_UPDATE, sql::NOW, sql_field_type::TIME);
        $qp = $this->sql_update_fields($db_con->sql_creator(), $fvt_lst);
        try {
            $db_con->exe_par($qp);
        } catch (Exception $e) {
            $result = 'setting of value update trigger failed';
            $trace_link = log_err($result . log::MSG_ERR_USING . $qp->sql . log::MSG_ERR_BECAUSE . $e->getMessage());
        }
        log_debug('value->save_field_trigger_update timestamp of ' . $this->id() . ' updated to "' . $this->last_update()->format('Y-m-d H:i:s') . '"');

        // trigger the batch job
        // save the pending update to the database for the batch calculation
        log_debug('value->save_field_trigger_update group id "' . $this->grp()->id() . '" for user ' . $this->user()->name . '');
        if ($this->is_id_set()) {
            $job = new job($this->user());
            $job->set_type(job_type_list::VALUE_UPDATE);
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
    function save_field_number(sql_db $db_con, value_base $db_rec, value_base $std_rec): string
    {
        $result = '';
        $updated = false;
        if ($this->is_numeric()) {
            if ($db_rec->number() <> $this->number()) {
                $log = $this->log_upd();
                $log->old_value = $db_rec->number();
                $log->new_value = $this->number();
                $log->std_value = $std_rec->number();
                $this->save_set_log_id($log);
                $log->set_field($this::FLD_VALUE);
                $result .= $this->save_field_user($db_con, $log);
                $updated = true;
            }
        } elseif ($this->is_time_value()) {
            if ($db_rec->value() <> $this->value()) {
                $log = $this->log_upd();
                $log->old_value = $db_rec->value();
                $log->new_value = $this->value();
                $log->std_value = $std_rec->value();
                $this->save_set_log_id($log);
                $log->set_field($this::FLD_VALUE);
                $result .= $this->save_field_user($db_con, $log);
                $updated = true;
            }
        } elseif ($this->is_text_value()) {
            if ($db_rec->value() <> $this->value()) {
                $log = $this->log_upd();
                $log->old_value = $db_rec->value();
                $log->new_value = $this->value();
                $log->std_value = $std_rec->value();
                $this->save_set_log_id($log);
                $log->set_field(self::FLD_VALUE_TEXT);
                $result .= $this->save_field_user($db_con, $log);
                $updated = true;
            }
        } elseif ($this->is_geo_value()) {
            if ($db_rec->value() <> $this->value()) {
                $log = $this->log_upd();
                $log->old_value = $db_rec->value();
                $log->new_value = $this->value();
                $log->std_value = $std_rec->value();
                $this->save_set_log_id($log);
                $log->set_field(self::FLD_VALUE_GEO);
                $result .= $this->save_field_user($db_con, $log);
                $updated = true;
            }
        } else {
            if ($db_rec->number() <> $this->number()) {
                $log = $this->log_upd();
                $log->old_value = $db_rec->number();
                $log->new_value = $this->number();
                $log->std_value = $std_rec->number();
                $this->save_set_log_id($log);
                $log->set_field(self::FLD_VALUE);
                $result .= $this->save_field_user($db_con, $log);
            }
        }
        if ($updated) {
            // updating the number is definitely relevant for calculation, so force to update the timestamp
            log_debug('trigger update');
            $result .= $this->save_field_trigger_update($db_con);
        }
        return $result;
    }

    /**
     * set the update parameters for the source link
     */
    function save_field_source(sql_db $db_con, value_base $db_rec, value_base $std_rec): string
    {
        $result = '';
        if ($db_rec->get_source_id() <> $this->get_source_id()) {
            $log = $this->log_update_parameter();
            $log->old_value = $db_rec->source_name();
            $log->old_id = $db_rec->get_source_id();
            $log->new_value = $this->source_name();
            $log->new_id = $this->get_source_id();
            $log->std_value = $std_rec->source_name();
            $log->std_id = $std_rec->get_source_id();
            $this->save_set_log_id($log);
            $log->set_field(source::FLD_ID);
            $result = $this->save_field_user($db_con, $log);
        }
        return $result;
    }

    /**
     * set to row id for the log
     * @param change_value|change_log $log
     * @return void
     */
    function save_set_log_id(change_value|change_log $log): void
    {
        $id = $this->id();
        if (is_string($id)) {
            $log->group_id = $id;
        } else {
            $log->row_id = $id;
        }
    }

    /**
     * save the value number and the source
     * TODO combine the log and update sql to one statement
     *
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @param value_base|sandbox_multi $db_rec the database record before the saving
     * @param value_base|sandbox_multi $std_rec the database record defined as standard because it is used by most users
     * @return string if not empty the message that should be shown to the user
     */
    function save_fields(sql_db $db_con, value_base|sandbox_multi $db_rec, value_base|sandbox_multi $std_rec): string
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
    function save_id_fields(sql_db $db_con, value_base|sandbox_multi $db_rec, value_base|sandbox_multi $std_rec): string
    {
        log_debug('value->save_id_fields');
        $result = '';

        // to load any missing objects
        $db_rec->load_phrases();
        $this->load_phrases();
        $std_rec->load_phrases();

        if ($db_rec->grp()->id() <> $this->grp()->id()) {
            log_debug('value->save_id_fields to ' . $this->dsp_id() . ' from "' . $db_rec->dsp_id() . '" (standard ' . $std_rec->dsp_id() . ')');

            $log = $this->log_upd();
            if ($db_rec->grp() != null) {
                $log->old_value = $db_rec->grp()->name();
            }
            if ($this->grp() != null) {
                $log->new_value = $this->grp()->name();
            }
            if ($std_rec->grp() != null) {
                $log->std_value = $std_rec->grp()->name();
            }
            $log->old_id = $db_rec->grp()->id();
            $log->new_id = $this->grp()->id();
            $log->std_id = $std_rec->grp()->id();
            $log->row_id = $this->id();
            $log->set_field(change_field_list::FLD_VALUE_GROUP);
            if ($log->add()) {
                $ext = $this->grp()->table_extension();
                $db_con->set_class(self::class, false, $ext);
                $result = $db_con->update_old($this->id(),
                    array(group::FLD_ID),
                    array($this->grp()->id()));
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
            $result .= $db_con->update($this->id(), array("time_stamp"),
                                                  array($this->time_stamp));
          }
        }
        */

        return $result;
    }

    /**
     * check if the id parameters are supposed to be changed
     */
    function save_id_if_updated(
        sql_db                   $db_con,
        sandbox_multi|value_base $db_rec,
        sandbox_multi|value_base $std_rec,
        ?bool                    $use_func = null
    ): string
    {
        log_debug('value->save_id_if_updated has name changed from "' . $db_rec->dsp_id() . '" to "' . $this->dsp_id() . '"');
        $result = '';

        // if the phrases or time has changed, check if value with the same phrases/time already exists
        if ($db_rec->grp()->id() <> $this->grp()->id()
            or $db_rec->time_stamp <> $this->time_stamp) {
            // check if a value with the same phrases/time is already in the database
            $chk_val = new value($this->user());
            //$chk_val->time_phr = $this->time_phr;
            //$chk_val->time_stamp = $this->time_stamp;
            $chk_val->load_by_grp($this->grp());
            log_debug('value->save_id_if_updated check value loaded');
            if ($chk_val->is_id_set()) {
                // TODO if the target value is already in the database combine the user changes with this values
                // $this->id() = $chk_val->id();
                // $result .= $this->save()->get_last_message();
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
                    // ... and create a deletion request for all users ???

                    // ... and create a new display component link
                    $this->set_id(0);
                    $this->owner_id = $this->user()->id();
                    $result .= $this->add($use_func)->get_last_message();
                    log_debug('value->save_id_if_updated recreate the value "' . $db_rec->dsp_id() . '" as ' . $this->dsp_id() . ' (standard "' . $std_rec->dsp_id() . '")');
                }
            }
        } else {
            log_debug('value->save_id_if_updated no id field updated (group ' . $db_rec->grp()->id() . '=' . $this->grp()->id() . ')');
        }

        log_debug('value->save_id_if_updated for ' . $this->dsp_id() . ' has been done');
        return $result;
    }

    /**
     * add a new value
     * @param bool|null $use_func if true a predefined function is used that also creates the log entries
     * @return user_message with status ok
     *                      or if something went wrong
     *                      the message that should be shown to the user
     *                      including suggested solutions
     */
    function add(?bool $use_func = null): user_message
    {
        log_debug();

        global $db_con;
        $usr_msg = new user_message();

        // log the insert attempt first
        $log = $this->log_add();
        if ($log->id() > 0) {
            // insert the value
            $ins_result = $db_con->insert($this->sql_insert($db_con->sql_creator()), 'add value');
            // the id of value is the given group id not a sequence
            //if ($ins_result->has_row()) {
            //    $this->set_id($ins_result->get_row_id());
            //}
            //$db_con->set_type(self::class);
            //$this->set_id($db_con->insert(array(group::FLD_ID, user::FLD_ID, self::FLD_VALUE, self::FLD_LAST_UPDATE), array($this->grp()->id(), $this->user()->id, $this->number, sql::NOW)));
            if ($this->is_id_set()) {
                // update the reference in the log
                if ($this->grp()->is_prime()) {
                    if (!$log->add_ref($this->id())) {
                        $usr_msg->add_message('adding the value reference in the system log failed');
                    }
                } else {
                    // TODO: save in the value or value big change log
                    $log = $this->log_add_value();
                }

                // update the phrase links for fast searching
                /*
                $upd_result = $this->upd_phr_links();
                if ($upd_result != '') {
                    $result->add_message('Adding the phrase links of the value failed because ' . $upd_result);
                    $this->set_id(0);
                }
                */

                if ($this->is_id_set()) {
                    // create an empty db_rec element to force saving of all set fields
                    $db_val = new value($this->user());
                    $db_val->set_id($this->id());
                    $db_val->set_value($this->value()); // ... but not the field saved already with the insert
                    $std_val = clone $db_val;
                    // save the value fields
                    $usr_msg->add_message($this->save_fields($db_con, $db_val, $std_val));
                }

            } else {
                $usr_msg->add_message("Adding value " . $this->id() . " failed.");
            }
        }

        return $usr_msg;
    }

    /**
     * insert or update a number in the database or save a user specific number
     * @param bool|null $use_func if true a predefined function is used that also creates the log entries
     * @return user_message in case of a problem the message that should be shown to the user
     */
    function save(?bool $use_func = null): user_message
    {
        log_debug();

        global $db_con;
        $usr_msg = new user_message();

        // check if a new value is supposed to be added or updated
        // TODO combine this db call with the add or update to one SQL sequence with one commit at the end
        if (!$this->is_saved()) {
            log_debug('check if a value ' . $this->dsp_id() . ' is already in the database');
            // check if a value for these phrases is already in the database
            $db_chk = new value($this->user());
            $db_chk->load_by_id($this->grp()->id());
            if ($db_chk->is_saved()) {
                $this->set_last_update($db_chk->last_update());
            }
        }

        if (!$this->is_saved()) {
            log_debug('add ' . $this->dsp_id());
            $usr_msg->add($this->add($use_func));
        } else {
            log_debug('update id ' . $this->id() . ' to save "' . $this->value() . '" for user ' . $this->user()->id());
            // update a value
            // TODO: if no one else has ever changed the value, change to default value, else create a user overwrite

            // read the database value to be able to check if something has been changed
            // done first, because it needs to be done for user and general values
            $db_rec = new value($this->user());
            $db_rec->load_by_id($this->grp()->id());
            log_debug("old database value loaded (" . $db_rec->value() . ") with group " . $db_rec->grp()->id() . ".");
            $std_rec = new value($this->user()); // user must also be set to allow to take the ownership
            $std_rec->set_grp($this->grp());
            $std_rec->load_standard();
            log_debug("standard value settings loaded (" . $std_rec->value() . ")");

            // for a correct user value detection (function can_change) set the owner even if the value has not been loaded before the save
            if ($this->owner_id <= 0) {
                $this->owner_id = $std_rec->owner_id;
            }

            // check if the id parameters are supposed to be changed
            if ($usr_msg->is_ok()) {
                $usr_msg->add_message($this->save_id_if_updated($db_con, $db_rec, $std_rec, $use_func));
            }

            // if a problem has appeared up to here, don't try to save the values
            // the problem is shown to the user by the calling interactive script
            // TODO add db write via function
            if ($usr_msg->is_ok()) {
                // if the user is the owner and no other user has adjusted the value, really delete the value in the database
                $usr_msg->add_message($this->save_fields($db_con, $db_rec, $std_rec));
            }

        }

        if (!$usr_msg->is_ok()) {
            log_err($usr_msg->get_last_message());
        }

        return $usr_msg;
    }


    /*
     * sql write fields
     */

    /**
     * get a list of database fields that might be used to create an sql insert or update statement
     *
     * @return array list of the database field names that have been updated
     */
    function db_fields_all(sql_type_list $sc_par_lst = new sql_type_list()): array
    {
        $fields = parent::db_fields_all();
        if (!$sc_par_lst->is_standard()) {
            $fields[] = source::FLD_ID;
            $fields = array_merge($fields, $this->db_fields_all_sandbox());
        }
        return $fields;
    }

    /**
     * get a list of database field names, values and types that have been updated
     * the last_update field is excluded here because this is an internal only field
     *
     * @param sandbox_multi|sandbox_value|value_base $sbx the same value sandbox as this to compare which fields have been changed
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par_field_list with the field names of the object and any child object
     */
    function db_fields_changed(
        sandbox_multi|sandbox_value|value_base $sbx,
        sql_type_list                          $sc_par_lst = new sql_type_list()
    ): sql_par_field_list
    {
        global $cng_fld_cac;
        $sc = new sql_creator();
        $table_id = $sc->table_id($this::class);

        $lst = parent::db_fields_changed($sbx, $sc_par_lst);

        if (!$sc_par_lst->is_standard()) {
            if ($sbx->source_id() <> $this->source_id()) {
                if ($sc_par_lst->incl_log()) {
                    $lst->add_field(
                        sql::FLD_LOG_FIELD_PREFIX . source::FLD_ID,
                        $cng_fld_cac->id($table_id . source::FLD_ID),
                        change::FLD_FIELD_ID_SQL_TYP
                    );
                }
                $lst->add_field(
                    source::FLD_ID,
                    $this->source_id(),
                    sql_field_type::INT
                );
            }
        }
        return $lst->merge($this->db_changed_sandbox_list($sbx, $sc_par_lst));
    }

}