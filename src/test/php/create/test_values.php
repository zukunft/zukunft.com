<?php

/*

    test/create/test_values.php - create the test value objects
    ---------------------------


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

include_once paths::MODEL_HELPER . 'config_numbers.php';
include_once paths::MODEL_PHRASE . 'phrase.php';
include_once paths::MODEL_PHRASE . 'phrase_list.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::MODEL_VALUE . 'value.php';
include_once paths::MODEL_VALUE . 'value_base.php';
include_once paths::MODEL_VALUE . 'value_geo.php';
include_once paths::MODEL_VALUE . 'value_list.php';
include_once paths::MODEL_VALUE . 'value_text.php';
include_once paths::MODEL_VALUE . 'value_time.php';
include_once paths::MODEL_VALUE . 'value_time_series.php';
include_once paths::MODEL_VALUE . 'value_ts_data.php';
include_once paths::MODEL_VALUE . 'value_list.php';
include_once paths::SHARED_CONST . 'values.php';
include_once paths::SHARED_TYPES . 'api_types.php';
include_once paths::SHARED_TYPES . 'protection_types.php';
include_once paths::SHARED_TYPES . 'share_types.php';
include_once html_paths::VALUE . 'value.php';
include_once html_paths::VALUE . 'value_list.php';
include_once test_paths::CONST . 'word_names.php';
include_once test_paths::UTILS . 'test_cleanup.php';
include_once test_paths::UTILS . 'test_lib.php';

use Zukunft\ZukunftCom\main\php\cfg\helper\config_numbers;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\value\value;
use Zukunft\ZukunftCom\main\php\cfg\value\value_base;
use Zukunft\ZukunftCom\main\php\cfg\value\value_geo;
use Zukunft\ZukunftCom\main\php\cfg\value\value_list;
use Zukunft\ZukunftCom\main\php\cfg\value\value_text;
use Zukunft\ZukunftCom\main\php\cfg\value\value_time;
use Zukunft\ZukunftCom\main\php\cfg\value\value_time_series;
use Zukunft\ZukunftCom\main\php\cfg\value\value_ts_data;
use Zukunft\ZukunftCom\main\php\shared\const\values;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\shared\types\protection_types;
use Zukunft\ZukunftCom\main\php\shared\types\share_types;
use Zukunft\ZukunftCom\main\php\web\value\value as value_ui;
use Zukunft\ZukunftCom\main\php\web\value\value_list as value_list_ui;
use Zukunft\ZukunftCom\test\php\utils\test_lib;
use DateTime;

class test_values extends test_objects
{

    /*
     * cleanup
     */

    /**
     * delete any remaining test words for a clean test start
     */
    function cleanup(string $ts): void
    {
        parent::cleanup_objects($ts, values::TEST_VALUES, new value_base($this->env->usr1));
    }


    /*
     * unit
     */

    function value(user_message $msg): value
    {
        $t_grp = new test_groups($this->env);
        $grp = $t_grp->group();
        return new value($this->env->usr1, round(values::PI_LONG, 13), $grp);
    }

    /**
     * the standard test value but with a non-default (admin) protection so that the
     * frontend value title shows the protection in its subtitle, mirroring the word
     * @return value the test value with admin protection set
     */
    function value_protected(user_message $msg): value
    {
        global $sys;
        $val = $this->value($msg);
        $val->set_protection_id($sys->typ_lst->ptc_typ->id(protection_types::ADMIN));
        return $val;
    }

    /**
     * @return value_ui the standard test value with the source and the time of the last update
     *                  as the api sends them for a page request, so that the source link and
     *                  the last update line of the value default page can be tested
     */
    function value_page_ui(user_message $msg): value_ui
    {
        $t_src = new test_sources($this->env);
        $val = $this->value($msg);
        $val->set_source($t_src->source_reserved());
        $val->set_last_update(new DateTime(test_const::DUMMY_DATETIME));
        return new value_ui($val->api_json([api_types::INCL_RELATED, api_types::TEST_MODE], $msg));
    }

    function value_incomplete(user_message $msg): value
    {
        $t_grp = new test_groups($this->env);
        $val = $this->value($msg);
        $val->set_grp($t_grp->group_incomplete());
        return $val;
    }

    function value_pi(): value
    {
        $t_grp = new test_groups($this->env);
        $grp = $t_grp->group_pi_symbol();
        return new value($this->env->usr1, round(values::PI_LONG, 13), $grp);
    }

    /**
     * @return value the pi number value as keyed in the seeded database by the "Pi (math)" triple
     */
    function value_pi_math(): value
    {
        $t_grp = new test_groups($this->env);
        $grp = $t_grp->group_pi_math();
        return new value($this->env->usr1, round(values::PI_LONG, 13), $grp);
    }

    function value_e(): value
    {
        $t_grp = new test_groups($this->env);
        $grp = $t_grp->group_e();
        return new value($this->env->usr1, round(values::E, 13), $grp);
    }

    function time_value(): value_time
    {
        $t_grp = new test_groups($this->env);
        $grp = $t_grp->group_pod_launch();
        return new value_time($this->env->usr1, new DateTime(values::TIME), $grp);
    }

    function text_value(): value_text
    {
        $t_grp = new test_groups($this->env);
        $grp = $t_grp->group_pod_url();
        return new value_text($this->env->usr1, values::TEXT, $grp);
    }

    function text_value_prime(): value_text
    {
        $t_grp = new test_groups($this->env);
        $grp = $t_grp->group_prime_max();
        return new value_text($this->env->usr1, values::TEXT, $grp);
    }

    function geo_value(): value_geo
    {
        $t_grp = new test_groups($this->env);
        $grp = $t_grp->group_pod_point();
        return new value_geo($this->env->usr1, values::GEO, $grp);
    }

    /**
     * @return value test that the number zero is written to the database
     */
    function value_zero(): value
    {
        $t_grp = new test_groups($this->env);
        $grp = $t_grp->group();
        return new value($this->env->usr1, values::SAMPLE_ZERO, $grp);
    }

    /**
     * @return value with more than one prime phrase
     */
    function value_prime_3(): value
    {
        $t_grp = new test_groups($this->env);
        $grp = $t_grp->group_prime_3();
        return new value($this->env->usr1, round(values::PI_LONG, 13), $grp);
    }

    /**
     * @return value with the maximal number of prime phrase
     */
    function value_prime_max(): value
    {
        $t_grp = new test_groups($this->env);
        $grp = $t_grp->group_prime_max();
        return new value($this->env->usr1, round(values::PI_LONG, 13), $grp);
    }

    /**
     * @return value with the share type set
     */
    function value_shared(value $val): value
    {
        global $sys;
        $val_upd = clone $val;
        $val_upd->set_share_id($sys->typ_lst->shr_typ->id(share_types::GROUP));
        return $val_upd;
    }

    function value_add(phrase $phr): value
    {
        $lst = new phrase_list($this->env->usr1);
        $lst->add($phr);
        $grp = $lst->get_grp_id(false);
        return new value($this->env->usr1, values::SAMPLE_FLOAT, $grp);
    }

    /**
     * @param phrase[] $phrases the phrases that should build the group of the value
     * @param float $number the number assigned to the value (a sample number by default)
     * @return value with the given number assigned to the group of the given phrases
     */
    function value_for_phrases(array $phrases, float $number = values::SAMPLE_FLOAT): value
    {
        $lst = new phrase_list($this->env->usr1);
        foreach ($phrases as $phr) {
            $lst->add($phr);
        }
        $grp = $lst->get_grp_id(false);
        return new value($this->env->usr1, $number, $grp);
    }

    /**
     * the system configuration with only the permission that decides
     * if a user without login (an ip user) can change data in the database
     * the yaml true of config.yaml is imported as one and the yaml false as zero
     *
     * @param bool $permitted true if an ip user is allowed to change data in the database
     * @return config_numbers the pod configuration with the ip user database change permission set
     */
    function config_ip_user_change(bool $permitted): config_numbers
    {
        $t_phr = new test_phrases($this->env);
        $grp = $t_phr->phrase_list_ip_user_change()->get_grp_id(false);
        $val = new value($this->env->usr1, (float)$permitted, $grp);
        $cfg = new config_numbers($this->env->usr1);
        // the config phrases have no database id, so the group id of the value is empty
        // and add() would skip the value, because it adds only objects with an id
        $cfg->set_lst([$val]);
        return $cfg;
    }

    /**
     * @param array $names the phrase names of the database cache switch e.g. a config_numbers::CACHE_ALLOWED_NAMES row
     * @param bool $allowed true if the cache switch should permit the cache usage
     * @return config_numbers a pod configuration with the given database cache switch
     */
    function config_cache_switch(array $names, bool $allowed): config_numbers
    {
        $t_phr = new test_phrases($this->env);
        $grp = $t_phr->list_cache_switch($names)->get_grp_id(false);
        $val = new value($this->env->usr1, (float)$allowed, $grp);
        $cfg = new config_numbers($this->env->usr1);
        // the config phrases have no database id, so the group id of the value is empty
        // and add() would skip the value, because it adds only objects with an id
        $cfg->set_lst([$val]);
        return $cfg;
    }

    /**
     * @return config_numbers a pod configuration without any config value
     */
    function config_empty(): config_numbers
    {
        return new config_numbers($this->env->usr1);
    }

    /**
     * a value list to test the "most relevant" value list component: two year groups (2022 newest and
     * 2021, each shared by two values), a phrase group (three values sharing the phrase "ABB") and one
     * ungrouped value; see value_list::list_most_relevant and docs/llm/pending_next_launch.md
     * @return value_list with time-grouped, phrase-grouped and ungrouped values
     */
    function value_list_most_relevant(): value_list
    {
        $t_wrd = new test_words($this->env);
        $inhab = $t_wrd->word_inhabitant()->phrase();
        $zh = $t_wrd->word_zh()->phrase();
        $bern = $t_wrd->word_bern()->phrase();
        $abb = $t_wrd->word_abb()->phrase();
        $vestas = $t_wrd->word_vestas()->phrase();
        $pi = $t_wrd->word_pi()->phrase();
        $y2021 = $t_wrd->word_2021()->phrase();
        $y2022 = $t_wrd->word_2022()->phrase();

        $lst = new value_list($this->env->usr1);
        // time group 2022 (newest, shown first): two values share the year 2022
        $lst->add($this->value_for_phrases([$inhab, $zh, $y2022], 434008));
        $lst->add($this->value_for_phrases([$inhab, $bern, $y2022], 134591));
        // time group 2021: two values share the year 2021
        $lst->add($this->value_for_phrases([$inhab, $zh, $y2021], 421878));
        $lst->add($this->value_for_phrases([$inhab, $bern, $y2021], 133883));
        // phrase group "ABB": three values share the phrase ABB (no time)
        $lst->add($this->value_for_phrases([$abb, $zh], 12.3));
        $lst->add($this->value_for_phrases([$abb, $bern], 4.5));
        $lst->add($this->value_for_phrases([$abb, $vestas], 6.7));
        // one ungrouped value shown below by impact
        $lst->add($this->value_for_phrases([$pi], 3.1415));
        return $lst;
    }

    /**
     * @return value_list_ui the frontend "most relevant" test value list
     */
    function value_list_most_relevant_ui(): value_list_ui
    {
        $tl = new test_lib();
        return $tl->list_to_ui($this->value_list_most_relevant(), [api_types::INCL_PHRASES]);
    }

    /**
     * a value list that groups into many small time groups: two values per year over seven years,
     * so that no single group reaches the value list limit but the page total does
     * e.g. to test that the total number of shown values is limited however the values are grouped
     *
     * @return value_list with more values in many small groups than the value list limit
     */
    function value_list_many_year_groups(): value_list
    {
        $t_wrd = new test_words($this->env);
        $inhab = $t_wrd->word_inhabitant()->phrase();
        $zh = $t_wrd->word_zh()->phrase();
        $bern = $t_wrd->word_bern()->phrase();
        $years = [
            $t_wrd->word_2019(), $t_wrd->word_2020(), $t_wrd->word_2021(), $t_wrd->word_2022(),
            $t_wrd->word_2023(), $t_wrd->word_2024(), $t_wrd->word_2025()
        ];

        $lst = new value_list($this->env->usr1);
        $number = 400000;
        foreach ($years as $yr) {
            $lst->add($this->value_for_phrases([$inhab, $zh, $yr->phrase()], $number));
            $number++;
            $lst->add($this->value_for_phrases([$inhab, $bern, $yr->phrase()], $number));
            $number++;
        }
        return $lst;
    }

    /**
     * @return value_list_ui the frontend value list with many small year groups
     */
    function value_list_many_year_groups_ui(): value_list_ui
    {
        $tl = new test_lib();
        return $tl->list_to_ui($this->value_list_many_year_groups(), [api_types::INCL_PHRASES]);
    }

    /**
     * a value list where all values share one phrase, so that they form a single group with more
     * members than the configured value list limit e.g. to test that a group is shortened
     * each value has its own year, so that the values are grouped by the shared phrase
     * and not by the time word
     *
     * @return value_list with more values in one group than the value list limit
     */
    function value_list_large_group(): value_list
    {
        $t_wrd = new test_words($this->env);
        $inhab = $t_wrd->word_inhabitant()->phrase();
        $zh = $t_wrd->word_zh()->phrase();
        $years = [
            $t_wrd->word_2019(), $t_wrd->word_2020(), $t_wrd->word_2021(), $t_wrd->word_2022(),
            $t_wrd->word_2023(), $t_wrd->word_2024(), $t_wrd->word_2025(), $t_wrd->word_2026()
        ];

        $lst = new value_list($this->env->usr1);
        $number = 400000;
        foreach ($years as $yr) {
            $lst->add($this->value_for_phrases([$inhab, $zh, $yr->phrase()], $number));
            $number++;
        }
        return $lst;
    }

    /**
     * @return value_list_ui the frontend value list with one group above the value list limit
     */
    function value_list_large_group_ui(): value_list_ui
    {
        $tl = new test_lib();
        return $tl->list_to_ui($this->value_list_large_group(), [api_types::INCL_PHRASES]);
    }

    /**
     * @return value with the maximal number of prime phrase
     */
    function value_main(): value
    {
        $t_grp = new test_groups($this->env);
        $grp = $t_grp->group_main_max();
        return new value($this->env->usr1, round(values::PI_LONG, 13), $grp);
    }

    function value_16(): value
    {
        $t_grp = new test_groups($this->env);
        $grp = $t_grp->group_16();
        return new value($this->env->usr1, round(values::PI_LONG, 13), $grp);
    }

    function value_16_filled(): value
    {
        global $sys;
        $t_src = new test_sources($this->env);
        $t_grp = new test_groups($this->env);
        $grp = $t_grp->group_16();
        $val = new value($this->env->usr1, round(values::PI_LONG, 13), $grp);
        $val->set_source($t_src->source_reserved());
        $val->exclude();
        $val->set_share_id($sys->typ_lst->shr_typ->id(share_types::GROUP));
        $val->set_protection_id($sys->typ_lst->ptc_typ->id(protection_types::USER));
        return $val;
    }

    function value_17_plus(): value
    {
        $t_grp = new test_groups($this->env);
        $grp = $t_grp->group_17_plus();
        return new value($this->env->usr1, round(values::PI_LONG, 13), $grp);
    }


    /*
     * si units
     */

    function transition_cs_133(): value
    {
        $t_grp = new test_groups($this->env);
        $t_src = new test_sources($this->env);
        $grp = $t_grp->transition_cs_133();
        $val = new value($this->env->usr1, round(values::TRANSITION_OF_CS, 13), $grp);
        $val->set_source($t_src->source_reserved());
        return $val;
    }

    function light_speed(): value
    {
        $t_grp = new test_groups($this->env);
        $t_src = new test_sources($this->env);
        $grp = $t_grp->group_speed_of_light();
        $val = new value($this->env->usr1, round(values::SPEED_OF_LIGHT, 13), $grp);
        $val->set_source($t_src->source_reserved());
        return $val;
    }

    function light_speed_with_two_units(): value
    {
        $t_grp = new test_groups($this->env);
        $t_src = new test_sources($this->env);
        $grp = $t_grp->group_speed_of_light_with_two_units();
        $val = new value($this->env->usr1, round(values::SPEED_OF_LIGHT, 13), $grp);
        $val->set_source($t_src->source_reserved());
        return $val;
    }

    /**
     * @return value with the inhabitants of the city of zurich
     */
    function people_zh(): value
    {
        $t_grp = new test_groups($this->env);
        $grp = $t_grp->group_zh_2019();
        return new value($this->env->usr1, values::CITY_ZH_INHABITANTS_2019, $grp);
    }

    /**
     * @return value with the inhabitants of the canton of zurich
     */
    /**
     * @return value the inhabitants of the canton Zurich scaled with the symbol word "mio",
     *               so that the symbol tooltip of the related word "million" can be tested
     */
    function people_zh_canton_mio_symbol(): value
    {
        $t_grp = new test_groups($this->env);
        $grp = $t_grp->group_canton_mio_symbol();
        return new value($this->env->usr1, values::CANTON_ZH_INHABITANTS_2020_IN_MIO, $grp);
    }

    function people_zh_canton_mio_symbol_ui(): value_ui
    {
        $tl = new test_lib();
        return $tl->ui_value($this->people_zh_canton_mio_symbol());
    }

    function people_zh_canton_mio(): value
    {
        $t_grp = new test_groups($this->env);
        $grp = $t_grp->group_canton();
        return new value($this->env->usr1, values::CANTON_ZH_INHABITANTS_2020_IN_MIO, $grp);
    }

    function people_zh_canton_mio_ui(): value_ui
    {
        $tl = new test_lib();
        return $tl->ui_value($this->people_zh_canton_mio());
    }

    /**
     * @return value with the inhabitants of Switzerland
     */
    function value_ch(): value
    {
        $t_grp = new test_groups($this->env);
        $grp = $t_grp->group_ch();
        return new value($this->env->usr1, values::CH_INHABITANTS_2019_IN_MIO, $grp);
    }

    /**
     * @return value with the inhabitants of Switzerland
     *               but with "million" missing the scaling type to test the scaling type check
     */
    function value_ch_unscaled(): value
    {
        $t_phr = new test_phrases($this->env);
        $grp = $t_phr->ch_inhabitants_in_mio_2019_unscaled()->get_grp_id(false);
        return new value($this->env->usr1, values::CH_INHABITANTS_2019_IN_MIO, $grp);
    }

    /**
     * @return value_list with only a few values for first basic tests
     */
    function value_list_short(user_message $msg): value_list
    {
        $lst = new value_list($this->env->usr1);
        $lst->add($this->value($msg));
        $lst->add($this->people_zh());
        return $lst;
    }

    /**
     * @return value_list with the standard test values
     */
    function value_list(user_message $msg): value_list
    {
        $lst = new value_list($this->env->usr1);
        $lst->add($this->value($msg));
        $lst->add($this->people_zh());
        return $lst;
    }

    /**
     * @return value_list with all values for selection and paging tests
     */
    function value_list_all(user_message $msg): value_list
    {
        $lst = new value_list($this->env->usr1);
        $lst->add($this->value($msg));
        $lst->add($this->people_zh());
        $lst->add($this->people_zh_canton_mio());
        $lst->add($this->value_ch());
        $lst->add($this->value_pi());
        $lst->add($this->value_e());
        $lst->add($this->transition_cs_133());
        $lst->add($this->light_speed());
        return $lst;
    }

    // TODO Prio 1 easy: move all test object creation to this class
    function value_list_math(): value_list
    {
        $lst = new value_list($this->env->usr1);
        $lst->add($this->value_pi());
        $lst->add($this->value_e());
        return $lst;
    }

    /**
     * @return value_list with the test values for the word zurich
     */
    function value_list_zh(): value_list
    {
        $val_lst = new value_list($this->env->usr1);
        $val_lst->add($this->people_zh());
        $val_lst->add($this->people_zh_canton_mio());
        $val_lst->add($this->value_ch());
        return $val_lst;
    }

    // TODO Prio 1 easy: rename a _dsp functions and object to _ui
    function value_list_ui(user_message $msg): value_list_ui
    {
        $tl = new test_lib();
        return $tl->list_to_ui($this->value_list($msg), [api_types::INCL_PHRASES]);
    }

    function value_list_zh_ui(): value_list_ui
    {
        $tl = new test_lib();
        return $tl->list_to_ui($this->value_list_zh(), [api_types::INCL_PHRASES]);
    }

    /**
     * the potential loss and the potential gain of every global problem of solution_prio.json,
     * so that the start page table of the global issues can be tested with the data that the
     * import creates: per problem the loss in trillion EUR and the gain of its solution in
     * billion htp, the eight problems whose numbers are an estimate marked with "assumed"
     *
     * @return value_list the cost and gain values of the global problems as defined in
     *         solution_prio.json, e.g. to test the start page table of the global issues
     */
    function value_list_solution_prio(): value_list
    {
        $t_wrd = new test_words($this->env);
        $t_trp = new test_triples($this->env);
        $potential = $t_wrd->word_potential()->phrase();
        $loss = $t_wrd->word_loss()->phrase();
        $gain = $t_wrd->word_gain()->phrase();
        $trillion = $t_wrd->word_trillion()->phrase();
        $billion = $t_wrd->word_billion()->phrase();
        $eur = $t_wrd->word_eur()->phrase();
        $htp = $t_wrd->word_htp()->phrase();
        $assumed = $t_wrd->word_assumed()->phrase();
        // per problem the phrase, the loss, the solution phrase, the gain and whether the two
        // numbers are estimated, in the order of the start page ranking
        $prio_lst = [
            [$t_trp->global_warming()->phrase(), 31.5, $t_trp->reduce_emissions()->phrase(), 35.2, false],
            [$t_wrd->word_populism()->phrase(), 23.8, $t_trp->avoid_wrong_decisions()->phrase(), 34.1, false],
            [$t_wrd->word_poverty()->phrase(), 20.4, $t_wrd->word_research()->phrase(), 34.1, false],
            [$t_wrd->word_health()->phrase(), 13.6, $t_wrd->word_taxes()->phrase(), 8.8, false],
            [$t_wrd->word_education()->phrase(), 9.4, $t_wrd->word_spending()->phrase(), 14.3, false],
            [$t_trp->wealth_concentration()->phrase(), 11.2, $t_trp->basic_income()->phrase(), 16, true],
            [$t_wrd->word_disinformation()->phrase(), 8, $t_trp->platform_regulation()->phrase(), 12, true],
            [$t_trp->market_power()->phrase(), 7.3, $t_trp->market_share_tax()->phrase(), 6, true],
            [$t_trp->biased_information()->phrase(), 6.5, $t_trp->delphi_method()->phrase(), 9, true],
            [$t_trp->black_box_ai()->phrase(), 4.5, $t_trp->public_ai()->phrase(), 7, true],
            [$t_trp->citizen_participation()->phrase(), 3.2, $t_trp->fluid_democracy()->phrase(), 5.5, true],
            [$t_trp->gdp_mismeasurement()->phrase(), 2.5, $t_trp->gross_domestic_usage()->phrase(), 4, true],
            [$t_trp->proprietary_software()->phrase(), 1.8, $t_trp->free_software()->phrase(), 3, true],
        ];
        $lst = new value_list($this->env->usr1);
        foreach ($prio_lst as [$problem, $loss_nbr, $solution, $gain_nbr, $is_assumed]) {
            $loss_phr = [$problem, $potential, $loss, $trillion, $eur];
            $gain_phr = [$problem, $solution, $potential, $gain, $billion, $htp];
            if ($is_assumed) {
                $loss_phr[] = $assumed;
                $gain_phr[] = $assumed;
            }
            $lst->add($this->value_for_phrases($loss_phr, $loss_nbr));
            $lst->add($this->value_for_phrases($gain_phr, $gain_nbr));
        }
        return $lst;
    }

    /**
     * @return value_list_ui the solution_prio cost and gain values for frontend unit testing
     */
    function value_list_solution_prio_ui(): value_list_ui
    {
        $tl = new test_lib();
        return $tl->list_to_ui($this->value_list_solution_prio(), [api_types::INCL_PHRASES]);
    }

    /**
     * the potential loss of global warming with the bounds of its probability range and its
     * confidence and the potential gain of its solution without bounds, with the numbers of
     * solution_prio.json, so that the range display of a value table can be tested: the bounds
     * are tagged "low" and "high", the confidence "confidence" and the loss is an estimate, so
     * it carries "assumed"
     *
     * @return value_list the centre, low and high potential loss, its confidence and the gain
     */
    function value_list_range(): value_list
    {
        $t_wrd = new test_words($this->env);
        $t_trp = new test_triples($this->env);
        $problem = $t_trp->global_warming()->phrase();
        $potential = $t_wrd->word_potential()->phrase();
        $loss_phr = [$problem, $potential, $t_wrd->word_loss()->phrase(),
            $t_wrd->word_trillion()->phrase(), $t_wrd->word_eur()->phrase(),
            $t_wrd->word_assumed()->phrase()];
        $low_phr = array_merge($loss_phr, [$t_wrd->word_low()->phrase()]);
        $high_phr = array_merge($loss_phr, [$t_wrd->word_high()->phrase()]);
        // the confidence is a share, so it names no unit of the loss but the percent format
        $conf_phr = [$problem, $potential, $t_wrd->word_loss()->phrase(),
            $t_wrd->word_confidence()->phrase(), $t_wrd->word_percent()->phrase(),
            $t_wrd->word_assumed()->phrase()];
        $gain_phr = [$problem, $potential, $t_wrd->word_gain()->phrase(),
            $t_wrd->word_billion()->phrase(), $t_wrd->word_htp()->phrase()];
        $lst = new value_list($this->env->usr1);
        $lst->add($this->value_for_phrases($loss_phr, 2.2));
        $lst->add($this->value_for_phrases($low_phr, 0.88));
        $lst->add($this->value_for_phrases($high_phr, 5.5));
        $lst->add($this->value_for_phrases($conf_phr, 0.2));
        $lst->add($this->value_for_phrases($gain_phr, 35.2));
        return $lst;
    }

    /**
     * one value per defined column of a table, so that a table with more defined columns than
     * fit on the widest screen can be tested: every defined column is shown and the tiers hide
     * them per screen size instead of a fixed number of columns
     *
     * @return value_list one value for each of the "problem", "solution", "cost", "gain" and
     *         "loss" columns, all about global warming
     */
    function value_list_defined_columns(): value_list
    {
        $t_wrd = new test_words($this->env);
        $t_trp = new test_triples($this->env);
        $problem = $t_trp->global_warming()->phrase();
        $col_phr_lst = [
            $t_wrd->word_problem()->phrase(),
            $t_wrd->solution()->phrase(),
            $t_wrd->word_cost()->phrase(),
            $t_wrd->word_gain()->phrase(),
            $t_wrd->word_loss()->phrase(),
        ];
        $lst = new value_list($this->env->usr1);
        $number = 1;
        foreach ($col_phr_lst as $col_phr) {
            $lst->add($this->value_for_phrases([$problem, $col_phr], $number));
            $number++;
        }
        return $lst;
    }

    /**
     * @return value_list_ui one value per defined column for frontend unit testing
     */
    function value_list_defined_columns_ui(): value_list_ui
    {
        $tl = new test_lib();
        return $tl->list_to_ui($this->value_list_defined_columns(), [api_types::INCL_PHRASES]);
    }

    /**
     * @return value_list_ui the potential loss with its range for frontend unit testing
     */
    function value_list_range_ui(): value_list_ui
    {
        $tl = new test_lib();
        return $tl->list_to_ui($this->value_list_range(), [api_types::INCL_PHRASES]);
    }

    /**
     * the potential loss of global warming in two units, with the numbers of solution_prio.json:
     * the absolute loss in trillion EUR and the loss as a share of the global happy time points,
     * so that a table with one column per unit can be tested
     *
     * @return value_list the potential loss in trillion EUR and in percent htp
     */
    function value_list_two_units(): value_list
    {
        $t_wrd = new test_words($this->env);
        $t_trp = new test_triples($this->env);
        $problem = $t_trp->global_warming()->phrase();
        $potential = $t_wrd->word_potential()->phrase();
        $loss = $t_wrd->word_loss()->phrase();
        $eur_phr = [$problem, $potential, $loss, $t_wrd->word_trillion()->phrase(),
            $t_wrd->word_eur()->phrase(), $t_wrd->word_assumed()->phrase()];
        $htp_phr = [$problem, $potential, $loss, $t_wrd->word_percent()->phrase(),
            $t_wrd->word_htp()->phrase()];
        $lst = new value_list($this->env->usr1);
        $lst->add($this->value_for_phrases($eur_phr, 2.2));
        $lst->add($this->value_for_phrases($htp_phr, -0.37));
        return $lst;
    }

    /**
     * @return value_list_ui the potential loss in two units for frontend unit testing
     */
    function value_list_two_units_ui(): value_list_ui
    {
        $tl = new test_lib();
        return $tl->list_to_ui($this->value_list_two_units(), [api_types::INCL_PHRASES]);
    }

    /**
     * two values related to the word Zurich but assigned to phrases of a different impact
     * so that the sort by impact and the display on the default word page can be tested
     * @return value_list with values related to Zurich of a low and a high impact
     */
    function value_list_zh_impact(): value_list
    {
        $t_wrd = new test_words($this->env);
        $t_trp = new test_triples($this->env);
        $zh = $t_wrd->word_zh()->phrase();
        $lst = new value_list($this->env->usr1);
        $lst->add($this->value_for_phrases([$zh, $t_trp->zh_city_low_impact()->phrase()]));
        $lst->add($this->value_for_phrases([$zh, $t_trp->company_zurich_high_impact()->phrase()]));
        return $lst;
    }

    function value_list_zh_impact_ui(): value_list_ui
    {
        $tl = new test_lib();
        return $tl->list_to_ui($this->value_list_zh_impact(), [api_types::INCL_PHRASES]);
    }

    /**
     * two values with the same (zero) impact and the same number but assigned to phrases with a
     * different name ("Zurich" and "city"), so that the deterministic tie break by the group name
     * can be tested independent of the volatile phrase group id (see value_list::sort_by_impact)
     * @return value_list_ui the ui value list with the two number-tie values
     */
    function value_list_number_tie_ui(): value_list_ui
    {
        $tl = new test_lib();
        $t_wrd = new test_words($this->env);
        $lst = new value_list($this->env->usr1);
        $lst->add($this->value_for_phrases([$t_wrd->word_zh()->phrase()], values::SAMPLE_FLOAT));
        $lst->add($this->value_for_phrases([$t_wrd->word_city()->phrase()], values::SAMPLE_FLOAT));
        return $tl->list_to_ui($lst, [api_types::INCL_PHRASES]);
    }

    function value_list_math_ui(): value_list_ui
    {
        $tl = new test_lib();
        return $tl->list_to_ui($this->value_list_math(), [api_types::INCL_PHRASES]);
    }

    /**
     * two values that name the reserved test source and one value without a source, so that the
     * value list of the source default view can be tested for both the included and the excluded case
     * @return value_list_ui the ui value list with the values of the reserved test source
     */
    function list_by_source_ui(): value_list_ui
    {
        $tl = new test_lib();
        $lst = new value_list($this->env->usr1);
        $lst->add($this->transition_cs_133());
        $lst->add($this->light_speed());
        $lst->add($this->people_zh());
        return $tl->list_to_ui($lst, [api_types::INCL_PHRASES]);
    }

    function list_all_ui(user_message $msg): value_list_ui
    {
        $tl = new test_lib();
        return $tl->list_to_ui($this->value_list_all($msg), [api_types::INCL_PHRASES]);
    }

    /**
     * @return value_time_series e.g. to test the table and index creation
     */
    function value_time_series(): value_time_series
    {
        $t_grp = new test_groups($this->env);
        $vts = new value_time_series($this->env->usr1);
        $vts->set_grp($t_grp->group_16());
        return $vts;
    }

    /**
     * @return value_ts_data for testing e.g. to test matrix calculations
     */
    function value_ts_data(): value_ts_data
    {
        $ts = new value_ts_data();
        $ts->value = round(values::PI_LONG, 13);
        return $ts;
    }

}