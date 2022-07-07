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

class expression
{

    /*
     *  code links
     */

    // predefined type selectors potentially used also in other classes
    const SELECT_ALL = "all";        // to get all formula elements
    const SELECT_PHRASE = "phrases";    // to filter only the words from the expression element list
    const SELECT_VERB = "verbs";      // to filter only the verbs from the expression element list
    const SELECT_FORMULA = "formulas";   // to filter only the formulas from the expression element list
    const SELECT_VERB_WORD = "verb_words"; // to filter the words and the words implied by the verbs from the expression element list

    // to convert word, formula or verbs database reference to word or word list and in a second step to a value or value list
    const MAKER_WORD_START = '{t';   //
    const MAKER_WORD_END = '}';    //
    const MAKER_TRIPLE_START = '{l';   //
    const MAKER_TRIPLE_END = '}';    //
    const MAKER_FORMULA_START = '{f';   //
    const MAKER_FORMULA_END = '}';    //

    /*
     *  object vars
     */

    public ?string $usr_text = null;   // the formula expression in the human-readable format
    public ?string $ref_text = null;   // the formula expression with the database references
    public ?string $err_text = null;   // description of the problems that appeared during the conversion from the human-readable to the database reference format
    public user $usr;                  // to get the user settings for the conversion
    public ?phrase_list $fv_phr_lst = null;  // list object of the words that should be added to the formula result
    public ?phrase_list $phr_lst = null;     // list of the word ids that are used for the formula result

    function __construct(user $usr)
    {
        $this->usr = $usr;
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
        log_debug('expression->get_ref_id >' . $ref_text . '<');
        $result = 0;

        $pos_start = strpos($ref_text, $start_maker);
        if ($pos_start !== false) {
            $r_part = zu_str_right_of($ref_text, $start_maker);
            $l_part = zu_str_left_of($r_part, $end_maker);
            if (is_numeric($l_part)) {
                $result = $l_part;
                log_debug('expression->get_ref_id -> part "' . $result . '"');
            }
        }

        log_debug('expression->get_ref_id -> "' . $result . '"');
        return $result;
    }

    /**
     * returns a positive word id if the formula string in the database format contains a word link
     * @return int the word id found in the reference text or zero if no word id is found
     */
    private function get_wrd_id(string $ref_text): int
    {
        log_debug('expression->get_wrd_id "' . $ref_text . '"');
        return $this->get_ref_id($ref_text, self::MAKER_WORD_START, self::MAKER_WORD_END);
    }

    private function get_frm_id(string $ref_text): int
    {
        log_debug('expression->get_wrd_id "' . $ref_text . '"');
        return $this->get_ref_id($ref_text, self::MAKER_FORMULA_START, self::MAKER_FORMULA_END);
    }

    /**
     * find the position of the formula indicator "="
     * use the part left of it to add the words to the result
     */
    function fv_part(): string
    {
        $result = zu_str_left_of($this->ref_text, ZUP_CHAR_CALC);
        return trim($result);
    }

    function fv_part_usr(): string
    {
        $result = zu_str_left_of($this->usr_text, ZUP_CHAR_CALC);
        return trim($result);
    }

    function r_part(): string
    {
        $result = zu_str_right_of($this->ref_text, ZUP_CHAR_CALC);
        return trim($result);
    }

    function r_part_usr(): string
    {
        $result = zu_str_right_of($this->usr_text, ZUP_CHAR_CALC);
        return trim($result);
    }

