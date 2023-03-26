<?php

/*

    word_exp.php - the simple export object for a word
    ------------

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

namespace cfg\export;

class word_exp extends sandbox_exp_named
{

    // field names used for JSON creation
    public ?string $plural = '';
    public ?string $description = '';
    public ?string $type = '';
    public ?string $view = '';
    public ?array $refs = [];

    function reset()
    {
        parent::reset();

        $this->plural = '';
        $this->description = '';
        $this->type = '';

        $this->view = '';
        $this->refs = [];
    }

}
