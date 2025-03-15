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

use cfg\const\files;
use cfg\helper\config_numbers;
use cfg\user\user;
use cfg\user\user_message;
use shared\const\triples;
use shared\const\words;

class import_file
{

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

    /**
     * import a single json file
     * TODO return a user message instead of a string
     *
     * @param string $filename
     * @param user $usr
     * @param bool $direct true if each object should be saved separate in the database 
     * @return string
     */
    function json_file(string $filename, user $usr, bool $direct = true): string
    {
        global $cfg;

        $import = new import();
        $msg = '';

        // get the relevant config values
        $read_bytes_per_second = $cfg->get_by(
            [triples::FILE_READ, triples::BYTES_SECOND, triples::EXPECTED_TIME, words::IMPORT], true);
        $total_bytes_per_second = $cfg->get_by(
            [words::TOTAL_PRE, triples::BYTES_SECOND, triples::EXPECTED_TIME, words::IMPORT], true);

        // indicate to the user that the import has started
        $size = filesize($filename);
        $time_total = $size / $total_bytes_per_second;
        $topic = 'import ' . basename($filename);
        $import->display_progress($time_total, $topic, '(' . round($size/1000) . ' kBytes)', true);

        // read the import file
        $json_str = file_get_contents($filename);
        $time_read = $size / $read_bytes_per_second;
        $import->display_progress($time_total, $topic, 'loaded');

        if (!$json_str) {
            log_err('Error reading JSON resource ' . $filename);
        } else {
            if ($json_str == '') {
                $msg .= ' failed because message file is empty of not found.';
            } else {

                // analyse the import file and update the database
                $import->start_analyse = microtime(true);
                if ($direct) {
                    $import_result = $import->put_json_direct($json_str, $usr, basename($filename), $time_total);
                } else {
                    $import_result = $import->put_json($json_str, $usr, basename($filename), $time_total);
                }

                // show the summery to the user
                if ($import_result->is_ok()) {
                    $msg .= ' done ('
                        . $import->words_done . ' words, '
                        . $import->verbs_done . ' verbs, '
                        . $import->triples_done . ' triples, '
                        . $import->formulas_done . ' formulas, '
                        . $import->values_done . ' values, '
                        . $import->list_values_done . ' simple values, '
                        . $import->sources_done . ' sources, '
                        . $import->refs_done . ' references, '
                        . $import->views_done . ' views loaded, '
                        . $import->components_done . ' components loaded, '
                        . $import->calc_validations_done . ' results validated, '
                        . $import->view_validations_done . ' views validated)';
                    if ($import->users_done > 0) {
                        $msg .= ' ... and ' . $import->users_done . ' $users';
                    }
                    if ($import->system_done > 0) {
                        $msg .= ' ... and ' . $import->system_done . ' $system objects';
                    }
                } else {
                    $msg .= ' failed because ' . $import_result->all_message_text() . '.';
                }
            }
        }

        return $msg;
    }

    /**
     * import a single yaml file
     *
     * @param string $filename
     * @param user $usr
     * @return user_message
     */
    function yaml_file(string $filename, user $usr): user_message
    {
        $usr_msg = new user_message();

        $yaml_str = file_get_contents($filename);
        if (!$yaml_str) {
            log_err('Error reading JSON resource ' . $filename);
        } else {
            if ($yaml_str == '') {
                $usr_msg->add_message(' failed because message file is empty of not found.');
            } else {
                $import = new import;
                $import_result = $import->put_yaml($yaml_str, $usr);
                if ($import_result->is_ok()) {
                    $usr_msg->add_info(' done (' . $import->status_text()->get_last_message() . ' )');
                    if ($import->users_done > 0) {
                        $usr_msg->add_message(' ... and ' . $import->users_done . ' $users');
                    }
                    if ($import->system_done > 0) {
                        $usr_msg->add_message(' ... and ' . $import->system_done . ' $system objects');
                    }
                } else {
                    $usr_msg->add_message(' failed because ' . $import_result->all_message_text() . '.');
                }
            }
        }

        return $usr_msg;
    }

    /**
     * import the initial system configuration
     * TODO validate the import by comparing the import with the api message to tne frontend
     *
     * @param user $usr who has triggered the function
     * @return bool true if the configuration has imported
     */
    function import_config_yaml(user $usr): bool
    {
        $result = false;

        if ($usr->is_admin() or $usr->is_system()) {
            $imf = new import_file();
            $import_result = $imf->yaml_file(files::SYSTEM_CONFIG, $usr);
            if (str_starts_with($import_result->get_last_message(), ' done ')) {
                $result = true;
            }
            // check the import
            $cfg = new config_numbers($usr);
            $cfg->load_cfg($usr);
            if ($cfg->count() != $import_result->checksum()) {
                // report the missing config values
                $imp = new import;
                $yaml_str = file_get_contents(files::SYSTEM_CONFIG);
                $yaml_array = yaml_parse($yaml_str);
                $dto = $imp->get_data_object_yaml($yaml_array, $usr);
                $dto->save($imp, 'config yaml');
                $val_diff = $dto->value_list()->diff($cfg);
                log_warning('These configuration values could not be imported: ' . $val_diff->dsp_id());
                //log_err('These configuration values could not be imported: ' . $val_diff->dsp_id());
            }
        }

        return $result;
    }

    /**
     * TODO move HTML code to frontend
     * import all zukunft.com base configuration json files
     * for an import it can be assumed that this base configuration is loaded
     * even if a user has overwritten some of these definitions the technical import should be possible
     * TODO load this configuration on first start of zukunft
     * TODO add a check bottom for admin to reload the base configuration
     */
    function import_base_config(user $usr, bool $direct = true): string
    {
        $result = '';
        log_info('base setup',
            'import_base_config',
            'import of the base setup',
            'import_base_config',
            $usr, true
        );

        foreach (files::BASE_CONFIG_FILES_DIRECT as $filename) {
            $this->echo('load ' . $filename);
            $result .= $this->json_file(files::MESSAGE_PATH . $filename, $usr, $direct);
        }

        log_debug('load base config ... done');

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

}
