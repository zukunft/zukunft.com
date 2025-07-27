<?php

/*

    web/figure/figure_list.php - the display extension of the api figure list object
    --------------------------

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

use cfg\const\paths;
use html\const\paths as html_paths;
include_once html_paths::FIGURE . 'figure.php';
include_once html_paths::SANDBOX . 'list_dsp.php';
include_once html_paths::USER . 'user_message.php';
include_once paths::SHARED_HELPER . 'CombineObject.php';
include_once paths::SHARED_HELPER . 'IdObject.php';
include_once paths::SHARED_HELPER . 'TextIdObject.php';

use html\figure\figure as figure_dsp;
use html\sandbox\list_dsp;
use html\user\user_message;
use shared\helper\CombineObject;
use shared\helper\IdObject;
use shared\helper\TextIdObject;

class figure_list extends list_dsp
{

    /*
     * construct and map
     */

    /**
     * set the vars of this figure list based on the given json
     * @param array $json_array an api single object json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function api_mapper(array $json_array): user_message
    {
        return parent::api_mapper_list($json_array, new figure_dsp());
    }


    /*
     * modify
     */

    /**
     * add a figure to the list
     * @param figure_dsp|IdObject|TextIdObject|CombineObject|null $to_add the figure frontend object that should be added to the list
     * @returns bool true if the figure has been added
     */
    function add(figure_dsp|IdObject|TextIdObject|CombineObject|null $to_add): bool
    {
        $result = false;
        if (!in_array($to_add->id(), $this->id_lst())) {
            $this->add_direct($to_add);
            $this->set_lst_dirty();
            $result = true;
        }
        return $result;
    }


    /*
     * base
     */

    /**
     * @return string with a list of the figure names with html links
     * ex. names_linked
     */
    function display(): string
    {
        $figures = array();
        foreach ($this->lst() as $fig) {
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
        foreach ($this->lst() as $fig) {
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
        foreach ($this->lst() as $val) {
            if ($val != null) {
                $val_dsp = $val->dsp_obj();
                $lst_dsp[] = $val_dsp;
            }
        }

        return new figure_list($lst_dsp);
    }

}
