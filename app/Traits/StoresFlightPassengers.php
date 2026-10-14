<?php

namespace App\Traits;

use App\Models\FlightPassenger;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Création d'un passager de vol depuis le formulaire « passagers[i][…] »
 * et enregistrement de ses pièces jointes (passeport, titre de séjour).
 */
trait StoresFlightPassengers
{
    use FileUpload;

    protected function createFlightPassenger(int $reservationId, array $passager): FlightPassenger
    {
        $stored = FlightPassenger::create([
            'reservation_id' => $reservationId,
            'type' => $passager['type'] === 'adult' ? 'adulte' : $passager['type'],
            'genre' => $passager['genre'] ?? null,
            'nom' => $passager['nom'],
            'prenom' => $passager['prenom'],
            'date_naissance' => $passager['date_naissance'],
            'tele1' => $passager['tele1'] ?? null,
            'tele2' => $passager['tele2'] ?? null,
            'tele_destination' => $passager['tele_destination'] ?? null,
            'assistance' => $passager['assistance'] ?? null,
            'Observation' => $passager['observation'] ?? null,
            'email' => $passager['email'] ?? null,
            'assurance_id' => $passager['assurance'] ?? null,
            'passport' => $passager['passport'] ?? null,
            'expiration' => $passager['expiration'] ?? null,
            'passport_en_cour' => ! empty($passager['passport_en_cour']),
            'sijour' => $passager['sejour'] ?? null,
            'sijour_expiration' => $passager['sejour_expiration'] ?? null,
            'sijour_en_cour' => ! empty($passager['sejour_en_cour']),
            'dossier_etat' => $passager['dossier_etat'] ?? null,
        ]);

        $this->storePassengerDocuments($stored, $passager['passport_file'] ?? null, $passager['sejour_file'] ?? null);

        return $stored;
    }

    /**
     * Met à jour un passager depuis sa fiche (composant reservation-passager-card, champs
     * « old_{type}_{champ}_{id} »). Seuls les champs présents dans le formulaire sont modifiés :
     * un champ désactivé (assurance) ou absent garde sa valeur.
     * $withVerification : la fiche contient aussi « vérifié », « inscrit aller / retour » et leurs références.
     */
    protected function updateFlightPassengerFromForm(FlightPassenger $passenger, Request $request, string $tripType, bool $withVerification = false): void
    {
        $key = fn (string $field) => "old_{$passenger->type}_{$field}_{$passenger->id}";
        if (! $request->has($key('nom'))) {
            return;
        }

        $columns = ['genre' => 'genre', 'nom' => 'nom', 'prenom' => 'prenom', 'date_naissance' => 'date_naissance',
            'passport' => 'passport', 'expiration' => 'expiration', 'tele1' => 'tele1', 'tele2' => 'tele2',
            'tele_destination' => 'tele_destination', 'assistance' => 'assistance', 'observation' => 'Observation',
            'email' => 'email', 'assurance' => 'assurance_id', 'sejour' => 'sijour', 'sejour_expiration' => 'sijour_expiration',
            'dossier_etat' => 'dossier_etat', 'ref_aller' => 'ref_aller', 'ref_retour' => 'ref_retour'];
        $values = [];
        foreach ($columns as $field => $column) {
            if ($request->has($key($field))) {
                $values[$column] = $request->input($key($field));
            }
        }
        $values['sijour_en_cour'] = $request->boolean($key('sejour_en_cour'));
        if ($withVerification) {
            $values['verifier'] = $request->filled($key('verifier')) ? 'Oui' : 'NON';
            if (in_array($tripType, ['aller_retour_diffrent_vol', 'aller_simple'])) {
                $values['inscrit_aller'] = $request->filled($key('inscrit_aller'));
            }
            if (in_array($tripType, ['aller_retour_diffrent_vol', 'retour_simple'])) {
                $values['inscrit_retour'] = $request->filled($key('inscrit_retour'));
            }
        }

        $passenger->update($values);
        $this->storePassengerDocuments($passenger, $request->file($key('passport_file')), $request->file($key('sejour_file')));
    }

    /** Supprime les pièces jointes d'un passager (passeport, titre de séjour) */
    protected function deletePassengerDocuments(FlightPassenger $passenger): void
    {
        foreach (['passports' => $passenger->passport_image, 'sejours' => $passenger->sijour_image] as $folder => $file) {
            if ($file) {
                Storage::disk('public')->delete("passagers/$folder/$file");
            }
        }
    }

    protected function storePassengerDocuments(FlightPassenger $passenger, mixed $passport, mixed $sejour): void
    {
        foreach (['passport_image' => [$passport, 'passports'], 'sijour_image' => [$sejour, 'sejours']] as $column => [$file, $folder]) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }
            $fileName = $passenger->id . '.' . $file->getClientOriginalExtension();
            $this->UploadFile($file, "storage/passagers/$folder/$fileName");
            $passenger->$column = $fileName;
        }
        $passenger->save();
    }
}
