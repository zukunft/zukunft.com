<?php

/*

    shared/group_id_url.php - the short form of a group id in a url
    -----------------------

    a group id of up to 16 phrases is a 112 char database key of 16 slots with 6 alpha_num chars
    for the phrase id and one sign char, e.g. "....0U+....0X+......+" (see cfg/group/id.php);
    a url names the same group shorter: the leading "." (zero) chars of a phrase id are left
    out, an empty slot is left out and the "+" of a word is written as "_", which no other part
    of the key uses, so that the url needs no "%2B" for it, e.g. "0U_0X_"

    the sign char ends each phrase id, so the short form is unambiguous and a database key
    is also accepted as a url id


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

    Copyright (c) 1995-2026 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\shared;

class group_id_url
{

    // the sign chars that end a phrase id of a group or result key (see cfg/group/id.php)
    const string CHAR_FORMULA = '=';
    const string CHAR_TRIPLE = '-';
    const string CHAR_SOURCE_TRIPLE = '(';
    const string CHAR_RESULT_TRIPLE = ')';
    const string CHAR_WORD = '+';
    const string CHAR_SOURCE_WORD = '<';
    const string CHAR_RESULT_WORD = '>';
    const array CHAR_SIGNS = [
        self::CHAR_FORMULA,
        self::CHAR_TRIPLE,
        self::CHAR_SOURCE_TRIPLE,
        self::CHAR_RESULT_TRIPLE,
        self::CHAR_WORD,
        self::CHAR_SOURCE_WORD,
        self::CHAR_RESULT_WORD,
    ];
    // the "+" of a word in a url, because a url reads a "+" as a space
    const string CHAR_WORD_URL = '_';
    // the alpha_num char of zero, which fills a phrase id to its length and an empty slot
    const string CHAR_ZERO = '.';
    // the number of alpha_num chars of one phrase id and the number of slots of a key
    const int ID_CHARS = 6;
    const int KEY_SLOTS = 16;
    // the alpha_num chars of a phrase id: "." and "/", the digits and the letters
    const string ID_CHAR_PATTERN = '[./0-9A-Za-z]';

    /**
     * the short form of a group id for a url, e.g. "0U_0X_" for "....0U+....0X+......+...";
     * an id that is no alpha_num key e.g. the integer id of a prime group stays as it is
     *
     * @param int|string $id the group id as the database uses it
     * @return string the id as it is written in a url
     */
    static function to_url(int|string $id): string
    {
        $result = (string)$id;
        if (self::is_key($result)) {
            $url_id = '';
            foreach (str_split($result, self::ID_CHARS + 1) as $slot) {
                $phr_id = ltrim(substr($slot, 0, self::ID_CHARS), self::CHAR_ZERO);
                // an empty slot is left out, because the sign char ends every phrase id
                if ($phr_id != '') {
                    $url_id .= $phr_id . self::sign_to_url(substr($slot, self::ID_CHARS, 1));
                }
            }
            $result = $url_id;
        }
        return $result;
    }

    /**
     * the group id as the database uses it from the short form of a url, e.g.
     * "....0U+....0X+......+..." for "0U_0X_"; a database key and an integer id stay as they are
     *
     * @param int|string $id the id as it is written in a url
     * @return int|string the group id as the database uses it
     */
    static function from_url(int|string $id): int|string
    {
        $result = $id;
        if (is_string($id) and self::is_url_key($id)) {
            $slots = [];
            $phr_id = '';
            foreach (str_split($id) as $char) {
                if (in_array($char, self::CHAR_SIGNS) or $char == self::CHAR_WORD_URL) {
                    $slots[] = str_pad($phr_id, self::ID_CHARS, self::CHAR_ZERO, STR_PAD_LEFT)
                        . self::sign_from_url($char);
                    $phr_id = '';
                } else {
                    $phr_id .= $char;
                }
            }
            // a group of up to 16 phrases is filled with empty slots to the fixed key length
            while (count($slots) < self::KEY_SLOTS) {
                $slots[] = str_repeat(self::CHAR_ZERO, self::ID_CHARS) . self::CHAR_WORD;
            }
            $result = implode('', $slots);
        }
        return $result;
    }

    /**
     * @param string $id a group id
     * @return bool true if the id is a database key of phrase ids with the fixed length and a sign char
     */
    private static function is_key(string $id): bool
    {
        $slot = self::ID_CHAR_PATTERN . '{' . self::ID_CHARS . '}[' . preg_quote(implode('', self::CHAR_SIGNS), '~') . ']';
        return preg_match('~^(' . $slot . ')+$~', $id) == 1;
    }

    /**
     * @param string $id a group id of a url
     * @return bool true if the id is a list of phrase ids each ended by a sign char, in the short or the database form
     */
    private static function is_url_key(string $id): bool
    {
        $signs = preg_quote(implode('', self::CHAR_SIGNS) . self::CHAR_WORD_URL, '~');
        return preg_match('~^(' . self::ID_CHAR_PATTERN . '{1,' . self::ID_CHARS . '}[' . $signs . '])+$~', $id) == 1;
    }

    /**
     * @param string $sign the sign char of a key slot
     * @return string the sign char as it is written in a url
     */
    private static function sign_to_url(string $sign): string
    {
        return $sign == self::CHAR_WORD ? self::CHAR_WORD_URL : $sign;
    }

    /**
     * @param string $sign the sign char of a url slot
     * @return string the sign char as the database key uses it
     */
    private static function sign_from_url(string $sign): string
    {
        return $sign == self::CHAR_WORD_URL ? self::CHAR_WORD : $sign;
    }

}
