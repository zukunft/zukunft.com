<?php

/*

    cfg/helper/server_guard.php - enforce the tls, session and file based IP / user whitelist guards
    ---------------------------

    Called early from the backend api bootstrap (application->start_api) and from the html frontend
    bootstrap (frontend->start). It upgrades a plain-http request to https, hardens the session cookie,
    rejects a cross-origin api write (csrf) and, if the server admin page has activated a whitelist
    (server_admin/state.json), rejects requests that are not on the list by sending the matching
    offline page from /optional and stopping.

    It is deliberately self-contained (only the session and a few files, no database), so it still
    works while the database is offline and it adds almost no overhead when no whitelist is active.
    It lives in the backend (cfg/helper) so the api entry point does not depend on the web frontend;
    the frontend, which already depends on the backend, uses it from here.

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

namespace Zukunft\ZukunftCom\main\php\cfg\helper;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::SHARED . 'url_var.php';

use Zukunft\ZukunftCom\main\php\shared\url_var;

class server_guard
{
    // placeholder in the offline reject pages, replaced with the admin contact email at serve time
    private const string CONTACT_PLACEHOLDER = '{{ADMIN_CONTACT}}';

    // the loopback addresses that mark a server-to-server call on the same host (see from_own_pod)
    private const array LOOPBACK_ADDRESSES = ['127.0.0.1', '::1'];

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

    /**
     * true if the current request reaches this pod over https, directly or via a tls terminating
     * proxy; $_SERVER is read here because this is the request bootstrap, like enforce() above
     * @return bool true if the request scheme is https
     */
    public static function request_is_https(): bool
    {
        $https_flag = $_SERVER['HTTPS'] ?? '';
        $result = ($https_flag !== '' && $https_flag !== 'off')
            || ($_SERVER['SERVER_PORT'] ?? '') === '443'
            || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
        return $result;
    }

    /**
     * true if the given deployment environment must serve every request over https: the prod and
     * test pods enforce tls, the dev pod stays on plain http so the local docker setup keeps working
     * @param string $env the deployment level from the environment file (ENV_PROD, ENV_UA or ENV_DEV)
     * @return bool true for the prod and test environment, false for dev or an unknown value
     */
    public static function tls_required(string $env): bool
    {
        $result = $env === ENV_PROD || $env === ENV_UA;
        return $result;
    }

    /**
     * in the prod and test environment redirect a plain-http request to the same url over https so
     * the session cookie is never sent in the clear; the dev environment is intentionally left on
     * plain http so the local docker setup keeps working. must run before session_start (so no
     * cookie is sent yet) and before harden_session so the upgrade happens on the first hop.
     * $_SERVER is read here because this is the request bootstrap, like harden_session
     * @return void
     */
    public static function enforce_tls(): void
    {
        $env = getenv(ENVIRONMENT) ?: '';
        if (self::tls_required($env) && !self::request_is_https() && !headers_sent()) {
            $host = $_SERVER['HTTP_HOST'] ?? '';
            $uri = $_SERVER['REQUEST_URI'] ?? '';
            header('Location: https://' . $host . $uri, true, 301);
            exit;
        }
    }

    /**
     * harden the session cookie before the session is started: the cookie is set http-only (not
     * readable by javascript, so an xss cannot steal it), secure when the request uses tls (not
     * sent over plain http, so it cannot be sniffed) and same-site lax (not sent on a cross-site
     * request, a second layer against csrf); use_strict_mode rejects a planted id, and on a tls
     * request the hsts header pins the browser to https so a later downgrade is refused.
     * $_SERVER is read here (via request_is_https) because this is the request/session bootstrap -
     * the same place enforce() reads the remote address - not deep in the business logic
     */
    public static function harden_session(): void
    {
        $is_https = self::request_is_https();
        // must be set before session_start so the flags apply to the issued cookie
        ini_set('session.use_strict_mode', '1');
        session_set_cookie_params([
            'httponly' => true,
            'secure' => $is_https,
            'samesite' => 'Lax',
        ]);
        // pin future requests to https for a year (only meaningful, and only sent, over tls)
        if ($is_https && !headers_sent()) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
        // baseline security headers for every response (http and https): nosniff stops mime-type
        // confusion, SAMEORIGIN blocks clickjacking by framing the site cross-origin, and a strict
        // referrer policy keeps the full url from leaking to third parties. a Content-Security-Policy
        // is deliberately left out here until the inline styles are audited (see docs/llm/pending.md)
        if (!headers_sent()) {
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: SAMEORIGIN');
            header('Referrer-Policy: strict-origin-when-cross-origin');
        }
    }

    /**
     * true if a state changing api request (post / put / delete) may proceed with respect to csrf:
     * a browser always sends an Origin (or at least a Referer) on a cross-origin write, so a request
     * whose Origin/Referer host does not match the request Host is rejected. a server-to-server call
     * carries no browser cookie and sends neither header, so it is allowed. $_SERVER is read here
     * because this is the request boundary, like enforce_tls / enforce
     * @return bool true if the write request is same-origin (or a non-browser call)
     */
    public static function same_origin(): bool
    {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        return self::origin_allowed($origin, $referer, $host);
    }

    /**
     * true if the request has been sent by this pod itself, e.g. the html frontend calling its
     * own read api server-to-server for the browsing user, so the api may trust the data user
     * parameter (url_var::USER, see user::data_user): the remote address must be the loopback
     * address or the pod's own address and no proxy forward header may be present, because behind
     * a reverse proxy on the same host an external request would also arrive from the loopback
     * address but always carries the forward header of the proxy.
     * $_SERVER is read here because this is the request boundary, like same_origin / enforce
     * @return bool true if the request has been sent by this pod itself
     */
    public static function from_own_pod(): bool
    {
        $forwarded = isset($_SERVER['HTTP_X_FORWARDED_FOR']) || isset($_SERVER['HTTP_X_REAL_IP']);
        return self::is_own_pod_call(
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['SERVER_ADDR'] ?? '',
            $forwarded);
    }

    /**
     * pure own-pod decision: true if the remote address is the loopback address or the pod's own
     * address and the request has not been forwarded by a proxy
     * @param string $remote_ip the address the request has been sent from ('' if unknown)
     * @param string $server_ip the own address of this pod server ('' if unknown)
     * @param bool $forwarded true if the request carries a proxy forward header
     * @return bool true if the request has been sent by this pod itself
     */
    public static function is_own_pod_call(string $remote_ip, string $server_ip, bool $forwarded): bool
    {
        $result = false;
        if (!$forwarded and $remote_ip !== '') {
            if (in_array($remote_ip, self::LOOPBACK_ADDRESSES, true)) {
                $result = true;
            } elseif ($server_ip !== '' and $remote_ip === $server_ip) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * true if a state changing api request carries a valid per-session csrf token; this is the
     * synchronizer-token defense that complements same_origin() and closes its both-headers-absent
     * fail-open: a cross-site attacker's forged request auto-sends the session cookie but cannot read
     * the token (same-origin policy) nor set the custom X-CSRF-Token header (it would trigger a cors
     * preflight the attacker cannot satisfy). the token is the same $_SESSION[SESSION_TOKEN] the html
     * frontend already checks (see frontend::request_token_valid), issued at login; read here at the
     * request bootstrap like same_origin, compared with hash_equals. a request without a session token
     * (no login) fails closed, which is fine because a write needs a logged-in, non-blocked user anyway
     *
     * @return bool true if the request X-CSRF-Token header matches the session token
     */
    public static function csrf_token_valid(): bool
    {
        $sent_token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        $session_token = $_SESSION[url_var::SESSION_TOKEN] ?? '';
        return $session_token !== '' && hash_equals($session_token, $sent_token);
    }

    /**
     * pure same-origin decision for a state changing request: allowed unless an Origin (preferred,
     * else a Referer) is present and its host does not match the request Host (fail open only when
     * the browser sends no origin hint at all, e.g. a server-to-server call)
     * @param string $origin the request Origin header value ('' if absent)
     * @param string $referer the request Referer header value, used only when no Origin is sent
     * @param string $host the request Host header value to match the origin against
     * @return bool true if the request may change data
     */
    public static function origin_allowed(string $origin, string $referer, string $host): bool
    {
        $result = true;
        if ($origin !== '') {
            $result = self::url_host($origin) === $host;
        } elseif ($referer !== '') {
            $result = self::url_host($referer) === $host;
        }
        return $result;
    }

    /** the host[:port] part of a url, e.g. 'https://example.com:8080/x' -> 'example.com:8080' */
    private static function url_host(string $url): string
    {
        $host = parse_url($url, PHP_URL_HOST) ?? '';
        $port = parse_url($url, PHP_URL_PORT);
        if ($host !== '' && $port !== null) {
            $host .= ':' . $port;
        }
        return $host;
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