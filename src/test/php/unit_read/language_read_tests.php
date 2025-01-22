<?php

/*

    test/php/unit_read/language.php - database unit testing of the language functions
    -------------------------------


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

use cfg\language\language;
use cfg\language\language_form;
use test\test_cleanup;

class language_read_tests
{

    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $t->header('language database unit');
        $t->name = 'language read db->';
        $t->resource_path = 'db/language/';

        $t->subheader('Language db read tests');

        $test_name = 'load language ' . language::DEFAULT . ' by name and id';
        $lan = new language(language::DEFAULT);
        $lan->load_by_name(language::TN_READ);
        $lan_by_id = new language(language::DEFAULT);
        $lan_by_id->load_by_id($lan->id(), language::class);
        $t->assert($test_name, $lan_by_id->name(), language::TN_READ);
        

        $t->subheader('Language type db read tests');

        // test reading a language form via API that is not yet included in the preloaded language form
        // e.g. because it has been just added by the user to request e new language form
        $test_name = 'load language form ' . language_form::PLURAL . ' by id';
        global $lan_for_cac;
        $lan_typ_id = $lan_for_cac->id(language_form::PLURAL);
        $lan_typ = new language_form(language_form::PLURAL);
        $lan_typ->load_by_id($lan_typ_id);
        $t->assert($test_name, $lan_typ->code_id(), language_form::PLURAL);

    }

}

