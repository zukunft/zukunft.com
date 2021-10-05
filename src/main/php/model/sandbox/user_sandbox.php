<?php

/*

  user_sandbox.php - the superclass for handling user specific objects including the database saving
  ----------------

  This superclass should be used by the classes words, formula, ... to enable user specific values and links


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
  along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.

  To contact the authors write to:
  Timon Zielonka <timon@zukunft.com>

  Copyright (c) 1995-2021 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>

  http://zukunft.com

*/

// TODO align the function return types with the source (ref) object
// TODO use the user sandbox also for the word object
// TODO check if handling of negative ids is correct

class user_sandbox
{

    const TYPE_NAMED = 'named';  // for user sandbox objects which have a unique name like formulas
    const TYPE_LINK = 'link';    // for user sandbox objects that link two objects like formula links
    const TYPE_VALUE = 'value';  // for user sandbox objects that are used to save values

    // fields to define the object; should be set in the constructor of the child object
    public ?string $obj_name = null;       // the object type to create the correct database fields e.g. for the type "word" the database field for the id is "word_id"
    public ?string $obj_type = null;       // either a "named" object or a "link" object
    public bool $rename_can_switch = True; // true if renaming an object can switch to another object with the new name

    // database fields that are used in all objects and that have a specific behavior
    public ?int $id = null;            // the database id of the object, which is the same for the standard and the user specific object
    public ?int $usr_cfg_id = null;    // the database id if there is already some user specific configuration for this object
    public ?user $usr = null;          // the person for whom the object is loaded, so to say the viewer
    public ?int $owner_id = null;      // the user id of the person who created the object, which is the default object
    public ?int $share_id = null;      // id for public, personal, group or private
    public ?int $protection_id = null; // id for no, user, admin or full protection
    public ?bool $excluded = null;     // the user sandbox for object is implemented, but can be switched off for the complete instance
    // but for calculation, use and display an excluded should not be used
    // when loading the word and saving the excluded field is handled as a normal user sandbox field,
    // but for calculation, use and display an excluded should not be used

    // database fields only used for objects that have a name
    public ?string $name = '';   // simply the object name, which cannot be empty if it is a named object

    // database fields only used for the value object
    public ?float $number = null; // simply the numeric value

    // database fields only used for objects that link two objects
    // TODO create a more specific object that covers all the objects that could be linked e.g. linkable_object
    public ?object $fob = null;        // the object from which this linked object is creating the connection
    public ?object $tob = null;        // the object to   which this linked object is creating the connection
    public ?string $from_name = null;  // the name of the from object type e.g. view for view_component_links
    public ?string $to_name = '';      // the name of the  to  object type e.g. view for view_component_links

    // database fields only used for the type objects such as words, formulas, values, terms and view component links
    public ?int $type_id = null; // the id of the source type, view type, view component type or word type e.g. to classify measure words
    // or the formula type to link special behavior to special formulas like "this" or "next"
    public ?string $type_name = ''; // the name of the word type, word link type, view type, view component type or formula type


    // to be overwritten by the child object
    function __construct()
    {
        $this->obj_type = user_sandbox::TYPE_NAMED;
    }

    // reset the search values of this object
    // needed to search for the standard object, because the search is work, value, formula or ... specific
    function reset()
    {
        $this->id = null;
        $this->usr_cfg_id = null;
        $this->usr = null;
        $this->owner_id = null;
        $this->excluded = null;

        $this->name = '';

        $this->number = null;

        $this->fob = null;
        $this->tob = null;
    }

    /*
      these functions differ for each object, so they are always in the child class and not this in the superclass

      private function row_mapper() {
      }

      private function load_standard() {
      }

      function load() {
      }

    */

    function load_owner(): bool
    {
        global $db_con;
        $result = false;

        if ($this->id > 0) {

            // TODO: try to avoid using load_test_user
            if ($this->owner_id > 0) {
                $usr = new user;
                $usr->id = $this->owner_id;
                if ($usr->load_test_user()) {
                    $this->usr = $usr;
                    $result = true;
                }
            } else {
                // take the ownership if it is not yet done. The ownership is probably missing due to an error in an older program version.
                $db_con->set_type($this->obj_name);
                if ($this->usr == null) {
                    log_err('Cannot set owner, because not user is set');
                } else {
                    $db_con->set_usr($this->usr->id);
                    if ($db_con->update($this->id, 'user_id', $this->usr->id)) {
                        $result = true;
                    }
                }
            }
        }
        return $result;
    }

    // dummy function to get the missing objects from the database that is always overwritten by the child class
    // returns false if the loading has failed
    function load_objects(): bool
    {
        return true;
    }

    // dummy function to get the missing reference object values from the database that is always overwritten by the child class
    // returns false if the loading has failed
    function load_standard(): bool
    {
        return true;
    }

    // dummy function to get the missing object values from the database that is always overwritten by the child class
    // returns false if the loading has failed
    function load(): bool
    {
        return true;
    }

    // return best possible identification for this object mainly used for debugging
    function dsp_id(): string
    {
        $result = '';
        if ($this->obj_type == user_sandbox::TYPE_NAMED) {
            if ($this->name <> '') {
                $result .= '"' . $this->name . '"';
                if ($this->id > 0) {
                    $result .= ' (' . $this->id . ')';
                }
            } else {
                $result .= $this->id;
            }
        } elseif ($this->obj_type == user_sandbox::TYPE_LINK) {
            if (isset($this->fob) or isset($this->tob)) {
                if (isset($this->fob)) {
                    $result .= 'from ' . $this->fob->dsp_id() . ' ';
                }
                if (isset($this->tob)) {
                    $result .= 'to ' . $this->tob->dsp_id();
                }
                $result .= ' of type ';
            } else {
                $result .= $this->name . ' (' . $this->id . ') of type ';
            }
            $result .= $this->obj_name . ' ' . $this->obj_type;
        } elseif ($this->obj_type == user_sandbox::TYPE_VALUE) {
            if (isset($this->grp)) {
                $result .= $this->grp->dsp_id();
            }
            if (isset($this->time_phr)) {
                if ($result <> '') {
                    $result .= '@';
                }
                if (gettype($this->time_phr) == 'object') {
                    $result .= $this->time_phr->dsp_id();
                }
            }
        } else {
            $result .= $this->obj_name . ' with id ' . $this->id . ' and unexpected type ' . $this->obj_type;
        }
        if (isset($this->usr)) {
            $result .= ' for user ' . $this->usr->id . ' (' . $this->usr->name . ')';
        }
        return $result;
    }

    // todo What should be returned?
    function id_used_msg(): string
    {
        return $this->dsp_id();
    }

    /*

    check functions

    */

    /*
    // check if the owner is set for all records of an user sandbox object
    // e.g. if the owner of a new word_link is set correctly at creation
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
      $db_con->usr_id = $this->usr->id;
      $db_con->set_type($this->obj_name);

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

    // get the term corresponding to this word or formula name
    // so in this case, if a formula or verb with the same name already exists, get it
    function term(): term
    {
        $trm = new term;
        $trm->name = $this->name;
        $trm->usr = $this->usr;
        $trm->load();
        return $trm;
    }

    /*

    type loading functions TODO load type lists upfront

    */

    // load the share type and return the share code id
    function share_type_code_id(): string
    {
        global $share_types;
        return $share_types->code_id($this->share_id);
    }

