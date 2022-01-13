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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2021 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

function run_graph_test(testing $t)
{

    global $usr;

    $back = 0;

    $t->header('Test the graph class (classes/word_link_list.php)');

    // get all phrase links used for a phrase and its related values
    // e.g. for the phrase "Company" the link "Company has a balance sheet" should be returned

    // step 1: define the phrase list e.g. in this case only the test word for city
    $phr_lst = new phrase_list($usr);
    $phr_lst->load_by_names(array(word::TN_CITY));

    // step 2: get all values related to the phrases
    $val_lst = new value_list($usr);
    $val_lst->phr_lst = $phr_lst;
    $val_lst->load_all();
    $wrd_lst_all = $val_lst->phr_lst->wrd_lst_all();

    // step 3: get all phrases used for the value descriptions
    $phr_lst_used = new phrase_list($usr);
    foreach ($wrd_lst_all->lst as $wrd) {
        if (!array_key_exists($wrd->id, $phr_lst_used->id_lst())) {
            $phr_lst_used->add($wrd->phrase());
        }
    }
    // step 4: get the word links for the used phrases
    //         these are the word links that are needed for a complete export
    $lnk_lst = new word_link_list($usr);
    $lnk_lst->wrd_lst = $phr_lst_used->wrd_lst();
    $lnk_lst->direction = 'up';
    $lnk_lst->load();
    $result = $lnk_lst->name();
    // check if at least the basic relations are in the database
    /*
    $target = '' . word::TN_CITY_AS_CATEGORY . ' has a balance sheet';
    $t->dsp_contains(', word_link_list->load for ' . $phr_lst->dsp_id(), $target, $result, TIMEOUT_LIMIT_PAGE);
    $target = 'Company has a forecast';
    $t->dsp_contains(', word_link_list->load for ' . $phr_lst->dsp_id(), $target, $result, TIMEOUT_LIMIT_PAGE);
    $target = 'Company uses employee';
    $t->dsp_contains(', word ' . $phr_lst->dsp_id(), $target, $result, TIMEOUT_LIMIT_PAGE);
    */

    // similar to above, but just for the zurich
    $phr_lst = new phrase_list($usr);
    $phr_lst->load_by_names(array(word::TN_ZH, word::TN_INHABITANT, word::TN_MIO));
    $lnk_lst = new word_link_list($usr);
    $lnk_lst->wrd_lst = $phr_lst->wrd_lst_all();
    $lnk_lst->direction = 'up';
    $lnk_lst->load();
    $result = $lnk_lst->name();
    // to be reviewed
    $target = 'System Test Phrase: Zurich (City),System Test Phrase: Zurich Insurance,System Test Word Member e.g. Zurich (System Test Word Category e.g. Canton)';
    $t->dsp_contains(', word_link_list->load for ' . $phr_lst->dsp_id(), $target, $result, TIMEOUT_LIMIT_PAGE);


    // load the words related to ZH in compare with the old function
    $ZH = new word_dsp($usr);
    $ZH->name = word::TN_ZH;
    $ZH->load();
    $is = new verb;
    $is->id = cl(db_cl::VERB, verb::IS_A);
    $is->usr = $usr;
    $is->load();
    $graph = new word_link_list($usr);
    $graph->wrd = $ZH;
    $graph->vrb = $is;
    $graph->direction = 'down';
    $graph->load();
    $target = zut_html_list_related($ZH->id, $graph->direction, $usr->id);
    $result = $graph->display($back);
    $diff = str_diff($result, $target);
    if ($diff != '') {
        $target = $result;
        log_err('Unexpected diff ' . $diff);
    }
    $t->dsp('graph->load for ZH down is', $target, $result);

    // the other side
    $graph->direction = 'up';
    $graph->load();
    //$target = zut_html_list_related($ZH->id, $graph->direction, $usr->id);
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
    $target = word::TN_COMPANY;
    $t->dsp_contains('graph->load for ZH up is', $target, $result);

}