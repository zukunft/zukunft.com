<?php

/*

  test/unit/formula.php - unit testing of the formula functions
  ---------------------
  

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

function run_formula_unit_tests()
{

    global $usr;
    global $sql_names;

    test_header('Unit tests of the formula class (src/main/php/model/formula/formula.php)');


    test_subheader('SQL statement tests');

    $db_con = new sql_db();

    // sql to load the formula by id
    $frm = new formula;
    $frm->id = 2;
    $frm->usr = $usr;
    $db_con->db_type = DB_TYPE_POSTGRES;
    $created_sql = $frm->load_sql($db_con);
    $expected_sql = "SELECT 
                            s.formula_id,  
                            u.formula_id AS user_formula_id,  
                            s.user_id,  
                            CASE WHEN (u.formula_name <> '' IS NOT TRUE) THEN s.formula_name ELSE u.formula_name END AS formula_name,  
                            CASE WHEN (u.formula_text <> '' IS NOT TRUE) THEN s.formula_text ELSE u.formula_text END AS formula_text,  
                            CASE WHEN (u.resolved_text <> '' IS NOT TRUE) THEN s.resolved_text ELSE u.resolved_text END AS resolved_text,  
                            CASE WHEN (u.description <> '' IS NOT TRUE) THEN s.description ELSE u.description END AS description,  
                            CASE WHEN (u.formula_type_id IS NULL) THEN s.formula_type_id ELSE u.formula_type_id END AS formula_type_id,  
                            CASE WHEN (u.all_values_needed IS NULL) THEN s.all_values_needed ELSE u.all_values_needed END AS all_values_needed,  
                            CASE WHEN (u.last_update IS NULL) THEN s.last_update ELSE u.last_update END AS last_update,  
                            CASE WHEN (u.excluded IS NULL) THEN s.excluded ELSE u.excluded END AS excluded,  
                            CASE WHEN (c.code_id <> '' IS NOT TRUE) THEN l.code_id ELSE c.code_id END AS code_id 
                       FROM formulas s LEFT JOIN user_formulas u ON s.formula_id = u.formula_id 
                                                                AND u.user_id = 1 
                                       LEFT JOIN formula_types l ON s.formula_type_id = l.formula_type_id 
                                       LEFT JOIN formula_types c ON u.formula_type_id = c.formula_type_id 
                      WHERE s.formula_id = 2;";
    test_dsp('formula->load_sql by formula id', zu_trim($expected_sql), zu_trim($created_sql));

    // ... and check if the prepared sql name is unique
    $result = false;
    $sql_name = $frm->load_sql($db_con, true);
    if (!in_array($sql_name, $sql_names)) {
        $result = true;
        $sql_names[] = $sql_name;
    }
    $target = true;
    test_dsp('formula->load_sql by formula id check sql name', $result, $target);


    test_subheader('Im- and Export tests');

    $json_in = json_decode(file_get_contents(PATH_TEST_IMPORT_FILES . 'unit/formula/scale_second_to_minute.json'), true);
    $frm = new formula;
    $frm->import_obj($json_in, false);
    $json_ex = json_decode(json_encode($frm->export_obj(false)), true);
    $result = json_is_similar($json_in, $json_ex);
    $target = true;
    test_dsp('formula->import check name', $target, $result);

}

