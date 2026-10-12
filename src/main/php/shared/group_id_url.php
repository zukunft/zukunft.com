<?php

/*

    shared/group_id_url.php - the sign chars of a group id and its form in a url
    -----------------------

    a group id of more than four prime phrases is a compact text key: each phrase id is written
    with only its significant alpha_num chars and ended by a sign char, e.g. "0U+0X+3-"
    (see docs/llm/group_id.md and cfg/group/id.php)

    a url uses the same key, only the "+" of a word is written as "_", which no other part of
    the key uses, so that the url needs no "%2B" for it, e.g. "0U_0X_3-"; both chars are read
    as the sign of a word


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
    // the "+" of a word in a url, because a url reads a "+" as a space
    const string CHAR_WORD_URL = '_';
    // the regular expression of one sign char, the same for php, postgres and mysql;
    // the triple sign first, so that the "-" is no range in the char class
    const string SIGN_PATTERN = '['
    . self::CHAR_TRIPLE
    . self::CHAR_FORMULA
    . self::CHAR_SOURCE_TRIPLE
    . self::CHAR_RESULT_TRIPLE
    . self::CHAR_WORD
    . self::CHAR_SOURCE_WORD
    . self::CHAR_RESULT_WORD
    . self::CHAR_WORD_URL . ']';
    // the max number of alpha_num chars of a 32-bit phrase id
    const int ID_CHARS = 6;
    // the alpha_num chars of a phrase id: "." and "/", the digits and the letters
    const string ID_CHAR_PATTERN = '[./0-9A-Za-z]';
    // marks the text key of a result in a figure list like the minus of an integer result id,
    // because a sign char only ends a phrase id and never starts a group id
    const string FIGURE_RESULT_PREFIX = '-';

    /**
     * the group id for a url, e.g. "0U_0X_" for "0U+0X+";
     * an id that is no text key e.g. the integer id of a prime group stays as it is
     *
     * @param int|string $id the group id as the database uses it
     * @return string the id as it is written in a url
     */
    static function to_url(int|string $id): string
    {
        $result = (string)$id;
        if (self::is_key($result)) {
            $result = str_replace(self::CHAR_WORD, self::CHAR_WORD_URL, $result);
        }
        return $result;
    }

    /**
     * the group id as the database uses it from a url, e.g. "0U+0X+" for "0U_0X_";
     * a database key and an integer id stay as they are
     *
     * @param int|string $id the id as it is written in a url
     * @return int|string the group id as the database uses it
     */
    static function from_url(int|string $id): int|string
    {
        $result = $id;
        if (is_string($id) and self::is_key($id)) {
            $result = str_replace(self::CHAR_WORD_URL, self::CHAR_WORD, $id);
        }
        return $result;
    }

    /**
     * @param string $id a text group id
     * @return int the number of phrase ids (incl. a formula id) of the key, because each ends with a sign char
     */
    static function phrase_count(string $id): int
    {
        return preg_match_all('~' . self::SIGN_PATTERN . '~', $id);
    }

    /**
     * the regular expression that finds a phrase within a text group id: the phrase key must
     * start the group id or follow a sign char, because e.g. "0U+" is also the end of "10U+"
     *
     * @param string $phr_key the key of one phrase id incl. its sign char e.g. "0U+"
     * @return string the pattern for php, postgres and mysql e.g. "(^|[-=()+<>_])0U\+"
     */
    static function phrase_pattern(string $phr_key): string
    {
        return '(^|' . self::SIGN_PATTERN . ')' . preg_replace('~[.+()]~', '\\\\$0', $phr_key);
    }

    /**
     * @param string $id a group id
     * @return bool true if the id is a list of phrase ids each ended by a sign char
     */
    private static function is_key(string $id): bool
    {
        $slot = self::ID_CHAR_PATTERN . '{1,' . self::ID_CHARS . '}' . self::SIGN_PATTERN;
        return preg_match('~^(' . $slot . ')+$~', $id) == 1;
    }

}
