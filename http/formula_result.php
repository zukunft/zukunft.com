<?php

/*

  formula_result.php - explains one formula result
  ------------------
  
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

// for callable php files the standard zukunft.com header to load all classes and allow debugging
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
use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\result\result;
use Zukunft\ZukunftCom\main\php\web\view\view as view_ui;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\shared\const\views;

$app = new frontend();
global $sys;
$db_con = $app->start("formula_result");

global $sys;

$result = ''; // reset the html code var

// load the session user parameters
$session_usr = new user;
$result .= $session_usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($session_usr->id() > 0) {

    $session_usr->load_usr_data();

    // show the header
    $msk = new view($session_usr);
    $msk->id = $sys->msk_cac->id(views::FORMULA_EXPLAIN);
    $lib = new library();
    $back = $lib->filter_var($_GET[url_var::BACK]); // the page (or phrase id) from which formula testing has been called
    $msk_dsp = new view_ui($msk->api_json());
    $dto = new data_object();
    $result .= $msk_dsp->dsp_navbar($dto, $back);

    // get the parameters
    $frm_val_id = $_GET[url_var::ID];      // id of the formula result if known already
    $frm_id = $_GET['formula']; // id of the formula which values should be explained
    $phr_id = $_GET['word'];    // id of the leading word used to order the result explaining
    //$wrd_group_id = $_GET['group'];   // id of the word group (excluding and time word)
    $time_id = $_GET['time'];    // id of the time word for which the value is valid (always the end of the period e.g. a value for 2016 is valid at the end of the year)

    // explain the result
    if ($frm_val_id > 0 or $frm_id > 0) {
        $res = new result();
        $res->load_by_id($frm_val_id);
        if ($res->id() > 0) {
            $result .= $res->explain($phr_id, $back);
        } else {
            $result .= log_err("Formula result with id " . $frm_val_id . ' not found.', "formula_result.php");
        }
        log_debug('formula_result.php explained (id' . $res->id() . ' for user ' . $session_usr->name . ')');
    } else {
        // ... or complain about a wrong call
        $url_txt = "";
        foreach ($_GET as $key => $value) {
            $url_txt .= $key . '=' . $value . ',';
        }
        $result .= log_err("Wrong parameters: " . $url_txt, "formula_result.php");
    }
}

echo $result;

$app->end($db_con);