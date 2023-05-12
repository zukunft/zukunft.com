<?php

/*

    test/unit/html/batch_job.php - testing of the batch job display functions
    -----------------------------
  

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

namespace test\html;

include_once WEB_SYSTEM_PATH . 'batch_job_list.php';

use html\html_base;
use html\system\batch_job_list as batch_job_list_dsp;
use test\test_cleanup;

class batch_job
{
    function run(test_cleanup $t): void
    {
        $html = new html_base();

        $t->subheader('batch job display unit tests');

        // test the batch job html display functions
        $test_page = $html->text_h2('batch job display test');
        $log_lst = new batch_job_list_dsp($t->dummy_job_list()->api_json());
        $test_page .= 'user view of a table with batch job entries<br>';
        $test_page .= $log_lst->display() . '<br>';

        $t->html_test($test_page, 'batch_job', $t);
    }

}