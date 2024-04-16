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

include_once SHARED_TYPES_PATH . 'component_type.php';
include_once DB_PATH . 'sql_db.php';
include_once MODEL_COMPONENT_PATH . 'component_type.php';
include_once MODEL_HELPER_PATH . 'type_list.php';
include_once MODEL_HELPER_PATH . 'type_object.php';

use shared\types\component_type as comp_type_shared;
use cfg\db\sql_db;
use cfg\type_list;
use cfg\type_object;

global $component_types;

class component_type_list extends type_list
{

    /**
     * adding the view component types used for unit tests to the dummy list
     */
    function load_dummy(): void {
        parent::load_dummy();
        $type = new type_object(comp_type_shared::TEXT, comp_type_shared::TEXT, '', 2);
        $this->add($type);
        $type = new type_object(comp_type_shared::PHRASE_NAME, comp_type_shared::PHRASE_NAME, '', 8);
        $this->add($type);
        $type = new type_object(comp_type_shared::FORM_TITLE, comp_type_shared::FORM_TITLE, '', 17);
        $this->add($type);
        $type = new type_object(comp_type_shared::FORM_BACK, comp_type_shared::FORM_BACK, '', 18);
        $this->add($type);
        $type = new type_object(comp_type_shared::FORM_CONFIRM, comp_type_shared::FORM_CONFIRM, '', 19);
        $this->add($type);
        $type = new type_object(comp_type_shared::FORM_NAME, comp_type_shared::FORM_NAME, '', 20);
        $this->add($type);
        $type = new type_object(comp_type_shared::FORM_DESCRIPTION, comp_type_shared::FORM_DESCRIPTION, '', 21);
        $this->add($type);
        $type = new type_object(comp_type_shared::FORM_PHRASE, comp_type_shared::FORM_PHRASE, '', 22);
        $this->add($type);
        $type = new type_object(comp_type_shared::FORM_VERB_SELECTOR, comp_type_shared::FORM_VERB_SELECTOR, '', 23);
        $this->add($type);
        $type = new type_object(comp_type_shared::FORM_SHARE_TYPE, comp_type_shared::FORM_SHARE_TYPE, '', 24);
        $this->add($type);
        $type = new type_object(comp_type_shared::FORM_PROTECTION_TYPE, comp_type_shared::FORM_PROTECTION_TYPE, '', 25);
        $this->add($type);
        $type = new type_object(comp_type_shared::FORM_CANCEL, comp_type_shared::FORM_CANCEL, '', 26);
        $this->add($type);
        $type = new type_object(comp_type_shared::FORM_SAVE, comp_type_shared::FORM_SAVE, '', 27);
        $this->add($type);
        $type = new type_object(comp_type_shared::FORM_DEL, comp_type_shared::FORM_DEL, '', 28);
        $this->add($type);
        $type = new type_object(comp_type_shared::FORM_END, comp_type_shared::FORM_END, '', 29);
        $this->add($type);
        $type = new type_object(comp_type_shared::ROW_START, comp_type_shared::ROW_START, '', 30);
        $this->add($type);
        $type = new type_object(comp_type_shared::ROW_RIGHT, comp_type_shared::ROW_RIGHT, '', 31);
        $this->add($type);
        $type = new type_object(comp_type_shared::ROW_END, comp_type_shared::ROW_END, '', 32);
        $this->add($type);
    }

    /**
     * return the database id of the default view component type
     */
    function default_id(): int
    {
        return parent::id(comp_type_shared::TEXT);
    }

}
