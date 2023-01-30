<?php

// TODO sync with triple
//use PHPUnit\Framework\TestCase;

//class triple_unit_tests extends TestCase
use api\triple_api;
use api\word_api;

class triple_unit_tests
{
    function run(testing $t): void
    {

        global $usr;

        // init
        $db_con = new sql_db();
        $t->name = 'triple->';
        $t->resource_path = 'db/triple/';
        $json_file = 'unit/triple/pi.json';
        $usr->set_id(1);

        $t->header('Unit tests of the triple class (src/main/php/model/word/triple.php)');


        $t->subheader('SQL statement tests');

        $trp = new triple($usr);
        $t->assert_load_sql_id($db_con, $trp);
        $t->assert_load_sql_name($db_con, $trp);
        $t->assert_load_sql_link($db_con, $trp);

        // sql to load the triple by id
        $trp = new triple($usr);
        $trp->set_id(2);
        $t->assert_load_standard_sql($db_con, $trp);

        // sql to load the triple by name
        $trp = new triple($usr);
        $trp->set_name(triple_api::TN_READ);
        $t->assert_load_standard_sql($db_con, $trp);


        $t->subheader('API unit tests');

        $trp = new triple($usr);
        $trp->set(1, triple_api::TN_READ, triple_api::TN_READ, verb::IS_A, word_api::TN_READ);
        $trp->description = 'The mathematical constant Pi';
        $api_trp = $trp->api_obj();
        $t->assert($t->name . 'api->id', $api_trp->id, $trp->id());
        $t->assert($t->name . 'api->name', $api_trp->name, $trp->name());
        $t->assert($t->name . 'api->description', $api_trp->description, $trp->description);
        $t->assert($t->name . 'api->from', $api_trp->from()->name, $trp->from->obj->name_dsp());
        $t->assert($t->name . 'api->to', $api_trp->to()->name, $trp->to->obj->name_dsp());


        $t->subheader('Im- and Export tests');

        $t->assert_json(new triple($usr), $json_file);

    }

}
