<?php

/*

    test/unit/html.php - unit testing of the html code generating functions
    ------------------


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

use api\phrase_list_api;
use api\word_api;
use html\button;
use html\html_base;
use html\html_selector;
use html\view_dsp;

class html_unit_tests
{
    function run(testing $t)
    {

        global $usr;
        $html = new html_base();

        $t->header('Unit tests of the html classes (src/main/php/web/html/*)');


        $t->subheader('Login pages');

        $created_html = $html->about();
        $expected_html = $t->file('web/html/about.html');
        $t->dsp('html_selector', $t->trim_html($expected_html), $t->trim_html($created_html));


        $t->subheader('Selector tests');

        // TODO test the creation of a phrase list API JSON
        // TODO create a selector using a list an with a simple test page header an footer
        //
        $sel = new html_selector();
        $sel->label = 'Test:';
        $sel->name = 'test_selector';
        $sel->form = 'test_form';
        $sel_lst = array();
        $sel_lst[1] = 'First';
        $sel_lst[2] = 'Second';
        $sel_lst[3] = 'Third (selected)';
        $sel_lst[4] = 'Fourth';
        $sel->lst = $sel_lst;
        $sel->selected = 3;
        $body = $html->form_start($sel->form);
        $body .= $sel->display();
        $body .= $html->form_end($sel->name, '');
        $t->html_test($body, 'selector', $t);

        // ... and check if the prepared sql name is unique
        //$t->assert_sql_name_unique($log_dsp->dsp_hist_links_sql($db_con, true));

        // button add
        $t->html_test((new button('Test', 'http'))->add(), 'button_add', $t);


        $t->subheader('HTML list tests');

        $lst = new verb_list($usr);
        $lst->add_verb(new verb(1, verb::IS_A));
        $lst->add_verb(new verb(2, verb::IS_PART_OF));
        $t->html_test($lst->dsp_obj()->list(verb::class, 'Verbs'), 'list_verbs', $t);

        $dsp = new view($usr);
        $dsp->set_id(1);
        $cmp1 = new view_cmp($usr);
        $cmp1->set_id(1);
        $cmp1->set_name(view_cmp::TN_READ);
        $cmp1->set_type(view_cmp_type::TEXT);
        $dsp->add_cmp($cmp1);
        $t->html_test($dsp->dsp_obj()->list_sort(), 'list_view_cmp', $t);


        $t->subheader('HTML table tests');

        // create a test set of phrase
        $phr_id = 1;
        $phr_zh = new \api\phrase_api($phr_id, word_api::TN_ZH); $phr_id++;
        $phr_city = new \api\phrase_api($phr_id, word_api::TN_CITY); $phr_id++;
        $phr_canton = new \api\phrase_api($phr_id, word_api::TN_CANTON); $phr_id++;
        $phr_ch = new \api\phrase_api($phr_id, word_api::TN_CH); $phr_id++;
        $phr_inhabitant = new \api\phrase_api($phr_id, word_api::TN_INHABITANT); $phr_id++;
        $phr_2019 = new \api\phrase_api($phr_id, word_api::TN_2019); $phr_id++;
        $phr_mio = new \api\phrase_api($phr_id, word_api::TN_MIO);
        $phr_pct = new \api\phrase_api($phr_id, word_api::TN_PCT);

        // create a test set of phrase groups
        $grp_id = 1;
        $phr_grp_city = new \api\phrase_group_api($grp_id); $grp_id++;
        $phr_grp_city->add($phr_zh);
        $phr_grp_city->add($phr_city);
        $phr_grp_city->add($phr_inhabitant);
        $phr_grp_city->add($phr_2019);
        $phr_grp_canton = new \api\phrase_group_api($grp_id); $grp_id++;
        $phr_grp_canton->add($phr_zh);
        $phr_grp_canton->add($phr_canton);
        $phr_grp_canton->add($phr_inhabitant);
        $phr_grp_canton->add($phr_mio);
        $phr_grp_canton->add($phr_2019);
        $phr_grp_ch = new \api\phrase_group_api($grp_id);
        $phr_grp_ch->add($phr_ch);
        $phr_grp_ch->add($phr_mio);
        $phr_grp_ch->add($phr_inhabitant);
        $phr_grp_ch->add($phr_2019);
        $phr_grp_city_pct = new \api\phrase_group_api($grp_id); $grp_id++;
        $phr_grp_city_pct->add($phr_zh);
        $phr_grp_city_pct->add($phr_city);
        $phr_grp_city_pct->add($phr_inhabitant);
        $phr_grp_city_pct->add($phr_2019);
        $phr_grp_city_pct->add($phr_pct);
        $phr_grp_canton_pct = new \api\phrase_group_api($grp_id); $grp_id++;
        $phr_grp_canton_pct->add($phr_zh);
        $phr_grp_canton_pct->add($phr_canton);
        $phr_grp_canton_pct->add($phr_inhabitant);
        $phr_grp_canton_pct->add($phr_2019);
        $phr_grp_canton_pct->add($phr_pct);
        $phr_lst_context = new \api\phrase_list_api();
        $phr_lst_context->add($phr_inhabitant);

        // create the value for the inhabitants of the city of zurich
        $val_id = 1;
        $val_city = new \api\value_api($val_id); $val_id++;
        $val_city->set_grp($phr_grp_city);
        $val_city->set_val(value::TV_CITY_ZH_INHABITANTS_2019);

        // create the value for the inhabitants of the city of zurich
        $val_canton = new \api\value_api($val_id); $val_id++;
        $val_canton->set_grp($phr_grp_canton);
        $val_canton->set_val(value::TV_CANTON_ZH_INHABITANTS_2020_IN_MIO);

        // create the value for the inhabitants of Switzerland
        $val_ch = new \api\value_api($val_id);
        $val_ch->set_grp($phr_grp_ch);
        $val_ch->set_val(value::TV_CH_INHABITANTS_2019_IN_MIO);

        // create the formula result for the inhabitants of the city of zurich
        $fv_id = 1;
        $fv_city = new \api\formula_value_api($fv_id); $fv_id++;
        $fv_city->set_grp($phr_grp_city_pct);
        $ch_val_scaled = value::TV_CH_INHABITANTS_2019_IN_MIO * 1000000;
        $fv_city->set_val(value::TV_CITY_ZH_INHABITANTS_2019 / $ch_val_scaled);

        // create the formula result for the inhabitants of the city of zurich
        $fv_canton = new \api\formula_value_api($fv_id); $fv_id++;
        $fv_canton->set_grp($phr_grp_canton_pct);
        $fv_canton->set_val(value::TV_CANTON_ZH_INHABITANTS_2020_IN_MIO / value::TV_CH_INHABITANTS_2019_IN_MIO);

        // create the formula result list and the table to display the results
        $fv_lst = new \api\formula_value_list_api();
        $fv_lst->add($fv_city);
        $fv_lst->add($fv_canton);
        $t->html_test($fv_lst->dsp_obj()->table(), 'table_formula_value', $t);

        // create the same table as above, but within a context
        $t->html_test($fv_lst->dsp_obj()->table($phr_lst_context), 'table_formula_value_context', $t);


        $t->subheader('View component tests');

        $cmp = new view_cmp($usr);
        $cmp->set(1, view_cmp::TN_ADD, view_cmp_type::TEXT);
        $t->html_test($cmp->dsp_obj()->html(), 'view_cmp_text', $t);

        $wrd = new \api\word_api();
        $wrd->set_name(word::TN_ADD);
        $cmp->obj = $wrd;

    }

}