    // load the share type and return the share code id
    function share_type_name()
    {
        log_debug('value->share_type_name for ' . $this->dsp_id());

        global $db_con;
        $result = '';

        // use the default share type if not set
        if ($this->share_id <= 0) {
            $this->share_id = cl(db_cl::SHARE_TYPE, share_type_list::DBL_PUBLIC);
        }

        $sql = "SELECT type_name 
              FROM share_types 
             WHERE share_type_id = " . $this->share_id . ";";
        //$db_con = new mysql;
        $db_con->usr_id = $this->usr->id;
        $db_row = $db_con->get1($sql);
        if (isset($db_row)) {
            $result = $db_row['type_name'];
        }

        log_debug('value->share_type_name for ' . $this->dsp_id() . ' got ' . $result);
        return $result;
    }

    /**
     * return the protection type code id
     */
    function protection_type_code_id(): string
    {
        global $protection_types;
        return $protection_types->code_id($this->protection_id);
    }

    // load the protection type and return the protection code id
    function protection_type_name()
    {
        log_debug('value->protection_type_name for ' . $this->dsp_id());

        global $db_con;
        $result = '';

        // use the default share type if not set
        if ($this->protection_id <= 0) {
            $this->protection_id = cl(db_cl::PROTECTION_TYPE, protection_type_list::DBL_NO);
        }

        $sql = "SELECT type_name
              FROM protection_types 
             WHERE protection_type_id = " . $this->protection_id . ";";
        //$db_con = new mysql;
        $db_con->usr_id = $this->usr->id;
        $db_row = $db_con->get1($sql);
        if (isset($db_row)) {
            $result = $db_row['type_name'];
        }

        log_debug('value->protection_type_name for ' . $this->dsp_id() . ' got ' . $result);
        return $result;
    }

    /*
    save functions
    */

    function changer_sql(sql_db $db_con, bool $get_name = false): string
    {
        $sql_name = $this->obj_name . '_changer';
        if ($this->owner_id > 0) {
            $sql_name .= '_ex_owner';
        }

        $sql_avoid_code_check_prefix = "SELECT";
        if ($this->owner_id > 0) {
            $sql = $sql_avoid_code_check_prefix . ' user_id 
                FROM user_' . $this->obj_name . 's 
               WHERE ' . $this->obj_name . '_id = ' . $this->id . '
                 AND user_id <> ' . $this->owner_id . '
                 AND (excluded <> 1 OR excluded is NULL)';
        } else {
            $sql = $sql_avoid_code_check_prefix . ' user_id 
                FROM user_' . $this->obj_name . 's 
               WHERE ' . $this->obj_name . '_id = ' . $this->id . '
                 AND (excluded <> 1 OR excluded is NULL)';
        }

        if ($get_name) {
            $result = $sql_name;
        } else {
            $result = $sql;
        }
        return $result;
    }

    // if the object has been changed by someone else than the owner the user id is returned
    // but only return the user id if the user has not also excluded it
    function changer()
    {
        log_debug($this->obj_name . '->changer ' . $this->dsp_id());

        global $db_con;

        $db_con->set_type($this->obj_name);
        $db_con->usr_id = $this->usr->id;
        $sql = $this->changer_sql($db_con);
        $db_row = $db_con->get1($sql);
        $user_id = $db_row['user_id'];

        log_debug($this->obj_name . '->changer is ' . $user_id);
        return $user_id;
    }

    // a list of all user that have ever changed the object
    function usr_lst(): user_list
    {
        log_debug($this->obj_name . '->usr_lst ' . $this->dsp_id());

        global $db_con;

        $result = new user_list;

        // add object owner
        $result->add_by_id($this->owner_id);

        $sql = 'SELECT user_id 
              FROM user_' . $this->obj_name . 's 
              WHERE ' . $this->obj_name . '_id = ' . $this->id . '
                AND (excluded <> 1 OR excluded is NULL)';
        $db_usr_lst = $db_con->get($sql);
        foreach ($db_usr_lst as $db_usr) {
            $result->add_by_id($db_usr['user_id']);
        }
        $result->load_by_id();

        return $result;
    }

    // get the user id of the most often used link (position) beside the standard (position)
    //
    // TODO review, because the median is not taking into account the number of standard used values
    function median_user()
    {
        log_debug($this->obj_name . '->median_user ' . $this->dsp_id() . ' beside the owner (' . $this->owner_id . ')');

        global $db_con;
        $result = 0;

        $sql = 'SELECT user_id 
              FROM user_' . $this->obj_name . 's 
              WHERE ' . $this->obj_name . '_id = ' . $this->id . '
                AND (excluded <> 1 OR excluded is NULL)';
        //$db_con = new mysql;
        $db_con->usr_id = $this->usr->id;
        $db_row = $db_con->get1($sql);
        if ($db_row['user_id'] > 0) {
            $result = $db_row['user_id'];
        } else {
            if ($this->owner_id > 0) {
                $result = $this->owner_id;
            } else {
                if ($this->usr->id > 0) {
                    $result = $this->usr->id;
                }
            }
        }
        log_debug($this->obj_name . '->median_user for ' . $this->dsp_id() . ' -> ' . $result);
        return $result;
    }

    // create a user setting for all objects that does not match the new standard object
    // TODO review
    function usr_cfg_create_all($std)
    {
        $result = '';
        log_debug($this->obj_name . '->usr_cfg_create_all ' . $this->dsp_id());

        // get a list of users that are using this object
        $usr_lst = $this->usr_lst();
        foreach ($usr_lst as $usr) {
            // create a usr cfg if needed
        }

        log_debug($this->obj_name . '->usr_cfg_create_all for ' . $this->dsp_id() . ' -> ' . $result);
        return $result;
    }

    // remove all user setting that are not needed any more based on the new standard object
    // TODO review
    function usr_cfg_cleanup($std)
    {
        $result = '';
        log_debug($this->obj_name . '->usr_cfg_cleanup ' . $this->dsp_id());

        // get a list of users that have a user cfg of this object
        $usr_lst = $this->usr_lst();
        foreach ($usr_lst as $usr) {
            // remove the usr cfg if not needed any more
        }

        log_debug($this->obj_name . '->usr_cfg_cleanup for ' . $this->dsp_id() . ' -> ' . $result);
        return $result;
    }

    // if the user is an admin the user can force to be the owner of this object
    // TODO review
    function take_ownership(): bool
    {
        $result = false;
        log_debug($this->obj_name . '->take_ownership ' . $this->dsp_id());

        if ($this->usr->is_admin()) {
            // TODO activate $result .= $this->usr_cfg_create_all();
            $result = $this->set_owner($this->usr->id); // TODO remove double getting of the user object
            // TODO activate $result .= $this->usr_cfg_cleanup();
        }

        log_debug($this->obj_name . '->take_ownership ' . $this->dsp_id() . ' -> done');
        return $result;
    }

    // change the owner of the object
    // any calling function should make sure that taking setting the owner is allowed
    // and that all user values
    // TODO review sql and object field compare of user and standard
    function set_owner($new_owner_id): bool
    {
        log_debug($this->obj_name . '->set_owner ' . $this->dsp_id() . ' to ' . $new_owner_id);

        global $db_con;
        $result = true;

        if ($this->id > 0 and $new_owner_id > 0) {
            // to recreate the calling object
            $std = clone $this;
            $std->reset();
            $std->id = $this->id;
            $std->usr = $this->usr;
            $std->load_standard();

            $db_con->set_type($this->obj_name);
            $db_con->set_usr($this->usr->id);
            if (!$db_con->update($this->id, 'user_id', $new_owner_id)) {
                $result = false;
            }

            $this->owner_id = $new_owner_id;
            $new_owner = new user;
            $new_owner->id = $new_owner_id;
            if ($new_owner->load_test_user()) {
                $this->usr = $new_owner;
            } else {
                $result = false;
            }

            log_debug($this->obj_name . '->set_owner for ' . $this->dsp_id() . ' to ' . $new_owner_id . ' -> number of db updates: ' . $result);
        }
        return $result;
    }

