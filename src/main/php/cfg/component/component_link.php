<?php

/*

    model/view/component_link.php - link a single display component/element to a view
    -----------------------------

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

include_once DB_PATH . 'sql_par_type.php';
include_once API_VIEW_PATH . 'component_link.php';

use api\view\component_link as component_link_api;
use cfg\db\sql;
use cfg\db\sql_db;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
use cfg\db\sql_par;
use cfg\db\sql_par_type;
use cfg\export\sandbox_exp;
use cfg\library;
use cfg\sandbox;
use cfg\sandbox_link_with_type;
use cfg\user;
use cfg\view;

class component_link extends sandbox_link_with_type
{

    /*
     * database link
     */

    // the database and JSON object field names used only for formula links
    const TBL_COMMENT = 'to link components to views with an n:m relation';
    const FLD_ID = 'component_link_id';
    const FLD_ORDER_NBR = 'order_nbr';
    const FLD_POS_COM = 'the position of the component e.g. right or below';
    const FLD_POS_TYPE = 'position_type_id';

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
        sandbox::FLD_EXCLUDED,
        sandbox::FLD_SHARE,
        sandbox::FLD_PROTECT
    );
    // all database field names excluding the id used to identify if there are some user specific changes
    const ALL_SANDBOX_FLD_NAMES = array(
        self::FLD_ORDER_NBR,
        self::FLD_POS_TYPE,
        sandbox::FLD_EXCLUDED,
        sandbox::FLD_SHARE,
        sandbox::FLD_PROTECT
    );
    // list of fields that select the objects that should be linked
    const FLD_LST_LINK = array(
        [view::FLD_ID, sql_field_type::INT, sql_field_default::NOT_NULL, sql::INDEX, view::class, ''],
        [component::FLD_ID, sql_field_type::INT, sql_field_default::NOT_NULL, sql::INDEX, component::class, ''],
    );
    // list of MANDATORY fields that CAN be CHANGEd by the user
    const FLD_LST_MUST_BUT_STD_ONLY = array(
        [self::FLD_ORDER_NBR, sql_field_type::INT, sql_field_default::NOT_NULL, '', '', ''],
        [component_link_type::FLD_ID, sql_field_type::INT, sql_field_default::ONE, sql::INDEX, component_link_type::class, ''],
        [position_type::FLD_ID, sql_field_type::INT, sql_field_default::TWO, sql::INDEX, position_type::class, self::FLD_POS_COM],
    );
    // list of fields that CAN be CHANGEd by the user
    const FLD_LST_MUST_BUT_USER_CAN_CHANGE = array(
        [self::FLD_ORDER_NBR, sql_field_type::INT, sql_field_default::NULL, '', '', ''],
        [component_link_type::FLD_ID, sql_field_type::INT, sql_field_default::NULL, sql::INDEX, component_link_type::class, ''],
        [position_type::FLD_ID, sql_field_type::INT, sql_field_default::NULL, sql::INDEX, position_type::class, self::FLD_POS_COM],
    );


    /*
     * code links
     */

    const POS_BELOW = 1;  // the view component is placed below the previous component
    const POS_SIDE = 2;   // the view component is placed on the right (or left for right to left writing) side of the previous component


    /*
     * object vars
     */

    public ?int $order_nbr = null;          // to sort the display item

    // to deprecate
    public ?int $pos_type_id = null;        // defines the position of the view component relative to the previous item (1 = below, 2= side, )
    public ?string $pos_code = null;        // side or below or ....


    /*
     * construct and map
     */

    function __construct(user $usr)
    {
        parent::__construct($usr);
        $lib = new library();
        $this->obj_name = $lib->class_to_name(component_link::class);
        $this->from_name = $lib->class_to_name(view::class);
        $this->to_name = $lib->class_to_name(component::class);

        $this->rename_can_switch = UI_CAN_CHANGE_VIEW_COMPONENT_LINK;

        $this->reset_objects($usr);
    }

    function reset(): void
    {
        parent::reset();

        $this->reset_objects($this->user());

        $this->order_nbr = null;
        $this->pos_type_id = null;
        $this->pos_code = null;
    }

    /**
     * reset the in memory fields used e.g. if some ids are updated
     * @param user $usr the user for whom this link is valid
     */
    private function reset_objects(user $usr): void
    {
        // assign the object specific objects to the standard link object
        // to enable the usage of the standard user sandbox link function for this view component link object
        $this->fob = new view($usr); // the display (view) object (used to save the correct name in the log)
        $this->tob = new component($usr); // the display component (view entry) object (used to save the correct name in the log)
    }

    /**
     * map the database fields to the object fields
     *
     * @param array|null $db_row with the data directly from the database
     * @param bool $load_std true if only the standard user sandbox object ist loaded
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
            $this->fob = new view($this->user());
            $this->fob->set_id($db_row[view::FLD_ID]);
            $this->tob = new component($this->user());
            $this->tob->set_id($db_row[component::FLD_ID]);
            $this->order_nbr = $db_row[self::FLD_ORDER_NBR];
            $this->pos_type_id = $db_row[self::FLD_POS_TYPE];
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
        parent::set_id($id);
        $this->set_view($msk);
        $this->set_component($cmp);
        $this->set_pos($pos);
    }

    /**
     * rename to standard link to object to view
     * @param view $msk
     */
    function set_view(view $msk): void
    {
        $this->fob = $msk;
    }

    /**
     * rename to standard link to object to component
     * @param component $cmp
     */
    function set_component(component $cmp): void
    {
        $this->tob = $cmp;
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
     * rename to standard link from object to view
     * @return object
     */
    function view(): object
    {
        return $this->fob;
    }

    /**
     * rename to standard link to object to component
     * @return object
     */
    function component(): object
    {
        return $this->tob;
    }

    /**
     * expose the order number as pos
     * @return int|null
     */
    function pos(): ?int
    {
        return $this->order_nbr;
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
        if ($this->tob != null) {
            $api_obj->set_component($this->tob->api_obj());
        }
        $api_obj->set_pos($this->order_nbr);

        //$api_obj->set_type_id($this->type_id());
    }

    /**
     * @return component_link_api the view component frontend api object
     */
    function api_obj(): object
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
     * loading
     */

    /**
     * create an SQL statement to retrieve the parameters of the standard view component link from the database
     *
     * @param sql $sc with the target db_type set
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_standard_sql(sql $sc, string $class = self::class): sql_par
    {
        // try to get the search values from the objects
        if ($this->id <= 0) {
            $this->id = 0;
        }

        $sc->set_class(self::class);
        $qp = new sql_par(self::class);
        if ($this->id != 0) {
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
        } elseif ($this->fob->id() > 0 and $this->tob->id() > 0) {
            $sc->add_where(view::FLD_ID, $this->fob->id());
            $sc->add_where(component::FLD_ID, $this->tob->id());
        } else {
            log_err('Cannot load default component link because id is missing');
        }
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * load the view component link parameters for all users
     * @param sql_par|null $qp placeholder to align the function parameters with the parent
     * @param string $class the name of this class to be delivered to the parent function
     * @return bool true if the standard view component link has been loaded
     */
    function load_standard(?sql_par $qp = null, string $class = self::class): bool
    {

        global $db_con;
        $result = false;

        $qp = $this->load_standard_sql($db_con->sql_creator(), $class);

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
     * create the common part of an SQL statement to retrieve the parameters of a view component link from the database
     *
     * @param sql $sc with the target db_type set
     * @param string $query_name the name extension to make the query name unique
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql $sc, string $query_name, string $class = self::class): sql_par
    {
        $qp = parent::load_sql_obj_vars($sc, $class);
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
        $qp = parent::load_sql_obj_vars($sc, self::class);
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
     * load a named user sandbox object by name
     * @param view $dsp the view to which the component should be added
     * @param component $cmp the phrase that is linked to the formula
     * @param string $class the name of the child class from where the call has been triggered
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_link(view $dsp, component $cmp, string $class = self::class): int
    {
        $id = parent::load_by_link_id($dsp->id(), 0, $cmp->id(), $class);
        // no need to reload the linked objects, just assign it
        if ($id != 0) {
            $this->set_view($dsp);
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
     * to load the related objects if the link object is loaded by an external query like in user_display to show the sandbox
     * @returns bool true if a link has been loaded
     */
    function load_objects(): bool
    {
        $result = true;
        if ($this->view() != null) {
            if ($this->view()->id() > 0 and $this->view()->name() == '') {
                $dsp = new view($this->user());
                if ($dsp->load_by_id($this->view()->id())) {
                    $this->set_view($dsp);
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
        return component_link::FLD_POS_TYPE;
    }

    function all_sandbox_fields(): array
    {
        return self::ALL_SANDBOX_FLD_NAMES;
    }


    /*
     * display functions
     */

    /**
     * @return string the name of the preloaded view component position type
     */
    private function pos_type_name(): string
    {
        global $position_types;
        return $position_types->name($this->type_id);
    }

    // remember the move of a display component
    // up only the component that has been move by the user
    // and not all other component changed, because this would be more confusing
    private function log_move($direction)
    {

    }

    // move one view component
    // TODO load to list once, resort and write all positions with one SQL statement
    private function move($direction): bool
    {
        $result = false;

        // load any missing parameters
        if ($this->id > 0) {
            $this->load_by_id($this->id);
        } elseif ($this->fob->id() != 0 and $this->tob->id() != 0) {
            $this->load_by_link_id($this->fob->id(), 0, $this->tob->id(), self::class);
        }
        $this->load_objects();

        // check the all minimal input parameters
        if ($this->id <= 0) {
            log_err("Cannot load the view component link.", "component_link->move");
        } elseif ($this->fob->id() <= 0 or $this->tob->id() <= 0) {
            log_err("The view component id and the view component id must be given to move it.", "component_link->move");
        } else {
            log_debug('component_link->move ' . $direction . ' ' . $this->dsp_id());

            // new reorder code that can create a separate order for each user
            if (!isset($this->fob) or !isset($this->tob)) {
                log_err("The view component and the view component cannot be loaded to move them.", "component_link->move");
            } else {
                $this->fob->load_components();

                // correct any wrong order numbers e.g. a missing number
                $order_number_corrected = false;
                log_debug('component_link->move check order numbers for ' . $this->fob->dsp_id());
                // TODO define the common sorting start number, which is 1 and not 0
                $order_nbr = 1;
                if ($this->view()->cmp_lnk_lst != null) {
                    foreach ($this->view()->cmp_lnk_lst->lst() as $cmp_lnk) {
                        // fix any wrong order numbers
                        if ($cmp_lnk->order_nbr != $order_nbr) {
                            log_debug('check order number of the view component '
                                . $cmp_lnk->dsp_id() . ' corrected from ' . $cmp_lnk->order_nbr
                                . ' to ' . $order_nbr . ' in ' . $this->fob->dsp_id());
                            //zu_err('Order number of the view component "'.$entry->name.'" corrected from '.$cmp_lnk->order_nbr.' to '.$order_nbr.'.', "component_link->move");
                            $cmp_lnk->order_nbr = $order_nbr;
                            $cmp_lnk->save();
                            $order_number_corrected = true;
                        }
                        log_debug('component_link->move check order numbers checked for '
                            . $this->fob->dsp_id() . ' and ' . $cmp_lnk->dsp_id() . ' at position ' . $order_nbr);
                        $order_nbr++;
                    }
                }
                if ($order_number_corrected) {
                    log_debug('component_link->move reload after correction');
                    $this->fob->load_components();
                    // check if correction was successful
                    $order_nbr = 0;
                    if ($this->fob->cmp_lst != null) {
                        foreach ($this->fob->cmp_lst->lst() as $entry) {
                            $cmp_lnk = new component_link($this->user());
                            $dsp = new view($this->user());
                            $dsp->load_by_id($this->fob->id());
                            $cmp_lnk->load_by_link($dsp, $entry);
                            if ($cmp_lnk->order_nbr != $order_nbr) {
                                log_err('Component link ' . $cmp_lnk->dsp_id() . ' should have position ' . $order_nbr . ', but is ' . $cmp_lnk->order_nbr, "component_link->move");
                            }
                        }
                    }
                }
                log_debug('component_link->move order numbers checked for ' . $this->fob->dsp_id());

                // actually move the selected component
                // TODO what happens if the another user has deleted some components?
                $order_nbr = 1;
                $prev_entry = null;
                $prev_entry_down = false;
                if ($this->fob->cmp_lnk_lst != null) {
                    foreach ($this->fob->cmp_lnk_lst->lst() as $cmp_lnk) {
                        // get the component link (TODO add the order number to the entry lst, so that this loading is not needed)
                        //$cmp_lnk = new component_link($this->user());
                        //$dsp = new view($this->user());
                        //$dsp->load_by_id($this->fob->id());
                        //$cmp_lnk->load_by_link($dsp, $entry);
                        if ($prev_entry_down) {
                            if (isset($prev_entry)) {
                                log_debug('component_link->move order number of the view component ' . $prev_entry->tob->dsp_id() . ' changed from ' . $prev_entry->order_nbr . ' to ' . $order_nbr . ' in ' . $this->fob->dsp_id());
                                $prev_entry->order_nbr = $order_nbr;
                                $prev_entry->save();
                                $prev_entry = null;
                            }
                            log_debug('component_link->move order number of the view component "' . $cmp_lnk->tob->name() . '" changed from ' . $cmp_lnk->order_nbr . ' to ' . $order_nbr . ' - 1 in "' . $this->fob->name() . '"');
                            $cmp_lnk->order_nbr = $order_nbr - 1;
                            $cmp_lnk->save();
                            $result = true;
                            $prev_entry_down = false;
                        }
                        if ($cmp_lnk->id() == $this->tob->id()) {
                            if ($direction == 'up') {
                                if ($cmp_lnk->order_nbr > 0) {
                                    log_debug('component_link->move order number of the view component ' . $cmp_lnk->tob->dsp_id() . ' changed from ' . $cmp_lnk->order_nbr . ' to ' . $order_nbr . ' - 1 in ' . $this->fob->dsp_id());
                                    $cmp_lnk->order_nbr = $order_nbr - 1;
                                    $cmp_lnk->save();
                                    $result = true;
                                    if (isset($prev_entry)) {
                                        log_debug('component_link->move order number of the view component ' . $prev_entry->tob->dsp_id() . ' changed from ' . $prev_entry->order_nbr . ' to ' . $order_nbr . ' in ' . $this->fob->dsp_id());
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
            $this->fob->load_components();
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

    // create a database record to save user specific settings for this component_link
    protected function add_usr_cfg(string $class = self::class): bool
    {
        global $db_con;
        $result = true;

        if (!$this->has_usr_cfg()) {
            if (isset($this->fob) and isset($this->tob)) {
                log_debug('component_link->add_usr_cfg for "' . $this->fob->name() . '"/"' . $this->tob->name() . '" by user "' . $this->user()->name . '"');
            } else {
                log_debug('component_link->add_usr_cfg for "' . $this->id . '" and user "' . $this->user()->name . '"');
            }

            // check again if there is not yet a record
            $db_con->set_class(self::class, true);
            $qp = new sql_par(self::class);
            $qp->name = 'component_link_add_usr_cfg';
            $db_con->set_name($qp->name);
            $db_con->set_usr($this->user()->id());
            $db_con->set_where_std($this->id);
            $qp->sql = $db_con->select_by_set_id();
            $qp->par = $db_con->get_par();
            $db_row = $db_con->get1($qp);
            if ($db_row != null) {
                $this->usr_cfg_id = $db_row[self::FLD_ID];
            }
            if (!$this->has_usr_cfg()) {
                // create an entry in the user sandbox
                $db_con->set_class(component_link::class, true);
                $log_id = $db_con->insert_old(array(self::FLD_ID, user::FLD_ID), array($this->id, $this->user()->id()));
                if ($log_id <= 0) {
                    log_err('Insert of user_component_link failed.');
                    $result = false;
                } else {
                    $result = true;
                }
            }
        }
        return $result;
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
        $result = $this->tob->export_obj($do_load);
        if ($this->order_nbr >= 0) {
            $result->position = $this->order_nbr;
        }
        return $result;
    }


    // check if the database record for the user specific settings can be removed

    /**
     * create an SQL statement to retrieve the user changes of the current view component link
     *
     * @param sql $sc with the target db_type set
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_user_changes(sql $sc, string $class = self::class): sql_par
    {
        $sc->set_class($class, true);
        return parent::load_sql_user_changes($sc, $class);
    }

    /**
     * get a similar reference
     */
    function get_similar(): component_link
    {
        $result = new component_link($this->user());

        $db_chk = clone $this;
        $db_chk->reset();
        $db_chk->load_by_link_and_pos($this->fob->id(), $this->tob->id(), $this->order_nbr);
        if ($db_chk->id > 0) {
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
     * @returns string any message that should be shown to the user or a empty string if everything is fine
     */
    private
    function save_field_order_nbr(sql_db $db_con, component_link $db_rec, component_link $std_rec): string
    {
        $result = '';
        if ($db_rec->order_nbr <> $this->order_nbr) {
            $log = $this->log_upd_field();
            $log->old_value = $db_rec->order_nbr;
            $log->new_value = $this->order_nbr;
            $log->std_value = $std_rec->order_nbr;
            $log->row_id = $this->id;
            $log->set_field(self::FLD_ORDER_NBR);
            $result .= $this->save_field_user($db_con, $log);
        }
        return $result;
    }

    /**
     * save all updated component_link fields excluding the name, because already done when adding a component_link
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param component_link|sandbox $db_rec the view component link as saved in the database before the update
     * @param component_link|sandbox $std_rec the default parameter used for this view component link
     * @returns string any message that should be shown to the user or a empty string if everything is fine
     */
    function save_fields(sql_db $db_con, component_link|sandbox $db_rec, component_link|sandbox $std_rec): string
    {
        $result = $this->save_field_order_nbr($db_con, $db_rec, $std_rec);
        $result .= $this->save_field_type($db_con, $db_rec, $std_rec);
        $result .= $this->save_field_excluded($db_con, $db_rec, $std_rec);
        log_debug('all fields for ' . $this->dsp_id() . ' has been saved');
        return $result;
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
            array($this->from_name . sql_db::FLD_EXT_ID, $this->to_name . sql_db::FLD_EXT_ID, "user_id", 'order_nbr'),
            array($this->view()->id(), $this->component()->id(), $this->user()->id(), $this->order_nbr));
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

}
