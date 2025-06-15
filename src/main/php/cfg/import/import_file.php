<?php

/*

  import.php - IMPORT a json in the zukunft.com exchange format
  ----------
  

zukunft.com - calc with words

copyright 1995-2021 by zukunft.com AG, Zurich

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

namespace cfg\import;

include_once MODEL_HELPER_PATH . 'config_numbers.php';
include_once MODEL_IMPORT_PATH . 'import.php';
include_once MODEL_USER_PATH . 'user.php';
include_once MODEL_USER_PATH . 'user_message.php';
include_once MODEL_CONST_PATH . 'files.php';
include_once SHARED_CONST_PATH . 'triples.php';
include_once SHARED_CONST_PATH . 'words.php';
include_once SHARED_ENUM_PATH . 'messages.php';
include_once SHARED_TYPES_PATH . 'file_types.php';
include_once TEST_CONST_PATH . 'files.php';

use cfg\const\files;
use cfg\helper\config_numbers;
use cfg\user\user;
use cfg\user\user_message;
use const\files as test_files;
use shared\const\triples;
use shared\const\words;
use shared\enum\messages as msg_id;
use shared\types\file_types;

class import_file
{

    const FILE = 'file';

    public float $start_time;
    public float $start_read;
    public float $start_analyse;
    public float $start_save;

    function __construct()
    {
        $this->start_time = microtime(true);
        $this->start_read = microtime(true);
        $this->start_analyse = microtime(true);
        $this->start_save = microtime(true);
    }


    /*
     * set and get
     */

    /*
     * use to apply the time of the parent process for continuous timestamp reporting
     */
    function set_start_time(float $tart_time): void
    {
        $this->start_time = $tart_time;
    }


    /**
     * import a single json file
     * TODO return a user message instead of a string
     *
     * @param string $filename
     * @param user $usr
     * @param bool $direct true if each object should be saved separate in the database
     * @return user_message
     */
    function json_file(string $filename, user $usr, bool $direct = true): user_message
    {
        global $cfg;

        $usr_msg = new user_message();
        $imp = new import($filename);
        $imp->set_start_time($this->start_time);

        // get the relevant config values
        $read_bytes_per_sec = $cfg->get_by([triples::FILE_READ, triples::BYTES_PER_SECOND, triples::EXPECTED_TIME, words::IMPORT], 1);
        $total_bytes_per_sec = $cfg->get_by([words::TOTAL_PRE, triples::BYTES_PER_SECOND, triples::EXPECTED_TIME, words::IMPORT], 1);
        $read_time_pct = $cfg->get_by([triples::FILE_READ, triples::TIME_PERCENT, words::IMPORT], 1);
        $decode_time_pct = $cfg->get_by([words::DECODE, triples::TIME_PERCENT, words::IMPORT], 1);
        $create_time_pct = $cfg->get_by([triples::OBJECT_CREATION, triples::TIME_PERCENT, words::IMPORT], 1);
        $store_time_pct = $cfg->get_by([triples::OBJECT_STORING, triples::TIME_PERCENT, words::IMPORT], 1);

        // indicate to the user that the import has started
        $size = filesize($filename);
        $imp->est_time_total = $size / $total_bytes_per_sec;
        $imp->est_time_read = $imp->est_time_total * $read_time_pct / 100;
        $imp->est_time_decode = $imp->est_time_total * $decode_time_pct / 100;
        $imp->est_time_create = $imp->est_time_total * $create_time_pct / 100;
        $imp->est_time_store = $imp->est_time_total * $store_time_pct / 100;


        // read the import file
        $imp->step_main_start(msg_id::READ, $imp->est_time_read);
        $imp->step_start(msg_id::READ, self::FILE, $size, $imp->est_time_read);
        $json_str = file_get_contents($filename);
        $imp->step_end($size, $read_bytes_per_sec);
        $imp->step_main_end($size, $read_bytes_per_sec);

        if (!$json_str) {
            $this->read_error($filename, file_types::JSOM, $usr_msg);
        } else {
            if ($json_str == '') {
                $usr_msg->add_id(msg_id::FAILED_MESSAGE_EMPTY);
            } else {

                // analyse the import file and update the database
                if ($direct) {
                    $import_result = $imp->put_json_direct($json_str, $usr);
                } else {
                    $import_result = $imp->put_json($json_str, $usr);
                }

                // show the summery to the user
                if ($import_result->is_ok()) {
                    $usr_msg->add_info_text(' done ('
                        . $imp->words_done . ' words, '
                        . $imp->verbs_done . ' verbs, '
                        . $imp->triples_done . ' triples, '
                        . $imp->formulas_done . ' formulas, '
                        . $imp->values_done . ' values, '
                        . $imp->list_values_done . ' simple values, '
                        . $imp->sources_done . ' sources, '
                        . $imp->refs_done . ' references, '
                        . $imp->views_done . ' views loaded, '
                        . $imp->components_done . ' components loaded, '
                        . $imp->calc_validations_done . ' results validated, '
                        . $imp->view_validations_done . ' views validated)');
                    if ($imp->users_done > 0) {
                        $usr_msg->add_info_text(' ... and ' . $imp->users_done . ' $users');
                    }
                    if ($imp->system_done > 0) {
                        $usr_msg->add_info_text(' ... and ' . $imp->system_done . ' $system objects');
                    }
                } else {
                    $usr_msg->add($import_result);
                    //$usr_msg->add_message_text('import of ' . $filename . ' failed');
                }
            }
        }

        return $usr_msg;
    }

    /**
     * import a single yaml file
     *
     * @param string $filename the file name including the local path of the file that should be imported
     * @param user $usr the user who has triggered the import
     * @return user_message the result of the import with suggested solution in case of a problem
     */
    function yaml_file(string $filename, user $usr): user_message
    {
        $usr_msg = new user_message();

        $yaml_str = file_get_contents($filename);
        if (!$yaml_str) {
            $this->read_error($filename, file_types::YAML, $usr_msg);
        } else {
            if ($yaml_str == '') {
                $this->empty_warning($filename, $usr_msg);
            } else {
                $imp = new import($filename);
                $import_result = $imp->put_yaml($yaml_str, $usr);
                if ($import_result->is_ok()) {
                    $this->done($imp->summary(), $usr_msg);
                } else {
                    $this->failed($import_result->all_message_text(), $usr_msg);
                }
                $usr_msg->add($import_result);
            }
        }

        return $usr_msg;
    }

    /**
     * import the initial system configuration
     * TODO validate the import by comparing the import with the api message to tne frontend
     *
     * @param user $usr who has triggered the function
     * @param bool $validate if true the import is validated even if the number of the values matches
     * @return user_message true if the configuration has imported
     */
    function import_config_yaml(user $usr, bool $validate = false): user_message
    {
        global $mtr;

        $usr_msg = new user_message();

        // only admin users are allowed to load the system config from the resource file
        if ($usr->is_admin() or $usr->is_system()) {

            // import the system configuration from the resource file
            $imf = new import_file();
            $usr_msg->add($imf->yaml_file(files::SYSTEM_CONFIG, $usr));

            // check the import if needed or requested
            if (!$usr_msg->is_ok() or $validate) {

                // load the system configuration from the database
                // TODO Prio 3 base the validation on the export yaml
                $cfg = new config_numbers($usr);
                $cfg->load_cfg($usr);

                // check based on the number of values
                $cfg_nbr = $cfg->count();
                $chk_nbr = $usr_msg->checksum();
                if ($cfg_nbr != $chk_nbr or $validate) {

                    // report a number difference
                    if ($cfg_nbr != $chk_nbr) {
                        $usr_msg->add_id_with_vars(msg_id::IMPORT_COUNT_DIFF, [
                            msg_id::VAR_FILE_NAME => files::SYSTEM_CONFIG,
                            msg_id::VAR_VALUE_COUNT => $cfg_nbr,
                            msg_id::VAR_VALUE_COUNT_CHK => $chk_nbr,
                        ]);
                    }

                    // reload the target values to be able to report the missing config values
                    $imp = new import(files::SYSTEM_CONFIG);
                    $yaml_str = file_get_contents(files::SYSTEM_CONFIG);
                    $yaml_array = yaml_parse($yaml_str);
                    $dto = $imp->get_data_object_yaml($yaml_array, $usr);
                    $load_msg = $dto->load();
                    if (!$load_msg->is_ok()) {

                        // report the issues on loading the config values
                        $usr_msg->add($load_msg);
                    } else {
                        if ($validate) {

                            // report all config value differences
                            $usr_msg->add($cfg->diff_msg($dto->value_list()));
                        } else {

                            // report al least the missing config values
                            $val_diff = $dto->value_list()->diff($cfg);
                            if ($val_diff->is_empty()) {

                                // confirm the validation by counting the values
                                $usr_msg->add_id_with_vars(msg_id::IMPORT_VALUE_COUNT_VALIDATED, [
                                    msg_id::VAR_FILE_NAME => files::SYSTEM_CONFIG,
                                    msg_id::VAR_VALUE_COUNT => $cfg_nbr,
                                ]);
                            } else {

                                // report al least the missing config values
                                $usr_msg->add_id_with_vars(msg_id::IMPORT_VALUES_MISSING, [
                                    msg_id::VAR_FILE_NAME => files::SYSTEM_CONFIG,
                                    msg_id::VAR_VALUE_LIST => $val_diff->dsp_id(),
                                ]);
                            }
                        }
                    }

                    // sum the import result
                    if (!$usr_msg->is_ok()) {
                        $usr_msg->add_id_with_vars(msg_id::IMPORT_FAIL_BECAUSE, [
                            msg_id::VAR_FILE_NAME => files::SYSTEM_CONFIG,
                            msg_id::VAR_VALUE_LIST => $usr_msg->all_message_text(),
                        ]);
                        $msg = $usr_msg->all_message_text();
                        echo $msg . "\n";
                        log_err($msg);
                    }
                }
            }

            // show the last message to the user which is hopefully a confirmation how many config values have been imported
            $msg = $usr_msg->all_message_text();
            echo $mtr->txt(msg_id::IMPORT_JSON) . ' ' . basename(files::SYSTEM_CONFIG) . ' ' . $msg . "\n";
            if (!$usr_msg->is_ok()) {
                log_warning($msg);
            }
        }

        return $usr_msg;
    }

    /**
     * TODO move HTML code to frontend
     * import all zukunft.com base configuration json files
     * for an import it can be assumed that this base configuration is loaded
     * even if a user has overwritten some of these definitions the technical import should be possible
     * TODO load this configuration on first start of zukunft
     * TODO add a check bottom for admin to reload the base configuration
     */
    function import_base_config(user $usr, bool $direct = false): string
    {
        $result = '';
        log_info('base setup',
            'import_base_config',
            'import of the base setup',
            'import_base_config',
            $usr, true
        );

        foreach (files::BASE_CONFIG_FILES as $filename) {
            $result .= $this->json_file(files::MESSAGE_PATH . $filename, $usr, $direct)->get_last_message();
        }

        // config files that cannot yet be loaded via list saving
        foreach (files::BASE_CONFIG_FILES_DIRECT as $filename) {
            $result .= $this->json_file(files::MESSAGE_PATH . $filename, $usr, true)->get_last_message();
        }

        log_debug('load base config ... done');

        return $result;
    }

    /**
     * import the default pod base configuration json files
     * for an import it can be assumed that this base configuration is loaded
     * even if a user has overwritten some of these definitions the technical import should be possible
     */
    function import_pod_config(user $usr, bool $direct = false): string
    {
        $result = '';
        log_info('pod setup',
            'import_pod_config',
            'import of the pod base setup',
            'import_pod_config',
            $usr, true
        );

        foreach (files::POD_CONFIG_FILES_DIRECT as $filename) {
            $result .= $this->json_file(files::MESSAGE_PATH . $filename, $usr, $direct)->get_last_message();
        }

        log_debug('load pod base config ... done');

        return $result;
    }

    /**
     * import the default pod base configuration json files
     * for an import it can be assumed that this base configuration is loaded
     * even if a user has overwritten some of these definitions the technical import should be possible
     */
    function import_test_config(user $usr, bool $direct = false): string
    {
        $result = '';
        log_info('test setup',
            'import_test_config',
            'import of the pod test setup',
            'import_test_config',
            $usr, true
        );

        foreach (test_files::TEST_IMPORT_FILE_LIST as $filename) {
            $result .= $this->json_file($filename, $usr, $direct)->get_last_message();
        }

        log_debug('load pod base config ... done');

        return $result;
    }

    /**
     * display a message immediately to the user
     * @param string $txt the text that should be should to the user
     */
    function echo(string $txt): void
    {
        echo $txt;
        echo "\n";
    }


    /*
     * internal message creation
     */

    /**
     * add the file read error to the user message
     * @param string $name the filename and path of the import file
     * @param string $type the file type
     * @param user_message $usr_msg the user message object
     */
    private function read_error(string $name, string $type, user_message $usr_msg): void
    {
        $usr_msg->add_id_with_vars(msg_id::IMPORT_READ_ERROR, [
            msg_id::VAR_FILE_TYPE => $type,
            msg_id::VAR_FILE_NAME => $name
        ]);
    }

    /**
     * add a warning that the file is empty to the user message
     * @param string $name the filename and path of the import file
     * @param user_message $usr_msg the user message object
     */
    private function empty_warning(string $name, user_message $usr_msg): void
    {
        $usr_msg->add_id_with_vars(msg_id::IMPORT_EMPTY, [
            msg_id::VAR_FILE_NAME => $name
        ]);
    }

    /**
     * add the final import result to the user message
     * @param string $err_txt a description of the errors and warning due to the import
     * @param user_message $usr_msg the user message object
     */
    private function failed(string $err_txt, user_message $usr_msg): void
    {
        $usr_msg->add_id_with_vars(msg_id::IMPORT_FAILED, [
            msg_id::VAR_ERROR_TEXT => $err_txt
        ]);
    }

    /**
     * add the final import result to the user message
     * @param string $summary a description of what has been imported
     * @param user_message $usr_msg the user message object
     */
    private function done(string $summary, user_message $usr_msg): void
    {
        $usr_msg->add_info_with_vars(msg_id::IMPORT_DONE, [
            msg_id::VAR_SUMMARY => $summary
        ]);
    }

}
