<?php

/*

    /web/view/view_cmp.php - the display extension of the api view component object
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

include_once API_VIEW_PATH . 'component.php';

use api\component_api;
use model\view_cmp_type;

use html\phrase\phrase as phrase_dsp;

class component_dsp_old extends component_api
{

    const FORM_ADD = 'component_add';
    const FORM_EDIT = 'component_edit';

    /**
     * @returns string the html code to display this view component
     */
    function html(?phrase_dsp $phr = null): string
    {
        global $component_types;
        return match ($component_types->code_id($this->type_id)) {
            view_cmp_type::TEXT => $this->text(),
            view_cmp_type::PHRASE_NAME => $this->word_name($phr),
            view_cmp_type::VALUES_RELATED => $this->table(),
            default => 'ERROR: unknown type ',
        };
    }

    /**
     * @return string a fixed text
     */
    function text(): string
    {
        global $component_types;
        if ($component_types->code_id($this->type_id) == view_cmp_type::TEXT) {
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
        global $component_types;
        if ($component_types->code_id($this->type_id) == view_cmp_type::PHRASE_NAME) {
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
        global $component_types;
        if ($component_types->code_id($this->type_id) == view_cmp_type::VALUES_RELATED) {
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
        global $component_types;
        if ($component_types->code_id($this->type_id) == view_cmp_type::NUMERIC_VALUE) {
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
        global $component_types;
        if ($component_types->code_id($this->type_id) == view_cmp_type::FORMULAS) {
            return $this->name();
        } else {
            return '';
        }
    }

    /**
     * TODO move code from view_cmp_dsp_old
     * @return string a dummy text
     */
    function results(): string
    {
        global $component_types;
        if ($component_types->code_id($this->type_id) == view_cmp_type::FORMULA_RESULTS) {
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
        global $component_types;
        if ($component_types->code_id($this->type_id) == view_cmp_type::WORDS_DOWN) {
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
        global $component_types;
        if ($component_types->code_id($this->type_id) == view_cmp_type::WORDS_UP) {
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
        global $component_types;
        if ($component_types->code_id($this->type_id) == view_cmp_type::JSON_EXPORT) {
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
        global $component_types;
        if ($component_types->code_id($this->type_id) == view_cmp_type::XML_EXPORT) {
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
        global $component_types;
        if ($component_types->code_id($this->type_id) == view_cmp_type::CSV_EXPORT) {
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
        global $component_types;
        if ($component_types->code_id($this->type_id) == view_cmp_type::VALUES_ALL) {
            return $this->name();
        } else {
            return '';
        }
    }

    /**
     * HTML code to edit all component fields
     * @param string $dsp_type the html code to display the type selector
     * @param string $phr_row the html code to select the phrase for the row
     * @param string $phr_col the html code to select the phrase for the column
     * @param string $phr_cols the html code to select the phrase for the second column
     * @param string $dsp_log the html code of the change log
     * @param string $back the html code to be opened in case of a back action
     * @return string the html code to display the edit page
     */
    function form_edit(
        string $dsp_type,
        string $phr_row,
        string $phr_col,
        string $phr_cols,
        string $dsp_log,
        string $back = ''): string
    {
        $html = new html_base();
        $result = '';

        $hidden_fields = '';
        if ($this->id <= 0) {
            $script = self::FORM_ADD;
            $fld_ext = '_add';
            $header = $html->text_h2('Create a view element');
        } else {
            $script = self::FORM_EDIT;
            $fld_ext = '';
            $header = $html->text_h2('Change "' . $this->name . '"');
            $hidden_fields .= $html->form_hidden("id", $this->id);
        }
        $hidden_fields .= $html->form_hidden("back", $back);
        $hidden_fields .= $html->form_hidden("confirm", '1');
        $detail_fields = $html->form_text("name" . $fld_ext, $this->name(), "Name");
        $detail_fields .= $html->form_text("description" . $fld_ext, $this->description, "Description");
        $detail_fields .= $dsp_type;
        $detail_row = $html->fr($detail_fields) . '<br>';
        $result = $header
            . $html->form($script, $hidden_fields . $detail_row)
            . '<br>';

        $result .= $dsp_log;

        return $result;
    }

}
