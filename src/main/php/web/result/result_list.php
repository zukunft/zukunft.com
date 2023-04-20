<?php

/*

    result_list_min_display.php - the display extension of the api result list object
    ---------------------------

    to creat the HTML code to display a list of formula results


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

namespace result;

use html\html_base;
use html\list_value_dsp;
use html\phrase_list_dsp;

include_once WEB_SANDBOX_PATH . 'list_value.php';

class result_list_dsp extends list_value_dsp
{


    /**
     * add a formula result to the list
     * @returns bool true if the formula result has been added
     */
    function add(result_dsp $res): bool
    {
        $result = false;
        if (!in_array($res->id(), $this->id_lst())) {
            $this->lst[] = $res;
            $this->set_lst_dirty();
            $result = true;
        }
        return $result;
    }


    /**
     * @return string the html code to show the formula results as a table to the user
     */
    function table(phrase_list_dsp $context_phr_lst = null, string $back = ''): string
    {
        $html = new html_base();

        // prepare to show where the user uses different word than a normal viewer
        $row_nbr = 0;
        $common_phrases = $this->common_phrases();

        // remove the context phrases from the header e.g. inhabitants for a text just about inhabitants
        $header_phrases = clone $common_phrases;
        if ($context_phr_lst != null) {
            $header_phrases->remove($context_phr_lst);
        }

        // if no phrase is left for the header, show 'description' as a dummy replacement
        if ($header_phrases->count() <= 0) {
            $head_text = 'description';
        } else {
            $head_text = $header_phrases->dsp_link();
        }
        $header_rows = '';
        $rows = '';
        foreach ($this->lst() as $res) {
            $row_nbr++;
            if ($row_nbr == 1) {
                $header = $html->th($head_text);
                $header .= $html->th('value');
                $header_rows = $html->tr($header);
            }
            $row = $html->td($res->name_linked($common_phrases));
            $row .= $html->td($res->value_linked($back));
            $rows .= $html->tr($row);
        }

        return $html->tbl($header_rows . $rows, $html::SIZE_HALF);
    }

}
