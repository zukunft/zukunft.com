<?php

/*

  word_edit.php - adjust a word
  -------------
  
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

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::SHARED_CONST . 'views.php';

use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\web\frontend;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\view\view;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\web\view\view as view_ui;
use Zukunft\ZukunftCom\main\php\web\word\word as word_ui;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\shared\const\views;

// open database
$app = new frontend();
$db_con = $app->start("word_edit");
$html = new html_base();

$result = ''; // reset the html code var
$usr_msg = new user_message(); // to collect all messages that should be shown to the user immediately

// load the session user parameters
$usr = new user;
$result .= $usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id() > 0) {

    $usr->load_usr_data();

    // prepare the display
    $msk = new view($usr);
    $msk->load_by_code_id(views::WORD_EDIT);
    $lib = new library();
    $back = $lib->filter_var($_GET[url_var::BACK]); // the word id from which this value change has been called (maybe later any page)

    // create the word object to have a place to update the parameters
    $wrd = new word($usr);
    $wrd->load_by_id($_GET[url_var::ID]);

    if ($wrd->id() <= 0) {
        $result .= log_info("The word id must be set to display a word.", "word_edit.php", '', (new Exception)->getTraceAsString(), $usr);
    } else {

        // get all parameters (but if not set, use the database value)
        if (isset($_GET[url_var::NAME])) {
            $wrd->set_name($_GET[url_var::NAME]);
        } //
        if (isset($_GET[url_var::PLURAL])) {
            $wrd->plural = $_GET[url_var::PLURAL];
        } //
        if (isset($_GET[url_var::DESCRIPTION])) {
            $wrd->description = $_GET[url_var::DESCRIPTION];
        } //
        if (isset($_GET['type'])) {
            $wrd->type_id = $_GET['type'];
        }        // any functional code for special word is defined with the code_id of the word type

        // if the save bottom has been pressed
        if ($_GET['confirm'] > 0) {

            // an empty word name should never be saved; instead the word should be deleted)
            if ($wrd->name() == '') {
                $usr_msg->add_message_text('An empty name should never be saved. Please delete the word instead.');
            } else {
                // save the changes
                $wrd->save($usr_msg);
            }
        }

        // if nothing yet done display the edit view (and any message on the top)
        if ($result == '') {
            // show the header
            $msk_dsp = new view_ui($msk->api_json());
            $dto = new data_object();
            $result .= $msk_dsp->dsp_navbar($dto, $back);
            $result .= $html->dsp_err($usr_msg->all_message_text());

            // show the word and its relations, so that the user can change it
            $wrd_dsp = new word_ui();
            $wrd_dsp->set_from_json($wrd->api_json());
            $result .= $wrd_dsp->dsp_edit($back);
        }
    }
}

echo $result;

$app->end($db_con);
