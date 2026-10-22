<?php

/*

    test/create/test_sql.php - create the test objects of the sql statement creation
    ------------------------


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

namespace Zukunft\ZukunftCom\test\php\create;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::DB . 'sql_field.php';
include_once paths::DB . 'sql_par.php';
include_once paths::DB . 'sql_par_field.php';
include_once paths::DB . 'sql_par_list.php';
include_once paths::DB . 'sql_par_type.php';
include_once paths::DB . 'sql_where.php';
include_once paths::MODEL_WORD . 'word.php';
include_once paths::SHARED_CONST_FIELDS . 'word_fields.php';
include_once test_paths::CONST . 'word_names.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_field;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par_field;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par_list;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_where;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\shared\const\fields\word_fields;
use Zukunft\ZukunftCom\test\php\const\word_names;

class test_sql
{

    /**
     * @return sql_field the name field of the word mathematics
     */
    function field(): sql_field
    {
        $fld = new sql_field();
        $fld->name = word_fields::FLD_NAME;
        $fld->value = word_names::MATH;
        $fld->type = sql_par_type::TEXT;
        return $fld;
    }

    /**
     * @return sql_par_field the name parameter field of the word mathematics
     */
    function par_field(): sql_par_field
    {
        $fld = new sql_par_field();
        $fld->name = word_fields::FLD_NAME;
        $fld->value = word_names::MATH;
        $fld->type = sql_par_type::TEXT;
        return $fld;
    }

    /**
     * @return sql_par the sql parameters of a word query
     */
    function par(): sql_par
    {
        return new sql_par(word::class);
    }

    /**
     * @return sql_par the sql parameters of a word query with the name of the word mathematics as parameter
     */
    function par_math(): sql_par
    {
        $qp = $this->par();
        $qp->par = [word_names::MATH];
        return $qp;
    }

    /**
     * @return sql_par_list a list with the word query with the name of the word mathematics as parameter
     */
    function par_list_math(): sql_par_list
    {
        $par_lst = new sql_par_list();
        $par_lst->add($this->par_math());
        return $par_lst;
    }

    /**
     * @return sql_where the where condition on the word name
     */
    function where(): sql_where
    {
        $whr = new sql_where();
        $whr->fld = word_fields::FLD_NAME;
        $whr->typ = sql_par_type::TEXT;
        return $whr;
    }

}
