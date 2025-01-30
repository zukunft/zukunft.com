<?php

/*

    web/phrase/term_list_dsp.php - the display extension of the api phrase list object
    ----------------------------

    mainly links to the word and triple display functions


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

namespace html\phrase;

include_once WEB_SANDBOX_PATH . 'list_dsp.php';
include_once WEB_USER_PATH . 'user_message.php';

use html\sandbox\list_dsp;
use html\user\user_message;

class term_list extends list_dsp
{


    /*
     * set and get
     */

    /**
     * set the vars of a term object based on the given json
     * @param array $json_array an api single object json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function set_from_json_array(array $json_array): user_message
    {
        return parent::set_list_from_json($json_array, new term());
    }


    /*
     * display
     */

    /**
     * @returns string the html code to display the phrases with the most useful link
     */
    function display(): string
    {
        $result = '';
        foreach ($this->lst as $trm) {
            if ($result != '' and $trm->display() != '') {
                $result .= ', ';
            }
            $result .= $trm->display();
        }
        return $result;
    }

    /**
     * @returns string the html code to display the phrases with the most useful link
     */
    function display_linked(): string
    {
        $result = '';
        foreach ($this->lst as $trm) {
            if ($result != '' and $trm->display_linked() != '') {
                $result .= ', ';
            }
            $result .= $trm->display_linked();
        }
        return $result;
    }

    function add(object $obj): bool
    {
        return $this->add_obj($obj);
    }
}
