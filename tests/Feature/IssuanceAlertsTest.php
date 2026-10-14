<?php

namespace Tests\Feature;

use App\Mail\Cron\IssuanceAlertMail;
use App\Services\SmsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Tests\TestCase;

/**
 * Tâche quotidienne flights:issuance-alerts (J-61, J-45, J-35).
 */
class IssuanceAlertsTest extends TestCase
{
    public function test_alerts_are_sent_for_each_deadline(): void
    {
        Mail::fake();
        config(['agency.notifications.emission_emails' => ['emission@example.com'], 'agency.notifications.emission_phones' => ['0600000000']]);
        $flights = DB::table('vols')->where('type_voyage', 'aller_retour')->orderBy('id')->limit(3)->pluck('id');
        DB::table('vols')->where('id', $flights[0])->update(['date_aller' => today()->addDays(61)]);
        DB::table('vols')->where('id', $flights[1])->update(['date_aller' => today()->addDays(45)]);
        DB::table('vols')->where('id', $flights[2])->update(['date_aller' => today()->addDays(35)]);
        // Un dossier incomplet sur le vol J-45, avec un adulte joignable
        $reservation = DB::table('vol_reservations')->where('vol_id', $flights[1])->where('status', 1)->value('id')
            ?? $this->markTestSkipped('aucune réservation sur le vol J-45 dans la démo');
        DB::table('vol_passagers')->where('reservation_id', $reservation)->update(['dossier_etat' => 'Incomplet']);
        DB::table('vol_passagers')->where('reservation_id', $reservation)->where('type', 'adulte')->update(['tele1' => '0611111111']);

        $sms = Mockery::mock(SmsService::class);
        $sent = [];
        $sms->shouldReceive('sendSms')->andReturnUsing(function ($phones, $message) use (&$sent) {
            $sent[] = [(array) $phones, $message];

            return ['sent' => true, 'response' => 'ok'];
        });
        $this->app->instance(SmsService::class, $sms);

        $this->artisan('flights:issuance-alerts')->assertSuccessful();

        Mail::assertSent(IssuanceAlertMail::class, 1);
        $messages = array_column($sent, 1);
        $this->assertNotEmpty(array_filter($messages, fn ($m) => str_contains($m, 'régler vos billets')), 'SMS client J-45');
        $this->assertNotEmpty(array_filter($sent, fn ($s) => in_array('0611111111', $s[0])));
        $this->assertNotEmpty(array_filter($messages, fn ($m) => str_contains($m, 'émission prochaine')), 'rappel J-35');
    }

    public function test_alerts_are_scheduled_once_a_day(): void
    {
        $event = collect(app(\Illuminate\Console\Scheduling\Schedule::class)->events())
            ->first(fn ($e) => str_contains($e->command, 'flights:issuance-alerts'));
        $this->assertNotNull($event);
        $this->assertSame('0 8 * * *', $event->expression);
    }
}
