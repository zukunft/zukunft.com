<?php

/*

  value.php - display a value
  ---------
  
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

use Zukunft\ZukunftCom\main\php\web\frontend;
use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\view\view;
use Zukunft\ZukunftCom\main\php\cfg\word\word_list;
use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\value\value as value_ui;
use Zukunft\ZukunftCom\main\php\web\view\view as view_ui;
use Zukunft\ZukunftCom\main\php\web\word\word_list as word_list_ui;
use Zukunft\ZukunftCom\main\php\shared\const\views as view_shared;
use Zukunft\ZukunftCom\main\php\shared\url_var;

include_once paths::SHARED_CONST . 'views.php';

// open database
$app = new frontend();
$db_con = $app->start("value");

// get the parameters
$wrd_names = $_GET['t'];
log_debug("value for " . $wrd_names);

$result = ''; // reset the html code var

// load the session user parameters
$usr = new user;
$result .= $usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id > 0) {

    $usr->load_usr_data();

    // prepare the display
    $msk = new view($usr);
    $msk->load_by_code_id(view_shared::VALUE);
    $back = $_GET[url_var::BACK] = ''; // the page (or phrase id) from which formula testing has been called

    $msk_dsp = new view_ui($msk->api_json());
    $dto = new data_object();
    $result .= $msk_dsp->dsp_navbar($dto, $back);

    if ($wrd_names <> '') {

        // load the words
        $wrd_lst = new word_list($usr);
        $wrd_lst->load_by_names(explode(",", $wrd_names));

        $wrd_lst_dsp = new word_list_ui($wrd_lst->api_json());
        $result .= $wrd_lst_dsp->name_link();
        $result .= ' = ';
        $val = $wrd_lst->value();
        $val_dsp = new value_ui($val->api_json());
        $result .= $val_dsp->value_edit($back);
    }
}

echo $result;

$app->end($db_con);
