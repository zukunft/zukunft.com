<?php

/*

    web/view/view_list.php - a list function to create the HTML code to display a view list
    ----------------------

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

namespace Zukunft\ZukunftCom\main\php\web\view;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::FORMULA . 'formula.php';
include_once html_paths::HTML . 'html_selector.php';
include_once html_paths::HTML . 'rest_call.php';
include_once html_paths::REF . 'ref.php';
include_once html_paths::REF . 'source.php';
include_once html_paths::RESULT . 'result.php';
include_once html_paths::SANDBOX . 'ListBase.php';
include_once html_paths::SANDBOX . 'sandbox.php';
include_once html_paths::SYSTEM . 'language.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::VALUE . 'value.php';
include_once html_paths::VERB . 'verb.php';
include_once html_paths::VIEW . 'view.php';
include_once html_paths::WORD . 'triple.php';
include_once html_paths::WORD . 'word.php';
include_once paths::SHARED_CONST . 'rest_ctrl.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_HELPER . 'CombineObject.php';
include_once paths::SHARED_HELPER . 'IdObject.php';
include_once paths::SHARED_HELPER . 'TextIdObject.php';
include_once paths::SHARED_TYPES . 'view_styles.php';
include_once paths::SHARED_TYPES . 'view_types.php';
include_once paths::SHARED . 'api.php';
include_once paths::SHARED . 'url_var.php';

use Zukunft\ZukunftCom\main\php\web\formula\formula;
use Zukunft\ZukunftCom\main\php\web\html\html_selector;
use Zukunft\ZukunftCom\main\php\web\html\rest_call;
use Zukunft\ZukunftCom\main\php\web\ref\ref;
use Zukunft\ZukunftCom\main\php\web\ref\source;
use Zukunft\ZukunftCom\main\php\web\result\result;
use Zukunft\ZukunftCom\main\php\web\sandbox\ListBase;
use Zukunft\ZukunftCom\main\php\web\sandbox\sandbox;
use Zukunft\ZukunftCom\main\php\web\system\language;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\value\value;
use Zukunft\ZukunftCom\main\php\web\verb\verb;
use Zukunft\ZukunftCom\main\php\web\view\view;
use Zukunft\ZukunftCom\main\php\web\word\triple;
use Zukunft\ZukunftCom\main\php\web\word\word;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\helper\CombineObject;
use Zukunft\ZukunftCom\main\php\shared\helper\IdObject;
use Zukunft\ZukunftCom\main\php\shared\helper\TextIdObject;
use Zukunft\ZukunftCom\main\php\shared\types\view_styles;
use Zukunft\ZukunftCom\main\php\shared\types\view_types;
use Zukunft\ZukunftCom\main\php\shared\url_var;

class view_list extends ListBase
{

    /*
     * set and get
     */

    /**
     * set the vars of the view list based on the given json
     * @param array $json_array an api single object json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function api_mapper(array $json_array): user_message
    {
        return parent::api_mapper_list($json_array, new view());
    }

    function get_by_code_id(string $code_id): view|sandbox|IdObject|TextIdObject|CombineObject|null
    {
        // TODO use a hash list
        $result = new view();
        foreach ($this->lst() as $dsp) {
            if ($dsp->code_id == $code_id) {
                $result = $dsp;
            }
        }
        return $result;
    }


    /*
     * load
     */

    function load_by_pattern(string $pattern = '%'): bool
    {
        $result = false;

        $data = array(url_var::PATTERN => $pattern);
        $rest = new rest_call();
        $json_body = $rest->api_get(view_list::class, $data);
        $this->api_mapper($json_body);
        if (!$this->is_empty()) {
            $result = true;
        }
        return $result;
    }

    /**
     * get the views that use this component from the backend
     *
     * @param int $id of the component
     * @return bool true if the load has been successful
     */
    function load_by_component_id(int $id): bool
    {
        $result = false;

        $data = array(url_var::COMPONENT => $id);
        $rest = new rest_call();
        $json_body = $rest->api_get(view_base::class, $data);
        $this->api_mapper($json_body);
        if (!$this->is_empty()) {
            $result = true;
        }
        return $result;
    }


    /*
     * base
     */

    /**
     * @return string with a list of the view names with html links
     * ex. names_linked
     */
    function name_tip(): string
    {
        $views = array();
        foreach ($this->lst() as $msk) {
            $views[] = $msk->name_tip();
        }
        return implode(', ', $views);
    }

    /**
     * @param string $back the back trace url for the undo functionality
     * @return string with a list of the view names with html links
     * ex. names_linked
     */
    function name_link(string $back = ''): string
    {
        return implode(', ', $this->names_linked($back));
    }

    /**
     * @param string $back the back trace url for the undo functionality
     * @return array with a list of the view names with html links
     */
    private function names_linked(string $back = ''): array
    {
        $views = array();
        foreach ($this->lst() as $msk) {
            $views[] = $msk->name_link();
        }
        return $views;
    }


    /*
     * info
     */

    /**
     * get the default view
     * TODO if a phrase can be ranked use the ranking view
     * @param sandbox $sbx the object to which the default view should be found
     * @return int the view id if no view has been selected until now
     */
    function default_id(sandbox $sbx): int
    {
        return match ($sbx::class) {
            word::class => views::WORD_ID,
            verb::class => views::VERB_ID,
            triple::class => views::TRIPLE_ID,
            source::class => views::SOURCE_ID,
            ref::class => views::REF_ID,
            language::class => views::LANGUAGE_ID,
            value::class => views::VALUE_ID,
            formula::class => views::FORMULA_ID,
            result::class => views::RESULT_ID,
            default => views::START_ID
        };
    }


    /*
     * filter
     */

    public function ex_system(): view_list
    {
        return $this->ex_type(view_types::SYSTEM_TYPES);
    }

    public function ex_non_phrase(): view_list
    {
        return $this->ex_type(view_types::NON_PHRASE_TYPES);
    }

    /**
     * excludes the views of the given types from the list
     * @param array $typ_lst list of view_types
     * @return view_list this list excluding the views of the given types
     */
    private function ex_type(array $typ_lst): view_list
    {
        $views = new view_list();
        foreach ($this->lst() as $msk) {
            $code_id = $msk->type_code_id();
            if (!in_array($code_id, $typ_lst)) {
                $views->add($msk);
            }
        }
        return $views;
    }

    /**
     * get only the views of the given type from the list
     * @param string $typ the view_type to select the views
     * @return view_list with the views of the given type
     */
    function only_type(string $typ): view_list
    {
        $views = new view_list();
        foreach ($this->lst() as $msk) {
            $code_id = $msk->type_code_id();
            if ($code_id == $typ) {
                $views->add($msk);
            }
        }
        return $views;
    }


    /*
     * select
     */

    /**
     * add the view list default values to the selector function
     *
     * @param string $form the html form name which must be unique within the html page
     * @param int|string|null $selected the unique database id of the object that has been selected
     * @param string $name the name of this selector which must be unique within the form
     * @param msg_id $label_id the text show to the user
     * @param string $style the formatting code to adjust the formatting
     * @returns string the html code to select a word from this list
     */
    function selector(
        string          $form = '',
        int|string|null $selected = null,
        string          $name = url_var::VIEW,
        msg_id          $label_id = msg_id::FORM_SELECT_VIEW,
        string          $style = view_styles::COL_SM_4,
        string          $type = html_selector::TYPE_SELECT
    ): string
    {
        return parent::selector($form, $selected, $name, $label_id, $style, $type);
    }

    /**
     * create a selection page where the user can select a view that should be used for a view
     */
    /*
    function selector_page($wrd_id, $back): string
    {

        global $db_con;
        $result = '';

        $sql = "SELECT view_id, view_name
                  FROM views
                 WHERE code_id IS NULL
              ORDER BY view_name;";
        $sql = sql_lst_usr("view", $this->user());
        $call = '/http/view.php?words=' . $wrd_id;
        $field = 'new_id';

        //$db_con = New mysql;
        $db_con->usr_id = $this->user()->id();
        $dsp_lst = $db_con->get_old($sql);
        foreach ($dsp_lst as $dsp) {
            $view_id = $dsp['id'];
            $view_name = $dsp['name'];
            if ($view_id == $this->id()) {
                $result .= '<b><a href="' . $call . '&' . $field . '=' . $view_id . '">' . $view_name . '</a></b> ';
            } else {
                $result .= '<a href="' . $call . '&' . $field . '=' . $view_id . '">' . $view_name . '</a> ';
            }
            $call_edit = '/http/view_edit.php?id=' . $view_id . '&word=' . $wrd_id . '&back=' . $back;
            $result .= \html\btn_edit('design the view', $call_edit) . ' ';
            $call_del = '/http/view_del.php?id=' . $view_id . '&word=' . $wrd_id . '&back=' . $back;
            $result .= \html\btn_del('delete the view', $call_del) . ' ';
            $result .= '<br>';
        }

        log_debug('done');
        return $result;
    }
    */

}
