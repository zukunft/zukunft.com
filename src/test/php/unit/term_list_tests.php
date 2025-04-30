<?php

/*

    test/php/unit/term_list.php - unit tests related to a term list
    ---------------------------


    zukunft.com - calc with words

    copyright 1995-2021 by zukunft.com AG, Blumentalstrasse 15, 8707 Uetikon am See, Switzerland

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

namespace unit;

include_once SHARED_CONST_PATH . 'triples.php';
include_once SHARED_CONST_PATH . 'formulas.php';
include_once SHARED_TYPES_PATH . 'verbs.php';
include_once SHARED_CONST_PATH . 'words.php';

use cfg\db\sql_creator;
use cfg\db\sql_db;
use cfg\formula\formula;
use cfg\phrase\term;
use cfg\phrase\term_list;
use cfg\phrase\trm_ids;
use cfg\verb\verb;
use cfg\word\triple;
use cfg\word\word;
use html\html_base;
use html\phrase\term_list as term_list_dsp;
use shared\const\formulas;
use shared\const\triples;
use shared\const\words;
use shared\types\verbs;
use test\test_cleanup;

class term_list_tests
{

    // to avoid the need to forward the test setup to every function
    public test_cleanup $t;

    /**
     * execute all term list unit tests and add the test result to the given testing object
     * @param test_cleanup $t the testing object to cumulate the testing results
     */
    function run(test_cleanup $t): void
    {
        global $usr;

        // init
        $db_con = new sql_db();
        $sc = new sql_creator();
        $t->name = 'term_list->';
        $t->resource_path = 'db/term/';

        // start the test section (ts)
        $ts = 'unit term list ';
        $t->header($ts);

        $t->subheader($ts . 'term list display');

        $this->t = $t;


        $t->subheader($ts . 'sql statement creation');

        // load only the names
        $phr_lst = new term_list($usr);
        $t->assert_sql_names($sc, $phr_lst, new term($usr));
        $t->assert_sql_names($sc, $phr_lst, new term($usr), verbs::IS_NAME);

        $test_name = 'load terms by ids';
        $trm_lst = new term_list($usr);
        $trm_ids = new trm_ids(array(3, -2, 4, -7));
        $t->assert_sql_by_ids($test_name, $sc, $trm_lst, $trm_ids);
        $lst = $this->new_list();
        $t->assert_sql_like($sc, $lst);


        $t->subheader($ts . 'api');

        $trm_lst = $t->term_list();
        $t->assert_api($trm_lst);


        $t->subheader($ts . 'html frontend');

        $trm_lst = $t->term_list();
        $t->assert_api_to_dsp($trm_lst, new term_list_dsp());

    }

    /**
     * @returns term_list a dummy term list for unit tests
     */
    function new_list(): term_list
    {
        global $usr;

        $lst = new term_list($usr);
        $lst->add($this->t->new_word(words::MATH)->term());
        $lst->add($this->t->new_triple(
            triples::PI_NAME,
            words::PI, verbs::IS, words::MATH)->term());
        $lst->add($this->t->new_formula(formulas::INCREASE)->term());
        $lst->add($this->t->new_verb(verbs::IS)->term());
        return $lst;
    }

    /**
     * create a term list test object without using a database connection
     * that matches the all members of word with id 1 (math const)
     */
    function get_term_list_related(test_cleanup $t): term_list
    {
        global $usr;
        $trm_lst = new term_list($usr);
        $trm_lst->add($t->triple_pi()->term());
        $trm_lst->add($t->word()->term());
        return $trm_lst;
    }

    /**
     * create the standard filled term object
     */
    private function get_term(int $id, string $name, int $type): term
    {
        global $usr;
        $result = null;
        if ($type == 1) {
            $wrd = new word($usr);
            $wrd->set($id, $name);
            $result = $wrd->term();
        } elseif ($type == 2)  {
            $trp = new triple($usr);
            $trp->set($id, $name);
            $result = $trp->term();
        } elseif ($type == 3)  {
            $frm = new formula($usr);
            $frm->set($id, $name);
            $result = $frm->term();
        } elseif ($type == 4)  {
            $vrb = new verb();
            $vrb->set($id, $name);
            $result = $vrb->term();
        }
        return $result;
    }

}