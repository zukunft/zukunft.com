<?php

/*

    model/sandbox/sandbox.php - the superclass for handling user specific objects including the database saving
    -------------------------

    This superclass should be used by the classes words, formula, ... to enable user specific values and links
    similar to sandbox.php but for database objects that have an auto sequence prime id
    TODO should be merged once php allows aggregating extends e.g. sandbox extends db_object, db_user_object


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

// TODO align the function return types with the source (ref) object
// TODO use the user sandbox also for the word object
// TODO check if handling of negative ids is correct
// TODO split into a link and a named user sandbox object to always use the smallest possible object

namespace cfg;

include_once DB_PATH . 'sql_db.php';
include_once DB_PATH . 'sql_par.php';
include_once DB_PATH . 'sql_par_type.php';
include_once MODEL_HELPER_PATH . 'db_object_seq_id_user.php';
include_once MODEL_PHRASE_PATH . 'phrase_type.php';
include_once MODEL_SANDBOX_PATH . 'protection_type.php';
include_once MODEL_SANDBOX_PATH . 'share_type.php';

use cfg\db\sql;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
use cfg\db\sql_par_type;
use cfg\export\sandbox_exp;
use cfg\log\change;
use cfg\log\change_log;
use cfg\log\change_log_action;
use cfg\log\change_log_link;
use Exception;

class sandbox extends db_object_seq_id_user
{

    /*
     * sandbox types
     */

    // the main types of user sandbox objects
    const TYPE_NAMED = 'named';  // for user sandbox objects which have a unique name like formulas
    const TYPE_LINK = 'link';    // for user sandbox objects that link two objects like formula links
    const TYPE_VALUE = 'value';  // for user sandbox objects that are used to save values


    /*
     * database link
     */

    // database and JSON object field names used in many user sandbox objects
    // the id field is not included here because it is used for the database relations and should be object specific
    // e.g. always "word_id" instead of simply "id"
    const FLD_EXCLUDED = 'excluded';    // field name used to delete the object only for one user
    const FLD_USER_NAME = 'user_name';
    const FLD_SHARE = "share_type_id";  // field name for the share permission
    const FLD_PROTECT = "protect_id";   // field name for the protection level

    // field lists for the table creation
    const FLD_ALL_OWNER = array(
        [user::FLD_ID, sql_field_type::INT, sql_field_default::NULL, sql::INDEX, user::class, 'the owner / creator of the value'],
    );
    const FLD_ALL_CHANGER = array(
        [user::FLD_ID, sql_field_type::KEY_PART_INT, sql_field_default::NOT_NULL, sql::INDEX, user::class, 'the changer of the '],
    );
    const FLD_ALL = array(
        [self::FLD_EXCLUDED, sql_field_type::BOOL, sql_field_default::NULL, '', '', 'true if a user, but not all, have removed it'],
        [self::FLD_SHARE, sql_field_type::INT_SMALL, sql_field_default::NULL, '', '', 'to restrict the access'],
        [self::FLD_PROTECT, sql_field_type::INT_SMALL, sql_field_default::NULL, '', '', 'to protect against unwanted changes'],
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
    const FLD_NAMES = array(
    );
    const FLD_NAMES_USR = array(
    );
    // combine FLD_NAMES_NUM_USR_SBX and FLD_NAMES_NUM_USR_ONLY_SBX just for shorter code
    const FLD_NAMES_NUM_USR = array(
        self::FLD_EXCLUDED,
        self::FLD_SHARE,
        self::FLD_PROTECT
    );
    // list of all user sandbox database types with a standard ID
    // so exclude values and result TODO check missing owner for values and results
    const DB_TYPES = array(
        sql_db::TBL_WORD,
        sql_db::TBL_TRIPLE,
        sql_db::TBL_FORMULA,
        sql_db::TBL_FORMULA_LINK,
        sql_db::TBL_VIEW,
        sql_db::TBL_COMPONENT,
        sql_db::TBL_COMPONENT_LINK
    );


    /*
     * object vars
     */

    // fields to define the object; should be set in the constructor of the child object
    // TODO use object class instead
    public ?string $obj_name = null;       // the object type to create the correct database fields e.g. for the type "word" the database field for the id is "word_id"
    public ?string $obj_type = null;       // either a "named" object or a "link" object
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
        // the default type that is overwritten by the child objects
        $this->obj_type = self::TYPE_NAMED;
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
      these functions differ for each object, so they are always in the child class and not this in the superclass

      private function load_standard() {
      }

      function load() {
      }

    */

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
        $this->share_id = $share_types->id(share_type::PUBLIC);
        $this->protection_id = $protection_types->id(protection_type::NO_PROTECT);
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
        $sql .= $this->sql_table_create($sc);
        $sc->set_class($this::class, true);
        $sql .= $this->sql_table_create($sc, true);
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
        $sql .= $this->sql_index_create($sc);
        $sc->set_class($this::class, true);
        $sql .= $this->sql_index_create($sc, true);
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
        $sql .= $this->sql_foreign_key_create($sc);
        $sc->set_class($this::class, true);
        $sql .= $this->sql_foreign_key_create($sc, true);
        return $sql;
    }


    /*
     * load
     */

    /**
     * create the SQL to load the single default value always by the id
     * @param sql $sc with the target db_type set
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_standard_sql(sql $sc, string $class = self::class): sql_par
    {
        $qp = new sql_par($class, true);
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
     * function that must be overwritten by the child object
     * @return array with all field names of the user sandbox object excluding the prime id field
     */
    protected function all_sandbox_fields(): array
    {
        return array();
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

        return $qp;
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
                $db_con->set_class($this->obj_name);
                $db_con->set_usr($this->user()->id());
                if ($db_con->update_old($this->id, user::FLD_ID, $this->user()->id())) {
                    $result = true;
                }
            }
        }
        return $result;
    }

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
     * dummy function to get the missing objects from the database that is always overwritten by the child class
     * @returns bool  false if the loading has failed
     */
    function load_objects(): bool
    {
        log_err('The dummy parent method get_similar has been called, which should never happen');
        return true;
    }

    /**
     * dummy function to get the missing object values from the database that is always overwritten by the child class
     * @returns bool  false if the loading has failed
     */
    function load_obj_vars(): bool
    {
        log_err('The dummy parent method get_similar has been called, which should never happen');
        return true;
    }


    /*
     *  check functions
     */

    /*
    // check if the owner is set for all records of a user sandbox object
    // e.g. if the owner of a new triple is set correctly at creation
    //      if not changes of another can overwrite the standard and by that influence the setup of the creator
    function chk_owner ($type, $correct) {
      zu_debug($this->obj_name.'->chk_owner for '.$type);

      global $db_con;
      $msg = '';

      // just to allow the call with one line
      if ($type <> '') {
        $this->obj_name = $type;
      }

      //$db_con = New mysql;
      $db_con->set_type($this->obj_name);
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

    /*
     * type loading functions
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
            $this->share_id = $share_types->id(share_type::PUBLIC);
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
            $this->protection_id = $protection_types->id(protection_type::NO_PROTECT);
        }

        return $protection_types->name($this->protection_id);
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
            if ($key == share_type::JSON_FLD) {
                $this->share_id = $share_types->id($value);
                if ($this->share_id < 0) {
                    $lib = new library();
                    $result->add_message('share type ' . $value . ' is not expected when importing ' . $lib->dsp_array($in_ex_json));
                }
            }
            if ($key == protection_type::JSON_FLD) {
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
     * @param sql_db $db_con
     * @return sql_par sql parameter to get the user id of the most often used link (position) beside the standard (position)
     */
    function median_user_sql(sql_db $db_con): sql_par
    {
        $qp = new sql_par($this->obj_name);
        $qp->name .= 'median_user';
        if ($this->owner_id > 0) {
            $qp->name .= '_ex_owner';
        }
        $db_con->set_class($this->obj_name, true);
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
     * save support - ownership and access
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
            // TODO activate $result .= $this->usr_cfg_create_all();
            $result = $this->set_owner($this->user()->id()); // TODO remove double getting of the user object
            // TODO activate $result .= $this->usr_cfg_cleanup();
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

            $db_con->set_class($this->obj_name);
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

    // true if no other user has modified the object
    // assuming that in this case no confirmation from the other users for an object change is needed
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

    function changer_sql(sql_db $db_con): sql_par
    {
        $qp = new sql_par($this->obj_name);
        $qp->name .= 'changer';
        if ($this->owner_id > 0) {
            $qp->name .= '_ex_owner';
        }
        $db_con->set_class($this->obj_name, true);
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
        $db_con->set_class($this->obj_name);
        $db_con->set_usr($this->user()->id());
        $qp = $this->changer_sql($db_con);
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
        $sc->set_class($class, true);
        $sc->set_name($qp->name);
        $sc->set_usr($this->user()->id());
        $sc->set_join_fields(
            array_merge(array(user::FLD_ID, user::FLD_NAME),user::FLD_NAMES_LIST),
            sql_db::TBL_USER,
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

        log_debug($this->obj_name . zu_dsp_bool($result));
        return $result;
    }


    /*
     * save support - user sandbox
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

        $result = false;
        $action = 'Deletion of user ' . $this->obj_name . ' ';
        $msg_failed = $this->id . ' failed for ' . $this->user()->name;

        $db_con->set_type(sql_db::TBL_USER_PREFIX . $this->obj_name);
        try {
            $msg = $db_con->delete(
                array($this->id_field(), user::FLD_ID),
                array($this->id, $this->user()->id()));
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

        global $db_con;
        $result = true;

        if ($this->id > 0 and $this->user()->id() > 0) {
            $log = $this->log_del();
            if ($log->id() > 0) {
                $db_con->usr_id = $this->user()->id();
                $result = $this->del_usr_cfg_exe($db_con);
            }

        } else {
            log_err('The database ID and the user must be set to remove a user specific modification of ' . $this->obj_name . '.', $this->obj_name . '->del_usr_cfg');
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


        if (!$this->has_usr_cfg()) {
            if ($this->obj_type == self::TYPE_NAMED) {
                log_debug('for "' . $this->dsp_id() . ' und user ' . $this->user()->name);
            } elseif ($this->obj_type == self::TYPE_LINK) {
                if (isset($this->fob) and isset($this->tob)) {
                    log_debug('for "' . $this->fob->name . '"/"' . $this->tob->name . '" by user "' . $this->user()->name . '"');
                } else {
                    log_debug('for "' . $this->id . '" and user "' . $this->user()->name . '"');
                }
            } else {
                log_err('Unknown user sandbox type ' . $this->obj_type . ' in ' . $this->obj_name, $this->obj_name . '->log_add');
            }
            $lib = new library();
            $class = $lib->class_to_name($class);

            // check again if there ist not yet a record
            $db_con->set_class($this->obj_name, true);
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
                $db_con->set_class(sql_db::TBL_USER_PREFIX . $this->obj_name);
                $db_con->set_usr($this->user()->id());
                $log_id = $db_con->insert_old(array($this->id_field(), user::FLD_ID), array($this->id, $this->user()->id()));
                if ($log_id <= 0) {
                    log_err('Insert of ' . sql_db::USER_PREFIX . $this->obj_name . ' failed.');
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
     * save support - log changes
     */

    /**
     * set the log entry parameter for a new named object
     * for all not named objects like links, this function is overwritten
     * e.g. that the user can see "added formula 'scale millions' to word 'mio'"
     */
    function log_add(): change
    {
        log_debug($this->dsp_id());

        $log = new change($this->user());

        $log->action = change_log_action::ADD;
        // TODO add the table exceptions from sql_db
        $log->set_table($this->obj_name . sql_db::TABLE_EXTENSION);
        $log->row_id = 0;
        $log->add();

        return $log;
    }

    /**
     * set the log entry parameter for a new link object
     */
    function log_link_add(): change_log_link
    {
        log_err('The dummy parent method get_similar has been called, which should never happen');
        return new change_log_link($this->user());
    }

    /**
     * set the main log entry parameters for updating one field
     */
    private function log_upd_common($log)
    {
        log_debug($this->dsp_id());
        $log->set_user($this->user());
        $log->action = change_log_action::UPDATE;
        if ($this->can_change()) {
            // TODO add the table exceptions from sql_db
            $log->set_table($this->obj_name . sql_db::TABLE_EXTENSION);
        } else {
            $log->set_table(sql_db::TBL_USER_PREFIX . $this->obj_name . sql_db::TABLE_EXTENSION);
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
    function log_upd_link(): change_log_link
    {
        log_debug($this->dsp_id());
        $log = new change_log_link($this->user());
        return $this->log_upd_common($log);
    }

    /**
     * create a log object for an update of an object field or a link
     * e.g. that the user can see "moved formula list to position 3 in phrase view"
     */
    function log_upd()
    {
        log_debug($this->dsp_id());
        if ($this->obj_type == self::TYPE_NAMED) {
            $log = $this->log_upd_field();
        } else {
            $log = $this->log_upd_link();
        }
        return $this->log_upd_common($log);
    }

    /**
     * dummy function definition that will be overwritten by the child object
     * @return change_log_link
     */
    function log_del_link(): change_log_link
    {
        log_err('The dummy parent method get_similar has been called, which should never happen');
        return new change_log_link($this->user());
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
     * save support - save fields
     */

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
     * @param change|change_log_link $log the log object to track the change and allow a rollback
     * @return string an empty string if everything is fine or the message that should be shown to the user
     */
    function save_field_user(sql_db $db_con, change|change_log_link $log): string
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
                        $db_con->set_class(sql_db::TBL_USER_PREFIX . $this->obj_name);
                        $db_con->set_usr($this->user()->id());
                        if (!$db_con->update_old($this->id, $log->field(), Null)) {
                            $result = 'remove of ' . $log->field() . ' failed';
                        }
                    }
                    $this->del_usr_cfg_if_not_needed(); // don't care what the result is, because in most cases it is fine to keep the user sandbox row
                } else {
                    $db_con->set_class($this->obj_name);
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
                    $db_con->set_class(sql_db::TBL_USER_PREFIX . $this->obj_name);
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
     * @param change|change_log_link $log the log object to track the change and allow a rollback
     * @return string an empty string if everything is fine or the message that should be shown to the user
     */
    function save_field(sql_db $db_con, change|change_log_link $log): string
    {
        $result = '';

        if ($log->new_id > 0) {
            $new_value = $log->new_id;
        } else {
            $new_value = $log->new_value;
        }
        if ($log->add()) {
            $db_con->set_class($this->obj_name);
            $db_con->set_usr($this->user()->id());
            if (!$db_con->update_old($this->id, $log->field(), $new_value)) {
                $result = 'update of value for ' . $log->field() . ' to ' . $new_value . ' failed';
            }
        }
        return $result;
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
                if ($this->obj_type == self::TYPE_LINK) {
                    $log = $this->log_del_link();
                } else {
                    $log = $this->log_del();
                }
            } else {
                if ($this->obj_type == self::TYPE_LINK) {
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

        if ($db_rec->is_excluded() <> $this->is_excluded()) {
            $log = $this->save_field_excluded_log($db_rec);
            $new_value = $this->is_excluded();
            $std_value = $std_rec->is_excluded();
            // similar to $this->save_field_do
            if ($this->can_change()) {
                $db_con->set_class($this->obj_name);
                $db_con->set_usr($this->user()->id());
                if (!$db_con->update_old($this->id, $log->field(), $new_value)) {
                    $result .= 'excluding of ' . $this->obj_name . ' failed';
                }
            } else {
                if (!$this->has_usr_cfg()) {
                    if (!$this->add_usr_cfg()) {
                        $result = 'creation of user sandbox to exclude failed';
                    }
                }
                if ($result == '') {
                    $db_con->set_class(sql_db::TBL_USER_PREFIX . $this->obj_name);
                    $db_con->set_usr($this->user()->id());
                    if ($new_value == $std_value) {
                        if (!$db_con->update_old($this->id, $log->field(), Null)) {
                            $result .= 'include of ' . $this->obj_name . ' for user failed';
                        }
                    } else {
                        if (!$db_con->update_old($this->id, $log->field(), $new_value)) {
                            $result .= 'excluding of ' . $this->obj_name . ' for user failed';
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
                    $db_con->set_class(sql_db::TBL_USER_PREFIX . $this->obj_name);
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
     * save support - check id
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
     * @param sql_db $db_con the active database connection
     * @param sandbox $db_rec the database record before the saving
     * @param sandbox $std_rec the database record defined as standard because it is used by most users
     * @returns string an empty string if everything is fine or a messages for the user what should be changed
     */
    function save_id_if_updated(sql_db $db_con, sandbox $db_rec, sandbox $std_rec): string
    {
        log_debug($this->dsp_id());
        $result = '';

        if ($this->is_id_updated($db_rec)) {
            $db_chk = $this->get_obj_with_same_id_fields();
            if ($db_chk->id <> 0) {
                log_debug('target already exists');
                if ($this->rename_can_switch) {
                    // ... if yes request to delete or exclude the record with the id parameters before the change
                    $to_del = clone $db_rec;
                    $msg = $to_del->del();
                    if (!$msg->is_ok()) {
                        $result .= 'Failed to delete the unused ' . $this->obj_name;
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
                            log_debug('found a ' . $this->obj_name . ' target ' . $db_chk->dsp_id() . ', so del ' . $db_rec->dsp_id() . ' and add ' . $this->dsp_id());
                        } else {
                            //$result = 'Failed to exclude the unused ' . $this->obj_name;
                            $result .= 'A ' . $this->obj_name . ' with the name "' . $this->name() . '" already exists. Please use another name or merge with this ' . $this->obj_name . '.';
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
                    log_debug('change the existing ' . $this->obj_name . ' ' . $this->dsp_id() . ' (db ' . $db_rec->dsp_id() . ', standard ' . $std_rec->dsp_id() . ')');
                    // TODO check if next line is needed
                    //$this->load_objects();
                    if ($this->obj_type == self::TYPE_LINK) {
                        $result .= $this->save_id_fields_link($db_con, $db_rec, $std_rec);
                    } elseif ($this->obj_type == self::TYPE_NAMED) {
                        $result .= $this->save_id_fields($db_con, $db_rec, $std_rec);
                    } else {
                        log_info('Save of id field for ' . $this->obj_type . ' not expected');
                    }
                } else {
                    // if the target link has not yet been created
                    // ... request to delete the old
                    $to_del = clone $db_rec;
                    $msg = $to_del->del();
                    if (!$msg->is_ok()) {
                        $result .= 'Failed to delete the unused ' . $this->obj_name;
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
     * save support - check similar
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
        if ($this->obj_name == sql_db::TBL_WORD and $obj_to_check->obj_name == sql_db::TBL_FORMULA) {
            // special case if word should be created representing the formula it is a kind of same at least the creation of the word should be alloed
            if ($this->name == $obj_to_check->name) {
                $result = true;
            }
        } elseif ($this->obj_name == sql_db::TBL_WORD and $obj_to_check->obj_name == sql_db::TBL_WORD) {

        */
        if ($this->obj_name == sql_db::TBL_WORD and $obj_to_check->obj_name == sql_db::TBL_WORD) {
            // special case a word should not be combined with a word that is representing a formulas
            if ($this->name() == $obj_to_check->name()) {
                if (isset($this->type_id) and isset($obj_to_check->type_id)) {
                    if ($this->type_id == $obj_to_check->type_id) {
                        $result = true;
                    } else {
                        if ($this->type_id == sql_db::TBL_FORMULA
                            and $obj_to_check->type_id == $phrase_types->id(phrase_type::FORMULA_LINK)) {
                            // if one is a formula and the other is a formula link word, the two objects are representing the same formula object (but the calling function should use the formula to update)
                            $result = true;
                        } elseif ($obj_to_check->type_id == sql_db::TBL_FORMULA
                            and $this->type_id == $phrase_types->id(phrase_type::FORMULA_LINK)) {
                            // like above, but the other way round
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
                    $result = true;
                }
            }
        } elseif ($this->obj_name == $obj_to_check->obj_name) {
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
            if ($this->obj_name == $obj_to_check->obj_name) {
                $result = $this->is_same_std($obj_to_check);
            } else {
                // create a synthetic unique index over words, phrase, verbs and formulas
                if ($this->obj_name == sql_db::TBL_WORD
                    or $this->obj_name == sql_db::TBL_TRIPLE
                    or $this->obj_name == sql_db::TBL_FORMULA
                    or $this->obj_name == sql_db::TBL_VERB) {
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
     * @return user_message with status ok
     *                      or if something went wrong
     *                      the message that should be shown to the user
     *                      including suggested solutions
     */
    function add(): user_message
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

    /*
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
     *
     */

    function save(): string
    {
        log_debug($this->dsp_id());

        global $db_con;

        // check the preserved names
        $result = $this->check_preserved();

        if ($result == '') {

            // load the objects if needed
            if ($this->obj_type == self::TYPE_LINK) {
                $this->load_objects();
            }

            // configure the global database connection object for the select, insert, update and delete queries
            $db_con->set_class($this->obj_name);
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
        }

        // create a new object if nothing similar has been found
        if ($result == '') {
            if ($this->id == 0) {
                log_debug('add');
                $result = $this->add()->get_last_message();
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
                        $result .= 'Reloading of user ' . $this->obj_name . ' failed';
                    } else {
                        log_debug('reloaded from db');
                        if ($this->obj_type == self::TYPE_LINK) {
                            if (!$db_rec->load_objects()) {
                                $result .= 'Reloading of the object for ' . $this->obj_name . ' failed';
                            }
                            // configure the global database connection object again to overwrite any changes from load_objects
                            $db_con->set_class($this->obj_name);
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
                            $result .= 'Reloading of the default values for ' . $this->obj_name . ' failed';
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
                        $result .= $this->save_id_if_updated($db_con, $db_rec, $std_rec);
                    }

                    // if a problem has appeared up to here, don't try to save the values
                    // the problem is shown to the user by the calling interactive script
                    if ($result == '') {
                        $result .= $this->save_fields($db_con, $db_rec, $std_rec);
                    }
                }
            }
            if ($result != '') {
                log_err($result, 'user_sandbox_' . $this->obj_name . '->save');
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

        global $db_con;
        global $phrase_types;

        $msg = '';
        $result = new user_message();

        // log the deletion request
        if ($this->obj_type == self::TYPE_LINK) {
            $log = $this->log_del_link();
        } else {
            $log = $this->log_del();
        }
        if ($log->id() > 0) {
            $db_con->usr_id = $this->user()->id();

            // for words first delete all links
            if ($this->obj_name == sql_db::TBL_WORD) {
                $msg = $this->del_links();
                $result->add($msg);
            }

            // for triples first delete all links
            if ($this->obj_name == sql_db::TBL_TRIPLE) {
                $msg = $this->del_links();
                $result->add($msg);
            }

            // for formulas first delete all links
            if ($this->obj_name == sql_db::TBL_FORMULA) {
                $msg = $this->del_links();
                $result->add($msg);

                // and the corresponding formula elements
                if ($result->is_ok()) {
                    $db_con->set_class(sql_db::TBL_FORMULA_ELEMENT);
                    $db_con->set_usr($this->user()->id());
                    $msg = $db_con->delete(sql_db::TBL_FORMULA . sql_db::FLD_EXT_ID, $this->id);
                    $result->add_message($msg);
                }

                // and the corresponding results
                if ($result->is_ok()) {
                    $db_con->set_class(sql_db::TBL_RESULT);
                    $db_con->set_usr($this->user()->id());
                    $msg = $db_con->delete(sql_db::TBL_FORMULA . sql_db::FLD_EXT_ID, $this->id);
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
            if ($this->obj_name == sql_db::TBL_COMPONENT) {
                $msg = $this->del_links();
                $result->add($msg);
            }

            // for views first delete all links
            if ($this->obj_name == sql_db::TBL_VIEW) {
                $msg = $this->del_links();
                $result->add($msg);
            }

            // delete first all user configuration that have also been excluded
            if ($result->is_ok()) {
                $db_con->set_class(sql_db::TBL_USER_PREFIX . $this->obj_name);
                $db_con->set_usr($this->user()->id());
                $msg = $db_con->delete(
                    array($this->obj_name . sql_db::FLD_EXT_ID, 'excluded'),
                    array($this->id, '1'));
                $result->add_message($msg);
            }
            if ($result->is_ok()) {
                // finally, delete the object
                $db_con->set_class($this->obj_name);
                $db_con->set_usr($this->user()->id());
                $msg = $db_con->delete($this->id_field(), $this->id);
                $result->add_message($msg);
                log_debug('of ' . $this->dsp_id() . ' done');
            } else {
                log_err('Delete failed for ' . $this->obj_name, $this->obj_name . '->del_exe', 'Delete failed, because removing the user settings for ' . $this->obj_name . ' ' . $this->dsp_id() . ' returns ' . $msg, (new Exception)->getTraceAsString(), $this->user());
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
            log_warning('Reload of for deletion has lead to unexpected', $this->obj_name . '->del', 'Reload of ' . $this->obj_name . ' ' . $this->dsp_id() . ' for deletion or exclude has unexpectedly lead to ' . $msg . '.', (new Exception)->getTraceAsString(), $this->user());
        } else {
            log_debug('reloaded ' . $this->dsp_id());
            // check if the object is still valid
            if ($this->id <= 0) {
                log_warning('Delete failed', $this->obj_name . '->del', 'Delete failed, because it seems that the ' . $this->obj_name . ' ' . $this->dsp_id() . ' has been deleted in the meantime.', (new Exception)->getTraceAsString(), $this->user());
            } else {
                // reload the objects if needed
                if ($this->obj_type == self::TYPE_LINK) {
                    if (!$this->load_objects()) {
                        $msg .= 'Reloading of linked objects ' . $this->obj_name . ' ' . $this->dsp_id() . ' failed.';
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
                            log_err('Delete failed', $this->obj_name . '->del', 'Delete failed, because no median user found for ' . $this->obj_name . ' ' . $this->dsp_id() . ' but change is nevertheless not allowed.', (new Exception)->getTraceAsString(), $this->user());
                        } else {
                            log_debug('set owner for ' . $this->dsp_id() . ' to user id "' . $new_owner_id . '"');

                            // TODO change the original object, so that it uses the configuration of the new owner

                            // set owner
                            if (!$this->set_owner($new_owner_id)) {
                                $msg .= 'Setting of owner while deleting ' . $this->obj_name . ' failed';
                                log_err($msg, $this->obj_name . '->del');

                            }

                            // delete all user records of the new owner
                            // does not use del_usr_cfg because the deletion request has already been logged
                            if ($msg == '') {
                                if (!$this->del_usr_cfg_exe($db_con)) {
                                    $msg .= 'Deleting of ' . $this->obj_name . ' failed';
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
                            if ($this->obj_type == self::TYPE_LINK) {
                                if (!$db_rec->load_objects()) {
                                    $msg .= 'Reloading of linked objects ' . $this->obj_name . ' ' . $this->dsp_id() . ' failed.';
                                }
                            }
                        }
                        if ($msg == '') {
                            $std_rec = clone $this;
                            $std_rec->reset();
                            $std_rec->id = $this->id;
                            $std_rec->set_user($this->user()); // must also be set to allow to take the ownership
                            if (!$std_rec->load_standard()) {
                                $msg .= 'Reloading of standard ' . $this->obj_name . ' ' . $this->dsp_id() . ' failed.';
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
        return 'A ' . $this->obj_name . ' with the name ' . $obj_to_add->dsp_id() . ' already exists. '
            . 'Please use another ' . $obj_to_add->obj_name . ' name.';
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
            if ($this->obj_name == sql_db::TBL_TRIPLE) {
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
            if ($this->obj_name == sql_db::TBL_FORMULA_LINK) {
                $log->set_field(formula_link::FLD_TYPE);
            } elseif ($this->obj_name == sql_db::TBL_WORD) {
                $log->set_field(phrase::FLD_TYPE);
            } elseif ($this->obj_name == sql_db::TBL_TRIPLE) {
                $log->set_field(phrase::FLD_TYPE);
            } else {
                $log->set_field($this->obj_name . sql_db::FLD_EXT_TYPE_ID);
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

}


