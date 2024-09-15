<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'icon_id', 'name', 'balance', 'currency'];

    // Define relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function icon()
    {
        return $this->belongsTo(Icon::class);
    }
}
