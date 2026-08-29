<?php

/*

    web/user/user.php - functions to create the HTML code to display the user setup and log information
    -----------------

    $usr is the suggested var name

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

namespace Zukunft\ZukunftCom\main\php\web\user;

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

// get the api const that are shared between the backend and the html frontend
// get the pure html frontend objects
include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::LOG . 'change_log_list.php';
include_once html_paths::LOG . 'user_log_display.php';
//include_once html_paths::REF . 'source.php';
include_once html_paths::SANDBOX . 'db_object.php';
include_once html_paths::SYSTEM . 'sys_log_list.php';
//include_once html_paths::PHRASE . 'term.php';
include_once html_paths::VIEW . 'view.php';
include_once html_paths::SHARED_ENUM . 'user_profiles.php';
include_once html_paths::SHARED_CONST . 'def.php';
include_once html_paths::SHARED_CONST . 'users.php';
include_once html_paths::SHARED_CONST . 'views.php';
include_once html_paths::SHARED_ENUM . 'messages.php';
include_once html_paths::SHARED_HELPER . 'Translator.php';
include_once html_paths::SHARED_TYPES . 'api_type_list.php';
include_once html_paths::SHARED . 'api.php';
include_once html_paths::SHARED . 'json_fields.php';
include_once html_paths::SHARED . 'library.php';
include_once html_paths::SHARED . 'url_var.php';

use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\log\change_log_list;
use Zukunft\ZukunftCom\main\php\web\log\user_log_display;
use Zukunft\ZukunftCom\main\php\web\phrase\term;
use Zukunft\ZukunftCom\main\php\web\ref\source;
use Zukunft\ZukunftCom\main\php\web\sandbox\db_object;
use Zukunft\ZukunftCom\main\php\web\system\sys_log_list;
use Zukunft\ZukunftCom\main\php\web\view\view;
use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\const\def;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\helper\Translator;
use Zukunft\ZukunftCom\main\php\shared\enum\user_profiles;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\api;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use DateTime;
use DateTimeInterface;

class user extends db_object
{

    /*
     * const
     */

    // TODO allow only admin users to add or change other users
    // curl views
    const string VIEW_ADD = views::USER_ADD;
    const string VIEW_EDIT = views::USER_EDIT;
    const string VIEW_DEL = views::USER_DEL;
    const int VIEW_ADD_ID = views::USER_ADMIN_ADD_ID;
    const int VIEW_EDIT_ID = views::USER_ADMIN_EDIT_ID;
    const int VIEW_DEL_ID = views::USER_ADMIN_DEL_ID;

    // curl message id
    const msg_id MSG_ADD = msg_id::USER_ADD;
    const msg_id MSG_EDIT = msg_id::USER_EDIT;
    const msg_id MSG_DEL = msg_id::USER_DEL;


    /*
     * object vars
     */

    // unique keys
    public ?string $name;
    public ?string $ip_addr;
    public ?string $email;

    // log in and sighup
    private ?string $password; // private to restrict the access to the unhashed password e.g. admin user can only overwrite it without seeing the old
    public ?string $activation_key = '';  // var used for the registration and logon process
    public ?DateTime $activation_timeout = null;
    public ?DateTime $db_now = null;      // timestamp of the database server to have a reference with time zone e.g. for the activation timeout
    public ?DateTime $last_login = null;
    public ?DateTime $last_logoff = null;

    // for the permission settings
    private int $profile_id;              // id of the preloaded user profiles to define the base permissions of the user that should be used now
    public ?string $code_id = null;       // the main id to detect system users
    public ?int $type_id = null;          // the confirmation level / status of the user e.g. email checked or passport checked which might lead to a different profile id
    public ?int $right_level = null;      // can be used to reduce the right level of the profile
    public ?int $status_id = null;        // id of the actual status of the user profiles to reduce temporary the user writes of the profile
    public ?bool $excluded = null;        // only use for admin so that they can deactivate users
    public bool $uses_sandbox = false;    // true if the user has changed any data, so the pages cannot be served from the standard page cache

    // additional info
    public ?DateTime $created = null;
    public ?string $description;
    public ?string $first_name;
    public ?string $last_name;

    // speed up cache
    public ?term $trm = null;       // the last term that the user has been looking at
    public ?view $msk = null;             // the last view that the user has been looking at
    public ?source $src = null;           // the last source that the user has been looking at

    // the changes done by this user as loaded with the user for its page, so that the
    // all user overwrites column can be filled without a second backend call
    // (like the chg_log of a word, see ui_log::all_user_overwrites)
    public ?change_log_list $chg_log = null;

    // TODO Prio 0 deprecate
    public ?string $profile;

    /*
     * construct and map
     */

    function __construct(?string $api_json = null)
    {
        $this->reset();
        parent::__construct($api_json);
    }

    function reset(): void
    {
        // more unique keys
        $this->name = null;
        $this->ip_addr = null;
        $this->email = null;

        // log in and sighup
        $this->password = null;
        $this->activation_key = '';
        $this->activation_timeout = null;
        $this->db_now = null;
        $this->last_login = null;
        $this->last_logoff = null;

        // for the permission settings
        $this->profile_id = 0;
        $this->code_id = null;
        $this->type_id = null;
        $this->right_level = null;
        $this->status_id = null;
        $this->excluded = null;
        $this->uses_sandbox = false;

        // additional info
        $this->created = null;
        $this->description = null;
        $this->first_name = null;
        $this->last_name = null;

        // volatile parameter to make the workflow more smooth
        $this->trm = null;
        $this->msk = null;
        $this->src = null;

        $this->profile = null;
    }

    /**
     * set the vars of this word frontend object bases on the url array
     * public because it is reused e.g. by the phrase group display object
     * @param array $url_array an array based on $_GET from a form submitted
     * @param user_message $msg to enrich with warnings, problems and solutions
     * @param data_object|null $dto the cache as a parameter to be able to simulate test conditions
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function url_mapper(array $url_array, user_message $msg, data_object|null $dto = null): user_message
    {
        parent::url_mapper($url_array, $msg, $dto);
        if ($msg->is_ok()) {
            if (array_key_exists(url_var::USERNAME, $url_array)) {
                if ($url_array[url_var::USERNAME] != null) {
                    $this->name = $url_array[url_var::USERNAME];
                }
            } elseif (array_key_exists(url_var::NAME, $url_array)) {
                // a user page is called with the generic name var like any other object page,
                // whereas the admin user edit form posts the name as the username field
                if ($url_array[url_var::NAME] != null) {
                    $this->name = $url_array[url_var::NAME];
                }
            }
            if (array_key_exists(url_var::EMAIL, $url_array)) {
                if ($url_array[url_var::EMAIL] != null) {
                    $this->email = $url_array[url_var::EMAIL];
                }
            }

            if (array_key_exists(url_var::USER_FIRST_NAME, $url_array)) {
                if ($url_array[url_var::USER_FIRST_NAME] != null) {
                    $this->first_name = $url_array[url_var::USER_FIRST_NAME];
                }
            }
            if (array_key_exists(url_var::USER_LAST_NAME, $url_array)) {
                if ($url_array[url_var::USER_LAST_NAME] != null) {
                    $this->last_name = $url_array[url_var::USER_LAST_NAME];
                }
            }
            // an unchecked checkbox is not part of the form post,
            // so the flag is false if it is missing in a post of the admin user edit form
            // and it stays unchanged for all other posts e.g. the user settings
            if (($url_array[url_var::MASK] ?? 0) == views::USER_ADMIN_EDIT_ID) {
                $this->uses_sandbox = array_key_exists(url_var::USER_USES_SANDBOX, $url_array);
            }
        }
        return $msg;
    }

    /**
     * set the vars of this object based on the api json array
     * @param array $json_array an api json message
     * @param user_message $msg OK or a warning e.g. if the server version does not match
     * @return bool true if the mapping has been completed successfully
     */
    function api_mapper(array $json_array, user_message $msg): bool
    {
        $lib = new library();

        if (array_key_exists(json_fields::ID, $json_array)) {
            $this->set_id($json_array[json_fields::ID]);
        } else {
            $this->set_id(0);
            $msg->add_error_text('Mandatory field id missing in API JSON ' . json_encode($json_array));
        }
        if (array_key_exists(json_fields::NAME, $json_array)) {
            $this->name = $json_array[json_fields::NAME];
        } else {
            $this->name = null;
        }
        if (array_key_exists(json_fields::IP_ADDR, $json_array)) {
            $this->ip_addr = $json_array[json_fields::IP_ADDR];
        } else {
            $this->ip_addr = null;
        }
        if (array_key_exists(json_fields::EMAIL, $json_array)) {
            $this->email = $json_array[json_fields::EMAIL];
        } else {
            $this->email = null;
        }

        if (array_key_exists(json_fields::ACTIVATION_KEY, $json_array)) {
            $this->activation_key = $json_array[json_fields::ACTIVATION_KEY];
        } else {
            $this->activation_key = null;
        }
        if (array_key_exists(json_fields::ACTIVATION_TIMEOUT, $json_array)) {
            $this->activation_timeout = $lib->get_datetime($json_array[json_fields::ACTIVATION_TIMEOUT], $this->dsp_id());
        } else {
            $this->activation_timeout = null;
        }
        if (array_key_exists(json_fields::DB_NOW, $json_array)) {
            $this->db_now = $lib->get_datetime($json_array[json_fields::DB_NOW], $this->dsp_id());
        } else {
            $this->db_now = null;
        }
        if (array_key_exists(json_fields::LAST_LOGIN, $json_array)) {
            $this->last_login = $lib->get_datetime($json_array[json_fields::LAST_LOGIN], $this->dsp_id());
        } else {
            $this->last_login = null;
        }
        if (array_key_exists(json_fields::LAST_LOGOFF, $json_array)) {
            $this->last_logoff = $lib->get_datetime($json_array[json_fields::LAST_LOGOFF], $this->dsp_id());
        } else {
            $this->last_logoff = null;
        }

        if (array_key_exists(json_fields::PROFILE_ID, $json_array)) {
            $this->profile_id = $json_array[json_fields::PROFILE_ID];
        } else {
            $this->profile_id = 0;
        }
        if (array_key_exists(json_fields::CODE_ID, $json_array)) {
            $this->code_id = $json_array[json_fields::CODE_ID];
        } else {
            $this->code_id = null;
        }
        if (array_key_exists(json_fields::TYPE, $json_array)) {
            $this->type_id = $json_array[json_fields::TYPE];
        } else {
            $this->type_id = 0;
        }
        if (array_key_exists(json_fields::RIGHT_LEVEL, $json_array)) {
            $this->right_level = $json_array[json_fields::RIGHT_LEVEL];
        } else {
            $this->right_level = null;
        }
        if (array_key_exists(json_fields::STATUS, $json_array)) {
            $this->status_id = $json_array[json_fields::STATUS];
        } else {
            $this->status_id = 0;
        }
        if (array_key_exists(json_fields::EXCLUDED, $json_array)) {
            $this->excluded = $json_array[json_fields::EXCLUDED];
        } else {
            $this->excluded = null;
        }
        // a missing flag reads as false like a null db value (see docs/llm/constants.md)
        if (array_key_exists(json_fields::USES_SANDBOX, $json_array)) {
            $this->uses_sandbox = (bool)$json_array[json_fields::USES_SANDBOX];
        } else {
            $this->uses_sandbox = false;
        }

        if (array_key_exists(json_fields::CREATED, $json_array)) {
            $this->created = $lib->get_datetime($json_array[json_fields::CREATED], $this->dsp_id());
        } else {
            $this->created = null;
        }
        if (array_key_exists(json_fields::DESCRIPTION, $json_array)) {
            $this->description = $json_array[json_fields::DESCRIPTION];
        } else {
            $this->description = null;
        }
        if (array_key_exists(json_fields::FIRST_NAME, $json_array)) {
            $this->first_name = $json_array[json_fields::FIRST_NAME];
        } else {
            $this->first_name = null;
        }
        if (array_key_exists(json_fields::LAST_NAME, $json_array)) {
            $this->last_name = $json_array[json_fields::LAST_NAME];
        } else {
            $this->last_name = null;
        }

        if (array_key_exists(json_fields::TERM_ID, $json_array)) {
            // TODO Prio 1 get term from cache if possible
            $trm = new term();
            $trm->set_id($json_array[json_fields::TERM_ID]);
            $this->trm = $trm;
        } else {
            $this->trm = null;
        }
        if (array_key_exists(json_fields::VIEW_ID, $json_array)) {
            // TODO Prio 1 get term from cache if possible
            $msk = new view();
            $msk->set_id($json_array[json_fields::VIEW_ID]);
            $this->msk = $msk;
        } else {
            $this->msk = null;
        }
        if (array_key_exists(json_fields::SOURCE_ID, $json_array)) {
            // TODO Prio 1 get term from cache if possible
            $src = new source();
            $src->set_id($json_array[json_fields::SOURCE_ID]);
            $this->src = $src;
        } else {
            $this->src = null;
        }

        return $msg->is_ok();
    }


    /*
     * load
     */

    /**
     * load the user by id and the changes that the user has done, so that the user page
     * can fill the all user overwrites column without a second backend call
     * (like a word that carries its change log, see ui_log::all_user_overwrites)
     *
     * @param int|string $id the database id of the user to load
     * @param user_message $msg to collect the load warnings for the user
     * @param int $usr_id the id of the session user to load the object for, 0 for the default
     * @return bool true on a successful load (mirrors load_by_id)
     */
    function load_by_id_with_related(int|string $id, user_message $msg, int $usr_id = 0): bool
    {
        $result = parent::load_by_id($id, $msg, [], $usr_id);
        if ($result) {
            $this->chg_log = new change_log_list();
            $this->chg_log->load_by_user((int)$this->id(), $msg);
        }
        return $result;
    }


    /*
     * set and get
     */

    function name_or_null(): ?string
    {
        return $this->name;
    }

    function name(): string|null
    {
        if ($this->name === null) {
            return '';
        } else {
            return $this->name;
        }
    }

    function get_description(): string
    {
        if ($this->description == null) {
            return '';
        } else {
            return $this->description;
        }
    }
    function last_term(): term|null
    {
        return $this->trm;
    }

    // TODO restrict the access to the unhashed password
    function password(): string|null
    {
        return $this->password;
    }


    /*
     * info
     */

    /**
     * @return bool true if the user is only identified by IP address and has not logged in
     */
    function is_ip_only(): bool
    {
        global $ui_sys;
        if ($this->profile_id <= 0) {
            return true;
        }
        // before the type cache is loaded (e.g. in the cached page fast path) the profile
        // cannot be checked, so assume the most restricted ip only case instead of a fatal
        if ($ui_sys?->typ_lst_cache?->usr_pro == null) {
            return true;
        }
        return $this->profile_id == $ui_sys->typ_lst_cache->usr_pro->id(user_profiles::IP_ONLY);
    }

    /**
     * @returns bool true if the user has admin rights
     */
    function is_admin(): bool
    {
        global $ui_sys;
        log_debug();
        $result = false;

        if ($this->is_profile_valid()) {
            if ($this->profile_id == $ui_sys->typ_lst_cache->usr_pro->id(user_profiles::ADMIN)) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * @returns bool true if the user has developer rights
     */
    function is_developer(): bool
    {
        global $ui_sys;
        log_debug();
        $result = false;

        if ($this->is_profile_valid()) {
            if ($this->profile_id == $ui_sys->typ_lst_cache->usr_pro->id(user_profiles::DEV)) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * @returns bool true if the user is a system user e.g. the reserved word names can be used
     */
    function is_system(): bool
    {
        global $ui_sys;
        log_debug();
        $result = false;

        if ($this->is_profile_valid()) {
            if ($this->profile_id == $ui_sys->typ_lst_cache->usr_pro->id(user_profiles::TEST)
                or $this->profile_id == $ui_sys->typ_lst_cache->usr_pro->id(user_profiles::SYSTEM)) {
                $result = true;
            }
        }
        // the two normal test users carry the test profile only to be allowed to write the
        // reserved test names (a backend privilege); for the frontend display they act like a
        // normal user, so most test pages render without the admin-only fields and only the
        // few explicit admin, developer and system user tests show them (see sees_admin_fields)
        if ($this->code_id == users::SYSTEM_TEST_CODE_ID
            or $this->code_id == users::SYSTEM_TEST_PARTNER_CODE_ID) {
            $result = false;
        }
        return $result;
    }

    /**
     * @returns bool true if the user has the reserved test profile, which keeps the privileges
     *               of a system user e.g. to use the admin masks (frontend::admin_mask_denied)
     *               even though the pages are displayed to it like for a normal user (see is_system)
     */
    function is_system_test(): bool
    {
        global $ui_sys;
        $result = false;

        if ($this->is_profile_valid()) {
            if ($this->profile_id == $ui_sys->typ_lst_cache->usr_pro->id(user_profiles::TEST)) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * @returns bool true if the user is allowed to see the admin-only fields (the cached usage and
     *               impact numbers, see fields::LOG_ADMIN_ONLY) in the change log and the confirm
     *               change preview, because the cached numbers are system internals that would
     *               only confuse a normal user
     */
    function sees_admin_fields(): bool
    {
        return $this->is_admin() or $this->is_developer() or $this->is_system();
    }

    /**
     * @returns bool true if the user is uniquely identified beyond an ip or a chosen name
     *               (mirrors the backend user::is_unique used by can_set_type_id)
     */
    function is_unique(): bool
    {
        global $ui_sys;
        $result = false;
        if ($this->is_profile_valid()) {
            foreach (user_profiles::CAN_CHANGE as $prf) {
                if ($this->profile_id == $ui_sys->typ_lst_cache->usr_pro->id($prf)) {
                    $result = true;
                }
            }
        }
        return $result;
    }

    /**
     * @returns bool true if the user may change the type of an object e.g. the phrase type of a word;
     *               an ip-only or name-only user is not permitted (mirrors backend user::can_set_type_id)
     */
    function can_set_type_id(): bool
    {
        return $this->is_unique();
    }

    /**
     * @returns bool true if the user may change the code id of an object e.g. of a source;
     *               only the system, test and developer users are permitted, because a code id
     *               links a database row to program code (mirrors backend user::can_set_code_id)
     */
    function can_set_code_id(): bool
    {
        $result = false;
        if ($this->is_system() or $this->is_system_test() or $this->is_developer()) {
            $result = true;
        }
        return $result;
    }

    /**
     * @returns bool true if the code id of an object should be shown to the user; a code id
     *               links a database row to program code, so it is only relevant for an admin
     *               or a developer and stays hidden for every other profile, even one that
     *               could technically set it (docs/llm/state-and-messages.md)
     */
    function can_see_code_id(): bool
    {
        $result = false;
        if ($this->is_admin() or $this->is_developer()) {
            $result = true;
        }
        return $result;
    }

    /**
     * @return string|null the human-readable profile name e.g. "admin" or null if profile is not set
     */
    function profile_name(): ?string
    {
        global $ui_sys;
        if ($this->profile_id > 0) {
            return $ui_sys->typ_lst_cache->usr_pro->name($this->profile_id);
        } else {
            return null;
        }
    }

    /**
     * returns the role label for the navbar tooltip and dropdown header, or null for regular users
     * regular profiles (NAME_ONLY, EMAIL, HUMAN) show no role; elevated profiles (ADMIN, DEV, SYS_LINK, TEST, LOG, SYSTEM) do
     *
     * @return string|null the profile name to display next to the username, or null for regular users
     */
    function navbar_role(): ?string
    {
        global $ui_sys;
        $elevated = [
            user_profiles::SYS_LINK,
            user_profiles::ADMIN,
            user_profiles::DEV,
            user_profiles::TEST,
            user_profiles::LOG,
            user_profiles::SYSTEM,
        ];
        foreach ($elevated as $prf) {
            if ($this->profile_id == $ui_sys->typ_lst_cache->usr_pro->id($prf)) {
                return $this->profile_name();
            }
        }
        return null;
    }

    /**
     * @return bool false if the profile is not set or is not found
     */
    private function is_profile_valid(): bool
    {
        if ($this->profile_id > 0) {
            return true;
        } else {
            return false;
        }
    }


    /*
     * interface
     */

    /**
     * @return array the json message array to send the updated data to the backend
     * an array is used (instead of a string) to enable combinations of api_array($msg) calls
     */
    function api_array(api_type_list|array $typ_lst, user_message $msg): array
    {
        $vars = parent::api_array($typ_lst, $msg);
        $vars[json_fields::NAME] = $this->name;
        $vars[json_fields::IP_ADDR] = $this->ip_addr;
        $vars[json_fields::EMAIL] = $this->email;

        $vars[json_fields::ACTIVATION_KEY] = $this->activation_key;
        $vars[json_fields::ACTIVATION_TIMEOUT] = $this->activation_timeout?->format(DateTimeInterface::ATOM);
        $vars[json_fields::DB_NOW] = $this->db_now?->format(DateTimeInterface::ATOM);
        $vars[json_fields::LAST_LOGIN] = $this->last_login?->format(DateTimeInterface::ATOM);
        $vars[json_fields::LAST_LOGOFF] = $this->last_logoff?->format(DateTimeInterface::ATOM);

        if ($this->is_profile_valid()) {
            $vars[json_fields::PROFILE_ID] = $this->profile_id;
        }
        $vars[json_fields::CODE_ID] = $this->code_id;
        if ($this->type_id > 0) {
            $vars[json_fields::TYPE] = $this->type_id;
        }
        $vars[json_fields::RIGHT_LEVEL] = $this->right_level;
        if ($this->status_id > 0) {
            $vars[json_fields::STATUS] = $this->status_id;
        }
        $vars[json_fields::EXCLUDED] = $this->excluded;
        $vars[json_fields::USES_SANDBOX] = $this->uses_sandbox;

        $vars[json_fields::CREATED] = $this->created?->format(DateTimeInterface::ATOM);
        $vars[json_fields::DESCRIPTION] = $this->description;
        $vars[json_fields::FIRST_NAME] = $this->first_name;
        $vars[json_fields::LAST_NAME] = $this->last_name;

        $vars[json_fields::TERM_ID] = $this->trm?->id();
        $vars[json_fields::VIEW_ID] = $this->msk?->id();
        $vars[json_fields::SOURCE_ID] = $this->src?->id();

        // TODO Prio 1 use vars filter for all api array creation functions
        // TODO Prio 1 check if password should be included and in which form
        return array_filter($vars, fn($value) => !is_null($value) && $value !== '');
    }


    /*
     * display
     */

    function name_link(
        array  $url_arr = [],
        string $style = '',
        int $msk_id = views::USER_ID,
        string $base_url = ''
    ): string
    {
        $html = new html_base();
        $url = $html->url_back($msk_id, $this->id(), $url_arr, base_url: $base_url);
        return $html->ref($url, $this->name(), $this->get_description(), $style);
    }

    /**
     * build the login form HTML
     *
     * @param string $extra_hidden additional hidden fields to inject before the submit button e.g. the mask id and 9-prefixed back params
     * @param string $back_url when non-empty an "or go back" link is appended after "or signup"
     * @return string the complete login form HTML followed by an "or signup" link
     */
    function form_login(string $extra_hidden = '', string $back_url = ''): string
    {
        global $mtr;

        $html = new html_base();
        $form_str = $mtr->txt(msg_id::FORM_NAME_USER_NAME_OR_EMAIL) . $html->br();
        $form_str .= $html->form_input(html_base::INPUT_TEXT, url_var::USERNAME_HUMAN) . $html->br2();
        $form_str .= $mtr->txt(msg_id::FORM_NAME_PASSWORD) . $html->br();
        // optional with show password toggle but without auto fill
        //$form_str .= $html->form_input_password(url_var::USER_PASSWORD_HUMAN, $mtr->txt(msg_id::FORM_SHOW_PASSWORD), html_base::AUTOCOMPLETE_CURRENT_PW) . $html->br2();
        $form_str .= $html->form_input(html_base::INPUT_PASSWORD, url_var::USER_PASSWORD_HUMAN) . $html->br2();
        $form_str .= $html->form_session_token();
        $form_str .= $extra_hidden;
        $form_str .= $html->form_submit($mtr->txt(msg_id::FORM_NAME_LOGIN)) . $html->br2();
        $or_signup = $mtr->txt(msg_id::OR) . ' ' . $html->ref(api::SIGNUP_SCRIPT, $mtr->txt(msg_id::SIGNUP));
        $or_back = '';
        if ($back_url !== '') {
            $or_back = ' ' . $mtr->txt(msg_id::OR) . ' ' . $mtr->txt(msg_id::GO) . ' ' . $html->ref($back_url, $mtr->txt(msg_id::BACK_LINK));
        }
        return $html->form_simple(api::MAIN_SCRIPT, html_base::METHOD_POST, $form_str) . $or_signup . $or_back;
    }

    /**
     * build the signup form HTML
     *
     * @param string $extra_hidden additional hidden fields to inject e.g. the mask id and 9-prefixed back params
     * @param string $usr_name pre-filled username shown when re-displaying after a validation error
     * @param string $email pre-filled email shown when re-displaying after a validation error
     * @return string the complete signup form HTML
     */
    function form_signup(string $extra_hidden = '', string $usr_name = '', string $email = ''): string
    {
        global $mtr;

        $html = new html_base();
        $form_usr = $mtr->txt(msg_id::FORM_NAME_USER_NAME) . $html->br();
        $form_usr .= $html->form_input(html_base::INPUT_TEXT, url_var::USERNAME, $usr_name);
        $form_str = $html->p($form_usr);
        $form_mail = $mtr->txt(msg_id::FORM_NAME_USER_EMAIL) . $html->br();
        $form_mail .= $html->form_input(html_base::INPUT_TEXT, url_var::EMAIL, $email);
        $form_str .= $html->p($form_mail);
        $form_pw = $mtr->txt(msg_id::FORM_NAME_PASSWORD) . $html->br();
        // optional
        //$form_pw .= $html->form_input_password(url_var::USER_PASSWORD, $mtr->txt(msg_id::FORM_SHOW_PASSWORD), html_base::AUTOCOMPLETE_NEW_PW);
        $form_pw .= $html->form_input(html_base::INPUT_PASSWORD, url_var::USER_PASSWORD);
        $form_str .= $html->p($form_pw);
        $form_pwr = $mtr->txt(msg_id::FORM_NAME_PASSWORD_RE) . $html->br();
        // optional
        //$form_pwr .= $html->form_input_password(url_var::USER_PASSWORD_RETYPE, $mtr->txt(msg_id::FORM_SHOW_PASSWORD), html_base::AUTOCOMPLETE_NEW_PW);
        $form_pwr .= $html->form_input(html_base::INPUT_PASSWORD, url_var::USER_PASSWORD_RETYPE);
        $form_str .= $html->p($form_pwr);
        $form_str .= $html->form_session_token();
        $form_str .= $extra_hidden;
        $form_str .= $html->button_submit($mtr->txt(msg_id::SIGN_UP));
        return $html->form_simple(api::MAIN_SCRIPT, html_base::METHOD_POST, $form_str);
    }

    /**
     * build the activate (password change) form HTML
     *
     * @param string $extra_hidden hidden fields for MASK and back params
     * @param int $usr_id the user id from the activation link
     * @param string $key the activation key from the activation link; if empty an input field is shown
     * @return string the complete form HTML or an error message if usr_id is missing
     */
    function form_activate(string $extra_hidden = '', int $usr_id = 0, string $key = ''): string
    {
        global $mtr;

        $html = new html_base();
        if ($usr_id <= 0) {
            return $html->dsp_err($mtr->txt(msg_id::ACTIVATE_ERR_MISSING_ID));
        }
        $form_str = $html->form_hidden(url_var::ID, (string)$usr_id);
        if ($key !== '') {
            $form_str .= $html->form_hidden(url_var::POST_KEY, $key);
        } else {
            $form_key = $mtr->txt(msg_id::ACTIVATE_KEY_LABEL) . $html->br();
            $form_key .= $html->form_input(html_base::INPUT_TEXT, url_var::POST_KEY);
            $form_str .= $html->p($form_key);
        }
        $form_pw = $mtr->txt(msg_id::FORM_NAME_PASSWORD) . $html->br();
        // optional
        // $form_pw .= $html->form_input_password(url_var::USER_PASSWORD, $mtr->txt(msg_id::FORM_SHOW_PASSWORD), html_base::AUTOCOMPLETE_NEW_PW);
        $form_pw .= $html->form_input(html_base::INPUT_PASSWORD, url_var::USER_PASSWORD);
        $form_str .= $html->p($form_pw);
        $form_pwr = $mtr->txt(msg_id::FORM_NAME_PASSWORD_RE) . $html->br();
        // optional
        // $form_pwr .= $html->form_input_password(url_var::USER_PASSWORD_RETYPE, $mtr->txt(msg_id::FORM_SHOW_PASSWORD), html_base::AUTOCOMPLETE_NEW_PW);
        $form_pwr .= $html->form_input(html_base::INPUT_PASSWORD, url_var::USER_PASSWORD_RETYPE);
        $form_str .= $html->p($form_pwr);
        $form_str .= $html->form_session_token();
        $form_str .= $extra_hidden;
        $form_str .= $html->button_submit($mtr->txt(msg_id::ACTIVATE_SUBMIT));
        return $html->form_simple(api::MAIN_SCRIPT, html_base::METHOD_POST, $form_str);
    }


    /**
     * build the password reset request form HTML
     *
     * @param string $extra_hidden additional hidden fields to inject e.g. the mask id and back params
     * @param string $back_url URL to navigate to when the user cancels; falls back to the main page if empty
     * @return string the complete reset form HTML followed by an "or cancel and go back" link
     */
    function form_reset(string $extra_hidden = '', string $back_url = ''): string
    {
        global $mtr;

        $html = new html_base();
        $form_usr = $mtr->txt(msg_id::FORM_NAME_USER_NAME) . $html->br();
        $form_usr .= $html->form_input(html_base::INPUT_TEXT, url_var::USERNAME_HUMAN);
        $form_str = $html->p($form_usr);
        $form_mail = $mtr->txt(msg_id::FORM_NAME_USER_EMAIL) . $html->br();
        $form_mail .= $html->form_input(html_base::INPUT_EMAIL, url_var::EMAIL_HUMAN);
        $form_str .= $html->p($form_mail);
        $form_str .= $html->form_session_token();
        $form_str .= $extra_hidden;
        $form_str .= $html->button_submit($mtr->txt(msg_id::RESET_SUBMIT));
        $cancel_url = $back_url !== '' ? $back_url : api::MAIN_SCRIPT;
        $or_cancel = ' ' . $mtr->txt(msg_id::OR) . ' ' . $mtr->txt(msg_id::CANCEL_AND_GO) . ' ' . $html->ref($cancel_url, $mtr->txt(msg_id::BACK_LINK));
        return $html->form_simple(api::MAIN_SCRIPT, html_base::METHOD_POST, $form_str) . $or_cancel;
    }


    /*
     * to review
     */

    /**
     * display a form with the user parameters such as name or email
     * @param array $url_arr the url vars of the calling page for the back link
     */
    function form_edit(array $url_arr = []): string
    {
        $html = new html_base();
        $result = ''; // reset the html code var

        if ($this->id > 0) {
            // display the user fields using a table and not using px in css to be independent of any screen solution
            // the same title as the user default view builds with the system_title_with_object_name component
            $header = $html->text_h2(msg_id::SYSTEM_TITLE_USER->text() . ' "' . $html->esc($this->name) . '"');
            $hidden_fields = $html->form_hidden("id", $this->id);
            // the calling page travels with the form as the '9'-prefixed hidden fields
            foreach (html_base::back_url_array($url_arr) as $key => $val) {
                $hidden_fields .= $html->form_hidden($key, (string)$val);
            }
            $detail_fields = $html->form_text(url_var::USER, $this->name, msg_id::FORM_FIELD_USERNAME);
            $detail_fields .= $html->form_text(url_var::EMAIL, $this->email, msg_id::FORM_FIELD_USER_EMAIL);
            $detail_fields .= $html->form_text(url_var::USER_FIRST_NAME, $this->first_name, msg_id::FORM_FIELD_USER_FIRST_NAME);
            $detail_fields .= $html->form_text(url_var::USER_LAST_NAME, $this->last_name, msg_id::FORM_FIELD_USER_LAST_NAME);
            $detail_row = $html->fr($detail_fields) . '<br>';
            $result = $header
                . $html->form(views::USER_EDIT, $hidden_fields . $detail_row)
                . '<br>';
        }

        return $result;
    }

    /**
     * display the latest changes of the user
     * TODO add display the latest changes by a user
     * @param array $url_arr the url vars of the calling page for the back link of the undo buttons
     */
    function dsp_changes(int $size, int $page, user_message $msg, array $url_arr = []): string
    {
        $log_ui = new user_log_display();
        return $log_ui->dsp_hist(user::class, $this->id(), $size, $page, $msg, '', $url_arr);
    }

    /**
     * display the error that are related to the user, so that he can track when they are closed
     * @param array $url_arr the url vars of the calling page for the back link of the close links
     */
    function dsp_errors(string $dsp_type, user_message $msg, int $size, int $page, array $url_arr = []): string
    {
        log_debug($dsp_type . ' errors for user ' . $this->name);

        $result = '';
        $err_lst = new sys_log_list;
        //$err_lst->set_user($this);
        //$err_lst->page = $page;
        //$err_lst->size = $size;
        //$err_lst->dsp_type = $dsp_type;
        if ($err_lst->load($msg)) {
            $err_lst_ui = new sys_log_list();
            $err_lst_ui->set_from_json($err_lst->api_json([], $msg), $msg);
            // the requesting user is not (yet) forwarded, so no close link is shown, but the
            // calling page is, so that a close link can return to it
            $result = $err_lst_ui->get_html($msg, null, $url_arr);
        }

        log_debug('done');
        return $result;
    }

    // create the HTML code to display the username with the HTML link
    function display(): string
    {
        $html = new html_base();
        return $html->ref_view(views::USER_ID, $this->id, $this->name);
    }


}
