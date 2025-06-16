<?php

/*

    model/phrase/term.php - either a word, verb, triple or formula
    -------------------

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

    Copyright (c) 1995-2024 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace cfg\phrase;

include_once MODEL_HELPER_PATH . 'combine_named.php';
include_once DB_PATH . 'sql.php';
include_once DB_PATH . 'sql_creator.php';
include_once DB_PATH . 'sql_db.php';
include_once DB_PATH . 'sql_par.php';
include_once DB_PATH . 'sql_type.php';
include_once DB_PATH . 'sql_field_type.php';
include_once MODEL_HELPER_PATH . 'db_object_seq_id.php';
include_once MODEL_FORMULA_PATH . 'formula.php';
include_once MODEL_SANDBOX_PATH . 'sandbox.php';
include_once MODEL_SANDBOX_PATH . 'sandbox_named.php';
include_once MODEL_VERB_PATH . 'verb.php';
include_once MODEL_USER_PATH . 'user.php';
include_once MODEL_USER_PATH . 'user_message.php';
include_once MODEL_WORD_PATH . 'word.php';
include_once MODEL_WORD_PATH . 'word_db.php';
include_once MODEL_WORD_PATH . 'triple.php';
include_once MODEL_PHRASE_PATH . 'phrase.php';
include_once SHARED_ENUM_PATH . 'messages.php';
include_once SHARED_TYPES_PATH . 'protection_type.php';
include_once SHARED_TYPES_PATH . 'share_type.php';
include_once SHARED_TYPES_PATH . 'phrase_type.php';
include_once SHARED_PATH . 'library.php';

use cfg\helper\combine_named;
use cfg\db\sql;
use cfg\db\sql_creator;
use cfg\db\sql_db;
use cfg\db\sql_par;
use cfg\db\sql_type;
use cfg\db\sql_field_type;
use cfg\helper\db_object_seq_id;
use cfg\formula\formula;
use cfg\sandbox\sandbox;
use cfg\sandbox\sandbox_named;
use cfg\user\user_message;
use cfg\verb\verb;
use cfg\user\user;
use cfg\word\word;
use cfg\word\triple;
use cfg\word\word_db;
use shared\enum\messages as msg_id;
use shared\types\protection_type as protect_type_shared;
use shared\types\share_type as share_type_shared;
use shared\types\phrase_type as phrase_type_shared;
use shared\library;

class term extends combine_named
{

    /*
     * database link
     */

    // field names of the database view for terms
    // the database view is used e.g. for a fast check of a new term name
    const FLD_ID = 'term_id';
    const FLD_ID_SQL_TYP = sql_field_type::INT;
    const FLD_NAME = 'term_name';
    const FLD_USAGE = 'usage'; // included in the database view to be able to show the user the most relevant terms
    const FLD_TYPE = 'term_type_id'; // the term type for word or triple or the formula type for formulas; not used for verbs

    // the common term database field names excluding the id and excluding the user specific fields
    const FLD_NAMES = array(
        self::FLD_TYPE
    );
    // list of the user specific database field names
    // some fields like the formula expression are only used for one term class e.g. formula
    // this is done because the total number of terms is expected to be less than 10 million
    // which database should be able to handle and only a few hundred are expected to be sent to via api at once
    const FLD_NAMES_USR = array(
        sandbox_named::FLD_DESCRIPTION,
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
    // list of term types used for the database views
    // using one array of sql table types per view
    const TBL_PRIME_COM = 'terms with an id less than 2^16 so that 4 term id fit in a 64 bit db key';
    const TBL_PRIME_WHERE = '< 32767'; // 2^16 / 2 - 1
    const TBL_WORD_WHERE = ['<> 10', sql::IS_NULL]; // to exclude the formula words from the term view
    const TBL_COM = 'terms with an id that is not prime';
    const FLD_WORD_ID_TO_TERM_ID = '* 2 - 1'; // to convert a word id to a term id
    const FLD_TRIPLE_ID_TO_TERM_ID = '* -2 + 1'; // to convert a triple id to a term id
    const FLD_FORMULA_ID_TO_TERM_ID = '* 2'; // to convert a formula id to a term id
    const FLD_VERB_ID_TO_TERM_ID = '* -2'; // to convert a verb id to a term id
    // each db view can have several sql table types and as second entry a where conditions
    // or list of or where conditions
    const TBL_LIST = [
        [sql_type::PRIME, [self::TBL_WORD_WHERE, self::TBL_PRIME_WHERE], self::TBL_PRIME_COM],
        [sql_type::MOST, [self::TBL_WORD_WHERE], self::TBL_COM],
        [sql_type::PRIME, [self::TBL_WORD_WHERE, self::TBL_PRIME_WHERE], self::TBL_PRIME_COM, sql_type::USER],
        [sql_type::MOST, [self::TBL_WORD_WHERE], self::TBL_COM, sql_type::USER],
    ];
    // list of original tables that should be connoted with union
    // with fields used in the view
    // the array contains on the first level the class, fields and the where fields
    // each fields can have additional to the name the target name (AS) and a calculation rules
    // the field name can also be an array where the first field is use with priority over the following
    // the where field can be a single field or an array
    const TBL_FLD_LST_VIEW = [
        [word::class, [
            [word_db::FLD_ID, term::FLD_ID, self::FLD_WORD_ID_TO_TERM_ID],
            [user::FLD_ID],
            [word_db::FLD_NAME, term::FLD_NAME],
            [sandbox_named::FLD_DESCRIPTION],
            [word_db::FLD_VALUES, self::FLD_USAGE],
            [phrase::FLD_TYPE, self::FLD_TYPE],
            [sandbox::FLD_EXCLUDED],
            [sandbox::FLD_SHARE],
            [sandbox::FLD_PROTECT],
            ['', formula::FLD_FORMULA_TEXT],
            ['', formula::FLD_FORMULA_USER_TEXT]
        ], [phrase::FLD_TYPE, word_db::FLD_ID]],
        [triple::class, [
            [triple::FLD_ID, term::FLD_ID, self::FLD_TRIPLE_ID_TO_TERM_ID],
            [user::FLD_ID],
            [[triple::FLD_NAME, triple::FLD_NAME_GIVEN, triple::FLD_NAME_AUTO], term::FLD_NAME],
            [sandbox_named::FLD_DESCRIPTION],
            [triple::FLD_VALUES, self::FLD_USAGE],
            [phrase::FLD_TYPE, self::FLD_TYPE],
            [sandbox::FLD_EXCLUDED],
            [sandbox::FLD_SHARE],
            [sandbox::FLD_PROTECT],
            ['', formula::FLD_FORMULA_TEXT],
            ['', formula::FLD_FORMULA_USER_TEXT]
        ], ['', triple::FLD_ID]],
        [formula::class, [
            [formula::FLD_ID, term::FLD_ID, self::FLD_FORMULA_ID_TO_TERM_ID],
            [user::FLD_ID],
            [formula::FLD_NAME, term::FLD_NAME],
            [sandbox_named::FLD_DESCRIPTION],
            [formula::FLD_USAGE, self::FLD_USAGE],
            [formula::FLD_TYPE, self::FLD_TYPE],
            [sandbox::FLD_EXCLUDED],
            [sandbox::FLD_SHARE],
            [sandbox::FLD_PROTECT],
            [formula::FLD_FORMULA_TEXT],
            [formula::FLD_FORMULA_USER_TEXT]
        ], ['', formula::FLD_ID]],
        [verb::class, [
            [verb::FLD_ID, term::FLD_ID, self::FLD_VERB_ID_TO_TERM_ID],
            [sql::NULL_VALUE, user::FLD_ID, sql::FLD_CONST],
            [verb::FLD_NAME, term::FLD_NAME],
            [sandbox_named::FLD_DESCRIPTION],
            [verb::FLD_WORDS, self::FLD_USAGE],
            [sql::NULL_VALUE, self::FLD_TYPE, sql::FLD_CONST],
            [sql::NULL_VALUE, sandbox::FLD_EXCLUDED, sql::FLD_CONST],
            [share_type_shared::PUBLIC_ID, sandbox::FLD_SHARE, sql::FLD_CONST],
            [protect_type_shared::ADMIN_ID, sandbox::FLD_PROTECT, sql::FLD_CONST],
            ['', formula::FLD_FORMULA_TEXT],
            ['', formula::FLD_FORMULA_USER_TEXT]
        ], ['', verb::FLD_ID]]
    ];


    /*
     * construct and map
     */

    /**
     * always set the user because a term is always user specific
     * @param user|word|triple|formula|verb|null $obj the user who requested to see this term
     */
    function __construct(user|word|triple|formula|verb|null $obj)
    {
        if ($obj::class == user::class) {
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
     * @param array|null $db_row with the data directly from the database
     * @return bool true if at least one term has been loaded
     */
    function row_mapper_sandbox(
        ?array $db_row,
        string $id_fld = term::FLD_ID,
        string $name_fld = term::FLD_NAME,
        string $type_fld = term::FLD_TYPE,
        bool   $load_std = false,
        bool   $allow_usr_protect = true
    ): bool
    {
        $result = false;
        $this->set_obj_id(0);
        if ($db_row != null) {
            if (array_key_exists(term::FLD_ID, $db_row)) {
                $this->set_obj_from_id($db_row[term::FLD_ID]);
                if ($this->type() == word::class) {
                    $result = $this->get_word()->row_mapper_sandbox($db_row, $load_std, $allow_usr_protect, $id_fld, $name_fld, $type_fld);
                } elseif ($this->type() == triple::class) {
                    $result = $this->get_triple()->row_mapper_sandbox($db_row, $load_std, $allow_usr_protect, $id_fld, $name_fld, $type_fld);
                } elseif ($this->type() == formula::class) {
                    $result = $this->get_formula()->row_mapper_sandbox($db_row, $load_std, $allow_usr_protect, $id_fld, $name_fld, $type_fld);
                } elseif ($this->type() == verb::class) {
                    $result = $this->get_verb()->row_mapper_verb($db_row, $id_fld, $name_fld);
                } else {
                    log_err('Term ' . $this->dsp_id() . ' is of unknown type');
                }
                // overwrite the term id in the object with the real object id
                $this->set_id($db_row[$id_fld]);
            } else {
                log_err('id field missing when trying to map term from ' . implode(',', $db_row));
            }
        }
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
                    $this->obj->set_id($id);
                }
            } elseif ($class == triple::class) {
                if ($this->obj == null) {
                    $this->obj = new triple($this->user());
                    $this->obj->set_id($id);
                }
            } elseif ($class == formula::class) {
                if ($this->obj == null) {
                    $this->obj = new formula($this->user());
                    $this->obj->set_id($id);
                }
            } elseif ($class == verb::class) {
                if ($this->obj == null) {
                    $this->obj = new verb();
                    $this->obj->set_id($id);
                }
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
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name of the query use to prepare and call the query
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    private function load_sql(sql_creator $sc, string $query_name): sql_par
    {
        $qp = new sql_par(self::class);
        $qp->name .= $query_name;

        $sc->set_class(self::class);
        $sc->set_name($qp->name);

        $sc->set_usr_fields(self::FLD_NAMES_USR);
        $sc->set_usr_num_fields(self::FLD_NAMES_NUM_USR);

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a term by term id (not the object id) from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param int $id the id of the term as defined in the database term view
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_id(sql_creator $sc, int $id): sql_par
    {
        $qp = $this->load_sql($sc, sql_db::FLD_ID);
        $sc->add_where(term::FLD_ID, $id);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a term by name from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $name the name of the term and the related word, triple, formula or verb
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_name(sql_creator $sc, string $name): sql_par
    {
        $qp = $this->load_sql($sc, sql_db::FLD_NAME);
        $sc->add_where(term::FLD_NAME, $name);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

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
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_id(int $id): int
    {
        global $db_con;

        log_debug($id);
        $qp = $this->load_sql_by_id($db_con->sql_creator(), $id);
        return $this->load($qp);
    }

    /**
     * test if the name is used already via view table and just load the main parameters
     * @param string $name the name of the term and the related word, triple, formula or verb
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_name(string $name): int
    {
        global $db_con;

        log_debug($name);
        $qp = $this->load_sql_by_name($db_con->sql_creator(), $name);
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
        global $phr_typ_cac;

        $result = false;
        $wrd = new word($this->user());
        if ($wrd->load_by_id($id)) {
            log_debug('type is "' . $wrd->type_id . '" and the formula type is ' . $phr_typ_cac->id(phrase_type_shared::FORMULA_LINK));
            if ($wrd->type_id == $phr_typ_cac->id(phrase_type_shared::FORMULA_LINK)) {
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
            if ($trp->load_by_id($id)) {
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
    private function load_formula_by_id(int $id): bool
    {
        $result = false;
        $frm = new formula($this->user());
        if ($frm->load_by_id($id)) {
            $this->set_id_from_obj($frm->id(), formula::class);
            $this->obj = $frm;
            $result = true;
        }
        return $result;
    }

    /**
     * simply load a verb
     */
    private function load_verb_by_id(int $id): bool
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
        global $phr_typ_cac;

        $result = false;
        $wrd = new word($this->user());
        if ($wrd->load_by_name($name)) {
            log_debug('type is "' . $wrd->type_id . '" and the formula type is ' . $phr_typ_cac->id(phrase_type_shared::FORMULA_LINK));
            if ($wrd->type_id == $phr_typ_cac->id(phrase_type_shared::FORMULA_LINK)) {
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
            if ($trp->load_by_name($name)) {
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
        if ($frm->load_by_name($name)) {
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

    function name_field(): string
    {
        return self::FLD_NAME;
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
            if (get_class($this->obj) == word::class) {
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

    function get_word(): word
    {
        $wrd = new word($this->user());
        if (get_class($this->obj) == word::class) {
            $wrd = $this->obj;
        }
        return $wrd;
    }

    function get_triple(): triple
    {
        $lnk = new triple($this->user());
        if (get_class($this->obj) == triple::class) {
            $lnk = $this->obj;
        }
        return $lnk;
    }

    function get_formula(): formula
    {
        $frm = new formula($this->user());
        if (get_class($this->obj) == formula::class) {
            $frm = $this->obj;
        }
        return $frm;
    }

    function get_verb(): verb
    {
        $vrb = new verb();
        if (get_class($this->obj) == verb::class) {
            $vrb = $this->obj;
        }
        return $vrb;
    }

    function get_phrase(): ?phrase
    {
        $phr = null;
        if (get_class($this->obj) == word::class) {
            $phr = $this->obj->phrase();
        } elseif (get_class($this->obj) == triple::class) {
            $phr = $this->obj->phrase();
        }
        return $phr;
    }


    /*
     * user interface language specific functions
     */

    /**
     * create a translatable message that the name is already used
     */
    function id_used_msg(db_object_seq_id $obj_to_add): user_message
    {
        $lib = new library();
        $usr_msg = new user_message();

        if ($this->id() != 0) {
            $class = $lib->class_to_name($this->type());
            $usr_msg->add_id_with_vars(msg_id::CLASS_ALREADY_EXISTS, [
                msg_id::VAR_CLASS_NAME => $class,
                msg_id::VAR_NAME => $this->name(),
                msg_id::VAR_VALUE => $lib->class_to_name($obj_to_add::class)
            ]);
        }

        return $usr_msg;
    }

    /**
     * create a message text that the name is already used
     */
    function id_used_msg_text(db_object_seq_id $obj_to_add): string
    {
        return $this->id_used_msg($obj_to_add)->get_last_message_translated();
    }

    /*
     * information functions
     */

    function is_time(): bool
    {
        $result = false;
        $phr = $this->get_phrase();
        if ($phr != null) {
            if ($phr->is_time()) {
                $result = true;
            }
        }
        return $result;
    }


    /*
     * info
     */

    /**
     * @return user_message ok message if this word or triple might be read to be added to the database
     */
    function can_be_ready(): user_message
    {
        return $this->obj()->can_be_ready();
    }

    /**
     * @return user_message ok message if this word or triple can be added to the database
     */
    function db_ready(): user_message
    {
        return $this->obj()->db_ready();
    }

    /**
     * @return bool true if it has a valid id and name and the phrase is expected to be stored in the database
     */
    function is_valid(): bool
    {
        return $this->obj()->is_valid();
    }


    /*
     * debug
     */

    /**
     * @return string the unique id fields
     */
    function dsp_id(): string
    {
        if ($this->obj() != null) {
            return $this->obj()->dsp_id() . ' as term';
        } else {
            return 'term with null object';
        }
    }

}