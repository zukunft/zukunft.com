<?php

/*

    docs.php - show a markdown document of /docs/ as html
    --------

    e.g. /http/docs.php?doc=faq shows docs/faq.md; only a file name of /docs/ without a path is
    accepted (see markdown::doc_path), so no other file can be read

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

    Copyright (c) 1995-2026 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

$start_time = microtime(true);

include_once 'const.php';

use Zukunft\ZukunftCom\main\php\shared\helper\Message;
use Zukunft\ZukunftCom\main\php\shared\url_var;

include_once WEB . 'frontend.php';

use Zukunft\ZukunftCom\main\php\web\frontend;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\html\markdown;

// the requested document, read once here and passed on as a parameter
$doc = (string)($_GET[url_var::DOC] ?? markdown::DOC_DEFAULT);

// open database
$app = new frontend();
// TODO Prio 0 use message in all frontend calls
$msg = new Message();
$db_con = $app->start("docs", $msg);

// a name that is no document of /docs/ is answered as not found
if (new markdown()->doc_path($doc) == '') {
    http_response_code(404);
}

// display the document
$html = new html_base();
echo $html->docs_page($doc, $msg);

// close the database and measure the script loading time
$app->end($db_con, $start_time);
