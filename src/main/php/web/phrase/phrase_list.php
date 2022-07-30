<?php

/*

    web\phrase_list_dsp.php - the display extension of the api phrase list object
    -----------------------

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

namespace html;

use api\phrase_list_api;
use word_type;

class phrase_list_dsp extends phrase_list_api
{

    /**
     * @returns string the html code to display the phrases with the most useful link
     */
    public function dsp_link(): string
    {
        $result = '';
        foreach ($this->lst as $phr) {
            if ($result != '' and $phr->dsp_link() != '') {
                $result .= ', ';
            }
            $result .= $phr->dsp_link();
        }
        return $result;
    }

    /**
     * @returns string the html code to display the plural of the phrases with the most useful link
     * TODO replace adding the s with a language specific functions that can include exceptions
     */
    private function plural(): string
    {
        return $this->dsp_link() . 's';
    }

    /**
     * @returns string the html code to display the phrases for a sentence start
     * TODO replace adding the s with a language specific functions that can include exceptions
     */
    private function InitCap(): string
    {
        return strtoupper(substr($this->plural(), 0, 1)) . substr($this->plural(), 1);
    }

    /**
     * @returns string the html code to display the phrases as a headline
     */
    public function headline(): string
    {
        $html = new html_base();
        return $html->text_h2($this->InitCap());
    }

    /**
     * @returns string the html code to select a word link type
     */
    private function selector(int $selected_id, string $form): string
    {
        $result = '';

        if ($selected_id <= 0) {
            $selected_id = word_type::DEFAULT;
        }

        $sel = new html_selector;
        $sel->form = $form;
        $sel->name = 'type';
        $sel->sql = sql_lst("word_type");
        $sel->selected = $selected_id;
        $sel->dummy_text = '';
        $result .= $sel->display();

        return $result;
    }

}
