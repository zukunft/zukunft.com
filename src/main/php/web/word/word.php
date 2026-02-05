<?php

/*

    web/word/word.php - create HTML code to display a words based on the api json message
    -----------------

    The main sections of this object are
    - object vars:       the variables of this word object
    - set and get:       to capsule the vars from unexpected changes
    - api:               set the object vars based on the api json message and create a json for the backend
    - cast:              create related frontend objects e.g. the phrase of a triple
    - load:              get an api json from the backend and
    - related:           load related objects from the backend
    - base:              html code for the single object vars
    - buttons:           html code for the buttons e.g. to add, edit, del, link or unlink
    - select:            html code to select parameter like the type
    - table:             html code for object vars within a table
    - actions:           send change messages to the backend
    - log:               show the hist changes related to this object
    - type:              link to code actions
    - views:             system view to add, change or delete the word (to be deprecated)


    This file is part of the frontend of zukunft.com - calc with words

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

    Copyright (c) 1995-2025 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

namespace Zukunft\ZukunftCom\main\php\web\word;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::TYPES . 'type_lists.php';
include_once html_paths::HTML . 'button.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::HTML . 'html_selector.php';
include_once html_paths::HTML . 'styles.php';
//include_once html_paths::HELPER . 'config.php';
include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::LOG . 'change_log_named.php';
//include_once html_paths::LOG . 'user_log_display.php';
include_once html_paths::PHRASE . 'phrase.php';
include_once html_paths::PHRASE . 'phrase_list.php';
//include_once html_paths::PHRASE . 'term.php';
include_once html_paths::SANDBOX . 'sandbox_code_id.php';
include_once html_paths::SANDBOX . 'sandbox_typed.php';
include_once html_paths::SYSTEM . 'back_trace.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::VERB . 'verb_list.php';
//include_once html_paths::VIEW . 'view_list.php';
include_once paths::API_OBJECT . 'api_message.php';
include_once paths::SHARED_CONST . 'def.php';
include_once paths::SHARED_CONST . 'rest_ctrl.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED_ENUM . 'foaf_direction.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_TYPES . 'phrase_types.php';
include_once paths::SHARED . 'api.php';
include_once paths::SHARED . 'url_var.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'library.php';

use Zukunft\ZukunftCom\main\php\api\api_message;
use Zukunft\ZukunftCom\main\php\web\helper\config;
use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\html\button;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\log\change_log_named;
use Zukunft\ZukunftCom\main\php\web\log\user_log_display;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\web\phrase\term;
use Zukunft\ZukunftCom\main\php\web\sandbox\sandbox_code_id;
use Zukunft\ZukunftCom\main\php\web\html\styles;
use Zukunft\ZukunftCom\main\php\web\system\back_trace;
use Zukunft\ZukunftCom\main\php\web\types\type_lists;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\verb\verb_list;
use Zukunft\ZukunftCom\main\php\web\view\view_list;
use Zukunft\ZukunftCom\main\php\shared\const\def;
use Zukunft\ZukunftCom\main\php\shared\const\rest_ctrl;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\enum\foaf_direction;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\types\phrase_types;
use Zukunft\ZukunftCom\main\php\shared\url_var;

class word extends sandbox_code_id
{

    /*
     * const
     */

    // crud views
    const string VIEW_ADD = views::WORD_ADD;
    const string VIEW_EDIT = views::WORD_EDIT;
    const string VIEW_DEL = views::WORD_DEL;

    // crud message id
    const msg_id MSG_ADD = msg_id::WORD_ADD;
    const msg_id MSG_EDIT = msg_id::WORD_EDIT;
    const msg_id MSG_DEL = msg_id::WORD_DEL;


    /*
     * object vars
     */

    // the language specific forms
    // TODO make most ui vars public and check the mappings
    private ?string $plural = null;

    // the main parent phrase
    private ?phrase $parent = null;

    // the impact used to sort the words
    public float $impact = 0.0;


    /*
     * construct and map
     */

    /**
     * set the vars of this word frontend object bases on the url array
     * public because it is reused e.g. by the phrase group display object
     * @param array $url_array an array based on $_GET from a form submit
     * @param user_message $usr_msg to enrich with warnings, problems and solutions
     * @param data_object|null $dto the cache as a parameter to be able to simulate test conditions
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function url_mapper(array $url_array, user_message $usr_msg, data_object|null $dto = null): user_message
    {
        parent::url_mapper($url_array, $usr_msg, $dto);
        if ($usr_msg->is_ok()) {
            if (array_key_exists(url_var::PLURAL, $url_array)) {
                $this->set_plural($url_array[url_var::PLURAL]);
            } else {
                $this->set_plural(null);
            }
            if (array_key_exists(url_var::IMPACT, $url_array)) {
                if ($url_array[url_var::IMPACT] != null) {
                    $this->impact = $url_array[url_var::IMPACT];
                }
            }
            if (array_key_exists(url_var::VIEW, $url_array)) {
                if ($url_array[url_var::VIEW] != null) {
                    $this->view_id = $url_array[url_var::VIEW];
                }
            }
        }
        return $usr_msg;
    }

    /**
     * set the vars of this object bases on the api json array
     * public because it is reused e.g. by the phrase group display object
     * @param array $json_array an api json message
     * @param user_message $msg ok or a warning e.g. if the server version does not match
     * @return bool true if the mapping has been completed successful
     */
    function api_mapper(array $json_array, user_message $msg): bool
    {
        // get body from message
        $api_msg = new api_message();
        $json_array = $api_msg->validate($json_array);

        parent::api_mapper($json_array, $msg);
        if (array_key_exists(json_fields::PLURAL, $json_array)) {
            $this->set_plural($json_array[json_fields::PLURAL]);
        } else {
            $this->set_plural(null);
        }
        if (array_key_exists(json_fields::PLURAL, $json_array)) {
            $this->set_plural($json_array[json_fields::PLURAL]);
        } else {
            $this->set_plural(null);
        }
        if (array_key_exists(json_fields::IMPACT, $json_array)) {
            if ($json_array[json_fields::IMPACT] != null) {
                $this->impact = $json_array[json_fields::IMPACT];
            } else {
                $this->impact = 0.0;
            }
        } else {
            $this->impact = 0.0;
        }
        if (array_key_exists(json_fields::PARENT, $json_array)) {
            $this->set_parent($json_array[json_fields::PARENT]);
        } else {
            $this->set_parent(null);
        }
        return $msg->is_ok();
    }


    /*
     * api
     */

    /**
     * create an api json array for the backend based on this frontend object
     * @return array the json message array to send the updated data to the backend
     * an array is used (instead of a string) to enable combinations of api_array() calls
     */
    function api_array(): array
    {
        $vars = parent::api_array();

        // usage and impact are not included here because this system value is never updated by the frontend
        $vars[json_fields::PLURAL] = $this->get_plural();
        if ($this->has_parent()) {
            $vars[json_fields::PARENT] = $this->parent()->api_array();
        }
        return array_filter($vars, fn($value) => !is_null($value) && $value !== '');
    }


    /*
     * set and get
     */

    function set_plural(?string $plural): void
    {
        $this->plural = $plural;
    }

    function get_plural(): ?string
    {
        return $this->plural;
    }

    function set_parent(?phrase $parent): void
    {
        $this->parent = $parent;
    }

    function parent(): ?phrase
    {
        return $this->parent;
    }

    function set_view_id(?int $view_id): void
    {
        $this->view_id = $view_id;
    }

    /**
     * @param string|null $code_id the code id of the phrase type
     */
    function set_type(?string $code_id): void
    {
        global $sys;
        if ($code_id == null) {
            $this->set_type_id();
        } else {
            $this->set_type_id($sys->typ_lst->phr_typ->id($code_id));
        }
    }


    /*
     * cast
     */

    /**
     * to have a similar cast function in word that in triple for the phrase
     * @return $this
     */
    function word(): word
    {
        return $this;
    }

    /**
     * @returns phrase the phrase display object base on this word object
     */
    function phrase(): phrase
    {
        $phr = new phrase();
        $phr->set_obj($this);
        return $phr;
    }

    /**
     * @returns term the word object cast into a term object
     */
    function term(): term
    {
        $trm = new term();
        $trm->set_obj($this);
        return $trm;
    }


    /*
     * load
     */

    /*
     * related
     */

    /**
     * get the parent phrases of the given phrase
     * if a phrase list is given get only the parent phrases within the list
     * if no phrase list is given get the phrases from the api
     * e.g. for Zurich the list is City and Canton based on a phrase list with City, Canton and Country
     * but  for Zurich the list is City, Canton and company based on a phrase list with company, City, Canton and Country
     * @param phrase_list|null $phr_lst
     * @param int $levels the number of parent levels
     * @return phrase_list
     */
    function parents(?phrase_list $phr_lst = null, int $levels = 1): phrase_list
    {
        $lst = new phrase_list();
        $lst->load_related($this->phrase(), foaf_direction::UP);
        return $lst;
    }

    /**
     * get all child phrases related to the given word
     * e.g. for city at least Zurich, Bern and Geneva are returned
     *
     * @param phrase_list|null $phr_lst if the cache list is given only phrase from this list are returned
     * @param int $levels
     * @return phrase_list
     */
    function children(?phrase_list $phr_lst = null, int $levels = 1): phrase_list
    {
        $lst = new phrase_list();
        $lst->load_related($this->phrase(), foaf_direction::DOWN);
        return $lst;
    }


    /*
     * base
     */

    /**
     * display a word with a link to the main page for the word
     * @param string|null $back the back trace url for the undo functionality
     * @param string $style the CSS style that should be used
     * @param int $msk_id database id of the view that should be shown
     * @returns string the html code
     */
    function name_link(?string $back = '', string $style = '', int $msk_id = views::WORD_ID): string
    {
        return parent::name_link($back, $style, $msk_id);
    }


    /*
     * buttons
     */

    /**
     * @returns string the html code to display a bottom to edit the word link in a table cell
     */
    function btn_unlink(int $link_id, string $back = ''): string
    {
        $url = new html_base()->url(rest_ctrl::LINK . rest_ctrl::REMOVE, $link_id, $this->id());
        return new button($url, $back)->del(msg_id::WORD_UNLINK);
    }


    /*
     * select
     */

    /**
     * wrapper for the word type selector
     * to prevent type changes of internal formula words
     * as a second line of defence
     * @param string $form the name of the html form
     * @param string $style the CSS style that should be used
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the phrase type
     */
    function dsp_type_selector(string $form, string $style = '', ?type_lists $typ_lst = null): string
    {
        global $sys;
        $result = '';
        if ($sys->typ_lst->phr_typ->code_id($this->type_id()) == phrase_types::FORMULA_LINK) {
            $result .= ' type: ' . $sys->typ_lst->phr_typ->name($this->type_id());
        } else {
            $result .= $this->phrase_type_selector($form, $typ_lst);
        }
        return $result;
    }

    /**
     * create the HTML code to select a phrase type
     * and select the phrase type of this word
     * @param string $form the name of the html form
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the phrase type
     */
    public function phrase_type_selector(string $form, ?type_lists $typ_lst): string
    {
        $used_phrase_id = $this->type_id();
        if ($used_phrase_id == null) {
            $used_phrase_id = $typ_lst->html_phrase_types->default_id();
        }
        return $typ_lst->html_phrase_types->selector($form, $used_phrase_id);
    }


    /*
     * table
     */

    /**
     * @param string $back the back trace url for the undo functionality
     * @param string $style the CSS style that should be used
     * @returns string the word as a table cell
     */
    function th(string $back = '', string $style = ''): string
    {
        return (new html_base)->th($this->name_link($back, $style));
    }

    /**
     * @return string the html code for a table row with the word
     */
    function tr(): string
    {
        return new html_base()->tr($this->td());
    }

    /**
     * @param string $back the back trace url for the undo functionality
     * @param string $style the CSS style that should be used
     * @returns string the word as a table cell
     */
    function td(string $back = '', string $style = '', int $intent = 0): string
    {
        $cell_text = $this->name_link($back, $style);
        return (new html_base)->td($cell_text, '', $intent);
    }


    /*
     * change action
     */

    /**
     * @returns string html code to display a single word in a column and allow to delete it
     */
    function dsp_del(): string
    {
        $html = new html_base();
        $name = $this->td();
        $btn = $html->td($this->btn_del());
        return $html->tr($name . $btn);
    }

    /**
     * allow the user to unlink a word
     */
    function dsp_unlink(int $link_id): string
    {
        $html = new html_base();
        $name = $this->td();
        $btn = $html->td($this->btn_unlink($link_id));
        return $html->tr($name . $btn);
    }


    /*
     * log
     */

    /**
     * @param back_trace $back the last changes to allow undo actions by the user
     * @return string with the HTML code to show the last changes of the view of this word
     */
    function log_view(back_trace $back): string
    {
        $log_ui = new change_log_named();
        return '';
    }


    /*
     * type
     */

    /**
     * repeating of the backend functions in the frontend to enable filtering in the frontend and reduce the traffic
     * repeated in triple, because a triple can have it's own type
     * kind of repeated in phrase to use hierarchies
     *
     * @param string $type the ENUM string of the fixed type
     * @returns bool true if the word has the given type
     * TODO Switch to php 8.1 and real ENUM
     */
    function is_type(string $type): bool
    {
        global $sys;
        $result = false;
        if ($this->type_id() != Null) {
            if ($this->type_id() == $sys->typ_lst->phr_typ->id($type)) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * @return bool true if the word has the type "time" e.g. "2022 (year)"
     */
    function is_time(): bool
    {
        return $this->is_type(phrase_types::TIME);
    }

    /**
     * @return bool true if the word has the type "time" e.g. "monthly"
     */
    function is_time_jump(): bool
    {
        return $this->is_type(phrase_types::TIME_JUMP);
    }

    /**
     * @return bool true if the word has the type "measure" (e.g. "meter" or "CHF")
     * in case of a division, these words are excluded from the result
     * in case of add, it is checked that the added value does not have a different measure
     */
    function is_measure(): bool
    {
        return $this->is_type(phrase_types::MEASURE);
    }

    /**
     * @return bool true if the word has the type "information" (e.g. "1967 (year of definition)")
     * if used for a value these phrases are shown only as a tooltip
     */
    function is_info(): bool
    {
        return $this->is_type(phrase_types::INFO);
    }

    /**
     * @return bool true if the word has the type "scaling" (e.g. "million", "million" or "one"; "one" is a hidden scaling type)
     */
    function is_scaling(): bool
    {
        $result = false;
        if ($this->is_type(phrase_types::SCALING)
            or $this->is_type(phrase_types::SCALING_HIDDEN)) {
            $result = true;
        }
        return $result;
    }

    /**
     * @return bool true if the word has the type "scaling_percent" (e.g. "percent")
     */
    function is_percent(): bool
    {
        return $this->is_type(phrase_types::PERCENT);
    }

    /**
     * @return bool true if the word is normally not shown to the user e.g. scaling of one is assumed
     */
    function is_hidden(): bool
    {
        return $this->is_type(phrase_types::SCALING_HIDDEN);
    }

    /*
     * TODO deprecate the following functions
     */


    /*
     * views
     */

    /**
     * display a word as the view header
     * @param phrase|null $is_part_of the word group as a hint to the user
     *        e.g. City Zurich because in many cases if just the word Zurich is given the assumption is,
     *             that the Zurich (City) is the phrase to select
     * @returns string the HTML code to display a word
     */
    function header(?phrase $is_part_of = null): string
    {
        $html = new html_base();

        $result = '';

        if ($this->id() <= 0) {
            $result .= 'no word selected';
        } else {
            // load the word parameters if not yet done
            if ($this->name == "") {
                log_err('Name for word with id ' . $this->id() . ' is empty', 'word_ui->ui_header');
            }

            //$default_view_id = cl(DBL_VIEW_WORD);
            $title = '';
            if ($is_part_of != null) {
                if ($is_part_of->name() <> '' and $is_part_of->name() <> 'not set') {
                    $url = $html->url(rest_ctrl::VIEW, $is_part_of->id(), '', url_var::WORDS);
                    $title .= ' (' . $html->ref($url, $is_part_of->name()) . ')';
                }
            }
            $url = $html->url(rest_ctrl::WORD . rest_ctrl::UPDATE, $this->id(), $this->id());
            $title .= $html->ref($url, $html->span($this->name(), styles::STYLE_GLYPH), 'Rename word');
            $result .= $html->dsp_text_h1($title);
        }

        return $result;
    }

    /*
     * TODO to be replaced by a system view
     */

    /**
     * HTML code to edit all word fields
     * @param string $dsp_graph the html code of the related phrases
     * @param string $dsp_log the html code of the change log
     * @param string $dsp_frm the html code of the linked formulas
     * @param string $dsp_type the html code of the type selector formulas
     * @param string $back the html code to be opened in case of a back action
     * @return string the html code to display the edit page
     */
    function form_edit(string $dsp_graph, string $dsp_log, string $dsp_frm, string $dsp_type, string $back = ''): string
    {
        $html = new html_base();
        $result = '';

        if ($this->id() > 0) {
            $header = $html->text_h2('Change "' . $this->name . '"');
            $hidden_fields = $html->form_hidden("id", $this->id());
            $hidden_fields .= $html->form_hidden("back", $back);
            $hidden_fields .= $html->form_hidden("confirm", '1');
            $detail_fields = $dsp_frm;
            $detail_fields .= $html->form_text(url_var::PLURAL, $this->get_plural(), msg_id::FORM_FIELD_PLURAL);
            $detail_fields .= $html->form_text(url_var::DESCRIPTION, $this->get_description(), msg_id::FORM_FIELD_DESCRIPTION);
            $detail_fields .= $dsp_type;
            $detail_row = $html->fr($detail_fields) . '<br>';
            $result = $header
                . $html->form(views::WORD_EDIT, $hidden_fields . $detail_row)
                . '<br>' . $dsp_graph;
        }

        $result .= $dsp_log;

        return $result;
    }


    /**
     * HTML code to edit all word fields
     */
    function dsp_edit(string $back = ''): string
    {
        $cfg = new config();
        $row_limit = $cfg->get_by([words::ROW, words::LIMIT], def::FALLBACK_DB_PAGE_ROWS);
        $html = new html_base();
        $phr_lst_up = $this->parents();
        $phr_lst_down = $this->children();
        $dsp_graph = $phr_lst_up->dsp_graph($this->phrase(), $back);
        $dsp_graph .= $phr_lst_down->dsp_graph($this->phrase(), $back);
        $wrd_ui = $this;
        // collect the display code for the user changes
        $dsp_log = '';
        $changes = $this->dsp_hist(1, $row_limit, '', $back);
        if (trim($changes) <> "") {
            $dsp_log .= $html->dsp_text_h3("Latest changes related to this word", "change_hist");
            $dsp_log .= $changes;
        }
        $changes = $this->dsp_hist_links(0, $row_limit, '', $back);
        if (trim($changes) <> "") {
            $dsp_log .= $html->dsp_text_h3("Latest link changes related to this word", "change_hist");
            $dsp_log .= $changes;
        }
        return $wrd_ui->form_edit(
            $dsp_graph,
            $dsp_log,
            //$this->dsp_formula($back),
            $this->dsp_type_selector(views::WORD_EDIT),
            $back);
    }

    /*
     * to review
     */

    function dsp_graph(foaf_direction $direction, verb_list $link_types, string $back = ''): string
    {
        return $this->phrase()->dsp_graph($direction, $link_types, $back);
    }

    /**
     * returns the html code to select a word link type
     * database link must be open
     * TODO: similar to verb->dsp_selector maybe combine???
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     */
    function selector_link($id, $form, $back, ?type_lists $typ_lst): string
    {
        /*
        log_debug('verb id ' . $id);
        global $db_con;

        $result = '';

        $sql_name = "";
        if ($db_con->get_type() == sql_db::POSTGRES) {
            $sql_name = "CASE WHEN (name_reverse  <> '' IS NOT TRUE AND name_reverse <> verb_name) THEN CONCAT(verb_name, ' (', name_reverse, ')') ELSE verb_name END AS name";
        } elseif ($db_con->get_type() == sql_db::MYSQL) {
            $sql_name = "IF (name_reverse <> '' AND name_reverse <> verb_name, CONCAT(verb_name, ' (', name_reverse, ')'), verb_name) AS name";
        } else {
            log_err('Unknown db type ' . $db_con->get_type());
        }
        $sql_avoid_code_check_prefix = "SELECT";
        $sql = $sql_avoid_code_check_prefix . " * FROM (
            SELECT verb_id AS id, 
                   " . $sql_name . ",
                   words
              FROM verbs 
      UNION SELECT verb_id * -1 AS id, 
                   CONCAT(name_reverse, ' (', verb_name, ')') AS name,
                   words
              FROM verbs 
             WHERE name_reverse <> '' 
               AND name_reverse <> verb_name) AS links
          ORDER BY words DESC, name;";
        $sel = new html_selector;
        $sel->form = $form;
        $sel->name = 'verb';
        $sel->sql = $sql;
        $sel->selected = $id;
        $sel->dummy_text = '';
        */
        global $usr;
        // TODO add $id to the parameters
        $result = $typ_lst->html_verbs->selector($form);

        if ($usr->is_admin()) {
            // admin users should always have the possibility to create a new link type
            $result .= \Zukunft\ZukunftCom\main\php\web\btn_add('add new link type', '/http/verb_add.php?back=' . $back);
        }

        return $result;
    }

    /**
     * to select an existing word to be added
     */
    private function selector_add($id, $form): string
    {
        $pattern = '';
        $phr_lst = new word_list();
        $phr_lst->load_like($pattern);
        //$sel->dummy_text = '... or select an existing word to link it';
        return $phr_lst->selector($form, $id, url_var::WORD, msg_id::FORM_SELECT_WORD);
    }


    /*
     * select
     */

    /**
     * @returns string the html code to select a word
     */
    function selector_word(int $id, int $pos, string $form): string
    {
        $pattern = '';
        $phr_lst = new word_list();
        $phr_lst->load_like($pattern);

        if ($pos > 0) {
            $name = url_var::WORD_POS . $pos;
        } else {
            $name = url_var::WORD;
        }
        return $phr_lst->selector($form, $id, $name, msg_id::FORM_SELECT_WORD);
    }

    /**
     * display the history of a word
     * maybe move this to a new object user_log_display
     * because this is very similar to a value linked function
     */
    function dsp_hist(int $page = 1, int $size = 20, string $call = '', string $back = ''): string
    {
        $log_ui = new user_log_display();
        return $log_ui->dsp_hist(word::class, $this->id(), $size, $page, '', null);
    }

    /**
     * display the history of a word
     */
    function dsp_hist_links($page, $size, $call, $back): string
    {
        log_debug($this->id() . ",size" . $size . ",b" . $size);
        $result = ''; // reset the html code var

        $log_ui = new user_log_display();
        $log_ui->id = $this->id();
        $log_ui->type = word::class;
        $log_ui->page = $page;
        $log_ui->size = $size;
        $log_ui->call = $call;
        $log_ui->back = $back;
        $result .= $log_ui->dsp_hist_links();

        log_debug('done');
        return $result;
    }


    /*
     * selectors
     */

    /**
     * create the HTML code to select a view
     * @param string $form the name of the html form
     * @param view_list $msk_lst with the suggested views
     * @param string $name the unique html field name for the selection of the view
     * @return string the html code to select a view
     */
    public function view_selector(
        string    $form,
        view_list $msk_lst,
        string    $name = url_var::VIEW,
        msg_id    $msg_id = msg_id::FORM_SELECT_VIEW
    ): string
    {
        $view_id = $this->view_id();
        if ($view_id == null) {
            $view_id = $msk_lst->default_id($this);
        }
        $msk_lst = $msk_lst->ex_system();
        $msk_lst = $msk_lst->ex_non_phrase();
        return $msk_lst->selector($form, $view_id, $name, $msg_id);
    }


    /*
     * fixed
     */

    function math(): word
    {
        $wrd = new word();
        $wrd->id = words::MATH_ID;
        $wrd->name = words::MATH;
        return $wrd;
    }


    /*
     * internal
     */

    private function has_parent(): bool
    {
        if ($this->parent() == null) {
            return false;
        } else {
            return true;
        }
    }

}
