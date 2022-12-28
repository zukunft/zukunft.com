<?php

/*

  test/unit_db/user_log.php - database unit testing of the user log functions
  -------------------------


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
use api\verb_api;
use api\triple_api;
use api\value_api;
use api\source_api;
use api\formula_api;
use api\view_api;
use api\view_cmp_api;

class change_log_unit_db_tests
{

    function run(testing $t): void
    {

        global $usr;

        // init
        $t->name = 'user log read db->';

        $t->header('Unit database tests of the user log classes (src/main/php/model/log/* and src/main/php/model/user/log_*)');

        $t->subheader('Load user log tests');

        // prepare the objects for the tests
        // TODO use these test functions for all dummy object creations
        $wrd = $t->dummy_word();
        $vrb = $t->dummy_verb();
        $trp = $t->dummy_triple();
        $val = $t->dummy_value();
        $frm = $t->dummy_formula();
        $src = $t->dummy_source();
        $ref = $t->dummy_reference();
        $dsp = $t->dummy_view();
        $cmp = $t->dummy_component();

        // check if loading the changes technically works
        $lst = new change_log_list();
        $result = $lst->load_by_fld_of_wrd($wrd, change_log_field::FLD_WORD_NAME);
        $t->assert('word name change', $result, true);

        // ... and if the first entry is the adding of the word name
        $first_change = $lst->lst()[0];
        $t->assert('first word change is adding', $first_change->old_value, '');
        $t->assert('... the name', $first_change->new_value, word_api::TN_READ);

        // check loading of verb name changes
        $lst = new change_log_list();
        $result = $lst->load_by_fld_of_vrb($vrb, change_log_field::FLD_VERB_NAME);
        $t->assert('verb name change', $result, true);

        // ... and if the first entry is the adding a verb name
        $first_change = $lst->lst()[0];
        $t->assert('first verb change is adding', $first_change->old_value, '');
        $t->assert('... the verb name', $first_change->new_value, verb_api::TN_READ);

        // check loading of given name changes of triples
        $lst = new change_log_list();
        $result = $lst->load_by_fld_of_trp($trp, change_log_field::FLD_TRIPLE_NAME);
        $t->assert('triple name change', $result, true);

        // ... and if the first entry is the setting the given name of a triple
        $first_change = $lst->lst()[0];
        $t->assert('first triple change is setting', $first_change->old_value, '');
        $t->assert('... the given name', $first_change->new_value, triple_api::TN_READ_NAME);

        // check loading of user value changes
        $lst = new change_log_list();
        $result = $lst->load_by_fld_of_val($val, change_log_field::FLD_VALUE_NUMBER);
        $t->assert('value change', $result, true);

        // ... and if the first entry is the update Pi probably because not all decimals can be saved in the database
        $first_change = $lst->lst()[0];
        $t->assert('first value change is updating Pi', $first_change->old_value, value_api::TV_READ_SHORT);
        $t->assert('... to empty', $first_change->new_value, value_api::TV_READ_SHORT);

        // check loading of user formula changes
        $lst = new change_log_list();
        $result = $lst->load_by_fld_of_frm($frm, change_log_field::FLD_FORMULA_USR_TEXT);
        $t->assert('formula expression change', $result, true);

        // ... and if the first entry is the adding the minute scale formula
        $first_change = $lst->lst()[0];
        $t->assert('first formula change is adding', $first_change->old_value, '');
        $t->assert('... the minute scale formula', $first_change->new_value, formula_api::TF_READ);

        // check loading of name changes of source
        $lst = new change_log_list();
        $result = $lst->load_by_fld_of_src($src, change_log_field::FLD_SOURCE_NAME);
        $t->assert('source name change', $result, true);

        // ... and if the first entry is the setting the source name
        $first_change = $lst->lst()[0];
        $t->assert('first source change is setting', $first_change->old_value, '');
        $t->assert('... the name', $first_change->new_value, source_api::TN_READ_API);

        // check loading of name changes of view
        $lst = new change_log_list();
        $result = $lst->load_by_fld_of_dsp($dsp, change_log_field::FLD_VIEW_NAME);
        $t->assert('view name change', $result, true);

        // ... and if the first entry is the setting the view name
        $first_change = $lst->lst()[0];
        $t->assert('first view change is setting', $first_change->old_value, '');
        $t->assert('... the name', $first_change->new_value, view_api::TN_READ);

        // check loading of name changes of view component
        $lst = new change_log_list();
        $result = $lst->load_by_fld_of_cmp($cmp, change_log_field::FLD_VIEW_CMP_NAME);
        $t->assert('view component name change', $result, true);

        // ... and if the first entry is the setting the view component name
        $first_change = $lst->lst()[0];
        $t->assert('first view component change is setting', $first_change->old_value, '');
        $t->assert('... the name', $first_change->new_value, view_cmp_api::TN_READ);

        // TODO add ref

        $t->subheader('API unit db tests');

        $wrd = new word($usr);
        $wrd->load_by_id(1);
        $log_lst = new change_log_list();
        $log_lst->load_by_fld_of_wrd($wrd, change_log_field::FLD_WORD_NAME);
        $t->assert_api($log_lst);

    }

}

