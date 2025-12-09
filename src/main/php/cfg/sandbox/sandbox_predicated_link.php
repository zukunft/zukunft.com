<?php

/*

    model/sandbox/sandbox_predicated_link.php - adding the type field to the user sandbox link superclass
    -----------------------------------------

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

namespace Zukunft\ZukunftCom\main\php\cfg\sandbox;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;

include_once paths::MODEL_SANDBOX . 'sandbox_link.php';
//include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_ENUM . 'messages.php';

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
     * @param bool $keep_user set to true to keep the original user
     */
    function reset(bool $keep_user = false): void
    {
        parent::reset($keep_user);
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
        $usr_msg = new user_message();
        $usr_msg->add_err_with_vars(msg_id::MISSING_FUNCTION_OVERWRITE, [
            msg_id::VAR_FUNCTION_NAME => 'predicate_name',
            msg_id::VAR_CLASS_NAME => $this::class
        ]);
        return $usr_msg->get_last_message();
    }

}