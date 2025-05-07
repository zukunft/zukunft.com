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
include_once MODEL_COMPONENT_PATH . 'component.php';
include_once MODEL_HELPER_PATH . 'data_object.php';
include_once EXPORT_PATH . 'export.php';
include_once MODEL_FORMULA_PATH . 'formula.php';
include_once MODEL_FORMULA_PATH . 'formula_list.php';
include_once MODEL_RESULT_PATH . 'result.php';
include_once MODEL_RESULT_PATH . 'result_list.php';
include_once MODEL_SYSTEM_PATH . 'ip_range.php';
include_once MODEL_SYSTEM_PATH . 'session.php';
include_once MODEL_PHRASE_PATH . 'phrase_list.php';
include_once MODEL_REF_PATH . 'ref.php';
include_once MODEL_REF_PATH . 'source.php';
include_once MODEL_RESULT_PATH . 'result.php';
include_once MODEL_RESULT_PATH . 'result_list.php';
include_once MODEL_WORD_PATH . 'word.php';
include_once MODEL_WORD_PATH . 'triple.php';
include_once MODEL_USER_PATH . 'user.php';
include_once MODEL_USER_PATH . 'user_message.php';
include_once MODEL_VALUE_PATH . 'value.php';
include_once MODEL_VALUE_PATH . 'value_base.php';
include_once MODEL_VALUE_PATH . 'value_text.php';
include_once MODEL_VALUE_PATH . 'value_list.php';
include_once MODEL_VERB_PATH . 'verb.php';
include_once MODEL_VIEW_PATH . 'view.php';
include_once MODEL_VIEW_PATH . 'view_list.php';
include_once MODEL_HELPER_PATH . 'data_object.php';
include_once SHARED_CONST_PATH . 'triples.php';
include_once SHARED_CONST_PATH . 'words.php';
include_once SHARED_ENUM_PATH . 'messages.php';
include_once SHARED_PATH . 'json_fields.php';
include_once SHARED_PATH . 'library.php';

use cfg\component\component;
use cfg\formula\formula;
use cfg\formula\formula_list;
use cfg\helper\data_object;
use cfg\phrase\phrase_list;
use cfg\ref\ref;
use cfg\ref\source;
use cfg\result\result;
use cfg\result\result_list;
use cfg\system\ip_range;
use cfg\user\user;
use cfg\user\user_message;
use cfg\value\value;
use cfg\value\value_base;
use cfg\value\value_list;
use cfg\value\value_text;
use cfg\verb\verb;
use cfg\view\view;
use cfg\view\view_list;
use cfg\word\triple;
use cfg\word\word;
use shared\const\triples;
use shared\const\words;
use shared\enum\messages as msg_id;
use shared\json_fields;
use shared\library;

class import
{

    // import assumption
    const IMPORT_VALIDATE_PCT_TIME = 10;

    // the user who wants to import data
    public ?user $usr = null;

    // description of the import job
    // the message id of the job name that is translated on the fly inti the language of the frontend
    // so if the user changes the frontend language during a long import
    // the import status is also shown in the changed language
    public ?msg_id $msg_id = null;
    // the file, url or stream name of the data to import
    public string $file_name = '';
    // the name of the main import step executed at the moment
    public msg_id|string $main_step = '';
    // the name of the import step executed at the moment
    public msg_id|string $step = '';
    // the object class processed at the moment
    public string $class = '';
    // the number of objects processed in this step until now
    public string $processed = '';

    // execution times

    // the time when the import has been started
    public float $start_time;
    // the start time of the main step e.g. object creation
    public float $start_time_main_step = 0;
    // the start time of the sub step e.g. saving words in the database
    public float $start_time_step = 0;

    // the total expected time to finish this import based on the file size
    public float $est_time_total;
    // the expected time to read the file based on the file size
    public float $est_time_read;
    // the expected time for decoding based on the file size
    public float $est_time_decode;
    // the expected time for object creation based on the file size
    public float $est_time_create;
    // the expected time for database save based on the file size
    public float $est_time_store;
    // the expected time for the main step executing now
    public float $est_time_main_step;
    // the expected time for the step executing now
    public float $est_time_step = 0;

    // sum of the estimated time already done of main steps
    public float $done_time_main_step = 0;
    // sum of the estimated time already done of sub steps
    public float $done_time_step = 0;

    // the number of objects / bytes within a step
    public float $micro_steps = 0;

    // the adjusted expected time to finish this import
    public float $time_exp_act;


    // timestamp of the last message shown to the user
    public float $last_display_time;

    // statistic data
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

    function __construct(string $file_name = '')
    {
        $this->start_time = microtime(true);
        $this->last_display_time = microtime(true);
        $this->msg_id = msg_id::IMPORT_JSON;
        if ($file_name != '') {
            $this->file_name = $file_name;
        }
        // set dummy value assuming that an import may last 3 seconds if no other estimates can be done
        $this->est_time_total = 3;
        $this->est_time_read = 0.05;
        $this->est_time_decode = 0.05;
        $this->est_time_create = 0.5;
        $this->est_time_store = 2.4;
        $this->time_exp_act = 3;
    }

