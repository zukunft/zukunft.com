<?php

/*

    web/component/execute/ui_base.php - the main html user interface components
    ---------------------------------


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

namespace Zukunft\ZukunftCom\main\php\web\component\execute;

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::SANDBOX . 'combine_named.php';
include_once html_paths::SANDBOX . 'db_object.php';
include_once html_paths::TYPES . 'type_object.php';

use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\sandbox\combine_named;
use Zukunft\ZukunftCom\main\php\web\sandbox\db_object;
use Zukunft\ZukunftCom\main\php\web\types\type_object;

class ui_base
{

    /**
     * @return string the name of a phrase and give the user the possibility to change the phrase name
     */
    function phrase_name(db_object|combine_named $phr): string
    {
        return $phr->name();
    }

    /**
     * TODO move to a component exe part class
     * @return string a dummy text
     */
    function verb_name(?db_object $dbo = null): string
    {
        return $dbo->name();
    }

    /**
     * TODO move to a component exe part class
     * @return string a dummy text
     */
    function triple_name(?db_object $dbo = null): string
    {
        return $dbo->name();
    }

    /**
     * TODO move to a component exe part class
     * @return string a dummy text
     */
    function value_name(?db_object $dbo = null): string
    {
        return $dbo->name();
    }

    /**
     * TODO move to a component exe part class
     * @return string a dummy text
     */
    function group_name(?db_object $dbo = null): string
    {
        return $dbo->name();
    }

    /**
     * TODO move to a component exe part class
     * @return string a dummy text
     */
    function num_value(?db_object $dbo = null): string
    {
        return $dbo->value();
    }

    /**
     * TODO Prio 0 fill with real code
     * @param db_object|null $dbo the term whose main value should be shown
     * @return string the html code with the most relevant value related to $dbo
     */
    function main_value(?db_object $dbo = null): string
    {
        return 'main_value placeholder';
    }

    /**
     * @param db_object|type_object|null $dbo the source whose name should be shown
     * @return string the name of the given source (admin users can change it via the related form)
     */
    function source_name(db_object|type_object|null $dbo = null): string
    {
        return $this->dbo_name($dbo);
    }

    /**
     * @param db_object|type_object|null $dbo the reference whose name should be shown
     * @return string the name of the given reference (admin users can change it via the related form)
     */
    function reference_name(db_object|type_object|null $dbo = null): string
    {
        return $this->dbo_name($dbo);
    }

    /**
     * @param db_object|type_object|null $dbo the language whose name should be shown
     * @return string the name of the given language (admin users can change it via the related form)
     */
    function language_name(db_object|type_object|null $dbo = null): string
    {
        return $this->dbo_name($dbo);
    }

    /**
     * type_object exposes name as a public property while db_object exposes it as a method;
     * this helper reads whichever shape is present so the *_name() callers stay one-liners
     * @param db_object|type_object|null $dbo the object to read the name from
     * @return string the user-facing name, or an empty string when $dbo is null
     */
    private function dbo_name(db_object|type_object|null $dbo): string
    {
        $result = '';
        if ($dbo instanceof db_object) {
            $result = $dbo->name();
        } elseif ($dbo instanceof type_object) {
            $result = $dbo->name;
        }
        return $result;
    }

    /**
     * TODO move code from component_dsp_old
     * @return string the html code to show a list of values
     */
    function table(?db_object $dbo = null, ?data_object $cfg = null): string
    {
        return 'values related to ' . $dbo->name() . ' ';
    }

    /**
     * TODO move code from component_dsp_old
     * @return string a dummy text
     */
    function all(): string
    {
        return $this->name();
    }

    /**
     * @param db_object|null $dbo the formula whose expression should be displayed
     * @return string the user-readable expression of the given formula, or an empty string when $dbo has no expression
     */
    function expression(?db_object $dbo = null): string
    {
        $result = '';
        if ($dbo != null and method_exists($dbo, 'user_expression')) {
            $result = $dbo->user_expression();
        }
        return $result;
    }

    function expression_latex_link(?db_object $dbo = null): string
    {
        $result = '';
        if ($dbo != null and method_exists($dbo, 'expression_latex_link')) {
            $result = $dbo->expression_latex_link();
        }
        return $result;
    }

    function name(): string
    {
        return $this::class;
    }

}
