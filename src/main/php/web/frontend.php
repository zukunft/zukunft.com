<?php

/*

    frontend.php - the main html frontend application
    ------------

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

include_once paths::WEB_CONST . 'paths.php';

use html\const\paths as html_paths;

// get library that is shared between the backend and the html frontend
include_once paths::SHARED . 'library.php';

// get the api const that are shared between the backend and the html frontend
include_once paths::SHARED . 'api.php';

// get the pure html frontend objects
include_once html_paths::USER . 'user.php';

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
include_once html_paths::VIEW . 'view_list.php';

use shared\library;
use shared\api;
use html\user\user;
use html\types\type_lists;

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

    private type_lists $typ_lst_cache;


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
        $this->typ_lst_cache = new type_lists();
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
        session_start();

        $usr = $this->get_user();

        // load upfront the frontend cache
        $api_msg = $this->api_get(type_lists::class);
        $this->typ_lst_cache = new type_lists($api_msg);

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


    /*
     * user
     */

    function get_user(): user
    {
        global $usr;
        $usr = new user();
        return $usr;
    }


    /*
     * view
     */

    function show_view(int $id): string
    {
        return $this->typ_lst_cache->get_view_by_id($id);
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
        $url = self::HOST_DEV . api::URL_API_PATH . $lib->camelize_ex_1($class);
        if (is_array($ids)) {
            $data = array($id_fld => implode(",", $ids));
        } else {
            $data = array($id_fld => $ids);
        }
        $ctrl = new rest_ctrl();
        return $ctrl->api_call(rest_ctrl::GET, $url, $data);
    }

}
