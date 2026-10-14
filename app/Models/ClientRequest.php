<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientRequest extends Model
{
    use HasFactory;

    // Nom de la table (si différent du nom du modèle en snake_case)
    protected $table = 'client_requests';

    // Colonnes autorisées pour les insertions de masse
    protected $fillable = [
        'agence_id',
        'gent_id',
        'destination',
        'formule',
        'departure_city',
        'arrived_city',
        'departure_date',
        'return_date',
        'nb_passagers_adultes',
        'nb_passagers_childs',
        'nb_passagers_babies',
        'bagage',
        'created_at',
    ];


    public $timestamps = false;


    public function gent()
    {
        return $this->belongsTo(Agent::class);
    }


    public function agence()
    {
        return $this->gent->agence();
    }
}

