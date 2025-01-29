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

namespace unit_read;

use cfg\component\component;
use cfg\component\component_link;
use cfg\view\view;
use shared\const\components;
use shared\const\views;
use test\test_cleanup;

class component_read_tests
{

    function run(test_cleanup $t): void
    {

        // init
        $t->name = 'component read db->';


        $t->header('component db read tests');

        $t->subheader('component load');
        $msk = new view($t->usr1);
        $t->assert_load($msk, views::START_NAME);

        $t->subheader('Component link db read tests');
        $test_name = 'load component link ' . views::START_NAME . ' to ' . components::WORD_NAME . ' by id';
        $msk = new view($t->usr1);
        $msk->load_by_name(views::START_NAME);
        $cmp = new component($t->usr1);
        $cmp->load_by_name(components::WORD_NAME);
        $cmp_lnk = new component_link($t->usr1);
        $cmp_lnk->load_by_link_and_type(1, 1, 1);
        $test_name .= ' view id';
        $t->assert($test_name, $cmp_lnk->view()->id(), $msk->id());
        $test_name .= ' component id';
        $t->assert($test_name, $cmp_lnk->component()->id(), $cmp->id());

    }

}

