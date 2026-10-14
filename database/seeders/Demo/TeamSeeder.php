<?php

namespace Database\Seeders\Demo;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Vie de l'équipe : demandes de congés, emploi du temps, notes, tâches de l'administration, présences,
 * procédures internes (fichiers générés) et paiements en ligne encaissés.
 */
class TeamSeeder extends Seeder
{
    use SeedsDemoData;

    public function run(): void
    {
        $super = $this->agentId('super.agent');
        $agent = $this->agentId('agent');
        $partner = $this->agentId('partenaire');

        foreach ([
            [$super, 'Congés d\'hiver', 'conge', 'approved', 30, 37],
            [$agent, 'Pont de mai', 'conge', 'pending', 45, 47],
            [$agent, 'Repos', 'repos', 'rejected', 10, 10],
            [$partner, 'Rendez-vous médical', 'maladie', 'approved', -5, -5],
        ] as [$agentId, $title, $type, $status, $from, $to]) {
            DB::table('calendars')->insert([
                'gent_id' => $agentId, 'name' => $title, 'type' => $type, 'status' => $status,
                'start_date' => $this->now()->addDays($from)->startOfDay(), 'end_date' => $this->now()->addDays($to)->endOfDay(),
                'rejection_reason' => $status === 'rejected' ? 'Période de forte activité' : null,
                'modification_token' => (string) Str::uuid(), 'created_at' => $this->now(), 'updated_at' => $this->now(),
            ]);
        }

        $this->teamLeaves();

        foreach ([
            [$super, 'Réunion d\'équipe', 1, 10, '#4f46e5'], [$super, 'Rappels clients', 2, 14, '#16a34a'],
            [$agent, 'Permanence téléphonique', 1, 9, '#f59e0b'], [$agent, 'Préparation des contrats', 3, 15, '#dc2626'],
        ] as [$agentId, $title, $days, $hour, $color]) {
            $start = $this->now()->addDays($days)->setTime($hour, 0);
            DB::table('emplois')->insert($this->stamps([
                'gent_id' => $agentId, 'title' => $title, 'start_date' => $start, 'end_date' => $start->copy()->addHours(2), 'color' => $color,
            ]));
        }

        foreach ([[$super, 'Relancer la compagnie pour les horaires du vol de Rome.'], [$agent, 'Envoyer les contrats signés à l\'administration.']] as [$agentId, $text]) {
            DB::table('black_notes')->insert(['gent_id' => $agentId, 'text' => $text, 'created_at' => $this->now()]);
        }
        foreach ([[$agent, 'Vérifier les passeports du groupe Italie avant vendredi.'], [$super, 'Préparer le point mensuel des ventes.']] as [$agentId, $text]) {
            DB::table('to_do_lists')->insert(['gent_id' => $agentId, 'text' => $text, 'created_at' => $this->now()]);
        }

        foreach (DB::table('gents')->where('state', 'enable')->pluck('id') as $agentId) {
            $away = DB::table('calendars')->where('gent_id', $agentId)->whereIn('status', ['approved', 'maladie'])->get(['start_date', 'end_date']);
            foreach (range(1, 60) as $daysAgo) {
                $day = $this->now()->subDays($daysAgo);
                $onLeave = $away->contains(fn ($leave) => $day->between($leave->start_date, $leave->end_date));
                if ($day->isWeekend() || $onLeave) {
                    continue;
                }
                DB::table('attendances')->insert([
                    'gent_id' => $agentId, 'date' => $day->toDateString(),
                    'heure_arrivee' => sprintf('%02d:%02d:00', 8 + ($daysAgo % 2), (7 * $daysAgo) % 60), 'ip_adresse' => '127.0.0.1',
                ]);
            }
        }

        // Procédures : un document PDF et une image
        $disk = Storage::disk('public');
        $disk->put('procedures/accueil-client.pdf', Pdf::loadHTML(
            '<h1 style="font-family: sans-serif">Accueil d\'un client en agence</h1>'
            . '<ol style="font-family: sans-serif"><li>Saluer et identifier le besoin.</li><li>Vérifier les disponibilités.</li>'
            . '<li>Établir le devis.</li><li>Encaisser l\'acompte et remettre le contrat.</li></ol>'
        )->output());
        $disk->put('procedures/bagages-cabine.png', $this->image('Bagage cabine : 55 x 40 x 20 cm, 8 kg'));
        foreach ([['Accueil d\'un client en agence', 'accueil-client.pdf'], ['Dimensions des bagages cabine', 'bagages-cabine.png']] as [$title, $file]) {
            DB::table('procedures')->insert(['name' => $title, 'media' => $file, 'created_at' => $this->now()]);
        }

        foreach ([[$super, 'Camille Leroy', 'Lyon', '69002', 640], [$agent, 'Nathan Thomas', 'Paris', '75011', 300]] as [$agentId, $name, $city, $zip, $price]) {
            DB::table('payment_historiques')->insert($this->stamps([
                'user_id' => $agentId, 'fullname' => $name, 'adresse' => '10 rue de la Paix', 'ville' => $city, 'zip' => $zip,
                'telephone' => $this->phone(), 'email' => Str::ascii(strtolower(str_replace(' ', '.', $name))) . '@example.com',
                'price' => $price, 'text' => 'Acompte voyage',
            ]));
        }
    }

