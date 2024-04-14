<?php

/*

    test/php/unit_ui/local_ui_tests.php - test if some key page are working on localhost
    -----------------------------------
  

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

namespace unit_ui;

use html\view\view as view_dsp;
use html\word\word as word_dsp;
use test\test_cleanup;
use const test\TEST_RES_UI_PATH;

class local_ui_tests
{

    function run(test_cleanup $t): void
    {

        $t->header('test local ui');

        $t->subheader('check about page e.g. to check the library');

        $test_name = 'check about page e.g. to check the library';
        $result = file_get_contents('http://localhost/http/about.php');
        $target = 'zukunft.com AG';
        $t->assert_text_contains($test_name, $result, $target);

        $api_json = file_get_contents(TEST_RES_UI_PATH . 'word_add.json');
        $msk_dsp = new view_dsp($api_json);
        $wrd = new word_dsp();
        $result = $msk_dsp->show($wrd, '');
        $target = 'word';
        $t->assert_text_contains($test_name, $result, $target);

    }
}