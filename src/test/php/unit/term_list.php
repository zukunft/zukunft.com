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

use html\html_base;

class term_list_unit_tests
{

    // to avoid the need to forward the test setup to every function
    public testing $t;

    /**
     * execute all term list unit tests and add the test result to the given testing object
     * @param testing $t the testing object to cumulate the testing results
     */
    function run(testing $t): void
    {
        // init
        $db_con = new sql_db();
        $t->name = 'term_list->';
        $t->resource_path = 'db/phrase/';

        $t->header('Unit tests of the term list class (src/main/php/model/phrase/term_list.php)');

        $html = new html_base();

        $t->subheader('term list display tests');

        $this->t = $t;

        // test the word list display functions
        $lst = $this->new_list();
        $test_page = $html->text_h2('Term list display test');
        $test_page .= 'names with links: ' . $lst->dsp_obj()->dsp() . '<br>';

        $t->html_test($test_page, 'term_list', $t);


        $t->subheader('term list sql tests');

        $lst = $this->new_list();
        $t->assert_load_sql($db_con, $lst);


    }

    /**
     * @returns term_list a dummy term list for unit tests
     */
    function new_list(): term_list
    {
        global $usr;

        $lst = new term_list($usr);
        $lst->add($this->t->new_word(word::TN_READ)->term());
        $lst->add($this->t->new_triple(
            triple::TN_READ_NAME,
            triple::TN_READ, verb::IS_A, word::TN_READ)->term());
        $lst->add($this->t->new_formula(formula::TN_READ)->term());
        $lst->add($this->t->new_verb(verb::IS_A)->term());
        return $lst;
    }

}