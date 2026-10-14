<?php

namespace App\Enums\Api;

enum OtpStatusEnum: string
{
    case OtpGenerated = '2001';
    case  Expired = '2002';
    case  Invalid = '2003';
    case ClientExist = '2004';
    case  AccountCreated = '2005';
    case  Identical = '2006';
    case  MAX_ATTEMPTS = '2007';
    case  MAX_GENERATED = '2008';
    case ClientNoExist = '2009';

}
