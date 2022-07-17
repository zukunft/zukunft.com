<?php

/*

    phrase_display.php - the extension of the phrase object to create API json messages or HTML code to display a word or triple
    ------------------

    deprecated - to be replaced by phrase_min_dsp

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

class phrase_dsp_old extends phrase
{

    /**
     * simply to display a single word in a table cell
     */
    function dsp_tbl_cell($intent): string
    {
        $result = '';
        if ($this->is_word()) {
            $wrd = $this->get_word_dsp();
            $result .= $wrd->dsp_td('','', $intent);
        }
        return $result;
    }

    //
    function dsp_selector($type, $form_name, $pos, $class, $back): string
    {
        return $this->dsp_selector($type, $form_name, $pos, $class, $back);
    }

}
