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

    // parameters to filter the import
    public ?user $usr = null; // the user who wants to import data
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

    public float $start_time;
    public float $step_time;
    public float $start_analyse;
    public float $start_save;
    public float $last_display_time;

    function __construct()
    {
        $this->start_time = microtime(true);
        $this->start_analyse = microtime(true);
        $this->start_save = microtime(true);
        $this->last_display_time = microtime(true);
    }

    /**
     * show the progress of an import process
     * @param float $expected_time the expected total as time or percent
     * @param string $name of the process
     * @param string|msg_id $step the part of the process that is done at the moment
     * @param bool $show if true the message should be preferred shown to the user
     * @param bool $stat if true the statistic of the import should be shown
     * @return void
     */
    function display_progress(
        float         $expected_time,
        string        $name = '',
        string|msg_id $step = '',
        bool          $show = false,
        bool          $stat = false
    ): void
    {
        $check_time = microtime(true);
        $time_since_last_display = $check_time - $this->last_display_time;
        $real_time = $check_time - $this->start_time;
        if (!is_string($step)) {
            $step = $step->text();
        }
        if ($stat) {
            echo $name . ' ' . round($real_time, 3) . 's ' . $expected_time . ' ' . $step . "\n";
        } elseif ($show or ($time_since_last_display > UI_MIN_RESPONSE_TIME)) {
            if ($real_time < 0.001) {
                $progress = '';
            } else {
                $progress = round($real_time / $expected_time * 100, 1) . '% ';
            }
            //echo '<br><br>import' . $progress . ' done<br>';
            echo $name . ' '
                . round($real_time, 3) . 's / ' . round($expected_time, 3) . 's '
                . $progress . $step . "\n";
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
            $dto = $this->get_data_object_yaml($yaml_array, $usr_trigger);
            $usr_msg = $dto->save($this, 'config yaml');
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
     * @param string $filename the filename for user info only
     * @param float $time_total the estimated total time for the import in seconds
     * @return user_message the result of the import
     */
    function put_json(
        string $json_str,
        user   $usr_trigger,
        string $filename,
        float  $time_total
    ): user_message
    {
        global $cfg;
        global $mtr;

        // get the relevant config values
        $decode_bytes_per_second = $cfg->get_by([
            words::DECODE,
            triples::BYTES_SECOND,
            triples::EXPECTED_TIME, words::IMPORT], 1);
        $object_creation_bytes_per_second = $cfg->get_by([
            triples::OBJECT_CREATION,
            triples::BYTES_SECOND,
            triples::EXPECTED_TIME, words::IMPORT], 1);

        $usr_msg = new user_message();
        $name = 'import ' . $filename;

        // read the import file
        $size = strlen($json_str);
        $time_decode = $size / $decode_bytes_per_second;
        $this->display_progress($time_total, $name, 'decode');
        $json_array = json_decode($json_str, true);

        // analyse the import file
        $step_start_time = microtime(true);
        $this->display_progress($time_total, $name, 'analysing');
        $dto = $this->get_data_object($json_array, $usr_trigger, $usr_msg, $filename, $time_total, $size);
        $step_end_time = microtime(true);
        $expected_analyse_time = $size / $object_creation_bytes_per_second;
        $real_step_time = $step_end_time - $step_start_time;
        $offset = $real_step_time - $expected_analyse_time;
        $time_total = $time_total + $offset;

        // write to the database
        $usr_msg->add($dto->save($this, $filename, $time_total));

        // show the import result
        if ($usr_msg->is_ok()) {
            $step = $mtr->txt(msg_id::DONE);
        } else {
            $step = $usr_msg->all_message_text();
        }

        $this->display_progress($time_total, $name, $step, true);

        return $usr_msg;
    }

    /**
     * drop a zukunft.com json message direct to the database
     *
     * @param string $json_str the zukunft.com JSON message to import as a string
     * @param user $usr_trigger the user who has triggered the import
     * @param string $filename the filename for user info only
     * @param float $time_total the estimated total time for the import in seconds
     * @return user_message the result of the import
     */
    function put_json_direct(
        string $json_str,
        user   $usr_trigger,
        string $filename = '',
        float  $time_total = 1
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
     * @param string $filename the filename for user info only
     * @param float $total the expected total time for the import in seconds
     * @return user_message the result of the import
     */
    private function put(
        array  $json_array,
        user   $usr_trigger,
        string $filename = '',
        float  $total = 1.0
    ): user_message
    {
        global $usr;
        $lib = new library();

        log_debug();
        $usr_msg = new user_message();
        $this->last_display_time = microtime(true);

        //$total = $lib->count_recursive($json_array, 3);
        $msg = 'import ' . $filename;
        $val_steps = round(self::IMPORT_VALIDATE_PCT_TIME * $total);
        //$total = $total + $val_steps;

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
            $this->display_progress($total, $msg);
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
                    $this->display_progress($total, $msg, $lib->class_to_table(verb::class));
                    $pos++;
                }
                $usr_msg->add($import_result);
            } elseif ($key == json_fields::WORDS) {
                foreach ($json_obj as $word) {
                    $wrd = new word($usr_trigger);
                    $import_result = $wrd->import_obj($word);
                    if ($import_result->is_ok()) {
                        $this->words_done++;
                    } else {
                        $this->words_failed++;
                    }
                    $usr_msg->add($import_result);
                    $this->display_progress($total, $msg, $lib->class_to_table(word::class));
                    $pos++;
                }
            } elseif ($key == json_fields::WORD_LIST) {
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
                $this->display_progress($total, $msg, $lib->class_to_table(phrase_list::class));
                $pos++;
            } elseif ($key == json_fields::TRIPLES) {
                foreach ($json_obj as $triple) {
                    $wrd_lnk = new triple($usr_trigger);
                    $import_result = $wrd_lnk->import_obj($triple);
                    if ($import_result->is_ok()) {
                        $this->triples_done++;
                    } else {
                        $this->triples_failed++;
                    }
                    $usr_msg->add($import_result);
                    $this->display_progress($pos, $total, $msg, $lib->class_to_table(triple::class));
                    $pos++;
                }
            } elseif ($key == json_fields::FORMULAS) {
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
                    $this->display_progress($total, $msg, $lib->class_to_table(formula::class));
                    $pos++;
                }
            } elseif ($key == json_fields::SOURCES) {
                foreach ($json_obj as $value) {
                    $src = new source($usr_trigger);
                    $import_result = $src->import_obj($value);
                    if ($import_result->is_ok()) {
                        $this->sources_done++;
                    } else {
                        $this->sources_failed++;
                    }
                    $usr_msg->add($import_result);
                    $this->display_progress($total, $msg, $lib->class_to_table(source::class));
                    $pos++;
                }
            } elseif ($key == json_fields::REFS) {
                foreach ($json_obj as $value) {
                    $ref = new ref($usr_trigger);
                    $import_result = $ref->import_obj($value);
                    if ($import_result->is_ok()) {
                        $this->refs_done++;
                    } else {
                        $this->refs_failed++;
                    }
                    $usr_msg->add($import_result);
                    $this->display_progress($total, $msg, $lib->class_to_table(ref::class));
                    $pos++;
                }
            } elseif ($key == json_fields::PHRASE_VALUES) {
                foreach ($json_obj as $val_key => $number) {
                    $val = new value($usr_trigger);
                    $import_result = $val->import_phrase_value($val_key, $number);
                    if ($import_result->is_ok()) {
                        $this->values_done++;
                    } else {
                        $this->values_failed++;
                    }
                    $usr_msg->add($import_result);
                    $this->display_progress($total, $msg, $lib->class_to_table(value::class));
                    $pos++;
                }
            } elseif ($key == json_fields::VALUES) {
                foreach ($json_obj as $value) {
                    $val = new value($usr_trigger);
                    $import_result = $val->import_obj($value);
                    if ($import_result->is_ok()) {
                        $this->values_done++;
                    } else {
                        $this->values_failed++;
                    }
                    $usr_msg->add($import_result);
                    $this->display_progress($total, $msg, $lib->class_to_table(value::class));
                    $pos++;
                }
            } elseif ($key == json_fields::VALUE_LIST) {
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
                    $this->display_progress($total, $msg, $lib->class_to_table(value_list::class));
                    $pos++;
                }
            } elseif ($key == json_fields::VIEWS) {
                foreach ($json_obj as $view) {
                    $view_obj = new view($usr_trigger);
                    $import_result = $view_obj->import_obj($view);
                    if ($import_result->is_ok()) {
                        $this->views_done++;
                    } else {
                        $this->views_failed++;
                    }
                    $usr_msg->add($import_result);
                    $this->display_progress($total, $msg, $lib->class_to_table(view::class));
                    $pos++;
                }
            } elseif ($key == json_fields::COMPONENTS) {
                foreach ($json_obj as $cmp) {
                    $cmp_obj = new component($usr_trigger);
                    $import_result = $cmp_obj->import_obj($cmp);
                    if ($import_result->is_ok()) {
                        $this->components_done++;
                    } else {
                        $this->components_failed++;
                    }
                    $usr_msg->add($import_result);
                    $this->display_progress($total, $msg, $lib->class_to_table(component::class));
                    $pos++;
                }
            } elseif ($key == json_fields::CALC_VALIDATION) {
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
                    $this->display_progress($total, $msg, $lib->class_to_table(result::class));
                    $pos++;
                }
            } elseif ($key == json_fields::VIEW_VALIDATION) {
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
                    $this->display_progress($total, $msg, $lib->class_to_table(view::class));
                    $pos++;
                }
            } elseif ($key == json_fields::IP_BLACKLIST) {
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
                    $this->display_progress($total, $msg, $lib->class_to_table(ip_range::class));
                    $pos++;
                }
            } else {
                $usr_msg->add_message('Unknown element ' . $key);
            }
        }

        // show 90% before validation starts
        $this->display_progress($total, $msg, 'validate');

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
        $this->display_progress($total, $msg);

        return $usr_msg;
    }

    /**
     * create a data object based on a json zukunft.com import array
     *
     * @param array $json_array the array of a zukunft.com yaml
     * @param user $usr_trigger the user who has started the import
     * @param string $filename the filename for user info only
     * @param float $total the expected total time for the import in seconds
     * @param int $size the number of bytes that needs to be processed
     * @return data_object filled based on the yaml array
     */
    function get_data_object(
        array        $json_array,
        user         $usr_trigger,
        user_message $usr_msg = new user_message(),
        string       $filename = '',
        float        $total = 1.0,
        int          $size = 0
    ): data_object
    {
        global $cfg;

        // get the relevant config values
        $analyse_bytes_per_second = $cfg->get_by([
            triples::OBJECT_CREATION, triples::BYTES_SECOND, triples::EXPECTED_TIME, words::IMPORT], 1);
        $analyse_words_per_second = $cfg->get_by(
            [triples::ANALYSE_WORDS, triples::BYTES_SECOND, triples::EXPECTED_TIME, words::IMPORT], 1);

        // estimate the total estimated time for analysing the import data
        $time_analyse = $size / $analyse_bytes_per_second;

        // estimate the time for each object type
        // where 5 is the number of data objects that are filled with this function
        $steps = 5;
        $step_time = $time_analyse / $steps;

        // create the data_object to fill
        $dto = new data_object($usr_trigger);

        $usr_msg->add($this->message_check($json_array));
        $msg = 'import ' . $filename;
        $sub_topic = 'counted ';
        if ($usr_msg->is_ok()) {
            // TODO add json_fields::IP_BLACKLIST
            // TODO add json_fields::USERS
            // TODO add json_fields::LIST_VERBS
            if (key_exists(json_fields::WORDS, $json_array)) {
                $this->step_start();
                $wrd_array = $json_array[json_fields::WORDS];
                $usr_msg->add($this->get_data_object_words($wrd_array, $usr_trigger, $dto,
                    $total, $step_time, $msg, $sub_topic));
                $total = $this->step_end($dto->word_list()->count(), $total, $analyse_words_per_second,
                    $msg, $sub_topic, word::class);
            }
            // TODO add json_fields::WORD_LIST
            if (key_exists(json_fields::TRIPLES, $json_array)) {
                $this->step_start();
                $trp_array = $json_array[json_fields::TRIPLES];
                $usr_msg->add($this->get_data_object_triples($trp_array, $usr_trigger, $dto,
                    $total, $step_time, $msg, $sub_topic));
                $total = $this->step_end($dto->triple_list()->count(), $total, $analyse_words_per_second,
                    $msg, $sub_topic, triple::class);
            }
            if (key_exists(json_fields::SOURCES, $json_array)) {
                $this->step_start();
                $src_array = $json_array[json_fields::SOURCES];
                $usr_msg->add($this->get_data_object_sources($src_array, $usr_trigger, $dto,
                    $total, $step_time, $msg, $sub_topic));
                $total = $this->step_end($dto->source_list()->count(), $total, $analyse_words_per_second,
                    $msg, $sub_topic, source::class);
            }
            // TODO add json_fields::REFS
            // TODO add json_fields::PHRASE_VALUES
            if (key_exists(json_fields::VALUES, $json_array)) {
                $this->step_start();
                $val_array = $json_array[json_fields::VALUES];
                $usr_msg->add($this->get_data_object_values($val_array, $usr_trigger, $dto,
                    $total, $step_time, $msg, $sub_topic));
                $total = $this->step_end($dto->value_list()->count(), $total, $analyse_words_per_second,
                    $msg, $sub_topic, value::class);
            }
            // TODO add json_fields::VALUE_LIST
            if (key_exists(json_fields::FORMULAS, $json_array)) {
                $this->step_start();
                $frm_array = $json_array[json_fields::FORMULAS];
                $usr_msg->add($this->get_data_object_formulas($frm_array, $usr_trigger, $dto,
                    $total, $step_time, $msg, $sub_topic));
                $total = $this->step_end($dto->formula_list()->count(), $total, $analyse_words_per_second,
                    $msg, $sub_topic, formula::class);
            }
            // TODO add json_fields::RESULTS
            // TODO add json_fields::CALC_VALIDATION
            // TODO add json_fields::VIEWS
            // TODO add json_fields::COMPONENTS
            // TODO add json_fields::VIEW_VALIDATION
        }
        return $dto;
    }

    private function step_start(): void
    {
        $this->step_time = microtime(true);
    }

    /**
     * @param int $nbr the number of precessed objects e.g. count(word)
     * @param float $est_time the estimated total time for the import
     * @param float $est_per_sec the expected number of objects that can be processed per second
     * @param string $msg text to show to the user which file is imported
     * @param string $sub_topic text to show to the user the step that is processed at the moment
     * @param string $class the class name that is processed now
     * @return float the adjusted total estimated time
     */
    private function step_end(
        int    $nbr = 0,
        float  $est_time = 0.0,
        float  $est_per_sec = 0.0,
        string $msg = '',
        string $sub_topic = '',
        string $class = ''
    ): float
    {
        $end_time = microtime(true);

        $lib = new library();
        if ($nbr > 0) {
            $part = $sub_topic . $lib->class_to_table($class) . ': ' . $nbr;
            $this->display_progress($est_time, $msg, $part, true);
            $expected_step_time = $nbr / $est_per_sec;
            $real_step_time = $end_time - $this->step_time;
            $offset = $real_step_time - $expected_step_time;
            $est_time = $est_time + $offset;
        }
        return $est_time;
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
     * @param float $total the total expect time of the import
     * @param float $total_step the total expect time of the import step
     * @param string $msg the name of the import e.g. import currency.json
     * @param string $sub_topic the name of the import step e.g. count values
     * @return user_message the messages to the user if something has not been fine
     */
    private function get_data_object_words(
        array       $json_array,
        user        $usr_trigger,
        data_object $dto,
        float       $total = 0,
        float       $total_step = 0,
        string      $msg = '',
        string      $sub_topic = ''
    ): user_message
    {
        $usr_msg = new user_message();
        $step = $this->get_data_object_step($json_array, $total_step);
        $part = $this->get_data_object_part($sub_topic, word::class);
        $i = 0;
        foreach ($json_array as $wrd_json) {
            $wrd = new word($usr_trigger);
            $usr_msg->add($wrd->import_mapper($wrd_json));
            $dto->add_word($wrd);
            $i++;
            $this->display_progress($total, $msg, $part . $i);
        }
        return $usr_msg;
    }

    /**
     * add the triples from the json array to the data object
     * @param array $json_array the word part of the import json
     * @param user $usr_trigger the user who has started the import
     * @param data_object $dto the data object that should be filled
     * @param float $total the total expect time of the import
     * @param float $total_step the total expect time of the import step
     * @param string $msg the name of the import e.g. import currency.json
     * @param string $sub_topic the name of the import step e.g. count values
     * @return user_message the messages to the user if something has not been fine
     */
    private function get_data_object_triples(
        array       $json_array,
        user        $usr_trigger,
        data_object $dto,
        float       $total = 0,
        float       $total_step = 0,
        string      $msg = '',
        string      $sub_topic = ''
    ): user_message
    {
        $usr_msg = new user_message();
        $step = $this->get_data_object_step($json_array, $total_step);
        $part = $this->get_data_object_part($sub_topic, triple::class);
        $i = 0;
        foreach ($json_array as $trp_json) {
            $trp = new triple($usr_trigger);
            $usr_msg->add($trp->import_mapper($trp_json, $dto));
            $dto->add_triple($trp);
            $i++;
            $this->display_progress($total, $msg, $part . $i);
        }
        return $usr_msg;
    }

    /**
     * add the source from the json array to the data object
     * @param array $json_array the source part of the import json
     * @param user $usr_trigger the user who has started the import
     * @param data_object $dto the data object that should be filled
     * @param float $total the total expect time of the import
     * @param float $total_step the total expect time of the import step
     * @param string $msg the name of the import e.g. import currency.json
     * @param string $sub_topic the name of the import step e.g. count values
     * @return user_message the messages to the user if something has not been fine
     */
    private function get_data_object_sources(
        array       $json_array,
        user        $usr_trigger,
        data_object $dto,
        float       $total = 0,
        float       $total_step = 0,
        string      $msg = '',
        string      $sub_topic = ''
    ): user_message
    {
        $usr_msg = new user_message();
        $step = $this->get_data_object_step($json_array, $total_step);
        $part = $this->get_data_object_part($sub_topic, source::class);
        $i = 0;
        foreach ($json_array as $src_json) {
            $src = new source($usr_trigger);
            $usr_msg->add($src->import_mapper($src_json, $dto));
            $dto->add_source($src);
            $i++;
            $this->display_progress($total, $msg, $part . $i);
        }
        return $usr_msg;
    }

    /**
     * add the source from the json array to the data object
     * @param array $json_array the source part of the import json
     * @param user $usr_trigger the user who has started the import
     * @param data_object $dto the data object that should be filled
     * @param float $total the total expect time of the import
     * @param float $total_step the total expect time of the import step
     * @param string $msg the name of the import e.g. import currency.json
     * @param string $sub_topic the name of the import step e.g. count values
     * @return user_message the messages to the user if something has not been fine
     */
    private function get_data_object_values(
        array       $json_array,
        user        $usr_trigger,
        data_object $dto,
        float       $total = 0,
        float       $total_step = 0,
        string      $msg = '',
        string      $sub_topic = ''
    ): user_message
    {
        $usr_msg = new user_message();
        $step = $this->get_data_object_step($json_array, $total_step);
        $part = $this->get_data_object_part($sub_topic, value::class);
        $i = 0;
        foreach ($json_array as $val_json) {
            $val = new value($usr_trigger);
            $usr_msg->add($val->import_mapper($val_json, $dto));
            $dto->add_value($val);
            $i++;
            $this->display_progress($total, $msg, $part . $i);
        }
        return $usr_msg;
    }

    /**
     * add the triples from the json array to the data object
     * @param array $json_array the word part of the import json
     * @param user $usr_trigger the user who has started the import
     * @param data_object $dto the data object that should be filled
     * @param float $total the total expect time of the import
     * @param float $total_step the total expect time of the import step
     * @param string $msg the name of the import e.g. import currency.json
     * @param string $sub_topic the name of the import step e.g. count values
     * @return user_message the messages to the user if something has not been fine
     */
    private function get_data_object_formulas(
        array       $json_array,
        user        $usr_trigger,
        data_object $dto,
        float       $total = 0,
        float       $total_step = 0,
        string      $msg = '',
        string      $sub_topic = ''
    ): user_message
    {
        $usr_msg = new user_message();
        $step = $this->get_data_object_step($json_array, $total_step);
        $part = $this->get_data_object_part($sub_topic, formula::class);
        $i = 0;
        foreach ($json_array as $frm_json) {
            $frm = new formula($usr_trigger);
            $usr_msg->add($frm->import_mapper($frm_json, $dto));
            $dto->add_formula($frm);
            $i++;
            $this->display_progress($total, $msg, $part . $i);
        }
        return $usr_msg;
    }

    /**
     * add the source from the json array to the data object
     * @param array $json_array the source part of the import json
     * @param float $total_step the total expect time of the import step
     * @return float the step size of each object of this import step
     */
    private function get_data_object_step(
        array $json_array,
        float $total_step
    ): float
    {
        if (count($json_array) > 0) {
            return $total_step / count($json_array);
        } else {
            return 0.0;
        }
    }

    /**
     * add the source from the json array to the data object
     * @param string $sub_topic the name of the import step e.g. count values
     * @param string $class the name class that is imported with this step
     * @return string the name of the import step
     */
    private function get_data_object_part(
        string $sub_topic = '',
        string $class = ''
    ): string
    {
        $lib = new library();
        return $sub_topic . $lib->class_to_table($class) . ': ';
    }

    /**
     * add the source from the json array to the data object
     * @param int $i the number of step objects imported so fare
     * @param float $total the total expect time of the import
     * @param string $msg the name of the import e.g. import currency.json
     * @param string $part the name of the import step e.g. count values
     */
    private function get_data_object_display(
        int    $i,
        float  $total,
        string $msg = '',
        string $part = ''
    ): void
    {
        $this->display_progress($total, $msg, $part . $i);
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
                    $val = new value($usr_trigger);
                    $val->set_phrase_lst($sub_phr_lst);
                    if (is_string($value)) {
                        // TODO Prio 1
                        log_warning('string value not yet implemented');
                    } else {
                        $val->set_value($value);
                    }
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
