<?php

/*

    model/value/value_time_series.php - the header object for time series values
    ---------------------------------

    TODO add function that decides if the user values should saved in a complete new time series or if overwrites should be saved
    TODO create a value_time_series_headers table which is only used to create a unique id for the time series data

    To save values that have a timestamp more efficient in a separate table


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

namespace Zukunft\ZukunftCom\main\php\cfg\value;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_SANDBOX . 'sandbox_value.php';
include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_par.php';
include_once paths::DB . 'sql_type.php';
include_once paths::DB . 'sql_type_list.php';
include_once paths::MODEL_CONST . 'def.php';
include_once paths::MODEL_GROUP . 'group.php';
include_once paths::MODEL_REF . 'source.php';
include_once paths::MODEL_REF . 'source_db.php';
include_once paths::MODEL_SANDBOX . 'sandbox.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_db.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED . 'library.php';

use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\main\php\cfg\db\sql;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type_list;
use Zukunft\ZukunftCom\main\php\cfg\group\group;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_value;
use Zukunft\ZukunftCom\main\php\cfg\ref\source;
use Zukunft\ZukunftCom\main\php\cfg\ref\source_db;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_db;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\library;

class value_time_series extends sandbox_value
{

    /*
     * database link
     */

    // object specific database and JSON object field names
    const string TBL_COMMENT = 'for the common parameters for a list of numbers that differ only by the timestamp';
    const string FLD_ID_COM = 'a 64 bit integer value because the number of time series is not expected to be too high';
    const string FLD_ID = 'value_time_series_id';
    const string FLD_LAST_UPDATE_COM = 'timestamp of the last update of any value of the list for fast update detection';
    const string FLD_LAST_UPDATE = 'last_update';

    // all database field names excluding the id and excluding the user-specific fields
    const array FLD_NAMES = array(
        user_db::FLD_ID,
        group::FLD_ID
    );

    // list of the user-specific numeric database field names
    const array FLD_NAMES_NUM_USR = array(
        source_db::FLD_ID,
        sql_db::FLD_EXCLUDED,
        sandbox::FLD_PROTECT
    );

    // list of field names that are only on the user sandbox row
    // e.g. the standard value does not need the share type, because it is by definition public (even if share types within a group of users needs to be defined, the value for the user group are also user sandbox table)
    const array FLD_NAMES_USR_ONLY = array(
        sandbox::FLD_SHARE
    );

    // list of fixed tables for the time series header
    const array TBL_LIST = array(
        [sql_type::MOST],
        [sql_type::PRIME],
        [sql_type::BIG]
    );


    /*
     * object vars
     */

    // related objects used also for database mapping
    public ?source $source;    // the source object

    /*
     * construct and map
     */

    /**
     * set the user sandbox type for a value time series object and set the user, which is needed in all cases
     * @param user $usr the user who requested to see this value
     */
    function __construct(user $usr)
    {
        parent::__construct($usr);

        $this->rename_can_switch = def::UI_CAN_CHANGE_VALUE;

        $this->reset(true);
    }

    function reset(bool $keep_user = false): void
    {
        parent::reset($keep_user);

        $this->set_grp(new group($this->get_user()));
        $this->source = null;
    }

    /*
     * database load functions that reads the object from the database
     */

    /**
     * map the database fields to the object fields
     *
     * @param array|null $db_row with the data directly from the database
     * @param bool $load_std true if only the standard user sandbox object is loaded
     * @param bool $allow_usr_protect false for using the standard protection settings for the default object used for all users
     * @param string $id_fld the name of the id field as defined in this child and given to the parent
     * @return bool true if the value time series is loaded and valid
     */
    function row_mapper_sandbox(
        ?array $db_row,
        bool   $load_std = false,
        bool   $allow_usr_protect = true,
        string $id_fld = self::FLD_ID): bool
    {
        $lib = new library();
        $result = parent::row_mapper_multi($db_row, '', self::FLD_ID);
        if ($result) {
            $this->grp()->set_id($db_row[group::FLD_ID]);
            if ($db_row[source_db::FLD_ID] > 0) {
                $this->source = new source($this->get_user());
                $this->source->id = $db_row[source_db::FLD_ID];
            }
            $this->set_last_update($lib->get_datetime($db_row[self::FLD_LAST_UPDATE], $this->dsp_id()));
        }
        return $result;
    }

    /**
     * create the SQL to load the default time series always by the id
     * @param sql_creator $sc with the target db_type set
     * @param array $fld_lst list of fields either for the value or the result
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql_standard(sql_creator $sc, array $fld_lst = []): sql_par
    {
        $fld_lst = array_merge(self::FLD_NAMES, self::FLD_NAMES_NUM_USR);
        return parent::load_sql_standard($sc, $fld_lst);
    }

    /**
     * create the common part of an SQL statement to retrieve the parameters of a time series from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name extension to make the query name unique
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql(sql_creator $sc, string $query_name, string $class = self::class): sql_par
    {
        $qp = parent::load_sql_obj_vars($sc, $class);
        $qp->name .= $query_name;

        $sc->set_class($class);
        $sc->set_name($qp->name);
        $sc->set_usr($this->get_user()->id);
        $sc->set_fields(self::FLD_NAMES);
        $sc->set_usr_num_fields(self::FLD_NAMES_NUM_USR);
        //$sc->set_usr_only_fields(self::FLD_NAMES_USR_ONLY);

        return $qp;
    }

    /**
     * load the standard value use by most users
     * @param sql_par|null $qp placeholder to align the function parameters with the parent
     * @return bool true if a time series has been loaded
     */
    function load_standard(?sql_par $qp = null): bool
    {
        global $db_con;
        $qp = $this->load_sql_standard($db_con->sql_creator());
        return parent::load_standard($qp);
    }

    /**
     * create the common part of an SQL statement to retrieve the parameters of a value time series
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name extension to make the query name unique
     * @param string $class the name of the child class from where the call has been triggered
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @param string $ext the query name extension e.g. to differentiate queries based on 1,2, or more phrases
     * @param string $id_ext the query name extension that indicated how many id fields are used e.g. "_p1"
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql_multi(
        sql_creator   $sc,
        string        $query_name,
        string        $class = self::class,
        sql_type_list $sc_par_lst = new sql_type_list(),
        string        $ext = '',
        string        $id_ext = ''
    ): sql_par
    {
        $qp = parent::load_sql_multi($sc, $query_name, $class, $sc_par_lst, $ext, $id_ext);

        // overwrite the standard id field name (value_id) with the main database id field for values "group_id"
        $sc->set_id_field($this->id_field());
        $sc->set_name($qp->name);
        $sc->set_fields(self::FLD_NAMES);
        $sc->set_usr($this->get_user()->id);
        $sc->set_usr_num_fields(self::FLD_NAMES_NUM_USR);
        //$sc->set_usr_only_fields(self::FLD_NAMES_USR_ONLY);

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a time series by the phrase group from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param group $grp the phrase group to which the time series should be loaded
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql_by_grp(sql_creator $sc, group $grp, string $class = self::class): sql_par
    {
        $qp = $this->load_sql($sc, group::FLD_ID);
        $sc->add_where(group::FLD_ID, $grp->id());
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * just set the class name for the user sandbox function
     * load a reference object by database id
     * TODO load the related time series data
     * @param int|string $id the id of the reference
     * @param ?sql_type $typ if known the value data type to preselect the table
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_id(
        int|string $id,
        ?sql_type $typ = null
    ): int
    {
        return parent::load_by_id($id);
    }

    /**
     * load a row from the database selected by id
     * TODO load the related time series data
     * @param group $grp the phrase group to which the time series should be loaded
     * @return bool true if time series has been loaded
     */
    function load_by_grp(group $grp, bool $by_source = false): bool
    {
        global $db_con;

        log_debug($grp->dsp_id());
        $qp = $this->load_sql_by_grp($db_con->sql_creator(), $grp);
        return $this->load($qp);
    }

    /**
     * add a new time series
     * @param user_message $usr_msg with status ok
     *                              or if something went wrong
     *                              the message that should be shown to the user
     *                              including suggested solutions
     * @return bool true if everything has been fine
     */
    function add(user_message $usr_msg): bool
    {
        log_debug('->add');

        global $db_con;

        // log the insert attempt first
        $log = $this->log_add();
        if ($log->id() > 0) {
            $db_con->set_class(value_time_series::class);
            $this->id = $db_con->insert_old(
                array(group::FLD_ID, user_db::FLD_ID, self::FLD_LAST_UPDATE),
                array($this->grp()->id(), $this->get_user()->id(), sql::NOW));
            if ($this->id() > 0) {
                // update the reference in the log
                if (!$log->add_ref($this->id())) {
                    $usr_msg->add_id(msg_id::VALUE_TIME_SERIES_LOG_REF_FAILED);
                }

                // update the phrase links for fast searching
                /*
                $upd_result = $this->upd_phr_links();
                if ($upd_result != '') {
                    $result->add_message_text('Adding the phrase links of the value time series failed because ' . $upd_result);
                    $this->set_id(0);
                }
                */

                // create an empty db_rec element to force saving of all set fields
                //$db_vts = new value_time_series($this->get_user());
                //$db_vts->id = $this->id();
                // TODO add the data list saving
            }
        }

        return $usr_msg->is_ok();
    }

    /*
     * info
     */

    /**
     * temp overwrite of the id_field function of sandbox_value class until this class is reviewed
     *
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return string|array the field name(s) of the prime database index of the object
     */
    function id_field(sql_type_list $sc_par_lst = new sql_type_list()): string|array
    {
        $lib = new library();
        return $lib->class_to_name($this::class) . sql_db::FLD_EXT_ID;
    }


    /*
     * write
     */

    /**
     * insert or update a time series in the database or save user-specific time series numbers
     * @param user_message the message that should be shown to the user in case something went wrong
     * @return bool true if everything has been fine
     */
    function save(user_message $msg): bool
    {
        log_debug('->save');

        global $db_con;

        // build the database object because the is anyway needed
        $db_con->set_class(value_time_series::class);
        $db_con->set_usr($this->get_user()->id);

        // check if a new time series is supposed to be added
        if ($this->id() <= 0) {
            // check if a time series for the phrase group is already in the database
            $db_chk = new value_time_series($this->get_user());
            $db_chk->load_by_grp($this->grp());
            if ($db_chk->id() > 0) {
                $this->id = $db_chk->id();
            }
        }

        if ($this->id() <= 0) {
            $this->add($msg);
        } else {
            // update a value
            // TODO: if no one else has ever changed the value, change to default value, else create a user overwrite

            // read the database value to be able to check if something has been changed
            // done first, because it needs to be done for user and general values
            $db_rec = new value_time_series($this->get_user());
            $db_rec->load_by_id($this->id());
            $std_rec = new value_time_series($this->get_user()); // user must also be set to allow to take the ownership
            $std_rec->id = $this->id();
            $std_rec->load_standard();

            // for a correct user value detection (function can_change) set the owner even if the value has not been loaded before the save
            if ($this->owner_id() <= 0) {
                $this->set_owner_id($std_rec->owner_id());
            }

            // check if the id parameters are supposed to be changed
            $this->save_id_if_updated($db_con, $db_rec, $std_rec, $msg);

            // if a problem has appeared up to here, don't try to save the values
            // the problem is shown to the user by the calling interactive script
            // TODO add function based db saving
            if ($msg->is_ok()) {
                // if the user is the owner and no other user has adjusted the value, really delete the value in the database
                $msg->add_message_text($this->save_fields($db_con, $db_rec, $std_rec, $msg));
            }

        }

        if (!$msg->is_ok()) {
            log_err($msg->get_last_message());
        }

        return $msg->is_ok();
    }

}