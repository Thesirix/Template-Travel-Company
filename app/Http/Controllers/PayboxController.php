<?php

namespace App\Http\Controllers;

use App\Mail\PaymentReceivedMail;
use App\Models\Payment;
use App\Support\Paybox;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Retours navigateur de Paybox pour les paiements saisis par un agent (Agent\PaymentController).
 * Un paiement n'est enregistré que si la signature Paybox est valide et correspond
 * à la référence et au montant en attente dans la session de l'agent.
 */
class PayboxController extends Controller
{
    public function accepted(Request $request, Paybox $paybox)
    {
        $pending = $request->session()->get('paybox.pending');
        $valid = $paybox->verify($request)
            && $request->query('Erreur') === Paybox::SUCCESS
            && $pending
            && hash_equals($pending['reference'], (string) $request->query('Ref'))
            && (int) $request->query('Mt') === (int) round($pending['price'] * 100);

        if (! $valid) {
            Log::warning('Retour Paybox refusé', $request->only(['Mt', 'Ref', 'Erreur']));

            return redirect()->route('agent.paiements')->with('error', "Le paiement n'a pas pu être vérifié : il n'a pas été enregistré.");
        }

        $payment = Payment::create(collect($pending)->except('reference')->all() + [
            'user_id' => Auth::guard('gent')->id(),
            'created_at' => now(),
        ]);
        $request->session()->forget('paybox.pending');

        try {
            Mail::to(config('agency.emails.payments'))->send(new PaymentReceivedMail($payment));
        } catch (Throwable $exception) {
            Log::error('Paiement enregistré, e-mail non envoyé', ['payment' => $payment->id, 'exception' => $exception]);
        }

        return redirect()->route('agent.paiements')->with('success', 'Paiement de ' . number_format($payment->price, 2, ',', ' ') . ' € accepté.');
    }

    public function waiting()
    {
        return redirect()->route('agent.paiements')->with('warning', Paybox::message('99999'));
    }

    public function refused(Request $request)
    {
        return redirect()->route('agent.paiements')->with('error', Paybox::message($request->query('Erreur')));
    }

    public function aborted()
    {
        return redirect()->route('agent.paiements')->with('warning', 'Paiement annulé.');
    }
}
