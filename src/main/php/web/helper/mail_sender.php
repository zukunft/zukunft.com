<?php

/*

    web/helper/mail_sender.php - send the mails of the pod e.g. the signup confirmation
    --------------------------

    sends a plain text mail via the smtp email account of the .env (SIGNUP_MAIL_*) with tls
    required, or via the local sendmail of php mail() if no smtp host is set; curl is used for
    smtp, because it handles the tls and the login and is already required by the pod
    var name: $mail


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

namespace Zukunft\ZukunftCom\main\php\web\helper;

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::SHARED_CONST . 'users.php';
include_once html_paths::USER . 'user_message.php';

use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\main\php\web\user\user_message;

class mail_sender
{

    // the port of the smtp server that expects tls from the start instead of starttls
    const int SMTP_PORT_IMPLICIT_TLS = 465;
    const string SMTP_SCHEME = 'smtp';
    const string SMTPS_SCHEME = 'smtps';
    // the seconds after which a mail server that does not answer is given up
    const int SMTP_TIMEOUT = 30;
    // the line end that a mail protocol expects
    const string CRLF = "\r\n";


    /**
     * send a plain text mail via the smtp account of the .env or, without a smtp host, via php mail()
     *
     * @param string $to the email address of the receiver e.g. the email of a new account
     * @param string $subject the subject in utf-8, which is encoded for the mail header here
     * @param string $body the plain text of the mail in utf-8
     * @param user_message $msg to report why the mail could not be sent, which the user cannot fix
     * @return bool true if the mail server has accepted the mail
     */
    function send(string $to, string $subject, string $body, user_message $msg): bool
    {
        if (!self::header_safe($to . $subject)) {
            log_err_msg_ui('mail refused because the address or the subject contains a line break', $msg);
            return false;
        }
        $subject_enc = mb_encode_mimeheader($subject, 'UTF-8', 'Q');
        if (SIGNUP_MAIL_HOST == '') {
            $result = mail($to, $subject_enc, $body, users::mail_header());
        } else {
            $result = $this->send_smtp($to, $subject_enc, $body, $msg);
        }
        return $result;
    }

    /**
     * @param string $to the email address of the receiver
     * @param string $subject_enc the subject already encoded for the mail header
     * @param string $body the plain text of the mail
     * @param user_message $msg to report why the smtp server has not accepted the mail
     * @return bool true if the smtp server has accepted the mail
     */
    private function send_smtp(string $to, string $subject_enc, string $body, user_message $msg): bool
    {
        $from = self::sender(SIGNUP_MAIL);
        // a mail without a stream to read it from would let curl upload nothing
        $stream = fopen('php://temp', 'r+');
        if ($stream === false) {
            log_err_msg_ui('the mail cannot be created, because no temporary stream is available', $msg);
            $result = false;
        } else {
            fwrite($stream, self::smtp_message($from, $to, $subject_enc, $body));
            rewind($stream);
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => self::smtp_url(SIGNUP_MAIL_HOST, SIGNUP_MAIL_PORT),
                // never send the password or the mail without tls
                CURLOPT_USE_SSL => CURLUSESSL_ALL,
                CURLOPT_USERNAME => SIGNUP_MAIL_USER,
                CURLOPT_PASSWORD => SIGNUP_MAIL_PW,
                CURLOPT_MAIL_FROM => '<' . $from . '>',
                CURLOPT_MAIL_RCPT => ['<' . $to . '>'],
                CURLOPT_UPLOAD => true,
                CURLOPT_INFILE => $stream,
                CURLOPT_TIMEOUT => self::SMTP_TIMEOUT,
            ]);
            $result = curl_exec($curl) !== false;
            if (!$result) {
                // the receiver is not logged, because the log is shown to the admins
                log_err_msg_ui('the mail could not be sent via ' . SIGNUP_MAIL_HOST . ': ' . curl_error($curl), $msg);
            }
            curl_close($curl);
            fclose($stream);
        }
        return $result;
    }

    /**
     * @param string $txt a text of the mail header e.g. the address of the receiver or the subject
     * @return bool false if the text has a line break, which would add a header (mail header injection)
     */
    static function header_safe(string $txt): bool
    {
        return preg_match('/[\r\n]/', $txt) !== 1;
    }

    /**
     * @param string $host the smtp server e.g. smtp.example.com
     * @param int $port 465 for tls from the start, any other port e.g. 587 for starttls
     * @return string the curl url of the smtp server e.g. 'smtp://smtp.example.com:587'
     */
    static function smtp_url(string $host, int $port): string
    {
        $scheme = $port == self::SMTP_PORT_IMPLICIT_TLS ? self::SMTPS_SCHEME : self::SMTP_SCHEME;
        return $scheme . '://' . $host . ':' . $port;
    }

    /**
     * @param string $signup_mail the sender address of the .env
     * @return string the sender address or the admin address of the pod if the .env has none
     */
    static function sender(string $signup_mail): string
    {
        return $signup_mail != '' ? $signup_mail : users::SYSTEM_ADMIN_EMAIL;
    }

    /**
     * the mail as the smtp server expects it: the header lines, an empty line and the text, all with
     * crlf line ends; a line that starts with a dot is escaped by curl
     *
     * @param string $from the sender address
     * @param string $to the email address of the receiver
     * @param string $subject_enc the subject already encoded for the mail header
     * @param string $body the plain text of the mail
     * @return string the complete mail
     */
    static function smtp_message(string $from, string $to, string $subject_enc, string $body): string
    {
        $header = [
            'From: ' . $from,
            'To: ' . $to,
            'Subject: ' . $subject_enc,
            'Date: ' . date(DATE_RFC2822),
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
        ];
        $text = str_replace(self::CRLF, "\n", $body);
        $text = str_replace("\n", self::CRLF, $text);
        return implode(self::CRLF, $header) . self::CRLF . self::CRLF . $text . self::CRLF;
    }

}
