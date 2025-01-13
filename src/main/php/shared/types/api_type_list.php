<?php

/*

    shared/types/api_type_list.php - a list of parameters to configure the api message
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

    Copyright (c) 1995-2024 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace shared\types;

use cfg\db\sql_type;

class api_type_list
{

    // the list of api message configuration settings
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
            if (!($typ instanceof api_type)) {
                log_err($typ . ' is expected to be an api type');
            }
        }
        $this->lst = $lst;
    }


    /*
     * modify
     */

    /**
     * add a type to the list
     * @param api_type $type the sql creation type that should be added
     * @return void
     */
    function add(api_type $type): void
    {
        if (!in_array($type, $this->lst)) {
            $this->lst[] = $type;
        }
    }

    /**
     * remove a type from the list if it has been in the list
     * @param api_type $type_to_remove the sql creation type that should be removed
     * @return bool true if the type has been in the list and has been removed
     */
    function remove(api_type $type_to_remove): bool
    {
        $result = false;
        if (($key = array_search($type_to_remove, $this->lst)) !== false) {
            unset($this->lst[$key]);
            $result = true;
        }
        return $result;
    }

    /**
     * @return bool true if the api message should include the phrases for the values
     */
    public function include_phrases(): bool
    {
        return in_array(api_type::INCL_PHRASES, $this->lst);
    }

    /**
     * @return bool true if the keys should not be filled to the full key length
     */
    public function no_key_fill(): bool
    {
        return in_array(api_type::NO_KEY_FILL, $this->lst);
    }

    /**
     * @return bool false to switch off the database load for unit tests
     */
    public function test_mode(): bool
    {
        return in_array(api_type::TEST_MODE, $this->lst);
    }

}

