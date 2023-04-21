<?php

/*

    /web/formula/formula.php - the display extension of the api formula object
    -----------------------

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

include_once WEB_SANDBOX_PATH . 'sandbox_typed.php';

use html\phrase\term as term_dsp;

class formula_dsp extends sandbox_typed_dsp
{

    /*
     * object vars
     */

    // the formula expression as shown to the user
    private string $usr_text;


    /*
     * construct and map
     */

    function __construct(int $id = 0, string $name = '')
    {
        parent::__construct($id, $name);
        $this->usr_text = '';
    }


    /*
     * set and get
     */

    function set_usr_text(string $usr_text)
    {
        $this->usr_text = $usr_text;
    }

    function usr_text(): string
    {
        return $this->usr_text;
    }


    /*
     * cast
     */

    function term(): term_dsp
    {
        return new term_dsp($this);
    }


    /*
     * display
     */

    /**
     * display the formula name with a link to the main page for the formula
     * @param string|null $back the back trace url for the undo functionality
     * @param string $style the CSS style that should be used
     * @returns string the html code
     */
    function dsp_link(?string $back = '', string $style = ''): string
    {
        $html = new html_base();
        $url = $html->url(api::FORMULA, $this->id, $back, api::PAR_VIEW_FORMULAS);
        return $html->ref($url, $this->name(), $this->name(), $style);
    }

    /**
     * display the formula name with a link to change the formula
     * @param string|null $back the back trace url for the undo functionality
     * @returns string the html code
     */
    function edit_link(?string $back = ''): string
    {
        $html = new html_base();
        $url = $html->url(api::FORMULA . api::UPDATE, $this->id, $back);
        return $html->ref($url, $this->name(), $this->name());
    }

}
