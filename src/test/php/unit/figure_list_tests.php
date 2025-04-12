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

namespace unit;

include_once DB_PATH . 'sql_creator.php';
include_once MODEL_FORMULA_PATH . 'fig_ids.php';
include_once MODEL_FORMULA_PATH . 'figure_list.php';
include_once WEB_FIGURE_PATH . 'figure_list.php';

use cfg\db\sql_creator;
use cfg\formula\fig_ids;
use cfg\formula\figure_list;
use html\figure\figure_list as figure_list_dsp;
use shared\types\api_type;
use test\test_cleanup;

class figure_list_tests
{

    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $sc = new sql_creator();
        $t->name = 'figure->';
        $t->resource_path = 'db/figure/';
        $json_file = 'unit/figure/figure_list_import.json';

        // start the test section (ts)
        $ts = 'unit figure list ';
        $t->header($ts);

        $t->subheader($ts . 'sql statement');

        // load by figure ids
        $test_name = 'load figures by ids';
        $fig_lst = new figure_list($usr);
        $t->assert_sql_by_ids($test_name, $sc, $fig_lst, new fig_ids([1, -1]));


        $t->subheader($ts . 'api');

        $fig_lst = $t->figure_list();
        $t->assert_api($fig_lst, 'figure_list_without_phrases');
        $t->assert_api($fig_lst, 'figure_list_with_phrases', [api_type::INCL_PHRASES]);


        $t->subheader($ts . 'html frontend');

        $fig_lst = $t->figure_list();
        $t->assert_api_to_dsp($fig_lst, new figure_list_dsp());

    }

}