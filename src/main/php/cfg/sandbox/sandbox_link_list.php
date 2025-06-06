<?php

/*

    model/sandbox/sandbox_link_list.php - add the link specific functions to the sandbox list object
    -----------------------------------

    The main sections of this object are
    - modify:            change potentially all items of this list object


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

namespace cfg\sandbox;

include_once MODEL_SANDBOX_PATH . 'sandbox_list.php';
include_once MODEL_COMPONENT_PATH . 'component.php';
include_once MODEL_COMPONENT_PATH . 'component_link.php';
include_once MODEL_PHRASE_PATH . 'term.php';
include_once MODEL_VIEW_PATH . 'view.php';
include_once MODEL_VIEW_PATH . 'view_term_link.php';

use cfg\component\component;
use cfg\component\component_link;
use cfg\phrase\term;
use cfg\view\view;
use cfg\view\view_term_link;

class sandbox_link_list extends sandbox_list
{

    /*
     * modify
     */

    /**
     * add a link based on parts to this list without saving it to the database
     * @return true if the link has been added
     */
    function add(int $id, view $msk, component|term $sbx, int $pos = 0): bool
    {
        if ($sbx::class == term::class) {
            $new_lnk = new view_term_link($this->user());
        } else {
            $new_lnk = new component_link($this->user());
        }
        $new_lnk->set($id, $msk, $sbx, $pos);
        return $this->add_link($new_lnk);
    }

    /**
     * add a link to this list without saving it to the database
     * @return true if the link has been added
     */
    function add_link(component_link|view_term_link $lnk_to_add): bool
    {
        $added = false;
        if ($this->can_add($lnk_to_add)) {
            $this->add_obj($lnk_to_add);
            $added = true;
        }
        return $added;
    }


    /*
      * internal
      */

    /**
     * test if the link already exists and if yes return false to prevent duplicates
     * can be overwritten by the child class e.g. if the same link a different positions is allowed
     * @param sandbox_link $lnk_to_add the link that should be added to the list
     * @return bool true if the link can be added
     */
    protected function can_add(sandbox_link $lnk_to_add): bool
    {
        $can_add = true;

        if (!$this->is_empty()) {
            foreach ($this->lst() as $lnk) {
                if ($can_add) {
                    if ($lnk->from_id() == $lnk_to_add->from_id()
                        and $lnk->to_id() == $lnk_to_add->to_id()) {
                        $can_add = false;
                    }
                    if ($lnk->id() == $lnk_to_add->id()
                        and $lnk->id() != 0 and $lnk_to_add->id() != 0
                        and $lnk->id() !== null and $lnk_to_add->id() !== null) {
                        $can_add = false;
                    }
                }
            }
        }
        return $can_add;
    }

}
