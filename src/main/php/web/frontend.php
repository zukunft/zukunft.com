<?php

/*

    web/frontend.php - the main html frontend application
    ----------------

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

    Copyright (c) 1995-2024 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace html;

use cfg\const\paths;
use const\files as test_files;
use Exception;
use html\component\component_exe as component_dsp;
use html\const\paths as html_paths;
use html\formula\formula as formula_dsp;
use html\helper\data_object;
use html\ref\ref as ref_dsp;
use html\ref\source as source_dsp;
use html\result\result as result_dsp;
use html\sandbox\db_object as db_object_dsp;
use html\sandbox\sandbox as sandbox_dsp;
use html\sandbox\sandbox_named as sandbox_named_dsp;
use html\types\type_lists;
use html\user\user as user_dsp;
use html\value\value as value_dsp;
use html\verb\verb as verb_dsp;
use html\view\view as view_dsp;
use html\word\triple as triple_dsp;
use html\word\word as word_dsp;
use shared\api;
use shared\const\rest_ctrl;
use shared\const\views;
use shared\library;
use shared\url_var;

include_once paths::WEB_CONST . 'paths.php';

// get library that is shared between the backend and the html frontend
include_once paths::SHARED . 'library.php';

// get the api const that are shared between the backend and the html frontend
include_once paths::SHARED . 'api.php';
include_once paths::SHARED . 'url_var.php';

// get the pure html frontend objects
include_once html_paths::USER . 'user.php';

include_once html_paths::HELPER . 'config.php';
include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::COMPONENT . 'component_exe.php';
include_once html_paths::FORMULA . 'formula.php';
include_once html_paths::TYPES . 'type_lists.php';
include_once html_paths::RESULT . 'result.php';
include_once html_paths::REF . 'ref.php';
include_once html_paths::REF . 'source.php';
include_once html_paths::SANDBOX . 'db_object.php';
include_once html_paths::SANDBOX . 'sandbox.php';
include_once html_paths::SANDBOX . 'sandbox_named.php';
include_once html_paths::TYPES . 'type_object.php';
include_once html_paths::TYPES . 'type_list.php';
include_once html_paths::TYPES . 'change_action_list.php';
include_once html_paths::TYPES . 'change_table_list.php';
include_once html_paths::TYPES . 'change_field_list.php';
include_once html_paths::TYPES . 'sys_log_status_list.php';
include_once html_paths::TYPES . 'job_type_list.php';
include_once html_paths::TYPES . 'languages.php';
include_once html_paths::TYPES . 'language_forms.php';
include_once html_paths::TYPES . 'share.php';
include_once html_paths::TYPES . 'protection.php';
include_once html_paths::TYPES . 'verbs.php';
include_once html_paths::TYPES . 'phrase_types.php';
include_once html_paths::TYPES . 'formula_type_list.php';
include_once html_paths::TYPES . 'formula_link_type_list.php';
include_once html_paths::TYPES . 'source_type_list.php';
include_once html_paths::TYPES . 'ref_type_list.php';
include_once html_paths::TYPES . 'view_type_list.php';
include_once html_paths::TYPES . 'view_link_type_list.php';
include_once html_paths::TYPES . 'component_type_list.php';
include_once html_paths::TYPES . 'component_link_type_list.php';
include_once html_paths::TYPES . 'position_type_list.php';
include_once html_paths::TYPES . 'type_lists.php';
include_once html_paths::VALUE . 'value.php';
include_once html_paths::VERB . 'verb.php';
include_once html_paths::VIEW . 'view.php';
include_once html_paths::WORD . 'triple.php';
include_once html_paths::WORD . 'word.php';
include_once TEST_CONST_PATH . 'files.php';
include_once paths::SHARED_CONST . 'rest_ctrl.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED . 'library.php';
include_once paths::SHARED . 'api.php';
include_once paths::SHARED . 'url_var.php';

class frontend
{

    /*
     * api const
     */

    const PAR_VIEW_ID = 'view'; // if the user has selected a special view, use it


    /*
     * servers
     */

    // TODO Prio 1 review (get from .env and not move to application.yaml and detect and fix it on initial program start)
    const HOST_DEV = 'http://localhost/';
    const HOST_UAT = 'https://test.zukunft.com/';
    const HOST_PROD = 'https://www.zukunft.com/';
    const HOST_SYS_LOG = '';

    /*
     * vars
     */

    private float $start_time; // the start time to detect long runners
    private string $code_name; // the name of the call script to locate issues
    private string $msg; // messages that should be shown to the user asap

    public ?type_lists $typ_lst_cache = null;


    /*
     * construct and map
     */

    /**
     * define the settings for this word object
     */
    function __construct(string $code_name)
    {
        $this->set_start_time();
        $this->set_code_name($code_name);
    }

    function reset_cache(): void
    {
        $this->typ_lst_cache = null;
    }


    /*
     * set and get
     */

    private function set_start_time(): void
    {
        $this->start_time = microtime(true);
    }

    private function set_code_name(string $code_name): void
    {
        $this->code_name = $code_name;
    }


    /*
     * session
     */

    function start(string $title): string
    {
        $result = '';

        // resume session (based on cookies)
        // TODO review session start and end calls
        session_start();

        $usr = $this->get_user();

        $this->load_cache();

        // html header
        $html = new html_base();
        echo $html->header($title, '', api::HOST_DEV, api::BS_PATH_DEV, api::BS_CSS_PATH_DEV);

        if (self::HOST_SYS_LOG != '') {
            $result .= $this->log_info('start ' . $this->code_name);
        }
        return $result;
    }

    function end(): string
    {
        $html = new html_base();
        echo $html->footer();

        $duration = microtime(true) - $this->start_time;
        if (self::HOST_SYS_LOG != '') {
            return $this->log_info('end ' . $this->code_name);
        } else {
            return '';
        }
    }

    /**
     * load the frontend cache once upfront via api
     * @return void
     */
    function load_cache(): void
    {
        if ($this->typ_lst_cache == null) {
            $api_msg = $this->api_get(type_lists::class);
            $this->set_cache($api_msg);
        }
    }

    /**
     * load the frontend cache from the test resource
     * @return void
     */
    function load_dummy_cache_from_test_resources(): void
    {
        if ($this->typ_lst_cache == null) {
            $api_msg = file_get_contents(test_files::TYPE_LISTS_CACHE);
            $this->set_cache($api_msg);
        }
    }

    /**
     * set the frontend cache once upfront base on the api message
     * used for the unit test without api calls
     *
     * @param string|null $api_msg with the api message as a string
     * @return void
     */
    function set_cache(?string $api_msg = null): void
    {
        if ($this->typ_lst_cache == null) {
            $this->typ_lst_cache = new type_lists($api_msg);
        }
    }


    /*
     * user
     */

    function get_user(): user_dsp
    {
        global $usr;
        $usr = new user_dsp();
        return $usr;
    }


    /*
     * view
     */


    /**
     * create the HTML code based on the given url
     *
     * @param array $url_array the parsed url as an array
     * @param user_dsp $usr the session user who has requested the view
     * @return string the html code to show the page to the user
     */
    function url_to_html(array $url_array, user_dsp $usr): string
    {
        // init the view
        $result = ''; // reset the html code var
        $msg = ''; // to collect all messages that should be shown to the user immediately

        // detect the url format and get the view id or code id
        $human_url = false;
        $pod_url = false;
        if (array_key_exists(url_var::MASK_HUMAN, $url_array)) {
            $human_url = true;
            $view = $url_array[url_var::MASK_HUMAN] ?? views::START_ID; // the database id of the view to display
        } elseif (array_key_exists(url_var::MASK_POD, $url_array)) {
            $pod_url = true;
            $view = $url_array[url_var::MASK_POD] ?? views::START_CODE; // the database id of the view to display
        } else {
            $view = $url_array[url_var::MASK] ?? views::START_ID; // the database id of the view to display
        }

        // get the general vars from the url
        $id = $url_array[url_var::ID] ?? 0; // the database id of the prime object to display
        // TODO Prio 1 complete all url vars mappings for $human_url, $pod_url and $short_url
        if ($human_url) {
            $step = $url_array[url_var::STEP_LONG] ?? 0; // the enum of the user process step to perform next
        } elseif ($pod_url) {
            $step = $url_array[url_var::STEP_POD] ?? 0; // the enum of the user process step to perform next
        } else {
            $step = $url_array[url_var::STEP] ?? 0; // the enum of the user process step to perform next
        }

        $new_view_id = $url_array[rest_ctrl::PAR_VIEW_NEW_ID] ?? '';
        $view_words = $url_array[url_var::WORDS] ?? '';
        $back = $url_array[url_var::BACK] ?? ''; // the word id from which this value change has been called (maybe later any page)

        // TODO move to the frontend __construct
        // get the fixed frontend config
        //$api_msg = $this->api_get(type_lists::class);
        //$frontend_cache = new type_lists($api_msg);

        // use default view if nothing is set
        if (($view == 0 or $view == '' or $view == null or $view == 'null') and $id == 0) {
            $view = views::START_ID;
        }

        // get the view id if the view code id is used
        if (is_numeric($view)) {
            $view_id = $view;
        } else {
            $msk = $this->typ_lst_cache->get_view($view);
            $view_id = $msk->id();
        }

        // select the main object to display
        $dbo = $this->view_id_to_dbo_dsp($view_id);

        // save form action
        // if the save bottom has been pressed
        if ($step > 0) {
            $dbo->url_mapper($url_array);
            $upd_result = $dbo->add_via_api();

            // if update was fine ...
            if ($upd_result->is_ok()) {
                // TODO Prio 0 get the id from the result
                //$id = $dbo->id();
                $id = 0;
                // ... display the calling page is switched off to keep the user on the edit view and see the implications of the change
                // switched off because maybe staying on the edit page is the expected behaviour
                if ($back == '' or $back == 0) {
                    $view_id = views::START_ID;
                }
                //$result .= dsp_go_back($back, $usr);
            } else {
                // ... or in case of a problem prepare to show the message
                $msg .= $upd_result->get_last_message();
            }
        }


        // get the main object to display
        if ($id != 0) {
            // if only the id is included in the url load the data via api
            if (count($url_array) <= 2) {
                $dbo->load_by_id($id);
            } else {
                $dbo->url_mapper($url_array);
            }
        } else {
            // get last term used by the user or a default value
            $wrd = $usr->last_term();
        }

        // select the view
        if (in_array($view_id, views::EDIT_DEL_MASKS_IDS)) {
            // TODO move as much a possible to backend functions
            if ($dbo->id() > 0) {
                // if the user has changed the view for this word, save it
                if ($new_view_id != '') {
                    $dbo->save_view($new_view_id);
                    $view_id = $new_view_id;
                } else {
                    // if the user has selected a special view, use it
                    if ($view_id == 0) {
                        // if the user has set a view for this word, use it
                        $view_id = $dbo->view_id();
                        if ($view_id <= 0) {
                            // if any user has set a view for this word, use the common view
                            $view_id = $dbo->calc_view_id();
                            if ($view_id <= 0) {
                                // if no one has set a view for this word, use the fallback view
                                $msk = $this->typ_lst_cache->get_view(views::WORD);
                                $view_id = $msk->id();
                            }
                        }
                    }
                }
            } else {
                $result .= log_err("No word selected.", "view.php", '',
                    (new Exception)->getTraceAsString());
            }
        }

        // create a display object, select and load the view and display the word according to the view
        if ($view_id != 0) {
            // TODO first create the frontend object and call from the frontend object the api
            // TODO for system views avoid the backend call by using the cache from the frontend
            // TODO get the system view from the preloaded cache
            // TODO use the frontend not the backend cache
            $msk_dsp = $this->typ_lst_cache->get_view_by_id($view_id);
            $title = $msk_dsp->title($dbo);
            $cfg = new data_object();
            $cfg->typ_lst_cache = $this->typ_lst_cache;
            $dsp_text = $msk_dsp->show($dbo, $cfg, $back);

            // use a fallback if the view is empty
            if ($dsp_text == '' or $msk_dsp->name() == '') {
                $msk_dsp = $this->typ_lst_cache->get_view(views::START);
                $dsp_text = $msk_dsp->name_tip($dbo, $back);
            }
            if ($dsp_text == '') {
                $result .= 'Please add a component to the view by clicking on Edit on the top right.';
            } else {
                $html = new html_base();
                $result .= $html->header($title, '');
                $result .= $dsp_text;
                $result .= $html->footer();
            }
        } else {
            $result .= log_err('No view for "' . $dbo->name() . '" found.',
                "view.php", '', (new Exception)->getTraceAsString());
        }

        return $result;
    }

    function show_view(int $id): string
    {
        return $this->typ_lst_cache->get_html_by_id($id);
    }


    /*
     * log
     */

    /**
     * send a log message to the system log server
     *
     * @param string $msg the message that should be sent
     * @return string if something is strange the message that should be shown to the user
     */
    private function log_info(string $msg): string
    {
        // TODO actually sent the message to the server
        return '';
    }


    /*
     * api
     */

    /**
     * get an api json as a string from the backend
     *
     * @param string $class the name of the class
     * @param array|string $ids
     * @param string $id_fld
     * @return string
     */
    function api_get(
        string       $class,
        array|string $ids = [],
        string       $id_fld = 'ids'
    ): string
    {
        $lib = new library();
        $class = $lib->class_to_name_pur($class);
        $url = self::HOST_DEV . url_var::API_PATH . $lib->camelize_ex_1($class);
        if (is_array($ids)) {
            $data = array($id_fld => implode(",", $ids));
        } else {
            $data = array($id_fld => $ids);
        }
        $ctrl = new rest_call();
        return $ctrl->api_call(rest_ctrl::GET, $url, $data);
    }

    /*
     * internal
     */

    private function view_id_to_dbo_dsp(int $view_id): sandbox_dsp|sandbox_named_dsp|db_object_dsp
    {
        // select the main object to display
        if (in_array($view_id, views::WORD_MASKS_IDS)) {
            $dbo_dsp = new word_dsp();
        } elseif (in_array($view_id, views::VERB_MASKS_IDS)) {
            $dbo_dsp = new verb_dsp();
        } elseif (in_array($view_id, views::TRIPLE_MASKS_IDS)) {
            $dbo_dsp = new triple_dsp();
        } elseif (in_array($view_id, views::SOURCE_MASKS_IDS)) {
            $dbo_dsp = new source_dsp();
        } elseif (in_array($view_id, views::REF_MASKS_IDS)) {
            $dbo_dsp = new ref_dsp();
        } elseif (in_array($view_id, views::VALUE_MASKS_IDS)) {
            $dbo_dsp = new value_dsp();
        } elseif (in_array($view_id, views::FORMULA_MASKS_IDS)) {
            $dbo_dsp = new formula_dsp();
        } elseif (in_array($view_id, views::RESULT_MASKS_IDS)) {
            $dbo_dsp = new result_dsp();
        } elseif (in_array($view_id, views::VIEW_MASKS_IDS)) {
            $dbo_dsp = new view_dsp();
        } elseif (in_array($view_id, views::COMPONENT_MASKS_IDS)) {
            $dbo_dsp = new component_dsp();
        } else {
            $dbo_dsp = new word_dsp();
        }
        return $dbo_dsp;
    }

}
