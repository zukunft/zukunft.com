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

    // html const used in zukunft.com
    const INPUT_TEXT = 'text';
    const INPUT_SUBMIT = 'submit';
    const INPUT_SEARCH = 'search';
    const INPUT_HIDDEN = 'hidden';

    // bootstrap const used in zukunft.com
    const BS_FORM = 'form-control';
    const BS_BTN = 'btn btn-space';
    const BS_BTN_SUCCESS = 'btn-outline-success';
    const BS_BTN_CANCEL = 'btn-outline-secondary';
    const BS_SM_2 = 'mr-sm-2';


    const IMG_LOGO = "/src/main/resources/images/ZUKUNFT_logo.svg";

    const SIZE_FULL = 'full';
    const SIZE_HALF = 'half';
    const STYLE_BORDERLESS = 'borderless';

    const WIDTH_FULL = '800px';
    const WIDTH_HALF = '400px';

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
            $result .= '  <link rel="stylesheet" type="text/css" href="/src/main/resources/style/style_bs.css" />';
            // load the icon font
            $result .= '  <link rel="stylesheet" href="https://www.zukunft.com/lib_external/fontawesome/css/all.css">';
            $result .= '  <script defer src="https://www.zukunft.com/lib_external/fontawesome/js/all.js"></script>';
        } else {
            // use a simple stylesheet without Javascript
            $result .= '  <link rel="stylesheet" type="text/css" href="/src/main/resources/style/style.css" />';
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
            $result .= '  <link rel="stylesheet" type="text/css" href="/src/main/resources/style/style_bs.css" />';
            // load the icon font
            $result .= '  <link rel="stylesheet" href="https://www.zukunft.com/lib_external/fontawesome/css/all.css">';
        } else {
            // use a simple stylesheet without Javascript
            $result .= '  <link rel="stylesheet" type="text/css" href="/src/main/resources/style/style.css" />';
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
     * @param string|null $back the back trace calls to return to the original url and for undo
     * @param string $par_name the parameter objects e.g. a phrase
     * @param string $id_ext an additional id parameter e.g. used to link and unlink two objects
     * @return string the created url
     */
    function url(string $obj_name,
                 int $id = 0,
                 ?string $back = '',
                 string $par_name = '',
                 string $id_ext = ''): string
    {
        $result = api::PATH_FIXED . $obj_name . api::EXT;
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
     * the HTML table functions used in zukunft.com
     */

    /**
     * show a text of link within a table header cell
     * @param string $header_text the text or link that should be shown
     * @return string the html code of the table header cell
     */
    function th(string $header_text): string
    {
        return '<th>' . $header_text . '</th>';
    }

    /**
     * create a header column text for each string of the given array
     * @param array $header_cols array of the text or link that should be shown
     * @return string the html code of the table header cell
     */
    function th_row(array $header_cols): string
    {
        $header_text = '';
        foreach ($header_cols as $col_name) {
            $header_text .= $this->th($col_name);
        }
        return $header_text;
    }

    /**
     * show the html code as a table row
     * @param string $row_text the text or link that should be shown
     * @return string the html code of the table row
     */
    function tr(string $row_text): string
    {
        return '<tr>' . $row_text . '</tr>';
    }

    /**
     * show a text of link within a table cell
     * @param string|null $cell_text the text or link that should be shown or null to return an empty cell
     * @param int $intent the number of spaces on the left (or right e.g. for arabic) inside the table cell
     * @return string the html code of the table cell
     */
    function td(?string $cell_text = '', int $intent = 0): string
    {
        // just for formatting the html code
        while ($intent > 0) {
            $cell_text .= '&nbsp;';
            $intent = $intent - 1;
        }
        return '<td>' . $cell_text . '</td>';
    }

    /**
     * create the html code to display a table
     * @param string $tbl_rows the html code of all rows including the header rows
     * @param string $tbl_style the size and style of the table
     * @return string the table html code
     */
    function tbl(string $tbl_rows, string $tbl_style = self::SIZE_FULL): string
    {
        return match ($tbl_style) {
            self::SIZE_HALF => $this->tbl_start_half() . $tbl_rows . $this->tbl_end(),
            self::STYLE_BORDERLESS => $this->tbl_start_hist() . $tbl_rows . $this->tbl_end(),
            default => $this->tbl_start() . $tbl_rows . $this->tbl_end(),
        };
    }

    private function tbl_start(): string
    {
        if (UI_USE_BOOTSTRAP) {
            $result = '<table class="table table-striped table-bordered">';
        } else {
            $result = '<table style="width:' . $this->tbl_width() . '">';
        }
        return $result;
    }

    function tbl_start_half(): string
    {
        if (UI_USE_BOOTSTRAP) {
            $result = '<table class="table col-sm-5 table-striped table-bordered">';
        } else {
            $result = '<table style="width:' . $this->tbl_width_half() . '">';
        }
        return $result;
    }

    function tbl_start_hist(): string
    {
        if (UI_USE_BOOTSTRAP) {
            $result = '<table class="table table-borderless text-muted">';
        } else {
            $result = '<table class="change_hist"';
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
     * the form HTML elements used in zukunft.com
     */

    /**
     * create the html code to display a table
     * @param string $form_name the unique name of the html form
     * @param string $tbl_rows the html code of all rows including the header rows
     * @return string the table html code
     */
    function form(
        string $form_name,
        string $tbl_rows,
        string $submit_name = '',
        string $back = '',
        string $del_call = ''): string
    {
        return $this->form_start($form_name) . $tbl_rows . $this->form_end_with_submit($submit_name, $back, $del_call);
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
            $result .= $this->dsp_form_fld($field, $txt_value, $label, $class, $attribute);
        } else {
            $result .= $field . ': <input type="text" name="' . $field . '" value="' . $txt_value . '">';
        }
        return $result;
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
    function form_end_with_submit(string $submit_name, string $back, $del_call = ''): string
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

        $result .= $this->dsp_form_center();
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
        $result .= '<img src="/src/main/resources/images/TREAM_logo.jpg" alt="TREAM" style="height: 20%;">';
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
        return self::WIDTH_FULL;
    }

    private function tbl_width_half(): string
    {
        return self::WIDTH_HALF;
    }

    /**
     * @return string display an explaining sub line e.g. (in mio CHF)
     */
    private function line_small($line_text): string
    {
        return "<small>" . $line_text . "</small><br>";
    }

    /**
     * converts object class name to an edit php script name
     *
     */
    function edit_url(string $class): string
    {
        return $class . api::UPDATE . api::EXT;
    }

    /**
     * display a list that can be sorted using the fixed field "order_nbr"
     * $sql_result - list of the query results
     *
     * @param array $item_lst a list of objects that have at least an id and a name
     */
    function list_sort(
        array  $item_lst,
        string $class,
        string $script_parameter,
        string $back = ''): string
    {
        $result = '';

        $row_nbr = 0;
        $num_rows = count($item_lst);
        foreach ($item_lst as $key =>  $item) {
            // list of all possible view entries
            $row_nbr = $row_nbr + 1;
            $edit_script = $this->edit_url($class);
            $url = $this->url($edit_script, $key, $back);
            $result .= $this->ref($url, $item);
            if ($row_nbr > 1) {
                $url = $this->url($edit_script, $key, $back, '&move_up=' . $key);
                $result .= $this->ref($url, 'up');
            }
            if ($row_nbr > 1 and $row_nbr < $num_rows) {
                $result .= '/';
            }
            if ($row_nbr < $num_rows) {
                $url = $this->url($edit_script, $key, $back, '&move_down=' . $key);
                $result .= $this->ref($url, 'down');
            }
            $result .= ' ';
            $result .= \html\btn_del('Delete ' . $class, $class . '?id=' . $script_parameter . '&del=' . $key);
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
     * @param string $class the object that is requested e.g. a view
     * @param string $back the target for the back / ctrl-z function
     * @returns string with the html code to display the list
     */
    function list(array $item_lst, string $class, string $back = ''): string
    {
        $result = "";

        foreach ($item_lst as $item) {
            if ($item->id != null) {
                $url = $this->url($class . api::UPDATE, $item->id, $back);
                $result .= $this->ref($url, $item->name);
                $result .= '<br>';
            }
        }
        $url_add = $this->url($class . api::CREATE, 0, $back);
        $result .= (new button($url_add, $back))->add($class . api::CREATE);
        $result .= '<br>';

        return $result;
    }

    /*
     * to dismiss / replace
     */

// ------------------------------------------------------------------
// output device specific support functions for the pure HTML version
// ------------------------------------------------------------------

// get the normal table width (should be based on the display size)
    function dsp_tbl_width(): string
    {
        return '800px';
    }

    function dsp_tbl_width_half(): string
    {
        return '400px';
    }

// display an explaining subtitle e.g. (in mio CHF)
    function dsp_line_small($line_text): string
    {
        return "<small>" . $line_text . "</small><br>";
    }


// ------------------------
// single element functions
// ------------------------

// simply to display headline text
    function dsp_text_h1($title, $style = '')
    {
        $result = '';
        if (UI_USE_BOOTSTRAP) {
            $result .= "<h2>" . $title . "</h2>";
        } else {
            if ($style <> "") {
                $result .= '<h1 class="' . $style . '">' . $title . '</h1>';
            } else {
                $result .= "<h1>" . $title . "</h1>";
            }
        }
        return $result;
    }

    function dsp_text_h2($title, $style = ''): string
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

    function dsp_text_h3($title, $style = ''): string
    {
        $result = '';
        if (UI_USE_BOOTSTRAP) {
            $result .= "<h6>" . $title . "</h6>";
        } else {
            if ($style <> "") {
                $result .= '<h3 class="' . $style . '">' . $title . '</h3>';
            } else {
                $result .= '<h3>' . $title . '</h3>';
            }
        }
        return $result;
    }

// after simple add views e.g. for a value automatically go back to the calling page
    function dsp_go_back($back, $usr): string
    {
        log_debug('dsp_go_back(' . $back . ')');

        $result = '';

        if ($back == '') {
            log_err("Internal error: go back page missing.", "dsp_header->dsp_go_back");
            header("Location: view.php?words=1"); // go back to the fallback page
        } else {
            if (is_numeric($back)) {
                header("Location: view.php?words=" . $back); // go back to the calling page and try to avoid double change script calls
            } else {
                header("Location: " . $back); // go back to the calling page and try to avoid double change script calls
            }
        }

        return $result;
    }

// display a simple text button
    function dsp_btn_text($btn_name, $call): string
    {
        $result = '';
        if (UI_USE_BOOTSTRAP) {
            $result .= '<a href="' . $call . '" class="btn btn-outline-secondary btn-space" role="button">' . $btn_name . '</a>';
        } else {
            $result .= '<a href="' . $call . '">' . $btn_name . '</a>';
        }
        return $result;
    }

// simply to display an error text interactively to the user; use this function always for easy redesign of the error messages
    function dsp_err($err_text): string
    {
        $result = '';
        if (UI_USE_BOOTSTRAP) {
            $result .= '<style class="text-danger">' . $err_text . '</style>';
        } else {
            $result .= '<style color="red">' . $err_text . '</style>';
        }
        return $result;
    }

// display a list of elements: replaced b html->list
    function dsp_list($item_lst, $item_type): string
    {
        $result = "";

        $edit_script = $item_type . "_edit.php";
        $add_script = $item_type . "_add.php";
        foreach ($item_lst as $item) {
            $result .= '<a href="/http/' . $edit_script . '?id=' . $item->id . '">' . $item->name . '</a><br> ';
        }
        $result .= \html\btn_add('Add ' . $item_type, $add_script);
        $result .= '<br>';

        return $result;
    }

// display a box with the history and the links
    function dsp_link_hist_box($comp_name, $comp_html,
                               $nbrs_name, $nbrs_html,
                               $hist_name, $hist_html,
                               $link_name, $link_html): string
    {

        $result = "";

        $comp_id = str_replace(' ', '_', strtolower($comp_name));
        $nbrs_id = str_replace(' ', '_', strtolower($nbrs_name));
        $hist_id = str_replace(' ', '_', strtolower($hist_name));
        $link_id = str_replace(' ', '_', strtolower($link_name));

        $result .= '<div class="col-sm-5">';
        $result .= '<ul class="nav nav-tabs">';
        $result .= '  <li class="nav-item">';
        $result .= '    <a class="nav-link active" id="' . $comp_id . '-tab" data-toggle="tab" href="#' . $comp_id . '" role="tab" aria-controls="' . $comp_id . '" aria-selected="true">' . $comp_name . '</a>';
        $result .= '  </li>';
        if ($nbrs_name <> '') {
            $result .= '  <li class="nav-item">';
            $result .= '    <a class="nav-link"        id="' . $nbrs_id . '-tab" data-toggle="tab" href="#' . $nbrs_id . '" role="tab" aria-controls="' . $nbrs_id . '" aria-selected="false">' . $nbrs_name . '</a>';
            $result .= '  </li>';
        }
        $result .= '  <li class="nav-item">';
        $result .= '    <a class="nav-link"        id="' . $hist_id . '-tab" data-toggle="tab" href="#' . $hist_id . '" role="tab" aria-controls="' . $hist_id . '" aria-selected="false">' . $hist_name . '</a>';
        $result .= '  </li>';
        $result .= '  <li class="nav-item">';
        $result .= '    <a class="nav-link"        id="' . $link_id . '-tab" data-toggle="tab" href="#' . $link_id . '" role="tab" aria-controls="' . $link_id . '" aria-selected="false">' . $link_name . '</a>';
        $result .= '  </li>';
        $result .= '</ul>';
        $result .= '<div class="tab-content border-right border-bottom border-left rounded-bottom" id="comp-hist-tab-content">';
        $result .= '  <div class="tab-pane fade active show" id="' . $comp_id . '" role="tabpanel" aria-labelledby="' . $comp_id . '-tab">';
        $result .= '    <div class="container">';
        $result .= $comp_html;
        $result .= '    </div>';
        $result .= '  </div>';
        if ($nbrs_name <> '') {
            $result .= '  <div class="tab-pane fade" id="' . $nbrs_id . '" role="tabpanel" aria-labelledby="' . $nbrs_id . '-tab">';
            $result .= '    <div class="container">';
            $result .= $nbrs_html;
            $result .= '    </div>';
            $result .= '  </div>';
        }
        $result .= '  <div class="tab-pane fade" id="' . $hist_id . '" role="tabpanel" aria-labelledby="' . $hist_id . '-tab">';
        $result .= '    <div class="container">';
        $result .= $hist_html;
        $result .= '    </div>';
        $result .= '  </div>';
        $result .= '  <div class="tab-pane fade" id="' . $link_id . '" role="tabpanel" aria-labelledby="' . $link_id . '-tab">';
        $result .= '    <div class="container">';
        $result .= $link_html;
        $result .= '    </div>';
        $result .= '  </div>';
        $result .= '</div>'; // of tab content

        return $result;
    }

// -----------------------
// table element functions
// -----------------------

    function dsp_tbl_start(): string
    {
        if (UI_USE_BOOTSTRAP) {
            $result = '<table class="table table-striped table-bordered">' . "\n";
        } else {
            $result = '<table style="width:' . $this->dsp_tbl_width() . '">' . "\n";
        }
        return $result;
    }

    function dsp_tbl_start_half(): string
    {
        if (UI_USE_BOOTSTRAP) {
            $result = '<table class="table col-sm-5 table-borderless">' . "\n";
        } else {
            $result = '<table style="width:' . $this->dsp_tbl_width_half() . '">' . "\n";
        }
        return $result;
    }

    function dsp_tbl_start_hist(): string
    {
        if (UI_USE_BOOTSTRAP) {
            $result = '<table class="table table-borderless text-muted">' . "\n";
        } else {
            $result = '<table class="change_hist"' . "\n";
        }
        return $result;
    }

// a table for a list of selectors
    function dsp_tbl_start_select(): string
    {
        if (UI_USE_BOOTSTRAP) {
            $result = '<table class="table col-sm-10 table-borderless">' . "\n";
        } else {
            $result = '<table style="width:' . $this->dsp_tbl_width_half() . '">' . "\n";
        }
        return $result;
    }

    function dsp_tbl_end(): string
    {
        $result = '</table>' . "\n";
        return $result;
    }

// -------------------------
// formula element functions
// -------------------------

// start a html form; the form name must be identical with the php script name
    function dsp_form_start($form_name): string
    {
        // switch on post forms for private values
        // return '<form action="'.$form_name.'.php" method="post" id="'.$form_name.'">';
        return '<form action="' . $form_name . '.php" id="' . $form_name . '">';
    }

// end a html form
    function dsp_form_end($submit_name, $back, $del_call = ''): string
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

    function dsp_form_center(): string
    {
        if (UI_USE_BOOTSTRAP) {
            return '<div class="container text-center">';
        } else {
            return '<div class="center_form">';
        }
    }

// add the element id, which should always be using the field "id"
    function dsp_form_id($id): string
    {
        return '<input type="hidden" name="id" value="' . $id . '">';
    }

// add the hidden field
    function dsp_form_hidden($field, $id): string
    {
        return '<input type="hidden" name="' . $field . '" value="' . $id . '">';
    }

// add the text field to a form
    function dsp_form_text($field, $txt_value, $label, $class = "col-sm-4", $attribute = ''): string
    {
        $result = '';
        if (UI_USE_BOOTSTRAP) {
            $result .= $this->dsp_form_fld($field, $txt_value, $label, $class, $attribute);
        } else {
            $result .= '' . $field . ': <input type="text" name="' . $field . '" value="' . $txt_value . '">';
        }
        return $result;
    }

// add the text big field to a form
    function dsp_form_text_big($field, $txt_value, $label, $class = "col-sm-4", $attribute = ''): string
    {
        $result = '';
        if (UI_USE_BOOTSTRAP) {
            $result .= $this->dsp_form_fld($field, $txt_value, $label, $class, $attribute);
        } else {
            $result .= '' . $field . ': <input type="text" name="' . $field . '" class="resizedTextbox" value="' . $txt_value . '">';
        }
        return $result;
    }

// add the field to a form
    function dsp_form_fld($field, $txt_value, $label, $class = "col-sm-4", $attribute = ''): string
    {
        $result = '';
        if ($label == '') {
            $label = $field;
        }
        if (UI_USE_BOOTSTRAP) {
            $result .= '<div class="form-group ' . $class . '">';
            $result .= '<label for="' . $field . '">' . $label . '</label>';
            $result .= '<input class="form-control" name="' . $field . '" id="' . $field . '" value="' . $txt_value . '" ' . $attribute . '>';
            $result .= '</div>';
        } else {
            $result .= $label . ' <input name="' . $field . '" value="' . $txt_value . '">';
        }
        return $result;
    }

// add the field to a form
    function dsp_form_fld_checkbox($field, $is_checked, $label): string
    {
        $result = '';
        if ($label == '') {
            $label = $field;
        }
        if (UI_USE_BOOTSTRAP) {
            $result .= '<div class="form-check-inline">';
            $result .= '<label class="form-check-label">';
            $result .= '<input class="form-check-input" type="checkbox" name="' . $field . '"';
            if ($is_checked) {
                $result .= ' checked';
            }
            $result .= '>' . $label . '</label>';
            $result .= '</div>';
        } else {
            $result .= '  <input type="checkbox" name="' . $field . '"';
            if ($is_checked) {
                $result .= ' checked';
            }
            $result .= '> ';
            $result .= $label;
        }
        return $result;
    }

    /**
     * display a file selector form
     */
    function dsp_form_file_select(): string
    {
        $result = '';
        /*
        if (UI_USE_BOOTSTRAP) {
          $result .= ' <form>';
          $result .= '  <div class="custom-file">';
          $result .= '    <input type="file" class="custom-file-input" id="fileToUpload">';
          $result .= '    <label class="custom-file-label" for="fileToUpload">Choose file</label>';
          $result .= '  </div>';
          //$result .= '  <button type="submit" id="submit" name="import" class="btn-submit">Import</button>';
          $result .= '</form>';

          $result .= '<script>';
          $result .= '$(".custom-file-input").on("change", function() {';
          $result .= '  var fileName = $(this).val().split("\\\\").pop();';
          $result .= '  $(this).siblings(".custom-file-label").addClass("selected").html(fileName);';
          $result .= '});';
          $result .= '</script> ';
        } else {
        */
        $result .= ' <form action="import.php" method="post" enctype="multipart/form-data">';
        $result .= '   Select JSON to upload:';
        $result .= '   <input type="file" name="fileToUpload" id="fileToUpload">';
        $result .= '   <input type="submit" value="Upload JSON" name="submit">';
        $result .= ' </form>';
        //}
        return $result;
    }


    /*
     * base elements - functions for all html elements used in zukunft.com
     */

    function button(string $text, string $style= '', string $type = ''): string
    {
        if ($style == '') {
            $style = self::BS_BTN_SUCCESS;
        }
        $class = ' class="'. self::BS_BTN . ' ' . $style . '"';
        if ($type == '') {
            $type = self::INPUT_SUBMIT;
        }
        $type = ' type="' . $type . '"';
        return '<button' . $class . $type . '>' . $text . '</button>';
    }

    function label(string $text, string $for = ''): string
    {
        if ($for == '') {
            $for = strtolower($text);
        }
        return '<label for="' . $for . '">' . $text . '</label>';
    }

    function input(
        string $name = '',
        string $value = '',
        string $type = '',
        string $class_add = '',
        string $placeholder = ''): string
    {
        if ($name != '') {
            $id = strtolower($name);
            $name = ' name="' . $name . '"';
        } else {
            $id = '1';
        }
        if ($value != '') {
            $value = ' value="' . $value . '"';
        }
        if ($type == '') {
            $type = self::INPUT_SUBMIT;
        }
        $type = ' type="' . $type . '"';
        if ($class_add != '' and $class_add[0] != ' ') {
            $class_add = ' ' . $class_add;
        }
        $class = ' class="' . self::BS_FORM . $class_add . '"';
        if ($placeholder != '') {
            $placeholder = ' placeholder="' . $placeholder . '"';
        }
        $id = ' id="' . $id . '"';
        return '<input' . $class . $type . $name . $id . $value . $placeholder . '>';
    }

    function div(string $text, string $class = ''): string
    {
        if ($class == '') {
            $class = 'form-group col-sm-4';
        }
        return '<div class="' . $class . '">' . $text . '</div>';
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
        if ($form_name == 'user_edit') {
            $script_name = 'user';
        } else {
            $script_name = $form_name;
        }
        $action = ' action="/http/' . $script_name . '.php"';
        $id = ' id="' . $form_name . '"';

        return '<form' . $action . $id . '>';
    }

    function form_field(string $name, string $value, string $id = ''): string
    {
        $text = $this->label($name) . $this->input($name, $value);
        return $this->div($text);
    }

    /**
     * end a html form
     */
    function form_end(): string
    {
        return '</form>';
    }


    /*
     * display interface
     *
     * all interface functions that should be used
     * depending on the settings either pure HTML, BOOTSTRAP HTML or JavaScript functions are called
     */

    /**
     * display a html text immediately to the user
     * @param string $txt the text that should be should to the user
     */
    function echo_html(string $txt): void
    {
        echo $txt . '<br>';
    }

    /**
     * display a message immediately to the user
     * @param string $txt the text that should be should to the user
     */
    function echo(string $txt): void
    {
        echo $txt;
    }

    /**
     *display a progress bar
     * TODO create a auto refresh page for async processes and the HTML front end without JavaScript
     * TODO create a db table, where the async process can drop the status
     * TODO add the refresh frequency setting to the general and user settings
     */
    function ui_progress($id, $value, $max, $text)
    {
        echo $text;
    }
}
