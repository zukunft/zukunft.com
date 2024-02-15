<?php

/*

    test/php/unit_read/component.php - database unit testing of the component functions
    --------------------------------


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

namespace test;

use api\component\component as component_api;
use api\view\view as view_api;
use cfg\component\component;
use cfg\component_link;
use cfg\view;

class component_unit_db_tests
{

    function run(test_cleanup $t): void
    {

        // init
        $t->header('Unit database tests of the component class (src/main/php/model/component/component.php)');
        $t->name = 'component read db->';


        $t->subheader('Component db read tests');

        $test_name = 'load component ' . component_api::TN_READ . ' by name and id';
        $cmp = new component($t->usr1);
        $cmp->load_by_name(component_api::TN_READ);
        $dsp_by_id = new component($t->usr1);
        $dsp_by_id->load_by_id($cmp->id(), component::class);
        $t->assert($test_name, $dsp_by_id->name(), component_api::TN_READ);


        $t->subheader('Component link db read tests');

        $test_name = 'load component link ' . view_api::TN_READ . ' to ' . component_api::TN_READ . ' by id';
        $dsp = new view($t->usr1);
        $dsp->load_by_name(view_api::TN_READ, view::class);
        $cmp = new component($t->usr1);
        $cmp->load_by_name(component_api::TN_READ);
        $cmp_lnk = new component_link($t->usr1);
        $cmp_lnk->load_by_link_and_type(1, 2, 1);
        $test_name .= ' view id';
        $t->assert($test_name, $cmp_lnk->fob->id(), $dsp->id());
        $test_name .= ' component id';
        $t->assert($test_name, $cmp_lnk->tob->id(), $cmp->id());

    }

}

