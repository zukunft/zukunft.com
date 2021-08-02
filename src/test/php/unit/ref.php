<?php

/*

  test/unit/ref.php - unit testing of the reference functions
  -----------------
  

zukunft.com - calc with words

copyright 1995-2021 by zukunft.com AG, Blumentalstrasse 15, 8707 Uetikon am See, Switzerland

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

function run_ref_unit_tests()
{

    test_header('Unit tests of the Ref class (src/main/php/model/ref/ref.php)');


    test_subheader('Im- and Export tests');

    $dsp_json = '{
          "name": "Second",
          "type": "wikipedia"
        }';
    $json_import_array = json_decode($dsp_json, true);
    $ref = new ref;
    $ref->import_obj($json_import_array, false);
    $json_export_string = json_encode($ref->export_obj(false));
    $result = json_decode($dsp_json) == json_decode($json_export_string);
    $target = true;
    test_dsp('word->import check name', $target, $result);

}

