<?php

/*

    test/unit_write/wikidata_write_tests.php - test the wikidata api interface
    ----------------------------------------


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

namespace Zukunft\ZukunftCom\test\php\unit_write;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once paths::MODEL_IMPORT . 'wikidata.php';
include_once paths::SHARED_CONST . 'refs.php';
include_once html_paths::USER . 'user_message.php';

use Zukunft\ZukunftCom\main\php\cfg\import\wikidata;
use Zukunft\ZukunftCom\main\php\cfg\import\import;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\refs;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class wikidata_write_tests
{

    /*
     * test the wikidata interface
     */

    /**
     * execute the API test using localhost
     * @param test_cleanup $t the test object that includes the test results collected until now
     * @return void
     */
    function run(test_cleanup $t): void
    {

        // start the test section (ts)
        $ts = 'wikidata';
        $t->header($ts);

        $t->subheader($ts . ' read');
        $usr_msg = new user_message();
        $wd_api = new wikidata();
        $wd_json = $wd_api->get(refs::ZH_KEY, $usr_msg);
        $ref_lst = $t->ref_list_zh_ui();
        $json = $wd_api->convert($wd_json, $ref_lst, $usr_msg);
        $imp = new import();
        $imp->put_json(json_encode($json));
    }

}