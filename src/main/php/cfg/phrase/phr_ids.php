<?php

/*

    model/phrase/phr_ids.php - helper class for phrase id lists
    ------------------------


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

namespace cfg\phrase;

/**
 * helper class to make sure that
 * a word id   list is never mixed with a phrase id list
 * a triple id list is never mixed with a phrase id list
 * a phrase id list is never mixed with a term id list
 */
class phr_ids
{
    public ?array $lst = null;

    /**
     * @param array|null $ids list of phrase or word ids
     */
    function __construct(?array $ids = null, ?array $trp_ids = null)
    {
        if ($ids != null) {
            $this->lst = $ids;
        } else {
            $this->lst = array();
        }
        if ($trp_ids != null) {
            $this->add_trp_ids($trp_ids);
        }
    }

    /**
     * @param array|null $trp_ids list of wrd ids
     */
    private function add_trp_ids(?array $trp_ids = null): void
    {
        foreach ($trp_ids as $trp_id) {
            $this->lst[] = $trp_id * -1;
        }
    }

    function count(): int
    {
        return (count($this->lst));
    }

    /**
     * @return array with only the word ids
     */
    function wrd_ids(): array
    {
        $wrd_ids = array();
        foreach ($this->lst as $phr_id) {
            if ($phr_id > 0) {
                $wrd_ids[] = $phr_id;
            }
        }
        return $wrd_ids;
    }

    /**
     * @return array with only the triple ids
     */
    function trp_ids(): array
    {
        $trp_ids = array();
        foreach ($this->lst as $phr_id) {
            if ($phr_id < 0) {
                $trp_ids[] = $phr_id;
            }
        }
        return $trp_ids;
    }

    /**
     * TODO check if not the id convert function needs to be used
     * @return trm_ids with the term ids
     */
    function trm_ids(): trm_ids
    {
        $id_lst = [];
        foreach ($this->lst as $phr_id) {
            $id_lst[] = $phr_id;
        }
        return new trm_ids($id_lst);
    }

}