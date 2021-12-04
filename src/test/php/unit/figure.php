<?php

/*

  test/unit/figure.php - unit testing of the figure functions
  --------------------
  

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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2021 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

class figure_unit_tests
{
    function run(testing $t)
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

}