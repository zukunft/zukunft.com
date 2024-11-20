<?php

/*

    shared/json_fields.php - list of json field names used for the api and im- and export
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

    /*
     * api
     */

    // json field names of the api json messages
    // which is supposed to be the same as the corresponding var of the api object
    // so that no additional mapping is needed
    // TODO check if api objects can be deprecated
    // and used in the backend to create the json for the frontend
    // and used in the frontend for the field selection
    const ID = 'id'; // the unique database id used to save the changes
    const NAME = 'name'; // the unique name of the object which is also a database index
    const DESCRIPTION = 'description';

    // the json field name in the api json message which is supposed to contain
    // the database id (or in some cases still the code id) of an object type
    // e.g. for the word api message it contains the id of the phrase type
    const TYPE = 'type_id';

    // the order number e.g. of the component within the view
    const POSITION = 'position';

    // the database id e.g. of a component_link
    const LINK_ID = 'link_id';

    // the code id of the view style of a view, component or component_link
    const STYLE = 'style';

    // e.g. the order of the components within a view
    const POS = 'position';

    // the position rules for a component relative to the previous component
    const POS_TYPE = 'position_type';

    // to link predefined functionality to a row e.g. to select a system view
    const CODE_ID = 'code_id';

    // to link predefined functionality to a row e.g. to select a system view
    const UI_MSG_CODE_ID = 'ui_msg_code_id';

}
