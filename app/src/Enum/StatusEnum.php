<?php

namespace App\Enum;

enum StatusEnum: string
{
    case OPEN = 'open';
    case BOOKED = 'booked';

    case APPROVED = 'approved';

    case PAID = 'paid';

    public function color(): string
    {
        $statuses = [
            self::OPEN->value => "green",
            self::BOOKED->value => "yellow",
            self::APPROVED->value => "blue",
            self::PAID->value => "purple",
        ];

        return $statuses[$this->value];
    }
}
