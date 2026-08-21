<?php

/*

    test/unit/html/term.php - testing of the term display functions
    -----------------------
  

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

namespace Zukunft\ZukunftCom\test\php\unit_ui;

use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\web\formula\formula;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\phrase\term;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\verb\verb;
use Zukunft\ZukunftCom\main\php\web\word\triple;
use Zukunft\ZukunftCom\main\php\web\word\word;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_formulas;
use Zukunft\ZukunftCom\test\php\create\test_triples;
use Zukunft\ZukunftCom\test\php\create\test_verbs;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class term_ui_tests
{
    function run(test_cleanup $t): void
    {
        $html = new html_base();
        $t_wrd = new test_words($t);
        $t_vrb = new test_verbs($t);
        $t_trp = new test_triples($t);
        $t_frm = new test_formulas($t);
        $msg = new user_message();

        // start the test section (ts)
        $ts = 'unit ui html term ';
        $t->header($ts);

        $wrd = new term($t_wrd->word()->term()->api_json());
        $trp = new term($t_trp->triple_pi()->term()->api_json());
        $vrb = new term($t_vrb->verb()->term()->api_json());
        $frm = new term($t_frm->formula()->term()->api_json());
        $test_page = $html->text_h2('term display test');
        $test_page .= 'word term with tooltip: ' . $wrd->name_tip() . '<br>';
        $test_page .= 'word term with link: ' . $wrd->name_link() . '<br>';
        $test_page .= 'triple term with tooltip: ' . $trp->name_tip() . '<br>';
        $test_page .= 'triple term with link: ' . $trp->name_link() . '<br>';
        $test_page .= 'verb term with tooltip: ' . $vrb->name_tip() . '<br>';
        $test_page .= 'verb term with link: ' . $vrb->name_link() . '<br>';
        $test_page .= 'formula term with tooltip: ' . $frm->name_tip() . '<br>';
        $test_page .= 'formula term with link: ' . $frm->name_link() . '<br>';
        $t->html_page_test($test_page, 'term', 'term', $msg);

        $t->subheader($ts . 'term id');

        // the term id encodes the class and the id of the term object: an odd id is a phrase
        // (positive a word, negative a triple) and an even id is a formula (positive) resp. a
        // verb (negative); so set_id must create the object of the matching class, set its
        // object id and id() must return the same term id again (see web/phrase/term.php)
        $id_cases = [
            [1, word::class, 1],
            [3, word::class, 2],
            [-1, triple::class, 1],
            [-3, triple::class, 2],
            [2, formula::class, 1],
            [4, formula::class, 2],
            [-2, verb::class, 1],
            [-4, verb::class, 2],
        ];
        foreach ($id_cases as [$trm_id, $class, $obj_id]) {
            $trm_case = new term();
            $trm_case->set_id($trm_id);
            $test_name = 'the term id ' . $trm_id . ' is a ' . library::class_to_name($class);
            $t->assert($test_name, $trm_case->obj()::class, $class);
            $test_name = '... with the object id ' . $obj_id;
            $t->assert($test_name, $trm_case->obj_id(), $obj_id);
            $test_name = '... and id() returns the term id ' . $trm_id . ' again';
            $t->assert($test_name, $trm_case->id(), $trm_id);
        }

        // an already loaded term object is kept, so that setting the term id of the same object
        // again does not drop the name that the api message has delivered
        $test_name = 'setting the same term id keeps the loaded object';
        $trm_wrd = new term($t_wrd->word()->term()->api_json());
        $trm_wrd->set_id($trm_wrd->id());
        $t->assert($test_name, $trm_wrd->name(), word_names::MATH);

        // a term id of another class replaces the loaded object, because the loaded object is
        // not the object of the given term id any more
        $test_name = 'a term id of another class replaces the loaded object';
        $trm_wrd = new term($t_wrd->word()->term()->api_json());
        $trm_wrd->set_id(-1);
        $t->assert($test_name, $trm_wrd->obj()::class, triple::class);
        $test_name = '... and drops the name of the replaced object';
        $t->assert($test_name, $trm_wrd->name() ?? '', '');

        // zero is the term id of no object, so the object id stays zero and id() returns zero
        // again instead of the term id of a not existing object
        $test_name = 'the term id zero has the object id zero';
        $trm_zero = new term();
        $trm_zero->set_id(0);
        $t->assert($test_name, $trm_zero->obj_id(), 0);
        $test_name = '... and id() returns zero again';
        $t->assert($test_name, $trm_zero->id(), 0);
    }

}