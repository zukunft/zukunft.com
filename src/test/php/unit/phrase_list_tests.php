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

namespace unit;

use cfg\const\paths;

include_once paths::MODEL_PHRASE . 'phr_ids.php';
include_once paths::MODEL_PHRASE . 'phrase_list.php';
include_once paths::SHARED_TYPES . 'phrase_type.php';
include_once paths::SHARED_CONST . 'triples.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED_TYPES . 'verbs.php';

use cfg\db\sql_creator;
use cfg\db\sql_db;
use cfg\phrase\phr_ids;
use cfg\phrase\phrase;
use cfg\phrase\phrase_list;
use cfg\verb\verb;
use cfg\word\word;
use html\phrase\phrase_list as phrase_list_dsp;
use shared\enum\foaf_direction;
use shared\const\triples;
use shared\const\words;
use shared\types\phrase_type as phrase_type_shared;
use shared\types\verbs;
use test\test_cleanup;

class phrase_list_tests
{
    public test_cleanup $test;
    public phrase_list $lst;
    public sql_db $db_con;

    /**
     * execute all phrase list unit tests and return the test result
     * TODO create a common test result object to return
     * TODO capsule all unit tests in a class like this example
     */
    function run(test_cleanup $t): void
    {

        global $usr;
        global $vrb_cac;

        // init
        $db_con = new sql_db();
        $sc = new sql_creator();
        $t->name = 'phrase_list->';
        $t->resource_path = 'db/phrase/';

        // start the test section (ts)
        $ts = 'unit phrase list ';
        $t->header($ts);

        $t->subheader($ts . 'cast');

        $phr_lst = $this->get_phrase_list();
        $trm_lst = $phr_lst->term_list();
        // using dsp_id() does not work here because the second word has the term id 3 instead of the phrase id 2
        $t->assert('cast phrase list to term list', $phr_lst->dsp_name(), $trm_lst->dsp_name());


        $t->subheader($ts . 'sql statement creation');

        // load by name pattern (expected to be most often used)
        $phr_lst = new phrase_list($usr);
        $t->assert_sql_like($sc, $phr_lst, 'S');

        // load by phrase ids
        $phr_lst = new phrase_list($usr);
        $phr_ids = new phr_ids(array(3, -2, 4, -7));
        $test_name = 'load phrases by ids';
        $t->assert_sql_by_ids($test_name, $sc, $phr_lst, $phr_ids);
        $this->assert_sql_names_by_ids($t, $db_con, $phr_lst, $phr_ids);
        $phr_names = array(words::MATH, triples::MATH_CONST);
        $t->assert_sql_by_names($sc, $phr_lst, $phr_names);

        // to review
        $t->assert_sql_names($sc, $phr_lst, new phrase($usr));
        $t->assert_sql_names($sc, $phr_lst, new phrase($usr), triples::MATH_CONST);

        $this->test = $t;

        // sql to load a list of phrases by a phrase list
        $phr_lst = new phrase_list($usr);
        $wrd = new word($usr);
        $wrd->set(words::DEFAULT_WORD_ID, words::CH);
        $phr_lst->add($wrd->phrase());
        $vrb = $vrb_cac->get_verb(verbs::PART_NAME);
        $this->assert_sql_linked_phrases($db_con->sql_creator(), $t, $phr_lst, $vrb, foaf_direction::UP);
        // TODO activate Prio 1
        //$this->assert_sql_by_phr_lst($db_con, $t, $phr_lst, $vrb, foaf_direction::UP);


        $t->subheader($ts . 'selection');

        // check that a time phrase is correctly removed from a phrase list
        $phr_lst = $this->get_phrase_list();
        $phr_lst_ex_time = clone $phr_lst;
        $phr_lst_ex_time->ex_time();
        $t->display('phrase_list->ex_time', true, true);
        $result = $phr_lst_ex_time->dsp_id();
        $target = $this->get_phrase_list_ex_time()->dsp_id();
        $t->display('phrase_list->ex_time names', $target, $result);

        $test_name = 'get all words related to a phrase list: mathematics, constant, mathematical constant, Pi and Pi (Math) results in mathematics, constant and Pi';
        $phr_lst = $t->phrase_list();
        $wrd_lst = $phr_lst->wrd_lst_all();
        $t->assert($test_name, $wrd_lst->count(), 4);

        // TODO add assume time sql statement test


        $t->subheader($ts . 'FOAF');

        $test_name = 'test the verb "are" by getting the phrases that are a city';
        $wrd_city = $t->word_city();
        $city_lst = $wrd_city->are($t->phrase_list_all());
        $target = $t->phrase_list_cities();
        // TODO activate Prio 2
        //$t->assert_contains($test_name, $city_lst->names(), $target->names());


        $t->subheader($ts . 'api');

        $phr_lst = $t->phrase_list();
        $t->assert_api($phr_lst);


        $t->subheader($ts . 'html frontend');

        $phr_lst = $t->phrase_list();
        $t->assert_api_to_dsp($phr_lst, new phrase_list_dsp());

        // math is dominant in a phrase list use math phrases as a suggestion for a new phrase
        $phr_lst_dsp = $t->phrase_list_dsp();
        $phr = $phr_lst_dsp->mainly();
        if ($phr != null) {
            $t->assert_text_contains('Main word is "math"', $phr->name(), words::MATH);
        }



        $t->subheader($ts . 'combined objects like phrases should not be used for im- or export, so not tests is needed. Instead the single objects like word or triple should be im- and exported');

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
        $wrd->set(words::DEFAULT_WORD_ID, words::TEST_ADD);
        return $wrd->phrase();
    }

