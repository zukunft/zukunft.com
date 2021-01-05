<?php 

/*

  about.php - display the legal information
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
  along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.
  
  To contact the authors write to:
  Timon Zielonka <timon@zukunft.com>
  
  Copyright (c) 1995-2021 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

// standard zukunft header for callable php files to allow debugging and lib loading
if (isset($_GET['debug'])) { $debug = $_GET['debug']; } else { $debug = 0; }
include_once '../lib/zu_lib.php'; if ($debug > 0) { echo 'libs loaded<br>'; }

// open database 
$db_con = zu_start("about", "center_form", $debug);

  $result = ''; // reset the html code var

  $result .= dsp_form_center(); 
  $result .= dsp_logo_big(); 
  $result .= '<br><br>'; 
  $result .= 'is sponsored by <br><br>'; 
  $result .= 'zukunft.com AG<br>'; 
  $result .= 'Blumentalstrasse 15<br>'; 
  $result .= '8707 Uetikon am See<br>'; 
  $result .= 'Switzerland<br><br>'; 
  $result .= '<a href="mailto:timon@zukunft.com">timon@zukunft.com</a><br><br>'; 
  $result .= 'zukunft.com AG also supports the '; 
  $result .= '<a href="https://github.com/zukunft/tream" title="github.com link">Open Source</a> Portfolio Management System<br><br>'; 
  $result .= '<a href="https://tream.biz/p4a/applications/tream/" title="TREAM demo">'; 
  $result .= '<img src="../images/TREAM_logo.jpg" alt="TREAM" style="height: 20%;">'; 
  $result .= '</a><br><br>'; 
  $result .= '</div>   ';

  // display the view
  echo $result;

// close the database  
zu_end_about($db_con, $debug);
