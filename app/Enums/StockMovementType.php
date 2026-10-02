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
            self::In => 'Entrée',
            self::Out => 'Sortie',
            self::Transfer => 'Transfert',
            self::Adjustment => 'Ajustement',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::In => 'Réception de marchandise sur un site',
            self::Out => 'Vente, consommation ou destruction',
            self::Transfer => 'Déplacement d’un site vers un autre',
            self::Adjustment => 'Correction après un inventaire physique',
        };
    }
}
