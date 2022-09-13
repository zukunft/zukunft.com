<?php

/*

    exp_obj.php - the superclass for the export objects
    -----------

    is used in service/export/json.php

    similar to the api objects, but the export objects does not contain any

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

namespace export;

class exp_obj
{
    // the field names used for the im- and export in the json or yaml format
    const FLD_NAME = 'name';
    const FLD_TYPE = 'type';
    const FLD_DESCRIPTION = 'description';
    const FLD_CODE_ID = 'code_id';
    const FLD_VIEW = 'view';
    const FLD_TIMESTAMP = 'timestamp';
    const FLD_TIME = 'time';
    const FLD_NUMBER = 'number';

}