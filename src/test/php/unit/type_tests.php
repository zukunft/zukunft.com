<?php

/*

    test/unit/language.php - unit testing of the language functions
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

namespace unit;

use cfg\formula_type;
use cfg\job_type;
use cfg\phrase_type;
use cfg\protection_type;
use cfg\share_type;
use cfg\source_type;
use cfg\sys_log_function;
use cfg\sys_log_status;
use cfg\sys_log_type;
use cfg\user\user_profile;
use cfg\user\user_type;
use cfg\user_official_type;
use cfg\view_type;
use test\test_cleanup;

class type_tests
{

    function run(test_cleanup $t): void
    {

        // init
        $t->name = 'type->';
        $t->resource_path = 'db/type/';


        $t->header('Unit tests of the type classes');

        // TODO job_types

        $t->subheader('System log type sql setup');
        $log_typ = new sys_log_type('');
        $t->assert_sql_table_create($log_typ);
        $t->assert_sql_index_create($log_typ);

        $t->subheader('system log status sql setup');
        $log_sta = new sys_log_status('');
        $t->assert_sql_table_create($log_sta);
        $t->assert_sql_index_create($log_sta);

        $t->subheader('system log status sql setup');
        $log_fuc = new sys_log_function('');
        $t->assert_sql_table_create($log_fuc);
        $t->assert_sql_index_create($log_fuc);

        $t->subheader('job type sql setup');
        $job_typ = new job_type('');
        $t->assert_sql_table_create($job_typ);
        $t->assert_sql_index_create($job_typ);

        $t->subheader('user type sql setup');
        $usr_typ = new user_type('');
        $t->assert_sql_table_create($usr_typ);
        $t->assert_sql_index_create($usr_typ);

        $t->subheader('user profile sql setup');
        $usr_prf = new user_profile('');
        $t->assert_sql_table_create($usr_prf);
        $t->assert_sql_index_create($usr_prf);

        $t->subheader('user identification sql setup');
        $usr_idt = new user_official_type('');
        $t->assert_sql_table_create($usr_idt);
        $t->assert_sql_index_create($usr_idt);

        $t->subheader('protection type sql setup');
        $prt_typ = new protection_type('');
        $t->assert_sql_table_create($prt_typ);
        $t->assert_sql_index_create($prt_typ);

        $t->subheader('share type sql setup');
        $shr_typ = new share_type('');
        $t->assert_sql_table_create($shr_typ);
        $t->assert_sql_index_create($shr_typ);

        $t->subheader('phrase type sql setup');
        $phr_typ = new phrase_type('');
        $t->assert_sql_table_create($phr_typ);
        $t->assert_sql_index_create($phr_typ);

        $t->subheader('source type sql setup');
        $src_typ = new source_type('');
        $t->assert_sql_table_create($src_typ);
        $t->assert_sql_index_create($src_typ);

        $t->subheader('formula type sql setup');
        $frm_typ = new formula_type('');
        $t->assert_sql_table_create($frm_typ);
        $t->assert_sql_index_create($frm_typ);

        $t->subheader('view type sql setup');
        $dsp_typ = new view_type('');
        $t->assert_sql_table_create($dsp_typ);
        $t->assert_sql_index_create($dsp_typ);
    }

}