    // true if no other user has modified the object
    // assuming that in this case no confirmation from the other users for an object change is needed
    function not_changed(): bool
    {
        $result = true;
        log_debug($this->obj_name . '->not_changed (' . $this->id . ') by someone else than the owner (' . $this->owner_id . ')');

        $other_usr_id = $this->changer();
        if ($other_usr_id > 0) {
            $result = false;
        }

        log_debug($this->obj_name . '->not_changed -> (' . $this->id . ' is ' . zu_dsp_bool($result) . ')');
        return $result;
    }

    // true if no one has used the object
    // TODO if this has been used for calculation, this is also used
    function not_used(): bool
    {
        $result = true;
        log_debug($this->obj_name . '->not_used (' . $this->id . ')');

        $using_usr_id = $this->median_user();
        if ($using_usr_id > 0) {
            $result = false;
        }

        log_debug($this->obj_name . '->not_used -> (' . zu_dsp_bool($result) . ')');
        return $result;
    }

    // true if no else one has used the object
    // TODO if this should be true if no one else has been used this object e.g. for calculation
    function used_by_someone_else(): bool
    {
        $result = true;
        log_debug($this->obj_name . '->used_by_someone_else (' . $this->id . ')');

        log_debug($this->obj_name . '->used_by_someone_else owner is ' . $this->owner_id . ' and the change is requested by ' . $this->usr->id);
        if ($this->owner_id == $this->usr->id or $this->owner_id <= 0) {
            $changer_id = $this->changer();
            // removed "OR $changer_id <= 0" because if no one has changed the object jet does not mean that it can be changed
            log_debug($this->obj_name . '->used_by_someone_else changer is ' . $changer_id . ' and the change is requested by ' . $this->usr->id);
            if ($changer_id == $this->usr->id or $changer_id <= 0) {
                $result = false;
            }
        }

        log_debug($this->obj_name . '->used_by_someone_else -> (' . zu_dsp_bool($result) . ')');
        return $result;
    }

    // true if the user is the owner and no one else has changed the object
    // because if another user has changed the object and the original value is changed, maybe the user object also needs to be updated
    function can_change(): bool
    {
        $result = false;

        // if the user who wants to change it, is the owner, he can do it
        // or if the owner is not set, he can do it (and the owner should be set, because every object should have an owner)
        log_debug($this->obj_name . '->can_change owner is ' . $this->owner_id . ' and the change is requested by ' . $this->usr->id);
        if ($this->owner_id == $this->usr->id or $this->owner_id <= 0) {
            $result = true;
        } else {
            $changer_id = $this->changer();
            // removed "OR $changer_id <= 0" because if no one has changed the object jet does not mean that it can be changed
            if ($changer_id == $this->usr->id) {
                $result = true;
            }
        }

        log_debug($this->obj_name . '->can_change -> (' . zu_dsp_bool($result) . ')');
        return $result;
    }

    // true if a record for a user specific configuration already exists in the database
    function has_usr_cfg(): bool
    {
        $result = false;
        if ($this->usr_cfg_id > 0) {
            $result = true;
        }

        log_debug($this->obj_name . '->has_usr_cfg -> (' . zu_dsp_bool($result) . ')');
        return $result;
    }

    // simply remove a user adjustment without check
    function del_usr_cfg_exe($db_con): bool
    {
        log_debug($this->obj_name . '->del_usr_cfg_exe ' . $this->dsp_id());

        $db_con->set_type(DB_TYPE_USER_PREFIX . $this->obj_name);
        $result = $db_con->delete(
            array($this->obj_name . '_id', 'user_id'),
            array($this->id, $this->usr->id));
        if (!$result) {
            $result .= 'Deletion of user ' . $this->obj_name . ' ' . $this->id . ' failed for ' . $this->usr->name . '.';
        }

        return $result;
    }

    // remove user adjustment and log it (used by user.php to undo the user changes)
    function del_usr_cfg(): bool
    {
        log_debug($this->obj_name . '->del_usr_cfg ' . $this->dsp_id());

        global $db_con;
        $result = true;

        if ($this->id > 0 and $this->usr->id > 0) {
            $log = $this->log_del();
            if ($log->id > 0) {
                $db_con->usr_id = $this->usr->id;
                $result = $this->del_usr_cfg_exe($db_con);
            }

        } else {
            log_err('The database ID and the user must be set to remove a user specific modification of ' . $this->obj_name . '.', $this->obj_name . '->del_usr_cfg');
        }

        return $result;
    }

    // dummy function to create a database record to save user specific settings that is always overwritten by the child class
    // returns false if the creation has failed and true if it was successful or not needed
    function add_usr_cfg(): bool
    {
        global $db_con;
        $result = true;

        if (!$this->has_usr_cfg()) {
            if ($this->obj_type == user_sandbox::TYPE_NAMED) {
                log_debug($this->obj_name . '->add_usr_cfg for "' . $this->dsp_id() . ' und user ' . $this->usr->name);
            } elseif ($this->obj_type == user_sandbox::TYPE_LINK) {
                if (isset($this->from) and isset($this->to)) {
                    log_debug($this->obj_name . '->add_usr_cfg for "' . $this->from->name . '"/"' . $this->to->name . '" by user "' . $this->usr->name . '"');
                } else {
                    log_debug($this->obj_name . '->add_usr_cfg for "' . $this->id . '" and user "' . $this->usr->name . '"');
                }
            } else {
                log_err('Unknown user sandbox type ' . $this->obj_type . ' in ' . $this->obj_name, $this->obj_name . '->log_add');
            }

            // check again if there ist not yet a record
            $db_con->set_type($this->obj_name, true);
            $db_con->set_usr($this->usr->id);
            $db_con->set_where($this->id);
            $sql = $db_con->select();
            $db_row = $db_con->get1($sql);
            if ($db_row != null) {
                $this->usr_cfg_id = $db_row[$db_con->get_id_field()];
            }
            if (!$this->has_usr_cfg()) {
                // create an entry in the user sandbox
                $db_con->set_type(DB_TYPE_USER_PREFIX . $this->obj_name);
                $db_con->set_usr($this->usr->id);
                $log_id = $db_con->insert(array($db_con->get_id_field(), sql_db::FLD_USER_ID), array($this->id, $this->usr->id));
                if ($log_id <= 0) {
                    log_err('Insert of ' . sql_db::USER_PREFIX . $this->obj_name . ' failed.');
                    $result = false;
                } else {
                    $result = true;
                }
            }
        }
        return $result;
    }

    // dummy function to check if the database record for the user specific settings can be removed that is always overwritten by the child class
    // returns false if the deletion has failed and true if it was successful or not needed
    function del_usr_cfg_if_not_needed(): bool
    {
        return true;
    }

    // set the log entry parameter for a new named object
    // for all not named objects like links, this function is overwritten
    // e.g. that the user can see "added formula 'scale millions' to word 'mio'"
    function log_add()
    {
        log_debug($this->obj_name . '->log_add ' . $this->dsp_id());
        if ($this->obj_type == user_sandbox::TYPE_NAMED) {
            $log = new user_log;
            $log->field = $this->obj_name . '_name';
            $log->old_value = '';
            $log->new_value = $this->name;
        } elseif ($this->obj_type == user_sandbox::TYPE_VALUE) {
            $log = new user_log;
            $log->field = 'word_value';
            $log->old_value = '';
            $log->new_value = $this->number;
        } elseif ($this->obj_type == user_sandbox::TYPE_LINK) {
            $log = new user_log_link;
            $log->new_from = $this->fob;
            $log->new_to = $this->tob;
        } else {
            $log = new user_log;
            log_err('Unknown user sandbox type ' . $this->obj_type . ' in ' . $this->obj_name, $this->obj_name . '->log_add');
        }
        $log->usr = $this->usr;
        $log->action = 'add';
        // TODO add the table exceptions from sql_db
        $log->table = $this->obj_name . 's';
        $log->row_id = 0;
        $log->add();

        return $log;
    }

