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

namespace cfg;

include_once MODEL_ELEMENT_PATH . 'element_group.php';
include_once MODEL_ELEMENT_PATH . 'element_group_list.php';

use Exception;
use shared\library;

class expression
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

    // text maker to convert phrase, formula or verb database reference to
    // a phrase or phrase list and in a second step to a value or value list
    const TERM_START = '{'; //
    const TERM_END = '}'; //
    const WORD_SYMBOL = 'w'; //
    const TRIPLE_SYMBOL = 't'; //
    const FORMULA_SYMBOL = 'f'; //
    const VERB_SYMBOL = 'v'; //
    const WORD_START = '{w';   //
    const WORD_END = '}';    //
    const TRIPLE_START = '{t';   //
    const TRIPLE_END = '}';    //
    const FORMULA_START = '{f';   //
    const FORMULA_END = '}';    //
    const VERB_START = '{v';   //
    const VERB_END = '}';    //

    // text conversion const (used to convert word, formula or verbs text to a reference)
    const BRACKET_OPEN = '(';    //
    const BRACKET_CLOSE = ')';    //
    const TXT_FIELD = '"';    // don't look for math symbols in text that is a high quotes

    // text conversion syntax elements
    // used to convert word, triple, verb or formula name to a database reference
    const TERM_DELIMITER = '"';    // or a zukunft verb or a zukunft formula
    const TERM_LIST_START = '[';    //
    const TERM_LIST_END = ']';    //
    const SEPARATOR = ',';    //
    const RANGE = ':';    //
    const CONCAT = '&';    //

    // math calc (probably not needed any more if r-project.org is used)
    const CHAR_CALC = '=';    //
    const ADD = '+';    //
    const SUB = '-';    //
    const MUL = '*';    //
    const DIV = '/';    //

    const AND = '&';   //
    const OR = '|';    // probably not needed because can and should be solved by triples

    // fixed functions
    const FUNC_IF = 'if';    //
    const FUNC_SUM = 'sum';    //
    const FUNC_IS_NUM = 'is.numeric';    //


    /*
     * object vars
     */

    private ?string $usr_text;         // the formula expression in the human-readable format
    private bool $usr_text_dirty;      // true if the reference text has been updated and not yet converted
    private ?string $ref_text;         // the formula expression with the database references
    private bool $ref_text_dirty;      // true if the human-readable text has been updated and not yet converted
    public ?string $err_text = null;   // description of the problems that appeared during the conversion from the human-readable to the database reference format
    public user $usr;                  // to get the user settings for the conversion


    /*
     * construct and map
     */

    function __construct(user $usr)
    {
        $this->usr = $usr;
        $this->usr_text = null;
        $this->usr_text_dirty = false;
        $this->ref_text = null;
        $this->ref_text_dirty = false;
    }


    /*
     * set and get
     */

    /**
     * update the expression by setting the human-readable format and try to update the database reference format
     * @param string $usr_txt the formula expression in the human-readable format
     * @param term_list|null $trm_lst a list of preloaded terms that should be used for the transformation
     * @return void
     */
    function set_user_text(string $usr_txt, ?term_list $trm_lst = null): void
    {
        $this->usr_text = $usr_txt;
        $this->usr_text_dirty = false;
        $this->ref_text_dirty = true;
        $this->ref_text($trm_lst);
    }

    /**
     * update the expression by setting the database reference format and try to update the human-readable format
     * @param string $ref_txt the formula expression in the database reference format
     * @param term_list|null $trm_lst a list of preloaded terms that should be used for the transformation
     * @return void
     */
    function set_ref_text(string $ref_txt, ?term_list $trm_lst = null): void
    {
        $this->ref_text = $ref_txt;
        $this->ref_text_dirty = false;
        $this->usr_text_dirty = true;
        $this->user_text($trm_lst);
    }

    /**
     * @param term_list|null $trm_lst a list of preloaded terms that should be used for the transformation
     * @return string|null the recreated expression in the human-readable format or null if an error has occurred
     */
    function user_text(?term_list $trm_lst = null): ?string
    {
        if ($this->usr_text_dirty) {
            $this->usr_text = $this->get_usr_text($trm_lst);
        }
        if (!$this->usr_text_dirty) {
            return $this->usr_text;
        } else {
            return '';
        }
    }

    /**
     * get and set the reference text based on the user formula expression
     * @param term_list|null $trm_lst a list of preloaded terms that should be used for the transformation
     * @return string|null the recreated expression in the database reference format or null if an error has occurred
     */
    function ref_text(?term_list $trm_lst = null): ?string
    {
        if ($this->ref_text_dirty) {
            $this->ref_text = $this->get_ref_text($trm_lst);
        }
        if (!$this->ref_text_dirty) {
            return $this->ref_text;
        } else {
            return '';
        }
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
     * get the phrases that should be added to the result of a formula
     *
     * @param term_list|null $trm_lst a list of preloaded terms that should be used for the transformation
     * @returns phrase_list with the phrases that should be added to the result of a formula
     * e.g. for >"percent" = ( "this" - "prior" ) / "prior"< a list with the phrase "percent" will be returned
     */
    function res_phr_lst(?term_list $trm_lst = null): phrase_list
    {
        $phr_lst = new phrase_list($this->usr);
        $phr_ids = $this->phr_id_lst($this->res_part());
        if ($trm_lst == null) {
            $phr_lst->load_names_by_ids($phr_ids);
        } else {
            $phr_lst->load_by_ids($phr_ids, $trm_lst->phrase_list());
        }

        return $phr_lst;
    }

    /**
     * a formula element group is a group of words, verbs, phrases or formula
     * that retrieve a value or a list of values
     * e.g. with "Sector" "differentiator" all
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

        $obj_sym = $lib->str_between($work, self::TERM_START, self::TERM_END);
        while ($obj_sym != '') {
            $elm = $this->element_by_symbol($obj_sym, $trm_lst);
            $elm_lst->add($elm);
            $work = $lib->str_right_of($work, self::TERM_END);
            $obj_sym = $lib->str_between($work, self::TERM_START, self::TERM_END);
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
        global $phrase_types;
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
                    if ($elm->obj->type_id == $phrase_types->id(phrase_type::THIS)
                        or $elm->obj->type_id == $phrase_types->id(phrase_type::NEXT)
                        or $elm->obj->type_id == $phrase_types->id(phrase_type::PRIOR)) {
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
     * convert
     * internal function to convert from reference text to user text and back
     */

    /**
     * convert the user text to the database reference format
     * @param term_list|null $trm_lst a list of preloaded terms that should be used for the transformation
     * @return string the expression in the formula reference format
     */
    private function get_ref_text(?term_list $trm_lst = null): string
    {
        $result = '';

        // check the formula indicator "=" and convert the left and right part separately
        $pos = strpos($this->usr_text, self::CHAR_CALC);
        if ($pos >= 0) {
            $left_part = $this->res_part_usr();
            $right_part = $this->r_part_usr();
            $left_part = $this->get_ref_part($left_part, $trm_lst);
            // continue with the right part of the expression only if the left part has been fine
            if (!$this->usr_text_dirty) {
                $right_part = $this->get_ref_part($right_part, $trm_lst);
            }
            $result = $left_part . self::CHAR_CALC . $right_part;
        }

        // remove all spaces because they are not relevant for calculation and to avoid too much recalculation
        return str_replace(" ", "", $result);
    }

    /**
     * @return string the formula expression converted to the user text from the database reference format
     * e.g. converts "{w5}={w6}{l12}/{f19}" to "'percent' = 'Sales' 'differentiator'/'Total Sales'"
     */
    private function get_usr_text(?term_list $trm_lst = null): string
    {
        log_debug($this->ref_text() . '< and user ' . $this->usr->name);
        $result = '';

        // check the formula indicator "=" and convert the left and right part separately
        $pos = strpos($this->ref_text, self::CHAR_CALC);
        if ($pos > 0) {
            $left_part = $this->res_part();
            $right_part = $this->r_part();
            log_debug('(l:' . $left_part . ',r:' . $right_part . '"');
            $left_part = $this->get_usr_part($left_part, $trm_lst);
            // continue with the right part of the expression only if the left part has been fine
            if (!$this->usr_text_dirty) {
                $right_part = $this->get_usr_part($right_part, $trm_lst);
            }
            $result = $left_part . self::CHAR_CALC . $right_part;
        }

        log_debug('done "' . $result . '"');
        return $result;
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
                $ref_text = $lib->str_right_of($ref_text, self::WORD_START . $new_wrd_id . self::WORD_END);
                $new_wrd_id = $this->get_word_id($ref_text);
            }
            // add triple ids to selection
            $new_trp_id = $this->get_triple_id($ref_text);
            while ($new_trp_id != 0) {
                if (!in_array($new_wrd_id, $id_lst)) {
                    $id_lst[] = $new_trp_id * -1;
                }
                $ref_text = $lib->str_right_of($ref_text, self::TRIPLE_START . $new_trp_id . self::TRIPLE_END);
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
     * find the position of the formula indicator "="
     * use the part left of it to add the words to the result
     */
    function res_part(): string
    {
        $lib = new library();
        $result = $lib->str_left_of($this->ref_text, self::CHAR_CALC);
        return trim($result);
    }

    function res_part_usr(): string
    {
        $lib = new library();
        $result = $lib->str_left_of($this->usr_text, self::CHAR_CALC);
        return trim($result);
    }

    function r_part(): string
    {
        $lib = new library();
        $result = $lib->str_right_of($this->ref_text, self::CHAR_CALC);
        return trim($result);
    }

    function r_part_usr(): string
    {
        $lib = new library();
        $result = $lib->str_right_of($this->usr_text, self::CHAR_CALC);
        return trim($result);
    }

    /**
     * @returns bool true if the formula contains a word, verb or formula link
     */
    function has_ref(): bool
    {
        log_debug($this->dsp_id());
        $result = false;

        if ($this->get_word_id($this->ref_text) > 0
            or $this->get_triple_id($this->ref_text) > 0
            or $this->get_formula_id($this->ref_text) > 0
            or $this->get_verb_id($this->ref_text) > 0) {
            $result = true;
        }

        log_debug('done ' . zu_dsp_bool($result));
        return $result;
    }

    /**
     * @return array of the term names used in the expression based on the user text
     * e.g. converts "'Sales' 'differentiator' / 'Total Sales'" to "Sales, differentiator, Total Sales"
     */
    function get_usr_names(): array
    {
        $result = [];
        $remaining = $this->user_text();

        if ($remaining != '') {
            // find the first word
            $start = 0;
            $pos = strpos($remaining, self::TERM_DELIMITER, $start);
            $end = strpos($remaining, self::TERM_DELIMITER, $pos + 1);
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
                    log_debug('start "' . $start . '"');
                    $pos = strpos($remaining, self::TERM_DELIMITER, $start);
                    if ($pos !== false) {
                        log_debug('pos "' . $pos . '"');
                        $end = strpos($remaining, self::TERM_DELIMITER, $pos + 1);
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
        return $this->get_ref_id($ref_text, self::WORD_START, self::WORD_END);
    }

    /**
     * returns the next triple id if the formula string in the database format contains a triple link
     * @param string $ref_text with the formula reference text e.g. ={t42}
     * @return int the word id found in the reference text or zero if no triple id is found
     */
    private function get_triple_id(string $ref_text): int
    {
        return $this->get_ref_id($ref_text, self::TRIPLE_START, self::TRIPLE_END);
    }

    /**
     * returns the next formula id if the formula string in the database format contains a triple link
     * @param string $ref_text with the formula reference text e.g. ={f42}
     * @return int the word id found in the reference text or zero if no formula id is found
     */
    private function get_formula_id(string $ref_text): int
    {
        return $this->get_ref_id($ref_text, self::FORMULA_START, self::FORMULA_END);
    }

    /**
     * returns the next verb id if the formula string in the database format contains a triple link
     * @param string $ref_text with the formula reference text e.g. ={v42}
     * @return int the word id found in the reference text or zero if no verb id is found
     */
    private function get_verb_id(string $ref_text): int
    {
        return $this->get_ref_id($ref_text, self::VERB_START, self::VERB_END);
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
            self::WORD_SYMBOL => parameter_type::WORD_CLASS,
            self::TRIPLE_SYMBOL => parameter_type::TRIPLE_CLASS,
            self::FORMULA_SYMBOL => parameter_type::FORMULA_CLASS,
            self::VERB_SYMBOL => parameter_type::VERB_CLASS,
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
                $obj_sym = $lib->str_between($work, self::TERM_START, self::TERM_END);
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
                    $work = $lib->str_right_of($work, self::TERM_END);

                    // add reference to result
                    if ($elm->obj != null) {

                        $found = true;

                        // group the references if needed
                        if ($group_it) {
                            $elm_grp->lst[] = $elm;
                            log_debug('new group element "' . $elm->name() . '"');

                            // find the next term reference
                            $txt_between_elm = $lib->str_left_of($work, self::TERM_START);
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
                if (!empty($elm_grp->lst)) {
                    $lst[] = $elm_grp;
                }
            }
        }
        $result->set_lst($lst);

        log_debug($lib->dsp_count($result->lst()) . ' elements');
        return $result;
    }

    /**
     * converts a formula from the database reference format to the human-readable format
     * e.g. converts "{w6}{l12}/{f19}" to "'Sales' 'differentiator'/'Total Sales'"
     * @param string $frm_part_text the expression text in user format that should be converted
     * @param term_list|null $trm_lst a list of preloaded terms that should be preferred used for the conversion
     * @return string the expression text in the database ref format
     */
    private function get_usr_part(string $frm_part_text, ?term_list $trm_lst = null): string
    {
        log_debug($frm_part_text . '< and user ' . $this->usr->name);
        $result = $frm_part_text;

        // if everything works fine the user text is not dirty anymore
        $this->usr_text_dirty = false;

        // replace the database references with the names
        $trm = $this->get_next_term_from_ref($result, $trm_lst);
        while ($trm != null) {
            $db_sym = $this->get_db_sym($trm);
            $result = str_replace($db_sym, self::TERM_DELIMITER . $trm->name() . self::TERM_DELIMITER, $result);
            $trm = $this->get_next_term_from_ref($result, $trm_lst);
        }

        log_debug($result);
        return $result;
    }

    /**
     * converts a formula from the user text format to the database reference format
     * e.g. converts "='Sales' 'differentiator'/'Total Sales'" to "={w6}{l12}/{f19}"
     *
     * @param string $frm_part_text the expression text in user format that should be converted
     * @param term_list|null $trm_lst a list of preloaded terms that should be preferred used for the conversion
     * @return string the expression text in the database ref format
     */
    private function get_ref_part(string $frm_part_text, term_list $trm_lst = null): string
    {
        log_debug('"' . $frm_part_text . ',' . $this->usr->name . '"');
        $result = $frm_part_text;

        // if everything works fine the user text is not dirty anymore
        $this->ref_text_dirty = false;

        if ($frm_part_text != '') {
            // find the first word
            $start = 0;
            $pos = strpos($result, self::TERM_DELIMITER, $start);
            $end = strpos($result, self::TERM_DELIMITER, $pos + 1);
            while ($end !== False) {
                // for 12'45'78: pos = 2, end = 5, name = 45, left = 12. right = 78
                $name = substr($result, $pos + 1, $end - $pos - 1);
                $left = substr($result, 0, $pos);
                $right = substr($result, $end + 1);
                log_debug('name "' . $name . '" (' . $end . ') left "' . $left . '" (' . $pos . ') right "' . $right . '"');

                $db_sym = '';

                // check if the preloaded terms can be used for the conversion
                if ($trm_lst != null) {
                    $trm = $trm_lst->get_by_name($name);
                    if ($trm != null) {
                        if ($trm->id_obj() > 0) {
                            $db_sym = $this->get_db_sym($trm);
                        }
                    }
                }


                // check for formulas first, because for every formula a word is also existing
                // similar to a part in get_usr_part, maybe combine
                if ($db_sym == '') {
                    $frm = new formula($this->usr);
                    $frm->load_by_name($name);
                    if ($frm->id() > 0) {
                        $db_sym = self::FORMULA_START . $frm->id() . self::FORMULA_END;
                        log_debug('found formula "' . $db_sym . '" for "' . $name . '"');
                    }
                }

                // check for words
                if ($db_sym == '') {
                    $wrd = new word($this->usr);
                    $wrd->load_by_name($name);
                    if ($wrd->id() > 0) {
                        $db_sym = self::WORD_START . $wrd->id() . self::WORD_END;
                        log_debug('found word "' . $db_sym . '" for "' . $name . '"');
                    }
                }

                // check for triple
                if ($db_sym == '') {
                    $trp = new triple($this->usr);
                    $trp->load_by_name($name);
                    if ($trp->id() > 0) {
                        $db_sym = self::TRIPLE_START . $trp->id() . self::TRIPLE_END;
                        log_debug('found triple "' . $db_sym . '" for "' . $name . '"');
                    }
                }

                // check for verbs
                if ($db_sym == '') {
                    $vrb = new verb;
                    $vrb->set_user($this->usr);
                    $vrb->load_by_name($name);
                    if ($vrb->id() > 0) {
                        $db_sym = self::VERB_START . $vrb->id() . self::VERB_END;
                        log_debug('found verb "' . $db_sym . '" for "' . $name . '"');
                    }
                }

                // if still not found report the missing link
                if ($db_sym == '' and $name <> '') {
                    $this->ref_text_dirty = true;
                    $this->err_text .= 'No word, triple, formula or verb found for "' . $name . '". ';
                }

                $result = $left . $db_sym . $right;
                log_debug('exchanged to "' . $result . '"');

                // find the next word
                $start = strlen($left) + strlen($db_sym);
                $end = false;
                if ($start < strlen($result)) {
                    log_debug('start "' . $start . '"');
                    $pos = strpos($result, self::TERM_DELIMITER, $start);
                    if ($pos !== false) {
                        log_debug('pos "' . $pos . '"');
                        $end = strpos($result, self::TERM_DELIMITER, $pos + 1);
                    }
                }
            }

            log_debug('done "' . $result . '"');
        }
        return $result;
    }

    /**
     * @param term $trm the term that should be used to create the database reference symbol
     * @return string the database reference symbol e.g. {w1} for word with the id 1
     */
    private function get_db_sym(term $trm): string
    {
        $db_sym = '';
        if ($trm->is_word()) {
            $db_sym = self::WORD_START . $trm->id_obj() . self::WORD_END;
        } elseif ($trm->is_triple()) {
            $db_sym = self::TRIPLE_START . $trm->id_obj() . self::TRIPLE_END;
        } elseif ($trm->is_formula()) {
            $db_sym = self::FORMULA_START . $trm->id_obj() . self::FORMULA_END;
        } elseif ($trm->is_verb()) {
            $db_sym = self::VERB_START . $trm->id_obj() . self::VERB_END;
        }
        return $db_sym;
    }

    /**
     * get the next term from the expression part in the database reference format
     *
     * @param string $frm_part_ref_text
     * @param term_list|null $trm_lst
     * @return term|null
     */
    private function get_next_term_from_ref(string $frm_part_ref_text, term_list $trm_lst = null): ?term
    {
        $trm = null;

        $lib = new library();

        // get a word
        $id = $lib->str_between($frm_part_ref_text, self::WORD_START, self::WORD_END);
        if ($id > 0) {
            $wrd = $trm_lst?->word_by_id($id);
            if ($wrd == null) {
                $wrd = new word($this->usr);
                $wrd->load_by_id($id, word::class);
                if ($wrd->id() == 0) {
                    $wrd = null;
                }
            }
            if ($wrd == null) {
                $this->usr_text_dirty = true;
                log_warning('Word with id ' . $id . ' not found');
            } else {
                $trm = $wrd->term();
            }
        }

        // get a triple
        if ($id == "") {
            $id = $lib->str_between($frm_part_ref_text, self::TRIPLE_START, self::TRIPLE_END);
            if ($id > 0) {
                $trp = $trm_lst?->triple_by_id($id);
                if ($trp == null) {
                    $trp = new triple($this->usr);
                    $trp->load_by_id($id);
                    if ($trp->id() == 0) {
                        $trp = null;
                    }
                }
                if ($trp == null) {
                    $this->usr_text_dirty = true;
                    log_warning('Triple with id ' . $id . ' not found');
                } else {
                    $trm = $trp->term();
                }
            }
        }

        // get a formulas
        if ($id == "") {
            $id = $lib->str_between($frm_part_ref_text, self::FORMULA_START, self::FORMULA_END);
            if ($id > 0) {
                $frm = $trm_lst?->formula_by_id($id);
                if ($frm == null) {
                    $frm = new formula($this->usr);
                    $frm->load_by_id($id, formula::class);
                    if ($frm->id() == 0) {
                        $frm = null;
                    }
                }
                if ($frm == null) {
                    $this->usr_text_dirty = true;
                    log_warning('Formula with id ' . $id . ' not found');
                } else {
                    $trm = $frm->term();
                }
            }
        }

        // get a verbs
        if ($id == "") {
            $id = $lib->str_between($frm_part_ref_text, self::VERB_START, self::VERB_END);
            if ($id > 0) {
                $vrb = $trm_lst?->verb_by_id($id);
                if ($vrb == null) {
                    $vrb = new verb;
                    $vrb->set_user($this->usr);
                    $vrb->load_by_id($id);
                    if ($vrb->id() == 0) {
                        $vrb = null;
                    }
                }
                if ($vrb == null) {
                    $this->usr_text_dirty = true;
                    log_warning('Verb with id ' . $id . ' not found');
                } else {
                    $trm = $vrb->term();
                }
            }
        }

        return $trm;
    }


    /*
     * to review
     */

    /**
     * similar to phr_lst, but
     * e.g. for "Sales" "differentiator" "Country" all "Country" words should be included
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
        return $this->usr_text;
    }

}
