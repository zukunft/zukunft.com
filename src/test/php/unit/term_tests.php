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

namespace Zukunft\ZukunftCom\test\php\unit;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::DB . 'sql_creator.php';
include_once paths::MODEL_PHRASE . 'term.php';
include_once html_paths::PHRASE . 'term.php';
include_once test_paths::CREATE . 'test_formulas.php';
include_once test_paths::CREATE . 'test_terms.php';
include_once test_paths::CREATE . 'test_triples.php';
include_once test_paths::CREATE . 'test_verbs.php';
include_once test_paths::CREATE . 'test_words.php';
include_once test_paths::UTILS . 'test_cleanup.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\phrase\term;
use Zukunft\ZukunftCom\main\php\web\phrase\term as term_dsp;
use Zukunft\ZukunftCom\test\php\create\test_formulas;
use Zukunft\ZukunftCom\test\php\create\test_terms;
use Zukunft\ZukunftCom\test\php\create\test_triples;
use Zukunft\ZukunftCom\test\php\create\test_verbs;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class term_tests
{
    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $sc = new sql_creator();
        $t_wrd = new test_words($t);
        $t_vrp = new test_verbs($t);
        $t_trp = new test_triples($t);
        $t_frm = new test_formulas($t);
        $t_trm = new test_terms($t);
        $t->name = 'term->';
        $t->resource_path = 'db/term/';

        // start the test section (ts)
        $ts = 'unit term ';
        $t->header($ts);

        $t->subheader($ts . 'set and get of the grouped object');

        $wrd = $t_wrd->word();
        $trm = $wrd->term();
        $t->assert($t->name . 'word id', $trm->id_obj(), $wrd->id());
        $t->assert($t->name . 'word name', $trm->name(), $wrd->name_dsp());

        $trp = $t_trp->triple_pi();
        $trm = $trp->term();
        $t->assert($t->name . 'triple id', $trm->id_obj(), $trp->id());
        $t->assert($t->name . 'triple name', $trm->name(), $trp->name());

        $frm = $t_frm->formula();
        $trm = $frm->term();
        $t->assert($t->name . 'formula id', $trm->id_obj(), $frm->id());
        $t->assert($t->name . 'formula name', $trm->name(), $frm->name());

        $vrb = $t_vrp->verb();
        $trm = $vrb->term();
        $t->assert($t->name . 'verb id', $trm->id_obj(), $vrb->id());
        $t->assert($t->name . 'verb name', $trm->name(), $vrb->name());


        $t->subheader($ts . 'sql setup');
        $trm = $t_trm->term();
        $t->assert_sql_view_create($trm);


        $t->subheader($ts . 'sql query');

        // check the creation of the prepared sql statements to load a term by id or name
        // TODO use assert_load_sql_id for all objects
        // TODO use assert_load_sql_name for all named objects
        $trm = new term($usr);
        $t->assert_sql_by_id($sc, $trm);
        $t->assert_sql_by_name($sc, $trm);


        $t->subheader($ts . 'html frontend');

        $trm = $t_trm->term();
        $t->assert_api_to_dsp($trm, new term_dsp());
        $trm = $t_trm->term_triple();
        $t->assert_api_to_dsp($trm, new term_dsp());
        $trm = $t_trm->term_formula();
        $t->assert_api_to_dsp($trm, new term_dsp());
        $trm = $t_trm->term_verb();
        $t->assert_api_to_dsp($trm, new term_dsp());

    }

}