    /**
     * show the progress of an import process
     * @param int $processed the number of processed objects until now
     * @param float $per_sec the expected number of words that can be analysed per second
     * @param string $sample a sample text what is imported at the moment
     * @param bool $show if true the message should be preferred shown to the user
     * @param bool $stat if true the statistic of the import should be shown
     * @return void
     */
    function display_progress(
        int    $processed = 0,
        float  $per_sec = 0,
        string $sample = '',
        bool   $show = false,
        bool   $stat = false
    ): void
    {
        global $mtr;
        global $cfg;

        $lib = new library();

        // fix the time
        $check_time = microtime(true);

        // get the updated configuration settings
        $ui_response_time = $cfg->get_by([triples::RESPONSE_TIME, words::MIN, words::FRONTEND, words::BEHAVIOUR]);

        // calc the time statistics
        $time_since_last_display = $check_time - $this->last_display_time;
        $total_time = 0;
        $processed_per_sec = 0;
        if ($stat or $show or ($time_since_last_display > $ui_response_time)) {
            $total_time = $check_time - $this->start_time;
            $step_time = $check_time - $this->start_time_step;
            if ($step_time > 0) {
                $processed_per_sec = $processed / $step_time;
            }

            // update the remaining time to avoid reporting too low eta
            $time_exp_adj = $this->calc_total_time($check_time, $processed);

            $this->time_exp_act = $time_exp_adj;
        }


        // create or update the text message parts
        $name = '';
        $progress = '';
        $times = '';
        $part = '';
        $speed = '';
        if ($stat or $show or ($time_since_last_display > $ui_response_time)) {
            $name = $mtr->txt($this->msg_id) . ' ' . basename($this->file_name);
            if (!is_string($this->step)) {
                $step = ' ' . $this->step->text();
            } else {
                $step = ' ' . $this->step;
            }
            if ($this->class == import_file::FILE) {
                $class = $this->class;
                $part = ' ' . $class . $step;
            } else {
                $class = $lib->class_to_table($this->class);
                $part = ' ' . $class . $step . ': ' . $processed;
            }
            $times = ' ' . round($total_time, 3) . 's / ' . round($this->time_exp_act, 3) . 's';
            $final_time = ' ' . round($total_time, 3) . 's ' . $this->time_exp_act;
            if ($total_time > 0.001) {
                $progress = ' ' . round($total_time / $this->time_exp_act * 100, 1) . '%';
            }
            if ($this->class == import_file::FILE or $this->step == msg_id::DECODED) {
                $speed = ' (' . round($processed_per_sec / 1000000, 1) . ' MB per sec';
            } else {
                $speed = ' (' . round($processed_per_sec, 1) . ' ' . $class . ' per sec';
            }
            if ($per_sec > 0) {
                $speed .= ' vs. ' . $per_sec . ')';
            } else {
                $speed .= ')';
            }
        }
        if ($sample != '') {
            $sample = ' ' . $mtr->txt(msg_id::EXAMPLE_SHORT) . ' ' . $sample;
        }

        if ($stat) {
            echo $name . $final_time . $step . $speed . "\n";
        } elseif ($show or ($time_since_last_display > $ui_response_time)) {
            //echo '<br><br>import' . $progress . ' done<br>';
            echo $name . $progress . $times . $part . $speed . $sample . "\n";
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
            $dto = $this->get_data_object_yaml($yaml_array, $usr_trigger);
            $usr_msg = $dto->save($this);
            $usr_msg->set_checksum($dto->value_list()->count());
        }
        return $usr_msg;
    }

    /**
     * drop a zukunft.com json message object to the database
     * and check the consistency upfront
     *
     * @param string $json_str the zukunft.com JSON message to import as a string
     * @param user $usr_trigger the user who has triggered the import
     * @return user_message the result of the import
     */
    function put_json(
        string $json_str,
        user   $usr_trigger
    ): user_message
    {
        global $cfg;

        // get the relevant config values
        $decode_per_sec = $cfg->get_by([
            words::DECODE,
            triples::BYTES_PER_SECOND,
            triples::EXPECTED_TIME, words::IMPORT], 1);
        $store_per_sec = $cfg->get_by([
            triples::OBJECT_STORING,
            triples::BYTES_PER_SECOND,
            triples::EXPECTED_TIME, words::IMPORT], 1);

        $usr_msg = new user_message();

        $size = strlen($json_str);

        // read the import file
        $this->step_main_start(msg_id::READ, $this->est_time_decode);
        $this->step_start(msg_id::DECODED, words::BYTE);
        $json_array = json_decode($json_str, true);
        $this->step_end($size, $decode_per_sec);
        $this->step_main_end();

        if ($json_array == null) {
            $usr_msg->add_id_with_vars(msg_id::JSON_DECODE,
                [msg_id::VAR_JSON_TEXT => $json_str]);
        } else {


            // analyse the import file
            $this->step_main_start(msg_id::COUNT, $this->est_time_create);
            $dto = $this->get_data_object($json_array, $usr_trigger, $usr_msg, $size);
            $this->step_main_end();

            // write to the database
            $this->step_main_start(msg_id::SAVE, $this->est_time_store);
            $usr_msg->add($dto->save($this));
            $this->step_main_end();

        }

        // show the import result
        $this->end($size, $store_per_sec, $usr_msg);

        return $usr_msg;
    }

