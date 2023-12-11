<?php

/*

    model/sandbox/sandbox_value.php - the superclass for handling user specific link objects including the database saving
    -------------------------------

    This superclass should be used by the class word links, formula links and view link


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

namespace cfg;

include_once MODEL_SANDBOX_PATH . 'sandbox_multi.php';
include_once MODEL_GROUP_PATH . 'group.php';

use cfg\db\sql;
use cfg\db\sql_db;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
use cfg\db\sql_par;
use cfg\group\group;
use cfg\group\group_id;
use cfg\log\change;
use cfg\log\change_log_action;
use cfg\log\change_log_field;
use cfg\log\change_log_link;
use cfg\value\value;
use DateTime;
use Exception;

class sandbox_value extends sandbox_multi
{

    // the table name extension for public unprotected values related up to four prime phrase
    const TBL_EXT_STD = '_standard';
    const TBL_COMMENT_STD = 'for public unprotected ';
    const TBL_COMMENT_STD_PRIME_CONT = 's related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated';
    const TBL_COMMENT_STD_CONT = 's that have never changed the owner, does not have a description and are rarely updated';
    const TBL_COMMENT = 'for ';
    const TBL_COMMENT_CONT = 's related to up to 16 phrases';
    const TBL_COMMENT_USER = 'for user specific changes of ';
    const TBL_COMMENT_PRIME = 'for the most often requested ';
    const TBL_COMMENT_PRIME_CONT = 's related up to four prime phrase';
    const TBL_COMMENT_PRIME_USER = 'to store the user specific changes for the most often requested ';
    const TBL_COMMENT_PRIME_USER_CONT = 's related up to four prime phrase';
    const TBL_COMMENT_BIG_CONT = 's related to more than 16 phrases';
    const TBL_COMMENT_BIG_USER = 'to store the user specific changes of ';
    const TBL_COMMENT_BIG_USER_CONT = 's related to more than 16 phrases';
    const TYPE_NUMBER = 'numeric';
    const TYPE_TEXT = 'text';
    const TYPE_TIME = 'time';
    const TYPE_GEO = 'geo';

    // the database field names used for all value tables e.g. also for results
    const FLD_ID_PREFIX = 'phrase_id_';


    // field lists for the table creation
    // the group is not a foreign key, because if the name is not changed by the user an entry in the group table is not needed
    const FLD_KEY = array(
        [group::FLD_ID, sql_field_type::KEY_512, sql_field_default::NOT_NULL, '', '', 'the 512-bit prime index to find the'],
    );
    const FLD_KEY_USER = array(
        [group::FLD_ID, sql_field_type::KEY_PART_512, sql_field_default::NOT_NULL, '', '', 'the 512-bit prime index to find the user'],
    );
    // TODO use not null for all keys if a separate table for each number of phrase is implemented
    // TODO FLD_KEY_PRIME and FLD_KEY_PRIME_USER are not the same only if just one phrase is the key
    const FLD_KEY_PRIME = array(
        [sandbox_value::FLD_ID_PREFIX . '1', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::NOT_NULL, sql::INDEX, '', 'phrase id that is part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '2', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::NULL, sql::INDEX, '', 'phrase id that is part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '3', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::NULL, sql::INDEX, '', 'phrase id that is part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '4', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::NULL, sql::INDEX, '', 'phrase id that is part of the prime key for a'],
    );
    const FLD_KEY_PRIME_USER = array(
        [sandbox_value::FLD_ID_PREFIX . '1', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::NOT_NULL, sql::INDEX, '', 'phrase id that is with the user id part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '2', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::NULL, sql::INDEX, '', 'phrase id that is with the user id part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '3', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::NULL, sql::INDEX, '', 'phrase id that is with the user id part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '4', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::NULL, sql::INDEX, '', 'phrase id that is with the user id part of the prime key for a'],
    );
    const FLD_KEY_BIG = array(
        [group::FLD_ID, sql_field_type::KEY_TEXT, sql_field_default::NOT_NULL, '', '', 'the variable text index to find'],
    );
    const FLD_KEY_BIG_USER = array(
        [group::FLD_ID, sql_field_type::KEY_PART_TEXT, sql_field_default::NOT_NULL, '', '', 'the text index for more than 16 phrases to find the'],
    );
    const FLD_ALL_VALUE_NUM = array(
        [value::FLD_VALUE, sql_field_type::NUMERIC_FLOAT, sql_field_default::NOT_NULL, '', '', 'the numeric value given by the user'],
    );
    const FLD_ALL_VALUE_NUM_USER = array(
        [value::FLD_VALUE, sql_field_type::NUMERIC_FLOAT, sql_field_default::NULL, '', '', 'the user specific numeric value change'],
    );
    const FLD_ALL_VALUE_TEXT = array(
        [value::FLD_VALUE_TEXT, sql_field_type::TEXT, sql_field_default::NOT_NULL, '', '', 'the text value given by the user'],
    );
    const FLD_ALL_VALUE_TIME = array(
        [value::FLD_VALUE_TIME, sql_field_type::TIME, sql_field_default::NOT_NULL, '', '', 'the timestamp given by the user'],
    );
    const FLD_ALL_VALUE_GEO = array(
        [value::FLD_VALUE_GEO, sql_field_type::GEO, sql_field_default::NOT_NULL, '', '', 'the geolocation given by the user'],
    );
    const FLD_ALL_VALUE_TEXT_USER = array(
        [value::FLD_VALUE_TEXT, sql_field_type::TEXT, sql_field_default::NULL, '', '', 'the user specific text value change'],
    );
    const FLD_ALL_VALUE_TIME_USER = array(
        [value::FLD_VALUE_TIME, sql_field_type::TIME, sql_field_default::NULL, '', '', 'the user specific timestamp change'],
    );
    const FLD_ALL_VALUE_GEO_USER = array(
        [value::FLD_VALUE_GEO, sql_field_type::GEO, sql_field_default::NULL, '', '', 'the user specific geolocation change'],
    );
    const FLD_ALL_SOURCE = array(
        [source::FLD_ID, sql_field_type::INT, sql_field_default::NULL, sql::INDEX, source::class, 'the source of the value as given by the user'],
    );
    const FLD_ALL_CHANGED = array(
        [value::FLD_LAST_UPDATE, sql_field_type::TIME, sql_field_default::NULL, '', '', 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation'],
    );
    const FLD_ALL_SOURCE_GROUP = array();
    const FLD_ALL_SOURCE_GROUP_PRIME = array();
    const FLD_ALL_SOURCE_GROUP_BIG = array();
    const FLD_ALL_OWNER = array(
        [user::FLD_ID, sql_field_type::INT, sql_field_default::NULL, sql::INDEX, user::class, 'the owner / creator of the value'],
    );
    const FLD_ALL_CHANGER = array(
        [user::FLD_ID, sql_field_type::KEY_PART_INT, sql_field_default::NOT_NULL, sql::INDEX, user::class, 'the changer of the '],
    );


    /*
     * object vars
     */

    // database fields only used for the value object
    public group $grp;  // phrases (word or triple) group object for this value
    protected ?float $number; // simply the numeric value
    private ?DateTime $last_update = null; // the time of the last update of fields that may influence the calculated results; also used to detect if the value has been saved


    /*
     * construct and map
     */

    /**
     * all value user specific, that's why the user is always set
     */
    function __construct(user $usr)
    {
        parent::__construct($usr);
        $this->reset();
    }

    function reset(): void
    {
        parent::reset();
        $this->set_grp(new group($this->user()));
        $this->set_number(null);
        $this->set_last_update(null);
    }

    /**
     * map the database fields to the object fields
     * to be extended by the child functions
     *
     * @param array|null $db_row with the data directly from the database
     * @param string $ext the table type e.g. to indicate if the id is int
     * @param string $id_fld the name of the id field as set in the child class
     * @return bool true if the user sandbox object is loaded and valid
     */
    function row_mapper_multi(?array $db_row, string $ext, string $id_fld = ''): bool
    {
        $this->set_last_update(null);
        return parent::row_mapper_multi($db_row, $id_fld);
    }


    /*
     * set and get
     */

    function set_grp(group $grp): void
    {
        $this->grp = $grp;
        $this->set_id($grp->id());
    }

    function grp(): group
    {
        return $this->grp;
    }

    /**
     * set the numeric value of the user sandbox object
     *
     * @param float|null $number the numeric value that should be saved in the database
     * @return void
     */
    function set_number(?float $number): void
    {
        $this->number = $number;
    }

    /**
     * @return float|null the numeric value
     */
    function number(): ?float
    {
        return $this->number;
    }

    /**
     * set the timestamp of the last update of this value
     *
     * @param DateTime|null $last_update the timestamp when this value has been updated eiter by the user or a calculatio job
     * @return void
     */
    function set_last_update(?DateTime $last_update): void
    {
        $this->last_update = $last_update;
    }

    /**
     * @return DateTime|null the timestamp when the user has last updated the value
     */
    function last_update(): ?DateTime
    {
        return $this->last_update;
    }


    /*
     * sql create
     */

    /**
     * the sql statements to create all tables used to store values in the database
     *
     * @param sql $sc with the target db_type set
     * @return string the sql statement to create the table
     */
    function sql_table(sql $sc): string
    {
        return $this->sql_creator($sc, 0);
    }

    /**
     * the sql statements to create all indices for the tables used to store values in the database
     *
     * @param sql $sc with the target db_type set
     * @return string the sql statement to create the indices of the value tables
     */
    function sql_index(sql $sc): string
    {
        return $this->sql_creator($sc, 1);
    }

    /**
     * the sql statements to create all foreign keys for the tables
     *
     * @param sql $sc with the target db_type set
     * @return string the sql statement to create the foreign keys of a value table
     */
    function sql_foreign_key(sql $sc): string
    {
        return $this->sql_creator($sc, 2);
    }

    /**
     * the sql statements to create either all tables ($pos = 0), the indices ($pos = 1) or the foreign keys ($pos = 2)
     * used to store values in the database
     *
     * @param sql $sc with the target db_type set
     * @return string the sql statement to create the table
     */
    private function sql_creator(sql $sc, int $pos): string
    {

        $sql_array = $this->sql_one_type(
            $sc,
            self::FLD_ALL_VALUE_NUM,
            self::FLD_ALL_VALUE_NUM_USER,
            '', $this::TYPE_NUMBER
        );
        $sql = $sql_array[$pos];
        $sql_array = $this->sql_one_type(
            $sc,
            self::FLD_ALL_VALUE_TEXT,
            self::FLD_ALL_VALUE_TEXT_USER,
            '_' . $this::TYPE_TEXT, $this::TYPE_TEXT
        );
        $sql .= $sql_array[$pos];
        $sql_array = $this->sql_one_type(
            $sc,
            self::FLD_ALL_VALUE_TIME,
            self::FLD_ALL_VALUE_TIME_USER,
            '_' . $this::TYPE_TIME, $this::TYPE_TIME
        );
        $sql .= $sql_array[$pos];
        $sql_array = $this->sql_one_type(
            $sc,
            self::FLD_ALL_VALUE_GEO,
            self::FLD_ALL_VALUE_GEO_USER,
            '_' . $this::TYPE_GEO, $this::TYPE_GEO
        );
        $sql .= $sql_array[$pos];
        return $sql;
    }

    /**
     * create the sql statements for a set (standard, prime and big) tables
     * for one field type e.g. numeric value, text values
     *
     * @param sql $sc
     * @param array $fld_par the parameters for the value field e.g. for a numeric field, text, time or geo
     * @param array $fld_par_usr the user specific parameters for the value field
     * @param string $ext_type the additional table extension for the field type
     * @param string $type_name the name of the value type
     * @return array the sql statements to create the tables, indices and foreign keys
     */
    protected function sql_one_type(
        sql    $sc,
        array  $fld_par,
        array  $fld_par_usr,
        string $ext_type = '',
        string $type_name = ''): array
    {
        $lib = new library();
        $type_name .= ' ' . $lib->class_to_name($this::class);

        $sql = $sc->sql_separator();
        $sql_index = $sc->sql_separator();
        $sql_foreign = $sc->sql_separator();

        $sc->set_class($this::class, false, $ext_type . self::TBL_EXT_STD . group_id::TBL_EXT_PRIME);
        $fields = array_merge(self::FLD_KEY_PRIME, $fld_par, $this::FLD_ALL_SOURCE);
        $sql .= $sc->table_create($fields, $type_name,
            $this::TBL_COMMENT_STD . $type_name . $this::TBL_COMMENT_STD_PRIME_CONT);
        $sql_index .= $sc->index_create($fields);
        $sql_foreign .= $sc->foreign_key_create($fields);
        $sc->set_class($this::class, false, $ext_type . self::TBL_EXT_STD);
        $fields = array_merge(self::FLD_KEY, $fld_par, $this::FLD_ALL_SOURCE);
        $sql .= $sc->table_create($fields, $type_name,
            $this::TBL_COMMENT_STD . $type_name . $this::TBL_COMMENT_STD_CONT);
        $sql_index .= $sc->index_create($fields);
        $sql_foreign .= $sc->foreign_key_create($fields);

        $sql .= $sc->sql_separator();
        $std_fields = array_merge(
            $fld_par,
            $this::FLD_ALL_SOURCE,
            $this::FLD_ALL_CHANGED,
            $this::FLD_ALL_OWNER,
            sandbox::FLD_ALL);
        $std_usr_fields = array_merge(
            $this::FLD_ALL_CHANGER,
            $fld_par_usr,
            $this::FLD_ALL_SOURCE,
            $this::FLD_ALL_CHANGED,
            sandbox::FLD_ALL);
        $fields = array_merge(self::FLD_KEY, $this::FLD_ALL_SOURCE_GROUP, $std_fields);
        $sc->set_class($this::class, false, $ext_type);
        $sql .= $sc->table_create($fields, $type_name, $this::TBL_COMMENT . $type_name . $this::TBL_COMMENT_CONT);
        $sql_index .= $sc->index_create($fields);
        $sql_foreign .= $sc->foreign_key_create($fields);
        $fields = array_merge(self::FLD_KEY_USER, $this::FLD_ALL_SOURCE_GROUP, $std_usr_fields);
        $sc->set_class($this::class, true, $ext_type);
        $sql .= $sc->table_create($fields, $type_name, $this::TBL_COMMENT_USER . $type_name . $this::TBL_COMMENT_CONT);
        $sql_index .= $sc->index_create($fields);
        $sql_foreign .= $sc->foreign_key_create($fields);

        $sql .= $sc->sql_separator();
        $fields = array_merge(self::FLD_KEY_PRIME, $this::FLD_ALL_SOURCE_GROUP_PRIME, $std_fields);
        $sc->set_class($this::class, false, $ext_type . group_id::TBL_EXT_PRIME);
        $sql .= $sc->table_create($fields, $type_name, $this::TBL_COMMENT_PRIME . $type_name . $this::TBL_COMMENT_PRIME_CONT);
        $sql_index .= $sc->index_create($fields);
        $sql_foreign .= $sc->foreign_key_create($fields);
        $fields = array_merge(self::FLD_KEY_PRIME_USER, $this::FLD_ALL_SOURCE_GROUP_PRIME, $std_usr_fields);
        $sc->set_class($this::class, true, $ext_type . group_id::TBL_EXT_PRIME);
        $sql .= $sc->table_create($fields, $type_name, $this::TBL_COMMENT_PRIME_USER . $type_name . $this::TBL_COMMENT_PRIME_USER_CONT);
        $sql_index .= $sc->index_create($fields);
        $sql_foreign .= $sc->foreign_key_create($fields);

        $sql .= $sc->sql_separator();
        $fields = array_merge(self::FLD_KEY_BIG, $this::FLD_ALL_SOURCE_GROUP_BIG, $std_fields);
        $sc->set_class($this::class, false, $ext_type . group_id::TBL_EXT_BIG);
        $sql .= $sc->table_create($fields, $type_name, $this::TBL_COMMENT . $type_name . $this::TBL_COMMENT_BIG_CONT);
        $sql_index .= $sc->index_create($fields);
        $sql_foreign .= $sc->foreign_key_create($fields);
        $fields = array_merge(self::FLD_KEY_BIG_USER, $this::FLD_ALL_SOURCE_GROUP_BIG, $std_usr_fields);
        $sc->set_class($this::class, true, $ext_type . group_id::TBL_EXT_BIG);
        $sql .= $sc->table_create($fields, $type_name, $this::TBL_COMMENT_BIG_USER . $type_name . $this::TBL_COMMENT_BIG_USER_CONT);
        $sql_index .= $sc->index_create($fields);
        $sql_foreign .= $sc->foreign_key_create($fields);

        return [$sql, $sql_index, $sql_foreign];
    }


    /*
     * load
     */

    /**
     * create an SQL statement to retrieve a value or result by id from the database
     *
     * @param sql $sc with the target db_type set
     * @param int|string $id the id of the value
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_id(sql $sc, int|string $id, string $class = self::class): sql_par
    {
        $this->grp()->set_id($id);
        return $this->load_sql_by_grp_id($sc, 'id', $class);
    }

    /**
     * create an SQL statement to retrieve a value by phrase group from the database
     *
     * @param sql $sc with the target db_type set
     * @param group $grp the id of the phrase group
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_grp(sql $sc, group $grp, string $class = self::class): sql_par
    {
        $this->set_grp($grp);
        return $this->load_sql_by_grp_id($sc, 'grp', $class);
    }

    /**
     * set the where condition and the final query parameters
     * for a value or result query
     *
     * @param sql_par $qp the query parameters fully set without the sql, par and ext
     * @param sql $sc the sql creator with all parameters set
     * @param string $ext the table extension
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    protected function load_sql_set_where(sql_par $qp, sql $sc, string $ext): sql_par
    {
        if ($this->grp->is_prime()) {
            $fields = $this->grp->id_names(phrase::FLD_ID . '_');
            $values = $this->grp->id_lst();
            $pos = 0;
            foreach ($fields as $field) {
                $sc->add_where($field, $values[$pos]);
                $pos++;
            }
        } else {
            $sc->add_where(group::FLD_ID, $this->grp->id());
        }

        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();
        $qp->ext = $ext;

        return $qp;
    }

    /**
     * create an SQL statement to retrieve the user changes of the current object
     *
     * @param sql $sc with the target db_type set
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_user_changes(sql $sc, string $class = self::class): sql_par
    {
        $qp = new sql_par($class);
        $qp->name .= 'usr_cfg';
        $sc->set_name($qp->name);
        $sc->set_usr($this->user()->id());
        $sc->set_fields($this->all_sandbox_fields());

        $fields = $this->grp->id_names(phrase::FLD_ID . '_');
        $values = $this->grp->id_lst();
        $pos = 0;
        foreach ($fields as $field) {
            $sc->add_where($field, $values[$pos]);
            $pos++;
        }

        $sc->add_where(user::FLD_ID, $this->user()->id());
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();
        return $qp;
    }

    /**
     * create an SQL statement to retrieve a value or result by already set phrase group
     *
     * @param sql $sc with the target db_type set
     * @param string $query_name the unique name of the query e.g. id or name
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    protected function load_sql_by_grp_id(sql $sc, string $query_name, string $class = self::class): sql_par
    {
        $tbl_ext = $this->grp->table_extension(true);
        $ext = $this->grp->table_extension();
        $qp = $this->load_sql_multi($sc, $query_name, $class, $ext, $tbl_ext);
        return $this->load_sql_set_where($qp, $sc, $ext);
    }

    /**
     * load the value parameters for all users
     * @param sql_par|null $qp the query parameter created by the function of the child object e.g. word->load_standard
     * @param string $class the name of the child class from where the call has been triggered
     * @return bool true if the standard object has been loaded
     */
    function load_standard(?sql_par $qp = null, string $class = ''): bool
    {
        global $db_con;

        $db_row = $db_con->get1($qp);
        return $this->row_mapper_sandbox_multi($db_row, $qp->ext, true, false);
    }


    /*
     * information
     */

    /**
     * @return bool true if the value has been at least once saved to the database
     */
    function is_saved(): bool
    {
        if ($this->last_update() == null) {
            return false;
        } else {
            return true;
        }
    }


    /*
     * cast
     */

    /**
     * @param object $api_obj frontend API object filled with the database id
     */
    function fill_api_obj(object $api_obj): void
    {
        parent::fill_api_obj($api_obj);

        $api_obj->set_number($this->number);
    }


    /*
     * save
     */

    /**
     * set the log entry parameter for a new value object
     * for all not named objects like links, this function is overwritten
     * e.g. that the user can see "added formula 'scale millions' to word 'mio'"
     */
    function log_add(): change
    {
        log_debug($this->dsp_id());

        $log = new change($this->user());
        $log->action = change_log_action::ADD;
        $log->set_table($this->obj_type . sql_db::TABLE_EXTENSION);
        $log->set_field(change_log_field::FLD_NUMERIC_VALUE);
        $log->old_value = '';
        $log->new_value = $this->number;

        $log->row_id = 0;
        $log->add();

        return $log;
    }

    /**
     * TODO create and use change_log_value
     * @return change
     */
    function log_add_value(): change
    {
        return new change($this->user());
    }

    /**
     * set the log entry parameter to delete a object
     * @returns change_log_link with the object presets e.g. th object name
     */
    function log_del(): change
    {
        log_debug($this->dsp_id());

        $log = new change($this->user());
        $log->action = change_log_action::DELETE;
        $log->set_table($this->obj_name . sql_db::TABLE_EXTENSION);
        $log->set_field(change_log_field::FLD_NUMERIC_VALUE);
        $log->old_value = $this->number;
        $log->new_value = '';

        $log->row_id = $this->id;
        $log->add();

        return $log;
    }

    /**
     * updated the object id fields (e.g. for a word or formula the name, and for a link the linked ids)
     * should only be called if the user is the owner and nobody has used the display component link
     * @param sql_db $db_con the active database connection
     * @param sandbox_multi $db_rec the database record before the saving
     * @param sandbox_multi $std_rec the database record defined as standard because it is used by most users
     * @returns string either the id of the updated or created source or a message to the user with the reason, why it has failed
     * @throws Exception
     */
    function save_id_fields(sql_db $db_con, sandbox_multi $db_rec, sandbox_multi $std_rec): string
    {

        return 'The user sandbox save_id_fields does not support ' . $this->obj_type . ' for ' . $this->obj_name;
    }


    /**
     * the common part of the sql statement creation for insert and update statements
     * @param sql $sc with the target db_type set
     * @param bool $usr_tbl true if a db row should be added to the user table
     * @return sql_par the common part for insert and update sql statements
     */
    protected function sql_common(sql $sc, bool $usr_tbl = false): sql_par
    {
        $lib = new library();
        // the value table name is not yet using the number of phrase keys as extension
        $tbl_ext = $this->grp->table_extension(true);
        $ext = $this->grp->table_extension();
        $sc->set_class($this::class, $usr_tbl, $tbl_ext);
        $sql_name = $lib->class_to_name($this::class);
        $qp = new sql_par($sql_name);
        if ($tbl_ext == group_id::TBL_EXT_BIG) {
            $qp->name = $sql_name . $tbl_ext;
        } else {
            $qp->name = $sql_name . $tbl_ext . $ext;
        }
        if ($usr_tbl) {
            $qp->name .= '_user';
        }
        return $qp;
    }

    /**
     * create the sql statement to update a value in the database
     * @param sql $sc with the target db_type set
     * @param bool $usr_tbl true if the user table row should be updated
     * @return sql_par the SQL insert statement, the name of the SQL statement and the parameter list
     */
    function sql_update(
        sql   $sc,
        array $fields = [],
        array $values = [],
        bool  $usr_tbl = false
    ): sql_par
    {
        $qp = $this->sql_common($sc, $usr_tbl);
        $qp->name .= '_update';
        $sc->set_name($qp->name);
        $qp->sql = $sc->sql_update($this->id_field(), $this->id(), $fields, $values);
        $qp->par = $values;
        return $qp;
    }


    /**
     * actually update a field in the main database record or the user sandbox
     * the usr id is taken into account in sql_db->update (maybe move outside)
     *
     * for values the log should show to the user just which value has been changed
     * but the technical log needs to remember in which actual table the change has been saved
     *
     * @param sql_db $db_con the active database connection that should be used
     * @param change|change_log_link $log the log object to track the change and allow a rollback
     * @return string an empty string if everything is fine or the message that should be shown to the user
     */
    function save_field_user(
        sql_db                 $db_con,
        change|change_log_link $log
    ): string
    {
        $result = '';

        if ($log->new_id > 0) {
            $new_value = $log->new_id;
            $std_value = $log->std_id;
        } else {
            $new_value = $log->new_value;
            $std_value = $log->std_value;
        }
        $ext = $this->grp()->table_extension();
        if ($log->add()) {
            if ($this->can_change()) {
                if ($new_value == $std_value) {
                    if ($this->has_usr_cfg()) {
                        $msg = 'remove user change of ' . $log->field();
                        log_debug($msg);
                        $db_con->set_class(sql_db::TBL_USER_PREFIX . $this->obj_name . $ext);
                        $db_con->set_usr($this->user()->id());
                        $qp = $this->sql_update($db_con->sql_creator(), array($log->field()), array(null));
                        $usr_msg = $db_con->update($qp, $msg);
                        $result = $usr_msg->get_message();
                    }
                    $this->del_usr_cfg_if_not_needed(); // don't care what the result is, because in most cases it is fine to keep the user sandbox row
                } else {
                    $msg = 'update of ' . $log->field() . ' to ' . $new_value;
                    log_debug($msg);
                    $db_con->set_class($this->obj_type . $ext);
                    $db_con->set_usr($this->user()->id());
                    $qp = $this->sql_update($db_con->sql_creator(), array($log->field()), array($new_value));
                    $usr_msg = $db_con->update($qp, $msg);
                    $result = $usr_msg->get_message();
                }
            } else {
                if (!$this->has_usr_cfg()) {
                    if (!$this->add_usr_cfg()) {
                        $result = 'creation of user sandbox for ' . $log->field() . ' failed';
                    }
                }
                if ($result == '') {
                    $db_con->set_class(sql_db::TBL_USER_PREFIX . $this->obj_name . $ext);
                    $db_con->set_usr($this->user()->id());
                    if ($new_value == $std_value) {
                        $msg = 'remove user change of ' . $log->field();
                        log_debug($msg);
                        $qp = $this->sql_update($db_con->sql_creator(), array($log->field()), array(Null));
                        $usr_msg = $db_con->update($qp, $msg);
                        $result = $usr_msg->get_message();
                    } else {
                        $msg = 'update of ' . $log->field() . ' to ' . $new_value;
                        log_debug($msg);
                        $qp = $this->sql_update($db_con->sql_creator(), array($log->field()), array($new_value));
                        $usr_msg = $db_con->update($qp, $msg);
                        $result = $usr_msg->get_message();
                    }
                    $this->del_usr_cfg_if_not_needed(); // don't care what the result is, because in most cases it is fine to keep the user sandbox row
                }
            }
        }
        return $result;
    }


    /*
     * debug
     */

    /**
     * @return string with the best possible identification for this value mainly used for debugging
     */
    function dsp_id(): string
    {
        $result = $this->dsp_id_short();
        $result .= $this->dsp_id_user();
        return $result;
    }

    /**
     * @return string with the short identification for links
     */
    function dsp_id_short(): string
    {
        $result = $this->dsp_id_entry();
        if ($this->id() != 0) {
            $id_fields = $this->id_field();
            if (is_array($id_fields)) {
                $fld_dsp = ' (' . implode(', ' ,$id_fields);
                $fld_dsp .= ' = ' . $this->grp()->dsp_id_short() . ')';
                $result .= $fld_dsp;
            } else {
                $result .= ' (' . $id_fields . ' ' . $this->id() . ')';
            }
        } else {
            $result .= ' (' . $this->id_field() . ' no set)';
        }
        return $result;
    }

    /**
     * @return string with the short identification for lists
     */
    function dsp_id_entry(): string
    {
        $result = '';
        if (isset($this->grp)) {
            $result .= '"' . $this->grp->name() . '" ';
        }
        if ($this->number() != null) {
            $result .= $this->number();
        } else {
            $result .= 'null';
        }
        return $result;
    }

}


