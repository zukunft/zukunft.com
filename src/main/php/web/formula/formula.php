<?php

/*

    /web/formula/formula.php - the display extension of the api formula object
    -----------------------

    to creat the HTML code to display a formula


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

namespace html\formula;

include_once WEB_SANDBOX_PATH . 'sandbox_typed.php';

use controller\controller;
use html\api;
use html\button;
use html\html_base;
use html\msg;
use html\phrase\term as term_dsp;
use html\sandbox_typed_dsp;

class formula extends sandbox_typed_dsp
{

    /*
     * object vars
     */

    // the formula expression as shown to the user
    private string $usr_text;


    /*
     * set and get
     */

    /**
     * repeat here the sandbox object function to force to include all formula object fields
     * @param array $json_array an api single object json message
     * @return void
     */
    function set_obj_from_json_array(array $json_array): void
    {
        $wrd = new formula();
        $wrd->set_from_json_array($json_array);
    }

    /**
     * set the vars this formula bases on the api json array
     * public because it is reused e.g. by the phrase group display object
     * @param array $json_array an api json message
     * @return void
     */
    function set_from_json_array(array $json_array): void
    {
        parent::set_from_json_array($json_array);
        if (array_key_exists(controller::API_FLD_USER_TEXT, $json_array)) {
            $this->set_usr_text($json_array[controller::API_FLD_USER_TEXT]);
        } else {
            $this->set_usr_text(null);
        }
    }

    function set_usr_text(string $usr_text): void
    {
        $this->usr_text = $usr_text;
    }

    function usr_text(): string
    {
        return $this->usr_text;
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

        $vars[controller::API_FLD_USER_TEXT] = $this->usr_text();
        return array_filter($vars, fn($value) => !is_null($value));
    }


    /*
     * cast
     */

    function term(): term_dsp
    {
        $trm = new term_dsp();
        $trm->set_obj($this);
        return $trm;
    }


    /*
     * display
     */

    /**
     * display the formula name with the tooltip
     * @returns string the html code
     */
    function display(): string
    {
        return $this->name();
    }

    /**
     * display the formula name with a link to the main page for the formula
     * @param string|null $back the back trace url for the undo functionality
     * @param string $style the CSS style that should be used
     * @returns string the html code
     */
    function display_linked(?string $back = '', string $style = ''): string
    {
        $html = new html_base();
        $url = $html->url(api::FORMULA, $this->id, $back, api::PAR_VIEW_FORMULAS);
        return $html->ref($url, $this->name(), $this->name(), $style);
    }

    /**
     * @param string|null $back the back trace url for the undo functionality
     * @returns string the html url to change a formula
     */
    function edit_url(?string $back = ''): string
    {
        return $this->obj_url(controller::DSP_FORMULA_EDIT, $back);
    }

    /**
     * display the formula name with a link to change the formula
     * @param string|null $back the back trace url for the undo functionality
     * @returns string the html code
     */
    function edit_link(?string $back = ''): string
    {
        return (new html_base())->ref($this->edit_url($back), $this->name(), $this->name());
    }

    /**
     * create the HTML code for a button to create a new formula
     * @param string $back the stack trace for the undo functionality
     * @return string html code to change to formula
     */
    function btn_add(string $back = ''): string
    {
        $url = $this->obj_url(controller::DSP_FORMULA_ADD, $back);
        return (new button($url, $back))->add(msg::FORMULA_ADD, $this->name);
    }

    /**
     * create the HTML code for a button to change the formula
     * @param string $back the stack trace for the undo functionality
     * @return string html code to change to formula
     */
    function btn_edit(string $back = ''): string
    {
        return (new button('', $this->edit_url($back)))->edit(msg::FORMULA_EDIT, msg::FOR . $this->name);
    }

    /**
     * create the HTML code for a button to delete or exclude this formula
     * @param string $back the stack trace for the undo functionality
     * @return string html code to change to formula
     */
    function btn_del(string $back = ''): string
    {
        $url = $this->obj_url(controller::DSP_FORMULA_DEL, $back);
        return (new button('Change ' . self::class . $this->name, $url))->del();
    }

}
