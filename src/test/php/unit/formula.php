<?php

/*

  test/unit/formula.php - unit testing of the formula functions
  ---------------------
  

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

class formula_unit_tests
{
    function run(testing $t)
    {

        global $usr;

        // init
        $db_con = new sql_db();
        $t->name = 'formula->';
        $t->resource_path = 'db/formula/';
        $json_file = 'unit/formula/scale_second_to_minute.json';
        $usr->id = 1;

        $t->header('Unit tests of the formula class (src/main/php/model/formula/formula.php)');

        $t->subheader('SQL statement tests');

        // sql to load the formula by id
        $frm = new formula($usr);
        $frm->id = 2;
        $t->assert_load_sql($db_con, $frm);
        $t->assert_load_standard_sql($db_con, $frm);

        // sql to load the formula by name
        $frm = new formula($usr);
        $frm->name = formula::TF_SCALE_MIO;
        $t->assert_load_sql($db_con, $frm);
        $t->assert_load_standard_sql($db_con, $frm);


        $t->subheader('Im- and Export tests');

        $t->assert_json(new formula($usr), $json_file);

        $t->subheader('Expression tests');

        // get the id of the phrases that should be added to the result based on the formula reference text
        $exp = new expression($usr);
        $exp->ref_text = '{t205}={t203}*1000000';
        $result = $exp->fv_phr_lst();
        $target = new phrase_list($usr);
        $wrd = new word($usr);
        $wrd->id = 205;
        $target->lst[] = $wrd->phrase();
        $t->assert('Expression->fv_phr_lst for ' . formula::TF_SCALE_MIO, $result->dsp_id(), $target->dsp_id());

        // get the special formulas used in a formula to calculate the result
        // e.g. "next" is a special formula to get the following values
        /*
        $frm_next = new formula($usr);
        $frm_next->name = "next";
        $frm_next->type_id = cl(db_cl::FORMULA_TYPE, formula::NEXT);
        $frm_next->id = 1;
        $frm_has_next = new formula($usr);
        $frm_has_next->usr_text = '=next';
        $t->assert('Expression->fv_phr_lst for ' . formula::TF_SCALE_MIO, $result->dsp_id(), $target->dsp_id());
        */
    }

}