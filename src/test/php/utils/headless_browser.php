<?php

/*

    test/php/utils/headless_browser.php - a browser without a screen that opens a page and returns the text of an element
    -----------------------------------

    the first browser found of LibreWolf, Firefox and chromium is used: LibreWolf and Firefox are
    driven by their remote control Marionette over a local socket, chromium dumps the page after
    the scripts have run (--dump-dom)

    $brw is the suggested var name


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

use Zukunft\ZukunftCom\main\php\shared\library;

class headless_browser
{

    // how a browser is driven
    const string MARIONETTE = 'marionette';
    const string DUMP_DOM = 'dump_dom';
    // the browsers in the order of use with the way they are driven
    const array BROWSERS = [
        'librewolf' => self::MARIONETTE,
        'firefox' => self::MARIONETTE,
        'chromium' => self::DUMP_DOM,
    ];
    // the settings of the temporary profile: Marionette on a free port, which the browser writes to the
    // file MARIONETTE_PORT_FILE of the profile, and no question of LibreWolf for english pages
    const string MARIONETTE_PREFS = 'user_pref("marionette.port", 0);' . "\n"
        . 'user_pref("privacy.spoof_english", 1);' . "\n";
    const string MARIONETTE_PORT_FILE = 'MarionetteActivePort';
    const string MARIONETTE_HOST = '127.0.0.1';
    // the seconds to wait for the start, an answer and the end of the browser
    const int TIMEOUT = 20;
    // the milliseconds that Marionette waits for a page or a script, below TIMEOUT, so that the browser
    // answers with an error before the socket ends the wait
    const int MARIONETTE_TIMEOUT = 10000;
    // the microseconds between two checks if the browser has started or ended
    const int POLL = 100000;
    // the time in milliseconds that chromium gives the page and its scripts before it dumps the page
    const int DUMP_TIME_BUDGET = 5000;
    // the page that Marionette opens between two pages, so that a page that differs only by the hash is loaded again
    const string BLANK = 'about:blank';

    private string $cmd = '';
    private string $kind = '';
    private string $profile = '';
    private int $pid = 0;
    // the socket of Marionette and the id of the last command
    private mixed $socket = null;
    private int $cmd_id = 0;

    /**
     * start the first browser found, which for Marionette starts the browser with a temporary profile
     *
     * @return string the name of the started browser e.g. librewolf or an empty string if none is found
     */
    function start(): string
    {
        $result = '';
        foreach (self::BROWSERS as $name => $kind) {
            if ($result == '') {
                $cmd = trim(shell_exec('command -v ' . escapeshellarg($name)) ?? '');
                if ($cmd != '') {
                    $this->cmd = $cmd;
                    $this->kind = $kind;
                    $result = $name;
                }
            }
        }
        if ($this->kind == self::MARIONETTE and !$this->start_marionette()) {
            $this->stop();
            $result = '';
        }
        return $result;
    }

    /**
     * open the page and return the text of the element after the scripts of the page have created it
     *
     * @param string $url the url of the page e.g. file:///tmp/calculator_editor.html#autarky_formula
     * @param string $id the id of the element e.g. putMessage
     * @return string|null the text of the element or null if the page has no such element or the browser has failed
     */
    function text(string $url, string $id): ?string
    {
        $result = null;
        if ($this->kind == self::MARIONETTE) {
            $this->command('WebDriver:Navigate', ['url' => self::BLANK]);
            $this->command('WebDriver:Navigate', ['url' => $url]);
            // wait until the element exists, which a page script may create after the load
            $script = 'const id = arguments[0]; const done = arguments[arguments.length - 1];'
                . ' const wait = function () { const el = document.getElementById(id);'
                . ' if (el) { done(el.textContent); } else { setTimeout(wait, 50); } };'
                . ' wait();';
            $result = $this->command('WebDriver:ExecuteAsyncScript', ['script' => $script, 'args' => [$id]]);
        } elseif ($this->kind == self::DUMP_DOM) {
            $cmd = escapeshellarg($this->cmd) . ' --headless --disable-gpu --allow-file-access-from-files'
                . ' --virtual-time-budget=' . self::DUMP_TIME_BUDGET
                . ' --dump-dom ' . escapeshellarg($url) . ' 2>/dev/null';
            $dom = shell_exec($cmd) ?? '';
            $match = [];
            if (preg_match('#<[a-z]+ id="' . preg_quote($id, '#') . '">(.*?)</[a-z]+>#s', $dom, $match)) {
                $result = html_entity_decode($match[1]);
            }
        }
        return $result;
    }

    /**
     * end the browser and remove its temporary profile
     */
    function stop(): void
    {
        if ($this->socket !== null) {
            $this->command('Marionette:Quit', []);
            fclose($this->socket);
            $this->socket = null;
        }
        $start = microtime(true);
        while ($this->pid > 0 and file_exists('/proc/' . $this->pid) and microtime(true) - $start < self::TIMEOUT) {
            usleep(self::POLL);
        }
        $this->pid = 0;
        if ($this->profile != '') {
            $lib = new library();
            $lib->dir_remove($this->profile);
            $this->profile = '';
        }
    }

    /**
     * start the browser with Marionette on a free port and open a session
     *
     * @return bool true if the session is open
     */
    private function start_marionette(): bool
    {
        $this->profile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('headless_browser_');
        mkdir($this->profile);
        file_put_contents($this->profile . DIRECTORY_SEPARATOR . 'user.js', self::MARIONETTE_PREFS);
        $cmd = escapeshellarg($this->cmd) . ' --headless --marionette --no-remote --profile '
            . escapeshellarg($this->profile) . ' > /dev/null 2>&1 & echo $!';
        $this->pid = (int)shell_exec($cmd);
        $port_file = $this->profile . DIRECTORY_SEPARATOR . self::MARIONETTE_PORT_FILE;
        $port = 0;
        $start = microtime(true);
        while ($port == 0 and microtime(true) - $start < self::TIMEOUT) {
            usleep(self::POLL);
            if (is_file($port_file)) {
                $port = (int)file_get_contents($port_file);
            }
        }
        if ($port > 0) {
            $socket = fsockopen(self::MARIONETTE_HOST, $port, $err_no, $err_txt, self::TIMEOUT);
            if ($socket !== false) {
                stream_set_timeout($socket, self::TIMEOUT);
                $this->socket = $socket;
                // the greeting of Marionette
                $this->read();
                $this->command('WebDriver:NewSession', ['capabilities' => (object)[]]);
                $this->command('WebDriver:SetTimeouts',
                    ['pageLoad' => self::MARIONETTE_TIMEOUT, 'script' => self::MARIONETTE_TIMEOUT]);
            }
        }
        return $this->socket !== null;
    }

    /**
     * send a command to Marionette and wait for its answer
     *
     * @param string $name the name of the command e.g. WebDriver:Navigate
     * @param array $par the parameters of the command
     * @return mixed the value of the answer or null if the command has failed
     */
    private function command(string $name, array $par): mixed
    {
        $this->cmd_id = $this->cmd_id + 1;
        $msg = json_encode([0, $this->cmd_id, $name, (object)$par]);
        fwrite($this->socket, strlen($msg) . ':' . $msg);
        // the answer is [1, id, error, result]; a late answer of an earlier command whose wait has ended is skipped
        $answer = $this->read();
        while ($answer != [] and $answer[1] != $this->cmd_id) {
            $answer = $this->read();
        }
        return $answer[3]['value'] ?? null;
    }

    /**
     * @return array the next message of Marionette, which starts with its length and a colon
     */
    private function read(): array
    {
        $len = '';
        $char = fgetc($this->socket);
        while ($char !== false and $char !== ':') {
            $len .= $char;
            $char = fgetc($this->socket);
        }
        $data = '';
        $part = '';
        // a closed socket or a timeout ends the read with the part read so far
        while ($part !== false and strlen($data) < (int)$len) {
            $part = fread($this->socket, (int)$len - strlen($data));
            $part = $part === '' ? false : $part;
            $data .= (string)$part;
        }
        return json_decode($data, true) ?? [];
    }

}
