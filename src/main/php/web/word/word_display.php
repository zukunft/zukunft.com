<?php

/*

  word_display.php - the extension of the word object to create word base html code
  ----------------
  
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
  
  Copyright (c) 1995-2022 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

class word_dsp extends word
{

    // default view settings
    const TIME_MIN_COLS = 3; // minimum number of same time type word to display in a table e.g. if at least 3 years exist use a table to display
    const TIME_MAX_COLS = 10; // maximum number of same time type word to display in a table e.g. if more the 10 years exist, by default show only the lst 10 years

    // display a word as the view header
    function dsp_header(): string
    {
        log_debug('word_dsp->dsp_header (' . $this->id . ')');
        $result = '';

        if ($this->id <= 0) {
            $result .= 'no word selected';
        } else {
            // load the word parameters if not yet done
            if ($this->name == "") {
                $this->load();
            }

            $is_part_of = $this->is_mainly();
            //$default_view_id = cl(DBL_VIEW_WORD);
            $title = '';
            //$title .= '<a href="/http/view.php?words='.$this->id.'&view='.$default_view_id.'" title="'.$this->description.'">'.$this->name.'</a>';
            $title .= $this->name;
            if ($is_part_of->name <> '' and $is_part_of->name <> 'not set') {
                $title .= ' (<a href="/http/view.php?words=' . $is_part_of->id . '">' . $is_part_of->name . '</a>)';
            }
            /*      $title .= '  '.'<a href="/http/word_edit.php?id='.$this->id.'&back='.$this->id.'" title="Rename word"><img src="'.ZUH_IMG_EDIT.'" alt="Rename word" style="height: 0.65em;"></a>'; */
            $title .= '  ' . '<a href="/http/word_edit.php?id=' . $this->id . '&back=' . $this->id . '" title="Rename word"><span class="glyphicon glyphicon-pencil"></a>';
            $title .= '</h2>';
            $result .= dsp_text_h1($title);
        }

        return $result;
    }


    // simply to display a single word link
    function dsp_link(): string
    {
        return '<a href="/http/view.php?words=' . $this->id . '" title="' . $this->description . '">' . $this->name . '</a>';
    }

    // similar to dsp_link, but using s CSS style; used by ??? to ???
    function dsp_link_style($style): string
    {
        return '<a href="/http/view.php?words=' . $this->id . '" title="' . $this->description . '" class="' . $style . '">' . $this->name . '</a>';
    }

    // simply to display a single word in a table as a header
    function dsp_tbl_head_right(): string
    {
        log_debug('word_dsp->dsp_tbl_head_right');
        $result = '    <th class="right_ref">' . "\n";
        $result .= '      ' . $this->dsp_link() . "\n";
        $result .= '    </th>' . "\n";
        return $result;
    }

    // simply to display a single word in a table cell
    function dsp_tbl_cell($intent): string
    {
        log_debug('word_dsp->dsp_tbl_cell');
        $result = '    <td>' . "\n";
        while ($intent > 0) {
            $result .= '&nbsp;';
            $intent = $intent - 1;
        }
        $result .= '      ' . $this->dsp_link() . '' . "\n";
        $result .= '    </td>' . "\n";
        return $result;
    }

    // simply to display a single word in a table
    // rename and join to dsp_tbl_cell to have a more specific name
    function dsp_tbl(int $intent): string
    {
        log_debug('word_dsp->dsp_tbl');
        $result = '    <td>' . "\n";
        while ($intent > 0) {
            $result .= '&nbsp;';
            $intent = $intent - 1;
        }
        $result .= '      ' . $this->dsp_link() . '' . "\n";
        $result .= '    </td>' . "\n";
        return $result;
    }

    function dsp_tbl_row(): string
    {
        $result = '  <tr>' . "\n";
        $result .= $this->dsp_tbl(0);
        $result .= '  </tr>' . "\n";
        return $result;
    }

    // simply to display a single word and allow to delete it
    // used by value->dsp_edit
    function dsp_name_del($del_call): string
    {
        log_debug('word_dsp->dsp_name_del');
        $result = '  <tr>' . "\n";
        $result .= $this->dsp_tbl_cell(0);
        $result .= '    <td>' . "\n";
        $result .= '      ' . btn_del("delete", $del_call) . '<br> ';
        $result .= '    </td>' . "\n";
        $result .= '  </tr>' . "\n";
        return $result;
    }

    //
    function dsp_selector($type, $form_name, $pos, $class, $back): string
    {
        $phr = $this->phrase();
        return $phr->dsp_selector($type, $form_name, $pos, $class, $back);
    }

    // create a selector that contains the time words
    // $type is the phrase type of the time words that should be preferred displayed
    //       e.g. "year" to show the years first
    //         or "next years" to show the future years
    //         or "past years" to show the last years
    // $form_name is the type of the selector e.g. drop down
    // $pos is the ???
    // $back is the page that should be displayed after the selection is done
    function dsp_time_selector($type, $form_name, $pos, $back): string
    {
        log_debug('word_dsp->dsp_selector -> for form ' . $form_name . '' . $pos);
        global $db_con;

        $result = '';

        if ($pos > 0) {
            $field_name = "phrase" . $pos;
            //$field_name = "time".$pos;
        } else {
            $field_name = "phrase";
            //$field_name = "time";
        }
        //
        if ($type->id > 0) {
            $sql_from = "word_links l, words w";
            $sql_where_and = "AND w.word_id = l.from_phrase_id 
                        AND l.verb_id = " . cl(db_cl::VERB, verb::IS_A) . "              
                        AND l.to_phrase_id = " . $type->id;
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
                                        AND u.user_id = " . $this->usr->id . " 
                      WHERE w.word_type_id = " . cl(db_cl::WORD_TYPE, word_type_list::DBL_TIME) . "
                        " . $sql_where_and . "            
                   GROUP BY name) AS s
            WHERE (excluded <> 1 OR excluded is NULL)                                    
          ORDER BY name;";
        $sel = new html_selector;
        $sel->usr = $this->usr;
        $sel->form = $form_name;
        $sel->name = $field_name;
        $sel->sql = $sql;
        $sel->selected = $this->id;
        $sel->dummy_text = '... please select';
        $result .= $sel->display();

        log_debug('word_dsp->dsp_selector -> done ');
        return $result;
    }

    // display the history of a word
    // display the history of a word
    // maybe move this to a new object user_log_display
    // because this is very similar to a value linked function
    private function dsp_hist($page, $size, $call, $back): string
    {
        log_debug("word_dsp->dsp_hist for id " . $this->id . " page " . $size . ", size " . $size . ", call " . $call . ", back " . $back . ".");
        $result = ''; // reset the html code var

        $log_dsp = new user_log_display;
        $log_dsp->id = $this->id;
        $log_dsp->usr = $this->usr;
        $log_dsp->type = 'word';
        $log_dsp->page = $page;
        $log_dsp->size = $size;
        $log_dsp->call = $call;
        $log_dsp->back = $back;
        $result .= $log_dsp->dsp_hist();

        log_debug('word_dsp->dsp_hist -> done');
        return $result;
    }

    // show the changes of the view
    function dsp_log_view(string $back = ''): string
    {
        log_debug('word_dsp->dsp_log_view (' . $this->id . ')');
        $result = '';

        // if ($this->id <= 0 OR !is_null($this->usr_id)) {
        if ($this->id <= 0) {
            $result .= 'no word selected';
        } else {
            // load the word parameters if not yet done
            if ($this->name == "") {
                $this->load();
            }

            $changes = $this->dsp_hist(1, 20, '', $back);
            if (trim($changes) <> "") {
                $result .= dsp_text_h3("Latest view changes related to this word", "change_hist");
                $result .= $changes;
            }
        }

        return $result;
    }

    // list of related words and values filtered by a link type
    function dsp_val_list($col_wrd, $back): string
    {
        log_debug('word_dsp->dsp_val_list for ' . $this->dsp_id() . ' with "' . $col_wrd->name . '" columns for user ' . $this->usr->name);

        $result = $this->dsp_header();

        //$result .= $this->name."<br>";
        //$result .= $col_wrd->name."<br>";

        $row_lst = $this->children();    // not $this->are(), because e.g. for "Company" the word "Company" itself should not be included in the list
        $col_lst = $col_wrd->children();
        log_debug('word_dsp->dsp_val_list -> columns ' . $col_lst->dsp_id());

        $row_lst->name_sort();
        $col_lst->name_sort();

        // TODO use this for fast loading
        $val_matrix = $row_lst->val_matrix($col_lst);
        $row_lst_dsp = $row_lst->dsp_obj();
        $result .= $row_lst_dsp->dsp_val_matrix($val_matrix);

        log_debug('word_dsp->dsp_val_list -> table');

        // display the words
        $row_nbr = 0;
        $result .= dsp_tbl_start();
        foreach ($row_lst->lst as $row_phr) {
            // display the column headers
            // not needed any more if wrd lst is created based on word_display elements
            // to review
            $row_phr_dsp = new word_dsp($this->usr);
            $row_phr_dsp->id = $row_phr->id;
            $row_phr_dsp->load();
            if ($row_nbr == 0) {
                $result .= '  <tr>' . "\n";
                $result .= '    <th>' . "\n";
                $result .= '    </th>' . "\n";
                foreach ($col_lst->lst as $col_lst_wrd) {
                    log_debug('word_dsp->dsp_val_list -> column ' . $col_lst_wrd->name);
                    $result .= $col_lst_wrd->dsp_tbl_head_right();
                }
                $result .= '  </tr>' . "\n";
            }

            // display the rows
            log_debug('word_dsp->dsp_val_list -> row');
            $result .= '  <tr>' . "\n";
            $result .= '      ' . $row_phr_dsp->dsp_tbl(0) . '' . "\n";
            foreach ($col_lst->lst as $col_lst_wrd) {
                $result .= '    <td>' . "\n";
                $val_wrd_ids = array();
                $val_wrd_ids[] = $row_phr->id;
                $val_wrd_ids[] = $col_lst_wrd->id;
                asort($val_wrd_ids);
                $val_wrd_lst = new word_list($this->usr);
                $val_wrd_lst->load_by_ids($val_wrd_ids);
                log_debug('word_dsp->dsp_val_list -> get group ' . dsp_array($val_wrd_ids));
                $wrd_grp = $val_wrd_lst->get_grp();
                if ($wrd_grp->id > 0) {
                    log_debug('word_dsp->dsp_val_list -> got group ' . $wrd_grp->id);
                    $in_value = $wrd_grp->result(0);
                    $fv_text = '';
                    // temp solution to be reviewed
                    if ($in_value['id'] > 0) {
                        $fv = new formula_value($this->usr);
                        $fv->load_by_id($in_value['id']);
                        if ($fv->value <> 0) {
                            $fv_text = $fv->val_formatted();
                        } else {
                            $fv_text = '';
                        }
                    }
                    if ($fv_text <> '') {
                        //$back = $row_phr->id;
                        if (!isset($back)) {
                            $back = $this->id;
                        }
                        if ($in_value['usr'] > 0) {
                            $result .= '      <p class="right_ref"><a href="/http/formula_result.php?id=' . $in_value['id'] . '&phrase=' . $row_phr->id . '&group=' . $wrd_grp->id . '&back=' . $back . '" class="user_specific">' . $fv_text . '</a></p>' . "\n";
                        } else {
                            $result .= '      <p class="right_ref"><a href="/http/formula_result.php?id=' . $in_value['id'] . '&phrase=' . $row_phr->id . '&group=' . $wrd_grp->id . '&back=' . $back . '">' . $fv_text . '</a></p>' . "\n";
                        }
                    }
                }
                $result .= '    </td>' . "\n";
            }
            $result .= '  </tr>' . "\n";
            $row_nbr++;
        }

        // display an add button to offer the user to add one row
        $result .= '<tr><td>' . $this->btn_add($back) . '</td></tr>';

        $result .= dsp_tbl_end();

        return $result;
    }

    // shows all words the link to the given word
    // returns the html code to edit a linked word
    // ??? identical to word_list ???
    function dsp_graph($direction, string $back = ''): string
    {
        log_debug('word_dsp->dsp_graph of ' . $this->dsp_id() . ' ' . $direction . ' for user ' . $this->usr->name);
        $result = '';

        // get the link types related to the word
        $vrb_lst = $this->link_types($direction);

        // loop over the link types
        if ($vrb_lst == null) {
            $result .= 'Nothing linked to ' . $this->name() . ' utils now. Click here to link it.';
        } else {
            foreach ($vrb_lst->lst as $vrb) {
                log_debug('word_dsp->dsp_graph verb ' . $vrb->name);

                // show the RDF graph for this verb
                $trp_lst = new word_link_list($this->usr);
                $trp_lst->load_by_phr($this->phrase(), $vrb, $direction);
                $phr_lst = $trp_lst->phrase_lst();
                $wrd_lst = $phr_lst->wrd_lst_all();
                $wrd_lst_dsp = $wrd_lst->dsp_obj();
                $result .= $wrd_lst_dsp->display($back);

            }
        }

        return $result;
    }

    // allow the user to unlink a word
    function dsp_unlink($link_id): string
    {
        log_debug('word_dsp->dsp_unlink(' . $link_id . ')');
        $result = '    <td>' . "\n";
        $result .= btn_del("unlink word", "/http/link_del.php?id=" . $link_id . "&back=" . $this->id);
        $result .= '    </td>' . "\n";

        return $result;
    }

    // to select a existing word to be added
    private function selector_add($id, $form, $bs_class): string
    {
        log_debug('word_dsp->selector_add ... word id ' . $id);
        $result = '';
        $sel = new html_selector;
        $sel->usr = $this->usr;
        $sel->form = $form;
        $sel->name = 'add';
        $sel->label = "Word:";
        $sel->bs_class = $bs_class;
        $sel->sql = sql_lst_usr("word", $this->usr);
        $sel->selected = $id;
        $sel->dummy_text = '... or select an existing word to link it';
        $result .= $sel->display();

        return $result;
    }

    // returns the html code to select a word link type
    // database link must be open
    private function selector_type($id, $form): string
    {
        log_debug('word_dsp->selector_type ... word id ' . $id);
        $result = '';

        if ($id <= 0) {
            $id = DEFAULT_WORD_TYPE_ID;
        }

        $sel = new html_selector;
        $sel->usr = $this->usr;
        $sel->form = $form;
        $sel->name = 'type';
        $sel->sql = sql_lst("word_type");
        $sel->selected = $id;
        $sel->dummy_text = '';
        $result .= $sel->display();

        return $result;
    }

    // returns the html code to select a word link type
    // database link must be open
    // TODO: similar to verb->dsp_selector maybe combine???
    function selector_link($id, $form, $back): string
    {
        log_debug('word_dsp->selector_link ... verb id ' . $id);
        global $db_con;

        $result = '';

        $sql_name = "";
        if ($db_con->get_type() == sql_db::POSTGRES) {
            $sql_name = "CASE WHEN (name_reverse  <> '' IS NOT TRUE AND name_reverse <> verb_name) THEN CONCAT(verb_name, ' (', name_reverse, ')') ELSE verb_name END AS name";
        } elseif ($db_con->get_type() == sql_db::MYSQL) {
            $sql_name = "IF (name_reverse <> '' AND name_reverse <> verb_name, CONCAT(verb_name, ' (', name_reverse, ')'), verb_name) AS name";
        } else {
            log_err('Unknown db type ' . $db_con->get_type());
        }
        $sql_avoid_code_check_prefix = "SELECT";
        $sql = $sql_avoid_code_check_prefix . " * FROM (
            SELECT verb_id AS id, 
                   " . $sql_name . ",
                   words
              FROM verbs 
      UNION SELECT verb_id * -1 AS id, 
                   CONCAT(name_reverse, ' (', verb_name, ')') AS name,
                   words
              FROM verbs 
             WHERE name_reverse <> '' 
               AND name_reverse <> verb_name) AS links
          ORDER BY words DESC, name;";
        $sel = new html_selector;
        $sel->usr = $this->usr;
        $sel->form = $form;
        $sel->name = 'verb';
        $sel->sql = $sql;
        $sel->selected = $id;
        $sel->dummy_text = '';
        $result .= $sel->display();

        if ($this->usr->is_admin()) {
            // admin users should always have the possibility to create a new link type
            $result .= btn_add('add new link type', '/http/verb_add.php?back=' . $back);
        }

        return $result;
    }

    // returns the html code to select a word
    // database link must be open
    function selector_word($id, $pos, $form_name): string
    {
        log_debug('word_dsp->selector_word ... word id ' . $id);
        $result = '';

        if ($pos > 0) {
            $field_id = "word" . $pos;
        } else {
            $field_id = "word";
        }
        $sel = new html_selector;
        $sel->usr = $this->usr;
        $sel->form = $form_name;
        $sel->name = $field_id;
        $sel->sql = sql_lst_usr("word", $this->usr);
        $sel->selected = $id;
        $sel->dummy_text = '';
        $result .= $sel->display();

        log_debug('word_dsp->selector_word ... done ' . $id);
        return $result;
    }

    //
    private function dsp_type_selector($script, $bs_class): string
    {
        $result = '';
        $sel = new html_selector;
        $sel->usr = $this->usr;
        $sel->form = $script;
        $sel->name = 'type';
        $sel->label = "Word type:";
        $sel->bs_class = $bs_class;
        $sel->sql = sql_lst("word_type");
        $sel->selected = $this->type_id;
        $sel->dummy_text = '';
        $result .= $sel->display();
        return $result;
    }

    // HTML code to edit all word fields
    function dsp_add($wrd_id, $wrd_to, $vrb_id, $back): string
    {
        log_debug('word_dsp->dsp_add ' . $this->dsp_id() . ' (type ' . $this->type_id . ') or link the existing word with id ' . $wrd_id . ' to ' . $wrd_to . ' by verb ' . $vrb_id . ' for user ' . $this->usr->name . ' (called by ' . $back . ')');
        $result = '';

        $form = "word_add";
        $result .= dsp_text_h2('Add a new word');
        $result .= dsp_form_start($form);
        $result .= dsp_form_hidden("back", $back);
        $result .= dsp_form_hidden("confirm", '1');
        $result .= '<div class="form-row">';
        $result .= dsp_form_text("word_name", $this->name, "Name:", "col-sm-4");
        $result .= $this->dsp_type_selector($form, "col-sm-4");
        $result .= $this->selector_add($wrd_id, $form, "form-row") . ' ';
        $result .= '</div>';
        $result .= 'which ';
        $result .= '<div class="form-row">';
        $result .= $this->selector_link($vrb_id, $form, $back);
        $result .= $this->selector_word($wrd_to, 0, $form);
        $result .= '</div>';
        $result .= dsp_form_end('', $back);

        log_debug('word_dsp->dsp_add ... done');
        return $result;
    }

    // HTML code to edit all word fields
    function dsp_edit(string $back = ''): string
    {
        log_debug('word_dsp->dsp_edit ' . $this->dsp_id());
        $result = '';

        if ($this->id > 0) {
            $form = "word_edit";
            $result .= dsp_text_h2('Change "' . $this->name . '"');
            $result .= dsp_form_start($form);
            $result .= dsp_form_hidden("id", $this->id);
            $result .= dsp_form_hidden("back", $back);
            $result .= dsp_form_hidden("confirm", '1');
            $result .= '<div class="form-row">';
            if ($this->type_id == cl(db_cl::WORD_TYPE, word_type_list::DBL_FORMULA_LINK)) {
                $result .= dsp_form_hidden("name", $this->name);
                $result .= '  to change the name of "' . $this->name . '" rename the ';
                $frm = $this->formula();
                $result .= $frm->name_linked($back);
                $result .= '.<br> ';
            } else {
                $result .= dsp_form_text("name", $this->name, "Name:", "col-sm-4");
            }
            $result .= dsp_form_text("plural", $this->plural, "Plural:", "col-sm-4");
            if ($this->type_id == cl(db_cl::WORD_TYPE, word_type_list::DBL_FORMULA_LINK)) {
                $result .= ' type: ' . $this->type_name();
            } else {
                $result .= $this->dsp_type_selector('word_edit', "col-sm-4");
            }
            $result .= '</div>';
            $result .= '<br>';
            $result .= dsp_form_text("description", $this->description, "Description:");
            $result .= dsp_form_end('', $back);
            $result .= '<br>';
            $result .= $this->dsp_graph(word_select_direction::UP, $back,);
            $result .= $this->dsp_graph(word_select_direction::DOWN, $back,);
        }

        // display the user changes
        $changes = $this->dsp_hist(1, SQL_ROW_LIMIT, '', $back);
        if (trim($changes) <> "") {
            $result .= dsp_text_h3("Latest changes related to this word", "change_hist");
            $result .= $changes;
        }
        $changes = $this->dsp_hist_links(0, SQL_ROW_LIMIT, '', $back);
        if (trim($changes) <> "") {
            $result .= dsp_text_h3("Latest link changes related to this word", "change_hist");
            $result .= $changes;
        }

        log_debug('word_dsp->dsp_edit -> done');
        return $result;
    }

    // display the history of a word
    function dsp_hist_links($page, $size, $call, $back): string
    {
        log_debug("word_dsp->dsp_hist_links (" . $this->id . ",size" . $size . ",b" . $size . ")");
        $result = ''; // reset the html code var

        $log_dsp = new user_log_display;
        $log_dsp->id = $this->id;
        $log_dsp->usr = $this->usr;
        $log_dsp->type = 'word';
        $log_dsp->page = $page;
        $log_dsp->size = $size;
        $log_dsp->call = $call;
        $log_dsp->back = $back;
        $result .= $log_dsp->dsp_hist_links();

        log_debug('word_dsp->dsp_hist_links -> done');
        return $result;
    }

    function view(): ?view {
        return $this->load_view();
    }

}
