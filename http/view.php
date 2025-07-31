<?php

/*

    view.php - create the HTML code to display a zukunft.com view
    --------

    - the view contains the overall formatting like page size
    - the view component links to words, values or formulas
    - a view component can be linked to a view or a view component define by the view_link_type

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
// to allow debugging of errors in the library that only appear on the server
$debug = $_GET['debug'] ?? 0;
// get the root path from the path of this file (relative path)
const ROOT_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;

// set the other path once for all scripts
const PHP_PATH = ROOT_PATH . 'src' . DIRECTORY_SEPARATOR . 'main' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR;
// load once the common const and vars used almost every time
include_once PHP_PATH . 'init.php';

use cfg\const\paths;
use html\const\paths as html_paths;

// load what is used here
include_once paths::WEB . 'frontend.php';
include_once paths::MODEL_SYSTEM . 'system_time_list.php';
include_once paths::MODEL_SYSTEM . 'system_time_type.php';
include_once paths::API_OBJECT . 'controller.php';
include_once html_paths::HELPER . 'config.php';
include_once html_paths::HTML . 'rest_ctrl.php';
include_once html_paths::VALUE . 'value.php';
include_once html_paths::VIEW . 'view.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_VALUE . 'value.php';
include_once paths::MODEL_VIEW . 'view.php';
include_once paths::MODEL_WORD . 'word.php';
include_once paths::SHARED_CONST . 'views.php';

use cfg\component\component;
use cfg\formula\formula;
use cfg\ref\ref;
use cfg\ref\source;
use cfg\result\result;
use cfg\user\user;
use cfg\value\value;
use cfg\verb\verb;
use cfg\view\view;
use cfg\word\triple;
use cfg\word\word;
use html\frontend;
use html\helper\config;
use html\html_base;
use html\rest_ctrl;
use html\types\type_lists;
use html\component\component_exe as component_dsp;
use html\formula\formula as formula_dsp;
use html\result\result as result_dsp;
use html\ref\ref as ref_dsp;
use html\ref\source as source_dsp;
use html\value\value as value_dsp;
use html\verb\verb as verb_dsp;
use html\view\view as view_dsp;
use html\word\triple as triple_dsp;
use html\word\word as word_dsp;
use shared\api;
use shared\const\views as view_shared;

// open database
$db_con = prg_start("view", '', false);

if ($db_con->is_open()) {

    // get the parameters
    $url_array = $_GET;

    /* only for local debugging
    echo '<br>';
    echo '<br>';
    echo '<br>';
    echo '<br>';
    echo '<br>';
    echo '<br>';
    echo implode(',',$url_array);
    echo '<br>';
    echo implode(',',array_keys($url_array));
    echo '<br>';
    echo '<br>';

    $url_values = explode(',', '2,1,1,1,new word 2,1,Mathematics is an area of knowledge that includes the topics of numbers and formulas,1,3');
    $url_keys = explode(',', 'm,id,back,confirm,name,phrase_type,description,share,protection');

    $url_array = [];
    $i = 0;
    foreach ($url_keys as $key){
        $url_array[$key] = $url_values[$i];
        $i++;
    }
    */

    $view = $url_array[api::URL_VAR_MASK] ?? 0; // the database id of the view to display
    $id = $url_array[api::URL_VAR_ID] ?? 0; // the database id of the prime object to display
    $confirm = $url_array[api::URL_VAR_CONFIRM_LONG] ?? 0; // the database id of the prime object to display

    $new_view_id = $url_array[rest_ctrl::PAR_VIEW_NEW_ID] ?? '';
    $view_words = $url_array[api::URL_VAR_WORDS] ?? '';
    $back = $url_array[api::URL_VAR_BACK] ?? ''; // the word id from which this value change has been called (maybe later any page)

    // init the view
    global $sys_msk_cac;
    $result = ''; // reset the html code var
    $msg = ''; // to collect all messages that should be shown to the user immediately

    // load the session user parameters
    $usr = new user;
    $result .= $usr->get();

    // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
    if ($usr->id() > 0) {

        // TODO move to the frontend __construct
        // get the fixed frontend config
        $main = new frontend('view');
        $api_msg = $main->api_get(type_lists::class);
        $frontend_cache = new type_lists($api_msg);

        // load the user changeable configuration once via api
        global $cfg;
        $cfg = new config();
        $cfg->load();

        $usr->load_usr_data();

        // use default view if nothing is set
        if (($view == 0 or $view == '' or $view == null or $view == 'null') and $id == 0) {
            $view = view_shared::START_ID;
        }

        // get the view id if the view code id is used
        if (is_numeric($view)) {
            $view_id = $view;
        } else {
            $view_id = $sys_msk_cac->id($view);
        }

        // select the main object to display
        if (in_array($view_id, view_shared::WORD_MASKS_IDS)) {
            $dbo_dsp = new word_dsp();
            $dbo = new word($usr);
        } elseif (in_array($view_id, view_shared::VERB_MASKS_IDS)) {
            $dbo_dsp = new verb_dsp();
            $dbo = new verb();
        } elseif (in_array($view_id, view_shared::TRIPLE_MASKS_IDS)) {
            $dbo_dsp = new triple_dsp();
            $dbo = new triple($usr);
        } elseif (in_array($view_id, view_shared::SOURCE_MASKS_IDS)) {
            $dbo_dsp = new source_dsp();
            $dbo = new source($usr);
        } elseif (in_array($view_id, view_shared::REF_MASKS_IDS)) {
            $dbo_dsp = new ref_dsp();
            $dbo = new ref($usr);
        } elseif (in_array($view_id, view_shared::VALUE_MASKS_IDS)) {
            $dbo_dsp = new value_dsp();
            $dbo = new value($usr);
        } elseif (in_array($view_id, view_shared::FORMULA_MASKS_IDS)) {
            $dbo_dsp = new formula_dsp();
            $dbo = new formula($usr);
        } elseif (in_array($view_id, view_shared::RESULT_MASKS_IDS)) {
            $dbo_dsp = new result_dsp();
            $dbo = new result($usr);
        } elseif (in_array($view_id, view_shared::VIEW_MASKS_IDS)) {
            $dbo_dsp = new view_dsp();
            $dbo = new view($usr);
        } elseif (in_array($view_id, view_shared::COMPONENT_MASKS_IDS)) {
            $dbo_dsp = new component_dsp();
            $dbo = new component($usr);
        } else {
            $dbo_dsp = new word_dsp();
            $dbo = new word($usr);
        }

        // save form action
        // if the save bottom has been pressed
        if ($confirm > 0) {
            $dbo_dsp->url_mapper($url_array);
            $dbo->api_mapper($dbo_dsp->api_array());

            // save the changes
            $upd_result = $dbo->save();

            // if update was fine ...
            if ($upd_result->is_ok()) {
                $id = $dbo->id();
                // ... display the calling page is switched off to keep the user on the edit view and see the implications of the change
                // switched off because maybe staying on the edit page is the expected behaviour
                if ($back == '' or $back == 0) {
                    $view_id = view_shared::START_ID;
                }
                //$result .= dsp_go_back($back, $usr);
            } else {
                // ... or in case of a problem prepare to show the message
                $msg .= $upd_result->get_last_message();
            }
        }


        // get the main object to display
        if ($id != 0) {
            $dbo_dsp->load_by_id($id);
        } else {
            // get last term used by the user or a default value
            $wrd = $usr->last_term();
        }

        // select the view
        if (in_array($view_id, view_shared::EDIT_DEL_MASKS_IDS)) {
            // TODO move as much a possible to backend functions
            if ($dbo_dsp->id() > 0) {
                // if the user has changed the view for this word, save it
                if ($new_view_id != '') {
                    $dbo_dsp->save_view($new_view_id);
                    $view_id = $new_view_id;
                } else {
                    // if the user has selected a special view, use it
                    if ($view_id == 0) {
                        // if the user has set a view for this word, use it
                        $view_id = $dbo_dsp->view_id();
                        if ($view_id <= 0) {
                            // if any user has set a view for this word, use the common view
                            $view_id = $dbo_dsp->calc_view_id();
                            if ($view_id <= 0) {
                                // if no one has set a view for this word, use the fallback view
                                $view_id = $sys_msk_cac->id(view_shared::WORD);
                            }
                        }
                    }
                }
            } else {
                $result .= log_err("No word selected.", "view.php", '',
                    (new Exception)->getTraceAsString(), $usr);
            }
        }

        // create a display object, select and load the view and display the word according to the view
        if ($view_id != 0) {
            // TODO first create the frontend object and call from the frontend object the api
            // TODO for system views avoid the backend call by using the cache from the frontend
            // TODO get the system view from the preloaded cache
            $msk_dsp = new view_dsp();
            $msk_dsp->load_by_id_with($view_id);
            $title = $msk_dsp->title($dbo_dsp);
            $dsp_text = $msk_dsp->show($dbo_dsp, null, $back);

            // use a fallback if the view is empty
            if ($dsp_text == '' or $msk_dsp->name() == '') {
                $view_id = $sys_msk_cac->id(view_shared::START);
                $msk_dsp->load_by_id_with($view_id);
                $dsp_text = $msk_dsp->name_tip($dbo_dsp, $back);
            }
            if ($dsp_text == '') {
                $result .= 'Please add a component to the view by clicking on Edit on the top right.';
            } else {
                $html = new html_base();
                $result .= $html->header($title, '');
                $result .= $dsp_text;
            }
        } else {
            $result .= log_err('No view for "' . $dbo_dsp->name() . '" found.',
                "view.php", '', (new Exception)->getTraceAsString(), $usr);
        }

    }

    echo $result;
    // close the database
    prg_end($db_con);
}