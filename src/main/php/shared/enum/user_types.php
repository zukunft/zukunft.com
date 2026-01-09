<?php

/*

    shared/enum/user_types.php - a shared database based enum for the user types
    --------------------------

    the user type is the basic thrust level of a user similar to https://en.wikipedia.org/wiki/Wikipedia:User_groups#Registered_user_accounts


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

namespace Zukunft\ZukunftCom\main\php\shared\enum;

enum user_types: string
{

    // list of the user types that have a coded functionality
    // TODO Prio 2 add for all user profiles at least one unit test
    // TODO Prio 2 add the usefully cases from https://en.wikipedia.org/wiki/Wikipedia:User_groups#Registered_user_accounts
    // TODO Prio 2 add a table where the concrete permissions can be configured for each user profile
    // TODO Prio 3 review the user trust and permission setup
    // to sum up the user trust, role and permission setup
    // - each user can have one oor more profiles to which a list of concrete coded permissions are assigned
    // - each user has a type which is the internal trust level and is the main profile used for fallback so that each user has at least one profile
    // - each user can have one or more official types which are the external trust levels
    const string GUEST = "Guest"; // a read only access
    const string IP_ADDR = "IP address"; // identified only by IP address
    const string VERIFIED = "verified"; // verified by email or mobile
    const int VERIFIED_ID = 3;
    const string VERIFIED_NAME = 'Verified';
    const string VERIFIED_COM = 'verified by email or mobile';
    const string SECURED = "Secured"; // verified with a high security e.g. via passport of a trusted country

}