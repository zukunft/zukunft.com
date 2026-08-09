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
    function value_zh(): value
    {
        $t_grp = new test_groups($this->env);
        $grp = $t_grp->group_zh_2019();
        return new value($this->env->usr1, values::CITY_ZH_INHABITANTS_2019, $grp);
    }

    /**
     * @return value with the inhabitants of the canton of zurich
     */
    function value_canton(): value
    {
        $t_grp = new test_groups($this->env);
        $grp = $t_grp->group_canton();
        return new value($this->env->usr1, values::CANTON_ZH_INHABITANTS_2020_IN_MIO, $grp);
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
     *               but with "mio" missing the scaling type to test the scaling type check
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
        $lst->add($this->value_zh());
        return $lst;
    }

    /**
     * @return value_list with the standard test values
     */
    function value_list(user_message $msg): value_list
    {
        $lst = new value_list($this->env->usr1);
        $lst->add($this->value($msg));
        $lst->add($this->value_zh());
        return $lst;
    }

    /**
     * @return value_list with all values for selection and paging tests
     */
    function value_list_all(user_message $msg): value_list
    {
        $lst = new value_list($this->env->usr1);
        $lst->add($this->value($msg));
        $lst->add($this->value_zh());
        $lst->add($this->value_canton());
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
        $val_lst->add($this->value_zh());
        $val_lst->add($this->value_canton());
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