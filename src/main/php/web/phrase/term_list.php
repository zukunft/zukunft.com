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

include_once WEB_SANDBOX_PATH . 'sandbox_list_named.php';
include_once WEB_USER_PATH . 'user_message.php';

use html\sandbox\sandbox_list_named;
use html\user\user_message;

class term_list extends sandbox_list_named
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
     * base
     */

    /**
     * @returns string the html code to display the phrases with the most useful link
     */
    function name_tip(): string
    {
        $result = '';
        foreach ($this->lst() as $trm) {
            if ($result != '' and $trm->name_tip() != '') {
                $result .= ', ';
            }
            $result .= $trm->name_tip();
        }
        return $result;
    }

    /**
     * @returns string the html code to display the phrases with the most useful link
     */
    function name_link(): string
    {
        $result = '';
        foreach ($this->lst() as $trm) {
            if ($result != '' and $trm->name_link() != '') {
                $result .= ', ';
            }
            $result .= $trm->name_link();
        }
        return $result;
    }

    /**
     * get a term from the term list selected by the word, triple, formula or verb id
     *
     * @param int $id the word, triple, formula or verb id (not the term id!)
     * @param string $class the word, triple, formula or verb class name
     * @return term|null the word object from the list or null
     */
    function term_by_obj_id(int $id, string $class): ?term
    {
        $trm = new term();
        $trm->set_obj_from_class($class);
        $trm->set_obj_id($id);
        $trm_id = $trm->id();
        if ($trm_id != 0) {
            $trm = $this->get_by_id($trm_id);
        }
        return $trm;
    }

}
