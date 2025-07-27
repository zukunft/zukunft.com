<?php

/*

    model/helper/db_id_object_user.php - a base object for all user specific database id objects
    ----------------------------------

    same as db_object_user but for database objects that have an auto sequence prime id
    TODO should be merged once php allows aggregating extends e.g. sandbox extends db_object, db_user_object

    The main sections of this object are
    - object vars:       the variables of this seq id object
    - construct and map: including the mapping of the db row to this seq id object
    - set and get:       to capsule the single variables from unexpected changes
    - info:              functions to make code easier to read
    - modify:            change potentially all variables of this seq id object with one function


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

namespace cfg\helper;

use cfg\const\paths;

include_once paths::MODEL_HELPER . 'db_object_seq_id.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_HELPER . 'CombineObject.php';
include_once paths::SHARED . 'library.php';

use cfg\user\user;
use cfg\user\user_message;
use shared\enum\messages as msg_id;
use shared\helper\CombineObject;
use shared\library;

class db_object_seq_id_user extends db_object_seq_id
{

    /*
     * object vars
     */

    private user $usr; // the person for whom the object is loaded, so to say the viewer


    /*
     * construct and map
     */

    /**
     * @param user $usr the user how has requested to see his view on the object
     */
    function __construct(user $usr)
    {
        parent::__construct();
        $this->set_user($usr);
    }


    /*
     * set and get
     */

    /**
     * set the user of the user sandbox object
     *
     * @param user $usr the person who wants to access the object e.g. the word
     * @return void
     */
    function set_user(user $usr): void
    {
        $this->usr = $usr;
    }

    /**
     * @return user the person who wants to see a word, verb, triple, formula, view or result
     */
    function user(): user
    {
        return $this->usr;
    }

    /**
     * @return int the id of the user or 0 if the user is not set
     */
    function user_id(): int
    {
        return $this->usr->id();
    }


    /*
     * info
     */

    /**
     * create human-readable messages of the differences between the db id objects
     * @param CombineObject|db_object_seq_id_user|db_object_seq_id $obj which might be different to this db id object
     * @return user_message the human-readable messages of the differences between the db id objects
     */
    function diff_msg(CombineObject|db_object_seq_id_user|db_object_seq_id $obj): user_message
    {
        $usr_msg = parent::diff_msg($obj);
        if ($this->user_id() != $obj->user_id()) {
            $lib = new library();
            $usr_msg->add_id_with_vars(msg_id::DIFF_USER, [
                msg_id::VAR_USER => $obj->user()->dsp_id(),
                msg_id::VAR_USER_CHK => $this->user()->dsp_id(),
                msg_id::VAR_CLASS_NAME => $lib->class_to_name($this::class),
                msg_id::VAR_NAME => $this->dsp_id(),
            ]);
        }
        return $usr_msg;
    }


    /*
     * modify
     */

    /**
     * fill this db user object based on the given object
     * if the given user id is not set (null) the user id is set
     *
     * @param CombineObject|db_object_seq_id_user|db_object_seq_id $obj sandbox object with the values that should be updated e.g. based on the import
     * @param user $usr_req the user who has requested the fill
     * @return user_message a warning in case of a conflict e.g. due to a missing change time
     */
    function fill(CombineObject|db_object_seq_id_user|db_object_seq_id $obj, user $usr_req): user_message
    {
        $usr_msg = parent::fill($obj, $usr_req);
        if ($obj->user_id() != null) {
            $this->set_user($obj->user());
        }
        return $usr_msg;
    }


    /*
     * debug
     */

    /**
     * @returns string best possible identification for this object mainly used for debugging
     */
    function dsp_id_user(): string
    {
        global $debug;
        $result = '';
        if ($debug > DEBUG_SHOW_USER or $debug == 0) {
            if ($this->user() != null) {
                $result .= ' for user ' . $this->user()->id() . ' (' . $this->user()->name . ')';
            }
        }
        return $result;
    }

}