    /**
     * drop a zukunft.com json message direct to the database
     *
     * @param string $json_str the zukunft.com JSON message to import as a string
     * @param user $usr_trigger the user who has triggered the import
     * @return user_message the result of the import
     */
    function put_json_direct(
        string $json_str,
        user   $usr_trigger
    ): user_message
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
    private function put(
        array $json_array,
        user  $usr_trigger
    ): user_message
    {
        global $usr;
        $lib = new library();

        log_debug();
        $usr_msg = new user_message();
        $this->last_display_time = microtime(true);

        // get the user first to allow user specific validation
        $usr_import = null;
        foreach ($json_array as $key => $json_obj) {
            if ($usr_import == null) {
                if ($key == json_fields::USERS) {
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
            $this->display_progress();
            $pos++;
            if ($key == json_fields::VERSION) {
                if (prg_version_is_newer($json_obj)) {
                    $usr_msg->add_message('Import file has been created with version ' . $json_obj . ', which is newer than this, which is ' . PRG_VERSION);
                }
            } elseif ($key == json_fields::POD) {
                // TODO set the source pod
                log_warning('import of pod details not yet implemented');
            } elseif ($key == json_fields::TIME) {
                // TODO set the time of the export
                log_warning('import of time not yet implemented');
            } elseif ($key == json_fields::SELECTION) {
                // TODO set the selection as context
                log_warning('import of selection not yet implemented');
            } elseif ($key == json_fields::DESCRIPTION) {
                // TODO remember the description for the log
                log_warning('import of description not yet implemented');
            } elseif ($key == json_fields::USER_NAME) {
                // TODO set the user that has created the export
                log_warning('import of a single user not yet implemented');
            } elseif ($key == json_fields::USERS) {
                // TODO import the users (but only by a user with the privileges)
                log_warning('import of users not yet implemented');
            } elseif ($key == json_fields::LIST_VERBS) {
                $this->step_start(msg_id::SAVE_LIST, verb::class);
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
                    $this->display_progress($this->verbs_done);
                    $pos++;
                }
                $usr_msg->add($import_result);
                $this->step_end($this->verbs_done);
            } elseif ($key == json_fields::WORDS) {
                $this->step_start(msg_id::SAVE_SINGLE, word::class);
                foreach ($json_obj as $word) {
                    $wrd = new word($usr_trigger);
                    $import_result = $wrd->import_obj($word);
                    if ($import_result->is_ok()) {
                        $this->words_done++;
                    } else {
                        $this->words_failed++;
                    }
                    $usr_msg->add($import_result);
                    $this->display_progress($this->words_done);
                    $pos++;
                }
                $this->step_end($this->words_done);
            } elseif ($key == json_fields::WORD_LIST) {
                $this->step_start(msg_id::SAVE_LIST, word::class);
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
                $this->display_progress($this->words_done);
                $pos++;
            } elseif ($key == json_fields::TRIPLES) {
                $this->step_start(msg_id::SAVE_SINGLE, triple::class);
                foreach ($json_obj as $triple) {
                    $wrd_lnk = new triple($usr_trigger);
                    $import_result = $wrd_lnk->import_obj($triple);
                    if ($import_result->is_ok()) {
                        $this->triples_done++;
                    } else {
                        $this->triples_failed++;
                    }
                    $usr_msg->add($import_result);
                    $this->display_progress($this->triples_done);
                    $pos++;
                }
            } elseif ($key == json_fields::FORMULAS) {
                $this->step_start(msg_id::SAVE_SINGLE, formula::class);
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
                    $this->display_progress($this->formulas_done);
                    $pos++;
                }
            } elseif ($key == json_fields::SOURCES) {
                $this->step_start(msg_id::SAVE_SINGLE, source::class);
                foreach ($json_obj as $value) {
                    $src = new source($usr_trigger);
                    $import_result = $src->import_obj($value);
                    if ($import_result->is_ok()) {
                        $this->sources_done++;
                    } else {
                        $this->sources_failed++;
                    }
                    $usr_msg->add($import_result);
                    $this->display_progress($this->sources_done);
                    $pos++;
                }
            } elseif ($key == json_fields::REFS) {
                $this->step_start(msg_id::SAVE_SINGLE, ref::class);
                foreach ($json_obj as $value) {
                    $ref = new ref($usr_trigger);
                    $import_result = $ref->import_obj($value);
                    if ($import_result->is_ok()) {
                        $this->refs_done++;
                    } else {
                        $this->refs_failed++;
                    }
                    $usr_msg->add($import_result);
                    $this->display_progress($this->refs_done);
                    $pos++;
                }
            } elseif ($key == json_fields::PHRASE_VALUES) {
                $this->step_start(msg_id::SAVE_SINGLE, value::class);
                foreach ($json_obj as $val_key => $number) {
                    $val = new value($usr_trigger);
                    $import_result = $val->import_phrase_value($val_key, $number);
                    if ($import_result->is_ok()) {
                        $this->values_done++;
                    } else {
                        $this->values_failed++;
                    }
                    $usr_msg->add($import_result);
                    $this->display_progress($this->values_done);
                    $pos++;
                }
            } elseif ($key == json_fields::VALUES) {
                $this->step_start(msg_id::SAVE_SINGLE, value::class);
                foreach ($json_obj as $value) {
                    $val = new value($usr_trigger);
                    $import_result = $val->import_obj($value);
                    if ($import_result->is_ok()) {
                        $this->values_done++;
                    } else {
                        $this->values_failed++;
                    }
                    $usr_msg->add($import_result);
                    $this->display_progress($this->values_done);
                    $pos++;
                }
            } elseif ($key == json_fields::VALUE_LIST) {
                $this->step_start(msg_id::SAVE_LIST, value::class);
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
                    $this->display_progress($this->list_values_done);
                    $pos++;
                }
            } elseif ($key == json_fields::VIEWS) {
                $this->step_start(msg_id::SAVE_SINGLE, view::class);
                foreach ($json_obj as $view) {
                    $view_obj = new view($usr_trigger);
                    $import_result = $view_obj->import_obj($view);
                    if ($import_result->is_ok()) {
                        $this->views_done++;
                    } else {
                        $this->views_failed++;
                    }
                    $usr_msg->add($import_result);
                    $this->display_progress($this->views_done);
                    $pos++;
                }
            } elseif ($key == json_fields::COMPONENTS) {
                $this->step_start(msg_id::SAVE_SINGLE, component::class);
                foreach ($json_obj as $cmp) {
                    $cmp_obj = new component($usr_trigger);
                    $import_result = $cmp_obj->import_obj($cmp);
                    if ($import_result->is_ok()) {
                        $this->components_done++;
                    } else {
                        $this->components_failed++;
                    }
                    $usr_msg->add($import_result);
                    $this->display_progress($this->components_done);
                    $pos++;
                }
            } elseif ($key == json_fields::CALC_VALIDATION) {
                $this->step_start(msg_id::SAVE_SINGLE, result::class);
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
                    $this->display_progress($this->calc_validations_done);
                    $pos++;
                }
            } elseif ($key == json_fields::VIEW_VALIDATION) {
                $this->step_start(msg_id::SAVE_SINGLE, view::class);
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
                    $this->display_progress($this->view_validations_done);
                    $pos++;
                }
            } elseif ($key == json_fields::IP_BLACKLIST) {
                $this->step_start(msg_id::SAVE_SINGLE, ip_range::class);
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
                    $this->display_progress($this->system_done);
                    $pos++;
                }
            } else {
                $usr_msg->add_message('Unknown element ' . $key);
            }
        }

        // show 90% before validation starts
        $this->step_start(msg_id::VALIDATE);

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
        $this->display_progress();

        return $usr_msg;
    }

    /**
     * create a data object based on a json zukunft.com import array
     *
     * @param array $json_array the array of a zukunft.com yaml
     * @param user $usr_trigger the user who has started the import
     * @param int $size the number of bytes that needs to be processed
     * @return data_object filled based on the yaml array
     */
    function get_data_object(
        array        $json_array,
        user         $usr_trigger,
        user_message $usr_msg = new user_message(),
        int          $size = 0
    ): data_object
    {
        global $cfg;

        // get the relevant config values
        $wrd_per_sec = $cfg->get_by([words::WORDS, words::CREATE, triples::OBJECTS_PER_SECOND, triples::EXPECTED_TIME, words::IMPORT], 1);
        $trp_per_sec = $cfg->get_by([words::TRIPLES, words::CREATE, triples::OBJECTS_PER_SECOND, triples::EXPECTED_TIME, words::IMPORT], 1);
        $src_per_sec = $cfg->get_by([words::SOURCES, words::CREATE, triples::OBJECTS_PER_SECOND, triples::EXPECTED_TIME, words::IMPORT], 1);
        $val_per_sec = $cfg->get_by([words::VALUES, words::CREATE, triples::OBJECTS_PER_SECOND, triples::EXPECTED_TIME, words::IMPORT], 1);
        $frm_per_sec = $cfg->get_by([words::FORMULAS, words::CREATE, triples::OBJECTS_PER_SECOND, triples::EXPECTED_TIME, words::IMPORT], 1);
        $msk_per_sec = $cfg->get_by([words::VIEWS, words::CREATE, triples::OBJECTS_PER_SECOND, triples::EXPECTED_TIME, words::IMPORT], 1);
        $cmp_per_sec = $cfg->get_by([words::COMPONENTS, words::CREATE, triples::OBJECTS_PER_SECOND, triples::EXPECTED_TIME, words::IMPORT], 1);

        // estimate the time for each object type
        // where 5 is the number of data objects that are filled with this function
        $steps = 5;
        $step_time = $this->est_time_create / $steps;

        // create the data_object to fill
        $dto = new data_object($usr_trigger);

        $usr_msg->add($this->message_check($json_array));
        if ($usr_msg->is_ok()) {
            // TODO add json_fields::IP_BLACKLIST
            // TODO add json_fields::USERS
            // TODO add json_fields::LIST_VERBS
            if (key_exists(json_fields::WORDS, $json_array)) {
                $wrd_array = $json_array[json_fields::WORDS];
                $this->step_start(msg_id::COUNT, word::class, count($wrd_array), $step_time);
                $usr_msg->add($this->dto_get_words($wrd_array, $usr_trigger, $dto, $wrd_per_sec));
                $this->step_end($dto->word_list()->count(), $wrd_per_sec);
            }
            // TODO add json_fields::WORD_LIST
            if (key_exists(json_fields::TRIPLES, $json_array)) {
                $trp_array = $json_array[json_fields::TRIPLES];
                $this->step_start(msg_id::COUNT, triple::class, count($trp_array), $step_time);
                $usr_msg->add($this->dto_get_triples($trp_array, $usr_trigger, $dto, $trp_per_sec));
                $this->step_end($dto->triple_list()->count(), $trp_per_sec);
            }
            if (key_exists(json_fields::SOURCES, $json_array)) {
                $src_array = $json_array[json_fields::SOURCES];
                $this->step_start(msg_id::COUNT, source::class, count($src_array), $step_time);
                $usr_msg->add($this->dto_get_sources($src_array, $usr_trigger, $dto, $src_per_sec));
                $this->step_end($dto->source_list()->count(), $src_per_sec);
            }
            // TODO add json_fields::REFS
            // TODO add json_fields::PHRASE_VALUES
            if (key_exists(json_fields::VALUES, $json_array)) {
                $val_array = $json_array[json_fields::VALUES];
                $this->step_start(msg_id::COUNT, value::class, count($val_array), $step_time);
                $usr_msg->add($this->dto_get_values($val_array, $usr_trigger, $dto, $val_per_sec));
                $this->step_end($dto->value_list()->count(), $val_per_sec);
            }
            // TODO add json_fields::VALUE_LIST
            if (key_exists(json_fields::FORMULAS, $json_array)) {
                $frm_array = $json_array[json_fields::FORMULAS];
                $this->step_start(msg_id::COUNT, formula::class, count($frm_array), $step_time);
                $usr_msg->add($this->dto_get_formulas($frm_array, $usr_trigger, $dto, $frm_per_sec));
                $this->step_end($dto->formula_list()->count(), $frm_per_sec);
            }
            // TODO add json_fields::RESULTS
            // TODO add json_fields::CALC_VALIDATION
            // TODO add json_fields::COMPONENTS
            if (key_exists(json_fields::COMPONENTS, $json_array)) {
                $cmp_array = $json_array[json_fields::COMPONENTS];
                $this->step_start(msg_id::COUNT, component::class, count($cmp_array), $step_time);
                $usr_msg->add($this->dto_get_components($cmp_array, $usr_trigger, $dto, $cmp_per_sec));
                $this->step_end($dto->component_list()->count(), $cmp_per_sec);
            }
            // TODO add json_fields::VIEWS
            if (key_exists(json_fields::VIEWS, $json_array)) {
                $msk_array = $json_array[json_fields::VIEWS];
                $this->step_start(msg_id::COUNT, view::class, count($msk_array), $step_time);
                $usr_msg->add($this->dto_get_views($msk_array, $usr_trigger, $dto, $msk_per_sec));
                $this->step_end($dto->view_list()->count(), $msk_per_sec);
            }
            // TODO add json_fields::VIEW_VALIDATION
        }
        return $dto;
    }

    /**
     * start a group of steps and remember the estimated time
     * @param string|msg_id $step
     * @param float $exp_time the estimated time for this step base on the bytes to import
     * @return void
     */
    function step_main_start(
        string|msg_id $step = '',
        float         $exp_time = 0.0
    ): void
    {
        $this->main_step = $step;
        $this->start_time_main_step = microtime(true);
        $this->est_time_main_step = $exp_time;
        $this->done_time_step = 0;
    }

    /**
     * remember how much is done by adding the estimated time done
     */
    function step_main_end(): void
    {
        $this->done_time_main_step = $this->done_time_main_step + $this->est_time_main_step;
        $this->est_time_main_step = 0;
        $this->done_time_step = 0;
    }

    function step_start(
        string|msg_id $step = '',
        string        $class = '',
        float         $total = 0,
        float         $exp_time = 0.0
    ): void
    {
        $this->step = $step;
        $this->class = $class;
        $this->start_time_step = microtime(true);
        $this->est_time_step = $exp_time;
        $this->micro_steps = $total;
        if ($class == import_file::FILE) {
            global $mtr;
            $name = $mtr->txt($this->msg_id) . ' ' . basename($this->file_name);
            $speed = '(' . round($total / 1000) . ' kBytes)';
            echo $name . ' ' . $speed . "\n";
        }
    }

    /**
     * @param int $nbr the number of precessed objects e.g. count(word)
     * @param float $est_per_sec the expected number of objects that can be processed per second
     */
    function step_end(
        int   $nbr = 0,
        float $est_per_sec = 0.0
    ): void
    {
        $end_time = microtime(true);
        $this->done_time_step = $this->done_time_step + $this->est_time_step;
        $this->est_time_step = 0;

        if ($nbr > 0) {
            if ($this->step == msg_id::DECODED) {
                $used_est_per_sec = $est_per_sec * 1000000;
            } elseif ($this->class == import_file::FILE) {
                $this->step = msg_id::LOADED;
                $used_est_per_sec = $est_per_sec * 1000000;
            } else {
                $this->step = msg_id::TOTAL;
                $used_est_per_sec = $est_per_sec;
            }
            if ($used_est_per_sec != 0) {
                $this->time_exp_act = $this->calc_total_time($end_time, $nbr);
            }
            $this->display_progress($nbr, $est_per_sec, '', true);
        }
    }

    /**
     * calc the adjusted expected total execution time
     *
     * @param float $check_time the timestamp of the update
     * @return float the eta (estimate time of arrival) based total execution time
     */
    private function calc_total_time(float $check_time, int $processed = 0): float
    {
        // highlight the original expected time based on the file size
        $original_time_expected = $this->est_time_total;

        // get the real execution time until now
        $time_already = $check_time - $this->start_time;

        // calc the percentage of the step done
        if ($processed > 0 and $this->micro_steps > 0) {
            $step_done_in_pct = $processed / $this->micro_steps;
        } else {
            $step_done_in_pct = 0;
        }

        // calc the time done until now based on the estimates
        $est_step_time_done = $this->est_time_step * $step_done_in_pct;

        // get the micro percent done
        // based on the main steps already done ($this->time_done_main_step)
        // plus the steps already done ($this->time_done_step)
        // plus the micro step time done ($this->time_done_step)
        $est_time_done = $this->done_time_main_step + $this->done_time_step + $est_step_time_done;

        // calc the percentage done base on the original estimates
        $pct_done = $est_time_done / $original_time_expected;

        // calc the remaining time in percent
        $pct_remaining = 1 - $pct_done;

        // estimated time until now
        $est_time_now = $original_time_expected * $pct_done;

        // real time until now
        $real_time_now = $time_already * $pct_done;

        // calc the factor how much longer (or short) it takes relative to the original estimate
        $factor = 1;
        if ($est_time_now > 0) {
            $factor = $real_time_now / $est_time_now;
        }

        // calc the remaining time based on the original estimate
        $est_time_remaining = $original_time_expected * $pct_remaining;

        // reduce the adjustment factor based on the percent executed until now
        // to avoid high adjustments on the beginning of the process
        $factor_used = $pct_remaining + ($factor * $pct_done);

        // TODO remove
        /*
        echo 'done in pct: ' . round($pct_done * 100, 1)
            . ' (' . round($this->done_time_main_step, 4) .'+' . round($this->done_time_step, 4) .'+' . round($est_step_time_done, 4) .') '
            . ' factor used ' . round($factor_used, 2) . ' time_exp_remaining ' . round($est_time_remaining, 2) . "\n";
        */

        // calc the adjusted expected total execution time
        return $time_already + $est_time_remaining * $factor_used;

    }

    /**
     * calc the import times and show the result to the user
     * @param int $nbr the number of precessed objects e.g. count(word)
     * @param float $est_per_sec the expected number of objects that can be processed per second
     */
    function end(
        int          $nbr = 0,
        float        $est_per_sec = 0.0,
        user_message $usr_msg = new user_message()
    ): void
    {
        global $mtr;

        $end_time = microtime(true);

        if ($usr_msg->is_ok()) {
            $step = $mtr->txt(msg_id::DONE);
        } else {
            $step = $usr_msg->all_message_text();
        }

        $lib = new library();
        if ($nbr > 0) {
            $this->display_progress($nbr, $est_per_sec);
            $expected_time = $nbr / $est_per_sec;
            $real_time = $end_time - $this->start_time;
            $name = $mtr->txt($this->msg_id) . ' ' . basename($this->file_name);
            $part = $lib->class_to_table($this->class) . ' ' . $step . ': ' . $nbr;
            echo $name . ' ' . $part . "\n";
        }
    }

    /**
     * check the import message header
     * @param array $json_array the complete json import message as an array
     * @return user_message if something is not fine the message that should be shown to the user
     */
    private function message_check(array $json_array): user_message
    {
        $usr_msg = new user_message();
        if (key_exists(json_fields::VERSION, $json_array)) {
            if (prg_version_is_newer($json_array[json_fields::VERSION])) {
                $usr_msg->add_message('Import file has been created with version ' . $json_array[json_fields::VERSION] . ', which is newer than this, which is ' . PRG_VERSION);
            }
        }
        // TODO add json_fields::POD
        // TODO add json_fields::TIME
        // TODO add json_fields::SELECTION
        // TODO add json_fields::DESCRIPTION
        // TODO add json_fields::USER_NAME
        return $usr_msg;
    }

    /**
     * add the words from the json array to the data object
     * @param array $json_array the word part of the import json
     * @param user $usr_trigger the user who has started the import
     * @param data_object $dto the data object that should be filled
     * @param float $per_sec the expected number of words that can be analysed per second
     * @return user_message the messages to the user if something has not been fine
     */
    private function dto_get_words(
        array       $json_array,
        user        $usr_trigger,
        data_object $dto,
        float       $per_sec = 0
    ): user_message
    {
        $usr_msg = new user_message();

        $i = 0;
        foreach ($json_array as $wrd_json) {
            $wrd = new word($usr_trigger);
            $usr_msg->add($wrd->import_mapper($wrd_json, $dto));
            $dto->add_word($wrd);
            $i++;
            $this->display_progress($i, $per_sec, $wrd->dsp_id());
        }
        return $usr_msg;
    }

    /**
     * add the triples from the json array to the data object
     * @param array $json_array the triple part of the import json
     * @param user $usr_trigger the user who has started the import
     * @param data_object $dto the data object that should be filled
     * @param float $per_sec the expected number of triples that can be analysed per second
     * @return user_message the messages to the user if something has not been fine
     */
    private function dto_get_triples(
        array       $json_array,
        user        $usr_trigger,
        data_object $dto,
        float       $per_sec = 0
    ): user_message
    {
        $usr_msg = new user_message();

        $i = 0;
        foreach ($json_array as $trp_json) {
            $trp = new triple($usr_trigger);
            $usr_msg->add($trp->import_mapper($trp_json, $dto));
            $dto->add_triple($trp);
            $i++;
            $this->display_progress($i, $per_sec, $trp->dsp_id());
        }
        return $usr_msg;
    }

    /**
     * add the sources from the json array to the data object
     * @param array $json_array the source part of the import json
     * @param user $usr_trigger the user who has started the import
     * @param data_object $dto the data object that should be filled
     * @param float $per_sec the expected number of sources that can be analysed per second
     * @return user_message the messages to the user if something has not been fine
     */
    private function dto_get_sources(
        array       $json_array,
        user        $usr_trigger,
        data_object $dto,
        float       $per_sec = 0
    ): user_message
    {
        $usr_msg = new user_message();

        $i = 0;
        foreach ($json_array as $src_json) {
            $src = new source($usr_trigger);
            $usr_msg->add($src->import_mapper($src_json, $dto));
            $dto->add_source($src);
            $i++;
            $this->display_progress($i, $per_sec, $src->dsp_id());
        }
        return $usr_msg;
    }

    /**
     * add the values from the json array to the data object
     * @param array $json_array the value part of the import json
     * @param user $usr_trigger the user who has started the import
     * @param data_object $dto the data object that should be filled
     * @param float $per_sec the expected number of values that can be analysed per second
     * @return user_message the messages to the user if something has not been fine
     */
    private function dto_get_values(
        array       $json_array,
        user        $usr_trigger,
        data_object $dto,
        float       $per_sec = 0
    ): user_message
    {
        $usr_msg = new user_message();

        $i = 0;
        foreach ($json_array as $val_json) {
            $val = new value($usr_trigger);
            $usr_msg->add($val->import_mapper($val_json, $dto));
            $dto->add_value($val);
            $i++;
            $this->display_progress($i, $per_sec, $val->dsp_id());
        }
        return $usr_msg;
    }

    /**
     * add the formulas from the json array to the data object
     * @param array $json_array the formula part of the import json
     * @param user $usr_trigger the user who has started the import
     * @param data_object $dto the data object that should be filled
     * @param float $per_sec the expected number of formulas that can be analysed per second
     * @return user_message the messages to the user if something has not been fine
     */
    private function dto_get_formulas(
        array       $json_array,
        user        $usr_trigger,
        data_object $dto,
        float       $per_sec = 0
    ): user_message
    {
        $usr_msg = new user_message();

        $i = 0;
        foreach ($json_array as $frm_json) {
            $frm = new formula($usr_trigger);
            $usr_msg->add($frm->import_mapper($frm_json, $dto));
            $dto->add_formula($frm);
            $i++;
            $this->display_progress($i, $per_sec, $frm->dsp_id());
        }
        return $usr_msg;
    }

    /**
     * add the views from the json array to the data object
     * @param array $json_array the view part of the import json
     * @param user $usr_trigger the user who has started the import
     * @param data_object $dto the data object that should be filled
     * @param float $per_sec the expected number of formulas that can be analysed per second
     * @return user_message the messages to the user if something has not been fine
     */
    private function dto_get_views(
        array       $json_array,
        user        $usr_trigger,
        data_object $dto,
        float       $per_sec = 0
    ): user_message
    {
        $usr_msg = new user_message();

        $i = 0;
        foreach ($json_array as $msk_json) {
            $msk = new view($usr_trigger);
            $usr_msg->add($msk->import_mapper($msk_json, $dto));
            $dto->add_view($msk);
            $i++;
            $this->display_progress($i, $per_sec, $msk->dsp_id());
        }
        return $usr_msg;
    }

    /**
     * add the components from the json array to the data object
     * @param array $json_array the component part of the import json
     * @param user $usr_trigger the user who has started the import
     * @param data_object $dto the data object that should be filled
     * @param float $per_sec the expected number of formulas that can be analysed per second
     * @return user_message the messages to the user if something has not been fine
     */
    private function dto_get_components(
        array       $json_array,
        user        $usr_trigger,
        data_object $dto,
        float       $per_sec = 0
    ): user_message
    {
        $usr_msg = new user_message();

        $i = 0;
        foreach ($json_array as $cmp_json) {
            $cmp = new component($usr_trigger);
            $usr_msg->add($cmp->import_mapper($cmp_json, $dto));
            $dto->add_component($cmp);
            $i++;
            $this->display_progress($i, $per_sec, $cmp->dsp_id());
        }
        return $usr_msg;
    }

    /**
     * create a data object based on a yaml zukunft.com import array
     *
     * @param array $yml_arr the array of a zukunft.com yaml
     * @param user $usr_trigger the user who has started the import
     * @return data_object filled based on the yaml array
     */
    function get_data_object_yaml(array $yml_arr, user $usr_trigger): data_object
    {
        $dto = new data_object($usr_trigger);
        $wrd = null;
        $trp = null;
        $val = null;
        $phr_lst = new phrase_list($usr_trigger);
        $dto = $this->get_data_object_yaml_loop($dto, $phr_lst, $yml_arr, $wrd, $trp, $val, $usr_trigger);
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

    private function get_data_object_yaml_loop(
        data_object $dto,
        phrase_list $phr_lst,
        array       $yml_arr,
        ?word       $wrd,
        ?triple     $trp,
        ?value_base $val,
        user        $usr_trigger
    ): data_object
    {
        foreach ($yml_arr as $key => $value) {
            // add the tooltip to the last added phrase of value
            if ($key == words::TOOLTIP_COMMENT) {
                if ($wrd == null and $trp == null and $val == null) {
                    $dto->add_message('yaml is not expected to start with a tooltip-comment');
                } else {
                    if ($wrd != null) {
                        $wrd->set_description($value);
                        $dto->add_word($wrd);
                        $phr_lst->add_by_name($wrd->phrase());
                        $wrd = null;
                    }
                    if ($trp != null) {
                        $trp->set_description($value);
                        $dto->add_triple($trp);
                        $phr_lst->add_by_name($trp->phrase());
                        $trp = null;
                    }
                    if ($val != null) {
                        $val->set_description($value);
                        $dto->add_value($val);
                        $val = null;
                    }
                }
            } else {
                // add the previous set word or triple to the lists
                if ($wrd != null) {
                    $dto->add_word($wrd);
                    $phr_lst->add_by_name($wrd->phrase());
                    $wrd = null;
                }
                if ($trp != null) {
                    $dto->add_triple($trp);
                    $phr_lst->add_by_name($trp->phrase());
                    $trp = null;
                }
                // add the previous value to the lists
                if ($val != null) {
                    $dto->add_value($val);
                    $val = null;
                }
                // add the phrase
                // if the name has a space create the separate words and use the triple
                if (str_contains($key, ' ')) {
                    $trp = $this->yaml_data_object_map_triple($key, $dto, $usr_trigger);
                } else {
                    // set the name for a normal word
                    // but ignore the keyword "sys-conv-value" that is only used as a placeholder for the value
                    if ($key != words::SYS_CONF_VALUE) {
                        $wrd = new word($usr_trigger);
                        $wrd->set_name($key);
                    }
                }
                // add this word or triple to the lists
                $sub_phr_lst = clone $phr_lst;
                if ($wrd != null) {
                    $dto->add_word($wrd);
                    $sub_phr_lst->add_by_name($wrd->phrase());
                    $wrd = null;
                }
                if ($trp != null) {
                    $dto->add_triple($trp);
                    $sub_phr_lst->add_by_name($trp->phrase());
                    $trp = null;
                }
                // add the sub array
                if (is_array($value)) {
                    $dto = $this->get_data_object_yaml_loop($dto, $sub_phr_lst, $value, $wrd, $trp, $val, $usr_trigger);
                } else {
                    // remember the value
                    // TODO add percent, geo and time
                    if (is_string($value)) {
                        $val = new value_text($usr_trigger);
                    } else {
                        $val = new value($usr_trigger);
                    }
                    $val->set_phrase_lst($sub_phr_lst);
                    $val->set_value($value);
                }
            }
        }
        // add the previous value to the lists
        if ($val != null) {
            $dto->add_value($val);
            $val = null;
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
