<?php

/*

    web/result_dsp.php - the display extension of the api result object
    ------------------

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

namespace html\result;

include_once WEB_SANDBOX_PATH . 'sandbox_value.php';
include_once WEB_FIGURE_PATH . 'figure.php';
include_once WEB_FORMULA_PATH . 'formula.php';
include_once WEB_PHRASE_PATH . 'phrase_list.php';
include_once WEB_USER_PATH . 'user_message.php';
include_once WEB_HTML_PATH . 'html_base.php';
include_once SHARED_PATH . 'json_fields.php';
include_once SHARED_PATH . 'library.php';

use html\formula\formula;
use html\html_base;
use html\phrase\phrase_list as phrase_list_dsp;
use html\sandbox\sandbox_value;
use html\figure\figure as figure_dsp;
use html\user\user_message;
use shared\json_fields;
use shared\library;


class result extends sandbox_value
{

    /*
     * set and get
     */

    /**
     * set the vars of this result bases on the api json array
     * public because it is reused e.g. by the phrase group display object
     * @param array $json_array an api json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function set_from_json_array(array $json_array): user_message
    {
        $usr_msg = parent::set_from_json_array($json_array);
        /* TODO add all result fields that are not part of the sandbox value object
        if (array_key_exists(json_fields::USER_TEXT, $json_array)) {
            $this->set_usr_text($json_array[json_fields::USER_TEXT]);
        } else {
            $this->set_usr_text(null);
        }
        */
        return $usr_msg;
    }


    /*
     * display
     */

    /**
     * @param phrase_list_dsp|null $phr_lst_header list of phrases that are shown already in the context e.g. the table header and that should not be shown again
     * @returns string the html code to display the phrase group with reference links
     */
    function display(phrase_list_dsp $phr_lst_header = null): string
    {
        return $this->grp()->name_tip($phr_lst_header);
    }

    /**
     * @param phrase_list_dsp|null $phr_lst_header list of phrases that are shown already in the context e.g. the table header and that should not be shown again
     * @returns string the html code to display the phrase group with reference links
     */
    function display_linked(phrase_list_dsp $phr_lst_header = null): string
    {
        return $this->grp()->name_link_list($phr_lst_header);
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
     * interface
     */

    /**
     * @return array the json message array to send the updated data to the backend
     * an array is used (instead of a string) to enable combinations of api_array() calls
     */
    function api_array(): array
    {
        $vars = parent::api_array();
        $vars[json_fields::PHRASES] = $this->grp()->phr_lst()->api_array();
        $vars[json_fields::NUMBER] = $this->number();
        return array_filter($vars, fn($value) => !is_null($value) && $value !== '');
    }


    /*
     * review
     */

    // explain a formula result to the user
    // create an HTML page that shows different levels of detail information for one formula result to explain to the user how the value is calculated
    function explain($lead_phr_id, $back): string
    {
        $lib = new library();
        $html = new html_base();

        $result = '';

        // display the leading word
        // $lead_wrd =
        // $lead_wrd->id()  = $lead_phr_id;
        // $lead_wrd->usr = $this->user();
        // $lead_wrd->load();
        //$result .= $lead_phr_id->name();

        // build the title
        $title = '';
        // add the words that specify the calculated value to the title
        $val_phr_lst = clone $this->grp()->phrase_list();
        $val_wrd_lst = $val_phr_lst->wrd_lst_all();
        $title .= $lib->dsp_array($val_wrd_lst->api_obj()->ex_measure_and_time_lst()->dsp_obj()->names_linked());
        $time_phr = $lib->dsp_array($val_wrd_lst->dsp_obj()->time_lst()->names_linked());
        if ($time_phr <> '') {
            $title .= ' (' . $time_phr . ')';
        }
        $title .= ': ';
        // add the value  to the title
        $title .= $this->display($back);
        $result .= $html->dsp_text_h1($title);
        log_debug('explain the value for ' . $val_phr_lst->dsp_name() . ' based on ' . $this->src_grp->phrase_list()->dsp_name());

        // display the measure and scaling of the value
        if ($val_wrd_lst->has_percent()) {
            $result .= 'from ' . $val_wrd_lst->api_obj()->measure_scale_lst()->dsp_obj()->display();
        } else {
            $result .= 'in ' . $val_wrd_lst->api_obj()->measure_scale_lst()->dsp_obj()->display();
        }
        $result .= '</br></br>' . "\n";

        // display the formula with links
        $frm = new formula();
        $frm->load_by_id($this->frm->id());
        $frm_html = $frm;
        $result .= ' based on</br>' . $frm_html->name_link($back);
        $result .= ' ' . $frm_html->dsp_text($back) . "\n";
        $result .= ' ' . $frm_html->btn_edit($back) . "\n";
        $result .= '</br></br>' . "\n";

        // load the formula element groups
        // each element group can contain several elements
        // e.g. for <journey time premium offset = "journey time average" / "journey time max premium" "percent">
        // <"journey time max premium" "percent"> is one element group with two elements
        // and these two elements together are used to select the value
        $exp = $frm->expression();
        //$elm_lst = $exp->element_lst ($back);
        $elm_grp_lst = $exp->element_grp_lst($back);
        log_debug("elements loaded (" . $lib->dsp_count($elm_grp_lst->lst()) . " for " . $frm->ref_text() . ")");

        $result .= ' where</br>';

        // check the element consistency and if it fails, create a warning
        if (!$this->src_grp->phrase_list()->is_empty()) {
            log_warning("Missing source words for the calculated value " . $this->dsp_id(), "result->explain");
        } else {

            $elm_nbr = 0;
            foreach ($elm_grp_lst->lst() as $elm_grp) {

                // display the formula element names and create the element group object
                $result .= $elm_grp->dsp_names($back) . ' ';
                log_debug('elm grp name "' . $elm_grp->dsp_names($back) . '" with back "' . $back . '"');


                // exclude the formula word from the words used to select the formula element values
                // so reverse what has been done when saving the result
                $this->src_grp->set_phrase_list(clone $this->src_grp->phrase_list());
                $frm_wrd_id = $frm->name_wrd->id();
                $this->src_grp->phrase_list()->diff_by_ids(array($frm_wrd_id));
                log_debug('formula word "' . $frm->name_wrd->name() . '" excluded from ' . $this->src_grp->phrase_list()->dsp_name());

                // select or guess the element time word if needed
                log_debug('guess the time ... ');
                $elm_time_phr = $this->src_grp->phrase_list()->assume_time();

                $elm_grp->set_phrase_list($this->src_grp->phrase_list());
                $elm_grp->time_phr = $elm_time_phr;
                $elm_grp->usr = $this->user();
                log_debug('words set ' . $elm_grp->phrase_list()->dsp_name() . ' taken from the source and user "' . $elm_grp->usr->name . '"');

                // finally, display the value used in the formula
                $result .= ' = ' . $elm_grp->dsp_values($back);
                $result .= '</br>';
                log_debug('next element');
                $elm_nbr++;
            }
        }

        return $result;
    }

}
