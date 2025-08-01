<?php

/*

    web/html/button.php - create the html code to display a button to the user
    ------------------

    mainly used to have a common user interface


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

namespace html;

use cfg\const\paths;
use html\const\paths as html_paths;

//include_once html_paths::PHRASE . 'phrase_list.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED . 'library.php';

use shared\library;
use html\phrase\phrase_list;
use shared\enum\messages as msg_id;

class button
{

    const IMG_ADD_FA = "fa-plus-square";
    const IMG_EDIT_FA = "fa-edit";
    const IMG_DEL_FA = "fa-times-circle";
    const IMG_UNDO = paths::REL_IMAGE . 'button_undo.svg';
    const IMG_FIND = paths::REL_IMAGE . 'button_find.svg';
    const IMG_UN_FILTER = paths::REL_IMAGE . 'button_filter_off.svg';
    const IMG_BACK = paths::REL_IMAGE . 'button_back.svg';

    // parameters for the simple buttons
    public string $title = ''; // title to display on mouse over
    public string $call = ''; // url to call if the user clicks
    public string $back = ''; // word id, word name or url that should be called after the action is completed

    /*
     * construct and capsule
     */

    /**
     * @param string $url the url that is called if the button is pressed
     * @param string $back the history of changes by the user to be able to perform correct undo actions
     */
    function __construct(string $url = '', string $back = '')
    {
        $this->call = $url;
        $this->back = $back;
    }


    /*
     * set
     */

    /**
     * set the button user test
     * @param msg_id $ui_msg_id the const message id that indicates what should be shown to the user in the language that he has selected
     * @param string $explain additional information that should be shown to the user
     */
    function set(msg_id $ui_msg_id, string $explain = ''): void
    {
        $this->set_ui_msg($ui_msg_id, $explain);
    }


    /*
     * HTML code
     */

    /**
     * @param string $icon the path of the icon that should be shown
     * @returns string the HTML code to display a button
     */
    private function html(string $icon): string
    {
        return '<a href="' . $this->call . '" title="' . $this->title . '"><img src="' . $icon . '" alt="' . $this->title . '"></a>';
    }

    // same as html but the bootstrap version
    private function html_fa(string $icon): string
    {
        return '<a href="' . $this->call . '" title="' . $this->title . '"><i class="far ' . $icon . '"></i></a>';
    }

    private function set_ui_msg(msg_id $ui_msg_id, string $explain = ''): void
    {
        global $mtr;
        if ($ui_msg_id != '') {
            $this->title = $mtr->txt($ui_msg_id);
        }
        if ($explain != '') {
            $this->title .= $explain;
        }
    }

    // button function to keep the image call on one place
    function add(msg_id $ui_msg_id, string $explain = ''): string
    {
        $this->set_ui_msg($ui_msg_id, $explain);
        return $this->html_fa(self::IMG_ADD_FA);
    } // an add button to create a new entry

    function edit(msg_id $ui_msg_id, string $explain = ''): string
    {
        $this->set_ui_msg($ui_msg_id, $explain);
        return $this->html_fa(self::IMG_EDIT_FA);
    } // an edit button to adjust an entry

    function del(msg_id $ui_msg_id, string $explain = ''): string
    {
        $this->set_ui_msg($ui_msg_id, $explain);
        return $this->html_fa(self::IMG_DEL_FA);
    } // an delete button to remove an entry

    function undo(msg_id $ui_msg_id, string $explain = ''): string
    {
        $this->set_ui_msg($ui_msg_id, $explain);
        return $this->html(self::IMG_UNDO);
    } // an undo button to undo a change (not only the last)

    function find(msg_id $ui_msg_id, string $explain = ''): string
    {
        $this->set_ui_msg($ui_msg_id, $explain);
        return $this->html(self::IMG_FIND);
    } // a find button to search for a word

    function un_filter(msg_id $ui_msg_id, string $explain = ''): string
    {
        $this->set_ui_msg($ui_msg_id, $explain);
        return $this->html(self::IMG_UN_FILTER);
    } // button to remove a filter

    function back_img(): string
    {
        return $this->html(self::IMG_BACK);
    } // button to go back to the original calling page

    /**
     * display a button to go back to the main calling page (several pages have been show to adjust the view of a word, go back to the word not to the view edit pages)
     * $back can be either the id of the last used word or the url path
     */
    function back(string $back = ''): string
    {
        if ($back == '') {
            $back = 1; // temp solution
        }
        $this->title = 'back';
        if (is_numeric($back)) {
            $this->call = '/http/view.php?words=' . $back;
        } else {
            $this->call = $back;
        }
        return $this->back_img();
    }

    /**
     * ask a yes/no question with the default calls
     * @param string $title the text show beside the button
     * @param string $description the text shown inside the button
     * @param string $call the url that should be call if the button is pressed
     * @returns string the HTML code to display a confirm button
     */
    function confirm(string $title, string $description, string $call): string
    {
        $html = new html_base();
        $result = $html->dsp_text_h3($title);
        $result .= $description . '<br><br>';
        $result .= '<a href="' . $call . '&confirm=1" title="Yes">Yes</a> / <a href="' . $call . '&confirm=-1" title="No">No</a>';
        //$result = $title.'<a href="'.$call.'&confirm=1" title="Yes">Yes</a>/<a href="'.$call.'&confirm=-1" title="No">No</a>';
        //$result = '<a href="'.$call.'" onclick="return confirm(\''.$title.'\')">'.$title.'</a>';
        //$result = "<a onClick=\"javascript: return confirm('".$title."');\" href='".$call."'>x</a>";
        return $result;
    }

    /**
     * the old zuh_btn_confirm without description, replace with zuh_btn_confirm
     */
    function yes_no(string $ui_msg_id = '', string $explain = ''): string
    {
        $html = new html_base();
        //zu_debug("button->yes_no ".$this->title.".", 10);

        if ($ui_msg_id != '') {
            // TODO review
            //$ui_msg = new message();
            //$this->title = $ui_msg->txt($ui_msg_id);
            $this->title = '';
        }
        if ($explain != '') {
            $this->title .= $explain;
        }

        $result = $html->dsp_text_h3($this->title);
        $result .= '<a href="' . $this->call . '&confirm=1" title="Yes">Yes</a>/<a href="' . $this->call . '&confirm=-1" title="No">No</a>';
        //$result = $this->title.'<a href="'.$this->call.'&confirm=1" title="Yes">Yes</a>/<a href="'.$this->call.'&confirm=-1" title="No">No</a>';
        //$result = '<a href="'.$this->call.'" onclick="return confirm(\''.$this->title.'\')">'.$this->title.'</a>';
        //$result = "<a onClick=\"javascript: return confirm('".$this->title."');\" href='".$this->call."'>x</a>";
        //zu_debug("button->yes_no ".$this->title." done.", 10);
        return $result;
    }

    /**
     * display a button to add a value
     */
    function add_value($phr_lst, $type_ids, $back): string
    {
        log_debug("button->add_value");
        $lib = new library();

        $url_phr = '';
        if (isset($phr_lst)) {
            if (get_class($phr_lst) <> phrase_list::class) {
                log_err("Object to add must be of type phrase_list, but it is " . get_class($phr_lst) . ".", "button->add_value");
            } else {
                if (!empty($phr_lst->id_lst())) {
                    $this->title = "add new value similar to " . $phr_lst->dsp_name();
                } else {
                    $this->title = "add new value";
                }
                // TODO activate
                //$url_phr = $phr_lst->id_url_long();
            }
        }

        log_debug("type URL");
        $url_type = '';
        if (isset($type_ids)) {
            $url_type = $lib->ids_to_url($type_ids, "type");
        }

        $this->call = '/http/value_add.php?back=' . $back . $url_phr . $url_type;
        $result = $this->add();

        log_debug($result);
        return $result;
    }

    /**
     * similar to btn_add_value, but uses a simple modal box
     */
    function add_value_fast($modal_nbr, $phr_lst, $phr_main, $common_lst, $back): string
    {
        log_debug();
        $result = '';

        $html = new html_base();

        // group the modal box with the button
        $result .= '<div class="container">';

        // build the phrase list for the modal box header
        $phr_time = $phr_lst->time_lst();
        $common_lst_ex_main = clone $common_lst;
        $common_lst_ex_main->del($phr_main);
        $phr_lst_header = clone $phr_lst;
        $phr_lst_header->diff($common_lst_ex_main);
        $phr_lst_header->diff($phr_time);


        // the button to call the modal box
        $result .= '  <button type="button" class="btn btn-outline-secondary" data-toggle="modal" data-target="#val_add' . $modal_nbr . '">';
        $result .= '    ';
        $result .= '  </button>';
        // the modal box itself
        $form_name = '/http/value_add';
        $result .= '  <div class="modal" id="val_add' . $modal_nbr . '">';
        $result .= '    <div class="modal-dialog">';
        $result .= '      <div class="modal-content">';
        $result .= '        <div class="modal-header">';
        $result .= '          <h4 class="modal-title">';
        $result .= '            ' . $phr_lst_header->name_dsp();
        $result .= '          </h4>';
        $result .= '          <button type="button" class="save" data-dismiss="modal">&times;</button>';
        $result .= '        </div>';
        $result .= '        <div class="modal-body">';
        $result .= $html->dsp_form_start($form_name);
        $result .= '            ' . $phr_time->name_dsp();
        $result .= $html->input('phrases', implode(",", $phr_lst->ids()), html_base::INPUT_HIDDEN);
        $result .= $html->input('back', $back, html_base::INPUT_HIDDEN);
        $result .= $html->input('confirm', '1', html_base::INPUT_HIDDEN);
        $result .= $html->input('value', '0', html_base::INPUT_TEXT);
        $result .= '            ' . $common_lst_ex_main->name_dsp();
        $result .= '          </form>';
        $result .= '        </div>';
        $result .= '        <div class="modal-footer">';
        //$result .= dsp_form_end ('', $back);
        $result .= '          <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>';
        $result .= '          <button type="submit" class="btn btn-outline-success"   data-dismiss="modal">Save</button>';
        $result .= '        </div>';
        $result .= '      </div>';
        $result .= '    </div>';
        $result .= '  </div>';

        // close the modal group
        $result .= '</div>';

        log_debug($result);
        return $result;
    }

    /**
     * display a button to adjust a value
     */
    function edit_value($phr_lst, $group_id, $back): string
    {
        log_debug($phr_lst->name() . ",v" . $group_id . ",b" . $back);

        if (!empty($phr_lst->ids)) {
            $this->title = "change the value for " . $phr_lst->name();
        } else {
            $this->title = "change this value";
        }
        $this->call = '/http/value_edit.php?id=' . $group_id . '&back=' . $back;
        $result = $this->edit();
        log_debug($result);
        return $result;
    }

    /**
     * display a button to exclude a value
     */
    function del_value($phr_lst, $group_id, $back): string
    {
        log_debug($phr_lst->name() . ",v" . $group_id . ",b" . $back);

        if (!empty($phr_lst->ids)) {
            $this->title = "delete the value for " . $phr_lst->name();
        } else {
            $this->title = "delete this value";
        }
        $this->call = '/http/value_del.php?id=' . $group_id . '&back=' . $back;
        $result = $this->del(msg_id::DEL);
        log_debug($result);
        return $result;
    }

}

