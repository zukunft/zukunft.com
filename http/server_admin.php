<?php

/*

    server_admin.php - protected server administration page
    ----------------

    A deliberately self-contained admin page: it does NOT boot the app or the
    database, because one of its jobs is to upgrade the database while it is
    offline. It reads only the .env file and the server_admin/ state folder.

    Access is protected by:
      1. the client IP must be in SERVER_ADMIN_IP
      2. a fixed username + password (bcrypt hash) per admin from .env, plus an
         optional per-admin client IP range (SERVER_ADMIN_x_IP_FROM/TO)

    Functions:
      - activate / deactivate the file based user whitelist (else db blacklist)
      - activate / deactivate the file based IP  whitelist (else db blacklist)
      - update the program   (git) via script/server_admin.sh update-program
      - upgrade the database       via script/server_admin.sh upgrade-database


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

declare(strict_types=1);

const ROOT = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
const ADMIN_DIR = ROOT . 'server_admin' . DIRECTORY_SEPARATOR;
const STATE_FILE = ADMIN_DIR . 'state.json';
const USER_WL_FILE = ADMIN_DIR . 'user_whitelist.txt';
const IP_WL_FILE = ADMIN_DIR . 'ip_whitelist.txt';
const ADMIN_SCRIPT = ROOT . 'script' . DIRECTORY_SEPARATOR . 'server_admin.sh';
const ENV_FILE = ROOT . '.env';
// the program version is part of the release, so it is not in .env but in the version file
const VERSION_FILE = ROOT . 'version.txt';

// idle timeout for an authenticated session, in seconds
const SESSION_TTL = 1800;


/* --------------------------------------------------------------------------
 *  tiny helpers (no app / db dependency on purpose)
 * ------------------------------------------------------------------------ */

/**
 * minimal .env reader, same comment handling as src/main/php/cfg/const/env.php
 * @return array<string,string>
 */
function read_env(string $file): array
{
    $out = [];
    if (!is_readable($file)) {
        return $out;
    }
    foreach (file($file) as $line) {
        if (str_starts_with($line, '#') || trim($line) === '') {
            continue;
        }
        if (str_contains($line, '#')) {
            $line = substr($line, 0, strpos($line, '#'));
        }
        $line = rtrim($line, "\r\n");
        $pos = strpos($line, '=');
        if ($pos === false) {
            continue;
        }
        $out[trim(substr($line, 0, $pos))] = trim(substr($line, $pos + 1));
    }
    return $out;
}

/**
 * build the list of configured server admins from the parsed .env
 * - 'admin'           : full access admin (may switch the IP whitelist off)
 * - 'admin2'/'admin3' : restricted admins, may not switch the IP whitelist off
 * all log in with a fixed username + password and an optional inclusive client IP range
 * @param array<string,string> $env
 * @return array<int,array{id:string,username:string,pw_hash:string,ip_from:string,ip_to:string,restricted:bool}>
 */
function read_admins(array $env): array
{
    $out = [];
    $defs = [
        ['id' => 'admin', 'user' => 'SERVER_ADMIN_USER', 'pw' => 'SERVER_ADMIN_PW',
            'from' => '', 'to' => '', 'restricted' => false],
        ['id' => 'admin2', 'user' => 'SERVER_ADMIN_2_USER', 'pw' => 'SERVER_ADMIN_2_PW',
            'from' => 'SERVER_ADMIN_2_IP_FROM', 'to' => 'SERVER_ADMIN_2_IP_TO', 'restricted' => true],
        ['id' => 'admin3', 'user' => 'SERVER_ADMIN_3_USER', 'pw' => 'SERVER_ADMIN_3_PW',
            'from' => 'SERVER_ADMIN_3_IP_FROM', 'to' => 'SERVER_ADMIN_3_IP_TO', 'restricted' => true],
    ];
    foreach ($defs as $d) {
        $username = trim($env[$d['user']] ?? '');
        $pw_hash = trim($env[$d['pw']] ?? '');
        if ($username !== '' && $pw_hash !== '') {
            $out[] = [
                'id' => $d['id'],
                'username' => $username,
                'pw_hash' => $pw_hash,
                'ip_from' => $d['from'] === '' ? '' : trim($env[$d['from']] ?? ''),
                'ip_to' => $d['to'] === '' ? '' : trim($env[$d['to']] ?? ''),
                'restricted' => $d['restricted'],
            ];
        }
    }
    return $out;
}

/** the real client IP; only REMOTE_ADDR is trusted for the security gate */
function client_ip(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? '';
}

