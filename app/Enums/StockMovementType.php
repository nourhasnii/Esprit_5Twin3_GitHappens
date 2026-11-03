<?php

namespace App\Enums;

enum StockMovementType: string
{
    case In = 'in';
    case Out = 'out';
    case Transfer = 'transfer';
    case Adjustment = 'adjustment';

    public function label(): string
    {
        return match ($this) {
            self::In => __('Entrée'),
            self::Out => __('Sortie'),
            self::Transfer => __('Transfert'),
            self::Adjustment => __('Ajustement'),
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::In => __('Réception de marchandise sur un site'),
            self::Out => __('Vente, consommation ou destruction'),
            self::Transfer => __('Déplacement d’un site vers un autre'),
            self::Adjustment => __('Correction après un inventaire physique'),
        };
    }
}
