<?php

/*

    test/unit/html/triple.php - testing of the html frontend functions for triples
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

namespace Zukunft\ZukunftCom\test\php\unit_ui;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::SHARED_CONST . 'words.php';

use Zukunft\ZukunftCom\main\php\shared\enum\languages;
use Zukunft\ZukunftCom\main\php\web\component\execute\system_form;
use Zukunft\ZukunftCom\main\php\web\component\execute\ui_list;
use Zukunft\ZukunftCom\main\php\web\frontend;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\web\user\user as user_ui;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\word\triple;
use Zukunft\ZukunftCom\main\php\shared\const\fields\fields;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_phrases;
use Zukunft\ZukunftCom\test\php\create\test_triples;
use Zukunft\ZukunftCom\test\php\create\test_users;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class triple_ui_tests
{
    function run(test_cleanup $t, frontend $ui): void
    {
        $html = new html_base();
        $t_trp = new test_triples($t);
        $t_phr = new test_phrases($t);
        $msg = new user_message();

        $base_url = THIS_URL;
        $lan = languages::DEFAULT;
        $url_arr = [url_var::MASK => views::WORD_ID, url_var::ID => triple_names::CANTON_ZURICH_ID];

        // start the test section (ts)
        $ts = 'unit ui html triple ';
        $t->header($ts);

        $trp = new triple($t_trp->triple()->api_json());
        $phr_lst = new phrase_list($t_phr->phrase_list()->api_json());
        $test_page = $html->text_h1('Triple display test');
        $test_page .= $html->text_h2('names');
        $test_page .= 'with tooltip: ' . $trp->name_tip() . '<br>';
        $test_page .= 'with link: ' . $trp->name_link() . '<br>';
        $test_page .= $html->text_h2('buttons');
        $test_page .= 'add button: ' . $trp->btn_add($url_arr, $base_url) . '<br>';
        $test_page .= 'edit button: ' . $trp->btn_edit($url_arr, $base_url) . '<br>';
        $test_page .= 'del button: ' . $trp->btn_del($url_arr, $base_url) . '<br>';
        $test_page .= $html->text_h2('select');
        $from_rows = $trp->phrase_type_selector(views::TRIPLE_EDIT, $msg, $ui->dto->typ_lst_cache) . '<br>';
        $from_rows .= $trp->verb_selector(views::TRIPLE_EDIT, $ui->dto->typ_lst_cache) . '<br>';
        $from_rows .= $trp->phrase_selector($phr_lst, url_var::PHRASE_FROM,views::TRIPLE_EDIT, $trp->get_from()->id()) . '<br>';
        $from_rows .= $trp->phrase_selector($phr_lst, url_var::PHRASE_TO, views::TRIPLE_EDIT, $trp->get_to()->id()) . '<br>';
        $test_page .= $html->form(views::TRIPLE_EDIT, $from_rows);
        $test_page .= $html->text_h2('table');
        $test_page .= $html->tbl($html->tr($trp->tr()));
        $test_page .= $t->dsp_title_named_edit($trp, $msg);

        // the table view uses one title component for a word and a triple, so for a triple it
        // must show the same title as the default triple view
        $form = new system_form();
        $test_page .= $html->text_h2('phrase title of ' . $trp->name());
        $test_page .= $form->title_phrase($trp, $msg) . '<br>';

        // show the related phrases grouped by verb as on the default triple page
        // ("related phrases without subtitles": the verb linked to its page, then the linked
        // phrases); the "global problem" parents are health/education (via "can be", shown)
        // and poverty/populism (via "is a", excluded)
        $list = new ui_list();
        $trp_problem = new triple($t_trp->global_problem()->api_json());
        $trp_problem->phr_lst = $t_phr->phrase_list_start_view_ui();
        $test_page .= $html->text_h2('related phrases without subtitles of ' . $trp_problem->name());
        $test_page .= $list->phrases_related_ex_subtitle($trp_problem, $msg) . '<br>';

        $t->html_page_test($test_page, 'triple', 'triple', $msg, $base_url, $lan);

        $t->subheader($ts . 'related phrases without subtitles');
        $sub_html = $list->phrases_related_ex_subtitle($trp_problem, $msg);
        $test_name = 'the "can be" related phrase is shown grouped under its verb';
        $t->assert_text_contains($test_name, $sub_html, word_names::HEALTH);
        $test_name = 'the is-a parents are excluded from the related phrases without subtitles';
        $t->assert_text_not_contains($test_name, $sub_html, word_names::POVERTY);

        $t->subheader($ts . 'phrase title');
        $test_name = 'the phrase title of a triple is the triple title';
        $t->assert($test_name, $form->title_phrase($trp, $msg), $form->title_triple($trp, $msg));
        $ttl_html = $form->title_phrase($trp, $msg);
        $test_name = 'the phrase title names the triple';
        $t->assert_text_contains($test_name, $ttl_html, $trp->name());
        // the subheader of a triple names the from phrase, the verb and the to phrase
        $test_name = 'the phrase title subheader names the from phrase of the triple';
        $t->assert_text_contains($test_name, $ttl_html, $trp->get_from()->name());
        $test_name = 'the phrase title subheader names the to phrase of the triple';
        $t->assert_text_contains($test_name, $ttl_html, $trp->get_to()->name());

        $t->subheader($ts . 'view tab box');

        // like on the word page the 'my' tab shows the fields the session user has overwritten
        // in user_triples and is only shown if the user is logged in and the api has delivered
        // overwrites; the tab rendering itself is shared with the word page (ui_list::view_tab_box),
        // so this block checks that a triple with overwrites gets the tab like a word
        global $ui_sys;
        global $mtr;
        $trp_json = json_decode($t_trp->triple()->api_json(), true);
        $trp_json[json_fields::USER_OVERWRITES] = [
            [
                json_fields::FIELD => fields::FLD_DESCRIPTION,
                json_fields::USR_VALUE => 'my triple description',
                json_fields::STD_VALUE => triple_names::MATH_CONST_COM,
            ],
        ];
        $trp_tab = new triple(json_encode($trp_json));
        $my_tab_ref = 'href="#' . strtolower($mtr->txt(msg_id::FORM_SUB_TITLE_MY)) . '"';
        $usr_tab_keep = $ui_sys->usr ?? null;

        $test_name = 'the user with triple overwrites sees the my tab';
        $t_usr = new test_users();
        $ui_sys->usr = new user_ui($t_usr->user_sys_normal()->api_json());
        $tab_html = $list->view_tab_box($trp_tab, $msg, true);
        $t->assert_text_contains($test_name, $tab_html, $my_tab_ref);

        $test_name = '... with the user value and the standard value of the overwritten field';
        $t->assert_text_contains($test_name, $tab_html, 'my triple description');
        $t->assert_text_contains($test_name, $tab_html, triple_names::MATH_CONST_COM);

        $test_name = 'without a logged in user the triple page shows no my tab';
        unset($ui_sys->usr);
        $anon_html = $list->view_tab_box($trp_tab, $msg, true);
        $t->assert_text_not_contains($test_name, $anon_html, $my_tab_ref);

        // restore the session user for the following tests
        if ($usr_tab_keep != null) {
            $ui_sys->usr = $usr_tab_keep;
        }
    }

}