<?php

/*

    web/component/execute/ui_preview.php - the html user interface components to preview object changes
    ------------------------------------


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

include_once html_paths::EXECUTE . 'ui_base.php';

class ui_preview extends ui_base
{

    /**
     * TODO move code from component_dsp_old
     * @return string a dummy text
     */
    function view_after(): string
    {
        return $this->name();
    }

    /**
     * TODO move code from component_dsp_old
     * @return string a dummy text
     */
    function view_before(): string
    {
        return $this->name();
    }

    /**
     * TODO add code and maybe move
     * @return string a dummy text
     */
    function paste_table(): string
    {
        return $this->name();
    }

    /**
     * TODO add code and maybe move
     * @return string a dummy text
     */
    function table_body(): string
    {
        return $this->name();
    }

    /**
     * TODO add code and maybe move
     * @return string a dummy text
     */
    function selection_text(): string
    {
        return $this->name();
    }

    /**
     * TODO add code and maybe move
     * @return string a dummy text
     */
    function popup_title(): string
    {
        return $this->name();
    }

    /**
     * TODO add code and maybe move
     * @return string a dummy text
     */
    function popup_class(): string
    {
        return $this->name();
    }

    /**
     * TODO add code and maybe move
     * @return string a dummy text
     */
    function popup_changes(): string
    {
        return $this->name();
    }

    /**
     * TODO add code and maybe move
     * @return string a dummy text
     */
    function popup_impact(): string
    {
        return $this->name();
    }

    /**
     * TODO add code and maybe move
     * @return string a dummy text
     */
    function view_diff(): string
    {
        return $this->name();
    }

}
