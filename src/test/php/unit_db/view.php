<?php

/*

  test/unit_db/view.php - database unit testing of the view functions
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

function run_view_unit_db_tests(testing $t)
{

    global $db_con;
    global $usr;

    $t->header('Unit database tests of the view class (src/main/php/model/value/view.php)');

    $t->subheader('System view tests');

    // load the views used by the system e.g. change word
    $lst = new view_list($usr);
    $lst->usr = $usr;
    $result = $lst->load($db_con);
    $target = true;
    $t->dsp('unit_db_view->load', $target, $result);

    // ... and check if at least the most critical is loaded
    $result = cl(db_cl::VIEW, view::WORD);
    if ($result > 0) {
        $target = $result; // just check if the id is found
    }
    $t->dsp('unit_db_view->check' . view::WORD, $result, $target);

    $t->subheader('View types tests');

    // load the view types
    $lst = new view_type_list();
    $result = $lst->load($db_con);
    $target = true;
    $t->dsp('unit_db_view->load_types', $target, $result);

    // ... and check if at least the most critical is loaded
    $result = cl(db_cl::VIEW_TYPE, view_type::DEFAULT);
    $target = 1;
    $t->dsp('unit_db_view->check type' . view_type::DEFAULT, $result, $target);

    $t->subheader('View component types tests');

    // load the view component types
    $cmp_lst = new view_cmp_type_list();
    $result = $cmp_lst->load($db_con);
    $target = true;
    $t->dsp('unit_db_view_component->load_types', $target, $result);

    // ... and check if at least the most critical is loaded
    $result = cl(db_cl::VIEW_COMPONENT_TYPE, view_cmp_type::TEXT);
    $target = 3;
    $t->dsp('unit_db_view_component->check component type' . view_cmp_type::TEXT, $result, $target);

}

