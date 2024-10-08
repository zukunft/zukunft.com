<?php

/*

    model/system/job_time.php - to schedulea a job with predefined parameters
    -------------------------


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

use cfg\db\sql;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;

class job_time extends db_object_seq_id
{

    /*
     * database link
     */

    // field names and comments used for the database creation
    const TBL_COMMENT = 'to schedule jobs with predefined parameters';
    const FLD_SCHEDULE_COM = 'the crontab for the job schedule';
    const FLD_SCHEDULE = 'schedule';
    const FLD_USER_COM = 'the id of the user who edit the scheduler the last time';
    const FLD_TYPE_COM = 'the id of the job type that should be started';
    const FLD_START_COM = 'the last start of the job';
    const FLD_START = 'start';
    const FLD_PARAMETER_COM = 'the phrase id that contains all parameters for the next job start';
    const FLD_PARAMETER = 'parameter';

    // field lists for the table creation
    const FLD_LST_ALL = array(
        [self::FLD_SCHEDULE, sql_field_type::CRONTAB, sql_field_default::NULL, sql::INDEX, '', self::FLD_SCHEDULE_COM],
        [job_type::FLD_ID, type_object::FLD_ID_SQLTYP, sql_field_default::NOT_NULL, sql::INDEX, job_type::class, self::FLD_TYPE_COM],
        [user::FLD_ID, sql_field_type::INT, sql_field_default::NOT_NULL, sql::INDEX, user::class, self::FLD_USER_COM],
        [self::FLD_START, sql_field_type::TIME, sql_field_default::NULL, '', '', self::FLD_START_COM],
        [self::FLD_PARAMETER, sql_field_type::INT, sql_field_default::NULL, sql::INDEX, '', self::FLD_PARAMETER_COM, phrase::FLD_ID],
    );

}
