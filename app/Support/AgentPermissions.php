<?php

namespace App\Support;

use App\Models\Agent;
use Spatie\Permission\Models\Permission;

/**
 * Droits des agents (garde « gent ») : un droit par destination, plus les modules de l'espace agent.
 */
class AgentPermissions
{
    /** Modules de l'espace agent : libellé => permission */
    public const MODULES = [
        'Bateau' => 'gerer Bateau',
        'Demandes clients' => 'gerer Clients',
        'Devis' => 'gerer devis',
        'Paiements' => 'gerer Payer',
        'Extractions' => 'gerer Extraction',
        'Procédures' => 'gerer Procedure',
        'Allotements' => 'gerer Block power',
        'Planning de l\'équipe' => 'gerer Planning',
    ];

    public function __construct(private Branding $brand)
    {
    }

    /**
     * Droits regroupés pour l'affichage : ['Destinations' => [libellé => permission], 'Modules' => [...]].
     */
    public function grouped(): array
    {
        $destinations = [];
        foreach ($this->brand->destinations() as $destination) {
            if ($name = $destination->permissionName()) {
                $destinations[$destination->name] = $name;
            }
        }

        return ['Destinations' => $destinations, 'Modules' => self::MODULES];
    }

    /** @return string[] */
    public function managed(): array
    {
        return array_values(array_merge(...array_values(array_map('array_values', $this->grouped()))));
    }

    /**
     * Applique les droits cochés. Les permissions sont créées si besoin ;
     * les permissions que ce formulaire ne gère pas sont conservées.
     */
    public function sync(Agent $agent, array $requested): void
    {
        $managed = $this->managed();
        foreach ($managed as $name) {
            Permission::findOrCreate($name, 'gent');
        }
        $kept = array_diff($agent->getPermissionNames()->all(), $managed);
        $agent->syncPermissions(array_values(array_merge($kept, array_intersect($requested, $managed))));
    }
}
