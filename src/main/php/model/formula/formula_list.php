<?php

/*

  formula_list.php - a simple list of formulas
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
  
  Copyright (c) 1995-2021 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

class formula_list
{

    public $lst = array(); // the list of the loaded formula objects
    public $usr = Null;    // if 0 (not NULL) for standard formulas, otherwise for a user specific formulas

    // fields to select the formulas
    public $wrd = NULL;    // show the formulas related to this word
    public $phr_lst = NULL;    // show the formulas related to this phrase list
    public $ids = array(); // a list of formula ids to load all formulas at once

    // in memory only fields
    public $back = NULL;    // the calling stack

    // fill the formula list based on a database records
    // $db_rows is an array of an array with the database values
    private function rows_mapper($db_rows)
    {
        if ($db_rows != null) {
            foreach ($db_rows as $db_row) {
                if (is_null($db_row['excluded']) or $db_row['excluded'] == 0) {
                    if ($db_row['formula_id'] > 0) {
                        $frm = new formula;
                        $frm->usr = $this->usr;
                        $frm->id = $db_row['formula_id'];
                        $frm->name = $db_row['formula_name'];
                        $frm->ref_text = $db_row['formula_text'];
                        $frm->usr_text = $db_row['resolved_text'];
                        $frm->description = $db_row['description'];
                        $frm->type_id = $db_row['formula_type_id'];
                        $frm->type_cl = $db_row['code_id'];
                        $frm->last_update = new DateTime($db_row['last_update']);
                        if ($db_row['all_values_needed'] == 1) {
                            $frm->need_all_val = true;
                        } else {
                            $frm->need_all_val = false;
                        }
                        /*
                        if ($frm->type_id > 0) {
                          $sql_type = "SELECT code_id
                                        FROM formula_types
                                        WHERE formula_type_id = ".$frm->type_id.";";
                          $db_type = $db_con->get1($sql_type, $frm->usr_id);
                          $frm->type_cl  = $db_type['code_id'];
                        }
                        */
                        if ($frm->name <> '') {
                            $name_wrd = new word_dsp;
                            $name_wrd->name = $frm->name;
                            $name_wrd->usr = $this->usr;
                            $name_wrd->load();
                            $frm->name_wrd = $name_wrd;
                        }
                        $this->lst[] = $frm;
                    }
                }
            }
        }
    }

    // load the missing formula parameters from the database
    // todo: if this list contains already some formula, don't add them again!
    function load()
    {

        global $db_con;

        // check the all minimal input parameters
        if (!isset($this->usr)) {
            log_err("The user id must be set to load a list of formulas.", "formula_list->load");
        } else {

            // set the where clause depending on the given selection parameters
            // default is to load all formulas to check all formula results
            $sql_from = 'formulas f';
            $sql_where = 'f.formula_id > 0';
            if (count($this->ids) > 0) {
                $sql_from = 'formulas f';
                $sql_where = 'f.formula_id IN (' . implode(',', $this->ids) . ')';
            } elseif (isset($this->wrd)) {
                $sql_from = 'formula_links l, formulas f';
                $sql_where = 'l.phrase_id = ' . $this->wrd->id . ' AND l.formula_id = f.formula_id';
            } elseif (isset($this->phr_lst)) {
                if ($this->phr_lst->ids_txt() <> '') {
                    $sql_from = 'formula_links l, formulas f';
                    $sql_where = 'l.phrase_id IN (' . $this->phr_lst->ids_txt() . ') AND l.formula_id = f.formula_id';
                } else {
                    log_err("A phrase list is set (" . $this->phr_lst->dsp_id() . "), but the id list is " . $this->phr_lst->ids_txt() . ".", "formula_list->load");

                    $sql_from = 'formula_links l, formulas f';
                    $sql_where = 'l.formula_id = f.formula_id';
                }
            }

            if ($sql_where == '') {
                // activate this error message for page loading of the complete formula list
                log_err("Either the word or the ID list must be set for loading.", "formula_list->load");
            } else {
                log_debug('formula_list->load by (' . $sql_where . ')');
                // the formula name is excluded from the user sandbox to avoid confusion
                $sql = "SELECT f.formula_id,
                       f.formula_name,
                    " . $db_con->get_usr_field('formula_text', 'f', 'u') . ",
                    " . $db_con->get_usr_field('resolved_text', 'f', 'u') . ",
                    " . $db_con->get_usr_field('description', 'f', 'u') . ",
                    " . $db_con->get_usr_field('formula_type_id', 'f', 'u', sql_db::FLD_FORMAT_VAL) . ",
                    " . $db_con->get_usr_field('code_id', 't', 'c') . ",
                    " . $db_con->get_usr_field('all_values_needed', 'f', 'u', sql_db::FLD_FORMAT_VAL) . ",
                    " . $db_con->get_usr_field('last_update', 'f', 'u', sql_db::FLD_FORMAT_VAL) . ",
                    " . $db_con->get_usr_field('excluded', 'f', 'u', sql_db::FLD_FORMAT_VAL) . "
                  FROM " . $sql_from . " 
             LEFT JOIN user_formulas u ON u.formula_id = f.formula_id 
                                      AND u.user_id = " . $this->usr->id . " 
             LEFT JOIN formula_types t ON f.formula_type_id = t.formula_type_id
             LEFT JOIN formula_types c ON u.formula_type_id = c.formula_type_id
                 WHERE " . $sql_where . "
              GROUP BY f.formula_id;";
                $db_con->usr_id = $this->usr->id;
                $db_lst = $db_con->get($sql);
                $this->rows_mapper($db_lst);
            }
        }
    }

    // rename the name function to be inline with the other classes
    function dsp_id(): string
    {
        $result = $this->name();
        if ($result <> '') {
            $result = '"' . $result . '"';
        }
        return $result;
    }

    function name(): string
    {
        return implode(",", $this->names());
    }

    // this function is called from dsp_id, so no other call is allowed
    function names(): array
    {
        $result = array();
        foreach ($this->lst as $frm) {
            $result[] = $frm->name;
        }
        return $result;
    }

    // lists all formulas with results related to a word
    function display($type = 'short'): string
    {
        log_debug('formula_list->display ' . $this->dsp_id());
        $result = '';

        if (isset($this->wrd)) {
            // list all related formula results
            usort($this->lst, array("formula", "cmp"));
            foreach ($this->lst as $frm) {
                // formatting should be moved
                //$resolved_text = str_replace('"','&quot;', $frm->usr_text);
                //$resolved_text = str_replace('"','&quot;', $frm->dsp_text($this->back));
                $formula_value = $frm->dsp_result($this->wrd, $this->back);
                // if the formula value is empty use the id to be able to select the formula
                if ($formula_value == '') {
                    $result .= $frm->id;
                } else {
                    $result .= ' value ' . $formula_value;
                }
                if ($type == 'short') {
                    $result .= ' ' . $frm->name_linked($this->back);
                    $result .= ' ' . $frm->btn_del($this->back);
                    $result .= ', ';
                } else {
                    $result .= ' ' . $frm->name_linked($this->back);
                    $result .= ' (' . $frm->dsp_text($this->back) . ')';
                    $result .= ' ' . $frm->btn_del($this->back);
                    $result .= ' <br> ';
                }
            }
        }

        log_debug("formula_list->display ... done (" . $result . ")");
        return $result;
    }

}