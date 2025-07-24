<?php

/*

    model/formula/expression.php - a text that implies a data selection and can calculate a number
    ----------------------------

    the formula expression with
    the right part of the equation sign which for calculation the result
    and the left part which contains phrases to be added to the result
    usually in the database reference format

    sample
    formula with name "increase"
    and expression "percent" = ("this" - "prior") / "prior
"
    original request: formula: increase, words: "Nestlé", "turnover"
    formula "increase": (next[] - last[]) / last[]
    formula "next": needs time jump value[is time jump for
    so                       -> next["time jump"->,         "follower of"->"Now"]
    1. find_missing_phrase_types: next["time jump"->"Company","follower of"->"Now"]
    2. calc word:               next["YoY",                 "follower of"->"Now"]
    3. calc word:               next["YoY",                 "follower of"->"This Year"]
    4. calc word:               next["YoY",                 "follower of"->"2013"]
    5. calc word:               next["YoY",                 "2014"]


    parse

    1. get inner part
    2. do fixed functions
    3. add word
    4. get values

    rules:
    a verb cannot have the same name as a word
    a formula cannot have the same name as a word
    if needed just add "(formula)"



    next needs time jump -> value[is time jump for->:time_word]
    this year:
    2013 is predecessor of 2014
    time jump for company is "YoY" -> word link table
    next

    formula types: calc:

    syntax: function_name["link_type->phrase_type:word_name"]
    or: word_from>triple:word_to e.g. “>is a:Company” lists all companies
    or: word[condition_formula] e.g. “GAAP[>is:US]” use GAAP only for US based companies
    or: word1 || word2 e.g. “GAAP[>is:US]” use word1 or word2 (maybe replace “||” with “or” for users)
    or: -word e.g. “-word” remove the word from the context

    samples: next["->Timejump"] means next needs a time jump word
    db format: {f2}[->{}]

    Four steps to get the result
    1. replace functions
    2. complete words
    3. get values
    4. send calc to octave


    zu_calc("increase("Nestlé", "turnover")")
      -> find_missing_phrase_types("Nestlé", "turnover") with formula "increase" (zu_word_find_missing_types ($word_array, $formula_id))
        -> increase needs time_jump phrase_type
      -> assume_missing words("Nestlé", "turnover") with phrase_type "time_jump"
          -> get time_jump for "Nestlé", "turnover"
            -> add_word "YoY" linked to "Company" linked to "Nestlé"
            -> result increase("Nestlé", "turnover", "YoY")
    -> zu_calc("next(Nestlé, turnover, YoY)")
       -> increase formula: (next() - last()) / last()
       -> add_word("Next Year")
          -> zu_calc("get_value(Nestlé, turnover, YoY, Next Year)")

    Sample 2
    zu_calc("countryweight("Nestlé")")
    -> formula = '="Country" "differentiator"/"differentiator total"' // convert predefined formula "differentiator total"
    -> "differentiator total" = '=sum("differentiator")' // convert predefined formula "differentiator total"
    -> call 'get words("Nestlé" "Country" "differentiator")', which returns a list of words ergo the result will be a list
    -> call 'get values("Nestlé" "Country" "differentiator")', which returns a list of values
    -> call function sum to get the sum of the differentiators
    -> calc the percent result for each value



    Parser

    The parsing is done in two steps:

    1. add default words and replace the words with value
    2. calc the result

    Do automatic caching of the results if needed


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

namespace cfg\formula;

include_once SHARED_CALC_PATH . 'expression.php';
include_once MODEL_ELEMENT_PATH . 'element.php';
include_once MODEL_ELEMENT_PATH . 'element_group.php';
include_once MODEL_ELEMENT_PATH . 'element_group_list.php';
include_once MODEL_ELEMENT_PATH . 'element_list.php';
include_once MODEL_PHRASE_PATH . 'phr_ids.php';
include_once MODEL_PHRASE_PATH . 'phrase_list.php';
include_once MODEL_PHRASE_PATH . 'term.php';
include_once MODEL_PHRASE_PATH . 'term_list.php';
include_once MODEL_WORD_PATH . 'word.php';
include_once MODEL_WORD_PATH . 'triple.php';
include_once MODEL_VERB_PATH . 'verb.php';
include_once MODEL_USER_PATH . 'user.php';
include_once SHARED_CALC_PATH . 'parameter_type.php';
include_once SHARED_CONST_PATH . 'chars.php';
include_once SHARED_TYPES_PATH . 'phrase_type.php';
include_once SHARED_PATH . 'library.php';

use cfg\element\element;
use cfg\element\element_group;
use cfg\element\element_group_list;
use cfg\element\element_list;
use cfg\phrase\phr_ids;
use cfg\phrase\phrase_list;
use cfg\phrase\term;
use cfg\phrase\term_list;
use cfg\user\user;
use cfg\verb\verb;
use cfg\word\triple;
use cfg\word\word;
use Exception;
use shared\calc\expression as shared_expression;
use shared\calc\parameter_type;
use shared\const\chars;
use shared\library;
use shared\types\phrase_type as phrase_type_shared;

class expression extends shared_expression
{

    /*
     * code links
     */

    // predefined type selectors potentially used also in other classes
    const SELECT_ALL = "all";              // to get all formula elements
    const SELECT_PHRASE = "phrases";       // to filter only the words from the expression element list
    const SELECT_VERB = "verbs";           // to filter only the verbs from the expression element list
    const SELECT_FORMULA = "formulas";     // to filter only the formulas from the expression element list
    const SELECT_VERB_WORD = "verb_words"; // to filter the words and the words implied by the verbs from the expression element list


    /*
     * object vars
     */

    public user $usr; // to get the user settings for the conversion


    /*
     * construct and map
     */

    function __construct(user $usr)
    {
        $this->usr = $usr;
        $this->reset();
    }


    /*
     * interface
     */

    /**
     * get the phrases that are user to calculate the expression result
     * used to detect if the phrases should trigger predefined function e.g. to scale the values
     *
     * @param term_list|null $trm_lst a list of preloaded terms that should be used for the transformation
     * @returns phrase_list with the phrases from a given formula text and load the phrases
     */
    function phr_lst(?term_list $trm_lst = null): phrase_list
    {
        $phr_lst = new phrase_list($this->usr);
        $phr_ids = $this->phr_id_lst($this->r_part());
        if ($trm_lst == null) {
            $phr_lst->load_names_by_ids($phr_ids);
        } else {
            $phr_lst->load_names_by_ids($phr_ids, $trm_lst->phrase_list());
        }

        return $phr_lst;
    }

    /**
     * get all terms used for this formula expression
     *
     * @param term_list|null $cac_trm_lst cache of the terns to avoid multiple db loading
     * @return term_list with all terms used in this expression
     */
    function terms(?term_list $cac_trm_lst = null): term_list
    {
        $trm_lst = $this->element_list($cac_trm_lst)->term_list();
        $trm_lst->merge($this->result_phrases($cac_trm_lst)->term_list());
        return $trm_lst;
    }

    /**
     * get the phrases that should be added to the result of a formula
     *
     * @param term_list|null $trm_lst a list of preloaded terms that should be used for the transformation
     * @returns phrase_list with the phrases that should be added to the result of a formula
     * e.g. for >"percent" = ( "this" - "prior" ) / "prior"< a list with the phrase "percent" will be returned
     */
    function result_phrases(?term_list $trm_lst = null): phrase_list
    {
        $phr_lst = new phrase_list($this->usr);
        $phr_ids = $this->phr_id_lst($this->res_part());
        if ($trm_lst == null) {
            $phr_lst->load_names_by_ids($phr_ids);
        } else {
            $cac_lst = $trm_lst->get_by_ids($phr_ids->trm_ids());
            $ids_to_load = array_diff($phr_ids->lst, $cac_lst->id_lst());
            if (count($ids_to_load) > 0) {
                $phr_lst->load_by_ids($phr_ids, $trm_lst->phrase_list());
            }
        }

        return $phr_lst;
    }

    function is_valid(): bool
    {
        $is_valid = true;
        if (($this->ref_text() == null or $this->ref_text() == '')
        and ($this->user_text() == null or $this->user_text() == '')) {
            $is_valid = false;
        }
        return $is_valid;
    }

    /**
     * a formula element group is a group of words, verbs, phrases or formula
     * that retrieve a value or a list of values
     * e.g. with "sector" "differentiator" all
     *
     * @param term_list|null $trm_lst a list of preloaded terms that should be used for the transformation
     * @return element_list|element_group_list with the formula element groups used in the expression
     */
    function element_grp_lst(?term_list $trm_lst = null): element_list|element_group_list
    {
        return $this->element_lst_all(expression::SELECT_ALL, TRUE, $trm_lst);
    }

    /**
     * get a list of all formula elements
     *
     * @param term_list|null $trm_lst a list of preloaded terms that should be used for the transformation
     * @return element_list a list of all formula elements
     * (don't use for number retrieval, use element_grp_lst instead, because )
     */
    function element_list(?term_list $trm_lst = null): element_list
    {
        $lib = new library();

        $elm_lst = new element_list($this->usr);
        $work = $this->r_part();

        $obj_sym = $lib->str_between($work, chars::TERM_START, chars::TERM_END);
        while ($obj_sym != '') {
            $elm = $this->element_by_symbol($obj_sym, $trm_lst);
            $elm_lst->add($elm);
            $work = $lib->str_right_of($work, chars::TERM_END);
            $obj_sym = $lib->str_between($work, chars::TERM_START, chars::TERM_END);
        }
        return $elm_lst;
    }

    /**
     * list of elements (in this case only formulas) that are of the predefined type "following"
     * e.g. "this", "next" and "prior"
     * @param term_list|null $trm_lst a list of preloaded terms that should be preferred used for the conversion
     * @return phrase_list a list of all formulas words that are using hardcoded functions
     */
    function element_special_following(?term_list $trm_lst = null): phrase_list
    {
        global $phr_typ_cac;
        $lib = new library();

        $phr_lst = new phrase_list($this->usr);
        $elm_lst = $this->element_list($trm_lst);
        if (!$elm_lst->is_empty()) {
            foreach ($elm_lst->lst() as $elm) {
                if ($elm->type == formula::class) {
                    if ($elm->obj != null) {
                        if ($elm->obj->type_cl == formula_type::THIS
                            or $elm->obj->type_cl == formula_type::NEXT
                            or $elm->obj->type_cl == formula_type::PREV) {
                            if ($elm->obj->name_wrd != null) {
                                $phr_lst->add($elm->obj->name_wrd->phrase());
                            }
                        }
                    }
                }
                if ($elm->type == word::class or $elm->type == triple::class) {
                    if ($elm->obj->type_id == $phr_typ_cac->id(phrase_type_shared::THIS)
                        or $elm->obj->type_id == $phr_typ_cac->id(phrase_type_shared::NEXT)
                        or $elm->obj->type_id == $phr_typ_cac->id(phrase_type_shared::PRIOR)) {
                        $phr_lst->add($elm->obj->phrase());
                    }
                }
            }
        }

        log_debug($lib->dsp_count($phr_lst->lst()));
        return $phr_lst;
    }

    /**
     * similar to element_special_following, but returns the formula and not the word
     * @param term_list|null $trm_lst a list of preloaded terms that should be preferred used for the conversion
     * @return formula_list a list of all formulas that are using hardcoded functions
     */
    function element_special_following_frm(?term_list $trm_lst = null): formula_list
    {
        $lib = new library();

        $frm_lst = new formula_list($this->usr);
        $elm_lst = $this->element_list($trm_lst);
        if (!$elm_lst->is_empty()) {
            foreach ($elm_lst->lst() as $elm) {
                if ($elm->type == formula::class) {
                    if ($elm->obj != null) {
                        if ($elm->obj->type_cl == formula_type::THIS
                            or $elm->obj->type_cl == formula_type::NEXT
                            or $elm->obj->type_cl == formula_type::PREV) {
                            $frm_lst->add($elm->obj);
                        }
                    }
                }
            }
        }

        log_debug($lib->dsp_count($frm_lst->lst()));
        return $frm_lst;
    }


    /*
     * internal
     * functions public just for testing
     */

    /**
     * @returns phr_ids with the word and triple ids from a given formula text
     * and without loading the objects from the database
     */
    function phr_id_lst(string $ref_text): phr_ids
    {
        $id_lst = [];

        $lib = new library();

        if ($ref_text <> "") {
            // add word ids to selection
            $new_wrd_id = $this->get_word_id($ref_text);
            while ($new_wrd_id != 0) {
                if (!in_array($new_wrd_id, $id_lst)) {
                    $id_lst[] = $new_wrd_id;
                }
                $ref_text = $lib->str_right_of($ref_text, chars::WORD_START . $new_wrd_id . chars::WORD_END);
                $new_wrd_id = $this->get_word_id($ref_text);
            }
            // add triple ids to selection
            $new_trp_id = $this->get_triple_id($ref_text);
            while ($new_trp_id != 0) {
                if (!in_array($new_wrd_id, $id_lst)) {
                    $id_lst[] = $new_trp_id * -1;
                }
                $ref_text = $lib->str_right_of($ref_text, chars::TRIPLE_START . $new_trp_id . chars::TRIPLE_END);
                $new_trp_id = $this->get_triple_id($ref_text);
            }
        }

        return new phr_ids($id_lst);
    }

    /**
     * @returns phrase_list with the word and triple ids from a given formula text
     * and without loading the objects from the database
     */
    function phr_id_lst_as_phr_lst(string $ref_text): phrase_list
    {
        $phr_lst = new phrase_list($this->usr);
        $id_lst = $this->phr_id_lst($ref_text)->lst;
        foreach ($id_lst as $id) {
            $phr_lst->add_id($id);
        }
        return $phr_lst;
    }

    /**
     * @returns bool true if the formula contains a word, verb or formula link
     */
    function has_ref(): bool
    {
        log_debug($this->dsp_id());
        $result = false;

        if ($this->get_word_id($this->ref_text()) > 0
            or $this->get_triple_id($this->ref_text()) > 0
            or $this->get_formula_id($this->ref_text()) > 0
            or $this->get_verb_id($this->ref_text()) > 0) {
            $result = true;
        }

        log_debug('done ' . zu_dsp_bool($result));
        return $result;
    }

    /**
     * @return array of the term names used in the expression based on the user text
     * e.g. converts "'sales' 'differentiator' / 'Total sales'" to "sales, differentiator, Total sales"
     */
    function get_usr_names(): array
    {
        $result = [];
        $remaining = $this->user_text();

        if ($remaining != '') {
            // find the first word
            $start = 0;
            $pos = strpos($remaining, chars::TERM_DELIMITER, $start);
            $end = strpos($remaining, chars::TERM_DELIMITER, $pos + 1);
            while ($end !== False) {
                // for 12'45'78: pos = 2, end = 5, name = 45, left = 12. right = 78
                $name = substr($remaining, $pos + 1, $end - $pos - 1);
                if (!in_array($name, $result)) {
                    $result[] = $name;
                }
                $remaining = substr($remaining, $end + 1);

                // find the next word
                $end = false;
                if ($start < strlen($remaining)) {
                    $pos = strpos($remaining, chars::TERM_DELIMITER, $start);
                    if ($pos !== false) {
                        $end = strpos($remaining, chars::TERM_DELIMITER, $pos + 1);
                    }
                }
            }
        }
        return $result;
    }


    /*
     * internal functions
     */

    /**
     * returns the next word id if the formula string in the database format contains a word link
     * @param string $ref_text with the formula reference text e.g. ={w203}
     * @return int the word id found in the reference text or zero if no word id is found
     */
    private function get_word_id(string $ref_text): int
    {
        return $this->get_ref_id($ref_text, chars::WORD_START, chars::WORD_END);
    }

    /**
     * returns the next triple id if the formula string in the database format contains a triple link
     * @param string $ref_text with the formula reference text e.g. ={t42}
     * @return int the word id found in the reference text or zero if no triple id is found
     */
    private function get_triple_id(string $ref_text): int
    {
        return $this->get_ref_id($ref_text, chars::TRIPLE_START, chars::TRIPLE_END);
    }

    /**
     * returns the next formula id if the formula string in the database format contains a triple link
     * @param string $ref_text with the formula reference text e.g. ={f42}
     * @return int the word id found in the reference text or zero if no formula id is found
     */
    private function get_formula_id(string $ref_text): int
    {
        return $this->get_ref_id($ref_text, chars::FORMULA_START, chars::FORMULA_END);
    }

    /**
     * returns the next verb id if the formula string in the database format contains a triple link
     * @param string $ref_text with the formula reference text e.g. ={v42}
     * @return int the word id found in the reference text or zero if no verb id is found
     */
    private function get_verb_id(string $ref_text): int
    {
        return $this->get_ref_id($ref_text, chars::VERB_START, chars::VERB_END);
    }

    /**
     * returns the next positive reference (word, verb or formula) id if the formula string in the database format contains a database reference link
     * uses the $ref_text as a parameter because to ref_text is in many cases only a part of the complete reference text
     *
     * @param string $ref_text with the formula reference text e.g. ={f203}
     * @param string $start_maker the definition of the start of the reference
     * @param string $end_maker the definition of the end of the reference
     * @return int the id found in the reference text or zero if no id is found
     */
    private function get_ref_id(string $ref_text, string $start_maker, string $end_maker): int
    {
        $result = 0;

        $lib = new library();

        $pos_start = strpos($ref_text, $start_maker);
        if ($pos_start !== false) {
            $r_part = $lib->str_right_of($ref_text, $start_maker);
            $l_part = $lib->str_left_of($r_part, $end_maker);
            if (is_numeric($l_part)) {
                $result = $l_part;
            }
        }

        return $result;
    }

    /**
     * create a formula element based on the id symbol e.g. w2 for word with id 2
     * and get the word, triple, formula or verb either from the given preloaded term list
     * or load the object from the database
     *
     * @param string $obj_sym the formula element symbol e.g. t2 for triple with id 2
     * @param term_list|null $trm_lst a list of preloaded terms
     * @return element the filled formula element
     */
    private function element_by_symbol(string $obj_sym, ?term_list $trm_lst = null): element
    {
        $elm = new element($this->usr);
        $elm->type = match ($obj_sym[0]) {
            chars::WORD_SYMBOL => parameter_type::WORD_CLASS,
            chars::TRIPLE_SYMBOL => parameter_type::TRIPLE_CLASS,
            chars::FORMULA_SYMBOL => parameter_type::FORMULA_CLASS,
            chars::VERB_SYMBOL => parameter_type::VERB_CLASS,
        };
        $id = substr($obj_sym, 1);
        $trm = $trm_lst?->term_by_obj_id($id, $elm->type);
        if ($trm == null) {
            $trm = new term($this->usr);
            $trm->load_by_obj_id($id, $elm->type);
        }
        if ($trm != null) {
            if ($trm->id() != 0) {
                $elm->obj = $trm->obj();
                $elm->symbol = $this->get_db_sym($trm);
            } else {
                log_warning($elm->type . ' with id ' . $id . ' not found');
            }
        }

        return $elm;
    }

    /**
     * create a list of all formula elements
     * with the $type parameter the result list can be filtered
     * the filter is done within this function, because e.g. a verb can increase the number of words to return
     * if group it is true, element groups instead of single elements are returned
     * the order of the formula elements is relevant because the elements can influence each other
     */
    private function element_lst_all(
        string     $type = self::SELECT_ALL,
        bool       $group_it = false,
        ?term_list $trm_lst = null
    ): element_list|element_group_list
    {
        log_debug('get ' . $type . ' out of "' . $this->ref_text() . '" for user ' . $this->usr->name);

        $lib = new library();

        // init result and work vars
        $lst = array();
        if ($group_it) {
            $result = new element_group_list($this->usr);
            $elm_grp = new element_group;
            $elm_grp->usr = $this->usr;
        } else {
            $result = new element_list($this->usr);
        }
        $result->set_user($this->usr);
        $work = $this->r_part();
        if (is_null($type) or $type == "") {
            $type = self::SELECT_ALL;
        }

        if ($work == '') {
            // zu_warning ???
            log_warning('work is empty', '', ' work is empty', (new Exception)->getTraceAsString(), $this->usr);
        } else {
            // loop over the formula text and replace ref by ref from left to right
            $found = true;
            $nbr = 0;
            while ($found and $nbr < MAX_LOOP) {
                log_debug('in "' . $work . '"');
                $found = false;

                // $pos is the position von the next element
                // to list the elements from left to right, set it to the right most position at the beginning of each replacement
                $obj_sym = $lib->str_between($work, chars::TERM_START, chars::TERM_END);
                if ($obj_sym != '') {
                    $elm = $this->element_by_symbol($obj_sym, $trm_lst);

                    // filter the elements if requested
                    if ($type == self::SELECT_PHRASE) {
                        if ($elm->type != word::class and $elm->type != triple::class) {
                            $elm->obj = null;
                        }
                    }
                    if ($type == self::SELECT_FORMULA) {
                        if ($elm->type != formula::class) {
                            $elm->obj = null;
                        }
                    }
                    if ($type == self::SELECT_VERB) {
                        if ($elm->type != verb::class) {
                            $elm->obj = null;
                        }
                    }
                    if ($type == self::SELECT_VERB_WORD) {
                        if ($elm->type != word::class and $elm->type != verb::class) {
                            $elm->obj = null;
                        }
                    }

                    // update work text
                    $work = $lib->str_right_of($work, chars::TERM_END);

                    // add reference to result
                    if ($elm->obj != null) {

                        $found = true;

                        // group the references if needed
                        if ($group_it) {
                            $elm_grp->add_obj($elm);
                            log_debug('new group element "' . $elm->name() . '"');

                            // find the next term reference
                            $txt_between_elm = $lib->str_left_of($work, chars::TERM_START);
                            $txt_between_elm = trim($txt_between_elm);

                            // check if the references does not have any math symbol in between
                            // and therefore are used to retrieve one value
                            if (strlen($txt_between_elm) > 0) {
                                $lst[] = $elm_grp;
                                log_debug('group finished with ' . $elm->name());
                                $elm_grp = new element_group;
                                $elm_grp->usr = $this->usr;
                            }
                        } else {
                            $lst[] = $elm;
                        }
                        $nbr++;
                    }
                }
            }

            // add last element group
            if ($group_it) {
                if (!$elm_grp->is_empty()) {
                    $lst[] = $elm_grp;
                }
            }
        }
        $result->set_lst($lst);

        log_debug($lib->dsp_count($result->lst()) . ' elements');
        return $result;
    }


    /*
     * to review
     */

    /**
     * similar to phr_lst, but
     * e.g. for "sales" "differentiator" "Country" all "Country" words should be included
     * TODO should also include the words implied by the verbs
     */
    function phr_verb_lst(): phrase_list
    {
        $lib = new library();

        log_debug();
        $elm_lst = $this->element_lst_all(expression::SELECT_PHRASE);
        log_debug('got ' . $lib->dsp_count($elm_lst->lst()) . ' formula elements');
        $phr_lst = new phrase_list($this->usr);
        foreach ($elm_lst->lst() as $elm) {
            log_debug('check elements ' . $elm->name());
            if ($elm->type == formula::class) {
                if (isset($elm->wrd_obj)) {
                    $phr = $elm->wrd_obj->phrase();
                    $phr_lst->add($phr);
                } else {
                    log_err('Word missing for formula element ' . $elm->dsp_id() . '.', 'expression->phr_verb_lst');
                }
            } elseif ($elm->type == word::class) {
                if (isset($elm->obj)) {
                    $phr = $elm->obj->phrase();
                    $phr_lst->add($phr);
                } else {
                    log_err('Word missing for formula element ' . $elm->dsp_id() . '.', 'expression->phr_verb_lst');
                }
            } elseif ($elm->type == verb::class) {
                log_warning('Use Formula element ' . $elm->dsp_id() . ' has an unexpected type.', 'expression->phr_verb_lst');
            } else {
                log_err('Formula element ' . $elm->dsp_id() . ' has an unexpected type.', 'expression->phr_verb_lst');
            }
        }
        // TODO check if the phrases are already loaded
        //$phr_lst->load();
        log_debug($lib->dsp_count($phr_lst->lst()));
        return $phr_lst;
    }


    /*
     * debug
     */

    /**
     * @return string with the expression name to use it for debugging
     */
    function dsp_id(): string
    {
        // $result = '"' . $this->usr_text . '" (' . $this->ref_text . ')';
        // the user is no most cases no extra info
        // $result .= ' for user '.$this->usr->name.'';
        return '"' . $this->user_text() . '" (' . $this->ref_text() . ')';
    }

    function name(): string
    {
        return $this->user_text();
    }

    /*
     * overwrite
     */

    protected function get_formula_symbol(string $name): string
    {
        $frm = new formula($this->usr);
        $frm->load_by_name($name);
        if ($frm->id() > 0) {
            $db_sym = chars::FORMULA_START . $frm->id() . chars::FORMULA_END;
            log_debug('found formula "' . $db_sym . '" for "' . $name . '"');
        } else {
            $db_sym = '';
        }
        return $db_sym;
    }

    protected function get_word_symbol(string $name): string
    {
        $wrd = new word($this->usr);
        $wrd->load_by_name($name);
        if ($wrd->id() > 0) {
            $db_sym = chars::WORD_START . $wrd->id() . chars::WORD_END;
            log_debug('found word "' . $db_sym . '" for "' . $name . '"');
        } else {
            $db_sym = '';
        }
        return $db_sym;
    }

    protected function get_triple_symbol(string $name): string
    {
        $trp = new triple($this->usr);
        $trp->load_by_name($name);
        if ($trp->id() > 0) {
            $db_sym = chars::TRIPLE_START . $trp->id() . chars::TRIPLE_END;
            log_debug('found triple "' . $db_sym . '" for "' . $name . '"');
        } else {
            $db_sym = '';
        }
        return $db_sym;
    }

    protected function get_verb_symbol(string $name): string
    {
        $vrb = new verb;
        $vrb->set_user($this->usr);
        $vrb->load_by_name($name);
        if ($vrb->id() > 0) {
            $db_sym = chars::VERB_START . $vrb->id() . chars::VERB_END;
            log_debug('found verb "' . $db_sym . '" for "' . $name . '"');
        } else {
            $db_sym = '';
        }
        return $db_sym;
    }

    protected function load_word(int $id): ?word
    {
        $wrd = new word($this->usr);
        $wrd->load_by_id($id);
        if ($wrd->id() == 0) {
            $wrd = null;
        }
        return $wrd;
    }

    protected function load_triple(int $id): ?triple
    {
        $trp = new triple($this->usr);
        $trp->load_by_id($id);
        if ($trp->id() == 0) {
            $trp = null;
        }
        return $trp;
    }

    protected function load_formula(int $id): ?formula
    {
        $frm = new formula($this->usr);
        $frm->load_by_id($id);
        if ($frm->id() == 0) {
            $frm = null;
        }
        return $frm;
    }

    protected function load_verb(int $id): ?verb
    {
        $vrb = new verb();
        $vrb->set_user($this->usr);
        $vrb->load_by_id($id);
        if ($vrb->id() == 0) {
            $vrb = null;
        }
        return $vrb;
    }

}
