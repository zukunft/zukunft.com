<?php

/*

    api\formula.php - the minimal formula object for the frontend API
    ---------------


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

namespace api;

use html\formula_dsp;

class formula_api extends user_sandbox_named_api
{

    // formulas for stand-alone unit tests
    // for database based test formulas see model/formula/formula.php
    const TF_SECTOR = '= "Country" "differentiator" "Canton" / "Total"';
    const TF_SECTOR_REF = '= {t1} {v1} {t2} / {t3}';

    // the formula expression as shown to the user
    private string $usr_text;

    /*
     * construct and map
     */

    function __construct(int $id = 0, string $name = '')
    {
        parent::__construct($id, $name);
        $this->usr_text = '';
    }

    /*
     * set and get
     */

    public function set_usr_text(string $usr_text)
    {
        $this->usr_text = $usr_text;
    }

    public function usr_text(): string
    {
        return $this->usr_text;
    }

    /*
     * casting objects
     */

    /**
     * @returns formula_dsp the cast object with the HTML code generating functions
     */
    function dsp_obj(): formula_dsp
    {
        $dsp_obj = new formula_dsp($this->id, $this->name);
        $dsp_obj->set_usr_text($this->usr_text());
        return $dsp_obj;
    }

}
