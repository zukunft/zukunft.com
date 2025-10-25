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
include_once paths::MODEL_COMPONENT . 'component_link_type_list.php';
include_once paths::MODEL_COMPONENT . 'component_type_list.php';
include_once paths::MODEL_COMPONENT . 'position_type_list.php';
include_once paths::MODEL_COMPONENT . 'view_style_list.php';
include_once paths::MODEL_CONST . 'def.php';
include_once paths::MODEL_CONST . 'files.php';
include_once paths::DB . 'sql_db.php';
include_once paths::MODEL_ELEMENT . 'element_type_list.php';
include_once paths::MODEL_FORMULA . 'formula_type_list.php';
include_once paths::MODEL_FORMULA . 'formula_link_type_list.php';
include_once paths::MODEL_HELPER . 'type_list.php';
include_once paths::MODEL_HELPER . 'type_object.php';
include_once paths::MODEL_LANGUAGE . 'language_form.php';
include_once paths::MODEL_LANGUAGE . 'language_form_list.php';
include_once paths::MODEL_LANGUAGE . 'language_list.php';
include_once paths::MODEL_LOG . 'change_action_list.php';
include_once paths::MODEL_LOG . 'change_field.php';
include_once paths::MODEL_LOG . 'change_field_list.php';
include_once paths::MODEL_LOG . 'change_table.php';
include_once paths::MODEL_LOG . 'change_table_list.php';
include_once paths::MODEL_PHRASE . 'phrase_types.php';
include_once paths::MODEL_REF . 'source_type_list.php';
include_once paths::MODEL_REF . 'ref_type_list.php';
include_once paths::MODEL_SANDBOX . 'protection_type_list.php';
include_once paths::MODEL_SANDBOX . 'share_type_list.php';
include_once paths::MODEL_SYSTEM . 'job_type_list.php';
include_once paths::MODEL_SYSTEM . 'sys_log_status_list.php';
include_once paths::MODEL_VIEW . 'view_type_list.php';
include_once paths::MODEL_VIEW . 'view_link_type_list.php';
include_once paths::MODEL_VERB . 'verb_list.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_profile_list.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'library.php';
include_once test_paths::UTILS . 'test_cleanup.php';

