<?php

/*

    model/phrase/phrase_list.php - a list of phrase (word or triple) objects
    --------------------------

    Compared to groups a phrase list is a memory only object that cannot be saved to the database

    TODO
        add function to
             selects out of a word list the most important word
             e.g. given the word list "Turnover, Nestlé, 2014, GAAP", "Turnover" and "Nestlé" is selected,
             because "2014" is the default time word for a company
             and "GAAP" is the default Accounting word for a company
        add function to
             returns an array of the missing word types
             e.g. ("Nestlé", "turnover") with formula "increase" returns "time_jump" is missing

    The main sections of this object are
    - construct and map: including the mapping of the db row to this word object
    - set and get:       to capsule the vars from unexpected changes
    - cast:              create an api object and set the vars from an api json
    - load:              database access object (DAO) functions
    - sql:               to create sql statements e.g. for load
    - tree building      create foaf trees
    - im- and export:    create an export object and set the vars from an import object
    - information:       functions to make code easier to read
    - check:             validate the list
    - modify:            change potentially all object and all variables of this list with one function call
    - save:              manage to update the database
    - debug:             internal support functions for debugging
    - display:           to be moved to the frontend
    - review:            functions that should be reviewed


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

namespace cfg\phrase;

include_once MODEL_SANDBOX_PATH . 'sandbox_list_named.php';
include_once DB_PATH . 'sql_creator.php';
include_once DB_PATH . 'sql_db.php';
include_once DB_PATH . 'sql_par.php';
include_once DB_PATH . 'sql_par_type.php';
include_once MODEL_FORMULA_PATH . 'formula_list.php';
include_once MODEL_GROUP_PATH . 'group.php';
include_once MODEL_GROUP_PATH . 'group_id.php';
include_once MODEL_HELPER_PATH . 'data_object.php';
include_once MODEL_SANDBOX_PATH . 'sandbox.php';
include_once MODEL_SANDBOX_PATH . 'sandbox_list_named.php';
include_once MODEL_USER_PATH . 'user_message.php';
include_once MODEL_VALUE_PATH . 'value.php';
include_once MODEL_VALUE_PATH . 'value_base.php';
include_once MODEL_VALUE_PATH . 'value_list.php';
include_once MODEL_VERB_PATH . 'verb.php';
include_once MODEL_WORD_PATH . 'word.php';
include_once MODEL_WORD_PATH . 'word_list.php';
include_once MODEL_WORD_PATH . 'triple.php';
include_once MODEL_WORD_PATH . 'triple_list.php';
include_once MODEL_PHRASE_PATH . 'trm_ids.php';
include_once MODEL_PHRASE_PATH . 'term_list.php';
include_once SHARED_ENUM_PATH . 'foaf_direction.php';
include_once SHARED_ENUM_PATH . 'messages.php';
include_once SHARED_TYPES_PATH . 'phrase_type.php';
include_once SHARED_TYPES_PATH . 'verbs.php';
include_once SHARED_PATH . 'json_fields.php';
include_once SHARED_PATH . 'library.php';

use cfg\db\sql_creator;
use cfg\db\sql_db;
use cfg\db\sql_par;
use cfg\db\sql_par_type;
use cfg\formula\formula_list;
use cfg\group\group;
use cfg\group\group_id;
use cfg\helper\data_object;
use cfg\sandbox\sandbox;
use cfg\sandbox\sandbox_list_named;
use cfg\user\user_message;
use cfg\value\value;
use cfg\value\value_list;
use cfg\verb\verb;
use cfg\word\word;
use cfg\word\word_list;
use cfg\word\triple;
use cfg\word\triple_list;
use shared\enum\foaf_direction;
use shared\enum\messages as msg_id;
use shared\json_fields;
use shared\types\phrase_type as phrase_type_shared;
use shared\types\verbs;
use shared\library;

class phrase_list extends sandbox_list_named
{

    // $lst of base_list is used as array of the loaded phrase objects
    // (key is at the moment the database id, but it looks like this has no advantages,
    // so a normal 0 to n order could have more advantages)
    // $usr of sandbox list is the user object of the person for whom the phrase list is loaded, so to say the viewer


    /*
     * construct and map
     */

    /**
     * fill the phrase list based on a database records
     * actually just set the phrase object for the parent function
     *
     * @param array|null $db_rows is an array of an array with the database values
     * @param bool $load_all force to include also the excluded phrases e.g. for admins
     * @return bool true if at least one phrase has been added
     */
    protected function rows_mapper(?array $db_rows, bool $load_all = false): bool
    {
        return parent::rows_mapper_obj(new phrase($this->user()), $db_rows, $load_all);
    }

    /**
     * map a phrase list api json to this model phrase list object
     * @param array $api_json the api array with the phrases that should be mapped
     */
    function api_mapper(array $api_json): user_message
    {
        $usr_msg = new user_message();

        foreach ($api_json as $json_phr) {
            $phr = new phrase($this->user());
            $usr_msg->add($phr->api_mapper($json_phr));
            if ($usr_msg->is_ok()) {
                $this->add($phr);
            }
        }

        return $usr_msg;
    }

    /**
     * import a phrase list from an inner part of a JSON array object
     *
     * @param array $json_obj an array with the data of the json object
     * @param data_object|null $dto cache of the objects imported until now for the primary references
     * @param object|null $test_obj if not null the unit test object to get a dummy seq id
     * @return user_message the status of the import and if needed the error messages that should be shown to the user
     */
    function import_mapper(array $json_obj, data_object $dto = null, object $test_obj = null): user_message
    {
        $usr_msg = new user_message();
        $phr_lst = $dto->phrase_list();

        foreach ($json_obj as $phr_name) {
            if ($phr_name == '') {
                $usr_msg->add_message(implode(',', $json_obj) . ' contains an empty phrase name');
            } else {
                if ($usr_msg->is_ok()) {
                    $phr = $phr_lst->get_by_name($phr_name);
                    if ($phr == null) {
                        $usr_msg->add_type_message($phr_name, msg_id::PHRASE_MISSING->value);
                        $phr = new phrase($this->user());
                        $phr->set_name($phr_name);
                        $phr_lst->add_by_name($phr);
                    }
                    $this->add_by_name($phr);
                }
            }
        }

        return $usr_msg;
    }


    /*
     * load
     */

    /**
     * load a list of phrase names based on the given pattern
     *
     * @param string $pattern to select the phrases
     * @return bool true if at least one phrase has been loaded
     */
    function load_like(string $pattern): bool
    {
        global $db_con;

        $sc = $db_con->sql_creator();
        $qp = $this->load_sql_like($sc, $pattern);
        return $this->load($qp);
    }

    /**
     * load the phrases including the related word or triple object
     * by the given name list from the database
     *
     * @param array $names of phrase names that should be loaded
     * @param phrase_list|null $phr_lst a list of preloaded phrase that should not be loaded again
     * @return bool true if at least one phrase has been loaded
     */
    function load_by_names(array $names, ?phrase_list $phr_lst = null): bool
    {
        global $db_con;

        // exclude the names that have been in the cache
        if ($phr_lst != null) {
            $names_to_load = array_diff($names, $phr_lst->names());
        } else {
            $names_to_load = $names;
        }

        // create the sql and load
        $sc = $db_con->sql_creator();
        $qp = $this->load_sql_by_names($sc, $names_to_load);
        return $this->load($qp);
    }

    /**
     * load the phrases including the related word or triple object
     * by the given id list from the database
     * TODO make it optional to include excluded phrases
     *
     * @param phr_ids $ids phrase ids that should be loaded
     * @param phrase_list|null $phr_lst a list of preloaded phrase that should not be loaded again
     * @return bool true if at least one phrase has been loaded
     */
    function load_by_ids(phr_ids $ids, ?phrase_list $phr_lst = null): bool
    {
        global $db_con;

        // exclude the id that have been in the cache
        if ($phr_lst != null) {
            $ids_lst_to_load = array_diff($ids->lst, $phr_lst->ids());
            $ids_to_load = new phr_ids($ids_lst_to_load);
        } else {
            $ids_to_load = $ids;
        }

        // create the sql and load
        $sc = $db_con->sql_creator();
        $qp = $this->load_sql_by_ids($sc, $ids_to_load);
        $result = $this->load($qp);
        if ($phr_lst != null) {
            $phr_lst_to_add = $phr_lst->filter_by_ids($ids);
            if (!$phr_lst_to_add->is_empty()) {
                $this->merge($phr_lst_to_add);
                $result = true;
            }
        }
        return $result;
    }

    /*
     * sql
     */

    /**
     * create an SQL statement to retrieve a list of phrase objects
     * by the name pattern from the database
     * TODO add limit and page
     * TODO add read test that formula link words are excluded
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $pattern phrase names that should be loaded
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_like(sql_creator $sc, string $pattern): sql_par
    {
        $qp = $this->load_sql($sc, 'name_like');
        $sc->add_where(phrase::FLD_NAME, $pattern, sql_par_type::LIKE_R);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a list of phrase objects by the name from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param array $names phrase names that should be loaded
     * @param string $fld the name of the name field
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_names(
        sql_creator $sc,
        array $names,
        string $fld = phrase::FLD_NAME
    ): sql_par
    {
        return parent::load_sql_by_names($sc, $names, $fld);
    }

    /**
     * create an SQL statement to retrieve a list of phrase objects by the id from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param phr_ids $ids phrase ids that should be loaded
     * @param int $limit the number of rows to return
     * @param int $offset jump over these number of pages
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_ids(
        sql_creator $sc,
        phr_ids     $ids,
        int         $limit = 0,
        int         $offset = 0
    ): sql_par
    {
        $qp = $this->load_sql($sc, 'ids');
        $sc->add_where(phrase::FLD_ID, $ids->lst, sql_par_type::INT_LIST);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * set the SQL query parameters to load a list of phrase objects
     * with all parameters and the related phrase
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name extension to make the query name unique
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql_creator $sc, string $query_name): sql_par
    {
        $sc->set_class(phrase::class);
        $qp = new sql_par(self::class);
        $qp->name .= $query_name;

        $sc->set_name($qp->name); // assign incomplete name to force the usage of the user as a parameter
        $sc->set_usr($this->user()->id());
        $sc->set_fields(phrase::FLD_NAMES);
        $sc->set_usr_fields(phrase::FLD_NAMES_USR_NO_NAME);
        $sc->set_usr_num_fields(phrase::FLD_NAMES_NUM_USR);
        $sc->set_order_text(sql_db::STD_TBL . '.' . $sc->name_sql_esc(phrase::FLD_VALUES) . ' DESC, ' . phrase::FLD_NAME);
        return $qp;
    }

    // to review

    /**
     * create an SQL statement to retrieve a list of phrase names by the id from the database
     * compared to load_sql_by_ids this reads only the phrase names and not the related phrase to save time and memory
     *
     * @param sql_creator $sc with the target db_type set
     * @param phr_ids $ids phrase ids that should be loaded
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_names_sql_by_ids(sql_creator $sc, phr_ids $ids): sql_par
    {
        $qp = $this->load_sql($sc, 'ids_fast');
        $sc->add_where(phrase::FLD_ID, $ids->lst, sql_par_type::INT_LIST);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * set the SQL query parameters to load a list of phrase by a phrase list, verb and direction
     * @param sql_creator $sc with the target db_type set
     * @param verb|null $vrb if set to filter the selection
     * @param foaf_direction $direction to select either the parents, children or all related words ana triples
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_phr_lst(
        sql_creator $sc, ?verb $vrb = null, foaf_direction $direction = foaf_direction::BOTH): sql_par
    {
        $qp = $this->load_sql($sc, 'sc_phr_lst');
        if (!$this->empty()) {
            if ($direction == foaf_direction::UP) {
                $sc->add_where(triple::FLD_FROM, $this->ids());
                $qp->name .= '_' . $direction->value;
            } elseif ($direction == foaf_direction::DOWN) {
                $sc->add_where(triple::FLD_TO, $this->ids());
                $qp->name .= '_' . $direction->value;;
            } elseif ($direction == foaf_direction::BOTH) {
                $sc->add_where(triple::FLD_FROM, $this->ids(), sql_par_type::INT_LIST_OR);
                $sc->add_where(triple::FLD_TO, $this->ids(), sql_par_type::INT_LIST_OR);
            }
            if ($vrb != null) {
                if ($vrb->id() > 0) {
                    $sc->add_where(verb::FLD_ID, $vrb->id());
                    $qp->name .= '_and_vrb';
                }
            }
            $sc->set_name($qp->name);
            $qp->sql = $sc->sql();
        } else {
            $qp->name = '';
            log_err('At least the phrase must be set to load a triple list by phrase');
        }
        $qp->par = $sc->get_par();
        return $qp;
    }

    /**
     * load the phrase names by the given id list from the database
     *
     * @param phr_ids $ids phrase ids that should be loaded
     * @param phrase_list|null $phr_lst list of the phrases already loaded to reduce traffic
     * @return bool true if at least one phrase has been loaded
     */
    function load_names_by_ids(phr_ids $ids, ?phrase_list $phr_lst = null): bool
    {
        global $db_con;
        if ($phr_lst != null) {
            $ids_lst_to_load = array_diff($ids->lst, $phr_lst->ids());
            $ids_to_load = new phr_ids($ids_lst_to_load);
        } else {
            $ids_to_load = $ids;
        }
        $qp = $this->load_names_sql_by_ids($db_con->sql_creator(), $ids_to_load);
        $result = $this->load($qp);
        if ($phr_lst != null) {
            $phr_lst_to_add = $phr_lst->filter_by_ids($ids);
            if (!$phr_lst_to_add->is_empty()) {
                $this->merge($phr_lst_to_add);
                $result = true;
            }
        }
        return $result;
    }

    /**
     * load a list of phrase names
     * @param string $pattern the pattern to filter the phrases
     * @param int $limit the number of rows to return
     * @param int $offset jump over these number of pages
     * @return bool true if at least one phrase found
     */
    function load_names(string $pattern = '', int $limit = 0, int $offset = 0): bool
    {
        return parent::load_sbx_names(new phrase($this->user()), $pattern, $limit, $offset);
    }


    /*
     * cast
     */

    /**
     * @return term_list filled with all phrases from this phrase list
     */
    function term_list(): term_list
    {
        $trm_lst = new term_list($this->user());
        foreach ($this->lst() as $phr) {
            $trm_lst->add($phr->term());
        }
        return $trm_lst;
    }


    /*
     * set and get
     */


    /*
     * im- and export
     */

    /**
     * import a phrase list from an inner part of a JSON array object
     *
     * @param array $json_obj an array with the data of the json object
     * @param object|null $test_obj if not null the unit test object to get a dummy seq id
     * @return user_message the status of the import and if needed the error messages that should be shown to the user
     */
    function import_lst(array $json_obj, object $test_obj = null): user_message
    {
        global $phr_typ_cac;

        $usr_msg = new user_message();
        foreach ($json_obj as $phr_name) {
            if ($phr_name != '') {
                $phr = new phrase($this->user());
                if ($usr_msg->is_ok()) {
                    if (!$test_obj) {
                        // TODO prevent that this happens at all
                        if (is_array($phr_name)) {
                            $lib = new library();
                            log_err($lib->dsp_array($phr_name) . ' is expected to be a string');
                            // TODO remove this fallback solution
                            if (count($phr_name) == 1) {
                                $phr_name = $phr_name[0];
                            }
                        }
                        if (!is_array($phr_name)) {
                            $phr->load_by_name($phr_name);
                            if ($phr->id() == 0) {
                                // for new phrase use the word object
                                // TODO add a test case if a triple with the name exists but the triple is based on other phrases than the given phrase
                                //      e.g. 1. create triple with "1967 "is a" "(year of definition)" but has the name "2019 (year of definition)" and a value with the phrase "1967 (year of definition)" is supposed to be added
                                $wrd = new word($this->user());
                                $wrd->load_by_name($phr_name);
                                if ($wrd->id() == 0) {
                                    $wrd->set_name($phr_name);
                                    $wrd->type_id = $phr_typ_cac->default_id();
                                    $usr_msg->add($wrd->save());
                                }
                                if ($wrd->id() == 0) {
                                    log_err('Cannot add word "' . $phr_name . '" when importing ' . $this->dsp_id(), 'value->import_obj');
                                } else {
                                    $phr = $wrd->phrase();
                                }
                            }
                        }
                    } else {
                        // fallback for unit tests
                        $phr->set_name($phr_name, word::class);
                        $phr->set_id($test_obj->seq_id());
                    }
                }
                $this->add($phr);
            }
        }

        // save the word in the database
        // TODO check why this is needed
        if ($usr_msg == '' and $test_obj == null) {
            $usr_msg->add($this->save());
        }

        return $usr_msg;
    }

    /**
     * import a word list object from a JSON array object
     *
     * @param array $json_obj an array with the data of the json object
     * @return user_message the status of the import and if needed the error messages that should be shown to the user
     */
    function import_names(array $json_obj): user_message
    {
        $usr_msg = new user_message();
        foreach ($json_obj as $word_name) {
            $wrd = new word($this->user());
            $wrd->set_name($word_name);
            $this->add($wrd->phrase());
        }
        $this->save();

        return $usr_msg;
    }

    /**
     * fill this list with the phrases of the given json without writing to the database
     * @param array $json_array
     * @return user_message
     */
    function import_context(array $json_array): user_message
    {
        global $usr;

        $usr_msg = new user_message();
        foreach ($json_array as $key => $json_obj) {
            if ($key == json_fields::WORDS) {
                foreach ($json_obj as $word) {
                    $wrd = new word($usr);
                    $import_result = $wrd->import_mapper($word);
                    $this->add_by_name($wrd->phrase());
                    $usr_msg->add($import_result);
                }
            } elseif ($key == json_fields::TRIPLES) {
                foreach ($json_obj as $triple) {
                    $trp = new triple($usr);
                    $import_result = $trp->import_mapper($triple);
                    $this->add_by_name($trp->phrase());
                    $usr_msg->add($import_result);
                }
            }
        }
        return $usr_msg;
    }

    /**
     * create an array with the export json phrases
     * @param bool $do_load to switch off the database load for unit tests
     * @return array the filled array used to create the user export json
     */
    function export_json(bool $do_load = true): array
    {
        $phr_lst = [];

        foreach ($this->lst() as $phr) {
            if (get_class($phr) == word::class or get_class($phr) == triple::class) {
                $phr_lst[] = $phr->export_json($do_load);
            } else {
                log_err('The function phrase_list->export_json returns ' . $phr->dsp_id() . ', which is ' . get_class($phr) . ', but not a word.', 'export->get');
            }
        }
        return $phr_lst;
    }


    /*
      tree building function
      ----------------------

      Overview for words, triples and phrases and it's lists

               children and            parents return the direct parents and children   without the original phrase(s)
          foaf_children and       foaf_parents return the    all parents and children   without the original phrase(s)
                    are and                 is return the    all parents and children including the original phrase(s) for the specific verb "is a"
               contains                        return the    all             children including the original phrase(s) for the specific verb "contains"
                                    is part of return the    all parents              without the original phrase(s) for the specific verb "contains"
                   next and              prior return the direct parents and children   without the original phrase(s) for the specific verb "follows"
            followed_by and        follower_of return the    all parents and children   without the original phrase(s) for the specific verb "follows"
      differentiated_by and differentiator_for return the    all parents and children   without the original phrase(s) for the specific verb "can_contain"


    user samples
        1. "Zurich is a" -> "City or Canton"
        2. "City of Zurich is part of" -> "Canton of Zurich"
        3. "City of Zurich is part of and ..." -> "Canton of Zurich"
        ...
        7. "Switzerland has the cities" -> "Zurich (City)" and "Bern (City)"

    technical samples

        1.       parents of  "Zurich"        and the verb "is"                  includes "City" and "Canton"
        2.       parents of  "Zurich (City)" and the verb "is part of"          includes "Canton of Zurich"
        3.  foaf_parents of  "Zurich (City)" and the verb "is part of"          includes "Canton of Zurich" and "Switzerland"
        4.  foaf_parents of  "Zurich"        and the verb "is" and "is part of" includes "Canton", "City" and "Switzerland"
        5.      children for "Switzerland"   and the verb "contains"            includes "Canton of Zurich"
        6. foaf_children for "Switzerland"   and the verb "contains"            includes "Canton of Zurich" and "Zurich (City)"
        7. foaf_children for "Switzerland"   and the verb "contains"
                                            and the direct children of "City"  includes "Zurich (City)" and "Bern (City)"


            "contains" for "balance sheet" is "assets" and "liabilities" and "company" and "balance sheet" (used to get all related values)
          "is part of" for "assets" is "balance sheet" but not "assets"

                "next" for "2016" is "2017"
               "prior" for "2017" is "2016"
      "is followed by" for "2016" is "2017" and "2018"
      "is follower of" for "2016" is "2015" and "2014"

      "wind energy" and "energy" "can be differentiator for" "sector"
                        "sector" "can be differentiated_by"  "wind energy" and "energy"

      if "wind energy" "is part of" "energy"

      if only the word Zurich is given as base $phr, the selection should include City and Canton as optional preselection
      by selection the preselection e.g. Canton the selection should be modified so that the 20 most often used Cantons are on the top
      with "more Canton" the user can increase to list of cantons
      at the end of the preselection list alternative groups such as City should be shown

    */

    /**
     * build one level of a phrase tree
     * @param int $level 1 if the parents of the original phrases are added
     * @param phrase_list $added_phr_lst list of the added phrase during the foaf selection process
     * @param verb|null $vrb if set to filter the related phrases by the relation type
     * @param foaf_direction $direction to select if the parents or children should be selected - "up" to select the parents
     * @param int $max_level the max level that should be used for the selection; if 0 the technical max level ist used
     * @return phrase_list the accumulated list of added phrases
     */
    private function foaf_level(
        int $level, phrase_list $added_phr_lst, ?verb $vrb, foaf_direction $direction, int $max_level
    ): phrase_list
    {
        // use the default max search level if nothing is given
        if ($max_level > 0) {
            $max_loops = $max_level;
        } else {
            $max_loops = MAX_RECURSIVE;
        }
        $loops = $level;

        // set the list if phrases used to get the related phrases
        $accumulated_list = clone $this;

        $additional_added_triples = new phrase_list($this->user());
        $additional_added_phrases = new phrase_list($this->user());

        do {
            $loops = $loops + 1;

            if (!$accumulated_list->is_empty()) {

                // TODO review this temp fix
                // to not include the direct linked "parents" because they are not real parents
                if ($direction == foaf_direction::DOWN) {
                    // load the linking triples but only if the verb suggest it
                    $additional_added_triples = $accumulated_list->load_linking_triples($vrb, $direction);
                    // get the phrases not added before
                    $additional_added_triples->diff($added_phr_lst);
                    // remember the added phrases
                    $added_phr_lst->merge($additional_added_triples);
                }

                if ($direction == foaf_direction::BOTH) {
                    // load all linked up phrases
                    $additional_added_phrases = $accumulated_list->load_linked_phrases($vrb, foaf_direction::UP);
                    // get the phrases not added before
                    $additional_added_phrases->diff($added_phr_lst);
                    // remember the added phrases
                    $added_phr_lst->merge($additional_added_phrases);

                    // load all linked down phrases
                    $additional_added_phrases = $accumulated_list->load_linked_phrases($vrb, foaf_direction::DOWN);
                    // get the phrases not added before
                    $additional_added_phrases->diff($added_phr_lst);
                    // remember the added phrases
                    $added_phr_lst->merge($additional_added_phrases);
                } else {
                    // load all linked phrases
                    $additional_added_phrases = $accumulated_list->load_linked_phrases($vrb, $direction);
                    // get the phrases not added before
                    $additional_added_phrases->diff($added_phr_lst);
                    // remember the added phrases
                    $added_phr_lst->merge($additional_added_phrases);
                }
            }

            // accumulate the list used as a base for the search
            $accumulated_list->merge($added_phr_lst);

            if ($loops >= MAX_RECURSIVE) {
                log_fatal("max number (" . $loops . ") of loops for phrase reached.", "phrase_list->tree_up_level");
            }
        } while ((
            !empty($additional_added_triples->lst()) or
            !empty($additional_added_phrases->lst())
        ) and $loops < $max_loops);
        log_debug('->foaf_level done');
        return $added_phr_lst;
    }

    /**
     * add the direct linked phrases to the list
     * and remember which phrases have be added
     *
     * @param verb|null $vrb if set to filter the children by the relation type
     * @param foaf_direction $direction to define the link direction
     * @return phrase_list with only the new added phrases
     */
    function load_linked_phrases(?verb $vrb, foaf_direction $direction): phrase_list
    {

        global $db_con;
        $lib = new library();
        $additional_added = new phrase_list($this->user()); // list of the added phrases with this call

        $qp = $this->load_sql_linked_phrases($db_con->sql_creator(), $vrb, $direction);
        if ($qp->name == '') {
            log_warning('The phrase list is empty, so nothing could be found', self::class . '->load_linked_phrases');
        } else {
            $db_con->usr_id = $this->user()->id();
            $db_phr_lst = $db_con->get($qp);
            if ($db_phr_lst) {
                log_debug('got ' . $lib->dsp_count($db_phr_lst));
                foreach ($db_phr_lst as $db_phr) {
                    if (is_null($db_phr[sandbox::FLD_EXCLUDED]) or $db_phr[sandbox::FLD_EXCLUDED] == 0) {
                        // add the phrase linked by the triple
                        if ($db_phr[phrase::FLD_ID] != 0 and !in_array($db_phr[phrase::FLD_ID], $this->ids())) {
                            $new_phrase = new phrase($this->user());
                            $new_phrase->row_mapper_sandbox($db_phr);
                            $additional_added->add($new_phrase);
                            log_debug('added "' . $new_phrase->dsp_id() . '" for verb (' . $db_phr[verb::FLD_ID] . ')');
                        }
                    }
                }
                log_debug('added (' . $additional_added->dsp_id() . ')');
            }
        }
        return $additional_added;
    }

    /**
     * add the direct linking triples to the list
     * and remember which phrases have be added
     *
     * @param verb|null $vrb if set to filter the children by the relation type
     * @param foaf_direction $direction to define the link direction
     * @return phrase_list with only the new added phrases
     */
    function load_linking_triples(?verb $vrb, foaf_direction $direction): phrase_list
    {
        $trp_lst = new triple_list($this->user());
        $trp_lst->load_by_phr_lst($this, $vrb, $direction);
        return $trp_lst->phrase_lst();
    }


    /**
     * similar to foaf_parents, but for only one level
     * ex foaf_parent_step
     * @param verb|null $vrb if not null the verbs to filter the parents
     * @param int $level is the number of levels that should be looked into and 0 (zero) loads unlimited levels
     */
    function parents(?verb $vrb = null, int $level = 0): phrase_list
    {
        log_debug($vrb->dsp_id());
        $wrd_lst = $this->wrd_lst_all();
        $added_wrd_lst = $wrd_lst->parents($vrb, $level);
        $added_phr_lst = $added_wrd_lst->phrase_lst();

        log_debug($added_phr_lst->name());
        return $added_phr_lst;
    }

    /**
     * get all children
     * e.g. for country it will return Switzerland and also Zurich because Zurich is part of Switzerland
     * similar to all_parents, but the other way round
     * @param verb|null $vrb if set to filter the children by the relation type
     * @returns phrase_list the accumulated list of added words
     */
    function all_children(?verb $vrb): phrase_list
    {
        $wrd_lst = $this->wrd_lst_all();
        $added_wrd_lst = $wrd_lst->children($vrb);
        $added_phr_lst = $added_wrd_lst->phrase_lst();

        log_debug($added_phr_lst->name());
        return $added_phr_lst;
    }

    /**
     * get the words and triples "below" the given phrases
     * e.g. for "Switzerland" it will return "Canton of Zurich"
     *
     * @param verb|null $vrb if set to filter the children by the relation type
     *                       if not set the children are not filtered by the verb
     * @param int $max_level to limit the search depth
     * @return phrase_list with all phrases "below" the original list
     */
    function foaf_children(?verb $vrb = null, int $max_level = 0): phrase_list
    {
        return $this->foaf(foaf_direction::DOWN, $vrb, $max_level);
    }

    /**
     * get the words and triples "below" the given phrases
     * e.g. for "Zurich" it will return "Canton of Zurich"
     *
     * @param verb|null $vrb if not null the verbs to filter the parents
     * @param int $max_level to limit the search depth
     * @returns array a list of phrases, that characterises the given phrase
     */
    function foaf_parents(?verb $vrb = null, int $max_level = 0): phrase_list
    {
        return $this->foaf(foaf_direction::UP, $vrb, $max_level);
    }

    /**
     * get the words and triples related the given phrases
     * e.g. for "Canton of Zurich" it will return "Zurich" and "Switzerland"
     *
     * @param verb|null $vrb if not null the verbs to filter the parents
     * @param int $max_level to limit the search depth
     * @returns array a list of phrases, that characterises the given phrase
     */
    function foaf_related(?verb $vrb = null, int $max_level = 0): phrase_list
    {
        return $this->foaf(foaf_direction::BOTH, $vrb, $max_level);
    }

    /**
     * get the related words and triples
     * if requested filtered by the verb and number of levels
     * e.g. for "Switzerland" and "DOWN" it will return "Canton of Zurich"
     * e.g. for "Zurich" and "UP" it will return "Canton of Zurich"
     *
     * @param foaf_direction $direction to select either the parents, children or all related words ana triples
     * @param verb|null $vrb if set to filter the children by the relation type
     *                       if not set the children are not filtered by the verb
     * @param int $max_level to limit the search depth
     * @return phrase_list with all phrases "below" the original list
     */
    private function foaf(foaf_direction $direction, ?verb $vrb = null, int $max_level = 0): phrase_list
    {
        $level = 0;
        $added_phr_lst = new phrase_list($this->user()); // list of the added phrases
        $added_phr_lst = $this->foaf_level(
            $level, $added_phr_lst, $vrb, $direction, $max_level
        );

        log_debug($added_phr_lst->name());
        return $added_phr_lst;
    }

    /**
     * get the direct children
     * e.g. for country it will return Switzerland, but not Zurich
     * similar to foaf_children, but for only one level so ex the foaf_child_step
     * @param verb|null $vrb if set to filter the children by the relation type
     * @return phrase_list the phrase list of the direct children without th original list
     */
    function direct_children(?verb $vrb = null): phrase_list
    {
        $wrd_lst = $this->wrd_lst_all();
        $added_wrd_lst = $wrd_lst->direct_children($vrb);
        $added_phr_lst = $added_wrd_lst->phrase_lst();

        log_debug($added_phr_lst->dsp_id());
        return $added_phr_lst;
    }

    /**
     * @return phrase_list list of phrases that are related to this phrase list
     * e.g. for "ABB" and "Daimler" it will return "Company" (but not "ABB"???)
     */
    function is(): phrase_list
    {
        global $vrb_cac;
        $phr_lst = $this->foaf_parents($vrb_cac->get_verb(verbs::IS));
        log_debug($this->dsp_id() . ' is ' . $phr_lst->dsp_name());
        return $phr_lst;
    }

    /**
     * get the related phrase
     * e.g. for "City" it will return "Zurich", "Bern" and "Geneva"
     *
     * @return phrase_list a list of phrases that are related to this phrase list
     */
    function are(): phrase_list
    {
        global $vrb_cac;
        log_debug($this->dsp_id());
        $phr_lst = $this->all_children($vrb_cac->get_verb(verbs::IS));
        log_debug($this->dsp_id() . ' are ' . $phr_lst->dsp_id());
        $phr_lst->merge($this);
        log_debug($this->dsp_id() . ' merged into ' . $phr_lst->dsp_id());
        return $phr_lst;
    }

    /**
     * @returns phrase_list a list of phrases that are related to this phrase list
     */
    function contains(): phrase_list
    {
        global $vrb_cac;
        $phr_lst = $this->all_children($vrb_cac->get_verb(verbs::PART_NAME));
        $phr_lst->merge($this);
        log_debug($this->dsp_id() . ' contains ' . $phr_lst->name());
        return $phr_lst;
    }


    /*
     * information
     */

    /**
     * @returns bool true if the phrase list is empty
     */
    function empty(): bool
    {
        if ($this->count() <= 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return string a unique id of the phrase list
     */
    function id(): string
    {
        $result = 'null';
        $id_lst = $this->id_lst();
        if ($this->count() > 0) {
            asort($id_lst);
            $result = implode(",", $id_lst);
        }
        return $result;
    }

    /**
     * @return phrase_list with all phrases that does not yet have a database id
     */
    function missing_ids(): phrase_list
    {
        $phr_lst = new phrase_list($this->user());
        foreach ($this->lst() as $phr) {
            if ($phr->id() == 0) {
                $phr_lst->add_by_name($phr);
            }
        }
        return $phr_lst;
    }


    /**
     * @returns bool true if none of the phrase list id needs more than 16 bit
     *          the most often used phrases should have an id below 2^16 / 2 - 1 = 32767
     */
    function prime_only(): bool
    {
        $result = true;
        foreach ($this->lst() as $phr) {
            if ($phr->id() > 32767 or $phr->id() < -32767) {
                $result = false;
            }
        }
        return $result;
    }

    /**
     * @returns bool true if at least one id is positive or not used to avoid exeeding PHP_INT_MAX
     */
    function one_positiv(): bool
    {
        $result = false;
        foreach ($this->lst() as $phr) {
            if ($phr->id() > 0) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * get the phrase ids as an array
     * switch to ids() if possible
     * @return array with the ids of theis phrase list
     */
    function id_lst(): array
    {
        return $this->phrase_ids()->lst;
    }

    /**
     * get the phrase ids as an array
     * switch to ids() if possible
     * @return array with the ids of theis phrase list
     */
    function obj_id_lst(): array
    {
        $result = array();
        if (count($this->lst()) > 0) {
            foreach ($this->lst() as $phr) {
                $result[] = $phr->id();
            }
        }
        return $result;
    }

    /**
     * @return phr_ids with the sorted phrase ids where a triple has a negative id
     */
    function phrase_ids(): phr_ids
    {
        $lst = array();
        if (count($this->lst()) > 0) {
            foreach ($this->lst() as $phr) {
                // use only valid ids
                if ($phr->id() <> 0) {
                    if (!array_key_exists($phr->id(), $lst)) {
                        $lst[] = $phr->id();
                    }
                }
            }
        }
        asort($lst);
        return (new phr_ids($lst));
    }

    /**
     * @return array with the word ids
     */
    function wrd_ids(): array
    {
        $result = array();
        if (count($this->lst()) > 0) {
            foreach ($this->lst() as $phr) {
                // use only valid word ids
                if ($phr->is_word()) {
                    $result[] = $phr->obj_id();
                }
            }
        }
        asort($result);
        return $result;
    }

    /**
     * @return array with the triple ids (converted from the negative phrase ids)
     */
    function trp_ids(): array
    {
        $result = array();
        if (count($this->lst()) > 0) {
            foreach ($this->lst() as $phr) {
                // use only valid triple ids
                if (!$phr->is_word()) {
                    $result[] = $phr->obj_id();
                }
            }
        }
        asort($result);
        return $result;
    }

    /**
     * return an url with the phrase ids
     * the order of the ids is used to sort the phrases for the user
     */
    function id_url(): string
    {
        $result = '';
        if (count($this->lst()) > 0) {
            $result = '&phrases=' . implode(",", $this->id_lst());
        }
        return $result;
    }

    /**
     * the old long form to encode
     */
    function id_url_long(): string
    {
        $lib = new library();
        return $lib->ids_to_url($this->id_lst(), "phrase");
    }

    /**
     * @returns bool true if all phrases of the list have a name and an id
     */
    function loaded(?phr_ids $ids = null): bool
    {
        $result = true;
        if ($ids != null) {
            $ids_to_load = $ids->lst;
            $ids_loaded = $this->id_lst();
            $ids_to_load = array_diff($ids_to_load, $ids_loaded);
            if (count($ids_to_load) > 0) {
                $result = false;

            }
        }
        foreach ($this->lst() as $phr) {
            if ($phr->id() == 0 or $phr->name() == '') {
                $result = false;
            }
        }
        return $result;
    }

    /**
     * makes sure that all combinations of "are" and "contains" are included
     * @return phrase_list with the additional are and contains phrases
     */
    function are_and_contains(): phrase_list
    {
        log_debug('phrase_list->are_and_contains for ' . $this->dsp_id());

        // this first time get all related items
        $phr_lst = clone $this;
        $phr_lst = $phr_lst->are();
        $phr_lst = $phr_lst->contains();
        $added_lst = clone $phr_lst;
        $added_lst->diff($this);
        // ... and after that get only for the new
        if ($added_lst->count() > 0) {
            $loops = 0;
            log_debug('added ' . $added_lst->dsp_id() . ' to ' . $phr_lst->name());
            do {
                $next_lst = clone $added_lst;
                $next_lst = $next_lst->are();
                $added_lst = $next_lst->contains();
                $added_lst->diff($phr_lst);
                if ($added_lst->count() > 0) {
                    log_debug('add ' . $added_lst->dsp_id() . ' to ' . $phr_lst->name());
                }
                $phr_lst->merge($added_lst);
                $loops++;
            } while ($added_lst->count() > 0 and $loops < MAX_LOOP);
        }
        log_debug($this->dsp_id() . ' are_and_contains ' . $phr_lst->name());
        return $phr_lst;
    }

    /**
     * add all potential differentiator phrases of the phrase lst e.g. get "energy" for "sector"
     */
    function differentiators(): phrase_list
    {
        global $vrb_cac;
        log_debug('for ' . $this->dsp_id());
        $phr_lst = $this->all_children($vrb_cac->get_verb(verbs::CAN_CONTAIN));
        log_debug('merge ' . $this->dsp_id());
        $this->merge($phr_lst);
        log_debug($phr_lst->dsp_id() . ' for ' . $this->dsp_id());
        return $phr_lst;
    }

    /**
     * same as differentiators, but including the subtypes e.g. get "energy" and "wind energy" for "sector" if "wind energy" is part of "energy"
     */
    function differentiators_all(): phrase_list
    {
        global $vrb_cac;
        log_debug('for ' . $this->dsp_id());
        // this first time get all related items
        $phr_lst = $this->all_children($vrb_cac->get_verb(verbs::CAN_CONTAIN));
        $phr_lst = $phr_lst->are();
        $added_lst = $phr_lst->contains();
        $added_lst->diff($this);
        // ... and after that get only for the new
        if ($added_lst->count() > 0) {
            $loops = 0;
            log_debug('added ' . $added_lst->dsp_id() . ' to ' . $phr_lst->name());
            do {
                $next_lst = $added_lst->all_children($vrb_cac->get_verb(verbs::CAN_CONTAIN));
                $next_lst = $next_lst->are();
                $added_lst = $next_lst->contains();
                $added_lst->diff($phr_lst);
                if ($added_lst->count() > 0) {
                    log_debug('add ' . $added_lst->name() . ' to ' . $phr_lst->name());
                }
                $phr_lst->merge($added_lst);
                $loops++;
            } while ($added_lst->count() > 0 and $loops < MAX_LOOP);
        }
        log_debug($phr_lst->name() . ' for ' . $this->dsp_id());
        return $phr_lst;
    }

    /**
     * similar to differentiators, but only a filtered list of differentiators is viewed to increase speed
     */
    function differentiators_filtered($filter_lst): phrase_list
    {
        log_debug('for ' . $this->dsp_id());
        $result = $this->differentiators_all();
        $result = $result->del_list($filter_lst);
        log_debug($result->dsp_id());
        return $result;
    }


    /*
     * check
     */

    /**
     * @return bool true if this phrase list has at least one entry
     */
    function is_valid(): bool
    {
        $result = true;
        if ($this->count() <= 0) {
            $result = false;
        }
        return $result;
    }


    /*
     * modify
     */

    /**
     * add a list of words to the phrase list, but only if it is not yet part of the phrase list
     *
     * @param word_list|null $wrd_lst_to_add the list of words to add as a word list object
     * @returns bool true is at least one word has been added
     */
    function add_wrd_lst(?word_list $wrd_lst_to_add): bool
    {
        $result = false;
        // check parameters
        if ($wrd_lst_to_add != null) {
            if ($wrd_lst_to_add->lst() != null) {
                foreach ($wrd_lst_to_add->lst() as $wrd) {
                    if ($this->add($wrd->phrase())) {
                        $result = true;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * add a list of triples to the phrase list, but only if it is not yet part of the phrase list
     *
     * @param word_list|null $trp_lst_to_add the list of words to add as a word list object
     * @returns bool true is at least one word has been added
     */
    function add_trp_lst(?triple_list $trp_lst_to_add): bool
    {
        $result = false;
        // check parameters
        if ($trp_lst_to_add != null) {
            if ($trp_lst_to_add->lst() != null) {
                foreach ($trp_lst_to_add->lst() as $trp) {
                    if ($this->add($trp->phrase())) {
                        $result = true;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * add one phrase by the id to the phrase list, but only if it is not yet part of the phrase list
     * the new phrases are not loaded from the database, which should be done later if required
     * @param int $phr_id_to_add the id that should be added
     */
    function add_id(int $phr_id_to_add): void
    {
        log_debug('phrase_list->add_id (' . $phr_id_to_add . ')');
        if ($phr_id_to_add <> 0) {
            if (!in_array($phr_id_to_add, $this->id_lst())) {
                $phr_to_add = new phrase($this->user());
                $phr_to_add->set_id($phr_id_to_add);

                $this->add($phr_to_add);
            }
        }
    }

    /**
     * add one phrase to the phrase list defined by the phrase name
     */
    function add_name($phr_name_to_add): void
    {
        log_debug('phrase_list->add_name "' . $phr_name_to_add . '"');
        if (is_null($this->user()->id())) {
            log_err("The user must be set.", "phrase_list->add_name");
        } else {
            $phr_to_add = new phrase($this->user());
            $phr_to_add->load_by_name($phr_name_to_add);

            if ($phr_to_add->id() <> 0) {
                $this->add($phr_to_add);
            } else {
                log_err('"' . $phr_name_to_add . '" not found.', "phrase_list->add_name");
            }
        }
        log_debug('added "' . $phr_name_to_add . '" to ' . $this->dsp_id() . ')');
    }

    /**
     * del one phrase to the phrase list, but only if it is not yet part of the phrase list
     * @param phrase $phr_to_del the phrase that should be removed from the list
     */
    function del(phrase $phr_to_del): void
    {
        log_debug($phr_to_del->dsp_id());
        $phr_ids = $this->id_lst();
        if (count($phr_ids) > 0) {
            if (in_array($phr_to_del->id(), $phr_ids)) {
                $del_pos = array_search($phr_to_del->id(), $phr_ids);
                if ($this->get($del_pos)->id() == $phr_to_del->id()) {
                    unset($this->lst()[$del_pos]);
                } else {
                    log_err('Remove of ' . $phr_to_del->dsp_id() . ' failed');
                }
            }
        }
    }

    /**
     * merge as a function, because the array_merge does not create an object
     */
    function merge($new_phr_lst): phrase_list
    {
        log_debug($new_phr_lst->dsp_id() . ' to ' . $this->dsp_id());
        if (!$new_phr_lst->is_empty()) {
            foreach ($new_phr_lst->lst() as $new_phr) {
                $this->add($new_phr);
            }
        }
        return $this;
    }

    /**
     * merge as a function, because the array_merge does not create an object
     * @return phrase_list this all phrases of this and the given list
     */
    function merge_by_name($new_phr_lst): phrase_list
    {
        log_debug($new_phr_lst->dsp_id() . ' to ' . $this->dsp_id());
        if (!$new_phr_lst->is_empty()) {
            foreach ($new_phr_lst->lst() as $new_phr) {
                $this->add_by_name($new_phr);
            }
        }
        return $this;
    }

    /**
     * remove a list of phrases from this phrase list
     * e.g. out of "2014", "2015", "2016", "2017"
     *      with the filter "2016", "2017","2018"
     *      the result is "2016", "2017"
     * @param phrase_list $filter_lst a phrase list with the phrases that should be removed from this list
     * @returns phrase_list list a phrase excluding the given phrases
     */
    function del_list(phrase_list $filter_lst): phrase_list
    {
        $result = clone $this;

        // check and adjust the parameters
        if (get_class($filter_lst) == word_list::class) {
            $filter_phr_lst = $filter_lst->phrase_lst();
        } else {
            $filter_phr_lst = $filter_lst;
        }
        if (!isset($filter_phr_lst)) {
            log_err('Phrases to delete are missing.', 'phrase_list->diff');
        }
        if (get_class($filter_phr_lst) <> phrase_list::class) {
            log_err(get_class($filter_phr_lst) . ' cannot be used to delete phrases.', 'phrase_list->diff');
        }

        if (!$result->is_empty()) {
            $phr_lst = array();
            $lst_ids = $filter_phr_lst->id_lst();
            foreach ($result->lst() as $phr) {
                if (in_array($phr->id(), $lst_ids)) {
                    $phr_lst[] = $phr;
                }
            }
            $result->set_lst($phr_lst);
            log_debug($result->dsp_id());
        }
        return $result;
    }

    /**
     * filters a phrase list by an id list
     * e.g. out of "2014 (1)", "2015 (2)", "2016 (3)", "2017 (4)"
     *      with the filter 2, 3
     *      the result is "2015 (2)", "2016 (3)"
     * @param phr_ids $id_lst a list with the phrase ids that should be used from this list
     * @returns phrase_list list a phrase excluding the given phrases
     */
    function filter_by_ids(phr_ids $id_lst): phrase_list
    {
        $result = clone $this;

        if (empty($id_lst->lst)) {
            // return an empty list
            $result = new phrase_list($this->user());
        } else {
            $result = clone $this;
            $phr_lst = array();
            $lst_ids = $id_lst->lst;
            foreach ($result->lst() as $phr) {
                if (in_array($phr->id(), $lst_ids)) {
                    $phr_lst[] = $phr;
                }
            }
            $result->set_lst($phr_lst);
            log_debug($result->dsp_id());
        }
        return $result;
    }

    /**
     * leave only the valid words and triples in this list
     * @return void
     */
    function filter_valid(): void
    {
        $lst = [];
        foreach ($this->lst() as $phr) {
            if ($phr->is_valid()) {
                $lst[] = $phr;
            }
        }
        $this->set_lst($lst);
    }

    /**
     * diff as a function, because the array_diff does not seem to work for an object list
     *
     * e.g. for "2014", "2015", "2016", "2017"
     * and delete list of "2016", "2017","2018"
     * the result is "2014", "2015"
     *
     * @param phrase_list $del_lst is the list of phrases that should be removed from this list object
     */
    function get_diff(phrase_list $del_lst): phrase_list
    {
        log_debug('phrase_list->diff of ' . $del_lst->dsp_id() . ' and ' . $this->dsp_id());

        $result = clone new phrase_list($this->user());
        if (!$this->is_empty()) {
            $lst_ids = $del_lst->id_lst();
            foreach ($this->lst() as $phr) {
                if (!in_array($phr->id(), $lst_ids)) {
                    $result->add($phr);
                }
            }
        }
        return $result;
    }

    /**
     * diff as a function, because the array_diff does not seem to work for an object list
     *
     * e.g. for "2014", "2015", "2016", "2017"
     * and delete list of "2016", "2017","2018"
     * the result is "2014", "2015"
     *
     * @param phrase_list $del_lst is the list of phrases that should be removed from this list object
     */
    function diff(phrase_list $del_lst): void
    {
        log_debug('phrase_list->diff of ' . $del_lst->dsp_id() . ' and ' . $this->dsp_id());

        if (!$this->is_empty()) {
            $result = array();
            $lst_ids = $del_lst->id_lst();
            foreach ($this->lst() as $phr) {
                if (!in_array($phr->id(), $lst_ids)) {
                    $result[] = $phr;
                }
            }
            $this->set_lst($result);
        }

        log_debug($this->dsp_id());
    }

    /**
     * same as diff but sometimes this name looks better
     */
    function not_in(phrase_list $del_phr_lst): void
    {
        log_debug('phrase_list->not_in get out of ' . $this->dsp_name() . ' not in ' . $del_phr_lst->name() . ')');
        $this->diff($del_phr_lst);
    }
    /*
    // keep only those phrases in the list that are not in the list to delete
    // e.g. for "2014", "2015", "2016", "2017" and the exclude list of "2016", "2017","2018" the result is "2014", "2015"
    function not_in($del_phr_lst) {
      zu_debug('phrase_list->not_in');
      foreach ($this->lst() AS $phr) {
        if ($phr->id() != 0) {
          if (in_array($phr->id, $del_phr_lst->ids)) {
            $del_pos = array_search($phr->id, $this->ids);
            zu_debug('phrase_list->not_in -> to exclude ('.$this->get($del_pos(->name.')');
            unset ($this->lst[$del_pos]);
            unset ($this->ids[$del_pos]);
          }
        }
      }
      zu_debug('phrase_list->not_in -> '.$this->dsp_id());
    }
    */

    /**
     * similar to diff, but using an id array to exclude instead of a phrase list object
     */
    function diff_by_ids($del_phr_ids): void
    {
        $this->id_lst();
        foreach ($del_phr_ids as $del_phr_id) {
            if ($del_phr_id > 0) {
                log_debug('phrase_list->diff_by_ids ' . $del_phr_id);
                if ($del_phr_id > 0 and in_array($del_phr_id, $this->id_lst())) {
                    $del_pos = array_search($del_phr_id, $this->id_lst());
                    unset($this->lst()[$del_pos]);
                }
            }
        }
        //$this->ids = array_diff($this->ids, $del_phr_ids);
        log_debug($this->dsp_id());
    }

    /**
     * look at a phrase list and remove the general phrase, if there is a more specific phrase also part of the list e.g. remove "Country", but keep "Switzerland"
     */
    function keep_only_specific(): array
    {
        log_debug('phrase_list->keep_only_specific (' . $this->dsp_id());
        $lib = new library();

        $result = $this->id_lst();
        foreach ($this->lst() as $phr) {
            // temp workaround utils the reason is found, why the user is sometimes not set
            if (!isset($phr->usr)) {
                $phr->set_user($this->user());
            }
            $phr_lst_is = $phr->is();
            if (isset($phr_lst_is)) {
                if (!empty($phr_lst_is->ids)) {
                    $result = $lib->lst_not_in($result, $phr_lst_is->ids);
                    log_debug($phr->name() . ' is of type ' . $phr_lst_is->dsp_id());
                }
            }
        }

        log_debug(implode(",", $result));
        return $result;
    }

    /**
     * @return bool true if a phrase lst contains a time phrase
     */
    function has_time(): bool
    {
        $result = false;
        // loop over the phrase ids and add only the time ids to the result array
        foreach ($this->lst() as $phr) {
            log_debug('check (' . $phr->name() . ')');
            if ($result == false) {
                if ($phr->is_time()) {
                    $result = true;
                }
            }
        }
        log_debug(zu_dsp_bool($result));
        return $result;
    }

    /**
     * @return bool true if a phrase lst contains a measure phrase
     */
    function has_measure(): bool
    {
        log_debug('for ' . $this->dsp_id());
        $result = false;
        // loop over the phrase ids and add only the time ids to the result array
        foreach ($this->lst() as $phr) {
            log_debug('check ' . $phr->dsp_id());
            if ($result == false) {
                if ($phr->is_measure()) {
                    $result = true;
                }
            }
        }
        log_debug(zu_dsp_bool($result));
        return $result;
    }

    /**
     * @return bool true if a phrase lst contains a scaling phrase
     */
    function has_scaling(): bool
    {
        $result = false;
        // loop over the phrase ids and add only the time ids to the result array
        foreach ($this->lst() as $phr) {
            log_debug('check ' . $phr->dsp_id());
            if ($result == false) {
                if ($phr->is_scaling()) {
                    $result = true;
                }
            }
        }
        log_debug(zu_dsp_bool($result));
        return $result;
    }

    /**
     * @return bool true if a phrase lst contains a percent scaling phrase, which is used for a predefined formatting of the value
     */
    function has_percent(): bool
    {
        $result = false;
        // loop over the phrase ids and add only the time ids to the result array
        foreach ($this->lst() as $phr) {
            // temp solution for testing
            $phr->set_user($this->user());
            log_debug('check ' . $phr->dsp_id());
            if ($result == false) {
                if ($phr->is_percent()) {
                    $result = true;
                }
            }
        }
        log_debug(zu_dsp_bool($result));
        return $result;
    }

    /**
     * get all phrases of this phrase list that have at least one time term
     * TODO to be replaced by time_lst
     * @return array of time phrases
     */
    function time_lst_old(): array
    {
        global $phr_typ_cac;

        log_debug($this->dsp_id());

        $result = array();
        $time_type = $phr_typ_cac->id(phrase_type_shared::TIME);
        // loop over the phrase ids and add only the time ids to the result array
        foreach ($this->lst() as $phr) {
            if ($phr->type_id() == $time_type) {
                $result[] = $phr;
            }
        }
        //zu_debug('phrase_list->time_lst_old -> ('.zu_lst_dsp($result).')');
        return $result;
    }

    /**
     * get all words of this phrase list that have at least one time term
     * TODO use a phrase list instead of a word list because the same word can be of type time and id
     * @return word_list the list object of the time words (not the time phrases!)
     */
    function time_word_list(): word_list
    {
        $wrd_lst = $this->wrd_lst_all();
        $result = $wrd_lst->time_lst();
        $result->set_user($this->user());
        return $result;
    }

    function time_list(): phrase_list
    {
        $lst = new phrase_list($this->user());
        foreach ($this->lst() as $phr) {
            if ($phr->is_time()) {
                $lst->add($phr);
            }
        }
        return $lst;
    }

    /**
     * @param string $phr_typ code_id of the type that should be selected
     * @return phrase_list
     */
    function get_by_type(string $phr_typ): phrase_list
    {
        $lst = new phrase_list($this->user());
        foreach ($this->lst() as $phr) {
            if ($phr->is_type($phr_typ)) {
                $lst->add_by_name($phr);
            }
        }
        return $lst;
    }

    /**
     * @param string $phr_typ code_id of the type that should be selected
     * @return array
     */
    function get_names_by_type(string $phr_typ): array
    {
        return $this->get_by_type($phr_typ)->names();
    }

    /**
     * @return phrase with the most useful time phrase
     */
    function time_useful(): ?phrase
    {
        log_debug('phrase_list->time_useful for ' . $this->dsp_name());

        $result = null;

        $wrd_lst = $this->wrd_lst_all();
        $time_wrds = $wrd_lst->time_lst();
        log_debug('phrase_list->time_useful times ');
        log_debug('phrase_list->time_useful times ' . implode(",", $time_wrds->ids()));
        foreach ($time_wrds->ids() as $time_id) {
            if (is_null($result)) {
                $time_wrd = new word($this->user());
                $time_wrd->load_by_id($time_id);
                // return a phrase not a word because "Q1" can be also a wikidata Qualifier and to differentiate this, "Q1 (Quarter)" should be returned
                $result = $time_wrd->phrase();
            } else {
                log_warning("The word list contains more time word than supported by the program.", "phrase_list->time_useful");
            }
        }
        //$result = zu_lst_to_flat_lst($phrase_lst);
        //$result = clone $this;
        //$result->wlsort();
        //$result = $phrase_lst;
        //asort($result);
        // sort
        //print_r($phrase_lst);

        // get the most often time type e.g. years if the list contains more than 5 years
        //$type_most_used = zut_time_type_most_used ($phrase_lst);

        // if nothing special is defined try to select 20 % outlook to the future
        // get the latest time without estimate
        // check the number of none estimate results
        // if the hist is longer than it should be, define the start phrase
        // fill from the start phrase the default number of phrases


        //zu_debug('phrase_list->time_useful -> '.$result->name());
        return $result;
    }

    /**
     * get the most useful time for the given words
     * TODO: review
     * @param term_list|null $trm_lst a list of preloaded terms that should be used for the transformation
     * @return phrase|null with the most useful time phrase
     */
    function assume_time(?term_list $trm_lst = null): ?phrase
    {
        $time_phr = null;
        $wrd_lst = $this->wrd_lst_all();
        $time_wrd = $wrd_lst->assume_time($trm_lst);
        if (isset($time_wrd)) {
            $time_phr = $time_wrd;
        }
        return $time_phr;
    }

    /**
     * filter the measure phrases out of the list of phrases
     * @return phrase_list with the measure phrases
     */
    function measure_lst(): phrase_list
    {
        global $phr_typ_cac;
        log_debug('phrase_list->measure_lst(' . $this->dsp_id());
        $lib = new library();

        $result = new phrase_list($this->user());
        $measure_type = $phr_typ_cac->id(phrase_type_shared::MEASURE);
        // loop over the phrase ids and add only the time ids to the result array
        foreach ($this->lst() as $phr) {
            if (get_class($phr) <> phrase::class and get_class($phr) <> word::class) {
                log_warning('The phrase list contains ' . $this->dsp_id() . ' of type ' . get_class($phr) . ', which is not supposed to be in the list.', 'phrase_list->measure_lst');
                log_debug('phrase_list->measure_lst contains object ' . get_class($phr) . ', which is not a phrase');
            } else {
                if ($phr->type_id() == $measure_type) {
                    $result->add($phr);
                    log_debug('found (' . $phr->name() . ')');
                } else {
                    log_debug($phr->name() . ' has type id ' . $phr->type_id() . ', which is not the measure type id ' . $measure_type);
                }
            }
        }
        log_debug($lib->dsp_count($result->lst()));
        return $result;
    }

    /**
     * filter the scaling phrases out of the list of phrases
     * @return phrase_list with the scaling phrases
     */
    function scaling_lst(): phrase_list
    {
        global $phr_typ_cac;

        log_debug('phrase_list->scaling_lst(' . $this->dsp_id());
        $lib = new library();

        $result = new phrase_list($this->user());
        $scale_type = $phr_typ_cac->id(phrase_type_shared::SCALING);
        $scale_hidden_type = $phr_typ_cac->id(phrase_type_shared::SCALING_HIDDEN);
        // loop over the phrase ids and add only the time ids to the result array
        foreach ($this->lst() as $phr) {
            if ($phr->type_id() == $scale_type or $phr->type_id() == $scale_hidden_type) {
                $result->add($phr);
                log_debug('found (' . $phr->name() . ')');
            } else {
                log_debug('not found (' . $phr->name() . ')');
            }
        }
        log_debug($lib->dsp_count($result->lst()));
        return $result;
    }

    /**
     * Exclude all time phrases out of the list of phrases
     */
    function ex_time(): void
    {
        log_debug($this->dsp_id());
        $del_wrd_lst = $this->time_word_list();
        $del_phr_lst = $del_wrd_lst->phrase_lst();
        $this->diff($del_phr_lst);
    }

    /**
     * Exclude all measure phrases out of the list of phrases
     */
    function ex_measure(): void
    {
        $del_phr_lst = $this->measure_lst();
        $this->diff($del_phr_lst);
        log_debug($this->dsp_name() . ' (exclude measure ' . $del_phr_lst->dsp_name() . ')');
    }

    /**
     * Exclude all scaling phrases out of the list of phrases
     */
    function ex_scaling(): void
    {
        $del_phr_lst = $this->scaling_lst();
        $this->diff($del_phr_lst);
        log_debug($this->dsp_name() . ' (exclude scaling ' . $del_phr_lst->dsp_name() . ')');
    }

    /**
     * sort the phrase object list by name
     * TODO use the sort function of the named list
     * @return array list with the phrases (not a phrase list object!) sorted by name
     */
    function name_sort(): array
    {
        log_debug($this->dsp_id() . ' and user ' . $this->user()->name);
        $lib = new library();

        $name_lst = array();
        $result = array();
        $pos = 0;
        foreach ($this->lst() as $phr) {
            $name_lst[$pos] = $phr->name();
            $pos++;
        }
        asort($name_lst);
        log_debug('sorted "' . implode('","', $name_lst) . '" (' . $lib->dsp_array(array_keys($name_lst)) . ')');
        foreach (array_keys($name_lst) as $sorted_id) {
            log_debug('get ' . $sorted_id);
            $phr_to_add = $this->get($sorted_id);
            log_debug('got ' . $phr_to_add->name());
            $result[] = $phr_to_add;
        }
        // check
        if ($this->count() <> count($result)) {
            log_err("Sorting changed the number of phrases from " . $lib->dsp_count($this->lst()) . " to " . $lib->dsp_count($result) . ".", "phrase_list->wlsort");
        } else {
            $this->set_lst($result);
            $this->id_lst();
        }
        log_debug('sorted ' . $this->dsp_id());
        return $result;
    }

    /**
     * sort the phrase object list by id
     * @return phrase_list with the phrases (not a phrase list object!) sorted by name
     */
    function sort_by_id(): phrase_list
    {
        $result = clone $this;
        $id_lst = $this->id_lst();
        asort($id_lst);
        $result->set_lst(array());
        foreach (array_keys($id_lst) as $sorted_id) {
            $phr_to_add = $this->get($sorted_id);
            $result->add($phr_to_add);
        }
        return $result;
    }

    /**
     * sort the phrase object list by id in reverse order
     * @return phrase_list with the phrases (not a phrase list object!) sorted by name
     */
    function sort_rev_by_id(): phrase_list
    {
        $result = clone $this;
        $id_lst = $this->id_lst();
        arsort($id_lst);
        $result->set_lst(array());
        foreach (array_keys($id_lst) as $sorted_id) {
            $phr_to_add = $this->get($sorted_id);
            $result->add($phr_to_add);
        }
        return $result;
    }

    /**
     * get the last time phrase of the phrase list
     * @return phrase with the last phrase of the type time
     */
    function max_time(): phrase
    {
        log_debug($this->dsp_id() . ' and user ' . $this->user()->name);
        $max_phr = new phrase($this->user());
        if (!$this->is_empty()) {
            foreach ($this->lst() as $phr) {
                // to be replaced by "is following"
                if ($phr->name() > $max_phr->name()) {
                    log_debug('select (' . $phr->name() . ' instead of ' . $max_phr->name() . ')');
                    $max_phr = clone $phr;
                }
            }
        }
        return $max_phr;
    }

    /**
     * @return group|null the group with only the id set based to this list or null if no group matches
     */
    function get_grp_id(bool $do_save = true): ?group
    {
        $grp = null;
        if ($this->is_empty()) {
            log_err('Cannot create phrase group for an empty list.', 'phrase_list->get_grp');
        } else {
            $grp = new group($this->user());
            $grp_id = new group_id();
            $grp->set_id($grp_id->get_id($this));
            $grp->set_phrase_list(clone $this);
        }
        return $grp;
    }

    /**
     * @return array all phrases that are part of each phrase group of the list
     */
    function common(phrase_list $filter_lst): array
    {
        $result = array();
        $lib = new library();
        log_debug('of ' . $this->dsp_name() . ' and ' . $filter_lst->name());
        if (count($this->lst()) > 0) {
            foreach ($this->lst() as $phr) {
                if (isset($phr)) {
                    log_debug('check if "' . $phr->name() . '" is in ' . $filter_lst->name());
                    if (in_array($phr, $filter_lst->lst())) {
                        $result[] = $phr;
                    }
                }
            }
            $this->set_lst($result);
            $this->id_lst();
        }
        log_debug($lib->dsp_count($this->lst()));
        return $result;
    }

    /**
     * @return phrase_list the combined list of this list and the given list without changing this phrase list
     */
    function concat_unique($join_phr_lst): phrase_list
    {
        log_debug();
        $lib = new library();
        $result = clone $this;
        foreach ($join_phr_lst->lst as $phr) {
            if (!in_array($phr, $result->lst())) {
                $result->add_by_name($phr);
            }
        }
        log_debug($lib->dsp_count($result->lst()));
        return $result;
    }


    /*
     * data request function
     */

    /**
     * @return value_list all values related to this phrase list
     */
    function val_lst(): value_list
    {
        $val_lst = new value_list($this->user());
        $val_lst->load_by_phr_lst($this, true);

        return $val_lst;
    }

    /**
     * @return formula_list all formulas related to this phrase list
     */
    function frm_lst(): formula_list
    {
        $frm_lst = new formula_list($this->user());
        $frm_lst->load_by_phr_lst($this);

        return $frm_lst;
    }


    /**
     * get the best matching value or value list for this phrase list
     * e.g. if for "ABB", "sales" no direct number is found,
     *   1) try to get a formula result, if also no formula result,
     *   2) assume an additional phrase by getting the phrase with the most values for the phrase list
     *      which could be in this case "millions"
     *   3) repeat with 2)
     *
     * e.g. if many numbers matches the phrase list e.g. Nestlé sales million, CHF (and Water, and Coffee)
     *      the value with the least additional phrases is selected
     *
     * @return value the best matching value
     */
    function value(): value
    {
        $val = new value($this->user());
        $val->load_by_grp($this->get_grp_id());

        return $val;
    }

    /**
     * @return value the best matching value scaled to one
     */
    function value_scaled(): value
    {
        $val = $this->value();
        $wrd_lst = $this->wrd_lst_all();
        $val->set_number($val->scale($wrd_lst));

        return $val;
    }


    /*
     * save
     */

    /**
     * save all changes of the phrase list to the database
     * TODO speed up by creation one SQL statement
     *
     * @return user_message the message that should be shown to the user if something went wrong
     */
    function save(): user_message
    {
        $usr_msg = new user_message();

        // get the phrase names that are already in the database
        $db_lst = clone $this;
        $db_lst->reset();
        $db_lst->load_by_names($this->names());

        // create a list of phrase that needs to be added and that needs to be updated
        $add_lst = clone $this;
        $add_lst->reset();
        $chg_lst = clone $this;
        $chg_lst->reset();
        foreach ($this->lst() as $phr) {
            $db_phr = $db_lst->get_by_name($phr->name());
            if ($db_phr == null) {
                $add_lst->add_by_name($phr);
            } else {
                if ($phr->needs_db_update($db_phr)) {
                    $chg_lst->add_by_name($phr);
                }
            }
        }

        // add the missing phrase
        foreach ($add_lst->lst() as $phr) {
            $usr_msg->add($phr->save());
        }
        // update the phrase that are needed
        foreach ($chg_lst->lst() as $phr) {
            $usr_msg->add($phr->save());
        }

        return $usr_msg;
    }


    /*
     * display
     */

    /**
     * return one string with all names of the list with the link
     */
    function name_linked(): string
    {
        $result = '';
        foreach ($this->lst() as $phr) {
            if ($phr != null) {
                if ($result != '') {
                    $result .= ', ';
                }
                $result .= $phr->name_linked();
            }
        }
        return $result;
    }

    /**
     * @return string one string with all names of the list and reduced in size mainly for debugging
     * this function is called from dsp_id, so no other call is allowed
     */
    function dsp_name(): string
    {
        global $debug;
        $lib = new library();

        $name_lst = $this->names();
        if ($debug > 10) {
            $result = '"' . implode('","', $name_lst) . '"';
        } else {
            $result = '"' . implode('","', array_slice($name_lst, 0, 7));
            if (count($name_lst) > 8) {
                $result .= ' ... total ' . $lib->dsp_count($this->lst());
            }
            $result .= '"';
        }

        return $result;
    }

    /**
     * @return string one string with all names of the list
     */
    function name(int $limit = null): string
    {
        $result = '';
        if ($limit == null) {
            $limit = LIST_MIN_NAMES;
        }
        $lib = new library();
        $name_lst = $this->names();
        if (count($name_lst) <= $limit) {
            $result .= '"' . implode('","', $name_lst) . '"';
        } else {
            $result .= '"' . implode('","', array_slice($name_lst, 0, $limit - 1)) . '"';
            $result .= ' ... total ' . $lib->dsp_count($name_lst);
        }
        return $result;
    }

    /**
     * @return array with all phrase names in alphabetic order
     * this function is called from dsp_id, so no call of another function is allowed
     * TODO move to a parent object for phrase list and term list
     */
    function names(int $limit = null): array
    {
        $name_lst = array();
        foreach ($this->lst() as $phr) {
            if ($phr != null) {
                $name_lst[] = $phr->name();
            }
        }
        return $name_lst;
    }

    /**
     * @return bool true if the phrase is part of the phrase list
     */
    function does_contain($phr_to_check): bool
    {
        $result = false;

        foreach ($this->lst() as $phr) {
            if ($phr->id() == $phr_to_check->id()) {
                $result = true;
            }
        }

        return $result;
    }


    /*
     * review - to be moved to the sql creator
     */

    /**
     * load a list of phrases by a given phrase, verb and direction
     * e.g. for "Zurich" "is a" and "UP" the result is "Canton", "City" and "Company"
     *
     * @param phrase $phr the phrase which should be used for selecting the words or triples
     * @param verb|null $vrb if set to filter the selection
     * @param foaf_direction $direction to select either the parents, children or all related words ana triples
     * @return bool true if at least one triple found
     */
    function load_by_phr(
        phrase         $phr,
        ?verb          $vrb = null,
        foaf_direction $direction = foaf_direction::BOTH
    ): bool
    {
        $this->reset();

        $wrd_lst = new word_list($this->user());
        $wrd_lst->load_linked_words($vrb, $direction);
        $wrd_added = $this->add_wrd_lst($wrd_lst);

        $trp_lst = new triple_list($this->user());
        $trp_lst->load_by_phr($phr, $vrb, $direction);
        $trp_added = $this->add_trp_lst($trp_lst);

        if ($wrd_added or $trp_added) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * load the related phrases of a given type
     *
     * used to create a selector that contains the time words
     * @param phrase $phr the base phrase used for the selection
     *         e.g. "year" to show the years first
     *         or "next years" to show the future years
     *         or "past years" to show the last years
     */
    function load_by_phr_vrb_and_type(
        phrase         $phr,
        ?verb          $vrb = null,
        phrase_types   $wrd_types,
        foaf_direction $direction = foaf_direction::BOTH): phrase_list
    {
        $result = new phrase_list($this->user());
        /*
         * if ($pos > 0) {
            $field_name = "phrase" . $pos;
            //$field_name = "time".$pos;
        } else {
            $field_name = "phrase";
            //$field_name = "time";
        }
        //
        if ($type->id() > 0) {
            $sql_from = "triples l, words w";
            $sql_where_and = "AND w.word_id = l.from_phrase_id
                        AND l.verb_id = " . $vrb_cac->id(verbs::IS_A) . "
                        AND l.to_phrase_id = " . $type->id();
        } else {
            $sql_from = "words w";
            $sql_where_and = "";
        }
        $sql_avoid_code_check_prefix = "SELECT";
        $sql = $sql_avoid_code_check_prefix . " id, name
              FROM ( SELECT w.word_id AS id,
                            " . $db_con->get_usr_field("word_name", "w", "u", sql_db::FLD_FORMAT_TEXT, "name") . ",
                            " . $db_con->get_usr_field("excluded", "w", "u", sql_db::FLD_FORMAT_BOOL) . "
                       FROM " . $sql_from . "
                  LEFT JOIN user_words u ON u.word_id = w.word_id
                                        AND u.user_id = " . $this->user()->id() . "
                      WHERE w.phrase_type_id = " . cl(db_cl::WORD_TYPE, phrase_type_list::DBL_TIME) . "
                        " . $sql_where_and . "
                   GROUP BY name) AS s
            WHERE (excluded <> 1 OR excluded is NULL)
          ORDER BY name;";
        $sel = new html_selector;
        $sel->form = $form_name;
        $sel->name = $field_name;
        $sel->sql = $sql;
        $sel->selected = $this->id();
        $sel->dummy_text = '... please select';
        $result .= $sel->display();
         */
        return $result;
    }

    /**
     * create the sql statement to select the related phrases
     * the relation can be narrowed with a verb id
     *
     * @param sql_creator $sc the db connection object as a function parameter for unit testing
     * @param verb|null $vrb if set to select only phrases linked with this verb
     * @param foaf_direction $direction to define the link direction
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_linked_phrases(sql_creator $sc, ?verb $vrb, foaf_direction $direction): sql_par
    {
        $qp = $this->load_sql($sc, '');
        $join_field = '';
        if (count($this->lst()) <= 0) {
            log_warning('The phrase list is empty, so nothing could be found', self::class . "->load_sql_by_linked_type");
            $qp->name = '';
        } else {
            if ($direction == foaf_direction::UP) {
                $qp->name .= 'parents';
                $sc->add_where(triple::FLD_FROM, $this->ids(), sql_par_type::INT_LIST, sql_db::LNK_TBL);
                $join_field = triple::FLD_TO;
            } elseif ($direction == foaf_direction::DOWN) {
                $qp->name .= 'children';
                $sc->add_where(triple::FLD_TO, $this->ids(), sql_par_type::INT_LIST, sql_db::LNK_TBL);
                //$sql_where = sql_db::LNK_TBL . '.' . triple::FLD_TO . $sql_in . $db_con->par_name() . ')';
                $join_field = triple::FLD_FROM;
            } else {
                log_err('Unknown direction ' . $direction->value);
            }
            // verbs can have a negative id for the reverse selection
            if ($vrb != null) {
                $sc->add_where(verb::FLD_ID, $vrb->id(), null, sql_db::LNK_TBL);
                $qp->name .= '_verb_select';
            }
            $sc->set_join_fields(
                array(verb::FLD_ID),
                triple::class,
                phrase::FLD_ID,
                $join_field);
            $sc->set_name($qp->name);
            $qp->sql = $sc->sql();
            $qp->par = $sc->get_par();
        }

        return $qp;
    }

    /**
     * build a word list including the triple words or in other words flatten the list e.g. for parent inclusions
     * @return word_list with all words of the phrases split into single words
     */
    function wrd_lst_all(): word_list
    {
        log_debug('phrase_list->wrd_lst_all for ' . $this->dsp_id());

        $wrd_lst = new word_list($this->user());

        // check the basic settings
        if ($this->user() == null) {
            log_err('User for phrase list ' . $this->dsp_id() . ' missing', 'phrase_list->wrd_lst_all');
        }

        // fill the word list
        foreach ($this->lst() as $phr) {
            if ($phr->obj() == null) {
                log_err('Phrase ' . $phr->dsp_id() . ' could not be loaded', 'phrase_list->wrd_lst_all');
            } else {
                if ($phr->obj()->id() == 0) {
                    log_err('Phrase ' . $phr->dsp_id() . ' could not be loaded', 'phrase_list->wrd_lst_all');
                } else {
                    if ($phr->name() == '') {
                        $phr->load();
                        log_warning('Phrase ' . $phr->dsp_id() . ' needs unexpected reload', 'phrase_list->wrd_lst_all');
                    }
                    // TODO check if old can ge removed: if ($phr->id() > 0) {
                    if (get_class($phr->obj()) == word::class) {
                        $wrd_lst->add($phr->obj());
                    } elseif (get_class($phr->obj()) == triple::class) {
                        // use the recursive triple function to include the foaf words
                        $sub_wrd_lst = $phr->obj()->wrd_lst();
                        foreach ($sub_wrd_lst->lst() as $wrd) {
                            if ($wrd->name() == '') {
                                $wrd->load();
                                log_warning('Word ' . $wrd->dsp_id() . ' needs unexpected reload', 'phrase_list->wrd_lst_all');
                            }
                            $wrd_lst->add($wrd);
                        }
                    } else {
                        log_err('The phrase list ' . $this->dsp_id() . ' contains ' . $phr->obj()->dsp_id() . ', which is neither a word nor a phrase, but it is a ' . get_class($phr->obj), 'phrase_list->wrd_lst_all');
                    }
                }
            }
        }

        log_debug($wrd_lst->dsp_id());
        return $wrd_lst;
    }

    /**
     * get a word list from the phrase list
     * @return word_list list of the words from the phrase list
     */
    function words(): word_list
    {
        $wrd_lst = new word_list($this->user());
        foreach ($this->lst() as $phr) {
            if ($phr->is_word()) {
                $wrd_lst->add($phr->obj());
            }
        }
        return $wrd_lst;
    }

    /**
     * get a triple list from the phrase list
     * @return triple_list list of the triples from the phrase list
     */
    function triples(): triple_list
    {
        $trp_lst = new triple_list($this->user());
        foreach ($this->lst() as $phr) {
            if ($phr->is_triple()) {
                $trp_lst->add($phr->obj());
            }
        }
        return $trp_lst;
    }

    /**
     * get a triple list from the phrase list
     * @return triple_list list of the triples from the phrase list
     */
    function triples_by_name(): triple_list
    {
        $trp_lst = new triple_list($this->user());
        foreach ($this->lst() as $phr) {
            if ($phr->is_triple()) {
                $trp_lst->add_by_name($phr->obj());
            }
        }
        return $trp_lst;
    }

}


