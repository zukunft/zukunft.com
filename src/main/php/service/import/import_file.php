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

# import a single json file
function import_json_file($filename): string
{
    global $usr;

    $msg = '';

    $json_str = file_get_contents($filename);
    if ($json_str == '') {
        $msg .= ' failed because message file is empty of not found.';
    } else {
        $import = new file_import;
        $import->usr = $usr;
        $import->json_str = $json_str;
        $import_result = $import->put();
        if ($import_result == '') {
            $msg .= ' done (' . $import->words_done . ' words, ' . $import->triples_done . ' triples, ' . $import->formulas_done . ' formulas, ' . $import->values_done . ' sources, ' . $import->sources_done . ' values, ' . $import->views_done . ' views loaded)';
        } else {
            $msg .= ' failed because ' . $import_result . '.';
        }
    }

    return $msg;
}

# import all zukunft.com base configuration json files
# for an import it can be assumed that this base configuration is loaded
# even if a user has overwritten some of these definitions the technical import should be possible
# TODO load this configuration on first start of zukunft
# TODO add a check bottom for admin to reload the base configuration
function import_base_config(): string
{
    $result = '';

    $import_path = '../src/main/resources/';

    log_debug('load base config');

    $file_list = unserialize(BASE_CONFIG_FILES);
    foreach ($file_list as $filename) {
        ui_echo("load " . $filename);
        $result .= import_json_file($import_path . $filename);
    }

    log_debug('load base config ... done');

    return $result;
}
