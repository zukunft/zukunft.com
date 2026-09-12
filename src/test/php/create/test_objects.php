<?php

/*

    test/create/test_objects.php - parent for the test object creators
    ----------------------------


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

namespace Zukunft\ZukunftCom\test\php\create;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::MODEL_CONST . 'def.php';
include_once paths::MODEL_HELPER . 'type_object.php';
include_once paths::MODEL_GROUP . 'group.php';
include_once paths::MODEL_REF . 'ref.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::MODEL_VERB . 'verb.php';
include_once paths::MODEL_PHRASE . 'phrase.php';
include_once paths::MODEL_PHRASE . 'phrase_list.php';
include_once paths::MODEL_SANDBOX . 'sandbox_link_named.php';
include_once paths::MODEL_SANDBOX . 'sandbox_multi.php';
include_once paths::MODEL_SANDBOX . 'sandbox_named.php';
include_once paths::SHARED . 'library.php';
include_once test_paths::UTILS . 'test_cleanup.php';

use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\main\php\cfg\helper\type_object;
use Zukunft\ZukunftCom\main\php\cfg\group\group;
use Zukunft\ZukunftCom\main\php\cfg\ref\ref;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_link_named;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_multi;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_named;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class test_objects
{

    /*
     * init
     */

    // use the global test environment
    protected test_cleanup $env;

    function __construct(test_cleanup $env)
    {
        $this->env = $env;
    }


    /*
     * cleanup
     */

    /**
     * delete any remaining test objects for a clean test start of each test
     */
    function cleanup_objects(
        string                                                                           $ts,
        array                                                                            $obj_names,
        sandbox_named|sandbox_link_named|sandbox_multi|verb|phrase|ref|group|type_object $obj
    ): void
    {
        $msg = new user_message();
        $lib = new library();
        $class = $lib->class_to_name($obj::class);
        $this->env->subheader($ts . 'cleanup ' . $class);
        // for a named object or a type row remove the change log entries of the test rows before the
        // rows themselves are deleted below, so that no change log entry keeps pointing to a deleted test row
        if (in_array($obj::class, def::NAME_CLASSES) or $obj instanceof type_object) {
            $this->env->cleanup_change_log($obj, $obj_names);
        }
        foreach ($obj_names as $obj_name) {
            if (in_array($obj::class, def::NAME_CLASSES)) {
                $this->env->write_named_cleanup($obj, $obj_name);
            } elseif (in_array($obj::class, def::VALUE_CLASSES)) {
                $phr_lst = new phrase_list($obj->get_user());
                $phr_lst->load_by_names($obj_name, $msg);
                // a value is named by its complete group, so a phrase list that misses one name
                // (e.g. because its test word is not created yet or has already been deleted)
                // names another value and must never be deleted
                if ($phr_lst->count() == count($obj_name)) {
                    $grp = new group($obj->get_user());
                    $grp->set_phrase_list($phr_lst);
                    // remove the value's change log before the value is deleted, but only if at least
                    // one phrase of the value is a test row (see cleanup_change_log_value)
                    $this->env->cleanup_change_log_value($grp, $phr_lst);
                    $this->env->write_value_cleanup($obj, $grp);
                }
            } elseif ($obj::class == ref::class) {
                // remove the ref's change log before the ref is deleted, but only if the ref's phrase
                // is a test row (see cleanup_change_log_ref)
                $this->env->cleanup_change_log_ref($obj, $obj_name);
                $this->env->write_named_cleanup($obj, $obj_name);
            } elseif ($obj::class == group::class) {
                // the group test spec is [name, [phrase names]]; remove the group's change log if at
                // least one phrase is a test row (see cleanup_change_log_group)
                $this->env->cleanup_change_log_group($obj, $obj_name);
            } elseif ($obj instanceof type_object) {
                $this->env->write_named_cleanup($obj, $obj_name);
            } else {
                log_err('no cleanup for ' . $class);
            }
        }
    }

}