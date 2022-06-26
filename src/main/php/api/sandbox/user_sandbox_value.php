<?php

/*

    api\user_sandbox_value.php - the minimal superclass for the frontend API
    --------------------------

    This superclass should be used by the classes word_min, formula_min, ... to enable user specific values and links


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

class user_sandbox_value_api extends user_sandbox_api
{

    private phrase_group_api $grp; // the phrase group with the list of words and triples (not the source words and triples)
    private ?float $val; // the number calculated by the system

    /*
     * construct and map
     */

    function __construct(int $id = 0)
    {
        parent::__construct($id);

        $this->grp = new phrase_group_api();
        $this->val = null;
    }

    /*
     * set and get
     */

    public function set_grp(phrase_group_api $grp)
    {
        $this->grp = $grp;
    }

    public function set_val(float $val)
    {
        $this->val = $val;
    }

    public function grp(): phrase_group_api
    {
        return $this->grp;
    }

    public function val(): float
    {
        return $this->val;
    }

    /**
     * @returns phrase_list_api the list of phrases as an object
     */
    function phr_lst(): phrase_list_api
    {
        return $this->grp->phr_lst();
    }

    /**
     * @param phrase_list_api|null $phr_lst_header list of phrases that are shown already in the context e.g. the table header and that should not be shown again
     * @returns string the html code to display the phrase group with reference links
     */
    public function name_linked(phrase_list_api $phr_lst_header = null): string
    {
        return $this->grp->name_linked($phr_lst_header);
    }

    /**
     * @returns string the html code to display the value with reference links
     * TODO create a popup with the details e.g. the values of other users
     */
    public function value_linked(): string
    {
        return $this->val;
    }

    /*
    function load_phrases(): bool
    {
        return $this->grp->load_phrases();
    }
    */

}


