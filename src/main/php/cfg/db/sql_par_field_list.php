<?php

/*

    model/db/sql_par_field_list.php - a list of sql parameter fields
    -----------------------------

    TODO split this list into
         1. a list with just the field, value and type that should be used for the sql parameters
            and in the sql creator as a replacement for the fields, values und types object
         2. a list with additional the related name field for the user change log
            and the info if a field is an id or a changed value or both


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

use cfg\const\paths;

include_once paths::DB . 'sql_par_field.php';
//include_once paths::MODEL_HELPER . 'combine_named.php';
include_once paths::MODEL_HELPER . 'db_object_seq_id.php';
//include_once paths::MODEL_FORMULA . 'formula_db.php';
include_once paths::MODEL_LOG . 'change.php';
include_once paths::MODEL_SANDBOX . 'sandbox.php';
include_once paths::MODEL_SANDBOX . 'sandbox_multi.php';
include_once paths::MODEL_SANDBOX . 'sandbox_named.php';
include_once paths::MODEL_SANDBOX . 'sandbox_link_named.php';
//include_once paths::MODEL_HELPER . 'type_list.php';
include_once paths::MODEL_HELPER . 'type_object.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::SHARED . 'library.php';

use cfg\formula\formula_db;
use cfg\helper\combine_named;
use cfg\helper\db_object_seq_id;
use cfg\log\change;
use cfg\sandbox\sandbox;
use cfg\sandbox\sandbox_link_named;
use cfg\sandbox\sandbox_multi;
use cfg\sandbox\sandbox_named;
use cfg\helper\type_list;
use cfg\helper\type_object;
use cfg\user\user;
use DateTime;
use DateTimeInterface;
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

    function add(?sql_par_field $fld): void
    {
        if ($fld != null) {
            if (!in_array($fld->name, $this->names())) {
                $this->lst[] = $fld;
            }
        }
    }

    /**
     * create a name and an id field in the list base on a field with
     * @param sql_par_field $fld with id and name set
     * @return void
     */
    function add_with_split(sql_par_field $fld): void
    {
        if ($fld->value != null or $fld->old != null) {
            $this->add_name_part($fld);
        }
        if ($fld->id != null or $fld->old_id != null) {
            $this->add_id_part($fld);
        }
    }

    function add_id_part(?sql_par_field $fld): void
    {
        if ($fld != null) {
            $this->add_field(
                $fld->name,
                $fld->id,
                $fld->type_id,
                $fld->old_id
            );
        }
    }

    function add_name_part(sql_par_field $fld): void
    {
        $this->add_field(
            $fld->par_name,
            $fld->value,
            $fld->type,
            $fld->old
        );
    }

    function add_list(sql_par_field_list $fld_lst): void
    {
        foreach ($fld_lst->lst as $fld) {
            $this->add($fld);
        }
    }

    /**
     * add a field based on the single parameters to the list
     *
     * @param string $name the field name in the change table, so view_id not view or view_name
     * @param string|int|float|DateTime|null $value the value that has been changed from the user point of view, so the view name not the view id
     * @param sql_par_type|sql_field_type|null $type the type of the user value e.g. name for the view name
     * @param string|int|float|DateTime|null $old the value before the user change from the user point of view, so the view name not the view id
     * @param string $par_name
     * @param string|int|null $id
     * @param string|int|null $old_id
     * @param sql_par_type|sql_field_type|null $type_id
     * @return void
     */
    function add_field(
        string                           $name,
        string|int|float|DateTime|null   $value,
        sql_par_type|sql_field_type|null $type = null,
        string|int|float|DateTime|null   $old = null,
        string                           $par_name = '',
        string|int|null                  $id = null,
        string|int|null                  $old_id = null,
        sql_par_type|sql_field_type|null $type_id = null
    ): void
    {
        $fld = new sql_par_field();
        $fld->name = $name;
        $fld->value = $value;
        if ($type === null) {
            if (is_string($value)) {
                $fld->type = sql_par_type::TEXT;
            } else {
                $fld->type = sql_par_type::INT;
            }
        } else {
            if ($type::class === sql_field_type::class) {
                $fld->type = $type->par_type();
            } else {
                $fld->type = $type;
            }
        }
        $fld->old = $old;
        if ($par_name !== null) {
            $fld->par_name = $par_name;
        }
        $fld->id = $id;
        $fld->old_id = $old_id;
        if ($type_id !== null) {
            if ($type_id::class === sql_field_type::class) {
                $fld->type_id = $type_id->par_type();
            } else {
                $fld->type_id = $type_id;
            }
        } else {
            $fld->type_id = null;
        }
        $this->add($fld);
    }

    /**
     * add a link field e.g. the link to the view or the phrase type
     *
     * @param string $db_fld the field name to be updated in the database e.g. view_id
     * @param string $usr_fld the field name from the user point of view e.g. view_name
     * @param sandbox|combine_named|null $chg_sbx the object with the user changes that should be saved in the database (e.g. $this)
     * @param sandbox|combine_named|null $db_sbx the object as it is in the database before the change
     * @return void
     */
    function add_link_field(
        string                     $db_fld,
        string                     $usr_fld,
        sandbox|combine_named|null $chg_sbx,
        sandbox|combine_named|null $db_sbx
    ): void
    {
        $this->add_field(
            $db_fld,
            $chg_sbx?->name(),
            sandbox_named::FLD_NAME_SQL_TYP,
            $db_sbx?->name(),
            $usr_fld,
            $chg_sbx?->id(),
            $db_sbx?->id(),
            db_object_seq_id::FLD_ID_SQL_TYP);
    }

    /**
     * add a type or predicate field
     * and include the type name for logging based on the given type list
     * e.g. the phrase type or reference type
     *
     * @param string $db_fld the field name to be updated in the database e.g. view_id
     * @param string $usr_fld the field name from the user point of view e.g. view_name
     * @param int|null $chg_id the type id changed by the user that should be saved in the database
     * @param int|null $db_id the type id as it is in the database before the change
     * @param type_list $typ_lst the preloaded list of type
     * @return void
     */
    function add_type_field(
        string    $db_fld,
        string    $usr_fld,
        ?int      $chg_id,
        ?int      $db_id,
        type_list $typ_lst
    ): void
    {
        $this->add_field(
            $db_fld,
            $typ_lst->name_or_null($chg_id),
            type_list::FLD_NAME_SQL_TYP,
            $typ_lst->name_or_null($db_id),
            $usr_fld,
            $chg_id,
            $db_id,
            type_object::FLD_ID_SQL_TYP);
    }

    /**
     * add the id and the user field to this list
     * *
     * @param sandbox|sandbox_multi $sbx the sandbox object that has been updated
     * @return void
     */
    function add_id_and_user(sandbox|sandbox_multi $sbx): void
    {
        $this->add_field(
            $sbx::FLD_ID,
            $sbx->id(),
            db_object_seq_id::FLD_ID_SQL_TYP
        );
        $this->add_field(
            user::FLD_ID,
            $sbx->user_id(),
            db_object_seq_id::FLD_ID_SQL_TYP
        );

    }

    /**
     * add the user field to this list
     *
     * @param sandbox|sandbox_multi $sbx_upd the updated fields of the user sandbox object should be saved
     * @param sandbox|sandbox_multi $sbx_db the same user sandbox object as $sbx_upd but with the values in the db before the update
     * @param bool $do_log true if the field for logging the change should be included
     * @param int $table_id the id of the table for logging
     * @return void
     */
    function add_user(
        sandbox|sandbox_multi $sbx_upd,
        sandbox|sandbox_multi $sbx_db,
        bool                  $do_log,
        int                   $table_id
    ): void
    {
        global $cng_fld_cac;

        if ($sbx_db->user_id() <> $sbx_upd->user_id()) {
            if ($do_log) {
                $this->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . user::FLD_ID,
                    $cng_fld_cac->id($table_id . user::FLD_ID),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            if ($sbx_db->user_id() == 0) {
                $old_user_id = null;
            } else {
                $old_user_id = $sbx_db->user_id();
            }
            $this->add_field(
                user::FLD_ID,
                $sbx_upd->user_id(),
                db_object_seq_id::FLD_ID_SQL_TYP,
                $old_user_id
            );
        }

    }

    /**
     * add the name and description field to this list
     *
     * @param sandbox|sandbox_named|sandbox_link_named $sbx_upd the updated fields of the user sandbox object should be saved
     * @param sandbox|sandbox_named|sandbox_link_named $sbx_db the same user sandbox object as $sbx_upd but with the values in the db before the update
     * @param bool $do_log true if the field for logging the change should be included
     * @param int $table_id the id of the table for logging
     * @return void
     */
    function add_name_and_description(
        sandbox|sandbox_named|sandbox_link_named $sbx_upd,
        sandbox|sandbox_named|sandbox_link_named $sbx_db,
        bool                                     $do_log,
        int                                      $table_id
    ): void
    {
        global $cng_fld_cac;

        // include the name field for the log also if the object is only excluded
        if ($sbx_db->name_or_null() <> $sbx_upd->name()) {
            if ($do_log) {
                $this->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . $sbx_upd->name_field(),
                    $cng_fld_cac->id($table_id . $sbx_upd->name_field()),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $this->add_field(
                $sbx_upd->name_field(),
                $sbx_upd->name(),
                sandbox_named::FLD_NAME_SQL_TYP,
                $sbx_db->name_or_null()
            );
        }
        if ($sbx_db->description <> $sbx_upd->description) {
            if ($do_log) {
                $this->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . sql_db::FLD_DESCRIPTION,
                    $cng_fld_cac->id($table_id . sql_db::FLD_DESCRIPTION),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $this->add_field(
                sql_db::FLD_DESCRIPTION,
                $sbx_upd->description,
                sql_db::FLD_DESCRIPTION_SQL_TYP,
                $sbx_db->description
            );
        }
    }

    function del(string $fld_name): void
    {
        $result = [];
        foreach ($this->lst as $fld) {
            if ($fld->name != $fld_name) {
                $result[] = $fld;
            }
        }
        $this->lst = $result;
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
                $sc = new sql_creator();
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

    /**
     * @return bool true if the list contains only internal fields
     *              e.g. the user id, last upadte and the action
     *              which means that there is no need for a database update
     */
    function is_empty_except_internal_fields(): bool
    {
        $names = array_diff($this->names(),
            [sql::FLD_LOG_FIELD_PREFIX . user::FLD_ID, user::FLD_ID, formula_db::FLD_LAST_UPDATE]);
        if (count($names) == 0) {
            return true;
        } else {
            return false;
        }
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

    function values(): array
    {
        $result = [];
        foreach ($this->lst as $fld) {
            $result[] = $fld->value;
        }
        return $result;
    }

    function par_names(): array
    {
        $result = [];
        foreach ($this->lst as $fld) {
            $result[] = $fld->par_name;
        }
        return $result;
    }

    function db_values(): array
    {
        $result = [];
        foreach ($this->lst as $fld) {
            if ($fld->id !== null) {
                $result[] = $fld->id;
            } else {
                if ($fld->value != sql::NOW) {
                    $result[] = $fld->value;
                }
            }
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
     * @param array $names_to_select list of field names that should be excluded for the result list
     * @return sql_par_field_list with the sql parameter fields that matches none of the field names
     */
    function get_diff(array $names_to_select): sql_par_field_list
    {
        $result = new sql_par_field_list();
        foreach ($this->lst as $fld) {
            if (!in_array($fld->name, $names_to_select)) {
                $result->add($fld);
            }
        }
        return $result;
    }

    /**
     * get the value for the given field name
     * @param string $name the name of the field to select
     * @param bool $can_be_missing if true no error log message is created if the field does not exists
     * @return sql_par_field|null the name, value and type selected by the name
     */
    function get(string $name, bool $can_be_missing = false): ?sql_par_field
    {
        $key = array_search($name, $this->names());
        if ($key === false) {
            if (!$can_be_missing) {
                log_err('field "' . $name . '" missing in "' . implode(',', $this->names())) . '"';
            }
            return null;
        } else {
            return $this->lst[$key];
        }
    }

    /**
     * get the value for the given field name
     * @param string $name the name of the field to select
     * @return string|int|float|DateTime|null the value related to the given field name
     */
    function get_value(string $name): string|int|float|DateTime|null
    {
        $key = array_search($name, $this->names());
        if ($key === false) {
            return null;
        } else {
            return $this->lst[$key]->value;
        }
    }

    /**
     * get the id for the given field name
     * @param string $name the name of the field to select
     * @return string|int|null the value related to the given field name
     */
    function get_id(string $name): string|int|null
    {
        $key = array_search($name, $this->names());
        if ($key === false) {
            return null;
        } else {
            return $this->lst[$key]->id;
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

    /**
     * get the old id for the given field name
     * @param string $name the name of the field to select
     * @return string|int|null the value related to the given field name
     */
    function get_old_id(string $name): string|int|null
    {
        $key = array_search($name, $this->names());
        return $this->lst[$key]->old_id;
    }


    function get_type(string $name): sql_par_type|sql_field_type
    {
        $key = array_search($name, $this->names());
        return $this->lst[$key]->type;
    }

    function get_type_id(string $name): sql_par_type|sql_field_type
    {
        $key = array_search($name, $this->names());
        return $this->lst[$key]->type_id;
    }

    function get_par_name(string $name): string|null
    {
        $key = array_search($name, $this->names());
        return $this->lst[$key]->par_name;
    }

    function has_name(string $name): bool
    {
        return in_array($name, $this->names());
    }

    function merge(sql_par_field_list $lst_to_add): sql_par_field_list
    {
        foreach ($lst_to_add->lst as $fld) {
            $this->add($fld);
        }
        return $this;
    }

    function esc_names(sql_creator $sc): void
    {
        foreach ($this->lst as $key => $fld) {
            if ($fld->value != sql::NOW) {
                $this->lst[$key]->name = $sc->name_sql_esc($fld->name);
            }
        }
    }

    function sql_field_list(): sql_field_list
    {
        $lst = new sql_field_list();
        foreach ($this->lst as $par_fld) {
            $lst->add_par_field($par_fld);
        }
        return $lst;
    }

    /**
     * create the sql function call parameter statement
     * @param sql_creator $sc
     * @return string
     */
    function par_sql(sql_creator $sc): string
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
                if ($par_typ == sql_par_type::TEXT
                    or $par_typ == sql_field_type::TEXT
                    or $par_typ == sql_par_type::KEY_512
                    or $par_typ == sql_field_type::NAME) {
                    $sql .= "'" . $fld->value . "'";
                } elseif ($fld->value instanceof DateTime) {
                    $sql .= "'" . $fld->value->format(DateTimeInterface::ATOM) . "'";
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
     * @param sql_creator $sc
     * @return string
     */
    function par_types(sql_creator $sc): string
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
     * @param sql_creator $sc
     * @return string
     */
    function par_vars(sql_creator $sc): string
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
     * @param sql_creator $sc
     * @return string
     */
    function sql_par_names(sql_creator $sc): string
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