    /**
     * get the phrases that should be added to the result of a formula
     * e.g. for >"percent" = ( "this" - "prior" ) / "prior"< a list with the phrase "percent" will be returned
     * @return phrase_list|null with the phrase that should be added to the result
     */
    function fv_phr_lst(): ?phrase_list
    {
        log_debug('expression->fv_phr_lst >' . $this->ref_text . '< and user ' . $this->usr->name . '"');
        $phr_lst = null;
        $wrd_ids = array();

        // create a local copy of the reference text not to modify the original text
        $ref_text = $this->fv_part();

        if ($ref_text <> "") {
            // add words to selection
            $new_wrd_id = $this->get_wrd_id($ref_text);
            while ($new_wrd_id > 0) {
                if (!in_array($new_wrd_id, $wrd_ids)) {
                    $wrd_ids[] = $new_wrd_id;
                }
                $ref_text = zu_str_right_of($ref_text, self::MAKER_WORD_START . $new_wrd_id . self::MAKER_WORD_END);
                $new_wrd_id = $this->get_wrd_id($ref_text);
            }
            $phr_lst = new phrase_list($this->usr);
            if (count($wrd_ids) > 0) {
                $phr_lst->load_by_ids((new phr_ids($wrd_ids)));
            }
            //$phr_lst->ids = $wrd_ids;
            //$phr_lst->load();
            log_debug('expression->fv_phr_lst -> ' . $phr_lst->dsp_name());
        }

        log_debug('expression->fv_phr_lst -> done');
        $this->fv_phr_lst = $phr_lst;
        return $phr_lst;
    }

    /**
     * extracts an array with the words from a given formula text and load the words
     */
    function phr_lst(): ?phrase_list
    {
        log_debug('expression->phr_lst "' . $this->ref_text . ',u' . $this->usr->name . '"');
        $phr_lst = null;
        $wrd_ids = array();

        // create a local copy of the reference text not to modify the original text
        $ref_text = $this->r_part();

        if ($ref_text <> "") {
            // add words to selection
            $new_wrd_id = $this->get_wrd_id($ref_text);
            while ($new_wrd_id > 0) {
                if (!in_array($new_wrd_id, $wrd_ids)) {
                    $wrd_ids[] = $new_wrd_id;
                }
                $ref_text = zu_str_right_of($ref_text, self::MAKER_WORD_START . $new_wrd_id . self::MAKER_WORD_END);
                log_debug('remaining: ' . $ref_text);
                $new_wrd_id = $this->get_wrd_id($ref_text);
            }

            // load the word parameters
            $phr_lst = new phrase_list($this->usr);
            if (!empty($wrd_ids)) {
                $phr_lst->load_by_ids((new phr_ids($wrd_ids)));
            }
        }

        if ($phr_lst != null) {
            log_debug('expression->phr_lst -> ' . $phr_lst->dsp_name());
        }
        $this->phr_lst = $phr_lst;
        return $phr_lst;
    }

