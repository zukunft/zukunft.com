<?php

/*

    /web/figure/figure_list.php - the display extension of the api figure list object
    ---------------------------

    to creat the HTML code to display a list of figures


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

namespace html\figure;

use api\combine_object_api;
use api\figure_api;
use html\list_dsp;
use html\figure\figure as figure_dsp;
use html\value\value as value_dsp;
use html\result\result as result_dsp;
use model\library;

class figure_list extends list_dsp
{

    /*
     * set and get
     */

    /**
     * set the vars of a figure object based on the given json
     * @param array $json_array an api single object json message
     * @return object a term_dsp with the word or triple set based on the given json
     */
    function set_obj_from_json_array(array $json_array): object
    {
        $fig = null;
        if (array_key_exists(combine_object_api::FLD_CLASS, $json_array)) {
            if ($json_array[combine_object_api::FLD_CLASS] == figure_api::CLASS_VALUE) {
                $val = new value_dsp();
                $val->set_from_json_array($json_array);
                $fig = $val->figure();
            } elseif ($json_array[combine_object_api::FLD_CLASS] == figure_api::CLASS_RESULT) {
                $res = new result_dsp();
                $res->set_from_json_array($json_array);
                $fig = $res->figure();
            } else {
                log_err('class ' . $json_array[combine_object_api::FLD_CLASS] . ' not expected.');
            }
        } else {
            $lib = new library();
            log_err('json key ' . combine_object_api::FLD_CLASS . ' is missing in ' . $lib->dsp_array($json_array));
        }
        return $fig;
    }


    /*
     * modify
     */

    /**
     * add a figure to the list
     * @returns bool true if the figure has been added
     */
    function add(figure_dsp $fig): bool
    {
        $result = false;
        if (!in_array($fig->id(), $this->id_lst())) {
            $this->lst[] = $fig;
            $this->set_lst_dirty();
            $result = true;
        }
        return $result;
    }

    /*
     * display
     */

    /**
     * @return string with a list of the figure names with html links
     * ex. names_linked
     */
    function display(): string
    {
        $figures = array();
        foreach ($this->lst as $fig) {
            $figures[] = $fig->display();
        }
        return implode(', ', $figures);
    }

    /**
     * @param string $back the back trace url for the undo functionality
     * @return string with a list of the figure names with html links
     * ex. names_linked
     */
    function display_linked(string $back = ''): string
    {
        return implode(', ', $this->names_linked($back));
    }

    /**
     * @param string $back the back trace url for the undo functionality
     * @return array with a list of the figure names with html links
     */
    function names_linked(string $back = ''): array
    {
        $names = array();
        foreach ($this->lst as $fig) {
            $names[] = $fig->display_linked();
        }
        return $names;
    }

    /**
     * @returns figure_list the cast object with the HTML code generating functions
     */
    function dsp_obj(): figure_list
    {
        // cast the single list objects
        $lst_dsp = array();
        foreach ($this->lst as $val) {
            if ($val != null) {
                $val_dsp = $val->dsp_obj();
                $lst_dsp[] = $val_dsp;
            }
        }

        return new figure_list($lst_dsp);
    }

}
