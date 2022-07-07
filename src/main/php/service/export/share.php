<?php

/*

  share.php - object to handle the sharing of values and formulas
  ---------
  
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

// list of all share type
// this can always be in memory because this will change almost never and if it changes it it still shows the old display name it is fine
$share_types = array();

class share_type {

  // database fields
  public $id          = null; // the database id of the state type
  public $name        = null; // the person who wants to see something
  public $comment     = '';   // the source description that is shown as a mouseover explain to the user
  public $code_id     = '';   // to trigger the code functions linked to this share type
  
  // in memory only fields
  public $type_name    = '';   // 
  
  // true if the user is allow to see the value or formulas
  function can_read() {
    $result = '';
    
    return $result;
  }
  
  // load the missing source parameters from the database
  function load() {
  }
  
}

