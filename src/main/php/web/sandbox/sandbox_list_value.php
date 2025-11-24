<?php

/*

    web/sandbox/sandbox_list_value.php - the minimal list object for values
    ----------------------------------

    unlike value_list_min this is the parent object for all lists that have values
    e.g. used for the value and formula result api object


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

namespace Zukunft\ZukunftCom\main\php\web\sandbox;

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::SANDBOX . 'sandbox_list_named.php';
include_once html_paths::PHRASE . 'phrase_list.php';

use Zukunft\ZukunftCom\main\php\web\phrase\phrase_list;

class sandbox_list_value extends sandbox_list_named
{

    function __construct(?string $api_json = null)
    {
        parent::__construct($api_json);
    }


    /*
     * modify
     */

    /**
     * @returns phrase_list with the phrases that are used in all values of the list
     */
    protected function common_phrases(): phrase_list
    {
        // get common words
        $common_phr_lst = new phrase_list();
        foreach ($this->lst() as $val) {
            if ($val != null) {
                if ($val->phr_lst() != null) {
                    if ($val->phr_lst()->lst() != null) {
                        $common_phr_lst->intersect($val->phr_lst());
                    }
                }
            }
        }
        return $common_phr_lst;
    }

}
