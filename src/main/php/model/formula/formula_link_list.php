<?php

/*

  formula_link_list.php - a list of formula word links
  ---------------------
  
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

class formula_link_list
{

    public ?array $lst = null; // the list of formula word link objects
    public ?user $usr = null;  // the user who wants to see or modify the list

    // search fields
    public ?formula $frm = null; // to select all links for this formula

    // fill the formula link list based on a database records
    // $db_rows is an array of an array with the database values
    private function rows_mapper($db_rows)
    {
        if ($db_rows != null) {
            foreach ($db_rows as $db_row) {
                if ($db_row['formula_link_id'] > 0) {
                    $frm_lnk = new formula_link;
                    $frm_lnk->id = $db_row['formula_link_id'];
                    $frm_lnk->usr = $this->usr;
                    $frm_lnk->usr_cfg_id = $db_row['user_link_id'];
                    $frm_lnk->owner_id = $db_row['user_id'];
                    $frm_lnk->formula_id = $db_row['formula_id'];
                    $frm_lnk->phrase_id = $db_row['phrase_id'];
                    $frm_lnk->link_type_id = $db_row['link_type_id'];
                    $frm_lnk->excluded = $db_row['excluded'];
                    $this->lst[] = $frm_lnk;
                }
            }
        }
    }

    // load the missing formula parameters from the database
    function load()
    {

        global $db_con;

        // check the all minimal input parameters are set
        if (!isset($this->usr)) {
            log_err("The user id must be set to load a list of formula links.", "formula_link_list->load");
        } else {

            // set the where clause depending on the values given
            $sql_where = '';
            if (isset($this->frm)) {
                if ($this->frm->id > 0) {
                    $sql_where = "l.formula_id = " . $this->frm->id;
                }
            }

            if ($sql_where == '') {
                log_err("The words assigned to a formula cannot be loaded because the formula is not defined.", "formula_link_list->load");
            } else {
                $sql = "SELECT DISTINCT 
                       l.formula_link_id,
                       u.formula_link_id AS user_link_id,
                       l.user_id,
                       l.formula_id, 
                       l.phrase_id,
                    " . $db_con->get_usr_field('link_type_id', 'l', 'u', sql_db::FLD_FORMAT_VAL) . ",
                    " . $db_con->get_usr_field('excluded', 'l', 'u', sql_db::FLD_FORMAT_VAL) . "
                  FROM formula_link_types t, formula_links l
             LEFT JOIN user_formula_links u ON u.formula_link_id = l.formula_link_id 
                                                AND u.user_id = " . $this->usr->id . " 
                  WHERE " . $sql_where . ";";
                //$db_con = new mysql;
                $db_con->usr_id = $this->usr->id;
                $db_lst = $db_con->get($sql);
                $this->rows_mapper($db_lst);
                log_debug('formula_link_list->load -> ' . count($this->lst) . ' links loaded');
            }
        }
    }

    // get an array with all phrases linked of this list e.g. linked to one formula
    function phrase_ids($sbx): array
    {
        log_debug('formula_link_list->ids');
        $result = array();

        foreach ($this->lst as $frm_lnk) {
            if ($frm_lnk->phrase_id <> 0) {
                if ($sbx) {
                    if ($frm_lnk->excluded <= 0) {
                        $result[] = $frm_lnk->phrase_id;
                    }
                } else {
                    $result[] = $frm_lnk->phrase_id;
                }
            }
        }

        log_debug('formula_link_list->ids -> got ' . count($result));
        return $result;
    }

    // delete all links without log because this is used only when deleting a formula
    // and the main event of deleting the formula is already logged
    function del_without_log(): bool
    {
        log_debug('formula_link_list->del_without_log');

        global $db_con;
        $result = true;

        foreach ($this->lst as $frm_lnk) {
            if ($result) {
                if ($frm_lnk->can_change() > 0 and $frm_lnk->not_used()) {
                    //$db_con = new mysql;
                    $db_con->usr_id = $this->usr->id;
                    // delete first all user configuration that have also been excluded
                    $db_con->set_type(DB_TYPE_USER_PREFIX . DB_TYPE_FORMULA_LINK);
                    $result = $db_con->delete(array('formula_link_id', 'excluded'), array($frm_lnk->id, '1'));
                    if ($result) {
                        $db_con->set_type(DB_TYPE_FORMULA_LINK);
                        $result = $db_con->delete('formula_link_id', $frm_lnk->id);
                    }
                } else {
                    log_err("Cannot delete a formula word link (id " . $frm_lnk->id . "), which is used or created by another user.", "formula_link_list->del_without_log");
                }
            }
        }

        log_debug('formula_link_list->del_without_log -> done');
        return $result;
    }

}