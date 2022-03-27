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

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

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

// name including the user id as a parameter to return the verb in the language of the user
function zul_name ($id) {
  return zu_sql_get_field ('verb', $id, 'verb_name');
}

// the verb id for the given name
function zul_id ($name) {
  log_debug('zul_id('.$name.')');
  return zu_sql_get_id ('verb', $name);
}
