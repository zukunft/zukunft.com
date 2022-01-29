<?php

/*

  formula_exp.php - the simple export object for a formula
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
  along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.
  
  To contact the authors write to:
  Timon Zielonka <timon@zukunft.com>
  
  Copyright (c) 1995-2022 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

class formula_exp extends user_sandbox_exp_named
{

    // field names used for JSON creation
    public ?string $expression = '';
    public ?string $description = '';
    public ?string $type = '';
    public ?string $assigned_word = '';

    function reset()
    {
        parent::reset();

        $this->expression = '';
        $this->description = '';
        $this->type = '';

        $this->assigned_word = '';
    }

}
