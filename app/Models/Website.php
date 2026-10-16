<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Website extends Model
{
    //
    protected $fillable = [
        'user_id',
        'name',
        'url',
        'domain',
        'status',
        'analysis_data',
        'analysis_error',
        'analyzed_at',
    ];

    protected function casts(): array
    {
        return [
            'analysis_data' => 'array',
            'analyzed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
