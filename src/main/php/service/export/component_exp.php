<?php

/*

  component_exp.php - the simple export object for a view component including the position that is save in the component link
  ----------------------
  
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

class component_exp extends sandbox_exp_named
{

    // field names used for JSON creation
    public ?int $position = 0;
    public ?string $type = '';
    public ?string $code_id = '';
    public ?string $ui_msg_code_id = '';
    public ?string $row = '';
    public ?string $column = '';
    public ?string $column2 = '';
    public ?string $description = '';

    function reset(): void
    {
        parent::reset();

        $this->position = 0;
        $this->type = '';
        $this->code_id = '';
        $this->ui_msg_code_id = '';
        $this->row = '';
        $this->column = '';
        $this->column2 = '';
        $this->description = '';
    }

}
