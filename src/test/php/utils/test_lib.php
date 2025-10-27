<?php

/*

    test_lib.php - general functions used in several unit, read or write tests
    ------------

    TODO move all test resource file reading to this class
    TODO create a update_test_result function to overwrite a test file if confirmed by a developer


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

    Copyright (c) 1995-2025 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\test\php\utils;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::SANDBOX . 'list_dsp.php';
include_once html_paths::USER . 'user.php';
include_once html_paths::VIEW . 'view_list.php';
include_once paths::MODEL_IMPORT . 'import.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_CONST . 'files.php';
include_once test_paths::CONST . 'files.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_list;
use Zukunft\ZukunftCom\main\php\cfg\log\change_log_list;
use Zukunft\ZukunftCom\main\php\cfg\value\value_list;
use Zukunft\ZukunftCom\main\php\cfg\component\component;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_object_seq_id;
use Zukunft\ZukunftCom\main\php\cfg\helper\type_list;
use Zukunft\ZukunftCom\main\php\cfg\ref\ref;
use Zukunft\ZukunftCom\main\php\cfg\ref\ref_list;
use Zukunft\ZukunftCom\main\php\cfg\ref\source;
use Zukunft\ZukunftCom\main\php\cfg\result\result;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_list;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_value;
use Zukunft\ZukunftCom\main\php\cfg\system\base_list;
use Zukunft\ZukunftCom\main\php\cfg\value\value;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb;
use Zukunft\ZukunftCom\main\php\cfg\view\view;
use Zukunft\ZukunftCom\main\php\cfg\view\view_list;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\cfg\word\triple_list;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\cfg\word\word_list;
use Zukunft\ZukunftCom\main\php\cfg\import\import;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message as backend_user_message;
use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\main\php\shared\enum\user_profiles;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\web\component\component_exe as component_ui;
use Zukunft\ZukunftCom\main\php\web\formula\formula as formula_ui;
use Zukunft\ZukunftCom\main\php\web\formula\formula_list as formula_list_ui;
use Zukunft\ZukunftCom\main\php\web\helper\data_object as data_object_ui;
use Zukunft\ZukunftCom\main\php\web\log\change_log_list as change_log_list_ui;
use Zukunft\ZukunftCom\main\php\web\ref\ref as ref_ui;
use Zukunft\ZukunftCom\main\php\web\ref\ref_list as ref_list_ui;
use Zukunft\ZukunftCom\main\php\web\ref\source as source_ui;
use Zukunft\ZukunftCom\main\php\web\result\result as result_ui;
use Zukunft\ZukunftCom\main\php\web\sandbox\db_object as db_object_ui;
use Zukunft\ZukunftCom\main\php\web\types\type_lists;
use Zukunft\ZukunftCom\main\php\web\user\user as user_ui;
use Zukunft\ZukunftCom\main\php\web\sandbox\list_dsp as list_ui;
use Zukunft\ZukunftCom\main\php\web\value\value as value_ui;
use Zukunft\ZukunftCom\main\php\web\value\value_list as value_list_ui;
use Zukunft\ZukunftCom\main\php\web\verb\verb as verb_ui;
use Zukunft\ZukunftCom\main\php\web\view\view as view_ui;
use Zukunft\ZukunftCom\main\php\web\view\view_list as view_list_ui;
use Zukunft\ZukunftCom\main\php\shared\const\files;
use Zukunft\ZukunftCom\main\php\web\word\triple as triple_ui;
use Zukunft\ZukunftCom\main\php\web\word\word as word_ui;
use Zukunft\ZukunftCom\main\php\web\word\word_list as word_list_ui;
use Zukunft\ZukunftCom\main\php\web\word\triple_list as triple_list_ui;
use Zukunft\ZukunftCom\test\php\const\files as test_files;
use Zukunft\ZukunftCom\test\php\create\test_formulas;
use Zukunft\ZukunftCom\test\php\create\test_log;
use Zukunft\ZukunftCom\test\php\create\test_refs;
use Zukunft\ZukunftCom\test\php\create\test_triples;
use Zukunft\ZukunftCom\test\php\create\test_values;
use Zukunft\ZukunftCom\test\php\create\test_words;

class test_lib
{

    /**
     * cast a backend user to a frontend user
     * @param user $usr the filled backend user object
     * @return user_ui the filled frontend user object
     */
    function cast_user(user $usr): user_ui
    {
        $usr_dsp = new user_ui();
        $usr_dsp->set_from_json($usr->api_json());
        return $usr_dsp;
    }

    function cast_view_list(view_list $msk_lst): view_list_ui
    {
        $api_msg = $msk_lst->api_json();
        return new view_list_ui($api_msg);
    }

    /**
     * create and frontend cache and fill it with all entries for the unit tests
     * TODO use it for the global ui_cac var
     * @param user $usr the user for which the sample cache should be created
     * @param test_cleanup $t the test environment e.g. the collect the errors
     * @return data_object_ui
     */
    function ui_test_cache(user $usr, test_cleanup $t): data_object_ui
    {
        global $ui_cac;

        $dto_dsp = new data_object_ui();
        $dto_dsp->usr = $this->cast_user($usr);

        // load type lists from resource json file
        $api_msg = file_get_contents(test_files::TYPE_LISTS_CACHE);
        $dto_dsp->typ_lst_cache = new type_lists($api_msg);

        // import system views from resource json file
        $imp = new import();
        $imp->usr = $usr;
        $json_str = file_get_contents(files::SYSTEM_VIEWS);
        $size = strlen($json_str);
        $json_array = json_decode($json_str, true);
        $usr_msg = new backend_user_message();
        $dto = $imp->get_data_object($json_array, $usr_msg, $size);
        $dto_dsp->set_view_list($this->cast_view_list($dto->view_list()));

        // TODO Prio 2 separate the test object creation from the test object class because this is not depending on the test object settings
        $t_wrd = new test_words($t);
        $t_trp = new test_triples($t);
        $t_ref = new test_refs($t);
        $t_val = new test_values($t);
        $t_frm = new test_formulas($t);
        $t_log = new test_log($t);

        // set the value cache list based
        $dto_dsp->wrd_lst = $t_wrd->word_list_ui();
        $dto_dsp->trp_lst = $t_trp->triple_list_ui();
        $dto_dsp->ref_lst = $t_ref->ref_list_math_ui();
        $dto_dsp->val_lst = $t_val->value_list_math_ui();
        $dto_dsp->frm_lst = $t_frm->formula_list_ui();
        $dto_dsp->chg_log = $t_log->log_list_named_ui();

        // set the global cache var
        $ui_cac = $dto_dsp;

        return $dto_dsp;
    }

    /**
     * cast a backend list to a frontend list via api message
     * @param sandbox_list|type_list|change_log_list $lst the filled backend list
     * @param api_type_list|array $typ_lst configuration for the api message e.g. if phrases should be included
     * @return word_list_ui|triple_list_ui|ref_list_ui|value_list_ui|formula_list_ui|change_log_list_ui|list_ui
     */
    function list_to_ui(
        sandbox_list|type_list|change_log_list $lst,
        api_type_list|array                    $typ_lst = []
    ): word_list_ui|triple_list_ui|ref_list_ui|value_list_ui|formula_list_ui|change_log_list_ui|list_ui
    {
        $tl = new test_lib();
        $lst_ui = $tl->obj_to_ui_obj($lst);
        $api_json = $lst->api_json($typ_lst);
        $lst_ui->set_from_json($api_json);
        return $lst_ui;
    }

    /**
     * get the frontend object related to the given backend object
     * @param db_object_seq_id|sandbox_value|base_list|type_list $dbo the given backend object
     * @return false|db_object_ui|list_ui the corresponding frontend object
     */
    public function obj_to_ui_obj(
        db_object_seq_id|sandbox_value|base_list|type_list $dbo
    ): false|db_object_ui|list_ui
    {
        return match ($dbo::class) {
            word::class => new word_ui(),
            verb::class => new verb_ui(),
            triple::class => new triple_ui(),
            source::class => new source_ui(),
            ref::class => new ref_ui(),
            value::class => new value_ui(),
            //group::class => new group_ui(),
            formula::class => new formula_ui(),
            result::class => new result_ui(),
            view::class => new view_ui(),
            component::class => new component_ui(),
            user::class => new user_ui(),
            word_list::class => new word_list_ui(),
            triple_list::class => new triple_list_ui(),
            value_list::class => new value_list_ui(),
            ref_list::class => new ref_list_ui(),
            formula_list::class => new formula_list_ui(),
            change_log_list::class => new change_log_list_ui(),
            default => false,
        };
    }

    /**
     * set the all values of the frontend object based on a backend object using the api object
     * @param object $model_obj the frontend object with the values of the backend object
     */
    function dsp_obj(object $model_obj, object $dsp_obj, bool $do_save = true): object
    {
        $api_json = $model_obj->api_json();
        $dsp_obj->set_from_json($api_json);
        return $dsp_obj;
    }

    /**
     * just to test the database abstraction layer, but without real connection to any database
     * @return sql_db dummy database connection for internal unit testing
     */
    function unit_test_db_con(): sql_db
    {
        $db_con = new sql_db();
        $db_con->db_type = SQL_DB_TYPE;
        return $db_con;
    }

    /*
     * TODO Prio 0 review
     */

    /**
     * create the dummy users for internal unit testing
     * @return user the normal test user
     */
    function users_for_unit_tests(): user
    {
        global $usr_sys;
        global $usr;

        // create a dummy system user for unit testing
        $usr_sys = new user;
        $usr_sys->id = users::SYSTEM_ID;
        $usr_sys->name = users::SYSTEM_NAME;

        // create a dummy user for testing
        $usr = new user;
        $usr->id = users::SYSTEM_TEST_ID;
        $usr->name = users::SYSTEM_TEST_NAME;
        $usr->set_profile(user_profiles::EMAIL);

        return $usr;
    }



}
