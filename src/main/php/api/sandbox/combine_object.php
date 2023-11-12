<?php

/*

    api/sandbox/combine_object.php - parent object to combine two or four sandbox api objects
    ------------------------------

    e.g. to combine value and result to figure
    or word and triple to phrase
    or word, triple, verb and formula to term

    TODO use it for phrase
    TODO use it for term


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

namespace api\sandbox;

class combine_object_api
{

    // the json field name in the api json message to identify if the term is a word, triple, verb or formula
    const FLD_CLASS = 'class';

    /*
     * object vars
     */

    protected object $obj;


    /*
     * set and get
     */

    function set_obj(object $obj): void
    {
        $this->obj = $obj;
    }

    function obj(): object
    {
        return $this->obj;
    }


    /*
     * interface
     */

    /**
     * @return string the json api message as a text string
     */
    function get_json(): string
    {
        return json_encode($this->jsonSerialize());
    }

    /**
     * @return array with the value vars including the private vars
     * but without empty vars to save traffic
     * the function json_array of the frontend object includes empty vars
     * to allow the user to remove vars from the database
     */
    function jsonSerialize(): array
    {
        $vars = $this->obj()->jsonSerialize();
        return array_filter($vars, fn($value) => !is_null($value) && $value !== '');
    }

}
