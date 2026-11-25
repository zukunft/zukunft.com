<?php

/*

    test/php/utils/test_timing.php - the time of each test section of a saved test log
    ------------------------------

    reads the text log of a test run, e.g. saved by php test/test.php > test_run.log, and creates a
    markdown report with the time per test section, summed per first word and per first two words
    of the section header, and the slowest sections, so that the section that is worth to be run in
    parallel can be found

    the log writer (text_log::time_stamp) puts the runtime seconds in front of every line, and a
    section header (test_base::header and subheader) is a line with only the header text, so the
    time of a section is the time from its header to the next header; a failed test is written as
    an error block from "ERROR <test name>:" to ", took ..." and belongs to its section


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

class test_timing
{

    // a log line: the runtime seconds of text_log::time_stamp, one space and the text
    const string LINE_PATTERN = '/^(\d+\.\d+) (.*)$/';
    // the start of a line that is written by a test step and not by a section header
    const array INFO_PREFIXES = ['ERROR', 'ok ', 'updated ', 'timeout', 'PHP ', 'warning', 'WARNING'];
    // the start and the end of the block of a failed test (test_base::assert_dsp and duration_text)
    const string ERROR_START = 'ERROR ';
    const string ERROR_END = ', took ';
    // the number of the slowest sections in the report
    const int TOP_SECTIONS = 25;
    // the decimals of the seconds in the report and of the rounding of a sum
    const int DECIMALS = 1;
    const int SUM_DECIMALS = 4;
    const int SECONDS_PER_MINUTE = 60;

    /**
     * @param string $line one line of the text log of a test run e.g. "1463.5311 workflow start "
     * @return array|null the runtime seconds and the text without the spaces at the end
     *                    e.g. [1463.5311, 'workflow start'] or null if the line has no timestamp
     */
    function line_parts(string $line): ?array
    {
        $result = null;
        if (preg_match(self::LINE_PATTERN, rtrim($line, "\r\n"), $match) === 1) {
            $result = [(float)$match[1], rtrim($match[2])];
        }
        return $result;
    }

    /**
     * @param string $text the text of a log line without its timestamp
     * @return bool true if the text is a section header e.g. "db write triple", which starts with a
     *              letter and has no colon and no slash, unlike a failed test, an updated file or a
     *              line of a php warning or of a page in an error block
     */
    function is_header(string $text): bool
    {
        $txt = trim($text);
        $result = $txt != '' && ctype_alpha($txt[0]) && !str_contains($txt, ':') && !str_contains($txt, '/');
        foreach (self::INFO_PREFIXES as $prefix) {
            if (str_starts_with($txt, $prefix)) {
                $result = false;
            }
        }
        return $result;
    }

    /**
     * @param string $log the text log of a test run
     * @return array per section header in the order of the log the header text, the seconds until
     *               the next header or the last line of the log and the runtime seconds of its start
     */
    function sections(string $log): array
    {
        $result = [];
        $name = null;
        $start = 0.0;
        $last = 0.0;
        $in_error = false;
        foreach (explode("\n", $log) as $line) {
            $parts = $this->line_parts($line);
            if ($parts !== null) {
                [$sec, $txt] = $parts;
                $last = $sec;
                if ($in_error) {
                    // the lines of a failed test end with its duration
                    $in_error = !str_starts_with($txt, self::ERROR_END);
                } elseif (str_starts_with($txt, self::ERROR_START) && str_ends_with($txt, ':')) {
                    $in_error = true;
                } elseif ($this->is_header($txt)) {
                    if ($name !== null) {
                        $result[] = [$name, round($sec - $start, self::SUM_DECIMALS), $start];
                    }
                    $name = $txt;
                    $start = $sec;
                }
            }
        }
        if ($name !== null) {
            $result[] = [$name, round($last - $start, self::SUM_DECIMALS), $start];
        }
        return $result;
    }

    /**
     * @param array $sections the sections of a test log (see sections)
     * @param int $words the number of the first words of the header that name a group e.g. 2 for "db write"
     * @return array the seconds per group, the slowest group first
     */
    function sums(array $sections, int $words): array
    {
        $result = [];
        foreach ($sections as [$name, $sec]) {
            $group = implode(' ', array_slice(explode(' ', $name), 0, $words));
            $result[$group] = round(($result[$group] ?? 0.0) + $sec, self::SUM_DECIMALS);
        }
        arsort($result);
        return $result;
    }

    /**
     * @param string $log the text log of a test run
     * @param int $top the number of the slowest sections that are listed
     * @return string the markdown report with the total, the time per first word and per first two
     *                words of the section header and the slowest sections
     */
    function md(string $log, int $top = self::TOP_SECTIONS): string
    {
        $sec_lst = $this->sections($log);
        $total = array_sum(array_column($sec_lst, 1));
        $result = '# Test timing' . "\n\n";
        $result .= '- ' . count($sec_lst) . ' sections, ' . $this->seconds_text($total) . ' seconds, '
            . $this->seconds_text($total / self::SECONDS_PER_MINUTE) . ' minutes' . "\n";
        $result .= $this->sum_table('Per first word of the section header', $this->sums($sec_lst, 1), $total);
        $result .= $this->sum_table('Per first two words of the section header', $this->sums($sec_lst, 2), $total);
        $slowest = $sec_lst;
        usort($slowest, fn($a, $b) => $b[1] <=> $a[1]);
        $result .= "\n" . '## The ' . $top . ' slowest sections' . "\n\n";
        $result .= '| section | seconds | minutes | start |' . "\n" . '|---|---|---|---|' . "\n";
        foreach (array_slice($slowest, 0, $top) as [$name, $sec, $start]) {
            $result .= '| ' . $name . ' | ' . $this->seconds_text($sec) . ' | '
                . $this->seconds_text($sec / self::SECONDS_PER_MINUTE) . ' | ' . $this->seconds_text($start) . ' |' . "\n";
        }
        return $result;
    }

    /**
     * @param string $title the title of the table
     * @param array $sums the seconds per group, the slowest group first (see sums)
     * @param float $total the seconds of all sections
     * @return string the markdown table of the groups with their share of the total
     */
    private function sum_table(string $title, array $sums, float $total): string
    {
        $result = "\n" . '## ' . $title . "\n\n";
        $result .= '| group | seconds | minutes | share |' . "\n" . '|---|---|---|---|' . "\n";
        foreach ($sums as $group => $sec) {
            $share = $total > 0 ? 100 * $sec / $total : 0.0;
            $result .= '| ' . $group . ' | ' . $this->seconds_text($sec) . ' | '
                . $this->seconds_text($sec / self::SECONDS_PER_MINUTE) . ' | ' . $this->seconds_text($share) . ' % |' . "\n";
        }
        return $result;
    }

    /**
     * @param float $num the seconds, minutes or the share
     * @return string the number with one decimal and without a thousand separator e.g. "20.0"
     */
    private function seconds_text(float $num): string
    {
        return number_format($num, self::DECIMALS, '.', '');
    }

}
