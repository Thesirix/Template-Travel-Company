<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class RecordDailyAttendance
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::guard('gent')->check()) {
            $gent = Auth::guard('gent')->user();

            $date = now()->toDateString();

            // Vérifie si une présence a déjà été enregistrée aujourd'hui
            $alreadyPresent = Attendance::where('gent_id', $gent->id)
                ->where('date', $date)
                ->exists();

            // Si c’est la première fois aujourd’hui, on affiche la motivation
            if (!$alreadyPresent) {
                Session::flash('showMotivationMessage', true);
            }

            // Création de la ligne de présence (n'aura lieu qu'une seule fois par jour)
            $attendance = Attendance::firstOrCreate(
                [
                    'gent_id' => $gent->id,
                    'date'    => $date,
                ],
                [
                    'heure_arrivee' => now()->setTimezone('Europe/Paris'),
                    'ip_adresse'    => $request->server('REMOTE_ADDR'),
                    'mac_adresse'   => $this->getMacAddress(),
                ]
            );

            // ----------------ferié--------------------

            if ($attendance->wasRecentlyCreated) {
                $d = Carbon::parse($date);



                $joursFeries = [
                    '01-05',
                    '14-07',
                    '15-08',
                    '25-12',
                ];


                if (in_array($d->format('d-m'), $joursFeries)) {

                    DB::table('leave_counters')
                        ->where('gent_id', $gent->id)
                        ->where('type', 'ferie')
                        ->increment('balance', 1);
                }
            }
        }

        return $next($request);
    }



    /**
     * Fonction pour récupérer l'adresse MAC en fonction du système d'exploitation.
     *
     * @return string
     */
    private function getMacAddress()
    {
        if (PHP_OS_FAMILY === 'Windows') {


            $output = shell_exec('getmac');
            $lines = explode("\n", trim($output));

            foreach ($lines as $line) {

                $parts = preg_split('/\s+/', trim($line));
                if (isset($parts[0]) && preg_match('/([A-Fa-f0-9]{2}[:-]){5}[A-Fa-f0-9]{2}/', $parts[0])) {
                    return $parts[0]; // Retourne la première adresse MAC valide
                }
            }
        } else {
            // Pour Linux/macOS, vérifie plusieurs interfaces réseau
            $interfaces = ['eth0', 'en0', 'wlan0']; // Ethernet, Mac, Wi-Fi
            foreach ($interfaces as $interface) {
                $mac = shell_exec("cat /sys/class/net/{$interface}/address 2>/dev/null");
                if ($mac) {
                    return trim($mac);
                }
            }
        }
        return 'Adresse MAC non trouvée';
    }
}
