<?php

/*

  export.php - create an object to export data - the object can be converted to a json, yaml or XML message
  ----------
  
  offer the user the long or the short version
  the short version is using one time id for words, triples and groups
  
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
  along with zukunft.com. If not, see <http://www.gnu.org/licenses/agpl.html>.
  
  To contact the authors write to:
  Timon Zielonka <timon@zukunft.com>
  
  Copyright (c) 1995-2022 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

class export
{

    // parameters to filter the export
    public ?user $usr = null;            // the user who wants to im- or export
    public ?phrase_list $phr_lst = null; // to export all values related to this phrase

    // export zukunft.com data as object for creating e.g. a json message
    function get(): object
    {

        global $db_con;

        log_debug('export->get');
        $export_obj = (object)[];

        if ($this->phr_lst != null) {
            if (count($this->phr_lst->lst) <= 0) {
                log_warning("No words to filter the export are defined.", "export->get");
            } else {

                // 1. create the header
                $export_obj->version = PRG_VERSION;
                $export_obj->pod = cfg_get(CFG_SITE_NAME, $db_con);
                $export_obj->time = date("Y-m-d H:i:s");
                $export_obj->user = $this->usr->name;
                $phr_lst_dsp = $this->phr_lst->dsp_obj();
                $export_obj->selection = $phr_lst_dsp->names(); // must be set by before the call TODO not nice better use the $phr_lst->object_exp_lst()

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

                // 4. export all words that have a special type or any other non default setting (standard words are created automatically on import with just the name)
                log_debug('export->get typed words');
                if ($wrd_lst != null) {
                    $exp_words = $wrd_lst->export_obj();
                    if (count($exp_words) > 0) {
                        $export_obj->words = $exp_words;
                    }
                }

                // 5. export all word relations
                log_debug('export->get triples');
                $lnk_lst = $this->phr_lst->trp_lst();
                $exp_triples = array();
                foreach ($lnk_lst->lst as $lnk) {
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
                if ($frm_lst->lst != null) {
                    foreach ($frm_lst->lst as $frm) {
                        $exp_frm = $frm->export_obj();
                        if (isset($exp_frm)) {
                            $exp_formulas[] = $exp_frm;
                        }
                    }
                }
                $export_obj->formulas = $exp_formulas;

                // 7. add all sources to the export object
                log_debug('export->get sources');
                $source_lst = $val_lst->source_lst();
                log_debug('export->got ' . dsp_count($source_lst) . ' sources');
                $exp_sources = array();
                if ($source_lst != null) {
                    foreach ($source_lst as $src) {
                        if (isset($src)) {
                            $exp_src = $src->export_obj();
                            if (isset($exp_src)) {
                                $exp_sources[] = $exp_src;
                            }
                        }
                    }
                }
                if (count($exp_sources) > 0) {
                    $export_obj->sources = $exp_sources;
                }

                // 8. add all values to the export object
                log_debug('export->get values');
                $exp_values = array();
                foreach ($val_lst->lst as $val) {
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
                foreach ($view_lst as $view) {
                    $exp_view_lst[] = $view->export_obj();
                }
                $export_obj->views = $exp_view_lst;

                // 10. just for validating the import: add all formula results to the export
                log_debug('export->get formula results');
                $exp_formula_values = array();
                $frm_lst = $this->phr_lst->frm_lst();
                if ($frm_lst->lst != null) {
                    foreach ($frm_lst->lst as $frm) {
                        $fv_lst = $frm->get_fv_lst();
                        if ($fv_lst->lst != null) {
                            foreach ($fv_lst->lst as $fv) {
                                $exp_fv = $fv->export_obj();
                                if (isset($exp_fv)) {
                                    $exp_formula_values[] = $exp_fv;
                                }
                            }
                        }
                    }
                }
                $export_obj->formula_values = $exp_formula_values;

                // 11. just for validating the import: add "screenshots" of the views to the export
                log_debug('export->get screenshots');
            }
        }

        log_debug('export->get ... done');
        return $export_obj;
    }


}
