<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The five permission categories required by the spec. Every permission
 * created via App\Helpers\PermissionName belongs to exactly one of these.
 */
enum PermissionType: string
{
    case Menu = 'menu';
    case Submenu = 'submenu';
    case Button = 'button';
    case Api = 'api';
    case Report = 'report';
}
