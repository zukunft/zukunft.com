<?php

/*

    shared/json_fields.php - list of json field names used for im- and export
    ----------------------

    ths json fields for the api messages are in the shared api object


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

namespace shared;

class json_fields
{

    // TODO easy move all fields used for the json im- and export messages to this object

    // the database id e.g. of a component_link
    const LINK_ID = 'link_id';

    // the code id of the view style of a view, component or component_link
    const STYLE = 'style';

    // e.g. the order of the components within a view
    const POS = 'position';

    // the position rules for a component relative to the previous component
    const POS_TYPE = 'position_type';

    // to link predefind functionality to a row e.g. to select a system view
    const CODE_ID = 'code_id';

    // to link predefind functionality to a row e.g. to select a system view
    const UI_MSG_CODE_ID = 'ui_msg_code_id';

}