    // set the main log entry parameters for updating one field
    private function log_upd_common($log)
    {
        log_debug($this->obj_name . '->log_upd_common ' . $this->dsp_id());
        $log->usr = $this->usr;
        $log->action = 'update';
        if ($this->can_change()) {
            // TODO add the table exceptions from sql_db
            $log->table = $this->obj_name . 's';
        } else {
            $log->table = DB_TYPE_USER_PREFIX . $this->obj_name . 's';
        }

        return $log;
    }

    // create a log object for an update of an object field
    function log_upd_field(): user_log
    {
        log_debug($this->obj_name . '->log_upd_field ' . $this->dsp_id());
        $log = new user_log;
        return $this->log_upd_common($log);
    }

    // create a log object for an update of link
    function log_upd_link(): user_log
    {
        log_debug($this->obj_name . '->log_upd_link ' . $this->dsp_id());
        $log = new user_log_link;
        return $this->log_upd_common($log);
    }

    // create a log object for an update of an object field or an link
    // e.g. that the user can see "moved formula list to position 3 in phrase view"
    function log_upd()
    {
        log_debug($this->obj_name . '->log_upd ' . $this->dsp_id());
        if ($this->obj_type == user_sandbox::TYPE_NAMED) {
            $log = $this->log_upd_field();
        } else {
            $log = $this->log_upd_link();
        }
        return $this->log_upd_common($log);
    }

    // set the log entry parameter to delete a object
    function log_del()
    {
        log_debug($this->obj_name . '->log_del ' . $this->dsp_id());
        if ($this->obj_type == user_sandbox::TYPE_NAMED) {
            $log = new user_log;
            $log->field = $this->obj_name . '_name';
            $log->old_value = $this->name;
            $log->new_value = '';
        } elseif ($this->obj_type == user_sandbox::TYPE_VALUE) {
            $log = new user_log;
            $log->field = 'word_value';
            $log->old_value = $this->number;
            $log->new_value = '';
        } elseif ($this->obj_type == user_sandbox::TYPE_LINK) {
            $log = new user_log_link;
            $log->old_from = $this->fob;
            $log->old_to = $this->tob;
        } else {
            $log = new user_log;
            log_err('Unknown user sandbox type ' . $this->obj_type . ' in ' . $this->obj_name, $this->obj_name . '->log_del');
        }
        $log->usr = $this->usr;
        $log->action = 'del';
        $log->table = $this->obj_name . 's';
        $log->row_id = $this->id;
        $log->add();

        return $log;
    }

    /**
     * check if this object uses any preserved names and if return a message to the user
     *
     * @return string
     */
    private function check_preserved(): string
    {
        global $usr;

        $result = '';
        if (!$usr->is_system()) {
            if ($this->obj_type == user_sandbox::TYPE_NAMED) {
                if ($this->obj_name == DB_TYPE_WORD) {
                    if (in_array($this->name, word::RESERVED_WORDS)) {
                        $result = '"' . $this->name . '" is a reserved name for system testing. Please use another name';
                    }
                }
            }
        }
        return $result;
    }