/** true if $ip matches one of the comma separated IPs / CIDR ranges in $list */
function ip_allowed(string $ip, string $list): bool
{
    if ($ip === '' || trim($list) === '') {
        return false;
    }
    $bin_ip = @inet_pton($ip);
    if ($bin_ip === false) {
        return false;
    }
    foreach (preg_split('/[\s,]+/', trim($list), -1, PREG_SPLIT_NO_EMPTY) as $entry) {
        if ($entry === 'localhost') {
            $entry = str_contains($ip, ':') ? '::1' : '127.0.0.1';
        }
        if (ip_matches($bin_ip, $entry)) {
            return true;
        }
    }
    return false;
}

/** match a binary IP against a single IP or CIDR entry */
function ip_matches(string $bin_ip, string $entry): bool
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

/** true if $ip is within the inclusive range [$from, $to] (same IP family) */
function ip_in_range(string $ip, string $from, string $to): bool
{
    $bin = @inet_pton($ip);
    $bin_from = @inet_pton($from);
    $bin_to = @inet_pton($to);
    if ($bin === false || $bin_from === false || $bin_to === false) {
        return false;
    }
    if (strlen($bin) !== strlen($bin_from) || strlen($bin) !== strlen($bin_to)) {
        return false;
    }
    // strcmp compares the packed (network byte order) addresses byte by byte
    return strcmp($bin, $bin_from) >= 0 && strcmp($bin, $bin_to) <= 0;
}

/** true if the caller may log in as $admin: no range configured, or client IP within it */
function admin_ip_allowed(array $admin): bool
{
    $from = $admin['ip_from'] ?? '';
    $to = $admin['ip_to'] ?? '';
    if ($from === '' || $to === '') {
        return true;
    }
    return ip_in_range(client_ip(), $from, $to);
}

/** @return array{user_whitelist_active:bool,ip_whitelist_active:bool} */
function read_state(): array
{
    $default = ['user_whitelist_active' => false, 'ip_whitelist_active' => false];
    if (!is_readable(STATE_FILE)) {
        return $default;
    }
    $data = json_decode((string)file_get_contents(STATE_FILE), true);
    if (!is_array($data)) {
        return $default;
    }
    return [
        'user_whitelist_active' => (bool)($data['user_whitelist_active'] ?? false),
        'ip_whitelist_active' => (bool)($data['ip_whitelist_active'] ?? false),
    ];
}

function write_state(array $state): bool
{
    $json = json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    return @file_put_contents(STATE_FILE, $json, LOCK_EX) !== false;
}

/** count non-comment, non-empty lines in a whitelist file */
function whitelist_count(string $file): int
{
    if (!is_readable($file)) {
        return 0;
    }
    $n = 0;
    foreach (file($file) as $line) {
        $line = trim($line);
        if ($line !== '' && !str_starts_with($line, '#')) {
            $n++;
        }
    }
    return $n;
}

/** run a server_admin.sh sub command via sudo and capture the combined output */
function run_admin_script(string $action): array
{
    $cmd = 'sudo -n ' . escapeshellarg(ADMIN_SCRIPT) . ' ' . escapeshellarg($action) . ' 2>&1';
    $out = [];
    $code = 0;
    exec($cmd, $out, $code);
    return [implode("\n", $out), $code];
}

/**
 * turn a failed admin script run into an actionable error for the (already authenticated) admin
 * surfaces the last output line - which is also shown in full in the Output box below the forms,
 * so this exposes nothing the admin cannot already see - and names the most common cause when the
 * command produced no output at all (typically sudo is not permitted for the web server user)
 * @param string $label the action shown to the admin e.g. 'Program update'
 * @param string $output the combined stdout/stderr captured from the script
 * @param int $code the non-zero exit code returned by the script
 * @return string the error message to show above the form
 */
function run_error(string $label, string $output, int $code): string
{
    $lines = array_filter(array_map('trim', explode("\n", $output)), fn($line) => $line !== '');
    $last = $lines === [] ? '' : (string)end($lines);
    if ($last === '') {
        $result = $label . ' could not be started (exit code ' . $code . '); the web server user is '
            . 'likely not permitted to run "sudo -n ' . basename(ADMIN_SCRIPT) . '" without a password.';
    } else {
        $result = $label . ' failed (exit code ' . $code . '): ' . $last;
    }
    return $result;
}

function h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

