<?php

namespace App\Enums;

enum SiteType: string
{
    case Production = 'production';
    case Warehouse = 'warehouse';
    case DistributionCenter = 'distribution_center';
    case Store = 'store';
    case Association = 'association';

    public function label(): string
    {
        return match ($this) {
            self::Production => 'Site de production',
            self::Warehouse => 'Entrepôt',
            self::DistributionCenter => 'Centre de distribution',
            self::Store => 'Magasin',
            self::Association => 'Association (dons)',
        };
    }

    /** Les associations reçoivent des dons : elles ne stockent pas et ne vendent pas. */
    public function isLogistic(): bool
    {
        return $this !== self::Association;
    }
}
