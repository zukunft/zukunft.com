<?php

/*

  formula_test.php - to debug the formula results
  ----------------

  
  to do
  -----
  
  always create the default result first meas for user 0
  calculate the result for a single user only if a dependency differs
  calculate only if really needed, means if one of the dependencies has been updated


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
include_once PHP_PATH . 'zu_lib.php';

include_once SHARED_PATH . 'views.php';

use cfg\formula_list;
use cfg\phr_ids;
use cfg\phrase_list;
use cfg\result_list;
use cfg\user;
use cfg\view;
use html\html_base;
use html\view\view as view_dsp;
use shared\api;
use shared\library;
use shared\views as view_shared;

// open database
$db_con = prg_start("start formula_test.php");
$html = new html_base();

global $system_views;

// load the session user parameters
$session_usr = new user;
$result = $session_usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($session_usr->id() > 0) {

    $session_usr->load_usr_data();
    $lib = new library();

    // show the header even if all parameters are wrong
    $msk = new view($session_usr);
    $msk->set_id($system_views->id(view_shared::MC_FORMULA_TEST));
    $back = $_GET[api::URL_VAR_BACK] = ''; // the page (or phrase id) from which formula testing has been called
    $msk_dsp = new view_dsp($msk->api_json());
    echo $msk_dsp->dsp_navbar($back);

    // get all parameters
    $frm_id = $_GET[api::URL_VAR_ID];
    $phr_ids_txt = $_GET['phrases'];
    $usr_id = $_GET['user'];    // to force another user view for testing the formula calculation
    $refresh = $_GET['refresh']; // delete all results for this formula and calculate the results again

    // load the user for whom the formula results should be refreshed
    if ($usr_id <= 0) {
        //$usr_id = TEST_USER_ID;
        $usr = $session_usr;
    } else {
        $usr = new user;
        $usr->set_id($usr_id);
        $usr->get();
    }

    if ($frm_id == '') {
        echo $html->dsp_text_h2("Please select a formula");
        echo "<br>";
    } else {

        // if the user clicks on "more details" the debug level is increased
        $debug_next_level = $debug + 1;

        // load the formulas to calculate
        $frm_lst = new formula_list($usr);
        $frm_lst->load_by_ids(explode(",", $frm_id));

        // display the first formula name as a sample
        $frm1 = $frm_lst->lst()[0]; // just as a sample to display some info to the user

        // delete all formula results if requested
        if ($refresh == 1) {
            log_debug('refresh all formula results for ' . $frm1->id);
            $frm1->res_del();
            log_debug('old formula results for ' . $frm_id . ' deleted');
        }

        // if only one result is selected, display the selected result words
        $phr_lst = new phrase_list($usr);
        $dsp_lst = "";
        if ($phr_ids_txt <> "") {
            $phr_ids = explode(",", $phr_ids_txt);
            $phr_ids = $lib->ids_not_empty($phr_ids);
            if (!empty($phr_ids)) {
                $phr_lst->load_names_by_ids(new phr_ids($phr_ids));
                $dsp_lst = "for " . $phr_lst->name_linked() . " ";
            }
        }
        $html->dsp_text_h2('Calculate the ' . $frm1->name_linked($back) . ' ' . $dsp_lst);
        echo '<br>';

        // if a single calculation is selected by the user, show only this
        if (!empty($phr_ids)) {

            foreach ($frm_lst->lst() as $frm) {
                log_debug('calculate "' . $frm->dsp_text() . '" for ' . $phr_lst->name_linked());
                $res_lst = $frm->calc($phr_lst);

                // display the single result if requested
                if (!empty($res_lst)) {
                    $res = $res_lst[0];
                    if ($debug > 0) {
                        if (is_null($res->grp->phr_lst) > 0) {
                            $debug_text = '' . $frm->name_linked() . ' for ';
                        } else {
                            $debug_text = '' . $frm->name_linked() . ' for ' . $res->grp->phr_lst->name_linked();
                        }
                        $debug_text .= ' = ' . $res->display_linked($back) . ' (<a href="/http/formula_test.php?id=' . $frm_id . '&phrases=' . $phr_ids_txt . '&user=' . $usr->id() . '&back=' . $back . '&debug=' . $debug_next_level . '">more details</a>)';
                        log_debug($debug_text);
                    }
                }
            }

        } else {

            // ... otherwise calculate all results for the formulas
            // start displaying while calculating
            ob_implicit_flush(true);
            ob_end_flush();
            log_debug("create the calculation queue ... ");
            $calc_pos = 0;
            $last_msg_time = time();

            // build the calculation queue
            // the standard value will always be checked first
            // and after that the user specific value will be calculated if needed
            // TODO: but only if the user has done some changes
            $calc_res_lst = new result_list($usr);
            foreach ($frm_lst->lst() as $frm) {
                $calc_lst = $calc_res_lst->frm_upd_lst($frm, $back);
            }

            log_debug("calculate queue is build (number of values to test: " . $lib->dsp_count($calc_lst->lst()) . ")");

            // execute the queue
            foreach ($calc_lst->lst() as $r) {
                log_debug('calculate "' . $r->frm->name . '" for ' . $r->phr_lst->name());
                if ($phr_ids_txt == "" or $phr_ids == $r->phr_lst->ids) {

                    // calculate one formula result
                    $frm = clone $r->frm;
                    $res_lst = $frm->calc($r->phr_lst);

                    if (!empty($res_lst)) {
                        // display the single result if requested
                        if ($debug > 3) {
                            foreach ($res_lst as $res) {
                                if ($res->is_updated) {
                                    //$debug_text  = ''.$r->frm->name.' for '.$r->phr_lst->name_linked();
                                    $debug_text = '' . $r->frm->name . ' for ' . $r->phr_lst->name_linked();
                                    $debug_text .= ' = ' . $res->display_linked($back) . ' (<a href="/http/formula_test.php?id=' . $frm_id;
                                    if (implode(",", $r->phr_lst->ids) <> "") {
                                        $debug_text .= '&phrases=' . implode(",", $r->phr_lst->ids);
                                    }
                                    $debug_text .= '&user=' . $usr->id() . '&back=' . $back . '&debug=' . $debug_next_level . '">more details for this result</a>)';
                                    log_debug($debug_text);
                                } else {
                                    log_debug("Skipped " . $debug_text);
                                }
                            }
                        } else {
                            $res = $res_lst[0];
                            if ($res->is_updated) {
                                //$debug_text  = ''.$r->frm->name.' for '.$r->phr_lst->name_linked();
                                $debug_text = '' . $r->frm->name . ' for ' . $res->src_phr_lst->name_linked();
                                $debug_text .= ' = ' . $res->display_linked($back) . ' (<a href="/http/formula_test.php?id=' . $frm_id;
                                if (implode(",", $r->phr_lst->ids) <> "") {
                                    $debug_text .= '&phrases=' . implode(",", $r->phr_lst->ids);
                                }
                                $debug_text .= '&user=' . $usr->id() . '&back=' . $back . '&debug=' . $debug_next_level . '">more details only for this result</a>)';
                                log_debug($debug_text);
                            }
                        }

                        // show the user the progress every two seconds
                        if ($last_msg_time + UI_MIN_RESPONSE_TIME < time()) {
                            $calc_pct = ($calc_pos / sizeof($calc_lst->lst())) * 100;
                            if ($res->is_updated) {
                                echo "" . round($calc_pct, 2) . "% processed (calculate " . $r->frm->name_linked($back) . " for " . $r->phr_lst->name_linked() . " = " . $res->display_linked($back) . ")<br>";
                            } else {
                                echo "" . round($calc_pct, 2) . "% processed (check " . $r->frm->name_linked($back) . " for " . $r->phr_lst->name_linked() . ")<br>";
                            }
                            ob_flush();
                            flush();
                            $last_msg_time = time();
                        }
                    }
                }
                $calc_pos++;
            }
            ob_end_flush();
        }

        // display the finish message
        echo "<br>";
        echo "calculation finished (display detail level " . $debug . "";
        $call_next_level = ', <a href="/http/formula_test.php?id=' . $frm_id;
        if ($phr_ids_txt <> "") {
            $call_next_level .= '&phrases=' . $phr_ids_txt;
        }
        $call_next_level .= '&user=' . $usr->id() . '&back=' . $back . '&debug=' . $debug_next_level . '">more details</a>';
        echo $call_next_level . ")<br>";

    }


}

// Closing connection
prg_end($db_con);