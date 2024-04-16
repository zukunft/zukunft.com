<?php

/*

    model/sandbox/protection_type_list.php - a database based enum list for the data protection types
    --------------------------------------


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

    Copyright (c) 1995-2023 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

namespace cfg;

include_once SHARED_TYPES_PATH . 'protection_type.php';
include_once DB_PATH . 'sql_db.php';
include_once MODEL_SANDBOX_PATH . 'protection_type.php';

use shared\types\protection_type as protect_type_shared;

global $protection_types;

class protection_type_list extends type_list
{

    /**
     * create dummy type list for the unit tests without database connection
     */
    function load_dummy(): void
    {
        $this->reset();
        $type = new type_object(protect_type_shared::NO_PROTECT, protect_type_shared::NO_PROTECT, '', 1);
        $this->add($type);
        $type = new type_object(protect_type_shared::USER, protect_type_shared::USER, '', 2);
        $this->add($type);
        $type = new type_object(protect_type_shared::ADMIN, protect_type_shared::ADMIN, '', 3);
        $this->add($type);
        $type = new type_object(protect_type_shared::NO_CHANGE, protect_type_shared::NO_CHANGE, '', 4);
        $this->add($type);
    }

    /**
     * return the database id of the default protection type
     */
    function default_id(): int
    {
        return parent::id(protect_type_shared::NO_PROTECT);
    }

}