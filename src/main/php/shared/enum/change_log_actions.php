<?php

/*

    shared/enum/change_log_actions.php - what a change log table adds beside when, who and what
    ----------------------------------

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

/**
 * what a change log table shows beside the when, who and what of each change: the type of the
 * changed object, the grouping by that type and the action icons
 * (see web/log/change_log_list::tbl_when_who_what)
 *
 * an empty list adds nothing, which is what the change log of one object shows, because there the
 * object type is the same for every row and the 'my' and 'others' tabs of the same page already
 * offer the actions
 */
enum change_log_actions: string
{
    // the type of the changed object e.g. 'Words' in an own column left of the what column, which
    // a change log needs as soon as it lists the changes of more than one object type
    case OBJECT_TYPE = 'object_type';
    // one table per object type instead of one table for all changes, each with the type as its
    // header; the type column is dropped then, because the header already names the type
    case GROUP_BY_TYPE = 'group_by_type';
    // the icon that opens the confirm page which sets the changed field back to the value before
    // the change, so that the user can reset one overwrite without opening the object page
    case UNDO = 'undo';
    // the icon that opens the 'others' tab of the changed object, which lists the values of the
    // other users; this needs no database query, but is also shown when no other user has a value
    case OTHERS_LINK = 'others_link';
    // the values of the other users in the table itself; this needs the values on the change entry
    // (see cfg/log/change_log_list::load_changed_objects), which costs a query per changed object
    case OTHERS_INLINE = 'others_inline';

    /**
     * @return bool true if this entry adds a column right of the what column
     */
    function is_action_column(): bool
    {
        return in_array($this, [self::UNDO, self::OTHERS_LINK, self::OTHERS_INLINE], true);
    }
}