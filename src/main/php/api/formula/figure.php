<?php

/*

    api/formula/figure.php - the minimal figure object
    ----------------------


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

use JsonSerializable;

class figure_api extends combine_object_api implements JsonSerializable
{

    // the json field name in the api json message to identify if the figure is a value or result
    const CLASS_VALUE = 'value';
    const CLASS_RESULT = 'result';


    /*
     * construct and map
     */

    function __construct(value_api|result_api|null $val_obj = null)
    {
        $this->set_obj($val_obj);
    }


    /*
     * set and get
     */

    function id(): int
    {
        if ($this->is_result()) {
            return $this->obj_id() * -1;
        } else {
            return  $this->obj_id();
        }
    }

    /**
     * @return int the id of the containing object
     * e.g if the figure id is  1 and the object is a value  with id 1 simply 1 is returned
     * but if the figure id is -1 and the object is a result with id 1   also 1 is returned
     */
    function id_obj(): int
    {
        if ($this->obj == null) {
            return 0;
        } else {
            return $this->obj->id();
        }
    }

    /**
     * @return int|null the id of the object
     */
    function obj_id(): ?int
    {
        return $this->obj()?->id();
    }

    /**
     * @return bool true if this figure has been calculated based on other numbers
     *              false if this figure has been defined by a user
     */
    function is_result(): bool
    {
        if ($this->obj()::class == result_api::class) {
            return true;
        } else {
            return false;
        }
    }


    /*
     * interface
     */

    /**
     * @return array with the value vars including the private vars
     */
    function jsonSerialize(): array
    {
        $vars = $this->obj()->jsonSerialize();
        if ($this->is_result()) {
            $vars[combine_object_api::FLD_CLASS] = self::CLASS_RESULT;
        } else {
            $vars[combine_object_api::FLD_CLASS] = self::CLASS_VALUE;
        }
        return $vars;
    }

}
