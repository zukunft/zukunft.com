<?php

/*

    model/word/triple_list.php - a list of word links, mainly used to build a RDF graph
    --------------------------

    example:
    for company the list of linked words should contain "... has a balance sheet" and "... has a cash flow statement"

    related objects
    word list   - load a list of words that
                    are parents or children of a given word or linked to a group
                    or based on given ids or names
    phrase list - load a list of words or triples based on


    The main sections of this object are
    - construct and map: including the mapping of the db row to this triple object
    - cast:              create an api object and set the vars from an api json
    - load:              database access object (DAO) functions
    - sql:               to create sql statements e.g. for load word from the sql database
    - im- and export:    create an export object and set the vars from an import object
    - info:              functions to make code easier to read
    - convert:           more complex cast
    - parts:             get a list of the triple parts
    - save:              block wise insert or update of all triples in the database


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

use cfg\const\paths;

include_once paths::MODEL_SANDBOX . 'sandbox_list_named.php';
include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_par.php';
include_once paths::DB . 'sql_par_type.php';
include_once paths::MODEL_HELPER . 'combine_named.php';
include_once paths::MODEL_HELPER . 'data_object.php';
include_once paths::MODEL_IMPORT . 'import.php';
include_once paths::MODEL_PHRASE . 'phrase.php';
include_once paths::MODEL_PHRASE . 'phrase_list.php';
include_once paths::MODEL_SANDBOX . 'sandbox_link_named.php';
include_once paths::MODEL_SANDBOX . 'sandbox_named.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::MODEL_VERB . 'verb.php';
include_once paths::MODEL_VERB . 'verb_db.php';
include_once paths::MODEL_WORD . 'triple.php';
include_once paths::MODEL_WORD . 'word.php';
include_once paths::MODEL_WORD . 'word_list.php';
include_once paths::SHARED_CONST . 'triples.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED_ENUM . 'foaf_direction.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_TYPES . 'verbs.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'library.php';

use cfg\db\sql_creator;
use cfg\db\sql_db;
use cfg\db\sql_par;
use cfg\db\sql_par_type;
use cfg\helper\combine_named;
use cfg\helper\data_object;
use cfg\import\import;
use cfg\phrase\phrase;
use cfg\phrase\phrase_list;
use cfg\sandbox\sandbox_link_named;
use cfg\sandbox\sandbox_list_named;
use cfg\sandbox\sandbox_named;
use cfg\user\user;
use cfg\user\user_message;
use cfg\verb\verb;
use cfg\verb\verb_db;
use shared\const\triples;
use shared\const\words;
use shared\enum\foaf_direction;
use shared\enum\messages as msg_id;
use shared\library;
use shared\types\verbs;

class triple_list extends sandbox_list_named
{

    public array $lst; // the list of triples

    // fields to select a part of the graph (TODO deprecated)
    public array $ids = array();  // list of link ids
    public ?word $wrd = null;          // show the graph elements related to this word
    public ?word_list $wrd_lst = null; // show the graph elements related to these words
    public ?verb $vrb = null;     // show the graph elements related to this verb
    public foaf_direction $direction = foaf_direction::DOWN;  // either up, down or both


    /*
     * construct and map
     */

    /**
     * fill the triple list based on a database records
     * actually just add the single triple object to the parent function
     * TODO check that a similar function is used for all lists
     *
     * @param array $db_rows is an array of an array with the database values
     * @param bool $load_all force to include also the excluded phrases e.g. for admins
     * @return bool true if at least one formula link has been added
     */
    protected function rows_mapper(array $db_rows, bool $load_all = false): bool
    {
        return parent::rows_mapper_obj(new triple($this->user()), $db_rows, $load_all);
    }



    /*
     * load
     */

    /**
     * load a list of triple names
     * @param string $pattern the pattern to filter the triples
     * @param int $limit the number of rows to return
     * @param int $offset jump over these number of pages
     * @return bool true if at least one triple found
     */
    function load_names(string $pattern = '', int $limit = 0, int $offset = 0): bool
    {
        return parent::load_sbx_names(new triple($this->user()), $pattern, $limit, $offset);
    }

    /**
     * load a list of triples by the ids
     * @param array $wrd_ids a list of int values with the triple ids
     * @return bool true if at least one triple found
     */
    function load_by_ids(array $wrd_ids): bool
    {
        global $db_con;
        $qp = $this->load_sql_by_ids($db_con->sql_creator(), $wrd_ids);
        return $this->load($qp);
    }

    /**
     * load a list of triples by a phrase, verb and direction
     * @param phrase $phr the phrase which should be used for selecting the words or triples
     * @param verb|null $vrb if set to filter the selection
     * @param foaf_direction $direction to select either the parents, children or all related words ana triples
     * @return bool true if at least one triple found
     */
    function load_by_phr(
        phrase $phr, ?verb $vrb = null, foaf_direction $direction = foaf_direction::BOTH): bool
    {
        global $db_con;
        $qp = $this->load_sql_by_phr($db_con->sql_creator(), $phr, $vrb, $direction);
        return $this->load($qp);
    }

    /**
     * load a list of triples by a list of phrases, verb and direction
     * @param phrase_list $phr_lst the phrase which should be used for selecting the words or triples
     * @param verb|null $vrb if set to filter the selection
     * @param foaf_direction $direction to select either the parents, children or all related words ana triples
     * @return bool true if at least one triple found
     */
    function load_by_phr_lst(
        phrase_list $phr_lst, ?verb $vrb = null, foaf_direction $direction = foaf_direction::BOTH): bool
    {
        global $db_con;
        $qp = $this->load_sql_by_phr_lst($db_con->sql_creator(), $phr_lst, $vrb, $direction);
        return $this->load($qp);
    }

    /**
     * load this list of triples
     * @param sql_par $qp the SQL statement, the unique name of the SQL statement and the parameter list
     * @param bool $load_all force to include also the excluded triples e.g. for admins
     * @return bool true if at least one triple found
     */
    protected function load(sql_par $qp, bool $load_all = false): bool
    {
        global $db_con;

        $result = false;

        if ($qp->name == '') {
            log_err('The query name cannot be created to load a ' . self::class, self::class . '->load');
        } else {
            $this->reset();
            $db_rows = $db_con->get($qp);
            if ($db_rows != null) {
                foreach ($db_rows as $db_row) {
                    $trp = new triple($this->user());
                    $trp->row_mapper_sandbox($db_row);
                    // the simple object row mapper allows mapping excluded objects to remove the exclusion
                    // but an object list should not have excluded objects
                    if (!$trp->is_excluded() or $load_all) {
                        $this->add_obj($trp);
                        $result = true;
                        // fill verb
                        $trp->set_verb_id($db_row[verb_db::FLD_ID]);
                        // fill from
                        $trp->set_fob(new phrase($this->user()));
                        $trp->fob()->row_mapper_sandbox($db_row, triple_db::FLD_FROM, '1');
                        // fill to
                        $trp->set_tob(new phrase($this->user()));
                        $trp->tob()->row_mapper_sandbox($db_row, triple_db::FLD_TO, '2');
                    } else {
                        log_info($trp->dsp_id() . ' is excluded');
                    }
                }
            }
        }

        return $result;
    }


    /*
     * sql
     */

    /**
     * add the triple name field to
     * the SQL statement to load only the triple id and name
     *
     * @param sql_creator $sc with the target db_type set
     * @param sandbox_named|sandbox_link_named|combine_named $sbx the single child object
     * @param string $pattern the pattern to filter the triples
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
        $qp = $this->load_sql_names_pre($sc, $sbx, $pattern, $limit, $offset);

        $sc->set_usr_fields(array($sbx->name_field()));

        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * set the SQL query parameters to load a list of triples by the names
     * @param sql_creator $sc with the target db_type set
     * @param array $names a list of strings with the word names
     * @param string $fld the name of the name field
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_names(
        sql_creator $sc,
        array       $names,
        string      $fld = triple_db::FLD_NAME
    ): sql_par
    {
        return parent::load_sql_by_names($sc, $names, $fld);
    }

    /**
     * set the SQL query parameters to load a list of triples by the ids
     * @param sql_creator $sc with the target db_type set
     * @param array $trp_ids a list of int values with the triple ids
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_ids(
        sql_creator $sc,
        array       $trp_ids
    ): sql_par
    {
        $qp = $this->load_sql($sc);
        if (count($trp_ids) > 0) {
            $qp->name .= 'ids';
            $sc->set_name($qp->name);
            $sc->add_where(triple_db::FLD_ID, $trp_ids, sql_par_type::INT_LIST);
            $qp->sql = $sc->sql();
        } else {
            $qp->name = '';
        }
        $qp->par = $sc->get_par();
        return $qp;
    }

    /**
     * set the SQL query parameters to load a list of triples by a phrase, verb and direction
     * @param sql_creator $sc with the target db_type set
     * @param phrase $phr the phrase which should be used for selecting the words or triples
     * @param verb|null $vrb if set to filter the selection
     * @param foaf_direction $direction to select either the parents, children or all related words ana triples
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_phr(
        sql_creator    $sc,
        phrase         $phr,
        ?verb          $vrb = null,
        foaf_direction $direction = foaf_direction::BOTH
    ): sql_par
    {
        $qp = $this->load_sql($sc);
        if ($phr->id() <> 0) {
            $qp->name .= 'phr';
            if ($direction == foaf_direction::UP) {
                $sc->add_where(triple_db::FLD_FROM, $phr->id());
            } elseif ($direction == foaf_direction::DOWN) {
                $sc->add_where(triple_db::FLD_TO, $phr->id());
            } elseif ($direction == foaf_direction::BOTH) {
                $sc->add_where(triple_db::FLD_FROM, $phr->id(), sql_par_type::INT_OR);
                $sc->add_where(triple_db::FLD_TO, $phr->id(), sql_par_type::INT_OR);
            }
            if ($vrb != null) {
                if ($vrb->id() > 0) {
                    $sc->add_where(verb_db::FLD_ID, $vrb->id());
                    $qp->name .= '_and_vrb';
                }
            }
            if ($direction == foaf_direction::UP) {
                $qp->name .= '_up';
            } elseif ($direction == foaf_direction::DOWN) {
                $qp->name .= '_down';
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
     * set the SQL query parameters to load a list of triples by a phrase, verb and direction
     * @param sql_creator $sc with the target db_type set
     * @param phrase_list $phr_lst a list of phrase which should be used for selecting the words or triples
     * @param verb|null $vrb if set to filter the selection
     * @param foaf_direction $direction to select either the parents, children or all related words ana triples
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_phr_lst(
        sql_creator    $sc,
        phrase_list    $phr_lst,
        ?verb          $vrb = null,
        foaf_direction $direction = foaf_direction::BOTH): sql_par
    {
        $qp = $this->load_sql($sc);
        if (!$phr_lst->empty()) {
            $qp->name .= 'phr_lst';
            if ($direction == foaf_direction::UP) {
                $sc->add_where(triple_db::FLD_FROM, $phr_lst->ids());
                $qp->name .= '_' . $direction->value;
            } elseif ($direction == foaf_direction::DOWN) {
                $sc->add_where(triple_db::FLD_TO, $phr_lst->ids());
                $qp->name .= '_' . $direction->value;
            } elseif ($direction == foaf_direction::BOTH) {
                $sc->add_where(triple_db::FLD_FROM, $phr_lst->ids(), sql_par_type::INT_LIST_OR);
                $sc->add_where(triple_db::FLD_TO, $phr_lst->ids(), sql_par_type::INT_LIST_OR);
            }
            if ($vrb != null) {
                if ($vrb->id() > 0) {
                    $sc->add_where(verb_db::FLD_ID, $vrb->id());
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
     * set the SQL query parameters to load a list of triples
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name extension to make the query name unique
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql_creator $sc, string $query_name = ''): sql_par
    {
        $sc->set_class(triple::class);
        $qp = new sql_par(self::class);
        if ($query_name != '') {
            $qp->name .= $query_name;
        }
        $sc->set_name($qp->name); // assign incomplete name to force the usage of the user as a parameter
        $sc->set_usr($this->user()->id());
        $sc->set_fields(array_merge(triple_db::FLD_NAMES_LINK, triple_db::FLD_NAMES));
        $sc->set_usr_fields(triple_db::FLD_NAMES_USR);
        $sc->set_usr_num_fields(triple_db::FLD_NAMES_NUM_USR);
        // also load the linked user specific phrase with the same SQL statement (word until now)
        $sc->set_join_fields(
            phrase::FLD_NAMES,
            phrase::class,
            triple_db::FLD_FROM,
            phrase::FLD_ID
        );
        $sc->set_join_usr_fields(
            phrase::FLD_NAMES_USR,
            phrase::class,
            triple_db::FLD_FROM,
            phrase::FLD_ID
        );
        $sc->set_join_usr_num_fields(
            phrase::FLD_NAMES_NUM_USR,
            phrase::class,
            triple_db::FLD_FROM,
            phrase::FLD_ID,
            true
        );
        $sc->set_join_fields(
            phrase::FLD_NAMES,
            phrase::class,
            triple_db::FLD_TO,
            phrase::FLD_ID
        );
        $sc->set_join_usr_fields(
            phrase::FLD_NAMES_USR,
            phrase::class,
            triple_db::FLD_TO,
            phrase::FLD_ID
        );
        $sc->set_join_usr_num_fields(
            phrase::FLD_NAMES_NUM_USR,
            phrase::class,
            triple_db::FLD_TO,
            phrase::FLD_ID,
            true
        );
        $sc->set_order_text(sql_db::STD_TBL . '.' . $sc->name_sql_esc(verb_db::FLD_ID) . ', ' . triple_db::FLD_NAME_GIVEN);
        return $qp;
    }


    /*
     * im- and export
     */

    /**
     * import a triple list object from a JSON array object
     *
     * @param array $json_obj an array with the data of the json object
     * @param user $usr_req the user how has initiated the import mainly used to prevent any user to gain additional rights
     * @param data_object|null $dto cache of the objects imported until now for the primary references
     * @param object|null $test_obj if not null the unit test object to get a dummy seq id
     * @return user_message the status of the import and if needed the error messages that should be shown to the user
     */
    function import_obj(
        array        $json_obj,
        user         $usr_req,
        ?data_object $dto = null,
        object       $test_obj = null
    ): user_message
    {
        $usr_msg = new user_message();
        foreach ($json_obj as $value) {
            $trp = new triple($this->user());
            $usr_msg->add($trp->import_obj($value, $usr_req, $dto, $test_obj));
            $this->add($trp);
        }

        return $usr_msg;
    }

    /**
     * create an array with the export json triples
     * @param bool $do_load to switch off the database load for unit tests
     * @return array the filled array used to create the user export json
     */
    function export_json(bool $do_load = true): array
    {
        $trp_lst = [];

        foreach ($this->lst() as $trp) {
            if (get_class($trp) == triple::class) {
                $trp_lst[] = $trp->export_json($do_load);
            } else {
                log_err('The function triple_list->export_json returns ' . $trp->dsp_id() . ', which is ' . get_class($trp) . ', but not a word.', 'export->get');
            }
        }

        return $trp_lst;
    }


    /*
     *  information
     */

    /**
     * @return bool true if the list contains at least one triple
     */
    function has_values(): bool
    {
        if ($this->count() > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * delete all loaded triples e.g. to delete all the triples linked to a word
     * @return user_message
     */
    function del(): user_message
    {
        $usr_msg = new user_message();

        foreach ($this->lst() as $trp) {
            $usr_msg->add($trp->del());
        }
        return new user_message();
    }

    /**
     * reports the difference to the given triple list as a human-readable messages
     * @param sandbox_list_named $sbx_lst the list of the object to compare with
     * @param msg_id $msg_missing the message id for a missing sandbox object
     * @param msg_id $msg_id_missing the message id for a missing sandbox object id
     * @param msg_id $msg_additional the message id for an additional sandbox object
     * @param msg_id $msg_id_additional the message id for an additional sandbox object id
     * @return user_message
     */
    function diff_msg(
        sandbox_list_named $sbx_lst,
        msg_id             $msg_missing = msg_id::TRIPLE_MISSING,
        msg_id             $msg_id_missing = msg_id::TRIPLE_ID_MISSING,
        msg_id             $msg_additional = msg_id::TRIPLE_ADDITIONAL,
        msg_id             $msg_id_additional = msg_id::TRIPLE_ID_ADDITIONAL
    ): user_message
    {
        return parent::diff_msg($sbx_lst,
            msg_id::TRIPLE_MISSING,
            msg_id::TRIPLE_ID_MISSING,
            msg_id::TRIPLE_ADDITIONAL,
            msg_id::TRIPLE_ID_ADDITIONAL);
    }


    /*
     * convert
     */

    /**
     * convert this triple list object into a phrase list object
     * @return phrase_list with all triples of this list as a phrase
     */
    function phrase_list(): phrase_list
    {
        $phr_lst = new phrase_list($this->user());
        foreach ($this->lst() as $lnk) {
            $phr_lst->add($lnk->phrase());
        }
        return $phr_lst;
    }

    /**
     * convert this triple list object into a phrase list object
     * and use the name as the unique key instead of the database id
     * used for the data_object based import
     * @return phrase_list with all triples of this list as a phrase
     */
    function phrase_lst_of_names(): phrase_list
    {
        $phr_lst = new phrase_list($this->user());
        foreach ($this->lst() as $lnk) {
            if ($lnk::class == phrase::class) {
                log_err('unexpected phrase instead of triple in triple list');
                $phr_lst->add_by_name($lnk);
            } else {
                $phr_lst->add_by_name_direct($lnk->phrase());
            }
        }
        return $phr_lst;
    }

    /**
     * get the triples that does not yet have a database id, but have a name
     * including the linked triples
     * @return triple_list with all triples potentially missing in the database
     */
    function triples_to_add_to_db(): triple_list
    {
        $trp_lst = new triple_list($this->user());
        foreach ($this->lst() as $trp) {
            if ($trp->no_id_but_name()) {
                $trp_lst->add_by_name_direct($trp);
            }
            $from = $trp->from();
            if ($from->is_triple()) {
                if ($from->no_id_but_name()) {
                    $trp_lst->add_by_name_direct($from);
                }
            }
            $to = $trp->to();
            if ($to->is_triple()) {
                if ($to->no_id_but_name()) {
                    $trp_lst->add_by_name_direct($to);
                }
            }
        }
        return $trp_lst;
    }


    /*
     * parts
     */

    /**
     * get a list of the phrase parts
     * @return phrase_list with all triples of this list as a phrase
     */
    function phrase_parts(): phrase_list
    {
        $phr_lst = new phrase_list($this->user());
        foreach ($this->lst() as $lnk) {
            $phr_lst->add($lnk->from());
            $phr_lst->add($lnk->to());
        }
        return $phr_lst;
    }

    /**
     * @return triple_list with all phrases that does not yet have a database id
     */
    function missing_ids(): triple_list
    {
        $trp_lst = new triple_list($this->user());
        foreach ($this->lst() as $trp) {
            if ($trp->id() == 0) {
                $trp_lst->add_by_name_direct($trp);
            }
        }
        return $trp_lst;
    }


    /*
     * save
     */

    /**
     * add or update all triples to the database
     * starting with the $cache that contains the words
     * add the triples that does not yet have a database id
     *
     * @param import $imp the import object with the filename and the estimated time of arrival
     * @param phrase_list $cache the cached phrases that does not need to be loaded from the db again
     * @return user_message
     */
    function save_with_cache(import $imp, phrase_list $cache): user_message
    {
        global $cfg;

        $usr_msg = new user_message();

        $load_per_sec = $cfg->get_by([words::TRIPLES, words::LOAD, triples::OBJECTS_PER_SECOND, triples::EXPECTED_TIME, words::IMPORT], 1);
        $save_per_sec = $cfg->get_by([words::TRIPLES, words::STORE, triples::OBJECTS_PER_SECOND, triples::EXPECTED_TIME, words::IMPORT], 1);
        $upd_per_sec = $cfg->get_by([words::TRIPLES, words::UPDATE, triples::OBJECTS_PER_SECOND, triples::EXPECTED_TIME, words::IMPORT], 1);
        $del_per_sec = $cfg->get_by([words::TRIPLES, words::DELETE, triples::OBJECTS_PER_SECOND, triples::EXPECTED_TIME, words::IMPORT], 1);
        $max_trp_levels = $cfg->get_by([words::TRIPLES, triples::MAX_LEVELS, words::IMPORT], 99);

        if ($this->is_empty()) {
            log_info('no triples to save');
        } else {

            // repeat filling the database id to the triple list
            // and adding missing triples to the database
            // until it is clear that a triple is missing
            $trp_added = true;
            $level = 0;
            $db_lst_all = new triple_list($this->user());
            $add_lst = new triple_list($this->user());
            while ($trp_added and $level < $max_trp_levels) {
                $trp_added = false;
                $usr_msg->unset_added_depending();

                // collect all triples with names that does not yet have a database id and needs to be added
                $chk_lst = $this->triples_to_add_to_db();

                // add the database id to the triple list of words and triples used until now
                $chk_lst->fill_by_name($cache, true, false);

                // fill missing verbs
                $chk_lst->fill_missing_verbs();

                // get the triples that needs to be added
                // TODO check if other list save function are using the cache instead of this here
                $load_lst = $chk_lst->missing_ids();

                // load the triples by name from the database that does not yet have a database id
                $step_time = $load_lst->count() / $load_per_sec;
                $imp->step_start(msg_id::LOAD, triple::class, $load_lst->count(), $step_time);
                $db_lst = new triple_list($this->user());
                // force to load all names including the triples excluded by the user to potential include the triples due to the import
                // TODO add load_all = true also to the other objects
                $db_lst->load_by_names($load_lst->names(true), true);
                $imp->step_end($load_lst->count(), $load_per_sec);

                // fill up the cache to prevent loading the same triple again in the next level
                // TODO increase speed!
                $cache = $cache->merge($db_lst->phrase_list());

                // fill up the overall db list with db value for later detection of the triples that needs to be updated
                $db_lst_all->merge($db_lst);

                // fill up the loaded list with db value to select only the triples that really needs to be inserted
                $load_lst->fill_by_name($db_lst, true, false);

                // select the triples that are ready to be added to the database
                $load_lst = $load_lst->get_ready();

                // get the triples that still needs to be added
                // TODO check if other list save function are using the cache instead of this here
                $add_lst = $load_lst->missing_ids();

                // create any missing sql insert functions and insert the missing triples
                if (!$add_lst->is_empty()) {
                    $step_time = $add_lst->count() / $save_per_sec;
                    $imp->step_start(msg_id::SAVE, triple::class, $add_lst->count(), $step_time);
                    $usr_msg->add($add_lst->insert($cache, true, $imp, triple::class));
                    if ($add_lst->count() > 0) {
                        $usr_msg->set_added_depending();
                        $trp_added = true;
                    }
                    $imp->step_end($add_lst->count(), $save_per_sec);
                }

                $cache->filter_valid();

                $level++;
            }

            // reload the id of the triples added with the last run
            // TODO use the insert message instead to increase speed
            $db_lst = new triple_list($this->user());
            if (!$add_lst->is_empty()) {
                $db_lst->load_by_names($add_lst->names(true), true);
            }

            // fill up the overall db list with db value for later detection of the triples that needs to be updated
            $db_lst_all->merge($db_lst);


            // create any missing sql update functions and update the triples
            $usr_msg->add($this->update($db_lst_all, true, $imp, triple::class, $upd_per_sec));


            // fill up the main list with the words
            $this->fill_by_name($cache, true);
            // fill up the main list with the triples to check if anything is missing
            $this->fill_by_name($db_lst_all, true);

            // report missing triples
            $this->report_missing($usr_msg);


            // create any missing sql delete functions and delete unused sandbox objects
            $usr_msg->add($this->delete($db_lst_all, true, $imp, triple::class, $del_per_sec));

        }

        return $usr_msg;
    }

    /**
     * add the ids and other variables from the given list and add missing words, triples, ...
     * select the related object by the name
     *
     * @param triple_list|sandbox_list_named $db_lst a list of sandbox objects that might have more vars set e.g. the db id
     * @param bool $fill_all force to include also the excluded names e.g. for import
     * @param bool $report_missing if true it is expected that all triples are in the given $db_lst
     * @return user_message a warning in case of a conflict e.g. due to a missing change time
     */
    function fill_by_name(
        triple_list|sandbox_list_named $db_lst,
        bool                           $fill_all = false,
        bool                           $report_missing = true
    ): user_message
    {
        $usr_msg = new user_message();

        // loop over the objects of theis list because it is expected to be smaller than tha cache list
        foreach ($this->lst() as $trp) {
            $this->fill_triple_by_name($db_lst, $trp, $usr_msg, $fill_all, $report_missing);
            if ($trp->needs_from()) {
                $this->fill_triple_by_name($db_lst, $trp->from(), $usr_msg, $fill_all, $report_missing);
            }
            $this->fill_triple_by_name($db_lst, $trp->to(), $usr_msg, $fill_all, $report_missing);
        }
        return $usr_msg;
    }

    private function report_missing(user_message $usr_msg): void
    {
        foreach ($this->lst() as $trp) {
            if (!$trp->excluded) {
                if ($trp->needs_from()) {
                    $phr = $trp->from();
                    if (!$phr->is_valid()) {
                        $usr_msg->add_id_with_vars(msg_id::IMPORT_PHRASE_NOT_FOUND, [
                            msg_id::VAR_NAME => $phr->name(),
                            msg_id::VAR_ID => $trp->dsp_id()
                        ]);
                    }
                }
                $phr = $trp->to();
                if (!$phr->is_valid()) {
                    $usr_msg->add_id_with_vars(msg_id::IMPORT_PHRASE_NOT_FOUND, [
                        msg_id::VAR_NAME => $phr->name(),
                        msg_id::VAR_ID => $trp->dsp_id()
                    ]);
                }
            }
        }
    }

    private function fill_triple_by_name(
        triple_list|sandbox_list_named $db_lst,
        triple|phrase                  $phr,
        user_message                   $usr_msg,
        bool                           $fill_all = false,
        bool                           $report_missing = true
    ): void
    {
        global $usr;
        if ($phr->id() == 0 and $phr->name($fill_all) != '') {
            $db_obj = $db_lst->get_by_name($phr->name($fill_all), $fill_all);
            if ($db_obj != null) {
                $phr->fill($db_obj, $usr);
            } else {
                if ($report_missing and !$phr->is_excluded()) {
                    $lib = new library();
                    $usr_msg->add_id_with_vars(msg_id::ADDED_OBJECT_NOT_FOUND, [
                        msg_id::VAR_CLASS_NAME => $lib->class_to_name($phr::class),
                        msg_id::VAR_NAME => $phr->dsp_id()
                    ]);
                }
            }
        }
    }

    function fill_missing_verbs(): user_message
    {
        global $vrb_cac;

        $usr_msg = new user_message();
        foreach ($this->lst() as $phr) {
            if ($phr::class == triple::class) {
                if ($phr->verb() == null) {
                    $phr->set_verb($vrb_cac->get_verb(verbs::NOT_SET));
                    $usr_msg->add_id_with_vars(msg_id::TRIPLE_VERB_SET, [
                        msg_id::VAR_ID => $phr->dsp_id(),
                        msg_id::VAR_VALUE => verbs::NOT_SET
                    ]);
                }
            }
        }
        return $usr_msg;
    }

    /**
     * get a list of triples that are ready to be added to the database
     * @return triple_list list of the triples that have an id or a name
     */
    function get_ready(user_message $usr_msg = new user_message(), string $file_name = ''): triple_list
    {
        $trp_lst = new triple_list($this->user());
        foreach ($this->lst() as $trp) {
            $trp_msg = $trp->db_ready();
            if ($trp_msg->is_ok()) {
                $trp_lst->add_by_name($trp);
            } else {
                $usr_msg->add($trp_msg);
                $usr_msg->add_id_with_vars(msg_id::IMPORT_TRIPLE_NOT_READY, [
                    msg_id::VAR_FILE_NAME => $file_name,
                    msg_id::VAR_TRIPLE_NAME => $trp->dsp_id(),
                ]);
            }
        }
        return $trp_lst;
    }

}