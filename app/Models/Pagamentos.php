<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pagamentos extends Model
{
    protected $fillable = [
        'user_id',
        'desc',
        'value',
        'client_id',
        'payment_id',
        'payment_status',
        'processed'
    ];

    protected $casts = [
        'processed' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
