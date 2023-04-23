<?php

/*

    test/unit/result_list.php - unit testing of the FORMULA VALUE functions
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

include_once WEB_FIGURE_PATH . 'figure_list.php';

use model\figure;
use model\figure_list;
use model\sql_db;
use html\figure\figure_list as figure_list_dsp;

class figure_list_unit_tests
{

    function run(testing $t): void
    {

        global $usr;

        // init
        $db_con = new sql_db();
        $t->name = 'figure->';
        $t->resource_path = 'db/figure/';
        $json_file = 'unit/figure/figure_list_import.json';
        $usr->set_id(1);


        $t->header('Unit tests of the figure list class (src/main/php/model/figure/figure_list.php)');

        $t->subheader('SQL creation tests');

        // sql to load a list of results by the formula id
        $fig_lst = new figure_list($usr);
        $fig = new figure($usr);
        $fig->set_id(1);
        // TODO active
        //$t->assert_load_list_sql($db_con, $fig_lst, $fig);


        $t->subheader('Im- and Export tests');
        // TODO active
        //$t->assert_json(new figure_list($usr), $json_file);


        $t->subheader('HTML frontend unit tests');

        // TODO active
        //$fig_lst = $t->dummy_figure_list();
        //$t->assert_api_to_dsp($fig_lst, new figure_list_dsp());

    }

}