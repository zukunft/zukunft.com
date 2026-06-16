<?php

/*

    web/helper/config.php - to cache and manage the user config in the frontend
    ---------------------

    This superclass should be used by the classes word_dsp, formula_dsp, ... to enable user-specific values and links
    var name: $ui_cfg


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

namespace Zukunft\ZukunftCom\main\php\web\helper;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once paths::MODEL_HELPER . 'system_object.php';
include_once html_paths::HTML . 'rest_call.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::VALUE . 'value_list.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_HELPER . 'Config.php';
include_once paths::SHARED_TYPES . 'system_time_type.php';
include_once paths::SHARED . 'api.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'url_var.php';

use Zukunft\ZukunftCom\main\php\cfg\helper\system_object;
use Zukunft\ZukunftCom\main\php\web\html\rest_call;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\value\value_list;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\helper\Config as shared_config;
use Zukunft\ZukunftCom\main\php\shared\types\system_time_type;
use Zukunft\ZukunftCom\main\php\shared\api;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\url_var;

class config extends value_list
{

    /*
     * const
     */

    const int LIMIT_NAME_LIST = shared_config::LIMIT_NAME_LIST;
    const int LIMIT_SEARCH_LIST = shared_config::LIMIT_SEARCH_LIST;


    /*
     * interface
     */

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


    /*
     * load
     */

    /**
     * request the user-specific frontend configuration from the backend
     * @param system_object $sys the backend system control object for the execution time tracking
     * @param string $part the config part to load e.g. the frontend config
     * @return user_message if it fails the reason why
     */
    function load(system_object $sys, string $part = api::CONFIG_FRONTEND): user_message
    {
        $msg = new user_message();
        $sys->times->switch(system_time_type::LOAD_CONFIG);

        $data = [];
        $data[url_var::CONFIG_PART] = $part;
        $data[url_var::WITH_PHRASES] = url_var::TRUE;
        $rest = new rest_call();
        $json_body = $rest->api_get(config::class, $data);
        if (array_key_exists(json_fields::MSG, $json_body)) {
            $msg->add(msg_id::API_MESSAGE, [msg_id::VAR_JSON_TEXT => $json_body[json_fields::MSG]]);
        }
        if ($msg->is_ok()) {
            $this->api_mapper($json_body);
            if ($this->is_empty()) {
                $msg->add_id(msg_id::CONFIG_API_MESSAGE_EMPTY);
            }
        }
        $sys->times->switch(system_time_type::DEFAULT);
        return $msg;
    }

    /**
     * get a frontend config value selected by the phrase names
     *
     * @param array $names with the phrase names to select the config value
     * @param int|float|string|null $default the value used if the config value is missing or zero e.g. to avoid a zero list limit
     * @return int|float|string|null with the user-specific config value or the given default
     */
    function get_by(array $names, int|float|string|null $default = null): int|float|string|null
    {
        $val = $this->get_by_names($names);
        $num = $val?->value();
        if ($num == null) {
            $num = $default;
        }
        return $num;
    }

}


