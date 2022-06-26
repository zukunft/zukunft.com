<?php

/*

    api\list_value.php - the minimal list object for values
    ------------------

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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace api;

class list_value_api extends list_api
{

    function __construct(array $lst = array())
    {
        parent::__construct($lst);
    }

    /**
     * @returns phrase_list_api with the phrases that are used in all values of the list
     */
    protected function common_phrases(): phrase_list_api
    {
        // get common words
        $common_phr_lst = new phrase_list_api();
        foreach ($this->lst as $val) {
            if ($val != null) {
                if ($val->phr_lst() != null) {
                    if ($val->phr_lst()->lst != null) {
                        $common_phr_lst->intersect($val->phr_lst());
                    }
                }
            }
        }
        return $common_phr_lst;
    }

}
