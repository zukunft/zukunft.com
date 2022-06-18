<?php

/*

    phrase_list_min_display.php - the display extension of the api phrase list object
    ---------------------------

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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

class phrase_list_min_dsp extends \api\phrase_list_min
{
    /**
     * @returns string the html code to display the phrases with the most useful link
     */
    public function name_linked(): string
    {
        $result = '';
        foreach ($this->lst as $phr) {
            if ($result != '' and $phr->name_linked() != '') {
                $result .= ', ';
            }
            $result .= $phr->name_linked();
        }
        return $result;
    }
}
