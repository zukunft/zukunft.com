<?php

/*

  display_interface.php - this module contain all interface functions that should be used
  ---------------------
  
  depending on the settings either pure HTML, BOOTSTRAP HTML or JavaScript functions are called
  
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

// display a message immediately to the user
function ui_echo($text, $style = '') {
  echo $text;
}

// display a progress bar
// TODO create a auto refresh page for async processes and the HTML front end without JavaScript
// TODO create a db table, where the async process can drop the status
// TODO add the refresh frequency setting to the general and user settings
function ui_progress($id, $value, $max, $text) {
    echo $text;
}

