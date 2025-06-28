<?php

/*

    model/phrase/group.php - a combination of a word list and a triple_list
    -----------------------------

    the prime group is designed to be useful for normal values e.g. the number of inhabitants in Zurich 2023
    the index group is designed to be useful for structured values e.g.
    the index big group is designed to be useful for highly structured values e.g. the ISIN as a 48-bit value with up to 65k fields (because each field can be a triple a multi dimensional tables can be store with index big)

    TODO add index and index big tables
    TODO remove the fields word_ids, triple_ids and id_order
    TODO rename to group (and element_group to combination)
    TODO move name and description to user_groups
    TODO use 32 bit key for the phrase_id (and prepare a 64 bit key in needed)
    TODO use 512 bit key for phrase groups for up to 16 phrases
    TODO prepare a 4096 key for long phrase groups up to 64 phrases

    a kind of phrase list, but separated into two different lists

    a phrase group is always an unsorted list of phrases and is used to select a value
    for the selection the phrases are always connected with AND
    for an OR selection a parent phrase should be use (or a temp phrase is created)
    if the order of phrases is relevant, they should be ordered by creating new phrases


    phrase groups are not part of the user sandbox, because this is a kind of hidden layer
    The main intention for word groups is to save space and execution time

    The main sections of this object are
    - db const:          const for the database link
    - object vars:       the variables of this word object
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

namespace cfg\group;

include_once SHARED_ENUM_PATH . 'messages.php';
include_once DB_PATH . 'sql.php';
include_once DB_PATH . 'sql_creator.php';
include_once DB_PATH . 'sql_db.php';
include_once DB_PATH . 'sql_field_default.php';
include_once DB_PATH . 'sql_field_type.php';
include_once DB_PATH . 'sql_par.php';
include_once DB_PATH . 'sql_par_field_list.php';
include_once DB_PATH . 'sql_par_type.php';
include_once DB_PATH . 'sql_type.php';
include_once DB_PATH . 'sql_type_list.php';
include_once MODEL_PHRASE_PATH . 'phr_ids.php';
include_once MODEL_PHRASE_PATH . 'phrase.php';
include_once MODEL_PHRASE_PATH . 'phrase_list.php';
include_once MODEL_RESULT_PATH . 'result.php';
include_once MODEL_SANDBOX_PATH . 'sandbox_multi.php';
include_once MODEL_SANDBOX_PATH . 'sandbox_value.php';
include_once MODEL_USER_PATH . 'user.php';
include_once MODEL_USER_PATH . 'user_message.php';
include_once MODEL_VALUE_PATH . 'value.php';
include_once MODEL_VALUE_PATH . 'value_base.php';
include_once MODEL_WORD_PATH . 'word.php';
include_once SHARED_CONST_PATH . 'groups.php';
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
use cfg\db\sql_par_type;
use cfg\db\sql_type;
use cfg\db\sql_type_list;
use cfg\phrase\phr_ids;
use cfg\phrase\phrase;
use cfg\phrase\phrase_list;
use cfg\result\result;
use cfg\sandbox\sandbox_multi;
use cfg\sandbox\sandbox_value;
use cfg\user\user;
use cfg\user\user_message;
use cfg\value\value;
use cfg\word\word;
use shared\const\groups;
use shared\enum\messages as msg_id;
use shared\json_fields;
use shared\library;
use shared\types\api_type_list;

class group extends sandbox_multi
{

    /*
     * db const
     */

    // object specific database and JSON object field names
    // *_COM: the description of the field
    // *_SQL_TYP is the sql data type used for the field
    const FLD_ID_COM = 'the 64-bit prime index to find the -=class=-';
    const FLD_ID_COM_USER = 'the 64-bit prime index to find the user -=class=-';
    const FLD_ID = 'group_id';
    const FLD_NAME_COM = 'the user specific group name which can contain the phrase names in a different order to display the group (does not need to be unique)';
    const FLD_NAME = 'group_name';
    const FLD_NAME_SQL_TYP = sql_field_type::TEXT;
    const FLD_DESCRIPTION_COM = 'the user specific description for mouse over helps';
    const FLD_DESCRIPTION = 'description';
    const FLD_DESCRIPTION_SQL_TYP = sql_field_type::TEXT;

    // comments used for the database creation
    const TBL_COMMENT = 'to add a user given name using a 512-bit group id index for up to 16 32-bit phrase ids including the order';
    const TBL_COMMENT_PRIME = 'to add a user given name using a 64-bit group id index for up to four 16-bit phrase ids including the order';
    const TBL_COMMENT_INDEX = 'to add a user given name using a 64-bit group id index for one 32-bit and two 16-bit phrase ids including the order';
    const TBL_COMMENT_BIG = 'to add a user given name using a group id index with a variable length for more than 16 32-bit phrase ids including the order';
    const TBL_COMMENT_INDEX_BIG = 'to add a user given name using a 64-bit group id index for one 48-bit and one 16-bit phrase id including the order';

    // list of fields with parameters used for the database creation
    // the fields that can be changed by the user
    const FLD_KEY_PRIME = array(
        [group::FLD_ID, sql_field_type::KEY_INT_NO_AUTO, sql_field_default::NOT_NULL, '', '', self::FLD_ID_COM],
    );
    const FLD_KEY_PRIME_USER = array(
        [group::FLD_ID, sql_field_type::KEY_PART_INT, sql_field_default::NOT_NULL, '', '', self::FLD_ID_COM_USER],
    );
    const FLD_LST_USER_CAN_CHANGE = array(
        [self::FLD_NAME, self::FLD_NAME_SQL_TYP, sql_field_default::NULL, '', '', self::FLD_NAME_COM],
        [self::FLD_DESCRIPTION, self::FLD_DESCRIPTION_SQL_TYP, sql_field_default::NULL, '', '', self::FLD_DESCRIPTION_COM],
    );

    // all database field names excluding the id
    const FLD_NAMES = array(
        self::FLD_DESCRIPTION
    );
    // list of fixed tables where a group name overwrite might be stored
    // TODO check if this can be used somewhere else means if there are unwanted repeating
    const TBL_LIST = array(
        [sql_type::MOST],
        [sql_type::PRIME],
        [sql_type::BIG]
    );


    /*
     * object vars
     */

    // database fields
    private phrase_list $phr_lst; // the phrase list object
    public ?string $name;         // maybe later the user should have the possibility to overwrite the generic name, but this is not user at the moment
    public ?string $description;  // the automatically created generic name for the word group, used for a quick display of values

    // true if the object has been saved in the database
    // needed for groups because neither last_update like for values and results can be used
    // nor can the database sequence id be used that indicates the status e.g. for named objects
    private bool $is_saved;


    /*
     * construct and map
     */

    /**
     * set the user which is needed in all cases
     * @param user $usr the user who requested to see this phrase group
     */
    function __construct(user $usr, int|string $id = 0, array $prh_names = [])
    {
        parent::__construct($usr);

        $this->reset();

        if ($id > 0) {
            $this->set_id($id);
        }
        $this->add_phrase_names($prh_names);
    }

    function reset(): void
    {
        $this->set_id(0);
        $this->name = null;
        $this->description = null;
        $this->phr_lst = new phrase_list($this->user());
        $this->is_saved = false;
    }

    /**
     * map the database fields to one db row to this phrase group object
     *
     * @param array|null $db_row with the data directly from the database
     * @param string $id_fld the name of the id field
     * @return bool true if one phrase group is found
     */
    function row_mapper(?array $db_row, string $id_fld = ''): bool
    {
        $result = false;
        if ($db_row != null) {
            $this->set_id(0);
            if (array_key_exists(self::FLD_ID, $db_row)) {
                $this->set_id($db_row[self::FLD_ID]);
                $grp_id = new group_id();
                $phr_ids = new phr_ids($grp_id->get_array($db_row[self::FLD_ID]));
                $this->load_lst($phr_ids);
                $result = true;
            }
        }
        if ($result) {
            $this->name = $db_row[self::FLD_NAME];
            $this->description = $db_row[self::FLD_DESCRIPTION];
            $this->is_saved = true;
        }
        return $result;
    }

    /**
     * map a group api json to this model group object
     * similar to the import_obj function but using the database id instead of names as the unique key
     * @param array $api_json the api array with the group values that should be mapped
     * @return user_message the message for the user why the action has failed and a suggested solution
     */
    function api_mapper(array $api_json): user_message
    {
        $msg = parent::api_mapper($api_json);

        foreach ($api_json as $key => $value) {

            if ($key == json_fields::ID) {
                $this->set_id($value);
            }
            if ($key == json_fields::NAME) {
                $this->set_name($value);
            }
            if ($key == json_fields::DESCRIPTION) {
                $this->set_description($value);
            }

        }

        return $msg;
    }


    /*
     * api
     */

    /**
     * create an array for the api json message
     * differs from the export array by using the internal id instead of the names
     * @param api_type_list $typ_lst configuration for the api message e.g. if phrases should be included
     * @param user|null $usr the user for whom the api message should be created which can differ from the session user
     * @return array the filled array used to create the api json message to the frontend
     */
    function api_json_array(api_type_list $typ_lst, user|null $usr = null): array
    {
        if ($this->is_excluded() and !$typ_lst->test_mode()) {
            $vars = [];
            $vars[json_fields::ID] = $this->id();
            $vars[json_fields::EXCLUDED] = true;
        } else {
            $vars = parent::api_json_array($typ_lst, $usr);
            $vars[json_fields::ID] = $this->id();
            if ($this->name != null or !$typ_lst->include_phrases()) {
                $vars[json_fields::NAME] = $this->name();
            }
            if ($this->description() != null) {
                $vars[json_fields::DESCRIPTION] = $this->description();
            }
            if ($typ_lst->include_phrases()) {
                $phr_lst = $this->phrase_list();
                $vars[json_fields::PHRASES] = $phr_lst->api_json_array($typ_lst);
            }

        }

        return $vars;
    }


    /*
     * set and get
     */

    /**
     * set the phrase list of this group
     * and return the unique database id of this group
     * @param phrase_list $phr_lst sorted list of phrases for this group
     * @return int|string $id either a 62-bit int, a 512-bit id with 16 phrase ids or a text with more than 16 +/- separated 6 char alpha_num coded phrase ids
     */
    function set_phrase_list(phrase_list $phr_lst): int|string
    {
        $this->phr_lst = $phr_lst;
        return $this->set_id_from_phrase_list($phr_lst);
    }

    /**
     * set the phrase list based in the group id
     * @param int|string $id the group id that should be used to set the phrase list
     * @param phrase_list|null $phr_lst_in list of the phrases already loaded to reduce traffic
     * @return bool true if the list has be set successfully
     */
    function set_phrase_list_by_id(int|string $id, ?phrase_list $phr_lst_in = null): bool
    {
        $grp_id = new group_id();
        $phr_ids = new phr_ids($grp_id->get_array($id));
        $phr_lst = new phrase_list($this->user());
        if ($phr_lst->load_names_by_ids($phr_ids, $phr_lst_in)) {
            $this->set_phrase_list($phr_lst);
            return true;
        } else {
            return false;
        }

    }

    /**
     * set the unique database id of this group
     * @param int|string $id either a 62-bit int, a 512-bit id with 16 phrase ids or a text with more than 16 +/- separated 6 char alpha_num coded phrase ids
     */
    function set_id(int|string $id): void
    {
        $this->id = $id;
    }

    /**
     * set the user given name for this group
     * @param string $name the name as given by the user
     * @return void
     */
    function set_name(string $name = ''): void
    {
        if ($name != '') {
            $this->name = $name;
        } else {
            if ($this->has_phrase_list()) {
                $this->name = implode(',', $this->phr_lst->names());
            } else {
                log_warning('name of phrase group ' . $this->dsp_id() . ' missing');
            }
        }
    }

    function set_description(string $description): void
    {
        $this->description = $description;
        $this->is_saved = false;
    }

    /**
     * @return string|null the description of the value, which is the description of the phrase group
     */
    function description(): ?string
    {
        return $this->description;
    }

    function phrase_list(): phrase_list
    {
        return $this->phr_lst;
    }

    /**
     * @return bool true if the phrase list matches the id and updated
     */
    function has_phrase_list(): bool
    {
        if ($this->phr_lst->count() > 0) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * @param bool $no_fill if true the id is not filled up to the complete key size e.g. for the api messages
     * @return int|string either a 62-bit int, a 512-bit id with 16 phrase ids or a text with more than 16 +/- separated 6 char alpha_num coded phrase ids
     * the internal null value is used to detect if database saving has been tried
     */
    function id(bool $no_fill = false): int|string
    {
        if (is_numeric($this->id)) {
            return (int)$this->id;
        } else {
            if ($no_fill) {
                $id = $this->id;
                $grp_id = new id();
                $zero_id = $grp_id->int2alpha_num(0);
                while (str_ends_with($id, $zero_id)) {
                    $id = str_replace($zero_id, '', $id);
                }
                return $id;
            } else {
                return $this->id;
            }
        }
    }

    /**
     * @return array with the ids of this group
     */
    function id_lst(): array
    {
        $grp_id = new group_id();
        return $grp_id->get_array($this->id());
    }

    /**
     * @return array with the numbered names of this group
     */
    function id_names(bool $all = false, int $max = group_id::PRIME_PHRASES_STD): array
    {
        $name_lst = array();
        $grp_id = new group_id();
        if ($all) {
            for ($pos = 1; $pos <= $max; $pos++) {
                $name_lst[] = phrase::FLD_ID . '_' . $pos;
            }
        } else {
            $pos = 1;
            foreach ($grp_id->get_array($this->id()) as $id) {
                $name_lst[] = phrase::FLD_ID . '_' . $pos;
                $pos++;
            }
        }
        return $name_lst;
    }

    /**
     * @return sql_par_field_list with the id field or fields of this group
     */
    function id_fvt(): sql_par_field_list
    {
        $fvt_lst = new sql_par_field_list();
        if ($this->is_prime()) {
            $grp_id = new group_id();
            $pos = 1;
            foreach ($grp_id->get_array($this->id()) as $id) {
                $name = phrase::FLD_ID . '_' . $pos;
                $fvt_lst->add_field($name, $id, sql_field_type::INT_SMALL);
                $pos++;
            }
        } else {
            if ($this->is_big()) {
                $fvt_lst->add_field(group::FLD_ID, $this->id(), sql_field_type::TEXT);
            } else {
                $fvt_lst->add_field(group::FLD_ID, $this->id(), sql_field_type::KEY_512);
            }
        }
        return $fvt_lst;
    }

    function id_fvt_main(): sql_par_field_list
    {
        $fvt_lst = new sql_par_field_list();
        $grp_id = new group_id();
        $pos = 1;
        foreach ($grp_id->get_array($this->id()) as $id) {
            $name = phrase::FLD_ID . '_' . $pos;
            $fvt_lst->add_field($name, $id, sql_field_type::INT_SMALL);
            $pos++;
        }
        return $fvt_lst;
    }

    /**
     * get the table name extension for value, result and group tables
     * depending on the number of phrases a different table for value and results is used
     * for faster searching
     *
     * @param bool $with_phrase_count false if the number of phrases are not relevant e.g. even for prime tables
     * @return string the extension for the table name based on the id
     */
    function table_extension(bool $with_phrase_count = true): string
    {
        $grp_id = new group_id();
        return $grp_id->table_extension($this->id(), $with_phrase_count);
    }

    /**
     *
     * @return sql_type the table type based on the id e.g. "MOST" for a group with 5 to 16 phrases
     */
    function table_type(): sql_type
    {
        $grp_id = new group_id();
        return $grp_id->table_type($this->id());
    }

    /**
     * set the unique database id of this group
     * @param phrase_list $phr_lst sorted list of phrases for this group
     * @return int|string $id either a 62-bit int, a 512-bit id with 16 phrase ids or a text with more than 16 +/- separated 6 char alpha_num coded phrase ids
     */
    function set_id_from_phrase_list(phrase_list $phr_lst): int|string
    {
        $grp_id = new group_id();
        $this->set_id($grp_id->get_id($phr_lst));
        return $this->id();
    }

    /**
     * @return bool if the id of the group is valid
     */
    function is_id_set(): bool
    {
        if ($this->id() != 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * create a clone and update the name (mainly used for unit testing)
     *
     * @param string $name the target name
     * @return $this a clone with the name changed
     */
    function renamed(string $name): group
    {
        $obj_cpy = clone $this;
        $obj_cpy->set_name($name);
        return $obj_cpy;
    }

    /**
     * dummy function that should always be overwritten by the child object
     * @return string
     */
    function name_field(): string
    {
        return self::FLD_NAME;
    }

    /**
     * @return group this object to match the related value and result functions
     */
    function grp(): group
    {
        return $this;
    }


    /*
     * information
     */

    /**
     * @return bool true if the group has been at least once saved to the database
     */
    function is_saved(): bool
    {
        return $this->is_saved;
    }

    /**
     * mark that the group has been saved and that the object matches the db entry
     */
    function set_saved(): void
    {
        $this->is_saved = true;
    }


    /*
     * sql create
     */

    /**
     * the sql statement to create the group tables
     *
     * @param sql_creator $sc with the target db_type set
     * @return string the sql statement to create the table
     */
    function sql_table(sql_creator $sc): string
    {
        return $this->sql_creator($sc)[0];
    }

    /**
     * the sql statements to create all indices for the group tables used to store the group name changes of a user
     *
     * @param sql_creator $sc with the target db_type set
     * @return string the sql statement to create the indices
     */
    function sql_index(sql_creator $sc): string
    {
        return $this->sql_creator($sc)[1];
    }

    /**
     * the sql statements to create all foreign keys for the group tables used to store the group name changes of a user
     *
     * @param sql_creator $sc with the target db_type set
     * @return string the sql statement to create the foreign keys
     */
    function sql_foreign_key(sql_creator $sc): string
    {
        return $this->sql_creator($sc)[2];
    }

    /**
     * the sql statements to truncate the group tables
     *
     * @param sql_creator $sc with the target db_type set
     * @return string the sql statement to create the table
     */
    function sql_truncate(sql_creator $sc): string
    {
        return $this->sql_creator($sc)[3];
    }

    /**
     * the sql statements to create
     * 0 => the group tables
     * 1 => all indices for the group tables used to store the group name changes of a user
     * 1 => all foreign keys for the group tables used to store the group name changes of a user
     *
     * @param sql_creator $sc with the target db_type set
     * @return array the sql statement to create the table
     */
    private function sql_creator(sql_creator $sc): array
    {
        $sql = $sc->sql_separator();
        $sql_index = $sc->sql_separator();
        $sql_foreign = $sc->sql_separator();
        $sql_truncate = '';
        $sql_lst = [$sql, $sql_index, $sql_foreign, $sql_truncate];
        $sql_lst = $this->sql_one_tbl($sc, new sql_type_list([sql_type::MOST]), sandbox_value::FLD_KEY, $this::TBL_COMMENT, $sql_lst);
        $sql_lst = $this->sql_one_tbl($sc, new sql_type_list([sql_type::MOST, sql_type::USER]), sandbox_value::FLD_KEY_USER, $this::TBL_COMMENT, $sql_lst);
        $sql_lst = $this->sql_one_tbl($sc, new sql_type_list([sql_type::PRIME]), group::FLD_KEY_PRIME, $this::TBL_COMMENT_PRIME, $sql_lst);
        $sql_lst = $this->sql_one_tbl($sc, new sql_type_list([sql_type::PRIME, sql_type::USER]), group::FLD_KEY_PRIME_USER, $this::TBL_COMMENT_PRIME, $sql_lst);
        $sql_lst = $this->sql_one_tbl($sc, new sql_type_list([sql_type::BIG]), sandbox_value::FLD_KEY_BIG, $this::TBL_COMMENT_BIG, $sql_lst);
        return $this->sql_one_tbl($sc, new sql_type_list([sql_type::BIG, sql_type::USER]), sandbox_value::FLD_KEY_BIG_USER, $this::TBL_COMMENT_BIG, $sql_lst);
    }

    /**
     * add the sql statements for one table to the given array of sql statements
     * @param sql_creator $sc the sql creator object with the target db_type set
     * @param sql_type_list $sc_par_lst of parameters for the sql creation
     * @param array $key_fld with the parameter for the table primary key field
     * @param string $tbl_comment the comment for the table in the sql statement
     * @param array $sql_lst the list with the sql statements created until now
     * @return array the list of sql statements including the statements created by this function call
     */
    private function sql_one_tbl(
        sql_creator   $sc,
        sql_type_list $sc_par_lst,
        array         $key_fld,
        string        $tbl_comment,
        array         $sql_lst
    ): array
    {
        $sc->set_class($this::class, $sc_par_lst);
        $fields = array_merge($key_fld, sandbox_value::FLD_ALL_OWNER, $this::FLD_LST_USER_CAN_CHANGE);
        $usr_tbl = $sc_par_lst->is_usr_tbl();
        if ($usr_tbl) {
            $fields = array_merge($key_fld, sandbox_value::FLD_ALL_CHANGER, $this::FLD_LST_USER_CAN_CHANGE);
        }
        $sql_lst[0] .= parent::sql_table_create($sc, $sc_par_lst, $fields, $tbl_comment);
        $sql_lst[1] .= parent::sql_index_create($sc, $sc_par_lst, $fields);
        $sql_lst[2] .= parent::sql_foreign_key_create($sc, $sc_par_lst, $fields);
        $sql_lst[3] .= parent::sql_truncate_create($sc, $sc_par_lst);
        return $sql_lst;
    }


    /*
     * load
     */

    /**
     * TODO move (and other functions) to db_object and rename the existing db_object to db_id_object
     * just set the class name for the user sandbox function
     * load a word object by database id
     * @param int|string $id the id of the group
     * @param ?sql_type $typ for group this field is not used
     * @return int|string the id of the object found and zero if nothing is found
     */
    function load_by_id(
        int|string $id,
        ?sql_type  $typ = null
    ): int|string
    {
        global $db_con;

        log_debug($id);
        $qp = $this->load_sql_by_id($db_con->sql_creator(), $id);
        return $this->load($qp);
    }

    /**
     * load a group object from the database selected by the group name
     * @param string $name the id of the group
     * @return int|string the id of the object found and zero if nothing is found
     */
    function load_by_name(string $name): int|string
    {
        global $db_con;

        log_debug($name);
        $qp = $this->load_sql_by_name($db_con->sql_creator(), $name);
        return $this->load($qp);
    }

    /**
     * function to load group based on a phrase list
     * if no user hase change the group name simply the generated name may be returned
     * because not all groups have a database entry
     *
     * @param phrase_list $phr_lst list of phrases
     * @return bool
     */
    function load_by_phr_lst(phrase_list $phr_lst): bool
    {
        global $db_con;
        $qp = $this->load_sql_by_phrase_list($db_con->sql_creator(), $phr_lst);
        if ($this->load($qp)) {
            return true;
        } else {
            $this->set_phrase_list($phr_lst);
            return $this->is_id_set();
        }
    }

    /**
     * shortcut function to create the phrase list and load the group with one call
     * @param phr_ids $ids list of phrase ids where triples have a negative id
     * @return bool
     */
    function load_by_ids(phr_ids $ids): bool
    {
        $phr_lst = new phrase_list($this->user());
        $phr_lst->load_names_by_ids($ids);
        return $this->load_by_phr_lst($phr_lst);
    }

    /**
     * load the word and triple objects based on the ids load from the database if needed
     * TODO review
     */
    private function load_lst(?phr_ids $ids = null): void
    {
        if (!$this->phrase_list()->loaded($ids)) {
            if ($ids == null) {
                $ids = $this->phrase_list()->phrase_ids();
            }
            $this->phrase_list()->load_by_ids($ids);
        }
    }

    /**
     * load the standard group use by most users for the given phrase group and time
     *
     * @return bool true if the standard value has been loaded
     */
    function load_standard_by_id(): bool
    {
        global $db_con;
        $qp = $this->load_standard_sql($db_con->sql_creator());
        return parent::load_standard($qp);
    }

    /**
     * load the standard group use by most users for the given phrase group and time
     * @param string $name the name given by the user for the group
     * @return bool true if the standard value has been loaded
     */
    function load_standard_by_name(string $name): bool
    {
        global $db_con;
        $qp = $this->load_standard_by_name_sql($db_con->sql_creator(), $name);
        return parent::load_standard($qp);
    }

    /**
     * load one database row e.g. word, triple, value, formula, result, view, component or log entry from the database
     * @param sql_par $qp the query parameters created by the calling function
     * @return int|string the id of the object found and zero if nothing is found
     */
    protected function load(sql_par $qp): int|string
    {
        if (!parent::load_without_id_return($qp)) {
            $this->set_phrase_list_by_id($this->id());
        }
        return $this->id();
    }

    /**
     * create an SQL statement to retrieve a user sandbox object by id from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param int|string $id the id of the phrase group, which can also be a string representing a 512-bit key
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_id(
        sql_creator $sc,
        int|string  $id,
        ?sql_type   $typ = null,
        string      $class = self::class
    ): sql_par
    {
        $this->set_id($id);
        // for the group the number of phrases are not relevant for the queries
        $sc_par_lst = new sql_type_list([$this->table_type()]);
        $qp = $this->load_sql_multi($sc, sql_db::FLD_ID, $class, $sc_par_lst);
        $sc->add_where($this->id_field(), $id);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a phrase groups by name from the database
     * only selects groups where the default name has been overwritten by the user
     * TODO check that the user does not use a group name that matches the generated name of another group
     * TODO include the prime and big tables into the search
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $name the name of the phrase group
     * @return sql_par the SQL statement base on the parameters set in $this
     */
    function load_sql_by_name(sql_creator $sc, string $name): sql_par
    {
        $qp = $this->load_sql($sc, sql_db::FLD_NAME);
        foreach (group::TBL_LIST as $tbl_typ) {
            $qp_tbl = $this->load_sql_by_name_single($sc, $name, $tbl_typ);
            if ($sc->db_type() != sql_db::MYSQL) {
                $qp->merge($qp_tbl, true);
            } else {
                $qp->merge($qp_tbl);
            }
        }
        $par_types = array();
        foreach ($qp->par as $par) {
            if (is_numeric($par)) {
                $par_types[] = sql_par_type::INT;
            } else {
                $par_types[] = sql_par_type::TEXT;
            }
        }

        $qp->sql = $sc->prepare_sql($qp->sql, $qp->name, $par_types);

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a phrase groups by name from the database
     * from a single table so either from the table with an int, 512bit or text key
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $name the name of the phrase group
     * @param array $sc_par_arr the parameters for the sql statement creation
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_name_single(sql_creator $sc, string $name, array $sc_par_arr): sql_par
    {
        $sc_par_lst = new sql_type_list($sc_par_arr);
        $qp = $this->load_sql_multi($sc, sql_db::FLD_NAME, $this::class, $sc_par_lst);
        $sc->add_where(self::FLD_NAME, $name);
        $qp->sql = $sc->sql(0, true, false);
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a phrase groups from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param phrase_list $phr_lst list of phrases that should all be used to create the group id
     * @return sql_par the SQL statement base on the parameters set in $this
     */
    function load_sql_by_phrase_list(sql_creator $sc, phrase_list $phr_lst): sql_par
    {
        $grp_id = new group_id();
        return $this->load_sql_by_id($sc, $grp_id->get_id($phr_lst));
    }

    /**
     * create the SQL to load the default group always by the id
     * @param sql_creator $sc with the target db_type set
     * @param array $fld_lst list of fields either for the value or the result
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_standard_sql(sql_creator $sc, array $fld_lst = []): sql_par
    {
        $fld_lst = array_merge(
            $this::FLD_NAMES,
            array(user::FLD_ID)
        );
        return parent::load_standard_sql($sc, $fld_lst);
    }

    /**
     * create the SQL to load the single default value always by the id
     * @param sql_creator $sc with the target db_type set
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_standard_by_name_sql(sql_creator $sc, string $name): sql_par
    {
        $sc_par_lst = new sql_type_list();
        $sc_par_lst->add($this->table_type());
        $sc_par_lst->add(sql_type::NORM);
        $qp = new sql_par($this::class, $sc_par_lst);
        $qp->name .= sql_db::FLD_NAME;

        $fld_lst = array_merge(
            $this::FLD_NAMES,
            array(user::FLD_ID)
        );

        $sc->set_class($this::class, $sc_par_lst);
        $sc->set_name($qp->name);
        $sc->set_id_field($this->id_field());
        $sc->set_fields($fld_lst);
        $sc->set_usr($this->user()->id());
        $sc->add_where($this->name_field(), $name);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }


    /*
     * load (to be deprecated)
     */

    /**
     * create an SQL statement to retrieve a phrase groups from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $class the name of this class to overwrite the parent class
     * @return sql_par the SQL statement base on the parameters set in $this
     */
    function load_sql_obj_vars(sql_creator $sc, string $class = self::class): sql_par
    {
        $sc->set_class($class);
        $qp = new sql_par($class);
        $qp->name .= $this->load_sql_name_ext();
        $sc->set_name($qp->name);
        $sc->set_usr($this->user()->id());
        $sc->set_fields(self::FLD_NAMES);

        return $this->load_sql_select_qp($sc, $qp);
    }

    /**
     * load the object parameters for all users
     * @return bool true if the phrase group object has been loaded
     */
    function load_by_obj_vars(): bool
    {
        global $db_con;
        $result = false;

        $qp = $this->load_sql_obj_vars($db_con->sql_creator());

        if ($qp->sql == '') {
            log_err('Some ids for a ' . self::class . ' must be set to load a ' . self::class, self::class . '->load');
        } else {
            $db_row = $db_con->get1($qp);
            $result = $this->row_mapper($db_row);
            if ($result and $this->phrase_list()->empty()) {
                $this->load_lst();
            }
        }
        return $result;
    }

    /**
     * @return string the name of the SQL statement name extension based on the filled fields
     */
    private function load_sql_name_ext(): string
    {
        if ($this->id() != 0) {
            return sql_db::FLD_ID;
        } elseif (!$this->phrase_list()->is_empty()) {
            return 'phr_ids';
        } elseif ($this->name != '') {
            return sql_db::FLD_NAME;
        } else {
            log_err('Either the database ID (' . $this->id() . ') or the ' .
                self::class . ' link objects (' . $this->dsp_id() . ') and the user (' . $this->user()->id() . ') must be set to load a ' .
                self::class, self::class . '->load');
            return '';
        }
    }

    /**
     * add the select parameters to the query parameters
     *
     * @param sql_creator $sc the db connection object with the SQL name and others parameter already set
     * @param sql_par $qp the query parameters with the name already set
     * @return sql_par the query parameters with the select parameters added
     */
    private function load_sql_select_qp(sql_creator $sc, sql_par $qp): sql_par
    {
        if ($this->id() != 0) {
            $sc->add_where(self::FLD_ID, $this->id());
        } elseif (!$this->phrase_list()->is_empty()) {
            $this->set_id_from_phrase_list($this->phrase_list());
            $sc->add_where(self::FLD_ID, $this->id());
        } elseif ($this->name != '') {
            $sc->add_where(self::FLD_NAME, $this->name, sql_par_type::TEXT);
        }
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();
        return $qp;
    }

    /**
     * load the phrase names based on the group id
     */
    function load_phrase_names(): void
    {
        $grp_id = new group_id();
        $ids = $grp_id->get_array($this->id());
        $phr_ids = (new phr_ids($ids));
        $phr_lst = new phrase_list($this->user());
        $phr_lst->load_names_by_ids($phr_ids);
        $this->set_phrase_list($phr_lst);
    }

    /**
     * load the phrases with all parameters based on the group id
     */
    function load_phrases(): void
    {
        $grp_id = new group_id();
        $ids = $grp_id->get_array($this->id());
        $phr_ids = (new phr_ids($ids));
        $phr_lst = new phrase_list($this->user());
        $phr_lst->load_by_ids($phr_ids);
        $this->set_phrase_list($phr_lst);
    }


    /*
     * get functions - to load or create with one call
     */

    /**
     * set the id of this group based on the given phrase list
     * and save the given group name or description if needed
     *
     * @param phrase_list $phr_lst the list of phrase that should be used to set the group id
     * @param string $name the given name as a replacement for the generated name
     * @param string $description a user defined description of the group
     * @param bool $do_save set it to false for unit testing
     * @return user_message
     */
    function get_by_phrase_list(
        phrase_list $phr_lst,
        string      $name = '',
        string      $description = '',
        bool        $do_save = true): user_message
    {
        $usr_msg = new user_message();
        $db_entry_needed = false;
        $this->set_phrase_list($phr_lst);
        if ($name != '' and $name != $this->generic_name()) {
            $this->name = $name;
            $db_entry_needed = true;
        }
        if ($description != '') {
            $this->description = $description;
            $db_entry_needed = true;
        }
        // only save the group in the database if the name or description is given by the user
        if ($do_save and $db_entry_needed) {
            // check if there is already a db entry
            $db_rec = new group($this->user());
            $db_rec->load_by_id($this->id());
            if ($db_rec->name() != $this->name() or $db_rec->description != $this->description) {
                // TODO call insert or update sql statement
                //$result .= $this->save_id();
                log_warning('save of group description not yet implemented');
            }
        }
        return $usr_msg;
    }

    /**
     * get the word/triple group name (and create a new group if needed)
     * @param bool $do_save can be set to false for unit testing
     * based on a string with the word and triple ids
     */
    function get(bool $do_save = true): string
    {
        log_debug($this->dsp_id());
        $result = '';

        // get the id based on the given parameters
        $test_load = clone $this;
        if ($do_save) {
            $result .= $test_load->load_by_obj_vars();
            log_debug('loaded ' . $this->dsp_id());
        } else {
            // TODO use a unit test seq builder
            $test_load->set_id(1);
        }

        // use the loaded group or create the word group if it is missing
        if ($test_load->id() > 0) {
            $this->id = $test_load->id();
        } else {
            log_debug('save ' . $this->dsp_id());
            $this->load_by_obj_vars();
            $result .= $this->save_id();
        }

        // update the database for correct selection references
        if ($this->id() > 0) {
            $result .= $this->generic_name($do_save); // update the generic name if needed
        }

        log_debug('got ' . $this->dsp_id());
        return $result;
    }

    /**
     * @return int|null the group id generated from the previous set phrase list
     */
    function get_id(): ?int
    {
        if (!$this->is_id_set()) {
            // if the id has not yet been set, try to create it, but actually this should never happen
            log_warning('Unexpected creation of the group id triggered for ' . $this->dsp_id());
            $this->get();
        }
        return $this->id;
    }

    /**
     * create the sql statement
     */
    function get_by_wrd_lst_sql(bool $get_name = false): string
    {
        $wrd_lst = $this->phrase_list()->words();

        $sql_name = 'group_by_';
        if ($this->id() != 0) {
            $sql_name .= sql_db::FLD_ID;
        } elseif (!$wrd_lst->is_empty()) {
            $sql_name .= count($wrd_lst->lst()) . 'word_id';
        } else {
            log_err("Either the database ID (" . $this->id() . ") or a word list and the user (" . $this->user()->id() . ") must be set to load a phrase list.", "phrase_list->load");
        }

        $sql_from = '';
        $sql_from_prefix = '';
        $sql_where = '';
        if ($this->id() != 0) {
            $sql_from .= 'groups ';
            $sql_where .= 'group_id = ' . $this->id();
        } else {
            $pos = 1;
            $prev_pos = 1;
            $sql_from_prefix = 'l1.';
            foreach ($wrd_lst->lst() as $wrd) {
                if ($wrd != null) {
                    if ($wrd->id() <> 0) {
                        if ($sql_from == '') {
                            $sql_from .= 'group_word_links l' . $pos;
                        } else {
                            $sql_from .= ', group_word_links l' . $pos;
                        }
                        if ($sql_where == '') {
                            $sql_where .= 'l' . $pos . '.word_id = ' . $wrd->id();
                        } else {
                            $sql_where .= ' AND l' . $pos . '.word_id = l' . $prev_pos . '.word_id AND l' . $pos . '.word_id = ' . $wrd->id();
                        }
                    }
                }
                $prev_pos = $pos;
                $pos++;
            }
        }
        $sql = "SELECT " . $sql_from_prefix . "group_id 
                  FROM " . $sql_from . "
                 WHERE " . $sql_where . "
              GROUP BY " . $sql_from_prefix . "group_id;";
        log_debug('sql ' . $sql);

        if ($get_name) {
            $result = $sql_name;
        } else {
            $result = $sql;
        }
        return $result;
    }

    /**
     * check if the user has requested a group with a preserved name
     * and yes if return a message to the user
     *
     * @return user_message
     */
    protected function check_preserved(): user_message
    {
        global $usr;
        global $mtr;

        // init
        $usr_msg = new user_message();
        $msg_res = $mtr->txt(msg_id::IS_RESERVED);
        $msg_for = $mtr->txt(msg_id::RESERVED_NAME);
        $lib = new library();
        $class_name = $lib->class_to_name($this::class);

        if (in_array($this->name, $this->reserved_names())) {
            // the admin user needs to add the read test group name during initial load
            // so for admin do not create a message
            if (!$usr->is_admin() and !$usr->is_system()) {
                $usr_msg->add_id_with_vars(msg_id::GROUP_IS_RESERVED, [
                    msg_id::VAR_NAME => $this->name(),
                    msg_id::VAR_JSON_TEXT => $msg_res . ' ' . $class_name . ' ' . $msg_for
                ]);
            }
        }
        return $usr_msg;
    }

    /**
     * @return array with the reserved triple names
     */
    protected function reserved_names(): array
    {
        return groups::RESERVED_GROUP_NAMES;
    }

    /*
    // get the best matching group for a word list
    // at the moment "best matching" is defined as the highest number of results
    private function get_by_wrd_lst(): group
    {

        global $db_con;
        $result = null;

        $wrd_lst = $this->phrase_list()->wrd_lst();

        if (isset($wrd_lst)) {
            if ($wrd_lst->lst > 0) {

                $pos = 1;
                $prev_pos = 1;
                $sql_from = '';
                $sql_where = '';
                foreach ($wrd_lst->ids as $wrd_id) {
                    if ($sql_from == '') {
                        $sql_from .= 'group_word_links l' . $pos;
                    } else {
                        $sql_from .= ', group_word_links l' . $pos;
                    }
                    if ($sql_where == '') {
                        $sql_where .= 'l' . $pos . '.word_id = ' . $wrd_id;
                    } else {
                        $sql_where .= ' AND l' . $pos . '.word_id = l' . $prev_pos . '.word_id AND l' . $pos . '.word_id = ' . $wrd_id;
                    }
                    $prev_pos = $pos;
                    $pos++;
                }
                $sql = "SELECT" . " l1.group_id
                  FROM " . $sql_from . "
                 WHERE " . $sql_where . "
              GROUP BY l1.group_id;";
                log_debug('group->get_by_wrd_lst sql ' . $sql);
                //$db_con = New mysql;
                $db_con->usr_id = $this->user()->id();
                $db_grp = $db_con->get1_old($sql);
                if ($db_grp != null) {
                    $this->id = $db_grp[group::FLD_ID];
                    if ($this->id() > 0) {
                        log_debug('group->get_by_wrd_lst got id ' . $this->id());
                        $result = $this->load();
                        log_debug('group->get_by_wrd_lst ' . $result . ' found <' . $this->id() . '> for ' . $wrd_lst->name() . ' and user ' . $this->user()->name);
                    } else {
                        log_warning('No group found for words ' . $wrd_lst->name() . '.', "group->get_by_wrd_lst");
                    }
                }
            } else {
                log_warning("Word list is empty.", "group->get_by_wrd_lst");
            }
        } else {
            log_warning("Word list is missing.", "group->get_by_wrd_lst");
        }

        return $this;
    }
    */


    /*
     * modify
     */

    /**
     * @param word $wrd the word that should be added to this phrase group
     * @return bool true if the word has been added and false if the word already is part of the group
     */
    function add_word(word $wrd): bool
    {
        return $this->phr_lst->add($wrd->phrase());
    }

    /**
     * add a list of phrases based on the name WITHOUT loading the database id
     * used mainly for testing
     * @param array $prh_names
     * @return bool
     */
    function add_phrase_names(array $prh_names = []): bool
    {
        $result = false;
        if (count($prh_names) > 0) {
            $wrd_id = 1;
            foreach ($prh_names as $prh_name) {
                if (!in_array($prh_name, $this->phrase_list()->names())) {
                    // if only the name is know, add a simple word
                    $wrd = new word($this->user());
                    $wrd->set($wrd_id, $prh_name);
                    $this->add_word($wrd);
                    $result = true;
                }
                $wrd_id++;
            }
            $this->set_name();
        }
        return $result;
    }


    /*
     * information
     */

    /**
     * @return bool true if this group has less than 5 phrase ids
     *              and all have a low id number which means that they are often used and classified as prime phrases
     */
    function is_prime(): bool
    {
        $grp_id = new group_id();
        $id = $grp_id->get_id($this->phr_lst);
        if (count($this->phr_lst->lst()) == 0 and is_string($this->id())) {
            if ($this->id() != '') {
                $id = $this->id();
                log_warning('fix wrong using of value id');
            }
        }
        return $grp_id->is_prime($id);
    }

    /**
     * @return bool always false because there is no need for main groups
     */
    function is_main(): bool
    {
        return false;
    }

    function is_big(): bool
    {
        $grp_id = new group_id();
        $id = $grp_id->get_id($this->phr_lst);
        return $grp_id->is_big($id);
    }


    /*
     * save
     */

    /**
     * check if a group with the unique key already exists
     * returns null if no similar group is found
     * or returns the group with the same unique key that is not the actual object
     *
     * @return group a filled object that has the same name
     *                 or a sandbox object with id() = 0 if nothing similar has been found
     */
    function get_similar(): group
    {
        $result = new group($this->user());

        // check potential duplicate by name
        $db_chk = clone $this;
        $db_chk->reset();
        $db_chk->set_user($this->user());
        // check with the standard namespace
        if ($db_chk->load_standard_by_name($this->name())) {
            if ($db_chk->id() > 0) {
                log_debug($this->dsp_id() . ' has the same name is the already existing "' . $db_chk->dsp_id() . '" of the standard namespace');
                $result = $db_chk;
            }
        }
        // check with the user namespace
        $db_chk->set_user($this->user());
        if ($this->name() != '') {
            if ($db_chk->load_by_name($this->name())) {
                if ($db_chk->id() > 0) {
                    log_debug($this->dsp_id() . ' has the same name is the already existing "' . $db_chk->dsp_id() . '" of the user namespace');
                    $result = $db_chk;
                }
            }
        } else {
            log_err('The name must be set to check if a similar object exists');
        }

        return $result;
    }

    /**
     * add a new group to the database
     *
     * @param bool $use_func if true a predefined function is used that also creates the log entries
     * @return user_message with status ok
     *                      or if something went wrong
     *                      the message that should be shown to the user
     *                      including suggested solutions
     */
    function add(bool $use_func = false): user_message
    {
        log_debug($this->dsp_id());

        global $db_con;
        $usr_msg = new user_message();

        if ($use_func) {
            $sc = $db_con->sql_creator();
            $qp = $this->sql_insert($sc, new sql_type_list([sql_type::LOG]));
            $ins_msg = $db_con->insert($qp, 'add and log ' . $this->dsp_id());
            if ($ins_msg->is_ok()) {
                $this->id = $ins_msg->get_row_id();
            }
            $usr_msg->add($ins_msg);
        } else {

            // log the insert attempt first
            $log = $this->log_add();
            if ($log->id() > 0) {

                // insert the new object and save the object key
                // TODO check that always before a db action is called the db type is set correctly
                $sc = $db_con->sql_creator();
                $qp = $this->sql_insert($sc);
                $ins_msg = $db_con->insert($qp, 'add ' . $this->dsp_id());
                if ($ins_msg->is_ok()) {
                    $this->id = $ins_msg->get_row_id();
                    $this->set_saved();
                }

                // save the object fields if saving the key was successful
                if ($this->is_saved()) {
                    log_debug($this::class . ' ' . $this->dsp_id() . ' has been added');
                    // update the id in the log
                    if (!$log->add_ref($this->id())) {
                        $usr_msg->add_id(msg_id::FAILED_UPDATE_REF);
                    }

                } else {
                    $usr_msg->add_id_with_vars(msg_id::FAILED_ADD_GROUP, [msg_id::VAR_ID => $this->dsp_id()]);
                }
            }
        }

        return $usr_msg;
    }


    /*
     * display
     */

    /**
     * return the first value related to the word lst
     * or an array with the value and the user_id if the result is user specific
     */
    function value(): value
    {
        $val = new value($this->user());
        $val->load_by_grp($this);

        log_debug($val->grp()->dsp_id() . ' for "' . $this->user()->name . '" is ' . $val->number());
        return $val;
    }

    /**
     * TODO add a unit test
     * @return phrase of the most relevant time
     */
    function time(): phrase
    {
        $phr = new phrase($this->user());
        $phr_lst = $this->phrase_list()->time_word_list();
        if (!$phr_lst->is_empty()) {
            // TODO use a new "most relevant" function
            $phr = $phr_lst->lst()[0];
        } else {
            $phr = $phr_lst->assume_time();
        }
        return $phr;
    }

    /**
     * TODO add a db read test
     * @param $time_wrd_id
     * @return result the best matching result of the group
     */
    function result($time_wrd_id): result
    {
        log_debug($this->id() . ",time" . $time_wrd_id . ",u" . $this->user()->name);

        global $db_con;

        $res = new result($this->user());
        $result = $res->load_by_grp($this);

        // if no user specific result is found, get the standard result
        if ($result === false) {
            $result = $res->load_std_by_grp($this);

            // get any time value: to be adjusted to: use the latest
            if ($result === false) {
                $grp_ex_time = $this->get_ex_time();
                $result = $res->load_std_by_grp($this);
                if ($result === false) {
                    log_info('no result found for ' . $this->dsp_id());
                } else {
                    log_debug($res->dsp_id());
                }
            } else {
                log_debug($res->dsp_id());
            }
        } else {
            log_debug($res->dsp_id() . " for " . $this->user()->dsp_id());
        }

        return $res;
    }

    /**
     * create the generic group name
     * TODO check if saving the generic name in the database is needed for faster search (most likely not)
     *
     * @returns string the generic name if it has been saved to the database
     */
    private function generic_name(bool $do_save = true): string
    {
        log_debug();

        $result = '';

        // if not yet done, load, the words and triple list
        if ($do_save) {
            $this->load_lst();
        }

        // TODO take the order into account
        $group_name = $this->phrase_list()->dsp_name();

        // update the name if possible and needed
        /*
        if ($this->description <> $group_name and $do_save) {
            if ($this->id() > 0) {
                // update the generic name in the database
                $db_con->usr_id = $this->user()->id();
                $db_con->set_class(group::class);
                // TODO activate Prio 2
                /*
                if ($db_con->update_old($this->id(), self::FLD_DESCRIPTION, $group_name)) {
                    $result = $group_name;
                }
                log_debug('updated to ' . $group_name);
            }
            $this->description = $group_name;
        }
        log_debug('group name ' . $group_name);
        */

        return $result;
    }


    function get_ex_time(): group
    {
        $phr_lst = $this->phrase_list();
        $phr_lst->ex_time();
        return $phr_lst->get_grp_id();
    }

    /*
     * create the HTML code to select a phrase group be selecting a combination of words and triples
    private function selector()
    {
        $result = '';
        log_debug('group->selector for ' . $this->id() . ' and user "' . $this->user()->name . '"');

        new function: load_main_type to load all word and phrase types with one query

        Allow to remember the view order of words and phrases

        the form should create an url with the ids in the view order
        -> this is converted by this class to word ids, triple ids for selecting the group and saving the view order and the time for the value selection

        Create a new group if needed without asking the user
    Create a new value if needed, but ask the user: abb sales of 46000, is still used by other users. Do you want to suggest the users to switch to abb revenues 4600? If yes, a request is created. If no, do you want to additional save abb revenues 4600 (and keep abb sales of 46000)? If no, nothing is saved and the form is shown again with a highlighted cancel or back button.

      update the link tables for fast selection


        return $result;
    }
    */


    /**
     * TODO review
     * set the phrase group object vars based on an api json array
     * similar to import_obj but using the database id instead of the names and code id
     * @param array $api_json the api array
     * @return user_message false if a value could not be set
     */
    function save_from_api_msg(array $api_json, bool $do_save = true): user_message
    {
        log_debug();
        $usr_msg = new user_message();

        foreach ($api_json as $key => $value) {

            if ($key == json_fields::NAME) {
                $this->name = $value;
            }
        }

        if ($usr_msg->is_ok() and $do_save) {
            $usr_msg->add_message_text($this->save_id());
        }

        return $usr_msg;
    }


    /**
     * create a new phrase group
     */
    private function save_id(): ?int
    {
        log_debug($this->dsp_id());

        if ($this->id() <= 0) {
            $this->generic_name();

            // write new group
            if (!$this->phrase_list()->is_empty()) {
                $grp_id = new group_id();
                $this->set_id($grp_id->get_id($this->phrase_list()));
            } else {
                log_err('The phrase list must be set to create a group for ' . $this->dsp_id() . '.', 'phrase_group->save_id');
            }
        }

        return $this->id;
    }

    /**
     * delete a phrase group that is supposed not to be used anymore
     * the removal if the linked values must be done before calling this function
     * the word and triple links related to this phrase group are also removed
     * TODO maybe move this to del_exe
     *
     * @param bool|null $use_func if true a predefined function is used that also creates the log entries
     * @return user_message
     */
    function del(?bool $use_func = null): user_message
    {
        global $db_con;
        $usr_msg = new user_message();
        $sc = $db_con->sql_creator();

        if ($use_func) {
            $qp = $this->sql_delete($sc, new sql_type_list([sql_type::LOG]));
            $del_msg = $db_con->delete($qp, 'del and log ' . $this->dsp_id());
            $usr_msg->add($del_msg);
        } else {

            // log the delete attempt first
            if ($this->is_prime()) {
                $log = $this->log_del_prime();
            } elseif ($this->is_big()) {
                $log = $this->log_del_big();
            } else {
                $log = $this->log_del();
            }
            if ($log->id() > 0) {
                $db_con->set_class(group::class);
                $qp = $this->sql_delete($sc);
                $msg = $db_con->delete($qp, 'del ' . $this->dsp_id());
                $usr_msg->add($msg);
            }
        }

        return $usr_msg;
    }


    /*
     * sql write
     */

    /**
     * create the sql statement to add a new group name to the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par the SQL insert statement, the name of the SQL statement and the parameter list
     */
    function sql_insert(sql_creator $sc, sql_type_list $sc_par_lst = new sql_type_list()): sql_par
    {
        // clone the sql parameter list to avoid changing the given list
        $sc_par_lst_used = clone $sc_par_lst;
        // set the sql query type
        $sc_par_lst_used->add(sql_type::INSERT);
        $qp = $this->sql_common($sc, $sc_par_lst_used);
        // overwrite the standard auto increase id field name
        $sc->set_id_field($this->id_field());
        //$qp->name .= sql::file_sep . sql::file_insert;
        $sc->set_name($qp->name);
        $fvt_lst = new sql_par_field_list();
        $fvt_lst->set([
            [group::FLD_ID, $this->id(), $sc->get_sql_par_type($this->id())],
            [user::FLD_ID, $this->user()->id(), sql_par_type::INT],
            [self::FLD_NAME, $this->name, sql_par_type::TEXT],
            [self::FLD_DESCRIPTION, $this->description, sql_par_type::TEXT]
        ]);
        $qp->sql = $sc->create_sql_insert($fvt_lst);
        $qp->par = $fvt_lst->values();

        return $qp;
    }

    /**
     * create the sql statement to update a group name in the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param group $db_grp
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par the SQL insert statement, the name of the SQL statement and the parameter list
     */
    function sql_update(sql_creator $sc, group $db_grp, sql_type_list $sc_par_lst): sql_par
    {
        // clone the sql parameter list to avoid changing the given list
        $sc_par_lst_used = clone $sc_par_lst;
        // set the sql query type
        $sc_par_lst_used->add(sql_type::UPDATE);
        $lib = new library();
        $qp = $this->sql_common($sc, $sc_par_lst_used);
        $fld_val_typ_lst = $this->db_changed($db_grp);
        if (count($fld_val_typ_lst) == 0) {
            $fld_val_typ_lst = [
                [self::FLD_NAME, $this->name, self::FLD_NAME_SQL_TYP],
                [self::FLD_DESCRIPTION, $this->description, self::FLD_DESCRIPTION_SQL_TYP]
            ];
        }
        $fields = $sc->get_fields($fld_val_typ_lst);
        $fld_name = implode(sql::NAME_SEP, $lib->sql_name_shorten($fields));
        $qp->name .= sql::NAME_SEP . $fld_name;
        $sc->set_name($qp->name);
        $fvt_lst = new sql_par_field_list();
        $fvt_lst->set($fld_val_typ_lst);
        $qp->sql = $sc->create_sql_update($this->id_field(), $this->id(), $fvt_lst);
        $values = $sc->get_values($fld_val_typ_lst);
        $values[] = $this->id();
        $qp->par = $values;
        return $qp;
    }

    /**
     * the common part of the sql statement creation for insert and update statements
     * @param sql_creator $sc with the target db_type set
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par the common part for insert and update sql statements
     */
    protected function sql_common(sql_creator $sc, sql_type_list $sc_par_lst): sql_par
    {
        $sc_par_lst->add($this->table_type());
        $qp = new sql_par($this::class, $sc_par_lst);
        $sc->set_class($this::class, $sc_par_lst);
        $sc->set_name($qp->name);
        return $qp;
    }


    /*
     * sql write fields
     */

    /**
     * get a list of all database fields that might be changed excluding the internal database id
     *
     * @return array list of all database field names that have been updated
     */
    function db_fields_all(sql_type_list $sc_par_lst = new sql_type_list()): array
    {
        return array_merge([self::FLD_NAME, self::FLD_DESCRIPTION]);
    }

    /**
     * get a list of database fields that have been updated
     *
     * @param group $grp the compare value to detect the changed fields
     * @return array list of the database field names that have been updated
     */
    function db_changed(group $grp): array
    {
        $lst = [];
        if ($grp->name() <> $this->name()) {
            $lst[] = [
                self::FLD_NAME,
                $this->name(),
                self::FLD_NAME_SQL_TYP
            ];
        }
        if ($grp->description <> $this->description) {
            $lst[] = [
                self::FLD_DESCRIPTION,
                $this->description,
                self::FLD_DESCRIPTION_SQL_TYP
            ];
        }
        return $lst;
    }


    /*
     * testing only
     */

    /**
     * internal function for testing the link for fast search
     * load_link_ids_for_testing is not needed any more because the group id includes the
     */
    function load_link_ids_for_testing(): array
    {

        /*
        global $db_con;
        $result = array();

        $db_con->set_class(sql_db::VT_PHRASE_GROUP_LINK);
        $db_con->usr_id = $this->user()->id();
        $qp = new sql_par(self::class);
        $qp->name .= 'test_link_ids';
        $db_con->set_name($qp->name);
        $db_con->set_fields(array(phrase::FLD_ID));
        $db_con->add_par(sql_par_type::INT, $this->id());
        $qp->sql = $db_con->select_by_field(group::FLD_ID);
        $qp->par = $db_con->get_par();
        $lnk_id_lst = $db_con->get($qp);
        foreach ($lnk_id_lst as $db_row) {
            $result[] = $db_row[phrase::FLD_ID];
        }
        */

        $grp_id_obj = new group_id();
        $result = $grp_id_obj->get_array($this->id());

        asort($result);
        return $result;
    }


    /*
     * debug
     */

    /**
     * @return string with the best possible id for this element mainly used for debugging
     */
    function dsp_id(): string
    {
        $result = '';

        if ($this->name() <> '') {
            $result .= '"' . $this->name() . '" (group_id ' . $this->id() . ')';
        } else {
            $result .= 'group_id ' . $this->id();
        }
        if ($this->name <> '') {
            $result .= ' as "' . $this->name . '"';
        }
        if ($result == '') {
            if (!$this->phrase_list()->is_empty()) {
                $result .= ' for phrases ' . $this->phrase_list()->dsp_id();
            }
        }
        global $debug;
        if ($debug > DEBUG_SHOW_USER or $debug == 0) {
            if ($this->user() != null) {
                $result .= ' for user ' . $this->user()->id() . ' (' . $this->user()->name . ')';
            }
        }

        return $result;
    }

    /**
     * @return string with the best possible id for this element mainly used for debugging
     */
    function dsp_id_medium(): string
    {
        return $this->name() . '(' . $this->dsp_id_short() . ')';
    }

    /**
     * @return string with the best possible id for this element mainly used for debugging
     */
    function dsp_id_short(): string
    {
        $grp_id_obj = new group_id();
        return implode(',', $grp_id_obj->get_array($this->id(), true));
    }

    /**
     * @return string with the group name
     */
    function name(): string
    {
        if ($this->name <> '') {
            // use the user defined description
            $result = $this->name;
        } else {
            // or use the standard generic description
            $result = $this->name_generated();
        }

        return $result;
    }

    /**
     * @return string with the generated name based on the phrase list
     */
    function name_generated(): string
    {
        $name_lst = $this->phrase_list()->names();
        return implode(",", $name_lst);
    }

    /**
     * @return array a list of the word and triple names
     */
    function names(): array
    {
        log_debug();

        // if not yet done, load, the words and triple list
        $this->load_lst();

        return $this->phrase_list()->names();
    }

}