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

include_once SHARED_PATH . 'library.php';
include_once EXPORT_PATH . 'export.php';
include_once MODEL_FORMULA_PATH . 'formula.php';
include_once MODEL_FORMULA_PATH . 'formula_list.php';
include_once MODEL_RESULT_PATH . 'result.php';
include_once MODEL_RESULT_PATH . 'result_list.php';
include_once MODEL_SYSTEM_PATH . 'ip_range.php';
include_once MODEL_SYSTEM_PATH . 'session.php';
include_once MODEL_REF_PATH . 'ref.php';
include_once MODEL_REF_PATH . 'source.php';
include_once MODEL_WORD_PATH . 'word.php';
include_once MODEL_WORD_PATH . 'triple.php';
include_once MODEL_VALUE_PATH . 'value.php';
include_once MODEL_VALUE_PATH . 'value_list.php';
include_once MODEL_VERB_PATH . 'verb.php';
include_once MODEL_VIEW_PATH . 'view.php';
include_once MODEL_VIEW_PATH . 'view_list.php';
include_once MODEL_HELPER_PATH . 'data_object.php';

use cfg\component\component;
use cfg\data_object;
use cfg\export\export;
use cfg\formula;
use cfg\formula_list;
use cfg\ip_range;
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
use shared\library;

class import
{

    // import assumption
    const IMPORT_VALIDAT_PCT_TIME = 10;

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

    function display_progress(int $pos, int $total, string $topic = ''): void
    {
        $lib = new library();
        $check_time = microtime(true);
        $time_since_last_display = $check_time - $this->last_display_time;
        if ($time_since_last_display > UI_MIN_RESPONSE_TIME) {
            $progress = round($pos / $total * 100) . '%';
            //echo '<br><br>import' . $progress . ' done<br>';
            echo $progress . ' ' . $lib->class_to_name($topic) . "\n";
            log_debug('import->put ' . $progress);
            $this->last_display_time = microtime(true);
        }
    }

    /**
     * drop a zukunft.com yaml object to the database
     *
     * @param string $yaml_str the zukunft.com YAML message to import as a string
     * @param user $usr_trigger the user who has triggered the import
     * @return user_message the result of the import
     */
    function put_yaml(string $yaml_str, user $usr_trigger): user_message
    {
        $usr_msg = new user_message();
        $yaml_array = yaml_parse($yaml_str);
        if ($yaml_array == null) {
            if ($yaml_str != '') {
                $usr_msg->add_message('YAML decode failed of ' . $yaml_str);
            } else {
                $usr_msg->add_warning('YAML string is empty');
            }
        } else {
            $dto = $this->yaml_data_object($yaml_array, $usr_trigger);
            $usr_msg = $dto->save();
        }
        return $usr_msg;
    }

    /**
     * drop a zukunft.com yaml object to the database
     *
     * @param string $json_str the zukunft.com JSON message to import as a string
     * @param user $usr_trigger the user who has triggered the import
     * @return user_message the result of the import
     */
    function put_json(string $json_str, user $usr_trigger): user_message
    {
        $usr_msg = new user_message();
        $json_array = json_decode($json_str, true);
        if ($json_array == null) {
            if ($json_str != '') {
                $usr_msg->add_message('JSON decode failed of ' . $json_str);
            } else {
                $usr_msg->add_warning('JSON string is empty');
            }
        } else {
            $usr_msg = $this->put($json_array, $usr_trigger);
        }
        return $usr_msg;
    }

