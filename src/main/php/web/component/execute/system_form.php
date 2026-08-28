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

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once html_paths::DB . 'sql_db.php';
include_once html_paths::COMPONENT . 'component.php';
include_once html_paths::COMPONENT . 'component_link.php';
include_once html_paths::COMPONENT . 'component_list.php';
include_once html_paths::FORMULA . 'formula.php';
include_once html_paths::FORMULA . 'formula_link.php';
include_once html_paths::FORMULA . 'formula_list.php';
include_once html_paths::CONST . 'icons.php';
include_once html_paths::CONST . 'def.php';
include_once html_paths::HTML . 'html_names.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::HTML . 'styles.php';
include_once html_paths::PHRASE . 'phrase_list.php';
include_once html_paths::REF . 'ref.php';
include_once html_paths::REF . 'source_list.php';
include_once html_paths::RESULT . 'result_list.php';
include_once html_paths::SANDBOX . 'combine_named.php';
include_once html_paths::SANDBOX . 'db_object.php';
include_once html_paths::SANDBOX . 'sandbox.php';
include_once html_paths::SANDBOX . 'sandbox_code_id.php';
include_once html_paths::SANDBOX . 'sandbox_link.php';
include_once html_paths::SANDBOX . 'sandbox_list.php';
include_once html_paths::SYSTEM . 'language.php';
include_once html_paths::TYPES . 'type_list.php';
include_once html_paths::TYPES . 'type_lists.php';
include_once html_paths::TYPES . 'type_object.php';
include_once html_paths::TYPES . 'view_style_list.php';
include_once html_paths::USER . 'user.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::VALUE . 'value.php';
include_once html_paths::VALUE . 'value_list.php';
include_once html_paths::VERB . 'verb.php';
include_once html_paths::VIEW . 'term_view.php';
include_once html_paths::VIEW . 'view.php';
include_once html_paths::VIEW . 'view_list.php';
include_once html_paths::VIEW . 'view_relation.php';
include_once html_paths::WORD . 'triple.php';
include_once html_paths::WORD . 'word.php';
include_once html_paths::SHARED_CONST . 'components.php';
include_once html_paths::SHARED_CONST . 'def.php';
include_once html_paths::SHARED_CONST . 'views.php';
include_once html_paths::SHARED_CONST . 'words.php';
include_once html_paths::SHARED_ENUM . 'messages.php';
include_once html_paths::SHARED_TYPES . 'view_styles.php';
include_once html_paths::SHARED . 'api.php';
include_once html_paths::SHARED . 'url_var.php';
include_once html_paths::SHARED . 'library.php';
include_once test_paths::CONST . 'word_names.php';

