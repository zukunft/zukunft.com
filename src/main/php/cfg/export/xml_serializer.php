<?php

/*

    xml_serializer.php - turning an array or object into XML using PHP
    ------------------

    functions adopted from http://www.sean-barton.co.uk/2009/03/turning-an-array-or-object-into-xml-using-php/


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

    Copyright (c) 1995-2024 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace cfg\export;

use stdClass;

class xml_serializer
{

    //

    public static function generate_valid_xml_from_obj(
        stdClass $obj,
        string $node_block = 'nodes',
        string $node_name = 'node'
    ): string
    {
        $arr = get_object_vars($obj);
        return self::generate_valid_xml_from_array($arr, $node_block, $node_name);
    }

    public static function generate_valid_xml_from_array(
        $array,
        string $node_block = 'nodes',
        string $node_name = 'node'
    ): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>';

        $xml .= '<' . $node_block . '>';
        $xml .= self::generate_xml_from_array($array, $node_name);
        $xml .= '</' . $node_block . '>';

        return $xml;
    }

    private static function generate_xml_from_array($array, string $node_name): string
    {
        $xml = '';

        if (is_array($array) || is_object($array)) {
            foreach ($array as $key => $value) {
                if (is_numeric($key)) {
                    $key = $node_name;
                }

                $xml .= '<' . $key . '>' . self::generate_xml_from_array($value, $node_name) . '</' . $key . '>';
            }
        } else {
            $xml = htmlspecialchars($array, ENT_QUOTES);
        }

        return $xml;
    }

}
