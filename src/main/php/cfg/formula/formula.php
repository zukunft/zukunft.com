<?php

/*

    model/formula/formula.php - the main formula object for calculation
    -----------------------

    The main sections of this object are
    - db const:          const for the database link
    - object vars:       the variables of this formula object
    - construct and map: including the mapping of the db row to this formula object
    - api:               create an api array for the frontend and set the vars based on a frontend api message
    - set and get:       to capsule the vars from unexpected changes
    - preloaded:         select e.g. types from cache
    - cast:              create an api object and set the vars from an api json
    - load:              database access object (DAO) functions
    - word:              manage the related word
    - info:              functions to make code easier to read
    - assign:            to define when a formula should be used
    - result:            manage the formula results
    - calc:              manage the formula calculation
    - im- and export:    create an export object and set the vars from an import object
    - expression:        handel to single parts of a formula
    - link:              add or remove a link to a word (this is user specific, so use the user sandbox)
    - save:              to update the formula in the database and for the user sandbox
    - del:               manage to remove from the database
    - sql write:         sql statement creation to write to the database
    - sql write fields:  field list for writing to the database


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

    Copyright (c) 1995-2024 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

namespace Zukunft\ZukunftCom\main\php\cfg\formula;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_ELEMENT . 'element.php';
include_once paths::MODEL_ELEMENT . 'element_list.php';
include_once paths::MODEL_FORMULA . 'formula_map.php';
include_once paths::MODEL_PHRASE . 'phr_ids.php';
include_once paths::MODEL_PHRASE . 'phrase.php';
include_once paths::MODEL_PHRASE . 'phrase_list.php';
include_once paths::MODEL_PHRASE . 'term_list.php';
include_once paths::MODEL_RESULT . 'result.php';
include_once paths::MODEL_RESULT . 'result_list.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_db.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::MODEL_VALUE . 'value.php';
include_once paths::SERVICE_MATH . 'calc_internal.php';
include_once paths::SHARED_TYPES . 'phrase_type.php';
include_once paths::SHARED_CALC . 'parameter_type.php';
include_once paths::SHARED_CONST . 'chars.php';
include_once paths::SHARED . 'library.php';

use Zukunft\ZukunftCom\main\php\cfg\element\element;
use Zukunft\ZukunftCom\main\php\cfg\element\element_list;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phr_ids;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\cfg\phrase\term_list;
use Zukunft\ZukunftCom\main\php\cfg\result\result;
use Zukunft\ZukunftCom\main\php\cfg\result\result_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_db;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\value\value;
use Zukunft\ZukunftCom\main\php\service\math\calc_internal;
use Zukunft\ZukunftCom\main\php\shared\calc\parameter_type;
use Zukunft\ZukunftCom\main\php\shared\const\chars;
use Zukunft\ZukunftCom\main\php\shared\library;

class formula extends formula_map
{

    /*
     * object vars
     */


    // in memory-only fields
    // list of phrase that links to this formula
    public ?string $ref_text_r = '';       // the part of the formula expression that is right of the equation sign (used as a work-in-progress field for calculation)


    /*
     * construct and map
     */

    /**
     * define the settings for this formula object
     * @param user $usr the user who requested to see this formula
     */
    function __construct(user $usr)
    {
        $this->reset();
        parent::__construct($usr);
    }

    /**
     * clear the view component object values
     * @param bool $keep_user set to true to keep the original user
     * @return void
     */
    function reset(bool $keep_user = false): void
    {
        parent::reset($keep_user);

        $this->ref_text_r = '';
    }


    /*
     * info
     */

    /**
     * if the formula has a fixed process for the result
     * e.g. "this" or "next" where the value of this or the following time word is returned
     * @return bool true if result calculation is a kind of hardcoded
     */
    function is_predefined(): bool
    {
        return in_array($this->type_code_id(), formula_type::PREDEFINED_CALCULATION);
    }


    /*
     * predefined
     */

    /**
     * return the result of a special formula
     * e.g. "this" or "next" where the value of this or the following time word is returned
     */
    function calc_predefined(phrase_list $phr_lst, ?phrase $time_phr = null): value
    {
        log_debug("formula->special_result (" . $this->id() . ",t" . $phr_lst->dsp_id() . ",time" . $time_phr->name() . " and user " . $this->get_user()->name . ")");
        $val = null;

        if ($this->type_id > 0) {
            log_debug("type (" . $this->type_cl . ")");
            if ($this->type_cl == formula_type::THIS) {
                $val_phr_lst = clone $phr_lst;
                $val_phr_lst->add($time_phr); // the time word should be added at the end, because ...
                log_debug("this (" . $time_phr->name() . ")");
                $val = $val_phr_lst->value_scaled();
            }
            if ($this->type_cl == formula_type::NEXT) {
                $val_phr_lst = clone $phr_lst;
                $next_wrd = $time_phr->next();
                if ($next_wrd->id() > 0) {
                    $val_phr_lst->add($next_wrd); // the time word should be added at the end, because ...
                    log_debug("next (" . $next_wrd->name() . ")");
                    $val = $val_phr_lst->value_scaled();
                }
            }
            if ($this->type_cl == formula_type::PREV) {
                $val_phr_lst = clone $phr_lst;
                $prior_wrd = $time_phr->prior();
                if ($prior_wrd->id() > 0) {
                    $val_phr_lst->add($prior_wrd->phrase()); // the time word should be added at the end, because ...
                    log_debug("prior (" . $prior_wrd->name() . ")");
                    $val = $val_phr_lst->value_scaled();
                }
            }
        }

        log_debug('result: ' . $val->number());
        return $val;
    }

    /**
     * return the time word id used for the special formula results
     * e.g. "this" or "next" where the value of this or the following time word is returned
     * TODO Prio 1 move to phrase list
     */
    function special_time_phr(phrase $time_phr): phrase
    {
        log_debug($this->type_cl . ' for ' . $time_phr->dsp_id());
        $result = $time_phr;

        if ($this->type_id > 0) {
            if ($time_phr->id() <= 0) {
                log_err('No time defined for ' . $time_phr->dsp_id() . '.', 'formula->special_time_phr');
            } else {
                if ($this->type_cl == formula_type::THIS) {
                    $result = $time_phr;
                }
                if ($this->type_cl == formula_type::NEXT) {
                    $this_wrd = $time_phr->main_word();
                    $next_wrd = $this_wrd->next();
                    $result = $next_wrd->phrase();
                }
                if ($this->type_cl == formula_type::PREV) {
                    $this_wrd = $time_phr->main_word();
                    $prior_wrd = $this_wrd->prior();
                    $result = $prior_wrd->phrase();
                }
            }
        }

        log_debug('got ' . $result->dsp_id());
        return $result;
    }

    /**
     * get all phrases included by a special formula element for a list of phrases
     * e.g. if the list of phrases is "2016" and "2017" and the special formulas are "prior" and "next" the result should be "2015", "2016","2017" and "2018"
     * TODO Prio 1 move to phrase list
     */
    function special_phr_lst(phrase_list $phr_lst): phrase_list
    {
        log_debug('for ' . $phr_lst->dsp_id());
        $result = clone $phr_lst;

        foreach ($phr_lst->lst() as $phr) {
            // temp solution utils the real reason is found why the phrase list elements are missing the user settings
            if (!isset($phr->usr)) {
                $phr->set_user($this->get_user());
            }
            // get all special phrases
            $time_phr = $this->special_time_phr($phr);
            if (isset($time_phr)) {
                $result->add($time_phr);
                log_debug('added time ' . $time_phr->dsp_id() . ' to ' . $result->dsp_id());
            }
        }

        log_debug($result->dsp_id());
        return $result;
    }


    /*
     * assign
     */

    /**
     * lists of all words directly assigned to a formula and where the formula should be used
     * TODO Prio 1 move to phrase list
     * TODO rename to
     * - linked_phrases:               for the phrases directly linked       to the formula based on the user settings
     * - linked_phrases_standard:      for the phrases directly linked       to the formula based on the standard settings for new user
     * - linked_phrases_all_user:      for the phrases directly linked       to the formula for any user
     * - linked_foaf_phrases:          for the linked phrases including foaf to the formula based on the user settings
     * - linked_foaf_phrases_standard: for the linked phrases including foaf to the formula based on the standard settings for new user
     * - linked_foaf_phrases_all_user: for the linked phrases including foaf to the formula for any user
     */
    function assign_phr_glst_direct($sbx): ?phrase_list
    {
        $phr_lst = null;
        $lib = new library();

        if ($this->id() > 0 and $this->get_user() != null) {
            log_debug('for formula ' . $this->dsp_id() . ' and user "' . $this->get_user()->name . '"');
            $frm_lnk_lst = new formula_link_list($this->get_user());
            $frm_lnk_lst->load_by_frm_id($this->id());
            $phr_ids = $frm_lnk_lst->phrase_ids($sbx);

            if (count($phr_ids->lst) > 0) {
                $phr_lst = new phrase_list($this->get_user());
                $phr_lst->load_names_by_ids($phr_ids);
                log_debug("number of words " . $lib->dsp_count($phr_lst->lst()));
            }
        } else {
            log_err("The user id must be set to list the formula links.", "formula->assign_phr_glst_direct");
        }

        return $phr_lst;
    }

    /**
     * the complete list of a phrases assigned to a formula
     * TODO Prio 1 move to phrase list
     * TODO rename to linked_foaf_phrases_standard
     */
    function assign_phr_lst_direct(): ?phrase_list
    {
        return $this->assign_phr_glst_direct(false);
    }

    /**
     * the user specific list of a phrases assigned to a formula
     * TODO Prio 1 move to phrase list
     */
    function assign_phr_ulst_direct(): ?phrase_list
    {
        return $this->assign_phr_glst_direct(true);
    }

    /**
     * returns a list of all words that the formula is assigned to
     * e.g. if the formula is assigned to "company" and "ABB is a company" include ABB in the word list
     * TODO Prio 1 move to phrase list
     */
    function assign_phr_glst($sbx): phrase_list
    {
        $phr_lst = new phrase_list($this->get_user());
        $lib = new library();

        if ($this->id() > 0 and $this->get_user() != null) {
            $direct_phr_lst = $this->assign_phr_glst_direct($sbx);
            if ($direct_phr_lst != null) {
                if (!$direct_phr_lst->is_empty()) {
                    log_debug($this->dsp_id() . ' direct assigned words and triples ' . $direct_phr_lst->dsp_id());

                    //$indirect_phr_lst = $direct_phr_lst->is();
                    $indirect_phr_lst = $direct_phr_lst->are();
                    log_debug('indirect assigned words and triples ' . $indirect_phr_lst->dsp_id());

                    // merge direct and indirect assigns (maybe later using phrase_list->merge)
                    $phr_ids = array_merge($direct_phr_lst->id_lst(), $indirect_phr_lst->id_lst());
                    $phr_ids = array_unique($phr_ids);

                    $phr_lst->load_by_ids((new phr_ids($phr_ids)));
                    log_debug('number of words and triples ' . $lib->dsp_count($phr_lst->lst()));
                } else {
                    log_debug('no words are assigned to ' . $this->dsp_id());
                }
            }
        } else {
            log_err('The id and user id must be set to list the formula links.', 'formula->assign_phr_glst');
        }

        return $phr_lst;
    }

    /**
     * the complete list of a phrases assigned to a formula
     * TODO Prio 1 move to phrase list
     */
    function assign_phr_lst(): phrase_list
    {
        return $this->assign_phr_glst(false);
    }

    /**
     * the user specific list of a phrases assigned to a formula
     * TODO Prio 1 move to phrase list
     */
    function assign_phr_ulst(): phrase_list
    {
        return $this->assign_phr_glst(true);
    }


    /*
     * result
     */

    /**
     * delete all results for this formula
     * @return string an empty string if the deletion has been successful
     *                or the error message that should be shown to the user
     *                which may include a link for error tracing
     */
    function res_del(): string
    {
        log_debug("formula->res_del (" . $this->id() . ")");

        global $db_con;

        $db_con->set_class(result::class);
        $db_con->set_usr($this->get_user()->id());
        return $db_con->delete_old(formula_db::FLD_ID, $this->id());
    }

    /**
     * create a result object for this formula
     *
     * @param phrase_list $phr_lst list of the phrases that describes the result
     * @return result with the value from this formula
     */
    private function create_result(phrase_list $phr_lst): result
    {
        $rst = new result($this->get_user());
        $rst->frm = $this;
        $rst->src_grp = $phr_lst->get_grp_id();
        $rst->ref_text = $this->ref_text_r;
        $rst->num_text = $this->ref_text_r;
        $rst->src_grp->set_phrase_list(clone $phr_lst);
        $rst->grp()->set_phrase_list(clone $phr_lst);
        if ($rst->last_val_update < $this->last_update) {
            $rst->last_val_update = $this->last_update;
        }
        return $rst;
    }


    /*
     * calc
     */

    /**
     * fill the formula in the reference format with numbers
     * TODO review by splitting it up
     *
     * @param phrase_list $phr_lst list of phrase used to select the value for the calculation
     * @param phrase_list|null $pre_phr_lst list of preloaded / cached terms
     * TODO verbs
     * @return result_list all results of the formula for the given phrase list
     */
    function to_num(phrase_list $phr_lst, ?phrase_list $pre_phr_lst = null): result_list
    {
        log_debug('get numbers for ' . $this->dsp_id() . ' and ' . $phr_lst->dsp_id());
        $lib = new library();

        // check
        $pre_trm_lst = $pre_phr_lst?->term_list();
        if ($this->ref_text_r == '' and $this->ref_text <> '') {
            $exp = new expression($this->get_user());
            $exp->set_ref_text($this->ref_text, $pre_trm_lst);
            $this->ref_text_r = chars::CHAR_CALC . $exp->r_part();
        }

        // create the result list
        $res_lst = new result_list($this->get_user());

        // create a master result object to only need to fill it with the numbers in the code below
        $res_init = $this->create_result($phr_lst); // maybe move the constructor of result_list?

        // load the formula element groups; similar parts is used in the explain method in result
        // e.g. for "sales differentiator sector / Total sales" the element groups are
        //      "sales differentiator sector" and "Total sales" where
        //      the element group "sales differentiator sector" has the elements: "sales" (of type word), "differentiator" (verb), "sector" (word)
        $exp = $this->expression($pre_trm_lst);
        $elm_grp_lst = $exp->element_grp_lst($pre_trm_lst);
        log_debug('in ' . $exp->ref_text() . ' ' . $lib->dsp_count($elm_grp_lst->lst()) . ' element groups found');

        // to check if all needed values are given
        $all_elm_grp_filled = true;

        // loop over the element groups and replace the symbol with a number
        // TODO move to an element_exe class
        foreach ($elm_grp_lst->lst() as $elm_grp) {

            // get the figures based on the context e.g. the formula element "Share Price" for the context "ABB" can be 23.11
            // a figure is either the user edited value or a calculated formula result
            $elm_grp->phr_lst = clone $phr_lst;
            $elm_grp->build_symbol();
            $fig_lst = $elm_grp->figures($pre_trm_lst);
            log_debug('figures ');
            log_debug('figures ' . $fig_lst->dsp_id() . ' (' . $lib->dsp_count($fig_lst->lst()) . ') for ' . $elm_grp->dsp_id());

            // fill the figure into the formula text and create as much value and results as needed
            if ($fig_lst->lst() != null) {
                if (count($fig_lst->lst()) == 1) {
                    // if no figure is found, use the master result as a placeholder
                    if ($res_lst->lst() != null) {
                        if (count($res_lst->lst()) == 0) {
                            $res_lst->add_obj($res_init);
                        }
                    } else {
                        $res_lst->add_obj($res_init);
                    }
                    // fill each result created by any previous number filling
                    foreach ($res_lst->lst() as $res) {
                        // fill each result created by any previous number filling
                        if (!$res->val_missing) {
                            if ($fig_lst->fig_missing and $this->need_all_val) {
                                log_debug('figure missing');
                                $res->val_missing = True;
                            } else {
                                $fig = $fig_lst->lst()[0];
                                $res->num_text = str_replace($fig->get_symbol(), $fig->number(), $res->num_text);
                                if ($res->last_val_update < $fig->last_update()) {
                                    $res->last_val_update = $fig->last_update();
                                }
                                log_debug('one figure "' . $fig->number() . '" for "' . $fig->get_symbol() . '" in "' . $res->num_text . '"');
                            }
                        }
                    }
                } elseif (count($fig_lst->lst()) > 1) {
                    // create the formula result object only if at least one figure if found
                    if (count($res_lst->lst()) == 0) {
                        $res_lst->add_obj($res_init);
                    }
                    // if there is more than one number to fill, replicate each previous result, so in fact it multiplies the number of results
                    foreach ($res_lst->lst() as $res) {
                        $res_master = clone $res;
                        $fig_nbr = 1;
                        foreach ($fig_lst->lst() as $fig) {
                            if (!$res->val_missing) {
                                if ($fig_lst->fig_missing and $this->need_all_val) {
                                    log_debug('figure missing');
                                    $res->val_missing = True;
                                } else {
                                    // for the first previous result, just fill in the first number
                                    if ($fig_nbr == 1) {

                                        // if the result has been the standard result utils now
                                        if ($res->is_std()) {
                                            // ... and the value is user specific
                                            if (!$fig->is_std()) {
                                                // split the result into a standard
                                                // get the standard value
                                                // $fig_std = ...;
                                                $res_std = clone $res;
                                                $res_std->num_text = str_replace($fig->get_symbol(), $fig->number(), $res_std->num_text);
                                                if ($res_std->last_val_update < $fig->last_update()) {
                                                    $res_std->last_val_update = $fig->last_update();
                                                }
                                                log_debug('one figure "' . $fig->number() . '" for "' . $fig->get_symbol() . '" in "' . $res->num_text . '"');
                                                $res_lst->add_obj($res_std);
                                                // ... and split into a user specific part
                                                $res->is_std = false;
                                            }
                                        }

                                        $res->num_text = str_replace($fig->get_symbol(), $fig->number(), $res->num_text);
                                        if ($res->last_val_update < $fig->last_update()) {
                                            $res->last_val_update = $fig->last_update();
                                        }
                                        log_debug('one figure "' . $fig->number() . '" for "' . $fig->get_symbol() . '" in "' . $res->num_text . '"');
                                    } else {
                                        // if the result has been the standard result utils now
                                        if ($res_master->is_std()) {
                                            // ... and the value is user specific
                                            if (!$fig->is_std()) {
                                                // split the result into a standard
                                                // get the standard value
                                                // $fig_std = ...;
                                                $res_std = clone $res_master;
                                                $res_std->num_text = str_replace($fig->get_symbol(), $fig->number(), $res_std->num_text);
                                                if ($res_std->last_val_update < $fig->last_update()) {
                                                    $res_std->last_val_update = $fig->last_update();
                                                }
                                                log_debug('one figure "' . $fig->number() . '" for "' . $fig->get_symbol() . '" in "' . $res->num_text . '"');
                                                $res_lst->add_obj($res_std);
                                                // ... and split into a user specific part
                                                $res_master->is_std = false;
                                            }
                                        }

                                        // for all following result reuse the first result and fill with the next number
                                        $res_new = clone $res_master;
                                        $res_new->num_text = str_replace($fig->get_symbol(), $fig->number(), $res_new->num_text);
                                        if ($res->last_val_update < $fig->last_update()) {
                                            $res->last_val_update = $fig->last_update();
                                        }
                                        log_debug('one figure "' . $fig->number() . '" for "' . $fig->get_symbol() . '" in "' . $res->num_text . '"');
                                        $res_lst->add_obj($res_new);
                                    }
                                    log_debug('figure "' . $fig->number() . '" for "' . $fig->get_symbol() . '" in "' . $res->num_text . '"');
                                    $fig_nbr++;
                                }
                            }
                        }
                    }
                } else {
                    // if not figure found remember to switch off the result if needed
                    log_debug('no figures found for ' . $elm_grp->dsp_id() . ' and ' . $phr_lst->dsp_id());
                    $all_elm_grp_filled = false;
                }
            }
        }

        // if some values are not filled and all are needed, switch off the incomplete formula results
        if ($this->need_all_val) {
            log_debug('for ' . $phr_lst->dsp_id() . ' all value are needed');
            if ($all_elm_grp_filled) {
                log_debug('for ' . $phr_lst->dsp_id() . ' all value are filled');
            } else {
                log_debug('some needed values missing for ' . $phr_lst->dsp_id());
                foreach ($res_lst->lst() as $res) {
                    log_debug('some needed values missing for ' . $res->dsp_id() . ' so switch off');
                    $res->val_missing = True;
                }
            }
        }

        // calculate the final numeric results
        // TODO move to a result_list_exe class
        $lib = new library();
        if ($res_lst->lst() != null) {
            foreach ($res_lst->lst() as $res) {
                // at least the formula update should be used
                if ($res->last_val_update < $this->last_update) {
                    $res->last_val_update = $this->last_update;
                }
                // calculate only if any parameter has been updated since last calculation
                if ($res->num_text == '') {
                    log_err('num text is empty nothing needs to be done, but actually this should never happen');
                } else {
                    if ($res->last_val_update > $res->last_update()) {
                        // check if all needed value exist
                        $can_calc = false;
                        if ($this->need_all_val) {
                            log_debug('calculate ' . $this->dsp_id() . ' only if all numbers are given');
                            if ($res->val_missing) {
                                log_debug('got some numbers for ' . $this->dsp_id() . ' and ' . $lib->dsp_array($res->phr_ids()));
                            } else {
                                if ($res->is_std) {
                                    log_debug('got all numbers for ' . $this->dsp_id() . ' and ' . $res->name_linked() . ': ' . $res->num_text);
                                } else {
                                    log_debug('got all numbers for ' . $this->dsp_id() . ' and ' . $res->name_linked() . ': ' . $res->num_text . ' (user specific)');
                                }
                                $can_calc = true;
                            }
                        } else {
                            log_debug('always calculate ' . $this->dsp_id());
                            $can_calc = true;
                        }
                        if ($can_calc) {
                            log_debug('calculate ' . $res->num_text . ' for ' . $phr_lst->dsp_id());
                            $calc = new calc_internal();
                            $res->set_number($calc->parse($res->num_text));
                            $res->is_updated = true;
                            log_debug('the calculated ' . $this->dsp_id() . ' is ' . $res->number() . ' for ' . $res->grp()->phrase_list()->dsp_id());
                        }
                    }
                }
            }
        }

        return $res_lst;
    }

    // create the calculation request for one formula and one usr
    /*
    function calc_requests($phr_lst) {
    $result = array();

    $calc_request = New job;
    $calc_request->frm     = $this;
    $calc_request->usr     = $this->get_user();
    $calc_request->phr_lst = $phr_lst;
    $result[] = $calc_request;
    zu_debug('request "'.$frm->name().'" for "'.$phr_lst->name().'"');

    return $result;
    }
    */


    /**
     * calculate the result for one formula for one user
     * and save the result in the database
     * @param phrase_list $phr_lst is the context for the value retrieval, and it also contains any time words
     * the time words are only separated right before saving to the database
     * always returns an array of results
     * TODO check if calculation is really needed
     *      if one of the result words is a scaling word, remove all value scaling words
     *      always create a default result (for the user 0)
     */
    function calc(phrase_list $phr_lst): ?array
    {
        $result = null;
        $lib = new library();

        // check the parameters
        if (!isset($phr_lst)) {
            log_warning('The calculation context for ' . $this->dsp_id() . ' is empty.', 'formula->calc');
        } else {
            log_debug('->calc ' . $this->dsp_id() . ' for ' . $phr_lst->dsp_id());

            // check if an update of the result is needed
            /*
      $needs_update = true;
      if ($this->has_verb ($this->ref_text, $this->get_user()->id)) {
        $needs_update = true; // this case will be checked later
      } else {
        $frm_wrd_ids = $this->wrd_ids($this->ref_text, $this->get_user()->id());
      } */

            // reload the formula if needed, but this should be done by the calling function, so create an info message
            if ($this->name() == '' or is_null($this->name_wrd)) {
                if ($this->id() > 0) {
                    $this->load_by_id($this->id());
                    log_info('formula ' . $this->dsp_id() . ' reloaded.', 'formula->calc');
                } else {
                    log_warning('formula ' . $this->dsp_id() . ' cannot be reloaded');
                }
            }

            // build the formula expression for calculating the result
            $exp = new expression($this->get_user());
            $exp->set_ref_text($this->ref_text);

            // the phrase left of the equation sign should be added to the result
            // e.g. percent for the increase formula
            $has_result_phrases = false;
            $res_lst = new result_list($this->get_user());
            if ($exp->is_valid()) {
                $res_add_phr_lst = $exp->result_phrases();
                if (isset($res_add_phr_lst)) {
                    log_debug('use words ' . $res_add_phr_lst->dsp_id() . ' for the result');
                    $has_result_phrases = true;
                }
                // use only the part right of the equation sign for the result calculation
                $this->ref_text_r = chars::CHAR_CALC . $exp->r_part();
                log_debug('->calc got result words of ' . $this->ref_text_r);

                // get the list of the numeric results
                // $res_lst is a list of all results saved in the database
                $res_lst = $this->to_num($phr_lst);
                if (isset($res_add_phr_lst)) {
                    log_debug($lib->dsp_count($res_lst->lst()) . ' formula results to save');
                }
            }

            // save the numeric results
            if ($res_lst->lst() != null) {
                foreach ($res_lst->lst() as $res) {
                    if ($res->val_missing) {
                        // check if res needs to be removed from the database
                        log_debug('some values missing for ' . $res->dsp_id());
                    } else {
                        if ($res->is_updated) {
                            log_debug('formula result ' . $res->dsp_id() . ' is updated');

                            // make common assumptions on the word list

                            // apply general rules to the result words
                            if (isset($res_add_phr_lst)) {

                                // add the phrases left of the equal sign to the result e.g. percent for the increase formula
                                log_debug('result words "' . $res_add_phr_lst->dsp_id() . '" defined for ' . $res->grp()->dsp_id());
                                $res_add_wrd_lst = $res_add_phr_lst->wrd_lst_all();

                                // if the result words contains "percent" remove any measure word from the list, because a relative value is expected without measure
                                if ($res_add_wrd_lst->has_percent()) {
                                    log_debug('has percent');
                                    $res->grp()->phrase_list()->ex_measure();
                                    log_debug('measure words removed from ' . $res->grp()->phrase_list()->dsp_id());
                                }

                                // if in the formula is defined, that the result is in percent
                                // and the values used are in millions, the result is only in percent, but not in millions
                                // TODO check that all value have the same scaling and adjust the scaling if needed
                                if ($res_add_wrd_lst->has_percent()) {
                                    $res->grp()->phrase_list()->ex_scaling();
                                    log_debug('scaling words removed from ' . $res->grp()->phrase_list()->dsp_id());
                                    // maybe add the scaling word to the result words to remember based on which words the result has been created,
                                    // but probably this is not needed, because the source words are also saved
                                    //$scale_wrd_lst = $res_add_wrd_lst->scaling_lst ();
                                    //$res->grp()->phrase_list()->merge($scale_wrd_lst->lst);
                                    //zu_debug(self::class . '->calc -> added the scaling word '.implode(",",$scale_wrd_lst->names()).' to the result words "'.implode(",",$res->grp()->phrase_list()->names()).'"');
                                }

                                // if the formula is a scaling formula, remove the obsolete scaling word from the source words
                                if ($res_add_wrd_lst->has_scaling()) {
                                    $res->grp()->phrase_list()->ex_scaling();
                                    log_debug('scaling words removed from ' . $res->grp()->phrase_list()->dsp_id());
                                }

                            }

                            // add the formula result word
                            // e.g. in the increase formula "percent" should be on the left side of the equation because the result is supposed to be in percent
                            if (isset($res_add_phr_lst)) {
                                log_debug('add words ' . $res_add_phr_lst->dsp_id() . ' to the result');
                                foreach ($res_add_phr_lst->lst() as $frm_result_wrd) {
                                    $res->grp()->phrase_list()->add($frm_result_wrd);
                                }
                                log_debug('added words ' . $res_add_phr_lst->dsp_id() . ' to the result ' . $res->grp()->phrase_list()->dsp_id());
                            }

                            // add the formula name also to the result phrase e.g. increase
                            if (is_null($this->name_wrd)) {
                                $this->reload_wrd();
                            }
                            if (is_null($this->name_wrd)) {
                                log_warning('Cannot load word for formula ' . $this->dsp_id());
                            } else {
                                $res->grp()->phrase_list()->add($this->name_wrd->phrase());
                            }

                            $res = $res->save_if_updated($has_result_phrases);

                        }
                    }
                }
            }


            $result = $res_lst->lst();
        }

        log_debug('done');
        return $result;
    }

    /**
     * calculate the formula results based on a given figure list
     *
     * @param figure_list $fig_lst the value and results that should be used for the calculation
     * @return figure_list the received figure list with the additions formula results
     */
    function calc_with(figure_list $fig_lst): figure_list
    {
        return $fig_lst;
    }

    /**
     * @param term_list|null $trm_lst a list of preloaded terms that should be used for the transformation
     * @return expression the formula expression as an expression element
     */
    function expression(?term_list $trm_lst = null): expression
    {
        $exp = new expression($this->get_user());
        $exp->set_ref_text($this->ref_text, $trm_lst);
        $exp->set_user_text($this->usr_text, $trm_lst);
        log_debug('->expression ' . $exp->ref_text() . ' for user ' . $exp->usr->name);
        return $exp;
    }

    /**
     * @return result_list a list of all formula results linked to this formula
     */
    function get_res_lst(): result_list
    {
        $res_lst = new result_list($this->get_user());
        $res_lst->load_by_frm($this);
        return $res_lst;
    }




    /*
     * expression
     * TODO probably to be replaced with expression functions
     */

    /**
     * @param string $formula the formula expression in the reference format
     * @param string $start_maker
     * @param string $end_maker
     * @return int a positive term object (e.g. word, triple, verb, or formula) id
     *             if the formula string in the database format contains a link
     */
    private function get_term_id(string $formula, string $start_maker, string $end_maker): int
    {
        $lib = new library();

        $result = 0;
        $pos_start = strpos($formula, $start_maker);
        if ($pos_start !== false) {
            $r_part = $lib->str_right_of($formula, $start_maker);
            $l_part = $lib->str_left_of($r_part, $end_maker);
            if (is_numeric($l_part)) {
                $result = $l_part;
                log_debug($result);
            }
        }

        return $result;
    }

    /**
     * get all terms used in this formula
     * including the phrases that should be added to the result
     * @param term_list $cache with the terms already loaded
     * @return term_list list of all terms used in the formula expression
     */
    function term_list(term_list $cache): term_list
    {
        $trm_lst = new term_list($this->get_user());
        $exp = $this->expression($cache);
        $elm_lst = $exp->element_list($cache);
        foreach ($elm_lst->lst() as $elm) {
            $trm_lst->add($elm->term());
        }
        $res_phr_lst = $exp->result_phrases($cache);
        return $trm_lst->merge($res_phr_lst->term_list());
    }

    /**
     * @param string $frm_text the formula expression in the reference format
     * @param string $start_maker
     * @param string $end_maker
     * @return array with one type of term ids from a given formula text
     */
    private function trm_ids(string $frm_text, string $start_maker, string $end_maker): array
    {
        $result = array();

        $lib = new library();

        // add term id to selection
        $new_trm_id = $this->get_term_id($frm_text, $start_maker, $end_maker);
        while ($new_trm_id > 0) {
            if (!in_array($new_trm_id, $result)) {
                $result[] = $new_trm_id;
            }
            $frm_text = $lib->str_right_of($frm_text, $start_maker . $new_trm_id . $end_maker);
            $new_trm_id = $this->get_term_id($frm_text, $start_maker, $end_maker);
        }

        log_debug($lib->dsp_array($result));
        return $result;
    }

    /**
     * @param string $frm_text the formula expression in the reference format
     * @return array with the word ids from a given formula text
     */
    function wrd_ids(string $frm_text): array
    {
        return $this->trm_ids($frm_text, chars::WORD_START, chars::WORD_END);
    }

    /**
     * @param string $frm_text the formula expression in the reference format
     * @return array with the word ids from a given formula text
     */
    function trp_ids(string $frm_text): array
    {
        return $this->trm_ids($frm_text, chars::TRIPLE_START, chars::TRIPLE_END);
    }

    /**
     * @param string $frm_text the formula expression in the reference format
     * @return array with the word ids from a given formula text
     */
    function vrb_ids(string $frm_text): array
    {
        return $this->trm_ids($frm_text, chars::VERB_START, chars::VERB_END);
    }

    /**
     * @param string $frm_text the formula expression in the reference format
     * @return array with the formula ids from a given formula text
     */
    function frm_ids(string $frm_text): array
    {
        return $this->trm_ids($frm_text, chars::FORMULA_START, chars::FORMULA_END);
    }

    /**
     * update formula links
     * part of element_refresh for one element type and one user
     * TODO move this to the formula element list object
     */
    function element_refresh_type(string $frm_text, $element_type, $frm_usr_id, $db_usr_id): bool
    {
        log_debug('->element_refresh_type (f' . $this->id() . $frm_text . ',' . $element_type . ',u' . $frm_usr_id . ')');

        global $db_con;
        $result = true;

        // read the elements from the formula text
        $elm_type_id = $element_type;
        switch ($element_type) {
            case parameter_type::TRIPLE_ID:
                $elm_ids = $this->trp_ids($frm_text);
                break;
            case parameter_type::VERB_ID:
                $elm_ids = $this->vrb_ids($frm_text);
                break;
            case parameter_type::FORMULA_ID:
                $elm_ids = $this->frm_ids($frm_text);
                break;
            default:
                $elm_ids = $this->wrd_ids($frm_text);
                break;
        }
        $lib = new library();
        log_debug('got (' . $lib->dsp_array($elm_ids) . ') of type ' . $element_type . ' from text');

        // read the existing elements from the database
        $frm_elm_lst = new element_list($this->get_user());
        $qp = $frm_elm_lst->load_sql_by_frm_and_type_id($db_con->sql_creator(), $this->id(), $elm_type_id);
        $db_lst = $db_con->get($qp);

        $elm_db_ids = array();
        if ($db_lst != null) {
            foreach ($db_lst as $db_row) {
                $elm_db_ids[] = $db_row['ref_id'];
            }
        }
        $lib = new library();
        log_debug('got (' . $lib->dsp_array($elm_db_ids) . ') of type ' . $element_type . ' from database');

        // add missing links
        $elm_add_ids = array_diff($elm_ids, $elm_db_ids);
        $elm_order_nbr = 1;
        $lib = new library();
        log_debug('add ' . $element_type . ' (' . $lib->dsp_array($elm_add_ids) . ')');
        // TODO use element list object
        foreach ($elm_add_ids as $elm_add_id) {
            $field_names = array();
            $field_values = array();
            $field_names[] = formula_db::FLD_ID;
            $field_values[] = $this->id();
            $field_names[] = user_db::FLD_ID;
            if ($frm_usr_id > 0) {
                $field_values[] = $frm_usr_id;
            } else {
                $field_values[] = $this->get_user()->id();
            }
            $field_names[] = element::FLD_TYPE;
            $field_values[] = $elm_type_id;
            $field_names[] = element::FLD_REF_ID;
            $field_values[] = $elm_add_id;
            $field_names[] = element::FLD_ORDER;
            $field_values[] = $elm_order_nbr;
            $db_con->set_class(element::class);
            $add_result = $db_con->insert_old($field_names, $field_values);
            // in this case the row id is not needed, but for testing the number of action should be indicated by adding a '1' to the result string
            //if ($add_result > 0) {
            //    $result .= '1';
            //}
            $elm_order_nbr++;
        }

        // delete links not needed any more
        $elm_del_ids = array_diff($elm_db_ids, $elm_ids);
        $lib = new library();
        log_debug('del ' . $element_type . ' (' . $lib->dsp_array($elm_del_ids) . ')');
        foreach ($elm_del_ids as $elm_del_id) {
            $field_names = array();
            $field_values = array();
            $field_names[] = formula_db::FLD_ID;
            $field_values[] = $this->id();
            if ($frm_usr_id > 0) {
                $field_names[] = user_db::FLD_ID;
                $field_values[] = $frm_usr_id;
            }
            $field_names[] = element::FLD_TYPE;
            $field_values[] = $elm_type_id;
            $field_names[] = element::FLD_REF_ID;
            $field_values[] = $elm_del_id;
            $db_con->set_class(element::class);
            $del_result = $db_con->delete_old($field_names, $field_values);
            if ($del_result != '') {
                $result = false;
            }
        }

        log_debug($lib->dsp_bool($result));
        return $result;
    }

    /**
     * update the database references to the formula elements
     * to be able to use the sql statements to find all formulas depending on a word. triple, verb or formula
     * TODO create one SQL statement for the update that is executed with one commit statement
     * @param string $frm_text the reference text that should be used for the update
     * @return bool true if the update has been fine
     */
    function element_refresh(string $frm_text): bool
    {
        log_debug('->element_refresh (f' . $this->id() . $frm_text . ',u' . $this->get_user()->id() . ')');

        global $db_con;
        $result = true;

        // refresh the links for the standard formula used if the user has not changed the formula
        $result = $this->element_refresh_type($frm_text, parameter_type::WORD_ID, 0, $this->get_user()->id);

        // update triple links of the standard formula
        if ($result) {
            $result = $this->element_refresh_type($frm_text, parameter_type::TRIPLE_ID, 0, $this->get_user()->id);
        }

        // update verb links of the standard formula
        if ($result) {
            $result = $this->element_refresh_type($frm_text, parameter_type::VERB_ID, 0, $this->get_user()->id);
        }

        // update formula links of the standard formula
        if ($result) {
            $result = $this->element_refresh_type($frm_text, parameter_type::FORMULA_ID, 0, $this->get_user()->id);
        }

        // refresh the links for the user specific formula
        $qp = $this->load_sql_user_changes_frm($db_con);
        $db_lst = $db_con->get($qp);
        if ($db_lst != null) {
            foreach ($db_lst as $db_row) {
                // update word links of the user formula
                if ($result) {
                    $result = $this->element_refresh_type($frm_text, parameter_type::WORD_ID, $db_row[user_db::FLD_ID], $this->get_user()->id);
                }
                // update triple links of the user formula
                if ($result) {
                    $result = $this->element_refresh_type($frm_text, parameter_type::TRIPLE_ID, $db_row[user_db::FLD_ID], $this->get_user()->id);
                }
                // update verb links of the user formula
                if ($result) {
                    $result = $this->element_refresh_type($frm_text, parameter_type::VERB_ID, $db_row[user_db::FLD_ID], $this->get_user()->id);
                }
                // update formula links of the standard formula
                if ($result) {
                    $result = $this->element_refresh_type($frm_text, parameter_type::FORMULA_ID, $db_row[user_db::FLD_ID], $this->get_user()->id);
                }
            }
        }

        log_debug('done' . $result);
        return $result;
    }




    /*
     * save
     */

    /**
     * update the database reference text based on the user text
     * TODO check in not the left AND the right part needs to be transformed as expression
     * TODO Prio 1 return a user message instead of a string
     *
     * @param term_list|null $trm_lst a list of preloaded terms that should be used for the transformation
     * @param user_message $usr_msg to enrich with problems and suggested solution
     * @return bool true if the update of the reference text was successful and otherwise the error message is added to the user_message object
     */
    function generate_ref_text(
        ?term_list   $trm_lst = null,
        user_message $usr_msg = new user_message()
    ): bool
    {
        if ($this->usr_text != null) {
            if ($this->ref_text == '' or !$this->ref_text_dirty) {
                $exp = new expression($this->get_user());
                $exp->set_user_text($this->usr_text, $trm_lst);
                $this->ref_text = $exp->ref_text($trm_lst, $usr_msg);
                if ($usr_msg->is_ok()) {
                    $this->ref_text_dirty = false;
                }
            }
        }
        return $usr_msg->is_ok();
    }

    /**
     * update the user text based on the database reference text
     *
     * @param term_list|null $trm_lst a list of preloaded terms that should be used for the transformation
     * @return string which is empty if the update of the reference text was successful and otherwise the error message that should be shown to the user
     */
    function generate_usr_text(?term_list $trm_lst = null): string
    {
        $result = '';
        $exp = new expression($this->get_user());
        $exp->set_user_text($this->usr_text);
        $this->ref_text = $exp->ref_text($trm_lst);
        $this->ref_text_dirty = false;
        return $result;
    }

}