    /**
     * set the update parameters for the word type
     * TODO: log the ref
     */
    function save_field_type($db_con, $db_rec, $std_rec): bool
    {
        $result = true;
        if ($db_rec->type_id <> $this->type_id) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->type_name();
            $log->old_id = $db_rec->type_id;
            $log->new_value = $this->type_name();
            $log->new_id = $this->type_id;
            $log->std_value = $std_rec->type_name();
            $log->std_id = $std_rec->type_id;
            $log->row_id = $this->id;
            $log->field = 'word_type_id';
            $result = $this->save_field_do($db_con, $log);
            log_debug('word->save_field_type changed type to "' . $log->new_value . '" (' . $log->new_id . ')');
        }
        return $result;
    }

    /**
     * dummy function to save all updated word fields, which is always overwritten by the child class
     */
    function save_fields($db_con, $db_rec, $std_rec): bool
    {
        return true;
    }

    // actually update a field in the main database record or the user sandbox
    // the usr id is taken into account in sql_db->update (maybe move outside)
    function save_field_do($db_con, $log): bool
    {
        $result = true;

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
                        log_debug($this->obj_name . '->save_field_do remove user change');
                        $db_con->set_type(DB_TYPE_USER_PREFIX . $this->obj_name);
                        $db_con->set_usr($this->usr->id);
                        $result = $db_con->update($this->id, $log->field, Null);
                    }
                    $this->del_usr_cfg_if_not_needed(); // don't care what the result is, because in most cases it is fine to keep the user sandbox row
                } else {
                    $db_con->set_type($this->obj_name);
                    $result = $db_con->update($this->id, $log->field, $new_value);
                }
            } else {
                if (!$this->has_usr_cfg()) {
                    if (!$this->add_usr_cfg()) {
                        $result = false;
                    }
                }
                if ($result) {
                    $db_con->set_type(DB_TYPE_USER_PREFIX . $this->obj_name);
                    $db_con->set_usr($this->usr->id);
                    if ($new_value == $std_value) {
                        log_debug($this->obj_name . '->save_field_do remove user change');
                        $result = $db_con->update($this->id, $log->field, Null);
                    } else {
                        $result = $db_con->update($this->id, $log->field, $new_value);
                    }
                    $this->del_usr_cfg_if_not_needed(); // don't care what the result is, because in most cases it is fine to keep the user sandbox row
                }
            }
        }
        return $result;
    }

    // set the update parameters for the value excluded
    // returns false if something has gone wrong
    function save_field_excluded($db_con, $db_rec, $std_rec): bool
    {
        log_debug($this->obj_name . '->save_field_excluded ' . $this->dsp_id());
        $result = true;

        if ($db_rec->excluded <> $this->excluded) {
            if ($this->excluded == 1) {
                $log = $this->log_del();
            } else {
                $log = $this->log_add();
            }
            $new_value = $this->excluded;
            $std_value = $std_rec->excluded;
            $log->field = 'excluded';
            // similar to $this->save_field_do
            if ($this->can_change()) {
                $db_con->set_type($this->obj_name);
                $result = $db_con->update($this->id, $log->field, $new_value);
            } else {
                if (!$this->has_usr_cfg()) {
                    if (!$this->add_usr_cfg()) {
                        $result = false;
                    }
                }
                if ($result) {
                    $db_con->set_type(DB_TYPE_USER_PREFIX . $this->obj_name);
                    if ($new_value == $std_value) {
                        $result = $db_con->update($this->id, $log->field, Null);
                    } else {
                        $result = $db_con->update($this->id, $log->field, $new_value);
                    }
                    if (!$this->del_usr_cfg_if_not_needed()) {
                        $result = false;
                    }
                }
            }
        }
        return $result;
    }

    // save the share level in the database if allowed
    function save_field_share($db_con, $db_rec, $std_rec): bool
    {
        log_debug($this->obj_name . '->save_field_share ' . $this->dsp_id());
        $result = true;

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
            $log->field = sql_db::FLD_SHARE;

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
                        $result = false;
                    }
                }
                if ($result) {
                    $db_con->set_type(DB_TYPE_USER_PREFIX . $this->obj_name);
                    $result = $db_con->update($this->id, $log->field, $new_value);
                }
            }
        }

        log_debug($this->obj_name . '->save_field_share ' . $this->dsp_id());
        return $result;
    }

    // save the protection level in the database if allowed
    // TODO is the setting of the standard needed?
    function save_field_protection($db_con, $db_rec, $std_rec)
    {
        $result = '';
        log_debug($this->obj_name . '->save_field_protection ' . $this->dsp_id());

        if ($db_rec->protection_id <> $this->protection_id) {
            $log = $this->log_upd_field();
            $log->old_value = $db_rec->protection_type_name();
            $log->old_id = $db_rec->protection_id;
            $log->new_value = $this->protection_type_name();
            $log->new_id = $this->protection_id;
            $log->std_value = $std_rec->protection_type_name();
            $log->std_id = $std_rec->protection_id;
            $log->row_id = $this->id;
            $log->field = sql_db::FLD_PROTECT;
            $result .= $this->save_field_do($db_con, $log);
        }

        log_debug($this->obj_name . '->save_field_protection ' . $this->dsp_id());
        return $result;
    }

    // updated the object id fields (e.g. for a word or formula the name, and for a link the linked ids)
    // should only be called if the user is the owner and nobody has used the display component link
    // returns either the id of the updated or created source or a message to the user with the reason, why it has failed
    function save_id_fields($db_con, $db_rec, $std_rec): bool
    {
        $result = true;
        log_debug($this->obj_name . '->save_id_fields ' . $this->dsp_id());

        if ($this->is_id_updated($db_rec)) {
            $log = null;
            log_debug($this->obj_name . '->save_id_fields to ' . $this->dsp_id() . ' from ' . $db_rec->dsp_id() . ' (standard ' . $std_rec->dsp_id() . ')');
            if ($this->obj_type == user_sandbox::TYPE_NAMED) {
                $log = $this->log_upd_field();
                $log->old_value = $db_rec->name;
                $log->new_value = $this->name;
                $log->std_value = $std_rec->name;
                $log->field = $this->obj_name . '_name';
            } elseif ($this->obj_type == user_sandbox::TYPE_VALUE) {
                log_err('The user sandbox save_id_fields does not support ' . $this->obj_type . ' for ' . $this->obj_name, $this->obj_name . '->save_id_fields');
            } elseif ($this->obj_type == user_sandbox::TYPE_LINK) {
                $log = $this->log_upd_link();
                $log->old_from = $db_rec->fob;
                $log->new_from = $this->fob;
                $log->std_from = $std_rec->fob;
                $log->old_to = $db_rec->tob;
                $log->new_to = $this->tob;
                $log->std_to = $std_rec->tob;
            } else {
                log_err('Unknown user sandbox type ' . $this->obj_type . ' in ' . $this->obj_name, $this->obj_name . '->save_id_fields');
            }
            $log->row_id = $this->id;
            if ($log->add()) {
                if ($this->obj_type == user_sandbox::TYPE_NAMED) {
                    $db_con->set_type($this->obj_name);
                    $result = $db_con->update($this->id,
                        array($this->obj_name . '_name'),
                        array($this->name));
                } elseif ($this->obj_type == user_sandbox::TYPE_LINK) {
                    $db_con->set_type($this->obj_name);
                    $result = $db_con->update($this->id,
                        array($this->from_name . '_id', $this->from_name . '_id'),
                        array($this->fob->id, $this->tob->id));
                }
            }
        }
        log_debug($this->obj_name . '->save_id_fields for ' . $this->dsp_id() . ' done');
        return $result;
    }

    // check if the id parameters are supposed to be changed
    // TODO add the link type for word links
    private function is_id_updated($db_rec): bool
    {
        $result = False;
        log_debug($this->obj_name . '->is_id_updated ' . $this->dsp_id());

        if ($this->obj_type == user_sandbox::TYPE_NAMED) {
            log_debug($this->obj_name . '->is_id_updated compare name ' . $db_rec->name . ' with ' . $this->name);
            if ($db_rec->name <> $this->name) {
                $result = True;
            }
        } elseif ($this->obj_type == user_sandbox::TYPE_LINK) {
            log_debug($this->obj_name . '->is_id_updated compare id ' . $db_rec->fob->id . '/' . $db_rec->tob->id . ' with ' . $this->fob->id . '/' . $this->tob->id);
            if ($db_rec->fob->id <> $this->fob->id
                or $db_rec->tob->id <> $this->tob->id) {
                $result = True;
                // TODO check if next line is needed
                // $this->reset_objects();
            }
        } else {
            log_err('Unexpected type ' . $this->obj_type);
        }

        log_debug($this->obj_name . '->is_id_updated -> (' . zu_dsp_bool($result) . ')');
        return $result;
    }

    // check if the id parameters are supposed to be changed
    // return an empty string if everything is fine or a messages for the user what should be changed
    function save_id_if_updated($db_con, $db_rec, $std_rec): string
    {
        log_debug($this->obj_name . '->save_id_if_updated ' . $this->dsp_id());
        $result = '';

        if ($this->is_id_updated($db_rec)) {
            // check if target key value already exists
            log_debug($this->obj_name . '->save_id_if_updated check if target already exists ' . $this->dsp_id() . ' (has been "' . $db_rec->dsp_id() . '")');
            $db_chk = clone $this;
            $db_chk->id = 0; // to force the load by the id fields
            $db_chk->load_standard();
            if ($db_chk->id > 0) {
                log_debug($this->obj_name . '->save_id_if_updated target already exists');
                if ($this->rename_can_switch) {
                    // ... if yes request to delete or exclude the record with the id parameters before the change
                    $to_del = clone $db_rec;
                    if (!$to_del->del()) {
                        $result = 'Failed to delete the unused ' . $this->obj_name;
                        log_err($result);
                    }
                    if ($result = '') {
                        // .. and use it for the update
                        // TODO review the logging: from the user view this is a change not a delete and update
                        $this->id = $db_chk->id;
                        $this->owner_id = $db_chk->owner_id;
                        // TODO check which links needs to be updated, because this is a kind of combine objects
                        // force the include again
                        $this->excluded = null;
                        $db_rec->excluded = '1';
                        if ($this->save_field_excluded($db_con, $db_rec, $std_rec)) {
                            log_debug($this->obj_name . '->save_id_if_updated found a ' . $this->obj_name . ' target ' . $db_chk->dsp_id() . ', so del ' . $db_rec->dsp_id() . ' and add ' . $this->dsp_id());
                        } else {
                            //$result = 'Failed to exclude the unused ' . $this->obj_name;
                            $result .= 'A ' . $this->obj_name . ' with the name "' . $this->name . '" already exists. Please use another name or merge with this ' . $this->obj_name . '.';
                            log_err($result);
                        }
                    }
                } else {
                    if ($this->obj_type == user_sandbox::TYPE_NAMED) {
                        $result .= 'A ' . $this->obj_name . ' with the name "' . $this->name . '" already exists. Please use another name.';
                    } elseif ($this->obj_type == user_sandbox::TYPE_LINK) {
                        $result .= 'A ' . $this->obj_name . ' from ' . $this->fob->dsp_id() . ' to ' . $this->tob->dsp_id() . ' already exists.';
                    }
                }
            } else {
                log_debug($this->obj_name . '->save_id_if_updated target does not yet exist');
                // TODO check if e.g. for word links and formula links "and $this->not_used()" needs to be added
                if ($this->can_change()) {
                    // in this case change is allowed and done
                    log_debug($this->obj_name . '->save_id_if_updated change the existing ' . $this->obj_name . ' ' . $this->dsp_id() . ' (db ' . $db_rec->dsp_id() . ', standard ' . $std_rec->dsp_id() . ')');
                    // TODO check if next line is needed
                    //$this->load_objects();
                    if (!$this->save_id_fields($db_con, $db_rec, $std_rec)) {
                        $result = 'Failed to update the recreated ' . $this->obj_name;
                        log_err($result);
                    }
                } else {
                    // if the target link has not yet been created
                    // ... request to delete the old
                    $to_del = clone $db_rec;
                    if (!$to_del->del()) {
                        $result = 'Failed to delete the unused ' . $this->obj_name;
                        log_err($result);
                    }
                    // TODO .. and create a deletion request for all users ???

                    if ($result = '') {
                        // ... and create a new display component link
                        $this->id = 0;
                        $this->owner_id = $this->usr->id;
                        if ($this->add()) {
                            log_debug($this->obj_name . '->save_id_if_updated recreate the ' . $this->obj_name . ' del ' . $db_rec->dsp_id() . ' add ' . $this->dsp_id() . ' (standard ' . $std_rec->dsp_id() . ')');
                        } else {
                            $result = 'Failed to add the recreated ' . $this->obj_name;
                            log_err($result);
                        }
                    }
                }
            }
        }

        return $result;
    }

    // create a new object
    // returns the id of the creates object
    // TODO do a rollback in case of an error
    function add(): int
    {
        log_debug($this->obj_name . '->add ' . $this->dsp_id());

        global $db_con;
        $result = 0;

        // log the insert attempt first
        $log = $this->log_add();
        if ($log->id > 0) {

            // insert the new object and save the object key
            // TODO check that always before a db action is called the db type is set correctly
            $db_con->set_type($this->obj_name);
            if ($this->obj_type == user_sandbox::TYPE_NAMED) {
                $this->id = $db_con->insert(array($this->obj_name . '_name', "user_id"), array($this->name, $this->usr->id));
            } elseif ($this->obj_type == user_sandbox::TYPE_LINK) {
                $this->id = $db_con->insert(array($this->from_name . '_id', $this->to_name . '_id', "user_id", 'order_nbr'), array($this->fob->id, $this->tob->id, $this->usr->id, $this->order_nbr));
            } else {
                log_err('Method add cannot (yet) handle objects of type ' . $this->obj_type . '.', 'user_sandbox->add');
            }

            // save the object fields if saving the key was successful
            if ($this->id > 0) {
                log_debug($this->obj_name . '->add ' . $this->obj_type . ' ' . $this->dsp_id() . ' has been added');
                // update the id in the log
                if (!$log->add_ref($this->id)) {
                    log_err('Updating the reference in the log failed');
                    // TODO do rollback or retry?
                } else {
                    //$result .= $this->set_owner($new_owner_id);

                    // create an empty db_rec element to force saving of all set fields
                    $db_rec = clone $this;
                    $db_rec->reset();
                    if ($this->obj_type == user_sandbox::TYPE_NAMED) {
                        $db_rec->name = $this->name;
                    } elseif ($this->obj_type == user_sandbox::TYPE_LINK) {
                        $db_rec->fob = $this->fob;
                        $db_rec->tob = $this->tob;
                    }
                    $db_rec->usr = $this->usr;
                    $std_rec = clone $db_rec;
                    // save the object fields
                    if ($this->save_fields($db_con, $db_rec, $std_rec)) {
                        $result = $this->id;
                    }
                }

            } else {
                log_err('Adding ' . $this->obj_name . ' failed', 'user_sandbox->add', 'Adding ' . $this->obj_type . ' ' . $this->dsp_id() . ' failed due to logging error.', (new Exception)->getTraceAsString(), $this->usr);
            }
        }

        return $result;
    }

    // check if the unique key (not the db id) of two user sandbox object is the same if the object type is the same, so the simple case
    private function is_same_std($obj_to_check): bool
    {
        $result = false;
        if ($this->obj_type == user_sandbox::TYPE_NAMED) {
            if ($this->name == $obj_to_check->name) {
                $result = true;
            }
        } elseif ($this->obj_type == user_sandbox::TYPE_LINK) {
            if (isset($this->fob)
                and isset($this->tob)
                and isset($obj_to_check->fob)
                and isset($obj_to_check->tob)) {
                if ($this->fob->id == $obj_to_check->fob->id and
                    $this->tob->id == $obj_to_check->tob->id) {
                    $result = true;
                }
            } else {
                log_err('The objects of ' . $this->dsp_id() . ' and ' . $obj_to_check->dsp_id() . ' are not loaded');
            }
        }
        return $result;
    }

    // check the the given object is by the unique keys the same as the actual object
    // handles the specials case that for each formula a corresponding word is created (which needs to be check if this is really needed)
    // so if a formula word "millions" is not the same as the standard word "millions" because the formula word "millions" is representing a formula which should not be combined
    // in short: if two objects are the same by this definition, they are supposed to be merged
    function is_same($obj_to_check): bool
    {
        $result = false;
        // special case a word should not be combined with a word that is representing a formulas
        if ($this->obj_name == DB_TYPE_WORD and $obj_to_check->obj_name == DB_TYPE_WORD) {
            if ($this->name == $obj_to_check->name) {
                if (isset($this->type_id) and isset($obj_to_check->type_id)) {
                    if ($this->type_id == $obj_to_check->type_id) {
                        $result = true;
                    } else {
                        if ($this->type_id == DB_TYPE_FORMULA and $obj_to_check->type_id == cl(db_cl::WORD_TYPE, word_type_list::DBL_FORMULA_LINK)) {
                            // if one is a formula and the other is a formula link word, the two objects are representing the same formula object (but the calling function should use the formula to update)
                            $result = true;
                        } elseif ($obj_to_check->type_id == DB_TYPE_FORMULA and $this->type_id == cl(db_cl::WORD_TYPE, word_type_list::DBL_FORMULA_LINK)) {
                            // like above, but the other way round
                            $result = true;
                        } elseif ($this->type_id == cl(db_cl::WORD_TYPE, word_type_list::DBL_FORMULA_LINK) or $obj_to_check->type_id == cl(db_cl::WORD_TYPE, word_type_list::DBL_FORMULA_LINK)) {
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

    // just to double check if the get similar function is working correctly
    // so if the formulas "millions" is compared with the word "millions" this function returns true
    // in short: if two objects are similar by this definition, they should not be both in the database
    function is_similar($obj_to_check): bool
    {
        $result = false;
        if ($obj_to_check != null) {
            if ($this->obj_name == $obj_to_check->obj_name) {
                $result = $this->is_same_std($obj_to_check);
            } else {
                // create a synthetic unique index over words, phrase, verbs and formulas
                if ($this->obj_name == DB_TYPE_WORD or $this->obj_name == DB_TYPE_PHRASE or $this->obj_name == DB_TYPE_FORMULA or $this->obj_name == DB_TYPE_VERB) {
                    if ($this->name == $obj_to_check->name) {
                        $result = true;
                    }
                }
            }
        }
        return $result;
    }

    // check if an object with the unique key already exists
    // returns null if no similar object is found
    // or returns the object with the same unique key that is not the actual object
    // any warning or error message needs to be created in the calling function
    // e.g. if the user tries to create a formula named "millions"
    //      but a word with the same name already exists, a term with the word "millions" is returned
    //      in this case the calling function should suggest the user to name the formula "scale millions"
    //      to prevent confusion when writing a formula where all words, phrases, verbs and formulas should be unique
    function get_similar()
    {
        $result = null;

        // check potential duplicate by name
        if ($this->obj_type == user_sandbox::TYPE_NAMED) {
            // for words and formulas it needs to be checked if a term (word, verb or formula) with the same name already exist
            // for verbs the check is inside the verbs class because verbs are not part of the user sandbox
            if ($this->obj_name == DB_TYPE_WORD or $this->obj_name == DB_TYPE_FORMULA) {
                $similar_trm = $this->term();
                if ($similar_trm != null) {
                    if ($similar_trm->obj != null) {
                        $result = $similar_trm->obj;
                        if (!$this->is_similar($result)) {
                            log_err($this->dsp_id() . ' is supposed to be similar to ' . $result->dsp_id() . ', but it seems not');
                        }
                    }
                }
            } else {
                // used for view, view_component, source, ...
                $db_chk = clone $this;
                $db_chk->reset();
                $db_chk->usr = $this->usr;
                $db_chk->name = $this->name;
                // check with the standard namespace
                if ($db_chk->load_standard()) {
                    if ($db_chk->id > 0) {
                        log_debug($this->obj_name . '->get_similar "' . $this->dsp_id() . '" has the same name is the already existing "' . $db_chk->dsp_id() . '" of the standard namespace');
                        $result = $db_chk;
                    }
                }
                // check with the user namespace
                if ($result == null) {
                    $db_chk->usr = $this->usr;
                    if ($db_chk->load()) {
                        if ($db_chk->id > 0) {
                            log_debug($this->obj_name . '->get_similar "' . $this->dsp_id() . '" has the same name is the already existing "' . $db_chk->dsp_id() . '" of the user namespace');
                            $result = $db_chk;
                        }
                    }
                }
            }
        } elseif ($this->obj_type == user_sandbox::TYPE_LINK) {
            // check for linked objects
            if (!isset($this->fob) or !isset($this->tob)) {
                log_err('The linked objects for ' . $this->dsp_id() . ' are missing.', 'user_sandbox->get_similar');
            } else {
                $db_chk = clone $this;
                $db_chk->reset();
                $db_chk->fob = $this->fob;
                $db_chk->tob = $this->tob;
                if ($db_chk->load_standard()) {
                    if ($db_chk->id > 0) {
                        log_debug($this->obj_name . '->get_similar the ' . $this->fob->name . ' "' . $this->fob->name . '" is already linked to "' . $this->tob->name . '" of the standard linkspace');
                        $result = $db_chk;
                    }
                }
                // check with the user linkspace
                if ($result == null) {
                    $db_chk->usr = $this->usr;
                    if ($db_chk->load()) {
                        if ($db_chk->id > 0) {
                            log_debug($this->obj_name . '->get_similar the ' . $this->fob->name . ' "' . $this->fob->name . '" is already linked to "' . $this->tob->name . '" of the user linkspace');
                            $result = $db_chk;
                        }
                    }
                }
            }
        } else {
            $result = log_err('Method get_similar cannot (yet) handle objects of type ' . $this->obj_type . '.', 'user_sandbox->get_similar');
        }

        return $result;
    }

    /*

     a word rename creates a new word and a word deletion request
     a word is deleted after all users have confirmed
     words with an active deletion request are listed at the end
     a word can have a formula linked
     values and formulas can be linked to a word, a triple or a word group
     verbs needs a confirmation for creation (but the name can be reserved)
     all other parameters beside the word/verb name can be user specific

     time words are separated from the word groups to reduce the number of word groups
     for daily data or shorter a normal date or time field is used
     a time word can also describe a period

    */

    // add or update a user sandbox object (word, value, formula or ...) in the database
    // returns either the id of the updated or created object or a message with the reason why it has failed that can be shown to the user
    /*
     * the save used cases are
     *
     * 1. a source is supposed to be saved with without id and         a name  and no source                with the same name already exists -> add the source
     * 2. a source is supposed to be saved with without id and         a name, but  a source                with the same name already exists -> ask the user to confirm the changes or use another name (at the moment simply update)
     * 3. a word   is supposed to be saved with without id and         a name  and no word, verb or formula with the same name already exists -> add the word
     * 4. a word   is supposed to be saved with without id and         a name, but  a word                  with the same name already exists -> ask the user to confirm the changes or use another name (at the moment simply update)
     * 5. a word   is supposed to be saved with without id and         a name, but  a verb or formula       with the same name already exists -> ask the user to use another name (or rename the formula)
     * 6. a source is supposed to be saved with with    id and a changed name -> the source is supposed to be renamed -> check if the new name is already used -> (6a.) if yes,            ask to merge, change the name or cancel the update -> (6b.) if the new name does not exist, ask the user to confirm the changes
     * 7. a word   is supposed to be saved with with    id and a changed name -> the word   is supposed to be renamed -> check if the new name is already used -> (7a.) if yes for a word, ask to merge, change the name or cancel the update -> (7b.) if the new name does not exist, ask the user to confirm the changes
     *                                                                                                                                                         -> (7c.) if yes for a verb, ask to        change the name or cancel the update
     *
     * TODO add wizards to handle the update chains
     *
     */

    function save(): string
    {
        log_debug($this->obj_name . '->save ' . $this->dsp_id());

        global $db_con;

        // check the preserved names
        $result = $this->check_preserved();

        if ($result == '') {

            // load the objects if needed
            if ($this->obj_type == user_sandbox::TYPE_LINK) {
                $this->load_objects();
            }

            // configure the global database connection object for the select, insert, update and delete queries
            $db_con->set_type($this->obj_name);
            $db_con->set_usr($this->usr->id);

            // create an object to check possible duplicates
            $similar = null;

            // if a new object is supposed to be added check upfront for a similar object to prevent adding duplicates
            if ($this->id == 0) {
                log_debug($this->obj_name . '->save check possible duplicates before adding ' . $this->dsp_id());
                $similar = $this->get_similar();
                if ($similar != null) {
                    // check that the get_similar function has really found a similar object and report
                    if (!$this->is_similar($similar)) {
                        log_err($this->dsp_id() . ' seems to be not similar to ' . $similar->dsp_id());
                    }
                    if ($similar->id <> 0) {
                        // if similar is found set the id to trigger the updating instead of adding
                        $similar->load(); // e.g. to get the type_id
                        $this->id = $similar->id;
                    } else {
                        $similar = null;
                    }
                }
            }

            // create a new object if nothing similar has been found
            if ($this->id == 0) {
                log_debug($this->obj_name . '->save add');
                $result = strval($this->add());
            } else {
                // if the similar object is not the same as $this object, suggest renaming $this object
                if ($similar != null) {
                    log_debug($this->obj_name . '->save got similar and suggest renaming or merge');
                    // if a source already exists update the source
                    // but if a word with the same name of a formula already exists
                    if (!$this->is_same($similar)) {
                        $result = $similar->id_used_msg();
                    }
                }

                // update the existing object
                if ($result == '') {
                    log_debug($this->obj_name . '->save update');

                    // read the database values to be able to check if something has been changed;
                    // done first, because it needs to be done for user and general object values
                    $db_rec = clone $this;
                    $db_rec->reset();
                    $db_rec->id = $this->id;
                    $db_rec->usr = $this->usr;
                    if (!$db_rec->load()) {
                        $result = 'Reloading of user ' . $this->obj_name . ' failed';
                        log_err($result);
                    } else {
                        log_debug($this->obj_name . '->save reloaded from db');
                        if ($this->obj_type == user_sandbox::TYPE_LINK) {
                            if (!$db_rec->load_objects()) {
                                $result = 'Reloading of the object for ' . $this->obj_name . ' failed';
                                log_err($result);
                            }
                            // configure the global database connection object again to overwrite any changes from load_objects
                            $db_con->set_type($this->obj_name);
                        }
                    }

                    // load the common object
                    $std_rec = clone $this;
                    $std_rec->reset();
                    $std_rec->id = $this->id;
                    $std_rec->usr = $this->usr; // must also be set to allow to take the ownership
                    if ($result == '') {
                        if (!$std_rec->load_standard()) {
                            $result = 'Reloading of the default values for ' . $this->obj_name . ' failed';
                            log_err($result);
                        }
                    }

                    // for a correct user setting detection (function can_change) set the owner even if the object has not been loaded before the save
                    if ($result == '') {
                        log_debug($this->obj_name . '->save standard loaded');

                        if ($this->owner_id <= 0) {
                            $this->owner_id = $std_rec->owner_id;
                        }
                    }

                    // check if the id parameters are supposed to be changed
                    if ($result == '') {
                        $result = $this->save_id_if_updated($db_con, $db_rec, $std_rec);
                    }

                    // if a problem has appeared up to here, don't try to save the values
                    // the problem is shown to the user by the calling interactive script
                    if ($result == '') {
                        if (!$this->save_fields($db_con, $db_rec, $std_rec)) {
                            $result = 'Saving of fields for a ' . $this->obj_name . ' failed';
                            log_err($result);
                        }
                    }
                }
            }
        }

        return $result;
    }

    // dummy function to remove depending objects, which needs to be overwritten by the child classes
    function del_links(): bool
    {
        return true;
    }

    // delete the complete object (the calling function del must have checked that no one uses this object)
    // returns false if something went wrong
    private function del_exe(): bool
    {
        log_debug($this->obj_name . '->del_exe ' . $this->dsp_id());

        global $db_con;
        $result = true;

        // log the deletion request
        $log = $this->log_del();
        if ($log->id > 0) {
            //$db_con = new mysql;
            $db_con->usr_id = $this->usr->id;

            // for formulas first delete all links
            if ($this->obj_name == DB_TYPE_FORMULA) {
                $result = $this->del_links();

                // and the corresponding formula elements
                if ($result) {
                    $db_con->set_type(DB_TYPE_FORMULA_ELEMENT);
                    $result = $db_con->delete(DB_TYPE_FORMULA . DB_FIELD_EXT_ID, $this->id);
                }

                // and the corresponding word name
                if ($result) {
                    $db_con->set_type(DB_TYPE_WORD);
                    $result = $db_con->delete(DB_TYPE_WORD . DB_FIELD_EXT_NAME, $this->name);
                }
            }

            // delete first all user configuration that have also been excluded
            if ($result) {
                $db_con->set_type(DB_TYPE_USER_PREFIX . $this->obj_name);
                $result = $db_con->delete(
                    array($this->obj_name . DB_FIELD_EXT_ID, 'excluded'),
                    array($this->id, '1'));
            }
            if ($result) {
                // finally delete the object
                $db_con->set_type($this->obj_name);
                $result = $db_con->delete($this->obj_name . '_id', $this->id);
                log_debug($this->obj_name . '->del_exe of ' . $this->dsp_id() . ' done');
            } else {
                log_err('Delete failed for ' . $this->obj_name, $this->obj_name . '->del_exe', 'Delete failed, because removing the user settings for ' . $this->obj_name . ' ' . $this->dsp_id() . ' returns ' . $result, (new Exception)->getTraceAsString(), $this->usr);
            }
        }

        return $result;
    }

    // exclude or delete an object
    // TODO if the owner deletes it, change the owner to the new median user
    // TODO check if all have deleted the object
    //      does not remove the user excluding if no one else is using it
    function del(): bool
    {
        log_debug($this->obj_name . '->del ' . $this->dsp_id());

        global $db_con;
        $result = false;

        // refresh the object with the database to include all updates utils now (TODO start of lock for commit here)
        // TODO it seems that the owner is not updated
        if (!$this->load()) {
            log_warning('Reload of for deletion has lead to unexpected', $this->obj_name . '->del', 'Reload of ' . $this->obj_name . ' ' . $this->dsp_id() . ' for deletion or exclude has unexpectedly lead to ' . $result . '.', (new Exception)->getTraceAsString(), $this->usr);
        } else {
            log_debug($this->obj_name . '->del reloaded ' . $this->dsp_id());
            // check if the object is still valid
            if ($this->id <= 0) {
                log_warning('Delete failed', $this->obj_name . '->del', 'Delete failed, because it seems that the ' . $this->obj_name . ' ' . $this->dsp_id() . ' has been deleted in the meantime.', (new Exception)->getTraceAsString(), $this->usr);
            } else {
                // check if the object simply can be deleted, because it has never been used
                if (!$this->used_by_someone_else()) {
                    $result = $this->del_exe();
                } else {
                    // if the owner deletes the object find a new owner or delete the object completely
                    if ($this->owner_id == $this->usr->id) {
                        log_debug($this->obj_name . '->del owner has requested the deletion');
                        // get median user
                        $new_owner_id = $this->median_user();
                        if ($new_owner_id == 0) {
                            log_err('Delete failed', $this->obj_name . '->del', 'Delete failed, because no median user found for ' . $this->obj_name . ' ' . $this->dsp_id() . ' but change is nevertheless not allowed.', (new Exception)->getTraceAsString(), $this->usr);
                        } else {
                            log_debug($this->obj_name . '->del set owner for ' . $this->dsp_id() . ' to user id "' . $new_owner_id . '"');

                            // TODO change the original object, so that it uses the configuration of the new owner

                            // set owner
                            $result = $this->set_owner($new_owner_id);

                            // delete all user records of the new owner
                            // does not use del_usr_cfg because the deletion request has already been logged
                            if ($result) {
                                $result = $this->del_usr_cfg_exe($db_con);
                            }

                        }
                    }
                    // check again after the owner change if the object simply can be deleted, because it has never been used
                    // TODO check if "if ($this->can_change() AND $this->not_used()) {" would be correct
                    if (!$this->used_by_someone_else()) {
                        log_debug($this->obj_name . '->del can delete ' . $this->dsp_id() . ' after owner change');
                        $result = $this->del_exe();
                    } else {
                        log_debug($this->obj_name . '->del exclude ' . $this->dsp_id());
                        $this->excluded = 1;

                        // simple version TODO combine with save function

                        $db_rec = clone $this;
                        $db_rec->reset();
                        $db_rec->id = $this->id;
                        $db_rec->usr = $this->usr;
                        $result = $db_rec->load();
                        if ($result) {
                            log_debug($this->obj_name . '->save reloaded ' . $db_rec->dsp_id() . ' from database');
                            if ($this->obj_type == user_sandbox::TYPE_LINK) {
                                $result = $db_rec->load_objects();
                            }
                        }
                        if ($result) {
                            $std_rec = clone $this;
                            $std_rec->reset();
                            $std_rec->id = $this->id;
                            $std_rec->usr = $this->usr; // must also be set to allow to take the ownership
                            $result = $std_rec->load_standard();
                        }
                        if ($result) {
                            log_debug($this->obj_name . '->save loaded standard ' . $std_rec->dsp_id());
                            $this->save_field_excluded($db_con, $db_rec, $std_rec);
                        }
                    }
                }
            }
            // TODO end of db commit and unlock the records
            log_debug($this->obj_name . '->del done');
        }

        return $result;
    }

}


