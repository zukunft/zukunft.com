<?php

/*

    html_base.php - function to create the basic HTML elements used for zukunft.com
    -------------

    depending on the settings either pure HTML, BOOTSTRAP HTML or vue.js code is created


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

class html_base
{

    const IMG_LOGO = "https://www.zukunft.com/images/ZUKUNFT_logo.svg";

    /*
     * header & footer
     */

    /**
     * @param string $title simple the HTML title used
     * @param string $style e.g. to center for the login page
     * @returns string the general HTML header
     */
    function header(string $title, string $style = ""): string
    {
        $result = '<!DOCTYPE html>';
        $result .= '<html lang="en">'; // TODO: to be adjusted depending on the display language
        if ($title <> "") {
            $result .= '<head><title>' . $title . ' (zukunft.com)</title>';
        } else {
            $result .= '<head><title>zukunft.com</title>';
        }
        $result .= '  <meta charset="utf-8">';
        if (UI_USE_BOOTSTRAP) {
            // include the bootstrap stylesheets
            $result .= '  <link rel="stylesheet" href="https://www.zukunft.com/lib_external/bootstrap/4.3.1/css/bootstrap.css">';
            // include the jQuery UI stylesheets
            $result .= '  <link rel="stylesheet" href="https://www.zukunft.com/lib_external/jQueryUI/1.12.1/jquery-ui.css">';
            // include the jQuery library
            $result .= '  <script src="https://www.zukunft.com/lib_external/jQuery/jquery-3.3.1.js"></script>';
            // include the jQuery UI library
            $result .= '  <script src="https://www.zukunft.com/lib_external/jQueryUI/1.12.1/jquery-ui.js"></script>';
            // include the popper.js library
            $result .= '  <script src="https://www.zukunft.com/lib_external/popper.js/1.14.5/popper.min.js"></script>';
            // include the tether library
            //$result .= '  <script src="https://www.zukunft.com/lib_external/tether/dist/js/tether.min.js"></script>';
            // include the typeahead and Bloodhound JavaScript plugins
            //$result .= '  <script src="https://www.zukunft.com/lib_external/typeahead/bootstrap3-typeahead.js"></script>';
            //$result .= '  <script src="https://www.zukunft.com/lib_external/typeahead/typeahead.bundle.js"></script>';
            // include the bootstrap Tokenfield JavaScript plugins
            // $result .= '  <script src="https://www.zukunft.com/lib_external/bootstrap-tokenfield/dist/bootstrap-tokenfield.js"></script>';
            // include the bootstrap Tokenfield stylesheets
            //$result .= '  <script src="https://www.zukunft.com/lib_external/bootstrap-tokenfield/dist/css/bootstrap-tokenfield.css"></script>';
            // include the bootstrap JavaScript plugins
            $result .= '  <script src="https://www.zukunft.com/lib_external/bootstrap/4.1.3/js/bootstrap.js"></script>';
            // adjust the styles where needed
            $result .= '  <link rel="stylesheet" type="text/css" href="../../../../style/style_bs.css" />';
            // load the icon font
            $result .= '  <link rel="stylesheet" href="https://www.zukunft.com/lib_external/fontawesome/css/all.css">';
            $result .= '  <script defer src="https://www.zukunft.com/lib_external/fontawesome/js/all.js"></script>';
        } else {
            // use a simple stylesheet without Javascript
            $result .= '  <link rel="stylesheet" type="text/css" href="../../../../style/style.css" />';
        }
        $result .= '</head>';
        if (UI_USE_BOOTSTRAP) {
            $result .= '<body>';
            $result .= '  <div class="container">';
        } else {
            if ($style <> "") {
                $result .= '<body class="' . $style . '">';
            } else {
                $result .= '<body>';
            }
        }

        return $result;
    }

    /**
     * @param string $title simple the HTML title used
     * @returns string the simple HTML header for unit tests
     */
    function header_test(string $title): string
    {
        $result = '<!DOCTYPE html>';
        $result .= '<html lang="en">'; // TODO: to be adjusted depending on the display language
        if ($title <> "") {
            $result .= '<head><title>' . $title . ' (zukunft.com)</title>';
        } else {
            $result .= '<head><title>zukunft.com</title>';
        }
        $result .= '  <meta charset="utf-8">';
        if (UI_USE_BOOTSTRAP) {
            // include the bootstrap stylesheets
            $result .= '  <link rel="stylesheet" href="https://www.zukunft.com/lib_external/bootstrap/4.3.1/css/bootstrap.css">';
            $result .= '  <link rel="stylesheet" type="text/css" href="../../../../style/style_bs.css" />';
            // load the icon font
            $result .= '  <link rel="stylesheet" href="https://www.zukunft.com/lib_external/fontawesome/css/all.css">';
        } else {
            // use a simple stylesheet without Javascript
            $result .= '  <link rel="stylesheet" type="text/css" href="../../../../style/style.css" />';
        }
        $result .= '</head>';
        if (UI_USE_BOOTSTRAP) {
            $result .= '<body>';
            $result .= '  <div class="container">';
        } else {
            $result .= '<body>';
        }

        return $result;
    }

    /**
     * @param bool $no_about
     * @returns string the general HTML footer
     */
    function footer(bool $no_about = false): string
    {
        $result = '';
        if (UI_USE_BOOTSTRAP) {
            $result = '    </div>';
        }
        $result .= '  <footer>';
        if (UI_USE_BOOTSTRAP) {
            $result .= '  <div class="text-center">';
        } else {
            $result .= '  <div class="footer">';
        }
        $result .= '<small>';
        if (!$no_about) {
            $result .= $this->ref($this->url("about"), "About") . ' &middot; ';
        }
        $result .= '<a href="/http/privacy_policy.html" title="Privacy Policy">Privacy Policy</a> &middot; ';
        $result .= 'All structured data is available under the <a href="//creativecommons.org/publicdomain/zero/1.0/" title="Definition of the Creative Commons CC0 License">Creative Commons CC0</a> License';
        $result .= ' and the <a href="https://github.com/zukunft/zukunft.com" title="program code">program code</a> under the <a href="https://www.gnu.org/licenses/agpl.html" title="AGPL3">AGPL3</a> License';
        // for the about page this does not make sense
        $result .= '</small>';
        $result .= '</div>';
        $result .= '</footer>';
        $result .= '</body>';
        $result .= '</html>';

        return $result;
    }

    /*
     * wrapper for the basic html elements used
     */

    function ref(string $url, string $name, string $title = '', string $style = ''): string
    {
        $result = '<a href="' . $url . '"';
        if ($title != '') {
            $result .= ' title="' . $title . '"';
        } else {
            $result .= ' title="' . $name . '"';
        }
        if ($style != '') {
            $result .= ' class="' . $style . '"';
        }
        $result .= '>';
        $result .= $name;
        $result .= '</a>';
        return $result;
    }

    /**
     * @param string $text the text that should be formatted
     * @param string $style the CSS class names
     * @return string the html code
     */
    function span(string $text, string $style = ''): string
    {
        return '<span class="' . $style . '">' . $text . '</span>';
    }

    /*
     * wrapper for internal references used in the html code
     */

    /**
     * build an url for link a zukunft.com element
     *
     * @param string $obj_name the object that is requested e.g. a view
     * @param int $id the id of the parameter e.g. 1 for math const
     * @param string $back the back trace calls to return to the original url and for undo
     * @param string $par_name the parameter objects e.g. a phrase
     * @param string $id_ext an additional id parameter e.g. used to link and unlink two objects
     * @return string the created url
     */
    function url(string $obj_name,
                 int $id = 0,
                 string $back = '',
                 string $par_name = '',
                 string $id_ext = ''): string
    {
        $result = api::PATH . $obj_name . api::EXT;
        if ($id <> 0) {
            if ($par_name != '') {
                $result .= '?' . $par_name . '=' . $id;
            } else {
                $result .= '?id=' . $id;
            }
            if ($id_ext != '') {
                $result .= '&' . $id_ext;
            }
        }
        if ($back != '') {
            $result .= '&back=' . $back;
        }
        return $result;
    }

    /*
     * text formatting
     */

    function text_h2(string $title, string $style = ''): string
    {
        $result = '';
        if (UI_USE_BOOTSTRAP) {
            $result .= "<h4>" . $title . "</h4>";
        } else {
            if ($style <> "") {
                $result .= '<h2 class="' . $style . '">' . $title . '</h2>';
            } else {
                $result .= "<h2>" . $title . "</h2>";
            }
        }
        return $result;
    }

    /*
     * images
     */

    /**
     * @returns string the zukunft.com logo with a link to the home page
     */
    function logo(): string
    {
        $result = '';
        if (UI_USE_BOOTSTRAP) {
            $result .= '<a class="navbar-brand" href="/http/view.php" title="zukunft.com">';
            $result .= '<img src="' . self::IMG_LOGO . '" alt="zukunft.com" style="height: 4em;">';
        } else {
            $result .= '<a href="/http/view.php" title="zukunft.com">';
            $result .= '<img src="' . self::IMG_LOGO . '" alt="zukunft.com" style="height: 5em;">';
        }
        $result .= '</a>';
        return $result;
    }

    /**
     * @returns string the increased zukunft.com logo to display it in the center
     */
    function logo_big(): string
    {
        $result = '<a href="/http/view.php" title="zukunft.com Logo">';
        $result .= '<img src="' . self::IMG_LOGO . '" alt="zukunft.com" style="height: 30%;">';
        $result .= '</a>';
        return $result;
    }


    /*
     * table HTML functions used
     */

    function th(string $header_text): string
    {
        return '<th>' . $header_text . '</th>';
    }

    function tr(string $row_text): string
    {
        return '<tr>' . $row_text . '</tr>';
    }

    /**
     * show a text of link within a table cell
     * @param string $cell_text the text or link that should be shown
     * @param int $intent the number of spaces on the left (or right e.g. for arabic) inside the table cell
     * @return string the html code of the table cell
     */
    function td(string $cell_text, int $intent = 0): string
    {
        while ($intent > 0) {
            $cell_text .= '&nbsp;';
            $intent = $intent - 1;
        }
        return '<td>' . $cell_text . '</td>';
    }

    /**
     * create the html code to display a table
     * @param string $tbl_rows the html code of all rows inlcuding the header rows
     * @return string the table html code
     */
    function tbl(string $tbl_rows): string
    {
        return $this->tbl_start() . $tbl_rows . $this->tbl_end();
    }

    private function tbl_start(): string
    {
        if (UI_USE_BOOTSTRAP) {
            $result = '<table class="table table-striped table-bordered">' . "\n";
        } else {
            $result = '<table style="width:' . $this->tbl_width() . '">' . "\n";
        }
        return $result;
    }

    function tbl_start_half(): string
    {
        if (UI_USE_BOOTSTRAP) {
            $result = '<table class="table col-sm-5 table-borderless">' . "\n";
        } else {
            $result = '<table style="width:' . $this->tbl_width_half() . '">' . "\n";
        }
        return $result;
    }

    function tbl_start_hist(): string
    {
        if (UI_USE_BOOTSTRAP) {
            $result = '<table class="table table-borderless text-muted">' . "\n";
        } else {
            $result = '<table class="change_hist"' . "\n";
        }
        return $result;
    }

    /**
     * a table for a list of selectors
     */
    function tbl_start_select(): string
    {
        if (UI_USE_BOOTSTRAP) {
            $result = '<table class="table col-sm-10 table-borderless">' . "\n";
        } else {
            $result = '<table style="width:' . $this->tbl_width_half() . '">' . "\n";
        }
        return $result;
    }

    private function tbl_end(): string
    {
        return '</table>' . "\n";
    }

    /*
     * HTML elements like forms
     */

    /**
     * create the html code to display a table
     * @param string $tbl_rows the html code of all rows including the header rows
     * @return string the table html code
     */
    function form(string $form_name, string $tbl_rows, string $submit_name = '', string $back = '', string $del_call = ''): string
    {
        return $this->form_start($form_name) . $tbl_rows . $this->form_end($submit_name, $back, $del_call);
    }

    /**
     * @param string $row_text the html code that should be wrapped into a form row
     * @return string the html code of the form row
     */
    function fr(string $row_text): string
    {
        return '<div class="' . api::CLASS_FORM_ROW . '">' . $row_text . '</div>';
    }

    /**
     * @param string $field the name of the form field
     * @param string $txt_value the expected value of the form field
     * @param string $label the expected value of the form field
     * @return string the html code of the form field
     */
    function form_text(string $field,
                       ?string $txt_value = '',
                       string $label = '',
                       string $class = api::CLASS_COL_4,
                       string $attribute = ''): string
    {
        $result = '';
        if ($label == '') {
            $label = strtoupper($field[0]) . substr($field, 1) . ':';
        }
        if (UI_USE_BOOTSTRAP) {
            $result .= dsp_form_fld($field, $txt_value, $label, $class, $attribute);
        } else {
            $result .= $field . ': <input type="text" name="' . $field . '" value="' . $txt_value . '">';
        }
        return $result;
    }

    /**
     * start a html form; the form name must be identical with the php script name
     * @param string $form_name the name and id of the form
     * @returns string the HTML code to start a form
     */
    function form_start(string $form_name): string
    {
        // switch on post forms for private values
        // return '<form action="'.$form_name.'.php" method="post" id="'.$form_name.'">';
        return '<form action="' . $form_name . '.php" id="' . $form_name . '">';
    }

    /**
     * add the hidden field
     * @param string $name the internal name of the field
     * @param string $value the value that should be returned
     * @@returns string the html code to add a hidden field
     */
    function form_hidden(string $name, string $value): string
    {
        return '<input type="hidden" name="' . $name . '" value="' . $value . '">';
    }

    /**
     * end a html form
     */
    function form_end($submit_name, $back, $del_call = ''): string
    {
        $result = '';
        if (UI_USE_BOOTSTRAP) {
            if ($submit_name == "") {
                $result .= '<button type="submit" class="btn btn-outline-success btn-space">Save</button>';
            } else {
                $result .= '<button type="submit" class="btn btn-outline-success btn-space">' . $submit_name . '</button>';
            }
            if ($back <> "") {
                if (is_numeric($back)) {
                    $result .= '<a href="/http/view.php?words=' . $back . '" class="btn btn-outline-secondary btn-space" role="button">Cancel</a>';
                } else {
                    $result .= '<a href="' . $back . '" class="btn btn-outline-secondary btn-space" role="button">Cancel</a>';
                }
            }
            if ($del_call <> '') {
                $result .= '<a href="' . $del_call . '" class="btn btn-outline-danger" role="button">delete</a>';
            }
        } else {
            if ($submit_name == "") {
                $result .= '<input type="submit">';
            } else {
                $result .= '<input type="submit" value="' . $submit_name . '">';
            }
            if ($back <> "") {
                $result .= \html\btn_back($back);
            }
            if ($del_call <> "") {
                $result .= \html\btn_del('delete', $del_call);
            }
        }
        $result .= '</form>';
        return $result;
    }

    /**
     * @return string the HTML code of the about page
     */
    function about(): string
    {
        $result = $this->header("", "center_form"); // reset the html code var

        $result .= dsp_form_center();
        $result .= $this->logo_big();
        $result .= '<br><br>';
        $result .= 'is sponsored by <br><br>';
        $result .= 'zukunft.com AG<br>';
        $result .= 'Blumentalstrasse 15<br>';
        $result .= '8707 Uetikon am See<br>';
        $result .= 'Switzerland<br><br>';
        $result .= '<a href="mailto:timon@zukunft.com">timon@zukunft.com</a><br><br>';
        $result .= 'zukunft.com AG also supports the ';
        $result .= $this->ref("https://github.com/zukunft/tream", "Open Source", "github.com link") . ' Portfolio Management System<br><br>';
        $result .= '<a href="https://tream.biz/p4a/applications/tream/" title="TREAM demo">';
        $result .= '<img src="https://www.zukunft.com/images/TREAM_logo.jpg" alt="TREAM" style="height: 20%;">';
        $result .= '</a><br><br>';
        $result .= '</div>   ';
        $result .= $this->footer(true);

        return $result;
    }

    /*
     * output device specific support functions for the pure HTML version
     */

    /**
     * @return string get the normal table width (should be based on the display size)
     */
    private function tbl_width(): string
    {
        return '800px';
    }

    private function tbl_width_half(): string
    {
        return '400px';
    }

    /**
     * @return string display an explaining sub line e.g. (in mio CHF)
     */
    private function line_small($line_text): string
    {
        return "<small>" . $line_text . "</small><br>";
    }

    /**
     * display a list that can be sorted using the fixed field "order_nbr"
     * $sql_result - list of the query results
     */
    function list_sort($sql_result, $id_field, $text_field, $script_name, $script_parameter)
    {
        $result = '';

        $row_nbr = 0;
        $num_rows = mysqli_num_rows($sql_result);
        while ($entry = mysqli_fetch_array($sql_result, MySQLi_ASSOC)) {
            // list of all possible view entries
            $row_nbr = $row_nbr + 1;
            $edit_script = zu_id_to_edit($id_field);
            $result .= '<a href="/http/' . $edit_script . '?id=' . $entry[$id_field] . '&back=' . $script_parameter . '">' . $entry[$text_field] . '</a> ';
            if ($row_nbr > 1) {
                $result .= '<a href="/http/' . $script_name . '?id=' . $script_parameter . '&move_up=' . $entry[$id_field] . '">up</a>';
            }
            if ($row_nbr > 1 and $row_nbr < $num_rows) {
                $result .= '/';
            }
            if ($row_nbr < $num_rows) {
                $result .= '<a href="/http/' . $script_name . '?id=' . $script_parameter . '&move_down=' . $entry[$id_field] . '">down</a>';
            }
            $result .= ' ';
            $result .= \html\btn_del('Delete ' . $text_field, $script_name . '?id=' . $script_parameter . '&del=' . $entry[$id_field]);
            $result .= '<br>';
        }

        return $result;
    }

    /**
     * display a list of elements
     * the list should be paged and the items should be edible
     *
     * e,g, to display a list of verbs
     * similar to the table function, which is used for values and formula results
     *
     * @param array $item_lst a list of objects that have at least an id and a name
     * @param string the class name of the array entries
     * @returns string with the html code to display the list
     */
    function list(array $item_lst, string $item_type, string $back = ''): string
    {
        $result = "";

        foreach ($item_lst as $item) {
            if ($item->id != null) {
                $url = $this->url($item_type . api::UPDATE, $item->id, $back);
                $result .= $this->ref($url, $item->name);
                $result .= '<br>';
            }
        }
        $url_add = $this->url($item_type . api::CREATE, 0, $back);
        $result .= (new button('Add ' . $item_type, $url_add))->add();
        $result .= '<br>';

        return $result;
    }


}
