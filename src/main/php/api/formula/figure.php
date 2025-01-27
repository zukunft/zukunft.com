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

namespace api\formula;

include_once API_SANDBOX_PATH . 'combine_object.php';
include_once SHARED_PATH . 'json_fields.php';

use api\sandbox\combine_object as combine_object_api;
use JsonSerializable;

class figure extends combine_object_api implements JsonSerializable
{

    // the json field name in the api json message to identify if the figure is a value or result
    const CLASS_VALUE = 'value';
    const CLASS_RESULT = 'result';


    /**
     * @return int the id of the containing object
     * e.g. if the figure id is  1 and the object is a value  with id 1 simply 1 is returned
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

}
