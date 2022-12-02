<?php

/*

    expression.php - a text (usually in the database reference format) that implies a data selection and can calculate a number
    --------------

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

use cfg\formula_type;

class expression
{

    /* sample

    original request: formula: increase, words: "Nestlé", "turnover"
    formula "increase": (next[] - last[]) / last[]
    formula "next": needs time jump value[is time jump for
    so                       -> next["time jump"->,         "follower of"->"Now"]
    1. find_missing_word_types: next["time jump"->"Company","follower of"->"Now"]
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

    syntax: function_name["link_type->word_type:word_name"]
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
      -> find_missing_word_types("Nestlé", "turnover") with formula "increase" (zu_word_find_missing_types ($word_array, $formula_id))
        -> increase needs time_jump word_type
      -> assume_missing words("Nestlé", "turnover") with word_type "time_jump"
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

    */


    /*
     * code links
     */

    // predefined type selectors potentially used also in other classes
    const SELECT_ALL = "all";        // to get all formula elements
    const SELECT_PHRASE = "phrases";    // to filter only the words from the expression element list
    const SELECT_VERB = "verbs";      // to filter only the verbs from the expression element list
    const SELECT_FORMULA = "formulas";   // to filter only the formulas from the expression element list
    const SELECT_VERB_WORD = "verb_words"; // to filter the words and the words implied by the verbs from the expression element list

    // text maker to convert phrase, formula or verb database reference to
    // a phrase or phrase list and in a second step to a value or value list
    const WORD_START = '{t';   //
    const WORD_END = '}';    //
    const TRIPLE_START = '{l';   //
    const TRIPLE_END = '}';    //
    const FORMULA_START = '{f';   //
    const FORMULA_END = '}';    //

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
    const OPER_ADD = '+';    //
    const OPER_SUB = '-';    //
    const OPER_MUL = '*';    //
    const OPER_DIV = '/';    //

    const OPER_AND = '&';    //
    const OPER_OR = '|';    //

    // fixed functions
    const FUNC_IF = 'if';    //
    const FUNC_SUM = 'sum';    //
    const FUNC_ISNUM = 'is.numeric';    //


    /*
     * object vars
     */

    public ?string $usr_text = null;   // the formula expression in the human-readable format
    public ?string $ref_text = null;   // the formula expression with the database references
    public ?string $err_text = null;   // description of the problems that appeared during the conversion from the human-readable to the database reference format
    public user $usr;                  // to get the user settings for the conversion
    public ?phrase_list $fv_phr_lst = null;  // list object of the words that should be added to the formula result
    public ?phrase_list $phr_lst = null;     // list of the phrase ids that are used for the formula result

    function __construct(user $usr)
    {
        $this->usr = $usr;
    }

    /*
     * the main interface functions
     */

    /**
     * @returns phrase_list with the phrases from a given formula text and load the phrases
     */
    function phr_lst(): phrase_list
    {
        $phr_lst = new phrase_list($this->usr);
        $phr_ids = $this->phr_id_lst($this->r_part());
        $phr_lst->load_by_ids($phr_ids);

        return $phr_lst;
    }

    /**
     * @returns phrase_list with the phrases that should be added to the result of a formula
     * e.g. for >"percent" = ( "this" - "prior" ) / "prior"< a list with the phrase "percent" will be returned
     */
    function fv_phr_lst(): phrase_list
    {
        $phr_lst = new phrase_list($this->usr);
        $phr_ids = $this->phr_id_lst($this->fv_part());
        $phr_lst->load_by_ids($phr_ids);

        return $phr_lst;
    }

    /**
     * @return formula_element_list a list of all formula elements
     * (don't use for number retrieval, use element_grp_lst instead, because )
     */
    function element_lst(): formula_element_list|formula_element_group_list
    {
        return $this->element_lst_all(expression::SELECT_ALL, FALSE);
    }

    /**
     * a formula element group is a group of words, verbs, phrases or formula that retrieve a value or a list of values
     * e.g. with "Sector" "differentiator" all
     */
    function element_grp_lst(): formula_element_list|formula_element_group_list
    {
        return $this->element_lst_all(expression::SELECT_ALL, TRUE);
    }

    /*
     * functions public just for testing
     */

    /**
     * @returns phr_ids with the word and triple ids from a given formula text and without loading the objects from the database
     */
    function phr_id_lst(string $ref_text): phr_ids
    {
        $id_lst = [];

        if ($ref_text <> "") {
            // add phrase ids to selection
            $new_phr_id = $this->get_phr_id($ref_text);
            while ($new_phr_id != 0) {
                $id_lst[] = $new_phr_id;
                $ref_text = zu_str_right_of($ref_text, self::WORD_START . $new_phr_id . self::WORD_END);
                $new_phr_id = $this->get_phr_id($ref_text);
            }
        }

        return new phr_ids($id_lst);
    }

    /**
     * @returns phrase_list with the word and triple ids from a given formula text and without loading the objects from the database
     */
    function phr_id_lst_as_phr_lst(string $ref_text): phrase_list
    {
        $phr_lst = new phrase_list($this->usr);

        if ($ref_text <> "") {
            // add phrases to selection
            $new_phr_id = $this->get_phr_id($ref_text);
            while ($new_phr_id != 0) {
                $phr_lst->add_id($new_phr_id);
                $ref_text = zu_str_right_of($ref_text, self::WORD_START . $new_phr_id . self::WORD_END);
                $new_phr_id = $this->get_phr_id($ref_text);
            }
        }

        $this->phr_lst = $phr_lst;
        return $phr_lst;
    }

    /**
     * convert the user text to the database reference format
     * @param term_list $trm_lst a list of preloaded terms that should be used for the transformation
     * @returns string the expression in the database reference format
     */
    function get_ref_text(term_list $trm_lst = null): string
    {
        $result = '';

        // check the formula indicator "=" and convert the left and right part separately
        $pos = strpos($this->usr_text, self::CHAR_CALC);
        if ($pos >= 0) {
            $left_part = $this->fv_part_usr();
            $right_part = $this->r_part_usr();
            $left_part = $this->get_ref_part($left_part, $trm_lst);
            $right_part = $this->get_ref_part($right_part, $trm_lst);
            $result = $left_part . self::CHAR_CALC . $right_part;
        }

        // remove all spaces because they are not relevant for calculation and to avoid too much recalculation
        return str_replace(" ", "", $result);
    }

    /**
     * @return string the formula expression converted to the user text from the database reference format
     * e.g. converts "{t5}={t6}{l12}/{f19}" to "'percent' = 'Sales' 'differentiator'/'Total Sales'"
     */
    function get_usr_text(): string
    {
        log_debug('expression->get_usr_text >' . $this->ref_text . '< and user ' . $this->usr->name);
        $result = '';

        // check the formula indicator "=" and convert the left and right part separately
        $pos = strpos($this->ref_text, self::CHAR_CALC);
        if ($pos > 0) {
            $left_part = $this->fv_part();
            $right_part = $this->r_part();
            log_debug('expression->get_usr_text -> (l:' . $left_part . ',r:' . $right_part . '"');
            $left_part = $this->get_usr_part($left_part);
            $right_part = $this->get_usr_part($right_part);
            $result = $left_part . self::CHAR_CALC . $right_part;
        }

        log_debug('expression->get_usr_text ... done "' . $result . '"');
        return $result;
    }

    /**
     * find the position of the formula indicator "="
     * use the part left of it to add the words to the result
     */
    public function fv_part(): string
    {
        $result = zu_str_left_of($this->ref_text, self::CHAR_CALC);
        return trim($result);
    }

    function fv_part_usr(): string
    {
        $result = zu_str_left_of($this->usr_text, self::CHAR_CALC);
        return trim($result);
    }

    function r_part(): string
    {
        $result = zu_str_right_of($this->ref_text, self::CHAR_CALC);
        return trim($result);
    }

    function r_part_usr(): string
    {
        $result = zu_str_right_of($this->usr_text, self::CHAR_CALC);
        return trim($result);
    }

    /**
     * @returns bool true if the formula contains a word, verb or formula link
     */
    function has_ref(): bool
    {
        log_debug($this->dsp_id());
        $result = false;

        if ($this->get_phr_id($this->ref_text) > 0
            or $this->get_frm_id($this->ref_text) > 0
            or $this->get_ref_id($this->ref_text, self::WORD_START, self::WORD_END) > 0
            or $this->get_ref_id($this->ref_text, self::FORMULA_START, self::FORMULA_END) > 0) {
            $result = true;
        }

        log_debug('done ' . zu_dsp_bool($result));
        return $result;
    }

    /*
     * display functions
     */

    /**
     * format the expression name to use it for debugging
     */
    function dsp_id(): string
    {
        // $result = '"' . $this->usr_text . '" (' . $this->ref_text . ')';
        // the user is no most cases no extra info
        // $result .= ' for user '.$this->usr->name.'';
        return '"' . $this->usr_text . '" (' . $this->ref_text . ')';
    }

    function name(): string
    {
        return $this->usr_text;
    }

    /*
     * internal functions
     */

    /**
     * returns a phrase id if the formula string in the database format contains a phrase link
     * @param string $ref_text with the formula reference text e.g. ={f203}
     * @return int the phrase id found in the reference text or zero if no phrase id is found
     */
    private function get_phr_id(string $ref_text): int
    {
        return $this->get_ref_id($ref_text, self::WORD_START, self::WORD_END);
    }

    private function get_frm_id(string $ref_text): int
    {
        return $this->get_ref_id($ref_text, self::FORMULA_START, self::FORMULA_END);
    }

    /**
     * returns a positive reference (word, verb or formula) id if the formula string in the database format contains a database reference link
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

        $pos_start = strpos($ref_text, $start_maker);
        if ($pos_start !== false) {
            $r_part = zu_str_right_of($ref_text, $start_maker);
            $l_part = zu_str_left_of($r_part, $end_maker);
            if (is_numeric($l_part)) {
                $result = $l_part;
            }
        }

        return $result;
    }

    /**
     * create a list of all formula elements
     * with the $type parameter the result list can be filtered
     * the filter is done within this function, because e.g. a verb can increase the number of words to return
     * if group it is true, element groups instead of single elements are returned
     */
    private function element_lst_all(string $type = self::SELECT_ALL, bool $group_it = false): formula_element_list|formula_element_group_list
    {
        log_debug('expression->element_lst_all get ' . $type . ' out of "' . $this->ref_text . '" for user ' . $this->usr->name);

        // init result and work vars
        $lst = array();
        if ($group_it) {
            $result = new formula_element_group_list($this->usr);
            $elm_grp = new formula_element_group;
            $elm_grp->usr = $this->usr;
        } else {
            $result = new formula_element_list($this->usr);
        }
        $result->set_user($this->usr);
        $work = $this->r_part();
        if (is_null($type) or $type == "") {
            $type = self::SELECT_ALL;
        }

        if ($work == '') {
            // zu_warning ???
            log_warning('expression->element_lst_all -> work is empty', '', ' work is empty', (new Exception)->getTraceAsString(), $this->usr);
        } else {
            // loop over the formula text and replace ref by ref from left to right
            $found = true;
            $nbr = 0;
            while ($found and $nbr < MAX_LOOP) {
                log_debug('expression->element_lst_all -> in "' . $work . '"');
                $found = false;

                // $pos is the position von the next element
                // to list the elements from left to right, set it to the right most position at the beginning of each replacement
                $pos = strlen($work);
                $elm = new formula_element($this->usr);

                // find the next word reference
                if ($type == expression::SELECT_ALL or $type == expression::SELECT_PHRASE or $type == expression::SELECT_VERB_WORD) {
                    $obj_id = zu_str_between($work, self::WORD_START, self::WORD_END);
                    if (is_numeric($obj_id)) {
                        if ($obj_id > 0) {
                            $elm->type = formula_element::TYPE_WORD;
                            $wrd = new word($this->usr);
                            $wrd->set_id($obj_id);
                            $elm->obj = $wrd;
                            $pos = strpos($work, self::WORD_START);
                            log_debug('expression->element_lst_all -> wrd pos ' . $pos);
                        }
                    }
                }

                // find the next verb reference
                if ($type == expression::SELECT_ALL or $type == expression::SELECT_VERB) {
                    $new_pos = strpos($work, self::TRIPLE_START);
                    log_debug('expression->element_lst_all -> verb pos ' . $new_pos);
                    if ($new_pos < $pos) {
                        $obj_id = zu_str_between($work, self::TRIPLE_START, self::TRIPLE_END);
                        if (is_numeric($obj_id)) {
                            if ($obj_id > 0) {
                                $elm->type = formula_element::TYPE_VERB;
                                $vrb = new verb();
                                $vrb->id = $obj_id;
                                $elm->obj = $vrb;
                                $pos = $new_pos;
                            }
                        }
                    }
                }

                // find the next formula reference
                if ($type == expression::SELECT_ALL or $type == expression::SELECT_FORMULA or $type == expression::SELECT_PHRASE or $type == expression::SELECT_VERB_WORD) {
                    $new_pos = strpos($work, self::FORMULA_START);
                    log_debug('expression->element_lst_all -> frm pos ' . $new_pos);
                    if ($new_pos < $pos) {
                        $obj_id = zu_str_between($work, self::FORMULA_START, self::FORMULA_END);
                        if (is_numeric($obj_id)) {
                            if ($obj_id > 0) {
                                $elm->type = formula_element::TYPE_FORMULA;
                                $frm = new verb();
                                $frm->id = $obj_id;
                                $elm->obj = $frm;
                                $pos = $new_pos;
                            }
                        }
                    }
                }

                // add reference to result
                if ($elm->obj != null) {
                    if ($elm->obj->id() > 0) {
                        $elm->usr = $this->usr;
                        $elm->load_by_id($elm->obj->id());

                        // update work text
                        $changed = str_replace($elm->symbol, $elm->name, $work);
                        log_debug('expression->element_lst_all -> found "' . $elm->name . '" for ' . $elm->symbol . ', so "' . $work . '" is now "' . $changed . '"');
                        if ($changed <> $work) {
                            $work = $changed;
                            $found = true;
                            $pos = $pos + strlen($elm->name);
                        }

                        // group the references if needed
                        if ($group_it) {
                            $elm_grp->lst[] = $elm;
                            log_debug('expression->element_lst_all -> new group element "' . $elm->name . '"');

                            $txt_between_elm = '';
                            $next_pos = 0;
                            if ($pos > 0) {
                                // get the position of the next element to check if a new group should be created or added to the same
                                $next_pos = strlen($work);
                                log_debug('expression->element_lst_all -> next_pos ' . $next_pos);
                                $new_pos = strpos($work, self::WORD_START);
                                if ($new_pos < $next_pos) {
                                    $obj_id = zu_str_between($work, self::WORD_START, self::WORD_END);
                                    if (is_numeric($obj_id)) {
                                        if ($obj_id > 0) {
                                            $next_pos = $new_pos;
                                            log_debug('expression->element_lst_all -> next_pos shorter by word ' . $next_pos);
                                        }
                                    }
                                }
                                $new_pos = strpos($work, self::TRIPLE_START);
                                if ($new_pos < $next_pos) {
                                    $obj_id = zu_str_between($work, self::TRIPLE_START, self::TRIPLE_END);
                                    if (is_numeric($obj_id)) {
                                        if ($obj_id > 0) {
                                            $next_pos = $new_pos;
                                            log_debug('expression->element_lst_all -> next_pos shorter by verb ' . $next_pos);
                                        }
                                    }
                                }
                                $new_pos = strpos($work, self::FORMULA_START);
                                if ($new_pos < $next_pos) {
                                    $obj_id = zu_str_between($work, self::FORMULA_START, self::FORMULA_END);
                                    if (is_numeric($obj_id)) {
                                        if ($obj_id > 0) {
                                            $next_pos = $new_pos;
                                            log_debug('expression->element_lst_all -> next_pos shorter by formula  ' . $next_pos);
                                        }
                                    }
                                }

                                // get the text between the references
                                $len = $next_pos - $pos;
                                log_debug('expression->element_lst_all -> in "' . $work . '" after ' . $pos . ' len ' . $len . ' "' . $next_pos . ' - ' . $pos . ')');
                                $txt_between_elm = substr($work, $pos, $len);
                                log_debug('expression->element_lst_all -> between elements "' . $txt_between_elm . '" ("' . $work . '" from ' . $pos . ' to ' . $next_pos . ')');
                                $txt_between_elm = str_replace('"', '', $txt_between_elm);
                                $txt_between_elm = trim($txt_between_elm);
                            }
                            // check if the references does not have any math symbol in between and therefore are used to retrieve one value
                            if (strlen($txt_between_elm) > 0 or $next_pos == strlen($work)) {
                                $lst[] = $elm_grp;
                                log_debug('expression->element_lst_all -> group finished with ' . $elm->name);
                                $elm_grp = new formula_element_group;
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
        $result->lst = $lst;

        log_debug('expression->element_lst_all got -> ' . dsp_count($result->lst) . ' elements');
        return $result;
    }

    /**
     * similar to phr_lst, but
     * e.g. for "Sales" "differentiator" "Country" all "Country" words should be included
     * TODO should also include the words implied by the verbs
     */
    function phr_verb_lst(): phrase_list
    {
        log_debug('expression->phr_verb_lst');
        $elm_lst = $this->element_lst_all(expression::SELECT_PHRASE, FALSE);
        log_debug('expression->phr_verb_lst -> got ' . dsp_count($elm_lst->lst) . ' formula elements');
        $phr_lst = new phrase_list($this->usr);
        foreach ($elm_lst->lst as $elm) {
            log_debug('expression->phr_verb_lst -> check elements ' . $elm->name());
            if ($elm->type == 'formula') {
                if (isset($elm->wrd_obj)) {
                    $phr = $elm->wrd_obj->phrase();
                    $phr_lst->add($phr);
                } else {
                    log_err('Word missing for formula element ' . $elm->dsp_id . '.', 'expression->phr_verb_lst');
                }
            } elseif ($elm->type == formula_element::TYPE_WORD) {
                if (isset($elm->obj)) {
                    $phr = $elm->obj->phrase();
                    $phr_lst->add($phr);
                } else {
                    log_err('Word missing for formula element ' . $elm->dsp_id . '.', 'expression->phr_verb_lst');
                }
            } elseif ($elm->type == formula_element::TYPE_VERB) {
                log_err('Use Formula element ' . $elm->dsp_id . ' has an unexpected type.', 'expression->phr_verb_lst');
            } else {
                log_err('Formula element ' . $elm->dsp_id . ' has an unexpected type.', 'expression->phr_verb_lst');
            }
        }
        // TODO check if the phrases are already loaded
        //$phr_lst->load();
        log_debug('expression->phr_verb_lst -> ' . dsp_count($phr_lst->lst()));
        return $phr_lst;
    }

    /**
     * list of elements (in this case only formulas) that are of the predefined type "following", e.g. "this", "next" and "prior"
     */
    function element_special_following(): phrase_list
    {
        $phr_lst = new phrase_list($this->usr);
        $elm_lst = $this->element_lst_all(expression::SELECT_ALL, FALSE);
        if (!empty($elm_lst->lst)) {
            foreach ($elm_lst->lst as $elm) {
                if ($elm->frm_type == formula_type::THIS
                    or $elm->frm_type == formula_type::NEXT
                    or $elm->frm_type == formula_type::PREV) {
                    $phr_lst->add($elm->wrd_obj);
                }
            }
            /* TODO check if the phrases are already loaded
            if (!empty($phr_lst->lst)) {
                $phr_lst->load();
            }
            */
        }

        log_debug('expression->element_special_following -> ' . dsp_count($phr_lst->lst()));
        return $phr_lst;
    }

    /**
     * similar to element_special_following, but returns the formula and not the word
     */
    function element_special_following_frm(): formula_list
    {
        $frm_lst = new formula_list($this->usr);
        $elm_lst = $this->element_lst_all(expression::SELECT_ALL, FALSE);
        if (!empty($elm_lst->lst)) {
            foreach ($elm_lst->lst as $elm) {
                if ($elm->frm_type == formula_type::THIS
                    or $elm->frm_type == formula_type::NEXT
                    or $elm->frm_type == formula_type::PREV) {
                    $frm_lst->lst[] = $elm->obj;
                    $frm_lst->ids[] = $elm->id;
                }
            }
            log_debug('expression->element_special_following_frm -> pre load ' . dsp_count($frm_lst->lst));
            /*
            if (!empty($frm_lst->lst)) {
              $frm_lst->load();
            }
            */
        }

        log_debug('expression->element_special_following_frm -> ' . dsp_count($frm_lst->lst));
        return $frm_lst;
    }

    /**
     * converts a formula from the database reference format to the human-readable format
     * e.g. converts "{t6}{l12}/{f19}" to "'Sales' 'differentiator'/'Total Sales'"
     */
    private function get_usr_part($formula)
    {
        log_debug('expression->get_usr_part >' . $formula . '< and user ' . $this->usr->name);
        $result = $formula;

        // replace the words
        $id = zu_str_between($result, self::WORD_START, self::WORD_END);
        while ($id > 0) {
            $db_sym = self::WORD_START . $id . self::WORD_END;
            $wrd = new word($this->usr);
            $wrd->load_by_id($id, word::class);
            $result = str_replace($db_sym, self::TERM_DELIMITER . $wrd->name() . self::TERM_DELIMITER, $result);
            $id = zu_str_between($result, self::WORD_START, self::WORD_END);
        }

        // replace the formulas
        $id = zu_str_between($result, self::FORMULA_START, self::FORMULA_END);
        while ($id > 0) {
            $db_sym = self::FORMULA_START . $id . self::FORMULA_END;
            $frm = new formula($this->usr);
            $frm->load_by_id($id, formula::class);
            $result = str_replace($db_sym, self::TERM_DELIMITER . $frm->name() . self::TERM_DELIMITER, $result);
            $id = zu_str_between($result, self::FORMULA_START, self::FORMULA_END);
        }

        // replace the verbs
        $id = zu_str_between($result, self::TRIPLE_START, self::TRIPLE_END);
        while ($id > 0) {
            $db_sym = self::TRIPLE_START . $id . self::TRIPLE_END;
            $vrb = new verb;
            $vrb->id = $id;
            $vrb->set_user($this->usr);
            $vrb->load_by_vars();
            $result = str_replace($db_sym, self::TERM_DELIMITER . $vrb->name . self::TERM_DELIMITER, $result);
            $id = zu_str_between($result, self::TRIPLE_START, self::TRIPLE_END);
        }

        log_debug('expression->get_usr_part -> "' . $result . '"');
        return $result;
    }

    /**
     * converts a formula from the user text format to the database reference format
     * e.g. converts "='Sales' 'differentiator'/'Total Sales'" to "={t6}{l12}/{f19}"
     *
     * @param string $frm_part_text the expression text in user format that should be converted
     * @param term_list|null $trm_lst a list of preloaded terms that should be prevered used for the convesion
     * @return string the expression text in the database ref format
     *
     * TODO split into three steps
     *      1. get the names from the text
     *      2. load the terms by the names
     *      3. replace the names with the term ids
     */
    private function get_ref_part(string $frm_part_text, term_list $trm_lst = null): string
    {
        log_debug('expression->get_ref_part "' . $frm_part_text . ',' . $this->usr->name . '"');
        $result = $frm_part_text;

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
                log_debug('expression->get_ref_part -> name "' . $name . '" (' . $end . ') left "' . $left . '" (' . $pos . ') right "' . $right . '"');

                $db_sym = '';

                // check if the preloaded terms can be used for the conversion
                if ($trm_lst != null) {
                    $trm = $trm_lst->get_by_name($name);
                    if ($trm != null) {
                        if ($trm->id() > 0) {
                            $db_sym = self::FORMULA_START . $trm->id() . self::FORMULA_END;
                        }
                    }
                }


                // check for formulas first, because for every formula a word is also existing
                // similar to a part in get_usr_part, maybe combine
                $frm = new formula($this->usr);
                $frm->load_by_name($name, formula::class);
                if ($frm->id() > 0) {
                    $db_sym = self::FORMULA_START . $frm->id() . self::FORMULA_END;
                    log_debug('expression->get_ref_part -> found formula "' . $db_sym . '" for "' . $name . '"');
                }

                // check for words
                if ($db_sym == '') {
                    $wrd = new word($this->usr);
                    $wrd->load_by_name($name, word::class);
                    if ($wrd->id() > 0) {
                        $db_sym = self::WORD_START . $wrd->id() . self::WORD_END;
                        log_debug('expression->get_ref_part -> found word "' . $db_sym . '" for "' . $name . '"');
                    }
                }

                // check for verbs
                if ($db_sym == '') {
                    $vrb = new verb;
                    $vrb->name = $name;
                    $vrb->set_user($this->usr);
                    $vrb->load_by_vars();
                    if ($vrb->id > 0) {
                        $db_sym = self::TRIPLE_START . $vrb->id . self::TRIPLE_END;
                        log_debug('expression->get_ref_part -> found verb "' . $db_sym . '" for "' . $name . '"');
                    }
                }

                // if still not found report the missing link
                if ($db_sym == '' and $name <> '') {
                    $this->err_text .= 'No word, triple, formula or verb found for "' . $name . '". ';
                }

                $result = $left . $db_sym . $right;
                log_debug('expression->get_ref_part -> changed to "' . $result . '"');

                // find the next word
                $start = strlen($left) + strlen($db_sym);
                $end = false;
                if ($start < strlen($result)) {
                    log_debug('expression->get_ref_part -> start "' . $start . '"');
                    $pos = strpos($result, self::TERM_DELIMITER, $start);
                    if ($pos !== false) {
                        log_debug('expression->get_ref_part -> pos "' . $pos . '"');
                        $end = strpos($result, self::TERM_DELIMITER, $pos + 1);
                    }
                }
            }

            log_debug('expression->get_ref_part -> done "' . $result . '"');
        }
        return $result;
    }

    /**
     * @return array of the term names used in the expression based on the user text
     * e.g. converts "'Sales' 'differentiator' / 'Total Sales'" to "Sales, differentiator, Total Sales"
     */
    public function get_usr_names(): array
    {
        $result = [];
        $remaining = $this->usr_text;

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
                    log_debug('expression->get_ref_part -> start "' . $start . '"');
                    $pos = strpos($remaining, self::TERM_DELIMITER, $start);
                    if ($pos !== false) {
                        log_debug('expression->get_ref_part -> pos "' . $pos . '"');
                        $end = strpos($remaining, self::TERM_DELIMITER, $pos + 1);
                    }
                }
            }
        }
        return $result;
    }

}
