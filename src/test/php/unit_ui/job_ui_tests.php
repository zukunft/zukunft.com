<?php

/*

    test/unit/html/job.php - testing of the batch task display functions
    ----------------------
  

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

use html\const\paths as html_paths;

include_once html_paths::SYSTEM . 'job_list.php';

use html\html_base;
use html\system\job_list as job_list_dsp;
use test\test_cleanup;

class job_ui_tests
{
    function run(test_cleanup $t): void
    {
        $html = new html_base();

        // start the test section (ts)
        $ts = 'unit ui html batch job ';
        $t->header($ts);

        // test the batch job html display functions
        $test_page = $html->text_h2('batch job display test');
        $log_lst = new job_list_dsp($t->job_list()->api_json());
        $test_page .= 'user view of a table with batch job entries<br>';
        $test_page .= $log_lst->display() . '<br>';

        $t->html_test($test_page, 'job', 'job', $t);
    }

}