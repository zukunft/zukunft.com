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

    The main sections of this object are
    - construct and map: including the mapping of the db row to this word object
    - api:               create an api array for the frontend and set the vars based on a frontend api message


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

//include_once MODEL_SANDBOX_PATH . 'sandbox_value.php';
include_once DB_PATH . 'sql.php';
include_once DB_PATH . 'sql_creator.php';
include_once DB_PATH . 'sql_db.php';
include_once DB_PATH . 'sql_field_default.php';
include_once DB_PATH . 'sql_field_type.php';
include_once DB_PATH . 'sql_par.php';
include_once DB_PATH . 'sql_par_field_list.php';
include_once DB_PATH . 'sql_type.php';
include_once DB_PATH . 'sql_type_list.php';
include_once MODEL_ELEMENT_PATH . 'element_list.php';
include_once EXPORT_PATH . 'export.php';
include_once SERVICE_EXPORT_PATH . 'result_exp.php';
include_once MODEL_FORMULA_PATH . 'expression.php';
include_once MODEL_FORMULA_PATH . 'figure.php';
include_once MODEL_FORMULA_PATH . 'formula.php';
include_once MODEL_GROUP_PATH . 'group.php';
include_once MODEL_GROUP_PATH . 'group_id.php';
include_once MODEL_GROUP_PATH . 'group_list.php';
include_once MODEL_HELPER_PATH . 'data_object.php';
include_once MODEL_HELPER_PATH . 'db_object_multi.php';
include_once MODEL_PHRASE_PATH . 'phrase_list.php';
include_once MODEL_SANDBOX_PATH . 'sandbox.php';
include_once MODEL_SANDBOX_PATH . 'sandbox_multi.php';
include_once MODEL_SANDBOX_PATH . 'sandbox_value.php';
include_once MODEL_USER_PATH . 'user.php';
include_once MODEL_USER_PATH . 'user_message.php';
include_once MODEL_VALUE_PATH . 'value_base.php';
include_once SHARED_CONST_PATH . 'chars.php';
include_once SHARED_CALC_PATH . 'parameter_type.php';
include_once SHARED_ENUM_PATH . 'messages.php';
include_once SHARED_TYPES_PATH . 'api_type_list.php';
include_once SHARED_PATH . 'json_fields.php';
include_once SHARED_PATH . 'library.php';

