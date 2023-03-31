<?php

/*

    /web/ref/ref.php - the extension of the reference API objects to create ref base html code
    ----------------

    This file is part of the frontend of zukunft.com - calc with refs

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

use api\ref_api;

class ref_dsp extends ref_api
{

    /**
     * @returns string simply the ref name, but later with mouse over that shows the description
     */
    function dsp(): string
    {
        return $this->type_name() . ' ' . $this->external_key;
    }

}
