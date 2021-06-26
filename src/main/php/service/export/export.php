<?php

/*

  export.php - create an object to export data - the object can be converted to a json, yaml or XML message
  ----------
  
  offer the user the long or the short version
  the short version is using one time ids for words, triples and groups
  
  add the instance id, user id and time stamp to the export file
    
  TODO
  - offer to export the change log
  - export only the user view
  - ... or include the standard value
  - ... or include all user values in the export object
    
  
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

class export {

  // parameters to filter the export
  public $usr     = NULL; // the user who wants to im- or export
  public $phr_lst = NULL; // to export all values related to this phrase
  
  // export zukunft.com data as object for creating e.g. a json message
  function get () {

    global $db_con;

    log_debug('export->get');
    $export_obj = Null;
    
    if (count($this->phr_lst) <= 0) {
      log_warning("No words to filter the export are defined.","export->get");
    } else {

      // 1. create the header
      $export_obj->version   = PRG_VERSION;
      $export_obj->pod       = cfg_get(CFG_SITE_NAME, $this->usr, $db_con);
      $export_obj->time      = date("Y-m-d H:i:s");
      $export_obj->user      = $this->usr->name;
      $export_obj->selection = $this->phr_lst->names(); // must be set by before the call TODO not nice
      
      // 1.1. collect all personal values - value that cannot be seen by other user

      // 2. collect values linked to the user selected words
      //    e.g. if carrots are selected get the climate gas emissions per weight percent
      log_debug('export->get values');
      $val_lst = $this->phr_lst->val_lst();
      
      // 3. get all words and triples needed for the values that should be exported
      //    e.g. carrots, climate gas emission (CO2, methane), weight, percent
      log_debug('export->get words and triples');
      $this->phr_lst->merge($val_lst->phr_lst_all());
      $wrd_lst = $this->phr_lst->wrd_lst_all();
      
      // 4. export all words that have a special type or any other non default setting
      log_debug('export->get typed words');
      $exp_words = array();
      foreach ($wrd_lst->lst AS $wrd) {
        if (get_class($wrd) == 'word' or get_class($wrd) == 'word_dsp') {
          if ($wrd->has_cfg()) {
            $exp_wrd = $wrd->export_obj();
            if (isset($exp_wrd)) {
              $exp_words[] = $exp_wrd;
            }
          }  
        } else {
          log_err('The function wrd_lst_all returns '.$wrd->dsp_id().', which is '.get_class($wrd).', but not a word.','export->get');
        }
      }
      log_debug('export->get typed words done');
      if (count($exp_words) > 0) {
        $export_obj->words = $exp_words;
      }  
      
      // 5. export all word relations
      log_debug('export->get triples');
      $lnk_lst = $this->phr_lst->wrd_lnk_lst();
      $exp_triples = array();
      foreach ($lnk_lst->lst AS $lnk) {
        $exp_lnk = $lnk->export_obj();
        if (isset($exp_lnk)) {
          $exp_triples[] = $exp_lnk;
        }
      }
      if (count($exp_triples) > 0) {
        $export_obj->triples = $exp_triples;
      }  

      // 6. export all used formula relations to reproduce the results
      log_debug('export->get formulas');
      $frm_lst = $this->phr_lst->frm_lst();
      $exp_formulas = array();
      foreach ($frm_lst->lst AS $frm) {
        $exp_frm = $frm->export_obj();
        if (isset($exp_frm)) {
          $exp_formulas[] = $exp_frm;
        }  
      }
      $export_obj->formulas = $exp_formulas;

      // 7. add all sources to the export object
      log_debug('export->get sources');
      $source_lst = $val_lst->source_lst();
      log_debug('export->got '.count($source_lst).' sources');
      $exp_sources = array();
      foreach ($source_lst AS $src) {
        if (isset($src)) {
          $exp_src = $src->export_obj();
          if (isset($exp_src)) {
            $exp_sources[] = $exp_src;
          }
        }
      }
      if (count($exp_sources) > 0) {
        $export_obj->sources = $exp_sources;
      }  

      // 8. add all values to the export object
      log_debug('export->get values');
      $exp_values = array();
      foreach ($val_lst->lst AS $val) {
        if (isset($val)) {
          $exp_val = $val->export_obj();
          if (isset($exp_val)) {
            $exp_values[] = $exp_val;
          }
        }
      }
      $export_obj->values = $exp_values;
      
      // 9. add all views and view components to the export object
      // TODO create an array add function that does not add duplicates
      log_debug('export->get views');
      //$wrd_lst = $phr_lst_used->wrd_lst_all();
      $view_lst = $wrd_lst->view_lst();
      $exp_view_lst = array();
      foreach ($view_lst AS $view) {
        $exp_view_lst[] = $view->export_obj();
      }
      $export_obj->views = $exp_view_lst;
      
      // 10. just for validating the import: add all formula results to the export
      log_debug('export->get formula results');
      
      // 11. just for validating the import: add "screenshots" of the views to the export
      log_debug('export->get screenshots');
    }  
    
    log_debug('export->get ... done');
    return $export_obj;    
  }
  

  
}

?>
