<?php

/*

    model/phrase/group.php - a combination of a word list and a triple_list
    -----------------------------

    TODO use a new table group_links to link the phrases to a group
    TODO add a order_nbr field to the group_links table
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
use cfg\db\sql_par;
use cfg\db_object;
use cfg\db\sql;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
use cfg\db\sql_par_type;
use cfg\library;
use cfg\phr_ids;
use cfg\phrase;
use cfg\phrase_list;
use cfg\result\result;
use cfg\sandbox_non_seq_id;
use cfg\sandbox_value;
use cfg\db\sql_db;
use cfg\triple;
use cfg\user;
use cfg\user_message;
use cfg\value\value;
use cfg\word;
use cfg\export\sandbox_exp;

class group extends sandbox_non_seq_id
{

    /*
     * database link
     */

    // object specific database and JSON object field names
    const FLD_ID = 'group_id';
    const FLD_NAME = 'group_name';
    const FLD_DESCRIPTION = 'description';

    // comments used for the database creation
    const TBL_COMMENT = 'to add a user given name using a 512-bit group id index for up to 16 32-bit phrase ids including the order';
    const TBL_COMMENT_PRIME = 'to add a user given name using a 64-bit group id index for up to four 16-bit phrase ids including the order';
    const TBL_COMMENT_BIG = 'to add a user given name using a group id index with a variable length for more than 16 32-bit phrase ids including the order';
    const TBL_EXT_PRIME = '_prime'; // the table name extension for up to four prime phrase ids
    const TBL_EXT_BIG = '_big'; // the table name extension for more than 16 phrase ids

    // list of fields with parameters used for the database creation
    // the fields that can be changed by the user
    const FLD_LST_CREATE_CHANGEABLE = array(
        [self::FLD_NAME, sql_field_type::TEXT, sql_field_default::NULL, '', '', 'the user specific group name which can contain the phrase names in a different order to display the group (does not need to be unique)'],
        [self::FLD_DESCRIPTION, sql_field_type::TEXT, sql_field_default::NULL, '', '', 'the user specific description for mouse over helps'],
    );

    // all database field names excluding the id
    const FLD_NAMES = array(
        self::FLD_DESCRIPTION
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
        $this->set_id(0);
        if ($db_row != null) {
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
     * @return int|string either a 62-bit int, a 512-bit id with 16 phrase ids or a text with more than 16 +/- seperated 6 char alpha_num coded phrase ids
     * the internal null value is used to detect if database saving has been tried
     */
    function id(): int|string
    {
        return $this->id;
    }

    /**
     * @return string the extension for the table name based on the id
     */
    function table_extension(): string
    {
        $ext = '';
        $grp_id = new group_id();
        if ($grp_id->is_prime($this->id())) {
            $ext = self::TBL_EXT_PRIME;
        } elseif ($grp_id->is_big($this->id())) {
            $ext = self::TBL_EXT_BIG;
        }
        return $ext;
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
        $sql_lst = $this->sql_one_tbl($sc, false, '', sandbox_value::FLD_KEY, $this::TBL_COMMENT, $sql_lst);
        $sql_lst = $this->sql_one_tbl($sc, true, '', sandbox_value::FLD_KEY_USER, $this::TBL_COMMENT, $sql_lst);
        $sql_lst = $this->sql_one_tbl($sc, false, group::TBL_EXT_PRIME, sandbox_value::FLD_KEY_PRIME, $this::TBL_COMMENT_PRIME, $sql_lst);
        $sql_lst = $this->sql_one_tbl($sc, true, group::TBL_EXT_PRIME, sandbox_value::FLD_KEY_PRIME_USER, $this::TBL_COMMENT_PRIME, $sql_lst);
        $sql_lst = $this->sql_one_tbl($sc, false, group::TBL_EXT_BIG, sandbox_value::FLD_KEY_BIG, $this::TBL_COMMENT_BIG, $sql_lst);
        return $this->sql_one_tbl($sc, true, group::TBL_EXT_BIG, sandbox_value::FLD_KEY_BIG_USER, $this::TBL_COMMENT_BIG, $sql_lst);
    }

    /**
     * add the sql statements for one table to the given array of sql statements
     * @param sql $sc the sql creator object with the target db_type set
     * @param bool $usr_table true if the table should save the user specific changes
     * @param string $tbl_ext the table extension e.g. prime for a short list of primarily used phrases
     * @param array $key_fld with the parameter for the table primary key field
     * @param string $tbl_comment the comment for the table in the sql statement
     * @param array $sql_lst the list with the sql statements created until now
     * @return array the list of sql statements including the statements created by this function call
     */
    private function sql_one_tbl(
        sql    $sc,
        bool   $usr_table,
        string $tbl_ext,
        array  $key_fld,
        string $tbl_comment,
        array  $sql_lst
    ): array
    {
        $sc->set_class($this::class, $usr_table, $tbl_ext);
        $fields = array_merge($key_fld, sandbox_value::FLD_ALL_OWNER, $this::FLD_LST_CREATE_CHANGEABLE);
        if ($usr_table) {
            $fields = array_merge($key_fld, sandbox_value::FLD_ALL_CHANGER, $this::FLD_LST_CREATE_CHANGEABLE);
        }
        $sql_lst[0] .= parent::sql_table_create($sc, $usr_table, $fields, $tbl_comment);
        $sql_lst[1] .= parent::sql_index_create($sc, $usr_table, $fields);
        $sql_lst[2] .= parent::sql_foreign_key_create($sc, $usr_table, $fields);
        $sql_lst[3] .= parent::sql_truncate_create($sc, $usr_table);
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
        $ext = $this->table_extension();
        $qp = $this->load_sql_multi($sc, sql_db::FLD_ID, $class, $ext);
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
     * @return int the id of the object found and zero if nothing is found
     */
    protected function load(sql_par $qp): int
    {
        parent::load_without_id_return($qp);
        return $this->id();
    }

    /**
     * TODO move (and other functions) to db_object and rename the existing db_object to db_id_object
     * just set the class name for the user sandbox function
     * load a word object by database id
     * @param int|string $id the id of the group
     * @param string $class the group class name
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_id(int|string $id, string $class = self::class): int
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

        $qp = $this->load_sql_obj_vars($db_con);

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
        return $this->load_by_lst($phr_lst);
    }

    /**
     * shortcut function to create group based on a phrase list
     * @param phrase_list $phr_lst list of phrases
     * @return bool
     */
    function load_by_lst(phrase_list $phr_lst): bool
    {
        // TODO review
        // $phr_lst->ex_time();
        $this->set_phrase_list($phr_lst);
        return $this->load_by_obj_vars();
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
            if ($do_save) {
                $result .= $this->save_links();  // update the database links for fast selection
            }
            $result .= $this->generic_name($do_save); // update the generic name if needed
        }

        log_debug('got ' . $this->dsp_id());
        return $result;
    }

    /**
     * set the group id (and create a new group if needed)
     * ex grp_id that returns the id
     */
    function get_id(): ?int
    {
        log_debug($this->dsp_id());
        $this->get();
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
        $phr_lst = $this->phrase_list()->time_lst();
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
                $db_con->set_class(sql_db::TBL_GROUP);
                // TODO activate
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


    /*
     * save function - because the phrase group is a wrapper for a word and triple list the save function should not be called from outside this class
     */

    /**
     * get a list of database fields that have been updated
     *
     * @param group $grp the compare value to detect the changed fields
     * @return array list of the database field names that have been updated
     */
    function changed_db_fields(group $grp): array
    {
        $is_updated = false;
        $result = [];
        if ($grp->name() <> $this->name()) {
            $result[] = self::FLD_NAME;
            $is_updated = true;
        }
        if ($grp->description <> $this->description) {
            $result[] = self::FLD_DESCRIPTION;
            $is_updated = true;
        }
        return $result;
    }

    /**
     * get a list of database field values that have been updated
     *
     * @param group $grp the compare value to detect the changed fields
     * @return array list of the database field values that have been updated
     */
    function changed_db_values(group $grp): array
    {
        $is_updated = false;
        $result = [];
        if ($grp->name() <> $this->name()) {
            $result[] = $this->name();
            $is_updated = true;
        }
        if ($grp->description <> $this->description) {
            $result[] = $this->description;
            $is_updated = true;
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
     * create the word group links for faster selection of the word groups based on single words
     */
    private function save_links(): string
    {
        $result = $this->save_phr_links(sql_db::TBL_WORD);
        $result .= $this->save_phr_links(sql_db::TBL_TRIPLE);
        return $result;
    }

    /**
     * create links to the group from words or triples for faster selection of the phrase groups based on single words or triples
     * word and triple links are saved in two different tables to be able to use the database foreign keys
     */
    private function save_phr_links($type): string
    {
        log_debug();

        global $db_con;
        $result = '';
        $lib = new library();

        // create the db link object for all actions
        $db_con->usr_id = $this->user()->id();

        // switch between the word and triple settings
        $lnk = new group_link();
        $qp = $lnk->load_by_group_id_sql($db_con, $this);
        if ($type == sql_db::TBL_WORD) {
            $table_name = $db_con->get_table_name(sql_db::TBL_GROUP_LINK);
            $field_name = word::FLD_ID;
        } else {
            $table_name = $db_con->get_table_name(sql_db::TBL_PHRASE_GROUP_TRIPLE_LINK);
            $field_name = triple::FLD_ID;
        }

        // read all existing group links
        $grp_lnk_rows = $db_con->get($qp);
        $db_ids = array();
        if ($grp_lnk_rows != null) {
            foreach ($grp_lnk_rows as $grp_lnk_row) {
                $db_ids[] = $grp_lnk_row[$field_name];
            }
            log_debug('found ' . implode(",", $db_ids));
        }

        // switch between the word and triple settings
        if ($type == sql_db::TBL_WORD) {
            $add_ids = array_diff($this->phrase_list()->wrd_ids(), $db_ids);
            $del_ids = array_diff($db_ids, $this->phrase_list()->wrd_ids());
        } else {
            $add_ids = array_diff($this->phrase_list()->trp_ids(), $db_ids);
            $del_ids = array_diff($db_ids, $this->phrase_list()->trp_ids());
        }

        // add the missing links
        if (count($add_ids) > 0) {
            $add_nbr = 0;
            $sql = '';
            foreach ($add_ids as $add_id) {
                if ($add_id <> '') {
                    if ($sql == '') {
                        $sql = 'INSERT INTO ' . $table_name . ' (group_id, ' . $field_name . ') VALUES ';
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
            if ($sql <> '') {
                //$sql_result = $db_con->exe($sql, 'group->save_phr_links', array());
                $lib = new library();
                $result = $db_con->exe_try('Adding of group links "' . $lib->dsp_array($add_ids) . '" for ' . $this->id,
                    $sql);
            }
        }
        $lib = new library();
        log_debug('added links "' . $lib->dsp_array($add_ids) . '" lead to ' . implode(",", $db_ids));

        // remove the links not needed any more
        if (count($del_ids) > 0) {
            log_debug('del ' . implode(",", $del_ids));
            $sql = 'DELETE FROM ' . $table_name . ' 
               WHERE group_id = ' . $this->id . '
                ' . $lib->sql_array($del_ids, ' AND ' . $field_name . ' IN (', ')') . ';';
            //$sql_result = $db_con->exe($sql, "group->delete_phr_links", array());
            $result = $db_con->exe_try('Removing of group links "' . $lib->dsp_array($del_ids) . '" from ' . $this->id,
                $sql);
        }
        log_debug('deleted links "' . $lib->dsp_array($del_ids) . '" lead to ' . implode(",", $db_ids));

        return $result;
    }

    /**
     * delete all phrase links to the phrase group e.g. to be able to delete the phrase group
     * @return user_message
     */
    function del_phr_links(): user_message
    {
        global $db_con;
        $result = new user_message();

        $db_con->set_class(sql_db::TBL_GROUP_LINK);
        $db_con->usr_id = $this->user()->id();
        $msg = $db_con->delete(self::FLD_ID, $this->id);
        $result->add_message($msg);

        $db_con->set_class(sql_db::TBL_PHRASE_GROUP_TRIPLE_LINK);
        $db_con->usr_id = $this->user()->id();
        $msg = $db_con->delete(self::FLD_ID, $this->id);
        $result->add_message($msg);

        // delete the related value
        $val = new value($this->user());
        $val->load_by_grp($this);

        if ($val->id() > 0) {
            $val->del();
        }

        return $result;
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
        $result = $this->del_phr_links();

        $db_con->set_class(sql_db::TBL_GROUP);
        $db_con->usr_id = $this->user()->id();
        $msg = $db_con->delete(self::FLD_ID, $this->id);
        $result->add_message($msg);

        return $result;
    }

    /*
     * cur(l)
     */

    /**
     * create the sql statement to add a new group name to the database
     *
     * @param sql $sc with the target db_type set
     * @param bool $usr_tbl true if a db row should be added to the user table
     * @return sql_par the SQL insert statement, the name of the SQL statement and the parameter list
     */
    function sql_insert(sql $sc, bool $usr_tbl = false): sql_par
    {
        $qp = $this->sql_common($sc, $usr_tbl);
        // overwrite the standard auto increase id field name
        $sc->set_id_field($this->id_field());
        $qp->name .= '_insert';
        $sc->set_name($qp->name);
        $fields = array(group::FLD_ID, user::FLD_ID, self::FLD_NAME, self::FLD_DESCRIPTION);
        $values = array($this->id(), $this->user()->id(), $this->name, $this->description);
        $qp->sql = $sc->sql_insert($fields, $values);
        $qp->par = $values;

        return $qp;
    }

    /**
     * create the sql statement to update a group name in the database
     *
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
        $lib = new library();
        $qp = $this->sql_common($sc, $usr_tbl);
        if (count($fields) == 0) {
            $fields = array(self::FLD_NAME, self::FLD_DESCRIPTION);
        }
        if (count($values) == 0) {
            $values = array($this->name, $this->description);
        }
        $fld_name = implode('_', $lib->sql_name_shorten($fields));
        $qp->name .= '_upd_' . $fld_name;
        $sc->set_name($qp->name);
        $qp->sql = $sc->sql_update($this->id_field(), $this->id(), $fields, $values);
        $values[] = $this->id();
        $qp->par = $values;
        return $qp;
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
        $ext = $this->table_extension();
        $sc->set_class($this::class, $usr_tbl, $ext);
        $sql_name = $lib->class_to_name($this::class);
        $qp = new sql_par($sql_name . $ext);
        $qp->name = $sql_name . $ext;
        if ($usr_tbl) {
            $qp->name .= '_user';
        }
        return $qp;
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