/** shared page shell opening: doctype, head with the app stylesheets, and the logo */
function page_head(string $title): string
{
    return '<!DOCTYPE html>' . "\n"
        . '<html lang="en">' . "\n"
        . '<head>' . "\n"
        . '<meta charset="utf-8">' . "\n"
        . '<meta name="viewport" content="width=device-width, initial-scale=1.0">' . "\n"
        . '<meta name="robots" content="noindex,nofollow">' . "\n"
        . '<title>' . h($title) . ' - zukunft.com</title>' . "\n"
        . '<link rel="stylesheet" href="/external_lib/bootstrap/css/bootstrap.css">' . "\n"
        . '<link rel="stylesheet" href="/external_lib/fontawesome/css/all.css">' . "\n"
        . '<link rel="stylesheet" href="/src/main/resources/style/style_html.css">' . "\n"
        . '</head>' . "\n"
        . '<body>' . "\n"
        . '<main class="main-container">' . "\n"
        . '<div class="logo-section">'
        . '<a href="/http/view.php" title="zukunft.com" class="navbar-brand">'
        . '<img src="/src/main/resources/images/ZUKUNFT_logo.svg" alt="zukunft.com" class="brand-logo" style="height:8rem;width:auto;"></a>'
        . '</div>' . "\n";
}

/** shared page shell closing: the standard footer with the legal links */
function page_foot(): string
{
    return '</main>' . "\n"
        . '<footer class="site-footer"><p>'
        . '<a href="/http/about.php">About</a> &middot; '
        . '<a href="/http/privacy_policy.html">Privacy Policy</a> &middot; '
        . 'All structured data is available under the '
        . '<a href="https://creativecommons.org/publicdomain/zero/1.0/" title="CC0 License">Creative Commons CC0</a> '
        . 'Licence unless otherwise stated and the '
        . '<a href="https://github.com/zukunft/zukunft.com">program code</a> '
        . 'under the <a href="https://www.gnu.org/licenses/agpl.html">AGPL3</a> Licence.'
        . '</p></footer>' . "\n"
        . '</body></html>';
}

/** the pod name and url from .env, shown as a subtitle under the "Server admin" heading */
function pod_subtitle(string $pod_name, string $pod_url): string
{
    $html = '<p class="subtitle"><strong>' . h($pod_name) . '</strong>';
    if ($pod_url !== '') {
        $html .= ' &middot; <a href="' . h($pod_url) . '" target="_blank" rel="noopener noreferrer">'
            . h($pod_url) . '</a>';
    }
    return $html . '</p>';
}


/* --------------------------------------------------------------------------
 *  bootstrap: session, config, gates
 * ------------------------------------------------------------------------ */

session_name('zu_srv_admin');
session_start();

$env = read_env(ENV_FILE);
// the program version is not in .env but in the version file, because it is part of the release
$version = read_env(VERSION_FILE);
$admins = read_admins($env);
$ip_list = trim($env['SERVER_ADMIN_IP'] ?? '');

// the pod this page administers, shown next to the heading
$pod_name = $env['POD_NAME'] ?? '';
$pod_url = $env['THIS_URL'] ?? '';

// gate 1: IP allowlist - a rejected caller must not even learn the page exists
if (!ip_allowed(client_ip(), $ip_list)) {
    http_response_code(404);
    exit;
}

// the page is only usable once at least one admin username + password is configured
if ($admins === []) {
    http_response_code(503);
    header('Content-Type: text/html; charset=utf-8');
    header('X-Robots-Tag: noindex, nofollow');
    echo page_head('Server admin');
    echo '<h4>Server admin</h4>';
    echo pod_subtitle($pod_name, $pod_url);
    echo '<div class="alert alert-danger">The server admin page is not configured. '
        . 'Set a <code>SERVER_ADMIN_USER</code> + <code>SERVER_ADMIN_PW</code> pair in '
        . '<code>.env</code> (hash the password with <code>php server_admin/hashpw.php</code>).</div>';
    echo page_foot();
    exit;
}

// expire an idle authenticated session
if (!empty($_SESSION['srv_admin_authed'])
    && ($_SESSION['srv_admin_seen'] ?? 0) < time() - SESSION_TTL) {
    $_SESSION = [];
}
$authed = !empty($_SESSION['srv_admin_authed']);
$restricted = !empty($_SESSION['srv_admin_restricted']);
if ($authed) {
    $_SESSION['srv_admin_seen'] = time();
}

$notice = '';
$error = '';
$output = '';


