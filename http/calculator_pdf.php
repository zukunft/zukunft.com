<?php

/*

    calculator_pdf.php - the pdf of the definition of a calculator
    ------------------

    the PDF tab of the calculator editor (script/calculator_editor.js) posts the data of the page
    with its unsaved changes as json, which is the same json as the export of the editor, and gets
    the pdf as a download (calculator_definition_report); the pdf is created only from the posted
    data, so the request reads and changes nothing in the database, but it needs a logged-in user
    and the session token of the editor page, so that the pod creates no pdf for an unknown caller

    This file is part of zukunft.com - calc with words

    zukunft.com is free software: you can redistribute it and/or modify it
    under the terms of the GNU Affero General Public License as
    published by the Free Software Foundation, either version 3 of
    the License, or (at your option) any later version.
    zukunft.com is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/agpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2026 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

$start_time = microtime(true);

include_once 'const.php';

use Zukunft\ZukunftCom\main\php\shared\url_var;

include_once WEB . 'frontend.php';
include_once WEB . 'component/execute/calculator_definition_report.php';
include_once WEB . 'component/execute/calculator_report.php';
include_once WEB . 'html/pdf_writer.php';

use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\web\component\execute\calculator_definition_report;
use Zukunft\ZukunftCom\main\php\web\component\execute\calculator_report;
use Zukunft\ZukunftCom\main\php\web\frontend;
use Zukunft\ZukunftCom\main\php\web\helper\config;
use Zukunft\ZukunftCom\main\php\web\html\pdf_writer;
use Zukunft\ZukunftCom\main\php\web\user\user_message as user_message_ui;

global $mtr;
global $sys;
global $ui_sys;

// the largest data of a page that the pod turns into a pdf, so that a request cannot use the memory of the pod
const CALCULATOR_PDF_MAX_BYTES = 10 * 1024 * 1024;

// the request is read once here and passed on as parameters: the session token that the editor sends in the
// csrf header (api::CSRF_TOKEN_HEADER) and the posted json, of which at most one byte more than allowed is read
$url_arr = [url_var::SESSION_TOKEN => (string)($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '')];
$body = (string)file_get_contents('php://input', false, null, 0, CALCULATOR_PDF_MAX_BYTES + 1);

// open the session and the database for the user and the translations
$app = new frontend();
$msg_ui = new user_message_ui();
$db_con = $app->start('calculator pdf', $msg_ui, $url_arr);

$usr_id = 0;
if ($db_con->is_open()) {
    $usr = new user;
    $msg = new user_message();
    $usr->get($msg);
    $usr_id = $usr->id();
}
$doc = strlen($body) > CALCULATOR_PDF_MAX_BYTES ? null : json_decode($body, true);
if ($usr_id <= 0 or $url_arr[url_var::SESSION_TOKEN] == '' or !$app->session_token_valid) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo $mtr->txt(msg_id::CALCULATOR_DEFINITION_NO_LOGIN);
} elseif (!is_array($doc)) {
    http_response_code(400);
    header('Content-Type: text/plain; charset=utf-8');
    echo $mtr->txt(msg_id::CALCULATOR_DEFINITION_NO_JSON);
} else {
    // the types and the configuration of the user, with which the pdf shows the page like the calculator page
    $app->load_cache($msg_ui);
    $ui_sys = $app->dto;
    $ui_sys->cfg = new config();
    $ui_sys->cfg->load($sys, $msg_ui);
    $def_rpt = new calculator_definition_report();
    $pdf = $def_rpt->pdf($doc, new DateTime(), $msg_ui);
    header('Content-Type: ' . pdf_writer::MIME_TYPE);
    header(calculator_report::attachment_header($def_rpt->page_name($doc)));
    echo $pdf;
}

// close the database and measure the script loading time
$app->end($db_con, $start_time);
