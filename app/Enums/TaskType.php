<?php

namespace App\Enums;

enum TaskType: string
{
    case BUG  = "bug";
    case FEATURE = "feature";
    case IMPROVEMNT  = "improvement";
}
