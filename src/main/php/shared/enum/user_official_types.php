<?php

/*

    shared/enum/user_official_types.php - a shared database based enum for the external user types
    -----------------------------------


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

enum user_official_types: string
{

    // list of official user identification types
    const string PASSPORT_EU = "passport_eu";
    const int PASSPORT_EU_ID = 1;
    const string PASSPORT_EU_NAME = "EU passport";
    const string PASSPORT_EU_COM = "";
    const string PASSPORT_US = "passport_us";
    const string PASSPORT_US_FAKE = "passport_us_fake"; // if the passport is faked not by an official source
    const string PASSPORT_US_FAKE_BY_STATE = "passport_us_fake_by_state"; // if the passport is faked by an official source

}