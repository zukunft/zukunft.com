<?php

/*

    model/system/job_type.php - a predefined batch task that can be triggered by a user action or a scheduler
    -------------------------

    TODO allow to create workflows
         e.g. to request at other users to remove the user overwrites of a word
         that is requested to be deleted, which the users can confirm or reject
         the process is completed if all user have confirmed the word removal
         add the timestamp to each job step (compare with tream)


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

class job_type extends type_object
{

    // list of the job types that have a coded functionality
    const WORD_DELETE = "word_delete";
    const TRIPLE_DELETE = "triple_delete";


    /*
     * database link
     */

    // comments used for the database creation
    const TBL_COMMENT = 'for predefined batch jobs that can be triggered by a user action or scheduled e.g. data synchronisation';
    const FLD_ID = 'job_type_id'; // repeated to enable use in other const (TODO try to use something like "final" in java)

}
