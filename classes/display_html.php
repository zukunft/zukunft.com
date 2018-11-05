<?php

/*

  display_html.php - all html code should be in this library
  ----------------
  
  
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
  
  Copyright (c) 1995-2018 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

// the general html header
function dsp_header($title, $style) {
  $result  = '';
  $result .= '<html>';
  $result .= '  <head>';
  if ($title <> "") {
    $result .= '    <title>'.$title.' (zukunft.com)</title>';
  } else {
    $result .= '    <title>zukunft.com</title>';
  }  
  $result .= '    <link rel="stylesheet" type="text/css" href="../style/style.css" />';
  $result .= '  </head>';
  if ($style <> "") {
    $result .= '  <body class="'.$style.'">';
  } else {
    $result .= '  <body>';
  }  

  return $result;
}

// the general html footer
function dsp_footer($no_about, $empty) {
  $result  = '';
  $result .= '  </body>';
  // for the api e.g. the csv export no footer should be shown
  if (!$empty) {
    $result .= '  <footer>';
    $result .= '  <div class="footer">';
    $result .= '  <p>All structured data is available under the <a href="//creativecommons.org/publicdomain/zero/1.0/" title="Definition of the Creative Commons CC0 License">Creative Commons CC0 License</a></p>';
    // for the about page this does not make sense
    $result .= '  <p>';
    if (!$no_about) {
      $result .= '  <a href="/http/about.php" title="About">About zukunft.com</a>';
    }
    $result .= '  <a href="/http/privacy_policy.html" title="About">Privacy Policy</a> ';
    $result .= '  </p>';
    $result .= '  </div>';
    $result .= '  </footer>';
  }
  $result .= '</html>';

  return $result;
}


// ----------------------
// output device specific
// ----------------------

// get the normal table width (should be based on the display size)
function dsp_tbl_width () {
  $result = '800px';
  return $result;
}
function dsp_tbl_width_half () {
  $result = '400px';
  return $result;
}

// display an explaining subline e.g. (in mio CHF)
function dsp_line_small ($line_text) {
  return "<small>".$line_text."</small><br>";
}



// ------------------------
// single element functions
// ------------------------

// simply to display headline text
function dsp_text_h1 ($title, $style) {
  if ($style <> "") {
    return '<h2 class="'.$style.'">'.$title.'</h2>';
  } else {  
    return "<h1>".$title."</h1>";
  }  
}
function dsp_text_h2 ($title, $style) {
  if ($style <> "") {
    return '<h2 class="'.$style.'">'.$title.'</h2>';
  } else {  
    return "<h2>".$title."</h2>";
  }  
}
function dsp_text_h3 ($title, $style) {
  if ($style <> "") {
    return '<h3 class="'.$style.'">'.$title.'</h3>';
  } else {  
    return '<h3>'.$title.'</h3>';
  }  
}

// simply to display a single word in a table as a header
function dsp_tbl_head ($link_name, $debug) {
  zu_debug('dsp_tbl_head', $debug-20);
  $result  = '    <th>'."\n";
  $result .= '      '.$link_name."\n";
  $result .= '    </th>'."\n";
  return $result;
}

// simply to display a single word in a table as a header
function dsp_tbl_head_right ($link_name, $debug) {
  zu_debug('dsp_tbl_head_right', $debug-20);
  $result  = '    <th>'."\n";
  $result .= '      <p align="right">'.$link_name.'</p>'."\n";
  $result .= '    </th>'."\n";
  return $result;
}

// after simple add views e.g. for a value automatically go back to the calling page
function dsp_go_back($back, $usr, $debug) {
  zu_debug('dsp_go_back('.$back.')', $debug-20);

  $result = '';

  if ($back == '') {
    zu_err("Internal error: go back page missing.", "dsp_header->dsp_go_back", '', (new Exception)->getTraceAsString(), $this->usr);
    header("Location: view.php?words=1"); // go back to the fallback page
  } else {
    if (is_numeric($back)) {
      header("Location: view.php?words=".$back.""); // go back to the calling page and try to avoid double change script calls
    } else {
      header("Location: ".$back.""); // go back to the calling page and try to avoid double change script calls
    }
  }

  return $result;
}

// start a html form; the form name must be identical with the php script name
function dsp_form_start ($form_name) {
  // switch on post forms for private values
  // return '<form action="'.$form_name.'.php" method="post" id="'.$form_name.'">';
  return '<form action="'.$form_name.'.php" id="'.$form_name.'">';
}

// end a html form
function dsp_form_end ($submit_name) {
  if ($submit_name == "") {
    return '<input type="submit"></form>';
  } else {  
    return '<input type="submit" value="'.$submit_name.'">';
  }
}

// add the element id, which should always be using the field "id"
function dsp_form_id ($id) {
  return '<input type="hidden" name="id" value="'.$id.'">';
}

// add the hidden field
function dsp_form_hidden ($field, $id) {
  return '<input type="hidden" name="'.$field.'" value="'.$id.'">';
}

// add the text field to a form
function dsp_form_text ($field, $txt_value) {
  return ''.$field.': <input type="text" name="'.$field.'" value="'.$txt_value.'">';
}

// add the text big field to a form
function dsp_form_text_big ($field, $txt_value) {
  return ''.$field.': <input type="text" name="'.$field.'" class="resizedTextbox" value="'.$txt_value.'">';
}

// add the field to a form
function dsp_form_fld ($field, $txt_value) {
  return '<input name="'.$field.'" value="'.$txt_value.'">';
}

function dsp_tbl_start () {
  $result = '<table style="width:'.dsp_tbl_width ().'">'."\n";
  return $result;
}

function dsp_tbl_start_half () {
  $result = '<table style="width:'.dsp_tbl_width_half ().'">'."\n";
  return $result;
}

function dsp_tbl_end () {
  $result = '</table>'."\n";
  return $result;
}

// simply to display an error text interactivly to the user; use this function always for easy redesign of the error messages
function dsp_err ($err_text) {
  return '<font color="red">'.$err_text.'</font>';
}

// display a list of elements
function dsp_list ($item_lst, $item_type, $debug) {
  $result  = "";

  $edit_script = $item_type."_edit.php";
  $add_script  = $item_type."_add.php";
  foreach ($item_lst as $item) {
    $result .=  '<a href="/http/'.$edit_script.'?id='.$item->id.'">'.$item->name.'</a><br> ';
  }
  $result .= zuh_btn_add ('Add '.$item_type, $add_script);
  $result .= '<br>';

  return $result;
}



?>
