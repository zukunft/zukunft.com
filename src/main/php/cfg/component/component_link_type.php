<?php

/*

    model/component/component_link_type.php - db based ENUM of the component view link types
    ---------------------------------------

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

namespace Zukunft\ZukunftCom\main\php\cfg\component;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_HELPER . 'type_object.php';

use Zukunft\ZukunftCom\main\php\cfg\helper\type_object;

class component_link_type extends type_object
{

    /*
     * code links
     */

    // list of the component link types that have a coded functionality
    const string ALWAYS = "always"; // the component is always shown as it is
    const string EXPRESSION = "expression"; // the component is only shown if an expression is true
    const string DEFAULT = self::ALWAYS;


    /*
     * database link
     */

    // comments used for the database creation
    const string TBL_COMMENT = 'to assign predefined behaviour to a component view link';
    const string FLD_ID = 'component_link_type_id'; // to use in const until final is allowed

}
