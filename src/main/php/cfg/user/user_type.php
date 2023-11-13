<?php

/*

    model/user/user_type.php - the superclass for word, formula and view types
    ------------------------

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

    Copyright (c) 1995-2023 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

// TODO combine with class type_object

use api\sandbox\type_object_api;

class user_type
{

    // database and JSON object field names
    const FLD_NAME = 'type_name';

    // the standard fields of a type
    public int $id;                // the database id is also used as the array pointer
    public string $code_id;        // this id text is unique for all code links and is used for system im- and export
    public string $name;           // simply the type name as shown to the user
    public ?string $comment = '';  // to explain the type to the user as a tooltip


    /*
     * construct and map
     */

    function __construct(string $code_id, string $name, string $comment = '', int $id = 0)
    {
        $this->id = $id;
        $this->set_code_id($code_id);
        $this->set_name($name);
        if ($comment != '') {
            $this->set_comment($comment);
        }
    }

    function reset(): void
    {
        $this->id = 0;
        $this->code_id = '';
        $this->name = '';
        $this->comment = null;
    }


    /*
     * set and get
     */

    function set_code_id(string $code_id): void
    {
        $this->code_id = $code_id;
    }

    function code_id(): string
    {
        return $this->code_id;
    }

    function set_name(string $name): void
    {
        $this->name = $name;
    }

    function name(): string
    {
        return $this->name;
    }

    function set_comment(string $comment): void
    {
        $this->comment = $comment;
    }

    function comment(): string
    {
        return $this->comment;
    }


    /*
     * cast
     */

    /**
     * @return type_object_api the code link frontend api object
     */
    function api_obj(): type_object_api
    {
        $api_obj = new type_object_api();
        $api_obj->id = $this->id;
        $api_obj->name = $this->name;
        $api_obj->code_id = $this->code_id;
        return $api_obj;
    }

}