    /**
     * create a list of all formula elements
     * with the $type parameter the result list can be filtered
     * the filter is done within this function, because e.g. a verb can increase the number of words to return
     * if group it is true, element groups instead of single elements are returned
     */
    private function element_lst_all($type, $group_it, string $back = '')
    {
        log_debug('expression->element_lst_all get ' . $type . ' out of "' . $this->ref_text . '" for user ' . $this->usr->name);

        // init result and work vars
        $lst = array();
        if ($group_it) {
            $result = new formula_element_group_list;
            $elm_grp = new formula_element_group;
            $elm_grp->usr = $this->usr;
        } else {
            $result = new formula_element_list($this->usr);
        }
        $result->usr = $this->usr;
        $work = $this->r_part();
        if (is_null($type) or $type == "") {
            $type = expression::SELECT_ALL;
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
                    $obj_id = zu_str_between($work, self::MAKER_WORD_START, self::MAKER_WORD_END);
                    if (is_numeric($obj_id)) {
                        if ($obj_id > 0) {
                            $elm->type = formula_element::TYPE_WORD;
                            $wrd = new word($this->usr);
                            $wrd->id = $obj_id;
                            $elm->obj = $wrd;
                            $pos = strpos($work, self::MAKER_WORD_START);
                            log_debug('expression->element_lst_all -> wrd pos ' . $pos);
                        }
                    }
                }

                // find the next verb reference
                if ($type == expression::SELECT_ALL or $type == expression::SELECT_VERB) {
                    $new_pos = strpos($work, self::MAKER_TRIPLE_START);
                    log_debug('expression->element_lst_all -> verb pos ' . $new_pos);
                    if ($new_pos < $pos) {
                        $obj_id = zu_str_between($work, self::MAKER_TRIPLE_START, self::MAKER_TRIPLE_END);
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
                    $new_pos = strpos($work, self::MAKER_FORMULA_START);
                    log_debug('expression->element_lst_all -> frm pos ' . $new_pos);
                    if ($new_pos < $pos) {
                        $obj_id = zu_str_between($work, self::MAKER_FORMULA_START, self::MAKER_FORMULA_END);
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
                    if ($elm->obj->id > 0) {
                        $elm->usr = $this->usr;
                        $elm->back = $back;
                        $elm->load($elm->obj->id);

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
                                $new_pos = strpos($work, self::MAKER_WORD_START);
                                if ($new_pos < $next_pos) {
                                    $obj_id = zu_str_between($work, self::MAKER_WORD_START, self::MAKER_WORD_END);
                                    if (is_numeric($obj_id)) {
                                        if ($obj_id > 0) {
                                            $next_pos = $new_pos;
                                            log_debug('expression->element_lst_all -> next_pos shorter by word ' . $next_pos);
                                        }
                                    }
                                }
                                $new_pos = strpos($work, self::MAKER_TRIPLE_START);
                                if ($new_pos < $next_pos) {
                                    $obj_id = zu_str_between($work, self::MAKER_TRIPLE_START, self::MAKER_TRIPLE_END);
                                    if (is_numeric($obj_id)) {
                                        if ($obj_id > 0) {
                                            $next_pos = $new_pos;
                                            log_debug('expression->element_lst_all -> next_pos shorter by verb ' . $next_pos);
                                        }
                                    }
                                }
                                $new_pos = strpos($work, self::MAKER_FORMULA_START);
                                if ($new_pos < $next_pos) {
                                    $obj_id = zu_str_between($work, self::MAKER_FORMULA_START, self::MAKER_FORMULA_END);
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
     * get a list of all formula elements (don't use for number retrieval, use element_grp_lst instead, because )
     */
    function element_lst($back)
    {
        return $this->element_lst_all(expression::SELECT_ALL, FALSE, $back);
    }

    /**
     * a formula element group is a group of words, verbs, phrases or formula that retrieve a value or a list of values
     * e.g. with "Sector" "differentiator" all
     */
    function element_grp_lst(string $back = '')
    {
        return $this->element_lst_all(expression::SELECT_ALL, TRUE, $back);
    }

    /**
     * similar to phr_lst, but
     * e.g. for "Sales" "differentiator" "Country" all "Country" words should be included
     * TODO should also include the words implied by the verbs
     */
    function phr_verb_lst(string $back = ''): phrase_list
    {
        log_debug('expression->phr_verb_lst');
        $elm_lst = $this->element_lst_all(expression::SELECT_PHRASE, FALSE, $back);
        log_debug('expression->phr_verb_lst -> got ' . dsp_count($elm_lst->lst) . ' formula elements');
        $phr_lst = new phrase_list($this->usr);
        foreach ($elm_lst->lst as $elm) {
            log_debug('expression->phr_verb_lst -> check elements ' . $elm->name());
            if ($elm->type == 'formula') {
                if (isset($elm->wrd_obj)) {
                    $phr = $elm->wrd_obj->phrase();
                    $phr_lst->lst[] = $phr;
                } else {
                    log_err('Word missing for formula element ' . $elm->dsp_id . '.', 'expression->phr_verb_lst');
                }
            } else {
                $phr_lst->lst[] = $elm;
            }
        }
        // TODO check if the phrases are already loaded
        //$phr_lst->load();
        log_debug('expression->phr_verb_lst -> ' . dsp_count($phr_lst->lst));
        return $phr_lst;
    }

    /**
     * list of elements (in this case only formulas) that are of the predefined type "following", e.g. "this", "next" and "prior"
     */
    function element_special_following(string $back = ''): phrase_list
    {
        $phr_lst = new phrase_list($this->usr);
        $elm_lst = $this->element_lst_all(expression::SELECT_ALL, FALSE, $back);
        if (!empty($elm_lst->lst)) {
            foreach ($elm_lst->lst as $elm) {
                if ($elm->frm_type == formula::THIS
                    or $elm->frm_type == formula::NEXT
                    or $elm->frm_type == formula::PREV) {
                    $phr_lst->lst[] = $elm->wrd_obj;
                }
            }
            /* TODO check if the phrases are already loaded
            if (!empty($phr_lst->lst)) {
                $phr_lst->load();
            }
            */
        }

        log_debug('expression->element_special_following -> ' . dsp_count($phr_lst->lst));
        return $phr_lst;
    }

    /**
     * similar to element_special_following, but returns the formula and not the word
     */
    function element_special_following_frm(string $back = ''): formula_list
    {
        $frm_lst = new formula_list($this->usr);
        $elm_lst = $this->element_lst_all(expression::SELECT_ALL, FALSE, $back);
        if (!empty($elm_lst->lst)) {
            foreach ($elm_lst->lst as $elm) {
                if ($elm->frm_type == formula::THIS
                    or $elm->frm_type == formula::NEXT
                    or $elm->frm_type == formula::PREV) {
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
     * e.g. converts "={t6}{l12}/{f19}" to "='Sales' 'differentiator'/'Total Sales'"
     */
    private function get_usr_part($formula)
    {
        log_debug('expression->get_usr_part >' . $formula . '< and user ' . $this->usr->name);
        $result = $formula;

        // replace the words
        $id = zu_str_between($result, self::MAKER_WORD_START, self::MAKER_WORD_END);
        while ($id > 0) {
            $db_sym = self::MAKER_WORD_START . $id . self::MAKER_WORD_END;
            $wrd = new word($this->usr);
            $wrd->id = $id;
            $wrd->load();
            $result = str_replace($db_sym, ZUP_CHAR_WORD . $wrd->name . ZUP_CHAR_WORD, $result);
            $id = zu_str_between($result, self::MAKER_WORD_START, self::MAKER_WORD_END);
        }

        // replace the formulas
        $id = zu_str_between($result, self::MAKER_FORMULA_START, self::MAKER_FORMULA_END);
        while ($id > 0) {
            $db_sym = self::MAKER_FORMULA_START . $id . self::MAKER_FORMULA_END;
            $frm = new formula($this->usr);
            $frm->id = $id;
            $frm->load();
            $result = str_replace($db_sym, ZUP_CHAR_WORD . $frm->name . ZUP_CHAR_WORD, $result);
            $id = zu_str_between($result, self::MAKER_FORMULA_START, self::MAKER_FORMULA_END);
        }

        // replace the verbs
        $id = zu_str_between($result, self::MAKER_TRIPLE_START, self::MAKER_TRIPLE_END);
        while ($id > 0) {
            $db_sym = self::MAKER_TRIPLE_START . $id . self::MAKER_TRIPLE_END;
            $vrb = new verb;
            $vrb->id = $id;
            $vrb->usr = $this->usr;
            $vrb->load();
            $result = str_replace($db_sym, ZUP_CHAR_WORD . $vrb->name . ZUP_CHAR_WORD, $result);
            $id = zu_str_between($result, self::MAKER_TRIPLE_START, self::MAKER_TRIPLE_END);
        }

        log_debug('expression->get_usr_part -> "' . $result . '"');
        return $result;
    }

    /**
     * convert the database reference format to the user text
     */
    function get_usr_text(): string
    {
        log_debug('expression->get_usr_text >' . $this->ref_text . '< and user ' . $this->usr->name);
        $result = '';

        // check the formula indicator "=" and convert the left and right part separately
        $pos = strpos($this->ref_text, ZUP_CHAR_CALC);
        if ($pos > 0) {
            $left_part = $this->fv_part();
            $right_part = $this->r_part();
            log_debug('expression->get_usr_text -> (l:' . $left_part . ',r:' . $right_part . '"');
            $left_part = $this->get_usr_part($left_part);
            $right_part = $this->get_usr_part($right_part);
            $result = $left_part . ZUP_CHAR_CALC . $right_part;
        }

        log_debug('expression->get_usr_text ... done "' . $result . '"');
        return $result;
    }

    /**
     * converts a formula from the user text format to the database reference format
     * e.g. converts "='Sales' 'differentiator'/'Total Sales'" to "={t6}{l12}/{f19}"
     */
    private function get_ref_part($formula)
    {
        log_debug('expression->get_ref_part "' . $formula . ',' . $this->usr->name . '"');
        $result = $formula;

        if ($formula != '') {
            // find the first word
            $start = 0;
            $pos = strpos($result, ZUP_CHAR_WORD, $start);
            $end = strpos($result, ZUP_CHAR_WORD, $pos + 1);
            while ($end !== False) {
                // for 12'45'78: pos = 2, end = 5, name = 45, left = 12. right = 78
                $name = substr($result, $pos + 1, $end - $pos - 1);
                $left = substr($result, 0, $pos);
                $right = substr($result, $end + 1);
                log_debug('expression->get_ref_part -> name "' . $name . '" (' . $end . ') left "' . $left . '" (' . $pos . ') right "' . $right . '"');

                $db_sym = '';

                // check for formulas first, because for every formula a word is also existing
                // similar to a part in get_usr_part, maybe combine
                $frm = new formula($this->usr);
                $frm->name = $name;
                $frm->load();
                if ($frm->id > 0) {
                    $db_sym = self::MAKER_FORMULA_START . $frm->id . self::MAKER_FORMULA_END;
                    log_debug('expression->get_ref_part -> found formula "' . $db_sym . '" for "' . $name . '"');
                }

                // check for words
                if ($db_sym == '') {
                    $wrd = new word($this->usr);
                    $wrd->name = $name;
                    $wrd->load();
                    if ($wrd->id > 0) {
                        $db_sym = self::MAKER_WORD_START . $wrd->id . self::MAKER_WORD_END;
                        log_debug('expression->get_ref_part -> found word "' . $db_sym . '" for "' . $name . '"');
                    }
                }

                // check for verbs
                if ($db_sym == '') {
                    $vrb = new verb;
                    $vrb->name = $name;
                    $vrb->usr = $this->usr;
                    $vrb->load();
                    if ($vrb->id > 0) {
                        $db_sym = self::MAKER_TRIPLE_START . $vrb->id . self::MAKER_TRIPLE_END;
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
                    $pos = strpos($result, ZUP_CHAR_WORD, $start);
                    if ($pos !== false) {
                        log_debug('expression->get_ref_part -> pos "' . $pos . '"');
                        $end = strpos($result, ZUP_CHAR_WORD, $pos + 1);
                    }
                }
            }

            log_debug('expression->get_ref_part -> done "' . $result . '"');
        }
        return $result;
    }

    /**
     * convert the user text to the database reference format
     */
    function get_ref_text()
    {
        log_debug('expression->get_ref_text ' . $this->dsp_id());
        $result = '';

        // check the formula indicator "=" and convert the left and right part separately
        $pos = strpos($this->usr_text, ZUP_CHAR_CALC);
        if ($pos >= 0) {
            $left_part = $this->fv_part_usr();
            $right_part = $this->r_part_usr();
            log_debug('expression->get_ref_text -> (l:' . $left_part . ',r:' . $right_part . '"');
            $left_part = $this->get_ref_part($left_part);
            $right_part = $this->get_ref_part($right_part);
            $result = $left_part . ZUP_CHAR_CALC . $right_part;
        }

        // remove all spaces because they are not relevant for calculation and to avoid too much recalculation
        $result = str_replace(" ", "", $result);

        log_debug('expression->get_ref_text -> done "' . $result . '"');
        return $result;
    }

    /**
     * returns true if the formula contains a word, verb or formula link
     */
    function has_ref(): bool
    {
        log_debug('expression->has_ref ' . $this->dsp_id());
        $result = false;

        if ($this->get_wrd_id($this->ref_text) > 0
            or $this->get_frm_id($this->ref_text) > 0
            or $this->get_ref_id($this->ref_text, self::MAKER_WORD_START, self::MAKER_WORD_END) > 0
            or $this->get_ref_id($this->ref_text, self::MAKER_FORMULA_START, self::MAKER_FORMULA_END) > 0) {
            $result = true;
        }

        log_debug('expression->has_ref -> done ' . zu_dsp_bool($result));
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

}