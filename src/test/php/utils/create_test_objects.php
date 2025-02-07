<?php

/*

    test/utils/create_test_objects.php - create the standard object for testing
    ----------------------------------

    TODO create all test object from here
    TODO shorten the names e.g. if the phrase is most often used use the functin name canton() for the phrase

    object adding, loading and testing functions

    create_* to create an object mainly used to shorten the code in unit tests
    add_* to create an object and save it in the database to prepare the testing (not used for all classes)
    load_* just load the object, but does not create the object
    test_* additional creates the object if needed and checks if it has been persistent

    * is for the name of the class, so the long name e.g. word not wrd


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

namespace test;

include_once MODEL_HELPER_PATH . 'type_object.php';
include_once SHARED_TYPES_PATH . 'component_type.php';
include_once MODEL_PHRASE_PATH . 'phrase.php';
include_once MODEL_PHRASE_PATH . 'term.php';
include_once MODEL_COMPONENT_PATH . 'component.php';
include_once MODEL_COMPONENT_PATH . 'component_list.php';
include_once MODEL_RESULT_PATH . 'results.php';
include_once MODEL_VALUE_PATH . 'value.php';
include_once MODEL_VALUE_PATH . 'value_time.php';
include_once MODEL_VALUE_PATH . 'value_text.php';
include_once MODEL_VALUE_PATH . 'value_geo.php';
include_once MODEL_VALUE_PATH . 'value_ts_data.php';
include_once WEB_FORMULA_PATH . 'formula.php';
include_once SHARED_ENUM_PATH . 'change_actions.php';
include_once SHARED_ENUM_PATH . 'change_tables.php';
include_once SHARED_ENUM_PATH . 'change_fields.php';
include_once SHARED_ENUM_PATH . 'sys_log_statuus.php';
include_once SHARED_TYPES_PATH . 'phrase_type.php';
include_once SHARED_TYPES_PATH . 'position_types.php';
include_once SHARED_TYPES_PATH . 'verbs.php';
include_once SHARED_TYPES_PATH . 'view_styles.php';
include_once SHARED_CONST_PATH . 'components.php';
include_once SHARED_CONST_PATH . 'formulas.php';
include_once SHARED_CONST_PATH . 'groups.php';
include_once SHARED_CONST_PATH . 'triples.php';
include_once SHARED_CONST_PATH . 'values.php';
include_once SHARED_CONST_PATH . 'words.php';
include_once SHARED_PATH . 'json_fields.php';

use cfg\component\component;
use cfg\component\component_link;
use cfg\component\component_link_list;
use cfg\component\component_link_type;
use cfg\component\component_list;
use cfg\component\component_type_list;
use cfg\component\position_type_list;
use cfg\component\view_style_list;
use cfg\db\sql_db;
use cfg\element\element;
use cfg\element\element_list;
use cfg\element\element_type_list;
use cfg\formula\expression;
use cfg\formula\figure;
use cfg\formula\figure_list;
use cfg\formula\formula;
use cfg\formula\formula_link;
use cfg\formula\formula_link_type;
use cfg\formula\formula_link_type_list;
use cfg\formula\formula_list;
use cfg\formula\formula_type;
use cfg\formula\formula_type_list;
use cfg\group\group;
use cfg\group\group_list;
use cfg\helper\type_list;
use cfg\helper\type_object;
use cfg\language\language;
use cfg\language\language_form;
use cfg\language\language_form_list;
use cfg\language\language_list;
use cfg\log\change;
use cfg\log\change_action_list;
use cfg\log\change_field;
use cfg\log\change_field_list;
use cfg\log\change_link;
use cfg\log\change_log_list;
use cfg\log\change_table;
use cfg\log\change_table_list;
use cfg\log\change_values_big;
use cfg\log\change_values_geo_big;
use cfg\log\change_values_geo_norm;
use cfg\log\change_values_geo_prime;
use cfg\log\change_values_norm;
use cfg\log\change_values_prime;
use cfg\log\change_values_text_big;
use cfg\log\change_values_text_norm;
use cfg\log\change_values_text_prime;
use cfg\log\change_values_time_big;
use cfg\log\change_values_time_norm;
use cfg\log\change_values_time_prime;
use cfg\log\changes_big;
use cfg\log\changes_norm;
use cfg\phrase\phrase;
use cfg\phrase\phrase_list;
use cfg\phrase\phrase_types;
use cfg\phrase\term;
use cfg\phrase\term_list;
use cfg\ref\ref;
use cfg\ref\ref_type;
use cfg\ref\ref_type_list;
use cfg\ref\source;
use cfg\ref\source_type;
use cfg\ref\source_type_list;
use cfg\result\result;
use cfg\result\result_list;
use cfg\result\results;
use cfg\sandbox\protection_type_list;
use cfg\sandbox\sandbox;
use cfg\sandbox\share_type_list;
use cfg\system\job;
use cfg\system\job_list;
use cfg\system\job_type_list;
use cfg\system\sys_log;
use cfg\system\sys_log_list;
use cfg\system\sys_log_status_list;
use cfg\user\user;
use cfg\user\user_profile_list;
use cfg\value\value;
use cfg\value\value_geo;
use cfg\value\value_list;
use cfg\value\value_text;
use cfg\value\value_time;
use cfg\value\value_time_series;
use cfg\value\value_ts_data;
use cfg\verb\verb;
use cfg\verb\verb_list;
use cfg\view\view;
use cfg\view\view_link_type;
use cfg\view\view_link_type_list;
use cfg\view\view_list;
use cfg\view\view_term_link;
use cfg\view\view_type_list;
use cfg\word\triple;
use cfg\word\triple_list;
use cfg\word\word;
use cfg\word\word_db;
use cfg\word\word_list;
use controller\api_message;
use DateTime;
use html\phrase\phrase_list as phrase_list_dsp;
use html\system\messages;
use html\view\view_list as view_list_dsp;
use html\word\word as word_dsp;
use shared\enum\change_actions;
use shared\enum\change_fields;
use shared\enum\change_tables;
use shared\enum\sys_log_statuus;
use shared\enum\user_profiles;
use shared\json_fields;
use shared\library;
use shared\const\components;
use shared\const\formulas;
use shared\const\groups;
use shared\const\refs;
use shared\const\sources;
use shared\const\triples;
use shared\const\values;
use shared\const\views;
use shared\const\words;
use shared\types\api_type_list;
use shared\types\component_type as comp_type_shared;
use shared\types\phrase_type as phrase_type_shared;
use shared\types\position_types;
use shared\types\protection_type as protect_type_shared;
use shared\types\share_type as share_type_shared;
use shared\types\verbs;
use shared\types\view_styles;
use shared\types\view_type;
use unit\sys_log_tests;
use unit_write\component_link_write_tests;
use unit_write\component_write_tests;
use unit_write\formula_link_write_tests;
use unit_write\formula_write_tests;
use unit_write\group_write_tests;
use unit_write\source_write_tests;
use unit_write\triple_write_tests;
use unit_write\value_write_tests;
use unit_write\view_write_tests;
use unit_write\word_write_tests;

class create_test_objects extends test_base
{

    // the timestamp used for unit testing
    const DUMMY_DATETIME = '2022-12-26T18:23:45+01:00';

    /*
     * dummy objects for unit tests
     */

    function type_lists_api(user $usr): string
    {
        $is_ok = true;

        $usr_pro_cac = new user_profile_list();
        $phr_typ_cac = new phrase_types();
        $frm_typ_cac = new formula_type_list();
        $frm_lnk_typ_cac = new formula_link_type_list();
        $elm_typ_cac = new element_type_list();
        $msk_typ_cac = new view_type_list();
        $msk_sty_cac = new view_style_list();
        $msk_lnk_typ_cac = new view_link_type_list();
        $cmp_typ_cac = new component_type_list();
        //$cmp_lnk_typ_cac = new component_link_type_list();
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
        //$cmp_lnk_typ_cac->load_dummy();
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
        $this->read_all_names_from_config_csv($phr_typ_cac);

        $vars[json_fields::LIST_USER_PROFILES] = $usr_pro_cac->api_json_array();
        $vars[json_fields::LIST_PHRASE_TYPES] = $phr_typ_cac->api_json_array();
        $vars[json_fields::LIST_FORMULA_TYPES] = $frm_typ_cac->api_json_array();
        $vars[json_fields::LIST_FORMULA_LINK_TYPES] = $frm_lnk_typ_cac->api_json_array();
        $vars[json_fields::LIST_ELEMENT_TYPES] = $elm_typ_cac->api_json_array();
        $vars[json_fields::LIST_VIEW_TYPES] = $msk_typ_cac->api_json_array();
        $vars[json_fields::LIST_VIEW_STYLES] = $msk_sty_cac->api_json_array();
        $vars[json_fields::LIST_VIEW_LINK_TYPES] = $msk_lnk_typ_cac->api_json_array();
        $vars[json_fields::LIST_COMPONENT_TYPES] = $cmp_typ_cac->api_json_array();
        // TODO activate
        //$vars[json_fields::LIST_VIEW_COMPONENT_LINK_TYPES] = $cmp_lnk_typ_cac->api_json_array();
        $vars[json_fields::LIST_COMPONENT_POSITION_TYPES] = $pos_typ_cac->api_json_array();
        $vars[json_fields::LIST_REF_TYPES] = $ref_typ_cac->api_json_array();
        $vars[json_fields::LIST_SOURCE_TYPES] = $src_typ_cac->api_json_array();
        $vars[json_fields::LIST_SHARE_TYPES] = $shr_typ_cac->api_json_array();
        $vars[json_fields::LIST_PROTECTION_TYPES] = $ptc_typ_cac->api_json_array();
        $vars[json_fields::LIST_LANGUAGES] = $lan_cac->api_json_array();
        $vars[json_fields::LIST_LANGUAGE_FORMS] = $lan_for_cac->api_json_array();
        $vars[json_fields::LIST_SYS_LOG_STATI] = $sys_log_sta_cac->api_json_array();
        $vars[json_fields::LIST_JOB_TYPES] = $job_typ_cac->api_json_array();
        $vars[json_fields::LIST_CHANGE_LOG_ACTIONS] = $cng_act_cac->api_json_array();
        $vars[json_fields::LIST_CHANGE_LOG_TABLES] = $cng_tbl_cac->api_json_array();
        $vars[json_fields::LIST_CHANGE_LOG_FIELDS] = $cng_fld_cac->api_json_array();
        $vars[json_fields::LIST_VERBS] = $vrb_cac->api_json_array();
        $sys_msk_cac = $this->view_list();
        $vars[json_fields::LIST_SYSTEM_VIEWS] = $sys_msk_cac->api_json_array(new api_type_list([]));

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
    public function read_all_names_from_config_csv(type_list $list): bool
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
                        $typ_obj->set_id($data[$id_col]);
                        $typ_obj->set_name($data[$name_col]);
                        if ($code_id_col > 0) {
                            $typ_obj->set_code_id($data[$code_id_col]);
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
        foreach (BASE_CODE_LINK_FILES as $csv_class) {
            $csv_file_name = $lib->class_to_name($csv_class);
            if (str_ends_with($type, '_list')) {
                $csv_list_type = $csv_file_name . '_list';
            } else {
                $csv_list_type = $csv_file_name;
            }
            $csv_file_name .= sql_db::TABLE_EXTENSION;
            if ($csv_list_type == $type) {
                $csv_path = PATH_BASE_CODE_LINK_FILES . $csv_file_name . BASE_CODE_LINK_FILE_TYPE;
            }
        }
        return $csv_path;
    }

    /**
     * @return user the user used for unit testing
     */
    function user_sys_test(): user
    {
        $usr = new user();
        $usr->set(3, user::SYSTEM_TEST_NAME, user::SYSTEM_TEST_EMAIL);
        $usr->set_profile(user_profiles::TEST);
        return $usr;
    }

    /**
     * @return word "Mathematics" as the main word for unit testing
     */
    function word(): word
    {
        $wrd = new word($this->usr1);
        $wrd->set(words::MATH_ID, words::MATH);
        $wrd->description = words::MATH_COM;
        $wrd->set_type(phrase_type_shared::NORMAL);
        global $ptc_typ_cac;
        $wrd->protection_id = $ptc_typ_cac->id(protect_type_shared::ADMIN);
        return $wrd;
    }

    /**
     * @return word "Mathematics" without the id e.g. as given by the import
     */
    function word_name_only(): word
    {
        $wrd = new word($this->usr1);
        $wrd->set_name(words::MATH);
        return $wrd;
    }

    /**
     * @return word "Mathematics" with all object variables set for complete unit testing
     */
    function word_filled(): word
    {
        global $shr_typ_cac;
        global $ptc_typ_cac;
        $wrd = new word($this->usr1);
        $wrd->set(words::MATH_ID, words::MATH);
        $wrd->description = words::MATH_COM;
        $wrd->set_type(phrase_type_shared::NORMAL);
        $wrd->set_code_id(words::MATH);
        $wrd->plural = words::MATH_PLURAL;
        $wrd->set_view_id(views::START_ID);
        $wrd->set_usage(2);
        $wrd->exclude();
        $wrd->share_id = $shr_typ_cac->id(share_type_shared::GROUP);
        $wrd->protection_id = $ptc_typ_cac->id(protect_type_shared::USER);
        return $wrd;
    }

    /**
     * @return word with all fields set and a reserved test name for testing the db write function
     */
    function word_filled_add(): word
    {
        $wrd = $this->word_filled();
        $wrd->include();
        $wrd->set_id(0);
        $wrd->set_name(words::TN_ADD);
        return $wrd;
    }

    /**
     * @return word with all fields set and a another reserved test name for testing the db write function
     */
    function word_filled_add_to(): word
    {
        $wrd = $this->word_filled();
        $wrd->include();
        $wrd->set_id(0);
        $wrd->set_name(words::TN_ADD_TO);
        return $wrd;
    }

    /**
     * @return word to test the sql insert via function
     */
    function word_add_by_func(): word
    {
        $wrd = new word($this->usr1);
        $wrd->set_name(words::TN_ADD_VIA_FUNC);
        return $wrd;
    }

    /**
     * @return word to test the sql insert without use of function
     */
    function word_add_by_sql(): word
    {
        $wrd = new word($this->usr1);
        $wrd->set_name(words::TN_ADD_VIA_SQL);
        return $wrd;
    }

    /**
     * @return word_dsp the word "Mathematics" for frontend unit testing
     */
    function word_dsp(): word_dsp
    {
        $wrd = $this->word();
        return new word_dsp($wrd->api_json());
    }

    /**
     * @return word "constant" to create the main triple for unit testing
     */
    function word_const(): word
    {
        $wrd = new word($this->usr1);
        $wrd->set(words::CONST_ID, words::CONST_NAME);
        $wrd->description = words::CONST_COM;
        $wrd->set_type(phrase_type_shared::MATH_CONST);
        global $ptc_typ_cac;
        $wrd->protection_id = $ptc_typ_cac->id(protect_type_shared::ADMIN);
        return $wrd;
    }

    /**
     * @return word "Pi" to test the const behavior
     */
    function word_pi(): word
    {
        $wrd = new word($this->usr1);
        $wrd->set(words::PI_ID, words::PI);
        $wrd->description = words::PI_COM;
        $wrd->set_type(phrase_type_shared::MATH_CONST);
        global $ptc_typ_cac;
        $wrd->protection_id = $ptc_typ_cac->id(protect_type_shared::ADMIN);
        return $wrd;
    }

    /**
     * @return word "circumference" to test the const behavior
     */
    function word_cf(): word
    {
        $wrd = new word($this->usr1);
        $wrd->set(words::CIRCUMFERENCE_ID, words::CIRCUMFERENCE);
        return $wrd;
    }

    /**
     * @return word "diameter" to test the const behavior
     */
    function word_diameter(): word
    {
        $wrd = new word($this->usr1);
        $wrd->set(words::DIAMETER_ID, words::DIAMETER);
        return $wrd;
    }

    /**
     * @return word "Euler's constant" to test the handling of >'<
     */
    function word_e(): word
    {
        $wrd = new word($this->usr1);
        $wrd->set(words::E_ID, words::E);
        $wrd->set_type(phrase_type_shared::MATH_CONST);
        return $wrd;
    }

    /**
     * @return word year e.g. to test the table row selection
     */
    function word_year(): word
    {
        $wrd = new word($this->usr1);
        $wrd->set(words::YEAR_CAP_ID, words::YEAR_CAP);
        $wrd->set_type(phrase_type_shared::TIME);
        return $wrd;
    }

    /**
     * @return word 2019 to test creating of a year
     */
    function word_2019(): word
    {
        $wrd = new word($this->usr1);
        $wrd->set(words::TI_2019, words::TN_2019);
        $wrd->set_type(phrase_type_shared::TIME);
        return $wrd;
    }

    /**
     * @return word 2020 to test create a year
     */
    function word_2020(): word
    {
        $wrd = new word($this->usr1);
        $wrd->set(words::TI_2020, words::TN_2020);
        $wrd->set_type(phrase_type_shared::TIME);
        return $wrd;
    }

    /**
     * @return word percent to test percent related rules e.g. to remove measure at division
     */
    function word_percent(): word
    {
        $wrd = new word($this->usr1);
        $wrd->set(words::TI_PCT, words::TN_PCT);
        $wrd->set_type(phrase_type_shared::PERCENT);
        return $wrd;
    }

    /**
     * @return word of the master pod name
     */
    function word_zukunftcom(): word
    {
        $wrd = new word($this->usr1);
        $wrd->set(words::MASTER_POD_NAME_ID, words::MASTER_POD_NAME);
        return $wrd;
    }

    /**
     * @return word pod
     */
    function word_pod(): word
    {
        $wrd = new word($this->usr1);
        $wrd->set(words::POD_ID, words::POD);
        return $wrd;
    }

    /**
     * @return word launch
     */
    function word_launch(): word
    {
        $wrd = new word($this->usr1);
        $wrd->set(words::LAUNCH_ID, words::LAUNCH);
        return $wrd;
    }

    /**
     * @return word url
     */
    function word_url(): word
    {
        $wrd = new word($this->usr1);
        $wrd->set(words::URL_ID, words::URL);
        return $wrd;
    }

    /**
     * @return word geo point
     */
    function word_point(): word
    {
        $wrd = new word($this->usr1);
        $wrd->set(words::POINT_ID, words::POINT);
        return $wrd;
    }

// TODO explain for each test object for which test it is used
// TODO rename because in the test object "$t->" the prefix dummy is not needed
    function word_this(): word
    {
        $wrd = new word($this->usr1);
        $wrd->set(words::THIS_ID, words::THIS_NAME);
        $wrd->set_type(phrase_type_shared::THIS);
        return $wrd;
    }

    function word_prior(): word
    {
        $wrd = new word($this->usr1);
        $wrd->set(words::PRIOR_ID, words::PRIOR_NAME);
        $wrd->set_type(phrase_type_shared::PRIOR);
        return $wrd;
    }

    function word_one(): word
    {
        $wrd = new word($this->usr1);
        $wrd->set(words::ONE_ID, words::ONE);
        $wrd->set_type(phrase_type_shared::SCALING_HIDDEN);
        return $wrd;
    }

    function word_mio(): word
    {
        $wrd = new word($this->usr1);
        $wrd->set(words::MIO_ID, words::MIO_SHORT);
        $wrd->set_type(phrase_type_shared::SCALING);
        return $wrd;
    }

    function word_minute(): word
    {
        $wrd = new word($this->usr1);
        $wrd->set(words::MINUTE_ID, words::MINUTE);
        return $wrd;
    }

    function word_second(): word
    {
        $wrd = new word($this->usr1);
        $wrd->set(words::SECOND_ID, words::SECOND);
        return $wrd;
    }

    function word_ch(): word
    {
        $wrd = new word($this->usr1);
        $wrd->set(words::CH_ID, words::CH);
        return $wrd;
    }

    /**
     * @return word city to test the verb "is a" / "are" to get the list of cities
     */
    function word_city(): word
    {
        $wrd = new word($this->usr1);
        $wrd->set(words::CITY_ID, words::CITY);
        return $wrd;
    }

    /**
     * @return word canton to test the separation of the cantons from the cities based on the same word
     */
    function word_canton(): word
    {
        $wrd = new word($this->usr1);
        $wrd->set(words::CANTON_ID, words::CANTON);
        return $wrd;
    }

    /**
     * @return word with id and name of Zurich
     */
    function word_zh(): word
    {
        $wrd = new word($this->usr1);
        $wrd->set(words::ZH_ID, words::ZH);
        return $wrd;
    }

    /**
     * @return word with id and name of Bern
     */
    function word_bern(): word
    {
        $wrd = new word($this->usr1);
        $wrd->set(words::BE_ID, words::BE);
        return $wrd;
    }

    /**
     * @return word with id and name of Geneva
     */
    function word_ge(): word
    {
        $wrd = new word($this->usr1);
        $wrd->set(words::GE_ID, words::GE);
        return $wrd;
    }

    function word_inhabitant(): word
    {
        $wrd = new word($this->usr1);
        $wrd->set(words::INHABITANT_ID, words::INHABITANT);
        $wrd->plural = words::INHABITANTS;
        return $wrd;
    }

    function word_parts(): word
    {
        $wrd = new word($this->usr1);
        $wrd->set(words::TI_PARTS, words::TN_PARTS);
        return $wrd;
    }

    function word_total(): word
    {
        $wrd = new word($this->usr1);
        $wrd->set(words::TI_TOTAL, words::TN_TOTAL_PRE);
        return $wrd;
    }

    function word_gwp(): word
    {
        $wrd = new word($this->usr1);
        $wrd->set(words::TI_GWP, words::TN_GWP);
        return $wrd;
    }

    function words_canton_zh_inhabitants(): array
    {
        return [words::ZH, words::CANTON, words::INHABITANTS, words::MIO];
    }

    /**
     * @return word_list with some basic words for unit testing
     */
    function word_list(): word_list
    {
        $lst = new word_list($this->usr1);
        $lst->add($this->word());
        $lst->add($this->word_const());
        $lst->add($this->word_pi());
        $lst->add($this->word_e());
        return $lst;
    }

    /**
     * @return word_list with a few words for unit testing
     */
    function word_list_short(): word_list
    {
        $lst = new word_list($this->usr1);
        $lst->add($this->word());
        $lst->add($this->word_pi());
        return $lst;
    }

    /**
     * @return word_list with at least one word of each type for unit testing
     */
    function word_list_all_types(): word_list
    {
        $lst = new word_list($this->usr1);
        $lst->add($this->word());
        $lst->add($this->word_const());
        $lst->add($this->word_pi());
        $lst->add($this->word_2019());
        $lst->add($this->word_one());
        $lst->add($this->word_mio());
        $lst->add($this->word_percent());
        return $lst;
    }

    /**
     * @return verb the default verb
     */
    function verb(): verb
    {
        $vrb = new verb(verbs::TI_READ, verbs::TN_READ, verbs::NOT_SET);
        $vrb->set_user($this->usr1);
        return $vrb;
    }

    /**
     * @return verb a standard verb with user null
     */
    function verb_is(): verb
    {
        return new verb(verbs::TI_IS, verbs::TN_IS, verbs::IS);
    }

    /**
     * @return verb a standard verb with user null
     */
    function verb_part(): verb
    {
        return new verb(verbs::TI_PART, verbs::TN_PART, verbs::IS_PART_OF);
    }

    /**
     * @return verb a standard verb with user null
     */
    function verb_of(): verb
    {
        $vrb = new verb(verbs::TI_OF, verbs::TN_OF, verbs::CAN_CONTAIN_NAME_REVERSE);
        $vrb->set_user($this->usr1);
        return $vrb;
    }

    /**
     * @return triple "Mathematical constant" used for unit testing
     */
    function triple(): triple
    {
        $trp = new triple($this->usr1);
        $trp->set(triples::MATH_CONST_ID, triples::MATH_CONST);
        $trp->description = triples::MATH_CONST_COM;
        $trp->set_from($this->word_const()->phrase());
        $trp->set_verb($this->verb_part());
        $trp->set_to($this->word()->phrase());
        $trp->set_type(phrase_type_shared::MATH_CONST);
        global $ptc_typ_cac;
        $trp->protection_id = $ptc_typ_cac->id(protect_type_shared::ADMIN);
        return $trp;
    }

    /**
     * @return triple with all fields set and a reserved test name for testing the db write function
     */
    function triple_filled_add(): triple
    {
        $trp = $this->triple();
        $trp->include();
        $trp->set_id(0);
        $trp->set_name(triples::SYSTEM_TEST_ADD);
        $trp->set_from($this->word_filled_add()->phrase());
        $trp->set_to($this->word_filled_add_to()->phrase());
        return $trp;
    }

    /**
     * @return triple "Mathematical constant" with only the name set as it may be created by the import
     */
    function triple_name_only(): triple
    {
        $trp = new triple($this->usr1);
        $trp->set_name(triples::MATH_CONST);
        return $trp;
    }

    /**
     * @return triple "Mathematical constant" with only the link names set as it may be created by the import
     */
    function triple_link_only(): triple
    {
        $trp = new triple($this->usr1);
        $trp->set_from($this->word_const()->phrase());
        $trp->set_verb($this->verb_part());
        $trp->set_to($this->word()->phrase());
        return $trp;
    }

    /**
     * @return triple "pi (math)" used for unit testing
     */
    function triple_pi(): triple
    {
        $trp = new triple($this->usr1);
        $trp->set(triples::PI_ID, triples::PI_NAME);
        $trp->description = triples::PI_COM;
        $trp->set_from($this->word_pi()->phrase());
        $trp->set_verb($this->verb_is());
        $trp->set_to($this->triple()->phrase());
        $trp->set_type(phrase_type_shared::TRIPLE_HIDDEN);
        return $trp;
    }

    /**
     * @return triple to select the system configuration
     */
    function triple_sys_config(): triple
    {
        $wrd = new triple($this->usr1);
        $wrd->set(triples::SYSTEM_CONFIG_ID, triples::SYSTEM_CONFIG);
        return $wrd;
    }

    /**
     * @return triple "e (math const)" used for unit testing
     */
    function triple_e(): triple
    {
        $trp = new triple($this->usr1);
        $trp->set(triples::E_ID, triples::E);
        $trp->description = triples::E_COM;
        $trp->set_from($this->word_e()->phrase());
        $trp->set_verb($this->verb_is());
        $trp->set_to($this->triple()->phrase());
        $trp->set_type(phrase_type_shared::TRIPLE_HIDDEN);
        return $trp;
    }

    /**
     * @return triple to test the sql insert via function
     */
    function triple_add_by_func(): triple
    {
        $trp = new triple($this->usr1);
        $trp->set_name(triples::SYSTEM_TEST_ADD_VIA_FUNC);
        $wrd_add_func = $this->load_word(words::TN_ADD_VIA_FUNC);
        $wrd_math = $this->load_word(words::MATH);
        $trp->set_from($wrd_add_func->phrase());
        $trp->set_verb($this->verb_is());
        $trp->set_to($wrd_math->phrase());
        return $trp;
    }

    /**
     * @return triple to test the sql insert without use of a function
     */
    function triple_add_by_sql(): triple
    {
        $trp = new triple($this->usr1);
        $trp->set_name(triples::SYSTEM_TEST_ADD_VIA_SQL);
        $wrd_add_func = $this->load_word(words::TN_ADD_VIA_SQL);
        $wrd_math = $this->load_word(words::MATH);
        $trp->set_from($wrd_add_func->phrase());
        $trp->set_verb($this->verb_is());
        $trp->set_to($wrd_math->phrase());
        return $trp;
    }

    /**
     * @return triple "Zurich (City)" used for unit testing
     */
    function zh_city(): triple
    {
        $trp = new triple($this->usr1);
        $trp->set(triples::CITY_ZH_ID, triples::CITY_ZH);
        $trp->set_from($this->word_zh()->phrase());
        $trp->set_verb($this->verb_is());
        $trp->set_to($this->word_city()->phrase());
        return $trp;
    }

    /**
     * @return triple "Zurich (City)" used for unit testing
     */
    function zh_canton(): triple
    {
        $trp = new triple($this->usr1);
        $trp->set(triples::CITY_ZH_ID, triples::CITY_ZH);
        $trp->set_from($this->word_zh()->phrase());
        $trp->set_verb($this->verb_is());
        $trp->set_to($this->word_canton()->phrase());
        return $trp;
    }

    /**
     * @return triple "Bern (City)" used for unit testing
     */
    function triple_bern(): triple
    {
        $trp = new triple($this->usr1);
        $trp->set(triples::CITY_BE_ID, triples::CITY_BE);
        $trp->set_from($this->word_bern()->phrase());
        $trp->set_verb($this->verb_is());
        $trp->set_to($this->word_city()->phrase());
        return $trp;
    }

    /**
     * @return triple "Geneva (City)" used for unit testing
     */
    function triple_ge(): triple
    {
        $trp = new triple($this->usr1);
        $trp->set(triples::CITY_GE_ID, triples::CITY_GE);
        $trp->set_from($this->word_ge()->phrase());
        $trp->set_verb($this->verb_is());
        $trp->set_to($this->word_city()->phrase());
        return $trp;
    }

    function triple_list(): triple_list
    {
        $lst = new triple_list($this->usr1);
        $lst->add($this->triple_pi());
        return $lst;
    }

    function phrase(): phrase
    {
        return $this->word()->phrase();
    }

    function phrase_pi(): phrase
    {
        return $this->triple_pi()->phrase();
    }

    /**
     * @return phrase of the word year because on most case the phrase is used instead of the word
     */
    function year(): phrase
    {
        return $this->word_year()->phrase();
    }

    /**
     * @return phrase of the word canton because on most case the phrase is used instead of the word
     */
    function canton(): phrase
    {
        return $this->word_canton()->phrase();
    }

    /**
     * @return phrase of the word city
     */
    function city(): phrase
    {
        return $this->word_city()->phrase();
    }

    function phrase_zh_city(): phrase
    {
        return $this->zh_city()->phrase();
    }

    function phrase_list(): phrase_list
    {
        $lst = new phrase_list($this->usr1);
        $lst->add($this->word()->phrase());
        $lst->add($this->word_const()->phrase());
        $lst->add($this->word_pi()->phrase());
        $lst->add($this->triple()->phrase());
        $lst->add($this->triple_pi()->phrase());
        return $lst;
    }

    function phrase_list_prime(): phrase_list
    {
        $lst = new phrase_list($this->usr1);
        $lst->add($this->word()->phrase());
        $lst->add($this->word_const()->phrase());
        $lst->add($this->triple()->phrase());
        $lst->add($this->triple_pi()->phrase());
        return $lst;
    }

    /**
     * @return phrase_list with one word and one triple
     */
    function phrase_list_small(): phrase_list
    {
        $lst = new phrase_list($this->usr1);
        $lst->add($this->word_pi()->phrase());
        $lst->add($this->triple()->phrase());
        return $lst;
    }

    /**
     * @return phrase_list with more than 10 phrases
     */
    function phrase_list_long(): phrase_list
    {
        $lst = new phrase_list($this->usr1);
        $lst->add($this->word()->phrase());
        $lst->add($this->word_const()->phrase());
        $lst->add($this->word_pi()->phrase());
        $lst->add($this->word_e()->phrase());
        $lst->add($this->word_2019()->phrase());
        $lst->add($this->word_one()->phrase());
        $lst->add($this->word_mio()->phrase());
        $lst->add($this->word_percent()->phrase());
        $lst->add($this->triple()->phrase());
        $lst->add($this->triple_pi()->phrase());
        $lst->add($this->zh_canton()->phrase());
        $lst->add($this->triple_bern()->phrase());
        $lst->add($this->triple_ge()->phrase());
        return $lst;
    }

    function phrase_list_pi(): phrase_list
    {
        $lst = new phrase_list($this->usr1);
        $lst->add($this->triple_pi()->phrase());
        return $lst;
    }

    function phrase_list_const(): phrase_list
    {
        $lst = new phrase_list($this->usr1);
        $lst->add($this->word_const()->phrase());
        return $lst;
    }

    /**
     * @return phrase_list with some math const e.g. to test loading a list of values by phrase list
     */
    function phrase_list_math_const(): phrase_list
    {
        $lst = new phrase_list($this->usr1);
        $lst->add($this->triple_pi()->phrase());
        $lst->add($this->triple_e()->phrase());
        return $lst;
    }

    /**
     * @return phrase_list with the cities for unit testing
     */
    function phrase_list_cities(): phrase_list
    {
        $lst = new phrase_list($this->usr1);
        $lst->add($this->zh_city()->phrase());
        $lst->add($this->triple_bern()->phrase());
        $lst->add($this->triple_ge()->phrase());
        return $lst;
    }

    /**
     * @return phrase_list with the Zurich inhabitants and 2020 for unit testing the result id
     */
    function zh_inhabitants_2020(): phrase_list
    {
        $lst = new phrase_list($this->usr1);
        $lst->add($this->zh_city()->phrase());
        $lst->add($this->word_inhabitant()->phrase());
        $lst->add($this->word_2020()->phrase());
        return $lst;
    }

    /**
     * @return phrase_list with the Zurich inhabitants and 2020 for unit testing the result id
     */
    function zh_ge_inhabitants_2020(): phrase_list
    {
        $lst = new phrase_list($this->usr1);
        $lst->add($this->zh_city()->phrase());
        $lst->add($this->triple_ge()->phrase());
        $lst->add($this->word_inhabitant()->phrase());
        $lst->add($this->word_2020()->phrase());
        return $lst;
    }

    /**
     * @return phrase_list with all phrases used for unit testing
     */
    function phrase_list_all(): phrase_list
    {
        $lst = new phrase_list($this->usr1);
        $lst->merge($this->phrase_list());
        $lst->merge($this->phrase_list_math_const());
        $lst->merge($this->phrase_list_cities());
        return $lst;
    }

    /**
     * @return phrase_list with 16 entries to test the normal group id creation
     * 1    ...../+
     * 11    .....9-
     * 12    .....A+
     * 37    .....Z-
     * 38    .....a+
     * 64    ..../.-
     * 376    ....3s+
     * 2367    ....Yz-
     * 13108    ...1Ao+
     * 82124    ...I1A-
     * 505294    ../vLC+
     * 2815273    ..8jId-
     * 17192845    .//ZSB+
     */
    function phrase_list_13(): phrase_list
    {
        $lst = new phrase_list($this->usr1);
        $wrd = $this->word();
        $wrd->set_id(1);
        $wrd->set_name('word1');
        $lst->add($wrd->phrase());
        $trp = $this->triple();
        $trp->set_id(11);
        $trp->set_name('triple1');
        $lst->add($trp->phrase());
        $wrd = $this->word();
        $wrd->set_id(12);
        $wrd->set_name('word2');
        $lst->add($wrd->phrase());
        $trp = $this->triple();
        $trp->set_id(37);
        $trp->set_name('triple2');
        $lst->add($trp->phrase());
        $wrd = $this->word();
        $wrd->set_id(38);
        $wrd->set_name('word3');
        $lst->add($wrd->phrase());
        $trp = $this->triple();
        $trp->set_id(64);
        $trp->set_name('triple3');
        $lst->add($trp->phrase());
        $wrd = $this->word();
        $wrd->set_id(376);
        $wrd->set_name('word4');
        $lst->add($wrd->phrase());
        $trp = $this->triple();
        $trp->set_id(2367);
        $trp->set_name('triple4');
        $lst->add($trp->phrase());
        $wrd = $this->word();
        $wrd->set_id(13108);
        $wrd->set_name('word5');
        $lst->add($wrd->phrase());
        $trp = $this->triple();
        $trp->set_id(82124);
        $trp->set_name('triple5');
        $lst->add($trp->phrase());
        $wrd = $this->word();
        $wrd->set_id(505294);
        $wrd->set_name('word6');
        $lst->add($wrd->phrase());
        $trp = $this->triple();
        $trp->set_id(2815273);
        $trp->set_name('triple6');
        $lst->add($trp->phrase());
        $wrd = $this->word();
        $wrd->set_id(17192845);
        $wrd->set_name('word7');
        $lst->add($wrd->phrase());
        return $lst;
    }

    /**
     * @return phrase_list with 16 entries to test the normal group id creation
     * 1    ...../+
     * 11    .....9-
     * 12    .....A+
     * 37    .....Z-
     * 38    .....a+
     * 64    ..../.-
     * 376    ....3s+
     * 2367    ....Yz-
     * 13108    ...1Ao+
     * 82124    ...I1A-
     * 505294    ../vLC+
     * 2815273    ..8jId-
     * 17192845    .//ZSB+
     * 106841477    .4LYK3-
     */
    function phrase_list_14(): phrase_list
    {
        $lst = $this->phrase_list_13();
        $trp = $this->triple();
        $trp->set_id(106841477);
        $trp->set_name('triple7');
        $lst->add($trp->phrase());
        return $lst;
    }

    /**
     * @return phrase_list with 16 entries to test the normal group id creation
     * 1    ...../+
     * 11    .....9-
     * 12    .....A+
     * 37    .....Z-
     * 38    .....a+
     * 64    ..../.-
     * 376    ....3s+
     * 2367    ....Yz-
     * 13108    ...1Ao+
     * 82124    ...I1A-
     * 505294    ../vLC+
     * 2815273    ..8jId-
     * 17192845    .//ZSB+
     * 106841477    .4LYK3-
     */
    function phrase_list_14b(): phrase_list
    {
        $lst = $this->phrase_list_13();
        $trp = $this->triple();
        $trp->set_id(3516593476);
        $trp->set_name('triple8');
        $lst->add($trp->phrase());
        return $lst;
    }

    /**
     * @return phrase_list with 16 entries to test the normal group id creation
     * 1    ...../+
     * 11    .....9-
     * 12    .....A+
     * 37    .....Z-
     * 38    .....a+
     * 64    ..../.-
     * 376    ....3s+
     * 2367    ....Yz-
     * 13108    ...1Ao+
     * 82124    ...I1A-
     * 505294    ../vLC+
     * 2815273    ..8jId-
     * 17192845    .//ZSB+
     * 106841477    .4LYK3-
     * 628779863    .ZSahL+
     * 3516593476    1FajJ2-
     */
    function phrase_list_16(): phrase_list
    {
        $lst = $this->phrase_list_13();
        $trp = $this->triple();
        $trp->set_id(106841477);
        $trp->set_name('triple7');
        $lst->add($trp->phrase());
        $wrd = $this->word();
        $wrd->set_id(628779863);
        $wrd->set_name('word8');
        $lst->add($wrd->phrase());
        $trp = $this->triple();
        $trp->set_id(3516593476);
        $trp->set_name('triple8');
        $lst->add($trp->phrase());
        return $lst;
    }

    function phrase_list_17_plus(): phrase_list
    {
        $lst = $this->phrase_list_16();
        $wrd = $this->word();
        $wrd->set_id(987654321);
        $wrd->set_name('word17');
        $lst->add($wrd->phrase());
        return $lst;
    }

    /**
     * @return phrase_list to get all inhabitant related to the Canton Zurich
     */
    function canton_zh_phrase_list(): phrase_list
    {
        $lst = new phrase_list($this->usr1);
        $lst->add($this->word_canton()->phrase());
        $lst->add($this->word_zh()->phrase());
        $lst->add($this->word_inhabitant()->phrase());
        return $lst;
    }

    /**
     * @return phrase_list to get all inhabitant related to the Canton Zurich
     */
    function ch_inhabitant_phrase_list(): phrase_list
    {
        $lst = new phrase_list($this->usr1);
        $lst->add($this->word_ch()->phrase());
        $lst->add($this->word_inhabitant()->phrase());
        return $lst;
    }

    /**
     * @return phrase_list the phrases relevant for having a second entry in the phrase group list
     */
    function phrase_list_zh_2019(): phrase_list
    {
        $lst = new phrase_list($this->usr1);
        $lst->add($this->word_zh()->phrase());
        $lst->add($this->word_inhabitant()->phrase());
        $lst->add($this->word_2019()->phrase());
        return $lst;
    }

    /**
     * @return phrase_list the phrases relevant for testing the max number of prime phrases
     */
    function phrase_list_zh_city(): phrase_list
    {
        $lst = new phrase_list($this->usr1);
        $lst->add($this->word_zh()->phrase());
        $lst->add($this->word_city()->phrase());
        $lst->add($this->word_inhabitant()->phrase());
        $lst->add($this->word_2019()->phrase());
        return $lst;
    }

    /**
     * @return phrase_list the phrases relevant for testing the max number of prime phrases
     */
    function phrase_list_zh_city_pct(): phrase_list
    {
        $lst = $this->phrase_list_zh_city();
        $lst->add($this->word_percent()->phrase());
        return $lst;
    }

    /**
     * @return phrase_list the phrases relevant for testing the max number of prime phrases
     */
    function phrase_list_zh_mio(): phrase_list
    {
        $lst = new phrase_list($this->usr1);
        $lst->add($this->word_zh()->phrase());
        $lst->add($this->word_inhabitant()->phrase());
        $lst->add($this->word_2019()->phrase());
        $lst->add($this->word_mio()->phrase());
        return $lst;
    }

    /**
     * @return phrase_list the phrases relevant for testing the max number of prime phrases
     */
    function phrase_list_canton_mio(): phrase_list
    {
        $lst = new phrase_list($this->usr1);
        $lst->add($this->word_zh()->phrase());
        $lst->add($this->word_canton()->phrase());
        $lst->add($this->word_inhabitant()->phrase());
        $lst->add($this->word_2019()->phrase());
        $lst->add($this->word_mio()->phrase());
        return $lst;
    }

    /**
     * @return phrase_list the phrases relevant for testing the max number of prime phrases
     */
    function phrase_list_canton_pct(): phrase_list
    {
        $lst = new phrase_list($this->usr1);
        $lst->add($this->word_zh()->phrase());
        $lst->add($this->word_canton()->phrase());
        $lst->add($this->word_inhabitant()->phrase());
        $lst->add($this->word_2019()->phrase());
        $lst->add($this->word_percent()->phrase());
        return $lst;
    }

    /**
     * @return phrase_list the phrases relevant for testing the max number of prime phrases
     */
    function phrase_list_ch_mio(): phrase_list
    {
        $lst = new phrase_list($this->usr1);
        $lst->add($this->word_ch()->phrase());
        $lst->add($this->word_inhabitant()->phrase());
        $lst->add($this->word_2019()->phrase());
        $lst->add($this->word_mio()->phrase());
        return $lst;
    }

    /**
     * @return phrase_list the phrases relevant for testing the max number of prime phrases
     */
    function phrase_list_zh_mio_2020(): phrase_list
    {
        $lst = new phrase_list($this->usr1);
        $lst->add($this->word_zh()->phrase());
        $lst->add($this->word_inhabitant()->phrase());
        $lst->add($this->word_2020()->phrase());
        $lst->add($this->word_mio()->phrase());
        return $lst;
    }

    /**
     * @return phrase_list the phrases relevant for testing the increase formula
     */
    function phrase_list_increase(): phrase_list
    {
        $lst = new phrase_list($this->usr1);
        $lst->add($this->word_percent()->phrase());
        $lst->add($this->word_this()->phrase());
        $lst->add($this->word_prior()->phrase());
        $lst->add($this->word_ch()->phrase());
        $lst->add($this->word_inhabitant()->phrase());
        $lst->add($this->word_2020()->phrase());
        $lst->add($this->word_mio()->phrase());
        return $lst;
    }

    /**
     * @return phrase_list with the phrases to select the launch date of this pod in the config
     */
    function phrase_list_pod_launch(): phrase_list
    {
        $lst = new phrase_list($this->usr1);
        $lst->add($this->word_zukunftcom()->phrase());
        $lst->add($this->triple_sys_config()->phrase());
        $lst->add($this->word_pod()->phrase());
        $lst->add($this->word_launch()->phrase());
        return $lst;
    }

    /**
     * @return phrase_list with the phrases to select the url of this pod in the config
     */
    function phrase_list_pod_url(): phrase_list
    {
        $lst = new phrase_list($this->usr1);
        $lst->add($this->word_zukunftcom()->phrase());
        $lst->add($this->triple_sys_config()->phrase());
        $lst->add($this->word_pod()->phrase());
        $lst->add($this->word_url()->phrase());
        return $lst;
    }

    /**
     * @return phrase_list with the phrases to select the geolocation of this pod development in the config
     */
    function phrase_list_pod_point(): phrase_list
    {
        $lst = new phrase_list($this->usr1);
        $lst->add($this->word_zukunftcom()->phrase());
        $lst->add($this->triple_sys_config()->phrase());
        $lst->add($this->word_pod()->phrase());
        $lst->add($this->word_point()->phrase());
        return $lst;
    }

    function phrase_list_dsp(): phrase_list_dsp
    {
        return new phrase_list_dsp($this->phrase_list()->api_json());
    }

    /**
     * @return group with one prime phrases
     */
    function group(): group
    {
        $lst = $this->phrase_list_pi();
        $grp = $lst->get_grp_id(false);
        $grp->name = groups::TN_READ;
        return $grp;
    }

    /**
     * @return group with the phrases of the launch date of this pod
     */
    function group_pod_launch(): group
    {
        $lst = $this->phrase_list_pod_launch();
        $grp = $lst->get_grp_id(false);
        $grp->name = groups::TN_TIME_VALUE;
        $grp->description = groups::TD_TIME_VALUE;
        return $grp;
    }

    /**
     * @return group with the phrases of the url of this pod
     */
    function group_pod_url(): group
    {
        $lst = $this->phrase_list_pod_url();
        $grp = $lst->get_grp_id(false);
        $grp->name = groups::TN_TEXT_VALUE;
        $grp->description = groups::TD_TEXT_VALUE;
        return $grp;
    }

    /**
     * @return group with the phrases of the geolocation of this pod
     */
    function group_pod_point(): group
    {
        $lst = $this->phrase_list_pod_point();
        $grp = $lst->get_grp_id(false);
        $grp->name = groups::TN_GEO_VALUE;
        $grp->description = groups::TD_GEO_VALUE;
        return $grp;
    }

    /**
     * @return group with three prime phrases
     */
    function group_prime_3(): group
    {
        $lst = $this->phrase_list_zh_2019();
        $grp = $lst->get_grp_id(false);
        $grp->name = groups::TN_ZH_2019;
        return $grp;
    }

    /**
     * @return group with the max number of prime phrases
     */
    function group_prime_max(): group
    {
        $lst = $this->phrase_list_zh_mio();
        $grp = $lst->get_grp_id(false);
        $grp->name = groups::TN_ZH_2019_IN_MIO;
        return $grp;
    }

    /**
     * @return group with the max number of main phrases
     */
    function group_main_max(): group
    {
        $lst = $this->phrase_list_increase();
        $grp = $lst->get_grp_id(false);
        $grp->name = groups::TN_CH_INCREASE_2020;
        return $grp;
    }

    function group_16(): group
    {
        $lst = $this->phrase_list_16();
        $grp = $lst->get_grp_id(false);
        $grp->name = groups::TN_READ;
        return $grp;
    }

    function group_17_plus(): group
    {
        $lst = $this->phrase_list_17_plus();
        $grp = $lst->get_grp_id(false);
        $grp->name = groups::TN_READ;
        return $grp;
    }

    /**
     * @return group with only the word constant
     */
    function group_const(): group
    {
        $lst = $this->phrase_list_const();
        $grp = $lst->get_grp_id(false);
        $grp->name = groups::TN_READ;
        return $grp;
    }

    function group_zh(): group
    {
        $lst = $this->phrase_list_zh_2019();
        $grp = $lst->get_grp_id(false);
        $grp->name = groups::TN_ZH_2019;
        return $grp;
    }

    function group_canton(): group
    {
        $lst = $this->phrase_list_canton_mio();
        return $lst->get_grp_id(false);
    }

    function group_ch(): group
    {
        $lst = $this->phrase_list_ch_mio();
        return $lst->get_grp_id(false);
    }

    function group_list(): group_list
    {
        $lst = new group_list($this->usr1);
        $lst->add($this->group());
        return $lst;
    }

    function group_list_long(): group_list
    {
        $lst = new group_list($this->usr1);
        $lst->add($this->group());
        $lst->add($this->group_zh());
        $lst->add($this->group_prime_3());
        $lst->add($this->group_prime_max());
        $lst->add($this->group_main_max());
        $lst->add($this->group_16());
        $lst->add($this->group_17_plus());
        return $lst;
    }

    function term(): term
    {
        return $this->word()->term();
    }

    function term_triple(): term
    {
        return $this->triple()->term();
    }

    function term_formula(): term
    {
        return $this->formula()->term();
    }

    function term_verb(): term
    {
        return $this->verb()->term();
    }

    /**
     * @return term_list with all terms used for the unit tests
     */
    function term_list(): term_list
    {
        $lst = new term_list($this->usr1);
        $lst->add($this->term());
        $lst->add($this->term_triple());
        $lst->add($this->term_formula());
        $lst->add($this->term_verb());
        return $lst;
    }

    /**
     * @return term_list with all terms used for the unit tests
     */
    function term_list_all(): term_list
    {
        $lst = new term_list($this->usr1);
        $lst->add($this->term());
        $lst->add($this->term_triple());
        $lst->add($this->term_formula());
        $lst->add($this->term_verb());
        $lst->add($this->triple_pi()->term());
        $lst->add($this->word_pi()->term());
        $lst->add($this->word_cf()->term());
        $lst->add($this->word_percent()->term());
        $lst->add($this->word_prior()->term());
        $lst->add($this->word_this()->term());
        $lst->add($this->word_parts()->term());
        $lst->add($this->word_total()->term());
        $lst->add($this->verb_of()->term());
        $lst->add($this->word_one()->term());
        $lst->add($this->word_mio()->term());
        return $lst;
    }

    /**
     * @return term_list a term list with the time terms e.g. minute and second
     */
    function term_list_time(): term_list
    {
        $lst = new term_list($this->usr1);
        $lst->add($this->word_second()->term());
        $lst->add($this->word_minute()->term());
        return $lst;
    }

    /**
     * @return term_list the terms relevant for testing the increase formula
     */
    function term_list_increase(): term_list
    {
        $lst = new term_list($this->usr1);
        $lst->add($this->word_percent()->term());
        $lst->add($this->formula_this()->term());
        $lst->add($this->formula_prior()->term());
        $lst->add($this->word_ch()->term());
        $lst->add($this->word_inhabitant()->term());
        $lst->add($this->word_2020()->term());
        $lst->add($this->word_mio()->term());
        return $lst;
    }

    /**
     * @return term_list a term list with the scaling terms e.g. one and million
     */
    function term_list_scale(): term_list
    {
        $lst = new term_list($this->usr1);
        $lst->add($this->word_one()->term());
        $lst->add($this->word_mio()->term());
        return $lst;
    }

    function value(): value
    {
        $grp = $this->group();
        return new value($this->usr1, round(values::PI_LONG, 13), $grp);
    }

    function time_value(): value_time
    {
        $grp = $this->group_pod_launch();
        return new value_time($this->usr1, new DateTime(values::TIME), $grp);
    }

    function text_value(): value_text
    {
        $grp = $this->group_pod_url();
        return new value_text($this->usr1, values::TEXT, $grp);
    }

    function geo_value(): value_geo
    {
        $grp = $this->group_pod_point();
        return new value_geo($this->usr1, values::GEO, $grp);
    }

    /**
     * @return value test that the number zero is written to the database
     */
    function value_zero(): value
    {
        $grp = $this->group();
        return new value($this->usr1, values::SAMPLE_ZERO, $grp);
    }

    /**
     * @return value with more than one prime phrase
     */
    function value_prime_3(): value
    {
        $grp = $this->group_prime_3();
        return new value($this->usr1, round(values::PI_LONG, 13), $grp);
    }

    /**
     * @return value with the maximal number of prime phrase
     */
    function value_prime_max(): value
    {
        $grp = $this->group_prime_max();
        return new value($this->usr1, round(values::PI_LONG, 13), $grp);
    }

    function value_16(): value
    {
        $grp = $this->group_16();
        return new value($this->usr1, round(values::PI_LONG, 13), $grp);
    }

    function value_16_filled(): value
    {
        global $shr_typ_cac;
        global $ptc_typ_cac;
        $grp = $this->group_16();
        $val = new value($this->usr1, round(values::PI_LONG, 13), $grp);
        $val->set_source_id($this->source()->id());
        $val->exclude();
        $val->share_id = $shr_typ_cac->id(share_type_shared::GROUP);
        $val->protection_id = $ptc_typ_cac->id(protect_type_shared::USER);
        return $val;
    }

    function value_17_plus(): value
    {
        $grp = $this->group_17_plus();
        return new value($this->usr1, round(values::PI_LONG, 13), $grp);
    }

    function value_zh(): value
    {
        $grp = $this->group_zh();
        return new value($this->usr1, values::CITY_ZH_INHABITANTS_2019, $grp);
    }

    function value_canton(): value
    {
        $grp = $this->group_canton();
        return new value($this->usr1, values::CANTON_ZH_INHABITANTS_2020_IN_MIO, $grp);
    }

    function value_ch(): value
    {
        $grp = $this->group_ch();
        return new value($this->usr1, values::CH_INHABITANTS_2019_IN_MIO, $grp);
    }

    function value_list(): value_list
    {
        $lst = new value_list($this->usr1);
        $lst->add($this->value());
        $lst->add($this->value_zh());
        return $lst;
    }

    /**
     * @return value_time_series e.g. to test the table and index creation
     */
    function value_time_series(): value_time_series
    {
        $vts = new value_time_series($this->usr1);
        $vts->set_grp($this->group_16());
        return $vts;
    }

    /**
     * @return value_ts_data for testing e.g. to test matrix calculations
     */
    function value_ts_data(): value_ts_data
    {
        $ts = new value_ts_data();
        $ts->value = round(values::PI_LONG, 13);
        return $ts;
    }

    /**
     * @return formula for testing e.g. the expression calculation
     */
    function formula(): formula
    {
        $frm = new formula($this->usr1);
        $frm->set(1, formulas::SCALE_TO_SEC);
        $frm->set_user_text(formulas::SCALE_TO_SEC_EXP, $this->term_list_time());
        $frm->set_type(formula_type::CALC);
        return $frm;
    }

    /**
     * @return formula with only the name set to test reseving the name
     */
    function formula_name_only(): formula
    {
        $frm = new formula($this->usr1);
        $frm->set(1, formulas::SCALE_MIO_EXP);
        return $frm;
    }

    /**
     * @return formula with all object variables set for complete unit testing e.g. of the sql function creation
     */
    function formula_filled(): formula
    {
        global $shr_typ_cac;
        global $ptc_typ_cac;
        $frm = new formula($this->usr1);
        $frm->set(1, formulas::SCALE_TO_SEC);
        $frm->set_user_text(formulas::SCALE_TO_SEC_EXP, $this->term_list_time());
        $frm->set_type(formula_type::CALC);
        $frm->description = formulas::SCALE_TO_SEC_COM;
        $frm->need_all_val = true;
        $frm->last_update = new DateTime(sys_log_tests::TV_TIME);
        $frm->set_view_id(views::START_ID);
        $frm->set_usage(2);
        $frm->exclude();
        $frm->share_id = $shr_typ_cac->id(share_type_shared::GROUP);
        $frm->protection_id = $ptc_typ_cac->id(protect_type_shared::USER);
        return $frm;
    }

    /**
     * @return formula with all fields set and a reserved test name for testing the db write function
     */
    function formula_filled_add(): formula
    {
        $frm = $this->formula_filled();
        $frm->include();
        $frm->set_id(0);
        $frm->set_name(formulas::SYSTEM_TEXT_ADD);
        return $frm;
    }

    /**
     * @return formula to test the "increase" calculations
     */
    function formula_increase(): formula
    {
        $frm = new formula($this->usr1);
        $frm->set(formulas::INCREASE_ID, formulas::INCREASE);
        $frm->set_user_text(formulas::INCREASE_EXP, $this->term_list_increase());
        $frm->set_type(formula_type::CALC);
        return $frm;
    }

    /**
     * @return formula to select the actual value related to the given context
     */
    function formula_this(): formula
    {
        $frm = new formula($this->usr1);
        $frm->set(formulas::THIS_ID, formulas::THIS_NAME);
        $frm->set_user_text(formulas::THIS_EXP, $this->phrase_list_increase()->term_list());
        $frm->set_type(formula_type::THIS);
        return $frm;
    }

    /**
     * @return formula to select the last value previous the actual value related to the given context
     */
    function formula_prior(): formula
    {
        $frm = new formula($this->usr1);
        $frm->set(formulas::PRIOR_ID, formulas::PRIOR);
        $frm->set_user_text(formulas::PRIOR_EXP, $this->phrase_list_increase()->term_list());
        $frm->set_type(formula_type::PREV);
        return $frm;
    }

    function formula_list(): formula_list
    {
        $lst = new formula_list($this->usr1);
        $lst->add($this->formula());
        return $lst;
    }

    function formula_link(): formula_link
    {
        global $frm_lnk_typ_cac;
        $lnk = new formula_link($this->usr1);
        $lnk->set(1, $this->formula(), $this->word()->phrase());
        $lnk->set_predicate_id($frm_lnk_typ_cac->id(formula_link_type::TIME_PERIOD));
        $lnk->order_nbr = 2;
        return $lnk;
    }

    function formula_link_filled(): formula_link
    {
        global $shr_typ_cac;
        global $ptc_typ_cac;
        $lnk = $this->formula_link();
        $lnk->exclude();
        $lnk->share_id = $shr_typ_cac->id(share_type_shared::GROUP);
        $lnk->protection_id = $ptc_typ_cac->id(protect_type_shared::USER);
        return $lnk;
    }

    function formula_link_filled_add(): formula_link
    {
        $lnk = $this->formula_link();
        $lnk->include();
        $lnk->set_id(0);
        $lnk->set_formula($this->formula_filled_add());
        $lnk->set_phrase($this->word_filled_add()->phrase());
        return $lnk;
    }

    /**
     * @return formula to test the sql insert via function
     */
    function formula_add_by_func(): formula
    {
        $frm = new formula($this->usr1);
        $frm->set_name(formulas::SYSTEM_TEXT_ADD_VIA_FUNC);
        $frm->set_user_text(formulas::INCREASE_EXP, $this->term_list_increase());
        $frm->set_type(formula_type::CALC);
        return $frm;
    }

    /**
     * based on the phrase list by intention to test what happens if the formulas are missing
     * @return formula to test the sql insert without use of function
     */
    function formula_add_by_sql(): formula
    {
        $frm = new formula($this->usr1);
        $frm->set_name(formulas::SYSTEM_TEXT_ADD_VIA_SQL);
        $frm->set_user_text(formulas::INCREASE_EXP, $this->phrase_list_increase()->term_list());
        $frm->set_type(formula_type::CALC);
        return $frm;
    }

    function expression(): expression
    {
        $trm_lst = $this->term_list_time();
        return $this->formula()->expression($trm_lst);
    }

    function element(): element
    {
        $elm_lst = $this->element_list();
        return $elm_lst->lst()[0];
    }

    function element_list(): element_list
    {
        $trm_lst = $this->term_list_time();
        $exp = $this->formula()->expression($trm_lst);
        return $exp->element_list($trm_lst);
    }

    function result_simple(): result
    {
        $res = new result($this->usr1);
        $wrd = $this->word();
        $phr_lst = new phrase_list($this->usr1);
        $phr_lst->add($wrd->phrase());
        $res->set_id(1);
        $res->grp()->set_phrase_list($phr_lst);
        $res->set_number(results::TV_INT);
        return $res;
    }

    function result_prime(): result
    {
        $res = new result($this->usr1);
        $res->set_grp($this->group());
        $res->set_src_grp($this->group_const());
        $res->set_number(results::TV_INT);
        return $res;
    }

    function result_prime_max(): result
    {
        $res = new result($this->usr1);
        $res->set_grp($this->group_prime_3());
        $res->set_src_grp($this->group_const());
        $res->set_number(results::TV_INT);
        return $res;
    }

    function result_main(): result
    {
        $res = new result($this->usr1);
        $res->set_grp($this->group_prime_max());
        $res->set_src_grp($this->group_const());
        $res->set_number(results::TV_INT);
        return $res;
    }

    function result_main_max(): result
    {
        $res = new result($this->usr1);
        $res->set_formula($this->formula());
        $res->set_grp($this->group_main_max());
        $res->set_src_grp($this->group_const());
        $res->set_number(results::TV_INT);
        return $res;
    }

    /**
     * @return result with all fields set to none standard to test if all fields are updated
     */
    function result_main_filled(): result
    {
        global $shr_typ_cac;
        global $ptc_typ_cac;
        $res = $this->result_main_max();
        $res->exclude();
        $res->share_id = $shr_typ_cac->id(share_type_shared::GROUP);
        $res->protection_id = $ptc_typ_cac->id(protect_type_shared::USER);
        return $res;
    }

    function result(): result
    {
        $res = new result($this->usr1);
        $res->set_grp($this->group_16());
        $res->set_number(results::TV_INT);
        return $res;
    }

    function result_big(): result
    {
        $res = new result($this->usr1);
        $res->set_grp($this->group_17_plus());
        $res->set_number(results::TV_INT);
        return $res;
    }


    function result_pct(): result
    {
        $res = new result($this->usr1);
        $wrd_pct = $this->new_word(words::TN_PCT, 2, phrase_type_shared::PERCENT);
        $phr_lst = new phrase_list($this->usr1);
        $phr_lst->add($wrd_pct->phrase());
        $res->grp()->set_phrase_list($phr_lst);
        $res->set_number(results::TV_PCT);
        return $res;
    }

    function result_list(): result_list
    {
        $lst = new result_list($this->usr1);
        $lst->add($this->result_simple());
        $lst->add($this->result_pct());
        return $lst;
    }

    /**
     * @return figure with all vars set for unit testing - user value case
     */
    function figure_value(): figure
    {
        $val = $this->value();
        $val->set_last_update(new DateTime(self::DUMMY_DATETIME));
        return $val->figure();
    }

    /**
     * @return figure with all vars set for unit testing - user value case
     */
    function figure_result(): figure
    {
        $res = $this->result_simple();
        return $res->figure();
    }

    function figure_list(): figure_list
    {
        $lst = new figure_list($this->usr1);
        $lst->add($this->figure_value());
        $lst->add($this->figure_result());
        return $lst;
    }

    function source(): source
    {
        $src = new source($this->usr1);
        $src->set(sources::SIB_ID, sources::SIB, source_type::PDF);
        $src->description = sources::SIB_COM;
        $src->url = sources::SIB_URL;
        return $src;
    }

    /**
     * @return source with all fields set for testing the sql function creation
     */
    function source_filled(): source
    {
        global $shr_typ_cac;
        global $ptc_typ_cac;
        $src = $this->source();
        $src->exclude();
        $src->share_id = $shr_typ_cac->id(share_type_shared::GROUP);
        $src->protection_id = $ptc_typ_cac->id(protect_type_shared::USER);
        return $src;
    }

    /**
     * @return source with all fields set and a reseved test name for testing the db write function
     */
    function source_filled_add(): source
    {
        $src = $this->source_filled();
        $src->include();
        $src->set_id(0);
        $src->set_name(sources::SYSTEM_TEST_ADD);
        return $src;
    }

    /**
     * @return source used for the reference
     */
    function source_ref(): source
    {
        $src = new source($this->usr1);
        $src->set(sources::WIKIDATA_ID, sources::WIKIDATA, source_type::CSV);
        return $src;
    }

    /**
     * @return source additional with the fields that only an admin user is allowed to import
     */
    function source_admin(): source
    {
        $src = $this->source();
        $src->code_id = sources::SIB_CODE;
        return $src;
    }

    /**
     * @return source to test the sql insert via function
     */
    function source_add_by_func(): source
    {
        $msk = new source($this->usr1);
        $msk->set_name(sources::SYSTEM_TEST_ADD_VIA_FUNC);
        return $msk;
    }

    /**
     * @return source to test the sql insert without use of function
     */
    function source_add_by_sql(): source
    {
        $msk = new source($this->usr1);
        $msk->set_name(sources::SYSTEM_TEST_ADD_VIA_SQL);
        return $msk;
    }

    /**
     * @return ref with the most often used fields set for unit testing
     */
    function reference(): ref
    {
        global $ref_typ_cac;
        $ref = new ref($this->usr1);
        $ref->set(refs::PI_ID,
            $this->word_pi()->phrase(), $ref_typ_cac->id(ref_type::WIKIDATA), refs::PI_KEY);
        $ref->description = refs::PI_COM;
        return $ref;
    }

    /**
     * @return ref with the most often used fields set for unit testing
     */
    function reference1(): ref
    {
        global $ref_typ_cac;
        $ref = new ref($this->usr1);
        $ref->set(1,
            $this->word()->phrase(), $ref_typ_cac->id(ref_type::WIKIDATA), refs::PI_KEY);
        $ref->description = refs::PI_COM;
        return $ref;
    }

    /**
     * @return ref with the more fields set for unit testing
     */
    function reference_plus(): ref
    {
        $ref = $this->reference();
        $ref->source = $this->source_ref();
        $ref->url = refs::PI_URL;
        return $ref;
    }

    /**
     * @return ref with the most often fields changed by user plus the link to the norm db row
     */
    function reference_user(): ref
    {
        $ref = new ref($this->usr1);
        $ref->set(4);
        $ref->description = refs::PI_COM;
        return $ref;
    }

    /**
     * @return ref with the most often used fields set for unit testing
     */
    function reference_change(): ref
    {
        global $ref_typ_cac;
        $ref = new ref($this->usr1);
        $ref->set(12,
            $this->word_gwp()->phrase(), $ref_typ_cac->id(ref_type::WIKIDATA), refs::CHANGE_NEW_KEY);
        $ref->description = refs::CHANGE_OLD_KEY;
        return $ref;
    }

    /**
     * @return ref with all fields set to a non default value
     */
    function ref_filled(): ref
    {
        global $shr_typ_cac;
        global $ptc_typ_cac;
        $ref = $this->reference();
        $ref->source = $this->source();
        $ref->url = refs::PI_URL;
        $ref->include();
        $ref->share_id = $shr_typ_cac->id(share_type_shared::GROUP);
        $ref->protection_id = $ptc_typ_cac->id(protect_type_shared::USER);
        return $ref;
    }

    /**
     * @return ref with all field changed to a non default value that can be user specific
     */
    function ref_filled_user(): ref
    {
        global $shr_typ_cac;
        global $ptc_typ_cac;
        $ref = $this->reference_user();
        $ref->external_key = refs::PI_KEY;
        $ref->url = refs::PI_URL;
        $ref->source = $this->source();
        $ref->description = refs::PI_COM;
        $ref->exclude();
        $ref->share_id = $shr_typ_cac->id(share_type_shared::GROUP);
        $ref->protection_id = $ptc_typ_cac->id(protect_type_shared::USER);
        return $ref;
    }

    function ref_filled_add(): ref
    {
        $ref = $this->ref_filled();
        $ref->include();
        $ref->set_id(0);
        $ref->set_phrase($this->word_filled_add()->phrase());
        return $ref;
    }

    function view(): view
    {
        $msk = new view($this->usr1);
        $msk->set(1, views::START_NAME);
        $msk->description = views::START_COM;
        $msk->code_id = views::START_CODE;
        return $msk;
    }

    function view_protected(): view
    {
        global $ptc_typ_cac;
        $msk = new view($this->usr1);
        $msk->set(1, views::START_NAME);
        $msk->description = views::START_COM;
        $msk->code_id = views::START_CODE;
        $msk->set_type(view_type::ENTRY);
        $msk->protection_id = $ptc_typ_cac->id(protect_type_shared::ADMIN);
        return $msk;
    }

    /**
     * @return view with sample data to view a phrase from the science point of view
     */
    function view_science(): view
    {
        $msk = new view($this->usr1);
        $msk->set(views::SCIENCE_ID, views::SCIENCE);
        $msk->description = views::SCIENCE_NAME;
        return $msk;
    }

    /**
     * @return view with sample data to show mainly related words that are relevant in sciences
     */
    function view_historic(): view
    {
        $msk = new view($this->usr1);
        $msk->set(views::HISTORIC_ID, views::HISTORIC_NAME);
        $msk->description = views::HISTORIC_COM;
        return $msk;
    }

    /**
     * @return view a view from the biological point of view e.g. with the
     */
    function view_biological(): view
    {
        $msk = new view($this->usr1);
        $msk->set(views::BIOLOGICAL_ID, views::BIOLOGICAL_NAME);
        $msk->description = views::BIOLOGICAL_COM;
        return $msk;
    }

    /**
     * @return view with sample data to show mainly related words that are relevant in sciences
     */
    function view_education(): view
    {
        $msk = new view($this->usr1);
        $msk->set(views::EDUCATION_ID, views::EDUCATION_NAME);
        $msk->description = views::EDUCATION_COM;
        return $msk;
    }

    /**
     * @return view with sample data to show mainly related words that are relevant in sciences
     */
    function view_touristic(): view
    {
        $msk = new view($this->usr1);
        $msk->set(views::TOURISTIC_ID, views::TOURISTIC_NAME);
        $msk->description = views::TOURISTIC_COM;
        return $msk;
    }

    /**
     * @return view with sample data to show mainly related words that are relevant in sciences
     */
    function view_graph(): view
    {
        $msk = new view($this->usr1);
        $msk->set(views::GRAPH_ID, views::GRAPH_NAME);
        $msk->description = views::GRAPH_COM;
        return $msk;
    }

    /**
     * @return view with sample data to show mainly related words that are relevant in sciences
     */
    function view_simple(): view
    {
        $msk = new view($this->usr1);
        $msk->set(views::SIMPLE_ID, views::SIMPLE_NAME);
        $msk->description = views::SIMPLE_COM;
        return $msk;
    }

    /**
     * @return view created by a user, so without a code_id
     */
    function view_added(): view
    {
        $msk = new view($this->usr1);
        $msk->set(1, views::START_NAME);
        $msk->description = views::START_COM;
        return $msk;
    }

    /**
     * @return view with all fields e.g. to check if all fields are covered by the sql insert statement creation
     */
    function view_filled(): view
    {
        global $shr_typ_cac;
        global $ptc_typ_cac;
        $msk = new view($this->usr1);
        $msk->set(1, views::START_NAME);
        $msk->description = views::START_COM;
        $msk->code_id = views::START_CODE;
        $msk->set_type(view_type::DETAIL);
        $msk->set_style(view_styles::COL_SM_4);
        $msk->exclude();
        $msk->share_id = $shr_typ_cac->id(share_type_shared::GROUP);
        $msk->protection_id = $ptc_typ_cac->id(protect_type_shared::USER);
        return $msk;
    }

    /**
     * @return view with all fields set and a reseved test name for testing the db write function
     */
    function view_filled_add(): view
    {
        $msk = $this->view_filled();
        $msk->include();
        $msk->set_id(0);
        $msk->code_id = views::TEST_ADD;
        $msk->set_name(views::TEST_ADD_NAME);
        return $msk;
    }

    /**
     * @return view to test the sql insert via function
     */
    function view_add_by_func(): view
    {
        $msk = new view($this->usr1);
        $msk->set_name(views::TEST_ADD_VIA_FUNC_NAME);
        return $msk;
    }

    /**
     * @return view to test the sql insert without use of function
     */
    function view_add_by_sql(): view
    {
        $msk = new view($this->usr1);
        $msk->set_name(views::TEST_ADD_VIA_SQL_NAME);
        return $msk;
    }

    function view_with_components(): view
    {
        $msk = $this->view_protected();
        $msk->cmp_lnk_lst = $this->component_link_list();
        return $msk;
    }

    function view_word_add(): view
    {
        $msk = new view($this->usr1);
        $msk->set(views::TEST_FORM_ID, views::TEST_FORM_NAME);
        $msk->description = views::TEST_FORM_COM;
        $msk->code_id = views::TEST_FORM;
        $msk->cmp_lnk_lst = $this->components_word_add($msk);
        return $msk;
    }

    function view_list(): view_list
    {
        $lst = new view_list($this->usr1);
        $lst->add($this->view_with_components());
        $lst->add($this->view_word_add());
        return $lst;
    }

    /**
     * @return view_list with a list of suggested views for a word
     */
    function view_list_word(): view_list
    {
        $lst = new view_list($this->usr1);
        $lst->add($this->view_science());
        $lst->add($this->view_historic());
        $lst->add($this->view_education());
        $lst->add($this->view_touristic());
        return $lst;
    }

    /**
     * TODO add the relevance to test the sorting
     * @return view_list with a longer list of suggested views for a word
     */
    function view_list_word_long(): view_list
    {
        $lst = $this->view_list_word();
        $lst->add($this->view_biological());
        $lst->add($this->view_graph());
        $lst->add($this->view_simple());
        return $lst;
    }

    /**
     * @return view_list_dsp a sample frontend view list
     */
    function view_list_dsp(): view_list_dsp
    {
        return new view_list_dsp($this->view_list_word()->api_json());
    }

    /**
     * @return view_list_dsp a sample frontend view list with more than 5 entries
     */
    function view_list_long_dsp(): view_list_dsp
    {
        return new view_list_dsp($this->view_list_word_long()->api_json());
    }

    function view_link(): view_term_link
    {
        global $msk_lnk_typ_cac;
        $lnk = new view_term_link($this->usr1);
        $lnk->set(1, $this->view(), $this->word()->term());
        $lnk->set_predicate_id($msk_lnk_typ_cac->id(view_link_type::DEFAULT));
        $lnk->description = 2;
        return $lnk;
    }

    function view_link_filled(): view_term_link
    {
        global $shr_typ_cac;
        global $ptc_typ_cac;
        $lnk = $this->view_link();
        $lnk->exclude();
        $lnk->share_id = $shr_typ_cac->id(share_type_shared::GROUP);
        $lnk->protection_id = $ptc_typ_cac->id(protect_type_shared::USER);
        return $lnk;
    }

    function view_link_filled_add(): view_term_link
    {
        $lnk = $this->view_link_filled();
        $lnk->include();
        $lnk->set_id(0);
        $lnk->set_view($this->view_filled_add());
        $lnk->set_term($this->word_filled_add()->term());
        return $lnk;
    }

    function component(): component
    {
        $cmp = new component($this->usr1);
        $cmp->set(components::WORD_ID, components::WORD_NAME, comp_type_shared::PHRASE_NAME);
        $cmp->description = components::WORD_COM;
        return $cmp;
    }

    function component_matrix(): component
    {
        $cmp = new component($this->usr1);
        $cmp->set(components::MATRIX_ID, components::MATRIX_NAME, comp_type_shared::CALC_SHEET);
        $cmp->description = components::MATRIX_COM;
        return $cmp;
    }

    /**
     * @return component with all fields set to check if the save and load process is complete
     */
    function component_filled(): component
    {
        global $shr_typ_cac;
        global $ptc_typ_cac;
        $cmp = new component($this->usr1);
        $cmp->set(1, components::WORD_NAME, comp_type_shared::PHRASE_NAME);
        $cmp->description = components::WORD_COM;
        $cmp->set_type(comp_type_shared::TEXT);
        $cmp->set_style(view_styles::COL_SM_4);
        $cmp->code_id = components::FORM_TITLE;
        $cmp->ui_msg_code_id = messages::PLEASE_SELECT;
        $cmp->set_row_phrase($this->year());
        $cmp->set_col_phrase($this->canton());
        $cmp->set_col_sub_phrase($this->city());
        $cmp->set_formula($this->formula());
        $cmp->set_link_type(component_link_type::EXPRESSION);
        $cmp->exclude();
        $cmp->share_id = $shr_typ_cac->id(share_type_shared::GROUP);
        $cmp->protection_id = $ptc_typ_cac->id(protect_type_shared::USER);
        return $cmp;
    }

    /**
     * @return component with all fields set and a reseved test name for testing the db write function
     */
    function component_filled_add(): component
    {
        $cmp = $this->component_filled();
        $cmp->include();
        $cmp->set_id(0);
        $cmp->set_name(components::TEST_ADD_NAME);
        return $cmp;
    }

    /**
     * @return component to test the sql insert via function
     */
    function component_add_by_func(): component
    {
        $cmp = new component($this->usr1);
        $cmp->set_name(components::TEST_ADD_VIA_FUNC_NAME);
        $cmp->set_type(comp_type_shared::TEXT);
        return $cmp;
    }

    /**
     * @return component to test the sql insert without use of function
     */
    function component_add_by_sql(): component
    {
        $cmp = new component($this->usr1);
        $cmp->set_name(components::TEST_ADD_VIA_SQL_NAME);
        $cmp->set_type(comp_type_shared::TEXT);
        return $cmp;
    }

    function component_word_add_title(): component
    {
        $cmp = new component($this->usr1);
        $cmp->set(1, components::FORM_TITLE_NAME, comp_type_shared::FORM_TITLE);
        $cmp->description = components::FORM_TITLE_COM;
        $cmp->code_id = components::FORM_TITLE;
        return $cmp;
    }

    function component_word_add_back_stack(): component
    {
        $cmp = new component($this->usr1);
        $cmp->set(2, components::FORM_BACK_NAME, comp_type_shared::FORM_BACK);
        $cmp->description = components::FORM_BACK_COM;
        $cmp->code_id = components::FORM_BACK;
        return $cmp;
    }

    function component_word_add_button_confirm(): component
    {
        $cmp = new component($this->usr1);
        $cmp->set(3, components::FORM_CONFIRM_NAME, comp_type_shared::FORM_CONFIRM);
        $cmp->description = components::FORM_CONFIRM_COM;
        $cmp->code_id = components::FORM_CONFIRM;
        return $cmp;
    }

    function component_word_add_name(): component
    {
        $cmp = new component($this->usr1);
        $cmp->set(4, components::FORM_NAME_NAME, comp_type_shared::FORM_NAME);
        $cmp->description = components::FORM_NAME_COM;
        $cmp->code_id = components::FORM_NAME;
        return $cmp;
    }

    function component_word_add_description(): component
    {
        $cmp = new component($this->usr1);
        $cmp->set(5, components::FORM_DESCRIPTION_NAME, comp_type_shared::FORM_DESCRIPTION);
        $cmp->description = components::FORM_DESCRIPTION_COM;
        $cmp->code_id = components::FORM_DESCRIPTION;
        return $cmp;
    }

    function component_word_add_phrase_type(): component
    {
        $cmp = new component($this->usr1);
        $cmp->set(6, components::FORM_PHRASE_TYPE_NAME, comp_type_shared::FORM_PHRASE_TYPE);
        $cmp->description = components::FORM_PHRASE_TYPE_COM;
        $cmp->code_id = components::FORM_PHRASE_TYPE;
        return $cmp;
    }

    function component_word_add_share_type(): component
    {
        $cmp = new component($this->usr1);
        $cmp->set(7, components::FORM_SHARE_TYPE_NAME, comp_type_shared::FORM_SHARE_TYPE);
        $cmp->description = components::FORM_SHARE_TYPE_COM;
        $cmp->code_id = components::FORM_SHARE_TYPE;
        return $cmp;
    }

    function component_word_add_protection_type(): component
    {
        $cmp = new component($this->usr1);
        $cmp->set(8, components::FORM_PROTECTION_TYPE_NAME, comp_type_shared::FORM_PROTECTION_TYPE);
        $cmp->description = components::FORM_PROTECTION_TYPE_COM;
        $cmp->code_id = components::FORM_PROTECTION_TYPE;
        return $cmp;
    }

    function component_word_add_cancel(): component
    {
        $cmp = new component($this->usr1);
        $cmp->set(9, components::FORM_CANCEL_NAME, comp_type_shared::FORM_CANCEL);
        $cmp->description = components::FORM_CANCEL_COM;
        $cmp->code_id = components::FORM_CANCEL;
        return $cmp;
    }

    function component_word_add_save(): component
    {
        $cmp = new component($this->usr1);
        $cmp->set(10, components::FORM_SAVE_NAME, comp_type_shared::FORM_SAVE);
        $cmp->description = components::FORM_SAVE_COM;
        $cmp->code_id = components::FORM_SAVE;
        return $cmp;
    }

    function component_word_add_form_end(): component
    {
        $cmp = new component($this->usr1);
        $cmp->set(11, components::FORM_END_NAME, comp_type_shared::FORM_END);
        $cmp->description = components::FORM_END_COM;
        $cmp->code_id = components::FORM_END;
        return $cmp;
    }

    function component_list(): component_list
    {
        $lst = new component_list($this->usr1);
        $lst->add($this->component());
        $lst->add($this->component_word_add_share_type());
        return $lst;
    }

    function component_link(): component_link
    {
        $lnk = new component_link($this->usr1);
        $lnk->set(1, $this->view(), $this->component(), 1);
        return $lnk;
    }

    function component_matrix_link(): component_link
    {
        $lnk = new component_link($this->usr1);
        $lnk->set(2, $this->view(), $this->component_matrix(), 2);
        return $lnk;
    }


    function component_link_filled(): component_link
    {
        global $shr_typ_cac;
        global $ptc_typ_cac;
        $lnk = new component_link($this->usr1);
        $lnk->set(1, $this->view(), $this->component(), 1);
        $lnk->set_predicate(component_link_type::EXPRESSION);
        $lnk->set_pos_type(position_types::SIDE);
        $lnk->set_style(view_styles::COL_SM_4);
        $lnk->exclude();
        $lnk->share_id = $shr_typ_cac->id(share_type_shared::GROUP);
        $lnk->protection_id = $ptc_typ_cac->id(protect_type_shared::USER);
        return $lnk;
    }

    function component_link_filled_add(): component_link
    {
        $lnk = $this->component_link_filled();
        $lnk->include();
        $lnk->set_id(0);
        $lnk->set_view($this->view_filled_add());
        $lnk->set_component($this->component_filled_add());
        return $lnk;
    }

    function component_link_list(): component_link_list
    {
        $lst = new component_link_list($this->usr1);
        $lst->add_link($this->component_link());
        $lst->add_link($this->component_matrix_link());
        return $lst;
    }

    function components_word_add(view $msk): component_link_list
    {
        $pos = 1;
        $lst = new component_link_list($this->usr1);
        $lst->add($pos, $msk, $this->component_word_add_title(), $pos);
        $pos++;
        $lst->add($pos, $msk, $this->component_word_add_back_stack(), $pos);
        $pos++;
        $lst->add($pos, $msk, $this->component_word_add_button_confirm(), $pos);
        $pos++;
        $lst->add($pos, $msk, $this->component_word_add_name(), $pos);
        $pos++;
        $lst->add($pos, $msk, $this->component_word_add_description(), $pos);
        $pos++;
        $lst->add($pos, $msk, $this->component_word_add_phrase_type(), $pos);
        $pos++;
        $lst->add($pos, $msk, $this->component_word_add_share_type(), $pos);
        $pos++;
        $lst->add($pos, $msk, $this->component_word_add_protection_type(), $pos);
        $pos++;
        $lst->add($pos, $msk, $this->component_word_add_cancel(), $pos);
        $pos++;
        $lst->add($pos, $msk, $this->component_word_add_save(), $pos);
        $pos++;
        $lst->add($pos, $msk, $this->component_word_add_form_end(), $pos);
        return $lst;
    }

    function view_term_link(): view_term_link
    {
        $lnk = new view_term_link($this->usr1);
        $lnk->set_view($this->view());
        $lnk->set_predicate(view_link_type::DEFAULT);
        $lnk->set_term($this->term());
        return $lnk;
    }

    function language(): language
    {
        return new language(language::DEFAULT, language::TN_READ, 'English is the default', 1);
    }

    /**
     * @return change an insert change log entry of a named user sandbox object with some dummy values
     */
    function change_log_named(): change
    {
        global $usr_sys;

        $chg = new change($usr_sys);
        $chg->set_time_str(self::DUMMY_DATETIME);
        $chg->set_action(change_actions::ADD);
        $chg->set_table(change_tables::WORD);
        $chg->set_field(change_fields::FLD_WORD_NAME);
        $chg->new_value = words::MATH;
        $chg->row_id = 1;
        return $chg;
    }

    /**
     * @return change an update change log entry of a named user sandbox object
     */
    function change_log_named_update(): change
    {
        $chg = $this->change_log_named();
        $chg->old_value = words::TN_RENAMED;
        return $chg;
    }

    /**
     * @return change a delete change log entry of a named user sandbox object
     */
    function change_log_named_delete(): change
    {
        $chg = $this->change_log_named_update();
        $chg->new_value = null;
        return $chg;
    }

    /**
     * @return change an insert change log entry for a reference of a named user sandbox object
     */
    function change_log_ref(): change
    {
        global $phr_typ_cac;
        $chg = $this->change_log_named();
        $chg->set_field(change_fields::FLD_PHRASE_TYPE);
        $chg->new_value = phrase_type_shared::TIME;
        $chg->new_id = $phr_typ_cac->id(phrase_type_shared::TIME);
        return $chg;
    }

    /**
     * @return change an insert change log entry for a reference of a named user sandbox object
     */
    function change_log_ref_update(): change
    {
        global $phr_typ_cac;
        $chg = $this->change_log_ref();
        $chg->old_value = phrase_type_shared::MEASURE;
        $chg->old_id = $phr_typ_cac->id(phrase_type_shared::MEASURE);
        return $chg;
    }

    /**
     * @return change an insert change log entry for a reference of a named user sandbox object
     */
    function change_log_ref_delete(): change
    {
        $chg = $this->change_log_ref_update();
        $chg->new_value = null;
        $chg->new_id = null;
        return $chg;
    }

    /**
     * @return changes_norm a change log entry of a group where the id is a 512bit field and not an id
     */
    function change_log_norm(): changes_norm
    {
        global $usr_sys;

        $chg = new changes_norm($usr_sys);
        $chg->set_time_str(self::DUMMY_DATETIME);
        $chg->set_action(change_actions::ADD);
        $chg->set_table(change_tables::WORD);
        $chg->set_field(change_fields::FLD_WORD_NAME);
        $chg->new_value = words::MATH;
        $chg->row_id = 1;
        return $chg;
    }

    /**
     * @return changes_big a change log entry of a group where the id is a text field and not an id
     */
    function change_log_big(): changes_big
    {
        global $usr_sys;

        $chg = new changes_big($usr_sys);
        $chg->set_time_str(self::DUMMY_DATETIME);
        $chg->set_action(change_actions::ADD);
        $chg->set_table(change_tables::WORD);
        $chg->set_field(change_fields::FLD_WORD_NAME);
        $chg->new_value = words::MATH;
        $chg->row_id = 1;
        return $chg;
    }

    /**
     * @return object an insert change log entry of a value with some dummy values and a standard group id
     */
    function log_obj_from_class(string $class): object
    {
        $lib = new library();

        $log = $this->log_class_to_object($class);
        $val_class = $this->log_class_to_value_class($class);
        $val_fld = $this->log_class_to_value_field($class);
        $val = $this->log_class_to_value($class);
        $log->set_time_str(self::DUMMY_DATETIME);
        $log->set_action(change_actions::ADD);
        $log->set_table($lib->class_to_table($val_class));
        $log->set_field($val_fld);
        $log->group_id = $this->group()->id();
        $log->new_value = $val;
        $log->row_id = 1;
        return $log;
    }

    /**
     * create the change log object based on the log class name
     * @param string $class the name of the log class
     * @return change|change_values_big|change_values_geo_big|change_values_geo_norm|change_values_geo_prime|change_values_norm|change_values_prime|change_values_text_prime|change_values_text_norm|change_values_text_big|change_values_time_big|change_values_time_norm|change_values_time_prime|changes_big|changes_norm
     */
    private function log_class_to_object(string $class): change|change_values_big|change_values_geo_big|change_values_geo_norm|change_values_geo_prime|change_values_norm|change_values_prime|change_values_text_prime|change_values_text_norm|change_values_text_big|change_values_time_big|change_values_time_norm|change_values_time_prime|changes_big|changes_norm
    {
        global $usr_sys;

        if ($class == change::class) {
            $chg = new change($usr_sys);
        } elseif ($class == changes_norm::class) {
            $chg = new changes_norm($usr_sys);
        } elseif ($class == changes_big::class) {
            $chg = new changes_big($usr_sys);
        } elseif ($class == change_values_prime::class) {
            $chg = new change_values_prime($usr_sys);
        } elseif ($class == change_values_norm::class) {
            $chg = new change_values_norm($usr_sys);
        } elseif ($class == change_values_big::class) {
            $chg = new change_values_big($usr_sys);
        } elseif ($class == change_values_time_prime::class) {
            $chg = new change_values_time_prime($usr_sys);
        } elseif ($class == change_values_time_norm::class) {
            $chg = new change_values_time_norm($usr_sys);
        } elseif ($class == change_values_time_big::class) {
            $chg = new change_values_time_big($usr_sys);
        } elseif ($class == change_values_text_prime::class) {
            $chg = new change_values_text_prime($usr_sys);
        } elseif ($class == change_values_text_norm::class) {
            $chg = new change_values_text_norm($usr_sys);
        } elseif ($class == change_values_text_big::class) {
            $chg = new change_values_text_big($usr_sys);
        } elseif ($class == change_values_geo_prime::class) {
            $chg = new change_values_geo_prime($usr_sys);
        } elseif ($class == change_values_geo_norm::class) {
            $chg = new change_values_geo_norm($usr_sys);
        } elseif ($class == change_values_geo_big::class) {
            $chg = new change_values_geo_big($usr_sys);
        } else {
            log_err('change log class ' . $class . ' not expected');
            $chg = new change($usr_sys);
        }
        return $chg;
    }

    private function log_class_to_value_class(string $class): string
    {
        return match ($class) {
            change::class,
            changes_norm::class,
            changes_big::class
            => word::class,
            change_values_prime::class,
            change_values_big::class,
            change_values_norm::class
            => value::class,
            change_values_time_prime::class,
            change_values_time_big::class,
            change_values_time_norm::class
            => value_time::class,
            change_values_text_prime::class,
            change_values_text_norm::class,
            change_values_text_big::class
            => value_text::class,
            change_values_geo_prime::class,
            change_values_geo_norm::class,
            change_values_geo_big::class
            => value_geo::class,
            change_link::class => triple::class,
        };
    }

    private function log_class_to_value_field(string $class): string
    {
        return match ($class) {
            change::class,
            changes_norm::class,
            changes_big::class
            => word_db::FLD_NAME,
            change_values_prime::class,
            change_values_big::class,
            change_values_norm::class
            => value::FLD_VALUE,
            change_values_time_prime::class,
            change_values_time_big::class,
            change_values_time_norm::class
            => value_time::FLD_VALUE,
            change_values_text_prime::class,
            change_values_text_norm::class,
            change_values_text_big::class
            => value_text::FLD_VALUE,
            change_values_geo_prime::class,
            change_values_geo_norm::class,
            change_values_geo_big::class
            => value_geo::FLD_VALUE,
            change_link::class => triple::class,
        };
    }

    private function log_class_to_value(string $class): string|float|Datetime
    {
        return match ($class) {
            change::class,
            changes_norm::class,
            changes_big::class
            => words::MATH,
            change_values_prime::class,
            change_values_big::class,
            change_values_norm::class
            => values::PI_SHORT,
            change_values_time_prime::class,
            change_values_time_big::class,
            change_values_time_norm::class
            => (new DateTime(values::TIME)),
            change_values_text_prime::class,
            change_values_text_norm::class,
            change_values_text_big::class
            => values::TEXT,
            change_values_geo_prime::class,
            change_values_geo_norm::class,
            change_values_geo_big::class
            => values::GEO,
            change_link::class => triple::class,
        };
    }

    /**
     * @return change_values_norm an insert change log entry of a value with some dummy values and a standard group id
     */
    function change_log_value(): change_values_norm
    {
        global $usr_sys;

        $chg = new change_values_norm($usr_sys);
        $chg->set_time_str(self::DUMMY_DATETIME);
        $chg->set_action(change_actions::ADD);
        $chg->set_table(change_tables::VALUE);
        $chg->set_field(change_fields::FLD_NUMERIC_VALUE);
        $chg->group_id = $this->group()->id();
        $chg->new_value = values::PI_SHORT;
        $chg->row_id = 1;
        return $chg;
    }

    /**
     * @return change_values_prime a change log entry of a value with some dummy values and a prime group id
     */
    function change_log_value_prime(): change_values_prime
    {
        global $usr_sys;

        $chg = new change_values_prime($usr_sys);
        $chg->set_time_str(self::DUMMY_DATETIME);
        $chg->set_action(change_actions::ADD);
        $chg->set_table(change_tables::WORD);
        $chg->set_field(change_fields::FLD_WORD_NAME);
        $chg->new_value = values::PI_SHORT;
        $chg->row_id = 1;
        return $chg;
    }

    /**
     * @return change_values_big a change log entry of a value with some dummy values and a big group id
     */
    function change_log_value_big(): change_values_big
    {
        global $usr_sys;

        $chg = new change_values_big($usr_sys);
        $chg->set_time_str(self::DUMMY_DATETIME);
        $chg->set_action(change_actions::ADD);
        $chg->set_table(change_tables::WORD);
        $chg->set_field(change_fields::FLD_WORD_NAME);
        $chg->new_value = values::PI_SHORT;
        $chg->row_id = 1;
        return $chg;
    }

    /**
     * @return change_values_norm an update change log entry of a value
     */
    function change_log_value_update(): change_values_norm
    {
        $chg = $this->change_log_value();
        $chg->old_value = values::SAMPLE_INT;
        return $chg;
    }

    /**
     * @return change_values_norm a delete change log entry of a value
     */
    function change_log_value_delete(): change_values_norm
    {
        $chg = $this->change_log_value_update();
        $chg->new_value = null;
        return $chg;
    }

    /**
     * @return change_link a change log entry of a link change
     */
    function change_log_link(): change_link
    {
        global $usr_sys;

        $chg = new change_link($usr_sys);
        $chg->set_time_str(self::DUMMY_DATETIME);
        $chg->set_action(change_actions::ADD);
        $chg->set_table(change_tables::TRIPLE);
        $chg->new_from_id = words::CONST_ID;
        $chg->new_link_id = verbs::TI_PART;
        $chg->new_to_id = words::MATH_ID;
        $chg->row_id = 1;
        return $chg;
    }

    /**
     * @return sys_log a open system error log entry
     */
    function sys_log(): sys_log
    {
        global $sys_log_sta_cac;
        $sys = new sys_log();
        $sys->set_id(1);
        $sys->log_time = new DateTime(sys_log_tests::TV_TIME);
        $sys->usr_name = user::SYSTEM_TEST_NAME;
        $sys->log_text = sys_log_tests::TV_LOG_TEXT;
        $sys->log_trace = sys_log_tests::TV_LOG_TRACE;
        $sys->function_name = sys_log_tests::TV_FUNC_NAME;
        $sys->solver_name = sys_log_tests::TV_SOLVE_ID;
        $sys->status_name = $sys_log_sta_cac->id(sys_log_statuus::OPEN);
        return $sys;
    }

    /**
     * @return sys_log a closed system error log entry
     */
    function sys_log2(): sys_log
    {
        global $sys_log_sta_cac;
        $sys = new sys_log();
        $sys->set_id(2);
        $sys->log_time = new DateTime(sys_log_tests::TV_TIME);
        $sys->usr_name = user::SYSTEM_TEST_NAME;
        $sys->log_text = sys_log_tests::T2_LOG_TEXT;
        $sys->log_trace = sys_log_tests::T2_LOG_TRACE;
        $sys->function_name = sys_log_tests::T2_FUNC_NAME;
        $sys->solver_name = sys_log_tests::TV_SOLVE_ID;
        $sys->status_name = $sys_log_sta_cac->id(sys_log_statuus::CLOSED);
        return $sys;
    }

    /**
     * @return job a batch job entry with some dummy values
     */
    function job(): job
    {
        $sys_usr = $this->system_user();
        $job = new job($sys_usr, new DateTime(sys_log_tests::TV_TIME));
        $job->set_id(1);
        $job->start_time = new DateTime(sys_log_tests::TV_TIME);
        $job->set_type(job_type_list::BASE_IMPORT);
        $job->row_id = 1;
        return $job;
    }

    /**
     * @return change_log_list a list of change log entries with some dummy values
     *
     * TODO add at least one sample for rename and delete
     * TODO add at least one sample for verb, triple, value, formula, source, ref, view and component
     */
    function change_log_list_named(): change_log_list
    {
        $log_lst = new change_log_list();
        $log_lst->add($this->change_log_named());
        return $log_lst;
    }

    /**
     * @return sys_log_list a list of system error entries with some dummy values
     */
    function sys_log_list(): sys_log_list
    {
        $sys_lst = new sys_log_list();
        $sys_lst->add($this->sys_log());
        $sys_lst->add($this->sys_log2());
        return $sys_lst;
    }

    /**
     * @return job_list a list of batch job entries with some dummy values
     */
    function job_list(): job_list
    {
        $sys_usr = $this->system_user();
        $job_lst = new job_list($sys_usr);
        $job_lst->add($this->job());
        return $job_lst;
    }

    /**
     * @return user the system user for the database updates
     */
    function system_user(): user
    {
        $sys_usr = new user;
        $sys_usr->set_id(SYSTEM_USER_ID);
        $sys_usr->name = "zukunft.com system";
        $sys_usr->code_id = 'system';
        $sys_usr->dec_point = ".";
        $sys_usr->thousand_sep = "'";
        $sys_usr->percent_decimals = 2;
        $sys_usr->profile_id = 5;
        return $sys_usr;
    }


    /**
     * set the all values of the frontend object based on a backend object using the api object
     * @param object $model_obj the frontend object with the values of the backend object
     */
    function dsp_obj(object $model_obj, object $dsp_obj, bool $do_save = true): object
    {
        $api_json = $model_obj->api_json();
        $dsp_obj->set_from_json($api_json);
        return $dsp_obj;
    }


    /*
     * word
     */

    /**
     * create a new word e.g. for unit testing with a given type
     *
     * @param string $wrd_name the name of the word that should be created
     * @param int $id to force setting the id for unit testing
     * @param string|null $wrd_type_code_id the id of the predefined word type which the new word should have
     * @param user|null $test_usr if not null the user for whom the word should be created to test the user sandbox
     * @return word the created word object
     */
    function new_word(string $wrd_name, int $id = 0, ?string $wrd_type_code_id = null, ?user $test_usr = null): word
    {
        if ($id == null) {
            $id = $this->next_seq_nbr();
        }
        if ($test_usr == null) {
            $test_usr = $this->usr1;
        }

        $wrd = new word($test_usr);
        $wrd->set_id($id);
        $wrd->set_name($wrd_name);

        if ($wrd_type_code_id != null) {
            $wrd->set_type($wrd_type_code_id);
        }
        return $wrd;
    }

    /**
     * load a word from the database
     *
     * @param string $wrd_name the name of the word which should be loaded
     * @param user|null $test_usr if not null the user for whom the word should be created to test the user sandbox
     * @return word the word loaded from the database by name
     */
    function load_word(string $wrd_name, ?user $test_usr = null): word
    {
        if ($test_usr == null) {
            $test_usr = $this->usr1;
        }
        $wrd = new word($test_usr);
        $wrd->load_by_name($wrd_name);
        return $wrd;
    }

    /**
     * create and fill word object without using the database
     *
     * @param string $wrd_name the name of the word which should be loaded
     * @param user|null $test_usr if not null the user for whom the word should be created to test the user sandbox
     * @return word the word with the name set
     */
    function create_word(string $wrd_name, ?user $test_usr = null): word
    {
        if ($test_usr == null) {
            $test_usr = $this->usr1;
        }
        $wrd = new word($test_usr);
        $wrd->set_name($wrd_name);
        return $wrd;
    }

    /**
     * save the just created word object in the database
     *
     * @param string $wrd_name the name of the word which should be loaded
     * @param string|null $wrd_type_code_id the id of the predefined word type which the new word should have
     * @param user|null $test_usr if not null the user for whom the word should be created to test the user sandbox
     * @return word the word that is saved in the database by name
     */
    function add_word(string $wrd_name, ?string $wrd_type_code_id = null, ?user $test_usr = null): word
    {
        global $phr_typ_cac;
        $wrd = $this->load_word($wrd_name, $test_usr);
        if ($wrd->id() == 0) {
            $wrd->set_name($wrd_name);
            $result = $wrd->save()->get_last_message();
            if ($result != '') {
                log_err('add formula failed due to: ' . $result);
            }
        }
        if ($wrd->id() <= 0) {
            log_err('Cannot create word ' . $wrd_name);
        }
        if ($wrd_type_code_id != null) {
            $wrd->type_id = $phr_typ_cac->id($wrd_type_code_id);
            $result = $wrd->save()->get_last_message();
            if ($result != '') {
                log_err('add formula failed due to: ' . $result);
            }
        }
        return $wrd;
    }

    /**
     * check if a word object could have been added to the database
     *
     * @param string $wrd_name the name of the word which should be loaded
     * @param string|null $wrd_type_code_id the id of the predefined word type which the new word should have
     * @param user|null $test_usr if not null the user for whom the word should be created to test the user sandbox
     * @return word the word that is saved in the database by name
     */
    function test_word(string $wrd_name, ?string $wrd_type_code_id = null, ?user $test_usr = null): word
    {
        $wrd = $this->add_word($wrd_name, $wrd_type_code_id, $test_usr);
        $this->assert('add_word', $wrd->name(), $wrd_name);
        return $wrd;
    }

    /**
     * check if an object could have been added to the database
     * TODO deprecate and replace with asser_write_sandbox
     *
     * @param sandbox $sbx the filled sandbox object that should be created or updated in the database
     * @param user|null $test_usr if not null the user for whom the word should be created to test the user sandbox
     * @return bool true if the object has been created of updated
     */
    function assert_db_sandbox_object(sandbox $sbx, ?user $test_usr = null): bool
    {
        $test_name = 'db ';
        $result = '';
        $target = '';
        $db_obj = clone $sbx;
        $db_obj->reset();
        if ($sbx->is_named_obj()) {
            $target = $sbx->name();
            $test_name .= $target;
            if ($db_obj->load_by_name($sbx->name())) {
                if ($sbx->id() == 0) {
                    $sbx->set_id($db_obj->id());
                    $sbx->save();
                    $test_name .= ' update ';
                } elseif ($sbx->id() == $db_obj->id()) {
                    $sbx->save();
                    $test_name .= ' update ';
                } else {
                    log_err($sbx::class . ' has id ' . $db_obj->id() . ' in the database but not yet supported by assert_db_sandbox_object');
                }
            } else {
                $test_name .= ' add ';
                $sbx->save();
            }
        } else {
            log_err($sbx::class . ' not yet supported by assert_db_sandbox_object');
        }
        $test_name .= ' of ' . $sbx::class . ' ' . $target;
        $db_obj->reset();
        if ($db_obj->load_by_id($sbx->id())) {
            $target = $db_obj->name();
        }
        return $this->assert($test_name, $result, $target);
    }

    /*
     * triple test creation
     */

    /**
     * create a new word e.g. for unit testing with a given type
     *
     * @param string $wrd_name the given name of the triple that should be created
     * @param string $from_name the name of the child word e.g. zurich
     * @param string $verb_code_id the code id of the child to parent relation e.g. is a
     * @param string $to_name the name of the parent word e.g. city
     * @param int|null $id t force setting the id for unit testing
     * @param string|null $wrd_type_code_id the id of the predefined word type which the new word should have
     * @param user|null $test_usr if not null the user for whom the word should be created to test the user sandbox
     * @return triple the created triple object
     */
    function new_triple(string  $wrd_name,
                        string  $from_name,
                        string  $verb_code_id,
                        string  $to_name,
                        int     $id = 0,
                        ?string $wrd_type_code_id = null,
                        ?user   $test_usr = null): triple
    {
        global $vrb_cac;
        global $phr_typ_cac;

        if ($id == null) {
            $id = $this->next_seq_nbr();
        }
        if ($test_usr == null) {
            $test_usr = $this->usr1;
        }

        $trp = new triple($test_usr);
        $trp->set_id($id);
        $trp->set_from($this->new_word($from_name)->phrase());
        $trp->set_verb_id($vrb_cac->id($verb_code_id));
        $trp->set_to($this->new_word($to_name)->phrase());
        $trp->set_name($wrd_name);

        if ($wrd_type_code_id != null) {
            $trp->type_id = $phr_typ_cac->id($wrd_type_code_id);
        }
        return $trp;
    }

    /**
     * load a triple by the linked phrase ids without creating it
     * @param string $from_name the name of child phrase
     * @param string $verb_code_id the code id of the predicate
     * @param string $to_name the name of parent phrase
     * @return triple
     */
    function load_triple(string $from_name,
                         string $verb_code_id,
                         string $to_name): triple
    {
        global $vrb_cac;

        $wrd_from = $this->load_word($from_name, $this->usr1);
        $wrd_to = $this->load_word($to_name, $this->usr1);
        $from = $wrd_from->phrase();
        $to = $wrd_to->phrase();

        $vrb = $vrb_cac->get_verb($verb_code_id);

        $lnk_test = new triple($this->usr1);
        if ($from->id() > 0 and $to->id() > 0) {
            // check if the forward link exists
            $lnk_test->load_by_link_id($from->id(), $vrb->id(), $to->id());
        }
        return $lnk_test;
    }

    function create_triple(
        string $from_name,
        string $verb_code_id,
        string $to_name,
        ?user  $test_usr = null): triple
    {
        global $vrb_cac;

        if ($test_usr == null) {
            $test_usr = $this->usr1;
        }

        $wrd_from = $this->create_word($from_name);
        $wrd_to = $this->create_word($to_name);
        $from = $wrd_from->phrase();
        $to = $wrd_to->phrase();

        $vrb = $vrb_cac->get_verb($verb_code_id);

        $lnk_test = new triple($test_usr);
        $lnk_test->set_from($from);
        $lnk_test->set_verb($vrb);
        $lnk_test->set_to($to);
        return $lnk_test;
    }

    /**
     * check if a triple exists and if not create it if requested
     * @param string $from_name a phrase name
     * @param string $to_name a phrase name
     * @param string $target the expected name of the triple
     * @param string $name_given the name that the triple should be set to
     * @param bool $auto_create if true the related words should be created if the phrase does not exist
     * @return triple the loaded or created triple
     */
    function test_triple(string $from_name,
                         string $verb_code_id,
                         string $to_name,
                         string $target = '',
                         string $name_given = '',
                         bool   $auto_create = true): triple
    {
        global $vrb_cac;

        $result = new triple($this->usr1);

        // load the phrases to link and create words if needed
        $from = $this->load_phrase($from_name);
        if ($from->id() == 0 and $auto_create) {
            $from = $this->add_word($from_name)->phrase();
        }
        if ($from->id() == 0) {
            log_err('Cannot get phrase ' . $from_name);
        }
        $to = $this->load_phrase($to_name);
        if ($to->id() == 0 and $auto_create) {
            $to = $this->add_word($to_name)->phrase();
        }
        if ($to->id() == 0) {
            log_err('Cannot get phrase ' . $to_name);
        }

        // load the verb
        $vrb = $vrb_cac->get_verb($verb_code_id);

        // check if the triple exists or create a new if needed
        $trp = new triple($this->usr1);
        if ($vrb == null) {
            log_err("Phrases " . $from_name . " and " . $to_name . " cannot be created");
        } else {
            if ($from->id() == 0 or $vrb->id() == 0 or $to->id() == 0) {
                log_err("Phrases " . $from_name . " and " . $to_name . " cannot be created");
            } else {
                // check if the forward link exists
                $trp->load_by_link_id($from->id(), $vrb->id(), $to->id());
                if ($trp->id() > 0) {
                    // refresh the given name if needed
                    if ($name_given <> '' and $trp->name(true) <> $name_given) {
                        $trp->set_name_given($name_given);
                        $trp->set_name($name_given);
                        $result = $trp->save()->get_last_message();
                        if ($result != '') {
                            log_err('save tripple failed due to: ' . $result);
                        }
                        $trp->load_by_id($trp->id());
                    }
                    $result = $trp;
                } else {
                    // check if the backward link exists
                    $trp->set_from($to);
                    $trp->set_verb($vrb);
                    $trp->set_to($from);
                    $trp->set_user($this->usr1);
                    $trp->load_by_link_id($to->id(), $vrb->id(), $from->id());
                    $result = $trp;
                    // create the link if requested
                    if ($trp->id() <= 0 and $auto_create) {
                        $trp->set_from($from);
                        $trp->set_verb($vrb);
                        $trp->set_to($to);
                        if ($trp->name(true) <> $name_given) {
                            $trp->set_name_given($name_given);
                            $trp->set_name($name_given);
                        }
                        $save_result = $trp->save()->get_last_message();
                        if ($save_result != '') {
                            log_err('save tripple failed due to: ' . $save_result);
                        }
                        $trp->load_by_id($trp->id());
                    }
                }
            }
        }

        // assume the target name if not given
        $result_text = '';
        if ($trp->id() > 0) {
            $result_text = $trp->name(true);
            if ($target == '') {
                $target = $trp->name(true);
            }
        }

        $this->display('test_triple', $target, $result_text, self::TIMEOUT_LIMIT_DB);
        return $result;
    }

    function del_triple(string $from_name,
                        string $verb_code_id,
                        string $to_name): bool
    {
        $trp = $this->load_triple($from_name, $verb_code_id, $to_name);
        if ($trp->id() <> 0) {
            $trp->del();
            return true;
        } else {
            return false;
        }
    }

    function del_triple_by_name(string $name): bool
    {
        $trp = new triple($this->usr1);
        $trp->load_by_name($name);
        if ($trp->id() <> 0) {
            $trp->del();
            return true;
        } else {
            return false;
        }
    }


    /*
     * group test creation
     */

    /**
     * load a word from the database
     *
     * @param string $grp_name the name of the group which should be loaded
     * @param user|null $test_usr if not null the user for whom the word should be created to test the user sandbox
     * @return group the group loaded from the database by name
     */
    function load_group(string $grp_name, ?user $test_usr = null): group
    {
        if ($test_usr == null) {
            $test_usr = $this->usr1;
        }
        $grp = new group($test_usr);
        $grp->load_by_name($grp_name);
        return $grp;
    }

    /**
     * create group object based on the phrase list without using the database
     *
     * @param phrase_list $phr_lst with the phrases to identify the group
     * @param user|null $test_usr if not null the user for whom the word should be created to test the user sandbox
     * @return group the word with the name set
     */
    function create_group(phrase_list $phr_lst, ?user $test_usr = null): group
    {
        if ($test_usr == null) {
            $test_usr = $this->usr1;
        }
        $grp = new group($test_usr);
        $grp->set_phrase_list($phr_lst);
        return $grp;
    }

    /**
     * save the just created group object in the database
     *
     * @param array $phr_names with the phrases to identify the group
     * @param string $grp_name the group name that should be used
     * @param user|null $test_usr if not null the user for whom the word should be created to test the user sandbox
     * @return group the group that is saved in the database by name
     */
    function add_group(array $phr_names, string $grp_name, ?user $test_usr = null): group
    {
        if ($test_usr == null) {
            $test_usr = $this->usr1;
        }
        $grp = $this->load_group($grp_name);
        if (!$grp->is_saved()) {
            $phr_lst = new phrase_list($test_usr);
            $phr_lst->load_by_names($phr_names);
            $grp = $this->create_group($phr_lst, $test_usr);
            $grp->set_name($grp_name);
            $result = $grp->save()->get_last_message();
            if ($result != '') {
                log_err('add group failed due to: ' . $result);
            }
        }
        return $grp;
    }

    /**
     * check if a group object could have been added to the database
     *
     * @param array $phr_names with the phrases to identify the group
     * @param string $grp_name the group name that should be used
     * @param user|null $test_usr if not null the user for whom the word should be created to test the user sandbox
     * @return group the group that is saved in the database
     */
    function test_group(array $phr_names, string $grp_name, ?user $test_usr = null): group
    {
        $grp = $this->add_group($phr_names, $grp_name, $test_usr);
        $this->assert('test_group', $grp->name(), $grp_name);
        return $grp;
    }


    /*
     * formula test creation
     */

    /**
     * create a new formula e.g. for unit testing with a given type
     *
     * @param string $frm_name the name of the formula that should be created
     * @param int $id to force setting the id for unit testing
     * @param string|null $frm_type_code_id the id of the predefined formula type which the new formula should have
     * @param user|null $test_usr if not null the user for whom the formula should be created to test the user sandbox
     * @return formula the created formula object
     */
    function new_formula(string $frm_name, int $id = 0, ?string $frm_type_code_id = null, ?user $test_usr = null): formula
    {
        global $frm_typ_cac;

        if ($id == null) {
            $id = $this->next_seq_nbr();
        }
        if ($test_usr == null) {
            $test_usr = $this->usr1;
        }

        $frm = new formula($test_usr);
        $frm->set_id($id);
        $frm->set_name($frm_name);

        if ($frm_type_code_id != null) {
            $frm->type_id = $frm_typ_cac->id($frm_type_code_id);
        }
        return $frm;
    }

    function load_formula(string $frm_name): formula
    {
        $frm = new formula($this->usr1);
        $frm->load_by_name($frm_name);
        return $frm;
    }

    /**
     * get or create a formula
     */
    function add_formula(string $frm_name, string $frm_text): formula
    {
        $frm = $this->load_formula($frm_name);
        if ($frm->id() == 0) {
            $frm->set_name($frm_name);
            $frm->usr_text = $frm_text;
            $frm->generate_ref_text();
            $result = $frm->save()->get_last_message();
            // TODO add this check to all add functions
            if ($result != '') {
                log_err('add formula failed due to: ' . $result);
            }
        }
        return $frm;
    }

    function test_formula(string $frm_name, string $frm_text): formula
    {
        $frm = $this->add_formula($frm_name, $frm_text);
        $this->display('formula', $frm_name, $frm->name());
        return $frm;
    }


    /*
     * reference test creation
     */

    function load_ref(string $wrd_name, string $type_name): ref
    {

        $wrd = $this->load_word($wrd_name);
        $phr = $wrd->phrase();

        global $ref_typ_cac;
        $ref = new ref($this->usr1);
        if ($phr->id() != 0) {
            // TODO check if type name is the code id or really the name
            $ref->load_by_link_ids($phr->id(), $ref_typ_cac->id($type_name));
        }
        return $ref;
    }

    function add_ref(string $wrd_name, string $external_key, string $type_name): ref
    {
        global $ref_typ_cac;
        $wrd = $this->test_word($wrd_name);
        $phr = $wrd->phrase();
        $ref = $this->load_ref($wrd->name(), $type_name);
        if ($ref->id() == 0) {
            $ref->set_phrase($phr);
            // TODO check if type name is the code id or really the name
            $ref->set_predicate_id($ref_typ_cac->id($type_name));
            $ref->external_key = $external_key;
            $result = $ref->save()->get_last_message();
            if ($result != '') {
                log_err('add ref failed due to: ' . $result);
            }
        }
        return $ref;
    }

    function test_ref(string $wrd_name, string $external_key, string $type_name): ref
    {
        $ref = $this->add_ref($wrd_name, $external_key, $type_name);
        $target = $external_key;
        $this->display('ref', $target, $ref->external_key);
        return $ref;
    }

    function load_phrase(string $phr_name): phrase
    {
        $phr = new phrase($this->usr1);
        $phr->load_by_name($phr_name);
        $phr->load_obj();
        return $phr;
    }

    /**
     * test if a phrase with the given name exists, but does not create it, if it has not yet been created
     * @param string $phr_name name of the phrase to test
     * @return phrase the loaded phrase object
     */
    function test_phrase(string $phr_name): phrase
    {
        $phr = $this->load_phrase($phr_name);
        $this->display('phrase', $phr_name, $phr->name(true));
        return $phr;
    }

    /**
     * create a phrase list object based on an array of strings
     */
    function load_word_list(array $array_of_word_str): word_list
    {
        $wrd_lst = new word_list($this->usr1);
        $wrd_lst->load_by_names($array_of_word_str);
        return $wrd_lst;
    }

    function test_word_list(array $array_of_word_str): word_list
    {
        $wrd_lst = $this->load_word_list($array_of_word_str);
        $target = '"' . implode('","', $array_of_word_str) . '"';
        $result = $wrd_lst->name();
        $this->display(', word list', $target, $result);
        return $wrd_lst;
    }

    /**
     * create a phrase list object based on an array of strings
     */
    function load_phrase_list(array $array_of_word_str): phrase_list
    {
        $phr_lst = new phrase_list($this->usr1);
        $phr_lst->load_by_names($array_of_word_str);
        return $phr_lst;
    }

    function test_phrase_list(array $array_of_word_str): phrase_list
    {
        $phr_lst = $this->load_phrase_list($array_of_word_str);
        $target = '"' . implode('","', $array_of_word_str) . '"';
        $result = $phr_lst->dsp_name();
        $this->display(', phrase list', $target, $result);
        return $phr_lst;
    }

    /**
     * load a phrase group by the list of phrase names
     * @param array $array_of_phrase_str with the names of the words or triples
     * @return group|null
     */
    function load_phrase_group(array $array_of_phrase_str): ?group
    {
        return $this->load_phrase_list($array_of_phrase_str)->get_grp_id();
    }

    /**
     * load a phrase group by the name
     * which can be either the name set by the users
     * or the automatically created name based on the phrases
     * @param string $phrase_group_name
     * @return group
     */
    function load_phrase_group_by_name(string $phrase_group_name): group
    {
        $phr_grp = new group($this->usr1);
        $phr_grp->name = $phrase_group_name;
        $phr_grp->load_by_obj_vars();
        return $phr_grp;
    }

    /**
     * add a phrase group to the database
     * @param array $array_of_phrase_str the phrase names
     * @param string $name the name that should be shown to the user
     * @return group the phrase group object including the database is
     */
    function add_phrase_group(array $array_of_phrase_str, string $name): group
    {
        $grp = new group($this->usr1);
        $grp->get_by_phrase_list($this->load_phrase_list($array_of_phrase_str), $name);
        return $grp;
    }

    /**
     * delete a phrase group from the database
     * @param string $phrase_group_name the name that should be shown to the user
     * @return bool true if the phrase group has been deleted
     */
    function del_phrase_group(string $phrase_group_name): bool
    {
        $phr_grp = $this->load_phrase_group_by_name($phrase_group_name);
        if ($phr_grp->del()) {
            return true;
        } else {
            return false;
        }
    }

    function load_value_by_id(user $usr, int $id): value
    {
        $val = new value($this->usr1);
        $val->load_by_id($id);
        return $val;
    }

    function load_value(array $array_of_word_str): value
    {

        // the time separation is done here until there is a phrase series value table that can be used also to time phrases
        $phr_lst = $this->load_phrase_list($array_of_word_str);
        $phr_grp = $phr_lst->get_grp_id();

        $val = new value($this->usr1);
        if ($phr_grp == null) {
            log_err('Cannot get phrase group for ' . $phr_lst->dsp_id());
        } else {
            $val->load_by_grp($phr_grp);
        }
        return $val;
    }

    function add_value(array $array_of_word_str, float $target): value
    {
        $val = $this->load_value($array_of_word_str);
        if (!$val->is_saved()) {
            $phr_lst = $this->load_phrase_list($array_of_word_str);
            $phr_grp = $phr_lst->get_grp_id();

            // getting the latest value if selected without time phrase should be done when reading the value
            //$time_phr = $phr_lst->time_useful();
            //$phr_lst->ex_time();

            $val = new value($this->usr1);
            if ($phr_grp == null) {
                log_err('Cannot get phrase group for ' . $phr_lst->dsp_id());
            } else {
                $val->set_grp($phr_grp);
            }
            $val->set_number($target);
            $result = $val->save()->get_last_message();
            if ($result != '') {
                log_err('add value failed due to: ' . $result);
            }
        }

        return $val;
    }

    function test_value(array $array_of_word_str, float $target): value
    {
        $val = $this->add_value($array_of_word_str, $target);
        $result = $val->number();
        $this->display(', value->load for ' . $val->name(), $target, $result);
        return $val;
    }

    function load_value_by_phr_grp(group $phr_grp): value
    {
        $val = new value($this->usr1);
        $val->load_by_grp($phr_grp);
        return $val;
    }

    function add_value_by_phr_grp(group $phr_grp, float $target): value
    {
        $val = $this->load_value_by_phr_grp($phr_grp);
        if (!$val->is_saved()) {
            $val->set_grp($phr_grp);
            $val->set_number($target);
            $result = $val->save()->get_last_message();
            if ($result != '') {
                log_err('add value by group failed due to: ' . $result);
            }
        }

        return $val;
    }

    function test_value_by_phr_grp(group $phr_grp, float $target): value
    {
        $val = $this->add_value_by_phr_grp($phr_grp, $target);
        $result = $val->number();
        $this->display(', value->load for ' . $val->name(), $target, $result);
        return $val;
    }

    function del_value_by_phr_grp(group $phr_grp): bool
    {
        $val = $this->load_value_by_phr_grp($phr_grp);
        if ($val->del()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * create a new verb e.g. for unit testing with a given type
     *
     * @param string $vrb_name the name of the verb that should be created
     * @param int $id to force setting the id for unit testing
     * @return verb the created verb object
     */
    function new_verb(string $vrb_name, int $id = 0): verb
    {
        if ($id == null) {
            $id = $this->next_seq_nbr();
        }

        $vrb = new verb();
        $vrb->set_id($id);
        $vrb->set_name($vrb_name);
        $vrb->set_user($this->usr1);

        return $vrb;
    }


    /*
     * source test creation
     */

    function load_source(string $src_name): source
    {
        $src = new source($this->usr1);
        $src->load_by_name($src_name);
        return $src;
    }

    function add_source(string $src_name): source
    {
        $src = $this->load_source($src_name);
        if ($src->id() == 0) {
            $src->set_name($src_name);
            $result = $src->save()->get_last_message();
            if ($result != '') {
                log_err('add source failed due to: ' . $result);
            }
        }
        return $src;
    }

    function test_source(string $src_name): source
    {
        $src = $this->add_source($src_name);
        $this->display('source', $src_name, $src->name());
        return $src;
    }

    /**
     * @return array json message to test if adding a new word via the api works fine
     */
    function word_put_json(): array
    {
        global $db_con;
        global $phr_typ_cac;
        $msg = new api_message();
        $wrd = new word($this->usr1);
        $wrd->set_name(words::TN_ADD_API);
        $wrd->description = words::TD_ADD_API;
        $wrd->type_id = $phr_typ_cac->id(phrase_type_shared::NORMAL);
        $body_array = $wrd->api_json_array(new api_type_list([]));
        return $msg->api_header_array($db_con, word::class, $this->usr1, $body_array);
    }

    /**
     * @return array json message to test if updating of a word via the api works fine
     */
    function word_post_json(): array
    {
        global $db_con;
        $msg = new api_message();
        $wrd = new word($this->usr1);
        $wrd->set_name(words::TN_UPD_API);
        $wrd->description = words::TD_UPD_API;
        $body_array = $wrd->api_json_array(new api_type_list([]));
        return $msg->api_header_array($db_con, word::class, $this->usr1, $body_array);
    }

    /**
     * @return array json message to test if adding a new source via the api works fine
     */
    function source_put_json(): array
    {
        global $db_con;
        global $src_typ_cac;
        $msg = new api_message();
        $src = new source($this->usr1);
        $src->set_name(sources::SYSTEM_TEST_ADD_API);
        $src->description = sources::SYSTEM_TEST_ADD_API_COM;
        $src->url = sources::SYSTEM_TEST_ADD_API_URL;
        $src->type_id = $src_typ_cac->id(source_type::PDF);
        $body_array = $src->api_json_array(new api_type_list([]));
        return $msg->api_header_array($db_con, source::class, $this->usr1, $body_array);
    }

    /**
     * @return array json message to test if updating of a source via the api works fine
     */
    function source_post_json(): array
    {
        global $db_con;
        $msg = new api_message();
        $src = new source($this->usr1);
        $src->set_name(sources::SYSTEM_TEST_UPD_API);
        $src->description = sources::SYSTEM_TEST_UPD_API_COM;
        $body_array = $src->api_json_array(new api_type_list([]));
        return $msg->api_header_array($db_con, source::class, $this->usr1, $body_array);
    }

    /**
     * @return array json message to test if adding a new reference via the api works fine
     */
    function reference_put_json(): array
    {
        global $db_con;
        global $reference_types;
        $msg = new api_message();
        $ref = new ref($this->usr1);
        $ref->set_phrase($this->word()->phrase());
        $ref->external_key = refs::SYSTEM_TEST_API_ADD_KEY;
        $ref->description = refs::SYSTEM_TEST_API_ADD_COM;
        $ref->url = refs::SYSTEM_TEST_API_ADD_URL;
        $ref->predicate_id = $reference_types->id(source_type::PDF);
        $body_array = $ref->api_json_array(new api_type_list([]));
        return $msg->api_header_array($db_con, ref::class, $this->usr1, $body_array);
    }

    /*
     * view test creation
     */

    /**
     * load a view and if the test user is set for a specific user
     */
    function load_view(string $dsp_name, ?user $test_usr = null): view
    {
        if ($test_usr == null) {
            $test_usr = $this->usr1;
        }

        $msk = new view($test_usr);
        $msk->load_by_name($dsp_name);
        return $msk;
    }

    function add_view(string $dsp_name, user $test_usr): view
    {
        $msk = $this->load_view($dsp_name, $test_usr);
        if ($msk->id() == 0) {
            $msk->set_user($test_usr);
            $msk->set_name($dsp_name);
            $result = $msk->save()->get_last_message();
            if ($result != '') {
                log_err('add view failed due to: ' . $result);
            }
        }
        return $msk;
    }

    function test_view(string $dsp_name, ?user $test_usr = null): view
    {
        if ($test_usr == null) {
            $test_usr = $this->usr1;
        }

        $msk = $this->add_view($dsp_name, $test_usr);
        $this->display('view', $dsp_name, $msk->name(), test_base::TIMEOUT_LIMIT_DB);
        return $msk;
    }

    function del_view(string $dsp_name, ?user $test_usr = null): view
    {
        if ($test_usr == null) {
            $test_usr = $this->usr1;
        }

        $msk = $this->load_view($dsp_name, $test_usr);
        if ($msk->id() != 0) {
            $msk->del_links();
            $msk->del();
        }
        return $msk;
    }


    /*
     * component test creation
     */

    function load_component(string $cmp_name, ?user $test_usr = null): component
    {
        if ($test_usr == null) {
            $test_usr = $this->usr1;
        }

        $cmp = new component($test_usr);
        $cmp->load_by_name($cmp_name);
        return $cmp;
    }

    function add_component(string $cmp_name, user $test_usr, string $type_code_id = ''): component
    {
        global $cmp_typ_cac;

        $cmp = $this->load_component($cmp_name, $test_usr);
        if ($cmp->id() == 0 or $cmp->id() == Null) {
            $cmp->set_user($test_usr);
            $cmp->set_name($cmp_name);
            if ($type_code_id != '') {
                $cmp->type_id = $cmp_typ_cac->id($type_code_id);
            }
            $result = $cmp->save()->get_last_message();
            if ($result != '') {
                log_err('add component failed due to: ' . $result);
            }
        }
        return $cmp;
    }

    function test_component(string $cmp_name, string $type_code_id = '', ?user $test_usr = null): component
    {
        if ($test_usr == null) {
            $test_usr = $this->usr1;
        }

        $cmp = $this->add_component($cmp_name, $test_usr, $type_code_id);
        $this->display('view component', $cmp_name, $cmp->name());
        return $cmp;
    }

    function test_component_lnk(string $dsp_name, string $cmp_name, int $pos): component_link
    {
        $msk = $this->load_view($dsp_name);
        $cmp = $this->load_component($cmp_name);
        $lnk = new component_link($this->usr1);
        $lnk->reset();
        $lnk->set_view($msk);
        $lnk->set_component($cmp);
        $lnk->order_nbr = $pos;
        $result = $lnk->save()->get_last_message();
        $target = '';
        $this->display('view component link', $target, $result);
        return $lnk;
    }

    function test_component_unlink(string $dsp_name, string $cmp_name): string
    {
        $result = '';
        $msk = $this->load_view($dsp_name);
        $cmp = $this->load_component($cmp_name);
        if ($msk->id() > 0 and $cmp->id() > 0) {
            $result = $cmp->unlink($msk);
        }
        return $result;
    }

    function test_formula_link(string $formula_name, string $word_name, bool $autocreate = true): string
    {
        $result = '';

        $frm = new formula($this->usr1);
        $frm->load_by_name($formula_name);
        $wrd = new word($this->usr1);
        $wrd->load_by_name($word_name);
        if ($frm->id() > 0 and $wrd->id() <> 0) {
            $frm_lnk = new formula_link($this->usr1);
            $frm_lnk->load_by_link($frm, $wrd->phrase());
            if ($frm_lnk->id() > 0) {
                $result = $frm_lnk->formula()->name() . ' is linked to ' . $frm_lnk->phrase()->name();
                $target = $formula_name . ' is linked to ' . $word_name;
                $this->display('formula_link', $target, $result);
            } else {
                if ($autocreate) {
                    $frm_lnk->set_formula($frm);
                    $frm_lnk->set_phrase($wrd->phrase());
                    $result = $frm_lnk->save()->get_last_message();
                    if ($result != '') {
                        log_err('add formula link failed due to: ' . $result);
                    }
                }
            }
        }
        return $result;
    }

    /**
     * create all database entries used for the read db unit tests
     * the created database rows can be accessed by the users but are not expected to be changed and cannot be changed
     *
     * @param all_tests $t the test object to collect the errors and calculate the execution times
     * @return void
     */
    function create_test_db_entries(all_tests $t): void
    {
        (new word_write_tests())->create_test_words($t);
        (new triple_write_tests())->create_test_triples($t);
        (new triple_write_tests())->create_base_times($t);
        (new group_write_tests())->create_test_groups($t);
        (new source_write_tests())->create_test_sources($t);
        (new formula_write_tests())->create_test_formulas($t);
        (new formula_link_write_tests())->create_test_formula_links($t);
        (new view_write_tests())->create_test_views($t);
        // (new view_link_write_tests())->create_test_views($t);
        (new component_write_tests())->create_test_components($t);
        (new component_link_write_tests())->create_test_component_links($t);
        (new value_write_tests())->create_test_values($t);
    }

}