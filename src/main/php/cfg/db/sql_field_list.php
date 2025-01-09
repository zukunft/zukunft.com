<?php

/*

    cfg/db/sql_field_list.php - a list of sql parameter fields
    -------------------------

    The list of the parameters used for one query or function on order of usage
    unit by field name AND value

    TODO add function that returns either the name e.g._word_id or the placeholder e.g. $3 / ?

    TODO add a sql_where_list, which contains
        the name or letter of the table
        name of the db field
        assign type ( = / in / AND / OR / ... )
        the position of the related parameter

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

include_once DB_PATH . 'sql_field.php';
include_once SHARED_PATH . 'library.php';

use DateTime;
use shared\library;

class sql_field_list
{

    public array $lst = [];  // a list of sql parameter fields

    /**
     * add a field to the list
     * entries with an empty names are allowed e.g. for the sql function Now() that needs no parameter
     *
     * @param sql_field|null $fld the field to add with at least the name set
     * @return void
     */
    function add(?sql_field $fld): void
    {
        $this->lst[] = $fld;
    }

    /**
     * add a field based on the separate name, value and type
     *
     * @param string|null $name
     * @param string|int|float|DateTime|null $value
     * @param sql_par_type|null $type
     * @return void
     */
    function add_field(
        ?string $name,
        string|int|float|DateTime|null $value = null,
        sql_par_type|null $type = null
    ): void
    {
        $fvt = new sql_field();
        $fvt->name = $name;
        $fvt->value = $value;
        $fvt->type = $type;
        $this->add($fvt);
    }

    /**
     * add a value without name e.g. for the sql function Now()
     *
     * @param string $value
     * @return void
     */
    function add_value(string $value): void
    {
        $fvt = new sql_field();
        $fvt->name = null;
        $fvt->value = $value;
        $fvt->type = sql_par_type::CONST;
        $this->add($fvt);
    }

    /**
     * add a sql par field and ignore the id and old part of the sql par field
     * @param sql_par_field $fld with at least the name set
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return void
     */
    function add_par_field(
        sql_par_field $fld,
        sql_type_list $sc_par_lst = new sql_type_list()
    ): void
    {
        $sql_fld = new sql_field();
        $sql_fld->name = $fld->name;
        if ($sc_par_lst->use_named_par()) {
            $sql_fld->name = sql::PAR_PREFIX . $fld->name;
        }
        $sql_fld->value = $fld->value;
        $sql_fld->type = $fld->type;
        $this->add($sql_fld);
    }

    /**
     * add the id part of a sql par field
     * @param sql_par_field $fld with at least the name set
     * @return void
     */
    function add_par_field_id(sql_par_field $fld): void
    {
        $sql_fld = new sql_field();
        $sql_fld->name = $fld->name;
        $sql_fld->value = $fld->id;
        $sql_fld->type = $fld->type_id;
        $this->add($sql_fld);
    }

    /**
     * get the sql_field of the given position
     * @param int $pos the position of the requested element
     * @return sql_field if found the filled sql_field
     */
    function get(int $pos): sql_field
    {
        if (array_key_exists($pos, $this->lst)) {
            return $this->lst[$pos];
        } else {
            log_err('sql field ' . $pos . ' not found');
            return new sql_field();
        }
    }

    /**
     * the name of a parameter from a given position
     * @param int $pos the position in the parameter list starting with zero
     * @return string the name of the parameter
     */
    function name(int $pos): string
    {
        return $this->lst[$pos]->name;
    }

    /**
     * the value of a parameter from a given position
     * @param int $pos the position in the parameter list starting with zero
     * @return string the value of the parameter
     */
    function value(int $pos): string
    {
        return $this->lst[$pos]->value;
    }

    /**
     * the type of parameter from a given position
     * @param int $pos the position in the parameter list starting with zero
     * @return sql_par_type the sql type of the parameter
     */
    function type(int $pos): sql_par_type
    {
        return $this->lst[$pos]->type;
    }

    function pos(string $name): int
    {
        return array_search($name, $this->names());
    }

    /**
     * @return array with the field names of the list
     */
    function names(): array
    {
        $result = [];
        foreach ($this->lst as $fld) {
            $result[] = $fld->name;
        }
        return $result;
    }

    /**
     * @return array with the field values of the list
     */
    function values(): array
    {
        $result = [];
        foreach ($this->lst as $fld) {
            $result[] = $fld->value;
        }
        return $result;
    }

    /**
     * @return array with the field types of the list
     */
    function types(): array
    {
        $result = [];
        foreach ($this->lst as $fld) {
            $result[] = $fld->type;
        }
        return $result;
    }

    function has(string $name): bool
    {
        if (in_array($name, $this->names())) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return int get the number of named parameters (excluding the const like Now())
     */
    function count(): int
    {
        return count($this->names());
    }

    /**
     * @return array with the field names of the list or the const value
     */
    function names_or_const(): array
    {
        $result = [];
        foreach ($this->lst as $fld) {
            if ($fld->type == sql_par_type::CONST) {
                $result[] = $fld->value;
            } else {
                $result[] = $fld->name;
            }
        }
        return $result;
    }

    /**
     * @return string with the parameter names formatted for sql
     */
    function sql_names(): string
    {
        $lib = new library();
        return $lib->sql_array($this->names_or_const(), ' ', ' ');
    }

}

