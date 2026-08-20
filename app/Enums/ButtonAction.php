<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The standard set of button-level actions every CRUD module exposes.
 * Combined with a module key (e.g. "users") to form a permission name,
 * e.g. "users.view", "users.export".
 */
enum ButtonAction: string
{
    case View = 'view';
    case Add = 'add';
    case Edit = 'edit';
    case Delete = 'delete';
    case Restore = 'restore';
    case Export = 'export';
    case Print = 'print';
    case Import = 'import';
    case Approve = 'approve';

    public function label(): string
    {
        return match ($this) {
            self::View => 'View',
            self::Add => 'Add',
            self::Edit => 'Edit',
            self::Delete => 'Delete',
            self::Restore => 'Restore',
            self::Export => 'Export',
            self::Print => 'Print',
            self::Import => 'Import',
            self::Approve => 'Approve',
        };
    }
}
