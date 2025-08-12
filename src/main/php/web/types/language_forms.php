<?php

/*

    web/types/language_form.php - the preloaded data language_forms used for the html frontend
    ---------------------------


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

include_once html_paths::TYPES . 'type_lists.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED . 'url_var.php';

use shared\enum\messages as msg_id;
use shared\url_var;

class language_forms extends type_list
{

    const NAME = url_var::LANGUAGE_FORM;

    /**
     * create the HTML code to select a language form
     * @param string $form the unique name of the html form
     * @param int|null $selected the id of the preselected language form
     * @param string $name the unique name inside the form for this selector
     * @returns string the html code to select a type from this list
     */
    function selector(
        string   $form = '',
        int|null $selected = null,
        string   $name = self::NAME
    ): string
    {
        return parent::type_selector($form, $selected, $name, msg_id::LABEL_COMPONENT_LINK_TYPE);
    }

}