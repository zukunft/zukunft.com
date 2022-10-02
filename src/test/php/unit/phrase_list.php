<?php

/*

    test/php/unit/phrase_list.php - unit tests related to a phrase list
    -----------------------------


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

use cfg\phrase_type;

class phrase_list_unit_tests
{
    const TEST_NAME = 'phrase_list->';
    const PATH = 'db/phrase/';
    const FILE_EXT = '.sql';
    const FILE_MYSQL = '_mysql';

    public testing $test;
    public phrase_list $lst;
    public sql_db $db_con;

    /**
     * execute all phrase list unit tests and return the test result
     * TODO create a common test result object to return
     * TODO capsule all unit tests in a class like this example
     */
    function run(testing $t): void
    {

        $t->header('Unit tests of the phrase list class (src/main/php/model/phrase/phrase_list.php)');

        $t->subheader('SQL statement creation tests');

        $this->test = $t;

        // sql to load a list of value by the phrase id
        $ids = array(1,2,3);
        $qp = $this->assert_by_ids_sql($ids, sql_db::POSTGRES);
        $this->assert_by_ids_sql($ids, sql_db::MYSQL);
        $this->test->assert_sql_name_unique($qp->name);

        $t->subheader('Selection tests');

        // check that a time phrase is correctly removed from a phrase list
        $phr_lst = $this->get_phrase_list();
        $phr_lst_ex_time = clone $phr_lst;
        $phr_lst_ex_time->ex_time();
        $result = true;
        $target = true;
        $t->dsp('phrase_list->ex_time', $target, $result);
        $result = $phr_lst_ex_time->dsp_id();
        $target = $this->get_phrase_list_ex_time()->dsp_id();
        $t->dsp('phrase_list->ex_time names', $target, $result);

    }

    /**
     * create the standard phrase list test object without using a database connection
     */
    public function get_phrase_list(): phrase_list
    {
        global $usr;
        $phr_lst = new phrase_list($usr);
        $phr_lst->add($this->get_phrase());
        $phr_lst->add($this->get_time_phrase());
        return $phr_lst;
    }

    /**
     * same as get_phrase_list but without time phrase
     */
    private function get_phrase_list_ex_time(): phrase_list
    {
        global $usr;
        $phr_lst = new phrase_list($usr);
        $phr_lst->add($this->get_phrase());
        return $phr_lst;
    }

    /**
     * create the standard filled phrase object
     */
    private function get_phrase(): phrase
    {
        global $usr;
        $wrd = new word($usr);
        $wrd->id = 1;
        $wrd->name = word::TN_ADD;
        return $wrd->phrase();
    }

    /**
     * create the filled time phrase object
     */
    private function get_time_phrase(): phrase
    {
        global $usr;
        $wrd = new word($usr);
        $wrd->id = 2;
        $wrd->name = word::TN_RENAMED;
        $wrd->type_id = cl(db_cl::WORD_TYPE, phrase_type::TIME);
        return $wrd->phrase();
    }

    /**
     * test the SQL statement creation for a phrase list
     *
     * @param array $ids all word or triple id that should be loaded
     * @param string $dialect if not PostgreSQL the name of the SQL dialect
     * @return void
     */
    private function assert_by_ids_sql(array $ids, string $dialect = ''): sql_par
    {
        global $usr;

        $lst = new phrase_list($usr);
        $db_con = new sql_db();
        $db_con->db_type = $dialect;
        $dialect_ext = '';
        if ($dialect == sql_db::MYSQL) {
            $dialect_ext = self::FILE_MYSQL;
        }
        $qp = $lst->load_by_wrd_ids_sql($db_con, $ids);
        $expected_sql = $this->test->file(self::PATH . $qp->name . $dialect_ext . self::FILE_EXT);
        $this->test->assert(
            self::TEST_NAME . $qp->name . $dialect,
            $this->test->trim($qp->sql),
            $this->test->trim($expected_sql)
        );
        $qp = $lst->load_by_trp_ids_sql($db_con, $ids);
        $expected_sql = $this->test->file(self::PATH . $qp->name . $dialect_ext . self::FILE_EXT);
        $this->test->assert(
            self::TEST_NAME . $qp->name . $dialect,
            $this->test->trim($qp->sql),
            $this->test->trim($expected_sql)
        );
        return $qp;
    }

}