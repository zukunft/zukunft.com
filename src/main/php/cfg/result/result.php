<?php

/*

    model/formula/result.php - the calculated numeric result of a formula
    -------------------------------

    TODO: add these function
    TODO rename to result
    TODO create a separate table for the time series results

    set_dirty_on_value_update  - set all formula result value to dirty that are depending on an updated values via Apache Kafka messages not via database
    set_dirty_on_result_update - set all formula result value to dirty that are depending on an updated formula result
    set_cleanup_prios          - define which formula results needs to be updated first
    cleanup                    - update/calculated all dirty formula results
                               do the cleanup calculations always "in memory"
                               drop the results in blocks to the database



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

namespace cfg\result;

include_once MODEL_SANDBOX_PATH . 'sandbox_value.php';
include_once DB_PATH . 'sql_par_type.php';
include_once DB_PATH . 'sql_type.php';
include_once SERVICE_EXPORT_PATH . 'result_exp.php';

use api\result\result as result_api;
use cfg\db\sql;
use cfg\db\sql_db;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
use cfg\db\sql_par;
use cfg\db\sql_par_field_list;
use cfg\db\sql_type;
use cfg\db\sql_type_list;
use cfg\element_list;
use cfg\export\export;
use cfg\export\result_exp;
use cfg\export\sandbox_exp;
use cfg\expression;
use cfg\figure;
use cfg\formula;
use cfg\group\group;
use cfg\group\group_id;
use cfg\group\group_list;
use cfg\parameter_type;
use cfg\phrase_list;
use cfg\sandbox;
use cfg\sandbox_value;
use cfg\user;
use cfg\user_message;
use cfg\value\value;
use DateTime;
use html\formula\formula as formula_dsp;
use html\html_base;
use shared\library;

class result extends sandbox_value
{

    /*
     * database link
     */

    // database fields only used for results
    const FLD_ID = 'group_id';
    const FLD_SOURCE = 'source_';
    // TODO replace with result::FLD_SOURCE . group::FLD_ID
    const FLD_SOURCE_GRP = 'source_group_id';
    // TODO replace with group::FLD_ID
    const FLD_GRP = 'group_id';
    const FLD_VALUE = 'numeric_value';
    const FLD_LAST_UPDATE = 'last_update';
    const FLD_TS_ID_COM = 'the id of the time series as a 64 bit integer value because the number of time series is not expected to be too high';
    const FLD_TS_ID_COM_USER = 'the 64 bit integer which is unique for the standard and the user series';
    const FLD_RESULT_TS_ID = 'result_time_series_id';
    const FLD_DIRTY = 'dirty';
    const FLD_ALL_TIME_SERIES = array(
        [self::FLD_RESULT_TS_ID, sql_field_type::INT, sql_field_default::NOT_NULL, sql::INDEX, '', self::FLD_TS_ID_COM],
    );
    const FLD_ALL_TIME_SERIES_USER = array(
        [self::FLD_RESULT_TS_ID, sql_field_type::INT, sql_field_default::NOT_NULL, sql::INDEX, '', self::FLD_TS_ID_COM_USER],
    );

    // all database field names excluding the id and excluding the user specific fields
    const FLD_NAMES = array(
        formula::FLD_ID,
        user::FLD_ID,
        self::FLD_SOURCE_GRP,
        self::FLD_VALUE,
        self::FLD_LAST_UPDATE
    );
    const FLD_NAMES_ALL = array(
        user::FLD_ID,
        self::FLD_SOURCE_GRP,
        formula::FLD_ID,
        self::FLD_VALUE,
    );
    const FLD_NAMES_NON_STD = array(
        user::FLD_ID,
        self::FLD_SOURCE_GRP,
        formula::FLD_ID,
    );
    const FLD_NAMES_STD = array(
        self::FLD_SOURCE_GRP,
        formula::FLD_ID,
        self::FLD_VALUE,
    );
    // fields that are not part of the standard result table, but that needs to be included for a correct union field match
    const FLD_NAMES_STD_DUMMY = array(
        user::FLD_ID,
        self::FLD_SOURCE_GRP,
    );
    const FLD_NAMES_STD_NON_DUMMY = array(
        formula::FLD_ID,
    );
    const FLD_NAMES_DUMMY = array(
        user::FLD_ID,
        self::FLD_SOURCE_GRP,
        formula::FLD_ID,
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
        self::FLD_LAST_UPDATE,
        sandbox::FLD_EXCLUDED,
        sandbox::FLD_PROTECT
    );
    // list of field names that are only on the user sandbox row
    // e.g. the standard result does not need the share type, because it is by definition public
    // (even if share types within a group of users needs to be defined,
    // the value for the user group are also user sandbox table)
    const FLD_NAMES_USR_ONLY = array(
        sandbox::FLD_CHANGE_USER,
        sandbox::FLD_SHARE
    );

    // database table extensions used
    // TODO add a similar list to the value class
    const TBL_EXT_LST = array(
        sql_type::PRIME,
        sql_type::MAIN,
        sql_type::MOST,
        sql_type::BIG
    );
    // list of fixed tables where a value might be stored
    const TBL_LIST = array(
        [sql_type::PRIME, sql_type::STANDARD],
        [sql_type::MAIN, sql_type::STANDARD],
        [sql_type::MOST, sql_type::STANDARD],
        [sql_type::MOST],
        [sql_type::PRIME],
        [sql_type::MAIN],
        [sql_type::BIG]
    );
    // list of fixed tables without the pure key value tables
    const TBL_LIST_EX_STD = array(
        [sql_type::MOST],
        [sql_type::PRIME],
        [sql_type::MAIN],
        [sql_type::BIG]
    );

    const FLD_KEY_PRIME = array(
        [formula::FLD_ID, sql_field_type::KEY_PART_INT_SMALL, sql_field_default::NOT_NULL, sql::INDEX, '', 'formula id that is part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '1', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::NOT_NULL, sql::INDEX, '', 'phrase id that is part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '2', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::ZERO, sql::INDEX, '', 'phrase id that is part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '3', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::ZERO, sql::INDEX, '', 'phrase id that is part of the prime key for a'],
    );
    const FLD_KEY_MAIN_STD = array(
        [formula::FLD_ID, sql_field_type::KEY_PART_INT_SMALL, sql_field_default::NOT_NULL, sql::INDEX, '', 'formula id that is part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '1', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::NOT_NULL, sql::INDEX, '', 'phrase id that is part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '2', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::ZERO, sql::INDEX, '', 'phrase id that is part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '3', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::ZERO, sql::INDEX, '', 'phrase id that is part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '4', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::ZERO, sql::INDEX, '', 'phrase id that is part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '5', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::ZERO, sql::INDEX, '', 'phrase id that is part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '6', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::ZERO, sql::INDEX, '', 'phrase id that is part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '7', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::ZERO, sql::INDEX, '', 'phrase id that is part of the prime key for a'],
    );
    const FLD_KEY_MAIN = array(
        [sandbox_value::FLD_ID_PREFIX . '1', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::NOT_NULL, sql::INDEX, '', 'phrase id that is part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '2', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::ZERO, sql::INDEX, '', 'phrase id that is part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '3', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::ZERO, sql::INDEX, '', 'phrase id that is part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '4', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::ZERO, sql::INDEX, '', 'phrase id that is part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '5', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::ZERO, sql::INDEX, '', 'phrase id that is part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '6', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::ZERO, sql::INDEX, '', 'phrase id that is part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '7', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::ZERO, sql::INDEX, '', 'phrase id that is part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '8', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::ZERO, sql::INDEX, '', 'phrase id that is part of the prime key for a'],
    );
    const FLD_KEY_PRIME_USER = array(
        [formula::FLD_ID, sql_field_type::KEY_PART_INT_SMALL, sql_field_default::NOT_NULL, sql::INDEX, '', 'formula id that is with the user id part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '1', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::NOT_NULL, sql::INDEX, '', 'phrase id that is with the user id part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '2', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::ZERO, sql::INDEX, '', 'phrase id that is with the user id part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '3', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::ZERO, sql::INDEX, '', 'phrase id that is with the user id part of the prime key for a'],
    );
    const FLD_KEY_MAIN_USER = array(
        [sandbox_value::FLD_ID_PREFIX . '1', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::NOT_NULL, sql::INDEX, '', 'phrase id that is with the user id part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '2', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::ZERO, sql::INDEX, '', 'phrase id that is with the user id part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '3', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::ZERO, sql::INDEX, '', 'phrase id that is with the user id part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '4', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::ZERO, sql::INDEX, '', 'phrase id that is with the user id part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '5', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::ZERO, sql::INDEX, '', 'phrase id that is with the user id part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '6', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::ZERO, sql::INDEX, '', 'phrase id that is with the user id part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '7', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::ZERO, sql::INDEX, '', 'phrase id that is with the user id part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '8', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::ZERO, sql::INDEX, '', 'phrase id that is with the user id part of the prime key for a'],
    );
    const FLD_ALL_CHANGED = array(
        [value::FLD_LAST_UPDATE, sql_field_type::TIME, sql_field_default::NULL, '', '', 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation'],
        [formula::FLD_ID, sql_field_type::INT, sql_field_default::NOT_NULL, sql::INDEX, formula::class, 'the id of the formula which has been used to calculate this result'],
    );
    const FLD_ALL_SOURCE = array();
    const FLD_ALL_SOURCE_GROUP = array(
        [self::FLD_SOURCE . group::FLD_ID, sql_field_type::REF_512, sql_field_default::NULL, sql::INDEX, '', '512-bit reference to the sorted phrase list used to calculate this result'],
    );
    const FLD_ALL_SOURCE_GROUP_PRIME = array(
        [self::FLD_SOURCE . group::FLD_ID, sql_field_type::INT, sql_field_default::NULL, sql::INDEX, '', '64-bit reference to the sorted phrase list used to calculate this result'],
    );
    const FLD_ALL_SOURCE_GROUP_BIG = array(
        [self::FLD_SOURCE . group::FLD_ID, sql_field_type::TEXT, sql_field_default::NULL, sql::INDEX, '', 'text reference to the sorted phrase list used to calculate this result'],
    );
    const FLD_ALL_OWNER = array(
        [user::FLD_ID, sql_field_type::INT, sql_field_default::NULL, sql::INDEX, user::class, 'the id of the user who has requested the calculation'],
    );
    const FLD_ALL_CHANGER = array(
        [user::FLD_ID, sql_field_type::KEY_PART_INT, sql_field_default::NOT_NULL, sql::INDEX, user::class, 'the id of the user who has requested the change of the '],
    );

    const TBL_COMMENT = 'to cache the formula ';
    const TBL_COMMENT_PRIME = 'to cache the formula most often requested ';
    const TBL_COMMENT_STD = 'to cache the formula public unprotected ';
    const TBL_COMMENT_USER = 'to cache the user specific changes of ';


    /*
     * object vars
     */

    // database fields
    public ?group $src_grp = null;      // the phrase group used that selected the numbers to calculate this result
    public formula $frm;                // the formula object used to calculate this result
    public group $grp;                  // the phrase group of the result
    // TODO use the value of the sandbox_value object
    public ?float $value = null;        // ... and finally the numeric value
    public ?bool $is_std = True;        // true as long as no user specific value, formula or assignment is used for this result

    // to deprecate
    public ?DateTime $last_update = null;      // ... and the time of the last update; all updates up to this time are included in this result


    // in memory only fields (all methods except load and save should use the wrd_lst object not the ids and not the group id)
    public ?bool $val_missing = False;         // true if at least one of the results is not set which means is NULL (but zero is a value)
    public ?bool $is_updated = False;          // true if the result has been calculated, but not yet saved
    public ?string $ref_text = null;           // the formula text in the database reference format on which the result is based
    public ?string $num_text = null;           // the formula text filled with numbers used for the result calculation
    public ?DateTime $last_val_update = null;  // the time of the last update of an underlying value, formula result or formula
    //                                            if this is later than the last update the result needs to be updated
    private string $symbol = '';               // the symbol of the related formula element


    /*
     * construct and map
     */

    function __construct(user $usr)
    {
        parent::__construct($usr);
        $this->last_update = new DateTime();
        $this->last_val_update = new DateTime();
        $this->reset();
    }

    function reset(): void
    {
        $this->frm = new formula($this->user());
        $this->grp = new group($this->user());
        $this->src_grp = new group($this->user());
        $this->set_id(0);
    }

    /**
     * map the database fields to the object fields
     *
     * @param array|null $db_row with the data directly from the database
     * @param string $ext the table type e.g. to indicate if the id is int
     * @param string $id_fld the name of the id field as set in the child class
     * @param bool $one_id_fld false if the unique database id is based on more than one field and due to that the database id should not be used for the object id
     * @return bool true if a result has been loaded and is valid
     */
    function row_mapper_multi(?array $db_row, string $ext, string $id_fld = '', bool $one_id_fld = true): bool
    {
        $lib = new library();
        $result = parent::row_mapper_multi($db_row, $ext, self::FLD_ID);
        if ($result) {
            $this->frm->set_id($db_row[formula::FLD_ID]);
            if (substr($ext, 0, 2) == group_id::TBL_EXT_PHRASE_ID) {
                $this->src_grp->set_id((int)$db_row[self::FLD_SOURCE_GRP]);
            } else {
                $this->src_grp->set_id($db_row[self::FLD_SOURCE_GRP]);
            }
            $this->value = $db_row[self::FLD_VALUE];
            $this->owner_id = $db_row[user::FLD_ID];
            $this->last_update = $lib->get_datetime($db_row[self::FLD_LAST_UPDATE]);
            $this->last_val_update = $lib->get_datetime($db_row[self::FLD_LAST_UPDATE]);

            $this->load_phrases(true);
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

    function src_grp_id(): int|string
    {
        return $this->src_grp->id();
    }

    function set_grp(group $grp): void
    {
        $this->grp = $grp;
        $this->set_id($grp->id());
    }

    function frm_id(): int
    {
        return $this->frm->id();
    }

    function grp(): group
    {
        return $this->grp;
    }

    function set_symbol(string $symbol): void
    {
        $this->symbol = $symbol;
    }

    function symbol(): string
    {
        return $this->symbol;
    }

    function is_std(): bool
    {
        return $this->is_std;
    }

    function number(): ?float
    {
        return $this->value;
    }

    function last_update(): DateTime
    {
        return $this->last_update;
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
     * cast
     */

    /**
     * @return result_api the formula result frontend api object
     */
    function api_obj(bool $do_save = true): object
    {
        $api_obj = new result_api($this->id);
        $api_obj->set_number($this->value);
        if ($this->grp->phrase_list() != null) {
            $grp = $this->grp->phrase_list()->get_grp_id($do_save);
            $api_obj->set_grp($grp->api_obj());
        }
        return $api_obj;
    }

    /**
     * @returns string the api json message for the object as a string
     */
    function api_json(bool $do_save = true): string
    {
        return $this->api_obj($do_save)->get_json();
    }


    /*
     * load
     */

    /**
     * create the SQL to load the single default result always by the id
     * @param sql $sc with the target db_type set
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_standard_sql(sql $sc, string $class = self::class): sql_par
    {
        $tbl_typ = $this->grp->table_type();
        $ext = $this->grp->table_extension();
        $qp = new sql_par($class, new sql_type_list([sql_type::NORM]), $ext . sql_type::NORM->extension());
        $qp->name .= sql_db::FLD_ID;
        $sc->set_class($class, new sql_type_list([]), $tbl_typ->extension());
        $sc->set_name($qp->name);
        $sc->set_id_field($this->id_field());
        $sc->set_fields(array_merge(self::FLD_NAMES, array(user::FLD_ID)));

        return $this->load_sql_set_where($qp, $sc, $ext);
        // TODO check which parts can be move to a parent class
        //return parent::load_standard_sql($sc, $class);
    }

    /**
     * fill the sql creator with the parameter the SQL to load results
     * from one of the tables with results
     *
     * @param sql $sc with the target db_type set
     * @param string $query_name the unique name of the query e.g. id or name
     * @param string $class the name of the child class from where the call has been triggered
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @param string $ext the query name extension e.g. to differentiate queries based on 1,2, or more phrases
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_multi(
        sql      $sc,
        string   $query_name,
        string   $class = self::class,
        sql_type_list    $sc_par_lst = new sql_type_list([]),
        string   $ext = ''
    ): sql_par
    {
        $qp = parent::load_sql_multi($sc, $query_name, $class, $sc_par_lst, $ext);

        // overwrite the standard id field name (result_id) with the main database id field for results "group_id"
        $sc->set_id_field($this->id_field());
        $sc->set_name($qp->name);
        $sc->set_usr($this->user()->id());
        $sc->set_fields(self::FLD_NAMES);

        return $qp;
    }

    /**
     * create the SQL to load a results by the id
     * added to value just to assign the class for the user sandbox object
     *
     * @param sql $sc with the target db_type set
     * @param int|string $id the id of the result
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_id(sql $sc, int|string $id, string $class = self::class): sql_par
    {
        return parent::load_sql_by_id($sc, $id, $class);
    }

    /**
     * create the SQL to load a results by phrase group id
     *
     * @param sql $sc with the target db_type set
     * @param group $grp the group used for the selection
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_grp(sql $sc, group $grp, string $class = self::class): sql_par
    {
        return parent::load_sql_by_grp($sc, $grp, $class);
    }

    /**
     * prepare the query parameter to load a results by phrase group id
     *
     * @param sql $sc with the target db_type set
     * @param group $grp the group used for the selection
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    private function load_sql_by_grp_prepare(sql $sc, group $grp): sql_par
    {
        $qp = $this->load_sql($sc, 'grp');
        $sc->set_name($qp->name);
        $sc->add_where(self::FLD_GRP, $grp->id());
        return $qp;
    }

    /**
     * create the SQL to load a default results for all users by phrase group id
     *
     * @param sql $sc with the target db_type set
     * @param group $grp the group used for the selection
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_std_by_grp(sql $sc, group $grp): sql_par
    {
        $sc->set_class(self::class);
        // overwrite the standard id field name (result_id) with the main database id field for results "group_id"
        $sc->set_id_field($this->id_field());
        $sc->set_fields(array_merge(self::FLD_NAMES, array(user::FLD_ID)));

        $qp = $this->load_sql_by_grp_prepare($sc, $grp);
        return parent::load_standard_sql_by($sc, $qp);
    }

    /**
     * create the SQL to load a results by formula id and phrase group id
     *
     * @param sql $sc with the target db_type set
     * @param formula $frm the formula used for the selection
     * @param group $grp the group used for the selection
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_frm_grp(sql $sc, formula $frm, group $grp): sql_par
    {
        $qp = $this->load_sql($sc, 'frm_grp');
        $sc->set_name($qp->name);
        $sc->add_where(formula::FLD_ID, $frm->id());
        $sc->add_where(self::FLD_GRP, $grp->id());
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();
        return $qp;
    }

    /**
     * create the SQL to load a results by formula id and phrase group id
     *
     * @param sql $sc with the target db_type set
     * @param formula $frm the formula used for the selection
     * @param group_list $lst the group used for the selection
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_frm_grp_lst(sql $sc, formula $frm, group_list $lst): sql_par
    {
        $qp = $this->load_sql($sc, 'frm_grp_lst');
        $sc->set_name($qp->name);
        $sc->add_where(formula::FLD_ID, $frm->id());
        $sc->add_where(self::FLD_GRP, $lst->ids());
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();
        return $qp;
    }

    /**
     * create an SQL statement to retrieve the user changes of the current result
     *
     * @param sql $sc with the target db_type set
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_user_changes(sql $sc, string $class = self::class): sql_par
    {
        $tbl_typ = $this->grp->table_type();
        $sc->set_class($class, new sql_type_list([sql_type::USER]), $tbl_typ->extension());
        // overwrite the standard id field name (result_id) with the main database id field for results "group_id"
        $sc->set_id_field($this->id_field());
        return parent::load_sql_user_changes($sc, $class);
    }

    /**
     * load (or force reload from database of) a result by the id
     *
     * @param int|string $id the unique database id of the result that should be loaded
     * @param string $class always the result class just to be compatible with the parent function
     * @return int true if result has been loaded
     */
    function load_by_id(int|string $id = 0, string $class = self::class): int
    {
        global $db_con;
        $result = 0;

        if ($id != 0) {
            // if the id is given load the result from the database
            $res_usr = $this->user();
            $this->reset();
            $this->set_user($res_usr);
            $this->set_id($id);
        } else {
            // if the id is not given, refresh the object based pn the database
            if ($this->id != 0) {
                $id = $this->id();
                $res_usr = $this->user();
                $this->reset();
                $this->set_user($res_usr);
                $this->set_id($id);
            } else {
                log_err('The result id and the user must be set ' .
                    'to load a ' . self::class, self::class . '->load_by_id');
            }
        }
        $qp = $this->load_sql_by_id($db_con->sql_creator(), $id);
        if ($qp->name != '') {
            $db_row = $db_con->get1($qp);
            $this->row_mapper_multi($db_row, $qp->ext);
            $result = $this->id();
        }

        return $result;
    }

    /**
     * load all a result by the phrase group id and time phrase
     *
     * @param group $grp to select the result
     * @return bool true if result has been loaded
     */
    function load_by_grp(group $grp, ?int $time_phr_id = null): bool
    {
        global $db_con;
        $result = false;

        if (!$grp->is_id_set()) {
            log_err('The result phrase group id and the user must be set ' .
                'to load a ' . self::class, self::class . '->load_by_grp');
        } else {
            $res_usr = $this->user();
            $this->reset();
            $this->set_user($res_usr);
            $qp = $this->load_sql_by_grp($db_con->sql_creator(), $grp);
            if ($qp->name != '') {
                $db_row = $db_con->get1($qp);
                $this->row_mapper_multi($db_row, $qp->ext);
                $result = true;
            }
        }

        return $result;
    }

    /**
     * load all a default results for all users by the phrase group id and time phrase
     *
     * @param group $grp to select the result
     * @return bool true if result has been loaded
     */
    function load_std_by_grp(group $grp): bool
    {
        global $db_con;
        $result = false;

        if ($grp->is_id_set()) {
            log_err('The result phrase group id and the user must be set ' .
                'to load a ' . self::class, self::class . '->load_std_by_grp');
        } else {
            $res_usr = $this->user();
            $this->reset();
            $this->set_user($res_usr);
            $qp = $this->load_sql_std_by_grp($db_con->sql_creator(), $grp);
            if ($qp->name != '') {
                $db_row = $db_con->get1($qp);
                $this->row_mapper_multi($db_row, $qp->ext);
                $result = true;
            }
        }

        return $result;
    }

    /**
     * load all a result by the phrase group id and time phrase
     *
     * @param formula $frm to select the result
     * @param group $grp to select the result
     * @return bool true if result has been loaded
     */
    function load_by_formula_and_group(formula $frm, group $grp): bool
    {
        global $db_con;
        $result = false;

        if ($frm->id() <= 0) {
            log_err('The formula id must be set to load a ' . self::class);
        } elseif (!$grp->is_id_set()) {
            log_err('The phrase group id must be set to load a ' . self::class);
        } else {
            $res_usr = $this->user();
            $this->reset();
            $this->set_user($res_usr);
            $qp = $this->load_sql_by_frm_grp($db_con->sql_creator(), $frm, $grp);
            if ($qp->name != '') {
                $db_row = $db_con->get1($qp);
                $this->row_mapper_multi($db_row, $qp->ext);
                $result = true;
            }
        }

        return $result;
    }

    /**
     * load all a result by the phrase group id and time phrase
     *
     * @param formula $frm to select the result
     * @param group_list $lst the group used for the selection
     * @return bool true if result has been loaded
     */
    function load_by_formula_and_group_list(formula $frm, group_list $lst): bool
    {
        global $db_con;
        $result = false;

        if ($frm->id() <= 0) {
            log_err('The formula id must be set to load a ' . self::class);
        } else {
            $res_usr = $this->user();
            $this->reset();
            $this->set_user($res_usr);
            $qp = $this->load_sql_by_frm_grp_lst($db_con->sql_creator(), $frm, $lst);
            if ($qp->name != '') {
                $db_row = $db_con->get1($qp);
                $this->row_mapper_multi($db_row, $qp->ext);
                $result = true;
            }
        }

        return $result;
    }

    /**
     * load all a result by a give phrase list and if set the time phrase
     *
     * @return bool true if result has been loaded
     */
    function load_by_phr_lst(phrase_list $phr_lst, ?int $time_phr_id = null): bool
    {
        $result = false;

        if ($phr_lst->is_valid()) {
            $res_usr = $this->user();
            $this->reset();
            $this->set_user($res_usr);
            $grp = $phr_lst->get_grp_id();
            $result = $this->load_by_grp($grp, $time_phr_id);
        } else {
            log_err('The result phrase list and the user must be set ' .
                'to load a ' . self::class, self::class . '->load_by_phr_lst');
        }

        return $result;
    }

    /**
     * create the SQL to load a results by a given where statement
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param string $sql_where the ready to use SQL where statement
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_where(sql_db $db_con, sql_par $qp, string $sql_where = ''): sql_par
    {
        $db_con->set_name($qp->name);
        $db_con->set_usr($this->user()->id());
        $db_con->set_fields(self::FLD_NAMES);
        $db_con->set_where_text($sql_where);
        $qp->sql = $db_con->select_by_set_id();
        $qp->par = $db_con->get_par();

        return $qp;
    }

    /**
     * load the record from the database
     * in a separate function, because this can be called twice from the load function
     *
     * @param sql_par $qp the ready to use SQL where statement with the name and the parameters
     * @return bool true if one database record has been loaded
     */
    private function load_rec(sql_par $qp): bool
    {
        global $db_con;
        $result = false;

        $val_rows = $db_con->get($qp);
        if ($val_rows != null) {
            if (count($val_rows) > 0) {
                $val_row = $val_rows[0];
                $result = $this->row_mapper_multi($val_row, $qp->ext);
            }
        }
        return $result;
    }


    /*
     * phrase loading methods
     */

    /**
     * update the source phrase list based on the source phrase group id
     * @param bool $force_reload set to true if a loaded phrase list should refresh with database values
     */
    private function load_phr_lst_src(bool $force_reload = false): void
    {
        if ($this->src_grp->is_id_set()) {
            if ($this->src_grp->phrase_list() == null or $force_reload) {
                log_debug('for source group "' . $this->src_grp->id() . '"');
                $phr_grp = new group($this->user());
                $phr_grp->load_by_id($this->src_grp->id());
                if (!$phr_grp->phrase_list()->empty()) {
                    $this->src_grp->set_phrase_list($phr_grp->phrase_list());
                    log_debug('source phrases ' . $this->src_grp->phrase_list()->dsp_name() . ' loaded');
                } else {
                    log_debug('no source words found for ' . $this->dsp_id());
                }
            }
        }
        if ($this->src_grp->phrase_list() != null) {
            if ($this->src_grp->phrase_list()->empty()) {
                log_warning("Missing source words for the calculated value " . $this->id() . ' (group id ' . $this->src_grp->dsp_id() . ').', "result->load_phr_lst_src");
            }
        } else {
            log_warning("Missing source words for the calculated value " . $this->id() . ' (group id ' . $this->src_grp->dsp_id() . ').', "result->load_phr_lst_src");
        }
    }

    /**
     * update the phrase list based on the word group id
     * @param bool $force_reload set to true if a loaded phrase list should refresh with database values
     */
    private function load_phr_lst(bool $force_reload = false): void
    {
        if ($this->grp->is_id_set()) {
            if ($this->grp->phrase_list() == null or $force_reload) {
                log_debug('for group "' . $this->grp->id() . '"');
                $phr_grp = new group($this->user());
                $phr_grp->load_by_id($this->grp->id());
                if (!$phr_grp->phrase_list()->empty()) {
                    $this->grp->set_phrase_list($phr_grp->phrase_list());
                    log_debug('phrases ' . $this->grp->phrase_list()->dsp_name() . ' loaded');
                } else {
                    log_debug('no result phrases found for ' . $this->dsp_id());
                }
            }
        }
        if ($this->grp->phrase_list() != null) {
            if ($this->grp->phrase_list()->empty()) {
                log_warning("Missing result phrases for the calculated value " . $this->id(), "result->load_phr_lst");
            }
        } else {
            log_warning("Missing result phrases for the calculated value " . $this->id(), "result->load_phr_lst");
        }
    }

    /**
     * update the phrase objects based on the phrase group ids
     * (usually done after loading the formula result from the database)
     */
    function load_phrases(bool $force_reload = false): void
    {
        if ($this->id > 0) {
            log_debug('for user ' . $this->user()->name);
            $this->load_phr_lst_src($force_reload);
            $this->load_phr_lst($force_reload);
        }
    }

    /**
     * update the formulas objects based on the id
     */
    private function load_formula(): void
    {
        if ($this->frm->id() > 0) {
            log_debug('for user ' . $this->user()->name);
            $frm = new formula($this->user());
            $frm->load_by_id($this->frm->id(), formula::class);
            $this->frm = $frm;
        }
    }


    /*
     * information
     */


    /*
     * im- and export
     */

    /**
     * validate a formulas value by comparing the external object result with the calculated result
     *
     * @param array $json_obj an array with the data of the json object
     * @param object|null $test_obj if not null the unit test object to get a dummy seq id
     * @return user_message the status of the import and if needed the error messages that should be shown to the user
     */
    function import_obj(array $json_obj, object $test_obj = null): user_message
    {
        log_debug();
        $result = parent::import_db_obj($this, $test_obj);

        if ($test_obj) {
            $do_save = false;
        } else {
            $do_save = true;
        }

        foreach ($json_obj as $key => $res) {

            if ($key == export::WORDS) {
                $phr_lst = new phrase_list($this->user());
                $result->add($phr_lst->import_lst($res, $test_obj));
                if ($result->is_ok()) {
                    $phr_grp = $phr_lst->get_grp_id($do_save);
                    log_debug('got word group ' . $phr_grp->dsp_id());
                    $this->set_grp($phr_grp);
                    log_debug('set grp id to ' . $this->grp->id());
                }
            }

            /*
            if ($key == exp_obj::FLD_TIMESTAMP) {
                if (strtotime($res)) {
                    $this->time_stamp = get_datetime($res, $this->dsp_id(), 'JSON import');
                } else {
                    log_err('Cannot add timestamp "' . $res . '" when importing ' . $this->dsp_id(), 'value->import_obj');
                }
            }
            */

            if ($key == sandbox_exp::FLD_NUMBER) {
                $this->value = $res;
            }

        }

        // save the result in the database
        if (!$test_obj) {
            if ($result->is_ok()) {
                $this->save();
                log_debug($this->dsp_id());
            } else {
                log_debug($result->all_message_text());
            }
        }

        return $result;
    }

    /**
     * create an JSON result object for the export
     * to enable the validation of the results during import
     *
     * @param bool $do_load true if the result should be validated again before export
     *                      use false for a faster export
     * @return result_exp the filled formula validation object used for JSON creation
     */
    function export_obj(bool $do_load = true): result_exp
    {
        log_debug();
        $result = new result_exp();

        // reload the value parameters
        if ($do_load) {
            $this->load_by_id();
            log_debug(result::class . '->export_obj load phrases');
            $this->load_phrases();
        }

        // add the phrases
        log_debug(result::class . '->export_obj get phrases');
        $phr_lst = array();
        // TODO use either word and triple export_obj function or phrase
        if ($this->grp->phrase_list() != null) {
            if (!$this->grp->phrase_list()->is_empty()) {
                foreach ($this->grp->phrase_list()->lst() as $phr) {
                    $phr_lst[] = $phr->name();
                }
                if (count($phr_lst) > 0) {
                    $result->words = $phr_lst;
                }
            }
        }

        // add the value itself
        $result->number = $this->value;

        log_debug(json_encode($result));
        return $result;
    }

    /*
       methods to prepare the words for saving into the database
       ---------------------------------------------------------
    */

    // update the source word group id based on the word list ($this->grp->phrase_list())
    private function save_prepare_phr_lst_src(): void
    {
        if ($this->src_grp->phrase_list()->is_empty()) {
            // TODO check if the phrases are already loaded
            // $this->src_grp->phrase_list()->load();
            // get the word group id (and create the group if needed)
            // TODO include triples
            if (count($this->src_grp->phrase_list()->id_lst()) > 0) {
                log_debug("source group for " . $this->src_grp->phrase_list()->dsp_id() . ".");
                $grp = new group($this->user());
                $grp->load_by_phr_lst($this->src_grp->phrase_list());
                $this->src_grp->set_id($grp->get_id());
            }
            log_debug("source group id " . $this->src_grp->dsp_id() . " for " . $this->src_grp->phrase_list()->dsp_name() . ".");
        }
    }

    // update the word group id based on the word list ($this->grp->phrase_list())
    private function save_prepare_phr_lst(): void
    {
        if ($this->grp->phrase_list()->is_empty()) {
            // get the word group id (and create the group if needed)
            // TODO include triples
            $grp = new group($this->user());
            $grp->load_by_phr_lst($this->grp->phrase_list());
            $this->grp->set_id($grp->get_id());
            log_debug("group id " . $this->grp->id() . " for " . $this->grp->phrase_list()->dsp_name() . ".");
        }
    }

    // update the word ids based on the word objects (usually done before saving the formula result to the database)
    private function save_prepare_wrds()
    {
        log_debug();
        $this->save_prepare_phr_lst_src();
        $this->save_prepare_phr_lst();
        log_debug("done.");
    }

    /**
     * depending on the phrases format the numeric value
     * e.g. if the result phrases contains a word of type percent format the value per default as percent
     * similar to the corresponding function in the "value" class
     *
     * @returns string with the value in the most useful format for humans
     */
    function val_formatted(): string
    {
        $result = '';

        if (!is_null($this->value)) {
            log_debug('result->val_formatted');
            if ($this->grp->phrase_list() == null) {
                $this->load_phrases();
                log_debug('result->val_formatted loaded');
            }
            log_debug('result->val_formatted check ' . $this->dsp_id());
            if ($this->grp->phrase_list()->has_percent()) {
                $result = round($this->value * 100, $this->user()->percent_decimals) . ' %';
                log_debug('result->val_formatted percent of ' . $this->value);
            } else {
                if ($this->value >= 1000 or $this->value <= -1000) {
                    log_debug('result->val_formatted format');
                    $result .= number_format($this->value, 0, $this->user()->dec_point, $this->user()->thousand_sep);
                } else {
                    log_debug('result->val_formatted round');
                    $result = round($this->value, 2);
                }
            }
        }
        log_debug('result->val_formatted done');
        return $result;
    }

    /**
     * create and return the figure object for the value
     */
    function figure(): figure
    {
        return new figure($this);
    }

    /**
     * @returns array with the ids of the phrases related to this formula result
     */
    function phr_ids(): array
    {
        $id_lst = [];
        if ($this->grp->phrase_list() != null) {
            $id_lst = $this->grp->phrase_list()->id_lst();
        }
        return $id_lst;
    }

    /*
     * display
     */

    /**
     * this function is called from dsp_id, so no other call is allowed
     * @return string the best possible name for the object
     */
    function name(): string
    {
        $result = '';

        if (!$this->grp->phrase_list()->is_empty()) {
            $result .= $this->grp->phrase_list()->dsp_name();
        }

        return $result;
    }

    function name_linked(): string
    {
        $result = '';

        if (!$this->grp->phrase_list()->is_empty()) {
            $result .= $this->grp->phrase_list()->name_linked();
        }

        return $result;
    }

    /**
     * html code to show the value with the indication if the value is influence by the user input
     */
    function display(): string
    {
        $result = '';
        if (!is_null($this->value)) {
            $num_text = $this->val_formatted();
            if ($this->owner_id > 0) {
                $result .= '<span class="user_specific">' . $num_text . '</span>' . "\n";
            } else {
                $result .= $num_text . "\n";
            }
        }
        return $result;
    }

    /**
     * html code to show the value with the possibility to click for the result explanation
     */
    function display_linked(string $back = ''): string
    {
        $result = '';
        if (!is_null($this->value)) {
            $num_text = $this->val_formatted();
            $link_format = '';
            if ($this->owner_id > 0) {
                $link_format = ' class="user_specific"';
            }
            // TODO review
            $wrd_ids = $this->phr_ids();
            if (!empty($wrd_ids)) {
                $lead_phr_id = $wrd_ids[0];
                $result .= '<a href="/http/formula_result.php?id=' . $this->id() . '&phrase=' . $lead_phr_id . '&group=' . $this->grp->id() . '&back=' . $back . '"' . $link_format . '>' . $num_text . '</a>';
            }
        }
        return $result;
    }

    // explain a formula result to the user
    // create an HTML page that shows different levels of detail information for one formula result to explain to the user how the value is calculated
    function explain($lead_phr_id, $back): string
    {
        $lib = new library();
        $html = new html_base();
        log_debug('result->explain ' . $this->dsp_id() . ' for user ' . $this->user()->name);
        $result = '';

        // display the leading word
        // $lead_wrd =
        // $lead_wrd->id()  = $lead_phr_id;
        // $lead_wrd->usr = $this->user();
        // $lead_wrd->load();
        //$result .= $lead_phr_id->name();

        // build the title
        $title = '';
        // add the words that specify the calculated value to the title
        $val_phr_lst = clone $this->grp->phrase_list();
        $val_wrd_lst = $val_phr_lst->wrd_lst_all();
        $title .= $lib->dsp_array($val_wrd_lst->api_obj()->ex_measure_and_time_lst()->dsp_obj()->names_linked());
        $time_phr = $lib->dsp_array($val_wrd_lst->dsp_obj()->time_lst()->dsp_obj()->names_linked());
        if ($time_phr <> '') {
            $title .= ' (' . $time_phr . ')';
        }
        $title .= ': ';
        // add the value  to the title
        $title .= $this->display($back);
        $result .= $html->dsp_text_h1($title);
        log_debug('explain the value for ' . $val_phr_lst->dsp_name() . ' based on ' . $this->src_grp->phrase_list()->dsp_name());

        // display the measure and scaling of the value
        if ($val_wrd_lst->has_percent()) {
            $result .= 'from ' . $val_wrd_lst->api_obj()->measure_scale_lst()->dsp_obj()->display();
        } else {
            $result .= 'in ' . $val_wrd_lst->api_obj()->measure_scale_lst()->dsp_obj()->display();
        }
        $result .= '</br></br>' . "\n";

        // display the formula with links
        $frm = new formula($this->user());
        $frm->load_by_id($this->frm->id(), formula::class);
        $frm_html = new formula_dsp($frm->api_json());
        $result .= ' based on</br>' . $frm_html->display_linked($back);
        $result .= ' ' . $frm_html->dsp_text($back) . "\n";
        $result .= ' ' . $frm_html->btn_edit($back) . "\n";
        $result .= '</br></br>' . "\n";

        // load the formula element groups
        // each element group can contain several elements
        // e.g. for <journey time premium offset = "journey time average" / "journey time max premium" "percent">
        // <"journey time max premium" "percent"> is one element group with two elements
        // and these two elements together are use to select the value
        $exp = $frm->expression();
        //$elm_lst = $exp->element_lst ($back);
        $elm_grp_lst = $exp->element_grp_lst($back);
        log_debug("elements loaded (" . $lib->dsp_count($elm_grp_lst->lst()) . " for " . $frm->ref_text . ")");

        $result .= ' where</br>';

        // check the element consistency and if it fails, create a warning
        if (!$this->src_grp->phrase_list()->is_empty()) {
            log_warning("Missing source words for the calculated value " . $this->dsp_id(), "result->explain");
        } else {

            $elm_nbr = 0;
            foreach ($elm_grp_lst->lst() as $elm_grp) {

                // display the formula element names and create the element group object
                $result .= $elm_grp->dsp_names($back) . ' ';
                log_debug('elm grp name "' . $elm_grp->dsp_names($back) . '" with back "' . $back . '"');


                // exclude the formula word from the words used to select the formula element values
                // so reverse what has been done when saving the result
                $this->src_grp->set_phrase_list(clone $this->src_grp->phrase_list());
                $frm_wrd_id = $frm->name_wrd->id();
                $this->src_grp->phrase_list()->diff_by_ids(array($frm_wrd_id));
                log_debug('formula word "' . $frm->name_wrd->name() . '" excluded from ' . $this->src_grp->phrase_list()->dsp_name());

                // select or guess the element time word if needed
                log_debug('guess the time ... ');
                $elm_time_phr = $this->src_grp->phrase_list()->assume_time();

                $elm_grp->set_phrase_list($this->src_grp->phrase_list());
                $elm_grp->time_phr = $elm_time_phr;
                $elm_grp->usr = $this->user();
                log_debug('words set ' . $elm_grp->phrase_list()->dsp_name() . ' taken from the source and user "' . $elm_grp->usr->name . '"');

                // finally, display the value used in the formula
                $result .= ' = ' . $elm_grp->dsp_values($back);
                $result .= '</br>';
                log_debug('next element');
                $elm_nbr++;
            }
        }

        return $result;
    }

    // update (calculate) all results that are depending
    // e.g. if the PE ratio for ABB, 2018 has been updated,
    //      the target price for ABB, 2018 needs to be updated if it is based on the PE ratio
    // so:  get a list of all formulas, where the result is used
    //      based on the frm id and the word group
    function update_depending(): array
    {
        $lib = new library();
        log_debug("(f" . $this->frm->id() . ",t" . $lib->dsp_array($this->phr_ids()) . ",v" . $this->value . " and user " . $this->user()->name . ")");

        global $db_con;
        $result = array();

        // get depending formulas
        $frm_elm_lst = new element_list($this->user());
        $frm_elm_lst->load_by_frm_and_type_id($this->frm->id(), parameter_type::FORMULA_ID);
        $frm_ids = array();
        foreach ($frm_elm_lst as $frm_elm) {
            if ($frm_elm->obj != null) {
                $frm_ids[] = $frm_elm->obj->id;
            }
        }
        // get formula results that may need an update (maybe include also word groups that have any word of the updated word group)
        if (!empty($frm_ids)) {
            $sql_in = $lib->sql_array($frm_ids, ' formula_id IN (', ') ');
            $sql = "SELECT group_id, formula_id
                FROM results 
               WHERE " . $sql_in . "
                 AND group_id = " . $this->grp->id() . "
                 AND user_id         = " . $this->user()->id() . ";";
            //$db_con = New mysql;
            $db_con->usr_id = $this->user()->id();
            $val_rows = $db_con->get_old($sql);
            foreach ($val_rows as $val_row) {
                $frm_ids[] = $val_row[formula::FLD_ID];
                $res_upd = new result($this->user());
                $res_upd->load_by_id($val_row[self::FLD_ID]);
                $res_upd->update();
                // if the value is really updated, remember the value is to check if this triggers more updates
                $result[] = $res_upd->save();
            }
        }

        return $result;
    }

    // update the result of this result (without loading or saving)
    function update(): void
    {
        log_debug('result->update ' . $this->dsp_id());
        // check parameters
        if ($this->phr_lst()->is_empty()) {
            log_err("Phrase list is missing.", "result->update");
        } elseif ($this->frm->id() <= 0) {
            log_err("Formula ID is missing.", "result->update");
        } else {
            // prepare update
            $this->load_phrases();
            $this->load_formula();

            $frm = $this->frm;
            $phr_lst = $this->src_grp->phrase_list();
            $frm->calc($phr_lst, '');

            //$this->save_if_updated ();
            log_debug('result->update ' . $this->dsp_id() . ' to ' . $this->value . ' done');
        }
    }

    private function save_without_time(): string
    {
        $res_no_time = clone $this;
        // $res_no_time->time_phr = null;
        return $res_no_time->save();
    }


    /**
     * @return bool true if a value without time is already saved
     *
     * e.g. if the user asks for the inhabitants of the city of zurich and does not specify the time
     *      the guess is that the user wants the latest reported number
     *      so this function will return false if the latest reported number is not yet saved in the database
     */
    // TODO add check
    private function has_no_time_value(): bool
    {
        $res_check = clone $this;
        $phr_lst_ex_time = $res_check->grp->phrase_list();
        $phr_lst_ex_time->ex_time();
        return !$res_check->load_by_phr_lst($phr_lst_ex_time);
    }

    // check if a single formula result needs to be saved to the database
    function save_if_updated(bool $has_result_phrases = false): bool
    {
        global $debug;
        $result = true;

        // don't save the result if some needed numbers are missing
        if ($this->val_missing) {
            log_debug('Some values are missing for ' . $this->dsp_id());
        } else {
            // save only if any parameter has been updated since last calculation
            if ($this->last_val_update <= $this->last_update) {
                if (isset($this->last_val_update) and isset($this->last_update)) {
                    log_debug($this->dsp_id() . ' not saved because the result has been calculated at ' . $this->last_update->format('Y-m-d H:i:s') . ' and after the last parameter update ' . $this->last_val_update->format('Y-m-d H:i:s'));
                } else {
                    log_debug($this->dsp_id() . ' not saved because the result has been calculated after the last parameter update ');
                }
                //zu_debug('result->save_if_updated -> save '.$this->dsp_id().' not saved because the result has been calculated at '.$this->last_update.' which is after the last parameter update at '.$this->last_update);
            } else {
                if (isset($this->last_val_update) and isset($this->last_update)) {
                    log_debug('save ' . $this->dsp_id() . ' because parameters have been updated at ' . $this->last_val_update->format('Y-m-d H:i:s') . ' and the formula result update is from ' . $this->last_update->format('Y-m-d H:i:s'));
                } else {
                    if (isset($this->last_val_update)) {
                        log_debug('save ' . $this->dsp_id() . ' and result update time is set to ' . $this->last_val_update->format('Y-m-d H:i:s'));
                        $this->last_update = $this->last_val_update;
                    } else {
                        log_debug('save ' . $this->dsp_id() . ' but times are missing');
                    }
                }
                // check the formula result consistency
                if (!$this->grp->phrase_list()->is_empty()) {
                    log_warning('The result phrases for ' . $this->dsp_id() . ' are missing.', 'result->save_if_updated');
                }
                if (!$this->src_grp->phrase_list()->is_empty()) {
                    log_warning('The source phrases for ' . $this->dsp_id() . ' are missing.', 'result->save_if_updated');
                }

                // add the formula name word, but not is the result words are defined in the formula
                // e.g. if the formula "country weight" is calculated the word "country weight" should be added to the result values
                if (!$has_result_phrases) {
                    log_debug('add the formula name ' . $this->frm->dsp_id() . ' to the result phrases ' . $this->grp->phrase_list()->dsp_id());
                    if ($this->frm != null) {
                        if ($this->frm->name_wrd != null) {
                            $this->grp->phrase_list()->add($this->frm->name_wrd->phrase());
                        }
                    }
                }

                // e.g. if the formula is a division and the values used have a measure word like meter or CHF, the result is only in percent, but not in meter or CHF
                // simplified version, that needs to be review to handle more complex formulas
                if (strpos($this->frm->ref_text_r, expression::DIV) !== false) {
                    log_debug('check measure ' . $this->grp->phrase_list()->dsp_id());
                    if ($this->grp->phrase_list()->has_measure()) {
                        $this->grp->phrase_list()->ex_measure();
                        log_debug('measure removed from words ' . $this->grp->phrase_list()->dsp_id());
                    }
                }

                // build the formula result object
                //$this->frm_id = $this->frm->id();
                //$this->user()->id() = $frm_result->result_user;
                log_debug('save "' . $this->value . '" for ' . $this->grp->phrase_list()->dsp_id());

                // get the default time for the phrases e.g. if the increase for ABB sales is calculated the last reported sales increase is assumed
                $lst_ex_time = $this->grp->phrase_list();
                $lst_ex_time->ex_time();
                $res_default_time = $lst_ex_time->assume_time(); // must be the same function called used in 2num
                if (isset($res_default_time)) {
                    log_debug('save "' . $this->value . '" for ' . $this->grp->phrase_list()->dsp_id() . ' and default time ' . $res_default_time->dsp_id());
                } else {
                    log_debug('save "' . $this->value . '" for ' . $this->grp->phrase_list()->dsp_id());
                }

                if (!isset($this->value)) {
                    log_info('No result calculated for "' . $this->frm->name() . '" based on ' . $this->src_grp->phrase_list()->dsp_id() . ' for user ' . $this->user()->id() . '.', "result->save_if_updated");
                } else {
                    // save the default value if the result time is the "newest"
                    if (isset($res_default_time)) {
                        log_debug('check if result time ' . $this->grp->time()->dsp_id() . ' is the default time ' . $res_default_time->dsp_id());
                        if ($this->grp->time()->id() == $res_default_time->id()) {
                            // if there is not yet a general value for all user, save it now
                            $result .= $this->save_without_time();
                        }
                    }

                    // save the value without time if no value without time is yet saved for the phrase group
                    if ($this->has_no_time_value()) {
                        $result .= $this->save_without_time();
                    }

                    // save the result
                    $this->save();
                    $res_id = $this->id();

                    if ($debug > 0) {
                        $debug_txt = 'result = ' . $this->value . ' saved for ' . $this->grp->phrase_list()->name_linked();
                        if ($debug > 3) {
                            $debug_txt .= ' (group id "' . $this->grp->id() . '" as id "' . $res_id . '" based on ' . $this->src_grp->phrase_list()->name_linked() . ' (group id "' . $this->src_grp->dsp_id() . ')';
                        }
                        if (!$this->is_std) {
                            $debug_txt .= ' for user "' . $this->user()->name . '"';
                        }
                        log_debug($debug_txt . '');
                    }
                }
            }
        }
        return $result;
    }

    /**
     * save the formula result to the database
     * TODO check if user specific result needs to be added
     * for the word selection the id list is the lead, not the object list and not the group
     * @return string the message that should be shown to the user in case something went wrong
     */
    function save(): string
    {

        global $db_con;
        global $debug;
        $result = '';

        // check the parameters e.g. a result must always be linked to a formula
        if ($this->frm->id() <= 0) {
            log_err("Formula id missing.", "result->save");
        } elseif (empty($this->grp->phrase_list())) {
            log_err("No words for the result.", "result->save");
        } elseif (empty($this->src_grp->phrase_list())) {
            log_err("No words for the calculation.", "result->save");
        } elseif ($this->user() == null) {
            log_err("User missing.", "result->save");
        } else {
            if ($debug > 0) {
                $debug_txt = 'result->save (' . $this->value . ' for formula ' . $this->frm->id() . ' with ' . $this->grp->phrase_list()->dsp_name() . ' based on ' . $this->src_grp->phrase_list()->dsp_name();
                if (!$this->is_std) {
                    $debug_txt .= ' and user ' . $this->user()->id();
                }
                $debug_txt .= ')';
                log_debug($debug_txt);
            }

            // build the database object because the is anyway needed
            //$db_con = new mysql;
            $db_con->set_usr($this->user()->id());
            $db_con->set_class(result::class);

            // build the word list if needed to separate the time word from the word list
            $this->save_prepare_wrds();
            log_debug("group id " . $this->grp->id() . " and source group id " . $this->src_grp->dsp_id());

            // check if a database update is needed
            // or if a second results object with the database values
            $res_db = new result($this->user());
            $res_db->load_by_id($this->id());
            $row_id = $res_db->id();
            $db_val = $res_db->value;

            // if value exists, check it an update is needed
            // updates of results are not logged because they could be reproduced
            if ($row_id > 0) {
                if ($db_con->sf($db_val) <> $db_con->sf($this->value)) {
                    $msg = 'update result ' . result::FLD_VALUE . ' to ' . $this->value
                        . ' from ' . $db_val . ' for ' . $this->dsp_id();
                    log_debug($msg);
                    $db_con->set_class(result::class);
                    $sc = $db_con->sql_creator();
                    $qp = $this->sql_update($sc, $res_db);
                    $usr_msg = $db_con->update($qp, $msg);
                    if ($usr_msg->is_ok()) {
                        $result = $row_id;
                    }
                } else {
                    $msg = 'update of result ' . result::FLD_VALUE . ' ' . $this->dsp_id() . ' not needed';
                    log_debug($msg);
                    $this->id = $row_id;
                    $result = $row_id;
                }
            } else {
                $msg = 'insert result ' . $this->value . ' for ' . $this->dsp_id();
                $field_names = array();
                $field_values = array();
                $field_names[] = formula::FLD_ID;
                $field_values[] = $this->frm->id();
                $field_names[] = result::FLD_VALUE;
                $field_values[] = $this->value;
                $field_names[] = result::FLD_GRP;
                $field_values[] = $this->grp->id();
                $field_names[] = result::FLD_SOURCE_GRP;
                $field_values[] = $this->src_grp->id();
                if (!$this->is_std) {
                    $field_names[] = user::FLD_ID;
                    $field_values[] = $this->user()->id();
                }
                $field_names[] = result::FLD_LAST_UPDATE;
                //$field_values[] = sql_creator::NOW; // replaced with time of last change that has been included in the calculation
                $field_values[] = $this->last_val_update->format('Y-m-d H:i:s');
                $db_con->set_class(result::class);
                $sc = $db_con->sql_creator();
                $qp = $this->sql_insert($sc);
                $usr_msg = $db_con->insert($qp, $msg);
                if ($usr_msg->is_ok()) {
                    $result = $row_id;
                }
            }
        }

        log_debug("id (" . $result . ")");
        return $result;

    }


    /*
     * sql write
     */

    /**
     * create the sql statement to add a new result to the database
     * TODO add source group
     *
     * @param sql $sc with the target db_type set
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par the SQL insert statement, the name of the SQL statement and the parameter list
     */
    function sql_insert(sql $sc, sql_type_list $sc_par_lst = new sql_type_list([])): sql_par
    {
        $qp = $this->sql_common($sc, $sc_par_lst);
        $usr_tbl = $sc_par_lst->is_usr_tbl();
        // overwrite the standard auto increase id field name
        $sc->set_id_field($this->id_field());
        $qp->name .= sql::file_sep . sql::file_insert;
        $sc->set_name($qp->name);
        if ($this->grp->is_prime()) {
            $fields = $this->grp->id_names();
            $fields[] = user::FLD_ID;
            $values = $this->grp->id_lst();
            $values[] = $this->user()->id();
            if (!$usr_tbl) {
                $fields[] = self::FLD_VALUE;
                $fields[] = self::FLD_LAST_UPDATE;
                $values[] = $this->number;
                $values[] = sql::NOW;
            }
        } else {
            if ($usr_tbl) {
                $fields = array(group::FLD_ID, user::FLD_ID);
                $values = array($this->grp->id(), $this->user()->id());
            } else {
                $fields = array(group::FLD_ID, user::FLD_ID, self::FLD_VALUE, self::FLD_LAST_UPDATE);
                $values = array($this->grp->id(), $this->user()->id(), $this->number, sql::NOW);
            }

        }
        $fvt_lst = new sql_par_field_list();
        $fvt_lst->fill_from_arrays($fields, $values);
        $qp->sql = $sc->create_sql_insert($fvt_lst);
        $par_values = [];
        foreach (array_keys($values) as $i) {
            if ($values[$i] != sql::NOW) {
                $par_values[$i] = $values[$i];
            }
        }

        $qp->par = $par_values;
        return $qp;
    }

    /**
     * create the sql statement to update a result in the database
     * TODO make code review and move part to the parent sandbox value class
     *
     * @param sql $sc with the target db_type set
     * @param result $db_res the result object with the database values before the update
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par the SQL insert statement, the name of the SQL statement and the parameter list
     */
    function sql_update(sql $sc, result $db_res, sql_type_list $sc_par_lst = new sql_type_list([])): sql_par
    {
        $lib = new library();
        $usr_tbl = $sc_par_lst->is_usr_tbl($sc_par_lst);
        $qp = $this->sql_common($sc, $sc_par_lst);
        // get the fields and values that have been changed and needs to be updated in the database
        $fld_val_typ_lst = $this->db_changed($db_res);
        $fields = $sc->get_fields($fld_val_typ_lst);
        $fld_name = implode('_', $lib->sql_name_shorten($fields));
        // TODO use const
        $qp->name .= '_update_' . $fld_name;
        $sc->set_name($qp->name);
        if ($this->grp->is_prime()) {
            $id_fields = $this->grp->id_names(true);
        } else {
            $id_fields = $this->id_field();
        }
        $id = $this->id();
        $id_lst = [];
        if (is_array($id_fields)) {
            if (!is_array($id)) {
                $grp_id = new group_id();
                $id_lst = $grp_id->get_array($id, true);
                foreach ($id_lst as $key => $value) {
                    if ($value == null) {
                        $id_lst[$key] = 0;
                    }
                }
            } else {
                $id_lst = $id;
            }
        } else {
            $id_lst = $id;
        }
        if ($usr_tbl) {
            $id_fields[] = user::FLD_ID;
            if (!is_array($id_lst)) {
                $id_lst = [$id_lst];
            }
            $id_lst[] = $this->user()->id();
        }
        $fvt_lst = new sql_par_field_list();
        $fvt_lst->set($fld_val_typ_lst);
        $qp->sql = $sc->create_sql_update($id_fields, $id_lst, $fvt_lst);

        $qp->par = $sc->par_values();
        return $qp;
    }


    /*
     * sql write fields
     */

    /**
     * get a list of database fields that have been updated
     * excluding the internal only last_update and is_std fields
     *
     * @return array list of the database field names that have been updated
     */
    function db_fields_all(): array
    {
        $result = parent::db_fields_all_value();
        $result[] = self::FLD_SOURCE . group::FLD_ID;
        $result[] = formula::FLD_ID;
        return array_merge($result, $this->db_fields_all_sandbox());
    }

    /**
     * get a list of database field names, values and types that have been updated
     * excluding the internal only last_update and is_std fields
     *
     * @param result|sandbox_value $sbv the compare value to detect the changed fields
     * @return array list of the database field names that have been updated
     */
    function db_changed(result|sandbox_value $sbv): array
    {
        $lst = parent::db_changed_value($sbv);
        if ($sbv->src_grp_id() <> $this->src_grp_id()) {
            // TODO review type
            $lst[] = [
                self::FLD_SOURCE . group::FLD_ID,
                $this->src_grp_id(),
                sql_field_type::INT
            ];
        }
        if ($sbv->frm_id() <> $this->frm_id()) {
            $lst[] = [
                formula::FLD_ID,
                $this->frm_id(),
                formula::FLD_ID_SQLTYP
            ];
        }
        // if any field has been updated, update the last_update field also
        if (count($lst) == 0 or $this->last_update() == null) {
            $lst[] = [
                self::FLD_LAST_UPDATE,
                sql::NOW,
                sql_field_type::TIME
            ];
        }
        return array_merge($lst, $this->db_fields_changed($sbv));
    }

}