<?php

/*

    test_graph.php - TESTing of the GRAPH functions
    --------------

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

use api\word_api;
use model\phrase_list;
use model\triple_list;
use model\value_list;
use model\verb;
use model\word;
use test\testing;
use const test\TIMEOUT_LIMIT_PAGE;

function run_graph_test(testing $t): void
{

    global $usr;

    $back = 0;

    $t->header('Test the graph class (classes/triple_list.php)');

    // get all phrase links used for a phrase and its related values
    // e.g. for the phrase "Company" the link "Company has a balance sheet" should be returned

    // step 1: define the phrase list e.g. in this case only the test word for city
    $phr_lst = new phrase_list($usr);
    $phr_lst->load_by_names(array(word_api::TN_CITY));

    // step 2: get all values related to the phrases
    $val_lst = new value_list($usr);
    $val_lst->phr_lst = $phr_lst;
    $val_lst->load_all();
    $wrd_lst_all = $val_lst->phr_lst->wrd_lst_all();

    // step 3: get all phrases used for the value descriptions
    $phr_lst_used = new phrase_list($usr);
    foreach ($wrd_lst_all->lst() as $wrd) {
        if (!array_key_exists($wrd->id(), $phr_lst_used->id_lst())) {
            $phr_lst_used->add($wrd->phrase());
        }
    }
    // step 4: get the word links for the used phrases
    //         these are the word links that are needed for a complete export
    $lnk_lst = new triple_list($usr);
    $lnk_lst->wrd_lst = $phr_lst_used->wrd_lst();
    $lnk_lst->direction = 'up';
    $lnk_lst->load_old();
    $result = $lnk_lst->name();
    // check if at least the basic relations are in the database
    /*
    $target = '' . word_api::TN_CITY_AS_CATEGORY . ' has a balance sheet';
    $t->dsp_contains(', triple_list->load for ' . $phr_lst->dsp_id(), $target, $result, TIMEOUT_LIMIT_PAGE);
    $target = 'Company has a forecast';
    $t->dsp_contains(', triple_list->load for ' . $phr_lst->dsp_id(), $target, $result, TIMEOUT_LIMIT_PAGE);
    $target = 'Company uses employee';
    $t->dsp_contains(', word ' . $phr_lst->dsp_id(), $target, $result, TIMEOUT_LIMIT_PAGE);
    */

    // similar to above, but just for the zurich
    $phr_lst = new phrase_list($usr);
    $phr_lst->load_by_names(array(word_api::TN_ZH, word_api::TN_INHABITANTS, word_api::TN_MIO));
    $lnk_lst = new triple_list($usr);
    $lnk_lst->wrd_lst = $phr_lst->wrd_lst_all();
    $lnk_lst->direction = 'up';
    $lnk_lst->load_old();
    $result = $lnk_lst->name();
    // TODO to be reviewed
    $target = 'System Test Phrase: Zurich Insurance,Zurich (Canton),Zurich (City)';
    if ($result != $target) {
        $target = 'System Test Phrase: Zurich Insurance,Zurich (City),Zurich (Canton)';
    }
    $t->dsp_contains(', triple_list->load for ' . $phr_lst->dsp_id(), $target, $result, TIMEOUT_LIMIT_PAGE);


    // the other side
    $ZH = new word($usr);
    $ZH->load_by_name(word_api::TN_ZH, word::class);
    $is = new verb;
    $is->set_user($usr);
    $is->load_by_code_id(verb::IS_A);
    $graph = new triple_list($usr);
    $graph->wrd = $ZH;
    $graph->vrb = $is;
    $graph->direction = 'up';
    $graph->load_old();
    //$target = zut_html_list_related($ZH->id, $graph->direction, $usr->id());
    $result = $graph->display($back);
    /*
    $diff = str_diff($result, $target);
    if ($diff != null) {
        if (in_array('view', $diff)) {
            if (in_array(0, $diff['view'])) {
                if ($diff['view'][0] == 0) {
                    $target = $result;
                }
            }
        }
    } */
    $target = word_api::TN_COMPANY;
    $t->dsp_contains('graph->load for ZH up is', $target, $result);

}