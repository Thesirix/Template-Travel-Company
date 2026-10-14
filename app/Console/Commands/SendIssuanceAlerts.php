<?php

namespace App\Console\Commands;

use App\Enums\FlightPassengerRegistrationStatus;
use App\Enums\PassengerType;
use App\Mail\Cron\FailureMail;
use App\Mail\Cron\IssuanceAlertMail;
use App\Models\Agent;
use App\Models\Flight;
use App\Models\FlightGroup;
use App\Models\FlightReservation;
use App\Services\SmsService;
use Closure;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Alertes quotidiennes avant l'émission des billets :
 * - J-61 : liste des vols aux responsables de l'émission (e-mail + SMS), délai d'annulation / rétrocession ;
 * - J-45 : clients dont le dossier est incomplet (SMS) et leurs agents ;
 * - J-35 : rappel d'émission aux responsables et dossiers incomplets aux agents.
 * Un échec est signalé par e-mail à la boîte « alertes » sans bloquer les autres étapes.
 */
class SendIssuanceAlerts extends Command
{
    protected $signature = 'flights:issuance-alerts';

    protected $description = "Alertes d'émission des vols (J-61, J-45, J-35) : e-mails et SMS aux responsables, agents et clients";

    public function __construct(private SmsService $sms)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->step('J-61', fn () => $this->retrocessionDeadline(Carbon::today()->addDays(61)));
        $this->step('J-45', fn () => $this->unpaidFiles(Carbon::today()->addDays(45)));
        $this->step('J-35', fn () => $this->issuanceReminder(Carbon::today()->addDays(35)));

        return self::SUCCESS;
    }

    private function retrocessionDeadline(Carbon $date): void
    {
        $groups = FlightGroup::whereHas('flights', fn ($q) => $q->whereDate('date_aller', $date))
            ->with(['flights' => fn ($q) => $q->whereDate('date_aller', $date)])->get();
        if ($groups->isEmpty()) {
            return;
        }

        if ($recipients = config('agency.notifications.emission_emails')) {
            Mail::to($recipients)->send(new IssuanceAlertMail(
                'Alerte : annulation et rétrocession (J-61)',
                'Bonjour, voici la liste des vols au départ dans 61 jours :',
                $groups,
            ));
        }
        $this->sms->sendSms(config('agency.notifications.emission_phones'),
            'Vols au départ dans 61 jours (annulation / rétrocession) : ' . $this->dates($groups->flatMap->flights));
    }

    private function unpaidFiles(Carbon $date): void
    {
        $reservations = $this->incompleteReservations()->whereHas('vol', fn ($q) => $q->whereDate('date_aller', $date))
            ->with('passagers')->get();

        foreach ($reservations as $reservation) {
            $phones = $reservation->passagers->where('type', PassengerType::ADULT->value)
                ->flatMap(fn ($p) => [$p->tele1, $p->tele2])->filter()->unique()->values()->all();
            $this->sms->sendSms($phones, "Bonjour, merci de passer régler vos billets, sans quoi vos places seront annulées. URGENT");
        }
        $this->notifyAgents($reservations, 'Bonjour, merci de contacter vos clients pour les factures : ');
    }

    private function issuanceReminder(Carbon $date): void
    {
        $flights = Flight::whereDate('date_aller', $date)->get();
        if ($flights->isEmpty()) {
            return;
        }

        $this->sms->sendSms(config('agency.notifications.emission_phones'),
            'Bonjour, émission prochaine des vols du ' . $this->dates($flights)
            . ' : merci de vérifier que les dossiers sont complets et de transmettre la liste finale à la compagnie.');

        $reservations = $this->incompleteReservations()->whereIn('vol_id', $flights->pluck('id'))->get();
        $this->notifyAgents($reservations, 'Bonjour, ces dossiers sont incomplets, merci de contacter vos clients. URGENT. Factures : ');
    }

    private function incompleteReservations()
    {
        return FlightReservation::with('bill')->where('status', true)
            ->whereHas('passagers', fn ($q) => $q->where('dossier_etat', FlightPassengerRegistrationStatus::INCOMPLETE->value));
    }

    /** Un SMS par agent avec les numéros de facture de ses réservations */
    private function notifyAgents($reservations, string $message): void
    {
        $byAgent = $reservations->groupBy('gent_id');
        foreach (Agent::whereIn('id', $byAgent->keys())->get() as $agent) {
            $bills = $byAgent[$agent->id]->map(fn ($r) => $r->bill?->n_acompte ?: '#' . $r->id)->implode(', ');
            $this->sms->sendSms($agent->phone, $message . $bills);
        }
    }

    private function dates($flights): string
    {
        return $flights->map(fn ($f) => Carbon::parse($f->date_aller)->format('d/m/Y'))->unique()->implode(', ');
    }

    private function step(string $name, Closure $callback): void
    {
        try {
            $callback();
            $this->info("$name : OK");
        } catch (Throwable $exception) {
            Log::error("Alertes d'émission $name en échec", ['exception' => $exception]);
            $this->error("$name : " . $exception->getMessage());
            try {
                Mail::to(config('agency.emails.alerts'))->send(new FailureMail("Alertes d'émission $name", $exception->getMessage()));
            } catch (Throwable $mailException) {
                Log::error("Impossible d'envoyer l'alerte d'échec", ['exception' => $mailException]);
            }
        }
    }
}
