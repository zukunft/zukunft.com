<?php

/*

    /web/system/batch_job.php - the extension of the batch_job API objects to create batch_job base html code
    -------------------------

    This file is part of the frontend of zukunft.com - calc with batch_jobs

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

namespace html;

use api\batch_job_api;
use api\phrase_api;
use back_trace;
use cfg\phrase_type;

class batch_job_dsp extends batch_job_api
{


    /*
     * base elements
     */

    /**
     * @returns string simply the batch_job name, but later with mouse over that shows the description
     */
    function dsp(): string
    {
        return $this->type()->name;
    }

    /**
     * display a batch_job with a link to the main page for the batch_job
     * @param string|null $back the back trace url for the undo functionality
     * @param string $style the CSS style that should be used
     * @returns string the html code
     */
    function dsp_link(?string $back = '', string $style = ''): string
    {
        $html = new html_base();
        $url = $html->url(api::VIEW, $this->id, $back, api::PAR_VIEW_WORDS);
        return $html->ref($url, $this->name(), $this->description(), $style);
    }

    /**
     * @param string $back the back trace url for the undo functionality
     * @param string $style the CSS style that should be used
     * @returns string the batch_job as a table cell
     */
    function td(string $back = '', string $style = '', int $intent = 0): string
    {
        $cell_text = $this->dsp_link($back, $style);
        return (new html_base)->td($cell_text, $intent);
    }

    /**
     * @param string $back the back trace url for the undo functionality
     * @param string $style the CSS style that should be used
     * @returns string the batch_job as a table cell
     */
    function th(string $back = '', string $style = ''): string
    {
        return (new html_base)->th($this->dsp_link($back, $style));
    }

    /**
     * @return string the html code for a table row with the batch_job
     */
    function tr(): string
    {
        return (new html_base())->tr($this->td());
    }

}