    /**
     * create the filled time phrase object
     */
    private function get_time_phrase(): phrase
    {
        global $usr;
        global $phr_typ_cac;

        $wrd = new word($usr);
        $wrd->set(words::CONST_ID, words::TEST_RENAMED);
        $wrd->type_id = $phr_typ_cac->id(phrase_type_shared::TIME);
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
     * test the SQL statement creation for a phrase list in all SQL dialect
     * and check if the statement name is unique
     *
     * @param test_cleanup $t the test environment
     * @param sql_db $db_con the test database connection
     * @param phrase_list $lst the empty phrase list object
     * @param phr_ids $ids filled with a list of word ids to be used for the query creation
     */
    private function assert_sql_names_by_ids(
        test_cleanup $t,
        sql_db $db_con,
        phrase_list $lst,
        phr_ids $ids): void
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $lst->load_names_sql_by_ids($db_con->sql_creator(), $ids);
        $result = $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $lst->load_names_sql_by_ids($db_con->sql_creator(), $ids);
            $t->assert_qp($qp, $db_con->db_type);
        }
    }

    /**
     * similar to assert_load_sql_name from test_base but to test the SQL statement creation
     * to get the linked phrases
     *
     * @param sql_db $db_con does not need to be connected to a real database
     * @param test_cleanup $t the testing object with the error counting of this test run
     * @param object $usr_obj the user sandbox object e.g. a word
     * @param verb|null $vrb to select only words linked with this verb
     * @param foaf_direction $direction to define the link direction
     */
    private function assert_sql_linked_phrases(
        sql_creator    $sc,
        test_cleanup   $t,
        object         $usr_obj,
        ?verb          $vrb,
        foaf_direction $direction): void
    {
        // check the Postgres query syntax
        $sc->set_db_type(sql_db::POSTGRES);
        $qp = $usr_obj->load_sql_linked_phrases($sc, $vrb, $direction);
        $result = $t->assert_qp($qp, $sc->db_type());

        // ... and check the MySQL query syntax
        if ($result) {
            $sc->set_db_type(sql_db::MYSQL);
            $qp = $usr_obj->load_sql_linked_phrases($sc, $vrb, $direction);
            $t->assert_qp($qp, $sc->db_type());
        }
    }

    /**
     * similar to assert_sql_linked_phrases from test_base but to test the SQL statement creation
     * to get the linked phrases and using the separate sql creator
     *
     * @param sql_db $db_con does not need to be connected to a real database
     * @param test_cleanup $t the testing object with the error counting of this test run
     * @param object $usr_obj the user sandbox object e.g. a word
     * @param verb|null $vrb to select only words linked with this verb
     * @param foaf_direction $direction to define the link direction
     */
    private function assert_sql_by_phr_lst(
        sql_db         $db_con,
        test_cleanup   $t,
        object         $usr_obj,
        ?verb          $vrb,
        foaf_direction $direction): void
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $usr_obj->load_sql_by_phr_lst($db_con->sql_creator(), $vrb, $direction);
        $result = $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $usr_obj->load_sql_by_phr_lst($db_con->sql_creator(), $vrb, $direction);
            $t->assert_qp($qp, $db_con->db_type);
        }
    }

}