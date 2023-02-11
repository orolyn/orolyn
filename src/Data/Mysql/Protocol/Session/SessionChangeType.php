<?php

namespace Orolyn\Data\Mysql\Protocol\Session;

enum SessionChangeType: int
{
    case SessionTrackSystemVariables = 0;
    case SessionTrackSchema = 1;
    case SessionTrackStateChange = 2;
    case SessionTrackGtids = 3;
    case SessionTrackTransactionCharacteristics = 4;
    case SessionTrackTransactionState = 5;
}
