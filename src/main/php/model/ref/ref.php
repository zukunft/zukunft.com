<?php

/*

  ref.php - a link between a phrase and another system such as wikidata
  -------
  
  TODO add to UI; add unit tests
  
  
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
  along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.
  
  To contact the authors write to:
  Timon Zielonka <timon@zukunft.com>
  
  Copyright (c) 1995-2021 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
   
*/

class ref
{

    // database fields
    public ?int $id = null;               // the database id of the reference
    public ?int $phr_id = null;           // the database id of the word, verb or formula
    public ?string $external_key = null;  // the unique key in the external system
    public ?int $ref_type_id = null;      // the id of the ref type

    // in memory only fields
    public ?user $usr = null;             // just needed for logging the changes
    public ?phrase $phr = null;           // the phrase object
    public ?ref_type $ref_type = null;    // the ref type object

    function reset()
    {
        $this->id = null;
        $this->phr_id = null;
        $this->external_key = '';
        $this->ref_type_id = null;

        $this->usr = null;
        $this->phr = null;
        $this->ref_type = null;
    }

    // test if the name is used already
    function load(): bool
    {
        global $db_con;
        $result = false;

        // check if the minimal input parameters are set
        if ($this->id <= 0 and ($this->phr_id <= 0 or $this->ref_type_id <= 0)) {
            log_err('Either the database ID (' . $this->id . ') or the phrase id (' . $this->phr_id . ') AND the reference type id (' . $this->ref_type_id . ') must be set to load a reference.', 'ref->load');
        } else {

            $db_con->set_type(DB_TYPE_REF);
            $db_con->set_usr($this->usr->id);
            $db_con->set_link_fields('phrase_id', 'ref_type_id');
            $db_con->set_fields(array('external_key'));
            $db_con->set_where_link($this->id, $this->phr_id, $this->ref_type_id);
            $sql = $db_con->select();

            if ($db_con->get_where() <> '') {
                $db_ref = $db_con->get1($sql);
                if ($db_ref != null) {
                    if ($db_ref['ref_id'] > 0) {
                        $this->id = $db_ref['ref_id'];
                        $this->phr_id = $db_ref['phrase_id'];
                        $this->external_key = $db_ref['external_key'];
                        $this->ref_type_id = $db_ref['ref_type_id'];
                        if ($this->load_objects()) {
                            $result = true;
                            log_debug('ref->load -> done ' . $this->dsp_id());
                        }
                    } else {
                        $this->id = 0;
                    }
                }
            }
        }
        return $result;
    }

    // to load the related objects if the reference object is loaded
    private function load_objects(): bool
    {
        $result = true;

        if (!isset($this->phr)) {
            if ($this->phr_id <> 0) {
                $phr = new phrase;
                $phr->id = $this->phr_id;
                $phr->usr = $this->usr;
                if ($phr->load()) {
                    $this->phr = $phr;
                    log_debug('ref->load_objects -> phrase ' . $this->phr->dsp_id() . ' loaded');
                } else {
                    $result = false;
                }
            }
        }
        if (!isset($this->ref_type)) {
            if ($this->ref_type_id > 0) {
                $this->ref_type = get_ref_type_by_id($this->ref_type_id);
                log_debug('ref->load_objects -> ref_type ' . $this->ref_type->name . ' loaded');
            }
        }

        log_debug('ref->load_objects -> done');
        return $result;
    }

    // import a link to external database from an imported object
    function import_obj($json_obj, bool $do_save = true): bool
    {
        $result = false;

        $this->reset();
        foreach ($json_obj as $key => $value) {
            if ($key == 'name') {
                $this->external_key = $value;
            }
            if ($key == 'type') {
                $this->ref_type = get_ref_type($value);

                if (!isset($this->ref_type)) {
                    log_err('Reference type for ' . $value . ' not found', 'ref->import_obj');
                } else {
                    $this->ref_type_id = get_ref_type_id($value);
                }
                log_debug('ref->import_obj -> ref_type set based on ' . $value . ' (' . $this->ref_type_id . ')');
            }
        }
        // to be able to log the object names
        if ($this->load_objects()) {
            if ($do_save) {
                if ($this->save() > 0) {
                    log_debug('ref->import_obj -> ' . $this->dsp_id());
                    $result = true;
                }
            }
        }

        return $result;
    }

