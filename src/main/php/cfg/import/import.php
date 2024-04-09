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

namespace cfg\import;

include_once EXPORT_PATH . 'export.php';
include_once MODEL_FORMULA_PATH . 'formula.php';
include_once MODEL_FORMULA_PATH . 'formula_list.php';
include_once MODEL_RESULT_PATH . 'result.php';
include_once MODEL_RESULT_PATH . 'result_list.php';
include_once MODEL_SYSTEM_PATH . 'ip_range.php';
include_once MODEL_SYSTEM_PATH . 'session.php';
include_once MODEL_HELPER_PATH . 'library.php';
include_once MODEL_REF_PATH . 'ref.php';
include_once MODEL_REF_PATH . 'source.php';
include_once MODEL_WORD_PATH . 'word.php';
include_once MODEL_WORD_PATH . 'triple.php';
include_once MODEL_VALUE_PATH . 'value.php';
include_once MODEL_VALUE_PATH . 'value_list.php';
include_once MODEL_VERB_PATH . 'verb.php';
include_once MODEL_VIEW_PATH . 'view.php';
include_once MODEL_VIEW_PATH . 'view_list.php';

use cfg\component\component;
use cfg\export\export;
use cfg\formula;
use cfg\formula_list;
use cfg\ip_range;
use cfg\library;
use cfg\phrase_list;
use cfg\ref;
use cfg\result\result;
use cfg\result\result_list;
use cfg\source;
use cfg\triple;
use cfg\user;
use cfg\user_message;
use cfg\value\value;
use cfg\value\value_list;
use cfg\verb;
use cfg\view;
use cfg\view_list;
use cfg\word;
use cfg\word_list;

