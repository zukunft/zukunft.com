<?php

/*

  user.php - a person who uses zukunft.com
  --------

  if a user has done 3 value edits he can add new values (adding a word to a value also creates a new value)
  if a user has added 3 values and at least one is accepted by another user, he can add words and formula and he must have a valid email
  if a user has added 2 formula and both are accepted by at least one other user and no one has complained, he can change formulas and words, including linking of words
  if a user has linked a 10 words and all got accepted by one other user and no one has complained, he can request new verbs and he must have an validated address

  if a user got 10 pending word or formula discussion, he can no longer add words or formula utils the open discussions are less than 10
  if a user got 5 pending word or formula discussion, he can no longer change words or formula utils the open discussions are less than 5
  if a user got 2 pending verb discussion, he can no longer add verbs utils the open discussions are less than 2

  the same ip can max 10 add 10 values and max 5 user a day, upon request the number of max user creation can be increased for an ip range

  
  
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

class user
{

    // database fields
    public ?int $id = null;               // the database id of the word link type (verb)
    public ?string $name = null;          // simply the username, which cannot be empty
    public ?string $ip_addr = null;       // simply the ip address used if no username is given
    public ?string $email = null;         //
    public ?string $first_name = null;    //
    public ?string $last_name = null;     //
    public ?string $code_id = null;       // the main id to detect system users
    public ?string $dec_point = null;     // the decimal point char for this user
    public ?string $thousand_sep = null;  // the thousand separator user for this user
    public ?int $profile_id = null;       // id of the user profile
    public ?int $source_id = null;        // id of the last source used by the user

    // user setting parameters
    public ?int $wrd_id = null;           // id of the last word viewed by the user
    public ?int $vrb_id = null;           // id of the last verb used by the user

    // in memory only fields
    public ?word $wrd = null;             // the last word viewed by the user

    //
    private function load_db()
    {

        global $db_con;

        $db_usr = null;
        // select the user either by id, code_id, name or ip
        $sql_where = '';
        if ($this->id > 0) {
            $sql_where = "u.user_id = " . $this->id;
            log_debug('user->load user id ' . $this->id);
        } elseif ($this->code_id > 0) {
            $sql_where = "u.code_id = " . $this->code_id;
        } elseif ($this->name <> '') {
            $sql_where = "u.user_name = " . sf($this->name);
        } elseif ($this->ip_addr <> '') {
            $sql_where = "u.ip_address = " . sf($this->ip_addr);
        }

        log_debug('user->load search by "' . $sql_where . '"');
        if ($sql_where == '') {
            log_err("Either the database ID, the user name, the ip address or the code_id must be set for loading a user.", "user->load", '', (new Exception)->getTraceAsString(), $this);
        } else {
            $sql = "SELECT u.user_id,
                     u.code_id,
                     u.user_name,
                     u.ip_address,
                     u.email,
                     u.first_name,
                     u.last_name,
                     u.last_word_id,
                     u.source_id,
                     u.user_profile_id
                FROM users u 
              WHERE " . $sql_where . ";";
            $db_con->usr_id = $this->id;
            $db_usr = $db_con->get1($sql);
        }
        return $db_usr;
    }

    // load the missing user parameters from the database
    // private because the loading should be done via the get method
    private function load(): bool
    {
        $result = false;
        $db_usr = $this->load_db();
        if (isset($db_usr)) {
            if ($db_usr['user_id'] <= 0) {
                $this->id = 0;
            } else {
                $this->id = $db_usr['user_id'];
                $this->code_id = $db_usr[sql_db::FLD_CODE_ID];
                $this->name = $db_usr['user_name'];
                $this->ip_addr = $db_usr['ip_address'];
                $this->email = $db_usr['email'];
                $this->first_name = $db_usr['first_name'];
                $this->last_name = $db_usr['last_name'];
                $this->wrd_id = $db_usr['last_word_id'];
                $this->source_id = $db_usr['source_id'];
                $this->profile_id = $db_usr['user_profile_id'];
                $this->dec_point = DEFAULT_DEC_POINT;
                $this->thousand_sep = DEFAULT_THOUSAND_SEP;
                $result = true;
            }
            log_debug('user->load (' . $this->name . ')');
        }
        return $result;
    }

    // special function to exposed the user loading for simulating test users for the automatic system test
    // TODO used also in the user sandbox: check if this is correct
    function load_test_user(): bool
    {
        return $this->load();
    }

    private function ip_in_range($ip_addr, $min, $max): bool
    {
        $result = false;
        if (ip2long(trim($min)) <= ip2long(trim($ip_addr)) && ip2long(trim($ip_addr)) <= ip2long(trim($max))) {
            log_debug('user->ip_in_range ip ' . $ip_addr . ' (' . ip2long(trim($ip_addr)) . ') is in range between ' . $min . ' (' . ip2long(trim($min)) . ') and  ' . $max . ' (' . ip2long(trim($max)) . ')');
            return true;
        }
        return $result;

    }

    // return the message, why the if is not permitted
    // exposed as public mainly for testing
    public function ip_check($ip_addr): string
    {
        log_debug('user->ip_check (' . $ip_addr . ')');

        global $db_con;

        $msg = '';
        $sql = "SELECT ip_from,
                   ip_to,
                   reason
              FROM user_blocked_ips 
             WHERE is_active = 1;";
        $db_con->usr_id = $this->id;
        $ip_lst = $db_con->get($sql);
        foreach ($ip_lst as $ip_range) {
            log_debug('user->ip_check range (' . $ip_range['ip_from'] . ' to ' . $ip_range['ip_to'] . ')');
            if ($this->ip_in_range($ip_addr, $ip_range['ip_from'], $ip_range['ip_to'])) {
                log_debug('user->ip_check ip ' . $ip_addr . ' blocked due to range from ' . $ip_range['ip_from'] . ' to ' . $ip_range['ip_to']);
                $msg = 'Your IP ' . $ip_addr . ' is blocked at the moment because ' . $ip_range['reason'] . '. If you think, this should not be the case, please request the unblocking with an email to admin@zukunft.com.';
                $this->id = 0; // switch off the permission
            }
        }
        return $msg;
    }

    // get the ip address of the active user
    private function get_ip()
    {
        if (array_key_exists("REMOTE_ADDR", $_SERVER)) {
            $this->ip_addr = $_SERVER['REMOTE_ADDR'];
        }
        if ($this->ip_addr == null) {
            $this->ip_addr = 'localhost';
        }
        return $this->ip_addr;
    }

    // get the active session user object
    function get()
    {
        $result = ''; // for the result message e.g. if the user is blocked

        // test first if the IP is blocked
        if ($this->ip_addr == '') {
            $this->get_ip();
        } else {
            log_debug('user->get (' . $this->ip_addr . ')');
        }
        // even if the user has an open session, but the ip is blocked, drop the user
        $result .= $this->ip_check($this->ip_addr);

        if ($result == '') {
            // if the user has logged in use the logged in account
            if (isset($_SESSION['logged'])) {
                if ($_SESSION['logged']) {
                    $this->id = $_SESSION['usr_id'];
                    $this->load();
                    log_debug('user->get -> use (' . $this->id . ')');
                }
            } else {
                // else use the IP address (for testing don't overwrite any testing ip)
                $this->get_ip();
                $this->load();
                if ($this->id <= 0) {
                    // use the ip address as the username and add the user
                    $this->name = $this->ip_addr;
                    $upd_result = $this->save();
                    // adding a new user automatically is normal, so the result does not need to be shown to the user
                    if (str_replace('1', '', $upd_result) <> '') {
                        $result = $upd_result;
                    }
                }
            }
        }
        log_debug('user->got "' . $this->name . '" (' . $this->id . ')');
        return $result;
    }

    // true if the user has admin rights
    function is_admin(): bool
    {
        log_debug('user->is_admin (' . $this->id . ')');
        $result = false;

        if (!isset($this->profile_id)) {
            $this->load();
        }
        if ($this->profile_id == cl(db_cl::USER_PROFILE, user_profile_list::DBL_ADMIN)) {
            $result = true;
        }
        return $result;
    }

    // true if the user has the right to import data
    function can_import(): bool
    {
        log_debug('user->can_import (' . $this->id . ')');
        $result = false;

        if (!isset($this->profile_id)) {
            $this->load();
        }
        if ($this->profile_id == cl(db_cl::USER_PROFILE, user_profile_list::DBL_ADMIN)) {
            $result = true;
        }
        return $result;
    }

    // load the last word used by the user
    function last_wrd(): word_dsp
    {
        if ($this->wrd_id <= 0) {
            $this->wrd_id = DEFAULT_WORD_ID;
        }
        $wrd = new word_dsp;
        $wrd->id = $this->wrd_id;
        $wrd->usr = $this;
        $wrd->load();
        $this->wrd = $wrd;
        return $wrd;
    }

    // set the parameters for the virtual user that represents the standard view for all users
    function dummy_all()
    {
        $this->id = 0;
        $this->code_id = 'all';
        $this->name = 'standard user view for all users';
    }

    //
    function dsp_id(): string
    {
        return $this->name . ' (' . $this->id . ')';
    }

    // create the display user object based on the object (no needed any more if always the display user object is used)
    function dsp_user(): user_dsp
    {
        $dsp_user = new user_dsp;
        $dsp_user->id = $this->id;
        $dsp_user->load();
        return $dsp_user;
    }

    // create the HTML code to display the user name with the HTML link
    function display(): string
    {
        return '<a href="/http/user.php?id=' . $this->id . '">' . $this->name . '</a>';
    }

    // remember the last source that the user has used
    function set_source($source_id): bool
    {
        log_debug('user->set_source(' . $this->id . ',s' . $source_id . ')');
        global $db_con;
        //$db_con = new mysql;
        $db_con->usr_id = $this->id;
        $db_con->set_type(DB_TYPE_USER);
        return $db_con->update($this->id, 'source_id', $source_id);
    }

    // remember the last source that the user has used
    // todo add the database field
    function set_verb($vrb_id): bool
    {
        log_debug('user->set_verb(' . $this->id . ',s' . $vrb_id . ')');
        global $db_con;
        //$db_con = new mysql;
        $db_con->usr_id = $this->id;
        $result = $db_con->set_type(DB_TYPE_USER);
        //$result = $db_con->update($this->id, 'verb_id', $vrb_id);
        return $result;
    }

    // set the main log entry parameters for updating one word field
    private function log_upd(): user_log
    {
        log_debug('user->log_upd user ' . $this->name);
        $log = new user_log;
        $log->usr = $this;
        $log->action = 'update';
        $log->table = 'users';

        return $log;
    }

    // check and update a single user parameter
    private function upd_par($db_con, $usr_par, $db_row, $fld_name, $par_name)
    {
        $result = '';
        if ($usr_par[$par_name] <> $db_row[$fld_name]
            and $usr_par[$par_name] <> "") {
            $log = $this->log_upd();
            $log->old_value = $db_row[$fld_name];
            $log->new_value = $usr_par[$par_name];
            $log->row_id = $this->id;
            $log->field = $fld_name;
            if ($log->add()) {
                $db_con->set_type(DB_TYPE_USER);
                $result = $db_con->update($this->id, $log->field, $log->new_value);
            }
        }
        return $result;
    }

    // check and update all user parameters
    function upd_pars($usr_par)
    {
        log_debug('user->upd_pars');
        log_debug('user->upd_pars(u' . $this->id . ',p' . dsp_array($usr_par) . ')');

        global $db_con;

        $result = ''; // reset the html code var

        // build the database object because the is anyway needed
        $db_con->usr_id = $this->id;
        $db_con->set_type(DB_TYPE_USER);

        $db_usr = new user;
        $db_usr->id = $this->id;
        $db_row = $db_usr->load_db();
        log_debug('user->save -> database user loaded "' . $db_row['name'] . '"');

        $this->upd_par($db_con, $usr_par, $db_row, "user_name", 'name');
        $this->upd_par($db_con, $usr_par, $db_row, "email", 'email');
        $this->upd_par($db_con, $usr_par, $db_row, "first_name", 'fname');
        $this->upd_par($db_con, $usr_par, $db_row, "last_name", 'lname');

        log_debug('user->upd_pars -> done');
        return $result;
    }

    // if at least one other user has switched off all changes from this user
    // all changes of this user should be excluded from the standard values
    // e.g. a user has a
    function exclude_from_standard()
    {
    }

    // create a new user or update the existing
    // returns the id of the updated or created user
    function save(): string
    {
        global $db_con;

        $result = '';

        // build the database object because the is anyway needed
        //$db_con = new mysql;
        $db_con->usr_id = $this->id;
        $db_con->set_type(DB_TYPE_USER);

        if ($this->id <= 0) {
            log_debug("user->save add (" . $this->name . ")");

            $this->id = $db_con->insert("user_name", $this->name);
            // log the changes???
            if ($this->id > 0) {
                // add the ip address to the user
                if ($db_con->update($this->id, "ip_address", $this->get_ip())) {
                    $result = strval($this->id);
                }
                log_debug("user->save add ... done." . $result . ".");
            } else {
                log_debug("user->save add ... failed." . $result . ".");
            }
        } else {
            // update the ip address and log the changes????
            log_warning('user->save method for ip update missing', 'user->save', 'method for ip update missing', (new Exception)->getTraceAsString(), $this);
        }
        return $result;
    }
}
