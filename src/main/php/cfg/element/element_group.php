<?php

/*

    model/element/element_group.php - a group of formula elements that, in combination, return a value or a list of values
    -----------------------------

    e.g. for for "ABB", "differentiator" and "sector" (or "sectors" "of" "ABB")
       a list of all sector values is returned
    or in other words for each element group a where clause for value retrieval is created

    phrases are always used to select the smallest set of value (in SQL by using "AND" in the where clause)
    e.g. "ABB" "sales" excludes the values for "ABB income tax" and "Danone sales"

    verbs are always used to add a set of values
    e.g. "ABB", "sales", "differentiator" and "sector" will return a list of sector sales for ABB
       so the SQL statement would be "... WHERE ("ABB" AND "sales" AND "sector1") OR ("ABB" AND "sales" AND "sector2") OR ....

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

namespace Zukunft\ZukunftCom\main\php\cfg\element;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_FORMULA . 'figure_list.php';
include_once paths::MODEL_FORMULA . 'formula.php';
include_once paths::MODEL_FORMULA . 'formula_type.php';
include_once paths::MODEL_PHRASE . 'phrase.php';
include_once paths::MODEL_PHRASE . 'phrase_list.php';
include_once paths::MODEL_PHRASE . 'term_list.php';
include_once paths::MODEL_RESULT . 'result.php';
include_once paths::MODEL_SYSTEM . 'list_db_write.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::MODEL_VALUE . 'value.php';
include_once paths::MODEL_WORD . 'triple_list.php';
include_once paths::MODEL_WORD . 'word.php';
include_once paths::SHARED_CALC . 'prior_operand.php';
include_once paths::SHARED_ENUM . 'foaf_direction.php';
include_once paths::SHARED_TYPES . 'verbs.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'library.php';

use Zukunft\ZukunftCom\main\php\cfg\formula\figure_list;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\cfg\result\result;
use Zukunft\ZukunftCom\main\php\cfg\phrase\term_list;
use Zukunft\ZukunftCom\main\php\cfg\system\list_db_write;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\value\value;
use Zukunft\ZukunftCom\main\php\cfg\word\triple_list;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_type;
use Zukunft\ZukunftCom\main\php\shared\calc\prior_operand;
use Zukunft\ZukunftCom\main\php\shared\enum\foaf_direction;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;

class element_group extends list_db_write
{

    public ?phrase_list $phr_lst = null; // phrase list object with the context to retrieve the element number
    public ?user $usr = null;            // the results can differ for each user; this is the user who wants to see the result

    public ?string $symbol = null; // the formula reference text for this element group; used to fill in the numbers into the formula


    /**
     * create an array for the api json message
     *
     * @param api_type_list|array $typ_lst configuration for the api message e.g. if phrases should be included
     * @param user_message $msg to collect the mapping problems for the requesting user
     * @param user|null $usr the user for whom the api message should be created which can differ from the session user
     * @returns array with the json fields to create an api message
     */
    function api_json_array(api_type_list|array $typ_lst, user_message $msg, user|null $usr = null): array
    {
        $vars = [];
        if (is_array($typ_lst)) {
            $typ_lst = new api_type_list($typ_lst);
        }
        $vars[json_fields::LIST_ELEMENTS] = parent::api_json_array($typ_lst, $msg, $usr);
        if ($this->phr_lst != null) {
            $vars[json_fields::PHRASES] = $this->phr_lst->api_json_array($typ_lst, $msg, $usr);
        }
        return $vars;
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

    // TODO Prio 2 handle multi entry cases if needed
    function id(): int
    {
        if (count($this->lst()) == 1) {
            return $this->lst()[0]->obj->id();
        } else {
            return 0;
        }
    }

    // recreate the element group symbol based on the element list ($this->lst)
    function build_symbol(): string
    {
        $this->symbol = '';

        foreach ($this->lst() as $elm) {
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
     * set the time phrase based on a predefined formula such as "prior" or "next"
     * e.g. if the predefined formula "prior" is used and the time is 2017 than 2016 should be used
     */
    private function set_formula_time_phrase(element $frm_elm, phrase_list $val_phr_lst, user_message $msg): ?phrase
    {
        log_debug('for ' . $frm_elm->dsp_id() . ' and ' . $val_phr_lst->dsp_id());

        $val_time_phr = new phrase($this->usr);

        // guess the time word if needed
        log_debug('assume time for ' . $val_phr_lst->dsp_id());
        $val_time_phr = $val_phr_lst->assume_time($msg);

        // adjust the element time word if forced by the special formula
        if (isset($val_time_phr)) {
            if ($val_time_phr->id() == 0) {
                // switched off because it is not working for "this"
                log_err('No time found for "' . $frm_elm->obj->name . '".', 'element_group->figures');
            } else {
                log_debug('get predefined time result');
                if (isset($frm_elm->obj)) {
                    $val_time = $frm_elm->obj->special_time_phr($val_time_phr, $msg);
                    if ($val_time->id() > 0) {
                        $val_time_phr = $val_time;
                        if ($val_time_phr->id() == 0) {
                            $val_time_phr->load_by_name($val_time_phr->name(), $msg);
                        }
                        if ($val_time_phr->name() == '') {
                            $val_time_phr->load_by_id($val_time_phr->id(), $msg);
                        }
                        log_debug('add element word for special formula result ' . $val_phr_lst->dsp_id() . ' taken from the result');
                    }
                }
            }
        }
        if (isset($val_time_phr)) {
            // before adding a special time word, remove all other time words from the word list
            $val_phr_lst->ex_time($msg);
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
    function figures(user_message $msg, ?term_list $trm_lst = null): figure_list
    {
        log_debug('figures ' . $this->dsp_id());
        $lib = new library();

        if ($this->reads_prior_row()) {
            if ($this->symbol == '') {
                $this->build_symbol();
            }
            return $this->prior_row_figures($msg);
        }

        // init the resulting figure list
        $fig_lst = new figure_list($this->usr);

        // add the words of the formula element group to the value selection
        // e.g. 1: for the formula "this" and the phrases "Switzerland" and "inhabitants" the Swiss inhabitants are requested
        // e.g. for the formula "= sales - cost" and the phrases "ABB" the ABB sales is requested
        foreach ($this->lst() as $frm_elm) {

            // init the word list for the figure selection because
            // the word list for the figure selection ($val_phr_lst) may differ from the requesting word list ($this->phr_lst) because
            // e.g. 1: $val_phr_lst is Swiss inhabitants
            // e.g. if "percent" is requested and a measure word is part of the request, the measure words are ignored
            $val_phr_lst = clone $this->phr_lst;
            $val_time_phr = null;

            // build the symbol for the number replacement before adding the formula elements
            // e.g. 1: {f18}
            if ($this->symbol == '') {
                $this->build_symbol();
            }

            log_debug('use element ' . $frm_elm->dsp_id() . ' also for value selection');

            // get the element word to be able to add it later to the value selection (differs for the element type)
            if ($frm_elm->type() == word::class) {
                if ($frm_elm->id() > 0) {
                    $val_phr_lst->add($frm_elm->obj->phrase());
                    log_debug('include ' . $frm_elm->dsp_id() . ' in value selection');
                }
            }

            // get the formula related word to be able to add it later to the value selection (differs for the element type)
            // e.g. 1: setting the $val_time_phr to 2020
            if ($frm_elm->type() == formula::class) {
                // at the moment the special formulas only change the time word, this is why val_wrd_id is not set here
                if ($frm_elm->obj->is_predefined() and $val_time_phr == null) {
                    $val_time_phr = $this->set_formula_time_phrase($frm_elm, $val_phr_lst, $msg);
                    if ($val_time_phr != null) {
                        log_debug('adjusted time ' . $val_time_phr->dsp_id());
                    }
                } else {
                    if ($frm_elm->wrd_id > 0) {
                        $val_phr_lst->add($frm_elm->wrd_obj->phrase());
                    }
                    log_debug('include formula word "' . $frm_elm->wrd_obj->name . '" (' . $frm_elm->wrd_id . ')');
                }
            }

            // add the time to the list
            $val_time_phr = $val_phr_lst->assume_time($msg, $trm_lst);
            if ($val_time_phr != null) {
                log_debug('for time ' . $val_time_phr->dsp_id());
            }
            if (!$val_phr_lst->has_time($msg) and $val_time_phr != null) {
                $val_phr_lst->add($val_time_phr);
            }

            // get the word group
            $val_phr_lst_sort = $val_phr_lst->lst();
            usort($val_phr_lst_sort, array(phrase::class, "cmp"));
            $val_phr_lst->set_lst($val_phr_lst_sort);

            $this->add_figure($fig_lst, $val_phr_lst, $frm_elm->symbol, $msg);
        }

        log_debug($lib->dsp_count($fig_lst->lst()) . ' found');
        return $fig_lst;
    }

    /**
     * add the number of a phrase list to the figures: a value that a user has entered, else a formula result
     *
     * @param figure_list $fig_lst the figures of this element group, marked as incomplete if no number is found
     * @param phrase_list $val_phr_lst the phrases that select the number
     * @param string|null $val_symbol the symbol of the figure of a value
     * @param user_message $msg to report a problem of the load
     * @return void
     */
    private function add_figure(figure_list $fig_lst, phrase_list $val_phr_lst, ?string $val_symbol, user_message $msg): void
    {
        $val_phr_grp = $val_phr_lst->get_grp_id();
        // try to get a normal value set by the user directly for the phrase list
        // display the word group value and offer the user to change it
        // e.g. if the user has overwritten a result use the user overwrite
        log_debug('load word value for ' . $val_phr_lst->dsp_id());
        $wrd_val = new value($this->usr);
        // TODO Prio 2 create $wrd_val->load_best();
        // the load result and not isset(), because load_by_grp sets the group also if no
        // value is found, which would add a figure without a number
        if ($wrd_val->load_by_grp($val_phr_grp, $msg)) {
            // save the value to the result
            $fig = $wrd_val->figure();
            $fig->set_symbol($val_symbol);
            $fig_lst->add($fig, $msg);
            log_debug('value result for ' . $val_phr_lst->dsp_id() . ' = ' . $wrd_val->number() . ' (symbol ' . $fig->get_symbol() . ')');
        } else {
            // if there is no number that the user has entered for the word list, try to get the most useful formula result
            log_debug('load result for ' . $val_phr_lst->dsp_name());
            $grp_res = new result($this->usr);
            // save the value to the result; never compare the id with a number, because php
            // reports e.g. the text key '/x-' > 0 as false (see db_object_multi::is_id_set)
            if ($grp_res->load_by_grp($val_phr_grp, $msg, true)) {
                $fig = $grp_res->figure();
                $fig->set_symbol($this->symbol);
                $fig_lst->add($fig, $msg);
                log_debug('result for ' . $val_phr_lst->dsp_name() . ' (word group ' . $val_phr_grp->id() . ', user ' . $this->usr->id() . ') = ' . $grp_res->number());
            } else {
                // if there is also not a formula result at least one number of the formula is not valid
                $fig_lst->fig_missing = True;
                log_debug('figure missing');
            }
        }
    }

    /**
     * @return bool true if the group is an operand with prior next to another phrase e.g. "cumulative deviation","prior",
     *              which reads the previous row (shared\calc\prior_operand), and not the time jump of a plain "prior"
     */
    function reads_prior_row(): bool
    {
        $has_prior = false;
        foreach ($this->lst() as $elm) {
            if ($elm->type() == formula::class and $elm->obj?->type_cl == formula_type::PREV) {
                $has_prior = true;
            }
        }
        return ($has_prior and count($this->lst()) > 1);
    }

    /**
     * @param user_message $msg to report a problem of the load
     * @return figure_list the number of the previous row of the context, the number 0 for the first row
     *                     and no figure, marked as incomplete, if the previous row is not clear
     */
    private function prior_row_figures(user_message $msg): figure_list
    {
        $fig_lst = new figure_list($this->usr);
        $names = array_map(fn($elm) => $elm->obj->name(), $this->lst());
        $ctx = $this->phr_lst->names();
        $pri = new prior_operand($this->prior_names($msg));
        [$sel_names, $sel_ctx] = $pri->select($names, $ctx);
        if ($pri->is_first($names, $ctx)) {
            $val = new value($this->usr);
            $val->set_number(0);
            $fig = $val->figure();
            $fig->set_symbol($this->symbol);
            $fig_lst->add($fig, $msg);
        } elseif ($sel_names == $names) {
            $fig_lst->fig_missing = true;
        } else {
            $this->add_figure($fig_lst, $this->prior_phrases($sel_names, $sel_ctx, $msg), $this->symbol, $msg);
        }
        return $fig_lst;
    }

    /**
     * @param array $names the phrase names of the operand with the previous phrase
     * @param array $ctx the phrase names of the context with the previous phrase
     * @param user_message $msg to report a problem of the load
     * @return phrase_list the phrases that select the number of the previous row
     */
    private function prior_phrases(array $names, array $ctx, user_message $msg): phrase_list
    {
        $result = new phrase_list($this->usr);
        foreach (array_unique([...$ctx, ...$names]) as $name) {
            $phr = new phrase($this->usr);
            $phr->load_by_name($name, $msg);
            $result->add($phr, false, $msg);
        }
        return $result;
    }

    /**
     * @param user_message $msg to report a problem of the load
     * @return array per phrase name the name of the phrase before it, for the chains of the phrases of the context
     */
    private function prior_names(user_message $msg): array
    {
        global $sys;
        $trp_lst = new triple_list($this->usr);
        $trp_lst->load_by_phr_lst($this->phr_lst, $msg, $sys->verb(verbs::FOLLOW), foaf_direction::BOTH);
        $result = [];
        foreach ($trp_lst->lst() as $trp) {
            $result = prior_operand::with_link($result, $trp->get_from()->name(), $trp->get_to()->name());
        }
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

    /**
     * @param ?int $limit the max number of ids to show
     * @return array with the database ids of all objects of this list
     */
    function ids(?int $limit = null): array
    {
        $result = array();
        foreach ($this->lst() as $frm_elm) {
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
        return $result;
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

}