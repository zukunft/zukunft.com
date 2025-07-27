<?php

/*

    shared/enum/user_profiles.php - a shared database based enum for the user profiles
    -----------------------------


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

namespace shared\enum;

enum user_profiles: string
{

    // list of the user profiles that have a coded functionality
    const IP_ONLY = "ip";         // if only the ip of the request is known
    const NORMAL = self::IP_ONLY; // the default profile for new users
    const NAME_ONLY = "name";     // the user has selected and reserved a unique username
    const EMAIL = "email";        // the email of the account has been confirmed
    const HUMAN = "human";        // it is confirmed that this user is a human
    const SYS_LINK = "link";      // for technical accounts for external but trustworthy systems
    const ADMIN = "admin";        // administrator that can add and change verbs and sees the code_id
    const DEV = "dev";            // reserved for developers which are supposed to code the verb functionality
    const TEST = "test";          // reserved for the system test user e.g. for internal unit and integration tests
    const SYSTEM = "system";      // reserved for the system user which is executing cleanup tasks
    const SYSTEM_ID = 18;         // only used for the initial setup
    const NORMAL_ID = 1;          // fixed id used as default for new users

}