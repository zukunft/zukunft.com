<?php

/*

    sandbox_exp_link.php - the superclass for the link export objects
    -------------------------


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

class sandbox_exp_link extends sandbox_exp
{
    // field names used for JSON creation
    public ?string $name = '';   // the target (to) object name, which cannot be empty; the source (from) object is defined by the placement in the JSON
    public ?string $share = null;     // the share permissions of the object; null means that the default share type is used whereas an empty string means that the share type should be overwritten with the default share type
    public ?string $protection= null; // the protection of the given object; TODO check that empty string over writes the setting

    // reset the search values of this object
    function reset(): void
    {
        $this->name = '';
        $this->share = null;
        $this->protection = null;
    }

}