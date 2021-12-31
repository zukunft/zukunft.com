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
     * object vars
     */

    // database fields
    public ?int $id = null;                  // the database id of the word group
    public ?string $grp_name = null;         // maybe later the user should have the possibility to overwrite the generic name, but this is not user at the moment
    public ?string $auto_name = null;        // the automatically created generic name for the word group, used for a quick display of values
    public ?phrase_list $phr_lst = null;     // the phrase list object
    public ?string $id_order_txt = null;     // the ids from above in the order that the user wants to see them

    // fields to deprecate
    public ?string $wrd_id_txt = null;       // text of all linked words in ascending order for fast search (this is the master and the link table "value_phrase_links" is the slave)
    public ?string $lnk_id_txt = null;       // text of all linked triples in ascending order for fast search (as $wrd_id_txt this is the master and a negative id in "value_phrase_links" is the slave)

    // in memory only fields
    public ?user $usr = null;                // the user object of the person for whom the word and triple list is loaded, so to say the viewer
    public ?array $id_order = array();       // the ids from above in the order that the user wants to see them
    public ?array $wrd_ids = array();        // list of the word ids to load a list of words with one sql statement from the database
    public ?array $lnk_ids = array();        // list of the triple ids to load a list of words with one sql statement from the database

    // fields to deprecate
    public ?word_list $wrd_lst = null;       // the word list object
    public ?word_link_list $lnk_lst = null;  // the triple (word_link) object
    public ?array $ids = null;               // list of the phrase (word (positive id) or triple (negative id)) ids
    //                                          this is set by the frontend scripts and converted here to retrieve or create a group
    //                                          the order is always ascending for be able to use this as a index to select the group

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
        $this->phr_lst = new phrase_list($this->usr);
        $this->grp_name = '';
        $this->auto_name = '';

        $this->wrd_id_txt = '';
        $this->lnk_id_txt = '';
        $this->id_order_txt = '';

        $this->ids = array();
        $this->id_order = array();
        $this->wrd_ids = array();
        $this->lnk_ids = array();

        $this->wrd_lst = null;
        $this->lnk_lst = null;
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
        $qp = new sql_par();
        $qp->name = self::class . '_by_' . $this->load_sql_name_ext();

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

            }
        }
        return $result;
    }

    function load_by_lst_ids(array $ids): bool
    {
        $phr_lst = new phrase_list($this->usr);
        $phr_lst->load_by_ids($ids);
        // TODO review
        $phr_lst->ex_time();
        $this->phr_lst = $phr_lst;
        return $this->load();
    }

    /**
     * load the word and triple objects based on the ids load from the database
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
            $qp->sql = $db_con->select();
        } elseif ($wrd_txt != '' and $trp_txt != '') {
            $db_con->add_par(sql_db::PAR_TEXT, "'" . $wrd_txt . "'");
            $db_con->add_par(sql_db::PAR_TEXT, "'" . $trp_txt . "'");
            $qp->sql = $db_con->select_by_link_ids(array(self::FLD_TRIPLE_IDS, self::FLD_WORD_IDS));
        } elseif ($trp_txt != '') {
            $db_con->add_par(sql_db::PAR_TEXT, "'" . $trp_txt . "'");
            $qp->sql = $db_con->select_by_link_ids(array(self::FLD_TRIPLE_IDS));
        } elseif ($wrd_txt != '') {
            $db_con->add_par(sql_db::PAR_TEXT, "'" . $wrd_txt . "'");
            $qp->sql = $db_con->select_by_link_ids(array(self::FLD_WORD_IDS));
        }
        $qp->par = $db_con->get_par();
        return $qp;
    }

    // separate the words from the triples (word_links)
    // this also excludes automatically any empty ids
    private function set_ids_to_wrd_or_lnk_ids()
    {
        $this->wrd_ids = array();
        $this->lnk_ids = array();
        foreach ($this->ids as $id) {
            if ($id > 0) {
                $this->wrd_ids[] = $id;
            } elseif ($id < 0) {
                $this->lnk_ids[] = $id * -1;
            }
        }
        log_debug('phrase_group->set_ids_to_wrd_or_lnk_ids split "' . dsp_array($this->ids) . '" to "' . dsp_array($this->wrd_ids) . '" and "' . dsp_array($this->lnk_ids) . '"');
    }

    // the opposite of set_ids_to_wrd_or_lnk_ids
    private function set_ids_from_wrd_or_lnk_ids()
    {
        log_debug('phrase_group->set_ids_from_wrd_or_lnk_ids for "' . dsp_array($this->wrd_ids) . '"');
        if (isset($this->wrd_ids)) {
            $this->ids = zu_ids_not_zero($this->wrd_ids);
        } else {
            $this->ids = array();
        }
        log_debug('phrase_group->set_ids_from_wrd_or_lnk_ids done words "' . dsp_array($this->ids) . '"');
        if (isset($this->lnk_ids)) {
            log_debug('phrase_group->set_ids_from_wrd_or_lnk_ids try triples "' . dsp_array($this->lnk_ids) . '"');
            foreach ($this->lnk_ids as $id) {
                if (trim($id) <> '') {
                    log_debug('phrase_group->set_ids_from_wrd_or_lnk_ids try triple "' . $id . '"');
                    if ($id == 0) {
                        log_warning('Zero triple id excluded in phrase group "' . $this->auto_name . '" (id ' . $this->id . ').', "phrase_group->set_ids_from_wrd_or_lnk_ids");
                    } else {
                        log_debug('phrase_group->set_ids_from_wrd_or_lnk_ids add triple "' . $id . '"');
                        $this->ids[] = $id * -1;
                    }
                }
            }
        }
        log_debug('phrase_group->set_ids_from_wrd_or_lnk_ids for "' . dsp_array($this->wrd_ids) . '" done');
    }

    // load the word list based on the word id array
    private function set_wrd_lst()
    {
        if (isset($this->wrd_ids)) {
            log_debug('phrase_group->set_wrd_lst for "' . dsp_array($this->wrd_ids) . '"');

            // ignore double word entries
            $this->wrd_ids = array_unique($this->wrd_ids);

            if (count($this->wrd_ids) > 0) {
                // make sure that there is not time word
                // maybe not needed if the calling function has done this already
                $wrd_lst = new word_list;
                $wrd_lst->ids = $this->wrd_ids;
                $wrd_lst->usr = $this->usr;
                $wrd_lst->load();
                $wrd_lst->ex_time();
                $this->wrd_lst = $wrd_lst;
                $this->wrd_ids = $wrd_lst->ids;
                // also fill the phrase list with the converted objects that are already loaded
                $phr_lst = $wrd_lst->phrase_lst();
                if (isset($this->phr_lst) and isset($phr_lst)) {
                    $this->phr_lst = $this->phr_lst->concat_unique($phr_lst);
                } else {
                    $this->phr_lst = $phr_lst;
                }
                log_debug('phrase_group->set_wrd_lst got ' . $this->wrd_lst->name());
                log_debug('phrase_group->set_wrd_lst got phrase ' . $this->phr_lst->name());
            }
        }
    }

    // load the triple list based on the triple id array
    private function set_lnk_lst()
    {
        if (isset($this->lnk_ids)) {
            log_debug('phrase_group->set_lnk_lst for "' . dsp_array($this->lnk_ids) . '"');

            // ignore double word entries
            $this->lnk_ids = array_unique($this->lnk_ids);

            if (count($this->lnk_ids) > 0) {
                // make sure that there is not time word
                // maybe not needed if the calling function has done this already
                $lnk_lst = new word_link_list;
                $lnk_lst->ids = $this->lnk_ids;
                $lnk_lst->usr = $this->usr;
                $lnk_lst->load();
                //$lnk_lst->ex_time();
                $this->lnk_lst = $lnk_lst;
                $this->lnk_ids = $lnk_lst->ids;
                // also fill the phrase list with the converted objects that are already loaded
                $phr_lst = $lnk_lst->phrase_lst();
                if (isset($this->phr_lst) and isset($phr_lst)) {
                    $this->phr_lst = $this->phr_lst->concat_unique($phr_lst);
                } else {
                    $this->phr_lst = $phr_lst;
                }
                log_debug('phrase_group->set_lnk_lst got ' . $this->lnk_lst->name());
                log_debug('phrase_group->set_wrd_lst got phrase ' . $this->phr_lst->name());
            }
        }
    }

    // create the wrd_id_txt based on the wrd_ids
    private function set_wrd_id_txt()
    {
        log_debug('phrase_group->set_wrd_id_txt for "' . dsp_array($this->wrd_ids) . '"');

        // make sure that the ids have always the same order
        asort($this->wrd_ids);

        $wrd_id_txt = dsp_array($this->wrd_ids);
        log_debug('phrase_group->set_wrd_id_txt test text "' . $wrd_id_txt . '"');

        if (strlen($wrd_id_txt) > 255) {
            log_err('Too many words assigned to one value ("' . $wrd_id_txt . '" is longer than the max database size of 255).', "phrase_group->set_wrd_id_txt");
        } else {
            $this->wrd_id_txt = dsp_array($this->wrd_ids);
        }
    }

    // create the lnk_id_txt based on the lnk_ids
    private function set_lnk_id_txt()
    {
        log_debug('phrase_group->set_lnk_id_txt for "' . implode(",", $this->lnk_ids) . '"');

        // make sure that the ids have always the same order
        asort($this->lnk_ids);

        $lnk_id_txt = implode(",", $this->lnk_ids);
        log_debug('phrase_group->set_lnk_id_txt test text "' . $lnk_id_txt . '"');

        if (strlen($lnk_id_txt) > 255) {
            log_err('Too many triples assigned to one value ("' . $lnk_id_txt . '" is longer than the db size of 255).', "phrase_group->set_lnk_id_txt");
        } else {
            $this->lnk_id_txt = implode(",", $this->lnk_ids);
        }
        log_debug('phrase_group->set_lnk_id_txt to "' . $this->lnk_id_txt . '"');
    }

    private function set_ids_from_wrd_or_lnk_lst()
    {
        $this->wrd_ids = array();
        $this->lnk_ids = array();
        $this->ids = array();
        if (isset($this->wrd_lst)) {
            log_debug('phrase_group->set_ids_from_wrd_or_lnk_lst wrd ids for ' . $this->wrd_lst->dsp_id());
            // reload the words if needed
            //$this->wrd_lst->load();
            if (count($this->wrd_lst->ids) > 0) {
                $wrd_lst = $this->wrd_lst;
                $wrd_lst->ex_time();
                $this->wrd_ids = $wrd_lst->ids;
                log_debug('phrase_group->set_ids_from_wrd_or_lnk_lst wrd ids ' . implode(",", $this->wrd_ids));
                // also fill the phrase list with the converted objects that are already loaded
                $phr_lst = $wrd_lst->phrase_lst();
                if (isset($this->phr_lst) and isset($phr_lst)) {
                    $this->phr_lst = $this->phr_lst->concat_unique($phr_lst);
                } else {
                    $this->phr_lst = $phr_lst;
                }
            }
        }
        if (isset($this->lnk_lst)) {
            log_debug('phrase_group->set_ids_from_wrd_or_lnk_lst lnk ids');
            // reload the words if needed
            //$this->lnk_lst->load();
            if (count($this->lnk_lst->ids) > 0) {
                $lnk_lst = $this->lnk_lst;
                //$lnk_lst->ex_time();
                $this->lnk_ids = $lnk_lst->ids;
                log_debug('phrase_group->set_ids_from_wrd_or_lnk_lst lnk ids ' . implode(",", $this->lnk_ids));
                // also fill the phrase list with the converted objects that are already loaded
                $phr_lst = $lnk_lst->phrase_lst();
                if (isset($this->phr_lst) and isset($phr_lst)) {
                    $this->phr_lst = $this->phr_lst->concat_unique($phr_lst);
                } else {
                    $this->phr_lst = $phr_lst;
                }
            }
        }
        $this->set_ids_from_wrd_or_lnk_ids();
    }

    // set ids based on the phrase list
    private function set_ids_from_phr_lst()
    {
        if (isset($this->phr_lst)
            and !isset($this->wrd_lst)
            and !isset($this->lnk_lst)) {
            log_debug('phrase_group->set_ids_from_phr_lst from ' . $this->phr_lst->dsp_id());
            // reload the phrases if needed
            if (count($this->phr_lst->ids()) > 0) {
                $this->phr_lst->load_by_ids($this->phr_lst->ids());
            }
            if (count($this->phr_lst->ids()) > 0) {
                $wrd_lst = $this->phr_lst->wrd_lst();
                $wrd_lst->ex_time();
                $this->wrd_ids = $wrd_lst->ids();

                $lnk_lst = $this->phr_lst->trp_lst();
                //$lnk_lst->ex_time();
                $this->lnk_ids = $lnk_lst->ids();
            }
        }
        $this->set_ids_from_wrd_or_lnk_ids();
    }

    // for building the where clause don't use the sf function to force the string format search
    private function set_lst_where(): string
    {
        log_debug('phrase_group->set_lst_where');
        $sql_where = '';
        if ($this->wrd_id_txt <> '' and $this->lnk_id_txt <> '') {
            $sql_where = "word_ids   = '" . $this->wrd_id_txt . "'
                AND triple_ids = '" . $this->lnk_id_txt . "'";
        } elseif ($this->wrd_id_txt <> '') {
            $sql_where = "word_ids   = '" . $this->wrd_id_txt . "'";
        } elseif ($this->lnk_id_txt <> '') {
            $sql_where = "triple_ids = '" . $this->lnk_id_txt . "'";
        }
        log_debug('phrase_group->set_lst_where -> ' . $sql_where);
        return $sql_where;
    }

    // this also excludes automatically any empty ids
    private function set_ids_to_lst_and_txt()
    {
        log_debug('phrase_group->set_ids_to_lst_and_txt ' . $this->dsp_id());
        $this->set_ids_to_wrd_or_lnk_ids();
        $this->set_wrd_lst();
        $this->set_lnk_lst();
        $this->set_wrd_id_txt();
        $this->set_lnk_id_txt();
    }

    // set all parameters based on the combined id list
    // used by the frontend
    private function load_by_ids()
    {
        log_debug('phrase_group->load_by_ids ' . $this->dsp_id());
        $sql_where = '';
        if (isset($this->ids)) {
            if (count($this->ids) > 0) {
                $this->set_ids_to_lst_and_txt();
                $sql_where = $this->set_lst_where();
            }
        }
        return $sql_where;
    }

    // set all parameters based on the separate id lists
    // used by the backend if the list object is not yet loaded
    private function load_by_wrd_or_lnk_ids()
    {
        log_debug('phrase_group->load_by_wrd_or_lnk_ids ' . $this->dsp_id());
        if (isset($this->wrd_ids) and isset($this->lnk_ids)) {
            if (count($this->wrd_ids) > 0 or count($this->lnk_ids) > 0) {
                $this->set_wrd_lst();
                $this->set_lnk_lst();
                $this->set_wrd_id_txt();
                $this->set_lnk_id_txt();
            }
        }
        $sql_where = $this->set_lst_where();
    }

    // set all parameters based on the word and triple list objects
    // use by the backend, because in the backend the list objects are probably already loaded
    private function load_by_wrd_or_lnk_lst()
    {
        log_debug('phrase_group->load_by_wrd_or_lnk_lst ' . $this->dsp_id());
        $sql_where = '';
        if (isset($this->wrd_lst) and isset($this->lnk_lst)) {
            if (count($this->wrd_lst) > 0 or count($this->lnk_lst) > 0) {
                $this->set_ids_from_wrd_or_lnk_lst();
                $this->set_wrd_id_txt();
                $this->set_lnk_id_txt();
                $sql_where = $this->set_lst_where();
            }
        }
        return $sql_where;
    }

    // set all parameters based on the phrase list objects
    private function load_by_phr_lst()
    {
        log_debug('phrase_group->load_by_phr_lst ' . $this->dsp_id());
        $sql_where = '';
        if (isset($this->phr_lst)) {
            if ($this->phr_lst->count() > 0) {
                $this->set_ids_from_phr_lst();
                $this->set_wrd_id_txt();
                $this->set_lnk_id_txt();
                $sql_where = $this->set_lst_where();
            }
        }
        return $sql_where;
    }

    // set all parameters based on the given setting
    private function load_by_selector($sql_where)
    {
        log_debug('phrase_group->load_by_selector ' . $this->dsp_id());
        if ($sql_where == '') {
            $sql_where = $this->load_by_ids();
        }
        if ($sql_where == '') {
            $sql_where = $this->load_by_wrd_or_lnk_ids();
        }
        if ($sql_where == '') {
            $sql_where = $this->load_by_phr_lst();
        }
        if ($sql_where == '') {
            $sql_where = $this->load_by_wrd_or_lnk_lst();
        }
        return $sql_where;
    }

    /**
     * load the word and triple objects based on the ids load from the database
     */
    function load_lst_old()
    {
        log_debug('phrase_group->load_lst');

        // load only if needed
        if ($this->wrd_id_txt <> '') {
            log_debug('phrase_group->load_lst words for "' . $this->wrd_id_txt . '"');
            if ($this->wrd_ids <> explode(",", $this->wrd_id_txt)
                or !isset($this->wrd_lst)) {
                $this->wrd_ids = explode(",", $this->wrd_id_txt);
                $wrd_lst = new word_list;
                $wrd_lst->ids = $this->wrd_ids;
                $wrd_lst->usr = $this->usr;
                $wrd_lst->load();
                $this->wrd_lst = $wrd_lst;
                log_debug('phrase_group->load_lst words (' . $this->wrd_lst->count() . ')');
                // also fill the phrase list with the converted objects that are already loaded
                $phr_lst = $wrd_lst->phrase_lst();
                if (isset($this->phr_lst) and isset($phr_lst)) {
                    $this->phr_lst = $this->phr_lst->concat_unique($phr_lst);
                } else {
                    $this->phr_lst = $phr_lst;
                }
            }
        }

        if ($this->lnk_id_txt <> '') {
            log_debug('phrase_group->load_lst triples for "' . $this->lnk_id_txt . '"');
            if ($this->lnk_ids <> explode(",", $this->lnk_id_txt)
                or !isset($this->lnk_lst)) {
                $this->lnk_ids = explode(",", $this->lnk_id_txt);
                $lnk_lst = new word_link_list;
                $lnk_lst->ids = $this->lnk_ids;
                $lnk_lst->usr = $this->usr;
                $lnk_lst->load();
                $this->lnk_lst = $lnk_lst;
                log_debug('phrase_group->load_lst triples (' . $this->lnk_lst->count() . ')');
                // also fill the phrase list with the converted objects that are already loaded
                $phr_lst = $lnk_lst->phrase_lst();
                if (isset($this->phr_lst) and isset($phr_lst)) {
                    $this->phr_lst = $this->phr_lst->concat_unique($phr_lst);
                } else {
                    $this->phr_lst = $phr_lst;
                }
            }
        }
        log_debug('phrase_group->load_lst ... done');
    }

    // internal function for testing the link for fast search
    function load_link_ids()
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

    // true if the current phrase group contains at least all phrases of the given $grp
    // e.g. $this ($val->grp) has the "ABB, Sales, million, CHF" and the table row ($grp) has "ABB, Sales" than this (value) can be used for this row
    function has_all_phrases_of($grp)
    {
        log_debug("phrase_group->has_all_phrases_of");
        $result = true;

        if (isset($grp->phr_lst)) {
            foreach ($grp->phr_lst->lst as $phr) {
                if (!in_array($phr->id, $this->ids)) {
                    log_debug('phrase_group->has_all_phrases_of -> "' . $phr->id . '" is missing in ' . implode(",", $this->ids));
                    $result = false;
                }
            }
        }

        return $result;
    }

    /*

    get functions - to load or create with one call

    */

    // get the word/triple group name (and create a new group if needed)
    // based on a string with the word and triple ids
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
            $result .= $this->load(); // TODO load twice should not be needed
        } else {
            log_debug('phrase_group->get save ' . $this->dsp_id());
            $this->load_by_selector('');
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

    // set the group id (and create a new group if needed)
    // ex grp_id that returns the id
    function get_id(): int
    {
        log_debug('phrase_group->get_id ' . $this->dsp_id());
        $this->get();
        return $this->id;
    }

    /**
     * create the sql statement
     */
    function get_by_wrd_lst_sql(sql_db $db_con, bool $get_name = false): string
    {
        $sql_name = 'phrase_group_by_';
        if ($this->id != 0) {
            $sql_name .= 'id';
        } elseif ($this->wrd_lst != null) {
            if (count($this->wrd_lst->lst) > 0) {
                $sql_name .= count($this->wrd_lst->lst) . 'word_id';
            }
        } else {
            log_err("Either the database ID (" . $this->id . ") or a word list and the user (" . $this->usr->id . ") must be set to load a phrase list.", "phrase_list->load");
        }

        $sql_from = '';
        $sql_from_prefix = '';
        $sql_where = '';
        if ($this->id != 0) {
            $sql_from .= 'phrase_groups ';
            $sql_where .= 'phrase_group_id = ' . $this->id;
        } elseif ($this->wrd_lst != null) {
            $pos = 1;
            $prev_pos = 1;
            $sql_from_prefix = 'l1.';
            foreach ($this->wrd_lst->lst as $wrd) {
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

    // get the best matching group for a word list
    // at the moment "best matching" is defined as the highest number of results
    private function get_by_wrd_lst()
    {

        global $db_con;
        $result = null;

        if (isset($this->wrd_lst)) {
            if ($this->wrd_lst->lst > 0) {

                $pos = 1;
                $prev_pos = 1;
                $sql_from = '';
                $sql_where = '';
                foreach ($this->wrd_lst->ids as $wrd_id) {
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
                $sql = "SELECT"." l1.phrase_group_id 
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
                        log_debug('phrase_group->get_by_wrd_lst ' . $result . ' found <' . $this->id . '> for ' . $this->wrd_lst->name() . ' and user ' . $this->usr->name);
                    } else {
                        log_warning('No group found for words ' . $this->wrd_lst->name() . '.', "phrase_group->get_by_wrd_lst");
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

    /*
    display functions
    */

    // return best possible id for this element mainly used for debugging
    function dsp_id(): string
    {
        $result = '';

        if ($this->name(0) <> '') {
            $result .= '"' . $this->name(0) . '" (' . $this->id . ')';
        } else {
            $result .= $this->id;
        }
        if ($this->grp_name <> '') {
            $result .= ' as "' . $this->grp_name . '"';
        }
        if ($result == '') {
            if (isset($this->phr_lst)) {
                $result .= ' for phrases ' . $this->phr_lst->dsp_id();
            } elseif ($this->ids != null) {
                $result .= ' for phrase ids ' . implode(",", $this->ids);
            }
        }
        if ($result == '') {
            if (isset($this->wrd_lst)) {
                $result .= ' for words ' . $this->wrd_lst->dsp_id();
            } elseif (count($this->wrd_ids) > 0) {
                $result .= ' for word ids ' . implode(",", $this->wrd_ids);
            } elseif ($this->wrd_id_txt <> '') {
                $result .= ' for word ids ' . implode(",", $this->wrd_id_txt);
            }
            if (isset($this->lnk_lst)) {
                $result .= ', triples ' . $this->lnk_lst->dsp_id();
            } elseif (count($this->lnk_ids) > 0) {
                $result .= ', triple ids ' . implode(",", $this->lnk_ids);
            } elseif ($this->lnk_id_txt <> '') {
                $result .= ', triple ids ' . implode(",", $this->lnk_id_txt);
            }
        }
        if (isset($this->usr)) {
            $result .= ' for user ' . $this->usr->id . ' (' . $this->usr->name . ')';
        }

        return $result;
    }

    // return a string with the group name
    function name()
    {
        $result = '';

        if ($this->grp_name <> '') {
            // use the user defined description
            $result = $this->grp_name;
        } else {
            // or use the standard generic description
            $name_lst = array();
            if (isset($this->wrd_lst)) {
                $name_lst = array_merge($name_lst, $this->wrd_lst->names());
            }
            if (isset($this->lnk_lst)) {
                $name_lst = array_merge($name_lst, $this->lnk_lst->names());
            }
            $result = implode(",", $name_lst);
        }

        return $result;
    }

    // return a list of the word and triple names
    function names()
    {
        log_debug('phrase_group->names');

        // if not yet done, load, the words and triple list
        $this->load_lst_old();

        $result = array();
        if (isset($this->wrd_lst)) {
            $result = array_merge($result, $this->wrd_lst->names());
        }
        if (isset($this->lnk_lst)) {
            $result = array_merge($result, $this->lnk_lst->names());
        }

        log_debug('phrase_group->names -> ' . implode(",", $result));
        return $result;
    }

    // return the first value related to the word lst
    // or an array with the value and the user_id if the result is user specific
    function value()
    {
        $val = new value($this->usr);
        $val->wrd_lst = $this;
        $val->load();

        log_debug('phrase_group->value ' . $val->wrd_lst->name() . ' for "' . $this->usr->name . '" is ' . $val->number);
        return $val;
    }

    // get the "best" value for the word list and scale it e.g. convert "2.1 mio" to "2'100'000"
    function value_scaled()
    {
        //zu_debug("phrase_group->value_scaled (".$this->name()." for ".$this->usr->name.")");

        $val = $this->value();

        // get all words related to the value id; in many cases this does not match with the value_words there are use to get the word: it may contains additional word ids
        if ($val->id > 0) {
            //zu_debug("phrase_group->value_scaled -> get word ids ".$this->name());
            $val->load_phrases();
            // switch on after value->scale is working fine
            //$val->number = $val->scale($val->wrd_lst);
        }

        return $val;
    }

    //
    function result($time_wrd_id)
    {
        log_debug("phrase_group->result (" . $this->id . ",time" . $time_wrd_id . ",u" . $this->usr->name . ")");

        global $db_con;
        $result = array();

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

    // create the generic group name (and update the database record if needed and possible)
    // returns the generic name if it has been saved to the database
    private function generic_name(): string
    {
        log_debug('phrase_group->generic_name');

        global $db_con;
        $result = '';

        // if not yet done, load, the words and triple list
        $this->load_lst_old();

        $word_name = '';
        if (isset($this->wrd_lst)) {
            $word_name = $this->wrd_lst->name();
            log_debug('phrase_group->generic_name word name ' . $word_name);
        }
        $triple_name = '';
        if (isset($this->lnk_lst)) {
            $triple_name = $this->lnk_lst->name();
            log_debug('phrase_group->generic_name triple name ' . $triple_name);
        }
        if ($word_name <> '' and $triple_name <> '') {
            $group_name = $word_name . ',' . $triple_name;
        } else {
            $group_name = $word_name . $triple_name;
        }

        // update the name if possible and needed
        if ($this->auto_name <> $group_name) {
            if ($this->id > 0) {
                // update the generic name in the database
                //$db_con = new mysql;
                $db_con->usr_id = $this->usr->id;
                $db_con->set_type(DB_TYPE_PHRASE_GROUP);
                if ($db_con->update($this->id, 'auto_description', $group_name)) {
                    $result = $group_name;
                }
                log_debug('phrase_group->generic_name updated to ' . $group_name);
            }
            $this->auto_name = $group_name;
        }
        log_debug('phrase_group->generic_name ... group name ' . $group_name);

        return $result;
    }

    // create the HTML code to select a phrase group be selecting a combination of words and triples
    private function selector()
    {
        $result = '';
        log_debug('phrase_group->selector for ' . $this->id . ' and user "' . $this->usr->name . '"');

        /*
        new function: load_main_type to load all word and phrase types with one query

        Allow to remember the view order of words and phrases

        the form should create a url with the ids in the view order
        -> this is converted by this class to word ids, triple ids for selecting the group and saving the view order and the time for the value selection

        Create a new group if needed without asking the user
    Create a new value if needed, but ask the user: abb sales of 46000, is still used by other users. Do you want to suggest the users to switch to abb revenues 4600? If yes, a request is created. If no, do you want to additional save abb revenues 4600 (and keep abb sales of 46000)? If no, nothing is saved and the form is shown again with a highlighted cancel or back button.

      update the link tables for fast selection

        */

        return $result;
    }


    /*

    save function - because the phrase group is a wrapper for a word and triple list the save function should not be called from outside this class

    */

    // save the user specific group name
    private function save()
    {
    }

    // create a new word group
    private function save_id()
    {
        log_debug('phrase_group->save_id ' . $this->dsp_id());

        global $db_con;

        if ($this->id <= 0) {
            $this->generic_name();

            // write new group
            if ($this->wrd_id_txt <> '' or $this->lnk_id_txt <> '') {
                //$db_con = new mysql;
                if ($this->usr == null) {
                    log_err('User missing when saving phrase group');
                } else {
                    $db_con->usr_id = $this->usr->id;
                }
                $db_con->set_type(DB_TYPE_PHRASE_GROUP);

                $wrd_id_txt = implode(',', $this->phr_lst->wrd_ids());
                if (strlen($wrd_id_txt) > 255) {
                    log_err('Too many words assigned to one value ("' . $wrd_id_txt . '" is longer than the max database size of 255).', "phrase_group->set_wrd_id_txt");
                    $wrd_id_txt = zu_str_left($wrd_id_txt, 255);
                }
                $trp_id_txt = implode(',', $this->phr_lst->trp_ids());
                if (strlen($trp_id_txt) > 255) {
                    log_err('Too many triple assigned to one value ("' . $wrd_id_txt . '" is longer than the max database size of 255).', "phrase_group->set_wrd_id_txt");
                    $trp_id_txt = zu_str_left($trp_id_txt, 255);
                }

                $this->id = $db_con->insert(array('word_ids', 'triple_ids', 'auto_description'),
                    array($wrd_id_txt, $trp_id_txt, $this->auto_name));
            } else {
                log_err('Either a word (' . $this->wrd_id_txt . ') or triple (' . $this->lnk_id_txt . ')  must be set to create a group for ' . $this->dsp_id() . '.', 'phrase_group->save_id');
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
            $field_name = 'word_id';
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
            log_debug('phrase_group->save_phr_links -> should have word ids ' . implode(",", $this->wrd_ids));
            $add_ids = array_diff($this->wrd_ids, $db_ids);
            $del_ids = array_diff($db_ids, $this->wrd_ids);
        } else {
            log_debug('phrase_group->save_phr_links -> should have triple ids ' . implode(",", $this->lnk_ids));
            $add_ids = array_diff($this->lnk_ids, $db_ids);
            $del_ids = array_diff($db_ids, $this->lnk_ids);
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
            log_debug('phrase_group->save_phr_links -> del ' . implode(",", $del_ids) . '');
            $del_nbr = 0;
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
     * create the phrase list based in the word and triple list if needed
     * to be removed by using only the phrase list
     */
    function sync_lists()
    {
        if ($this->phr_lst == null) {
            $this->phr_lst = new phrase_list($this->usr);
            if ($this->wrd_lst != null) {
                if ($this->wrd_lst->lst != null) {
                    foreach ($this->wrd_lst->lst as $wrd) {
                        if ($wrd != null) {
                            $this->phr_lst->lst[] = $wrd->phrase();
                        }
                    }
                }
            }
            if ($this->lnk_lst != null) {
                if ($this->lnk_lst->lst != null) {
                    foreach ($this->lnk_lst->lst as $lnk) {
                        if ($lnk != null) {
                            $this->phr_lst->lst[] = $lnk->phrase();
                        }
                    }
                }
            }
        }
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

}