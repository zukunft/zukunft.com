<?php

/*

    web/sandbox/combine_named.php - parent object for a phrase or term display objects
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

namespace html\sandbox;

use cfg\const\paths;
use html\const\paths as html_paths;

include_once html_paths::HTML . 'rest_call.php';
include_once html_paths::SANDBOX . 'combine_object.php';
include_once paths::SHARED_CONST . 'rest_ctrl.php';
include_once paths::SHARED . 'json_fields.php';

use html\rest_call as api_dsp;
use shared\json_fields;

class combine_named extends combine_object
{

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
     * @return int|string|null the id of the object
     * e.g. 1 for the triple Pi (math)
     * whereas the phrase has the id -1
     * the id of the phrase or term is created
     * by the function id() of phrase or term
     */
    function obj_id(): int|string|null
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
     * @return string|null the plural of the word, triple, formula or verb
     */
    function plural(): ?string
    {
        return $this->obj()?->plural();
    }

    /**
     * TODO review and use only frontend objects
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


    /*
     * load
     */

    /**
     * load the phrase by name via api
     * @param string $name
     * @return bool
     */
    function load_by_name(string $name): bool
    {
        $result = false;

        $api = new api_dsp();
        $json_body = $api->api_call_name($this::class, $name);
        if ($json_body) {
            $this->api_mapper($json_body);
            if ($this->obj_id() != 0) {
                $result = true;
            }
        }
        return $result;
    }


    /*
     * interface
     */

    /**
     * @return array the json message array to send the updated data to the backend
     */
    function api_array(): array
    {
        $vars = array();
        $vars[json_fields::ID] = $this->obj()?->id();
        $vars[json_fields::NAME] = $this->name();
        $vars[json_fields::DESCRIPTION] = $this->description();
        $vars[json_fields::TYPE] = $this->type_id();
        return $vars;
    }


    /*
     * debug
     */

}
