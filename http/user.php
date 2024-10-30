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

use cfg\component\component;
use cfg\component\component_link;
use cfg\db\sql_db;
use cfg\formula;
use cfg\formula_link;
use cfg\triple;
use cfg\user;
use cfg\user_profile;
use cfg\value\value;
use cfg\view;
use cfg\word;
use controller\controller;
use html\html_base;
use html\view\view as view_dsp;
use shared\api;

$debug = $_GET['debug'] ?? 0;
const ROOT_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
include_once ROOT_PATH . 'src/main/php/zu_lib.php';

$db_con = prg_start("user");
$html = new html_base();

global $user_profiles;

$result = ''; // reset the html code var

// get the parameters
$id = $_GET[api::URL_VAR_ID];
$back = $_GET[controller::API_BACK];
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
$dsp_usr = $usr->dsp_obj();
$dsp_usr_old = $usr->dsp_user();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id() > 0) {
    log_debug($usr->id());

    $usr->load_usr_data();

    // prepare the display
    $msk = new view($usr);
    $msk->load_by_code_id(controller::MC_USER);

    // do user change
    $result .= $usr->upd_pars($_GET);

    // undo user changes for values
    if ($undo_val > 0) {
        $val = new value($usr);
        $val->set_id($undo_val);
        $val->del_usr_cfg();
    }

    // undo user changes for words
    if ($undo_wrd > 0) {
        $wrd = new word($usr);
        $wrd->set_id($undo_wrd);
        $wrd->del_usr_cfg();
    }

    // undo user changes for triples
    if ($undo_lnk > 0) {
        $lnk = new triple($usr);
        $lnk->set_id($undo_lnk);
        $lnk->del_usr_cfg();
    }

    // undo user changes for formulas
    if ($undo_frm > 0) {
        $frm = new formula($usr);
        $frm->set_id($undo_frm);
        $frm->del_usr_cfg();
    }

    // undo user changes for formula word links
    if ($undo_frm_lnk > 0) {
        $frm_lnk = new formula_link($usr);
        $frm_lnk->set_id($undo_frm_lnk);
        $frm_lnk->del_usr_cfg();
    }

    // undo user changes for formulas
    if ($undo_dsp > 0) {
        $msk = new view($usr);
        $msk->set_id($undo_dsp);
        $msk->del_usr_cfg();
    }

    // undo user changes for formulas
    if ($undo_cmp > 0) {
        $cmp = new component($usr);
        $cmp->set_id($undo_cmp);
        $cmp->del_usr_cfg();
    }

    // undo user changes for formulas
    if ($undo_cmp_lnk > 0) {
        $cmp_lnk = new component_link($usr);
        $cmp_lnk->set_id($undo_cmp_lnk);
        $cmp_lnk->del_usr_cfg();
    }

    $msk_dsp = new view_dsp($msk->api_json());
    $result .= $msk_dsp->dsp_navbar($back);
    $result .= $dsp_usr->form_edit($back);

    // allow to import data
    if ($usr->can_import()) {
        $result .= $html->dsp_text_h2('<br>Data import<br>');
        $result .= $html->dsp_text_h3('<br>Import <a href="/http/import.php">JSON</a><br>');
        $result .= $html->dsp_text_h3('<br>');
    }

    // allow admins to test the system consistence
    if ($usr->is_admin()) {
        $result .= $html->dsp_text_h2('<br>System testing<br>');
        $result .= $html->dsp_text_h3('<br>Perform all unit <a href="/test/test.php">tests</a><br>');
        $result .= $html->dsp_text_h3('<br>Perform critical unit and integration <a href="/test/test_quick.php">tests</a><br>');
        $result .= $html->dsp_text_h3('<br>Force <a href="/test/test_base_config.php">reloading</a> the base configuration e.g. to check that the units definition are still OK.<br>');
        $result .= $html->dsp_text_h3('<br>');
    }

    // display the user sandbox if there is something in
    $sandbox = $dsp_usr_old->dsp_sandbox($back);
    if (trim($sandbox) <> "") {
        $result .= $html->dsp_text_h2("Your changes, which are not standard");
        $result .= $sandbox;
        $result .= $html->dsp_text_h3('<br>');
    }

    // display the user changes 
    $changes = $dsp_usr_old->dsp_changes(0, sql_db::ROW_LIMIT, 1, $back);
    if (trim($changes) <> "") {
        $result .= $html->dsp_text_h2("Your latest changes");
        $result .= $changes;
        $result .= $html->dsp_text_h3('<br>');
    }

    // display the program issues that the user has found if there are some
    $errors = $dsp_usr_old->dsp_errors("", sql_db::ROW_LIMIT, 1, $back);
    if (trim($errors) <> "") {
        $result .= $html->dsp_text_h2("Program issues that you found, that have not yet been solved.");
        $result .= $errors;
        $result .= $html->dsp_text_h3('<br>');
    }

    // display all program issues if the user is an admin
    if ($usr->profile_id == $user_profiles->id(user_profile::ADMIN)) {
        $errors_all = $dsp_usr_old->dsp_errors("other", sql_db::ROW_LIMIT, 1, $back);
        if (trim($errors_all) <> "") {
            $result .= $html->dsp_text_h2("Program issues that other user have found, that have not yet been solved.");
            $result .= $errors_all;
            $result .= $html->dsp_text_h3('<br>');
        }
    }

    if ($_SESSION['logged']) {
        $result .= '<br><br><a href="/http/logout.php">logout</a>';
    }
}

$result .= '<br><br>';
$result .= \html\btn_back($back);

echo $result;

// Closing connection
prg_end($db_con);
