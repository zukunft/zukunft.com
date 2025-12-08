<?php

/*

    model/component/component_link.php - link a single display component/element to a view
    ----------------------------------

    The main sections of this object are
    - db const:          const for the database link
    - object vars:       the variables of this word object
    - construct and map: including the mapping of the db row to this word object
    - api:               create an api array for the frontend and set the vars based on a frontend api message
    - set and get:       to capsule the vars from unexpected changes
    - preloaded:         select e.g. types from cache
    - cast:              create an api object and set the vars from an api json
    - load:              database access object (DAO) functions
    - modify:            change the order
    - save:              manage to update the database
    - sql write fields:  field list for writing to the database
    - debug:             internal support functions for debugging

    TODO  if a link is owned by someone, who has deleted it, it can be changed by anyone else
          or another way to formulate this: if the owner deletes a link, the ownership should be move to the remaining users
          force to remove all user settings to be able to delete a link as an admin


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

namespace Zukunft\ZukunftCom\main\php\cfg\component;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_field_default.php';
include_once paths::DB . 'sql_field_type.php';
include_once paths::DB . 'sql_par.php';
include_once paths::DB . 'sql_par_field_list.php';
include_once paths::DB . 'sql_par_type.php';
include_once paths::DB . 'sql_type.php';
include_once paths::DB . 'sql_type_list.php';
include_once paths::MODEL_CONST . 'def.php';
include_once paths::EXPORT . 'export_type_list.php';
include_once paths::MODEL_LOG . 'change.php';
include_once paths::MODEL_HELPER . 'data_object.php';
include_once paths::MODEL_HELPER . 'db_object_seq_id.php';
include_once paths::MODEL_SANDBOX . 'sandbox.php';
include_once paths::MODEL_SANDBOX . 'sandbox_link.php';
include_once paths::MODEL_SANDBOX . 'sandbox_named.php';
include_once paths::MODEL_HELPER . 'type_object.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_db.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::MODEL_VIEW . 'view.php';
include_once paths::MODEL_VIEW . 'view_db.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_HELPER . 'CombineObject.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';
include_once paths::SHARED_TYPES . 'position_types.php';
include_once paths::SHARED_TYPES . 'view_styles.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'library.php';

use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\main\php\cfg\db\sql;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_default;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par_field_list;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type_list;
use Zukunft\ZukunftCom\main\php\cfg\export\export_type_list;
use Zukunft\ZukunftCom\main\php\cfg\helper\data_object;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_object_seq_id;
use Zukunft\ZukunftCom\main\php\cfg\log\change;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_link;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_named;
use Zukunft\ZukunftCom\main\php\cfg\helper\type_object;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_db;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\view\view;
use Zukunft\ZukunftCom\main\php\cfg\view\view_db;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\helper\CombineObject;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\types\position_types;
use Zukunft\ZukunftCom\main\php\shared\types\view_styles;

class component_link extends sandbox_link
{

    /*
     * db const
     */

    // the database and JSON object field names used only for formula links
    const string TBL_COMMENT = 'to link components to views with an n:m relation';
    const string FLD_ID = 'component_link_id';
    const string FLD_ORDER_NBR = 'order_nbr';
    const sql_field_type FLD_ORDER_NBR_SQL_TYP = sql_field_type::INT;
    const string FLD_POS_COM = 'the position of the component e.g. right or below';
    const string FLD_POS_TYPE = 'position_type_id';
    const string FLD_POS_TYPE_NAME = 'position'; // for log only
    const string FLD_STYLE_COM = 'the display style for this component link';
    const string FLD_STYLE = 'view_style_id';

    // all database field names excluding the user specific fields and the id
    const array FLD_NAMES = array(
        view_db::FLD_ID,
        component::FLD_ID
    );
    // list of the link database field names
    const array FLD_NAMES_LINK = array(
        view_db::FLD_ID,
        component::FLD_ID
    );
    // list of the user specific database field names
    const array FLD_NAMES_NUM_USR = array(
        self::FLD_ORDER_NBR,
        self::FLD_POS_TYPE,
        self::FLD_STYLE,
        sql_db::FLD_EXCLUDED,
        sandbox::FLD_SHARE,
        sandbox::FLD_PROTECT
    );
    // all database field names excluding the id used to identify if there are some user specific changes
    const array ALL_SANDBOX_FLD_NAMES = array(
        self::FLD_ORDER_NBR,
        self::FLD_POS_TYPE,
        self::FLD_STYLE,
        sql_db::FLD_EXCLUDED,
        sandbox::FLD_SHARE,
        sandbox::FLD_PROTECT
    );
    // list of fields that select the objects that should be linked
    const array FLD_LST_LINK = array(
        [view_db::FLD_ID, sql_field_type::INT, sql_field_default::NOT_NULL, sql::INDEX, view::class, ''],
        [component::FLD_ID, sql_field_type::INT, sql_field_default::NOT_NULL, sql::INDEX, component::class, ''],
    );
    // list of MANDATORY fields that CANNOT be CHANGED by the user
    const array FLD_LST_MUST_BUT_STD_ONLY = array(
        [self::FLD_ORDER_NBR, self::FLD_ORDER_NBR_SQL_TYP, sql_field_default::ONE, '', '', ''],
        [component_link_type::FLD_ID, type_object::FLD_ID_SQL_TYP, sql_field_default::ONE, sql::INDEX, component_link_type::class, ''],
        [position_type::FLD_ID, type_object::FLD_ID_SQL_TYP, sql_field_default::ONE, sql::INDEX, position_type::class, self::FLD_POS_COM],
        [self::FLD_STYLE, type_object::FLD_ID_SQL_TYP, sql_field_default::NULL, sql::INDEX, view_style::class, self::FLD_STYLE_COM],
    );
    // list of fields that CAN be CHANGED by the user
    const array FLD_LST_MUST_BUT_USER_CAN_CHANGE = array(
        [self::FLD_ORDER_NBR, self::FLD_ORDER_NBR_SQL_TYP, sql_field_default::NULL, '', '', ''],
        [component_link_type::FLD_ID, type_object::FLD_ID_SQL_TYP, sql_field_default::NULL, sql::INDEX, component_link_type::class, ''],
        [position_type::FLD_ID, type_object::FLD_ID_SQL_TYP, sql_field_default::NULL, sql::INDEX, position_type::class, self::FLD_POS_COM],
        [self::FLD_STYLE, type_object::FLD_ID_SQL_TYP, sql_field_default::NULL, sql::INDEX, view_style::class, self::FLD_STYLE_COM],
    );


    // default const
    const int START_ORDER_NBR = 1;


    /*
     * object vars
     */

    // to sort the display item
    public ?int $order_nbr = null;

    // defines the position of the view component relative to the previous item (1 = below, 2= side, )
    private ?type_object $pos_type = null;

    // the default display style for this component which can be overwritten by the link
    private ?type_object $style = null;


    /*
     * construct and map
     */

    function __construct(user $usr)
    {
        parent::__construct($usr);

        // TODO deprecate and use the object name instead
        $lib = new library();
        $this->from_name = $lib->class_to_name(view::class);
        $this->to_name = $lib->class_to_name(component::class);

        $this->rename_can_switch = def::UI_CAN_CHANGE_VIEW_COMPONENT_LINK;

        $this->reset_objects($usr);
    }

    function reset(bool $keep_user = false): void
    {
        parent::reset($keep_user);

        $this->reset_objects($this->get_user());

        // set the default values
        $this->set_predicate(component_link_type::ALWAYS);
        $this->set_pos(null);
        $this->set_pos_type(position_types::BELOW);
        $this->set_style(null);

        $this->order_nbr = null;
    }

    /**
     * reset the in memory fields used e.g. if some ids are updated
     * @param user $usr the user for whom this link is valid
     */
    private function reset_objects(user $usr): void
    {
        // assign the object specific objects to the standard link object
        // to enable the usage of the standard user sandbox link function for this view component link object
        $this->set_view(new view($usr)); // the display (view) object (used to save the correct name in the log)
        $this->set_component(new component($usr)); // the display component (view entry) object (used to save the correct name in the log)
    }

    /**
     * map the database fields to the object fields
     *
     * @param array|null $db_row with the data directly from the database
     * @param bool $load_std true if only the standard user sandbox object is loaded
     * @param bool $allow_usr_protect false for using the standard protection settings for the default object used for all users
     * @param string $id_fld the name of the id field as defined in this child and given to the parent
     * @return bool true if the view component link is loaded and valid
     */
    function row_mapper_sandbox(
        ?array $db_row,
        bool   $load_std = false,
        bool   $allow_usr_protect = true,
        string $id_fld = self::FLD_ID): bool
    {
        $result = parent::row_mapper_sandbox($db_row, $load_std, $allow_usr_protect, self::FLD_ID);
        if ($result) {
            $this->set_view(new view($this->get_user()));
            $this->get_view()->id = $db_row[view_db::FLD_ID];
            $this->set_component(new component($this->get_user()));
            $this->get_component()->id = $db_row[component::FLD_ID];
            $this->order_nbr = $db_row[self::FLD_ORDER_NBR];
            $this->set_pos_type_by_id($db_row[self::FLD_POS_TYPE]);
            $this->set_style_by_id($db_row[self::FLD_STYLE]);
        }
        return $result;
    }

    /**
     * map a component api json to this model component link object
     * @param array $api_json the api array with the values that should be mapped
     * @param user_message $usr_msg the message for the user why the action has failed and a suggested solution
     * @return bool true if the mapping has been completed successful
     */
    function api_mapper(array $api_json, user_message $usr_msg): bool
    {
        parent::api_mapper($api_json, $usr_msg);

        if (array_key_exists(json_fields::COMPONENT_ID, $api_json)) {
            $this->set_component(new component($this->get_user()));
            $this->get_component()->id = $api_json[json_fields::COMPONENT_ID];
        }
        if (array_key_exists(json_fields::POSITION, $api_json)) {
            $this->order_nbr = $api_json[json_fields::POSITION];
        }
        if (array_key_exists(json_fields::POS_TYPE, $api_json)) {
            $this->set_pos_type_by_id($api_json[json_fields::POS_TYPE]);
        }
        if (array_key_exists(json_fields::STYLE, $api_json)) {
            $this->set_style_by_id($api_json[json_fields::STYLE]);
        }

        return $usr_msg->is_ok();
    }

    /**
     * set the vars of this component link object based on the given json without writing to the database
     * the code_id is not expected to be included in the im- and export because the internal views are not expected to be included in the ex- and import
     *
     * @param array $in_ex_json an array with the data of the json object
     * @param user_message $usr_msg to enrich with warnings, problems and solutions
     * @param data_object|null $dto the data object that contains the already imported components
     * @return bool true if everything was fine
     */
    function import_mapper(
        array        $in_ex_json,
        user_message $usr_msg,
        ?data_object $dto = null
    ): bool
    {
        global $db_con;

        // reset the all parameters for the view object but keep the user
        $this->reset(true);
        parent::import_mapper($in_ex_json, $usr_msg, $dto);

        // if for the component only the position and name is defined
        // do not overwrite an existing component
        // instead just add the existing component
        if ((count($in_ex_json) == 2
                and array_key_exists(json_fields::POSITION, $in_ex_json)
                and array_key_exists(json_fields::NAME, $in_ex_json))
            or (count($in_ex_json) == 3
                and array_key_exists(json_fields::POSITION, $in_ex_json)
                and array_key_exists(json_fields::NAME, $in_ex_json)
                and array_key_exists(json_fields::POS_TYPE, $in_ex_json))
            or (count($in_ex_json) == 3
                and array_key_exists(json_fields::POSITION, $in_ex_json)
                and array_key_exists(json_fields::NAME, $in_ex_json)
                and array_key_exists(json_fields::STYLE, $in_ex_json))
            or (count($in_ex_json) == 4
                and array_key_exists(json_fields::POSITION, $in_ex_json)
                and array_key_exists(json_fields::NAME, $in_ex_json)
                and array_key_exists(json_fields::POS_TYPE, $in_ex_json)
                and array_key_exists(json_fields::STYLE, $in_ex_json))) {

            // get component from dto by name
            $cmp = $dto?->get_component_by_name($in_ex_json[json_fields::NAME]);
            if ($cmp == null) {
                if ($db_con->is_open()) {
                    $usr_msg->add_id_with_vars(msg_id::COMPONENT_MISSING, [
                        msg_id::VAR_COMPONENT_NAME => $in_ex_json[json_fields::NAME],
                        msg_id::VAR_JSON_TEXT => json_encode($in_ex_json)
                    ]);
                }
                $cmp = new component($usr_msg->usr);
                $cmp->set_name($in_ex_json[json_fields::NAME]);
            }
            $this->set_component($cmp);

        } elseif (array_key_exists(json_fields::NAME, $in_ex_json)) {
            // assign components just be the name to a view
            $usr_msg->add_id_with_vars(msg_id::COMPONENT_CREATED, [
                msg_id::VAR_COMPONENT_NAME => $in_ex_json[json_fields::NAME]
            ]);
            $cmp = new component($usr_msg->usr);
            $cmp->import_mapper($in_ex_json, $usr_msg, $dto);
        } elseif (array_key_exists(json_fields::VIEW, $in_ex_json)
            and array_key_exists(json_fields::COMPONENT, $in_ex_json)) {
            // import a component link independent of the view

            // import the view
            // TODO Prio 1 move as function "get_from_import_json" to the view object
            if (array_key_exists(json_fields::VIEW, $in_ex_json)) {
                $msk_json = $in_ex_json[json_fields::VIEW];
                if (is_array($msk_json)) {
                    if (count($msk_json) == 1 and array_key_exists(json_fields::NAME, $msk_json)) {
                        $msk_json = $msk_json[json_fields::NAME];
                    }
                }
                if (is_string($msk_json)) {
                    $msk = $dto?->get_view_by_name($msk_json);
                    if ($msk == null) {
                        $usr_msg->add_id_with_vars(msg_id::VIEW_MISSING_IMPORT, [
                            msg_id::VAR_VIEW => $msk_json,
                            msg_id::VAR_JSON_TEXT => json_encode($in_ex_json)
                        ]);
                        $msk = new view($usr_msg->usr);
                        $msk->set_name($msk_json);
                    }
                    $this->set_view($msk);
                } elseif (is_array($msk_json)) {
                    $msk = new view($usr_msg->usr);
                    $msk->import_mapper($msk_json, $usr_msg, $dto);
                    if ($usr_msg->is_ok()) {
                        $this->set_view($msk);
                    }
                }
            } else {
                $usr_msg->add_info_with_vars(msg_id::VIEW_CREATED, [
                    msg_id::VAR_VIEW_NAME => $in_ex_json[json_fields::NAME]
                ]);
                $msk = new view($usr_msg->usr);
                $msk->import_mapper($in_ex_json, $usr_msg, $dto);
                $this->set_view($msk);
            }

            // import the component
            // TODO Prio 1 move as function "get_from_import_json" to the component object
            if (array_key_exists(json_fields::COMPONENT, $in_ex_json)) {
                $msk_json = $in_ex_json[json_fields::COMPONENT];
                if (is_array($msk_json)) {
                    if (count($msk_json) == 1 and array_key_exists(json_fields::NAME, $msk_json)) {
                        $msk_json = $msk_json[json_fields::NAME];
                    }
                }
                if (is_string($msk_json)) {
                    $msk = $dto?->get_component_by_name($msk_json);
                    if ($msk == null) {
                        $usr_msg->add_id_with_vars(msg_id::COMPONENT_MISSING_IMPORT, [
                            msg_id::VAR_COMPONENT => $msk_json,
                            msg_id::VAR_JSON_TEXT => json_encode($in_ex_json)
                        ]);
                        $msk = new component($usr_msg->usr);
                        $msk->set_name($msk_json);
                    }
                    $this->set_component($msk);
                } elseif (is_array($msk_json)) {
                    $msk = new component($usr_msg->usr);
                    $msk->import_mapper($msk_json, $usr_msg, $dto);
                    if ($usr_msg->is_ok()) {
                        $this->set_component($msk);
                    }
                }
            } else {
                $usr_msg->add_info_with_vars(msg_id::COMPONENT_CREATED, [
                    msg_id::VAR_COMPONENT_NAME => $in_ex_json[json_fields::NAME]
                ]);
                $msk = new component($usr_msg->usr);
                $msk->import_mapper($in_ex_json, $usr_msg, $dto);
                $this->set_component($msk);
            }

        } else {
            $msg = 'unexpected component link json format';
            log_err($msg);
        }

        if (array_key_exists(json_fields::PREDICATE, $in_ex_json)) {
            global $sys;
            $this->predicate_id = $sys->typ_lst->cmp_lnk_typ->id($in_ex_json[json_fields::PREDICATE]);;
        }

        // set the link position and type
        if (array_key_exists(json_fields::POSITION, $in_ex_json)) {
            $this->set_pos($in_ex_json[json_fields::POSITION]);
        }
        if (array_key_exists(json_fields::POS_TYPE, $in_ex_json)) {
            $this->set_pos_type($in_ex_json[json_fields::POS_TYPE]);
        }
        if (array_key_exists(json_fields::STYLE, $in_ex_json)) {
            $this->set_style($in_ex_json[json_fields::STYLE]);
        }

        return $usr_msg->is_ok();
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
        $vars = [];
        if (!$this->is_excluded() or $typ_lst->test_mode() or $typ_lst->with_excluded()) {

            $vars = parent::api_json_array($typ_lst, $usr);

            if ($typ_lst->link_details()) {
                // the full object detail version
                if ($this->get_view() != null) {
                    if ($typ_lst->include_views()) {
                        $vars[json_fields::VIEW] = $this->get_view()->api_json_array($typ_lst, $usr);
                    } else {
                        if ($this->get_view()->id() != 0) {
                            $vars[json_fields::VIEW_ID] = $this->get_view()->id();
                        }
                    }
                }
                if ($this->get_component() != null) {
                    if ($typ_lst->include_components()) {
                        $vars[json_fields::COMPONENT] = $this->get_component()->api_json_array($typ_lst, $usr);
                    } else {
                        if ($this->get_component()->id() != 0) {
                            $vars[json_fields::COMPONENT_ID] = $this->get_component()->id();
                        }
                    }
                }
            } else {
                // the single layer json array version
                if ($this->id() != 0) {
                    // move the id of the link to json field "link_id"
                    // so that the json field "id" can be used for the component id
                    $vars[json_fields::LINK_ID] = $this->id();
                    unset($vars[json_fields::ID]);
                }
                if ($typ_lst->include_components()) {
                    if ($this->get_component() != null) {
                        $vars = array_merge($vars, $this->get_component()->api_json_array($typ_lst, $usr));
                    }
                } else {
                    if ($this->get_component()->id() != 0) {
                        $vars[json_fields::ID] = $this->get_component()->id();
                    }
                }
            }

            // to order the component is only defined on the component link itself
            if ($this->order_nbr != component_link::START_ORDER_NBR or $this->id() != 0) {
                $vars[json_fields::POSITION] = $this->order_nbr;
            }
            // the position type is mainly defined on the component link but there is a default setting
            if ($this->get_pos_type_code_id() != position_types::DEFAULT or $this->id() != 0) {
                $vars[json_fields::POS_TYPE] = $this->get_pos_type_id();
            }
            // overwrite the style of the component with the style of the link
            if ($this->get_style_id() != null) {
                $vars[json_fields::STYLE] = $this->get_style_id();
            }

        } elseif ($this->is_excluded() and $typ_lst->with_excluded_id()) {
            if ($this->id() != 0) {
                $vars[json_fields::ID] = $this->id();
            }
            $vars[json_fields::EXCLUDED] = true;
        }

        return $vars;
    }


    /*
     * set and get
     */

    /**
     * set the most relevant vars of a component link with one function
     * @param int $id
     * @param view $msk
     * @param component $cmp
     * @param int $pos
     * @return void
     */
    function set(int $id, view $msk, component $cmp, int $pos): void
    {
        $this->reset();
        $this->id = $id;
        $this->set_view($msk);
        $this->set_component($cmp);
        $this->set_pos($pos);
    }

    /**
     * set the link type for this component to the linked view
     *
     * @param string $type_code_id the code id that should be added to this view component link
     * @return void
     */
    function set_predicate(string $type_code_id): void
    {
        global $sys;
        $this->predicate_id = $sys->typ_lst->cmp_lnk_typ->id($type_code_id);
    }

    /**
     * rename to standard link to object to view
     * @param view $msk
     */
    function set_view(view $msk): void
    {
        $this->set_fob($msk);
    }

    /**
     * rename to standard link to object to component
     * @param component $cmp
     */
    function set_component(component $cmp): void
    {
        $this->set_tob($cmp);
    }

    /**
     * rename to standard link to object to component
     * @param int $id
     */
    function set_component_id(int $id): void
    {
        $this->get_component()->id = $id;
    }

    /**
     * set the position of this link
     * @param int|null $pos
     */
    function set_pos(int|null $pos): void
    {
        $this->order_nbr = $pos;
    }

    /**
     * set the position type for the component in the linked view
     *
     * @param string $code_id the code id that should be added to this view component link
     * @return void
     */
    function set_pos_type(string $code_id): void
    {
        global $sys;
        if ($code_id == null) {
            $this->pos_type = null;
        } else {
            $this->pos_type = $sys->typ_lst->pos_typ->get_by_code_id($code_id);
        }
    }

    /**
     * set the position type for the component in the linked view by the database id
     *
     * @param int|null $pos_type_id the database id of the position type
     * @return void
     */
    function set_pos_type_by_id(?int $pos_type_id): void
    {
        global $sys;
        if ($pos_type_id == null) {
            $this->pos_type = null;
        } else {
            $this->pos_type = $sys->typ_lst->pos_typ->get($pos_type_id);
        }
    }

    /**
     * @return int|null the database id of the component position type
     */
    function get_pos_type_id(): ?int
    {
        return $this->pos_type->id();
    }

    /**
     * @return type_object the position type for the component in the linked view by the database id
     */
    function get_pos_type(): type_object
    {
        return $this->pos_type;
    }

    /**
     * @return string|null the code id of the position type for the component in the linked view by the database id
     */
    function get_pos_type_code_id(): ?string
    {
        return $this->pos_type->get_code_id();
    }

    /**
     * set the style for this component link that overwrites the view and component style
     *
     * @param string|null $code_id the code id that should be added to this view component link
     * @return void
     */
    function set_style(?string $code_id): void
    {
        global $sys;
        if ($code_id == null) {
            $this->style = null;
        } else {
            $this->style = $sys->typ_lst->msk_sty->get_by_code_id($code_id);
        }
    }

    /**
     * set the style for this component link by the database id
     *
     * @param int|null $style_id the database id of the display style
     * @return void
     */
    function set_style_by_id(?int $style_id): void
    {
        global $sys;
        if ($style_id == null) {
            $this->style = null;
        } else {
            $this->style = $sys->typ_lst->msk_sty->get($style_id);
        }
    }

    /**
     * @return type_object|null the view style or null
     */
    function get_style(): ?type_object
    {
        if ($this->style == null) {
            if ($this->get_component()->get_style() == null) {
                return $this->get_view()->get_style();
            } else {
                return $this->get_component()->get_style();
            }
        } else {
            return $this->style;
        }
    }

    /**
     * @return int|null the database id of the view style or null
     */
    function get_style_id(): ?int
    {
        $style_id = $this->style?->id();
        if ($style_id == null) {
            $style_id = $this->get_component()->get_style_id();
        }
        return $style_id;
    }

    /**
     * rename to standard link from object to view
     * @return view
     */
    function get_view(): sandbox_named
    {
        return $this->fob();
    }

    /**
     * rename to standard link to object to component
     * @return sandbox_named|component
     */
    function get_component(): sandbox_named|component
    {
        return $this->tob();
    }

    /**
     * expose the order number as pos
     * @return int|null
     */
    function get_pos(): ?int
    {
        return $this->order_nbr;
    }

    /**
     * copy the link objects from this object to the given component_link
     * used to unset any changes in the link to detect only the changes fields that the user is allowed to change
     *
     * @param sandbox_link|component_link $lnk
     * @return component_link
     */
    function set_link_objects(sandbox_link|component_link $lnk): component_link
    {
        $lnk->set_view($this->get_view());
        $lnk->set_predicate_id($this->predicate_id());
        $lnk->set_component($this->get_component());
        return $lnk;
    }

    /**
     * @return string a unique key including the position of the component link based on the names of the view and component
     */
    function get_key(): string
    {
        $from_name = str_replace(self::KEY_SEP, self::KEY_SEP_ESC, $this->from_name());
        $link_name = str_replace(self::KEY_SEP, self::KEY_SEP_ESC, $this->predicate_name());
        $to_name = str_replace(self::KEY_SEP, self::KEY_SEP_ESC, $this->to_name());
        return $from_name . self::KEY_SEP . $link_name . self::KEY_SEP . $to_name . self::KEY_SEP . strval($this->get_pos());
    }

    /**
     * overwrite the link type function
     * @return string|null the code id of the verb
     */
    function get_predicate_code_id(): ?string
    {
        global $sys;
        $id = $this->predicate_id();
        $typ = $sys->typ_lst->cmp_lnk_typ->get($id);
        if ($typ != null) {
            return $typ->get_code_id();
        } else {
            $msg = 'component link type with id ' . $id . ' is missing';
            log_err($msg);
            return $msg;
        }
    }


    /*
     * modify
     */

    /**
     * fill this component link object based on the given object
     * if the given type is not set (null) the type is not removed
     * if the given type is zero (not null) the type is removed
     *
     * @param component_link|sandbox|CombineObject|db_object_seq_id $obj sandbox object with the values that should be updated e.g. based on the import
     * @param user $usr_req the user who has requested the fill
     * @return user_message a warning in case of a conflict e.g. due to a missing change time
     */
    function fill(component_link|sandbox|CombineObject|db_object_seq_id $obj, user $usr_req): user_message
    {
        $usr_msg = parent::fill($obj, $usr_req);
        if ($obj->order_nbr != null) {
            $this->order_nbr = $obj->order_nbr;
        }
        if ($obj->pos_type != null) {
            $this->pos_type = $obj->pos_type;
        }
        if ($obj->style != null) {
            $this->style = $obj->style;
        }
        return $usr_msg;
    }


    /*
     * preloaded
     */

    /**
     * @return string the name of the preloaded view component link type
     */
    function predicate_name(): string
    {
        global $sys;
        return $sys->typ_lst->cmp_lnk_typ->name($this->predicate_id);
    }


    /*
     * load
     */

    /**
     * load a named user sandbox object by name
     * @param view $msk the view to which the component should be added
     * @param component $cmp the phrase that is linked to the formula
     * @param string $class the name of the child class from where the call has been triggered
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_link(view $msk, component $cmp, string $class = self::class): int
    {
        $id = parent::load_by_link_id($msk->id(), 0, $cmp->id(), $class);
        // no need to reload the linked objects, just assign it
        if ($id != 0) {
            $this->set_view($msk);
            $this->set_component($cmp);
        }
        return $id;
    }

    /**
     * load the component link by the unique link ids including the pos
     * @param int $msk_id the id of the view
     * @param int $cmp_id the id of the lin type
     * @param int $pos the position of the component
     * @return int the id of the component link found and zero if nothing is found
     */
    function load_by_link_and_pos(int $msk_id, int $cmp_id, int $pos): int
    {
        global $db_con;

        log_debug();
        $qp = $this->load_sql_by_link_and_pos($db_con->sql_creator(), $msk_id, $cmp_id, $pos);
        return $this->load($qp);
    }

    /**
     * load the component_link by the link id
     *
     * @param int $from_id the subject object id
     * @param int $type_id the predicate object id
     * @param int $to_id the object (grammar) object id
     * @return bool true if at least one link has been loaded
     */
    function load_by_link_and_type(int $from_id, int $type_id, int $to_id): bool
    {
        global $db_con;
        $qp = $this->load_sql_by_link_and_type($db_con->sql_creator(), $from_id, $type_id, $to_id, self::class);
        return $this->load($qp);
    }

    /**
     * load the view component link parameters for all users
     * @param sql_par|null $qp placeholder to align the function parameters with the parent
     * @return bool true if the standard view component link has been loaded
     */
    function load_standard(?sql_par $qp = null): bool
    {

        global $db_con;
        $result = false;

        $qp = $this->load_sql_standard($db_con->sql_creator());

        if ($qp->has_par()) {
            $db_dsl = $db_con->get1($qp);
            $result = $this->row_mapper_sandbox($db_dsl, true);
            if ($result) {
                $result = $this->load_owner();
            }
        }
        return $result;
    }

    /**
     * create an SQL statement to retrieve the parameters of the standard view component link from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_standard(sql_creator $sc): sql_par
    {
        // try to get the search values from the objects
        if ($this->id() <= 0) {
            $this->id = 0;
        }

        $sc->set_class($this::class);
        $qp = new sql_par($this::class);
        if ($this->id() != 0) {
            $qp->name .= 'std_id';
        } else {
            $qp->name .= 'std_link_ids';
        }
        $sc->set_name($qp->name);
        //TODO check if $db_con->set_usr($this->get_user()->id()); is needed
        $sc->set_fields(array(user_db::FLD_ID));
        $sc->set_fields(array_merge(
            self::FLD_NAMES,
            self::FLD_NAMES_NUM_USR,
            array(user_db::FLD_ID)));
        if ($this->id() > 0) {
            $sc->add_where($this->id_field(), $this->id());
        } elseif ($this->get_view()->id() > 0 and $this->get_component()->id() > 0) {
            $sc->add_where(view_db::FLD_ID, $this->get_view()->id());
            $sc->add_where(component::FLD_ID, $this->get_component()->id());
        } else {
            log_err('Cannot load default component link because id is missing');
        }
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * load the component_link by the link id
     *
     * @param int $view_id the id of the view
     * @return int the max order number of components related to the given view
     */
    function load_max_pos_by_view(int $view_id): int
    {
        global $db_con;
        $qp = $this->load_sql_max_pos($db_con->sql_creator(), $view_id);
        $db_row = $db_con->get1($qp);
        if ($db_row != null) {
            if (array_key_exists(sql::MAX_PREFIX . self::FLD_ORDER_NBR, $db_row)) {
                if ($db_row[sql::MAX_PREFIX . self::FLD_ORDER_NBR] != null) {
                    return $db_row[sql::MAX_PREFIX . self::FLD_ORDER_NBR];
                } else {
                    return 0;
                }
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }


    /*
     * load sql
     */

    /**
     * create an SQL statement to load the component_link by the link id
     *
     * @param sql_creator $sc with the target db_type set
     * @param int $dsp_id the view id
     * @param int $type_id the link type id
     * @param int $cmp_id the component id
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_link_and_type(sql_creator $sc, int $dsp_id, int $type_id, int $cmp_id, string $class = self::class): sql_par
    {
        return parent::load_sql_by_link($sc, $dsp_id, $type_id, $cmp_id, $class);
    }

    /**
     * create an SQL statement to retrieve a user sandbox link by the ids of the linked objects from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param int $msk_id the id of the view
     * @param int $cmp_id the id of the lin type
     * @param int $pos the position of the component
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_link_and_pos(sql_creator $sc, int $msk_id, int $cmp_id, int $pos): sql_par
    {
        $qp = $this->load_sql($sc, 'link_and_pos');
        $sc->add_where($this->from_field(), $msk_id);
        $sc->add_where($this->to_field(), $cmp_id);
        $sc->add_where(self::FLD_ORDER_NBR, $pos);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * create a simple SQL statement to retrieve the max order number of one view
     *
     * @param sql_creator $sc with the target db_type set
     * @param int $id the id of the view
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_max_pos(sql_creator $sc, int $id): sql_par
    {
        $qp = new sql_par(self::class);
        $qp->name .= 'max_pos';

        $sc->set_class(self::class);
        $sc->set_name($qp->name);
        $sc->set_usr($this->get_user()->id);
        $sc->add_usr_grp_field(self::FLD_ORDER_NBR, sql_par_type::MAX);
        $sc->add_where(view_db::FLD_ID, $id, sql_par_type::INT_SUB);
        $qp->sql = $sc->sql(1, false);
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * create the common part of an SQL statement to retrieve the parameters of a view component link from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name extension to make the query name unique
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql_creator $sc, string $query_name, string $class = self::class): sql_par
    {
        $qp = new sql_par($class);
        $qp->name .= $query_name;

        $sc->set_class($class);
        $sc->set_name($qp->name);
        $sc->set_usr($this->get_user()->id);
        $sc->set_fields(self::FLD_NAMES_LINK);
        $sc->set_usr_num_fields(self::FLD_NAMES_NUM_USR);

        return $qp;
    }

    /**
     * create an SQL statement to retrieve the user changes of the current view component link
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
        return parent::load_sql_user_changes($sc, $sc_par_lst);
    }


    /*
     * retrieval
     */

    /**
     * to load the related objects if the link object is loaded by an external query like in user_display to show the sandbox
     * @returns bool true if a link has been loaded
     */
    function reload_objects(): bool
    {
        $result = true;
        if ($this->get_view() != null) {
            if ($this->get_view()->id() > 0 and $this->get_view()->name() == '') {
                $msk = new view($this->get_user());
                if ($msk->load_by_id($this->get_view()->id())) {
                    $this->set_view($msk);
                } else {
                    $result = false;
                }
            }
        }
        if ($this->get_component() != null) {
            if ($this->get_component()->id() > 0 and $this->get_component()->name() == '') {
                $cmp = new component($this->get_user());
                if ($cmp->load_by_id($this->get_component()->id())) {
                    $this->set_component($cmp);
                } else {
                    $result = false;
                }
            }
        }
        return $result;
    }


    /*
     * sql fields
     */

    function from_field(): string
    {
        return view_db::FLD_ID;
    }

    function to_field(): string
    {
        return component::FLD_ID;
    }

    function type_field(): string
    {
        return component_link_type::FLD_ID;
    }

    /**
     * @return string the field name of the name db field as a function for complex overwrites
     */
    function name_field(): string
    {
        return '';
    }

    function all_sandbox_fields(): array
    {
        return self::ALL_SANDBOX_FLD_NAMES;
    }


    /*
     * im- and export
     */

    /**
     * create an array with the export json fields of this component
     * which does not include the internal database id
     * TODO Prio 1 add export type list with the potential option not to include the view
     *             so that it can be used to export the linked components of a view
     *             and add a unit test outside the horizontal tests for this case
     * TODO Prio 1 add export type list with the potential to export only the name and position
     * @param export_type_list|array $exp_typ define the export format
     * @param bool $do_load true if any missing data should be loaded while creating the array
     * @return array with the json fields
     */
    function export_json(export_type_list|array $exp_typ = [], bool $do_load = true): array
    {
        global $sys;

        if (is_array($exp_typ)) {
            $exp_typ = new export_type_list($exp_typ);
        }

        $vars = parent::export_json($exp_typ, $do_load);
        if (!$exp_typ->ignore_from()) {
            if ($this->get_view()?->name() != null) {
                $vars[json_fields::VIEW] = $this->get_view()->export_json($exp_typ, $do_load);
            }
            if ($this->get_component()?->name() != null) {
                $vars[json_fields::COMPONENT] = $this->get_component()->export_json($exp_typ, $do_load);
            }
        } else {
            if ($this->get_component()?->name() != null) {
                $vars[json_fields::NAME] = $this->get_component()->name();
            }
        }

        // do not include the default link type in the export
        // if the export message is imported into a new version where the default is changed
        // the expectation is not to change the default which means to use the changed default value
        // to be discussed again on real cases
        if ($this->predicate_id == $sys->typ_lst->cmp_lnk_typ->id(component_link_type::DEFAULT)) {
            unset($vars[json_fields::PREDICATE]);
        }
        /*
        if ($this->pos_type_id >= 0
            and $this->pos_type_id == $sys->typ_lst->pos_typ->id(position_types::DEFAULT)) {
            $vars[json_fields::POSITION] = $sys->typ_lst->pos_typ->id(position_types::DEFAULT);
        }
        */
        if ($this->pos_type != null
            and $this->pos_type?->get_code_id() != position_types::DEFAULT) {
            $vars[json_fields::POS_TYPE] = $this->pos_type->get_code_id();
        }
        if ($this->style != null
            and $this->style?->get_code_id() != view_styles::DEFAULT) {
            $vars[json_fields::STYLE] = $this->style->get_code_id();
        }
        if ($this->order_nbr >= 0) {
            $vars[json_fields::POSITION] = $this->order_nbr;
        }

        return $vars;
    }


    /*
     * modify
     */

    // move one view component
    // TODO load to list once, resort and write all positions with one SQL statement
    private function move($direction): bool
    {
        $result = false;

        $usr_msg = new user_message();

        // load any missing parameters
        if ($this->id() > 0) {
            $this->load_by_id($this->id());
        } elseif ($this->get_view()->id() != 0 and $this->get_component()->id() != 0) {
            $this->load_by_link_id($this->get_view()->id(), 0, $this->get_component()->id(), self::class);
        }
        $this->reload_objects();

        // check the all minimal input parameters
        if ($this->id() <= 0) {
            log_err("Cannot load the view component link.", "component_link->move");
        } elseif ($this->get_view()->id() <= 0 or $this->get_component()->id() <= 0) {
            log_err("The view component id and the view component id must be given to move it.", "component_link->move");
        } else {
            log_debug('component_link->move ' . $direction . ' ' . $this->dsp_id());

            // new reorder code that can create a separate order for each user
            if ($this->get_view() == null or $this->get_component() == null) {
                log_err("The view component and the view component cannot be loaded to move them.", "component_link->move");
            } else {
                $this->get_view()->load_components();

                // correct any wrong order numbers e.g. a missing number
                $order_number_corrected = false;
                log_debug('component_link->move check order numbers for ' . $this->get_view()->dsp_id());
                // TODO define the common sorting start number, which is 1 and not 0
                $order_nbr = component_link::START_ORDER_NBR;
                if ($this->get_view()->cmp_lnk_lst != null) {
                    foreach ($this->get_view()->cmp_lnk_lst->lst() as $cmp_lnk) {
                        // fix any wrong order numbers
                        if ($cmp_lnk->order_nbr != $order_nbr) {
                            log_debug('check order number of the view component '
                                . $cmp_lnk->dsp_id() . ' corrected from ' . $cmp_lnk->order_nbr
                                . ' to ' . $order_nbr . ' in ' . $this->get_view()->dsp_id());
                            //zu_err('Order number of the view component "'.$entry->name.'" corrected from '.$cmp_lnk->order_nbr.' to '.$order_nbr.'.', "component_link->move");
                            $cmp_lnk->order_nbr = $order_nbr;
                            $cmp_lnk->save($usr_msg)->get_last_message();
                            $order_number_corrected = true;
                        }
                        log_debug('component_link->move check order numbers checked for '
                            . $this->get_view()->dsp_id() . ' and ' . $cmp_lnk->dsp_id() . ' at position ' . $order_nbr);
                        $order_nbr++;
                    }
                }
                if ($order_number_corrected) {
                    log_debug('component_link->move reload after correction');
                    $this->get_view()->load_components();
                    // check if correction was successful
                    $order_nbr = 0;
                    $cmp_lst = $this->get_view()->components();
                    if (!$cmp_lst->is_empty()) {
                        foreach ($cmp_lst->lst() as $entry) {
                            $cmp_lnk = new component_link($this->get_user());
                            $msk = new view($this->get_user());
                            $msk->load_by_id($this->get_view()->id());
                            $cmp_lnk->load_by_link($msk, $entry);
                            if ($cmp_lnk->order_nbr != $order_nbr) {
                                log_err('Component link ' . $cmp_lnk->dsp_id() . ' should have position ' . $order_nbr . ', but is ' . $cmp_lnk->order_nbr, "component_link->move");
                            }
                        }
                    }
                }
                log_debug('component_link->move order numbers checked for ' . $this->get_view()->dsp_id());

                // actually move the selected component
                // TODO what happens if the another user has deleted some components?
                $order_nbr = 1;
                $prev_entry = null;
                $prev_entry_down = false;
                if ($this->get_view()->cmp_lnk_lst != null) {
                    foreach ($this->get_view()->cmp_lnk_lst->lst() as $cmp_lnk) {
                        // get the component link (TODO add the order number to the entry lst, so that this loading is not needed)
                        //$cmp_lnk = new component_link($this->get_user());
                        //$msk = new view($this->get_user());
                        //$msk->load_by_id($this->get_view_id());
                        //$cmp_lnk->load_by_link($msk, $entry);
                        if ($prev_entry_down) {
                            if (isset($prev_entry)) {
                                log_debug('component_link->move order number of the view component ' . $prev_entry->tob->dsp_id() . ' changed from ' . $prev_entry->order_nbr . ' to ' . $order_nbr . ' in ' . $this->get_view()->dsp_id());
                                $prev_entry->order_nbr = $order_nbr;
                                $prev_entry->save($usr_msg);
                                $prev_entry = null;
                            }
                            log_debug('component_link->move order number of the view component "' . $cmp_lnk->tob->name() . '" changed from ' . $cmp_lnk->order_nbr . ' to ' . $order_nbr . ' - 1 in "' . $this->get_view()->name() . '"');
                            $cmp_lnk->order_nbr = $order_nbr - 1;
                            $cmp_lnk->save($usr_msg);
                            $result = true;
                            $prev_entry_down = false;
                        }
                        if ($cmp_lnk->id() == $this->get_component()->id()) {
                            if ($direction == 'up') {
                                if ($cmp_lnk->order_nbr > 0) {
                                    log_debug('component_link->move order number of the view component ' . $cmp_lnk->tob->dsp_id() . ' changed from ' . $cmp_lnk->order_nbr . ' to ' . $order_nbr . ' - 1 in ' . $this->get_view()->dsp_id());
                                    $cmp_lnk->order_nbr = $order_nbr - 1;
                                    $cmp_lnk->save($usr_msg);
                                    $result = true;
                                    if (isset($prev_entry)) {
                                        log_debug('component_link->move order number of the view component ' . $prev_entry->tob->dsp_id() . ' changed from ' . $prev_entry->order_nbr . ' to ' . $order_nbr . ' in ' . $this->get_view()->dsp_id());
                                        $prev_entry->order_nbr = $order_nbr;
                                        $prev_entry->save($usr_msg);
                                    }
                                }
                            } else {
                                if ($cmp_lnk->order_nbr > 0) {
                                    $prev_entry = $cmp_lnk;
                                    $prev_entry_down = true;
                                }
                            }
                        }
                        $prev_entry = $cmp_lnk;
                        $order_nbr++;
                    }
                }
            }

            // force reloading view components
            log_debug('component_link->move reload');
            $this->get_view()->load_components();
        }

        log_debug('component_link->move done');
        return $result;
    }

    // move on view component up
    function move_up(): bool
    {
        return $this->move('up');
    }

    // move on view component down
    function move_down(): bool
    {
        return $this->move('down');
    }


    /*
     * save
     */

    // check if the database record for the user specific settings can be removed

    /**
     * get a similar reference
     */
    function get_similar(): component_link
    {
        $result = new component_link($this->get_user());

        $db_chk = clone $this;
        $db_chk->reset();
        $db_chk->load_by_link_and_pos($this->get_view()->id(), $this->get_component()->id(), $this->order_nbr);
        if ($db_chk->id() > 0) {
            log_debug('a component link like ' . $this->dsp_id() . ' already exists');
            $result = $db_chk;
        }

        return $result;
    }

    /**
     * set the update parameters for the view component order_nbr
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param component_link $db_rec the view component link as saved in the database before the update
     * @param component_link $std_rec the default parameter used for this view component link
     * @return user_message the message that should be shown to the user in case something went wrong
     */
    private
    function save_field_order_nbr(sql_db $db_con, component_link $db_rec, component_link $std_rec): user_message
    {
        $usr_msg = new user_message();
        if ($db_rec->order_nbr <> $this->order_nbr) {
            $log = $this->log_upd_field();
            $log->old_value = $db_rec->order_nbr;
            $log->new_value = $this->order_nbr;
            $log->std_value = $std_rec->order_nbr;
            $log->row_id = $this->id();
            $log->set_field(self::FLD_ORDER_NBR);
            $usr_msg->add($this->save_field_user($db_con, $log));
        }
        return $usr_msg;
    }

    /**
     * save all updated component_link fields excluding the name, because already done when adding a component_link
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param component_link|sandbox $db_obj the view component link as saved in the database before the update
     * @param component_link|sandbox $norm_obj the default parameter used for this view component link
     * @return user_message the message that should be shown to the user in case something went wrong
     */
    function save_all_fields(sql_db $db_con, component_link|sandbox $db_obj, component_link|sandbox $norm_obj): user_message
    {
        $usr_msg = $this->save_field_order_nbr($db_con, $db_obj, $norm_obj);
        $usr_msg->add($this->save_field_type($db_con, $db_obj, $norm_obj));
        $usr_msg->add($this->save_field_excluded($db_con, $db_obj, $norm_obj));
        log_debug('all fields for ' . $this->dsp_id() . ' has been saved');
        return $usr_msg;
    }

    /**
     * create a new link object including the order number
     * @returns int the id of the creates object
     */
    function add_insert(): int
    {
        global $db_con;
        $lib = new library();
        $db_con->set_class(self::class);
        return $db_con->insert_old(
            array($this->from_name . sql_db::FLD_EXT_ID, $this->to_name . sql_db::FLD_EXT_ID, user_db::FLD_ID, 'order_nbr'),
            array($this->get_view()->id(), $this->get_component()->id(), $this->get_user()->id(), $this->order_nbr));
    }


    /*
     * sql write fields
     */

    /**
     * get a list of all database fields that might be changed
     * excluding the internal fields e.g. the database id
     * field list must be corresponding to the db_fields_changed fields
     *
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return array list of all database field names that have been updated
     */
    function db_fields_all(sql_type_list $sc_par_lst = new sql_type_list()): array
    {
        return array_merge(
            parent::db_all_fields_link($sc_par_lst),
            [
                component_link_type::FLD_ID,
                self::FLD_ORDER_NBR,
                self::FLD_POS_TYPE,
                self::FLD_STYLE
            ],
            parent::db_fields_all_sandbox()
        );
    }

    /**
     * get a list of database field names, values and types that have been updated
     *
     * @param sandbox|component_link $sbx the compare value to detect the changed fields
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @param user_message $usr_msg the user message object that collects any issues during the sql creation
     * @return sql_par_field_list list 3 entry arrays with the database field name, the value and the sql type that have been updated
     */
    function db_fields_changed(
        sandbox|component_link $sbx,
        sql_type_list          $sc_par_lst = new sql_type_list(),
        user_message           $usr_msg = new user_message()
    ): sql_par_field_list
    {
        global $sys;

        $sc = new sql_creator();
        $do_log = $sc_par_lst->incl_log();
        $usr_tbl = $sc_par_lst->is_usr_tbl();
        $table_id = $sc->table_id($this::class);

        $lst = parent::db_fields_changed($sbx, $sc_par_lst, $usr_msg);
        // for the standard table the type field should always be included because it is part of the prime index
        if ($sbx->predicate_id() !== $this->predicate_id() or (!$usr_tbl and $sc_par_lst->is_insert())) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . component_link_type::FLD_ID,
                    $sys->typ_lst->cng_fld->id($table_id . component_link_type::FLD_ID),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            if ($this->predicate_id() < 0) {
                $usr_msg->add_id_with_vars(msg_id::COMPONENT_LINK_TYPE_MISSING, [
                    msg_id::VAR_TYPE => $this->predicate_name(),
                    msg_id::VAR_NAME => $this->dsp_id()
                ]);
            }
            $lst->add_type_field(
                component_link_type::FLD_ID,
                type_object::FLD_NAME,
                $this->predicate_id(),
                $sbx->predicate_id(),
                $sys->typ_lst->cmp_lnk_typ
            );
        }
        if ($sbx->get_pos() !== $this->get_pos()) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . self::FLD_ORDER_NBR,
                    $sys->typ_lst->cng_fld->id($table_id . self::FLD_ORDER_NBR),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                self::FLD_ORDER_NBR,
                $this->get_pos(),
                self::FLD_ORDER_NBR_SQL_TYP,
                $sbx->get_pos()
            );
        }
        if ($sbx->get_pos_type_id() !== $this->get_pos_type_id()) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . self::FLD_POS_TYPE,
                    $sys->typ_lst->cng_fld->id($table_id . self::FLD_POS_TYPE),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            if ($this->get_pos_type_id() < 0) {
                $usr_msg->add_id_with_vars(msg_id::COMPONENT_POS_TYPE_MISSING, [
                    msg_id::VAR_TYPE => $this->get_pos_type_id(),
                    msg_id::VAR_NAME => $this->dsp_id()
                ]);
            }
            $lst->add_type_field(
                self::FLD_POS_TYPE,
                self::FLD_POS_TYPE_NAME,
                $this->get_pos_type_id(),
                $sbx->get_pos_type_id(),
                $sys->typ_lst->pos_typ
            );
        }
        if ($sbx->get_style_id() !== $this->get_style_id()) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . self::FLD_STYLE,
                    $sys->typ_lst->cng_fld->id($table_id . self::FLD_STYLE),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            // TODO easy move to id function of type list
            if ($this->get_style_id() < 0) {
                $usr_msg->add_id_with_vars(msg_id::COMPONENT_LINK_STYLE_MISSING, [
                    msg_id::VAR_TYPE => $this->get_style_id(),
                    msg_id::VAR_NAME => $this->dsp_id()
                ]);
            }
            $lst->add_type_field(
                self::FLD_STYLE,
                view_style::FLD_NAME,
                $this->get_style_id(),
                $sbx->get_style_id(),
                $sys->typ_lst->msk_sty
            );
        }
        return $lst->merge($this->db_changed_sandbox_list($sbx, $sc_par_lst));
    }


    /*
     * debug
     */

    /**
     * @returns string a programmer readable description of the link for unique identification
     * NEVER call any methods from this function because this function is used for debugging and a call can cause an endless loop
     */
    function dsp_id(): string
    {
        $result = parent::dsp_id();
        $pos = $this->get_pos();
        if ($pos != null) {
            $result .= ' at pos ' . $pos;
        } else {
            $result .= ' without pos';
        }
        return $result;
    }

    // remember the move of a display component
    // up only the component that has been move by the user
    // and not all other component changed, because this would be more confusing
    private function log_move($direction)
    {

    }

}
