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
  along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.
  
  To contact the authors write to:
  Timon Zielonka <timon@zukunft.com>
  
  Copyright (c) 1995-2021 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

class XMLSerializer
{

    // functions adopted from http://www.sean-barton.co.uk/2009/03/turning-an-array-or-object-into-xml-using-php/

    public static function generateValidXmlFromObj(stdClass $obj, $node_block = 'nodes', $node_name = 'node')
    {
        $arr = get_object_vars($obj);
        return self::generateValidXmlFromArray($arr, $node_block, $node_name);
    }

    public static function generateValidXmlFromArray($array, $node_block = 'nodes', $node_name = 'node')
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>';

        $xml .= '<' . $node_block . '>';
        $xml .= self::generateXmlFromArray($array, $node_name);
        $xml .= '</' . $node_block . '>';

        return $xml;
    }

    private static function generateXmlFromArray($array, $node_name)
    {
        $xml = '';

        if (is_array($array) || is_object($array)) {
            foreach ($array as $key => $value) {
                if (is_numeric($key)) {
                    $key = $node_name;
                }

                $xml .= '<' . $key . '>' . self::generateXmlFromArray($value, $node_name) . '</' . $key . '>';
            }
        } else {
            $xml = htmlspecialchars($array, ENT_QUOTES);
        }

        return $xml;
    }

}

class xml_io
{

    // parameters to filter the export
    public $usr = NULL; // the user who wants to im- or export
    public $phr_lst = NULL; // to export all values related to this phrase


    // to build the xml
    //public $phr_lst_used = NULL; // all phrases used by the exported values

    // export zukunft.com data as xml
    function export($debug)
    {
        zu_debug('xml_io->export', $debug - 10);
        $result = '';

        // get the export object
        $export_instance = new export;
        $export_instance->usr = $this->usr;
        $export_instance->phr_lst = $this->phr_lst;
        $export_obj = $export_instance->get($debug - 1);

        zu_debug('xml_io->export xml string from ' . json_encode($export_obj), $debug - 16);

        $xml_generator = new XMLSerializer;
        $std_class = json_decode(json_encode($export_obj));
        $result .= $xml_generator->generateValidXmlFromObj($std_class);

        zu_debug('xml_io->export done with ' . $result, $debug - 16);

        return $result;
    }


}

