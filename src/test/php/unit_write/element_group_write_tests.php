<?php

/*

    test/php/unit_write/element_group_tests.php - write test FORMULA ELEMENT GROUPS to the database and check the results
    ---------------------------------------------------


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

namespace unit_write;

use cfg\phrase\phrase_list;
use html\element\element_group;
use html\figure\figure as figure_dsp;
use html\figure\figure_list;
use shared\library;
use shared\const\formulas;
use shared\const\words;
use test\test_api;
use test\test_cleanup;

class element_group_write_tests
{

    function run(test_cleanup $t): void
    {

        global $usr;
        $lib = new library();

        $t->header('Test the formula element group list class (classes/element_group_list.php)');

        // load the test ids
        $frm_this = $t->load_formula(formulas::THIS_NAME);
        $frm_prior = $t->load_formula(formulas::PRIOR);

        // load increase formula for testing
        $frm = $t->load_formula(formulas::INCREASE);

        // build the expression, which is in this case "percent" = ( "this" - "prior" ) / "prior"
        $exp = $frm->expression();
        // build the element group list which is in this case "this" and "prior", but an element group can contain more than one word
        $elm_grp_lst = $exp->element_grp_lst();

        $result = $elm_grp_lst->dsp_id();
        $target = '"' . formulas::THIS_NAME . '" (' . $frm_this->id() . ') / "' . formulas::PRIOR . '" (' . $frm_prior->id() . ') / "' . formulas::PRIOR . '" (' . $frm_prior->id() . ')';
        $t->dsp_contains(', element_group_list->dsp_id', $target, $result);


        $t->header('Test the formula element group class (classes/element_group.php)');

        // define the element group object to retrieve the value
        if (count($elm_grp_lst->lst()) > 0) {

            // prepare the phrase list for the formula element selection
            // means "get all numbers related to the Swiss inhabitants for 2019 and 2020"
            $phr_lst = new phrase_list($usr);
            $phr_lst->load_by_names(array(words::CH, words::INHABITANTS, words::MIO));

            // get "this" from the formula element group list
            $elm_grp = $elm_grp_lst->lst()[0];
            $elm_grp->phr_lst = clone $phr_lst;

            // test debug id first
            $result = $elm_grp->dsp_id();
            $target = '"'
                . words::THIS_NAME . '" (' . $frm_this->id() . ') and "'
                . words::INHABITANTS . '","'
                . words::MIO . '","'
                . words::CH . '"';
            $t->display('element_group->dsp_id', $target, $result);

            // test symbol for text replacement in the formula expression text
            $result = $elm_grp->build_symbol();
            $target = '{f' . $frm_this->id() . '}';
            $t->display('element_group->build_symbol', $target, $result);

            // test if the values for an element group are displayed correctly
            $elm_grp_dsp = new element_group($elm_grp->api_json());
            // TODO Prio 1 activate
            //$result = $elm_grp_dsp->dsp_values();
            //$fig_lst = $elm_grp->figures();
            //$target = '<a href="/http/result_edit.php?id=' . $fig_lst->get_first_id() . '" title="8.51">8.51</a>';
            //$t->display('element_group->dsp_values', $target, $result);

            // remember the figure list for the figure and figure list class test
            $fig_lst = $elm_grp->figures();

            $t->header('figure database write tests');

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
                    //$target = '<a href="/http/value_edit.php?id=438&back=1" class="' . styles::STYLE_USER . '">35\'481</a>';
                    $target = '<a href="/http/result_edit.php?id=' . $fig->id() . '" title="8.51">8.51</a>';
                    $t->display('figure->display_linked', $target, $result);
                }
            } else {
                $result = 'figure list is empty';
                $target = 'this (3) and "System Test Word Parent e.g. '  . words::CH . '","System Test Word Unit e.g. inhabitant"';
                $t->display('element_group->figures', $target, $result);
            }


            $t->header('Test the figure list class (classes/figure_lst.php)');

            // TODO fix it
            $fig_lst->load_phrases();
            $result = htmlspecialchars($fig_lst->dsp_id());
            //$target = htmlspecialchars("<style class=\"' . styles::STYLE_USER . '\">35'481</style> (438)");
            $result = str_replace("<", "&lt;", str_replace(">", "&gt;", $result));
            //$target = str_replace("<", "&lt;", str_replace(">", "&gt;", $target));
            $fig_lst = $elm_grp->figures();
            $fig_id = $fig_lst->get_first_id();
            $target = ' 8.505251 {f18}'  . words::INHABITANTS . ','  . words::MIO . ','  . words::CH . '  (' . $fig_id . ')';
            $t->assert('figure_list->dsp_id', $result, $target);

            $fig_lst_dsp = new figure_list($fig_lst->api_json());
            $result = $fig_lst_dsp->display();
            $target = "8.51 ";
            $t->display('figure_list->display', $target, $result);

        } else {
            $result = 'formula element group list is empty';
            $target = 'this (3) and "'
                . words::ABB . '","'
                . words::SALES . '","'
                . words::CHF . '","'
                . words::MIO . '","'
                . words::YEAR_2015 . '"@';
            $t->display('element_group->dsp_names', $target, $result);
        }

    }

}