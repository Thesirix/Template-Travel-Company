<?php

namespace Tests\Feature\Agent;

use App\Mail\PaymentReceivedMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Paiement par carte saisi par un agent : formulaire signé, puis retour Paybox vérifié par signature RSA.
 */
class CardPaymentTest extends TestCase
{
    private \OpenSSLAsymmetricKey $privateKey;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->actingAsAgent('super.agent');

        // Paire de clés de test : la clé publique joue le rôle de pubkey.pem de Paybox
        $this->privateKey = openssl_pkey_get_private(file_get_contents(base_path('tests/Fixtures/paybox-test-private.pem')));
        config(['services.paybox.public_key' => base_path('tests/Fixtures/paybox-test-public.pem'), 'services.paybox.hmac_key' => str_repeat('AB', 64)]);
    }

    private function startPayment(): array
    {
        $response = $this->post(route('agent.paiements.pay'), [
            'paynom' => 'Claire Martin', 'payadresse' => '3 rue des Lilas', 'paycity' => 'Lyon', 'paypostal' => '69001',
            'paytele' => '06 11 22 33 44', 'payprice' => '249.90', 'paytype' => 'Acompte réservation', 'acceptpaypolicy' => '1',
        ]);
        $response->assertOk()->assertSee('PBX_HMAC', false)->assertSee('24990', false);

        return session('paybox.pending');
    }

    /** Retour navigateur signé comme le ferait Paybox */
    private function signedReturn(array $values): string
    {
        $query = http_build_query($values);
        openssl_sign($query, $signature, $this->privateKey, OPENSSL_ALGO_SHA1);

        return route('paybox.accepted') . '?' . $query . '&Sign=' . urlencode(base64_encode($signature));
    }

    public function test_payment_form_requires_the_commitment(): void
    {
        $this->post(route('agent.paiements.pay'), ['paynom' => 'Claire Martin', 'payprice' => 10])->assertSessionHasErrors(['acceptpaypolicy', 'payadresse']);
        $this->get(route('agent.paiements'))->assertOk();
    }

    public function test_signed_return_records_the_payment(): void
    {
        $pending = $this->startPayment();
        $this->get($this->signedReturn(['Mt' => 24990, 'Ref' => $pending['reference'], 'Auto' => 'XXXXXX', 'Erreur' => '00000']))
            ->assertRedirect(route('agent.paiements'))->assertSessionHas('success');

        $this->assertDatabaseHas('payment_historiques', ['fullname' => 'Claire Martin', 'price' => 249.90, 'text' => 'Acompte réservation']);
        $this->assertNull(session('paybox.pending'));
        Mail::assertSent(PaymentReceivedMail::class);
    }

    public function test_forged_or_mismatched_returns_are_refused(): void
    {
        $pending = $this->startPayment();
        $before = DB::table('payment_historiques')->count();

        // Sans signature
        $this->get(route('paybox.accepted', ['Mt' => 24990, 'Ref' => $pending['reference'], 'Erreur' => '00000']))->assertSessionHas('error');
        // Signé mais montant différent
        $this->get($this->signedReturn(['Mt' => 100, 'Ref' => $pending['reference'], 'Auto' => 'X', 'Erreur' => '00000']))->assertSessionHas('error');
        // Signé mais autre commande
        $this->get($this->signedReturn(['Mt' => 24990, 'Ref' => 'autre', 'Auto' => 'X', 'Erreur' => '00000']))->assertSessionHas('error');

        $this->assertSame($before, DB::table('payment_historiques')->count());
        Mail::assertNothingSent();
    }

    public function test_refused_and_aborted_returns(): void
    {
        $this->get(route('paybox.refused', ['Erreur' => '00004']))->assertRedirect(route('agent.paiements'))
            ->assertSessionHas('error', 'Numéro de carte ou cryptogramme visuel invalide.');
        $this->get(route('paybox.aborted'))->assertSessionHas('warning');
        $this->get(route('paybox.waiting'))->assertSessionHas('warning');
    }
}
