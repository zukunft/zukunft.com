<?php

/*

    test/unit/term.php - unit testing of the TERM functions
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

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace unit;

use html\const\paths as html_paths;

include_once html_paths::WORD . 'word.php';
include_once html_paths::WORD . 'triple.php';
include_once html_paths::FORMULA . 'formula.php';
include_once html_paths::VERB . 'verb.php';
include_once html_paths::PHRASE . 'term.php';

use cfg\db\sql_creator;
use html\phrase\term as term_dsp;
use cfg\phrase\term;
use test\test_cleanup;

class term_tests
{
    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $sc = new sql_creator();
        $t->name = 'term->';
        $t->resource_path = 'db/term/';

        // start the test section (ts)
        $ts = 'unit term ';
        $t->header($ts);

        $t->subheader($ts . 'set and get of the grouped object');

        $wrd = $t->word();
        $trm = $wrd->term();
        $t->assert($t->name . 'word id', $trm->id_obj(), $wrd->id());
        $t->assert($t->name . 'word name', $trm->name(), $wrd->name_dsp());

        $trp = $t->triple_pi();
        $trm = $trp->term();
        $t->assert($t->name . 'triple id', $trm->id_obj(), $trp->id());
        $t->assert($t->name . 'triple name', $trm->name(), $trp->name());

        $frm = $t->formula();
        $trm = $frm->term();
        $t->assert($t->name . 'formula id', $trm->id_obj(), $frm->id());
        $t->assert($t->name . 'formula name', $trm->name(), $frm->name());

        $vrb = $t->verb();
        $trm = $vrb->term();
        $t->assert($t->name . 'verb id', $trm->id_obj(), $vrb->id());
        $t->assert($t->name . 'verb name', $trm->name(), $vrb->name());


        $t->subheader($ts . 'sql setup');
        $trm = $t->term();
        $t->assert_sql_view_create($trm);


        $t->subheader($ts . 'sql query');

        // check the creation of the prepared sql statements to load a term by id or name
        // TODO use assert_load_sql_id for all objects
        // TODO use assert_load_sql_name for all named objects
        $trm = new term($usr);
        $t->assert_sql_by_id($sc, $trm);
        $t->assert_sql_by_name($sc, $trm);


        $t->subheader($ts . 'html frontend');

        $trm = $t->term();
        $t->assert_api_to_dsp($trm, new term_dsp());
        $trm = $t->term_triple();
        $t->assert_api_to_dsp($trm, new term_dsp());
        $trm = $t->term_formula();
        $t->assert_api_to_dsp($trm, new term_dsp());
        $trm = $t->term_verb();
        $t->assert_api_to_dsp($trm, new term_dsp());

    }

}
