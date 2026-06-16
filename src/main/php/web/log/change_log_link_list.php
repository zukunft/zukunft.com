<?php

/*

    web/log/change_log_link_list.php - to create the HTML code to display a list of user link changes
    --------------------------------

    loads the link change history of one object from the backend via the API and renders it as a table


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

namespace Zukunft\ZukunftCom\main\php\web\log;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::HTML . 'html_base.php';
include_once html_paths::HTML . 'rest_call.php';
include_once html_paths::HTML . 'styles.php';
include_once html_paths::LOG . 'change_log_link.php';
include_once html_paths::SANDBOX . 'ListBase.php';
include_once html_paths::SYSTEM . 'back_trace.php';
include_once html_paths::USER . 'user.php';
include_once html_paths::USER . 'user_message.php';
include_once paths::SHARED_CONST . 'rest_ctrl.php';
include_once paths::SHARED . 'library.php';
include_once paths::SHARED . 'url_var.php';

use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\html\rest_call;
use Zukunft\ZukunftCom\main\php\web\html\styles;
use Zukunft\ZukunftCom\main\php\web\sandbox\ListBase;
use Zukunft\ZukunftCom\main\php\web\system\back_trace;
use Zukunft\ZukunftCom\main\php\web\user\user;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\rest_ctrl;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\url_var;

class change_log_link_list extends ListBase
{

    /*
     * set and get
     */

    /**
     * set the vars of this list based on the given api json
     * @param array $json_array an api list json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function api_mapper(array $json_array): user_message
    {
        return parent::api_mapper_list($json_array, new change_log_link());
    }


    /*
     * load
     */

    /**
     * load the link change history of one object from the backend via the api
     *
     * @param string $class the class name of the object whose link changes should be loaded
     * @param int|string $id the database id of the object
     * @param user|null $usr the user who wants to see the changes
     * @return user_message to report any problems to the user
     */
    function load_by_object(string $class, int|string $id = 1, user|null $usr = null): user_message
    {
        $usr_msg = new user_message();
        $json = $this->load_api_by_object($class, $id, $usr);
        $this->set_from_json(json_decode($json, true));
        return $usr_msg;
    }

    /**
     * get the json of the link change history of one object from the api
     *
     * @param string $class the class name of the object whose link changes should be loaded
     * @param int|string $id the database id of the object
     * @param user|null $usr the user who wants to see the changes
     * @return string the api json as a string
     */
    function load_api_by_object(string $class, int|string $id = 1, user|null $usr = null): string
    {
        $lib = new library();
        $log_class = $lib->class_to_name(change_log_link_list::class);
        $url = THIS_URL . url_var::API_PATH . $lib->camelize_ex_1($log_class);
        $data = [];
        $data[url_var::LOG_CLASS] = $lib->class_to_api_name($class);
        $data[url_var::ID] = $id;
        $ctrl = new rest_call();
        return $ctrl->api_call(rest_ctrl::GET, $url, $data);
    }


    /*
     * table
     */

    /**
     * show all link changes of an object e.g. a word as a table
     * @param back_trace|null $back the back trace url for the undo functionality
     * @return string the html code with all link changes of the list
     */
    function tbl(?back_trace $back = null): string
    {
        $html = new html_base();
        $html_text = '';
        if (!$this->is_empty()) {
            $html_text .= new change_log_link()->th();
            foreach ($this->lst() as $chg) {
                $html_text .= $chg->tr($back);
            }
        }
        return $html->tbl($html_text, styles::STYLE_BORDERLESS);
    }

}