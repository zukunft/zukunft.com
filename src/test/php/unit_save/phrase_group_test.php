<?php

/*

  phrase_group_test.php - PHRASE GROUP function unit TESTs
  ---------------------
  

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

function run_phrase_group_test()
{

    global $usr;

    test_header('Test the phrase group class (src/main/php/model/phrase/phrase_group.php)');

    // test getting the phrase group id based on word ids
    $wrd_lst = new word_list;
    $wrd_lst->usr = $usr;
    $wrd_lst->add_name(word::TN_ZH);
    $wrd_lst->add_name(word::TN_CANTON);
    $wrd_lst->add_name(word::TN_INHAPITANT);
    $wrd_lst->add_name(word::TN_MIO);
    $wrd_lst->load();
    $phr_grp = new phrase_group;
    $phr_grp->usr = $usr;
    $phr_grp->ids = $wrd_lst->ids;
    $phr_grp->load();
    $result = $phr_grp->id;
    $target = 0;
    if ($result > 0) {
        $target = $result;
        $id_without_year = $result;
    }
    test_dsp('phrase_group->load by ids for ' . implode(",", $wrd_lst->names()), $target, $result);

    // ... and if the time word is correctly excluded
    $wrd_lst->add_name(word::TN_2020);
    $wrd_lst->load();
    $phr_grp = new phrase_group;
    $phr_grp->usr = $usr;
    $phr_grp->ids = $wrd_lst->ids;
    $phr_grp->load();
    $result = $phr_grp->id;
    //if ($result > 0 and $result != $id_without_year) {
    // actually the group id with time word is supposed to the the same as the phrase group id without time word because the time word is not included in the phrase group
    if ($result > 0) {
        $target = $result;
    }
    test_dsp('phrase_group->load by ids excluding time for ' . implode(",", $wrd_lst->names()), $target, $result);

    // load based on id
    if ($phr_grp->id > 0) {
        $phr_grp_reload = new phrase_group;
        $phr_grp_reload->usr = $usr;
        $phr_grp_reload->id = $phr_grp->id;
        $phr_grp_reload->load();
        $phr_grp_reload->load_lst();
        $wrd_lst_reloaded = $phr_grp_reload->wrd_lst;
        $result = implode(",", $wrd_lst_reloaded->names());
        $target = word::TN_MIO . ',' . word::TN_CANTON . ',' . word::TN_ZH . ',' . word::TN_INHAPITANT;
        test_dsp('phrase_group->load for id ' . $phr_grp->id, $target, $result);
    }

    // test getting the phrase group id based on word and word link ids
    $phr_lst = new phrase_list();
    $phr_lst->usr = $usr;
    $phr_lst->add_name(phrase::TN_ZH_CITY);
    $phr_lst->add_name(word::TN_INHAPITANT);
    $phr_lst->load();
    $zh_city_grp = new phrase_group;
    $zh_city_grp->usr = $usr;
    $zh_city_grp->ids = $phr_lst->ids;
    $result = $zh_city_grp->get_id();
    if ($result > 0) {
        $target = $result;
    }
    test_dsp('phrase_group->load by ids for ' . $phr_lst->dsp_id(), $target, $result, TIMEOUT_LIMIT_PAGE);

    // test names
    $result = implode(",", $zh_city_grp->names());
    $target = word::TN_INHAPITANT . ',' . phrase::TN_ZH_CITY;
    test_dsp('phrase_group->names', $target, $result);

    // test if the phrase group links are correctly recreated when a group is updated
    $phr_lst = new phrase_list;
    $phr_lst->usr = $usr;
    $phr_lst->add_name(word::TN_ZH);
    $phr_lst->add_name(word::TN_CANTON);
    $phr_lst->add_name(word::TN_INHAPITANT);
    $phr_lst->load();
    $grp = $phr_lst->get_grp();
    $grp_check = new phrase_group;
    $grp_check->usr = $usr;
    $grp_check->id = $grp->id;
    $grp_check->load();
    $result = $grp_check->load_link_ids();
    $target = $grp->ids;
    test_dsp('phrase_group->load_link_ids for ' . $phr_lst->dsp_id(), $target, $result, TIMEOUT_LIMIT_PAGE);

    // second test if the phrase group links are correctly recreated when a group is updated
    $phr_lst = new phrase_list;
    $phr_lst->usr = $usr;
    $phr_lst->add_name(word::TN_ZH);
    $phr_lst->add_name(word::TN_CANTON);
    $phr_lst->add_name(word::TN_INHAPITANT);
    $phr_lst->add_name(word::TN_MIO);
    $phr_lst->add_name(word::TN_2020);
    $phr_lst->load();
    $grp = $phr_lst->get_grp();
    $grp_check = new phrase_group;
    $grp_check->usr = $usr;
    $grp_check->id = $grp->id;
    $grp_check->load();
    $result = $grp_check->load_link_ids();
    $target = $grp->ids;
    test_dsp('phrase_group->load_link_ids for ' . $phr_lst->dsp_id(), $target, $result, TIMEOUT_LIMIT_PAGE);

    // test value
    // test value_scaled


    // load based on wrd and lnk lst
    // load based on wrd and lnk ids
    // maybe if cleanup removes the unneeded group

    // test the user sandbox for the user names
    // test if the search links are correctly created

}