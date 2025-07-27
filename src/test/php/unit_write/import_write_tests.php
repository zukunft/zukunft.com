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

use cfg\const\paths;

include_once paths::MODEL_IMPORT . 'import.php';
include_once paths::MODEL_IMPORT . 'convert_wikipedia_table.php';
include_once paths::MODEL_CONST . 'files.php';
include_once paths::MODEL_SANDBOX . 'sandbox_named.php';
include_once paths::MODEL_SANDBOX . 'sandbox_link_named.php';
include_once TEST_CONST_PATH . 'files.php';

use cfg\component\component;
use cfg\formula\formula;
use cfg\group\group;
use cfg\helper\type_object;
use cfg\import\import_file;
use cfg\ref\ref;
use cfg\ref\source;
use cfg\sandbox\sandbox_link_named;
use cfg\sandbox\sandbox_named;
use cfg\sandbox\sandbox_value;
use cfg\user\user;
use cfg\value\value;
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
        global $db_con;

        // start the test section (ts)
        $ts = 'db write import ';
        $t->header($ts);

        $this->assert_import_json_named($t, $ts, new user(),
            users::TEST_USER_NAME, users::TEST_USER_COM, test_files::IMPORT_USERS);

        $this->assert_import_json_named($t, $ts, new word($usr),
            words::TEST_ADD, words::TEST_ADD_COM, test_files::IMPORT_WORDS);

        $this->assert_import_json_named($t, $ts, new verb(),
            verbs::TEST_ADD_NAME, verbs::TEST_ADD_COM, test_files::IMPORT_VERBS);


        $this->assert_import_json_named($t, $ts, new triple($usr),
            triples::SYSTEM_TEST_ADD, triples::SYSTEM_TEST_ADD_COM, test_files::IMPORT_TRIPLES);

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
            sources::SYSTEM_TEST_ADD, sources::SYSTEM_TEST_ADD_COM, test_files::IMPORT_SOURCES);

        /*
        $this->assert_import_json_value($t, $ts, new value($usr),
            WORDS::TEST_ADD_VALUE, sources::SYSTEM_TEST_ADD_COM, test_files::IMPORT_VALUES);
        */


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
            formulas::SYSTEM_TEST_ADD, formulas::SYSTEM_TEST_ADD_COM, test_files::IMPORT_FORMULAS );

        $this->assert_import_json_named($t, $ts, new component($usr),
            components::TEST_ADD_NAME, components::TEST_ADD_COM, test_files::IMPORT_COMPONENTS);

        $this->assert_import_json_named($t, $ts, new view($usr),
            views::TEST_ADD_NAME, views::TEST_ADD_COM, test_files::IMPORT_VIEWS);

        $db_con->check_sequences();
    }

    /**
     * test creating a sandbox named object via json import
     * and update and delete it
     *
     * @param test_cleanup $t the test object to collect the test results
     * @param string $ts the test section name just for the log header
     * @param sandbox_named|sandbox_link_named|type_object|user $sbx the named sandbox object e.g. word or type_object for verbs or sandbox_link_named for triples or user
     * @param string $add_name the name of the added test object
     * @param string $description the description used for testing
     * @param string $filename base file name for the create, update and delete json message file
     * @return void the result is documented in the test object $t
     */
    function assert_import_json_named(
        test_cleanup                                      $t,
        string                                            $ts,
        sandbox_named|sandbox_link_named|type_object|user $sbx,
        string                                            $add_name,
        string                                            $description,
        string                                            $filename
    ): void
    {
        global $usr;

        $lib = new library();
        $imf = new import_file();

        $name = $lib->class_to_name($sbx::class);
        $t->subheader($ts . $name);

        $test_name = 'import the test ' . $name;
        $imp_msg = $imf->json_file($filename . test_files::JSON, $usr, false);
        $t->assert_true($test_name, $imp_msg->is_ok());
        $test_name = 'test if the ' . $name . ' has been added to the database';
        $sbx->load_by_name($add_name);
        $t->assert_greater_zero($test_name, $sbx->id());

        $test_name = 'add the description to the test ' . $name . ' via import';
        $imp_msg = $imf->json_file($filename . test_files::IMPORT_UPDATE_EXT . test_files::JSON, $usr, false);
        $t->assert_true($test_name, $imp_msg->is_ok());
        $test_name = 'test if the description has been added in the database';
        $sbx->load_by_name($add_name);
        $t->assert($test_name, $sbx->description(), $description);

        $test_name = 'remove the test ' . $name . ' via import';
        $imp_msg = $imf->json_file($filename . test_files::IMPORT_UNDO_EXT . test_files::JSON, $usr, false);
        $t->assert_true($test_name, $imp_msg->is_ok());
        $test_name = 'test if the test ' . $name . ' has been deleted from the database';
        $sbx->load_by_name($add_name);
        if ($sbx::class != verb::class) {
            // TODO prio 3 maybe activate also for verbs but at least should be activated for normal sandbox objects
            $t->assert($test_name, $sbx->id(), 0);
        }

        $test_name = 'remove the test ' . $name . ' directly as fallback to cleanup the database';
        $sbx->load_by_name($add_name);
        if ($sbx->id() > 0) {
            if ($sbx::class == verb::class) {
                $sbx->set_user($usr);
            }
            $sbx->del();
        }
        $sbx->load_by_name($add_name);
        $t->assert($test_name, $sbx->id(), 0);
    }

    /**
     * test creating a sandbox named object via json import
     * and update and delete it
     *
     * @param test_cleanup $t the test object to collect the test results
     * @param string $ts the test section name just for the log header
     * @param sandbox_value $sbx the value or result sandbox object
     * @param string $add_name the name of the added test object
     * @param string $description the description used for testing
     * @param string $filename base file name for the create, update and delete json message file
     * @return void the result is documented in the test object $t
     */
    function assert_import_json_value(
        test_cleanup  $t,
        string        $ts,
        sandbox_value $sbx,
        string        $add_name,
        string        $description,
        string                                            $filename
    ): void
    {
        global $usr;

        $lib = new library();
        $imf = new import_file();

        $name = $lib->class_to_name($sbx::class);
        $t->subheader($ts . $name);

        $test_name = 'import the test ' . $name;
        $imp_msg = $imf->json_file($filename, $usr, false);
        $t->assert_true($test_name, $imp_msg->is_ok());
        $test_name = 'test if the ' . $name . ' has been added to the database';
        $sbx->load_by_names([$add_name]);
        $t->assert_greater_zero($test_name, $sbx->id());

        $test_name = 'add the description to the test ' . $name . ' via import';
        $imp_msg = $imf->json_file($filename . test_files::IMPORT_UPDATE_EXT, $usr, false);
        $t->assert_true($test_name, $imp_msg->is_ok());
        $test_name = 'test if the description has been added in the database';
        $sbx->load_by_names([$add_name]);
        $t->assert($test_name, $sbx->description(), $description);

        $test_name = 'remove the test ' . $name . ' via import';
        $imp_msg = $imf->json_file($filename . test_files::IMPORT_UNDO_EXT, $usr, false);
        $t->assert_true($test_name, $imp_msg->is_ok());
        $test_name = 'test if the test ' . $name . ' has been deleted from the database';
        $sbx->load_by_names([$add_name]);
        $t->assert($test_name, $sbx->id(), 0);

        $test_name = 'remove the test ' . $name . ' directly as fallback to cleanup the database';
        $sbx->load_by_names([$add_name]);
        if ($sbx->id() > 0) {
            $sbx->del();
        }
        $sbx->load_by_names([$add_name]);
        $t->assert($test_name, $sbx->id(), 0);
    }

}