<?php

// TODO sync with word_link
use PHPUnit\Framework\TestCase;

class triple_unit_tests extends TestCase
{
    function run(testing $t)
    {

        global $usr;

        // init
        $db_con = new sql_db();
        $t->name = 'triple->';
        $t->resource_path = 'db/triple/';
        $json_file = 'unit/triple/second.json';
        $usr->id = 1;

        $t->header('Unit tests of the triple class (src/main/php/model/word/triple.php)');


        $t->subheader('SQL statement tests');

        // sql to load the word by id
        $wrd = new word_link($usr);
        $wrd->id = 2;
        $t->assert_load_sql($db_con, $wrd);
        $t->assert_load_standard_sql($db_con, $wrd);

        // sql to load the word by name
        $wrd = new word_link($usr);
        $t->assert_load_sql($db_con, $wrd);
        $t->assert_load_standard_sql($db_con, $wrd);


        $t->subheader('Im- and Export tests');

        $t->assert_json(new word_link($usr), $json_file);

    }

}
