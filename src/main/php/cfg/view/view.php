<?php

/*

    model/view/view.php - the main display object
    -----------------

    The main sections of this object are
    - db const:          const for the database link
    - object vars:       the variables of this word object
    - construct and map: including the mapping of the db row to this word object
    - api:               create an api array for the frontend and set the vars based on a frontend api message
    - im- and export:    create an export object and set the vars from an import object
    - set and get:       to capsule the vars from unexpected changes
    - preloaded:         select e.g. types from cache
    - cast:              create an api object and set the vars from an api json
    - load:              database access object (DAO) functions
    - load related:      load e.g. the related components from the database
    - load helper:       the field names of this object as overwrite functions
    - components:        modify interface functions
    - assign:            interface functions to assign the view to word, triples, verbs or formulas
    - information:       functions to make code easier to read
    - save:              manage to update the database
    - save helper:       helpers for updating the database
    - sql write fields:  field list for writing to the database
    - display:           to be moved to the frontend object


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

namespace cfg\view;

include_once MODEL_SANDBOX_PATH . 'sandbox_typed.php';
include_once DB_PATH . 'sql.php';
include_once DB_PATH . 'sql_creator.php';
include_once DB_PATH . 'sql_db.php';
include_once DB_PATH . 'sql_field_default.php';
include_once DB_PATH . 'sql_field_type.php';
include_once DB_PATH . 'sql_par.php';
include_once DB_PATH . 'sql_par_field_list.php';
include_once DB_PATH . 'sql_par_type.php';
include_once DB_PATH . 'sql_type.php';
include_once DB_PATH . 'sql_type_list.php';
include_once MODEL_COMPONENT_PATH . 'component.php';
include_once MODEL_COMPONENT_PATH . 'component_link.php';
include_once MODEL_COMPONENT_PATH . 'component_link_list.php';
include_once MODEL_COMPONENT_PATH . 'component_list.php';
include_once MODEL_COMPONENT_PATH . 'position_type.php';
include_once MODEL_COMPONENT_PATH . 'view_style.php';
include_once MODEL_HELPER_PATH . 'data_object.php';
include_once MODEL_HELPER_PATH . 'type_list.php';
include_once MODEL_HELPER_PATH . 'type_object.php';
include_once MODEL_LANGUAGE_PATH . 'language.php';
include_once MODEL_LOG_PATH . 'change.php';
include_once MODEL_PHRASE_PATH . 'phrase.php';
include_once MODEL_PHRASE_PATH . 'term.php';
include_once MODEL_SANDBOX_PATH . 'sandbox.php';
include_once MODEL_SANDBOX_PATH . 'sandbox_named.php';
include_once MODEL_SANDBOX_PATH . 'sandbox_typed.php';
include_once MODEL_USER_PATH . 'user.php';
include_once MODEL_USER_PATH . 'user_message.php';
include_once MODEL_VIEW_PATH . 'view_term_link.php';
include_once MODEL_VIEW_PATH . 'view_type.php';
include_once SHARED_TYPES_PATH . 'api_type_list.php';
include_once SHARED_TYPES_PATH . 'position_types.php';
include_once SHARED_CONST_PATH . 'views.php';
include_once SHARED_PATH . 'json_fields.php';
include_once SHARED_PATH . 'library.php';

use cfg\component\component;
use cfg\component\component_link;
use cfg\component\component_link_list;
use cfg\component\component_list;
use cfg\component\view_style;
use cfg\db\sql;
use cfg\db\sql_creator;
use cfg\db\sql_db;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
use cfg\db\sql_par;
use cfg\db\sql_par_field_list;
use cfg\db\sql_par_type;
use cfg\db\sql_type;
use cfg\db\sql_type_list;
use cfg\helper\data_object;
use cfg\helper\type_list;
use cfg\helper\type_object;
use cfg\language\language;
use cfg\log\change;
use cfg\phrase\phrase;
use cfg\phrase\term;
use cfg\sandbox\sandbox;
use cfg\sandbox\sandbox_named;
use cfg\sandbox\sandbox_typed;
use cfg\user\user;
use cfg\user\user_message;
use shared\json_fields;
use shared\library;
use shared\const\views;
use shared\types\api_type_list;
use shared\types\position_types;

class view extends sandbox_typed
{

    /*
     * db const
     */

    // comments used for the database creation
    const TBL_COMMENT = 'to store all user interfaces entry points';

    // the database and JSON object field names used only for views
    // *_COM: the description of the field
    const FLD_ID = 'view_id';
    const FLD_NAME_COM = 'the name of the view used for searching';
    const FLD_NAME = 'view_name';
    const FLD_DESCRIPTION_COM = 'to explain the view to the user with a mouse over text; to be replaced by a language form entry';
    const FLD_TYPE_COM = 'to link coded functionality to views e.g. to use a view for the startup page';
    const FLD_TYPE = 'view_type_id';
    const FLD_STYLE_COM = 'the default display style for this view';
    const FLD_STYLE = 'view_style_id';
    const FLD_CODE_ID_COM = 'to link coded functionality to a specific view e.g. define the internal system views';
    // the JSON object field names
    const FLD_COMPONENT = 'components';

    // list of fields that MUST be set by one user
    const FLD_LST_MUST_BE_IN_STD = array(
        [self::FLD_NAME, sql_field_type::NAME_UNIQUE, sql_field_default::NOT_NULL, sql::INDEX, '', self::FLD_NAME_COM],
    );
    // list of must fields that CAN be changed by the user
    const FLD_LST_MUST_BUT_USER_CAN_CHANGE = array(
        [language::FLD_ID, sql_field_type::KEY_PART_INT, sql_field_default::ONE, sql::INDEX, language::class, self::FLD_NAME_COM],
        [self::FLD_NAME, sql_field_type::NAME, sql_field_default::NULL, sql::INDEX, '', self::FLD_NAME_COM],
    );
    // list of fields that CAN be changed by the user
    const FLD_LST_USER_CAN_CHANGE = array(
        [self::FLD_DESCRIPTION, self::FLD_DESCRIPTION_SQL_TYP, sql_field_default::NULL, '', '', self::FLD_DESCRIPTION_COM],
        [self::FLD_TYPE, type_object::FLD_ID_SQL_TYP, sql_field_default::NULL, sql::INDEX, view_type::class, self::FLD_TYPE_COM],
        [self::FLD_STYLE, type_object::FLD_ID_SQL_TYP, sql_field_default::NULL, sql::INDEX, view_style::class, self::FLD_STYLE_COM],
    );
    // list of fields that CANNOT be changed by the user
    const FLD_LST_NON_CHANGEABLE = array(
        [sql::FLD_CODE_ID, sql_field_type::NAME_UNIQUE, sql_field_default::NULL, '', '', self::FLD_CODE_ID_COM],
    );

    // all database field names excluding the id
    const FLD_NAMES = array(
        sql::FLD_CODE_ID
    );
    // list of the user specific database field names
    const FLD_NAMES_USR = array(
        sandbox_named::FLD_DESCRIPTION
    );
    // list of the user specific database field names
    const FLD_NAMES_NUM_USR = array(
        self::FLD_TYPE,
        self::FLD_STYLE,
        sandbox::FLD_EXCLUDED,
        sandbox::FLD_SHARE,
        sandbox::FLD_PROTECT
    );
    // all database field names excluding the id used to identify if there are some user specific changes
    const ALL_SANDBOX_FLD_NAMES = array(
        sandbox_named::FLD_DESCRIPTION,
        self::FLD_TYPE,
        self::FLD_STYLE,
        sandbox::FLD_EXCLUDED,
        sandbox::FLD_SHARE,
        sandbox::FLD_PROTECT
    );


    /*
     * object vars
     */

    // database fields additional to the user sandbox fields for the view component
    // to select internal predefined views
    public ?string $code_id = null;

    // in memory only fields
    // all links to the component objects in correct order
    public ?component_link_list $cmp_lnk_lst;

    // the default display style for this component which can be overwritten by the link
    private ?type_object $style = null;


    /*
     * construct and map
     */

    /**
     * define the settings for this view object
     * @param user $usr the user who requested to see this view
     */
    function __construct(user $usr)
    {
        $this->reset();

        parent::__construct($usr);
        $this->rename_can_switch = UI_CAN_CHANGE_VIEW_NAME;
    }

    function reset(): void
    {
        parent::reset();

        $this->type_id = null;
        $this->style = null;
        $this->code_id = null;

        $this->cmp_lnk_lst = null;
    }

    // TODO check if there is any case where the user fields should not be set

    /**
     * map the database fields to the object fields
     *
     * @param array|null $db_row with the data directly from the database
     * @param bool $load_std true if only the standard user sandbox object is loaded
     * @param bool $allow_usr_protect false for using the standard protection settings for the default object used for all users
     * @param string $id_fld the name of the id field as defined in this child and given to the parent
     * @param string $name_fld the name of the name field as defined in this child class
     * @return bool true if the view is loaded and valid
     */
    function row_mapper_sandbox(
        ?array $db_row,
        bool   $load_std = false,
        bool   $allow_usr_protect = true,
        string $id_fld = self::FLD_ID,
        string $name_fld = self::FLD_NAME
    ): bool
    {
        $result = parent::row_mapper_sandbox($db_row, $load_std, $allow_usr_protect, $id_fld, $name_fld);
        if ($result) {
            if (array_key_exists(self::FLD_TYPE, $db_row)) {
                $this->type_id = $db_row[self::FLD_TYPE];
            }
            if (array_key_exists(self::FLD_STYLE, $db_row)) {
                $this->set_style_by_id($db_row[self::FLD_STYLE]);
            }
            if (array_key_exists(sql::FLD_CODE_ID, $db_row)) {
                $this->code_id = $db_row[sql::FLD_CODE_ID];
            }
        }
        return $result;
    }

    /**
     * map a view api json to this model view object
     * similar to the import_obj function but using the database id instead of names as the unique key
     * @param array $api_json the api array with the word values that should be mapped
     * @return user_message the message for the user why the action has failed and a suggested solution
     */
    function api_mapper(array $api_json): user_message
    {
        $msg = parent::api_mapper($api_json);

        foreach ($api_json as $key => $value) {

            if ($key == json_fields::CODE_ID) {
                if ($value <> '') {
                    $this->code_id = $value;
                }
            }
            /* TODO review
            if ($key == exp_obj::FLD_VIEW) {
                $wrd_view = new view($this->user());
                if ($do_save) {
                    $wrd_view->load_by_name($value);
                    if ($wrd_view->id() == 0) {
                        $result->add_message('Cannot find view "' . $value . '" when importing ' . $this->dsp_id());
                    } else {
                        $this->view_id = $wrd_view->id();
                    }
                } else {
                    $wrd_view->set_name($value);
                }
                $this->view = $wrd_view;
            }

            */

        }

        return $msg;
    }

    /**
     * set the vars of this view object based on the given json without writing to the database
     * the code_id is not expected to be included in the im- and export because the internal views are not expected to be included in the ex- and import
     *
     * @param array $in_ex_json an array with the data of the json object
     * @param object|null $test_obj if not null the unit testing object
     * @return user_message the status of the import and if needed the error messages that should be shown to the user
     */
    function import_mapper(array $in_ex_json, data_object $dto = null, object $test_obj = null): user_message
    {
        log_debug();

        // reset the all parameters for the view object but keep the user
        $usr = $this->user();
        $this->reset();
        $this->set_user($usr);
        $usr_msg = parent::import_mapper($in_ex_json, $dto, $test_obj);

        // first save the parameters of the view itself
        if (key_exists(json_fields::TYPE_NAME, $in_ex_json)) {
            if ($in_ex_json[json_fields::TYPE_NAME] != '') {
                $type_id = $this->type_id_by_code_id($in_ex_json[json_fields::TYPE_NAME]);
                if ($type_id == type_list::CODE_ID_NOT_FOUND) {
                    $usr_msg->add_message('view type "'
                        . $in_ex_json[json_fields::TYPE_NAME] . '" not found');
                } else {
                    $this->type_id = $type_id;
                }
            }
        }
        if (key_exists(json_fields::CODE_ID, $in_ex_json)) {
            if ($in_ex_json[json_fields::CODE_ID] != '') {
                if ($this->user()->is_admin() or $this->user()->is_system()) {
                    $this->code_id = $in_ex_json[json_fields::CODE_ID];
                }
            }
        }

        // TODO get component from the dto object
        // TODO check if it is working that after saving (or remembering) add the view components
        if (key_exists(json_fields::COMPONENTS, $in_ex_json)) {
            $json_lst = $in_ex_json[json_fields::COMPONENTS];
            $cmp_pos = 1;
            foreach ($json_lst as $json_cmp) {
                $lnk = new component_link($usr);
                $lnk->import_mapper($json_cmp, $dto, $test_obj);
                $this->add_component($lnk, $cmp_pos);
                $cmp_pos++;
            }
        }

        // TODO add the assigned terms
        // after the view has it's components assign the view to the terms
        if (key_exists(json_fields::ASSIGNED, $in_ex_json)) {
            $value = $in_ex_json[json_fields::ASSIGNED];
            foreach ($value as $trm_name) {
                $trm = new term($this->user());
                $trm->load_by_name($trm_name);
                if ($trm->id() == 0) {
                    log_warning('word "' . $trm_name .
                        '" created to link it to view "' . $this->name() .
                        '" as requested by the import of ');
                }
                if ($trm->id() != 0) {
                    $this->add_term($trm);
                }
            }
        }

        if (!$usr_msg->is_ok()) {
            $lib = new library();
            $usr_msg->add_message(' when importing ' . $lib->dsp_array($in_ex_json));
        }

        return $usr_msg;
    }


    /*
     * api
     */

    /**
     * create an array for the api json creation
     * differs from the export array by using the internal id instead of the names
     * @param api_type_list $typ_lst configuration for the api message e.g. if phrases should be included
     * @param user|null $usr the user for whom the api message should be created which can differ from the session user
     * @return array the filled array used to create the api json message to the frontend
     */
    function api_json_array(api_type_list $typ_lst, user|null $usr = null): array
    {
        if ($this->is_excluded()) {
            $vars = [];
            $vars[json_fields::ID] = $this->id();
            $vars[json_fields::EXCLUDED] = true;
        } else {
            $vars = parent::api_json_array($typ_lst, $usr);
            $vars[json_fields::CODE_ID] = $this->code_id;
            if ($this->cmp_lnk_lst != null) {
                $vars[json_fields::COMPONENTS] = $this->cmp_lnk_lst->api_json_array($typ_lst);
            }
        }

        return $vars;
    }


    /*
     * im- and export
     */

    /**
     * import a view from a JSON object
     * the code_id is not expected to be included in the im- and export because the internal views are not expected to be included in the ex- and import
     *
     * @param array $in_ex_json an array with the data of the json object
     * @param object|null $test_obj if not null the unit testing object
     * @return user_message the status of the import and if needed the error messages that should be shown to the user
     */
    function import_obj(array $in_ex_json, object $test_obj = null): user_message
    {
        log_debug();

        // reset the all parameters for the word object but keep the user
        $usr = $this->user();
        $this->reset();
        $this->set_user($usr);
        $result = parent::import_mapper($in_ex_json, null, $test_obj);

        // first save the parameters of the view itself
        foreach ($in_ex_json as $key => $value) {

            if ($key == json_fields::TYPE_NAME) {
                if ($value != '') {
                    $type_id = $this->type_id_by_code_id($value);
                    if ($type_id == type_list::CODE_ID_NOT_FOUND) {
                        $result->add_message('view type "' . $value . '" not found');
                    } else {
                        $this->type_id = $type_id;
                    }
                }
            }
            if ($key == json_fields::CODE_ID) {
                if ($value != '') {
                    if ($this->user()->is_admin() or $this->user()->is_system()) {
                        $this->code_id = $value;
                    }
                }
            }
        }

        if (!$test_obj) {
            if ($this->name == '') {
                $result->add_message('name in view missing');
            } else {
                $result->add($this->save());

                if ($result->is_ok()) {
                    // TODO save also the links
                    //$dsp_lnk = new component_link();
                    log_debug($this->dsp_id());
                }
            }
        }

        // after saving (or remembering) add the view components
        foreach ($in_ex_json as $key => $value) {
            if ($key == self::FLD_COMPONENT) {
                $json_lst = $value;
                $cmp_pos = 1;
                foreach ($json_lst as $json_cmp) {
                    $cmp = new component($usr);
                    $style_code_id = null;
                    $pos_type_code_id = null;
                    // if for the component only the position and name is defined
                    // do not overwrite an existing component
                    // instead just add the existing component
                    if ((count($json_cmp) == 2
                            and array_key_exists(json_fields::POSITION, $json_cmp)
                            and array_key_exists(json_fields::NAME, $json_cmp))
                        or (count($json_cmp) == 3
                            and array_key_exists(json_fields::POSITION, $json_cmp)
                            and array_key_exists(json_fields::NAME, $json_cmp)
                            and array_key_exists(json_fields::POS_TYPE, $json_cmp))
                        or (count($json_cmp) == 3
                            and array_key_exists(json_fields::POSITION, $json_cmp)
                            and array_key_exists(json_fields::NAME, $json_cmp)
                            and array_key_exists(json_fields::STYLE, $json_cmp))
                        or (count($json_cmp) == 4
                            and array_key_exists(json_fields::POSITION, $json_cmp)
                            and array_key_exists(json_fields::NAME, $json_cmp)
                            and array_key_exists(json_fields::POS_TYPE, $json_cmp)
                            and array_key_exists(json_fields::STYLE, $json_cmp))) {
                        $cmp->load_by_name($json_cmp[json_fields::NAME]);
                        if (array_key_exists(json_fields::POS_TYPE, $json_cmp)) {
                            $pos_type_code_id = $json_cmp[json_fields::POS_TYPE];
                        }
                        if (array_key_exists(json_fields::STYLE
                            , $json_cmp)) {
                            $style_code_id = $json_cmp[json_fields::STYLE];
                        }
                        // if the component does not jet exist
                        // nevertheless create the component
                        // but send a warning message
                        if ($cmp->id() <= 0) {
                            log_warning('Component ' . $json_cmp[json_fields::NAME]
                                . ' has not yet been created, but is supposed to be at position '
                                . $json_cmp[json_fields::POSITION] . ' of a view ');
                            $cmp->import_obj($json_cmp, $test_obj);
                        }
                    } else {
                        log_warning('overwriting the component by the view');
                        $cmp->import_obj($json_cmp, $test_obj);
                    }
                    // on import first add all view components to the view object and save them all at once
                    // TODO overwrite the style or position type
                    $result->add_message($this->save_component($cmp, $cmp_pos, $pos_type_code_id, $style_code_id, $test_obj));
                    $cmp_pos++;
                }
            }
        }

        // TODO add the assigned terms
        // after the view has it's components assign the view to the terms
        foreach ($in_ex_json as $key => $value) {
            if ($key == json_fields::ASSIGNED) {
                foreach ($value as $trm_name) {
                    $trm = new term($this->user());
                    $trm->load_by_name($trm_name);
                    if ($trm->id() == 0) {
                        log_warning('word "' . $trm_name .
                            '" created to link it to view "' . $this->name() .
                            '" as requested by the import of ');
                    }
                    if ($trm->id() != 0) {
                        $this->add_term($trm);
                    }
                }
            }
        }

        if (!$result->is_ok()) {
            $lib = new library();
            $result->add_message(' when importing ' . $lib->dsp_array($in_ex_json));
        }

        return $result;
    }

    /**
     * create an array with the export json fields
     * @param bool $do_load true if any missing data should be loaded while creating the array
     * @return array with the json fields
     */
    function export_json(bool $do_load = true): array
    {
        $vars = parent::export_json($do_load);

        // add the view components used
        if ($do_load) {
            $this->load_components();
        }
        if ($this->cmp_lnk_lst != null) {
            $vars[json_fields::COMPONENTS] = $this->cmp_lnk_lst->export_json();
        }
        return $vars;
    }


    /*
     * set and get
     */

    /**
     * set the most used object vars with one set statement
     * @param int $id mainly for test creation the database id of the view
     * @param string $name mainly for test creation the name of the view
     * @param string $type_code_id the code id of the predefined view type
     */
    function set(int $id = 0, string $name = '', string $type_code_id = ''): void
    {
        parent::set($id, $name);

        if ($type_code_id != '') {
            $this->set_type($type_code_id);
        }
    }

    /**
     * set the view type
     *
     * @param string $type_code_id the code id that should be added to this view
     * @return void
     */
    function set_type(string $type_code_id): void
    {
        global $msk_typ_cac;
        $this->type_id = $msk_typ_cac->id($type_code_id);
    }

    /**
     * set the default style for this view by the code id
     *
     * @param string|null $code_id the code id of the display style use for im and export
     * @return void
     */
    function set_style(?string $code_id): void
    {
        global $msk_sty_cac;
        if ($code_id == null) {
            $this->style = null;
        } else {
            $this->style = $msk_sty_cac->get_by_code_id($code_id);
        }
    }

    /**
     * set the default style for this view by the database id
     *
     * @param int|null $style_id the database id of the display style
     * @return void
     */
    function set_style_by_id(?int $style_id): void
    {
        global $msk_sty_cac;
        if ($style_id == null) {
            $this->style = null;
        } else {
            $this->style = $msk_sty_cac->get($style_id);
        }
    }

    /**
     * @return view_style|type_object|null the view style for this component or null if the parent style should be used
     */
    function style(): view_style|type_object|null
    {
        return $this->style;
    }

    /**
     * @return int|null the database id of the view style or null
     */
    function style_id(): ?int
    {
        return $this->style?->id();
    }

    /**
     * @return string a unique name for the view that is also used in the code
     */
    function code_id(): string
    {
        if ($this->code_id == null) {
            return '';
        } else {
            return $this->code_id;
        }
    }

    /**
     * @return string the description of the view
     */
    function comment(): string
    {
        if ($this->description == null) {
            return '';
        } else {
            return $this->description;
        }
    }

    /**
     * @return component_link_list the list of the component links of this view
     */
    function component_link_list(): component_link_list
    {
        return $this->cmp_lnk_lst;
    }

    /**
     * @return component_list the list of the linked components of this view
     */
    function components(): component_list
    {
        $ids = $this->cmp_lnk_lst->cmp_ids();
        $cmp_lst = new component_list($this->user());
        $cmp_lst->load_by_ids($ids);
        return $cmp_lst;
    }

    /**
     * @return int the number of linked components of this view
     */
    function component_links(): int
    {
        return $this->component_link_list()->count();
    }


    /*
     * preloaded
     */

    /**
     * @return string the name of the view type
     */
    function type_name(): string
    {
        global $msk_typ_cac;
        return $msk_typ_cac->name($this->type_id);
    }

    /**
     * get the view type code id based on the database id set in this object
     * @return string
     */
    private function type_code_id(): string
    {
        global $msk_typ_cac;
        return $msk_typ_cac->code_id($this->type_id);
    }

    /**
     * get the view type database id based on the code id
     * @param string $code_id
     * @return int
     */
    private function type_id_by_code_id(string $code_id): int
    {
        global $msk_typ_cac;
        return $msk_typ_cac->id($code_id);
    }


    /*
     * load
     */

    /**
     * load a view by code id
     * @param string $code_id the code id of the view
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_code_id(string $code_id): int
    {
        global $db_con;

        log_debug($code_id);
        $sc = $db_con->sql_creator();
        $qp = $this->load_sql_by_code_id($sc, $code_id);
        return parent::load($qp);
    }

    /**
     * load the suggested view for a phrase
     * @param phrase $phr the phrase for which the most often used view should be loaded
     * @return bool true if at least one view is found
     */
    function load_by_phrase(phrase $phr): bool
    {
        global $db_con;

        $sc = $db_con->sql_creator();
        $qp = $this->load_sql_by_term($sc, $phr->term());
        $db_view = $db_con->get1($qp);
        return $this->row_mapper_sandbox($db_view);
    }

    /**
     * load the suggested view for a term
     * @param term $trm the word, triple, verb or formula for which the most often used view should be loaded
     * @return bool true if at least one view is found
     */
    function load_by_term(term $trm): bool
    {
        global $db_con;

        $sc = $db_con->sql_creator();
        $qp = $this->load_sql_by_term($sc, $trm);
        $db_view = $db_con->get1($qp);
        return $this->row_mapper_sandbox($db_view);
    }

    /**
     * load the view parameters for all users including the user id to know the owner of the standard
     * @param sql_par|null $qp placeholder to align the function parameters with the parent
     * @return bool true if the standard view has been loaded
     */
    function load_standard(?sql_par $qp = null): bool
    {

        global $db_con;
        $qp = $this->load_standard_sql($db_con->sql_creator());
        $result = parent::load_standard($qp);

        if ($result) {
            $result = $this->load_owner();
        }
        return $result;
    }

    /**
     * create an SQL statement to retrieve a view by code id from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $code_id the code id of the view
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_code_id(sql_creator $sc, string $code_id): sql_par
    {
        $qp = $this->load_sql($sc, 'code_id', $this::class);
        $sc->add_where(sql::FLD_CODE_ID, $code_id);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a view by the phrase from the database
     * TODO include user_view_term_links into the selection
     * TODO take the usage into account for the selection of the view
     *
     * @param sql_creator $sc with the target db_type set
     * @param term $trm the code id of the view
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_term(sql_creator $sc, term $trm, string $class = self::class): sql_par
    {
        $qp = $this->load_sql($sc, 'term', $class);
        $sc->set_join_fields(
            view_term_link::FLD_NAMES,
            view_term_link::class,
            view::FLD_ID,
            view::FLD_ID);
        $sc->add_where(term::FLD_ID, $trm->id(), null, sql_db::LNK_TBL);
        //$sc->set_order(component_link::FLD_ORDER_NBR, '', sql_db::LNK_TBL);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * create the common part of an SQL statement to retrieve the parameters of a view from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name extension to make the query name unique
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql_creator $sc, string $query_name): sql_par
    {
        $sc->set_class($this::class);
        return parent::load_sql_fields(
            $sc, $query_name,
            self::FLD_NAMES,
            self::FLD_NAMES_USR,
            self::FLD_NAMES_NUM_USR
        );
    }

    /**
     * create the SQL to load the default view always by the id
     *
     * @param sql_creator $sc with the target db_type set
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_standard_sql(sql_creator $sc): sql_par
    {
        $sc->set_class($this::class);
        $sc->set_fields(array_merge(
            self::FLD_NAMES,
            self::FLD_NAMES_USR,
            self::FLD_NAMES_NUM_USR,
            array(user::FLD_ID)
        ));

        return parent::load_standard_sql($sc);
    }


    /*
     * load related
     */

    /**
     * load all parts of this view for this user
     * @param sql_db|null $db_con_given the database connection as a parameter for the initial load of the system views
     * @return bool false if a technical error on loading has occurred; an empty list if fine and returns true
     */
    function load_components(?sql_db $db_con_given = null): bool
    {
        global $db_con;

        log_debug();
        $db_con_used = $db_con_given;
        if ($db_con_used == null) {
            $db_con_used = $db_con;
        }

        $this->cmp_lnk_lst = new component_link_list($this->user());
        $result = $this->cmp_lnk_lst->load_by_view_with_components($this, $db_con_used);
        log_debug($this->cmp_lnk_lst->count() . ' loaded for ' . $this->dsp_id());

        return $result;
    }

    /**
     * create an SQL statement to retrieve all view components of a view
     * TODO check if it can be combined with load_sql from component_link_list
     * TODO make the order user specific
     *
     * @param sql_db $db_con as a function parameter for unit testing
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_components_sql(sql_db $db_con): sql_par
    {
        $qp = new sql_par(component::class);
        if ($this->id() != 0) {
            $qp->name .= 'view_id';
        } elseif ($this->name != '') {
            $qp->name .= sql_db::FLD_NAME;
        } else {
            log_err("Either the database ID (" . $this->id() . "), the view name (" . $this->name . ") or the code_id (" . $this->code_id . ")  must be set to load the components of a view.", "view->load_components_sql");
        }

        $db_con->set_class(component_link::class);
        $db_con->set_usr($this->user()->id());
        $db_con->set_name($qp->name);
        $db_con->set_fields(component_link::FLD_NAMES);
        $db_con->set_usr_num_fields(component_link::FLD_NAMES_NUM_USR);
        $db_con->set_join_usr_fields(
            array_merge(component::FLD_NAMES_USR, array(component::FLD_NAME)),
            component::class);
        $db_con->set_join_usr_num_fields(
            component::FLD_NAMES_NUM_USR,
            component::class);
        $db_con->add_par(sql_par_type::INT, $this->id());
        $db_con->set_order(component_link::FLD_ORDER_NBR);
        $qp->sql = $db_con->select_by_field_list(array(view::FLD_ID));
        $qp->par = $db_con->get_par();

        return $qp;
    }


    /*
     * load helper
     */

    /**
     * @return string the field name of the name db field as a function for complex overwrites
     */
    function name_field(): string
    {
        return self::FLD_NAME;
    }

    /**
     * @return array with all db field names as a function for complex overwrites
     */
    function all_sandbox_fields(): array
    {
        return self::ALL_SANDBOX_FLD_NAMES;
    }

    /**
     * @param sql_creator $sc the sql creator without view joins
     * @return sql_creator the sql creator with the view join set
     */
    function set_join(sql_creator $sc): sql_creator
    {
        $sc->set_join_fields(view::FLD_NAMES, view::class);
        $sc->set_join_usr_fields(view::FLD_NAMES_USR, view::class);
        $sc->set_join_usr_num_fields(view::FLD_NAMES_NUM_USR, view::class);
        return $sc;
    }


    /*
     * components
     */

    /**
     * add a new component to this view
     * @param component_link $lnk the component link with the component object
     * @return user_message an empty string if the new component link has been saved to the database
     *                      or the message that should be shown to the user
     */
    function add_component(component_link $lnk, int $pos = null): user_message
    {
        $result = new user_message();

        // if no position is requested add the component at the end
        if ($lnk->pos() == null) {
            if ($pos != null) {
                $lnk->set_pos($pos);
            } else {
                $lnk->set_pos($this->component_links() + 1);
            }
        }
        if ($lnk->pos_type() == null) {
            $lnk->set_pos_type(position_types::BELOW);
        }
        if ($this->cmp_lnk_lst == null) {
            $this->cmp_lnk_lst = new component_link_list($this->user());
        }
        $lnk->set_view($this);
        $this->cmp_lnk_lst->add_link_by_name($lnk);

        return $result;
    }

    /**
     * save a new component to the database and add it to this view
     * @param component $cmp the view component that should be added
     * @param int|null $pos is set the position, where the
     * @param object|null $test_obj if not null the unit test object to get a dummy seq id
     * @return string an empty string if the new component link has been saved to the database
     *                or the message that should be shown to the user
     */
    function save_component(
        component $cmp,
        ?int      $pos = null,
        ?string   $pos_type_code_id = null,
        ?string   $style_code_id = null,
        object    $test_obj = null
    ): string
    {
        $result = '';

        // if no position is requested add the component at the end
        if ($pos == null) {
            $pos = $this->component_links() + 1;
        }
        if ($pos_type_code_id == null) {
            $pos_type_code_id = position_types::BELOW;
        }
        if ($pos != null) {
            if ($this->cmp_lnk_lst == null) {
                $this->cmp_lnk_lst = new component_link_list($this->user());
            }
            if (!$test_obj) {
                $cmp->save();
                $cmp_lnk = new component_link($this->user());
                $cmp_lnk->reset();
                $cmp_lnk->view()->set_id($this->id());
                $cmp_lnk->component()->set_id($cmp->id());
                $cmp_lnk->order_nbr = $pos;
                $cmp_lnk->set_pos_type($pos_type_code_id);
                $cmp_lnk->set_style($style_code_id);
                $cmp_lnk->save();
                $this->cmp_lnk_lst->add($cmp_lnk->id(), $this, $cmp, $pos);
            } else {
                $this->cmp_lnk_lst->add($pos, $this, $cmp, $pos);
            }
        }
        // compare with the database links and save the differences

        return $result;
    }

    /**
     * move one view component one place up
     * in case of an error the error message is returned
     * if everything is fine an empty string is returned
     */
    function entry_up($component_id): string
    {
        $result = '';
        // check the all minimal input parameters
        if ($component_id <= 0) {
            log_err("The view component id must be given to move it.", "view->entry_up");
        } else {
            $cmp = new component($this->user());
            $cmp->load_by_id($component_id);
            $cmp_lnk = new component_link($this->user());
            $cmp_lnk->load_by_link($this, $cmp);
            $result .= $cmp_lnk->move_up();
        }
        return $result;
    }

    /**
     * move one view component one place down
     */
    function entry_down($component_id): string
    {
        $result = '';
        // check the all minimal input parameters
        if ($component_id <= 0) {
            log_err("The view component id must be given to move it.", "view->entry_down");
        } else {
            $cmp = new component($this->user());
            $cmp->load_by_id($component_id);
            $cmp_lnk = new component_link($this->user());
            $cmp_lnk->load_by_link($this, $cmp);
            $result .= $cmp_lnk->move_down();
        }
        return $result;
    }


    /*
     * assign
     */

    /**
     * links this view to a term
     * @param term $trm the term that should be linked
     * @return user_message with the message to the user if something has gone wrong and the suggested solutions
     */
    function add_term(term $trm): user_message
    {
        $usr_msg = new user_message();
        $lnk = new view_term_link($this->user());
        $lnk->set_view($this);
        $lnk->set_term($trm);
        $usr_msg->add($lnk->save());
        return $usr_msg;
    }

    /**
     * unlinks this view from the given term
     * @param term $trm the term that should be removed from the list of assigned terms
     * @return user_message with the message to the user if something has gone wrong and the suggested solutions
     */
    function del_term(term $trm): user_message
    {
        $usr_msg = new user_message();
        // TODO implement
        $usr_msg->add_message('not yet implemented');
        return $usr_msg;
    }


    /*
     * information
     */

    /**
     * true if the view is part of the view element list
     */
    function is_in_list($dsp_lst): bool
    {
        $result = false;

        foreach ($dsp_lst as $dsp_id) {
            log_debug($dsp_id . ' = ' . $this->id() . '?');
            if ($dsp_id == $this->id()) {
                $result = true;
            }
        }

        return $result;
    }


    /*
     * save
     */

    /**
     * create an SQL statement to retrieve the user changes of the current view
     *
     * @param sql_creator $sc with the target db_type set
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation e.g. standard for values and results
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_user_changes(
        sql_creator   $sc,
        sql_type_list $sc_par_lst = new sql_type_list()
    ): sql_par
    {
        $sc->set_class($this::class, new sql_type_list([sql_type::USER]));
        $sc->set_fields(array_merge(
            self::FLD_NAMES_USR,
            self::FLD_NAMES_NUM_USR
        ));
        return parent::load_sql_user_changes($sc, $sc_par_lst);
    }

    /**
     * set the update parameters for the view code_id (only allowed for admin)
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @param view $db_rec the database record before the saving
     * @param view $std_rec the database record defined as standard because it is used by most users
     * @return user_message the message that should be shown to the user in case something went wrong
     */
    function save_field_code_id(sql_db $db_con, view $db_rec, view $std_rec): user_message
    {
        $usr_msg = new user_message;
        // special case: do not remove a code id
        if ($this->code_id != '') {
            if ($db_rec->code_id <> $this->code_id) {
                $log = $this->log_upd();
                $log->old_value = $db_rec->code_id;
                $log->new_value = $this->code_id;
                $log->std_value = $std_rec->code_id;
                $log->row_id = $this->id();
                $log->set_field(sql::FLD_CODE_ID);
                $usr_msg->add($this->save_field_user($db_con, $log));
            }
        }
        return $usr_msg;
    }

    /**
     * save all updated view fields excluding the name, because already done when adding a view
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @param view|sandbox $db_obj the database record before the saving
     * @param view|sandbox $norm_obj the database record defined as standard because it is used by most users
     * @return user_message the message that should be shown to the user in case something went wrong
     */
    function save_all_fields(sql_db $db_con, view|sandbox $db_obj, view|sandbox $norm_obj): user_message
    {
        $usr_msg = parent::save_fields_typed($db_con, $db_obj, $norm_obj);
        $usr_msg->add($this->save_field_code_id($db_con, $db_obj, $norm_obj));
        log_debug('all fields for ' . $this->dsp_id() . ' has been saved');
        return $usr_msg;
    }


    /*
     * save helper
     */

    /**
     * @return array with the reserved view names
     */
    protected function reserved_names(): array
    {
        return views::RESERVED_NAMES;
    }

    /**
     * @return array with the fixed view names for db read testing
     */
    protected function fixed_names(): array
    {
        return views::FIXED_NAMES;
    }

    /**
     * delete the view component links of linked to this view
     * @return user_message of the link removal and if needed the error messages that should be shown to the user
     */
    function del_links(): user_message
    {
        $usr_msg = new user_message();

        // collect all component links where this view is used
        $lnk_lst = new component_link_list($this->user());
        $lnk_lst->load_by_view($this);

        // if there are links, delete if not used by anybody else than the user who has requested the deletion
        // or exclude the links for the user if the link is used by someone else
        if (!$lnk_lst->is_empty()) {
            $usr_msg->add($lnk_lst->del());
        }

        return $usr_msg;
    }


    /*
     * sql write fields
     */

    /**
     * get a list of all database fields that might be changed
     * excluding the internal fields e.g. the database id
     * field list must be corresponding to the db_fields_changed fields
     *
     * @param sql_type_list $sc_par_lst only used for link objects
     * @return array list of all database field names that have been updated
     */
    function db_fields_all(sql_type_list $sc_par_lst = new sql_type_list()): array
    {
        return array_merge(
            parent::db_fields_all(),
            [
                view::FLD_TYPE,
                view::FLD_STYLE,
                sql::FLD_CODE_ID
            ],
            parent::db_fields_all_sandbox()
        );
    }

    /**
     * get a list of database field names, values and types that have been updated
     *
     * @param sandbox|view $sbx the compare value to detect the changed fields
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par_field_list list 3 entry arrays with the database field name, the value and the sql type that have been updated
     */
    function db_fields_changed(
        sandbox|view  $sbx,
        sql_type_list $sc_par_lst = new sql_type_list()
    ): sql_par_field_list
    {
        global $cng_fld_cac;

        $sc = new sql_creator();
        $do_log = $sc_par_lst->incl_log();
        $table_id = $sc->table_id($this::class);

        $lst = parent::db_fields_changed($sbx, $sc_par_lst);
        if ($sbx->type_id() <> $this->type_id()) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . view::FLD_TYPE,
                    $cng_fld_cac->id($table_id . view::FLD_TYPE),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            global $msk_typ_cac;
            $lst->add_type_field(
                view::FLD_TYPE,
                type_object::FLD_NAME,
                $this->type_id(),
                $sbx->type_id(),
                $msk_typ_cac
            );
        }
        if ($sbx->style_id() <> $this->style_id()) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . self::FLD_STYLE,
                    $cng_fld_cac->id($table_id . self::FLD_STYLE),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            global $msk_sty_cac;
            // TODO move to id function of type list
            if ($this->style_id() < 0) {
                log_err('view style for ' . $this->dsp_id() . ' not found');
            }
            $lst->add_type_field(
                self::FLD_STYLE,
                view_style::FLD_NAME,
                $this->style_id(),
                $sbx->style_id(),
                $msk_sty_cac
            );
        }
        if ($sbx->code_id <> $this->code_id) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . sql::FLD_CODE_ID,
                    $cng_fld_cac->id($table_id . sql::FLD_CODE_ID),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                sql::FLD_CODE_ID,
                $this->code_id,
                sql::FLD_CODE_ID_SQL_TYP,
                $sbx->code_id
            );
        }
        return $lst->merge($this->db_changed_sandbox_list($sbx, $sc_par_lst));
    }


    /*
     * display
     * TODO to be moved to the frontend object
     */

    /**
     * return the html code to display a view name with the link
     */
    function name_linked($wrd, $back): string
    {

        $result = '<a href="/http/view_edit.php?id=' . $this->id();
        if (isset($wrd)) {
            $result .= '&word=' . $wrd->id();
        }
        $result .= '&back=' . $back . '">' . $this->name . '</a>';

        return $result;
    }

    /**
     * display the unique id fields
     */
    function name_dsp(): string
    {
        return '"' . $this->name . '"';
    }

}
