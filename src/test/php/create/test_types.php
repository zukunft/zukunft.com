<?php

/*

    test/create/test_types.php - create a set of type objects for unit testing
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

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\test\php\create;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::API_OBJECT . 'api_message.php';
include_once paths::DB . 'sql_db.php';
include_once paths::MODEL_CONST . 'def.php';
include_once paths::MODEL_CONST . 'files.php';
include_once paths::MODEL_HELPER . 'type_list.php';
include_once paths::MODEL_HELPER . 'type_lists.php';
include_once paths::MODEL_HELPER . 'type_object.php';
include_once paths::MODEL_LANGUAGE . 'language_form.php';
include_once paths::MODEL_LOG . 'change_field.php';
include_once paths::MODEL_LOG . 'change_table.php';
include_once paths::MODEL_PHRASE . 'phrase_type.php';
include_once paths::MODEL_SANDBOX . 'protection_type.php';
include_once paths::MODEL_SANDBOX . 'share_type.php';
include_once paths::MODEL_SYSTEM . 'sys_log_function.php';
include_once paths::MODEL_SYSTEM . 'sys_log_status.php';
include_once paths::MODEL_SYSTEM . 'sys_log_type.php';
include_once paths::MODEL_SYSTEM . 'job_type.php';
include_once paths::MODEL_USER . 'user_official_type.php';
include_once paths::MODEL_USER . 'user_profile.php';
include_once paths::MODEL_USER . 'user_type.php';
include_once paths::MODEL_VERB . 'verb_list.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::SHARED_ENUM . 'sys_log_functions.php';
include_once paths::SHARED_ENUM . 'sys_log_statuus.php';
include_once paths::SHARED_ENUM . 'sys_log_types.php';
include_once paths::SHARED_ENUM . 'user_official_types.php';
include_once paths::SHARED_ENUM . 'user_profiles.php';
include_once paths::SHARED_ENUM . 'user_types.php';
include_once paths::SHARED_TYPES . 'api_types.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';
include_once paths::SHARED_TYPES . 'job_types.php';
include_once paths::SHARED_TYPES . 'phrase_types.php';
include_once paths::SHARED_TYPES . 'protection_types.php';
include_once paths::SHARED_TYPES . 'share_types.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'library.php';
include_once test_paths::UTILS . 'test_cleanup.php';

use Zukunft\ZukunftCom\main\php\api\api_message;
use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\main\php\cfg\const\files;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\helper\type_list;
use Zukunft\ZukunftCom\main\php\cfg\helper\type_lists;
use Zukunft\ZukunftCom\main\php\cfg\helper\type_object;
use Zukunft\ZukunftCom\main\php\cfg\language\language_form;
use Zukunft\ZukunftCom\main\php\cfg\log\change_field;
use Zukunft\ZukunftCom\main\php\cfg\log\change_table;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_type;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\protection_type;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\share_type;
use Zukunft\ZukunftCom\main\php\cfg\system\sys_log_function;
use Zukunft\ZukunftCom\main\php\cfg\system\sys_log_status;
use Zukunft\ZukunftCom\main\php\cfg\system\sys_log_type;
use Zukunft\ZukunftCom\main\php\cfg\system\job_type;
use Zukunft\ZukunftCom\main\php\cfg\user\user_official_type;
use Zukunft\ZukunftCom\main\php\cfg\user\user_profile;
use Zukunft\ZukunftCom\main\php\cfg\user\user_type;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\shared\enum\sys_log_functions;
use Zukunft\ZukunftCom\main\php\shared\enum\sys_log_statuus;
use Zukunft\ZukunftCom\main\php\shared\enum\sys_log_types;
use Zukunft\ZukunftCom\main\php\shared\enum\user_official_types;
use Zukunft\ZukunftCom\main\php\shared\enum\user_profiles;
use Zukunft\ZukunftCom\main\php\shared\enum\user_types;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\types\job_types;
use Zukunft\ZukunftCom\main\php\shared\types\phrase_types as phrase_types;
use Zukunft\ZukunftCom\main\php\shared\types\protection_types;
use Zukunft\ZukunftCom\main\php\shared\types\share_types;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class test_types
{

    /*
     * init
     */

    // use the global test environment
    private test_cleanup $env;

    function __construct(test_cleanup $env) {
        $this->env = $env;
    }


    /*
     * unit
     */

    /**
     * @return sys_log_type "info" as the main system log type for unit testing
     */
    function sys_log_type(): sys_log_type
    {
        return new sys_log_type(
            sys_log_types::INFO,
            sys_log_types::INFO_NAME,
            sys_log_types::INFO_COM,
            sys_log_types::INFO_ID);
    }

    /**
     * @return sys_log_status "open" as the main system log status for unit testing
     */
    function sys_log_status(): sys_log_status
    {
        return new sys_log_status(
            sys_log_statuus::OPEN,
            sys_log_statuus::OPEN_NAME,
            sys_log_statuus::OPEN_COM,
            sys_log_statuus::OPEN_ID);
    }

    /**
     * @return sys_log_function "import_base_config" as the main system log function for unit testing
     */
    function sys_log_function(): sys_log_function
    {
        return new sys_log_function(
            sys_log_functions::IMPORT_BASE_CONFIG,
            sys_log_functions::IMPORT_BASE_CONFIG_NAME,
            sys_log_functions::IMPORT_BASE_CONFIG_COM,
            sys_log_functions::IMPORT_BASE_CONFIG_ID);
    }

    /**
     * @return job_type "open" as the main system log status for unit testing
     */
    function job_type(): job_type
    {
        return new job_type(
            job_types::VALUE_UPDATE,
            job_types::VALUE_UPDATE_NAME,
            job_types::VALUE_UPDATE_COM,
            job_types::VALUE_UPDATE_ID);
    }

    /**
     * @return user_type "open" as the main system log status for unit testing
     */
    function user_type(): user_type
    {
        return new user_type(
            user_types::VERIFIED,
            user_types::VERIFIED_NAME,
            user_types::VERIFIED_COM,
            user_types::VERIFIED_ID);
    }

    /**
     * @return user_profile "open" as the main system log status for unit testing
     */
    function user_profile(): user_profile
    {
        return new user_profile(
            user_profiles::NORMAL,
            user_profiles::NORMAL_NAME,
            user_profiles::NORMAL_COM,
            user_profiles::NORMAL_ID);
    }

    /**
     * @return user_official_type "open" as the main system log status for unit testing
     */
    function user_official_type(): user_official_type
    {
        return new user_official_type(
            user_official_types::PASSPORT_EU,
            user_official_types::PASSPORT_EU_NAME,
            user_official_types::PASSPORT_EU_COM,
            user_official_types::PASSPORT_EU_ID);
    }

    /**
     * @return protection_type "open" as the main system log status for unit testing
     */
    function protection_type(): protection_type
    {
        return new protection_type(
            protection_types::NO_PROTECT,
            protection_types::NO_PROTECT_NAME,
            protection_types::NO_PROTECT_COM,
            protection_types::NO_PROTECT_ID);
    }

    /**
     * @return share_type "public" as the default share type for unit testing
     */
    function share_type(): share_type
    {
        return new share_type(
            share_types::PUBLIC,
            share_types::PUBLIC_NAME,
            share_types::PUBLIC_COM,
            share_types::PUBLIC_ID);
    }

    /**
     * @return phrase_type "normal" as the main phrase type for unit testing
     */
    function phrase_type(): phrase_type
    {
        return new phrase_type(
            phrase_types::NORMAL,
            phrase_types::NORMAL_ID,
            phrase_types::NORMAL_NAME);
    }


    /*
     * dummy type objects for unit tests
     */

    /**
     * create the api json message for all types
     * @param user $usr the user who wants to see his types
     * @return string api json string with the types of the given user
     */
    function type_lists_api(user $usr): string
    {
        global $sys;

        $typ_lst = new type_lists();
        $typ_lst->load_dummy();

        // read the corresponding names and description from the internal config csv files
        $vars = [];
        if ($this->read_all_names_from_config_csv($typ_lst->phr_typ)) {
            $vars = $typ_lst->api_json_array();

            // add verbs
            $sys->typ_lst->vrb = new verb_list();
            $sys->typ_lst->vrb->load_dummy();
            $vars[json_fields::LIST_VERBS] = $sys->typ_lst->vrb->api_json_array();

            // add views
            $t_msk = new test_views($this->env);
            $sys_msk_cac = $t_msk->view_list();
            $vars[json_fields::LIST_SYSTEM_VIEWS] = $sys_msk_cac->api_json_array(new api_type_list([api_types::INCL_COMPONENTS]));
        }

        global $db_con;
        $api_msg = new api_message();
        $pod_name = $api_msg->api_site_name($db_con);
        return $api_msg->api_json($pod_name, 'type_lists', $vars, [api_types::HEADER], $usr);
    }

    /**
     * reads the name and description from the csv resource file and changes the corresponding type list entry
     * used to simplify the dummy list creation because this way only a list of code_ids is needed to create a list
     *
     * @param type_list $list the type list that should be filled
     * @return bool true if the list has been updated
     */
    private function read_all_names_from_config_csv(type_list $list): bool
    {
        $result = false;

        $lib = new library();
        $type = $lib->class_to_name($list::class);

        // load the csv
        $csv_path = $this->config_csv_get_file($list);
        if ($csv_path != '') {
            $row = 1;
            $code_id_col = 0;
            $name_col = 0;
            $desc_col = 0;
            if (($handle = fopen($csv_path, "r")) !== FALSE) {
                while (($data = fgetcsv($handle, 0, ",", "'")) !== FALSE) {
                    if ($row == 1) {
                        $col_names = $lib->array_trim($data);
                        if (in_array(json_fields::CODE_ID, $col_names)) {
                            $code_id_col = array_search(json_fields::CODE_ID, $col_names);
                        }
                        if (in_array(type_object::FLD_NAME, $col_names)) {
                            $name_col = array_search(type_object::FLD_NAME, $col_names);
                        }
                        if (in_array(json_fields::DESCRIPTION, $col_names)) {
                            $desc_col = array_search(json_fields::DESCRIPTION, $col_names);
                        }
                    } else {
                        $typ_obj = null;
                        $code_id = trim($data[$code_id_col]);
                        if ($code_id == 'NULL') {
                            $id = $data[0];
                            $typ_obj = $list->get($id);
                        } else {
                            if ($list->id($code_id) == null) {
                                log_warning($type . ' ' . $data[$name_col] . ' not jet included in the unit tests');
                            } else {
                                $typ_obj = $list->get_by_code_id($code_id);
                            }
                        }
                        if ($typ_obj != null) {
                            $typ_obj->set_name($data[$name_col]);
                            if ($desc_col > 0) {
                                $typ_obj->set_description($data[$desc_col]);
                            }
                        }
                    }
                    $row++;
                }
                fclose($handle);
                $result = true;
            }
        }
        return $result;
    }

    /**
     * fill the list base on the csv resource file
     *
     * @param type_list $list the type list that should be filled
     * @return type_list the filled type list
     */
    public function read_from_config_csv(type_list $list): type_list
    {
        $lib = new library();

        // load the csv
        $csv_path = $this->config_csv_get_file($list);
        if ($csv_path != '') {
            $row = 1;
            $code_id_col = 0;
            $id_col = 0;
            $name_col = 0;
            $desc_col = 0;
            // change log field specific
            $table_col = 0;
            if (($handle = fopen($csv_path, "r")) !== FALSE) {
                while (($data = fgetcsv($handle, 0, ",", "'")) !== FALSE) {
                    if ($row == 1) {
                        $col_names = $lib->array_trim($data);
                        if (in_array(json_fields::ID, $col_names)) {
                            $id_col = array_search(json_fields::ID, $col_names);
                        } elseif (in_array(change_table::FLD_ID, $col_names)) {
                            $id_col = array_search(change_table::FLD_ID, $col_names);
                        } elseif (in_array(change_field::FLD_ID, $col_names)) {
                            $id_col = array_search(change_field::FLD_ID, $col_names);
                        }
                        if (in_array(json_fields::CODE_ID, $col_names)) {
                            $code_id_col = array_search(json_fields::CODE_ID, $col_names);
                        }
                        if (in_array(type_object::FLD_NAME, $col_names)) {
                            $name_col = array_search(type_object::FLD_NAME, $col_names);
                        } elseif (in_array(change_table::FLD_NAME, $col_names)) {
                            $name_col = array_search(change_table::FLD_NAME, $col_names);
                        } elseif (in_array(change_field::FLD_NAME, $col_names)) {
                            $name_col = array_search(change_field::FLD_NAME, $col_names);
                        } elseif (in_array(language_form::FLD_NAME, $col_names)) {
                            $name_col = array_search(language_form::FLD_NAME, $col_names);
                        }

                        if (in_array(change_field::FLD_TABLE, $col_names)) {
                            $table_col = array_search(change_field::FLD_TABLE, $col_names);
                        }
                        if (in_array(json_fields::DESCRIPTION, $col_names)) {
                            $desc_col = array_search(json_fields::DESCRIPTION, $col_names);
                        }
                    } else {
                        if ($table_col > 0) {
                            $typ_obj = new type_object($data[$table_col] . $data[$name_col]);
                        } else {
                            $typ_obj = new type_object($data[$name_col]);
                        }
                        $typ_obj->id = $data[$id_col];
                        $typ_obj->set_name($data[$name_col]);
                        if ($code_id_col > 0) {
                            $typ_obj->set_code_id_db($data[$code_id_col]);
                        }
                        if (array_key_exists($desc_col, $data)) {
                            $typ_obj->set_description($data[$desc_col]);
                        } else {
                            log_err($desc_col . ' is missing in ' . $lib->dsp_array($data));
                        }
                        $list->add($typ_obj);
                    }
                    $row++;
                }
                fclose($handle);
            }
        }
        return $list;
    }

    private function config_csv_get_file(type_list $list): string
    {
        $csv_path = '';
        $lib = new library();
        $type = $lib->class_to_name($list::class);
        foreach (def::BASE_CODE_LINK_FILES as $csv_class) {
            $csv_file_name = $lib->class_to_name($csv_class);
            if (str_ends_with($type, '_list')) {
                $csv_list_type = $csv_file_name . '_list';
            } else {
                $csv_list_type = $csv_file_name;
            }
            $csv_file_name .= sql_db::TABLE_EXTENSION;
            if ($csv_list_type == $type) {
                $csv_path = files::CODE_LINK_PATH . $csv_file_name . files::CODE_LINK_TYPE;
            }
        }
        return $csv_path;
    }

}