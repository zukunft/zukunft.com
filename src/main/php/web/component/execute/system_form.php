<?php

/*

    web/component/execute/system_form.php - function to execute a system form component
    -------------------------------------

    to create the HTML code to display a system form component

    The main sections of this object are
    - object vars:       the variables of this word object


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
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::DB . 'sql_db.php';
include_once html_paths::COMPONENT . 'component.php';
include_once html_paths::COMPONENT . 'component_list.php';
include_once html_paths::FORMULA . 'formula.php';
include_once html_paths::FORMULA . 'formula_list.php';
include_once html_paths::CONST . 'icons.php';
include_once html_paths::CONST . 'def.php';
include_once html_paths::HTML . 'html_names.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::HTML . 'styles.php';
include_once html_paths::REF . 'ref.php';
include_once html_paths::REF . 'source_list.php';
include_once html_paths::SANDBOX . 'combine_named.php';
include_once html_paths::SANDBOX . 'db_object.php';
include_once html_paths::SANDBOX . 'sandbox.php';
include_once html_paths::SANDBOX . 'sandbox_list.php';
include_once html_paths::SYSTEM . 'language.php';
include_once html_paths::PHRASE . 'phrase_list.php';
include_once html_paths::TYPES . 'type_list.php';
include_once html_paths::TYPES . 'type_lists.php';
include_once html_paths::TYPES . 'type_object.php';
include_once html_paths::TYPES . 'view_style_list.php';
include_once html_paths::USER . 'user.php';
include_once html_paths::RESULT . 'result_list.php';
include_once html_paths::VALUE . 'value.php';
include_once html_paths::VALUE . 'value_list.php';
include_once html_paths::VIEW . 'view_list.php';
include_once html_paths::VIEW . 'view_relation.php';
include_once html_paths::WORD . 'triple.php';
include_once html_paths::WORD . 'word.php';
include_once paths::SHARED_CONST . 'components.php';
include_once paths::SHARED_CONST . 'def.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_TYPES . 'view_styles.php';
include_once paths::SHARED . 'api.php';
include_once paths::SHARED . 'url_var.php';
include_once paths::SHARED . 'library.php';
include_once test_paths::CONST . 'word_names.php';

use Zukunft\ZukunftCom\main\php\web\component\component;
use Zukunft\ZukunftCom\main\php\web\component\component_list;
use Zukunft\ZukunftCom\main\php\web\formula\formula;
use Zukunft\ZukunftCom\main\php\web\formula\formula_list;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\html\styles;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\web\ref\ref;
use Zukunft\ZukunftCom\main\php\web\ref\source_list;
use Zukunft\ZukunftCom\main\php\web\sandbox\combine_named;
use Zukunft\ZukunftCom\main\php\web\sandbox\db_object;
use Zukunft\ZukunftCom\main\php\web\sandbox\sandbox;
use Zukunft\ZukunftCom\main\php\web\sandbox\sandbox_list;
use Zukunft\ZukunftCom\main\php\web\system\language;
use Zukunft\ZukunftCom\main\php\web\types\type_list;
use Zukunft\ZukunftCom\main\php\web\types\type_lists;
use Zukunft\ZukunftCom\main\php\web\types\type_object;
use Zukunft\ZukunftCom\main\php\web\user\user;
use Zukunft\ZukunftCom\main\php\web\result\result_list;
use Zukunft\ZukunftCom\main\php\web\value\value;
use Zukunft\ZukunftCom\main\php\web\value\value_list;
use Zukunft\ZukunftCom\main\php\web\view\view_list;
use Zukunft\ZukunftCom\main\php\web\view\view_relation;
use Zukunft\ZukunftCom\main\php\web\word\triple;
use Zukunft\ZukunftCom\main\php\web\word\word;
use Zukunft\ZukunftCom\main\php\web\const\icons;
use Zukunft\ZukunftCom\main\php\shared\api;
use Zukunft\ZukunftCom\main\php\shared\const\components;
use Zukunft\ZukunftCom\main\php\web\const\def as def_ui;
use Zukunft\ZukunftCom\main\php\shared\const\def;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\types\view_styles;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\test\php\const\word_names;

class system_form extends component
{

    /**
     * start an HTML form, show the title and set and set the unique form name
     * @param string $form_name the name of the view which is also used for the html form name
     * @param msg_id|null $ui_msg_code_id the message id of the text that should be shown to the user in the user-specific frontend language
     * @return string the html code to start a new form and display the tile
     */
    function form_tile(string $form_name, ?msg_id $ui_msg_code_id = null): string
    {
        global $mtr;

        $html = new html_base();
        $result = '';
        if ($ui_msg_code_id != null) {
            $result .= $html->text_h2($mtr->txt($ui_msg_code_id));
        }
        $result .= $html->form_start($form_name);
        return $result;
    }

    /**
     * page title of named object with explaining subtitle
     *
     * example outputs:
     * - no related loaded:   "Zurich <edit-icon>"
     * - related limit=2:     "Zurich" /n "is a <city>, <canton>, ... <edit-icon>"
     * - related limit=high:  "Zurich" /n "is a city, canton, Company <edit-icon>"
     * - related symbol:      "CHF" /n "is symbol for <Swiss Franc> <edit-icon>"
     *
     * @param db_object $dbo the object whose name is shown as the page title
     * @param int $max to limit the number of related phrases shown before a "..." link
     * @return string the html code for the page title with the related-phrases and edit links
     */
    function title_named(
        db_object $dbo,
        int       $max = def::LIMIT_RELATED_PER_VERB
    ): string
    {
        // for a named object the page title is simply its name shown big
        return $this->title_box($dbo, $dbo->name(), $max);
    }

    /**
     * the page title for a triple: show the triple name big as the title (not as a link)
     * and the from, verb and to with a link to each word/triple and the verb in the
     * subtitle, with the same edit link and category subtitle as the named title
     *
     * @param triple|db_object $dbo the triple whose name is the title and whose from, verb and to are the subtitle
     * @param int $max to limit the number of related phrases shown before a "..." link
     * @return string the html code for the triple page title
     */
    function title_triple(
        triple|db_object $dbo,
        int              $max = def::LIMIT_RELATED_PER_VERB
    ): string
    {
        // the from/verb/to links move to the subtitle; the title shows the plain triple name
        $from_verb_to = '';
        if ($dbo::class == triple::class) {
            if ($dbo->verb != null) {
                $from_verb_to = $dbo->get_from()?->name_link() . ' '
                    . $dbo->get_verb()->name_link() . ' '
                    . $dbo->get_to()?->name_link();
            } elseif ($dbo->get_from() != null or $dbo->get_to() != null) {
                $from_verb_to = $dbo->get_from()->name_link() . ' '
                    . $dbo->get_to()->name_link();
            }
        }
        return $this->title_box($dbo, $dbo->name(), $max, $from_verb_to);
    }

    /**
     * the page title for a formula: like the named title (formula name big plus the edit link),
     * but the subtitle lists the phrases the formula is assigned to instead of parent phrases
     * (the assigned phrases are rendered by category_subtitle() from the formula's phr_lst)
     *
     * @param db_object $dbo the formula whose name is the title and whose assigned phrases are the subtitle
     * @param int $max to limit the number of assigned phrases shown before a "..." link
     * @return string the html code for the formula page title
     */
    function title_formula(
        db_object $dbo,
        int       $max = def::LIMIT_RELATED_PER_VERB
    ): string
    {
        return $this->title_named($dbo, $max);
    }

    /**
     * the page title for a value: unlike a named object, the heading is not a plain
     * name but the related phrases shown as links (each with the phrase description as
     * tooltip) followed by the value itself, the same way a word title shows its name;
     * the edit link and the type, share and protection subtitle are added by title_box
     *
     * @param db_object $dbo the value whose related phrases and number are the title
     * @param int $max to limit the number of related phrases shown before a "..." link
     * @return string the html code for the value page title
     */
    function title_value(
        db_object $dbo,
        int       $max = def::LIMIT_RELATED_PER_VERB
    ): string
    {
        // the heading shows the related phrases as links with tooltip plus the value
        $heading_content = $dbo->name();
        if ($dbo::class == value::class) {
            $heading_content = $dbo->name_link();
        }
        return $this->title_box($dbo, $heading_content, $max);
    }

    /**
     * the shared page-title box with the edit link and the category, type, share and
     * protection subtitles; the big heading content is the object name, and a triple
     * additionally passes its from/verb/to links shown first in the same subtitle line
     *
     * @param db_object $dbo the object whose page title is shown
     * @param string $heading_content the html shown big in the title heading
     * @param int $max to limit the number of related phrases shown before a "..." link
     * @param string $lead_subtitle optional html prepended to the subtitle (e.g. a triple's from/verb/to links)
     * @return string the html code for the page title
     */
    private function title_box(
        db_object $dbo,
        string    $heading_content,
        int       $max = def::LIMIT_RELATED_PER_VERB,
        string    $lead_subtitle = ''
    ): string
    {
        $html = new html_base();

        $lnk = $this->edit_link($dbo);

        // category subtitle is created based on verbs listed in verbs::CATEGORY_VERBS
        $cat = $this->category_subtitle($dbo, $max);

        // type subtitle with a link to the type page if the object has a non-default type
        $typ = $this->type_subtitle($dbo);
        $cat_typ = $html->concat_category_text($cat, $typ);

        // share and protection subtitle if not default
        $shr = $this->share_subtitle($dbo);
        $ptc = $this->protection_subtitle($dbo);
        $shr_ptc = $html->concat_entry_text($shr, $ptc);

        // join all subtitle parts with the category separator " / "; a triple prepends its
        // from/verb/to links so the whole subtitle stays on one parenthesized line
        $sub_txt = $html->concat_category_text($cat_typ, $shr_ptc);
        $sub_txt = $html->concat_category_text($lead_subtitle, $sub_txt);

        $heading = '<' . html_base::H4 . ' ' . html_base::CLASS_HTML . '="' . styles::HEADING_INLINE . '">'
            . $heading_content . '</' . html_base::H4 . '>';
        $txt = $html->div($heading . $lnk, styles::HEADING_LINE);

        if ($sub_txt !== '') {
            $txt .= $html->div('(' . $sub_txt . ')', styles::SUBTITLE);
        }

        return $html->row_start() . $txt . $html->row_end();
    }

    /**
     * category subtitle for a phrase like "<verb name> <link1>, <link2>, ..."
     *
     * @param db_object $dbo the object whose name is shown as the page title
     * @param int $max to limit the number of related phrases shown before a "..." link
     * @return string the html code for the page title with the related-phrases and edit links
     */
    private function category_subtitle(
        db_object $dbo,
        int       $max = def::LIMIT_RELATED_PER_VERB
    ): string
    {
        $result = '';

        if ($dbo::class == word::class or $dbo::class == triple::class) {
            if ($dbo->phr_lst != null) {
                $result = $dbo->phr_lst->category_subtitle($dbo->phrase(), $max);
            }
        } elseif ($dbo::class == formula::class) {
            // a formula is not verb-linked to its phrases, so show the assigned phrases as a
            // plain comma-separated list of links instead of the verb-grouped category subtitle
            if ($dbo->phr_lst != null) {
                $result = $dbo->phr_lst->assigned_subtitle($max);
            }
        } elseif ($dbo::class == value::class) {
            // a value lists its related phrases already in the title heading, so the
            // subtitle is left to the type, share and protection parts
            $result = '';
        } else {
            $lib = new library();
            log_warning('category_subtitle not yet defined for ' . $lib->class_to_name($dbo::class));
        }
        return $result;
    }

    /**
     * type subtitle for an object with a non-default type e.g. "measure" for a measure word
     * the type name is a link to the type page that shows the other phrases of the same type
     * and the fixed code rules linked to this type
     *
     * @param word|db_object $dbo the object whose name is shown as the page title
     * @return string the html link to the type page or '' if the object has the default type
     */
    private function type_subtitle(word|db_object $dbo): string
    {
        global $ui_sys;
        if (in_array($dbo::class, def_ui::TYPE_CLASSES)) {
            // the type name links to the type page that lists the other phrases of this type
            // and the fixed code rules linked to this phrase type
            // TODO Prio 3 point this to the dedicated phrase type page once it exists
            return $this->type_link($ui_sys?->typ_lst_cache?->class_to_type_list($dbo::class), $dbo->type_id());
        } else {
            return '';
        }
    }

    /**
     * share subtitle for a sandbox object with a non-default share type e.g. "personal"
     *
     * @param sandbox|db_object $dbo the object whose name is shown as the page title
     * @return string the html link to the share type or '' if the object has the default share type
     */
    private function share_subtitle(sandbox|db_object $dbo): string
    {
        global $ui_sys;
        return $this->type_link($ui_sys?->typ_lst_cache?->shr_typ, $dbo->share_id());
    }

    /**
     * protection subtitle for a sandbox object with a non-default protection type e.g. "admin protection"
     *
     * @param sandbox|db_object $dbo the object whose name is shown as the page title
     * @return string the html link to the protection type or '' if the object has the default protection type
     */
    private function protection_subtitle(sandbox|db_object $dbo): string
    {
        global $ui_sys;
        return $this->type_link($ui_sys?->typ_lst_cache?->ptc_typ, $dbo->protection_id());
    }

    /**
     * the link to a type page if the given type is set and is not the default type of the list
     * common part of type_subtitle, share_subtitle and protection_subtitle
     *
     * @param type_list|null $typ_lst the cached type list e.g. the phrase, share or protection types
     * @param int|null $type_id the type id of the object e.g. its type, share or protection id
     * @return string the html link to the type or '' if the type is missing or the default type
     */
    private function type_link(?type_list $typ_lst, ?int $type_id): string
    {
        $result = '';
        if ($typ_lst !== null and $type_id !== null and $type_id != $typ_lst->default_id()) {
            $typ = $typ_lst->get($type_id);
            if ($typ !== null) {
                $result = $typ->name_link();
            }
        }
        return $result;
    }

    /**
     * create a html link to change an object e.g. a word, value or formula
     *
     * @param db_object $dbo any database object that can be changed by the user or an admin
     * @return string for a link icon to change the object
     */
    private function edit_link(db_object $dbo): string
    {
        global $mtr;

        $html = new html_base();
        $url = $html->url_new($dbo::VIEW_EDIT_ID, $dbo->id());
        $icon = '<' . html_base::I . ' ' . html_base::CLASS_HTML . '="' . icons::EDIT . '"></' . html_base::I . '>';
        return $html->ref($url, $icon, $mtr->txt($dbo::MSG_EDIT), styles::HEADING_ICON_INLINE);
    }

    /**
     * create the HTML code to select this and the previous views
     * // TODO Prio 2 review
     *
     * @param int $msk_id the database id of the view that should be shown
     * @param int|string|null $id the database id of the object that should be shown in the view (string is used for the phrase list of values)
     * @param array $url_array the url of the shown view, used to carry forward its '9'-prefixed back
     *                         targets (e.g. the object's own view a confirm view should return to)
     * @return string the html code to include the back trace into the form result
     */
    function form_back(int $msk_id, int|string|null $id, array $url_array = []): string
    {
        $result = '';
        $html = new html_base();
        $result .= $html->input(url_var::MASK, msg_id::FORM_FIELD_MASK, $msk_id, html_base::INPUT_HIDDEN);
        $result .= $html->input(url_var::ID, msg_id::FORM_FIELD_ID, $id, html_base::INPUT_HIDDEN);
        // carry the '9'-prefixed back targets so cancel and the post-action redirect return to where the
        // user came from (the confirm view sets the object's own view + id as the back target)
        foreach ($url_array as $key => $val) {
            if (str_starts_with($key, url_var::BACK)) {
                $result .= $html->form_hidden($key, (string)$val);
            }
        }
        return $result;
    }

    /**
     * // TODO Prio 2 review
     * @return string the html code to check if the form changes has already confirmed by the user
     */
    function form_confirm(int $msk_id = 0): string
    {
        $html = new html_base();
        // on a confirm view the next submit is the confirmation that writes the change, so advance the
        // step to confirmed; on the edit / add / del view it is the save that first asks to confirm
        $step = in_array($msk_id, views::CONFIRM_MASKS_IDS) ? url_var::STEP_CONFIRMED : url_var::STEP_CONFIRM;
        return $html->input(url_var::STEP, msg_id::FORM_FIELD_CONFIRM, $step, html_base::INPUT_HIDDEN);
    }

    /**
     * @return string the html code so that an admin user can overwrite the username
     */
    function admin_form_username(user|db_object $dbo): string
    {
        $html = new html_base();
        return $html->input(
            url_var::USERNAME,
            msg_id::FORM_FIELD_USERNAME,
            $dbo->name(),
            html_base::INPUT_TEXT);
    }

    /**
     * @return string the html code so that an admin user can overwrite the user email
     */
    function admin_form_user_email(user|db_object $dbo): string
    {
        $html = new html_base();
        return $html->input(
            url_var::EMAIL,
            msg_id::FORM_FIELD_USER_EMAIL,
            $dbo->email,
            html_base::INPUT_EMAIL);
    }

    /**
     * @return string the html code so that an admin user can overwrite the user password
     */
    function admin_form_user_password(user|db_object $dbo): string
    {
        $html = new html_base();
        return $html->input(
            url_var::USER_PASSWORD,
            msg_id::FORM_FIELD_USER_PASSWORD,
            $dbo->password(),
            html_base::INPUT_PASSWORD);
    }

    /**
     * @return string the html code so that an admin can overwrite the language symbol
     */
    function admin_form_language_symbol(language|db_object $dbo): string
    {
        $html = new html_base();
        return $html->input(
            url_var::LANGUAGE_SYMBOL,
            msg_id::FORM_FIELD_LANGUAGE_SYMBOL,
            'symbol field missing',
            html_base::INPUT_TEXT);
    }

    /**
     * @return string the html code to show the language symbol
     */
    function show_language_symbol(language|db_object $dbo): string
    {
        // TODO Prio 0 add system to web language
        return $dbo->name;
    }


    /**
     * show the name of an object to the user
     * @param db_object|type_object $dbo the object
     * @param string $code_id e.g. to select the name in case of a link object
     * @return string the html code to show the object name to the user
     */
    function show_name(db_object|type_object $dbo, string $code_id = ''): string
    {
        if ($code_id == '') {
            return $dbo->name();
        } elseif ($code_id == 'show_field_formula_name') {
            return $dbo->formula_name();
        } elseif ($code_id == 'show_field_phrase_name') {
            return $dbo->phrase_name();
        } else {
            log_warning('code id ' . $code_id . ' not yet defined in show_name');
            return $dbo->name();
        }
    }

    /**
     * @param db_object|type_object $dbo the object
     * @return string the html code to show the object description to the user
     */
    function show_description(db_object|type_object $dbo): string
    {
        return $dbo->get_description();
    }

    /**
     * @param word|db_object $dbo the word
     * @return string the plural form of the word as read-only text (empty if no plural is set)
     */
    function show_plural(word|db_object $dbo): string
    {
        return $dbo->plural ?? '';
    }

    /**
     * @param word|db_object $dbo the word
     * @return string the user-readable name of the word's phrase type (empty if no type is set)
     */
    function show_phrase_type(word|db_object $dbo): string
    {
        global $ui_sys;

        $result = '';
        $type_id = $dbo->type_id();
        if ($type_id !== null) {
            $result = $ui_sys->typ_lst_cache->phr_typ->name($type_id);
        }
        return $result;
    }

    /**
     * @param ref|db_object $dbo the object
     * @return string the html code to show the object reference type to the user
     */
    function show_ref_type(ref|db_object $dbo): string
    {
        return $dbo->type_name();
    }

    /**
     * @param ref|db_object $dbo the object
     * @return string the html code to show the object reference type to the user
     */
    function show_ref_key(ref|db_object $dbo): string
    {
        return $dbo->external_key();
    }

    /**
     * @param ref|db_object $dbo the object
     * @return string the html code to show the object reference type to the user
     */
    function show_ref_source(ref|db_object $dbo): string
    {
        $src_txt = $dbo->source_name();
        if ($src_txt == null) {
            $src_txt = '';
        }
        return $src_txt;
    }

    /**
     * @param ref|db_object $dbo the object
     * @return string the html code to show the object reference type to the user
     */
    function show_ref_url(ref|db_object $dbo): string
    {
        return $dbo->url();
    }

    /**
     * TODO Prio 1 fill with the correct field
     * @param db_object $dbo the object
     * @return string the html code to show the object name to the user
     */
    function show_usage(db_object $dbo): string
    {
        return $dbo->name();
    }

    /**
     * @param view_relation|db_object $dbo the object
     * @return string|null the html code to show the object name to the user
     */
    function show_parent_view(view_relation|db_object $dbo): string|null
    {
        return $dbo->parent()?->name();
    }

    /**
     * @param view_relation|db_object $dbo the object
     * @return string|null the html code to show the object name to the user
     */
    function show_child_view(view_relation|db_object $dbo): string|null
    {
        return $dbo->child()?->name();
    }

    /**
     * @param view_relation|db_object $dbo the object
     * @return string|null the html code to show the object name to the user
     */
    function show_relation_type(view_relation|db_object $dbo): string|null
    {
        return $dbo->relation_type()?->name();
    }

    /**
     * @param view_relation|db_object $dbo the object
     * @return string|null the html code to show the object name to the user
     */
    function show_start_pos(view_relation|db_object $dbo): string|null
    {
        return $dbo->start_pos;
    }

    /**
     * TODO Prio 1 fill with the correct field
     * @param db_object $dbo the object
     * @return string the html code to show the object name to the user
     */
    function result(db_object $dbo): string
    {
        return $dbo->name();
    }

    /**
     * TODO Prio 1 fill with the correct field
     * @param db_object $dbo the object
     * @return string the html code to show the object name to the user
     */
    function used_as_text(db_object $dbo): string
    {
        return $dbo->name();
    }

    /**
     * TODO Prio 1 fill with the correct field
     * @param db_object $dbo the object
     * @return string the html code to show the object name to the user
     */
    function used_as_text_link(db_object $dbo): string
    {
        return $dbo->name();
    }

    /**
     * @param db_object|type_object $dbo the object
     * @return string the html code to request the object name from the user
     */
    /**
     * an editable text field of an edit / add form that also sends the unchanged db value as the
     * '8'-prefixed pre value, so the confirm view can show the value before the change and detect which
     * fields the user actually changed (see url_var::PRE)
     *
     * TODO Prio 1 send the '8'-prefixed pre value for all editable fields, not only the text fields
     *   (name, description, plural): also the selects (phrase type, share, protection, view, and the
     *   triple from / verb / to) so the confirm diff is complete for every object type and field
     *
     * @param string $url_id the url var name of the field e.g. url_var::NAME
     * @param msg_id $label the field label message id
     * @param string|null $value the current db value shown in the field and kept as the pre value
     * @param string $style_text the column style of the field
     * @return string the html code of the editable field plus the hidden pre value
     */
    private function form_field_tracked(string $url_id, msg_id $label, ?string $value, string $style_text): string
    {
        $html = new html_base();
        $value = $value ?? '';
        return $html->form_field($url_id, $label, $value, html_base::INPUT_TEXT, '', $style_text)
            . $html->form_hidden(url_var::PRE . $url_id, $value);
    }

    function form_name(db_object|type_object $dbo, string $style_text): string
    {
        return $this->form_field_tracked(url_var::NAME, msg_id::FORM_FIELD_NAME, $dbo->name(), $style_text);
    }

    /**
     * @param db_object|type_object $dbo
     * @return string the html code to request the description from the user
     */
    function form_description(db_object|type_object $dbo): string
    {
        return $this->form_field_tracked(
            url_var::DESCRIPTION, msg_id::FORM_FIELD_DESCRIPTION, $dbo->get_description(), view_styles::COL_SM_12);
    }

    /**
     * @param db_object $dbo the object
     * @return string the html code to request the object plural from the user
     */
    function form_field_plural(db_object $dbo, string $style_text): string
    {
        return $this->form_field_tracked(url_var::PLURAL, msg_id::FORM_FIELD_PLURAL, $dbo->get_plural(), $style_text);
    }

    /**
     * request the verb name if used the other way round
     * e.g. if Zurich is part of Switzerland, Switzerland contains Zurich and "contains" is the reverse name for "ia part of"
     * @param db_object $dbo the object
     * @return string the html code to request the verb name used if the triple is used the other way round
     */
    function form_field_reverse(db_object $dbo, string $style_text): string
    {
        $html = new html_base();
        $reverse = $dbo->reverse();
        if ($reverse == null) {
            $reverse = '';
        }
        return $html->form_field(
            url_var::REVERSE,
            msg_id::FORM_FIELD_REVERSE,
            $reverse,
            html_base::INPUT_TEXT,
            '', $style_text
        );
    }

    /**
     * request the verb name if used the other way round
     * e.g. if Zurich is part of Switzerland, Switzerland contains Zurich and "contains" is the reverse name for "ia part of"
     * @param db_object $dbo the object
     * @return string the html code to request the verb name used if the triple is used the other way round
     */
    function form_field_plural_reverse(db_object $dbo, string $style_text): string
    {
        $html = new html_base();
        $reverse = $dbo->plural_reverse();
        if ($reverse == null) {
            $reverse = '';
        }
        return $html->form_field(
            url_var::REVERSE_PLURAL,
            msg_id::FORM_FIELD_PLURAL_REVERSE,
            $reverse,
            html_base::INPUT_TEXT,
            '', $style_text
        );
    }

    /**
     * request the verb name if used in a formula
     * @param db_object $dbo the object
     * @return string the html code to request the verb name used in a formula
     */
    function form_field_name_in_formulas(db_object $dbo, string $style_text): string
    {
        $html = new html_base();
        $frm_name = $dbo->formula_name();
        if ($frm_name == null) {
            $frm_name = '';
        }
        return $html->form_field(
            url_var::NAME_IN_FORMULA,
            msg_id::FORM_FIELD_PLURAL_REVERSE,
            $frm_name,
            html_base::INPUT_TEXT,
            '', $style_text
        );
    }

    /**
     * request the external kay of a reference
     * @param ref|db_object $dbo the reference object
     * @return string the html code to request the verb name used in a formula
     */
    function form_field_ref_key(ref|db_object $dbo, string $style_text): string
    {
        $html = new html_base();
        $ref_key = $dbo->external_key();
        if ($ref_key == null) {
            $ref_key = '';
        }
        return $html->form_field(
            url_var::EXTERNAL_KEY,
            msg_id::FORM_FIELD_EXTERNAL_KEY,
            $ref_key,
            html_base::INPUT_TEXT,
            '', $style_text
        );
    }

    /**
     * edit field for the triple weight
     * @param triple|db_object $trp the triple object
     * @return string the html code to request the triple weight from the user
     */
    function form_field_weight(triple|db_object $trp): string
    {
        $html = new html_base();
        $weight = $trp->weight;
        if ($weight == null) {
            $weight = '';
        }
        return $html->form_field(
            url_var::WEIGHT,
            msg_id::FORM_FIELD_WEIGHT,
            $weight,
            html_base::INPUT_PERCENT,
            '', view_styles::COL_SM_1
        );
    }

    /**
     * @param db_object $dbo the object
     * @return string the html code to request a numeric value from the user
     */
    function form_num_value(db_object $dbo, string $style_text): string
    {
        $html = new html_base();
        $val_txt = $dbo->value();
        if ($val_txt == null) {
            $val_txt = '';
        }
        return $html->form_field(
            url_var::VALUE,
            msg_id::FORM_FIELD_VALUE,
            $val_txt,
            html_base::INPUT_NUMBER,
            '', $style_text
        );
    }

    /**
     * @return string the html code to request a url from the user
     */
    function form_field_url(db_object $dbo, string $style_text = ''): string
    {
        $html = new html_base();
        $url = $dbo->url();
        if ($url == null) {
            $url = '';
        }
        if ($style_text == '') {
            $style_text = view_styles::COL_SM_12;
        }
        return $html->form_field(
            url_var::URL,
            msg_id::FORM_FIELD_URL,
            $url,
            html_base::INPUT_TEXT,
            '',
            $style_text
        );
    }

    /**
     * @return string the html code to request the group name
     */
    function form_field_group_name(db_object $dbo): string
    {
        $html = new html_base();
        return $html->form_field(
            url_var::GROUP_NAME,
            msg_id::FORM_FIELD_GROUP,
            $dbo->name(),
            html_base::INPUT_TEXT,
            '',
            view_styles::COL_SM_8
        );
    }

    /**
     * @return string the html code to request the source group name
     */
    function form_field_source_group_name(db_object $dbo): string
    {
        $html = new html_base();
        return $html->form_field(
            url_var::GROUP_NAME,
            msg_id::FORM_FIELD_GROUP,
            $dbo->name(),
            html_base::INPUT_TEXT,
            '',
            view_styles::COL_SM_8
        );
    }

    /**
     * @return string the html code to request the group name or a list of phrases
     */
    function form_field_group_or_phrases(db_object $dbo): string
    {
        $html = new html_base();
        return $html->form_field(
            url_var::GROUP_NAME,
            msg_id::FORM_FIELD_GROUP,
            $dbo->name(),
            html_base::INPUT_TEXT,
            '',
            view_styles::COL_SM_8
        );
    }

    /**
     * @return string the html code to request the group name or a list of phrases
     */
    function form_field_source_group_or_phrases(db_object $dbo): string
    {
        $html = new html_base();
        return $html->form_field(
            url_var::GROUP_NAME,
            msg_id::FORM_FIELD_GROUP,
            $dbo->name(),
            html_base::INPUT_TEXT,
            '',
            view_styles::COL_SM_8
        );
    }

    /**
     * @return string the html code to request the formula link priority
     */
    function form_field_formula_link_priority(db_object $dbo): string
    {
        $html = new html_base();
        return $html->form_field(
            url_var::FORMULA_LINK_PRIO,
            msg_id::FORM_FIELD_GROUP,
            'priority missing'
        );
    }

    /**
     * @return string the html code to request the view link priority
     */
    function form_field_view_link_priority(db_object $dbo): string
    {
        // TODO Prio 2 add priority to view relation
        $html = new html_base();
        return $html->form_field(
            url_var::VIEW_TERM_LINK_PRIO,
            msg_id::FORM_FIELD_VIEW_TERM_LINK_PRIO,
            'prio missing'
        );
    }

    /**
     * @return string the html code to request the component position
     */
    function form_field_component_link_order_number(db_object $dbo): string
    {
        $html = new html_base();
        return $html->form_field(
            url_var::COMPONENT_LINK,
            msg_id::FORM_FIELD_COMPONENT_LINK,
            'order number missing'
        );
    }

    /**
     * @return string the html code to request the view modification start position
     */
    function form_view_relation_pos(db_object $dbo): string
    {
        $html = new html_base();
        return $html->form_field(
            url_var::POSITION,
            msg_id::FORM_FIELD_COMPONENT_LINK,
            'position missing missing',
            html_base::INPUT_INT,
            '',
            view_styles::COL_SM_1
        );
    }

    /**
     * @return string the html code to request the selection name from the user
     */
    function form_field_selection_name(db_object|sandbox_list $dbo): string
    {
        $html = new html_base();
        return $html->form_field(
            url_var::NAME,
            msg_id::FORM_FIELD_NAME,
            $this->selection_value($dbo),
            html_base::INPUT_TEXT,
            '',
            view_styles::COL_SM_8
        );
    }

    /**
     * @return string the html code to request the selection description from the user
     */
    function form_field_selection_description(db_object|sandbox_list $dbo): string
    {
        $html = new html_base();
        return $html->form_field(
            url_var::GROUP_NAME,
            msg_id::FORM_FIELD_GROUP,
            $this->selection_value($dbo),
            html_base::INPUT_TEXT,
            '',
            view_styles::COL_SM_8
        );
    }

    /**
     * @return string the html code to request the selection text from the user
     */
    function form_field_selection_text(db_object $dbo): string
    {
        $html = new html_base();
        return $html->form_field(
            url_var::DESCRIPTION,
            msg_id::FORM_FIELD_GROUP,
            $this->selection_value($dbo),
            html_base::INPUT_TEXT,
            '',
            view_styles::COL_SM_8
        );
    }

    /**
     * pick a safe pre-fill value for a selection form field
     * - sandbox_list::name() wraps the result in quotes (e.g. '""' for an empty list),
     *   which breaks the surrounding HTML value="..." attribute
     * - name_pur() returns an empty string for empty lists and avoids the outer quotes
     * @param db_object|sandbox_list $dbo the backend object whose name should pre-fill the field
     * @return string the value to put into the input
     */
    private function selection_value(db_object|sandbox_list $dbo): string
    {
        if ($dbo instanceof sandbox_list) {
            $result = $dbo->name_pur();
        } else {
            $result = $dbo->name();
        }
        return $result;
    }

    /**
     * create the HTML code to select a word or triple
     * selected by the component type form_select_phrase
     * in this case there can be more than only component with the type form_select_phrase
     * all are used to select a phrase
     * but depending on the code_id different url fields and labels are used
     *
     * TODO move form_select_phrase_to to a const
     * TODO remove fixed pattern
     *
     * @param db_object|triple $dbo the frontend phrase object with the id used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @return string the html code to request the description from the user
     */
    function form_phrase(
        db_object|triple $dbo,
        string           $form_name,
        string           $code_id = '',
        ?phrase_list     $phr_lst = null,
        bool             $test_mode = false
    ): string
    {
        $lib = new library();
        // TODO use a pattern base on user entry
        $pattern = '';
        if ($test_mode) {
            $pattern = word_names::MATH;
        }

        // get the selected phrase id
        $id = $dbo->id();
        $name = url_var::PHRASE;
        $label_id = msg_id::FORM_SELECT_PHRASE;
        if ($code_id == components::FORM_PHRASE_FROM_CODE_ID) {
            $id = $dbo->get_from()?->id();
            $name = url_var::PHRASE_FROM;
            $label_id = msg_id::FORM_SELECT_PHRASE_FROM;
        } elseif ($code_id == components::FORM_PHRASE_TO_CODE_ID) {
            $id = $dbo->get_to()?->id();
            $name = url_var::PHRASE_TO;
            $label_id = msg_id::FORM_SELECT_PHRASE_TO;
        } elseif ($code_id == components::FORM_PHRASE_REF_CODE_ID) {
            $id = $dbo->get_from()?->id();
        } elseif ($code_id == components::FORM_PHRASE_ROW) {
            // TODO Prio 1 activate
            // $id = $dbo->phr_row?->id();
            $id = 1;
            $name = url_var::PHRASE_ROW;
            $label_id = msg_id::FORM_SELECT_PHRASE_ROW;
        } elseif ($code_id == components::FORM_PHRASE_COL) {
            // TODO Prio 1 activate
            //$id = $dbo->phr_col?->id();
            $id = 1;
            $name = url_var::PHRASE_COL;
            $label_id = msg_id::FORM_SELECT_PHRASE_COL;
        } elseif ($code_id == components::FORM_PHRASE_COL_SUB) {
            // TODO Prio 1 activate
            //$id = $dbo->phr_col2?->id();
            $id = 1;
            $name = url_var::PHRASE_COL_SUB;
            $label_id = msg_id::FORM_SELECT_PHRASE_COL_SUB;
        }
        if ($id == null) {
            $id = 0;
            log_warning('id missing in ' . $dbo->dsp_id());
        }

        // use an empty list if none is provided so the selector renders without crashing
        $phr_lst ??= new phrase_list();
        return $phr_lst->phrase_selector($name, $form_name, $id, $pattern, $label_id);
    }

    /**
     * create the HTML code to select one or more words or triples
     * TODO review
     *
     * @param db_object|triple|sandbox_list $dbo the frontend phrase object with the id used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @return string the html code to request the description from the user
     */
    function form_phrases(
        db_object|triple|sandbox_list $dbo,
        string                        $form_name,
        string                        $code_id = '',
        ?phrase_list                  $phr_lst = null,
        bool                          $test_mode = false
    ): string
    {
        $lib = new library();
        // TODO use a pattern base on user entry
        $pattern = '';
        if ($test_mode) {
            $pattern = word_names::MATH;
        }

        // get the selected phrase id
        $id = $dbo->id();
        $name = url_var::PHRASE;
        $label_id = msg_id::FORM_SELECT_PHRASE;
        if ($code_id == components::FORM_PHRASE_FROM_CODE_ID) {
            $id = $dbo->get_from()?->id();
            $name = url_var::PHRASE_FROM;
            $label_id = msg_id::FORM_SELECT_PHRASE_FROM;
        } elseif ($code_id == components::FORM_PHRASE_TO_CODE_ID) {
            $id = $dbo->get_to()?->id();
            $name = url_var::PHRASE_TO;
            $label_id = msg_id::FORM_SELECT_PHRASE_TO;
        } elseif ($code_id == components::FORM_PHRASE_REF_CODE_ID) {
            $id = $dbo->get_from()?->id();
        } elseif ($code_id == components::FORM_PHRASE_ROW) {
            // TODO Prio 1 activate
            // $id = $dbo->phr_row?->id();
            $id = 1;
            $name = url_var::PHRASE_ROW;
            $label_id = msg_id::FORM_SELECT_PHRASE_ROW;
        } elseif ($code_id == components::FORM_PHRASE_COL) {
            // TODO Prio 1 activate
            //$id = $dbo->phr_col?->id();
            $id = 1;
            $name = url_var::PHRASE_COL;
            $label_id = msg_id::FORM_SELECT_PHRASE_COL;
        } elseif ($code_id == components::FORM_PHRASE_COL_SUB) {
            // TODO Prio 1 activate
            //$id = $dbo->phr_col2?->id();
            $id = 1;
            $name = url_var::PHRASE_COL_SUB;
            $label_id = msg_id::FORM_SELECT_PHRASE_COL_SUB;
        }
        if ($id == null) {
            $id = 0;
            log_warning('id missing in ' . $dbo->dsp_id());
        }

        // use an empty list if none is provided so the selector renders without crashing
        $phr_lst ??= new phrase_list();
        return $phr_lst->phrase_selector($name, $form_name, $id, $pattern, $label_id);
    }

    /**
     * create the HTML code to select a word, verb, triple or formula
     * TODO Prio 1 review
     *
     * @param db_object|triple $dbo the frontend phrase object with the id used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @return string the html code to request the description from the user
     */
    function form_term(
        db_object|triple $dbo,
        string           $form_name,
        string           $code_id = '',
        ?phrase_list     $phr_lst = null,
        bool             $test_mode = false
    ): string
    {
        $lib = new library();
        // TODO use a pattern base on user entry
        $pattern = '';
        if ($test_mode) {
            $pattern = word_names::MATH;
        }

        // get the selected phrase id
        $id = $dbo->id();
        $name = url_var::PHRASE;
        $label_id = msg_id::FORM_SELECT_PHRASE;
        if ($code_id == components::FORM_PHRASE_FROM_CODE_ID) {
            $id = $dbo->get_from()?->id();
            $name = url_var::PHRASE_FROM;
            $label_id = msg_id::FORM_SELECT_PHRASE_FROM;
        } else {
            // TODO Prio 1 activate
            //$id = $dbo->phr_col2?->id();
            $id = 1;
            $name = url_var::PHRASE_COL_SUB;
            $label_id = msg_id::FORM_SELECT_PHRASE_COL_SUB;
        }
        if ($id == null) {
            $id = 0;
            log_warning('id missing in ' . $dbo->dsp_id());
        }

        // use an empty list if none is provided so the selector renders without crashing
        $phr_lst ??= new phrase_list();
        return $phr_lst->phrase_selector($name, $form_name, $id, $pattern, $label_id);
    }

    /**
     * create the HTML code to select one or mane words, verbs, triples or formulas
     * TODO Prio 1 review
     *
     * @param db_object|triple $dbo the frontend phrase object with the id used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @return string the html code to request the description from the user
     */
    function form_terms(
        db_object|triple $dbo,
        string           $form_name,
        string           $code_id = '',
        ?phrase_list     $phr_lst = null,
        bool             $test_mode = false
    ): string
    {
        $lib = new library();
        // TODO use a pattern base on user entry
        $pattern = '';
        if ($test_mode) {
            $pattern = word_names::MATH;
        }

        // get the selected phrase id
        $id = $dbo->id();
        $name = url_var::PHRASE;
        $label_id = msg_id::FORM_SELECT_PHRASE;
        if ($code_id == components::FORM_PHRASE_FROM_CODE_ID) {
            $id = $dbo->get_from()?->id();
            $name = url_var::PHRASE_FROM;
            $label_id = msg_id::FORM_SELECT_PHRASE_FROM;
        } else {
            // TODO Prio 1 activate
            //$id = $dbo->phr_col2?->id();
            $id = 1;
            $name = url_var::PHRASE_COL_SUB;
            $label_id = msg_id::FORM_SELECT_PHRASE_COL_SUB;
        }
        if ($id == null) {
            $id = 0;
            log_warning('id missing in ' . $dbo->dsp_id());
        }

        // use an empty list if none is provided so the selector renders without crashing
        $phr_lst ??= new phrase_list();
        return $phr_lst->phrase_selector($name, $form_name, $id, $pattern, $label_id);
    }

    /**
     * create the html code for the form element to select the phrase type
     * @param db_object $dbo the frontend phrase object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the verb
     */
    function form_verb(db_object $dbo, string $form_name, ?type_lists $typ_lst): string
    {
        return $dbo->verb_selector($form_name, $typ_lst);
    }

    /**
     * create the html code for the form element to select one or more verbs
     * @param db_object $dbo the frontend phrase object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the verb
     */
    function form_verbs(db_object $dbo, string $form_name, ?type_lists $typ_lst): string
    {
        return $dbo->verb_selector($form_name, $typ_lst);
    }

    /**
     * create the html code for the form element to select the source
     * @param db_object $dbo the frontend phrase object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param source_list|null $src_lst the frontend cache with the configuration, the preloaded source and the cached objects
     * @param string $pattern the selection pattern to filter a selection
     * @return string the html code to select the source
     */
    function form_source(db_object $dbo, string $form_name, ?source_list $src_lst, string $pattern = ''): string
    {
        return $dbo->source_selector($form_name, $pattern, $src_lst);
    }

    /**
     * create the html code for the form element to select one or many sources
     * @param db_object $dbo the frontend phrase object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param source_list|null $src_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the source
     */
    function form_sources(db_object $dbo, string $form_name, ?source_list $src_lst): string
    {
        return $dbo->source_selector($form_name, '', $src_lst);
    }

    /**
     * create the html code for the form element to select the reference
     * @param db_object $dbo the frontend phrase object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @param string $pattern the selection pattern to filter a selection
     * @return string the html code to select the reference
     */
    function form_ref(db_object $dbo, string $form_name, ?type_lists $typ_lst, string $pattern = ''): string
    {
        return $dbo->ref_selector($form_name, $pattern);
    }

    /**
     * create the html code for the form element to select one or many references
     * TODO Prio 1 review
     * @param db_object $dbo the frontend phrase object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the reference
     */
    function form_refs(db_object $dbo, string $form_name, ?type_lists $typ_lst): string
    {
        return $dbo->ref_selector($form_name, '');
    }

    /**
     * create the html code for the form element to select a value
     * @param db_object $dbo the frontend phrase object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param value_list|null $val_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the view
     */
    function form_value(db_object $dbo, string $form_name, ?value_list $val_lst): string
    {
        return $dbo->value_selector($form_name, $val_lst);
    }

    /**
     * create the html code for the form element to select a value
     * @param db_object $dbo the frontend phrase object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param value_list|null $val_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the view
     */
    function form_values(db_object $dbo, string $form_name, ?value_list $val_lst): string
    {
        return $dbo->value_selector($form_name, $val_lst);
    }

    /**
     * create the html code for the form element to select a result
     * @param db_object $dbo the frontend phrase object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param result_list|null $res_lst cached list of results for fast selection
     * @return string the html code to select the result
     */
    function form_result(db_object $dbo, string $form_name, ?result_list $res_lst): string
    {
        return $dbo->result_selector($form_name, $res_lst);
    }

    /**
     * create the html code for the form element to select results
     * @param db_object $dbo the frontend phrase object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param result_list|null $res_lst cached list of results for fast selection
     * @return string the html code to select the results
     */
    function form_results(db_object $dbo, string $form_name, ?result_list $res_lst): string
    {
        return $dbo->result_selector($form_name, $res_lst);
    }

    /**
     * create the html code for the form element to select one formula
     * @param db_object $dbo the frontend object with the view used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param formula_list|null $frm_lst cached list of views for fast selection
     * @return string the html code to select the view
     */
    function form_formula(db_object $dbo, string $form_name, ?formula_list $frm_lst): string
    {
        return $dbo->formula_selector($form_name, $frm_lst);
    }

    /**
     * create the html code for the form element to select one formula
     * @param db_object $dbo the frontend object with the view used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param formula_list|null $frm_lst cached list of views for fast selection
     * @return string the html code to select the view
     */
    function form_formulas(db_object $dbo, string $form_name, ?formula_list $frm_lst): string
    {
        return $dbo->formula_selector($form_name, $frm_lst);
    }

    /**
     * create the html code for the form element to select the view
     * @param db_object $dbo the frontend object with the view used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param view_list|null $msk_lst cached list of views for fast selection
     * @return string the html code to select the view
     */
    function form_view(db_object $dbo, string $form_name, ?view_list $msk_lst): string
    {
        return $dbo->view_selector($form_name, $msk_lst);
    }

    /**
     * create the html code for the form element to select the parent view
     * @param db_object $dbo the frontend object with the view used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param view_list|null $msk_lst cached list of views for fast selection
     * @return string the html code to select the view
     */
    function form_parent_view(db_object $dbo, string $form_name, ?view_list $msk_lst): string
    {
        return $dbo->view_selector($form_name, $msk_lst,
            url_var::VIEW_PARENT, msg_id::FORM_SELECT_PARENT_VIEW);
    }

    /**
     * create the html code for the form element to select the child view
     * @param db_object $dbo the frontend object with the view used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param view_list|null $msk_lst cached list of views for fast selection
     * @return string the html code to select the view
     */
    function form_child_view(db_object $dbo, string $form_name, ?view_list $msk_lst): string
    {
        return $dbo->view_selector($form_name, $msk_lst,
            url_var::VIEW_CHILD, msg_id::FORM_SELECT_CHILD_VIEW);
    }

    /**
     * create the html code for the form element to select the view
     * there are three fields / functions to select a view:
     *   form_view_default - this select default to set the default view of a sandbox object within a system form
     *   form_view         - the select view as a form field e.g. to select a view for the export
     *   select_view       - the select view as a direct save to change the view of a sandbox object without changing other fields
     *
     * @param db_object $dbo the frontend object with the view used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param view_list|null $msk_lst cached list of views for fast selection
     * @return string the html code to select the view
     */
    function form_view_default(db_object $dbo, string $form_name, ?view_list $msk_lst): string
    {
        return $dbo->view_selector($form_name, $msk_lst);
    }

    /**
     * create the html code for the form element to select one or many views
     * @param db_object $dbo the frontend object with the view used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param view_list|null $msk_lst cached list of views for fast selection
     * @return string the html code to select the view
     */
    function form_views(db_object $dbo, string $form_name, ?view_list $msk_lst): string
    {
        return $dbo->view_selector($form_name, $msk_lst);
    }

    /**
     * create the html code for the form element to select the component
     * @param db_object $dbo the frontend object with the component used until now
     * @param string $form_name the name of the component which is also used for the html form name
     * @param string $pattern the pattern used to filter the components by the name
     * @param int $id the id of the component selected until now
     * @param component_list|null $cmp_lst cached list of components for fast selection
     * @return string the html code to select the component
     */
    function form_component(
        db_object       $dbo,
        string          $form_name,
        string          $pattern,
        int             $id,
        ?component_list $cmp_lst
    ): string
    {
        return $dbo->component_selector($form_name, $pattern, $id, $cmp_lst);
    }

    /**
     * create the html code for the form element to select one or many components
     * @param db_object $dbo the frontend object with the component used until now
     * @param string $form_name the name of the component which is also used for the html form name
     * @param string $pattern the pattern used to filter the components by the name
     * @param int $id the id of the component selected until now
     * @param component_list|null $msk_lst cached list of components for fast selection
     * @return string the html code to select the component
     */
    function form_components(
        db_object       $dbo,
        string          $form_name,
        string          $pattern,
        int             $id,
        ?component_list $msk_lst
    ): string
    {
        return $dbo->component_selector($form_name, $pattern, $id, $msk_lst);
    }

    /**
     * create the html code for the form element to select the phrase type
     * @param db_object $dbo the frontend phrase object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the phrase type
     */
    function form_phrase_type(db_object $dbo, string $form_name, ?type_lists $typ_lst): string
    {
        return $dbo->phrase_type_selector($form_name, $typ_lst);
    }

    /**
     * create the html code for the form element to select the source type
     * @param db_object $dbo the frontend source object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the source type
     */
    function form_source_type(db_object $dbo, string $form_name, ?type_lists $typ_lst): string
    {
        return $dbo->source_type_selector($form_name, $typ_lst);
    }

    /**
     * create the html code for the form element to select the reference type
     * @param db_object $dbo the frontend reference object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the reference type
     */
    function form_ref_type(db_object $dbo, string $form_name, ?type_lists $typ_lst): string
    {
        return $dbo->ref_type_selector($form_name, $typ_lst);
    }

    /**
     * create the html code for the form element to select the value type
     * @param db_object $dbo the frontend value object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the value type
     */
    function form_value_type(db_object $dbo, string $form_name, ?type_lists $typ_lst): string
    {
        return $dbo->value_type_selector($form_name, $typ_lst);
    }

    /**
     * create the html code for the form element to select the formula type
     * @param db_object $dbo the frontend formula object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @return string the html code to select the formula type
     */
    function form_formula_type(db_object $dbo, string $form_name, ?type_lists $typ_lst): string
    {
        return $dbo->formula_type_selector($form_name, $typ_lst);
    }

    /**
     * create the html code for the form element to select the view type
     * @param db_object $dbo the frontend view object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the view type
     */
    function form_view_type(db_object $dbo, string $form_name, ?type_lists $typ_lst): string
    {
        return $dbo->view_type_selector($form_name, $typ_lst);
    }

    /**
     * create the html code for the form element to select the view style
     * used by the view and the component
     *
     * @param db_object $dbo the frontend view object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the view type
     */
    function form_view_style(db_object $dbo, string $form_name, ?type_lists $typ_lst): string
    {
        return $dbo->style_selector($form_name, $typ_lst);
    }

    /**
     * create the html code for the form element to select the component type
     * @param db_object $dbo the frontend component object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the component type
     */
    function form_component_type(db_object $dbo, string $form_name, ?type_lists $typ_lst): string
    {
        return $dbo->component_type_selector($form_name, $typ_lst);
    }

    /**
     * create the html code for the form element to select the component style
     * @param db_object $dbo the frontend component object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the component style
     */
    function form_component_style(db_object $dbo, string $form_name, ?type_lists $typ_lst): string
    {
        return $dbo->component_style_selector($form_name, $typ_lst);
    }

    /**
     * create the html code for the form element to select the view relation type
     * @param db_object $dbo the frontend component object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the view relation type
     */
    function form_view_relation_type(db_object $dbo, string $form_name, ?type_lists $typ_lst): string
    {
        return $dbo->view_relation_type_selector($form_name, $typ_lst);
    }

    /**
     * create the html code for the form element to select the formula link type
     * @param db_object $dbo the frontend formula object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the formula link type
     */
    function form_formula_link_type(db_object $dbo, string $form_name, ?type_lists $typ_lst): string
    {
        return $dbo->formula_link_type_selector($form_name, $typ_lst);
    }

    /**
     * create the html code for the form element to select the view link type
     * @param db_object $dbo the frontend view object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the view link type
     */
    function form_view_link_type(db_object $dbo, string $form_name, ?type_lists $typ_lst): string
    {
        return $dbo->view_link_type_selector($form_name, $typ_lst);
    }

    /**
     * create the html code for the form element to select the component link type
     * @param db_object $dbo the frontend component object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the component link type
     */
    function form_component_link_type(db_object $dbo, string $form_name, ?type_lists $typ_lst): string
    {
        return $dbo->component_link_type_selector($form_name, $typ_lst);
    }

    /**
     * create the html code for the form element to select the component position type
     * @param db_object $dbo the frontend component object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the component link type
     */
    function form_component_pos_type(db_object $dbo, string $form_name, ?type_lists $typ_lst): string
    {
        return $dbo->component_link_type_selector($form_name, $typ_lst);
    }

    /**
     * create the html code for the form element to select the share type
     * @param db_object $dbo the frontend object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the share type
     */
    function form_share_type(db_object $dbo, string $form_name, ?type_lists $typ_lst): string
    {
        return $dbo->share_type_selector($form_name, $typ_lst);
    }

    /**
     * create the html code for the form element to select the protection type
     * @param db_object $dbo the frontend object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the protection type
     */
    function form_protection_type(db_object $dbo, string $form_name, ?type_lists $typ_lst): string
    {
        return $dbo->protection_type_selector($form_name, $typ_lst);
    }

    /**
     * TODO Prio 0 review
     * create the html code for the form element to select the protection type
     * @param db_object $dbo the frontend object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param view_list|null $msk_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the protection type
     */
    function form_table_linked_view(db_object $dbo, string $form_name, ?view_list $msk_lst): string
    {
        return $dbo->view_selector($form_name, $msk_lst);
    }

    /**
     * create the html code for the form element to enter the formula expression
     * @param db_object $dbo the frontend formula object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @return string the html code to select the formula type
     */
    function form_formula_expression(db_object $dbo, string $form_name): string
    {
        $html = new html_base();
        return $html->form_field(
            url_var::NEED_ALL,
            msg_id::FORM_FIELD_FORMULA_EXPRESSION,
            $dbo->user_expression(),
            html_base::INPUT_TEXT,
            '',
            view_styles::COL_SM_12);
    }

    /**
     * create the html code for the form flag to set that the formula needs all fields to be set
     * @param db_object $dbo the frontend formula object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @return string the html code to select the formula type
     */
    function form_formula_all_fields(db_object $dbo, string $form_name): string
    {
        $html = new html_base();
        return $html->dsp_form_fld_checkbox(
            url_var::NEED_ALL,
            $dbo->need_all(),
            "calculate only if all values used in the formula exist");
    }

    /**
     * the cancel button of an edit / add / del / confirm view that returns to the object's own view
     *
     * @param int $msk_id the database id of the view that shows the cancel button
     * @param db_object|type_object|combine_named|sandbox_list|null $dbo the shown object (the same
     *                         union as dsp_entries passes), used for the object id of the return url
     * @param array $url_array the url of the shown view; its origin mask (the edit/add/del mask a
     *                         confirm view was opened from) is used to find the base view because the
     *                         confirm mask itself does not encode the object type
     * @return string the html code for a form cancel button
     */
    function button_cancel(
        int                                                   $msk_id,
        db_object|type_object|combine_named|sandbox_list|null $dbo = null,
        array                                                 $url_array = []
    ): string
    {
        $html = new html_base();
        $views = new views();
        $base_id = $views->code_id_to_id($views->system_to_base($views->id_to_code_id($msk_id)));
        $id = $dbo?->id() ?? 0;
        // a generic confirm view has no base mapping of its own, so return to the '9'-prefixed back
        // target (the object's own default view + id set when the confirm view was opened)
        if ($base_id == 0) {
            $base_id = (int)($url_array[url_var::BACK . url_var::MASK] ?? 0);
            $id = (int)($url_array[url_var::BACK . url_var::ID] ?? $id);
        }
        $result = '';
        $url = api::HOST_SAME . api::MAIN_SCRIPT_EXT
            . url_var::PAR . url_var::MASK . url_var::EQ . $base_id;
        if ($id != 0) {
            $url .= url_var::ADD . url_var::ID . url_var::EQ . $id;
        }
        global $mtr;
        $result .= $html->ref($url, $mtr->txt(msg_id::FORM_BUTTON_CANCEL), '', html_base::BS_BTN . ' ' . html_base::BS_BTN_CANCEL);
        return $result;
    }

    /**
     * @return string the html code for a form save button
     */
    function button_save(): string
    {
        $html = new html_base();
        global $mtr;
        // post the save as a form action so the edit view routes to the confirm view (see form_start)
        return $html->button_bs($mtr->txt(msg_id::FORM_BUTTON_SAVE), '', '', url_var::POST_SUBMIT);
    }

    /**
     * @return string the html code for a form confirm button (used by the confirm change views)
     */
    function button_confirm(): string
    {
        $html = new html_base();
        global $mtr;
        // post the confirm as a form action so url_to_action writes the change to the database
        return $html->button_bs($mtr->txt(msg_id::FORM_BUTTON_CONFIRM), '', '', url_var::POST_SUBMIT);
    }

    /**
     * @return string the html code for a form save button
     */
    function button_del(): string
    {
        $html = new html_base();
        global $mtr;
        return $html->button_bs($mtr->txt(msg_id::FORM_BUTTON_DEL), html_base::BS_BTN_DEL);
    }

    /**
     * @return string the html code for a form save button
     */
    function button_import(): string
    {
        $html = new html_base();
        return $html->button_bs('Import', html_base::BS_BTN_IMPORT);
    }

    /**
     * @return string the html code for a form save button
     */
    function button_export(): string
    {
        $html = new html_base();
        return $html->button_bs('Export', html_base::BS_BTN_EXPORT);
    }

    /**
     * TODO Prio 0 wire up the request action
     * @return string the html code for a button that requests a new e.g. type item
     */
    function button_request(): string
    {
        $html = new html_base();
        return $html->button_bs('Request');
    }

    /**
     * @return string that simply closes the form
     */
    function form_end(): string
    {
        $html = new html_base();
        return $html->form_end();
    }

    /**
     * @return string combine the next elements to one row
     */
    function row_start(): string
    {
        $html = new html_base();
        return $html->row_start();
    }

    /**
     * @return string combine the next elements to one row and align to the right
     */
    function row_right(): string
    {
        $html = new html_base();
        return $html->row_right();
    }

    /**
     * @return string to start a new row and center the following components (e.g. the confirm buttons)
     */
    function row_center(): string
    {
        $html = new html_base();
        return $html->row_center();
    }

    /**
     * @return string just to indicate that a row ends
     */
    function row_end(): string
    {
        $html = new html_base();
        return $html->row_end();
    }

}
