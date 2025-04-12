<?php

/*

    test/unit/view.php - unit testing of the view functions
    ------------------
  

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

    Copyright (c) 1995-2024 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace unit;

use cfg\db\sql_creator;
use cfg\db\sql_type;
use cfg\view\view_term_link;
use shared\const\views;
use test\test_cleanup;

class view_term_link_tests
{
    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $sc = new sql_creator();
        $t->name = 'view->';
        $t->resource_path = 'db/view/';

        // start the test section (ts)
        $ts = 'unit view term link ';
        $t->header($ts);

        $t->subheader($ts . 'view_term_link sql setup');
        $dsp_trm_lnk = new view_term_link($usr);
        $t->assert_sql_table_create($dsp_trm_lnk);
        $t->assert_sql_index_create($dsp_trm_lnk);
        $t->assert_sql_foreign_key_create($dsp_trm_lnk);

        $t->subheader($ts . 'view_term_link sql read');
        $lnk = new view_term_link($usr);
        $t->assert_sql_by_id($sc, $lnk);
        $lnk = $t->view_term_link();
        $t->assert_sql_standard($sc, $lnk);
        // TODO check if all links have the check
        $t->assert_sql_by_link($sc, $lnk);
        $t->assert_sql_user_changes($sc, $lnk);

        $t->subheader($ts . 'view_term_link sql write insert');
        $lnk = $t->view_term_link();
        $t->assert_sql_insert($sc, $lnk);
        $t->assert_sql_insert($sc, $lnk, [sql_type::LOG]);
        $lnk->description = views::LINK_COM;
        $t->assert_sql_insert($sc, $lnk, [sql_type::USER]);
        $t->assert_sql_insert($sc, $lnk, [sql_type::LOG, sql_type::USER]);

        $t->subheader($ts . 'view_term_link sql write update');
        $lnk_described = $lnk->cloned();
        $lnk_described->description = views::LINK_COM;
        $t->assert_sql_update($sc, $lnk_described, $lnk);
        $t->assert_sql_update($sc, $lnk_described, $lnk, [sql_type::USER]);
        $t->assert_sql_update($sc, $lnk_described, $lnk, [sql_type::LOG]);
        $t->assert_sql_update($sc, $lnk_described, $lnk, [sql_type::LOG, sql_type::USER]);

        $t->subheader($ts . 'view_term_link sql delete');
        $t->assert_sql_delete($sc, $lnk);
        $t->assert_sql_delete($sc, $lnk, [sql_type::USER]);
        $t->assert_sql_delete($sc, $lnk, [sql_type::LOG]);
        $t->assert_sql_delete($sc, $lnk, [sql_type::LOG, sql_type::USER]);
        $t->assert_sql_delete($sc, $lnk, [sql_type::EXCLUDE]);
        $t->assert_sql_delete($sc, $lnk, [sql_type::USER, sql_type::EXCLUDE]);

        $t->subheader($ts . 'triple api');
        $lnk = $t->view_term_link();
        //$t->assert_api_json($lnk);
    }

}