<?php

// TODO sync with triple
//use PHPUnit\Framework\TestCase;

//class triple_unit_tests extends TestCase
class triple_unit_tests
{
    function run(testing $t)
    {

        global $usr;

        // init
        $db_con = new sql_db();
        $t->name = 'triple->';
        $t->resource_path = 'db/triple/';
        $json_file = 'unit/triple/pi.json';
        $usr->id = 1;

        $t->header('Unit tests of the triple class (src/main/php/model/word/triple.php)');


        $t->subheader('SQL statement tests');

        // sql to load the triple by id
        $trp = new triple($usr);
        $trp->id = 2;
        $t->assert_load_sql($db_con, $trp);
        $t->assert_load_standard_sql($db_con, $trp);

        // sql to load the triple by name
        $trp = new triple($usr);
        $trp->name = triple::TN_READ;
        $t->assert_load_sql($db_con, $trp);
        $t->assert_load_standard_sql($db_con, $trp);


        $t->subheader('Im- and Export tests');

        $t->assert_json(new triple($usr), $json_file);

    }

}
