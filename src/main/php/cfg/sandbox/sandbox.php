<?php

/*

    cfg/sandbox/sandbox.php - the superclass for handling user specific objects including the database saving
    -----------------------

    TODO should be merged once php allows aggregating extends e.g. sandbox extends db_object, db_user_object

    This superclass should be used by the classes words, formula, ... to enable user specific values and links
    similar to sandbox.php but for database objects that have an auto sequence prime id

    The main sections of this object are
    - sandbox types:     const to group the classes
    - db const:          const for the database link
    - object vars:       the variables of this word object
    - construct and map: including the mapping of the db row to this word object
    - set and get:       to capsule the vars from unexpected changes
    - preloaded:         select e.g. types from cache
    - cast:              create an api object and set the vars from an api json
    - load:              database access object (DAO) functions
    - im- and export:    create an export object and set the vars from an import object
    - information:       functions to make code easier to read
    - owner and access:  functions to make code easier to read
    - sandbox:           manage the user sandbox
    - log:               write the changes to the log
    - save fields:       write single fields to the database
    - save id:           update the id in the database and create user messages
    - similar:           check for similar objects before writing to the database
    - add:               create and execute the db insert statements
    - save:              manage to update the database
    - delete:            manage to remove from the database
    - type field:        write the type field to the database (to review)
    - sql write:         sql statement creation to write to the database
    - sql write fields:  field list for writing to the database
    - sql create:        to create the database table, index and foreign keys
    - internal check:    for testing during development


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

// TODO align the function return types with the source (ref) object
// TODO use the user sandbox also for the word object
// TODO check if handling of negative ids is correct
// TODO split into a link and a named user sandbox object to always use the smallest possible object

namespace cfg;

include_once SHARED_TYPES_PATH . 'protection_type.php';
include_once SHARED_TYPES_PATH . 'share_type.php';
include_once DB_PATH . 'sql_db.php';
include_once DB_PATH . 'sql_par.php';
include_once DB_PATH . 'sql_par_type.php';
include_once MODEL_HELPER_PATH . 'db_object_seq_id_user.php';
include_once MODEL_PHRASE_PATH . 'phrase_type.php';
include_once MODEL_SANDBOX_PATH . 'protection_type.php';
include_once MODEL_SANDBOX_PATH . 'share_type.php';

use cfg\db\sql_par_field_list;
use cfg\db\sql_type_list;
use shared\types\protection_type as protect_type_shared;
use shared\types\share_type as share_type_shared;
use cfg\component\component;
use cfg\component\component_link;
use cfg\db\sql;
use cfg\db\sql_db;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
use cfg\db\sql_par;
use cfg\db\sql_par_type;
use cfg\db\sql_type;
use cfg\export\sandbox_exp;
use cfg\log\change;
use cfg\log\change_action;
use cfg\log\change_link;
use cfg\log\change_log;
use cfg\result\result;
use Exception;
use shared\library;

class sandbox extends db_object_seq_id_user
{

    /*
     * sandbox types
     */

    // the main types of user sandbox objects
    // TODO maybe to be replaced by class lists
    const TYPE_NAMED = 'named';  // for user sandbox objects which have a unique name like formulas
    const TYPE_LINK = 'link';    // for user sandbox objects that link two objects like formula links
    const TYPE_VALUE = 'value';  // for user sandbox objects that are used to save values


    /*
     * db const
     */

    // database and JSON object field names used in many user sandbox objects
    // the id field is not included here because it is used for the database relations and should be object specific
    // e.g. always "word_id" instead of simply "id
    // *_COM: the description of the field
    // *_SQLTYP is the sql data type used for the field
    const FLD_ID_COM = 'the owner / creator of the -=class=-';
    const FLD_ID_COM_CHANGER = 'the changer of the -=class=-';
    const FLD_ID = ''; // is always overwritten by the child class just added here to prevent polymorph warning
    const FLD_EXCLUDED_COM = 'true if a user, but not all, have removed it';
    const FLD_EXCLUDED = 'excluded';    // field name used to delete the object only for one user
    const FLD_EXCLUDED_SQLTYP = sql_field_type::BOOL;
    const FLD_CHANGE_USER = 'change_user_id'; // id of the user how wants something the object to be different from most other users
    const FLD_USER_NAME = 'user_name';
    const FLD_SHARE_COM = 'to restrict the access';
    const FLD_SHARE = "share_type_id";  // field name for the share permission
    const FLD_SHARE_SQLTYP = sql_field_type::INT_SMALL;
    const FLD_PROTECT_COM = 'to protect against unwanted changes';
    const FLD_PROTECT = "protect_id";   // field name for the protection level
    const FLD_PROTECT_SQLTYP = sql_field_type::INT_SMALL;

    // field lists for the table creation
    const FLD_ALL_OWNER = array(
        [user::FLD_ID, sql_field_type::INT, sql_field_default::NULL, sql::INDEX, user::class, self::FLD_ID_COM],
    );
    const FLD_ALL_CHANGER = array(
        [user::FLD_ID, sql_field_type::KEY_PART_INT, sql_field_default::NOT_NULL, sql::INDEX, user::class, self::FLD_ID_COM_CHANGER],
    );
    const FLD_LST_ALL = array(
        [self::FLD_EXCLUDED, self::FLD_EXCLUDED_SQLTYP, sql_field_default::NULL, '', '', self::FLD_EXCLUDED_COM],
        [self::FLD_SHARE, self::FLD_SHARE_SQLTYP, sql_field_default::NULL, '', '', self::FLD_SHARE_COM],
        [self::FLD_PROTECT, self::FLD_PROTECT_SQLTYP, sql_field_default::NULL, '', '', self::FLD_PROTECT_COM],
    );

    // numeric and user specific database field names that are user for most user sandbox objects
    const FLD_NAMES_NUM_USR_SBX = array(
        self::FLD_EXCLUDED,
        self::FLD_PROTECT
    );
    // numeric database field names that only exist in the table for the user specific data
    const FLD_NAMES_NUM_USR_ONLY_SBX = array(
        self::FLD_SHARE // the standard value is per definition share to public
    );
    // dummy arrays that should be overwritten by the child object
    const FLD_NAMES = array();
    const FLD_NAMES_USR = array();
    // database fields that should only be taken from the user sandbox table
    const FLD_NAMES_USR_ONLY = array();
    // combine FLD_NAMES_NUM_USR_SBX and FLD_NAMES_NUM_USR_ONLY_SBX just for shorter code
    const FLD_NAMES_NUM_USR = array(
        self::FLD_EXCLUDED,
        self::FLD_SHARE,
        self::FLD_PROTECT
    );
    // list of all user sandbox database types with a standard ID
    // so exclude values and result TODO check missing owner for values and results
    const DB_TYPES = array(
        word::class,
        triple::class,
        formula::class,
        formula_link::class,
        view::class,
        component::class,
        component_link::class
    );


    /*
     * object vars
     */

    // fields to define the object; should be set in the constructor of the child object
    public bool $rename_can_switch = True; // true if renaming an object can switch to another object with the new name

    // database fields that are used in all objects and that have a specific behavior
    public ?int $usr_cfg_id = null;    // the database id if there is already some user specific configuration for this object
    private user $usr;                 // the person for whom the object is loaded, so to say the viewer
    public ?int $owner_id = null;      // the user id of the person who created the object, which is the default object
    public ?int $share_id = null;      // id for public, personal, group or private
    public ?int $protection_id = null; // id for no, user, admin or full protection
    public bool $excluded = false;     // the user sandbox for object is implemented, but can be switched off for the complete instance
    // but for calculation, use and display an excluded should not be used
    // when loading the word and saving the excluded field is handled as a normal user sandbox field,
    // but for calculation, use and display an excluded should not be used

    /*
     * dummy var because many child objects use a type_id and to enable to add the common code here
     *
     * could and should be moved to a user_sandbox_type_extension object
     * as soon as php allows something like 'extends _sandbox_named and user_sandbox_type_extension'
     *
     * database id of the type used for named user sandbox objects with predefined functionality
     * such as words, formulas, values, terms and view component links
     * because all types are preloaded with the database id the name and code id can fast be received
     * the id of the source type, view type, view component type or word type
     * e.g. to classify measure words
     * the id of the source type, view type, view component type or word type e.g. to classify measure words
     * or the formula type to link special behavior to special formulas like "this" or "next"
     */
    public ?int $type_id = null;


    /*
     * construct and map
     */

    /**
     * all user sandbox object are user specific, that's why the user is always set
     * and most user sandbox objects are named object
     * but this is in many cases be overwritten by the child object
     * @param user $usr the user how has requested to see his view on the object
     */
    function __construct(user $usr)
    {
        parent::__construct($usr);
    }

    /**
     * reset the search values of this object
     * needed to search for the standard object, because the search is work, value, formula or ... specific
     */
    function reset(): void
    {
        $this->id = 0;
        $this->usr_cfg_id = null;
        $this->owner_id = null;
        $this->excluded = false;
    }

    /**
     * create a clone and empty all fields
     *
     * @return $this a clone with the name changed
     */
    function clone_reset(): sandbox
    {
        $obj_cpy = clone $this;
        $obj_cpy->reset();
        return $obj_cpy;
    }

    /**
     * map the database fields to the object fields
     * to be extended by the child object
     * the parent row_mapper function should be used for all db_objects
     * this row_mapper_sandbox function should be used for all user sandbox objects
     *
     * @param array|null $db_row with the data directly from the database
     * @param bool $load_std true if only the standard user sandbox object ist loaded
     * @param bool $allow_usr_protect false for using the standard protection settings for the default object used for all users
     * @param string $id_fld the name of the id field as set in the child class
     * @return bool true if the user sandbox object is loaded and valid
     */
    function row_mapper_sandbox(
        ?array $db_row,
        bool   $load_std = false,
        bool   $allow_usr_protect = true,
        string $id_fld = ''
    ): bool
    {
        if ($id_fld == '') {
            $id_fld = $this->id_field();
        }
        $result = parent::row_mapper($db_row, $id_fld);
        if ($result) {
            $this->owner_id = $db_row[user::FLD_ID];
            // e.g. the list of names does not include the field excluded
            // TODO instead the excluded rows are filtered out on SQL level
            if (array_key_exists(sandbox::FLD_EXCLUDED, $db_row)) {
                $this->set_excluded($db_row[self::FLD_EXCLUDED]);
            }
            if (!$load_std) {
                $this->usr_cfg_id = $db_row[sql_db::TBL_USER_PREFIX . $id_fld];
            }
            if ($allow_usr_protect) {
                $this->row_mapper_usr($db_row, $id_fld);
            } else {
                $this->row_mapper_std();
            }
        }
        return $result;
    }

    /**
     * map the standard user sandbox database fields to this user specific object
     *
     * @param array $db_row with the data loaded from the database
     * @return void
     */
    function row_mapper_usr(array $db_row): void
    {
        if (array_key_exists(self::FLD_SHARE, $db_row)) {
            $this->share_id = $db_row[self::FLD_SHARE];
        }
        if (array_key_exists(self::FLD_PROTECT, $db_row)) {
            $this->protection_id = $db_row[self::FLD_PROTECT];
        }
    }

    /**
     * map the standard user sandbox database fields to this default object for all users
     *
     * @return void
     */
    function row_mapper_std(): void
    {
        global $share_types;
        global $protection_types;
        $this->share_id = $share_types->id(share_type_shared::PUBLIC);
        $this->protection_id = $protection_types->id(protect_type_shared::NO_PROTECT);
    }


    /*
     * set and get
     */

    /**
     * set the excluded field from a database value
     * with postgres and MySQL this is pretty strait forward so more to prevent future issues
     *
     * @param bool $db_val the value from the database row array
     * @return void
     */
    function set_excluded(?bool $db_val): void
    {
        if ($db_val == null) {
            $this->excluded = false;
        } else {
            $this->excluded = $db_val;
        }
    }

    /**
     * set excluded to 'true' to switch off the usage of this user sandbox object
     * @return void
     */
    function exclude(): void
    {
        $this->excluded = true;
    }

    /**
     * set excluded to 'false' to switch on the usage of this user sandbox object
     * @return void
     */
    function include(): void
    {
        $this->excluded = false;
    }

    /**
     * @return bool true if the user does not want to use this object at all
     */
    function is_excluded(): bool
    {
        return $this->excluded;
    }


    /*
     * preloaded
     */

    /**
     * @returns string the share type code id based on the database share type id
     */
    function share_type_code_id(): string
    {
        global $share_types;
        return $share_types->code_id($this->share_id);
    }

    /**
     * @returns string the share type name based on the database share type id
     */
    function share_type_name(): string
    {
        global $share_types;

        // use the default share type if not set
        if ($this->share_id <= 0) {
            $this->share_id = $share_types->id(share_type_shared::PUBLIC);
        }

        global $share_types;
        return $share_types->name($this->share_id);
    }

    /**
     * @return string the protection type code id based on the database id
     */
    function protection_type_code_id(): string
    {
        global $protection_types;
        return $protection_types->code_id($this->protection_id);
    }

    /**
     * @return string the protection type name based on the database id
     */
    function protection_type_name(): string
    {
        global $protection_types;

        // use the default share type if not set
        if ($this->protection_id <= 0) {
            $this->protection_id = $protection_types->id(protect_type_shared::NO_PROTECT);
        }

        return $protection_types->name($this->protection_id);
    }

    /**
     * dummy function that should always be overwritten by the child object
     * @return string
     */
    function name_field(): string
    {
        return '';
    }


    /*
     * cast
     */

    /**
     * @param object $api_obj frontend API object filled with the database id
     */
    function fill_api_obj(object $api_obj): void
    {
        $api_obj->set_id($this->id());
    }

    /**
     * @return object frontend API object filled with the database id
     */
    function fill_min_obj(object $min_obj): object
    {
        $min_obj->set_id($this->id());
        return $min_obj;
    }

    /**
     * TODO deprecate
     * fill a similar object that is extended with display interface functions
     *
     * @param object $dsp_obj the object that should be filled with all user sandbox values
     */
    function fill_dsp_obj(object $dsp_obj): void
    {
        $dsp_obj->set_id($this->id());
        $dsp_obj->usr_cfg_id = $this->usr_cfg_id;
        $dsp_obj->usr = $this->user();
        $dsp_obj->owner_id = $this->owner_id;
        $dsp_obj->excluded = $this->is_excluded();
    }


    /*
     * load
     */

    /*
     * these functions differ for each object, so they are always in the child class and not this in the superclass
     *
     * private function load_standard() {}
     * function load() {}
    */

    /**
     * load one database row e.g. word, triple, formula, view or component from the database
     * for values and result the db key might be an 512-bit id or even a string
     * so for values and results the load_non_int_db_key function is used instead of this load function
     *
     * @param sql_par $qp the query parameters created by the calling function
     * @return int the id of the object found and zero if nothing is found
     */
    protected function load(sql_par $qp): int
    {
        global $db_con;

        $db_row = $db_con->get1($qp);
        $this->row_mapper_sandbox($db_row);
        return $this->id();
    }

    /**
     * load the object parameters for all users
     * @param sql_par|null $qp the query parameter created by the function of the child object e.g. word->load_standard
     * @param string $class the name of the child class from where the call has been triggered
     * @return bool true if the standard object has been loaded
     */
    function load_standard(?sql_par $qp = null, string $class = ''): bool
    {
        global $db_con;
        $result = false;

        if ($this->id <= 0) {
            log_err('The ' . $class . ' id must be set to load ' . $class, $class . '->load_standard');
        } else {
            $db_row = $db_con->get1($qp);
            $result = $this->row_mapper_sandbox($db_row, true, false);
        }
        return $result;
    }

    /**
     * create the SQL to load the single default value always by the id
     * @param sql $sc with the target db_type set
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_standard_sql(sql $sc, string $class = self::class): sql_par
    {
        $qp = new sql_par($class, new sql_type_list([sql_type::NORM]));
        $qp->name .= sql_db::FLD_ID;

        $sc->set_name($qp->name);
        $sc->set_usr($this->user()->id());
        $sc->add_where($this->id_field(), $this->id());
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * create the SQL to load the single default value always by something else than the main id
     * @param sql $sc with the target db_type set
     * @param sql_par $qp the query parameters with the class and name already set
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_standard_sql_by(sql $sc, sql_par $qp): sql_par
    {
        $qp->name .= '_std';
        $sc->set_name($qp->name);
        $sc->set_usr($this->user()->id());
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * create the SQL to load a sandbox object with numeric user specific fields
     *
     * @param sql $sc with the target db_type set
     * @param sandbox $sbx the name of the child class from where the call has been triggered
     * @param string $query_name the name extension to make the query name unique
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_usr_num(sql $sc, sandbox $sbx, string $query_name): sql_par
    {
        $lib = new library();

        $qp = new sql_par($sbx::class);
        $qp->name .= $query_name;

        $sc->set_class($sbx::class);
        $sc->set_name($qp->name);
        $sc->set_usr($this->user()->id());
        $sc->set_fields($sbx::FLD_NAMES);
        $sc->set_usr_fields($sbx::FLD_NAMES_USR);
        $sc->set_usr_num_fields($sbx::FLD_NAMES_NUM_USR);
        $sc->set_usr_only_fields($sbx::FLD_NAMES_USR_ONLY);

        return $qp;
    }

    /**
     * prepare the SQL parameter to load a single user specific value
     *
     * @param sql $sc with the target db_type set
     * @param string $query_name the name of the selection fields to make the query name unique
     * @param array $fields list of the fields from the child object
     * @param array $usr_fields list of the user specified fields from the child object
     * @param array $usr_num_fields list of the fields from the child object
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_fields(
        sql    $sc,
        string $query_name,
        array  $fields,
        array  $usr_fields,
        array  $usr_num_fields,
    ): sql_par
    {
        $qp = parent::load_sql($sc, $query_name);
        $sc->set_usr($this->user()->id());
        $sc->set_fields($fields);
        $sc->set_usr_fields($usr_fields);
        $sc->set_usr_num_fields($usr_num_fields);

        return $qp;
    }

    /**
     * function that must be overwritten by the child object
     * @return array with all field names of the user sandbox object excluding the prime id field
     */
    protected function all_sandbox_fields(): array
    {
        return array();
    }

    /**
     * create the SQL to load a single user specific value
     * TODO replace by load_sql_usr or load_sql_usr_num
     *
     * @param sql $sc with the target db_type set
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_obj_vars(sql $sc, string $class): sql_par
    {
        return new sql_par($class);
    }

    function load_owner(): bool
    {
        global $db_con;
        $result = false;

        if ($this->id > 0) {

            // TODO: try to avoid using load_test_user
            if ($this->owner_id > 0) {
                $usr = new user;
                if ($usr->load_by_id($this->owner_id)) {
                    $this->set_user($usr);
                    $result = true;
                }
            } else {
                // take the ownership if it is not yet done. The ownership is probably missing due to an error in an older program version.
                $db_con->set_class($this::class);
                $db_con->set_usr($this->user()->id());
                if ($db_con->update_old($this->id, user::FLD_ID, $this->user()->id())) {
                    $result = true;
                }
            }
        }
        return $result;
    }

    /**
     * dummy function to get the missing objects from the database that is always overwritten by the child class
     * @returns bool  false if the loading has failed
     */
    function load_objects(): bool
    {
        log_err('The dummy parent method get_similar has been called, which should never happen');
        return true;
    }


    /*
     * im- and export
     */

    /**
     * function to import the core user sandbox object values from a json string
     * e.g. the share and protection settings
     *
     * @param array $in_ex_json an array with the data of the json object
     * @param object|null $test_obj if not null the unit test object to get a dummy seq id
     * @return user_message the status of the import and if needed the error messages that should be shown to the user
     */
    function import_obj(array $in_ex_json, object $test_obj = null): user_message
    {
        global $share_types;
        global $protection_types;

        $result = parent::import_db_obj($this, $test_obj);
        foreach ($in_ex_json as $key => $value) {
            if ($key == share_type_shared::JSON_FLD) {
                $this->share_id = $share_types->id($value);
                if ($this->share_id < 0) {
                    $lib = new library();
                    $result->add_message('share type ' . $value . ' is not expected when importing ' . $lib->dsp_array($in_ex_json));
                }
            }
            if ($key == protect_type_shared::JSON_FLD) {
                $this->protection_id = $protection_types->id($value);
                if ($this->protection_id < 0) {
                    $lib = new library();
                    $result->add_message('protection type ' . $value . ' is not expected when importing ' . $lib->dsp_array($in_ex_json));
                }
            }
        }
        return $result;
    }

    /**
     * create an object for the export which does not include the internal references
     * to be overwritten by the child object
     *
     * @return sandbox_exp a reduced export object that can be used to create a JSON message
     */
    function export_obj(): sandbox_exp
    {
        log_warning($this::class . ' does not have an expected instance of the export_obj function');
        return (new sandbox_exp());
    }


    /*
     * information
     */

    /**
     * check if the sandbox object in the database needs to be updated
     *
     * @param sandbox $db_obj the word as saved in the database
     * @return bool true if this word has infos that should be saved in the datanase
     */
    function needs_db_update_sandbox(sandbox $db_obj): bool
    {
        $result = false;
        if ($this->owner_id != null) {
            if ($this->owner_id != $db_obj->owner_id) {
                $result = true;
            }
        }
        if ($this->share_id != null) {
            if ($this->share_id != $db_obj->share_id) {
                $result = true;
            }
        }
        if ($this->protection_id != null) {
            if ($this->protection_id != $db_obj->protection_id) {
                $result = true;
            }
        }
        // TODO what about excluded?
        return $result;
    }

    /**
     * @param sql_db $db_con
     * @return sql_par sql parameter to get the user id of the most often used link (position) beside the standard (position)
     */
    function median_user_sql(sql_db $db_con): sql_par
    {
        $qp = new sql_par($this::class);
        $qp->name .= 'median_user';
        if ($this->owner_id > 0) {
            $qp->name .= '_ex_owner';
        }
        $db_con->set_class($this::class, true);
        $db_con->set_name($qp->name);
        $db_con->set_usr($this->user()->id());
        $db_con->set_fields(array(user::FLD_ID));
        $qp->sql = $db_con->select_by_id_not_owner($this->id);

        $qp->par = $db_con->get_par();

        return $qp;
    }

    /**
     * @returns int the user id of the most often used link (position) beside the standard (position)
     * TODO review, because the median is not taking into account the number of standard used values
     */
    function median_user(): int
    {
        log_debug($this->dsp_id() . ' beside the owner (' . $this->owner_id . ')');

        global $db_con;
        $result = 0;

        $qp = $this->median_user_sql($db_con);
        $db_row = $db_con->get1($qp);
        if ($db_row[user::FLD_ID] > 0) {
            $result = $db_row[user::FLD_ID];
        } else {
            if ($this->owner_id > 0) {
                $result = $this->owner_id;
            } else {
                if ($this->user()->id() > 0) {
                    $result = $this->user()->id();
                }
            }
        }
        log_debug('for ' . $this->dsp_id() . ': ' . $result);
        return $result;
    }


    /*
     * owner and access
     */

    /**
     * if the user is an admin the user can force to be the owner of this object
     * TODO review
     */
    function take_ownership(): bool
    {
        $result = false;
        log_debug($this->dsp_id());

        if ($this->user()->is_admin()) {
            // TODO activate Prio 3 $result .= $this->usr_cfg_create_all();
            $result = $this->set_owner($this->user()->id()); // TODO remove double getting of the user object
            // TODO activate Prio 3 $result .= $this->usr_cfg_cleanup();
        }

        log_debug($this->dsp_id() . ' done');
        return $result;
    }

    /**
     * change the owner of the object
     * any calling function should make sure that taking setting the owner is allowed
     * and that all user values
     * TODO review sql and object field compare of user and standard
     */
    function set_owner(int $new_owner_id): bool
    {
        log_debug($this->dsp_id() . ' to ' . $new_owner_id);

        global $db_con;
        $result = true;

        if ($this->id > 0 and $new_owner_id > 0) {
            // to recreate the calling object
            $std = clone $this;
            $std->reset();
            $std->id = $this->id;
            $std->set_user($this->user());
            $std->load_standard();

            $db_con->set_class($this::class);
            $db_con->set_usr($this->user()->id());
            if (!$db_con->update_old($this->id, user::FLD_ID, $new_owner_id)) {
                $result = false;
            }

            $this->owner_id = $new_owner_id;
            $new_owner = new user;
            if ($new_owner->load_by_id($new_owner_id)) {
                $this->set_user($new_owner);
            } else {
                $result = false;
            }

            log_debug('for ' . $this->dsp_id() . ' to ' . $new_owner_id . ': number of db updates: ' . $result);
        }
        return $result;
    }

    /**
     * @return bool true if no other user has modified the object
     * assuming that in this case no confirmation from the other users for an object change is needed
     */
    function not_changed(): bool
    {
        $result = true;
        log_debug($this->id . ' by someone else than the owner ' . $this->owner_id);

        $other_usr_id = $this->changer();
        if ($other_usr_id > 0) {
            $result = false;
        }

        log_debug($this->id . ' is ' . zu_dsp_bool($result));
        return $result;
    }

    /**
     * true if no one has used the object
     * TODO if this has been used for calculation, this is also used
     */
    function not_used(): bool
    {
        $result = true;
        log_debug($this->id);

        $using_usr_id = $this->median_user();
        if ($using_usr_id > 0) {
            $result = false;
        }

        log_debug(zu_dsp_bool($result));
        return $result;
    }

    /**
     * create an SQL statement to get all the users that have changed this value
     * TODO deprecate
     * @param sql_db $db_con
     * @return sql_par
     */
    function load_sql_changer_old(sql_db $db_con): sql_par
    {
        $qp = new sql_par($this::class);
        $qp->name .= 'changer';
        if ($this->owner_id > 0) {
            $qp->name .= '_ex_owner';
        }
        $db_con->set_class($this::class, true);
        $db_con->set_name($qp->name);
        $db_con->set_usr($this->user()->id());
        $db_con->set_fields(array(user::FLD_ID));
        $qp->sql = $db_con->select_by_id_not_owner($this->id, $this->owner_id);

        $qp->par = $db_con->get_par();

        return $qp;
    }

    /**
     * if the object has been changed by someone else than the owner the user id is returned
     * but only return the user id if the user has not also excluded it
     * @returns int the user id of someone who has changed the object, but is not owner
     */
    function changer(): int
    {
        log_debug($this->dsp_id());

        global $db_con;

        $user_id = 0;
        $db_con->set_class($this::class);
        $db_con->set_usr($this->user()->id());
        //$qp = $this->load_sql_changer($db_con->sql_creator());
        $qp = $this->load_sql_changer_old($db_con);
        $db_row = $db_con->get1($qp);
        if ($db_row) {
            $user_id = $db_row[user::FLD_ID];
        }

        log_debug('is ' . $user_id);
        return $user_id;
    }

    /**
     * create an SQL statement to get a list of all user that have ever changed the object
     * @param sql $sc with the target db_type set
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_of_users_that_changed(sql $sc): sql_par
    {
        $lib = new library();

        $qp = new sql_par($this::class);
        $qp->name .= 'user_list';

        $class = $lib->class_to_name($this::class);
        $sc->set_class($class, new sql_type_list([sql_type::USER]));
        $sc->set_name($qp->name);
        $sc->set_usr($this->user()->id());
        $sc->set_join_fields(
            array_merge(array(user::FLD_ID, user::FLD_NAME), user::FLD_NAMES_LIST),
            user::class,
            user::FLD_ID,
            user::FLD_ID);
        $sc->add_where($this->id_field(), $this->id());
        $sc->add_where(sandbox::FLD_EXCLUDED, 1, sql_par_type::INT_NOT_OR_NULL);

        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * @return user_list a list of all user that have ever changed the object
     */
    function changed_by(): user_list
    {
        log_debug($this->dsp_id());

        global $db_con;

        $usr_id_lst = array();
        $result = new user_list($this->user());

        // add object owner
        $usr_id_lst[] = $this->owner_id;
        $qp = $this->load_sql_of_users_that_changed($db_con->sql_creator());
        $db_usr_lst = $db_con->get($qp);
        foreach ($db_usr_lst as $db_usr) {
            if ($db_usr[user::FLD_ID] > 0) {
                $usr_id_lst[] = $db_usr[user::FLD_ID];
            }
        }
        $result->load_by_ids($db_con, $usr_id_lst);

        return $result;
    }

    /**
     * true if no else one has used the object
     * TODO if this should be true if no one else has been used this object e.g. for calculation
     */
    function used_by_someone_else(): bool
    {
        $result = true;
        log_debug($this->id);

        log_debug('owner is ' . $this->owner_id . ' and the change is requested by ' . $this->user()->id());
        if ($this->owner_id == $this->user()->id() or $this->owner_id <= 0) {
            $changer_id = $this->changer();
            // removed "OR $changer_id <= 0" because if no one has changed the object jet does not mean that it can be changed
            log_debug('changer is ' . $changer_id . ' and the change is requested by ' . $this->user()->id());
            if ($changer_id == $this->user()->id() or $changer_id <= 0) {
                $result = false;
            }
        }

        log_debug(': ' . zu_dsp_bool($result));
        return $result;
    }

    /**
     * @return bool true if the user is the owner and no one else has changed the object
     *              because if another user has changed the object and the original value is changed,
     *              maybe the user object also needs to be updated
     */
    function can_change(): bool
    {
        $result = false;

        // if the user who wants to change it, is the owner, he can do it
        // or if the owner is not set, he can do it (and the owner should be set, because every object should have an owner)
        log_debug('owner is ' . $this->owner_id . ' and the change is requested by ' . $this->user()->id());
        if ($this->owner_id == $this->user()->id() or $this->owner_id <= 0) {
            $result = true;
        }

        log_debug($this::class . zu_dsp_bool($result));
        return $result;
    }


    /*
     * sandbox
     */

    /**
     * @return bool true if a record for a user specific configuration already exists in the database
     */
    function has_usr_cfg(): bool
    {
        $result = false;
        if ($this->usr_cfg_id > 0) {
            $result = true;
        }

        log_debug(zu_dsp_bool($result));
        return $result;
    }

    /**
     * simply remove a user adjustment without check
     * log a system error if a technical error has occurred
     *
     * @return bool true if user sandbox row has successfully been deleted
     */
    function del_usr_cfg_exe($db_con): bool
    {
        log_debug($this->dsp_id() . ' und user ' . $this->user()->name);
        $lib = new library();
        $class_name = $lib->class_to_name($this::class);

        $result = false;
        $action = 'Deletion of user ' . $class_name . ' ';
        $msg_failed = $this->id . ' failed for ' . $this->user()->name;

        $db_con->set_class($this::class, true);
        try {
            if ($this->sql_write_prepared()) {
                $qp = $this->sql_delete($db_con->sql_creator(), new sql_type_list([sql_type::USER]));
                $usr_msg = $db_con->delete($qp, $this::class . ' user exclusions');
                $msg = $usr_msg->get_message();
            } else {
                $msg = $db_con->delete_old(
                    array($this->id_field(), user::FLD_ID),
                    array($this->id, $this->user()->id()));
            }
            if ($msg == '') {
                $this->usr_cfg_id = null;
                $result = true;
            } else {
                log_err($action . $msg_failed . ' because ' . $msg);
            }
        } catch (Exception $e) {
            log_err($action . $msg_failed . ' because ' . $e);
        }
        return $result;
    }

    /**
     * remove user adjustment and log it (used by user.php to undo the user changes)
     */
    function del_usr_cfg(): bool
    {
        log_debug($this->dsp_id());
        $lib = new library();
        $class_name = $lib->class_to_name($this::class);

        global $db_con;
        $result = true;

        if ($this->id > 0 and $this->user()->id() > 0) {
            $log = $this->log_del();
            if ($log->id() > 0) {
                $db_con->usr_id = $this->user()->id();
                $result = $this->del_usr_cfg_exe($db_con);
            }

        } else {
            log_err('The database ID and the user must be set to remove a user specific modification of ' . $class_name . '.', $this::class . '->del_usr_cfg');
        }

        return $result;
    }

    /**
     * create a database record to save user specific settings for a user sandbox object
     * TODO combine the reread and the adding in a commit transaction; same for all db change transactions
     * @return bool false if the creation has failed and true if it was successful or not needed
     */
    protected function add_usr_cfg(string $class = self::class): bool
    {
        global $db_con;
        $result = true;
        $lib = new library();
        $class_name = $lib->class_to_name($this::class);
        $class = $lib->class_to_name($class);


        if (!$this->has_usr_cfg()) {
            if ($this->is_named_obj()) {
                log_debug('for "' . $this->dsp_id() . ' und user ' . $this->user()->name);
            } elseif ($this->is_link_obj()) {
                if (isset($this->fob) and isset($this->tob)) {
                    log_debug('for "' . $this->fob->name . '"/"' . $this->tob->name . '" by user "' . $this->user()->name . '"');
                } else {
                    log_debug('for "' . $this->id . '" and user "' . $this->user()->name . '"');
                }
            } else {
                log_err('Unknown user sandbox type ' . $class_name . ' in ' . $this::class, $this::class . '->log_add');
            }

            // check again if there ist not yet a record
            $db_con->set_class($this::class, true);
            $qp = new sql_par($class);
            $qp->name = $class . '_add_usr_cfg';
            $db_con->set_name($qp->name);
            $db_con->set_usr($this->user()->id());
            $db_con->set_where_std($this->id);
            $qp->sql = $db_con->select_by_set_id();
            $qp->par = $db_con->get_par();
            $db_row = $db_con->get1($qp);
            if ($db_row != null) {
                $this->usr_cfg_id = $db_row[$this->id_field()];
            }
            if (!$this->has_usr_cfg()) {
                // create an entry in the user sandbox
                $db_con->set_class($this::class, true);
                $db_con->set_usr($this->user()->id());
                $log_id = $db_con->insert_old(array($this->id_field(), user::FLD_ID), array($this->id, $this->user()->id()));
                if ($log_id <= 0) {
                    log_err('Insert of ' . sql_db::USER_PREFIX . $this::class . ' failed.');
                    $result = false;
                } else {
                    $this->usr_cfg_id = $log_id;
                }
            }
        }
        return $result;
    }

    /**
     * create an SQL statement to retrieve the user changes of the current object
     *
     * @param sql $sc with the target db_type set
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_user_changes(sql $sc, string $class = self::class): sql_par
    {
        $qp = new sql_par($class);
        $qp->name .= 'usr_cfg';
        $sc->set_name($qp->name);
        $sc->set_usr($this->user()->id());
        $sc->set_fields($this->all_sandbox_fields());
        $sc->add_where($this->id_field(), $this->id());
        $sc->add_where(user::FLD_ID, $this->user()->id());
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();
        return $qp;
    }

    /**
     * check again if there ist not yet a record
     * @return bool true if the user has done some personal changes on this object
     */
    protected function check_usr_cfg(): bool
    {
        global $db_con;
        $result = false;

        log_debug('for "' . $this->dsp_id() . ' und user ' . $this->user()->dsp_id());

        // check again if there ist not yet a record
        $qp = $this->load_sql_user_changes($db_con->sql_creator());
        $db_con->usr_id = $this->user()->id();
        $db_row = $db_con->get1($qp);
        if ($db_row != null) {
            $this->usr_cfg_id = $db_row[$this->id_field()];
            if ($this->has_usr_cfg()) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * check if the database record for the user specific settings can be removed
     * TODO separate the query parameter creation and add a unit test
     * @return bool false if the deletion has failed and true if it was successful or not needed
     */
    protected function del_usr_cfg_if_not_needed(): bool
    {

        global $db_con;
        $result = true;

        // TODO check if next line is working
        //if ($this->has_usr_cfg) {

        // check again if there ist not yet a record
        $qp = $this->load_sql_user_changes($db_con->sql_creator());
        $db_con->usr_id = $this->user()->id();
        $usr_cfg_row = $db_con->get1($qp);
        if ($usr_cfg_row) {
            log_debug('check for "' . $this->dsp_id() . ' und user ' . $this->user()->name . ' with (' . $qp->sql . ')');
            if ($usr_cfg_row[$this->id_field()] > 0) {
                if ($this->no_usr_fld_used($this->all_sandbox_fields(), $usr_cfg_row)) {
                    $result = $this->del_usr_cfg_exe($db_con);
                }
            }
        }
        //}

        // don't throw an error message if another account has removed the user sandbox row in the meantime
        if (!$this->has_usr_cfg()) {
            $result = true;
        }

        return $result;
    }

    /**
     * check if the database row with the user specific data is still needed
     *
     * @param array $fld_lst all potential user specific fields of the object
     * @param array $db_row the database record of the user table
     * @return bool true if no field contain any user overwrite
     */
    protected function no_usr_fld_used(array $fld_lst, array $db_row): bool
    {
        $result = true;
        foreach ($fld_lst as $field_name) {
            if ($db_row[$field_name] != '') {
                $result = false;
            }
        }
        return $result;
    }

    /**
     * remove all user setting that are not needed any more based on the new standard object
     * TODO review
     */
    function usr_cfg_cleanup(sandbox $std): string
    {
        $result = '';
        log_debug($this->dsp_id());

        // get a list of users that have a user cfg of this object
        $usr_lst = $this->changed_by();
        foreach ($usr_lst as $usr) {
            // remove the usr cfg if not needed any more
            $this->del_usr_cfg_if_not_needed($this->id_field(), $this->all_sandbox_fields());
        }

        log_debug('for ' . $this->dsp_id() . ': ' . $result);
        return $result;
    }


    /*
     * log
     */

    /**
     * set the log entry parameter for a new named object
     * for all not named objects like links, this function is overwritten
     * e.g. that the user can see "added formula 'scale millions' to word 'mio'"
     */
    function log_add(): change
    {
        log_debug($this->dsp_id());
        $lib = new library();
        $class_name = $lib->class_to_name($this::class);

        $log = new change($this->user());

        $log->action = change_action::ADD;
        // TODO add the table exceptions from sql_db
        $log->set_table($class_name . sql_db::TABLE_EXTENSION);
        $log->row_id = 0;
        $log->add();

        return $log;
    }

    /**
     * set the log entry parameter for a new link object
     */
    function log_link_add(): change_link
    {
        log_err('The dummy parent method get_similar has been called, which should never happen');
        return new change_link($this->user());
    }

    /**
     * set the main log entry parameters for updating one field
     */
    private function log_upd_common($log)
    {
        log_debug($this->dsp_id());
        $lib = new library();
        $class_name = $lib->class_to_name($this::class);
        $log->set_user($this->user());
        $log->action = change_action::UPDATE;
        if ($this->can_change()) {
            // TODO add the table exceptions from sql_db
            $log->set_table($class_name . sql_db::TABLE_EXTENSION);
        } else {
            $log->set_table(sql_db::TBL_USER_PREFIX . $class_name . sql_db::TABLE_EXTENSION);
        }

        return $log;
    }

    /**
     * create a log object for an update of an object field
     */
    function log_upd_field(): change
    {
        log_debug($this->dsp_id());
        $log = new change($this->user());
        return $this->log_upd_common($log);
    }

    /**
     * create a log object for an update of link
     */
    function log_upd_link(): change_link
    {
        log_debug($this->dsp_id());
        $log = new change_link($this->user());
        return $this->log_upd_common($log);
    }

    /**
     * create a log object for an update of an object field or a link
     * e.g. that the user can see "moved formula list to position 3 in phrase view"
     */
    function log_upd()
    {
        log_debug($this->dsp_id());
        if ($this->is_named_obj()) {
            $log = $this->log_upd_field();
        } else {
            $log = $this->log_upd_link();
        }
        return $this->log_upd_common($log);
    }

    /**
     * dummy function definition that will be overwritten by the child object
     * @return change_link
     */
    function log_del_link(): change_link
    {
        log_err('The dummy parent method get_similar has been called, which should never happen');
        return new change_link($this->user());
    }

    /**
     * dummy function definition that will be overwritten by the child object
     * @return change
     */
    function log_del(): change
    {
        log_err('The dummy parent method get_similar has been called, which should never happen');
        return new change($this->user());
    }

    /**
     * dummy function definition that will be overwritten by the child object
     * check if this object uses any preserved names and if return a message to the user
     * @return string
     */
    protected function check_preserved(): string
    {
        log_err('The dummy parent method get_similar has been called, which should never happen');
        return '';
    }


    /*
     * save fields
     */

    /**
     * update all fields and create or delete the user overwrite database row if needed
     * TODO activate and use it
     *
     * @param sql_db $db_con the active database connection that should be used
     * @param sandbox $db_obj this object with the variables set in the database before the update
     * @param sandbox $norm_obj this object with the variables of the norm set as in the database before the update
     * @return user_message if anything fails the message for the user to fix the issue
     */
    function save_all_fields(sql_db $db_con, sandbox $db_obj, sandbox $norm_obj): user_message
    {
        // always return a user message and if everything is fine, it is just empty
        $usr_msg = new user_message();
        // the sql creator is used more than once, so create it upfront
        $sc = $db_con->sql_creator();
        // if the user is allowed to change the norm row e.g. because no other user has used it, change the norm row directly
        if ($this->can_change()) {
            // if there is no difference between the user row and the norm row remove all fields from the user row
            if ($this->no_diff($norm_obj)) {
                if ($this->has_usr_cfg()) {
                    $qp = $this->sql_delete($sc, new sql_type_list([sql_type::USER]));
                    $usr_msg->add($db_con->delete($qp, 'remove user overwrites of ' . $this->dsp_id()));
                }
                // check if some user overwrites can be removed
                $this->del_usr_cfg_if_not_needed(); // don't care what the result is, because in most cases it is fine to keep the user sandbox row
            } else {
                // apply the changes directly to the norm db record
                $qp = $this->sql_update($sc, $norm_obj);
                $usr_msg->add($db_con->update($qp, 'update ' . $this->dsp_id()));
            }
        } else {
            // if the norm row should not be changed by the user, create a user sandbox row if needed
            if (!$this->has_usr_cfg()) {
                if (!$this->add_usr_cfg()) {
                    $usr_msg->add_message('creation of user sandbox for ' . $this->dsp_id() . ' failed');
                }
            }
            if ($usr_msg->is_ok()) {
                if ($this->no_diff($norm_obj)) {
                    if ($this->has_usr_cfg()) {
                        $qp = $this->sql_delete($sc, new sql_type_list([sql_type::USER]));
                        $usr_msg->add($db_con->delete($qp, 'remove user overwrites of ' . $this->dsp_id()));
                    }
                } else {
                    // apply the changes directly to the norm db record
                    $qp = $this->sql_update($sc, $norm_obj, new sql_type_list([sql_type::USER]));
                    $usr_msg->add($db_con->update($qp, 'update user row for ' . $this->dsp_id()));
                }
                // check if some user overwrites can be removed
                $this->del_usr_cfg_if_not_needed(); // don't care what the result is, because in most cases it is fine to keep the user sandbox row
            }
        }
        return $usr_msg;
    }

    /**
     * dummy function to save all updated word fields, which is always overwritten by the child class
     */
    function save_fields(sql_db $db_con, sandbox $db_rec, sandbox $std_rec): string
    {
        return '';
    }

    /**
     * actually update a field in the main database record or the user sandbox
     * the usr id is taken into account in sql_db->update (maybe move outside)
     * @param sql_db $db_con the active database connection that should be used
     * @param change|change_link $log the log object to track the change and allow a rollback
     * @return string an empty string if everything is fine or the message that should be shown to the user
     */
    function save_field_user(sql_db $db_con, change|change_link $log): string
    {
        $result = '';

        if ($log->new_id > 0) {
            $new_value = $log->new_id;
            $std_value = $log->std_id;
        } else {
            $new_value = $log->new_value;
            $std_value = $log->std_value;
        }
        if ($log->add()) {
            if ($this->can_change()) {
                if ($new_value == $std_value) {
                    if ($this->has_usr_cfg()) {
                        log_debug('remove user change');
                        $db_con->set_class($this::class, true);
                        $db_con->set_usr($this->user()->id());
                        if (!$db_con->update_old($this->id, $log->field(), Null)) {
                            $result = 'remove of ' . $log->field() . ' failed';
                        }
                    }
                    $this->del_usr_cfg_if_not_needed(); // don't care what the result is, because in most cases it is fine to keep the user sandbox row
                } else {
                    $db_con->set_class($this::class);
                    $db_con->set_usr($this->user()->id());
                    if (!$db_con->update_old($this->id, $log->field(), $new_value)) {
                        $result = 'update of ' . $log->field() . ' to ' . $new_value . ' failed';
                    }
                }
            } else {
                if (!$this->has_usr_cfg()) {
                    if (!$this->add_usr_cfg()) {
                        $result = 'creation of user sandbox for ' . $log->field() . ' failed';
                    }
                }
                if ($result == '') {
                    $db_con->set_class($this::class, true);
                    $db_con->set_usr($this->user()->id());
                    if ($new_value == $std_value) {
                        log_debug('remove user change');
                        if (!$db_con->update_old($this->id, $log->field(), Null)) {
                            $result = 'remove of user value for ' . $log->field() . ' failed';
                        }
                    } else {
                        if (!$db_con->update_old($this->id, $log->field(), $new_value)) {
                            $result = 'update of user value for ' . $log->field() . ' to ' . $new_value . ' failed';
                        }
                    }
                    $this->del_usr_cfg_if_not_needed(); // don't care what the result is, because in most cases it is fine to keep the user sandbox row
                }
            }
        }
        return $result;
    }

    /**
     * actually update a field in the main database record
     * without user the user sandbox
     * the usr id is taken into account in sql_db->update (maybe move outside)
     * @param sql_db $db_con the active database connection that should be used
     * @param change|change_link $log the log object to track the change and allow a rollback
     * @return string an empty string if everything is fine or the message that should be shown to the user
     */
    function save_field(sql_db $db_con, change|change_link $log): string
    {
        $result = '';

        if ($log->new_id > 0) {
            $new_value = $log->new_id;
        } else {
            $new_value = $log->new_value;
        }
        if ($log->add()) {
            $db_con->set_class($this::class);
            $db_con->set_usr($this->user()->id());
            if (!$db_con->update_old($this->id, $log->field(), $new_value)) {
                $result = 'update of value for ' . $log->field() . ' to ' . $new_value . ' failed';
            }
        }
        return $result;
    }

    /**
     * detects if this object has be changed compared to the given object
     *
     * @param sandbox $db_obj the user database or standard record for compare
     * @return bool true if any of the fields does not match
     */
    function no_diff(sandbox $db_obj): bool
    {
        if (count($this->db_fields_changed($db_obj)) > 0) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @param sandbox $db_rec the object as saved in the database before the change
     * @return change_log the log object predefined for excluding
     */
    function save_field_excluded_log(sandbox $db_rec): change_log
    {
        $log = new change_log($this->user());
        if ($db_rec->is_excluded() <> $this->is_excluded()) {
            if ($this->is_excluded()) {
                if ($this->is_link_obj()) {
                    $log = $this->log_del_link();
                } else {
                    $log = $this->log_del();
                }
            } else {
                if ($this->is_link_obj()) {
                    $log = $this->log_link_add();
                } else {
                    $log = $this->log_add();
                }
            }
        }
        $log->set_field(self::FLD_EXCLUDED);
        return $log;
    }

    /**
     * set the update parameters for the value excluded
     * @param sql_db $db_con the active database connection that should be used
     * @param sandbox $db_rec the object as saved in the database before this field is updated
     * @param sandbox $std_rec the default object without user specific changes
     * returns false if something has gone wrong
     */
    function save_field_excluded(sql_db $db_con, sandbox $db_rec, sandbox $std_rec): string
    {
        log_debug($this->dsp_id());
        $result = '';
        $lib = new library();
        $class_name = $lib->class_to_name($this::class);

        if ($db_rec->is_excluded() <> $this->is_excluded()) {
            $log = $this->save_field_excluded_log($db_rec);
            $new_value = $this->is_excluded();
            $std_value = $std_rec->is_excluded();
            // similar to $this->save_field_do
            if ($this->can_change()) {
                $db_con->set_class($this::class);
                $db_con->set_usr($this->user()->id());
                if (!$db_con->update_old($this->id, $log->field(), $new_value)) {
                    $result .= 'excluding of ' . $class_name . ' failed';
                }
            } else {
                if (!$this->has_usr_cfg()) {
                    if (!$this->add_usr_cfg()) {
                        $result = 'creation of user sandbox to exclude failed';
                    }
                }
                if ($result == '') {
                    $db_con->set_class($this::class, true);
                    $db_con->set_usr($this->user()->id());
                    if ($new_value == $std_value) {
                        if (!$db_con->update_old($this->id, $log->field(), Null)) {
                            $result .= 'include of ' . $class_name . ' for user failed';
                        }
                    } else {
                        if (!$db_con->update_old($this->id, $log->field(), $new_value)) {
                            $result .= 'excluding of ' . $class_name . ' for user failed';
                        }
                    }
                    if (!$this->del_usr_cfg_if_not_needed()) {
                        $result .= ' and user sandbox cannot be cleaned';
                    }
                }
            }
        }
        return $result;
    }

    /**
     * save the share level in the database if allowed
     * @param sql_db $db_con the active database connection that should be used
     * @param sandbox $db_rec the object as saved in the database before this field is updated
     * @param sandbox $std_rec the default object without user specific changes
     * @return string the message that should be shown to the user
     */
    function save_field_share(sql_db $db_con, sandbox $db_rec, sandbox $std_rec): string
    {
        log_debug($this->dsp_id());
        $result = '';

        if ($db_rec->share_id <> $this->share_id) {
            $log = $this->log_upd_field();
            $log->old_value = $db_rec->share_type_name();
            $log->old_id = $db_rec->share_id;
            $log->new_value = $this->share_type_name();
            $log->new_id = $this->share_id;
            // TODO is the setting of the standard needed?
            $log->std_value = $std_rec->share_type_name();
            $log->std_id = $std_rec->share_id;
            $log->row_id = $this->id;
            $log->set_field(self::FLD_SHARE);

            // save_field_do is not used because the share type can only be set on the user record
            if ($log->new_id > 0) {
                $new_value = $log->new_id;
                $std_value = $log->std_id;
            } else {
                $new_value = $log->new_value;
                $std_value = $log->std_value;
            }
            if ($log->add()) {
                if (!$this->has_usr_cfg()) {
                    if (!$this->add_usr_cfg()) {
                        $result = 'creation of user sandbox for share type failed';
                    }
                }
                if ($result == '') {
                    $db_con->set_class($this::class, true);
                    $db_con->set_usr($this->user()->id());
                    if (!$db_con->update_old($this->id, $log->field(), $new_value)) {
                        $result = 'setting of share type failed';
                    }
                }
            }
        }

        log_debug($this->dsp_id());
        return $result;
    }

    /**
     * save the protection level in the database if allowed
     * TODO is the setting of the standard needed?
     */
    function save_field_protection(sql_db $db_con, sandbox $db_rec, sandbox $std_rec): string
    {
        $result = '';
        log_debug($this->dsp_id());

        if ($db_rec->protection_id <> $this->protection_id) {
            $log = $this->log_upd_field();
            $log->old_value = $db_rec->protection_type_name();
            $log->old_id = $db_rec->protection_id;
            $log->new_value = $this->protection_type_name();
            $log->new_id = $this->protection_id;
            $log->std_value = $std_rec->protection_type_name();
            $log->std_id = $std_rec->protection_id;
            $log->row_id = $this->id;
            $log->set_field(self::FLD_PROTECT);
            $result .= $this->save_field_user($db_con, $log);
        }

        log_debug($this->dsp_id());
        return $result;
    }


    /*
     * save id
     */

    /**
     * dummy function definition that will be overwritten by the child objects
     * check if the id parameters are supposed to be changed
     * @param sandbox $db_rec the object data as it is now in the database
     * @return bool true if one of the object id fields have been changed
     */
    function is_id_updated(sandbox $db_rec): bool
    {
        return false;
    }

    /**
     * check if target key value already exists
     * overwritten in the word class for formula link words
     * TODO load the user value not the standard value but also check the standard value
     *
     * @return sandbox object with id zero if no object with the same id is found
     */
    function get_obj_with_same_id_fields(): sandbox
    {
        log_debug('check if target already exists ' . $this->dsp_id());
        $db_chk = clone $this;
        $db_chk->id = 0; // to force the load by the id fields
        $db_chk->load_standard(); // TODO should not ADDITIONAL the user specific load be called
        return $db_chk;
    }

    /**
     * @return string text that request the user to use another name
     * overwritten in the word class for formula link words
     */
    function msg_id_already_used(): string
    {
        return '';
    }

    /**
     * check if the id parameters are supposed to be changed
     * and change the id (which can start a longer lasting confirmation process)
     *
     * The possible cases are
     * 1. the new name is not used by the user and not used for the standard -> simply rename it
     * 2. the new name is not used by the user but     used for the standard -> join with the standard word
     * 3. the new name is     used by the user but not used for the standard -> send a warning to the user and offer join
     * 4. the new name is     used by the user and     used for the standard -> send a warning to the user and offer join
     *
     * @param sql_db $db_con the active database connection
     * @param sandbox $db_rec the database record before the saving
     * @param sandbox $std_rec the database record defined as standard because it is used by most users
     * @returns string an empty string if everything is fine or a messages for the user what should be changed
     */
    function save_id_if_updated(sql_db $db_con, sandbox $db_rec, sandbox $std_rec): string
    {
        log_debug($this->dsp_id());
        $result = '';
        $lib = new library();
        $class_name = $lib->class_to_name($this::class);

        if ($this->is_id_updated($db_rec)) {

            // check the preserved names
            $result = $this->check_preserved();

            if ($result == '') {
                $db_chk = $this->get_obj_with_same_id_fields();
                if ($db_chk->id <> 0) {
                    log_debug('target already exists');
                    if ($this->rename_can_switch) {
                        // ... if yes request to delete or exclude the record with the id parameters before the change
                        $to_del = clone $db_rec;
                        $msg = $to_del->del();
                        if (!$msg->is_ok()) {
                            $result .= 'Failed to delete the unused ' . $class_name;
                        }
                        if ($result = '') {
                            // .. and use it for the update
                            // TODO review the logging: from the user view this is a change not a delete and update
                            $this->id = $db_chk->id;
                            $this->owner_id = $db_chk->owner_id;
                            // TODO check which links needs to be updated, because this is a kind of combine objects
                            // force the include again
                            $this->include();
                            $db_rec->exclude();
                            $result .= $this->save_field_excluded($db_con, $db_rec, $std_rec);
                            if ($result == '') {
                                log_debug('found a ' . $class_name . ' target ' . $db_chk->dsp_id() . ', so del ' . $db_rec->dsp_id() . ' and add ' . $this->dsp_id());
                            } else {
                                //$result = 'Failed to exclude the unused ' . $this-::class;
                                $result .= 'A ' . $class_name . ' with the name "' . $this->name() . '" already exists. Please use another name or merge with this ' . $class_name . '.';
                            }
                        }
                    } else {
                        $result .= $this->msg_id_already_used();
                    }
                } else {
                    log_debug('target does not yet exist');
                    // TODO check if e.g. for word links and formula links "and $this->not_used()" needs to be added
                    if ($this->can_change()) {
                        // in this case change is allowed and done
                        log_debug('change the existing ' . $class_name . ' ' . $this->dsp_id() . ' (db ' . $db_rec->dsp_id() . ', standard ' . $std_rec->dsp_id() . ')');
                        // TODO check if next line is needed
                        //$this->load_objects();
                        if ($this->is_link_obj()) {
                            $result .= $this->save_id_fields_link($db_con, $db_rec, $std_rec);
                        } elseif ($this->is_named_obj()) {
                            $result .= $this->save_id_fields($db_con, $db_rec, $std_rec);
                        } else {
                            log_info('Save of id field for ' . $class_name . ' not expected');
                        }
                    } else {
                        // if the target link has not yet been created
                        // ... request to delete the old
                        $to_del = clone $db_rec;
                        $msg = $to_del->del();
                        if (!$msg->is_ok()) {
                            $result .= 'Failed to delete the unused ' . $this::class;
                        }
                        // TODO .. and create a deletion request for all users ???

                        if ($result = '') {
                            // ... and create a new display component link
                            $this->id = 0;
                            $this->owner_id = $this->user()->id();
                            $result .= $this->add()->get_last_message();
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * dummy function that is supposed to be overwritten by the child classes for e.g. named or link objects
     *
     * updated the object id fields (e.g. for a word or formula the name, and for a link the linked ids)
     * should only be called if the user is the owner and nobody has used the display component link
     * @param sql_db $db_con the active database connection
     * @param sandbox $db_rec the database record before the saving
     * @param sandbox $std_rec the database record defined as standard because it is used by most users
     * @returns string either the id of the updated or created source or a message to the user with the reason, why it has failed
     */
    function save_id_fields(sql_db $db_con, sandbox $db_rec, sandbox $std_rec): string
    {
        log_warning($this->dsp_id());
        return '';
    }


    /*
     * similar
     */

    /**
     * dummy function that is supposed to be overwritten by the child classes for e.g. named or link objects
     *
     * check if the unique key (not the db id) of two user sandbox object is the same if the object type is the same, so the simple case
     * @param object $obj_to_check the object used for the comparison
     * @return bool true if the objects represent the same
     */
    function is_same_std(object $obj_to_check): bool
    {
        return false;
    }

    /**
     * check that the given object is by the unique keys the same as the actual object
     * handles the specials case that for each formula a corresponding word is created (which needs to be checked if this is really needed)
     * so if a formula word "millions" is not the same as the standard word "millions" because the formula word "millions" is representing a formula which should not be combined
     * in short: if two objects are the same by this definition, they are supposed to be merged
     */
    function is_same($obj_to_check): bool
    {
        global $phrase_types;

        $result = false;

        /*
        if ($this::class == word::class and $obj_to_check::class == formula::class) {
            // special case if word should be created representing the formula it is a kind of same at least the creation of the word should be alloed
            if ($this->name == $obj_to_check->name) {
                $result = true;
            }
        } elseif ($this::class == word::class and $obj_to_check::class == word::class) {

        */
        if ($this::class == word::class) {
            // special case a word should not be combined with a word that is representing a formulas
            if ($this->name() == $obj_to_check->name()) {
                if (isset($this->type_id) and isset($obj_to_check->type_id)) {
                    if ($this->type_id == $obj_to_check->type_id) {
                        $result = true;
                    } else {
                        if ($obj_to_check::class == formula::class
                            and $this->type_id == $phrase_types->id(phrase_type::FORMULA_LINK)) {
                            // if one is a formula and the other is a formula link word, the two objects are representing the same formula object (but the calling function should use the formula to update)
                            $result = true;
                        } elseif ($this->type_id == $phrase_types->id(phrase_type::FORMULA_LINK)
                            or $obj_to_check->type_id == $phrase_types->id(phrase_type::FORMULA_LINK)) {
                            // if one of the two words is a formula link and not both, the user should ge no suggestion to combine them
                            $result = false;
                        } else {
                            // a measure word can be combined with a measure scale word
                            $result = true;
                        }
                    }
                } else {
                    log_debug('The type_id of the two objects to compare are not set');
                }
            }
        } elseif ($this::class == $obj_to_check::class) {
            $result = $this->is_same_std($obj_to_check);
        }
        return $result;
    }

    /**
     * just to double-check if the get similar function is working correctly
     * so if the formulas "millions" is compared with the word "millions" this function returns true
     * in short: if two objects are similar by this definition, they should not be both in the database
     * @param null|object $obj_to_check the object used for the comparison
     * @return bool true if the objects represent the same
     */
    function is_similar(?object $obj_to_check): bool
    {
        $result = false;
        if ($obj_to_check != null) {
            //
            if ($this::class == $obj_to_check::class) {
                $result = $this->is_same_std($obj_to_check);
            } else {
                // create a synthetic unique index over words, phrase, verbs and formulas
                if ($this::class == word::class
                    or $this::class == triple::class
                    or $this::class == formula::class
                    or $this::class == verb::class) {
                    if ($this->name() == $obj_to_check->name()) {
                        $result = true;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * dummy function that is supposed to be overwritten by the child classes for e.g. named or link objects
     *
     * check if an object with the unique key already exists
     * returns null if no similar object is found
     * or returns the object with the same unique key that is not the actual object
     * any warning or error message needs to be created in the calling function
     * e.g. if the user tries to create a formula named "millions"
     *      but a word with the same name already exists, a term with the word "millions" is returned
     *      in this case the calling function should suggest the user to name the formula "scale millions"
     *      to prevent confusion when writing a formula where all words, phrases, verbs and formulas should be unique
     * @returns sandbox a filled object that has the same name or links the same objects
     *                  or a sandbox object with id() = 0 if nothing similar has been found
     */
    function get_similar(): sandbox
    {
        log_err('The dummy parent method get_similar has been called, which should never happen');
        return new sandbox($this->user());
    }


    /*
     * add
     */

    /**
     * dummy function that is supposed to be overwritten by the child classes for e.g. named or link objects
     * @param bool $use_func if true a predefined function is used that also creates the log entries
     * @return user_message with status ok
     *                      or if something went wrong
     *                      the message that should be shown to the user
     *                      including suggested solutions
     */
    function add(bool $use_func = false): user_message
    {
        $result = new user_message();
        $msg = 'The dummy parent add function has been called, which should never happen';
        log_err($msg);
        $result->add_message($msg);
        return $result;
    }

    /*
     * save
     *
     * a word rename creates a new word and a word deletion request
     * a word is deleted after all users have confirmed
     * words with an active deletion request are listed at the end
     * a word can have a formula linked
     * values and formulas can be linked to a word, a triple or a word group
     * verbs needs a confirmation for creation (but the name can be reserved)
     * all other parameters beside the word/verb name can be user specific
     *
     * time words are separated from the word groups to reduce the number of word groups
     * for daily data or shorter a normal date or time field is used
     * a time word can also describe a period
     */

    /**
     * add or update a user sandbox object (word, value, formula or ...) in the database
     * returns either the id of the updated or created object or a message with the reason why it has failed that can be shown to the user
     *
     * the save used cases are
     *
     * 1. a source is supposed to be saved without id and         a name  and no source                with the same name already exists -> add the source
     * 2. a source is supposed to be saved without id and         a name, but  a source                with the same name already exists -> ask the user to confirm the changes or use another name (at the moment simply update)
     * 3. a word   is supposed to be saved without id and         a name  and no word, verb or formula with the same name already exists -> add the word
     * 4. a word   is supposed to be saved without id and         a name, but  a word                  with the same name already exists -> ask the user to confirm the changes or use another name (at the moment simply update)
     * 5. a word   is supposed to be saved without id and         a name, but  a verb or formula       with the same name already exists -> ask the user to use another name (or rename the formula)
     * 6. a source is supposed to be saved with    id and a changed name -> the source is supposed to be renamed -> check if the new name is already used -> (6a.) if yes,            ask to merge, change the name or cancel the update -> (6b.) if the new name does not exist, ask the user to confirm the changes
     * 7. a word   is supposed to be saved with    id and a changed name -> the word   is supposed to be renamed -> check if the new name is already used -> (7a.) if yes for a word, ask to merge, change the name or cancel the update -> (7b.) if the new name does not exist, ask the user to confirm the changes
     *                                                                                                                                                         -> (7c.) if yes for a verb, ask to        change the name or cancel the update
     * TODO add wizards to handle the update chains
     * TODO check also that a word does not match any generated triple name
     * TODO check also that a word does not match any user name (or find a solution for each user namespace)
     * TODO return a user_message with a suggested solution instead of a string
     *
     * @param bool|null $use_func if true a predefined function is used that also creates the log entries
     */

    function save(?bool $use_func = null): string
    {
        log_debug($this->dsp_id());

        global $db_con;

        $result = '';

        $lib = new library();
        $class_name = $lib->class_to_name($this::class);

        // decide which db write method should be used
        if ($use_func === null) {
            if (in_array($this::class, sql_db::CLASSES_USE_WITH_LOG_FUNC_FOR_SAVE)) {
                $use_func = true;
            } else {
                $use_func = false;
            }
        }

        // load the objects if needed
        if ($this->is_link_obj()) {
            $this->load_objects();
        }

        // configure the global database connection object for the select, insert, update and delete queries
        $db_con->set_class($this::class);
        $db_con->set_usr($this->user()->id());

        // create an object to check possible duplicates
        $similar = null;

        // if a new object is supposed to be added check upfront for a similar object to prevent adding duplicates
        if ($this->id() == 0) {
            log_debug('check possible duplicates before adding ' . $this->dsp_id());
            $similar = $this->get_similar();
            if ($similar->id() <> 0) {
                // check that the get_similar function has really found a similar object and report potential program errors
                if (!$this->is_similar($similar)) {
                    $result .= $this->dsp_id() . ' seems to be not similar to ' . $similar->dsp_id();
                } else {
                    // if similar is found set the id to trigger the updating instead of adding
                    $similar->load_by_id($similar->id, $similar::class); // e.g. to get the type_id
                    // prevent that the id of a formula is used for the word with the type formula link
                    if (get_class($this) == get_class($similar)) {
                        $this->id = $similar->id;
                    } else {
                        if (!((get_class($this) == word::class and get_class($similar) == formula::class)
                            or (get_class($this) == triple::class and get_class($similar) == formula::class))) {
                            $result = $similar->id_used_msg($this);
                        }
                    }
                }
            } else {
                $similar = null;
            }

        }

        // create a new object if nothing similar has been found
        if ($result == '') {
            if ($this->id == 0) {

                // check the preserved names
                $result = $this->check_preserved();

                if ($result == '') {
                    log_debug('add');
                    $result = $this->add($use_func)->get_last_message();
                }

            } else {
                // if the similar object is not the same as $this object, suggest renaming $this object
                if ($similar != null) {
                    log_debug('got similar and suggest renaming or merge');
                    // e.g. if a source already exists update the source
                    // but if a word with the same name of a formula already exists suggest a new formula name
                    if (!$this->is_same($similar)) {
                        $result = $similar->id_used_msg($this);
                    }
                }

                // update the existing object
                if ($result == '') {
                    log_debug('update');

                    // read the database values to be able to check if something has been changed;
                    // done first, because it needs to be done for user and general object values
                    $db_rec = clone $this;
                    $db_rec->reset();
                    $db_rec->set_user($this->user());
                    if ($db_rec->load_by_id($this->id, $db_rec::class) != $this->id()) {
                        $result .= 'Reloading of user ' . $class_name . ' failed';
                    } else {
                        log_debug('reloaded from db');
                        if ($this->is_link_obj()) {
                            if (!$db_rec->load_objects()) {
                                $result .= 'Reloading of the object for ' . $class_name . ' failed';
                            }
                            // configure the global database connection object again to overwrite any changes from load_objects
                            $db_con->set_class($this::class);
                            $db_con->set_usr($this->user()->id());
                        }
                        // relevant is if there is a user config in the database
                        // so use this information to prevent
                        // the need to forward the db_rec to all functions
                        if ($db_rec->has_usr_cfg() and !$this->has_usr_cfg()) {
                            $this->usr_cfg_id = $db_rec->usr_cfg_id;
                        }
                    }

                    // load the common object
                    $std_rec = clone $this;
                    $std_rec->reset();
                    $std_rec->id = $this->id;
                    $std_rec->set_user($this->user()); // must also be set to allow to take the ownership
                    if ($result == '') {
                        if (!$std_rec->load_standard()) {
                            $result .= 'Reloading of the default values for ' . $class_name . ' failed';
                        }
                    }

                    // for a correct user setting detection (function can_change) set the owner even if the object has not been loaded before the save
                    if ($result == '') {
                        log_debug('standard loaded');

                        if ($this->owner_id <= 0) {
                            $this->owner_id = $std_rec->owner_id;
                        }
                    }

                    // check if the id parameters are supposed to be changed
                    if ($result == '') {
                        // TODO for the prepared update just revered the name if not allowed
                        $result .= $this->save_id_if_updated($db_con, $db_rec, $std_rec);
                    }

                    // if a problem has appeared up to here, don't try to save the values
                    // the problem is shown to the user by the calling interactive script
                    if ($result == '') {
                        // TODO activate when the prepared SQL is ready to use
                        //if (!$this->sql_write_prepared()) {
                        $result .= $this->save_fields($db_con, $db_rec, $std_rec);
                        //} else {
                        //    $result .= $this->save_all_fields($db_con, $db_rec, $std_rec)->get_last_message();
                        //}
                    }
                }
            }
            if ($result != '') {
                log_warning($result, 'user_sandbox_' . $class_name . '->save');
            }
        }

        return $result;
    }


    /*
     * delete
     */

    /**
     * delete the complete object (the calling function del must have checked that no one uses this object)
     * @returns string the message that should be shown to the user if something went wrong or an empty string if everything is fine
     */
    private function del_exe(): string
    {
        log_debug($this->dsp_id());
        $lib = new library();
        $class_name = $lib->class_to_name($this::class);

        global $db_con;
        global $phrase_types;

        $msg = '';
        $result = new user_message();

        // log the deletion request
        if ($this->is_link_obj()) {
            $log = $this->log_del_link();
        } else {
            $log = $this->log_del();
        }
        if ($log->id() > 0) {
            $db_con->usr_id = $this->user()->id();

            // for words first delete all links
            if ($this::class == word::class) {
                $msg = $this->del_links();
                $result->add($msg);
            }

            // for triples first delete all links
            if ($this::class == triple::class) {
                $msg = $this->del_links();
                $result->add($msg);
            }

            // for formulas first delete all links
            if ($this::class == formula::class) {
                $msg = $this->del_links();
                $result->add($msg);

                // and the corresponding formula elements
                if ($result->is_ok()) {
                    $db_con->set_class(element::class);
                    $db_con->set_usr($this->user()->id());
                    $msg = $db_con->delete_old($this->id_field(), $this->id);
                    $result->add_message($msg);
                }

                // and the corresponding results
                if ($result->is_ok()) {
                    $db_con->set_class(result::class);
                    $db_con->set_usr($this->user()->id());
                    $msg = $db_con->delete_old($this->id_field(), $this->id);
                    $result->add_message($msg);
                }

                // and the corresponding word if possible
                if ($result->is_ok()) {
                    $wrd = new word($this->user());
                    $wrd->load_by_name($this->name());
                    $wrd->type_id = $phrase_types->id(phrase_type::FORMULA_LINK);
                    $msg = $wrd->del();
                    $result->add($msg);
                }

            }

            // for view components first delete all links
            if ($this::class == component::class) {
                $msg = $this->del_links();
                $result->add($msg);
            }

            // for views first delete all links
            if ($this::class == view::class) {
                $msg = $this->del_links();
                $result->add($msg);
            }

            // delete first all user configuration that have also been excluded
            if ($result->is_ok()) {
                if ($this->sql_write_prepared()) {
                    $sc = $db_con->sql_creator();
                    $qp = $this->sql_delete($sc, new sql_type_list([sql_type::USER, sql_type::EXCLUDE]));
                    $msg = $db_con->delete($qp, $this::class . ' user exclusions');
                    $result->add($msg);
                } else {
                    $db_con->set_class($this::class, true);
                    $db_con->set_usr($this->user()->id());
                    $msg = $db_con->delete_old(
                        array($class_name . sql_db::FLD_EXT_ID, 'excluded'),
                        array($this->id, '1'));
                    $result->add_message($msg);
                }
            }
            if ($result->is_ok()) {
                // finally, delete the object
                if ($this->sql_write_prepared()) {
                    $sc = $db_con->sql_creator();
                    $qp = $this->sql_delete($sc);
                    $msg = $db_con->delete($qp, $this::class . ' user exclusions');
                    $result->add($msg);
                } else {
                    $db_con->set_class($this::class);
                    $db_con->set_usr($this->user()->id());
                    $msg = $db_con->delete_old($this->id_field(), $this->id);
                    $result->add_message($msg);
                }
                log_debug('of ' . $this->dsp_id() . ' done');
            } else {
                log_err('Delete failed for ' . $class_name, $this::class . '->del_exe', 'Delete failed, because removing the user settings for ' . $class_name . ' ' . $this->dsp_id() . ' returns ' . $msg, (new Exception)->getTraceAsString(), $this->user());
            }
        }

        return $result->get_last_message();
    }

    /**
     * exclude or delete an object
     * @return user_message with status ok
     *                      or if something went wrong
     *                      the message that should be shown to the user
     *                      including suggested solutions
     *
     * TODO if the owner deletes it, change the owner to the new median user
     * TODO check if all have deleted the object
     *      does not remove the user excluding if no one else is using it
     */
    function del(): user_message
    {
        log_debug($this->dsp_id());
        $lib = new library();
        $class_name = $lib->class_to_name($this::class);

        global $db_con;
        $result = new user_message();
        $msg = '';

        // refresh the object with the database to include all updates utils now (TODO start of lock for commit here)
        // TODO it seems that the owner is not updated
        $reloaded = false;
        $reloaded_id = $this->load_by_id($this->id(), $this::class);
        if ($reloaded_id != 0) {
            $reloaded = true;
        }

        if (!$reloaded) {
            log_warning('Reload of for deletion has lead to unexpected', $class_name . '->del', 'Reload of ' . $class_name . ' ' . $this->dsp_id() . ' for deletion or exclude has unexpectedly lead to ' . $msg . '.', (new Exception)->getTraceAsString(), $this->user());
        } else {
            log_debug('reloaded ' . $this->dsp_id());
            // check if the object is still valid
            if ($this->id <= 0) {
                log_warning('Delete failed', $class_name . '->del', 'Delete failed, because it seems that the ' . $class_name . ' ' . $this->dsp_id() . ' has been deleted in the meantime.', (new Exception)->getTraceAsString(), $this->user());
            } else {
                // reload the objects if needed
                if ($this->is_link_obj()) {
                    if (!$this->load_objects()) {
                        $msg .= 'Reloading of linked objects ' . $class_name . ' ' . $this->dsp_id() . ' failed.';
                    }
                }
                // check if the object simply can be deleted, because it has never been used
                if (!$this->used_by_someone_else()) {
                    $msg .= $this->del_exe();
                } else {
                    // if the owner deletes the object find a new owner or delete the object completely
                    if ($this->owner_id == $this->user()->id()) {
                        log_debug('owner has requested the deletion');
                        // get median user
                        $new_owner_id = $this->median_user();
                        if ($new_owner_id == 0) {
                            log_err('Delete failed', $class_name . '->del', 'Delete failed, because no median user found for ' . $class_name . ' ' . $this->dsp_id() . ' but change is nevertheless not allowed.', (new Exception)->getTraceAsString(), $this->user());
                        } else {
                            log_debug('set owner for ' . $this->dsp_id() . ' to user id "' . $new_owner_id . '"');

                            // TODO change the original object, so that it uses the configuration of the new owner

                            // set owner
                            if (!$this->set_owner($new_owner_id)) {
                                $msg .= 'Setting of owner while deleting ' . $class_name . ' failed';
                                log_err($msg, $class_name . '->del');

                            }

                            // delete all user records of the new owner
                            // does not use del_usr_cfg because the deletion request has already been logged
                            if ($msg == '') {
                                if (!$this->del_usr_cfg_exe($db_con)) {
                                    $msg .= 'Deleting of ' . $class_name . ' failed';
                                }
                            }

                        }
                    }
                    // check again after the owner change if the object simply can be deleted, because it has never been used
                    // TODO check if "if ($this->can_change() AND $this->not_used()) {" would be correct
                    if (!$this->used_by_someone_else()) {
                        log_debug('can delete ' . $this->dsp_id() . ' after owner change');
                        $msg .= $this->del_exe();
                    } else {
                        log_debug('exclude ' . $this->dsp_id());
                        $this->exclude();

                        // simple version TODO combine with save function

                        $db_rec = clone $this;
                        $db_rec->reset();
                        $db_rec->set_user($this->user());
                        if ($db_rec->load_by_id($this->id, $db_rec::class)) {
                            log_debug('reloaded ' . $db_rec->dsp_id() . ' from database');
                            if ($this->is_link_obj()) {
                                if (!$db_rec->load_objects()) {
                                    $msg .= 'Reloading of linked objects ' . $class_name . ' ' . $this->dsp_id() . ' failed.';
                                }
                            }
                        }
                        if ($msg == '') {
                            $std_rec = clone $this;
                            $std_rec->reset();
                            $std_rec->id = $this->id;
                            $std_rec->set_user($this->user()); // must also be set to allow to take the ownership
                            if (!$std_rec->load_standard()) {
                                $msg .= 'Reloading of standard ' . $class_name . ' ' . $this->dsp_id() . ' failed.';
                            }
                        }
                        if ($msg == '') {
                            log_debug('loaded standard ' . $std_rec->dsp_id());
                            $msg .= $this->save_field_excluded($db_con, $db_rec, $std_rec);
                        }
                    }
                }
            }
            // TODO end of db commit and unlock the records
            log_debug('done');
        }

        $result->add_message($msg);
        return $result;
    }

    /**
     * @return string a message to use a different name
     */
    function id_used_msg(sandbox $obj_to_add): string
    {
        $lib = new library();
        $class_name = $lib->class_to_name($this::class);
        $obj_to_add_name = $lib->class_to_name($obj_to_add::class);
        return 'A ' . $class_name . ' with the name "' . $obj_to_add->name() . '" already exists. '
            . 'Please use another ' . $obj_to_add_name . ' name.';
    }

    /**
     * dummy function to remove depending on objects, which needs to be overwritten by the child classes
     */
    function del_links(): user_message
    {
        return new user_message();
    }


    /*
     * type field
     *
     * functions that are not used in this object, but in many child objects
     * and that is predefined in the main parent object to avoid code redundancy
     *
     * could and should be moved to a user_sandbox_type_extension object
     * as soon as php allows something like 'extends _sandbox_named and user_sandbox_type_extension'
     */

    /**
     * set the update parameters for the word, triple, formula, view or component type
     * TODO review
     * TODO: log the ref
     * TODO: save the reference also in the log
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @param sandbox $db_rec the database record before the saving
     * @param sandbox $std_rec the database record defined as standard because it is used by most users
     * @return string if not empty the message that should be shown to the user
     */
    function save_field_type(
        sql_db  $db_con,
        sandbox $db_rec,
        sandbox $std_rec
    ): string
    {
        $result = '';
        if ($db_rec->type_id <> $this->type_id) {
            if ($this::class == triple::class) {
                $log = $this->log_upd_field();
            } else {
                $log = $this->log_upd();
            }
            $log->old_value = $db_rec->type_name();
            $log->old_id = $db_rec->type_id;
            $log->new_value = $this->type_name();
            $log->new_id = $this->type_id;
            $log->std_value = $std_rec->type_name();
            $log->std_id = $std_rec->type_id;
            $log->row_id = $this->id;
            // special case just to shorten the field name
            if ($this::class == formula_link::class) {
                $log->set_field(formula_link::FLD_TYPE);
            } elseif ($this::class == word::class) {
                $log->set_field(phrase::FLD_TYPE);
            } elseif ($this::class == triple::class) {
                $log->set_field(phrase::FLD_TYPE);
            } else {
                $lib = new library();
                $log->set_field($lib->class_to_name($this::class) . sql_db::FLD_EXT_TYPE_ID);
            }
            $result .= $this->save_field_user($db_con, $log);
            log_debug('changed type to "' . $log->new_value . '" (from ' . $log->new_id . ')');
        }
        return $result;
    }

    /**
     * dummy function that should be overwritten by the child object
     * @return string the name of the object type
     */
    function type_name(): string
    {
        $msg = 'ERROR: the type name function should have been overwritten by the child object';
        return log_err($msg);
    }


    /*
     * sql write
     */

    /**
     * update the sandbox object in the database
     *
     * @param string $msg the message shown to the user in case of a problem to idemtify the update
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return user_message the message and potential solution shown to the user in case of a problem
     */
    function insert(string $msg = '', sql_type_list $sc_par_lst = new sql_type_list([])): user_message
    {
        global $db_con;

        // set the actual class before accessing the database to ...
        log_debug($msg);
        $sc = $db_con->sql_creator();
        $db_con->set_class($this::class, $sc_par_lst->is_usr_tbl());
        $sc = $db_con->sql_creator();
        $qp = $this->sql_insert($sc, $sc_par_lst);
        return $db_con->insert($qp, $msg);
    }

    /**
     * update the sandbox object in the database
     *
     * @param string $msg the message shown to the user in case of a problem to idemtify the update
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return user_message the message and potential solution shown to the user in case of a problem
     */
    function update(string $msg = '', sql_type_list $sc_par_lst): user_message
    {
        global $db_con;

        // set the actual class before accessing the database to ...
        log_debug($msg);
        $sc = $db_con->sql_creator();
        $db_con->set_class($this::class, $sc_par_lst->is_usr_tbl());
        // TODO check if needed
        $db_con->usr_id = $this->user_id();
        $sc = $db_con->sql_creator();
        // reload the database row to prevent failures due to caching
        $db_row = clone $this;
        $db_row->load_by_id($this->id());
        $qp = $this->sql_update($sc, $db_row, $sc_par_lst);
        return $db_con->update($qp, $msg);
    }

    /**
     * create the sql statement to insert a sandbox object in the database
     * dummy function to be overwritten by the child object
     *
     * @param sql $sc with the target db_type set
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par the SQL insert statement, the name of the SQL statement and the parameter list
     */
    function sql_insert(sql $sc, sql_type_list $sc_par_lst = new sql_type_list([])): sql_par
    {
        log_err('sql_insert is probably missing for ' . $this::class);
        return new sql_par('');
    }

    /**
     * create the sql statement to update a word in the database
     * dummy function to be overwritten by the child object
     *
     * @param sql $sc with the target db_type set
     * @param sandbox $db_row the sandbox object with the database values before the update
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par the SQL insert statement, the name of the SQL statement and the parameter list
     */
    function sql_update(sql $sc, sandbox $db_row, sql_type_list $sc_par_lst = new sql_type_list([])): sql_par
    {
        log_err('sql_update is probably missing for ' . $this::class);
        return new sql_par('');
    }

    /**
     * create the sql statement to delete a word in the database (not exclude!)
     * dummy function to be overwritten by the child object
     *
     * @param sql $sc with the target db_type set
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par the SQL insert statement, the name of the SQL statement and the parameter list
     */
    function sql_delete(sql $sc, sql_type_list $sc_par_lst = new sql_type_list([])): sql_par
    {
        return new sql_par('');
    }

    /**
     * @return bool true if for this database and class
     *              a prepared script including the write to the log
     *              for db write should be used
     */
    function sql_use_script_with_log(): bool
    {
        if (in_array($this::class, sql_db::DB_WRITE_LOG_SCRIPT_CLASSES)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool true if for this database and class a prepared script for db write should be used
     */
    function sql_write_prepared(): bool
    {
        if (in_array($this::class, sql_db::DB_WRITE_PREPARED)) {
            return true;
        } else {
            return false;
        }
    }


    /*
     * sql write fields
     */

    /**
     * repeat the all_db_fields function in the parent object to prevent polymorph warning
     * @return array list of all database field names that have been updated
     */
    function db_fields_all_sandbox(): array
    {
        return [
            self::FLD_EXCLUDED,
            self::FLD_SHARE,
            self::FLD_PROTECT
        ];
    }

    /**
     * get a list of database field names, values and types that have been changed compared to a given object
     * to add to the list with the list of the child object e.g. word
     * the last_update field is excluded here because this is an internal only field
     *
     * @param sandbox $sbx the same sandbox as this to compare which fields have been changed
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par_field_list with the field names of the object and any child object
     */
    function db_changed_sandbox_list(sandbox $sbx, sql_type_list $sc_par_lst): sql_par_field_list
    {
        global $change_table_list;
        global $change_field_list;

        $lst = new sql_par_field_list();
        $sc = new sql();
        $do_log = $sc_par_lst->and_log();
        $lib = new library();
        $table_id = $sc->table_id($this::class);

        if ($sbx->excluded <> $this->excluded) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . self::FLD_EXCLUDED,
                    $change_field_list->id($table_id . self::FLD_EXCLUDED),
                    change::FLD_FIELD_ID_SQLTYP
                );
            }
            $lst->add_field(
                self::FLD_EXCLUDED,
                $this->excluded,
                self::FLD_EXCLUDED_SQLTYP,
                $sbx->excluded
            );
        }
        if ($sbx->share_id <> $this->share_id) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . self::FLD_SHARE,
                    $change_field_list->id($table_id . self::FLD_SHARE),
                    change::FLD_FIELD_ID_SQLTYP
                );
            }
            $lst->add_field(
                self::FLD_SHARE,
                $this->share_id,
                self::FLD_SHARE_SQLTYP,
                $sbx->share_id
            );
        }
        if ($sbx->protection_id <> $this->protection_id) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . self::FLD_PROTECT,
                    $change_field_list->id($table_id . self::FLD_PROTECT),
                    change::FLD_FIELD_ID_SQLTYP
                );
            }
            $lst->add_field(
                self::FLD_PROTECT,
                $this->protection_id,
                self::FLD_PROTECT_SQLTYP,
                $sbx->protection_id
            );
        }
        return $lst;
    }


    /*
     * sql create
     */

    /**
     * the sql statement to create the tables of a sandbox object
     *
     * @param sql $sc with the target db_type set
     * @return string the sql statement to create the table
     */
    function sql_table(sql $sc): string
    {
        $sql = $sc->sql_separator();
        $sql .= $this->sql_table_create($sc, new sql_type_list([sql_type::SANDBOX]));
        $sc->set_class($this::class, new sql_type_list([sql_type::USER]));
        $sql .= $this->sql_table_create($sc, new sql_type_list([sql_type::SANDBOX, sql_type::USER]));
        return $sql;
    }

    /**
     * the sql statement to create the database indices of a sandbox object
     *
     * @param sql $sc with the target db_type set
     * @return string the sql statement to create the indices
     */
    function sql_index(sql $sc): string
    {
        $sql = $sc->sql_separator();
        $sql .= $this->sql_index_create($sc, new sql_type_list([sql_type::SANDBOX]));
        $sc->set_class($this::class, new sql_type_list([sql_type::USER]));
        $sql .= $this->sql_index_create($sc, new sql_type_list([sql_type::SANDBOX, sql_type::USER]));
        return $sql;
    }

    /**
     * the sql statement to create the foreign keys of a sandbox object
     *
     * @param sql $sc with the target db_type set
     * @return string the sql statement to create the foreign keys
     */
    function sql_foreign_key(sql $sc): string
    {
        $sql = $sc->sql_separator();
        $sql .= $this->sql_foreign_key_create($sc, new sql_type_list([sql_type::SANDBOX]));
        $sc->set_class($this::class, new sql_type_list([sql_type::USER]));
        $sql .= $this->sql_foreign_key_create($sc, new sql_type_list([sql_type::SANDBOX, sql_type::USER]));
        return $sql;
    }


    /*
     * sql write
     */

    /**
     * create the sql statement to add a new sandbox object e.g. word, triple or value to the database
     * TODO add qp merge
     *
     * @param sql $sc with the target db_type set
     * @param sql_par_field_list $fvt_lst list of field names, values and sql types additional to the standard id and name fields
     * @param array $fld_lst_all list of field names of the given object
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par the SQL insert statement, the name of the SQL statement and the parameter list
     */
    function sql_insert_named(
        sql                $sc,
        sql_par_field_list $fvt_lst,
        array              $fld_lst_all = [],
        sql_type_list      $sc_par_lst = new sql_type_list([])): sql_par
    {
        // check the parameters
        $lib = new library();

        // create the main query parameter object and set the name
        $and_log = $sc_par_lst->and_log();
        $fld_chg_ext = $lib->sql_field_ext($fvt_lst->names(), $fld_lst_all);
        $ext = sql::file_sep . sql::file_insert;
        if ($and_log) {
            $ext .= sql_type::LOG->extension();
        }
        $ext .= sql::file_sep . $fld_chg_ext;
        $qp = $this->sql_common($sc, $sc_par_lst, $ext);

        if ($and_log) {
            // log functions must always use named parameters
            $sc_par_lst->add(sql_type::NAMED_PAR);
            $qp = $this->sql_insert_named_and_log($sc, $qp, $fvt_lst, $fld_lst_all, $sc_par_lst);
        } else {
            // add the child object specific fields and values
            $qp->sql = $sc->create_sql_insert($fvt_lst);
            $qp->par = $fvt_lst->values();
        }

        return $qp;
    }

    /**
     * create the sql statement to add a new named sandbox object e.g. add a word to the database
     * TODO add qp merge
     *
     * @param sql $sc sql creator with the target db_type already set
     * @param sql_par_field_list $fvt_lst list of field names, values and sql types additional to the standard id fields
     * @param array $fld_lst_all list of all potential field names of the given object that can be changed by the user
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par the SQL insert statement, the name of the SQL statement and the parameter list
     */
    function sql_insert_named_and_log(
        sql                $sc,
        sql_par            $qp,
        sql_par_field_list $fvt_lst,
        array              $fld_lst_all = [],
        sql_type_list      $sc_par_lst = new sql_type_list([])
    ): sql_par
    {
        // set some var names to shorten the code lines
        $usr_tbl = $sc_par_lst->is_usr_tbl();
        $ext = sql::file_sep . sql::file_insert;
        $id_field = $sc->id_field_name();
        $var_name_row_id = $sc->var_name_row_id($sc_par_lst);

        // add the change action field to the field list for the log entries
        global $change_action_list;
        $fvt_lst->add_field(
            change_action::FLD_ID,
            $change_action_list->id(change_action::ADD),
            type_object::FLD_ID_SQLTYP
        );

        // list of parameters actually used in order of the function usage
        $par_lst_out = new sql_par_field_list();

        // init the function body
        $id_fld_new = $sc->var_name_new_id($sc_par_lst);
        $sql = $sc->sql_func_start($id_fld_new, $sc_par_lst);

        // don't use the log parameter for the sub queries
        $sc_par_lst_sub = $sc_par_lst->remove(sql_type::LOG);
        $sc_par_lst_sub->add(sql_type::LIST);
        $sc_par_lst_log = clone $sc_par_lst_sub;

        // create sql to set the prime key upfront to get the sequence id
        if (!$usr_tbl) {
            $qp_id = clone $qp;
            $qp_id = $this->sql_insert_key_field($sc, $qp_id, $fvt_lst, $id_fld_new, $sc_par_lst_sub);
            $par_lst_out->add($qp_id->par_fld);
            $sql .= $qp_id->sql;
        }

        // get the data fields and move the unique db key field to the first entry
        $fld_lst_ex_log = array_intersect($fvt_lst->names(), $fld_lst_all);
        if ($usr_tbl) {
            $key_fld_pos = array_search($this->id_field(), $fld_lst_ex_log);
            unset($fld_lst_ex_log[$key_fld_pos]);
            $key_fld_pos = array_search(user::FLD_ID, $fld_lst_ex_log);
            unset($fld_lst_ex_log[$key_fld_pos]);
            $fld_lst_ex_log_and_key = $fld_lst_ex_log;
        } else {
            $key_fld_pos = array_search($this->name_field(), $fld_lst_ex_log);
            unset($fld_lst_ex_log[$key_fld_pos]);
            $fld_lst_ex_log_and_key = array_merge([$qp_id->par_fld->name], $fld_lst_ex_log);
        }

        // create the query parameters for the single log entries
        $qp_log = $sc->sql_func_log($this::class, $this->user(), $fld_lst_ex_log_and_key, $fvt_lst, $sc_par_lst_log);
        $sql .= ' ' . $qp_log->sql;
        $par_lst_out->add_list($qp_log->par_fld_lst);

        if ($usr_tbl) {
            // insert a new row in the user table
            $fld_lst_ex_log_and_key = array_merge([$this->id_field(), user::FLD_ID], $fld_lst_ex_log);
            $fvt_lst_ex_log_and_key = $fvt_lst->get_intersect($fld_lst_ex_log_and_key);
            $sc_insert = clone $sc;
            $qp_insert = $this->sql_common($sc_insert, $sc_par_lst_sub);
            $sc_par_lst_sub->add(sql_type::NO_ID_RETURN);
            $qp_insert->sql = $sc_insert->create_sql_insert($fvt_lst_ex_log_and_key, $sc_par_lst_sub);
            // add the insert row to the function body and close the with statement with an ";"
            $sql .= ' ' . $qp_insert->sql . ';';
        } else {
            // update the fields excluding the unique id
            $update_fields = array_values($fld_lst_ex_log);
            $update_values = [];
            foreach ($fld_lst_ex_log as $fld) {
                $update_values[] = $fvt_lst->get_value($fld);
            }
            $update_types = [];
            foreach ($update_values as $val) {
                $update_types[] = $sc->get_sql_par_type($val);
            }
            $update_fld_val_typ_lst = [];
            foreach ($update_fields as $key => $field) {
                $update_fld_val_typ_lst[] = [$field, $update_values[$key], $update_types[$key]];
            }
            $sc_update = clone $sc;
            $sc_par_lst_upd = $sc_par_lst;
            $sc_par_lst_upd->add(sql_type::UPDATE);
            $sc_par_lst_upd_ex_log = $sc_par_lst_upd->remove(sql_type::LOG);
            $sc_par_lst_upd_ex_log->add(sql_type::SUB);
            $qp_update = $this->sql_common($sc_update, $sc_par_lst_upd_ex_log);;
            $update_fvt_lst = new sql_par_field_list();
            $update_fvt_lst->set($update_fld_val_typ_lst);
            $qp_update->sql = $sc_update->create_sql_update(
                $id_field, $var_name_row_id, $update_fvt_lst, [], $sc_par_lst_upd_ex_log);
            // add the insert row to the function body
            $sql .= ' ' . $qp_update->sql . ' ';
        }

        if ($sc->db_type == sql_db::POSTGRES) {
            if ($id_fld_new != '' and !$usr_tbl) {
                $sql .= sql::RETURN . ' ' . $id_fld_new . '; ';
            }
        }

        $sql .= $sc->sql_func_end();

        // create the query parameters for the actual change
        $qp_chg = clone $qp;
        $qp_chg->sql = $sc->create_sql_insert($par_lst_out, $sc_par_lst);

        // merge all together and create the function
        $qp->sql = $qp_chg->sql . $sql . ';';
        $qp->par = $par_lst_out->values();

        // create the call sql statement
        $qp->call_name = $qp_chg->name . '_call';
        $qp->call = ' ' . sql::PREPARE . ' ' . $qp_chg->name . '_call';
        if ($sc->db_type == sql_db::POSTGRES) {
            $qp->call .= ' (' . $par_lst_out->par_types($sc) . ') ' . sql::AS . ' ';
        } else {
            $qp->call .= ' ' . sql::FROM . " '";
        }
        $qp->call .= sql::SELECT . ' ' . $qp_chg->name;
        $qp->call .= ' (' . $par_lst_out->par_vars($sc) . ')';
        if ($sc->db_type == sql_db::POSTGRES) {
            $qp->call .= ';';
        } else {
            $qp->call .= "';";
        }

        return $qp;
    }

    /**
     * create the sql statement to add a new named sandbox object e.g. word to the database
     * dummy function overwritten by the child objects
     *
     * @param sql $sc with the target db_type set
     * @param sql_par $qp
     * @param sql_par_field_list $fvt_lst list of field names, values and sql types additional to the standard id and name fields
     * @param string $id_fld_new
     * @param sql_type_list $sc_par_lst_sub the parameters for the sql statement creation
     * @return sql_par the SQL insert statement, the name of the SQL statement and the parameter list
     */
    function sql_insert_key_field(
        sql                $sc,
        sql_par            $qp,
        sql_par_field_list $fvt_lst,
        string             $id_fld_new,
        sql_type_list      $sc_par_lst_sub = new sql_type_list([])
    ): sql_par
    {
        return $qp;
    }

    /**
     * create the sql statement to change or exclude a named sandbox object e.g. word to the database
     *
     * @param sql $sc with the target db_type set
     * @param sql_par_field_list $fvt_lst list of field names, values and sql types additional to the standard id and name fields
     * @param array $fld_lst_all list of field names of the given object
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par the SQL update statement, the name of the SQL statement and the parameter list
     */
    function sql_update_named(
        sql                $sc,
        sql_par_field_list $fvt_lst,
        array              $fld_lst_all = [],
        sql_type_list      $sc_par_lst = new sql_type_list([])): sql_par
    {
        // TODO deprecate
        $val_lst = $fvt_lst->values();

        $lib = new library();
        $and_log = $sc_par_lst->and_log();
        $usr_tbl = $sc_par_lst->is_usr_tbl();
        $fld_lst = $fvt_lst->names();
        $fld_chg_ext = $lib->sql_field_ext($fld_lst, $fld_lst_all);
        $ext = sql::file_sep . sql::file_update;
        if ($and_log) {
            $ext .= sql_type::LOG->extension();
        }
        $ext .= sql::file_sep . $fld_chg_ext;
        $qp = $this->sql_common($sc, $sc_par_lst, $ext);
        if ($and_log) {
            // log functions must always use named parameters
            $sc_par_lst->add(sql_type::NAMED_PAR);
            $qp = $this->sql_update_named_and_log($sc, $qp, $fvt_lst, $fld_lst_all, $sc_par_lst);
        } else {
            if ($usr_tbl) {
                $qp->sql = $sc->create_sql_update(
                    [$this->id_field(), user::FLD_ID], [$this->id(), $this->user_id()], $fvt_lst);
            } else {
                $qp->sql = $sc->create_sql_update($this->id_field(), $this->id(), $fvt_lst);
            }
            $qp->par = $val_lst;
        }

        return $qp;
    }

    /**
     * @param sql $sc the sql creator object with the db type set
     * @param sql_par $qp the query parameter with the name already set
     * @param sql_par_field_list $fvt_lst
     * @param array $fld_lst_all
     * @param sql_type_list $sc_par_lst
     * @return sql_par
     */
    private function sql_update_named_and_log(
        sql                $sc,
        sql_par            $qp,
        sql_par_field_list $fvt_lst,
        array              $fld_lst_all = [],
        sql_type_list      $sc_par_lst = new sql_type_list([])
    ): sql_par
    {

        // set some var names to shorten the code lines
        $usr_tbl = $sc_par_lst->is_usr_tbl();
        $ext = sql::file_sep . sql::file_insert;
        $id_fld = $sc->id_field_name();
        $id_val = '_' . $id_fld;

        // add the change action field to the list for the log entries
        global $change_action_list;
        $fvt_lst->add_field(
            change_action::FLD_ID,
            $change_action_list->id(change_action::UPDATE),
            type_object::FLD_ID_SQLTYP
        );

        // list of parameters actually used in order of the function usage
        $par_lst_out = new sql_par_field_list();

        // init the function body
        $sql = $sc->sql_func_start('', $sc_par_lst);

        // don't use the log parameter for the sub queries
        $sc_par_lst_sub = $sc_par_lst->remove(sql_type::LOG);
        $sc_par_lst_sub->add(sql_type::LIST);

        // get the fields actually changed
        $fld_lst = $fvt_lst->names();
        $fld_lst_chg = array_intersect($fld_lst, $fld_lst_all);

        // for the user sandbox table remove the primary key fields from the list
        if ($usr_tbl) {
            $key_fld_pos = array_search($id_fld, $fld_lst_chg);
            unset($fld_lst_chg[$key_fld_pos]);
            $key_fld_pos = array_search(user::FLD_ID, $fld_lst_chg);
            unset($fld_lst_chg[$key_fld_pos]);
        }

        // the fields that where the changes should be added to the change log
        $par_lst_chg = $fvt_lst->intersect($fld_lst_chg);

        // create the queries for the log entries
        $func_body_change = '';
        foreach ($par_lst_chg as $fld) {

            // create the insert log statement for the field of the loop
            $log = new change($this->user());
            $log->set_table_by_class($this::class);
            $log->set_field($fld->name);
            $log->old_value = $fvt_lst->get_old($fld->name);
            $log->new_value = $fld->value;
            // make sure that also overwrites are added to the log
            if ($log->old_value != null or $log->new_value != null) {
                if ($log->old_value == null) {
                    $log->old_value = '';
                }
                if ($log->new_value == null) {
                    $log->new_value = '';
                }
            }

            // TODO get the id of the new entry and use it in the log
            $sc_log = clone $sc;
            $sc_par_lst_log = $sc_par_lst_sub;
            $sc_par_lst_log->add(sql_type::VALUE_SELECT);
            $sc_par_lst_log->add(sql_type::UPDATE_PART);
            $sc_par_lst_log->add(sql_type::SELECT_FOR_INSERT);
            // TODO replace dummy value table with an enum value
            $qp_log = $log->sql_insert(
                $sc_log, $sc_par_lst_log, $ext . '_' . $fld->name, '', $fld->name, $id_val);

            // TODO get the fields used in the change log sql from the sql
            $func_body_change .= ' ' . $qp_log->sql . ';';

            // add the user_id if needed
            $log_usr_id = $fvt_lst->get_value(user::FLD_ID);
            if ($log_usr_id == null) {
                $log_usr_id = $this->user_id();
            }
            $par_lst_out->add_field(
                user::FLD_ID,
                $log_usr_id,
                db_object_seq_id::FLD_ID_SQLTYP);

            // add the change_action_id if needed
            $par_lst_out->add_field(
                change_action::FLD_ID,
                $fvt_lst->get_value(change_action::FLD_ID),
                sql_par_type::INT_SMALL);

            // add the field_id of the field actually changed if needed
            $par_lst_out->add_field(
                sql::FLD_LOG_FIELD_PREFIX . $fld->name,
                $fvt_lst->get_value(sql::FLD_LOG_FIELD_PREFIX . $fld->name),
                sql_par_type::INT_SMALL);

            // add the db field value of the field actually changed if needed
            $par_lst_out->add_field(
                $fld->name . change::FLD_OLD_EXT,
                $fvt_lst->get_old($fld->name),
                $fvt_lst->get_type($fld->name));

            // add the field value of the field actually changed if needed
            $par_lst_out->add_field(
                $fld->name,
                $fvt_lst->get_value($fld->name),
                $fvt_lst->get_type($fld->name));

            // add the row id of the standard table for user overwrites
            $log_id = $fvt_lst->get_value($id_fld);
            if ($log_id == null) {
                $log_id = $this->id();
            }
            $par_lst_out->add_field(
                $id_fld,
                $log_id,
                db_object_seq_id::FLD_ID_SQLTYP);
        }
        $sql .= ' ' . $func_body_change;

        // update the fields excluding the unique id
        $update_fields = array_values($fld_lst_chg);
        $update_values = [];
        $update_types = [];
        foreach ($fld_lst_chg as $fld) {
            $update_values[] = $fvt_lst->get_value($fld);
            $update_types[] = $fvt_lst->get_type($fld);
        }
        $update_fld_val_typ_lst = [];
        foreach ($update_fields as $key => $field) {
            $update_fld_val_typ_lst[] = [$field, $update_values[$key], $update_types[$key]];
        }
        $sc_update = clone $sc;
        $sc_par_lst_upd = new sql_type_list([sql_type::NAMED_PAR, sql_type::UPDATE, sql_type::UPDATE_PART]);
        $sc_par_lst_upd_ex_log = $sc_par_lst_upd->remove(sql_type::LOG);
        if ($usr_tbl) {
            $sc_par_lst_upd_ex_log->add(sql_type::USER);
        }
        $qp_update = $this->sql_common($sc_update, $sc_par_lst_upd_ex_log);;
        $update_fvt_lst = new sql_par_field_list();
        $update_fvt_lst->set($update_fld_val_typ_lst);
        if ($usr_tbl) {
            $sc_par_lst_upd->add(sql_type::USER);
        }
        $qp_update->sql = $sc_update->create_sql_update(
            $id_fld, $id_val, $update_fvt_lst, [], $sc_par_lst_upd, true, '', $id_fld);
        // add the insert row to the function body
        $sql .= ' ' . $qp_update->sql . ' ';

        $sql .= $sc->sql_func_end();

        // create the query parameters for the actual change
        $qp_chg = clone $qp;
        $qp_chg->sql = $sc->create_sql_update(
            $id_fld, $id_val, $par_lst_out, [], $sc_par_lst);
        $qp_chg->par = $fvt_lst->values();

        // merge all together and create the function
        $qp->sql = $qp_chg->sql . $sql . ';';

        $qp->call = ' ' . sql::SELECT . ' ' . $qp_chg->name . ' (';

        $call_val_str = $par_lst_out->par_sql($sc);

        $qp->call .= $call_val_str . ');';

        return $qp;
    }

    /**
     * the common part of the sql_insert and sql_update functions
     * TODO include the sql statements to log the changes
     *
     * @param sql $sc with the target db_type set
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @param string $name_ext the query name extension to differ the queries based on the fields changed
     * @return sql_par prepared sql parameter object with the name set
     */
    protected function sql_common(sql $sc, sql_type_list $sc_par_lst = new sql_type_list([]), string $name_ext = ''): sql_par
    {
        $lib = new library();
        $usr_tbl = $sc_par_lst->is_usr_tbl();
        $sc->set_class($this::class, $sc_par_lst);
        $sql_name = $lib->class_to_name($this::class);
        $qp = new sql_par($sql_name);
        $qp->name = $sql_name . $name_ext;
        if ($usr_tbl) {
            $qp->name .= '_user';
        }
        $sc->set_name($qp->name);
        return $qp;
    }

    /*
     * internal check
     */

    /**
     * TODO deprecate because using the object const is actually faster in execution
     * return the expected database id field name of the object
     * should actually be static, but seems to be not yet possible
     * TODO check if it can be combined with id_field()
     */
    function fld_id(string $class = self::class): string
    {
        $lib = new library();
        return $lib->class_to_name($class) . sql_db::FLD_EXT_ID;
    }

    function fld_usr_id(string $class = self::class): string
    {
        $lib = new library();
        return sql_db::USER_PREFIX . $lib->class_to_name($class) . sql_db::FLD_EXT_ID;
    }

    function fld_name(string $class = self::class): string
    {
        $lib = new library();
        return $lib->class_to_name($class) . sql_db::FLD_EXT_NAME;
    }

    /**
     * @return bool true if this sandbox object has a name as unique key
     * final function overwritten by the child object
     */
    function is_named_obj(): bool
    {
        return true;
    }

    /**
     * @return bool true if this sandbox object links two objects
     * final function overwritten by the child object
     */
    function is_link_obj(): bool
    {
        return false;
    }

    /**
     * @return bool true if this sandbox object is a value or result
     * final function overwritten by the child object
     */
    function is_value_obj(): bool
    {
        return false;
    }


    /*
     *  check functions
     */

    /*
    // check if the owner is set for all records of a user sandbox object
    // e.g. if the owner of a new triple is set correctly at creation
    //      if not changes of another can overwrite the standard and by that influence the setup of the creator
    function chk_owner ($type, $correct) {
      zu_debug($class_name.'->chk_owner for '.$type);

      global $db_con;
      $msg = '';

      // just to allow the call with one line
      if ($type <> '') {
        $class_name = $type;
      }

      //$db_con = New mysql;
      $db_con->set_type($class_name);
      $db_con->set_usr($this->user()->id());

      if ($correct === True) {
        // set the default owner for all records with a missing owner
        $change_txt = $db_con->set_default_owner();
        if ($change_txt <> '') {
          $msg = 'Owner set for '.$change_txt.' '.$type.'s.';
        }
      } else {
        // get the list of records with a missing owner
        $id_lst = $db_con->missing_owner();
        $id_txt = implode(",",$id_lst);
        if ($id_txt <> '') {
          $msg = 'Owner not set for '.$type.' ID '.$id_txt.'.';
        }
      }

      return $id_lst;
    }
    */

}


