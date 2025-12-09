<?php

/*

    shared/types/position_types.php - how view components can be placed for the user
    -------------------------------

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

    Copyright (c) 1995-2023 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

namespace Zukunft\ZukunftCom\main\php\shared\types;

class position_types
{

    // list of the view component position types that have a coded functionality

    // place the component in a new row
    const string BELOW = "below";

    // place the component right or left of the previous component depending on the language write order e.g for arabic it will be left
    const string SIDE = "side";

    // place the component below the previous component but within an explicitly defined row
    const string COMBINE = "combine";

    // place the component right or left the previous component but within an explicitly defined row
    const string COLUMN = "column";

    const string DEFAULT = self::BELOW;
    const int DEFAULT_ID = 1;

}
