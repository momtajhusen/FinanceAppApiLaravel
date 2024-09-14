<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'attachment_url',
    ];

    /**
     * Get the transaction that owns the attachment.
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
