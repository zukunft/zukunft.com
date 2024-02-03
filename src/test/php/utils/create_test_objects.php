<?php

/*

    test/utils/create_test_objects.php - create the standard object for testing
    ----------------------------------

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

include_once API_REF_PATH . 'ref.php';
include_once API_PHRASE_PATH . 'group.php';
include_once MODEL_PHRASE_PATH . 'phrase.php';
include_once MODEL_PHRASE_PATH . 'term.php';
include_once MODEL_COMPONENT_PATH . 'component.php';
include_once MODEL_COMPONENT_PATH . 'component_list.php';
include_once WEB_FORMULA_PATH . 'formula.php';

use api\api;
use api\component\component as component_api;
use api\formula\formula as formula_api;
use api\phrase\group as group_api;
use api\ref\ref as ref_api;
use api\result\result as result_api;
use api\ref\source as source_api;
use api\word\triple as triple_api;
use api\system\type_lists as type_lists_api;
use api\value\value as value_api;
use api\verb\verb as verb_api;
use api\view\view as view_api;
use api\word\word as word_api;
use api\api_message;
use cfg\batch_job;
use cfg\batch_job_list;
use cfg\batch_job_type_list;
use cfg\log\change_log_action;
use cfg\log\change_log_field;
use cfg\log\change_log_list;
use cfg\log\change;
use cfg\log\change_log_table;
use cfg\component\component;
use cfg\component\component_list;
use cfg\component\component_pos_type_list;
use cfg\component\component_type;
use cfg\component\component_type_list;
use cfg\component_link;
use cfg\component_link_list;
use cfg\expression;
use cfg\figure;
use cfg\figure_list;
use cfg\formula;
use cfg\formula_element;
use cfg\formula_element_list;
use cfg\formula_element_type_list;
use cfg\formula_link;
use cfg\formula_link_type_list;
use cfg\formula_list;
use cfg\formula_type;
use cfg\formula_type_list;
use cfg\group\group;
use cfg\group\group_list;
use cfg\language;
use cfg\language_form_list;
use cfg\language_list;
use cfg\library;
use cfg\log\system_log;
use cfg\log\system_log_list;
use cfg\phrase;
use cfg\phrase_list;
use cfg\phrase_type;
use cfg\phrase_types;
use cfg\protection_type_list;
use cfg\ref;
use cfg\ref_type_list;
use cfg\result\result;
use cfg\result\result_list;
use cfg\share_type_list;
use cfg\source;
use cfg\source_type;
use cfg\source_type_list;
use cfg\sys_log_status;
use cfg\term;
use cfg\term_list;
use cfg\triple;
use cfg\triple_list;
use cfg\type_list;
use cfg\type_object;
use cfg\user;
use cfg\user_profile_list;
use cfg\value\value;
use cfg\value\value_list;
use cfg\value\value_phrase_link;
use cfg\verb;
use cfg\verb_list;
use cfg\view;
use cfg\view_list;
use cfg\view_type_list;
use cfg\word;
use cfg\word_list;
use controller\controller;
use api\log\system_log as system_log_api;
use DateTime;
use html\phrase\phrase_list as phrase_list_dsp;
use html\word\word as word_dsp;
use test\write\component_link_test;
use test\write\component_test;
use test\write\formula_link_test;
use test\write\formula_test;
use test\write\source_test;
use test\write\triple_test;
use test\write\value_test;
use test\write\view_test;
use test\write\word_test;

class create_test_objects extends test_base
{

    const DUMMY_DATETIME = '2022-12-26T18:23:45+01:00';

    /*
     * dummy objects for unit tests
     */

    function dummy_type_lists_api(user $usr): type_lists_api
    {
        global $db_con;

        $user_profiles = new user_profile_list();
        $phrase_types = new phrase_types();
        $formula_types = new formula_type_list();
        $formula_link_types = new formula_link_type_list();
        $formula_element_types = new formula_element_type_list();
        $view_types = new view_type_list();
        $component_types = new component_type_list();
        //$component_link_types = new component_link_type_list();
        $component_position_types = new component_pos_type_list();
        $ref_types = new ref_type_list();
        $source_types = new source_type_list();
        $share_types = new share_type_list();
        $protection_types = new protection_type_list();
        $languages = new language_list();
        $language_forms = new language_form_list();
        $sys_log_stati = new sys_log_status();
        $job_types = new batch_job_type_list();
        $change_log_actions = new change_log_action();
        $change_log_tables = new change_log_table();
        $change_log_fields = new change_log_field();
        $verbs = new verb_list();

        $user_profiles->load_dummy();
        $phrase_types->load_dummy();
        $formula_types->load_dummy();
        $formula_link_types->load_dummy();
        $formula_element_types->load_dummy();
        $view_types->load_dummy();
        $component_types->load_dummy();
        //$component_link_types->load_dummy();
        $component_position_types->load_dummy();
        $ref_types->load_dummy();
        $source_types->load_dummy();
        $share_types->load_dummy();
        $protection_types->load_dummy();
        $languages->load_dummy();
        $language_forms->load_dummy();
        $sys_log_stati->load_dummy();
        $job_types->load_dummy();
        $change_log_actions->load_dummy();
        $change_log_tables->load_dummy();
        $change_log_fields->load_dummy();
        $verbs->load_dummy();

        // read the corresponding names and description from the internal config csv files
        $this->read_name_from_config_csv($phrase_types);

        $lst = new type_lists_api($db_con, $usr);
        $lst->add($user_profiles->api_obj(), controller::API_LIST_USER_PROFILES);
        $lst->add($phrase_types->api_obj(), controller::API_LIST_PHRASE_TYPES);
        $lst->add($formula_types->api_obj(), controller::API_LIST_FORMULA_TYPES);
        $lst->add($formula_link_types->api_obj(), controller::API_LIST_FORMULA_LINK_TYPES);
        $lst->add($formula_element_types->api_obj(), controller::API_LIST_FORMULA_ELEMENT_TYPES);
        $lst->add($view_types->api_obj(), controller::API_LIST_VIEW_TYPES);
        $lst->add($component_types->api_obj(), controller::API_LIST_COMPONENT_TYPES);
        //$lst->add($component_link_types->api_obj(), controller::API_LIST_VIEW_COMPONENT_LINK_TYPES);
        $lst->add($component_position_types->api_obj(), controller::API_LIST_COMPONENT_POSITION_TYPES);
        $lst->add($ref_types->api_obj(), controller::API_LIST_REF_TYPES);
        $lst->add($source_types->api_obj(), controller::API_LIST_SOURCE_TYPES);
        $lst->add($share_types->api_obj(), controller::API_LIST_SHARE_TYPES);
        $lst->add($protection_types->api_obj(), controller::API_LIST_PROTECTION_TYPES);
        $lst->add($languages->api_obj(), controller::API_LIST_LANGUAGES);
        $lst->add($language_forms->api_obj(), controller::API_LIST_LANGUAGE_FORMS);
        $lst->add($sys_log_stati->api_obj(), controller::API_LIST_SYS_LOG_STATI);
        $lst->add($job_types->api_obj(), controller::API_LIST_JOB_TYPES);
        $lst->add($change_log_actions->api_obj(), controller::API_LIST_CHANGE_LOG_ACTIONS);
        $lst->add($change_log_tables->api_obj(), controller::API_LIST_CHANGE_LOG_TABLES);
        $lst->add($change_log_fields->api_obj(), controller::API_LIST_CHANGE_LOG_FIELDS);
        $lst->add($verbs->api_obj(), controller::API_LIST_VERBS);

        $system_views = $this->dummy_view_list();
        $lst->add($system_views->api_obj(), controller::API_LIST_SYSTEM_VIEWS);

        return $lst;
    }

    private function read_name_from_config_csv(type_list $list): bool
    {
        $result = false;

        $lib = new library();
        $type = $lib->class_to_name($list::class);

        // get the list of CSV and loop
        $csv_file_list = unserialize(BASE_CODE_LINK_FILES);
        foreach ($csv_file_list as $csv_file_name) {
            if ($csv_file_name == $type) {
                // load the csv
                $csv_path = PATH_BASE_CODE_LINK_FILES . $csv_file_name . BASE_CODE_LINK_FILE_TYPE;
                $row = 1;
                $code_id_col = 0;
                $name_col = 0;
                $desc_col = 0;
                if (($handle = fopen($csv_path, "r")) !== FALSE) {
                    while (($data = fgetcsv($handle, 0, ",", "'")) !== FALSE) {
                        if ($row == 1) {
                            $col_names = $lib->array_trim($data);
                            if (in_array(api::FLD_CODE_ID, $col_names)) {
                                $code_id_col = array_search(api::FLD_CODE_ID, $col_names);
                            }
                            if (in_array(type_object::FLD_NAME, $col_names)) {
                                $name_col = array_search(type_object::FLD_NAME, $col_names);
                            }
                            if (in_array(api::FLD_DESCRIPTION, $col_names)) {
                                $desc_col = array_search(api::FLD_DESCRIPTION, $col_names);
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
                                    $typ_obj->set_comment($data[$desc_col]);
                                }
                            }
                        }
                        $row++;
                    }
                    fclose($handle);
                }
                $result = true;
            }
        }

        return $result;
    }

    /**
     * @return user the user used for unit testing
     */
    function dummy_user(): user
    {
        $usr = new user();
        $usr->set(2, user::SYSTEM_TEST_NAME, user::SYSTEM_TEST_EMAIL);
        return $usr;
    }

    /**
     * @return word "Mathematics" as the main word for unit testing
     */
    function dummy_word(): word
    {
        $wrd = new word($this->usr1);
        $wrd->set(word_api::TI_MATH, word_api::TN_READ);
        $wrd->description = word_api::TD_READ;
        $wrd->set_type(phrase_type::NORMAL);
        return $wrd;
    }

    /**
     * @return word_dsp the word "Mathematics" for frontend unit testing
     */
    function dummy_word_dsp(): word_dsp
    {
        $wrd = $this->dummy_word();
        return new word_dsp($wrd->api_json());
    }

    /**
     * @return word "constant" to create the main triple for unit testing
     */
    function dummy_word_const(): word
    {
        $wrd = new word($this->usr1);
        $wrd->set(word_api::TI_CONST, word_api::TN_CONST);
        $wrd->description = word_api::TD_CONST;
        $wrd->set_type(phrase_type::MATH_CONST);
        return $wrd;
    }

    /**
     * @return word "Pi" to test the const behavior
     */
    function dummy_word_pi(): word
    {
        $wrd = new word($this->usr1);
        $wrd->set(word_api::TI_PI, word_api::TN_PI);
        $wrd->description = word_api::TD_PI;
        $wrd->set_type(phrase_type::MATH_CONST);
        return $wrd;
    }

    /**
     * @return word "circumference" to test the const behavior
     */
    function dummy_word_cf(): word
    {
        $wrd = new word($this->usr1);
        $wrd->set(word_api::TI_CIRCUMFERENCE, word_api::TN_CIRCUMFERENCE);
        return $wrd;
    }

    /**
     * @return word "diameter" to test the const behavior
     */
    function dummy_word_dia(): word
    {
        $wrd = new word($this->usr1);
        $wrd->set(word_api::TI_DIAMETER, word_api::TN_DIAMETER);
        return $wrd;
    }

    /**
     * @return word "Euler's constant" to test the handling of >'<
     */
    function dummy_word_e(): word
    {
        $wrd = new word($this->usr1);
        $wrd->set(word_api::TI_E, word_api::TN_E);
        $wrd->set_type(phrase_type::MATH_CONST);
        return $wrd;
    }

    /**
     * @return word 2019 to test creating of a year
     */
    function dummy_word_2019(): word
    {
        $wrd = new word($this->usr1);
        $wrd->set(word_api::TI_2019, word_api::TN_2019);
        $wrd->set_type(phrase_type::TIME);
        return $wrd;
    }

    /**
     * @return word 2020 to test create a year
     */
    function dummy_word_2020(): word
    {
        $wrd = new word($this->usr1);
        $wrd->set(word_api::TI_2020, word_api::TN_2020);
        $wrd->set_type(phrase_type::TIME);
        return $wrd;
    }

    function dummy_word_pct(): word
    {
        $wrd = new word($this->usr1);
        $wrd->set(word_api::TI_PCT, word_api::TN_PCT);
        $wrd->set_type(phrase_type::PERCENT);
        return $wrd;
    }

    function dummy_word_this(): word
    {
        $wrd = new word($this->usr1);
        $wrd->set(word_api::TI_THIS, word_api::TN_THIS_PRE);
        $wrd->set_type(phrase_type::THIS);
        return $wrd;
    }

    function dummy_word_prior(): word
    {
        $wrd = new word($this->usr1);
        $wrd->set(word_api::TI_PRIOR, word_api::TN_PRIOR_PRE);
        $wrd->set_type(phrase_type::PRIOR);
        return $wrd;
    }

    function dummy_word_one(): word
    {
        $wrd = new word($this->usr1);
        $wrd->set(word_api::TI_ONE, word_api::TN_ONE);
        $wrd->set_type(phrase_type::SCALING_HIDDEN);
        return $wrd;
    }

    function dummy_word_mio(): word
    {
        $wrd = new word($this->usr1);
        $wrd->set(word_api::TI_MIO, word_api::TN_MIO_SHORT);
        $wrd->set_type(phrase_type::SCALING);
        return $wrd;
    }

    function dummy_word_minute(): word
    {
        $wrd = new word($this->usr1);
        $wrd->set(word_api::TI_MINUTE, word_api::TN_MINUTE);
        return $wrd;
    }

    function dummy_word_second(): word
    {
        $wrd = new word($this->usr1);
        $wrd->set(word_api::TI_SECOND, word_api::TN_SECOND);
        return $wrd;
    }

    function dummy_word_ch(): word
    {
        $wrd = new word($this->usr1);
        $wrd->set(word_api::TI_CH, word_api::TN_CH);
        return $wrd;
    }

    function dummy_word_canton(): word
    {
        $wrd = new word($this->usr1);
        $wrd->set(word_api::TI_CANTON, word_api::TN_CANTON);
        return $wrd;
    }

    function dummy_word_city(): word
    {
        $wrd = new word($this->usr1);
        $wrd->set(word_api::TI_CITY, word_api::TN_CITY);
        return $wrd;
    }

    function dummy_word_zh(): word
    {
        $wrd = new word($this->usr1);
        $wrd->set(word_api::TI_ZH, word_api::TN_ZH);
        return $wrd;
    }

    function dummy_word_inhabitant(): word
    {
        $wrd = new word($this->usr1);
        $wrd->set(word_api::TI_INHABITANT, word_api::TN_INHABITANT);
        return $wrd;
    }

    function dummy_word_parts(): word
    {
        $wrd = new word($this->usr1);
        $wrd->set(word_api::TI_PARTS, word_api::TN_PARTS);
        return $wrd;
    }

    function dummy_word_total(): word
    {
        $wrd = new word($this->usr1);
        $wrd->set(word_api::TI_TOTAL, word_api::TN_TOTAL_PRE);
        return $wrd;
    }

    /**
     * @return word_list with some basic words for unit testing
     */
    function dummy_word_list(): word_list
    {
        $lst = new word_list($this->usr1);
        $lst->add($this->dummy_word());
        $lst->add($this->dummy_word_const());
        $lst->add($this->dummy_word_pi());
        $lst->add($this->dummy_word_e());
        return $lst;
    }

    /**
     * @return word_list with a few words for unit testing
     */
    function dummy_word_list_short(): word_list
    {
        $lst = new word_list($this->usr1);
        $lst->add($this->dummy_word());
        $lst->add($this->dummy_word_pi());
        return $lst;
    }

    /**
     * @return word_list with at least one word of each type for unit testing
     */
    function dummy_word_list_all_types(): word_list
    {
        $lst = new word_list($this->usr1);
        $lst->add($this->dummy_word());
        $lst->add($this->dummy_word_const());
        $lst->add($this->dummy_word_pi());
        $lst->add($this->dummy_word_2019());
        $lst->add($this->dummy_word_one());
        $lst->add($this->dummy_word_mio());
        $lst->add($this->dummy_word_pct());
        return $lst;
    }

    /**
     * @return verb the default verb
     */
    function dummy_verb(): verb
    {
        $vrb = new verb(verb_api::TI_READ, verb_api::TN_READ, verb::NOT_SET);
        $vrb->set_user($this->usr1);
        return $vrb;
    }

    /**
     * @return verb a standard verb with user null
     */
    function dummy_verb_is(): verb
    {
        return new verb(verb_api::TI_IS, verb_api::TN_IS, verb::IS);
    }

    /**
     * @return verb a standard verb with user null
     */
    function dummy_verb_part(): verb
    {
        return new verb(verb_api::TI_PART, verb_api::TN_PART, verb::IS_PART_OF);
    }

    /**
     * @return verb a standard verb with user null
     */
    function dummy_verb_of(): verb
    {
        $vrb = new verb(verb_api::TI_OF, verb_api::TN_OF, verb::CAN_CONTAIN_NAME_REVERSE);
        $vrb->set_user($this->usr1);
        return $vrb;
    }

    /**
     * @return triple "Mathematical constant" used for unit testing
     */
    function dummy_triple(): triple
    {
        $trp = new triple($this->usr1);
        $trp->set(triple_api::TI_READ, triple_api::TN_READ);
        $trp->description = triple_api::TD_READ;
        $trp->set_from($this->dummy_word_const()->phrase());
        $trp->set_verb($this->dummy_verb_part());
        $trp->set_to($this->dummy_word()->phrase());
        $trp->set_type(phrase_type::MATH_CONST);
        return $trp;
    }

    /**
     * @return triple "pi (math)" used for unit testing
     */
    function dummy_triple_pi(): triple
    {
        $trp = new triple($this->usr1);
        $trp->set(triple_api::TI_PI, triple_api::TN_PI_NAME);
        $trp->description = triple_api::TD_PI;
        $trp->set_from($this->dummy_word_pi()->phrase());
        $trp->set_verb($this->dummy_verb_is());
        $trp->set_to($this->dummy_triple()->phrase());
        $trp->set_type(phrase_type::TRIPLE_HIDDEN);
        return $trp;
    }

    /**
     * @return triple "e (math const)" used for unit testing
     */
    function dummy_triple_e(): triple
    {
        $trp = new triple($this->usr1);
        $trp->set(triple_api::TI_E, triple_api::TN_E);
        $trp->description = triple_api::TD_E;
        $trp->set_from($this->dummy_word_e()->phrase());
        $trp->set_verb($this->dummy_verb_is());
        $trp->set_to($this->dummy_triple()->phrase());
        $trp->set_type(phrase_type::TRIPLE_HIDDEN);
        return $trp;
    }

    /**
     * @return triple "Zurich (City)" used for unit testing
     */
    function dummy_triple_zh(): triple
    {
        $trp = new triple($this->usr1);
        $trp->set(triple_api::TI_ZH_CITY, triple_api::TN_ZH_CITY);
        $trp->set_from($this->dummy_word_zh()->phrase());
        $trp->set_verb($this->dummy_verb_is());
        $trp->set_to($this->dummy_word_city()->phrase());
        return $trp;
    }

    function dummy_triple_list(): triple_list
    {
        $lst = new triple_list($this->usr1);
        $lst->add($this->dummy_triple_pi());
        return $lst;
    }

    function dummy_phrase(): phrase
    {
        return $this->dummy_word()->phrase();
    }

    function phrase_pi(): phrase
    {
        return $this->dummy_triple_pi()->phrase();
    }

    function phrase_zh(): phrase
    {
        return $this->dummy_triple_zh()->phrase();
    }

    function dummy_phrase_list(): phrase_list
    {
        $lst = new phrase_list($this->usr1);
        $lst->add($this->dummy_word()->phrase());
        $lst->add($this->dummy_word_const()->phrase());
        $lst->add($this->dummy_word_pi()->phrase());
        $lst->add($this->dummy_triple()->phrase());
        $lst->add($this->dummy_triple_pi()->phrase());
        return $lst;
    }

    function dummy_phrase_list_prime(): phrase_list
    {
        $lst = new phrase_list($this->usr1);
        $lst->add($this->dummy_word()->phrase());
        $lst->add($this->dummy_word_const()->phrase());
        $lst->add($this->dummy_triple()->phrase());
        $lst->add($this->dummy_triple_pi()->phrase());
        return $lst;
    }

    /**
     * @return phrase_list with one word and one triple
     */
    function dummy_phrase_list_small(): phrase_list
    {
        $lst = new phrase_list($this->usr1);
        $lst->add($this->dummy_word_pi()->phrase());
        $lst->add($this->dummy_triple()->phrase());
        return $lst;
    }

    function dummy_phrase_list_pi(): phrase_list
    {
        $lst = new phrase_list($this->usr1);
        $lst->add($this->dummy_triple_pi()->phrase());
        return $lst;
    }

    /**
     * @return phrase_list with some math const e.g. to test loading a list of values by phrase list
     */
    function phrase_list_math_const(): phrase_list
    {
        $lst = new phrase_list($this->usr1);
        $lst->add($this->dummy_triple_pi()->phrase());
        $lst->add($this->dummy_triple_e()->phrase());
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
    function dummy_phrase_list_16(): phrase_list
    {
        $lst = new phrase_list($this->usr1);
        $wrd = $this->dummy_word();
        $wrd->set_id(1);
        $wrd->set_name('word1');
        $lst->add($wrd->phrase());
        $trp = $this->dummy_triple();
        $trp->set_id(11);
        $trp->set_name('triple1');
        $lst->add($trp->phrase());
        $wrd = $this->dummy_word();
        $wrd->set_id(12);
        $wrd->set_name('word2');
        $lst->add($wrd->phrase());
        $trp = $this->dummy_triple();
        $trp->set_id(37);
        $trp->set_name('triple2');
        $lst->add($trp->phrase());
        $wrd = $this->dummy_word();
        $wrd->set_id(38);
        $wrd->set_name('word3');
        $lst->add($wrd->phrase());
        $trp = $this->dummy_triple();
        $trp->set_id(64);
        $trp->set_name('triple3');
        $lst->add($trp->phrase());
        $wrd = $this->dummy_word();
        $wrd->set_id(376);
        $wrd->set_name('word4');
        $lst->add($wrd->phrase());
        $trp = $this->dummy_triple();
        $trp->set_id(2367);
        $trp->set_name('triple4');
        $lst->add($trp->phrase());
        $wrd = $this->dummy_word();
        $wrd->set_id(13108);
        $wrd->set_name('word5');
        $lst->add($wrd->phrase());
        $trp = $this->dummy_triple();
        $trp->set_id(82124);
        $trp->set_name('triple5');
        $lst->add($trp->phrase());
        $wrd = $this->dummy_word();
        $wrd->set_id(505294);
        $wrd->set_name('word6');
        $lst->add($wrd->phrase());
        $trp = $this->dummy_triple();
        $trp->set_id(2815273);
        $trp->set_name('triple6');
        $lst->add($trp->phrase());
        $wrd = $this->dummy_word();
        $wrd->set_id(17192845);
        $wrd->set_name('word7');
        $lst->add($wrd->phrase());
        $trp = $this->dummy_triple();
        $trp->set_id(106841477);
        $trp->set_name('triple7');
        $lst->add($trp->phrase());
        $wrd = $this->dummy_word();
        $wrd->set_id(628779863);
        $wrd->set_name('word8');
        $lst->add($wrd->phrase());
        $trp = $this->dummy_triple();
        $trp->set_id(3516593476);
        $trp->set_name('triple8');
        $lst->add($trp->phrase());
        return $lst;
    }

    function dummy_phrase_list_17_plus(): phrase_list
    {
        $lst = $this->dummy_phrase_list_16();
        $wrd = $this->dummy_word();
        $wrd->set_id(987654321);
        $wrd->set_name('word17');
        $lst->add($wrd->phrase());
        return $lst;
    }

    /**
     * @return phrase_list to get all numbers related to a list of phrases
     */
    function dummy_phrase_list_zh(): phrase_list
    {
        $lst = new phrase_list($this->usr1);
        $lst->add($this->dummy_word_canton()->phrase());
        $lst->add($this->dummy_word_zh()->phrase());
        $lst->add($this->dummy_word_inhabitant()->phrase());
        return $lst;
    }

    /**
     * @return phrase_list the phrases relevant for having a second entry in the phrase group list
     */
    function dummy_phrase_list_zh_2019(): phrase_list
    {
        $lst = new phrase_list($this->usr1);
        $lst->add($this->dummy_word_zh()->phrase());
        $lst->add($this->dummy_word_inhabitant()->phrase());
        $lst->add($this->dummy_word_2019()->phrase());
        return $lst;
    }

    /**
     * @return phrase_list the phrases relevant for testing the max number of prime phrases
     */
    function dummy_phrase_list_zh_mio(): phrase_list
    {
        $lst = new phrase_list($this->usr1);
        $lst->add($this->dummy_word_zh()->phrase());
        $lst->add($this->dummy_word_inhabitant()->phrase());
        $lst->add($this->dummy_word_2019()->phrase());
        $lst->add($this->dummy_word_mio()->phrase());
        return $lst;
    }

    /**
     * @return phrase_list the phrases relevant for testing the increase formula
     */
    function dummy_phrase_list_increase(): phrase_list
    {
        $lst = new phrase_list($this->usr1);
        $lst->add($this->dummy_word_pct()->phrase());
        $lst->add($this->dummy_word_this()->phrase());
        $lst->add($this->dummy_word_prior()->phrase());
        $lst->add($this->dummy_word_ch()->phrase());
        $lst->add($this->dummy_word_inhabitant()->phrase());
        $lst->add($this->dummy_word_2020()->phrase());
        $lst->add($this->dummy_word_mio()->phrase());
        return $lst;
    }

    function dummy_phrase_list_dsp(): phrase_list_dsp
    {
        return new phrase_list_dsp($this->dummy_phrase_list()->api_json());
    }

    /**
     * @return group with one prime phrases
     */
    function dummy_phrase_group(): group
    {
        $lst = $this->dummy_phrase_list_pi();
        $grp = $lst->get_grp_id(false);
        $grp->name = group_api::TN_READ;
        return $grp;
    }

    /**
     * @return group with three prime phrases
     */
    function dummy_phrase_group_prime_3(): group
    {
        $lst = $this->dummy_phrase_list_zh_2019();
        $grp = $lst->get_grp_id(false);
        $grp->name = group_api::TN_READ;
        return $grp;
    }

    /**
     * @return group with the max number of prime phrases
     */
    function dummy_phrase_group_prime_max(): group
    {
        $lst = $this->dummy_phrase_list_zh_mio();
        $grp = $lst->get_grp_id(false);
        $grp->name = group_api::TN_READ;
        return $grp;
    }

    function dummy_phrase_group_16(): group
    {
        $lst = $this->dummy_phrase_list_16();
        $grp = $lst->get_grp_id(false);
        $grp->name = group_api::TN_READ;
        return $grp;
    }

    function dummy_phrase_group_17_plus(): group
    {
        $lst = $this->dummy_phrase_list_17_plus();
        $grp = $lst->get_grp_id(false);
        $grp->name = group_api::TN_READ;
        return $grp;
    }

    function dummy_phrase_group_zh(): group
    {
        $lst = $this->dummy_phrase_list_zh_2019();
        $grp = $lst->get_grp_id(false);
        $grp->name = group_api::TN_ZH_2019;
        return $grp;
    }

    function dummy_phrase_group_list(): group_list
    {
        $lst = new group_list($this->usr1);
        $lst->add($this->dummy_phrase_group());
        return $lst;
    }

    function dummy_term(): term
    {
        return $this->dummy_word()->term();
    }

    function dummy_term_triple(): term
    {
        return $this->dummy_triple()->term();
    }

    function dummy_term_formula(): term
    {
        return $this->dummy_formula()->term();
    }

    function dummy_term_verb(): term
    {
        return $this->dummy_verb()->term();
    }

    /**
     * @return term_list with all terms used for the unit tests
     */
    function dummy_term_list(): term_list
    {
        $lst = new term_list($this->usr1);
        $lst->add($this->dummy_term());
        $lst->add($this->dummy_term_triple());
        $lst->add($this->dummy_term_formula());
        $lst->add($this->dummy_term_verb());
        return $lst;
    }

    /**
     * @return term_list with all terms used for the unit tests
     */
    function dummy_term_list_all(): term_list
    {
        $lst = new term_list($this->usr1);
        $lst->add($this->dummy_term());
        $lst->add($this->dummy_term_triple());
        $lst->add($this->dummy_term_formula());
        $lst->add($this->dummy_term_verb());
        $lst->add($this->dummy_triple_pi()->term());
        $lst->add($this->dummy_word_pi()->term());
        $lst->add($this->dummy_word_cf()->term());
        $lst->add($this->dummy_word_pct()->term());
        $lst->add($this->dummy_word_prior()->term());
        $lst->add($this->dummy_word_this()->term());
        $lst->add($this->dummy_word_parts()->term());
        $lst->add($this->dummy_word_total()->term());
        $lst->add($this->dummy_verb_of()->term());
        $lst->add($this->dummy_word_one()->term());
        $lst->add($this->dummy_word_mio()->term());
        return $lst;
    }

    /**
     * @return term_list a term list with the time terms e.g. minute and second
     */
    function dummy_term_list_time(): term_list
    {
        $lst = new term_list($this->usr1);
        $lst->add($this->dummy_word_second()->term());
        $lst->add($this->dummy_word_minute()->term());
        return $lst;
    }

    /**
     * @return term_list a term list with the scaling terms e.g. one and million
     */
    function dummy_term_list_scale(): term_list
    {
        $lst = new term_list($this->usr1);
        $lst->add($this->dummy_word_one()->term());
        $lst->add($this->dummy_word_mio()->term());
        return $lst;
    }

    function dummy_value(): value
    {
        $grp = $this->dummy_phrase_group();
        return new value($this->usr1, round(value_api::TV_READ, 13), $grp);
    }

    /**
     * @return value with more than one prime phrase
     */
    function dummy_value_prime_3(): value
    {
        $grp = $this->dummy_phrase_group_prime_3();
        return new value($this->usr1, round(value_api::TV_READ, 13), $grp);
    }

    /**
     * @return value with the maximal number of prime phrase
     */
    function dummy_value_prime_max(): value
    {
        $grp = $this->dummy_phrase_group_prime_max();
        return new value($this->usr1, round(value_api::TV_READ, 13), $grp);
    }

    function dummy_value_16(): value
    {
        $grp = $this->dummy_phrase_group_16();
        return new value($this->usr1, round(value_api::TV_READ, 13), $grp);
    }

    function dummy_value_17_plus(): value
    {
        $grp = $this->dummy_phrase_group_17_plus();
        return new value($this->usr1, round(value_api::TV_READ, 13), $grp);
    }

    function dummy_value_zh(): value
    {
        $grp = $this->dummy_phrase_group_zh();
        return new value($this->usr1, value_api::TV_CITY_ZH_INHABITANTS_2019, $grp);
    }

    function dummy_value_list(): value_list
    {
        $lst = new value_list($this->usr1);
        $lst->add($this->dummy_value());
        $lst->add($this->dummy_value_zh());
        return $lst;
    }

    function dummy_value_phrase_link(): value_phrase_link
    {
        $lnk = new value_phrase_link($this->usr1);
        $lnk->set(1, $this->dummy_value(), $this->dummy_phrase());
        return $lnk;
    }

    function dummy_formula(): formula
    {
        $frm = new formula($this->usr1);
        $frm->set(1, formula_api::TN_READ);
        $frm->set_user_text(formula_api::TF_READ, $this->dummy_term_list_time());
        $frm->set_type(formula_type::CALC);
        return $frm;
    }

    function dummy_formula_increase(): formula
    {
        $frm = new formula($this->usr1);
        $frm->set(21, formula_api::TN_INCREASE);
        $frm->set_user_text(formula_api::TF_INCREASE, $this->dummy_phrase_list_increase()->term_list());
        $frm->set_type(formula_type::CALC);
        return $frm;
    }

    function dummy_formula_list(): formula_list
    {
        $lst = new formula_list($this->usr1);
        $lst->add($this->dummy_formula());
        return $lst;
    }

    function dummy_formula_link(): formula_link
    {
        $lnk = new formula_link($this->usr1);
        $lnk->set(1, $this->dummy_formula(), $this->dummy_word()->phrase());
        return $lnk;
    }

    function dummy_expression(): expression
    {
        $trm_lst = $this->dummy_term_list_time();
        return $this->dummy_formula()->expression($trm_lst);
    }

    function dummy_element(): formula_element
    {
        $lst = $this->dummy_element_list();
        return $lst->lst()[0];
    }

    function dummy_element_list(): formula_element_list
    {
        $trm_lst = $this->dummy_term_list_time();
        $exp = $this->dummy_formula()->expression($trm_lst);
        return $exp->element_list($trm_lst);
    }

    function dummy_result(): result
    {
        $res = new result($this->usr1);
        $wrd = $this->dummy_word();
        $phr_lst = new phrase_list($this->usr1);
        $phr_lst->add($wrd->phrase());
        $res->set_id(1);
        $res->grp->set_phrase_list($phr_lst);
        $res->value = result_api::TV_INT;
        return $res;
    }

    function dummy_result_prime(): result
    {
        $res = new result($this->usr1);
        $res->set_grp($this->dummy_phrase_group());
        $res->value = result_api::TV_INT;
        return $res;
    }

    function dummy_result_16(): result
    {
        $res = new result($this->usr1);
        $res->set_grp($this->dummy_phrase_group_16());
        $res->value = result_api::TV_INT;
        return $res;
    }

    function dummy_result_pct(): result
    {
        $res = new result($this->usr1);
        $wrd_pct = $this->new_word(word_api::TN_PCT, 2, phrase_type::PERCENT);
        $phr_lst = new phrase_list($this->usr1);
        $phr_lst->add($wrd_pct->phrase());
        $res->grp->set_phrase_list($phr_lst);
        $res->value = 0.01234;
        return $res;
    }

    function dummy_result_list(): result_list
    {
        $lst = new result_list($this->usr1);
        $lst->add($this->dummy_result());
        $lst->add($this->dummy_result_pct());
        return $lst;
    }

    /**
     * @return figure with all vars set for unit testing - user value case
     */
    function dummy_figure_value(): figure
    {
        $val = $this->dummy_value();
        $val->set_last_update(new DateTime(self::DUMMY_DATETIME));
        return $val->figure();
    }

    /**
     * @return figure with all vars set for unit testing - user value case
     */
    function dummy_figure_result(): figure
    {
        $res = $this->dummy_result();
        return $res->figure();
    }

    function dummy_figure_list(): figure_list
    {
        $lst = new figure_list($this->usr1);
        $lst->add($this->dummy_figure_value());
        $lst->add($this->dummy_figure_result());
        return $lst;
    }

    function dummy_source(): source
    {
        $src = new source($this->usr1);
        $src->set(3, source_api::TN_READ_API, source_type::PDF);
        $src->description = source_api::TD_READ_API;
        $src->url = source_api::TU_READ_API;
        return $src;
    }

    function dummy_source1(): source
    {
        $src = new source($this->usr1);
        $src->set(1, source_api::TN_READ_API, source_type::PDF);
        $src->description = source_api::TD_READ_API;
        $src->url = source_api::TU_READ_API;
        return $src;
    }

    function dummy_reference(): ref
    {
        $ref = new ref($this->usr1);
        $ref->set(4);
        $ref->phr = $this->dummy_word_pi()->phrase();
        $ref->source = $this->dummy_source1();
        $ref->external_key = ref_api::TK_READ;
        $ref->url = ref_api::TU_READ;
        $ref->description = ref_api::TD_READ;
        return $ref;
    }

    function dummy_view(): view
    {
        $dsp = new view($this->usr1);
        $dsp->set(1, view_api::TN_READ);
        $dsp->description = view_api::TD_READ;
        $dsp->code_id = view_api::TI_READ;
        return $dsp;
    }

    function dummy_view_with_components(): view
    {
        $dsp = $this->dummy_view();
        $dsp->cmp_lnk_lst = $this->dummy_component_link_list();
        return $dsp;
    }

    function dummy_view_word_add(): view
    {
        $dsp = new view($this->usr1);
        $dsp->set(3, view_api::TN_FORM);
        $dsp->description = view_api::TD_FORM;
        $dsp->code_id = view_api::TI_FORM;
        $dsp->cmp_lnk_lst = $this->dummy_components_word_add($dsp);
        return $dsp;
    }

    function dummy_view_list(): view_list
    {
        $lst = new view_list($this->usr1);
        $lst->add($this->dummy_view_with_components());
        $lst->add($this->dummy_view_word_add());
        return $lst;
    }

    function dummy_component(): component
    {
        $cmp = new component($this->usr1);
        $cmp->set(1, component_api::TN_READ, component_type::PHRASE_NAME);
        $cmp->description = component_api::TD_READ;
        return $cmp;
    }

    function dummy_component_word_add_title(): component
    {
        $cmp = new component($this->usr1);
        $cmp->set(1, component_api::TN_FORM_TITLE, component_type::FORM_TITLE);
        $cmp->description = component_api::TD_FORM_TITLE;
        $cmp->code_id = component_api::TI_FORM_TITLE;
        return $cmp;
    }

    function dummy_component_word_add_back_stack(): component
    {
        $cmp = new component($this->usr1);
        $cmp->set(2, component_api::TN_FORM_BACK, component_type::FORM_BACK);
        $cmp->description = component_api::TD_FORM_BACK;
        $cmp->code_id = component_api::TI_FORM_BACK;
        return $cmp;
    }

    function dummy_component_word_add_button_confirm(): component
    {
        $cmp = new component($this->usr1);
        $cmp->set(3, component_api::TN_FORM_CONFIRM, component_type::FORM_CONFIRM);
        $cmp->description = component_api::TD_FORM_CONFIRM;
        $cmp->code_id = component_api::TI_FORM_CONFIRM;
        return $cmp;
    }

    function dummy_component_word_add_name(): component
    {
        $cmp = new component($this->usr1);
        $cmp->set(4, component_api::TN_FORM_NAME, component_type::FORM_NAME);
        $cmp->description = component_api::TD_FORM_NAME;
        $cmp->code_id = component_api::TI_FORM_NAME;
        return $cmp;
    }

    function dummy_component_word_add_description(): component
    {
        $cmp = new component($this->usr1);
        $cmp->set(5, component_api::TN_FORM_DESCRIPTION, component_type::FORM_DESCRIPTION);
        $cmp->description = component_api::TD_FORM_DESCRIPTION;
        $cmp->code_id = component_api::TI_FORM_DESCRIPTION;
        return $cmp;
    }

    function dummy_component_word_add_share_type(): component
    {
        $cmp = new component($this->usr1);
        $cmp->set(6, component_api::TN_FORM_SHARE_TYPE, component_type::FORM_SHARE_TYPE);
        $cmp->description = component_api::TD_FORM_SHARE_TYPE;
        $cmp->code_id = component_api::TI_FORM_SHARE_TYPE;
        return $cmp;
    }

    function dummy_component_word_add_protection_type(): component
    {
        $cmp = new component($this->usr1);
        $cmp->set(7, component_api::TN_FORM_PROTECTION_TYPE, component_type::FORM_PROTECTION_TYPE);
        $cmp->description = component_api::TD_FORM_PROTECTION_TYPE;
        $cmp->code_id = component_api::TI_FORM_PROTECTION_TYPE;
        return $cmp;
    }

    function dummy_component_word_add_cancel(): component
    {
        $cmp = new component($this->usr1);
        $cmp->set(8, component_api::TN_FORM_CANCEL, component_type::FORM_CANCEL);
        $cmp->description = component_api::TD_FORM_CANCEL;
        $cmp->code_id = component_api::TI_FORM_CANCEL;
        return $cmp;
    }

    function dummy_component_word_add_save(): component
    {
        $cmp = new component($this->usr1);
        $cmp->set(9, component_api::TN_FORM_SAVE, component_type::FORM_SAVE);
        $cmp->description = component_api::TD_FORM_SAVE;
        $cmp->code_id = component_api::TI_FORM_SAVE;
        return $cmp;
    }

    function dummy_component_word_add_form_end(): component
    {
        $cmp = new component($this->usr1);
        $cmp->set(10, component_api::TN_FORM_END, component_type::FORM_END);
        $cmp->description = component_api::TD_FORM_END;
        $cmp->code_id = component_api::TI_FORM_END;
        return $cmp;
    }

    function dummy_component_list(): component_list
    {
        $lst = new component_list($this->usr1);
        $lst->add($this->dummy_component());
        $lst->add($this->dummy_component_word_add_share_type());
        return $lst;
    }

    function dummy_component_link(): component_link
    {
        $lnk = new component_link($this->usr1);
        $lnk->set(1, $this->dummy_view(), $this->dummy_component(), 1);
        return $lnk;
    }

    function dummy_component_link_list(): component_link_list
    {
        $lst = new component_link_list($this->usr1);
        $lst->add_link($this->dummy_component_link());
        return $lst;
    }

    function dummy_components_word_add(view $msk): component_link_list
    {
        $pos = 1;
        $lst = new component_link_list($this->usr1);
        $lst->add($pos, $msk, $this->dummy_component_word_add_title(), $pos);
        $pos++;
        $lst->add($pos, $msk, $this->dummy_component_word_add_back_stack(), $pos);
        $pos++;
        $lst->add($pos, $msk, $this->dummy_component_word_add_button_confirm(), $pos);
        $pos++;
        $lst->add($pos, $msk, $this->dummy_component_word_add_name(), $pos);
        $pos++;
        $lst->add($pos, $msk, $this->dummy_component_word_add_description(), $pos);
        $pos++;
        $lst->add($pos, $msk, $this->dummy_component_word_add_share_type(), $pos);
        $pos++;
        $lst->add($pos, $msk, $this->dummy_component_word_add_protection_type(), $pos);
        $pos++;
        $lst->add($pos, $msk, $this->dummy_component_word_add_cancel(), $pos);
        $pos++;
        $lst->add($pos, $msk, $this->dummy_component_word_add_save(), $pos);
        $pos++;
        $lst->add($pos, $msk, $this->dummy_component_word_add_form_end(), $pos);
        return $lst;
    }

    function dummy_language(): language
    {
        return new language(language::DEFAULT, language::TN_READ, 'English is the default', 1);
    }

    /**
     * @return change a change log entry of a named user sandbox object with some dummy values
     */
    function dummy_change_log_named(): change
    {
        global $usr_sys;

        $chg = new change($usr_sys);
        $chg->set_time_str(self::DUMMY_DATETIME);
        $chg->set_action(change_log_action::ADD);
        $chg->set_table(change_log_table::WORD);
        $chg->set_field(change_log_field::FLD_WORD_NAME);
        $chg->new_value = word_api::TN_READ;
        $chg->row_id = 1;
        return $chg;
    }

    /**
     * @return system_log a system error entry
     */
    function dummy_sys_log(): system_log
    {
        global $sys_log_stati;
        $sys = new system_log();
        $sys->set_id(1);
        $sys->log_time = new DateTime(system_log_api::TV_TIME);
        $sys->usr_name = user::SYSTEM_TEST_NAME;
        $sys->log_text = system_log_api::TV_LOG_TEXT;
        $sys->log_trace = system_log_api::TV_LOG_TRACE;
        $sys->function_name = system_log_api::TV_FUNC_NAME;
        $sys->solver_name = system_log_api::TV_SOLVE_ID;
        $sys->status_name = $sys_log_stati->id(sys_log_status::OPEN);
        return $sys;
    }

    /**
     * @return system_log a system error entry
     */
    function dummy_sys_log2(): system_log
    {
        global $sys_log_stati;
        $sys = new system_log();
        $sys->set_id(2);
        $sys->log_time = new DateTime(system_log_api::TV_TIME);
        $sys->usr_name = user::SYSTEM_TEST_NAME;
        $sys->log_text = system_log_api::T2_LOG_TEXT;
        $sys->log_trace = system_log_api::T2_LOG_TRACE;
        $sys->function_name = system_log_api::T2_FUNC_NAME;
        $sys->solver_name = system_log_api::TV_SOLVE_ID;
        $sys->status_name = $sys_log_stati->id(sys_log_status::CLOSED);
        return $sys;
    }

    /**
     * @return batch_job a batch job entry with some dummy values
     */
    function dummy_job(): batch_job
    {
        $sys_usr = $this->system_user();
        $job = new batch_job($sys_usr, new DateTime(system_log_api::TV_TIME));
        $job->set_id(1);
        $job->start_time = new DateTime(system_log_api::TV_TIME);
        $job->set_type(batch_job_type_list::BASE_IMPORT);
        $job->row_id = 1;
        return $job;
    }

    /**
     * @return change_log_list a list of change log entries with some dummy values
     *
     * TODO add at least one sample for rename and delete
     * TODO add at least one sample for verb, triple, value, formula, source, ref, view and component
     */
    function dummy_change_log_list_named(): change_log_list
    {
        $log_lst = new change_log_list();
        $log_lst->add($this->dummy_change_log_named());
        return $log_lst;
    }

    /**
     * @return system_log_list a list of system error entries with some dummy values
     */
    function dummy_system_log_list(): system_log_list
    {
        $sys_lst = new system_log_list();
        $sys_lst->add($this->dummy_sys_log());
        $sys_lst->add($this->dummy_sys_log2());
        return $sys_lst;
    }

    /**
     * @return batch_job_list a list of batch job entries with some dummy values
     */
    function dummy_job_list(): batch_job_list
    {
        $sys_usr = $this->system_user();
        $job_lst = new batch_job_list($sys_usr);
        $job_lst->add($this->dummy_job());
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
        $dsp_obj->set_from_json($model_obj->api_obj($do_save)->get_json());
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
        global $phrase_types;
        $wrd = $this->load_word($wrd_name, $test_usr);
        if ($wrd->id() == 0) {
            $wrd->set_name($wrd_name);
            $wrd->save();
        }
        if ($wrd->id() <= 0) {
            log_err('Cannot create word ' . $wrd_name);
        }
        if ($wrd_type_code_id != null) {
            $wrd->type_id = $phrase_types->id($wrd_type_code_id);
            $wrd->save();
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
        $target = $wrd_name;
        $this->display('testing->add_word', $target, $wrd->name());
        return $wrd;
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
        global $verbs;
        global $phrase_types;

        if ($id == null) {
            $id = $this->next_seq_nbr();
        }
        if ($test_usr == null) {
            $test_usr = $this->usr1;
        }

        $trp = new triple($test_usr);
        $trp->set_id($id);
        $trp->fob = $this->new_word($from_name)->phrase();
        $trp->verb = $verbs->get_verb($verb_code_id);
        $trp->tob = $this->new_word($to_name)->phrase();
        $trp->set_name($wrd_name);

        if ($wrd_type_code_id != null) {
            $trp->type_id = $phrase_types->id($wrd_type_code_id);
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
        global $verbs;

        $wrd_from = $this->load_word($from_name, $this->usr1);
        $wrd_to = $this->load_word($to_name, $this->usr1);
        $from = $wrd_from->phrase();
        $to = $wrd_to->phrase();

        $vrb = $verbs->get_verb($verb_code_id);

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
        global $verbs;

        if ($test_usr == null) {
            $test_usr = $this->usr1;
        }

        $wrd_from = $this->create_word($from_name);
        $wrd_to = $this->create_word($to_name);
        $from = $wrd_from->phrase();
        $to = $wrd_to->phrase();

        $vrb = $verbs->get_verb($verb_code_id);

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
        global $verbs;

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
        $vrb = $verbs->get_verb($verb_code_id);

        // check if the triple exists or create a new if needed
        $trp = new triple($this->usr1);
        if ($from->id() == 0 or $to->id() == 0) {
            log_err("Phrases " . $from_name . " and " . $to_name . " cannot be created");
        } else {
            // check if the forward link exists
            $trp->load_by_link_id($from->id(), $vrb->id(), $to->id());
            if ($trp->id() > 0) {
                // refresh the given name if needed
                if ($name_given <> '' and $trp->name(true) <> $name_given) {
                    $trp->set_name_given($name_given);
                    $trp->set_name($name_given);
                    $trp->save();
                    $trp->load_by_id($trp->id());
                }
                $result = $trp;
            } else {
                // check if the backward link exists
                $trp->fob = $to;
                $trp->verb = $vrb;
                $trp->tob = $from;
                $trp->set_user($this->usr1);
                $trp->load_by_link_id($to->id(), $vrb->id(), $from->id());
                $result = $trp;
                // create the link if requested
                if ($trp->id() <= 0 and $auto_create) {
                    $trp->fob = $from;
                    $trp->verb = $vrb;
                    $trp->tob = $to;
                    if ($trp->name(true) <> $name_given) {
                        $trp->set_name_given($name_given);
                        $trp->set_name($name_given);
                    }
                    $trp->save();
                    $trp->load_by_id($trp->id());
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

        $this->display('test_triple', $target, $result_text, TIMEOUT_LIMIT_DB);
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
        global $formula_types;

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
            $frm->type_id = $formula_types->id($frm_type_code_id);
        }
        return $frm;
    }

    function load_formula(string $frm_name): formula
    {
        $frm = new formula($this->usr1);
        $frm->load_by_name($frm_name, formula::class);
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
            $frm->save();
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

        $lst = new ref_type_list();
        $ref = new ref($this->usr1);
        if ($phr->id() != 0) {
            $ref->load_by_link_ids($phr->id(), $lst->get_ref_type($type_name)->id());
        }
        return $ref;
    }

    function test_ref(string $wrd_name, string $external_key, string $type_name): ref
    {
        $lst = new ref_type_list();
        $wrd = $this->test_word($wrd_name);
        $phr = $wrd->phrase();
        $ref = $this->load_ref($wrd->name(), $type_name);
        if ($ref->id() == 0) {
            $ref->phr = $phr;
            $ref->ref_type = $lst->get_ref_type($type_name);
            $ref->external_key = $external_key;
            $ref->save();
        }
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
     * @return group
     */
    function load_phrase_group(array $array_of_phrase_str): group
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
        $val->load_by_id($id, value::class);
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
                $val->grp = $phr_grp;
            }
            $val->set_number($target);
            $val->save();
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
            $val->save();
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
            $src->save();
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
        global $phrase_types;
        $msg = new api_message($db_con, word::class, $this->usr1);
        $wrd = new word_api();
        $wrd->name = word_api::TN_ADD_API;
        $wrd->description = word_api::TD_ADD_API;
        $wrd->type_id = $phrase_types->id(phrase_type::NORMAL);
        $msg->add_body($wrd);
        return $msg->get_json_array();
    }

    /**
     * @return array json message to test if updating of a word via the api works fine
     */
    function word_post_json(): array
    {
        global $db_con;
        $msg = new api_message($db_con, word::class, $this->usr1);
        $wrd = new word_api();
        $wrd->name = word_api::TN_UPD_API;
        $wrd->description = word_api::TD_UPD_API;
        $msg->add_body($wrd);
        return $msg->get_json_array();
    }

    /**
     * @return array json message to test if adding a new source via the api works fine
     */
    function source_put_json(): array
    {
        global $db_con;
        global $source_types;
        $msg = new api_message($db_con, source::class, $this->usr1);
        $src = new source_api();
        $src->name = source_api::TN_ADD_API;
        $src->description = source_api::TD_ADD_API;
        $src->url = source_api::TU_ADD_API;
        $src->type_id = $source_types->id(source_type::PDF);
        $msg->add_body($src);
        return $msg->get_json_array();
    }

    /**
     * @return array json message to test if updating of a source via the api works fine
     */
    function source_post_json(): array
    {
        global $db_con;
        $msg = new api_message($db_con, source::class, $this->usr1);
        $src = new source_api();
        $src->name = source_api::TN_UPD_API;
        $src->description = source_api::TD_UPD_API;
        $msg->add_body($src);
        return $msg->get_json_array();
    }

    /**
     * @return array json message to test if adding a new reference via the api works fine
     */
    function reference_put_json(): array
    {
        global $db_con;
        global $reference_types;
        $msg = new api_message($db_con, ref::class, $this->usr1);
        $ref = new ref_api();
        $ref->phrase_id = $this->dummy_word()->phrase()->id();
        $ref->external_key = ref_api::TK_ADD_API;
        $ref->description = ref_api::TD_ADD_API;
        $ref->url = ref_api::TU_ADD_API;
        $ref->type_id = $reference_types->id(source_type::PDF);
        $msg->add_body($ref);
        return $msg->get_json_array();
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

        $dsp = new view($test_usr);
        $dsp->load_by_name($dsp_name, view::class);
        return $dsp;
    }

    function add_view(string $dsp_name, user $test_usr): view
    {
        $dsp = $this->load_view($dsp_name, $test_usr);
        if ($dsp->id() == 0) {
            $dsp->set_user($test_usr);
            $dsp->set_name($dsp_name);
            $dsp->save();
        }
        return $dsp;
    }

    function test_view(string $dsp_name, ?user $test_usr = null): view
    {
        if ($test_usr == null) {
            $test_usr = $this->usr1;
        }

        $dsp = $this->add_view($dsp_name, $test_usr);
        $this->display('view', $dsp_name, $dsp->name());
        return $dsp;
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
        $cmp->load_by_name($cmp_name, component::class);
        return $cmp;
    }

    function add_component(string $cmp_name, user $test_usr, string $type_code_id = ''): component
    {
        global $component_types;

        $cmp = $this->load_component($cmp_name, $test_usr);
        if ($cmp->id() == 0 or $cmp->id() == Null) {
            $cmp->set_user($test_usr);
            $cmp->set_name($cmp_name);
            if ($type_code_id != '') {
                $cmp->type_id = $component_types->id($type_code_id);
            }
            $cmp->save();
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
        $dsp = $this->load_view($dsp_name);
        $cmp = $this->load_component($cmp_name);
        $lnk = new component_link($this->usr1);
        $lnk->fob = $dsp;
        $lnk->tob = $cmp;
        $lnk->order_nbr = $pos;
        $result = $lnk->save();
        $target = '';
        $this->display('view component link', $target, $result);
        return $lnk;
    }

    function test_component_unlink(string $dsp_name, string $cmp_name): string
    {
        $result = '';
        $dsp = $this->load_view($dsp_name);
        $cmp = $this->load_component($cmp_name);
        if ($dsp->id() > 0 and $cmp->id() > 0) {
            $result = $cmp->unlink($dsp);
        }
        return $result;
    }

    function test_formula_link(string $formula_name, string $word_name, bool $autocreate = true): string
    {
        $result = '';

        $frm = new formula($this->usr1);
        $frm->load_by_name($formula_name, formula::class);
        $wrd = new word($this->usr1);
        $wrd->load_by_name($word_name, word::class);
        if ($frm->id() > 0 and $wrd->id() <> 0) {
            $frm_lnk = new formula_link($this->usr1);
            $frm_lnk->load_by_link($frm, $wrd->phrase());
            if ($frm_lnk->id() > 0) {
                $result = $frm_lnk->fob->name() . ' is linked to ' . $frm_lnk->tob->name();
                $target = $formula_name . ' is linked to ' . $word_name;
                $this->display('formula_link', $target, $result);
            } else {
                if ($autocreate) {
                    $frm_lnk->fob = $frm;
                    $frm_lnk->tob = $wrd->phrase();
                    $frm_lnk->save();
                }
            }
        }
        return $result;
    }

    /**
     * create all database entries used for the read db unit tests
     * @return void
     */
    function create_test_db_entries(test_unit_read_db $t): void
    {
        (new word_test())->create_test_words($t);
        (new triple_test())->create_test_triples($t);
        (new triple_test())->create_base_times($t);
        (new source_test())->create_test_sources($t);
        (new formula_test())->create_test_formulas($t);
        (new formula_link_test())->create_test_formula_links($t);
        (new view_test())->create_test_views($t);
        (new component_test())->create_test_components($t);
        (new component_link_test())->create_test_component_links($t);
        (new value_test())->create_test_values($t);
    }

}