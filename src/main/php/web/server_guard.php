<?php

/*

    web/server_guard.php - enforce the file based IP / user whitelist
    --------------------

    Called early from frontend->start(). If the server admin page has activated
    a whitelist (server_admin/state.json), this rejects requests that are not on
    the list by sending the matching offline page from /optional and stopping.

    It is deliberately self-contained (only the session and a few files, no
    database), so it still works while the database is offline and it adds
    almost no overhead when no whitelist is active.

      - IP whitelist active   -> only IPs / CIDRs in server_admin/ip_whitelist.txt
                                 may connect, others get optional/ip_reject.html
      - user whitelist active -> a logged in user whose id or name is not in
                                 server_admin/user_whitelist.txt gets
                                 optional/user_reject.html


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

namespace Zukunft\ZukunftCom\main\php\web;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::SHARED . 'url_var.php';

use Zukunft\ZukunftCom\main\php\shared\url_var;

class server_guard
{
    // placeholder in the offline reject pages, replaced with the admin contact email at serve time
    private const string CONTACT_PLACEHOLDER = '{{ADMIN_CONTACT}}';

    /**
     * reject the current request if it violates an active whitelist.
     * safe to call on every request: it does nothing unless a whitelist is on.
     */
    public static function enforce(): void
    {
        // without the path const we cannot locate the config - stay out of the way
        if (!defined('ROOT_PATH')) {
            return;
        }
        $state = self::read_state(ROOT_PATH . 'server_admin' . DIRECTORY_SEPARATOR . 'state.json');

        // IP whitelist works for every request, even anonymous ones
        if ($state['ip_whitelist_active'] ?? false) {
            $list = self::read_list(ROOT_PATH . 'server_admin' . DIRECTORY_SEPARATOR . 'ip_whitelist.txt');
            self::warn_if_empty_ip_whitelist($list);
            if (!self::ip_allowed($_SERVER['REMOTE_ADDR'] ?? '', $list)) {
                self::reject('optional' . DIRECTORY_SEPARATOR . 'ip_reject.html');
            }
        }

        // the user whitelist only restricts logged in users, so the login page
        // stays reachable for everybody
        if (($state['user_whitelist_active'] ?? false) && !empty($_SESSION[url_var::SESSION_LOGGED])) {
            self::enforce_user(
                (string)($_SESSION[url_var::SESSION_USER_ID] ?? ''),
                (string)($_SESSION[url_var::USERNAME_HUMAN] ?? '')
            );
        }
    }

    /**
     * reject a just-authenticated user whose id or name is not on an active
     * user whitelist. Called at login / signup / activation time so a rejected
     * user is stopped immediately instead of only on the next request.
     * A no-op unless the user whitelist is active.
     */
    public static function enforce_user(string $usr_id, string $usr_name): void
    {
        if (self::user_rejected($usr_id, $usr_name)) {
            self::reject('optional' . DIRECTORY_SEPARATOR . 'user_reject.html');
        }
    }

    /**
     * true if an active user whitelist would reject this user (by id or name).
     * Lets callers react without sending the reject page, e.g. signup showing a
     * clear "ask the admin to add you" message instead of creating an account.
     * Returns false (allowed) when no user whitelist is active.
     */
    public static function user_rejected(string $usr_id, string $usr_name): bool
    {
        if (!defined('ROOT_PATH')) {
            return false;
        }
        $state = self::read_state(ROOT_PATH . 'server_admin' . DIRECTORY_SEPARATOR . 'state.json');
        if (!($state['user_whitelist_active'] ?? false)) {
            return false;
        }
        $list = self::read_list(ROOT_PATH . 'server_admin' . DIRECTORY_SEPARATOR . 'user_whitelist.txt');
        return !self::user_allowed($usr_id, $usr_name, $list);
    }

    /** log a warning if an active IP whitelist is empty (which locks everyone out) */
    private static function warn_if_empty_ip_whitelist(array $list): void
    {
        if ($list === [] && function_exists('log_warning')) {
            log_warning(
                'the IP whitelist is active but empty - every visitor is locked out of the main app; '
                . 'add entries to server_admin/ip_whitelist.txt or deactivate the IP whitelist',
                'server_guard'
            );
        }
    }

    /** @return array{user_whitelist_active:bool,ip_whitelist_active:bool} */
    private static function read_state(string $file): array
    {
        $default = ['user_whitelist_active' => false, 'ip_whitelist_active' => false];
        if (!is_readable($file)) {
            return $default;
        }
        $data = json_decode((string)file_get_contents($file), true);
        if (!is_array($data)) {
            return $default;
        }
        return [
            'user_whitelist_active' => (bool)($data['user_whitelist_active'] ?? false),
            'ip_whitelist_active' => (bool)($data['ip_whitelist_active'] ?? false),
        ];
    }

    /**
     * read a whitelist file into a list of entries, skipping blanks and # comments
     * @return string[]
     */
    private static function read_list(string $file): array
    {
        $out = [];
        if (!is_readable($file)) {
            return $out;
        }
        foreach (file($file) as $line) {
            $line = trim($line);
            if ($line !== '' && !str_starts_with($line, '#')) {
                $out[] = $line;
            }
        }
        return $out;
    }

    /** @param string[] $list */
    private static function ip_allowed(string $ip, array $list): bool
    {
        if ($ip === '') {
            return false;
        }
        $bin_ip = @inet_pton($ip);
        if ($bin_ip === false) {
            return false;
        }
        foreach ($list as $entry) {
            if ($entry === 'localhost') {
                $entry = str_contains($ip, ':') ? '::1' : '127.0.0.1';
            }
            if (self::ip_matches($bin_ip, $entry)) {
                return true;
            }
        }
        return false;
    }

    /** match a binary IP against a single IP or CIDR entry */
    private static function ip_matches(string $bin_ip, string $entry): bool
    {
        if (str_contains($entry, '/')) {
            [$subnet, $bits] = explode('/', $entry, 2);
            $bin_net = @inet_pton($subnet);
            if ($bin_net === false || strlen($bin_net) !== strlen($bin_ip)) {
                return false;
            }
            $bits = (int)$bits;
            $bytes = intdiv($bits, 8);
            $rem = $bits % 8;
            if ($bytes > 0 && substr($bin_ip, 0, $bytes) !== substr($bin_net, 0, $bytes)) {
                return false;
            }
            if ($rem === 0) {
                return true;
            }
            $mask = chr((0xff << (8 - $rem)) & 0xff);
            return (($bin_ip[$bytes] & $mask) === ($bin_net[$bytes] & $mask));
        }
        $bin_net = @inet_pton($entry);
        return $bin_net !== false && hash_equals($bin_net, $bin_ip);
    }

    /** @param string[] $list a user is allowed if their id or name is listed */
    private static function user_allowed(string $usr_id, string $usr_name, array $list): bool
    {
        foreach ($list as $entry) {
            if ($usr_id !== '' && $entry === $usr_id) {
                return true;
            }
            if ($usr_name !== '' && strcasecmp($entry, $usr_name) === 0) {
                return true;
            }
        }
        return false;
    }

    /** send the given offline page from the docroot and stop the request */
    private static function reject(string $rel_page): void
    {
        http_response_code(403);
        header('Content-Type: text/html; charset=utf-8');
        header('X-Robots-Tag: noindex, nofollow');
        $page = ROOT_PATH . $rel_page;
        if (is_readable($page)) {
            echo self::fill_admin_contact((string)file_get_contents($page));
        } else {
            echo 'Access denied.';
        }
        exit;
    }

    /**
     * replace the admin-contact placeholder in an offline page with the configured
     * email (SERVER_ADMIN_MAIL); if no email is set the placeholder is dropped so
     * the sentence stays grammatical.
     */
    private static function fill_admin_contact(string $html): string
    {
        $mail = defined('SERVER_ADMIN_MAIL') ? (string)\SERVER_ADMIN_MAIL : '';
        if ($mail !== '') {
            $safe = htmlspecialchars($mail, ENT_QUOTES, 'UTF-8');
            $snippet = ' at <a href="mailto:' . $safe . '">' . $safe . '</a>';
        } else {
            $snippet = '';
        }
        return str_replace(self::CONTACT_PLACEHOLDER, $snippet, $html);
    }
}