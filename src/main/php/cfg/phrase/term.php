<?php

/*

    model/phrase/term.php - either a word, verb, triple or formula
    ---------------------

    TODO: load formula word
        check triple

    mainly to check the term consistency of all objects
    a term must be unique for word, verb and triple e.g. "Company" is a word "is a" is a verb and "Kanton Zurich" is a triple
    all terms are the same for each user
    if a user changes a term and the term has been used already
    a new term is created and the deletion of the existing term is requested
    if all user have confirmed the deletion, the term is finally deleted
    each user can have its own language translation which must be unique only for one user
    so one user may use "Zurich" in US English for "Kanton Zurich"
    and another user may use "Zurich" in US English for "Zurich AG"


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

namespace cfg;

include_once MODEL_HELPER_PATH . 'combine_named.php';
include_once API_PHRASE_PATH . 'term.php';
include_once API_WORD_PATH . 'word.php';
include_once API_WORD_PATH . 'triple.php';
include_once API_VERB_PATH . 'verb.php';
include_once API_FORMULA_PATH . 'formula.php';
include_once MODEL_SANDBOX_PATH . 'sandbox.php';
include_once MODEL_WORD_PATH . 'word.php';
include_once MODEL_WORD_PATH . 'triple.php';
include_once MODEL_VERB_PATH . 'verb.php';
include_once MODEL_FORMULA_PATH . 'formula.php';
include_once MODEL_PHRASE_PATH . 'phrase.php';
include_once WEB_PHRASE_PATH . 'term.php';

use api\term_api;
use api\word_api;
use html\html_base;
use html\phrase\term as term_dsp;
use html\word\word as word_dsp;
use user_dsp_old;

class term extends combine_named
{

    /*
     * database link
     */

    // field names of the database view for terms
    // the database view is used e.g. for a fast check of a new term name
    const FLD_ID = 'term_id';
    const FLD_NAME = 'term_name';
    const FLD_USAGE = 'usage'; // included in the database view to be able to show the user the most relevant terms
    const FLD_TYPE = 'term_type_id'; // the phrase type for word or triple or the formula type for formulas; not used for verbs

    // the common phrase database field names excluding the id and excluding the user specific fields
    const FLD_NAMES = array(
        self::FLD_TYPE
    );
    // list of the user specific database field names
    // some fields like the formula expression are only used for one term class e.g. formula
    // this is done because the total number of terms is expected to be less than 10 million
    // which database should be able to handle and only a few hundred are expected to be sent to via api at once
    const FLD_NAMES_USR = array(
        sql_db::FLD_DESCRIPTION,
        formula::FLD_FORMULA_TEXT,
        formula::FLD_FORMULA_USER_TEXT
    );
    // list of the user specific numeric database field names
    const FLD_NAMES_NUM_USR = array(
        self::FLD_USAGE,
        sandbox::FLD_EXCLUDED,
        sandbox::FLD_SHARE,
        sandbox::FLD_PROTECT
    );


    /*
     * construct and map
     */

    /**
     * always set the user because a term is always user specific
     * @param user|word|triple|formula|verb|null $obj the user who requested to see this term
     */
    function __construct(user|word|triple|formula|verb|null $obj)
    {
        // TODO remove user_dsp_old
        if ($obj::class == user::class or $obj::class == user_dsp_old::class) {
            // create a dummy word object to remember the user
            parent::__construct(new word($obj));
        } else {
            parent::__construct($obj);
        }
    }

    function reset(): void
    {
        $this->set_id(0);
    }

    /**
     * TODO deprecate and replace with row_mapper_obj
     * map the main field from the term view to a term object
     * @return bool true if at least one term has been loaded
     */
    function row_mapper(array $db_row): bool
    {
        $result = false;
        $this->set_id(0);
        if ($db_row != null) {
            if ($db_row[self::FLD_ID] != 0) {
                $this->set_obj_from_id($db_row[self::FLD_ID]);
                $this->set_name($db_row[self::FLD_NAME]);
                $this->set_usage($db_row[self::FLD_USAGE]);
                $result = true;
            }
        }
        return $result;
    }

    /**
     * map a complete underlying object to a term
     * @return bool true if at least one term has been loaded
     */
    function row_mapper_obj(array $db_row, string $class, string $id_fld, string $name_fld, string $type_fld = '', bool $load_std = false, bool $allow_usr_protect = true): bool
    {
        $result = false;
        if ($class == word::class) {
            $result = $this->get_word()->row_mapper_sandbox($db_row, $load_std, $allow_usr_protect, $id_fld, $name_fld, $type_fld);
        } elseif ($class == triple::class) {
            $result = $this->get_triple()->row_mapper_sandbox($db_row, $load_std, $allow_usr_protect, $id_fld, $name_fld, $type_fld);
        } elseif ($class == formula::class) {
            $result = $this->get_formula()->row_mapper_sandbox($db_row, $load_std, $allow_usr_protect, $id_fld, $name_fld, $type_fld);
        } elseif ($class == verb::class) {
            $result = $this->get_verb()->row_mapper_verb($db_row, $id_fld, $name_fld);
        } else {
            log_warning('Term ' . $this->dsp_id() . ' is of unknown type');
        }
        // overwrite the term id in the object with the real object id
        $this->set_id($db_row[$id_fld]);
        return $result;
    }

    /*
     * set and get
     */

    /**
     * create the expected object based on the class name
     * must have the same logic as the database view and the frontend
     * @param string $class the term id as received e.g. from the database view
     * @return void
     */
    function set_obj_from_class(string $class): void
    {
        if ($class == triple::class) {
            $this->obj = new triple($this->user());
        } elseif ($class == formula::class) {
            $this->obj = new formula($this->user());
        } elseif ($class == verb::class) {
            $this->obj = new verb();
        } else {
            $this->obj = new word($this->user());
        }
    }

    /**
     * create the expected object based on the id
     * must have the same logic as the database view and the frontend
     * TODO dismiss?
     *
     * @param int $id the term id as received e.g. from the database view
     * @return void
     */
    function set_obj_from_id(int $id): void
    {
        if ($id > 0) {
            if ($id % 2 == 0) {
                $this->obj = new formula($this->user());
            } else {
                $this->obj = new word($this->user());
            }
        } else {
            if ($id % 2 == 0) {
                $this->obj = new verb();
            } else {
                $this->obj = new triple($this->user());
            }
        }
        $this->set_id($id);
    }

    /**
     * set the object id based on the given term id
     * must have the same logic as the api and the frontend
     *
     * @param int $id the term (not the object!) id
     * @return void
     */
    function set_id(int $id): void
    {
        if ($id % 2 == 0) {
            $this->set_obj_id(abs($id) / 2);
        } else {
            $this->set_obj_id((abs($id) + 1) / 2);
        }
    }

    /**
     * set the term id based id the word, triple, verb or formula id
     * must have the same logic as the database view and the frontend
     * TODO deprecate?
     *
     * @param int $id the object id that is converted to the term id
     * @param string $class the class of the term object
     * @return void
     */
    function set_id_from_obj(int $id, string $class): void
    {
        if ($id != null) {
            if ($class == word::class) {
                if ($this->obj == null) {
                    $this->obj = new word($this->user());
                }
                $this->id = ($id * 2) - 1;
            } elseif ($class == triple::class) {
                if ($this->obj == null) {
                    $this->obj = new triple($this->user());
                }
                $this->id = ($id * -2) + 1;
            } elseif ($class == formula::class) {
                if ($this->obj == null) {
                    $this->obj = new formula($this->user());
                }
                $this->id = ($id * 2);
            } elseif ($class == verb::class) {
                if ($this->obj == null) {
                    $this->obj = new verb();
                }
                $this->id = ($id * -2);
            }
            $this->obj->set_id($id);
        }
    }

    /**
     * create the word, triple, formula or verb object based on the given class
     *
     * @param string $class the calling class name
     * @return void
     */
    private function set_obj_by_class(string $class): void
    {
        if ($class == word::class) {
            $this->obj = new word($this->user());
        } elseif ($class == triple::class) {
            $this->obj = new triple($this->user());
        } elseif ($class == formula::class) {
            $this->obj = new formula($this->user());
        } elseif ($class == verb::class) {
            $this->obj = new verb();
        } else {
            log_err('Unexpected class ' . $class . ' when creating term ' . $this->dsp_id());
        }
    }

    /**
     * set the name of the term object, which is also the name of the term
     * because of this object name retrieval set and get of the name is needed for all linked objects
     *
     * @param string $name the name of the term set in the related object
     * @param string $class the class of the term object can be set to force the creation of the related object
     * @return void
     */
    function set_name(string $name, string $class = ''): void
    {
        if ($class != '' and $this->obj == null) {
            $this->set_obj_by_class($class);
        }
        $this->obj->set_name($name);
    }

    /**
     * set the user of the term object, which is also the user of the term
     * because of this object retrieval set and get of the user is needed for all linked objects
     *
     * @param user $usr the person who wants to add a term (word, verb, triple or formula)
     * @param string $class the class of the term object can be set to force the creation of the related object
     * @return void
     */
    function set_user(user $usr, string $class = ''): void
    {
        if ($class != '' and $this->obj == null) {
            $this->set_obj_by_class($class);
        }
        $this->obj->set_user($usr);
    }

    /**
     * set the value to rank the terms by usage
     *
     * @param int|null $usage a higher value moves the term to the top of the selection list
     * @return void
     */
    function set_usage(?int $usage): void
    {
        if ($usage == null) {
            $this->obj->set_usage(0);
        } else {
            $this->obj->set_usage($usage);
        }
    }

    /**
     * @return int the id of the term witch is  (corresponding to id_obj())
     * must have the same logic as the database view and the frontend
     *
     * e.g.  1 for a word with id 1
     *  and  3 for a word with id 2
     *  and -1 for a triple with id 1
     *  and -3 for a triple with id 2
     *  and  2 for a formula with id 1
     *  and  4 for a formula with id 2
     *  and -2 for a verb with id 1
     *  and -4 for a verb with id 2
     * , -1 for a triple, 2 for a formula and -2 for a verb
     */
    function id(): int
    {
        if ($this->is_word()) {
            return ($this->obj_id() * 2) - 1;
        } elseif ($this->is_triple()) {
            return ($this->obj_id() * -2) + 1;
        } elseif ($this->is_formula()) {
            return ($this->obj_id() * 2);
        } elseif ($this->is_verb()) {
            return ($this->obj_id() * -2);
        } else {
            return 0;
        }
    }

    /**
     * get the object id based on the term id
     *
     * @return int|null the id of the containing object witch is (corresponding to id())
     * e.g. for a word    with id 1 simply 1 is returned
     *  and for a triple  with id 1   also 1 is returned
     *  and for a formula with id 1   also 1 is returned
     *  and for a verb    with id 1   also 1 is returned
     */
    function id_obj(): ?int
    {
        return $this->obj?->id();
    }

    function name(): string
    {
        $result = '';
        if (isset($this->obj)) {
            $result = $this->obj->name();
        }
        return $result;
    }

    /**
     * @return user|null the person who wants to see a term (word, verb, triple or formula)
     *                   in case of a verb it can be null
     */
    function user(): ?user
    {
        $result = new user();
        if (isset($this->obj)) {
            $result = $this->obj->user();
        }
        return $result;
    }

    function type(): string
    {
        $result = '';
        if (isset($this->obj)) {
            $result = $this->obj::class;
        }
        return $result;
    }

    function usage(): int
    {
        return $this->obj->usage();
    }


    /*
     * cast
     */

    /**
     * @return term_api the term frontend api object
     */
    function api_obj(): term_api
    {
        if ($this->is_word()) {
            return $this->get_word()->api_obj()->term();
        } elseif ($this->is_triple()) {
            return $this->get_triple()->api_obj()->term();
        } elseif ($this->is_formula()) {
            return $this->get_formula()->api_obj()->term();
        } elseif ($this->is_verb()) {
            return $this->get_verb()->api_obj()->term();
        } else {
            log_warning('Term ' . $this->dsp_id() . ' is of unknown type');
            return (new term_api(new word_api()));
        }
    }

    /**
     * @returns string the api json message for the object as a string
     */
    function api_json(): string
    {
        return $this->api_obj()->get_json();
    }

    /**
     * @return phrase the word or triple cast as a phrase
     */
    public
    function phrase(): phrase
    {
        $phr = new phrase($this->user());
        if ($this->is_word()) {
            $phr->set_id_from_obj($this->id_obj(), word::class);
            $phr->obj = $this->obj;
        }
        if ($this->is_triple()) {
            $phr->set_id_from_obj($this->id_obj(), triple::class);
            $phr->obj = $this->obj;
        }
        return $phr;
    }

    /*
     * load functions
     */

    /**
     * create the common part of an SQL statement to retrieve a term from the database
     * uses the term view which includes only the most relevant fields of words, triples, formulas and verbs
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param string $query_name the name of the query use to prepare and call the query
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    private
    function load_sql(sql_db $db_con, string $query_name): sql_par
    {
        $qp = new sql_par(self::class);
        $qp->name .= $query_name;

        $db_con->set_type(sql_db::VT_TERM);
        $db_con->set_name($qp->name);

        $db_con->set_usr_fields(self::FLD_NAMES_USR);
        $db_con->set_usr_num_fields(self::FLD_NAMES_NUM_USR);

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a term by term id (not the object id) from the database
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param int $id the id of the term as defined in the database term view
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_id(sql_db $db_con, int $id): sql_par
    {
        $qp = $this->load_sql($db_con, 'id');
        $db_con->add_par_int($id);
        $qp->sql = $db_con->select_by_field(term::FLD_ID);
        $qp->par = $db_con->get_par();

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a term by name from the database
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param string $name the name of the term and the related word, triple, formula or verb
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_name(sql_db $db_con, string $name): sql_par
    {
        $qp = $this->load_sql($db_con, 'name');
        $db_con->add_par_txt($name);
        $qp->sql = $db_con->select_by_field(term::FLD_NAME);
        $qp->par = $db_con->get_par();

        return $qp;
    }

    /**
     * load a term from the database view
     * @param sql_par $qp the query parameters created by the calling function
     * @return int the id of the object found and zero if nothing is found
     */
    private function load(sql_par $qp): int
    {
        global $db_con;

        $db_row = $db_con->get1($qp);
        $this->row_mapper($db_row);
        return $this->id();
    }

    /**
     * load the main term parameters by id from the database term view
     * @param int $id the id of the term as defined in the database term view
     * @param string $class not used for this term object just to be compatible with the db base object
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_id(int $id, string $class = self::class): int
    {
        global $db_con;

        log_debug($id);
        $qp = $this->load_sql_by_id($db_con, $id);
        return $this->load($qp);
    }

    /**
     * test if the name is used already via view table and just load the main parameters
     * @param string $name the name of the term and the related word, triple, formula or verb
     * @param string $class not used for this term object just to be compatible with the db base object
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_name(string $name, string $class = self::class): int
    {
        global $db_con;

        log_debug($name);
        $qp = $this->load_sql_by_name($db_con, $name);
        return $this->load($qp);
    }

    /**
     * load the term object by the word or triple id (not the phrase id)
     * @param int $id the id of the term object e.g. for a triple "-1"
     * @param string $class not used for this term object just to be compatible with the db base object
     * @param bool $including_triples to include the words or triple of a triple (not recursive)
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_obj_id(int $id, string $class, bool $including_triples = true): int
    {
        log_debug($this->name());
        $result = 0;

        if ($class == word::class) {
            if ($this->load_word_by_id($id)) {
                $result = $this->obj_id();
            }
        } elseif ($class == triple::class) {
            if ($this->load_triple_by_id($id, $including_triples)) {
                $result = $this->obj_id();
            }
        } elseif ($class == formula::class) {
            if ($this->load_formula_by_id($id)) {
                $result = $this->obj_id();
            }
        } elseif ($class == verb::class) {
            if ($this->load_verb_by_id($id)) {
                $result = $this->obj_id();
            }
        } else {
            log_err('Unexpected class ' . $class . ' when creating term ' . $this->dsp_id());
        }

        log_debug('term->load loaded id "' . $this->id() . '" for ' . $this->name());

        return $result;
    }

    /**
     * simply load a word
     * (separate functions for loading  for a better overview)
     */
    private
    function load_word_by_id(int $id): bool
    {
        global $phrase_types;

        $result = false;
        $wrd = new word($this->user());
        if ($wrd->load_by_id($id, word::class)) {
            log_debug('type is "' . $wrd->type_id . '" and the formula type is ' . $phrase_types->id(phrase_type::FORMULA_LINK));
            if ($wrd->type_id == $phrase_types->id(phrase_type::FORMULA_LINK)) {
                $result = $this->load_formula_by_id($id);
            } else {
                $this->set_id_from_obj($wrd->id(), word::class);
                $this->obj = $wrd;
                $result = true;
            }
        }
        return $result;
    }

    /**
     * simply load a triple
     */
    private
    function load_triple_by_id(int $id, bool $including_triples): bool
    {
        $result = false;
        if ($including_triples) {
            $trp = new triple($this->user());
            if ($trp->load_by_id($id, triple::class)) {
                $this->set_id_from_obj($trp->id(), triple::class);
                $this->obj = $trp;
                $result = true;
            }
        }
        return $result;
    }

    /**
     * simply load a formula
     * without fixing any missing related word issues
     */
    private
    function load_formula_by_id(int $id): bool
    {
        $result = false;
        $frm = new formula($this->user());
        if ($frm->load_by_id($id, formula::class)) {
            $this->set_id_from_obj($frm->id(), formula::class);
            $this->obj = $frm;
            $result = true;
        }
        return $result;
    }

    /**
     * simply load a verb
     */
    private
    function load_verb_by_id(int $id): bool
    {
        $result = false;
        $vrb = new verb;
        $vrb->set_name($this->name());
        $vrb->set_user($this->user());
        if ($vrb->load_by_id($id)) {
            $this->set_id_from_obj($vrb->id(), verb::class);
            $this->obj = $vrb;
            $result = true;
        }
        return $result;
    }

    /**
     * test if the name is used already and load the object
     * @param string $name the name of the term (and word, triple, formula or verb) to load
     * @param bool $including_triples to include the words or triple of a triple (not recursive)
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_obj_name(string $name, bool $including_triples = true): int
    {
        log_debug($this->name());
        $result = 0;

        if ($this->load_word_by_name($name)) {
            $result = $this->id();
        } elseif ($this->load_triple_by_name($name, $including_triples)) {
            $result = $this->id();
        } elseif ($this->load_formula_by_name($name)) {
            $result = $this->id();
        } elseif ($this->load_verb_by_name($name)) {
            $result = $this->id();
        }
        log_debug('term->load loaded id "' . $this->id() . '" for ' . $this->name());

        return $result;
    }

    /**
     * simply load a word by name
     * (separate functions for loading  for a better overview)
     */
    private
    function load_word_by_name(string $name): bool
    {
        global $phrase_types;

        $result = false;
        $wrd = new word($this->user());
        if ($wrd->load_by_name($name, word::class)) {
            log_debug('type is "' . $wrd->type_id . '" and the formula type is ' . $phrase_types->id(phrase_type::FORMULA_LINK));
            if ($wrd->type_id == $phrase_types->id(phrase_type::FORMULA_LINK)) {
                $result = $this->load_formula_by_name($name);
            } else {
                $this->set_id_from_obj($wrd->id(), word::class);
                $this->obj = $wrd;
                $result = true;
            }
        }
        return $result;
    }

    /**
     * simply load a triple by name
     */
    private
    function load_triple_by_name(string $name, bool $including_triples): bool
    {
        $result = false;
        if ($including_triples) {
            $trp = new triple($this->user());
            if ($trp->load_by_name($name, triple::class)) {
                $this->set_id_from_obj($trp->id(), triple::class);
                $this->obj = $trp;
                $result = true;
            }
        }
        return $result;
    }

    /**
     * simply load a formula by name
     * without fixing any missing related word issues
     */
    private
    function load_formula_by_name(string $name): bool
    {
        $result = false;
        $frm = new formula($this->user());
        if ($frm->load_by_name($name, formula::class)) {
            $this->set_id_from_obj($frm->id(), formula::class);
            $this->obj = $frm;
            $result = true;
        }
        return $result;
    }

    /**
     * simply load a verb by name
     */
    private
    function load_verb_by_name(string $name): bool
    {
        $result = false;
        $vrb = new verb;
        $vrb->set_name($this->name());
        $vrb->set_user($this->user());
        if ($vrb->load_by_name($name)) {
            $this->set_id_from_obj($vrb->id(), verb::class);
            $this->obj = $vrb;
            $result = true;
        }
        return $result;
    }

    /*
     * classification
     */

    /**
     * @return bool true if this term is a word or supposed to be a word
     */
    public
    function is_word(): bool
    {
        $result = false;
        if (isset($this->obj)) {
            if (get_class($this->obj) == word::class or get_class($this->obj) == word_dsp::class) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * @return bool true if this term is a triple or supposed to be a triple
     */
    public
    function is_triple(): bool
    {
        $result = false;
        if (isset($this->obj)) {
            if (get_class($this->obj) == triple::class) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * @return bool true if this term is a formula or supposed to be a triple
     */
    public
    function is_formula(): bool
    {
        $result = false;
        if (isset($this->obj)) {
            if (get_class($this->obj) == formula::class) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * @return bool true if this term is a verb or supposed to be a triple
     */
    public
    function is_verb(): bool
    {
        $result = false;
        if (isset($this->obj)) {
            if (get_class($this->obj) == verb::class) {
                $result = true;
            }
        }
        return $result;
    }

    /*
     * conversion
     */

    public
    function get_word(): word
    {
        $wrd = new word($this->user());
        if (get_class($this->obj) == word::class) {
            $wrd = $this->obj;
        }
        return $wrd;
    }

    public
    function get_triple(): triple
    {
        $lnk = new triple($this->user());
        if (get_class($this->obj) == triple::class) {
            $lnk = $this->obj;
        }
        return $lnk;
    }

    public
    function get_formula(): formula
    {
        $frm = new formula($this->user());
        if (get_class($this->obj) == formula::class) {
            $frm = $this->obj;
        }
        return $frm;
    }

    public
    function get_verb(): verb
    {
        $vrb = new verb();
        if (get_class($this->obj) == verb::class) {
            $vrb = $this->obj;
        }
        return $vrb;
    }

    /*
    * user interface language specific functions
    */

    /**
     * create a message text that the name is already used
     */
    function id_used_msg(db_object $obj_to_add): string
    {
        $lib = new library();
        $html = new html_base();
        $result = "";

        if ($this->id() != 0) {
            $class = $lib->class_to_name($this->type());
            $result = $html->dsp_err(
                'A ' . $class . ' with the name "' . $this->name() . '" already exists. '
                . 'Please use another ' . $lib->class_to_name($obj_to_add::class) . ' name.');
        }

        return $result;
    }

    /*
     * information functions
     */

    /**
     * display the unique id fields
     */
    function dsp_id(): string
    {
        $result = '';

        if ($this->name() <> '') {
            $result .= '"' . $this->name() . '"';
            if ($this->id() > 0) {
                $result .= ' (' . $this->id() . ')';
            }
        } else {
            $result .= $this->id();
        }
        if ($this->user()->id() > 0) {
            $result .= ' for user ' . $this->user()->id() . ' (' . $this->user()->name . ')';
        }
        return $result;
    }

}