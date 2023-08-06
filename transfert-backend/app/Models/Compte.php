<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compte extends Model
{
    use HasFactory;
    public function client(){
        return $this->belongsTo(Client::class, 'client_id');
    }
    protected $hidden = [
        'created_at',
        'updated_at'
    ];
    protected $fillable = [
        'numero_compte',
        'solde',
        'client_id',
        'fournisseur'
    ];
}
