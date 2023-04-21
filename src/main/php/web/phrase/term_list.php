<?php

/*

    /web/phrase/term_list_dsp.php - the display extension of the api phrase list object
    -----------------------------

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

use html\html_selector;
use html\list_dsp;

include_once WEB_SANDBOX_PATH . 'list.php';

class term_list extends list_dsp
{

    /**
     * @returns string the html code to display the phrases with the most useful link
     */
    function dsp(): string
    {
        $result = '';
        foreach ($this->lst as $trm) {
            if ($result != '' and $trm->dsp_link() != '') {
                $result .= ', ';
            }
            $result .= $trm->dsp_link();
        }
        return $result;
    }

    /**
     * @returns string the html code to select a phrase out of this list
     */
    function selector(string $name = '', string $form = '', int $selected = 0): string
    {
        $sel = new html_selector;
        $sel->name = $name;
        $sel->form = $form;
        $sel->lst = $this->lst_key();
        $sel->selected = $selected;

        return $sel->dsp();
    }

    function add(object $obj): bool
    {
        return $this->add_obj($obj);
    }
}
