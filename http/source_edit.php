<?php

/*

  source_edit.php - rename and adjust a source
  ---------------
  
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

// standard zukunft header for callable php files to allow debugging and lib loading
$debug = $_GET['debug'] ?? 0;
const ROOT_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
const PHP_PATH = ROOT_PATH . 'src' . DIRECTORY_SEPARATOR . 'main' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR;
include_once PHP_PATH . 'init.php';

use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\web\frontend;
use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\cfg\ref\source;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\view\view;
use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\ref\source as source_ui;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\web\view\view as view_ui;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\url_var;

include_once paths::SHARED_CONST . 'views.php';

// open database
$app = new frontend();
global $sys, $cac, $cfg;
$db_con = $app->start($sys, "source_edit", $cac, $cfg);

global $sys;

$result = ''; // reset the html code var
$usr_msg = new user_message(); // to collect all messages that should be shown to the user immediately

// load the session user parameters
$usr = new user;
$result .= $usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id() > 0) {

    $html = new \Zukunft\ZukunftCom\main\php\web\html\html_base();

    $usr->load_usr_data();

    // prepare the display
    $msk = new view($usr);
    $msk->load_by_id($sys->msk_cac->id(views::SOURCE_EDIT));
    $lib = new library();
    $back = $lib->filter_var($_GET[url_var::BACK]); // the original calling page that should be shown after the change if finished

    // create the source object to have an place to update the parameters
    $src = new source($usr);
    $src->load_by_id($_GET[url_var::ID]);

    if ($src->id() <= 0) {
        $result .= log_err("No source found to change because the id is missing.", "source_edit.php");
    } else {

        // if the save button has been pressed at least the name is filled (an empty name should never be saved; instead the word should be deleted)
        if ($_GET[url_var::NAME] <> '') {

            // get the parameters (but if not set, use the database value)
            if (isset($_GET[url_var::NAME])) {
                $src->set_name($_GET[url_var::NAME]);
            }
            if (isset($_GET[url_var::URL])) {
                $src->url = $_GET[url_var::URL];
            }
            if (isset($_GET[url_var::DESCRIPTION])) {
                $src->description = $_GET[url_var::DESCRIPTION];
            }

            // save the changes
            $upd_result = $src->save($usr_msg);

            // if update was successful ...
            if ($usr_msg->is_ok()) {
                // remember the source for the next values to add
                $usr->src = $src;
                $usr->save($usr_msg);

                // ... and display the calling view
                $result .= $html->dsp_go_back($back, $usr);
            }

        }

        // if nothing yet done display the add view (and any message on the top)
        if ($result == '') {
            // show the header
            $msk_dsp = new view_ui($msk->api_json());
            $dto = new data_object();
            $result .= $msk_dsp->dsp_navbar($dto, $back);
            $result .= $html->dsp_err($usr_msg->all_message_text());

            // show the source and its relations, so that the user can change it
            $scr_dsp = new source_ui($src->api_json());
            //$result .= $scr_dsp->dsp_edit($back);
        }
    }
}

echo $result;

$app->end($sys, $db_con);
