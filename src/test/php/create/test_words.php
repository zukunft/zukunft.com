<?php

/*

    test/create/test_words.php - create the test word objects
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
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::MODEL_VIEW . 'view.php';
include_once paths::MODEL_WORD . 'word.php';
include_once paths::MODEL_WORD . 'word_list.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED_TYPES . 'api_types.php';
include_once paths::SHARED_TYPES . 'phrase_types.php';
include_once paths::SHARED_TYPES . 'protection_types.php';
include_once paths::SHARED_TYPES . 'share_types.php';
include_once html_paths::WORD . 'word.php';
include_once html_paths::WORD . 'word_list.php';
include_once test_paths::CREATE . 'test_const.php';
include_once test_paths::UTILS . 'test_cleanup.php';
include_once test_paths::UTILS . 'test_lib.php';

use Zukunft\ZukunftCom\main\php\cfg\view\view;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\cfg\word\word_list;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\shared\types\phrase_types;
use Zukunft\ZukunftCom\main\php\shared\types\protection_types;
use Zukunft\ZukunftCom\main\php\shared\types\share_types;
use Zukunft\ZukunftCom\main\php\web\word\word as word_ui;
use Zukunft\ZukunftCom\main\php\web\word\word_list as word_list_ui;
use Zukunft\ZukunftCom\test\php\utils\test_lib;

class test_words extends test_objects
{

    /*
     * cleanup
     */

    /**
     * delete any remaining test words for a clean test start
     */
    function cleanup(string $ts): void
    {
        parent::cleanup_objects($ts, words::TEST_WORDS, new word($this->env->usr1));
    }


    /*
     * unit
     */

    /**
     * @return word "mathematics" as the main word for unit testing
     */
    function word(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::MATH_ID, words::MATH);
        $wrd->description = words::MATH_COM;
        $wrd->set_type(phrase_types::NORMAL, $this->env->usr1);
        global $sys;
        $wrd->set_protection_id($sys->typ_lst->ptc_typ->id(protection_types::ADMIN));
        return $wrd;
    }

    /**
     * @return word object where the most specific mandatory var is not set which is in case of a word the id and the name
     */
    function word_incomplete(): word
    {
        $wrd = $this->word();
        $wrd->id = 0;
        $wrd->set_name(null);
        return $wrd;
    }

    /**
     * @return word "mathematics" without the id e.g. as given by the import
     */
    function word_name_only(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set_name(words::MATH);
        return $wrd;
    }

    /**
     * @return word "company" with a suggested view
     */
    function word_view_set(): word
    {
        global $sys;
        $wrd = new word($this->env->usr1);
        $wrd->set(words::COMPANY_ID, words::COMPANY);
        $msk = new view($this->env->usr1);
        $msk->set(views::HISTORIC_ID);
        $wrd->view = $msk;
        $wrd->set_protection_id($sys->typ_lst->ptc_typ->id(protection_types::ADMIN));
        return $wrd;
    }

    /**
     * @return word "mathematics" without the id e.g. as given by the import
     */
    function word_view_not_4_user(): word
    {
        $wrd = $this->word_view_set();
        $wrd->view = null;
        $wrd->set_protection_id(null);
        return $wrd;
    }

    /**
     * @return word "mathematics" with all object variables set for complete unit testing
     */
    function word_filled(): word
    {
        global $sys;
        $wrd = new word($this->env->usr1);
        $wrd->set(words::MATH_ID, words::MATH);
        $wrd->description = words::MATH_COM;
        $wrd->set_type(phrase_types::SCALING, $this->env->usr1);
        $wrd->set_code_id(words::MATH, $this->env->usr_system);
        $wrd->plural = words::MATH_PLURAL;
        $wrd->set_view_id(views::MATH_CONST_ID);
        $wrd->set_usage(test_const::DUMMY_USAGE_WORD);
        $wrd->set_impact(test_const::DUMMY_IMPACT);
        $wrd->exclude();
        $wrd->set_share_id($sys->typ_lst->shr_typ->id(share_types::GROUP));
        $wrd->set_protection_id($sys->typ_lst->ptc_typ->id(protection_types::USER));
        return $wrd;
    }

    /**
     * @return word with all fields set and a reserved test name for testing the db write function
     */
    function word_filled_add(): word
    {
        $wrd = $this->word_filled();
        $wrd->include();
        $wrd->id = 0;
        $wrd->set_name(words::TEST_ADD);
        return $wrd;
    }

    /**
     * @return word with all fields set and another reserved test name for testing the db write function
     */
    function word_filled_add_to(): word
    {
        $wrd = $this->word_filled();
        $wrd->include();
        $wrd->id = 0;
        $wrd->set_name(words::TEST_ADD_TO);
        return $wrd;
    }

    /**
     * @return word to test the sql insert via function
     */
    function word_add_by_func(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set_name(words::TEST_ADD_VIA_FUNC);
        return $wrd;
    }

    /**
     * @return word to test the api insert call
     */
    function word_add_via_api(): word
    {
        global $sys;
        $wrd = new word($this->env->usr1);
        $wrd->set_name(words::TEST_ADD_API);
        $wrd->description = words::TEST_ADD_API_COM;
        $wrd->type_id = $sys->typ_lst->phr_typ->id(phrase_types::NORMAL);
        return $wrd;
    }

    /**
     * @return word to test the api update call
     */
    function word_update_via_api(): word
    {
        global $sys;
        $wrd = new word($this->env->usr1);
        $wrd->set_name(words::TEST_UPD_API);
        $wrd->description = words::TEST_UPD_API_COM;
        $wrd->type_id = $sys->typ_lst->phr_typ->id(phrase_types::MEASURE);
        return $wrd;
    }

    /**
     * @return word_ui the word "mathematics" for frontend unit testing
     */
    function word_dsp(): word_ui
    {
        $wrd = $this->word();
        return new word_ui($wrd->api_json());
    }

    /**
     * @return word "constant" to create the main triple for unit testing
     */
    function word_const(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::CONST_ID, words::CONST_NAME);
        $wrd->description = words::CONST_COM;
        $wrd->set_type(phrase_types::MATH_CONST, $this->env->usr1);
        global $sys;
        $wrd->set_protection_id($sys->typ_lst->ptc_typ->id(protection_types::ADMIN));
        return $wrd;
    }

    /**
     * @return word "Pi" to test the const behavior
     */
    function word_pi_symbol(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::PI_SYMBOL_ID, words::PI_SYMBOL);
        $wrd->description = words::PI_SYMBOL_COM;
        $wrd->set_type(phrase_types::MATH_CONST, $this->env->usr1);
        global $sys;
        $wrd->set_protection_id($sys->typ_lst->ptc_typ->id(protection_types::ADMIN));
        return $wrd;
    }

    /**
     * @return word "Pi" to test the const behavior
     */
    function word_pi(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::PI_ID, words::PI);
        $wrd->description = words::PI_COM;
        $wrd->set_type(phrase_types::MATH_CONST, $this->env->usr1);
        global $sys;
        $wrd->set_protection_id($sys->typ_lst->ptc_typ->id(protection_types::ADMIN));
        return $wrd;
    }

    /**
     * @return word "circumference" to test the const behavior
     */
    function word_cf(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::CIRCUMFERENCE_ID, words::CIRCUMFERENCE);
        return $wrd;
    }

    /**
     * @return word "diameter" to test the const behavior
     */
    function word_diameter(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::DIAMETER_ID, words::DIAMETER);
        return $wrd;
    }

    /**
     * @return word "Euler's number" to test the handling of >'<
     */
    function word_e_symbol(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::E_SYMBOL_ID, words::E_SYMBOL);
        $wrd->set_type(phrase_types::MATH_CONST, $this->env->usr1);
        return $wrd;
    }

    /**
     * @return word "Euler's number" to test the handling of >'<
     */
    function word_e(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::E_ID, words::E);
        $wrd->set_type(phrase_types::MATH_CONST, $this->env->usr1);
        return $wrd;
    }

    /*
     * si units
     */

    function speed(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::SPEED_ID, words::SPEED);
        return $wrd;
    }

    function light(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::LIGHT_ID, words::LIGHT);
        return $wrd;
    }

    function meter(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::METER_ID, words::METER);
        return $wrd;
    }

    function definition(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::DEFINITION_ID, words::DEFINITION);
        return $wrd;
    }

    function hyperfine(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::HYPERFINE_ID, words::HYPERFINE);
        return $wrd;
    }

    function transition(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::TRANSITION_ID, words::TRANSITION);
        return $wrd;
    }

    function frequency(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::FREQUENCY_ID, words::FREQUENCY);
        return $wrd;
    }

    function cs_133(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::CS_133_ID, words::CS_133);
        return $wrd;
    }

    function hz(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::HZ_ID, words::HZ);
        $wrd->set_type(phrase_types::MEASURE, $this->env->usr1);
        $wrd->set_description(words::HZ_COM);
        return $wrd;
    }

    function word_1967(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::YEAR_1967_ID, words::YEAR_1967);
        $wrd->set_type(phrase_types::TIME, $this->env->usr1);
        return $wrd;
    }

    function word_1983(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::YEAR_1983_ID, words::YEAR_1983);
        $wrd->set_type(phrase_types::TIME, $this->env->usr1);
        return $wrd;
    }

    /**
     * @return word year e.g. to test the table row selection
     */
    function word_year(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::YEAR_CAP_ID, words::YEAR_CAP);
        $wrd->set_type(phrase_types::TIME, $this->env->usr1);
        return $wrd;
    }

    /**
     * @return word 2019 to test creating of a year
     */
    function word_2019(): word
    {
        return $this->year_x(words::YEAR_2019_ID, words::YEAR_2019);
    }

    /**
     * @return word 2020 to test creating a year
     */
    function word_2020(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::YEAR_2020_ID, words::YEAR_2020);
        $wrd->set_type(phrase_types::TIME, $this->env->usr1);
        $wrd->set_description(words::YEAR_2020_COM);
        return $wrd;
    }

    function word_2021(): word
    {
        return $this->year_x(words::YEAR_2021_ID, words::YEAR_2021);
    }

    function word_2022(): word
    {
        return $this->year_x(words::YEAR_2022_ID, words::YEAR_2022);
    }

    function word_2023(): word
    {
        return $this->year_x(words::YEAR_2023_ID, words::YEAR_2023);
    }

    function word_2024(): word
    {
        return $this->year_x(words::YEAR_2024_ID, words::YEAR_2024);
    }

    function word_2025(): word
    {
        return $this->year_x(words::YEAR_2025_ID, words::YEAR_2025);
    }

    function word_2026(): word
    {
        return $this->year_x(words::YEAR_2026_ID, words::YEAR_2026);
    }

    function word_2027(): word
    {
        return $this->year_x(words::YEAR_2027_ID, words::YEAR_2027);
    }

    function word_2028(): word
    {
        return $this->year_x(words::YEAR_2028_ID, words::YEAR_2028);
    }

    function word_2029(): word
    {
        return $this->year_x(words::YEAR_2029_ID, words::YEAR_2029);
    }

    function word_2030(): word
    {
        return $this->year_x(words::YEAR_2030_ID, words::YEAR_2030);
    }

    /**
     * @return word of a year with the given id and name
     */
    private function year_x(int $id, string $name): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set($id, $name);
        $wrd->set_type(phrase_types::TIME, $this->env->usr1);
        return $wrd;
    }

    /**
     * @return word per cent to test percent related rules e.g. to remove measure at division
     */
    function word_percent(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::PCT_ID, words::PCT);
        $wrd->set_type(phrase_types::PERCENT, $this->env->usr1);
        return $wrd;
    }

    /**
     * @return word of the master pod name
     */
    function word_zukunft_com(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::MASTER_POD_NAME_ID, words::MASTER_POD_NAME);
        return $wrd;
    }

    /**
     * @return word pod
     */
    function word_pod(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::POD_ID, words::POD);
        return $wrd;
    }

    /**
     * @return word launch
     */
    function word_launch(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::LAUNCH_ID, words::LAUNCH);
        return $wrd;
    }

    /**
     * @return word url
     */
    function word_url(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::URL_ID, words::URL);
        return $wrd;
    }

    /**
     * @return word geo point
     */
    function word_point(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::POINT_ID, words::POINT);
        return $wrd;
    }

