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

    // load the main test word
    $wrd_company = test_word(word::TN_READ);

    // test getting the group id based on ids
    $wrd_lst = new word_list;
    $wrd_lst->usr = $usr;
    $wrd_lst->add_name(TW_ABB);
    $wrd_lst->add_name(TW_SALES);
    $wrd_lst->add_name(TW_CHF);
    $wrd_lst->add_name(TW_MIO);
    $wrd_lst->load();
    $abb_grp = new phrase_group;
    $abb_grp->usr = $usr;
    $abb_grp->ids = $wrd_lst->ids;
    $abb_grp->load();
    $result = $abb_grp->id;
    $target = '2116';
    test_dsp('phrase_group->load by ids for ' . implode(",", $wrd_lst->names()), $target, $result);

    // ... and if the time word is correctly excluded
    $wrd_lst->add_name(TW_2014);
    $wrd_lst->load();
    $abb_grp = new phrase_group;
    $abb_grp->usr = $usr;
    $abb_grp->ids = $wrd_lst->ids;
    $abb_grp->load();
    $result = $abb_grp->id;
    $target = '2116';
    test_dsp('phrase_group->load by ids excluding time for ' . implode(",", $wrd_lst->names()), $target, $result);

    // load based on id
    if ($abb_grp->id > 0) {
        $abb_grp_reload = new phrase_group;
        $abb_grp_reload->usr = $usr;
        $abb_grp_reload->id = $abb_grp->id;
        $abb_grp_reload->load();
        $abb_grp_reload->load_lst();
        $wrd_lst_reloaded = $abb_grp_reload->wrd_lst;
        $result = implode(",", $wrd_lst_reloaded->names());
        $target = 'million,CHF,Sales,ABB';
        test_dsp('phrase_group->load for id ' . $abb_grp->id, $target, $result);
    }

    // if a new group is created in needed when a triple is added
    $wrd_zh = load_word(TW_ZH);
    $lnk_company = new word_link;
    $lnk_company->from_id = $wrd_zh->id;
    $lnk_company->verb_id = cl(db_cl::VERB, verb::DBL_IS);
    $lnk_company->to_id = $wrd_company->id;
    $lnk_company->usr = $usr;
    $lnk_company->load();
    $wrd_lst = new word_list;
    $wrd_lst->usr = $usr;
    $wrd_lst->add_name(TW_SALES);
    $wrd_lst->add_name(TW_CHF);
    $wrd_lst->add_name(TW_MIO);
    $wrd_lst->load();
    $zh_ins_grp = new phrase_group;
    $zh_ins_grp->usr = $usr;
    $zh_ins_grp->ids = $wrd_lst->ids;
    $zh_ins_grp->ids[] = $lnk_company->id * -1;
    $result = $zh_ins_grp->get_id();
    $target = '3490';
    test_dsp('phrase_group->load by ids for ' . $lnk_company->name . ' and ' . implode(",", $wrd_lst->names()), $target, $result, TIMEOUT_LIMIT_PAGE);

    // test names
    $result = implode(",", $zh_ins_grp->names());
    $target = 'million,CHF,Sales,Zurich Insurance';  // fix the issue after the libraries are excluded
    //$target = 'million,CHF,Sales,'.TP_ZH_INS.'';
    test_dsp('phrase_group->names', $target, $result);

    // test if the phrase group links are correctly recreated when a group is updated
    $phr_lst = new phrase_list;
    $phr_lst->usr = $usr;
    $phr_lst->add_name(TW_ABB);
    $phr_lst->add_name(TW_SALES);
    $phr_lst->add_name(TW_2016);
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
    $phr_lst->add_name(TW_ABB);
    $phr_lst->add_name(TW_SALES);
    $phr_lst->add_name(TW_CHF);
    $phr_lst->add_name(TW_MIO);
    $phr_lst->add_name(TW_2016);
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