// only to shorten the code the basic buttons as a function without object
// this way only one code line is needed 
function btn_add(string $text, string $url): string
{
    $b = new button($url);
    return $b->add('', $text);
}      // an add button to create a new entry
function btn_edit(string $text, string $url): string
{
    $b = new button($url);
    return $b->edit('', $text);
}     // an edit button to adjust an entry
function btn_del(string $text, string $url): string
{
    $b = new button($url);
    return $b->del('', $text);
}      // an delete button to remove an entry
function btn_undo(string $text, string $url): string
{
    $b = new button($url);
    return $b->undo('', $text);
}     // an undo button to undo a change (not only the last)
function btn_find(string $text, string $url): string
{
    $b = new button($url);
    return $b->find('', $text);
}     // a find button to search for a word
function btn_unfilter(string $text, string $url): string
{
    $b = new button($url);
    return $b->un_filter('', $text);
} // button to remove a filter
function btn_yesno(string $text, string $url): string
{
    $b = new button($url);
    return $b->yes_no('', $text);
}    // button to get the user confirmation
function btn_back($back_link): string
{
    $b = new button($back_link);
    return $b->back($back_link);
} // button to remove a filter


// button to add a new value related to some phrases
function btn_add_value($phr_lst, $type_ids, $back): string
{
    $b = new button();
    return $b->add_value($phr_lst, $type_ids, $back);
}

// similar to btn_add_value, but uses a simple modal box
function btn_add_value_fast($modal_nbr, $phr_lst, $phr_main, $common_lst, $back): string
{
    $b = new button();
    return $b->add_value_fast($modal_nbr, $phr_lst, $phr_main, $common_lst, $back);
}
