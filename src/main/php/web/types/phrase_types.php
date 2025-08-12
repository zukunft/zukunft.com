<?php

/*

    web/types/phrase_types.php - the preloaded data phrase types used for the html frontend
    --------------------------


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

    Copyright (c) 1995-2024 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

namespace html\types;

use cfg\const\paths;

include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_TYPES . 'phrase_type.php';
include_once paths::SHARED_TYPES . 'view_styles.php';
include_once paths::SHARED . 'api.php';
include_once paths::SHARED . 'url_var.php';

use shared\enum\messages as msg_id;
use shared\types\phrase_type;
use shared\types\view_styles;
use shared\url_var;

class phrase_types extends type_list
{

    const NAME = url_var::PHRASE_TYPE;

    /**
     * create the HTML code to select a phrase type
     * @param string $form the name of the html form
     * @param int $selected the database id of the
     * @param string $name the unique name inside the form for this selector
     * @param string $style e.g. to define the size of the select field
     * @returns string the html code to select a type from this list
     */
    function selector(
        string $form,
        int    $selected = 1,
        string $name = self::NAME,
        string $style = view_styles::COL_SM_4
    ): string
    {
        return parent::type_selector($form, $selected, $name, msg_id::LABEL_TYPE, $style);
    }


    /*
     * set and get
     */

    function default_id(): int
    {
        return parent::id(phrase_type::NORMAL);
    }

}