    /**
     * drop a zukunft.com json object to the database
     *
     * @param array $json_array the zukunft.com JSON message to import as an array
     * @param user $usr_trigger the user who has triggered the import
     * @return user_message the result of the import
     */
    private function put(array $json_array, user $usr_trigger): user_message
    {
        global $usr;
        $lib = new library();

        log_debug();
        $usr_msg = new user_message();
        $this->last_display_time = microtime(true);

        $total = $lib->count_recursive($json_array, 3);
        $val_steps = round(self::IMPORT_VALIDAT_PCT_TIME * $total);
        $total = $total + $val_steps;

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
                    $usr_msg->add($import_result);
                }
            }
        }
        // if no user is defined in the json to import use the active user
        if ($usr_import == null) {
            $usr_import = $usr;
        }

        // remember the usr_msg and view that should be validated after the import
        $res_to_validate = new result_list($usr_import);
        $frm_to_calc = new formula_list($usr_import);
        $dsp_to_validate = new view_list($usr_import);
        $pos = 0;
        foreach ($json_array as $key => $json_obj) {
            $this->display_progress($pos, $total);
            $pos++;
            if ($key == export::VERSION) {
                if (prg_version_is_newer($json_obj)) {
                    $usr_msg->add_message('Import file has been created with version ' . $json_obj . ', which is newer than this, which is ' . PRG_VERSION);
                }
            } elseif ($key == export::POD) {
                // TODO set the source pod
                log_warning('import of pod details not yet implimented');
            } elseif ($key == export::TIME) {
                // TODO set the time of the export
                log_warning('import of time not yet implimented');
            } elseif ($key == export::SELECTION) {
                // TODO set the selection as context
                log_warning('import of selection not yet implimented');
            } elseif ($key == export::DESCRIPTION) {
                // TODO remember the description for the log
                log_warning('import of description not yet implimented');
            } elseif ($key == export::USER) {
                // TODO set the user that has created the export
                log_warning('import of a single user not yet implimented');
            } elseif ($key == export::USERS) {
                // TODO import the users (but only by a user with the privileges)
                log_warning('import of users not yet implimented');
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
                    $this->display_progress($pos, $total, verb::class);
                    $pos++;
                }
                $usr_msg->add($import_result);
            } elseif ($key == export::WORDS) {
                foreach ($json_obj as $word) {
                    $wrd = new word($usr_trigger);
                    $import_result = $wrd->import_obj($word);
                    if ($import_result->is_ok()) {
                        $this->words_done++;
                    } else {
                        $this->words_failed++;
                    }
                    $usr_msg->add($import_result);
                    $this->display_progress($pos, $total, word::class);
                    $pos++;
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
                $usr_msg->add($import_result);
                $this->display_progress($pos, $total, phrase_list::class);
                $pos++;
            } elseif ($key == export::TRIPLES) {
                foreach ($json_obj as $triple) {
                    $wrd_lnk = new triple($usr_trigger);
                    $import_result = $wrd_lnk->import_obj($triple);
                    if ($import_result->is_ok()) {
                        $this->triples_done++;
                    } else {
                        $this->triples_failed++;
                    }
                    $usr_msg->add($import_result);
                    $this->display_progress($pos, $total, triple::class);
                    $pos++;
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
                    $usr_msg->add($import_result);
                    $this->display_progress($pos, $total, formula::class);
                    $pos++;
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
                    $usr_msg->add($import_result);
                    $this->display_progress($pos, $total, source::class);
                    $pos++;
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
                    $usr_msg->add($import_result);
                    $this->display_progress($pos, $total, ref::class);
                    $pos++;
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
                    $usr_msg->add($import_result);
                    $this->display_progress($pos, $total, value::class);
                    $pos++;
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
                    $usr_msg->add($import_result);
                    $this->display_progress($pos, $total, value::class);
                    $pos++;
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
                    $usr_msg->add($import_result);
                    $this->display_progress($pos, $total, value_list::class);
                    $pos++;
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
                    $usr_msg->add($import_result);
                    $this->display_progress($pos, $total, view::class);
                    $pos++;
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
                    $usr_msg->add($import_result);
                    $this->display_progress($pos, $total, component::class);
                    $pos++;
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
                    $usr_msg->add($import_result);
                    $this->display_progress($pos, $total, result::class);
                    $pos++;
                }
            } elseif ($key == export::VIEW_VALIDATION) {
                // TODO switch to view usr_msg
                // TODO add a unit test
                foreach ($json_obj as $value) {
                    $msk = new view($usr_trigger);
                    $import_result = $msk->import_obj($value);
                    if ($import_result->is_ok()) {
                        $this->view_validations_done++;
                        $dsp_to_validate->add($msk);
                    } else {
                        $this->view_validations_failed++;
                    }
                    $usr_msg->add($import_result);
                    $this->display_progress($pos, $total, view::class);
                    $pos++;
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
                    $usr_msg->add($import_result);
                    $this->display_progress($pos, $total, ip_range::class);
                    $pos++;
                }
            } else {
                $usr_msg->add_message('Unknown element ' . $key);
            }
        }

        // show 90% before validation starts
        $this->display_progress($total - $val_steps, $total);

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

        // show 100% before validation starts
        $this->display_progress($total, $total);

        return $usr_msg;
    }

    /**
     * count the number of words or triples within a yaml zukunft.com import array
     *
     * @param array $yaml_array
     * @return int
     */
    function yaml_phrase_count(array $yaml_array): int
    {
        return count($this->yaml_phrase_names($yaml_array));
    }

    /**
     * get a list with the phrase names from a yaml zukunft.com import array
     * @param array $yaml_array
     * @return array list of the phrase names used in yaml
     */
    function yaml_phrase_names(array $yaml_array): array
    {
        $lib = new library();
        $names = array_unique($lib->array_keys_r($yaml_array));
        return array_diff($names, [word::TOOLTIP_COMMENT]);
    }

    /**
     * get a list with the word names from a yaml zukunft.com import array
     * @param array $yaml_array
     * @return array list of the word names used in yaml
     */
    function yaml_word_names(array $yaml_array): array
    {
        $names = $this->yaml_phrase_names($yaml_array);
        $wrd_names = [];
        foreach ($names as $name) {
            $name = trim($name);
            if (!str_contains($name, ' ')) {
                $wrd_names[] = $name;
            }
        }
        return $wrd_names;
    }

    /**
     * get a list with the all word names (splited triples) from a yaml zukunft.com import array
     * @param array $yaml_array
     * @return array list of the word names used in yaml
     */
    function yaml_word_names_all(array $yaml_array): array
    {
        $names = $this->yaml_phrase_names($yaml_array);
        $wrd_names = [];
        foreach ($names as $name) {
            $name = trim($name);
            if (str_contains($name, ' ')) {
                $wrd_names[] = $name;
            }
        }
        return $wrd_names;
    }

    /**
     * get a list with the triple names from a yaml zukunft.com import array
     * @param array $yaml_array
     * @return array list of the triple names used in yaml
     */
    function yaml_triple_names(array $yaml_array): array
    {
        $names = $this->yaml_phrase_names($yaml_array);
        $wrd_names = [];
        foreach ($names as $name) {
            $name = trim($name);
            if (str_contains($name, ' ')) {
                $wrd_names[] = $name;
            }
        }
        return $wrd_names;
    }

    /**
     * create a data object based on a yaml zukunft.com import array
     *
     * @param array $yml_arr the array of a zukunft.com yaml
     * @param user $usr_trigger the user who has started the import
     * @return data_object filled based on the yaml array
     */
    function yaml_data_object(array $yml_arr, user $usr_trigger): data_object
    {
        $dto = new data_object($usr_trigger);
        $wrd = null;
        $trp = null;
        $val = null;
        $phr_lst = new phrase_list($usr_trigger);
        $dto = $this->yaml_data_object_loop($dto, $phr_lst, $yml_arr, $wrd, $trp, $val, $usr_trigger);
        // add the last word, triple or value to the lists
        if ($wrd != null) {
            $dto->add_word($wrd);
            $phr_lst->add($wrd->phrase());
        }
        if ($trp != null) {
            $dto->add_triple($trp);
            $phr_lst->add($trp->phrase());
        }
        if ($val != null) {
            // TODO add any last phrase to the value phrase list
            $val = new value($usr_trigger);
            $val->set_phrase_lst($phr_lst);
            $dto->add_value($val);
        }
        return $dto;
    }

    function yaml_data_object_loop(
        data_object $dto,
        phrase_list $phr_lst,
        array       $yml_arr,
        ?word       $wrd,
        ?triple     $trp,
        ?value      $val,
        user        $usr_trigger
    ): data_object
    {
        foreach ($yml_arr as $key => $value) {
            $dto = $this->yaml_data_object_map($key, $value, $dto, $phr_lst, $wrd, $trp, $val, $usr_trigger);
        }
        return $dto;
    }

    /**
     * @param string $key
     * @param string|array $value
     * @param data_object $dto
     * @param phrase_list $phr_lst
     * @param word|null $wrd the last created word
     * @param triple|null $trp the last created triple
     * @param value|null $val the last created value
     * @param user $usr_trigger
     * @return data_object
     */
    function yaml_data_object_map(
        string       $key,
        string|array $value,
        data_object  $dto,
        phrase_list  $phr_lst,
        ?word        $wrd,
        ?triple      $trp,
        ?value       $val,
        user         $usr_trigger
    ): data_object
    {
        // add the comment to the previous set word or triple and add it to the lists
        if ($key == word::TOOLTIP_COMMENT) {
            if ($wrd == null and $trp == null) {
                $dto->add_message('yaml is not expected to start with a tooltip-comment');
            } else {
                $wrd?->set_description($value);
                $trp?->set_description($value);
            }
        } else {
            // add the previous set word or triple to the lists
            if ($wrd != null) {
                $dto->add_word($wrd);
                $phr_lst->add_by_name($wrd->phrase());
            }
            if ($trp != null) {
                $dto->add_triple($trp);
                $phr_lst->add_by_name($trp->phrase());
            }
            if ($val != null) {
                $dto->add_value($val);
            }
            // reset the word, triple and value
            $wrd = null;
            $trp = null;
            $val = null;
            // if the name has a space create the separate words and use the triple
            if ($key != word::SYS_CONF_VALUE) {
                if (str_contains($key, ' ')) {
                    $trp = $this->yaml_data_object_map_triple($key, $dto, $usr_trigger);
                } else {
                    // set the name for a normal word
                    $wrd = new word($usr_trigger);
                    $wrd->set_name($key);
                }
            }
            if (is_array($value)) {
                $sub_phr_lst = clone $phr_lst;
                $dto = $this->yaml_data_object_loop($dto, $sub_phr_lst, $value, $wrd, $trp, $val, $usr_trigger);
            } else {
                // add the final phrase
                // if the name has a space create the separate words and use the triple
                if (str_contains($key, ' ')) {
                    $trp = $this->yaml_data_object_map_triple($key, $dto, $usr_trigger);
                    $dto->add_triple($trp);
                    $phr_lst->add($trp->phrase());
                } else {
                    // set the name for a normal word
                    $wrd = new word($usr_trigger);
                    $wrd->set_name($key);
                    $dto->add_word($wrd);
                    $phr_lst->add($wrd->phrase());
                }
                // add value
                $val = new value($usr_trigger);
                $val->set_phrase_lst($phr_lst);
                if (is_numeric($value)) {
                    $val->set_number($value);
                } else {
                    log_warning('text values cannot yet be used');
                }
                $dto->add_value($val);
            }
        }
        return $dto;
    }

    /**
     * @param string $key
     * @param data_object $dto
     * @param user $usr_trigger
     * @return triple
     */
    function yaml_data_object_map_triple(
        string      $key,
        data_object $dto,
        user        $usr_trigger
    ): triple
    {
        $names = explode(' ', $key);
        // create the single words and add them to the data_object
        $from = null;
        $to = null;
        foreach ($names as $name) {
            $wrd = new word($usr_trigger);
            $wrd->set_name($name);
            $dto->add_word($wrd);
            if ($from == null) {
                $from = $wrd;
            } else {
                if ($to == null) {
                    $to = $wrd;
                } else {
                    log_err('unexpect number of word for a triple');
                }
            }
        }
        // reset the word because the words are already added to the list and are included in the triple
        $wrd = null;
        // create the triple based on the words and set the name of the triple
        $trp = new triple($usr_trigger);
        if ($from != null and $to != null) {
            $trp->set_from($from->phrase());
            $trp->set_to($to->phrase());
            $trp->set_name($key);
        } else {
            log_err('unexpect number of word for a triple');
        }

        return $trp;
    }

    function status_text(): user_message
    {
        $usr_msg = new user_message();
        $msg_txt = $this->status_text_entry('words', $this->words_done, $this->words_failed);
        $msg_txt .= $this->status_text_entry('verbs', $this->verbs_done, $this->verbs_failed);
        $msg_txt .= $this->status_text_entry('triples', $this->triples_done, $this->triples_failed);
        $msg_txt .= $this->status_text_entry('formulas', $this->formulas_done, $this->formulas_failed);
        $msg_txt .= $this->status_text_entry('values', $this->values_done, $this->values_failed);
        $msg_txt .= $this->status_text_entry('simple values', $this->list_values_done, $this->list_values_failed);
        $msg_txt .= $this->status_text_entry('sources', $this->sources_done, $this->sources_failed);
        $msg_txt .= $this->status_text_entry('references', $this->refs_done, $this->refs_failed);
        $msg_txt .= $this->status_text_entry('views', $this->views_done, $this->views_failed);
        $msg_txt .= $this->status_text_entry('components', $this->components_done, $this->components_failed);
        $msg_txt .= $this->status_text_entry('results validated', $this->calc_validations_done, $this->calc_validations_failed);
        $msg_txt .= $this->status_text_entry('views validated', $this->view_validations_done, $this->view_validations_failed);
        $usr_msg->add_message($msg_txt);
        return $usr_msg;
    }

    private function status_text_entry(string $name, int $done, int $failed): string
    {
        $msg_txt = '';
        if ($done > 0 or $failed > 0) {
            $msg_txt .= $done;
            if ($failed > 0) {
                $msg_txt .= 'done (' . $failed . ' failed)';

            }
            $msg_txt .= ' ' . $name;
        }
        if ($msg_txt != '') {
            $msg_txt .= ', ';
        }
        return $msg_txt;
    }


}
