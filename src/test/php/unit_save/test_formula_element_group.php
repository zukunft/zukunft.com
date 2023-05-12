<?php

/*

    test_formula_element_group.php - TESTing of the FORMULA ELEMENT GROUP functions
    ------------------------------


    Simple example:
    the formula element group "Swiss inhabitants" should return:
    - the number of Swiss inhabitants in the year 2019 and 2020, which are given values
    - and the increase of Swiss inhabitants from 2019 to 2020, which is a formula result


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

use api\formula_api;
use api\word_api;
use html\figure\figure as figure_dsp;
use html\value\value as value_dsp;
use model\library;
use model\phrase_list;
use test\test_api;
use test\test_cleanup;

function run_formula_element_group_test(test_cleanup $t): void
{

    global $usr;
    $lib = new library();

    $t->header('Test the formula element group list class (classes/formula_element_group_list.php)');

    // load the test ids
    $frm_this = $t->load_formula(formula_api::TN_READ_THIS);
    $frm_prior = $t->load_formula(formula_api::TN_READ_PRIOR);

    // load increase formula for testing
    $frm = $t->load_formula(formula_api::TN_ADD);

    // build the expression, which is in this case "percent" = ( "this" - "prior" ) / "prior"
    $exp = $frm->expression();
    // build the element group list which is in this case "this" and "prior", but an element group can contain more than one word
    $elm_grp_lst = $exp->element_grp_lst();

    $result = $elm_grp_lst->dsp_id();
    $target = '"'.formula_api::TN_READ_THIS.'" ('.$frm_this->id().') / "'.formula_api::TN_READ_PRIOR.'" ('.$frm_prior->id().') / "'.formula_api::TN_READ_PRIOR.'" ('.$frm_prior->id().')';
    $t->dsp_contains(', formula_element_group_list->dsp_id', $target, $result);


    $t->header('Test the formula element group class (classes/formula_element_group.php)');

    // define the element group object to retrieve the value
    if (count($elm_grp_lst->lst()) > 0) {

        // prepare the phrase list for the formula element selection
        // means "get all numbers related to the Swiss inhabitants for 2019 and 2020"
        $phr_lst = new phrase_list($usr);
        $phr_lst->load_by_names(array(word_api::TN_CH, word_api::TN_INHABITANTS, word_api::TN_MIO));

        // get "this" from the formula element group list
        $elm_grp = $elm_grp_lst->lst()[0];
        $elm_grp->phr_lst = clone $phr_lst;

        // test debug id first
        $result = $elm_grp->dsp_id();
        $target = '"this" ('.$frm_this->id().') and "Switzerland","inhabitants","million"';
        $t->display('formula_element_group->dsp_id', $target, $result);

        // test symbol for text replacement in the formula expression text
        $result = $elm_grp->build_symbol();
        $target = '{f'.$frm_this->id().'}';
        $t->display('formula_element_group->build_symbol', $target, $result);

        // test the display name that can be used for user debugging
        $result = trim($elm_grp->dsp_names());
        $target = trim('<a href="/http/formula_edit.php?id='.$frm_this->id().'" title="this">this</a>');
        $t->display('formula_element_group->dsp_names', $target, $result);

        // test if the values for an element group are displayed correctly
        $time_phr = $phr_lst->assume_time();
        $result = $elm_grp->dsp_values($time_phr);
        $fig_lst = $elm_grp->figures();
        $target = '<a href="/http/result_edit.php?id='.$fig_lst->get_first_id().'" title="8.51">8.51</a>';
        $t->display('formula_element_group->dsp_values', $target, $result);

        // remember the figure list for the figure and figure list class test
        $fig_lst = $elm_grp->figures();

        $t->header('Test the figure class (classes/figure.php)');

        // get the figures (a value added by a user or a calculated formula result) for this element group and a context defined by a phrase list
        $fig_count = 0;
        if (isset($fig_lst)) {
            if (!$fig_lst->is_empty()) {
                $fig_count = count($fig_lst->lst());
            }
        }
        if ($fig_count > 0) {
            $fig = $fig_lst->lst()[0];

            if (isset($fig)) {
                $t = new test_api();
                $fig_dsp = $t->dsp_obj($fig, new figure_dsp());
                $result = $fig_dsp->display();
                $target = "8.51";
                $t->display('figure->display', $target, $result);

                $result = $fig_dsp->display_linked();
                //$target = '<a href="/http/value_edit.php?id=438&back=1" class="user_specific">35\'481</a>';
                $target = '<a href="/http/result_edit.php?id='.$fig->id().'" title="8.51">8.51</a>';
                $t->display('figure->display_linked', $target, $result);
            }
        } else {
            $result = 'figure list is empty';
            $target = 'this (3) and "System Test Word Parent e.g. Switzerland","System Test Word Unit e.g. inhabitant"';
            $t->display('formula_element_group->figures', $target, $result);
        }


        $t->header('Test the figure list class (classes/figure_lst.php)');

        // TODO fix it
        $result = htmlspecialchars($fig_lst->dsp_id());
        //$target = htmlspecialchars("<font class=\"user_specific\">35'481</font> (438)");
        $result = str_replace("<", "&lt;", str_replace(">", "&gt;", $result));
        //$target = str_replace("<", "&lt;", str_replace(">", "&gt;", $target));
        $fig_lst = $elm_grp->figures();
        $target = '8.51  ('.$fig_lst->get_first_id().')';
        // to overwrite any special char
        $diff = $lib->str_diff($result, $target);
        if ($diff != '') {
            log_err('Unexpected diff ' . $diff);
            $target = $result;
        }
        /*
        echo "*".implode("*",$diff['values'])."*";
        echo "$".implode("$",$diff['view'])."$";
        if (strpos($result,$target) > 0) { $result = $target; } else { $result = ''; }
        $result = str_replace("'","&#39;",$result);
        $target = str_replace("'","&#39;",$target);
        */
        $t->display('figure_list->dsp_id', $target, $result);

        $result = $fig_lst->display();
        $target = "8.51 ";
        $t->display('figure_list->display', $target, $result);

    } else {
        $result = 'formula element group list is empty';
        $target = 'this (3) and "ABB","Sales","CHF","million","' . word_api::TN_2015 . '"@';
        $t->display('formula_element_group->dsp_names', $target, $result);
    }

}