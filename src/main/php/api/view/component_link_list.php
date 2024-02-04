<?php

/*

    api/view/component_link_list.php - a list of api component links
    --------------------------------


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

namespace api\view;

use api\sandbox\list_object as list_api;
use api\view\component_link;

class component_link_list extends list_api
{

    /*
     * construct and map
     */

    /**
     * add a link of component to a view to the list
     * the same component can be linked several times at different positions
     *
     * @returns bool true if at least one component has been added
     */
    function add(component_link $lnk): bool
    {
        $do_add = true;
        if (in_array($lnk->id(), $this->id_lst())) {
            $do_add = false;
        } else {
            foreach ($this->lst() as $lst_lnk) {
                if ($lst_lnk->view()->id() == $lnk->view()->id()
                    and $lst_lnk->component()->id() == $lnk->component()->id()
                    and $lst_lnk->pos() == $lnk->pos()) {
                    $do_add = false;
                }
            }
        }
        if ($do_add) {
            $this->add_obj($lnk);
            $this->set_lst_dirty();
        }
        return $do_add;
    }

}
