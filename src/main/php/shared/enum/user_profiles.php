<?php

/*

    shared/enum/user_profiles.php - a shared database based enum for the user profiles
    -----------------------------

    the user profile is used to define the coded functionality of a user similar to https://en.wikipedia.org/wiki/Wikipedia:User_groups#Table

    the user type is the basic thrust level of a user similar to https://en.wikipedia.org/wiki/Wikipedia:User_groups#Registered_user_accounts
    the official type is used for the external thrust level of a user


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

enum user_profiles: string
{

    // list of the user profiles that have a coded functionality
    // TODO Prio 2 add for all user profiles at least one unit test
    // TODO Prio 2 add the usefully cases from https://en.wikipedia.org/wiki/Wikipedia:User_groups#Table
    const string IP_ONLY = "ip";         // if only the ip of the request is known
    const string NORMAL = self::IP_ONLY; // the default profile for new users
    const int NORMAL_ID = 1;             // fixed id used as default for new users
    const string NORMAL_NAME = 'ip only';
    const string NORMAL_COM = 'if only the ip of the request is known';
    const string NAME_ONLY = "name";     // the user has selected and reserved a unique username
    const string EMAIL = "email";        // the email of the account has been confirmed
    const string HUMAN = "human";        // it is confirmed that this user is a human
    const string SYS_LINK = "link";      // for technical accounts for external but trustworthy systems
    const string ADMIN = "admin";        // administrator that can add and change verbs and sees the code_id
    const string DEV = "dev";            // reserved for developers which are supposed to code the verb functionality
    const string TEST = "test";          // reserved for the system test user e.g. for internal unit and integration tests
    const string SYSTEM = "system";      // reserved for the system user which is executing cleanup tasks
    const int SYSTEM_ID = 18;         // only used for the initial setup

}