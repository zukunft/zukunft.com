<?php

/*

    word_type.php - the word type setting
    -------------

    types are used to assign coded functionality to a word, formula or view
    a user can create a new type to group words, formulas or views and request new functionality for the group
    types can be renamed by a user and the user change the comment
    it should be possible to translate types on the fly
    on each program start the types are loaded once into an array, because they are not supposed to change during execution

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

use cfg\object_type;
use cfg\phrase_type;

class word_type extends object_type
{

    const DEFAULT = phrase_type::NORMAL;

    /*
     * construct and map
     */

    function __construct(string $code_id, int $id = 0, string $name = '')
    {
        $this->code_id = $code_id;
        $this->id = $id;
        $this->name = $name;
    }

    function code_id(): string
    {
        return $this->code_id;
    }
}