<?php

/*

    model/sandbox/combine_named.php - parent object for a phrase or term objects
    -------------------------------

    phrase and term have the fields name, description and type in common


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

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace cfg\helper;

use cfg\const\paths;

include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_type.php';
include_once paths::DB . 'sql_where_type.php';
include_once paths::MODEL_HELPER . 'combine_object.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_message.php';
//include_once paths::MODEL_VERB . 'verb.php';
include_once paths::SHARED . 'library.php';

use cfg\db\sql;
use cfg\db\sql_creator;
use cfg\db\sql_db;
use cfg\db\sql_type;
use cfg\user\user;
use cfg\user\user_message;
use cfg\verb\verb;
use shared\library;

class combine_named extends combine_object
{

    /*
     * database link
     */

    // list of phrase types used for the database views
    const TBL_LIST = array(
        [sql_type::PRIME],
        [sql_type::MOST],
        [sql_type::PRIME, sql_type::USER],
        [sql_type::MOST, sql_type::USER]
    );
    // list of original tables that should be connoted with union
    // with fields used in the view
    // overwritten by the child objects
    const TBL_FLD_LST_VIEW = [];

    /*
     * construct and map
     */

    /**
     * set the object vars of a phrase or term to the neutral initial value
     */
    function reset(): void
    {
        $this->obj?->reset();
    }


    /*
     * set and get
     */

    /**
     * @param int $id the id of the object
     * e.g. 1 for the triple Pi (math)
     * the id of the phrase or term is
     * created dynamically by the child class
     */
    function set_obj_id(int $id): void
    {
        $this->obj()?->set_id($id);
    }

    /**
     * @return int|null the id of the object
     * e.g. 1 for the triple Pi (math)
     * whereas the phrase has the id -1
     * the id of the phrase or term is created
     * by the function id() of phrase or term
     */
    function obj_id(): ?int
    {
        return $this->obj()?->id();
    }

    /**
     * @param string $name the name of the word, triple, formula or verb
     * @return void
     */
    function set_name(string $name): void
    {
        $this->obj()?->set_name($name);
    }

    /**
     * @return string|null the name of the word, triple, formula or verb
     */
    function name(): ?string
    {
        return $this->obj()?->name();
    }

    /**
     * @param string|null $description the description of the word, triple, formula or verb
     * @return void
     */
    function set_description(?string $description): void
    {
        $this->obj()?->set_description($description);
    }

    /**
     * @return string|null the description of the word, triple, formula or verb
     */
    function description(): ?string
    {
        return $this->obj()?->description();
    }

    /**
     * @param int|null $type_id the type id of the word, triple, formula or verb
     * @param user $usr_req the user who wants to change the type
     * @return user_message warning message for the user if the permissions are missing
     */
    function set_type_id(?int $type_id, user $usr_req = new user()): user_message
    {
        return $this->obj()?->set_type_id($type_id, $usr_req);
    }

    /**
     * @param string|null $code_id the code id of the target share type or null to remove the parent overwrite
     * @return void
     */
    function set_share(?string $code_id): void
    {
        $this->obj()?->set_share($code_id);
    }

    /**
     * @param string|null $code_id the code id of the target protection or null to remove the parent overwrite
     * @return void
     */
    function set_protection(?string $code_id): void
    {
        $this->obj()?->set_protection($code_id);
    }

    /**
     * @return int|null the type id of the word, triple, formula or verb
     * if null the type of related phrase or term can be used
     * e.g. if the type of the triple "Pi (math)" is not set
     * but the triple is "Pi is a math const" and the type for "math const" is set it is used
     */
    function type_id(): ?int
    {
        return $this->obj()?->type_id();
    }

    /**
     * set excluded to 'true' to switch off the usage of this named combine object
     * @return void
     */
    function exclude(): void
    {
        $this->obj()?->exclude();
    }

    /**
     * set excluded to 'false' to switch on the usage of this user sandbox object
     * @return void
     */
    function include(): void
    {
        $this->obj()?->include();
    }

    /**
     * @return bool true if the user does not want to use this object at all
     */
    function is_excluded(): bool
    {
        return $this->obj()?->is_excluded();
    }

    /**
     * @return bool true if the excluded field is set
     */
    function is_exclusion_set(): bool
    {
        return $this->obj()->is_exclusion_set();
    }

    /**
     * @param string|null $plural the code id of the target protection or null to remove the parent overwrite
     * @return void
     */
    function set_plural(?string $plural): void
    {
        $this->obj()?->set_plural($plural);
    }


    /*
     * SQL creation
     */

    /**
     * @return string the SQL script to create the views
     */
    function sql_view(sql_creator $sc, string $class): string
    {
        $sql = $sc->sql_separator();
        $lib = new library();
        $tbl_name = $lib->class_to_name($class);
        foreach ($this::TBL_LIST as $sc_par_lst) {
            $tbl_typ = $sc_par_lst[0];
            $tbl_com = $sc_par_lst[2];
            $usr_prefix = '';
            if ($sc->is_user($sc_par_lst)) {
                $usr_prefix = sql_type::USER->prefix();
            }
            $sql .= $sc->sql_view_header($sc->get_table_name($usr_prefix . $tbl_typ->prefix() . $tbl_name), $tbl_com);
            $sql .= $this->sql_create_view($sc, $tbl_name, $sc_par_lst) . '; ';
        }
        return $sql;
    }

    function sql_create_view(sql_creator $sc, string $tbl_name, array $sc_par_lst): string
    {
        $lib = new library();
        $usr_prefix = '';
        if ($sc->is_user($sc_par_lst)) {
            $usr_prefix = sql_type::USER->prefix();
        }
        $tbl_typ = $sc_par_lst[0];
        $tbl_where = $sc_par_lst[1];
        $sql = sql::CREATE . ' ';
        $sql .= sql::VIEW . ' ';
        $sql .= $sc->get_table_name($usr_prefix . $tbl_typ->prefix() . $tbl_name) . ' ' . sql::AS . ' ';
        $sql_tbl = '';
        foreach ($this::TBL_FLD_LST_VIEW as $tbl) {
            if ($sql_tbl != '') {
                $sql_tbl .= ' ' . sql::UNION . ' ';
            }
            $sub_class = $tbl[0];
            $fld_lst = $tbl[1];
            $fld_where = $tbl[2];
            $tbl_name = $lib->class_to_name($sub_class);
            if ($sub_class == verb::class) {
                $usr_prefix = '';
            }
            $tbl_chr = $tbl_name[0];
            $sql_tbl .= sql::SELECT . ' ';
            $sql_fld = '';
            foreach ($fld_lst as $fld) {
                if ($sql_fld != '') {
                    $sql_fld .= ', ';
                }
                $fld_name = $fld[0];
                if (is_array($fld_name)) {
                    $sql_fld .= $this->sql_when($sc, $fld_name, $tbl_chr);
                } else {
                    if (count($fld) > 2) {
                        if ($fld[2] == sql_db::FLD_CONST) {
                            if ($fld_name == '') {
                                $sql_fld .= "''";
                            } else {
                                $sql_fld .= $fld_name;
                            }
                        } else {
                            if ($fld_name == '') {
                                $sql_fld .= "''";
                            } else {
                                $sql_fld .= $tbl_chr . '.' . $sc->name_sql_esc($fld_name);
                            }
                        }
                    } else {
                        if ($fld_name == '') {
                            $sql_fld .= "''";
                        } else {
                            $sql_fld .= $tbl_chr . '.' . $sc->name_sql_esc($fld_name);
                        }
                    }
                }
                if (count($fld) > 1) {
                    if (count($fld) > 2) {
                        if ($fld[2] != sql_db::FLD_CONST) {
                            $sql_fld .= ' ' . $fld[2] . ' ' . sql::AS . ' ' . $sc->name_sql_esc($fld[1]);
                        } else {
                            $sql_fld .= ' ' . sql::AS . ' ' . $sc->name_sql_esc($fld[1]);
                        }
                    } else {
                        $sql_fld .= ' ' . sql::AS . ' ' . $sc->name_sql_esc($fld[1]);
                    }
                }
            }
            $sql_tbl .= $sql_fld . ' ';
            $sql_tbl .= sql::FROM . ' ' . $sc->get_table_name($usr_prefix . $tbl_name) . ' ';
            $sql_tbl .= sql::AS . ' ' . $tbl_chr;
            if (is_array($fld_where)) {
                $sql_where_fld = '';
                $sql_where_cond = '';
                $cond_pos = 0;
                foreach ($fld_where as $fld_where_name) {
                    if ($sql_where_fld != '') {
                        $sql_where_fld .= ' ' . sql::AND . ' ';
                    }
                    if ($fld_where_name != '') {
                        if (count($tbl_where) > $cond_pos) {
                            $tbl_where_fld = $tbl_where[$cond_pos];
                            if (is_array($tbl_where_fld)) {
                                foreach ($tbl_where_fld as $tbl_where_cond) {
                                    if ($sql_where_cond != '') {
                                        $sql_where_cond .= ' ' . sql::OR . ' ';
                                    } else {
                                        $sql_where_cond .= ' (';
                                    }
                                    if ($tbl_where_cond != '') {
                                        $sql_where_cond .= ' ' . $tbl_chr . '.' . $sc->name_sql_esc($fld_where_name) . ' ';
                                        $sql_where_cond .= $tbl_where_cond;
                                    }
                                }
                                $sql_where_cond .= ') ';
                            } else {
                                if ($tbl_where_fld != '') {
                                    $sql_where_fld .= ' ' . $tbl_chr . '.' . $sc->name_sql_esc($fld_where_name) . ' ';
                                    $sql_where_fld .= $tbl_where_fld;
                                }
                            }
                        }
                    }
                    $cond_pos++;
                }
                if ($sql_where_fld != '') {
                    if ($sql_where_cond != '') {
                        $sql_tbl .= ' ' . sql::WHERE . ' ' . $sql_where_cond . ' ' . sql::AND . ' ' . $sql_where_fld;
                    } else {
                        $sql_tbl .= ' ' . sql::WHERE . ' ' . $sql_where_fld;
                    }
                } else {
                    if ($sql_where_cond != '') {
                        $sql_tbl .= ' ' . sql::WHERE . ' ' . $sql_where_cond;
                    }
                }
            } else {
                if ($tbl_where != '') {
                    $sql_tbl .= ' ' . sql::WHERE . ' ' . $tbl_chr . '.' . $sc->name_sql_esc($fld_where) . ' ';
                    $sql_tbl .= $tbl_where;
                }
            }
        }
        $sql .= $sql_tbl;
        return $sql;
    }

    private function sql_when(sql_creator $sc, array $fld_lst, string $tbl_chr): string
    {
        $sql = '';
        $this_fld = array_shift($fld_lst);
        if (count($fld_lst) > 0) {
            if ($sc->is_MySQL()) {
                $sql .= sql::CASE_MYSQL . ' ';
            } else {
                $sql .= sql::CASE . ' (';
            }
            $sql .= $tbl_chr . '.' . $sc->name_sql_esc($this_fld) . ' ' . sql::IS_NULL;
            if ($sc->is_MySQL()) {
                $sql .= sql::THEN_MYSQL . ' ';
            } else {
                $sql .= ') ' . sql::THEN . ' ';
            }
            if (count($fld_lst) > 1) {
                $sql .= $this->sql_when($sc, $fld_lst, $tbl_chr);
            } else {
                $sql .= $tbl_chr . '.' . $fld_lst[0];
            }
        }
        if (count($fld_lst) > 0) {
            if ($sc->is_MySQL()) {
                $sql .= sql::ELSE_MYSQL . ' ';
            } else {
                $sql .= ' ' . sql::ELSE . ' ';
            }
            $sql .= $tbl_chr . '.' . $sc->name_sql_esc($this_fld) . ' ';
            if ($sc->is_MySQL()) {
                $sql .= sql::END_MYSQL;
            } else {
                $sql .= ' ' . sql::END;
            }
        }
        return $sql;
    }

}
