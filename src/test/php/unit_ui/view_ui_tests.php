<?php

/*

    test/unit/html/view.php - testing of the html frontend functions for view
    -----------------------
  

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

use Zukunft\ZukunftCom\main\php\shared\enum\languages;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\component\execute\system_form;
use Zukunft\ZukunftCom\main\php\web\const\icons;
use Zukunft\ZukunftCom\main\php\web\component\execute\ui_list;
use Zukunft\ZukunftCom\main\php\web\frontend;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\html\styles;
use Zukunft\ZukunftCom\main\php\web\user\user as user_ui;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\view\term_view as term_view_ui;
use Zukunft\ZukunftCom\main\php\web\view\view;
use Zukunft\ZukunftCom\main\php\web\view\view_relation as view_relation_ui;
use Zukunft\ZukunftCom\main\php\web\word\word;
use Zukunft\ZukunftCom\main\php\shared\const\components;
use Zukunft\ZukunftCom\main\php\shared\const\fields\fields;
use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\shared\types\view_link_types;
use Zukunft\ZukunftCom\main\php\shared\types\view_relation_types;
use Zukunft\ZukunftCom\main\php\shared\types\view_styles;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_const;
use Zukunft\ZukunftCom\test\php\create\test_log;
use Zukunft\ZukunftCom\test\php\create\test_users;
use Zukunft\ZukunftCom\test\php\create\test_views;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class view_ui_tests
{
    function run(test_cleanup $t, frontend $ui): void
    {
        global $mtr;
        global $ui_sys;

        $html = new html_base();
        $t_msk = new test_views($t);
        $msg = new user_message();

        $base_url = THIS_URL;
        $lan = languages::DEFAULT;
        $url_arr = [url_var::MASK => views::VIEW_EDIT_ID, url_var::ID => views::WORD_ID];

        // start the test section (ts)
        $ts = 'unit ui html view ';
        $t->header($ts);

        $msk = new view($t_msk->view()->api_json());
        $test_page = $html->text_h2('view display test');
        $test_page .= 'with tooltip: ' . $msk->name_tip() . '<br>';
        $test_page .= 'with link: ' . $msk->name_link() . '<br>';
        $test_page .= $html->text_h2('buttons');
        $test_page .= 'add button: ' . $msk->btn_add($url_arr, $base_url) . '<br>';
        $test_page .= 'edit button: ' . $msk->btn_edit($url_arr, $base_url) . '<br>';
        $test_page .= 'del button: ' . $msk->btn_del($url_arr, $base_url) . '<br>';
        $test_page .= $html->text_h2('select');
        $from_rows = $msk->view_type_selector(views::VIEW_EDIT, $ui->dto->typ_lst_cache, $msg) . '<br>';
        //$from_rows .= $msk->component_selector(views::VIEW_EDIT, '', 1) . '<br>';
        $test_page .= $html->form(views::VIEW_EDIT, $from_rows);
        $test_page .= $t->dsp_title_named_edit($msk, $msg);
        // the filled view has a non default type, share and protection, so its title shows all
        // three parts of the subtitle, which the plain view of the line above does not
        $msk_filled = new view($t_msk->view_filled_included()->api_json());
        $test_page .= $t->dsp_title_named_edit($msk_filled, $msg);

        $t->subheader($ts . 'title');

        // the view default page shows the view name as the page title and the type, the share
        // and the protection in the subtitle like the word default view (see base_views.json
        // view_default), so the api message of a view must carry these three fields
        $test_name = 'the type of a view is sent to the frontend';
        $t->assert_true($test_name, $msk_filled->type_id($msg) > 0);
        $test_name = 'the share type of a view is sent to the frontend';
        $t->assert_true($test_name, $msk_filled->share_id() > 0);
        $test_name = 'the protection type of a view is sent to the frontend';
        $t->assert_true($test_name, $msk_filled->protection_id() > 0);

        $sfm = new system_form();
        $ttl_html = $sfm->title_named($msk_filled, $msg);
        $test_name = 'the view title names the view';
        $t->assert_text_contains($test_name, $ttl_html, $msk_filled->name());
        $test_name = 'the view title links to the view edit view';
        $t->assert_text_contains($test_name, $ttl_html, url_var::MASK . '=' . views::VIEW_EDIT_ID);
        $test_name = 'the view title has a subtitle for the type, share and protection';
        $t->assert_text_contains($test_name, $ttl_html, styles::SUBTITLE);
        // a view without type, share and protection set shows no empty subtitle brackets
        $test_name = 'a view without a type shows no subtitle';
        $t->assert_text_not_contains($test_name, $sfm->title_named($msk, $msg), styles::SUBTITLE);

        $t->subheader($ts . 'show fields');

        // the view default page shows the name of the display style (see base_views.json
        // view_default), so the api message of a view must carry the style id
        $test_name = 'the style of a view is sent to the frontend';
        $t->assert_true($test_name, $msk_filled->get_style_id() > 0);
        $test_name = 'the style of a view is shown with its user-readable name and its label';
        $t->assert($test_name, $sfm->show_style($msk_filled),
            $t->labeled(msg_id::FORM_SELECT_VIEW_STYLE, view_styles::COL_SM_4_NAME));
        // a view without a style shows an empty text and never a php warning
        $test_name = 'a view without a style shows an empty text';
        $t->assert($test_name, $sfm->show_style($msk), '');

        // the view default page shows the owner (the user who created the view and defines the
        // standard values), which the page url resp. the api message carries as the user name
        $test_name = 'the owner of a view is shown';
        $msk_owned = new view();
        $msk_owned->url_mapper([url_var::OWNER => users::SYSTEM_TEST_NAME], $msg);
        $t->assert($test_name, $sfm->show_owner($msk_owned), users::SYSTEM_TEST_NAME);
        $test_name = 'a view without a known owner shows an empty text';
        $t->assert($test_name, $sfm->show_owner($msk), '');

        $t->subheader($ts . 'view components');

        // the view default page lists the components of the shown view sorted by their position;
        // the components come from the request cache that also provides the views for the page
        // rendering itself, because the page url only carries the view id
        $list = new ui_list();
        $cmp_html = $list->view_components($msk, $msg);
        $test_name = 'the components of the start view are listed';
        $t->assert_text_contains($test_name, $cmp_html, components::WORD_NAME);
        $test_name = 'the listed components link to the component default page';
        $t->assert_text_contains($test_name, $cmp_html, url_var::MASK . '=' . views::COMPONENT_DEFAULT_ID);

        // a view that is not in the cache or has no components gets the no-components message
        $test_name = 'a view without components shows the no-components message';
        $msk_empty = new view($t_msk->view_add()->api_json());
        $t->assert($test_name, $list->view_components($msk_empty, $msg),
            $mtr->txt(msg_id::INFO_VIEW_HAS_NO_COMPONENTS));


        $t->subheader($ts . 'view tab box');

        // the view default page shows the change log and the user overwrites in the tab box (see
        // the 'view tab box' of base_views.json); a view has no related views of its own, because
        // the components of the view are listed by the 'view components' component of the page
        $t_log = new test_log($t);
        $t_usr = new test_users();
        $msk_related = $t_msk->view_filled_included();
        $msk_related->changes_related = $t_log->log_list_view();
        // test mode so the backend emits the given list without loading it from the database
        $msk_json = json_decode($msk_related->api_json(
            [api_types::TEST_MODE, api_types::INCL_RELATED]), true);

        $test_name = 'the changes of a view are sent to the frontend';
        $t->assert_true($test_name, ($msk_json[json_fields::CHANGES] ?? []) != []);

        // the overwrites are read from the user sandbox table, which the test mode skips, so the
        // 'my' rows are added here like on the word and the component page
        $msk_json[json_fields::USER_OVERWRITES] = [
            [
                json_fields::FIELD => fields::FLD_DESCRIPTION,
                json_fields::USR_VALUE => 'my own text for this view',
                json_fields::STD_VALUE => views::START_COM,
            ],
        ];
        $msk_tab = new view(json_encode($msk_json));

        $test_name = 'the changes of a view reach the frontend view object';
        $t->assert_true($test_name, $msk_tab->chg_log != null and !$msk_tab->chg_log->is_empty());

        $views_tab_ref = 'href="#' . strtolower($mtr->txt(msg_id::FORM_SUB_TITLE_VIEWS)) . '"';
        $log_tab_ref = 'href="#' . strtolower($mtr->txt(msg_id::FORM_SUB_TITLE_LOG)) . '"';
        $my_tab_ref = 'href="#' . strtolower($mtr->txt(msg_id::FORM_SUB_TITLE_MY)) . '"';
        $usr_tab_keep = $ui_sys->usr ?? null;
        // the user comes from the factory, because the my tab is only shown to a user with an id
        $ui_sys->usr = new user_ui($t_usr->user_sys_normal()->api_json());
        $tab_html = $list->view_tab_box($msk_tab, $msg, true);

        $test_name = 'the view page shows the changes tab';
        $t->assert_text_contains($test_name, $tab_html, $log_tab_ref);
        $test_name = '... with the change that added the view';
        $t->assert_text_contains($test_name, $tab_html, views::START_NAME);

        $test_name = 'the user with view overwrites sees the my tab';
        $t->assert_text_contains($test_name, $tab_html, $my_tab_ref);
        $test_name = '... with the user value and the standard value of the overwritten field';
        $t->assert_text_contains($test_name, $tab_html, 'my own text for this view');
        $t->assert_text_contains($test_name, $tab_html, views::START_COM);

        // like on the word page the undo icon links to the confirm page of the view edit view
        // that sets the field back to the standard value (see view::db_fld_to_url)
        $test_name = '... and an undo link to the confirm page for the overwritten field';
        $t->assert_text_contains($test_name, $tab_html, icons::UNDO);
        $t->assert_text_contains($test_name, $tab_html, url_var::MASK . '=' . views::VIEW_EDIT_ID);
        $t->assert_text_contains($test_name, $tab_html, url_var::STEP . '=' . url_var::STEP_CONFIRM);

        // the components of the view are shown by their own component, so the tab box of a view
        // page never has a views tab
        $test_name = 'the view page shows no views tab';
        $t->assert_text_not_contains($test_name, $tab_html, $views_tab_ref);

        $test_name = 'a view without overwrites shows no my tab';
        $msk_no_ovr = new view($t_msk->view_filled_included()->api_json());
        $t->assert_text_not_contains($test_name, $list->view_tab_box($msk_no_ovr, $msg, true), $my_tab_ref);

        $test_name = 'without a logged in user the view page shows no my tab';
        unset($ui_sys->usr);
        $t->assert_text_not_contains($test_name, $list->view_tab_box($msk_tab, $msg, true), $my_tab_ref);

        // restore the session user for the following tests
        if ($usr_tab_keep == null) {
            unset($ui_sys->usr);
        } else {
            $ui_sys->usr = $usr_tab_keep;
        }


        $t->subheader($ts . 'link title');

        // the term view default page shows the generated link name as the page title with the
        // linked view and term as links in the subtitle and the description below (see
        // base_views.json term_view_default); a page request (INCL_RELATED) carries the names of
        // the linked objects, so that the subtitle links have a text and not only a target
        $trm_msk = new term_view_ui($t_msk->term_view_filled_included()->api_json(
            [api_types::TEST_MODE, api_types::INCL_RELATED]));
        $ttl_html = $sfm->title_link($trm_msk, $msg);
        $test_name = 'the term view title names the linked view';
        $t->assert_text_contains($test_name, $ttl_html, views::START_NAME);
        $test_name = '... and the linked term';
        $t->assert_text_contains($test_name, $ttl_html, word_names::MATH);
        $test_name = 'the term view title links to the term view edit view';
        $t->assert_text_contains($test_name, $ttl_html, url_var::MASK . '=' . views::VIEW_LINK_EDIT_ID);
        $test_name = 'the term view title has a subtitle for the share and protection';
        $t->assert_text_contains($test_name, $ttl_html, styles::SUBTITLE);
        $test_name = 'the description of a term view is sent to the frontend';
        $t->assert_true($test_name, $sfm->show_description($trm_msk) != '');

        // the page url carries only the ids of the linked objects, so the names of the subtitle
        // links come from the request cache; the term id of a word is its doubled id minus one;
        // the word name needs the word list of the request cache, so the rich test cache is used
        $test_name = 'the term view title of a page url names the linked objects';
        global $ui_sys;
        $trm_msk_url = new term_view_ui();
        $trm_msk_url->url_mapper([
            url_var::VIEW => (string)views::START_ID,
            url_var::TERM => (string)(word_names::MATH_ID * 2 - 1)
        ], $msg, $ui_sys);
        $url_html = $sfm->title_link($trm_msk_url, $msg);
        $t->assert_text_contains($test_name, $url_html, views::START_NAME);
        $t->assert_text_contains($test_name . ' and the term', $url_html, word_names::MATH);

        // the view link edit form shows an order number field, which the term_view has not yet,
        // so it shows an empty text instead of writing a log error (see the TODO in term_view)
        $test_name = 'a term view shows an empty order number';
        $t->assert($test_name, $sfm->show_order_nbr($trm_msk_url), '');

        // the view relation default page uses the same link title (see base_views.json
        // view_relation_default)
        $mrl = new view_relation_ui($t_msk->view_relation_filled_included()->api_json(
            [api_types::TEST_MODE, api_types::INCL_RELATED]));
        $ttl_html = $sfm->title_link($mrl, $msg);
        $test_name = 'the view relation title names the linked parent view';
        $t->assert_text_contains($test_name, $ttl_html, views::WORD_EDIT);
        $test_name = '... and the linked child view';
        $t->assert_text_contains($test_name, $ttl_html, views::WORD_LOG);
        $test_name = 'the view relation title links to the view relation edit view';
        $t->assert_text_contains($test_name, $ttl_html, url_var::MASK . '=' . views::VIEW_RELATION_EDIT_ID);
        $test_name = 'the view relation title has a subtitle for the share and protection';
        $t->assert_text_contains($test_name, $ttl_html, styles::SUBTITLE);
        $test_name = 'the description of a view relation is sent to the frontend';
        $t->assert_true($test_name, $sfm->show_description($mrl) != '');

        // the page url of a view relation also carries only the ids of the two linked views;
        // both are system views, so the names come from the type list cache of the page frontend
        $test_name = 'the view relation title of a page url names the linked views';
        $mrl_url = new view_relation_ui();
        $mrl_url->url_mapper([
            url_var::VIEW_PARENT => (string)views::START_ID,
            url_var::VIEW_CHILD => (string)views::WORD_ID
        ], $msg, $ui->dto);
        $url_html = $sfm->title_link($mrl_url, $msg);
        $t->assert_text_contains($test_name, $url_html, views::START_NAME);
        $t->assert_text_contains($test_name . ' and the child view', $url_html, views::WORD_NAME);

        // a fresh view relation of an add form shows no empty subtitle brackets and has an
        // empty name, never a 'objects not set' placeholder as the page title
        $test_name = 'a fresh view relation shows no subtitle';
        $mrl_new = new view_relation_ui();
        $t->assert_text_not_contains($test_name, $sfm->title_link($mrl_new, $msg), styles::SUBTITLE);
        $test_name = 'a fresh view relation has an empty name';
        $t->assert($test_name, $mrl_new->name(), '');
        $test_name = 'a fresh term view has an empty name';
        $t->assert($test_name, (new term_view_ui())->name(), '');


        $t->subheader($ts . 'link fields');

        // the term view default page shows how the term is linked to the view (see base_views.json
        // term_view_default); a term view has no order number yet (see system_form::order_nbr)
        // the default type is never sent to the frontend (see sandbox_link::api_json_array), so
        // like the style of a component the field stays empty as long as the default applies
        $test_name = 'the term view page shows no link type for the default type';
        $t->assert($test_name, $sfm->show_link_type($trm_msk), '');
        $test_name = 'a fresh term view shows no link type';
        $t->assert($test_name, $sfm->show_link_type(new term_view_ui()), '');

        $test_name = 'the term view page shows a link type that differs from the default';
        $trm_msk_sel = $t_msk->term_view_filled_included();
        $trm_msk_sel->set_predicate(view_link_types::SELECTED_WORD);
        $trm_msk_sel_ui = new term_view_ui($trm_msk_sel->api_json(
            [api_types::TEST_MODE, api_types::INCL_RELATED]));
        $t->assert($test_name, $sfm->show_link_type($trm_msk_sel_ui),
            $t->labeled(msg_id::SHOW_FIELD_LINK_TYPE, view_link_types::SELECTED_WORD_NAME));

        // the view relation default page additionally shows where in the parent view the
        // components of the child view are added (see base_views.json view_relation_default)
        $test_name = 'the view relation page shows no link type for the default type';
        $t->assert($test_name, $sfm->show_link_type($mrl), '');
        $test_name = 'the view relation page shows a relation type that differs from the default';
        $mrl_del = $t_msk->view_relation_filled_included();
        $mrl_del->set_relation_type(view_relation_types::REMOVE);
        $mrl_del_ui = new view_relation_ui($mrl_del->api_json(
            [api_types::TEST_MODE, api_types::INCL_RELATED]));
        $t->assert($test_name, $sfm->show_link_type($mrl_del_ui),
            $t->labeled(msg_id::SHOW_FIELD_LINK_TYPE, view_relation_types::REMOVE_NAME));

        $test_name = 'the view relation page shows the start position';
        $t->assert($test_name, $sfm->show_start_pos($mrl),
            $t->labeled(msg_id::SHOW_FIELD_START_POS, (string)$t_msk->view_relation()->start_pos));
        $test_name = 'a fresh view relation shows no start position';
        $t->assert($test_name, $sfm->show_start_pos($mrl_new), '');


        $t->subheader($ts . 'link tab box');

        // both link default pages show the change log and the user overwrites in the tab box;
        // a link has no related views, so the tab box of a link page has no views tab
        $t_log = new test_log($t);
        $t_usr = new test_users();
        $mrl_related = $t_msk->view_relation_filled_included();
        $mrl_related->changes_related = $t_log->log_list_view_relation();
        // test mode so the backend emits the given list without loading it from the database
        $mrl_json = json_decode($mrl_related->api_json(
            [api_types::TEST_MODE, api_types::INCL_RELATED]), true);

        $test_name = 'the changes of a view relation are sent to the frontend';
        $t->assert_true($test_name, ($mrl_json[json_fields::CHANGES] ?? []) != []);

        // the overwrites are read from the user sandbox table, which the test mode skips, so the
        // 'my' rows are added here like on the word and the formula link page
        $mrl_json[json_fields::USER_OVERWRITES] = [
            [
                json_fields::FIELD => fields::FLD_DESCRIPTION,
                json_fields::USR_VALUE => 'my own text for this relation',
                json_fields::STD_VALUE => 'add usage and log of a word',
            ],
        ];
        $mrl_tab = new view_relation_ui(json_encode($mrl_json));

        $usr_tab_keep = $ui_sys->usr ?? null;
        // the user comes from the factory, because the my tab is only shown to a user with an id
        $ui_sys->usr = new user_ui($t_usr->user_sys_normal()->api_json());
        $tab_html = $list->view_tab_box($mrl_tab, $msg, true);

        $test_name = 'the view relation page shows the changes tab';
        $t->assert_text_contains($test_name, $tab_html, $log_tab_ref);
        $test_name = '... with the changed start position';
        $t->assert_text_contains($test_name, $tab_html,
            (string)test_const::VIEW_RELATION_START_POS);

        $test_name = 'the user with view relation overwrites sees the my tab';
        $t->assert_text_contains($test_name, $tab_html, $my_tab_ref);
        $test_name = '... with the user value and the standard value of the overwritten field';
        $t->assert_text_contains($test_name, $tab_html, 'my own text for this relation');

        // a link has no views of its own, so the tab box of a link page never has a views tab
        $test_name = 'the view relation page shows no views tab';
        $t->assert_text_not_contains($test_name, $tab_html, $views_tab_ref);

        // restore the session user for the following tests
        if ($usr_tab_keep == null) {
            unset($ui_sys->usr);
        } else {
            $ui_sys->usr = $usr_tab_keep;
        }

        // show a view with a side-or-below group where the columns
        // are shown side by side on wide screens and stacked on small screens
        $t_wrd = new test_words($t);
        $wrd = new word($t_wrd->word()->api_json());
        $msk_cols = new view($t_msk->view_side_or_below()->api_json([api_types::INCL_COMPONENTS]));
        $cols_html = $msk_cols->show($wrd, $msg, $ui->dto, '', true);
        $test_page .= $html->text_h2('side or below columns');
        $test_page .= $cols_html;
        $t->html_page_test($test_page, 'view', 'view', $msg, $base_url, $lan);

        $t->subheader($ts . 'side or below columns');
        $test_name = 'each column limits the minimal width so that up to four fit at the wide side width';
        $t->assert_text_contains($test_name, $cols_html, 'min-width: 700px');
        $test_name = 'the first column is shown before the side-or-first-below column';
        $t->assert_text_order($test_name, $cols_html, components::COL_FIRST_NAME, components::COL_SECOND_NAME);
        $test_name = 'the side-or-below column is shown before the side-or-last-below column';
        $t->assert_text_order($test_name, $cols_html, components::COL_THIRD_NAME, components::COL_FOURTH_NAME);
        // rendering the plain view builds the complete component html, so a semi page timeout is used
        $test_name = 'without the side or below position types no minimal width is set';
        $msk_plain = new view($t_msk->view_with_components()->api_json([api_types::INCL_COMPONENTS]));
        $t->assert_text_not_contains($test_name, $msk_plain->show($wrd, $msg, $ui->dto, '', true), 'min-width', $t::TIMEOUT_LIMIT_PAGE_SEMI);
    }

}