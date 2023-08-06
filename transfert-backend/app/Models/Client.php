<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;
    public function comptes()
    {
        return $this->hasMany(Compte::class, 'client_id');
    }

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
    protected $fillable = [
        'nom',
        'prenom',
        'numero_telephone'
    ];
}
