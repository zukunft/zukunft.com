<?php

/*

    test/unit/export.php - unit testing of the export functions
    --------------------
  

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

    Copyright (c) 1995-2024 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace unit_read;

use cfg\export\xml;
use im_export\json_io;
use test\test_cleanup;
use const test\TIMEOUT_LIMIT_PAGE;

class export_tests
{
    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $t->name = 'export->';

        $t->header('Unit tests of export');

        $t->subheader('Test the xml export class (classes/xml.php)');

        $phr_lst = $t->dummy_phrase_list();
        $xml_exp = new xml($t->usr1);
        $result = $xml_exp->export_by_phrase_list($phr_lst);
        $target = 'Mathematics';
        $t->dsp_contains(', xml->export for ' . $phr_lst->dsp_id() . ' contains at least ' . $target, $target, $result, TIMEOUT_LIMIT_PAGE);

        $t->header('Test the json export class (classes/json.php)');

        $json_export = new json_io($usr, $phr_lst);
        $result = $json_export->export();
        $target = 'Mathematics';
        //$t->dsp_contains(', json->export for ' . $phr_lst->dsp_id() . ' contains at least ' . $target, $target, $result, TIMEOUT_LIMIT_PAGE);

    }

}