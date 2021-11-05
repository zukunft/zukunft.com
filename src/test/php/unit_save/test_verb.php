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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2021 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

function run_verb_test(testing $t)
{

    global $usr;

    $t->header('Test the verb class (classes/verb.php)');

    // check the loading of the "is a" verb
    $vrb = new verb;
    $vrb->id = cl(db_cl::VERB, verb::IS_A);
    $vrb->usr = $usr;
    $vrb->load();
    $target = 'is a';
    $result = $vrb->name;
    $t->dsp('verb->load ', $target, $result);


    $t->header('Test the verb list class (classes/verb_list.php)');

    // check the loading of the "is a" verb
    $wrd_ZH = $t->load_word(word::TN_ZH);
    $vrb_lst = $wrd_ZH->link_types('up');
    $target = 'is a';
    $result = '';
    // select the first verb
    foreach ($vrb_lst->lst as $vrb) {
        if ($result == '') {
            $result = $vrb->name;
        }
    }
    $t->dsp('verb_list->load ', $target, $result);

}