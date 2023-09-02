<?php

/*

    web/types/share.php - the preloaded data share types used for the html frontend
    ------------------------


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

namespace html\types;

use cfg\share_type;

class share extends type_list
{

    const NAME = 'share';

    /**
     * @returns string the html code to select a type from this list
     */
    function selector(
        string $form = '',
        int $selected = 0,
        string $name = self::NAME,
        string $bs_class = '',
        string $label = ''
    ): string
    {
        global $html_share_types;
        return parent::type_selector($html_share_types->lst_key(), $name, $form, $selected, $bs_class, $label);
    }

    /*
     * set and get
     */

    function default_id(): int
    {
        return parent::id(share_type::PUBLIC);
    }

}