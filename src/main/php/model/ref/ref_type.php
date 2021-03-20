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

$ref_type_lst = array();      // list of reference type objects with id as the key
$ref_type_name_lst = array(); // list of reference type ids with the name as the key

class ref_type {

  // database fields
  public $id           = NULL; // the database id of the link type
  public $name         = '';   // the name that is displayed to the user
  public $description  = '';   // the tool tip that is shown to the user upon request by mouse over
  public $code_id      = '';   // to link the behavior to a reference type
  public $url          = '';   // the url that can be used to receive data if the external key is added

  // in memory only fields
  public $usr      = NULL;     // just needed for logging the changes
    
  // display the unique id fields
  public function dsp_id () {
    $result = ''; 

    $result .= $this->name; 
    if ($result <> '') {
      if ($this->id > 0) {
        $result .= ' ('.$this->id.')';
      }  
    } else {
      $result .= $this->id;
    }
    return $result;
  }

  public function name () {
    return $this->name;
  }

}

// load all reference types if needed
function load_ref_types($debug) {
  zu_debug('ref_type->load_ref_types', $debug-10);

  global $db_con;
  global $usr;
  global $ref_type_lst;
  global $ref_type_name_lst;
  
  if (empty($ref_type_lst)) {
    //$db_con = New mysql;
    $db_con->usr_id = $usr->id;         
    $db_lst = $db_con->load_types('ref_type', array('base_url'), $debug-14);  
    foreach ($db_lst AS $db_row) {
      $id = $db_row['ref_type_id'];
      $name = $db_row['ref_type_name'];
      if ($id <= 0) {
        zu_err('A reference type with id '.$id.' is in the database, but the id should always be greater zero.', 'load_ref_types', '', (new Exception)->getTraceAsString(), $usr);
      } elseif ($name == '') {
        zu_err('A reference type with an empty name is in the database, but a name must be set', 'load_ref_types', '', (new Exception)->getTraceAsString(), $usr);
      } else {
        zu_debug('ref_type->load_ref_types -> add '.$name, $debug-18);
        $ref_type = New ref_type;
        $ref_type->usr         = $usr;
        $ref_type->id          = $db_row['ref_type_id']; // TODO needed??
        $ref_type->name        = $db_row['ref_type_name'];
        $ref_type->description = $db_row['description'];
        $ref_type->code_id     = $db_row['code_id'];
        $ref_type->url         = $db_row['base_url'];
        $ref_type_lst[$id] = $ref_type;
        $ref_type_name_lst[$name] = $id;
      }
    }  
    zu_debug('ref_type->load_ref_types -> loaded '.count($ref_type_lst), $debug-12);
  }  
}

// get a reference type object based on the id
function get_ref_type($id, $debug) {
  zu_debug('ref_type->get_ref_type', $debug-10);

  global $usr;
  global $ref_type_lst;
  
  $result = NULL;
  load_ref_types($debug-1);
  if (!array_key_exists($id, $ref_type_lst)) {
    zu_err('A reference type with id '.$id.' is not found.', 'get_ref_type', '', (new Exception)->getTraceAsString(), $usr);
  } else {
    $result = $ref_type_lst[$id];
    zu_debug('ref_type->get_ref_type -> done '.$result->dsp_id(), $debug-12);
  }
  
  return $result;  
}

// get a reference type object based on the name
function get_ref_type_by_name($name, $debug) {
  zu_debug('ref_type->get_ref_type_by_name', $debug-10);

  global $usr;
  global $ref_type_name_lst;
  
  $result = NULL;
  load_ref_types($debug-1);
  if (!array_key_exists($name, $ref_type_name_lst)) {
    zu_err('A reference type with name '.$name.' is not found.', 'get_ref_type_by_name', '', (new Exception)->getTraceAsString(), $usr);
  } else {
    $id = $ref_type_name_lst[$name];
    $result = get_ref_type($id, $debug-1);
    zu_debug('ref_type->get_ref_type_by_name -> done '.$result->dsp_id(), $debug-12);
  }
  
  return $result;  
}