use Zukunft\ZukunftCom\main\php\web\component\component;
use Zukunft\ZukunftCom\main\php\web\component\component_link;
use Zukunft\ZukunftCom\main\php\web\component\component_list;
use Zukunft\ZukunftCom\main\php\web\formula\formula;
use Zukunft\ZukunftCom\main\php\web\formula\formula_link;
use Zukunft\ZukunftCom\main\php\web\formula\formula_list;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\html\styles;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\web\ref\ref;
use Zukunft\ZukunftCom\main\php\web\ref\source_list;
use Zukunft\ZukunftCom\main\php\web\sandbox\combine_named;
use Zukunft\ZukunftCom\main\php\web\sandbox\db_object;
use Zukunft\ZukunftCom\main\php\web\sandbox\sandbox;
use Zukunft\ZukunftCom\main\php\web\sandbox\sandbox_code_id;
use Zukunft\ZukunftCom\main\php\web\sandbox\sandbox_link;
use Zukunft\ZukunftCom\main\php\web\sandbox\sandbox_list;
use Zukunft\ZukunftCom\main\php\web\system\language;
use Zukunft\ZukunftCom\main\php\web\types\type_list;
use Zukunft\ZukunftCom\main\php\web\types\type_lists;
use Zukunft\ZukunftCom\main\php\web\types\type_object;
use Zukunft\ZukunftCom\main\php\web\user\user;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\result\result_list;
use Zukunft\ZukunftCom\main\php\web\value\value;
use Zukunft\ZukunftCom\main\php\web\value\value_list;
use Zukunft\ZukunftCom\main\php\web\view\term_view;
use Zukunft\ZukunftCom\main\php\web\view\view;
use Zukunft\ZukunftCom\main\php\web\view\view_list;
use Zukunft\ZukunftCom\main\php\web\verb\verb;
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
     * - with a class word: 'view "Word" <edit-icon>'
     *
     * @param db_object $dbo the object whose name is shown as the page title
     * @param int $max to limit the number of related phrases shown before a "..." link
     * @param msg_id|null $ui_msg_code_id the translated class word shown in front of the name,
     *                                    null for a page where the class is obvious from the name
     * @return string the html code for the page title with the related-phrases and edit links
     */
    function title_named(
        db_object    $dbo,
        user_message $msg,
        int          $max = def::LIMIT_RELATED_PER_VERB,
        array        $url_array = [],
        ?msg_id      $ui_msg_code_id = null
    ): string
    {
        global $mtr;

        // for a named object the page title is simply its name shown big; a page whose name alone
        // does not say what is shown (a view and a component can have the name of a word) puts the
        // translated class word in front of it and quotes the name, like the verb page title
        $title = $this->esc($dbo->name());
        if ($ui_msg_code_id != null) {
            $title = $mtr->txt($ui_msg_code_id) . ' "' . $title . '"';
        }
        return $this->subtitle($dbo, $title, $msg, $max, '', $url_array);
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
        user_message     $msg,
        int              $max = def::LIMIT_RELATED_PER_VERB,
        array            $url_array = []
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
        return $this->subtitle($dbo, $this->esc($dbo->name()), $msg, $max, $from_verb_to, $url_array);
    }

    /**
     * the page title for a link object (formula link, term view, component link or view relation):
     * show the generated link name big as the title and the two linked objects with a link to each
     * in the subtitle, with the same edit link and share and protection subtitle as the named title
     * (like the triple title, where the from, verb and to move to the subtitle)
     *
     * @param sandbox_link|db_object $dbo the link whose name is the title and whose linked objects are the subtitle
     * @param int $max to limit the number of related entries shown before a "..." link
     * @return string the html code for the link page title
     */
    function title_link(
        sandbox_link|db_object $dbo,
        user_message           $msg,
        int                    $max = def::LIMIT_RELATED_PER_VERB,
        array                  $url_array = []
    ): string
    {
        // the links to the two linked objects move to the subtitle like the triple from/verb/to
        $from_to = '';
        if ($dbo instanceof sandbox_link) {
            $from_to = $dbo->name_linked();
        }
        return $this->subtitle($dbo, $this->esc($dbo->name()), $msg, $max, $from_to, $url_array);
    }

    /**
     * the page title for a phrase: a triple gets the triple title (name plus the from, verb and to
     * links in the subheader), a word or any other named object gets the named title (name plus the
     * related phrases in the subheader), so that one view can show a word and a triple with the same
     * title component instead of one title component per phrase type
     *
     * @param db_object $dbo the phrase whose name is shown as the page title
     * @param int $max to limit the number of related phrases shown before a "..." link
     * @return string the html code for the phrase page title with its subheader
     */
    function title_phrase(
        db_object    $dbo,
        user_message $msg,
        int          $max = def::LIMIT_RELATED_PER_VERB,
        array        $url_array = []
    ): string
    {
        // the class decides, not the phrase id, because the frontend objects of a view are
        // typed (a word view carries a word), while the phrase id is only known after a load
        if ($dbo::class == triple::class) {
            return $this->title_triple($dbo, $msg, $max, $url_array);
        } else {
            return $this->title_named($dbo, $msg, $max, $url_array);
        }
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
        db_object    $dbo,
        user_message $msg,
        int          $max = def::LIMIT_RELATED_PER_VERB,
        array        $url_array = []
    ): string
    {
        return $this->title_named($dbo, $msg, $max, $url_array);
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
        db_object    $dbo,
        user_message $msg,
        int          $max = def::LIMIT_RELATED_PER_VERB,
        array        $url_array = []
    ): string
    {
        // the heading shows the related phrases as links with tooltip plus the value
        $heading_content = $this->esc($dbo->name());
        if ($dbo::class == value::class) {
            $heading_content = $dbo->name_link($msg);
        }
        return $this->subtitle($dbo, $heading_content, $msg, $max, '', $url_array);
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
    private function subtitle(
        db_object    $dbo,
        string       $heading_content,
        user_message $msg,
        int          $max = def::LIMIT_RELATED_PER_VERB,
        string       $lead_subtitle = '',
        array        $url_array = []
    ): string
    {
        $html = new html_base();

        $lnk = $this->edit_link($dbo, $url_array);

        // category subtitle is created based on verbs listed in verbs::CATEGORY_VERBS
        $cat = $this->category_subtitle($dbo, $max);

        // type subtitle with a link to the type page if the object has a non-default type
        $typ = $this->type_subtitle($dbo, $msg);
        $cat_typ = $html->concat_category_text($cat, $typ, $msg);

        if ($dbo instanceof sandbox) {
            // share and protection subtitle if not default
            $shr = $this->share_subtitle($dbo);
            $ptc = $this->protection_subtitle($dbo);
            $shr_ptc = $html->concat_entry_text($shr, $ptc, $msg);
        } else {
            $shr_ptc = '';
        }

        // join all subtitle parts with the category separator " / "; a triple prepends its
        // from/verb/to links so the whole subtitle stays on one parenthesized line
        $sub_txt = $html->concat_category_text($cat_typ, $shr_ptc, $msg);
        $sub_txt = $html->concat_category_text($lead_subtitle, $sub_txt, $msg);

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
    private function type_subtitle(word|db_object $dbo, user_message $msg): string
    {
        global $ui_sys;
        if (in_array($dbo::class, def_ui::TYPE_CLASSES)) {
            // the type name links to the type page that lists the other phrases of this type
            // and the fixed code rules linked to this phrase type
            // TODO Prio 3 point this to the dedicated phrase type page once it exists
            return $this->type_link($ui_sys?->typ_lst_cache?->class_to_type_list($dbo::class), $dbo->type_id($msg));
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
     * the page-identifying url params of the calling page are added with the url_var::BACK ('9')
     * prefix so the edit mask can return to the calling page e.g. on cancel
     * or if the pod blocks the change of an ip user (see /http/view.php)
     *
     * @param db_object $dbo any database object that can be changed by the user or an admin
     * @param array $url_array the url params of the calling page used to create the back params
     * @return string for a link icon to change the object
     */
    private function edit_link(db_object $dbo, array $url_array = []): string
    {
        global $mtr;

        $html = new html_base();
        $url = $html->url_with_back(
            $html->url_back($dbo::VIEW_EDIT_ID, $dbo->id()),
            html_base::page_url_array($url_array)
        );
        $icon = '<' . html_base::I . ' ' . html_base::CLASS_HTML . '="' . icons::EDIT . '"></' . html_base::I . '>';
        return $html->ref($url, $icon, $mtr->txt($dbo::MSG_EDIT), styles::HEADING_ICON_INLINE, true);
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
        global $mtr;
        $html = new html_base();
        return $html->input(
            url_var::USER_PASSWORD,
            msg_id::FORM_FIELD_USER_PASSWORD,
            $dbo->password(),
            html_base::INPUT_PASSWORD);
        /*
         * optional with show password but without auto fill
        return $html->input_password(
            url_var::USER_PASSWORD,
            msg_id::FORM_FIELD_USER_PASSWORD,
            $mtr->txt(msg_id::FORM_SHOW_PASSWORD),
            $dbo->password());
        */
    }

    /**
     * @return string the html code so that an admin user can switch if the pages
     *                for the user must be created from the user sandbox
     */
    function admin_form_user_uses_sandbox(user|db_object $dbo): string
    {
        global $mtr;
        $html = new html_base();
        return $html->dsp_form_fld_checkbox(
            url_var::USER_USES_SANDBOX,
            $dbo->uses_sandbox,
            $mtr->txt(msg_id::FORM_FIELD_USER_USES_SANDBOX));
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
        return $this->esc($dbo->name);
    }


    /**
     * show the name of an object to the user
     * @param db_object|type_object $dbo the object
     * @param string $code_id e.g. to select the name in case of a link object
     * @return string the html code to show the object name to the user
     */
    function show_name(db_object|type_object $dbo, string $code_id = ''): string
    {
        return $this->get_name($dbo, $code_id);
    }

    /**
     * show the name of an object as a headline in the center
     * @param db_object|type_object $dbo the object
     * @param string $code_id e.g. to select the name in case of a link object
     * @return string the html code to show the object name to the user
     */
    function show_name_big(db_object|type_object $dbo, string $code_id = ''): string
    {
        return html_base::h3($this->get_name($dbo, $code_id));
    }

    /**
     * TODO Prio 2 remove exceptions
     * get the name of an object
     * @param db_object|type_object $dbo the object
     * @param string $code_id e.g. to select the name in case of a link object
     * @return string the html code to show the object name to the user
     */
    private function get_name(db_object|type_object $dbo, string $code_id = ''): string
    {
        if ($code_id == '') {
            $result = $dbo->name();
        } elseif ($code_id == 'show_field_formula_name') {
            $result = $dbo->formula_name();
        } elseif ($code_id == 'show_field_phrase_name') {
            $result = $dbo->phrase_name();
        } else {
            log_warning('code id ' . $code_id . ' not yet defined in show_name');
            $result = $dbo->name();
        }
        return $this->esc($result);
    }

    /**
     * escape a user-settable string (a name, description or reference field) so it is shown
     * literally and cannot inject html into the page (stored xss); element-text context, because
     * these show_* outputs are concatenated into the page body by component_exe, not into an attribute
     * @param string|null $text the raw user text
     * @return string the text safe to place between html tags
     */
    private function esc(?string $text): string
    {
        $html = new html_base();
        return $html->esc($text);
    }

    /**
     * @param db_object|type_object $dbo the object
     * @return string the html code to show the object description to the user
     */
    function show_description(db_object|type_object $dbo): string
    {
        return $this->esc($dbo->get_description());
    }

    /**
     * @param word|db_object $dbo the word
     * @return string the plural form of the word as read-only text (empty if no plural is set)
     */
    function show_plural(word|db_object $dbo): string
    {
        return $this->show_field_labeled($dbo->plural ?? '', msg_id::FORM_FIELD_PLURAL);
    }

    /**
     * @param verb|db_object $dbo the verb
     * @return string the reverse name of the verb as read-only text (empty if no reverse name is set)
     */
    function show_reverse(verb|db_object $dbo): string
    {
        return $this->show_field_labeled($dbo->reverse ?? '', msg_id::FORM_FIELD_REVERSE);
    }

    /**
     * @param verb|db_object $dbo the verb
     * @return string the plural of the reverse name of the verb as read-only text
     *                (empty if no plural reverse name is set)
     */
    function show_plural_reverse(verb|db_object $dbo): string
    {
        return $this->show_field_labeled($dbo->rev_plural ?? '', msg_id::FORM_FIELD_PLURAL_REVERSE);
    }

    /**
     * @param verb|db_object $dbo the verb
     * @return string the short name that the verb has in a formula as read-only text, where both
     *                sides of the triple are combined (empty if no formula name is set)
     */
    function show_name_in_formulas(verb|db_object $dbo): string
    {
        return $this->show_field_labeled($dbo->frm_name ?? '', msg_id::FORM_FIELD_NAME_IN_FORMULAS);
    }

    /**
     * a read only field value with the translated label of the field in front of it, e.g.
     * 'Plural: are', because the verb page shows the plural, the reverse and the plural reverse
     * below each other and without the label the user cannot tell which value is which
     *
     * the label of the matching form field is reused, so that the read only page and the edit
     * form name the same field the same way (see form_field_plural)
     *
     * @param string $value the field value of the object
     * @param msg_id $ui_msg_code_id the message id of the field label
     * @return string the escaped value behind its label, '' if the object has no value
     */
    private function show_field_labeled(string $value, msg_id $ui_msg_code_id): string
    {
        return $this->label_with_html($this->esc($value), $ui_msg_code_id);
    }

    /**
     * like show_field_labeled, but for a value that is html already, e.g. the link of a formula,
     * so the caller is responsible that the given html is safe
     *
     * @param string $html_code the html shown behind the label
     * @param msg_id $ui_msg_code_id the message id of the field label
     * @return string the html behind its label, '' if the object has no value
     */
    private function label_with_html(string $html_code, msg_id $ui_msg_code_id): string
    {
        global $mtr;

        $result = '';
        // a field without a value is not shown at all, so its label would stand alone
        if ($html_code != '') {
            $result = $mtr->txt($ui_msg_code_id) . def::FALLBACK_LABEL_SEPARATOR . $html_code;
        }
        return $result;
    }

    /**
     * @param view|component|component_link|db_object $dbo the object whose display style is shown
     * @return string the user-readable name of the display style (empty if no style is set)
     */
    function show_style(view|component|component_link|db_object $dbo): string
    {
        global $ui_sys;
        $result = '';
        // guarded by class, because only a view, a component and a component link have a display
        // style (the link style overwrites the style of the linked component) and a mis-assigned
        // seed component must not stop the page with a fatal
        if ($dbo instanceof view or $dbo instanceof component or $dbo instanceof component_link) {
            $style_id = $dbo->get_style_id();
            if ($style_id != null) {
                $result = $this->esc($ui_sys?->typ_lst_cache?->msk_sty?->name($style_id) ?? '');
            }
        } else {
            log_err($dbo::class . ' is not expected to have a display style');
        }
        return $result;
    }

    /**
     * @param component|db_object $dbo the component whose calculation formula is shown
     * @return string the linked name of the formula (empty if no formula is set or known)
     */
    function show_formula(component|db_object $dbo): string
    {
        global $ui_sys;
        $result = '';
        // guarded by class, because only a component links a calculation formula and a
        // mis-assigned seed component must not stop the page with a fatal
        if ($dbo instanceof component) {
            if ($dbo->formula_id != null) {
                // resolve the name from the request cache, because the page url and the
                // api message only carry the formula id
                $frm = $ui_sys?->frm_lst?->get($dbo->formula_id);
                $result = $frm?->name_link() ?? '';
            }
        } else {
            log_err($dbo::class . ' is not expected to have a calculation formula');
        }
        return $result;
    }

    /**
     * @param triple|db_object $dbo the triple whose weight is shown
     * @return string the weight behind its label as read only text (empty if no weight is set)
     */
    function show_weight(triple|db_object $dbo): string
    {
        $result = '';
        // guarded by class, because only a triple has a weight and a mis-assigned seed
        // component must not stop the page with a fatal
        if ($dbo instanceof triple) {
            $result = $this->show_field_labeled((string)($dbo->weight ?? ''), msg_id::FORM_FIELD_WEIGHT);
        } else {
            log_err($dbo::class . ' is not expected to have a weight');
        }
        return $result;
    }

    /**
     * @param triple|db_object $dbo the triple whose condition formula is shown
     * @return string the linked name of the condition formula behind its label
     *                (empty if no condition is set or the formula is not known)
     */
    function show_condition_formula(triple|db_object $dbo): string
    {
        $result = '';
        // guarded by class, because only a triple has a condition formula and a mis-assigned
        // seed component must not stop the page with a fatal
        if ($dbo instanceof triple) {
            // the api sends the formula itself for a page request, so the name needs no cache;
            // name_link() returns safe html, so it is added behind the label unescaped
            $result = $this->label_with_html(
                $dbo->condition?->name_link() ?? '', msg_id::FORM_FIELD_CONDITION_FORMULA);
        } else {
            log_err($dbo::class . ' is not expected to have a condition formula');
        }
        return $result;
    }

    /**
     * @param value|db_object $dbo the value whose source is shown
     * @return string the linked name of the source behind its label
     *                (empty if the value has no source or the source is not known)
     */
    function show_source(value|db_object $dbo): string
    {
        $result = '';
        // guarded by class, because only a value links a source and a mis-assigned
        // seed component must not stop the page with a fatal
        if ($dbo instanceof value) {
            // the api sends the source itself for a page request, so the name needs no cache;
            // name_link() returns safe html, so it is added behind the label unescaped
            if ($dbo->src?->name() != '') {
                $result = $this->label_with_html($dbo->src->name_link(), msg_id::FORM_SELECT_SOURCE);
            }
        } else {
            log_err($dbo::class . ' is not expected to have a source');
        }
        return $result;
    }

    /**
     * @param value|ref|db_object $dbo the value or reference whose last update time is shown
     * @return string the time of the last update behind its label in the user's time format
     *                (empty if the object has never been updated e.g. a not yet saved value)
     */
    function show_last_update(value|ref|db_object $dbo): string
    {
        global $ui_sys;

        $result = '';
        // guarded by class, because only a value and a reference track the time of the last
        // update and a mis-assigned seed component must not stop the page with a fatal
        if ($dbo instanceof value) {
            $upd = $dbo->last_update;
        } elseif ($dbo instanceof ref) {
            // the reference keeps the api time text, so it is parsed here for the display format
            $upd = null;
            if ($dbo->last_update != null) {
                $lib = new library();
                $upd = $lib->get_datetime($dbo->last_update, $dbo->dsp_id(), 'show last update');
            }
        } else {
            $upd = null;
            log_err($dbo::class . ' is not expected to have a last update time');
        }
        if ($upd != null) {
            $result = $this->show_field_labeled(
                date_format($upd, $ui_sys->cfg->date_time_format()),
                msg_id::SYSTEM_DB_FIELD_LAST_UPDATE);
        }
        return $result;
    }

    /**
     * @param ref|db_object $dbo the reference whose impact is shown
     * @return string the impact number behind its label as read only text, because the impact
     *                is calculated by the system and can never be changed by the user
     *                (empty if the impact has not yet been calculated)
     */
    function show_impact(ref|db_object $dbo): string
    {
        $result = '';
        // guarded by class, because a mis-assigned seed component must not stop the page
        if ($dbo instanceof ref) {
            $result = $this->show_field_labeled(
                (string)($dbo->impact ?? ''), msg_id::SYSTEM_DB_FIELD_IMPACT);
        } else {
            log_err($dbo::class . ' is not expected to show the impact');
        }
        return $result;
    }

    /**
     * @param component|db_object $dbo the component whose row phrase is shown
     * @param phrase_list $phr_lst the request cache with the preloaded phrases
     * @return string the linked name of the row phrase (empty if not set or not known)
     */
    function show_row_phrase(component|db_object $dbo, phrase_list $phr_lst): string
    {
        $result = '';
        if ($dbo instanceof component) {
            $result = $this->component_phrase($dbo->row_phrase, $phr_lst);
        } else {
            log_err($dbo::class . ' is not expected to have a row phrase');
        }
        return $result;
    }

    /**
     * @param component|db_object $dbo the component whose column phrase is shown
     * @param phrase_list $phr_lst the request cache with the preloaded phrases
     * @return string the linked name of the column phrase (empty if not set or not known)
     */
    function show_col_phrase(component|db_object $dbo, phrase_list $phr_lst): string
    {
        $result = '';
        if ($dbo instanceof component) {
            $result = $this->component_phrase($dbo->col_phrase, $phr_lst);
        } else {
            log_err($dbo::class . ' is not expected to have a column phrase');
        }
        return $result;
    }

    /**
     * @param component|db_object $dbo the component whose sub column phrase is shown
     * @param phrase_list $phr_lst the request cache with the preloaded phrases
     * @return string the linked name of the sub column phrase (empty if not set or not known)
     */
    function show_col_sub_phrase(component|db_object $dbo, phrase_list $phr_lst): string
    {
        $result = '';
        if ($dbo instanceof component) {
            $result = $this->component_phrase($dbo->col_sub_phrase, $phr_lst);
        } else {
            log_err($dbo::class . ' is not expected to have a sub column phrase');
        }
        return $result;
    }

    /**
     * the linked name of one layout phrase (row, column or sub column) of a component; the
     * shared part of show_row_phrase, show_col_phrase and show_col_sub_phrase
     * @param int|null $phr_id the id of the layout phrase or null if the field is not set
     * @param phrase_list $phr_lst the request cache with the preloaded phrases
     * @return string the linked phrase name (empty if the phrase is not set or not known)
     */
    private function component_phrase(?int $phr_id, phrase_list $phr_lst): string
    {
        $result = '';
        if ($phr_id != null) {
            // resolve the name from the request cache, because the page url and the api
            // message only carry the phrase id
            $phr = $phr_lst->get($phr_id);
            $result = $phr?->name_link() ?? '';
        }
        return $result;
    }

    /**
     * @param sandbox|db_object $dbo the object whose owner is shown
     * @return string the name of the user who owns the object (empty if the owner is not known)
     */
    function show_owner(sandbox|db_object $dbo): string
    {
        $result = '';
        // guarded by class, because only a sandbox object has an owner and a mis-assigned
        // seed component must not stop the page with a fatal
        if ($dbo instanceof sandbox) {
            $result = $this->esc($dbo->owner_name());
        } else {
            log_err($dbo::class . ' is not expected to have an owner');
        }
        return $result;
    }

    /**
     * @param word|db_object $dbo the word
     * @return string the user-readable name of the word's phrase type (empty if no type is set)
     */
    function show_phrase_type(word|db_object $dbo, user_message $msg): string
    {
        $result = '';
        $type_id = $dbo->type_id($msg);
        $phr_typ = type_lists::phrase_types($msg);
        if ($type_id !== null and $phr_typ != null) {
            $result = $this->esc($phr_typ->name($type_id));
        }
        return $result;
    }

    /**
     * @param ref|db_object $dbo the object
     * @return string the html code to show the object reference type to the user
     */
    function show_ref_type(ref|db_object $dbo): string
    {
        return $this->show_field_labeled($dbo->type_name(), msg_id::FORM_SELECT_REF_TYPE);
    }

    /**
     * @param ref|db_object $dbo the object
     * @return string the external key of the reference behind its label (empty if not yet set)
     */
    function show_ref_key(ref|db_object $dbo): string
    {
        // a new reference of an add form has no external key yet
        return $this->show_field_labeled($dbo->external_key() ?? '', msg_id::FORM_FIELD_EXTERNAL_KEY);
    }

    /**
     * @param ref|db_object $dbo the object
     * @return string the linked name of the source of the reference behind its label
     *                (empty if no source is set or only the source id is known)
     */
    function show_ref_source(ref|db_object $dbo): string
    {
        $result = '';
        // the api sends the source with the name for a page request; name_link() returns
        // safe html, so it is added behind the label unescaped
        if ($dbo->source()?->name() != '') {
            $result = $this->label_with_html($dbo->source()->name_link(), msg_id::FORM_SELECT_SOURCE);
        }
        return $result;
    }

    /**
     * @param ref|db_object $dbo the object
     * @return string the url of the reference as a link to the external page behind its label
     *                (empty if no url is set e.g. for a new reference of an add form)
     */
    function show_ref_url(ref|db_object $dbo): string
    {
        $result = '';
        $url = $dbo->url();
        if ($url != null and $url != '') {
            $html = new html_base();
            // the url is user-settable, but html_base::ref escapes the shown name
            // and drops the link if the scheme is not one of the allowed ones
            $result = $this->label_with_html($html->ref($url, $url), msg_id::FORM_FIELD_URL);
        }
        return $result;
    }

    /**
     * @param ref|db_object $dbo the reference whose linked phrase is shown
     * @return string the linked name of the word or triple this reference belongs to behind
     *                its label (empty if the phrase is not set or only its id is known)
     */
    function show_ref_phrase(ref|db_object $dbo): string
    {
        $result = '';
        // guarded by class, because only a reference links a single phrase and a mis-assigned
        // seed component must not stop the page with a fatal
        if ($dbo instanceof ref) {
            // the api sends the phrase with the name for a page request; name_link() returns
            // safe html, so it is added behind the label unescaped
            if ($dbo->phrase()->name() != '') {
                $result = $this->label_with_html($dbo->phrase()->name_link(), msg_id::FORM_SELECT_PHRASE);
            }
        } else {
            log_err($dbo::class . ' is not expected to link a single phrase');
        }
        return $result;
    }

    /**
     * TODO Prio 1 fill with the correct field
     * @param db_object $dbo the object
     * @return string the html code to show the object name to the user
     */
    function show_usage(db_object $dbo): string
    {
        return $this->esc($dbo->name());
    }

    /**
     * @param view_relation|db_object $dbo the object
     * @return string|null the html code to show the object name to the user
     */
    function show_parent_view(view_relation|db_object $dbo): string|null
    {
        return $this->esc($dbo->parent()?->name());
    }

    /**
     * @param view_relation|db_object $dbo the object
     * @return string|null the html code to show the object name to the user
     */
    function show_child_view(view_relation|db_object $dbo): string|null
    {
        return $this->esc($dbo->child()?->name());
    }

    /**
     * @param sandbox_link|db_object $dbo the link whose link type is shown
     * @return string the user-readable name of the link type (empty if no type is set)
     */
    function show_link_type(sandbox_link|db_object $dbo): string
    {
        $result = '';
        // guarded by class so that a mis-assigned seed component cannot fatal
        if ($dbo instanceof sandbox_link) {
            $result = $this->esc($dbo->link_type()?->name());
        } else {
            log_err($dbo::class . ' is not expected to have a link type');
        }
        return $result;
    }

    /**
     * @param view_relation|db_object $dbo the view relation whose start position is shown
     * @return string the start position of the relation (empty if no start position is set)
     */
    function show_start_pos(view_relation|db_object $dbo): string
    {
        $result = '';
        // guarded by class so that a mis-assigned seed component cannot fatal
        if ($dbo instanceof view_relation) {
            $result = (string)($dbo->start_pos ?? '');
        } else {
            log_err($dbo::class . ' is not expected to have a start position');
        }
        return $result;
    }

    /**
     * used by the link default page and as the current value of the link form field
     * @param formula_link|component_link|term_view|db_object $dbo the link whose order number is shown
     * @return string the order number of the link (empty if no order number is set)
     */
    function show_order_nbr(formula_link|component_link|term_view|db_object $dbo): string
    {
        $result = '';
        // TODO Prio 2 add an order number to term_view, until then it shows an empty text
        // guarded by class so that a mis-assigned seed component cannot fatal
        if ($dbo instanceof formula_link or $dbo instanceof component_link) {
            $result = (string)($dbo->order_nbr ?? '');
        } elseif (!$dbo instanceof term_view) {
            log_err($dbo::class . ' is not expected to have an order number');
        }
        return $result;
    }

    /**
     * TODO Prio 1 fill with the correct field
     * @param db_object $dbo the object
     * @return string the html code to show the object name to the user
     */
    function result(db_object $dbo): string
    {
        return $this->esc($dbo->name());
    }

    /**
     * TODO Prio 1 fill with the correct field
     * @param db_object $dbo the object
     * @return string the html code to show the object name to the user
     */
    function used_as_text(db_object $dbo): string
    {
        return $this->esc($dbo->name());
    }

    /**
     * TODO Prio 1 fill with the correct field
     * @param db_object $dbo the object
     * @return string the html code to show the object name to the user
     */
    function used_as_text_link(db_object $dbo): string
    {
        return $this->esc($dbo->name());
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
     * TODO Prio 1 send the '8'-prefixed pre value for the remaining select fields: the share and
     *   protection selects already send it (see sandbox::share_type_selector / protection_type_selector);
     *   still missing are the phrase type, the view and the triple from / verb / to so the confirm diff
     *   is complete for every object type and field
     *
     * @param string $url_id the url var name of the field e.g. url_var::NAME
     * @param msg_id $label the field label message id
     * @param string|null $value the current db value shown in the field and kept as the pre value
     * @param string $style_text the column style of the field
     * @param db_object|type_object|null $dbo the object, used to keep the original db snapshot as the
     *                       '8' pre value on a re-render (e.g. after a save error) instead of the change
     * @param string $refresh which part of the form a refresh icon beside the label should recalculate
     *                        e.g. url_var::REFRESH_LATEX, '' for a field without a refresh icon
     * @return string the html code of the editable field plus the hidden pre value
     */
    private function form_field_tracked(
        string                     $url_id,
        msg_id                     $label,
        ?string                    $value,
        string                     $style_text,
        db_object|type_object|null $dbo = null,
        string                     $refresh = ''
    ): string
    {
        $html = new html_base();
        $value = $value ?? '';
        // on a re-render keep the original db snapshot from the url, else the unchanged value is the snap
        $pre = ($dbo instanceof db_object) ? ($dbo->pre_value($url_id) ?? $value) : $value;
        return $html->form_field($url_id, $label, $value, html_base::INPUT_TEXT, '', $style_text, $refresh)
            . $html->form_hidden(url_var::PRE . $url_id, $pre);
    }

    function form_name(db_object|type_object $dbo, string $style_text): string
    {
        return $this->form_field_tracked(url_var::NAME, msg_id::FORM_FIELD_NAME, $dbo->name(), $style_text, $dbo);
    }

    /**
     * @param db_object|type_object $dbo
     * @return string the html code to request the description from the user
     */
    function form_description(db_object|type_object $dbo): string
    {
        return $this->form_field_tracked(
            url_var::DESCRIPTION, msg_id::FORM_FIELD_DESCRIPTION, $dbo->get_description(), view_styles::COL_SM_12, $dbo);
    }

    /**
     * edit field for the code id that links a database row to program code, e.g. of a source;
     * the code id is only shown to an admin or a developer (see user::can_see_code_id), and
     * only a user whose profile may also change it gets the input field, an admin sees the
     * code id as read only text
     *
     * @param sandbox_code_id|db_object $dbo the object with the code id used until now
     * @return string the html code of the code id field, '' if the user may not see it
     */
    function form_field_code_id(sandbox_code_id|db_object $dbo): string
    {
        global $ui_sys;

        $result = '';
        // guarded by class, because only a code id object has a code id and a mis-assigned
        // seed component must not stop the page with a fatal
        if ($dbo instanceof sandbox_code_id) {
            if ($ui_sys?->usr?->can_see_code_id() ?? false) {
                if ($ui_sys->usr->can_set_code_id()) {
                    $result = $this->form_field_tracked(
                        url_var::CODE_ID,
                        msg_id::SYSTEM_DB_FIELD_CODE_ID,
                        $dbo->code_id,
                        view_styles::COL_SM_4,
                        $dbo);
                } else {
                    // an admin may see but not change the code id
                    $result = $this->show_field_labeled($dbo->code_id ?? '', msg_id::SYSTEM_DB_FIELD_CODE_ID);
                }
            }
        } else {
            log_err($dbo::class . ' is not expected to have a code id');
        }
        return $result;
    }

    /**
     * @param db_object $dbo the object
     * @return string the html code to request the object plural from the user
     */
    function form_field_plural(db_object $dbo, string $style_text): string
    {
        return $this->form_field_tracked(url_var::PLURAL, msg_id::FORM_FIELD_PLURAL, $dbo->get_plural(), $style_text, $dbo);
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
    function form_num_value(db_object $dbo, string $style_text, user_message $msg): string
    {
        $html = new html_base();
        $val_txt = $dbo->value($msg);
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
     * @return string the html code to request a doi from the user
     */
    function form_field_doi(db_object $dbo, string $style_text = ''): string
    {
        $html = new html_base();
        $doi = $dbo->doi();
        if ($doi == null) {
            $doi = '';
        }
        if ($style_text == '') {
            $style_text = view_styles::COL_SM_12;
        }
        return $html->form_field(
            url_var::DOI,
            msg_id::FORM_FIELD_DOI,
            $doi,
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
     * shows the current order number, so that saving the form does not drop it
     * @param formula_link|db_object $dbo the formula link that is added or changed
     * @return string the html code to request the formula link priority
     */
    function form_field_formula_link_priority(formula_link|db_object $dbo): string
    {
        $html = new html_base();
        return $html->form_field(
            url_var::FORMULA_LINK_PRIO,
            msg_id::FORM_FIELD_FORMULA_LINK_PRIO,
            $this->show_order_nbr($dbo),
            html_base::INPUT_INT
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
     * shows the current order number, so that saving the form does not drop it
     * @param component_link|db_object $dbo the component link that is added or changed
     * @return string the html code to request the component position
     */
    function form_field_component_link_order_number(component_link|db_object $dbo): string
    {
        $html = new html_base();
        return $html->form_field(
            url_var::POSITION,
            msg_id::FORM_FIELD_COMPONENT_LINK,
            $this->show_order_nbr($dbo),
            html_base::INPUT_INT
        );
    }

    /**
     * @param view_relation|db_object $dbo the view relation that is added or changed
     * @return string the html code to request the view modification start position
     */
    function form_view_relation_pos(view_relation|db_object $dbo): string
    {
        $html = new html_base();
        return $html->form_field(
            url_var::POSITION,
            msg_id::FORM_FIELD_COMPONENT_LINK,
            $this->show_start_pos($dbo),
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
    function form_view(db_object $dbo, string $form_name, user_message $msg, ?view_list $msk_lst): string
    {
        return $dbo->view_selector($form_name, $msk_lst, $msg);
    }

    /**
     * create the html code for the form element to select the parent view
     * @param db_object $dbo the frontend object with the view used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param view_list|null $msk_lst cached list of views for fast selection
     * @return string the html code to select the view
     */
    function form_parent_view(db_object $dbo, string $form_name, user_message $msg, ?view_list $msk_lst): string
    {
        return $dbo->view_selector($form_name, $msk_lst, $msg,
            url_var::VIEW_PARENT, msg_id::FORM_SELECT_PARENT_VIEW);
    }

    /**
     * create the html code for the form element to select the child view
     * @param db_object $dbo the frontend object with the view used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param view_list|null $msk_lst cached list of views for fast selection
     * @return string the html code to select the view
     */
    function form_child_view(db_object $dbo, string $form_name, user_message $msg, ?view_list $msk_lst): string
    {
        return $dbo->view_selector($form_name, $msk_lst, $msg,
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
    function form_view_default(db_object $dbo, string $form_name, user_message $msg, ?view_list $msk_lst): string
    {
        return $dbo->view_selector($form_name, $msk_lst, $msg);
    }

    /**
     * create the html code for the form element to select one or many views
     * @param db_object $dbo the frontend object with the view used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param view_list|null $msk_lst cached list of views for fast selection
     * @return string the html code to select the view
     */
    function form_views(db_object $dbo, string $form_name, user_message $msg, ?view_list $msk_lst): string
    {
        return $dbo->view_selector($form_name, $msk_lst, $msg);
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
    function form_phrase_type(db_object $dbo, string $form_name, user_message $msg, ?type_lists $typ_lst): string
    {
        return $dbo->phrase_type_selector($form_name, $msg, $typ_lst);
    }

    /**
     * create the html code for the form element to select the source type
     * @param db_object $dbo the frontend source object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the source type
     */
    function form_source_type(db_object $dbo, string $form_name, user_message $msg, ?type_lists $typ_lst): string
    {
        return $dbo->source_type_selector($form_name, $typ_lst, $msg);
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
    function form_formula_type(db_object $dbo, string $form_name, user_message $msg, ?type_lists $typ_lst): string
    {
        return $dbo->formula_type_selector($form_name, $msg, $typ_lst);
    }

    /**
     * create the html code for the form element to select the view type
     * @param db_object $dbo the frontend view object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the view type
     */
    function form_view_type(db_object $dbo, string $form_name, user_message $msg, ?type_lists $typ_lst): string
    {
        return $dbo->view_type_selector($form_name, $typ_lst, $msg);
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
    function form_view_style(db_object $dbo, string $form_name, user_message $msg, ?type_lists $typ_lst): string
    {
        return $dbo->style_selector($form_name, $typ_lst, $msg);
    }

    /**
     * create the html code for the form element to select the component type
     * @param db_object $dbo the frontend component object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the component type
     */
    function form_component_type(db_object $dbo, string $form_name, user_message $msg, ?type_lists $typ_lst): string
    {
        return $dbo->component_type_selector($form_name, $typ_lst, $msg);
    }

    /**
     * create the html code for the form element to select the component style
     * @param db_object $dbo the frontend component object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the component style
     */
    function form_component_style(db_object $dbo, string $form_name, user_message $msg, ?type_lists $typ_lst): string
    {
        return $dbo->component_style_selector($form_name, $typ_lst, $msg);
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
    function form_table_linked_view(db_object $dbo, string $form_name, user_message $msg, ?view_list $msk_lst): string
    {
        return $dbo->view_selector($form_name, $msk_lst, $msg);
    }

    /**
     * create the html code for the form element to enter the formula expression
     * @param db_object $dbo the frontend formula object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @return string the html code to select the formula type
     */
    function form_formula_expression(db_object $dbo, string $form_name): string
    {
        // the expression field posts the user expression (not the need-all flag, which is a
        // separate checkbox with the same key); the raw expression is passed so form_field escapes
        // the quotes exactly once - passing a pre-escaped value would double-encode the quotes.
        // form_field_tracked also sends the '8'-prefixed pre value so the confirm view can show the
        // formula text before the change (see url_var::PRE)
        // 2/3 of the width, because the expression with the term links is shown in the last third
        // the refresh icon takes the changes of the latex field over into the expression
        return $this->form_field_tracked(
            url_var::USER_EXPRESSION,
            msg_id::FORM_FIELD_FORMULA_EXPRESSION,
            $dbo->get_usr_text(),
            view_styles::COL_SM_8,
            $dbo,
            url_var::REFRESH_EXPRESSION);
    }

    /**
     * create the html code for the form element to enter the formula in the latex format
     * @param db_object $dbo the frontend formula object with the latex text used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @return string the html code of the latex input field
     */
    function form_formula_latex(db_object $dbo, string $form_name): string
    {
        // form_field_tracked also sends the '8'-prefixed pre value so the confirm view can show
        // the latex text before the change (see url_var::PRE)
        // 2/3 of the width, because the formatted latex with the term links is shown in the last third
        // the refresh icon creates the latex again based on the expression
        return $this->form_field_tracked(
            url_var::LATEX,
            msg_id::FORM_FIELD_FORMULA_LATEX,
            $dbo->get_latex(),
            view_styles::COL_SM_8,
            $dbo,
            url_var::REFRESH_LATEX);
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
     * @return string combine the next components to one row
     */
    function row_start(): string
    {
        $html = new html_base();
        return $html->row_start();
    }

    /**
     * @return string combine the next components to one row and align to the right
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
