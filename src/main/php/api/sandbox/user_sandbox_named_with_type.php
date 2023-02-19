<?php

/*

    api/sandbox/user_sandbox_named_with_type_api.php - extends the superclass for named api objects with the type id
    ------------------------------------------------


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

use html\phrase_dsp;
use html\term_dsp;
use html\word_dsp;

class user_sandbox_named_with_type_api extends user_sandbox_named_api
{

    // all named objects can have a type that links predefined functionality to it
    // e.g. all value assigned with the percent word are per default shown as percent with two decimals
    // the frontend object just contains the id of the type
    // because the type can be fast selected from the preloaded type list
    public ?int $type_id;


    /*
     * construct and map
     */

    function __construct(int $id = 0, string $name = '', ?string $description = null, ?int $type_id = null)
    {
        parent::__construct($id, $name);
        $this->set_type_id($type_id);
    }


    /*
     * set and get
     */

    public function set_type_id(?int $type_id): void
    {
        $this->type_id = $type_id;
    }

    public function type_id(): ?int
    {
        return $this->type_id;
    }

    /*
     * cast
     */

    /**
     * @return phrase_api|phrase_dsp the related phrase api or display object with the basic values filled
     */
    function phrase(): phrase_api|phrase_dsp
    {
        if ($this::class == word_api::class) {
            $phr = new phrase_api($this->id, $this->name);
            $phr->set_type($this->type_id());
            return $phr;
        } elseif ($this::class == word_dsp::class) {
            $phr =  new phrase_dsp($this->id, $this->name);
            $phr->set_type($this->type_id());
            return $phr;
        } elseif ($this::class == triple_api::class) {
            $phr =  new phrase_api($this->id * -1, $this->name);
            $phr->set_type($this->type_id());
            return $phr;
        } else {
            log_err('Unexpected ' . $this::class);
            return new phrase_api($this->id, $this->name);
        }
    }

    function term(): term_api|term_dsp
    {
        return new term_api($this->id, $this->name);
    }

}


