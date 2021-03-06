<?php

/*

  ref_type.php - the base object for links between a phrase and another system such as wikidata
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
  along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.
  
  To contact the authors write to:
  Timon Zielonka <timon@zukunft.com>
  
  Copyright (c) 1995-2021 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
   
*/

// TODO: check if this really needs to be global
$ref_type_lst = array();      // list of reference type objects with id as the key
$ref_type_name_lst = array(); // list of reference type ids with the name as the key

class ref_type
{

    // database fields
    public ?int $id = null;              // the database id of the link type
    public ?string $name = null;         // the name that is displayed to the user
    public ?string $description = null;  // the tool tip that is shown to the user upon request by mouse over
    public ?string $code_id = null;      // to link the behavior to a reference type
    public ?string $url = null;          // the url that can be used to receive data if the external key is added

    // in memory only fields
    public ?user $usr = null;            // just needed for logging the changes

    // display the unique id fields
    public function dsp_id(): string
    {
        $result = '';

        $result .= $this->name;
        if ($result <> '') {
            if ($this->id > 0) {
                $result .= ' (' . $this->id . ')';
            }
        } else {
            $result .= $this->id;
        }
        return $result;
    }

    public function name(): string
    {
        return $this->name;
    }

}

// load all reference types if needed
function load_ref_types()
{
    log_debug('ref_type->load_ref_types');

    global $db_con;
    global $usr;
    global $ref_type_lst;
    global $ref_type_name_lst;

    if (empty($ref_type_lst)) {
        //$db_con = New mysql;
        $db_con->usr_id = $usr->id;
        $db_lst = $db_con->load_types('ref_type', array('base_url'));
        foreach ($db_lst as $db_row) {
            $id = $db_row['ref_type_id'];
            $name = $db_row['ref_type_name'];
            if ($id <= 0) {
                log_err('A reference type with id ' . $id . ' is in the database, but the id should always be greater zero.', 'load_ref_types', '', (new Exception)->getTraceAsString(), $usr);
            } elseif ($name == '') {
                log_err('A reference type with an empty name is in the database, but a name must be set', 'load_ref_types', '', (new Exception)->getTraceAsString(), $usr);
            } else {
                log_debug('ref_type->load_ref_types -> add ' . $name);
                $ref_type = new ref_type;
                $ref_type->usr = $usr;
                $ref_type->id = $db_row['ref_type_id']; // TODO needed??
                $ref_type->name = $db_row['ref_type_name'];
                $ref_type->description = $db_row['description'];
                $ref_type->code_id = $db_row['code_id'];
                $ref_type->url = $db_row['base_url'];
                $ref_type_lst[$id] = $ref_type;
                $ref_type_name_lst[$name] = $id;
            }
        }
        log_debug('ref_type->load_ref_types -> loaded ' . count($ref_type_lst));
    }
}

// get a reference type object based on the id
function get_ref_type($id)
{
    log_debug('ref_type->get_ref_type');

    global $usr;
    global $ref_type_lst;

    $result = null;
    load_ref_types();
    if (!array_key_exists($id, $ref_type_lst)) {
        log_err('A reference type with id ' . $id . ' is not found.', 'get_ref_type', '', (new Exception)->getTraceAsString(), $usr);
    } else {
        $result = $ref_type_lst[$id];
        log_debug('ref_type->get_ref_type -> done ' . $result->dsp_id());
    }

    return $result;
}

// get a reference type object based on the name
function get_ref_type_by_name($name)
{
    log_debug('ref_type->get_ref_type_by_name');

    global $usr;
    global $ref_type_name_lst;

    $result = null;
    load_ref_types();
    if (!array_key_exists($name, $ref_type_name_lst)) {
        log_err('A reference type with name ' . $name . ' is not found.', 'get_ref_type_by_name', '', (new Exception)->getTraceAsString(), $usr);
    } else {
        $id = $ref_type_name_lst[$name];
        $result = get_ref_type($id);
        log_debug('ref_type->get_ref_type_by_name -> done ' . $result->dsp_id());
    }

    return $result;
}