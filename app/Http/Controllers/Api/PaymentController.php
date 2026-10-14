<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Flight;
use App\Support\Paybox;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Paiement en ligne d'un vol par un client du site public (Paybox System).
 */
class PaymentController extends Controller
{
    public function __construct(private Paybox $paybox)
    {
    }

    /**
     * Paramètres signés du formulaire Paybox pour le vol et le nombre de passagers demandés.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'flightId' => 'required|integer',
            'adultNb' => 'required|integer|min:1',
            'childNb' => 'nullable|integer|min:0',
            'babyNb' => 'nullable|integer|min:0',
        ]);
        $flight = Flight::find($data['flightId']);
        if (! $flight) {
            return response()->json(['message' => 'Vol introuvable.'], 404);
        }
        $seats = $data['adultNb'] + ($data['childNb'] ?? 0);
        if ($seats > $flight->place_disponible) {
            return response()->json(['message' => 'Il ne reste pas assez de places sur ce vol.'], 422);
        }

        $client = Auth::guard('client')->user();
        $total = $flight->salePrice($data['adultNb'], $data['childNb'] ?? 0, $data['babyNb'] ?? 0);

        $fields = $this->paybox->fields((int) round($total * 100), (string) $client->id, $client->email, [
            'PBX_EFFECTUE' => route('api.payments.confirmed'),
            'PBX_ANNULE' => route('api.payments.canceled'),
            'PBX_REFUSE' => route('api.payments.refused'),
            'PBX_REPONDRE_A' => route('api.payments.notify'),
            'PBX_DISPLAY' => 100,
        ]);

        return response()->json(['data' => ['PBX_URL' => $this->paybox->url(), 'dateTime' => $fields['PBX_TIME']] + $fields]);
    }

    /** Retour navigateur : paiement accepté */
    public function confirmed()
    {
        return redirect()->away(config('app.front_url') . '/profile?payment=success');
    }

    /** Retour navigateur : paiement annulé par le client */
    public function canceled()
    {
        return $this->backToFront('00001');
    }

    /** Retour navigateur : paiement refusé par la banque */
    public function refused(Request $request)
    {
        return $this->backToFront((string) $request->query('Erreur', '00100'));
    }

    /** Notification serveur à serveur de Paybox (résultat de la transaction) */
    public function notify(Request $request)
    {
        Log::info('Notification Paybox', $request->only(['Mt', 'Ref', 'Auto', 'Erreur']) + ['signature_valide' => $this->paybox->verify($request)]);

        return response('', 200);
    }

    private function backToFront(string $error)
    {
        return redirect()->away(config('app.front_url') . '/inscription?payment-error=' . urlencode($error));
    }
}
