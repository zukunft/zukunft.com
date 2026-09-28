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
include_once paths::DB . 'sql_db.php';
include_once paths::MODEL_PHRASE . 'term.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_CONST_FIELDS . 'formula_fields.php';
include_once html_paths::PHRASE . 'term.php';
include_once test_paths::CREATE . 'test_formulas.php';
include_once test_paths::CREATE . 'test_terms.php';
include_once test_paths::CREATE . 'test_triples.php';
include_once test_paths::CREATE . 'test_verbs.php';
include_once test_paths::CREATE . 'test_words.php';
include_once test_paths::UTILS . 'test_cleanup.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\phrase\term;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\fields\formula_fields;
use Zukunft\ZukunftCom\main\php\web\phrase\term as term_ui;
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


        $t->subheader($ts . 'id encoding');

        // the sign and the parity of the term id encode the class of the term object, so a term
        // created from a bare id e.g. from an api message or a page url must restore the class,
        // because the term constructor creates a word as dummy object (see term::set_id)
        $wrd = $t_wrd->word();
        $trm = new term($t->usr1);
        $trm->set_id($wrd->term()->id());
        $t->assert_true($t->name . 'a word term id sets the word class', $trm->is_word());
        $t->assert($t->name . 'a word term id keeps the word id', $trm->id_obj(), $wrd->id());
        $t->assert($t->name . 'a word term id round trip', $trm->id(), $wrd->term()->id());

        $trp = $t_trp->triple_pi();
        $trm = new term($t->usr1);
        $trm->set_id($trp->term()->id());
        $t->assert_true($t->name . 'a triple term id sets the triple class', $trm->is_triple());
        // negative: the dummy word of a new term must not survive a negative odd term id
        $t->assert_false($t->name . '... and not the word class', $trm->is_word());
        $t->assert($t->name . 'a triple term id keeps the triple id', $trm->id_obj(), $trp->id());
        $t->assert($t->name . 'a triple term id round trip', $trm->id(), $trp->term()->id());

        $frm = $t_frm->formula();
        $trm = new term($t->usr1);
        $trm->set_id($frm->term()->id());
        $t->assert_true($t->name . 'a formula term id sets the formula class', $trm->is_formula());
        // negative: only a negative even term id is a verb
        $t->assert_false($t->name . '... and not the verb class', $trm->is_verb());
        $t->assert($t->name . 'a formula term id keeps the formula id', $trm->id_obj(), $frm->id());
        $t->assert($t->name . 'a formula term id round trip', $trm->id(), $frm->term()->id());

        $vrb = $t_vrp->verb();
        $trm = new term($t->usr1);
        $trm->set_id($vrb->term()->id());
        $t->assert_true($t->name . 'a verb term id sets the verb class', $trm->is_verb());
        // negative: only a positive even term id is a formula
        $t->assert_false($t->name . '... and not the formula class', $trm->is_formula());
        $t->assert($t->name . 'a verb term id keeps the verb id', $trm->id_obj(), $vrb->id());
        $t->assert($t->name . 'a verb term id round trip', $trm->id(), $vrb->term()->id());

        // a verb is system vocabulary without a user, so changing the class of a verb term must
        // not fail, although a word, triple and formula cannot be created without a user
        $trm = $t_vrp->verb_alias()->term();
        $trm->set_id($wrd->term()->id());
        $t->assert_true($t->name . 'a word term id on a verb term sets the word class', $trm->is_word());
        // negative: the verb of the term must not survive an odd positive term id
        $t->assert_false($t->name . '... and not the verb class', $trm->is_verb());
        $t->assert($t->name . '... and sets the word id', $trm->id_obj(), $wrd->id());

        // a term id of zero encodes no class, so a reset only removes the id
        $trm = $t_trp->triple_pi()->term();
        $trm->reset();
        $t->assert_true($t->name . 'a reset term keeps the triple class', $trm->is_triple());
        $t->assert($t->name . 'a reset term has no id', $trm->id(), 0);

        // an already loaded object of the same class is kept, so that its name is not lost
        $trm = $wrd->term();
        $trm->set_id($wrd->term()->id());
        $t->assert($t->name . 'the same term id keeps the name', $trm->name(), $wrd->name_dsp());


        $t->subheader($ts . 'row mapper');

        // a row of the terms view carries the term id, which encodes the class, so the term
        // object gets the decoded object id e.g. the term id 2 is the formula with id 1;
        // the import loads the missing terms this way (see term_list::load) and a formula
        // with the term id as object id would fill the import with a formula id that the
        // database does not have, so the element insert violates the elements_formula_fk
        $msg = new user_message($t->usr1);
        $frm = $t_frm->formula();
        $db_row = [
            term::FLD_ID => $frm->term()->id(),
            sql_db::TBL_USER_PREFIX . term::FLD_ID => null,
            term::FLD_NAME => $frm->name(),
        ];
        $trm = new term($t->usr1);
        $trm->row_mapper_sandbox($db_row, $msg);
        $t->assert_true($t->name . 'a terms view row of a formula maps to the formula', $trm->is_formula());
        $t->assert($t->name . '... with the formula id', $trm->id_obj(), $frm->id());
        // negative: the term id must not become the id of the formula object
        $t->assert_false($t->name . '... and not with the term id', $trm->id_obj() == $frm->term()->id());
        $t->assert($t->name . '... and keeps the term id', $trm->id(), $frm->term()->id());
        $t->assert($t->name . '... and the name', $trm->name(), $frm->name());

        // a row of the formula table carries the object id itself, which must not be decoded
        // like a term id (the term id is only added to the row to select the class)
        $db_row = [
            term::FLD_ID => $frm->term()->id(),
            formula_fields::FLD_ID => $frm->id(),
            sql_db::TBL_USER_PREFIX . formula_fields::FLD_ID => null,
            formula_fields::FLD_NAME => $frm->name(),
        ];
        $trm = new term($t->usr1);
        $trm->row_mapper_sandbox($db_row, $msg, formula_fields::FLD_ID, formula_fields::FLD_NAME, formula_fields::FLD_TYPE);
        $t->assert_true($t->name . 'a formula table row maps to the formula', $trm->is_formula());
        $t->assert($t->name . '... with the formula id', $trm->id_obj(), $frm->id());
        // negative: the object id 1 read as a term id would be a word
        $t->assert_false($t->name . '... and not to a word', $trm->is_word());
        $t->assert($t->name . '... and the name', $trm->name(), $frm->name());


        $t->subheader($ts . 'sql setup');
        $trm = $t_trm->term();
        $t->assert_sql_view_create($trm);


        $t->subheader($ts . 'sql query');

        // check the creation of the prepared sql statements to load a term by id or name
        // TODO use assert_load_sql_id for all objects
        // TODO use assert_load_sql_name for all named objects
        $trm = new term($t->usr1);
        $t->assert_sql_by_id($sc, $trm);
        $t->assert_sql_by_name($sc, $trm);


        $t->subheader($ts . 'html frontend');

        $trm = $t_trm->term();
        $t->assert_api_to_ui($trm, new term_ui());
        $trm = $t_trm->term_triple();
        $t->assert_api_to_ui($trm, new term_ui());
        $trm = $t_trm->term_formula();
        $t->assert_api_to_ui($trm, new term_ui());
        $trm = $t_trm->term_verb();
        $t->assert_api_to_ui($trm, new term_ui());

    }

}
