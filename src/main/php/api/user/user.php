<?php

/*

    api\user.php - the simple object to export a user json for the frontend API
    ------------

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

class user_api
{

    // field names used for JSON creation
    public string $name;
    public string $description;
    public string $profile;

    function __construct()
    {
        $this->name = '';
        $this->description = '';
        $this->profile = '';
    }

    /**
     * just used for unit testing
     * @return string the frontend API JSON string
     */
    function get_json(): string
    {
        return json_encode($this);
    }

}
