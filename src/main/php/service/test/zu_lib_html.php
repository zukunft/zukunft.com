<?php

/*

  zu_lib_html.php - Zukunft html related functions
  ---------------

  prefix: zuh_* 

  This library should be the only place where HTML code is used
  This way the output can be adjusted easy


  general zukunft functions to create the standard zukunft.com elements
  -------
  
  zuh_header            - the general html header
  zuh_footer            - the general html footer
  zuh_top_right         - show the standard top right corner, where the user can login
  zuh_top_right_no_view - same as zuh_top_right, but without the view change used for the view editors

  
  single element functions
  --------------
  
  zuh_text_h1 / h2 / h3 - simply to display headline text
  zuh_tbl_start         
  zuh_btn_add           - an add button to create a new entry
  zuh_btn_edit          - an edit button to adjust an entry
  zuh_btn_del           - an delete button to remove an entry
  zuh_btn_find          - a find button to search for a word
  zuh_btn_unfilter      - remove a filter
  zuh_btn_yesno         - ask a yes/no question with the defaut calls
  zuh_form_start        - start a html form; the form name must be identical with the php script name
  zuh_form_end          - end a html form
  zuh_form_id           - add the element id, which should always be using the field "id"
  zuh_form_hidden       - add the hidden field
  zuh_form_fld 

  
  output device functions that change the result depending on the selected output device
  -------------
  
  zuh_tbl_width - 

  
  complex display functions
  ---------------
  
  zuh_selector      - return the link word name for more than one item and for the reverse relation
  zuh_selector_lst  - similar to zuh_selector but using a list not a query
  zuh_selector_page - create a selection page where the user e.g. can select a view
  zuh_list_sort     - display a list that can be sorted using the fixed field "order_nbr"

  
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




// ---------------------------------
// main zukunft.com display elements
// ---------------------------------

// the general html header
function zuh_header($title, $style) {
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
function zuh_footer($no_about, $empty) {
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

// same as zuh_top_right, but without the view change used for the view editors
function zuh_top_right_user($view_id, $view_name, $word_id) {
  $html = new html_base();
  log_debug('zuh_top_right_user('.$view_id.','.$view_name.')');
  $result  = '';
  $result .= '<table style="width:100%">';
  $result .= '<tr><td>';
  $result .= $html->logo();
  $result .= '</td><td class="right_ref">';
  //$result  = '<div class="right_ref">';
  if ($_SESSION['logged']) { 
    $result .= '<a href="/http/user.php?id='.$_SESSION['usr_id'].'&back='.$word_id.'">'.$_SESSION['user_name'].'</a>';
  } else {  
    $result .= '<a href="/http/login.php">log in</a> or <a href="/http/signup.php">Create account</a>';
  }
  return $result;
}

function zuh_top_right_logout() {
  log_debug('zuh_top_right_logout');
  if ($_SESSION['logged']) { 
    $result = ' <a href="/http/logout.php">log out</a>';
  } else {  
    $result = '';
  }
  return $result;
}
 



// after a word, value or formula has been added or changed go back to the calling page
function zuh_go_back($back, $user_id) {
  log_debug('zuh_go_back('.$back.')');

  $result = '';
  // old version for testing, which has the disadvantage of potential double creations
  // $result .= zut_dsp($back, $user_id);

  if (is_numeric($back)) {
    header("Location: view.php?words=".$back.""); // go back to the calling page and try to avoid double change script calls
  } else {
    header("Location: ".$back.""); // go back to the calling page and try to avoid double change script calls
  }

  return $result;
}


// ------------------------
// single element functions
// ------------------------

function zuh_text_h3 ($title, $style = '') {
  if ($style <> "") {
    return '<h3 class="'.$style.'">'.$title.'</h3>';
  } else {  
    return '<h3>'.$title.'</h3>';
  }  
}

// display an explaining subline e.g. (in mio CHF)
function zuh_line_small ($line_text) {
  return "<small>".$line_text."</small><br>";
}

// simply to display an error text interactivly to the user; use this function always for easy redesign of the error messages
function zuh_err ($err_text) {
  return '<font color="red">'.$err_text.'</font>';
}

function zuh_tbl_start () {
  $result = '<table style="width:'.zuh_tbl_width ().'">'."\n";
  return $result;
}

function zuh_tbl_start_half () {
  $result = '<table style="width:'.zuh_tbl_width_half ().'">'."\n";
  return $result;
}

function zuh_tbl_end () {
  $result = '</table>'."\n";
  return $result;
}

// display a button
function zuh_btn ($icon, $title, $call) {
  $result = '<a href="/http/'.$call.'" title="'.$title.'"><img src="'.$icon.'" alt="'.$title.'"></a>';
  return $result;
}

// display a bootstrap button
function zuh_btn_fa ($icon, $title, $call) {
  $result = '<a href="/http/'.$call.'" title="'.$title.'"><i class="far '.$icon.'"></i></a>';
  return $result;
}

// button function to keep the image call on one place
function zuh_btn_undo     ($title, $call) { return zuh_btn   (ZUH_IMG_UNDO,        $title, $call); } // an undo button to undo an change (not only the last)




// start a html form; the form name must be identical with the php script name
function zuh_form_start ($form_name) {
  return '<form action="'.$form_name.'.php" id="'.$form_name.'">';
}

// end a html form
function zuh_form_end ($submit_name) {
  if ($submit_name == "") {
    return '<input type="submit"></form>';
  } else {  
    return '<input type="submit" value="'.$submit_name.'">';
  }
}

// add the element id, which should always be using the field "id"
function zuh_form_id ($id) {
  return '<input type="hidden" name="id" value="'.$id.'">';
}

// add the hidden field
function zuh_form_hidden ($field, $id) {
  return '<input type="hidden" name="'.$field.'" value="'.$id.'">';
}

// add the field to a form
function zuh_form_fld ($field, $txt_value) {
  return '<input name="'.$field.'" value="'.$txt_value.'">';
}


// ----------------------
// output device specific
// ----------------------

// get the normal table width (should be based on the display size)
function zuh_tbl_width () {
  $result = '800px';
  return $result;
}
function zuh_tbl_width_half () {
  $result = '400px';
  return $result;
}


// -------------------------
// a little bit more complex display functions
// -------------------------

// return the link word name for more than one item and for the reverse relation
// the query should return the id and the description
// $name       - php field name for the selected value
// $from       - php script name that has called this selector and will be called after the selection
// $query      - sql query to select the values where the first column is the database id and the second the selection value
// $selected   - database id of the selected value; if 0 add a "please select ..." entry 
// $dummy_text - text that should be displayed instead of the default "please select ..."
function zuh_selector ($name, $form, $query, $selected, $dummy_text) {
  log_debug('zuh_selector ('.$name.','.$form.','.$query.',s'.$selected.','.$dummy_text.')');
  $result  = '';

  $result .= '<select name="'.$name.'" form="'.$form.'">';
  
  if ($selected == 0) {
    if ($dummy_text == '') {
      $result .= '      <option value="0" selected>please select ...</option>';
    } else {
      $result .= '      <option value="0" selected>'.$dummy_text.'</option>';
    }  
  }

  $sql_result = zu_sql_get_all($query);
  //$sql_result = mysqli_query($query) or die('Query failed: ' . mysqli_error());
  while ($word_entry = mysqli_fetch_array($sql_result, MySQLi_NUM)) {
    if ($word_entry[0] == $selected AND $selected <> 0) {
      log_debug('zuh_selector ... selected '.$word_entry[0]);
      $result .= '      <option value="'.$word_entry[0].'" selected>'.$word_entry[1].'</option>';
    } else {  
      //zu_debug('zuh_selector ... not selected '.$word_entry[0]);
      $result .= '      <option value="'.$word_entry[0].'">'.$word_entry[1].'</option>';
    }
  }

  $result .= '</select>';

  log_debug('zuh_selector ... done');
  return $result;
}

// similar to zuh_selector but using a list not a query
function zuh_selector_lst ($name, $form, $word_lst, $selected) {
  log_debug('zuh_selector_lst('.$name.','.$form.','.implode(",",array_keys($word_lst)).',s'.$selected.')');
  $result  = '';

  $result .= '<select name="'.$name.'" form="'.$form.'">';

  foreach (array_keys($word_lst) as $word_id) {
    if ($word_id == $selected) {
      log_debug('zuh_selector_lst ... selected '.$word_id);
      $result .= '      <option value="'.$word_id.'" selected>'.$word_lst[$word_id].'</option>';
    } else {  
      $result .= '      <option value="'.$word_id.'">'.$word_lst[$word_id].'</option>';
    }
  }

  $result .= '</select>';

  log_debug('zuh_selector_lst ... done');
  return $result;
}


