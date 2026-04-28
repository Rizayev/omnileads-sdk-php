<?php

declare(strict_types=1);

namespace Madtec\OmniLeads\Enums;

enum DayOfWeek: int
{
    case SUNDAY = 0;
    case MONDAY = 1;
    case TUESDAY = 2;
    case WEDNESDAY = 3;
    case THURSDAY = 4;
    case FRIDAY = 5;
    case SATURDAY = 6;

    public function label(): string
    {
        return match ($this) {
            self::SUNDAY => 'Воскресенье',
            self::MONDAY => 'Понедельник',
            self::TUESDAY => 'Вторник',
            self::WEDNESDAY => 'Среда',
            self::THURSDAY => 'Четверг',
            self::FRIDAY => 'Пятница',
            self::SATURDAY => 'Суббота',
        };
    }
}