    /**
     * create a reference object for export (so excluding e.g. the database id)
     * @return ref_exp a reduced reference object for the JSON message creation
     */
    function export_obj(): ref_exp
    {
        $result = new ref_exp();

        if ($this->external_key <> '') {
            $result->name = $this->external_key;
        }
        if ($this->ref_type <> '') {
            $result->type = $this->ref_type->code_id;
        }

        return $result;
    }

    /*
    display functions
    */

    /**
     * display the unique id fields
     */
    function dsp_id(): string
    {
        $result = $this->name();
        if ($result <> '') {
            if ($this->id > 0) {
                $result .= ' (' . $this->id . ')';
            }
        } else {
            $result .= $this->id;
        }
        return $result;
    }

    // create the unique name
    function name(): string
    {
        $result = '';

        if (isset($this->phr)) {
            $result .= 'ref of "' . $this->phr->name . '"';
        } else {
            if (isset($this->phr_id)) {
                if ($this->phr_id > 0) {
                    $result .= 'ref of phrase id ' . $this->phr_id . ' ';
                }
            }
        }
        if (isset($this->ref_type)) {
            $result .= 'to "' . $this->ref_type->name . '"';
        } else {
            if (isset($this->ref_type_id)) {
                if ($this->ref_type_id > 0) {
                    $result .= 'to type id ' . $this->ref_type_id . ' ';
                }
            }
        }
        return $result;
    }

    // set the log entry parameter for a new reference
    function log_add(): user_log_link
    {
        log_debug('ref->log_add ' . $this->dsp_id());

        // check that the minimal parameters are set
        if (!isset($this->phr)) {
            log_err('The phrase object must be set to log adding an external reference.', 'ref->log_add');
        }
        if (!isset($this->ref_type)) {
            log_err('The reference type object must be set to log adding an external reference.', 'ref->log_add');
        }

        $log = new user_log_link;
        $log->usr = $this->usr;
        $log->action = 'add';
        $log->table = 'refs';
        // TODO review in log_link
        // TODO object must be loaded before it can be logged
        $log->new_from = $this->phr;
        $log->new_link = $this->ref_type;
        $log->new_to = $this;
        $log->row_id = 0;
        $log->add();

        return $log;
    }

    // set the main log entry parameters for updating one reference field
    function log_upd($db_rec): user_log_link
    {
        log_debug('ref->log_upd ' . $this->dsp_id());
        $log = new user_log_link;
        $log->usr = $this->usr;
        $log->action = 'update';
        $log->table = 'refs';
        $log->old_from = $db_rec->phr;
        $log->old_link = $db_rec->ref_type;
        $log->old_to = $db_rec;
        $log->new_from = $this->phr;
        $log->new_link = $this->ref_type;
        $log->new_to = $this;
        $log->row_id = $this->id;
        $log->add();

        return $log;
    }

    // set the log entry parameter to delete a reference
    function log_del(): user_log_link
    {
        log_debug('ref->log_del ' . $this->dsp_id());

        // check that the minimal parameters are set
        if (!isset($this->phr)) {
            log_err('The phrase object must be set to log deletion of an external reference.', 'ref->log_del');
        }
        if (!isset($this->ref_type)) {
            log_err('The reference type object must be set to log deletion of an external reference.', 'ref->log_del');
        }

        $log = new user_log_link;
        $log->usr = $this->usr;
        $log->action = 'del';
        $log->table = 'refs';
        $log->old_from = $this->phr;
        $log->old_link = $this->ref_type;
        $log->old_to = $this;
        $log->row_id = $this->id;
        $log->add();

        return $log;
    }

