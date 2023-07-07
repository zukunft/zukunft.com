<?php

/*

    api/api.php - constants used for the backend to frontend api of zukunft.com
    -----------


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

namespace api;

class api
{

    /*
     * fields
     */

    // json field names of the api json messages
    // which is supposed to be the same as the corresponding var of the api object
    // so that no additional mapping is needed
    const FLD_ID = 'id'; // the unique database id used to save the changes
    const FLD_NAME = 'name'; // the unique name of the object which is also a database index
    const FLD_DESCRIPTION = 'description';

    // the json field name in the api json message which is supposed to contain
    // the database id (or in some cases still the code id) of an object type
    // e.g. for the word api message it contains the id of the phrase type
    const FLD_TYPE = 'type_id';

    // the json field name for code id to select a single object
    // e.g. to select a system view
    const FLD_CODE_ID = 'code_id';

    // reference fields e.g. to link a phrase to an external reference
    const FLD_PHRASE = 'phrase_id';
    const FLD_SOURCE = 'source_id';

    const FLD_USER_ID = 'user_id';

}