    /**
     * Demandes de congés de chaque agent, passées et à venir, dans tous les statuts
     * (validé, en attente, refusé, arrêt maladie), pour que le planning de l'équipe soit parlant.
     */
    private function teamLeaves(): void
    {
        $agents = DB::table('gents')->where('state', 'enable')->orderBy('id')->pluck('id')->values();
        foreach ($agents as $i => $agentId) {
            $shift = ($i * 3) % 11;
            $leaves = [
                ['Congés d\'été', 'conge', 'approved', -95 + $shift, -91 + $shift],
                ['Récupération', 'repos', 'approved', -24 + $shift, -24 + $shift],
                ['Vacances de la Toussaint', 'conge', $i % 3 === 0 ? 'approved' : 'pending', 12 + $shift, 16 + $shift],
                ['Congés de fin d\'année', 'conge', 'pending', 68 + $shift, 72 + $shift],
            ];
            if ($i % 3 === 1) {
                $leaves[] = ['Arrêt maladie', 'maladie', 'maladie', -40 + $shift, -39 + $shift];
            }
            if ($i % 4 === 2) {
                $leaves[] = ['Pont du 11 novembre', 'repos', 'rejected', 27, 27];
            }
            foreach ($leaves as [$title, $type, $status, $from, $to]) {
                $start = $this->now()->addDays($from)->startOfDay();
                $end = $this->now()->addDays($to)->endOfDay();
                $days = 0;
                for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                    $days += $d->isSunday() ? 0 : 1;
                }
                DB::table('calendars')->insert([
                    'gent_id' => $agentId, 'name' => $title, 'type' => $type, 'status' => $status,
                    'start_date' => $start, 'end_date' => $end,
                    'jours_par_type' => json_encode([$type => $days]),
                    'rejection_reason' => $status === 'rejected' ? 'Trop d\'agents absents ce jour-là' : null,
                    'modification_token' => (string) Str::uuid(), 'created_at' => $this->now(), 'updated_at' => $this->now(),
                ]);
            }
        }
    }

    /** Petite image PNG avec un texte (procédure de démonstration) */
    private function image(string $text): string
    {
        $img = imagecreatetruecolor(640, 200);
        imagefill($img, 0, 0, imagecolorallocate($img, 238, 242, 255));
        imagestring($img, 5, 30, 90, $text, imagecolorallocate($img, 49, 46, 129));
        ob_start();
        imagepng($img);

        return (string) ob_get_clean();
    }
}
