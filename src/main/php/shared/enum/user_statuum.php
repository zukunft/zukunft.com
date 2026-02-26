<?php

/*

    shared/enum/user_statuum.php - a shared database based enum for the user status
    ----------------------------

    the user status is used to temporary reduce the permissions defined by the user_profile

    the user_type is based on external sources
    whereas the user_profile defines the long-term internal permissions,
    which can be adjusted by the short term user_status


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

    Copyright (c) 1995-2026 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\shared\enum;

enum user_statuum: string
{

    // list of the user statuum that have a coded functionality
    const string ACTIVE = "active"; // no restrictions are applied
    const string READ_ONLY = "read-only"; // the write permission is switched off for the user
    const int READ_ONLY_ID = 3;
    const string READ_ONLY_NAME = "read only";
    const string READ_ONLY_COM = "the write permission is switched off for the user";
    const string GEO_FENCED = "geo-fenced"; // the user can only be seen and write within a given geo fence
    const string WRITE_GEO = "write-geo"; // the user can only change data within a geo fence
    const string READ_GEO = "read-geo"; // change of the user can only be seen within a geo fence
    const string EXCLUDED = "excluded"; // all changes of the user are excluded from the results for other users
    const string ADMIN_ONLY = 'admin-only'; // the user is excluded and even the change log can only be seen by an admin e.g. because the user has published something illegal

}