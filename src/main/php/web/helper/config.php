<?php

/*

    web/helper/config.php - to cache and manage the user config in the frontend
    ---------------------

    This superclass should be used by the classes word_dsp, formula_dsp, ... to enable user specific values and links


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

    Copyright (c) 1995-2023 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace html\helper;

include_once WEB_VALUE_PATH . 'value_list.php';
include_once WEB_HTML_PATH . 'rest_ctrl.php';
include_once SHARED_PATH . 'api.php';
include_once SHARED_HELPER_PATH . 'Config.php';
include_once SHARED_CONST_PATH . 'words.php';
include_once WEB_PHRASE_PATH . 'phrase_list.php';
include_once WEB_USER_PATH . 'user_message.php';

use html\phrase\phrase_list;
use html\rest_ctrl;
use html\user\user_message;
use html\value\value_list;
use shared\api;
use shared\const\words;
use shared\helper\Config as shared_config;

class config extends value_list
{

    // TODO add the user setting as default
    function percent_decimals(): int
    {
        return shared_config::DEFAULT_PERCENT_DECIMALS;
    }

    function dec_point(): string
    {
        return shared_config::DEFAULT_DEC_POINT;
    }

    function thousand_sep(): string
    {
        return shared_config::DEFAULT_THOUSAND_SEP;
    }

    /**
     * @return string with the date format as requested by the user
     */
    function date_time_format(): string
    {
        return shared_config::DEFAULT_DATE_TIME_FORMAT;
    }

    /**
     * request the user specific frontend configuration from the backend
     * @return user_message if it fails the reason why
     */
    function load(string $part = api::CONFIG_FRONTEND): user_message
    {
        $usr_msg = new user_message();

        $data = array(api::URL_VAR_CONFIG_PART => $part);
        $rest = new rest_ctrl();
        $json_body = $rest->api_get(config::class, $data);
        $this->api_mapper($json_body);
        if (!$this->is_empty()) {
            $usr_msg->add_message('config api message is empty');
        }
        return $usr_msg;
    }

    /**
     * get a frontend config value selected by the phrase names
     * @param array $names with the phrase names to select the config value
     * @return int|float|string|null with the user specific config value
     */
    function get_by_names(array $names): int|float|string|null
    {
        $phr_lst = new phrase_list();
        $val = null;
        switch ($names) {
            case [words::PERCENT, words::DECIMAL]:
                $val = shared_config::DEFAULT_PERCENT_DECIMALS;
                break;
            case [words::ROW, words::LIMIT]:
                $val = shared_shared_config::ROW_LIMIT;
                break;
            case [words::DECIMAL, words::POINT]:
                $val = shared_config::DEFAULT_DEC_POINT;
        }
        return $val;
    }

}


