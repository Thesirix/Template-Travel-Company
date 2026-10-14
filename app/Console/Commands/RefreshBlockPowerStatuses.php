<?php

namespace App\Console\Commands;

use App\Models\BlockPowerOption;
use Illuminate\Console\Command;

/**
 * Allotements : « bientôt échue » à l'approche de la date limite, « expirée » une fois celle-ci passée
 * (seulement pour les options encore en vente ou nominatives, pas pour celles confirmées).
 */
class RefreshBlockPowerStatuses extends Command
{
    protected $signature = 'block-power:refresh';

    protected $description = 'Marque les allotements bientôt échues ou expirées';

    public function handle(): int
    {
        $expired = BlockPowerOption::inStatus(BlockPowerOption::ACTIVE)->whereDate('option_date', '<', today())->update(['status' => 'expired']);
        $expiring = BlockPowerOption::inStatus('available')
            ->whereDate('option_date', '<=', today()->addDays(BlockPowerOption::EXPIRING_DAYS))->update(['status' => 'expiring']);

        $this->info("$expired option(s) expirée(s), $expiring option(s) bientôt échue(s).");

        return self::SUCCESS;
    }
}
