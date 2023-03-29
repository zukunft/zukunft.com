<?php

/*

    test/unit_db/language.php - database unit testing of the language functions
    -------------------------


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

use cfg\language;
use cfg\language_form;

class language_unit_db_tests
{

    function run(testing $t): void
    {

        global $usr;

        // init
        $t->header('Unit database tests of the language class (src/main/php/model/language/language.php)');
        $t->name = 'language read db->';
        $t->resource_path = 'db/language/';

        $t->subheader('Language db read tests');

        $test_name = 'load language ' . language::DEFAULT . ' by name and id';
        $phr = new language(language::DEFAULT);
        $phr->load_by_name(language::TN_READ, language::class);
        $lan_by_id = new language(language::DEFAULT);
        $lan_by_id->load_by_id($phr->id(), language::class);
        $t->assert($test_name, $lan_by_id->name(), language::TN_READ);
        

        $t->subheader('Language type db read tests');

        // test reading a language form via API that is not yet included in the preloaded language form
        // e.g. because it has been just added by the user to request e new language form
        $test_name = 'load language form ' . language_form::PLURAL . ' by id';
        global $language_forms;
        $lan_typ_id = $language_forms->id(language_form::PLURAL);
        $lan_typ = new language_form(language_form::PLURAL);
        $lan_typ->load_by_id($lan_typ_id);
        $t->assert($test_name, $lan_typ->code_id(), language_form::PLURAL);

    }

}

