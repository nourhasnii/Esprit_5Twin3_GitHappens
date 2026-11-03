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
            self::Pending => __('En attente'),
            self::Accepted => __('Validée'),
            self::Modified => __('Modifiée'),
            self::Rejected => __('Rejetée'),
            self::Rescued => __('Plan anti-gaspillage'),
        };
    }
}
