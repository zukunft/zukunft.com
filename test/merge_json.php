<?php

/*

    merge_json.php - merge two zukunft.com import json files into one without losing data
    --------------

    usage: php test/merge_json.php <first.json> <second.json> <merged.json>

    the objects of both files are mapped to a data object each (like on import),
    the second data object fills the first one (data_object::fill), so
    - an object that only the second file has is added
    - a matching object (same name resp. same value phrase group) of the second file
      only fills the vars that the first file has left unset
    - on a conflict the first file wins, like the first import file of a phrase
      stays the owner of the description (see docs/llm/json_structure.md)
    and the merged data object is written back to a json file (data_object::export_json)

    checks that only developers and local admin can start the merge


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

    Copyright (c) 1995-2026 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('This script may only be run from the command line.');
}

// read the command line arguments once at the top (see docs/llm/coding.md)
$file1 = $argv[1] ?? '';
$file2 = $argv[2] ?? '';
$file_out = $argv[3] ?? '';

include_once 'test_const.php';

// load the main test class to get the test environment
include_once TEST_PHP_PATH . 'test_app.php';

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\cfg\import\import;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\test\php\test_app;

include_once paths::MODEL_IMPORT . 'import.php';
include_once paths::SHARED . 'json_fields.php';

global $db_con;

// init main objects
$msg = new user_message();

// open database and display header
$app = new test_app();
$db_con = $app->start("merge json", $msg);

if ($db_con->is_open()) {

    // load the session user parameters
    $start_usr = new user;
    $result = $start_usr->get($msg);

    // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
    if ($start_usr->id() > 0) {
        if ($start_usr->is_admin()) {

            if ($file1 == '' or $file2 == '' or $file_out == '') {
                echo 'usage: php test/merge_json.php <first.json> <second.json> <merged.json>' . "\n";
            } elseif (!is_file($file1)) {
                echo 'first file "' . $file1 . '" not found' . "\n";
            } elseif (!is_file($file2)) {
                echo 'second file "' . $file2 . '" not found' . "\n";
            } elseif (is_file($file_out)) {
                // never overwrite silently: the merged file is reviewed by the developer first
                echo 'output file "' . $file_out . '" exists already' . "\n";
            } else {

                // read the two import files
                $json1 = json_decode(file_get_contents($file1), true);
                $json2 = json_decode(file_get_contents($file2), true);
                if ($json1 === null) {
                    echo 'first file "' . $file1 . '" is not valid json' . "\n";
                } elseif ($json2 === null) {
                    echo 'second file "' . $file2 . '" is not valid json' . "\n";
                } else {

                    // map each file to a data object like on import
                    $imp = new import($file1);
                    $imp->usr = $start_usr;
                    $dto1 = $imp->get_data_object($json1, $msg);
                    $imp = new import($file2);
                    $imp->usr = $start_usr;
                    $dto2 = $imp->get_data_object($json2, $msg);

                    // merge the second data object into the first (the first file wins on conflicts)
                    $msg->merge($dto1->fill($dto2, $start_usr));

                    // create the merged json with the headers of the input files (first file wins)
                    $json_out = $dto1->export_json($msg);
                    foreach ([json_fields::VERSION, json_fields::POD, json_fields::TIME,
                                 json_fields::SELECTION, json_fields::DESCRIPTION, json_fields::USER] as $hdr) {
                        if (key_exists($hdr, $json1)) {
                            $json_out[$hdr] = $json1[$hdr];
                        } elseif (key_exists($hdr, $json2)) {
                            $json_out[$hdr] = $json2[$hdr];
                        }
                    }

                    // write the merged file
                    $ok = file_put_contents($file_out, json_encode($json_out,
                            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n");
                    if ($ok === false) {
                        echo 'writing "' . $file_out . '" failed' . "\n";
                    } else {
                        echo 'merged "' . $file1 . '" and "' . $file2 . '" into "' . $file_out . '"' . "\n";
                    }

                    // report the merge issues to the developer
                    $msg_text = $msg->all_message_text();
                    if ($msg_text != '') {
                        echo $msg_text . "\n";
                    }
                }
            }

        } else {
            echo 'Only admin users are allowed to merge import files. Login as an admin.' . "\n";
        }
    }

    // Closing connection
    $app->end($db_con, false);

}
