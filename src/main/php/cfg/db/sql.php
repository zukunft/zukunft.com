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

namespace Zukunft\ZukunftCom\main\php\cfg\db;

class sql
{

    // common SQL const that must exist in all used sql dialects
    // or the say it another way: used SQL elements that are the same in all used dialects

    // start of statements
    const string SELECT = 'SELECT';
    const string INSERT = 'INSERT';
    const string UPDATE = 'UPDATE';
    const string DELETE = 'DELETE FROM';
    const string PREPARE = 'PREPARE';

    // setup roles
    const string ROLE_CREATE = 'CREATE ROLE';
    const string ROLE_WITH = 'WITH LOGIN CREATEDB NOSUPERUSER NOCREATEROLE INHERIT NOREPLICATION CONNECTION LIMIT -1';
    const string ROLE_WITH_MYSQL = 'WITH LOGIN CREATEDB NOSUPERUSER NOCREATEROLE INHERIT NOREPLICATION CONNECTION LIMIT -1';
    const string PASSWORD = "PASSWORD '";
    const string PASSWORD_END = "'";

    // setup tables
    const string CREATE = 'CREATE OR REPLACE';
    const string INDEX = 'INDEX';
    const string UNIQUE = 'UNIQUE INDEX';  // TODO check if UNIQUE needs to be used for word and triple names

    // functions
    const string NOW = 'Now()';

    // logic
    const string AND = 'AND';
    const string OR = 'OR';

    // condition
    const string WHERE = 'WHERE';

    // sorting
    const string ORDER_BY = 'ORDER BY';
    const string ORDER_ASC = 'ASC';
    const string ORDER_DESC = 'DESC';

    // not yet checked in sql_creator
    const string NULL_VALUE = 'NULL';
    const string DROP_MYSQL = 'DROP PROCEDURE IF EXISTS';
    const string TRUNCATE = 'TRUNCATE';
    const string CASCADE = 'CASCADE';
    const string FUNCTION = 'FUNCTION';
    const string FUNCTION_MYSQL = 'CREATE PROCEDURE';
    const string FUNCTION_NAME = '$$';
    const string FUNCTION_DECLARE = 'DECLARE';
    const string FUNCTION_BEGIN = 'BEGIN';
    const string FUNCTION_BEGIN_MYSQL = 'BEGIN';
    const string INTO = 'INTO';
    const string FUNCTION_END = 'END $$ LANGUAGE plpgsql';
    const string FUNCTION_END_MYSQL = 'END';
    const string FUNCTION_RETURN_INT = 'RETURNS bigint AS';
    const string FUNCTION_RETURN_INT_MYSQL = '';
    const string FUNCTION_NO_RETURN = 'RETURNS void AS';
    const string FUNCTION_COUNT = 'COUNT';
    const string FUNCTION_NO_RETURN_MYSQL = '';
    const string RETURNING = 'RETURNING';
    const string RETURN = 'RETURN';
    const string VIEW = 'VIEW';
    const string AS = 'AS';
    const string FROM = 'FROM';
    const string LEFT_JOIN = 'LEFT JOIN';
    const string ON = 'ON';
    const string CONCAT = 'CONCAT';
    const string CASE = 'CASE WHEN';
    const string CASE_MYSQL = 'IF(';
    const string THEN = 'THEN';
    const string THEN_MYSQL = ',';
    const string IS_NULL = 'IS NULL';
    const string NULL = 'NULL';
    const string NOT_TRUE = 'IS NOT TRUE';
    const string ELSE = 'ELSE';
    const string ELSE_MYSQL = ',';
    const string END = 'END';
    const string END_MYSQL = ')';
    const string UNION = 'UNION';
    const string UNION_ALL = 'UNION ALL';
    const string IN = 'IN';
    const string COALESCE = 'COALESCE';

    // to separate one SQL statement from the next
    const string SEP = ';';

    const string TBL_SEP = '.';
    const string WITH = 'WITH';
    const string PRIMARY_KEY = 'PRIMARY KEY';

    const string LAST_ID_MYSQL = 'SELECT LAST_INSERT_ID() AS ';
    const string TRUE = '1'; // representing true in the where part for a smallint field
    const string FALSE = '0'; // representing true in the where part for a smallint field
    const int ID_NULL = 0; // the 'not set' value for an id; could have been null if postgres index would allow it

    // query name extensions to make the query name unique
    const string NAME_ALL = 'all'; // for queries that should return all rows without paging
    const string NAME_SEP = '_'; // for snake case query and file names
    const string NAME_PHRASE_COUNT = 'r'; // the number of phrase ids used to select the values
    const string NAME_BY = 'by'; // to separate the query selection in the query name e.g. for (load) word_by_name
    const string NAME_EXT_USER = '_user';
    const string NAME_EXT_COUNT = '_count';
    const string NAME_EXT_MEDIAN_USER = 'median_user'; // to get the user that is owner of the most often used db row
    const string NAME_EXT_EX_OWNER = 'ex_owner'; // excluding the owner of the loaded db row
    const string NAME_EXT_USER_CONFIG = 'usr_cfg';

    // for sql functions that do the change log and the actual change with on function
    const string FLD_LOG_FIELD_PREFIX = 'field_id_'; // added to the field name to include the preloaded log field id
    const string FLD_LOG_ID_PREFIX = 'id_'; // added to the field name to include the actual id changed in the log e.g. for type changes
    const string FLD_LOG_REQ_USER = 'req_user_id'; // the sql parameter name for the user id of the user who has requested the change
    const string PAR_PREFIX = '_'; // to separate the parameter names e.g. _word_id instead of word_id for the given parameter
    const string PAR_PREFIX_MYSQL = '@'; // for the new sequence id using MySQL
    const string PAR_NEW_ID_PREFIX = 'new_'; // added to the field id name to remember the sequence id

    // query field prefixes to make the field name unique
    const string FROM_FLD_PREFIX = 'from_';
    const string TO_FLD_PREFIX = 'to_';

    // enum values used for the table creation
    const string fld_type_ = '';

    // sql const string used for this sql statement creator
    const string MAX_PREFIX = 'max_';

    // postgres parameter types for prepared queries
    const string PG_PAR_INT = 'bigint';
    const string PG_PAR_LIST = '[]';
    const string PG_PAR_INT_SMALL = 'smallint';
    const string PG_PAR_TEXT = 'text';
    const string PG_PAR_FLOAT = 'numeric';

    // placeholder for the class name in table or field comments
    const string COMMENT_CLASS_NAME = '-=class=-';

}
