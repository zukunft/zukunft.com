<?php

/*

    api/log/change_log_list.php - a list function to create the HTML code to display a list of user changes
    ---------------------------

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

namespace html\log;

include_once WEB_HTML_PATH . 'html_base.php';
include_once WEB_SANDBOX_PATH . 'list_dsp.php';
include_once WEB_SYSTEM_PATH . 'back_trace.php';
include_once WEB_USER_PATH . 'user.php';
include_once WEB_USER_PATH . 'user_message.php';
include_once WEB_HTML_PATH . 'rest_ctrl.php';
include_once WEB_HTML_PATH . 'styles.php';
include_once SHARED_PATH . 'api.php';
include_once SHARED_PATH . 'library.php';

use html\html_base;
use html\rest_ctrl;
use html\sandbox\list_dsp;
use html\styles;
use html\system\back_trace;
use html\user\user;
use html\user\user_message;
use shared\api;
use shared\library;

class change_log_list extends list_dsp
{

    /*
     * set and get
     */

    /**
     * set the vars of a word object based on the given json
     * @param array $json_array an api single object json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function set_from_json_array(array $json_array): user_message
    {
        return parent::set_list_from_json($json_array, new change_log_named());
    }


    /*
     * load
     */

    /**
     * load a list of changes from the api
     *
     * @param string $class the class name of the object to test
     * @param int|string $id the database id of the object to which the changes should be listed
     * @param string $fld the url api field name to select only some changes e.g. 'word_field'
     * @param user|null $usr to select only the changes of this user
     * @param int $size to set a page size that is different from the default page size
     * @param int $page offset the number of pages
     * @return user_message to report any problems to the user
     */
    function load_by_object_field(
        string     $class,
        int|string $id = 1,
        string     $fld = '',
        user|null  $usr = null,
        int        $size = 0,
        int        $page = 0
    ): user_message
    {
        $usr_msg = new user_message();
        $json = $this->load_api_by_object_field($class, $id, $fld, $usr, $size, $page);
        $actual = json_decode($json, true);

        $this->set_from_json($actual);

        return $usr_msg;
    }

    /**
     * get the json of a list of changes from the api
     *
     * @param string $class the class name of the object to test
     * @param int|string $id the database id of the object to which the changes should be listed
     * @param string $fld the url api field name to select only some changes e.g. 'word_field'
     * @param user|null $usr to select only the changes of this user
     * @param int $limit to set a page size that is different from the default page size
     * @param int $page offset the number of pages
     * @return string the api json as a string
     */
    function load_api_by_object_field(
        string     $class,
        int|string $id = 1,
        string     $fld = '',
        user|null  $usr = null,
        int        $limit = 0,
        int        $page = 0
    ): string
    {
        $lib = new library();
        $log_class = $lib->class_to_name(change_log_list::class);
        $url = api::HOST_TESTING . api::URL_API_PATH . $lib->camelize_ex_1($log_class);
        $class = $lib->class_to_api_name($class);
        $data = [];
        $data[api::URL_VAR_CLASS] = $class;
        $data[api::URL_VAR_ID] = $id;
        $data[api::URL_VAR_FIELD] = $fld;
        $ctrl = new rest_ctrl();
        return $ctrl->api_call(rest_ctrl::GET, $url, $data);
    }


    /*
     * table
     */

    /**
     * show all changes of a named user sandbox object e.g. a word as table
     * @param back_trace|null $back the back trace url for the undo functionality
     * @return string the html code with all words of the list
     */
    function tbl(back_trace $back = null, bool $condensed = false, bool $with_users = false): string
    {
        $html = new html_base();
        $html_text = $this->th($condensed, $with_users);
        foreach ($this->lst() as $chg) {
            $html_text .= $html->td($chg->tr($back, $condensed, $with_users));
        }
        return $html->tbl($html->tr($html_text), styles::STYLE_BORDERLESS);
    }

    /**
     * @return string with the html table header to show the changes of sandbox objects e.g. a words
     */
    private function th(bool $condensed = false, bool $with_users = false): string
    {
        $html = new html_base();
        $head_text = $html->th('time');
        if ($condensed) {
            $head_text .= $html->th('changed to');
        } else {
            if ($with_users) {
                $head_text .= $html->th('user');
            }
            $head_text .= $html->th_row(array('field','from','to'));
            $head_text .= $html->th('');  // extra column for the undo icon
        }
        return $head_text;
    }

}
