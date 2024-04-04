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

include_once MODEL_IMPORT_PATH . 'import.php';

use cfg\user;
use html\html_base;

class import_file
{
    /**
     * import a single json file
     * TODO return a user message instead of a string
     *
     * @param string $filename
     * @param user $usr
     * @return string
     */
    function json_file(string $filename, user $usr): string
    {
        $msg = '';

        $json_str = file_get_contents($filename);
        if ($json_str == false) {
            log_err('Error reading JSON resource ' . $filename);
        } else {
            if ($json_str == '') {
                $msg .= ' failed because message file is empty of not found.';
            } else {
                $import = new import;
                $import_result = $import->put($json_str, $usr);
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
     * import the initial system configuration
     * @param user $usr who has triggered the function
     * @return bool true if the configuration has imported
     */
    function import_config(user $usr): bool
    {
        global $db_con;
        global $verbs;

        $result = false;

        if ($usr->is_admin() or $usr->is_system()) {
            $imf = new import_file();
            $import_result = $imf->json_file(SYSTEM_CONFIG_FILE, $usr);
            if (str_starts_with($import_result, ' done ')) {
                $result = true;
            }
        }

        // TODO load the config
        // $verbs = new verb_list($usr);
        // $verbs->load($db_con);

        return $result;
    }

    /**
     * import all zukunft.com base configuration json files
     * for an import it can be assumed that this base configuration is loaded
     * even if a user has overwritten some of these definitions the technical import should be possible
     * TODO load this configuration on first start of zukunft
     * TODO add a check bottom for admin to reload the base configuration
     */
    function import_base_config(user $usr): string
    {
        $result = '';
        log_info('base setup',
            'import_base_config',
            'import of the base setup',
            'import_base_config',
            $usr, true
        );

        $html = new html_base();
        foreach (BASE_CONFIG_FILES as $filename) {
            $html->echo('load ' . $filename);
            $result .= $this->json_file(PATH_BASE_CONFIG_MESSAGE_FILES . $filename, $usr);
        }

        log_debug('load base config ... done');

        return $result;
    }

    /**
     * import some zukunft.com test json files
     */
    function import_test_files(user $usr): string
    {
        $result = '';
        log_info('test import',
            'import_test_files',
            'import of the some test json files',
            'import_test_files',
            $usr, true
        );

        $html = new html_base();
        foreach (TEST_IMPORT_FILE_LIST as $filename) {
            $html->echo('load ' . $filename);
            $result .= $this->json_file(PATH_TEST_IMPORT_FILES . $filename, $usr);
        }

        log_debug('import test ... done');

        return $result;
    }

}
