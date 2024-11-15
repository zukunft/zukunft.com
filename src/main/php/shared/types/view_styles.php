<?php

/*

    cfg/component/view_styles.php - db based ENUM of the view and component styles
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

    Copyright (c) 1995-2024 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

namespace shared\types;

class view_styles
{

    // list of the view and component styles that have a coded functionality
    // where *_COM is the description for the tooltip

    // just to display a fixed text
    const SM_COL_4_COM = 'use 1/3 of the width';
    const SM_COL_4 = 'col-md-4';

    // list of the styles used for unit testing
    const TEST_TYPES = array(
        [self::SM_COL_4, 1],
    );

}
