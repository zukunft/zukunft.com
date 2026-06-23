<?php

/*

    web/helper/user_request.php - bundle the context of a frontend user request
    ---------------------------

    combines the recurring parameters of frontend::url_to_action and
    frontend::url_user_reaction (the backend and frontend user, the message buffer,
    the frontend cache and the do_it flag) into one object so the call stays short
    var name: $req


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

    Copyright (c) 1995-2026 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\web\helper;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once paths::MODEL_USER . 'user.php';
include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::USER . 'user.php';
include_once html_paths::USER . 'user_message.php';

use Zukunft\ZukunftCom\main\php\cfg\user\user as user_backend;
use Zukunft\ZukunftCom\main\php\web\user\user as user_ui;
use Zukunft\ZukunftCom\main\php\web\user\user_message;

class user_request
{

    /*
     *  object vars
     */

    // the backend user, updated in place e.g. on login
    public user_backend $usr_backend;
    // the frontend user, updated in place e.g. on login
    public user_ui $usr;
    // the message buffer enriched with potential errors
    public user_message $usr_msg;
    // the frontend cache used to reduce the backend loading
    public data_object $dto;
    // false to skip the database execution e.g. for unit testing
    public bool $do_it;


    /*
     *  construct and map
     */

    /**
     * @param user_backend $usr_backend the backend user, updated in place e.g. on login
     * @param user_ui $usr the frontend user, updated in place e.g. on login
     * @param user_message $usr_msg the message buffer enriched with potential errors
     * @param data_object $dto the frontend cache used to reduce the backend loading
     * @param bool $do_it false to skip the database execution e.g. for unit testing
     */
    function __construct(
        user_backend $usr_backend,
        user_ui      $usr,
        user_message $usr_msg,
        data_object  $dto = new data_object(),
        bool         $do_it = true
    )
    {
        $this->usr_backend = $usr_backend;
        $this->usr = $usr;
        $this->usr_msg = $usr_msg;
        $this->dto = $dto;
        $this->do_it = $do_it;
    }

}