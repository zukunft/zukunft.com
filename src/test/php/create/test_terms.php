<?php

/*

    test/create/test_terms.php - create the test term objects
    --------------------------


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

namespace Zukunft\ZukunftCom\test\php\create;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::MODEL_HELPER . 'data_object.php';
include_once paths::MODEL_PHRASE . 'term.php';
include_once paths::MODEL_PHRASE . 'term_list.php';
include_once test_paths::UTILS . 'test_cleanup.php';

use Zukunft\ZukunftCom\main\php\cfg\helper\data_object;
use Zukunft\ZukunftCom\main\php\cfg\phrase\term;
use Zukunft\ZukunftCom\main\php\cfg\phrase\term_list;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class test_terms
{

    /*
     * init
     */

    // use the global test environment
    private test_cleanup $env;

    function __construct(test_cleanup $env)
    {
        $this->env = $env;
    }


    /*
     * unit
     */

    function term(): term
    {
        $t_wrd = new test_words($this->env);
        return $t_wrd->word()->term();
    }

    function term_triple(): term
    {
        $t_trp = new test_triples($this->env);
        return $t_trp->triple_impact()->term();
    }

    function term_triple_pi(): term
    {
        $t_trp = new test_triples($this->env);
        return $t_trp->triple_pi()->term();
    }

    function term_formula(): term
    {
        $t_frm = new test_formulas($this->env);
        return $t_frm->formula()->term();
    }

    function term_formula_increase(): term
    {
        $t_frm = new test_formulas($this->env);
        return $t_frm->formula_increase()->term();
    }

    function term_verb(): term
    {
        $t_vrb = new test_verbs($this->env);
        return $t_vrb->verb()->term();
    }

    function term_verb_is(): term
    {
        $t_vrb = new test_verbs($this->env);
        return $t_vrb->verb_is()->term();
    }

    /**
     * @return term_list with all terms used for the unit tests
     */
    function term_list_short(): term_list
    {
        $lst = new term_list($this->env->usr1);
        $lst->add($this->term());
        $lst->add($this->term_triple());
        $lst->add($this->term_formula());
        $lst->add($this->term_verb());
        return $lst;
    }

    /**
     * @return term_list with all terms used for the unit tests
     */
    function term_list(): term_list
    {
        $lst = new term_list($this->env->usr1);
        $lst->add($this->term());
        $lst->add($this->term_triple_pi());
        $lst->add($this->term_formula_increase());
        $lst->add($this->term_verb_is());
        return $lst;
    }

    /**
     * @return term_list with all terms used for the unit tests
     */
    function term_list_all(): term_list
    {
        $t_wrd = new test_words($this->env);
        $t_trp = new test_triples($this->env);
        $t_vrb = new test_verbs($this->env);
        $t_frm = new test_formulas($this->env);
        $lst = new term_list($this->env->usr1);
        $lst->add($this->term());
        $lst->add($this->term_triple());
        $lst->add($this->term_formula());
        $lst->add($this->term_verb());
        $lst->add($t_trp->triple_pi()->term());
        $lst->add($t_wrd->word_pi()->term());
        $lst->add($t_wrd->word_cf()->term());
        $lst->add($t_wrd->word_percent()->term());
        $lst->add($t_wrd->word_prior()->term());
        $lst->add($t_wrd->word_this()->term());
        $lst->add($t_wrd->word_parts()->term());
        $lst->add($t_wrd->word_total()->term());
        $lst->add($t_wrd->second()->term());
        $lst->add($t_wrd->word_minute()->term());
        $lst->add($t_vrb->verb_of()->term());
        $lst->add($t_vrb->verb_with()->term());
        $lst->add($t_wrd->word_one()->term());
        $lst->add($t_wrd->word_mio()->term());
        $lst->add($t_frm->formula_this()->term());
        $lst->add($t_frm->formula_prior()->term());
        return $lst;
    }

    /**
     * @return term_list a term list with the time terms e.g. minute and second
     */
    function term_list_time(): term_list
    {
        $t_wrd = new test_words($this->env);
        $t_trp = new test_triples($this->env);
        $lst = new term_list($this->env->usr1);
        $lst->add($t_trp->second()->term());
        $lst->add($t_wrd->word_minute()->term());
        $lst->add($t_wrd->word_hour()->term());
        return $lst;
    }

    /**
     * @return term_list a term list with the time terms e.g. minute and second
     */
    function term_list_years(): term_list
    {
        $t_wrd = new test_words($this->env);
        $lst = new term_list($this->env->usr1);
        $lst->add($t_wrd->word_2019()->term());
        $lst->add($t_wrd->word_2020()->term());
        return $lst;
    }

    /**
     * @return term_list the terms relevant for testing the increase formula
     */
    function term_list_increase(): term_list
    {
        $t_wrd = new test_words($this->env);
        $t_frm = new test_formulas($this->env);
        $t_vrb = new test_verbs($this->env);
        $lst = new term_list($this->env->usr1);
        $lst->add($t_wrd->word_percent()->term());
        $lst->add($t_frm->formula_this()->term());
        $lst->add($t_frm->formula_prior()->term());
        $lst->add($t_wrd->word_ch()->term());
        $lst->add($t_wrd->word_inhabitant()->term());
        $lst->add($t_wrd->word_2020()->term());
        $lst->add($t_wrd->word_mio()->term());
        $lst->add($t_vrb->verb_is_filled()->term());
        $lst->add($t_wrd->word_total()->term());
        $lst->add($t_wrd->word_city()->term());
        return $lst;
    }

    /**
     * @return term_list a term list with the scaling terms e.g. one and million
     */
    function term_list_scale(): term_list
    {
        $t_wrd = new test_words($this->env);
        $lst = new term_list($this->env->usr1);
        $lst->add($t_wrd->word_one()->term());
        $lst->add($t_wrd->word_mio()->term());
        return $lst;
    }

    /**
     * @return term_list a term list with the scaling terms and the million scaling formula
     */
    function term_list_scale_mio(): term_list
    {
        $t_frm = new test_formulas($this->env);
        $lst = $this->term_list_scale();
        $lst->add($t_frm->formula_scale_mio()->term());
        return $lst;
    }

    /**
     * @return data_object with the million scaling formula and its result word "one"
     *                     as loaded by value->scale for the scale calculation
     */
    function dto_scale_mio(): data_object
    {
        $t_wrd = new test_words($this->env);
        $t_frm = new test_formulas($this->env);
        $dto = new data_object($this->env->usr1);
        $dto->add_word($t_wrd->word_one());
        $dto->add_formula($t_frm->formula_scale_mio());
        return $dto;
    }

    /**
     * @return data_object without any scaling formula to test the missing formula warning
     */
    function dto_scale_none(): data_object
    {
        return new data_object($this->env->usr1);
    }

    /**
     * @return data_object with the million scaling formula but the result word "one"
     *                     missing the scaling type to test the scaling type check
     */
    function dto_scale_mio_unscaled(): data_object
    {
        $t_wrd = new test_words($this->env);
        $t_frm = new test_formulas($this->env);
        $dto = new data_object($this->env->usr1);
        $dto->add_word($t_wrd->word_one_unscaled());
        $dto->add_formula($t_frm->formula_scale_mio());
        return $dto;
    }

    /**
     * create a huge list of terms for speed testing
     * TODO review
     * @returns term_list a dummy term list for unit tests
     */
    function list_huge(test_cleanup $t, int $size): term_list
    {
        global $usr;
        $t_wrd = new test_words($t);
        $t_trp = new test_triples($t);
        $t_vrb = new test_verbs($t);
        $t_frm = new test_formulas($t);

        $lst = new term_list($usr);
        for ($i = 1; $i <= $size; $i++) {
            // first create at least two words, so that a triple can be created
            if ($i <= 2) {
                $lst->add($t_wrd->random($i)->term());
            } else {
                $type = rand(1, 4);
                if ($type == 1) {
                    $lst->add($t_wrd->random($i)->term());
                } elseif ($type == 2) {
                    $lst->add($t_trp->random($i, $lst->phrase_list(), $t)->term());
                } elseif ($type == 3) {
                    $lst->add($t_frm->random($i)->term());
                } elseif ($type == 4) {
                    $lst->add($t_vrb->random()->term());
                }
            }
        }
        return $lst;
    }

}