<?php

/*

  word_link_list.php - a list of word links, mainly used to build a RDF graph
  ------------------
  
  example:
  for company the list of linked words should contain "... has a balance sheet" and "... has a cash flow statement"
  
  
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

class word_link_list
{
    const DIRECTION_UP = 'up';
    const DIRECTION_DOWN = 'down';
    const DIRECTION_BOTH = 'both';

    public array $lst = array(); // the list of word links
    public ?user $usr = null;    // the user object of the person for whom the word list is loaded, so to say the viewer

    // fields to select a part of the graph
    public array $ids = array();  // list of link ids
    public ?word $wrd = null;          // show the graph elements related to this word
    public ?word_list $wrd_lst = null; // show the graph elements related to these words
    public ?verb $vrb = null;     // show the graph elements related to this verb
    public ?verb_list $vrb_lst = null; // show the graph elements related to these verbs
    public string $direction = self::DIRECTION_DOWN;  // either up, down or both

    /*
     * not really used?
     *
    private function load_lnk_fields($pos): string
    {
        global $db_con;

        $sql = "t" . $pos . ".word_id AS word_id" . $pos . ",
                t" . $pos . ".user_id AS user_id" . $pos . ",
                " . $db_con->get_usr_field('word_name', 't' . $pos, 'u' . $pos) . ",
                " . $db_con->get_usr_field('plural', 't' . $pos, 'u' . $pos) . ",
                " . $db_con->get_usr_field(sql_db::FLD_DESCRIPTION, 't' . $pos, 'u' . $pos) . ",
                " . $db_con->get_usr_field('word_type_id', 't' . $pos, 'u' . $pos, sql_db::FLD_FORMAT_VAL) . ",
                " . $db_con->get_usr_field('excluded', 't' . $pos, 'u' . $pos, sql_db::FLD_FORMAT_VAL) . ",
                  t" . $pos . "." . $db_con->get_table_name(DB_TYPE_VALUE) . " AS values" . $pos . "";
        return $sql;
    }

    private function load_lnk_from($pos): string
    {
        return " words t" . $pos . " LEFT JOIN user_words u" . $pos . " ON u" . $pos . ".word_id = t" . $pos . ".word_id 
                                           AND u" . $pos . ".user_id = " . $this->usr->id . " ";
    }
    */

    private function load_wrd_fields($pos): string
    {
        global $db_con;

        return "t" . $pos . ".word_id AS word_id" . $pos . ",
                t" . $pos . ".user_id AS user_id" . $pos . ",
                " . $db_con->get_usr_field('word_name', 't' . $pos, 'u' . $pos, sql_db::FLD_FORMAT_TEXT, 'word_name' . $pos) . ",
                " . $db_con->get_usr_field('plural', 't' . $pos, 'u' . $pos, sql_db::FLD_FORMAT_TEXT, 'plural' . $pos) . ",
                " . $db_con->get_usr_field(sql_db::FLD_DESCRIPTION, 't' . $pos, 'u' . $pos, sql_db::FLD_FORMAT_TEXT, sql_db::FLD_DESCRIPTION . $pos) . ",
                " . $db_con->get_usr_field('word_type_id', 't' . $pos, 'u' . $pos, sql_db::FLD_FORMAT_VAL, 'word_type_id' . $pos) . ",
                " . $db_con->get_usr_field('view_id', 't' . $pos, 'u' . $pos, sql_db::FLD_FORMAT_VAL, 'view_id' . $pos) . ",
                " . $db_con->get_usr_field('excluded', 't' . $pos, 'u' . $pos, sql_db::FLD_FORMAT_VAL, 'excluded' . $pos) . ",
                  t" . $pos . "." . $db_con->get_table_name(DB_TYPE_VALUE) . " AS values" . $pos . "";
    }

    private function load_wrd_from($pos): string
    {
        return " words t" . $pos . " LEFT JOIN user_words u" . $pos . " ON u" . $pos . ".word_id = t" . $pos . ".word_id 
                                                                       AND u" . $pos . ".user_id = " . $this->usr->id . " ";
    }

    // returns the of predefined sql statement (must be corresponding to load_sql)
    function load_sql_name(): string
    {
        $sql_name = '';
        $sql_name_type = '';

        if (isset($this->ids)) {
            if (count($this->ids) > 0) {
                $sql_name = 'word_link_list_by_ids';
            }
        }
        if ($sql_name == '') {
            if (isset($this->wrd)) {
                if ($this->direction == self::DIRECTION_DOWN) {
                    $sql_name = 'word_link_list_word_down';
                } else if ($this->direction == self::DIRECTION_UP) {
                    $sql_name = 'word_link_list_word_up';
                } else if ($this->direction == self::DIRECTION_BOTH) {
                    $sql_name = 'word_link_list_word_both';
                    log_warning('Word link search direction ' . $this->direction . ' not yet expected');
                } else {
                    log_err('Word link search direction ' . $this->direction . ' not expected');
                }
            }
        }
        if ($sql_name == '') {
            if (isset($this->wrd_lst)) {
                if ($this->wrd_lst->ids_txt() != '') {
                    if ($this->direction == self::DIRECTION_DOWN) {
                        $sql_name = 'word_link_list_word_list_down';
                    } else if ($this->direction == self::DIRECTION_UP) {
                        $sql_name = 'word_link_list_word_list_up';
                    } else if ($this->direction == self::DIRECTION_BOTH) {
                        $sql_name = 'word_link_list_word_list_both';
                        log_warning('Word link search direction ' . $this->direction . ' not yet expected');
                    } else {
                        log_err('Word link search direction ' . $this->direction . ' not expected');
                    }
                }
            }
        }
        // with additional verb selection
        if (isset($this->vrb)) {
            if ($this->vrb->id > 0) {
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
    function load_sql(): string
    {

        global $db_con;

        $result = '';

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
                $id_txt = sql_array($this->ids);
                if ($id_txt <> '') {
                    $sql_where = 'l.word_link_id IN (' . $id_txt . ')';
                    $sql_wrd1_fields = $this->load_wrd_fields('');
                    $sql_wrd1_from = $this->load_wrd_from('');
                    $sql_wrd1 = 'AND l.from_phrase_id = t.word_id';
                    $sql_wrd1_fields .= ', ';
                    $sql_wrd1_from .= ', ';
                    $sql_wrd2_fields = $this->load_wrd_fields('2');
                    $sql_wrd2_from = $this->load_wrd_from('2');
                    $sql_wrd2 = 'l.to_phrase_id = t2.word_id';
                    log_debug('word_link_list->load where ids ' . $sql_where);
                }
            }
        }
        // .. else if an original word is set, select all related word links depending on the direction
        // in this case only the fields from the target words needs to be included in the result
        if ($sql_where == '') {
            if (isset($this->wrd)) {
                $sql_wrd2_fields = $this->load_wrd_fields('2');
                $sql_wrd2_from = $this->load_wrd_from('2');
                if ($this->direction == self::DIRECTION_UP) {
                    $sql_where = 'l.from_phrase_id = ' . $this->wrd->id;
                    $sql_wrd2 = 'l.to_phrase_id = t2.word_id';
                } else {
                    $sql_where = 'l.to_phrase_id   = ' . $this->wrd->id;
                    $sql_wrd2 = 'l.from_phrase_id = t2.word_id';
                }
                log_debug('word_link_list->load where wrd ' . $sql_where);
            }
        }
        // .. else if a list of original words is given select all word links related to the list
        // in this case also the fields from the original words needs to be included in the result
        if ($sql_where == '') {
            if (isset($this->wrd_lst)) {
                if ($this->wrd_lst->ids_txt() != '') {
                    log_debug('word_link_list->load based on word list');
                    $sql_wrd1_fields = $this->load_wrd_fields('');
                    $sql_wrd1_from = $this->load_wrd_from('');
                    $sql_wrd1_fields .= ', ';
                    $sql_wrd1_from .= ', ';
                    $sql_wrd2_fields = $this->load_wrd_fields('2');
                    $sql_wrd2_from = $this->load_wrd_from('2');
                    log_debug('word_link_list->load based on word list loaded');
                    if ($this->direction == self::DIRECTION_UP) {
                        $sql_where = 'l.from_phrase_id IN (' . $this->wrd_lst->ids_txt() . ')';
                        $sql_wrd1 = 'AND l.from_phrase_id = t.word_id';
                        $sql_wrd2 = 'l.to_phrase_id   = t2.word_id';
                    } else {
                        $sql_where = 'l.to_phrase_id   IN (' . $this->wrd_lst->ids_txt() . ')';
                        $sql_wrd1 = 'AND l.to_phrase_id   = t.word_id';
                        $sql_wrd2 = 'l.from_phrase_id = t2.word_id';
                    }
                    log_debug('word_link_list->load where wrd in ' . $sql_where);
                }
            }
        }

        // if a verb is set, select only the word links with the given verb
        if (isset($this->vrb)) {
            if ($this->vrb->id > 0) {
                $sql_type = 'AND l.verb_id = ' . $this->vrb->id;
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
            log_err("A word or word list must be set to show a graph.", "word_link_list->load");
        } else {

            // load the word link and the destination word with one sql statement to save time
            // similar to word->load and word_link->load
            // TODO check if and how GROUP BY t2.word_id, l.verb_id can / should be added
            $result = "SELECT l.word_link_id,
                       l.from_phrase_id,
                       l.verb_id,
                       l.to_phrase_id,
                       l.description,
                       l.word_link_name,
                       v.verb_id,
                       v.code_id,
                       v.verb_name,
                       v.name_plural,
                       v.name_reverse,
                       v.name_plural_reverse,
                       v.formula_name,
                       v.description,
                       v.words,
                       " . $db_con->get_usr_field('excluded', 'l', 'ul', sql_db::FLD_FORMAT_VAL) . ",
                       " . $sql_wrd1_fields . "
                       " . $sql_wrd2_fields . "
                  FROM word_links l
             LEFT JOIN user_word_links ul ON ul.word_link_id = l.word_link_id 
                                        AND ul.user_id = " . $this->usr->id . ",
                       verbs v, 
                       " . $sql_wrd1_from . "
                       " . $sql_wrd2_from . "
                 WHERE l.verb_id = v.verb_id 
                       " . $sql_wrd1 . "
                   AND " . $sql_wrd2 . " 
                   AND " . $sql_where . "
                       " . $sql_type . " 
              ORDER BY l.verb_id, word_link_name;";  // maybe used word_name_t1 and word_name_t2
            // alternative: ORDER BY v.verb_id, t.values DESC, t.word_name;";
        }
        return $result;
    }

    // load the word link without the linked objects, because in many cases the object are already loaded by the caller
    // unit tested by
    function load()
    {
        log_debug('word_link_list->load');

        global $db_con;

        // check the all minimal input parameters
        if (!isset($this->usr)) {
            log_err("The user id must be set to load a graph.", "word_link_list->load");
        } else {
            $db_con->set_usr($this->usr->id);
            $sql = $this->load_sql();
            $db_lst = $db_con->get($sql);
            log_debug('word_link_list->load ... sql "' . $sql . '"');
            $this->lst = array();
            $this->ids = array();
            if ($db_lst != null) {
                foreach ($db_lst as $db_lnk) {
                    if (is_null($db_lnk['excluded']) or $db_lnk['excluded'] == 0) {
                        if ($db_lnk['word_link_id'] > 0) {
                            // create one work link object and fill it
                            $new_link = new word_link;
                            // fill the fields used for searching
                            $new_link->usr = $this->usr;
                            $new_link->id = $db_lnk['word_link_id'];
                            $new_link->from->id = $db_lnk['from_phrase_id'];
                            $new_link->verb->id = $db_lnk['verb_id'];
                            $new_link->to->id = $db_lnk['to_phrase_id'];
                            $new_link->description = $db_lnk[sql_db::FLD_DESCRIPTION];
                            $new_link->name = $db_lnk['word_link_name'];
                            // fill the verb
                            if ($db_lnk['verb_id'] > 0) {
                                $new_verb = new verb;
                                $new_verb->usr = $this->usr;
                                $new_verb->row_mapper($db_lnk);
                                $new_link->verb = $new_verb;
                            }
                            // fill the from word
                            // if the source word is set, the query result probably does not contain the values of the source word
                            if (isset($this->wrd)) {
                                log_debug('word_link_list->load ... use "' . $this->wrd->name . '" as from');
                                if ($this->wrd != null) {
                                    $new_link->from = $this->wrd->phrase();
                                    $new_link->from_name = $this->wrd->name;
                                }
                            } else {
                                if ($db_lnk['word_id'] > 0) {
                                    $new_word = new word_dsp;
                                    $new_word->usr = $this->usr;
                                    $new_word->row_mapper($db_lnk);
                                    $new_word->link_type_id = $db_lnk['verb_id'];
                                    $new_link->from = $new_word->phrase();
                                    $new_link->from_name = $new_word->name;
                                } elseif ($db_lnk['word_id'] < 0) {
                                    $new_word = new word_link;
                                    $new_word->usr = $this->usr;
                                    $new_word->id = $db_lnk['word_id'] * -1; // TODO check if not word_id is correct
                                    $new_link->from = $new_word->phrase();
                                    $new_link->from_name = $new_word->name;
                                } else {
                                    log_warning('word_link_list->load word missing');
                                }
                            }
                            // fill the to word
                            if ($db_lnk['word_id2'] > 0) {
                                $new_word = new word_dsp;
                                $new_word->usr = $this->usr;
                                $new_word->id = $db_lnk['word_id2'];
                                $new_word->owner_id = $db_lnk['user_id2'];
                                $new_word->name = $db_lnk['word_name2'];
                                $new_word->plural = $db_lnk['plural2'];
                                $new_word->description = $db_lnk['description2'];
                                $new_word->type_id = $db_lnk['word_type_id2'];
                                $new_word->link_type_id = $db_lnk['verb_id'];
                                //$added_wrd2_lst->add($new_word);
                                log_debug('word_link_list->load -> added word "' . $new_word->name . '" for verb (' . $db_lnk['verb_id'] . ')');
                                $new_link->to = $new_word->phrase();
                                $new_link->to_name = $new_word->name;
                            } elseif ($db_lnk['word_id2'] < 0) {
                                $new_word = new word_link;
                                $new_word->usr = $this->usr;
                                $new_word->id = $db_lnk['word_id2'] * -1;
                                $new_link->to = $new_word->phrase();
                                $new_link->to_name = $new_word->name;
                            }
                            $this->lst[] = $new_link;
                            $this->ids[] = $new_link->id;
                        }
                    }
                }
            }
            log_debug('word_link_list->load ... done (' . dsp_count($this->lst) . ')');
        }
    }

    // add one triple to the triple list, but only if it is not yet part of the list
    function add($lnk_to_add)
    {
        log_debug('word_link_list->add ' . $lnk_to_add->dsp_id());
        if (!in_array($lnk_to_add->id, $this->ids)) {
            if ($lnk_to_add->id > 0) {
                $this->lst[] = $lnk_to_add;
                $this->ids[] = $lnk_to_add->id;
            }
        }
    }

    /*
    display functions
    */

    // description of the triple list for debugging
    function dsp_id(): string
    {
        $result = '';

        $id = dsp_array($this->ids);
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
        return dsp_array($this->names());
    }

    // return a list of the triple names
    // this function is called from dsp_id, so no other call is allowed
    function names(): array
    {
        $result = array();
        if (isset($this->lst)) {
            foreach ($this->lst as $lnk) {
                if ($lnk->name <> '') {
                    $result[] = $lnk->name;
                }
            }
        }
        return $result;
    }

    // shows all words the link to the given word
    // returns the html code to select a word that can be edit
    function display($back): string
    {
        $result = '';

        // check the all minimal input parameters
        if (!isset($this->usr)) {
            log_err("The user id must be set to load a graph.", "word_link_list->load");
        } else {
            if (isset($this->wrd)) {
                log_debug('graph->display for ' . $this->wrd->name . ' ' . $this->direction . ' and user ' . $this->usr->name . ' called from ' . $back);
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
                    if ($lnk->verb->id <> $prev_verb_id) {
                        log_debug('graph->display type "' . $lnk->verb->name . '"');

                        // select the same side of the verb
                        if ($this->direction == verb::DIRECTION_DOWN) {
                            $directional_link_type_id = $lnk->verb->id;
                        } else {
                            $directional_link_type_id = $lnk->verb->id * -1;
                        }

                        // display the link type
                        if ($lnk->verb->id == $next_lnk->verb->id) {
                            $result .= $this->wrd->plural;
                            if ($this->direction == verb::DIRECTION_DOWN) {
                                $result .= " " . $lnk->verb->rev_plural;
                            } else {
                                $result .= " " . $lnk->verb->plural;
                            }
                        } else {
                            $result .= $this->wrd->name;
                            if ($this->direction == verb::DIRECTION_DOWN) {
                                $result .= " " . $lnk->verb->reverse;
                            } else {
                                $result .= " " . $lnk->verb->name;
                            }
                        }
                    }
                    $result .= dsp_tbl_start_half();
                    $prev_verb_id = $lnk->verb->id;

                    // display the word
                    if ($lnk->from == null) {
                        log_warning('graph->display from is missing');
                    } else {
                        log_debug('word->dsp_graph display word ' . $lnk->from->name);
                        $result .= '  <tr>' . "\n";
                        if ($lnk->to != null) {
                            $dsp_obj = $lnk->to->get_dsp_obj();
                            $result .= $dsp_obj->dsp_tbl_cell(0);
                        }
                        $result .= $lnk->dsp_btn_edit($lnk->from);
                        if ($lnk->from != null) {
                            $dsp_obj = $lnk->from->get_dsp_obj();
                            $result .= $dsp_obj->dsp_unlink($lnk->id);
                        }
                        $result .= '  </tr>' . "\n";
                    }

                    // use the last word as a sample for the new word type
                    $last_linked_word_id = 0;
                    if ($lnk->verb->id == cl(db_cl::VERB, verb::DBL_FOLLOW)) {
                        $last_linked_word_id = $lnk->to->id;
                    }

                    // in case of the verb "following" continue the series after the last element
                    $start_id = 0;
                    if ($lnk->verb->id == cl(db_cl::VERB, verb::DBL_FOLLOW)) {
                        $start_id = $last_linked_word_id;
                        // and link with the same direction (looks like not needed!)
                        /* if ($directional_link_type_id > 0) {
                          $directional_link_type_id = $directional_link_type_id * -1;
                        } */
                    } else {
                        if ($lnk->from == null) {
                            log_warning('graph->display from is missing');
                        } else {
                            $start_id = $lnk->from->id; // to select a similar word for the verb following
                        }
                    }

                    if ($lnk->verb->id <> $next_lnk->verb->id) {
                        if ($lnk->from == null) {
                            log_warning('graph->display from is missing');
                        } else {
                            $start_id = $lnk->from->id;
                        }
                        // give the user the possibility to add a similar word
                        $result .= '  <tr>';
                        $result .= '    <td>';
                        $result .= '      ' . btn_add("Add similar word", '/http/word_add.php?verb=' . $directional_link_type_id . '&word=' . $start_id . '&type=' . $lnk->to->type_id . '&back=' . $start_id);
                        $result .= '    </td>';
                        $result .= '  </tr>';

                        $result .= dsp_tbl_end();
                        $result .= '<br>';
                    }
                }
            }
        }
        return $result;
    }

    // returns the number of phrases in this list
    function count(): int
    {
        return count($this->lst);
    }


    /*
    convert functions
    */

    // convert the word list object into a phrase list object
    function phrase_lst(): phrase_list
    {
        $phr_lst = new phrase_list;
        $phr_lst->usr = $this->usr;
        foreach ($this->lst as $lnk) {
            $phr_lst->lst[] = $lnk->phrase();
        }
        return $phr_lst;
    }

}