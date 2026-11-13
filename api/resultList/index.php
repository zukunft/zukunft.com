<?php

/*

    api/resultList/index.php - the result list API controller
    ------------------------

    send a list of results to the frontend


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

include_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'api_const.php';

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_HELPER . 'db_cache_page.php';
include_once paths::MODEL_PHRASE . 'phr_ids.php';
include_once paths::MODEL_PHRASE . 'phrase_list.php';
include_once paths::MODEL_RESULT . 'result_list.php';
include_once paths::SHARED_TYPES . 'api_types.php';
include_once paths::SHARED_TYPES . 'system_time_type.php';
include_once paths::SHARED . 'api.php';

use Zukunft\ZukunftCom\main\php\cfg\application;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_cache_page;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phr_ids;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\cfg\result\result_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\api\controller;
use Zukunft\ZukunftCom\main\php\shared\api;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\shared\types\system_time_type;
use Zukunft\ZukunftCom\main\php\shared\url_var;

// init api app and open database
$app = new application();
$msg = new user_message(); // for api
$db_con = $app->start_api("resultList", $msg);

if ($db_con->is_open()) {

    // load the session user parameters store the requesting user on the single message
    $usr = new user;
    $usr->get($msg);
    $msg->usr = $usr;

    $result = ''; // reset the json message string

    // get the parameters
    // TODO Prio 2 use a json with the ids
    // TODO Prio 2 add load by formula and source
    $ids = $_GET[url_var::ID_LST] ?? '';
    $phr_ids = $_GET[api::JSON_LIST_PHRASE_IDS] ?? '';
    $ctx_ids = $_GET[api::JSON_LIST_CONTEXT_IDS] ?? '';
    $page = (int)($_GET[api::JSON_LIST_PAGE] ?? 0);

    // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
    $cac_key = '';
    // an object also without a user, so that the cache header can read why nothing has been cached
    $cac_page = new db_cache_page();
    if ($usr->id > 0) {
        $cac_par = [
            url_var::ID_LST => $ids,
            api::JSON_LIST_PHRASE_IDS => $phr_ids,
            api::JSON_LIST_CONTEXT_IDS => $ctx_ids,
            api::JSON_LIST_PAGE => $page
        ];
        $cac_key = db_cache_page::api_key(result_list::class, $cac_par, $usr->id);
        $cac_page = new db_cache_page();
        // each step in a section of its own, so that the time report of the frontend splits the wait for this answer
        $sys->times->switch(system_time_type::API_CACHE);
        $result = $cac_page->json_by_key($cac_key, $msg);
        $sys->times->switch();
    }
    // without a user the result stays empty, which is no cached answer
    $cac_hit = ($usr->id > 0 and $result !== null);
    if ($usr->id > 0 and $result === null) {
        $result = '';
        // the snap is taken before the results are read, so a change during the read outdates the entry
        $snap_time = new DateTime();

        if ($phr_ids != '') {
            // the loads book their database read and mapping themselves, so that their other work stays api_ctrl
            $phr_lst = new phrase_list($usr);
            $phr_lst->load_names_by_ids(new phr_ids(explode(",", $phr_ids)), $msg);
            $lst = new result_list($usr);
            // any result of any of the given phrases, e.g. the results of all global problems,
            // because a result normally names only one of the requested phrases
            // paged, so that the results of many phrases are complete: the answer names a next page if it is full,
            // counted before the context filter, because a filtered page can be short although a next page exists
            $lst->load_by_phrase_list($phr_lst, $msg, true, page: $page, paged: true);
            $sys->times->switch(system_time_type::API_FILTER);
            // only the results within the context that a page can use, e.g. of the initial effort only the
            // results of the rows of a table, filtered before the phrases are loaded for the few that are left
            if ($ctx_ids != '') {
                $lst->filter_within_phrases(explode(",", $ctx_ids));
            }
            // drop the results the requesting user may not read (idor); see sandbox_multi::is_readable_by
            $lst->filter_readable_by($usr);
            $sys->times->switch();
            // the frontend names the rows and the columns of a value table by the phrases of each
            // result, so the group phrases are loaded and emitted with the results, each phrase once
            $sys->times->switch(system_time_type::LOAD_RELATED);
            $lst->load_phrases($msg);
            $sys->times->switch();
            $sys->times->switch(system_time_type::MAP_JSON);
            $result = $lst->api_json([api_types::INCL_PHRASES, api_types::PHRASES_ONCE], $msg);
            $sys->times->switch();
        } elseif ($ids != '') {
            $ids = explode(",", $ids);
            $lst = new result_list($usr);
            $lst->load_by_ids($ids, $msg);
            // drop the results the requesting user may not read (idor); see sandbox_multi::is_readable_by
            $lst->filter_readable_by($usr);
            $result = $lst->api_json([], $msg);
        } else {
            $msg->add_message_text('result or phrase id list is missing');
        }
        $sys->times->switch(system_time_type::API_CACHE);
        $cac_page->save_json($cac_key, $result, $snap_time, $msg);
        $sys->times->switch();
    }

    $ctrl = new controller();
    $ctrl->cache_header($cac_hit, $cac_page->not_saved);
    $ctrl->get_json($result, $msg);


    $app->end_api($db_con, $msg);
}