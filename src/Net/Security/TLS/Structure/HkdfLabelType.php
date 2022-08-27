<?php

namespace Orolyn\Net\Security\TLS\Structure;

enum HkdfLabelType: string
{
    case EXT_BINDER     = 'tls13 ext binder';
    case RES_BINDER     = 'tls13 res binder';
    case C_E_TRAFFIC    = 'tls13 c e traffic';
    case E_EXP_MASTER   = 'tls13 e exp master';
    case C_HS_TRAFFIC   = 'tls13 c hs traffic';
    case S_HS_TRAFFIC   = 'tls13 s hs traffic';
    case C_AP_TRAFFIC   = 'tls13 c ap traffic';
    case S_AP_TRAFFIC   = 'tls13 s ap traffic';
    case EXP_MASTER     = 'tls13 exp master';
    case RES_MASTER     = 'tls13 res master';
    case DERIVED        = 'tls13 derived';
    case KEY            = 'tls13 key';
    case IV             = 'tls13 iv';
}
