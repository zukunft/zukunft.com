<?php

/*

    _user_exp.php - the simple export object for a user
    --------------

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


namespace cfg\export;

include_once SERVICE_EXPORT_PATH . 'sandbox_exp_named.php';

class user_exp extends sandbox_exp_named
{

    // field names used for JSON creation
    public ?string $email = null;         //
    public ?string $first_name = null;    //
    public ?string $last_name = null;     //
    public ?string $description = null;
    public ?string $profile = null;
    public ?string $code_id = null;

    function reset(): void
    {
        sandbox_exp_named::reset();

        $this->email = '';
        $this->first_name = '';
        $this->last_name = '';
        $this->description = '';
        $this->profile = '';
        $this->code_id = '';
    }

}
