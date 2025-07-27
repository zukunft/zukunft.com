<?php

/*

    test/php/unit_write/graph_tests.php - TESTing of the GRAPH functions
    -----------------------------------

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

namespace unit_write;

use cfg\phrase\phrase_list;
use cfg\value\value_list;
use cfg\word\triple_list;
use cfg\word\word;
use html\word\triple_list as triple_list_dsp;
use shared\enum\foaf_direction;
use shared\const\words;
use test\test_cleanup;

class graph_tests
{

    function run(test_cleanup $t): void
    {

        global $usr;

        $back = 0;

        $t->header('Test the graph class (classes/triple_list.php)');

        // get values related to a phrase list
        // e.g. to get top 10 cities by the number of inhabitants
        // in SQL the statement would be: SELECT inhabitants FROM city ORDER BY inhabitants DESC LIMIT 10;
        // in zukunft.com the statement should be: top 10 cities by inhabitants
        // both statements should be possible in zukunft.com

        // the (slow but first step) internal translation could be

        // interpretation
        // step 1: detect that "top 10" is a limit and order setting
        // step 2: detect that the words to select the values are "city" and "inhabitants"

        // request building
        // step 1: define the phrase list e.g. in this case only the test word for city

        $phr_lst = new phrase_list($usr);
        $phr_lst->load_by_names(array(words::CITY));

        // step 2: get all values related to the phrases
        $val_lst = new value_list($usr);
        $val_lst->load_by_phr_lst($phr_lst);
        $wrd_lst_all = $val_lst->phr_lst()->wrd_lst_all();

        // step 3: get all phrases used for the value descriptions
        $phr_lst_used = new phrase_list($usr);
        foreach ($wrd_lst_all->lst() as $wrd) {
            if (!array_key_exists($wrd->id(), $phr_lst_used->id_lst())) {
                $phr_lst_used->add($wrd->phrase());
            }
        }
        // step 4: get the word links for the used phrases
        //         these are the word links that are needed for a complete export
        // TODO activate Prio 1
        $lnk_lst = new triple_list($usr);
        //$lnk_lst->load_by_phr_lst($phr_lst_used, null, foaf_direction::UP);
        //$result = $lnk_lst->name();
        // check if at least the basic relations are in the database
        /*
        $target = '' . words::TN_CITY_AS_CATEGORY . ' has a balance sheet';
        $t->dsp_contains(', triple_list->load for ' . $phr_lst->dsp_id(), $target, $result, $t::TIMEOUT_LIMIT_PAGE);
        $target = 'Company has a forecast';
        $t->dsp_contains(', triple_list->load for ' . $phr_lst->dsp_id(), $target, $result, $t::TIMEOUT_LIMIT_PAGE);
        $target = 'Company uses employee';
        $t->dsp_contains(', word ' . $phr_lst->dsp_id(), $target, $result, $t::TIMEOUT_LIMIT_PAGE);
        */

        // similar to above, but just for the zurich
        $phr_lst = new phrase_list($usr);
        $phr_lst->load_by_names(array(words::ZH, words::INHABITANTS, words::MIO));
        $lnk_lst = new triple_list($usr);
        $lnk_lst->load_by_phr_lst($phr_lst, null, foaf_direction::UP);
        //$lnk_lst->wrd_lst = $phr_lst->wrd_lst_all();
        $result = $lnk_lst->name();
        // TODO to be reviewed
        $target = words::ZH;
        $t->dsp_contains(', triple_list->load for ' . $phr_lst->dsp_id(), $target, $result, $t::TIMEOUT_LIMIT_PAGE);


        $test_name = 'load the types of Zurich from the database: Zurich is a ';
        // load the word Zurich from the database
        $ZH = new word($usr);
        $ZH->load_by_name(words::ZH);
        // load all types of Zurich e.g. Zurich Insurance
        $zh_lst = new phrase_list($usr);
        $zh_lst->load_by_phr($ZH->phrase(), $t->verb_is(), foaf_direction::UP);
        // load the type names of the Zurich types e.g. Company
        $trp_lst = $zh_lst->triples();
        $zh_types = $trp_lst->phrase_parts();
        // create the HTML code to display the type names
        $api_json = json_decode($zh_types->api_json(), true);
        $dsp_trp_list = new triple_list_dsp();
        $dsp_trp_list->api_mapper($api_json);
        $result = $dsp_trp_list->tbl($back);
        $t->assert_text_contains($test_name . words::CITY, $result, words::COMPANY);
        $t->assert_text_contains($test_name . words::CANTON, $result, words::COMPANY);
        $t->assert_text_contains($test_name . words::COMPANY, $result, words::COMPANY);

    }

}