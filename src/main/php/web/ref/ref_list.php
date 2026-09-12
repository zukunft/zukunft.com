<?php

/*

    web/ref/ref_list.php - create the HTML code to display a reference list
    --------------------

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

namespace Zukunft\ZukunftCom\main\php\web\ref;

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::CONST . 'icons.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::HTML . 'styles.php';
include_once html_paths::PHRASE . 'phrase.php';
include_once html_paths::PHRASE . 'phrase_list.php';
include_once html_paths::SANDBOX . 'ListBase.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::SHARED . 'json_fields.php';
include_once html_paths::SHARED . 'url_var.php';

use Zukunft\ZukunftCom\main\php\web\const\icons;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\html\styles;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\web\sandbox\ListBase;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\url_var;

class ref_list extends ListBase
{

    /*
     * set and get
     */

    /**
     * set the vars of a source object based on the given json
     * @param array $json_array an api single object json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function api_mapper(array $json_array): user_message
    {
        return parent::api_mapper_list($json_array, new ref());
    }

    /**
     * select the references that are linked to the given phrase
     * @param phrase|null $phr
     * @return ref_list
     */
    function get_by_phrase(phrase|null $phr, user_message $msg): ref_list
    {
        $ref_lst = new ref_list();
        if ($phr != null) {
            foreach ($this->lst() as $ref) {
                if ($ref->has_phrase($phr)) {
                    $ref_lst->add($ref, $msg);
                }
            }
        }
        return $ref_lst;
    }


    /*
     * display
     */

    /**
     * show the references below each other, sorted first so the html order is deterministic
     * and independent of the api/db row order (see the frontend "sort every rendered list" rule)
     * @param phrase_list $context_phr_lst phrases not repeated in the reference name
     * @param array $url_arr the url vars of the calling page for the back link
     * @param string $style to define e.g. the width of the list
     * @param int|null $limit the max number of entries to show
     * @param int|null $page the offset if there are more entries that could be shown at once
     * @return string the html code to show the references
     */
    function list(
        user_message $msg,
        phrase_list $context_phr_lst = new phrase_list(),
        array       $url_arr = [],
        string      $style = '',
        ?int        $limit = null,
        ?int        $page = null
    ): string
    {
        $this->sort_by_impact_and_type();
        return parent::list($msg, $context_phr_lst, $url_arr, $style, $limit, $page);
    }

    /**
     * a small plus icon shown at the end of the reference list of a phrase that opens the ref add
     * form with the phrase preselected, so the user can add a reference to e.g. the shown word
     *
     * @param phrase $phr the phrase the new reference should be linked to
     * @param array $url_arr the url vars of the calling page for the back link
     * @return string the html code of the add icon, empty if the phrase is not yet saved
     */
    function add_link(phrase $phr, array $url_arr = []): string
    {
        global $mtr;

        $result = '';
        // a phrase without a db id cannot be linked, so there is nothing to add a reference to
        if ($phr->id() != 0) {
            $html = new html_base();
            $url = $html->url_back(ref::VIEW_ADD_ID, 0, $url_arr, url_var::PHRASE . '=' . $phr->id());
            $result = $html->ref($url, $html->icon(icons::ADD),
                $mtr->txt(ref::MSG_ADD), styles::HEADING_ICON_INLINE, true);
        }
        return $result;
    }

    /**
     * sort the references by impact (highest first) and then by reference type name, with the
     * external key as the final tiebreaker, so the rendered order is always deterministic
     * @return void
     */
    function sort_by_impact_and_type(): void
    {
        $lst = $this->lst();
        usort($lst, function (ref $a, ref $b) {
            return ($b->impact ?? 0.0) <=> ($a->impact ?? 0.0)
                ?: strcmp($a->type_name(), $b->type_name())
                ?: strcmp($a->external_key() ?? '', $b->external_key() ?? '');
        });
        $this->set_lst($lst);
    }

}
