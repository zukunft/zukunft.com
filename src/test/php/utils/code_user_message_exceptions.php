<?php

/*

    test/php/utils/code_user_message_exceptions.php - list the user_message creations below the entry points
    -----------------------------------------------

    creates the markdown report docs/code_user_message_exceptions.md with every
    'new user_message(' resp. 'new Message(' of the source library, because the message
    of a request is created once by the http resp. api entry point and travels from
    there as the $msg parameter (docs/llm/state-and-messages.md)

    a creation below the entry points is an exception that must be explained by a
    comment directly above it (or trailing on the same line); the report lists the
    still unexplained creations as the remaining rule breaks

    and it checks that a created message is never lost: what it collects must reach
    the caller (merged, returned, read or kept in an object field), because an error
    that nobody reports is the silent failure the message parameter exists to prevent


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

namespace Zukunft\ZukunftCom\test\php\utils;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::SHARED . 'library.php';

use Zukunft\ZukunftCom\main\php\shared\library;

class code_user_message_exceptions
{

    // the source trees below the entry points: the http and api index scripts are outside these
    // trees, because they are the sanctioned place to create the one message of a request
    const array SCAN_PATHS = [
        'main backend' => paths::MODEL,
        'api objects' => paths::API_OBJECT,
        'frontend' => paths::WEB,
        'shared' => paths::SHARED,
    ];

    // the user_message classes themselves are the home of the creation (clone, reset, sub message)
    const array HOME_FILES = ['user_message.php', 'sql_message.php', 'Message.php'];

    // the message classes: the backend user_message and the shared Message that the frontend and
    // the lists use; both are the one message of a request, so both are checked the same way
    private const string MSG_CLASS = '(?:user_message|Message)';
    // the namespace that a creation may carry e.g. 'new \Zukunft\...\user_message('
    private const string NAME_SPACE = '(?:\\\\?[\w\\\\]*\\\\)?';
    // a message creation e.g. '$msg = new user_message($usr)' incl. the namespaced form
    private const string NEW_PATTERN = '/new\s+' . self::NAME_SPACE . self::MSG_CLASS . '\s*\(/i';
    // a creation used as the default value of a function parameter e.g. 'user_message $msg = new user_message()'
    private const string DEFAULT_PATTERN = '/' . self::MSG_CLASS . '\s+\$\w+\s*=\s*new\s+'
    . self::NAME_SPACE . self::MSG_CLASS . '\s*\(/i';
    // a nullable message parameter e.g. '?user_message $msg = null' or 'user_message|null $msg = null'
    // which drops the report of every caller that passes none, just like a default creation does
    private const string NULL_PATTERN = '/(\?\s*' . self::NAME_SPACE . self::MSG_CLASS
    . '|' . self::NAME_SPACE . self::MSG_CLASS . '\s*\|\s*null)\s+\$\w+\s*=\s*null/i';
    // a creation assigned to a variable e.g. '$add_msg = new user_message()' or the fallback form
    // '$add_msg = $msg ?? new user_message()' - the variable name is needed to check whether the
    // collected errors ever reach the caller
    private const string CREATE_VAR_PATTERN = '/\$(\w+)\s*=[^=]*\bnew\s+'
    . self::NAME_SPACE . self::MSG_CLASS . '\s*\(/i';
    // a creation that is the return value e.g. 'return new user_message()', so the caller gets it
    private const string RETURN_PATTERN = '/\breturn\s[^;]*\bnew\s+'
    . self::NAME_SPACE . self::MSG_CLASS . '\s*\(/i';
    // a creation assigned to an object field e.g. '$this->msg = new user_message()' which outlives
    // the function, so the caller can still read what has been collected
    private const string CREATE_FIELD_PATTERN = '/\$\w+(->\w+)+\s*=[^=]*\bnew\s+'
    . self::NAME_SPACE . self::MSG_CLASS . '\s*\(/i';
    // the start of the next function, which ends the scope in which a local message can be reported
    private const string FUNCTION_PATTERN = '/^\s{0,4}(?:public |private |protected |static |abstract |final )*function\s+\w+\s*\(/';
    // the reader functions of a message; calling one of them on a local message means that its
    // content is used, so the collected errors are not lost
    private const string READ_FUNCTIONS = 'is_ok|all_message_text|get_message|get_last_message'
    . '|has_msg_id|is_error|get_all_var_messages';
    // the words that mark a message which is deliberately not reported, e.g. a display path that
    // has no caller message at all; without this the creation is listed as a lost message
    private const string NOT_REPORTED_MARK = 'not reported';

    /**
     * build the markdown report of the user_message creations below the entry points
     *
     * @return string the markdown content for docs/code_user_message_exceptions.md
     */
    function md(): string
    {
        $exp_cnt = 0;
        $def_lst = [];
        $open_lst = [];
        $null_lst = [];
        $lost_lst = [];
        foreach (self::SCAN_PATHS as $sec => $path) {
            $this->scan_section($sec, $path, $exp_cnt, $def_lst, $open_lst, $null_lst, $lost_lst);
        }
        $open_cnt = $this->hit_count($open_lst);
        $def_cnt = $this->hit_count($def_lst);
        $null_cnt = $this->hit_count($null_lst);
        $lost_cnt = $this->hit_count($lost_lst);
        $all_cnt = $exp_cnt + $open_cnt + $def_cnt;

        $md_txt = '# User message exceptions' . "\n";
        $md_txt .= "\n";
        $md_txt .= 'generated by coding_rule_tests::php_user_message_creation_tests - do not edit manually' . "\n";
        $md_txt .= "\n";
        $md_txt .= 'the user_message of a request is created once by the http resp. api entry point and' . "\n";
        $md_txt .= 'is passed down as the $msg parameter (docs/llm/state-and-messages.md), so every' . "\n";
        $md_txt .= '"new user_message(" resp. "new Message(" below the entry points is an exception' . "\n";
        $md_txt .= 'that needs a comment behind the creation on the same line explaining why a local' . "\n";
        $md_txt .= 'message is needed - typically a buffer that is merged back or a message of' . "\n";
        $md_txt .= 'a different user; only a block of sibling buffers shares one comment above it' . "\n";
        $md_txt .= "\n";
        $md_txt .= $all_cnt . ' creations below the entry points: ' . $exp_cnt . ' explained, '
            . $def_cnt . ' parameter defaults and ' . $open_cnt . ' still unexplained' . "\n";
        $md_txt .= 'and ' . $null_cnt . ' nullable message parameters and '
            . $lost_cnt . ' messages that never reach the caller' . "\n";
        $md_txt .= $this->section_md('parameter defaults', $def_lst,
            'a default value drops the message of a caller that passes none,'
            . ' so each of these is a silent message loss waiting for a threading pass');
        $md_txt .= $this->section_md('messages that never reach the caller', $lost_lst,
            'a message that is filled and then goes out of scope loses every error it collected -'
            . ' including an inline "new user_message()" handed to a called function, which no one'
            . ' can read again; merge it into the caller message, return it or read it - and if the'
            . ' drop is on purpose, e.g. a display path with no caller message, say "'
            . self::NOT_REPORTED_MARK . '" in the comment behind the creation');
        $md_txt .= $this->section_md('nullable message parameters', $null_lst,
            'a message parameter is required, because a request has exactly one message and null'
            . ' describes a state that does not exist; "$msg?->add(...)" reports nothing at the call'
            . ' sites that pass none, so a caller without a message needs one itself');
        $md_txt .= $this->section_md('unexplained creations', $open_lst,
            'the remaining rule breaks: explain the exception with a comment or thread the $msg of the caller');
        return $md_txt;
    }

    /**
     * scan one source tree and sort every user_message creation into the explained count,
     * the parameter defaults and the still unexplained creations
     *
     * @param string $sec the section name e.g. 'main backend'
     * @param string $path the source folder of the section e.g. paths::MODEL
     * @param int $exp_cnt (in/out) number of creations explained by a comment
     * @param array $def_lst (in/out) map of file name to the parameter default hits
     * @param array $open_lst (in/out) map of file name to the unexplained hits
     * @param array $null_lst (in/out) map of file name to the nullable message parameter hits
     * @param array $lost_lst (in/out) map of file name to the messages that never reach the caller
     * @return void
     */
    private function scan_section(
        string $sec,
        string $path,
        int    &$exp_cnt,
        array  &$def_lst,
        array  &$open_lst,
        array  &$null_lst,
        array  &$lost_lst
    ): void
    {
        $lib = new library();
        foreach ($lib->array_to_path($lib->dir_to_array($path)) as $code_file) {
            if (!str_ends_with($code_file, '.php') or $this->is_home_file($code_file)) {
                continue;
            }
            $lines = file($path . $code_file);
            $name = $sec . ': ' . str_replace(DIRECTORY_SEPARATOR, '/', $code_file);
            foreach ($lines as $line_idx => $line) {
                if (preg_match(self::NULL_PATTERN, $line) and !$this->is_comment($line)) {
                    $null_lst[$name][] = $name . ':' . ($line_idx + 1) . ' - ' . trim($line);
                }
                if (!preg_match(self::NEW_PATTERN, $line) or $this->is_comment($line)) {
                    continue;
                }
                $hit = $name . ':' . ($line_idx + 1) . ' - ' . trim($line);
                // a message that is created, filled and then dropped loses every collected error
                if (!preg_match(self::DEFAULT_PATTERN, $line)
                    and !str_contains($line, self::NOT_REPORTED_MARK)
                    and !$this->is_reported($lines, $line_idx, $line)) {
                    $lost_lst[$name][] = $hit;
                }
                if (preg_match(self::DEFAULT_PATTERN, $line)) {
                    $def_lst[$name][] = $hit;
                } elseif ($this->is_explained($lines, $line_idx)) {
                    $exp_cnt++;
                } else {
                    $open_lst[$name][] = $hit;
                }
            }
        }
    }

    /**
     * build the markdown section of one hit group with one line per creation
     *
     * @param string $sec the section name e.g. 'unexplained creations'
     * @param array $hit_lst map of file name to its hit lines
     * @param string $explain the one line why the hits of this section are listed
     * @return string the markdown section or an empty string if the section has no hit
     */
    private function section_md(string $sec, array $hit_lst, string $explain): string
    {
        $sec_txt = '';
        if ($hit_lst != []) {
            ksort($hit_lst, SORT_STRING);
            $row_txt = '';
            foreach ($hit_lst as $file_hits) {
                foreach ($file_hits as $hit) {
                    $row_txt .= $hit . "\n";
                }
            }
            $sec_txt = "\n" . '## ' . $sec . "\n" . "\n" . $explain . "\n" . "\n"
                . '```' . "\n" . $row_txt . '```' . "\n";
        }
        return $sec_txt;
    }

    /**
     * @param array $hit_lst map of file name to its hit lines
     * @return int the total number of hits of all files
     */
    private function hit_count(array $hit_lst): int
    {
        $result = 0;
        foreach ($hit_lst as $file_hits) {
            $result += count($file_hits);
        }
        return $result;
    }

    /**
     * check that the errors collected in a created message ever reach the caller
     *
     * a created message is only useful if its content leaves the function again, so it must be
     * returned, assigned to an object field, or - when it is a local variable - merged into another
     * message, returned or read (see is_used); a message that is only filled and then goes out of
     * scope loses every error it collected, which is the silent failure this check exists for
     *
     * a creation that is not named at all, e.g. 'set_type_id($id, new user_message($usr))', is
     * always a loss: there is no variable left to read what the called function reported
     *
     * @param array $lines the code lines of the php file
     * @param int $line_idx the zero based line index of the creation
     * @param string $line the code line of the creation
     * @return bool true if the collected errors reach the caller
     */
    private function is_reported(array $lines, int $line_idx, string $line): bool
    {
        $result = false;
        if (preg_match(self::RETURN_PATTERN, $line)
            or preg_match(self::CREATE_FIELD_PATTERN, $line)) {
            $result = true;
        } elseif (preg_match(self::CREATE_VAR_PATTERN, $line, $var_found)) {
            $result = $this->is_used($lines, $line_idx, $var_found[1]);
        }
        return $result;
    }

    /**
     * check that a named message is merged into another message ($msg->merge($local)), returned,
     * or read via one of the READ_FUNCTIONS before it goes out of scope
     *
     * the scope ends at the next function declaration, so a use in a later function does not
     * count as a report of this message
     *
     * @param array $lines the code lines of the php file
     * @param int $line_idx the zero based line index of the creation
     * @param string $var the variable name of the created message without the $
     * @return bool true if the collected errors reach the caller
     */
    private function is_used(array $lines, int $line_idx, string $var): bool
    {
        $result = false;
        $in_scope = true;
        $name = preg_quote($var, '/');
        $merged = '/->merge\s*\(\s*\$' . $name . '\b/';
        $returned = '/return\s+\$' . $name . '\b/';
        $read = '/\$' . $name . '\s*->\s*(?:' . self::READ_FUNCTIONS . ')\s*\(/';
        $i = $line_idx + 1;
        $last = count($lines);
        while ($i < $last and $in_scope and !$result) {
            $line = $lines[$i];
            if (preg_match(self::FUNCTION_PATTERN, $line)) {
                $in_scope = false;
            } else {
                if (preg_match($merged, $line)
                    or preg_match($returned, $line)
                    or preg_match($read, $line)) {
                    $result = true;
                }
            }
            $i++;
        }
        return $result;
    }

    /**
     * a creation is explained by a comment behind it on the same line, which is where this codebase
     * states why a local message is needed; a comment line above is also accepted, because a block
     * of buffers that belong together (e.g. the per level messages of an import loop) is declared
     * in consecutive lines and shares the one comment above the block
     *
     * @param array $lines the code lines of the php file
     * @param int $line_idx the zero based line index of the creation
     * @return bool true if the creation carries an explanation
     */
    private function is_explained(array $lines, int $line_idx): bool
    {
        $result = false;
        if (str_contains($lines[$line_idx], '//')) {
            $result = true;
        } else {
            // walk up over the sibling creations of the same block to its first line
            $first = $line_idx;
            while ($first > 0 and preg_match(self::NEW_PATTERN, $lines[$first - 1])) {
                $first--;
            }
            if ($first > 0) {
                $result = $this->is_comment($lines[$first - 1]);
            }
        }
        return $result;
    }

    /**
     * @param string $line one code line
     * @return bool true if the line is a comment line, so it never contains code
     */
    private function is_comment(string $line): bool
    {
        $head = ltrim($line);
        return $head != '' and ($head[0] == '*'
                or str_starts_with($head, '//') or str_starts_with($head, '/*'));
    }

    /**
     * @param string $code_file the relative path of the php file
     * @return bool true if the file is a message class, the sanctioned home of the creation
     */
    private function is_home_file(string $code_file): bool
    {
        $result = false;
        foreach (self::HOME_FILES as $home) {
            if (str_ends_with($code_file, DIRECTORY_SEPARATOR . $home)) {
                $result = true;
            }
        }
        return $result;
    }

}