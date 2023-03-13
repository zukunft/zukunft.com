<?php

/*

    /web/figure_dsp.php - the display extension of the api figure object
    -------------------

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

    Copyright (c) 1995-2023 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace html;

include_once API_FORMULA_PATH . DIRECTORY_SEPARATOR . 'figure.php';

use api\figure_api;
use api\phrase_list_api;

class figure_dsp
{

    /*
     * object vars
     */

    private value_dsp|formula_value_dsp $obj;


    /*
     * set and get
     */

    function set_from_json(string $json_api_msg): void
    {
        $json_array = json_decode($json_api_msg);
        if ($json_array[figure_api::TYPE_FLD] == figure_api::TYPE_RESULT)
        {
            $fv_dsp = new formula_value_dsp();
            $fv_dsp->set_from_json_array();
            $this->set_obj($fv_dsp);
        } else {
            $val = new value_dsp();
            $val->set_from_json_array();
            $this->set_obj($val);
        }
    }

    function set_obj(value_dsp|formula_value_dsp $obj): void
    {
        $this->obj = $obj;
    }

    function obj(): value_dsp|formula_value_dsp
    {
        return $this->obj;
    }


    /*
     * display
     */

    /**
     * @param phrase_list_api|null $phr_lst_header list of phrases that are shown already in the context e.g. the table header and that should not be shown again
     * @returns string the html code to display the phrase group with reference links
     */
    function name_linked(phrase_list_api $phr_lst_header = null): string
    {
        return $this->grp_dsp()->name_linked($phr_lst_header);
    }


    /**
     * return the html code to display a value
     * this is the opposite of the convert function
     */
    function display(string $back = ''): string
    {
        return round($this->number(), 2);
    }

    /**
     * html code to show the value with the possibility to click for the result explanation
     */
    function display_linked(string $back = ''): string
    {
        $html = new html_base();
        return $html->ref($html->url(api::VALUE_EDIT, $this->id, $back), $this->val_formatted());
    }

}
