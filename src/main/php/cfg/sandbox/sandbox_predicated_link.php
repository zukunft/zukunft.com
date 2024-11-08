<?php

/*

    model/sandbox/sandbox_link_with_type.php - adding the type field to the user sandbox link superclass
    ----------------------------------------

    similar to sandbox_link_named, but for links without name


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

namespace cfg;

use shared\api;

include_once MODEL_SANDBOX_PATH . 'sandbox_link.php';

class sandbox_predicated_link extends sandbox_link
{

    /*
     * object vars
     */


    /*
     * construct and map
     */

    /**
     * reset the type of the link object
     */
    function reset(): void
    {
        parent::reset();
    }


    /*
     * settings
     */

    /**
     * @return bool true because all child objects use the link type
     */
    function is_link_type_obj(): bool
    {
        return true;
    }


    /*
     * preloaded
     */

    /**
     * dummy function that should be overwritten by the child object
     * @return string the name of the object type
     */
    function predicate_name(): string
    {
        $msg = 'ERROR: the type name function should have been overwritten by the child object';
        return log_err($msg);
    }

}