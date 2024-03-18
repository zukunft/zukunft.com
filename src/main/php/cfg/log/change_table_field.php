<?php

/*

    cfg/log/change_table_field.php - helper class to create the database view with the log table and field name
    ------------------------------


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

namespace cfg\log;

use cfg\db\sql;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
use cfg\db\sql_table_type;
use cfg\library;
use cfg\sandbox_named;
use cfg\verb;

class change_table_field
{


    /*
     * database link
     */

    // fields used for the database view creation
    const FLD_ID = 'change_field_id';
    const FLD_ID_AS = 'change_table_field_id';
    const FLD_NAME_AS = 'change_table_field_name';
    const FLD_CODE_ID_AS = 'code_id';
    // array with the const to create the view
    // first  entry is a list of tables for the view
    // second entry is the list of fields with the parameters
    //        each field has an entry for the field name, the source table and the target field name
    // third  entry are the fields to link the tables
    const FLD_LST_VIEW = [
        [[change_table::class, 't'], [change_field::class, 'f']],
        [
            [change_field::FLD_ID, 'f', self::FLD_ID_AS],
            [[[change_table::FLD_ID, 't'], [change_field::FLD_NAME, 'f']], '', self::FLD_NAME_AS],
            [sandbox_named::FLD_DESCRIPTION, 'f'],
            [[sql::FLD_CODE_ID, 'f', [[change_table::FLD_ID, 't'], [change_field::FLD_NAME, 'f']]], '', self::FLD_CODE_ID_AS]
        ],
        [[change_table::FLD_ID, 't'], [change_field::FLD_TABLE, 'f']]
    ];


    /*
     * SQL creation
     */

    /**
     * @return string the SQL script to create the views
     */
    function sql_view_link(sql $sc, array $tbl_fld_lst): string
    {
        $lib = new library();

        $tbl_lst = $tbl_fld_lst[0];
        $fld_par_lst = $tbl_fld_lst[1];
        $lnk_lst = $tbl_fld_lst[2];

        $sql = $sc->sql_view_header($sc->get_table_name($lib->class_to_name($this::class)), '');
        $sql .= sql::CREATE . ' ';
        $sql .= sql::VIEW . ' ';
        $sql .= $sc->get_table_name($this::class) . ' ' . sql::AS . ' ';
        $sql .= sql::SELECT . ' ';

        $sql_fld = '';
        foreach ($fld_par_lst as $fld_par) {
            if ($sql_fld != '') {
                $sql_fld .= ', ';
            }

            $fld_name = $fld_par[0];
            if (is_array($fld_name)) {
                $fld_lst = $fld_name[0];
                if (is_array($fld_lst)) {
                    $sql_fld .= $this->sql_field_concat($sc, $fld_name);
                } else {
                    $sql_fld .= $this->sql_field_when($sc, $fld_name);
                }
            } else {
                $sql_fld .= $this->sql_field_table($sc, $fld_par);
            }
            if (count($fld_par) > 1) {
                $fld_as = $fld_par[2];
                if ($fld_as != '') {
                    $sql_fld .= ' ' . sql::AS . ' ' . $fld_as;
                }
            }
        }
        $sql .= $sql_fld;

        $sql .= ' ' . sql::FROM . ' ';
        $sql_tbl = '';
        foreach ($tbl_lst as $tbl_par) {
            if ($sql_tbl != '') {
                $sql_tbl .= ', ';
            }
            $tbl_name = $tbl_par[0];
            $tbl_chr = $tbl_par[1];
            $sql_tbl .= $sc->name_sql_esc($sc->get_table_name($tbl_name)) . ' ' . sql::AS . ' ' . $tbl_chr;
        }
        $sql .= $sql_tbl;

        $sql .= ' ' . sql::WHERE . ' ';
        $from = $lnk_lst[0];
        $to = $lnk_lst[1];
        $from_name = $from[0];
        $from_chr = $from[1];
        $sql .= $from_chr . '.' . $sc->name_sql_esc($from_name);
        $sql .= ' = ';
        $to_name = $to[0];
        $to_chr = $to[1];
        $sql .= $to_chr . '.' . $sc->name_sql_esc($to_name);
        $sql .= '; ';

        return $sql;
    }

    private function sql_field_table(sql $sc, array $fld_par): string
    {
        $fld_name = $fld_par[0];
        $fld_tbl = $fld_par[1];
        $tbl_chr = substr($fld_tbl, 0, 1);
        return $tbl_chr . '.' . $sc->name_sql_esc($fld_name);
    }

    private function sql_field_concat(sql $sc, array $fld_par): string
    {
        $sql = sql::CONCAT . '(';
        $sql_fld = '';
        foreach ($fld_par as $fld) {
            if ($sql_fld != '') {
                $sql_fld .= ', ';
            }
            $sql_fld .= $this->sql_field_table($sc, $fld);
        }
        $sql .= $sql_fld . ') ';
        return $sql;
    }

    private function sql_field_when(sql $sc, array $fld_par): string
    {
        $sql = '';
        $fld_when = $fld_par[0];
        $tbl_when = $fld_par[1];
        $fld_then = $fld_par[2];
        if ($sc->is_MySQL()) {
            $sql .= sql::CASE_MYSQL . ' ';
        } else {
            $sql .= sql::CASE . ' (';
        }
        $sql .= $this->sql_field_table($sc, [$fld_when, $tbl_when]);
        $sql .= ' ' . sql::IS_NULL . ' ';
        if ($sc->is_MySQL()) {
            $sql .= sql::THEN_MYSQL . ' ';
        } else {
            $sql .= ') ' . sql::THEN . ' ';
        }
        $sql .= $this->sql_field_concat($sc, $fld_then);
        if ($sc->is_MySQL()) {
            $sql .= sql::ELSE_MYSQL . ' ';
        } else {
            $sql .= ' ' . sql::ELSE . ' ';
        }
        $sql .= $this->sql_field_table($sc, [$fld_when, $tbl_when]);
        if ($sc->is_MySQL()) {
            $sql .= sql::END_MYSQL;
        } else {
            $sql .= ' ' . sql::END;
        }
        return $sql;
    }

}