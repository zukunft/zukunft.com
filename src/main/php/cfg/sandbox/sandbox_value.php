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
use cfg\db\sql_par_field_list;
use cfg\db\sql_par_type;
use cfg\db\sql_type;
use cfg\db\sql_type_list;
use cfg\group\group;
use cfg\group\group_id;
use cfg\log\change;
use cfg\log\change_action;
use cfg\log\change_field_list;
use cfg\log\change_link;
use cfg\log\change_value;
use cfg\result\result;
use cfg\value\value;
use DateTime;
use Exception;
use shared\library;

class sandbox_value extends sandbox_multi
{

    // the table name extension for public unprotected values related up to four prime phrase
    const TBL_EXT_STD = '_standard';
    const TBL_COMMENT_STD = 'for public unprotected ';
    const TBL_COMMENT_STD_PRIME_CONT = 's related up to four prime phrase that have never changed the owner, does not have a description and are rarely updated';
    const TBL_COMMENT_STD_MAIN_CONT = 's related up to eight prime phrase that have never changed the owner, does not have a description and are rarely updated';
    const TBL_COMMENT_STD_CONT = 's that have never changed the owner, does not have a description and are rarely updated';
    const TBL_COMMENT = 'for ';
    const TBL_COMMENT_CONT = 's related to up to 16 phrases';
    const TBL_COMMENT_USER = 'for user specific changes of ';
    const TBL_COMMENT_PRIME = 'for the most often requested ';
    const TBL_COMMENT_PRIME_CONT = 's related up to four prime phrase';
    const TBL_COMMENT_PRIME_USER = 'to store the user specific changes for the most often requested ';
    const TBL_COMMENT_PRIME_USER_CONT = 's related up to four prime phrase';
    const TBL_COMMENT_MAIN = 'to cache the formula second most often requested ';
    const TBL_COMMENT_MAIN_CONT = 's related up to eight prime phrase';
    const TBL_COMMENT_MAIN_USER = 'to store the user specific changes to cache the formula second most often requested ';
    const TBL_COMMENT_MAIN_USER_CONT = 's related up to eight prime phrase';
    const TBL_COMMENT_BIG_CONT = 's related to more than 16 phrases';
    const TBL_COMMENT_BIG_USER = 'to store the user specific changes of ';
    const TBL_COMMENT_BIG_USER_CONT = 's related to more than 16 phrases';
    // TODO review the time series comments
    const TBL_COMMENT_TS = 'for the common parameters for a list of numbers that differ only by the timestamp';
    const TYPE_NUMBER = 'numeric';
    const TYPE_TEXT = 'text';
    const TYPE_TIME = 'time';
    const TYPE_GEO = 'geo';
    const TYPE_TIME_SERIES = 'time_series';
    const FLD_USER_SOURCE_COM = 'one user can add different values from different sources, that have the same group, but a different value, so the source should be included in the unique key';

    // the database field names used for all value tables e.g. also for results
    const FLD_ID_PREFIX = 'phrase_id_';

    // database fields used for user values and results
    const FLD_VALUE = 'numeric_value';
    const FLD_LAST_UPDATE = 'last_update';

