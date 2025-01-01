<?php

/*

    api/user/user.php - the simple object to export a user json for the frontend API
    -----------------

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

namespace api\user;

class user
{

    /*
     * const for system testing
     */

    // the fixed system user used for testing
    // TN_* is the name of the predefined source used for testing
    // TI_* is the id after adding the predefined sources
    // TD_* is the description  of the predefined source
    const TD_READ = 'standard user view for all users';
    const TD_READ_IP = '66.249.64.95'; // used to check the blocking of an IP address

    // field names used for JSON creation
    public string $id;
    public ?string $name;
    public ?string $description;
    public ?string $profile;
    public ?string $email;
    public ?string $first_name;
    public ?string $last_name;

    function __construct()
    {
        $this->set_id(0);
        $this->name = '';
        $this->description = null;
        $this->profile = null;
        $this->email = null;
        $this->first_name = null;
        $this->last_name = null;
    }



    /*
     * set and get
     */

    function set_id(int $id): void
    {
        $this->id = $id;
    }

    function id(): int
    {
        return $this->id;
    }


    /*
     * interface
     */

    /**
     * just used for unit testing
     * @return string the frontend API JSON string
     */
    function get_json(): string
    {
        return json_encode($this);
    }

}
