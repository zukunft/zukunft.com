<?php

/*

    /web/value.php - the display extension of the api value object
    -------------

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

namespace html\value;

include_once WEB_SANDBOX_PATH . 'sandbox_value.php';
include_once API_SANDBOX_PATH . 'sandbox.php';
include_once API_SANDBOX_PATH . 'sandbox_value.php';

use controller\controller;
use api\api;
use html\api as api_dsp;
use html\html_base;
use html\phrase\phrase_group;
use html\phrase\phrase_list as phrase_list_dsp;
use html\figure\figure as figure_dsp;
use html\sandbox\sandbox_value;

class value extends sandbox_value
{


    /**
     * @param phrase_list_dsp $phr_lst_exclude usually the context phrases that does not need to be repeated
     * @return string the HTML code of all phrases linked to the value, but not including the phrase from the $phr_lst_exclude
     */
    function name_linked(phrase_list_dsp $phr_lst_exclude): string
    {
        return $this->grp()->display_linked($phr_lst_exclude);
    }

    /**
     * @return string the formatted value with a link to change this value
     */
    function ref_edit(string $back): string
    {
        $html = new html_base();
        return $html->ref($html->url(api_dsp::VALUE_EDIT, $this->id, $back), $this->val_formatted());
    }


    /*
     * set and get
     */

    /**
     * set the vars of this object bases on the api json string
     * @param string $json_api_msg an api json message as a string
     * @return void
     */
    function set_from_json(string $json_api_msg): void
    {
        $this->set_from_json_array(json_decode($json_api_msg, true));
    }

    /**
     * @param phrase_group $grp
     * @return float
     */
    function get(phrase_group $grp): float
    {
        /*
         * $result = null;
         * check if the value can be returned from frontend cache
         *  // if the group contains a table phrase of the prime pod for the frontend,
         *  if ($grp->has_table_pod_phrase()) {
         *      $tbl_phr = $grp->get_pod_table_phrase()
         *      if (!$tbl_phr->is_cached()) {
         *          $tbl_cache->check_size_and_remove_unneeded();
         *          $tbl_cache->get_from_backend($grp);
         *      }
         *      $result = $tbl_cache->get_number($grp);
         *  // if the group contains a table phrase which is cloned in the prime pod for the frontend,
         *  } elseif ($grp->has_table_clone_phrase()) {
         *      $tbl_phr = $grp->get_clone_table_phrase()
         *      if (!$tbl_phr->is_cached()) {
         *          $tbl_cache->check_size_and_remove_unneeded();
         *          $tbl_cache->get_from_backend($grp);
         *      }
         *      $result = $tbl_cache->get_number($grp);
         *  // if the group up to 4 phrases and all phrases are prime phrases
         *  } elseif ($grp->has_max_4_prime_phrases()) {
         *      if (!$grp->prime_is_cached()) {
         *          $grp_prime_cache->check_size_and_remove_unneeded();
         *          $grp_prime_cache->get_from_backend($grp);
         *      }
         *      $result = $grp_prime_cache->get_number($grp);
         *  // if the grp up to 16 phrases
         *  } elseif ($grp->has_max_16_phrases()) {
         *      if (!$grp->is_cached()) {
         *          $grp_cache->check_size_and_remove_unneeded();
         *          $grp_cache->get_from_backend($grp);
         *      }
         *      $result = $grp_cache->get_number($grp);
         *  // if the group contains more than 16 phrases
         *  } else {
         *      if (!$grp->is_cached()) {
         *          $grp_big_cache->check_size_and_remove_unneeded();
         *          $grp_big_cache->get_from_backend($grp);
         *      }
         *      $result = $grp_big_cache->get_number($grp);
         *  }
         * if the frontend cache is not yet fully used include
         *
         *
         */
        return 0;
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
        $vars[api::FLD_PHRASES] = $this->grp()->phr_lst()->api_array();
        $vars[api::FLD_NUMBER] = $this->number();
        return array_filter($vars, fn($value) => !is_null($value) && $value !== '');
    }


    /*
     * cast
     */

    /**
     * @returns figure_dsp the figure display object base on this value object
     */
    function figure(): figure_dsp
    {
        $fig = new figure_dsp();
        $fig->set_obj($this);
        return $fig;
    }


    /*
     * display
     */

    function display(string $back = ''): string
    {
        if (!$this->is_std()) {
            return '<span class="user_specific">' . $this->val_formatted() . '</span>';
        } else {
            return $this->val_formatted();
        }
    }

    function display_linked(string $back = ''): string
    {
        return $this->ref_edit($back);
    }

}
