<?php

/*

    model/phrase/phrase_group.php - a combination of a word list and a triple_list
    -----------------------------

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

namespace cfg;

include_once MODEL_HELPER_PATH . 'db_object.php';
include_once MODEL_PHRASE_PATH . 'phr_ids.php';
include_once MODEL_PHRASE_PATH . 'phrase_list.php';
include_once MODEL_PHRASE_PATH . 'phrase_group_word_link.php';
include_once MODEL_PHRASE_PATH . 'phrase_group_triple_link.php';
include_once API_PHRASE_PATH . 'phrase_group.php';

use api\phrase_group_api;
use model\export\exp_obj;

class phrase_group extends db_object
{

    /*
     * database link
     */

    // object specific database and JSON object field names
    const FLD_ID = 'phrase_group_id';
    const FLD_NAME = 'phrase_group_name';
    const FLD_DESCRIPTION = 'auto_description';
    const FLD_WORD_IDS = 'word_ids';
    const FLD_TRIPLE_IDS = 'triple_ids';
    const FLD_ORDER = 'id_order';

    // all database field names excluding the id
    const FLD_NAMES = array(
        self::FLD_DESCRIPTION,
        self::FLD_WORD_IDS,
        self::FLD_TRIPLE_IDS,
        self::FLD_ORDER
    );


    /*
     * object vars
     */

    // database fields
    public ?string $grp_name;     // maybe later the user should have the possibility to overwrite the generic name, but this is not user at the moment
    public phrase_list $phr_lst;  // the phrase list object
    public ?string $id_order_txt; // the ids from above in the order that the user wants to see them

    // to deprecate
    public ?string $auto_name;    // the automatically created generic name for the word group, used for a quick display of values

    // in memory only fields
    private user $usr;             // the user object of the person for whom the word and triple list is loaded, so to say the viewer
    public ?array $id_order = array();       // the ids from above in the order that the user wants to see them


    /*
     * construct and map
     */

    /**
     * set the user which is needed in all cases
     * @param user $usr the user who requested to see this phrase group
     */
    function __construct(user $usr, int $id = 0, array $prh_names = [])
    {
        parent::__construct();
        $this->set_user($usr);

        $this->reset();

        if ($id > 0) {
            $this->id = $id;
        }
        $this->add_phrase_names($prh_names);
    }

    private function reset(): void
    {
        $this->id = 0;
        $this->grp_name = null;
        $this->auto_name = null;
        $this->phr_lst = new phrase_list($this->usr);
        $this->id_order_txt = null;

        $this->id_order = array();
    }

    /**
     * map the database fields to one db row to this phrase group object
     *
     * @param array|null $db_row with the data directly from the database
     * @param string $id_fld the name of the id field as set in the child class
     * @return bool true if one phrase group is found
     */
    function row_mapper(?array $db_row, string $id_fld = ''): bool
    {
        $result = parent::row_mapper($db_row, self::FLD_ID);
        if ($result) {
            $this->grp_name = $db_row[self::FLD_NAME];
            $this->auto_name = $db_row[self::FLD_DESCRIPTION];
            $this->phr_lst->add_by_ids(
                $db_row[self::FLD_WORD_IDS],
                $db_row[self::FLD_TRIPLE_IDS]
            );
            $this->load_lst();
        }
        return $result;
    }


    /*
     * set and get
     */

    /**
     * set the user of the phrase group
     *
     * @param user $usr the person who wants to access the phrase group
     * @return void
     */
    function set_user(user $usr): void
    {
        $this->usr = $usr;
    }

    /**
     * @return user the person who wants to see the phrase group
     */
    function user(): user
    {
        return $this->usr;
    }

    function set_name(string $name = ''): void
    {
        if ($name != '') {
            $this->grp_name = $name;
        } else {
            if ($this->phr_lst->count() > 0) {
                $this->grp_name = implode(',', $this->phr_lst->names());
            } else {
                log_warning('name of phrase group ' . $this->dsp_id() . ' missing');
            }
        }
    }


    /*
     * cast
     */

    /**
     * @return phrase_group_api the phrase group frontend API object
     */
    function api_obj(): phrase_group_api
    {
        $api_obj = new phrase_group_api();
        $api_obj->reset_lst();
        foreach ($this->phr_lst->lst() as $phr) {
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
     * load
     */

    /**
     * create the common part of an SQL statement to retrieve the complete phrase group from the database
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param string $query_name the name of the query use to prepare and call the query
     * @param string $class the name of this class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    protected function load_sql(sql_db $db_con, string $query_name, string $class = self::class): sql_par
    {
        $qp = parent::load_sql($db_con, $query_name, $class);

        $db_con->set_type(sql_db::TBL_PHRASE_GROUP);
        $db_con->set_name($qp->name);
        $db_con->set_fields(self::FLD_NAMES);

        return $qp;
    }


    /*
     * load functions - the set functions are used to define the loading selection criteria
     */

    /**
     * create an SQL statement to retrieve a phrase groups from the database
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @return sql_par the SQL statement base on the parameters set in $this
     */
    function load_sql_obj_vars(sql_db $db_con): sql_par
    {
        $db_con->set_type(sql_db::TBL_PHRASE_GROUP);
        $qp = new sql_par(self::class);
        $qp->name .= $this->load_sql_name_ext();
        $db_con->set_name($qp->name);
        $db_con->set_usr($this->user()->id());
        $db_con->set_fields(self::FLD_NAMES);

        return $this->load_sql_select_qp($db_con, $qp);
    }

    // TODO review
    function load_by_id(int $id, string $class = self::class): int
    {
        $this->set_id($id);
        return $this->load_by_obj_vars();
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
            if ($result and $this->phr_lst->empty()) {
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
        $phr_lst = new phrase_list($this->usr);
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
        $this->phr_lst = $phr_lst;
        return $this->load_by_obj_vars();
    }

    /**
     * load the word and triple objects based on the ids load from the database if needed
     */
    private function load_lst(): void
    {
        if (!$this->phr_lst->loaded()) {
            $ids = $this->phr_lst->phrase_ids();
            $this->phr_lst->load_by_ids_old($ids);
        }
    }

    /**
     * @return string the name of the SQL statement name extension based on the filled fields
     */
    private function load_sql_name_ext(): string
    {
        if ($this->id != 0) {
            return sql_db::FLD_ID;
        } elseif (count($this->phr_lst->wrd_ids()) > 0 and count($this->phr_lst->trp_ids()) > 0) {
            return 'wrd_and_trp_ids';
        } elseif (count($this->phr_lst->trp_ids()) > 0) {
            return 'trp_ids';
        } elseif (count($this->phr_lst->wrd_ids()) > 0) {
            return 'wrd_ids';
        } elseif ($this->grp_name != '') {
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
     * @param sql_db $db_con the db connection object with the SQL name and others parameter already set
     * @param sql_par $qp the query parameters with the name already set
     * @return sql_par the query parameters with the select parameters added
     */
    private function load_sql_select_qp(sql_db $db_con, sql_par $qp): sql_par
    {
        $wrd_txt = implode(',', $this->phr_lst->wrd_ids());
        $trp_txt = implode(',', $this->phr_lst->trp_ids());
        if ($this->id != 0) {
            $db_con->add_par(sql_db::PAR_INT, $this->id);
            $qp->sql = $db_con->select_by_set_id();
        } elseif ($wrd_txt != '' and $trp_txt != '') {
            $db_con->add_par(sql_db::PAR_TEXT, $wrd_txt);
            $db_con->add_par(sql_db::PAR_TEXT, $trp_txt);
            $qp->sql = $db_con->select_by_field_list(array(self::FLD_WORD_IDS, self::FLD_TRIPLE_IDS));
        } elseif ($trp_txt != '') {
            $db_con->add_par(sql_db::PAR_TEXT, $trp_txt);
            $qp->sql = $db_con->select_by_field_list(array(self::FLD_TRIPLE_IDS));
        } elseif ($wrd_txt != '') {
            $db_con->add_par(sql_db::PAR_TEXT, $wrd_txt);
            $qp->sql = $db_con->select_by_field_list(array(self::FLD_WORD_IDS));
        } elseif ($this->grp_name != '') {
            $db_con->add_par(sql_db::PAR_TEXT, $this->grp_name);
            $qp->sql = $db_con->select_by_field_list(array(self::FLD_NAME));
        }
        $qp->par = $db_con->get_par();
        return $qp;
    }


    /*
     * get functions - to load or create with one call
     */

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
        $wrd_lst = $this->phr_lst->wrd_lst();

        $sql_name = 'phrase_group_by_';
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
            $sql_from .= 'phrase_groups ';
            $sql_where .= 'phrase_group_id = ' . $this->id;
        } else {
            $pos = 1;
            $prev_pos = 1;
            $sql_from_prefix = 'l1.';
            foreach ($wrd_lst->lst() as $wrd) {
                if ($wrd != null) {
                    if ($wrd->id() <> 0) {
                        if ($sql_from == '') {
                            $sql_from .= 'phrase_group_word_links l' . $pos;
                        } else {
                            $sql_from .= ', phrase_group_word_links l' . $pos;
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
        $sql = "SELECT " . $sql_from_prefix . "phrase_group_id 
                  FROM " . $sql_from . "
                 WHERE " . $sql_where . "
              GROUP BY " . $sql_from_prefix . "phrase_group_id;";
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
    private function get_by_wrd_lst(): phrase_group
    {

        global $db_con;
        $result = null;

        $wrd_lst = $this->phr_lst->wrd_lst();

        if (isset($wrd_lst)) {
            if ($wrd_lst->lst > 0) {

                $pos = 1;
                $prev_pos = 1;
                $sql_from = '';
                $sql_where = '';
                foreach ($wrd_lst->ids as $wrd_id) {
                    if ($sql_from == '') {
                        $sql_from .= 'phrase_group_word_links l' . $pos;
                    } else {
                        $sql_from .= ', phrase_group_word_links l' . $pos;
                    }
                    if ($sql_where == '') {
                        $sql_where .= 'l' . $pos . '.word_id = ' . $wrd_id;
                    } else {
                        $sql_where .= ' AND l' . $pos . '.word_id = l' . $prev_pos . '.word_id AND l' . $pos . '.word_id = ' . $wrd_id;
                    }
                    $prev_pos = $pos;
                    $pos++;
                }
                $sql = "SELECT" . " l1.phrase_group_id
                  FROM " . $sql_from . "
                 WHERE " . $sql_where . "
              GROUP BY l1.phrase_group_id;";
                log_debug('phrase_group->get_by_wrd_lst sql ' . $sql);
                //$db_con = New mysql;
                $db_con->usr_id = $this->user()->id();
                $db_grp = $db_con->get1_old($sql);
                if ($db_grp != null) {
                    $this->id = $db_grp[phrase_group::FLD_ID];
                    if ($this->id > 0) {
                        log_debug('phrase_group->get_by_wrd_lst got id ' . $this->id);
                        $result = $this->load();
                        log_debug('phrase_group->get_by_wrd_lst ' . $result . ' found <' . $this->id . '> for ' . $wrd_lst->name() . ' and user ' . $this->user()->name);
                    } else {
                        log_warning('No group found for words ' . $wrd_lst->name() . '.', "phrase_group->get_by_wrd_lst");
                    }
                }
            } else {
                log_warning("Word list is empty.", "phrase_group->get_by_wrd_lst");
            }
        } else {
            log_warning("Word list is missing.", "phrase_group->get_by_wrd_lst");
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
                if (!in_array($prh_name, $this->phr_lst->names())) {
                    // if only the name is know, add a simple word
                    $wrd = new word($this->usr);
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
     * display functions
     */

    /**
     * return best possible id for this element mainly used for debugging
     */
    function dsp_id(): string
    {
        $result = '';

        if ($this->name() <> '') {
            $result .= '"' . $this->name() . '" (' . $this->id . ')';
        } else {
            $result .= $this->id;
        }
        if ($this->grp_name <> '') {
            $result .= ' as "' . $this->grp_name . '"';
        }
        if ($result == '') {
            if (isset($this->phr_lst)) {
                $result .= ' for phrases ' . $this->phr_lst->dsp_id();
            }
        }
        if ($this->user() != null) {
            $result .= ' for user ' . $this->user()->id() . ' (' . $this->user()->name . ')';
        }

        return $result;
    }

    /**
     * @return string with the group name
     */
    function name(): string
    {
        if ($this->grp_name <> '') {
            // use the user defined description
            $result = $this->grp_name;
        } else {
            // or use the standard generic description
            $name_lst = $this->phr_lst->names();
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

        return $this->phr_lst->names();
    }

    /**
     * return the first value related to the word lst
     * or an array with the value and the user_id if the result is user specific
     */
    function value(): value
    {
        $val = new value($this->usr);
        $val->load_by_grp($this);

        log_debug($val->grp->dsp_id() . ' for "' . $this->user()->name . '" is ' . $val->number());
        return $val;
    }

    /**
     * @param $time_wrd_id
     * @return array|null
     */
    function result($time_wrd_id): ?array
    {
        log_debug($this->id . ",time" . $time_wrd_id . ",u" . $this->user()->name);

        global $db_con;

        //$db_con = new mysql;
        $db_con->usr_id = $this->user()->id();
        $sql = "SELECT result_id AS id,
                   result    AS num,
                   user_id          AS usr,
                   last_update      AS upd
              FROM results 
             WHERE phrase_group_id = " . $this->id . "
               AND user_id = " . $this->user()->id() . ";";
        $result = $db_con->get1_old($sql);

        // if no user specific result is found, get the standard result
        if ($result === false) {
            $sql = "SELECT result_id AS id,
                     result    AS num,
                     user_id          AS usr,
                     last_update      AS upd
                FROM results 
               WHERE phrase_group_id = " . $this->id . "
                 AND (user_id = 0 OR user_id IS NULL);";
            $result = $db_con->get1_old($sql);

            // get any time value: to be adjusted to: use the latest
            if ($result === false) {
                $sql = "SELECT result_id AS id,
                       result    AS num,
                       user_id          AS usr,
                       last_update      AS upd
                  FROM results 
                 WHERE phrase_group_id = " . $this->id . "
                   AND (user_id = 0 OR user_id IS NULL);";
                $result = $db_con->get1_old($sql);
                log_debug($result['num']);
            } else {
                log_debug($result['num']);
            }
        } else {
            log_debug($result['num'] . " for " . $this->user()->id());
        }

        return $result;
    }

    /**
     * create the generic group name (and update the database record if needed and possible)
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
        $group_name = $this->phr_lst->dsp_name();

        // update the name if possible and needed
        if ($this->auto_name <> $group_name and $do_save) {
            if ($this->id > 0) {
                // update the generic name in the database
                $db_con->usr_id = $this->user()->id();
                $db_con->set_type(sql_db::TBL_PHRASE_GROUP);
                if ($db_con->update($this->id, self::FLD_DESCRIPTION, $group_name)) {
                    $result = $group_name;
                }
                log_debug('updated to ' . $group_name);
            }
            $this->auto_name = $group_name;
        }
        log_debug('group name ' . $group_name);

        return $result;
    }

    /*
     * create the HTML code to select a phrase group be selecting a combination of words and triples
    private function selector()
    {
        $result = '';
        log_debug('phrase_group->selector for ' . $this->id . ' and user "' . $this->user()->name . '"');

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

            if ($key == exp_obj::FLD_NAME) {
                $this->grp_name = $value;
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
     * create a new phrase group
     */
    private function save_id(): ?int
    {
        log_debug($this->dsp_id());

        global $db_con;

        if ($this->id <= 0) {
            $this->generic_name();

            // write new group
            $wrd_id_txt = implode(',', $this->phr_lst->wrd_ids());
            $trp_id_txt = implode(',', $this->phr_lst->trp_ids());
            if ($wrd_id_txt <> '' or $trp_id_txt <> '') {
                $db_con->usr_id = $this->user()->id();

                if (strlen($wrd_id_txt) > 255) {
                    log_err('Too many words assigned to one value ("' . $wrd_id_txt . '" is longer than the max database size of 255).', "phrase_group->set_wrd_id_txt");
                    $wrd_id_txt = substr($wrd_id_txt, 0, 255);
                }
                if (strlen($trp_id_txt) > 255) {
                    log_err('Too many triple assigned to one value ("' . $wrd_id_txt . '" is longer than the max database size of 255).', "phrase_group->set_wrd_id_txt");
                    $trp_id_txt = substr($trp_id_txt, 0, 255);
                }

                $db_con->set_type(sql_db::TBL_PHRASE_GROUP);
                $this->id = $db_con->insert(array(self::FLD_WORD_IDS, self::FLD_TRIPLE_IDS, self::FLD_DESCRIPTION),
                    array($wrd_id_txt, $trp_id_txt, $this->auto_name));
            } else {
                log_err('Either a word (' . $wrd_id_txt . ') or triple (' . $trp_id_txt . ')  must be set to create a group for ' . $this->dsp_id() . '.', 'phrase_group->save_id');
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
        if ($type == sql_db::TBL_WORD) {
            $lnk = new phrase_group_word_link();
            $qp = $lnk->load_by_group_id_sql($db_con, $this);
            $table_name = $db_con->get_table_name(sql_db::TBL_PHRASE_GROUP_WORD_LINK);
            $field_name = word::FLD_ID;
        } else {
            $lnk = new phrase_group_triple_link();
            $qp = $lnk->load_by_group_id_sql($db_con, $this);
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
            $add_ids = array_diff($this->phr_lst->wrd_ids(), $db_ids);
            $del_ids = array_diff($db_ids, $this->phr_lst->wrd_ids());
        } else {
            $add_ids = array_diff($this->phr_lst->trp_ids(), $db_ids);
            $del_ids = array_diff($db_ids, $this->phr_lst->trp_ids());
        }

        // add the missing links
        if (count($add_ids) > 0) {
            $add_nbr = 0;
            $sql = '';
            foreach ($add_ids as $add_id) {
                if ($add_id <> '') {
                    if ($sql == '') {
                        $sql = 'INSERT INTO ' . $table_name . ' (phrase_group_id, ' . $field_name . ') VALUES ';
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
                //$sql_result = $db_con->exe($sql, 'phrase_group->save_phr_links', array());
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
               WHERE phrase_group_id = ' . $this->id . '
                ' . $lib->sql_array($del_ids, ' AND ' . $field_name . ' IN (', ')') . ';';
            //$sql_result = $db_con->exe($sql, "phrase_group->delete_phr_links", array());
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

        $db_con->set_type(sql_db::TBL_PHRASE_GROUP_WORD_LINK);
        $db_con->usr_id = $this->user()->id();
        $msg = $db_con->delete(self::FLD_ID, $this->id);
        $result->add_message($msg);

        $db_con->set_type(sql_db::TBL_PHRASE_GROUP_TRIPLE_LINK);
        $db_con->usr_id = $this->user()->id();
        $msg = $db_con->delete(self::FLD_ID, $this->id);
        $result->add_message($msg);

        // delete the related value
        $val = new value($this->usr);
        $val->load_by_grp($this);

        if ($val->id > 0) {
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

        $db_con->set_type(sql_db::TBL_PHRASE_GROUP);
        $db_con->usr_id = $this->user()->id();
        $msg = $db_con->delete(self::FLD_ID, $this->id);
        $result->add_message($msg);

        return $result;
    }

    /*
     * testing only
     */

    /**
     * internal function for testing the link for fast search
     */
    function load_link_ids_for_testing(): array
    {

        global $db_con;
        $result = array();

        $db_con->set_type(sql_db::VT_PHRASE_GROUP_LINK);
        $db_con->usr_id = $this->user()->id();
        $qp = new sql_par(self::class);
        $qp->name .= 'test_link_ids';
        $db_con->set_name($qp->name);
        $db_con->set_fields(array(phrase::FLD_ID));
        $db_con->add_par(sql_db::PAR_INT, $this->id);
        $qp->sql = $db_con->select_by_field(phrase_group::FLD_ID);
        $qp->par = $db_con->get_par();
        $lnk_id_lst = $db_con->get($qp);
        foreach ($lnk_id_lst as $db_row) {
            $result[] = $db_row[phrase::FLD_ID];
        }

        asort($result);
        return $result;
    }

}