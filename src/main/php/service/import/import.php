<?php

/*

  import.php - import data - take a object from a json, yaml or XML message and trigger the object saves
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
  along with zukunft.com. If not, see <http://www.gnu.org/licenses/agpl.html>.
  
  To contact the authors write to:
  Timon Zielonka <timon@zukunft.com>
  
  Copyright (c) 1995-2022 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

use export\exp_obj;

class file_import
{

    // parameters to filter the import
    public ?user $usr = null; // the user who wants to import data
    public ?string $json_str = null; // a string with the json data to import
    public ?int $users_done = 0;
    public ?int $users_failed = 0;
    public ?int $verbs_done = 0;
    public ?int $verbs_failed = 0;
    public ?int $words_done = 0;
    public ?int $words_failed = 0;
    public ?int $triples_done = 0;
    public ?int $triples_failed = 0;
    public ?int $formulas_done = 0;
    public ?int $formulas_failed = 0;
    public ?int $sources_done = 0;
    public ?int $sources_failed = 0;
    public ?int $values_done = 0;
    public ?int $values_failed = 0;
    public ?int $list_values_done = 0;
    public ?int $list_values_failed = 0;
    public ?int $views_done = 0;
    public ?int $views_failed = 0;
    public ?int $calc_validations_done = 0;
    public ?int $calc_validations_failed = 0;
    public ?int $view_validations_done = 0;
    public ?int $view_validations_failed = 0;
    public ?int $system_done = 0;
    public ?int $system_failed = 0;

    public float $last_display_time;

    function display_progress(int $pos, int $total)
    {
        $check_time = microtime(true);
        $time_since_last_display = $check_time - $this->last_display_time;
        if ($time_since_last_display > UI_MIN_RESPONSE_TIME) {
            $progress = round($pos / $total * 100) . '%';
            echo '<br><br>import' . $progress . ' done<br>';
            log_debug('import->put ' . $progress);
            $this->last_display_time = microtime(true);
        }
    }

    /**
     * drop a zukunft.com json object to the database
     */
    function put(): user_message
    {
        log_debug();
        $result = new user_message();
        $this->last_display_time = microtime(true);

        $json_array = json_decode($this->json_str, true);
        if ($json_array != null) {
            $total = count_recursive($json_array, 1);
            $pos = 0;
            $this->display_progress($pos, $total);
            foreach ($json_array as $key => $json_obj) {
                $pos++;
                if ($key == export::VERSION) {
                    if (prg_version_is_newer($json_obj)) {
                        $result->add_message('Import file has been created with version ' . $json_obj . ', which is newer than this, which is ' . PRG_VERSION);
                    }
                } elseif ($key == export::POD) {
                    // TODO set the source pod
                } elseif ($key == export::TIME) {
                    // TODO set the time of the export
                } elseif ($key == export::SELECTION) {
                    // TODO set the selection as context
                } elseif ($key == export::USER) {
                    // TODO set the user that has created the export
                } elseif ($key == export::USERS) {
                    $import_result = new user_message();
                    foreach ($json_obj as $user) {
                        // TODO check if the constructor is always used
                        $usr = new user;
                        $import_result = $usr->import_obj($user, $this->usr->profile_id);
                        if ($import_result->is_ok()) {
                            $this->users_done++;
                        } else {
                            $this->users_failed++;
                        }
                    }
                    $result->add($import_result);
                } elseif ($key == export::VERBS) {
                    $import_result = new user_message();
                    foreach ($json_obj as $verb) {
                        $vrb = new verb;
                        $vrb->usr = $this->usr;
                        $import_result = $vrb->import_obj($verb);
                        if ($import_result->is_ok()) {
                            $this->verbs_done++;
                        } else {
                            $this->verbs_failed++;
                        }
                    }
                    $result->add($import_result);
                } elseif ($key == export::WORDS) {
                    foreach ($json_obj as $word) {
                        $wrd = new word($this->usr);
                        $import_result = $wrd->import_obj($word);
                        if ($import_result->is_ok()) {
                            $this->words_done++;
                        } else {
                            $this->words_failed++;
                        }
                        $result->add($import_result);
                    }
                } elseif ($key == export::TRIPLES) {
                    foreach ($json_obj as $triple) {
                        $wrd_lnk = new word_link($this->usr);
                        $import_result = $wrd_lnk->import_obj($triple);
                        if ($import_result->is_ok()) {
                            $this->triples_done++;
                        } else {
                            $this->triples_failed++;
                        }
                        $result->add($import_result);
                    }
                } elseif ($key == export::FORMULAS) {
                    foreach ($json_obj as $formula) {
                        $frm = new formula($this->usr);
                        $import_result = $frm->import_obj($formula);
                        if ($import_result->is_ok()) {
                            $this->formulas_done++;
                        } else {
                            $this->formulas_failed++;
                        }
                        $result->add($import_result);
                    }
                } elseif ($key == export::SOURCES) {
                    foreach ($json_obj as $value) {
                        $src = new source($this->usr);
                        $import_result = $src->import_obj($value);
                        if ($import_result->is_ok()) {
                            $this->sources_done++;
                        } else {
                            $this->sources_failed++;
                        }
                        $result->add($import_result);
                    }
                } elseif ($key == export::VALUES) {
                    foreach ($json_obj as $value) {
                        $val = new value($this->usr);
                        $import_result = $val->import_obj($value);
                        if ($import_result->is_ok()) {
                            $this->values_done++;
                        } else {
                            $this->values_failed++;
                        }
                        $result->add($import_result);
                    }
                } elseif ($key == export::VALUE_LIST) {
                    // TODO add a unit test
                    foreach ($json_obj as $value) {
                        $val = new value_list($this->usr);
                        $import_result = $val->import_obj($value);
                        if ($import_result->is_ok()) {
                            $this->list_values_done++;
                        } else {
                            $this->list_values_failed++;
                        }
                        $result->add($import_result);
                    }
                } elseif ($key == export::VIEWS) {
                    foreach ($json_obj as $view) {
                        $view_obj = new view($this->usr);
                        $import_result = $view_obj->import_obj($view);
                        if ($import_result->is_ok()) {
                            $this->views_done++;
                        } else {
                            $this->views_failed++;
                        }
                        $result->add($import_result);
                    }
                } elseif ($key == export::CALC_VALIDATION) {
                    // TODO add a unit test
                    foreach ($json_obj as $value) {
                        $fv = new formula_value($this->usr);
                        $import_result = $fv->import_obj($value);
                        if ($import_result->is_ok()) {
                            $this->calc_validations_done++;
                        } else {
                            $this->calc_validations_failed++;
                        }
                        $result->add($import_result);
                    }
                } elseif ($key == export::VIEW_VALIDATION) {
                    // TODO switch to view result
                    // TODO add a unit test
                    foreach ($json_obj as $value) {
                        $fv = new view($this->usr);
                        $import_result = $fv->import_obj($value);
                        if ($import_result->is_ok()) {
                            $this->view_validations_done++;
                        } else {
                            $this->view_validations_failed++;
                        }
                        $result->add($import_result);
                    }
                } elseif ($key == export::IP_BLACKLIST) {
                    foreach ($json_obj as $ip_range) {
                        $ip_obj = new ip_range;
                        $ip_obj->usr = $this->usr;
                        $import_result = $ip_obj->import_obj($ip_range);
                        if ($import_result->is_ok()) {
                            $this->system_done++;
                        } else {
                            $this->system_failed++;
                        }
                        $result->add($import_result);
                    }
                } else {
                    $result->add_message('Unknown element ' . $key);
                }
            }
        }

        return $result;
    }

}
