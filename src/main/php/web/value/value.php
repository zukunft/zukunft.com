<?php

/*

    web\value.php - the display extension of the api value object
    -------------

    to creat the HTML code to display a formula


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
use api\value_api;

class value_dsp extends value_api
{
    /**
     * @param phrase_list_api $phr_lst_exclude usually the context phrases that does not need to be repeated
     * @return string the HTML code of all phrases linked to the value, but not including the phrase from the $phr_lst_exclude
     */
    function name_linked(phrase_list_api $phr_lst_exclude): string
    {
        return $this->grp_dsp()->name_linked($phr_lst_exclude);
    }

    /**
     * @return string the formatted value with a link to change this value
     */
    function ref_edit(string $back): string
    {
        $html = new html_base();
        return $html->ref($html->url(api::VALUE_EDIT, $this->id, $back), $this->val_formatted());
    }

    /**
     * depending on the word list format the numeric value
     * format the value for on screen display
     * similar to the corresponding function in the "formula_value" class
     * @returns string the html text with the formatted value
     */
    function val_formatted(): string
    {
        $result = '';

        // TODO check that the phrases are set

        if (!$this->is_null()) {
            if ($this->is_percent()) {
                $result = round($this->val() * 100, $this->usr->percent_decimals) . "%";
            } else {
                if ($this->val() >= 1000 or $this->val() <= -1000) {
                    $result .= number_format($this->val(), 0, $this->usr->dec_point, $this->usr->thousand_sep);
                } else {
                    $result = round($this->val(), 2);
                }
            }
        }
        return $result;
    }

    /*
     * info
     */

    /**
     * @return bool true if one of the phrases that classify this value is of type percent
     */
    function is_percent(): bool
    {
        if ($this->grp()->has_percent()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool true if the value is not available
     */
    function is_null(): bool
    {
        if ($this->val() == null) {
            return true;
        } else {
            return false;
        }
    }

    /*
     * deprecated function names
     */

    function display_linked(string $back): string
    {
        return $this->ref_edit($back);
    }

    function display(string $back): string
    {
        return $this->val_formatted();
    }

}
