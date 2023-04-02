<?php

/*

    api/sandbox/combine_named.php - parent object for a phrase or term api objects
    -----------------------------

    phrase and term have the fields name, description and type in common


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

namespace api;

include_once API_SANDBOX_PATH . 'combine_object.php';

class combine_named_api extends combine_object_api
{

    /*
     * construct and map
     */

    function __construct(
        int    $id = 0,
        string $name = '',
        string $description = null,
        int    $type_id = null)
    {
        $this->set_obj_id($id);
        $this->set_name($name);
        $this->set_description($description);
        $this->set_type_id($type_id);
    }

    /**
     * set the object vars of a phrase or term to the neutral initial value
     */
    function reset(): void
    {
        $this->set_obj_id(0);
        $this->set_name('');
        $this->set_description(null);
        $this->set_type_id(null);
    }


    /*
     * set and get
     */

    /**
     * @param int $id the id of the object
     * e.g. 1 for the triple Pi (math)
     * the id of the phrase or term is
     * created dynamically by the child class
     */
    function set_obj_id(int $id): void
    {
        $this->obj()?->set_id($id);
    }

    /**
     * @return int the id of the object
     * e.g. 1 for the triple Pi (math)
     * whereas the phrase has the id -1
     * the id of the phrase or term is created
     * by the function id() of phrase or term
     */
    function obj_id(): int
    {
        return $this->obj()?->id();
    }

    /**
     * @param string $name the name of the word, triple, formula or verb
     * @return void
     */
    function set_name(string $name): void
    {
        $this->obj()?->set_name($name);
    }

    /**
     * @return string|null the name of the word, triple, formula or verb
     */
    function name(): ?string
    {
        return $this->obj()?->name();
    }

    /**
     * @param string|null $description the description of the word, triple, formula or verb
     * @return void
     */
    function set_description(?string $description): void
    {
        $this->obj()?->set_description($description);
    }

    /**
     * @return string|null the description of the word, triple, formula or verb
     */
    function description(): ?string
    {
        return $this->obj()?->description();
    }

    /**
     * @param int|null $type_id the type id of the word, triple, formula or verb
     * @return void
     */
    function set_type_id(?int $type_id): void
    {
        $this->obj()?->set_type_id($type_id);
    }

    /**
     * @return int|null the type id of the word, triple, formula or verb
     * if null the type of related phrase or term can be used
     * e.g. if the type of the triple "Pi (math)" is not set
     * but the triple is "Pi is a math const" and the type for "math const" is set it is used
     */
    function type_id(): ?int
    {
        return $this->obj()?->type_id();
    }

}
