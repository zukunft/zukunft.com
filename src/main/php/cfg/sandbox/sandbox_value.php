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
use cfg\group\result_id;
use cfg\log\change;
use cfg\log\change_action;
use cfg\log\change_field_list;
use cfg\log\change_link;
use cfg\log\change_value;
use cfg\result\result;
use cfg\value\value;
use cfg\value\value_dsp_old;
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
        [self::FLD_VALUE, sql_field_type::NUMERIC_FLOAT, sql_field_default::NOT_NULL, '', '', 'the numeric value given by the user'],
    );
    const FLD_ALL_VALUE_NUM_USER = array(
        [self::FLD_VALUE, sql_field_type::NUMERIC_FLOAT, sql_field_default::NULL, '', '', 'the user specific numeric value change'],
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
        [self::FLD_LAST_UPDATE, sql_field_type::TIME, sql_field_default::NULL, '', '', 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation'],
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

    /**
     * TODO review (add ...)
     * @return bool true if no user has changed the value and no parameter beside the value is set
     */
    function is_standard(): bool
    {
        if ($this->usr_cfg_id == null
            and $this->owner_id == null
            and !$this->excluded) {
            return true;
        } else {
            return false;
        }
    }

    function table_type(): sql_type
    {
        if ($this::class == value::class or $this::class == value_dsp_old::class) {
            return $this->grp->table_type();
        } else {
            if ($this->is_main()) {
                return sql_type::MAIN;
            } else {
                return $this->grp->table_type();
            }
        }
    }

    function table_extension(): string
    {
        if ($this::class == value::class or $this::class == value_dsp_old::class) {
            return $this->grp->table_extension();
        } else {
            if ($this->is_main()) {
                $grp_id = new group_id();
                return group_id::TBL_EXT_PHRASE_ID . $grp_id->count($this->grp_id());
            } else {
                return $this->grp->table_extension();
            }
        }
    }

    /**
     * always returns zero, but overwritten by the result object
     * @return int the formula id of the result
     */
    function formula_id(): int
    {
        return 0;
    }


    /*
     * forward group get
     */

    function is_prime(): bool
    {
        if ($this::class == value::class or $this::class == value_dsp_old::class) {
            return $this->grp()->is_prime();
        } else {
            $grp_id = new group_id();
            $nbr_of_ids = $grp_id->count($this->grp_id());
            if ($nbr_of_ids <= result_id::PRIME_PHRASES_STD) {
                return true;
            } else {
                return false;
            }
        }
    }

    function is_main(): bool
    {
        if ($this::class == value::class or $this::class == value_dsp_old::class) {
            return false;
        } else {
            $grp_id = new group_id();
            $nbr_of_ids = $grp_id->count($this->grp_id());
            if ($nbr_of_ids > result_id::PRIME_PHRASES_STD
                and $nbr_of_ids <= group_id::MAIN_PHRASES_STD) {
                return true;
            } else {
                return false;
            }
        }
    }

    function is_big(): bool
    {
        return $this->grp()->is_big();
    }

    /**
     * TODO create a function max_phrases that is overwritten by the result object
     * @param bool $all
     * @return array
     */
    function id_names(bool $all = false): array
    {
        // TODO remove value_dsp_old
        if ($this::class == value::class or $this::class == value_dsp_old::class) {
            return $this->grp()->id_names($all);
        } else {
            if ($this->is_main()) {
                if ($this->is_standard()) {
                    return $this->grp()->id_names($all, group_id::MAIN_PHRASES_STD);
                } else {
                    return $this->grp()->id_names($all, result_id::MAIN_PHRASES_ALL);
                }
            } else {
                return $this->grp()->id_names($all);
            }
        }
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
     * @param string $comment_overwrite
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
        $this->load_sql_where_id($qp, $sc, true);

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
        if ($this->is_prime() or $this->is_main()) {
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
     * create an SQL statement to retrieve the user changes of the current value or result
     *
     * @param sql $sc with the target db_type set
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation e.g. standard
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_user_changes(
        sql           $sc,
        sql_type_list $sc_par_lst = new sql_type_list([])
    ): sql_par
    {
        $sc_par_lst->add(sql_type::USER);
        $sc_par_lst->add($this->table_type());
        $sc->set_class($this::class, $sc_par_lst);
        // overwrite the standard id field name (value_id or result_id) with the main database id field for results "group_id"
        $sc->set_id_field($this->id_field($sc_par_lst));

        // remove user parameter before the query name creation because by_usr_cfg id enough
        $qp = new sql_par($this::class, $sc_par_lst->remove(sql_type::USER), '', $this->table_extension());
        $qp->name .= sql::NAME_EXT_USER_CONFIG;
        $sc->set_name($qp->name);
        $sc->set_usr($this->user()->id());
        $sc->set_fields($this->all_sandbox_fields());

        // get and set the prime db key list for this sandbox object
        $fvt_lst_id = $this->id_fvt_lst($sc_par_lst);
        $sc->add_where_fvt($fvt_lst_id);

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
            $ext .= sql::NAME_SEP . sql::NAME_EXT_EX_OWNER;
        }
        $sc_par_lst = [sql_type::COMPLETE, sql_type::USER];
        $sc_par_lst[] = $this->table_type();
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
        $sc_par_lst = new sql_type_list([$this->table_type()]);
        $id_ext = $this->table_extension();
        $qp = $this->load_sql_multi($sc, $query_name, $class, $sc_par_lst, '', $id_ext);
        return $this->load_sql_set_where($qp, $sc, $id_ext);
    }

    /**
     * load the value parameters for all users
     * @param sql_par|null $qp the query parameter created by the function of the child object e.g. word->load_standard
     * @return bool true if the standard object has been loaded
     */
    function load_standard(?sql_par $qp = null): bool
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
     * @param array $fld_lst list of fields either for the value or the result
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_standard_sql(
        sql    $sc,
        array  $fld_lst = []
    ): sql_par
    {
        $sc_par_lst = new sql_type_list([]);
        $sc_par_lst->add($this->table_type());
        $sc_par_lst->add(sql_type::NORM);
        $id_ext = $this->table_extension();
        $qp = new sql_par($this::class, $sc_par_lst, '', $id_ext);
        $qp->name .= sql_db::FLD_ID;
        $sc->set_class($this::class, $sc_par_lst);
        $sc->set_name($qp->name);
        $sc->set_id_field($this->id_field());
        $sc->set_fields($fld_lst);

        return $this->load_sql_set_where($qp, $sc, $id_ext);
    }

    /**
     * sql statement to get the user that has created the most often used value
     * @param sql $sc
     * @return sql_par sql parameter
     */
    function load_sql_median_user(sql $sc): sql_par
    {
        $sc_par_lst = new sql_type_list([]);
        $sc_par_lst->add($this->table_type());
        $sc_par_lst->add(sql_type::USER);
        $ext = sql::NAME_EXT_MEDIAN_USER;
        if ($this->owner_id > 0) {
            $ext .= sql::NAME_SEP . sql::NAME_EXT_EX_OWNER;
        }
        $id_ext = $this->table_extension();
        $qp = new sql_par($this::class, $sc_par_lst, $ext, $id_ext);
        $sc->set_class($this::class, $sc_par_lst);
        $sc->set_name($qp->name);
        $sc->set_usr($this->user()->id());
        $sc->set_id_field($this->id_field());
        $sc->set_fields(array(user::FLD_ID));

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
     * get the id fields, values and types for this value or result object
     *
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par_field_list with the id fields, values and types for this value or result object
     */
    function id_fvt_lst(sql_type_list $sc_par_lst = new sql_type_list([])): sql_par_field_list
    {
        $lst = new sql_par_field_list();
        if ($this->is_prime()) {
            if ($this::class == result::class) {
                $lst->add_field(
                    formula::FLD_ID,
                    $this->formula_id(),
                    sql_field_type::INT_SMALL
                );
            }
            if ($this::class == result::class and $sc_par_lst->is_standard()) {
                $fld_lst = $this->id_fields_prime(1, result_id::PRIME_PHRASES_STD);
            } else {
                $fld_lst = $this->id_fields_prime();
            }
            $id_lst = $this->grp()->id_lst();
            if (count($fld_lst) < count($id_lst)) {
                log_err('the number if id fields and id values differ for ' . $this->dsp_id());
            } else {
                foreach ($fld_lst as $key => $fld) {
                    $id = null;
                    if (array_key_exists($key, $id_lst)) {
                        $id = $id_lst[$key];
                        $lst->add_field($fld, $id, sql_field_type::INT_SMALL);
                    } elseif (!$sc_par_lst->is_insert() or $sc_par_lst->incl_log()) {
                        $lst->add_field($fld, $id, sql_field_type::INT_SMALL);
                    }
                }
            }
        } elseif ($this::class == result::class and $this->is_main()) {
            $lst->add_field(
                formula::FLD_ID,
                $this->formula_id(),
                sql_field_type::INT_SMALL
            );
            if ($sc_par_lst->is_standard()) {
                $fld_lst = $this->id_fields_prime(1,
                    result_id::MAIN_SOURCE_PHRASES
                    + result_id::MAIN_PHRASES_STD
                    + result_id::MAIN_RESULT_PHRASES);
            } else {
                $fld_lst = $this->id_fields_main();
            }
            $id_lst = $this->grp()->id_lst();
            if (count($fld_lst) < count($id_lst)) {
                log_err('the number if id fields and id values differ for ' . $this->dsp_id());
            } else {
                foreach ($fld_lst as $key => $fld) {
                    $id = null;
                    if (array_key_exists($key, $id_lst)) {
                        $id = $id_lst[$key];
                        $lst->add_field($fld, $id, sql_field_type::INT_SMALL);
                    } elseif (!$sc_par_lst->is_insert()) {
                        $lst->add_field($fld, $id, sql_field_type::INT_SMALL);
                    }
                }
            }
        } else {
            if ($this->is_big()) {
                $lst->add_field(
                    $this->id_field_group(),
                    $this->id(),
                    sql_field_type::TEXT
                );
            } else {
                $lst->add_field(
                    $this->id_field_group(),
                    $this->id(),
                    sql_field_type::KEY_512
                );
            }
        }
        // for standard values the user id of the creator is taken from the change log
        if (!$sc_par_lst->is_standard()) {
            // TODO add the test case to change the user of a normal value
            if ($sc_par_lst->is_insert() or $sc_par_lst->is_usr_tbl()) {
                $lst->add_field(
                    user::FLD_ID,
                    $this->user()->id(),
                    user::FLD_ID_SQLTYP
                );
            }
        }
        return $lst;
    }

    /**
     * overwrites the standard db_object function because
     * the main id field of value is not value_id, but group_id
     *
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return string|array the field name(s) of the prime database index of the object
     */
    function id_field(sql_type_list $sc_par_lst = new sql_type_list([])): string|array
    {
        $result = $this->id_field_group();
        if ($this->is_prime()) {
            if ($this::class == result::class and $sc_par_lst->is_standard()) {
                // TODO merge with result::FLD_KEY_PRIME ?
                $id_fields = $this->id_fields_prime(1, result_id::PRIME_PHRASES_STD);
                $result = array_merge([formula::FLD_ID], $id_fields);
            } else {
                if ($this::class == result::class) {
                    $result = array_merge([formula::FLD_ID], $this->id_fields_prime());
                } else {
                    $result = $this->id_fields_prime();
                }
            }
        } elseif ($this->is_main()) {
            if ($this::class == result::class and $sc_par_lst->is_standard()) {
                // TODO merge with result::FLD_KEY_PRIME ?
                $id_fields = $this->id_fields_main(1, group_id::MAIN_PHRASES_STD);
                $result = array_merge([formula::FLD_ID], $id_fields);
            } else {
                $result = array_merge([formula::FLD_ID], $this->id_fields_main());
            }
        }
        return $result;
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
     * @return array with the id fields for a main value
     */
    function id_fields_main(int $start = 1, int $end = group_id::MAIN_PHRASES_STD): array
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
     * @param change|change_value $log with the target table set
     * @return change|change_value with the log id set
     */
    protected function log_add_common(change|change_value $log): change|change_value
    {
        log_debug($this->dsp_id());
        $log->set_action(change_action::ADD);
        $log->set_field(change_field_list::FLD_NUMERIC_VALUE);
        $log->group_id = $this->grp_id();
        $log->old_value = null;
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
        $log->set_action(change_action::DELETE);
        $class = $lib->class_to_name($this::class);
        $log->set_table($class . sql_db::TABLE_EXTENSION);
        $log->set_field(change_field_list::FLD_NUMERIC_VALUE);
        $log->old_value = $this->number;
        $log->new_value = null;

        $log->row_id = $this->id();
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
        $ext = $this->table_extension();
        if ($log->add()) {
            if ($this->can_change()) {
                $sql_fld_typ = $sc->get_sql_par_type($new_value);
                if ($new_value == $std_value) {
                    if ($this->has_usr_cfg()) {
                        $msg = 'remove user change of ' . $log->field();
                        log_debug($msg);
                        $db_con->set_class($this::class, true, $ext);
                        $db_con->set_usr($this->user()->id());
                        $fvt_lst = new sql_par_field_list();
                        $fvt_lst->add_field($log->field(), null, sql_par_type::CONST);
                        $qp = $this->sql_update_fields($db_con->sql_creator(), $fvt_lst);
                        $usr_msg = $db_con->update($qp, $msg);
                        $result = $usr_msg->get_message();
                    }
                    $this->del_usr_cfg_if_not_needed(); // don't care what the result is, because in most cases it is fine to keep the user sandbox row
                } else {
                    $msg = 'update of ' . $log->field() . ' to ' . $new_value;
                    log_debug($msg);
                    $db_con->set_class($this::class, false, $ext);
                    $db_con->set_usr($this->user()->id());
                    $fvt_lst = new sql_par_field_list();
                    $fvt_lst->add_field($log->field(), $new_value, $sql_fld_typ);
                    $qp = $this->sql_update_fields($db_con->sql_creator(), $fvt_lst);
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
                    $fvt_lst = new sql_par_field_list();
                    if ($new_value == $std_value) {
                        $msg = 'remove user change of ' . $log->field();
                        log_debug($msg);
                        $fvt_lst->add_field($log->field(), Null, $sql_fld_typ);
                    } else {
                        $msg = 'update of ' . $log->field() . ' to ' . $new_value;
                        log_debug($msg);
                        $fvt_lst->add_field($log->field(), $new_value, $sql_fld_typ);
                    }
                    $qp = $this->sql_update_fields($db_con->sql_creator(), $fvt_lst, new sql_type_list([sql_type::USER]));
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
        // create an empty sandbox object but of the same type and with the same user to detect the fields that should be written
        $db_row = $this->cloned(null);
        return $this->sql_write($sc, $db_row, $sc_par_lst_used);
    }

    /**
     * create the sql statement to update a value or result in the database
     * TODO move the code to an object used by sandbox and sandbox_value
     *
     * @param sql $sc with the target db_type set
     * @param sandbox_value $db_row the sandbox object with the database values before the update
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par the SQL insert statement, the name of the SQL statement and the parameter list
     */
    function sql_update(
        sql           $sc,
        sandbox_value $db_row,
        sql_type_list $sc_par_lst = new sql_type_list([])
    ): sql_par
    {
        // clone the parameter list to avoid changing the given list
        $sc_par_lst_used = clone $sc_par_lst;
        // set the sql query type
        $sc_par_lst_used->add(sql_type::UPDATE);
        return $this->sql_write($sc, $db_row, $sc_par_lst_used);
    }

    /**
     * create a sql statement to insert or update a sandbox object in the database
     * TODO move the code to an object used by sandbox and sandbox_value
     *
     * @param sql $sc with the target db_type set
     * @param sandbox_value|null $db_row the sandbox object with the database values before the update
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par the SQL insert statement, the name of the SQL statement and the parameter list
     */
    function sql_write(
        sql                $sc,
        sandbox_value|null $db_row,
        sql_type_list      $sc_par_lst = new sql_type_list([])
    ): sql_par
    {
        // set the target sql table type for this value
        $sc_par_lst->add($this->table_type());
        // get the name indicator how many id fields are user
        $id_ext = $this->table_extension();
        // get the prime db key list for this sandbox object
        $fvt_lst_id = $this->id_fvt_lst($sc_par_lst);
        // clone to keep the db key list unchanged
        $fvt_lst = clone $fvt_lst_id;
        // add the list of the changed fields to the id list
        $fvt_lst->add_list($this->db_fields_changed($db_row, $sc_par_lst));
        // get the list of all fields that can be changed by the user
        $fld_lst_all = $this->db_fields_all($sc_par_lst);
        $fld_lst_ex_id = array_diff($fld_lst_all, $fvt_lst_id->names());
        // select the changes that should be written e.g. exclude th id in case of an update
        if ($sc_par_lst->is_update()) {
            $fvt_lst = $fvt_lst->get_intersect($fld_lst_ex_id);
        }
        // make the query name unique based on the changed fields
        $lib = new library();
        $ext = sql::NAME_SEP . $lib->sql_field_ext($fvt_lst, $fld_lst_ex_id);
        // create the main query parameter object and set the query name
        $qp = $this->sql_common($sc, $sc_par_lst, $ext, $id_ext);
        // overwrite the standard auto increase id field name
        $sc->set_id_field($this->id_field($sc_par_lst));
        // use the query name for the sql creation
        $sc->set_name($qp->name);
        // actually create the sql statement
        if ($sc_par_lst->incl_log()) {
            // log functions must always use named parameters
            $sc_par_lst->add(sql_type::NAMED_PAR);
            $qp = $this->sql_write_with_log($sc, $qp, $fvt_lst_id, $fvt_lst, $fld_lst_all, $sc_par_lst);
        } else {
            if ($sc_par_lst->is_insert()) {
                $qp->sql = $sc->create_sql_insert($fvt_lst);
                // set the parameters for the query execution
                $qp->par = $fvt_lst->db_values();
            } else {
                $qp->sql = $sc->create_sql_update_fvt($fvt_lst_id, $fvt_lst, $sc_par_lst);
                // and remember the paraemeters used
                $qp->par = $sc->par_values();
            }
        }
        return $qp;
    }

    /**
     * create the sql statement to add a new value and log the changes
     *
     * @param sql $sc sql creator with the target db_type already set
     * @param sql_par_field_list $fvt_lst_id list of id field names, values and sql types additional to the standard id fields
     * @param sql_par_field_list $fvt_lst list of field names, values and sql types additional to the standard id fields
     * @param array $fld_lst_all list of all potential field names of the given object that can be changed by the user
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par the SQL insert statement, the name of the SQL statement and the parameter list
     */
    function sql_write_with_log(
        sql                $sc,
        sql_par            $qp,
        sql_par_field_list $fvt_lst_id,
        sql_par_field_list $fvt_lst,
        array              $fld_lst_all = [],
        sql_type_list      $sc_par_lst = new sql_type_list([])
    ): sql_par
    {
        // init the function body
        $sql = $sc->sql_func_start('', $sc_par_lst);

        // don't use the log parameter for the sub queries
        $sc_par_lst->add(sql_type::NO_ID_RETURN);
        $sc_par_lst_sub = $sc_par_lst->remove(sql_type::LOG);
        $sc_par_lst_sub->add(sql_type::SUB);
        $sc_par_lst_sub->add(sql_type::SELECT_FOR_INSERT);
        $sc_par_lst_log = $sc_par_lst_sub->remove(sql_type::STANDARD);

        // add the change action field to the field list for the log entries
        global $change_action_list;
        $fvt_lst->add_field(
            change_action::FLD_ID,
            $change_action_list->id(change_action::ADD),
            type_object::FLD_ID_SQLTYP
        );

        // get the fields for the value log entry
        $fvt_lst_log = clone $fvt_lst;
        $fvt_lst_log->add_field(group::FLD_ID, $this->grp()->id());

        // for standard prime values add the user only for the log
        if ($sc_par_lst->is_standard() and $sc_par_lst->is_prime()) {
            $fvt_lst_log->add_field(user::FLD_ID, $this->user_id(), sql_par_type::INT);
        }

        // create the log entry for the value
        $qp_log = $sc->sql_func_log_value($this, $this->user(), $fvt_lst_log, $sc_par_lst_log);
        $sql .= ' ' . $qp_log->sql;

        // list of parameters actually used in order of the function usage
        $par_lst_out = new sql_par_field_list();
        $par_lst_out->add_list($qp_log->par_fld_lst);

        // get the data fields and move the unique db key field to the first entry
        $fld_lst_ex_log = array_intersect($fvt_lst->names(), $fld_lst_all);

        // check if other vars than the value have been changed
        $fld_lst_ex_id = array_diff($fld_lst_ex_log, $fvt_lst_id->names());
        $fld_lst_ex_id_and_val = array_diff($fld_lst_ex_id, [
            change_action::FLD_ID,
            sandbox_value::FLD_VALUE,
            sandbox_value::FLD_LAST_UPDATE
        ]);

        // ... and log the value parameter changes if needed
        if (count($fld_lst_ex_id_and_val) > 0) {
            $qp_log = $sc->sql_func_log($this::class, $this->user(), $fld_lst_ex_id_and_val, $fvt_lst_log, $sc_par_lst_log);
            $sql .= ' ' . $qp_log->sql;
            $par_lst_out->add_list($qp_log->par_fld_lst);
        }

        // insert a new row
        $sc_write = clone $sc;
        $qp_write = $this->sql_common($sc_write, $sc_par_lst_sub);
        $sc_write->set_name($qp_write->name);

        // collect the fields that should be written to the database
        $fvt_lst_write = new sql_par_field_list();
        // add the id to the changes
        // TODO maybe net out with calling function and / or make list correct from beginning
        $fvt_lst_all = clone $fvt_lst;
        if ($sc_par_lst->is_update()) {
            $fvt_lst_all->add_list($fvt_lst_id);
        }
        if ($sc_par_lst->is_insert()) {
            foreach ($fvt_lst_id->names() as $fld) {
                $fvt_lst_write->add($fvt_lst_all->get($fld));
            }
        }
        if (!$sc_par_lst->is_standard()) {
            if ($fvt_lst_all->has_name(user::FLD_ID) and $sc_par_lst->is_insert()) {
                $fvt_lst_write->add($fvt_lst_all->get(user::FLD_ID));
            }
        }
        $fvt_lst_write->add($fvt_lst_all->get(sandbox_value::FLD_VALUE));
        if (!$sc_par_lst->is_standard()) {
            $fvt_lst_write->add($fvt_lst_all->get(sandbox_value::FLD_LAST_UPDATE));
        }

        if ($sc_par_lst->is_insert()) {
            // create the sql to actually add the value to the database
            $qp_write->sql = $sc_write->create_sql_insert($fvt_lst_write, $sc_par_lst_sub);
        } else {
            // create the sql to actually update the value to the database
            $qp_write->sql = $sc_write->create_sql_update_fvt($fvt_lst_id, $fvt_lst_write, $sc_par_lst_sub);
        }
        // add the insert row to the function body
        $sql .= ' ' . $qp_write->sql . ' ';
        // add the fields used to the parameter list except the sql Now() function call
        $fvt_lst_write->del(sandbox_value::FLD_LAST_UPDATE);
        $par_lst_out->add_list($fvt_lst_write);
        if ($sc_par_lst->is_update()) {
            $par_lst_out->add_list($fvt_lst_id);
        }

        // close the sql function statement
        $sql .= $sc->sql_func_end();

        // create the query parameters for the actual change
        $qp_chg = clone $qp;
        $qp_chg->sql = $sc->create_sql_insert($par_lst_out, $sc_par_lst);

        // merge all together and create the function
        $qp->sql = $qp_chg->sql . $sql . ';';
        $qp->par = $par_lst_out->values();

        // create the call sql statement
        return $sc->sql_call($qp, $qp_chg->name, $par_lst_out);
    }

    /**
     * create the sql statement to update a value in the database
     * based on the given list of fields and values
     *
     * @param sql $sc with the target db_type set
     * @param sql_par_field_list $fvt_lst list of field names, values and sql types additional to the standard id and name fields
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par the SQL insert statement, the name of the SQL statement and the parameter list
     */
    function sql_update_fields(
        sql                $sc,
        sql_par_field_list $fvt_lst,
        sql_type_list      $sc_par_lst = new sql_type_list([])
    ): sql_par
    {
        // set the sql query type
        $sc_par_lst->add(sql_type::UPDATE);
        // set the target sql table type for this value e.g. add prime
        $sc_par_lst->add($this->table_type());
        // get the name indicator how many id fields are user
        $id_ext = $this->table_extension();

        // get the sql name extension to make the name unique based on the fields that should be updated
        // TODO replace with the number base notation
        $lib = new library();
        $ext = implode(sql::NAME_SEP, $lib->sql_name_shorten($fvt_lst->names()));
        if ($ext != '') {
            $ext = sql::NAME_SEP . $ext;
        }
        // set the name of the query parameters
        $qp = $this->sql_common($sc, $sc_par_lst, $ext, $id_ext);
        // use the query name for the sql creation
        $sc->set_name($qp->name);
        // the value might have more than one unique db key
        $id_fields = $this->sql_id_fields();
        // get the db key values related to the db prime key
        $id_lst = $this->sql_id_val($id_fields);
        // add the user id if a user specific value should be saved
        if ($sc_par_lst->is_usr_tbl()) {
            $id_fields[] = user::FLD_ID;
            if (!is_array($id_lst)) {
                $id_lst = [$id_lst];
            }
            $id_lst[] = $this->user()->id();
        }
        // finally actually create the sql
        $qp->sql = $sc->create_sql_update($id_fields, $id_lst, $fvt_lst);
        // and remember the paraemeters used
        $qp->par = $sc->par_values();
        return $qp;
    }

    /**
     * create the sql statement to delete a value in the database
     * TODO check if user specific overwrites can be deleted
     *
     * @param sql $sc with the target db_type set
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par the SQL insert statement, the name of the SQL statement and the parameter list
     */
    function sql_delete(
        sql           $sc,
        sql_type_list $sc_par_lst = new sql_type_list([])
    ): sql_par
    {
        // clone the parameter list to avoid changing the given list
        $sc_par_lst_used = clone $sc_par_lst;
        // set the sql query type
        $sc_par_lst_used->add(sql_type::DELETE);
        // set the target sql table type for this value e.g. add prime
        $sc_par_lst_used->add($this->table_type());
        // get the name indicator how many id fields are user
        $id_ext = $this->grp->table_extension();
        // get the prime db key list for this sandbox object
        $fvt_lst_id = $this->id_fvt_lst($sc_par_lst_used);

        // create the main query parameter object and set the query name
        $qp = $this->sql_common($sc, $sc_par_lst_used, '', $id_ext);
        $sc->set_name($qp->name);
        if ($sc_par_lst_used->incl_log()) {
            // log functions must always use named parameters
            $sc_par_lst_used->add(sql_type::NAMED_PAR);
            $qp = $this->sql_delete_and_log($sc, $qp, $fvt_lst_id, $sc_par_lst_used);
        } else {
            // TODO add test fpr !$sc_par_lst_used->exclude_sql()
            $qp->sql = $sc->create_sql_delete_fvt($fvt_lst_id, $sc_par_lst_used);
            // and remember the paraemeters used
            $qp->par = $sc->par_values();
        }
        return $qp;
    }

    /**
     * create a sql statement to delete or exclude a value
     *
     * @param sql $sc the sql creator object with the db type set
     * @param sql_par $qp the query parameter with the name already set
     * @param sql_par_field_list $fvt_lst_id name, value and type of the id field (or list of field names)
     * @param sql_type_list $sc_par_lst
     * @return sql_par
     */
    private function sql_delete_and_log(
        sql                $sc,
        sql_par            $qp,
        sql_par_field_list $fvt_lst_id,
        sql_type_list      $sc_par_lst = new sql_type_list([])
    ): sql_par
    {

        // overwrite the standard auto increase id field name
        $sc->set_id_field($this->id_field($sc_par_lst));
        // use the query name for the sql creation
        $sc->set_name($qp->name);

        // init the function body
        $sql = $sc->sql_func_start('', $sc_par_lst);

        // don't use the log parameter for the sub queries
        $sc_par_lst->add(sql_type::NO_ID_RETURN);
        $sc_par_lst_sub = $sc_par_lst->remove(sql_type::LOG);
        $sc_par_lst_sub->add(sql_type::SUB);
        $sc_par_lst_sub->add(sql_type::DELETE_PART);
        $sc_par_lst_sub->add(sql_type::SELECT_FOR_INSERT);
        $sc_par_lst_log = $sc_par_lst_sub->remove(sql_type::STANDARD);

        // list of parameters actually used in order of the function usage
        $fvt_lst_log = new sql_par_field_list();

        // add the change action field to the field list for the log entries
        global $change_action_list;
        $fvt_lst_log->add_field(
            change_action::FLD_ID,
            $change_action_list->id(change_action::DELETE),
            type_object::FLD_ID_SQLTYP
        );

        // get the fields for the value log entry
        $fvt_lst_log->add_field(group::FLD_ID, $this->grp()->id());

        // for standard prime values add the user only for the log
        if ($sc_par_lst->is_standard() and $sc_par_lst->is_prime()) {
            $fvt_lst_log->add_field(user::FLD_ID, $this->user_id(), sql_par_type::INT);
        }

        // create the log entry for the value
        $qp_log = $sc->sql_func_log_value($this, $this->user(), $fvt_lst_log, $sc_par_lst_log);
        $sql .= ' ' . $qp_log->sql;

        // list of parameters actually used in order of the function usage
        $par_lst_out = new sql_par_field_list();
        $par_lst_out->add_list($qp_log->par_fld_lst);

        // set missing par names
        foreach ($fvt_lst_id->lst as $fvt) {
            if ($fvt->par_name == '') {
                $fvt->par_name = '_' . $fvt->name;
            }
        }

        // create the actual delete or exclude statement
        $sc_delete = clone $sc;
        $qp_delete = $this->sql_common($sc_delete, $sc_par_lst_log);
        $qp_delete->sql = $sc_delete->create_sql_delete_fvt_new($fvt_lst_id, $sc_par_lst_sub);
        // add the insert row to the function body
        $sql .= ' ' . $qp_delete->sql . ' ';

        $sql .= $sc->sql_func_end();

        // create the query parameters for the call
        $qp_func = clone $qp;
        $sc_par_lst_func = clone $sc_par_lst;
        $sc_par_lst_func->add(sql_type::FUNCTION);
        $sc_par_lst_func->add(sql_type::DELETE);
        $sc_par_lst_func->add(sql_type::NO_ID_RETURN);
        if ($sc_par_lst->exclude_sql()) {
            $sc_par_lst_func->add(sql_type::EXCLUDE);
        }
        $qp_func = $this->sql_common($sc_delete, $sc_par_lst_func);

        $par_lst_out->add_list($fvt_lst_id);
        $qp_func->sql = $sc->create_sql_delete_fvt_new($fvt_lst_id, $sc_par_lst_func, $par_lst_out);

        // merge all together and create the function
        $qp->sql = $qp_func->sql . ' ' . $sql . ';';

        // create the function call
        $qp->call_sql = ' ' . sql::SELECT . ' ' . $qp->name . ' (';

        $call_val_str = $par_lst_out->par_sql($sc);

        $qp->call_sql .= $call_val_str . ');';

        return $qp;
    }

    /**
     * @return string|array with the id field name or with the array of id fields
     */
    private function sql_id_fields(): string|array
    {
        if ($this->grp->is_prime()) {
            return $this->grp->id_names(true);
        } else {
            return $this->id_field();
        }
    }

    /**
     * @param string|array $id_fields the id field name or with the array of id fields
     * @return int|string|array with the unique db key or with the array of keys that in combination are unique
     */
    private function sql_id_val(string|array $id_fields): int|string|array
    {
        $id = $this->id();
        if (is_array($id_fields)) {
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
        return $id_lst;
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
    function db_fields_all(sql_type_list $sc_par_lst = new sql_type_list([])): array
    {
        if ($this->is_prime() or $this->is_main()) {
            $fields = $this->grp->id_names();
        } else {
            $fields = [group::FLD_ID];
        }
        if (!$sc_par_lst->is_standard()) {
            $fields[] = user::FLD_ID;
        }
        $fields[] = self::FLD_VALUE;
        if (!$sc_par_lst->is_standard()) {
            $fields[] = self::FLD_LAST_UPDATE;
        }
        return $fields;
    }

    /**
     * get a list of database field names, values and types that have been updated
     * the last_update field is excluded here because this is an internal only field
     *
     * @param sandbox_multi|sandbox_value $sbx the same value sandbox as this to compare which fields have been changed
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par_field_list with the field names of the object and any child object
     */
    function db_fields_changed(
        sandbox_multi|sandbox_value $sbx,
        sql_type_list               $sc_par_lst = new sql_type_list([])
    ): sql_par_field_list
    {
        $sc = new sql();
        $do_log = $sc_par_lst->incl_log();
        $is_insert = $sc_par_lst->is_insert();
        $is_update = $sc_par_lst->is_update();
        $table_id = $sc->table_id($this::class);

        /*
         * TODO check if sandbox named function match this logic
         * if insert always add user as long as not standard
         * on update the user is only used for the where condition
         */

        $lst = new sql_par_field_list();
        if ($is_insert) {
            if ($this::class == result::class and $this->is_main()) {
                $lst = $this->grp->id_fvt_main();
            } else {
                $lst = $this->grp->id_fvt();
            }
        }
        if (!$sc_par_lst->is_standard()) {
            if ($is_insert) {
                $lst->add_user($this, $sbx, $do_log, $table_id);
            }
        }
        // TODO check that all numeric fields are checked with !== to force writing the value zero
        if ($sbx->number() !== $this->number()) {
            if ($is_update) {
                $lst->add_field(
                    self::FLD_VALUE,
                    $this->number(),
                    sql_field_type::NUMERIC_FLOAT,
                    $sbx->number()
                );
            } else {
                $lst->add_field(
                    self::FLD_VALUE,
                    $this->number(),
                    sql_field_type::NUMERIC_FLOAT
                );
            }
        }
        if (!$sc_par_lst->is_standard()) {
            // if any field has been updated, update the last_update field also
            if (!$lst->is_empty_except_internal_fields() or $this->last_update() == null) {
                $lst->add_field(
                    self::FLD_LAST_UPDATE,
                    sql::NOW,
                    sql_field_type::TIME
                );
            }
        }
        return $lst;
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
     * @param float|null $number the target value
     * @return $this a clone with the number changed
     */
    function cloned(?float $number): sandbox_value
    {
        $obj_cpy = clone $this;
        $obj_cpy->reset();
        $obj_cpy->set_number($number);
        return $obj_cpy;
    }

    /**
     * create a clone and reset the timestamp to trigger the updating the dependent results
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
            $sc_par_lst = new sql_type_list([$this->table_type()]);
            $id_fields = $this->id_field($sc_par_lst);
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


