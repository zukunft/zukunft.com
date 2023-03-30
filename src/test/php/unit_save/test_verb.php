<?php

/*

    test_verb.php - TESTing of the VERB class
    -------------


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
use model\verb;
use model\word_select_direction;
use test\testing;

function run_verb_test(testing $t): void
{

    global $usr;
    global $verbs;

    $t->header('Test the verb class (classes/verb.php)');

    // check the loading of the "is a" verb
    $vrb = new verb;
    $vrb->set_user($usr);
    $vrb->load_by_id($verbs->id(verb::IS_A));
    $target = 'is a';
    $result = $vrb->name();
    $t->dsp('verb->load ', $target, $result);


    $t->header('Test the verb list class (classes/verb_list.php)');

    // check the loading of the "is a" verb
    $wrd_ZH = $t->load_word(word_api::TN_ZH);
    $vrb_lst = $wrd_ZH->link_types(word_select_direction::UP);
    $target = 'is a';
    $result = '';
    // select the first verb
    foreach ($vrb_lst->lst as $vrb) {
        if ($result == '') {
            $result = $vrb->name();
        }
    }
    $t->dsp('verb_list->load ', $target, $result);

}