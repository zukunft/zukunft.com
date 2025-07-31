<?php

/*

    shared/enum/url_actions.php - enum of the frontend actions based on the url
    ---------------------------


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

    Copyright (c) 1995-2025 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace shared\enum;

enum url_actions: string
{

    // possible url values for URL_VAR_CONFIRM field
    const SHOW_ONLY = 'show_only'; // create the html frontend code based only on the url without loading addition data from the database or via api
    const API_RELOAD = 'api'; // reload the object via api before creating the html code
    const CONFIRM = 'confirm'; // ask the user for confirmation of the changes

}