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

include_once DB_PATH . 'sql_par_type.php';
include_once MODEL_HELPER_PATH . 'db_object.php';
include_once MODEL_PHRASE_PATH . 'phr_ids.php';
include_once MODEL_PHRASE_PATH . 'phrase_list.php';
include_once MODEL_GROUP_PATH . 'group_link.php';
include_once MODEL_GROUP_PATH . 'group_id.php';
include_once API_PHRASE_PATH . 'group.php';

use api\phrase\group as group_api;
use cfg\db\sql;
use cfg\db\sql_db;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
use cfg\db\sql_par;
use cfg\db\sql_par_type;
use cfg\db\sql_type;
use cfg\export\sandbox_exp;
use cfg\phr_ids;
use cfg\phrase;
use cfg\phrase_list;
use cfg\result\result;
use cfg\sandbox_multi;
use cfg\sandbox_value;
use cfg\user;
use cfg\user_message;
use cfg\value\value;
use cfg\word;
use shared\library;

class group extends sandbox_multi
{

    /*f
     * database link
     */

    // object specific database and JSON object field names
    const FLD_ID = 'group_id';
    const FLD_NAME = 'group_name';
    const FLD_DESCRIPTION = 'description';

    // comments used for the database creation
    const TBL_COMMENT = 'to add a user given name using a 512-bit group id index for up to 16 32-bit phrase ids including the order';
    const TBL_COMMENT_PRIME = 'to add a user given name using a 64-bit group id index for up to four 16-bit phrase ids including the order';
    const TBL_COMMENT_INDEX = 'to add a user given name using a 64-bit group id index for one 32-bit and two 16-bit phrase ids including the order';
    const TBL_COMMENT_BIG = 'to add a user given name using a group id index with a variable length for more than 16 32-bit phrase ids including the order';
    const TBL_COMMENT_INDEX_BIG = 'to add a user given name using a 64-bit group id index for one 48-bit and one 16-bit phrase id including the order';

    // list of fields with parameters used for the database creation
    // the fields that can be changed by the user
    const FLD_KEY_PRIME = array(
        [group::FLD_ID, sql_field_type::KEY_INT_NO_AUTO, sql_field_default::NOT_NULL, '', '', 'the 64-bit prime index to find the -=class=-'],
    );
    const FLD_KEY_PRIME_USER = array(
        [group::FLD_ID, sql_field_type::KEY_PART_INT, sql_field_default::NOT_NULL, '', '', 'the 64-bit prime index to find the user -=class=-'],
    );
    const FLD_LST_USER_CAN_CHANGE = array(
        [self::FLD_NAME, sql_field_type::TEXT, sql_field_default::NULL, '', '', 'the user specific group name which can contain the phrase names in a different order to display the group (does not need to be unique)'],
        [self::FLD_DESCRIPTION, sql_field_type::TEXT, sql_field_default::NULL, '', '', 'the user specific description for mouse over helps'],
    );

    // all database field names excluding the id
    const FLD_NAMES = array(
        self::FLD_DESCRIPTION
    );
    // list of fixed tables where a group name overwrite might be stored
    // TODO check if this can be used somewhere else means if there are unwanted repeatings
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
    private user $usr;            // the person for whom the object is loaded, so to say the viewer
    public ?string $name;         // maybe later the user should have the possibility to overwrite the generic name, but this is not user at the moment
    public ?string $description;  // the automatically created generic name for the word group, used for a quick display of values


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

