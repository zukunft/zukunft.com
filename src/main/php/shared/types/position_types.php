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

    Copyright (c) 1995-2026 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

namespace Zukunft\ZukunftCom\main\php\shared\types;

class position_types
{

    // list of the view component position types that have a coded functionality

    // place the component in a new row
    const string BELOW = "below";
    const int BELOW_ID = 1;
    const string BELOW_NAME = "below";
    const string BELOW_COM = "below the previous entry";

    // place the component right or left of the previous component depending on the language write order e.g for arabic it will be left
    const string SIDE = "side";

    // place the component below the previous component but within an explicitly defined row
    const string COMBINE = "combine";

    // place the component right or left the previous component but within an explicitly defined row
    const string COLUMN = "column";

    // start the first column of a group that is shown side by side on wide screens
    // and below each other if the screen width in pixel is below
    // the 'min side width' of the user configuration
    const string SIDE_OR_FIRST_BELOW = "side_or_first_below";
    const int SIDE_OR_FIRST_BELOW_ID = 5;

    // start a following column of a side by side group that is moved below on small screens
    const string SIDE_OR_BELOW = "side_or_below";
    const int SIDE_OR_BELOW_ID = 6;

    // start the last column of a side by side group that is moved below on small screens
    const string SIDE_OR_LAST_BELOW = "side_or_last_below";
    const int SIDE_OR_LAST_BELOW_ID = 7;

    // the position types that start a new column of a group
    // that is shown side by side on wide screens and stacked on small screens
    const array SIDE_OR_BELOW_GROUP = [
        self::SIDE_OR_FIRST_BELOW,
        self::SIDE_OR_BELOW,
        self::SIDE_OR_LAST_BELOW,
    ];

    // the maximal number of side-or-below columns shown side by side on the widest screen;
    // each column gets a minimal width of 'max side width' / this count so that up to this
    // many columns fit at the configured wide width and the row wraps to fewer columns
    // (down to one) as the screen gets narrower
    const int MAX_SIDE_COLUMNS = 4;

    const string DEFAULT = self::BELOW;
    const int DEFAULT_ID = 1;

}
