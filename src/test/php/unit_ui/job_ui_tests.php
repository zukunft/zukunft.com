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

namespace Zukunft\ZukunftCom\test\php\unit_ui;

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::EXECUTE . 'system_page.php';
include_once html_paths::SYSTEM . 'job_list.php';

use Zukunft\ZukunftCom\main\php\web\component\execute\system_page;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\system\job_list;
use Zukunft\ZukunftCom\test\php\create\test_jobs;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class job_ui_tests
{
    function run(test_cleanup $t): void
    {
        $html = new html_base();
        $t_job = new test_jobs($t);

        // start the test section (ts)
        $ts = 'unit ui html batch job ';
        $t->header($ts);

        // test the batch job html display functions
        $test_page = $html->text_h2('batch job display test');
        $job_lst = new job_list($t_job->job_list()->api_json());
        $test_page .= 'user view of a table with batch job entries<br>';
        $test_page .= $job_lst->display() . '<br>';

        // test the SYSTEM_ADMIN_JOBS_DELAYED component-type renderer (system_page::admin_jobs_delayed)
        $page = new system_page();
        $job_lst = new job_list($t_job->job_list_delayed()->api_json());
        $test_page .= $html->text_h2('admin_jobs_delayed test');
        $test_page .= 'empty job list shows the no-open-jobs notice<br>';
        $test_page .= $page->admin_jobs_delayed() . '<br>';
        $test_page .= 'job list with one open batch job sorted by request_time ascending<br>';
        $test_page .= $page->admin_jobs_delayed($job_lst) . '<br>';

        $t->html_page_test($test_page, 'job', 'job', $t);
    }

}