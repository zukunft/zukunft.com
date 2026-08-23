<?php

/*

    test/unit/html/source.php - testing of the html frontend functions for sources
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

use Zukunft\ZukunftCom\main\php\shared\const\fields\fields;
use Zukunft\ZukunftCom\main\php\shared\const\sources;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\languages;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\component\execute\system_form;
use Zukunft\ZukunftCom\main\php\web\const\icons;
use Zukunft\ZukunftCom\main\php\web\component\execute\ui_base;
use Zukunft\ZukunftCom\main\php\web\component\execute\ui_list;
use Zukunft\ZukunftCom\main\php\web\helper\data_object as data_object_ui;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\html\styles;
use Zukunft\ZukunftCom\main\php\web\ref\source;
use Zukunft\ZukunftCom\main\php\web\user\user as user_ui;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_log;
use Zukunft\ZukunftCom\test\php\create\test_sources;
use Zukunft\ZukunftCom\test\php\create\test_users;
use Zukunft\ZukunftCom\test\php\create\test_values;
use Zukunft\ZukunftCom\test\php\create\test_views;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class source_ui_tests
{
    function run(test_cleanup $t): void
    {
        global $mtr;
        global $ui_sys;

        $html = new html_base();
        $t_src = new test_sources($t);
        $t_val = new test_values($t);
        $msg = new user_message();

        $base_url = THIS_URL;
        $lan = languages::DEFAULT;
        $url_arr = [url_var::MASK => views::WORD_ID, url_var::ID => word_names::ZH_ID];

        // start the test section (ts)
        $ts = 'unit ui html source ';
        $t->header($ts);

        $src = new source($t_src->source_reserved()->api_json());
        $test_page = $html->text_h2('source display test');
        $test_page .= 'with tooltip: ' . $src->name_tip() . '<br>';
        $test_page .= 'with link: ' . $src->name_link() . '<br>';
        $test_page .= $t->dsp_title_named_edit($src, $msg);
        // the filled source has a non default type, share and protection, so its title shows all
        // three parts of the subtitle, which the default source of the page above does not
        $src_filled = new source($t_src->source_filled_included()->api_json());
        $test_page .= $t->dsp_title_named_edit($src_filled, $msg);
        // the source default view shows the url of the source as a link below the description
        $base = new ui_base();
        $test_page .= $html->text_h2('source url link');
        $test_page .= $base->source_url_link($src);
        $t->html_page_test($test_page, 'source', 'source', $msg, $base_url, $lan);

        $t->subheader($ts . 'title');

        // the source default view shows the source name as the page title and the type, the share
        // and the protection in the subtitle like the word default view (see base_views.json
        // source_default), so the api message of a source must carry these three fields
        $test_name = 'the type of a source is sent to the frontend';
        $t->assert_true($test_name, $src_filled->type_id($msg) > 0);
        $test_name = 'the share type of a source is sent to the frontend';
        $t->assert_true($test_name, $src_filled->share_id() > 0);
        $test_name = 'the protection type of a source is sent to the frontend';
        $t->assert_true($test_name, $src_filled->protection_id() > 0);

        $sfm = new system_form();
        $ttl_html = $sfm->title_named($src_filled, $msg);
        $test_name = 'the source title names the source';
        $t->assert_text_contains($test_name, $ttl_html, $src_filled->name());
        $test_name = 'the source title links to the source edit view';
        $t->assert_text_contains($test_name, $ttl_html, url_var::MASK . '=' . views::SOURCE_EDIT_ID);
        $test_name = 'the source title has a subtitle for the type, share and protection';
        $t->assert_text_contains($test_name, $ttl_html, styles::SUBTITLE);

        $t->subheader($ts . 'show fields');

        // the source default view shows the description and the url of the source below the title
        // (see base_views.json source_default), the type is already part of the title subtitle
        $test_name = 'the source description is shown as read only text';
        $t->assert($test_name, $sfm->show_description($src), sources::SIB_COM);

        $test_name = 'the source url is shown as a link to the source';
        $t->assert_text_contains($test_name, $base->source_url_link($src), sources::SIB_URL);

        // a source without a url shows an empty text and never a dead link
        $src_no_url = new source($t_src->source_add()->api_json());
        $test_name = 'a source without a url shows an empty text';
        $t->assert($test_name, $base->source_url_link($src_no_url), '');

        $t->subheader($ts . 'values of a source');

        // the source default view lists the values that name this source with the unit and the
        // phrases of each value, so that the user sees what has been taken from the source
        $list = new ui_list();
        $dto = new data_object_ui();
        $dto->val_lst = $t_val->list_by_source_ui();
        $val_html = $list->values_by_source($src, $msg, $dto);
        // the speed of light is one of the test values that name the reserved test source
        $test_name = 'the values that use the source are listed';
        $t->assert_text_contains($test_name, $val_html, triple_names::SPEED_OF_LIGHT);

        // a value that names another source (or none) is not part of the list of this source
        $test_name = 'a value without the source is not listed';
        $t->assert_text_not_contains($test_name, $val_html, word_names::ZH);

        // a source that no value names gets the not-used message instead of an empty section
        $src_unused = new source($t_src->source_ref()->api_json());
        $test_name = 'a source without values shows the not used message';
        $t->assert($test_name, $list->values_by_source($src_unused, $msg, $dto),
            $mtr->txt(msg_id::INFO_NOT_USED_FOR_VALUES));

        // a source that is not yet written has the id 0, which must never match the values
        // that have no source at all (see value_list::filter)
        $test_name = 'a source without an id does not list the values without a source';
        $t->assert($test_name, $list->values_by_source($src_no_url, $msg, $dto),
            $mtr->txt(msg_id::INFO_NOT_USED_FOR_VALUES));


        $t->subheader($ts . 'view tab box');

        // the source default page shows the views that can show a source, the change log and the
        // user overwrites in the tab box (see the 'source tab box' of base_views.json)
        $t_msk = new test_views($t);
        $t_log = new test_log($t);
        $t_usr = new test_users();
        $src_related = $t_src->source_filled_included();
        $src_related->views_related = $t_msk->view_list_word();
        $src_related->changes_related = $t_log->log_list_source();
        // test mode so the backend emits the two given lists without loading them from the database
        $src_json = json_decode($src_related->api_json(
            [api_types::TEST_MODE, api_types::INCL_RELATED]), true);

        $test_name = 'the views of a source are sent to the frontend';
        $t->assert_true($test_name, ($src_json[json_fields::VIEWS] ?? []) != []);
        $test_name = 'the changes of a source are sent to the frontend';
        $t->assert_true($test_name, ($src_json[json_fields::CHANGES] ?? []) != []);

        // the overwrites are read from the user sandbox table, which the test mode skips, so the
        // 'my' rows are added here like on the word and the view page
        $src_json[json_fields::USER_OVERWRITES] = [
            [
                json_fields::FIELD => fields::FLD_DESCRIPTION,
                json_fields::USR_VALUE => 'my own text for this source',
                json_fields::STD_VALUE => sources::BFS_COM,
            ],
        ];
        $src_tab = new source(json_encode($src_json));

        $test_name = 'the views of a source reach the frontend source object';
        $t->assert_true($test_name, $src_tab->view_lst != null and !$src_tab->view_lst->is_empty());
        $test_name = 'the changes of a source reach the frontend source object';
        $t->assert_true($test_name, $src_tab->chg_log != null and !$src_tab->chg_log->is_empty());

        $views_tab_ref = 'href="#' . strtolower($mtr->txt(msg_id::FORM_SUB_TITLE_VIEWS)) . '"';
        $log_tab_ref = 'href="#' . strtolower($mtr->txt(msg_id::FORM_SUB_TITLE_LOG)) . '"';
        $my_tab_ref = 'href="#' . strtolower($mtr->txt(msg_id::FORM_SUB_TITLE_MY)) . '"';
        $usr_tab_keep = $ui_sys->usr ?? null;
        // the user comes from the factory, because the my tab is only shown to a user with an id
        $ui_sys->usr = new user_ui($t_usr->user_sys_normal()->api_json());
        $tab_html = $list->view_tab_box($src_tab, $msg, true);

        $test_name = 'the source page shows the views tab';
        $t->assert_text_contains($test_name, $tab_html, $views_tab_ref);
        $test_name = '... with the name of a view that can show the source';
        $t->assert_text_contains($test_name, $tab_html, views::SCIENCE);
        // the switch button must open the edit view of the shown object, so on a source page the
        // source edit view and never the word edit view (see view::switch_link)
        $test_name = '... and a switch button that opens the source edit view';
        $t->assert_text_contains($test_name, $tab_html, url_var::MASK . '=' . views::SOURCE_EDIT_ID);

        $test_name = 'the source page shows the changes tab';
        $t->assert_text_contains($test_name, $tab_html, $log_tab_ref);
        $test_name = '... with the change that added the source';
        $t->assert_text_contains($test_name, $tab_html, sources::BFS);

        $test_name = 'the user with source overwrites sees the my tab';
        $t->assert_text_contains($test_name, $tab_html, $my_tab_ref);
        $test_name = '... with the user value and the standard value of the overwritten field';
        $t->assert_text_contains($test_name, $tab_html, 'my own text for this source');
        $t->assert_text_contains($test_name, $tab_html, sources::BFS_COM);

        // like on the word page the undo icon links to the confirm page of the source edit view
        // that sets the field back to the standard value (see source::db_fld_to_url)
        $test_name = '... and an undo link to the confirm page for the overwritten field';
        $t->assert_text_contains($test_name, $tab_html, icons::UNDO);
        $t->assert_text_contains($test_name, $tab_html, url_var::STEP . '=' . url_var::STEP_CONFIRM);

        $test_name = 'a source without the related data shows no views and no my tab';
        $src_plain = new source($t_src->source_filled_included()->api_json());
        $plain_html = $list->view_tab_box($src_plain, $msg, true);
        $t->assert_text_not_contains($test_name, $plain_html, $views_tab_ref);
        $t->assert_text_not_contains($test_name, $plain_html, $my_tab_ref);

        $test_name = 'without a logged in user the source page shows no my tab';
        unset($ui_sys->usr);
        $t->assert_text_not_contains($test_name, $list->view_tab_box($src_tab, $msg, true), $my_tab_ref);

        // restore the session user for the following tests
        if ($usr_tab_keep == null) {
            unset($ui_sys->usr);
        } else {
            $ui_sys->usr = $usr_tab_keep;
        }
    }

}