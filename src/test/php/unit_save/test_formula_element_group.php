<?php

/*

  test_formula_element_group.php - TESTing of the FORMULA ELEMENT GROUP functions
  ------------------------------
  

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

function run_formula_element_group_test()
{

    global $usr;

    $back = 0;

    test_header('Test the formula element group list class (classes/formula_element_group_list.php)');

    // load increase formula for testing
    $frm = load_formula(formula::TN_INCREASE);

    $phr_lst = new phrase_list;
    $phr_lst->usr = $usr;
    $phr_lst->add_name(word::TN_CH);
    $phr_lst->add_name(word::TN_INHABITANT);
    $phr_lst->add_name(word::TN_MIO);
    $phr_lst->add_name(word::TN_2019);
    $phr_lst->load();

    $phr_lst_next = new phrase_list;
    $phr_lst_next->usr = $usr;
    $phr_lst_next->add_name(word::TN_CH);
    $phr_lst_next->add_name(word::TN_INHABITANT);
    $phr_lst_next->add_name(word::TN_MIO);
    $phr_lst_next->add_name(word::TN_2020);
    $phr_lst_next->load();

    // build the expression which is in this case "percent" = ( "this" - "prior" ) / "prior"
    $exp = $frm->expression();
    // build the element group list which is in this case "this" and "prior", but an element group can contain more than one word
    $elm_grp_lst = $exp->element_grp_lst($back);

    $result = $elm_grp_lst->dsp_id();
    $target = '"this" (18),"prior" (20) for user 2 (zukunft.com system test)';
    test_dsp_contains(', formula_element_group_list->dsp_id', $target, $result);


    test_header('Test the formula element group class (classes/formula_element_group.php)');

    // define the element group object to retrieve the value
    if (count($elm_grp_lst->lst) > 0) {
        $elm_grp = $elm_grp_lst->lst[0];
        $elm_grp->phr_lst = clone $phr_lst;

        // test debug id first
        $result = $elm_grp->dsp_id();
        $target = '"this" (18) and "System Test Word Parent e.g. Switzerland","System Test Word Unit e.g. inhabitant","System Test Scaling Word e.g. millions","System Test Another Time Word e.g. 2019"';
        test_dsp('formula_element_group->dsp_id', $target, $result);

        // test symbol for text replacement in the formula expression text
        $result = $elm_grp->build_symbol();
        $target = '{f18}';
        test_dsp('formula_element_group->build_symbol', $target, $result);

        // test the display name that can be used for user debugging
        $result = trim($elm_grp->dsp_names($back));
        $target = trim('<a href="/http/formula_edit.php?id=18&back=0">this</a>');
        test_dsp('formula_element_group->dsp_names', $target, $result);

        // test if the values for an element group are displayed correctly
        $time_phr = $phr_lst->assume_time();
        $result = $elm_grp->dsp_values($back, $time_phr);
        $target = '';
        test_dsp('formula_element_group->dsp_values', $target, $result);

        $time_phr = $phr_lst_next->assume_time();
        $result = $elm_grp->dsp_values($back, $time_phr);
        // TODO activate $target = '<a href="/http/value_edit.php?id=438&back=1" class="user_specific">35\'481</a> (2015)';
        $target = ' (System Test Another Time Word e.g. 2019)';
        test_dsp('formula_element_group->dsp_values', $target, $result);

        // remember the figure list for the figure and figure list class test
        $fig_lst = $elm_grp->figures();

        test_header('Test the figure class (classes/figure.php)');

        // get the figures (a value added by a user or a calculated formula result) for this element group and a context defined by a phrase list
        $fig_count = 0;
        if (isset($fig_lst)) {
            if (isset($fig_lst->lst)) {
                $fig_count = count($fig_lst->lst);
            }
        }
        if ($fig_count > 0) {
            $fig = $fig_lst->lst[0];

            if (isset($fig)) {
                $result = $fig->display($back);
                $target = "35'481";
                test_dsp('figure->display', $target, $result);

                $result = $fig->display_linked($back);
                $target = '<a href="/http/value_edit.php?id=438&back=1" class="user_specific">35\'481</a>';
                test_dsp('figure->display_linked', $target, $result);
            }
        } else {
            $result = 'figure list is empty';
            $target = 'this (3) and "ABB","Sales","CHF","million","2015"@';
            test_dsp('formula_element_group->figures', $target, $result);
        }


        test_header('Test the figure list class (classes/figure_lst.php)');

        $result = htmlspecialchars($fig_lst->dsp_id());
        $target = htmlspecialchars("<font class=\"user_specific\">35'481</font> (438)");
        $result = str_replace("<", "&lt;", str_replace(">", "&gt;", $result));
        $target = str_replace("<", "&lt;", str_replace(">", "&gt;", $target));
        // to overwrite any special char
        $diff = str_diff($result, $target);
        if ($diff != null) {
            if (in_array('view', $diff)) {
                if (in_array(0, $diff['view'])) {
                    if ($diff['view'][0] == 0) {
                        $target = $result;
                    }
                }
            }
        }
        /*
        echo "*".implode("*",$diff['values'])."*";
        echo "$".implode("$",$diff['view'])."$";
        if (strpos($result,$target) > 0) { $result = $target; } else { $result = ''; }
        $result = str_replace("'","&#39;",$result);
        $target = str_replace("'","&#39;",$target);
        */
        test_dsp('figure_list->dsp_id', $target, $result);

        $result = $fig_lst->display();
        $target = "35'481 ";
        test_dsp('figure_list->display', $target, $result);

    } else {
        $result = 'formula element group list is empty';
        $target = 'this (3) and "ABB","Sales","CHF","million","2015"@';
        test_dsp('formula_element_group->dsp_names', $target, $result);
    }

}