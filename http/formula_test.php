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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2021 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

$debug = $_GET['debug'] ?? 0;
include_once '../src/main/php/zu_lib.php';

// open database
$db_con = prg_start("start formula_test.php");

// load the session user parameters
$session_usr = new user;
$result = $session_usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($session_usr->id > 0) {

    load_usr_data();

    // show the header even if all parameters are wrong
    $dsp = new view_dsp;
    $dsp->usr = $session_usr;
    $dsp->id = cl(db_cl::VIEW, view::FORMULA_TEST);
    $back = $_GET['back']; // the page (or phrase id) from which formula testing has been called
    echo $dsp->dsp_navbar($back);

    // get all parameters
    $frm_id = $_GET['id'];
    $phr_ids_txt = $_GET['phrases'];
    $usr_id = $_GET['user'];    // to force another user view for testing the formula calculation
    $refresh = $_GET['refresh']; // delete all results for this formula and calculate the results again

    // load the user for whom the formula results should be refreshed
    if ($usr_id <= 0) {
        //$usr_id = TEST_USER_ID;
        $usr = $session_usr;
    } else {
        $usr = new user;
        $usr->id = $usr_id;
        $usr->get();
    }

    if ($frm_id == '') {
        echo dsp_text_h2("Please select a formula");
        echo "<br>";
    } else {

        // if the user clicks on "more details" the debug level is increased
        $debug_next_level = $debug + 1;

        // load the formulas to calculate
        $frm_lst = new formula_list;
        $frm_lst->ids = explode(",", $frm_id);
        $frm_lst->usr = $usr;
        $frm_lst->load();

        // display the first formula name as a sample
        $frm1 = $frm_lst->lst[0]; // just as a sample to display some info to the user

        // delete all formula results if requested
        if ($refresh == 1) {
            log_debug('refresh all formula results for ' . $frm1->id);
            $frm1->fv_del();
            log_debug('old formula results for ' . $frm_id . ' deleted');
        }

        // if only one result is selected, display the selected result words
        $dsp_lst = "";
        if ($phr_ids_txt <> "") {
            $phr_ids = explode(",", $phr_ids_txt);
            $phr_ids = zu_ids_not_empty($phr_ids);
            if (!empty($phr_ids)) {
                $phr_lst = new phrase_list($usr);
                $phr_lst->load_by_ids($phr_ids);
                $dsp_lst = "for " . $phr_lst->name_linked() . " ";
            }
        }
        dsp_text_h2('Calculate the ' . $frm1->name_linked($back) . ' ' . $dsp_lst);
        echo '<br>';

        // if a single calculation is selected by the user, show only this
        if (!empty($phr_ids)) {

            foreach ($frm_lst->lst as $frm) {
                log_debug('calculate "' . $frm->dsp_text() . '" for ' . $phr_lst->name_linked());
                $fv_lst = $frm->calc($phr_lst);

                // display the single result if requested
                if (!empty($fv_lst)) {
                    $fv = $fv_lst[0];
                    if ($debug > 0) {
                        if (is_null($fv->phr_lst) > 0) {
                            $debug_text = '' . $frm->name_linked() . ' for ';
                        } else {
                            $debug_text = '' . $frm->name_linked() . ' for ' . $fv->phr_lst->name_linked();
                        }
                        $debug_text .= ' = ' . $fv->display_linked($back) . ' (<a href="/http/formula_test.php?id=' . $frm_id . '&phrases=' . $phr_ids_txt . '&user=' . $usr->id . '&back=' . $back . '&debug=' . $debug_next_level . '">more details</a>)';
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
            $calc_fv_lst = new formula_value_list;
            $calc_fv_lst->usr = $usr;
            foreach ($frm_lst->lst as $frm) {
                $calc_fv_lst->frm = $frm;
                $calc_lst = $calc_fv_lst->frm_upd_lst($usr, $back);
            }

            log_debug("calculate queue is build (number of values to test: " . dsp_count($calc_lst->lst) . ")");

            // execute the queue
            foreach ($calc_lst->lst as $r) {
                log_debug('calculate "' . $r->frm->name . '" for ' . $r->phr_lst->name());
                if ($phr_ids_txt == "" or $phr_ids == $r->phr_lst->ids) {

                    // calculate one formula result
                    $frm = clone $r->frm;
                    $fv_lst = $frm->calc($r->phr_lst);

                    if (!empty($fv_lst)) {
                        // display the single result if requested
                        if ($debug > 3) {
                            foreach ($fv_lst as $fv) {
                                if ($fv->is_updated) {
                                    //$debug_text  = ''.$r->frm->name.' for '.$r->phr_lst->name_linked();
                                    $debug_text = '' . $r->frm->name . ' for ' . $r->phr_lst->name_linked();
                                    $debug_text .= ' = ' . $fv->display_linked($back) . ' (<a href="/http/formula_test.php?id=' . $frm_id;
                                    if (implode(",", $r->phr_lst->ids) <> "") {
                                        $debug_text .= '&phrases=' . implode(",", $r->phr_lst->ids);
                                    }
                                    $debug_text .= '&user=' . $usr->id . '&back=' . $back . '&debug=' . $debug_next_level . '">more details for this result</a>)';
                                    log_debug($debug_text);
                                } else {
                                    log_debug("Skipped " . $debug_text);
                                }
                            }
                        } else {
                            $fv = $fv_lst[0];
                            if ($fv->is_updated) {
                                //$debug_text  = ''.$r->frm->name.' for '.$r->phr_lst->name_linked();
                                $debug_text = '' . $r->frm->name . ' for ' . $fv->src_phr_lst->name_linked();
                                $debug_text .= ' = ' . $fv->display_linked($back) . ' (<a href="/http/formula_test.php?id=' . $frm_id;
                                if (implode(",", $r->phr_lst->ids) <> "") {
                                    $debug_text .= '&phrases=' . implode(",", $r->phr_lst->ids);
                                }
                                $debug_text .= '&user=' . $usr->id . '&back=' . $back . '&debug=' . $debug_next_level . '">more details only for this result</a>)';
                                log_debug($debug_text);
                            }
                        }

                        // show the user the progress every two seconds
                        if ($last_msg_time + UI_MIN_RESPONSE_TIME < time()) {
                            $calc_pct = ($calc_pos / sizeof($calc_lst->lst)) * 100;
                            if ($fv->is_updated) {
                                echo "" . round($calc_pct, 2) . "% processed (calculate " . $r->frm->name_linked($back) . " for " . $r->phr_lst->name_linked() . " = " . $fv->display_linked($back) . ")<br>";
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
        $call_next_level .= '&user=' . $usr->id . '&back=' . $back . '&debug=' . $debug_next_level . '">more details</a>';
        echo $call_next_level . ")<br>";

    }


}

// Closing connection
prg_end($db_con);
?>
