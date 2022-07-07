<?php

/*

    web\phrase_group_dsp.php - the extension of the phrase group api object to create the HTML code to display a word or triple
    ------------------------

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

use api\phrase_group_api;
use api\phrase_list_api;

class phrase_group_dsp extends phrase_group_api
{
    /**
     * @returns string the html code to display the phrase group with reference links
     */
    function name_linked(phrase_list_api $phr_lst_header = null): string
    {
        $result = '';
        if ($this->name_dirty()) {
            if ($this->name <> '') {
                $result .= $this->name;
            } else {
                $lst_to_show = $this->phr_lst();
                if ($phr_lst_header != null) {
                    if (!$phr_lst_header->is_empty()) {
                        $lst_to_show->remove($phr_lst_header);
                    }
                }
                foreach ($lst_to_show->lst() as $phr) {
                    if ($result <> '') {
                        $result .= ', ';
                    }
                    $result .= $phr->name_linked();
                }
            }
            $this->unset_name_dirty();
        } else {
            $result = $this->name_linked();
        }
        return $result;
    }

    /*
     * set and get
     */

    function set_lst_dsp(array $lst): void
    {
        $phr_lst_dsp = array();
        foreach ($lst as $phr) {
            $phr_lst_dsp[] = $phr->dsp_obj();
        }
        $this->set_lst($phr_lst_dsp);
    }

}
