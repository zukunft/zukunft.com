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
include_once TEST_CONST_PATH . 'files.php';

use cfg\component\component;
use cfg\formula\formula;
use cfg\import\import_file;
use cfg\ref\ref;
use cfg\ref\source;
use cfg\user\user;
use cfg\verb\verb;
use cfg\word\triple;
use cfg\word\word;
use shared\const\components;
use shared\const\formulas;
use shared\const\refs;
use shared\const\sources;
use shared\const\triples;
use shared\const\users;
use shared\const\words;
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


        $t->subheader($ts . 'user');

        $test_name = 'import the test user';
        $imp_msg = $imf->json_file(test_files::IMPORT_USERS, $usr, false);
        $t->assert_true($test_name, $imp_msg->is_ok());
        $test_name = 'test if the user has been added to the database';
        $usr_add = new user();
        $usr_add->load_by_name(users::TEST_USER_NAME);
        $t->assert_greater_zero($test_name, $usr_add->id());

        $test_name = 'add the description to the test user via import';
        $imp_msg = $imf->json_file(test_files::IMPORT_USERS_UPDATE, $usr, false);
        $t->assert_true($test_name, $imp_msg->is_ok());
        $test_name = 'test if the description has been added in the database';
        $usr_add = new user();
        $usr_add->load_by_name(users::TEST_USER_NAME);
        $t->assert($test_name, $usr_add->description, users::TEST_USER_COM);

        $test_name = 'remove the test user via import';
        $imp_msg = $imf->json_file(test_files::IMPORT_USERS_UNDO, $usr, false);
        $t->assert_true($test_name, $imp_msg->is_ok());
        $test_name = 'test if the test user has been deleted from the database';
        $usr_add = new user();
        $usr_add->load_by_name(users::TEST_USER_NAME);
        $t->assert($test_name, $usr_add->id(), 0);

        $test_name = 'remove the test user directly as fallback to cleanup the database';
        $usr_add = new user();
        $usr_add->load_by_name(users::TEST_USER_NAME);
        if ($usr_add->id() > 0) {
            $usr_add->del($usr);
        }
        $usr_add = new user();
        $usr_add->load_by_name(users::TEST_USER_NAME);
        $t->assert($test_name, $usr_add->id(), 0);


        $t->subheader($ts . 'word');

        $test_name = 'import the test word';
        $imp_msg = $imf->json_file(test_files::IMPORT_WORDS, $usr, false);
        $t->assert_true($test_name, $imp_msg->is_ok());
        $test_name = 'test if the word has been added to the database';
        $wrd = new word($usr);
        $wrd->load_by_name(words::TEST_ADD);
        $t->assert_greater_zero($test_name, $wrd->id());

        $test_name = 'add the description to the test word via import';
        $imp_msg = $imf->json_file(test_files::IMPORT_WORDS_UPDATE, $usr, false);
        $t->assert_true($test_name, $imp_msg->is_ok());
        $test_name = 'test if the description has been added in the database';
        $wrd = new word($usr);
        $wrd->load_by_name(words::TEST_ADD);
        $t->assert($test_name, $wrd->description(), words::TEST_ADD_COM);

        $test_name = 'remove the test word via import';
        $imp_msg = $imf->json_file(test_files::IMPORT_WORDS_UNDO, $usr, false);
        $t->assert_true($test_name, $imp_msg->is_ok());
        $test_name = 'test if the test word has been deleted from the database';
        $wrd = new word($usr);
        $wrd->load_by_name(words::TEST_ADD);
        $t->assert($test_name, $wrd->id(), 0);

        $test_name = 'remove the test word directly as fallback to cleanup the database';
        $wrd = new word($usr);
        $wrd->load_by_name(words::TEST_ADD);
        if ($wrd->id() > 0) {
            $wrd->del();
        }
        $wrd = new word($usr);
        $wrd->load_by_name(words::TEST_ADD);
        $t->assert($test_name, $wrd->id(), 0);


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


        $t->subheader($ts . 'triple');

        $test_name = 'import the test triple';
        $imp_msg = $imf->json_file(test_files::IMPORT_TRIPLES, $usr, false);
        $t->assert_true($test_name, $imp_msg->is_ok());
        $test_name = 'test if the triple has been added to the database';
        $trp = new triple($usr);
        $trp->load_by_name(triples::SYSTEM_TEST_ADD);
        $t->assert_greater_zero($test_name, $trp->id());

        $test_name = 'add the description to the test triple via import';
        $imp_msg = $imf->json_file(test_files::IMPORT_TRIPLES_UPDATE, $usr, false);
        $t->assert_true($test_name, $imp_msg->is_ok());
        $test_name = 'test if the description has been added in the database';
        $trp = new triple($usr);
        $trp->load_by_name(triples::SYSTEM_TEST_ADD);
        $t->assert($test_name, $trp->description(), triples::SYSTEM_TEST_ADD_COM);

        $test_name = 'remove the test triple via import';
        $imp_msg = $imf->json_file(test_files::IMPORT_TRIPLES_UNDO, $usr, false);
        $t->assert_true($test_name, $imp_msg->is_ok());
        $test_name = 'test if the test triple has been deleted from the database';
        $trp = new triple($usr);
        $trp->load_by_name(triples::SYSTEM_TEST_ADD);
        $t->assert($test_name, $trp->id(), 0);

        $test_name = 'remove the test triple directly as fallback to cleanup the database';
        $trp = new triple($usr);
        $trp->load_by_name(triples::SYSTEM_TEST_ADD);
        if ($trp->id() > 0) {
            $trp->del();
        }
        $trp = new triple($usr);
        $trp->load_by_name(triples::SYSTEM_TEST_ADD);
        $t->assert($test_name, $trp->id(), 0);

        $test_name = 'remove the test word and word to directly as fallback to cleanup the database';
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


        $t->subheader($ts . 'source');

        $test_name = 'import the test source';
        $imp_msg = $imf->json_file(test_files::IMPORT_SOURCES, $usr, false);
        $t->assert_true($test_name, $imp_msg->is_ok());
        $test_name = 'test if the source has been added to the database';
        $src = new source($usr);
        $src->load_by_name(sources::SYSTEM_TEST_ADD);
        $t->assert_greater_zero($test_name, $src->id());

        $test_name = 'add the description to the test source via import';
        $imp_msg = $imf->json_file(test_files::IMPORT_SOURCES_UPDATE, $usr, false);
        $t->assert_true($test_name, $imp_msg->is_ok());
        $test_name = 'test if the description has been added in the database';
        $src = new source($usr);
        $src->load_by_name(sources::SYSTEM_TEST_ADD);
        $t->assert($test_name, $src->description(), sources::SYSTEM_TEST_ADD_COM);

        $test_name = 'remove the test source via import';
        $imp_msg = $imf->json_file(test_files::IMPORT_SOURCES_UNDO, $usr, false);
        $t->assert_true($test_name, $imp_msg->is_ok());
        $test_name = 'test if the test source has been deleted from the database';
        $src = new source($usr);
        $src->load_by_name(sources::SYSTEM_TEST_ADD);
        $t->assert($test_name, $src->id(), 0);

        $test_name = 'remove the test source directly as fallback to cleanup the database';
        $src = new source($usr);
        $src->load_by_name(sources::SYSTEM_TEST_ADD);
        if ($src->id() > 0) {
            $src->del();
        }
        $src = new source($usr);
        $src->load_by_name(sources::SYSTEM_TEST_ADD);
        $t->assert($test_name, $src->id(), 0);


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


        $t->subheader($ts . 'formula');

        $test_name = 'import the test formula';
        $imp_msg = $imf->json_file(test_files::IMPORT_FORMULAS, $usr, false);
        $t->assert_true($test_name, $imp_msg->is_ok());
        $test_name = 'test if the formula has been added to the database';
        $frm = new formula($usr);
        $frm->load_by_name(formulas::SYSTEM_TEST_ADD);
        $t->assert_greater_zero($test_name, $frm->id());

        $test_name = 'add the description to the test formula via import';
        $imp_msg = $imf->json_file(test_files::IMPORT_FORMULAS_UPDATE, $usr, false);
        $t->assert_true($test_name, $imp_msg->is_ok());
        $test_name = 'test if the description has been added in the database';
        $frm = new formula($usr);
        $frm->load_by_name(formulas::SYSTEM_TEST_ADD);
        $t->assert($test_name, $frm->description(), formulas::SYSTEM_TEST_ADD_COM);

        $test_name = 'remove the test formula via import';
        $imp_msg = $imf->json_file(test_files::IMPORT_FORMULAS_UNDO, $usr, false);
        $t->assert_true($test_name, $imp_msg->is_ok());
        $test_name = 'test if the test formula has been deleted from the database';
        $frm = new formula($usr);
        $frm->load_by_name(formulas::SYSTEM_TEST_ADD);
        $t->assert($test_name, $frm->id(), 0);

        $test_name = 'remove the test formula directly as fallback to cleanup the database';
        $frm = new formula($usr);
        $frm->load_by_name(formulas::SYSTEM_TEST_ADD);
        if ($frm->id() > 0) {
            $frm->del();
        }
        $frm = new formula($usr);
        $frm->load_by_name(formulas::SYSTEM_TEST_ADD);
        $t->assert($test_name, $frm->id(), 0);


        $t->subheader($ts . 'component');

        $test_name = 'import the test component';
        $imp_msg = $imf->json_file(test_files::IMPORT_COMPONENTS, $usr, false);
        $t->assert_true($test_name, $imp_msg->is_ok());
        $test_name = 'test if the component has been added to the database';
        $frm = new component($usr);
        $frm->load_by_name(components::TEST_ADD_NAME);
        $t->assert_greater_zero($test_name, $frm->id());

        $test_name = 'add the description to the test component via import';
        $imp_msg = $imf->json_file(test_files::IMPORT_COMPONENTS_UPDATE, $usr, false);
        $t->assert_true($test_name, $imp_msg->is_ok());
        $test_name = 'test if the description has been added in the database';
        $frm = new component($usr);
        $frm->load_by_name(components::TEST_ADD_NAME);
        $t->assert($test_name, $frm->description(), components::TEST_ADD_COM);

        $test_name = 'remove the test component via import';
        $imp_msg = $imf->json_file(test_files::IMPORT_COMPONENTS_UNDO, $usr, false);
        $t->assert_true($test_name, $imp_msg->is_ok());
        $test_name = 'test if the test component has been deleted from the database';
        $frm = new component($usr);
        $frm->load_by_name(components::TEST_ADD_NAME);
        $t->assert($test_name, $frm->id(), 0);

        $test_name = 'remove the test component directly as fallback to cleanup the database';
        $frm = new component($usr);
        $frm->load_by_name(components::TEST_ADD_NAME);
        if ($frm->id() > 0) {
            $frm->del();
        }
        $frm = new component($usr);
        $frm->load_by_name(components::TEST_ADD_NAME);
        $t->assert($test_name, $frm->id(), 0);

    }

}