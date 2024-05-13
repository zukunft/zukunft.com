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

use shared\library;

class sql_par_field_list
{
    // assumed positions of the field name, value and type in the array used for set
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
            $type = $fld_array[self::TYP_POS];
            if (is_string($type)) {
                $fld->type = sql_par_type::TEXT;
            } else {
                if ($type::class === sql_field_type::class) {
                    $fld->type = $type->par_type();
                } else {
                    $fld->type = $type;
                }
            }
            $this->lst[] = $fld;
        }
    }

    function add(sql_par_field $fld): void
    {
        if (!in_array($fld->name, $this->names())) {
            $this->lst[] = $fld;
        }
    }

    function add_list(sql_par_field_list $fld_lst): void
    {
        foreach ($fld_lst->lst as $fld) {
            $this->add($fld);
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
        if ($type::class === sql_field_type::class) {
            $fld->type = $type->par_type();
        } else {
            $fld->type = $type;
        }
        $fld->old = $old;
        $this->add($fld);
    }

    function fill_from_arrays(array $fields, array $values, array $types = []): void
    {
        if (count($fields) <> count($values)) {
            $lib = new library();
            log_err(
                'SQL insert call with different number of fields (' . $lib->dsp_count($fields)
                . ': ' . $lib->dsp_array($fields) . ') and values (' . $lib->dsp_count($values)
                . ': ' . $lib->dsp_array($values) . ').', "user_log->add");
        } else {
            $i = 0;
            foreach ($fields as $fld) {
                $val = $values[$i];
                $sc = new sql();
                $type = $sc->get_sql_par_type($val);
                if (count($types) == count($fields)) {
                    $type = $types[$i];
                }
                $this->add_field($fld, $val, $type);
                $i++;
            }
        }
    }

    function is_empty(): bool
    {
        if (count($this->lst) == 0) {
            return true;
        } else {
            return false;
        }
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
     * @param array $names_to_select list of field names that should be selected for the result list
     * @return sql_par_field_list with the sql parameter fields that matches the field names
     */
    function get_intersect(array $names_to_select): sql_par_field_list
    {
        $result = new sql_par_field_list();
        foreach ($this->lst as $fld) {
            if (in_array($fld->name, $names_to_select)) {
                $result->add($fld);
            }
        }
        return $result;
    }

    /**
     * get the value for the given field name
     * @param string $name the name of the field to select
     * @return sql_par_field the name, value and type selected by the name
     */
    function get(string $name): sql_par_field
    {
        $key = array_search($name, $this->names());
        return $this->lst[$key];
    }

    /**
     * get the value for the given field name
     * @param string $name the name of the field to select
     * @return string|int|float|null the value related to the given field name
     */
    function get_value(string $name): string|int|float|null
    {
        $key = array_search($name, $this->names());
        if ($key === false) {
            return null;
        } else {
            return $this->lst[$key]->value;
        }
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
     * @return string
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
            if ($sc->db_type == sql_db::POSTGRES) {
                if ($val_typ != '') {
                    $sql .= '::' . $val_typ;
                }
            }
        }
        return $sql;
    }

    /**
     * create the sql call parameter type statement part
     * @param sql $sc
     * @return string
     */
    function par_types(sql $sc): string
    {
        $sql = '';
        foreach ($this->lst as $key => $fld) {
            if ($sql != '') {
                $sql .= ', ';
            }
            $val_typ = $sc->par_type_to_postgres($fld->type);
            $sql .= $val_typ;
        }
        return $sql;
    }

    /**
     * create the sql call parameter symbol statement part
     * @param sql $sc
     * @return string
     */
    function par_vars(sql $sc): string
    {
        $sql = '';
        $pos = 1;
        foreach ($this->lst as $key => $fld) {
            if ($sql != '') {
                $sql .= ', ';
            }
            if ($sc->db_type == sql_db::POSTGRES) {
                $sql .= '$' . $pos;
            } else {
                $sql .= '?';
            }
            $pos++;
        }
        return $sql;
    }

    /**
     * create the sql function call parameter statement
     * @param sql $sc
     * @return string
     */
    function par_names(sql $sc): string
    {
        $sql = '';
        foreach ($this->lst as $key => $fld) {
            if ($sql != '') {
                $sql .= ', ';
            }
            $val_typ = $sc->par_type_to_postgres($fld->type);
            $sql .= '_' . $fld->name;
            if ($val_typ != '') {
                $sql .= ' ' . $val_typ;
            }
        }
        return $sql;
    }

}