    // update a ref in the database or update the existing
    // returns the database id of the created reference or 0 if not successful
    private function add(): int
    {
        log_debug('ref->add ' . $this->dsp_id());

        global $db_con;
        $result = 0;

        // log the insert attempt first
        $log = $this->log_add();
        if ($log->id > 0) {
            // insert the new reference
            $db_con->set_type(DB_TYPE_REF);
            $db_con->set_usr($this->usr->id);

            $this->id = $db_con->insert(
                array('phrase_id', 'external_key', 'ref_type_id'),
                array($this->phr_id, $this->external_key, $this->ref_type_id));
            if ($this->id > 0) {
                // update the id in the log for the correct reference
                if ($log->add_ref($this->id)) {
                    $result = $this->id;
                }
            } else {
                log_err('Adding reference ' . $this->dsp_id() . ' failed.', 'ref->add');
            }
        }

        return $result;
    }

    // get a similar reference
    function get_similar(): ref
    {
        $result = null;
        log_debug('ref->get_similar ' . $this->dsp_id());

        $db_chk = clone $this;
        $db_chk->reset();
        $db_chk->phr_id = $this->phr_id;
        $db_chk->ref_type_id = $this->ref_type_id;
        $db_chk->usr = $this->usr;
        $db_chk->load();
        if ($db_chk->id > 0) {
            log_debug('ref->get_similar an external reference for ' . $this->dsp_id() . ' already exists');
            $result = $db_chk;
        }

        return $result;
    }

    // update a ref in the database or update the existing
    // returns the id of the updated or created reference
    function save(): int
    {
        log_debug('ref->save ' . $this->dsp_id());

        global $db_con;
        $result = 0;

        // build the database object because the is anyway needed
        $db_con->set_usr($this->usr->id);
        $db_con->set_type(DB_TYPE_REF);

        // check if the external reference is supposed to be added
        if ($this->id <= 0) {
            // check possible duplicates before adding
            log_debug('ref->save check possible duplicates before adding ' . $this->dsp_id());
            $similar = $this->get_similar();
            if (isset($similar)) {
                if ($similar->id <> 0) {
                    $this->id = $similar->id;
                }
            }
        }

        // create a new object or update an existing
        if ($this->id <= 0) {
            log_debug('ref->save add');
            $result = $this->add();
        } else {
            log_debug('ref->save update');

            // read the database values to be able to check if something has been changed;
            // done first, because it needs to be done for user and general object values
            $db_rec = clone $this;
            $db_rec->reset();
            $db_rec->id = $this->id;
            $db_rec->usr = $this->usr;
            $db_rec->load();
            log_debug('ref->save reloaded from db');

            // if needed log the change and update the database
            if ($this->external_key <> $db_rec->external_key) {
                $log = $this->log_upd($db_rec);
                if ($log->id > 0) {
                    $db_con->set_type(DB_TYPE_REF);
                    if ($db_con->update($this->id, 'external_key', $this->external_key)) {
                        $result = $this->id;
                        log_debug('ref->save update ... done.' . $result . '');
                    }
                }
            }
        }
        return $result;
    }

    // delete a reference of return false if it fails
    function del(): bool
    {
        global $db_con;
        $result = false;

        if (!$this->load()) {
            log_warning('Reload of ref ' . $this->dsp_id() . ' for deletion failed', 'ref->del');
        } else {
            if ($this->id <= 0) {
                log_warning('Delete failed, because it seems that the ref ' . $this->dsp_id() . ' has been deleted in the meantime.', 'ref->del');
            } else {
                $log = $this->log_del();
                if ($log->id > 0) {
                    $db_con->set_type(DB_TYPE_REF);
                    $result = $db_con->delete('ref_id', $this->id);
                    log_debug('ref->del update -> done.' . $result . '');
                }
            }
        }
        return $result;
    }

}