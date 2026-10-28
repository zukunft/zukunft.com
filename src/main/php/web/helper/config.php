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

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::MODEL_HELPER . 'system_object.php';
include_once html_paths::HTML . 'rest_call.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::VALUE . 'value.php';
include_once html_paths::VALUE . 'value_list.php';
include_once html_paths::SHARED_ENUM . 'messages.php';
include_once html_paths::SHARED_CONST . 'triples.php';
include_once html_paths::SHARED_CONST . 'views.php';
include_once html_paths::SHARED_CONST . 'words.php';
include_once html_paths::SHARED_HELPER . 'Config.php';
include_once html_paths::SHARED_TYPES . 'system_time_type.php';
include_once html_paths::SHARED . 'api.php';
include_once html_paths::SHARED . 'json_fields.php';
include_once html_paths::SHARED . 'url_var.php';

use Zukunft\ZukunftCom\main\php\cfg\helper\system_object;
use Zukunft\ZukunftCom\main\php\web\html\rest_call;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\value\value;
use Zukunft\ZukunftCom\main\php\web\value\value_list;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\const\words;
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
    const int LIMIT_LINK_LIST = shared_config::LIMIT_LINK_LIST;
    const int LIMIT_SEARCH_LIST = shared_config::LIMIT_SEARCH_LIST;
    const int LIMIT_PHRASE_PRELOAD = shared_config::LIMIT_PHRASE_PRELOAD;
    const string DEFAULT_FRONTEND_TYPE = shared_config::DEFAULT_FRONTEND_TYPE;
    // the config.yaml key of each precision tier (number format > digits) with its fallback number of digits
    const array DIGITS_FALLBACK = [
        words::DEFAULT => shared_config::DEFAULT_DIGITS,
        words::PRECISE => shared_config::PRECISE_DIGITS,
        triples::VERY_PRECISE => shared_config::VERY_PRECISE_DIGITS,
        words::ROUGH => shared_config::ROUGH_DIGITS,
        triples::VERY_ROUGH => shared_config::VERY_ROUGH_DIGITS,
    ];


    /*
     * interface
     */

    /**
     * @param user_message $msg to report a problem of reading the config
     * @return string the frontend type of the user e.g. shared_config::FRONTEND_PURE_HTML
     */
    function frontend_type(user_message $msg): string
    {
        $names = [words::TYPE, words::FRONTEND, words::USER];
        return (string)$this->get_by($names, $msg, self::DEFAULT_FRONTEND_TYPE);
    }

    /**
     * the view that adds a value for the given frontend type: a pure html frontend selects the phrases of the
     * new value step by step, while a frontend with javascript selects them in the add value form
     *
     * @param string $type the frontend type e.g. shared_config::FRONTEND_PURE_HTML
     * @return int the id of the view that adds a value
     */
    static function value_add_view(string $type): int
    {
        $result = views::VALUE_ADD_DETAIL_ID;
        if ($type == shared_config::FRONTEND_PURE_HTML) {
            $result = views::VALUE_ADD_NO_JS_ID;
        }
        return $result;
    }

    // TODO Prio 2 add the user setting as default
    function percent_decimals(): int
    {
        return shared_config::DEFAULT_PERCENT_DECIMALS;
    }

    /**
     * the full precision tier has no number of digits, because it shows all decimals
     *
     * @param string $tier the config.yaml key of the precision tier e.g. words::PRECISE
     * @return int the number of digits shown of a number of the tier (config.yaml number format > digits),
     *             the shared config const of the tier if config.yaml has none, and of the default tier for an unknown tier
     */
    function digits(string $tier = words::DEFAULT): int
    {
        $tier = array_key_exists($tier, self::DIGITS_FALLBACK) ? $tier : words::DEFAULT;
        $val = $this->get_by_names_specific([triples::NUMBER_FORMAT, words::DIGITS, $tier]);
        return $val?->number() === null ? self::DIGITS_FALLBACK[$tier] : (int)$val->number();
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
     * @param user_message $msg to collect the load errors for the user
     * @param string $part the config part to load e.g. the frontend config
     * @return user_message if it fails the reason why
     */
    function load(system_object $sys, user_message $msg, string $part = api::CONFIG_FRONTEND): user_message
    {
        $sys->times->switch(system_time_type::LOAD_CONFIG);

        $data = [];
        $data[url_var::CONFIG_PART] = $part;
        $data[url_var::WITH_PHRASES] = url_var::TRUE;
        $rest = new rest_call();
        $json_body = $rest->api_get(config::class, $data, $msg);
        if (array_key_exists(json_fields::MSG, $json_body)) {
            $msg->add(msg_id::API_MESSAGE, [msg_id::VAR_JSON_TEXT => $json_body[json_fields::MSG]]);
        }
        if ($msg->is_ok()) {
            $this->api_mapper($json_body);
            if ($this->is_empty()) {
                $msg->add_id(msg_id::CONFIG_API_MESSAGE_EMPTY);
            }
        }
        // continue with the section of the caller
        $sys->times->switch();
        return $msg;
    }

    /**
     * get a frontend config value selected by the phrase names
     *
     * @param array $names with the phrase names to select the config value
     * @param user_message $msg to collect the retrival messages
     * @param int|float|string|null $default the value used if the config value is missing or zero e.g. to avoid a zero list limit
     * @return int|float|string|null with the user-specific config value or the given default
     */
    function get_by(array $names, user_message $msg, int|float|string|null $default = null): int|float|string|null
    {
        $val = $this->get_by_names($names);
        $num = $val?->value($msg);
        if ($num == null) {
            $num = $default;
        }
        return $num;
    }

    /**
     * the raw text of a frontend config value e.g. a font or a colour of the page head, because
     * get_by returns the html escaped text e.g. &quot; for the quotes of "Segoe UI"; the most specific
     * value, because e.g. the names of a light editor colour are also the names of its dark colour
     *
     * @param array $names with the phrase names to select the config value
     * @return string|null the text of the config value or null if the config has no text value with these names
     */
    function text_by(array $names, array $without = []): ?string
    {
        return $this->value_by($names, $without)?->text_value();
    }

    /**
     * the most specific frontend config value e.g. to save the dark mode of the user as an overwrite
     * of the config value (see html_base::theme_switch)
     *
     * @param array $names with the phrase names to select the config value
     * @param array $without the phrase names that the config value must not have e.g. "dark" for a light colour
     * @return value|null the config value with all the names and the fewest other phrases
     */
    function value_by(array $names, array $without = []): ?value
    {
        return $this->get_by_names_specific($names, $without);
    }

    /**
     * the config value with all the names and the fewest other phrases, because the path of a
     * value may contain a name of a sibling key too e.g. "default" in "user > default > frontend"
     * of every precision tier, so the first match would depend on the order of the list
     *
     * @param array $names the phrase names that the config value must have
     * @param array $without the phrase names that the config value must not have
     * @return value|null the most specific config value or null if no value has all the names
     */
    private function get_by_names_specific(array $names, array $without = []): ?value
    {
        $result = null;
        foreach ($this->lst() as $val) {
            if ($val->match_all($names)
                and array_intersect($without, $val->phr_lst()->names()) == []
                and ($result == null
                    or count($val->phr_lst()->names()) < count($result->phr_lst()->names()))) {
                $result = $val;
            }
        }
        return $result;
    }

}


