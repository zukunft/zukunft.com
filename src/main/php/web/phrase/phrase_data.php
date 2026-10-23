<?php

/*

    web/phrase/phrase_data.php - the data of one phrase as the phraseData api sends it
    --------------------------

    e.g. the calculator tree of a phrase with its values and results for the calculator editor

    $phd is the suggested var name

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

    Copyright (c) 1995-2026 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\web\phrase;

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::HTML . 'html_base.php';
include_once html_paths::HTML . 'rest_call.php';
include_once html_paths::PHRASE . 'phrase.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::SHARED_CONST . 'rest_ctrl.php';
include_once html_paths::SHARED_ENUM . 'messages.php';
include_once html_paths::SHARED_ENUM . 'phrase_data_walks.php';
include_once html_paths::SHARED . 'json_fields.php';
include_once html_paths::SHARED . 'library.php';
include_once html_paths::SHARED . 'url_var.php';

use Zukunft\ZukunftCom\main\php\shared\const\rest_ctrl;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\enum\phrase_data_walks;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\html\rest_call;
use Zukunft\ZukunftCom\main\php\web\user\user_message;

class phrase_data
{

    /**
     * the answer of the phraseData api is the export json without the api message header, so it
     * is read by the plain api call and not by rest_call::api_get, which expects the header
     *
     * @param phrase $phr the phrase of the page e.g. example PV installation
     * @param user_message $msg with the requesting user and to report an answer without the phrase
     * @return array the words, triples, formulas, sources, values and results on the from side of
     *               the phrase with their database ids, or an empty array if the api has not sent them
     */
    function load_from_walk(phrase $phr, user_message $msg): array
    {
        $html = new html_base();
        $lib = new library();
        $api = new rest_call();
        $url = $html->url_api($lib->class_to_api_route(self::class));
        $data = [
            url_var::ID => $phr->id(),
            url_var::PHRASE_DATA_WALK => phrase_data_walks::FROM->value,
            url_var::INCL_RESULTS => url_var::TRUE
        ];
        $json = json_decode($api->api_call(rest_ctrl::GET, $url, $api->data_with_user($data, $msg)), true) ?? [];
        $result = [];
        if (array_key_exists(json_fields::SELECTION, $json)) {
            $result = $json;
        } elseif (array_key_exists(json_fields::MSG, $json)) {
            // the reason of the api e.g. an unknown phrase
            $msg->add(msg_id::API_MESSAGE, [msg_id::VAR_JSON_TEXT => $json[json_fields::MSG]]);
        } else {
            $msg->add(msg_id::API_MESSAGE_EMPTY, [msg_id::VAR_REQUEST => $url]);
        }
        return $result;
    }

}
