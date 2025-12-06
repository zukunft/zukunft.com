<?php

/*

    test/create/test_verbs.php - create the test verb objects
    --------------------------


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
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::MODEL_VERB . 'verb.php';
include_once paths::SHARED_TYPES . 'verbs.php';
include_once test_paths::CREATE . 'test_const.php';
include_once test_paths::UTILS . 'test_cleanup.php';

use Zukunft\ZukunftCom\main\php\cfg\verb\verb;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class test_verbs
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
     * @return verb the default verb
     */
    function verb(): verb
    {
        $vrb = new verb(verbs::NOT_SET_ID, verbs::NOT_SET_NAME, verbs::NOT_SET);
        $vrb->set_description(verbs::NOT_SET_COM);
        $vrb->set_user($this->env->usr1);
        return $vrb;
    }

    /**
     * @return verb the default verb with all vars set
     */
    function verb_filled(): verb
    {
        $vrb = new verb(verbs::NOT_SET_ID, verbs::NOT_SET_NAME, verbs::NOT_SET);
        $vrb->set_description(verbs::NOT_SET_COM);
        $vrb->set_user($this->env->usr1);
        return $vrb;
    }

    /**
     * @return verb a standard verb with user null
     */
    function verb_is(): verb
    {
        return new verb(verbs::IS_ID, verbs::IS_NAME, verbs::IS);
    }

    /**
     * @return verb a standard verb with all fields set
     */
    function verb_is_filled(): verb
    {
        $vrb = $this->verb_is();
        $vrb->set_description(verbs::IS_COM);
        $vrb->set_plural(verbs::IS_PLURAL);
        $vrb->set_reverse(verbs::IS_REVERSE);
        $vrb->set_reverse_plural(verbs::IS_REV_PLURAL);
        $vrb->set_formula_name(verbs::IS_NAME_FORMULA);
        $vrb->set_user($this->env->usr1);
        $vrb->set_usage(test_const::DUMMY_USAGE_VERB);
        $vrb->set_impact(test_const::DUMMY_IMPACT_VERB);
        return $vrb;
    }

    /**
     * @return verb that has different entries for all fields
     */
    function verb_measure(): verb
    {
        return new verb(verbs::MEASURE_ID, verbs::MEASURE_NAME, verbs::MEASURE);
    }

    /**
     * @return verb a standard verb with all fields set
     */
    function verb_measure_filled(): verb
    {
        $vrb = $this->verb_measure();
        $vrb->set_description(verbs::MEASURE_COM);
        $vrb->set_plural(verbs::MEASURE_PLURAL);
        $vrb->set_reverse(verbs::MEASURE_REVERSE);
        $vrb->set_reverse_plural(verbs::MEASURE_REV_PLURAL);
        $vrb->set_formula_name(verbs::MEASURE_NAME_FORMULA);
        $vrb->set_user($this->env->usr1);
        $vrb->set_usage(test_const::DUMMY_USAGE_VERB);
        $vrb->set_impact(test_const::DUMMY_IMPACT_VERB);
        return $vrb;
    }

    /**
     * @return verb alias
     */
    function verb_alias(): verb
    {
        return new verb(verbs::ALIAS_ID, verbs::ALIAS_NAME, verbs::ALIAS);
    }

    /**
     * @return verb a standard verb with user null
     */
    function verb_part(): verb
    {
        return new verb(verbs::PART_ID, verbs::PART_NAME, verbs::PART_NAME);
    }

    /**
     * @return verb to narrow a selection
     */
    function verb_of(): verb
    {
        $vrb = new verb(verbs::OF_ID, verbs::OF, verbs::OF);
        $vrb->set_user($this->env->usr1);
        return $vrb;
    }

    /**
     * @return verb e.g. for meter per second
     */
    function verb_per(): verb
    {
        $vrb = new verb(verbs::PER_ID, verbs::PER, verbs::PER);
        $vrb->set_user($this->env->usr1);
        return $vrb;
    }

    /**
     * @return verb that indicates a status change e.g. water can get warmer
     */
    function verb_can_get(): verb
    {
        return new verb(verbs::CAN_GET_ID, verbs::CAN_GET_NAME, verbs::CAN_GET);
    }

    /**
     * @return verb a standard verb with user null
     */
    function verb_with(): verb
    {
        $vrb = new verb(verbs::WITH_ID, verbs::WITH, verbs::CAN_CONTAIN_NAME_REVERSE);
        $vrb->set_user($this->env->usr1);
        return $vrb;
    }

    /**
     * @return verb a standard verb with user null
     */
    function verb_can_be(): verb
    {
        return new verb(verbs::CAN_BE_ID, verbs::CAN_BE_NAME, verbs::CAN_BE);
    }

    function verb_has(): verb
    {
        return new verb(verbs::HAS_ID, verbs::HAS_NAME, verbs::HAS);
    }


    /*
     * speed
     */

    /**
     * create a new verb e.g. for unit testing with a given type
     *
     * @return verb the created verb object
     */
    function random(): verb
    {
        global $sys;
        $vrb_id = rand(1, $sys->typ_lst->vrb->count());
        return $sys->typ_lst->vrb->get_by_id($vrb_id);
    }

}