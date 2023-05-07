<?php

/*

    service/export/verb_exp.php - the simple export object for a verb
    ---------------------------

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


use cfg\export\sandbox_exp_named;

class verb_exp extends sandbox_exp_named
{

    // field names used for JSON creation
    public ?string $description = '';
    public ?string $code_id= '';
    public ?string $name_reverse = '';
    public ?string $name_plural = '';
    public ?string $name_plural_reverse = '';

    function reset(): void
    {
        sandbox_exp_named::reset();

        $this->description = '';
        $this->code_id = '';
        $this->name_reverse = '';
        $this->name_plural = '';
        $this->name_plural_reverse = '';
    }

}