use Zukunft\ZukunftCom\main\php\api\api_message;
use Zukunft\ZukunftCom\main\php\cfg\component\component_link_type_list;
use Zukunft\ZukunftCom\main\php\cfg\component\component_type_list;
use Zukunft\ZukunftCom\main\php\cfg\component\position_type_list;
use Zukunft\ZukunftCom\main\php\cfg\component\view_style_list;
use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\main\php\cfg\const\files;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\element\element_type_list;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_type_list;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_link_type_list;
use Zukunft\ZukunftCom\main\php\cfg\helper\type_list;
use Zukunft\ZukunftCom\main\php\cfg\helper\type_object;
use Zukunft\ZukunftCom\main\php\cfg\language\language_form;
use Zukunft\ZukunftCom\main\php\cfg\language\language_form_list;
use Zukunft\ZukunftCom\main\php\cfg\language\language_list;
use Zukunft\ZukunftCom\main\php\cfg\log\change_action_list;
use Zukunft\ZukunftCom\main\php\cfg\log\change_field;
use Zukunft\ZukunftCom\main\php\cfg\log\change_field_list;
use Zukunft\ZukunftCom\main\php\cfg\log\change_table;
use Zukunft\ZukunftCom\main\php\cfg\log\change_table_list;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_types;
use Zukunft\ZukunftCom\main\php\cfg\ref\source_type_list;
use Zukunft\ZukunftCom\main\php\cfg\ref\ref_type_list;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\protection_type_list;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\share_type_list;
use Zukunft\ZukunftCom\main\php\cfg\system\job_type_list;
use Zukunft\ZukunftCom\main\php\cfg\system\sys_log_status_list;
use Zukunft\ZukunftCom\main\php\cfg\view\view_type_list;
use Zukunft\ZukunftCom\main\php\cfg\view\view_link_type_list;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_profile_list;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
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
     * dummy type objects for unit tests
     */

    /**
     * create the api json message for all types
     * @param user $usr the user who wants to see his types
     * @return string api json string with the types of the given user
     */
    function type_lists_api(user $usr): string
    {
        $usr_pro_cac = new user_profile_list();
        $phr_typ_cac = new phrase_types();
        $frm_typ_cac = new formula_type_list();
        $frm_lnk_typ_cac = new formula_link_type_list();
        $elm_typ_cac = new element_type_list();
        $msk_typ_cac = new view_type_list();
        $msk_sty_cac = new view_style_list();
        $msk_lnk_typ_cac = new view_link_type_list();
        $cmp_typ_cac = new component_type_list();
        $cmp_lnk_typ_cac = new component_link_type_list();
        $pos_typ_cac = new position_type_list();
        $ref_typ_cac = new ref_type_list();
        $src_typ_cac = new source_type_list();
        $shr_typ_cac = new share_type_list();
        $ptc_typ_cac = new protection_type_list();
        $lan_cac = new language_list();
        $lan_for_cac = new language_form_list();
        $sys_log_sta_cac = new sys_log_status_list();
        $job_typ_cac = new job_type_list();
        $cng_act_cac = new change_action_list();
        $cng_tbl_cac = new change_table_list();
        $cng_fld_cac = new change_field_list();
        $vrb_cac = new verb_list();

        $usr_pro_cac->load_dummy();
        $phr_typ_cac->load_dummy();
        $frm_typ_cac->load_dummy();
        $frm_lnk_typ_cac->load_dummy();
        $elm_typ_cac->load_dummy();
        $msk_typ_cac->load_dummy();
        $msk_sty_cac->load_dummy();
        $msk_lnk_typ_cac->load_dummy();
        $cmp_typ_cac->load_dummy();
        $cmp_lnk_typ_cac->load_dummy();
        $pos_typ_cac->load_dummy();
        $ref_typ_cac->load_dummy();
        $src_typ_cac->load_dummy();
        $shr_typ_cac->load_dummy();
        $ptc_typ_cac->load_dummy();
        $lan_cac->load_dummy();
        $lan_for_cac->load_dummy();
        $sys_log_sta_cac->load_dummy();
        $job_typ_cac->load_dummy();
        $cng_act_cac->load_dummy();
        $cng_tbl_cac->load_dummy();
        $cng_fld_cac->load_dummy();
        $vrb_cac->load_dummy();

        // read the corresponding names and description from the internal config csv files
        $vars = [];
        if ($this->read_all_names_from_config_csv($phr_typ_cac)) {
            $vars[json_fields::LIST_USER_PROFILES] = $usr_pro_cac->api_json_array();
            $vars[json_fields::LIST_PHRASE_TYPES] = $phr_typ_cac->api_json_array();
            $vars[json_fields::LIST_FORMULA_TYPES] = $frm_typ_cac->api_json_array();
            $vars[json_fields::LIST_FORMULA_LINK_TYPES] = $frm_lnk_typ_cac->api_json_array();
            $vars[json_fields::LIST_ELEMENT_TYPES] = $elm_typ_cac->api_json_array();
            $vars[json_fields::LIST_VIEW_TYPES] = $msk_typ_cac->api_json_array();
            $vars[json_fields::LIST_VIEW_STYLES] = $msk_sty_cac->api_json_array();
            $vars[json_fields::LIST_VIEW_LINK_TYPES] = $msk_lnk_typ_cac->api_json_array();
            $vars[json_fields::LIST_COMPONENT_TYPES] = $cmp_typ_cac->api_json_array();
            $vars[json_fields::LIST_COMPONENT_LINK_TYPES] = $cmp_lnk_typ_cac->api_json_array();
            $vars[json_fields::LIST_COMPONENT_POSITION_TYPES] = $pos_typ_cac->api_json_array();
            $vars[json_fields::LIST_REF_TYPES] = $ref_typ_cac->api_json_array();
            $vars[json_fields::LIST_SOURCE_TYPES] = $src_typ_cac->api_json_array();
            $vars[json_fields::LIST_SHARE_TYPES] = $shr_typ_cac->api_json_array();
            $vars[json_fields::LIST_PROTECTION_TYPES] = $ptc_typ_cac->api_json_array();
            $vars[json_fields::LIST_LANGUAGES] = $lan_cac->api_json_array();
            $vars[json_fields::LIST_LANGUAGE_FORMS] = $lan_for_cac->api_json_array();
            $vars[json_fields::LIST_SYS_LOG_STATUUS] = $sys_log_sta_cac->api_json_array();
            $vars[json_fields::LIST_JOB_TYPES] = $job_typ_cac->api_json_array();
            $vars[json_fields::LIST_CHANGE_LOG_ACTIONS] = $cng_act_cac->api_json_array();
            $vars[json_fields::LIST_CHANGE_LOG_TABLES] = $cng_tbl_cac->api_json_array();
            $vars[json_fields::LIST_CHANGE_LOG_FIELDS] = $cng_fld_cac->api_json_array();
            $vars[json_fields::LIST_VERBS] = $vrb_cac->api_json_array();
            $t_msk = new test_views($this->env);
            $sys_msk_cac = $t_msk->view_list();
            $vars[json_fields::LIST_SYSTEM_VIEWS] = $sys_msk_cac->api_json_array(new api_type_list([]));
        }

        global $db_con;
        $api_msg = new api_message();
        return json_encode($api_msg->api_header_array($db_con, 'type_lists', $usr, $vars));
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