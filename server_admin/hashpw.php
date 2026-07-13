<?php

/*

    server_admin/hashpw.php - bcrypt password hash helper for the server admin page
    -----------------------

    The restricted server admins (SERVER_ADMIN_2 / SERVER_ADMIN_3) log in with a
    username and password; the password is stored in .env as a bcrypt hash.
    This CLI helper turns a password into that hash.

    Usage:
      php server_admin/hashpw.php
          prompt for a password (input hidden) and print its bcrypt hash
          for SERVER_ADMIN_2_PW / SERVER_ADMIN_3_PW in .env

    This file is part of zukunft.com - calc with words, GNU AGPL v3, see
    <http://www.gnu.org/licenses/agpl.html>. Timon Zielonka <timon@zukunft.com>

*/

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

// read the password from a prompt with echo turned off, never from argv
fwrite(STDERR, "password: ");
$tty = @shell_exec('stty -g 2>/dev/null');
if ($tty !== null && $tty !== '') {
    shell_exec('stty -echo 2>/dev/null');
}
$pw = rtrim((string)fgets(STDIN), "\r\n");
if ($tty !== null && $tty !== '') {
    shell_exec('stty ' . escapeshellarg(trim($tty)) . ' 2>/dev/null');
    fwrite(STDERR, "\n");
}
if ($pw === '') {
    fwrite(STDERR, "empty password - aborting\n");
    exit(1);
}

$hash = password_hash($pw, PASSWORD_BCRYPT);
if (str_contains($hash, '=')) {
    // bcrypt never produces '=', but guard the .env parser just in case
    fwrite(STDERR, "unexpected '=' in hash - not safe for .env\n");
    exit(1);
}
echo "add to .env (SERVER_ADMIN_2_PW or SERVER_ADMIN_3_PW):\n\n";
echo $hash . "\n";