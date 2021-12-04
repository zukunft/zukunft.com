<?php

/*

  test/unit/formula_link.php - unit testing of the formula link functions
  --------------------------
  

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

class formula_link_unit_tests
{
    function run(testing $t)
    {

        global $usr;
        global $sql_names;

        $t->header('Unit tests of the formula link class (src/main/php/model/formula/formula_link.php)');

        $t->subheader('SQL statement tests');

        $db_con = new sql_db();

        // sql to load the formula link by id
        $lnk = new formula_link();
        $lnk->id = 2;
        $lnk->usr = $usr;
        $db_con->db_type = sql_db::POSTGRES;
        $created_sql = $lnk->load_sql($db_con);
        $expected_sql = "SELECT 
                        s.formula_link_id,  
                        u.formula_link_id AS user_formula_link_id,  
                        s.user_id,  
                        s.formula_id,  
                        s.phrase_id,  
                        CASE WHEN (u.link_type_id IS NULL) THEN s.link_type_id ELSE u.link_type_id END AS link_type_id,  
                        CASE WHEN (u.excluded     IS NULL) THEN s.excluded     ELSE u.excluded     END AS excluded 
                   FROM formula_links s 
              LEFT JOIN user_formula_links u ON s.formula_link_id = u.formula_link_id 
                                            AND u.user_id = 1 
                  WHERE s.formula_link_id = 2;";
        $t->dsp('formula_link->load_sql by formula link id', $t->trim($expected_sql), $t->trim($created_sql));

        // ... and check if the prepared sql name is unique
        $result = false;
        $sql_name = $lnk->load_sql($db_con, true);
        if (!in_array($sql_name, $sql_names)) {
            $result = true;
            $sql_names[] = $sql_name;
        }
        $target = true;
        $t->dsp('formula_link->load_sql by formula link id check sql name', $result, $target);

        // ... and for MySQL
        $db_con->db_type = sql_db::MYSQL;
        $created_sql = $lnk->load_sql($db_con);
        $expected_sql = "SELECT " . " 
                        s.formula_link_id,  
                        u.formula_link_id AS user_formula_link_id,  
                        s.user_id,  
                        s.formula_id,  
                        s.phrase_id,  
                        IF(u.link_type_id IS NULL, s.link_type_id, u.link_type_id) AS link_type_id,          
                        IF(u.excluded     IS NULL, s.excluded,         u.excluded) AS excluded 
                   FROM formula_links s 
              LEFT JOIN user_formula_links u ON s.formula_link_id = u.formula_link_id 
                                            AND u.user_id = 1 
                  WHERE s.formula_link_id = 2;";
        $t->dsp('formula_link->load_sql for MySQL by formula link id', $t->trim($expected_sql), $t->trim($created_sql));

        // sql to load the standard formula link by id
        $db_con->db_type = sql_db::POSTGRES;
        $created_sql = $lnk->load_standard_sql($db_con);
        $expected_sql = "SELECT 
                        formula_link_id,  
                        formula_id,  
                        phrase_id,  
                        user_id,  
                        link_type_id,  
                        excluded 
                   FROM formula_links 
                  WHERE formula_link_id = 2;";
        $t->dsp('formula_link->load_standard_sql by formula link id', $t->trim($expected_sql), $t->trim($created_sql));

        // ... and check if the prepared sql name is unique
        $result = false;
        $sql_name = $lnk->load_standard_sql($db_con, true);
        if (!in_array($sql_name, $sql_names)) {
            $result = true;
            $sql_names[] = $sql_name;
        }
        $target = true;
        $t->dsp('formula_link->load_standard_sql by formula link id check sql name', $result, $target);

        // ... and for MySQL
        $db_con->db_type = sql_db::MYSQL;
        $created_sql = $lnk->load_standard_sql($db_con);
        $expected_sql = "SELECT " . " 
                        formula_link_id,  
                        formula_id,  
                        phrase_id,  
                        user_id,  
                        link_type_id,  
                        excluded 
                   FROM formula_links 
                  WHERE formula_link_id = 2;";
        $t->dsp('formula_link->load_standard_sql for MySQL by formula link id', $t->trim($expected_sql), $t->trim($created_sql));

        // sql to load the user formula link by id
        $db_con->db_type = sql_db::POSTGRES;
        $created_sql = $lnk->load_user_sql($db_con);
        $expected_sql = "SELECT formula_link_id,
                            link_type_id,
                            excluded
                       FROM user_formula_links
                      WHERE formula_link_id = 2 
                        AND user_id = 1;";
        $t->dsp('formula_link->load_user_sql by formula link id', $t->trim($expected_sql), $t->trim($created_sql));

        // sql to check if no one else has changed the formula link
        $lnk = new formula_link();
        $lnk->id = 2;
        $lnk->owner_id = 3;
        $db_con->db_type = sql_db::POSTGRES;
        $created_sql = $lnk->not_changed_sql();
        $expected_sql = "SELECT user_id 
                FROM user_formula_links 
               WHERE formula_link_id = 2 
                 AND user_id <> 3 
                 AND (excluded <> 1 OR excluded is NULL);";
        $t->dsp('formula_link->not_changed_sql by owner id', $t->trim($expected_sql), $t->trim($created_sql));

        // ... and check if the prepared sql name is unique
        $result = false;
        $sql_name = $lnk->not_changed_sql(true);
        if (!in_array($sql_name, $sql_names)) {
            $result = true;
            $sql_names[] = $sql_name;
        }
        $target = true;
        $t->dsp('formula_link->not_changed_sql by owner id check sql name', $result, $target);

        // MySQL check not needed, because it is the same as for PostgreSQL

        /*
        $t->subheader('Im- and Export tests');

        $json_in = json_decode(file_get_contents(PATH_TEST_IMPORT_FILES . 'unit/formula/scale_second_to_minute.json'), true);
        $lnk = new formula;
        $lnk->import_obj($json_in, false);
        $json_ex = json_decode(json_encode($lnk->export_obj(false)), true);
        $result = json_is_similar($json_in, $json_ex);
        $target = true;
        $t->dsp('formula_link->import check name', $target, $result);
        */

    }

}