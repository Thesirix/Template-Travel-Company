<?php

namespace App\Enums;

enum ReservationHistoryEnum: string
{
    case create = 'Créer une réservation';
    case modify_pax = 'Modifier la réservation';
    case modify_date = 'Modifier la date';
    case cancel = 'Annuler la réservation';
    case delete_passager = 'Supprimer un passager';
    case delete_payment = 'Supprimer un paiement N Acompte ';
    case add_payment = 'Ajouter un paiement N Acompte ';
    case modify_payment = 'Modifier un paiement N Acompte ';
    case add_passager = 'Ajouter un passager';
}
