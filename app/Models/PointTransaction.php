<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\Download;

class PointTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'points',
        'type',
        'description',
        'download_id'
    ];

    const TYPE_CREDIT = 'credit';
    const TYPE_DEBIT = 'debit';

    protected $casts = [
        'points' => 'integer'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function download()
    {
        return $this->belongsTo(Download::class);
    }

    public function scopeCredits($query)
    {
        return $query->where('type', self::TYPE_CREDIT);
    }

    public function scopeDebits($query)
    {
        return $query->where('type', self::TYPE_DEBIT);
    }
}
