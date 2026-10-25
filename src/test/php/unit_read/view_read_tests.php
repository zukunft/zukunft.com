<?php

/*

    test/php/unit_read/view.php - database unit testing of the view functions
    ---------------------------


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

namespace Zukunft\ZukunftCom\test\php\unit_read;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;

include_once paths::SHARED_TYPES . 'view_types.php';
include_once paths::SHARED_TYPES . 'component_types.php';
include_once paths::SHARED_CONST . 'views.php';

use Zukunft\ZukunftCom\main\php\cfg\component\component;
use Zukunft\ZukunftCom\main\php\cfg\component\component_type_list;
use Zukunft\ZukunftCom\main\php\cfg\view\view;
use Zukunft\ZukunftCom\main\php\cfg\view\view_link_type;
use Zukunft\ZukunftCom\main\php\cfg\view\view_link_type_list;
use Zukunft\ZukunftCom\main\php\cfg\view\view_sys_list;
use Zukunft\ZukunftCom\main\php\cfg\view\view_type_list;
use Zukunft\ZukunftCom\main\php\shared\const\components;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\const\views as view_shared;
use Zukunft\ZukunftCom\main\php\shared\types\component_types as comp_type_shared;
use Zukunft\ZukunftCom\main\php\shared\types\view_link_types;
use Zukunft\ZukunftCom\main\php\shared\types\view_types as view_type_shared;
use Zukunft\ZukunftCom\test\php\create\test_db_load;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class view_read_tests
{

    function run(test_cleanup $t): void
    {

        global $sys;
        global $db_con;
        $msg = new user_message();

        // init
        $t_db = new test_db_load($t);
        $t->name = 'view read->';
        $t->resource_path = 'db/view/';

        // start the test section (ts)
        $ts = 'db read view ';
        $t->header($ts);

        $t->subheader($ts . 'load');
        $msk = new view($t->usr1);
        $t->assert_load($msk, views::START_NAME);

        $test_name = 'load the components of view ' . views::START_NAME . ' contains ' . components::WORD_NAME;
        $msk->load_components($msg);
        $t->assert_contains($test_name, $msk->cmp_lnk_lst->names(), components::WORD_NAME);

        $test_name = 'load view by code id "' . view_shared::WORD_ADD_DETAIL . '"';
        $msk = new view($t->usr1);
        $msk->load_by_code_id(view_shared::WORD_ADD_DETAIL, $msg);
        $t->assert($test_name, $msk->name(), views::TEST_FORM_NEW_NAME);

        $test_name = 'load view by phrase "' . view_shared::WORD_ADD_DETAIL . '"';
        $msk = new view($t->usr1);
        // TODO Prio 2 activate
        //$msk->load_by_phrase($t->phrase_pi());
        //$t->assert($test_name, $msk->name(), views::TN_FORM_NEW);

        $test_name = 'load view by term "' . view_shared::WORD_ADD_DETAIL . '"';
        $msk = new view($t->usr1);
        // TODO Prio 2 activate
        //$msk->load_by_term($t_frm->formula()->term());
        //$t->assert($test_name, $msk->name(), views::TN_FORM_NEW);

        $t->subheader($ts . 'types');

        // load the view types
        $lst = new view_type_list();
        $result = $lst->load($db_con, $msg);
        $t->assert('load_types', $result, true);

        // ... and check if at least the most critical is loaded
        $result = $sys->typ_lst->msk_typ->id(view_type_shared::DEFAULT);
        $t->assert('check type' . view_type_shared::DEFAULT, $result, 1);

        // load the view link types
        $lst = new view_link_type_list();
        $result = $lst->load($db_con, $msg);
        $t->assert('load_types', $result, true);

        // ... and check if at least the most critical is loaded
        $result = $sys->typ_lst->msk_lnk_typ->id(view_link_types::DEFAULT);
        $t->assert('check type' . view_link_types::DEFAULT, $result, 1);


        $t->subheader($ts . 'api creation');

        $test_name = views::START_NAME;
        $cmp = $t_db->load_word($msg, views::START_NAME);
        $t->assert_export_reload($ts . $test_name, $cmp);


        $t->subheader($ts . 'system');
        $t->name = 'view list read db->';

        // load the views used by the system e.g. change word
        $lst = new view_sys_list($t->usr1);
        $lst->usr = $t->usr1;
        $result = $lst->load($db_con, $msg);
        // the first system view list load also warms up the type list cache, so a multi db timeout is used
        $t->assert('load', $result, true, $t::TIMEOUT_LIMIT_DB_MULTI);

        // ... and check if at least the most critical is loaded
        $result = $sys->msk_cac->id(view_shared::WORD);
        $target = 0;
        if ($result > 0) {
            $target = $result; // just check if the id is found
        }
        $t->assert('check view with code id ' . view_shared::WORD, $result, $target);

        // the components of all system views are loaded at once, which must give each view the same
        // components in the same order as the load of the components of the single view
        $sys_word = $lst->get_by_code_id(view_shared::WORD);
        $single_word = new view($t->usr1);
        $single_word->load_by_id($sys_word->id(), $msg);
        $single_word->load_components($msg);
        $test_name = 'the system view has the same components as the single load of the view';
        $t->assert($test_name, $sys_word->cmp_lnk_lst->names(), $single_word->cmp_lnk_lst->names());
        // negative: the split of the links of all system views gives a view no link of another view
        $test_name = '... and no component link of another system view';
        $t->assert($test_name, $sys_word->cmp_lnk_lst->view_ids(), [$sys_word->id()]);

        // the view relations of all system views are loaded at once too, which gives the table
        // its editor as the editor_views.json import has defined it
        $test_name = 'the table view has the table editor as editor';
        $t->assert($test_name, $lst->get_by_code_id(view_shared::TABLE)?->editor_id, view_shared::TABLE_EDITOR_ID);
        $test_name = '... but a view without an editor relation has no editor';
        $t->assert_null($test_name, $sys_word->editor_id);

        // the editors change other views, so editor_views.json gives them the editor type
        $test_name = 'the calculator editor is an editor view';
        $msk_typ_code = $lst->get_by_code_id(view_shared::CALCULATOR_EDITOR)?->type_code_id();
        $t->assert($test_name, $msk_typ_code, view_type_shared::EDITOR);
        $test_name = 'the table editor is an editor view';
        $msk_typ_code = $lst->get_by_code_id(view_shared::TABLE_EDITOR)?->type_code_id();
        $t->assert($test_name, $msk_typ_code, view_type_shared::EDITOR);
        // negative: the calculator only shows the phrase, so it keeps its phrase type
        $test_name = '... but the calculator is no editor view';
        $msk_typ_code = $lst->get_by_code_id(view_shared::CALCULATOR)?->type_code_id();
        $t->assert_not($test_name, $msk_typ_code, view_type_shared::EDITOR);

        // the add, change and delete views change data, so system_views.json gives them the edit type
        $test_name = 'the add word view is an edit view';
        $msk_typ_code = $lst->get_by_code_id(view_shared::WORD_ADD)?->type_code_id();
        $t->assert($test_name, $msk_typ_code, view_type_shared::MASK_DEFAULT);
        $test_name = 'the change word view is an edit view';
        $msk_typ_code = $lst->get_by_code_id(view_shared::WORD_EDIT)?->type_code_id();
        $t->assert($test_name, $msk_typ_code, view_type_shared::MASK_DEFAULT);
        $test_name = 'the delete word view is an edit view';
        $msk_typ_code = $lst->get_by_code_id(view_shared::WORD_DEL)?->type_code_id();
        $t->assert($test_name, $msk_typ_code, view_type_shared::MASK_DEFAULT);
        $test_name = 'the undo view is an edit view';
        $msk_typ_code = $lst->get_by_code_id(view_shared::UNDO)?->type_code_id();
        $t->assert($test_name, $msk_typ_code, view_type_shared::MASK_DEFAULT);
        // negative: the login changes no data, so it keeps the system type
        $test_name = '... but the login is no edit view';
        $msk_typ_code = $lst->get_by_code_id(view_shared::LOGIN)?->type_code_id();
        $t->assert($test_name, $msk_typ_code, view_type_shared::SYSTEM);
        $test_name = '... and the system error list is a system view for an admin';
        $msk_typ_code = $lst->get_by_code_id(view_shared::ERROR_UPDATE)?->type_code_id();
        $t->assert($test_name, $msk_typ_code, view_type_shared::SYSTEM);

        $test_name = 'the import view has the import type';
        $msk_typ_code = $lst->get_by_code_id(view_shared::IMPORT)?->type_code_id();
        $t->assert($test_name, $msk_typ_code, view_type_shared::IMPORT);
        // negative: the paste of a table adds values without a file, so it is an edit view
        $test_name = '... but the paste table view is no import view';
        $msk_typ_code = $lst->get_by_code_id(view_shared::PASTE_TABLE)?->type_code_id();
        $t->assert($test_name, $msk_typ_code, view_type_shared::MASK_DEFAULT);

        // the views of a word or a triple have the phrase type
        $test_name = 'the calculator is a phrase view';
        $msk_typ_code = $lst->get_by_code_id(view_shared::CALCULATOR)?->type_code_id();
        $t->assert($test_name, $msk_typ_code, view_type_shared::PHRASE);
        $test_name = 'the table is a phrase view';
        $msk_typ_code = $lst->get_by_code_id(view_shared::TABLE)?->type_code_id();
        $t->assert($test_name, $msk_typ_code, view_type_shared::PHRASE);

        // a view that shows one object has the type of the object
        $test_name = 'the value detail is a value view';
        $msk_typ_code = $lst->get_by_code_id(view_shared::VALUE_DETAIL)?->type_code_id();
        $t->assert($test_name, $msk_typ_code, view_type_shared::VALUE);
        $test_name = 'the result explanation is a result view';
        $msk_typ_code = $lst->get_by_code_id(view_shared::RESULT_EXPLAIN)?->type_code_id();
        $t->assert($test_name, $msk_typ_code, view_type_shared::RESULT);

        $test_name = 'the word usage and log is a sub view';
        $msk_typ_code = $lst->get_by_code_id(view_shared::WORD_LOG)?->type_code_id();
        $t->assert($test_name, $msk_typ_code, view_type_shared::SUB);
        // the formula test is an admin debug view, so it is not offered in the view selectors
        $test_name = 'the formula test is a system view';
        $msk_typ_code = $lst->get_by_code_id(view_shared::FORMULA_TEST)?->type_code_id();
        $t->assert($test_name, $msk_typ_code, view_type_shared::SYSTEM);

        $test_name = 'the export of one object is an export view';
        $msk_typ_code = $lst->get_by_code_id(view_shared::EXPORT_OBJECT)?->type_code_id();
        $t->assert($test_name, $msk_typ_code, view_type_shared::EXPORT);
        $test_name = 'the job control is a system view';
        $msk_typ_code = $lst->get_by_code_id(view_shared::JOB_CONTROL)?->type_code_id();
        $t->assert($test_name, $msk_typ_code, view_type_shared::SYSTEM);
        $test_name = 'the admin view to complete the word tree is a system view';
        $msk_typ_code = $lst->get_by_code_id(view_shared::COMPLETE)?->type_code_id();
        $t->assert($test_name, $msk_typ_code, view_type_shared::SYSTEM);
        $test_name = 'the language selection is a system view';
        $msk_typ_code = $lst->get_by_code_id(view_shared::LANGUAGE_SELECT)?->type_code_id();
        $t->assert($test_name, $msk_typ_code, view_type_shared::SYSTEM);
        $test_name = 'the term default view has the standard type';
        $msk_typ_code = $lst->get_by_code_id(view_shared::TERM)?->type_code_id();
        $t->assert($test_name, $msk_typ_code, view_type_shared::DEFAULT);

        $test_name = 'the word detail view is a word view';
        $msk_typ_code = $lst->get_by_code_id(view_shared::WORD_DEFAULT_DETAIL)?->type_code_id();
        $t->assert($test_name, $msk_typ_code, view_type_shared::WORD);
        $test_name = 'the value detail view is a value view';
        $msk_typ_code = $lst->get_by_code_id(view_shared::VALUE_DEFAULT_DETAIL)?->type_code_id();
        $t->assert($test_name, $msk_typ_code, view_type_shared::VALUE);
        $test_name = 'the word range view is a word view';
        $msk_typ_code = $lst->get_by_code_id(view_shared::WORD_DEFAULT_RANGE)?->type_code_id();
        $t->assert($test_name, $msk_typ_code, view_type_shared::WORD);
        // the detail_view type does not say the object, so a view of this type is not offered for a word or triple
        $test_name = 'a detail view is not offered for a word or triple';
        $t->assert_true($test_name, in_array(view_type_shared::DETAIL, view_type_shared::NON_PHRASE_TYPES));
        $test_name = '... but a phrase view is';
        $t->assert_false($test_name, in_array(view_type_shared::PHRASE, view_type_shared::NON_PHRASE_TYPES));

        // check all system views
        // TODO Prio 2 activate
        //$t->assert_view(view_shared::DSP_COMPONENT_ADD, $t->usr1);
        //$t->assert_view(view_shared::DSP_COMPONENT_EDIT, $t->usr1);
        //$t->assert_view(view_shared::DSP_COMPONENT_DEL, $t->usr1);



        $t->subheader($ts . 'component');

        $test_name = 'load view component ' . components::WORD_NAME . ' by name and id';
        $cmp = new component($t->usr1);
        $cmp->load_by_name(components::WORD_NAME, $msg);
        $cmp_by_id = new component($t->usr1);
        $cmp_by_id->load_by_id($cmp->id(), $msg);
        $t->assert($test_name, $cmp_by_id->name(), components::WORD_NAME);
        $t->assert($test_name, $cmp_by_id->description, components::WORD_COM);


        $t->subheader($ts . 'component types');
        $t->name = 'view component read db->';

        // load the view component types
        $cmp_lst = new component_type_list();
        $result = $cmp_lst->load($db_con, $msg);
        $t->assert('load_types', $result, true);

        // ... and check if at least the most critical is loaded
        $result = $sys->typ_lst->cmp_typ->id(comp_type_shared::TEXT);
        $t->assert('check type' . comp_type_shared::TEXT, $result, comp_type_shared::TEXT_ID);
    }

}

