<?php

/*

  zu_lib_link.php - old verb (word link type) functions (just just for regression code testing)
  ---------------

  prefix: zul_* 
  prefix: zutl_* 

  word links are the "verbs" that combine two words to a RDF tripple


  get functions
  ---
  
  zul_plural_reverse - return the link word name for more than one item and for the reverse relation
  zul_reverse        - 
  zul_plural         -
  zul_name           -
  zul_id             - the word link / verb database id independent from the user; what if a user has renamed a verb? 
  zul_type           -

  
  display functions
  -------
  
  zul_dsp_list       - display all verbs and allow an admin to change it
  zutl_dsp           - display all verbs and allow an admin to change it

  
zukunft.com - calc with words

copyright 1995-2021 by zukunft.com AG, Blumentalstrasse 15, 8707 Uetikon am See, Switzerland

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/


// return the link word name for more than one item and for the reverse relation
// e.g. for the link "is a" the plural reverse is "are": "ABB is a company and Roche is a company" -> "Companies are ABB and Roche"
function zul_plural_reverse ($id) {
  return zu_sql_get_field ('verb', $id, 'name_plural_reverse');
}

function zul_reverse ($id) {
  return zu_sql_get_field ('verb', $id, 'name_reverse');
}

function zul_plural ($id) {
  return zu_sql_get_field ('verb', $id, 'name_plural');
}

// name including the user id as a parameter to return the verb in the lanuage of the user
function zul_name ($id) {
  return zu_sql_get_field ('verb', $id, 'verb_name');
}

// the verb id for the given name
function zul_id ($name) {
  log_debug('zul_id('.$name.')');
  return zu_sql_get_id ('verb', $name);
}

// return the link type id
function zul_type ($name) {
  return zu_sql_get_field ('verb', $name, 'verb_name');
}

// display all verbs and allow an admin to change it
function zul_dsp_list ($user_id) {
  log_debug('zul_dsp_list('.$user_id.')');
  $result  = "";

  $verb_lst = zu_sql_verbs($user_id);
  $result .= zuh_list($verb_lst, "verb");

  return $result;
}

// show the html form to add a new verb 
function zul_dsp_add ($verb_name, $name_plural, $name_reverse, $name_plural_reverse, $user_id, $back_link) {
  log_debug('zul_dsp_add ('.$verb_name.','.$name_plural.','.$name_reverse.','.$name_plural_reverse.','.$user_id.','.$back_link.')');
  $result = '';
  
  $result .= zuh_text_h2('Add verb (word link type)');
  $result .= zuh_form_start("verb_add");
  $result .= zuh_tbl_start_half();
  $result .= '  <tr>';
  $result .= '    <td>';
  $result .= '      verb name:';
  $result .= '    </td>';
  $result .= '    <td>';
  $result .= '      <input type="text" name="verb_name" value="'.$verb_name.'">';
  $result .= '    </td>';
  $result .= '  </tr>';
  $result .= '  <tr>';
  $result .= '    <td>';
  $result .= '      verb plural:';
  $result .= '    </td>';
  $result .= '    <td>';
  $result .= '      <input type="text" name="verb_plural" value="'.$name_plural.'">';
  $result .= '    </td>';
  $result .= '  </tr>';
  $result .= '  <tr>';
  $result .= '    <td>';
  $result .= '      reverse:';
  $result .= '    </td>';
  $result .= '    <td>';
  $result .= '      <input type="text" name="verb_reverse" value="'.$name_reverse.'">';
  $result .= '    </td>';
  $result .= '  </tr>';
  $result .= '  <tr>';
  $result .= '    <td>';
  $result .= '      plural_reverse:';
  $result .= '    </td>';
  $result .= '    <td>';
  $result .= '      <input type="text" name="verb_plural_reverse" value="'.$name_plural_reverse.'">';
  $result .= '    </td>';
  $result .= '  </tr>';
  $result .= '  <input type="hidden" name="back" value="'.$back_link.'">';
  $result .= '  <input type="hidden" name="confirm" value="1">';
  $result .= zuh_tbl_end();
  $result .= zuh_form_end();

  log_debug('zul_dsp_add ... done');
  return $result;
}

