<?php

/*

    web/helper/data_object.php - a header object for all frontend data objects e.g. phrase_list, values, formulas
    --------------------------


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

namespace html\helper;

use cfg\const\paths;
use html\const\paths as html_paths;
include_once html_paths::PHRASE . 'phrase_list.php';
include_once html_paths::TYPES . 'type_lists.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::VIEW . 'view_list.php';
include_once html_paths::WORD . 'word_list.php';
include_once paths::SHARED . 'json_fields.php';

use html\phrase\phrase_list;
use html\types\type_lists;
use html\user\user_message;
use html\view\view_list;
use html\word\word_list;
use shared\json_fields;

class data_object
{

    /*
     *  object vars
     */

    private word_list $wrd_lst;
    private phrase_list $phr_lst;
    private view_list $msk_lst;
    public ?type_lists $typ_lst_cache = null;

    // for warning and errors while filling the data_object
    private user_message $usr_msg;
    // set to false if the api should not be used to reload missing data
    private bool $online;


    /*
     * construct and map
     */

    /**
     * init the data object vars and set the lists based on the given api json
     * @param string|null $api_json string with the api json message to fill the list
     */
    function __construct(?string $api_json = null)
    {
        if ($api_json != null) {
            $this->set_from_json($api_json);
        } else {
            $this->reset();
        }
    }

    function reset(): void
    {
        $this->wrd_lst = new word_list();
        $this->phr_lst = new phrase_list();
        $this->msk_lst = new view_list();
        $this->usr_msg = new user_message();
        $this->online = true;
    }


    /*
     * set and get
     */

    /**
     * set the vars of these list display objects bases on the api message
     * @param string $json_api_msg an api json message as a string
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function set_from_json(string $json_api_msg): user_message
    {
        $usr_msg = new user_message();
        $this->reset();
        $json_array = json_decode($json_api_msg, true);
        if (array_key_exists(json_fields::WORDS, $json_array)) {
            $msg = $this->wrd_lst->api_mapper($json_array[json_fields::WORDS]);
            $usr_msg->add($msg);
        }
        return $usr_msg;
    }

    /**
     * set the view_list of this data object
     * @param view_list $msk_lst
     */
    function set_view_list(view_list $msk_lst): void
    {
        $this->msk_lst = $msk_lst;
    }

    /**
     * @return view_list with the view of this data object
     */
    function view_list(): view_list
    {
        return $this->msk_lst;
    }

    /**
     * @return bool true if this context object contains a view list
     */
    function has_view_list(): bool
    {
        if ($this->msk_lst->count() > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool true if this context object contains at least some phrases
     */
    function has_phrases(): bool
    {
        if ($this->phr_lst->count() > 0) {
            return true;
        } else {
            return false;
        }
    }

    function add_phrases(phrase_list $phr_lst): void
    {
        foreach ($phr_lst->lst() as $phr) {
            $this->phr_lst->add($phr);
        }
    }

    function phrase_list(): phrase_list
    {
        return $this->phr_lst;
    }

    function set_online(): void
    {
        $this->online = true;
    }

    function set_offline(): void
    {
        $this->online = false;
    }

}
