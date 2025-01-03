<?php

/*

    model/word/word_list.php - a list of word objects
    ------------------------

    actually only used for phrase splitting; in most other cases phrase_list is used

    TODO: check the consistence usage of the parameter $back
    TODO: add bool $incl_is to include all words that are of the category id e.g. $ids contains the id for "company" than "ABB" should be included, if "ABB is a Company" is true
    TODO: add bool $incl_alias to include all alias words that are of the ids
    TODO: look at a word list and remove the general word, if there is a more specific word also part of the list
          e.g. remove "Country", but keep "Switzerland"

    The main sections of this object are
    - construct and map: including the mapping of the db row to this word object
    - cast:              create an api object and set the vars from an api json
    - load:              database access object (DAO) functions
    - sql:               to create sql statments e.g. for load word from the sql database
    - tree building      create foaf trees
    - im- and export:    create an export object and set the vars from an import object
    - modify:            change potentially all object and all variables of this list with one function call
    - filter:            filter this list
    - convert:           more complex cast


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

    Copyright (c) 1995-2023 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace cfg\word;

include_once MODEL_SANDBOX_PATH . 'sandbox_list_named.php';
include_once API_WORD_PATH . 'word_list.php';
include_once DB_PATH . 'sql_creator.php';
include_once DB_PATH . 'sql_db.php';
include_once DB_PATH . 'sql_par.php';
include_once DB_PATH . 'sql_par_type.php';
include_once MODEL_GROUP_PATH . 'group.php';
include_once MODEL_GROUP_PATH . 'group_id.php';
include_once MODEL_HELPER_PATH . 'combine_named.php';
include_once MODEL_PHRASE_PATH . 'phr_ids.php';
include_once MODEL_PHRASE_PATH . 'phrase.php';
include_once MODEL_PHRASE_PATH . 'phrase_list.php';
include_once MODEL_PHRASE_PATH . 'term_list.php';
include_once MODEL_SANDBOX_PATH . 'sandbox.php';
include_once MODEL_SANDBOX_PATH . 'sandbox_link_named.php';
include_once MODEL_SANDBOX_PATH . 'sandbox_named.php';
include_once MODEL_USER_PATH . 'user.php';
include_once MODEL_USER_PATH . 'user_message.php';
include_once MODEL_VALUE_PATH . 'value.php';
include_once MODEL_VALUE_PATH . 'value_list.php';
include_once MODEL_VERB_PATH . 'verb.php';
include_once WEB_WORD_PATH . 'word.php';
include_once WEB_WORD_PATH . 'word_list.php';
include_once SHARED_ENUM_PATH . 'foaf_direction.php';
include_once SHARED_TYPES_PATH . 'phrase_type.php';
include_once SHARED_TYPES_PATH . 'verbs.php';
include_once SHARED_PATH . 'library.php';

use api\word\word_list as word_list_api;
use cfg\db\sql_creator;
use cfg\db\sql_db;
use cfg\db\sql_par;
use cfg\db\sql_par_type;
use cfg\group\group;
use cfg\group\group_id;
use cfg\helper\combine_named;
use cfg\phrase\phr_ids;
use cfg\phrase\phrase;
use cfg\phrase\phrase_list;
use cfg\phrase\term_list;
use cfg\sandbox\sandbox;
use cfg\sandbox\sandbox_link_named;
use cfg\sandbox\sandbox_list_named;
use cfg\sandbox\sandbox_named;
use cfg\user\user;
use cfg\user\user_message;
use cfg\value\value;
use cfg\value\value_list;
use cfg\verb\verb;
use html\word\word as word_dsp;
use html\word\word_list as word_list_dsp;
use shared\types\phrase_type as phrase_type_shared;
use shared\enum\foaf_direction;
use shared\library;
use shared\types\verbs;

class word_list extends sandbox_list_named
{
    // $lst of base_list is the array of the loaded word objects
    // (key is at the moment the database id, but it looks like this has no advantages,
    // so a normal 0 to n order could have more advantages)
    // $usr of sandbox list is the user object of the person for whom the word list is loaded, so to say the viewer


    /*
     * construct and map
     */

    /**
     * fill the word list based on a database records
     * actually just add the single word object to the parent function
     *
     * @param array $db_rows is an array of an array with the database values
     * @param bool $load_all force to include also the excluded phrases e.g. for admins
     * @return bool true if at least one formula link has been added
     */
    protected function rows_mapper(array $db_rows, bool $load_all = false): bool
    {
        return parent::rows_mapper_obj(new word($this->user()), $db_rows, $load_all);
    }


    /*
     * cast
     */

    /**
     * @return word_list_api the word list object with the display interface functions
     */
    function api_obj(): word_list_api
    {
        $api_obj = new word_list_api();
        foreach ($this->lst() as $wrd) {
            $api_obj->add($wrd->api_obj());
        }
        return $api_obj;
    }

    /**
     * @returns string the api json message for the object as a string
     */
    function api_json(): string
    {
        return $this->api_obj()->get_json();
    }

    /**
     * @return word_list_dsp the word list object with the display interface functions
     */
    function dsp_obj(): word_list_dsp
    {
        $dsp_obj = new word_list_dsp();
        foreach ($this->lst() as $wrd) {
            $wrd_dsp = new word_dsp($wrd->api_json());
            $dsp_obj->add($wrd_dsp);
        }
        return $dsp_obj;
    }


    /*
     * load
     */

    // TODO add a list of word where the user has changed the standard

    /**
     * load words with the given pattern
     *
     * @param string $pattern the text part that should be used to select the words
     * @return bool true if at least one word has been loaded
     * TODO filter by type while loading e.g. to exclude formula words
     */
    function load_like(string $pattern): bool
    {
        global $db_con;
        $qp = $this->load_sql_like($db_con->sql_creator(), $pattern);
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
        $qp = $this->load_sql_by_names($db_con->sql_creator(), $wrd_names);
        return $this->load($qp);
    }

    /**
     * load a list of words by the ids
     * @param array $wrd_ids a list of int values with the word ids
     * @return bool true if at least one word found
     */
    function load_by_ids(array $wrd_ids): bool
    {
        global $db_con;
        $qp = $this->load_sql_by_ids($db_con->sql_creator(), $wrd_ids);
        return $this->load($qp);
    }

    /**
     * load a list of word names
     * @param string $pattern the pattern to filter the words
     * @param int $limit the number of rows to return
     * @param int $offset jump over these number of pages
     * @return bool true if at least one word found
     */
    function load_names(string $pattern = '', int $limit = 0, int $offset = 0): bool
    {
        return parent::load_sbx_names(new word($this->user()), $pattern, $limit, $offset);
    }

    /**
     * load a list of words by the phrase group id
     * TODO needs to be checked if really needed
     *
     * @param int|string $grp_id the id of the phrase group
     * @return bool true if at least one word found
     */
    function load_by_grp_id(int|string $grp_id): bool
    {
        $grp_id_obj = new group_id();
        $ids = $grp_id_obj->get_array($grp_id);
        $phr_ids_obj = new phr_ids($ids);
        $wrd_ids = $phr_ids_obj->wrd_ids();
        return $this->load_by_ids($wrd_ids);
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
        $qp = $this->load_sql_by_type($db_con->sql_creator(), $type_id);
        return $this->load($qp);
    }


    /*
     * sql
     */

    /**
     * add formula word filter to
     * the SQL statement to load only the word id and name
     *
     * @param sql_creator $sc with the target db_type set
     * @param sandbox_named|sandbox_link_named|combine_named $sbx the single child object
     * @param string $pattern the pattern to filter the words
     * @param int $limit the number of rows to return
     * @param int $offset jump over these number of pages
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_names(
        sql_creator                                    $sc,
        sandbox_named|sandbox_link_named|combine_named $sbx,
        string                                         $pattern = '',
        int                                            $limit = 0,
        int                                            $offset = 0
    ): sql_par
    {
        global $phr_typ_cac;

        $qp = $this->load_sql_names_pre($sc, $sbx, $pattern, $limit, $offset);

        $sc->add_where(phrase::FLD_TYPE, $phr_typ_cac->id(phrase_type_shared::FORMULA_LINK), sql_par_type::CONST_NOT);

        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * set the SQL query parameters to load a list of words
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name extension to make the query name unique
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql_creator $sc, string $query_name = ''): sql_par
    {
        $sc->set_class(word::class);
        $qp = new sql_par(self::class);
        $qp->name .= $query_name;
        $sc->set_name($qp->name);
        $sc->set_usr($this->user()->id());
        $sc->set_fields(word::FLD_NAMES);
        $sc->set_usr_fields(word::FLD_NAMES_USR);
        $sc->set_usr_num_fields(word::FLD_NAMES_NUM_USR);
        $sc->set_order_text(sql_db::STD_TBL . '.' . $sc->name_sql_esc(word::FLD_VALUES) . ' DESC, '
            . word::FLD_NAME);
        return $qp;
    }

    /**
     * set the SQL query parameters to load a list of words by the ids
     * used also for load by group because the ids can be extracted directly from the group id
     *
     * @param sql_creator $sc with the target db_type set
     * @param array $wrd_ids a list of int values with the word ids
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_ids(sql_creator $sc, array $wrd_ids): sql_par
    {
        $qp = $this->load_sql($sc, 'ids');
        if (count($wrd_ids) > 0) {
            $sc->add_where(word::FLD_ID, $wrd_ids);
            $qp->sql = $sc->sql();
        } else {
            $qp->name = '';
        }
        $qp->par = $sc->get_par();
        return $qp;
    }

    /**
     * set the SQL query parameters to load a list of words by the names
     * @param sql_creator $sc with the target db_type set
     * @param array $wrd_names a list of strings with the word names
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_names(sql_creator $sc, array $wrd_names): sql_par
    {
        $qp = $this->load_sql($sc, 'names');
        if (count($wrd_names) > 0) {
            $sc->add_where(word::FLD_NAME, $wrd_names, sql_par_type::TEXT_LIST);
            $qp->sql = $sc->sql();
        } else {
            $qp->name = '';
        }
        $qp->par = $sc->get_par();
        return $qp;
    }

    /**
     * set the SQL query parameters to load a list of words by the type
     * @param sql_creator $sc with the target db_type set
     * @param int $type_id the id of the word type
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_type(sql_creator $sc, int $type_id): sql_par
    {
        $qp = $this->load_sql($sc, 'type');
        if ($type_id > 0) {
            $sc->add_where(phrase::FLD_TYPE, $type_id);
            $qp->sql = $sc->sql();
        } else {
            $qp->name = '';
        }
        $qp->par = $sc->get_par();
        return $qp;
    }

    /**
     * set the SQL query parameters to load a list of words by a word pattern
     * @param sql_creator $sc with the target db_type set
     * @param string $pattern the text part that should be used to select the words
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_like(sql_creator $sc, string $pattern = ''): sql_par
    {
        $qp = $this->load_sql($sc, 'name_like');
        if ($pattern != '') {
            $sc->add_where(word::FLD_NAME, $pattern, sql_par_type::LIKE_R);
            $qp->sql = $sc->sql();
        } else {
            $qp->name = '';
        }
        $qp->par = $sc->get_par();
        return $qp;
    }

    /**
     * create the sql statement to select the related words
     * the relation can be narrowed with a verb id
     *
     * @param sql_creator $sc with the target db_type set
     * @param verb|null $vrb if set to select only words linked with this verb
     * @param foaf_direction $direction to define the link direction
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_linked_words(sql_creator $sc, ?verb $vrb, foaf_direction $direction): sql_par
    {
        $qp = $this->load_sql($sc);
        $join_field = '';
        if ($this->count() <= 0) {
            log_warning('The word list is empty, so nothing could be found', self::class . "->load_sql_by_linked_type");
            $qp->name = '';
        } else {
            if ($direction == foaf_direction::UP) {
                $qp->name .= 'parents';
                $sc->add_where(triple::FLD_FROM, $this->ids(), sql_par_type::INT_LIST, sql_db::LNK_TBL);
                $join_field = triple::FLD_TO;
            } elseif ($direction == foaf_direction::DOWN) {
                $qp->name .= 'children';
                $sc->add_where(triple::FLD_TO, $this->ids(), sql_par_type::INT_LIST, sql_db::LNK_TBL);
                $join_field = triple::FLD_FROM;
            } else {
                log_err('Unknown direction ' . $direction->value);
            }
            $sc->set_join_fields(
                array(verb::FLD_ID),
                triple::class,
                word::FLD_ID,
                $join_field);
            // verbs can have a negative id for the reverse selection
            if ($vrb != null) {
                $qp->name .= '_verb_select';
                $sc->add_where(verb::FLD_ID, $vrb->id(), null, sql_db::LNK_TBL);
            }
            $sc->set_name($qp->name);
            $qp->sql = $sc->sql();
            $qp->par = $sc->get_par();
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
        $qp = $this->load_sql($db_con->sql_creator(), 'user_changes');
        if ($usr->id() > 0) {
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
     * @param bool $load_all force to include also the excluded words e.g. for admins
     * @return bool true if at least one word found
     */
    function load(sql_par $qp, bool $load_all = false): bool
    {
        global $db_con;
        $result = false;

        if ($qp->name == '') {
            log_err('The query name cannot be created to load a ' . self::class, self::class . '->load');
        } else {
            $db_rows = $db_con->get($qp);
            if ($db_rows != null) {
                foreach ($db_rows as $db_row) {
                    $wrd = new word($this->user());
                    $wrd->row_mapper_sandbox($db_row);
                    $this->add_obj($wrd);
                    $result = true;
                }
            }
        }

        return $result;
    }

    /**
     * add the direct linked words to the list
     * and remember which words have be added
     *
     * @param verb|null $vrb if set to select only words linked with this verb
     * @param foaf_direction $direction to define the link direction
     * @return word_list with only the new added words
     */
    function load_linked_words(?verb $vrb, foaf_direction $direction): word_list
    {

        global $db_con;
        $lib = new library();
        $additional_added = new word_list($this->user()); // list of the added words with this call

        $qp = $this->load_sql_linked_words($db_con->sql_creator(), $vrb, $direction);
        if ($qp->name == '') {
            log_warning('The word list is empty, so nothing could be found', self::class . '->load_linked_words');
        } else {
            $db_con->usr_id = $this->user()->id();
            $db_wrd_lst = $db_con->get($qp);
            if ($db_wrd_lst) {
                log_debug('got ' . $lib->dsp_count($db_wrd_lst));
                foreach ($db_wrd_lst as $db_wrd) {
                    if (is_null($db_wrd[sandbox::FLD_EXCLUDED]) or $db_wrd[sandbox::FLD_EXCLUDED] == 0) {
                        if ($db_wrd[word::FLD_ID] > 0 and !in_array($db_wrd[word::FLD_ID], $this->ids())) {
                            $new_word = new word($this->user());
                            $new_word->row_mapper_sandbox($db_wrd);
                            $additional_added->add($new_word);
                            log_debug('added "' . $new_word->dsp_id() . '" for verb (' . $db_wrd[verb::FLD_ID] . ')');
                        }
                    }
                }
                log_debug('added (' . $additional_added->dsp_id() . ')');
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
     * @param verb|null $vrb id of the verb that is used to select the parents
     * @param foaf_direction $direction to select if the parents or children should be selected - "up" to select the parents
     * @param int $max_level the max $level that should be used for the selection
     * @return word_list the accumulated list of added words
     */
    private function foaf_level(
        int            $level,
        word_list      $added_wrd_lst,
        ?verb          $vrb,
        foaf_direction $direction,
        int            $max_level = 0): word_list
    {
        $log_msg = 'foaf_level ';
        if ($vrb != null) {
            log_debug('verb ' . $vrb->dsp_id() . ' ');
        }
        $log_msg .= 'level ' . $level . ' ' . $direction->value . ' added ' . $added_wrd_lst->name();
        log_debug($log_msg);
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
            $additional_added = $additional_added->load_linked_words($vrb, $direction);
            // get the words not added before
            $additional_added->diff($added_wrd_lst);
            // remember the added words
            $added_wrd_lst->merge($additional_added);

            if ($loops >= MAX_RECURSIVE) {
                log_fatal("max number (" . $loops . ") of loops reached.", "word_list->foaf_level");
            }
        } while (!empty($additional_added->lst()) and $loops < $max_loops);
        log_debug('->foaf_level done');
        return $added_wrd_lst;
    }

    /**
     * returns a list of words, that characterises the given word e.g. for the "ABB Ltd." it will return "Company" if the verb_id is "is"
     *
     * @param verb|null $vrb id of the verb that is used to select the parents
     * @returns word_list the accumulated list of added words
     */
    function foaf_parents(?verb $vrb): word_list
    {
        $level = 0;
        $added_wrd_lst = new word_list($this->user()); // list of the added word ids
        $added_wrd_lst = $this->foaf_level($level, $added_wrd_lst, $vrb, foaf_direction::UP);

        log_debug($added_wrd_lst->dsp_id());
        return $added_wrd_lst;
    }

    /**
     * similar to foaf_parents, but for only one level
     * ex foaf_parent_step
     * @param verb|null $vrb if set to filter the children by the relation type
     * @param int $level is the number of levels that should be looked into
     * @returns word_list the accumulated list of added words
     */
    function parents(?verb $vrb, int $level): word_list
    {
        $added_wrd_lst = new word_list($this->user()); // list of the added word ids
        $added_wrd_lst = $this->foaf_level($level, $added_wrd_lst, $vrb, foaf_direction::UP, $level);

        log_debug($added_wrd_lst->name());
        return $added_wrd_lst;
    }

    /**
     * get all children
     * up to a level if defined
     * e.g. for country it will return Switzerland and also Zurich because Zurich is part of Switzerland
     * similar to parent, but the other way round
     * @param verb|null $vrb if set to filter the children by the relation type
     * @param int $level is the number of levels that should be looked into
     * @returns word_list the accumulated list of added words
     */
    function children(?verb $vrb, int $level = 0): word_list
    {
        $added_wrd_lst = new word_list($this->user()); // list of the added word ids
        $added_wrd_lst = $this->foaf_level($level, $added_wrd_lst, $vrb, foaf_direction::DOWN, $level);

        log_debug($added_wrd_lst->dsp_id());
        return $added_wrd_lst;
    }

    /**
     * similar to foaf_child, but for only one level
     * ex foaf_child_step
     * @param verb|null $vrb if set to filter the children by the relation type
     * @returns word_list the accumulated list of added words
     */
    function direct_children(?verb $vrb): word_list
    {
        $added_wrd_lst = new word_list($this->user()); // list of the added word ids
        $added_wrd_lst = $this->foaf_level(1, $added_wrd_lst, $vrb, foaf_direction::DOWN, 1);

        log_debug($added_wrd_lst->dsp_id());
        return $added_wrd_lst;
    }

    /**
     * returns a list of words that are related to this word list
     * e.g. for "ABB" and "Daimler" it will return "Company", but not "ABB"
     * @returns word_list with the added words
     */
    function is(): word_list
    {
        global $vrb_cac;
        $wrd_lst = $this->foaf_parents($vrb_cac->get_verb(verbs::IS));
        log_debug($this->dsp_id() . ' is ' . $wrd_lst->name());
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
        global $vrb_cac;
        log_debug('for ' . $this->dsp_id());
        $wrd_lst = $this->children($vrb_cac->get_verb(verbs::IS));
        $wrd_lst->merge($this);
        log_debug($this->dsp_id() . ' are ' . $wrd_lst->name());
        return $wrd_lst;
    }

    /**
     * returns a list of words that are related to this word list
     * @returns word_list with the added words
     */
    function contains(): word_list
    {
        global $vrb_cac;
        $wrd_lst = $this->children($vrb_cac->get_verb(verbs::IS_PART_OF));
        $wrd_lst->merge($this);
        log_debug($this->dsp_id() . ' contains ' . $wrd_lst->name());
        return $wrd_lst;
    }

    /**
     * makes sure that all combinations of "are" and "contains" are included
     * @returns word_list with the added words
     */
    function are_and_contains(): word_list
    {
        log_debug('for ' . $this->dsp_id());

        // this first time get all related items
        $wrd_lst = clone $this;
        $wrd_lst = $wrd_lst->are();
        $wrd_lst = $wrd_lst->contains();
        $added_lst = clone $wrd_lst;
        $added_lst->diff($this);
        if (count($added_lst->lst()) > 0) {
            log_debug('add ' . $added_lst->name() . ' to ' . $wrd_lst->name());
        }
        // ... and after that get only for the new
        if (count($added_lst->lst()) > 0) {
            $loops = 0;
            log_debug('added ' . $added_lst->name() . ' to ' . $wrd_lst->name());
            do {
                $next_lst = clone $added_lst;
                $next_lst = $next_lst->are();
                $next_lst = $next_lst->contains();
                $next_lst->diff($added_lst);
                $added_lst->merge($next_lst);
                if (count($next_lst->lst()) > 0) {
                    log_debug('add ' . $next_lst->name() . ' to ' . $wrd_lst->name());
                }
                $wrd_lst->merge($added_lst);
                $loops++;
            } while (count($next_lst->lst()) > 0 and $loops < MAX_LOOP);
        }
        log_debug($this->dsp_id() . ' are_and_contains ' . $wrd_lst->name());
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
        global $vrb_cac;
        $wrd_lst = $this->foaf_parents($vrb_cac->get_verb(verbs::CAN_CONTAIN));
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
        global $vrb_cac;
        // this first time get all related items
        // parents and not children because the verb is "can contain", but here the question is for "can be split by"
        $wrd_lst = $this->foaf_parents($vrb_cac->get_verb(verbs::CAN_CONTAIN));
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
        $parents = new word_list($this->user());
        foreach ($this->lst() as $wrd) {
            $phr_lst = $wrd->parents();
            $wrd_lst = $phr_lst->wrd_lst_all();
            $parents->merge($wrd_lst);
        }
        $result = clone $this;
        $result->diff($parents);
        return $result;
    }


    /*
     * im- and export
     */

    /**
     * import a word list object from a JSON array object
     *
     * @param array $json_obj an array with the data of the json object
     * @param object|null $test_obj if not null the unit test object to get a dummy seq id
     * @return user_message the status of the import and if needed the error messages that should be shown to the user
     */
    function import_obj(array $json_obj, object $test_obj = null): user_message
    {
        $usr_msg = new user_message();
        foreach ($json_obj as $value) {
            $wrd = new word($this->user());
            $usr_msg->add($wrd->import_obj($value, $test_obj));
            $this->add($wrd);
        }

        return $usr_msg;
    }

    /**
     * create an array with the export json fields
     * @param bool $do_load to switch off the database load for unit tests
     * @return array the filled array used to create the user export json
     */
    function export_json(bool $do_load = true): array
    {
        $wrd_lst = [];
        foreach ($this->lst() as $wrd) {
            if (get_class($wrd) == word::class) {
                if ($wrd->has_cfg()) {
                    $wrd_lst[] = $wrd->export_json($do_load);
                }
            } else {
                log_err('The function wrd_lst->export_json returns ' . $wrd->dsp_id() . ', which is ' . get_class($wrd) . ', but not a word.', 'export->get');
            }
        }
        return $wrd_lst;
    }


    /*
     * modify
     */

    /**
     * add one word by the id to the word list, but only if it is not yet part of the word list
     * @param int $wrd_id_to_add id of the word object that should be added
     * @return bool true if the word has been added and false if the word has been already in the list
     */
    function add_id(int $wrd_id_to_add): bool
    {
        $result = false;
        log_debug($wrd_id_to_add);
        if (!in_array($wrd_id_to_add, $this->ids())) {
            if ($wrd_id_to_add > 0) {
                $wrd_to_add = new word($this->user());
                $wrd_to_add->load_by_id($wrd_id_to_add);

                $this->add($wrd_to_add);
                $result = true;
            }
        }
        return $result;
    }

    /**
     * add one word to the word list defined by the word name
     * @param string $wrd_name_to_add name of the word object that should be added
     * @return bool true if the word has been added and false if the word has been already in the list
     */
    function add_name(string $wrd_name_to_add): bool
    {
        $result = false;
        log_debug($wrd_name_to_add);
        if (is_null($this->user()->id())) {
            log_err("The user must be set.", "word_list->add_name");
        } else {
            $wrd_to_add = new word($this->user());
            $wrd_to_add->load_by_name($wrd_name_to_add);

            $this->add($wrd_to_add);
            $result = true;
        }
        return $result;
    }

    /**
     * merge as a function, because the array_merge does not create an object
     * @param word_list $new_wrd_lst with the words that should be added
     * @return bool true if at least one word has been added that has not yet been in the list
     */
    function merge(word_list $new_wrd_lst): bool
    {
        $result = false;
        log_debug('->merge ' . $new_wrd_lst->name() . ' to ' . $this->dsp_id() . '"');
        foreach ($new_wrd_lst->lst() as $new_wrd) {
            log_debug('->merge add ' . $new_wrd->name() . ' (' . $new_wrd->id() . ')');
            $this->add($new_wrd);
            $result = true;
        }
        return $result;
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
        log_debug('->diff of ' . $del_wrd_lst->dsp_id() . ' and ' . $this->dsp_id());

        // check and adjust the parameters
        if (!isset($del_wrd_lst)) {
            log_err('Phrases to delete are missing.', 'word_list->diff');
        }

        if (count($this->lst()) > 0) {
            $result = array();
            $lst_ids = $del_wrd_lst->ids();
            foreach ($this->lst() as $wrd) {
                if (!in_array($wrd->id(), $lst_ids)) {
                    $result[] = $wrd;
                }
            }
            $this->set_lst($result);
        }

        log_debug($this->dsp_id());
    }

    /**
     * similar to diff, but using an id array to exclude instead of a word list object
     *
     * @param array $del_wrd_ids is the list of word ids that should be removed from this list object
     */
    function diff_by_ids(array $del_wrd_ids): void
    {
        $lib = new library();
        foreach ($del_wrd_ids as $del_wrd_id) {
            if ($del_wrd_id > 0) {
                if (in_array($del_wrd_id, $this->ids())) {
                    $del_pos = array_search($del_wrd_id, $this->ids());
                    log_debug('exclude (' . $this->get_by_id($del_pos)->name() . ')');
                    $this->unset($del_pos);
                }
            }
        }
        log_debug($this->dsp_id() . ' (' . $lib->dsp_array($this->ids()));
    }

    /**
     * Exclude all time words out of the list of words
     */
    function ex_time(): void
    {
        $del_wrd_lst = $this->time_lst();
        $this->diff($del_wrd_lst);
        log_debug($this->dsp_id());
    }

    /**
     * Exclude all measure words out of the list of words
     */
    function ex_measure(): void
    {
        $del_wrd_lst = $this->measure_lst();
        $this->diff($del_wrd_lst);
        log_debug($this->dsp_id());
    }

    /**
     * Exclude all scaling words out of the list of words
     */
    function ex_scaling(): void
    {
        $del_wrd_lst = $this->scaling_lst();
        $this->diff($del_wrd_lst);
        log_debug($this->dsp_id());
    }

    /**
     * remove the percent words from this word list
     */
    function ex_percent(): void
    {
        $del_wrd_lst = $this->percent_lst();
        $this->diff($del_wrd_lst);
        log_debug($this->dsp_id());
    }

    /**
     * sort a word list by name
     * TODO use the user:sandbox_list_named function
     */
    function wlsort(): array
    {
        log_debug($this->dsp_id() . ' and user ' . $this->user()->name);
        $lib = new library();
        $name_lst = array();
        $result = array();
        $pos = 0;
        foreach ($this->lst() as $wrd) {
            $name_lst[$pos] = $wrd->name();
            $pos++;
        }
        asort($name_lst);
        log_debug('sorted "' . implode('","', $name_lst) . '" (' . $lib->dsp_array(array_keys($name_lst)) . ')');
        foreach (array_keys($name_lst) as $sorted_id) {
            log_debug('get ' . $sorted_id);
            $wrd_to_add = $this->get($sorted_id);
            log_debug('got ' . $wrd_to_add->name());
            $result[] = $wrd_to_add;
        }
        // check
        if (count($this->lst()) <> count($result)) {
            log_err("Sorting changed the number of words from " . $lib->dsp_count($this->lst()) . " to " . $lib->dsp_count($result) . ".", "word_list->wlsort");
        } else {
            $this->set_lst($result);
        }
        log_debug('sorted ' . $this->dsp_id());
        return $result;
    }


    /*
     * filter
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
        log_debug('->filter of ' . $filter_lst->dsp_id() . ' and ' . $this->dsp_id());
        $result = clone $this;

        // check and adjust the parameters
        if ($filter_lst->count() <= 0) {
            log_err('Phrases to delete are missing.', 'word_list->filter');
        }
        if (get_class($filter_lst) <> word_list::class) {
            log_err(get_class($filter_lst) . ' cannot be used to delete words.', 'word_list->filter');
        }

        if (count($result->lst()) > 0) {
            $wrd_lst = array();
            $lst_ids = $filter_lst->ids();
            foreach ($result->lst() as $wrd) {
                if (in_array($wrd->id(), $lst_ids)) {
                    $wrd_lst[] = $wrd;
                }
            }
            $result->set_lst($wrd_lst);
            log_debug($result->dsp_id());
        }

        return $result;
    }

    /**
     * filter the time words out of the list of words
     * @return word_list with the time words (all)
     */
    function time_lst(): word_list
    {
        log_debug('for words "' . $this->dsp_id() . '"');
        $lib = new library();

        global $phr_typ_cac;
        $result = new word_list($this->user());
        $time_type = $phr_typ_cac->id(phrase_type_shared::TIME);
        // loop over the word ids and add only the time ids to the result array
        foreach ($this->lst() as $wrd) {
            if ($wrd->type_id() == $time_type) {
                $result->add($wrd);
                log_debug('found (' . $wrd->name() . ')');
            } else {
                log_debug('not found (' . $wrd->name() . ')');
            }
        }
        if (count($result->lst()) < 10) {
            log_debug('total found ' . $result->dsp_id());
        } else {
            log_debug('total found: ' . $lib->dsp_count($result->lst()) . ' ');
        }
        return $result;
    }

    /**
     * create a useful list of time phrases
     * @return word_list with the "useful" time words
     */
    function time_useful(): word_list
    {
        log_debug('for ' . $this->dsp_id());

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


        log_debug($result->dsp_id());
        return $result;
    }

    /**
     * filter the measure words out of the list of words
     * @return word_list with the measure words
     */
    function measure_lst(): word_list
    {
        global $phr_typ_cac;
        $lib = new library();

        log_debug($this->dsp_id());

        $result = new word_list($this->user());
        $measure_type = $phr_typ_cac->id(phrase_type_shared::MEASURE);
        // loop over the word ids and add only the time ids to the result array
        foreach ($this->lst() as $wrd) {
            if ($wrd->type_id == $measure_type) {
                $result->add_obj($wrd);
                log_debug('found (' . $wrd->name() . ')');
            } else {
                log_debug($wrd->name() . ' is not measure');
            }
        }
        log_debug($lib->dsp_count($result->lst()));
        return $result;
    }

    /**
     * filter the scaling words out of the list of words
     * @return word_list with the scaling words
     */
    function scaling_lst(): word_list
    {
        global $phr_typ_cac;
        $lib = new library();

        log_debug($this->dsp_id());

        $result = new word_list($this->user());
        $scale_type = $phr_typ_cac->id(phrase_type_shared::SCALING);
        $scale_hidden_type = $phr_typ_cac->id(phrase_type_shared::SCALING_HIDDEN);
        // loop over the word ids and add only the time ids to the result array
        foreach ($this->lst() as $wrd) {
            if ($wrd->type_id == $scale_type or $wrd->type_id == $scale_hidden_type) {
                $wrd->usr = $this->user(); // review: should not be needed
                $result->add_obj($wrd);
                log_debug('found (' . $wrd->name() . ')');
            } else {
                log_debug('not found (' . $wrd->name() . ')');
            }
        }
        log_debug($lib->dsp_count($result->ids()));
        return $result;
    }

    /**
     * filter the percent words out of the list of words
     * @return word_list with the percent words
     */
    function percent_lst(): word_list
    {
        global $phr_typ_cac;
        $lib = new library();

        log_debug($this->dsp_id());

        $result = new word_list($this->user());
        $percent_type = $phr_typ_cac->id(phrase_type_shared::PERCENT);
        // loop over the word ids and add only the time ids to the result array
        foreach ($this->lst() as $wrd) {
            if ($wrd->type_id == $percent_type) {
                $result->add_obj($wrd);
                log_debug('found (' . $wrd->name() . ')');
            } else {
                log_debug($wrd->name() . ' is not percent');
            }
        }
        log_debug($lib->dsp_count($result->ids()));
        return $result;
    }


    /*
     * extract
     */

    /**
     * @return array list of the word ids
     */
    function ids(int $limit = null): array
    {
        $result = array();
        foreach ($this->lst() as $wrd) {
            if ($wrd->id() > 0) {
                $result[] = $wrd->id();
            }
        }
        return $result;
    }


    /*
     *  convert
     */

    /**
     * get the best matching phrase group
     */
    function get_grp(): ?group
    {
        log_debug('->get_grp');

        $grp = new group($this->user());

        // get or create the group
        if (count($this->ids()) <= 0) {
            log_err('Cannot create phrase group for an empty list.', 'word_list->get_grp');
        } else {
            $grp = new group($this->user());
            $grp->load_by_ids((new phr_ids($this->ids())));
        }

        /*
        TODO check if a new group is not created
        $result = $grp->get_id();
        if ($result->id() > 0) {
          zu_debug('word_list->get_grp <'.$result->id.'> for "'.$this->name().'" and user '.$this->user()->name);
        } else {
          zu_debug('word_list->get_grp create for "'.implode(",",$grp->wrd_lst->names()).'" ('.implode(",",$grp->wrd_lst->ids()).') and user '.$grp->usr->name);
          $result = $grp->get_id();
          if ($result->id() > 0) {
            zu_debug('word_list->get_grp created <'.$result->id.'> for "'.$this->name().'" and user '.$this->user()->name);
          }
        }
        */
        log_debug('done ' . $grp->id());
        return $grp;
    }

    /**
     * convert the word list object into a phrase list object
     * @return phrase_list with all words of this list
     */
    function phrase_lst(): phrase_list
    {
        log_debug($this->dsp_id());
        $lib = new library();
        $phr_lst = new phrase_list($this->user());
        foreach ($this->lst() as $phr) {
            if (get_class($phr) == word::class or get_class($phr) == word_dsp::class) {
                $phr_lst->add($phr->phrase());
            } elseif (get_class($phr) == phrase::class) {
                $phr_lst->add($phr);
            } else {
                log_err('unexpected object type ' . get_class($phr));
            }
        }
        $phr_lst->id_lst();
        log_debug('done ' . $lib->dsp_count($phr_lst->lst()));
        return $phr_lst;
    }

    /**
     * @return value the first (or later "best") value related to the word lst
     * or an array with the value and the user_id if the result is user specific
     */
    function value(): value
    {
        $val = new value($this->user());
        $phr_lst = $this->phrase_lst();
        $phr_grp = new group($this->user());
        $phr_grp->load_by_phr_lst($phr_lst);
        $val->load_by_grp($phr_grp);

        log_debug($val->name() . ' for "' . $this->user()->name . '" is ' . $val->number());
        return $val;
    }

    /**
     * @return value get first (or later "best") value related to the word lst
     * and scale it e.g. convert "2.1 mio" to "2'100'000"
     */
    function value_scaled(): value
    {
        log_debug($this->dsp_id() . " for " . $this->user()->name);

        $val = $this->value();

        // get all words related to the value id; in many cases this does not match with the value_words there are used to get the word: it may contain additional word ids
        if ($val->is_id_set()) {
            log_debug("get word " . $this->name());
            //$val->load_phrases();
            // switch on after value->scale is working fine
            //$val->number = $val->scale($val->wrd_lst);
        }

        return $val;
    }


    /*
     *  info
     */

    /**
     * @return bool true if the word is part of the word list
     */
    function does_contain($wrd_to_check): bool
    {
        $result = false;

        foreach ($this->lst() as $wrd) {
            if ($wrd->id() == $wrd_to_check->id()) {
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
        log_debug('for ' . $this->dsp_id());
        $result = false;
        // loop over the word ids and add only the time ids to the result array
        foreach ($this->lst() as $wrd) {
            log_debug('check (' . $wrd->name() . ')');
            if (!$result) {
                if ($wrd->is_time()) {
                    $result = true;
                }
            }
        }
        log_debug(zu_dsp_bool($result));
        return $result;
    }

    /**
     * @return bool true if a word lst contains a measure word
     */
    function has_measure(): bool
    {
        $result = false;
        // loop over the word ids and add only the time ids to the result array
        foreach ($this->lst() as $wrd) {
            log_debug('check (' . $wrd->name() . ')');
            if (!$result) {
                if ($wrd->is_measure()) {
                    $result = true;
                }
            }
        }
        log_debug(zu_dsp_bool($result));
        return $result;
    }

    /**
     * @return bool true if a word lst contains a scaling word
     */
    function has_scaling(): bool
    {
        $result = false;
        // loop over the word ids and add only the time ids to the result array
        foreach ($this->lst() as $wrd) {
            log_debug('check (' . $wrd->name() . ')');
            if (!$result) {
                if ($wrd->is_scaling()) {
                    $result = true;
                }
            }
        }
        log_debug(zu_dsp_bool($result));
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
        foreach ($this->lst() as $wrd) {
            log_debug('check (' . $wrd->name() . ')');
            if (!$result) {
                if ($wrd->is_percent()) {
                    $result = true;
                }
            }
        }
        log_debug(zu_dsp_bool($result));
        return $result;
    }


    /*
     * select linked
     */

    /**
     * get a list of all views used to the words
     * @return array of views linked to this word list
     */
    function view_lst(): array
    {
        $result = array();
        $lib = new library();
        log_debug();

        foreach ($this->lst() as $wrd) {
            // $wrd_dsp = $wrd->dsp_obj();
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
                    log_debug('add ' . $view->dsp_id());
                    $result[] = $view;
                }
            }
        }

        log_debug('done got ' . $lib->dsp_count($result));
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
        log_debug($this->dsp_id() . ' and user ' . $this->user()->name);
        $max_wrd = new word($this->user());
        if (count($this->lst()) > 0) {
            foreach ($this->lst() as $wrd) {
                // TODO replaced by "is following"
                if ($wrd->name() > $max_wrd->name()) {
                    log_debug('select (' . $wrd->name() . ' instead of ' . $max_wrd->name() . ')');
                    $max_wrd = clone $wrd;
                }
            }
        }
        return $max_wrd;
    }

    /**
     * get the time of the last value related to a word and assigned to a phrase list
     * @param term_list|null $trm_lst a list of preloaded terms that should be used for the transformation
     * @return word|null a time word (or phrase?)
     */
    function max_val_time(?term_list $trm_lst = null): ?word
    {
        log_debug($this->dsp_id() . ' and user ' . $this->user()->name);
        $lib = new library();
        $wrd = null;

        if ($trm_lst == null) {
            // load the list of all value related to the word list
            $val_lst = new value_list($this->user());
            $val_lst->load_by_phr_lst($this->phrase_lst());
            log_debug($lib->dsp_count($val_lst->lst()) . ' values for ' . $this->dsp_id());

            $time_ids = array();
            foreach ($val_lst->lst() as $val) {
                $val->load_phrases();
                if (isset($val->time_phr)) {
                    log_debug('value (' . $val->number() . ' @ ' . $val->time_phr->name() . ')');
                    if ($val->time_phr->id() > 0) {
                        if (!in_array($val->time_phr->id(), $time_ids)) {
                            $time_ids[] = $val->time_phr->id();
                            log_debug('add word id (' . $val->time_phr->id() . ')');
                        }
                    }
                }
            }

            $time_lst = new word_list($this->user());
            if (count($time_ids) > 0) {
                $time_lst->load_by_ids($time_ids);
                $wrd = $time_lst->max_time();
            }
        } else {
            $time_lst = new word_list($this->user());
            foreach ($trm_lst->lst() as $trm) {
                if ($trm->is_time()) {
                    $time_lst->add($trm->word());
                }
            }
            $wrd = $time_lst->max_time();
        }

        /*
        // get all values related to the selecting word, because this is probably strongest selection and to save time reduce the number of records asap
        $val = New value;
        $val->wrd_lst = $this;
        $val->usr = $this->user();
        $val->load_by_wrd_lst();
        $value_lst = array();
        $value_lst[$val->id] = $val->number();
        zu_debug('word_list->max_val_time -> ('.implode(",",$value_lst).')');

        if (sizeof($value_lst) > 0) {

          // get all words related to the value list
          $all_word_lst = zu_sql_value_lst_words($value_lst, $this->user()->id());

          // get the time words
          $time_lst = zut_time_lst($all_word_lst);

          // get the most useful (last) time words (replace by a "followed by" sorted list
          ar sort($time_lst);
          $time_keys = array_keys($time_lst);
          $wrd_id = $time_keys[0];
          $wrd = New word_dsp;
          if ($wrd_id > 0) {
            $wrd->id = $wrd_id;
            $wrd->usr = $this->user();
            $wrd->load();
          }
        }
        */
        if ($wrd != null) {
            log_debug('done (' . $wrd->name() . ')');
        }
        return $wrd;
    }

    /**
     * get the most useful time for the given list
     * so either the last time from the word list
     * or the time of the last "real" (reported) value for the word list
     *
     * always returns a phrase to avoid converting in the calling function
     * @param term_list|null $trm_lst a list of preloaded terms that should be used for the transformation
     * @return phrase|null a time phrase
     */
    function assume_time(?term_list $trm_lst = null): ?phrase
    {
        log_debug('for ' . $this->dsp_id());
        $result = null;
        $phr = null;

        if ($this->has_time()) {
            // get the last time from the word list
            $time_phr_lst = $this->time_lst();
            // shortcut, replace with a most_useful function
            foreach ($time_phr_lst->lst() as $time_wrd) {
                if (is_null($phr)) {
                    $phr = $time_wrd;
                    $phr->set_user($this->user());
                } else {
                    log_warning("The word list contains more time word than supported by the program.", "word_list->assume_time");
                }
            }
            log_debug('time ' . $phr->name() . ' assumed for ' . $this->name());
        } else {
            // get the time of the last "real" (reported) value for the word list
            $wrd_max_time = $this->max_val_time($trm_lst);
            $phr = $wrd_max_time?->phrase();
        }

        if ($phr != null) {
            log_debug('time used "' . $phr->name() . '" (' . $phr->id() . ')');
            if (get_class($phr) == word::class or get_class($phr) == word_dsp::class) {
                $result = $phr->phrase();
            } else {
                $result = $phr;
            }
        } else {
            log_debug('no time found');
        }
        return $result;
    }


    /*
     * save
     */

    function save(): user_message
    {
        $usr_msg = new user_message();

        // load the words that are already in the database
        $db_lst = new word_list($this->user());
        $db_lst->load_by_names($this->names());

        // create any missing sql functions and insert the missing triples
        $usr_msg->add($this->insert($db_lst));

        // update the existing words
        // TODO create a test that fields not included in the import message are not updated, but e.g. an empty descrption is updated
        // loop over the words and check if all needed functions exist
        // create the missing functions
        // create blocks of update function calls

        return $usr_msg;
    }

}