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

namespace cfg;

include_once API_WORD_PATH . 'triple_list.php';
include_once DB_PATH . 'sql_par_type.php';

use api\word\triple_list as triple_list_api;
use cfg\db\sql;
use cfg\db\sql_par_type;
use html\html_base;
use html\word\triple as triple_dsp;
use html\word\triple_list as triple_list_dsp;

class triple_list extends sandbox_list
{

    public array $lst; // the list of triples
    private user $usr; // the user object of the person for whom the triple list is loaded, so to say the viewer

    // fields to select a part of the graph (TODO deprecated)
    public array $ids = array();  // list of link ids
    public ?word $wrd = null;          // show the graph elements related to this word
    public ?word_list $wrd_lst = null; // show the graph elements related to these words
    public ?verb $vrb = null;     // show the graph elements related to this verb
    public ?verb_list $vrb_lst = null; // show the graph elements related to these verbs
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
     * cast
     */

    /**
     * @return triple_list_api the triple list object with the display interface functions
     */
    function api_obj(): triple_list_api
    {
        $api_obj = new triple_list_api();
        foreach ($this->lst() as $trp) {
            $api_obj->add($trp->api_obj());
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


    /*
     * load functions
     */

    /**
     * add the triple name field to
     * the SQL statement to load only the triple id and name
     *
     * @param sql $sc with the target db_type set
     * @param sandbox_named|sandbox_link_named|combine_named $sbx the single child object
     * @param string $pattern the pattern to filter the triples
     * @param int $limit the number of rows to return
     * @param int $offset jump over these number of pages
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_names(
        sql                                            $sc,
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
     * set the SQL query parameters to load a list of triples
     * @param sql $sc with the target db_type set
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql $sc): sql_par
    {
        $sc->set_class(triple::class);
        $qp = new sql_par(self::class);
        $sc->set_name($qp->name); // assign incomplete name to force the usage of the user as a parameter
        $sc->set_usr($this->user()->id());
        $sc->set_fields(array_merge(triple::FLD_NAMES_LINK, triple::FLD_NAMES));
        $sc->set_usr_fields(triple::FLD_NAMES_USR);
        $sc->set_usr_num_fields(triple::FLD_NAMES_NUM_USR);
        // also load the linked user specific phrase with the same SQL statement (word until now)
        $sc->set_join_fields(
            phrase::FLD_NAMES,
            sql_db::TBL_PHRASE,
            triple::FLD_FROM,
            phrase::FLD_ID
        );
        $sc->set_join_usr_fields(
            phrase::FLD_NAMES_USR,
            sql_db::TBL_PHRASE,
            triple::FLD_FROM,
            phrase::FLD_ID
        );
        $sc->set_join_usr_num_fields(
            phrase::FLD_NAMES_NUM_USR,
            sql_db::TBL_PHRASE,
            triple::FLD_FROM,
            phrase::FLD_ID,
            true
        );
        $sc->set_join_fields(
            phrase::FLD_NAMES,
            sql_db::TBL_PHRASE,
            triple::FLD_TO,
            phrase::FLD_ID
        );
        $sc->set_join_usr_fields(
            phrase::FLD_NAMES_USR,
            sql_db::TBL_PHRASE,
            triple::FLD_TO,
            phrase::FLD_ID
        );
        $sc->set_join_usr_num_fields(
            phrase::FLD_NAMES_NUM_USR,
            sql_db::TBL_PHRASE,
            triple::FLD_TO,
            phrase::FLD_ID,
            true
        );
        $sc->set_order_text(sql_db::STD_TBL . '.' . $sc->name_sql_esc(verb::FLD_ID) . ', ' . triple::FLD_NAME_GIVEN);
        return $qp;
    }

    /**
     * set the SQL query parameters to load a list of triples by the ids
     * @param sql $sc with the target db_type set
     * @param array $trp_ids a list of int values with the triple ids
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_ids(sql $sc, array $trp_ids): sql_par
    {
        $qp = $this->load_sql($sc);
        if (count($trp_ids) > 0) {
            $qp->name .= 'ids';
            $sc->set_name($qp->name);
            $sc->add_where(triple::FLD_ID, $trp_ids, sql_par_type::INT_LIST);
            $qp->sql = $sc->sql();
        } else {
            $qp->name = '';
        }
        $qp->par = $sc->get_par();
        return $qp;
    }

    /**
     * set the SQL query parameters to load a list of triples by a phrase, verb and direction
     * @param sql $sc with the target db_type set
     * @param phrase $phr the phrase which should be used for selecting the words or triples
     * @param verb|null $vrb if set to filter the selection
     * @param foaf_direction $direction to select either the parents, children or all related words ana triples
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_phr(
        sql            $sc,
        phrase         $phr,
        ?verb          $vrb = null,
        foaf_direction $direction = foaf_direction::BOTH): sql_par
    {
        $qp = $this->load_sql($sc);
        if ($phr->id() <> 0) {
            $qp->name .= 'phr';
            if ($direction == foaf_direction::UP) {
                $sc->add_where(triple::FLD_FROM, $phr->id());
            } elseif ($direction == foaf_direction::DOWN) {
                $sc->add_where(triple::FLD_TO, $phr->id());
            } elseif ($direction == foaf_direction::BOTH) {
                $sc->add_where(triple::FLD_FROM, $phr->id(), sql_par_type::INT_OR);
                $sc->add_where(triple::FLD_TO, $phr->id(), sql_par_type::INT_OR);
            }
            if ($vrb != null) {
                if ($vrb->id() > 0) {
                    $sc->add_where(verb::FLD_ID, $vrb->id());
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
     * @param sql $sc with the target db_type set
     * @param phrase_list $phr_lst a list of phrase which should be used for selecting the words or triples
     * @param verb|null $vrb if set to filter the selection
     * @param foaf_direction $direction to select either the parents, children or all related words ana triples
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_phr_lst(
        sql            $sc,
        phrase_list    $phr_lst,
        ?verb          $vrb = null,
        foaf_direction $direction = foaf_direction::BOTH): sql_par
    {
        $qp = $this->load_sql($sc);
        if (!$phr_lst->empty()) {
            $qp->name .= 'phr_lst';
            if ($direction == foaf_direction::UP) {
                $sc->add_where(triple::FLD_FROM, $phr_lst->ids());
                $qp->name .= '_' . $direction->value;
            } elseif ($direction == foaf_direction::DOWN) {
                $sc->add_where(triple::FLD_TO, $phr_lst->ids());
                $qp->name .= '_' . $direction->value;;
            } elseif ($direction == foaf_direction::BOTH) {
                $sc->add_where(triple::FLD_FROM, $phr_lst->ids(), sql_par_type::INT_LIST_OR);
                $sc->add_where(triple::FLD_TO, $phr_lst->ids(), sql_par_type::INT_LIST_OR);
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
     * load this list of triples
     * @param sql_par $qp the SQL statement, the unique name of the SQL statement and the parameter list
     * @param bool $load_all force to include also the excluded triples e.g. for admins
     * @return bool true if at least one triple found
     */
    protected function load(sql_par $qp, bool $load_all = false): bool
    {
        global $db_con;
        global $verbs;
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
                        $trp->verb = $verbs->get_verb_by_id($db_row[verb::FLD_ID]);
                        // fill from
                        $trp->fob = new phrase($this->user());
                        $trp->fob->row_mapper_sandbox($db_row, triple::FLD_FROM, '1');
                        // fill to
                        $trp->tob = new phrase($this->user());
                        $trp->tob->row_mapper_sandbox($db_row, triple::FLD_TO, '2');
                    }
                }
            }
        }

        return $result;
    }

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

    /*
     * load functions (to deprecate because not based on prepared queries )
     */

    private function load_wrd_fields(sql_db $db_con, $pos): string
    {
        return 't' . $pos . '.word_id AS word_id' . $pos . ',
                t' . $pos . '.user_id AS user_id' . $pos . ',
                ' . $db_con->get_usr_field(word::FLD_NAME, 't' . $pos, 'u' . $pos, sql_db::FLD_FORMAT_TEXT, word::FLD_NAME . $pos) . ',
                ' . $db_con->get_usr_field(word::FLD_PLURAL, 't' . $pos, 'u' . $pos, sql_db::FLD_FORMAT_TEXT, word::FLD_PLURAL . $pos) . ',
                ' . $db_con->get_usr_field(sandbox_named::FLD_DESCRIPTION, 't' . $pos, 'u' . $pos, sql_db::FLD_FORMAT_TEXT, sandbox_named::FLD_DESCRIPTION . $pos) . ',
                ' . $db_con->get_usr_field(phrase::FLD_TYPE, 't' . $pos, 'u' . $pos, sql_db::FLD_FORMAT_VAL, phrase::FLD_TYPE . $pos) . ',
                ' . $db_con->get_usr_field(view::FLD_ID, 't' . $pos, 'u' . $pos, sql_db::FLD_FORMAT_VAL, view::FLD_ID . $pos) . ',
                ' . $db_con->get_usr_field(sandbox::FLD_EXCLUDED, 't' . $pos, 'u' . $pos, sql_db::FLD_FORMAT_VAL, sandbox::FLD_EXCLUDED . $pos) . ',
                  t' . $pos . '.' . $db_con->get_table_name_esc(value::class) . ' AS values' . $pos;
    }

    private function load_wrd_from($pos): string
    {
        return ' words t' . $pos . ' LEFT JOIN user_words u' . $pos . ' ON u' . $pos . '.word_id = t' . $pos . '.word_id 
                                                                       AND u' . $pos . '.user_id = ' . $this->user()->id() . ' ';
    }

    /**
     * add one triple to the triple list, but only if it is not yet part of the list
     * @param triple
     * @return bool true if the triple has been added to the list
     *              and false if the triple already exists
     */
    function add($lnk_to_add): bool
    {
        log_debug($lnk_to_add->dsp_id());
        $result = false;

        if (!in_array($lnk_to_add->id(), $this->ids)) {
            if ($lnk_to_add->id() > 0) {
                $this->add_obj($lnk_to_add);
                $this->ids[] = $lnk_to_add->id();
                $result = true;
            }
        }
        return $result;
    }


    /*
     * im- and export
     */

    /**
     * import a triple list object from a JSON array object
     *
     * @param array $json_obj an array with the data of the json object
     * @param object|null $test_obj if not null the unit test object to get a dummy seq id
     * @return user_message the status of the import and if needed the error messages that should be shown to the user
     */
    function import_obj(array $json_obj, object $test_obj = null): user_message
    {
        $result = new user_message();
        foreach ($json_obj as $value) {
            $trp = new triple($this->user());
            $result->add($trp->import_obj($value, $test_obj));
            $this->add($trp);
        }

        return $result;
    }

    /**
     * create a list of word objects for the export
     * @param bool $do_load to switch off the database load for unit tests
     * @return array with the reduced triple objects that can be used to create a JSON message
     */
    function export_obj(bool $do_load = true): array
    {
        $exp_triples = array();
        foreach ($this->lst() as $trp) {
            if (get_class($trp) == triple::class) {
                $exp_triples[] = $trp->export_obj($do_load);
            } else {
                log_err('The function triple_list->export_obj returns ' . $trp->dsp_id() . ', which is ' . get_class($trp) . ', but not a word.', 'export->get');
            }
        }
        return $exp_triples;
    }

    /*
     * display functions
     */

    /**
     * TODO move to the frontend
     * shows all words the link to the given word
     * returns the html code to select a word that can be edited
     */
    function display(string $back = ''): string
    {
        global $verbs;

        $html = new html_base();
        $result = '';

        // check the all minimal input parameters
        if ($this->user() == null) {
            log_err("The user id must be set to load a graph.", "triple_list->load");
        } else {
            if (isset($this->wrd)) {
                log_debug('graph->display for ' . $this->wrd->name() . ' ' . $this->direction->value . ' and user ' . $this->user()->name . ' called from ' . $back);
            }
            $prev_verb_id = 0;

            // loop over the graph elements
            foreach (array_keys($this->lst()) as $lnk_id) {
                // reset the vars
                $directional_link_type_id = 0;

                $lnk = $this->get($lnk_id);
                // get the next link to detect if there is more than one word linked with the same link type
                // TODO check with a unit test if last element is used
                if ($this->count() - 1 > $lnk_id) {
                    $next_lnk = $this->get($lnk_id + 1);
                } else {
                    $next_lnk = $lnk;
                }

                // display type header
                if ($lnk->verb == null) {
                    log_warning('graph->display type is missing');
                } else {
                    if ($lnk->verb()->id() <> $prev_verb_id) {
                        log_debug('graph->display type "' . $lnk->verb()->name() . '"');

                        // select the same side of the verb
                        if ($this->direction == foaf_direction::DOWN) {
                            $directional_link_type_id = $lnk->verb()->id();
                        } else {
                            $directional_link_type_id = $lnk->verb()->id() * -1;
                        }

                        // display the link type
                        if ($lnk->verb()->id() == $next_lnk->verb()->id()) {
                            if ($this->wrd != null) {
                                $result .= $this->wrd->plural;
                            }
                            if ($this->direction == foaf_direction::DOWN) {
                                $result .= " " . $lnk->verb()->rev_plural;
                            } else {
                                $result .= " " . $lnk->verb()->plural;
                            }
                        } else {
                            $result .= $this->wrd->name();
                            if ($this->direction == foaf_direction::DOWN) {
                                $result .= " " . $lnk->verb()->reverse;
                            } else {
                                $result .= " " . $lnk->verb()->name;
                            }
                        }
                    }
                    $result .= $html->dsp_tbl_start_half();
                    $prev_verb_id = $lnk->verb()->id();

                    // display the word
                    if ($lnk->fob() == null) {
                        log_warning('graph->display from is missing');
                    } else {
                        log_debug('word->dsp_graph display word ' . $lnk->fob()->name());
                        $result .= '  <tr>' . "\n";
                        if ($lnk->tob() != null) {
                            $dsp_obj = $lnk->tob()->get_dsp_obj();
                            $result .= $dsp_obj->dsp_tbl_cell(0);
                        }
                        $lnk_dsp = new triple_dsp($lnk->api_json());
                        $result .= $lnk_dsp->btn_edit($lnk->fob()->dsp_obj());
                        if ($lnk->fob() != null) {
                            $dsp_obj = $lnk->fob()->get_dsp_obj();
                            $result .= $dsp_obj->dsp_unlink($lnk->id());
                        }
                        $result .= '  </tr>' . "\n";
                    }

                    // use the last word as a sample for the new word type
                    $last_linked_word_id = 0;
                    if ($lnk->verb()->id() == $verbs->id(verb::FOLLOW)) {
                        $last_linked_word_id = $lnk->tob()->id;
                    }

                    // in case of the verb "following" continue the series after the last element
                    $start_id = 0;
                    if ($lnk->verb()->id() == $verbs->id(verb::FOLLOW)) {
                        $start_id = $last_linked_word_id;
                        // and link with the same direction (looks like not needed!)
                        /* if ($directional_link_type_id > 0) {
                          $directional_link_type_id = $directional_link_type_id * -1;
                        } */
                    } else {
                        if ($lnk->fob() == null) {
                            log_warning('graph->display from is missing');
                        } else {
                            $start_id = $lnk->fob()->id(); // to select a similar word for the verb following
                        }
                    }

                    if ($lnk->verb()->id() <> $next_lnk->verb()->id()) {
                        if ($lnk->fob() == null) {
                            log_warning('graph->display from is missing');
                        } else {
                            $start_id = $lnk->fob()->id();
                        }
                        // give the user the possibility to add a similar word
                        $result .= '  <tr>';
                        $result .= '    <td>';
                        $result .= '      ' . \html\btn_add("Add similar word", '/http/word_add.php?verb=' .
                                $directional_link_type_id . '&word=' . $start_id . '&type=' . $lnk->tob()->type_id . '&back=' . $start_id);
                        $result .= '    </td>';
                        $result .= '  </tr>';

                        $result .= $html->dsp_tbl_end();
                        $result .= '<br>';
                    }
                }
            }
        }
        return $result;
    }

    /*
     *  information functions
     */

    /**
     * @return bool true if the list has no entry
     */
    function is_empty(): bool
    {
        $result = true;
        if ($this->lst() != null) {
            if ($this->count() > 0) {
                $result = false;
            }
        }
        return $result;
    }

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
        $result = new user_message();

        foreach ($this->lst() as $trp) {
            $result->add($trp->del());
        }
        return new user_message();
    }


    /*
     * convert functions
     */

    /**
     * convert the word list object into a phrase list object
     */
    function phrase_lst(): phrase_list
    {
        $phr_lst = new phrase_list($this->user());
        foreach ($this->lst() as $lnk) {
            $phr_lst->add($lnk->phrase());
        }
        return $phr_lst;
    }

}