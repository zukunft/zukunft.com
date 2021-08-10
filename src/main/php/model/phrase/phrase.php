<?php

/*

  phrase.php - either a word or a triple
  ----------
  
  this is not saved in a separate table
  e.g. to build a selector the entries are caught either from the words or word_links table
  
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

class phrase
{

    public ?int $id = null;            // if positive the database id of the word or if negative of a triple
    public ?user $usr = null;          // the person for whom the word is loaded, so to say the viewer
    public ?string $name = null;       // simply the word or triple name to reduce the number of "->" on the code
    public ?string $description = '';  // simply the word or triple description to reduce the number of "->" on the code
    public ?object $obj = null;        // if loaded the linked word or triple object

    // in memory only fields
    public ?string $type_name = null;  //
    public ?string $is_wrd = null;     // the main type object e.g. for "ABB" it is the word object for "Company"
    public ?int $is_wrd_id = null;     // the id for the is object
    public ?int $dsp_pos = null;       // position of the word on the screen
    public ?int $dsp_lnk_id = null;    // position or link id based on which to item is displayed on the screen
    public ?int $link_type_id = null;  // used in the word list to know based on which relation the word was added to the list

    //  load either a word or triple
    function load(): bool
    {
        log_debug('phrase->load ' . $this->dsp_id());
        $result = false;
        if ($this->id < 0) {
            $lnk = new word_link;
            $lnk->id = $this->id * -1;
            $lnk->usr = $this->usr;
            $lnk->load();
            $this->obj = $lnk;
            $this->name = $lnk->name; // is this really useful? better save execution time and have longer code using ->obj->name
            log_debug('phrase->loaded triple ' . $this->dsp_id());
        } elseif ($this->id > 0) {
            $wrd = new word_dsp;
            $wrd->id = $this->id;
            $wrd->usr = $this->usr;
            $wrd->load();
            $this->obj = $wrd;
            $this->name = $wrd->name;
            log_debug('phrase->loaded word ' . $this->dsp_id());
        } elseif ($this->name <> '') {
            // add to load word link
            $trm = new term;
            $trm->name = $this->name;
            $trm->usr = $this->usr;
            $trm->load();
            if ($trm->type == 'word') {
                $this->obj = $trm->obj;
                $this->id = $trm->id;
                log_debug('phrase->loaded word ' . $this->dsp_id() . ' by name');
            } elseif ($trm->type == 'triple') {
                $this->obj = $trm->obj;
                $this->id = $trm->id * -1;
                log_debug('phrase->loaded triple ' . $this->dsp_id() . ' by name');
            } elseif ($trm->type == 'formula') {
                if (isset($trm->obj->name_wrd)) {
                    $this->obj = $trm->obj->name_wrd;
                    $this->id = $trm->obj->name_wrd->id;
                    log_debug('phrase->loaded formula ' . $this->dsp_id() . ' by name');
                }
            } else {
                if ($this->type_name == '') {
                    // TODO check that this ($phrase->load) is never used for an error detection
                    log_warning('"' . $this->name . '" not found.', "phrase->load");
                } else {
                    log_err('"' . $this->name . '" has the type ' . $this->type_name . ' which is not expected for a phrase.', "phrase->load");
                }
            }
        }
        log_debug('phrase->load done ' . $this->dsp_id());
        return $result;
    }

    //
    function main_word()
    {
        log_debug('phrase->main_word ' . $this->dsp_id());
        $result = null;

        if ($this->id == 0 or $this->name == '') {
            $this->load();
        }
        if ($this->id < 0) {
            $lnk = $this->obj;
            $lnk->load_objects(); // try to be on the save side and it is anyway checked if loading is really needed
            $result = $lnk->from;
        } elseif ($this->id > 0) {
            $result = $this->obj;
        } else {
            log_err('"' . $this->name . '" has the type ' . $this->type_name . ' which is not expected for a phrase.', "phrase->main_word");
        }
        log_debug('phrase->main_word done ' . $result->dsp_id());
        return $result;
    }

    /**
     * to enable the recursive function in work_link
     */
    function wrd_lst(): word_list
    {
        $wrd_lst = new word_list;
        $wrd_lst->usr = $this->usr;
        if ($this->id < 0) {
            $sub_wrd_lst = $this->wrd_lst();
            foreach ($sub_wrd_lst as $wrd) {
                $wrd_lst->add($wrd);
            }
        } else {
            $wrd_lst->add($this->obj);
        }
        return $wrd_lst;
    }

    function type_id()
    {
        log_debug('phrase->type_id ' . $this->dsp_id());
        $result = null;

        $wrd = $this->main_word();
        $result = $wrd->type_id;

        log_debug('phrase->type_id for ' . $this->dsp_id() . ' is ' . $result);
        return $result;
    }

    /*
      im- and export functions
    */

    /**
     * import a phrase object from a JSON array object
     *
     * @param array $json_obj an array with the data of the json object
     * @param bool $do_save can be set to false for unit testing
     * @return bool true if the import has been successfully saved to the database
     */
    function import_obj(string $json_value, bool $do_save = true): bool
    {
        $result = false;
        $this->name = $json_value;
        if ($do_save) {
            $this->load();
            if ($this->id == 0) {
                $wrd = new word;
                $wrd->name = $json_value;
                $wrd->usr = $this->usr;
                $wrd->load();
                if ($wrd->id == 0) {
                    $wrd->name = $json_value;
                    $wrd->type_id = cl(db_cl::WORD_TYPE, word_type_list::DBL_TIME);
                    $wrd->save();
                }
                if ($wrd->id == 0) {
                    log_err('Cannot add time word "' . $json_value . '" when importing ' . $this->dsp_id(), 'value->import_obj');
                } else {
                    $this->id = $wrd->id;
                }
            }
        }

        return $result;
    }

    /*
    data retrieval functions
    */

    // get a list of all values related to this phrase
    function val_lst()
    {
        log_debug('phrase->val_lst for ' . $this->dsp_id() . ' and user "' . $this->usr->name . '"');
        $val_lst = new value_list;
        $val_lst->usr = $this->usr;
        $val_lst->phr = $this;
        $val_lst->page_size = SQL_ROW_MAX;
        $val_lst->load();
        log_debug('phrase->val_lst -> got ' . count($val_lst->lst));
        return $val_lst;
    }

    /*

    display functions

    */

    // display the unique id fields
    function dsp_id(): string
    {
        $result = '';

        if ($this->name <> '') {
            $result .= '"' . $this->name . '"';
            if ($this->id > 0) {
                $result .= ' (' . $this->id . ')';
            }
        } else {
            $result .= $this->id;
        }
        if (isset($this->usr)) {
            $result .= ' for user ' . $this->usr->id . ' (' . $this->usr->name . ')';
        }
        return $result;
    }

    // return the name (just because all objects should have a name function)
    function name(): string
    {
        //$result = $this->name;
        $result = '"' . $this->name . '"';
        return $result;
    }

    function name_linked(): string
    {
        return '<a href="/http/view.php?words=' . $this->id . '" title="' . $this->description . '">' . $this->name . '</a>';
    }

    function dsp_tbl()
    {
        if (!isset($this->obj)) {
            $this->load();
        }
        log_debug('phrase->dsp_tbl for ' . $this->dsp_id());
        // the function dsp_tbl should exists for words and triples
        $result = $this->obj->dsp_tbl();
        return $result;
    }

    function dsp_tbl_row()
    {
        // the function dsp_tbl_row should exists for words and triples
        if (isset($this->obj)) {
            $result = $this->obj->dsp_tbl_row();
        } else {
            log_err('The phrase object is missing for ' . $this->dsp_id() . '.', "formula_value->load");
        }
        return $result;
    }

    // return the html code to display a word
    function display(): string
    {
        return '<a href="/http/view.php?words=' . $this->id . '">' . $this->name . '</a>';
    }

    // simply to display a single word or triple link
    function dsp_link(): string
    {
        return '<a href="/http/view.php?words=' . $this->id . '" title="' . $this->description . '">' . $this->name . '</a>';
    }

    // similar to dsp_link
    function dsp_link_style($style)
    {
        return '<a href="/http/view.php?words=' . $this->id . '" title="' . $this->description . '" class="' . $style . '">' . $this->name . '</a>';
    }

    // helper function that returns a word list object just with the word object
    function lst()
    {
        $phr_lst = new phrase_list;
        $phr_lst->usr = $this->usr;
        $phr_lst->add($this);
        log_debug('phrase->lst -> ' . $phr_lst->name());
        return $phr_lst;
    }

    // returns a list of phrase that are related to this word e.g. for "ABB" it will return "Company" (but not "ABB"???)
    function is()
    {
        $this_lst = $this->lst();
        $phr_lst = $this_lst->is();
        //$phr_lst->add($this,);
        log_debug('phrase->is -> ' . $this->dsp_id() . ' is a ' . $phr_lst->name());
        return $phr_lst;
    }

    public static function cmp($a, $b)
    {
        return strcmp($a->name, $b->name);
    }

    // returns a list of words that are related to this word e.g. for "ABB" it will return "Company" (but not "ABB"???)
    /*  function is () {
        if ($this->id > 0) {
          $wrd_lst = $this->parents();
        } else {
        }

        zu_debug('phrase->is -> '.$this->dsp_id().' is a '.$wrd_lst->name());
        return $wrd_lst;
      } */

    // true if the word id has a "is a" relation to the related word
    // e.g.for the given word string
    function is_a($related_phrase)
    {
        log_debug('phrase->is_a (' . $this->dsp_id() . ',' . $related_phrase->name . ')');

        $result = false;
        $is_phrases = $this->is(); // should be taken from the original array to increase speed
        if (in_array($related_phrase->id, $is_phrases->ids)) {
            $result = true;
        }

        log_debug('phrase->is_a -> ' . zu_dsp_bool($result) . '' . $this->id);
        return $result;
    }

    // SQL to list the user phrases (related to a type if needed)
    function sql_list($type): string
    {
        log_debug('phrase->sql_list');
        global $db_con;

        $sql_type_from = '';
        $sql_type_where = '';

        // if no phrase type is define, list all words and triples
        // todo: but if word has several types don't offer to the user to select the simple word
        // TODO ERROR: FEHLER:  Spalte »u.excluded« muss in der GROUP-BY-Klausel erscheinen oder in einer Aggregatfunktion verwendet werden
        // LINE 22:                                   CASE WHEN (u.excluded IS N...
        //                                                      ^
        $sql_words = 'SELECT DISTINCT w.word_id AS id, 
                             ' . $db_con->get_usr_field("word_name", "w", "u", sql_db::FLD_FORMAT_TEXT, "phrase_name") . ',
                             ' . $db_con->get_usr_field("excluded", "w", "u", sql_db::FLD_FORMAT_BOOL) . '
                        FROM words w   
                   LEFT JOIN user_words u ON u.word_id = w.word_id 
                                         AND u.user_id = ' . $this->usr->id . ' ';
        $sql_triples = 'SELECT DISTINCT l.word_link_id * -1 AS id, 
                               ' . $db_con->get_usr_field("word_link_name", "l", "u", sql_db::FLD_FORMAT_TEXT, "phrase_name") . ',
                               ' . $db_con->get_usr_field("excluded", "l", "u", sql_db::FLD_FORMAT_BOOL) . '
                          FROM word_links l
                     LEFT JOIN user_word_links u ON u.word_link_id = l.word_link_id 
                                                AND u.user_id = ' . $this->usr->id . ' ';

        if (isset($type)) {
            if ($type->id > 0) {

                // select all phrase ids of the given type e.g. ABB, DANONE, Zurich
                $sql_where_exclude = 'excluded = 0';
                $sql_field_names = 'id, phrase_name, excluded';
                $sql_wrd_all = 'SELECT from_phrase_id AS id FROM (
                                        SELECT DISTINCT
                                               l.from_phrase_id,    
                                               ' . $db_con->get_usr_field("excluded", "l", "u", sql_db::FLD_FORMAT_BOOL) . '
                                          FROM word_links l
                                     LEFT JOIN user_word_links u ON u.word_link_id = l.word_link_id 
                                                                AND u.user_id = ' . $this->usr->id . '
                                         WHERE l.to_phrase_id = ' . $type->id . ' 
                                           AND l.verb_id = ' . cl(db_cl::VERB, verb::DBL_IS) . ' ) AS a 
                                         WHERE ' . $sql_where_exclude . ' ';

                // ... out of all those get the phrase ids that have also other types e.g. Zurich (Canton)
                $sql_wrd_other = 'SELECT from_phrase_id FROM (
                                        SELECT DISTINCT
                                               l.from_phrase_id,    
                                               ' . $db_con->get_usr_field("excluded", "l", "u", sql_db::FLD_FORMAT_BOOL) . '
                                          FROM word_links l
                                     LEFT JOIN user_word_links u ON u.word_link_id = l.word_link_id 
                                                                AND u.user_id = ' . $this->usr->id . '
                                         WHERE l.to_phrase_id <> ' . $type->id . ' 
                                           AND l.verb_id = ' . cl(db_cl::VERB, verb::DBL_IS) . '
                                           AND l.from_phrase_id IN (' . $sql_wrd_all . ') ) AS o 
                                         WHERE ' . $sql_where_exclude . ' ';

                // if a word has no other type, use the word
                $sql_words = 'SELECT DISTINCT ' . $sql_field_names . ' FROM (
                      SELECT DISTINCT
                             w.word_id AS id, 
                             ' . $db_con->get_usr_field("word_name", "w", "u", sql_db::FLD_FORMAT_TEXT, "phrase_name") . ',
                             ' . $db_con->get_usr_field("excluded", "w", "u", sql_db::FLD_FORMAT_BOOL) . '
                        FROM ( ' . $sql_wrd_all . ' ) a, words w
                   LEFT JOIN user_words u ON u.word_id = w.word_id 
                                         AND u.user_id = ' . $this->usr->id . '
                       WHERE w.word_id NOT IN ( ' . $sql_wrd_other . ' )                                        
                         AND w.word_id = a.id ) AS w 
                       WHERE ' . $sql_where_exclude . ' ';

                // if a word has another type, use the triple
                $sql_triples = 'SELECT DISTINCT ' . $sql_field_names . ' FROM (
                        SELECT DISTINCT
                               l.word_link_id * -1 AS id, 
                               ' . $db_con->get_usr_field("word_link_name", "l", "u", sql_db::FLD_FORMAT_TEXT, "phrase_name") . ',
                               ' . $db_con->get_usr_field("excluded", "l", "u", sql_db::FLD_FORMAT_BOOL) . '
                          FROM word_links l
                     LEFT JOIN user_word_links u ON u.word_link_id = l.word_link_id 
                                                AND u.user_id = ' . $this->usr->id . '
                         WHERE l.from_phrase_id IN ( ' . $sql_wrd_other . ')                                        
                           AND l.verb_id = ' . cl(db_cl::VERB, verb::DBL_IS) . '
                           AND l.to_phrase_id = ' . $type->id . ' ) AS t 
                         WHERE ' . $sql_where_exclude . ' ';
                /*
                $sql_type_from = ', word_links t LEFT JOIN user_word_links ut ON ut.word_link_id = t.word_link_id
                                                                             AND ut.user_id = '.$this->usr->id.'';
                $sql_type_where_words   = 'WHERE w.word_id = t.from_phrase_id
                                             AND t.verb_id = '.cl(SQL_LINK_TYPE_IS).'
                                             AND t.to_phrase_id = '.$type->id.' ';
                $sql_type_where_triples = 'WHERE l.to_phrase_id = t.from_phrase_id
                                             AND t.verb_id = '.cl(SQL_LINK_TYPE_IS).'
                                             AND t.to_phrase_id = '.$type->id.' ';
                $sql_words   = 'SELECT w.word_id AS id,
                                      IF(u.word_name IS NULL, w.word_name, u.word_name) AS name,
                                      IF(u.excluded IS NULL, COALESCE(w.excluded, 0), COALESCE(u.excluded, 0)) AS excluded
                                  FROM words w
                            LEFT JOIN user_words u ON u.word_id = w.word_id
                                                  AND u.user_id = '.$this->usr->id.'
                                      '.$sql_type_from.'
                                      '.$sql_type_where_words.'
                              GROUP BY name';
                $sql_triples = 'SELECT l.word_link_id * -1 AS id,
                                      IF(u.name IS NULL, l.name, u.name) AS name,
                                      IF(u.excluded IS NULL, COALESCE(l.excluded, 0), COALESCE(u.excluded, 0)) AS excluded
                                  FROM word_links l
                            LEFT JOIN user_word_links u ON u.word_link_id = l.word_link_id
                                                        AND u.user_id = '.$this->usr->id.'
                                      '.$sql_type_from.'
                                      '.$sql_type_where_triples.'
                              GROUP BY name';
                              */
            }
        }
        $sql_avoid_code_check_prefix = "SELECT";
        $sql = $sql_avoid_code_check_prefix . ' DISTINCT id, phrase_name
              FROM ( ' . $sql_words . ' UNION ' . $sql_triples . ' ) AS p
             WHERE excluded = 0
          ORDER BY p.phrase_name;';
        log_debug('phrase->sql_list -> ' . $sql);
        return $sql;
    }

    /*

    display functions

    */

    // create a selector that contains the words and triples
    // if one form contains more than one selector, $pos is used for identification
    // $type is a word to preselect the list to only those phrases matching this type
    function dsp_selector($type, $form_name, $pos, $class, $back)
    {
        log_debug('phrase->dsp_selector -> type "' . $type->name . '" with id ' . $this->id . ' selected for form ' . $form_name . '' . $pos);
        $result = '';

        if ($pos > 0) {
            $field_name = "phrase" . $pos;
        } else {
            $field_name = "phrase";
        }
        $sel = new selector;
        $sel->usr = $this->usr;
        $sel->form = $form_name;
        $sel->name = $field_name;
        if ($form_name == "value_add" or $form_name == "value_edit") {
            $sel->label = "";
        } else {
            if ($pos == 1) {
                $sel->label = "From:";
            } elseif ($pos == 2) {
                $sel->label = "To:";
            } else {
                $sel->label = "Word:";
            }
        }
        $sel->bs_class = $class;
        $sel->sql = $this->sql_list($type);
        $sel->selected = $this->id;
        $sel->dummy_text = '... please select';
        $result .= $sel->display();

        log_debug('phrase->dsp_selector -> done ');
        return $result;
    }

    // simply to display a single word and allow to delete it
    // used by value->dsp_edit
    function dsp_name_del($del_call)
    {
        log_debug('phrase->dsp_name_del');
        if ($this->id > 0) {
            $result = $this->dsp_name_del($del_call);
        } else {
        }
        return $result;
    }

    // button to add a new word similar to this phrase
    function btn_add($back)
    {
        $wrd = $this->main_word();
        $result = $wrd->btn_add($back);
        return $result;
    }

    // returns the best guess category for a word  e.g. for "ABB" it will return only "Company"
    function is_mainly()
    {
        $result = null;
        $is_wrd_lst = $this->is();
        if (count($is_wrd_lst->lst) >= 1) {
            $result = $is_wrd_lst->lst[0];
        }
        log_debug('phrase->is_mainly -> (' . $this->dsp_id() . ' is a ' . $result->name . ')');
        return $result;
    }

    /*
    word replication functions
    */

    function is_time()
    {
        $wrd = $this->main_word();
        return $wrd->is_time();
    }

    // return true if the word has the type "measure" (e.g. "meter" or "CHF")
    // in case of a division, these words are excluded from the result
    // in case of add, it is checked that the added value does not have a different measure
    function is_measure()
    {
        $wrd = $this->main_word();
        return $wrd->is_measure();
    }

    // return true if the word has the type "scaling" (e.g. "million", "million" or "one"; "one" is a hidden scaling type)
    function is_scaling()
    {
        $wrd = $this->main_word();
        return $wrd->is_scaling();
    }

    // return true if the word has the type "scaling_percent" (e.g. "percent")
    function is_percent()
    {
        $wrd = $this->main_word();
        return $wrd->is_percent();
    }

    // create a selector that contains the time words
    // e.g. Q1 can be the first Quarter of a year and in this case the four quarters of a year should be the default selection
    //      if this is the triple "Q1 of 2018" a list of triples of this year should be the default selection
    //      if Q1 is a wikidata qualifier a general time selector should be shown
    function dsp_time_selector($type, $form_name, $pos, $back)
    {

        $wrd = $this->main_word();
        return $wrd->dsp_time_selector($type, $form_name, $pos, $back);
    }


}