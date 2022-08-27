<?php

(function () {
    if (false === $defaultServer = getenv('OROLYN_DEFAULT_DNS')) {
        $defaultServer = '8.8.8.8';

        if (file_exists($conf = '/etc/resolv.conf')) {
            if (preg_match('/\bnameserver\s*([\d\.]+)\b/', file_get_contents($conf), $match)) {
                if (false !== inet_pton($match[1])) {
                    $defaultServer = $match[1];
                }
            }
        }
    }

    define('__OROLYN_DEFAULT_DNS__', $defaultServer);
})();
