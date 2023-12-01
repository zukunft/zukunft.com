<?php

/*

    model/group/group_id_list.php - functions for a list of group ids
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

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

namespace cfg\group;

use cfg\phrase_list;

class group_id_list
{

    /**
     * list of the table extension / types where the value or result rows might be found
     *
     * @param array $ids with the group ids that should be searched for
     * @param bool $is_grp true to get the table extension for groups
     * @return array with the table extensions where the values or results might be found
     */
    function table_ext_list(array $ids, bool $is_grp = false): array
    {
        $ext_lst = array();
        $grp_id = new group_id();
        foreach ($ids as $id) {
            $ext = $grp_id->table_extension($id, $is_grp);
            if (!in_array($ext, $ext_lst)) {
                $ext_lst[] = $ext;
            }
        }
        return $ext_lst;
    }

}