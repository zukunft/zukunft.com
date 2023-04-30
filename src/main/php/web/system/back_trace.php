<?php

/*

    back_trace.php - list of links that the user has called in the past
    --------------

    used to implement CTRL-Z

    TODO if possible should be included in the request using a json body
    TODO allow tree based back and force steps

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

namespace html\system;

class back_trace
{
    public ?array $url_lst = null;

    /** encode the back trace for an url */
    function url_encode(): string
    {
        $result = '';
        if ($this->url_lst != null) {
            foreach ($this->url_lst as $url) {
                if ($result == '') {
                    $result = $url;
                }
            }

        }
        return $result;
    }

}
