<?php

/*

    formula_value_list_min_display.php - the display extension of the api formula value list object
    ----------------------------------

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

namespace html;

use api\formula_value_list_api;
use api\phrase_list_api;
use html_table;

class formula_value_list_dsp extends formula_value_list_api
{
    /**
     * @return string the html code to show the formula results as a table to the user
     */
    function table(phrase_list_api $context_phr_lst = null, string $back = ''): string
    {
        $result = ''; // reset the html code var

        $tbl = new html_table();

        // prepare to show where the user uses different word than a normal viewer
        $row_nbr = 0;
        $result .= $tbl->start(html_table::SIZE_HALF);
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
            $head_text = $header_phrases->dsp_obj()->name_linked();
        }
        foreach ($this->lst() as $fv) {
            $row_nbr++;
            $result .= $tbl->row_start();
            if ($row_nbr == 1) {
                $result .= $tbl->header($head_text);
                $result .= $tbl->header('value');
                $result .= $tbl->row();
            }
            $result .= $tbl->cell($fv->name_linked($common_phrases));
            $result .= $tbl->cell($fv->value_linked($back));
            $result .= $tbl->row_end();
        }
        $result .= dsp_tbl_end();

        log_debug("fv_lst->display -> done");
        return $result;
    }

}
