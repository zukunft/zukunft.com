<?php

/*

  phrase_group.php - a combination of a word list and a word_link_list
  ----------------
  
  a kind of phrase list, but separated into two different lists
  
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
  along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.
  
  To contact the authors write to:
  Timon Zielonka <timon@zukunft.com>
  
  Copyright (c) 1995-2021 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

use phrase\phrase_group_min;

class phrase_group
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
     * for system testing
     */

    const TN_READ = 'Pi (math)';

    /*
     * object vars
     */

    // database fields
    public ?int $id = null;       // the database id of the word group
    public ?string $grp_name;     // maybe later the user should have the possibility to overwrite the generic name, but this is not user at the moment
    public phrase_list $phr_lst;  // the phrase list object
    public ?string $id_order_txt; // the ids from above in the order that the user wants to see them

    // to deprecate
    public ?string $auto_name;    // the automatically created generic name for the word group, used for a quick display of values

    // in memory only fields
    public user $usr;             // the user object of the person for whom the word and triple list is loaded, so to say the viewer
    public ?array $id_order = array();       // the ids from above in the order that the user wants to see them

    /*
     * construct and map
     */

    /**
     * set the user which is needed in all cases
     * @param user $usr the user who requested to see this phrase group
     */
    function __construct(user $usr)
    {
        $this->usr = $usr;

        $this->reset();
    }

    private function reset()
    {
        $this->id = null;
        $this->grp_name = null;
        $this->auto_name = null;
        $this->phr_lst = new phrase_list($this->usr);
        $this->id_order_txt = null;

        $this->id_order = array();
    }

    /**
     * @return phrase_group the phrase group frontend API object
     */
    function min_obj(): object
    {
        $min_obj = new phrase_group_min();
        $min_obj->lst = array();
        foreach ($this->phr_lst->lst as $phr) {
            $min_obj->lst[] = $phr->get_obj->min_obj();
        }
        $min_obj->id = $this->id;
        return $min_obj;
    }

    function row_mapper(array $db_row): bool
    {
        $result = false;
        $this->id = 0;
        if ($db_row != null) {
            if ($db_row[self::FLD_ID] > 0) {
                $this->id = $db_row[self::FLD_ID];
                $this->grp_name = $db_row[self::FLD_NAME];
                $this->auto_name = $db_row[self::FLD_DESCRIPTION];
                $this->phr_lst->add_by_ids(
                    $db_row[self::FLD_WORD_IDS],
                    $db_row[self::FLD_TRIPLE_IDS]
                );
                $this->load_lst();
                $result = true;
            }
        }
        return $result;
    }

    /*
    load functions - the set functions are used to define the loading selection criteria
    */

    /**
     * create an SQL statement to retrieve a phrase groups from the database
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @return sql_par the SQL statement base on the parameters set in $this
     */
    function load_sql(sql_db $db_con): sql_par
    {
        $qp = new sql_par(self::class);
        $qp->name .= $this->load_sql_name_ext();

        $db_con->set_type(DB_TYPE_PHRASE_GROUP);
        $db_con->set_name($qp->name);
        $db_con->set_usr($this->usr->id);
        $db_con->set_fields(self::FLD_NAMES);

        return $this->load_sql_select_qp($db_con, $qp);
    }

    /**
     * load the object parameters for all users
     * @return bool true if the phrase group object has been loaded
     */
    function load(): bool
    {
        global $db_con;
        $result = false;

        $qp = $this->load_sql($db_con);

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
        $phr_lst->load_by_ids($ids);
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
        $phr_lst->ex_time();
        $this->phr_lst = $phr_lst;
        return $this->load();
    }

    /**
     * load the word and triple objects based on the ids load from the database if needed
     */
    private function load_lst()
    {
        if (!$this->phr_lst->loaded()) {
            $ids = $this->phr_lst->ids();
            $this->phr_lst->load_by_ids($ids);
        }
    }

    /**
     * @return string the name of the SQL statement name extension based on the filled fields
     */
    private function load_sql_name_ext(): string
    {
        if ($this->id != 0) {
            return 'id';
        } elseif (count($this->phr_lst->wrd_ids()) > 0 and count($this->phr_lst->trp_ids()) > 0) {
            return 'wrd_and_trp_ids';
        } elseif (count($this->phr_lst->trp_ids()) > 0) {
            return 'trp_ids';
        } elseif (count($this->phr_lst->wrd_ids()) > 0) {
            return 'wrd_ids';
        } elseif ($this->grp_name != '') {
            return 'name';
        } else {
            log_err('Either the database ID (' . $this->id . ') or the ' .
                self::class . ' link objects (' . $this->dsp_id() . ') and the user (' . $this->usr->id . ') must be set to load a ' .
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
            $qp->sql = $db_con->select_by_id();
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
     * based on a string with the word and triple ids
     */
    function get(): string
    {
        log_debug('phrase_group->get ' . $this->dsp_id());
        $result = '';

        // get the id based on the given parameters
        $test_load = clone $this;
        $result .= $test_load->load();
        log_debug('phrase_group->get loaded ' . $this->dsp_id());

        // use the loaded group or create the word group if it is missing
        if ($test_load->id > 0) {
            $this->id = $test_load->id;
        } else {
            log_debug('phrase_group->get save ' . $this->dsp_id());
            $this->load();
            $result .= $this->save_id();
        }

        // update the database for correct selection references
        if ($this->id > 0) {
            $result .= $this->save_links();  // update the database links for fast selection
            $result .= $this->generic_name(); // update the generic name if needed
        }

        log_debug('phrase_group->get -> got ' . $this->dsp_id());
        return $result;
    }

    /**
     * set the group id (and create a new group if needed)
     * ex grp_id that returns the id
     */
    function get_id(): ?int
    {
        log_debug('phrase_group->get_id ' . $this->dsp_id());
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
            $sql_name .= 'id';
        } elseif (count($wrd_lst->lst) > 0) {
            $sql_name .= count($wrd_lst->lst) . 'word_id';
        } else {
            log_err("Either the database ID (" . $this->id . ") or a word list and the user (" . $this->usr->id . ") must be set to load a phrase list.", "phrase_list->load");
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
            foreach ($wrd_lst->lst as $wrd) {
                if ($wrd != null) {
                    if ($wrd->id <> 0) {
                        if ($sql_from == '') {
                            $sql_from .= 'phrase_group_word_links l' . $pos;
                        } else {
                            $sql_from .= ', phrase_group_word_links l' . $pos;
                        }
                        if ($sql_where == '') {
                            $sql_where .= 'l' . $pos . '.word_id = ' . $wrd->id;
                        } else {
                            $sql_where .= ' AND l' . $pos . '.word_id = l' . $prev_pos . '.word_id AND l' . $pos . '.word_id = ' . $wrd->id;
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
        log_debug('phrase_group->get_by_wrd_lst sql ' . $sql);

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
                $db_con->usr_id = $this->usr->id;
                $db_grp = $db_con->get1_old($sql);
                if ($db_grp != null) {
                    $this->id = $db_grp['phrase_group_id'];
                    if ($this->id > 0) {
                        log_debug('phrase_group->get_by_wrd_lst got id ' . $this->id);
                        $result = $this->load();
                        log_debug('phrase_group->get_by_wrd_lst ' . $result . ' found <' . $this->id . '> for ' . $wrd_lst->name() . ' and user ' . $this->usr->name);
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
        if (isset($this->usr)) {
            $result .= ' for user ' . $this->usr->id . ' (' . $this->usr->name . ')';
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
        log_debug('phrase_group->names');

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
        $val->grp = $this;
        $val->load();

        log_debug('phrase_group->value ' . $val->wrd_lst->name() . ' for "' . $this->usr->name . '" is ' . $val->number);
        return $val;
    }

    /**
     * @param $time_wrd_id
     * @return array|null
     */
    function result($time_wrd_id): ?array
    {
        log_debug("phrase_group->result (" . $this->id . ",time" . $time_wrd_id . ",u" . $this->usr->name . ")");

        global $db_con;

        if ($time_wrd_id > 0) {
            $sql_time = " time_word_id = " . $time_wrd_id . " ";
        } else {
            $sql_time = " (time_word_id IS NULL OR time_word_id = 0) ";
        }

        //$db_con = new mysql;
        $db_con->usr_id = $this->usr->id;
        $sql = "SELECT formula_value_id AS id,
                   formula_value    AS num,
                   user_id          AS usr,
                   last_update      AS upd
              FROM formula_values 
             WHERE phrase_group_id = " . $this->id . "
               AND " . $sql_time . "
               AND user_id = " . $this->usr->id . ";";
        $result = $db_con->get1_old($sql);

        // if no user specific result is found, get the standard result
        if ($result === false) {
            $sql = "SELECT formula_value_id AS id,
                     formula_value    AS num,
                     user_id          AS usr,
                     last_update      AS upd
                FROM formula_values 
               WHERE phrase_group_id = " . $this->id . "
                 AND " . $sql_time . "
                 AND (user_id = 0 OR user_id IS NULL);";
            $result = $db_con->get1_old($sql);

            // get any time value: to be adjusted to: use the latest
            if ($result === false) {
                $sql = "SELECT formula_value_id AS id,
                       formula_value    AS num,
                       user_id          AS usr,
                       last_update      AS upd
                  FROM formula_values 
                 WHERE phrase_group_id = " . $this->id . "
                   AND (user_id = 0 OR user_id IS NULL);";
                $result = $db_con->get1_old($sql);
                log_debug("phrase_group->result -> (" . $result['num'] . ")");
            } else {
                log_debug("phrase_group->result -> (" . $result['num'] . ")");
            }
        } else {
            log_debug("phrase_group->result -> (" . $result['num'] . " for " . $this->usr->id . ")");
        }

        return $result;
    }

    /**
     * create the generic group name (and update the database record if needed and possible)
     * @returns string the generic name if it has been saved to the database
     */
    private function generic_name(): string
    {
        log_debug('phrase_group->generic_name');

        global $db_con;
        $result = '';

        // if not yet done, load, the words and triple list
        $this->load_lst();

        // TODO take the order into account
        $group_name = $this->phr_lst->dsp_name();

        // update the name if possible and needed
        if ($this->auto_name <> $group_name) {
            if ($this->id > 0) {
                // update the generic name in the database
                $db_con->usr_id = $this->usr->id;
                $db_con->set_type(DB_TYPE_PHRASE_GROUP);
                if ($db_con->update($this->id, self::FLD_DESCRIPTION, $group_name)) {
                    $result = $group_name;
                }
                log_debug('phrase_group->generic_name updated to ' . $group_name);
            }
            $this->auto_name = $group_name;
        }
        log_debug('phrase_group->generic_name ... group name ' . $group_name);

        return $result;
    }

    /*
     * create the HTML code to select a phrase group be selecting a combination of words and triples
    private function selector()
    {
        $result = '';
        log_debug('phrase_group->selector for ' . $this->id . ' and user "' . $this->usr->name . '"');

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


    /*
     * save function - because the phrase group is a wrapper for a word and triple list the save function should not be called from outside this class
     */

    /**
     * create a new phrase group
     */
    private function save_id(): ?int
    {
        log_debug('phrase_group->save_id ' . $this->dsp_id());

        global $db_con;

        if ($this->id <= 0) {
            $this->generic_name();

            // write new group
            $wrd_id_txt = implode(',', $this->phr_lst->wrd_ids());
            $trp_id_txt = implode(',', $this->phr_lst->trp_ids());
            if ($wrd_id_txt <> '' or $trp_id_txt <> '') {
                $db_con->usr_id = $this->usr->id;

                if (strlen($wrd_id_txt) > 255) {
                    log_err('Too many words assigned to one value ("' . $wrd_id_txt . '" is longer than the max database size of 255).', "phrase_group->set_wrd_id_txt");
                    $wrd_id_txt = zu_str_left($wrd_id_txt, 255);
                }
                if (strlen($trp_id_txt) > 255) {
                    log_err('Too many triple assigned to one value ("' . $wrd_id_txt . '" is longer than the max database size of 255).', "phrase_group->set_wrd_id_txt");
                    $trp_id_txt = zu_str_left($trp_id_txt, 255);
                }

                $db_con->set_type(DB_TYPE_PHRASE_GROUP);
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
        $result = $this->save_phr_links(DB_TYPE_WORD);
        $result .= $this->save_phr_links(DB_TYPE_TRIPLE);
        return $result;
    }

    /**
     * create links to the group from words or triples for faster selection of the phrase groups based on single words or triples
     * word and triple links are saved in two different tables to be able to use the database foreign keys
     */
    private function save_phr_links($type): string
    {
        log_debug('phrase_group->save_phr_links');

        global $db_con;
        $result = '';

        // create the db link object for all actions
        //$db_con = New mysql;
        $db_con->usr_id = $this->usr->id;

        // switch between the word and triple settings
        if ($type == DB_TYPE_WORD) {
            $table_name = $db_con->get_table_name(DB_TYPE_PHRASE_GROUP_WORD_LINK);
            $field_name = word::FLD_ID;
        } else {
            $table_name = $db_con->get_table_name(DB_TYPE_PHRASE_GROUP_TRIPLE_LINK);
            $field_name = 'triple_id';
        }

        // read all existing group links
        $sql = 'SELECT ' . $field_name . '
              FROM ' . $table_name . '
             WHERE phrase_group_id = ' . $this->id . ';';
        $grp_lnk_rows = $db_con->get_old($sql);
        $db_ids = array();
        if ($grp_lnk_rows != null) {
            foreach ($grp_lnk_rows as $grp_lnk_row) {
                $db_ids[] = $grp_lnk_row[$field_name];
            }
            log_debug('phrase_group->save_phr_links -> found ' . implode(",", $db_ids));
        }

        // switch between the word and triple settings
        if ($type == DB_TYPE_WORD) {
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
                $result = $db_con->exe_try('Adding of group links "' . dsp_array($add_ids) . '" for ' . $this->id,
                    $sql);
            }
        }
        log_debug('phrase_group->save_phr_links -> added links "' . dsp_array($add_ids) . '" lead to ' . implode(",", $db_ids));

        // remove the links not needed any more
        if (count($del_ids) > 0) {
            log_debug('phrase_group->save_phr_links -> del ' . implode(",", $del_ids));
            $sql = 'DELETE FROM ' . $table_name . ' 
               WHERE phrase_group_id = ' . $this->id . '
                 AND ' . $field_name . ' IN (' . sql_array($del_ids) . ');';
            //$sql_result = $db_con->exe($sql, "phrase_group->delete_phr_links", array());
            $result = $db_con->exe_try('Removing of group links "' . dsp_array($del_ids) . '" from ' . $this->id,
                $sql);
        }
        log_debug('phrase_group->save_phr_links -> deleted links "' . dsp_array($del_ids) . '" lead to ' . implode(",", $db_ids));

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

        $db_con->set_type(DB_TYPE_PHRASE_GROUP_WORD_LINK);
        $db_con->usr_id = $this->usr->id;
        $msg = $db_con->delete(self::FLD_ID, $this->id);
        $result->add_message($msg);

        $db_con->set_type(DB_TYPE_PHRASE_GROUP_TRIPLE_LINK);
        $db_con->usr_id = $this->usr->id;
        $msg = $db_con->delete(self::FLD_ID, $this->id);
        $result->add_message($msg);

        // delete the related value
        $val = new value($this->usr);
        $val->grp = $this;
        $val->load();

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

        $db_con->set_type(DB_TYPE_PHRASE_GROUP);
        $db_con->usr_id = $this->usr->id;
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
    function load_link_ids(): array
    {

        global $db_con;
        $result = array();

        $sql = 'SELECT phrase_id 
              FROM phrase_group_phrase_links
             WHERE phrase_group_id = ' . $this->id . ';';
        //$db_con = New mysql;
        $db_con->usr_id = $this->usr->id;
        $lnk_id_lst = $db_con->get_old($sql);
        foreach ($lnk_id_lst as $db_row) {
            $result[] = $db_row[phrase::FLD_ID];
        }

        asort($result);
        return $result;
    }

}