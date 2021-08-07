<?php

/*

  formula.php - the main formula object
  -----------------
  
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

class formula extends user_sandbox_description
{

    // list of the formula types that have a coded functionality
    const CALC = "default";    // a normal calculation formula
    const NEXT = "time_next";  //time jump forward: replaces a time term with the next time term based on the verb follower. E.g. "2017" "next" would lead to use "2018"
    const THIS = "time_this";  // selects the assumed time term
    const PREV = "time_prior"; // time jump backward: replaces a time term with the previous time term based on the verb follower. E.g. "2017" "next" would lead to use "2016"
    const REV = "reversible";  // used to define a const value that is not supposed to be changed like pi

    // database fields additional to the user sandbox fields
    public ?string $ref_text = '';         // the formula expression with the names replaced by database references
    public ?string $usr_text = '';         // the formula expression in the user format
    public ?string $description = '';      // describes to the user what this formula is doing
    public ?bool $need_all_val = false;    // calculate and save the result only if all used values are not null
    public ?DateTime $last_update = null;  // the time of the last update of fields that may influence the calculated results

    // in memory only fields
    public ?string $type_cl = '';          // the code id of the formula type
    public ?word $name_wrd = null;         // the word object for the formula name:
    //                                        because values can only be assigned to words, also for the formula name a word must exist
    public bool $needs_fv_upd = false;     // true if the formula results needs to be updated
    public ?string $ref_text_r = '';       // the part of the formula expression that is right of the equation sign (used as a work-in-progress field for calculation)

    function __construct()
    {
        parent::__construct();
        $this->obj_name = DB_TYPE_FORMULA;

        $this->rename_can_switch = UI_CAN_CHANGE_FORMULA_NAME;
    }

    function reset()
    {
        $this->id = null;
        $this->usr_cfg_id = null;
        $this->usr = null;
        $this->owner_id = null;
        $this->excluded = null;

        $this->name = '';

        $this->ref_text = '';
        $this->usr_text = '';
        $this->description = '';
        $this->type_id = null;
        $this->need_all_val = false;
        $this->last_update = null;

        $this->type_cl = '';
        $this->type_name = '';
        $this->name_wrd = null;

        $this->needs_fv_upd = false;
        $this->ref_text_r = '';
    }

    /**
     * load the corresponding name word for the formula name
     */
    function load_wrd(): bool
    {
        $result = true;

        $do_load = true;
        if (isset($this->name_wrd)) {
            if ($this->name_wrd->name == $this->name) {
                $do_load = false;
            }
        }
        if ($do_load) {
            log_debug('formula->load_wrd load ' . $this->dsp_id());
            $name_wrd = new word_dsp;
            $name_wrd->name = $this->name;
            $name_wrd->usr = $this->usr;
            $name_wrd->load();
            if ($name_wrd->id > 0) {
                $this->name_wrd = $name_wrd;
            } else {
                $result = false;
            }
        }
        return $result;
    }


    /**
     * create the corresponding name word for the formula name
     */
    function create_wrd(): bool
    {
        log_debug('formula->create_wrd create formula linked word ' . $this->dsp_id());
        $result = false;

        // if the formula word is missing, try a word creating as a kind of auto recovery
        $name_wrd = new word_dsp;
        $name_wrd->name = $this->name;
        $name_wrd->type_id = cl(db_cl::WORD_TYPE, word_type_list::DBL_FORMULA_LINK);
        $name_wrd->usr = $this->usr;
        $name_wrd->save();
        if ($name_wrd->id > 0) {
            //zu_info('Word with the formula name "'.$this->name.'" has been missing for id '.$this->id.'.','formula->calc');
            $this->name_wrd = $name_wrd;
            $result = true;
        } else {
            log_err('Word with the formula name "' . $this->name . '" missing for id ' . $this->id . '.', 'formula->create_wrd');
        }
        return $result;
    }

    private function row_mapper($db_row, $map_usr_fields = false)
    {
        if ($db_row != null) {
            if ($db_row['formula_id'] > 0) {
                $this->id = $db_row['formula_id'];
                $this->name = $db_row['formula_name'];
                $this->owner_id = $db_row['user_id'];
                $this->ref_text = $db_row['formula_text'];
                $this->usr_text = $db_row['resolved_text'];
                $this->description = $db_row[sql_db::FLD_DESCRIPTION];
                $this->type_id = $db_row['formula_type_id'];
                $this->type_cl = $db_row[sql_db::FLD_CODE_ID];
                $this->last_update = new DateTime($db_row['last_update']);
                $this->excluded = $db_row['excluded'];
                // TODO create a boolean converter for shorter code here
                if ($db_row['all_values_needed'] == 1) {
                    $this->need_all_val = true;
                } else {
                    $this->need_all_val = false;
                }
                if ($map_usr_fields) {
                    $this->usr_cfg_id = $db_row['user_formula_id'];
                    $this->owner_id = $db_row['user_id'];
                }
            } else {
                $this->id = 0;
            }
        } else {
            $this->id = 0;
        }
    }

    /**
     * load the formula parameters for all users
     */
    function load_standard(): bool
    {
        global $db_con;
        $result = false;

        $db_con->set_type(DB_TYPE_FORMULA);
        $db_con->set_fields(array(sql_db::FLD_USER_ID, 'formula_text', 'resolved_text', sql_db::FLD_DESCRIPTION, 'formula_type_id', 'all_values_needed', 'last_update', 'excluded')); // the user_id should be included to all user sandbox tables to detect the owner of the standard value
        $db_con->set_join_fields(array(sql_db::FLD_CODE_ID), 'formula_type');
        $db_con->set_where($this->id, $this->name);
        $sql = $db_con->select();

        if ($db_con->get_where() <> '') {
            $db_rec = $db_con->get1($sql);
            $this->row_mapper($db_rec);
            $result = $this->load_owner();
        }
        log_debug('formula->load_standard -> done');
        return $result;
    }

    /**
     * create an SQL statement to retrieve the parameters of a formula from the database
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param bool $get_name to create the SQL statement name for the predefined SQL within the same function to avoid duplicating if in case of more than on where type
     * @return string the SQL statement base on the parameters set in $this
     */
    function load_sql(sql_db $db_con, bool $get_name = false): string
    {

        $sql_name = 'formula_by_';
        if ($this->id != 0) {
            $sql_name .= 'id';
        } elseif ($this->name != '') {
            $sql_name .= 'name';
        } else {
            log_err("Either the database ID (" . $this->id . ") or the formula name (" . $this->name . ") and the user (" . $this->usr->id . ") must be set to load a word.", "word->load");
        }
        // the formula name should be excluded from the user sandbox to avoid confusion
        $db_con->set_type(DB_TYPE_FORMULA);
        $db_con->set_usr($this->usr->id);
        $db_con->set_join_usr_fields(array(sql_db::FLD_CODE_ID), 'formula_type');
        $db_con->set_usr_fields(array('formula_text', 'resolved_text', sql_db::FLD_DESCRIPTION));
        $db_con->set_usr_num_fields(array('formula_type_id', 'all_values_needed', 'last_update', 'excluded'));
        $db_con->set_where($this->id, $this->name);
        $sql = $db_con->select();

        if ($get_name) {
            $result = $sql_name;
        } else {
            $result = $sql;
        }
        return $result;
    }

    /**
     * load the missing formula parameters from the database
     */
    function load(): bool
    {
        global $db_con;
        $result = false;

        // check the all minimal input parameters
        if (!isset($this->usr)) {
            log_err("The user id must be set to load a formula.", "formula->load");
        } elseif ($this->id <= 0 and $this->name == '') {
            log_err("Either the database ID (" . $this->id . ") or the formula name (" . $this->name . ") and the user (" . $this->usr->id . ") must be set to load a formula.", "formula->load");
        } else {

            $sql = $this->load_sql($db_con);

            if ($db_con->get_where() <> '') {
                $db_frm = $db_con->get1($sql);
                $this->row_mapper($db_frm, true);
                if ($this->id > 0) {
                    // TODO check the exclusion handling
                    log_debug('formula->load ' . $this->dsp_id() . ' not excluded');

                    // load the formula name word object
                    if (is_null($this->name_wrd)) {
                        $result = $this->load_wrd();
                    } else {
                        $result = true;
                    }
                }
            }
        }
        log_debug('formula->load -> done ' . $this->dsp_id());
        return $result;
    }

    /**
     * get the formula type name from the database
     */
    function formula_type_name()
    {
        $result = '';

        if ($this->type_id > 0) {
            $result = cl_name(db_cl::FORMULA_TYPE, $this->type_id);
        }
        return $result;
    }

    /**
     * return the true if the formula has a special type and the result is a kind of hardcoded
     * e.g. "this" or "next" where the value of this or the following time word is returned
     */
    function is_special(): bool
    {
        $result = false;
        if ($this->type_cl <> "") {
            $result = true;
            log_debug('formula->is_special -> ' . $this->dsp_id());
        }
        return $result;
    }

    /**
     * return the result of a special formula
     * e.g. "this" or "next" where the value of this or the following time word is returned
     */
    function special_result($phr_lst, $time_phr)
    {
        log_debug("formula->special_result (" . $this->id . ",t" . $phr_lst->dsp_id() . ",time" . $time_phr->name . " and user " . $this->usr->name . ")");
        $val = null;

        if ($this->type_id > 0) {
            log_debug("formula->special_result -> type (" . $this->type_cl . ")");
            if ($this->type_cl == formula::THIS) {
                $val_phr_lst = clone $phr_lst;
                $val_phr_lst->add($time_phr); // the time word should be added at the end, because ...
                log_debug("formula->special_result -> this (" . $time_phr->name . ")");
                $val = $val_phr_lst->value_scaled();
            }
            if ($this->type_cl == formula::NEXT) {
                $val_phr_lst = clone $phr_lst;
                $next_wrd = $time_phr->next();
                if ($next_wrd->id > 0) {
                    $val_phr_lst->add($next_wrd); // the time word should be added at the end, because ...
                    log_debug("formula->special_result -> next (" . $next_wrd->name . ")");
                    $val = $val_phr_lst->value_scaled();
                }
            }
            if ($this->type_cl == formula::PREV) {
                $val_phr_lst = clone $phr_lst;
                $prior_wrd = $time_phr->prior();
                if ($prior_wrd->id > 0) {
                    $val_phr_lst->add($prior_wrd); // the time word should be added at the end, because ...
                    log_debug("formula->special_result -> prior (" . $prior_wrd->name . ")");
                    $val = $val_phr_lst->value_scaled();
                }
            }
        }

        log_debug("formula->special_result -> (" . $val->number . ")");
        return $val;
    }

    /**
     * return the time word id used for the special formula results
     * e.g. "this" or "next" where the value of this or the following time word is returned
     */
    function special_time_phr($time_phr)
    {
        log_debug('formula->special_time_phr "' . $this->type_cl . '" for ' . $time_phr->dsp_id() . '');
        $result = $time_phr;

        if ($this->type_id > 0) {
            if ($time_phr->id <= 0) {
                log_err('No time defined for ' . $time_phr->dsp_id() . '.', 'formula->special_time_phr');
            } else {
                if ($this->type_cl == formula::THIS) {
                    $result = $time_phr;
                }
                if ($this->type_cl == formula::NEXT) {
                    $this_wrd = $time_phr->main_word();
                    $next_wrd = $this_wrd->next();
                    $result = $next_wrd->phrase();
                }
                if ($this->type_cl == formula::PREV) {
                    $this_wrd = $time_phr->main_word();
                    $prior_wrd = $this_wrd->prior();
                    $result = $prior_wrd->phrase();
                }
            }
        }

        log_debug('formula->special_time_phr got ' . $result->dsp_id());
        return $result;
    }

    /**
     * get all phrases included by a special formula element for a list of phrases
     * e.g. if the list of phrases is "2016" and "2017" and the special formulas are "prior" and "next" the result should be "2015", "2016","2017" and "2018"
     */
    function special_phr_lst($phr_lst)
    {
        log_debug('formula->special_phr_lst for ' . $phr_lst->dsp_id());
        $result = clone $phr_lst;

        foreach ($phr_lst->lst as $phr) {
            // temp solution utils the real reason is found why the phrase list elements are missing the user settings
            if (!isset($phr->usr)) {
                $phr->usr = $this->usr;
            }
            // get all special phrases
            $time_phr = $this->special_time_phr($phr);
            if (isset($time_phr)) {
                $result->add($time_phr);
                log_debug('formula->special_phr_lst -> added time ' . $time_phr->dsp_id() . ' to ' . $result->dsp_id());
            }
        }

        log_debug('formula->special_phr_lst -> ' . $result->dsp_id());
        return $result;
    }

    /**
     * lists of all words directly assigned to a formula and where the formula should be used
     */
    function assign_phr_glst_direct($sbx): phrase_list
    {
        $phr_lst = null;

        if ($this->id > 0 and isset($this->usr)) {
            log_debug('formula->assign_phr_glst_direct for formula ' . $this->dsp_id() . ' and user "' . $this->usr->name . '"');
            $frm_lnk_lst = new formula_link_list;
            $frm_lnk_lst->usr = $this->usr;
            $frm_lnk_lst->frm = $this;
            $frm_lnk_lst->load();
            $phr_ids = $frm_lnk_lst->phrase_ids($sbx);

            if (count($phr_ids) > 0) {
                $phr_lst = new phrase_list;
                $phr_lst->ids = $phr_ids;
                $phr_lst->usr = $this->usr;
                $phr_lst->load();
            }
            log_debug("formula->assign_phr_glst_direct -> number of words " . count($phr_lst->lst));
        } else {
            log_err("The user id must be set to list the formula links.", "formula->assign_phr_glst_direct");
        }

        return $phr_lst;
    }

    /**
     * the complete list of a phrases assigned to a formula
     */
    function assign_phr_lst_direct(): phrase_list
    {
        return $this->assign_phr_glst_direct(false);
    }

    /**
     * the user specific list of a phrases assigned to a formula
     */
    function assign_phr_ulst_direct(): phrase_list
    {
        return $this->assign_phr_glst_direct(true);
    }

    /**
     * returns a list of all words that the formula is assigned to
     * e.g. if the formula is assigned to "Company" and "ABB is a Company" include ABB in the word list
     */
    function assign_phr_glst($sbx): phrase_list
    {
        $phr_lst = new phrase_list;
        $phr_lst->usr = $this->usr;

        if ($this->id > 0 and isset($this->usr)) {
            $direct_phr_lst = $this->assign_phr_glst_direct($sbx);
            if (count($direct_phr_lst->lst) > 0) {
                log_debug('formula->assign_phr_glst -> ' . $this->dsp_id() . ' direct assigned words and triples ' . $direct_phr_lst->dsp_id());

                //$indirect_phr_lst = $direct_phr_lst->is();
                $indirect_phr_lst = $direct_phr_lst->are();
                log_debug('formula->assign_phr_glst -> indirect assigned words and triples ' . $indirect_phr_lst->dsp_id());

                // merge direct and indirect assigns (maybe later using phrase_list->merge)
                $phr_ids = array_merge($direct_phr_lst->ids, $indirect_phr_lst->ids);
                $phr_ids = array_unique($phr_ids);

                $phr_lst->ids = $phr_ids;
                $phr_lst->load();
                log_debug('formula->assign_phr_glst -> number of words and triples ' . count($phr_lst->lst));
            } else {
                log_debug('formula->assign_phr_glst -> no words are assigned to ' . $this->dsp_id());
            }
        } else {
            log_err('The user id must be set to list the formula links.', 'formula->assign_phr_glst');
        }

        return $phr_lst;
    }


    /**
     * the complete list of a phrases assigned to a formula
     */
    function assign_phr_lst(): phrase_list
    {
        return $this->assign_phr_glst(false);
    }

    /**
     * the user specific list of a phrases assigned to a formula
     */
    function assign_phr_ulst(): phrase_list
    {
        return $this->assign_phr_glst(true);
    }


    public static function cmp($a, $b)
    {
        return strcmp($a->name, $b->name);
    }


    /**
     * delete all formula values (results) for this formula
     */
    function fv_del(): bool
    {
        log_debug("formula->fv_del (" . $this->id . ")");

        global $db_con;

        $db_con->set_type(DB_TYPE_FORMULA_VALUE);
        $db_con->set_usr($this->usr->id);
        return $db_con->delete('formula_id', $this->id);
    }


    /**
     * fill the formula in the reference format with numbers
     * TODO verbs
     */
    function to_num($phr_lst, $back): formula_value_list
    {
        log_debug('get numbers for ' . $this->name_linked($back) . ' and ' . $phr_lst->name_linked());

        // check
        if ($this->ref_text_r == '' and $this->ref_text <> '') {
            $exp = new expression;
            $exp->ref_text = $this->ref_text;
            $exp->usr = $this->usr;
            $this->ref_text_r = ZUP_CHAR_CALC . $exp->r_part();
        }

        // guess the time if needed and exclude the time for consistent word groups
        $wrd_lst = $phr_lst->wrd_lst_all();
        $time_wrd = $wrd_lst->assume_time();
        if (isset($time_wrd)) {
            $time_phr = $time_wrd->phrase();
        }
        $phr_lst_ex = clone $phr_lst;
        $phr_lst_ex->ex_time();
        log_debug('formula->to_num -> the phrases excluded time are ' . $phr_lst_ex->dsp_id());

        // create the formula value list
        $fv_lst = new formula_value_list;
        $fv_lst->usr = $this->usr;

        // create a master formula value object to only need to fill it with the numbers in the code below
        $fv_init = new formula_value; // maybe move the constructor of formula_value_list?
        $fv_init->usr = $this->usr;
        $fv_init->frm = $this;
        $fv_init->frm_id = $this->id;
        $fv_init->ref_text = $this->ref_text_r;
        $fv_init->num_text = $this->ref_text_r;
        $fv_init->src_phr_lst = clone $phr_lst_ex;
        $fv_init->phr_lst = clone $phr_lst_ex;
        if (isset($time_phr)) {
            $fv_init->src_time_phr = clone $time_phr;
        }
        if (isset($time_phr)) {
            $fv_init->time_phr = clone $time_phr;
        }
        if ($fv_init->last_val_update < $this->last_update) {
            $fv_init->last_val_update = $this->last_update;
        }

        // load the formula element groups; similar parts is used in the explain method in formula_value
        // e.g. for "Sales differentiator Sector / Total Sales" the element groups are
        //      "Sales differentiator Sector" and "Total Sales" where
        //      the element group "Sales differentiator Sector" has the elements: "Sales" (of type word), "differentiator" (verb), "Sector" (word)
        $exp = $this->expression();
        $elm_grp_lst = $exp->element_grp_lst("");
        log_debug('formula->to_num -> in ' . $exp->ref_text . ' ' . count($elm_grp_lst->lst) . ' element groups found');

        // to check if all needed value are given
        $all_elm_grp_filled = true;

        // loop over the element groups and replace the symbol with a number
        foreach ($elm_grp_lst->lst as $elm_grp) {

            // get the figures based on the context e.g. the formula element "Share Price" for the context "ABB" can be 23.11
            // a figure is either the user edited value or a calculated formula result)
            $elm_grp->phr_lst = clone $phr_lst_ex;
            if (isset($time_phr)) {
                $elm_grp->time_phr = clone $time_phr;
            }
            $elm_grp->build_symbol();
            $fig_lst = $elm_grp->figures();
            log_debug('formula->to_num -> figures ');
            log_debug('formula->to_num -> figures ' . $fig_lst->dsp_id() . ' (' . count($fig_lst->lst) . ') for ' . $elm_grp->dsp_id());

            // fill the figure into the formula text and create as much formula values / results as needed
            if (count($fig_lst->lst) == 1) {
                // if no figure if found use the master result as placeholder
                if (count($fv_lst->lst) == 0) {
                    $fv_lst->lst[] = $fv_init;
                }
                // fill each formula values created by any previous number filling
                foreach ($fv_lst->lst as $fv) {
                    // fill each formula values created by any previous number filling
                    if ($fv->val_missing == False) {
                        if ($fig_lst->fig_missing and $this->need_all_val) {
                            log_debug('formula->to_num -> figure missing');
                            $fv->val_missing == True;
                        } else {
                            $fig = $fig_lst->lst[0];
                            $fv->num_text = str_replace($fig->symbol, $fig->number, $fv->num_text);
                            if ($fv->last_val_update < $fig->last_update) {
                                $fv->last_val_update = $fig->last_update;
                            }
                            log_debug('formula->to_num -> one figure "' . $fig->number . '" for "' . $fig->symbol . '" in "' . $fv->num_text . '"');
                        }
                    }
                }
            } elseif (count($fig_lst->lst) > 1) {
                // create the formula result object only if at least one figure if found
                if (count($fv_lst->lst) == 0) {
                    $fv_lst->lst[] = $fv_init;
                }
                // if there is more than one number to fill replicate each previous result, so in fact it multiplies the number of results
                foreach ($fv_lst->lst as $fv) {
                    $fv_master = clone $fv;
                    $fig_nbr = 1;
                    foreach ($fig_lst->lst as $fig) {
                        if ($fv->val_missing == False) {
                            if ($fig_lst->fig_missing and $this->need_all_val) {
                                log_debug('formula->to_num -> figure missing');
                                $fv->val_missing == True;
                            } else {
                                // for the first previous result, just fill in the first number
                                if ($fig_nbr == 1) {

                                    // if the result has been the standard result utils now
                                    if ($fv->is_std) {
                                        // ... and the value is user specific
                                        if (!$fig->is_std) {
                                            // split the result into a standard
                                            // get the standard value
                                            // $fig_std = ...;
                                            $fv_std = clone $fv;
                                            $fv_std->usr = null;
                                            $fv_std->num_text = str_replace($fig->symbol, $fig->number, $fv_std->num_text);
                                            if ($fv_std->last_val_update < $fig->last_update) {
                                                $fv_std->last_val_update = $fig->last_update;
                                            }
                                            log_debug('formula->to_num -> one figure "' . $fig->number . '" for "' . $fig->symbol . '" in "' . $fv->num_text . '"');
                                            $fv_lst->lst[] = $fv_std;
                                            // ... and split into a user specific part
                                            $fv->is_std = false;
                                        }
                                    }

                                    $fv->num_text = str_replace($fig->symbol, $fig->number, $fv->num_text);
                                    if ($fv->last_val_update < $fig->last_update) {
                                        $fv->last_val_update = $fig->last_update;
                                    }
                                    log_debug('formula->to_num -> one figure "' . $fig->number . '" for "' . $fig->symbol . '" in "' . $fv->num_text . '"');
                                } else {
                                    // if the result has been the standard result utils now
                                    if ($fv_master->is_std) {
                                        // ... and the value is user specific
                                        if (!$fig->is_std) {
                                            // split the result into a standard
                                            // get the standard value
                                            // $fig_std = ...;
                                            $fv_std = clone $fv_master;
                                            $fv_std->usr = null;
                                            $fv_std->num_text = str_replace($fig->symbol, $fig->number, $fv_std->num_text);
                                            if ($fv_std->last_val_update < $fig->last_update) {
                                                $fv_std->last_val_update = $fig->last_update;
                                            }
                                            log_debug('formula->to_num -> one figure "' . $fig->number . '" for "' . $fig->symbol . '" in "' . $fv->num_text . '"');
                                            $fv_lst->lst[] = $fv_std;
                                            // ... and split into a user specific part
                                            $fv_master->is_std = false;
                                        }
                                    }

                                    // for all following result reuse the first result and fill with the next number
                                    $fv_new = clone $fv_master;
                                    $fv_new->num_text = str_replace($fig->symbol, $fig->number, $fv_new->num_text);
                                    if ($fv->last_val_update < $fig->last_update) {
                                        $fv->last_val_update = $fig->last_update;
                                    }
                                    log_debug('formula->to_num -> one figure "' . $fig->number . '" for "' . $fig->symbol . '" in "' . $fv->num_text . '"');
                                    $fv_lst->lst[] = $fv_new;
                                }
                                log_debug('formula->to_num -> figure "' . $fig->number . '" for "' . $fig->symbol . '" in "' . $fv->num_text . '"');
                                $fig_nbr++;
                            }
                        }
                    }
                }
            } else {
                // if not figure found remember to switch off the result if needed
                log_debug('formula->to_num -> no figures found for ' . $elm_grp->dsp_id() . ' and ' . $phr_lst_ex->dsp_id());
                $all_elm_grp_filled = false;
            }
        }

        // if some values are not filled and all are needed, switch off the incomplete formula results
        if ($this->need_all_val) {
            log_debug('formula->to_num -> for ' . $phr_lst_ex->dsp_id() . ' all value are needed');
            if ($all_elm_grp_filled) {
                log_debug('formula->to_num -> for ' . $phr_lst_ex->dsp_id() . ' all value are filled');
            } else {
                log_debug('formula->to_num -> some needed values missing for ' . $phr_lst_ex->dsp_id());
                foreach ($fv_lst->lst as $fv) {
                    log_debug('formula->to_num -> some needed values missing for ' . $fv->dsp_id() . ' so switch off');
                    $fv->val_missing = True;
                }
            }
        }

        // calculate the final numeric results
        foreach ($fv_lst->lst as $fv) {
            // at least the formula update should be used
            if ($fv->last_val_update < $this->last_update) {
                $fv->last_val_update = $this->last_update;
            }
            // calculate only if any parameter has been updated since last calculation
            if ($fv->num_text == '') {
                log_err('num text is empty nothing needs to be done, but actually this should never happen');
            } else {
                if ($fv->last_val_update > $fv->last_update) {
                    // check if all needed value exist
                    $can_calc = false;
                    if ($this->need_all_val) {
                        log_debug('calculate ' . $this->name_linked($back) . ' only if all numbers are given');
                        if ($fv->val_missing) {
                            log_debug('got some numbers for ' . $this->name_linked($back) . ' and ' . dsp_array($fv->wrd_ids));
                        } else {
                            if ($fv->is_std) {
                                log_debug('got all numbers for ' . $this->name_linked($back) . ' and ' . $fv->name_linked($back) . ': ' . $fv->num_text);
                            } else {
                                log_debug('got all numbers for ' . $this->name_linked($back) . ' and ' . $fv->name_linked($back) . ': ' . $fv->num_text . ' (user specific)');
                            }
                            $can_calc = true;
                        }
                    } else {
                        log_debug('always calculate ' . $this->dsp_id());
                        $can_calc = true;
                    }
                    if ($can_calc == true and isset($time_wrd)) {
                        log_debug('calculate ' . $fv->num_text . ' for ' . $phr_lst_ex->dsp_id());
                        $fv->value = zuc_math_parse($fv->num_text, $phr_lst_ex->ids, $time_wrd->id);
                        $fv->is_updated = true;
                        log_debug('the calculated ' . $this->name_linked($back) . ' is ' . $fv->value . ' for ' . $fv->phr_lst->name_linked());
                    }
                }
            }
        }

        return $fv_lst;
    }

    // create the calculation request for one formula and one usr
    /*
  function calc_requests($phr_lst) {
    $result = array();

    $calc_request = New batch_job;
    $calc_request->frm     = $this;
    $calc_request->usr     = $this->usr;
    $calc_request->phr_lst = $phr_lst;
    $result[] = $calc_request;
    zu_debug('request "'.$frm->name.'" for "'.$phr_lst->name().'"');

    return $result;
  }
  */


    /**
     * calculate the result for one formula for one user
     * and save the result in the database
     * the $phr_lst is the context for the value retrieval and it also contains any time words
     * the time words are only separated right before saving to the database
     * always returns an array of formula values
     * TODO check if calculation is really needed
     *      if one of the result words is a scaling word, remove all value scaling words
     *      always create a default result (for the user 0)
     */
    function calc($phr_lst, $back): array
    {
        $result = null;

        // check the parameters
        if (!isset($phr_lst)) {
            log_warning('The calculation context for ' . $this->dsp_id() . ' is empty.', 'formula->calc');
        } else {
            log_debug('formula->calc ' . $this->dsp_id() . ' for ' . $phr_lst->dsp_id());

            // check if an update of the result is needed
            /*
      $needs_update = true;
      if ($this->has_verb ($this->ref_text, $this->usr->id)) {
        $needs_update = true; // this case will be checked later
      } else {
        $frm_wrd_ids = $this->wrd_ids($this->ref_text, $this->usr->id);
      } */

            // reload the formula if needed, but this should be done by the calling function, so create an info message
            if ($this->name == '' or is_null($this->name_wrd)) {
                $this->load();
                log_info('formula ' . $this->dsp_id() . ' reloaded.', 'formula->calc');
            }

            // build the formula expression for calculating the result
            $exp = new expression;
            $exp->ref_text = $this->ref_text;
            $exp->usr = $this->usr;

            // the phrase left of the equation sign should be added to the result
            $fv_add_phr_lst = $exp->fv_phr_lst();
            if (isset($fv_add_phr_lst)) {
                log_debug('formula->calc -> use words ' . $fv_add_phr_lst->dsp_id() . ' for the result');
            }
            // use only the part right of the equation sign for the result calculation
            $this->ref_text_r = ZUP_CHAR_CALC . $exp->r_part();
            log_debug('formula->calc got result words of ' . $this->ref_text_r);

            // get the list of the numeric results
            // $fv_lst is a list of all results saved in the database
            $fv_lst = $this->to_num($phr_lst, $back);
            if (isset($fv_add_phr_lst)) {
                log_debug('formula->calc -> ' . count($fv_lst->lst) . ' formula results to save');
            }

            // save the numeric results
            foreach ($fv_lst->lst as $fv) {
                if ($fv->val_missing) {
                    // check if fv needs to be remove from the database
                    log_debug('some values missing for ' . $fv->dsp_id());
                } else {
                    if ($fv->is_updated) {
                        log_debug('formula result ' . $fv->dsp_id() . ' is updated');
                        // add the formula result word
                        // e.g. in the increase formula "percent" should be on the left side of the equation because the result is supposed to be in percent
                        if (isset($fv_add_phr_lst)) {
                            log_debug('formula->calc -> add words ' . $fv_add_phr_lst->dsp_id() . ' to the result');
                            foreach ($fv_add_phr_lst->lst as $frm_result_wrd) {
                                $fv->phr_lst->add($frm_result_wrd);
                            }
                            log_debug('formula->calc -> added words ' . $fv_add_phr_lst->dsp_id() . ' to the result ' . $fv->phr_lst->dsp_id());
                        }

                        // make common assumptions on the word list

                        // apply general rules to the result words
                        if (isset($fv_add_phr_lst)) {
                            log_debug('formula->calc -> result words "' . $fv_add_phr_lst->dsp_id() . '" defined for ' . $fv->phr_lst->dsp_id());
                            $fv_add_wrd_lst = $fv_add_phr_lst->wrd_lst_all();

                            // if the result words contains "percent" remove any measure word from the list, because a relative value is expected without measure
                            if ($fv_add_wrd_lst->has_percent()) {
                                log_debug('formula->calc -> has percent');
                                $fv->phr_lst->ex_measure();
                                log_debug('formula->calc -> measure words removed from ' . $fv->phr_lst->dsp_id());
                            }

                            // if in the formula is defined, that the result is in percent
                            // and the values used are in millions, the result is only in percent, but not in millions
                            if ($fv_add_wrd_lst->has_percent()) {
                                $fv->phr_lst->ex_scaling();
                                log_debug('formula->calc -> scaling words removed from ' . $fv->phr_lst->dsp_id());
                                // maybe add the scaling word to the result words to remember based on which words the result has been created,
                                // but probably this is not needed, because the source words are also saved
                                //$scale_wrd_lst = $fv_add_wrd_lst->scaling_lst ();
                                //$fv->phr_lst->merge($scale_wrd_lst->lst);
                                //zu_debug('formula->calc -> added the scaling word "'.implode(",",$scale_wrd_lst->names()).'" to the result words "'.implode(",",$fv->phr_lst->names()).'"');
                            }
                        }

                        $fv = $fv->save_if_updated();

                    }
                }
            }

            /*
        // ??? add the formula name word also to the source words
        $src_phr_lst->add($this->name_wrd);
      */

            $result = $fv_lst->lst;
        }

        log_debug('formula->calc -> done');
        return $result;
    }

    /**
     * return the formula expression as an expression element
     */
    function expression(): expression
    {
        $exp = new expression;
        $exp->ref_text = $this->ref_text;
        $exp->usr_text = $this->usr_text;
        $exp->usr = $this->usr;
        log_debug('formula->expression ' . $exp->ref_text . ' for user ' . $exp->usr->name);
        return $exp;
    }

    /**
     * import a formula from a JSON object
     *
     * @param array $json_obj an array with the data of the json object
     * @param bool $do_save can be set to false for unit testing
     * @return bool true if the import has been successfully saved to the database
     */
    function import_obj(array $json_obj, bool $do_save = true): bool
    {
        global $formula_types;
        global $share_types;
        global $protection_types;

        log_debug('formula->import_obj');
        $result = false;

        // reset the all parameters for the formula object but keep the user
        $usr = $this->usr;
        $this->reset();
        $this->usr = $usr;
        foreach ($json_obj as $key => $value) {
            if ($key == 'name') {
                $this->name = $value;
            }
            if ($key == 'type') {
                $this->type_id = $formula_types->id($value);
            }
            if ($key == 'expression') {
                if ($value <> '') {
                    $this->usr_text = $value;
                }
            }
            if ($key == 'description') {
                if ($value <> '') {
                    $this->description = $value;
                }
            }
            if ($key == 'share') {
                $this->share_id = $share_types->id($value);
            }
            if ($key == 'protection') {
                $this->protection_id = $protection_types->id($value);
            }
        }

        // set the default type if no type is specified
        if ($this->type_id == 0) {
            $this->type_id = $formula_types->default_id();
        }

        return $result;
    }

    /**
     * create an object for the export
     */
    function export_obj(bool $do_load = true): formula_exp
    {
        global $formula_types;

        log_debug('formula->export_obj');
        $result = new formula_exp();

        if ($this->name <> '') {
            $result->name = $this->name;
        }
        if (isset($this->type_id)) {
            if ($this->type_id <> $formula_types->default_id()) {
                $result->type = $formula_types->code_id($this->type_id);
            }
        }
        if ($this->usr_text <> '') {
            $result->expression = $this->usr_text;
        }
        if ($this->description <> '') {
            $result->description = $this->description;
        }

        // add the share type
        if ($this->share_id > 0 and $this->share_id <> cl(db_cl::SHARE_TYPE, share_type_list::DBL_PUBLIC)) {
            $result->share = $this->share_type_code_id();
        }

        // add the protection type
        if ($this->protection_id > 0 and $this->protection_id <> cl(db_cl::PROTECTION_TYPE, protection_type_list::DBL_NO)) {
            $result->protection = $this->protection_type_code_id();
        }

        if ($do_load) {
            $phr_lst = $this->assign_phr_lst_direct();
            foreach ($phr_lst->lst as $phr) {
                $result->assigned_word = $phr->name();
            }
        }

        log_debug('formula->export_obj -> ' . json_encode($result));
        return $result;
    }

    /*
    probably to be replaced with expression functions
    */

    /**
     * returns a positive word id if the formula string in the database format contains a word link
     */
    function get_word($formula)
    {
        log_debug("formula->get_word (" . $formula . ")");
        $result = 0;

        $pos_start = strpos($formula, ZUP_CHAR_WORD_START);
        if ($pos_start === false) {
            $result = 0;
        } else {
            $r_part = zu_str_right_of($formula, ZUP_CHAR_WORD_START);
            $l_part = zu_str_left_of($r_part, ZUP_CHAR_WORD_END);
            if (is_numeric($l_part)) {
                $result = $l_part;
                log_debug("formula->get_word -> " . $result);
            }
        }

        log_debug("formula->get_word -> (" . $result . ")");
        return $result;
    }

    function get_formula($formula)
    {
        log_debug("formula->get_formula (" . $formula . ")");
        $result = 0;

        $pos_start = strpos($formula, ZUP_CHAR_FORMULA_START);
        if ($pos_start === false) {
            $result = 0;
        } else {
            $r_part = zu_str_right_of($formula, ZUP_CHAR_FORMULA_START);
            $l_part = zu_str_left_of($r_part, ZUP_CHAR_FORMULA_END);
            if (is_numeric($l_part)) {
                $result = $l_part;
                log_debug("formula->get_formula -> " . $result);
            }
        }

        log_debug("formula->get_formula -> (" . $result . ")");
        return $result;
    }

    /**
     * extracts an array with the word ids from a given formula text
     */
    function wrd_ids($frm_text, $user_id): array
    {
        log_debug('formula->wrd_ids (' . $frm_text . ',u' . $user_id . ')');
        $result = array();

        // add words to selection
        $new_wrd_id = $this->get_word($frm_text);
        while ($new_wrd_id > 0) {
            if (!in_array($new_wrd_id, $result)) {
                $result[] = $new_wrd_id;
            }
            $frm_text = zu_str_right_of($frm_text, ZUP_CHAR_WORD_START . $new_wrd_id . ZUP_CHAR_WORD_END);
            $new_wrd_id = $this->get_word($frm_text);
        }

        log_debug('formula->wrd_ids -> (' . dsp_array($result) . ')');
        return $result;
    }

    /**
     * extracts an array with the formula ids from a given formula text
     */
    function frm_ids($frm_text, $user_id): array
    {
        log_debug('formula->ids (' . $frm_text . ',u' . $user_id . ')');
        $result = array();

        // add words to selection
        $new_frm_id = $this->get_formula($frm_text);
        while ($new_frm_id > 0) {
            if (!in_array($new_frm_id, $result)) {
                $result[] = $new_frm_id;
            }
            $frm_text = zu_str_right_of($frm_text, ZUP_CHAR_FORMULA_START . $new_frm_id . ZUP_CHAR_FORMULA_END);
            $new_frm_id = $this->get_formula($frm_text);
        }

        log_debug('formula->ids -> (' . dsp_array($result) . ')');
        return $result;
    }

    /**
     * update formula links
     * part of element_refresh for one element type and one user
     */
    function element_refresh_type($frm_text, $element_type, $frm_usr_id, $db_usr_id): bool
    {
        log_debug('formula->element_refresh_type (f' . $this->id . '' . $frm_text . ',' . $element_type . ',u' . $frm_usr_id . ')');

        global $db_con;
        $result = true;

        // read the elements from the formula text
        $elm_type_id = clo($element_type);
        switch ($element_type) {
            case DBL_FORMULA_PART_TYPE_FORMULA:
                $elm_ids = $this->frm_ids($frm_text, $frm_usr_id);
                break;
            default:
                $elm_ids = $this->wrd_ids($frm_text, $frm_usr_id);
                break;
        }
        log_debug('formula->element_refresh_type -> got (' . dsp_array($elm_ids) . ') of type ' . $element_type . ' from text');

        // read the existing elements from the database
        if ($frm_usr_id > 0) {
            $sql = "SELECT ref_id FROM formula_elements WHERE formula_id = " . $this->id . " AND formula_element_type_id = " . $elm_type_id . " AND user_id = " . $frm_usr_id . ";";
        } else {
            $sql = "SELECT ref_id FROM formula_elements WHERE formula_id = " . $this->id . " AND formula_element_type_id = " . $elm_type_id . ";";
        }
        $db_con->usr_id = $this->usr->id;
        $db_con->set_type(DB_TYPE_FORMULA_ELEMENT);
        $db_lst = $db_con->get($sql);

        $elm_db_ids = array();
        foreach ($db_lst as $db_row) {
            $elm_db_ids[] = $db_row['ref_id'];
        }
        log_debug('formula->element_refresh_type -> got (' . dsp_array($elm_db_ids) . ') of type ' . $element_type . ' from database');

        // add missing links
        $elm_add_ids = array_diff($elm_ids, $elm_db_ids);
        log_debug('formula->element_refresh_type -> add ' . $element_type . ' (' . dsp_array($elm_add_ids) . ')');
        foreach ($elm_add_ids as $elm_add_id) {
            $field_names = array();
            $field_values = array();
            $field_names[] = 'formula_id';
            $field_values[] = $this->id;
            if ($frm_usr_id > 0) {
                $field_names[] = 'user_id';
                $field_values[] = $frm_usr_id;
            }
            $field_names[] = 'formula_element_type_id';
            $field_values[] = $elm_type_id;
            $field_names[] = 'ref_id';
            $field_values[] = $elm_add_id;
            $db_con->set_type(DB_TYPE_FORMULA);
            $add_result = $db_con->insert($field_names, $field_values);
            // in this case the row id is not needed, but for testing the number of action should be indicated by adding a '1' to the result string
            //if ($add_result > 0) {
            //    $result .= '1';
            //}
        }

        // delete links not needed any more
        $elm_del_ids = array_diff($elm_db_ids, $elm_ids);
        log_debug('formula->element_refresh_type -> del ' . $element_type . ' (' . dsp_array($elm_del_ids) . ')');
        foreach ($elm_del_ids as $elm_del_id) {
            $field_names = array();
            $field_values = array();
            $field_names[] = 'formula_id';
            $field_values[] = $this->id;
            if ($frm_usr_id > 0) {
                $field_names[] = 'user_id';
                $field_values[] = $frm_usr_id;
            }
            $field_names[] = 'formula_element_type_id';
            $field_values[] = $elm_type_id;
            $field_names[] = 'ref_id';
            $field_values[] = $elm_del_id;
            $db_con->set_type(DB_TYPE_FORMULA);
            if (!$db_con->delete($field_names, $field_values)) {
                $result = false;
            }
        }

        log_debug('formula->element_refresh_type -> (' . zu_dsp_bool($result) . ')');
        return $result;
    }

    /**
     * extracts an array with the word ids from a given formula text
     */
    function element_refresh($frm_text): bool
    {
        log_debug('formula->element_refresh (f' . $this->id . '' . $frm_text . ',u' . $this->usr->id . ')');

        global $db_con;
        $result = true;

        // refresh the links for the standard formula used if the user has not changed the formula
        $result = $this->element_refresh_type($frm_text, DBL_FORMULA_PART_TYPE_WORD, 0, $this->usr->id);

        // update formula links of the standard formula
        if ($result) {
            $result = $this->element_refresh_type($frm_text, DBL_FORMULA_PART_TYPE_FORMULA, 0, $this->usr->id);
        }

        // refresh the links for the user specific formula
        $sql = "SELECT user_id FROM user_formulas WHERE formula_id = " . $this->id . ";";
        //$db_con = New mysql;
        $db_con->usr_id = $this->usr->id;
        $db_lst = $db_con->get($sql);
        foreach ($db_lst as $db_row) {
            // update word links of the user formula
            if ($result) {
                $result = $this->element_refresh_type($frm_text, DBL_FORMULA_PART_TYPE_WORD, $db_row['user_id'], $this->usr->id);
            }
            // update formula links of the standard formula
            if ($result) {
                $result = $this->element_refresh_type($frm_text, DBL_FORMULA_PART_TYPE_FORMULA, $db_row['user_id'], $this->usr->id);
            }
        }

        log_debug('formula->element_refresh -> done' . $result);
        return $result;
    }


    /*
     * link functions - add or remove a link to a word (this is user specific, so use the user sandbox)
     */

    /**
     * link this formula to a word or triple
     */
    function link_phr($phr): bool
    {
        $result = '';
        if (isset($phr) and isset($this->usr)) {
            log_debug('formula->link_phr link ' . $this->dsp_id() . ' to "' . $phr->name . '" for user "' . $this->usr->name . '"');
            $frm_lnk = new formula_link;
            $frm_lnk->usr = $this->usr;
            $frm_lnk->fob = $this;
            $frm_lnk->tob = $phr;
            $result = $frm_lnk->save();
        }
        return $result;
    }

    /**
     * unlink this formula from a word or triple
     */
    function unlink_phr($phr)
    {
        $result = '';
        if (isset($phr) and isset($this->usr)) {
            log_debug('formula->unlink_phr unlink ' . $this->dsp_id() . ' from "' . $phr->name . '" for user "' . $this->usr->name . '"');
            $frm_lnk = new formula_link;
            $frm_lnk->usr = $this->usr;
            $frm_lnk->fob = $this;
            $frm_lnk->tob = $phr;
            $result = $frm_lnk->del();
        } else {
            $result .= log_err("Cannot unlink formula, phrase is not set.", "formula.php");
        }
        return $result;
    }

    /*
     * save functions - to update the formula in the database and for the user sandbox
     */

    /**
     * update the database reference text based on the user text
     */
    function set_ref_text(): string
    {
        $result = '';
        $exp = new expression;
        $exp->usr_text = $this->usr_text;
        $exp->usr = $this->usr;
        $this->ref_text = $exp->get_ref_text();
        $result .= $exp->err_text;
        return $result;
    }

    function is_used(): bool
    {
        return !$this->not_used();
    }

    function not_used(): bool
    {
        /*    $change_user_id = 0;
        $sql = "SELECT user_id
                  FROM user_formulas
                 WHERE formula_id = ".$this->id."
                   AND user_id <> ".$this->owner_id."
                   AND (excluded <> 1 OR excluded is NULL)";
        //$db_con = new mysql;
        $db_con->usr_id = $this->usr->id;
        $change_user_id = $db_con->get1($sql);
        if ($change_user_id > 0) {
          $result = false;
        } */
        return $this->not_changed();
    }

    /**
     * true if no other user has modified the formula
     * assuming that in this case not confirmation from the other users for a formula rename is needed
     */
    function not_changed(): bool
    {
        log_debug('formula->not_changed (' . $this->id . ')');

        global $db_con;
        $result = true;

        if ($this->owner_id > 0) {
            $sql = "SELECT user_id 
                FROM user_formulas 
              WHERE formula_id = " . $this->id . "
                AND user_id <> " . $this->owner_id . "
                AND (excluded <> 1 OR excluded is NULL)";
        } else {
            $sql = "SELECT user_id 
                FROM user_formulas 
              WHERE formula_id = " . $this->id . "
                AND (excluded <> 1 OR excluded is NULL)";
        }
        //$db_con = new mysql;
        $db_con->usr_id = $this->usr->id;
        $db_row = $db_con->get1($sql);
        if ($db_row['user_id'] > 0) {
            $result = false;
        }
        log_debug('formula->not_changed for ' . $this->id . ' is ' . zu_dsp_bool($result));
        return $result;
    }

    /**
     * true if the user is the owner and no one else has changed the formula
     * because if another user has changed the formula and the original value is changed, maybe the user formula also needs to be updated
     */
    function can_change(): bool
    {
        log_debug('formula->can_change ' . $this->dsp_id() . ' by user "' . $this->usr->name . '"');
        $can_change = false;
        if ($this->owner_id == $this->usr->id or $this->owner_id <= 0) {
            $can_change = true;
        }
        log_debug('formula->can_change -> (' . zu_dsp_bool($can_change) . ')');
        return $can_change;
    }

    /**
     * true if a record for a user specific configuration already exists in the database
     */
    function has_usr_cfg(): bool
    {
        $has_cfg = false;
        if ($this->usr_cfg_id > 0) {
            $has_cfg = true;
        }
        return $has_cfg;
    }

    /**
     * create a database record to save user specific settings for this formula
     * TODO combine the reread and the adding in a commit transaction
     */
    function add_usr_cfg(): bool
    {
        global $db_con;
        $result = true;

        if (!$this->has_usr_cfg()) {
            log_debug('formula->add_usr_cfg for "' . $this->dsp_id() . ' und user ' . $this->usr->name);

            // check again if there ist not yet a record
            $db_con->set_type(DB_TYPE_FORMULA, true);
            $db_con->set_usr($this->usr->id);
            $db_con->set_where($this->id);
            $sql = $db_con->select();
            $db_row = $db_con->get1($sql);
            if ($db_row != null) {
                $this->usr_cfg_id = $db_row['formula_id'];
            }
            if (!$this->has_usr_cfg()) {
                // create an entry in the user sandbox
                $db_con->set_type(DB_TYPE_USER_PREFIX . DB_TYPE_FORMULA);
                $log_id = $db_con->insert(array('formula_id', 'user_id'), array($this->id, $this->usr->id));
                if ($log_id <= 0) {
                    log_err('Insert of user_formula failed.');
                    $result = false;
                } else {
                    $result = true;
                }
            }
        }
        return $result;
    }

    /**
     * check if the database record for the user specific settings can be removed
     */
    function del_usr_cfg_if_not_needed(): bool
    {
        log_debug('formula->del_usr_cfg_if_not_needed pre check for "' . $this->dsp_id() . ' und user ' . $this->usr->name);

        global $db_con;
        $result = false;


        // check again if the user config is still needed (don't use $this->has_usr_cfg to include all updated)
        $sql = "SELECT formula_id,
                   formula_name,
                   formula_text,
                   resolved_text,
                   description,
                   formula_type_id,
                   all_values_needed,
                   excluded
              FROM user_formulas
             WHERE formula_id = " . $this->id . " 
               AND user_id = " . $this->usr->id . ";";
        //$db_con = New mysql;
        $db_con->usr_id = $this->usr->id;
        $usr_cfg = $db_con->get1($sql);
        log_debug('formula->del_usr_cfg_if_not_needed check for "' . $this->dsp_id() . ' und user ' . $this->usr->name . ' with (' . $sql . ')');
        if ($usr_cfg['formula_id'] > 0) {
            if ($usr_cfg['formula_text'] == ''
                and $usr_cfg['resolved_text'] == ''
                and $usr_cfg[sql_db::FLD_DESCRIPTION] == ''
                and $usr_cfg['formula_type_id'] == Null
                and $usr_cfg['all_values_needed'] == Null
                and $usr_cfg['excluded'] == Null) {
                // delete the entry in the user sandbox
                log_debug('formula->del_usr_cfg_if_not_needed any more for "' . $this->dsp_id() . ' und user ' . $this->usr->name);
                $result = $this->del_usr_cfg_exe($db_con);
            } else {
                log_debug('formula->del_usr_cfg_if_not_needed not true for "' . $this->dsp_id() . ' und user ' . $this->usr->name);
            }
        }

        return $result;
    }

    /**
     * simply remove a user adjustment without check
     */
    function del_usr_cfg_exe($db_con): bool
    {

        $db_con->set_type(DB_TYPE_FORMULA_ELEMENT);
        $result = $db_con->delete(
            array('formula_id', 'user_id'),
            array($this->id, $this->usr->id));
        if ($result) {
            $db_con->set_type(DB_TYPE_USER_PREFIX . DB_TYPE_FORMULA);
            $result = $db_con->delete(
                array('formula_id', 'user_id'),
                array($this->id, $this->usr->id));
            if (!$result) {
                $result .= 'Deletion of user formula ' . $this->id . ' failed for ' . $this->usr->name . '.';
            }
        }

        return $result;
    }

    /**
     * remove user adjustment and log it (used by user.php to undo the user changes)
     */
    function del_usr_cfg(): bool
    {

        global $db_con;
        $result = '';

        if ($this->id > 0 and $this->usr->id > 0) {
            log_debug('formula->del_usr_cfg  "' . $this->id . ' und user ' . $this->usr->name);

            $log = $this->log_del();
            if ($log->id > 0) {
                $db_con->usr_id = $this->usr->id;
                $result = $this->del_usr_cfg_exe($db_con);
            }

        } else {
            log_err("The formula database ID and the user must be set to remove a user specific modification.", "formula->del_usr_cfg");
        }

        return $result;
    }

    /**
     * update the time stamp to trigger an update of the depending results
     */
    function save_field_trigger_update($db_con): bool
    {
        $this->last_update = new DateTime();
        $db_con->set_type(DB_TYPE_FORMULA);
        $result = $db_con->update($this->id, 'last_update', 'Now()');
        log_debug('formula->save_field_trigger_update timestamp of ' . $this->id . ' updated to "' . $this->last_update->format('Y-m-d H:i:s') . '" with ' . $result);

        // save the pending update to the database for the batch calculation
        return $result;
    }

    /**
     * set the update parameters for the formula text as written by the user if needed
     */
    function save_field_usr_text($db_con, $db_rec, $std_rec): bool
    {
        $result = true;
        if ($db_rec->usr_text <> $this->usr_text) {
            $this->needs_fv_upd = true;
            $log = $this->log_upd();
            $log->old_value = $db_rec->usr_text;
            $log->new_value = $this->usr_text;
            $log->std_value = $std_rec->usr_text;
            $log->row_id = $this->id;
            $log->field = 'resolved_text';
            $result = $this->save_field_do($db_con, $log);
        }
        return $result;
    }

    /**
     * set the update parameters for the formula in the database reference format
     */
    function save_field_ref_text($db_con, $db_rec, $std_rec): bool
    {
        $result = true;
        if ($db_rec->ref_text <> $this->ref_text) {
            $this->needs_fv_upd = true;
            $log = $this->log_upd();
            $log->old_value = $db_rec->ref_text;
            $log->new_value = $this->ref_text;
            $log->std_value = $std_rec->ref_text;
            $log->row_id = $this->id;
            $log->field = 'formula_text';
            $result = $this->save_field_do($db_con, $log);
            // updating the reference expression is probably relevant for calculation, so force to update the timestamp
            if ($result) {
                $result = $this->save_field_trigger_update($db_con);
            }
        }
        return $result;
    }

    /**
     * set the update parameters for the formula type
     * todo: save the reference also in the log
     */
    function save_field_type($db_con, $db_rec, $std_rec): bool
    {
        $result = true;
        if ($db_rec->type_id <> $this->type_id) {
            $this->needs_fv_upd = true;
            $log = $this->log_upd();
            $log->old_value = $db_rec->formula_type_name();
            $log->old_id = $db_rec->type_id;
            $log->new_value = $this->formula_type_name();
            $log->new_id = $this->type_id;
            $log->std_value = $std_rec->formula_type_name();
            $log->std_id = $std_rec->type_id;
            $log->row_id = $this->id;
            $log->field = 'formula_type_id';
            $result = $this->save_field_do($db_con, $log);
        }
        return $result;
    }

    /**
     * set the update parameters that define if all formula values are needed to calculate a result
     */
    function save_field_need_all($db_con, $db_rec, $std_rec): bool
    {
        $result = true;
        if ($db_rec->need_all_val <> $this->need_all_val) {
            $this->needs_fv_upd = true;
            $log = $this->log_upd();
            if ($db_rec->need_all_val) {
                $log->old_value = '1';
            } else {
                $log->old_value = '0';
            }
            if ($this->need_all_val) {
                $log->new_value = '1';
            } else {
                $log->new_value = '0';
            }
            if ($std_rec->need_all_val) {
                $log->std_value = '1';
            } else {
                $log->std_value = '0';
            }
            $log->row_id = $this->id;
            $log->field = 'all_values_needed';
            $result = $this->save_field_do($db_con, $log);
            // if it is switch on that all fields are needed for the calculation, probably some formula results can be removed
            if ($result) {
                $result = $this->save_field_trigger_update($db_con);
            }
        }
        return $result;
    }

    /**
     * save all updated formula fields
     */
    function save_fields($db_con, $db_rec, $std_rec): bool
    {
        $result = $this->save_field_usr_text($db_con, $db_rec, $std_rec);
        if ($result) {
            $result = $this->save_field_ref_text($db_con, $db_rec, $std_rec);
        }
        if ($result) {
            $result = $this->save_field_description($db_con, $db_rec, $std_rec);
        }
        if ($result) {
            $result = $this->save_field_type($db_con, $db_rec, $std_rec);
        }
        if ($result) {
            $result = $this->save_field_need_all($db_con, $db_rec, $std_rec);
        }
        if ($result) {
            $result = $this->save_field_excluded($db_con, $db_rec, $std_rec);
        }
        log_debug('formula->save_fields "' . $result . '" fields for ' . $this->dsp_id() . ' has been saved');
        return $result;
    }

    /**
     * set the update parameters for the formula text as written by the user if needed
     */
    function save_field_name($db_con, $db_rec, $std_rec)
    {
        $result = '';
        if ($db_rec->name <> $this->name) {
            log_debug('formula->save_field_name to ' . $this->dsp_id() . ' from "' . $db_rec->name . '"');
            $this->needs_fv_upd = true;
            if ($this->can_change() and $this->not_changed()) {
                $log = $this->log_upd();
                $log->old_value = $db_rec->name;
                $log->new_value = $this->name;
                $log->std_value = $std_rec->name;
                $log->row_id = $this->id;
                $log->field = 'formula_name';
                $result .= $this->save_field_do($db_con, $log);
                // in case a word link exist, change also the name of the word
                $wrd = new word_dsp;
                $wrd->name = $db_rec->name;
                $wrd->usr = $this->usr;
                $wrd->load();
                $wrd->name = $this->name;
                $result .= $wrd->save();

            } else {
                // create a new formula
                // and request the deletion confirms for the old from all changers
                // ???? or update the user formula table
            }
        }
        return $result;
    }

    /**
     * updated the view component name (which is the id field)
     * should only be called if the user is the owner and nobody has used the display component link
     */
    function save_id_fields($db_con, $db_rec, $std_rec): bool
    {
        $result = true;
        if ($db_rec->name <> $this->name) {
            log_debug('formula->save_id_fields to ' . $this->dsp_id() . ' from ' . $db_rec->dsp_id() . ' (standard ' . $std_rec->dsp_id() . ')');
            // in case a word link exist, change also the name of the word
            $wrd = new word_dsp;
            $wrd->name = $db_rec->name;
            $wrd->usr = $this->usr;
            $wrd->load();
            $wrd->name = $this->name;
            $result .= $wrd->save();
            log_debug('formula->save_id_fields word "' . $db_rec->name . '" renamed to ' . $wrd->dsp_id());

            // change the formula name
            $log = $this->log_upd();
            $log->old_value = $db_rec->name;
            $log->new_value = $this->name;
            $log->std_value = $std_rec->name;
            $log->row_id = $this->id;
            $log->field = 'formula_name';
            if ($log->add()) {
                $db_con->set_type(DB_TYPE_FORMULA);
                $result = $db_con->update($this->id,
                    array("formula_name"),
                    array($this->name));
            }
        }
        log_debug('formula->save_id_fields for ' . $this->dsp_id() . ' has been done');
        return $result;
    }

    /**
     * check if the id parameters are supposed to be changed
     */
    function save_id_if_updated($db_con, $db_rec, $std_rec): string
    {
        log_debug('formula->save_id_if_updated has name changed from "' . $db_rec->name . '" to ' . $this->dsp_id());
        $result = '';

        // if the name has changed, check if word, verb or formula with the same name already exists; this should have been checked by the calling function, so display the error message directly if it happens
        if ($db_rec->name <> $this->name) {
            // check if a verb or word with the same name is already in the database
            $trm = $this->term();
            if ($trm->id > 0 and $trm->type <> 'formula') {
                $result .= $trm->id_used_msg();
                log_debug('formula->save_id_if_updated name "' . $trm->name . '" used already as "' . $trm->type . '"');
            } else {

                // check if target formula already exists
                log_debug('formula->save_id_if_updated check if target formula already exists ' . $this->dsp_id() . ' (has been ' . $db_rec->dsp_id() . ')');
                $db_chk = clone $this;
                $db_chk->id = 0; // to force the load by the id fields
                $db_chk->load_standard();
                if ($db_chk->id > 0) {
                    log_debug('formula->save_id_if_updated target formula name already exists ' . $db_chk->dsp_id());
                    if (UI_CAN_CHANGE_FORMULA_NAME) {
                        // ... if yes request to delete or exclude the record with the id parameters before the change
                        $to_del = clone $db_rec;
                        $result .= $to_del->del();
                        // .. and use it for the update
                        $this->id = $db_chk->id;
                        $this->owner_id = $db_chk->owner_id;
                        // force the include again
                        $this->excluded = null;
                        $db_rec->excluded = '1';
                        $this->save_field_excluded($db_con, $db_rec, $std_rec);
                        log_debug('formula->save_id_if_updated found a display component link with target ids "' . $db_chk->dsp_id() . '", so del "' . $db_rec->dsp_id() . '" and add ' . $this->dsp_id());
                    } else {
                        $result .= 'A view component with the name "' . $this->name . '" already exists. Please use another name.';
                    }
                } else {
                    log_debug('formula->save_id_if_updated target formula name does not yet exists ' . $db_chk->dsp_id());
                    if ($this->can_change() and $this->not_used()) {
                        // in this case change is allowed and done
                        log_debug('formula->save_id_if_updated change the existing display component link ' . $this->dsp_id() . ' (db "' . $db_rec->dsp_id() . '", standard "' . $std_rec->dsp_id() . '")');
                        //$this->load_objects();
                        $result .= $this->save_id_fields($db_con, $db_rec, $std_rec);
                    } else {
                        // if the target link has not yet been created
                        // ... request to delete the old
                        $to_del = clone $db_rec;
                        $result .= $to_del->del();
                        // .. and create a deletion request for all users ???

                        // ... and create a new display component link
                        $this->id = 0;
                        $this->owner_id = $this->usr->id;
                        // TODO check the result values and if the id is needed
                        $result = strval($this->add());
                        log_debug('formula->save_id_if_updated recreate the display component link del "' . $db_rec->dsp_id() . '" add ' . $this->dsp_id() . ' (standard "' . $std_rec->dsp_id() . '")');
                    }
                }
            }
        }

        log_debug('formula->save_id_if_updated for ' . $this->dsp_id() . ' has been done');
        return $result;
    }

    /**
     * create a new formula
     * the user sandbox function is overwritten because the formula text should never be null
     * and the corresponding formula word is created
     */
    function add(): int
    {
        log_debug('formula->add ' . $this->dsp_id());

        global $db_con;
        $result = 0;

        // log the insert attempt first
        $log = $this->log_add();
        if ($log->id > 0) {
            // insert the new formula
            $db_con->set_type(DB_TYPE_FORMULA);
            // include the formula_text and the resolved_text, because they should never be empty which is also forced by the db structure
            $this->id = $db_con->insert(
                array("formula_name", "user_id", "last_update", "formula_text", "resolved_text"),
                array($this->name, $this->usr->id, "Now()", $this->ref_text, $this->usr_text));
            if ($this->id > 0) {
                log_debug('formula->add formula ' . $this->dsp_id() . ' has been added as ' . $this->id);
                // update the id in the log for the correct reference
                if (!$log->add_ref($this->id)) {
                    log_err('Updating the reference in the log failed');
                    // TODO do rollback or retry?
                } else {
                    // create the related formula word
                    if ($this->create_wrd()) {

                        // create an empty db_frm element to force saving of all set fields
                        $db_rec = new formula;
                        $db_rec->name = $this->name;
                        $db_rec->usr = $this->usr;
                        $std_rec = clone $db_rec;
                        // save the formula fields
                        if ($this->save_fields($db_con, $db_rec, $std_rec)) {
                            $result = $this->id;
                        }
                    }
                }
            } else {
                log_err("Adding formula " . $this->name . " failed.", "formula->add");
            }
        }

        return $result;
    }

    /**
     * add or update a formula in the database or create a user formula
     * overwrite the user_sandbox function to create the formula ref text; maybe combine later
     */
    function save(): string
    {
        log_debug('formula->save >' . $this->usr_text . '< (id ' . $this->id . ') as ' . $this->dsp_id() . ' for user ' . $this->usr->name);

        global $db_con;
        $result = '';

        // build the database object because the is anyway needed
        $db_con->set_usr($this->usr->id);
        $db_con->set_type(DB_TYPE_FORMULA);

        // check if a new formula is supposed to be added
        if ($this->id <= 0) {
            // check if a verb, formula or word with the same name is already in the database
            log_debug('formula->save -> add ' . $this->dsp_id());
            $trm = $this->term();
            if ($trm->id > 0) {
                if ($trm->type <> 'formula') {
                    $result .= $trm->id_used_msg();
                } else {
                    $this->id = $trm->id;
                    log_debug('formula->save adding formula name ' . $this->dsp_id() . ' is OK');
                }
            }
        }

        // create a new formula or update an existing
        if ($this->id <= 0) {
            // convert the formula text to db format (any error messages should have been returned from the calling user script)
            if ($this->set_ref_text() <> '') {
                $result = strval($this->add());
            }
        } else {
            log_debug('formula->save -> update ' . $this->id);
            // read the database values to be able to check if something has been changed; done first,
            // because it needs to be done for user and general formulas
            $db_rec = new formula;
            $db_rec->id = $this->id;
            $db_rec->usr = $this->usr;
            $db_rec->load();
            log_debug('formula->save -> database formula "' . $db_rec->name . '" (' . $db_rec->id . ') loaded');
            $std_rec = new formula;
            $std_rec->id = $this->id;
            $std_rec->usr = $this->usr; // must also be set to allow to take the ownership
            $std_rec->load_standard();
            log_debug('formula->save -> standard formula "' . $std_rec->name . '" (' . $std_rec->id . ') loaded');

            // for a correct user formula detection (function can_change) set the owner even if the formula has not been loaded before the save
            if ($this->owner_id <= 0) {
                $this->owner_id = $std_rec->owner_id;
            }

            // ... and convert the formula text to db format (any error messages should have been returned from the calling user script)
            if ($this->set_ref_text() <> '') {

                // check if the id parameters are supposed to be changed
                if ($result == '') {
                    $result = $this->save_id_if_updated($db_con, $db_rec, $std_rec);
                }

                // if a problem has appeared up to here, don't try to save the values
                // the problem is shown to the user by the calling interactive script
                if ($result == '') {
                    if (!$this->save_fields($db_con, $db_rec, $std_rec)) {
                        $result = 'Saving of fields for ' . $this->obj_name . ' failed';
                        log_err($result);
                    }
                }
            }

            // update the reference table for fast calculation
            // a '1' in the result only indicates that an update has been done for testing; '1' doesn't mean that there has been an error
            if ($result == '') {
                if (!$this->element_refresh($this->ref_text)) {
                    $result = 'Refresh of the formula elements failed';
                    log_err($result);
                }
            }
        }

        return $result;

    }

    // TODO user specific???
    function del_links(): bool
    {
        $result = false;
        $frm_lnk_lst = new formula_link_list;
        $frm_lnk_lst->usr = $this->usr;
        $frm_lnk_lst->frm = $this;
        if ($frm_lnk_lst->load()) {
            $result = $frm_lnk_lst->del_without_log();
        }
        return $result;
    }

}