<?php

/*

    cfg/db/sql_par_field_list.php - a list of sql parameter fields
    -----------------------------


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

namespace cfg\db;

class sql_par_field_list
{
    // assumned positions of the field name, value and type in the array used for set
    private const FLD_POS = 0;
    private const VAL_POS = 1;
    private const TYP_POS = 2;

    public array $lst = [];  // a list of sql parameter fields

    /**
     * set the list based on an array where each item is an array with field, value and type
     * @param array $lst array where each item is an array with field, value and type
     * @return void
     */
    function set(array $lst): void
    {
        foreach ($lst as $fld_array) {
            $fld = new sql_par_field();
            $fld->name = $fld_array[self::FLD_POS];
            $fld->value = $fld_array[self::VAL_POS];
            $fld->type = $fld_array[self::TYP_POS];
            $this->lst[] = $fld;
        }
    }

    function add(sql_par_field $fld): void
    {
        if (!in_array($fld->name, $this->names())) {
            $this->lst[] = $fld;
        }
    }

    function add_field(
        string                      $name,
        string|int|float|null       $value,
        sql_par_type|sql_field_type $type,
        string|int|float|null       $old = null
    ): void
    {
        $fld = new sql_par_field();
        $fld->name = $name;
        $fld->value = $value;
        $fld->type = $type;
        $fld->old = $old;
        $this->add($fld);
    }

    function names(): array
    {
        $result = [];
        foreach ($this->lst as $fld) {
            $result[] = $fld->name;
        }
        return $result;
    }

    function values(): array
    {
        $result = [];
        foreach ($this->lst as $fld) {
            $result[] = $fld->value;
        }
        return $result;
    }

    function types(): array
    {
        $result = [];
        foreach ($this->lst as $fld) {
            $result[] = $fld->type;
        }
        return $result;
    }

    /**
     * @param array $names_to_select list of field names that should be selected for the result list
     * @return array with the sql parameter fields that matches the field names
     */
    function intersect(array $names_to_select): array
    {
        $result = [];
        foreach ($this->lst as $fld) {
            if (in_array($fld->name, $names_to_select)) {
                $result[] = $fld;
            }
        }
        return $result;
    }

    /**
     * get the value for the given field name
     * @param string $name the name of the field to select
     * @return string|int|float|null the value related to the given field name
     */
    function get_value(string $name): string|int|float|null
    {
        $key = array_search($name, $this->names());
        return $this->lst[$key]->value;
    }

    /**
     * get the old value for the given field name
     * @param string $name the name of the field to select
     * @return string|int|float|null the value related to the given field name
     */
    function get_old(string $name): string|int|float|null
    {
        $key = array_search($name, $this->names());
        return $this->lst[$key]->old;
    }


    function get_type(string $name): sql_par_type|sql_field_type
    {
        $key = array_search($name, $this->names());
        return $this->lst[$key]->type;
    }

    function merge(sql_par_field_list $lst_to_add): sql_par_field_list
    {
        foreach ($lst_to_add->lst as $fld) {
            $this->add($fld);
        }
        return $this;
    }

    function esc_names(sql $sc): void
    {
        foreach ($this->lst as $key => $fld) {
            if ($fld->value != sql::NOW) {
                $this->lst[$key]->name = $sc->name_sql_esc($fld->name);
            }
        }
    }

    /**
     * create the sql function call parameter statement
     * @param sql $sc
     * @return void
     */
    function par_sql(sql $sc): string
    {
        $sql = '';
        foreach ($this->lst as $key => $fld) {
            if ($sql != '') {
                $sql .= ', ';
            }
            $par_typ = $fld->type;
            $val_typ = $sc->par_type_to_postgres($fld->type);
            if ($fld->value === null) {
                $sql .= 'null';
            } else {
                if ($par_typ == sql_par_type::TEXT or $par_typ == sql_field_type::TEXT
                    or $par_typ == sql_field_type::NAME) {
                    $sql .= "'" . $fld->value . "'";
                } else {
                    $sql .= $fld->value;
                }
            }
            if ($val_typ != '') {
                $sql .= '::' . $val_typ;
            }
        }
        return $sql;
    }

}

