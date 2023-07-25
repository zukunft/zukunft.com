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

namespace test;

use api\formula_api;
use api\triple_api;
use api\word_api;
use cfg\phrase_list;
use cfg\trm_ids;
use html\html_base;
use html\phrase\term_list as term_list_dsp;
use cfg\formula;
use cfg\sql_db;
use cfg\term;
use cfg\term_list;
use cfg\triple;
use cfg\verb;
use cfg\word;

class term_list_unit_tests
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
        $t->name = 'term_list->';
        $t->resource_path = 'db/phrase/';

        $t->header('Unit tests of the term list class (src/main/php/model/phrase/term_list.php)');

        $html = new html_base();

        $t->subheader('term list display tests');

        $this->t = $t;


        $t->subheader('term list sql tests');

        $trm_lst = new term_list($usr);
        $trm_ids = new trm_ids(array(3, -2, 4, -7));
        $t->assert_sql_by_ids($db_con, $trm_lst, $trm_ids);
        $lst = $this->new_list();
        $t->assert_sql_like($db_con, $lst);


        $t->subheader('API unit tests');

        $trm_lst = $t->dummy_term_list();
        $t->assert_api($trm_lst);


        $t->subheader('HTML frontend unit tests');

        $trm_lst = $t->dummy_term_list();
        $t->assert_api_to_dsp($trm_lst, new term_list_dsp());

    }

    /**
     * @returns term_list a dummy term list for unit tests
     */
    function new_list(): term_list
    {
        global $usr;

        $lst = new term_list($usr);
        $lst->add($this->t->new_word(word_api::TN_READ)->term());
        $lst->add($this->t->new_triple(
            triple_api::TN_PI_NAME,
            triple_api::TN_PI, verb::IS, word_api::TN_READ)->term());
        $lst->add($this->t->new_formula(formula_api::TN_INCREASE)->term());
        $lst->add($this->t->new_verb(verb::IS)->term());
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
        $trm_lst->add($t->dummy_triple_pi()->term());
        $trm_lst->add($t->dummy_word()->term());
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