// TODO explain for each test object for which test it is used
// TODO rename because in the test object "$t->" the prefix dummy is not needed
    function word_this(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::THIS_ID, words::THIS_NAME);
        $wrd->set_type(phrase_types::THIS, $this->env->usr1);
        return $wrd;
    }

    function word_prior(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::PRIOR_ID, words::PRIOR_NAME);
        $wrd->set_type(phrase_types::PRIOR, $this->env->usr1);
        return $wrd;
    }

    function word_one(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::ONE_ID, words::ONE);
        $wrd->set_type(phrase_types::SCALING_HIDDEN, $this->env->usr1);
        return $wrd;
    }

    function word_math(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::MATH_ID, words::MATH);
        return $wrd;
    }

    function word_mio(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::MIO_ID, words::MIO_SHORT);
        $wrd->set_type(phrase_types::SCALING, $this->env->usr1);
        return $wrd;
    }

    function word_minute(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::MINUTE_ID, words::MINUTE);
        return $wrd;
    }

    function second(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::SECOND_ID, words::SECOND);
        return $wrd;
    }

    function word_ch(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::CH_ID, words::CH);
        return $wrd;
    }

    /**
     * @return word city to test the verb "is a" / "are" to get the list of cities
     */
    function word_city(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::CITY_ID, words::CITY);
        return $wrd;
    }

    /**
     * @return word canton to test the separation of the cantons from the cities based on the same word
     */
    function word_canton(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::CANTON_ID, words::CANTON);
        return $wrd;
    }

    /**
     * @return word with id and name of Zurich
     */
    function word_zh(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::ZH_ID, words::ZH);
        return $wrd;
    }

    /**
     * @return word with id and name of Bern
     */
    function word_bern(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::BE_ID, words::BE);
        return $wrd;
    }

    /**
     * @return word with id and name of Geneva
     */
    function word_ge(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::GE_ID, words::GE);
        return $wrd;
    }

    function word_inhabitant(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::INHABITANT_ID, words::INHABITANTS);
        $wrd->plural = words::INHABITANTS;
        return $wrd;
    }

    function word_parts(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::PARTS_ID, words::PARTS);
        return $wrd;
    }

    function word_total(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::TOTAL_ID, words::TOTAL_PRE);
        return $wrd;
    }

    function word_global(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::GLOBAL_ID, words::GLOBAL);
        return $wrd;
    }

    function word_problem(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::PROBLEM_ID, words::PROBLEM);
        return $wrd;
    }

    function word_potential(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::POTENTIAL_ID, words::POTENTIAL);
        return $wrd;
    }

    function word_climate(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::CLIMATE_ID, words::CLIMATE);
        return $wrd;
    }

    function word_warmer(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::WARMER_ID, words::WARMER);
        return $wrd;
    }

    function word_health(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::HEALTH_ID, words::HEALTH);
        return $wrd;
    }

    function word_populism(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::POPULISM_ID, words::POPULISM);
        return $wrd;
    }

    function word_poverty(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::POVERTY_ID, words::POVERTY);
        return $wrd;
    }

    function word_education(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::EDUCATION_ID, words::EDUCATION);
        return $wrd;
    }

    function word_happy(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::HAPPY_ID, words::HAPPY);
        return $wrd;
    }

    function word_time(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::TIME_ID, words::TIME);
        return $wrd;
    }

    function word_points(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::POINTS_ID, words::POINTS);
        return $wrd;
    }

    function word_trillion(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::TRILLION_ID, words::TRILLION);
        return $wrd;
    }

    function word_billion(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::BILLION_ID, words::BILLION);
        return $wrd;
    }

    function word_chf(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::CHF_ID, words::CHF);
        return $wrd;
    }

    function word_eur(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::EUR_ID, words::EUR);
        return $wrd;
    }

    function word_usd(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::USD_ID, words::USD);
        return $wrd;
    }

    function word_htp(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::HTP_ID, words::HTP);
        return $wrd;
    }

    function word_company(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::COMPANY_ID, words::COMPANY);
        return $wrd;
    }

    function word_abb(): word
    {
        $wrd = new word($this->env->usr1);
        $wrd->set(words::ABB_ID, words::ABB);
        return $wrd;
    }

    function words_canton_zh_inhabitants(): array
    {
        return [words::ZH, words::CANTON, words::INHABITANTS, words::MIO];
    }

    /**
     * @return word_list with some basic words for unit testing
     */
    function word_list(): word_list
    {
        $lst = new word_list($this->env->usr1);
        $lst->add($this->word());
        $lst->add($this->word_const());
        $lst->add($this->word_pi_symbol());
        $lst->add($this->word_e_symbol());
        return $lst;
    }

    /**
     * @return word_list with a few words for unit testing
     */
    function word_list_short(): word_list
    {
        $lst = new word_list($this->env->usr1);
        $lst->add($this->word());
        $lst->add($this->word_pi());
        return $lst;
    }

    /**
     * @return word_list with at least one word of each type for unit testing
     */
    function word_list_all_types(): word_list
    {
        $lst = new word_list($this->env->usr1);
        $lst->add($this->word());
        $lst->add($this->word_const());
        $lst->add($this->word_pi());
        $lst->add($this->word_cf());
        $lst->add($this->word_diameter());
        $lst->add($this->word_e_symbol());
        $lst->add($this->word_e());
        $lst->add($this->word_year());
        $lst->add($this->word_2019());
        $lst->add($this->word_2020());
        $lst->add($this->word_percent());
        $lst->add($this->word_math());
        $lst->add($this->word_one());
        $lst->add($this->word_mio());
        $lst->add($this->word_minute());
        $lst->add($this->second());
        $lst->add($this->word_ch());
        $lst->add($this->word_city());
        $lst->add($this->word_canton());
        $lst->add($this->word_zh());
        $lst->add($this->word_bern());
        $lst->add($this->word_ge());
        $lst->add($this->word_inhabitant());
        $lst->add($this->word_parts());
        $lst->add($this->word_total());
        $lst->add($this->word_problem());
        $lst->add($this->word_global());
        $lst->add($this->word_potential());
        $lst->add($this->word_climate());
        $lst->add($this->word_warmer());
        $lst->add($this->word_health());
        $lst->add($this->word_populism());
        $lst->add($this->word_poverty());
        $lst->add($this->word_education());
        $lst->add($this->word_happy());
        $lst->add($this->word_time());
        $lst->add($this->word_points());
        $lst->add($this->word_trillion());
        $lst->add($this->word_billion());
        $lst->add($this->word_chf());
        $lst->add($this->word_eur());
        $lst->add($this->word_usd());
        $lst->add($this->word_htp());
        $lst->add($this->word_company());
        $lst->add($this->word_abb());
        return $lst;
    }

    function word_list_ui(): word_list_ui
    {
        $tl = new test_lib();
        return $tl->list_to_ui($this->word_list_all_types(), [api_types::INCL_PHRASES]);
    }


    /*
     * speed tests
     */

    /**
     * create a new word with a random id e.g. for speed testing
     *
     * @param int|null $id a given sequence number to assure that the word name is unique
     * @return word the created word object
     */
    function random(?int $id = null): word
    {
        global $sys;

        if ($id == null) {
            $id = $this->env->next_seq_nbr();
        }
        $test_usr = $this->env->usr1;

        $wrd = new word($test_usr);
        $wrd->id = $id;
        $wrd->set_name(words::TEST_SPEED_PREFIX . $id);

        $type_id = rand(1, $sys->typ_lst->phr_typ->count());
        $wrd->set_type_id($type_id, $test_usr);
        return $wrd;
    }

}