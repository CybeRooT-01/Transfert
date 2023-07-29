<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

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
}
