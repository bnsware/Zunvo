<?php

function send_mail_message($to, $subject, $message) {
    if (defined('MAIL_HOST') && MAIL_HOST && MAIL_HOST !== 'smtp.gmail.com') {
        return send_smtp_mail($to, $subject, $message);
    }
    return send_email($to, $subject, $message);
}

function send_smtp_mail($to, $subject, $message) {
    $socket = @fsockopen(MAIL_HOST, MAIL_PORT, $errno, $errstr, 10);
    if (!$socket) {
        return send_email($to, $subject, $message);
    }
    $read = function() use ($socket) {
        return fgets($socket, 512);
    };
    $write = function($cmd) use ($socket) {
        fputs($socket, $cmd . "\r\n");
    };
    $read();
    $write('EHLO localhost');
    $read();
    if (MAIL_ENCRYPTION === 'tls') {
        $write('STARTTLS');
        $read();
        stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        $write('EHLO localhost');
        $read();
    }
    if (MAIL_USERNAME) {
        $write('AUTH LOGIN');
        $read();
        $write(base64_encode(MAIL_USERNAME));
        $read();
        $write(base64_encode(MAIL_PASSWORD));
        $read();
    }
    $write('MAIL FROM:<' . MAIL_FROM_EMAIL . '>');
    $read();
    $write('RCPT TO:<' . $to . '>');
    $read();
    $write('DATA');
    $read();
    $headers = "From: " . MAIL_FROM_NAME . " <" . MAIL_FROM_EMAIL . ">\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $body = "Subject: {$subject}\r\n{$headers}\r\n{$message}\r\n.";
    $write($body);
    $read();
    $write('QUIT');
    fclose($socket);
    return true;
}
