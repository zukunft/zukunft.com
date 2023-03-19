<?php

/*

    /web/phrase_group_dsp.php - the extension of the phrase group api object to create the HTML code to display a word or triple
    ------------------------

    mainly links to the word and triple display functions


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

namespace html;

use api\phrase_group_api;
use api\phrase_list_api;

class phrase_group_dsp extends sandbox_named_dsp
{

    /*
     * object vars
     */

    // list of word_min and triple_min objects
    private array $lst;

    // memory vs speed optimize vars
    private array $id_lst;
    private bool $lst_dirty;
    private string $name_linked;
    private bool $name_dirty;


    /*
     * construct and map
     */

    function __construct(int $id = 0, string $name = '')
    {
        parent::__construct($id, $name);
        $this->lst = [];

        $this->id_lst = array();
        $this->lst_dirty = false;
        $this->name_linked = '';
        $this->name_dirty = true;
    }


    /*
     * set and get
     */

    function set_lst($lst): void
    {
        $this->lst = $lst;
        $this->set_dirty();
    }

    function reset_lst(): void
    {
        $this->lst = array();
        $this->set_dirty();
    }

    function set_dirty(): void
    {
        $this->lst_dirty = true;
        $this->name_dirty = true;
    }

    function unset_name_dirty(): void
    {
        $this->name_dirty = false;
    }

    /**
     * @returns array the protected list of phrases
     */
    function lst(): array
    {
        return $this->lst;
    }

    function name_dirty(): bool
    {
        return $this->name_dirty;
    }

    /**
     * @returns array with all unique phrase ids og this list
     */
    private function id_lst(): array
    {
        $result = array();
        if ($this->lst_dirty) {
            foreach ($this->lst as $phr) {
                if (!in_array($phr->id, $result)) {
                    $result[] = $phr->id;
                }
            }
            $this->lst_dirty = false;
        } else {
            $result = $this->id_lst;
        }
        return $result;
    }

    /**
     * add a phrase to the list
     * @returns bool true if the phrase has been added
     */
    function add(phrase_dsp $phr): bool
    {
        $result = false;
        if (!in_array($phr->id(), $this->id_lst())) {
            $this->lst[] = $phr;
            $this->set_dirty();
            $result = true;
        }
        return $result;
    }

    /**
     * @returns phrase_list_dsp the list of phrases as an object
     */
    function phr_lst(): phrase_list_dsp
    {
        $result = new phrase_list_dsp();
        $result->set_lst($this->lst());
        return $result;
    }


    /*
     * info
     */

    function has_percent(): bool
    {
        return $this->phr_lst()->has_percent();
    }


    /**
     * @param phrase_list_dsp|null $phr_lst_header list of phrases already shown in the header and don't need to be include in the result
     * @return string
     */
    function name_linked(phrase_list_dsp $phr_lst_header = null): string
    {
        $result = '';
        if ($this->name_dirty() or $phr_lst_header != null) {
            if ($this->name <> '') {
                $result .= $this->name();
            } else {
                $lst_to_show = $this->phr_lst();
                if ($phr_lst_header != null) {
                    if (!$phr_lst_header->is_empty()) {
                        $lst_to_show->remove($phr_lst_header);
                    }
                }
                foreach ($lst_to_show->lst() as $phr) {
                    if ($result <> '') {
                        $result .= ', ';
                    }
                    $result .= $phr->dsp_link();
                }
            }
            $this->unset_name_dirty();
        } else {
            $result = $this->name();
        }
        return $result;
    }

    /*
     * set and get
     */

    function set_lst_dsp(array $lst): void
    {
        $phr_lst_dsp = array();
        foreach ($lst as $phr) {
            $phr_lst_dsp[] = $phr->dsp_obj();
        }
        $this->set_lst($phr_lst_dsp);
    }

}
