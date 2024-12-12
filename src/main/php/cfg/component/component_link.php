<?php

/*

    model/component/component_link.php - link a single display component/element to a view
    ----------------------------------

    The main sections of this object are
    - db const:          const for the database link
    - object vars:       the variables of this word object
    - construct and map: including the mapping of the db row to this word object
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

namespace cfg\component;

// TODO easy include all used classes
include_once DB_PATH . 'sql_par_type.php';
include_once API_VIEW_PATH . 'component_link.php';

use api\view\component_link as component_link_api;
use cfg\db\sql;
use cfg\db\sql_db;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
use cfg\db\sql_par;
use cfg\db\sql_par_field_list;
use cfg\db\sql_par_type;
use cfg\db\sql_type;
use cfg\db\sql_type_list;
use cfg\export\sandbox_exp;
use cfg\log\change;
use cfg\sandbox;
use cfg\sandbox_link;
use cfg\sandbox_named;
use cfg\type_object;
use cfg\user;
use cfg\user_message;
use cfg\view;
use shared\library;

class component_link extends sandbox_link
{

    /*
     * db const
     */

    // the database and JSON object field names used only for formula links
    const TBL_COMMENT = 'to link components to views with an n:m relation';
    const FLD_ID = 'component_link_id';
    const FLD_ORDER_NBR = 'order_nbr';
    const FLD_ORDER_NBR_SQL_TYP = sql_field_type::INT;
    const FLD_POS_COM = 'the position of the component e.g. right or below';
    const FLD_POS_TYPE = 'position_type_id';
    const FLD_POS_TYPE_NAME = 'position'; // for log only
    const FLD_STYLE_COM = 'the display style for this component link';
    const FLD_STYLE = 'view_style_id';

    // all database field names excluding the user specific fields and the id
    const FLD_NAMES = array(
        view::FLD_ID,
        component::FLD_ID
    );
    // list of the link database field names
    const FLD_NAMES_LINK = array(
        view::FLD_ID,
        component::FLD_ID
    );
    // list of the user specific database field names
    const FLD_NAMES_NUM_USR = array(
        self::FLD_ORDER_NBR,
        self::FLD_POS_TYPE,
        self::FLD_STYLE,
        sandbox::FLD_EXCLUDED,
        sandbox::FLD_SHARE,
        sandbox::FLD_PROTECT
    );
    // all database field names excluding the id used to identify if there are some user specific changes
    const ALL_SANDBOX_FLD_NAMES = array(
        self::FLD_ORDER_NBR,
        self::FLD_POS_TYPE,
        self::FLD_STYLE,
        sandbox::FLD_EXCLUDED,
        sandbox::FLD_SHARE,
        sandbox::FLD_PROTECT
    );
    // list of fields that select the objects that should be linked
    const FLD_LST_LINK = array(
        [view::FLD_ID, sql_field_type::INT, sql_field_default::NOT_NULL, sql::INDEX, view::class, ''],
        [component::FLD_ID, sql_field_type::INT, sql_field_default::NOT_NULL, sql::INDEX, component::class, ''],
    );
    // list of MANDATORY fields that CAN be CHANGED by the user
    const FLD_LST_MUST_BUT_STD_ONLY = array(
        [self::FLD_ORDER_NBR, self::FLD_ORDER_NBR_SQL_TYP, sql_field_default::ONE, '', '', ''],
        [component_link_type::FLD_ID, type_object::FLD_ID_SQL_TYP, sql_field_default::ONE, sql::INDEX, component_link_type::class, ''],
        [position_type::FLD_ID, type_object::FLD_ID_SQL_TYP, sql_field_default::ONE, sql::INDEX, position_type::class, self::FLD_POS_COM],
        [self::FLD_STYLE, type_object::FLD_ID_SQL_TYP, sql_field_default::NULL, sql::INDEX, view_style::class, self::FLD_STYLE_COM],
    );
    // list of fields that CAN be CHANGEd by the user
    const FLD_LST_MUST_BUT_USER_CAN_CHANGE = array(
        [self::FLD_ORDER_NBR, self::FLD_ORDER_NBR_SQL_TYP, sql_field_default::NULL, '', '', ''],
        [component_link_type::FLD_ID, type_object::FLD_ID_SQL_TYP, sql_field_default::NULL, sql::INDEX, component_link_type::class, ''],
        [position_type::FLD_ID, type_object::FLD_ID_SQL_TYP, sql_field_default::NULL, sql::INDEX, position_type::class, self::FLD_POS_COM],
        [self::FLD_STYLE, type_object::FLD_ID_SQL_TYP, sql_field_default::NULL, sql::INDEX, view_style::class, self::FLD_STYLE_COM],
    );


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

        $this->rename_can_switch = UI_CAN_CHANGE_VIEW_COMPONENT_LINK;

        $this->reset_objects($usr);
    }

    function reset(): void
    {
        parent::reset();

        $this->reset_objects($this->user());

        $this->set_predicate(component_link_type::ALWAYS);
        $this->set_pos_type(position_type::BELOW);
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
            $this->set_view(new view($this->user()));
            $this->view()->set_id($db_row[view::FLD_ID]);
            $this->set_component(new component($this->user()));
            $this->component()->set_id($db_row[component::FLD_ID]);
            $this->order_nbr = $db_row[self::FLD_ORDER_NBR];
            $this->set_pos_type_by_id($db_row[self::FLD_POS_TYPE]);
            $this->set_style_by_id($db_row[self::FLD_STYLE]);
        }
        return $result;
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
        parent::set_id($id);
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
        global $cmp_lnk_typ_cac;
        $this->predicate_id = $cmp_lnk_typ_cac->id($type_code_id);
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
     * set the position of this link
     * @param int $pos
     */
    function set_pos(int $pos): void
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
        global $pos_typ_cac;
        if ($code_id == null) {
            $this->pos_type = null;
        } else {
            $this->pos_type = $pos_typ_cac->get_by_code_id($code_id);
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
        global $pos_typ_cac;
        if ($pos_type_id == null) {
            $this->pos_type = null;
        } else {
            $this->pos_type = $pos_typ_cac->get($pos_type_id);
        }
    }

    /**
     * @return int|null the database id of the component position type
     */
    function pos_type_id(): ?int
    {
        return $this->pos_type->id();
    }

    /**
     * @return type_object the position type for the component in the linked view by the database id
     */
    function pos_type(): type_object
    {
        return $this->pos_type;
    }

    /**
     * set the style for this component link that overwrites the view and component style
     *
     * @param string|null $code_id the code id that should be added to this view component link
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
     * set the style for this component link by the database id
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
     * @return type_object|null the view style or null
     */
    function style(): ?type_object
    {
        if ($this->style == null) {
            if ($this->component()->style() == null) {
                return $this->view()->style();
            } else {
                return $this->component()->style();
            }
        } else {
            return $this->style;
        }
    }

    /**
     * @return int|null the database id of the view style or null
     */
    function style_id(): ?int
    {
        return $this->style?->id();
    }

    /**
     * rename to standard link from object to view
     * @return view
     */
    function view(): sandbox_named
    {
        return $this->fob();
    }

    /**
     * rename to standard link to object to component
     * @return sandbox_named|component
     */
    function component(): sandbox_named|component
    {
        return $this->tob();
    }

    /**
     * expose the order number as pos
     * @return int|null
     */
    function pos(): ?int
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
        $lnk->set_view($this->view());
        $lnk->set_predicate_id($this->predicate_id());
        $lnk->set_component($this->component());
        return $lnk;
    }


    /*
     * preloaded
     */

    /**
     * @return string the name of the preloaded view component link type
     */
    function predicate_name(): string
    {
        global $cmp_lnk_typ_cac;
        return $cmp_lnk_typ_cac->name($this->predicate_id);
    }


    /*
     * cast
     */

    /**
     * @param object $api_obj minimal component link object that vars should be set based on this object vars
     */
    function fill_api_obj(object $api_obj): void
    {
        $api_obj->set_id($this->id());
        if ($this->tob() != null) {
            $api_obj->set_component($this->tob()->api_obj());
        }
        $api_obj->set_pos($this->order_nbr);
        //$api_obj->set_type_id($this->type_id());
        $api_obj->set_pos_type($this->pos_type_id());
        if ($this->style != null) {
            $api_obj->set_style($this->style_id());
        }
    }

    /**
     * @return component_link_api the view component frontend api object
     */
    function api_obj(): component_link_api
    {
        $api_obj = new component_link_api();
        $this->fill_api_obj($api_obj);
        return $api_obj;
    }

    /**
     * @returns string the api json message for the object as a string
     */
    function api_json(): string
    {
        return $this->api_obj()->get_json();
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

        $qp = $this->load_standard_sql($db_con->sql_creator(), $this::class);

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
     * @param sql $sc with the target db_type set
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_standard_sql(sql $sc): sql_par
    {
        // try to get the search values from the objects
        if ($this->id() <= 0) {
            $this->set_id(0);
        }

        $sc->set_class($this::class);
        $qp = new sql_par($this::class);
        if ($this->id() != 0) {
            $qp->name .= 'std_id';
        } else {
            $qp->name .= 'std_link_ids';
        }
        $sc->set_name($qp->name);
        //TODO check if $db_con->set_usr($this->user()->id()); is needed
        $sc->set_fields(array(user::FLD_ID));
        $sc->set_fields(array_merge(
            self::FLD_NAMES,
            self::FLD_NAMES_NUM_USR,
            array(user::FLD_ID)));
        if ($this->id() > 0) {
            $sc->add_where($this->id_field(), $this->id());
        } elseif ($this->view()->id() > 0 and $this->component()->id() > 0) {
            $sc->add_where(view::FLD_ID, $this->view()->id());
            $sc->add_where(component::FLD_ID, $this->component()->id());
        } else {
            log_err('Cannot load default component link because id is missing');
        }
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * create the common part of an SQL statement to retrieve the parameters of a view component link from the database
     *
     * @param sql $sc with the target db_type set
     * @param string $query_name the name extension to make the query name unique
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql $sc, string $query_name, string $class = self::class): sql_par
    {
        $qp = new sql_par($class);
        $qp->name .= $query_name;

        $sc->set_class($class);
        $sc->set_name($qp->name);
        $sc->set_usr($this->user()->id());
        $sc->set_fields(self::FLD_NAMES_LINK);
        $sc->set_usr_num_fields(self::FLD_NAMES_NUM_USR);

        return $qp;
    }

    /**
     * create an SQL statement to load the component_link by the link id
     *
     * @param sql $sc with the target db_type set
     * @param int $dsp_id the view id
     * @param int $type_id the link type id
     * @param int $cmp_id the component id
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_link_and_type(sql $sc, int $dsp_id, int $type_id, int $cmp_id, string $class = self::class): sql_par
    {
        return parent::load_sql_by_link($sc, $dsp_id, $type_id, $cmp_id, $class);
    }

    /**
     * create an SQL statement to retrieve a user sandbox link by the ids of the linked objects from the database
     *
     * @param sql $sc with the target db_type set
     * @param int $msk_id the id of the view
     * @param int $cmp_id the id of the lin type
     * @param int $pos the position of the component
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_link_and_pos(sql $sc, int $msk_id, int $cmp_id, int $pos): sql_par
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
     * @param sql $sc with the target db_type set
     * @param int $id the id of the view
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_max_pos(sql $sc, int $id): sql_par
    {
        $qp = new sql_par(self::class);
        $qp->name .= 'max_pos';

        $sc->set_class(self::class);
        $sc->set_name($qp->name);
        $sc->set_usr($this->user()->id());
        $sc->add_usr_grp_field(self::FLD_ORDER_NBR, sql_par_type::MAX);
        $sc->add_where(view::FLD_ID, $id, sql_par_type::INT_SUB);
        $qp->sql = $sc->sql(1, false);
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * load the component_link by the link id
     *
     * @param int $view_id the id of the view
     * @return int the max order number of components related to the given view
     */
    function max_pos_by_view(int $view_id): int
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

    /**
     * to load the related objects if the link object is loaded by an external query like in user_display to show the sandbox
     * @returns bool true if a link has been loaded
     */
    function load_objects(): bool
    {
        $result = true;
        if ($this->view() != null) {
            if ($this->view()->id() > 0 and $this->view()->name() == '') {
                $msk = new view($this->user());
                if ($msk->load_by_id($this->view()->id())) {
                    $this->set_view($msk);
                } else {
                    $result = false;
                }
            }
        }
        if ($this->component() != null) {
            if ($this->component()->id() > 0 and $this->component()->name() == '') {
                $cmp = new component($this->user());
                if ($cmp->load_by_id($this->component()->id())) {
                    $this->set_component($cmp);
                } else {
                    $result = false;
                }
            }
        }
        return $result;
    }

    function from_field(): string
    {
        return view::FLD_ID;
    }

    function to_field(): string
    {
        return component::FLD_ID;
    }

    function type_field(): string
    {
        return component_link_type::FLD_ID;
    }

    function all_sandbox_fields(): array
    {
        return self::ALL_SANDBOX_FLD_NAMES;
    }


    /*
     * im- and export
     */

    /**
     * fill the component export object to create a json
     * which does not include the internal database id
     */
    function export_obj(bool $do_load = true): sandbox_exp
    {
        $result = $this->component()->export_obj($do_load);
        if ($this->order_nbr >= 0) {
            $result->position = $this->order_nbr;
        }
        return $result;
    }


    /*
     * modify
     */

    // move one view component
    // TODO load to list once, resort and write all positions with one SQL statement
    private function move($direction): bool
    {
        $result = false;

        // load any missing parameters
        if ($this->id() > 0) {
            $this->load_by_id($this->id());
        } elseif ($this->view()->id() != 0 and $this->component()->id() != 0) {
            $this->load_by_link_id($this->view()->id(), 0, $this->component()->id(), self::class);
        }
        $this->load_objects();

        // check the all minimal input parameters
        if ($this->id() <= 0) {
            log_err("Cannot load the view component link.", "component_link->move");
        } elseif ($this->view()->id() <= 0 or $this->component()->id() <= 0) {
            log_err("The view component id and the view component id must be given to move it.", "component_link->move");
        } else {
            log_debug('component_link->move ' . $direction . ' ' . $this->dsp_id());

            // new reorder code that can create a separate order for each user
            if ($this->view() == null or $this->component() == null) {
                log_err("The view component and the view component cannot be loaded to move them.", "component_link->move");
            } else {
                $this->view()->load_components();

                // correct any wrong order numbers e.g. a missing number
                $order_number_corrected = false;
                log_debug('component_link->move check order numbers for ' . $this->view()->dsp_id());
                // TODO define the common sorting start number, which is 1 and not 0
                $order_nbr = 1;
                if ($this->view()->cmp_lnk_lst != null) {
                    foreach ($this->view()->cmp_lnk_lst->lst() as $cmp_lnk) {
                        // fix any wrong order numbers
                        if ($cmp_lnk->order_nbr != $order_nbr) {
                            log_debug('check order number of the view component '
                                . $cmp_lnk->dsp_id() . ' corrected from ' . $cmp_lnk->order_nbr
                                . ' to ' . $order_nbr . ' in ' . $this->view()->dsp_id());
                            //zu_err('Order number of the view component "'.$entry->name.'" corrected from '.$cmp_lnk->order_nbr.' to '.$order_nbr.'.', "component_link->move");
                            $cmp_lnk->order_nbr = $order_nbr;
                            $cmp_lnk->save()->get_last_message();
                            $order_number_corrected = true;
                        }
                        log_debug('component_link->move check order numbers checked for '
                            . $this->view()->dsp_id() . ' and ' . $cmp_lnk->dsp_id() . ' at position ' . $order_nbr);
                        $order_nbr++;
                    }
                }
                if ($order_number_corrected) {
                    log_debug('component_link->move reload after correction');
                    $this->view()->load_components();
                    // check if correction was successful
                    $order_nbr = 0;
                    $cmp_lst = $this->view()->components();
                    if (!$cmp_lst->is_empty()) {
                        foreach ($cmp_lst->lst() as $entry) {
                            $cmp_lnk = new component_link($this->user());
                            $msk = new view($this->user());
                            $msk->load_by_id($this->view()->id());
                            $cmp_lnk->load_by_link($msk, $entry);
                            if ($cmp_lnk->order_nbr != $order_nbr) {
                                log_err('Component link ' . $cmp_lnk->dsp_id() . ' should have position ' . $order_nbr . ', but is ' . $cmp_lnk->order_nbr, "component_link->move");
                            }
                        }
                    }
                }
                log_debug('component_link->move order numbers checked for ' . $this->view()->dsp_id());

                // actually move the selected component
                // TODO what happens if the another user has deleted some components?
                $order_nbr = 1;
                $prev_entry = null;
                $prev_entry_down = false;
                if ($this->view()->cmp_lnk_lst != null) {
                    foreach ($this->view()->cmp_lnk_lst->lst() as $cmp_lnk) {
                        // get the component link (TODO add the order number to the entry lst, so that this loading is not needed)
                        //$cmp_lnk = new component_link($this->user());
                        //$msk = new view($this->user());
                        //$msk->load_by_id($this->view_id());
                        //$cmp_lnk->load_by_link($msk, $entry);
                        if ($prev_entry_down) {
                            if (isset($prev_entry)) {
                                log_debug('component_link->move order number of the view component ' . $prev_entry->tob->dsp_id() . ' changed from ' . $prev_entry->order_nbr . ' to ' . $order_nbr . ' in ' . $this->view()->dsp_id());
                                $prev_entry->order_nbr = $order_nbr;
                                $prev_entry->save();
                                $prev_entry = null;
                            }
                            log_debug('component_link->move order number of the view component "' . $cmp_lnk->tob->name() . '" changed from ' . $cmp_lnk->order_nbr . ' to ' . $order_nbr . ' - 1 in "' . $this->view()->name() . '"');
                            $cmp_lnk->order_nbr = $order_nbr - 1;
                            $cmp_lnk->save();
                            $result = true;
                            $prev_entry_down = false;
                        }
                        if ($cmp_lnk->id() == $this->component()->id()) {
                            if ($direction == 'up') {
                                if ($cmp_lnk->order_nbr > 0) {
                                    log_debug('component_link->move order number of the view component ' . $cmp_lnk->tob->dsp_id() . ' changed from ' . $cmp_lnk->order_nbr . ' to ' . $order_nbr . ' - 1 in ' . $this->view()->dsp_id());
                                    $cmp_lnk->order_nbr = $order_nbr - 1;
                                    $cmp_lnk->save();
                                    $result = true;
                                    if (isset($prev_entry)) {
                                        log_debug('component_link->move order number of the view component ' . $prev_entry->tob->dsp_id() . ' changed from ' . $prev_entry->order_nbr . ' to ' . $order_nbr . ' in ' . $this->view()->dsp_id());
                                        $prev_entry->order_nbr = $order_nbr;
                                        $prev_entry->save();
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
            $this->view()->load_components();
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
     * create an SQL statement to retrieve the user changes of the current view component link
     *
     * @param sql $sc with the target db_type set
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation e.g. standard for values and results
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_user_changes(
        sql           $sc,
        sql_type_list $sc_par_lst = new sql_type_list([])
    ): sql_par
    {
        $sc->set_class($this::class, new sql_type_list([sql_type::USER]));
        return parent::load_sql_user_changes($sc, $sc_par_lst);
    }

    /**
     * get a similar reference
     */
    function get_similar(): component_link
    {
        $result = new component_link($this->user());

        $db_chk = clone $this;
        $db_chk->reset();
        $db_chk->load_by_link_and_pos($this->view()->id(), $this->component()->id(), $this->order_nbr);
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
            array($this->from_name . sql_db::FLD_EXT_ID, $this->to_name . sql_db::FLD_EXT_ID, user::FLD_ID, 'order_nbr'),
            array($this->view()->id(), $this->component()->id(), $this->user()->id(), $this->order_nbr));
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
    function db_fields_all(sql_type_list $sc_par_lst = new sql_type_list([])): array
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
     * @return sql_par_field_list list 3 entry arrays with the database field name, the value and the sql type that have been updated
     */
    function db_fields_changed(
        sandbox|component_link $sbx,
        sql_type_list          $sc_par_lst = new sql_type_list([])
    ): sql_par_field_list
    {
        global $cng_fld_cac;

        $sc = new sql();
        $do_log = $sc_par_lst->incl_log();
        $usr_tbl = $sc_par_lst->is_usr_tbl();
        $table_id = $sc->table_id($this::class);

        $lst = parent::db_fields_changed($sbx, $sc_par_lst);
        // for the standard table the type field should always be included because it is part of the prime index
        if ($sbx->predicate_id() <> $this->predicate_id() or (!$usr_tbl and $sc_par_lst->is_insert())) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . component_link_type::FLD_ID,
                    $cng_fld_cac->id($table_id . component_link_type::FLD_ID),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            global $cmp_lnk_typ_cac;
            $lst->add_type_field(
                component_link_type::FLD_ID,
                type_object::FLD_NAME,
                $this->predicate_id(),
                $sbx->predicate_id(),
                $cmp_lnk_typ_cac
            );
        }
        if ($sbx->pos() <> $this->pos()) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . self::FLD_ORDER_NBR,
                    $cng_fld_cac->id($table_id . self::FLD_ORDER_NBR),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                self::FLD_ORDER_NBR,
                $this->pos(),
                self::FLD_ORDER_NBR_SQL_TYP,
                $sbx->pos()
            );
        }
        if ($sbx->pos_type_id() <> $this->pos_type_id()) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . self::FLD_POS_TYPE,
                    $cng_fld_cac->id($table_id . self::FLD_POS_TYPE),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            global $pos_typ_cac;
            $lst->add_type_field(
                self::FLD_POS_TYPE,
                self::FLD_POS_TYPE_NAME,
                $this->pos_type_id(),
                $sbx->pos_type_id(),
                $pos_typ_cac
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
            // TODO easy move to id function of type list
            if ($this->style_id() < 0) {
                log_err('component link style for ' . $this->dsp_id() . ' not found');
            }
            $lst->add_type_field(
                self::FLD_STYLE,
                view_style::FLD_NAME,
                $this->style_id(),
                $sbx->style_id(),
                $msk_sty_cac
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
        $pos = $this->pos();
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
