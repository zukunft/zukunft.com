<?php

/*

    /web/view/component.php - the display extension of the api component object
    -----------------------

    to creat the HTML code to display a component


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

namespace html\view;

include_once WEB_SANDBOX_PATH . 'sandbox_typed.php';

use controller\controller;
use html\api;
use html\html_base;
use html\phrase\phrase as phrase_dsp;
use html\phrase\term as term_dsp;
use html\sandbox\db_object as db_object_dsp;
use html\sandbox_typed_dsp;
use model\library;
use model\view_cmp_type;

class component extends sandbox_typed_dsp
{

    /*
     * object vars
     */

    public ?string $code_id = null;         // the entry type code id


    /*
     * display
     */

    /**
     * @param db_object_dsp $dbo the word, triple or formula object that should be shown to the user
     * @return string the html code of all view components
     */
    function dsp_entries(db_object_dsp $dbo, string $back): string
    {
        log_debug('"' . $dbo->dsp_id() . '" with the view ' . $this->dsp_id() . '"');

        $result = '';

        // list of all possible view components
        $type_code_id = $this->type_code_id();
        $result .= match ($type_code_id) {
            view_cmp_type::TEXT => $this->text(),
            view_cmp_type::WORD => $this->display_name(),
            view_cmp_type::PHRASE_NAME => $this->word_name($dbo->phrase()),
            view_cmp_type::VALUES_RELATED => $this->table($dbo),
            view_cmp_type::WORD_VALUE => $this->num_list($dbo, $back),
            view_cmp_type::FORMULAS => $this->formulas($dbo),
            view_cmp_type::FORMULA_RESULTS => $this->results($dbo),
            view_cmp_type::WORDS_DOWN => $this->word_children($dbo),
            view_cmp_type::WORDS_UP => $this->word_parents($dbo),
            view_cmp_type::JSON_EXPORT => $this->json_export($dbo, $back),
            view_cmp_type::XML_EXPORT => $this->xml_export($dbo, $back),
            view_cmp_type::CSV_EXPORT => $this->csv_export($dbo, $back),
            view_cmp_type::VALUES_ALL => $this->all($dbo, $back),
            view_cmp_type::FORM_TITLE => $this->form_tile($dbo, $back),
            view_cmp_type::FORM_BACK => $this->form_back($dbo, $back),
            view_cmp_type::FORM_CONFIRM => $this->form_confirm($dbo, $back),
            view_cmp_type::FORM_NAME => $this->form_name($dbo, $back),
            view_cmp_type::FORM_DESCRIPTION => $this->form_description($dbo, $back),
            view_cmp_type::FORM_CANCEL => $this->form_cancel($dbo, $back),
            view_cmp_type::FORM_SAVE => $this->form_save($dbo, $back),
            view_cmp_type::FORM_END => $this->form_end(),
            default => 'program code for component type ' . $type_code_id . ' missing<br>'
        };

        return $result;
    }

    /**
     * TODO review these simplified function
     * @return string
     */
    function display_name(): string
    {
        return $this->name();
    }

    /**
     * TODO review these simplified function
     * @return string
     */
    function display_linked(): string
    {
        return $this->name();
    }

    /**
     * @return string a fixed text
     */
    function text(): string
    {
        return $this->name();
    }

    /**
     * @return string the name of a phrase and give the user the possibility to change the phrase name
     */
    function word_name(phrase_dsp $phr): string
    {
        return $phr->name();
    }

    /**
     * TODO move code from view_cmp_dsp_old
     * @return string a dummy text
     */
    function table(): string
    {
        return $this->name();
    }

    /**
     * TODO move code from view_cmp_dsp_old
     * @return string a dummy text
     */
    function num_list(): string
    {
        return $this->name();
    }

    /**
     * TODO move code from view_cmp_dsp_old
     * @return string a dummy text
     */
    function formulas(): string
    {
        return $this->name();
    }

    /**
     * TODO move code from view_cmp_dsp_old
     * @return string a dummy text
     */
    function results(): string
    {
        return $this->name();
    }

    /**
     * TODO move code from view_cmp_dsp_old
     * @return string a dummy text
     */
    function word_children(): string
    {
        return $this->name();
    }

    /**
     * TODO move code from view_cmp_dsp_old
     * @return string a dummy text
     */
    function word_parents(): string
    {
        return $this->name();
    }

    /**
     * TODO move code from view_cmp_dsp_old
     * @return string a dummy text
     */
    function json_export(): string
    {
        return $this->name();
    }

    /**
     * TODO move code from view_cmp_dsp_old
     * @return string a dummy text
     */
    function xml_export(): string
    {
        return $this->name();
    }

    /**
     * TODO move code from view_cmp_dsp_old
     * @return string a dummy text
     */
    function csv_export(): string
    {
        return $this->name();
    }

    /**
     * TODO move code from view_cmp_dsp_old
     * @return string a dummy text
     */
    function all(): string
    {
        return $this->name();
    }

    /**
     * @return string the html code to start a new form and display the tile
     * TODO replace _add with a parameter value
     */
    function form_tile(db_object_dsp $dbo): string
    {
        $lib = new library();
        $html = new html_base();
        $form_name = $lib->class_to_name($dbo::class) . '_add';
        return $html->form_start($form_name);
    }

    /**
     * @return string the html code to include the back trace into the form result
     */
    function form_back(): string
    {
        $html = new html_base();
        return $html->input('back', '', html_base::INPUT_HIDDEN);
    }

    /**
     * @return string the html code to check if the form changes has already confirmed by the user
     */
    function form_confirm(): string
    {
        $html = new html_base();
        return $html->input('confirm', '1', html_base::INPUT_HIDDEN);
    }

    /**
     * @return string the html code to request the object name from the user
     */
    function form_name(db_object_dsp $dbo): string
    {
        $html = new html_base();
        return $html->form_field('Name', $dbo->name());
    }

    /**
     * @return string the html code to request the description from the user
     */
    function form_description(db_object_dsp $dbo): string
    {
        $html = new html_base();
        return $html->form_field('Description', $dbo->description());
    }

    /**
     * @return string the html code for a form cancel button
     */
    function form_cancel(): string
    {
        $html = new html_base();
        return $html->button('Cancel', html_base::BS_BTN_CANCEL);
    }

    /**
     * @return string the html code for a form save button
     */
    function form_save(): string
    {
        $html = new html_base();
        return $html->button('Save');
    }

    /**
     * @return string that simply closes the form
     */
    function form_end(): string
    {
        $html = new html_base();
        return $html->form_end();
    }


    /*
     * info
     */

    private function type_code_id(): string
    {
        global $component_types;
        return $component_types->code_id($this->type_id());
    }


    /*
     * set and get
     */

    /**
     * set the vars this component bases on the api json array
     * public because it is reused e.g. by the phrase group display object
     * @param array $json_array an api json message
     * @return void
     */
    function set_from_json_array(array $json_array): void
    {
        parent::set_from_json_array($json_array);
        if (array_key_exists(controller::API_FLD_CODE_ID, $json_array)) {
            $this->code_id = $json_array[controller::API_FLD_CODE_ID];
        } else {
            $this->code_id = null;
        }
    }

    /**
     * repeat here the sandbox object function to force to include all component object fields
     * @param array $json_array an api single object json message
     * @return void
     */
    function set_obj_from_json_array(array $json_array): void
    {
        $wrd = new component();
        $wrd->set_from_json_array($json_array);
    }


    /*
     * interface
     */

    /**
     * @return array the json message array to send the updated data to the backend
     * an array is used (instead of a string) to enable combinations of api_array() calls
     */
    function api_array(): array
    {
        $vars = parent::api_array();
        return array_filter($vars, fn($value) => !is_null($value));
    }


    /*
     * to be replaced
     */

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
            $script = controller::DSP_COMPONENT_ADD;
            $fld_ext = '_add';
            $header = $html->text_h2('Create a view element');
        } else {
            $script = controller::DSP_COMPONENT_EDIT;
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
