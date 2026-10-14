<?php

/*
| Liste des pages à tester, écrite dans tests/Browser/output/urls.json.
|
| Chaque route GET des espaces agent et admin est incluse automatiquement : une nouvelle page est
| donc testée sans rien modifier ici. Les paramètres d'URL ({group}, {booking}…) sont remplis avec
| de vrais enregistrements de la démo. Si une nouvelle route utilise un paramètre inconnu, elle est
| signalée dans « missing » : ajoutez sa valeur dans $byParam (ou $byRoute pour un cas particulier).
*/

require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$first = fn (string $table, array $where = []) => DB::table($table)->where($where)->orderBy('id')->value('id');
$group = DB::table('vol_groups')->where('type', 'round-trip')->orderBy('id')->value('id');
$reservation = DB::table('vol_reservations')->join('vols', 'vols.id', '=', 'vol_reservations.vol_id')
    ->where('vols.vol_group_id', $group)->where('vol_reservations.status', 1)->orderBy('vol_reservations.id')->value('vol_reservations.id');
$flight = DB::table('vols')->where('vol_group_id', $group)->orderBy('id')->value('id');
$destination = DB::table('destinations')->orderBy('position')->value('slug');
$adminFlight = DB::table('vols')->where('destination', $destination)->orderBy('id')->value('id');
$booking = fn (?string $etat) => $etat
    ? DB::table('boat_reservations')->where('etat', $etat)->value('booking_id')
    : DB::table('boat_bookings')->whereNotIn('id', DB::table('boat_reservations')->pluck('booking_id'))->orderBy('id')->value('id');
$option = fn (string $status) => DB::table('block_power_options')->where('status', $status)->value('id');

// Valeur d'un paramètre pour une route précise
$byRoute = [
    'agent.block-power.confirm-form' => ['option' => $option('named')],
    'admin.places.edit' => ['flight' => $adminFlight],
];
// Valeur par défaut de chaque nom de paramètre
$byParam = [
    'group' => $group, 'reservation' => $reservation, 'flight' => $flight, 'booking' => $booking(null),
    'option' => $option('available'), 'passenger' => $first('boat_passengers'),
    'boatDestination' => $first('boat_destinations'), 'blockPowerRequest' => $first('block_power_requests'),
    'procedure' => $first('procedures'), 'destination' => $destination,
    'client' => $first('clients'), 'agent' => $first('gents'), 'agency' => $first('agences'), 'country' => $first('countries'),
    'city' => $first('villes'), 'airport' => $first('air_ports'), 'company' => $first('companies'),
    'condition' => $first('conditions'), 'insurance' => $first('assurences'), 'locale' => 'fr',
];
// Exports et téléchargements : testés par PHPUnit (fichiers), pas comme des pages
$skip = '#\.(export|download|conditions|e-billet|contrat|logout)(\.|$)|deconnexion|telecharger#';

// Variantes utiles (filtres, onglets, états particuliers)
$out = [
    'agent' => [
        "agent/vols/$group/vente/filtre?order_by=villes.nom&order_type=desc&order_vol_type=aller_retour",
        "agent/extractions?destination=$destination",
        'agent/procedures/' . DB::table('procedures')->orderByDesc('id')->value('id'),
        'agent/bateau/' . $booking('confirmer'), 'agent/bateau/' . $booking('final'), 'agent/bateau/' . $booking('echange'),
        'agent/bateau/a-traiter?nom=Martin&destination=',
        'agent/allotements?statut=nominatives', 'agent/allotements?statut=a-emettre', 'agent/allotements?statut=emises',
        'agent/allotements?statut=archives', 'agent/allotements?statut=relachees', 'agent/allotements?statut=expirees',
        'agent/allotements?tri=price&sens=desc',
        'agent/allotements/options/' . $option('named') . '/confirmer',
    ],
    'admin' => [
        "admin/vols/$destination?archive=1", "admin/vols/$destination?archive=0",
        'admin/clients?q=Leroy', 'admin/historique?action=Ajouter',
    ],
    'public' => [],
    'missing' => [],
];

foreach (app('router')->getRoutes() as $route) {
    $name = (string) $route->getName();
    if (! in_array('GET', $route->methods()) || $name === '' || str_starts_with($name, 'api.') || preg_match($skip, $name) || str_ends_with($name, '.login')) {
        continue;
    }
    $space = preg_match('#^(agent|admin)\.#', $name, $m) ? $m[1] : 'public';
    if ($space === 'public' && in_array('auth:gent', $route->gatherMiddleware())) {
        $space = 'agent';
    }
    $params = [];
    foreach ($route->parameterNames() as $p) {
        $value = $byRoute[$name][$p] ?? $byParam[$p] ?? null;
        if ($value === null) {
            $out['missing'][] = "$name {{$p}}";
            continue 2;
        }
        $params[$p] = $value;
    }
    $out[$space][] = ltrim(route($name, $params, false), '/');
}

foreach (['agent', 'admin', 'public'] as $space) {
    $out[$space] = array_values(array_unique($out[$space]));
}
@mkdir(__DIR__ . '/output', 0777, true);
file_put_contents(__DIR__ . '/output/urls.json', json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
echo json_encode(array_map('count', $out)), "\n";