class import
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
    public ?int $refs_done = 0;
    public ?int $refs_failed = 0;
    public ?int $values_done = 0;
    public ?int $values_failed = 0;
    public ?int $list_values_done = 0;
    public ?int $list_values_failed = 0;
    public ?int $views_done = 0;
    public ?int $views_failed = 0;
    public ?int $components_done = 0;
    public ?int $components_failed = 0;
    public ?int $calc_validations_done = 0;
    public ?int $calc_validations_failed = 0;
    public ?int $view_validations_done = 0;
    public ?int $view_validations_failed = 0;
    public ?int $system_done = 0;
    public ?int $system_failed = 0;

    public float $last_display_time;

    function display_progress(int $pos, int $total): void
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
     *
     * @param string $json_str the zukunft.com JSON message to import as a string
     * @param user $usr_trigger the user who has triggered the import
     * @return user_message the result of the import
     */
    function put(string $json_str, user $usr_trigger): user_message
    {
        global $usr;
        $lib = new library();

        log_debug();
        $result = new user_message();
        $this->last_display_time = microtime(true);

        $json_array = json_decode($json_str, true);
        if ($json_array == null) {
            if ($json_str != '') {
                $result->add_message('JSON decode failed of ' . $json_str);
            } else {
                $result->add_warning('JSON string is empty');
            }
        } else {
            $total = $lib->count_recursive($json_array, 1);

            // get the user first to allow user specific validation
            $usr_import = null;
            foreach ($json_array as $key => $json_obj) {
                if ($usr_import == null) {
                    if ($key == export::USERS) {
                        $import_result = new user_message();
                        foreach ($json_obj as $user) {
                            // TODO check if the constructor is always used
                            $usr_import = new user;
                            $import_result = $usr_import->import_obj($user, $usr_trigger->profile_id);
                            if ($import_result->is_ok()) {
                                $this->users_done++;
                            } else {
                                $this->users_failed++;
                            }
                        }
                        $result->add($import_result);
                    }
                }
            }
            // if no user is defined in the json to import use the active user
            if ($usr_import == null) {
                $usr_import = $usr;
            }

            // remember the result and view that should be validated after the import
            $res_to_validate = new result_list($usr_import);
            $frm_to_calc = new formula_list($usr_import);
            $dsp_to_validate = new view_list($usr_import);
            $pos = 0;
            foreach ($json_array as $key => $json_obj) {
                $this->display_progress($pos, $total);
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
                } elseif ($key == export::DESCRIPTION) {
                    // TODO remember the description for the log
                } elseif ($key == export::USER) {
                    // TODO set the user that has created the export
                } elseif ($key == export::USERS) {
                    // TODO import the users (but only by a user with the privileges)
                } elseif ($key == export::VERBS) {
                    $import_result = new user_message();
                    foreach ($json_obj as $verb) {
                        $vrb = new verb;
                        $vrb->set_user($usr_trigger);
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
                        $wrd = new word($usr_trigger);
                        $import_result = $wrd->import_obj($word);
                        if ($import_result->is_ok()) {
                            $this->words_done++;
                        } else {
                            $this->words_failed++;
                        }
                        $result->add($import_result);
                    }
                } elseif ($key == export::WORD_LIST) {
                    // a list of just the word names without further parameter
                    // phrase list because a word might also be a triple
                    $phr_lst = new phrase_list($usr_trigger);
                    $import_result = $phr_lst->import_names($json_obj);
                    if ($import_result->is_ok()) {
                        $this->words_done++;
                    } else {
                        $this->words_failed++;
                    }
                    $result->add($import_result);
                } elseif ($key == export::TRIPLES) {
                    foreach ($json_obj as $triple) {
                        $wrd_lnk = new triple($usr_trigger);
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
                        $frm = new formula($usr_trigger);
                        $import_result = $frm->import_obj($formula);
                        if ($import_result->is_ok()) {
                            $this->formulas_done++;
                            $frm_to_calc->add($frm);
                        } else {
                            $this->formulas_failed++;
                        }
                        $result->add($import_result);
                    }
                } elseif ($key == export::SOURCES) {
                    foreach ($json_obj as $value) {
                        $src = new source($usr_trigger);
                        $import_result = $src->import_obj($value);
                        if ($import_result->is_ok()) {
                            $this->sources_done++;
                        } else {
                            $this->sources_failed++;
                        }
                        $result->add($import_result);
                    }
                } elseif ($key == export::REFS) {
                    foreach ($json_obj as $value) {
                        $ref = new ref($usr_trigger);
                        $import_result = $ref->import_obj($value);
                        if ($import_result->is_ok()) {
                            $this->refs_done++;
                        } else {
                            $this->refs_failed++;
                        }
                        $result->add($import_result);
                    }
                } elseif ($key == export::PHRASE_VALUES) {
                    foreach ($json_obj as $val_key => $number) {
                        $val = new value($usr_trigger);
                        $import_result = $val->import_phrase_value($val_key, $number);
                        if ($import_result->is_ok()) {
                            $this->values_done++;
                        } else {
                            $this->values_failed++;
                        }
                        $result->add($import_result);
                    }
                } elseif ($key == export::VALUES) {
                    foreach ($json_obj as $value) {
                        $val = new value($usr_trigger);
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
                        $val = new value_list($usr_trigger);
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
                        $view_obj = new view($usr_trigger);
                        $import_result = $view_obj->import_obj($view);
                        if ($import_result->is_ok()) {
                            $this->views_done++;
                        } else {
                            $this->views_failed++;
                        }
                        $result->add($import_result);
                    }
                } elseif ($key == export::COMPONENTS) {
                    foreach ($json_obj as $cmp) {
                        $cmp_obj = new component($usr_trigger);
                        $import_result = $cmp_obj->import_obj($cmp);
                        if ($import_result->is_ok()) {
                            $this->components_done++;
                        } else {
                            $this->components_failed++;
                        }
                        $result->add($import_result);
                    }
                } elseif ($key == export::CALC_VALIDATION) {
                    // TODO add a unit test
                    foreach ($json_obj as $value) {
                        $res = new result($usr_trigger);
                        $import_result = $res->import_obj($value);
                        if ($import_result->is_ok()) {
                            $this->calc_validations_done++;
                            $res_to_validate->add($res);
                        } else {
                            $this->calc_validations_failed++;
                        }
                        $result->add($import_result);
                    }
                } elseif ($key == export::VIEW_VALIDATION) {
                    // TODO switch to view result
                    // TODO add a unit test
                    foreach ($json_obj as $value) {
                        $dsp = new view($usr_trigger);
                        $import_result = $dsp->import_obj($value);
                        if ($import_result->is_ok()) {
                            $this->view_validations_done++;
                            $dsp_to_validate->add($dsp);
                        } else {
                            $this->view_validations_failed++;
                        }
                        $result->add($import_result);
                    }
                } elseif ($key == export::IP_BLACKLIST) {
                    foreach ($json_obj as $ip_range) {
                        $ip_obj = new ip_range;
                        $ip_obj->set_user($usr_trigger);
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

            // validate the import
            if (!$frm_to_calc->is_empty()) {
                foreach ($frm_to_calc->lst() as $frm) {
                    //$frm->calc();
                    if ($frm != null) {
                        log_debug($frm->dsp_id());
                    }
                }
            }
            if (!$res_to_validate->is_empty()) {
                foreach ($res_to_validate as $res) {
                    log_debug($res->dsp_id());
                }
            }
        }

        return $result;
    }

}
