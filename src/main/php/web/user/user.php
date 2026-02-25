<?php

/*

    web/user/user.php - functions to create the HTML code to display the user setup and log information
    -----------------

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

use DateTime;
use DateTimeInterface;
use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

// get the api const that are shared between the backend and the html frontend
// get the pure html frontend objects
include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::REF . 'source.php';
include_once html_paths::SANDBOX . 'db_object.php';
//include_once html_paths::PHRASE . 'term.php';
include_once html_paths::VIEW . 'view.php';
include_once paths::SHARED_ENUM . 'user_profiles.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'library.php';
include_once paths::SHARED . 'url_var.php';

use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\phrase\term;
use Zukunft\ZukunftCom\main\php\web\ref\source;
use Zukunft\ZukunftCom\main\php\web\sandbox\db_object;
use Zukunft\ZukunftCom\main\php\web\view\view;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\enum\user_profiles;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\url_var;

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

    // additional info
    public ?DateTime $created = null;
    public ?string $description;
    public ?string $first_name;
    public ?string $last_name;

    // speed up cache
    public ?term $trm = null;       // the last term that the user has been looking at
    public ?view $msk = null;             // the last view that the user has been looking at
    public ?source $src = null;           // the last source that the user has been looking at

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
     * @param user_message $usr_msg to enrich with warnings, problems and solutions
     * @param data_object|null $dto the cache as a parameter to be able to simulate test conditions
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function url_mapper(array $url_array, user_message $usr_msg, data_object|null $dto = null): user_message
    {
        parent::url_mapper($url_array, $usr_msg, $dto);
        if ($usr_msg->is_ok()) {
            if (array_key_exists(url_var::USERNAME, $url_array)) {
                if ($url_array[url_var::USERNAME] != null) {
                    $this->name = $url_array[url_var::USERNAME];
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
        }
        return $usr_msg;
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
     * set and get
     */

    function name(): string
    {
        return $this->name;
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
    function password(): string
    {
        return $this->password;
    }


    /*
     * info
     */

    /**
     * @returns bool true if the user has admin rights
     */
    function is_admin(): bool
    {
        global $sys;
        log_debug();
        $result = false;

        if ($this->is_profile_valid()) {
            if ($this->profile_id == $sys->typ_lst->usr_pro->id(user_profiles::ADMIN)) {
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
        global $sys;
        log_debug();
        $result = false;

        if ($this->is_profile_valid()) {
            if ($this->profile_id == $sys->typ_lst->usr_pro->id(user_profiles::TEST)
                or $this->profile_id == $sys->typ_lst->usr_pro->id(user_profiles::SYSTEM)) {
                $result = true;
            }
        }
        return $result;
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
     * an array is used (instead of a string) to enable combinations of api_array() calls
     */
    function api_array(): array
    {
        $vars = parent::api_array();
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

    function name_link(?string $back = '', string $style = '', int $msk_id = views::USER_ID): string
    {
        $html = new html_base();
        $url = $html->url_new($msk_id, $this->id(), '', $back);
        return $html->ref($url, $this->name(), $this->get_description(), $style);
    }

    /*
     * to review
     */

    /**
     * display a form with the user parameters such as name or email
     */
    function form_edit($back): string
    {
        $html = new html_base();
        $result = ''; // reset the html code var

        if ($this->id > 0) {
            // display the user fields using a table and not using px in css to be independent of any screen solution
            $header = $html->text_h2('User "' . $this->name . '"');
            $hidden_fields = $html->form_hidden("id", $this->id);
            $hidden_fields .= $html->form_hidden("back", $back);
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

}
