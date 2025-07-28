<?php

/*

    model/system/pod_type.php - to assign predefined code to a some pods
    -----------------------

    type versus status
    type is the target configuration of the pod
    status is the configuration as it is now


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

namespace cfg\system;

use cfg\const\paths;

include_once paths::MODEL_HELPER . 'type_object.php';

use cfg\helper\type_object;

class pod_type extends type_object
{

    // list of the pod types
    const MASTER = 'master'; // the main write pod for all phrases that are not assigned to any other pod
    const PHRASE_MASTER = 'phrase_master'; // the main write pod for the phrases assigned this pod
    const READ_ONLY = "read_only"; // a read only pod for load balancing


    /*
     * database link
     */

    // comments used for the database creation
    const TBL_COMMENT = 'for predefined code to a some pods';
    const FLD_ID = 'pod_type_id';

}
