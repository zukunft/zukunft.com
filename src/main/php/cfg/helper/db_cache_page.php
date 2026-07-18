<?php

/*

    model/helper/db_cache_page.php - the cached html pages of view-only requests keyed by the url
    ------------------------------

    The main sections of this object are
    - db const:          const for the database link
    - object vars:       the variables of this cached page object
    - construct and map: set the vars of this object to the initial value or based on a db row
    - load:              database access object (DAO) functions
    - save:              add or replace a cached html page
    - api:               create an api array for the frontend
    - sql write fields:  field list for writing to the database
    - sql fields:        helper for the sql field names
    - db helper:         checks before writing to the database
    - debug:             internal support functions for debugging


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

    Copyright (c) 1995-2026 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\cfg\helper;

use DateTime;
use DateTimeInterface;
use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_HELPER . 'db_object_seq_id.php';
include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_field_default.php';
include_once paths::DB . 'sql_field_type.php';
include_once paths::DB . 'sql_par.php';
include_once paths::DB . 'sql_par_field_list.php';
include_once paths::DB . 'sql_type_list.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'library.php';
include_once paths::SHARED_CONST_FIELDS . 'fields.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_default;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par_field_list;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\const\fields\fields;

class db_cache_page extends db_object_seq_id
{

    /*
     * db const
     */

    // database object field names and comments
    const string TBL_COMMENT = 'cached html pages of view-only requests keyed by the url for faster response times';
    const string FLD_ID = 'db_cache_page_id';
    const string FLD_URL_COM = 'the request url that the cached html page belongs to';
    const string FLD_URL = 'url';
    const string FLD_HTML_PAGE_COM = 'the pre-rendered html page returned for the url';
    const string FLD_HTML_PAGE = 'html_page';
    const string FLD_LAST_UPDATE_COM = 'timestamp of the last rendering of the cached html page';

    // all database field names excluding the id
    const array FLD_NAMES = array(
        self::FLD_URL,
        self::FLD_HTML_PAGE,
        fields::FLD_LAST_UPDATE
    );

    // field lists for the table creation
    const array FLD_LST_ALL = array(
        [self::FLD_URL, sql_field_type::TEXT, sql_field_default::NOT_NULL, sql::INDEX, '', self::FLD_URL_COM],
        [self::FLD_HTML_PAGE, sql_field_type::TEXT, sql_field_default::NULL, '', '', self::FLD_HTML_PAGE_COM],
        [fields::FLD_LAST_UPDATE, sql_field_type::TIME, sql_field_default::TIME_NOT_NULL, sql::INDEX, '', self::FLD_LAST_UPDATE_COM],
    );


    /*
     * object vars
     */

    // database fields
    public ?string $url = null;            // the request url that the cached html page belongs to
    public ?string $html_page = null;      // the pre-rendered html page returned for the url
    public ?DateTime $last_update = null;  // time when the cached html page has last been rendered


    /*
     * construct and map
     */

    /**
     * clear all cache page object values e.g. to detect the changed fields
     * @param bool $keep_user set to true to keep the original user
     * @return void
     */
    function reset(bool $keep_user = false): void
    {
        parent::reset($keep_user);
        $this->url = null;
        $this->html_page = null;
        $this->last_update = new DateTime();
    }

    /**
     * map the database fields to this cached html page object
     *
     * @param array|null $db_row with the data directly from the database
     * @param string $id_fld the name of the id field as set in the child class
     * @return bool true if a cached html page is found
     */
    function row_mapper(?array $db_row, string $id_fld = ''): bool
    {
        $lib = new library();
        $result = parent::row_mapper($db_row, self::FLD_ID);
        if ($result) {
            if (array_key_exists(self::FLD_URL, $db_row)) {
                $this->url = $db_row[self::FLD_URL];
            }
            if (array_key_exists(self::FLD_HTML_PAGE, $db_row)) {
                $this->html_page = $db_row[self::FLD_HTML_PAGE];
            }
            if (array_key_exists(fields::FLD_LAST_UPDATE, $db_row)) {
                $this->last_update = $lib->get_datetime($db_row[fields::FLD_LAST_UPDATE], $this->dsp_id());
            }
        }
        return $result;
    }


    /*
     * load
     */

    /**
     * load a cached html page from the database selected by url
     * @param string $url the request url of the cached html page
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_url(string $url): int
    {
        global $db_con;

        $this->reset();
        $qp = $this->load_sql_by_url($db_con->sql_creator(), $url);
        return $this->load($qp);
    }

    /**
     * get the cached html page for the given url
     *
     * @param string $url the request url of the cached html page
     * @return string|null the cached html page or null if the url is not (yet) cached
     */
    function html_by_url(string $url): ?string
    {
        $result = null;
        $id = $this->load_by_url($url);
        if ($id > 0) {
            $result = $this->html_page;
        }
        return $result;
    }


    /*
     * save
     */

    /**
     * add or replace the cached html page for the given url
     * and remember the rendering time in the last update timestamp
     *
     * @param string $url the request url that the cached html page belongs to
     * @param string $html the rendered html page that should be cached
     * @param user_message $msg to collect the problem messages for the requesting user
     * @return bool true if the cached html page has been saved
     */
    function save_html(string $url, string $html, user_message $msg): bool
    {
        $this->load_by_url($url);
        $this->url = $url;
        $this->html_page = $html;
        $this->last_update = new DateTime();
        $this->save($msg);
        return $msg->is_ok();
    }


    /*
     * load sql
     */

    /**
     * create the common part of an SQL statement to retrieve a cached html page from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name of the selection fields to make the query name unique
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql(sql_creator $sc, string $query_name, string $class = self::class): sql_par
    {
        $qp = parent::load_sql($sc, $query_name, $class);
        $sc->set_class($class);

        $sc->set_name($qp->name);
        $sc->set_fields(self::FLD_NAMES);

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a cached html page by url from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $url the request url of the cached html page
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql_by_url(sql_creator $sc, string $url): sql_par
    {
        $qp = $this->load_sql($sc, self::FLD_URL);
        $sc->add_where(self::FLD_URL, $url);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }


    /*
     * api
     */

    /**
     * create an array for the api json creation
     * @param api_type_list $typ_lst configuration for the api message e.g. if phrases should be included
     * @param user|null $usr the user for whom the api message should be created which can differ from the session user
     * @return array the filled array used to create the api json message to the frontend
     */
    function api_json_array(api_type_list $typ_lst, user|null $usr = null): array
    {
        $vars = [];

        $vars[json_fields::ID] = $this->id();
        $vars[json_fields::URL] = $this->url;
        $vars[json_fields::HTML_PAGE] = $this->html_page;
        $vars[json_fields::LAST_UPDATE] = $this->last_update?->format(DateTimeInterface::ATOM);

        return $vars;
    }


    /*
     * sql write fields
     */

    /**
     * get a list of all database fields that might be changed
     * excluding the internal fields e.g. the database id
     * field list must be corresponding to the db_fields_changed fields
     *
     * @param sql_type_list $sc_par_lst only used for link objects
     * @return array list of all database field names that have been updated
     */
    function db_fields_all(sql_type_list $sc_par_lst = new sql_type_list()): array
    {
        return array_merge(
            parent::db_fields_all(),
            [
                self::FLD_URL,
                self::FLD_HTML_PAGE,
                fields::FLD_LAST_UPDATE,
            ]
        );
    }

    /**
     * get a list of database field names, values and types that have been updated
     *
     * @param db_cache_page|db_object_seq_id $obj the compare value to detect the changed fields
     * @param user_message $msg the user message object that collects any issues during the sql creation
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par_field_list list 3 entry arrays with the database field name, the value and the sql type that have been updated
     */
    function db_fields_changed(
        db_cache_page|db_object_seq_id $obj,
        user_message                   $msg,
        sql_type_list                  $sc_par_lst = new sql_type_list()
    ): sql_par_field_list
    {
        $lst = parent::db_fields_changed($obj, $msg, $sc_par_lst);
        if ($obj->url !== $this->url) {
            $lst->add_field(
                self::FLD_URL,
                $this->url,
                sql_field_type::TEXT,
                $obj->url
            );
        }
        if ($obj->html_page !== $this->html_page) {
            $lst->add_field(
                self::FLD_HTML_PAGE,
                $this->html_page,
                sql_field_type::TEXT,
                $obj->html_page
            );
        }
        if ($obj->last_update != $this->last_update) {
            $lst->add_field(
                fields::FLD_LAST_UPDATE,
                $this->last_update?->format(sql_db::DATE_FORMAT),
                sql_field_type::TIME,
                $obj->last_update?->format(sql_db::DATE_FORMAT)
            );
        }
        return $lst;
    }


    /*
     * sql fields
     */

    function name_field(): string
    {
        return self::FLD_URL;
    }


    /*
     * db helper
     */

    /**
     * check if the cached html page can be added to the database
     * e.g. reject if the url that is used as the unique key is missing
     *
     * @param user_message $msg the message object that is enriched in case something went wrong to show the user the problem and the suggested solutions
     * @return bool true if everything has been fine
     */
    protected function check(user_message $msg): bool
    {
        if ($this->url == '') {
            $msg->add_err(msg_id::URL_KEY_MISSING, [
                msg_id::VAR_URL_KEY => $this->dsp_id()
            ]);
        }
        return $msg->is_ok();
    }


    /*
     * debug
     */

    function dsp_id(): string
    {
        $lib = new library();
        $class = $lib->class_to_name($this::class);
        $result = $class;
        if ($this->url != null) {
            $result .= ' ' . $this->url;
        }
        return $result . ' (' . $this->id_field() . ' ' . $this->id() . ')';
    }

    function name(): string|null
    {
        return $this->url;
    }

}
