<?php

/*

    model/helper/object_mapper.php - a library class to collect the backend object mappings
    ------------------------------


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

namespace Zukunft\ZukunftCom\main\php\cfg\helper;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

//include_once paths::MODEL_PHRASE . 'term.php';
//include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_ENUM . 'messages.php';

use Zukunft\ZukunftCom\main\php\cfg\phrase\term;
use Zukunft\ZukunftCom\main\php\cfg\ref\source;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\view\view;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\json_fields;

class object_mapper
{

    /**
     * get or create a word, verb, triple or formula by the name or the json array from this cache object
     * @param array $json an array with the data of the json object
     * @param user_message $msg to enrich with warnings, problems and solutions
     * @param data_object|null $dto cache of the objects imported until now for the primary references
     * @return term|null the term from cache or null if not found in cache
     */
    function get_term(
        array        $json,
        user_message $msg,
        ?data_object $dto = null
    ): ?term
    {
        if (key_exists(json_fields::TERM, $json)) {
            $trm_json = $json[json_fields::TERM];
            if (is_array($trm_json)) {
                $trm = new term($msg->usr);
                $trm->import_mapper($trm_json, $msg);
            } else {
                $trm = $dto?->get_term_by_name($trm_json);
                if ($trm == null) {
                    $msg->add(msg_id::TERM_MISSING_IMPORT, [
                        msg_id::VAR_TERM => $trm_json,
                        msg_id::VAR_JSON_TEXT => json_encode($json)
                    ]);
                    $trm = new term($msg->usr);
                    $trm->set_name($trm_json);
                }
            }
            return $trm;
        } else {
            return null;
        }
    }

    /**
     * get or create a view by the name or the json array from this cache object
     * @param array $json an array with the data of the json object
     * @param user_message $msg to enrich with warnings, problems and solutions
     * @param data_object|null $dto cache of the objects imported until now for the primary references
     * @return view|null the term from cache or null if not found in cache
     */
    function get_view(
        array        $json,
        user_message $msg,
        ?data_object $dto = null
    ): ?view
    {
        if (key_exists(json_fields::VIEW, $json)) {
            $msk_json = $json[json_fields::VIEW];
            if (is_array($msk_json)) {
                $msk = new view($msg->usr);
                $msk->import_mapper($msk_json, $msg);
            } else {
                $msk = $dto?->get_view_by_name($msk_json);
                if ($msk == null) {
                    $msg->add(msg_id::VIEW_MISSING_IMPORT, [
                        msg_id::VAR_VIEW => $msk_json,
                        msg_id::VAR_JSON_TEXT => json_encode($json)
                    ]);
                    $msk = new view($msg->usr);
                    $msk->set_name($msk_json);
                }
            }
            return $msk;
        } else {
            return null;
        }
    }

    /**
     * get or create a source by the name or the json array from this cache object
     * @param array $json an array with the data of the json object
     * @param user_message $msg to enrich with warnings, problems and solutions
     * @param data_object|null $dto cache of the objects imported until now for the primary references
     * @return source|null the term from cache or null if not found in cache
     */
    function get_source(
        array        $json,
        user_message $msg,
        ?data_object $dto = null
    ): ?source
    {
        if (key_exists(json_fields::VIEW, $json)) {
            $src_json = $json[json_fields::VIEW];
            if (is_array($src_json)) {
                $src = new source($msg->usr);
                $src->import_mapper($src_json, $msg);
            } else {
                $src = $dto?->get_source_by_name($src_json);
                if ($src == null) {
                    $msg->add(msg_id::SOURCE_MISSING_IMPORT, [
                        msg_id::VAR_SOURCE => $src_json,
                        msg_id::VAR_JSON_TEXT => json_encode($json)
                    ]);
                    $src = new source($msg->usr);
                    $src->set_name($src_json);
                }
            }
            return $src;
        } else {
            return null;
        }
    }

}
