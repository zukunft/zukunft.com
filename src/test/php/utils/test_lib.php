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

include_once paths::MODEL_IMPORT . 'import.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::MODEL_VIEW . 'view_relation.php';
include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::SANDBOX . 'ListBase.php';
include_once html_paths::USER . 'user.php';
include_once html_paths::VIEW . 'view_list.php';
include_once html_paths::VIEW . 'view_relation.php';
include_once html_paths::VIEW . 'term_view.php';
include_once paths::SHARED_CONST . 'files.php';
include_once test_paths::CONST . 'files.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_list;
use Zukunft\ZukunftCom\main\php\cfg\log\change_log_list;
use Zukunft\ZukunftCom\main\php\cfg\value\value_list;
use Zukunft\ZukunftCom\main\php\cfg\component\component;
use Zukunft\ZukunftCom\main\php\cfg\component\component_link;
use Zukunft\ZukunftCom\main\php\cfg\ref\source_list;
use Zukunft\ZukunftCom\main\php\cfg\view\view_relation;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_link;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_link_list;
use Zukunft\ZukunftCom\main\php\cfg\group\group;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_object_seq_id;
use Zukunft\ZukunftCom\main\php\cfg\helper\type_list;
use Zukunft\ZukunftCom\main\php\cfg\ref\ref;
use Zukunft\ZukunftCom\main\php\cfg\ref\ref_list;
use Zukunft\ZukunftCom\main\php\cfg\ref\source;
use Zukunft\ZukunftCom\main\php\cfg\result\result;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_list;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_multi;
use Zukunft\ZukunftCom\main\php\cfg\system\list_db_read;
use Zukunft\ZukunftCom\main\php\cfg\value\value;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb;
use Zukunft\ZukunftCom\main\php\cfg\view\view;
use Zukunft\ZukunftCom\main\php\cfg\view\view_list;
use Zukunft\ZukunftCom\main\php\cfg\view\term_view;
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
use Zukunft\ZukunftCom\main\php\web\component\component_link as component_link_ui;
use Zukunft\ZukunftCom\main\php\web\formula\formula as formula_ui;
use Zukunft\ZukunftCom\main\php\web\formula\formula_link as formula_link_ui;
use Zukunft\ZukunftCom\main\php\web\formula\formula_list as formula_list_ui;
use Zukunft\ZukunftCom\main\php\web\formula\formula_link_list as formula_link_list_ui;
use Zukunft\ZukunftCom\main\php\web\group\group as group_ui;
use Zukunft\ZukunftCom\main\php\web\helper\data_object as data_object_ui;
use Zukunft\ZukunftCom\main\php\web\log\change_log_list as change_log_list_ui;
use Zukunft\ZukunftCom\main\php\web\ref\ref as ref_ui;
use Zukunft\ZukunftCom\main\php\web\ref\ref_list as ref_list_ui;
use Zukunft\ZukunftCom\main\php\web\ref\source as source_ui;
use Zukunft\ZukunftCom\main\php\web\ref\source_list as source_list_ui;
use Zukunft\ZukunftCom\main\php\web\result\result as result_ui;
use Zukunft\ZukunftCom\main\php\web\sandbox\db_object as db_object_ui;
use Zukunft\ZukunftCom\main\php\web\types\type_lists;
use Zukunft\ZukunftCom\main\php\web\user\user as user_ui;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\sandbox\ListBase as list_ui;
use Zukunft\ZukunftCom\main\php\web\value\value as value_ui;
use Zukunft\ZukunftCom\main\php\web\value\value_list as value_list_ui;
use Zukunft\ZukunftCom\main\php\web\verb\verb as verb_ui;
use Zukunft\ZukunftCom\main\php\web\view\view as view_ui;
use Zukunft\ZukunftCom\main\php\web\view\view_relation as view_relation_ui;
use Zukunft\ZukunftCom\main\php\web\view\term_view as term_view_ui;
use Zukunft\ZukunftCom\main\php\web\view\view_list as view_list_ui;
use Zukunft\ZukunftCom\main\php\web\word\triple as triple_ui;
use Zukunft\ZukunftCom\main\php\web\word\word as word_ui;
use Zukunft\ZukunftCom\main\php\web\word\word_list as word_list_ui;
use Zukunft\ZukunftCom\main\php\web\word\triple_list as triple_list_ui;
use Zukunft\ZukunftCom\test\php\const\files as test_files;
use Zukunft\ZukunftCom\main\php\shared\const\files;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\test\php\create\test_formulas;
use Zukunft\ZukunftCom\test\php\create\test_log;
use Zukunft\ZukunftCom\test\php\create\test_refs;
use Zukunft\ZukunftCom\test\php\create\test_sources;
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
        $usr_ui = new user_ui();
        $usr_ui->set_from_json($usr->api_json(), new user_message());
        return $usr_ui;
    }

    function ui_value(value $val): value_ui
    {
        $api_msg = $val->api_json([api_types::INCL_PHRASES]);
        return new value_ui($api_msg);
    }

    /**
     * get the pure text from a html string
     * @return string the pure text of the html code
     */
    function text_from_html(string $html): string
    {
        $lib = new library();
        $result = $html;
        $code = $lib->str_between($result, '<', '>');
        while ($code != '') {
            $result = str_replace('<' . $code . '>', '', $result);
            $code = $lib->str_between($result, '<', '>');
        }
        return $result;
    }

    function cast_view_list(view_list $msk_lst): view_list_ui
    {
        $api_msg = $msk_lst->api_json();
        return new view_list_ui($api_msg);
    }

    /**
     * create the frontend cache and fill it with all entries for the unit tests
     * TODO use it for the global ui_cac var
     * @param user $usr the user for which the sample cache should be created
     *                  for unit tests a user that is allowed to import code-ids should be used
     * @param test_cleanup $t the test environment e.g. the collect the errors
     * @return data_object_ui
     */
    function ui_test_cache(user $usr, test_cleanup $t): data_object_ui
    {
        global $ui_sys;

        $dto_ui = new data_object_ui();
        $dto_ui->usr = $this->cast_user($usr);
        $dto_base_ui = new data_object_ui();
        $dto_base_ui->usr = $this->cast_user($usr);

        // load type lists from resource json file
        $api_msg = file_get_contents(test_files::TYPE_LISTS_CACHE);
        $dto_ui->typ_lst_cache = new type_lists($api_msg);

        // import system views from resource json file so that not all details need to be repeated in the test data creation class
        $imp = new import();
        $imp->usr = $usr;
        $json_str = file_get_contents(files::SYSTEM_VIEWS);
        $size = strlen($json_str);
        $json_array = json_decode($json_str, true);
        $usr_msg = new backend_user_message($usr);
        $dto = $imp->get_data_object($json_array, $usr_msg, $size);
        $dto_ui->set_view_list($this->cast_view_list($dto->view_list()));
        // add the view id because the import does not include the database id
        $dto_ui->add_id_to_views();
        // add the components to the views
        //$dto_ui->add_components_to_views();
        // import the base views
        $json_str = file_get_contents(files::BASE_VIEWS);
        $size = strlen($json_str);
        $json_array = json_decode($json_str, true);
        $usr_msg = new backend_user_message($usr);
        $dto_base = $imp->get_data_object($json_array, $usr_msg, $size);
        $dto_base_ui->set_view_list($this->cast_view_list($dto_base->view_list()));
        // add the view id because the import does not include the database id
        $dto_base_ui->add_id_to_views();
        // add the components to the views
        //$dto_base_ui->add_components_to_views();
        $dto_ui->merge_view_list($dto_base_ui->view_list());

        // TODO Prio 2 separate the test object creation from the test object class because this is not depending on the test object settings
        $t_wrd = new test_words($t);
        $t_trp = new test_triples($t);
        $t_src = new test_sources($t);
        $t_ref = new test_refs($t);
        $t_val = new test_values($t);
        $t_frm = new test_formulas($t);
        $t_log = new test_log($t);

        // set the value cache list based
        $dto_ui->wrd_lst = $t_wrd->word_list_ui();
        $dto_ui->trp_lst = $t_trp->triple_list_ui();
        $dto_ui->src_lst = $t_src->source_list_ui();
        $dto_ui->ref_lst = $t_ref->ref_list_math_ui();
        $dto_ui->val_lst = $t_val->list_all_ui();
        $dto_ui->frm_lst = $t_frm->formula_list_ui();
        $dto_ui->frm_lnk_lst = $t_frm->formula_link_list_ui();
        $dto_ui->chg_log = $t_log->log_list_named_ui();

        // set the global cache var
        $ui_sys = $dto_ui;

        return $dto_ui;
    }

    /**
     * cast a backend list to a frontend list via api message
     * @param sandbox_list|type_list|change_log_list $lst the filled backend list
     * @param api_type_list|array $typ_lst configuration for the api message e.g. if phrases should be included
     * @return word_list_ui|triple_list_ui|source_list_ui|ref_list_ui|value_list_ui|formula_list_ui|formula_link_list_ui|change_log_list_ui|list_ui
     */
    function list_to_ui(
        sandbox_list|type_list|change_log_list $lst,
        api_type_list|array                    $typ_lst = []
    ): word_list_ui|triple_list_ui|source_list_ui|ref_list_ui|value_list_ui|formula_list_ui|formula_link_list_ui|change_log_list_ui|list_ui
    {
        $tl = new test_lib();
        $lst_ui = $tl->obj_to_ui_obj($lst);
        $api_json = $lst->api_json($typ_lst);
        $lst_ui->set_from_json($api_json);
        return $lst_ui;
    }

    /**
     * TODO add missing frontend objects like
     * TODO Prio 0 easy add missing mapping error log message to all other object mapper
     * get the frontend object related to the given backend object
     * @param db_object_seq_id|sandbox_multi|list_db_read|type_list $dbo the given backend object
     * @return false|db_object_ui|list_ui the corresponding frontend object
     */
    public function obj_to_ui_obj(
        db_object_seq_id|sandbox_multi|list_db_read|type_list $dbo
    ): false|db_object_ui|list_ui
    {
        $result =  match ($dbo::class) {
            user::class => new user_ui(),
            word::class => new word_ui(),
            verb::class => new verb_ui(),
            triple::class => new triple_ui(),
            source::class => new source_ui(),
            ref::class => new ref_ui(),
            value::class => new value_ui(),
            group::class => new group_ui(),
            formula::class => new formula_ui(),
            formula_link::class => new formula_link_ui(),
            result::class => new result_ui(),
            view::class => new view_ui(),
            view_relation::class => new view_relation_ui(),
            term_view::class => new term_view_ui(),
            component::class => new component_ui(),
            component_link::class => new component_link_ui(),
            word_list::class => new word_list_ui(),
            triple_list::class => new triple_list_ui(),
            ref_list::class => new ref_list_ui(),
            source_list::class => new source_list_ui(),
            value_list::class => new value_list_ui(),
            formula_list::class => new formula_list_ui(),
            formula_link_list::class => new formula_link_list_ui(),
            change_log_list::class => new change_log_list_ui(),
            default => false,
        };
        if (!$result) {
            log_err('ui object for ' . $dbo::class . ' missing');
        }
        return $result;
    }

    /**
     * TODO Prio 1 add user_message as parameter
     * set the all values of the frontend object based on a backend object using the api object
     * @param object $model_obj the frontend object with the values of the backend object
     */
    function ui_obj(object $model_obj, object $dsp_obj, bool $do_save = true): object
    {
        $usr_msg = new user_message();
        $api_json = $model_obj->api_json();
        $dsp_obj->set_from_json($api_json, $usr_msg);
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

        $msg = new backend_user_message();

        // create a dummy system user for unit testing
        $usr_sys = new user;
        $usr_sys->id = users::SYSTEM_ID;
        $usr_sys->name = users::SYSTEM_NAME;

        // create a dummy user for testing
        $usr = new user;
        $usr->id = users::SYSTEM_TEST_ID;
        $usr->name = users::SYSTEM_TEST_NAME;
        $usr->set_profile(user_profiles::EMAIL, $msg);

        return $usr;
    }


}
