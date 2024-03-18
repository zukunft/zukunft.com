<?php

/*

    cfg/system/pod_status.php - the status of a pod
    -------------------------

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

namespace cfg;

class pod_status extends type_object
{

    // list of the pod statuus
    const ACTIVE = 'active';
    const TO_DEPRECATE = 'to_deprecate'; // data should be moved out of this pod to other pods
    const DEPRECATED = "deprecated"; // the pod is not active anymore


    /*
     * database link
     */

    // comments used for the database creation
    const TBL_COMMENT = 'for the actual status of a pod';
    const FLD_ID = 'pod_status_id';

}
