<?php

/*

    selector.php - to select a word (or formula or verb)
    ------------

    this should be as easy as possible that's why it got its own class

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

include_once WEB_SYSTEM_PATH . 'messages.php';

use html\system\messages;

class html_selector
{
    const TYPE_SELECT = "select";
    const TYPE_DATALIST = "datalist";

    // the parameters
    public string $name = '';       // the HTML form field name
    public string $form = '';       // the name of the HTML form
    public string $label = '';      // the label of the HTML form
    public string $bs_class = '';   // to add addition class information for the bootstrap version
    public string $attribute = '';  // to add addition attribute information for the bootstrap version e.g. display an disabled selector
    public string $sql = '';        // to deprecate: the list should be filled by the calling object with min objects: query to select the items
    public ?array $lst = null;      // list of objects from which the user can select
    public ?int $selected = null;   // id of the selected object
    public string $dummy_text = ''; // text for the NULL result if allowed
    public string $type = self::TYPE_SELECT;  // the selector type

    function display(): string
    {
        $result = $this->start_selector();

        if ($this->dummy_text == '') {
            $this->dummy_text = (new messages())->txt(messages::PLEASE_SELECT);
        }

        if ($this->selected == 0) {
            if ($this->type == self::TYPE_DATALIST) {
                $result .= '<option data-value="0" selected>' . $this->dummy_text . '</option>';
            } else {
                $result .= '<option value="0" selected>' . $this->dummy_text . '</option>';
            }
        }

        if ($this->count() > 0) {
            foreach ($this->lst as $key => $value) {
                $row_option = '';
                if ($key == $this->selected and $this->selected <> 0) {
                    $row_option = ' selected';
                }
                if ($this->type == self::TYPE_DATALIST) {
                    $result .= '<option data-value="' . $key . '" ' . $row_option . ' >' . $value . '</option>';
                } else {
                    $result .= '<option value="' . $key . '" ' . $row_option . ' >' . $value . '</option>';
                }
            }
        }

        $result .= $this->end_selector();

        return $result;
    }

    /**
     * @returns string the HTML code that starts a selector field
     */
    private function start_selector(): string
    {
        $result = '';
        // 06.11.2019: removed, check the calling functions
        /*
        if ($label == '') {
          $label == $this->name;
        }
        */
        if (UI_USE_BOOTSTRAP) {
            $result .= '<div class="form-group ' . $this->bs_class . '">';
            if ($this->label != "") {
                $result .= '<label for="' . $this->name . '">' . $this->label . '</label>';
            }
            $bs_class = 'form-control';
            /*
            if ($this->bs_class != '') {
                $bs_class .= ' ' . $this->bs_class;
            }
            */
            if ($this->type == self::TYPE_DATALIST) {
                $result .= '<input type="text" list="' . $this->name . '_list" class="' . $bs_class . '" name="' . $this->name . '" form="' . $this->form . '" id="' . $this->name . '" ' . $this->attribute . '>';
                $result .= '<datalist id="' . $this->name . '_list">';
            } else {
                if ($this->form != "") {
                    $result .= '<select class="' . $bs_class . '" name="' . $this->name . '" form="' . $this->form . '" id="' . $this->name . '" ' . $this->attribute . '>';
                } else {
                    $result .= '<select class="' . $bs_class . '" name="' . $this->name . '" id="' . $this->name . '" ' . $this->attribute . '>';
                }
            }
        } else {
            $result .= $this->label . ' <select name="' . $this->name . '" form="' . $this->form . '">';
        }
        return $result;
    }

    private function count(): int
    {
        if ($this->lst != null) {
            return count($this->lst);
        } else {
            return 0;
        }
    }

    /**
     * @returns string the HTML code to end a selector field
     */
    function end_selector(): string {
        if ($this->type == self::TYPE_DATALIST) {
            $result = '</datalist>';
        } else {
            $result = '</select>';
        }
        if (UI_USE_BOOTSTRAP) {
            $result .= '</div>';
        }
        return $result;
    }

}
