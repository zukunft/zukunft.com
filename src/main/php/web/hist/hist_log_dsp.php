<?php

/*

    word_log_dsp.php - display the past changes of an object
    ----------------

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

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

namespace html\hist;

include_once WEB_HTML_PATH . 'html_base.php';
include_once MODEL_WORD_PATH . 'word.php';
include_once WEB_WORD_PATH . 'word.php';

use html\html_base;
use html\word\word as word_dsp;
use cfg\word\word;

class hist_log_dsp
{

    // show the changes of the view
    function dsp_log_view(word $wrd, string $back = ''): string
    {
        $html = new html_base();
        log_debug($wrd->id());
        $result = '';

        // if ($this->id() <= 0 OR !is_null($this->usr_id)) {
        if ($wrd->id() <= 0) {
            $result .= 'no word selected';
        } else {
            // load the word parameters if not yet done
            if ($wrd->name() == "") {
                $wrd->load_by_id($wrd->id());
            }

            $wrd_dsp = new word_dsp($wrd->api_json());
            $changes = $wrd_dsp->dsp_hist(1, 20, '', $back);
            if (trim($changes) <> "") {
                $result .= $html->dsp_text_h3("Latest view changes related to this word", "change_hist");
                $result .= $changes;
            }
        }

        return $result;
    }

}
