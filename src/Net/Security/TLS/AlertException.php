<?php

namespace Orolyn\Net\Security\TLS;

use Exception;
use Orolyn\Net\Security\TLS\Structure\Alert;
use Orolyn\Net\Security\TLS\Structure\AlertLevel;
use Throwable;

abstract class AlertException extends Exception
{
    /**
     * @param Alert $alert
     * @param Throwable|null $previous
     */
    final private function __construct(Alert $alert, ?Throwable $previous = null)
    {
        parent::__construct($alert->alertDescription->name, $alert->alertDescription->value, $previous);
    }

    /**
     * @param Alert $alert
     * @return AlertException
     */
    public static function create(Alert $alert): AlertException
    {
        return match ($alert->alertLevel) {
            AlertLevel::Fatal => new AlertFatalException($alert),
            AlertLevel::Warning => new AlertWarningException($alert)
        };
    }
}
