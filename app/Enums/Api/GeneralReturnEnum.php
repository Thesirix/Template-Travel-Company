<?php

namespace App\Enums\Api;

enum GeneralReturnEnum:string
{
    case success = '5001';
    case  error= '5002';
    case  unauthenticated= '5003';

}
