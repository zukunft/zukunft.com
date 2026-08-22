<?php

/*

    web/value/value.php - create the html code to show a value to the user
    -------------------

    $val is the suggested var name

    to create the HTML code to show a value to the user
    and allow changing the value


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

namespace Zukunft\ZukunftCom\main\php\web\value;

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::DB . 'sql_db.php';
include_once html_paths::SANDBOX . 'sandbox_value.php';
include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::HTML . 'button.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::HTML . 'styles.php';
include_once html_paths::PHRASE . 'phrase.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::FIGURE . 'figure.php';
include_once html_paths::LOG . 'user_log_display.php';
include_once html_paths::GROUP . 'group.php';
include_once html_paths::PHRASE . 'phrase_list.php';
include_once html_paths::REF . 'ref_list.php';
include_once html_paths::REF . 'source.php';
include_once html_paths::REF . 'source_list.php';
include_once html_paths::SANDBOX . 'sandbox_value.php';
include_once html_paths::WORD . 'word.php';
include_once html_paths::SHARED_CONST . 'rest_ctrl.php';
include_once html_paths::SHARED_CONST . 'views.php';
include_once html_paths::SHARED_CONST_FIELDS . 'value_fields.php';
include_once html_paths::SHARED_ENUM . 'messages.php';
include_once html_paths::SHARED_HELPER . 'Message.php';
include_once html_paths::SHARED_TYPES . 'api_type_list.php';
include_once html_paths::SHARED . 'api.php';
include_once html_paths::SHARED . 'url_var.php';
include_once html_paths::SHARED . 'json_fields.php';
include_once html_paths::SHARED . 'library.php';

use Zukunft\ZukunftCom\main\php\web\figure\figure;
use Zukunft\ZukunftCom\main\php\web\group\group;
use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\log\user_log_display;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\web\ref\ref_list;
use Zukunft\ZukunftCom\main\php\web\ref\source;
use Zukunft\ZukunftCom\main\php\web\ref\source_list;
use Zukunft\ZukunftCom\main\php\web\sandbox\sandbox_value;
use Zukunft\ZukunftCom\main\php\web\html\styles;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\word\word;
use Zukunft\ZukunftCom\main\php\shared\const\rest_ctrl;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\helper\Message;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\api;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\shared\const\fields\value_fields;

class value extends sandbox_value
{

    /*
     * const
     */

    // curl views
    const string VIEW_ADD = views::VALUE_ADD;
    const string VIEW_EDIT = views::VALUE_EDIT;
    const string VIEW_DEL = views::VALUE_DEL;
    const int VIEW_ADD_ID = views::VALUE_ADD_ID;
    const int VIEW_EDIT_ID = views::VALUE_EDIT_ID;
    const int VIEW_DEL_ID = views::VALUE_DEL_ID;

    // curl message id
    const msg_id MSG_ADD = msg_id::VALUE_ADD;
    const msg_id MSG_EDIT = msg_id::VALUE_EDIT;
    const msg_id MSG_DEL = msg_id::VALUE_DEL;


    /*
     * object vars
     */

    public ?source $src = null;


    /*
     * construct and map
     */

    /**
     * set the vars of this value frontend object bases on the url array
     * TODO do the mapping always on normal, long and pod vars
     * @param array $url_array an array based on $_GET from a form submit
     * @param user_message $msg to enrich with warnings, problems and solutions
     * @param data_object|null $dto the cache as a parameter to be able to simulate test conditions
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function url_mapper(array $url_array, user_message $msg, data_object|null $dto = null): user_message
    {
        parent::url_mapper($url_array, $msg, $dto);
        if ($msg->is_ok()) {
            if (array_key_exists(url_var::SOURCE, $url_array)) {
                if ($url_array[url_var::SOURCE] != null) {
                    $this->set_source_id($url_array[url_var::SOURCE]);
                }
            }
        }
        return $msg;
    }

    /**
     * set the vars of this value object bases on the api json array
     * @param array $json_array an api json message
     * @param user_message $msg ok or a warning e.g. if the server version does not match
     */
    function api_mapper(array $json_array, user_message $msg): bool
    {
        parent::api_mapper($json_array, $msg);

        if (array_key_exists(json_fields::SOURCE_ID, $json_array)) {
            $this->set_source_id($json_array[json_fields::SOURCE_ID]);
        }

        return $msg->is_ok();
    }


    /*
     * set and get
     */

    function set_source_id(?int $id): void
    {
        if ($id == null) {
            $this->src = null;
        } else {
            if ($id > 0) {
                if ($this->src == null) {
                    $this->src = new source();
                }
                $this->src->id = $id;
            }
        }
    }

    function source_id(): ?int
    {
        return $this->src?->id;
    }

    /**
     * @param group $grp
     * @return float
     */
    function get(group $grp): float
    {
        /*
         * $result = null;
         * check if the value can be returned from frontend cache
         *  // if the group contains a table phrase of the prime pod for the frontend,
         *  if ($grp->has_table_pod_phrase()) {
         *      $tbl_phr = $grp->get_pod_table_phrase()
         *      if (!$tbl_phr->is_cached()) {
         *          $tbl_cache->check_size_and_remove_unneeded();
         *          $tbl_cache->get_from_backend($grp);
         *      }
         *      $result = $tbl_cache->get_number($grp);
         *  // if the group contains a table phrase which is cloned in the prime pod for the frontend,
         *  } elseif ($grp->has_table_clone_phrase()) {
         *      $tbl_phr = $grp->get_clone_table_phrase()
         *      if (!$tbl_phr->is_cached()) {
         *          $tbl_cache->check_size_and_remove_unneeded();
         *          $tbl_cache->get_from_backend($grp);
         *      }
         *      $result = $tbl_cache->get_number($grp);
         *  // if the group up to 4 phrases and all phrases are prime phrases
         *  } elseif ($grp->has_max_4_prime_phrases()) {
         *      if (!$grp->prime_is_cached()) {
         *          $grp_prime_cache->check_size_and_remove_unneeded();
         *          $grp_prime_cache->get_from_backend($grp);
         *      }
         *      $result = $grp_prime_cache->get_number($grp);
         *  // if the grp up to 16 phrases
         *  } elseif ($grp->has_max_16_phrases()) {
         *      if (!$grp->is_cached()) {
         *          $grp_cache->check_size_and_remove_unneeded();
         *          $grp_cache->get_from_backend($grp);
         *      }
         *      $result = $grp_cache->get_number($grp);
         *  // if the group contains more than 16 phrases
         *  } else {
         *      if (!$grp->is_cached()) {
         *          $grp_big_cache->check_size_and_remove_unneeded();
         *          $grp_big_cache->get_from_backend($grp);
         *      }
         *      $result = $grp_big_cache->get_number($grp);
         *  }
         * if the frontend cache is not yet fully used include
         *
         *
         */
        return 0;
    }


    /*
     * api
     */

    /**
     * @return array the json message array to send the updated data to the backend
     * an array is used (instead of a string) to enable combinations of api_array($msg) calls
     */
    function api_array(api_type_list|array $typ_lst, user_message $msg): array
    {
        $vars = parent::api_array($typ_lst, $msg);
        $vars[json_fields::PHRASES] = $this->grp->phr_lst()->api_array($typ_lst, $msg);
        $vars[json_fields::NUMBER] = $this->number();
        if ($this->src != null) {
            $vars[json_fields::SOURCE_ID] = $this->source_id();
        }
        return array_filter($vars, fn($value) => !is_null($value) && $value !== '');
    }


    /*
     * load
     */

    /**
     * load the value by id AND ask the backend to include the group phrases with their names
     * so the page-title renderer (value::name_link via the group phrase list) can show the
     * related phrases as links followed by the value, e.g. for "Pi (math) 3.14"
     *
     * Overrides the base load_by_id by attaching the WITH_PHRASES URL flag to the REST GET
     * call. The backend handler (api/value/index.php) translates that flag into
     * api_types::INCL_PHRASES, which makes sandbox_value::api_json_array() load the phrase
     * names (prime and main values carry only the phrase ids) and emit the phrases array,
     * which the frontend api_mapper picks up into $this->grp
     *
     * @param int|string $id the value id to load
     * @param user_message|Message $msg to collect the load errors
     * @param array $data additional data that should be included in the get request
     * @param int $usr_id the id of the session user to load the value for, 0 for the default
     * @return bool true on a successful load (mirrors load_by_id)
     */
    function load_by_id(int|string $id, user_message|Message $msg, array $data = [], int $usr_id = 0): bool
    {
        $data[url_var::WITH_PHRASES] = url_var::TRUE;
        return parent::load_by_id($id, $msg, $data, $usr_id);
    }

    /**
     * load the value by id AND ask the backend to include the views that can show this value,
     * the change log and the user overwrites, which the tabs of the value page show
     *
     * the api handler sets api_types::INCL_RELATED and value::api_json_array() emits the
     * views, changes and overwrites that the frontend api_mapper picks up into view_lst,
     * chg_log, user_overwrites and other_overwrites
     *
     * @param int|string $id the value id to load
     * @param int $usr_id the id of the session user to load the value for, 0 for the default
     * @return bool true on a successful load (mirrors load_by_id)
     */
    function load_by_id_with_related(int|string $id, user_message $msg, int $usr_id = 0): bool
    {
        return $this->load_by_id($id, $msg, [url_var::INCL_RELATED => url_var::TRUE], $usr_id);
    }


    /*
     * select
     */

    /**
     * to select the value if it matches all given phrase names
     * @param array $names the phrase names for the selection
     * @return bool true if this values is related to all phrase names
     */
    function match_all(array $names): bool
    {
        $result = true;
        $phr_names = $this->phr_lst()->names();
        foreach ($names as $name) {
            if ($result) {
                if (!in_array($name, $phr_names)) {
                    $result = false;
                }
            }
        }
        return $result;
    }


    /*
     * cast
     */

    /**
     * @returns figure the figure display object base on this value object
     */
    function figure(): figure
    {
        $fig = new figure();
        $fig->set_obj($this);
        return $fig;
    }


    /*
     * base
     */

    /**
     * create the html code to show only the value in default format to the user
     * this is the opposite of the convert function
     * @return string the html code to show only the value
     */
    function value(user_message $msg): string
    {
        $html = new html_base();
        if ($this->number() != null) {
            $result = $this->val_formatted($msg);
            if (!$this->is_std()) {
                $result = $html->span($result, styles::STYLE_USER);
            }
        } elseif ($this->text_value() != null) {
            // escape the user text value: value() returns display-ready html (the number branch
            // already returns a span) and the span/name callers emit it without escaping, so an
            // un-escaped text value would be stored xss
            $result = $html->esc($this->text_value());
        } elseif ($this->time_value() != null) {
            $result = $html->esc($this->time_value());
        } else {
            $result = '';
        }
        return $result;
    }

    /**
     * create the html code to show only the value formatted based on the user settings
     * with a link to see more related information of the value
     * @return string the formatted value with a link for more details
     */
    function value_link(user_message $msg, string $back = ''): string
    {
        $html = new html_base();
        $lib = new library();
        $url = $html->url_back($lib->class_to_name($this::class), $this->id(), '', $back);
        $txt = $this->value($msg);
        // value() already returns escaped/safe html, so ref() must not escape it again
        return $html->ref($url, $txt, '', '', true);
    }

    /**
     * create the html code to show only the value formatted based on the user settings
     * with a link to change the value itself or the value parameters
     * @param user_message $msg to collect the error messages
     * @param string $back the id of the original phrase (TODO Prio 0: to be replace with $url_arr that contains the previuos pages)
     * @return string the formatted value with a link to change this value
     */
    function value_edit(user_message $msg, string $back = ''): string
    {
        $html = new html_base();
        $url = $html->url_back(views::VALUE_DEFAULT_ID, $this->id(), '', $back);
        $txt = $this->value($msg);
        // value() already returns escaped/safe html, so ref() must not escape it again
        return $html->ref($url, $txt, '', '', true);
    }

    /**
     * create the html code to show the phrase names related to a value in declining order of impact
     * and the measure types behind the value as a symbol
     * but with the tooltip of the main measure type
     * e.g. for cost, global problem, populism, a billion, EUR 23.4 and global problem and cost in the $ex_phr_lst
     *      the result should be populism 23.4 b EUR
     *      where b has the tooltip and the link to phrase linked with 'is symbol for'
     *      and EUR has the tooltip and the link to Euro linked with '?? tbd.'
     *
     * @param user_message $msg to collect the error messages
     * @param array $url_arr with the url vars including the 9 prefixed vars of the previous url as a return path
     * @param phrase_list|null $ex_phr_lst list of phrases that should be exclude from the name
     *                                     e.g. because they have be a column header
     * @param data_object|null $dto the frontend cache with all data needed to create the html,
     *                              i.e. the phrases with their description for the tooltips and
     *                              the related phrases, so that e.g. the symbol "mio" can show
     *                              the description of the related word "million" as its tooltip
     * @return string one row with the linked phrase names, the value with the edit link
     *                and the measure links
     */
    function links_and_measure(
        user_message     $msg,
        array            $url_arr,
        phrase_list|null $ex_phr_lst = null,
        data_object|null $dto = null,
        string           $base_url = ''
    ): string
    {
        $html = new html_base();

        // start with the phrases of the value group and drop the given context phrases,
        // e.g. the page phrase and the column header of a table cell
        $phr_lst = clone $this->grp->phr_lst();
        if ($ex_phr_lst != null) {
            $phr_lst = $phr_lst->remove($ex_phr_lst);
        }

        // split off the measure (e.g. EUR), the scaling (e.g. billion) and the information
        // only phrases (e.g. 1983 (year of definition)), because they are shown behind the
        // number resp. as the tooltip of the number, not as part of the name
        $measure_lst = $phr_lst->measure_list($msg);
        $scale_lst = $phr_lst->scaling_list($msg);
        $info_lst = $phr_lst->info_list($msg);
        $phr_lst = $phr_lst->ex_measure_list($msg);
        $phr_lst = $phr_lst->ex_scaling_list($msg);
        $phr_lst = $phr_lst->ex_info_list($msg);
        if ($measure_lst->count() > 1) {
            log_warning($this->dsp_id() . ' is not expected to have more than one measure');
        }

        // the remaining phrases name the value with the most relevant phrase first
        $phr_lst->sort_by_impact();
        $name_txt = $this->phrase_links($phr_lst, $dto);

        // the number links to the value edit page with the calling page as the return path;
        // the information only phrases explain the number as its tooltip
        $url = $html->url_with_back(
            $html->url_back(views::VALUE_DEFAULT_ID, $this->id()),
            html_base::page_url_array($url_arr));
        // value() already returns escaped/safe html, so ref() must not escape it again
        $val_txt = $html->ref($url, $this->value($msg), $info_lst->name_pur(), '', true);

        // the scaling and the measure phrases follow the number like on a price tag;
        // a symbol like "mio" has no description of its own, so the cache supplies the
        // description of the word it is a symbol for (e.g. "million") as the tooltip
        $unit_txt = trim($this->phrase_links($scale_lst, $dto)
            . ' ' . $this->phrase_links($measure_lst, $dto));

        // each part is a span of its own, so that the css keeps the name, the number and the
        // measure side by side on one row and wraps the name first if the space gets tight;
        // text-nowrap also keeps each part on a single line in the html snapshot, so that the
        // separator of the phrase links stays directly behind the link (see library::format_html)
        $row_txt = '';
        if ($name_txt != '') {
            $row_txt .= $html->span($name_txt, styles::VALUE_NAME . ' ' . styles::TEXT_NOWRAP);
        }
        $row_txt .= $html->span($val_txt, styles::VALUE_NUM . ' ' . styles::TEXT_NOWRAP);
        if ($unit_txt != '') {
            $row_txt .= $html->span($unit_txt, styles::VALUE_UNIT . ' ' . styles::TEXT_NOWRAP);
        }
        return $html->div($row_txt, styles::VALUE_ROW);
    }

    /**
     * the links of the given phrases, each with the best tooltip the frontend cache knows:
     * the description of the phrase itself or of the phrase it is a symbol for
     *
     * @param phrase_list $phr_lst the phrases that should be linked e.g. the measure phrases
     * @param data_object|null $dto the frontend cache with the described and related phrases
     * @return string the comma separated html links of the given phrases
     */
    private function phrase_links(phrase_list $phr_lst, data_object|null $dto): string
    {
        if ($dto?->phr_lst == null) {
            return $phr_lst->name_link_list();
        }
        $result = '';
        $msg = new user_message(); // a local buffer, the tooltip lookup has no user relevant message
        foreach ($phr_lst->lst() as $phr) {
            if ($result <> '') {
                $result .= ', ';
            }
            $result .= $phr->name_link_with_tip($dto->phr_lst->tooltip($phr, $msg));
        }
        return $result;
    }

    /**
     * create the html code to show the value formatted based on the user settings
     * and the unit after the value
     * and with the information only phrases move the tooltip of the group name
     * and with the group name as a link to see the details of the value
     * @param user_message $msg to collect the error messages
     * @param string $back the id of the original phrase (TODO Prio 0: to be replace with $url_arr that contains the previuos pages)
     * @return string the description with links and the formatted value
     */
    function with_unit_and_info(user_message $msg, string $back = ''): string
    {
        $html = new html_base();
        $lib = new library();
        $phr_lst = $this->grp->phr_lst();
        $unit_phr_lst = $phr_lst->measure_list($msg);
        $info_phr_lst = $phr_lst->info_list($msg);
        $phr_lst = $phr_lst->ex_measure_list($msg);
        $phr_lst = $phr_lst->ex_info_list($msg);
        if ($unit_phr_lst->count() > 1) {
            log_err($this->dsp_id() . ' is not expected to have more than one unit');
        }
        $url = $html->url_back($lib->class_to_name($this::class), $this->id(), '', $back);
        $name_txt = $phr_lst->name_link_list();
        $val_txt = $this->value($msg);
        if (!$info_phr_lst->is_empty()) {
            // value() already returns escaped/safe html, so ref() must not escape it again
            $val_txt = $html->ref($url, $val_txt, $info_phr_lst->name_pur(), '', true);
        } else {
            $val_txt = $html->span($val_txt, '', $info_phr_lst->name_pur());
        }
        $unit_txt = $unit_phr_lst->name_link_list();
        return $name_txt . ' ' . $val_txt . ' ' . $unit_txt;
    }

    /**
     * perform the fixed validation tests that are never expected to be changed
     * e.g. a value has only one unit, so a value with e.g. "m/s" and "Hz" is reported
     * @param user_message $msg to collect the error messages of the phrase type checks
     * @return string the warning text translated to the frontend language as defined by the user
     */
    function warning_text(user_message $msg): string
    {
        $result = '';
        $unit_lst = $this->grp->phr_lst()->measure_list($msg);
        if ($unit_lst->count() > 1) {
            // a local buffer, because the warning text is returned for the page
            // and must not flag the callers shared message as failed
            $warning = new user_message();
            $warning->add(msg_id::VALUE_UNIT_NOT_UNIQUE, [
                msg_id::VAR_NAME => $unit_lst->name_pur()
            ]);
            $result = $warning->get_last_message_translated();
        }
        return $result;
    }

    /**
     * @return string interface function to align the value with the other sandbox objects
     */
    function name(): string|null
    {
        return $this->grp->name();
    }

    /**
     * the impact of a value is the highest impact of the phrases it is assigned to
     * so that the most relevant values (e.g. of the highest ranked phrase) are shown first
     * @return float the system calculated impact used to sort the values
     */
    function impact(): float
    {
        return $this->grp->phr_lst()->max_impact();
    }

    /**
     * the time phrase (e.g. "2022 (year)") of this value used by the "most relevant" value list to
     * group and to sort the values with a time newest first; a value has at most one time phrase
     * @return phrase|null the first time phrase of the value's group or null if the value has no time
     */
    function time_phrase(user_message $msg): ?phrase
    {
        $result = null;
        foreach ($this->grp->phr_lst()->lst() as $phr) {
            if ($result == null and $phr->is_time($msg)) {
                $result = $phr;
            }
        }
        return $result;
    }

    /**
     * @return string interface function to align the value with the other sandbox objects
     */
    function get_description(): string
    {
        return $this->grp->get_description();
    }

    /**
     * create the HTML code to show to the user
     * the value with the name and the formatted value
     * with a tooltip
     *
     * @param phrase_list|null $phr_lst_exclude usually the context phrases that does not need to be repeated
     * @param string $sep the separator string between the name and the value
     * @return string the HTML code of all phrases linked to the value, but not including the phrase from the $phr_lst_exclude
     */
    function name_tip(user_message $msg, phrase_list|null $phr_lst_exclude = null, string $sep = ' '): string
    {
        return $this->grp->name_tip($phr_lst_exclude) . $sep . $this->value($msg);
    }

    /**
     * create the HTML code to show the related phrases as links followed by the
     * numeric value itself in grey without a link, used as the value page title
     *
     * @param phrase_list|null $phr_lst_exclude usually the context phrases that does not need to be repeated
     * @param string $sep the separator string between the phrases and the value
     * @return string the HTML code of all phrases linked to the value, but not including the phrase from the $phr_lst_exclude
     */
    function name_link(user_message $msg, phrase_list|null $phr_lst_exclude = null, string $sep = ' '): string
    {
        $html = new html_base();
        $phr_links = $this->grp->name_link_list($phr_lst_exclude);
        $val_grey = $html->span($this->value($msg), styles::STYLE_GREY);
        return $phr_links . $sep . $val_grey;
    }

    /**
     * depending on the word list format the numeric value
     * format the value for on screen display
     * similar to the corresponding function in the "result" class
     * @returns string the HTML code to display this value
     */
    function val_formatted(user_message $msg): string
    {
        global $ui_sys;
        $cfg = $ui_sys->cfg;
        $result = '';

        if (!is_null($this->number())) {
            // load the list of phrases if needed
            if (!$this->grp->phr_lst()->is_empty()) {
                if ($this->grp->phr_lst()->has_percent($msg)) {
                    $result = round($this->number() * 100, $cfg->percent_decimals()) . "%";
                } else {
                    if ($this->number() >= 1000 or $this->number() <= -1000) {
                        $result .= number_format($this->number(), 0, $cfg->dec_point(), $cfg->thousand_sep());
                    } else {
                        $result = round($this->number(), 2);
                    }
                }
            } else {
                // use default settings
                $result = round($this->number(), 2);
            }
        }
        return $result;
    }

    /**
     * @param string $form
     * @param string $pattern
     * @param source_list|null $src_lst the frontend cache with the configuration, the preloaded source and the cached objects
     * @return string
     */
    function source_selector(string $form, string $pattern, ?source_list $src_lst): string
    {
        // TODO review and maybe use test_mode parameter
        if ($pattern != '') {
            $src_lst->load_like($pattern);
        }
        return $src_lst->selector($form, $this->id(), url_var::SOURCE, msg_id::FORM_SELECT_SOURCE);
    }

    /**
     * @param string $form
     * @param string $pattern
     * @return string
     */
    function ref_selector(string $form, string $pattern): string
    {
        $ref_lst = new ref_list();
        // TODO review and maybe use test_mode parameter
        if ($pattern != '') {
            $ref_lst->load_like($pattern);
        }
        return $ref_lst->selector($form, $this->id(), url_var::REF, msg_id::FORM_SELECT_VIEW_STYLE);
    }

    /*
     * info
     */

    /**
     * to select a value by a phrase
     * @param phrase $phr the phrase to select the value
     * @return bool true if the value contains the given phrase
     */
    function has_phrase(phrase $phr, user_message $msg): bool
    {
        $result = false;
        $phr_lst = $this->grp->phr_lst();
        foreach ($phr_lst->lst() as $val_phr) {
            if ($val_phr->is_same($phr)) {
                $result = true;
            } elseif ($val_phr->is_type_phrase($phr, $msg)) {
                $result = true;
            }
        }
        return $result;
    }

    /*
     * buttons
     */

    /**
     * offer the user to add a new value similar to this value
     *
     * possible future parameters:
     * $fixed_words - words that the user is not suggested to change this time
     * $select_word - suggested words which the user can change
     * $type_word   - word to preselect the suggested words e.g. "country" to list all their countries first for the suggested word
     *
     * @param array $url_arr the previous url with the back part
     * @param string $base_url to set an absolut html path for urls
     * @returns string the HTML code for a button to add a value related to this value
     */
    function btn_add(array $url_arr = [], string $base_url = ''): string
    {
        $msg_code_id = msg_id::VALUE_ADD;
        $explain = '';

        if ($this->grp->phr_lst()->is_empty()) {
            if (!empty($this->grp->phr_lst()->lst())) {
                $explain = htmlentities($this->grp->phr_lst()->dsp_name());
                $msg_code_id = msg_id::VALUE_ADD_SIMILAR;
            }
        }

        return parent::btn_add_sbx(
            views::VALUE_ADD_ID,
            $msg_code_id,
            $url_arr, $explain, $base_url);
    }

    /**
     * the html code to change the value
     *
     * @param array $url_arr the previous url with the back part
     * @param string $base_url to set an absolut html path for urls
     * @returns string the HTML code for a button to add a value related to this value
     */
    function btn_edit(array $url_arr = [], string $base_url = ''): string
    {
        $msg_code_id = msg_id::VALUE_EDIT;
        $explain = '';

        return parent::btn_edit_sbx(
            views::VALUE_EDIT_ID,
            $msg_code_id,
            $url_arr, $explain, $base_url);
    }

    /**
     * the html code to exclude or delete the value
     *
     * @param array $url_arr the previous url with the back part
     * @param string $base_url to set an absolut html path for urls
     * @returns string the HTML code for a button to add a value related to this value
     */
    function btn_del(array $url_arr = [], string $base_url = ''): string
    {
        $msg_code_id = msg_id::VALUE_DEL;
        $explain = '';

        return parent::btn_del_sbx(
            views::VALUE_DEL_ID,
            $msg_code_id,
            $url_arr, $explain, $base_url);
    }


    /*
     * backend requests
     */

    /**
     * @return user_message
     */
    private function reload(): user_message
    {
        $msg = new user_message(); // the message IS the return value, so the caller merges it
        if ($this->is_id_set()) {
            $this->load_by_id($this->id(), $msg);
        }
        return $msg;
    }

    /**
     * reload the value object from the database, but only if some related objects
     * e,g, the phrase list is probably missing
     * @return user_message
     */
    private function reload_if_needed(): user_message
    {
        $msg = new user_message(); // the ok default of this function, replaced by the reload result
        if (!$this->is_loaded()) {
            $msg = $this->reload();
        }
        return $msg;
    }

    /**
     * @return bool true if all related objects are loaded
     */
    private function is_loaded(): bool
    {
        $result = true;
        if ($this->grp->phr_lst()->is_empty()) {
            $result = false;
        }
        return $result;
    }

    function is_id_set(): bool
    {
        return $this->grp->is_id_set();
    }

    /*
     * to review
     */

    // the same as \html\btn_del_value, but with another icon
    function btn_undo_add_value($back): string
    {
        return \Zukunft\ZukunftCom\main\php\web\html\btn_undo('delete this value',
            new html_base()->url_back(views::VALUE_DEL_ID, $this->id(), '', $back));
    }

    // display a value, means create the HTML code that allows to edit the value
    function dsp_tbl_std(user_message $msg, $back): string
    {
        log_debug('value->dsp_tbl_std ');
        $html = new html_base();
        $result = '    <td>' . "\n";
        $result .= '      <div class="' . styles::STYLE_RIGHT . '">' . $html->ref($html->url_back(views::VALUE_EDIT_ID, $this->id(), '', $back), $this->val_formatted($msg)) . '</div>' . "\n";
        $result .= '    </td>' . "\n";
        return $result;
    }

    // same as dsp_tbl_std, but in the user-specific color
    function dsp_tbl_usr(user_message $msg, $back): string
    {
        log_debug('value->dsp_tbl_usr');
        $html = new html_base();
        $result = '';
        $result .= '    <td>' . "\n";
        $result .= '      <div class="' . styles::STYLE_RIGHT . '">' . $html->ref($html->url_back(views::VALUE_EDIT_ID, $this->id(), '', $back), $this->val_formatted($msg), '', styles::STYLE_USER) . '</div>' . "\n";
        $result .= '    </td>' . "\n";
        return $result;
    }

    function dsp_tbl(user_message $msg, $back): string
    {
        log_debug('value->dsp_tbl_std ');
        $result = '';

        if ($this->is_std()) {
            $result .= $this->dsp_tbl_std($msg, $back);
        } else {
            $result .= $this->dsp_tbl_usr($msg, $back);
        }
        return $result;
    }

    // display the history of a value
    function dsp_hist($page, $size, $call, $back): string
    {
        log_debug("value->dsp_hist for id " . $this->id() . " page " . $size . ", size " . $size . ", call " . $call . ", back " . $back . ".");
        $result = ''; // reset the html code var

        $log_ui = new user_log_display();
        $log_ui->id = $this->id();
        $log_ui->obj = $this;
        $log_ui->type = \Zukunft\ZukunftCom\main\php\cfg\value\value::class;
        $log_ui->page = $page;
        $log_ui->size = $size;
        $log_ui->call = $call;
        $log_ui->back = $back;
        //$result .= $log_ui->dsp_hist_old();

        log_debug("done");
        return $result;
    }

    // display the history of a value
    function dsp_hist_links($page, $size, $call, $back): string
    {
        log_debug($this->id() . ",size" . $size . ",b" . $size);
        $result = ''; // reset the html code var

        $log_ui = new user_log_display();
        $log_ui->id = $this->id();
        $log_ui->type = value::class;
        $log_ui->page = $page;
        $log_ui->size = $size;
        $log_ui->call = $call;
        $log_ui->back = $back;
        //$result .= $log_ui->dsp_hist_links();

        log_debug("done");
        return $result;
    }

    // lists all words related to a given value except the given word
    // and offer to add a formula to the value as an alternative
    // $wrd_add is only optional to display the last added word at the end
    // TODO: take user unlink of words into account
    // save data to the database only if "save" is pressed add and remove the word links "on the fly", which means that after the first call the edit view is more or less the same as the add view

    // display some value samples related to the wrd_id
    // with a preference of the start_word_ids
    /*
     * TODO recreate based on the group
    function dsp_samples($wrd_id, $start_wrd_ids, $size, $back): string
    {
        log_debug("value->dsp_samples (" . $wrd_id . ",rt" . implode(",", $start_wrd_ids) . ",size" . $size . ")");

        // TODO Prio 0 split and move the database part to the backend
        $db_con = new sql_db();
        $result = ''; // reset the html code var

        $html = new html_base();

        // get value changes by the user that are not standard
        $sql = "SELECT v.group_id,
                    " . $db_con->get_usr_field(value_fields::FLD_VALUE, 'v', 'u', sql_db::FLD_FORMAT_VAL) . ",
                   t.word_id,
                   t.word_name
              FROM groups g,
                   groups gt,
                   words t,
                   " . $db_con->get_table_name_esc(value::class) . " v
         LEFT JOIN user_values u ON v.group_id = u.group_id AND u.user_id = " . $this->user()->id() . "
             WHERE l.phrase_id = " . $wrd_id . "
               AND l.group_id = v.group_id
               AND v.group_id = lt.group_id
               AND lt.phrase_id <> " . $wrd_id . "
               AND lt.phrase_id = t.word_id
               AND (u.excluded IS NULL OR u.excluded = 0)
             LIMIT " . $size . ";";
        //$db_con = New mysql;
        $db_con->usr_id = $this->user()->id();
        $db_lst = $db_con->get_old($sql);

        // prepare to show where the user uses different value than a normal viewer
        $row_nbr = 0;
        $group_id = 0;
        $word_names = "";
        $result .= $html->dsp_tbl_start_hist();
        foreach ($db_lst as $db_row) {
            // display the headline first if there is at least on entry
            if ($row_nbr == 0) {
                $result .= '<tr>';
                $result .= '<th>samples</th>';
                $result .= '<th>for</th>';
                $result .= '</tr>';
                $row_nbr++;
            }

            $new_group_id = $db_row["group_id"];
            $wrd = new word_dsp();
            $wrd->set_id($db_row["word_id"]);
            $wrd->set_name($db_row["word_name"]);
            if ($group_id <> $new_group_id) {
                if ($word_names <> "") {
                    // display a row if the value has changed and
                    $result .= '<tr>';
                    $result .= '<td>' . $html->ref($html->url_new(views::VALUE_EDIT_ID, $group_id, '', $back), $row_value, '', 'grey') . '</td>';
                    $result .= '<td>' . $word_names . '</td>';
                    $result .= '</tr>';
                    $row_nbr++;
                }
                // prepare a new value display
                $row_value = $db_row["numeric_value"];
                $word_names = $wrd->name_linked(styles::STYLE_GREY);
                $group_id = $new_group_id;
            } else {
                $word_names .= ", " . $wrd->name_linked(styles::STYLE_GREY);
            }
        }
        // display the last row if there has been at least one word
        if ($word_names <> "") {
            $result .= '<tr>';
            $result .= '<td>' . $html->ref($html->url_new(views::VALUE_EDIT_ID, $group_id, '', $back), $row_value, '', 'grey') . '</td>';
            $result .= '<td>' . $word_names . '</td>';
            $result .= '</tr>';
        }
        $result .= $html->dsp_tbl_end();

        log_debug("done.");
        return $result;
    }
    */

    // simple modal box to add a value
    function dsp_add_fast($back): string
    {
        $html = new html_base();
        $result = '';

        $result .= '  ' . $html->h2('Modal Example');
        $result .= '  <!-- Button to Open the Modal -->';
        //$result .= '  <a href="/http/value_add.php?back=2" title="add"><img src="'.$icon.'" alt="'.$this->title.'"></a>';
        $result .= '';

        return $result;
    }

    // lists all phrases related to a given value except the given phrase
    // and offer to add a formula to the value as an alternative
    // $wrd_add is only optional to display the last added phrase at the end
    // TODO: take user unlink of phrases into account
    // save data to the database only if "save" is pressed add and remove the phrase links "on the fly", which means that after the first call the edit view is more or less the same as the add view
    function dsp_edit($type_ids, user_message $msg, string $back): string
    {
        $result = ''; // reset the html code var
        $lib = new library();
        $html = new html_base();

        // set main display parameters for the add or edit view
        if ($this->id() <= 0) {
            $script = "value_add";
            $result .= $html->dsp_form_start($script);
            $result .= $html->dsp_text_h3("Add value for");
            log_debug("value->dsp_edit new for " . $this->dsp_id());
        } else {
            $script = "value_edit";
            $result .= $html->dsp_form_start($script);
            $result .= $html->dsp_text_h3("Change value for");
            if (count($this->ids()) <= 0) {
                $this->load_phrases();
                log_debug('value->dsp_edit ' . $this->dsp_id());
            }
        }
        $this_url = rest_ctrl::PATH_FIXED . $script . '.php?id=' . $this->id() . '&back=' . $back; // url to call this display again to display the user changes

        // display the words and triples
        $result .= $html->dsp_tbl_start_select();
        if (count($this->ids()) > 0) {
            $url_pos = 1; // the phrase position (combined number for fixed, type and free phrases)
            // if the form is confirmed, save the value or the other way round: if with the plus sign only a new phrase is added, do not yet save the value
            $result .= $html->input(url_var::ID, msg_id::FORM_FIELD_ID, $this->id(), html_base::INPUT_HIDDEN);
            $result .= $html->input(url_var::STEP, msg_id::FORM_FIELD_CONFIRM, '1', html_base::INPUT_HIDDEN);

            // reset the phrase sample settings
            $main_wrd = null;
            log_debug("value->dsp_edit main wrd");

            /*
      // load the phrase list
      $phr_lst = New phrase_list;
      $phr_lst->ids = $this->ids;
      $phr_lst->usr = $this->user();
      $phr_lst->load();

      // separate the time if needed
      if ($this->time_id <= 0) {
        $this->time_phr = $phr_lst->time_useful();
        $phr_lst->del($this->time_phr);
        $this->time_id = $this->time_phr->id(); // not really needed ...
      }
      */

            // assign the type to the phrases
            $phr_lst = clone $this->grp->phrase_list();
            foreach ($phr_lst->lst() as $phr) {
                $phr->set_user($this->user());
                foreach (array_keys($this->ids()) as $pos) {
                    if ($phr->id == $this->ids()[$pos]) {
                        $phr->is_wrd_id = $type_ids[$pos];
                        $is_wrd = new word();
                        $is_wrd->set_id($phr->is_wrd_id);
                        $phr->is_wrd = $is_wrd;
                        $phr->dsp_pos = $pos;
                    }
                }
                // guess the missing phrase types
                if ($phr->is_wrd_id == 0) {
                    log_debug('guess type for "' . $phr->name() . '"');
                    $phr->is_wrd = $phr->is_mainly();
                    if ($phr->is_wrd->id() > 0) {
                        $phr->is_wrd_id = $phr->is_wrd->id();
                        log_debug('guessed type for ' . $phr->name() . ': ' . $phr->is_wrd->name);
                    }
                }
            }

            // show first the phrases, that are not supposed to be changed
            //foreach (array_keys($this->ids) AS $pos) {
            log_debug('show fixed phrases');
            foreach ($phr_lst->lst() as $phr) {
                //if ($type_ids[$pos] < 0) {
                if ($phr->is_wrd_id < 0) {
                    log_debug('show fixed phrase "' . $phr->name() . '"');
                    // allow the user to change also the fixed phrases
                    $type_ids_adj = $type_ids;
                    $type_ids_adj[$phr->dsp_pos] = 0;
                    $used_url = $this_url . $lib->ids_to_url($this->ids(), "phrase") .
                        $lib->ids_to_url($type_ids_adj, "type");
                    $result .= $phr->dsp_name_del($used_url);
                    $result .= '  <input type="' . html_base::INPUT_HIDDEN . '" name="phrase' . $url_pos . '" value="' . $phr->id . '">';
                    $url_pos++;
                }
            }

            // show the phrases that the user can change:
            // first the non-specific ones, that the phrases of a selective type
            // and new phrases at the end
            log_debug('show phrases');
            for ($dsp_type = 0; $dsp_type <= 1; $dsp_type++) {
                foreach ($phr_lst->lst() as $phr) {
                    /*
          // build a list of suggested phrases
          $phr_lst_sel_old = array();
          if ($phr->is_wrd_id > 0) {
            // prepare the selector for the type phrase
            $phr->is_wrd->usr = $this->user();
            $phr_lst_sel = $phr->is_wrd->children($msg);
            zu_debug("value->dsp_edit -> suggested phrases for ".$phr->name().": ".$phr_lst_sel->name().".");
          } else {
            // if no phrase group is found, use the phrase type time if the phrase is a time phrase
            if ($phr->is_time()) {
              $phr_lst_sel = New phrase_list;
              $phr_lst_sel->usr = $this->user();
              $phr_lst_sel->phrase_type_id = cl(SQL_WORD_TYPE_TIME);
              $phr_lst_sel->load();
            }
          } */

                    // build the url for the case that this phrase should be removed
                    log_debug('build url');
                    $phr_ids_adj = $this->ids();
                    $type_ids_adj = $type_ids;
                    array_splice($phr_ids_adj, $phr->dsp_pos, 1);
                    array_splice($type_ids_adj, $phr->dsp_pos, 1);
                    $used_url = $this_url . $lib->ids_to_url($phr_ids_adj, "phrase") .
                        $lib->ids_to_url($type_ids_adj, "type") .
                        '&confirm=1';
                    // url for the case that this phrase should be renamed
                    if ($phr->id() > 0) {
                        $phrase_url = api::MAIN_SCRIPT . '?' . url_var::MASK . '=' . views::WORD_EDIT . '&id=' . $phr->id . '&back=' . $back;
                    } else {
                        $lnk_id = $phr->id * -1;
                        $phrase_url = api::MAIN_SCRIPT . '?' . url_var::MASK . '=' . views::TRIPLE_EDIT . '&id=' . $lnk_id . '&back=' . $back;
                    }

                    // show the phrase selector
                    $result .= '  <tr>';

                    // show the phrases that have a type
                    if ($dsp_type == 0) {
                        if ($phr->is_wrd->id() > 0) {
                            log_debug('id ' . $phr->id . ' has a type');
                            $result .= '    <td>';
                            $result .= $phr->is_wrd->name . ':';
                            $result .= '    </td>';
                            //$result .= '    <input type="' . html_base::INPUT_HIDDEN . '" name="db'.$url_pos.'" value="'.$phr->dsp_lnk_id.'">';
                            $result .= '    <td>';
                            /*if (!empty($phr_lst_sel->lst)) {
                $result .= '      '.$phr_lst_sel->dsp_selector("phrase".$url_pos, $script, $phr->id);
              } else {  */
                            $result .= '      ' . $phr->dsp_selector($phr->is_wrd, $script, $url_pos, '', $back);
                            //}
                            $url_pos++;

                            $result .= '    </td>';
                            $result .= '    <td>' . \Zukunft\ZukunftCom\main\php\web\html\btn_del("Remove " . $phr->name(), $used_url) . '</td>';
                            $result .= '    <td>' . \Zukunft\ZukunftCom\main\php\web\html\btn_edit("Rename " . $phr->name(), $phrase_url) . '</td>';
                        }
                    }

                    // show the phrases that don't have a type
                    if ($dsp_type == 1) {
                        if ($phr->is_wrd->id == 0 and $phr->id() > 0) {
                            log_debug('id ' . $phr->id . ' has no type');
                            if (!isset($main_wrd)) {
                                $main_wrd = $phr;
                            }
                            //$result .= '    <input type="' . html_base::INPUT_HIDDEN . '" name="db'.$url_pos.'" value="'.$phr->dsp_lnk_id.'">';
                            $result .= '    <td colspan="2">';
                            $result .= '      ' . $phr->dsp_selector(0, $script, $url_pos, '', $back);
                            $url_pos++;

                            $result .= '    </td>';
                            $result .= '    <td>' . \Zukunft\ZukunftCom\main\php\web\html\btn_del("Remove " . $phr->name(), $used_url) . '</td>';
                            $result .= '    <td>' . \Zukunft\ZukunftCom\main\php\web\html\btn_edit("Rename " . $phr->name(), $phrase_url) . '</td>';
                        }
                    }


                    $result .= '  </tr>';
                }
            }

            // show the time phrase
            log_debug('show time');
            $time_lst = $this->grp->phr_lst()->time_word_list();
            $has_time = false;
            foreach ($time_lst->lst() as $time_phr) {
                $result .= '  <tr>';
                if ($time_phr->id() <> 0) {
                    $has_time = true;
                    $result .= '    <td colspan="2">';

                    log_debug('show time selector');
                    $result .= $time_phr->dsp_time_selector(0, $script, $url_pos, $back);
                    $url_pos++;

                    $result .= '    </td>';
                    $result .= '    <td>' . \Zukunft\ZukunftCom\main\php\web\html\btn_del("Remove " . $time_phr->name(), $used_url) . '</td>';
                }
                $result .= '  </tr>';
            }
            // show an empty time selector
            if (!$has_time) {
                $time_phr = new phrase($this->user());
                $result .= '  <tr>';
                $result .= '    <td colspan="2">';

                log_debug('show time selector');
                $result .= $time_phr->dsp_time_selector(0, $script, $url_pos, $back);
                $url_pos++;

                $result .= '    </td>';
                $result .= '    <td>' . \Zukunft\ZukunftCom\main\php\web\html\btn_del("Remove " . $time_phr->name(), $used_url) . '</td>';
                $result .= '  </tr>';
            }

            // show the new phrases
            log_debug('show new phrases');
            foreach ($this->ids() as $phr_id) {
                $result .= '  <tr>';
                if ($phr_id == 0) {
                    $result .= '    <td colspan="2">';

                    $phr_new = new phrase();
                    //$result .= $phr_new->dsp_selector(null, $script, $url_pos, '', $back);
                    $url_pos++;

                    $result .= '    </td>';
                    $result .= '    <td>' . \Zukunft\ZukunftCom\main\php\web\html\btn_del("Remove new", $used_url) . '</td>';
                }
                $result .= '  </tr>';
            }
        }

        $result .= $html->dsp_tbl_end();

        log_debug('table ended');
        $phr_ids_new = $this->ids();
        //$phr_ids_new[]  = $new_phrase_default;
        $phr_ids_new[] = 0;
        $type_ids_new = $type_ids;
        $type_ids_new[] = 0;
        $used_url = $this_url . $lib->ids_to_url($phr_ids_new, "phrase") .
            $lib->ids_to_url($type_ids_new, "type");
        $result .= '  ' . \Zukunft\ZukunftCom\main\php\web\html\btn_add("Add another phrase", $used_url);
        $result .= '  <br><br>';
        $result .= '  <input type="' . html_base::INPUT_HIDDEN . '" name="back" value="' . $back . '">';
        if ($this->id() > 0) {
            $result .= '  to <input type="' . html_base::INPUT_TEXT . '" name="value" value="' . $this->number() . '">';
        } else {
            $result .= '  is <input type="' . html_base::INPUT_TEXT . '" name="value">';
        }
        $result .= $html->dsp_form_end("Save", $back);
        $result .= '<br><br>';
        log_debug('load source');
        $src = $this->load_source();
        if (isset($src)) {
            $scr_ui = new source($src->api_json([], $msg));
            // TODO Prio 0 add the source selector to the value mask
            //$result .= $scr_ui->dsp_select($script, $back);
            $result .= '<br><br>';
        }

        // display the share type
        $result .= $this->dsp_share($script, $back);

        // display the protection type
        $result .= $this->dsp_protection($script, $back);

        $result .= '<br>';
        $result .= \Zukunft\ZukunftCom\main\php\web\html\btn_back($back);

        // display the user changes
        log_debug('user changes');
        if ($this->id() > 0) {
            $changes = $this->dsp_hist(0, 0, '', $back);
            if (trim($changes) <> "") {
                $result .= $html->dsp_text_h3("Latest changes related to this value", "change_hist");
                $result .= $changes;
            }
            $changes = $this->dsp_hist_links(0, 0, '', $back);
            if (trim($changes) <> "") {
                $result .= $html->dsp_text_h3("Latest link changes related to this value", "change_hist");
                $result .= $changes;
            }
        } else {
            // display similar values as a sample for the user to force a consistent type of entry e.g. cost should always be a negative number
            if (isset($main_wrd)) {
                $main_wrd->load($msg);
                // TODO Prio 2 activate based on a group load
                /*
                $samples = $this->dsp_samples($main_wrd->id, $this->ids(), 10, $back);
                log_debug("value->dsp_edit samples.");
                if (trim($samples) <> "") {
                    $result .= $html->dsp_text_h3('Please have a look at these other "' . $main_wrd->dsp_obj()->name_linked(styles::STYLE_GREY) . '" values as an indication', 'change_hist');
                    $result .= $samples;
                }
                */
            }
        }

        log_debug("done");
        return $result;
    }

    /**
     * @return string that best describes this object
     */
    function display(): string
    {
        return $this->name();
    }

}
