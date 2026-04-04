<?php

/*

    model/phrase/term.php - either a word, verb, triple or formula
    -------------------

    TODO: load formula word
        check triple

    mainly to check the term consistency of all objects
    a term must be unique for word, verb and triple e.g. "company" is a word "is a" is a verb and "Kanton Zurich" is a triple
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

namespace Zukunft\ZukunftCom\main\php\cfg\phrase;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_HELPER . 'combine_named.php';
include_once paths::MODEL_CONST . 'def.php';
include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_par.php';
include_once paths::DB . 'sql_type.php';
include_once paths::DB . 'sql_type_list.php';
include_once paths::DB . 'sql_field_type.php';
include_once paths::EXPORT . 'export_type_list.php';
include_once paths::MODEL_HELPER . 'data_object.php';
include_once paths::MODEL_HELPER . 'db_object_seq_id.php';
include_once paths::MODEL_FORMULA . 'formula.php';
include_once paths::MODEL_FORMULA . 'formula_db.php';
include_once paths::MODEL_SANDBOX . 'sandbox.php';
include_once paths::MODEL_VERB . 'verb.php';
include_once paths::MODEL_VERB . 'verb_db.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_db.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::MODEL_WORD . 'word.php';
include_once paths::MODEL_WORD . 'word_db.php';
include_once paths::MODEL_WORD . 'triple.php';
include_once paths::MODEL_WORD . 'triple_db.php';
include_once paths::MODEL_PHRASE . 'phrase.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_HELPER . 'Message.php';
include_once paths::SHARED_TYPES . 'protection_types.php';
include_once paths::SHARED_TYPES . 'share_types.php';
include_once paths::SHARED_TYPES . 'phrase_types.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'library.php';

use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\main\php\cfg\db\sql;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type_list;
use Zukunft\ZukunftCom\main\php\cfg\export\export_type_list;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_db;
use Zukunft\ZukunftCom\main\php\cfg\helper\combine_named;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_type;
use Zukunft\ZukunftCom\main\php\cfg\helper\data_object;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_object_seq_id;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox;
use Zukunft\ZukunftCom\main\php\cfg\user\user_db;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb_db;
use Zukunft\ZukunftCom\main\php\cfg\word\triple_db;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\cfg\word\word_db;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\helper\Message;
use Zukunft\ZukunftCom\main\php\shared\types\protection_types as protect_type_shared;
use Zukunft\ZukunftCom\main\php\shared\types\share_types as share_type_shared;
use Zukunft\ZukunftCom\main\php\shared\types\phrase_types as phrase_type_shared;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;

class term extends combine_named
{

    /*
     * database link
     */

    // field names of the database view for terms
    // the database view is used e.g. for a fast check of a new term name
    const string FLD_ID = 'term_id';
    const sql_field_type FLD_ID_SQL_TYP = sql_field_type::INT;
    const string FLD_NAME = 'term_name';
    const string FLD_USAGE = 'usage'; // included in the database view to be able to show the user the most relevant terms
    const string FLD_IMPACT = 'impact';
    const string FLD_TYPE = 'term_type_id'; // the term type for word or triple or the formula type for formulas; not used for verbs

    // the common term database field names excluding the id and excluding the user-specific fields
    const array FLD_NAMES = array(
        self::FLD_TYPE
    );
    // list of the user-specific database field names
    // some fields like the formula expression are only used for one term class e.g. formula
    // this is done because the total number of terms is expected to be less than 10 million
    // which database should be able to handle and only a few hundred are expected to be sent to via api at once
    const array FLD_NAMES_USR = array(
        sql_db::FLD_DESCRIPTION,
        formula_db::FLD_FORMULA_TEXT,
        formula_db::FLD_FORMULA_USER_TEXT
    );
    // list of the user-specific numeric database field names
    const array FLD_NAMES_NUM_USR = array(
        sql_db::FLD_USAGE,
        sql_db::FLD_IMPACT,
        sql_db::FLD_EXCLUDED,
        sandbox::FLD_SHARE,
        sandbox::FLD_PROTECT
    );
    // list of term types used for the database views
    // using one array of sql table types per view
    const string TBL_PRIME_COM = 'terms with an id less than 2^16 so that 4 term id fit in a 64 bit db key';
    const string TBL_PRIME_WHERE = '< 32767'; // 2^16 / 2 - 1
    const array TBL_WORD_WHERE = ['<> 10', sql::IS_NULL]; // to exclude the formula words from the term view
    const string TBL_COM = 'terms with an id that is not prime';
    const string FLD_WORD_ID_TO_TERM_ID = '* 2 - 1'; // to convert a word id to a term id
    const string FLD_TRIPLE_ID_TO_TERM_ID = '* -2 + 1'; // to convert a triple id to a term id
    const string FLD_FORMULA_ID_TO_TERM_ID = '* 2'; // to convert a formula id to a term id
    const string FLD_VERB_ID_TO_TERM_ID = '* -2'; // to convert a verb id to a term id
    // each db view can have several sql table types and as second entry a where conditions
    // or list of or where conditions
    const array TBL_LIST = [
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
    const array TBL_FLD_LST_VIEW = [
        [word::class, [
            [word_db::FLD_ID, term::FLD_ID, self::FLD_WORD_ID_TO_TERM_ID],
            [user_db::FLD_ID],
            [word_db::FLD_NAME, term::FLD_NAME],
            [sql_db::FLD_DESCRIPTION],
            [sql_db::FLD_USAGE],
            [sql_db::FLD_IMPACT],
            [phrase::FLD_TYPE, self::FLD_TYPE],
            [sql_db::FLD_EXCLUDED],
            [sandbox::FLD_SHARE],
            [sandbox::FLD_PROTECT],
            ['', formula_db::FLD_FORMULA_TEXT],
            ['', formula_db::FLD_FORMULA_USER_TEXT]
        ], [phrase::FLD_TYPE, word_db::FLD_ID]],
        [triple::class, [
            [triple_db::FLD_ID, term::FLD_ID, self::FLD_TRIPLE_ID_TO_TERM_ID],
            [user_db::FLD_ID],
            [[triple_db::FLD_NAME, triple_db::FLD_NAME_GIVEN, triple_db::FLD_NAME_AUTO], term::FLD_NAME],
            [sql_db::FLD_DESCRIPTION],
            [sql_db::FLD_USAGE],
            [sql_db::FLD_IMPACT],
            [phrase::FLD_TYPE, self::FLD_TYPE],
            [sql_db::FLD_EXCLUDED],
            [sandbox::FLD_SHARE],
            [sandbox::FLD_PROTECT],
            ['', formula_db::FLD_FORMULA_TEXT],
            ['', formula_db::FLD_FORMULA_USER_TEXT]
        ], ['', triple_db::FLD_ID]],
        [formula::class, [
            [formula_db::FLD_ID, term::FLD_ID, self::FLD_FORMULA_ID_TO_TERM_ID],
            [user_db::FLD_ID],
            [formula_db::FLD_NAME, term::FLD_NAME],
            [sql_db::FLD_DESCRIPTION],
            [sql_db::FLD_USAGE],
            [sql_db::FLD_IMPACT],
            [formula_db::FLD_TYPE, self::FLD_TYPE],
            [sql_db::FLD_EXCLUDED],
            [sandbox::FLD_SHARE],
            [sandbox::FLD_PROTECT],
            [formula_db::FLD_FORMULA_TEXT],
            [formula_db::FLD_FORMULA_USER_TEXT]
        ], ['', formula_db::FLD_ID]],
        [verb::class, [
            [verb_db::FLD_ID, term::FLD_ID, self::FLD_VERB_ID_TO_TERM_ID],
            [sql::NULL_VALUE, user_db::FLD_ID, sql_db::FLD_CONST],
            [verb_db::FLD_NAME, term::FLD_NAME],
            [sql_db::FLD_DESCRIPTION],
            [sql_db::FLD_USAGE],
            [sql_db::FLD_IMPACT],
            [sql::NULL_VALUE, self::FLD_TYPE, sql_db::FLD_CONST],
            [sql::NULL_VALUE, sql_db::FLD_EXCLUDED, sql_db::FLD_CONST],
            [share_type_shared::PUBLIC_ID, sandbox::FLD_SHARE, sql_db::FLD_CONST],
            [protect_type_shared::ADMIN_ID, sandbox::FLD_PROTECT, sql_db::FLD_CONST],
            ['', formula_db::FLD_FORMULA_TEXT],
            ['', formula_db::FLD_FORMULA_USER_TEXT]
        ], ['', verb_db::FLD_ID]]
    ];


    /*
     * construct and map
     */

    /**
     * always set the user because a term is always user-specific
     * @param user|word|triple|formula|verb|null $obj the user who requested to see this term
     */
    function __construct(user|word|triple|formula|verb|null $obj)
    {
        if ($obj != null) {
            if ($obj::class == user::class) {
                // create a dummy word object to remember the user
                parent::__construct(new word($obj));
            } else {
                parent::__construct($obj);
            }
        } else {
            log_err('object is null when trying to construct a term');
            parent::__construct(new word($obj));
        }
    }

    function reset(bool $keep_user = false): void
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
                $this->set_usage($db_row[sql_db::FLD_USAGE]);
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

    function clone_reset(bool $keep_user): word|verb|triple|formula|phrase|term
    {
        $obj = $this->obj->clone_reset($keep_user);
        if (in_array($obj::class, def::TERM_CLASSES)) {
            $obj = $obj->term();
        }
        return $obj;
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
            $this->obj = new triple($this->get_user());
        } elseif ($class == formula::class) {
            $this->obj = new formula($this->get_user());
        } elseif ($class == verb::class) {
            $this->obj = new verb();
        } else {
            $this->obj = new word($this->get_user());
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
                $this->obj = new formula($this->get_user());
            } else {
                $this->obj = new word($this->get_user());
            }
        } else {
            if ($id % 2 == 0) {
                $this->obj = new verb();
            } else {
                $this->obj = new triple($this->get_user());
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
                    $this->obj = new word($this->get_user());
                }
            } elseif ($class == triple::class) {
                if ($this->obj == null) {
                    $this->obj = new triple($this->get_user());
                }
            } elseif ($class == formula::class) {
                if ($this->obj == null) {
                    $this->obj = new formula($this->get_user());
                }
            } elseif ($class == verb::class) {
                if ($this->obj == null) {
                    $this->obj = new verb();
                }
            }
            $this->obj->id = $id;
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
            $this->obj = new word($this->get_user());
        } elseif ($class == triple::class) {
            $this->obj = new triple($this->get_user());
        } elseif ($class == formula::class) {
            $this->obj = new formula($this->get_user());
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
        $this->obj()->set_name($name);
    }

    /**
     * set the value to rank the words by impact
     *
     * @param float|null $impact a higher value moves the word to the top of the selection list
     * @return void
     */
    function set_impact(?float $impact): void
    {
        $this->obj()->impact = $impact;
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
        $this->obj()->set_user($usr);
    }

    /**
     * @return int the id of the user or 0 if the user is not set
     */
    function get_user_id(): int
    {
        return $this->obj()->get_user_id();
    }

    /**
     * @return int|null the id of the owner if the user is not set
     */
    function owner_id(): ?int
    {
        return $this->obj()->owner_id();
    }

    function code_id(): ?int
    {
        return $this->obj()->get_code_id();
    }

    function share_id(): ?int
    {
        return $this->obj()->share_id();
    }

    function protection_id(): ?int
    {
        return $this->obj()->protection_id();
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
            $this->obj()->usage = 0;
        } else {
            $this->obj()->usage = $usage;
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
        $id = 0;
        if ($this->obj_id() != 0) {
            if ($this->is_word()) {
                $id = ($this->obj_id() * 2) - 1;
            } elseif ($this->is_triple()) {
                $id = ($this->obj_id() * -2) + 1;
            } elseif ($this->is_formula()) {
                $id = ($this->obj_id() * 2);
            } elseif ($this->is_verb()) {
                $id = ($this->obj_id() * -2);
            } else {
                return 0;
            }
        }
        return $id;
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
        if ($this->obj() != null) {
            $result = $this->obj()->name();
        }
        return $result;
    }

    /**
     * @return user|null the person who wants to see a term (word, verb, triple or formula)
     *                   in case of a verb it can be null
     */
    function get_user(): ?user
    {
        $result = new user();
        if ($this->obj() != null) {
            $result = $this->obj()->get_user();
        }
        return $result;
    }

    function type(): string
    {
        $result = '';
        if ($this->obj() != null) {
            $result = $this->obj::class;
        }
        return $result;
    }

    function get_usage(): ?int
    {
        return $this->obj()->usage;
    }

    function get_impact(): ?float
    {
        return $this->obj()->impact;
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
        $phr = new phrase($this->get_user());
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
     * object
     */

    /**
     * call the object fill function
     *
     * @param word|triple|verb|formula|term $obj the object that should fill up this term
     * @return void
     */
    function fill(word|triple|verb|formula|term $obj): void
    {
        if ($this->is_word() and $obj::class == word::class) {
            $this->obj()->fill($obj, $this->get_user());
        } elseif ($this->is_word() and $obj::class == term::class and $obj->is_word()) {
            $this->obj()->fill($obj->get_word(), $this->get_user());
        } elseif ($this->is_triple() and $obj::class == triple::class) {
            $this->obj()->fill($obj, $this->get_user());
        } elseif ($this->is_triple() and $obj::class == term::class and $obj->is_triple()) {
            $this->obj()->fill($obj->get_triple(), $this->get_user());
        } elseif ($this->is_verb() and $obj::class == verb::class) {
            $this->obj()->fill($obj, $this->get_user());
        } elseif ($this->is_verb() and $obj::class == term::class and $obj->is_verb()) {
            $this->obj()->fill($obj->get_triple(), $this->get_user());
        } elseif ($this->is_formula() and $obj::class == formula::class) {
            $this->obj()->fill($obj, $this->get_user());
        } elseif ($this->is_formula() and $obj::class == term::class and $obj->is_formula()) {
            $this->obj()->fill($obj->get_triple(), $this->get_user());
        }
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
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
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
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
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
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
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
     * create the SQL to load the single default value always by the id or name
     * @param sql_creator $sc with the target db_type set
     * @param string $name the database row id to select the standard row
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql_standard_by_name(sql_creator $sc, string $name): sql_par
    {
        $qp = new sql_par($this::class, new sql_type_list([sql_type::NORM]));
        $qp->name .= sql_db::FLD_NAME;

        $sc->set_class($this::class);
        $sc->set_name($qp->name);
        $sc->set_fields($this->all_fields());
        $sc->add_where($this->name_field(), $name);
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
     * test if the name is used already via view table and load the main parameters
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
     * test if the name is used already via view table and load the main parameters
     * @param string $name the name of the term and the related word, triple, formula or verb
     * @param user_message $msg to collect the error messages and suggested solutions for the calling user
     * @return int the id of the object found and zero if nothing is found
     */
    function load_standard_by_name(string $name, user_message $msg): int
    {
        global $db_con;

        log_debug($name);
        $qp = $this->load_sql_standard_by_name($db_con->sql_creator(), $name);
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
        global $sys;

        $result = false;
        $wrd = new word($this->get_user());
        if ($wrd->load_by_id($id)) {
            log_debug('type is "' . $wrd->type_id . '" and the formula type is ' . $sys->typ_lst->phr_typ->id(phrase_type_shared::FORMULA_LINK));
            if ($wrd->type_id == $sys->typ_lst->phr_typ->id(phrase_type_shared::FORMULA_LINK)) {
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
            $trp = new triple($this->get_user());
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
        $frm = new formula($this->get_user());
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
        $vrb->set_user($this->get_user());
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
        global $sys;

        $result = false;
        $wrd = new word($this->get_user());
        if ($wrd->load_by_name($name)) {
            log_debug('type is "' . $wrd->type_id . '" and the formula type is ' . $sys->typ_lst->phr_typ->id(phrase_type_shared::FORMULA_LINK));
            if ($wrd->type_id == $sys->typ_lst->phr_typ->id(phrase_type_shared::FORMULA_LINK)) {
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
            $trp = new triple($this->get_user());
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
        $frm = new formula($this->get_user());
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
        $vrb->set_user($this->get_user());
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
        $wrd = new word($this->get_user());
        if (get_class($this->obj) == word::class) {
            $wrd = $this->obj;
        }
        return $wrd;
    }

    function get_triple(): triple
    {
        $lnk = new triple($this->get_user());
        if (get_class($this->obj) == triple::class) {
            $lnk = $this->obj;
        }
        return $lnk;
    }

    function get_formula(): formula
    {
        $frm = new formula($this->get_user());
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
            $phr = $this->obj()->phrase();
        } elseif (get_class($this->obj) == triple::class) {
            $phr = $this->obj()->phrase();
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
        $msg = new user_message();

        if ($this->id() != 0) {
            $class = $lib->class_to_name($this->type());
            $msg->add(msg_id::NAME_ALREADY_EXISTS, [
                msg_id::VAR_CLASS_NAME => $class,
                msg_id::VAR_NAME => $this->name(),
                msg_id::VAR_VALUE => $lib->class_to_name($obj_to_add::class)
            ]);
        }

        return $msg;
    }

    /**
     * create a message text that the name is already used
     */
    function id_used_msg_text(db_object_seq_id $obj_to_add): string
    {
        return $this->id_used_msg($obj_to_add)->get_last_message_translated();
    }


    /*
     * info functions
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
     * check if the word, verb, triple or formula in the database needs to be updated
     * e.g. for import if this word has only the name set, the protection should not be updated in the database
     *
     * @param term $db_trm the word, verb, triple or formula as saved in the database
     * @return bool true if this word has infos that should be saved in the database
     */
    function needs_db_update(term $db_trm): bool
    {
        if ($this->is_word() and $db_trm->is_word()) {
            $wrd = $this->obj();
            $db_wrd = $db_trm->obj();
            return $wrd->needs_db_update($db_wrd);
        } elseif ($this->is_verb() and $db_trm->is_verb()) {
            $vrb = $this->obj();
            $db_vrb = $db_trm->obj();
            return $vrb->needs_db_update($db_vrb);
        } elseif ($this->is_triple() and $db_trm->is_triple()) {
            $trp = $this->obj();
            $db_trp = $db_trm->obj();
            return $trp->needs_db_update($db_trp);
        } elseif ($this->is_formula() and $db_trm->is_formula()) {
            $frm = $this->obj();
            $db_frm = $db_trm->obj();
            return $frm->needs_db_update($db_frm);
        } else {
            return true;
        }
    }

    /**
     * check if the word, verb, triple or formula can be added to the database if all related terms are added
     * the differentiation to the db_ready is relevant to save a list of triples to the database
     * where some triples are part of other triples that have to be added with another save list attempt
     * @param user_message|Message $msg fill up with the message if this term might be read to be added to the database
     * @return bool true if another save list attempt is expected to add more word, verb, triple or formula to the database
     */
    function can_be_ready(user_message|Message $msg): bool
    {
        return $this->obj()->can_be_ready($msg);
    }

    /**
     * checks if the word, verb, triple or formula object can be added to the database
     *
     * @param user_message|Message $msg the explanation for the user why the underlying word, verb, triple or formula cannot yet be added to the database
     * @return true if all mandatory vars of the underlying object are set and the term can be stored in the database
     */
    function db_ready(user_message|Message $msg): bool
    {
        return $this->obj()->db_ready($msg);
    }

    /**
     * @return bool true if it has a valid id and name and the phrase is expected to be stored in the database
     */
    function is_valid(): bool
    {
        return $this->obj()->is_valid();
    }


    /*
     * im- and export
     */

    /**
     * set the vars of this term object based on the given json without writing to the database
     *
     * @param array $in_ex_json an array with the data of the json object
     * @param user_message $usr_msg to enrich with warnings, problems and solutions
     * @param data_object|null $dto the data object that contains the already imported formulas
     * @return bool true if everything was fine
     */
    function import_mapper(
        array        $in_ex_json,
        user_message $usr_msg,
        ?data_object $dto = null
    ): bool
    {
        // reset the all parameters for these formula link object but keep the user
        $this->reset(true);

        if (array_key_exists(json_fields::OBJECT_CLASS, $in_ex_json)) {
            $class = $in_ex_json[json_fields::OBJECT_CLASS];
            if ($class == json_fields::CLASS_WORD) {
                $wrd = new word($this->get_user());
                $wrd->import_mapper($in_ex_json, $usr_msg, $dto);
                $this->set_obj($wrd);
            } elseif ($class == json_fields::CLASS_VERB) {
                $vrb = new verb();
                $vrb->import_mapper($in_ex_json, $usr_msg, $dto);
                $this->set_obj($vrb);
            } elseif ($class == json_fields::CLASS_TRIPLE) {
                $trp = new triple($this->get_user());
                $trp->import_mapper($in_ex_json, $usr_msg, $dto);
                $this->set_obj($trp);
            } elseif ($class == json_fields::CLASS_FORMULA) {
                $frm = new formula($this->get_user());
                $frm->import_mapper($in_ex_json, $usr_msg, $dto);
                $this->set_obj($frm);
            } else {
                // TODO Prio 0 review
                $usr_msg->add_err(msg_id::IMPORT_FAILED, []);
            }
        }

        return $usr_msg->is_ok();
    }

    /**
     * create an array with the export json fields of this component
     * which does not include the internal database id
     * @param export_type_list|array $exp_typ define the export format
     * @param bool $do_load true if any missing data should be loaded while creating the array
     * @return array with the json fields
     */
    function export_json(export_type_list|array $exp_typ = [], bool $do_load = true): array
    {
        if ($this->is_word()) {
            $wrd = $this->get_word();
            $vars = $wrd->export_json($exp_typ, $do_load);
            $vars[json_fields::OBJECT_CLASS] = json_fields::CLASS_WORD;
        } elseif ($this->is_verb()) {
            $vrb = $this->get_verb();
            $vars = $vrb->export_json($exp_typ, $do_load);
            $vars[json_fields::OBJECT_CLASS] = json_fields::CLASS_VERB;
        } elseif ($this->is_triple()) {
            $trp = $this->get_triple();
            $vars = $trp->export_json($exp_typ, $do_load);
            $vars[json_fields::OBJECT_CLASS] = json_fields::CLASS_TRIPLE;
        } elseif ($this->is_formula()) {
            $frm = $this->get_formula();
            $vars = $frm->export_json($exp_typ, $do_load);
            $vars[json_fields::OBJECT_CLASS] = json_fields::CLASS_FORMULA;
        } else {
            $msg = 'term with unknown object';
            log_err($msg);
            $vars = [];
        }
        return $vars;
    }


    /*
     * sql fields
     */

    /**
     * @return array with all fields names of this object
     */
    protected function all_fields(): array
    {
        return $this::FLD_NAMES;
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
            $msg = 'ERROR: term with null object';
            log_err($msg);
            return $msg;
        }
    }

}