<?php

/*

    web\view\view.php - the display extension of the api view object
    -----------------

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

namespace html;

use api\view_api;

class view_dsp extends view_api
{

    public function list_sort(): string
    {
        return $this->components()->dsp();
    }

    private function components(): view_cmp_list_dsp
    {
        $lst = new view_cmp_list_dsp();
        foreach ($this->cmp_lst as $cmp) {
            $lst->add($cmp->dsp_obj());
        }
        return $lst;
    }
}
