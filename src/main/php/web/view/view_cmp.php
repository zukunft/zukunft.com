<?php

/*

    web\view\view_cmp.php - the display extension of the api view component object
    ---------------------

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

namespace html;

use api\view_cmp_api;
use view_cmp_type;

class view_cmp_dsp extends view_cmp_api
{

    /**
     * @returns string the html code to display this view component
     */
    function html(?phrase_dsp $phr = null): string
    {
        $result = '';
        switch ($this->type) {
            case view_cmp_type::TEXT:
                $result .= $this->text();
                break;
            case view_cmp_type::PHRASE_NAME:
                $result .= $this->word_name($phr);
                break;
            case view_cmp_type::VALUES_RELATED:
                $result .= $this->table();
                break;
            default:
                $result .= 'ERROR: unknown type ';
        }
        return $result;
    }

    /**
     * @return string a fixed text
     */
    function text(): string
    {
        if ($this->type == view_cmp_type::TEXT) {
            return $this->name();
        } else {
            return '';
        }
    }

    /**
     * @return string the name of a phrase and give the user the possibility to change the phrase name
     */
    function word_name(phrase_dsp $phr): string
    {
        if ($this->type == view_cmp_type::PHRASE_NAME) {
            return $phr->name();
        } else {
            return '';
        }
    }

    /**
     * TODO move code from view_cmp_dsp_old
     * @return string a dummy text
     */
    function table(): string
    {
        if ($this->type == view_cmp_type::VALUES_RELATED) {
            return $this->name();
        } else {
            return '';
        }
    }

    /**
     * TODO move code from view_cmp_dsp_old
     * @return string a dummy text
     */
    function num_list(): string
    {
        if ($this->type == view_cmp_type::WORD_VALUE) {
            return $this->name();
        } else {
            return '';
        }
    }

    /**
     * TODO move code from view_cmp_dsp_old
     * @return string a dummy text
     */
    function formulas(): string
    {
        if ($this->type == view_cmp_type::FORMULAS) {
            return $this->name();
        } else {
            return '';
        }
    }

    /**
     * TODO move code from view_cmp_dsp_old
     * @return string a dummy text
     */
    function formula_values(): string
    {
        if ($this->type == view_cmp_type::FORMULA_RESULTS) {
            return $this->name();
        } else {
            return '';
        }
    }

    /**
     * TODO move code from view_cmp_dsp_old
     * @return string a dummy text
     */
    function word_children(): string
    {
        if ($this->type == view_cmp_type::WORDS_DOWN) {
            return $this->name();
        } else {
            return '';
        }
    }

    /**
     * TODO move code from view_cmp_dsp_old
     * @return string a dummy text
     */
    function word_parents(): string
    {
        if ($this->type == view_cmp_type::WORDS_UP) {
            return $this->name();
        } else {
            return '';
        }
    }

    /**
     * TODO move code from view_cmp_dsp_old
     * @return string a dummy text
     */
    function json_export(): string
    {
        if ($this->type == view_cmp_type::JSON_EXPORT) {
            return $this->name();
        } else {
            return '';
        }
    }

    /**
     * TODO move code from view_cmp_dsp_old
     * @return string a dummy text
     */
    function xml_export(): string
    {
        if ($this->type == view_cmp_type::XML_EXPORT) {
            return $this->name();
        } else {
            return '';
        }
    }

    /**
     * TODO move code from view_cmp_dsp_old
     * @return string a dummy text
     */
    function csv_export(): string
    {
        if ($this->type == view_cmp_type::CSV_EXPORT) {
            return $this->name();
        } else {
            return '';
        }
    }

    /**
     * TODO move code from view_cmp_dsp_old
     * @return string a dummy text
     */
    function all(): string
    {
        if ($this->type == view_cmp_type::VALUES_ALL) {
            return $this->name();
        } else {
            return '';
        }
    }

}
