<?php

/*

  xml.php - to im- and export xml files
  -------
  
  offer the user the long or the short version
  the short version is using one time ids for words, triples and groups
  
  add the instance id, user id and time stamp to the export file
  
  
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

namespace cfg\export;

use cfg\const\paths;

include_once paths::MODEL_SANDBOX . 'user_service.php';
include_once paths::MODEL_PHRASE . 'phrase_list.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::EXPORT . 'xml_serializer.php';

use cfg\phrase\phrase_list;
use cfg\sandbox\user_service;
use cfg\user\user;

class xml extends user_service
{

    /**
     * create a xml for export based on a given phrase list
     *
     * @param phrase_list $phr_lst the phrases that are used to select the export
     * @param user|null $dsp_usr if the requesting user is privileged the view of the user that should be exported
     * @return string the xml with the words, values, formulas, results and views from the $dsp_usr point of view
     */
    function export_by_phrase_list(phrase_list $phr_lst, ?user $dsp_usr = null): string
    {
        log_debug();
        $result = '';

        // set the user sandbox that should be used for the data selection
        $usr = $this->user();
        if ($dsp_usr != null) {
            $usr = $dsp_usr;
        }

        // get the export object
        $export_instance = new export;
        $export_obj = $export_instance->get($usr, $phr_lst);
        log_debug(json_encode($export_obj));

        // create the xml
        $xml_generator = new xml_serializer;
        $std_class = json_decode(json_encode($export_obj));
        $result .= $xml_generator->generate_valid_xml_from_obj($std_class);
        log_debug($result);

        return $result;
    }


}

