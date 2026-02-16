<?php

/*

    test/unit/expression.php - unit testing of the helper objects
    ------------------------
  

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

    Copyright (c) 1995-2026 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\test\php\unit;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::SHARED_HELPER . 'ListOf.php';
include_once test_paths::CREATE . 'test_words.php';
include_once test_paths::UTILS . 'test_cleanup.php';

use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\helper\ListOfIdNamedCodeObjects;
use Zukunft\ZukunftCom\main\php\shared\helper\ListOfIdNamedObjects;
use Zukunft\ZukunftCom\main\php\shared\helper\ListOfIdObjects;
use Zukunft\ZukunftCom\main\php\shared\helper\ListOf;
use Zukunft\ZukunftCom\main\php\shared\helper\Message;
use Zukunft\ZukunftCom\main\php\shared\types\phrase_types;
use Zukunft\ZukunftCom\test\php\create\test_types;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class base_object_tests
{
    function run(test_cleanup $t): void
    {

        // init
        $t_wrd = new test_words($t);
        $t_typ = new test_types($t);
        $msg = new Message();

        // start the test section (ts)
        $ts = 'unit base object ';
        $t->header($ts);

        $t->subheader($ts . 'list');
        $test_name = 'count';
        $lst = new ListOf([$t_wrd->word(), $t_wrd->word_inhabitant(), $t_wrd->word_inhabitant()]);
        $t->assert($test_name, 3, $lst->count());
        $test_name = 'get first';
        $wrd = $lst->get_by_key(0);
        $t->assert($test_name, $wrd->id(), $t_wrd->word()->id());
        $test_name = 'get not existing';
        $wrd = $lst->get_by_key(4);
        $t->assert_null($test_name, $wrd);
        $test_name = 'report not existing';
        $usr_msg = new user_message();
        $lst->get_by_key(4, $usr_msg);
        $t->assert($test_name, $usr_msg->all_message_text(), '4 is missing in ListOf');
        $test_name = 'add object';
        $lst->add_obj($t_wrd->word_2020());
        $t->assert($test_name, 4, $lst->count());
        $test_name = 'add same object return message';
        $lst->add_obj($t_wrd->word_2020(), false, $msg);
        $t->assert($test_name, $msg->text(), 'trying to add ""2020" (word_id 140) for user 1 (zukunft.com system test)" which is already part of the Zukunft\ZukunftCom\main\php\cfg\word\word list');
        $test_name = 'add duplicate object';
        $lst->add_obj($t_wrd->word_2020(), true);
        $t->assert($test_name, 5, $lst->count());
        $test_name = 'reset';
        $lst->reset();
        $t->assert($test_name, 0, $lst->count());
        $test_name = 'is empty';
        $t->assert_true($test_name, $lst->is_empty());

        $t->subheader($ts . 'id list');
        $test_name = 'count';
        $lst = new ListOfIdObjects([$t_wrd->word(), $t_wrd->word_inhabitant(), $t_wrd->word_inhabitant()]);
        $t->assert($test_name, 3, $lst->count());
        $test_name = 'clone reset also removes the id list';
        $lst_two = $lst->clone_reset();
        $t->assert($test_name, 0, count($lst_two->ids()));
        $test_name = 'but the original id list is still set';
        $t->assert($test_name, 3, count($lst->ids()));
        $test_name = 'check existing id';
        $t->assert_true($test_name, $lst->has_id(words::MATH_ID));
        $test_name = 'check non existing id';
        $t->assert_false($test_name, $lst->has_id(words::YEAR_2026_ID));
        $test_name = 'id list is updated by setting the list';
        $lst->set_lst([$t_wrd->word(), $t_wrd->word_inhabitant(), $t_wrd->word_zh(), $t_wrd->word_2020()]);
        $t->assert($test_name, 4, count($lst->ids()));
        $test_name = 'get returns the object with the requested id';
        $t->assert($test_name, $lst->get(words::YEAR_2020_ID)->id(), words::YEAR_2020_ID);
        $test_name = 'get returns null if id is missing';
        $t->assert_null($test_name, $lst->get(words::YEAR_2021_ID));
        $test_name = 'first object is returned';
        $t->assert($test_name, $lst->get_first_object()->id(), words::MATH_ID);
        $test_name = 'diff can remove first two';
        $del_lst = new ListOfIdObjects([$t_wrd->word(), $t_wrd->word_inhabitant()]);
        $t->assert($test_name, $lst->diff($del_lst)->ids(), [words::ZH_ID,words::YEAR_2020_ID]);
        $test_name = 'diff can remove all duplicates';
        $lst = new ListOfIdObjects([$t_wrd->word(), $t_wrd->word_inhabitant(), $t_wrd->word_inhabitant()]);
        $del_lst = new ListOfIdObjects([$t_wrd->word_inhabitant()]);
        $lst = $lst->diff($del_lst);
        $t->assert($test_name, $lst->ids(), [words::MATH_ID]);
        $test_name = 'add object';
        $lst->add_obj($t_wrd->word_2020());
        $t->assert($test_name, 2, $lst->count());
        $test_name = 'add same object return message';
        $lst->add_obj($t_wrd->word_2020(), false, $msg);
        $t->assert($test_name, $msg->text(), 'trying to add ""2020" (word_id 140) for user 1 (zukunft.com system test)" which is already part of the Zukunft\ZukunftCom\main\php\cfg\word\word list');
        $test_name = 'add duplicate object';
        $lst->add_obj($t_wrd->word_2020(), true);
        $t->assert($test_name, 3, $lst->count());
        $test_name = 'unset by id removes all objects with the id';
        $lst->unset_by_id(words::YEAR_2020_ID);
        $t->assert($test_name, 1, $lst->count());
        $test_name = 'unset on non existing id does not change the list';
        $lst->unset_by_id(words::INHABITANT_ID);
        $t->assert($test_name, 1, $lst->count());
        $test_name = 'unset by id can empty the list';
        $lst->unset_by_id(words::MATH_ID);
        $t->assert_true($test_name, $lst->is_empty());

        $t->subheader($ts . 'name list');
        $test_name = 'count';
        $lst = new ListOfIdNamedObjects([$t_wrd->word(), $t_wrd->word_inhabitant(), $t_wrd->word_inhabitant()]);
        $t->assert($test_name, 3, $lst->count());
        $test_name = 'clone reset also removes the name list';
        $lst_two = $lst->clone_reset();
        $t->assert($test_name, 0, count($lst_two->names()));
        $test_name = 'but the original name list is still set';
        $t->assert($test_name, 3, count($lst->names()));
        $test_name = 'check existing name';
        $t->assert_true($test_name, $lst->has_name(words::MATH));
        $test_name = 'check non existing name';
        $t->assert_false($test_name, $lst->has_name(words::YEAR_2026));
        $test_name = 'name list is updated by setting the list';
        $lst->set_lst([$t_wrd->word(), $t_wrd->word_inhabitant(), $t_wrd->word_zh(), $t_wrd->word_2020()]);
        $t->assert($test_name, 4, count($lst->names()));
        $test_name = 'get returns the object with the requested named object';
        $t->assert($test_name, $lst->get_by_name(words::YEAR_2020)->id(), words::YEAR_2020_ID);
        $test_name = 'get returns null if name is missing';
        $t->assert_null($test_name, $lst->get_by_name(words::YEAR_2021));
        $test_name = 'id_by_name returns the id from object name';
        $t->assert($test_name, $lst->id_by_name(words::YEAR_2020), words::YEAR_2020_ID);
        $test_name = 'id_by_name returns zero if name is missing';
        $t->assert($test_name, $lst->id_by_name(words::YEAR_2021), 0);
        $test_name = 'add_obj_by_name can add objects without id';
        $wrd_no_id = $t_wrd->word();
        $wrd_no_id->id = 0;
        $wrd2_no_id = $t_wrd->word_inhabitant();
        $wrd2_no_id->id = 0;
        $lst = new ListOfIdNamedObjects();
        $lst->add_obj_by_name($wrd_no_id);
        $lst->add_obj_by_name($wrd2_no_id);
        $t->assert($test_name, 2, $lst->count());
        $test_name = 'add_obj_by_name does not add object with same name but without id';
        $lst->add_obj_by_name($wrd2_no_id);
        $t->assert($test_name, 2, $lst->count());
        $test_name = 'add_obj_by_name does add object with same name but without id if requested';
        $lst->add_obj_by_name($wrd2_no_id, true);
        $t->assert($test_name, 3, $lst->count());
        $test_name = 'diff can remove all duplicates even if the id is missing';
        $del_lst = new ListOfIdNamedObjects([$wrd2_no_id]);
        $lst = $lst->diff_by_name($del_lst);
        $t->assert($test_name, $lst->names(), [words::MATH]);
        $test_name = 'unset by name can empty the list';
        $lst->unset_by_name(words::MATH);
        $t->assert_true($test_name, $lst->is_empty());

        $t->subheader($ts . 'code id list');
        $test_name = 'count';
        $lst = new ListOfIdNamedCodeObjects([$t_typ->phrase_type(), $t_typ->phrase_type_measure(), $t_typ->phrase_type_measure(), $t_typ->phrase_type_time()]);
        $t->assert($test_name, 4, $lst->count());
        $test_name = 'clone reset also removes the code_id list';
        $lst_two = $lst->clone_reset();
        $t->assert($test_name, 0, count($lst_two->code_ids()));
        $test_name = 'but the original name list is still set';
        $t->assert($test_name, 4, count($lst->code_ids()));
        $test_name = 'and the original name list also is still set';
        $t->assert($test_name, 4, count($lst->names()));
        $test_name = 'check existing code_id';
        $t->assert_true($test_name, $lst->has_code_id(phrase_types::NORMAL));
        $test_name = 'check existing name';
        $t->assert_true($test_name, $lst->has_name(phrase_types::NORMAL_NAME));
        $test_name = 'check non existing code_id';
        $t->assert_false($test_name, $lst->has_code_id(phrase_types::CALC));
        $test_name = 'check non existing name';
        $t->assert_false($test_name, $lst->has_name(phrase_types::CALC_NAME));
        $test_name = 'code_id list is updated by setting the list';
        $lst->set_lst([$t_typ->phrase_type_time(), $t_typ->phrase_type_measure(), $t_typ->phrase_type_scaling(), $t_typ->phrase_type_scaling()]);
        $t->assert($test_name, 4, count($lst->code_ids()));
        $test_name = 'and also the name list';
        $t->assert($test_name, 4, count($lst->names()));
        $test_name = 'and also the id list';
        $t->assert($test_name, 4, count($lst->ids()));
        $test_name = 'get returns the object with the object with the requested code id and id';
        $t->assert($test_name, $lst->get_by_code_id(phrase_types::SCALING)->id(), phrase_types::SCALING_ID);
        $test_name = 'get by name returns the object with the requested named object';
        $t->assert($test_name, $lst->get_by_name(phrase_types::SCALING_NAME)->id(), phrase_types::SCALING_ID);
        $test_name = 'get returns null if code_id is missing';
        $t->assert_null($test_name, $lst->get_by_code_id(phrase_types::CALC));
        $test_name = 'id_by_code_id returns the id from object name';
        $t->assert($test_name, $lst->id_by_code_id(phrase_types::MEASURE), phrase_types::MEASURE_ID);
        $test_name = 'id_by_code_id returns zero if name is missing';
        $t->assert($test_name, $lst->id_by_name(phrase_types::CALC), 0);
        $test_name = 'add_obj_by_name can add objects without id';
        $typ_no_id = $t_typ->phrase_type();
        $typ_no_id->id = 0;
        $typ2_no_id = $t_typ->phrase_type_measure();
        $typ2_no_id->id = 0;
        $lst = new ListOfIdNamedCodeObjects();
        $lst->add_obj_by_code_id($typ_no_id);
        $lst->add_obj_by_code_id($typ2_no_id);
        $t->assert($test_name, 2, $lst->count());
        $test_name = 'add_obj_by_code_id does not add object with same code_id but without id';
        $lst->add_obj_by_code_id($typ2_no_id);
        $t->assert($test_name, 2, $lst->count());
        $test_name = 'add_obj_by_name does not add object with same name but without id';
        $lst->add_obj_by_name($typ2_no_id);
        $t->assert($test_name, 2, $lst->count());
        $test_name = 'add_obj_by_code_id does not add object with same code_id but without name and id';
        $typ2_no_id->name = '';
        $lst->add_obj_by_code_id($typ2_no_id);
        $t->assert($test_name, 2, $lst->count());
        $test_name = 'check that a type with a different code_id, but the same name cannot be added to the list';
        $typ2_no_id = $t_typ->phrase_type_measure();
        $typ2_no_id->id = 0;
        $typ2_no_id->code_id = 'new code_id';
        $lst->add_obj_by_code_id($typ2_no_id);
        $t->assert($test_name, 2, $lst->count());
        $test_name = 'check that a type with a different name, but the same code_id cannot be added to the list';
        $typ2_no_id = $t_typ->phrase_type_measure();
        $typ2_no_id->id = 0;
        $typ2_no_id->name = 'new name';
        $lst->add_obj_by_code_id($typ2_no_id);
        $t->assert($test_name, 2, $lst->count());
        $test_name = 'add_obj_by_code_id does add object with same name but without id if requested';
        $lst->add_obj_by_code_id($typ2_no_id, true);
        $t->assert($test_name, 3, $lst->count());
        $test_name = 'diff can remove all duplicates even if the id is missing';
        $del_lst = new ListOfIdNamedCodeObjects([$typ2_no_id]);
        $lst = $lst->diff_by_name($del_lst);
        $t->assert($test_name, $lst->names(), [phrase_types::NORMAL_NAME]);
        $test_name = 'unset by name can empty the list';
        $lst->unset_by_name(phrase_types::NORMAL_NAME);
        $t->assert_true($test_name, $lst->is_empty());



        // TODO Prio 0 add add and unset tests

        // TODO Prio 2 add tests fpr CombineObject, Config, IdObject, ListOfIdObjects, MapObjects, TextIdObjects, Translator and Workflow


    }

}