        $this->set_user($usr);

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
        }
        return $result;
    }


    /*
     * set and get
     */

    /**
     * set the phrase list of this group
     * and return the unique database id of this group
     * @param phrase_list $phr_lst sorted list of phrases for this group
     * @return int|string $id either a 62-bit int, a 512-bit id with 16 phrase ids or a text with more than 16 +/- seperated 6 char alpha_num coded phrase ids
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
     * @param int|string $id either a 62-bit int, a 512-bit id with 16 phrase ids or a text with more than 16 +/- seperated 6 char alpha_num coded phrase ids
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
            if ($this->phrase_list()->count() > 0) {
                $this->name = implode(',', $this->phr_lst->names());
            } else {
                log_warning('name of phrase group ' . $this->dsp_id() . ' missing');
            }
        }
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
     * @return int|string either a 62-bit int, a 512-bit id with 16 phrase ids or a text with more than 16 +/- seperated 6 char alpha_num coded phrase ids
     * the internal null value is used to detect if database saving has been tried
     */
    function id(): int|string
    {
        return $this->id;
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
    function id_names(bool $all = false): array
    {
        $name_lst = array();
        $grp_id = new group_id();
        if ($all) {
            for ($pos = 1; $pos <= group_id::PRIME_PHRASES_STD; $pos++) {
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
     * set the user of the user sandbox object
     *
     * @param user $usr the person who wants to access the object e.g. the word
     * @return void
     */
    function set_user(user $usr): void
    {
        $this->usr = $usr;
    }

    /**
     * @return user the person who wants to see a word, verb, triple, formula, view or result
     */
    function user(): user
    {
        return $this->usr;
    }

    /**
     * set the unique database id of this group
     * @param phrase_list $phr_lst sorted list of phrases for this group
     * @return int|string $id either a 62-bit int, a 512-bit id with 16 phrase ids or a text with more than 16 +/- seperated 6 char alpha_num coded phrase ids
     */
    private function set_id_from_phrase_list(phrase_list $phr_lst): int|string
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


    /*
     * cast
     */

    /**
     * @return group_api the phrase group frontend API object
     */
    function api_obj(): group_api
    {
        $api_obj = new group_api();
        $api_obj->reset_lst();
        foreach ($this->phrase_list()->lst() as $phr) {
            $api_obj->add($phr->api_obj());
        }
        $api_obj->set_id($this->id());
        $api_obj->set_name($this->name());
        return $api_obj;
    }

    /**
     * @returns string the api json message for the object as a string
     */
    function api_json(): string
    {
        return $this->api_obj()->get_json();
    }


    /*
     * sql create
     */

    /**
     * the sql statement to create the group tables
     *
     * @param sql $sc with the target db_type set
     * @return string the sql statement to create the table
     */
    function sql_table(sql $sc): string
    {
        return $this->sql_creator($sc)[0];
    }

    /**
     * the sql statements to create all indices for the group tables used to store the group name changes of a user
     *
     * @param sql $sc with the target db_type set
     * @return string the sql statement to create the indices
     */
    function sql_index(sql $sc): string
    {
        return $this->sql_creator($sc)[1];
    }

    /**
     * the sql statements to create all foreign keys for the group tables used to store the group name changes of a user
     *
     * @param sql $sc with the target db_type set
     * @return string the sql statement to create the foreign keys
     */
    function sql_foreign_key(sql $sc): string
    {
        return $this->sql_creator($sc)[2];
    }

    /**
     * the sql statements to truncate the group tables
     *
     * @param sql $sc with the target db_type set
     * @return string the sql statement to create the table
     */
    function sql_truncate(sql $sc): string
    {
        return $this->sql_creator($sc)[3];
    }

    /**
     * the sql statements to create
     * 0 => the group tables
     * 1 => all indices for the group tables used to store the group name changes of a user
     * 1 => all foreign keys for the group tables used to store the group name changes of a user
     *
     * @param sql $sc with the target db_type set
     * @return array the sql statement to create the table
     */
    private function sql_creator(sql $sc): array
    {
        $sql = $sc->sql_separator();
        $sql_index = $sc->sql_separator();
        $sql_foreign = $sc->sql_separator();
        $sql_truncate = '';
        $sql_lst = [$sql, $sql_index, $sql_foreign, $sql_truncate];
        $sql_lst = $this->sql_one_tbl($sc, [sql_type::MOST], sandbox_value::FLD_KEY, $this::TBL_COMMENT, $sql_lst);
        $sql_lst = $this->sql_one_tbl($sc, [sql_type::MOST, sql_type::USER], sandbox_value::FLD_KEY_USER, $this::TBL_COMMENT, $sql_lst);
        $sql_lst = $this->sql_one_tbl($sc, [sql_type::PRIME], group::FLD_KEY_PRIME, $this::TBL_COMMENT_PRIME, $sql_lst);
        $sql_lst = $this->sql_one_tbl($sc, [sql_type::PRIME, sql_type::USER], group::FLD_KEY_PRIME_USER, $this::TBL_COMMENT_PRIME, $sql_lst);
        $sql_lst = $this->sql_one_tbl($sc, [sql_type::BIG], sandbox_value::FLD_KEY_BIG, $this::TBL_COMMENT_BIG, $sql_lst);
        return $this->sql_one_tbl($sc, [sql_type::BIG, sql_type::USER], sandbox_value::FLD_KEY_BIG_USER, $this::TBL_COMMENT_BIG, $sql_lst);
    }

    /**
     * add the sql statements for one table to the given array of sql statements
     * @param sql $sc the sql creator object with the target db_type set
     * @param array $sc_par_lst of parameters for the sql creation
     * @param array $key_fld with the parameter for the table primary key field
     * @param string $tbl_comment the comment for the table in the sql statement
     * @param array $sql_lst the list with the sql statements created until now
     * @return array the list of sql statements including the statements created by this function call
     */
    private function sql_one_tbl(
        sql    $sc,
        array  $sc_par_lst,
        array  $key_fld,
        string $tbl_comment,
        array  $sql_lst
    ): array
    {
        $sc->set_class($this::class, $sc_par_lst);
        $fields = array_merge($key_fld, sandbox_value::FLD_ALL_OWNER, $this::FLD_LST_USER_CAN_CHANGE);
        $usr_tbl = $sc->is_usr_tbl($sc_par_lst);
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
     * create an SQL statement to retrieve a user sandbox object by id from the database
     *
     * @param sql $sc with the target db_type set
     * @param int|string $id the id of the phrase group, which can also be a string representing a 512-bit key
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_id(sql $sc, int|string $id, string $class = self::class): sql_par
    {
        $this->set_id($id);
        // for the group the number of phrases are not relevant for the queries
        $ext = $this->table_extension(false);
        $sc_par_lst = [];
        $sc_par_lst[] = $this->table_type();
        $qp = $this->load_sql_multi($sc, sql_db::FLD_ID, $class, $sc_par_lst, $ext);
        $sc->add_where($this->id_field(), $id);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a phrase groups from the database
     *
     * @param sql $sc with the target db_type set
     * @param phrase_list $phr_lst list of phrases that should all be used to create the group id
     * @return sql_par the SQL statement base on the parameters set in $this
     */
    function load_sql_by_phrase_list(sql $sc, phrase_list $phr_lst): sql_par
    {
        $grp_id = new group_id();
        return $this->load_sql_by_id($sc, $grp_id->get_id($phr_lst));
    }

    /**
     * create an SQL statement to retrieve a phrase groups by name from the database
     * only selects groups where the default name has been overwritten by the user
     * TODO check that the user does not use a group name that matches the generated name of another group
     * TODO include the prime and big tables into the search
     *
     * @param sql $sc with the target db_type set
     * @param string $name the name of the phrase group
     * @return sql_par the SQL statement base on the parameters set in $this
     */
    function load_sql_by_name(sql $sc, string $name): sql_par
    {
        $qp = $this->load_sql($sc, sql_db::FLD_NAME);
        $sc->add_where(self::FLD_NAME, $name);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }


    /*
     * load functions - the set functions are used to define the loading selection criteria
     */

    /**
     * create an SQL statement to retrieve a phrase groups from the database
     *
     * @param sql $sc with the target db_type set
     * @param string $class the name of this class to overwrite the parent class
     * @return sql_par the SQL statement base on the parameters set in $this
     */
    function load_sql_obj_vars(sql $sc, string $class = self::class): sql_par
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
     * TODO move (and other functions) to db_object and rename the existing db_object to db_id_object
     * just set the class name for the user sandbox function
     * load a word object by database id
     * @param int|string $id the id of the group
     * @param string $class the group class name
     * @return int|string the id of the object found and zero if nothing is found
     */
    function load_by_id(int|string $id, string $class = self::class): int|string
    {
        global $db_con;

        log_debug($id);
        if ($class == '') {
            $class = $this::class;
        }
        $qp = $this->load_sql_by_id($db_con->sql_creator(), $id, $class);
        return $this->load($qp);
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
     * load the word and triple objects based on the ids load from the database if needed
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
     * @return string the name of the SQL statement name extension based on the filled fields
     */
    private function load_sql_name_ext(): string
    {
        if ($this->id != 0) {
            return sql_db::FLD_ID;
        } elseif (!$this->phrase_list()->is_empty()) {
            return 'phr_ids';
        } elseif ($this->name != '') {
            return sql_db::FLD_NAME;
        } else {
            log_err('Either the database ID (' . $this->id . ') or the ' .
                self::class . ' link objects (' . $this->dsp_id() . ') and the user (' . $this->user()->id() . ') must be set to load a ' .
                self::class, self::class . '->load');
            return '';
        }
    }

    /**
     * add the select parameters to the query parameters
     *
     * @param sql $sc the db connection object with the SQL name and others parameter already set
     * @param sql_par $qp the query parameters with the name already set
     * @return sql_par the query parameters with the select parameters added
     */
    private function load_sql_select_qp(sql $sc, sql_par $qp): sql_par
    {
        if ($this->id != 0) {
            $sc->add_where(self::FLD_ID, $this->id);
        } elseif (!$this->phrase_list()->is_empty()) {
            $this->set_id_from_phrase_list($this->phrase_list());
            $sc->add_where(self::FLD_ID, $this->id);
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
    function load_phrase_list(): void
    {
        $grp_id = new group_id();
        $ids = $grp_id->get_array($this->id());
        $phr_ids = (new phr_ids($ids));
        $phr_lst = new phrase_list($this->user());
        $phr_lst->load_names_by_ids($phr_ids);
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
        $result = new user_message();
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
            }
        }
        return $result;
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
        if ($test_load->id > 0) {
            $this->id = $test_load->id;
        } else {
            log_debug('save ' . $this->dsp_id());
            $this->load_by_obj_vars();
            $result .= $this->save_id();
        }

        // update the database for correct selection references
        if ($this->id > 0) {
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
        $wrd_lst = $this->phrase_list()->wrd_lst();

        $sql_name = 'group_by_';
        if ($this->id != 0) {
            $sql_name .= sql_db::FLD_ID;
        } elseif (!$wrd_lst->is_empty()) {
            $sql_name .= count($wrd_lst->lst()) . 'word_id';
        } else {
            log_err("Either the database ID (" . $this->id . ") or a word list and the user (" . $this->user()->id() . ") must be set to load a phrase list.", "phrase_list->load");
        }

        $sql_from = '';
        $sql_from_prefix = '';
        $sql_where = '';
        if ($this->id != 0) {
            $sql_from .= 'groups ';
            $sql_where .= 'group_id = ' . $this->id;
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
                    if ($this->id > 0) {
                        log_debug('group->get_by_wrd_lst got id ' . $this->id);
                        $result = $this->load();
                        log_debug('group->get_by_wrd_lst ' . $result . ' found <' . $this->id . '> for ' . $wrd_lst->name() . ' and user ' . $this->user()->name);
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
     * modification functions
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
        if (count($this->phr_lst->lst()) == 0 and is_string($this->id)) {
            if ($this->id() != '') {
                $id = $this->id();
                log_warning('fix wrong using of value id');
            }
        }
        return $grp_id->is_prime($id);
    }

    function is_big(): bool
    {
        $grp_id = new group_id();
        $id = $grp_id->get_id($this->phr_lst);
        return $grp_id->is_big($id);
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

        log_debug($val->grp->dsp_id() . ' for "' . $this->user()->name . '" is ' . $val->number());
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
        log_debug($this->id . ",time" . $time_wrd_id . ",u" . $this->user()->name);

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

        global $db_con;
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
            if ($this->id > 0) {
                // update the generic name in the database
                $db_con->usr_id = $this->user()->id();
                $db_con->set_class(group::class);
                // TODO activate Prio 2
                /*
                if ($db_con->update_old($this->id, self::FLD_DESCRIPTION, $group_name)) {
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
        log_debug('group->selector for ' . $this->id . ' and user "' . $this->user()->name . '"');

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
        $result = new user_message();

        foreach ($api_json as $key => $value) {

            if ($key == sandbox_exp::FLD_NAME) {
                $this->name = $value;
            }
        }

        if ($result->is_ok() and $do_save) {
            $result->add_message($this->save_id());
        }

        return $result;
    }


    /**
     * create a new phrase group
     */
    private function save_id(): ?int
    {
        log_debug($this->dsp_id());

        global $db_con;

        if ($this->id <= 0) {
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
     *
     * @return user_message
     */
    function del(): user_message
    {
        global $db_con;
        $result = new user_message();

        $db_con->set_class(group::class);
        $db_con->usr_id = $this->user()->id();
        $msg = $db_con->delete_old(self::FLD_ID, $this->id);
        $result->add_message($msg);

        return $result;
    }


    /*
     * sql write
     */

    /**
     * create the sql statement to add a new group name to the database
     *
     * @param sql $sc with the target db_type set
     * @param array $sc_par_lst the parameters for the sql statement creation
     * @return sql_par the SQL insert statement, the name of the SQL statement and the parameter list
     */
    function sql_insert(sql $sc, array $sc_par_lst): sql_par
    {
        $qp = $this->sql_common($sc, $sc_par_lst);
        // overwrite the standard auto increase id field name
        $sc->set_id_field($this->id_field());
        $qp->name .= sql::file_sep . sql::file_insert;
        $sc->set_name($qp->name);
        $fields = array(group::FLD_ID, user::FLD_ID, self::FLD_NAME, self::FLD_DESCRIPTION);
        $values = array($this->id(), $this->user()->id(), $this->name, $this->description);
        $qp->sql = $sc->create_sql_insert($fields, $values);
        $qp->par = $values;

        return $qp;
    }

    /**
     * create the sql statement to update a group name in the database
     *
     * @param sql $sc with the target db_type set
     * @param group $db_grp
     * @param array $sc_par_lst the parameters for the sql statement creation
     * @return sql_par the SQL insert statement, the name of the SQL statement and the parameter list
     */
    function sql_update(sql $sc, group $db_grp, array $sc_par_lst): sql_par
    {
        $lib = new library();
        $qp = $this->sql_common($sc, $sc_par_lst);
        $fields = $this->db_fields_changed($db_grp);
        $values = $this->db_values_changed($db_grp);;
        $all_fields = $this->db_fields_all();
        if (count($fields) == 0) {
            $fields = array(self::FLD_NAME, self::FLD_DESCRIPTION);
        }
        if (count($values) == 0) {
            $values = array($this->name, $this->description);
        }
        $fld_name = implode('_', $lib->sql_name_shorten($fields));
        $qp->name .= '_upd_' . $fld_name;
        $sc->set_name($qp->name);
        $qp->sql = $sc->create_sql_update($this->id_field(), $this->id(), $fields, $values);
        $values[] = $this->id();
        $qp->par = $values;
        return $qp;
    }

    /**
     * the common part of the sql statement creation for insert and update statements
     * @param sql $sc with the target db_type set
     * @param array $sc_par_lst the parameters for the sql statement creation
     * @return sql_par the common part for insert and update sql statements
     */
    protected function sql_common(sql $sc, array $sc_par_lst = []): sql_par
    {
        $lib = new library();
        $usr_tbl = $sc->is_usr_tbl($sc_par_lst);
        $tbl_typ = $this->table_type();
        $ext = $tbl_typ->extension();
        $sc->set_class($this::class, $sc_par_lst, $tbl_typ->extension());
        $sql_name = $lib->class_to_name($this::class);
        $qp = new sql_par($sql_name);
        $qp->name = $sql_name . $ext;
        if ($usr_tbl) {
            $qp->name .= '_user';
        }
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
    function db_fields_all(): array
    {
        return array_merge([self::FLD_NAME, self::FLD_DESCRIPTION]);
    }

    /**
     * get a list of database fields that have been updated
     *
     * @param group $grp the compare value to detect the changed fields
     * @return array list of the database field names that have been updated
     */
    function db_fields_changed(group $grp): array
    {
        $result = [];
        if ($grp->name() <> $this->name()) {
            $result[] = self::FLD_NAME;
        }
        if ($grp->description <> $this->description) {
            $result[] = self::FLD_DESCRIPTION;
        }
        return $result;
    }

    /**
     * get a list of database field values that have been updated
     *
     * @param group $grp the compare value to detect the changed fields
     * @return array list of the database field values that have been updated
     */
    function db_values_changed(group $grp): array
    {
        $result = [];
        if ($grp->name() <> $this->name()) {
            $result[] = $this->name();
        }
        if ($grp->description <> $this->description) {
            $result[] = $this->description;
        }
        return $result;
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
        $db_con->add_par(sql_par_type::INT, $this->id);
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
            $result .= '"' . $this->name() . '" (group_id ' . $this->id . ')';
        } else {
            $result .= 'group_id ' . $this->id;
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
            $name_lst = $this->phrase_list()->names();
            $result = implode(",", $name_lst);
        }

        return $result;
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