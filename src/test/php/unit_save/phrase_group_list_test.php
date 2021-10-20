<?php

/*

  phrase_group_list_test.php - PHRASE GROUP LIST function unit TESTs
  --------------------------
  

zukunft.com - calc with words

copyright 1995-2021 by zukunft.com AG, Blumentalstrasse 15, 8707 Uetikon am See, Switzerland

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

function run_phrase_group_list_test()
{

    global $usr;

    test_header('Test the phrase group list class (src/main/php/model/phrase/phrase_group_list.php)');

    // define some phrase groups for testing

    // Switzerland inhabitants
    $phr_lst = new phrase_list;
    $phr_lst->usr = $usr;
    $phr_lst->add_name(word::TN_CH);
    $phr_lst->add_name(word::TN_INHABITANT);
    $phr_lst->add_name(word::TN_MIO);
    $phr_lst->load();
    $country_grp = $phr_lst->get_grp();

    // Canton of Zurich inhabitants
    $phr_lst = new phrase_list;
    $phr_lst->usr = $usr;
    $phr_lst->add_name(word::TN_ZH);
    $phr_lst->add_name(word::TN_CANTON);
    $phr_lst->add_name(word::TN_INHABITANT);
    $phr_lst->add_name(word::TN_MIO);
    $phr_lst->load();
    $canton_grp = $phr_lst->get_grp();

    // City of Zurich inhabitants
    $phr_lst = new phrase_list;
    $phr_lst->usr = $usr;
    $phr_lst->add_name(word::TN_ZH);
    $phr_lst->add_name(word::TN_CITY);
    $phr_lst->add_name(word::TN_INHABITANT);
    $phr_lst->add_name(word::TN_MIO);
    $phr_lst->load();
    $city_grp = $phr_lst->get_grp();

    // test add a phrase group to a phrase group list
    $grp_lst = new phrase_group_list;
    $grp_lst->usr = $usr;
    $grp_lst->add($country_grp);
    $grp_lst->add($canton_grp);
    $grp_lst->add($city_grp);
    $result = $grp_lst->name();
    $target = '' . word::TN_MIO . ',' . word::TN_CH . ',' . word::TN_INHABITANT .
        ' and ' . word::TN_MIO . ',' . word::TN_CANTON . ',' . word::TN_ZH . ',' . word::TN_INHABITANT .
        ' and ' . word::TN_MIO . ',' . word::TN_CITY . ',' . word::TN_ZH . ',' . word::TN_INHABITANT;
    test_dsp('phrase_group_list->add of ' . $country_grp->dsp_id() . ', ' . $country_grp->dsp_id() . ', ' . $city_grp->dsp_id(), $target, $result, TIMEOUT_LIMIT_PAGE);


    // test getting the common phrases of several group
    $grp_lst = new phrase_group_list;
    $grp_lst->usr = $usr;

    $wrd_lst = new word_list;
    $wrd_lst->usr = $usr;
    $wrd_lst->add_name(word::TN_CH);
    $wrd_lst->add_name(word::TN_INHABITANT);
    $wrd_lst->add_name(word::TN_MIO);
    $wrd_lst->load();
    $country_grp = $wrd_lst->get_grp();
    $grp_lst->add($country_grp);

    $wrd_lst = new word_list;
    $wrd_lst->usr = $usr;
    $wrd_lst->add_name(word::TN_ZH);
    $wrd_lst->add_name(word::TN_CANTON);
    $wrd_lst->add_name(word::TN_INHABITANT);
    $wrd_lst->add_name(word::TN_MIO);
    $wrd_lst->load();
    $canton_grp = $wrd_lst->get_grp();
    $grp_lst->add($canton_grp);

    $phr_lst = $grp_lst->common_phrases();
    $result = $phr_lst->name();
    $target = '"' . word::TN_MIO . '","' . word::TN_INHABITANT . '"';
    test_dsp('phrase_group_list->common_phrases of ' . $grp_lst->dsp_id(), $target, $result);

}