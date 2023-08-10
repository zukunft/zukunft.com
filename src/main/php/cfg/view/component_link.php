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

namespace cfg;

include_once DB_PATH . 'sql_par_type.php';

use api\component_api;
use cfg\db\sql_creator;
use cfg\db\sql_par_type;
use html\view\view_dsp_old;

class component_link extends sandbox_link_with_type
{

    /*
     * database link
     */

    // the database and JSON object field names used only for formula links
    const FLD_ID = 'component_link_id';
    const FLD_ORDER_NBR = 'order_nbr';
    const FLD_POS_TYPE = 'position_type';

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
        $this->obj_type = sandbox::TYPE_LINK;
        $this->obj_name = sql_db::TBL_COMPONENT_LINK;
        $this->from_name = sql_db::TBL_VIEW;
        $this->to_name = sql_db::TBL_COMPONENT;

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
            $this->fob->set_id($db_row[view::FLD_ID]);
            $this->tob->set_id($db_row[component::FLD_ID]);
            $this->order_nbr = $db_row[self::FLD_ORDER_NBR];
            $this->pos_type_id = $db_row[self::FLD_POS_TYPE];
        }
        return $result;
    }

    /*
     * loading
     */

    /**
     * create an SQL statement to retrieve the parameters of the standard view component link from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_standard_sql(sql_creator $sc, string $class = self::class): sql_par
    {
        // try to get the search values from the objects
        if ($this->id <= 0) {
            $this->id = 0;
        }

        $sc->set_type(sql_db::TBL_COMPONENT_LINK);
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
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name extension to make the query name unique
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    protected function load_sql(sql_creator $sc, string $query_name, string $class = self::class): sql_par
    {
        $qp = parent::load_sql_obj_vars($sc, $class);
        $qp->name .= $query_name;

        $sc->set_type(sql_db::TBL_COMPONENT_LINK);
        $sc->set_name($qp->name);
        $sc->set_usr($this->user()->id());
        $sc->set_fields(self::FLD_NAMES_LINK);
        $sc->set_usr_num_fields(self::FLD_NAMES_NUM_USR);

        return $qp;
    }

    /**
     * create an SQL statement to load the component_link by the link id
     *
     * @param sql_creator $sc with the target db_type set
     * @param int $dsp_id the view id
     * @param int $type_id the link type id
     * @param int $cmp_id the component id
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_link_and_type(sql_creator $sc, int $dsp_id, int $type_id, int $cmp_id, string $class): sql_par
    {
        return parent::load_sql_by_link($sc, $dsp_id, $type_id, $cmp_id, self::class);
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
        $qp = parent::load_sql_obj_vars($sc, self::class);
        $qp->name .= 'max_pos';

        $sc->set_type(sql_db::TBL_COMPONENT_LINK);
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
            if (array_key_exists(sql_creator::MAX_PREFIX . self::FLD_ORDER_NBR, $db_row)) {
                return $db_row[sql_creator::MAX_PREFIX . self::FLD_ORDER_NBR];
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
            $this->fob = $dsp;
            $this->tob = $cmp;
        }
        return $id;
    }

    /**
     * to load the related objects if the link object is loaded by an external query like in user_display to show the sandbox
     * @returns bool true if a link has been loaded
     */
    function load_objects(): bool
    {
        $result = true;
        if (!isset($this->fob) and $this->fob->id() > 0) {
            $dsp = new view($this->user());
            if ($dsp->load_by_id($this->fob->id())) {
                $this->fob = $dsp;
            } else {
                $result = false;
            }
        }
        if (!isset($this->tob) and $this->tob->id() > 0) {
            $cmp = new component($this->user());
            if ($cmp->load_by_id($this->tob->id())) {
                $this->tob = $cmp;
            } else {
                $result = false;
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
     * display the unique id fields
     * NEVER call any methods from this function because this function is used for debugging and a call can cause an endless loop
     * @returns string a programmer readable description of the link for unique identification
     */
    function dsp_id(): string
    {
        $result = '';

        if (isset($this->fob) and isset($this->tob)) {
            if ($this->fob->name() <> '' and $this->tob->name() <> '') {
                $result .= '"' . $this->tob->name() . '" in "'; // e.g. Company details
                $result .= $this->fob->name() . '"';     // e.g. cash flow statement
            }
            if ($this->fob->id <> 0 and $this->tob->id <> 0) {
                $result .= ' (' . $this->fob->id . ',' . $this->tob->id;
            }
            // fallback
            if ($result == '') {
                $result .= $this->fob->dsp_id() . ' to ' . $this->tob->dsp_id();
            }
        } else {
            $result .= 'view component objects not set';
        }
        if ($this->id > 0) {
            $result .= ' -> ' . $this->id . ')';
        } else {
            $result .= ', but no link id)';
        }
        if ($this->user() != null) {
            $result .= ' for user ' . $this->user()->id() . ' (' . $this->user()->name . ')';
        }
        return $result;
    }

    /**
     * @return string the name of the preloaded view component position type
     */
    private function pos_type_name(): string
    {
        global $component_position_types;
        return $component_position_types->name($this->type_id);
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
                if ($this->fob->cmp_lst != null) {
                    foreach ($this->fob->cmp_lst->lst() as $entry) {
                        // get the component link (TODO add the order number to the entry lst, so that this loading is not needed)
                        $cmp_lnk = new component_link($this->user());
                        $dsp = new view($this->user());
                        $dsp->load_by_id($this->fob->id());
                        $cmp_lnk->load_by_link($dsp, $entry);
                        // fix any wrong order numbers
                        if ($cmp_lnk->order_nbr != $order_nbr) {
                            log_debug('component_link->move check order number of the view component ' . $entry->dsp_id() . ' corrected from ' . $cmp_lnk->order_nbr . ' to ' . $order_nbr . ' in ' . $this->fob->dsp_id());
                            //zu_err('Order number of the view component "'.$entry->name.'" corrected from '.$cmp_lnk->order_nbr.' to '.$order_nbr.'.', "component_link->move");
                            $cmp_lnk->order_nbr = $order_nbr;
                            $cmp_lnk->save();
                            $order_number_corrected = true;
                        }
                        log_debug('component_link->move check order numbers checked for ' . $this->fob->dsp_id() . ' and ' . $entry->dsp_id() . ' at position ' . $order_nbr);
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
                if ($this->fob->cmp_lst != null) {
                    foreach ($this->fob->cmp_lst->lst() as $entry) {
                        // get the component link (TODO add the order number to the entry lst, so that this loading is not needed)
                        $cmp_lnk = new component_link($this->user());
                        $dsp = new view($this->user());
                        $dsp->load_by_id($this->fob->id());
                        $cmp_lnk->load_by_link($dsp, $entry);
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
                        if ($entry->id == $this->tob->id()) {
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
            $db_con->set_type(sql_db::TBL_COMPONENT_LINK, true);
            $qp = new sql_par(self::class);
            $qp->name = 'view_cmp_link_add_usr_cfg';
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
                $db_con->set_type(sql_db::TBL_USER_PREFIX . sql_db::TBL_COMPONENT_LINK);
                $log_id = $db_con->insert(array(self::FLD_ID, user::FLD_ID), array($this->id, $this->user()->id()));
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

    // check if the database record for the user specific settings can be removed

    /**
     * create an SQL statement to retrieve the user changes of the current view component link
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_user_changes(sql_creator $sc, string $class = self::class): sql_par
    {
        $sc->set_type(sql_db::TBL_COMPONENT_LINK, true);
        return parent::load_sql_user_changes($sc, $class);
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
        return $db_con->insert(
            array($this->from_name . sql_db::FLD_EXT_ID, $this->to_name . sql_db::FLD_EXT_ID, "user_id", 'order_nbr'),
            array($this->fob->id, $this->tob->id, $this->user()->id, $this->order_nbr));
    }

}
