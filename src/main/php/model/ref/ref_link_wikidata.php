<?php

/*

  ref_link_wikidata.php - link for the reference type wikidata
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
  along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.
  
  To contact the authors write to:
  Timon Zielonka <timon@zukunft.com>
  
  Copyright (c) 1995-2022 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

class ref_link_wikidata
{

    // to import an entity from wikidata
    function read(): bool
    {
        $result = false;
        log_debug('ref_link_wikidata->read ... done');
        /*
        example
         wget to
         https://www.wikidata.org/w/api.php?action=wbgetentities&ids=Q39|Q183&format=json&languages=en

         use JSON

         save the aliases



        */

        log_debug('ref_link_wikidata->read ... done');
        return $result;
    }

}