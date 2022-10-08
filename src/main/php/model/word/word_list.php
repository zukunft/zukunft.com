<?php

/*

    word_list.php - a list of word objects
    -------------

    actually only used for phrase splitting; in most other cases phrase_list is used

    TODO: check the consistence usage of the parameter $back
    TODO: add bool $incl_is to include all words that are of the category id e.g. $ids contains the id for "company" than "ABB" should be included, if "ABB is a Company" is true
    TODO: add bool $incl_alias to include all alias words that are of the ids

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

use cfg\phrase_type;
use api\word_list_api;
use html\word_dsp;
use html\word_list_dsp;

class word_list
{
    // array of the loaded word objects
    // (key is at the moment the database id, but it looks like this has no advantages,
    // so a normal 0 to n order could have more advantages)
    public array $lst;
    public user $usr;    // the user object of the person for whom the word list is loaded, so to say the viewer

    /*
     * construct and map
     */

    /**
     * always set the user because a word list is always user specific
     * @param user $usr the user who requested to see this word list
     */
    function __construct(user $usr)
    {
        $this->lst = array();
        $this->usr = $usr;
    }

    /*
     * casting objects
     */

    /**
     * @return word_list_api the word list object with the display interface functions
     */
    function api_obj(): word_list_api
    {
        $api_obj = new word_list_api();
        foreach ($this->lst as $wrd) {
            $api_obj->add($wrd->api_obj());
        }
        return $api_obj;
    }

    /**
     * @return word_list_dsp the word list object with the display interface functions
     */
    function dsp_obj(): word_list_dsp
    {
        $dsp_obj = new word_list_dsp();
        foreach ($this->lst as $wrd) {
            $dsp_obj->add($wrd->dsp_obj());
        }
        return $dsp_obj;
    }

    /*
     * load functions
     */

    /**
     * set the SQL query parameters to load a list of words
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql_db $db_con): sql_par
    {
        $db_con->set_type(sql_db::TBL_WORD);
        $qp = new sql_par(self::class);
        $db_con->set_name($qp->name); // assign incomplete name to force the usage of the user as a parameter
        $db_con->set_usr($this->usr->id);
        $db_con->set_fields(word::FLD_NAMES);
        $db_con->set_usr_fields(word::FLD_NAMES_USR);
        $db_con->set_usr_num_fields(word::FLD_NAMES_NUM_USR);
        $db_con->set_order_text(sql_db::STD_TBL . '.' . $db_con->name_sql_esc(word::FLD_VALUES) . ' DESC, ' . word::FLD_NAME);
        return $qp;
    }

    /**
     * set the SQL query parameters to load a list of words by the ids
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param array $wrd_ids a list of int values with the word ids
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_ids(sql_db $db_con, array $wrd_ids): sql_par
    {
        $qp = $this->load_sql($db_con);
        if (count($wrd_ids) > 0) {
            $qp->name .= 'ids';
            $db_con->set_name($qp->name);
            $db_con->add_par_in_int($wrd_ids);
            $qp->sql = $db_con->select_by_field(word::FLD_ID);
        } else {
            $qp->name = '';
        }
        $qp->par = $db_con->get_par();
        return $qp;
    }

    /**
     * set the SQL query parameters to load a list of words by the names
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param array $wrd_names a list of strings with the word names
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_names(sql_db $db_con, array $wrd_names): sql_par
    {
        $qp = $this->load_sql($db_con);
        if (count($wrd_names) > 0) {
            $qp->name .= 'names';
            $db_con->set_name($qp->name);
            $db_con->add_par_in_txt($wrd_names);
            $qp->sql = $db_con->select_by_field(word::FLD_NAME);
        } else {
            $qp->name = '';
        }
        $qp->par = $db_con->get_par();
        return $qp;
    }

    /**
     * set the SQL query parameters to load a list of words by the phrase group id
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param int $grp_id the id of the phrase group
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_grp_id(sql_db $db_con, int $grp_id): sql_par
    {
        $qp = $this->load_sql($db_con);
        if ($grp_id > 0) {
            $qp->name .= 'group';
            $db_con->set_name($qp->name);
            $db_con->add_par(sql_db::PAR_INT, $grp_id);
            $table_name = $db_con->get_table_name(sql_db::TBL_PHRASE_GROUP_WORD_LINK);
            $sql_where = sql_db::STD_TBL . '.' . word::FLD_ID . ' IN ( SELECT ' . word::FLD_ID . ' 
                                    FROM ' . $table_name . '
                                    WHERE ' . phrase_group::FLD_ID . ' = ' . $db_con->par_name() . ')';
            $db_con->set_where_text($sql_where);
            $qp->sql = $db_con->select_by_id();
        } else {
            $qp->name = '';
        }
        $qp->par = $db_con->get_par();
        return $qp;
    }

    /**
     * set the SQL query parameters to load a list of words by the type
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param int $type_id the id of the word type
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_type(sql_db $db_con, int $type_id): sql_par
    {
        $qp = $this->load_sql($db_con);
        if ($type_id > 0) {
            $qp->name .= 'type';
            $db_con->set_name($qp->name);
            $db_con->add_par(sql_db::PAR_INT, $type_id);
            $qp->sql = $db_con->select_by_field(word::FLD_TYPE);
        } else {
            $qp->name = '';
        }
        $qp->par = $db_con->get_par();
        return $qp;
    }

    /**
     * set the SQL query parameters to load a list of words by a word pattern
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param string $word_pattern the id of the word type
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_pattern(sql_db $db_con, string $word_pattern = ''): sql_par
    {
        $qp = $this->load_sql($db_con);
        if ($word_pattern !=  '') {
            $qp->name .= 'pattern';
            $db_con->set_name($qp->name);
            $db_con->add_name_pattern($word_pattern);
            $qp->sql = $db_con->select_by_field(word::FLD_NAME);
        } else {
            $qp->name = '';
        }
        $qp->par = $db_con->get_par();
        return $qp;
    }

    /**
     * create the sql statement to select the related words
     * the relation can be narrowed with a verb id
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param int $verb_id to select only words linked with this verb
     * @param string $direction to define the link direction
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_linked_words(sql_db $db_con, int $verb_id, string $direction): sql_par
    {
        $qp = $this->load_sql($db_con);
        $sql_where = '';
        $join_field = '';
        if (count($this->lst) <= 0) {
            log_warning('The word list is empty, so nothing could be found', self::class . "->load_sql_by_linked_type");
            $qp->name = '';
        } else {
            if ($db_con->db_type == sql_db::POSTGRES) {
                $sql_in = ' = ANY (';
            } else {
                $sql_in = ' IN (';
            }
            if ($direction == word_select_direction::UP) {
                $qp->name .= 'parents';
                $db_con->add_par_in_int($this->ids());
                $sql_where = sql_db::LNK_TBL . '.' . word_link::FLD_FROM . $sql_in . $db_con->par_name() . ')';
                $join_field = word_link::FLD_TO;
            } elseif ($direction == word_select_direction::DOWN) {
                $qp->name .= 'children';
                $db_con->add_par_in_int($this->ids());
                $sql_where = sql_db::LNK_TBL . '.' . word_link::FLD_TO . $sql_in . $db_con->par_name() . ')';
                $join_field = word_link::FLD_FROM;
            } else {
                log_err('Unknown direction ' . $direction);
            }
            // verbs can have a negative id for the reverse selection
            if ($verb_id <> 0) {
                $db_con->set_join_fields(
                    array(verb::FLD_ID),
                    sql_db::TBL_TRIPLE,
                    word::FLD_ID,
                    $join_field,
                    verb::FLD_ID,
                    $verb_id);
                $qp->name .= '_verb_select';
            } else {
                $db_con->set_join_fields(
                    array(verb::FLD_ID),
                    sql_db::TBL_TRIPLE,
                    word::FLD_ID,
                    $join_field);
            }
            $db_con->set_name($qp->name);
            $db_con->set_where_text($sql_where);
            $qp->sql = $db_con->select_by_id();
            $qp->par = $db_con->get_par();
        }

        return $qp;
    }

    /**
     * set the SQL query parameters to load all changes of one user on words
     * TODO build a general user change selection
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param user $usr the user for whom the changes should be loaded
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_user_changes_sql(sql_db $db_con, user $usr): sql_par
    {
        $qp = $this->load_sql($db_con);
        if ($usr->id > 0) {
            $qp->name .= 'user_changes';
            $db_con->set_name($qp->name);
            $qp->sql = $db_con->select_by_field(word::FLD_ID);
        } else {
            $qp->name = '';
        }
        $qp->par = $db_con->get_par();
        return $qp;
    }

    /**
     * load this list of words
     * @param sql_par $qp the SQL statement, the unique name of the SQL statement and the parameter list
     * @return bool true if at least one word found
     */
    function load(sql_par $qp): bool
    {
        global $db_con;
        $result = false;

        if ($qp->name == '') {
            log_err('The query name cannot be created to load a ' . self::class, self::class . '->load');
        } else {
            $db_rows = $db_con->get($qp);
            if ($db_rows != null) {
                foreach ($db_rows as $db_row) {
                    $wrd = new word($this->usr);
                    $wrd->row_mapper($db_row);
                    $this->lst[] = $wrd;
                    $result = true;
                }
            }
        }

        return $result;
    }

    /**
     * load a list of words by the ids
     * @param array $wrd_ids a list of int values with the word ids
     * @return bool true if at least one word found
     */
    function load_by_ids(array $wrd_ids): bool
    {
        global $db_con;
        $qp = $this->load_sql_by_ids($db_con, $wrd_ids);
        return $this->load($qp);
    }

    /**
     * load a list of words by the names
     * @param array $wrd_names a named object used for selection e.g. a word type
     * @return bool true if at least one word found
     */
    function load_by_names(array $wrd_names): bool
    {
        global $db_con;
        $qp = $this->load_sql_by_names($db_con, $wrd_names);
        return $this->load($qp);
    }

    /**
     * load a list of words by the phrase group id
     * TODO needs to be checked if really needed
     *
     * @param int $grp_id the id of the phrase group
     * @return bool true if at least one word found
     */
    function load_by_grp_id(int $grp_id): bool
    {
        global $db_con;
        $qp = $this->load_sql_by_grp_id($db_con, $grp_id);
        return $this->load($qp);
    }

    /**
     * load a list of words by the word type id
     *
     * @param int $type_id the id of the word type
     * @return bool true if at least one word found
     */
    function load_by_type(int $type_id): bool
    {
        global $db_con;
        $qp = $this->load_sql_by_type($db_con, $type_id);
        return $this->load($qp);
    }

    /**
     * load a list of words by a part of the word name
     *
     * @param string $pattern the text part that should be used to select the words
     * @return bool true if at least one word found
     * TODO filter by type while loading e.g. to exclude formula words
     */
    function load_by_pattern(string $pattern): bool
    {
        global $db_con;
        $qp = $this->load_sql_pattern($db_con, $pattern);
        return $this->load($qp);
    }

    /**
     * add the direct linked words to the list
     * and remember which words have be added
     *
     * @param int $verb_id to select only words linked with this verb
     * @param string $direction to define the link direction
     * @return word_list with only the new added words
     */
    function load_linked_words(int $verb_id, string $direction): word_list
    {

        global $db_con;
        $additional_added = new word_list($this->usr); // list of the added words with this call

        $qp = $this->load_sql_linked_words($db_con, $verb_id, $direction);
        if ($qp->name == '') {
            log_warning('The word list is empty, so nothing could be found', self::class . '->load_linked_words');
        } else {
            $db_con->usr_id = $this->usr->id;
            $db_wrd_lst = $db_con->get($qp);
            if ($db_wrd_lst) {
                log_debug(self::class . '->add_by_type -> got ' . dsp_count($db_wrd_lst));
                foreach ($db_wrd_lst as $db_wrd) {
                    if (is_null($db_wrd[user_sandbox::FLD_EXCLUDED]) or $db_wrd[user_sandbox::FLD_EXCLUDED] == 0) {
                        if ($db_wrd[word::FLD_ID] > 0 and !in_array($db_wrd[word::FLD_ID], $this->ids())) {
                            $new_word = new word($this->usr);
                            $new_word->row_mapper($db_wrd);
                            $additional_added->add($new_word);
                            log_debug(self::class . '->add_by_type -> added "' . $new_word->dsp_id() . '" for verb (' . $db_wrd[verb::FLD_ID] . ')');
                        }
                    }
                }
                log_debug(self::class . '->add_by_type -> added (' . $additional_added->dsp_id() . ')');
            }
        }
        return $additional_added;
    }


    /*
      tree building function
      ----------------------

      Overview for words, triples and phrases and it's lists

               children and            parents return the direct parents and children   without the original phrase(s)
          foaf_children and       foaf_parents return the    all parents and children   without the original phrase(s)
                    are and                 is return the    all parents and children including the original phrase(s) for the specific verb "is a"
               contains                        return the    all             children including the original phrase(s) for the specific verb "contains"
                                    is part of return the    all parents                without the original phrase(s) for the specific verb "contains"
                   next and              prior return the direct parents and children   without the original phrase(s) for the specific verb "follows"
            followed_by and        follower_of return the    all parents and children   without the original phrase(s) for the specific verb "follows"
      differentiated_by and differentiator_for return the    all parents and children   without the original phrase(s) for the specific verb "can_contain"

      Samples

      the        parents of  "ABB" can be "public limited company"
      the   foaf_parents of  "ABB" can be "public limited company" and "company"
                    "is" of  "ABB" can be "public limited company" and "company" and "ABB" (used to get all related values)
      the       children for "company" can include "public limited company"
      the  foaf_children for "company" can include "public limited company" and "ABB"
                   "are" for "company" can include "public limited company" and "ABB" and "company" (used to get all related values)

            "contains" for "balance sheet" is "assets" and "liabilities" and "company" and "balance sheet" (used to get all related values)
          "is part of" for "assets" is "balance sheet" but not "assets"

                "next" for "2016" is "2017"
               "prior" for "2017" is "2016"
      "is followed by" for "2016" is "2017" and "2018"
      "is follower of" for "2016" is "2015" and "2014"

      "wind energy" and "energy" "can be differentiator for" "sector"
                        "sector" "can be differentiated_by"  "wind energy" and "energy"

      if "wind energy" "is part of" "energy"

    */

    /**
     * build one level of a word tree
     * @param int $level 1 if the parents of the original words are added
     * @param word_list $added_wrd_lst list of the added word during the foaf selection process
     * @param int $verb_id id of the verb that is used to select the parents
     * @param string $direction to select if the parents or children should be selected - "up" to select the parents
     * @param int $max_level the max $level that should be used for the selection
     * @return word_list the accumulated list of added words
     */
    private function foaf_level(int $level, word_list $added_wrd_lst, int $verb_id, string $direction, int $max_level): word_list
    {
        log_debug(self::class . '->foaf_level (type id ' . $verb_id . ' level ' . $level . ' ' . $direction . ' added ' . $added_wrd_lst->name() . ')');
        if ($max_level > 0) {
            $max_loops = $max_level;
        } else {
            $max_loops = MAX_RECURSIVE;
        }
        $loops = 0;
        $additional_added = clone $this;
        do {
            $loops = $loops + 1;
            // load all linked words
            $additional_added = $additional_added->load_linked_words($verb_id, $direction);
            // get the words not added before
            $additional_added->diff($added_wrd_lst);
            // remember the added words
            $added_wrd_lst->merge($additional_added);

            if ($loops >= MAX_RECURSIVE) {
                log_fatal("max number (" . $loops . ") of loops for word " . $verb_id . " reached.", "word_list->tree_up_level");
            }
        } while (!empty($additional_added->lst) and $loops < $max_loops);
        log_debug(self::class . '->foaf_level done');
        return $added_wrd_lst;
    }

    /**
     * returns a list of words, that characterises the given word e.g. for the "ABB Ltd." it will return "Company" if the verb_id is "is"
     *
     * @param int $verb_id id of the verb that is used to select the parents
     * @returns word_list the accumulated list of added words
     */
    function foaf_parents(int $verb_id): word_list
    {
        log_debug(self::class . '->foaf_parents (type id ' . $verb_id . ')');
        $level = 0;
        $added_wrd_lst = new word_list($this->usr); // list of the added word ids
        $added_wrd_lst = $this->foaf_level($level, $added_wrd_lst, $verb_id, word_select_direction::UP, 0);

        log_debug(self::class . '->foaf_parents -> (' . $added_wrd_lst->name() . ')');
        return $added_wrd_lst;
    }

    /**
     * similar to foaf_parents, but for only one level
     * ex foaf_parent_step
     * @param int $level is the number of levels that should be looked into
     * @param int $verb_id id of the verb that is used to select the parents
     * @returns word_list the accumulated list of added words
     */
    function parents(int $verb_id, int $level): word_list
    {
        log_debug(self::class . '->parents(' . $verb_id . ')');
        $added_wrd_lst = new word_list($this->usr); // list of the added word ids
        $added_wrd_lst = $this->foaf_level($level, $added_wrd_lst, $verb_id, word_select_direction::UP, $level);

        log_debug(self::class . '->parents -> (' . $added_wrd_lst->name() . ')');
        return $added_wrd_lst;
    }

    /**
     * similar to foaf_parent, but the other way round e.g. for "Companies" it will return "ABB Ltd." and others if the link type is "are"
     * ex foaf_child
     * @param int $verb_id id of the verb that is used to select the parents
     * @returns word_list the accumulated list of added words
     */
    function foaf_children(int $verb_id): word_list
    {
        log_debug(self::class . '->foaf_children type ' . $verb_id);
        $level = 0;
        $added_wrd_lst = new word_list($this->usr); // list of the added word ids
        $added_wrd_lst = $this->foaf_level($level, $added_wrd_lst, $verb_id, word_select_direction::DOWN, 0);

        log_debug(self::class . '->foaf_children -> (' . $added_wrd_lst->name() . ')');
        return $added_wrd_lst;
    }

    /**
     * similar to foaf_child, but for only one level
     * ex foaf_child_step
     * @param int $verb_id id of the verb that is used to select the parents
     * @param int $level is the number of levels that should be looked into
     * @returns word_list the accumulated list of added words
     */
    function children(int $verb_id, int $level): word_list
    {
        log_debug(self::class . '->children type ' . $verb_id);
        $added_wrd_lst = new word_list($this->usr); // list of the added word ids
        $added_wrd_lst = $this->foaf_level($level, $added_wrd_lst, $verb_id, word_select_direction::DOWN, $level);

        log_debug(self::class . '->children -> (' . $added_wrd_lst->name() . ')');
        return $added_wrd_lst;
    }

    /**
     * returns a list of words that are related to this word list
     * e.g. for "ABB" and "Daimler" it will return "Company", but not "ABB"
     * @returns word_list with the added words
     */
    function is(): word_list
    {
        $wrd_lst = $this->foaf_parents(cl(db_cl::VERB, verb::IS_A));
        log_debug(self::class . '->is -> (' . $this->dsp_id() . ' is ' . $wrd_lst->name() . ')');
        return $wrd_lst;
    }

    /**
     * returns a list of words that are related to this word list
     * e.g. for "Company" it will return "ABB" and "Daimler" and "Company"
     * e.g. to get all related values
     * @returns word_list with the added words
     */
    function are(): word_list
    {
        log_debug(self::class . '->are for ' . $this->dsp_id());
        $wrd_lst = $this->foaf_children(cl(db_cl::VERB, verb::IS_A));
        $wrd_lst->merge($this);
        log_debug(self::class . '->are -> (' . $this->dsp_id() . ' are ' . $wrd_lst->name() . ')');
        return $wrd_lst;
    }

    /**
     * returns a list of words that are related to this word list
     * @returns word_list with the added words
     */
    function contains(): word_list
    {
        $wrd_lst = $this->foaf_children(cl(db_cl::VERB, verb::IS_PART_OF));
        $wrd_lst->merge($this);
        log_debug(self::class . '->contains -> (' . $this->dsp_id() . ' contains ' . $wrd_lst->name() . ')');
        return $wrd_lst;
    }

    /**
     * makes sure that all combinations of "are" and "contains" are included
     * @returns word_list with the added words
     */
    function are_and_contains(): word_list
    {
        log_debug(self::class . '->are_and_contains for ' . $this->dsp_id());

        // this first time get all related items
        $wrd_lst = clone $this;
        $wrd_lst = $wrd_lst->are();
        $wrd_lst = $wrd_lst->contains();
        $added_lst = clone $wrd_lst;
        $added_lst->diff($this);
        if (count($added_lst->lst) > 0) {
            log_debug(self::class . '->are_and_contains -> add ' . $added_lst->name() . ' to ' . $wrd_lst->name());
        }
        // ... and after that get only for the new
        if (count($added_lst->lst) > 0) {
            $loops = 0;
            log_debug(self::class . '->are_and_contains -> added ' . $added_lst->name() . ' to ' . $wrd_lst->name());
            do {
                $next_lst = clone $added_lst;
                $next_lst = $next_lst->are();
                $next_lst = $next_lst->contains();
                $next_lst->diff($added_lst);
                $added_lst->merge($next_lst);
                if (count($next_lst->lst) > 0) {
                    log_debug(self::class . '->are_and_contains -> add ' . $next_lst->name() . ' to ' . $wrd_lst->name());
                }
                $wrd_lst->merge($added_lst);
                $loops++;
            } while (count($next_lst->lst) > 0 and $loops < MAX_LOOP);
        }
        log_debug(self::class . '->are_and_contains -> ' . $this->dsp_id() . ' are_and_contains ' . $wrd_lst->name());
        return $wrd_lst;
    }

    /**
     * add all potential differentiator words of the word lst
     * e.g. get "energy" for "sector"
     *
     * @returns word_list with the added words
     */
    function differentiators(): word_list
    {
        $wrd_lst = $this->foaf_parents(cl(db_cl::VERB, verb::CAN_CONTAIN));
        $wrd_lst->merge($this);
        return $wrd_lst;
    }

    /**
     * same as differentiators, but including the subtypes
     * e.g. get "energy" and "wind energy" for "sector" if "wind energy" is part of "energy"
     * @returns word_list with the added words
     */
    function differentiators_all(): word_list
    {
        // this first time get all related items
        // parents and not children because the verb is "can contain", but here the question is for "can be split by"
        $wrd_lst = $this->foaf_parents(cl(db_cl::VERB, verb::CAN_CONTAIN));
        return $wrd_lst->are_and_contains();
    }

    /**
     * similar to differentiators, but only a filtered list of differentiators is viewed to increase speed
     * @param word_list $filter_lst with the words used to get the differentiators
     * @returns word_list with the added and filtered words
     */
    function differentiators_filtered(word_list $filter_lst): word_list
    {
        $result = $this->differentiators_all();
        return $result->filter($filter_lst);
    }

    /**
     * look at a word list and remove the general word,
     * if there is a more specific word also part of the list
     * e.g. remove "Country", but keep "Switzerland"
     *
     * @returns word_list with the specific words
     */
    function keep_only_specific(): word_list
    {
        $parents = new word_list($this->usr);
        foreach ($this->lst as $wrd) {
            $phr_lst = $wrd->parents();
            $wrd_lst = $phr_lst->wrd_lst_all();
            $parents->merge($wrd_lst);
        }
        $result = clone $this;
        $result->diff($parents);
        return $result;
    }

    /*
     * function that changes the list e.g. adding and filter
     */

    /**
     * add one word to the word list, but only if it is not yet part of the word list
     * @param word $wrd_to_add the word object that should be added
     */
    function add(word $wrd_to_add)
    {
        log_debug(self::class . '->add ' . $wrd_to_add->dsp_id());
        if (!in_array($wrd_to_add->id, $this->ids())) {
            if ($wrd_to_add->id > 0) {
                $this->lst[] = $wrd_to_add;
            }
        }
    }

    /**
     * add one word by the id to the word list, but only if it is not yet part of the word list
     * @param int $wrd_id_to_add id of the word object that should be added
     */
    function add_id(int $wrd_id_to_add)
    {
        log_debug(self::class . '->add_id (' . $wrd_id_to_add . ')');
        if (!in_array($wrd_id_to_add, $this->ids())) {
            if ($wrd_id_to_add > 0) {
                $wrd_to_add = new word($this->usr);
                $wrd_to_add->id = $wrd_id_to_add;
                $wrd_to_add->load();

                $this->add($wrd_to_add);
            }
        }
    }

    /**
     * add one word to the word list defined by the word name
     * @param string $wrd_name_to_add name of the word object that should be added
     */
    function add_name(string $wrd_name_to_add)
    {
        log_debug(self::class . '->add_name (' . $wrd_name_to_add . ')');
        if (is_null($this->usr->id)) {
            log_err("The user must be set.", "word_list->add_name");
        } else {
            $wrd_to_add = new word($this->usr);
            $wrd_to_add->name = $wrd_name_to_add;
            $wrd_to_add->load();

            $this->add($wrd_to_add);
        }
    }

    /**
     * merge as a function, because the array_merge does not create an object
     * @param word_list $new_wrd_lst with the words that should be added
     */
    function merge(word_list $new_wrd_lst)
    {
        log_debug(self::class . '->merge ' . $new_wrd_lst->name() . ' to ' . $this->dsp_id() . '"');
        foreach ($new_wrd_lst->lst as $new_wrd) {
            log_debug(self::class . '->merge add ' . $new_wrd->name . ' (' . $new_wrd->id . ')');
            $this->add($new_wrd);
        }
    }

    /**
     * diff as a function, because it seems the array_diff does not work for an object list
     *
     * e.g. if the $this word list is "January, February, March, April, May, June, Juli, August, September, October, November, December"
     * and the $del_wrd_lst is "May, June, Juli, August"
     * than $this->diff should be "January, February, March, April, September, October, November, December" and save to eat huîtres
     *
     * @param word_list $del_wrd_lst is the list of words that should be removed from this list object
     */
    function diff(word_list $del_wrd_lst): void
    {
        log_debug(self::class . '->diff of ' . $del_wrd_lst->dsp_id() . ' and ' . $this->dsp_id());

        // check and adjust the parameters
        if (!isset($del_wrd_lst)) {
            log_err('Phrases to delete are missing.', 'word_list->diff');
        }

        if (count($this->lst) > 0) {
            $result = array();
            $lst_ids = $del_wrd_lst->ids();
            foreach ($this->lst as $wrd) {
                if (!in_array($wrd->id, $lst_ids)) {
                    $result[] = $wrd;
                }
            }
            $this->lst = $result;
        }

        log_debug(self::class . '->diff -> ' . $this->dsp_id());
    }

    /**
     * similar to diff, but using an id array to exclude instead of a word list object
     *
     * @param array $del_wrd_ids is the list of word ids that should be removed from this list object
     */
    function diff_by_ids(array $del_wrd_ids)
    {
        foreach ($del_wrd_ids as $del_wrd_id) {
            if ($del_wrd_id > 0) {
                if (in_array($del_wrd_id, $this->ids())) {
                    $del_pos = array_search($del_wrd_id, $this->ids());
                    log_debug(self::class . '->diff_by_ids -> exclude (' . $this->lst[$del_pos]->name . ')');
                    unset ($this->lst[$del_pos]);
                }
            }
        }
        log_debug(self::class . '->diff_by_ids -> ' . $this->dsp_id() . ' (' . dsp_array($this->ids()) . ')');
    }

    /**
     * Exclude all time words out of the list of words
     */
    function ex_time()
    {
        $del_wrd_lst = $this->time_lst();
        $this->diff($del_wrd_lst);
        log_debug(self::class . '->ex_time -> ' . $this->dsp_id());
    }

    /**
     * Exclude all measure words out of the list of words
     */
    function ex_measure()
    {
        $del_wrd_lst = $this->measure_lst();
        $this->diff($del_wrd_lst);
        log_debug(self::class . '->ex_measure -> ' . $this->dsp_id());
    }

    /**
     * Exclude all scaling words out of the list of words
     */
    function ex_scaling()
    {
        $del_wrd_lst = $this->scaling_lst();
        $this->diff($del_wrd_lst);
        log_debug(self::class . '->ex_scaling -> ' . $this->dsp_id());
    }

    /**
     * remove the percent words from this word list
     */
    function ex_percent()
    {
        $del_wrd_lst = $this->percent_lst();
        $this->diff($del_wrd_lst);
        log_debug(self::class . '->ex_percent -> ' . $this->dsp_id());
    }

    /**
     * sort a word list by name
     */
    function wlsort(): array
    {
        log_debug(self::class . '->wlsort (' . $this->dsp_id() . ' and user ' . $this->usr->name . ')');
        $name_lst = array();
        $result = array();
        $pos = 0;
        foreach ($this->lst as $wrd) {
            $name_lst[$pos] = $wrd->name;
            $pos++;
        }
        asort($name_lst);
        log_debug(self::class . '->wlsort names sorted "' . implode('","', $name_lst) . '" (' . dsp_array(array_keys($name_lst)) . ')');
        foreach (array_keys($name_lst) as $sorted_id) {
            log_debug(self::class . '->wlsort get ' . $sorted_id);
            $wrd_to_add = $this->lst[$sorted_id];
            log_debug(self::class . '->wlsort got ' . $wrd_to_add->name);
            $result[] = $wrd_to_add;
        }
        // check
        if (count($this->lst) <> count($result)) {
            log_err("Sorting changed the number of words from " . dsp_count($this->lst) . " to " . dsp_count($result) . ".", "word_list->wlsort");
        } else {
            $this->lst = $result;
        }
        log_debug(self::class . '->wlsort sorted ' . $this->dsp_id());
        return $result;
    }

    /*
     * filter functions
     */

    /**
     * filters a word list
     *
     * e.g. out of "2014", "2015", "2016", "2017"
     * with the filter "2016", "2017","2018"
     * the result is "2016", "2017"
     *
     * @param word_list $filter_lst with the words that should be removed
     * @returns word_list with only the remaining words
     */
    function filter(word_list $filter_lst): word_list
    {
        log_debug(self::class . '->filter of ' . $filter_lst->dsp_id() . ' and ' . $this->dsp_id());
        $result = clone $this;

        // check and adjust the parameters
        if ($filter_lst->count() <= 0) {
            log_err('Phrases to delete are missing.', 'word_list->filter');
        }
        if (get_class($filter_lst) <> word_list::class) {
            log_err(get_class($filter_lst) . ' cannot be used to delete words.', 'word_list->filter');
        }

        if (count($result->lst) > 0) {
            $wrd_lst = array();
            $lst_ids = $filter_lst->ids();
            foreach ($result->lst as $wrd) {
                if (in_array($wrd->id, $lst_ids)) {
                    $wrd_lst[] = $wrd;
                }
            }
            $result->lst = $wrd_lst;
            log_debug(self::class . '->filter -> ' . $result->dsp_id() . ')');
        }

        return $result;
    }

    /**
     * filter the time words out of the list of words
     * @return word_list with the time words (all)
     */
    function time_lst(): word_list
    {
        log_debug(self::class . '->time_lst for words "' . $this->dsp_id() . '"');

        $result = new word_list($this->usr);
        $time_type = cl(db_cl::WORD_TYPE, phrase_type::TIME);
        // loop over the word ids and add only the time ids to the result array
        foreach ($this->lst as $wrd) {
            if ($wrd->type_id() == $time_type) {
                $result->add($wrd);
                log_debug(self::class . '->time_lst -> found (' . $wrd->name . ')');
            } else {
                log_debug(self::class . '->time_lst -> not found (' . $wrd->name . ')');
            }
        }
        if (count($result->lst) < 10) {
            log_debug(self::class . '->time_lst -> total found ' . $result->dsp_id());
        } else {
            log_debug(self::class . '->time_lst -> total found: ' . dsp_count($result->lst) . ' ');
        }
        return $result;
    }

    /**
     * create a useful list of time phrases
     * @return word_list with the "useful" time words
     */
    function time_useful(): word_list
    {
        log_debug(self::class . '->time_useful for ' . $this->dsp_id());

        //$result = zu_lst_to_flat_lst($word_lst);
        $result = clone $this;
        $result->wlsort();
        //$result = $word_lst;
        //a sort($result);
        // sort
        //print_r($word_lst);

        // get the most often time type e.g. years if the list contains more than 5 years
        //$type_most_used = zut_time_type_most_used ($word_lst);

        // if nothing special is defined try to select 20 % outlook to the future
        // get the latest time without estimate
        // check the number of none estimate results
        // if the hist is longer than it should be defined the start word
        // fill from the start word the default number of words


        log_debug(self::class . '->time_useful -> ' . $result->dsp_id());
        return $result;
    }

    /**
     * filter the measure words out of the list of words
     * @return word_list with the measure words
     */
    function measure_lst(): word_list
    {
        log_debug(self::class . '->measure_lst(' . $this->dsp_id() . ')');

        $result = new word_list($this->usr);
        $measure_type = cl(db_cl::WORD_TYPE, phrase_type::MEASURE);
        // loop over the word ids and add only the time ids to the result array
        foreach ($this->lst as $wrd) {
            if ($wrd->type_id == $measure_type) {
                $result->lst[] = $wrd;
                log_debug(self::class . '->measure_lst -> found (' . $wrd->name . ')');
            } else {
                log_debug(self::class . '->measure_lst -> (' . $wrd->name . ') is not measure');
            }
        }
        log_debug(self::class . '->measure_lst -> (' . dsp_count($result->lst) . ')');
        return $result;
    }

    /**
     * filter the scaling words out of the list of words
     * @return word_list with the scaling words
     */
    function scaling_lst(): word_list
    {
        log_debug(self::class . '->scaling_lst(' . $this->dsp_id() . ')');

        $result = new word_list($this->usr);
        $scale_type = cl(db_cl::WORD_TYPE, phrase_type::SCALING);
        $scale_hidden_type = cl(db_cl::WORD_TYPE, phrase_type::SCALING_HIDDEN);
        // loop over the word ids and add only the time ids to the result array
        foreach ($this->lst as $wrd) {
            if ($wrd->type_id == $scale_type or $wrd->type_id == $scale_hidden_type) {
                $wrd->usr = $this->usr; // review: should not be needed
                $result->lst[] = $wrd;
                log_debug(self::class . '->scaling_lst -> found (' . $wrd->name . ')');
            } else {
                log_debug(self::class . '->scaling_lst -> not found (' . $wrd->name . ')');
            }
        }
        log_debug(self::class . '->scaling_lst -> (' . dsp_count($result->ids()) . ')');
        return $result;
    }

    /**
     * filter the percent words out of the list of words
     * @return word_list with the percent words
     */
    function percent_lst(): word_list
    {
        log_debug(self::class . '->percent_lst(' . $this->dsp_id() . ')');

        $result = new word_list($this->usr);
        $percent_type = cl(db_cl::WORD_TYPE, phrase_type::PERCENT);
        // loop over the word ids and add only the time ids to the result array
        foreach ($this->lst as $wrd) {
            if ($wrd->type_id == $percent_type) {
                $result->lst[] = $wrd;
                log_debug(self::class . '->percent_lst -> found (' . $wrd->name . ')');
            } else {
                log_debug(self::class . '->percent_lst -> (' . $wrd->name . ') is not percent');
            }
        }
        log_debug(self::class . '->percent_lst -> (' . dsp_count($result->ids()) . ')');
        return $result;
    }

    /*
     * im- and export functions
     */

    /**
     * create a list of word objects for the export
     * @return array with the reduced word objects that can be used to create a JSON message
     */
    function export_obj(): array
    {
        $exp_words = array();
        foreach ($this->lst as $wrd) {
            if (get_class($wrd) == word::class or get_class($wrd) == word_dsp::class) {
                if ($wrd->has_cfg()) {
                    $exp_words[] = $wrd->export_obj();
                }
            } else {
                log_err('The function wrd_lst->export_obj returns ' . $wrd->dsp_id() . ', which is ' . get_class($wrd) . ', but not a word.', 'export->get');
            }
        }
        return $exp_words;
    }


    /*
     * extract functions
     */

    /**
     * @return array list of the word ids
     */
    function ids(): array
    {
        $result = array();
        foreach ($this->lst as $wrd) {
            if ($wrd->id > 0) {
                $result[] = $wrd->id;
            }
        }
        return $result;
    }

    /**
     * @return string with the list of the word ids as a SQL compatible text
     */
    function ids_txt(): string
    {
        return sql_array($this->ids());
    }

    /**
     * @return int the number of phrases in this list
     */
    function count(): int
    {
        return count($this->lst);
    }

    /*
     *  display functions
     */

    /**
     * @return string with the best possible id for this element mainly used for debugging
     */
    function dsp_id(): string
    {
        $id = $this->ids_txt();
        if ($this->name() <> '""') {
            $result = $this->name() . ' (' . $id . ')';
        } else {
            $result = $id;
        }
        if (isset($this->usr)) {
            $result .= ' for user ' . $this->usr->id . ' (' . $this->usr->name . ')';
        }

        return $result;
    }

    /**
     * this function is called from dsp_id, so no call of another function is allowed
     * @return string with all names of the list
     */
    function name(): string
    {
        global $debug;
        $result = '';

        if ($debug > 10) {
            $result .= '"' . implode('","', $this->names()) . '"';
        } else {
            $result .= '"' . implode('","', array_slice($this->names(), 0, 7));
            if (count($this->names()) > 8) {
                $result .= ' ... total ' . dsp_count($this->lst);
            }
            $result .= '"';
        }
        return $result;
    }

    /**
     * this function is called from dsp_id, so no call of another function is allowed
     * @return array a list of the word names
     */
    function names(): array
    {
        $result = array();
        foreach ($this->lst as $wrd) {
            if (isset($wrd)) {
                $result[] = $wrd->name;
            }
        }
        return $result;
    }

    /*
     *  convert functions
     */

    /**
     * get the best matching phrase group
     */
    function get_grp(): ?phrase_group
    {
        log_debug(self::class . '->get_grp');

        $grp = new phrase_group($this->usr);

        // get or create the group
        if (count($this->ids()) <= 0) {
            log_err('Cannot create phrase group for an empty list.', 'word_list->get_grp');
        } else {
            $grp = new phrase_group($this->usr);
            $grp->load_by_ids((new trm_ids($this->ids())));
        }

        /*
        TODO check if a new group is not created
        $result = $grp->get_id();
        if ($result->id > 0) {
          zu_debug('word_list->get_grp <'.$result->id.'> for "'.$this->name().'" and user '.$this->usr->name);
        } else {
          zu_debug('word_list->get_grp create for "'.implode(",",$grp->wrd_lst->names()).'" ('.implode(",",$grp->wrd_lst->ids()).') and user '.$grp->usr->name);
          $result = $grp->get_id();
          if ($result->id > 0) {
            zu_debug('word_list->get_grp created <'.$result->id.'> for "'.$this->name().'" and user '.$this->usr->name);
          }
        }
        */
        log_debug(self::class . '->phrase_lst -> done (' . $grp->id . ')');
        return $grp;
    }

    /**
     * convert the word list object into a phrase list object
     */
    function phrase_lst(): phrase_list
    {
        log_debug(self::class . '->phrase_lst ' . $this->dsp_id());
        $phr_lst = new phrase_list($this->usr);
        foreach ($this->lst as $wrd) {
            if (get_class($wrd) == word::class or get_class($wrd) == word_dsp::class) {
                $phr_lst->lst[] = $wrd->phrase();
            } elseif (get_class($wrd) == phrase::class) {
                $phr_lst->lst[] = $wrd;
            } else {
                log_err('unexpected object type ' . get_class($wrd));
            }
        }
        $phr_lst->id_lst();
        log_debug(self::class . '->phrase_lst -> done (' . dsp_count($phr_lst->lst) . ')');
        return $phr_lst;
    }

    /**
     * @return value the first (or later "best") value related to the word lst
     * or an array with the value and the user_id if the result is user specific
     */
    function value(): value
    {
        $val = new value($this->usr);
        $phr_lst = $this->phrase_lst();
        $time_phr = $phr_lst->time_useful();
        $phr_lst->ex_time();
        $phr_grp = new phrase_group($this->usr);
        $phr_grp->load_by_lst($phr_lst);
        $val->grp = $phr_grp;
        $val->time_phr = $time_phr;
        $val->load();

        log_debug(self::class . '->value "' . $val->name . '" for "' . $this->usr->name . '" is ' . $val->number);
        return $val;
    }

    /**
     * @return value get first (or later "best") value related to the word lst
     * and scale it e.g. convert "2.1 mio" to "2'100'000"
     */
    function value_scaled(): value
    {
        log_debug("word_list->value_scaled " . $this->dsp_id() . " for " . $this->usr->name . ".");

        $val = $this->value();

        // get all words related to the value id; in many cases this does not match with the value_words there are used to get the word: it may contain additional word ids
        if ($val->id > 0) {
            log_debug("word_list->value_scaled -> get word " . $this->name());
            //$val->load_phrases();
            // switch on after value->scale is working fine
            //$val->number = $val->scale($val->wrd_lst);
        }

        return $val;
    }

    /*
     *  info functions
     */

    /**
     * @return bool true if the word is part of the word list
     */
    function does_contain($wrd_to_check): bool
    {
        $result = false;

        foreach ($this->lst as $wrd) {
            if ($wrd->id == $wrd_to_check->id) {
                $result = true;
            }
        }

        return $result;
    }

    /**
     * @return bool true if a word lst contains a time word
     */
    function has_time(): bool
    {
        log_debug(self::class . '->has_time for ' . $this->dsp_id());
        $result = false;
        // loop over the word ids and add only the time ids to the result array
        foreach ($this->lst as $wrd) {
            log_debug(self::class . '->has_time -> check (' . $wrd->name . ')');
            if ($result == false) {
                if ($wrd->is_time()) {
                    $result = true;
                }
            }
        }
        log_debug(self::class . '->has_time -> (' . zu_dsp_bool($result) . ')');
        return $result;
    }

    /**
     * @return bool true if a word lst contains a measure word
     */
    function has_measure(): bool
    {
        $result = false;
        // loop over the word ids and add only the time ids to the result array
        foreach ($this->lst as $wrd) {
            log_debug(self::class . '->has_measure -> check (' . $wrd->name . ')');
            if ($result == false) {
                if ($wrd->is_measure()) {
                    $result = true;
                }
            }
        }
        log_debug(self::class . '->has_measure -> (' . zu_dsp_bool($result) . ')');
        return $result;
    }

    /**
     * @return bool true if a word lst contains a scaling word
     */
    function has_scaling(): bool
    {
        $result = false;
        // loop over the word ids and add only the time ids to the result array
        foreach ($this->lst as $wrd) {
            log_debug(self::class . '->has_scaling -> check (' . $wrd->name . ')');
            if ($result == false) {
                if ($wrd->is_scaling()) {
                    $result = true;
                }
            }
        }
        log_debug(self::class . '->has_scaling -> (' . zu_dsp_bool($result) . ')');
        return $result;
    }

    /**
     * @return bool true if a word lst contains a percent scaling word,
     * which is used for a predefined formatting of the value
     */
    function has_percent(): bool
    {
        $result = false;
        // loop over the word ids and add only the time ids to the result array
        foreach ($this->lst as $wrd) {
            log_debug(self::class . '->has_percent -> check (' . $wrd->name . ')');
            if ($result == false) {
                if ($wrd->is_percent()) {
                    $result = true;
                }
            }
        }
        log_debug(self::class . '->has_percent -> (' . zu_dsp_bool($result) . ')');
        return $result;
    }

    /*
     * functions to select linked objects
     */

    /**
     * get a list of all views used to the words
     * @return array of views linked to this word list
     */
    function view_lst(): array
    {
        $result = array();
        log_debug(self::class . '->view_lst');

        foreach ($this->lst as $wrd) {
            $wrd_dsp = $wrd->dsp_obj();
            // TODO review $view = $wrd_dsp->view();
            $view = $wrd->view();
            if (isset($view)) {
                $is_in_list = false;
                foreach ($result as $check_view) {
                    if ($check_view->id == $view->id) {
                        $is_in_list = true;
                    }
                }
                if (!$is_in_list) {
                    log_debug(self::class . '->view_lst add ' . $view->dsp_id());
                    $result[] = $view;
                }
            }
        }

        log_debug(self::class . '->view_lst done got ' . dsp_count($result));
        return $result;
    }

    /*
     * select functions - predefined data retrieval
     */

    /**
     * @return word the last time word of the word list
     */
    function max_time(): word
    {
        log_debug(self::class . '->max_time (' . $this->dsp_id() . ' and user ' . $this->usr->name . ')');
        $max_wrd = new word($this->usr);
        if (count($this->lst) > 0) {
            foreach ($this->lst as $wrd) {
                // to be replaced by "is following"
                if ($wrd->name > $max_wrd->name) {
                    log_debug(self::class . '->max_time -> select (' . $wrd->name . ' instead of ' . $max_wrd->name . ')');
                    $max_wrd = clone $wrd;
                }
            }
        }
        return $max_wrd;
    }

    /**
     * get the time of the last value related to a word and assigned to a word list
     * @return word|null a time word (or phrase?)
     */
    function max_val_time(): ?word
    {
        log_debug(self::class . '->max_val_time ' . $this->dsp_id() . ' and user ' . $this->usr->name . ')');
        $wrd = null;

        // load the list of all value related to the word list
        $val_lst = new value_list($this->usr);
        $val_lst->phr_lst = $this->phrase_lst();
        $val_lst->load_by_phr_lst();
        log_debug(self::class . '->max_val_time ... ' . dsp_count($val_lst->lst) . ' values for ' . $this->dsp_id());

        $time_ids = array();
        foreach ($val_lst->lst as $val) {
            $val->load_phrases();
            if (isset($val->time_phr)) {
                log_debug(self::class . '->max_val_time ... value (' . $val->number . ' @ ' . $val->time_phr->name . ')');
                if ($val->time_phr->id > 0) {
                    if (!in_array($val->time_phr->id, $time_ids)) {
                        $time_ids[] = $val->time_phr->id;
                        log_debug(self::class . '->max_val_time ... add word id (' . $val->time_phr->id . ')');
                    }
                }
            }
        }

        $time_lst = new word_list($this->usr);
        if (count($time_ids) > 0) {
            $time_lst->load_by_ids($time_ids);
            $wrd = $time_lst->max_time();
        }

        /*
        // get all values related to the selecting word, because this is probably strongest selection and to save time reduce the number of records asap
        $val = New value;
        $val->wrd_lst = $this;
        $val->usr = $this->usr;
        $val->load_by_wrd_lst();
        $value_lst = array();
        $value_lst[$val->id] = $val->number;
        zu_debug('word_list->max_val_time -> ('.implode(",",$value_lst).')');

        if (sizeof($value_lst) > 0) {

          // get all words related to the value list
          $all_word_lst = zu_sql_value_lst_words($value_lst, $this->usr->id);

          // get the time words
          $time_lst = zut_time_lst($all_word_lst);

          // get the most useful (last) time words (replace by a "followed by" sorted list
          ar sort($time_lst);
          $time_keys = array_keys($time_lst);
          $wrd_id = $time_keys[0];
          $wrd = New word_dsp;
          if ($wrd_id > 0) {
            $wrd->id = $wrd_id;
            $wrd->usr = $this->usr;
            $wrd->load();
          }
        }
        */
        if ($wrd != null) {
            log_debug(self::class . '->max_val_time ... done (' . $wrd->name . ')');
        }
        return $wrd;
    }

    /**
     * get the most useful time for the given list
     * so either the last time from the word list
     * or the time of the last "real" (reported) value for the word list
     *
     * always returns a phrase to avoid converting in the calling function
     * @return phrase|null a time phrase
     */
    function assume_time(): ?phrase
    {
        log_debug(self::class . '->assume_time for ' . $this->dsp_id());
        $result = null;
        $phr = null;

        if ($this->has_time()) {
            // get the last time from the word list
            $time_phr_lst = $this->time_lst();
            // shortcut, replace with a most_useful function
            foreach ($time_phr_lst->lst as $time_wrd) {
                if (is_null($phr)) {
                    $phr = $time_wrd;
                    $phr->usr = $this->usr;
                } else {
                    log_warning("The word list contains more time word than supported by the program.", "word_list->assume_time");
                }
            }
            log_debug('time ' . $phr->name . ' assumed for ' . $this->name());
        } else {
            // get the time of the last "real" (reported) value for the word list
            $wrd_max_time = $this->max_val_time();
            if ($wrd_max_time != null) {
                $phr = $wrd_max_time->phrase();
            }
        }

        if ($phr != null) {
            log_debug(self::class . '->assume_time -> time used "' . $phr->name . '" (' . $phr->id . ')');
            if (get_class($phr) == word::class or get_class($phr) == word_dsp::class) {
                $result = $phr->phrase();
            } else {
                $result = $phr;
            }
        } else {
            log_debug(self::class . '->assume_time -> no time found');
        }
        return $result;
    }

}

/**
 * helper class
 */
class word_select_direction
{
    const UP = 'up';     // to select the parents
    const DOWN = 'down'; // to select the children
}