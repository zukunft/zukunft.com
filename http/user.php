<?php

/*

  user.php - the main user page with the key settings of the user that is logged in
  --------


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
include_once '../src/main/php/zu_lib.php';

$db_con = prg_start("user");

$result = ''; // reset the html code var

// get the parameters
$id = $_GET['id'];
$back = $_GET['back'];
$undo_val = $_GET['undo_value'];
$undo_wrd = $_GET['undo_word'];
$undo_lnk = $_GET['undo_triple'];
$undo_frm = $_GET['undo_formula'];
$undo_frm_lnk = $_GET['undo_formula_link'];
$undo_dsp = $_GET['undo_view'];
$undo_cmp = $_GET['undo_component'];
$undo_cmp_lnk = $_GET['undo_view_link'];
$undo_src = $_GET['undo_source'];

// load the session user parameters
$usr = new user;
$result .= $usr->get();
$dsp_usr = $usr->dsp_user();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id > 0) {
    log_debug("user -> (" . $usr->id . ")");

    load_usr_data();

    // prepare the display
    $dsp = new view_dsp_old;
    $dsp->id = cl(db_cl::VIEW, view::USER);
    $dsp->usr = $usr;
    $dsp->load();

    // do user changes
    $result .= $usr->upd_pars($_GET);

    // undo user changes for values
    if ($undo_val > 0) {
        $val = new value($usr);
        $val->id = $undo_val;
        $val->del_usr_cfg();
    }

    // undo user changes for words
    if ($undo_wrd > 0) {
        $wrd = new word($usr);
        $wrd->id = $undo_wrd;
        $wrd->del_usr_cfg();
    }

    // undo user changes for triples
    if ($undo_lnk > 0) {
        $lnk = new word_link($usr);
        $lnk->id = $undo_lnk;
        $lnk->del_usr_cfg();
    }

    // undo user changes for formulas
    if ($undo_frm > 0) {
        $frm = new formula($usr);
        $frm->id = $undo_frm;
        $frm->del_usr_cfg();
    }

    // undo user changes for formula word links
    if ($undo_frm_lnk > 0) {
        $frm_lnk = new formula_link($usr);
        $frm_lnk->id = $undo_frm_lnk;
        $frm_lnk->del_usr_cfg();
    }

    // undo user changes for formulas
    if ($undo_dsp > 0) {
        $dsp = new view($usr);
        $dsp->id = $undo_dsp;
        $dsp->del_usr_cfg();
    }

    // undo user changes for formulas
    if ($undo_cmp > 0) {
        $cmp = new view_cmp($usr);
        $cmp->id = $undo_cmp;
        $cmp->del_usr_cfg();
    }

    // undo user changes for formulas
    if ($undo_cmp_lnk > 0) {
        $cmp_lnk = new view_cmp_link($usr);
        $cmp_lnk->id = $undo_cmp_lnk;
        $cmp_lnk->del_usr_cfg();
    }

    $result .= $dsp->dsp_navbar($back);
    $result .= $dsp_usr->dsp_edit($back);

    // allow to import data
    if ($usr->can_import()) {
        $result .= dsp_text_h2('<br>Data import<br>');
        $result .= dsp_text_h3('<br>Import <a href="/http/import.php">JSON</a><br>');
        $result .= dsp_text_h3('<br>');
    }

    // allow admins to test the system consistence
    if ($usr->is_admin()) {
        $result .= dsp_text_h2('<br>System testing<br>');
        $result .= dsp_text_h3('<br>Perform all unit <a href="/test/test.php">tests</a><br>');
        $result .= dsp_text_h3('<br>Perform critical unit and integration <a href="/test/test_quick.php">tests</a><br>');
        $result .= dsp_text_h3('<br>Force <a href="/test/test_base_config.php">reloading</a> the base configuration e.g. to check that the units definition are still OK.<br>');
        $result .= dsp_text_h3('<br>');
    }

    // display the user sandbox if there is something in
    $sandbox = $dsp_usr->dsp_sandbox($back);
    if (trim($sandbox) <> "") {
        $result .= dsp_text_h2("Your changes, which are not standard");
        $result .= $sandbox;
        $result .= dsp_text_h3('<br>');
    }

    // display the user changes 
    $changes = $dsp_usr->dsp_changes(0, SQL_ROW_LIMIT, 1, $back);
    if (trim($changes) <> "") {
        $result .= dsp_text_h2("Your latest changes");
        $result .= $changes;
        $result .= dsp_text_h3('<br>');
    }

    // display the program issues that the user has found if there are some
    $errors = $dsp_usr->dsp_errors("", SQL_ROW_LIMIT, 1, $back);
    if (trim($errors) <> "") {
        $result .= dsp_text_h2("Program issues that you found, that have not yet been solved.");
        $result .= $errors;
        $result .= dsp_text_h3('<br>');
    }

    // display all program issues if the user is an admin
    if ($usr->profile_id == cl(db_cl::USER_PROFILE, user_profile::ADMIN)) {
        $errors_all = $dsp_usr->dsp_errors("other", SQL_ROW_LIMIT, 1, $back);
        if (trim($errors_all) <> "") {
            $result .= dsp_text_h2("Program issues that other user have found, that have not yet been solved.");
            $result .= $errors_all;
            $result .= dsp_text_h3('<br>');
        }
    }

    if ($_SESSION['logged']) {
        $result .= '<br><br><a href="/http/logout.php">logout</a>';
    }
}

$result .= '<br><br>';
$result .= btn_back($back);

echo $result;

// Closing connection
prg_end($db_con);
