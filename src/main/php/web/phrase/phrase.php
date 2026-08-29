<?php

/*

    web/phrase/phrase.php - to create the html code to display a word or triple
    ---------------------

    $phr is the suggested var name

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

    Copyright (c) 1995-2023 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\web\phrase;

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::SANDBOX . 'combine_named.php';
include_once html_paths::TYPES . 'type_lists.php';
include_once html_paths::HTML . 'button.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::SHARED_CONST . 'rest_ctrl.php';
include_once html_paths::PHRASE . 'phrase_list.php';
include_once html_paths::USER . 'user_message.php';
//include_once html_paths::VERB . 'verb.php';
include_once html_paths::VERB . 'verb_list.php';
//include_once html_paths::WORD . 'word.php';
//include_once html_paths::WORD . 'word_list.php';
include_once html_paths::WORD . 'triple.php';
include_once html_paths::SHARED_CONST . 'def.php';
include_once html_paths::SHARED_CONST . 'views.php';
include_once html_paths::SHARED_CONST . 'words.php';
include_once html_paths::SHARED_ENUM . 'foaf_direction.php';
include_once html_paths::SHARED_ENUM . 'languages.php';
include_once html_paths::SHARED_ENUM . 'messages.php';
include_once html_paths::SHARED_TYPES . 'api_type_list.php';
include_once html_paths::SHARED_TYPES . 'verbs.php';
include_once html_paths::SHARED . 'api.php';
include_once html_paths::SHARED . 'json_fields.php';
include_once html_paths::SHARED . 'url_var.php';

use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\types\type_lists;
use Zukunft\ZukunftCom\main\php\web\sandbox\combine_named;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\verb\verb;
use Zukunft\ZukunftCom\main\php\web\verb\verb_list;
use Zukunft\ZukunftCom\main\php\web\word\triple;
use Zukunft\ZukunftCom\main\php\web\word\word;
use Zukunft\ZukunftCom\main\php\web\word\word_list;
use Zukunft\ZukunftCom\main\php\shared\const\def;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\enum\foaf_direction;
use Zukunft\ZukunftCom\main\php\shared\enum\languages;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\main\php\shared\api;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\url_var;

class phrase extends combine_named
{

    /*
     * construct and map
     */

    /**
     * set the vars of this phrase frontend object based on the url array
     * dispatches to word::url_mapper or triple::url_mapper depending on the
     * PHRASE_CLASS url field; falls back to a currently set obj if no class is provided
     * @param array $url_array an array based on $_GET from a form submit
     * @param user_message $msg to enrich with warnings, problems and solutions
     * @param data_object|null $dto the cache as a parameter to be able to simulate test conditions
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function url_mapper(array $url_array, user_message $msg, data_object|null $dto = null): user_message
    {
        // PHRASE_CLASS encodes word vs triple; mirrors the OBJECT_CLASS dispatch in api_mapper
        $class = $url_array[url_var::PHRASE_CLASS] ?? null;
        if ($class === json_fields::CLASS_WORD) {
            $this->set_obj(new word());
        } elseif ($class === json_fields::CLASS_TRIPLE) {
            $this->set_obj(new triple());
        }
        $obj = $this->obj();
        if ($obj instanceof triple) {
            $obj->url_mapper($url_array, $msg, $dto);
            $this->set_id($obj->id());
        } elseif ($obj instanceof word) {
            $obj->url_mapper($url_array, $msg, $dto);
        } else {
            $msg->add_error_text('Phrase class missing in url ' . json_encode($url_array));
        }
        return $msg;
    }

    /**
     * set the vars of this phrase frontend object bases on the api json array
     * @param array $json_array an api json message
     * @param user_message $msg ok or a warning e.g. if the server version does not match
     * @return bool true if the mapping has been completed successfully
     */
    function api_mapper(array $json_array, user_message $msg): bool
    {
        if (array_key_exists(json_fields::OBJECT_CLASS, $json_array)) {
            if ($json_array[json_fields::OBJECT_CLASS] == json_fields::CLASS_WORD) {
                $wrd_ui = new word();
                $wrd_ui->api_mapper($json_array, $msg);
                $this->set_obj($wrd_ui);
            } elseif ($json_array[json_fields::OBJECT_CLASS] == json_fields::CLASS_TRIPLE) {
                $trp_ui = new triple();
                $trp_ui->api_mapper($json_array, $msg);
                $this->set_obj($trp_ui);
                // switch the phrase id to the object id
                $this->set_id($trp_ui->id());
            } else {
                $msg->add_error_text('Json class ' . $json_array[json_fields::OBJECT_CLASS] . ' not expected for a phrase');
            }
        } else {
            $msg->add_error_text('Json class missing, but expected for a phrase');
        }
        return $msg->is_ok();
    }


    /*
     * api
     */

    /**
     * @return array the json message array to send the updated data to the backend
     */
    function api_array(api_type_list|array $typ_lst, user_message $msg): array
    {
        $vars = array();
        if ($this->is_word()) {
            $vars[json_fields::OBJECT_CLASS] = json_fields::CLASS_WORD;
        } else {
            $trp = $this->obj();
            if ($trp != null) {
                $vars[json_fields::OBJECT_CLASS] = json_fields::CLASS_TRIPLE;
                $vars[json_fields::FROM] = $trp->get_from()->id();
                $vars[json_fields::VERB] = $trp->get_verb()->id();
                $vars[json_fields::TO] = $trp->get_to()->id();
            }
        }
        $vars[json_fields::ID] = $this->obj_id();
        $vars[json_fields::NAME] = $this->name();
        $vars[json_fields::DESCRIPTION] = $this->get_description();
        $vars[json_fields::TYPE] = $this->type_id($msg);
        $vars[json_fields::PLURAL] = $this->get_plural();
        // TODO add exclude field and move to a parent object?
        if ($this->obj()?->share_id() != null) {
            $vars[json_fields::SHARE] = $this->obj()?->share_id();
        }
        if ($this->obj()?->protection_id() != null) {
            $vars[json_fields::PROTECTION] = $this->obj()?->protection_id();
        }
        return array_filter($vars, fn($value) => !is_null($value) && $value !== '');
    }

    /**
     * set the vars of this phrase html display object bases on the api message
     * @param string $json_api_msg an api json message as a string
     * @param user_message $msg ok or a warning e.g. if the server version does not match
     * @return bool true if the mapping has been completed successfully
     */
    function set_from_json(string $json_api_msg, user_message $msg): bool
    {
        return $this->api_mapper(json_decode($json_api_msg, true), $msg);
    }


    /*
     * set and get
     */

    /**
     * set the object id based on the given phrase id
     * must have the same logic as the database view and the api
     * @param int $id the phrase id that is converted to the object id
     * @return void
     */
    function set_id(int $id): void
    {
        $this->set_obj_id(abs($id));
    }

    /**
     * @return int the id of the phrase generated from the object id
     * e.g 1 for a word with id 1, -1 for a triple with id 1
     */
    function id(): int
    {
        if ($this->is_word()) {
            return $this->obj_id();
        } else {
            return $this->obj_id() * -1;
        }
    }

    /**
     * @return int|string|null the id of the word or triple
     * e.g 1 for a word with id 1, 1 for a triple with id 1
     */
    function obj_id(): int|string|null
    {
        return $this->obj()?->id();
    }

    function get_verb(): ?verb
    {
        if ($this->is_triple()) {
            return $this->obj()->get_verb();
        } else {
            return null;
        }
    }

    /**
     * @return int|null the id of the connecting verb when this phrase wraps a triple, else null
     */
    function get_verb_id(): ?int
    {
        if ($this->is_triple()) {
            return $this->get_verb()?->id();
        } else {
            return null;
        }
    }

    function get_from(): ?phrase
    {
        if ($this->is_triple()) {
            return $this->obj()->get_from();
        } else {
            return null;
        }
    }

    function get_to(): ?phrase
    {
        if ($this->is_triple()) {
            return $this->obj()->get_to();
        } else {
            return null;
        }
    }


    /*
     * classifications
     */

    /**
     * @return bool true if this phrase is a word or supposed to be a word
     */
    function is_word(): bool
    {
        if ($this->obj() != null) {
            if ($this->obj()::class == word::class) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    function is_triple(): bool
    {
        return !$this->is_word();
    }


    /*
     * info
     */

    function is_same(phrase $phr): bool
    {
        $result = false;
        $this_id = $this->id();
        $phr_id = $phr->id();
        if ($this_id != 0 and $phr_id != 0) {
            if ($this_id == $phr_id) {
                $result = true;
            }
        } else {
            $this_name = $this->name();
            $phr_name = $phr->name();
            if ($this_name == $phr_name) {
                $result = true;
            }
        }
        return $result;
    }

    function is_type_phrase(phrase $phr, user_message $msg): bool
    {
        global $ui_sys;

        $result = false;
        $typ_id = $this->type_id($msg);
        if ($typ_id != null) {
            $typ = $ui_sys?->typ_lst_cache?->phr_typ?->get($this->type_id($msg));
            if ($typ != null) {
                $typ_phr_lst = $typ->type_phrases($msg);
                foreach ($typ_phr_lst->lst() as $typ_phr) {
                    if ($phr->is_same($typ_phr)) {
                        $result = true;
                    }
                }
            } else {
                log_err('type for ' . $this->dsp_id() . ' not found');
            }
        }
        return $result;
    }

    /**
     * @return bool true if this phrase is of type percent
     */
    function is_percent(user_message $msg): bool
    {
        return $this->obj()->is_percent($msg);
    }

    function is_measure(user_message $msg): bool
    {
        return $this->obj()->is_measure($msg);
    }

    /**
     * @return bool true if the wrapped word or triple is a scaling phrase e.g. "billion"
     */
    function is_scaling(user_message $msg): bool
    {
        return $this->obj()->is_scaling($msg);
    }

    /**
     * @return bool true if the wrapped word or triple has the type "time" e.g. "2022 (year)"
     */
    function is_time(user_message $msg): bool
    {
        return $this->obj()->is_time($msg);
    }

    function is_info(user_message $msg): bool
    {
        return $this->obj()->is_info($msg);
    }

    /**
     * @return float the system calculated impact of the wrapped word or triple;
     *               used to sort a phrase list so that the most relevant phrase is shown first
     */
    function impact(): float
    {
        return $this->obj()->impact();
    }


    /*
     * related
     */

    /**
     * get the parent phrases of the given phrase (foaf_direction::UP)
     * if a phrase list is given get only the parent phrases within the list (no api call)
     * if no phrase list is given get the phrases from the api
     * e.g. for Zurich the list is city and canton based on a phrase list with city, canton and country
     * but  for Zurich the list is city, canton and company based on a phrase list with company, city, canton and country
     * @param phrase_list|null $phr_lst optional pre-loaded list to filter against, avoiding an api call
     * @param int $levels the number of parent levels
     * @return phrase_list capped by the user-specific frontend config limit
     */
    function parents(user_message $msg, ?phrase_list $phr_lst = null, int $levels = 1): phrase_list
    {
        return $this->related($msg, $phr_lst, foaf_direction::UP);
    }

    /**
     * get all child phrases related to the given phrase (foaf_direction::DOWN)
     * behaves like parents() but in the opposite direction
     * e.g. for city at least Zurich, Bern and Geneva are returned
     *
     * @param phrase_list|null $phr_lst optional pre-loaded list to filter against, avoiding an api call
     * @param int $levels the number of child levels
     * @return phrase_list capped by the user-specific frontend config limit
     */
    function children(user_message $msg, ?phrase_list $phr_lst = null, int $levels = 1): phrase_list
    {
        return $this->related($msg, $phr_lst, foaf_direction::DOWN);
    }

    /**
     * get the similar objects of this phrase i.e. the other phrases that share a parent with this word
     * via the 'is a' verb e.g. for 'Swiss franc' (which is a 'currency') the similar phrases are the
     * other children of 'currency' such as 'Euro' and 'US Dollar' (this phrase itself is excluded)
     *
     * @param phrase_list|null $phr_lst optional pre-loaded list to filter against, avoiding an api call
     * @return phrase_list the sibling phrases without this phrase, capped by the user-specific frontend config limit
     */
    function similar(user_message $msg, ?phrase_list $phr_lst = null): phrase_list
    {
        if ($phr_lst === null) {
            $phr_lst = new phrase_list();
            $phr_lst->load_related($this, foaf_direction::UP);
        }
        $result = new phrase_list();
        // for each "this is a <parent>" relation collect the other phrases that are also "a <parent>"
        // e.g. for "Swiss franc is a currency" collect all currencies: Swiss franc, Euro and US Dollar
        foreach ($phr_lst->children($this, $msg)->lst() as $is_a_trp) {
            $vrb = $is_a_trp->get_verb();
            if ($vrb?->id() == verbs::IS_ID) {
                foreach ($phr_lst->parents($is_a_trp->get_to(), $msg, $vrb)->lst() as $sibling) {
                    $result->add_phrase($sibling);
                }
            }
        }
        // remove this phrase itself so that only the similar phrases remain
        $self = new phrase_list();
        $self->add_phrase($this);
        return $result->remove($self);
    }

    /**
     * get the related phrases of a phrase in the given direction (parents for UP, children for DOWN)
     * if a phrase list is given filter the related phrases within it (no api call)
     * otherwise load them from the api, and cap the result by the user-specific frontend config limit
     *
     * @param phrase_list|null $phr_lst optional pre-loaded list to filter against, avoiding an api call
     * @param foaf_direction $direction foaf_direction::UP for parents, foaf_direction::DOWN for children
     * @return phrase_list capped by the user-specific frontend config limit
     */
    private function related(user_message $msg, ?phrase_list $phr_lst, foaf_direction $direction): phrase_list
    {
        if ($phr_lst !== null) {
            if ($direction == foaf_direction::UP) {
                $lst = $phr_lst->parents($this, $msg);
            } else {
                $lst = $phr_lst->children($this, $msg);
            }
        } else {
            $lst = new phrase_list();
            $lst->load_related($this, $direction);
        }
        // limit the number of related phrases shown to keep the page-title category subtitle readable
        global $ui_sys;
        if ($ui_sys?->cfg !== null) {
            $limit = $ui_sys->cfg->get_by(
                [words::RELATED, words::LIMIT, words::LISTS, words::FRONTEND, words::USER],
                $msg, def::FALLBACK_PHRASES_RELATED
            );
        } else {
            $limit = def::FALLBACK_PHRASES_RELATED;
        }
        if ($lst->count() > $limit) {
            $lst->set_lst(array_slice($lst->lst(), 0, $limit));
        }
        return $lst;
    }


    /*
     * base
     */

    /**
     * @returns string the html code to display with mouse over that shows the description
     */
    function name_tip(): string
    {
        return $this->obj()->name_tip();
    }

    /**
     * @returns string the html code to display the phrase with reference links
     */
    function name_link(): string
    {
        return $this->obj()->name_link();
    }

    /**
     * @param string $lan the code of the user interface language e.g. "en"
     * @returns string the html code to display the plural of the phrase with reference links
     */
    function name_link_plural(string $lan = languages::DEFAULT): string
    {
        return $this->obj()->name_link_plural($lan);
    }

    /**
     * like name_link, but with the tooltip given by the caller, e.g. taken from the frontend
     * cache, so that a symbol like "mio" can show the description of "million"
     *
     * @param string $tip the tooltip text; if empty the description of the phrase is used
     * @returns string the html code of the phrase link with the given tooltip
     */
    function name_link_with_tip(string $tip): string
    {
        $result = $this->name_link();
        if ($tip != '' and ($this->get_description() ?? '') == '') {
            $html = new html_base();
            $url = $html->url_back($this->view_id(), $this->id());
            $result = $html->ref($url, $this->name(), $tip);
        }
        return $result;
    }

    /**
     * @return int the view id that name_link uses for this phrase class
     */
    private function view_id(): int
    {
        if ($this->is_triple()) {
            return views::TRIPLE_ID;
        } else {
            return views::WORD_ID;
        }
    }

    /**
     * simply to display a single word in a table cell
     */
    function dsp_tbl_cell(int $intent): string
    {
        $result = '';
        if ($this->is_word()) {
            $wrd = $this->obj();
            // the cell is rendered without a calling page, so its link carries no back part
            $result .= $wrd->td([], '', $intent);
        }
        return $result;
    }


    /*
     * select
     */

    /**
     * get the phrases that are related to this phrase be the verbs "is" of "can be"
     * if a phrase list id given only this cache is used for the selection
     * @param phrase_list|null $phr_lst_cac
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return phrase_list
     */
    function is_or_can_be(user_message $msg, ?phrase_list $phr_lst_cac = null, ?type_lists $typ_lst = null): phrase_list
    {
        global $ui_sys;
        // fall back to the frontend request cache if the caller has no type list
        if ($typ_lst == null) {
            log_err('type list cache missing, falling back to the request cache');
            $typ_lst = $ui_sys->typ_lst_cache;
        }
        $result = new phrase_list();
        if ($phr_lst_cac != null) {
            $result->merge($phr_lst_cac->parents($this, $msg, $typ_lst->vrb->get_by_code_id(verbs::IS)), $msg);
            $result->merge($phr_lst_cac->parents($this, $msg, $typ_lst->vrb->get_by_code_id(verbs::CAN_BE)), $msg);
        }
        return $result;
    }


    /*
     * to review
     */

    function dsp_graph(foaf_direction $direction, user_message $msg, ?verb_list $link_types = null, array $url_arr = []): string
    {
        $phr_lst = new phrase_list();
        if ($phr_lst->load_related($this, $direction, $link_types)) {
            return $phr_lst->dsp_graph($this, $msg, $url_arr);
        } else {
            return '';
        }
    }

    /**
     * @return word the most relevant
     */
    function main_word(user_message $msg): word
    {
        if ($this->is_word()) {
            return $this->obj()->word();
        } else {
            return $this->obj()->main_word($msg);
        }
    }

    /**
     * similar to dsp_link
     *
     * @param $style
     * @return string
     */
    function dsp_link_style($style): string
    {
        $html = new html_base();
        return $html->ref(api::MAIN_SCRIPT . '?' . url_var::MASK . '=' . views::PHRASE . '&'
            . url_var::ID . '=' . $this->id(), $html->esc($this->name()), $this->obj()->description, $style);
    }

    /**
     * return the html code to display a word
     */
    function display(): string
    {
        return (new html_base())->ref_view(views::PHRASE, $this->id(), $this->name());
    }

    /**
     * simply to display a single word or triple link
     */
    function display_linked(): string
    {
        $html = new html_base();
        return $html->ref(api::MAIN_SCRIPT . '?' . url_var::MASK . '=' . views::PHRASE . '&'
            . url_var::ID . '=' . $this->id(), $html->esc($this->name()), $this->obj()->description);
    }

    function name_linked(): string
    {
        $html = new html_base();
        return $html->ref(api::MAIN_SCRIPT . '?' . url_var::MASK . '=' . views::PHRASE . '&'
            . url_var::ID . '=' . $this->id(), $html->esc($this->name()), $this->obj()->description);
    }

    /**
     * html code for a button to add a new phrase similar to this phrase
     **/
    function btn_add(user_message $msg, array $url_arr = [], string $base_url = ''): string
    {
        $wrd = $this->main_word($msg);
        return $wrd->btn_add($url_arr, $base_url);
    }

    /**
     * add or select a word of triple and create an "is a" triple linked to this phrase
     * html code for a button to add a new phrase similar to this phrase
     * @return string the html to add the word or triple
     **/
    function button_add_triple(array $url_arr = [], string $base_url = ''): string
    {
        $wrd = new word();
        return $wrd->btn_add($url_arr, $base_url);
    }

    /**
     * to enable the recursive function in work_link
     * TODO add a list of triple already split to detect endless loops
     */
    function wrd_lst(user_message $msg): word_list
    {
        $wrd_lst = new word_list();
        if (!$this->is_word()) {
            $trp = $this->obj();
            $sub_wrd_lst = $trp->wrd_lst($msg);
            foreach ($sub_wrd_lst->lst() as $wrd) {
                $wrd_lst->add($wrd, $msg);
            }
        } else {
            $wrd = $this->obj();
            $wrd_lst->add($wrd, $msg);
        }
        return $wrd_lst;
    }

    function dsp_tbl(int $intent = 0): string
    {
        $result = '';
        if ($this != null) {
            if ($this->obj != null) {
                // the cell is rendered without a calling page, so its link carries no back part
                $dsp_obj = $this->obj();
                if ($this->is_word()) {
                    $result = $dsp_obj->td([], '', $intent);
                } else {
                    // a triple is shown as a complete table row, which uses no intent
                    $result = $dsp_obj->tr();
                }
            }
        }
        log_debug('for ' . $this->dsp_id());
        return $result;
    }
}
