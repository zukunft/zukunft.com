<?php

/*

    web/system/system_time.php - one measured request time e.g. a slow url shown on the admin dashboard
    --------------------------

    $sys_tim is the suggested var name

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

    Copyright (c) 1995-2026 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\web\system;

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::SANDBOX . 'db_object.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::SHARED . 'json_fields.php';
include_once html_paths::SHARED_ENUM . 'messages.php';

use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\sandbox\db_object;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use DateTime;
use DateTimeInterface;
use Exception;

class system_time extends db_object
{

    // the chars of the url shown in the response time table, so that the table fits into the dashboard column
    const int URL_SHOW_CHARS = 100;
    // the mark behind a cut url, whose full text is shown as tooltip
    const string URL_CUT_MARK = '...';

    /*
     * object vars
     */

    // the start of the measured request
    public ?DateTime $start_time = null;
    // the request url as received from the frontend
    public string $url = '';
    // the execution time of the request
    public int $milliseconds = 0;


    /*
     * set and get
     */

    /**
     * set the vars of this request time based on the api json array
     * @param array $json_array an api json message
     * @param user_message $msg to report a timestamp that cannot be converted
     * @return bool true if the mapping has been completed successfully
     */
    function api_mapper(array $json_array, user_message $msg): bool
    {
        parent::api_mapper($json_array, $msg);
        if (array_key_exists(json_fields::TIME_START, $json_array)) {
            try {
                $this->start_time = new DateTime($json_array[json_fields::TIME_START]);
            } catch (Exception $e) {
                $msg->add_error_text('Error converting the request start time ' . $json_array[json_fields::TIME_START]
                    . ' because ' . $e->getMessage());
            }
        }
        if (array_key_exists(json_fields::URL, $json_array)) {
            $this->url = $json_array[json_fields::URL];
        }
        if (array_key_exists(json_fields::MILLISECONDS, $json_array)) {
            $this->milliseconds = (int)$json_array[json_fields::MILLISECONDS];
        }
        return $msg->is_ok();
    }


    /*
     * info
     */

    /**
     * @param int $limit the milliseconds from which a request is critical
     * @return bool true if the request has taken at least the given milliseconds
     */
    function is_critical(int $limit): bool
    {
        return $this->milliseconds >= $limit;
    }


    /*
     * display
     */

    /**
     * @return string the html table row of this request time
     */
    function display(): string
    {
        $html = new html_base();
        $row = $html->td($this->url_html());
        $row .= $html->td((string)$this->milliseconds);
        $row .= $html->td($this->start_time?->format(DateTimeInterface::ATOM) ?? '');
        return $html->tr($row);
    }

    /**
     * @return string the escaped url, cut after URL_SHOW_CHARS chars with the full url as tooltip
     */
    private function url_html(): string
    {
        $html = new html_base();
        $result = $html->esc($this->url);
        if (mb_strlen($this->url) > self::URL_SHOW_CHARS) {
            $short = $html->esc(mb_substr($this->url, 0, self::URL_SHOW_CHARS)) . self::URL_CUT_MARK;
            $result = $html->with_title($short, $this->url);
        }
        return $result;
    }

    /**
     * @return string the html table header row of the request times
     */
    function header(): string
    {
        global $mtr;
        $html = new html_base();
        $row = $html->th($mtr->txt(msg_id::ADMIN_HEAD_URL));
        $row .= $html->th($mtr->txt(msg_id::ADMIN_HEAD_MILLISECONDS));
        $row .= $html->th($mtr->txt(msg_id::ADMIN_HEAD_TIME));
        return $html->tr($row);
    }

}
