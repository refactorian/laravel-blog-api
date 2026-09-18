<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReadingHistory extends Model
{
    use HasFactory;

    protected $table = 'reading_history';

    protected $fillable = [
        'user_id',
        'post_id',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (ReadingHistory $history) {
            if (empty($history->read_at)) {
                $history->read_at = Carbon::now();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
