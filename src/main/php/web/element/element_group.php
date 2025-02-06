<?php

/*

    web/element/element_group.php - a group of formula elements that, in combination, return a value or a list of values
    -----------------------------


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

namespace html\element;

include_once WEB_PHRASE_PATH . 'phrase_list.php';
include_once WEB_FORMULA_PATH . 'formula.php';
include_once WEB_FIGURE_PATH . 'figure.php';
include_once WEB_FIGURE_PATH . 'figure_list.php';
include_once WEB_PHRASE_PATH . 'phrase.php';
include_once WEB_PHRASE_PATH . 'phrase_list.php';
include_once WEB_PHRASE_PATH . 'term_list.php';
include_once WEB_RESULT_PATH . 'result.php';
include_once WEB_SANDBOX_PATH . 'list_dsp.php';
include_once WEB_USER_PATH . 'user_message.php';
include_once WEB_VALUE_PATH . 'value.php';
include_once WEB_WORD_PATH . 'word.php';
include_once SHARED_TYPES_PATH . 'api_type.php';
include_once SHARED_PATH . 'library.php';

use html\figure\figure as figure;
use html\figure\figure_list;
use html\formula\formula;
use html\phrase\phrase;
use html\phrase\phrase_list;
use html\phrase\term_list;
use html\result\result;
use html\sandbox\list_dsp;
use html\user\user_message;
use html\value\value;
use html\word\word;
use shared\library;
use shared\types\api_type;

class element_group extends list_dsp
{

    // $lst is here an array of formula elements such as a word, verb or formula
    public ?phrase_list $phr_lst = null; // phrase list object with the context to retrieve the element number

    public ?string $symbol = null; // the formula reference text for this element group; used to fill in the numbers into the formula


    /**
     * set the vars of this element_group list based on the given json
     * @param array $json_array an api single object json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function set_from_json_array(array $json_array): user_message
    {
        return parent::set_list_from_json($json_array, new element());
    }

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
        if (count($this->lst()) == 1) {
            return $this->lst()[0]->obj->id();
        } else {
            return 0;
        }
    }

    /**
     * list of the formula element names independent of the element type
     * this function is called from dsp_id, so no other call is allowed
     */
    private function names(): array
    {
        $result = array();

        foreach ($this->lst() as $frm_elm) {
            // display the formula element name
            $result[] .= $frm_elm->name();
        }

        return $result;
    }

    /**
     * list of the formula element names independent of the element type
     */
    function dsp_names(string $back = ''): string
    {
        $result = '';

        foreach ($this->lst() as $frm_elm) {
            // display the formula element name
            $result .= $frm_elm->link($back) . ' ';
        }

        return $result;
    }


    /**
     * the HTML code to display a figure list
     */
    function dsp_values(string $back = ''): string
    {
        $result = '';

        $fig_lst = $this->figures();
        log_debug('got figures');

        // show the time if adjusted by a special formula element
        // build the html code to display the value with the link
        foreach ($fig_lst->lst() as $fig) {
            log_debug('display figure');
            $api_json = $fig->api_json([api_type::INCL_PHRASES]);
            $fig_dsp = new figure();
            $fig_dsp->set_from_json($api_json);
            $result .= $fig_dsp->display_linked($back);
        }

        // TODO: show the time phrase only if it differs from the main time phrase

        // display alternative values

        return $result;
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
        $lib = new library();

        // init the resulting figure list
        $fig_lst = new figure_list();

        // add the words of the formula element group to the value selection
        // e.g. 1: for the formula "this" and the phrases "Switzerland" and "inhabitants" the Swiss inhabitants are requested
        // e.g. for the formula "= sales - cost" and the phrases "ABB" the ABB sales is requested
        foreach ($this->lst() as $frm_elm) {

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
            $wrd_val = new value();
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
                $grp_res = new result();
                /* TODO review
                $grp_res->load_by_grp($val_phr_grp);
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

                    log_debug('result for ' . $val_phr_lst->dsp_name() . ', time ' . $val_time_phr->name() . '" (word group ' . $val_phr_grp->id() . ') = ' . $grp_res->number());
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
}