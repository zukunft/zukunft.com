<?php

/*

    model/import/wikidata.php - GET a json from wikidata and convert it into zukunft.com exchange format
    -------------------------

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

namespace Zukunft\ZukunftCom\main\php\cfg\import;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::HTML . 'rest_call.php';
include_once html_paths::REF . 'ref_list.php';
include_once html_paths::USER . 'user_message.php';
include_once paths::SHARED_CONST . 'refs.php';

use Zukunft\ZukunftCom\main\php\web\ref\ref_list;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\html\rest_call;
use Zukunft\ZukunftCom\main\php\shared\const\refs;

class wikidata
{

    const string ENTITIES = 'entities';

    function get(string $qid, user_message $usr_msg): array
    {
        $rest = new rest_call();
        $url = refs::WIKIDATA_URL . $qid . refs::WIKIDATA_EXT;
        return $rest->curl_get($url, $usr_msg);
    }

    function convert(array $wd_json, ref_list $ref_lst, user_message $usr_msg): array
    {
        $json = [];
        if (array_key_exists(self::ENTITIES, $wd_json)) {
            foreach($wd_json[self::ENTITIES] as $ex_key => $item) {
                $phr = $ref_lst->get_by_key($ex_key);
                if ($phr == null) {

                } else {

                }
            }
        }
        return $json;
    }

}
