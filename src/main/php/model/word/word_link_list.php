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

    public $lst = array(); // the list of word links
    public $usr = NULL;    // the user object of the person for whom the word list is loaded, so to say the viewer

    // fields to select a part of the graph
    public $ids = array(); // list of link ids
    public $wrd = NULL;    // show the graph elements related to this word
    public $wrd_lst = NULL;    // show the graph elements related to these words
    public $vrb = NULL;    // show the graph elements related to this verb
    public $vrb_lst = NULL;    // show the graph elements related to these verbs
    public $direction = 'down';  // either up, down or both

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
                " . $db_con->get_usr_field('description', 't' . $pos, 'u' . $pos) . ",
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
                " . $db_con->get_usr_field('description', 't' . $pos, 'u' . $pos, sql_db::FLD_FORMAT_TEXT, 'description' . $pos) . ",
                " . $db_con->get_usr_field('word_type_id', 't' . $pos, 'u' . $pos, sql_db::FLD_FORMAT_VAL) . ",
                " . $db_con->get_usr_field('excluded', 't' . $pos, 'u' . $pos, sql_db::FLD_FORMAT_VAL) . ",
                  t" . $pos . "." . $db_con->get_table_name(DB_TYPE_VALUE) . " AS values" . $pos . "";
    }

    private function load_wrd_from($pos): string
    {
        return " words t" . $pos . " LEFT JOIN user_words u" . $pos . " ON u" . $pos . ".word_id = t" . $pos . ".word_id 
                                                                       AND u" . $pos . ".user_id = " . $this->usr->id . " ";
    }

    // load the word link without the linked objects, because in many cases the object are already loaded by the caller
    function load()
    {
        log_debug('word_link_list->load');

        global $db_con;

        // check the all minimal input parameters
        if (!isset($this->usr)) {
            log_err("The user id must be set to load a graph.", "word_link_list->load");
        } else {
            // set the where clause depending on the defined select values
            $sql_where = '';
            $sql_type = '';
            $sql_wrd1_fields = '';
            $sql_wrd1_from = '';
            $sql_wrd1 = '';
            $sql_wrd2_fields = '';
            $sql_wrd2_from = '';
            $sql_wrd2 = '';
            // if the list of original word ids is set, use them for the selection
            if (isset($this->ids)) {
                $id_txt = implode(",", $this->ids);
                if ($id_txt <> '') {
                    $sql_where = 'l.word_link_id IN (' . implode(",", $this->ids) . ')';
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
            if ($sql_where == '') {
                if (isset($this->wrd)) {
                    $sql_wrd2_fields = $this->load_wrd_fields('2');
                    $sql_wrd2_from = $this->load_wrd_from('2');
                    if ($this->direction == 'up') {
                        $sql_where = 'l.from_phrase_id = ' . $this->wrd->id;
                        $sql_wrd2 = 'l.to_phrase_id = t2.word_id';
                    } else {
                        $sql_where = 'l.to_phrase_id   = ' . $this->wrd->id;
                        $sql_wrd2 = 'l.from_phrase_id = t2.word_id';
                    }
                    log_debug('word_link_list->load where wrd ' . $sql_where);
                }
            }
            if ($sql_where == '') {
                if (isset($this->wrd_lst)) {
                    log_debug('word_link_list->load based on word list');
                    $sql_wrd1_fields = $this->load_wrd_fields('');
                    $sql_wrd1_from = $this->load_wrd_from('');
                    $sql_wrd1_fields .= ', ';
                    $sql_wrd1_from .= ', ';
                    $sql_wrd2_fields = $this->load_wrd_fields('2');
                    $sql_wrd2_from = $this->load_wrd_from('2');
                    log_debug('word_link_list->load based on word list loaded');
                    if ($this->direction == 'up') {
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
            if (isset($this->vrb)) {
                if ($this->vrb->id > 0) {
                    $sql_type = 'AND l.verb_id = ' . $this->vrb->id;
                }
            }
            if (isset($this->vrb_lst)) {
                if (count($this->vrb_lst->lst) > 0) {
                    $sql_type = 'AND l.verb_id IN (' . $this->vrb_lst->ids_txt() . ')';
                }
            }

            // check the selection criteria and report missing parameters
            if ($sql_where == '' or $sql_wrd2 == '') {
                log_err("A word or word list must be set to show a graph.", "word_link_list->load");
            } else {

                // load the word link and the destination word with one sql statement to save time
                // similar to word->load and word_link->load
                // TODO check if and how GROUP BY t2.word_id, l.verb_id can / should be added
                $sql = "SELECT l.word_link_id,
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
                //$db_con = New mysql;
                $db_con->usr_id = $this->usr->id;
                $db_lst = $db_con->get($sql);
                log_debug('word_link_list->load ... sql "' . $sql . '"');
                $this->lst = array();
                $this->ids = array();
                if ($db_lst != null) {
                    foreach ($db_lst as $db_lnk) {
                        if (is_null($db_lnk['excluded']) or $db_lnk['excluded'] == 0) {
                            if ($db_lnk['word_link_id'] > 0) {
                                $new_link = new word_link;
                                $new_link->usr = $this->usr;
                                $new_link->id = $db_lnk['word_link_id'];
                                $new_link->from_id = $db_lnk['from_phrase_id'];
                                $new_link->verb_id = $db_lnk['verb_id'];
                                $new_link->to_id = $db_lnk['to_phrase_id'];
                                $new_link->description = $db_lnk['description'];
                                $new_link->name = $db_lnk['word_link_name'];
                                if ($db_lnk['verb_id'] > 0) {
                                    $new_verb = new verb;
                                    $new_verb->usr = $this->usr->id;
                                    $new_verb->row_mapper($db_lnk);
                                }
                                if ($db_lnk['word_id1'] > 0) {
                                    $new_word = new word_dsp;
                                    $new_word->usr = $this->usr;
                                    $new_word->row_mapper($db_lnk);
                                    $new_word->link_type_id = $db_lnk['verb_id'];
                                    $new_link->from = $new_word;
                                    $new_link->from_name = $new_word->name;
                                } elseif ($db_lnk['word_id1'] < 0) {
                                    $new_word = new word_link;
                                    $new_word->usr = $this->usr;
                                    $new_word->id = $db_lnk['word_id1'] * -1; // TODO check if not word_id is correct
                                    $new_link->from = $new_word;
                                    $new_link->from_name = $new_word->name;
                                } else {
                                    if (isset($this->wrd)) {
                                        log_debug('word_link_list->load ... use "' . $this->wrd->name . '" as from');
                                        $new_link->from = $this->wrd;
                                        $new_link->from_name = $this->wrd->name;
                                    }
                                }
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
                                    $new_link->to = $new_word;
                                    $new_link->to_name = $new_word->name;
                                } elseif ($db_lnk['word_id2'] < 0) {
                                    $new_word = new word_link;
                                    $new_word->usr = $this->usr;
                                    $new_word->id = $db_lnk['word_id2'] * -1;
                                    $new_link->to = $new_word;
                                    $new_link->to_name = $new_word->name;
                                }
                                $this->lst[] = $new_link;
                                $this->ids[] = $new_link->id;
                            }
                        }
                    }
                }
                log_debug('word_link_list->load ... done (' . count($this->lst) . ')');
            }
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

        $id = implode(",", $this->ids);
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
        return implode(",", $this->names());
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
                if (count($this->lst) > $lnk_id) {
                    $next_lnk = $this->lst[$lnk_id + 1];
                } else {
                    $next_lnk = $lnk;
                }

                // display type header
                if ($lnk->verb_id <> $prev_verb_id) {
                    log_debug('graph->display type "' . $lnk->link_type->name . '"');

                    // select the same side of the verb
                    if ($this->direction == "down") {
                        $directional_link_type_id = $lnk->verb_id;
                    } else {
                        $directional_link_type_id = $lnk->verb_id * -1;
                    }

                    // display the link type
                    if ($lnk->verb_id == $next_lnk->verb_id) {
                        $result .= $this->wrd->plural;
                        if ($this->direction == "down") {
                            $result .= " " . $lnk->link_type->rev_plural;
                        } else {
                            $result .= " " . $lnk->link_type->plural;
                        }
                    } else {
                        $result .= $this->wrd->name;
                        if ($this->direction == "down") {
                            $result .= " " . $lnk->link_type->reverse;
                        } else {
                            $result .= " " . $lnk->link_type->name;
                        }
                    }
                    $result .= dsp_tbl_start_half();
                    $prev_verb_id = $lnk->verb_id;
                }

                // display the word
                log_debug('word->dsp_graph display word ' . $lnk->from->name);
                $result .= '  <tr>' . "\n";
                $result .= $lnk->to->dsp_tbl_cell(0);
                $result .= $lnk->dsp_btn_edit($lnk->from);
                $result .= $lnk->from->dsp_unlink($lnk->id);
                $result .= '  </tr>' . "\n";

                // use the last word as a sample for the new word type
                $last_linked_word_id = 0;
                if ($lnk->verb_id == cl(DBL_LINK_TYPE_FOLLOW)) {
                    $last_linked_word_id = $lnk->to->id;
                }

                // in case of the verb "following" continue the series after the last element
                if ($lnk->verb_id == cl(DBL_LINK_TYPE_FOLLOW)) {
                    $start_id = $last_linked_word_id;
                    // and link with the same direction (looks like not needed!)
                    /* if ($directional_link_type_id > 0) {
                      $directional_link_type_id = $directional_link_type_id * -1;
                    } */
                } else {
                    $start_id = $lnk->from->id; // to select a similar word for the verb following
                }

                if ($lnk->verb_id <> $next_lnk->verb_id) {
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