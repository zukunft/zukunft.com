<?php

/*

    model/export/export_type_list.php - a list of parameters to configure the export message
    ---------------------------------


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

    Copyright (c) 1995-2024 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\cfg\export;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::EXPORT . 'export_type.php';

class export_type_list
{

    // the list of export message configuration settings
    public array $lst = [];

    /**
     * @param array $lst with the initial sql create parameter
     */
    function __construct(array $lst = [])
    {
        $this->lst = $lst;
    }

    public function set(array $lst): void
    {
        foreach ($lst as $typ) {
            if (!($typ instanceof export_type)) {
                log_err($typ . ' is expected to be an export type');
            }
        }
        $this->lst = $lst;
    }


    /*
     * modify
     */

    /**
     * add a type to the list
     * @param export_type $type the sql creation type that should be added
     * @return void
     */
    function add(export_type $type): void
    {
        if (!in_array($type, $this->lst)) {
            $this->lst[] = $type;
        }
    }

    /**
     * remove a type from the list if it has been in the list
     * @param export_type $type_to_remove the sql creation type that should be removed
     * @return bool true if the type has been in the list and has been removed
     */
    function remove(export_type $type_to_remove): bool
    {
        $result = false;
        if (($key = array_search($type_to_remove, $this->lst)) !== false) {
            unset($this->lst[$key]);
            $result = true;
        }
        return $result;
    }

    /**
     * @return bool true if the export message should include the phrases for the values
     */
    public function ignore_from(): bool
    {
        return in_array(export_type::IGNORE_FROM, $this->lst);
    }

}

