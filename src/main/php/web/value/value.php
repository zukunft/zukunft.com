<?php

/*

    web\value_dsp.php - the display extension of the api value object
    -----------------

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

}
