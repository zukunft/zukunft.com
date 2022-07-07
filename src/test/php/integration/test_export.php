<?php

/*

  test_export.php - TESTing of the EXPORT functions
  ---------------
  

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

function run_export_test(testing $t)
{

    global $usr;

    $t->header('Test the xml export class (classes/xml.php)');

    $phr_lst = new phrase_list($usr);
    $phr_lst->load_by_names(array(word::TN_READ));
    $xml_export = new xml_io;
    $xml_export->usr = $usr;
    $xml_export->phr_lst = $phr_lst;
    $result = $xml_export->export();
    $target = 'Mathematical constant';
    $t->dsp_contains(', xml->export for ' . $phr_lst->dsp_id() . ' contains at least ' . $target, $target, $result, TIMEOUT_LIMIT_PAGE);

    $t->header('Test the json export class (classes/json.php)');

    $json_export = new json_io;
    $json_export->usr = $usr;
    $json_export->phr_lst = $phr_lst;
    $result = $json_export->export();
    $target = 'Mathematical constant';
    $t->dsp_contains(', json->export for ' . $phr_lst->dsp_id() . ' contains at least ' . $target, $target, $result, TIMEOUT_LIMIT_PAGE);

}
