<?php

/*

    web/component/execute/ui_select.php - html interface components to select an object
    -----------------------------------


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

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::PHRASE . 'phrase_list.php';
include_once html_paths::SANDBOX . 'db_object.php';
include_once html_paths::TYPES . 'type_list.php';
include_once html_paths::TYPES . 'type_object.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED . 'url_var.php';

use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\web\sandbox\db_object;
use Zukunft\ZukunftCom\main\php\web\types\type_list;
use Zukunft\ZukunftCom\main\php\web\types\type_object;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\url_var;

class ui_select
{

    /**
     * @return string the name of a phrase and give the user the possibility to change the phrase name
     */
    function phrase_select(
        db_object $phr,
        string $form_name,
        phrase_list $phr_lst
    ): string
    {
        return $phr->phrase_selector($phr_lst, url_var::PHRASE, $form_name, $phr->id());
    }

    /**
     * the html code to select the view for the given object
     * which can also be the component itself
     * so view_select (for the $obj) can call view_selector of this class if $obj is of class component
     * @param db_object $dbo the word, triple or formula object that should be shown to the user
     * @param string $form the name of the view which is also used for the html form name
     * @param data_object|null $cfg the context used to create the view
     * @return string with the html code to select a view
     */
    function view_select(db_object $dbo, string $form, ?data_object $cfg = null): string
    {
        $msk_lst = null;
        // over
        if ($cfg != null) {
            if ($cfg->has_view_list()) {
                $msk_lst = $cfg->view_list();
            }
        }
        if ($msk_lst == null) {
            $msk_lst = $dbo->view_list();
        }
        return $dbo->view_selector($form, $msk_lst);
    }

    /**
     * show a selection list (e.g. of languages) and let the user pick one entry by name
     * each list entry has a database id, a display name, a description and an alternative
     * unique code id; only the name is shown, the database id is submitted as the form value
     * @param db_object|type_object|null $dbo the currently selected backend object; its id() pre-selects the matching list entry
     * @param type_list $lst the list whose entries the user can choose from
     * @param string $form_name the name of the surrounding html form
     * @param string $field_name the form field name carrying the chosen database id on submit
     * @param msg_id $label_id the message id of the label shown above the selector
     * @return string the html code to render the selection list
     */
    function list_select(
        db_object|type_object|null $dbo,
        type_list                  $lst,
        string                     $form_name = '',
        string                     $field_name = url_var::ID,
        msg_id                     $label_id = msg_id::FORM_SELECT
    ): string
    {
        $selected = $dbo?->id();
        return $lst->type_selector($form_name, $selected, $field_name, $label_id);
    }

}
