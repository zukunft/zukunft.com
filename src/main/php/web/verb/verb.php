<?php

/*

    web\verb\verb.php - the display extension of the api verb object
    -----------------

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

use api\verb_api;
use verb;

class verb_dsp extends verb_api
{

    /*
     * casting
     */

    function term(): term_dsp
    {
        return new term_dsp($this->id, $this->name, verb::class);
    }

    /**
     * display the verb with a link to the main page for the verb
     * @param string|null $back the back trace url for the undo functionality
     * @param string $style the CSS style that should be used
     * @returns string the html code
     */
    function dsp_link(?string $back = '', string $style = ''): string
    {
        $html = new html_base();
        $url = $html->url(api::VERB, $this->id, $back, api::PAR_VIEW_VERBS);
        return $html->ref($url, $this->name(), $this->name(), $style);
    }

}
