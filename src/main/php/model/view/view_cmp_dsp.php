<?php

/*

  view_component_display.php - to display a single display component like a headline or a table
  --------------------------
  
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

use html\html_selector;
use html\word_dsp;

class view_cmp_dsp_old extends view_cmp
{

    /**
     * @returns string the html code to display this view component
     */
    function html(): string
    {
        $result = '';
        switch ($this->type_id) {
            case cl(db_cl::VIEW_COMPONENT_TYPE, view_cmp_type::TEXT):
                $result .= $this->text();
                break;
            default:
                $result .= 'ERROR: unknown type id ' . $this->type_id;
        }
        return $result;
    }

    /**
     * @returns string html code to display a simple text
     */
    function text(): string
    {
        $result = '';
        if ($this->type_id == cl(db_cl::VIEW_COMPONENT_TYPE, view_cmp_type::TEXT)) {
            $result .= " " . $this->name();
        }
        return $result;
    }

    /**
     * show the word name and give the user the possibility to change the word name
     */
    function word_name(word $wrd): string
    {
        $result = '';
        if ($this->type_id == cl(db_cl::VIEW_COMPONENT_TYPE, view_cmp_type::PHRASE_NAME)) {
            if (!isset($wrd)) {
                $result .= log_err('No word selected for "' . $this->name . '".', "view_component_dsp->word_name");
            } else {
                $wrd_dsp = new word_dsp();
                $wrd_dsp->set_id($wrd->id());
                $wrd_dsp->set_name($wrd->name_dsp());
                $parent = $wrd->is_mainly()->get_dsp_obj();
                if ($parent != null) {
                    $result .= $wrd_dsp->header($parent);
                }
            }
        }

        return $result;
    }

    // display a table with the values of the first word, that are also linked to the second word (e.g. ABB as first word, Cash Flow Statement as second word)
    // $wrd is the word that the user has selected to see e.g. "Company" to see a list of the main companies
    // $this->word_id_col is the related word defined on the view component e.g. "Company main ratio" to see a "word value list" with all word related to "Company main ratio"

    // view type table with parameters:
    // row start words (build a tree )
    // col word (if time word set the newest value to the right

    function table($phr): string
    {
        $result = '';
        if ($this->type_id == cl(db_cl::VIEW_COMPONENT_TYPE, view_cmp_type::VALUES_RELATED)) {
            log_debug('of view component ' . $this->dsp_id() . ' for "' . $phr->name() . '" with columns "' . $this->wrd_row->name . '" and user "' . $this->user()->name . '"');
            $val_lst = new value_list_dsp_old($this->user());
            $val_lst->phr = $phr;
            $result .= $val_lst->dsp_table($this->wrd_row, $phr->id);
        }
        return $result;
    }

    /**
     * show a list of words and some values related to the words e.g. all companies with the main ratios
     */
    function num_list($wrd, $back): string
    {
        $result = '';

        if ($this->type_id == cl(db_cl::VIEW_COMPONENT_TYPE, view_cmp_type::WORD_VALUE)) {
            log_debug('in view ' . $this->dsp_id() . ' for word ' . $wrd->name() . ' and user ' . $this->user()->name);

            // check the parameters
            if (get_class($wrd) <> word_dsp::class) {
                $result .= log_warning('The word parameter has type ' . get_class($wrd) . ', but should be word_dsp.', "view_component_dsp->num_list");
                $wrd_dsp = new word_dsp($wrd->id, $wrd->name);
                $wrd = $wrd_dsp;
            }

            $this->load_phrases(); // to make sure that the column word object is loaded
            if (isset($this->wrd_col)) {
                $result .= $wrd->dsp_val_list($this->wrd_col, $this->wrd_col->is_mainly(), $back);
            } else {
                $result .= log_err('Column definition is missing for ' . $this->dsp_id() . '.', "view_component_dsp->num_list");
            }
        }
        return $result;
    }

    private function formula_list($wrd): formula_list
    {
        $frm_lst = new formula_list($this->user());
        $frm_lst->load_by_phr($wrd->phrase());
        return $frm_lst;
    }

    // display all formulas related to the given word
    function formulas($wrd, string $back = ''): string
    {
        $result = '';
        if ($this->type_id == cl(db_cl::VIEW_COMPONENT_TYPE, view_cmp_type::FORMULAS)) {
            log_debug('in view ' . $this->dsp_id() . ' for word ' . $wrd->name() . ' and user ' . $this->user()->name);
            $result .= dsp_text_h2('Formulas');

            $frm_lst = $this->formula_list($wrd);
            $result .= $frm_lst->display($back);

            $parent_word_lst = $wrd->parents();
            foreach ($parent_word_lst->lst as $parent_wrd) {
                log_debug('parent (' . $parent_wrd->name . ')');
                $result .= dsp_text_h3('Formulas inherent by ' . $parent_wrd->name);

                $frm_lst = $this->formula_list($parent_wrd);
                $result .= $frm_lst->display($back);
                // adding formulas direct to a parent word may not be intuitive
                //$result .= btn_add ('Add formulas', "/http/formula_add.php?word=".$parent_id."");
            }
            $result .= \html\btn_add('Add formula', "/http/formula_add.php?word=" . $wrd->id . "&back=" . $wrd->id . "");
            $result .= '<br>';
        }
        return $result;
    }

    // show a list of formula results related to a word
    function formula_values($wrd, string $back = ''): string
    {
        $result = '';
        if ($this->type_id == cl(db_cl::VIEW_COMPONENT_TYPE, view_cmp_type::FORMULA_RESULTS)) {
            log_debug('in view ' . $this->dsp_id() . ' for word ' . $wrd->name() . ' and user ' . $this->user()->name);
            $result .= "<br><br>calculated values<br>";
            $frm_val_lst = new formula_value_list($this->user());
            $frm_val_lst->load($wrd);
            $result .= $frm_val_lst->display($back);
        }
        return $result;
    }

    // show all words that are based on the given start word
    // and related to the main word
    // later the start word should be selected automatically based on what most users has clicked on
    function word_children($wrd): string
    {
        $result = '';

        if ($this->type_id == cl(db_cl::VIEW_COMPONENT_TYPE, view_cmp_type::WORDS_DOWN)) {
            log_debug('in view ' . $this->dsp_id() . ' for word ' . $wrd->name() . ' and user ' . $this->user()->name);
            $result .= $wrd->dsp_graph(word_select_direction::DOWN);
        }

        return $result;
    }

    // show all word that this words is based on
    function word_parents($wrd): string
    {
        $result = '';
        if ($this->type_id == cl(db_cl::VIEW_COMPONENT_TYPE, view_cmp_type::WORDS_DOWN)) {
            log_debug('in view ' . $this->dsp_id() . ' for word ' . $wrd->name() . ' and user ' . $this->user()->name);
            $result .= $wrd->dsp_graph(word_select_direction::UP);
        }
        return $result;
    }

    // configure the json export
    function json_export($wrd, $back): string
    {
        $result = '';
        if ($this->type_id == cl(db_cl::VIEW_COMPONENT_TYPE, view_cmp_type::JSON_EXPORT)) {
            log_debug('in view ' . $this->dsp_id() . ' for word ' . $wrd->name() . ' and user ' . $this->user()->name);
            $result .= '<br>';
            $result .= $wrd->config_json_export($back);
            $result .= '<br>';
        }
        return $result;
    }

    // configure the xml export
    function xml_export($wrd, $back): string
    {
        $result = '';
        if ($this->type_id == cl(db_cl::VIEW_COMPONENT_TYPE, view_cmp_type::XML_EXPORT)) {
            log_debug('in view ' . $this->dsp_id() . ' for word ' . $wrd->name() . ' and user ' . $this->user()->name);
            $result .= '<br>';
            $result .= $wrd->config_xml_export($back);
            $result .= '<br>';
        }
        return $result;
    }

    // configure the csv export
    function csv_export($wrd, $back): string
    {
        $result = '';
        if ($this->type_id == cl(db_cl::VIEW_COMPONENT_TYPE, view_cmp_type::CSV_EXPORT)) {
            log_debug('in view ' . $this->dsp_id() . ' for word ' . $wrd->name() . ' and user ' . $this->user()->name);
            $result .= '<br>';
            $result .= $wrd->config_csv_export($back);
            $result .= '<br>';
        }
        return $result;
    }

    /**
     * shows all: all words that link to the given word and all values related to the given word
     * @param phrase $phr the phrase used as a base to select the related phrases
     * @param string $back
     * @return string with the HTML code to display all related phrases
     */
    function all(phrase $phr, string $back = ''): string
    {
        log_debug('for word ' . $phr->name());
        $result = '';
        if ($this->type_id == cl(db_cl::VIEW_COMPONENT_TYPE, view_cmp_type::VALUES_ALL)) {
            log_debug('in view ' . $this->dsp_id() . ' for word ' . $phr->name() . ' and user ' . $this->user()->name);
            $result .= '<br>';
            $phrases_down = $phr->dsp_graph(word_select_direction::DOWN);
            $phrases_up = $phr->dsp_graph(word_select_direction::UP,);
            if ($phrases_down <> '' or $phrases_up <> '') {
                $result .= $phrases_down . $phrases_up;
            } else {
                $result .= "The type of " . $phr->name() . " is not jet defined. Please define what it is: ";
                $type_is = cl(db_cl::VERB, verb::IS_A);
                $result .= \html\btn_add("Please link " . $phr->name() . " to an existing word to include it in the lists", '/http/link_add.php?from=' . $phr->id . '&verb=' . $type_is . '&back=' . $phr->id);
            }
            $result .= '<br><br>values<br>';
            $val_lst = new value_list($this->user());;
            $val_lst->phr = $phr;
            log_debug('load values for word "' . $phr->name() . '" and user "' . $this->user()->name . '"');
            $val_lst->load();
            $val_lst_dsp = $val_lst->api_obj()->dsp_obj();
            $result .= $val_lst_dsp->table(null, $back);
        }
        return $result;
    }

    /*
     * to display the view component itself, so that the user can change it
     */


    // allow the user to unlink a view
    function btn_unlink($view_id, $wrd, $back): string
    {
        log_debug('me' . $this->id . ',m' . $view_id . ',t' . $wrd->id . ')');
        $result = '    <td>' . "\n";
        $result .= \html\btn_del("unlink view", "/http/view_component_edit.php?id=" . $this->id . "&unlink_view=" . $view_id . "&word=" . $wrd->id . "&back=" . $back);
        $result .= '    </td>' . "\n";
        return $result;
    }

    // lists of all views where a view component is used
    private function linked_views($add_link, $wrd, $back): string
    {
        log_debug("id " . $this->id . " and user " . $this->user()->id . " (word " . $wrd->id . ", add " . $add_link . ").");

        global $db_con;
        $result = '';

        if (UI_USE_BOOTSTRAP) {
            $result .= dsp_tbl_start_hist();
        } else {
            $result .= dsp_tbl_start_half();
        }

        $sql = "SELECT m.view_id, m.view_name 
              FROM view_component_links l, views m 
             WHERE l.view_component_id = " . $this->id . " 
               AND l.view_id = m.view_id;";
        //$db_con = New mysql;
        $db_con->usr_id = $this->user()->id;
        $view_lst = $db_con->get_old($sql);
        foreach ($view_lst as $view) {
            $result .= '  <tr>' . "\n";
            $result .= '    <td>' . "\n";
            $dsp = new view_dsp_old($this->user());
            $dsp->id = $view[view::FLD_ID];
            $dsp->name = $view[view::FLD_NAME];
            $result .= '      ' . $dsp->name_linked($wrd, $back) . '' . "\n";
            $result .= '    </td>' . "\n";
            $result .= $this->btn_unlink($view[view::FLD_ID], $wrd, $back);
            $result .= '  </tr>' . "\n";
        }

        // give the user the possibility to add a view
        $result .= '  <tr>';
        $result .= '    <td>';
        if ($add_link == 1) {
            $sel = new html_selector;
            $sel->form = 'view_component_edit';
            $sel->name = 'link_view';
            $sel->sql = sql_lst_usr("view", $this->user());
            $sel->selected = 0;
            $sel->dummy_text = 'select a view where the view component should also be used';
            $result .= $sel->display();

            $result .= dsp_form_end('', $back);
        } else {
            $result .= '      ' . \html\btn_add('add new', '/http/view_component_edit.php?id=' . $this->id . '&add_link=1&word=' . $wrd->id . '&back=' . $back);
        }
        $result .= '    </td>';
        $result .= '  </tr>';

        $result .= dsp_tbl_end();
        $result .= '  <br>';

        return $result;
    }

    // display the component type selector
    private function dsp_type_selector($script, $class): string
    {
        $result = '';
        $sel = new html_selector;
        $sel->form = $script;
        $sel->dummy_text = 'not set';
        $sel->name = 'type';
        $sel->label = "Type:";
        $sel->bs_class = $class;
        $sel->sql = sql_lst("view_component_type");
        $sel->selected = $this->type_id;
        $result .= $sel->display() . ' ';
        return $result;
    }

    // display the component word_row selector
    private function dsp_word_row_selector($script, $class): string
    {
        $result = '';
        $sel = new html_selector;
        $sel->form = $script;
        $sel->dummy_text = 'not set';
        $sel->name = 'word_row';
        if (isset($this->wrd_row)) {
            $sel->label = "Rows taken from " . $this->wrd_row->dsp_obj()->dsp_link() . ":";
        } else {
            $sel->label = "Take rows from:";
        }
        $sel->bs_class = $class;
        $sel->sql = sql_lst_usr("word", $this->user());
        $sel->selected = $this->word_id_row;
        $result .= $sel->display() . ' ';
        return $result;
    }

    // display the component word_col selector
    private function dsp_word_col_selector($script, $class): string
    {
        $result = '';
        $sel = new html_selector;
        $sel->form = $script;
        $sel->dummy_text = 'not set';
        $sel->name = 'word_col';
        if (isset($this->wrd_col)) {
            $sel->label = "Columns taken from " . $this->wrd_col->dsp_obj()->dsp_link() . ":";
        } else {
            $sel->label = "Take columns from:";
        }
        $sel->bs_class = $class;
        $sel->sql = sql_lst_usr("word", $this->user());
        $sel->selected = $this->word_id_col;
        $result .= $sel->display() . ' ';
        return $result;
    }

    // display the history of a view component
    function dsp_hist($page, $size, $call, $back): string
    {
        log_debug("for id " . $this->id . " page " . $size . ", size " . $size . ", call " . $call . ", back " . $back . ".");
        $result = ''; // reset the html code var

        $log_dsp = new user_log_display($this->user());
        $log_dsp->id = $this->id;
        $log_dsp->usr = $this->user();
        $log_dsp->type = view_cmp::class;
        $log_dsp->page = $page;
        $log_dsp->size = $size;
        $log_dsp->call = $call;
        $log_dsp->back = $back;
        $result .= $log_dsp->dsp_hist();

        log_debug("done");
        return $result;
    }

    // display the link history of a view component
    function dsp_hist_links($page, $size, $call, $back): string
    {
        log_debug("for id " . $this->id . " page " . $size . ", size " . $size . ", call " . $call . ", back " . $back . ".");
        $result = ''; // reset the html code var

        $log_dsp = new user_log_display($this->user());
        $log_dsp->id = $this->id;
        $log_dsp->type = view_cmp::class;
        $log_dsp->page = $page;
        $log_dsp->size = $size;
        $log_dsp->call = $call;
        $log_dsp->back = $back;
        $result .= $log_dsp->dsp_hist_links();

        log_debug("done");
        return $result;
    }

    // TODO HTML code to add a view component
    function dsp_add($add_link, $wrd, $back): string
    {
        return $this->dsp_edit($add_link, $wrd, $back);
    }

    // HTML code to edit all word fields
    function dsp_edit($add_link, $wrd, $back): string
    {
        log_debug($this->dsp_id() . ' for user ' . $this->user()->name . ' (called from ' . $back . ')');
        $result = '';

        // show the view component name
        if ($this->id <= 0) {
            $script = "view_component_add";
            $result .= dsp_text_h2('Create a view element for <a href="/http/view.php?words=' . $wrd->id . '">' . $wrd->name() . '</a>');
        } else {
            $script = "view_component_edit";
            $result .= dsp_text_h2('Edit the view element "' . $this->name . '" (used for <a href="/http/view.php?words=' . $wrd->id . '">' . $wrd->name() . '</a>) ');
        }
        $result .= '<div class="row">';

        // when changing a view component show the fields only on the left side
        if ($this->id > 0) {
            $result .= '<div class="col-sm-7">';
        }

        $result .= dsp_form_start($script);
        if ($this->id > 0) {
            $result .= dsp_form_id($this->id);
        }
        $result .= dsp_form_hidden("word", $wrd->id);
        $result .= dsp_form_hidden("back", $back);
        $result .= dsp_form_hidden("confirm", 1);
        $result .= '<div class="form-row">';
        $result .= dsp_form_fld("name", $this->name, "Component name:", "col-sm-8");
        $result .= $this->dsp_type_selector($script, "col-sm-4"); // allow to change the type
        $result .= '</div>';
        $result .= '<div class="form-row">';
        $result .= $this->dsp_word_row_selector($script, "col-sm-6"); // allow to change the word_row word
        $result .= $this->dsp_word_col_selector($script, "col-sm-6"); // allow to change the word col word
        $result .= '</div>';
        $result .= dsp_form_fld("comment", $this->description, "Comment:");
        if ($add_link <= 0) {
            if ($this->id > 0) {
                $result .= dsp_form_end('', $back, "/http/view_component_del.php?id=" . $this->id . "&back=" . $back);
            } else {
                $result .= dsp_form_end('', $back, '');
            }
        }

        if ($this->id > 0) {
            $result .= '</div>';

            $view_html = $this->linked_views($add_link, $wrd, $back);
            $changes = $this->dsp_hist(0, SQL_ROW_LIMIT, '', $back);
            if (trim($changes) <> "") {
                $hist_html = $changes;
            } else {
                $hist_html = 'Nothing changed yet.';
            }
            $changes = $this->dsp_hist_links(0, SQL_ROW_LIMIT, '', $back);
            if (trim($changes) <> "") {
                $link_html = $changes;
            } else {
                $link_html = 'No component have been added or removed yet.';
            }
            $result .= dsp_link_hist_box('Views', $view_html,
                '', '',
                'Changes', $hist_html,
                'Link changes', $link_html);
        }

        $result .= '</div>';   // of row
        $result .= '<br><br>'; // this a usually a small for, so the footer can be moved away

        return $result;
    }

}

