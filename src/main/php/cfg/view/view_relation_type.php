<?php

/*

    model/view/view_relation_type.php - to define the relation between two views
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

    Copyright (c) 1995-2024 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\cfg\view;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_HELPER . 'type_object.php';
include_once paths::SHARED_CONST_FIELDS . 'view_fields.php';
include_once paths::SHARED_TYPES . 'view_relation_types.php';

use Zukunft\ZukunftCom\main\php\cfg\helper\type_object;
use Zukunft\ZukunftCom\main\php\shared\const\fields\view_fields;
use Zukunft\ZukunftCom\main\php\shared\types\view_relation_types;

class view_relation_type extends type_object
{

    /*
     * code links
     */

    // the code id of the default relation, taken from the shared const class so that the default
    // is defined once; "parent_child" before, which is no code id of view_relation_types.csv, so
    // that the type list never found it and the default was never treated as the default
    const string DEFAULT = view_relation_types::DEFAULT;


    /*
     * database link
     */

    // comments used for the database creation
    const string TBL_COMMENT = view_fields::FLD_RELATION_TYPE_COM;
    // the db field name from the shared const, so that the frontend can use the same name
    // e.g. to map the field of an overwrite to the url var of the edit view (see db_fld_to_url)
    const string FLD_ID = view_fields::FLD_RELATION_TYPE;

}
