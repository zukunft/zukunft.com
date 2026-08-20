<?php

/*

    web/component/component_list.php - a list function to create the HTML code to display a view component list
    --------------------------------

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

namespace Zukunft\ZukunftCom\main\php\web\component;

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;
include_once html_paths::SANDBOX . 'sandbox_list_named.php';
include_once html_paths::COMPONENT . 'component_exe.php';
include_once html_paths::HELPER . 'config.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::SHARED_CONST . 'views.php';

use Zukunft\ZukunftCom\main\php\web\sandbox\sandbox_list_named;
use Zukunft\ZukunftCom\main\php\web\component\component_exe as component;
use Zukunft\ZukunftCom\main\php\web\helper\config;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\views;

class component_list extends sandbox_list_named
{

    /*
     * set and get
     */

    /**
     * set the vars of these list display objects bases on the api json array
     * @param array $json_array an api list json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function api_mapper(array $json_array): user_message
    {
        return parent::api_mapper_list($json_array, new component());
    }


    /*
     * display
     */

    /**
     * the component names with a link to each component as a comma separated list, sorted by
     * the position of the component in the view, so the list matches the page layout order
     * (unlike the name-sorted parent, because for the components of a view the position order
     * is the meaningful one)
     *
     * @param string $back the back trace url for the undo functionality
     * @param int $limit the max number of component names to add to the list
     *                   (untyped like in the parent, because php does not allow a child
     *                   to add a type to an untyped parent parameter)
     * @param int $msk_id the id of the view used to show a single component
     * @return string the linked component names
     */
    function name_link(
        string $back = '',
        $limit = config::LIMIT_NAME_LIST,
        int    $msk_id = views::COMPONENT_DEFAULT_ID
    ): string
    {
        // sorted by the position in the view and by the name for the components
        // that share a position, so the html order never depends on the api row
        // order (see docs/llm/frontend.md)
        $lst = $this->lst();
        usort($lst, fn(component $a, component $b) => $a->position <=> $b->position
            ?: strcmp($a->name() ?? '', $b->name() ?? ''));
        $names = [];
        foreach ($lst as $cmp) {
            if (count($names) < $limit) {
                $names[] = $cmp->name_link($back, '', $msk_id);
            }
        }
        return implode(', ', $names);
    }

    /*
     * load
     */

    function load_by_view_id(int $id, user_message $msg): bool
    {
        $url = '';
        return true;

    }

}
