<?php

/*

    api/log/change_log.php - the minimal object to show one log entry in the frontend API transfer
    ----------------------


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


class change_log_api
{
    public ?int $action_id = null;  // database id of the action used to get the name from the preloaded hash
    public ?int $table_id = null;   // database id of the table used to get the name from the preloaded hash
    public ?int $field_id = null;   // database id of the field used to get the name from the preloaded hash

    public ?int $row_id = null;     // the reference id of the row in the database table

    protected ?string $user_name = null;   //
    protected ?string $change_time = null; //

}
