<?php

declare(strict_types=1);

namespace App\Enums;

enum SmsChannel: string
{
    case Sms = 'sms';
    case WhatsApp = 'whatsapp';

    public function label(): string
    {
        return match ($this) {
            self::Sms => 'SMS',
            self::WhatsApp => 'WhatsApp',
        };
    }
}
