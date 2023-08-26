<?php

/*

    web\ref\source_list.php - create the HTML code to display a source list
    -----------------------

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

namespace html\ref;

include_once WEB_SANDBOX_PATH . 'list.php';

use cfg\phrase_type;
use html\word\word as word_dsp;
use html\html_base;
use html\list_dsp;
use html\word\word_list;

class source_list extends list_dsp
{

    /*
     * set and get
     */

    /**
     * set the vars of a word object based on the given json
     * @param array $json_array an api single object json message
     * @return object a word set based on the given json
     */
    function set_obj_from_json_array(array $json_array): object
    {
        $wrd = new word_dsp();
        $wrd->set_from_json_array($json_array);
        return $wrd;
    }


    /*
     * modify
     */

    /**
     * add a word to the list
     * @returns bool true if the word has been added
     */
    function add(word_dsp $wrd): bool
    {
        return parent::add_obj($wrd);
    }


    /*
     * load
     */

    /**
     * add the sources from the backend
     * @return bool
     */
    function load_all(): bool
    {
        $result = false;

        // TODO move the
        $api = new api_dsp();
        $data = array();
        $data[controller::URL_VAR_PHRASE] = $phr->id();
        $data[controller::URL_VAR_DIRECTION] = $direction;
        $data[controller::URL_VAR_LEVELS] = 1;
        $json_body = $api->api_get(self::class, $data);
        $this->set_from_json_array($json_body);
        if (!$this->is_empty()) {
            $result = true;
        }
        return $result;
    }


}