use cfg\db\sql;
use cfg\db\sql_creator;
use cfg\db\sql_db;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
use cfg\db\sql_par;
use cfg\db\sql_par_field_list;
use cfg\db\sql_type;
use cfg\db\sql_type_list;
use cfg\element\element_list;
use cfg\formula\figure;
use cfg\formula\formula;
use cfg\group\group;
use cfg\group\group_id;
use cfg\group\group_list;
use cfg\helper\data_object;
use cfg\helper\db_object_multi;
use cfg\phrase\phrase_list;
use cfg\sandbox\sandbox;
use cfg\sandbox\sandbox_multi;
use cfg\sandbox\sandbox_value;
use cfg\user\user;
use cfg\user\user_message;
use DateTime;
use shared\calc\parameter_type;
use shared\const\chars;
use shared\enum\messages as msg_id;
use shared\json_fields;
use shared\library;
use shared\types\api_type_list;

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
        [sandbox_multi::FLD_LAST_UPDATE, sql_field_type::TIME, sql_field_default::NULL, '', '', 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation'],
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
    // TODO use the is_std of the sandbox_value object
    public ?bool $is_std = True;        // true as long as no user specific value, formula or assignment is used for this result

    // database related variables
    private ?float $number = null;

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
        parent::reset();
        $this->frm = new formula($this->user());
        $this->set_grp(new group($this->user()));
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
            $this->set_number($db_row[self::FLD_VALUE]);
            $this->set_owner_id($db_row[user::FLD_ID]);
            $this->last_update = $lib->get_datetime($db_row[self::FLD_LAST_UPDATE]);
            $this->last_val_update = $lib->get_datetime($db_row[self::FLD_LAST_UPDATE]);

            $this->load_phrases(true);
        }

        return $result;
    }

    /**
     * TODO move the common parts to the parent object
     * map a result api json to this model result object
     * @param array $api_json the api array with the values that should be mapped
     */
    function api_mapper(array $api_json): user_message
    {
        // make sure that there are no unexpected leftovers but keep the user
        $usr = $this->user();
        $this->reset();
        $this->set_user($usr);

        $usr_msg = parent::api_mapper($api_json);

        if (array_key_exists(json_fields::PHRASES, $api_json)) {
            $phr_lst = new phrase_list($this->user());
            $usr_msg->add($phr_lst->api_mapper($api_json[json_fields::PHRASES]));
            if ($usr_msg->is_ok()) {
                $this->grp()->set_phrase_list($phr_lst);
            }
        }
        if (array_key_exists(json_fields::ID, $api_json)) {
            $this->set_id($api_json[json_fields::ID]);
        }
        if (array_key_exists(json_fields::NUMBER, $api_json)) {
            $value = $api_json[json_fields::NUMBER];
            if (is_numeric($value)) {
                $this->set_value($value);
            } else {
                $usr_msg->add_id_with_vars(msg_id::IMPORT_VALUE_NOT_NUMERIC, [
                    msg_id::VAR_VALUE => $value,
                    msg_id::VAR_GROUP => $this->grp()->dsp_id()
                ]);
            }
        }

        return $usr_msg;
    }

    /**
     * set the vars of this value object based on the given json without writing to the database
     * TODO import the description and save it in the group description
     *
     * @param array $in_ex_json an array with the data of the json object
     * @param data_object|null $dto cache of the objects imported until now for the primary references
     * @param object|null $test_obj if not null the unit test object to get a dummy seq id
     * @return user_message the status of the import and if needed the error messages that should be shown to the user
     */
    function import_mapper(array $in_ex_json, data_object $dto = null, object $test_obj = null): user_message
    {

        $usr_msg = parent::import_mapper($in_ex_json, $dto, $test_obj);

        if (key_exists(json_fields::WORDS, $in_ex_json)) {
            $phr_lst = new phrase_list($this->user());
            $usr_msg->add($phr_lst->import_mapper($in_ex_json[json_fields::WORDS], $dto, $test_obj));
            if ($usr_msg->is_ok()) {
                $phr_grp = $phr_lst->get_grp_id(false);
                $this->set_grp($phr_grp);
            }
        }

        if (key_exists(json_fields::FORMULA_NAME, $in_ex_json)) {
            $frm_name = $in_ex_json[json_fields::FORMULA_NAME];
            $frm = $dto->formula_list()->get_by_name($frm_name);
            if ($frm == null) {
                $usr_msg->add_id_with_vars(msg_id::FORMULA_MISSING_IMPORT, [
                    msg_id::VAR_FORMULA => $frm_name,
                    msg_id::VAR_JSON_TEXT => $in_ex_json
                ]);
                $frm = new formula($this->user());
                $frm->set_name($frm_name);
                $dto->source_list()->add_by_name($frm);
            }
            $this->frm = $frm;
        }

        return $this->common_mapper($in_ex_json, $usr_msg);
    }

    /**
     * set the vars of this value object based on the given json
     * that are the same for the api and the import mapper
     *
     * @param array $in_ex_json an array with the data of the json object
     * @param user_message $usr_msg the user message object to remember the message that should be shown to the user
     * @return user_message the enriched user message
     */
    private function common_mapper(
        array        $in_ex_json,
        user_message $usr_msg
    ): user_message
    {
        $lib = new library();

        if (key_exists(json_fields::TIMESTAMP, $in_ex_json)) {
            $value = $in_ex_json[json_fields::TIMESTAMP];
            if (strtotime($value)) {
                $this->time_stamp = $lib->get_datetime($value, $this->dsp_id(), 'JSON import');
            } else {
                $usr_msg->add_id_with_vars(msg_id::CANNOT_ADD_TIMESTAMP,
                    [msg_id::VAR_VALUE => $value, msg_id::VAR_ID => $this->dsp_id()]
                );
            }
        }

        if (key_exists(json_fields::NUMBER, $in_ex_json)) {
            $value = $in_ex_json[json_fields::NUMBER];
            if (is_numeric($value)) {
                $this->set_value($value);
            } else {
                $usr_msg->add_id_with_vars(msg_id::IMPORT_VALUE_NOT_NUMERIC,
                    [msg_id::VAR_VALUE => $value, msg_id::VAR_GROUP => $this->grp()->dsp_id()]
                );
            }
        }

        return $usr_msg;
    }


    /*
     * api
     */

    /**
     * create an array for the api json creation
     * differs from the export array by using the internal id instead of the names
     * @param api_type_list $typ_lst configuration for the api message e.g. if phrases should be included
     * @param user|null $usr the user for whom the api message should be created which can differ from the session user
     * @return array the filled array used to create the api json message to the frontend
     */
    function api_json_array(api_type_list $typ_lst, user|null $usr = null): array
    {
        $vars = parent::api_json_array($typ_lst, $usr);

        // add the source phrases if requested
        if ($typ_lst->include_phrases()) {
            $phr_lst = $this->source_group()->phrase_list();
            $vars[json_fields::CONTEXT] = $phr_lst->api_json_array($typ_lst);
        }

        // add the formula that has created the result
        if ($this->formula_id() != null) {
            $vars[json_fields::FORMULA_ID] = $this->formula_id();
        }

        // add the numeric string itself
        $vars[json_fields::NUMBER] = $this->value();

        return $vars;
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

    function set_value(float|DateTime|string|null $val): void
    {
        $this->number = $val;
    }

    function value(): float|DateTime|string|null
    {
        return $this->number;
    }

    function set_src_grp(group $grp): void
    {
        $this->src_grp = $grp;
    }

    function source_group(): group
    {
        return $this->src_grp;
    }

    function src_grp_id(): int|string
    {
        return $this->source_group()->id();
    }

    function set_formula(formula $frm): void
    {
        $this->frm = $frm;
    }

    function formula_id(): int
    {
        return $this->frm->id();
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
        return $this->grp()->phrase_list();
    }

    /**
     * @return array with the phrase names of this value from the phrase group
     */
    function phr_names(): array
    {
        return $this->grp()->phrase_list()->names();
    }


    /*
     * load
     */

    /**
     * create the SQL to load the single default result always by the id
     * @param sql_creator $sc with the target db_type set
     * @param array $fld_lst list of fields either for the value or the result
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_standard_sql(sql_creator $sc, array $fld_lst = []): sql_par
    {
        $fld_lst = array_merge(self::FLD_NAMES, array(user::FLD_ID));
        return parent::load_standard_sql($sc, $fld_lst);
    }

    /**
     * fill the sql creator with the parameter the SQL to load results
     * from one of the tables with results
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the unique name of the query e.g. id or name
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

        // overwrite the standard id field name (result_id) with the main database id field for results "group_id"
        $sc->set_id_field($this->id_field($sc_par_lst));
        $sc->set_name($qp->name);
        $sc->set_usr($this->user()->id());
        $sc->set_fields(self::FLD_NAMES);

        return $qp;
    }

    /**
     * create the SQL to load a results by phrase group id
     *
     * @param sql_creator $sc with the target db_type set
     * @param group $grp the group used for the selection
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_grp(sql_creator $sc, group $grp, string $class = self::class): sql_par
    {
        return parent::load_sql_by_grp($sc, $grp, $class);
    }

    /**
     * prepare the query parameter to load a results by phrase group id
     *
     * @param sql_creator $sc with the target db_type set
     * @param group $grp the group used for the selection
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    private function load_sql_by_grp_prepare(sql_creator $sc, group $grp): sql_par
    {
        $qp = $this->load_sql($sc, 'grp');
        $sc->set_name($qp->name);
        $sc->add_where(self::FLD_GRP, $grp->id());
        return $qp;
    }

    /**
     * create the SQL to load a default results for all users by phrase group id
     *
     * @param sql_creator $sc with the target db_type set
     * @param group $grp the group used for the selection
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_std_by_grp(sql_creator $sc, group $grp): sql_par
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
     * @param sql_creator $sc with the target db_type set
     * @param formula $frm the formula used for the selection
     * @param group $grp the group used for the selection
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_frm_grp(sql_creator $sc, formula $frm, group $grp): sql_par
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
     * @param sql_creator $sc with the target db_type set
     * @param formula $frm the formula used for the selection
     * @param group_list $lst the group used for the selection
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_frm_grp_lst(sql_creator $sc, formula $frm, group_list $lst): sql_par
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
     * load (or force reload from database of) a result by the id
     *
     * @param int|string $id the unique database id of the result that should be loaded
     * @param ?sql_type $typ if known the value data type to preselect the table
     * @return int true if result has been loaded
     */
    function load_by_id(
        int|string $id = 0,
        ?sql_type  $typ = null
    ): int|string
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
            if ($this->id() != 0) {
                $id = $this->id();
                $res_usr = $this->user();
                $this->reset();
                $this->set_user($res_usr);
                $this->set_id($id);
            } else {
                log_err('The result id and the user must be set ' .
                    'to load a ' . $this::class, $this::class . '->load_by_id');
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
     * @param bool $by_source set to true to force the selection e.g. by source phrase group id
     * @return bool true if result has been loaded
     */
    function load_by_grp(group $grp, bool $by_source = false): bool
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
    function load_by_phr_lst(phrase_list $phr_lst): bool
    {
        $result = false;

        if ($phr_lst->is_valid()) {
            $res_usr = $this->user();
            $this->reset();
            $this->set_user($res_usr);
            $grp = $phr_lst->get_grp_id();
            $result = $this->load_by_grp($grp);
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
        if ($this->grp()->is_id_set()) {
            if ($this->grp()->phrase_list() == null or $force_reload) {
                log_debug('for group "' . $this->grp()->id() . '"');
                $phr_grp = new group($this->user());
                $phr_grp->load_by_id($this->grp()->id());
                if (!$phr_grp->phrase_list()->empty()) {
                    $this->grp()->set_phrase_list($phr_grp->phrase_list());
                    log_debug('phrases ' . $this->grp()->phrase_list()->dsp_name() . ' loaded');
                } else {
                    log_debug('no result phrases found for ' . $this->dsp_id());
                }
            }
        }
        if ($this->grp()->phrase_list() != null) {
            if ($this->grp()->phrase_list()->empty()) {
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
        if ($this->id() > 0) {
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
            $frm->load_by_id($this->frm->id());
            $this->frm = $frm;
        }
    }


    /*
     * im- and export
     */

    /**
     * validate a formulas value by comparing the external object result with the calculated result
     *
     * @param array $in_ex_json an array with the data of the json object
     * @param object|null $test_obj if not null the unit test object to get a dummy seq id
     * @return user_message the status of the import and if needed the error messages that should be shown to the user
     */
    function import_obj(array $in_ex_json, object $test_obj = null): user_message
    {
        log_debug();
        $result = parent::import_db_obj($this, $test_obj);

        if ($test_obj) {
            $do_save = false;
        } else {
            $do_save = true;
        }

        foreach ($in_ex_json as $key => $res) {

            // the phrases of the result
            if ($key == json_fields::WORDS) {
                $phr_lst = new phrase_list($this->user());
                $result->add($phr_lst->import_lst($res, $test_obj));
                if ($result->is_ok()) {
                    $phr_grp = $phr_lst->get_grp_id($do_save);
                    log_debug('got word group ' . $phr_grp->dsp_id());
                    $this->set_grp($phr_grp);
                    log_debug('set grp id to ' . $this->grp_id());
                }
            }

            // the phrases used to calculate the result
            if ($key == json_fields::CONTEXT) {
                $phr_lst = new phrase_list($this->user());
                $result->add($phr_lst->import_lst($res, $test_obj));
                if ($result->is_ok()) {
                    $phr_grp = $phr_lst->get_grp_id($do_save);
                    log_debug('got context ' . $phr_grp->dsp_id());
                    $this->set_src_grp($phr_grp);
                    log_debug('set grp id to ' . $this->src_grp_id());
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

            if ($key == json_fields::NUMBER) {
                $this->set_number($res);
            }

        }

        // save the result in the database
        if (!$test_obj) {
            if ($result->is_ok()) {
                $this->save()->get_last_message();
                log_debug($this->dsp_id());
            } else {
                log_debug($result->all_message_text());
            }
        }

        return $result;
    }

    /**
     * create an array with the export json fields of the result
     * to enable the validation of the results during import
     * @param bool $do_load to switch off the database load for unit tests
     * @return array the filled array used to create the user export json
     */
    function export_json(bool $do_load = true): array
    {
        $vars = parent::export_json($do_load);

        // the formula that has created the result
        $vars[json_fields::FORMULA_NAME] = $this->frm->name();

        // TODO add source group

        // add the value itself
        $vars[json_fields::NUMBER] = $this->number();

        return $vars;
    }


    /*
       methods to prepare the words for saving into the database
       ---------------------------------------------------------
    */

    // update the source word group id based on the word list ($this->grp()->phrase_list())
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

    // update the word group id based on the word list ($this->grp()->phrase_list())
    private function save_prepare_phr_lst(): void
    {
        if ($this->grp()->phrase_list()->is_empty()) {
            // get the word group id (and create the group if needed)
            // TODO include triples
            $grp = new group($this->user());
            $grp->load_by_phr_lst($this->grp()->phrase_list());
            $this->grp()->set_id($grp->get_id());
            log_debug("group id " . $this->grp()->id() . " for " . $this->grp()->phrase_list()->dsp_name() . ".");
        }
    }

    // update the word ids based on the word objects (usually done before saving the formula result to the database)
    private function save_prepare_wrds(): void
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

        if (!is_null($this->number())) {
            log_debug('result->val_formatted');
            if ($this->grp()->phrase_list() == null) {
                $this->load_phrases();
                log_debug('result->val_formatted loaded');
            }
            log_debug('result->val_formatted check ' . $this->dsp_id());
            if ($this->grp()->phrase_list()->has_percent()) {
                $result = round($this->number() * 100, $this->user()->percent_decimals) . ' %';
                log_debug('result->val_formatted percent of ' . $this->number());
            } else {
                if ($this->number() >= 1000 or $this->number() <= -1000) {
                    log_debug('result->val_formatted format');
                    $result .= number_format($this->number(), 0, $this->user()->dec_point, $this->user()->thousand_sep);
                } else {
                    log_debug('result->val_formatted round');
                    $result = round($this->number(), 2);
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
        if ($this->grp()->phrase_list() != null) {
            $id_lst = $this->grp()->phrase_list()->id_lst();
        }
        return $id_lst;
    }


    /*
     * modify
     */

    /**
     * fill this sandbox object based on the given object
     *
     * @param sandbox_value|db_object_multi $obj sandbox object with the values that should be updated e.g. based on the import
     * @param user $usr_req the user who has requested the fill
     * @return user_message a warning in case of a conflict e.g. due to a missing change time
     */
    function fill(sandbox_value|db_object_multi $obj, user $usr_req): user_message
    {
        $usr_msg = parent::fill($obj, $usr_req);
        if ($obj->src_grp_id() != 0) {
            $this->set_src_grp($obj->source_group());
        }
        if ($obj->formula_id() != 0) {
            $this->set_formula($obj->frm);
        }
        if ($obj->value() != null) {
            $this->set_value($obj->value());
        }
        return $usr_msg;
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

        if (!$this->grp()->phrase_list()->is_empty()) {
            $result .= $this->grp()->phrase_list()->dsp_name();
        }

        return $result;
    }

    function name_linked(): string
    {
        $result = '';

        if (!$this->grp()->phrase_list()->is_empty()) {
            $result .= $this->grp()->phrase_list()->name_linked();
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
        log_debug("(f" . $this->frm->id() . ",t" . $lib->dsp_array($this->phr_ids()) . ",v" . $this->number() . " and user " . $this->user()->name . ")");

        global $db_con;
        $result = array();

        // get depending formulas
        $frm_elm_lst = new element_list($this->user());
        $frm_elm_lst->load_by_frm_and_type_id($this->frm->id(), parameter_type::FORMULA_ID);
        $frm_ids = array();
        foreach ($frm_elm_lst as $frm_elm) {
            if ($frm_elm->obj != null) {
                $frm_ids[] = $frm_elm->obj->id();
            }
        }
        // get formula results that may need an update (maybe include also word groups that have any word of the updated word group)
        if (!empty($frm_ids)) {
            $sql_in = $lib->sql_array($frm_ids, ' formula_id IN (', ') ');
            $sql = "SELECT group_id, formula_id
                FROM results 
               WHERE " . $sql_in . "
                 AND group_id = " . $this->grp()->id() . "
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
                $result[] = $res_upd->save()->get_last_message();
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
            log_debug('result->update ' . $this->dsp_id() . ' to ' . $this->number() . ' done');
        }
    }

    private function save_without_time(): string
    {
        $res_no_time = clone $this;
        // $res_no_time->time_phr = null;
        return $res_no_time->save()->get_last_message();
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
        $phr_lst_ex_time = $res_check->grp()->phrase_list();
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
                if (!$this->grp()->phrase_list()->is_empty()) {
                    log_warning('The result phrases for ' . $this->dsp_id() . ' are missing.', 'result->save_if_updated');
                }
                if (!$this->src_grp->phrase_list()->is_empty()) {
                    log_warning('The source phrases for ' . $this->dsp_id() . ' are missing.', 'result->save_if_updated');
                }

                // add the formula name word, but not is the result words are defined in the formula
                // e.g. if the formula "country weight" is calculated the word "country weight" should be added to the result values
                if (!$has_result_phrases) {
                    log_debug('add the formula name ' . $this->frm->dsp_id() . ' to the result phrases ' . $this->grp()->phrase_list()->dsp_id());
                    if ($this->frm != null) {
                        if ($this->frm->name_wrd != null) {
                            $this->grp()->phrase_list()->add($this->frm->name_wrd->phrase());
                        }
                    }
                }

                // e.g. if the formula is a division and the values used have a measure word like meter or CHF, the result is only in percent, but not in meter or CHF
                // simplified version, that needs to be review to handle more complex formulas
                if (strpos($this->frm->ref_text_r, chars::DIV) !== false) {
                    log_debug('check measure ' . $this->grp()->phrase_list()->dsp_id());
                    if ($this->grp()->phrase_list()->has_measure()) {
                        $this->grp()->phrase_list()->ex_measure();
                        log_debug('measure removed from words ' . $this->grp()->phrase_list()->dsp_id());
                    }
                }

                // build the formula result object
                //$this->frm_id = $this->frm->id();
                //$this->user()->id() = $frm_result->result_user;
                log_debug('save "' . $this->number() . '" for ' . $this->grp()->phrase_list()->dsp_id());

                // get the default time for the phrases e.g. if the increase for ABB sales is calculated the last reported sales increase is assumed
                $lst_ex_time = $this->grp()->phrase_list();
                $lst_ex_time->ex_time();
                $res_default_time = $lst_ex_time->assume_time(); // must be the same function called used in 2num
                if (isset($res_default_time)) {
                    log_debug('save "' . $this->number() . '" for ' . $this->grp()->phrase_list()->dsp_id() . ' and default time ' . $res_default_time->dsp_id());
                } else {
                    log_debug('save "' . $this->number() . '" for ' . $this->grp()->phrase_list()->dsp_id());
                }

                if ($this->number() == null) {
                    log_info('No result calculated for "' . $this->frm->name() . '" based on ' . $this->src_grp->phrase_list()->dsp_id() . ' for user ' . $this->user()->id() . '.', "result->save_if_updated");
                } else {
                    // save the default value if the result time is the "newest"
                    if (isset($res_default_time)) {
                        log_debug('check if result time ' . $this->grp()->time()->dsp_id() . ' is the default time ' . $res_default_time->dsp_id());
                        if ($this->grp()->time()->id() == $res_default_time->id()) {
                            // if there is not yet a general value for all user, save it now
                            $result .= $this->save_without_time();
                        }
                    }

                    // save the value without time if no value without time is yet saved for the phrase group
                    if ($this->has_no_time_value()) {
                        $result .= $this->save_without_time();
                    }

                    // save the result
                    $this->save()->get_last_message();
                    $res_id = $this->id();

                    if ($debug > 0) {
                        $debug_txt = 'result = ' . $this->number() . ' saved for ' . $this->grp()->phrase_list()->name_linked();
                        if ($debug > 3) {
                            $debug_txt .= ' (group id "' . $this->grp()->id() . '" as id "' . $res_id . '" based on ' . $this->src_grp->phrase_list()->name_linked() . ' (group id "' . $this->src_grp->dsp_id() . ')';
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
     * @param bool|null $use_func if true a predefined function is used that also creates the log entries
     * @return user_message the message that should be shown to the user in case something went wrong
     */
    function save(?bool $use_func = null): user_message
    {

        global $db_con;
        global $debug;
        $usr_msg = new user_message();

        // check the parameters e.g. a result must always be linked to a formula
        if ($this->frm->id() <= 0) {
            log_err("Formula id missing.", "result->save");
        } elseif (empty($this->grp()->phrase_list())) {
            log_err("No words for the result.", "result->save");
        } elseif (empty($this->src_grp->phrase_list())) {
            log_err("No words for the calculation.", "result->save");
        } elseif ($this->user() == null) {
            log_err("User missing.", "result->save");
        } else {
            if ($debug > 0) {
                $debug_txt = 'result->save (' . $this->number() . ' for formula ' . $this->frm->id() . ' with ' . $this->grp()->phrase_list()->dsp_name() . ' based on ' . $this->src_grp->phrase_list()->dsp_name();
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
            log_debug("group id " . $this->grp()->id() . " and source group id " . $this->src_grp->dsp_id());

            // check if a database update is needed
            // or if a second results object with the database values
            $res_db = new result($this->user());
            $res_db->load_by_id($this->id());
            $row_id = $res_db->id();
            $db_val = $res_db->number();

            // if value exists, check it an update is needed
            // updates of results are not logged because they could be reproduced
            if ($row_id > 0) {
                if ($db_con->sf($db_val) <> $db_con->sf($this->number())) {
                    $msg = 'update result ' . sandbox_multi::FLD_VALUE . ' to ' . $this->number()
                        . ' from ' . $db_val . ' for ' . $this->dsp_id();
                    log_debug($msg);
                    $db_con->set_class(result::class);
                    $sc = $db_con->sql_creator();
                    $qp = $this->sql_update($sc, $res_db);
                    $upd_msg = $db_con->update($qp, $msg);
                    if ($upd_msg->is_ok()) {
                        $usr_msg->set_db_row_id($row_id);
                    }
                } else {
                    $msg = 'update of result ' . sandbox_multi::FLD_VALUE . ' ' . $this->dsp_id() . ' not needed';
                    log_debug($msg);
                    $this->id = $row_id;
                    $usr_msg->set_db_row_id($row_id);
                }
            } else {
                $msg = 'insert result ' . $this->number() . ' for ' . $this->dsp_id();
                $field_names = array();
                $field_values = array();
                $field_names[] = formula::FLD_ID;
                $field_values[] = $this->frm->id();
                $field_names[] = sandbox_multi::FLD_VALUE;
                $field_values[] = $this->number();
                $field_names[] = result::FLD_GRP;
                $field_values[] = $this->grp()->id();
                $field_names[] = result::FLD_SOURCE_GRP;
                $field_values[] = $this->src_grp->id();
                if (!$this->is_std) {
                    $field_names[] = user::FLD_ID;
                    $field_values[] = $this->user()->id();
                }
                $field_names[] = sandbox_multi::FLD_LAST_UPDATE;
                //$field_values[] = sql::NOW; // replaced with time of last change that has been included in the calculation
                $field_values[] = $this->last_val_update->format('Y-m-d H:i:s');
                $db_con->set_class(result::class);
                $sc = $db_con->sql_creator();
                $qp = $this->sql_insert($sc);
                $upd_msg = $db_con->insert($qp, $msg);
                if ($upd_msg->is_ok()) {
                    $usr_msg->set_db_row_id($row_id);
                }
            }
        }

        log_debug("id (" . $usr_msg->get_row_id() . ")");
        return $usr_msg;

    }


    /*
     * sql write fields
     */

    /**
     * get a list of database fields that have been updated
     * excluding the internal only last_update and is_std fields
     *
     * @param sql_type_list $sc_par_lst only used for link objects
     * @return array list of the database field names that have been updated
     */
    function db_fields_all(sql_type_list $sc_par_lst = new sql_type_list()): array
    {
        $fields = parent::db_fields_all();
        if (!$sc_par_lst->is_standard()) {
            $fields[] = self::FLD_SOURCE . group::FLD_ID;
            $fields[] = formula::FLD_ID;
            $fields = array_merge($fields, $this->db_fields_all_sandbox());
        }
        return $fields;
    }

    /**
     * get a list of database field names, values and types that have been updated
     * the last_update field is excluded here because this is an internal only field
     *
     * @param sandbox_multi|sandbox_value|result $sbx the same value sandbox as this to compare which fields have been changed
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par_field_list with the field names of the object and any child object
     */
    function db_fields_changed(
        sandbox_multi|sandbox_value|result $sbx,
        sql_type_list                      $sc_par_lst = new sql_type_list()
    ): sql_par_field_list
    {
        $lst = parent::db_fields_changed($sbx, $sc_par_lst);
        if (!$sc_par_lst->is_standard()) {
            if ($sbx->src_grp_id() <> $this->src_grp_id()) {
                $lst->add_field(
                    self::FLD_SOURCE . group::FLD_ID,
                    $this->src_grp_id(),
                    sql_field_type::INT
                );
            }
            if ($sbx->formula_id() <> $this->formula_id()) {
                $lst->add_field(
                    formula::FLD_ID,
                    $this->formula_id(),
                    formula::FLD_ID_SQL_TYP
                );
            }
            // if any field has been updated, update the last_update field also
            if (!$lst->is_empty_except_internal_fields() or $this->last_update() == null) {
                $lst->add_field(
                    self::FLD_LAST_UPDATE,
                    sql::NOW,
                    sql_field_type::TIME
                );
            }
        }
        return $lst->merge($this->db_changed_sandbox_list($sbx, $sc_par_lst));
    }

}