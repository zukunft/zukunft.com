<?php

/*

    test/unit/value_phrase_link.php - unit testing of the VALUE PHRASE LINK functions
    -------------------------------


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

class value_phrase_link_unit_tests
{
    const TEST_NAME = 'value_phrase_link->';
    const PATH = 'db/value/';
    const FILE_EXT = '.sql';
    const FILE_MYSQL = '_mysql';

    public testing $test;
    public value_phrase_link $lnk;
    public sql_db $db_con;

    function run(testing $t)
    {

        global $usr;

        // init
        $this->db_con = new sql_db();
        $this->test = $t;

        /*
         * SQL creation tests (mainly to use the IDE check for the generated SQL statements)
         */

        $t->header('Unit tests of the value phrase link class (src/main/php/model/value/value_phrase_link.php)');

        $t->subheader('Database query creation tests');

        // sql to load a value phrase link by id
        $val_phr_lnk = new value_phrase_link($usr);
        $val_phr_lnk->id = 1;
        $this->assert_sql_all($val_phr_lnk);

        // sql to load a value phrase link by value, phrase and user id
        $val_phr_lnk = new value_phrase_link($usr);
        $val_phr_lnk->id = 0;
        $val_phr_lnk->val->id = 1;
        $val_phr_lnk->phr->id = 2;
        $this->assert_sql_all($val_phr_lnk);

        $t->subheader('Database list query creation tests');

        // sql to load a value phrase link list by value id
        $val_phr_lnk_lst = new value_phrase_link_list($usr);
        $phr = new phrase();
        $phr-> id = 2;
        $this->assert_lst_sql_all($val_phr_lnk_lst, $phr);

        // sql to load a value phrase link list by phrase id
        $val_phr_lnk_lst = new value_phrase_link_list($usr);
        $val = new value($usr);
        $val-> id = 3;
        $this->assert_lst_sql_all($val_phr_lnk_lst, null, $val);

    }

    /**
     * test the SQL statement creation for a value in all SQL dialect
     * and check if the statement name is unique
     *
     * @param value_phrase_link $lnk filled with an id to be able to load
     * @return void
     */
    private function assert_sql_all(value_phrase_link $lnk)
    {
        // check the PostgreSQL query syntax
        $this->assert_pg($lnk);

        $sql_name = $lnk->load_sql($this->db_con)->name;
        $this->test->assert_sql_name_unique($sql_name);

        // check the MySQL query syntax
        $this->assert_mysql($lnk);
    }

    /**
     * test the SQL statement creation for a value using PostgreSQL
     *
     * @param value_phrase_link $lnk filled with an id to be able to load
     * @return void
     */
    private function assert_pg(value_phrase_link $lnk)
    {
        $this->db_con->db_type = sql_db::POSTGRES;
        $this->assert_sql($lnk);
    }

    /**
     * test the SQL statement creation for a value using MySQL
     *
     * @param value_phrase_link $lnk filled with an id to be able to load
     * @return void
     */
    private function assert_mysql(value_phrase_link $lnk)
    {
        $this->db_con->db_type = sql_db::MYSQL;
        $this->assert_sql($lnk, self::FILE_MYSQL);
    }

    /**
     * test the SQL statement creation for a value
     *
     * @param value_phrase_link $lnk filled with an id to be able to load
     * @param string $dialect if not PostgreSQL the name of the SQL dialect
     * @return void
     */
    private function assert_sql(value_phrase_link $lnk, string $dialect = '')
    {
        $created_qp = $lnk->load_sql($this->db_con);
        $expected_sql = $this->test->file(self::PATH . $created_qp->name . $dialect . self::FILE_EXT);
        $this->test->assert(
            self::TEST_NAME . $created_qp->name . $dialect,
            $this->test->trim($created_qp->sql),
            $this->test->trim($expected_sql)
        );

    }

    /**
     * test the SQL statement creation for a value phrase link list in all SQL dialect
     * and check if the statement name is unique
     *
     * @param value_phrase_link_list $lst filled with an id to be able to load
     * @return void
     */
    private function assert_lst_sql_all(value_phrase_link_list $lst, ?phrase $phr = null, ?value $val = null)
    {
        // check the PostgreSQL query syntax
        $this->assert_lst_pg($lst, $phr, $val);

        $sql_name = $lst->load_sql($this->db_con, $phr, $val)->name;
        $this->test->assert_sql_name_unique($sql_name);

        // check the MySQL query syntax
        $this->assert_lst_mysql($lst, $phr, $val);
    }

    /**
     * test the SQL statement creation for a value using PostgreSQL
     *
     * @param value_phrase_link_list $lst filled with an id to be able to load
     * @return void
     */
    private function assert_lst_pg(value_phrase_link_list $lst, ?phrase $phr = null, ?value $val = null)
    {
        $this->db_con->db_type = sql_db::POSTGRES;
        $this->assert_lst_sql($lst, $phr, $val);
    }

    /**
     * test the SQL statement creation for a value using MySQL
     *
     * @param value_phrase_link_list $lst filled with an id to be able to load
     * @return void
     */
    private function assert_lst_mysql(value_phrase_link_list $lst, ?phrase $phr = null, ?value $val = null)
    {
        $this->db_con->db_type = sql_db::MYSQL;
        $this->assert_lst_sql($lst, $phr, $val, self::FILE_MYSQL);
    }

    /**
     * test the SQL statement creation for a value
     *
     * @param value_phrase_link_list $lst filled with an id to be able to load
     * @param string $dialect if not PostgreSQL the name of the SQL dialect
     * @return void
     */
    private function assert_lst_sql(value_phrase_link_list $lst, ?phrase $phr = null, ?value $val = null, string $dialect = '')
    {
        $created_qp = $lst->load_sql($this->db_con, $phr, $val);
        $expected_sql = $this->test->file(self::PATH . $created_qp->name . $dialect . self::FILE_EXT);
        $this->test->assert(
            self::TEST_NAME . $created_qp->name . $dialect,
            $this->test->trim($created_qp->sql),
            $this->test->trim($expected_sql)
        );

    }

}