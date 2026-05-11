<?php

namespace App\Enums;

enum CaseStatus: string
{
    case Concept = 'concept';
    case Published = 'published';

    public function label(): string
    {
        return match ($this) {
            self::Concept => 'Concept',
            self::Published => 'Gepubliceerd',
        };
    }
}
