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

namespace Zukunft\ZukunftCom\test\php\unit;

use DateTime;
use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::MODEL_PHRASE . 'phr_ids.php';
include_once paths::MODEL_PHRASE . 'phrase_list.php';
include_once paths::MODEL_PHRASE . 'term_list.php';
include_once paths::SHARED_TYPES . 'phrase_types.php';
include_once paths::SHARED_CONST . 'triples.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED_TYPES . 'verbs.php';
include_once test_paths::CONST . 'word_names.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phr_ids;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\cfg\phrase\term_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase_list as phrase_list_ui;
use Zukunft\ZukunftCom\main\php\shared\enum\foaf_direction;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\types\phrase_types as phrase_type_shared;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_const;
use Zukunft\ZukunftCom\test\php\create\test_phrases;
use Zukunft\ZukunftCom\test\php\create\test_triples;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

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

        global $sys;

        // init
        $db_con = new sql_db();
        $sc = new sql_creator();
        $t_wrd = new test_words($t);
        $t_trp = new test_triples($t);
        $t_phr = new test_phrases($t);
        $msg = new user_message();
        $t->name = 'phrase_list->';
        $t->resource_path = 'db/phrase/';

        // start the test section (ts)
        $ts = 'unit phrase list ';
        $t->header($ts);

        $t->subheader($ts . 'cast');

        $phr_lst = $this->get_phrase_list($t);
        $trm_lst = $phr_lst->term_list();
        // using dsp_id() does not work here because the second word has the term id 3 instead of the phrase id 2
        $t->assert('cast phrase list to term list', $phr_lst->dsp_name(), $trm_lst->dsp_name());


        $t->subheader($ts . 'sql statement creation');

        // load by name pattern (expected to be most often used)
        $phr_lst = new phrase_list($t->usr1);
        $t->assert_sql_like($sc, $phr_lst, 'S');

        // load by phrase ids
        $phr_lst = new phrase_list($t->usr1);
        $phr_ids = new phr_ids(array(3, -2, 4, -7));
        $test_name = 'load phrases by ids';
        $t->assert_sql_by_ids($test_name, $sc, $phr_lst, $phr_ids);
        $this->assert_sql_names_by_ids($t, $db_con, $phr_lst, $phr_ids);
        $phr_names = array(word_names::MATH, triple_names::MATH_CONST);
        $t->assert_sql_by_names($sc, $phr_lst, $phr_names);

        // to review
        $t->assert_sql_names($sc, $phr_lst, new phrase($t->usr1));
        $t->assert_sql_names($sc, $phr_lst, new phrase($t->usr1), triple_names::MATH_CONST);

        $this->test = $t;

        // sql to load a list of phrases by a phrase list
        $phr_lst = new phrase_list($t->usr1);
        $wrd = new word($t->usr1);
        $wrd->set(words::DEFAULT_WORD_ID, words::CH);
        $phr_lst->add($wrd->phrase());
        $vrb = $sys->verb(verbs::PART_NAME);
        $this->assert_sql_linked_phrases($db_con->sql_creator(), $t, $phr_lst, $vrb, foaf_direction::UP);
        // TODO Prio 1 activate
        //$this->assert_sql_by_phr_lst($db_con, $t, $phr_lst, $vrb, foaf_direction::UP);


        $t->subheader($ts . 'selection');

        // check that a time phrase is correctly removed from a phrase list
        $phr_lst = $this->get_phrase_list($t);
        $phr_lst_ex_time = clone $phr_lst;
        $phr_lst_ex_time->ex_time($msg);
        $t->assert('phrase_list->ex_time', true, true);
        $result = $phr_lst_ex_time->dsp_id();
        $target = $this->get_phrase_list_ex_time($t)->dsp_id();
        $t->assert('phrase_list->ex_time names', $result, $target);

        $test_name = 'get all words related to a phrase list: mathematics, constant, mathematical constant, Pi and Pi (Math) results in mathematics, constant and Pi';
        $phr_lst = $t_phr->phrase_list();
        $wrd_lst = $phr_lst->wrd_lst_all($msg);
        $t->assert($test_name, $wrd_lst->count(), 4);

        // TODO add assume time sql statement test

        $test_name = 'get this year from a list of years';
        $phr_lst = $t_phr->years();
        $fix_now = new DateTime(test_const::DUMMY_DATETIME);
        $msg = new user_message();
        $phr = $phr_lst->best_matching_time($t_wrd->word_year()->phrase(), $msg, $fix_now);
        $t->assert_text_contains($test_name, $phr->name(), $t_wrd->word_2022()->name());
        // TODO mix it with months and quarters to select the best matching and automatic estimations


        $t->subheader($ts . 'remove_terms');

        // positive: a phrase named in the delete term list is removed;
        // "Pi" has phrase id 17 but term id 33, so this only passes if the term ids are cast to phrase ids
        $test_name = 'a phrase named in the delete term list is removed';
        $phr_lst = new phrase_list($t->usr1);
        $phr_lst->add($this->get_phrase($t, word_names::ONE_ID, word_names::ONE));
        $phr_lst->add($this->get_phrase($t, word_names::PI_ID, word_names::PI));
        $del_lst = new term_list($t->usr1);
        $del_lst->add($this->get_phrase($t, word_names::PI_ID, word_names::PI)->term());
        $phr_lst->remove_terms($del_lst);
        $t->assert_text_not_contains($test_name, $phr_lst->dsp_name(), word_names::PI);
        $test_name = 'the phrase not in the delete term list remains';
        $t->assert_text_contains($test_name, $phr_lst->dsp_name(), word_names::ONE);

        // negative: a term that is not in the phrase list leaves the list unchanged
        $test_name = 'a term not in the phrase list leaves the list unchanged';
        $phr_lst = new phrase_list($t->usr1);
        $phr_lst->add($this->get_phrase($t, word_names::ONE_ID, word_names::ONE));
        $phr_lst->add($this->get_phrase($t, word_names::PI_ID, word_names::PI));
        $del_lst = new term_list($t->usr1);
        $del_lst->add($this->get_phrase($t, word_names::FLOW_ID, word_names::FLOW)->term());
        $phr_lst->remove_terms($del_lst);
        $t->assert($test_name, $phr_lst->count(), 2);


        $t->subheader($ts . 'FOAF');

        $test_name = 'test the verb "are" by getting the phrases that are a city';
        $wrd_city = $t_wrd->word_city();
        $city_lst = $wrd_city->are($msg);
        $target = $t_phr->phrase_list_cities();
        // TODO Prio 2 activate
        //$t->assert_contains($test_name, $city_lst->names(), $target->names());


        $t->subheader($ts . 'api');

        $phr_lst = $t_phr->phrase_list_api();
        $t->assert_api($phr_lst);


        $t->subheader($ts . 'html frontend');

        $phr_lst = $t_phr->phrase_list();
        $t->assert_api_to_ui($phr_lst, new phrase_list_ui());

        // math is dominant in a phrase list use math phrases as a suggestion for a new phrase
        $phr_lst_ui = $t_phr->ui_phrase_list();
        $phr = $phr_lst_ui->mainly();
        if ($phr != null) {
            $t->assert_text_contains('Main word is "math"', $phr->name(), word_names::MATH);
        }


        $t->subheader($ts . 'column order');

        // positive: the "is next main column after" chain orders the main columns and the
        // "is explaining column for" triples put each explaining column behind its main column
        $test_name = 'the explaining columns follow their main column';
        $phr_lst_ui = $t_phr->list_columns_ordered_ui();
        $target = implode(', ', [word_names::PROBLEM, word_names::LOSS, word_names::COST,
            word_names::SOLUTION, word_names::GAIN]);
        $t->assert($test_name, implode(', ', $phr_lst_ui->column_names()), $target);

        // negative: without the main column chain nothing tells which main column is the left
        // one, so the main columns keep the order of their explaining triples
        $test_name = 'without the main column chain the explaining triples decide';
        $phr_lst_ui = $t_phr->list_columns_unchained_ui();
        $target = implode(', ', [word_names::SOLUTION, word_names::GAIN,
            word_names::PROBLEM, word_names::LOSS, word_names::COST]);
        $t->assert($test_name, implode(', ', $phr_lst_ui->column_names()), $target);

        // negative: a circular main column chain has no first main column, so it is not walked,
        // but the columns still fall back to the explaining triples instead of being dropped
        $test_name = 'a circular main column chain drops no column';
        $phr_lst_ui = $t_phr->list_columns_circular_ui();
        $target = implode(', ', [word_names::SOLUTION, word_names::GAIN,
            word_names::PROBLEM, word_names::LOSS, word_names::COST]);
        $t->assert($test_name, implode(', ', $phr_lst_ui->column_names()), $target);

        // negative: a defined column that no explaining triple links to a main column is added
        // behind the ordered columns, so that no defined column is missing from the table
        $test_name = 'a column with no explaining triple is appended';
        $phr_lst_ui = $t_phr->list_columns_partly_explained_ui();
        $target = implode(', ', [word_names::PROBLEM, word_names::LOSS,
            word_names::SOLUTION, word_names::GAIN, word_names::COST]);
        $t->assert($test_name, implode(', ', $phr_lst_ui->column_names()), $target);

        // negative: the order triples alone define no column, so a list whose triples name no
        // column tier names no column and the caller falls back to its own ranking
        $test_name = 'a list without a column definition names no column';
        $phr_lst_ui = $t_phr->phrase_list_ui();
        $t->assert($test_name, $phr_lst_ui->column_names(), []);

        // the tier of a column says on which screens it is shown, so the table can hide a column
        // per screen size instead of dropping it; the "loss" column is defined as a mayor column
        // and the "potential loss" column as a main column
        $phr_lst_ui = $t_phr->list_columns_potential_loss_ui();
        $test_name = 'the tier of a mayor column is returned';
        $t->assert($test_name, $phr_lst_ui->column_tier(word_names::LOSS),
            triple_names::SYSTEM_COLUMN_MAYOR);
        $test_name = 'the tier of a main column is returned';
        $t->assert($test_name, $phr_lst_ui->column_tier(triple_names::POTENTIAL_LOSS),
            triple_names::SYSTEM_COLUMN_MAIN);
        // negative: a phrase that no triple of the list links to a tier is no column, so it has
        // no tier and the caller shows it on every screen
        $test_name = 'a phrase that is no column has no tier';
        $t->assert($test_name, $phr_lst_ui->column_tier(word_names::GAIN), '');


        $t->subheader($ts . 'child phrases');

        // the phrases that a triple of the list links to the given phrase, e.g. the problems
        // that the triples "<problem> (global problem)" link to "global problem"; the phrase
        // counterpart of child_names, used where the id is needed and not only the name
        $test_name = 'the phrases linked to the given phrase are returned';
        $phr_lst_ui = $t_phr->list_global_problems_ui();
        $phr = $t_trp->global_problem_ui()->phrase();
        $t->assert_contains($test_name, $phr_lst_ui->child_phrases($phr)->names(),
            [triple_names::GLOBAL_WARMING, word_names::POPULISM]);
        $test_name = '... and the phrase itself is not one of them';
        $t->assert_text_not_contains($test_name,
            implode(', ', $phr_lst_ui->child_phrases($phr)->names()), triple_names::GLOBAL_PROBLEM);
        // negative: a list without a triple that links to the given phrase has no child of it
        $test_name = 'a list with no link to the given phrase returns no child';
        $phr_lst_ui = $t_phr->list_columns_loss_ui();
        $t->assert($test_name, $phr_lst_ui->child_phrases($phr)->names(), []);



        $t->subheader($ts . 'import names');

        // an entry of the assigned json array that names no phrase is reported to the caller,
        // because the import would silently assign one phrase less than the json file asks for
        $test_name = 'an empty phrase name of an import is reported';
        $phr_lst = new phrase_list($t->usr1);
        $mapped = $phr_lst->import_map_names([word_names::MATH, ''], $msg);
        $t->assert_false($test_name, $mapped);
        $test_name = 'the empty phrase name message names the json part';
        $t->assert_text_contains($test_name, $msg->text(), word_names::MATH);
        $msg->reset();

        // a json array with only real names is mapped without any message
        $test_name = 'a list of phrase names is mapped';
        $phr_lst = new phrase_list($t->usr1);
        $mapped = $phr_lst->import_map_names([word_names::MATH, word_names::CONST_NAME], $msg);
        $t->assert_true($test_name, $mapped);

        // the phrases of an import have no id yet, so they are added to the list by their name
        $test_name = 'both names of the import are in the phrase list';
        $t->assert($test_name, $phr_lst->count(), 2);


        $t->subheader($ts . 'combined objects like phrases should not be used for im- or export, so not tests is needed. Instead the single objects like word or triple should be im- and exported');

    }

    /**
     * create the standard phrase list test object without using a database connection
     */
    function get_phrase_list(test_cleanup $t): phrase_list
    {
        $phr_lst = new phrase_list($t->usr1);
        $phr_lst->add($this->get_phrase_add($t));
        $phr_lst->add($this->get_time_phrase($t));
        return $phr_lst;
    }

    /**
     * same as get_phrase_list but without time phrase
     */
    private function get_phrase_list_ex_time(test_cleanup $t): phrase_list
    {
        $phr_lst = new phrase_list($t->usr1);
        $phr_lst->add($this->get_phrase_add($t));
        return $phr_lst;
    }

    /**
     * create the standard filled phrase object
     */
    private function get_phrase_add(test_cleanup $t): phrase
    {
        $wrd = new word($t->usr1);
        $wrd->set(words::DEFAULT_WORD_ID, word_names::TEST_ADD);
        return $wrd->phrase();
    }

    /**
     * create the filled time phrase object
     */
    private function get_time_phrase(test_cleanup $t): phrase
    {
        global $sys;

        $wrd = new word($t->usr1);
        $wrd->set(word_names::CONST_ID, word_names::TEST_RENAMED);
        $wrd->type_id = $sys->typ_lst->phr_typ->id(phrase_type_shared::TIME);
        return $wrd->phrase();
    }

    /**
     * create the standard filled phrase object
     */
    private function get_phrase(test_cleanup $t, int $id, string $name): phrase
    {
        $wrd = new word($t->usr1);
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