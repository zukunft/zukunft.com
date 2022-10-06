<?php

/*

    api\triple.php - the minimal triple (word_link) object
    --------------


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

namespace api;

use html\term_dsp;
use word_link;

class triple_api extends user_sandbox_named_api
{

    // the triple components
    private phrase_api $from;
    private verb_api $verb;
    private phrase_api $to;

    /*
     * construct and map
     */

    function __construct(
        int $id = 0,
        string $name = '',
        string $from = '',
        string $verb = '',
        string $to = ''
    )
    {
        parent::__construct($id, $name);
        $this->from = new phrase_api(0, $from);
        $this->verb= new verb_api(0, $verb);
        $this->to = new phrase_api(0, $to);
    }

    /*
     * casting objects
     */

    function term(): term_api|term_dsp
    {
        return new term_api($this->id, $this->name, word_link::class);
    }

}