/* --------------------------------------------------------------------------
 *  POST handling
 * ------------------------------------------------------------------------ */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        // match the username, verify the password, and check the client IP is
        // within the admin's configured range (if any)
        $username = trim($_POST['username'] ?? '');
        $password = (string)($_POST['password'] ?? '');

        $matched = null;
        if ($username !== '') {
            foreach ($admins as $a) {
                if (strcasecmp($a['username'], $username) === 0
                    && password_verify($password, $a['pw_hash'])
                    && admin_ip_allowed($a)) {
                    $matched = $a;
                    break;
                }
            }
        }
        if ($matched !== null) {
            // prevent session fixation
            session_regenerate_id(true);
            $_SESSION['srv_admin_authed'] = true;
            $_SESSION['srv_admin_id'] = $matched['id'];
            $_SESSION['srv_admin_restricted'] = $matched['restricted'];
            $_SESSION['srv_admin_seen'] = time();
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
            $authed = true;
            $restricted = $matched['restricted'];
            $notice = 'Welcome, ' . $matched['id'] . ($matched['restricted'] ? ' (restricted admin).' : '.');
        } else {
            // do not reveal which factor failed
            $error = 'Invalid credentials.';
            usleep(500000);
        }
    } elseif ($action === 'logout') {
        $_SESSION = [];
        session_regenerate_id(true);
        $authed = false;
        $notice = 'Logged out.';
    } elseif ($authed) {
        // all authenticated actions require a valid CSRF token
        if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
            $error = 'Invalid or missing CSRF token - action ignored.';
        } else {
            $state = read_state();
            switch ($action) {
                case 'toggle_user_wl':
                    $state['user_whitelist_active'] = !$state['user_whitelist_active'];
                    $notice = write_state($state)
                        ? 'User whitelist ' . ($state['user_whitelist_active'] ? 'activated.' : 'deactivated (database blacklist applies).')
                        : '';
                    if ($notice === '') {
                        $error = 'Could not write ' . STATE_FILE . ' - check permissions.';
                    }
                    break;
                case 'toggle_ip_wl':
                    // restricted admins may not reduce the IP whitelist to the database blacklist
                    if ($restricted && $state['ip_whitelist_active']) {
                        $error = 'Your admin role may not switch the IP whitelist off (whitelist -> blacklist).';
                        break;
                    }
                    $state['ip_whitelist_active'] = !$state['ip_whitelist_active'];
                    $notice = write_state($state)
                        ? 'IP whitelist ' . ($state['ip_whitelist_active'] ? 'activated.' : 'deactivated (database blacklist applies).')
                        : '';
                    if ($notice === '') {
                        $error = 'Could not write ' . STATE_FILE . ' - check permissions.';
                    } elseif ($state['ip_whitelist_active'] && whitelist_count(IP_WL_FILE) === 0) {
                        $error = 'Warning: the IP whitelist is active but empty - every visitor is locked out of the '
                            . 'main app. Add entries to server_admin/ip_whitelist.txt now.';
                    }
                    break;
                case 'update_program':
                    [$output, $code] = run_admin_script('update-program');
                    $notice = $code === 0 ? 'Program update finished.' : '';
                    if ($code !== 0) {
                        $error = run_error('Program update', $output, $code);
                    }
                    break;
                case 'upgrade_database':
                    [$output, $code] = run_admin_script('upgrade-database');
                    $notice = $code === 0 ? 'Database upgrade finished.' : '';
                    if ($code !== 0) {
                        $error = run_error('Database upgrade', $output, $code);
                    }
                    break;
                default:
                    $error = 'Unknown action.';
            }
        }
    } else {
        $error = 'Please authenticate first.';
    }
}

$state = read_state();
$csrf = $_SESSION['csrf'] ?? '';

// the database name depends on the configured database type
$db_type = $env['DB'] ?? '';
$db_name = $db_type === 'MySQL' ? ($env['MYSQL_DATABASE'] ?? '') : ($env['PGSQL_DATABASE'] ?? '');

// the git repository the program updates are pulled from
$repo_url = $env['SOURCE_REPO_URL'] ?? '';
if ($repo_url === '') {
    $repo_url = 'https://github.com/zukunft/zukunft.com';
}

