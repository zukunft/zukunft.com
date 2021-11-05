<?php

/*

  test/unit_db/view.php - database unit testing of the view functions
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

function run_view_unit_db_tests(testing $t)
{

    global $db_con;
    global $usr;

    $t->header('Unit database tests of the view class (src/main/php/model/value/view.php)');

    $t->subheader('System view tests');

    // load the views used by the system e.g. change word
    $lst = new view_list();
    $lst->usr = $usr;
    $result = $lst->load($db_con);
    $target = true;
    $t->dsp('unit_db_view->load', $target, $result);

    // ... and check if at least the most critical is loaded
    $result = cl(db_cl::VIEW, view::WORD);
    $target = 1;
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