    // field lists for the table creation
    // the group is not a foreign key, because if the name is not changed by the user an entry in the group table is not needed
    const FLD_KEY = array(
        [group::FLD_ID, sql_field_type::KEY_512, sql_field_default::NOT_NULL, '', '', 'the 512-bit prime index to find the -=class=-'],
    );
    const FLD_KEY_USER = array(
        [group::FLD_ID, sql_field_type::KEY_PART_512, sql_field_default::NOT_NULL, '', '', 'the 512-bit prime index to find the user -=class=-'],
    );
    // TODO use not null for all keys if a separate table for each number of phrase is implemented
    // TODO FLD_KEY_PRIME and FLD_KEY_PRIME_USER are not the same only if just one phrase is the key
    const FLD_KEY_PRIME = array(
        [sandbox_value::FLD_ID_PREFIX . '1', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::NOT_NULL, sql::INDEX, '', 'phrase id that is part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '2', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::ZERO, sql::INDEX, '', 'phrase id that is part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '3', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::ZERO, sql::INDEX, '', 'phrase id that is part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '4', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::ZERO, sql::INDEX, '', 'phrase id that is part of the prime key for a'],
    );
    const FLD_KEY_PRIME_USER = array(
        [sandbox_value::FLD_ID_PREFIX . '1', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::NOT_NULL, sql::INDEX, '', 'phrase id that is with the user id part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '2', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::ZERO, sql::INDEX, '', 'phrase id that is with the user id part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '3', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::ZERO, sql::INDEX, '', 'phrase id that is with the user id part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '4', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::ZERO, sql::INDEX, '', 'phrase id that is with the user id part of the prime key for a'],
    );
    const FLD_KEY_BIG = array(
        [group::FLD_ID, sql_field_type::KEY_TEXT, sql_field_default::NOT_NULL, '', '', 'the variable text index to find -=class=-'],
    );
    const FLD_KEY_BIG_USER = array(
        [group::FLD_ID, sql_field_type::KEY_PART_TEXT, sql_field_default::NOT_NULL, '', '', 'the text index for more than 16 phrases to find the -=class=-'],
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
    // TODO use this for the user tables
    const FLD_USER_SOURCE = array(
        [source::FLD_ID, sql_field_type::KEY_PART_INT, sql_field_default::NULL, sql::INDEX, source::class, self::FLD_USER_SOURCE_COM],
    );
    const FLD_ALL_CHANGED = array(
        [value::FLD_LAST_UPDATE, sql_field_type::TIME, sql_field_default::NULL, '', '', 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation'],
    );
    // dummy list which is always overwritten by either the value or result object
    const FLD_ALL_TIME_SERIES = array();
    const FLD_ALL_TIME_SERIES_USER = array();
    const FLD_ALL_SOURCE_GROUP = array();
    const FLD_ALL_SOURCE_GROUP_PRIME = array();
    const FLD_ALL_SOURCE_GROUP_BIG = array();
    const FLD_ALL_OWNER = array(
        [user::FLD_ID, sql_field_type::INT, sql_field_default::NULL, sql::INDEX, user::class, 'the owner / creator of the -=class=-'],
    );
    const FLD_ALL_CHANGER = array(
        [user::FLD_ID, sql_field_type::KEY_PART_INT, sql_field_default::NOT_NULL, sql::INDEX, user::class, 'the changer of the -=class=-'],
    );
    // database fields that should only be taken from the user sandbox table
    const FLD_NAMES_USR_ONLY = array(
        sandbox::FLD_CHANGE_USER
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
     * @param bool $one_id_fld false if the unique database id is based on more than one field and due to that the database id should not be used for the object id
     * @return bool true if the user sandbox object is loaded and valid
     */
    function row_mapper_multi(?array $db_row, string $ext, string $id_fld = '', bool $one_id_fld = true): bool
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

    function grp_id(): int|string
    {
        return $this->grp->id();
    }

    function is_id_set(): bool
    {
        return $this->grp()->is_id_set();
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
     * forward group get
     */

    function is_prime(): bool
    {
        return $this->grp()->is_prime();
    }

    function id_names(bool $all = false): array
    {
        return $this->grp()->id_names($all);
    }

    function id_lst(): array
    {
        return $this->grp()->id_lst();
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
        return $this->sql_table_creator($sc, 0);
    }

    /**
     * the sql statements to create all indices for the tables used to store values in the database
     *
     * @param sql $sc with the target db_type set
     * @return string the sql statement to create the indices of the value tables
     */
    function sql_index(sql $sc): string
    {
        return $this->sql_table_creator($sc, 1);
    }

    /**
     * the sql statements to create all foreign keys for the tables
     *
     * @param sql $sc with the target db_type set
     * @return string the sql statement to create the foreign keys of a value table
     */
    function sql_foreign_key(sql $sc): string
    {
        return $this->sql_table_creator($sc, 2);
    }

    /**
     * the sql statements to create either all tables ($pos = 0), the indices ($pos = 1) or the foreign keys ($pos = 2)
     * used to store values in the database
     *
     * @param sql $sc with the target db_type set
     * @return string the sql statement to create the table
     */
    private function sql_table_creator(sql $sc, int $pos): string
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
        $sql_array = $this->sql_one_type(
            $sc,
            $this::FLD_ALL_TIME_SERIES,
            $this::FLD_ALL_TIME_SERIES_USER,
            '_' . $this::TYPE_TIME_SERIES, $this::TYPE_TIME_SERIES,
            self::TBL_COMMENT_TS
        );
        $sql .= $sql_array[$pos];
        return $sql;
    }

    /**
     * create the sql statements for a set (standard, prime and big) tables
     * for one field type e.g. numeric value, text values
     * TODO move the table types to an const array
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
        string $type_name = '',
        string $comment_overwrite = ''): array
    {
        $lib = new library();
        $type_class_name = $type_name . ' ' . $lib->class_to_name($this::class);

        $sql = $sc->sql_separator();
        $sql_index = $sc->sql_separator();
        $sql_foreign = $sc->sql_separator();

        if ($type_name != $this::TYPE_TIME_SERIES) {
            // standard prime: for values or results without user specific changes and for up to four prime phrases
            $sc->set_class($this::class, new sql_type_list([]), $ext_type . self::TBL_EXT_STD . sql_type::PRIME->extension());
            $fields = array_merge($this::FLD_KEY_PRIME, $fld_par, $this::FLD_ALL_SOURCE);
            $tbl_comment = $this::TBL_COMMENT_STD . $type_class_name . $this::TBL_COMMENT_STD_PRIME_CONT;
            $sql .= $sc->table_create($fields, $type_class_name, $tbl_comment, $this::class);
            $sql_index .= $sc->index_create($fields, true);
            $sql_foreign .= $sc->foreign_key_create($fields);

            // standard main: for results without user specific changes and for up to e prime phrases
            if ($this::class == result::class) {
                $sc->set_class($this::class, new sql_type_list([]), $ext_type . self::TBL_EXT_STD . sql_type::MAIN->extension());
                $fields = array_merge(result::FLD_KEY_MAIN_STD, $fld_par, $this::FLD_ALL_SOURCE);
                $tbl_comment = $this::TBL_COMMENT_STD . $type_class_name . $this::TBL_COMMENT_STD_MAIN_CONT;
                $sql .= $sc->table_create($fields, $type_class_name, $tbl_comment, $this::class);
                $sql_index .= $sc->index_create($fields, true);
                $sql_foreign .= $sc->foreign_key_create($fields);
            }

            // standard: for values or results without user specific changes and for up to 16 phrases
            $sc->set_class($this::class, new sql_type_list([]), $ext_type . self::TBL_EXT_STD);
            $fields = array_merge(self::FLD_KEY, $fld_par, $this::FLD_ALL_SOURCE);
            $tbl_comment = $this::TBL_COMMENT_STD . $type_class_name . $this::TBL_COMMENT_STD_CONT;
            if ($comment_overwrite != '') {
                $tbl_comment = $comment_overwrite;
            }
            $sql .= $sc->table_create($fields, $type_class_name, $tbl_comment, $this::class);
            $sql_index .= $sc->index_create($fields);
            $sql_foreign .= $sc->foreign_key_create($fields);
            $sql .= $sc->sql_separator();
        }

        $std_fields = array_merge(
            $fld_par,
            $this::FLD_ALL_SOURCE,
            $this::FLD_ALL_CHANGED,
            $this::FLD_ALL_OWNER,
            sandbox::FLD_LST_ALL);
        if ($this::class == result::class) {
            $std_usr_fields = array_merge(
                $this::FLD_ALL_CHANGER,
                $fld_par_usr,
                $this::FLD_ALL_SOURCE,
                $this::FLD_ALL_CHANGED,
                sandbox::FLD_LST_ALL);
        } else {
            $std_usr_fields = array_merge(
                $this::FLD_ALL_CHANGER,
                $fld_par_usr,
                $this::FLD_USER_SOURCE,
                $this::FLD_ALL_CHANGED,
                sandbox::FLD_LST_ALL);
        }
        $fields = array_merge(self::FLD_KEY, $this::FLD_ALL_SOURCE_GROUP, $std_fields);

        // most: for values or results based on up to 16 phrases
        $sc->set_class($this::class, new sql_type_list([]), $ext_type);
        $tbl_comment = $this::TBL_COMMENT . $type_class_name . $this::TBL_COMMENT_CONT;
        if ($comment_overwrite != '') {
            $tbl_comment = $comment_overwrite;
        }
        $sql .= $sc->table_create($fields, $type_class_name, $tbl_comment, $this::class);
        $sql_index .= $sc->index_create($fields);
        $sql_foreign .= $sc->foreign_key_create($fields);
        $fields = array_merge(self::FLD_KEY_USER, $this::FLD_ALL_SOURCE_GROUP, $std_usr_fields);
        // most user: for user changes in values based on up to 16 phrases
        $sc->set_class($this::class, new sql_type_list([sql_type::USER]), $ext_type);
        $tbl_comment = $this::TBL_COMMENT_USER . $type_class_name . $this::TBL_COMMENT_CONT;
        if ($comment_overwrite != '') {
            $tbl_comment = $comment_overwrite;
        }
        $sql .= $sc->table_create($fields, $type_class_name, $tbl_comment, $this::class);
        $sql_index .= $sc->index_create($fields);
        $sql_foreign .= $sc->foreign_key_create($fields);

        // most: for values or results based on up to four prime phrases
        $sql .= $sc->sql_separator();
        $fields = array_merge(self::FLD_KEY_PRIME, $this::FLD_ALL_SOURCE_GROUP_PRIME, $std_fields);
        $sc->set_class($this::class, new sql_type_list([]), $ext_type . sql_type::PRIME->extension());
        $tbl_comment = $this::TBL_COMMENT_PRIME . $type_class_name . $this::TBL_COMMENT_PRIME_CONT;
        if ($comment_overwrite != '') {
            $tbl_comment = $comment_overwrite;
        }
        $sql .= $sc->table_create($fields, $type_class_name, $tbl_comment, $this::class);
        $sql_index .= $sc->index_create($fields, true);
        $sql_foreign .= $sc->foreign_key_create($fields);
        $fields = array_merge(self::FLD_KEY_PRIME_USER, $this::FLD_ALL_SOURCE_GROUP_PRIME, $std_usr_fields);
        // most user: for user changes in values based on up to four prime phrases
        $sc->set_class($this::class, new sql_type_list([sql_type::USER]), $ext_type . sql_type::PRIME->extension());
        $tbl_comment = $this::TBL_COMMENT_PRIME_USER . $type_class_name . $this::TBL_COMMENT_PRIME_USER_CONT;
        if ($comment_overwrite != '') {
            $tbl_comment = $comment_overwrite;
        }
        $sql .= $sc->table_create($fields, $type_class_name, $tbl_comment, $this::class);
        $sql_index .= $sc->index_create($fields, true);
        $sql_foreign .= $sc->foreign_key_create($fields);

        // main: for results based on up to eight prime phrases
        if ($this::class == result::class and $type_name != $this::TYPE_TIME_SERIES) {
            $sql .= $sc->sql_separator();
            $fields = array_merge(result::FLD_KEY_MAIN, $this::FLD_ALL_SOURCE_GROUP_PRIME, $std_fields);
            $sc->set_class($this::class, new sql_type_list([]), $ext_type . sql_type::MAIN->extension());
            $tbl_comment = $this::TBL_COMMENT_MAIN . $type_class_name . $this::TBL_COMMENT_MAIN_CONT;
            if ($comment_overwrite != '') {
                $tbl_comment = $comment_overwrite;
            }
            $sql .= $sc->table_create($fields, $type_class_name, $tbl_comment, $this::class);
            $sql_index .= $sc->index_create($fields, true);
            $sql_foreign .= $sc->foreign_key_create($fields);
            $fields = array_merge(result::FLD_KEY_MAIN_USER, $this::FLD_ALL_SOURCE_GROUP_PRIME, $std_usr_fields);
            // most user: for user changes in values based on up to four prime phrases
            $sc->set_class($this::class, new sql_type_list([sql_type::USER]), $ext_type . sql_type::MAIN->extension());
            $tbl_comment = $this::TBL_COMMENT_MAIN_USER . $type_class_name . $this::TBL_COMMENT_MAIN_USER_CONT;
            if ($comment_overwrite != '') {
                $tbl_comment = $comment_overwrite;
            }
            $sql .= $sc->table_create($fields, $type_class_name, $tbl_comment, $this::class);
            $sql_index .= $sc->index_create($fields, true);
            $sql_foreign .= $sc->foreign_key_create($fields);
        }

        // big: for values based on more than 16 phrases
        $sql .= $sc->sql_separator();
        $fields = array_merge(self::FLD_KEY_BIG, $this::FLD_ALL_SOURCE_GROUP_BIG, $std_fields);
        $sc->set_class($this::class, new sql_type_list([]), $ext_type . sql_type::BIG->extension());
        $tbl_comment = $this::TBL_COMMENT . $type_class_name . $this::TBL_COMMENT_BIG_CONT;
        if ($comment_overwrite != '') {
            $tbl_comment = $comment_overwrite;
        }
        $sql .= $sc->table_create($fields, $type_class_name, $tbl_comment, $this::class);
        $sql_index .= $sc->index_create($fields);
        $sql_foreign .= $sc->foreign_key_create($fields);
        $fields = array_merge(self::FLD_KEY_BIG_USER, $this::FLD_ALL_SOURCE_GROUP_BIG, $std_usr_fields);
        // most user: for user changes in values based on more than 16 phrases
        $sc->set_class($this::class, new sql_type_list([sql_type::USER]), $ext_type . sql_type::BIG->extension());
        $tbl_comment = $this::TBL_COMMENT_BIG_USER . $type_class_name . $this::TBL_COMMENT_BIG_USER_CONT;
        if ($comment_overwrite != '') {
            $tbl_comment = $comment_overwrite;
        }
        $sql .= $sc->table_create($fields, $type_class_name, $tbl_comment, $this::class);
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
        $this->load_sql_where_id($qp, $sc);

        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();
        $qp->ext = $ext;

        return $qp;
    }

    /**
     * set the where condition and the final query parameters
     * for a value or result query
     *
     * @param sql_par $qp the query parameters fully set without the sql, par and ext
     * @param sql $sc the sql creator with all parameters set
     * @param bool $all true if all id fields should be used independend from the number of ids
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    protected function load_sql_where_id(sql_par $qp, sql $sc, bool $all = false): sql_par
    {
        if ($this->is_prime()) {
            $fields = $this->id_names($all);
            $values = $this->id_lst();
            $pos = 0;
            foreach ($fields as $field) {
                $val_used = 0;
                if (array_key_exists($pos, $values)) {
                    $val_used = $values[$pos];
                }
                $sc->add_where($field, $val_used);
                $pos++;
            }
        } else {
            $sc->add_where(group::FLD_ID, $this->grp->id());
        }
        return $qp;
    }

    /**
     * create an SQL statement to retrieve the user changes of the current object
     *
     * @param sql $sc with the target db_type set
     * @param string $class the name of the child class from where the call has been triggered
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_user_changes(
        sql           $sc,
        string        $class = self::class,
        sql_type_list $sc_par_lst = new sql_type_list([])
    ): sql_par
    {
        // remove user parameter before the query name creation because by_usr_cfg id enough
        $qp = new sql_par($class, $sc_par_lst->remove(sql_type::USER), '', $this->grp->table_extension());
        $qp->name .= 'usr_cfg';
        $sc->set_name($qp->name);
        $sc->set_usr($this->user()->id());
        $sc->set_fields($this->all_sandbox_fields());

        if ($this->grp->is_prime()) {
            $fields = $this->id_names();
            $values = $this->id_lst();
        } else {
            $fields = array(group::FLD_ID);
            $values = array($this->grp->id());
        }
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
     * create an SQL statement to get all the users that have changed this value
     * TODO overwrites the sandbox function
     *
     * @param sql $sc with the target db_type set
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_changer(sql $sc): sql_par
    {
        $ext = 'changer';
        if ($this->owner_id > 0) {
            $ext .= '_ex_owner';
        }
        $sc_par_lst = [sql_type::COMPLETE, sql_type::USER];
        $sc_par_lst[] = $this->grp->table_type();
        $qp = new sql_par($this::class, new sql_type_list($sc_par_lst), $ext);
        $sc->set_class($this::class, new sql_type_list($sc_par_lst));
        $sc->set_name($qp->name);
        $sc->set_usr($this->user()->id());
        // overwrite the standard id field because e.g. prime values have a combined id field
        $sc->set_id_field($this->id_field());
        $sc->set_fields(array(user::FLD_ID));
        $this->load_sql_where_id($qp, $sc, true);
        $sc->add_where(sandbox::FLD_EXCLUDED, 1, sql_par_type::INT_NOT_OR_NULL);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * if the object has been changed by someone else than the owner the user id is returned
     * but only return the user id if the user has not also excluded it
     * @returns int the user id of someone who has changed the object, but is not owner
     */
    function changer(): int
    {
        log_debug($this->dsp_id());

        global $db_con;

        $user_id = 0;
        $db_con->set_class($this::class);
        $db_con->set_usr($this->user()->id());
        $qp = $this->load_sql_changer($db_con->sql_creator());
        $db_row = $db_con->get1($qp);
        if ($db_row) {
            $user_id = $db_row[user::FLD_ID];
        }

        log_debug('is ' . $user_id);
        return $user_id;
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
        $sc_par_lst = new sql_type_list([$this->grp()->table_type()]);
        $id_ext = $this->grp()->table_extension();
        $qp = $this->load_sql_multi($sc, $query_name, $class, $sc_par_lst, '', $id_ext);
        return $this->load_sql_set_where($qp, $sc, $id_ext);
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

    /**
     * create the SQL to load the single default value or result always by the id
     * the $sc fields must be set by the child function
     *
     * @param sql $sc with the target db_type set
     * @param string $class the name of the child class from where the call has been triggered
     * @param array $fld_lst list of fields either for the value or the result
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_standard_sql(
        sql $sc,
        string $class = self::class,
        array $fld_lst = []
    ): sql_par
    {
        $sc_par_lst = new sql_type_list([]);
        $sc_par_lst->add($this->grp->table_type());
        $sc_par_lst->add(sql_type::NORM);
        $id_ext = $this->grp->table_extension();
        $qp = new sql_par($class, $sc_par_lst, '', $id_ext);
        $qp->name .= sql_db::FLD_ID;
        $sc->set_class($class, $sc_par_lst);
        $sc->set_name($qp->name);
        $sc->set_id_field($this->id_field());
        $sc->set_fields($fld_lst);

        return $this->load_sql_set_where($qp, $sc, $id_ext);
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

    /**
     * overwrites the standard db_object function because
     * the main id field of value is not value_id, but group_id
     * @return string|array the field name(s) of the prime database index of the object
     */
    function id_field(): string|array
    {
        if ($this->is_prime()) {
            return $this->id_fields_prime();
        } else {
            return $this->id_field_group();
        }
    }

    /**
     * @return array with the id fields for none prime and prime values
     */
    function id_fields_both(): array
    {
        $id_fields = array();
        $id_fields[] = $this->id_field_group();
        return array_merge($id_fields, $this->id_fields_prime());
    }

    /**
     * @return array with the id fields for a prime value
     */
    function id_fields_prime(int $start = 1, int $end = group_id::PRIME_PHRASES_STD): array
    {
        $lib = new library();
        $id_fields = array();
        $base_name = $lib->class_to_name(phrase::class) . sql_db::FLD_EXT_ID . sql_db::FLD_SEP;
        for ($i = $start; $i <= $end; $i++) {
            $id_fields[] = $base_name . $i;
        }
        return $id_fields;
    }

    /**
     * @param bool $usr_tbl true if also the user group id field should be returned
     * @param bool $usr_only true if only the user table field should be returned
     * @return string|array with the id field for a none prime value
     */
    function id_field_group(bool $usr_tbl = false, bool $usr_only = false): string|array
    {
        $lib = new library();
        $fld_name = $lib->class_to_name(group::class) . sql_db::FLD_EXT_ID;
        if (!$usr_tbl) {
            if ($usr_only) {
                return sql_db::TBL_USER_PREFIX . $fld_name;
            } else {
                return $fld_name;
            }
        } else {
            $id_fields = array();
            $id_fields[] = $fld_name;
            $id_fields[] = sql_db::TBL_USER_PREFIX . $fld_name;
            return $id_fields;
        }
    }

    /**
     * set the id field based on the given table type
     * used for list load queries where the id if not yet set
     * @param sql_type $tbl_typ the table type that should be used for the id field selection
     * @return string|array the field name(s) of the prime database index of the object
     */
    function id_field_list(sql_type $tbl_typ = sql_type::MOST): string|array
    {
        $lib = new library();
        if ($tbl_typ == sql_type::PRIME) {
            $id_fields = array();
            $base_name = $lib->class_to_name(phrase::class) . sql_db::FLD_EXT_ID . '_';
            for ($i = 1; $i <= group_id::PRIME_PHRASES_STD; $i++) {
                $id_fields[] = $base_name . $i;
            }
            return $id_fields;
        } else {
            return $lib->class_to_name(group::class) . sql_db::FLD_EXT_ID;
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

        $phr_lst = $this->grp()->phrase_list();
        $api_phr_lst = $phr_lst->api_obj();
        $api_obj->phrases = $api_phr_lst;
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
        $lib = new library();

        $log = new change($this->user());
        $log->action = change_action::ADD;
        $class = $lib->class_to_name($this::class);
        $log->set_table($class . sql_db::TABLE_EXTENSION);
        $log->set_field(change_field_list::FLD_NUMERIC_VALUE);
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
     * @returns change_link with the object presets e.g. th object name
     */
    function log_del(): change
    {
        log_debug($this->dsp_id());
        $lib = new library();

        $log = new change($this->user());
        $log->action = change_action::DELETE;
        $class = $lib->class_to_name($this::class);
        $log->set_table($class . sql_db::TABLE_EXTENSION);
        $log->set_field(change_field_list::FLD_NUMERIC_VALUE);
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
        $lib = new library();
        $class_name = $lib->class_to_name($this::class);
        return 'The user sandbox save_id_fields does not support changing the phrase for ' . $class_name;
    }


    /**
     * the common part of the sql statement creation for insert and update statements
     * @param sql $sc with the target db_type set
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @param string $ext the change field base name extension that cannot be taken from the $sc_par_lst
     * @return sql_par the common part for insert and update sql statements
     */
    protected function sql_common(
        sql           $sc,
        sql_type_list $sc_par_lst,
        string        $ext = '',
        string        $id_ext = ''
    ): sql_par
    {
        // the value table name is not yet using the number of phrase keys as extension
        $sc->set_class($this::class, $sc_par_lst);
        return new sql_par($this::class, $sc_par_lst, $ext, $id_ext);
    }

    /**
     * create the sql statement to update a value in the database
     * @param sql $sc with the target db_type set
     * @param sandbox_value $db_obj the value object with the database values before the update
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par the SQL insert statement, the name of the SQL statement and the parameter list
     */
    function sql_update_value(sql $sc, sandbox_value $db_obj, sql_type_list $sc_par_lst): sql_par
    {
        $qp = $this->sql_common($sc, $sc_par_lst);
        $qp->name .= sql::NAME_SEP . sql::FILE_UPDATE;
        $sc->set_name($qp->name);
        // get the fields and values that have been changed and needs to be updated in the database
        // TODO fix it
        $fld_val_typ_lst = $this->db_changed($db_obj);
        $fvt_lst = new sql_par_field_list();
        $fvt_lst->set($fld_val_typ_lst);
        $qp->sql = $sc->create_sql_update($this->id_field(), $this->id(), $fvt_lst);
        $values = $sc->get_values($fld_val_typ_lst);
        $qp->par = $values;
        return $qp;
    }

    /**
     * dummy function to be overwritten by the child object
     * get a list of database fields that have been updated
     * excluding the internal only last_update and is_std fields
     *
     * @param sandbox_value $sbv the compare value to detect the changed fields
     * @return array list of the database field names that have been updated
     */
    function db_changed(sandbox_value $sbv): array
    {
        return [];
    }

    /**
     * dummy function to be overwritten by the child object
     * get a list of database fields that have been updated
     * excluding the internal only last_update and is_std fields
     *
     * @param sandbox_value $sbv the compare value to detect the changed fields
     * @return array list of the database field names that have been updated
     */
    function db_fields_changed(sandbox_value $sbv): array
    {
        return [];
    }

    /**
     * dummy function to be overwritten by the child object
     * get a list of database values that have been updated
     * excluding the internal only last_update and is_std values
     *
     * @param result $sbv the compare value to detect the changed fields
     * @return array list of the database field names that have been updated
     */
    function db_values_changed(sandbox_value $sbv): array
    {
        return [];
    }

    /**
     * actually update a field in the main database record or the user sandbox
     * the usr id is taken into account in sql_db->update (maybe move outside)
     *
     * for values the log should show to the user just which value has been changed
     * but the technical log needs to remember in which actual table the change has been saved
     *
     * @param sql_db $db_con the active database connection that should be used
     * @param change|change_value|change_link $log the log object to track the change and allow a rollback
     * @return string an empty string if everything is fine or the message that should be shown to the user
     */
    function save_field_user(
        sql_db                          $db_con,
        change|change_value|change_link $log
    ): string
    {
        $result = '';
        $sc = $db_con->sql_creator();

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
                $sql_fld_typ = $sc->get_sql_par_type($new_value);
                if ($new_value == $std_value) {
                    if ($this->has_usr_cfg()) {
                        $msg = 'remove user change of ' . $log->field();
                        log_debug($msg);
                        $db_con->set_class($this::class, true, $ext);
                        $db_con->set_usr($this->user()->id());
                        $fld_val_typ_lst = [[$log->field(), null, null]];
                        $qp = $this->sql_update_fields($db_con->sql_creator(), $fld_val_typ_lst);
                        $usr_msg = $db_con->update($qp, $msg);
                        $result = $usr_msg->get_message();
                    }
                    $this->del_usr_cfg_if_not_needed(); // don't care what the result is, because in most cases it is fine to keep the user sandbox row
                } else {
                    $msg = 'update of ' . $log->field() . ' to ' . $new_value;
                    log_debug($msg);
                    $db_con->set_class($this::class, false, $ext);
                    $db_con->set_usr($this->user()->id());
                    $fld_val_typ_lst = [[$log->field(), $new_value, $sql_fld_typ]];
                    $qp = $this->sql_update_fields($db_con->sql_creator(), $fld_val_typ_lst);
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
                    $db_con->set_class($this::class, true, $ext);
                    $db_con->set_usr($this->user()->id());
                    $sql_fld_typ = $sc->get_sql_par_type($new_value);
                    if ($new_value == $std_value) {
                        $msg = 'remove user change of ' . $log->field();
                        log_debug($msg);
                        $fld_val_typ_lst = [[$log->field(), Null, $sql_fld_typ]];
                    } else {
                        $msg = 'update of ' . $log->field() . ' to ' . $new_value;
                        log_debug($msg);
                        $fld_val_typ_lst = [[$log->field(), $new_value, $sql_fld_typ]];
                    }
                    $qp = $this->sql_update_fields($db_con->sql_creator(), $fld_val_typ_lst, new sql_type_list([sql_type::USER]));
                    $usr_msg = $db_con->update($qp, $msg);
                    $result = $usr_msg->get_message();
                    $this->del_usr_cfg_if_not_needed(); // don't care what the result is, because in most cases it is fine to keep the user sandbox row
                }
            }
        }
        return $result;
    }


    /*
     * sql write
     */

    /**
     * create the sql statement to add a new value or result to the database
     * TODO add source group
     *
     * @param sql $sc with the target db_type set
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par the SQL insert statement, the name of the SQL statement and the parameter list
     */
    function sql_insert(
        sql           $sc,
        sql_type_list $sc_par_lst = new sql_type_list([])
    ): sql_par
    {
        // clone the parameter list to avoid changing the given list
        $sc_par_lst_used = clone $sc_par_lst;
        // set the sql query type
        $sc_par_lst_used->add(sql_type::INSERT);
        // set the target sql table type for this value
        $sc_par_lst_used->add($this->grp->table_type());
        // get the name indicator how many id fields are user
        $id_ext = $this->grp->table_extension();
        $qp = $this->sql_common($sc, $sc_par_lst_used, '', $id_ext);
        // overwrite the standard auto increase id field name
        $sc->set_id_field($this->id_field());
        $sc->set_name($qp->name);
        $usr_tbl = $sc_par_lst_used->is_usr_tbl();
        $fvt_lst = new sql_par_field_list();
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
        if ($this::class == result::class) {
            $fields[] = formula::FLD_ID;
            $values[] = $this->frm->id();
            $fields[] = result::FLD_SOURCE_GRP;
            $values[] = $this->src_grp->id();
        }
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
     * @param value|result $db_val the result object with the database values before the update
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par the SQL insert statement, the name of the SQL statement and the parameter list
     */
    function sql_update(
        sql           $sc,
        value|result  $db_val,
        sql_type_list $sc_par_lst = new sql_type_list([])
    ): sql_par
    {
        // get the fields and values that have been changed and needs to be updated in the database
        $fld_val_typ_lst = $this->db_changed($db_val);
        return $this->sql_update_fields($sc, $fld_val_typ_lst, $sc_par_lst);
    }

    /**
     * create the sql statement to update a value in the database
     * based on the given list of fields and values
     *
     * @param sql $sc with the target db_type set
     * @param array $fld_val_typ_lst list of field names, values and sql types additional to the standard id and name fields
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par the SQL insert statement, the name of the SQL statement and the parameter list
     */
    function sql_update_fields(
        sql $sc,
        array $fld_val_typ_lst = [],
        sql_type_list $sc_par_lst = new sql_type_list([])
    ): sql_par
    {
        // clone the parameter list to avoid changing the given list
        $sc_par_lst_used = clone $sc_par_lst;
        // set the sql query type
        $sc_par_lst_used->add(sql_type::UPDATE);
        // set the target sql table type for this value
        $sc_par_lst_used->add($this->grp->table_type());
        // get the name indicator how many id fields are user
        $id_ext = $this->grp->table_extension();

        // get the fields and values that have been changed and needs to be updated in the database
        $lib = new library();
        $fields = $sc->get_fields($fld_val_typ_lst);
        $ext = implode('_', $lib->sql_name_shorten($fields));
        if ($ext != '') {
            $ext = sql::NAME_SEP . $ext;
        }

        $qp = $this->sql_common($sc, $sc_par_lst_used, $ext, $id_ext);

        $sc->set_name($qp->name);
        if ($this->grp->is_prime()) {
            $id_fields = $this->grp->id_names(true);
        } else {
            $id_fields = $this->id_field();
        }
        $id = $this->id();
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
        if ($sc_par_lst_used->is_usr_tbl()) {
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
     * list of all fields that can be changed by the user in this object
     * the last_update field is excluded here because this is an internal only field
     *
     * @param sql_type_list $sc_par_lst only used for link objects
     * @return array with the field names of the object and any child object
     */
    function db_fields_all_value(sql_type_list $sc_par_lst = new sql_type_list([])): array
    {
        return [group::FLD_ID, self::FLD_VALUE];
    }

    /**
     * get a list of database field names, values and types that have been updated
     * the last_update field is excluded here because this is an internal only field
     *
     * @param sandbox_value $sbx the same value sandbox as this to compare which fields have been changed
     * @return array with the field names of the object and any child object
     */
    function db_changed_value(sandbox_value $sbx): array
    {
        $lst = [];
        if ($sbx->grp_id() <> $this->grp_id()) {
            // TODO review the group type
            $lst[] = [
                group::FLD_ID,
                $this->grp_id(),
                sql_field_type::INT
            ];
        }
        if ($sbx->number() <> $this->number()) {
            $lst[] = [
                self::FLD_VALUE,
                $this->number(),
                sql_field_type::NUMERIC_FLOAT
            ];
        }
        return $lst;
    }

    /**
     * list of fields that have been changed compared to a given object
     * the last_update field is excluded here because this is an internal only field
     *
     * @param sandbox_value $sbx the same value sandbox as this to compare which fields have been changed
     * @return array with the field names of the object and any child object
     */
    function db_fields_changed_value(sandbox_value $sbx): array
    {
        $result = [];
        if ($sbx->grp_id() <> $this->grp_id()) {
            $result[] = group::FLD_ID;
        }
        if ($sbx->number() <> $this->number()) {
            $result[] = value::FLD_VALUE;
        }
        return $result;
    }

    /**
     * list of fields that have been changed compared to a given object
     * the last_update field is excluded here because this is an internal only field
     *
     * @param sandbox_value $sbx_val the same value sandbox as this to compare which fields have been changed
     * @return array with the field names of the object and any child object
     */
    function db_values_changed_value(sandbox_value $sbx_val): array
    {
        $result = [];
        if ($sbx_val->grp_id() <> $this->grp_id()) {
            $result[] = $this->grp_id();
        }
        if ($sbx_val->number() <> $this->number()) {
            $result[] = $this->number();
        }
        return $result;
    }


    /*
     * clone
     */

    /**
     * create a clone and update the number (mainly used for unit testing)
     *
     * @param float $number the target value
     * @return $this a clone with the number changed
     */
    function cloned(float $number): sandbox_value
    {
        $obj_cpy = clone $this;
        $obj_cpy->set_number($number);
        return $obj_cpy;
    }

    /**
     * create a clone and reset the timestamp to trigger the update of the depending results
     * @return $this a clone with the last update set to null
     */
    function updated(): sandbox_value
    {
        $obj_cpy = clone $this;
        $obj_cpy->last_update = null;
        return $obj_cpy;
    }


    /*
     * internal
     */

    /**
     * @return bool true if this sandbox object is a value or result (final function)
     */
    function is_value_obj(): bool
    {
        return true;
    }

    /**
     * @return bool true if this sandbox object has a name as unique key (final function)
     */
    function is_named_obj(): bool
    {
        return false;
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
            $tbl_typ = $this->grp->table_type();
            $id_fields = $this->id_field($tbl_typ);
            if (is_array($id_fields)) {
                $fld_dsp = ' (' . implode(', ', $id_fields);
                $fld_dsp .= ' = ' . $this->grp()->dsp_id_short() . ')';
                $result .= $fld_dsp;
            } else {
                $result .= ' (' . $id_fields . ' ' . $this->id() . ')';
            }
        } else {
            $id_fld = $this->id_field();
            if (is_array($id_fld)) {
                $lib = new library();
                $result .= ' (' . $lib->dsp_array($id_fld) . ' no set)';
            } else {
                $result .= ' (' . $id_fld . ' no set)';
            }
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
            $result .= '"' . $this->grp()->name() . '" ';
        }
        if ($this->number() != null) {
            $result .= $this->number();
        } else {
            $result .= 'null';
        }
        return $result;
    }

}