header('Content-Type: text/html; charset=utf-8');
header('X-Robots-Tag: noindex, nofollow');
echo page_head('Server admin');
?>

    <h4>Server admin</h4>
    <?= pod_subtitle($pod_name, $pod_url) ?>

    <?php if ($notice !== ''): ?><div class="alert alert-success"><?= h($notice) ?></div><?php endif; ?>
    <?php if ($error !== ''): ?><div class="alert alert-danger"><?= h($error) ?></div><?php endif; ?>

    <?php if (!$authed): ?>

        <div class="search-section">
            <form method="post">
                <input type="hidden" name="action" value="login">
                username<br>
                <input type="text" name="username" class="standard-input" autocomplete="username" autofocus><br><br>
                password<br>
                <input type="password" name="password" class="standard-input" autocomplete="current-password"><br><br>
                <input type="submit" value="Log in" class="submit-input">
            </form>
        </div>

    <?php else: ?>

        <p class="subtitle">Signed in as
            <strong><?= h($_SESSION['srv_admin_id'] ?? '') ?></strong><?= $restricted ? ' (restricted)' : '' ?>
            from <?= h(client_ip()) ?> &middot;
            <span class="auth-links"><form method="post" style="display:inline">
                <input type="hidden" name="action" value="logout">
                <input type="submit" value="log out" class="btn">
            </form></span>
        </p>

        <h5>Access control</h5>

        <p>
            User whitelist:
            <strong class="<?= $state['user_whitelist_active'] ? 'text-success' : 'text-muted' ?>"><?=
                $state['user_whitelist_active'] ? 'active' : 'inactive' ?></strong><br>
            <?php if ($state['user_whitelist_active']): ?>
                only the <?= whitelist_count(USER_WL_FILE) ?> user(s) in
                <code>server_admin/user_whitelist.txt</code> may use the pod.
            <?php else: ?>
                the database based blacklist applies.
            <?php endif; ?>
        </p>
        <form method="post">
            <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
            <input type="hidden" name="action" value="toggle_user_wl">
            <input type="submit" class="btn" value="<?= $state['user_whitelist_active'] ? 'Deactivate' : 'Activate' ?> user whitelist">
        </form>

        <hr>

        <p>
            IP whitelist:
            <strong class="<?= $state['ip_whitelist_active'] ? 'text-success' : 'text-muted' ?>"><?=
                $state['ip_whitelist_active'] ? 'active' : 'inactive' ?></strong><br>
            <?php if ($state['ip_whitelist_active']): ?>
                only the <?= whitelist_count(IP_WL_FILE) ?> address(es) in
                <code>server_admin/ip_whitelist.txt</code> may connect.
            <?php else: ?>
                the database based blacklist applies.
            <?php endif; ?>
        </p>
        <?php if ($state['ip_whitelist_active'] && whitelist_count(IP_WL_FILE) === 0): ?>
            <p class="text-danger">Empty whitelist: every visitor is locked out of the main app.
                Add entries to <code>server_admin/ip_whitelist.txt</code>.</p>
        <?php endif; ?>
        <?php if ($restricted && $state['ip_whitelist_active']): ?>
            <p class="subtitle">Your admin role may not switch the IP whitelist off.</p>
        <?php else: ?>
            <form method="post">
                <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
                <input type="hidden" name="action" value="toggle_ip_wl">
                <input type="submit" class="btn" value="<?= $state['ip_whitelist_active'] ? 'Deactivate' : 'Activate' ?> IP whitelist">
            </form>
        <?php endif; ?>

        <hr>

        <h5>Maintenance</h5>
        <p class="subtitle">These put an offline fallback page live, run the operation, then restore the site.</p>

        <p>
            environment <strong><?= h($env['ENV'] ?? '') ?></strong> &middot;
            branch <strong><?= h($env['BRANCH'] ?? '') ?></strong> &middot;
            code version <strong><?= h($version['CODE_VERSION'] ?? '') ?></strong> &middot;
            ui version <strong><?= h($version['UI_VERSION'] ?? '') ?></strong>
        </p>
        <p>
            source repository <a href="<?= h($repo_url) ?>" target="_blank" rel="noopener noreferrer"><?= h($repo_url) ?></a>
        </p>
        <form method="post" onsubmit="return confirm('Update the program now? The site shows a maintenance page while it runs.');">
            <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
            <input type="hidden" name="action" value="update_program">
            <input type="submit" class="btn" value="Update program">
        </form>
        <br>
        <p>
            database type <strong><?= h($db_type) ?></strong> &middot;
            database name <strong><?= h($db_name) ?></strong>
        </p>
        <form method="post" onsubmit="return confirm('Upgrade the database now? The site shows a maintenance page while it runs.');">
            <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
            <input type="hidden" name="action" value="upgrade_database">
            <input type="submit" class="btn" value="Upgrade database">
        </form>

        <?php if ($output !== ''): ?>
            <h5>Output</h5>
            <pre class="text-left"><?= h($output) ?></pre>
        <?php endif; ?>

    <?php endif; ?>

<?php echo page_foot(); ?>