<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Support\Paybox;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Paiement par carte d'un client au comptoir (Paybox System) :
 * l'agent saisit le titulaire et le montant, puis le client paie sur la page Paybox.
 * Le retour de la banque est traité par PayboxController.
 */
class PaymentController extends Controller
{
    public function index()
    {
        return view('agent.paiements.index');
    }

    public function pay(Request $request, Paybox $paybox)
    {
        $data = $request->validate([
            'paynom' => ['required', 'string', 'min:2', 'max:150'],
            'payadresse' => ['required', 'string', 'min:2', 'max:255'],
            'paycity' => ['required', 'string', 'min:2', 'max:100'],
            'paypostal' => ['required', 'string', 'min:2', 'max:20'],
            'paytele' => ['required', 'string', 'min:8', 'max:30'],
            'payprice' => ['required', 'numeric', 'min:1'],
            'paytype' => ['required', 'string', 'max:255'],
            'acceptpaypolicy' => ['accepted'],
        ], [], [
            'paynom' => 'titulaire de la carte', 'payadresse' => 'adresse', 'paycity' => 'ville', 'paypostal' => 'code postal',
            'paytele' => 'téléphone', 'payprice' => 'montant', 'paytype' => 'objet du paiement', 'acceptpaypolicy' => 'engagement',
        ]);

        $payment = [
            'reference' => Str::random(32),
            'fullname' => $data['paynom'],
            'adresse' => $data['payadresse'],
            'ville' => $data['paycity'],
            'zip' => $data['paypostal'],
            'telephone' => $data['paytele'],
            'email' => config('agency.emails.payments'),
            'price' => round((float) $data['payprice'], 2),
            'text' => $data['paytype'],
        ];
        $request->session()->put('paybox.pending', $payment);

        $fields = $paybox->fields((int) round($payment['price'] * 100), $payment['reference'], $payment['email'], [
            'PBX_EFFECTUE' => route('paybox.accepted'),
            'PBX_ATTENTE' => route('paybox.waiting'),
            'PBX_REFUSE' => route('paybox.refused'),
            'PBX_ANNULE' => route('paybox.aborted'),
        ]);

        return view('agent.paiements.index', ['payment' => $payment, 'paybox' => $fields, 'payboxUrl' => $paybox->url()]);
    }
}
