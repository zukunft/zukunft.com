<?php

/*

    json_validation.php - check all import json files and write docs/json_findings.md
    -------------------

    checks every json file of src/main/resources/messages and src/test/resources/import
    against the json rules of docs/llm/json_structure.md that are already coded in
    coding_rule_tests, and lists the findings per file in docs/json_findings.md

    as the last check of a file without any other finding the json format version is compared
    with the program version and raised if the file is behind, and the initial data version is
    added if the file does not yet name one, so this script also writes to the json files

    the folders are scanned with a directory iterator, so a json file added later is
    checked without changing any code

    this entry point only bootstraps the path consts and hands over to the
    json_validation class, so that the checks can be unit tested without the bootstrap

    it also compares the renumbered change tables and fields with their unsorted baseline
    (see change_link_validation) and lists the rows that are missing, added or relinked

    usage: php test/json_validation.php


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

// only the path consts are needed, because the json check reads files and never the database
include_once 'test_const.php';

use Zukunft\ZukunftCom\test\php\const\paths as test_paths;
use Zukunft\ZukunftCom\test\php\const\files as test_files;
use Zukunft\ZukunftCom\test\php\utils\change_link_validation;
use Zukunft\ZukunftCom\test\php\utils\json_validation;

include_once test_paths::CONST . 'files.php';
include_once test_paths::UTILS . 'change_link_validation.php';
include_once test_paths::UTILS . 'json_validation.php';

// check the import json files, update the version fields of the files without any other
// finding and write the findings report
$chk = new json_validation();
$md_txt = $chk->md(true);
file_put_contents(test_files::DOCS_JSON_FINDINGS, $md_txt);

// repeat the summary line of the report on the command line
$sum_txt = '';
foreach (explode("\n", $md_txt) as $line) {
    if ($sum_txt == '' and str_contains($line, 'json files checked')) {
        $sum_txt = $line;
    }
}
echo $sum_txt . PHP_EOL;
echo 'written to ' . test_files::DOCS_JSON_FINDINGS . PHP_EOL;

// check that the renumbered change tables and fields still hold the same rows and table references
$lnk_chk = new change_link_validation();
$lnk_findings = $lnk_chk->check();
foreach ($lnk_findings as $finding) {
    echo $finding . PHP_EOL;
}
echo count($lnk_findings) . ' change table and field findings' . PHP_EOL;
exit(0);