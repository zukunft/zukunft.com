<?php

/*

    model/phrase/trm_ids.php - helper class for term id lists
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

namespace cfg\phrase;

/**
 * helper class to make sure that
 * a word    id list is never mixed with a term id list
 * a verb    id list is never mixed with a term id list
 * a triple  id list is never mixed with a term id list
 * a formula id list is never mixed with a term id list
 * a term    id list is never mixed with a phrase id list
 */
class trm_ids
{
    public ?array $lst = null;

    function __construct(array $ids)
    {
        $this->lst = $ids;
    }

    function count(): int
    {
        return (count($this->lst));
    }
}