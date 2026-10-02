<?php

namespace App\Enums;

enum RecommendationStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Modified = 'modified';
    case Rejected = 'rejected';
    case Rescued = 'rescued';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'En attente',
            self::Accepted => 'Validée',
            self::Modified => 'Modifiée',
            self::Rejected => 'Rejetée',
            self::Rescued => 'Plan anti-gaspillage',
        };
    }
}
