<?php

/*

    web/component/execute/ui_rank.php - html user interface components for ranking objects
    ---------------------------------


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

include_once html_paths::EXECUTE . 'ui_list.php';
include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::SANDBOX . 'combine_named.php';
include_once html_paths::SANDBOX . 'db_object.php';
include_once html_paths::SHARED_ENUM . 'messages.php';
include_once html_paths::SHARED . 'url_var.php';
include_once html_paths::USER . 'user_message.php';

use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\sandbox\combine_named;
use Zukunft\ZukunftCom\main\php\web\sandbox\db_object;
use Zukunft\ZukunftCom\main\php\web\user\user_message;

class ui_rank
{

    /**
     * the triple names that are used by the system
     * e.g. to sort the related terms
     *      or the calculate the impact and create warning based on a threshold
     *
     * @return string with the triple names as a vertical list
     */
    function system_phrases(db_object $phr): string
    {
        // TODO Prio 2 review
        return $phr->name() . ' is ranked by time of discovery';
    }

    /**
     * the criterion of the ranking list, which is the impact, because no backend calculates another ranking
     * @return string the translated text that names the impact as the ranking criterion
     */
    function ranking_parameters(): string
    {
        global $mtr;
        return $mtr->txt(msg_id::RANKING_BY_IMPACT);
    }

    /**
     * the phrases related to the given phrase with the highest impact first
     * @param user_message $msg to report a problem of the related phrases
     * @param db_object|combine_named|null $dbo the phrase whose related phrases should be ranked
     * @param data_object|null $cfg the request cache with the related phrases
     * @return string the html code of the related phrases sorted by impact
     */
    function ranking_list(user_message $msg, db_object|combine_named|null $dbo = null, ?data_object $cfg = null): string
    {
        return new ui_list()->phrases_related($msg, $dbo, $cfg);
    }

}
