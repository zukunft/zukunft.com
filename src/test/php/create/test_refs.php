<?php

/*

    test/create/test_refs.php - create the test reference objects
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

namespace Zukunft\ZukunftCom\test\php\create;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::MODEL_REF . 'ref.php';
include_once paths::MODEL_REF . 'ref_list.php';
include_once paths::SHARED_CONST . 'refs.php';
include_once paths::SHARED_TYPES . 'api_types.php';
include_once paths::SHARED_TYPES . 'protection_types.php';
include_once paths::SHARED_TYPES . 'ref_types.php';
include_once paths::SHARED_TYPES . 'share_types.php';
include_once html_paths::REF . 'ref_list.php';
include_once test_paths::UTILS . 'test_cleanup.php';
include_once test_paths::UTILS . 'test_lib.php';

use Zukunft\ZukunftCom\main\php\cfg\ref\ref;
use Zukunft\ZukunftCom\main\php\cfg\ref\ref_list;
use Zukunft\ZukunftCom\main\php\shared\const\refs;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\shared\types\protection_types;
use Zukunft\ZukunftCom\main\php\shared\types\ref_types;
use Zukunft\ZukunftCom\main\php\shared\types\share_types;
use Zukunft\ZukunftCom\main\php\web\ref\ref_list as ref_list_ui;
use Zukunft\ZukunftCom\test\php\utils\test_lib;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class test_refs
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

    /**
     * @return ref with the most often used fields set for unit testing
     */
    function reference(): ref
    {
        global $sys;
        $t_wrd = new test_words($this->env);
        $ref = new ref($this->env->usr1);
        $ref->set(refs::PI_ID,
            $t_wrd->word_pi()->phrase(), $sys->typ_lst->ref_typ->id(ref_types::WIKIDATA), refs::PI_KEY);
        $ref->description = refs::PI_COM;
        return $ref;
    }

    /**
     * @return ref object where the most specific mandatory var is not set which is in case of a reference the id and the name
     */
    function reference_incomplete(): ref
    {
        $ref = $this->reference();
        $ref->set_predicate_id(-1);
        return $ref;
    }

    /**
     * @return ref with the most often used fields set for unit testing
     */
    function reference1(): ref
    {
        global $sys;
        $t_wrd = new test_words($this->env);
        $ref = new ref($this->env->usr1);
        $ref->set(1,
            $t_wrd->word()->phrase(), $sys->typ_lst->ref_typ->id(ref_types::WIKIDATA), refs::PI_KEY);
        $ref->description = refs::PI_COM;
        return $ref;
    }

    function reference_add(): ref
    {
        $ref = new ref($this->env->usr1);
        $ref->set_name(refs::SYSTEM_TEST_ADD);
        return $ref;
    }

    /**
     * @return ref with the most often used fields set for unit testing
     */
    function reference_math(): ref
    {
        global $sys;
        $t_wrd = new test_words($this->env);
        $ref = new ref($this->env->usr1);
        $ref->set(refs::MATH_ID,
            $t_wrd->word_math()->phrase(), $sys->typ_lst->ref_typ->id(ref_types::WIKIDATA), refs::MATH_KEY);
        $ref->description = refs::MATH_COM;
        return $ref;
    }

    /**
     * @return ref with the more fields set for unit testing
     */
    function reference_plus(): ref
    {
        $ref = $this->reference();
        $t_src = new test_sources($this->env);
        $ref->set_source($t_src->source_ref());
        $ref->set_url(refs::PI_URL);
        return $ref;
    }

    /**
     * @return ref with the most often fields changed by user plus the link to the norm db row
     */
    function reference_user(): ref
    {
        $ref = new ref($this->env->usr1);
        $ref->set(4);
        $ref->description = refs::PI_COM;
        return $ref;
    }

    /**
     * @return ref with the most often used fields set for unit testing
     */
    function reference_change(): ref
    {
        global $sys;
        $t_trp = new test_triples($this->env);
        $ref = new ref($this->env->usr1);
        $ref->set(12,
            $t_trp->triple_gwp()->phrase(), $sys->typ_lst->ref_typ->id(ref_types::WIKIDATA), refs::CHANGE_NEW_KEY);
        $ref->description = refs::CHANGE_OLD_KEY;
        return $ref;
    }

    /**
     * @return ref with all fields set to a non default value
     */
    function ref_filled(): ref
    {
        global $sys;
        $t_src = new test_sources($this->env);
        $ref = $this->reference();
        $ref->set_source($t_src->source());
        $ref->set_url(refs::PI_URL);
        $ref->include();
        $ref->set_share_id($sys->typ_lst->shr_typ->id(share_types::GROUP));
        $ref->set_protection_id($sys->typ_lst->ptc_typ->id(protection_types::USER));
        return $ref;
    }

    /**
     * @return ref with all field changed to a non default value that can be user-specific
     */
    function ref_filled_user(): ref
    {
        global $sys;
        $t_src = new test_sources($this->env);
        $ref = $this->reference_user();
        $ref->set_external_key(refs::PI_KEY);
        $ref->set_url(refs::PI_URL);
        $ref->set_source($t_src->source());
        $ref->description = refs::PI_COM;
        $ref->exclude();
        $ref->set_share_id($sys->typ_lst->shr_typ->id(share_types::GROUP));
        $ref->set_protection_id($sys->typ_lst->ptc_typ->id(protection_types::USER));
        return $ref;
    }

    function ref_filled_add(): ref
    {
        $t_wrd = new test_words($this->env);
        $ref = $this->ref_filled();
        $ref->include();
        $ref->id = 0;
        $ref->set_phrase($t_wrd->word_filled_add()->phrase());
        return $ref;
    }

    function ref_list_math(): ref_list
    {
        $lst = new ref_list();
        $lst->add($this->reference());
        $lst->add($this->reference_math());
        return $lst;
    }

    function ref_list_math_ui(): ref_list_ui
    {
        $tl = new test_lib();
        return $tl->list_to_ui($this->ref_list_math(), [api_types::INCL_PHRASES]);
    }

}