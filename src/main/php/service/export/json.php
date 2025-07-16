<?php

/*

    json.php - object to im- and export json files
    --------

    offer the user the long or the short version
    the short version is using one time ids for words, triples and groups

    add the instance id, user id and time stamp to the export file



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

namespace cfg\export;

include_once SERVICE_EXPORT_PATH . 'json.php';

use cfg\export\export;
use cfg\phrase\phrase_list;
use cfg\user\user;

class json_io
{

    // parameters to filter the export
    private ?user $usr = null; // the user who wants to im- or export
    private ?phrase_list $phr_lst = null;   // to export all values related to this phrase

    function __construct(?user $usr = null, ?phrase_list $phr_lst = null)
    {
        if ($usr != null and $phr_lst != null){
            $this->usr = $usr;
            $this->phr_lst = $phr_lst;
        }
    }

    // export zukunft.com data as json
    function export(): string
    {
        log_debug('json_io->export');
        $result = '';

        // get the export object
        $export_instance = new export;
        $export_obj = $export_instance->get($this->usr, $this->phr_lst);

        log_debug('json_io->export create json string');
        $result .= json_encode($export_obj);

        return $result;
    }

    // import zukunft.com data from json
    function import(): string
    {
        $result = '';

        return $result;
    }

}