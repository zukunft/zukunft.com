<?php

/*

    web/ref/source.php - the extension of the source API objects to create source base html code
    ------------------

    The main sections of this object are
    - object vars:       the variables of this word object
    - set and get:       to capsule the vars from unexpected changes
    - api:               set the object vars based on the api json message and create a json for the backend
    - base:              html code for the single object vars
    - select:            html code to select parameter like the type


    This file is part of the frontend of zukunft.com - calc with words

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

    Copyright (c) 1995-2025 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

namespace html\ref;

use cfg\const\paths;
use html\const\paths as html_paths;
include_once html_paths::SANDBOX . 'sandbox_code_id.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::HTML . 'rest_ctrl.php';
include_once html_paths::USER . 'user_message.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_TYPES . 'view_styles.php';
include_once paths::SHARED . 'json_fields.php';

use html\html_base;
use html\sandbox\sandbox_code_id;
use html\user\user_message;
use shared\const\views;
use shared\json_fields;
use shared\types\view_styles;

class source extends sandbox_code_id
{

    /*
     * object vars
     */

    private ?string $url;


    /*
     * set and get
     */

    function set_url(?string $url): void
    {
        $this->url = $url;
    }

    function url(): ?string
    {
        return $this->url;
    }


    /*
     * api
     */

    /**
     * set the vars of this source frontend object bases on the api json array
     * @param array $json_array an api json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function api_mapper(array $json_array): user_message
    {
        $usr_msg = parent::api_mapper($json_array);
        if (array_key_exists(json_fields::URL, $json_array)) {
            $this->set_url($json_array[json_fields::URL]);
        } else {
            $this->set_url(null);
        }
        return $usr_msg;
    }

    /**
     * @return array the json message array to send the updated data to the backend
     * an array is used (instead of a string) to enable combinations of api_array() calls
     */
    function api_array(): array
    {
        $vars = parent::api_array();
        $vars[json_fields::URL] = $this->url();
        return array_filter($vars, fn($value) => !is_null($value) && $value !== '');
    }


    /*
     * base
     */

    /**
     * display the source name with the tooltip
     * @returns string the html code
     */
    function name_tip(): string
    {
        return $this->name();
    }

    /**
     * display the source name with a link to the main page for the source
     * @param string|null $back the back trace url for the undo functionality
     * @param string $style the CSS style that should be used
     * @returns string the html code
     */
    function name_link(?string $back = '', string $style = '', int $msk_id = views::SOURCE_ID): string
    {
        return parent::name_link($back, $style, $msk_id);
    }


    /*
     * select
     */

    /**
     * @param string $form_name the name of the html form
     * @return string the html code to select the source type
     */
    private function dsp_select_type(string $form_name): string
    {
        global $html_source_types;
        return $html_source_types->selector($form_name);
    }

    /**
     * @param string $form_name
     * @param string $pattern
     * @return string
     */
    private function source_selector(string $form_name, string $pattern): string
    {
        $src_lst = new source_list();
        $src_lst->load_like($pattern);
        return $src_lst->selector($form_name, $this->id(), 'source', 'please define a source', '');
    }


    /*
     * to review
     */

    // display a html view to change the source name and url
    function dsp_edit(string $back = ''): string
    {
        log_debug($this->dsp_id());
        $html = new html_base();
        $result = '';

        if ($this->id() <= 0) {
            $script = "source_add";
            $result .= $html->dsp_text_h2("Add source");
        } else {
            $script = "source_edit";
            $result .= $html->dsp_text_h2('Edit source "' . $this->name . '"');
        }
        $result .= $html->dsp_form_start($script);
        //$result .= dsp_tbl_start();
        $result .= $html->dsp_form_hidden("id", $this->id());
        $result .= $html->dsp_form_hidden("back", $back);
        $result .= $html->dsp_form_hidden("confirm", 1);
        $result .= $html->dsp_form_fld("name", $this->name, "Source name:");
        $result .= '<tr><td>type   </td><td>' . $this->dsp_select_type($script, $back) . '</td></tr>';
        $result .= $html->dsp_form_fld("url", $this->url(), "URL:");
        $result .= $html->dsp_form_fld("comment", $this->description, "Comment:");
        //$result .= dsp_tbl_end ();
        $result .= $html->dsp_form_end('', $back);

        log_debug('done');
        return $result;
    }

    /**
     * display a selector for the value source
     */
    function dsp_select(string $form_name, string $back): string
    {
        global $usr;
        log_debug($this->dsp_id());
        $result = ''; // reset the html code var

        // for new values assume the last source used, but not for existing values to enable only changing the value, but not setting the source
        if ($this->id() <= 0 and $form_name == "value_add") {
            $this->id = $usr->source_id();
        }

        log_debug("source id used (" . $this->id() . ")");
        $result .= '      taken from ' . $this->source_selector($form_name, '') . ' ';
        $result .= '    <td>' . \html\btn_edit("Rename " . $this->name, '/http/source_edit.php?id=' . $this->id() . '&back=' . $back) . '</td>';
        $result .= '    <td>' . \html\btn_add("Add new source", '/http/source_add.php?back=' . $back) . '</td>';
        return $result;
    }

    public function source_type_selector(string $form_name): string
    {
        global $html_source_types;
        $used_source_type_id = $this->type_id();
        if ($used_source_type_id == null) {
            $used_source_type_id = $html_source_types->default_id();
        }
        return $html_source_types->selector($form_name, $used_source_type_id, 'type', view_styles::COL_SM_4, 'type:');
    }

}
