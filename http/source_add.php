<?php

/*

  source_add.php - to add a new value source
  --------------

  
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

/* standard zukunft header for callable php files to allow debugging and lib loading */

use html\html_base;
use model\db_cl;
use model\source;
use model\user;
use model\view;

$debug = $_GET['debug'] ?? 0;
const ROOT_PATH = __DIR__ . '/../';
include_once ROOT_PATH . 'src/main/php/zu_lib.php';

/* open database */
$db_con = prg_start("source_add");
$html = new html_base();

$result = ''; // reset the html code var
$msg = ''; // to collect all messages that should be shown to the user immediately

// load the session user parameters
$usr = new user;
echo $usr->get(); // if the usr identification fails, show any message immediately because this should never happen

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id() > 0) {

    load_usr_data();

    // prepare the display
    $dsp = new view_dsp_old($usr);
    $dsp->load_by_id(cl(db_cl::VIEW, view::SOURCE_ADD));
    $back = $_GET['back'];      // the calling word which should be displayed after saving

    // create the object to store the parameters so that if the add form is shown again it is already filled
    $src = new source($usr);

    // load the parameters to the view object to display the user input again in case of an error
    if (isset($_GET['name'])) {
        $src->set_name($_GET['name']);
    }    // name of the new source to add
    if (isset($_GET['url'])) {
        $src->url = $_GET['url'];
    }     // url of the new source to add
    if (isset($_GET['comment'])) {
        $src->description = $_GET['comment'];
    }

    // if the user has pressed save at least once
    if ($_GET['confirm'] > 0) {

        // check essential parameters
        if ($src->name() == "") {
            $msg .= 'Name missing; Please press back and enter a source name.';
        } else {

            // check if source name already exists (move this part to the save function??)
            $db_src = new source($usr);
            $db_src->load_by_name($src->name());
            if ($db_src->id() > 0) {
                $msg .= 'Name ' . $src->name() . ' is already existing. Please enter another name or use the existing source.';
            }

            // if the parameters are fine
            if ($msg == '') {
                // add the new source to the database
                $add_result = $src->save();

                // if adding was successful ...
                if (str_replace('1', '', $add_result) == '') {
                    // remember the source for the next values to add
                    $usr->set_source($src->id());

                    // ... and display the calling view
                    $result .= dsp_go_back($back, $usr);
                } else {
                    // ... or in case of a problem prepare to show the message
                    $msg .= $add_result;
                }
            }
        }
    }

    // if nothing yet done display the add view (and any message on the top)
    if ($result == '') {
        // display the add view again
        $result .= $dsp->dsp_navbar($back);
        $result .= $html->dsp_err($msg);

        // display the add source view
        $result .= $src->dsp_obj()->dsp_edit($back);
    }
}

echo $result;

prg_end($db_con);
