<?php

/*

    model/sandbox/sandbox_link_list.php - add the link specific functions to the sandbox list object
    -----------------------------------

    The main sections of this object are
    - modify:            change potentially all items of this list object


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

    Copyright (c) 1995-2025 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\cfg\sandbox;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_SANDBOX . 'sandbox_list.php';
//include_once paths::MODEL_COMPONENT . 'component.php';
//include_once paths::MODEL_COMPONENT . 'component_link.php';
//include_once paths::MODEL_FORMULA . 'formula_link.php';
//include_once paths::MODEL_PHRASE . 'term.php';
//include_once paths::MODEL_USER . 'user.php';
//include_once paths::MODEL_USER . 'user_message.php';
//include_once paths::MODEL_VIEW . 'view.php';
//include_once paths::MODEL_VIEW . 'term_view.php';
//include_once paths::MODEL_VIEW . 'view_relation.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_ENUM . 'value_types.php';
include_once paths::SHARED_HELPER . 'CombineObject.php';
include_once paths::SHARED_HELPER . 'IdObject.php';
include_once paths::SHARED_HELPER . 'Message.php';
include_once paths::SHARED_HELPER . 'TextIdObject.php';

use Zukunft\ZukunftCom\main\php\cfg\component\component;
use Zukunft\ZukunftCom\main\php\cfg\component\component_link;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_link;
use Zukunft\ZukunftCom\main\php\cfg\phrase\term;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\view\view;
use Zukunft\ZukunftCom\main\php\cfg\view\term_view;
use Zukunft\ZukunftCom\main\php\cfg\view\view_relation;
use Zukunft\ZukunftCom\main\php\shared\enum\value_types;
use Zukunft\ZukunftCom\main\php\shared\helper\CombineObject;
use Zukunft\ZukunftCom\main\php\shared\helper\IdObject;
use Zukunft\ZukunftCom\main\php\shared\helper\Message;
use Zukunft\ZukunftCom\main\php\shared\helper\TextIdObject;

class sandbox_link_list extends sandbox_list
{

    /*
     *  object vars
     */

    // memory vs speed optimize vars for faster finding the list position by the link key
    private array $key_pos_lst;
    private bool $key_lst_dirty;


    /*
     * construct and map
     */

    function __construct(user $usr, array $lst = [])
    {
        $this->key_pos_lst = [];
        $this->set_hash_dirty();

        parent::__construct($usr, $lst);
    }


    /*
     * set and get
     */

    /**
     * to be called after the lists have been updated
     * but the index list has not yet been updated
     */
    protected function set_hash_dirty(): void
    {
        parent::set_hash_dirty();
        $this->key_lst_dirty = true;
    }

    /**
     * @return true if the link key hash table is updated
     */
    private function is_key_list_dirty(): bool
    {
        return $this->key_lst_dirty;
    }

    /**
     * TODO add a unit test
     * @returns array with all unique link keys of this list with the position key within this list
     */
    protected function key_pos_list(): array
    {
        $result = array();
        if ($this->is_key_list_dirty()) {
            foreach ($this->lst() as $key => $obj) {
                $result[$obj->key()] = $key;
            }
            $this->key_pos_lst = $result;
            $this->key_lst_dirty = false;
        } else {
            $result = $this->key_pos_lst;
        }
        return $result;
    }


    /*
     * modify
     */

    /**
     * add a link based on parts to this list without saving it to the database
     * @return true if the link has been added
     */
    function add(int $id, view $msk, component|term $sbx, int $pos = 0): bool
    {
        if ($sbx::class == term::class) {
            $new_lnk = new term_view($this->get_user());
        } else {
            $new_lnk = new component_link($this->get_user());
        }
        $new_lnk->set($id, $msk, $sbx, $pos);
        return $this->add_link($new_lnk);
    }

    /**
     * add a link to this list without saving it to the database
     * @return true if the link has been added
     */
    function add_link(
        component_link|term_view|view_relation|formula_link $lnk_to_add,
        bool                                  $allow_duplicates = false
    ): bool
    {
        $added = false;
        if ($this->can_add($lnk_to_add)) {
            if ($lnk_to_add->id() == 0) {
                // a link that is not yet in the database has no id, and the list is exactly the
                // place where it waits for its insert (the readiness ladder of
                // docs/llm/architecture.md), so it is added directly, because
                // sandbox_list::add_obj skips an object without an id;
                // can_add above has already excluded a duplicate
                $this->add_direct($lnk_to_add);
            } else {
                $this->add_obj($lnk_to_add, $allow_duplicates);
            }
            $added = true;
        }
        return $added;
    }

    /**
     * add one link to the list of user sandbox objects,
     * but only if it is not yet part of the list
     * based on the names (not the db id) of the linked objects
     * @param component_link|term_view|view_relation|formula_link $obj_to_add the backend object that should be added
     * @param user_message|Message $msg to report why a link has not been added e.g. a mandatory value is missing
     * @param bool $allow_duplicates true if the list can contain the same entry twice e.g. for the components
     * @return bool true if the link has really been added to the list
     */
    function add_link_by_key(
        component_link|term_view|view_relation|formula_link $obj_to_add,
        user_message|Message                                $msg,
        bool                                                $allow_duplicates = false
    ): bool
    {
        $added = false;

        // add a missing user to the object
        // or check if the object user matches the list user
        // and allow exceptions only for admin users
        $msg->merge($this->add_user_check($obj_to_add));

        // if a sandbox object has the names of the objects to link, but not (yet) an id, add it nevertheless to the list
        if (!in_array($obj_to_add->get_key(), array_keys($this->key_pos_list())) or $allow_duplicates) {
            $rdy_msg = new user_message(); // can_be_ready returns $msg->is_ok(), so it must judge only this link
            if ($obj_to_add->can_be_ready($rdy_msg)) {
                $this->add_direct($obj_to_add);
                $added = true;
            } else {
                // only a real rejection reaches the caller, so it can tell the user why the link
                // is missing; a caller that adds links before the ids are known (the import)
                // passes an own message, because a link that is not ready yet is normal there
                $msg->merge($rdy_msg);
            }
        } else {
            log_warning('trying to add linked object via id but add_link_by_key has been called');
            $this->add_link($obj_to_add, $allow_duplicates);
            $added = true;
        }

        return $added;
    }

    /**
     * add the object to the list without duplicate check
     * and add the id to the id hash
     *
     * @param IdObject|TextIdObject|CombineObject|value_types|component_link|term_view|view_relation $obj_to_add
     * @return void
     */
    protected function add_direct(IdObject|TextIdObject|CombineObject|value_types|component_link|term_view|view_relation $obj_to_add): void
    {
        if (!$this->is_key_list_dirty()) {
            $this->key_pos_lst[$obj_to_add->get_key()] = count($this->lst());
        }
        parent::add_direct($obj_to_add);
    }


    /*
      * internal
      */

    /**
     * test if the link already exists and if yes return false to prevent duplicates
     * @param sandbox_link $lnk_to_add the link that should be added to the list
     * @return bool true if the link can be added
     */
    protected function can_add(sandbox_link $lnk_to_add): bool
    {
        $can_add = true;

        if (!$this->is_empty()) {
            foreach ($this->lst() as $lnk) {
                if ($can_add) {
                    if ($this->is_same_link($lnk, $lnk_to_add)) {
                        $can_add = false;
                    }
                    if ($lnk->id() == $lnk_to_add->id()
                        and $lnk->id() != 0 and $lnk_to_add->id() != 0
                        and $lnk->id() !== null and $lnk_to_add->id() !== null) {
                        $can_add = false;
                    }
                }
            }
        }
        return $can_add;
    }

    /**
     * test if two links connect the same objects, so that only one of them belongs in this list
     * can be overwritten by the child class e.g. if the same link at another position is allowed
     *
     * @param sandbox_link $lnk a link that is already in this list
     * @param sandbox_link $lnk_to_add the link that should be added to the list
     * @return bool true if both links connect the same objects
     */
    protected function is_same_link(sandbox_link $lnk, sandbox_link $lnk_to_add): bool
    {
        // by id or name, because the linked objects of an import are identified by
        // their name until they have been added to the database
        return $lnk->from_id_or_name() == $lnk_to_add->from_id_or_name()
            and $lnk->to_id_or_name() == $lnk_to_add->to_id_or_name();
    }

}
