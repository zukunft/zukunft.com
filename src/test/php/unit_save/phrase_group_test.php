<?php

/*

  phrase_group_test.php - PHRASE GROUP function unit TESTs
  ---------------------
  

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

function run_phrase_group_test(testing $t)
{

    global $usr;

    $t->header('Test the phrase group class (src/main/php/model/phrase/phrase_group.php)');

    // test getting the phrase group id based on word names
    $wrd_lst = new word_list($usr);
    $wrd_lst->load_by_names(array(word::TN_ZH, word::TN_CANTON, word::TN_INHABITANT, word::TN_MIO));
    $phr_grp = new phrase_group($usr);
    $phr_grp->load_by_lst($wrd_lst->phrase_lst());
    $result = $phr_grp->id();
    $target = 0;
    if ($result > 0) {
        $target = $result;
        $id_without_year = $result;
    }
    $t->dsp('phrase_group->load by ids for ' . implode(",", $wrd_lst->names()), $target, $result);

    // ... and if the time word is correctly excluded
    $wrd_lst = new word_list($usr);
    $wrd_lst->load_by_names(array(word::TN_ZH, word::TN_CANTON, word::TN_INHABITANT, word::TN_MIO, word::TN_2020));
    $phr_grp = new phrase_group($usr);
    $phr_grp->load_by_lst($wrd_lst->phrase_lst());
    $result = $phr_grp->id();
    //if ($result > 0 and $result != $id_without_year) {
    // actually the group id with time word is supposed to be the same as the phrase group id without time word because the time word is not included in the phrase group
    if ($result > 0) {
        $target = $result;
    }
    $t->dsp('phrase_group->load by ids excluding time for ' . implode(",", $wrd_lst->names()), $target, $result);

    // load based on id
    if ($phr_grp->id() > 0) {
        $phr_grp_reload = new phrase_group($usr);
        $phr_grp_reload->set_id($phr_grp->id());
        $phr_grp_reload->load();
        $wrd_lst_reloaded = $phr_grp_reload->phr_lst->wrd_lst();
        $result = array_diff(
            array(word::TN_MIO, word::TN_ZH, word::TN_CANTON, word::TN_INHABITANT, word::TN_CH),
            $wrd_lst_reloaded->names()
        );
    }
    $target = array(word::TN_CH) ;
    $t->dsp('phrase_group->load for id ' . $phr_grp->id(), $target, $result);

    // test getting the phrase group id based on word and word link ids
    $phr_lst = new phrase_list($usr);
    $phr_lst->load_by_names(array(phrase::TN_ZH_CITY, word::TN_INHABITANT));
    $zh_city_grp = $phr_lst->get_grp();
    $result = $zh_city_grp->get_id();
    if ($result > 0) {
        $target = $result;
    }
    $t->dsp('phrase_group->load by ids for ' . $phr_lst->dsp_id(), $target, $result, TIMEOUT_LIMIT_PAGE);

    // test names
    $result = implode(",", $zh_city_grp->names());
    $target = phrase::TN_ZH_CITY . ',' . word::TN_INHABITANT;
    $t->dsp('phrase_group->names', $target, $result);

    // test if the phrase group links are correctly recreated when a group is updated
    $phr_lst = new phrase_list($usr);
    $phr_lst->load_by_names(array(word::TN_ZH, word::TN_CANTON, word::TN_INHABITANT));
    $grp = $phr_lst->get_grp();
    $grp_check = new phrase_group($usr);
    $grp_check->set_id($grp->id());
    $grp_check->load();
    $result = $grp_check->load_link_ids_for_testing();
    $target = $grp->phr_lst->id_lst();
    $t->dsp('phrase_group->load_link_ids for ' . $phr_lst->dsp_id(), $target, $result, TIMEOUT_LIMIT_PAGE);

    // second test if the phrase group links are correctly recreated when a group is updated
    $phr_lst = new phrase_list($usr);
    $phr_lst->load_by_names(array(word::TN_ZH, word::TN_CANTON, word::TN_INHABITANT, word::TN_MIO, word::TN_2020));
    $grp = $phr_lst->get_grp();
    $grp_check = new phrase_group($usr);
    $grp_check->set_id($grp->id());
    $grp_check->load();
    $result = $grp_check->load_link_ids_for_testing();
    $target = $grp->phr_lst->id_lst();
    $t->dsp('phrase_group->load_link_ids for ' . $phr_lst->dsp_id(), $target, $result, TIMEOUT_LIMIT_PAGE);

    // test value
    // test value_scaled


    // load based on wrd and lnk lst
    // load based on wrd and lnk ids
    // maybe if cleanup removes the unneeded group

    // test the user sandbox for the user names
    // test if the search links are correctly created

}