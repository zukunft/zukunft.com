<?php

/*

    model/phrase/fig_ids.php - helper class for figure id lists
    ------------------------


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

namespace cfg\formula;

/**
 * helper class to make sure that
 * a value  id list is never mixed with a figure id list
 * a result id list is never mixed with a figure id list
 */
class fig_ids
{
    public ?array $lst = null;

    function __construct(array|string $ids)
    {
        if (is_string($ids)) {
            $this->set_by_txt($ids);
        } else {
            $this->lst = $ids;
        }
    }

    function set_by_txt(string $cst): void
    {
        $this->lst = explode(",", $cst);
    }

    function count(): int
    {
        return (count($this->lst));
    }
}