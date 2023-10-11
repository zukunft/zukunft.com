<?php

/*

  model/formula/formula_element_group.php - a group of formula elements that, in combination, return a value or a list of values
  ---------------------------------------
  
  e.g. for for "ABB", "differentiator" and "Sector" (or "Sectors" "of" "ABB")
       a list of all sector values is returned
  or in other words for each element group a where clause for value retrieval is created
  
  phrases are always used to select the smallest set of value (in SQL by using "AND" in the where clause)
  e.g. "ABB" "Sales" excludes the values for "ABB income tax" and "Danone Sales"
  
  verbs are always used to add a set of values
  e.g. "ABB", "Sales", "differentiator" and "Sector" will return a list of Sector sales for ABB
       so the SQL statement would be "... WHERE ("ABB" AND "Sales" AND "Sector1") OR ("ABB" AND "Sales" AND "Sector2") OR ....
  
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

namespace cfg;

include_once MODEL_FORMULA_PATH . 'figure_list.php';

use html\figure\figure as figure_dsp;
use html\result\result as result_dsp;
use test\test_api;

class formula_element_group
{

    public ?array $lst = null;           // array of formula elements such as a word, verb or formula
    public ?phrase_list $phr_lst = null; // phrase list object with the context to retrieve the element number
    public ?user $usr = null;            // the results can differ for each user; this is the user who wants to see the result

    public ?string $symbol = null; // the formula reference text for this element group; used to fill in the numbers into the formula


    /*
     * display
     */

    /**
     * show the element group name to the user in the most simple form (without any ids)
     */
    function name(): string
    {
        $lib = new library();
        return $lib->dsp_array($this->names());
    }

    // TODO handle multi entry cases if needed
    function id(): int
    {
        if (count($this->lst) == 1) {
            return $this->lst[0]->obj->id();
        } else {
            return 0;
        }
    }

    // recreate the element group symbol based on the element list ($this->lst)
    function build_symbol(): string
    {
        $this->symbol = '';

        foreach ($this->lst as $elm) {
            // build the symbol for the number replacement
            if ($this->symbol == '') {
                if ($elm->symbol != null) {
                    $this->symbol = $elm->symbol;
                }
            } else {
                if ($elm->symbol != null) {
                    $this->symbol .= ' ' . $elm->symbol;
                }
            }
            log_debug('symbol "' . $elm->symbol . '" added to "' . $this->symbol . '"');
        }

        return $this->symbol;
    }

    /**
     * list of the formula element names independent of the element type
     */
    function dsp_names(string $back = ''): string
    {
        $result = '';

        foreach ($this->lst as $frm_elm) {
            // display the formula element name
            $result .= $frm_elm->name_linked($back) . ' ';
        }

        return $result;
    }

    /**
     * set the time phrase based on a predefined formula such as "prior" or "next"
     * e.g. if the predefined formula "prior" is used and the time is 2017 than 2016 should be used
     */
    private function set_formula_time_phrase(formula_element $frm_elm, phrase_list $val_phr_lst): ?phrase
    {
        log_debug('for ' . $frm_elm->dsp_id() . ' and ' . $val_phr_lst->dsp_id());

        $val_time_phr = new phrase($this->usr);

        // guess the time word if needed
        log_debug('assume time for ' . $val_phr_lst->dsp_id());
        $val_time_phr = $val_phr_lst->assume_time();

        // adjust the element time word if forced by the special formula
        if (isset($val_time_phr)) {
            if ($val_time_phr->id() == 0) {
                // switched off because it is not working for "this"
                log_err('No time found for "' . $frm_elm->obj->name . '".', 'formula_element_group->figures');
            } else {
                log_debug('get predefined time result');
                if (isset($frm_elm->obj)) {
                    $val_time = $frm_elm->obj->special_time_phr($val_time_phr);
                    if ($val_time->id() > 0) {
                        $val_time_phr = $val_time;
                        if ($val_time_phr->id() == 0) {
                            $val_time_phr->load_by_name($val_time_phr->name());
                        }
                        if ($val_time_phr->name() == '') {
                            $val_time_phr->load_by_id($val_time_phr->id());
                        }
                        log_debug('add element word for special formula result ' . $val_phr_lst->dsp_id() . ' taken from the result');
                    }
                }
            }
        }
        if (isset($val_time_phr)) {
            // before adding a special time word, remove all other time words from the word list
            $val_phr_lst->ex_time();
            $val_phr_lst->add($val_time_phr);
            $this->phr_lst = $val_phr_lst;
            log_debug('got the special formula word "' . $val_time_phr->name() . '" (' . $val_time_phr->id() . ')');
        }

        if (isset($val_time_phr)) {
            log_debug('got ' . $val_time_phr->dsp_id());
        }

        return $val_time_phr;
    }


    /**
     *  get a list of figures related to the formula element group and a context defined by a list of words
     *    e.g. 1 for the formula elements <"this"> and the context <"Switzerland" "inhabitants">
     *      the latest number of Swiss inhabitants should be returned
     *    e.g. 2 for the formula elements <"journey time max premium" "percent"> and the context <"Zurich" "land lot" "minutes">
     *      the result for <"journey time max premium" "percent" "Zurich" "land lot"> should be returned
     *      and if no value is found, the next best match should be returned
     *    e.g. 3 for the formula element <"Share price"> and the context <"Nestlé">
     *      the result for <"Share price" "Nestlé" "2016" "CHF"> should be returned
     *      if the last share price is from 2016 and CHF is the most important (used) currency
     *
     * @param term_list|null $trm_lst a list of preloaded terms that should be used for the transformation
     * @return figure_list
     */
    function figures(?term_list $trm_lst = null): figure_list
    {
        log_debug('figures ' . $this->dsp_id());
        $lib = new library();

        // init the resulting figure list
        $fig_lst = new figure_list($this->usr);

        // add the words of the formula element group to the value selection
        // e.g. 1: for the formula "this" and the phrases "Switzerland" and "inhabitants" the Swiss inhabitants are requested
        // e.g. for the formula "= sales - cost" and the phrases "ABB" the ABB sales is requested
        foreach ($this->lst as $frm_elm) {

            $lead_wrd_id = 0;

            // init the word list for the figure selection because
            // the word list for the figure selection ($val_phr_lst) may differ from the requesting word list ($this->phr_lst) because
            // e.g. 1: $val_phr_lst is Swiss inhabitants
            // e.g. if "percent" is requested and a measure word is part of the request, the measure words are ignored
            $val_phr_lst = clone $this->phr_lst;
            $val_time_phr = $val_phr_lst->assume_time($trm_lst);
            if (isset($val_time_phr)) {
                log_debug('for time ' . $val_time_phr->dsp_id());
            }

            // build the symbol for the number replacement before adding the formula elements
            // e.g. 1: {f18}
            if ($this->symbol == '') {
                $this->build_symbol();
            }

            log_debug('use element ' . $frm_elm->dsp_id() . ' also for value selection');

            // get the element word to be able to add it later to the value selection (differs for the element type)
            if ($frm_elm->type == word::class) {
                if ($frm_elm->id() > 0) {
                    $val_phr_lst->add($frm_elm->obj->phrase());
                    log_debug('include ' . $frm_elm->dsp_id() . ' in value selection');
                }
            }

            // get the formula related word to be able to add it later to the value selection (differs for the element type)
            // e.g. 1: setting the $val_time_phr to 2020
            if ($frm_elm->type == formula::class) {
                // at the moment the special formulas only change the time word, this is why val_wrd_id is not set here
                if ($frm_elm->obj->is_special()) {
                    $val_time_phr = $this->set_formula_time_phrase($frm_elm, $val_phr_lst);
                    if (isset($val_time_phr)) {
                        log_debug('adjusted time ' . $val_time_phr->dsp_id());
                    }
                } else {
                    if ($frm_elm->wrd_id > 0) {
                        $val_phr_lst->add($frm_elm->wrd_obj->phrase());
                    }
                    log_debug('include formula word "' . $frm_elm->wrd_obj->name . '" (' . $frm_elm->wrd_id . ')');
                }
            }

            // get the word group
            $val_phr_lst_sort = $val_phr_lst->lst();
            usort($val_phr_lst_sort, array(phrase::class, "cmp"));
            $val_phr_lst->set_lst($val_phr_lst_sort);

            //asort($val_phr_lst);
            $val_phr_grp = $val_phr_lst->get_grp_id();
            log_debug('words group for "' . $val_phr_lst->dsp_name() . '" = ' . $val_phr_grp->id());

            // try to get a normal value set by the user directly for the phrase list
            // display the word group value and offer the user to change it
            // e.g. if the user has overwritten a result use the user overwrite
            log_debug('load word value for ' . $val_phr_lst->dsp_id());
            $wrd_val = new value($this->usr);
            // TODO create $wrd_val->load_best();
            $wrd_val->load_by_grp($val_phr_grp);

            if ($wrd_val->isset()) {
                // save the value to the result
                $fig = $wrd_val->figure();
                $fig->set_symbol($frm_elm->symbol);
                $fig_lst->add($fig);
                log_debug('value result for ' . $val_phr_lst->dsp_id() . ' = ' . $wrd_val->number() . ' (symbol ' . $fig->symbol() . ')');
            } else {
                // if there is no number that the user has entered for the word list, try to get the most useful formula result

                // temp solution only for the link
                if ($lead_wrd_id <= 0) {
                    $lead_wrd = $val_phr_lst->lst()[0];
                    $lead_wrd_id = 1;
                }

                // get the word group result, which means a formula result
                log_debug('load result for ' . $val_phr_lst->dsp_name());
                $grp_res = new result($this->usr);
                /*
                $grp_res->phr_grp_id = $val_phr_grp->id;
                if ($val_time_phr != null) {
                    $grp_res->time_phr = $val_time_phr;
                }
                $grp_res->load_obj_vars();
                */
                if ($val_time_phr == null) {
                    $time_id = null;
                } else {
                    $time_id = $val_time_phr->id();
                }
                $grp_res->load_by_grp($val_phr_grp, $time_id);

                // save the value to the result
                if ($grp_res->id() > 0) {
                    $fig = $grp_res->figure();
                    $fig->set_symbol($this->symbol);
                    $fig_lst->add($fig);

                    log_debug('result for ' . $val_phr_lst->dsp_name() . ', time ' . $val_time_phr->name() . '" (word group ' . $val_phr_grp->id() . ', user ' . $this->usr->id() . ') = ' . $grp_res->value);
                } else {
                    // if there is also not a formula result at least one number of the formula is not valid
                    $fig_lst->fig_missing = True;
                    log_debug('figure missing');
                }
            }
        }

        log_debug($lib->dsp_count($fig_lst->lst()) . ' found');
        return $fig_lst;
    }

    /**
     * the HTML code to display a figure list
     */
    function dsp_values(string $back = ''): string
    {
        log_debug();

        $result = '';

        $fig_lst = $this->figures();
        log_debug('got figures');

        // show the time if adjusted by a special formula element
        // build the html code to display the value with the link
        foreach ($fig_lst->lst() as $fig) {
            log_debug('display figure');
            $t = new test_api();
            $fig_dsp = $t->dsp_obj($fig, new figure_dsp());
            $result .= $fig_dsp->display_linked($back);
        }

        // TODO: show the time phrase only if it differs from the main time phrase

        // display alternative values


        log_debug('result "' . $result . '"');
        return $result;
    }


    /*
     * debug
     */

    /**
     * @return string with the unique id fields
     */
    function dsp_id(): string
    {
        $lib = new library();
        $id = $lib->dsp_array($this->ids());
        $name = $lib->dsp_array($this->names());
        $phr_name = '';
        if (isset($this->phr_lst)) {
            $phr_name = $this->phr_lst->dsp_name();
        }
        if ($name <> '') {
            $result = '"' . $name . '" (' . $id . ')';
        } else {
            $result = 'id (' . $id . ')';
        }
        if ($phr_name <> '') {
            $result .= ' and ' . $phr_name;
        }

        return $result;
    }

    private function ids(): array
    {
        $result = array();
        if (isset($this->lst)) {
            foreach ($this->lst as $frm_elm) {
                // use only valid ids
                if ($frm_elm->id() <> 0) {
                    $result[] = $frm_elm->id();
                } else {
                    if ($frm_elm->obj != null) {
                        if ($frm_elm->obj->id() <> 0) {
                            $result[] = $frm_elm->obj->id();
                        }
                    }
                }
            }
        }
        return $result;
    }

    /**
     * list of the formula element names independent of the element type
     * this function is called from dsp_id, so no other call is allowed
     */
    private function names(): array
    {
        $result = array();

        foreach ($this->lst as $frm_elm) {
            // display the formula element name
            $result[] .= $frm_elm->name();
        }

        return $result;
    }

}