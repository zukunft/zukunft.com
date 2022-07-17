<?php

/*

    display_html.php - all html code should be in this library
    ----------------

    depending on the settings either pure HTML or BOOTSTRAP HTML code is created

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
            header("Location: view.php?words=" . $back . ""); // go back to the calling page and try to avoid double change script calls
        } else {
            header("Location: " . $back . ""); // go back to the calling page and try to avoid double change script calls
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
        $result .= '<font class="text-danger">' . $err_text . '</font>';
    } else {
        $result .= '<font color="red">' . $err_text . '</font>';
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

// simply to display a single word in a table as a header
function dsp_tbl_head($link_name): string
{
    log_debug('dsp_tbl_head');
    $result = '    <th>' . "\n";
    $result .= '      ' . $link_name . "\n";
    $result .= '    </th>' . "\n";
    return $result;
}

// simply to display a single word in a table as a header
function dsp_tbl_head_right($link_name): string
{
    $result = '    <th class="right_ref">' . "\n";
    $result .= '      ' . $link_name . "\n";
    $result .= '    </th>' . "\n";
    return $result;
}

function dsp_tbl_start(): string
{
    if (UI_USE_BOOTSTRAP) {
        $result = '<table class="table table-striped table-bordered">' . "\n";
    } else {
        $result = '<table style="width:' . dsp_tbl_width() . '">' . "\n";
    }
    return $result;
}

function dsp_tbl_start_half(): string
{
    if (UI_USE_BOOTSTRAP) {
        $result = '<table class="table col-sm-5 table-borderless">' . "\n";
    } else {
        $result = '<table style="width:' . dsp_tbl_width_half() . '">' . "\n";
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
        $result = '<table style="width:' . dsp_tbl_width_half() . '">' . "\n";
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
        $result .= dsp_form_fld($field, $txt_value, $label, $class, $attribute);
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
        $result .= dsp_form_fld($field, $txt_value, $label, $class, $attribute);
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
        $result .= '<input class="form-control" name="' . $field . '" type="' . $field . '" id="' . $field . '" value="' . $txt_value . '" ' . $attribute . '>';
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

// display a file selector form
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
display functions for the unit and integration tests
*/

// display the header for each unit test
function dsp_test_header($headline): void
{
    echo '<br><br><h2>' . $headline . '</h2><br>';
}
