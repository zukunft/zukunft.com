<?php

/*

    test/create/test_sources.php - create the test source objects
    ----------------------------


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

namespace Zukunft\ZukunftCom\test\php\create;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::MODEL_REF . 'source.php';
include_once paths::MODEL_REF . 'source_list.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_CONST . 'sources.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_ENUM . 'source_types.php';
include_once paths::SHARED_TYPES . 'protection_types.php';
include_once paths::SHARED_TYPES . 'share_types.php';
include_once paths::SHARED . 'url_var.php';
include_once html_paths::REF . 'source.php';
include_once html_paths::REF . 'source_list.php';
include_once html_paths::USER . 'user_message.php';
include_once test_paths::UTILS . 'test_cleanup.php';
include_once test_paths::UTILS . 'test_lib.php';

use Zukunft\ZukunftCom\main\php\cfg\ref\source;
use Zukunft\ZukunftCom\main\php\cfg\ref\source_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\web\ref\source as source_ui;
use Zukunft\ZukunftCom\main\php\web\ref\source_list as source_list_ui;
use Zukunft\ZukunftCom\main\php\web\user\user_message as user_message_ui;
use Zukunft\ZukunftCom\main\php\shared\const\sources;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\source_types;
use Zukunft\ZukunftCom\main\php\shared\types\protection_types as protect_type_shared;
use Zukunft\ZukunftCom\main\php\shared\types\share_types as share_type_shared;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\test\php\utils\test_lib;

class test_sources extends test_objects
{

    /*
     * cleanup
     */

    /**
     * delete any remaining test source for a clean test start
     */
    function cleanup(string $ts): void
    {
        parent::cleanup_objects($ts, sources::TEST_SOURCES, new source($this->env->usr1));
    }


    /*
     * unit
     */

    function source(): source
    {
        $src = new source($this->env->usr1);
        $src->set(sources::BFS_ID, sources::BFS);
        $src->set_type(source_types::PDF, new user_message($this->env->usr1));
        $src->description = sources::BFS_COM;
        $src->url = sources::BFS_ULR;
        return $src;
    }

    function source_reserved(): source
    {
        $src = new source($this->env->usr1);
        $src->set(sources::SIB_ID, sources::SIB);
        $src->set_type(source_types::PDF, new user_message($this->env->usr1));
        $src->description = sources::SIB_COM;
        $src->url = sources::SIB_URL;
        return $src;
    }

    /**
     * @return source object where the most specific mandatory var is not set which is in case of a source the id and the name
     */
    function source_incomplete(): source
    {
        $src = $this->source_reserved();
        $src->id = 0;
        $src->set_name(null);
        return $src;
    }

    /**
     * @return source with all fields set for testing the sql function creation
     */
    function source_filled(): source
    {
        global $sys;
        $t_msk = new test_views($this->env);
        $src = $this->source();
        $src->doi = sources::TEST_DOI;
        // the view with its name, so that the export can name it
        $src->view = $t_msk->view_source();
        $src->exclude();
        $src->set_share_id($sys->typ_lst->shr_typ->id(share_type_shared::GROUP));
        $src->set_protection_id($sys->typ_lst->ptc_typ->id(protect_type_shared::USER));
        $src->set_usage(test_const::DUMMY_USAGE_SOURCE);
        return $src;
    }

    /**
     * @return source with all fields set for testing the sql function creation
     */
    function source_filled_included(): source
    {
        $src = $this->source_filled();
        $src->include();
        return $src;
    }

    /**
     * @return source with all fields set and a reserved test name for testing the db write function
     */
    function source_filled_add(): source
    {
        $src = $this->source_filled_included();
        $src->id = 0;
        $src->set_name(sources::SYSTEM_TEST_ADD);
        return $src;
    }

    function source_add(): source
    {
        $src = new source($this->env->usr1);
        $src->set_name(sources::SYSTEM_TEST_ADD);
        return $src;
    }

    /**
     * @return source used for the reference
     */
    function source_ref(): source
    {
        $src = new source($this->env->usr1);
        $src->set(sources::WIKIDATA_ID, sources::WIKIDATA);
        $src->set_type(source_types::CSV, new user_message($this->env->usr1));
        return $src;
    }

    /**
     * @return source additional with the fields that only an admin user is allowed to import
     */
    function source_admin(): source
    {
        $src = $this->source_reserved();
        $src->set_code_id_db(sources::SIB_CODE);
        return $src;
    }

    /**
     * @return source to test the sql insert via function
     */
    function source_add_by_func(): source
    {
        $msk = new source($this->env->usr1);
        $msk->set_name(sources::SYSTEM_TEST_ADD_VIA_FUNC);
        return $msk;
    }

    function source_list(): source_list
    {
        $lst = new source_list($this->env->usr1);
        $lst->add($this->source_filled_included());
        return $lst;
    }

    function source_list_ui(): source_list_ui
    {
        $tl = new test_lib();
        return $tl->list_to_ui($this->source_list());
    }


    /*
     * url
     */

    /**
     * the url of an empty source used to open the add source form
     *
     * @return array the source url parameters of a new source
     */
    static function source_new_url(user_message_ui $msg): array
    {
        $src_ui = new source_ui();
        return $src_ui->to_url_array($msg);
    }

    /**
     * the url of the added test source
     *
     * @return array the source url parameters of the added test source
     */
    function source_add_url(user_message_ui $msg): array
    {
        $src_ui = new source_ui($this->source_add()->api_json());
        return $src_ui->to_url_array($msg);
    }

    /**
     * the url parameters posted by the 'Add new source' form on save, used by the add_source
     * workflow test to show the new source in the confirm add view (docs/llm/testing.md);
     * the share and protection ids are the defaults of a newly added source; the object id and
     * the back target are added by the workflow step, not here
     *
     * @return array the add form url parameters of the new source
     */
    function add_url_array(): array
    {
        return [
            url_var::NAME => sources::SYSTEM_TEST_ADD,
            url_var::DESCRIPTION => sources::SYSTEM_TEST_ADD_COM,
            url_var::URL => sources::SYSTEM_TEST_ADD_URL,
            url_var::DOI => sources::TEST_DOI,
            url_var::SOURCE_TYPE => source_types::PDF_ID,
            url_var::SHARE => share_type_shared::PUBLIC_ID,
            url_var::PROTECTION => protect_type_shared::NO_PROTECT_ID
        ];
    }

    /**
     * the filled source url posted by the edit form in the second change_source round, mirroring
     * test_formulas::fill_url_array: the first round only changed the url, so the fill round changes
     * the description and adds the default view that the add form leaves unset; the '8'-prefixed
     * opening values are the state the source has after the first round, so the confirm view shows
     * only the description and the view as changed
     *
     * @param int $id the database id of the source the workflow runs on, used as the back target
     * @return array the edit form url with every field set plus the '8'-prefixed opening values
     */
    function fill_url_array(int $id): array
    {
        $msg = new user_message_ui();
        $url_arr = $this->source_add_url($msg);
        // the workflow step adds the current db id of the test source, so drop the factory id
        unset($url_arr[url_var::ID]);
        $url_arr[url_var::URL] = sources::TEST_URL_CHANGED;
        $url_arr[url_var::DESCRIPTION] = sources::TEST_DESCRIPTION_CHANGED;
        $url_arr[url_var::VIEW] = views::SOURCE_ID;
        $url_arr[url_var::PRE . url_var::NAME] = $url_arr[url_var::NAME];
        $url_arr[url_var::PRE . url_var::URL] = $url_arr[url_var::URL];
        $url_arr[url_var::BACK . url_var::ID] = $id;
        return $url_arr;
    }

}