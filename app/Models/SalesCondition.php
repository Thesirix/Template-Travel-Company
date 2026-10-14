<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesCondition extends Model
{
    protected $table = 'conditions';

    protected $fillable=['id','nom','Formalite_sante','Formalite_police','Formalite_inscription',
        'Formalite_modifiation','Formalite_annulation'];

}
