<?php

/*

    web/types/source_type_list.php - the preloaded data source types used for the html frontend
    -------------------------------


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

use cfg\const\paths;
use html\const\paths as html_paths;
include_once paths::SHARED_TYPES . 'view_styles.php';
include_once paths::SHARED_ENUM . 'source_types.php';

use shared\enum\source_types;
use shared\types\view_styles;

class source_type_list extends type_list
{

    const NAME = 'source type';

    /**
     * @returns string the html code to select a type from this list
     */
    function selector(
        string $form = '',
        int $selected = 0,
        string $name = self::NAME,
        string $bs_class = view_styles::COL_SM_4,
        string $label = ''
    ): string
    {
        return parent::type_selector($this->lst_key(), $name, $form, $selected, $bs_class, $label);
    }


    /*
     * set and get
     */

    function default_id(): int
    {
        return parent::id(source_types::CSV);
    }

}