<?php

/*

    api/formula/figure_list.php - a list object of api figure objects
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

    Copyright (c) 1995-2023 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace api\formula;

include_once API_SANDBOX_PATH . 'list_object.php';
include_once API_FORMULA_PATH . 'figure.php';

use api\sandbox\list_object as list_api;
use JsonSerializable;

class figure_list extends list_api implements JsonSerializable
{

    /*
     * construct and map
     */

    function __construct(array $lst = array())
    {
        parent::__construct($lst);
    }

    /**
     * add a figure to the list
     * @returns bool true if the figure has been added
     */
    function add(figure $phr): bool
    {
        return parent::add_obj($phr);
    }


    /*
     * interface
     */

    /**
     * @return array with the value vars including the private vars
     */
    function jsonSerialize(): array
    {
        $vars = [];
        foreach ($this->lst() as $fig) {
            $vars[] = json_decode(json_encode($fig));
        }
        return $vars;
    }

}
