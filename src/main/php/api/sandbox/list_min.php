<?php

/*

    List_min.php - the list object used for the api
    ------------

    e.g. used for the value and formula result api object

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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace api;

class list_min
{
    // the protected main var
    protected array $lst;

    // memory vs speed optimize vars
    private array $id_lst;
    private bool $lst_dirty;

    function __construct()
    {
        $this->lst = array();

        $this->id_lst = array();
        $this->lst_dirty = false;
    }

    /**
     * @returns true if the list has been replaced
     */
    public function set_lst(array $lst): bool
    {
        $this->lst = $lst;
        $this->lst_dirty = true;
        return true;
    }

    /**
     * @returns array the protected list of value or formula results
     */
    public function lst(): array
    {
        return $this->lst;
    }

    /**
     * @returns array with all unique ids of this list
     */
    protected function id_lst(): array
    {
        $result = array();
        if ($this->lst_dirty) {
            foreach ($this->lst as $fv) {
                if (!in_array($fv->id, $result)) {
                    $result[] = $fv->id;
                }
            }
            $this->id_lst = $result;
            $this->lst_dirty = false;
        } else {
            $result = $this->id_lst;
        }
        return $result;
    }

}
