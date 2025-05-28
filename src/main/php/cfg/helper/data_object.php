<?php

/*

    model/helper/data_object.php - a header object for all data objects e.g. phrase_list, values, formulas
    --------------------------

    the views are only added here for selection boxes


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

namespace cfg\helper;

// more specific includes are switched off to avoid circular includes
//include_once MODEL_COMPONENT_PATH . 'component.php';
//include_once MODEL_COMPONENT_PATH . 'component_list.php';
//include_once MODEL_FORMULA_PATH . 'formula.php';
//include_once MODEL_FORMULA_PATH . 'formula_list.php';
//include_once MODEL_IMPORT_PATH . 'import.php';
include_once MODEL_USER_PATH . 'user.php';
include_once MODEL_USER_PATH . 'user_message.php';
//include_once MODEL_REF_PATH . 'ref.php';
include_once MODEL_REF_PATH . 'ref_list.php';
//include_once MODEL_REF_PATH . 'source.php';
//include_once MODEL_REF_PATH . 'source_list.php';
//include_once MODEL_PHRASE_PATH . 'phrase.php';
//include_once MODEL_PHRASE_PATH . 'phrase_list.php';
//include_once MODEL_PHRASE_PATH . 'term.php';
//include_once MODEL_PHRASE_PATH . 'term_list.php';
//include_once MODEL_VALUE_PATH . 'value.php';
//include_once MODEL_VALUE_PATH . 'value_base.php';
//include_once MODEL_VALUE_PATH . 'value_list.php';
//include_once MODEL_VIEW_PATH . 'view_list.php';
//include_once MODEL_VERB_PATH . 'verb.php';
//include_once MODEL_VERB_PATH . 'verb_list.php';
//include_once MODEL_VIEW_PATH . 'view.php';
//include_once MODEL_VIEW_PATH . 'view_list.php';
//include_once MODEL_WORD_PATH . 'word.php';
//include_once MODEL_WORD_PATH . 'word_list.php';
//include_once MODEL_WORD_PATH . 'triple.php';
//include_once MODEL_WORD_PATH . 'triple_list.php';
include_once API_OBJECT_PATH . 'api_message.php';
include_once SHARED_CONST_PATH . 'triples.php';
include_once SHARED_CONST_PATH . 'words.php';
include_once SHARED_ENUM_PATH . 'messages.php';
include_once SHARED_TYPES_PATH . 'api_type_list.php';
include_once SHARED_PATH . 'json_fields.php';
include_once SHARED_PATH . 'library.php';

use cfg\component\component;
use cfg\component\component_list;
use cfg\formula\formula;
use cfg\formula\formula_list;
use cfg\import\import;
use cfg\phrase\phrase;
use cfg\phrase\phrase_list;
use cfg\phrase\term;
use cfg\phrase\term_list;
use cfg\ref\ref;
use cfg\ref\ref_list;
use cfg\ref\source;
use cfg\ref\source_list;
use cfg\user\user;
use cfg\user\user_message;
use cfg\value\value;
use cfg\value\value_base;
use cfg\value\value_list;
use cfg\verb\verb;
use cfg\verb\verb_list;
use cfg\view\view;
use cfg\view\view_list;
use cfg\word\word;
use cfg\word\word_list;
use cfg\word\triple;
use cfg\word\triple_list;
use controller\api_message;
use shared\const\triples;
use shared\const\words;
use shared\enum\messages as msg_id;
use shared\json_fields;
use shared\types\api_type_list;

class data_object
{

    /*
     *  object vars
     */

    private user $usr; // the person for whom the list has been created

    private word_list $wrd_lst;
    private verb_list $vrb_lst;
    private triple_list $trp_lst;
    private phrase_list $phr_lst;
    private bool $phr_lst_dirty;
    private source_list $src_lst;
    private ref_list $ref_lst;
    private value_list $val_lst;
    private formula_list $frm_lst;
    private term_list $trm_lst;
    private bool $trm_lst_dirty;
    private view_list $msk_lst;
    private component_list $cmp_lst;
    // for warning and errors while filling the data_object
    private user_message $usr_msg;


    /*
     * construct and map
     */

    /**
     * always set the user because always someone must have requested to create the list
     * e.g. an admin can have requested to import words for another user
     *
     * @param user $usr the user who requested the action
     */
    function __construct(user $usr)
    {
        $this->set_user($usr);
        $this->wrd_lst = new word_list($usr);
        $this->vrb_lst = new verb_list();
        $this->trp_lst = new triple_list($usr);
        $this->phr_lst = new phrase_list($usr);
        $this->phr_lst_dirty = false;
        $this->src_lst = new source_list($usr);
        $this->ref_lst = new ref_list($usr);
        $this->val_lst = new value_list($usr);
        $this->frm_lst = new formula_list($usr);
        $this->trm_lst = new term_list($usr);
        $this->trm_lst_dirty = false;
        $this->msk_lst = new view_list($usr);
        $this->cmp_lst = new component_list($usr);
        $this->usr_msg = new user_message();
    }


    /*
     * api
     */

    /**
     * create the api json message string of this data object that can be sent to the frontend
     * @param api_type_list|array $typ_lst configuration for the api message e.g. if phrases should be included
     * @param user|null $usr the user for whom the api message should be created which can differ from the session user
     * @return string with the api json string that should be sent to the backend
     */
    function api_json(api_type_list|array $typ_lst = [], user|null $usr = null): string
    {
        if (is_array($typ_lst)) {
            $typ_lst = new api_type_list($typ_lst);
        }

        $vars = $this->api_array($typ_lst);

        // add header if requested
        if ($typ_lst->use_header()) {
            global $db_con;
            $api_msg = new api_message();
            $msg = $api_msg->api_header_array($db_con, $this::class, $usr, $vars);
        } else {
            $msg = $vars;
        }

        return json_encode($msg);
    }

    /**
     * create an api json array for the backend based on this frontend object
     * @param api_type_list|array $typ_lst configuration for the api message e.g. if phrases should be included
     * @return array the json message array to send the updated data to the backend
     * an array is used (instead of a string) to enable combinations of api_array() calls
     */
    function api_array(api_type_list|array $typ_lst = []): array
    {
        $vars = [];
        $vars[json_fields::WORDS] = $this->wrd_lst->api_json_array($typ_lst);
        $vars[json_fields::VERBS] = $this->vrb_lst->api_json_array();
        $vars[json_fields::TRIPLES] = $this->trp_lst->api_json_array($typ_lst);
        $vars[json_fields::SOURCES] = $this->src_lst->api_json_array($typ_lst);
        $vars[json_fields::REFERENCES] = $this->ref_lst->api_json_array($typ_lst);
        $vars[json_fields::VALUES] = $this->val_lst->api_json_array($typ_lst);
        $vars[json_fields::FORMULAS] = $this->frm_lst->api_json_array($typ_lst);
        $vars[json_fields::VIEWS] = $this->msk_lst->api_json_array($typ_lst);
        $vars[json_fields::COMPONENTS] = $this->cmp_lst->api_json_array($typ_lst);
        return array_filter($vars, fn($value) => !is_null($value) && $value !== '');
    }


    /*
     * set and get
     */

    /**
     * set the user of the phrase list
     *
     * @param user $usr the person who wants to access the phrases
     * @return void
     */
    function set_user(user $usr): void
    {
        $this->usr = $usr;
    }

    /**
     * @return user the person who wants to see the phrases
     */
    function user(): user
    {
        return $this->usr;
    }

    /**
     * @return word_list with the words of this data object
     */
    function word_list(): word_list
    {
        return $this->wrd_lst;
    }

    /**
     * @return verb_list with the verbs of this data object
     */
    function verb_list(): verb_list
    {
        return $this->vrb_lst;
    }

    /**
     * @return triple_list with the triples of this data object
     */
    function triple_list(): triple_list
    {
        return $this->trp_lst;
    }

    /**
     * the phrase list merged by the name, not the database id
     * @return phrase_list with the words and triples of this data object
     */
    function phrase_list(): phrase_list
    {
        if ($this->phr_lst_dirty) {
            $phr_lst = $this->word_list()->phrase_lst_of_names();
            $phr_lst->merge_by_name($this->triple_list()->phrase_lst_of_names());
            $this->phr_lst = $phr_lst;
            $this->phr_lst_dirty = false;
        }
        return $this->phr_lst;
    }

    /**
     * the term list merged by the name, not the database id
     * @return term_list with the words, triples, verbs and formulas of this data object
     */
    function term_list(): term_list
    {
        if ($this->trm_lst_dirty) {
            $trm_lst = $this->phrase_list()->term_list();
            $trm_lst->merge_by_name($this->verb_list()->term_lst_of_names($this->user()));
            $trm_lst->merge_by_name($this->formula_list()->term_lst_of_names());
            $this->trm_lst = $trm_lst;
            $this->trm_lst_dirty = false;
        }
        return $this->trm_lst;
    }

    /**
     * @return source_list with the sources of this data object
     */
    function source_list(): source_list
    {
        return $this->src_lst;
    }

    /**
     * @return ref_list with the references of this data object
     */
    function reference_list(): ref_list
    {
        return $this->ref_lst;
    }

    /**
     * @return value_list with the values of this data object
     */
    function value_list(): value_list
    {
        return $this->val_lst;
    }

    /**
     * @return formula_list with the formulas of this data object
     */
    function formula_list(): formula_list
    {
        return $this->frm_lst;
    }

    /**
     * set the view_list of this data object
     * @param view_list $msk_lst
     */
    function set_view_list(view_list $msk_lst): void
    {
        $this->msk_lst = $msk_lst;
    }

    /**
     * @return view_list with the views of this data object
     */
    function view_list(): view_list
    {
        return $this->msk_lst;
    }

    /**
     * @return component_list with the components of this data object
     */
    function component_list(): component_list
    {
        return $this->cmp_lst;
    }

    /**
     * @return bool true if this context object contains a view list
     */
    function has_view_list(): bool
    {
        if ($this->msk_lst->count() > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * add a name word without db id to the list
     * @param word $wrd with the name set
     * @return void
     */
    function add_word(word $wrd): void
    {
        $this->phr_lst_dirty = true;
        $this->trm_lst_dirty = true;
        $this->wrd_lst->add_by_name($wrd);
    }

    /**
     * add a name verb without db id to the list
     * @param verb $vrb with the name set
     * @return void
     */
    function add_verb(verb $vrb): void
    {
        $this->trm_lst_dirty = true;
        $this->vrb_lst->add_by_name($vrb);
    }

    /**
     * add a triple with the names of the linked phrase names but without db id to the list
     * @param triple $trp with the name and word names set
     * @return void
     */
    function add_triple(triple $trp): void
    {
        $this->phr_lst_dirty = true;
        $this->trm_lst_dirty = true;
        $this->trp_lst->add_by_name($trp);
    }

    /**
     * add a source with the names but without db id to the list
     * @param source $src with the name and word names set
     * @return void
     */
    function add_source(source $src): void
    {
        $this->src_lst->add_by_name($src);
    }

    /**
     * add a reference with the names but without db id to the list
     * @param ref $ref with the phrase (or later term) name, reference type and the external key set
     * @return void
     */
    function add_reference(ref $ref): void
    {
        $this->ref_lst->add_by_name_type_and_key($ref);
    }

    /**
     * add a formula with word and triple names but without db id to the list
     * @param formula $frm with the name and word names set
     * @return void
     */
    function add_formula(formula $frm): void
    {
        $this->trm_lst_dirty = true;
        $this->frm_lst->add_by_name($frm);
    }

    /**
     * add a view with name but without db id to the list
     * @param view $frm with the name and parameters set
     * @return void
     */
    function add_view(view $frm): void
    {
        $this->msk_lst->add_by_name($frm);
    }

    /**
     * add a component with name but without db id to the list
     * @param component $frm with the name and parameters set
     * @return void
     */
    function add_component(component $frm): void
    {
        $this->cmp_lst->add_by_name($frm);
    }

    /**
     * add a value to the list
     * @param value_base $val a value that might not yet have a group id
     * @return void
     */
    function add_value(value_base $val): void
    {
        $this->val_lst->add_value_direct($val);
    }

    function add_message(string $msg): void
    {
        $this->usr_msg->add_message_text($msg);
    }

    /**
     * get a word or triple by the name from this cache object
     * @param string $name the name of the word or triple
     * @return phrase|null
     */
    function get_phrase_by_name(string $name): ?phrase
    {
        $wrd = $this->word_list()->get_by_name($name);
        $phr = $wrd?->phrase();
        if ($phr == null) {
            $trp = $this->triple_list()->get_by_name($name);
            $phr = $trp?->phrase();
        }
        return $phr;
    }

    function get_component_by_name(string $name): ?component
    {
        return $this->component_list()->get_by_name($name);
    }

    function get_value_by_names(array $names): ?value_base
    {
        return $this->value_list()->get_by_names($names);
    }

    function expected_word_import_time(): int
    {
        return $this->word_list()->count() * 10;
    }

    function expected_triple_import_time(): int
    {
        return $this->triple_list()->count() * 15;
    }

    function expected_value_import_time(): int
    {
        return $this->value_list()->count() * 10;
    }

    function expected_total_import_time(): int
    {
        return $this->expected_word_import_time()
            + $this->expected_triple_import_time()
            + $this->expected_value_import_time();
    }

    function count(): int
    {
        return $this->word_list()->count()
            + $this->triple_list()->count()
            + $this->value_list()->count();
    }

    /**
     * load the objects from the database and fill in missing db id
     * e.g. to validate the import
     * TODO Prio 2 add the missing lists and vars
     */
    function load(): user_message
    {
        $usr_msg = new user_message();
        $this->word_list()->load_by_names();
        $this->triple_list()->load_by_names();
        $usr_msg->add($this->value_list()->fill_phrase_ids_by_names($this->phrase_list()));
        //$this->value_list()->load_by_ids();
        return $usr_msg;
    }

    /**
     * add all words, triples and values to the database
     * or update the database
     * @param import $imp the import object that includes the start time of the import
     * @return user_message ok or the error message for the user with the suggested solution
     */
    function save(import $imp): user_message
    {
        global $cfg;

        $usr_msg = new user_message();

        // get the relevant config values
        $time_object = $cfg->get_by([triples::OBJECT_CREATION, words::PERCENT, triples::EXPECTED_TIME, words::IMPORT]);
        $trp_per_sec = $cfg->get_by([words::TRIPLES, words::STORE, triples::OBJECTS_PER_SECOND, triples::EXPECTED_TIME, words::IMPORT], 1);
        $src_per_sec = $cfg->get_by([words::SOURCES, words::STORE, triples::OBJECTS_PER_SECOND, triples::EXPECTED_TIME, words::IMPORT], 1);
        $ref_per_sec = $cfg->get_by([words::REFERENCES, words::STORE, triples::OBJECTS_PER_SECOND, triples::EXPECTED_TIME, words::IMPORT], 1);
        $val_per_sec = $cfg->get_by([words::VALUES, words::STORE, triples::OBJECTS_PER_SECOND, triples::EXPECTED_TIME, words::IMPORT], 1);
        $frm_per_sec = $cfg->get_by([words::FORMULAS, words::STORE, triples::OBJECTS_PER_SECOND, triples::EXPECTED_TIME, words::IMPORT], 1);
        $msk_per_sec = $cfg->get_by([words::VIEWS, words::STORE, triples::OBJECTS_PER_SECOND, triples::EXPECTED_TIME, words::IMPORT], 1);
        $cmp_per_sec = $cfg->get_by([words::COMPONENTS, words::STORE, triples::OBJECTS_PER_SECOND, triples::EXPECTED_TIME, words::IMPORT], 1);


        // save the data lists in order of the dependencies

        // import first the words
        $usr_msg->add($this->save_words($imp));

        // clone the list as cache to filter the phrases already fine
        // without removing the fine words or triples from the original lists
        $phr_lst = clone $this->word_list()->phrase_list();

        // import the triples
        $trp_lst = $this->triple_list();
        if (!$trp_lst->is_empty()) {

            // add the id of the words and triples just added to the triples
            // repeat this id assign until all triples have an id
            // or until it is clear that a triple is missing
            $trp_self_ref = true;
            $trp_add = true;
            while ($trp_self_ref and $trp_add) {
                $trp_self_ref = false;
                $trp_add = false;
                foreach ($this->triple_list()->lst() as $trp) {
                    // report triples where e.g. the linked phrases are missing
                    if (!$trp->db_ready()) {
                        $usr_msg->add_id_with_vars(msg_id::TRIPLE_NOT_VALID,
                            [msg_id::VAR_TRIPLE => $trp->dsp_id()]);
                    } else {
                        // set the id of the linked phrases or remember that they need to be added with the next round
                        $trp_self_ref = $this->check_triple_phrase($trp, $trp->from(), $phr_lst, $usr_msg, $trp_self_ref);
                        $trp_self_ref = $this->check_triple_phrase($trp, $trp->to(), $phr_lst, $usr_msg, $trp_self_ref);
                    }
                }

                // import the triples
                if ($usr_msg->is_ok()) {
                    // get the list of triples that should be imported
                    $trp_lst = $this->triple_list();
                    // clone the list to filter the phrases already fine without removing the fine triples from the original list
                    $cache = clone $phr_lst;
                    $cache->filter_valid();
                    // estimate the time for the import
                    $trp_est = $trp_lst->count() / $trp_per_sec;
                    $imp->step_start(msg_id::SAVE, triple::class, $trp_lst->count(), $trp_est);
                    $usr_msg->add($trp_lst->save($cache, $imp));
                    $imp->step_end($trp_lst->count(), $trp_per_sec);
                    if ($trp_lst->count() > 0) {
                        $trp_add = true;
                    }

                    // prepare adding the id of the triples just added to the triples
                    $phr_lst = $this->phrase_list();
                } else {
                    log_debug('triples not imported because ' . $usr_msg->all_message_text());
                }
            }

            // read the triple id of the last added phrases
            // get the list of triples that have been imported with the last add loop
            $trp_lst = $this->triple_list();
            // clone the list to filter the phrases already fine without removing the fine triples from the original list
            $cache = clone $phr_lst;
            $cache->filter_valid();
            // call the save function, but it is expected just to load the database id of the last added triples
            $usr_msg->add($trp_lst->save($cache, $imp));

            // report missing triples
            foreach ($this->triple_list()->lst() as $trp) {
                $phr = $trp->from();
                if (!$phr->is_valid()) {
                    $usr_msg->add_type_message($phr->name(), msg_id::PHRASE_ID_NOT_FOUND->value);
                }
                $phr = $trp->to();
                if (!$phr->is_valid()) {
                    $usr_msg->add_type_message($phr->name(), msg_id::PHRASE_ID_NOT_FOUND->value);
                }
            }

            // add the id of the triples just added to the values
            if ($usr_msg->is_ok()) {
                $phr_lst = $this->phrase_list();
                foreach ($this->value_list()->lst() as $val) {
                    foreach ($val->phrase_list()->lst() as $phr) {
                        if ($phr->id() == 0) {
                            if ($phr->name() == '') {
                                $usr_msg->add_warning_text('phrase id and name missing in ' . $phr->dsp_id());
                            } else {
                                $phr_reloaded = $phr_lst->get_by_name($phr->name());
                                $usr_msg->add($this->set_phrase_id($phr, $phr_reloaded));
                            }
                        }
                    }
                }
            }
        }

        // import the sources
        if ($usr_msg->is_ok()) {
            $src_lst = $this->source_list();
            $src_est = $src_lst->count() / $src_per_sec;
            $imp->step_start(msg_id::SAVE, source::class, $src_lst->count(), $src_est);
            $usr_msg->add($src_lst->save($imp, $src_per_sec));
            $imp->step_end($src_lst->count(), $src_per_sec);
        } else {
            log_debug('sources not imported because ' . $usr_msg->all_message_text());
        }

        // add the id of the phrases just added to the references
        if ($usr_msg->is_ok()) {
            $phr_lst = $this->phrase_list();
            foreach ($this->reference_list()->lst() as $ref) {
                $phr = $ref->phrase();
                if ($phr->id() == 0) {
                    if ($phr->name() == '') {
                        $usr_msg->add_warning_text('phrase id and name missing in ' . $phr->dsp_id());
                    } else {
                        $phr_reloaded = $phr_lst->get_by_name($phr->name());
                        $usr_msg->add($this->set_phrase_id($phr, $phr_reloaded));
                    }
                }
            }
        }

        // import the references
        if ($usr_msg->is_ok()) {
            $ref_lst = $this->reference_list();
            $ref_est = $ref_lst->count() / $ref_per_sec;
            $imp->step_start(msg_id::SAVE, ref::class, $ref_lst->count(), $ref_est);
            $usr_msg->add($ref_lst->save($imp, $ref_per_sec));
            $imp->step_end($ref_lst->count(), $ref_per_sec);
        } else {
            log_debug('sources not imported because ' . $usr_msg->all_message_text());
        }

        // import the values
        // TODO Prio 1 review and use predefined functions
        if ($usr_msg->is_ok()) {
            $val_lst = $this->value_list();
            $val_est = $val_lst->count() / $val_per_sec;
            $imp->step_start(msg_id::SAVE, value::class, $val_lst->count(), $val_est);
            $usr_msg->add($val_lst->save($imp, $val_per_sec));
            $imp->step_end($val_lst->count(), $val_per_sec);
        } else {
            log_debug('values not imported because ' . $usr_msg->all_message_text());
        }

        // clone the term list as cache to filter the terms already fine
        // without removing the fine words, triples, verbs and formulas from the original lists
        $trm_lst = clone $phr_lst->term_list();
        // TODO add the verbs

        // import the formulas
        $frm_lst = $this->formula_list();
        if (!$frm_lst->is_empty()) {

            // add the id of the formulas just added to the terms
            // repeat this id assign until all formulas have an id
            // or until it is clear that a terms is missing
            $frm_self_ref = true;
            $frm_add = true;
            while ($frm_self_ref and $frm_add) {
                $frm_self_ref = false;
                $frm_add = false;
                foreach ($this->formula_list()->lst() as $frm) {
                    if (!$frm->db_ready()) {
                        $usr_msg->add_id_with_vars(msg_id::FORMULA_NOT_VALID,
                            [msg_id::VAR_FORMULA => $frm->dsp_id()]);
                    } else {
                        $exp = $frm->expression($trm_lst);
                        $frm_trm_lst = $exp->terms($trm_lst);
                        foreach ($frm_trm_lst->lst() as $trm) {
                            $frm_self_ref = $this->check_formula_term($frm, $trm, $frm_trm_lst, $usr_msg, $trp_self_ref);
                        }
                    }
                }

                // save the formulas that are ready which means that does not use a formula that is not yet saved in the database
                if ($usr_msg->is_ok()) {
                    // get the list of formulas that should be imported
                    $frm_lst = $this->formula_list();
                    // clone the list to filter the phrases already fine without removing the fine triples from the original list
                    $cache = clone $trm_lst;
                    $cache->filter_valid();
                    // estimate the time for the import
                    $frm_est = $frm_lst->count() / $frm_per_sec;
                    $imp->step_start(msg_id::SAVE, formula::class, $frm_lst->count(), $frm_est);
                    $usr_msg->add($frm_lst->save($cache, $imp));
                    $imp->step_end($frm_lst->count(), $frm_per_sec);
                    if ($frm_lst->count() > 0) {
                        $frm_add = true;
                    }

                    // prepare adding the id of the triples just added to the triples
                    $trm_lst = $this->term_list();
                } else {
                    log_debug('formulas not imported because ' . $usr_msg->all_message_text());
                }
            }

            // report missing formulas
            foreach ($this->formula_list()->lst() as $frm) {
                $exp = $frm->expression($trm_lst);
                $frm_trm_lst = $exp->terms($trm_lst);
                foreach ($frm_trm_lst->lst() as $trm) {
                    $frm_trm = $trm_lst->get_by_name($trm->name());
                    if ($frm_trm == null) {
                        $usr_msg->add_type_message($trm->name(), msg_id::TERM_ID_NOT_FOUND->value);
                    }
                }
            }
        }

        // TODO Prio 1 review and use predefined functions
        if ($usr_msg->is_ok()) {
            $cmp_lst = $this->component_list();
            $cmp_est = $cmp_lst->count() / $cmp_per_sec;
            $imp->step_start(msg_id::SAVE, component::class, $cmp_lst->count(), $cmp_est);
            $usr_msg->add($cmp_lst->save($imp, $cmp_per_sec));
            $imp->step_end($cmp_lst->count(), $cmp_per_sec);

            // add the id of the components just added to the views
            if ($usr_msg->is_ok()) {
                $cmp_lst = $this->component_list();
                foreach ($this->view_list()->lst() as $msk) {
                    if ($msk->has_components()) {
                        foreach ($msk->component_link_list()->lst() as $lnk) {
                            $cmp = $lnk->component();
                            if ($cmp->id() == 0) {
                                if ($cmp->name() == '') {
                                    $usr_msg->add_warning_text('component id and name missing in ' . $cmp->dsp_id());
                                } else {
                                    $cmp_reloaded = $cmp_lst->get_by_name($cmp->name());
                                    if ($cmp_reloaded == null) {
                                        $usr_msg->add_warning_text('component id and name missing in ' . $cmp->dsp_id());
                                    } else {
                                        $cmp->set_id($cmp_reloaded->id());
                                        $lnk->set_component_id($cmp_reloaded->id());
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } else {
            log_debug('components not imported because ' . $usr_msg->all_message_text());
        }

        // TODO Prio 1 review and use predefined functions
        if ($usr_msg->is_ok()) {
            $msk_lst = $this->view_list();
            $msk_est = $msk_lst->count() / $msk_per_sec;
            $imp->step_start(msg_id::SAVE, source::class, $msk_lst->count(), $msk_est);
            $usr_msg->add($msk_lst->save($imp, $msk_per_sec));
            $imp->step_end($msk_lst->count(), $msk_per_sec);
        } else {
            log_debug('views not imported because ' . $usr_msg->all_message_text());
        }

        return $usr_msg;
    }

    /**
     * add or update all words to the database
     * @param import $imp the import object that includes the start time of the import
     * @return user_message ok or the error message for the user with the suggested solution
     */
    private function save_words(import $imp): user_message
    {
        global $cfg;
        $usr_msg = new user_message();

        $wrd_per_sec = $cfg->get_by([words::WORDS, words::STORE, triples::OBJECTS_PER_SECOND, triples::EXPECTED_TIME, words::IMPORT], 1);

        $wrd_lst = $this->word_list();
        if (!$wrd_lst->is_empty()) {
            $wrd_est = $wrd_lst->count() / $wrd_per_sec;
            $imp->step_start(msg_id::SAVE, word::class, $wrd_lst->count(), $wrd_est);
            $usr_msg->add($wrd_lst->save($imp));
            $imp->step_end($wrd_lst->count(), $wrd_per_sec);
        }
        return $usr_msg;
    }

    /**
     * TODO add the missing lists and vars of the dto object
     * reports the difference to the given data object as human-readable messages
     * @param data_object $dto which might have some differences
     * @return user_message all differences as a human-readable text
     */
    function diff_msg(data_object $dto): user_message
    {
        $usr_msg = new user_message();
        $usr_msg->add($this->word_list()->diff_msg($dto->word_list()));
        $usr_msg->add($this->triple_list()->diff_msg($dto->triple_list()));
        $usr_msg->add($this->value_list()->diff_msg($dto->value_list()));
        return $usr_msg;
    }

    /**
     * check if the phrase related to a triple is fine
     * and if not indicate a self reference by returning true
     *
     * @param triple $trp the triple that should be checked
     * @param phrase $phr either the from or to phrase of the triple
     * @param phrase_list $phr_lst the cache of all words and triples that are fine until now
     * @param user_message $usr_msg all user messages of the import up to this check
     * @param bool $trp_self_ref the status to the self reference before this check
     * @return bool
     */
    private function check_triple_phrase(
        triple       $trp,
        phrase       $phr,
        phrase_list  $phr_lst,
        user_message $usr_msg,
        bool         $trp_self_ref
    ): bool
    {
        if ($phr->id() == 0) {
            if ($phr->name() == '') {
                $usr_msg->add_type_message($trp->dsp_id(), msg_id::PHRASE_MISSING_FROM->value);
            } else {
                $phr_reloaded = $phr_lst->get_by_name($phr->name());
                if ($phr_reloaded == null) {
                    $trp_self_ref = true;
                } else {
                    $usr_msg->add($this->set_phrase_id($phr, $phr_reloaded));
                }
            }
        }
        return $trp_self_ref;
    }

    /**
     * check if the term related to a formula is fine
     * and if not indicate a self reference by returning true
     *
     * @param formula $frm the formula that should be checked
     * @param term $trm either the from or to phrase of the triple
     * @param term_list $trm_lst the cache of all terms that are fine until now
     * @param user_message $usr_msg all user messages of the import up to this check
     * @param bool $trp_self_ref the status to the self reference before this check
     * @return bool
     */
    private function check_formula_term(
        formula      $frm,
        term         $trm,
        term_list    $trm_lst,
        user_message $usr_msg,
        bool         $trp_self_ref
    ): bool
    {
        if ($trm->id() == 0) {
            if ($trm->name() == '') {
                $usr_msg->add_type_message($frm->dsp_id(), msg_id::PHRASE_MISSING_FROM->value);
            } else {
                $trm_reloaded = $trm_lst->get_by_name($trm->name());
                if ($trm_reloaded == null) {
                    $trp_self_ref = true;
                } else {
                    $usr_msg->add($this->set_term_id($trm, $trm_reloaded));
                }
            }
        }
        return $trp_self_ref;
    }

    /**
     * set the missing id in the phrase based on the given database phrase
     * @param phrase $phr which might have a missing id
     * @param phrase|null $db_phr which might have the missing id
     * @return user_message warning if something has been missing
     */
    private function set_phrase_id(phrase $phr, phrase|null $db_phr): user_message
    {
        $usr_msg = new user_message();
        if ($phr->id() == 0) {
            if ($db_phr != null) {
                $phr->set_id($db_phr->id());
            }
        }
        return $usr_msg;
    }

    /**
     * set the missing id in the term based on the given database term
     * @param term $trm which might have a missing id
     * @param term|null $db_trm which might have the missing id
     * @return user_message warning if something has been missing
     */
    private function set_term_id(term $trm, term|null $db_trm): user_message
    {
        $usr_msg = new user_message();
        if ($trm->id() == 0) {
            if ($db_trm != null) {
                $trm->set_id($db_trm->id());
            }
        }
        return $usr_msg;
    }

}
