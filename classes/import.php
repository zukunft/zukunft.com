<?php

/*

  import.php - import data - take a prased object from a json, yaml or XML message and trigger the object saves  
  ----------
  
  if the user is an admin the import can force to set the standard
    
  TODO
  check that the formula results matches with the import
  check that the view returns a similar result

  
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
  
  Copyright (c) 1995-2020 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

class file_import {

  // parameters to filter the import
  public $usr      = NULL; // the user who wants to import data
  public $json_str = NULL; // a string with the json data to import
  public $words_done = 0;
  public $words_failed = 0;
  public $triples_done = 0;
  public $triples_failed = 0;
  public $formulas_done = 0;
  public $formulas_failed = 0;
  public $sources_done = 0;
  public $sources_failed = 0;
  public $values_done = 0;
  public $values_failed = 0;
  public $views_done = 0;
  public $views_failed = 0;
  
  // import zukunft.com data as object for creating e.g. a json message
  function put ($debug) {
    zu_debug('import->put', $debug-10);
    $result = '';

    $json_array = json_decode($this->json_str, true);
    foreach ($json_array AS $key => $json_obj) {
      if ($key == 'version') {
        if (prg_version_is_newer($json_obj)) {
          $result .= 'Import file has been created with version '.$json_obj.', which is newer than this, which is '.PRG_VERSION.' ';
        }
      } elseif ($key == 'pod') {
      } elseif ($key == 'time') {
      } elseif ($key == 'user') {
        // TODO does it need to be checked
        //if ($usr->name <> $json_obj) {
        //}
      } elseif ($key == 'selection') {
      } elseif ($key == 'words') {
        foreach ($json_obj AS $word) {
          $wrd = New word;
          $wrd->usr = $this->usr;
          $import_result = $wrd->import_obj($word, $debug-1);
          if ($import_result == '') { $this->words_done++; } else { $this->words_failed++; }
          $result .= $import_result;
        }
      } elseif ($key == 'triples') {
        foreach ($json_obj AS $triple) {
          $wrd_lnk = New word_link;
          $wrd_lnk->usr = $this->usr;
          $import_result = $wrd_lnk->import_obj($triple, $debug-1);
          if ($import_result == '') { $this->triples_done++; } else { $this->triples_failed++; }
          $result .= $import_result;
        }
      } elseif ($key == 'formulas') {
        foreach ($json_obj AS $formula) {
          $frm = New formula;
          $frm->usr = $this->usr;
          $import_result = $frm->import_obj($formula, $debug-1);
          if ($import_result == '') { $this->formulas_done++; } else { $this->formulas_failed++; }
          $result .= $import_result;
        }
      } elseif ($key == 'sources') {
        foreach ($json_obj AS $value) {
          $src = New source;
          $src->usr = $this->usr;
          $import_result = $src->import_obj($value, $debug-1);
          if ($import_result == '') { $this->sources_done++; } else { $this->sources_failed++; }
          $result .= $import_result;
        }
      } elseif ($key == 'values') {
        foreach ($json_obj AS $value) {
          $val = New value;
          $val->usr = $this->usr;
          $import_result = $val->import_obj($value, $debug-1);
          if ($import_result == '') { $this->values_done++; } else { $this->values_failed++; }
          $result .= $import_result;
        }
      } elseif ($key == 'views') {
        foreach ($json_obj AS $view) {
          $view_obj = New view;
          $view_obj->usr = $this->usr;
          $import_result = $view_obj->import_obj($view, $debug-1);
          if ($import_result == '') { $this->views_done++; } else { $this->views_failed++; }
          $result .= $import_result;
        }
      } else {
        $result .= 'Unknow element '.$key.' ';
      }
    }
    
    return $result;    
  }
  

  
}

?>
