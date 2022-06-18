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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

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
        $t->html_test(btn_add('Test', 'http'), 'button_add', $t);


        $t->subheader('HTML table tests');

        // create a test set of phrase
        $phr_zh = new \api\phrase_min(1, word::TN_ZH);
        $phr_city = new \api\phrase_min(2, word::TN_CITY);
        $phr_ch = new \api\phrase_min(3, word::TN_CH);
        $phr_inhabitant = new \api\phrase_min(4, word::TN_INHABITANT);
        $phr_2019 = new \api\phrase_min(5, word::TN_2019);
        $phr_mio = new \api\phrase_min(6, word::TN_MIO);

        // create the formula result for the inhabitants of the city of zurich
        $fv_zh = new \api\formula_value_min(1);
        $fv_zh->grp = new \api\phrase_group_min(1);
        $fv_zh->grp->add($phr_zh);
        $fv_zh->grp->add($phr_city);
        $fv_zh->grp->add($phr_inhabitant);
        $fv_zh->grp->add($phr_2019);
        $fv_zh->val = value::TV_CITY_ZH_INHABITANTS_2019;

        // create the formula result for the inhabitants of Switzerland
        $fv_ch = new \api\formula_value_min(2);
        $fv_ch->grp = new \api\phrase_group_min(2);
        $fv_ch->grp->add($phr_ch);
        $fv_ch->grp->add($phr_mio);
        $fv_ch->grp->add($phr_inhabitant);
        $fv_ch->grp->add($phr_2019);
        $fv_ch->val = value::TV_CH_INHABITANTS_2019_IN_MIO;

        // create the formula list and the table to display the resuls
        $fv_lst = new \api\formula_value_list_min();
        $fv_lst->add($fv_zh);
        $fv_lst->add($fv_ch);
        $t->html_test($fv_lst->dsp_obj()->table(), 'formula_values_table', $t);


        $t->subheader('View component tests');

        $cmp = new view_cmp($usr);
        $cmp->set_type(view_cmp_type::TEXT);
        $cmp->name = 'View component text';
        $t->html_test($cmp->dsp_obj()->html(), 'view_cmp_text', $t);

        $wrd = new \api\word_min();
        $wrd->name = 'View component word name';
        $cmp->obj = $wrd;

    }

}
