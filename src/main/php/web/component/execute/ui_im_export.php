<?php

/*

    web/component/execute/ui_im_export.php - html user interface components for im- and export
    --------------------------------------


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

include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::HTML . 'html_selector.php';
include_once html_paths::SANDBOX . 'db_object.php';
include_once html_paths::SANDBOX . 'sandbox_list.php';
include_once html_paths::SHARED_ENUM . 'messages.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::SHARED . 'url_var.php';

use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\html\html_selector;
use Zukunft\ZukunftCom\main\php\web\sandbox\db_object;
use Zukunft\ZukunftCom\main\php\web\sandbox\sandbox_list;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\url_var;

class ui_im_export extends ui_base
{

    /**
     * the html code to select a filename e.g. to upload the file
     * TODO Prio 1 review
     * @param db_object|sandbox_list $dbo the word, triple or formula object that should be shown to the user
     * @param string $form the name of the view which is also used for the html form name
     * @param data_object|null $cfg the context used to create the view
     * @return string with the html code to select a view
     */
    function select_file(
        db_object|sandbox_list $dbo,
        string                 $form,
        data_object|null       $cfg = null
    ): string
    {
        $lst = [];
        // create a name of suggested file names
        if ($cfg != null) {
            if ($cfg->has_file_list()) {
                $lst = $cfg->file_list();
            }
        }
        // select to most likely name
        $name = null;
        if (count($lst) > 0) {
            $name = $lst[0];
        }
        return $dbo->file_selector($form, $name, $lst);
    }

    /**
     * the html code to select the format of the export view that exports in the selected format
     * @param string $form the name of the view which is also used for the html form name
     * @param array $url_arr the url of the page, so that the format selected before stays selected e.g. after a message
     * @return string the html code of the select of the export formats, json by default
     */
    function select_export_format(string $form, array $url_arr): string
    {
        $sel = new html_selector();
        $sel->name = url_var::EXPORT_FORMAT;
        $sel->form = $form;
        $sel->label_id = msg_id::FORM_SELECT_EXPORT_FORMAT;
        $sel->lst = url_var::HUMAN_TO_STD_EXPORT_FORMAT_VAL;
        $sel->selected = $url_arr[url_var::EXPORT_FORMAT] ?? url_var::EXPORT_FORMAT_JSON;
        return $sel->display();
    }

    /**
     * TODO Prio 2 move code from component_dsp_old
     * @return string a dummy text
     */
    function json_export(): string
    {
        return $this->name();
    }

    /**
     * TODO Prio 2 move code from component_dsp_old
     * @return string a dummy text
     */
    function xml_export(): string
    {
        return $this->name();
    }

    /**
     * TODO Prio 2 move code from component_dsp_old
     * @return string a dummy text
     */
    function csv_export(): string
    {
        return $this->name();
    }

    /**
     * TODO Prio 2 move code from component_dsp_old
     * @return string a dummy text
     */
    function ods_export(): string
    {
        return $this->name();
    }

}
