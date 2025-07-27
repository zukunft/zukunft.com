<?php

/*

    test/php/unit_write/import_tests.php - testing of the import functions
    ------------------------------------
  

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

namespace unit_write;

include_once MODEL_IMPORT_PATH . 'import.php';
include_once MODEL_IMPORT_PATH . 'convert_wikipedia_table.php';
include_once MODEL_CONST_PATH . 'files.php';
include_once MODEL_SANDBOX_PATH . 'sandbox_named.php';
include_once MODEL_SANDBOX_PATH . 'sandbox_link_named.php';
include_once TEST_CONST_PATH . 'files.php';

use cfg\component\component;
use cfg\formula\formula;
use cfg\import\import_file;
use cfg\ref\ref;
use cfg\ref\source;
use cfg\sandbox\sandbox_link_named;
use cfg\sandbox\sandbox_named;
use cfg\user\user;
use cfg\verb\verb;
use cfg\view\view;
use cfg\word\triple;
use cfg\word\word;
use shared\const\components;
use shared\const\formulas;
use shared\const\refs;
use shared\const\sources;
use shared\const\triples;
use shared\const\users;
use shared\const\views;
use shared\const\words;
use shared\library;
use shared\types\verbs;
use test\test_cleanup;
use const\files as test_files;

class import_write_tests
{
    function run(test_cleanup $t): void
    {
        global $usr;

        $imf = new import_file();


        // start the test section (ts)
        $ts = 'db write import ';
        $t->header($ts);

        $this->assert_import_json_named($t, $ts, new user(),
            users::TEST_USER_NAME, users::TEST_USER_COM,
            [
                test_files::IMPORT_USERS,
                test_files::IMPORT_USERS_UPDATE,
                test_files::IMPORT_USERS_UNDO
            ]);

        $this->assert_import_json_named($t, $ts, new word($usr),
            words::TEST_ADD, words::TEST_ADD_COM,
            [
                test_files::IMPORT_WORDS,
                test_files::IMPORT_WORDS_UPDATE,
                test_files::IMPORT_WORDS_UNDO
            ]);


        $t->subheader($ts . 'verb');

        $test_name = 'import the test verb';
        $imp_msg = $imf->json_file(test_files::IMPORT_VERBS, $usr, false);
        $t->assert_true($test_name, $imp_msg->is_ok());
        $test_name = 'test if the verb has been added to the database';
        $vrb = new verb();
        $vrb->load_by_name(verbs::TEST_ADD_NAME);
        $t->assert_greater_zero($test_name, $vrb->id());

        $test_name = 'add the description to the test verb via import';
        $imp_msg = $imf->json_file(test_files::IMPORT_VERBS_UPDATE, $usr, false);
        $t->assert_true($test_name, $imp_msg->is_ok());
        $test_name = 'test if the description has been added in the database';
        $vrb = new verb();
        $vrb->load_by_name(verbs::TEST_ADD_NAME);
        $t->assert($test_name, $vrb->description(), verbs::TEST_ADD_COM);

        $test_name = 'remove the test verb via import';
        $imp_msg = $imf->json_file(test_files::IMPORT_VERBS_UNDO, $usr, false);
        $t->assert_true($test_name, $imp_msg->is_ok());
        // TODO prio 3 maybe activate but at least should be activated for normal sandbox objects
        /*
        $test_name = 'test if the test verb has been deleted from the database';
        $vrb = new verb();
        $vrb->load_by_name(verbs::TEST_ADD_NAME);
        $t->assert($test_name, $vrb->id(), 0);
        */

        $test_name = 'remove the test verb directly as fallback to cleanup the database';
        $vrb = new verb();
        $vrb->load_by_name(verbs::TEST_ADD_NAME);
        if ($vrb->id() > 0) {
            $vrb->set_user($usr);
            $vrb->del();
        }
        $vrb = new verb();
        $vrb->load_by_name(verbs::TEST_ADD_NAME);
        $t->assert($test_name, $vrb->id(), 0);


        $this->assert_import_json_named($t, $ts, new triple($usr),
            triples::SYSTEM_TEST_ADD, triples::SYSTEM_TEST_ADD_COM,
            [
                test_files::IMPORT_TRIPLES,
                test_files::IMPORT_TRIPLES_UPDATE,
                test_files::IMPORT_TRIPLES_UNDO
            ]);


        $test_name = 'remove the test word and word to directly as fallback to cleanup the database as fallback for the triple case';
        $wrd = new word($usr);
        $wrd->load_by_name(words::TEST_ADD);
        if ($wrd->id() > 0) {
            $wrd->del();
        }
        $wrd = new word($usr);
        $wrd->load_by_name(words::TEST_ADD);
        $t->assert($test_name, $wrd->id(), 0);
        $wrd_to = new word($usr);
        $wrd_to->load_by_name(words::TEST_ADD_TO);
        if ($wrd_to->id() > 0) {
            $wrd_to->del();
        }
        $wrd_to = new word($usr);
        $wrd_to->load_by_name(words::TEST_ADD_TO);
        $t->assert($test_name, $wrd_to->id(), 0);


        $this->assert_import_json_named($t, $ts, new source($usr),
            sources::SYSTEM_TEST_ADD, sources::SYSTEM_TEST_ADD_COM,
            [
                test_files::IMPORT_SOURCES,
                test_files::IMPORT_SOURCES_UPDATE,
                test_files::IMPORT_SOURCES_UNDO
            ]);


        $t->subheader($ts . 'reference');

        /* TODO Prio 1 activate
        $test_name = 'import the test reference';
        $imp_msg = $imf->json_file(test_files::IMPORT_WORDS, $usr, false);
        $t->assert_true($test_name, $imp_msg->is_ok());
        $test_name = 'test if the reference has been added to the database';
        $ref = new ref($usr);
        $ref->load_by_ex_key(refs::SYSTEM_TEST_ADD);
        $t->assert_greater_zero($test_name, $ref->id());

        $test_name = 'add the description to the test reference via import';
        $imp_msg = $imf->json_file(test_files::IMPORT_WORDS_UPDATE, $usr, false);
        $t->assert_true($test_name, $imp_msg->is_ok());
        $test_name = 'test if the description has been added in the database';
        $ref = new ref($usr);
        $ref->load_by_ex_key(refs::SYSTEM_TEST_ADD);
        $t->assert($test_name, $ref->description, refs::SYSTEM_TEST_ADD);

        $test_name = 'remove the test reference via import';
        $imp_msg = $imf->json_file(test_files::IMPORT_WORDS_UNDO, $usr, false);
        $t->assert_true($test_name, $imp_msg->is_ok());
        //$test_name = 'test if the test reference has been deleted from the database';
        $ref = new ref($usr);
        $ref->load_by_ex_key(refs::SYSTEM_TEST_ADD);
        // TODO prio 2 activate but least the removal of the user
        //$t->assert($test_name, $ref->id(), 0);

        $test_name = 'remove the test reference directly as fallback to cleanup the database';
        $ref = new ref($usr);
        $ref->load_by_ex_key(refs::SYSTEM_TEST_ADD);
        if ($ref->id() > 0) {
            $ref->del();
        }
        $ref = new ref($usr);
        $ref->load_by_ex_key(refs::SYSTEM_TEST_ADD);
        $t->assert($test_name, $ref->id(), 0);
        */


        $this->assert_import_json_named($t, $ts, new formula($usr),
            formulas::SYSTEM_TEST_ADD, formulas::SYSTEM_TEST_ADD_COM,
            [
                test_files::IMPORT_FORMULAS,
                test_files::IMPORT_FORMULAS_UPDATE,
                test_files::IMPORT_FORMULAS_UNDO
            ]);

        $this->assert_import_json_named($t, $ts, new component($usr),
            components::TEST_ADD_NAME, components::TEST_ADD_COM,
            [
                test_files::IMPORT_COMPONENTS,
                test_files::IMPORT_COMPONENTS_UPDATE,
                test_files::IMPORT_COMPONENTS_UNDO
            ]);

        $this->assert_import_json_named($t, $ts, new view($usr),
            views::TEST_ADD_NAME, views::TEST_ADD_COM,
            [
                test_files::IMPORT_VIEWS,
                test_files::IMPORT_VIEWS_UPDATE,
                test_files::IMPORT_VIEWS_UNDO
            ]);

    }

    /**
     * test creating a sandbox named object via json import
     * and update and delete it
     *
     * @param test_cleanup $t the test object to collect the test results
     * @param string $ts the test section name just for the log header
     * @param sandbox_named $sbx the named sandbox object
     * @param string $add_name the name of the added test object
     * @param string $description the description used for testing
     * @param array $files with the create, update and delete json message file
     * @return void the result is documented in the test object $t
     */
    function assert_import_json_named(
        test_cleanup                          $t,
        string                                $ts,
        sandbox_named|sandbox_link_named|user $sbx,
        string                                $add_name,
        string                                $description,
        array                                 $files
    ): void
    {
        global $usr;

        $lib = new library();
        $imf = new import_file();

        $name = $lib->class_to_name($sbx::class);
        $t->subheader($ts . $name);

        $test_name = 'import the test ' . $name;
        $imp_msg = $imf->json_file($files[0], $usr, false);
        $t->assert_true($test_name, $imp_msg->is_ok());
        $test_name = 'test if the ' . $name . ' has been added to the database';
        $sbx->load_by_name($add_name);
        $t->assert_greater_zero($test_name, $sbx->id());

        $test_name = 'add the description to the test ' . $name . ' via import';
        $imp_msg = $imf->json_file($files[1], $usr, false);
        $t->assert_true($test_name, $imp_msg->is_ok());
        $test_name = 'test if the description has been added in the database';
        $sbx->load_by_name($add_name);
        $t->assert($test_name, $sbx->description(), $description);

        $test_name = 'remove the test ' . $name . ' via import';
        $imp_msg = $imf->json_file($files[2], $usr, false);
        $t->assert_true($test_name, $imp_msg->is_ok());
        $test_name = 'test if the test ' . $name . ' has been deleted from the database';
        $sbx->load_by_name($add_name);
        $t->assert($test_name, $sbx->id(), 0);

        $test_name = 'remove the test ' . $name . ' directly as fallback to cleanup the database';
        $sbx->load_by_name($add_name);
        if ($sbx->id() > 0) {
            $sbx->del();
        }
        $sbx->load_by_name($add_name);
        $t->assert($test_name, $sbx->id(), 0);
    }

}