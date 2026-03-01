<?php

/*

    test/create/test_triples.php - create the test triple objects
    ----------------------------


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
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::MODEL_PHRASE . 'phrase.php';
include_once paths::MODEL_PHRASE . 'phrase_list.php';
include_once paths::MODEL_VERB . 'verb.php';
include_once paths::MODEL_WORD . 'triple.php';
include_once paths::MODEL_WORD . 'triple_list.php';
include_once paths::MODEL_WORD . 'word.php';
include_once paths::SHARED_CONST . 'triples.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED_TYPES . 'api_types.php';
include_once paths::SHARED_TYPES . 'phrase_types.php';
include_once paths::SHARED_TYPES . 'protection_types.php';
include_once html_paths::WORD . 'triple_list.php';
include_once test_paths::UTILS . 'test_cleanup.php';
include_once test_paths::UTILS . 'test_lib.php';

use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\cfg\word\triple_list;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\shared\types\phrase_types;
use Zukunft\ZukunftCom\main\php\shared\types\protection_types;
use Zukunft\ZukunftCom\main\php\web\word\triple_list as triple_list_ui;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;
use Zukunft\ZukunftCom\test\php\utils\test_lib;

class test_triples extends test_objects
{

    /*
     * cleanup
     */

    /**
     * delete any remaining test triples for a clean test start
     */
    function cleanup(string $ts): void
    {
        parent::cleanup_objects($ts, triples::TEST_TRIPLES, new triple($this->env->usr1));

        // also clean up the words used for the triples
        $t_wrd = new test_words($this->env);
        $t_wrd->cleanup($ts);
    }


    /*
     * unit
     */

    /**
     * @return triple "mathematical constant" used for unit testing
     */
    function triple(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triples::MATH_CONST_ID, triples::MATH_CONST);
        $trp->description = triples::MATH_CONST_COM;
        $trp->set_from($t_wrd->word_const()->phrase());
        $trp->set_verb($t_vrb->verb_part());
        $trp->set_to($t_wrd->word()->phrase());
        $trp->set_type(phrase_types::MATH_CONST, $this->env->usr1);
        global $sys;
        $trp->set_protection_id($sys->typ_lst->ptc_typ->id(protection_types::ADMIN));
        return $trp;
    }

    /**
     * @return triple object where the most specific mandatory var is not set which is in case of a word the id and the name of the to phrase
     */
    function triple_incomplete(): triple
    {
        $t_wrd = new test_words($this->env);
        $trp = $this->triple();
        $trp->set_to($t_wrd->word_incomplete()->phrase());
        return $trp;
    }

    /**
     * TODO PRIO 1
     * @return triple as it is returned at the moment via phrase list api, means without links
     */
    function triple_api(): triple
    {
        $trp = new triple($this->env->usr1);
        $trp->set(triples::MATH_CONST_ID, triples::MATH_CONST);
        $trp->description = triples::MATH_CONST_COM;
        $trp->set_type(phrase_types::MATH_CONST, $this->env->usr1);
        global $sys;
        $trp->set_protection_id($sys->typ_lst->ptc_typ->id(protection_types::ADMIN));
        return $trp;
    }

    /**
     * @return triple with all fields set and a reserved test name for testing the db write function
     */
    function triple_filled(): triple
    {
        $trp = $this->triple();
        $trp->name_given = triples::MATH_CONST_GIVEN;
        $trp->weight = 0.5;
        $trp->set_view_id(views::MATH_CONST_ID);
        $trp->usage = triples::SYSTEM_TEST_ADD_USAGE;
        $trp->impact = triples::SYSTEM_TEST_ADD_IMPACT;
        $trp->exclude();
        return $trp;
    }

    /**
     * @return triple with all fields set and a reserved test name for testing the db write function
     */
    function triple_filled_included(): triple
    {
        $trp = $this->triple_filled();
        $trp->include();
        return $trp;
    }

    /**
     * @return triple with all fields set and a reserved test name for testing the db write function
     */
    function triple_filled_add(): triple
    {
        $t_wrd = new test_words($this->env);
        $trp = $this->triple_filled_included();
        $trp->id = 0;
        $trp->set_name(triples::SYSTEM_TEST_ADD);
        $trp->set_from($t_wrd->word_filled_add()->phrase());
        $trp->set_to($t_wrd->word_filled_add_to()->phrase());
        return $trp;
    }

    /**
     * @return triple "mathematical constant" with only the name set as it may be created by the import
     */
    function triple_name_only(): triple
    {
        $trp = new triple($this->env->usr1);
        $trp->set_name(triples::MATH_CONST);
        return $trp;
    }

    function triple_add(phrase $wrd_from, verb $vrb, phrase $phr_to): triple
    {
        $trp = new triple($this->env->usr1);
        $trp->set_name(triples::SYSTEM_TEST_ADD);
        $trp->set_from($wrd_from);
        $trp->set_verb($vrb);
        $trp->set_to($phr_to);
        return $trp;
    }

    /**
     * @return triple "mathematical constant" with only the link names set as it may be created by the import
     */
    function triple_link_only(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set_from($t_wrd->word_const()->phrase());
        $trp->set_verb($t_vrb->verb_part());
        $trp->set_to($t_wrd->word()->phrase());
        return $trp;
    }

    /**
     * @return triple "pi (unit symbol)" used for unit testing
     */
    function triple_pi_symbol(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triples::PI_SYMBOL_ID, triples::PI_SYMBOL_NAME);
        $trp->description = triples::PI_COM;
        $trp->set_from($t_wrd->word_pi_symbol()->phrase());
        $trp->set_verb($t_vrb->verb_alias());
        $trp->set_to($t_wrd->word_pi()->phrase());
        return $trp;
    }

    /**
     * @return triple "pi (math)" used for unit testing
     */
    function triple_pi(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triples::PI_ID, triples::PI_NAME);
        $trp->description = triples::PI_COM;
        $trp->set_from($t_wrd->word_pi()->phrase());
        $trp->set_verb($t_vrb->verb_is());
        $trp->set_to($this->triple()->phrase());
        $trp->set_type(phrase_types::TRIPLE_HIDDEN, $this->env->usr1);
        return $trp;
    }

    /**
     * TODO PRIO 1
     * @return triple pi as it is returned at the moment via phrase list api, means without links
     */
    function triple_pi_api(): triple
    {
        $trp = new triple($this->env->usr1);
        $trp->set(triples::PI_ID, triples::PI_NAME);
        $trp->description = triples::PI_COM;
        $trp->set_type(phrase_types::TRIPLE_HIDDEN, $this->env->usr1);
        return $trp;
    }


    /*
     * si units
     */

    /**
     * @return triple hyperfine transition frequency of Cs for unit testing of source values
     */
    function transition_cs_133(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_trp = new test_triples($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triples::TRANSITION_CS_ID, triples::TRANSITION_CS);
        $trp->set_from($t_trp->hyperfine_transition_frequency()->phrase());
        $trp->set_verb($t_vrb->verb_of());
        $trp->set_to($t_wrd->cs_133()->phrase());
        return $trp;
    }

    function hyperfine_transition_frequency(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_trp = new test_triples($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triples::TRANSITION_FREQUENCY_ID, triples::TRANSITION_FREQUENCY);
        $trp->set_from($t_trp->hyperfine_transition()->phrase());
        $trp->set_verb($t_vrb->verb_has());
        $trp->set_to($t_wrd->frequency()->phrase());
        return $trp;
    }

    function hyperfine_transition(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triples::HYPERFINE_TRANSITION_ID, triples::HYPERFINE_TRANSITION);
        $trp->set_from($t_wrd->transition()->phrase());
        $trp->set_verb($t_vrb->verb_can_be());
        $trp->set_to($t_wrd->hyperfine()->phrase());
        return $trp;
    }

    /**
     * @return triple speed of light for unit testing of source values
     */
    function speed_of_light(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triples::SPEED_OF_LIGHT_ID, triples::SPEED_OF_LIGHT);
        $trp->description = triples::SPEED_OF_LIGHT_COM;
        $trp->set_from($t_wrd->speed()->phrase());
        $trp->set_verb($t_vrb->verb_of());
        $trp->set_to($t_wrd->light()->phrase());
        return $trp;
    }

    function meter_per_second(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triples::M_PER_S_ID, triples::M_PER_S);
        $trp->description = triples::M_PER_S_COM;
        $trp->set_from($t_wrd->meter()->phrase());
        $trp->set_verb($t_vrb->verb_per());
        $trp->set_to($t_wrd->second()->phrase());
        $trp->set_type(phrase_types::MEASURE, $this->env->usr1);
        return $trp;
    }

    function definition_year_1983(): triple
    {
        $t_trp = new test_triples($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triples::DEFINITION_YEAR_1983_ID, triples::DEFINITION_YEAR_1983);
        $trp->set_from($t_trp->year_1983()->phrase());
        $trp->set_verb($t_vrb->verb_is());
        $trp->set_to($t_trp->definition_year()->phrase());
        $trp->set_type(phrase_types::INFO, $this->env->usr1);
        return $trp;
    }

    function definition_year_1967(): triple
    {
        $t_trp = new test_triples($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triples::DEFINITION_YEAR_1967_ID, triples::DEFINITION_YEAR_1967);
        $trp->set_from($t_trp->year_1967()->phrase());
        $trp->set_verb($t_vrb->verb_is());
        $trp->set_to($t_trp->definition_year()->phrase());
        $trp->set_type(phrase_types::INFO, $this->env->usr1);
        return $trp;
    }

    function definition_year(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triples::DEFINITION_YEAR_ID, triples::DEFINITION_YEAR);
        $trp->set_from($t_wrd->word_year()->phrase());
        $trp->set_verb($t_vrb->verb_of());
        $trp->set_to($t_wrd->definition()->phrase());
        return $trp;
    }

    function year_1983(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triples::YEAR_1983_ID, triples::YEAR_1983);
        $trp->set_from($t_wrd->word_1983()->phrase());
        $trp->set_verb($t_vrb->verb_is());
        $trp->set_to($t_wrd->word_year()->phrase());
        return $trp;
    }

    function year_1967(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triples::YEAR_1967_ID, triples::YEAR_1967);
        $trp->set_from($t_wrd->word_1967()->phrase());
        $trp->set_verb($t_vrb->verb_is());
        $trp->set_to($t_wrd->word_year()->phrase());
        return $trp;
    }

    /**
     * @return triple Global Warming Potential used for unit testing
     */
    function triple_global_warming(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triples::GLOBAL_WARMING_ID, triples::GLOBAL_WARMING);
        $trp->set_from($t_wrd->word_global()->phrase());
        $trp->set_verb($t_vrb->verb_is());
        $trp->set_to($t_wrd->word_warmer()->phrase());
        return $trp;
    }

    /**
     * @return triple Global Warming Potential used for unit testing
     */
    function triple_gwp(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triples::GWP_ID, triples::GWP);
        $trp->set_from($this->triple_global_warming()->phrase());
        $trp->set_verb($t_vrb->verb_is());
        $trp->set_to($t_wrd->word_potential()->phrase());
        return $trp;
    }

    /**
     * @return triple to select the system configuration
     */
    function triple_sys_config(): triple
    {
        $wrd = new triple($this->env->usr1);
        $wrd->set(triples::SYSTEM_CONFIG_ID, triples::SYSTEM_CONFIG);
        return $wrd;
    }

    /**
     * @return triple "e (math const)" used for unit testing
     */
    function triple_e(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triples::E_ID, triples::E);
        $trp->description = triples::E_COM;
        $trp->set_from($t_wrd->word_e()->phrase());
        $trp->set_verb($t_vrb->verb_is());
        $trp->set_to($this->triple()->phrase());
        $trp->set_type(phrase_types::TRIPLE_HIDDEN, $this->env->usr1);
        return $trp;
    }

    /**
     * @return triple to test the sql insert via function
     */
    function triple_add_by_func(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $t_db = new test_db_load($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set_name(triples::SYSTEM_TEST_ADD_VIA_FUNC);
        $wrd_add_func = $t_db->load_word(words::TEST_ADD_VIA_FUNC);
        $wrd_math = $t_db->load_word(words::MATH);
        $trp->set_from($wrd_add_func->phrase());
        $trp->set_verb($t_vrb->verb_is());
        $trp->set_to($wrd_math->phrase());
        return $trp;
    }

    /**
     * @return triple "Zurich (City)" used for unit testing
     */
    function zh_city(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triples::CITY_ZH_ID, triples::CITY_ZH_NAME);
        $trp->set_from($t_wrd->word_zh()->phrase());
        $trp->set_verb($t_vrb->verb_is());
        $trp->set_to($t_wrd->word_city()->phrase());
        $trp->set_description(triples::CITY_ZH_COM);
        return $trp;
    }

    /**
     * @return triple "Zurich (City)" used for unit testing
     */
    function zh_canton(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triples::CANTON_ZURICH_ID, triples::CANTON_ZURICH_NAME);
        $trp->set_from($t_wrd->word_zh()->phrase());
        $trp->set_verb($t_vrb->verb_is());
        $trp->set_to($t_wrd->word_canton()->phrase());
        return $trp;
    }

    /**
     * @return triple "Bern (City)" used for unit testing
     */
    function triple_bern(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triples::CITY_BE_ID, triples::CITY_BE);
        $trp->set_from($t_wrd->word_bern()->phrase());
        $trp->set_verb($t_vrb->verb_is());
        $trp->set_to($t_wrd->word_city()->phrase());
        return $trp;
    }

    /**
     * @return triple "Geneva (City)" used for unit testing
     */
    function triple_ge(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triples::CITY_GE_ID, triples::CITY_GE);
        $trp->set_from($t_wrd->word_ge()->phrase());
        $trp->set_verb($t_vrb->verb_is());
        $trp->set_to($t_wrd->word_city()->phrase());
        return $trp;
    }

    /**
     * @return triple "global problem" used for start view unit testing
     */
    function global_problem(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triples::GLOBAL_PROBLEM_ID, triples::GLOBAL_PROBLEM);
        $trp->set_from($t_wrd->word_problem()->phrase());
        $trp->set_verb($t_vrb->verb_can_be());
        $trp->set_to($t_wrd->word_global()->phrase());
        return $trp;
    }

    /**
     * @return triple "global warming" used for start view unit testing
     */
    function global_warming(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triples::GLOBAL_WARMING_ID, triples::GLOBAL_WARMING);
        $trp->set_from($t_wrd->word_climate()->phrase());
        $trp->set_verb($t_vrb->verb_can_get());
        $trp->set_to($t_wrd->word_warmer()->phrase());
        return $trp;
    }

    /**
     * @return triple that "global warming" "is a" "global problem" used for start view unit testing
     */
    function global_warming_problem(): triple
    {
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triples::GLOBAL_WARMING_PROBLEM_ID);
        $trp->set_from($this->global_warming()->phrase());
        $trp->set_verb($t_vrb->verb_is());
        $trp->set_to($this->global_problem()->phrase());
        return $trp;
    }

    /**
     * @return triple that "global warming potential" "is a" "global warming" used for start view unit testing
     */
    function global_warming_potential(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triples::GWP_ID);
        $trp->set_name(triples::GWP);
        $trp->set_from($this->global_warming()->phrase());
        $trp->set_verb($t_vrb->verb_is());
        $trp->set_to($t_wrd->word_potential()->phrase());
        return $trp;
    }

    /**
     * @return triple that "populism" "is a" "global problem" used for start view unit testing
     */
    function populism_problem(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triples::POPULISM_PROBLEM_ID);
        $trp->set_from($t_wrd->word_populism()->phrase());
        $trp->set_verb($t_vrb->verb_is());
        $trp->set_to($this->global_problem()->phrase());
        return $trp;
    }

    /**
     * @return triple that "poverty" "is a" "global problem" used for start view unit testing
     */
    function poverty_problem(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triples::POVERTY_PROBLEM_ID);
        $trp->set_from($t_wrd->word_poverty()->phrase());
        $trp->set_verb($t_vrb->verb_is());
        $trp->set_to($this->global_problem()->phrase());
        return $trp;
    }

    /**
     * @return triple that defines that "health" "can be a" "global problem" used for start view unit testing
     */
    function potential_health_problem(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triples::POTENTIAL_HEALTH_PROBLEM_ID);
        $trp->set_from($t_wrd->word_health()->phrase());
        $trp->set_verb($t_vrb->verb_can_be());
        $trp->set_to($this->global_problem()->phrase());
        return $trp;
    }

    /**
     * @return triple that defines that "education" "can be a" "global problem" used for start view unit testing
     */
    function potential_education_problem(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triples::POTENTIAL_EDUCATION_PROBLEM_ID);
        $trp->set_from($t_wrd->word_education()->phrase());
        $trp->set_verb($t_vrb->verb_can_be());
        $trp->set_to($this->global_problem()->phrase());
        return $trp;
    }

    /**
     * @return triple that defines "time points"
     */
    function time_points(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triples::TIME_POINTS_ID, triples::TIME_POINTS);
        $trp->set_from($t_wrd->word_time()->phrase());
        $trp->set_verb($t_vrb->verb_can_be());
        $trp->set_to($t_wrd->word_points()->phrase());
        return $trp;
    }

    /**
     * @return triple that defines the "happy time points"
     */
    function happy_time_points(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triples::HAPPY_TIME_POINTS_ID, triples::HAPPY_TIME_POINTS);
        $trp->set_from($t_wrd->word_happy()->phrase());
        $trp->set_verb($t_vrb->verb_can_be());
        $trp->set_to($this->time_points()->phrase());
        return $trp;
    }

    /**
     * @return triple_list with just one element to test the group id
     */
    function triple_list_one(): triple_list
    {
        $lst = new triple_list($this->env->usr1);
        $lst->add($this->triple_pi());
        return $lst;
    }

    /**
     * @return triple_list with only a few triples for efficient testing of the main functionalities
     */
    function triple_list_short(): triple_list
    {
        $lst = new triple_list($this->env->usr1);
        $lst->add($this->triple_filled());
        $lst->add($this->triple_pi());
        $lst->add($this->triple_gwp());
        return $lst;
    }

    /**
     * @return triple_list with many triples for testing the handling of longer lists including paging
     */
    function triple_list(): triple_list
    {
        $lst = new triple_list($this->env->usr1);
        $lst->add($this->triple_filled_included());
        $lst->add($this->triple_pi_symbol());
        $lst->add($this->zh_city());
        $lst->add($this->zh_canton());
        return $lst;
    }

    /**
     * @return triple_list with all triples for testing the handling of longer lists including paging
     */
    function triple_list_all(): triple_list
    {
        $lst = new triple_list($this->env->usr1);
        $lst->add($this->triple_filled_included());
        $lst->add($this->triple_pi_symbol());
        $lst->add($this->triple_pi());
        $lst->add($this->triple_e());
        $lst->add($this->global_problem());
        $lst->add($this->triple_global_warming());
        $lst->add($this->triple_gwp());
        $lst->add($this->global_warming_potential());
        $lst->add($this->populism_problem());
        $lst->add($this->poverty_problem());
        $lst->add($this->potential_health_problem());
        $lst->add($this->potential_education_problem());
        $lst->add($this->time_points());
        $lst->add($this->happy_time_points());
        $lst->add($this->zh_city());
        $lst->add($this->zh_canton());
        $lst->add($this->triple_bern());
        $lst->add($this->triple_ge());
        return $lst;
    }

    function triple_list_ui(): triple_list_ui
    {
        $tl = new test_lib();
        return $tl->list_to_ui($this->triple_list_all(), [api_types::INCL_PHRASES]);
    }


    /*
     * time
     */

    function year_2019(): triple
    {
        $t_wrd = new test_words($this->env);
        return $this->year_x(triples::YEAR_2019_ID, triples::YEAR_2019, $t_wrd->word_2019());
    }

    function year_2020(): triple
    {
        $t_wrd = new test_words($this->env);
        return $this->year_x(triples::YEAR_2020_ID, triples::YEAR_2020, $t_wrd->word_2020());
    }

    function year_2021(): triple
    {
        $t_wrd = new test_words($this->env);
        return $this->year_x(triples::YEAR_2021_ID, triples::YEAR_2021, $t_wrd->word_2021());
    }

    function year_2022(): triple
    {
        $t_wrd = new test_words($this->env);
        return $this->year_x(triples::YEAR_2022_ID, triples::YEAR_2022, $t_wrd->word_2022());
    }

    function year_2023(): triple
    {
        $t_wrd = new test_words($this->env);
        return $this->year_x(triples::YEAR_2023_ID, triples::YEAR_2023, $t_wrd->word_2023());
    }

    function year_2024(): triple
    {
        $t_wrd = new test_words($this->env);
        return $this->year_x(triples::YEAR_2024_ID, triples::YEAR_2024, $t_wrd->word_2024());
    }

    function year_2025(): triple
    {
        $t_wrd = new test_words($this->env);
        return $this->year_x(triples::YEAR_2025_ID, triples::YEAR_2025, $t_wrd->word_2025());
    }

    function year_2026(): triple
    {
        $t_wrd = new test_words($this->env);
        return $this->year_x(triples::YEAR_2026_ID, triples::YEAR_2026, $t_wrd->word_2026());
    }

    function year_2027(): triple
    {
        $t_wrd = new test_words($this->env);
        return $this->year_x(triples::YEAR_2027_ID, triples::YEAR_2027, $t_wrd->word_2027());
    }

    function year_2028(): triple
    {
        $t_wrd = new test_words($this->env);
        return $this->year_x(triples::YEAR_2028_ID, triples::YEAR_2028, $t_wrd->word_2028());
    }

    function year_2029(): triple
    {
        $t_wrd = new test_words($this->env);
        return $this->year_x(triples::YEAR_2029_ID, triples::YEAR_2029, $t_wrd->word_2029());
    }

    function year_2030(): triple
    {
        $t_wrd = new test_words($this->env);
        return $this->year_x(triples::YEAR_2030_ID, triples::YEAR_2030, $t_wrd->word_2030());
    }


    private function year_x(int $id, string $name, word $year): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set($id, $name);
        $trp->set_from($year->phrase());
        $trp->set_verb($t_vrb->verb_is());
        $trp->set_to($t_wrd->word_year()->phrase());
        return $trp;
    }

    /*
     * random
     */

    /**
     * create a triple with random parameters for speed testing
     *
     * @param int|null $id a given sequence number to assure that the triple name is unique
     * @param phrase_list $phr_lst list of the phrases created until now
     * @return triple the created triple object
     */
    function random(?int $id, phrase_list $phr_lst, test_cleanup $t): triple
    {
        global $sys;

        $t_vrb = new test_verbs($t);

        $from_id = rand(1, $phr_lst->count());
        $to_id = 1;
        if ($phr_lst->count() < 2) {
            log_err('phrase list too small for triple random');
        } elseif ($phr_lst->count() == 2) {
            if ($from_id == 1) {
                $to_id = 2;
            }
            $from_id = rand(1, $phr_lst->count());
        } else {
            $to_id = rand(1, $phr_lst->count());
            while ($from_id == $to_id) {
                $to_id = rand(1, $phr_lst->count());
            }
        }

        // make sure that from and to is not the same
        $trp = new triple($this->env->usr1);
        $trp->id = $id;
        $trp->set_from($phr_lst->get($from_id)->phrase());
        $trp->set_verb($t_vrb->random());
        $trp->set_to($phr_lst->get($to_id)->phrase());
        $trp->set_name(words::TEST_SPEED_PREFIX . $id);

        $type_id = rand(1, $sys->typ_lst->phr_typ->count());
        $trp->set_type_id($type_id, $this->env->usr1);
        return $trp;
    }

}