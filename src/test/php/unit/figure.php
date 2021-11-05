<?php

/*

  test/unit/figure.php - unit testing of the figure functions
  --------------------
  

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

function run_figure_unit_tests(testing $t)
{

    $t->header('Unit tests of the formula class (src/main/php/model/formula/figure.php)');

    // if the user has changed the formula, that related figure is not standard anymore
    $frm = new formula();
    $frm->usr_cfg_id = 1;
    $fig = new figure();
    $fig->obj = $frm;
    $result = $fig->is_std();
    $target = false;
    $t->dsp('figure->is_std if formula is changed by the user', $target, $result);

}

