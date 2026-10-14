<?php

namespace Tests\Feature;

use App\Mail\EmailVerificationMail;
use App\Mail\ResetPasswordMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * API du site public : inscription par code e-mail, connexion, compte, mot de passe oublié,
 * catalogue de vols, demande de devis, paiement.
 */
class ApiTest extends TestCase
{
    private function sentCode(string $mailable): string
    {
        $code = null;
        Mail::assertSent($mailable, function ($mail) use (&$code) {
            $code = $mail->otp;

            return true;
        });

        return (string) $code;
    }

    private function token($response): string
    {
        return $response->getCookie('access_token', false)->getValue();
    }

    public function test_sign_up_login_account_and_logout(): void
    {
        Mail::fake();
        $email = 'nouveau.client@example.com';

        $this->postJson(route('api.auth.otp'), ['email' => $email])->assertOk()->assertJson(['code' => '2001']);
        $this->postJson(route('api.auth.otp'), ['email' => 'client@travel-company.test'])->assertJson(['code' => '2004']);
        $code = $this->sentCode(EmailVerificationMail::class);

        $fields = ['email' => $email, 'firstName' => 'Nina', 'lastName' => 'Perret', 'phoneNumber' => '0611223344', 'password' => 'Secret123'];
        $this->postJson(route('api.auth.otp.verify'), $fields + ['otp' => '000000'])->assertStatus(422)->assertJson(['code' => '2003']);
        $response = $this->postJson(route('api.auth.otp.verify'), $fields + ['otp' => $code])->assertOk()->assertJson(['code' => '2005']);
        $this->assertDatabaseHas('clients', ['email' => $email, 'firstName' => 'Nina']);
        $response->assertCookie('isLogged');

        $this->withCredentials()->withUnencryptedCookie('access_token', $this->token($response))
            ->getJson(route('api.me'))->assertOk()->assertJsonPath('data.email', $email);
        $this->withCredentials()->withUnencryptedCookie('access_token', $this->token($response))
            ->postJson(route('api.me.update'), ['firstName' => 'Nina', 'lastName' => 'Perret-Roy'])->assertOk();
        $this->assertSame('Perret-Roy', DB::table('clients')->where('email', $email)->value('lastName'));

        $this->postJson(route('api.auth.login'), ['email' => $email, 'password' => 'mauvais'])->assertUnauthorized()->assertJson(['code' => '5003']);
        $login = $this->postJson(route('api.auth.login'), ['email' => $email, 'password' => 'Secret123'])->assertOk()->assertJson(['code' => '2005']);
        $this->withCredentials()->withUnencryptedCookie('access_token', $this->token($login))->postJson(route('api.auth.logout'))->assertOk()->assertJson(['code' => '5001']);

        $this->getJson(route('api.me'))->assertUnauthorized()->assertJson(['code' => '5003']);
    }

    public function test_otp_requests_are_limited(): void
    {
        Mail::fake();
        foreach (range(1, 3) as $i) {
            $this->postJson(route('api.auth.otp'), ['email' => 'limite@example.com'])->assertOk();
        }
        $this->postJson(route('api.auth.otp'), ['email' => 'limite@example.com'])->assertStatus(429)->assertJson(['code' => '2008']);
        Mail::assertSent(EmailVerificationMail::class, 3);
    }

    public function test_password_reset(): void
    {
        Mail::fake();
        $this->postJson(route('api.password-reset.otp'), ['email' => 'inconnu@example.com'])->assertNotFound()->assertJson(['code' => '2009']);
        $this->postJson(route('api.password-reset.otp'), ['email' => 'client@travel-company.test'])->assertOk()->assertJson(['code' => '2001']);
        $code = $this->sentCode(ResetPasswordMail::class);

        $this->postJson(route('api.password-reset.otp.verify'), ['email' => 'client@travel-company.test', 'otp' => $code, 'password' => 'Nouveau123'])
            ->assertOk()->assertJson(['code' => '2005']);
        $this->postJson(route('api.auth.login'), ['email' => 'client@travel-company.test', 'password' => 'Nouveau123'])->assertOk();
    }

    public function test_catalog_and_quote(): void
    {
        $destination = DB::table('vols')->where('date_aller', '>', today())->value('destination');
        $flight = DB::table('vols')->where('destination', $destination)->where('date_aller', '>', today())->value('id');

        $this->getJson(route('api.flights.index', $destination))->assertOk()->assertJsonFragment(['id' => $flight]);
        $this->postJson(route('api.flights.search', $destination), ['checkInAirport' => 'any'])->assertOk()->assertJsonStructure(['data']);
        $this->getJson(route('api.flights.show', [$destination, $flight]))->assertOk()->assertJsonPath('data.id', $flight);
        $this->getJson(route('api.flights.show', [$destination, 999999]))->assertNotFound();
        $this->postJson(route('api.airports.search'), ['country' => 'France'])->assertOk()->assertJsonStructure(['data']);

        $this->postJson(route('api.quotes.store'), [
            'departure_city' => 'Paris', 'arrival_city' => 'Rome', 'departure_date' => now()->addMonth()->toDateString(),
            'passagers_nb' => 2, 'first_name' => 'Jean', 'last_name' => 'Morel', 'phone_num' => '0600000000', 'email' => 'jean@example.com',
        ])->assertOk();
        $this->assertDatabaseHas('devis', ['email' => 'jean@example.com', 'passengers_nb' => 2]);
        $this->postJson(route('api.quotes.store'), [])->assertUnprocessable();
    }

    public function test_payment_parameters_are_signed(): void
    {
        config(['services.paybox.hmac_key' => str_repeat('AB', 64)]);
        $login = $this->postJson(route('api.auth.login'), ['email' => 'client@travel-company.test', 'password' => 'Demo1234!'])->assertOk();
        $flight = DB::table('vols')->where('date_aller', '>', today())->where('place_disponible', '>', 2)->first();

        $data = $this->withCredentials()->withUnencryptedCookie('access_token', $this->token($login))
            ->postJson(route('api.payments.store'), ['flightId' => $flight->id, 'adultNb' => 2])->assertOk()->json('data');
        $this->assertSame((int) round($flight->prix_vente_adult * 2 * 100), $data['PBX_TOTAL']);
        $this->assertStringContainsString('Sign:K', $data['PBX_RETOUR']);
        $this->assertSame(128, strlen($data['PBX_HMAC']));

        $this->get(route('api.payments.refused', ['Erreur' => '00004']))->assertRedirect();
        $this->get(route('api.payments.notify'))->assertOk();
    }
}
