<?php

/*

    formula_value_min.php - the minimal result value object
    ---------------------


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

use api\phrase_group_min;
use api\user_sandbox_min;

class formula_value_min extends user_sandbox_min
{

    public phrase_group_min $grp; // the phrase group with the list of words and triples (not the source words and triples)
    public float $val; // if the calculated number

    function __construct(int $id = 0)
    {
        parent::__construct($id);
        $this->grp = new phrase_group_min();
        $this->val = 0;
    }

    /**
     * @param phrase_group_min $phr_lst_header list of phrases that are shown already in the context e.g. the table header and that should not be shown again
     * @returns string the html code to display the phrase group with reference links
     */
    function name_linked(phrase_list_min $phr_lst_header = null): string
    {
        return $this->grp->name_linked($phr_lst_header);
    }

    /**
     * @returns string the html code to display the value with reference links
     * TODO create a popup with the details e.g. the values of other users
     */
    function value_linked(): string
    {
        return $this->val;
    }

    /**
     * @returns phrase_list_min the list of phrases of this phrase group
     */
    function phr_lst(): phrase_list_min
    {
        $result = new phrase_list_min();
        if ($this->grp != null) {
            $result->set_lst($this->grp->lst());
        }
        return $result;
    }

    function load_phrases(): bool
    {
        return $this->grp->load_phrases();
    }

}
