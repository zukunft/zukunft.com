<?php

/*

    model/db/sql.php - all sql language const used in all database dialects
    ----------------

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

    Copyright (c) 1995-2018 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace cfg\db;

class sql
{

    // common SQL const that must exist in all used sql dialects
    // or the say it another way: used SQL elements that are the same in all used dialects
    const SELECT = 'SELECT';
    const INSERT = 'INSERT';
    const UPDATE = 'UPDATE';
    const DELETE = 'DELETE FROM';
    const NOW = 'Now()';
    const ORDER_ASC = 'ASC';
    const ORDER_DESC = 'DESC';
    const NULL_VALUE = 'NULL';
    const INDEX = 'INDEX';
    const UNIQUE = 'UNIQUE INDEX';  // TODO check if UNIQUE needs to be used for word and triple names
    const PREPARE = 'PREPARE';
    const CREATE = 'CREATE OR REPLACE';
    const DROP_MYSQL = 'DROP PROCEDURE IF EXISTS';
    const TRUNCATE = 'TRUNCATE';
    const CASCADE = 'CASCADE';
    const FUNCTION = 'FUNCTION';
    const FUNCTION_MYSQL = 'CREATE PROCEDURE';
    const FUNCTION_NAME = '$$';
    const FUNCTION_DECLARE = 'DECLARE';
    const FUNCTION_BEGIN = 'BEGIN';
    const FUNCTION_BEGIN_MYSQL = 'BEGIN';
    const INTO = 'INTO';
    const FUNCTION_END = 'END $$ LANGUAGE plpgsql';
    const FUNCTION_END_MYSQL = 'END';
    const FUNCTION_RETURN_INT = 'RETURNS bigint AS';
    const FUNCTION_RETURN_INT_MYSQL = '';
    const FUNCTION_NO_RETURN = 'RETURNS void AS';
    const FUNCTION_NO_RETURN_MYSQL = '';
    const RETURNING = 'RETURNING';
    const RETURN = 'RETURN';
    const VIEW = 'VIEW';
    const AS = 'AS';
    const FROM = 'FROM';
    const WHERE = 'WHERE';
    const AND = 'AND';
    const OR = 'OR';
    const CONCAT = 'CONCAT';
    const CASE = 'CASE WHEN';
    const CASE_MYSQL = 'IF(';
    const THEN = 'THEN';
    const THEN_MYSQL = ',';
    const IS_NULL = 'IS NULL';
    const ELSE = 'ELSE';
    const ELSE_MYSQL = ',';
    const END = 'END';
    const END_MYSQL = ')';
    const UNION = 'UNION';
    const IN = 'IN';
    const SEP = ';';
    const TBL_SEP = '.';
    const WITH = 'WITH';

    const LAST_ID_MYSQL = 'SELECT LAST_INSERT_ID() AS ';
    const TRUE = '1'; // representing true in the where part for a smallint field
    const FALSE = '0'; // representing true in the where part for a smallint field
    const ID_NULL = 0; // the 'not set' value for an id; could have been null if postgres index would allow it

    // sql field names used for several classes
    const FLD_CODE_ID = 'code_id';     // field name for the code link e.g. for words used for the system configuration
    const FLD_CODE_ID_SQL_TYP = sql_field_type::CODE_ID;
    const FLD_VALUE = 'value';         // field name e.g. for the configuration value
    const FLD_TYPE_NAME = 'type_name'; // field name for the user specific name of a type; types are used to assign code to a db row
    const FLD_CONST = 'const'; // for the view creation to indicate that the field name as a const

    // query name extensions to make the query name unique
    const NAME_ALL = 'all'; // for queries that should return all rows without paging
    const NAME_SEP = '_'; // for snake case query and file names
    const NAME_BY = 'by'; // to separate the query selection in the query name e.g. for (load) word_by_name
    const NAME_EXT_USER = '_user';
    const NAME_EXT_MEDIAN_USER = 'median_user'; // to get the user that is owner of the most often used db row
    const NAME_EXT_EX_OWNER = 'ex_owner'; // excluding the owner of the loaded db row
    const NAME_EXT_USER_CONFIG = 'usr_cfg';

    // for sql functions that do the change log and the actual change with on function
    const FLD_LOG_FIELD_PREFIX = 'field_id_'; // added to the field name to include the preloaded log field id
    const FLD_LOG_ID_PREFIX = 'id_'; // added to the field name to include the actual id changed in the log e.g. for type changes
    const PAR_PREFIX = '_'; // to separate the parameter names e.g. _word_id instead of word_id for the given parameter
    const PAR_PREFIX_MYSQL = '@'; // for the new sequence id using MySQL
    const PAR_NEW_ID_PREFIX = 'new_'; // added to the field id name to remember the sequence id

    // query field prefixes to make the field name unique
    const FROM_FLD_PREFIX = 'from_';
    const TO_FLD_PREFIX = 'to_';

    // enum values used for the table creation
    const fld_type_ = '';

    // sql const used for this sql statement creator
    const MAX_PREFIX = 'max_';

    // postgres parameter types for prepared queries
    const PG_PAR_INT = 'bigint';
    const PG_PAR_LIST = '[]';
    const PG_PAR_INT_SMALL = 'smallint';
    const PG_PAR_TEXT = 'text';
    const PG_PAR_FLOAT = 'numeric';

    // placeholder for the class name in table or field comments
    const COMMENT_CLASS_NAME = '-=class=-';

}
