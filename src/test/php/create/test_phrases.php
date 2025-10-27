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
include_once paths::SHARED_TYPES . 'api_type.php';
include_once html_paths::PHRASE . 'phrase_list.php';
include_once test_paths::UTILS . 'test_cleanup.php';

use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\shared\types\api_type;
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
        $lst->add($t_wrd->word_pi_symbol()->phrase());
        $lst->add($t_trp->triple()->phrase());
        $lst->add($t_trp->triple_pi_symbol()->phrase());
        return $lst;
    }

    function phrase_list_pi_const(): phrase_list
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

    function phrase_list_prime(): phrase_list
    {
        $t_wrd = new test_words($this->env);
        $t_trp = new test_triples($this->env);
        $lst = new phrase_list($this->env->usr1);
        $lst->add($t_wrd->word()->phrase());
        $lst->add($t_wrd->word_const()->phrase());
        $lst->add($t_trp->triple()->phrase());
        $lst->add($t_trp->triple_pi_symbol()->phrase());
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

    function phrase_list_start_view_dsp(): phrase_list_ui
    {
        return new phrase_list_ui($this->phrase_list_start_view()->api_json([api_type::INCL_PHRASES]));
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
     * @return phrase_list to get all inhabitant related to the Canton Zurich
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
     * @return phrase_list to get all inhabitant related to the Canton Zurich
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
    function phrase_list_ch_mio(): phrase_list
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

    function phrase_list_dsp(): phrase_list_ui
    {
        return new phrase_list_ui($this->phrase_list()->api_json());
    }

}