<?php

/*

  import.php - select a file for importing into the zukunft.com database
  ----------


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

$debug = $_GET['debug'] ?? 0;
const ROOT_PATH = __DIR__ . '/../';
include_once ROOT_PATH . 'src/main/php/zu_lib.php';

// open database
$db_con = prg_start("import");

$result = ''; // reset the html code var
$msg = ''; // to collect all messages that should be shown to the user immediately

// load the session user parameters
$usr = new user;
$result .= $usr->get();
$back = $_GET['back'];     // the word id from which this value change has been called (maybe later any page)

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
log_debug('import.php check user ');
if ($usr->id() > 0) {

    load_usr_data();

    // prepare the display
    $dsp = new view_dsp_old($usr);
    $dsp->load_by_code_id(view::IMPORT);

    // get the filepath of the data that are supposed to be imported
    $fileName = $_FILES["fileToUpload"]["name"];
    if ($fileName == '') {
        $fileName = $_GET['filename'];
    }

    // if the user has confirmed the upload
    log_debug('import.php check submit ');
    //if ($_GET["confirm"] == 1) {
    if (isset($_POST["submit"])) {
        $uploadOk = True;
        if ($fileName <> '') {
            $msg .= 'Uploading of ' . $fileName;
        }
        $imageFileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Check file size if above 10 MB, which might take long
        if ($_FILES["fileToUpload"]["size"] > 11000000) {
            if ($msg == '') {
                $msg .= "Sorry, ";
            } else {
                $msg .= ", but ";
            }
            $msg .= "your file is larger than the limit of 10MB per file";
            $uploadOk = False;
        }
        if ($_FILES["fileToUpload"]["size"] <= 0) {
            if ($msg == '') {
                $msg .= "Sorry, ";
            } else {
                $msg .= " and ";
            }
            $msg .= "your file is empty";
            $uploadOk = False;
        }

        // Allow certain file formats
        if ($imageFileType != "json") {
            if ($msg == '') {
                $msg .= "Sorry, ";
            } else {
                $msg .= " and ";
            }
            $msg .= "only JSON files are allowed at the moment";
            $uploadOk = False;
        }

        log_debug('import.php check file ' . $fileName . ' done ');
        if ($uploadOk) {
            //checks for errors and checks that file is uploaded
            if ($_FILES['fileToUpload']['error'] == UPLOAD_ERR_OK
                && is_uploaded_file($_FILES['fileToUpload']['tmp_name'])) {
                $json_str = file_get_contents($_FILES['fileToUpload']['tmp_name']);
                $import = new file_import;
                $import->usr = $usr;
                $import->json_str = $json_str;
                $import_result = $import->put();
                if ($import_result->is_ok()) {
                    $msg .= ' done ('
                        . $import->words_done . ' words, '
                        . $import->verbs_done . ' verbs, '
                        . $import->triples_done . ' triples, '
                        . $import->formulas_done . ' formulas, '
                        . $import->sources_done . ' sources, '
                        . $import->refs_done . ' references, '
                        . $import->values_done . ' values, '
                        . $import->list_values_done . ' simple values, '
                        . $import->views_done . ' views loaded, '
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
            } else {
                if ($msg == '') {
                    $msg .= "Sorry, ";
                } else {
                    $msg .= " and ";
                }
                $msg .= "there was an error uploading your file with a size of " . $_FILES["fileToUpload"]["size"] . ' bytes';
            }
        }
    }
    if ($msg <> '') {
        $msg .= ".";
    }

    // if nothing yet done display the edit view (and any message on the top)
    if ($result == '') {
        log_debug('import.php display mask ');
        // show the value and the linked words to edit the value (again after removing or adding a word)
        $result .= $dsp->dsp_navbar($back);
        $result .= dsp_err($msg);

        $result .= dsp_form_file_select();
        // $result .= dsp_btn_text ('Start import', '/http/import.php?confirm=1&filepath='.);
        /*
        if ($fileName == '') {
          $result .= dsp_btn_text ('Start import', '/http/import.php?confirm=1');
        } else {
          $result .= dsp_btn_text ('Start import', '/http/import.php?confirm=1&filename='.$fileName);
        }
        */
    }
}

$result .= '<br><br>';
$result .= \html\btn_back($back);

echo $result;

// Closing connection
prg_end($db_con);