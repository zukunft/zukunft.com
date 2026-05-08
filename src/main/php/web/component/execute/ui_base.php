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
include_once html_paths::SANDBOX . 'db_object.php';

use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\sandbox\db_object;

class ui_base
{

    /**
     * @return string a fixed text
     */
    function text(): string
    {
        return 'fixed text component';
    }

    /**
     * @return string the name of a phrase and give the user the possibility to change the phrase name
     */
    function phrase_name(db_object $phr): string
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

    function name(): string
    {
        return $this::class;
    }

}
