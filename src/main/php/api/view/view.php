<?php

/*

    api/view/view.php - the view object for the frontend API
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

namespace api\view;

include_once API_SANDBOX_PATH . 'sandbox_typed.php';
include_once API_COMPONENT_PATH . 'component_list.php';
include_once API_VIEW_PATH . 'component_link_list.php';

use api\component\component_list AS component_list_api;
use api\sandbox\sandbox_typed as sandbox_typed_api;
use api\view\component_link_list AS component_link_list_api;
use shared\views;

class view extends sandbox_typed_api
{

    /*
     * object vars
     */

    // to link predefined behavier in the frontend
    public ?string $code_id = null;

    // the components linked to this view
    public ?component_link_list_api $components = null;
}
