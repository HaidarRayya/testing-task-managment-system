<?php

namespace App\Enums;

enum TaskWorkStatus: string
{
    case IDLE  = 'idel';
    case ACTIVE = 'active';
    case FINISHED = 'finished';
}
