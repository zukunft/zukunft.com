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

use api\triple_list_api;
use cfg\db\sql_creator;
use cfg\db\sql_par_type;
use html\html_base;
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
     * cast
     */

    /**
     * @return triple_list_api the word list object with the display interface functions
     */
    function api_obj(): triple_list_api
    {
        $api_obj = new triple_list_api();
        foreach ($this->lst as $wrd) {
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
     * @return triple_list the word list object with the display interface functions
     */
    function dsp_obj(): triple_list
    {
        $dsp_obj = new triple_list($this->usr);
        foreach ($this->lst as $wrd) {
            $dsp_obj->add($wrd->dsp_obj());
        }
        return $dsp_obj;
    }


    /*
     * load functions
     */

    /**
     * add the triple name field to
     * the SQL statement to load only the triple id and name
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
        $qp = $this->load_sql_names_pre($sc, $sbx, $pattern, $limit, $offset);

        $sc->set_usr_fields(array($sbx->name_field()));

        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * set the SQL query parameters to load a list of triples
     * @param sql_creator $sc with the target db_type set
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql_creator $sc): sql_par
    {
        $sc->set_type(sql_db::TBL_TRIPLE);
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
     * @param sql_creator $sc with the target db_type set
     * @param array $trp_ids a list of int values with the triple ids
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_ids(sql_creator $sc, array $trp_ids): sql_par
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
     * @param sql_creator $sc with the target db_type set
     * @param phrase $phr the phrase which should be used for selecting the words or triples
     * @param verb|null $vrb if set to filter the selection
     * @param foaf_direction $direction to select either the parents, children or all related words ana triples
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_phr(
        sql_creator $sc,
        phrase $phr,
        ?verb $vrb = null,
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
     * @param sql_creator $sc with the target db_type set
     * @param phrase_list $phr_lst a list of phrase which should be used for selecting the words or triples
     * @param verb|null $vrb if set to filter the selection
     * @param foaf_direction $direction to select either the parents, children or all related words ana triples
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_phr_lst(
        sql_creator $sc,
        phrase_list $phr_lst,
        ?verb $vrb = null,
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
     * @return bool true if at least one word found
     */
    protected function load(sql_par $qp, bool $load_all = false): bool
    {
        global $db_con;
        global $verbs;
        $result = false;

        if ($qp->name == '') {
            log_err('The query name cannot be created to load a ' . self::class, self::class . '->load');
        } else {
            $this->lst = array();
            $db_rows = $db_con->get($qp);
            if ($db_rows != null) {
                foreach ($db_rows as $db_row) {
                    $trp = new triple($this->user());
                    $trp->row_mapper_sandbox($db_row);
                    // the simple object row mapper allows mapping excluded objects to remove the exclusion
                    // but an object list should not have excluded objects
                    if (!$trp->is_excluded() or $load_all) {
                        $this->lst[] = $trp;
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
     * load a list of triples by the ids
     * @param array $wrd_ids a list of int values with the triple ids
     * @return bool true if at least one triple found
     */
    function load_by_ids(array $wrd_ids): bool
    {
        global $db_con;
        $qp = $this->load_sql_by_ids($db_con, $wrd_ids);
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
                  t' . $pos . '.' . $db_con->get_table_name_esc(sql_db::TBL_VALUE) . ' AS values' . $pos;
    }

    private function load_wrd_from($pos): string
    {
        return ' words t' . $pos . ' LEFT JOIN user_words u' . $pos . ' ON u' . $pos . '.word_id = t' . $pos . '.word_id 
                                                                       AND u' . $pos . '.user_id = ' . $this->user()->id() . ' ';
    }

    // returns the of predefined sql statement (must be corresponding to load_sql)
    function load_sql_name(): string
    {
        $sql_name = '';
        $sql_name_type = '';

        if (isset($this->ids)) {
            if (count($this->ids) > 0) {
                $sql_name = 'triple_list_by_ids';
            }
        }
        if ($sql_name == '') {
            if (isset($this->wrd)) {
                if ($this->direction == foaf_direction::DOWN) {
                    $sql_name = 'triple_list_word_down';
                } else if ($this->direction == foaf_direction::UP) {
                    $sql_name = 'triple_list_word_up';
                } else if ($this->direction == foaf_direction::BOTH) {
                    $sql_name = 'triple_list_word_both';
                    log_warning('Word link search direction ' . $this->direction->name . ' not yet expected');
                } else {
                    log_err('Word link search direction ' . $this->direction->name . ' not expected');
                }
            }
        }
        if ($sql_name == '') {
            if (isset($this->wrd_lst)) {
                if ($this->wrd_lst->ids_txt() != '') {
                    if ($this->direction == foaf_direction::DOWN) {
                        $sql_name = 'triple_list_word_list_down';
                    } else if ($this->direction == foaf_direction::UP) {
                        $sql_name = 'triple_list_word_list_up';
                    } else if ($this->direction == foaf_direction::BOTH) {
                        $sql_name = 'triple_list_word_list_both';
                        log_warning('Word link search direction ' . $this->direction->name . ' not yet expected');
                    } else {
                        log_err('Word link search direction ' . $this->direction->name . ' not expected');
                    }
                }
            }
        }
        // with additional verb selection
        if (isset($this->vrb)) {
            if ($this->vrb->id() > 0) {
                $sql_name_type = '_and_vrb';
            }
        }
        if ($sql_name_type == '') {
            if (isset($this->vrb_lst)) {
                if ($this->vrb_lst->ids_txt() != '') {
                    $sql_name_type = '_and_vrb_lst';
                }
            }
        }
        return $sql_name . $sql_name_type;
    }

    // create the sql statement to fill a word link list
    // TODO add query name
    function load_sql_old(sql_db $db_con): string
    {
        $lib = new library();
        $sql = '';

        // set the where clause depending on the defined select values
        $sql_where = '';
        $sql_type = '';
        $sql_wrd1_fields = '';
        $sql_wrd1_from = '';
        $sql_wrd1 = '';
        $sql_wrd2_fields = '';
        $sql_wrd2_from = '';
        $sql_wrd2 = '';
        // if the list of word link ids is set, use them for a direct selection
        if (isset($this->ids)) {
            if (count($this->ids) > 0) {
                $id_txt = $lib->sql_array($this->ids);
                if ($id_txt <> '') {
                    $sql_where = sql_db::LNK_TBL . '.triple_id IN (' . $id_txt . ')';
                    $sql_wrd1_fields = $this->load_wrd_fields($db_con, '1');
                    $sql_wrd1_from = $this->load_wrd_from('1');
                    $sql_wrd1 = 'AND l.from_phrase_id = t1.word_id';
                    $sql_wrd1_fields .= ', ';
                    $sql_wrd1_from .= ', ';
                    $sql_wrd2_fields = $this->load_wrd_fields($db_con, '2');
                    $sql_wrd2_from = $this->load_wrd_from('2');
                    $sql_wrd2 = sql_db::LNK_TBL . '.to_phrase_id = t2.word_id';
                    log_debug('triple_list->load where ids ' . $sql_where);
                }
            }
        }
        // ... else if an original word is set, select all related word links depending on the direction
        // in this case only the fields from the target words needs to be included in the result
        if ($sql_where == '') {
            if (isset($this->wrd)) {
                $sql_wrd2_fields = $this->load_wrd_fields($db_con, '2');
                $sql_wrd2_from = $this->load_wrd_from('2');
                if ($this->direction == foaf_direction::UP) {
                    $sql_where = sql_db::LNK_TBL . '.from_phrase_id = ' . $this->wrd->id();
                    $sql_wrd2 = sql_db::LNK_TBL . '.to_phrase_id = t2.word_id';
                } else {
                    $sql_where = sql_db::LNK_TBL . '.to_phrase_id   = ' . $this->wrd->id();
                    $sql_wrd2 = sql_db::LNK_TBL . '.from_phrase_id = t2.word_id';
                }
                log_debug('triple_list->load where wrd ' . $sql_where);
            }
        }
        // ... else if a list of original words is given select all word links related to the list
        // in this case also the fields from the original words needs to be included in the result
        if ($sql_where == '') {
            if (isset($this->wrd_lst)) {
                if ($this->wrd_lst->ids_txt() != '') {
                    log_debug('triple_list->load based on word list');
                    $sql_wrd1_fields = $this->load_wrd_fields($db_con, '1');
                    $sql_wrd1_from = $this->load_wrd_from('1');
                    $sql_wrd1_fields .= ', ';
                    $sql_wrd1_from .= ', ';
                    $sql_wrd2_fields = $this->load_wrd_fields($db_con, '2');
                    $sql_wrd2_from = $this->load_wrd_from('2');
                    log_debug('triple_list->load based on word list loaded');
                    if ($this->direction == foaf_direction::UP) {
                        $sql_where = sql_db::LNK_TBL . '.from_phrase_id IN (' . $this->wrd_lst->ids_txt() . ')';
                        $sql_wrd1 = 'AND l.from_phrase_id = t1.word_id';
                        $sql_wrd2 = sql_db::LNK_TBL . '.to_phrase_id   = t2.word_id';
                    } else {
                        $sql_where = sql_db::LNK_TBL . '.to_phrase_id   IN (' . $this->wrd_lst->ids_txt() . ')';
                        $sql_wrd1 = 'AND l.to_phrase_id   = t1.word_id';
                        $sql_wrd2 = sql_db::LNK_TBL . '.from_phrase_id = t2.word_id';
                    }
                    log_debug('triple_list->load where wrd in ' . $sql_where);
                }
            }
        }

        // if a verb is set, select only the word links with the given verb
        if (isset($this->vrb)) {
            if ($this->vrb->id() > 0) {
                $sql_type = 'AND l.verb_id = ' . $this->vrb->id();
            }
        }
        // if a list of verb is set, select the word links included in the list
        if ($sql_type == '') {
            if (isset($this->vrb_lst)) {
                if ($this->vrb_lst->ids_txt() != '') {
                    $sql_type = 'AND l.verb_id IN (' . $this->vrb_lst->ids_txt() . ')';
                }
            }
        }

        // check the selection criteria and report missing parameters
        if ($sql_where == '' or $sql_wrd2 == '') {
            log_err("A word or word list must be set to show a graph.", "triple_list->load");
        } else {

            // load the word link and the destination word with one sql statement to save time
            // similar to word->load and triple->load
            // TODO check if and how GROUP BY t2.word_id, l.verb_id can / should be added
            $sql = "SELECT l.triple_id,
                       ul.triple_id AS user_triple_id,
                       l.user_id,
                       l.from_phrase_id,
                       l.verb_id,
                       l.phrase_type_id,
                       l.to_phrase_id,
                       l.triple_name,
                       l.name_given,
                       l.name_generated,
                       l.description,
                       l.values,
                       l.share_type_id,
                       l.protect_id,
                       v.verb_id,
                       v.code_id,
                       v.verb_name,
                       v.name_plural,
                       v.name_reverse,
                       v.name_plural_reverse,
                       v.formula_name,
                       v.description,
                       v.words,
                       " . $db_con->get_usr_field(sandbox::FLD_EXCLUDED, 'l', 'ul', sql_db::FLD_FORMAT_VAL) . ",
                       " . $sql_wrd1_fields . "
                       " . $sql_wrd2_fields . "
                  FROM triples l
             LEFT JOIN user_triples ul ON ul.triple_id = l.triple_id 
                                        AND ul.user_id = " . $this->user()->id() . ",
                       verbs v, 
                       " . $sql_wrd1_from . "
                       " . $sql_wrd2_from . "
                 WHERE l.verb_id = v.verb_id 
                       " . $sql_wrd1 . "
                   AND " . $sql_wrd2 . " 
                   AND " . $sql_where . "
                       " . $sql_type . " 
              ORDER BY l.verb_id, name_given;";  // maybe used word_name_t1 and word_name_t2
            // alternative: ORDER BY v.verb_id, t.values DESC, t.word_name;";
        }
        return $sql;
    }

    // load the word link without the linked objects, because in many cases the object are already loaded by the caller
    // unit tested by
    function load_old()
    {
        log_debug('triple_list->load');
        $lib = new library();

        global $db_con;

        // check the all minimal input parameters
        if ($this->user() == null) {
            log_err("The user id must be set to load a graph.", "triple_list->load");
        } else {
            $db_con->set_usr($this->user()->id());
            $sql = $this->load_sql_old($db_con);
            $db_lst = $db_con->get_old($sql);
            log_debug('triple_list->load ... sql "' . $sql . '"');
            $this->lst = array();
            $this->ids = array();
            if ($db_lst != null) {
                foreach ($db_lst as $db_lnk) {
                    if (is_null($db_lnk[sandbox::FLD_EXCLUDED]) or $db_lnk[sandbox::FLD_EXCLUDED] == 0) {
                        $new_link = new triple($this->user());
                        $new_link->row_mapper_sandbox($db_lnk);
                        if ($new_link->id() > 0) {
                            // fill the verb
                            if ($new_link->verb->id() > 0) {
                                $new_verb = new verb;
                                $new_verb->set_user($this->user());
                                $new_verb->row_mapper_verb($db_lnk);
                                $new_link->verb = $new_verb;
                            }
                            // fill the "from" word
                            // if the source word is set, the query result probably does not contain the values of the source word
                            if (isset($this->wrd)) {
                                log_debug('triple_list->load ... use "' . $this->wrd->name() . '" as from');
                                if ($this->wrd != null) {
                                    $new_link->fob = $this->wrd->phrase();
                                    $new_link->from_name = $this->wrd->name();
                                }
                            } else {
                                if ($db_lnk[word::FLD_ID . '1'] > 0) {
                                    $new_word = new word($this->user());
                                    $new_word->set_id($db_lnk[word::FLD_ID . '1']);
                                    $new_word->owner_id = $db_lnk[user::FLD_ID . '1'];
                                    $new_word->set_name($db_lnk[word::FLD_NAME . '1']);
                                    $new_word->plural = $db_lnk[word::FLD_PLURAL . '1'];
                                    $new_word->description = $db_lnk[sandbox_named::FLD_DESCRIPTION . '1'];
                                    $new_word->type_id = $db_lnk[phrase::FLD_TYPE . '1'];
                                    //$new_word->row_mapper($db_lnk);
                                    $new_word->link_type_id = $db_lnk[verb::FLD_ID];
                                    $new_link->fob = $new_word->phrase();
                                    $new_link->from_name = $new_word->name();
                                } elseif ($db_lnk[word::FLD_ID . '1'] < 0) {
                                    $new_word = new triple($this->user());
                                    $new_word->set_id($db_lnk[word::FLD_ID . '1'] * -1); // TODO check if not word_id is correct
                                    $new_link->fob = $new_word->phrase();
                                    $new_link->from_name = $new_word->name();
                                } else {
                                    log_warning('triple_list->load word missing');
                                }
                            }
                            // fill the to word
                            if ($db_lnk[word::FLD_ID . '2'] > 0) {
                                $new_word = new word($this->user());
                                $new_word->set_id($db_lnk[word::FLD_ID . '2']);
                                $new_word->owner_id = $db_lnk[user::FLD_ID . '2'];
                                $new_word->set_name($db_lnk[word::FLD_NAME . '2']);
                                $new_word->plural = $db_lnk[word::FLD_PLURAL . '2'];
                                $new_word->description = $db_lnk[sandbox_named::FLD_DESCRIPTION . '2'];
                                $new_word->type_id = $db_lnk[phrase::FLD_TYPE . '2'];
                                $new_word->link_type_id = $db_lnk[verb::FLD_ID];
                                //$added_wrd2_lst->add($new_word);
                                log_debug('added word "' . $new_word->name() . '" for verb (' . $db_lnk[verb::FLD_ID] . ')');
                                $new_link->tob = $new_word->phrase();
                                $new_link->to_name = $new_word->name();
                            } elseif ($db_lnk[word::FLD_ID . '2'] < 0) {
                                $new_word = new triple($this->user());
                                $new_word->set_id($db_lnk[word::FLD_ID . '2'] * -1);
                                $new_link->tob = $new_word->phrase();
                                $new_link->to_name = $new_word->name();
                            }
                            $this->lst[] = $new_link;
                        }
                    }
                }
            }
            log_debug('triple_list->load ... done (' . $lib->dsp_count($this->lst) . ')');
        }
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
                $this->lst[] = $lnk_to_add;
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
        foreach ($this->lst as $trp) {
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

    // description of the triple list for debugging
    function dsp_id(): string
    {
        $result = '';

        $lib = new library();
        $id = $lib->dsp_array($this->ids);
        $name = $this->name();
        if ($name <> '') {
            $result .= '"' . $name . '" (' . $id . ')';
        } else {
            $result .= 'id (' . $id . ')';
        }

        return $result;
    }

    // description of the triple list for the user
    function name(): string
    {
        $lib = new library();
        return $lib->dsp_array($this->names());
    }

    // return a list of the triple names
    // this function is called from dsp_id, so no other call is allowed
    function names(): array
    {
        $result = array();
        foreach ($this->lst as $lnk) {
            if ($lnk->name() <> '') {
                $result[] = $lnk->name();
            }
        }
        return $result;
    }

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
            foreach (array_keys($this->lst) as $lnk_id) {
                // reset the vars
                $directional_link_type_id = 0;

                $lnk = $this->lst[$lnk_id];
                // get the next link to detect if there is more than one word linked with the same link type
                // TODO check with a unit test if last element is used
                if (count($this->lst) - 1 > $lnk_id) {
                    $next_lnk = $this->lst[$lnk_id + 1];
                } else {
                    $next_lnk = $lnk;
                }

                // display type header
                if ($lnk->verb == null) {
                    log_warning('graph->display type is missing');
                } else {
                    if ($lnk->verb->id() <> $prev_verb_id) {
                        log_debug('graph->display type "' . $lnk->verb->name() . '"');

                        // select the same side of the verb
                        if ($this->direction == foaf_direction::DOWN) {
                            $directional_link_type_id = $lnk->verb->id();
                        } else {
                            $directional_link_type_id = $lnk->verb->id() * -1;
                        }

                        // display the link type
                        if ($lnk->verb->id() == $next_lnk->verb->id()) {
                            $result .= $this->wrd->plural;
                            if ($this->direction == foaf_direction::DOWN) {
                                $result .= " " . $lnk->verb->rev_plural;
                            } else {
                                $result .= " " . $lnk->verb->plural;
                            }
                        } else {
                            $result .= $this->wrd->name();
                            if ($this->direction == foaf_direction::DOWN) {
                                $result .= " " . $lnk->verb->reverse;
                            } else {
                                $result .= " " . $lnk->verb->name;
                            }
                        }
                    }
                    $result .= $html->dsp_tbl_start_half();
                    $prev_verb_id = $lnk->verb->id();

                    // display the word
                    if ($lnk->fob == null) {
                        log_warning('graph->display from is missing');
                    } else {
                        log_debug('word->dsp_graph display word ' . $lnk->fob->name());
                        $result .= '  <tr>' . "\n";
                        if ($lnk->tob != null) {
                            $dsp_obj = $lnk->tob->get_dsp_obj();
                            $result .= $dsp_obj->dsp_tbl_cell(0);
                        }
                        $result .= $lnk->dsp_obj()->btn_edit($lnk->fob->dsp_obj());
                        if ($lnk->fob != null) {
                            $dsp_obj = $lnk->fob->get_dsp_obj();
                            $result .= $dsp_obj->dsp_unlink($lnk->id());
                        }
                        $result .= '  </tr>' . "\n";
                    }

                    // use the last word as a sample for the new word type
                    $last_linked_word_id = 0;
                    if ($lnk->verb->id() == $verbs->id(verb::FOLLOW)) {
                        $last_linked_word_id = $lnk->tob->id;
                    }

                    // in case of the verb "following" continue the series after the last element
                    $start_id = 0;
                    if ($lnk->verb->id() == $verbs->id(verb::FOLLOW)) {
                        $start_id = $last_linked_word_id;
                        // and link with the same direction (looks like not needed!)
                        /* if ($directional_link_type_id > 0) {
                          $directional_link_type_id = $directional_link_type_id * -1;
                        } */
                    } else {
                        if ($lnk->fob == null) {
                            log_warning('graph->display from is missing');
                        } else {
                            $start_id = $lnk->fob->id(); // to select a similar word for the verb following
                        }
                    }

                    if ($lnk->verb->id() <> $next_lnk->verb->id()) {
                        if ($lnk->fob == null) {
                            log_warning('graph->display from is missing');
                        } else {
                            $start_id = $lnk->fob->id();
                        }
                        // give the user the possibility to add a similar word
                        $result .= '  <tr>';
                        $result .= '    <td>';
                        $result .= '      ' . \html\btn_add("Add similar word", '/http/word_add.php?verb=' .
                                $directional_link_type_id . '&word=' . $start_id . '&type=' . $lnk->tob->type_id . '&back=' . $start_id);
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
        if ($this->lst != null) {
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

        foreach ($this->lst as $trp) {
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
        foreach ($this->lst as $lnk) {
            $phr_lst->add($lnk->phrase());
        }
        return $phr_lst;
    }

}