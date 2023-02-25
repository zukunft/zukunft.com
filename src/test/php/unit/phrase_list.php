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

use api\triple_api;
use api\word_api;
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

        global $usr;

        // init
        $db_con = new sql_db();
        $t->name = 'phrase_list->';
        $t->resource_path = 'db/phrase/';

        $t->header('Unit tests of the phrase list class (src/main/php/model/phrase/phrase_list.php)');


        $t->subheader('Cast tests');

        $phr_lst = $this->get_phrase_list();
        //$trm_lst = $phr_lst->term_list();
        //$t->assert('cast phrase list to term list', $phr_lst->dsp_id(), $trm_lst->dsp_id());


        $t->subheader('SQL statement creation tests');

        // load by phrase ids
        $phr_lst = new phrase_list($usr);
        $phr_ids = new phr_ids(array(3, -2, 4, -7));
        $this->assert_sql_by_ids($t, $db_con, $phr_lst, $phr_ids);

        $this->test = $t;

        // sql to load a list of value by the phrase id
        $ids = array(1, 2, 3);
        $qp = $this->assert_by_ids_sql($ids, sql_db::POSTGRES);
        $this->assert_by_ids_sql($ids, sql_db::MYSQL);
        $this->test->assert_sql_name_unique($qp->name);

        $phr_lst = new phrase_list($usr);
        $wrd = new word($usr);
        $wrd->set(1, word_api::TN_CH);
        $phr_lst->add($wrd->phrase());
        $this->assert_load_sql_linked_phrases(
            $db_con, $t, $phr_lst, 3, word_select_direction::UP
        );


        $t->subheader('Selection tests');

        // check that a time phrase is correctly removed from a phrase list
        $phr_lst = $this->get_phrase_list();
        $phr_lst_ex_time = clone $phr_lst;
        $phr_lst_ex_time->ex_time();
        $t->dsp('phrase_list->ex_time', true, true);
        $result = $phr_lst_ex_time->dsp_id();
        $target = $this->get_phrase_list_ex_time()->dsp_id();
        $t->dsp('phrase_list->ex_time names', $target, $result);


        $t->subheader('API unit tests');

        $phr_lst = $this->get_phrase_list_related();
        $t->assert_api($phr_lst);


    }

    /**
     * create the standard phrase list test object without using a database connection
     */
    function get_phrase_list(): phrase_list
    {
        global $usr;
        $phr_lst = new phrase_list($usr);
        $phr_lst->add($this->get_phrase_add());
        $phr_lst->add($this->get_time_phrase());
        return $phr_lst;
    }

    /**
     * create a phrase list test object without using a database connection
     * that matches the all members of word with id 1 (math const)
     */
    function get_phrase_list_related(): phrase_list
    {
        global $usr;
        $phr_lst = new phrase_list($usr);
        $phr_lst->add($this->get_phrase(1, word_api::TN_READ));
        $phr_lst->add($this->get_phrase(2, triple_api::TN_READ));
        return $phr_lst;
    }

    /**
     * same as get_phrase_list but without time phrase
     */
    private function get_phrase_list_ex_time(): phrase_list
    {
        global $usr;
        $phr_lst = new phrase_list($usr);
        $phr_lst->add($this->get_phrase_add());
        return $phr_lst;
    }

    /**
     * create the standard filled phrase object
     */
    private function get_phrase_add(): phrase
    {
        global $usr;
        $wrd = new word($usr);
        $wrd->set(1, word_api::TN_ADD);
        return $wrd->phrase();
    }

    /**
     * create the filled time phrase object
     */
    private function get_time_phrase(): phrase
    {
        global $usr;
        $wrd = new word($usr);
        $wrd->set(2, word_api::TN_RENAMED);
        $wrd->type_id = cl(db_cl::PHRASE_TYPE, phrase_type::TIME);
        return $wrd->phrase();
    }

    /**
     * create the standard filled phrase object
     */
    private function get_phrase(int $id, string $name): phrase
    {
        global $usr;
        $wrd = new word($usr);
        $wrd->set($id, $name);
        return $wrd->phrase();
    }

    /**
     * test the SQL statement creation for a phrase list
     *
     * @param array $ids all word or triple id that should be loaded
     * @param string $dialect if not Postgres the name of the SQL dialect
     * @return void
     */
    private function assert_by_ids_sql(array $ids, string $dialect = ''): sql_par
    {
        global $usr;

        $lib = new library();

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
            $lib->trim($qp->sql),
            $lib->trim($expected_sql)
        );
        $qp = $lst->load_by_trp_ids_sql($db_con, $ids);
        $expected_sql = $this->test->file(self::PATH . $qp->name . $dialect_ext . self::FILE_EXT);
        $this->test->assert(
            self::TEST_NAME . $qp->name . $dialect,
            $lib->trim($qp->sql),
            $lib->trim($expected_sql)
        );
        return $qp;
    }

    /**
     * test the SQL statement creation for a phrase list in all SQL dialect
     * and check if the statement name is unique
     *
     * @param testing $t the test environment
     * @param sql_db $db_con the test database connection
     * @param phrase_list $lst the empty phrase list object
     * @param phr_ids $ids filled with a list of word ids to be used for the query creation
     * @return bool true if all tests are fine
     */
    private function assert_sql_by_ids(testing $t, sql_db $db_con, phrase_list $lst, phr_ids $ids): bool
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $lst->load_names_sql_by_ids($db_con, $ids);
        $result = $t->assert_qp($qp, sql_db::POSTGRES);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $lst->load_names_sql_by_ids($db_con, $ids);
            $result = $t->assert_qp($qp, sql_db::MYSQL);
        }
        return $result;
    }

    /**
     * similar to assert_load_sql_name from test_base but to test the SQL statement creation
     * to get the linked phrases
     *
     * @param sql_db $db_con does not need to be connected to a real database
     * @param testing $t the testing object with the error counting of this test run
     * @param object $usr_obj the user sandbox object e.g. a word
     * @param int $verb_id to select only words linked with this verb
     * @param string $direction to define the link direction
     * @return bool true if all tests are fine
     */
    function assert_load_sql_linked_phrases(
        sql_db  $db_con,
        testing $t,
        object  $usr_obj,
        int     $verb_id,
        string  $direction): bool
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $usr_obj->load_sql_linked_phrases($db_con, $verb_id, $direction);
        $result = $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $usr_obj->load_sql_linked_phrases($db_con, $verb_id, $direction);
            $result = $t->assert_qp($qp, $db_con->db_type);
        }
        return $result;
    }

}