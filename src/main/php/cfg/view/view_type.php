<?php

/*

    model/view/view_type.php - ENUM of the view types
    ------------------------

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

namespace cfg;

class view_type
{

    // list of the view types that have a coded functionality
    const DEFAULT = "default";
    const ENTRY = "entry";
    const MASK_DEFAULT = "mask_default";
    const PRESENT = "presentation";
    const WORD_DEFAULT = "word_default";
    const DETAIL = "detail_view";
    const SYSTEM = "system";

    // list of view types that are used by the system
    // and should not be assignable by users
    const SYSTEM_TYPES = array(
        self::SYSTEM
    );

}
