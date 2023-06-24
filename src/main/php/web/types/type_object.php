<?php

/*

    /model/phrase/type_object.php - the superclass for word, formula and view types
    -----------------------------

    a base type object that can be used to link program code to single objects
    e.g. if a value is classified by a phrase of type percent the value by default is formatted in percent

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

namespace html\types;

include_once API_SANDBOX_PATH . 'type_object.php';

use api\type_object_api;
use cfg\type_list;
use cfg\db_cl;
use cfg\sql_db;
use cfg\sql_par;

class type_object
{

    /*
     * object vars
     */

    // the standard fields of a type
    public int $id;                // the database id is also used as the array pointer
    public string $name;           // simply the type name as shown to the user
    public string $code_id;        // this id text is unique for all code links and is used for system im- and export
    public ?string $comment = '';  // to explain the type to the user as a tooltip


    /*
     * construct and map
     */

    function __construct(int $id, ?string $code_id, string $name = '', string $comment = '')
    {
        $this->set_id($id);
        $this->set_name($name);
        $this->set_code_id($code_id);
        if ($comment != '') {
            $this->set_comment($comment);
        }
    }


    /*
     * set and get
     */

    function set_id(int $id): void
    {
        $this->id = $id;
    }

    function set_name(string $name): void
    {
        $this->name = $name;
    }

    function set_code_id(string $code_id): void
    {
        $this->code_id = $code_id;
    }

    function set_comment(string $comment): void
    {
        $this->comment = $comment;
    }

    function id(): int
    {
        return $this->id;
    }

    function name(): string
    {
        return $this->name;
    }

    function code_id(): string
    {
        return $this->code_id;
    }

    function comment(): string
    {
        return $this->comment;
    }


}
