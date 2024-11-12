<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN  = "admin";
    case DEVELOPER  = "developer";
    case TESTER  = "tester";
}
