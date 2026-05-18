<?php

/*

    test/unit_ui/system_view_ui_tests.php - test if the system view still look the same without using the api
    -------------------------------------


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

namespace Zukunft\ZukunftCom\test\php\unit_ui;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\cfg\system\job;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::MODEL_CONST . 'def.php';
include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::USER . 'user.php';
include_once paths::MODEL_CONST . 'def.php';
include_once paths::API_OBJECT . 'controller.php';
include_once paths::MODEL_SYSTEM . 'system_time_list.php';
include_once paths::SHARED_TYPES . 'system_time_type.php';
include_once paths::MODEL_HELPER . 'db_object.php';
include_once paths::MODEL_COMPONENT . 'component.php';
include_once paths::MODEL_FORMULA . 'formula.php';
include_once paths::MODEL_REF . 'ref.php';
include_once paths::MODEL_REF . 'source.php';
include_once paths::MODEL_RESULT . 'result.php';
include_once paths::MODEL_SANDBOX . 'sandbox.php';
include_once paths::MODEL_SYSTEM . 'sys_log.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_VALUE . 'value.php';
include_once paths::MODEL_VIEW . 'view.php';
include_once paths::MODEL_VIEW . 'view_relation.php';
include_once paths::MODEL_VERB . 'verb.php';
include_once paths::MODEL_WORD . 'triple.php';
include_once paths::MODEL_WORD . 'word.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED . 'api.php';
include_once paths::SHARED . 'url_var.php';
include_once test_paths::CONST . 'files.php';
include_once test_paths::CREATE . 'test_mappers.php';
include_once test_paths::CREATE . 'test_mappers.php';
include_once test_paths::UTILS . 'test_cleanup.php';
include_once test_paths::UTILS . 'test_lib.php';

use Zukunft\ZukunftCom\main\php\cfg\component\component;
use Zukunft\ZukunftCom\main\php\cfg\component\component_link;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_link;
use Zukunft\ZukunftCom\main\php\cfg\group\group;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_object;
use Zukunft\ZukunftCom\main\php\cfg\language\language;
use Zukunft\ZukunftCom\main\php\cfg\ref\ref;
use Zukunft\ZukunftCom\main\php\cfg\ref\source;
use Zukunft\ZukunftCom\main\php\cfg\result\result;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_multi;
use Zukunft\ZukunftCom\main\php\cfg\system\sys_log;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\value\value;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb;
use Zukunft\ZukunftCom\main\php\cfg\view\view;
use Zukunft\ZukunftCom\main\php\cfg\view\view_relation;
use Zukunft\ZukunftCom\main\php\cfg\view\term_view;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\web\frontend;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\user\user as user_ui;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\helper\MapObject;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\const\views as view_shared;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\change_actions;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\test\php\const\files as test_files;
use Zukunft\ZukunftCom\test\php\create\test_const;
use Zukunft\ZukunftCom\test\php\create\test_mappers;
use Zukunft\ZukunftCom\test\php\create\test_users;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;
use Zukunft\ZukunftCom\test\php\utils\test_lib;

class system_view_ui_tests
{
    function run(test_cleanup $t): void
    {

        // init
        $lib = new library();
        $tl = new test_lib();
        $t_usr = new test_users($t);
        $t_map = new test_mappers($t);
        $msp_ui = new MapObject();

        // start the test section (ts)
        $ts = 'unit ui system views ';
        $t->header($ts);
        $t->usr1 = $t_usr->user_sys_test();
        $usr_msg = new user_message();
        $usr_ui = $msp_ui->convertToUi($t->usr1, $usr_msg);
        $usr_msg->usr = $usr_ui;


        // shared frontend instance for all page tests
        $ui = new frontend('unit test');
        $dto = $tl->ui_test_cache($t->usr1, $t);
        $ui->set_cache($dto);
        // TODO Prio 1 deprecate
        $ui->load_dummy_cache_from_test_resources($t->usr1);
        $usr_dsp = $tl->cast_user($t->usr1);

        // test the notification component standalone
        $t->subheader($ts . 'notification');
        $html_base = new html_base();
        $test_name = 'dsp_notification renders warning div';
        $t->assert_html(
            $test_name,
            $html_base->dsp_notification('Forgot password?'),
            '<div class="alert alert-warning notification-bar">Forgot password?</div>'
        );

        // test that a failed login renders the notification in the full page
        $_SESSION[url_var::SESSION_TOKEN] = test_const::DUMMY_SESSION_TOKEN;
        $err_msg = new user_message();
        $err_msg->add(msg_id::PASSWORD_WRONG, []);
        $url_array = [url_var::MASK => views::LOGIN_ID];
        $login_html = $ui->url_to_html($url_array, null, $err_msg, $ui->dto);

        $notification_div = '<div class="alert alert-warning notification-bar">';
        $test_name = 'login page with failed login shows notification bar';
        $t->assert_text_contains($test_name, $login_html, $notification_div);

        $expected_msg = msg_id::PASSWORD_WRONG->value;
        $test_name = 'login page notification contains password wrong message';
        $t->assert_text_contains($test_name, $login_html, $expected_msg);

        $file_path = test_paths::HTML . test_paths::VIEW_FUNCTIONS . 'login_notification';
        $test_name = 'login page with failed login notification matches snapshot';
        $t->assert_html_page($test_name, $login_html, $file_path);

        // test that url_to_action preserves back params on login failure so "or go back" can be rendered
        $back_mask_key = url_var::BACK . url_var::MASK;
        $back_id_key = url_var::BACK . url_var::ID;
        $url_with_back = [
            url_var::MASK => views::LOGIN_ID,
            $back_mask_key => views::WORD_ID,
            $back_id_key => '123',
        ];
        $fail_msg = new user_message();
        $result_url = $ui->url_to_action($url_with_back, $t->usr1, $usr_dsp, $fail_msg, $ui->dto, false);

        $test_name = 'failed login preserves back mask param in returned url';
        $t->assert($test_name, $result_url[$back_mask_key] ?? '', views::WORD_ID);

        $test_name = 'failed login preserves back id param in returned url';
        $t->assert($test_name, $result_url[$back_id_key] ?? '', '123');

        $test_name = 'failed login keeps login mask in returned url';
        $t->assert($test_name, $result_url[url_var::MASK] ?? 0, views::LOGIN_ID);

        // test that a failed signup renders the notification in the full page
        $err_msg = new user_message();
        $err_msg->add(msg_id::SIGNUP_ERR_NAME_EXISTS, []);
        $url_array = [url_var::MASK => views::SIGNUP_ID];
        $signup_html = $ui->url_to_html($url_array, null, $err_msg, $ui->dto);

        $test_name = 'signup page with duplicate name shows notification bar';
        $t->assert_text_contains($test_name, $signup_html, $notification_div);

        $file_path = test_paths::HTML . test_paths::VIEW_FUNCTIONS . 'signup_notification';
        $test_name = 'signup page with name exists notification matches snapshot';
        $t->assert_html_page($test_name, $signup_html, $file_path);

        // test that url_to_action on logout resets both user objects to anonymous state
        $logout_backend = clone $t->usr1;
        $logout_frontend = $tl->cast_user($logout_backend);
        $logout_msg = new user_message();
        $logout_result_url = $ui->url_to_action(
            [url_var::MASK => views::LOGOUT_ID],
            $logout_backend,
            $logout_frontend,
            $logout_msg,
            $ui->dto,
            false
        );

        $test_name = 'logout action returns logout view url';
        $t->assert($test_name, $logout_result_url[url_var::MASK] ?? 0, views::LOGOUT_ID);

        $test_name = 'logout action resets backend user to anonymous';
        $t->assert($test_name, $logout_backend->has_db_id(), false);

        $test_name = 'logout action resets frontend user to ip-only';
        $t->assert($test_name, $logout_frontend->is_ip_only(), true);

        // test that the logout page shows the success message
        global $mtr;
        $url_array = [url_var::MASK => views::LOGOUT_ID];
        $logout_html = $ui->url_to_html($url_array, null, new user_message(), $ui->dto);

        $test_name = 'logout page shows logout notice text';
        $t->assert_text_contains($test_name, $logout_html, $mtr->txt(msg_id::LOGOUT_NOTICE));

        $file_path = test_paths::HTML . test_paths::VIEW_FUNCTIONS . 'logout_success';
        $test_name = 'logout page matches snapshot';
        $t->assert_html_page($test_name, $logout_html, $file_path);

        // test that a failed activation (key mismatch) renders the notification on the activate page
        $t->subheader($ts . 'login activate');

        $err_msg = new user_message();
        $err_msg->add(msg_id::ACTIVATE_ERR_KEY_MISMATCH, []);
        $url_array = [url_var::MASK => views::LOGIN_ACTIVATE_ID, url_var::ID => 1];
        $activate_html = $ui->url_to_html($url_array, null, $err_msg, $ui->dto);

        $test_name = 'activate page with key mismatch shows notification bar';
        $t->assert_text_contains($test_name, $activate_html, $notification_div);

        $test_name = 'activate page notification contains key mismatch message';
        $t->assert_text_contains($test_name, $activate_html, $mtr->txt(msg_id::ACTIVATE_ERR_KEY_MISMATCH));

        $file_path = test_paths::HTML . test_paths::VIEW_FUNCTIONS . 'activate_err_key_mismatch';
        $test_name = 'activate page with key mismatch notification matches snapshot';
        $t->assert_html_page($test_name, $activate_html, $file_path);

        // test that the activate page shown after a successful password reset email renders correctly
        // (action_login_reset redirects to LOGIN_ACTIVATE_ID on success, passing the user id)
        $t->subheader($ts . 'login reset');

        $url_array = [url_var::MASK => views::LOGIN_ACTIVATE_ID, url_var::ID => 1];
        $reset_sent_html = $ui->url_to_html($url_array, null, new user_message(), $ui->dto);

        $test_name = 'activate page after reset email shows activation key label';
        $t->assert_text_contains($test_name, $reset_sent_html, $mtr->txt(msg_id::ACTIVATE_SUBMIT));

        $file_path = test_paths::HTML . test_paths::VIEW_FUNCTIONS . 'reset_email_sent';
        $test_name = 'activate page after reset email matches snapshot';
        $t->assert_html_page($test_name, $reset_sent_html, $file_path);

        // test that the login_reset form renders with a cancel and go back link when no back params are given
        $url_array = [url_var::MASK => views::LOGIN_RESET_ID];
        $reset_form_html = $ui->url_to_html($url_array, null, new user_message(), $ui->dto);

        $test_name = 'login reset page shows cancel and go back link';
        $t->assert_text_contains($test_name, $reset_form_html, $mtr->txt(msg_id::CANCEL_AND_GO));

        $file_path = test_paths::HTML . test_paths::VIEW_FUNCTIONS . 'login_reset';
        $test_name = 'login reset page matches snapshot';
        $t->assert_html_page($test_name, $reset_form_html, $file_path);

        // test the system views by id
        // similar to horizontal_ui_tests which tests the curl view for the main objects
        $t->subheader($ts . 'by id');

        /*
        $test_name = 'test the start page upfront to have at least the header and footer fine for all pages';
        $url = 'http://localhost/http/view.php';
        $url_part = parse_url($url);
        parse_str($url_part["query"], $url_array);
        $html = $ui->url_to_html($url_array, $usr_dsp, $usr_msg, $ui->dto);
        $file_path = test_paths::HTML . test_paths::VIEW_FUNCTIONS . 'start_page';
        $t->assert_html_page($test_name, $html, $file_path);
        */

        // loop over the system views
        $this->assert_views_by_id($t, $t_map, $ui, $usr_dsp, $usr_msg, $lib);

    }

    /**
     * iterate over all system view ids and assert each rendered page matches its HTML snapshot
     * @param test_cleanup $t test runner for assertions and user fixtures
     * @param test_mappers $t_map builds filled test URLs per class and action
     * @param frontend $ui renders HTML from a URL array
     * @param user_ui $usr_dsp logged-in user used for views that require a session
     * @param user_message $usr_msg collects any messages produced during rendering
     * @param library $lib converts class names to file-path segments
     */
    private function assert_views_by_id(
        test_cleanup $t,
        test_mappers $t_map,
        frontend     $ui,
        user_ui      $usr_dsp,
        user_message $usr_msg,
        library      $lib
    ): void
    {
        $updated_files = [];
        // TODO Prio 3 review and use random?
        for ($msk_typ = 1; $msk_typ < 2; $msk_typ++) {
            for ($id = views::MIN_TEST_ID; $id <= views::MAX_TEST_ID; $id++) {
                $dbo = $this->view_id_to_dbo($id, $t->usr1);
                $action = $this->view_id_to_url_action($id);
                if ($msk_typ == 1) {
                    $url = $t_map->class_to_filled_url($dbo::class, $id, $action);
                } else {
                    $url = $t_map->class_to_filled_url($dbo::class, $id, $action, url_var::MASK);
                }
                $url_part = parse_url($url);
                parse_str($url_part["query"], $url_array);
                if (in_array($id, views::TEST_LOGIN_VIEW_IDS)) {
                    $html = $ui->url_to_html($url_array, $usr_dsp, $usr_msg, $ui->dto);
                } else {
                    $html = $ui->url_to_html($url_array, null, $usr_msg, $ui->dto);
                }
                [$folder, $dbo_name, $test_name] = $this->view_id_to_file_info($id, $dbo::class, $action, $url_array, $lib);
                $file_path = test_paths::VIEWS_BY_ID . $folder . $dbo_name;
                $updated_files[] = test_paths::RESOURCE . $file_path . test_files::HTML;
                $t->assert_html_page($test_name, $html, $file_path);
            }
        }
        // remove test files not used any more
        foreach ($lib->dir_files(test_paths::RESOURCE . test_paths::VIEWS_BY_ID) as $path) {
            if (str_ends_with($path, test_files::HTML) && !in_array($path, $updated_files)) {
                $t->delete_path_file($path);
            }
        }
    }

    /**
     * resolve the snapshot folder, filename prefix, and test name for one view id
     * @param int $id the view id
     * @param string $class the backend object class name
     * @param string $action the CRUD action
     * @param array $url_array the parsed URL parameters
     * @param library $lib helper for class-to-name conversion
     * @return array [$folder, $dbo_name, $test_name]
     */
    private function view_id_to_file_info(
        int     $id,
        string  $class,
        string  $action,
        array   $url_array,
        library $lib
    ): array
    {
        $prefix = $id . '_';
        if ($class == db_object::class) {
            $result = $this->db_object_file_info($id, $action, $prefix);
        } elseif (in_array($id, views::SEARCH_MASKS_IDS)) {
            $name = views::TEST_VIEW_IDS[$id] ?? 'search';
            $result = ['search' . DIRECTORY_SEPARATOR, $prefix . $name, $name . ' view'];
        } elseif (in_array($id, views::IM_EXPORT_MASKS_IDS)) {
            $name = views::TEST_VIEW_IDS[$id] ?? 'im_export';
            $result = ['im_export' . DIRECTORY_SEPARATOR, $prefix . $name, $name . ' view'];
        } else {
            $domain_class = $lib->class_to_name($class);
            $dbo_name = $prefix . $domain_class;
            $dbo_id = $url_array[url_var::ID] ?? 0;
            if ($action != change_actions::SHOW) {
                if (in_array($id, views::PROCESS_STEP_MASKS_IDS)) {
                    $dbo_name .= '_' . (views::TEST_VIEW_IDS[$id] ?? $action);
                } else {
                    $dbo_name .= '_' . $action;
                }
            }
            if ($dbo_id != 0) {
                $dbo_name .= '_' . $lib->str_to_file($dbo_id);
            }
            $result = [$domain_class . DIRECTORY_SEPARATOR, $dbo_name, $action . ' ' . $domain_class . ' view'];
        }
        return $result;
    }

    /**
     * resolve folder, filename prefix, and test name for a db_object view (system/process views)
     * @param int $id the view id
     * @param string $action the CRUD action
     * @param string $prefix the id-based filename prefix e.g. '60_'
     * @return array [$folder, $dbo_name, $test_name]
     */
    private function db_object_file_info(int $id, string $action, string $prefix): array
    {
        $result = ['other' . DIRECTORY_SEPARATOR, $prefix . 'other', 'other view'];
        if ($id == views::START_ID) {
            $result = ['start_page' . DIRECTORY_SEPARATOR, $prefix . 'start_page', 'start_page view'];
        } elseif (in_array($id, views::CONFIRM_MASKS_IDS)) {
            $result = $this->confirm_file_info($id, $action, $prefix);
        } elseif (in_array($id, views::STATIC_VIEW_IDS)) {
            // checked before PROCESS_STEP_MASKS_IDS because SETUP_ID appears in both
            $name = views::TEST_VIEW_IDS[$id] ?? 'static';
            $result = ['static' . DIRECTORY_SEPARATOR, $prefix . $name, $name . ' view'];
        } elseif (in_array($id, views::PROCESS_STEP_MASKS_IDS)) {
            $name = views::TEST_VIEW_IDS[$id] ?? 'process_step';
            $result = ['process' . DIRECTORY_SEPARATOR, $prefix . $name, $name . ' view'];
        }
        return $result;
    }

    /**
     * resolve folder, filename prefix, and test name for a confirm view
     * @param int $id the view id
     * @param string $action the CRUD action
     * @param string $prefix the id-based filename prefix e.g. '55_'
     * @return array [$folder, $dbo_name, $test_name]
     */
    private function confirm_file_info(int $id, string $action, string $prefix): array
    {
        $folder = 'confirm' . DIRECTORY_SEPARATOR;
        $file_name = 'unknown';
        $test_name = 'unknown view';
        if ($action == change_actions::ADD) {
            $file_name = 'confirm_word_add';
            $test_name = 'confirm word add view';
        } elseif ($action == change_actions::UPDATE) {
            if ($id == views::CONFIRM_VIEWS_ID) {
                $file_name = 'confirm_word_view_change';
                $test_name = 'confirm word mask change view';
            } else {
                $file_name = 'confirm_word_edit';
                $test_name = 'confirm word edit view';
            }
        } elseif ($action == change_actions::DELETE) {
            $file_name = 'confirm_word_del';
            $test_name = 'confirm word del view';
        } elseif ($id == views::SANDBOX_ID) {
            $file_name = 'sandbox';
            $test_name = 'confirm user sandbox view';
        } elseif ($id == views::UNDO_ID) {
            $file_name = 'undo';
            $test_name = 'undo change view';
        }
        return [$folder, $prefix . $file_name, $test_name];
    }

    private function view_id_to_dbo(int $view_id, user $usr): sandbox|sandbox_multi|user|db_object|phrase_list
    {
        // select the backend object to display
        // TODO add any missing system views like
        //      term_view_links, formula_link, component_links, styles, view_types,
        //      time_series, geo and text values, ip ranges, language, pod,
        //      add types (phrase_type, formula_type, formula_link_types, source_types,
        //                 ref_types, position_types, view_types, view_link_types,
        //                 component_types, component_link_types, pod_types, pod_status)
        //     (at least all curl views)
        if (in_array($view_id, view_shared::WORD_MASKS_IDS)) {
            $dbo = new word($usr);
        } elseif (in_array($view_id, view_shared::VERB_MASKS_IDS)) {
            $dbo = new verb();
        } elseif (in_array($view_id, view_shared::TRIPLE_MASKS_IDS)) {
            $dbo = new triple($usr);
        } elseif (in_array($view_id, view_shared::SOURCE_MASKS_IDS)) {
            $dbo = new source($usr);
        } elseif (in_array($view_id, view_shared::REF_MASKS_IDS)) {
            $dbo = new ref($usr);
        } elseif (in_array($view_id, view_shared::VALUE_MASKS_IDS)) {
            $dbo = new value($usr);
        } elseif (in_array($view_id, view_shared::GROUP_MASKS_IDS)) {
            $dbo = new group($usr);
        } elseif (in_array($view_id, view_shared::FORMULA_MASKS_IDS)) {
            $dbo = new formula($usr);
        } elseif (in_array($view_id, view_shared::RESULT_MASKS_IDS)) {
            $dbo = new result($usr);
        } elseif (in_array($view_id, view_shared::VIEW_MASKS_IDS)) {
            $dbo = new view($usr);
        } elseif (in_array($view_id, view_shared::COMPONENT_MASKS_IDS)) {
            $dbo = new component($usr);
        } elseif (in_array($view_id, view_shared::VIEW_LINK_MASKS_IDS)) {
            $dbo = new term_view($usr);
        } elseif (in_array($view_id, view_shared::COMPONENT_LINK_MASKS_IDS)) {
            $dbo = new component_link($usr);
        } elseif (in_array($view_id, view_shared::FORMULA_LINK_MASKS_IDS)) {
            $dbo = new formula_link($usr);
        } elseif (in_array($view_id, view_shared::VIEW_RELATION_MASKS_IDS)) {
            $dbo = new view_relation($usr);
        } elseif (in_array($view_id, view_shared::USER_MASKS_IDS)) {
            $dbo = new user();
        } elseif (in_array($view_id, view_shared::USER_LOGIN_MASK_IDS)) {
            $dbo = new user();
        } elseif (in_array($view_id, view_shared::ADMIN_MASK_IDS)) {
            $dbo = new user();
        } elseif (in_array($view_id, view_shared::LANGUAGE_MASKS_IDS)) {
            $dbo = new language();
        } elseif (in_array($view_id, view_shared::CONFIRM_MASKS_IDS)) {
            $dbo = new db_object();
        } elseif (in_array($view_id, view_shared::STATIC_VIEW_IDS)) {
            $dbo = new db_object();
        } elseif (in_array($view_id, view_shared::SYSTEM_LOG_VIEW_IDS)) {
            $dbo = new sys_log();
        } elseif (in_array($view_id, view_shared::CONTEXT_VIEW_IDS)) {
            $dbo = new phrase_list($usr);
        } elseif (in_array($view_id, view_shared::JOB_MASKS_IDS)) {
            $dbo = new job($usr);
        } else {
            $dbo = new db_object();
            if ($view_id != views::START_ID) {
                log_err('no backend object defined for view id ' . $view_id);
            }
        }
        return $dbo;
    }


    private function view_id_to_url_action(int $view_id): string
    {
        // select the backend object to display
        if (in_array($view_id, view_shared::SHOW_MASKS_IDS)) {
            $action = change_actions::SHOW;
        } elseif (in_array($view_id, view_shared::ADD_MASKS_IDS)) {
            $action = change_actions::ADD;
        } elseif (in_array($view_id, view_shared::EDIT_MASKS_IDS)) {
            $action = change_actions::UPDATE;
        } elseif (in_array($view_id, view_shared::DEL_MASKS_IDS)) {
            $action = change_actions::DELETE;
        } elseif (in_array($view_id, view_shared::SUB_MASKS_IDS)) {
            $action = change_actions::SUB;
        } elseif (in_array($view_id, view_shared::PROCESS_STEP_MASKS_IDS)) {
            $action = change_actions::STEP;
        } elseif (in_array($view_id, view_shared::SEARCH_MASKS_IDS)) {
            $action = change_actions::SEARCH;
        } else {
            $action = 'unknown';
        }
        return $action;
    }

}