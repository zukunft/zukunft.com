<?php

/*

    model/helper/type_list.php - the superclass for word, formula and view type lists
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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/agpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2023 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace cfg;

include_once DB_PATH . 'sql_db.php';
include_once DB_PATH . 'sql_par.php';
include_once SHARED_PATH . 'library.php';
include_once MODEL_VERB_PATH . 'verb.php';
include_once API_SYSTEM_PATH . 'type_list.php';
include_once WEB_USER_PATH . 'user_type_list.php';

use api\system\type_list as type_list_api;
use cfg\component\component_link_type;
use cfg\component\component_link_type_list;
use cfg\component\component_type;
use cfg\component\component_type_list;
use cfg\component\position_type;
use cfg\component\position_type_list;
use cfg\db\sql;
use cfg\db\sql_db;
use cfg\db\sql_field_type;
use cfg\db\sql_par;
use cfg\log\change_action;
use cfg\log\change_action_list;
use cfg\log\change_field_list;
use cfg\log\change_table;
use cfg\log\change_table_field;
use cfg\log\change_table_list;
use cfg\user\user_profile;
use html\user\user_type_list as type_list_dsp;
use shared\library;

class type_list
{

    /*
     * database link
     */

    // database and export JSON object field names
    const FLD_NAME = 'sys_log_function_name';
    const FLD_NAME_SQLTYP = sql_field_type::NAME;

    // error return codes
    const CODE_ID_NOT_FOUND = -1;

    // persevered type name and code id for unit and integration tests
    const TEST_NAME = 'System Test Type Name';
    const TEST_TYPE = 'System Test Type Code ID';


    /*
     * object vars
     */

    private array $lst = [];  // a list of type objects
    private array $hash = []; // hash list with the code id for fast selection
    private array $name_hash = []; // if the user can add new type the hash list of the names for fast selection
    private bool $usr_can_add = false; // true if the user can add new types that does not yet have a code id


    /*
     * construct and map
     */

    /**
     * @param bool $usr_can_add true if some types might not yet have a code id
     */
    function __construct(bool $usr_can_add = false)
    {
        $this->usr_can_add = $usr_can_add;
    }

    function reset(): void
    {
        $this->set_lst(array());
    }


    /*
     * cast
     */

    /**
     * @return type_list_api the object type list frontend api object
     */
    function api_obj(): object
    {
        return new type_list_api($this->lst);
    }

    /**
     * @returns string the api json message for the object as a string
     */
    function api_json(): string
    {
        return $this->api_obj()->get_json();
    }

    /**
     * @return type_list_dsp the word frontend api object
     */
    function dsp_obj(): object
    {
        return new type_list_dsp($this->lst);
    }


    /*
     * set and get
     */

    /**
     * @returns true if the list has been replaced
     */
    function set_lst(array $lst): bool
    {
        $this->lst = $lst;
        $this->get_hash($lst);
        if ($this->usr_can_add) {
            $this->get_name_hash($lst);
        }
        return true;
    }

    /**
     * @returns array the protected list of preloaded types
     */
    function lst(): array
    {
        return $this->lst;
    }

    /**
     * @returns array the hash list of preloaded types
     */
    function hash(): array
    {
        return $this->hash;
    }


    /*
     * interface set and get
     */

    function add(type_object|ref|view $item): void
    {
        if ($item->id() <= 0) {
            log_err('Type id ' . $item->id() . ' not expected');
        } elseif ($item->code_id == '' and !$this->usr_can_add) {
            log_err('Type code id for ' . $item->id() . ' cannot be empty');
        } else {
            $this->lst[$item->id()] = $item;
            $this->hash[$item->code_id] = $item->id();
        }
        if ($this->usr_can_add) {
            $this->name_hash[$item->name] = $item->id();
        }
    }

    /*
     * database (dao) functions
     */

    /**
     * set the common part of the sql parameters to load all rows of one 'type of database type'
     *
     * a type is the link between one object and some predefined behavior
     * a.g. a word like 'meter' has the type 'measure' which implies that
     * the result of meter divided by meter is a relative value which is e.g. in percent
     *
     * a 'database type' is a group of type used for the same objects
     * e.g. a db_type is phrase_type or view type
     *
     * @param sql $sc with the target db_type set
     * @param string $class the class of the related object e.g. phrase_type or formula_type
     * @param string $query_name the name extension to make the query name unique
     * @param string $order_field set if the type list should e.g. be sorted by the name instead of the id
     * @return sql_par the sql statement with the parameters and the name
     */
    function load_sql(
        sql    $sc,
        string $class,
        string $query_name = 'all',
        string $order_field = ''): sql_par
    {
        $lib = new library();
        $db_type = $lib->class_to_name($class);
        $sc->set_class($class);
        $qp = new sql_par($db_type);
        $qp->name = $db_type . '_' . $query_name;
        $sc->set_name($qp->name);
        if ($class == verb::class) {
            $sc->set_fields(verb::FLD_NAMES);
        } elseif ($class == ref_type::class) {
            $sc->set_fields(array(sandbox_named::FLD_DESCRIPTION, sql::FLD_CODE_ID, ref_type_list::FLD_URL));
        } else {
            $sc->set_fields(array(sandbox_named::FLD_DESCRIPTION, sql::FLD_CODE_ID));
        }
        if ($order_field == '') {
            $order_field = $sc->get_id_field_name($class);
        }
        $sc->set_order($order_field);

        return $qp;
    }

    /**
     * the sql parameters to load all rows of one 'type of database type'
     *
     * a type is the link between one object and some predefined behavior
     * a.g. a word like 'meter' has the type 'measure' which implies that
     * the result of meter divided by meter is a relative value which is e.g. in percent
     *
     * a 'database type' is a group of type used for the same objects
     * e.g. a db_type is phrase_type or view type
     *
     * TODO create a warning if number of rows is above the sql_db::ROW_MAX limit
     *
     * @param sql $sc with the target db_type set
     * @param string $class the class of the related object e.g. phrase_type or formula_type
     * @return sql_par the sql statement with the parameters and the name
     */
    function load_sql_all(sql $sc, string $class = ''): sql_par
    {
        if ($class == '') {
            $class = $this::class;
            // replace the type list class with the type class because the load is done from the list object instead of the type object
            $class = $this->list_class_to_type($class);
        }
        $qp = $this->load_sql($sc, $class);
        $sc->set_page(sql_db::ROW_MAX, 0);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * get the type class for a given type list class
     *
     * @param string $class a list class name
     * @return string the corresponding type class name
     */
    private function list_class_to_type(string $class): string
    {
        return match ($class) {
            sys_log_function_list::class => sys_log_function::class,
            sys_log_status_list::class => sys_log_status::class,
            user_profile_list::class => user_profile::class,
            change_action_list::class => change_action::class,
            change_table_list::class => change_table::class,
            change_field_list::class => change_table_field::class,
            share_type_list::class => share_type::class,
            protection_type_list::class => protection_type::class,
            job_type_list::class => job_type::class,
            language_form_list::class => language_form::class,
            language_list::class => language::class,
            verb_list::class => verb::class,
            ref_type_list::class => ref_type::class,
            source_type_list::class => source_type::class,
            formula_type_list::class => formula_type::class,
            formula_link_type_list::class => formula_link_type::class,
            element_type_list::class => element_type::class,
            view_type_list::class => view_type::class,
            component_type_list::class => component_type::class,
            component_link_type_list::class => component_link_type::class,
            position_type_list::class => position_type::class,
            default => $class,
        };
    }

    /**
     * force to reload the type names and translations from the database
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @param string $class the database name e.g. the table name without s
     * @return array the list of types
     */
    protected function load_list(sql_db $db_con, string $class): array
    {
        $this->lst = [];
        $qp = $this->load_sql_all($db_con->sql_creator(), $class);
        $db_lst = $db_con->get($qp);
        if ($db_lst != null) {
            foreach ($db_lst as $db_row) {
                $type_id = $db_row[$db_con->get_id_field_name($class)];
                $type_code_id = strval($db_row[sql::FLD_CODE_ID]);
                // database field name exceptions
                $type_name = '';
                if ($class == change_action::class) {
                    $type_name = strval($db_row[type_object::FLD_ACTION]);
                } elseif ($class == change_table::class) {
                    $type_name = strval($db_row[type_object::FLD_TABLE]);
                } elseif ($class == change_table_field::class) {
                    $type_name = strval($db_row[type_object::FLD_FIELD]);
                } elseif ($class == language_form::class) {
                    $type_name = strval($db_row[language_form::FLD_NAME]);
                } elseif ($class == language::class) {
                    $type_name = strval($db_row[language::FLD_NAME]);
                } else {
                    $type_name = strval($db_row[sql::FLD_TYPE_NAME]);
                }
                $type_comment = strval($db_row[sandbox_named::FLD_DESCRIPTION]);
                $type_obj = new type_object($type_code_id, $type_name, $type_comment, $type_id);
                $this->add($type_obj);
            }
        }
        return $this->lst;
    }

    /**
     * recreate the hash table to get the database id for a code_id
     * @param array $type_list the list of the code_id indexed by the database id
     * @return array with the database ids indexed by the code_id
     */
    function get_hash(array $type_list): array
    {
        $this->hash = [];
        if ($type_list != null) {
            foreach ($type_list as $key => $type) {
                $this->hash[$type->code_id] = $key;
            }
        }
        return $this->hash;
    }

    /**
     * recreate the hash table of the names to get the database id for a name
     * @param array $type_list the list of the code_id indexed by the database id
     * @return array with the database ids indexed by the code_id
     */
    function get_name_hash(array $type_list): array
    {
        $this->name_hash = [];
        if ($type_list != null) {
            if ($this->usr_can_add) {
                foreach ($type_list as $key => $type) {
                    $this->name_hash[$type->name] = $key;
                }
            }
        }
        return $this->name_hash;
    }

    /**
     * reload a type list from the database e.g. because a translation has changed and fill the hash table
     * @param string $class the child object class for the database table type name to select either word, formula, view, ...
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @return bool true if load was successful
     */
    function load(sql_db $db_con, string $class = ''): bool
    {
        $result = false;
        if ($class == '') {
            $class = $this::class;
            // replace the type list class with the type class because the load is done from the list object instead of the type object
            $class = $this->list_class_to_type($class);
        }
        $this->lst = $this->load_list($db_con, $class);
        $this->hash = $this->get_hash($this->lst);
        if ($this->usr_can_add) {
            $this->name_hash = $this->get_name_hash($this->lst);
        }
        if (count($this->hash) > 0) {
            $result = true;
        }
        return $result;
    }

    /**
     * return the database row id based on the code_id
     * and if code id is not foun, use the name
     *
     * @param string $code_id or the name
     * @return int the database id for the given code_id
     */
    function id(string $code_id): int
    {
        $lib = new library();
        $result = 0;
        if ($code_id != '' and $code_id != null) {
            if (array_key_exists($code_id, $this->hash)) {
                $result = $this->hash[$code_id];
            } else {
                if ($this->usr_can_add) {
                    if (array_key_exists($code_id, $this->name_hash)) {
                        $result = $this->name_hash[$code_id];

                    } else {
                        $result = self::CODE_ID_NOT_FOUND;
                        log_debug('Type id not found for name "' . $code_id . '" in ' . $lib->dsp_array_keys($this->name_hash));
                    }
                } else {
                    $result = self::CODE_ID_NOT_FOUND;
                    log_debug('Type id not found for "' . $code_id . '" in ' . $lib->dsp_array_keys($this->hash));
                }
            }
        } else {
            log_debug('Type code id not not set');
        }

        return $result;
    }

    /**
     * return user specific type name based on the database row id
     *
     * @param int|null $id
     * @return string
     */
    function name(?int $id): string
    {
        $result = '';
        if ($id != null) {
            $type = $this->get($id);
            if ($type != null) {
                $result = $type->name;
            } else {
                log_debug('Type id ' . $id . ' not found');
            }
        }
        return $result;
    }

    /**
     * return user specific type name based on the database row id
     *
     * @param int|null $id
     * @return string|null
     */
    function name_or_null(?int $id): ?string
    {
        if ($id == null) {
            return null;
        } else {
            return $this->name($id);
        }
    }

    /**
     * pick a type from the preloaded object list
     * @param int $id the database id of the expected type
     * @return type_object|null the type object
     */
    function get(int $id): ?type_object
    {
        $result = null;
        if ($id > 0) {
            if (array_key_exists($id, $this->lst)) {
                $result = $this->lst[$id];
            } else {
                log_err('Type with is ' . $id . ' not found in ' . $this->dsp_id());
            }
        } else {
            log_debug('Type id not set');
        }
        return $result;
    }

    /**
     * TODO to rename to get and rename get to get_by_id
     */
    function get_by_code_id(string $code_id): type_object
    {
        return $this->get($this->id($code_id));
    }

    function code_id(int $id): string
    {
        $result = '';
        $type = $this->get($id);
        if ($type != null) {
            $result = $type->code_id;
        } else {
            log_err('Type code id not found for ' . $id . ' in ' . $this->dsp_id());
        }
        return $result;
    }

    function count(): int
    {
        return count($this->lst());
    }

    /**
     * @return bool true if the list is empty (and a foreach loop will fail)
     */
    function is_empty(): bool
    {
        $result = false;
        if (empty($this->lst)) {
            $result = true;
        }
        return $result;
    }


    /*
     * unit test support functions
     */

    /**
     * create dummy type list for the unit tests without database connection
     */
    function load_dummy(): void
    {
        $this->lst = array();
        $this->hash = array();
        $this->name_hash = array();
        $type = new type_object(type_list::TEST_TYPE, type_list::TEST_NAME, '', 1);
        $this->add($type);
    }

    /**
     * @param array $code_id_list with the code ids that should be converted
     * @return array with the component ids
     */
    function view_id_list(array $code_id_list): array
    {
        global $view_types;

        $result = [];
        foreach ($code_id_list as $code_id) {
            $result[] = $view_types->id($code_id);
        }
        return $result;
    }

    /**
     * @param array $code_id_list with the code ids that should be converted
     * @return array with the component ids
     */
    function component_id_list(array $code_id_list): array
    {
        global $component_types;

        $result = [];
        foreach ($code_id_list as $code_id) {
            $result[] = $component_types->id($code_id);
        }
        return $result;
    }


    /*
     * debug
     */

    /**
     * @return string the verb list with the internal database ids for debugging
     */
    function dsp_id(): string
    {
        $names = '';
        $ids = '';
        if (!$this->is_empty()) {
            foreach ($this->lst as $key => $type) {
                if ($names != '') {
                    $names .= ', ';
                }
                $names .= '"' . $type->name() . '"';

                if ($ids != '') {
                    $ids .= ', ';
                }
                $ids .= $key;
            }
        }
        return $names . ' (' . $ids . ')';
    }

}