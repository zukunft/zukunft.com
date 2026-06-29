<?php

/*

    web/component/execute/ui_preview.php - the html user interface components to preview object changes
    ------------------------------------


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

include_once html_paths::EXECUTE . 'ui_base.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::HTML . 'styles.php';
include_once html_paths::SANDBOX . 'db_object.php';
include_once html_paths::SANDBOX . 'sandbox.php';
include_once html_paths::VIEW . 'view.php';
include_once paths::SHARED_CONST_FIELDS . 'fields.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'library.php';
include_once paths::SHARED . 'url_var.php';

use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\html\styles;
use Zukunft\ZukunftCom\main\php\web\sandbox\db_object;
use Zukunft\ZukunftCom\main\php\web\sandbox\sandbox;
use Zukunft\ZukunftCom\main\php\web\view\view;
use Zukunft\ZukunftCom\main\php\shared\const\fields\fields;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\url_var;

class ui_preview extends ui_base
{

    /**
     * show a preview of a view if the changes are confirmed
     * e.g. if the row selection phrase of a component is changed how the view would look after the change is confirmed
     * TODO Prio 2 fill with real code
     * @return string a dummy text
     */
    function view_after(): string
    {
        return 'placeholder for the view after the change';
    }

    /**
     * show the view as miniature to compare the view with the view_after and the changes highlighted
     * TODO Prio 2 fill with real code
     * @return string a dummy text
     */
    function view_before(): string
    {
        return 'placeholder for the view before the change';
    }

    /**
     * title for the table drop zone
     * TODO Prio 2 fill with real code and maybe move
     * @return string a dummy text
     */
    function paste_table(): string
    {
        return 'placeholder for the table drop zone title';
    }

    /**
     * show a drop zone for a table file upload
     * TODO Prio 2 fill with real code and maybe move
     * @return string a dummy text
     */
    function table_body(): string
    {
        return 'placeholder for the table drop zone';
    }

    /**
     * show the description of a named selection
     * e.g. to select the data for an export might get a combination of phrases and views
     *      it should be possible to save this specific combination and save it under a name
     * TODO Prio 2 fill with real code and maybe move
     * @return string a dummy text
     */
    function selection_text(): string
    {
        return 'placeholder for the selection text';
    }

    /**
     * show the heading of a confirm popup combining the translated action and the object class
     * e.g. 'update word' for the confirm update view; the action text is selected by the component
     * via its ui_msg_code_id (e.g. system_popup_title_update) and the class is derived from the object,
     * so this single component replaces the former split into popup_title and popup_class
     *
     * this is the confirm-view analog of form_tile: it shows the heading and then opens the form, so
     * the component must be the first one of the confirm view (ahead of the hidden fields)
     *
     * @param string $form_name the name of the confirm view used as the html form name
     * @param msg_id|null $ui_msg_code_id the message code id of the component using this component type
     * @param db_object|null $dbo the object that is being changed, used for the object class name
     * @return string the html heading line followed by the opening form tag
     */
    function popup_title(string $form_name = '', ?msg_id $ui_msg_code_id = null, ?db_object $dbo = null): string
    {
        global $mtr;
        $html = new html_base();
        $result = '';
        if ($ui_msg_code_id != null) {
            $title = $mtr->txt($ui_msg_code_id);
            if ($dbo != null) {
                $title .= ' ' . library::class_to_name_translated($dbo::class);
            }
            $heading = $html->h4($title, styles::HEADING_INLINE);
            $result = $html->div($heading, styles::HEADING_LINE);
        }
        // open the confirm form after the heading (like form_tile) so the following hidden step field
        // and the confirm button submit together as one post
        $result .= $html->form_start($form_name);
        return $result;
    }

    /**
     * show the class of the object to add or change in a popup form
     * e.g. for a quick add of a word or value something like the translated 'word ' or 'value '
     * TODO Prio 2 fill with real code and maybe move
     * @return string a dummy text
     */
    function popup_class(sandbox $sbx): string
    {
        return library::class_to_name_translated($sbx::class);
    }

    /**
     * show the pending field changes as a centered three column table so the user can confirm them:
     * the field label in the first column, the old 'from' value (grey, from the '8'-prefixed url) in
     * the second and the new 'to' value (in the 'changed' color) in the third; one row per field whose
     * new url value differs from its '8'-prefixed old value. the table is centered and its width follows
     * the config 'side width' screen breakpoints (8/12 very wide, 10/12 wide, 12/12 normal/small)
     *
     * @param array $url_array the parsed url with the new field values and their '8'-prefixed old values
     * @return string the html code of the centered change table, or an empty string if nothing changed
     */
    function popup_changes(array $url_array = [], ?db_object $dbo = null): string
    {
        global $mtr;
        $html = new html_base();
        // carry the pending change forward as hidden inputs so the confirm submit re-posts every edited
        // field; without this url_mapper would reset the fields not posted (e.g. the plural or the
        // share) to their default. the keys owned by the back / confirm components (the view mask, the
        // object id and the process step) are emitted there, and the 8-prefixed old values and
        // 9-prefixed back targets are shown only in the diff, so skip those. the origin mask is kept so
        // the confirm submit tells action_crud which object view to return to after the write
        $skip = [url_var::MASK, url_var::ID, url_var::STEP];
        $hidden = '';
        foreach ($url_array as $key => $val) {
            if (!in_array($key, $skip)
                and !str_starts_with($key, url_var::PRE)
                and !str_starts_with($key, url_var::BACK)) {
                $hidden .= $html->form_hidden($key, (string)$val);
            }
        }
        $rows = $this->change_rows($url_array, $dbo);
        $result = $hidden;
        if ($rows != '') {
            $head = $html->thead($html->tr(
                $html->th($mtr->txt(msg_id::CHANGE_TBL_FIELD))
                . $html->th($mtr->txt(msg_id::CHANGE_TBL_FROM))
                . $html->th($mtr->txt(msg_id::CHANGE_TBL_TO))));
            $result .= $html->div($html->tbl($head . $rows), styles::CHANGE_PREVIEW);
        }
        return $result;
    }

    /**
     * build the change-preview rows for the pending field changes ordered by the object's db field
     * order (sandbox_fld_order) and labelled with the translated db field name (text_db_field);
     * if the object has no field order yet the legacy fixed field set is used as a fallback
     *
     * @param array $url_array the parsed url with the new field values and their '8'-prefixed old values
     * @param db_object|null $dbo the object being changed, used for the db field order and the field labels
     * @return string the html table rows, one per changed field
     */
    private function change_rows(array $url_array, ?db_object $dbo): string
    {
        global $mtr;
        $rows = '';
        $order = $dbo?->sandbox_fld_order() ?? [];
        $url_keys = $dbo?->db_fld_to_url() ?? [];
        if ($order != [] and $url_keys != []) {
            foreach ($order as $db_fld) {
                if (array_key_exists($db_fld, $url_keys)) {
                    $rows .= $this->change_row($url_array, $url_keys[$db_fld], $mtr->text_db_field($db_fld), $db_fld);
                }
            }
        } else {
            // without an object field order derive the label from the url key itself: the human url key
            // is the json field name, which maps to a db field, which the translator turns into the label
            foreach ($this->changed_fields($url_array) as $url_key) {
                $db_fld = json_fields::json_field_to_db_field(url_var::std_to_human($url_key));
                $rows .= $this->change_row($url_array, $url_key, $mtr->text_db_field($db_fld), $db_fld);
            }
        }
        return $rows;
    }

    /**
     * list the standard url keys whose new value differs from the '8'-prefixed old value, i.e. the
     * fields the user actually changed; the keys owned by the back / confirm / crud machinery (the
     * view mask, the object id, the process step and the crud action) and the prefixed old / back
     * values are not object field changes and are therefore not counted
     *
     * @param array $url_array the parsed url with the new field values and their '8'-prefixed old values
     * @return array the standard url keys of the changed fields
     */
    private function changed_fields(array $url_array): array
    {
        $skip = [url_var::MASK, url_var::ID, url_var::STEP, url_var::ACTION];
        $changed = [];
        foreach ($url_array as $key => $val) {
            if (!in_array($key, $skip)
                and !str_starts_with($key, url_var::PRE)
                and !str_starts_with($key, url_var::BACK)
                and (string)$val != (string)($url_array[url_var::PRE . $key] ?? '')) {
                $changed[] = $key;
            }
        }
        return $changed;
    }

    /**
     * build one change-preview table row if the field's new url value differs from its '8'-prefixed old value
     *
     * @param array $url_array the parsed url with the new field values and their '8'-prefixed old values
     * @param string $url_key the url var short key that carries the field value
     * @param string $label the translated field name shown in the first column
     * @param string $db_fld the db field name, used to show the type name instead of the id for a type field
     * @return string the html table row, or an empty string if the field did not change
     */
    private function change_row(array $url_array, string $url_key, string $label, string $db_fld = ''): string
    {
        $html = new html_base();
        $result = '';
        $new = $url_array[$url_key] ?? '';
        $old = $url_array[url_var::PRE . $url_key] ?? '';
        if ($new != $old) {
            $from_text = $this->field_value($db_fld, (string)$old);
            $to_text = $this->field_value($db_fld, (string)$new);
            $field = $html->td($label);
            $from = $html->td('<span class="' . styles::STYLE_GREY . '">' . htmlspecialchars($from_text) . '</span>');
            $to = $html->td('<span class="' . styles::STYLE_CHANGED . '">' . htmlspecialchars($to_text) . '</span>');
            $result = $html->tr($field . $from . $to);
        }
        return $result;
    }

    /**
     * the display text of a single field value: for a type-id field (share, protection, phrase type, ...)
     * the type name from the request cache (or 'not set' when unset), otherwise the raw value unchanged
     *
     * @param string $db_fld the db field name whose value is shown
     * @param string $value the raw url value of the field (a type id for a type field)
     * @return string the value to show to the user
     */
    private function field_value(string $db_fld, string $value): string
    {
        global $ui_sys, $mtr;
        $result = $value;
        $type_list = $ui_sys?->typ_lst_cache?->field_to_type_list($db_fld);
        if ($type_list != null) {
            if ($value == '' or $value == '0') {
                $result = $mtr->txt(msg_id::NOT_SET);
            } else {
                $result = $type_list->name((int)$value);
            }
        } elseif ($db_fld == fields::FLD_VIEW) {
            // the view is a sandbox object, not a type, so resolve its id to the view name via the api
            $result = $this->view_name($value);
        }
        return $result;
    }

    /**
     * the display name of a view id used in the change preview: the view's name loaded via the api,
     * or 'not set' when no view is selected (an empty or zero id)
     *
     * @param string $value the raw url value of the view field (a view id)
     * @return string the view name to show to the user
     */
    private function view_name(string $value): string
    {
        global $mtr;
        $result = $mtr->txt(msg_id::NOT_SET);
        if ($value != '' and $value != '0') {
            $msk = new view();
            $msk->load_by_id((int)$value);
            $result = $msk->name();
        }
        return $result;
    }

    /**
     * show the impact of the pending change centered below the change table:
     * the translated 'impact' word multiplied by a field factor, which for now is the number of
     * changed fields as a placeholder until the real result impact of the change is calculated
     *
     * @param array $url_array the parsed url with the new field values and their '8'-prefixed old values
     * @return string the html code of the centered impact line, or an empty string if nothing changed
     */
    function popup_impact(array $url_array = []): string
    {
        global $mtr;
        $html = new html_base();
        $factor = count($this->changed_fields($url_array));
        $result = '';
        if ($factor > 0) {
            $result = $html->div($mtr->txt(msg_id::POPUP_IMPACT) . ' × ' . $factor, styles::CHANGE_IMPACT);
        }
        return $result;
    }

    /**
     * show the changes in a short form in a popup
     * e.g. if a value should be changed how the results would change
     * TODO Prio 2 fill with real code and maybe move
     * @return string a dummy text
     */
    function view_diff(): string
    {
        return 'placeholder for popup diff';
    }

}
