<?php

/*

    model/view/component_type_list.php - to link coded functionality to a view component
    ---------------------------------------

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

namespace cfg\component;

use cfg\sql_db;
use cfg\type_list;
use cfg\type_object;

include_once DB_PATH . 'sql_db.php';
include_once MODEL_COMPONENT_PATH . 'component_type.php';
include_once MODEL_HELPER_PATH . 'type_list.php';
include_once MODEL_HELPER_PATH . 'type_object.php';

global $component_types;

class component_type_list extends type_list
{

    /**
     * overwrite the general user type list load function to keep the link to the table type capsuled
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @return bool true if load was successful
     */
    function load(sql_db $db_con, string $db_type = sql_db::TBL_COMPONENT_TYPE): bool
    {
        return parent::load($db_con, $db_type);
    }

    /**
     * adding the view component types used for unit tests to the dummy list
     */
    function load_dummy(): void {
        parent::load_dummy();
        $type = new type_object(component_type::TEXT, component_type::TEXT, '', 2);
        $this->add($type);
        $type = new type_object(component_type::PHRASE_NAME, component_type::PHRASE_NAME, '', 8);
        $this->add($type);
        $type = new type_object(component_type::FORM_TITLE, component_type::FORM_TITLE, '', 17);
        $this->add($type);
        $type = new type_object(component_type::FORM_BACK, component_type::FORM_BACK, '', 18);
        $this->add($type);
        $type = new type_object(component_type::FORM_CONFIRM, component_type::FORM_CONFIRM, '', 19);
        $this->add($type);
        $type = new type_object(component_type::FORM_NAME, component_type::FORM_NAME, '', 20);
        $this->add($type);
        $type = new type_object(component_type::FORM_DESCRIPTION, component_type::FORM_DESCRIPTION, '', 21);
        $this->add($type);
        $type = new type_object(component_type::FORM_SHARE_TYPE, component_type::FORM_SHARE_TYPE, '', 22);
        $this->add($type);
        $type = new type_object(component_type::FORM_PROTECTION_TYPE, component_type::FORM_PROTECTION_TYPE, '', 23);
        $this->add($type);
        $type = new type_object(component_type::FORM_CANCEL, component_type::FORM_CANCEL, '', 24);
        $this->add($type);
        $type = new type_object(component_type::FORM_SAVE, component_type::FORM_SAVE, '', 25);
        $this->add($type);
        $type = new type_object(component_type::FORM_END, component_type::FORM_END, '', 26);
        $this->add($type);
    }

    /**
     * return the database id of the default view component type
     */
    function default_id(): int
    {
        return parent::id(component_type::TEXT);
    }

}
