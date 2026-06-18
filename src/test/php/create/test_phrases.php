<?php

/*

    test/create/test_phrases.php - create the test phrase objects
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
include_once paths::SHARED_TYPES . 'api_types.php';
include_once html_paths::PHRASE . 'phrase_list.php';
include_once test_paths::UTILS . 'test_cleanup.php';

use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase_list as phrase_list_ui;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class test_phrases
{

    /*
     * init
     */

    // use the global test environment
    private test_cleanup $env;

    function __construct(test_cleanup $env) {
        $this->env = $env;
    }


    /*
     * unit
     */

    function phrase(): phrase
    {
        $t_wrd = new test_words($this->env);
        return $t_wrd->word()->phrase();
    }

    function phrase_filled(): phrase
    {
        $t_trp = new test_triples($this->env);
        return $t_trp->triple_filled()->phrase();
    }

    function phrase_pi(): phrase
    {
        $t_trp = new test_triples($this->env);
        return $t_trp->triple_pi()->phrase();
    }

    /**
     * @return phrase of the word year because on most case the phrase is used instead of the word
     */
    function year(): phrase
    {
        $t_wrd = new test_words($this->env);
        return $t_wrd->word_year()->phrase();
    }

    /**
     * @return phrase of the word canton because on most case the phrase is used instead of the word
     */
    function canton(): phrase
    {
        $t_wrd = new test_words($this->env);
        return $t_wrd->word_canton()->phrase();
    }

    /**
     * @return phrase of the word city
     */
    function city(): phrase
    {
        $t_wrd = new test_words($this->env);
        return $t_wrd->word_city()->phrase();
    }

    function phrase_zh_city(): phrase
    {
        $t_trp = new test_triples($this->env);
        return $t_trp->zh_city()->phrase();
    }

    function phrase_list(): phrase_list
    {
        $t_wrd = new test_words($this->env);
        $t_trp = new test_triples($this->env);
        $lst = new phrase_list($this->env->usr1);
        $lst->add($t_wrd->word()->phrase());
        $lst->add($t_wrd->word_const()->phrase());
        $lst->add($t_wrd->word_pi()->phrase());
        $lst->add($t_trp->triple()->phrase());
        $lst->add($t_trp->triple_pi()->phrase());
        return $lst;
    }

    /**
     * @return phrase_list with the word one to force a value to be scaled to one
     */
    function phrase_list_one(): phrase_list
    {
        $t_wrd = new test_words($this->env);
        $lst = new phrase_list($this->env->usr1);
        $lst->add($t_wrd->word_one()->phrase());
        $lst->add($t_wrd->word_inhabitant()->phrase());
        return $lst;
    }

    /**
     * @return phrase_list as it is returned from the phrase list api, so at the moment without triple links
     */
    function phrase_list_api(): phrase_list
    {
        $t_wrd = new test_words($this->env);
        $t_trp = new test_triples($this->env);
        $lst = new phrase_list($this->env->usr1);
        $lst->add($t_wrd->word()->phrase());
        $lst->add($t_wrd->word_const()->phrase());
        $lst->add($t_wrd->word_pi()->phrase());
        $lst->add($t_trp->triple_api()->phrase());
        $lst->add($t_trp->triple_pi_api()->phrase());
        return $lst;
    }

    function phrase_list_prime(): phrase_list
    {
        $t_wrd = new test_words($this->env);
        $t_trp = new test_triples($this->env);
        $lst = new phrase_list($this->env->usr1);
        $lst->add($t_wrd->word()->phrase());
        $lst->add($t_wrd->word_const()->phrase());
        $lst->add($t_trp->triple()->phrase());
        $lst->add($t_trp->triple_pi()->phrase());
        return $lst;
    }

    /**
     * @return phrase_list with one word and one triple
     */
    function phrase_list_small(): phrase_list
    {
        $t_wrd = new test_words($this->env);
        $t_trp = new test_triples($this->env);
        $lst = new phrase_list($this->env->usr1);
        $lst->add($t_wrd->word_pi()->phrase());
        $lst->add($t_trp->triple()->phrase());
        return $lst;
    }

    /**
     * @return phrase_list with more than 10 phrases
     */
    function phrase_list_long(): phrase_list
    {
        $t_wrd = new test_words($this->env);
        $t_trp = new test_triples($this->env);
        $lst = new phrase_list($this->env->usr1);
        $lst->add($t_wrd->word()->phrase());
        $lst->add($t_wrd->word_const()->phrase());
        $lst->add($t_wrd->word_pi_symbol()->phrase());
        $lst->add($t_wrd->word_e()->phrase());
        $lst->add($t_wrd->word_2019()->phrase());
        $lst->add($t_wrd->word_one()->phrase());
        $lst->add($t_wrd->word_mio()->phrase());
        $lst->add($t_wrd->word_percent()->phrase());
        $lst->add($t_trp->triple()->phrase());
        $lst->add($t_trp->triple_pi()->phrase());
        $lst->add($t_trp->zh_canton()->phrase());
        $lst->add($t_trp->triple_bern()->phrase());
        $lst->add($t_trp->triple_ge()->phrase());
        return $lst;
    }

    function phrase_list_pi(): phrase_list
    {
        $t_trp = new test_triples($this->env);
        $lst = new phrase_list($this->env->usr1);
        $lst->add($t_trp->triple_pi()->phrase());
        return $lst;
    }

    function phrase_list_pi_symbol(): phrase_list
    {
        $t_wrd = new test_words($this->env);
        $lst = new phrase_list($this->env->usr1);
        $lst->add($t_wrd->word_pi_symbol()->phrase());
        return $lst;
    }

    function phrase_list_e(): phrase_list
    {
        $t_trp = new test_triples($this->env);
        $lst = new phrase_list($this->env->usr1);
        $lst->add($t_trp->triple_e()->phrase());
        return $lst;
    }

    function si_unit_transition_cs_133(): phrase_list
    {
        $t_wrd = new test_words($this->env);
        $t_trp = new test_triples($this->env);
        $lst = new phrase_list($this->env->usr1);
        $lst->add($t_trp->transition_cs_133()->phrase());
        $lst->add($t_wrd->hz()->phrase());
        $lst->add($t_trp->definition_year_1967()->phrase());
        return $lst;
    }

    function si_unit_speed_of_light(): phrase_list
    {
        $t_trp = new test_triples($this->env);
        $lst = new phrase_list($this->env->usr1);
        $lst->add($t_trp->speed_of_light()->phrase());
        $lst->add($t_trp->meter_per_second()->phrase());
        $lst->add($t_trp->definition_year_1983()->phrase());
        return $lst;
    }

    function phrase_list_const(): phrase_list
    {
        $t_wrd = new test_words($this->env);
        $lst = new phrase_list($this->env->usr1);
        $lst->add($t_wrd->word_const()->phrase());
        return $lst;
    }

    /**
     * @return phrase_list with some math const e.g. to test loading a list of values by phrase list
     */
    function phrase_list_math_const(): phrase_list
    {
        $t_trp = new test_triples($this->env);
        $lst = new phrase_list($this->env->usr1);
        $lst->add($t_trp->triple_pi()->phrase());
        $lst->add($t_trp->triple_e()->phrase());
        return $lst;
    }

    /**
     * @return phrase_list with the cities for unit testing
     */
    function phrase_list_cities(): phrase_list
    {
        $t_trp = new test_triples($this->env);
        $lst = new phrase_list($this->env->usr1);
        $lst->add($t_trp->zh_city()->phrase());
        $lst->add($t_trp->triple_bern()->phrase());
        $lst->add($t_trp->triple_ge()->phrase());
        return $lst;
    }

    /**
     * @return phrase_list with the Zurich inhabitants and 2020 for unit testing the result id
     */
    function zh_inhabitants_2020(): phrase_list
    {
        $t_trp = new test_triples($this->env);
        $t_wrd = new test_words($this->env);
        $lst = new phrase_list($this->env->usr1);
        $lst->add($t_trp->zh_city()->phrase());
        $lst->add($t_wrd->word_inhabitant()->phrase());
        $lst->add($t_wrd->word_2020()->phrase());
        return $lst;
    }

    /**
     * @return phrase_list with the Zurich inhabitants and 2020 for unit testing the result id
     */
    function zh_ge_inhabitants_2020(): phrase_list
    {
        $t_wrd = new test_words($this->env);
        $t_trp = new test_triples($this->env);
        $lst = new phrase_list($this->env->usr1);
        $lst->add($t_trp->zh_city()->phrase());
        $lst->add($t_trp->triple_ge()->phrase());
        $lst->add($t_wrd->word_inhabitant()->phrase());
        $lst->add($t_wrd->word_2020()->phrase());
        return $lst;
    }

    /**
     * @return phrase_list with simple year phrases
     */
    function years(): phrase_list
    {
        $t_trp = new test_triples($this->env);
        $lst = new phrase_list($this->env->usr1);
        $lst->add($t_trp->year_1967()->phrase());
        $lst->add($t_trp->year_1983()->phrase());
        $lst->add($t_trp->year_2019()->phrase());
        $lst->add($t_trp->year_2020()->phrase());
        $lst->add($t_trp->year_2021()->phrase());
        $lst->add($t_trp->year_2022()->phrase());
        $lst->add($t_trp->year_2023()->phrase());
        $lst->add($t_trp->year_2024()->phrase());
        $lst->add($t_trp->year_2025()->phrase());
        $lst->add($t_trp->year_2026()->phrase());
        $lst->add($t_trp->year_2027()->phrase());
        $lst->add($t_trp->year_2028()->phrase());
        $lst->add($t_trp->year_2029()->phrase());
        $lst->add($t_trp->year_2030()->phrase());
        return $lst;
    }

    /**
     * @return phrase_list with all phrases used for unit testing
     */
    function phrase_list_all(): phrase_list
    {
        $lst = new phrase_list($this->env->usr1);
        $lst->merge($this->phrase_list());
        $lst->merge($this->phrase_list_math_const());
        $lst->merge($this->phrase_list_cities());
        return $lst;
    }

    function phrase_list_start_view(): phrase_list
    {
        $t_wrd = new test_words($this->env);
        $t_trp = new test_triples($this->env);
        $lst = new phrase_list($this->env->usr1);
        $lst->add($t_wrd->word()->phrase());
        $lst->add($t_wrd->word_const()->phrase());
        $lst->add($t_trp->triple()->phrase());
        $lst->add($t_trp->triple_pi()->phrase());
        $lst->add($t_trp->global_problem()->phrase());
        $lst->add($t_trp->global_warming()->phrase());
        $lst->add($t_trp->global_warming_problem()->phrase());
        $lst->add($t_trp->global_warming_potential()->phrase());
        $lst->add($t_trp->populism_problem()->phrase());
        $lst->add($t_trp->potential_health_problem()->phrase());
        $lst->add($t_trp->poverty_problem()->phrase());
        $lst->add($t_trp->potential_education_problem()->phrase());
        $lst->add($t_trp->happy_time_points()->phrase());
        $lst->add($t_wrd->word_trillion()->phrase());
        $lst->add($t_wrd->word_billion()->phrase());
        $lst->add($t_wrd->word_usd()->phrase());
        $lst->add($t_wrd->word_htp()->phrase());
        return $lst;
    }

    function phrase_list_start_view_ui(): phrase_list_ui
    {
        return new phrase_list_ui($this->phrase_list_start_view()->api_json([api_types::INCL_PHRASES]));
    }

    /**
     * @return phrase_list with 16 entries to test the normal group id creation
     * 1    ...../+
     * 11    .....9-
     * 12    .....A+
     * 37    .....Z-
     * 38    .....a+
     * 64    ..../.-
     * 376    ....3s+
     * 2367    ....Yz-
     * 13108    ...1Ao+
     * 82124    ...I1A-
     * 505294    ../vLC+
     * 2815273    ..8jId-
     * 17192845    .//ZSB+
     */
    function phrase_list_13(): phrase_list
    {
        $t_wrd = new test_words($this->env);
        $t_trp = new test_triples($this->env);
        $lst = new phrase_list($this->env->usr1);
        $wrd = $t_wrd->word();
        $wrd->id = 1;
        $wrd->set_name('word1');
        $lst->add($wrd->phrase());
        $trp = $t_trp->triple();
        $trp->id = 11;
        $trp->set_name('triple1');
        $lst->add($trp->phrase());
        $wrd = $t_wrd->word();
        $wrd->id = 12;
        $wrd->set_name('word2');
        $lst->add($wrd->phrase());
        $trp = $t_trp->triple();
        $trp->id = 37;
        $trp->set_name('triple2');
        $lst->add($trp->phrase());
        $wrd = $t_wrd->word();
        $wrd->id = 38;
        $wrd->set_name('word3');
        $lst->add($wrd->phrase());
        $trp = $t_trp->triple();
        $trp->id = 64;
        $trp->set_name('triple3');
        $lst->add($trp->phrase());
        $wrd = $t_wrd->word();
        $wrd->id = 376;
        $wrd->set_name('word4');
        $lst->add($wrd->phrase());
        $trp = $t_trp->triple();
        $trp->id = 2367;
        $trp->set_name('triple4');
        $lst->add($trp->phrase());
        $wrd = $t_wrd->word();
        $wrd->id = 13108;
        $wrd->set_name('word5');
        $lst->add($wrd->phrase());
        $trp = $t_trp->triple();
        $trp->id = 82124;
        $trp->set_name('triple5');
        $lst->add($trp->phrase());
        $wrd = $t_wrd->word();
        $wrd->id = 505294;
        $wrd->set_name('word6');
        $lst->add($wrd->phrase());
        $trp = $t_trp->triple();
        $trp->id = 2815273;
        $trp->set_name('triple6');
        $lst->add($trp->phrase());
        $wrd = $t_wrd->word();
        $wrd->id = 17192845;
        $wrd->set_name('word7');
        $lst->add($wrd->phrase());
        return $lst;
    }

    /**
     * @return phrase_list with 16 entries to test the normal group id creation
     * 1    ...../+
     * 11    .....9-
     * 12    .....A+
     * 37    .....Z-
     * 38    .....a+
     * 64    ..../.-
     * 376    ....3s+
     * 2367    ....Yz-
     * 13108    ...1Ao+
     * 82124    ...I1A-
     * 505294    ../vLC+
     * 2815273    ..8jId-
     * 17192845    .//ZSB+
     * 106841477    .4LYK3-
     */
    function phrase_list_14(): phrase_list
    {
        $t_trp = new test_triples($this->env);
        $lst = $this->phrase_list_13();
        $trp = $t_trp->triple();
        $trp->id = 106841477;
        $trp->set_name('triple7');
        $lst->add($trp->phrase());
        return $lst;
    }

    /**
     * @return phrase_list with 16 entries to test the normal group id creation
     * 1    ...../+
     * 11    .....9-
     * 12    .....A+
     * 37    .....Z-
     * 38    .....a+
     * 64    ..../.-
     * 376    ....3s+
     * 2367    ....Yz-
     * 13108    ...1Ao+
     * 82124    ...I1A-
     * 505294    ../vLC+
     * 2815273    ..8jId-
     * 17192845    .//ZSB+
     * 106841477    .4LYK3-
     */
    function phrase_list_14b(): phrase_list
    {
        $t_trp = new test_triples($this->env);
        $lst = $this->phrase_list_13();
        $trp = $t_trp->triple();
        $trp->id = 3516593476;
        $trp->set_name('triple8');
        $lst->add($trp->phrase());
        return $lst;
    }

    /**
     * @return phrase_list with 16 entries to test the normal group id creation
     * 1    ...../+
     * 11    .....9-
     * 12    .....A+
     * 37    .....Z-
     * 38    .....a+
     * 64    ..../.-
     * 376    ....3s+
     * 2367    ....Yz-
     * 13108    ...1Ao+
     * 82124    ...I1A-
     * 505294    ../vLC+
     * 2815273    ..8jId-
     * 17192845    .//ZSB+
     * 106841477    .4LYK3-
     * 628779863    .ZSahL+
     * 3516593476    1FajJ2-
     */
    function phrase_list_16(): phrase_list
    {
        $t_wrd = new test_words($this->env);
        $t_trp = new test_triples($this->env);
        $lst = $this->phrase_list_13();
        $trp = $t_trp->triple();
        $trp->id = 106841477;
        $trp->set_name('triple7');
        $lst->add($trp->phrase());
        $wrd = $t_wrd->word();
        $wrd->id = 628779863;
        $wrd->set_name('word8');
        $lst->add($wrd->phrase());
        $trp = $t_trp->triple();
        $trp->id = 3516593476;
        $trp->set_name('triple8');
        $lst->add($trp->phrase());
        return $lst;
    }

    function phrase_list_17_plus(): phrase_list
    {
        $t_wrd = new test_words($this->env);
        $lst = $this->phrase_list_16();
        $wrd = $t_wrd->word();
        $wrd->id = 987654321;
        $wrd->set_name('word17');
        $lst->add($wrd->phrase());
        return $lst;
    }

    /**
     * @return phrase_list to get all inhabitant related to the canton Zurich
     */
    function canton_zh_phrase_list(): phrase_list
    {
        $t_wrd = new test_words($this->env);
        $lst = new phrase_list($this->env->usr1);
        $lst->add($t_wrd->word_canton()->phrase());
        $lst->add($t_wrd->word_zh()->phrase());
        $lst->add($t_wrd->word_inhabitant()->phrase());
        return $lst;
    }

    /**
     * @return phrase_list to get all inhabitant related to the canton Zurich
     */
    function ch_inhabitant_phrase_list(): phrase_list
    {
        $t_wrd = new test_words($this->env);
        $lst = new phrase_list($this->env->usr1);
        $lst->add($t_wrd->word_ch()->phrase());
        $lst->add($t_wrd->word_inhabitant()->phrase());
        return $lst;
    }

    /**
     * @return phrase_list the phrases relevant for having a second entry in the phrase group list
     */
    function phrase_list_zh_2019(): phrase_list
    {
        $t_wrd = new test_words($this->env);
        $lst = new phrase_list($this->env->usr1);
        $lst->add($t_wrd->word_zh()->phrase());
        $lst->add($t_wrd->word_inhabitant()->phrase());
        $lst->add($t_wrd->word_2019()->phrase());
        return $lst;
    }

    /**
     * @return phrase_list use today's inhabitants of the coty of zurich for group tests
     */
    function phrase_list_zh_city(): phrase_list
    {
        $t_wrd = new test_words($this->env);
        $t_trp = new test_triples($this->env);
        $lst = new phrase_list($this->env->usr1);
        $lst->add($t_trp->zh_city()->phrase());
        $lst->add($t_wrd->word_inhabitant()->phrase());
        return $lst;
    }

    /**
     * @return phrase_list the phrases relevant for testing the max number of prime phrases
     */
    function phrase_list_zh_city_2019(): phrase_list
    {
        $t_wrd = new test_words($this->env);
        $lst = $this->phrase_list_zh_city();
        $lst->add($t_wrd->word_2019()->phrase());
        return $lst;
    }

    /**
     * @return phrase_list the phrases relevant for having a second entry in the phrase group list
     */
    function phrase_list_zh_city_2020(): phrase_list
    {
        $t_wrd = new test_words($this->env);
        $lst = $this->phrase_list_zh_city();
        $lst->add($t_wrd->word_2020()->phrase());
        return $lst;
    }

    /**
     * @return phrase_list the phrases relevant for testing the max number of prime phrases
     */
    function phrase_list_zh_city_pct(): phrase_list
    {
        $t_wrd = new test_words($this->env);
        $lst = $this->phrase_list_zh_city_2019();
        $lst->add($t_wrd->word_percent()->phrase());
        return $lst;
    }

    /**
     * @return phrase_list the phrases relevant for testing the max number of prime phrases
     */
    function phrase_list_zh_mio(): phrase_list
    {
        $t_wrd = new test_words($this->env);
        $lst = new phrase_list($this->env->usr1);
        $lst->add($t_wrd->word_zh()->phrase());
        $lst->add($t_wrd->word_inhabitant()->phrase());
        $lst->add($t_wrd->word_2019()->phrase());
        $lst->add($t_wrd->word_mio()->phrase());
        return $lst;
    }

    /**
     * @return phrase_list the phrases relevant for testing the max number of prime phrases
     */
    function phrase_list_canton_mio(): phrase_list
    {
        $t_wrd = new test_words($this->env);
        $lst = new phrase_list($this->env->usr1);
        $lst->add($t_wrd->word_zh()->phrase());
        $lst->add($t_wrd->word_canton()->phrase());
        $lst->add($t_wrd->word_inhabitant()->phrase());
        $lst->add($t_wrd->word_2019()->phrase());
        $lst->add($t_wrd->word_mio()->phrase());
        return $lst;
    }

    /**
     * @return phrase_list the phrases relevant for testing the max number of prime phrases
     */
    function phrase_list_canton_pct(): phrase_list
    {
        $t_wrd = new test_words($this->env);
        $lst = new phrase_list($this->env->usr1);
        $lst->add($t_wrd->word_zh()->phrase());
        $lst->add($t_wrd->word_canton()->phrase());
        $lst->add($t_wrd->word_inhabitant()->phrase());
        $lst->add($t_wrd->word_2019()->phrase());
        $lst->add($t_wrd->word_percent()->phrase());
        return $lst;
    }

    /**
     * @return phrase_list the phrases relevant for testing the max number of prime phrases
     */
    function ch_inhabitants_in_mio_2019(): phrase_list
    {
        $t_wrd = new test_words($this->env);
        $lst = new phrase_list($this->env->usr1);
        $lst->add($t_wrd->word_ch()->phrase());
        $lst->add($t_wrd->word_inhabitant()->phrase());
        $lst->add($t_wrd->word_2019()->phrase());
        $lst->add($t_wrd->word_mio()->phrase());
        return $lst;
    }

    /**
     * @return phrase_list as ch_inhabitants_in_mio_2019 but with "mio" missing the scaling type
     *                     to test the scaling type check
     */
    function ch_inhabitants_in_mio_2019_unscaled(): phrase_list
    {
        $t_wrd = new test_words($this->env);
        $lst = new phrase_list($this->env->usr1);
        $lst->add($t_wrd->word_ch()->phrase());
        $lst->add($t_wrd->word_inhabitant()->phrase());
        $lst->add($t_wrd->word_2019()->phrase());
        $lst->add($t_wrd->word_mio_unscaled()->phrase());
        return $lst;
    }

    /**
     * @return phrase_list with the target phrases to scale a value to single inhabitants
     */
    function inhabitant_one_phrase_list(): phrase_list
    {
        $t_wrd = new test_words($this->env);
        $lst = new phrase_list($this->env->usr1);
        $lst->add($t_wrd->word_inhabitant()->phrase());
        $lst->add($t_wrd->word_one()->phrase());
        return $lst;
    }

    /**
     * @return phrase_list as inhabitant_one_phrase_list but with "one" missing the scaling type
     *                     to test the scaling type check
     */
    function inhabitant_one_unscaled_phrase_list(): phrase_list
    {
        $t_wrd = new test_words($this->env);
        $lst = new phrase_list($this->env->usr1);
        $lst->add($t_wrd->word_inhabitant()->phrase());
        $lst->add($t_wrd->word_one_unscaled()->phrase());
        return $lst;
    }

    /**
     * @return phrase_list the phrases relevant for testing the max number of prime phrases
     */
    function phrase_list_zh_mio_2020(): phrase_list
    {
        $t_wrd = new test_words($this->env);
        $lst = new phrase_list($this->env->usr1);
        $lst->add($t_wrd->word_zh()->phrase());
        $lst->add($t_wrd->word_inhabitant()->phrase());
        $lst->add($t_wrd->word_2020()->phrase());
        $lst->add($t_wrd->word_mio()->phrase());
        return $lst;
    }

    /**
     * @return phrase_list the phrases relevant for testing the increase formula
     */
    function phrase_list_increase(): phrase_list
    {
        $t_wrd = new test_words($this->env);
        $lst = new phrase_list($this->env->usr1);
        $lst->add($t_wrd->word_percent()->phrase());
        $lst->add($t_wrd->word_this()->phrase());
        $lst->add($t_wrd->word_prior()->phrase());
        $lst->add($t_wrd->word_ch()->phrase());
        $lst->add($t_wrd->word_inhabitant()->phrase());
        $lst->add($t_wrd->word_2020()->phrase());
        $lst->add($t_wrd->word_mio()->phrase());
        return $lst;
    }

    /**
     * @return phrase_list with the phrases to select the launch date of this pod in the config
     */
    function phrase_list_pod_launch(): phrase_list
    {
        $t_wrd = new test_words($this->env);
        $t_trp = new test_triples($this->env);
        $lst = new phrase_list($this->env->usr1);
        $lst->add($t_wrd->word_zukunft_com()->phrase());
        $lst->add($t_trp->triple_sys_config()->phrase());
        $lst->add($t_wrd->word_pod()->phrase());
        $lst->add($t_wrd->word_launch()->phrase());
        return $lst;
    }

    /**
     * @return phrase_list with the phrases to select the url of this pod in the config
     */
    function phrase_list_pod_url(): phrase_list
    {
        $t_wrd = new test_words($this->env);
        $t_trp = new test_triples($this->env);
        $lst = new phrase_list($this->env->usr1);
        $lst->add($t_wrd->word_zukunft_com()->phrase());
        $lst->add($t_trp->triple_sys_config()->phrase());
        $lst->add($t_wrd->word_pod()->phrase());
        $lst->add($t_wrd->word_url()->phrase());
        return $lst;
    }

    /**
     * @return phrase_list with the phrases to select the geolocation of this pod development in the config
     */
    function phrase_list_pod_point(): phrase_list
    {
        $t_wrd = new test_words($this->env);
        $t_trp = new test_triples($this->env);
        $lst = new phrase_list($this->env->usr1);
        $lst->add($t_wrd->word_zukunft_com()->phrase());
        $lst->add($t_trp->triple_sys_config()->phrase());
        $lst->add($t_wrd->word_pod()->phrase());
        $lst->add($t_wrd->word_point()->phrase());
        return $lst;
    }

    /**
     * a list of city and canton related phrases
     * e.g. to test the subtitle for the city zurich
     *
     * @return phrase_list with symbol triples
     */
    function list_zh(): phrase_list
    {
        $t_wrd = new test_words($this->env);
        $t_trp = new test_triples($this->env);
        $lst = new phrase_list($this->env->usr1);
        $lst->add($t_wrd->word_zh()->phrase());
        $lst->add($t_wrd->word_city()->phrase());
        $lst->add($t_trp->zh_city()->phrase());
        $lst->add($t_wrd->word_canton()->phrase());
        $lst->add($t_trp->zh_canton()->phrase());
        $lst->add($t_wrd->word_company()->phrase());
        $lst->add($t_trp->company_zurich()->phrase());
        return $lst;
    }

    /**
     * a list of city and canton related phrases
     * e.g. to test the subtitle for the city zurich in a different order
     *
     * @return phrase_list with symbol triples
     */
    function list_zh_impact(): phrase_list
    {
        $t_trp = new test_triples($this->env);
        $lst = $this->list_zh();
        $lst_imp = new phrase_list($this->env->usr1);
        $lst_imp->add($t_trp->zh_city_low_impact()->phrase());
        $lst_imp->add($t_trp->zh_canton_low_impact()->phrase());
        $lst_imp->add($t_trp->company_zurich_high_impact()->phrase());
        $lst_imp->fill_by_id($lst);
        return $lst_imp;
    }

    /**
     * a frontend list of all test phrases e.g. to check if the selections are fine
     *
     * @return phrase_list_ui with all phrases used for testing
     */
    function list_ui(): phrase_list_ui
    {
        $lst = $this->list_symbols_ui();
        $lst->merge($this->list_zh_ui());
        return $lst;
    }

    /**
     * a list of symbol triples to test the selection of the relevant symbols
     *
     * @return phrase_list_ui with symbol triples
     */
    function list_symbols_ui(): phrase_list_ui
    {
        $t_trp = new test_triples($this->env);
        $lst = new phrase_list($this->env->usr1);
        $lst->add($t_trp->symbol_chf()->phrase());
        return $this->ui_list($lst);
    }

    /**
     * the phrases related to the word "Swiss franc" as loaded with the word from the backend
     * e.g. to test the related phrases shown on the default word page
     *
     * @return phrase_list_ui with the symbol and the category triple of the Swiss franc
     */
    function list_swiss_franc_related_ui(): phrase_list_ui
    {
        $t_trp = new test_triples($this->env);
        $lst = new phrase_list($this->env->usr1);
        $lst->add($t_trp->symbol_chf()->phrase());
        $lst->add($t_trp->swiss_franc_currency()->phrase());
        return $this->ui_list($lst);
    }

    /**
     * the phrases related to the word "US dollar" as loaded with the word from the backend
     * e.g. to test the alias and symbol lines shown on the default word page
     *
     * @return phrase_list_ui with the alias, symbol, prefix and category triples of the US dollar
     */
    function list_us_dollar_related_ui(): phrase_list_ui
    {
        $t_trp = new test_triples($this->env);
        $lst = new phrase_list($this->env->usr1);
        $lst->add($t_trp->alias_dollar()->phrase());
        $lst->add($t_trp->alias_u_s_dollar()->phrase());
        $lst->add($t_trp->symbol_usd()->phrase());
        $lst->add($t_trp->in_usd()->phrase());
        $lst->add($t_trp->usd_currency()->phrase());
        return $this->ui_list($lst);
    }

    /**
     * the phrases related to the word "company" as loaded with the word from the backend
     * in a not sorted order e.g. to test that the related stocks are shown
     * sorted by the market capitalisation on the default word page
     *
     * @return phrase_list_ui with the stock triples of the company word
     */
    function list_company_related_ui(): phrase_list_ui
    {
        $t_trp = new test_triples($this->env);
        $lst = new phrase_list($this->env->usr1);
        $lst->add($t_trp->vestas_company()->phrase());
        $lst->add($t_trp->company_zurich_market_cap()->phrase());
        $lst->add($t_trp->abb_company()->phrase());
        return $this->ui_list($lst);
    }

    /**
     * the frontend list of city and canton related phrases
     * e.g. to test the subtitle for the city zurich
     *
     * @return phrase_list_ui with symbol triples
     */
    function list_zh_ui(): phrase_list_ui
    {
        return $this->ui_list($this->list_zh());
    }

    /**
     * a list of currencies and their common parent "currency" linked via the "is a" verb
     * e.g. to test word::similar where the similar words of "Swiss franc" are "Euro" and "US Dollar"
     *
     * @return phrase_list with the currency words and the "is a currency" triples
     */
    function list_currency(): phrase_list
    {
        $t_wrd = new test_words($this->env);
        $t_trp = new test_triples($this->env);
        $lst = new phrase_list($this->env->usr1);
        $lst->add($t_wrd->currency()->phrase());
        $lst->add($t_wrd->swiss_franc()->phrase());
        $lst->add($t_trp->swiss_franc_currency()->phrase());
        $lst->add($t_wrd->euro()->phrase());
        $lst->add($t_trp->euro_currency()->phrase());
        $lst->add($t_wrd->us_dollar()->phrase());
        $lst->add($t_trp->usd_currency()->phrase());
        return $lst;
    }

    /**
     * @return phrase_list_ui the frontend list of currency related phrases for the word::similar test
     */
    function list_currency_ui(): phrase_list_ui
    {
        return $this->ui_list($this->list_currency());
    }

    /**
     * the frontend list of city and canton related phrases
     * e.g. to test the subtitle for the city zurich in a different order
     *
     * @return phrase_list_ui with symbol triples
     */
    function list_zh_impact_ui(): phrase_list_ui
    {
        return $this->ui_list($this->list_zh_impact());
    }

    function ui_phrase_list(): phrase_list_ui
    {
        return $this->ui_list($this->phrase_list());
    }


    /*
     * convert
     */

    /**
     * convert a backend phrase list to a frontend list
     *
     * @param phrase_list $lst tbe backend list to convert
     * @return phrase_list_ui the converted frontend list
     */
    private function ui_list(phrase_list $lst): phrase_list_ui
    {
        return new phrase_list_ui($lst->api_json([api_types::INCL_PHRASES]));
    }

}