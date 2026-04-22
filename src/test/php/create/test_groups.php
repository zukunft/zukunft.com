<?php

/*

    test/create/test_groups.php - create the test group objects
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

include_once paths::MODEL_GROUP . 'group.php';
include_once paths::MODEL_GROUP . 'group_list.php';
include_once paths::MODEL_PHRASE . 'phrase.php';
include_once paths::MODEL_PHRASE . 'phrase_list.php';
include_once paths::SHARED_CONST . 'groups.php';

use Zukunft\ZukunftCom\main\php\cfg\group\group;
use Zukunft\ZukunftCom\main\php\cfg\group\group_list;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\shared\const\groups;

class test_groups extends test_objects
{

    /*
     * cleanup
     */

    /**
     * delete any remaining test groups for a clean test start
     */
    function cleanup(string $ts): void
    {
        parent::cleanup_objects($ts, groups::TEST_GROUPS_CREATE, new group($this->env->usr1));
    }


    /*
     * unit
     */

    /**
     * @return group with one prime phrase
     */
    function group(): group
    {
        $t_phr = new test_phrases($this->env);
        $lst = $t_phr->phrase_list_pi();
        $grp = $lst->get_grp_id(false);
        $grp->name = groups::TN_READ;
        return $grp;
    }

    /**
     * @return group with one prime phrase and all object vars set
     */
    function group_filled(): group
    {
        $grp = $this->group();
        $grp->description = groups::TN_READ_COM;
        return $grp;
    }

    /**
     * @return group object where the most specific mandatory var is not set which is in case of a group the id and the name
     */
    function group_incomplete(): group
    {
        $ref = $this->group();
        $ref->set_id('');
        return $ref;
    }

    function group_add(phrase $phr): group
    {
        $lst = new phrase_list($this->env->usr1);
        $lst->add($phr);
        return $lst->get_grp_id(false);
    }

    /**
     * @return group with one prime phrase
     */
    function group_pi_symbol(): group
    {
        $t_phr = new test_phrases($this->env);
        $lst = $t_phr->phrase_list_pi_symbol();
        $grp = $lst->get_grp_id(false);
        $grp->name = groups::TN_READ;
        return $grp;
    }

    /**
     * @return group with one prime phrases
     */
    function group_e(): group
    {
        $t_phr = new test_phrases($this->env);
        $lst = $t_phr->phrase_list_e();
        $grp = $lst->get_grp_id(false);
        $grp->name = groups::TN_READ;
        return $grp;
    }

    /**
     * @return group for the si unit speed of light
     */
    function group_speed_of_light(): group
    {
        $t_phr = new test_phrases($this->env);
        $lst = $t_phr->si_unit_speed_of_light();
        $grp = $lst->get_grp_id(false);
        $grp->name = groups::LENGTH_DEFINITION;
        return $grp;
    }

    /**
     * @return group for the si unit speed of light
     */
    function group_speed_of_light_with_two_units(): group
    {
        $t_wrd = new test_words($this->env);
        $t_phr = new test_phrases($this->env);
        $lst = $t_phr->si_unit_speed_of_light();
        $lst->add($t_wrd->Hz());
        $grp = $lst->get_grp_id(false);
        $grp->name = groups::LENGTH_DEFINITION;
        return $grp;
    }

    /**
     * @return group for the si unit hyperfine transition frequency of Cs
     */
    function transition_cs_133(): group
    {
        $t_phr = new test_phrases($this->env);
        $lst = $t_phr->si_unit_transition_cs_133();
        $grp = $lst->get_grp_id(false);
        $grp->name = groups::TIME_DEFINITION;
        return $grp;
    }

    /**
     * @return group with the phrases of the launch date of this pod
     */
    function group_pod_launch(): group
    {
        $t_phr = new test_phrases($this->env);
        $lst = $t_phr->phrase_list_pod_launch();
        $grp = $lst->get_grp_id(false);
        $grp->name = groups::TN_TIME_VALUE;
        $grp->description = groups::TD_TIME_VALUE;
        return $grp;
    }

    /**
     * @return group with the phrases of the url of this pod
     */
    function group_pod_url(): group
    {
        $t_phr = new test_phrases($this->env);
        $lst = $t_phr->phrase_list_pod_url();
        $grp = $lst->get_grp_id(false);
        $grp->name = groups::TN_TEXT_VALUE;
        $grp->description = groups::TD_TEXT_VALUE;
        return $grp;
    }

    /**
     * @return group with the phrases of the geolocation of this pod
     */
    function group_pod_point(): group
    {
        $t_phr = new test_phrases($this->env);
        $lst = $t_phr->phrase_list_pod_point();
        $grp = $lst->get_grp_id(false);
        $grp->name = groups::TN_GEO_VALUE;
        $grp->description = groups::TD_GEO_VALUE;
        return $grp;
    }

    /**
     * @return group with three prime phrases
     */
    function group_prime_3(): group
    {
        $t_phr = new test_phrases($this->env);
        $lst = $t_phr->phrase_list_zh_2019();
        $grp = $lst->get_grp_id(false);
        $grp->name = groups::TN_ZH_2019;
        return $grp;
    }

    /**
     * @return group with the max number of prime phrases
     */
    function group_prime_max(): group
    {
        $t_phr = new test_phrases($this->env);
        $lst = $t_phr->phrase_list_zh_mio();
        $grp = $lst->get_grp_id(false);
        $grp->name = groups::TN_ZH_2019_IN_MIO;
        return $grp;
    }

    /**
     * @return group with the max number of main phrases
     */
    function group_main_max(): group
    {
        $t_phr = new test_phrases($this->env);
        $lst = $t_phr->phrase_list_increase();
        $grp = $lst->get_grp_id(false);
        $grp->name = groups::TN_CH_INCREASE_2020;
        return $grp;
    }

    function group_16(): group
    {
        $t_phr = new test_phrases($this->env);
        $lst = $t_phr->phrase_list_16();
        $grp = $lst->get_grp_id(false);
        $grp->name = groups::TN_READ;
        return $grp;
    }

    function group_17_plus(): group
    {
        $t_phr = new test_phrases($this->env);
        $lst = $t_phr->phrase_list_17_plus();
        $grp = $lst->get_grp_id(false);
        $grp->name = groups::TN_READ;
        return $grp;
    }

    /**
     * @return group with only the word constant
     */
    function group_const(): group
    {
        $t_phr = new test_phrases($this->env);
        $lst = $t_phr->phrase_list_const();
        $grp = $lst->get_grp_id(false);
        $grp->name = groups::TN_READ;
        return $grp;
    }

    /**
     * @return group with one prime phrases
     */
    function group_zh(): group
    {
        $t_phr = new test_phrases($this->env);
        $lst = $t_phr->phrase_list_zh_city();
        $grp = $lst->get_grp_id(false);
        $grp->name = groups::ZH_CITY_INHABITANTS;
        $grp->description = groups::ZH_CITY_INHABITANTS_COM;
        return $grp;
    }

    function group_zh_2019(): group
    {
        $t_phr = new test_phrases($this->env);
        $lst = $t_phr->phrase_list_zh_2019();
        $grp = $lst->get_grp_id(false);
        $grp->name = groups::TN_ZH_2019;
        return $grp;
    }

    function group_zh_2020(): group
    {
        $t_phr = new test_phrases($this->env);
        $lst = $t_phr->phrase_list_zh_city_2020();
        return $lst->get_grp_id(false);
    }

    function group_canton(): group
    {
        $t_phr = new test_phrases($this->env);
        $lst = $t_phr->phrase_list_canton_mio();
        return $lst->get_grp_id(false);
    }

    function group_ch(): group
    {
        $t_phr = new test_phrases($this->env);
        $lst = $t_phr->ch_inhabitants_in_mio_2019();
        return $lst->get_grp_id(false);
    }

    function group_list(): group_list
    {
        $lst = new group_list($this->env->usr1);
        $lst->add($this->group());
        return $lst;
    }

    function group_list_long(): group_list
    {
        $lst = new group_list($this->env->usr1);
        $lst->add($this->group());
        $lst->add($this->group_zh_2019());
        $lst->add($this->group_prime_3());
        $lst->add($this->group_prime_max());
        $lst->add($this->group_main_max());
        $lst->add($this->group_16());
        $lst->add($this->group_17_plus());
        return $lst;
    }

}