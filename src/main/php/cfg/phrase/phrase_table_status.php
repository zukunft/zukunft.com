<?php
/*

    model/system/phrase_table_status.php - the status of a phrase table
    ----------------------------------


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


namespace Zukunft\ZukunftCom\main\php\cfg\phrase;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_HELPER . 'type_object.php';

use Zukunft\ZukunftCom\main\php\cfg\helper\type_object;

class phrase_table_status extends type_object
{

    // list of the pod statuus
    const string REQUESTED = 'requested'; // the system has requested the table creation
    const string CREATING = 'creating'; // the tables are created and filled with data
    const string ACTIVE = 'active'; // the tables are filled and are used as the primary source and target
    const string TO_DEPRECATE = 'to_deprecate'; // data should no longer be saved in the tables and are moved to other places
    const string DEPRECATED = "deprecated"; // tables should be empty and can be removed


    /*
     * database link
     */

    // comments used for the database creation
    const string TBL_COMMENT = 'for the actual status of tables for a phrase';
    const string FLD_ID = 'phrase_table_status_id';

}
