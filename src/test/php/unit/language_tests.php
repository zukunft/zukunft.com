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

    Copyright (c) 1995-2026 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\test\php\unit;

use Zukunft\ZukunftCom\main\php\cfg\language\language;
use Zukunft\ZukunftCom\main\php\cfg\language\language_form;
use Zukunft\ZukunftCom\main\php\shared\enum\language_forms;
use Zukunft\ZukunftCom\main\php\shared\enum\languages;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class language_tests
{

    function run(test_cleanup $t): void
    {

        // init
        $t->name = 'language->';
        $t->resource_path = 'db/language/';

        // start the test section (ts)
        $ts = 'unit language ';
        $t->header($ts);

        $t->subheader($ts . 'sql setup');
        $lan = new language('');
        $t->assert_sql_table_create($lan);
        $t->assert_sql_index_create($lan);

        $t->subheader($ts . 'form sql setup');
        $lan_for = new language_form('');
        $t->assert_sql_table_create($lan_for);
        $t->assert_sql_index_create($lan_for);
        $t->assert_sql_foreign_key_create($lan_for);


        $t->subheader($ts . 'api');

        global $sys;
        $lan = $sys->typ_lst->lan->get_by_code_id(languages::DEFAULT);
        $t->assert_api($lan, 'language');


        // start the test section (ts)
        $ts = 'unit language form ';
        $t->header($ts);

        $t->subheader($ts . 'api');

        global $sys;
        $lan_typ = $sys->typ_lst->lan_for->get_by_code_id(language_forms::PLURAL);
        $t->assert_api($lan_typ, 'language_form');

    }

}