<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'montant',
        'frais',
        'envoyeur_id',
        'receveur_id',
        'type_transaction',
        'permanent',
        'code_transaction',
        'date_transaction',
        'date_expiration'
    ];
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function sender()
    {
        return $this->belongsTo(Compte::class, 'envoyeur_id');
    }

    public function receiver()
    {
        return $this->belongsTo(Compte::class, 'receveur_id');
    }
    public function compte()
    {
        return $this->belongsTo(Compte::class, 'envoyeur_id');
    }
    
}
