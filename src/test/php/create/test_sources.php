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
include_once paths::SHARED_CONST . 'sources.php';
include_once paths::SHARED_ENUM . 'source_types.php';
include_once paths::SHARED_TYPES . 'protection_type.php';
include_once paths::SHARED_TYPES . 'share_type.php';
include_once html_paths::REF . 'source_list.php';
include_once test_paths::UTILS . 'test_cleanup.php';
include_once test_paths::UTILS . 'test_lib.php';

use Zukunft\ZukunftCom\main\php\cfg\ref\source;
use Zukunft\ZukunftCom\main\php\cfg\ref\source_list;
use Zukunft\ZukunftCom\main\php\web\ref\source_list as source_list_ui;
use Zukunft\ZukunftCom\main\php\shared\const\sources;
use Zukunft\ZukunftCom\main\php\shared\enum\source_types;
use Zukunft\ZukunftCom\main\php\shared\types\protection_type as protect_type_shared;
use Zukunft\ZukunftCom\main\php\shared\types\share_type as share_type_shared;
use Zukunft\ZukunftCom\test\php\utils\test_lib;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class test_sources
{

    /*
     * init
     */

    // use the global test environment
    private test_cleanup $env;

    function __construct(test_cleanup $env) {
        $this->env = $env;
    }


    /*
     * unit
     */

    function source(): source
    {
        $src = new source($this->env->usr1);
        $src->set(sources::SIB_ID, sources::SIB);
        $src->set_type(source_types::PDF, $this->env->usr1);
        $src->description = sources::SIB_COM;
        $src->url = sources::SIB_URL;
        return $src;
    }

    /**
     * @return source object where the most specific mandatory var is not set which is in case of a source the id and the name
     */
    function source_incomplete(): source
    {
        $src = $this->source();
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
        $src = $this->source();
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

    /**
     * @return source used for the reference
     */
    function source_ref(): source
    {
        $src = new source($this->env->usr1);
        $src->set(sources::WIKIDATA_ID, sources::WIKIDATA);
        $src->set_type(source_types::CSV, $this->env->usr1);
        return $src;
    }

    /**
     * @return source additional with the fields that only an admin user is allowed to import
     */
    function source_admin(): source
    {
        $src = $this->source();
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

    /**
     * @return source to test the sql insert without use of function
     */
    function source_add_by_sql(): source
    {
        $msk = new source($this->env->usr1);
        $msk->set_name(sources::SYSTEM_TEST_ADD_VIA_SQL);
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

}