<?php

/*

    model/phrase/phrase.php - either a word or a triple
    ---------------------

    this object is not stored in the database in a separate table
    e.g. to build a selector the entries are caught either from the words or triples table

    If the user wants to overwrite a formula result, there are two possibilities for the technical realisation

    1. for each formula automatically a word with the special type "formula link" is created
        advantages:
            user value handling is in one table (values)
            formulas can be part of a triple
        disadvantages:
            the formula name is saved twice

    2. The result can directly be overwritten by the user
        advantages:
            the formula name is only saved once
        disadvantages:
            the probably huge result table needs an extra field to indicate user overwrites which makes the use of key/value databases more complicated

    There is a word increase and a formula that calculates the increase, so the solution 1. with formula link words is implemented

    The main sections of this object are
    - db const:          const for the database link
    - construct and map: including the mapping of the db row to this word object
    - set and get:       to capsule the vars from unexpected changes
    - modify:            change potentially all variables of this word object
    - cast:              create an api object and set the vars from an api json
    - load:              database access object (DAO) functions
    - sql fields:        field names for sql
    - load related:      load related objects from the database
    - data retrieval:    load related lists from the database
    - classification:    information what
    - info:              functions to make code easier to read
    - save:              manage to update the database


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

namespace cfg\phrase;

use cfg\const\paths;

include_once paths::MODEL_HELPER . 'combine_named.php';
include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_field_type.php';
include_once paths::DB . 'sql_par.php';
include_once paths::DB . 'sql_type.php';
include_once paths::MODEL_HELPER . 'db_object_seq_id.php';
include_once paths::SHARED_HELPER . 'IdObject.php';
include_once paths::SHARED_HELPER . 'TextIdObject.php';
//include_once paths::MODEL_FORMULA . 'formula.php';
//include_once paths::MODEL_FORMULA . 'formula_db.php';
include_once paths::MODEL_FORMULA . 'formula_link.php';
include_once paths::MODEL_GROUP . 'group_list.php';
include_once paths::MODEL_SANDBOX . 'sandbox.php';
include_once paths::MODEL_SANDBOX . 'sandbox_named.php';
include_once paths::MODEL_VALUE . 'value_list.php';
include_once paths::MODEL_VERB . 'verb_db.php';
include_once paths::MODEL_VERB . 'verb_list.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_db.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::MODEL_WORD . 'word.php';
include_once paths::MODEL_WORD . 'word_db.php';
include_once paths::MODEL_WORD . 'word_list.php';
include_once paths::MODEL_WORD . 'triple.php';
include_once paths::MODEL_WORD . 'triple_db.php';
include_once paths::MODEL_PHRASE . 'phrase.php';
include_once paths::SHARED_ENUM . 'foaf_direction.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';
include_once paths::SHARED_TYPES . 'phrase_type.php';
include_once paths::SHARED_TYPES . 'verbs.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'library.php';

use cfg\db\sql;
use cfg\formula\formula_db;
use cfg\helper\combine_named;
use cfg\db\sql_creator;
use cfg\db\sql_db;
use cfg\db\sql_field_type;
use cfg\db\sql_par;
use cfg\db\sql_type;
use cfg\helper\db_object_seq_id;
use cfg\formula\formula;
use cfg\formula\formula_link;
use cfg\group\group_list;
use cfg\sandbox\sandbox;
use cfg\sandbox\sandbox_named;
use cfg\user\user_db;
use cfg\value\value_list;
use cfg\verb\verb_db;
use cfg\verb\verb_list;
use cfg\user\user;
use cfg\user\user_message;
use cfg\word\triple_db;
use cfg\word\word;
use cfg\word\word_db;
use cfg\word\word_list;
use cfg\word\triple;
use shared\enum\foaf_direction;
use shared\enum\messages as msg_id;
use shared\helper\IdObject;
use shared\helper\TextIdObject;
use shared\json_fields;
use shared\library;
use shared\types\phrase_type as phrase_type_shared;
use shared\types\verbs;

class phrase extends combine_named
{

    /*
     * db const
     */

    // the database and JSON object duplicate field names for combined word and triples mainly to link phrases
    // *_SQL_TYP is the sql data type used for the field
    const FLD_ID = 'phrase_id';
    const FLD_ID_SQL_TYP = sql_field_type::INT;
    const FLD_NAME = 'phrase_name';
    const FLD_TYPE = 'phrase_type_id';
    const FLD_TYPE_NAME = 'phrase_type_name'; // used for the log parameter only
    const FLD_TYPE_SQL_TYP = sql_field_type::INT_SMALL;
    const FLD_VALUES = 'values';

    // the common phrase database field names excluding the id and excluding the user specific fields
    const FLD_NAMES = array(
        phrase::FLD_TYPE
    );
    // list of the common user specific database field names of phrases excluding the standard name field
    const FLD_NAMES_USR_EX = array(
        sql_db::FLD_DESCRIPTION
    );
    // list of the common user specific database field names of phrases
    const FLD_NAMES_USR = array(
        phrase::FLD_NAME,
        sql_db::FLD_DESCRIPTION
    );
    // list of the common user specific database field names of phrases
    const FLD_NAMES_USR_NO_NAME = array(
        sql_db::FLD_DESCRIPTION
    );
    // list of the common user specific numeric database field names of phrases
    const FLD_NAMES_NUM_USR = array(
        self::FLD_VALUES,
        sql_db::FLD_EXCLUDED,
        sandbox::FLD_SHARE,
        sandbox::FLD_PROTECT
    );
    // list of phrase types used for the database views
    // using one array of sql table types per view
    const TBL_PRIME_COM = 'phrases with an id less than 2^16 so that 4 phrase id fit in a 64 bit db key';
    const TBL_PRIME_WHERE = '< 32767';
    const TBL_COM = 'phrases with an id that is not prime';
    const TBL_LIST = [
        [sql_type::PRIME, self::TBL_PRIME_WHERE, self::TBL_PRIME_COM],
        [sql_type::MOST, '', self::TBL_COM],
        [sql_type::PRIME, self::TBL_PRIME_WHERE, self::TBL_PRIME_COM, sql_type::USER],
        [sql_type::MOST, '', self::TBL_COM, sql_type::USER],
    ];
    // list of original tables that should be connoted with union
    // with fields used in the view
    const TBL_FLD_LST_VIEW = [
        [word::class, [
            [word_db::FLD_ID, phrase::FLD_ID],
            [user_db::FLD_ID],
            [word_db::FLD_NAME, phrase::FLD_NAME],
            [sql_db::FLD_DESCRIPTION],
            [word_db::FLD_VALUES],
            [phrase::FLD_TYPE],
            [sql_db::FLD_EXCLUDED],
            [sandbox::FLD_SHARE],
            [sandbox::FLD_PROTECT]
        ], word_db::FLD_ID],
        [triple::class, [
            [triple_db::FLD_ID, phrase::FLD_ID, '* -1'],
            [user_db::FLD_ID],
            [[triple_db::FLD_NAME, triple_db::FLD_NAME_GIVEN, triple_db::FLD_NAME_AUTO], phrase::FLD_NAME],
            [sql_db::FLD_DESCRIPTION],
            [triple_db::FLD_VALUES],
            [phrase::FLD_TYPE],
            [sql_db::FLD_EXCLUDED],
            [sandbox::FLD_SHARE],
            [sandbox::FLD_PROTECT]
        ], triple_db::FLD_ID]
    ];


    /*
     * construct and map
     */

    /**
     * always set the user because a phrase is always user specific
     * @param user|word|triple|null $obj the word or triple that should be covered by the phrase
     * @param int|null $id the database id of the phrase (not the object!)
     */
    function __construct(user|word|triple|null $obj = null, int|null $id = null)
    {
        if ($obj == null) {
            // create a dummy word object to remember the user
            parent::__construct(new word($this->user()));
        } else {
            if ($obj::class == user::class) {
                // create a dummy word object to remember the user
                parent::__construct(new word($obj));
            } else {
                parent::__construct($obj);
            }
        }
        if ($id != null) {
            $this->set_obj_from_id($id);
        }

    }

    /**
     * map the common word and triple database fields to the phrase fields
     *
     * @param array|null $db_row with the data directly from the database
     * @param string $id_fld the name of the id field as defined in this child and given to the parent
     * @return bool true if the triple is loaded and valid
     */
    function row_mapper_sandbox(?array $db_row, string $id_fld = self::FLD_ID, string $fld_ext = ''): bool
    {
        $result = false;
        $this->set_obj_id(0);
        if ($db_row != null) {
            /* TODO try to used the object mapper
            if (array_key_exists(phrase::FLD_ID, $db_row)) {
                $this->set_obj_from_id($db_row[phrase::FLD_ID]);
                if ($this->type() == word::class) {
                    $result = $this->get_word()->row_mapper_sandbox($db_row, $load_std, $allow_usr_protect, $id_fld, $name_fld, $type_fld);
                } elseif ($this->type() == triple::class) {
                    $result = $this->get_triple()->row_mapper_sandbox($db_row, $load_std, $allow_usr_protect, $id_fld, $name_fld, $type_fld);
                } else {
                    log_warning('Term ' . $this->dsp_id() . ' is of unknown type');
                }
                // overwrite the phrase id in the object with the real object id
                $this->set_id($db_row[$id_fld]);
                $this->set_name($db_row[phrase::FLD_NAME . $fld_ext]);
            }
            */

            if ($db_row[$id_fld] > 0) {
                // map a word
                $wrd = new word($this->user());
                $wrd->set_id($db_row[$id_fld]);
                $wrd->set_name($db_row[phrase::FLD_NAME . $fld_ext]);
                if (array_key_exists(sql_db::FLD_DESCRIPTION . $fld_ext, $db_row)) {
                    $wrd->description = $db_row[sql_db::FLD_DESCRIPTION . $fld_ext];
                }
                if (array_key_exists(phrase::FLD_TYPE . $fld_ext, $db_row)) {
                    $wrd->type_id = $db_row[phrase::FLD_TYPE . $fld_ext];
                }
                if (array_key_exists(sql_db::FLD_EXCLUDED . $fld_ext, $db_row)) {
                    $wrd->set_excluded($db_row[sql_db::FLD_EXCLUDED . $fld_ext]);
                }
                if (array_key_exists(sandbox::FLD_SHARE . $fld_ext, $db_row)) {
                    $wrd->set_share_id($db_row[sandbox::FLD_SHARE . $fld_ext]);
                }
                if (array_key_exists(sandbox::FLD_PROTECT . $fld_ext, $db_row)) {
                    $wrd->set_protection_id($db_row[sandbox::FLD_PROTECT . $fld_ext]);
                }
                //$wrd->set_owner_id($db_row[_user_db::FLD_ID . $fld_ext]);
                $this->obj = $wrd;
                $result = true;
            } elseif ($db_row[$id_fld] < 0) {
                // map a triple
                $trp = new triple($this->user());
                $trp->set_id($db_row[$id_fld] * -1);
                $name = $db_row[phrase::FLD_NAME . $fld_ext];
                if ($name != null) {
                    $trp->set_name($db_row[phrase::FLD_NAME . $fld_ext]);
                }
                if (array_key_exists(sql_db::FLD_DESCRIPTION . $fld_ext, $db_row)) {
                    $trp->description = $db_row[sql_db::FLD_DESCRIPTION . $fld_ext];
                }
                if (array_key_exists(phrase::FLD_TYPE . $fld_ext, $db_row)) {
                    $trp->type_id = $db_row[phrase::FLD_TYPE . $fld_ext];
                }
                if (array_key_exists(sql_db::FLD_EXCLUDED . $fld_ext, $db_row)) {
                    $trp->set_excluded($db_row[sql_db::FLD_EXCLUDED . $fld_ext]);
                }
                if (array_key_exists(sandbox::FLD_SHARE . $fld_ext, $db_row)) {
                    $trp->set_share_id($db_row[sandbox::FLD_SHARE . $fld_ext]);
                }
                if (array_key_exists(sandbox::FLD_PROTECT . $fld_ext, $db_row)) {
                    $trp->set_protection_id($db_row[sandbox::FLD_PROTECT . $fld_ext]);
                }
                // not yet loaded with initial load
                // $trp->name = $db_row[triple_db::FLD_NAME_GIVEN . $fld_ext];
                // $trp->set_owner_id($db_row[_user_db::FLD_ID . $fld_ext]);
                // $trp->from->set_id($db_row[triple_db::FLD_FROM]);
                // $trp->to->set_id($db_row[triple_db::FLD_TO]);
                // $trp->verb->set_id($db_row[verb_db::FLD_ID]);
                $this->obj = $trp;
                $result = true;
            }
        }
        return $result;
    }

    /**
     * map a phrase api json to this model phrase object
     * @param array $api_json the api array with the phrase values that should be mapped
     */
    function api_mapper(array $api_json): user_message
    {
        $usr_msg = new user_message();

        if (!array_key_exists(json_fields::ID, $api_json)) {
            log_warning('Missing id in api_json');
        } else {
            if ($api_json[json_fields::ID] > 0) {
                $wrd = new word($this->user());
                $usr_msg->add($wrd->api_mapper($api_json));
                if ($usr_msg->is_ok()) {
                    $this->obj = $wrd;
                }
            } else {
                $trp = new triple($this->user());
                $api_json[json_fields::ID] = $api_json[json_fields::ID] * -1;
                $usr_msg->add($trp->api_mapper($api_json));
                if ($usr_msg->is_ok()) {
                    $this->obj = $trp;
                }
            }
        }
        return $usr_msg;
    }


    /*
     * set and get
     */

    function obj(): word|triple|IdObject|TextIdObject|null
    {
        return $this->obj;
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
        // TODO check if not set_id should be used
        $this->set_obj_id(abs($id));
    }

    function set_obj_from_id(int $id): void
    {
        if ($id > 0) {
            $wrd = new word($this->user());
            $wrd->set_id($id);
            $this->obj = $wrd;
        } elseif ($id < 0) {
            $trp = new triple($this->user());
            $trp->set_id($id * -1);
            $this->obj = $trp;
        } else {
            log_warning('id of a phrase is not expected to be zero');
        }
    }

    /**
     * set the phrase id based id the word or triple id
     * must have the same logic as the database view and the frontend
     *
     * @param int $id the object id that is converted to the phrase id
     * @param string $class the class of the phrase object
     * @return void
     */
    function set_id_from_obj(int $id, string $class): void
    {
        if ($class == word::class) {
            $this->obj = new word($this->user());
            $this->set_obj_id($id);
        } elseif ($class == triple::class) {
            $this->obj = new triple($this->user());
            $this->set_obj_id($id);
        }
        $this->obj()->set_id($id);
    }

    /**
     * create the expected object based on the given class
     * @param string $class the calling class name
     * @return void
     */
    private function set_obj_from_class(string $class): void
    {
        if ($class == word::class) {
            $this->obj = new word($this->user());
        } elseif ($class == triple::class) {
            $this->obj = new triple($this->user());
        } else {
            log_err('Unexpected class ' . $class . ' when creating phrase ' . $this->dsp_id());
        }
    }

    /**
     * set the name of the phrase object, which is also the name of the phrase
     *
     * @param string $name the name of the phrase set in the related object
     * @param string $class the class of the phrase object can be set to force the creation of the related object
     * @return void
     */
    function set_name(string $name, string $class = ''): void
    {
        if ($class != '' and $this->obj == null) {
            $this->set_obj_from_class($class);
        }
        $this->obj()->set_name($name);
    }

    /**
     * set the user of the phrase
     *
     * @param user $usr the person who wants to access the phrase
     * @return void
     */
    function set_user(user $usr): void
    {
        $this->obj()->set_user($usr);
    }

    /**
     * @return int the id of the user or 0 if the user is not set
     */
    function user_id(): int
    {
        return $this->obj()->user_id();
    }

    /**
     * @return int|null the id of the owner if the user is not set
     */
    function owner_id(): ?int
    {
        return $this->obj()->owner_id();
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
     * set the value to rank the words by usage
     *
     * @param int|null $usage a higher value moves the word to the top of the selection list
     * @return void
     */
    function set_usage(?int $usage): void
    {
        $this->obj()->set_usage($usage);
    }

    /**
     * @return int the id of the phrase witch is (corresponding to id_obj())
     * e.g 1 for a word, -1 for a triple
     */
    function id(): int
    {
        if ($this->is_word()) {
            return $this->obj_id();
        } else {
            return $this->obj_id() * -1;
        }
    }

    /**
     * @return int the id of the containing object
     * e.g. if the phrase id is  1 and the object is a word   with id 1 simply 1 is returned
     * but  if the phrase id is -1 and the object is a triple with id 1   also 1 is returned
     */
    function id_obj(): int
    {
        if ($this->obj == null) {
            return 0;
        } else {
            return $this->obj()->id();
        }
    }

    /**
     * @param bool $ignore_excluded force to include also the excluded names e.g. for import
     * @return string the name of the phrase
     */
    function name(bool $ignore_excluded = false): string
    {
        if ($this->obj == null) {
            return '';
        } else {
            return $this->obj()->name($ignore_excluded);
        }
    }

    /**
     * @return user the person who wants to see the phrase
     */
    function user(): user
    {
        return $this->obj()->user();
    }

    /**
     * @return int|null a higher number indicates a higher usage
     */
    function usage(): ?int
    {
        return $this->obj()->usage();
    }

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
     * modify
     */

    /**
     * fill this word or triple based on the given phrase
     *
     * @param phrase|db_object_seq_id $phr word with the values that should be updated e.g. based on the import
     * @param user $usr_req the user who has requested the fill
     * @return user_message a warning in case of a conflict e.g. due to a missing change time
     */
    function fill(phrase|db_object_seq_id $phr, user $usr_req): user_message
    {
        $usr_msg = new user_message();
        if ($this->is_word()) {
            if ($phr::class == phrase::class) {
                if ($phr->is_word()) {
                    $usr_msg->add($this->obj()->fill($phr->word(), $usr_req));
                } else {
                    $usr_msg->add_id_with_vars(msg_id::FILL_WORD_WITH_OTHER,
                        [
                            msg_id::VAR_WORD_NAME => $this->dsp_id(),
                            msg_id::VAR_NAME => $phr->dsp_id(),
                        ]);
                }
            } elseif ($phr::class == word::class) {
                $usr_msg->add($this->obj()->fill($phr, $usr_req));
            } else {
                $usr_msg->add_id_with_vars(msg_id::FILL_WORD_WITH_OTHER,
                    [
                        msg_id::VAR_WORD_NAME => $this->dsp_id(),
                        msg_id::VAR_NAME => $phr->dsp_id(),
                    ]);
            }
        } else {
            if ($phr::class == phrase::class) {
                if ($phr->is_triple()) {
                    $usr_msg->add($this->obj()->fill($phr->triple(), $usr_req));
                } else {
                    $usr_msg->add_id_with_vars(msg_id::FILL_TRIPLE_WITH_OTHER,
                        [
                            msg_id::VAR_TRIPLE_NAME => $this->dsp_id(),
                            msg_id::VAR_NAME => $phr->dsp_id(),
                        ]);
                }
            } elseif ($phr::class == triple::class) {
                $usr_msg->add($this->obj()->fill($phr, $usr_req));
            } else {
                $usr_msg->add_id_with_vars(msg_id::FILL_WORD_WITH_OTHER,
                    [
                        msg_id::VAR_TRIPLE_NAME => $this->dsp_id(),
                        msg_id::VAR_NAME => $phr->dsp_id(),
                    ]);
            }
        }
        return $usr_msg;
    }


    /*
     * cast
     */

    /**
     * @return word|IdObject|TextIdObject|null the word object or null
     */
    function word(): word|IdObject|TextIdObject|null
    {
        if ($this->is_word()) {
            return $this->obj();
        } else {
            return null;
        }
    }

    /**
     * @return triple|IdObject|TextIdObject|null the triple object or null
     */
    function triple(): triple|IdObject|TextIdObject|null
    {
        if ($this->is_word()) {
            return null;
        } else {
            return $this->obj();
        }
    }

    function term(): term
    {
        $trm = new term($this->user());
        if ($this->obj != null) {
            $trm->obj = $this->obj;
            $trm->set_id_from_obj($this->id_obj(), $this->obj::class);
        }
        return $trm;
    }


    /*
     * im- and export
     */

    function export_json(): array
    {
        return $this->obj()->export_json();
    }


    /*
     * load
     */

    /**
     * test if the name is used already via view table and just load the main parameters
     * @param string $name the name of the phrase and the related word, triple, formula or verb
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
     * load the main phrase parameters by id from the database phrase view
     * @param int $id the id of the phrase as defined in the database phrase view
     *                must be a negative id for triples
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
     * load a phrase from the database view
     * @param sql_par $qp the query parameters created by the calling function
     * @return int the id of the object found and zero if nothing is found
     */
    private
    function load(sql_par $qp): int
    {
        global $db_con;

        $db_row = $db_con->get1($qp);
        $this->row_mapper_sandbox($db_row);
        return $this->id();
    }

    /**
     * create an SQL statement to retrieve a phrase by name from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $name the name of the phrase and the related word, triple, formula or verb
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_name(sql_creator $sc, string $name): sql_par
    {
        $qp = $this->load_sql($sc, sql_db::FLD_NAME);
        $sc->add_where(phrase::FLD_NAME, $name);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a phrase by phrase id (not the object id) from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param int $id the id of the phrase as defined in the database phrase view
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_id(sql_creator $sc, int $id): sql_par
    {
        $qp = $this->load_sql($sc, sql_db::FLD_ID);
        $sc->add_where(phrase::FLD_ID, $id);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * create the common part of an SQL statement to retrieve a phrase from the database view
     * uses the phrase view which includes only the most relevant fields of words or triples
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name of the query use to prepare and call the query
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    private
    function load_sql(sql_creator $sc, string $query_name): sql_par
    {
        $qp = new sql_par(self::class);
        $qp->name .= $query_name;

        $sc->set_class(self::class);
        $sc->set_name($qp->name);

        $sc->set_fields(self::FLD_NAMES);
        $sc->set_usr_fields(self::FLD_NAMES_USR_EX);
        $sc->set_usr_num_fields(self::FLD_NAMES_NUM_USR);

        return $qp;
    }


    /*
     * sql fields
     */

    function name_field(): string
    {
        return self::FLD_NAME;
    }


    /*
     * load related
     */

    function load_obj(): bool
    {
        $result = 0;
        if ($this->is_triple()) {
            $trp = new triple($this->user());
            $result = $trp->load_by_id($this->obj_id());
            $this->obj = $trp;
            // TODO check: $this->set_name($trp->name()); // is this really useful? better save execution time and have longer code using ->obj()->name
            log_debug('triple ' . $this->dsp_id());
        } elseif ($this->is_word()) {
            $wrd = new word($this->user());
            $result = $wrd->load_by_id($this->obj_id());
            $this->obj = $wrd;
            $this->set_name($wrd->name());
            log_debug('word ' . $this->dsp_id());
        }
        if ($result != 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * get the main word object
     * e.g. ???
     * assumes that the phrase has already been loaded
     *
     * @return object|null
     */
    function main_word(): ?object
    {
        log_debug($this->dsp_id());
        $result = null;

        if ($this->id() != 0 and $this->name() == '') {
            $this->load_by_id($this->id());
        }
        if ($this->id() == 0 and $this->name() != '') {
            $this->load_by_name($this->name());
        }
        if ($this->id() < 0) {
            $lnk = $this->obj();
            $lnk->load_objects(); // try to be on the save side, and it is anyway checked if loading is really needed
            $result = $lnk->fob();
        } elseif ($this->id() > 0) {
            $result = $this->obj;
        } else {
            log_err('"' . $this->name() . '" has unknown type which is not expected for a phrase.', "phrase->main_word");
        }
        log_debug('done ' . $result->dsp_id());
        return $result;
    }

    /**
     * to enable the recursive function in work_link
     * TODO add a list of triple already split to detect endless loops
     */
    function wrd_lst(): word_list
    {
        $wrd_lst = new word_list($this->user());
        if ($this->is_triple()) {
            $trp = $this->obj();
            $sub_wrd_lst = $trp->wrd_lst();
            foreach ($sub_wrd_lst->lst() as $wrd) {
                $wrd_lst->add($wrd);
            }
        } else {
            $wrd = $this->obj();
            $wrd_lst->add($wrd);
        }
        return $wrd_lst;
    }

    /**
     * return either the word type id or the word link type id
     * e.g. 2020 can be a year but also any other identification number e.g. a valor number,
     * so if there is both in the database the type must be saved on the word link instead of the word
     */
    function type_id(): ?int
    {
        $result = null;
        $result = $this->obj()?->type_id();
        if ($result == null or $result == 0) {
            $wrd = $this->main_word();
            $result = $wrd->type_id;
        }

        log_debug('for ' . $this->dsp_id() . ' is ' . $result);
        return $result;
    }

    function type_code_id(): string
    {
        global $phr_typ_cac;
        return $phr_typ_cac->code_id($this->type_id());
    }

    /**
     * if there is just one formula linked to the phrase, get it
     * TODO separate the query parameter creation and add a unit test
     * TODO allow also to retrieve a list of formulas
     * TODO get the user specific list of formulas
     */
    function formula(): formula
    {
        global $db_con;

        $db_con->set_class(formula_link::class);
        $qp = new sql_par(self::class);
        $qp->name = 'phrase_formula_by_id';
        $db_con->set_name($qp->name);
        $db_con->set_link_fields(formula_db::FLD_ID, phrase::FLD_ID);
        $db_con->set_where_link_no_fld(0, 0, $this->id());
        $qp->sql = $db_con->select_by_set_id();
        $qp->par = $db_con->get_par();
        $db_row = $db_con->get1($qp);
        $frm = new formula($this->user());
        if ($db_row !== false) {
            if ($db_row[formula_db::FLD_ID] > 0) {
                $frm->load_by_id($db_row[formula_db::FLD_ID]);
            }
        }

        return $frm;
    }

    /*
     * data retrieval
     */

    /**
     * get a list of all values related to this phrase
     */
    function val_lst(): value_list
    {
        $lib = new library();
        log_debug('for ' . $this->dsp_id() . ' and user "' . $this->user()->name . '"');
        $val_lst = new value_list($this->user());
        $val_lst->load_by_phr($this);
        log_debug('got ' . $lib->dsp_count($val_lst->lst()));
        return $val_lst;
    }

    /**
     * get a list of verbs either pointing to or from this phrase
     * e.g. for Zurich and direction up the list contains at least the verb "is", because Zurich is a Canton is default triple
     *
     * @param foaf_direction $direction UP or DOWN to select the direction
     * @returns verb_list with all used verbs in the given direction
     */
    function vrb_lst(foaf_direction $direction): verb_list
    {
        global $db_con;
        $lib = new library();

        log_debug('for ' . $this->dsp_id());
        $vrb_lst = new verb_list($this->user());
        $vrb_lst->load_by_linked_phrases($db_con, $this, $direction);
        log_debug('got ' . $lib->dsp_count($vrb_lst->lst()));
        return $vrb_lst;
    }

    /**
     * @return phrase_list with all phrases where this phrase is used
     */
    function all_parents(): phrase_list
    {
        $phr_lst = new phrase_list($this->user());
        $phr_lst->add($this);
        return $phr_lst->foaf_parents();
    }

    /**
     * @return phrase_list with all phrases "below" this phrase
     */
    function all_children(): phrase_list
    {
        log_debug($this->dsp_id());
        $phr_lst = new phrase_list($this->user());
        $phr_lst->add($this);
        return $phr_lst->foaf_children();
    }

    /**
     * @return phrase_list with all related phrases of this phrase
     */
    function all_related(): phrase_list
    {
        $phr_lst = new phrase_list($this->user());
        $phr_lst->add($this);
        return $phr_lst->foaf_related();
    }

    function groups(): group_list
    {
        $lst = new group_list($this->user());
        $lst->load_by_phr($this);
        return $lst;
    }

    /**
     * helper function that returns a phrase list object just with this phrase object
     * @return phrase_list
     */
    function lst(): phrase_list
    {
        $phr_lst = new phrase_list($this->user());
        $phr_lst->add($this);
        return $phr_lst;
    }

    /**
     * helper function that returns the direct children of this phrase without this phrase
     * @return phrase_list
     */
    function direct_children(): phrase_list
    {
        return $this->lst()->direct_children();
    }


    /**
     * returns a list of phrase that are related to this word e.g. for "ABB" it will return "company" (but not "ABB"???)
     */
    function is(): phrase_list
    {
        $this_lst = $this->lst();
        $phr_lst = $this_lst->is();
        // in case of a triple use at least the initial parent phrase,
        if ($this->is_triple()) {
            $phr_lst->add($this->obj()->to());
        }
        //$phr_lst->add($this,);
        log_debug($this->dsp_id() . ' is a ' . $phr_lst->dsp_name());
        return $phr_lst;
    }


    /*
     * classification
     */

    /**
     * @return bool true if this phrase is a word or supposed to be a word
     */
    function is_word(): bool
    {
        $result = false;
        if ($this->obj() !== null) {
            if ($this->obj()::class == word::class) {
                $result = true;
            }
        } else {
            if ($this->id() > 0) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * @return bool true if this phrase is a triple or supposed to be a triple
     */
    function is_triple(): bool
    {
        $result = false;
        if (isset($this->obj)) {
            if (get_class($this->obj) == triple::class) {
                $result = true;
            }
        } else {
            if ($this->id() < 0) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * @return bool true if this phrase is a formula or supposed to be a formula
     */
    private
    function is_formula(): bool
    {
        $result = false;
        if (isset($this->obj)) {
            if (get_class($this->obj) == formula::class) {
                $result = true;
            }
        } else {
            if ($this->id() < 0) {
                $result = true;
            }
        }
        return $result;
    }


    /*
     * info
     */

    /**
     * check if the word or triple in the database needs to be updated
     * e.g. for import if this word has only the name set, the protection should not be updated in the database
     *
     * @param phrase $db_phr the word or triple as saved in the database
     * @return bool true if this word has infos that should be saved in the database
     */
    function needs_db_update(phrase $db_phr): bool
    {
        if ($this->is_word() and $db_phr->is_word()) {
            $wrd = $this->obj();
            $db_wrd = $db_phr->obj();
            return $wrd->needs_db_update($db_wrd);
        } elseif ($this->is_triple() and $db_phr->is_triple()) {
            $trp = $this->obj();
            $db_trp = $db_phr->obj();
            return $trp->needs_db_update($db_trp);
        } else {
            return true;
        }
    }

    /**
     * @param string $type the ENUM string of the fixed type
     * @returns bool true if the word has the given type
     */
    function is_type(string $type): bool
    {
        if ($this->is_word()) {
            $wrd = $this->obj();
            return $wrd->is_type($type);
        } elseif ($this->is_triple()) {
            $trp = $this->obj();
            return $trp->is_type($type);
        } else {
            return false;
        }
    }

    function no_id_but_name(): bool
    {
        if ($this->is_word()) {
            $wrd = $this->obj();
            return $wrd->no_id_but_name();
        } elseif ($this->is_triple()) {
            $trp = $this->obj();
            return $trp->no_id_but_name();
        } else {
            return false;
        }
    }

    function is_excluded(): bool
    {
        if ($this->is_word()) {
            $wrd = $this->obj();
            return $wrd->is_excluded();
        } elseif ($this->is_triple()) {
            $trp = $this->obj();
            return $trp->is_excluded();
        } else {
            return false;
        }
    }


    public static function cmp($a, $b): string
    {
        return strcmp($a->name(), $b->name());
    }

    // returns a list of words that are related to this word e.g. for "ABB" it will return "company" (but not "ABB"???)
    /*  function is () {
        if ($this->id() > 0) {
          $wrd_lst = $this->parents();
        } else {
        }

        zu_debug('phrase->is -> '.$this->dsp_id().' is a '.$wrd_lst->name());
        return $wrd_lst;
      } */

    // true if the word id has an "is a" relation to the related word
    // e.g.for the given word string
    function is_a($related_phrase): bool
    {
        log_debug($this->dsp_id() . ',' . $related_phrase->name);

        $result = false;
        $is_phrases = $this->is(); // should be taken from the original array to increase speed
        if (in_array($related_phrase->id, $is_phrases->id_lst())) {
            $result = true;
        }

        log_debug(zu_dsp_bool($result) . $this->id());
        return $result;
    }

    // TODO deprecate and replace by phase list functions
    // SQL to list the user phrases (related to a type if needed)
    function sql_list($type): string
    {
        log_debug();
        global $db_con;
        global $vrb_cac;

        $sql_type_from = '';
        $sql_type_where = '';

        // if no phrase type is define, list all words and triples
        // TODO: but if word has several types don't offer to the user to select the simple word
        //                                                      ^
        $sql_words = 'SELECT DISTINCT w.word_id AS id, 
                             ' . $db_con->get_usr_field("word_name", "w", "u", sql_db::FLD_FORMAT_TEXT, "name") . ',
                             ' . $db_con->get_usr_field("excluded", "w", "u", sql_db::FLD_FORMAT_BOOL) . '
                        FROM words w   
                   LEFT JOIN user_words u ON u.word_id = w.word_id 
                                         AND u.user_id = ' . $this->user()->id() . ' ';
        $sql_triples = 'SELECT DISTINCT l.triple_id * -1 AS id, 
                               ' . $db_con->get_usr_field("name_given", "l", "u", sql_db::FLD_FORMAT_TEXT, "name") . ',
                               ' . $db_con->get_usr_field("excluded", "l", "u", sql_db::FLD_FORMAT_BOOL) . '
                          FROM triples l
                     LEFT JOIN user_triples u ON u.triple_id = l.triple_id 
                                                AND u.user_id = ' . $this->user()->id() . ' ';

        if (isset($type)) {
            if ($type->id() > 0) {

                // select all phrase ids of the given type e.g. ABB, DANONE, Zurich
                $sql_where_exclude = 'excluded = 0';
                $sql_field_names = 'id, name, excluded';
                $sql_wrd_all = 'SELECT from_phrase_id AS id FROM (
                                        SELECT DISTINCT
                                               l.from_phrase_id,    
                                               ' . $db_con->get_usr_field("excluded", "l", "u", sql_db::FLD_FORMAT_BOOL) . '
                                          FROM triples l
                                     LEFT JOIN user_triples u ON u.triple_id = l.triple_id 
                                                                AND u.user_id = ' . $this->user()->id() . '
                                         WHERE l.to_phrase_id = ' . $type->id() . ' 
                                           AND l.verb_id = ' . $vrb_cac->id(verbs::IS) . ' ) AS a 
                                         WHERE ' . $sql_where_exclude . ' ';

                // ... out of all those get the phrase ids that have also other types e.g. Zurich (Canton)
                $sql_wrd_other = 'SELECT from_phrase_id FROM (
                                        SELECT DISTINCT
                                               l.from_phrase_id,    
                                               ' . $db_con->get_usr_field("excluded", "l", "u", sql_db::FLD_FORMAT_BOOL) . '
                                          FROM triples l
                                     LEFT JOIN user_triples u ON u.triple_id = l.triple_id 
                                                                AND u.user_id = ' . $this->user()->id() . '
                                         WHERE l.to_phrase_id <> ' . $type->id() . ' 
                                           AND l.verb_id = ' . $vrb_cac->id(verbs::IS) . '
                                           AND l.from_phrase_id IN (' . $sql_wrd_all . ') ) AS o 
                                         WHERE ' . $sql_where_exclude . ' ';

                // if a word has no other type, use the word
                $sql_words = 'SELECT DISTINCT ' . $sql_field_names . ' FROM (
                      SELECT DISTINCT
                             w.word_id AS id, 
                             ' . $db_con->get_usr_field("word_name", "w", "u", sql_db::FLD_FORMAT_TEXT, "name") . ',
                             ' . $db_con->get_usr_field("excluded", "w", "u", sql_db::FLD_FORMAT_BOOL) . '
                        FROM ( ' . $sql_wrd_all . ' ) a, words w
                   LEFT JOIN user_words u ON u.word_id = w.word_id 
                                         AND u.user_id = ' . $this->user()->id() . '
                       WHERE w.word_id NOT IN ( ' . $sql_wrd_other . ' )                                        
                         AND w.word_id = a.id ) AS w 
                       WHERE ' . $sql_where_exclude . ' ';

                // if a word has another type, use the triple
                $sql_triples = 'SELECT DISTINCT ' . $sql_field_names . ' FROM (
                        SELECT DISTINCT
                               l.triple_id * -1 AS id, 
                               ' . $db_con->get_usr_field("name_given", "l", "u", sql_db::FLD_FORMAT_TEXT, "name") . ',
                               ' . $db_con->get_usr_field("excluded", "l", "u", sql_db::FLD_FORMAT_BOOL) . '
                          FROM triples l
                     LEFT JOIN user_triples u ON u.triple_id = l.triple_id 
                                                AND u.user_id = ' . $this->user()->id() . '
                         WHERE l.from_phrase_id IN ( ' . $sql_wrd_other . ')                                        
                           AND l.verb_id = ' . $vrb_cac->id(verbs::IS) . '
                           AND l.to_phrase_id = ' . $type->id() . ' ) AS t 
                         WHERE ' . $sql_where_exclude . ' ';
                /*
                $sql_type_from = ', triples t LEFT JOIN user_triples ut ON ut.triple_id = t.triple_id
                                                                             AND ut.user_id = '.$this->user()->id.'';
                $sql_type_where_words   = 'WHERE w.word_id = t.from_phrase_id
                                             AND t.verb_id = '.cl(SQL_LINK_TYPE_IS).'
                                             AND t.to_phrase_id = '.$type->id.' ';
                $sql_type_where_triples = 'WHERE l.to_phrase_id = t.from_phrase_id
                                             AND t.verb_id = '.cl(SQL_LINK_TYPE_IS).'
                                             AND t.to_phrase_id = '.$type->id.' ';
                $sql_words   = 'SELECT w.word_id AS id,
                                      IF(u.word_name IS NULL, w.word_name, u.word_name) AS name,
                                      IF(u.excluded IS NULL, COALESCE(w.excluded, 0), COALESCE(u.excluded, 0)) AS excluded
                                  FROM words w
                            LEFT JOIN user_words u ON u.word_id = w.word_id
                                                  AND u.user_id = '.$this->user()->id.'
                                      '.$sql_type_from.'
                                      '.$sql_type_where_words.'
                              GROUP BY name';
                $sql_triples = 'SELECT l.triple_id * -1 AS id,
                                      IF(u.name IS NULL, l.name, u.name) AS name,
                                      IF(u.excluded IS NULL, COALESCE(l.excluded, 0), COALESCE(u.excluded, 0)) AS excluded
                                  FROM triples l
                            LEFT JOIN user_triples u ON u.triple_id = l.triple_id
                                                        AND u.user_id = '.$this->user()->id.'
                                      '.$sql_type_from.'
                                      '.$sql_type_where_triples.'
                              'GROUP BY name';
                              */
            }
        }
        $sql_avoid_code_check_prefix = "SELECT";
        $sql = $sql_avoid_code_check_prefix . ' DISTINCT id, name
              FROM ( ' . $sql_words . ' ' . sql::UNION . ' ' . $sql_triples . ' ) AS p
             WHERE excluded = 0
          ORDER BY p.name;';
        log_debug($sql);
        return $sql;
    }


    /*
     * display functions
     */


    // returns the best guess category for a word  e.g. for "ABB" it will return only "company"
    function is_mainly()
    {
        $result = null;
        $is_wrd_lst = $this->is();
        if (!$is_wrd_lst->is_empty()) {
            $result = $is_wrd_lst->lst()[0];
            log_debug($this->dsp_id() . ' is a ' . $result->name());
        }
        return $result;
    }

    /*
     * forwards
     */

    function is_time(): bool
    {
        return $this->obj()->is_time();
    }

    /**
     * @return bool true if the word has the type "measure" (e.g. "meter" or "CHF")
     * in case of a division, these words are excluded from the result
     * in case of add, it is checked that the added value does not have a different measure
     */
    function is_measure(): bool
    {
        $wrd = $this->main_word();
        return $wrd->is_measure();
    }

// return true if the word has the type "scaling" (e.g. "million", "million" or "one"; "one" is a hidden scaling type)
    function is_scaling()
    {
        $wrd = $this->main_word();
        return $wrd->is_scaling();
    }

    /**
     * @returns true if the phrase type is set to "scaling_percent" (e.g. "percent")
     */
    function is_percent(): bool
    {
        global $phr_typ_cac;

        $result = false;
        if ($this->obj != null) {
            if ($this->obj()->type_id == $phr_typ_cac->id(phrase_type_shared::PERCENT)) {
                $result = true;
            }
        } else {
            $wrd = $this->main_word();
            $result = $wrd->is_percent();
        }
        return $result;
    }

    /**
     * @return phrase the following phrase based on the predefined verb following
     * e.g. the year 2020 if the given year phrase is 2019
     * TODO add to triple and review
     * TODO create unit tests
     */
    function next(): phrase
    {
        log_debug($this->dsp_id());

        global $db_con;
        global $vrb_cac;

        $result = new phrase($this->user());

        $link_id = $vrb_cac->id(verbs::FOLLOW);
        //$link_id = cl(db_cl::VERB, verbs::FOLLOW);
        //$db_con = new mysql;
        $db_con->usr_id = $this->user()->id();
        $db_con->set_class(triple::class);
        $key_result = $db_con->get_value_2key(triple_db::FLD_FROM, triple_db::FLD_TO, $this->id(), verb_db::FLD_ID, $link_id);
        if (is_numeric($key_result)) {
            $id = intval($key_result);
            if ($id > 0) {
                $result->load_by_id($id);
            }
        }
        return $result;
    }

    /**
     * return the follow word id based on the predefined verb following
     * TODO add to triple and review
     * TODO create unit tests
     */
    function prior(): word
    {
        log_debug($this->dsp_id());

        global $db_con;
        global $vrb_cac;

        $result = new word($this->user());

        $link_id = $vrb_cac->id(verbs::FOLLOW);
        //$link_id = cl(db_cl::VERB, verbs::FOLLOW);
        //$db_con = new mysql;
        $db_con->usr_id = $this->user()->id();
        $db_con->set_class(triple::class);
        $key_result = $db_con->get_value_2key(triple_db::FLD_TO, triple_db::FLD_FROM, $this->id(), verb_db::FLD_ID, $link_id);
        if (is_numeric($key_result)) {
            $id = intval($key_result);
            if ($id > 0) {
                $result->load_by_id($id);
            }
        }
        return $result;
    }


    /*
     * save
     */

    /**
     * @return user_message
     */
    function save(): user_message
    {
        global $phr_typ_cac;

        $usr_msg = new user_message();

        /*
        if (isset($this->obj)) {
            $usr_msg = $this->obj()->save();
        }
        */

        // try if the word exists
        $wrd = new word($this->user());
        $wrd->load_by_name($this->name());
        if ($wrd->id() > 0) {
            $this->set_obj_id($wrd->id());
        } else {
            // try if the triple exists
            $trp = new triple($this->user());
            $trp->load_by_name($this->name());
            if ($trp->id() > 0) {
                $this->set_obj_id($trp->id());
            } else {
                // create a word if neither the word nor the triple exists
                $wrd = new word($this->user());
                $wrd->set_name($this->name());
                $wrd->type_id = $phr_typ_cac->default_id();
                $usr_msg->add($wrd->save());
                if ($wrd->id() == 0) {
                    log_err('Cannot add from word ' . $this->dsp_id(), 'phrase->save');
                } else {
                    $this->set_obj_id($wrd->id());
                }
            }
        }

        return $usr_msg;
    }

    /**
     * delete either a word or triple
     * @return user_message an empty string if deleting has been successful
     */
    function del(): user_message
    {
        log_debug($this->dsp_id());
        $usr_msg = new user_message();

        // direct delete if the object is loaded
        if ($this->is_triple()) {
            $lnk = $this->obj;
            if ($lnk != null) {
                $usr_msg->add($lnk->del());
            }
        } elseif ($this->is_word()) {
            $wrd = $this->obj;
            if ($wrd != null) {
                $usr_msg->add($wrd->del());
            }
        } else {
            log_err('Unknown object type of ' . $this->dsp_id());
        }
        return $usr_msg;
    }

    /**
     * @param string $name the name of the phrase
     * @return user_message if something fails the explanation for the user what has happened
     *                      and the possible solutions with a suggestion
     */
    function get_or_add(string $name): user_message
    {
        // init the result
        $usr_msg = new user_message();
        // load the word or triple if it exists
        $this->load_by_name($name);
        if ($this->id() == 0) {
            // add a simple word if it does not yet exist
            $wrd = new word($this->user());
            $wrd->set_name($name);
            $usr_msg->add($wrd->save());
        }
        return $usr_msg;
    }


    /*
     * debug
     */

    /**
     * @param bool $full false if a short version e.g. for lists should be returned
     * @return string the unique id fields
     */
    function dsp_id(bool $full = true): string
    {
        if ($this->obj() != null) {
            return $this->obj()->dsp_id($full) . ' as phrase';
        } else {
            return 'phrase with null object';
        }
    }

    /*
     * display functions
     */

    // return the name (just because all objects should have a name function)
    function dsp_name(): string
    {
        //$result = $this->name();
        return '"' . $this->name() . '"';
    }

    function name_linked(): string
    {
        return '<a href="/http/view.php?words=' . $this->id() . '" title="' . $this->obj()->description . '">' . $this->name() . '</a>';
    }

    /**
     * get the related phrases
     * @param foaf_direction $direction up to select the parent phrases and dow for the children
     * @param verb_list|null $link_types to filter predicates on database level
     * @return phrase_list with the related phrases
     */
    function phrases(foaf_direction $direction, ?verb_list $link_types = null): phrase_list
    {
        $phr_lst = new phrase_list($this->user());
        if ($link_types == null) {
            $link_types = $this->vrb_lst($direction);
        }
        if ($link_types != null) {
            foreach ($link_types->lst() as $vrb) {
                $add_lst = new phrase_list($this->user());
                $add_lst->load_by_phr($this, $vrb, $direction);
                $phr_lst->merge($add_lst);
            }
        }
        return $phr_lst;
    }

    /**
     * return the html code to display a word
     */
    function display(): string
    {
        return '<a href="/http/view.php?words=' . $this->id() . '">' . $this->name() . '</a>';
    }

    /**
     * simply to display a single word or triple link
     */
    function display_linked(): string
    {
        return '<a href="/http/view.php?words=' . $this->id() . '" title="' . $this->obj()->description . '">' . $this->name() . '</a>';
    }

    /**
     * similar to dsp_link
     *
     * @param $style
     * @return string
     */
    function dsp_link_style($style): string
    {
        return '<a href="/http/view.php?words=' . $this->id() . '" title="' . $this->obj()->description . '" class="' . $style . '">' . $this->name() . '</a>';
    }

    // create a selector that contains the time words
    // e.g. Q1 can be the first Quarter of a year and in this case the four quarters of a year should be the default selection
    //      if this is the triple "Q1 of 2018" a list of triples of this year should be the default selection
    //      if Q1 is a wikidata qualifier a general time selector should be shown
    function dsp_time_selector($type, $form_name, $pos, $back)
    {

        $wrd = $this->main_word();
        return $wrd->dsp_time_selector($type, $form_name, $pos, $back);
    }

}