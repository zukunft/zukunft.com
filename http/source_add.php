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

// standard zukunft header for callable php files to allow debugging and lib loading
$debug = $_GET['debug'] ?? 0;
const ROOT_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
const PHP_PATH = ROOT_PATH . 'src' . DIRECTORY_SEPARATOR . 'main' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR;
include_once PHP_PATH . 'init.php';

use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\web\frontend;
use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::SHARED_CONST . 'views.php';

use Zukunft\ZukunftCom\main\php\cfg\ref\source;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\view\view;
use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\ref\source as source_ui;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\web\view\view as view_ui;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\shared\const\views;

/* open database */
$app = new frontend();
global $sys;
$db_con = $app->start("source_add");
$html = new html_base();

global $sys;

$result = ''; // reset the html code var
$usr_msg = new user_message(); // to collect all messages that should be shown to the user immediately

// load the session user parameters
$usr = new user;
echo $usr->get(); // if the usr identification fails, show any message immediately because this should never happen

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id() > 0) {

    $html = new html_base();

    $usr->load_usr_data();

    // prepare the display
    $msk = new view($usr);
    $msk->load_by_id($sys->msk_cac->id(views::SOURCE_ADD));
    $lib = new library();
    $back = $lib->filter_var($_GET[url_var::BACK]);      // the calling word which should be displayed after saving

    // create the object to store the parameters so that if the add form is shown again it is already filled
    $src = new source($usr);

    // load the parameters to the view object to display the user input again in case of an error
    if (isset($_GET[url_var::NAME])) {
        $src->set_name($_GET[url_var::NAME]);
    }    // name of the new source to add
    if (isset($_GET[url_var::URL])) {
        $src->url = $_GET[url_var::URL];
    }     // url of the new source to add
    if (isset($_GET[url_var::DESCRIPTION])) {
        $src->description = $_GET[url_var::DESCRIPTION];
    }

    // if the user has pressed save at least once
    if ($_GET['confirm'] > 0) {

        $msg = '';
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
                $src->save($usr_msg);

                // if adding was successful ...
                if ($usr_msg->is_ok()) {
                    // remember the source for the next values to add
                    $usr->src = $src;
                    $usr->save($usr_msg);

                    // ... and display the calling view
                    $result .= $html->dsp_go_back($back, $usr);
                }
            }
        }
    }

    // if nothing yet done display the add view (and any message on the top)
    if ($result == '') {
        // display the add view again
        $msk_dsp = new view_ui($msk->api_json());
        $dto = new data_object();
        $result .= $msk_dsp->dsp_navbar($dto, $back);
        $result .= $html->dsp_err($usr_msg->all_message_text());

        // display the add source view
        $scr_dsp = new source_ui($src->api_json());
        //$result .= $scr_dsp->dsp_edit($back);
    }
}

echo $result;

$app->end($db_con);
