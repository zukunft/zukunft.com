<?php

/*

    triple_dsp.php - a list function to create the HTML code to display a triple (two linked words or triples)
    --------------

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

use api\triple_api;
use phrase;

class triple_dsp extends triple_api
{

    /**
     * @returns string simply the word name, but later with mouse over that shows the description
     */
    function dsp(): string
    {
        return $this->name();
    }

    /**
     * @returns string the html code to display a bottom to edit the word link in a table cell
     */
    function btn_edit(phrase $wrd): string
    {

        $html = new html_base();
        $url = $html->url(api::PATH . 'link' . api::UPDATE . api::EXT, $this->id, $wrd->id);
        $btn = (new button("edit word link", $url))->edit();

        return $html->td($btn);
    }

}
